<?php 

/**
 * Linemedia Autoportal
 * Main module
 * Main include file
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__);

include_once('install/version.php');
include_once('constants.php');
include_once('functions.php');

global $DBType;


CModule::AddAutoloadClasses(
    "linemedia.auto",
    array(
            /*
             * ����������
             */
            'LinemediaAutoISearch'                  => "classes/general/interfaces/search.php",

            /*
             * �������� ������, ����������� �����
             */
            'LinemediaAutoApiDriver'                => "classes/general/api_driver.php",
            'LinemediaAutoApiModifications'			=> "classes/general/api_modifications.php",
            'LinemediaAutoSearch'                   => "classes/general/search.php",
            'LinemediaAutoSearchSimple'             => "classes/$DBType/search_simple.php",
            'LinemediaAutoSearchGroup'              => "classes/$DBType/search_group.php",
            'LinemediaAutoSearchPartial'            => "classes/$DBType/search_partial.php",

            /*
             * ��������������� - ��������� ������
             */
            'LinemediaAutoXML2Arr'                  => "classes/general/xml.php",
            'LinemediaAutoArr2XML'                  => "classes/general/xml.php",
            'LinemediaAutoI18N'                     => "classes/general/i18n.php",
            'LinemediaAutoDebug'                    => "classes/general/debug.php",
            'LinemediaAutoTecDocRights'             => "classes/general/tecdoc_rights.php",
            'LinemediaAutoLogger'                   => "classes/general/logger.php",
            'LinemediaAutoExcel'                    => "classes/general/excel.php",
            
            
            'LinemediaAutoBrowser'                  => "classes/general/browser.php",
            
            /*
             * ���� ������
             */
            'LinemediaAutoDatabaseAll'              => "classes/general/database.php",
            'LinemediaAutoDatabase'                 => "classes/$DBType/database.php",

            /*
             * �������
             */
            'LinemediaAutoImportAgent'              => "classes/general/import_agent.php",

            /*
             * ������ ����������� ������� Bitrix
             */
            'LinemediaAutoBasket'                   => "classes/general/basket.php",
            'LinemediaAutoBrand'                    => "classes/general/brand.php",
            'LinemediaAutoOrder'                    => "classes/general/order.php",
            'LinemediaAutoPartAll'                  => "classes/general/part.php",	        
            'LinemediaAutoPart'                     => "classes/$DBType/part.php",
            'LinemediaAutoCustomFieldsAll'          => "classes/general/custom_fields.php",
            'LinemediaAutoCustomFields'             => "classes/$DBType/custom_fields.php",
            'LinemediaAutoGroupTransfer'            => "classes/general/group_transfer.php",
            'LinemediaAutoPrice'                    => "classes/general/price.php",
            'LinemediaAutoSupplier'                 => "classes/general/supplier.php",
            'LinemediaAutoModule'                   => "classes/general/module.php",
            'LinemediaAutoTecDocAuto'               => "classes/general/tecdoc_auto.php",

            /*
             * �������
             */
            'LinemediaAutoUrlHelper'                => "classes/general/url_helper.php",
            'LinemediaAutoPartsHelper'              => "classes/general/parts_helper.php",
            'LinemediaAutoUserHelper'				=> 'classes/general/user_helper.php', // ��������������� ����� ��� ������ � ��������������
            'LinemediaAutoFileHelper'               => "classes/general/file_helper.php",


            'LinemediaAutoDirections'               => "classes/general/directions.php",
            'LinemediaAutoUser'                     => "classes/general/user.php",


            /*
             * ����� �������� ����������
             */
            'LinemediaAutoIblockPropertyUserGroup'  => "classes/general/iblock_prop_usergroup.php",
            'LinemediaAutoIblockPropertyCurrency'   => "classes/general/iblock_prop_currency.php",
            'LinemediaAutoIBlockPropertyCheckbox'	=> "classes/general/iblock_prop_checkbox.php",

            /*
             * ������ �������������� ��������
             */
            'LinemediaAutoWordForm'     			=> "classes/general/wordform.php",
            'LinemediaAutoCustomDiscount'           => "classes/general/custom_discount.php",
            'LinemediaAutoBasketFilter'             => "classes/general/basket_filter.php",
            'LinemediaAutoCSVChecker'               => "classes/general/csv_checker.php",
            'LinemediaAutoAttach'                   => "classes/general/attach.php",
            'LinemediaAutoRights'                   => "classes/general/rights.php",
            'LinemediaAutoNotepad'                 	=> "classes/general/notepad.php",// ����� ��� ������ � ���������
            'LinemediaAutoBrands'                   => 'classes/$DBType/brands.php',
            
            
            
            /*
             * ������� �������
             */
            'LinemediaAutoEventMain'                => "events/main.php", // ����� ������� �������� ������.
            'LinemediaAutoEventSale'                => "events/sale.php", // ����� ������� ��������.
            'LinemediaAutoEventIBlock'              => "events/iblock.php", // ����� ������� ����������.
            'LinemediaAutoEventSelf'                => "events/self.php", // ����� ������� ����� ������.
            'LinemediaEventApi'                  	=> "events/linemedia.api.php", // ����� ������� ������ API.
            
    )
);

/*
 * ��������� ������� ���� ��������� ��� ������ ���� (� �������� ��������������)
 * ����� �������� ����������� �����������
 */
LinemediaAutoEventMain::OnAdminInformerInsertItems_addUpdatesCheck();
LinemediaAutoEventMain::OnAdminInformerInsertItems_addLinemediaAccountCheck();

