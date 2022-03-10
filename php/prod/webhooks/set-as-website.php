<?php
#
# Turn PHP error reporting on|off
error_reporting(E_ALL);
ini_set( 'display_errors', '1' );
#
# Make sure we have the data we want in our POST request.
if( $settings["type"] === "codecommit" ){
	$info = $s3Buckets = null;
	$s3Buckets = NULL;
	
	$repofolder = WEBSITES . DIRECTORY_SEPARATOR . $settings["folder"];
	$configxml = $repofolder . "/config.xml";
	#
	# Get the settings from the subfolder XML file
	try {
		#
		# Throw error if local config XML file not found
		if(!file_exists($configxml))
			throw new Exception("Error: Local file config.xml not found!");
		#
		# Variables set from local config XML file
		$info = simplexml_load_file($configxml);
		$s3Buckets = Extractor::extract_from_xml($info, "/config/entry[@key='S3BUCKETS']");
		#
		# Set the bucket(s) as s3 website
		#
		# If no files were removed, update the data in Amazon S3
		foreach($s3Buckets->bucket as $bucket) {
			$website_ok = true;
			$s3 = new Omni\Aws\S3([
				"s3Bucket" => $bucket->name,
				"ip_addr" => $_SERVER["REMOTE_ADDR"]
			]);
			if (!$s3->bucketExists($params["s3Bucket"])){
				logger("# Creating Bucket: " . $s3->getBucket() . "\n");
				$s3->createBucket();
				$website_ok = $s3->createWebsite($s3->getWebsiteConfig());
			}
			if (!$website_ok)
				throw (new Exception("Website was not created successfully."));
		}
	} catch (Exception $e){
		logger($e->getMessage());
	}
} else logger("To set the codecommit repository as a website, add some S3Bucket in config.xml");
?>