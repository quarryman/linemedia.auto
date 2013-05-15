<?php

/**
 * Linemedia Autoportal
 * Main module
 * Iblock property for user groups
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);



/*
* 
*/
class LinemediaAutoIblockPropertyCurrency
{
    function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'N',
            'USER_TYPE' => 'currency',
            'DESCRIPTION' => GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_CURRENCY_TITLE'),

            'CheckFields' => array('LinemediaAutoIblockPropertyCurrency', 'CheckFields'),
            'GetLength' => array('LinemediaAutoIblockPropertyCurrency', 'GetLength'),
            'GetPropertyFieldHtml' => array('LinemediaAutoIblockPropertyCurrency', 'GetEditField'),
            'GetAdminListViewHTML' => array('LinemediaAutoIblockPropertyCurrency', 'GetFieldView'),
            'GetPublicViewHTML' => array('LinemediaAutoIblockPropertyCurrency', 'GetFieldView'),
            'GetPublicEditHTML' => array('LinemediaAutoIblockPropertyCurrency', 'GetEditField')
        );
    }

    function CheckFields($arProperty, $value) {
        return array();
    }

    function GetLength($arProperty, $value) {
        return strlen($value['VALUE']);
    }

    function GetEditField($arProperty, $value, $htmlElement)
    {
    	if(!CModule::IncludeModule('currency'))
    	{
	    	return false;
    	}
    	
        $str = '<select name="' . $htmlElement['VALUE'] . '">';
        //$str .= '<option value="">' . GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_CURRENCY_ALL') . '</option>';
        
        $rsGroups = CCurrency::GetList(($by="c_sort"), ($order="desc"), array());
        while($currency = $rsGroups->Fetch())
        {
            $selected = ($value['VALUE'] == $currency['CURRENCY']) ? ' selected' : '';
            $str .= '<option value="' . $currency['CURRENCY'] . '"' . $selected . '>' . $currency['CURRENCY'] . '</option>';
        }
        
        $str .= '</select>';
        return $str;
    }

    function GetFieldView($arProperty, $value, $htmlElement) {
        return $value['VALUE'];
    }
}
