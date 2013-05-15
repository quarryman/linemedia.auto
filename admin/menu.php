<?php

IncludeModuleLangFile(__FILE__);

$sMGRight = $APPLICATION->GetGroupRight('linemedia.auto');

if ((!defined('NO_LM_AUTO_MAIN_MODULE_INSTALLED') || NO_LM_AUTO_MAIN_MODULE_INSTALLED != true)&& IsModuleInstalled('linemedia.auto')) {

    if (!CModule::IncludeModule('linemedia.auto')) {
    	return;
    }
    
    // Получение ID сайта.
    $rsSite = CSite::GetList($by='SORT', $order='ASC', array('ACTIVE' => 'Y', 'DEF' => 'Y'));
    $arSite = $rsSite->Fetch();
    
    if ($sMGRight != 'D') {
        
        $aMenu[] = array(
            'parent_menu'       => 'global_menu_linemedia.auto',
            'section'           => 'linemedia.auto.php',
            'sort'              => 1,
            'url'               => 'linemedia.auto_sale_orders_list.php?lang='.LANGUAGE_ID,
            'more_url'          => array(
                                        'linemedia.auto_sale_orders_list.php',
                                        'linemedia.auto_sale_order_detail.php',
                                        'linemedia.auto_sale_order_print.php',
                                        'linemedia.auto_sale_order_edit.php'
                                    ),
            'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_ORDERS_TITLE'),
            'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_ORDERS_DESCRIPTION'),
            'icon'              => 'linemedia.auto_menu_icon_order',
            'page_icon'         => 'linemedia.auto_page_icon_order',
            'module_id'         => 'linemedia.auto',
            'items_id'          => 'menu_linemedia.auto_order',
            'dynamic'           => false,
            'items' => array(
            	array(
            		'url'           => 'linemedia.auto_sale_order_new.php?lang='.LANGUAGE_ID.'&LID='.$arSite['ID'],
	                'more_url'      => array(
	                                        'linemedia.auto_sale_order_new.php',
	                                    ),
	                'text'          => GetMessage('LM_AUTO_GLOBAL_MENU_CREATE_ORDER_TITLE'),
	                'title'         => GetMessage('LM_AUTO_GLOBAL_MENU_CREATE_ORDER_DESCTIPTION'),
	                'icon'          => 'linemedia.auto_menu_icon_order_create',
	                'page_icon'     => 'linemedia.auto_page_icon_order_create',
	                'module_id'     => 'linemedia.auto',
	                'items_id'      => 'menu_linemedia.auto_order_create',

            	),
            ),
        );
        
        if ($sMGRight > 'O') {
            $aMenu[] = array(
                'parent_menu'       => 'global_menu_linemedia.auto',
                'section'           => 'linemedia.auto.php',
                'sort'              => 10,
                'url'               => 'linemedia.auto_vin_iblock_list.php?lang='.LANGUAGE_ID,
                'more_url'          => array(
                                            'linemedia.auto_vin_iblock_list.php',
                                            'linemedia.auto_vin_iblock_show.php',
                                        ),
                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_VIN_IBLOCK_TITLE'),
                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_VIN_IBLOCK_DESCRIPTION'),
                'icon'              => 'linemedia.auto_menu_icon_vin',
                'page_icon'         => 'linemedia.auto_page_icon_vin',
                'module_id'         => 'linemedia.auto',
                'items_id'          => 'menu_linemedia.auto_vin',
                'dynamic'           => false,
            );
            
            $aMenu[] = array(
                'parent_menu'       => 'global_menu_linemedia.auto',
                'section'           => 'linemedia.auto.php',
                'sort'              => 9000,
                'url'               => 'linemedia.auto_check.php?lang='.LANGUAGE_ID,
                'more_url'          => array(),
                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_SERVICE_TITLE'),
                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_SERVICE_DESCRIPTION'),
                'icon'              => 'linemedia.auto_menu_icon_service',
                'page_icon'         => 'linemedia.auto_page_icon_service',
                'module_id'         => 'linemedia.auto',
                'items_id'          => 'menu_linemedia.auto_service',
                'dynamic'           => false,
                'items' => array(
                        array(
                            'url'               => 'linemedia.auto_price_check.php?lang='.LANGUAGE_ID,
    		                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_PRICE_CHECK_TITLE'),
    		                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_PRICE_CHECK_DESCRIPTION'),
    		                'icon'              => 'linemedia.auto_menu_icon_price_check',
    		                'page_icon'         => 'linemedia.auto_page_icon_price_check',
    		                'module_id'         => 'linemedia.auto',
    		                'items_id'          => 'menu_linemedia.auto_price_check',
                        ),
                        array(
                            'url'               => 'linemedia.auto_csv_check.php?lang='.LANGUAGE_ID,
    		                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_CSV_CHECK_TITLE'),
    		                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_CSV_CHECK_DESCRIPTION'),
    		                'icon'              => 'linemedia.auto_menu_icon_csv_check',
    		                'page_icon'         => 'linemedia.auto_page_icon_csv_check',
    		                'module_id'         => 'linemedia.auto',
    		                'items_id'          => 'menu_linemedia.auto_csv_check',
                        ),
                        array(
                            'url'               => 'linemedia.auto_products.php?lang='.LANGUAGE_ID,
    		                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_PRODUCTS_TITLE'),
    		                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_PRODUCTS_DESCRIPTION'),
    		                'icon'              => 'linemedia.auto_menu_icon_products',
    		                'page_icon'         => 'linemedia.auto_page_icon_products',
    		                'module_id'         => 'linemedia.auto',
    		                'items_id'          => 'menu_linemedia.auto_products',
    		                'more_url'          => array(
                                                    'linemedia.auto_part_edit.php',
                                                ),
                        ),
                        array(
                            'url'               => 'linemedia.auto_custom_fields.php?lang='.LANGUAGE_ID,
                            'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_CUSTOM_FIELDS_TITLE'),
                            'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_CUSTOM_FIELDS_DESCRIPTION'),
                            'icon'              => 'linemedia.auto_menu_icon_custom_fields',
                            'page_icon'         => 'linemedia.auto_page_icon_custom_fields',
                            'module_id'         => 'linemedia.auto',
                            'items_id'          => 'menu_linemedia.auto_custom_fields',
                            'more_url'          => array(
                                                    'linemedia.auto_custom_fields.php',
                                                ),
                        ),
                        array(
                            'url'               => 'linemedia.auto_check.php?lang='.LANGUAGE_ID,
    		                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_CHECK_TITLE'),
    		                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_CHECK_DESCRIPTION'),
    		                'icon'              => 'linemedia.auto_menu_icon_check',
    		                'page_icon'         => 'linemedia.auto_page_icon_check',
    		                'module_id'         => 'linemedia.auto',
    		                'items_id'          => 'menu_linemedia.auto_check',
                        ),
                ),
            );
            
            $aMenu[] = array(
                'parent_menu'       => 'global_menu_linemedia.auto',
                'section'           => 'linemedia.auto.php',
                'sort'              => 8000,
                'url'               => 'linemedia.auto_wordforms.php?lang='.LANGUAGE_ID,
                'more_url'          => array(
                                            'linemedia.auto_wordforms_add.php?lang='.LANGUAGE_ID
                                        ),
                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_WORDFORMS_TITLE'),
                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_WORDFORMS_DESCRIPTION'),
                'icon'              => 'linemedia.auto_menu_icon_wordforms',
                'page_icon'         => 'linemedia.auto_page_icon_wordforms',
                'module_id'         => 'linemedia.auto',
                'items_id'          => 'menu_linemedia.auto_wordforms',
                'dynamic'           => false,
            );

            $aMenu[] = array(
                'parent_menu'       => 'global_menu_linemedia.auto',
                'section'           => 'linemedia.auto.php',
                'sort'              => 9000,
                'url'               => 'linemedia.linemedia_account.php?lang='.LANGUAGE_ID,
                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_LINEMEDIA_ACCOUNT_TITLE'),
                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_LINEMEDIA_ACCOUNT_DESCRIPTION'),
                'icon'              => 'linemedia.auto_menu_icon_linemedia_account',
                'page_icon'         => 'linemedia.auto_page_icon_linemedia_account',
                'module_id'         => 'linemedia.auto',
                'items_id'          => 'menu_linemedia.linemedia_account',
            );
            
            $aMenu[] = array(
                'parent_menu'       => 'global_menu_linemedia.auto',
                'section'           => 'linemedia.auto.php',
                'sort'              => 10000,
                'url'               => 'linemedia.auto_help.php?lang='.LANGUAGE_ID,
                'more_url'          => array(),
                'text'              => GetMessage('LM_AUTO_GLOBAL_MENU_HELP_TITLE'),
                'title'             => GetMessage('LM_AUTO_GLOBAL_MENU_HELP_DESCRIPTION'),
                'icon'              => 'linemedia.auto_menu_icon_help',
                'page_icon'         => 'linemedia.auto_page_icon_help',
                'module_id'         => 'linemedia.auto',
                'items_id'          => 'menu_linemedia.auto_help',
                'dynamic'           => false,
            );
            
        }
        
        /*
         * Событие для других модулей.
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterAdminMenuBuild");
    	while ($arEvent = $events->Fetch()) {
    	    try {
    		    ExecuteModuleEventEx($arEvent, array(&$aMenu));
    		} catch (Exception $e) {
    		    throw $e;
    		}
        }
        
        return $aMenu;
    
    }

}
