<?php
/**
* This service will repost the data it receives to the service that is found in 
* the SERVICES file (look inside config.php to find the proper file).
* At the end, if there was a successful redirect of the data, the log will also
* be written to.
**/
error_reporting(E_ALL);
ini_set('display_errors', '1');
#
# INCLUDE FILES
require_once( __DIR__ . "/config.php" );
require_once( __ROOT__ . "/_resources/php/functions.php" );
spl_autoload_register("autoloader_Ouclasses");
use Omni\Aws\S3;
use Omni\Jwt\Firebase;
use Omni\Email\Emailer;
use Omni\Xml\Extractor;
use Omni\Ip\CheckIp;

#
# Block unauthorized HTTP requests.
if(isset($_SERVER["REMOTE_ADDR"]) && !CheckIp::ip_authorized($_SERVER["REMOTE_ADDR"]))
	logger_exit(
		sprintf("POST Request IP %s not allowed.", $_SERVER["REMOTE_ADDR"])
	);
#
# Time the webhook was triggered
$start = time();
$webhook_time = (new DateTime("NOW",new DateTimeZone("America/New_York")))->format("m-d-Y h:i:s");
#
#
#  Get POST data as a String.  Assume it is formatted as JSON
$data_from_ou = file_get_contents('php://input'); // Data from OU as String
logger(sprintf("\n# Begin %s", $webhook_time));
#
# The POST string is converted to an Array
$ou_paths_array = json_decode($data_from_ou, true);
#
# Require the site name to be present in the JSON file.
if(!isset($ou_paths_array["site"]))
	logger_exit(json_encode(["error" => "Missing Site Name"]));
#
# Extract what site we are dealing with.
$site = $ou_paths_array["site"];	
#
# copy content of the POST variable "success" key to the array we are working with.
$ou_success_array = $ou_paths_array["success"][$site];
#
# Filter the SETTINGS file by the site name which matches the one sent from OU.
$authorized_paths_array = filter_site_settings($site, file_get_contents(SERVICES));
#
# POST site name was not found in source file
if(count($authorized_paths_array) < 1)
	logger_exit("Invalid Site Name");
if(
#
# Add key/values from the SETTINGS file to the data to be posted to the next endpoint
foreach($authorized_paths_array as $key => $authorized_path){
	try {
		$repost_array = [];
		#
		# $authorized_path is 
		if(is_array($authorized_path)){
			foreach($authorized_path as $aakey => $aaval)
				$repost_array[$aakey] = $aaval;
		} else {
			$repost_array[$key] = $authorized_path;
		}
		#
		# Echo back if its a test.
		if(strcmp($authorized_path["type"], "test") === 0)
			exit($data_from_ou);

		#
		# Make sure the data received conforms to the SETTINGS file and has the correct path.
		$data = filter_settings_by_path($authorized_path, $ou_success_array);
		#
		# Doing a continue to go to the next element in the authorized_paths_array since there isn't anything to post.
		#
		# If there is data, put it into the repost_array[data]
		if(!count($data))
			continue;
		else
			$repost_array["data"] = $data;
		#
		# Here is the data we are posting to our next endpoint.
		$settings = array_merge([
			"site" => $ou_paths_array["site"],
			"time" => $webhook_time,
			"action" => $ou_paths_array["type"],
			"origin" => $ou_paths_array["origin"]
		], $repost_array);
		logger(sprintf("Data: %s", print_r($settings, true)));
		#
		# Format Post Array for display
		switch ( $settings["type"] ){
			case "email":
				$mail = new Emailer();
				logger(
					sprintf("Email: %s\n# Response: %s",
						$webhook_time,
						$mail->smail(json_encode($settings, JSON_PRETTY_PRINT), [
							"recipients" => $settings["dest"],
							"subject" => "Webhooks data:" . basename(__FILE__)
						])
					)
				);
				break;
			case "s3bucket":
				#
				# Upload the files to Amazon S3 directly
				include_once(__DIR__ . "/s3.php");
				break;
			case "codecommit":
				#
				# Upload the files to CodeCommit via SSH
				include_once(__DIR__ . "/codecommit.php");
				#
				# uncomment the following line if you are linking the CodeCommit repository directly to the S3 Bucket(s)
				// include_once(__DIR__ . "/set-as-website.php");
				break;
			case "redirect":
				logger(" POST to: " . $authorized_path["dest"] . " on " . $webhook_time);
				$response = request("POST", $authorized_path["dest"], json_encode($settings));
				break;
			default:
				logger("Unidentified type: " . $settings["type"]);
		}
	} catch (Exception $e){
		logger($e->getMessage());
	}
}
logger(sprintf("Exiting...Program ran in %d seconds", time() - $start));
?>