<?php
namespace Omni\Ip;
#
# IP Verification functions
/**
 * Check if a given ip is in a network
 * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
 * @return boolean true if the ip is in this range / false if not.
 */
class CheckIp {
	#
	# Check if IPv4 address is in the range provided
	private function check_ip_range($ip, $range)
	{
		if ( strpos( $range, '/' ) === false ) 
			$range .= '/32';
	
		// $range is in IP/CIDR format eg 127.0.0.1/24
		list( $range, $netmask ) = explode( '/', $range, 2 );
		$range_decimal = ip2long( $range );
		$ip_decimal = ip2long( $ip );
		$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal = ~ $wildcard_decimal;	
		return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
	}
	#
	# Authorize IP.  Add more functions to this condition to authorize more IP ranges
	public static function ip_authorized($ip)
	{
		return (ouip($ip) && ourip($ip)) ? true : false;
	}
	#
	# Check if IP is an OmniUpdate IP.
	private function ouip($ip) 
	{
		return check_ip_range($ip, "2.4.6.8"); // Check with OmniCMS for their IP/CIDR
	}
	#
	# Use merged IP ranges for testing user's IP
	function ourip($ip) 
	{
		foreach([
			"1.2.3.4/18", 	// main IP range
			"5.6.7.8/16"	// main IP range
		] as $range)
			if( check_ip_range($ip, $range) )
				return true;
		return false;
	}
}