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


CModule::IncludeModule('iblock');


/*
 * Привязка к группам пользователей
 */
class LinemediaAutoIblockPropertyUserGroup extends CIBlockPropertyElementList
{
    const GROUP_GUEST = 'guest';
    
    
    function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'user_group',
            'DESCRIPTION' => GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_USERGROUP_TITLE'),
            
            'CheckFields' => array('LinemediaAutoIblockPropertyUserGroup', 'CheckFields'),
            'GetLength' => array('LinemediaAutoIblockPropertyUserGroup', 'GetLength'),
            'GetPropertyFieldHtml' => array('LinemediaAutoIblockPropertyUserGroup', 'GetEditField'),
            'GetAdminListViewHTML' => array('LinemediaAutoIblockPropertyUserGroup', 'GetFieldView'),
            'GetPublicViewHTML' => array('LinemediaAutoIblockPropertyUserGroup', 'GetFieldView'),
            'GetPublicEditHTML' => array('LinemediaAutoIblockPropertyUserGroup', 'GetEditField'),
            
            
        );
    }
    
    
    
    function CheckFields($arProperty, $value)
    {
        return array();
    }
    
    
    function GetLength($arProperty, $value)
    {
        return strlen($value['VALUE']);
    }
    
    
    function GetEditField($arProperty, $value, $htmlElement)
    {
        $str  = '<select name="' . $htmlElement['VALUE'] . '">';
        $str .= '<option value="">' . GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_USERGROUP_ALL') . '</option>';
        $str .= '<option value="'.self::GROUP_GUEST.'" '.(($value['VALUE'] == self::GROUP_GUEST) ? ('selected') : ('')).'>' . GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_USERGROUP_GUEST') . '</option>';
        
        $rsGroups = CGroup::GetList(($by = "c_sort"), ($order = "desc"), array());
        while ($group = $rsGroups->Fetch()) {
            $selected = ($value['VALUE'] == $group['ID']) ? ' selected' : '';
            $str .= '<option value="' . $group['ID'] . '"' . $selected . '>' . $group['NAME'] . '</option>';
        }
        $str .= '</select>';
        
        return $str;
    }
    
    
    function GetFieldView($arProperty, $value, $htmlElement)
    {
        return $value['VALUE'];
    }
}
