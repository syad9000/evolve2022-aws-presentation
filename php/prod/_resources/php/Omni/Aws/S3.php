<?php
namespace Omni\Aws;
#
# Turn PHP error reporting on|off
#error_reporting(E_ALL);
#ini_set( 'display_errors', '1' );
#
# Include aws/aws-sdk-php
require_once($_SERVER["HOME"] . "/vendor/autoload.php"); 
#
# AWS Libraries
use \Aws\S3\S3Client;
use \Aws\S3\BatchDelete;
use \Aws\Exception\AwsException;
use \Aws\S3\Exception\DeleteMultipleObjectsException;
use \Exception;
use \DateTime;
use \DateTimeZone;

class S3 extends \Aws\S3\S3Client{
	private $s3Client;
	private $version;
	private $region;
	private $datetime;
	protected $http_root;
	protected $s3Bucket;
	protected $start_path;
	
	function __construct($params = [], $version = "latest", $region = "us-east-1")
	{

		$this->version = $version;
		$this->region = $region;
		$this->datetime = new DateTime("NOW", new DateTimeZone("America/New_York"));
		foreach($params as $key => $param)
			$this->{$key} = $param;
		
		$this->start_path = isset($this->start_path) ? $this->start_path : "/";
		$this->s3Client = new \Aws\S3\S3Client([
			'version' => $version,
			'region' => $region
		]);
	}
	function getBucket()
	{
		return $this->s3Bucket;
	}
	function setBucket($val)
	{
		$this->s3Bucket = $val;
	}
	function setHttpRoot($val)
	{
		$this->http_root = $val;
	}
	function getHttpRoot()
	{
		return $this->http_root;
	}
	function setDocumentRoot($val)
	{
		$this->document_root = $val;
	}
	function getDocumentRoot()
	{
		return $this->document_root;
	}
	function getRegion()
	{
		return $this->region;
	}
	function getStartPath()
	{
		return $this->start_path;
	}
	function setStartPath($val)
	{
		$this->start_path = $val;
	}
	function getVersion()
	{
		return $this->version;
	}
	function logger($msg = "")
	{
		if(!is_dir(dirname(ERRORLOG)))
			mkdir(dirname(ERRORLOG), 0755, true);

		file_put_contents(ERRORLOG, "#\n# $msg\n", FILE_APPEND);
	}
	function bucketExists($bucket_name)
	{
		foreach ($this->bucketList() as $bucket)
			if( $bucket == $bucket_name )
				return true;
			
		return false;
	}
	function bucketList()
	{
		$list = [];

		//Listing all S3 Bucket
		$buckets = $this->s3Client->listBuckets();
		foreach ($buckets['Buckets'] as $bucket)
			$list[] = $bucket['Name'];
		return $list;
	}
	#
	# Create a PHP file with the following code. First create an AWS.S3 client service that specifies the AWS Region and version. 
	# Then call the createBucket method with an array as the parameter. The only required field is the key ‘Bucket’, with a 
	# string value for the bucket name to create. However, you can specify the AWS Region with the ‘CreateBucketConfiguration’ 
	# field. If successful, this method returns the ‘Location’ of the bucket.
	function createBucket()
	{
		try {
			$result = $this->s3Client->createBucket([
				'Bucket' => $this->s3Bucket,
			]);
			$msg = sprintf("Bucket %s created\n# The bucket\'s location is: %s.\n# The bucket\'s effective URI is: %s.", 
				$this->s3Bucket,
				$result['Location'],  
				$result['@metadata']['effectiveUri']
			);
			$this->logger($msg);
			return true;
		} catch (AwsException $e) {
			$msg = sprintf('Error: %s', $e->getAwsErrorMessage());
			$this->logger($msg);

		}
		return false;
	}
	/**
	* Create an AWS S3 formatted key from a root relative http path (e.g. /_resources/files/images/imag1.jpg)
	*
	* @param http_path string - root relative path of a file/directory
	* @param mime string - mime type of the file.
	* @return string key 
	*/
	function createKey($path="", $mime=null)
	{
		if(isset($this->start_path))
			$path = str_replace("^($this->start_path)", "", $path);
			
		$http_path = str_replace($this->http_root, "", $path);
		$key = substr($http_path, 0, 1) === "/"  // Remove first slash from the $http_path
			? substr($http_path, 1) 
			: $http_path;
		
		if($mime === null)
			return $key;
		
		return ($mime === "directory" && (substr($http_path, -1) !== DIRECTORY_SEPARATOR) ) // Add a slash if http_path is a directory and doesn't end with a slash.
			? $key . DIRECTORY_SEPARATOR 
			: $key;
	}
	/**
	* Tell Amazon that we are publishing a website, not a normal S3 bucket
	*/
	function createWebsite($params)
	{
		try {
			$resp = $this->s3Client->putBucketWebsite($params);
			$this->logger(sprintf("Succeeded in setting bucket website configuration."));
		} catch (AwsException $e) {
			$this->logger($e->getMessage());
		} catch (Exception $e){
			$this->logger($e->getMessage());
		}
		return false;
	}
	function deleteFolder($s3Bucket, $objectPrefix){
		$ret = [
			"action" => "delete",
			"error" => [],
			"success" => []
		];
		try {
			
			$ret["success"][] = var_dump($delete);
		} catch (Exception $e){
			$this->logger($e->getMessage());
			return $ret;
		}
	}
	function deleteObject($s3Bucket = null, $key=null){
		$ret = [
			"success" => 0,
			"key" => $key
		];
		try {
			$result = $this->s3Client->deleteObject([
				'Bucket' => $s3Bucket, // REQUIRED
				'Key' => $key // REQUIRED
			]);
			$this->logger( sprintf("%d %s", $this->datetime->getTimestamp(), $key) );
			
			return [
				"success" => 1,
				"key" => $key
			];
		} catch (S3Exception $e) {
			$msg = sprintf("File: %s\n# S3 Error: %s", $key, $e->getMessage());
			$this->logger($msg);
			return $ret;
		} catch (Exception $e){
			$msg = sprintf("File: %s Path: %s\n# Error: %s", $key, $filepath, $e->getMessage());
			$this->logger($msg);
			return $ret;
		}
	}
	/**
	* Delete multiple object, including folders from S3 Bucket
	* Note: You can't rely on the mime type for deleting files.  Files are deleted first from the 
	* production server by OmniUpdate.  This script therefore can't look at the file's mime type to know
	* whether it is a directory or not.  So, this introduced a bug: If a directory in OmniUpdate named "test"
	* is deleted and there is another directory/file that starts with that same name in the directory (i.e. "test_dir"), it will be
	* deleted too.  
	*
	* To fix this, we try and check whether the S3 Bucket folder has any file inside it or not.
	*/
	function deleteObjects($s3Bucket = null, $arr_files)
	{
		$ret = [
			"action" => "delete",
			"error" => [],
			"success" => []
		];
		try {
			if(!isset($this->http_root))
				throw (new Exception("HTTP_ROOT not set for file transfer"));
			
			foreach($arr_files as $file){
				#
				$this->logger(sprintf("File Path: %s Mime: %s", $this->document_root . $file["path"], $mime) );
				#
				# Make $key.
				# Remove beginning slash and add an end slash if it did not have a '.' in the file["path"]. 
				#
				# Example:
				# filename: /test
				# key: test/
				#
				# filename: /test/test.jpg 
				# key: test/test.jpg
				$hasdot = strpos($file["path"], ".");
				$isdir = ($hasdot === false) ? true : false;
				$key = ($isdir === true) ? $this->createKey($file["path"], "directory") : $this->createKey($file["path"]);
				#
				$this->logger("Key = $key");
				#
				# If the file contains any other files (i.e. it is a directory, delete all the files in it first)
				foreach($this->listObjects($s3Bucket, $key) as $object){
					$response = $this->deleteObject($s3Bucket, $object);
					if($response["success"] === 1){
						$ret["success"][] = $response;
					} else {
						$ret["error"][] = $response;
					}
				}
				if ( $isdir ) // Another delete to delete the directory itself
					$response = $this->deleteObject($s3Bucket, $key);

				if($response["success"] === 1){
					$ret["success"][] = $response;
				} else {
					$ret["error"][] = $response;
				}
			}
			return $ret;
		} catch (S3Exception $e) {
			$msg = sprintf("#\n# S3 Error: %s\n", $e->getMessage());
			$this->logger($msg);
			return $ret;
		} catch (Exception $e){
			$msg = sprintf("#\n# Error: %s\n", $e->getMessage());
			$this->logger($msg);
			return $ret;
		}	
	}
	#
	# Add the website configuration.
	# See: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#putbucketwebsite-example-1
	function getWebsiteConfig($index = 'index.html', $error_doc = '_resources/error_docs/404.html')
	{
		
		$ret = [
			'Bucket' => $this->s3Bucket, // REQUIRED
			'WebsiteConfiguration'  => [
				'IndexDocument' => [
					'Suffix' => $index // REQUIRED
				],
				'ErrorDocument' => [
					'Key' => $error_doc // REQUIRED
				]
			]
		];
		if( isset($this->redirect) )
			$ret["WebsiteConfiguration"]["RedirectAllRequestsTo"] = [
				'HostName' => $this->redirect, // REQUIRED
				'Protocol' => 'https',
			];
		$this->logger(sprintf("Website Configuration: %s", print_r($ret, true)));
		return $ret;
	}
	
