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
 * Класс, отвечающий за работу с пользователями.
 */
class LinemediaAutoDirections
{
    
    
    
    
    /**
     * Список стран.
     */
    public static function getCountriesList()
    {
        $countries = GetCountryArray();
        
        $countries = array_combine($countries['reference_id'], $countries['reference']);
        
        return $countries;
    }
}

