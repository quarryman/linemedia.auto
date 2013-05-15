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
     * Установка ID поставщика.
     */
    public function OnStartIBlockElementAdd_setSupplierId(&$arFields)
    {
        global $APPLICATION;
        
        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        if ($arFields['IBLOCK_ID'] == $supplier_iblock_id) {
            
            // Это обновление элемента.
            if (!empty($arFields['CREATED_BY'])) {
                return;
            }
            
            // Получение свойства ID поставщика.
            $property = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id, 'CODE' => 'supplier_id'))->Fetch();
            
            $supplier_id = (string) $arFields['PROPERTY_VALUES'][$property['ID']]['n0']['VALUE'];
            
            if (empty($supplier_id)) {
                $arFields['PROPERTY_VALUES'][$property['ID']]['n0']['VALUE'] = LinemediaAutoSupplier::generateSupplierId();
            }
        }
    }
    
    
    /**
     * Проверка ID поставщика на уникальность.
     */
    public function OnBeforeIBlockElementAdd_checkSupplierId($arFields)
    {
        global $APPLICATION;
        
        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        if ($arFields['IBLOCK_ID'] == $supplier_iblock_id) {
            // Получение свойства ID поставщика.
            $property = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id, 'CODE' => 'supplier_id'))->Fetch();
            
            $supplier_id = (string) $arFields['PROPERTY_VALUES'][$property['ID']]['n0']['VALUE'];
            
            // Проверка ID поставщика на уникальность.
            if (LinemediaAutoSupplier::existsSupplierId($supplier_id)) {
                $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_DUPLICATE_SUPPLIER_ID'));
                return false;
            }
        }
    }
    
    
    /**
     * Проверка ID поставщика на уникальность.
     */
    public function OnBeforeIBlockElementUpdate_checkSupplierId($arFields)
    {
        global $APPLICATION;
        
        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        if ($arFields['IBLOCK_ID'] == $supplier_iblock_id) {
            
            // Существующий поставщик.
            $supplier_property = CIBlockElement::GetList(
                array(),
                array('IBLOCK_ID' => $supplier_iblock_id, 'ID' => $arFields['ID']),
                false,
                false,
                array('ID', 'PROPERTY_supplier_id')
            )->Fetch();
            
            // Получение свойства ID поставщика.
            $property = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $supplier_iblock_id, 'CODE' => 'supplier_id'))->Fetch();
            
            // Получение ID поставщика.
            $arProps     = $arFields['PROPERTY_VALUES'][$property['ID']];
            $supplier_id = reset($arProps);
            $supplier_id = $supplier_id['VALUE'];
            /**
                очищаем статистику по поставщику
            */
            BXClearCache(false, '/supplier_stat/'.$supplier_id.'/');

            if ($supplier_property['PROPERTY_SUPPLIER_ID_VALUE'] != $supplier_id) {
                // Проверка ID поставщика на существование.
                if (LinemediaAutoSupplier::existsSupplierId($supplier_id)) {
                    $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_DUPLICATE_SUPPLIER_ID'));
                    return false;
                }
            } else {
                // Проверка ID поставщика на уникальность.
                if (!LinemediaAutoSupplier::isUniqueSupplierId($supplier_id)) {
                    $APPLICATION->throwException(GetMessage('LM_AUTO_ERROR_DUPLICATE_SUPPLIER_ID'));
                    return false;
                }
            }
        }
    }
    
    
    
    /*
    * События на чистку кеша при изменении инфоблоков
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
    