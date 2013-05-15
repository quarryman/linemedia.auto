<?php


/**
 * Linemedia Autoportal
 * Main module
 * Parts helper
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);



/**
 * Класс, отвечающий за работу с заказами.
 */
class LinemediaAutoPartsHelper
{
    
    /**
	 * Очистка артикула от лишних символов.
     * 
     * @param string $art
     * @param bool $multiple
     * @return string
	 */
    public function clearArticle($art)
    {
        $result = '';
        
        if (defined('BX_UTF') && BX_UTF == true) {
            $result = mb_strtolower(str_replace(array(' ', '-', '/', '\\', '.', '"', '\'', PHP_EOL, chr(10), chr(13)), '', $art), 'UTF-8');
        } else {
            $result = mb_strtolower(str_replace(array(' ', '-', '/', '\\', '.', '"', '\'', PHP_EOL, chr(10), chr(13)), '', $art));
        }

        return $result;
	}
    
    
    /**
     * Сортировка.
     * 
     * @param array $catalogs
     * @param string $code
     */
    public static function sorting($catalogs, $sort, $order)
    {
        $sort   = (string) $sort;
        $order  = (string) $order;
        if (empty($sort)) {
            return $catalogs;
        }
        $order = (strtolower($order) == 'asc');
        
        foreach ($catalogs as &$parts) {
            $parts = self::sorts($parts, $sort, $order);
        }
        return $catalogs;
    }
    
    
    /**
     * Сортировка.
     * 
     * @param array $part1
     * @param array $part2
     * @param string $code
     */
    protected static function sorts($array, $key, $asc = true)
    {
        $result = array();
        $values = array();
        foreach ((array) $array as $id => $value) {
            $values[$id] = $value[$key];
        }
        
        if ($asc) {
            asort($values);
        } else {
            arsort($values);
        }
        
        foreach ($values as $id => $value) {
            $result[$id] = $array[$id];
        }
        return $result;
     }
}


