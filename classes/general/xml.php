<?php
/**
 * Linemedia Autoportal
 * Main module
 * Array 2 XML & xml 2 Array
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 
IncludeModuleLangFile(__FILE__); 

/*
 * Класс для конвертации ассоциативного многомерного массива в xml
 */
class LinemediaAutoArr2XML
{
	public static function encode($data, $startElement = 'xml', $xml_version = '1.0', $xml_encoding = 'UTF-8')
	{
        if (!is_array($data)) {
            $err = 'Invalid variable type supplied, expected array not found on line '.__LINE__." in Class: ".__CLASS__." Method: ".__METHOD__;
		    trigger_error($err);
            if ($this->_debug) {
                echo $err;
            }
            return false; //return false error occurred
        }
		$xml = new XmlWriter();
		$xml->openMemory();
		$xml->startDocument($xml_version, $xml_encoding);
		$xml->startElement($startElement);
        
		self::write($xml, $data);
        
		$xml->endElement();
		return $xml->outputMemory(true);
	}
    
    
	public static function write(XMLWriter &$xml, &$data)
	{
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'element'; // non assoc array
            }
            if (is_array($value)) {
                $xml->startElement($key);
    		    self::write($xml, $value);
    		    $xml->endElement();
    		    continue;
            }
            $xml->writeElement($key, $value);
        }
    }
}

/*
* Класс для конвертации xml в ассоциативный многомерный массив
*/
class LinemediaAutoXML2Arr
{
	public static function _struct_to_array($values, &$i)
	{
		$child = array();
		if (isset($values[$i]['value'])) 
		    array_push($child, $values[$i]['value']);

		$non_assoc_array_counter = 0;
		while ($i++ < count($values))
		{
			if(!isset($values[$i])) 
			    return $child;

			//if($values[$i]['tag'] == 'faces') print_r($values[$i]);
			switch ($values[$i]['type'])
			{
				case 'cdata':
					array_push($child, $values[$i]['value']);
				break;

				case 'complete':
					$name = $values[$i]['tag'];
					if($name == 'element') {
						$name = $non_assoc_array_counter;
						$non_assoc_array_counter++;
					}


					if(!empty($name))
					{
						$child[$name]= isset($values[$i]['value']) ? ($values[$i]['value']) : '';
						if(isset($values[$i]['attributes']))
						{
							$child[$name] = $values[$i]['attributes'];
						}
					}
				break;

				case 'open':
					$name = $values[$i]['tag'];

					if($name == 'element')
					{
						$name = $non_assoc_array_counter;
						$non_assoc_array_counter++;
					}

					$size = isset($child[$name]) ? sizeof($child[$name]) : 0;
					//$child[$name][$size] = self::_struct_to_array($values, $i);
					$child[$name] = self::_struct_to_array($values, $i);
				break;

				case 'close':
					return $child;
				break;
			}
		}
		return $child;
	}

	public static function decode($xml) {
		$values = array();
		$index  = array();
		$array  = array();
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parse_into_struct($parser, $xml, $values, $index);
		xml_parser_free($parser);
		$i = 0;
		$name = $values[$i]['tag'];
		$array[$name] = isset($values[$i]['attributes']) ? $values[$i]['attributes'] : '';
		$array[$name] = self::_struct_to_array($values, $i);
		return $array;
	}
}
