<?php
namespace Omni\Jwt;
#
# Turn PHP error reporting on|off
#error_reporting(E_ALL);
#ini_set( 'display_errors', '1' );
#
# Include JWT
require_once( $_SERVER["HOME"] . "/vendor/autoload.php" );
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;
use \Exception;
use \DateTime;
use \DateInterval;
use \DateTimeZone;

class Firebase{
	private $private_key_file;
	private $public_key_file;
	private $iss;
	private $iat;
	private $exp;
	private $payload;
	
	function __construct(){
		/*
		iss : The issuer of the token.
		sub : The subject of the token.
		aud : The audience of the token.
		exp : This will probably be the registered claim most often used. ...
		nbf : Defines the time before which the JWT MUST NOT be accepted for processing.
		iat : The time the JWT was issued. ...
		jti : Unique identifier for the JWT.
		*/
		$auth = json_decode(file_get_contents($_SERVER["HOME"] . "/etc/auth.json"));
		$this->private_key_file = $auth["key"];
		$this->public_key_file = $auth["key.pub"];
		if(isset($_SERVER["HTTPS"]) && isset($_SERVER["HTTP_HOST"]))
			$this->iss = ($_SERVER["HTTPS"] == "on" ? "https://" : "https://") . $_SERVER["HTTP_HOST"];
		#
		# Authentication User and Password
        $this->authentication = [
        	"username" => $auth->webhooks->username, 
        	"password" => $auth->webhooks->password
        ];
	}
	public function destroy_token()
    {
        unset($this->token);
    }
    public function set_token($val)
    {
    	$this->token = $val;
    }
    public function get_token()
    {
    	return $this->token;
    }
	public function authenticate($url="")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                  
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/json; charset=utf-8']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->authentication));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        $token = curl_exec($ch);
        if($token && intval(curl_getinfo($ch, CURLINFO_HTTP_CODE)) < 300)
            $this->set_token( $token );
        else
        	exit(" Authentication failed\n");
    }
	public function get_private_key(){	
		return trim(file_get_contents( $this->private_key_file ));
	}
	public function get_public_key(){
		return trim(file_get_contents( $this->public_key_file ));
	}
	public function encode_jwt( $post_data = ""){
		try {
			$iat = new DateTime("NOW", new DateTimeZone("America/New_York"));
			$exp = new DateTime("NOW", new DateTimeZone("America/New_York"));
			$exp->add(new DateInterval("PT2M"));	
			$this->payload = [
				"iss" => $this->iss,
				"iat" => $iat->getTimestamp(),
				"exp" => $exp->getTimestamp(),
				"data" => $post_data
			];
			return JWT::encode($this->payload, $this->get_private_key(), 'HS256');
		} catch (Exception $e){
			return $e->getMessage();
		}
	}
	public function decode_jwt( $jwt="" ){
		try {
			return JWT::decode($jwt, $this->get_public_key(), ['HS256']);	
		} catch (ExpiredException $e){
			return $e->getMessage();
		} catch (Exception $e){
			return $e->getMessage();
		}
	}
}