	function getMime($filepath){
		//there is a bug with finfo_file();
		//https://bugs.php.net/bug.php?id=53035
		//
		// hard coding the correct mime types for presently needed file extensions
		$mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filepath);
		switch( pathinfo($filepath, PATHINFO_EXTENSION) ){
			case "css":
				$mime = 'text/css';
				break;
			case "js":
				$mime = 'application/javascript';
				break;
			case "directory":
				$mime = "Folder";
			default:
				break;
		}
		return $mime;
	}
	
	function listObjects($s3Bucket = null, $objectPrefix = null)
	{
		try{
			
			if(!isset($s3Bucket) )
				throw ( new Exception("Missing bucket name." ));
			
			if(!isset($objectPrefix) || (strlen($objectPrefix) < 2))
				throw ( new Exception("Search error." ));
			
			$result = $this->s3Client->getIterator('ListObjects', [
				'Bucket' => $s3Bucket,
				'Prefix' => $objectPrefix
			]);
			if(!$result)
				throw ( new Exception("No results found. "));
			
			foreach($result as $object)
				yield $object["Key"];
			
		} catch (Exception $e){
			$this->logger($e->getMessage());
		}
	}
	#
	# This function needs the S3 Bucket name, the httppath and the filepath
	function putObject($s3Bucket = null, $key=null, $filepath=null, $access = 'public-read', $mimeType = null)
	{
		$ret = [
			"success" => 0,
			"key" => $key,
			"filepath" => $filepath,
		];
		try {
			if(!isset($key) || !isset($filepath))
				throw ( new Exception("Missing key or filepath." ));
			
			$result = $this->s3Client->putObject([
				'Bucket' =>  $s3Bucket,
				'Key' => $key,
				'SourceFile' => $filepath,
				'ACL' => $access,
				'ContentType' => ( $mimeType === null ) ? $this->getMime( $filepath ) : $mimeType
			]);
			$ret["success"] = 1;
		} catch (S3Exception $e) {
			$this->logger(sprintf("File: %s\n# S3 Error: %s", $key, $e->getMessage()));
		} catch (Exception $e){
			$this->logger(sprintf("File: %s Path: %s# Error: %s", $key, $filepath, $e->getMessage()));
		}
		return $ret;
	}
	function putObjects($s3Bucket = null, $dirpath = "", $arr_files = [], $access = 'public-read')
	{
		$ret = [
			"action" => "update",
			"error" => [],
			"success" => []
		];
		try {
			if(!isset($this->http_root))
				throw (new Exception("HTTP_ROOT not set for file transfer"));
			
			foreach($arr_files as $file){
				$http_path = str_replace($this->http_root, "", $file["url"]);
				$filepath = $dirpath . $http_path; // This will be the file on the server starting at /home/api/s3/sitefolder.
				$mime = $this->getMime( $filepath );
				$key = $this->createKey($http_path, $mime); // This will be the root-relative path. Preceding slash will be removed and slash will be added at the end for directories.
				
				$response = $this->putObject($s3Bucket, $key, $filepath, $access, $mime);
				
				if($response["success"] === 1){
					$ret["success"][] = $response;
				} else {
					$ret["error"][] = $response;
				}
			}
			return $ret;
		} catch (Exception $e){
			$this->logger(sprintf("Error: %s", $e->getMessage()));
		}
		return [];
	}
	
	function showBucketContents($s3Bucket = null)
	{
		try{
			if(!isset($s3Bucket) )
				throw ( new Exception("Missing bucket name." ));
			
			$result = $this->s3Client->getIterator('ListObjects', [
				'Bucket' => $s3Bucket
			]);
			if(!$result)
				throw ( new Exception("No results found. "));
			
			foreach($result as $object)
				logger($object["Key"]);
			
		} catch (Exception $e){
			$this->logger(sprintf("#\n# Error: %s\n", $e->getMessage()));
		}
	}
	
	function updateBucket(& $settings, $document_root, $params)
	{
		$response = null;
		$website_ok = true;
		$bucket_exists = $this->bucketExists($params["s3Bucket"]);
		$this->setBucket($params["s3Bucket"]);
		$this->logger(sprintf("Bucket %s %s", $params["s3Bucket"], ($bucket_exists ? "exists" : "does not exist") ));
		try {
			if( $bucket_exists === false){
				$this->logger(sprintf("Creating Bucket: %s", $params["s3Bucket"]));
				$this->createBucket();
				$website_ok = $this->createWebsite($this->getWebsiteConfig());
			}

			if (!$website_ok)
				throw new Exception("Website was not created successfully.");
			
			$this->logger("Action: " . $settings["action"]);
			switch( $settings["action"] ){
				case "file recycle":
				case "file deletion":
					$response = $this->deleteObjects($params["s3Bucket"], $settings["data"]);
					break;
				default:
					$response = $this->putObjects($params["s3Bucket"], $document_root, $settings["data"]);
					break;
			}
			$settings["response"] = $response;
			$this->logger(sprintf("Local Directory: %s\n# Target Directory: %s", $settings["folder"], $document_root));
		} catch (Exception $e){
			$this->logger(sprintf("%s\n# Error: %s", (new DateTime("NOW", new DateTimeZone("America/New_York")))->format("Y-m-d h:i:s"), $e->getMessage()));
		}
	}
}
?>