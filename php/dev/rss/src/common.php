<?php
#
# @return sliced array beginning at page number offset for number of items/page
# @param $arr - Array of rss items
# @param $num_pages - integer number of pages available
# @param $items_per_page - integer number of items to display per page
# @param $page - integer the page number
function build_ouput_array($arr = [], $num_pages=1, $items_per_page=5, $page=1)
{	
	$start = ($page - 1) * $items_per_page;
	return array_slice($arr, $start, $items_per_page);
}

function compare($a, $b) 
{
    return $a['pubDate'] == $b['pubDate'] 
    ? 0 
    : ($a['pubDate'] > $b['pubDate'] ? -1 : 1);
}

/** Get Media information from RSS **/
function getMediaInfo($item, $feed, $httphost)
{
	$obj = [];
	$namespaces = $item->getNamespaces(true);
	if(isset($namespaces["media"])){
		$media = $item->children($namespaces['media']);
		$obj = [
			"title" => strval($media->content->title),
			"description" => strval($media->content->description),
			"keywords" => strval($media->content->keywords),
			"link" => strval($media->content->link),
			"thumbnail" => getUrlPrefix(strval($media->content->thumbnail->attributes()->url), $feed, $httphost),
			"url" => getUrlPrefix(strval($media[0]->attributes()->url), $feed, $httphost)
		];
		$obj["image"] = ($obj["thumbnail"] != null)	? $obj["thumbnail"] : ($obj["url"] != null ? $obj["url"] : null);
	}
	return $obj;
};

/**
* @param url - the URL in question
* @param feed - RSS FEED we are getting with curl
* @param httphost - HTTPHOST value from the server to place in front of URL if it is set to local image
**/
function getUrlPrefix($url, $feed, $httphost)
{
	$p = parse_url($feed);
	if($p["host"] == $httphost || substr($url, 0, 4) == "https")
		return $url;
	else if(substr($url, 0, 1) == "/")
		return $p["scheme"] . "://" . $p["host"] . $url;
	else
		return $url;
}

function request( $type, $url = "", $params = [])
{
	$ch = curl_init();
	if($type == "POST")
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	
	if( count($params) > 0 )
		$url = $url . "?" . http_build_query($params);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$data = curl_exec($ch);
	curl_close($ch);
	
	return $data;
}

/**
* Bootstrap 3 pagination
*/
function pagination($page, $numberOfPages)
{
	$link = '?page=';
	$ret = "";
	if($numberOfPages > 1){
		$ret = '<nav><ul class="pagination">';
		if ($page > 1) {
			#
			# get previous page num
			$prevpage = $page - 1;
			$ret .= '<li><a href="'.$link.$prevpage.'" aria-label="Previous"><span aria-hidden="true">&#139;</span></a></li>';
		}

		#
		# loop to show links to range of pages around current page
		#	page=12 range=40 
		for ($x = 1; $x < $numberOfPages + 1; $x++) {
			#
			# if it's a valid page number...
			if($x == intval($page))
				$ret .= "<li class=\"active\"><a href=\"#\">$x <span class=\"sr-only\">(current)</span></a></li>";
			else
				$ret .= "<li><a href=\"$link$x\">$x</a></li>";
		}
		
		#
		# if not on last page, show forward and last page links        
		if ($page < $numberOfPages) {
			#
			# get next page
			$nextpage = $page + 1;
			$ret.= '<li><a href="'.$link.$nextpage.'" aria-label="Next"><span aria-hidden="true">&#155;</span></a></li>';
		}
		$ret.= "</ul></nav>";
	}
	echo $ret;
}

/**
* Get either all the RSS items, or items based on a tag and search parameter passed
* to the function.  If no search parameter, then the search will be performed by 
* category.
*
* @param $path String - Either relative or absolute path to an XML file to display
* @param $searchby String - XML node to search for (default:<category>)
* @param $tag String - Value to search for within XML node
*/
function getRSScategory($path="", $searchby="", $tag="")
{
	libxml_use_internal_errors(true);
	$posts = [];
	$path = @substr_compare($path, 'http', 0, 4) === 0 ? $path : "";
	$contents = request("GET", $path);
	$timezone = new DateTimeZone("America/New_York");
	if($contents === false)
		return $posts;
	try {
		$xml = simplexml_load_string($contents);
	} catch (Exception $e){
		printf("Error: %s\n", $e->getMessage() );
	}	
	if($xml === false) 
		return $posts;
		
	$xpath = empty($tag) || empty($searchby) ? "/rss/channel/item" : "/rss/channel/item[$searchby='$tag']";
	
	$items = $xml->xpath($xpath);
	
	if(!$items)
		return [];
	
	foreach($items as $item){
		$item->registerXPathNamespace("media", "http://search.yahoo.com/mrss/");
		$img = $item->xpath('./media:content');
		$pubDate = (new DateTime($item->pubDate, $timezone))->getTimestamp();
		$endDate = isset($item->endDate) ? (new DateTime($item->endDate, $timezone))->getTimestamp() : NULL;
		
		$posts[] = [
			"title" => strval($item->title),
			"description" => strval($item->description),
			"author" => strval($item->author),
			"link"  => strval($item->link),
			"pubDate" => $pubDate,
			"endDate" => $endDate,
			"category" => $item->category,
			"image" => isset($img[0]) ? [
				"src" => strval($img[0]->attributes()->url),
				"thumb" => strval($item->xpath('./media:content/media:thumbnail')[0]->attributes()->url),
				"alt" => strval($item->xpath('./media:content/media:title')[0])
			] : []
		];
	}
	usort($posts, "compare"); // Sort items descending by pubDate;
	return $posts;	
}

/** 
* Newer function for returning array of RSS items;
*/
function getRSSFeed($options)
{
	libxml_use_internal_errors(true);
	$xml = @simplexml_load_file($options["feed"]);
	$posts = [];
	$timezone = new DateTimeZone("America/New_York");
	if($xml === false)
		exit("");
	
	$xpath = (isset($options["tag"]) && isset($options["searchby"]) && !empty($options["tag"]) && !empty($options["searchby"]))
		? "/rss/channel/item[" . $options["searchby"] . "='" . $options["tag"] . "']"
		: "/rss/channel/item";
	
	$items = $xml->xpath($xpath);
	if($items !== false){
		foreach($items as $item){
			$pubDate = (new DateTime($item->pubDate, $timezone))->getTimestamp();
			$endDate = isset($item->endDate) ? (new DateTime($item->endDate, $timezone))->getTimestamp() : NULL;
			$media = getMediaInfo($item, $xml->channel->link, $_SERVER["HTTP_HOST"]);
			$posts[] = [
				"title" => strval($item->title),
				"description" => strval($item->description),
				"author" => strval($item->author),
				"link"  => strval($item->link),
				"pubDate" => $pubDate,
				"endDate" => $endDate,
				"category" => $item->category,
				"media" => $media
			];
		}
		usort($posts, "compare"); // Sort items descending by pubDate;
		
		return isset($options["limit"]) ? array_slice($posts, 0, intval($options["limit"])) : $posts;
	}
	return $posts;
}
?>
