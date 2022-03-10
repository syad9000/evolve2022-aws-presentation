<?php
namespace Omni\Xml;
#
#error_reporting(E_ALL);
#ini_set('display_errors', '1');

use \SimpleXMLElement;

class Extractor extends SimpleXMLElement{
	public static function extract_from_xml($obj = null, $xpath = "")
	{
		if(is_object($obj)){
			$item = $obj->xpath($xpath); // Will be an array if the XPATH was correct.
			if( is_array($item) && count($item))
				return $item[0];
		}
		return $obj;
	}
}


