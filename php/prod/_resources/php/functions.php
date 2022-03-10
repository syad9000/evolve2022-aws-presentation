<?php
#
# Functions
#
# Autoloader
function autoloader_Ouclasses( $cls )
{
	$path = $_SERVER["HOME"] . "/www/_resources/php/". str_replace("\\", "/", $cls) . ".php";
	if(file_exists($path))
		require_once($path);
}
#
# Templating function applying shell file to PHP global and local variables and returning 
# shell script with PHP variables substituted.
function create_repo_code( $filename, $args = [] )
{
	#
	# ensure the file exists
	if ( !file_exists( $filename ) )
		return "";

	#
	# Make values in the associative array easier to access by extracting them
	if ( is_array( $args ) )
		extract( $args );

	#
	# buffer the output (including the file is "output")
	ob_start();
	include $filename;
	return ob_get_clean();
}

function create_repo_folder( $repo_folder="" )
{
	// Create directory if not exists
	if(!is_dir($repo_folder))
		mkdir($repo_folder, 0755, true);
	return $repo_folder;
}

#
# Accomodate either AWS CodeCommit SSH or Github HTTPS type URLs for Repository locations
function create_repo_url($url="", $folder="")
{
	if(substr($url, 0, 3) === "ssh")
		return $url . DIRECTORY_SEPARATOR . $folder;
	else
		return $url . DIRECTORY_SEPARATOR . $folder . ".git";
}

#
#
function copy_code( $srcpath, $destpath )
{
	$updated = new Date("NOW", new DateTimeStamp("America/New_York"));
	$output = exec("cp -r $srcpath $destpath");
	return $output;
}
#
# Return the paths from the OU POST string (converted to an Array)
# that match the authorized_path index in the authorized_path array.
function filter_settings_by_path( $authorized_path = [], $post_paths_array = [], $ou_match_field = "path", $api_match_field="authorized_path" )
{
	#
	#
	$repost_array = [];
	foreach($post_paths_array  as $post_site => $post_path){
		logger("checking: ". $post_site . " " . $post_path);
		if(strpos(stripslashes($post_path[$ou_match_field]), stripslashes($authorized_path[$api_match_field])) === 0)
			$repost_array[] = $post_path;
	}
	return $repost_array;
}
#
# Filter the SERVICES JSON file for the "site" key comimg from the 
# OU Webhook POST request
function filter_site_settings( $key = "", $services_file_contents = "{}")
{
	#
	# Only accept string values
	if(! is_string($services_file_contents))
		return [];
	#
	# JSON encoded string only
	$settings_array = json_decode($services_file_contents, true);
	if($settings_array === NULL)
		return [];
	
	#
	# Find the associative array index to return by the $key argument.
	foreach($settings_array as $site => $services_array)
		if($site === $key)
			return $services_array;
	return [];
}
#
# Create a function to write to the Error log defined in config.php
function logger($msg = "")
{
	if(!is_dir(dirname(ERRORLOG)))
		mkdir(dirname(ERRORLOG), 0755, true);

	file_put_contents(ERRORLOG, "#\n# $msg\n", FILE_APPEND);
}

#
# Log and exit
function logger_exit($msg = "")
{
	logger($msg);
	die("$msg\n"); # presumably use die instead of exit because it is thought to close the connection too.
}

#
# Post once per OU notification that matches the services.json "path" variable for the appropriate web site
#
# @param $data_array - Array which contains the "success" key from the posted data
# @param $post_data The entire data received from the webhook.
function post_service( $data_array, $web_services_array)
{
	#
	# $service_name is the Website name that is sending the webhook. For example, "EITS", "TPS", "OneSource"
	# $data is the contents of the post_data at the $service_name key.
	#
	# Find the string key within the services.json file. Return the urls associated with this service key
	$service_arr = find_service($data_array["site"], $web_services_array);
	$service_index = -1;
	$post_data = false;
	$post_url = "";
	
	if( count($service_arr) > 0 ){
		#
		# Post the data
		foreach($data_array["success"] as $site => $endpoint){
			foreach($endpoint as $key => $dest_info){
				$service_index = search_array_by_key("path", $dest_info["path"], $service_arr[$site]);
				# Need to find service array path in the destination array to send notification.
				
				if($service_index > -1){
					$post_data = true;
					$post_url = $service_arr[$site][$service_index]["dest"];
				}
			}
		}
		#
		# Post the entire incomming JSON's success key to the destination.
		if($post_data === true){
			request("POST", $post_url, json_encode($data_array["success"]));
			#
			# Turning off emails
			//if(isset($service_arr["email"])){
			//	send_email($service_arr["email"], json_encode($data_array) );	
			//}
			return true;
		}
	}
	return false;
}


#
# Use CURL to perform HTTP requests
# 
# @param type string # value should be "GET", "POST" or something that is understood
# @param url string # destination URL
# @param post_data string # a JSON encoded string to post
# @return string # either the error code if unsuccessful or the data returned if successful
function request( $type, $url = "", $post_data = "{}" )
{
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, $url);
	if($type==="POST")
		curl_setopt_array($ch, [
			CURLOPT_POSTFIELDS => $post_data,
			CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8']
		]);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	return ($code == 200 && $data) ? $data : strval($code);
}

/**
* Iterate through the array members. 
* While iterating, search $str for value $member[$key]
*
* @param $key
* @param $str
* @param $arr
* @return boolean true or false depending on whether $str === $arr[$index][$key]
*/
function search_array_by_key($key, $str, $arr)
{
	
	foreach($arr as $index => $member){
		if(substr($str, 0, strlen($member[$key])) === $member[$key])
			return $index;
	}
	return -1;
}