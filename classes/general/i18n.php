<?php
/**
 * Linemedia Autoportal
 * Main module
 * Connection to Linemedia API
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__); 

class LinemediaAutoI18N
{
    protected static $ru = array('й','ц','у','к','е','н','г','ш','щ','з','х','ъ','ф','ы','в','а','п','р','о','л','д','ж','э','я','ч','с','м','и','т','ь','б','ю','Й','Ц','У','К','Е','Н','Г','Ш','Щ','З','Х','Ъ','Ф','Ы','В','А','П','Р','О','Л','Д','Ж','Э','Я','Ч','С','М','И','Т','Ь','Б','Ю');
    protected static $en = array('q','w','e','r','t','y','u','i','o','p','[',']','a','s','d','f','g','h','j','k','l',';',"'",'z','x','c','v','b','n','m',',','.','Q','W','E','R','T','Y','U','I','O','P','{','}','A','S','D','F','G','H','J','K','L',':','"','Z','X','C','V','B','N','M','<','>');
    
    
    /**
     * Проверка правильности ввода.
     */
    public static function switchLang($string, $from = 'ru', $to = 'en')
    {
        $langs = array('ru' => self::$ru, 'en' => self::$en);
        
        $string = str_replace($langs[$from], $langs[$to], (string) $string);
        
        return $string;
    } 
    
    
    /**
     * Вывод русских числительных.
     */
    public static function plural($number, $titles, $include = false)
    {
        $cases = array(2, 0, 1, 1, 1, 2); 
        $string = $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number%10, 5)]];
        if ($include) {
            $string = $number.' '.$string;
        }
        return $string;
    }
}