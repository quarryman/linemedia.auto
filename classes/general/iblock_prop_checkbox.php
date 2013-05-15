<?php

/**
 * Linemedia Autoportal
 * Tyres module
 * Basket management class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 


/**
 * Класс, реализующий свой тип данных в свойствах инфоблока.
 */
class LinemediaAutoIBlockPropertyCheckbox
{
    public function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE'     => 'Checkbox',
            'DESCRIPTION'   => GetMessage('LM_AUTO_TYRES_IBLOCK_PROPERTY_CHECKBOX_DESC'),

            'CheckFields'           => array(__CLASS__, 'CheckFields'),
            'GetLength'             => array(__CLASS__, 'GetLength'),
            'GetPropertyFieldHtml'  => array(__CLASS__, 'GetEditField'),
            'GetAdminListViewHTML'  => array(__CLASS__, 'GetAdminListViewHTML'),
            'GetPublicViewHTML'     => array(__CLASS__, 'GetFieldView'),
            'GetPublicEditHTML'     => array(__CLASS__, 'GetEditField'),
        );
    }
    
    
    public function CheckFields($arProperty, $value)
    {
        return array();
    }
    
    
    public function GetLength($arProperty, $value)
    {
        return strlen($value['VALUE']);
    }
    
    
    public function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return  $value['VALUE'] == 'Y' 
                ? GetMessage('LM_AUTO_TYRES_IBLOCK_PROPERTY_CHECKBOX_YES')
                : GetMessage('LM_AUTO_TYRES_IBLOCK_PROPERTY_CHECKBOX_NO');
    }
    
    
    public function GetEditField($arProperty, $value, $htmlElement)
    {
        return '<input type="hidden" name="' . $htmlElement['VALUE'] . '" value="N" />' . 
            '<input type="checkbox" name="' . $htmlElement['VALUE'] . '" value="Y"' .
            ($value['VALUE'] == 'Y' ? ' checked="checked"' : null) . ' />' . 
            ((isset($arProperty["WITH_DESCRIPTION"]) && $arProperty["WITH_DESCRIPTION"] == 'Y') ?
            '&nbsp;<input type="text" size="20" name="'.$htmlElement["DESCRIPTION"].'" value="'.htmlspecialchars($value["DESCRIPTION"]).'">' : '');
    }
    
    
    public function GetFieldView($arProperty, $value, $htmlElement)
    {
        return  $value['VALUE'] == 'Y' 
                ? GetMessage('LM_AUTO_TYRES_IBLOCK_PROPERTY_CHECKBOX_YES')
                : GetMessage('LM_AUTO_TYRES_IBLOCK_PROPERTY_CHECKBOX_NO');
    }
}