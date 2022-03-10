<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
header("Content-Type:text/plain; charset=utf-8");

require_once( __DIR__ . "/config.php");
require_once(__ROOT__ . "/_resources/php/functions.php");
spl_autoload_register("autoloader_Ouclasses");
#
# Include Config Files
use Omni\Aws\S3;

$info = $s3Bucket = null;
$cleared = [];
$logs = [HTMLLOG => "", JSONLOG => "[]", ERRORLOG => ""];
$savedir = "logs";
$timestamp = (new DateTime("NOW", new DateTimeZone("America/New_York")))->format("-Ymd-");
$random = bin2hex(openssl_random_pseudo_bytes(8));
#
# Archive Logs to Amazon S3
try {
	$s3 = new Omni\Aws\S3([
		"s3Bucket" => S3LOGS,
		"http_root" => $savedir
	]);
	foreach($logs as $logpath => $initval){
		if(filesize($logpath) < 1000)
			printf("Log " . $logpath . " not ready to be cleared yet!");
			
		$basepath = str_replace(__ROOT__, "", __DIR__);
		$key = $savedir . $basepath . DIRECTORY_SEPARATOR . basename($logpath) . $timestamp . $random;
		$s3->putObject( S3LOGS, $key, $logpath, 'authenticated-read');
		#
		# Optionally clear the logs after backing up.
		//file_put_contents($logpath, $initval);
		#
		# Output the results
		foreach($logs as $logpath => $initval)
			printf("%s copied\n", str_replace(__ROOT__, "", $logpath));
	}
} catch (Exception $e){
	logger($e->getMessage());
}
?>