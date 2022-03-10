<?php
#
# Turn PHP error reporting on|off
error_reporting(E_ALL);
ini_set( 'display_errors', '1' );


# Setup Variables for Creating the files and getting HTTP POST request.
$shell_code_dest = $shell_code = ""; // Variables that will be set in the script.
$start = time();
#
# Get data
if(!isset($settings))
	logger_exit("No settings found");

#
# Make sure we have the data we want in our POST request.
if( isset($settings["site"]) && isset($settings["type"]) && isset($settings["folder"]) && $settings["type"] === "s3bucket" ){
	$info = $s3Buckets = null;
	$ignore = $settings["ignore"] = [];
	$http_root = $ignore = $s3Buckets = $method = NULL;
	
	$repofolder = WEBSITES . DIRECTORY_SEPARATOR . $settings["folder"];
	$configxml = $repofolder . "/config.xml";
	#
	# Get the settings from the subfolder XML file
	try {
		#
		# Throw error if local config XML file not found
		if(!file_exists($configxml))
			throw new Exception(" Error: Local file config.xml not found!");
		#
		# Variables set from local config XML file
		$info = simplexml_load_file($configxml);
		$http_root = Extractor::extract_from_xml($info, "/config/entry[@key='REMOVE_TXT']");
		$ignore = Extractor::extract_from_xml($info, "/config/entry[@key='IGNORE']");
		$s3Buckets = Extractor::extract_from_xml($info, "/config/entry[@key='S3BUCKETS']");
		$method = Extractor::extract_from_xml($info, "/config/entry[@key='METHOD']");
		#
		# Throw Exception if S3_BUCKET and HTTP_ROOT not found in the config.xml
		if(! (isset($s3Buckets) && isset($http_root)) )
			throw new Exception(" Keys S3_BUCKET and HTTP_ROOT required in config.xml file.  Please set this up first!");
			
		#
		# Attempt to download the file via  HTTP_GET request if it wasn't found on the server.
		if( strcmp($method,"GET") === 0 ){
			foreach( $settings["data"] as $data ){
				logger(sprintf("Downloading: %s", $data["url"]));
				$root_rel_path = isset($http_root) ? str_replace($http_root, "", $data["url"]) : $data["url"];

				// Add a beginning slash if it's missing.
				if(! substr_compare($root_rel_path, "/", 0, 1) === 0)
					$root_rel_path = DIRECTORY_SEPARATOR . $root_rel_path;

				$full_path = __DIR__ . DIRECTORY_SEPARATOR . $settings["folder"] . $root_rel_path;
				if(!is_dir(dirname($full_path)))
					mkdir(dirname($full_path), 0755, true);
			
				$contents = request("GET", $data["url"]);
				file_put_contents($full_path, $contents);
			}
		}
			
		#
		# Remove any files in <entry key="IGNORE"> in file LOCAL_CONFIG
		if(isset($ignore))
			$settings["data"] = array_filter($settings["data"], function($item) use (& $ignore, & $http_root, & $settings){
				$root_rel_path = isset($http_root) ? str_replace($http_root, "", $item["url"]) : $item["url"];
				$file = basename($root_rel_path);
				$dir = dirname($root_rel_path);
				if( is_object($ignore->files->file) ){
					foreach( $ignore->files->file as $filename )
						if($filename == $file && !empty($filename)){
							$settings["ignore"][] = $root_rel_path;
							return false;
						}
				}
				// Remove directories found in 
				if( is_object($ignore->directories->directory) ){
					foreach($ignore->directories->directory as $dirname){
						$pos = strpos($dir, strval($dirname));

						if( $pos !== false && !empty($dirname)){
							$settings["ignore"][] = $root_rel_path;
							return false;
						}
					}
				}
				return true;
			});
		#
		# If no files were removed, update the data in Amazon S3
		if(count($settings["data"]) > 0) {
			$s3 = new S3([
				"http_root" => $http_root,
				"document_root" => $repofolder,
				"ip_addr" => $_SERVER["REMOTE_ADDR"]
			]);
			foreach($s3Buckets->bucket as $bucket) {
				$params = [
					"s3Bucket" => $bucket->name
				];
				if(isset($bucket->start_path))
					$params["start_path"] = $bucket->start_path; // The starting path to get from hosted files and to push to AWS.
				
				if(isset($bucket->redirect))
					$params["redirect"] = $bucket->redirect; // Set the website to redirect to another one instead of having content (for www subdomains)
				
				logger(sprintf("Updating S3 Bucket %s", $bucket->name));
				$s3->updateBucket($settings, $repofolder, $params);
			}
				} else logger(sprintf("%s No valid data found.", (new DateTime("NOW", new DateTimeZone("America/New_York")))->format("Y-m-d h:i:s")));
		#
		# Save output messages to files.
		logger_exit(sprintf("%s %s %s", (new DateTime("NOW", new DateTimeZone("America/New_York")))->format("Y-m-d h:i:s"), ucwords(basename(__DIR__)), "Finished"));
	} catch (Exception $e){
		logger($e->getMessage());
	}
} else logger_exit(json_encode(["error" => "# ". __LINE__ . " Input array not formatted correctly."]));
?>