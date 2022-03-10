<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once(__DIR__ . '/src/rss.php');
$default_page = 1;
$default_limit = 5;
#
# Get feed, limit and page variables from URL.
$feed = filter_input(INPUT_GET, "feed", FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED | FILTER_NULL_ON_FAILURE);
$limit = filter_input(INPUT_GET, "limit", FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>9999]]);
$page = filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 100]]);
$searchby = $tags = null;

if(isset($_GET["searchby"]))
	$searchby = strip_tags($_GET["searchby"]);
if(isset($_GET["tags"]))
	$tags = htmlspecialchars(strip_tags($_GET["tags"]));
	
# $options = [];
# $options["limit"] = $limit;
# $options["dates"] = "true";
# $options["dateFormat"] = "j F, Y";
displayRSS(getRSScategory($feed, $searchby, $tags), json_encode([
	"limit" => $limit === 0 || $limit === false ? $default_limit : $limit,
	"dates" => true,
	"dateFormat" => "j F, Y",
	"page" => $page === 0 || $page === false ? $default_page : $page,
	"json" => false
]));

