<?php
namespace Omni\Email;

#error_reporting(E_ALL);
#ini_set('display_errors', '1');
if(!defined("__PHPMAILER_ROOT__"))
	define("__PHPMAILER_ROOT__", $_SERVER["HOME"]);

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\SMTP;
use \PHPMailer\PHPMailer\Exception;
require_once(__PHPMAILER_ROOT__ . "/vendor/autoload.php");

class Emailer extends PHPMailer {
	
	public function __construct(){
		parent::__construct( true );
		
		$this->cred = [
			"server" => "post.example.edu",
			"username" => "omnicms",
			"password" => "waterunderthebridge",
			"emailaddr" => "webmaster@examle.edu"
		];
		// Create an instance; passing `true` enables exceptions
		//Server settings
		$this->SMTPDebug = 0;									// Turn off debug
		$this->isSMTP();                                       	// Send using SMTP
		$this->Host       = $this->cred["server"];     // Set the SMTP server to send through
		$this->SMTPAuth   = true;                               // Enable SMTP authentication
		$this->Username   = $this->cred["username"];       // SMTP username
		$this->Password   = $this->cred["password"];       // SMTP password
		$this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
		$this->Port       = 587;
		$this->Sender	  = $this->cred["emailaddr"];
		$this->Subject	  = "EITS API message";
	}
	
	public function getSender(){
		return $this->Sender;
	}
	
	public function setSender($val){
		$this->Sender = $val;
	}
	
	/**
	* Function for sending emails with PHP.  
	*
	*/
	public function email( $sender="", $recipients=[], $subject="", $body="" ){
		if(empty($sender))
			$sender = $this->Sender;
		
		try {
			//Recipients
			$this->setFrom($sender);
			foreach($recipients as $recipient)
				$this->addAddress($recipient);

			if(isset($_FILES["fileToUpload"]["name"]) && trim($_FILES["fileToUpload"]["name"]) != ''){
				/* 
				[
					[fileToUpload] => [
						[name] => lsamp.png 
						[type] => image/png 
						[tmp_name] => /tmp/phpkel8Oa 
						[error] => 0 
						[size] => 654043
					] 
				]
				*/
				$filetype = strtolower(pathinfo( $_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION ));
				if( isset($_FILES["fileToUpload"]["tmp_name"]) ){
					$thefile = base64_decode(base64_encode( file_get_contents( $_FILES["fileToUpload"]["tmp_name"] )));
					$this->AddStringAttachment($thefile, basename($_FILES["fileToUpload"]["name"]), 'base64', $filetype);
				}
			}
			// Content
			$this->isHTML(true); // Set email format to HTML
			$this->Subject = $subject;
			$this->Body    = $body;
			$this->AltBody = strip_tags(preg_replace("/<br[\s\/]?[\/]?>/", "\n", $body));

			$this->send();
			return "Message has been sent\n";
			
		} catch (Exception $e) {
			return printf("Message could not be sent. Mailer Error: %s\n", $e->getMessage());
		}
	}
	
	/**
	* Function for sending emails with PHP.  
	*
	*/
	public function smail( $body="", $config=[] ){
		
		if(empty($config["sender"]))
			$config["sender"] = $this->Sender;
		if(empty($config["subject"]))
			$config["subject"] = $this->Subject;
		try {
			
			//Recipients
			$this->setFrom($config["sender"]);
			foreach($config["recipients"] as $recipient)
				$this->addAddress($recipient);

			if(isset($_FILES["fileToUpload"]["name"]) && trim($_FILES["fileToUpload"]["name"]) != ''){
				/* 
				[
					[fileToUpload] => [
						[name] => lsamp.png 
						[type] => image/png 
						[tmp_name] => /tmp/phpkel8Oa 
						[error] => 0 
						[size] => 654043
					] 
				]
				*/
				$filetype = strtolower(pathinfo( $_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION ));
				if( isset($_FILES["fileToUpload"]["tmp_name"]) ){
					$thefile = base64_decode(base64_encode( file_get_contents( $_FILES["fileToUpload"]["tmp_name"] )));
					$this->AddStringAttachment($thefile, basename($_FILES["fileToUpload"]["name"]), 'base64', $filetype);
				}
			}
			// Content
			$this->isHTML(true); // Set email format to HTML
			$this->Subject = $config["subject"];
			$this->Body    = $body;
			$this->AltBody = strip_tags(preg_replace("/<br[\s\/]?[\/]?>/", "\n", $body));
			$this->send();
			return "Message has been sent\n";
			
		} catch (Exception $e) {
			return sprintf($e->getMessage());
		}
	}
}


