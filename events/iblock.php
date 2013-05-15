<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


/**
 * Linemedia Autoportal
 * Main module
 * Module events for iblocks
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);


class LinemediaAutoEventIBlock
{
    
    /**
     * ��������� ID ����������.
     */
    public function OnStartIBlockElementAdd_setSupplierId(&$arFields)
    {
        global $APPLICATION;
        
        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        if ($arFields['IBLOCK_ID'] == $supplier_iblock_id) {
            
            // ��� ���������� ��������.
            if (!empty($arFields['CREATED_BY'])) {
                return;
            }
            
            // ��������� �������� ID ����������.
            $property = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id, 'CODE' => 'supplier_id'))->Fetch();
            
            $supplier_id = (string) $arFields['PROPERTY_VALUES'][$property['ID']]['n0']['VALUE'];
            
            if (empty($supplier_id)) {
                $arFields['PROPERTY_VALUES'][$property['ID']]['n0']['VALUE'] = LinemediaAutoSupplier::generateSupplierId();
            }
        }
    }
    
    
    /**
     * �������� ID ���������� �� ������������.
     */
    public function OnBeforeIBlockElementAdd_checkSupplierId($arFields)
    {
        global $APPLICATION;
        
        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        if ($arFields['IBLOCK_ID'] == $supplier_iblock_id) {
            // ��������� �������� ID ����������.
            $property = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id, 'CODE' => 'supplier_id'))->Fetch();
            
            $supplier_id = (string) $arFields['PROPERTY_VALUES'][$property['ID']]['n0']['VALUE'];
            
            // �������� ID ���������� �� ������������.
            if (LinemediaAutoSupplier::existsSupplierId($supplier_id)) {
                $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_DUPLICATE_SUPPLIER_ID'));
                return false;
            }
        }
    }
    
    
    /**
     * �������� ID ���������� �� ������������.
     */
    public function OnBeforeIBlockElementUpdate_checkSupplierId($arFields)
    {
        global $APPLICATION;
        
        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        if ($arFields['IBLOCK_ID'] == $supplier_iblock_id) {
            
            // ������������ ���������.
            $supplier_property = CIBlockElement::GetList(
                array(),
                array('IBLOCK_ID' => $supplier_iblock_id, 'ID' => $arFields['ID']),
                false,
                false,
                array('ID', 'PROPERTY_supplier_id')
            )->Fetch();
            
            // ��������� �������� ID ����������.
            $property = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id, 'CODE' => 'supplier_id'))->Fetch();
            
            // ��������� ID ����������.
            $arProps     = $arFields['PROPERTY_VALUES'][$property['ID']];
            $supplier_id = reset($arProps);
            $supplier_id = $supplier_id['VALUE'];
            /**
                ������� ���������� �� ����������
            */
            BXClearCache(false, '/supplier_stat/'.$supplier_id.'/');

            if ($supplier_property['PROPERTY_SUPPLIER_ID_VALUE'] != $supplier_id) {
                // �������� ID ���������� �� �������������.
                if (LinemediaAutoSupplier::existsSupplierId($supplier_id)) {
                    $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_DUPLICATE_SUPPLIER_ID'));
                    return false;
                }
            } else {
                // �������� ID ���������� �� ������������.
                if (!LinemediaAutoSupplier::isUniqueSupplierId($supplier_id)) {
                    $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_DUPLICATE_SUPPLIER_ID'));
                    return false;
                }
            }
        }
    }
    
    
    
    /*
    * ������� �� ������ ���� ��� ��������� ����������
    */
    public function OnAfterIBlockElementAdd_clearCache(&$arFields)
    {
	    LinemediaAutoFileHelper::clearCache($arFields['IBLOCK_ID']);
    }
    public function OnAfterIBlockElementUpdate_clearCache(&$arFields)
    {
	    LinemediaAutoFileHelper::clearCache($arFields['IBLOCK_ID']);
    }
    public function OnIBlockElementDelete_clearCache($ID)
    {
    	$res = CIBlockElement::GetByID($ID);
    	if($arFields = $res->Fetch())
		    LinemediaAutoFileHelper::clearCache($arFields['IBLOCK_ID']);
    }
    
    
    
    
}
    