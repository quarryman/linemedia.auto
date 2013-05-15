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
             * Интерфейсы
             */
            'LinemediaAutoISearch'                  => "classes/general/interfaces/search.php",

            /*
             * Ключевые классы, производщие поиск
             */
            'LinemediaAutoApiDriver'                => "classes/general/api_driver.php",
            'LinemediaAutoApiModifications'			=> "classes/general/api_modifications.php",
            'LinemediaAutoSearch'                   => "classes/general/search.php",
            'LinemediaAutoSearchSimple'             => "classes/$DBType/search_simple.php",
            'LinemediaAutoSearchGroup'              => "classes/$DBType/search_group.php",
            'LinemediaAutoSearchPartial'            => "classes/$DBType/search_partial.php",

            /*
             * Вспомогательные - служебные классы
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
             * База данных
             */
            'LinemediaAutoDatabaseAll'              => "classes/general/database.php",
            'LinemediaAutoDatabase'                 => "classes/$DBType/database.php",

            /*
             * Импортёр
             */
            'LinemediaAutoImportAgent'              => "classes/general/import_agent.php",

            /*
             * Замены стандартных классов Bitrix
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
             * Хелперы
             */
            'LinemediaAutoUrlHelper'                => "classes/general/url_helper.php",
            'LinemediaAutoPartsHelper'              => "classes/general/parts_helper.php",
            'LinemediaAutoUserHelper'				=> 'classes/general/user_helper.php', // вспомогательный класс для работы с пользователями
            'LinemediaAutoFileHelper'               => "classes/general/file_helper.php",


            'LinemediaAutoDirections'               => "classes/general/directions.php",
            'LinemediaAutoUser'                     => "classes/general/user.php",


            /*
             * Новые свойства инфоблоков
             */
            'LinemediaAutoIblockPropertyUserGroup'  => "classes/general/iblock_prop_usergroup.php",
            'LinemediaAutoIblockPropertyCurrency'   => "classes/general/iblock_prop_currency.php",
            'LinemediaAutoIBlockPropertyCheckbox'	=> "classes/general/iblock_prop_checkbox.php",

            /*
             * Новоые второстепенные сущности
             */
            'LinemediaAutoWordForm'     			=> "classes/general/wordform.php",
            'LinemediaAutoCustomDiscount'           => "classes/general/custom_discount.php",
            'LinemediaAutoBasketFilter'             => "classes/general/basket_filter.php",
            'LinemediaAutoCSVChecker'               => "classes/general/csv_checker.php",
            'LinemediaAutoAttach'                   => "classes/general/attach.php",
            'LinemediaAutoRights'                   => "classes/general/rights.php",
            'LinemediaAutoNotepad'                 	=> "classes/general/notepad.php",// Класс для работы с блокнотом
            'LinemediaAutoBrands'                   => 'classes/$DBType/brands.php',
            
            
            
            /*
             * События модулей
             */
            'LinemediaAutoEventMain'                => "events/main.php", // Класс событий главного модуля.
            'LinemediaAutoEventSale'                => "events/sale.php", // Класс событий магазина.
            'LinemediaAutoEventIBlock'              => "events/iblock.php", // Класс событий инфоблоков.
            'LinemediaAutoEventSelf'                => "events/self.php", // Класс событий этого модуля.
            'LinemediaEventApi'                  	=> "events/linemedia.api.php", // Класс событий модуля API.
            
    )
);

/*
 * Некоторые события надо запускать при каждом хите (в основном администратора)
 * Чтобы показать необходимые уведомления
 */
LinemediaAutoEventMain::OnAdminInformerInsertItems_addUpdatesCheck();
LinemediaAutoEventMain::OnAdminInformerInsertItems_addLinemediaAccountCheck();

