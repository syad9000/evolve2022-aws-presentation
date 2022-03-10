<?php
#error_reporting(E_ALL);
#ini_set('display_errors', '1');
libxml_use_internal_errors(false);
require_once(__DIR__ . "/common.php");
/**
* Options: 
* pagination: Boolean - show or hide pagination
* limit: Integer - number of items to show
* images: Boolean - show or hide images
* media: display as bootstrap media-object https://getbootstrap.com/docs/3.4/components/#media
* dates: Boolean - show or hide dates
* dateFormat: String - PHP Date Format to use (default:'n/j/y')
* description: Boolean - show or hide description
* style: String - CSS class to add to the <ul>
* json: Boolean - Return output as JSON encoded string instead of HTML (default: false)
*
* Parameters:
* @rss SimpleXMLObject - the Feed to output
* @config String - JSON encoded String with options for displaying output
**/
function displayRSS($rss = [], $config="{}")
{
	# Get Options
	$opt = json_decode($config);
	$items = [];
	
	if( count($rss) < 1 )
		exit("<p>No events found</p>");
		
	# Set Options
	$page = isset($opt->page) ? $opt->page : 1;
	$dateFormat = isset($opt->dateFormat) ? $opt->dateFormat : "Y-m-d";
	$mediaObject = isset($opt->media) ? filter_var($opt->media, FILTER_VALIDATE_BOOLEAN) : false;
	$dates = isset($opt->dates) ? filter_var($opt->dates, FILTER_VALIDATE_BOOLEAN) : false;
	$author = isset($opt->author) ? filter_var($opt->author, FILTER_VALIDATE_BOOLEAN) : false;
	$description = isset($opt->description) ? filter_var($opt->description, FILTER_VALIDATE_BOOLEAN) : false;
	$description_length = isset($opt->description_length) ? intval($opt->description_length) : 60;
	$listStyle = isset($opt->listStyle) ? $opt->listStyle : 'media-list';
	$pagination = isset($opt->pagination) ? filter_var($opt->pagination, FILTER_VALIDATE_BOOLEAN) : false;
	$limit = isset($opt->limit) ? $opt->limit : 5;
	$num_pages = ceil(count($rss) / $limit);
	
	# Display Output as JSON if flag given
	if( filter_var($opt->json, FILTER_VALIDATE_BOOLEAN) === true )
		exit(json_encode($items));
	#
	# if 'pagination' = true and 'limit' is a positive integer > 0 then
	# 	display results by page, beginning at page 1.
	if($pagination === true || $page > 1)
		$items = build_ouput_array($rss, $num_pages, $limit, $page);
	#
	# if 'pagination' not true and 'limit' is a positive integer > 0 then
	else if ($limit > 0)
		$items = array_slice($rss, 0, $limit);
	#
	# else, we assume they want to display all of the items in the RSS feed
	else 
		$items = $rss; 
	
	
	#
	# Otherwise display HTML
	echo $mediaObject ? '<div class="' . $listStyle . '">'  : '<ul class="' . $listStyle . '">';
	foreach($items as $item){
		$datetime = DateTime::createFromFormat("U", intval($item["pubDate"]), new DateTimeZone("America/New_York"));
		$dates_snip = $dates === true ? '<span class="date"><svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-calendar3" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" focusable="false"><path fill-rule="evenodd" d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857V3.857z"/><path fill-rule="evenodd" d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg> ' . $datetime->format($dateFormat) . '</span>' : "";
		$author_snip = $author === true ? '<span class="author"><a href="?searchby=author&tag=' . $item["author"] . '"><svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-person-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" focusable="false"><path fill-rule="evenodd" d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg> ' . $item["author"] . '</span></a>' : "";
		$description_snip = $description === true ? "<p>" . substr(htmlentities($item["description"]), 0, $description_length) . "</p>" : "";
		
		if($mediaObject){
			$alt = empty($item["image"]["alt"]) ? $item["title"] : $item["image"]["alt"];
			echo '<div class="media">';
			#
			# Print image if it is found
			if($item["image"]){
				$img = '<img class="media-object" src="'.($item["image"]["thumb"] ? $item["image"]["thumb"] : ($item["image"]["src"] ? $item["image"]["src"] : "/_resources/images/template/placeholder.png")).'" alt="'.$alt.'" />';
				echo '<div class="media-left">
					<a href="'.$item["link"].'">'.$img.'</a>
				</div>';
			}
			echo '<div class="media-body">
					<h4 class="media-heading"><a href="'.$item["link"].'">'.htmlentities($item["title"]).'</a></h4>
					<div class="media-description">';
						echo ($dates === true || $author === true) ? '<p>'. $dates_snip . " " . $author_snip .'</p>' : '';
						echo $description === true ? '<p>'.htmlentities($item["description"]).'</p>' : '';
			echo 	'</div>
				</div>
			</div>
			';
		} else if($listStyle === 'media-list'){
			echo "<li class=\"media\">";
			#
			# Print image if it is found
			if ($dates === true)
				printf('<div class="media-left media-date"><a class="media-link" href="%s"><span class="media-month">%s</span><span class="media-day">%s</span></a></div>', 
					$item["link"],
					$datetime->format("M"),
					$datetime->format("d")
				);
			printf('<div class="media-body"><h3 class="media-heading"><a href="%s">%s</a></h3><div class="media-description">%s%s</div></div>',
				$item["link"],
				htmlentities($item["title"]),
				$author_snip,
				$description_snip
			);
			echo "</li>";
		} else {
			echo "<li>";
			#
			# Date snippet
			$date_snip = ($dates === true)
				? printf('<a class="media-link" href="%s">%s</a>', 
					$item["link"],
					$datetime->format($dateFormat)
				) : '';
			printf('<div class="media-body"><h3 class="media-heading"><a href="%s">%s</a></h3><div class="media-description">%s%s</div></div>',
				$item["link"],
				htmlentities($item["title"]),
				$date_snip . $author_snip,
				$description_snip
			);
			echo "</li>";
		}
	}
	echo $mediaObject ? '</div>' : '</ul>';
	if($pagination){
		#
		# Initial Pagination setup and Create links to posts
		echo pagination($page, $num_pages);
	}
		
}

