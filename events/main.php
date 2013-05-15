<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);

/**
 * Linemedia Autoportal
 * Main module
 * Module events for main bitrix module
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


class LinemediaAutoEventMain
{

    /**
     * Проверка и изменение главного меню в зависимости от настроек.
     */
    public function OnBuildGlobalMenu_CheckMainMenu(&$mainmenu, &$menu)
    {
        // Скрытие пунктов меню магазина.
        if (array_key_exists('global_menu_store', $mainmenu)) {
            
            if (COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE', 'N') == 'Y') {
                // Скрытие всего раздела "Магазин".
                
                foreach ($menu as $i => $item) {
                    if ($item['parent_menu'] == 'global_menu_store') {
                        unset($menu[$i]);
                    }
                }
                unset($mainmenu['global_menu_store']);
            } else {
                // Скрытие подпунктов меню раздела "Магазин".
                $hidemenu = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_MENU_HIDE', array()));
                $hidemenu = (array) $hidemenu['STORE'];
                
                foreach ((array) $menu as $i => $item) {
                    if ($item['parent_menu'] == 'global_menu_store') {
                        foreach ((array) $hidemenu as $index => $value) {
                            $indexes = explode('_', $index);
                            $itemmenu = &$menu[$i]['items'];
                            $last = array_pop($indexes);
                            
                            foreach ($indexes as $idx) {
                                $itemmenu = &$itemmenu[$idx];
                            }
                        }
                    }
                }
            }
        }
    }
    
    
    /**
     * Добавление главного меню "LM Автопортал".
     */
    public function OnBuildGlobal_AddMainMenu()
    {
    
    	$is12 = (version_compare(SM_VERSION, 12) >= 0);
    	$text = $is12 ? GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_TITLE') : '<nobr>' . GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_TITLE') . '</nobr>';
    	
        /*
         * Глобальный раздел меню "LM Автопортал"
         */
        $menu = array(
            'global_menu_linemedia.auto' => array(
            	"menu_id" => "LinemediaAuto",
                'icon' => 'linemedia.auto',
                'page_icon' => 'linemedia.auto',
                'index_icon' => 'linemedia.auto',
                'text' => $text,
                'title' => GetMessage('GLOBAL_MENU_TITLE'),
                'url' => 'linemedia.auto_index.php?lang='.LANGUAGE_ID,
                'sort' => 60,
                'items_id' => 'global_menu_linemedia',
                'help_section' => 'settings',
                'items' => array()
            )
        );
        
        return $menu;
    }
    
    
    /**
     * Проверка обновлений.
     */
    public static function OnAdminInformerInsertItems_addUpdatesCheck()
    {
        if (version_compare(SM_VERSION, 12) < 0) {
            return;
        }
        
        $updates = LinemediaAutoModule::checkUpdates('linemedia.auto');
        if ($updates === false) {
        	return;
        }
        end($updates);
        $last_version = key($updates);
        
        
        /*
         * Обновление уже установили
         */
        if (version_compare(LINEMEDIA_AUTO_MAIN_VERSION, $last_version) >= 0) {
            return;
        }
        
        $updates_str = '';
        $updates = array_slice($updates, 0, 5); // покажем последние 5 записей, а не все стомиллионов
        foreach ($updates as $ver => $txt) {
        	$updates_str .= '<span class="adm-informer-strong-text">' . $ver . '</span> '.$txt.'<br>';
    	}
        $qAIParams = array(
	        "TITLE" => GetMessage('LM_AUTO_MAIN_UPDATES_AVAILABLE'),
	        "COLOR" => "red",
	        "ALERT" => true,
	    );

	    $qAIParams["HTML"] = '
        <div class="adm-informer-item-section">
    	    <span class="adm-informer-item-l">'.GetMessage('LM_AUTO_MAIN_UPDATE_NEED').' <span class="adm-informer-strong-text"><a href="/bitrix/admin/update_system_partner.php?lang='.LANGUAGE_ID.'">'.GetMessage('LM_AUTO_MAIN_UPDATE_NEED_2').'</a></span></span>
    	</div>
    	<div class="adm-informer-item-section">
    	    <span class="adm-informer-item-r">'.GetMEssage('LM_AUTO_MAIN_YOUR_VERSION').': <span class="adm-informer-strong-text">'.LINEMEDIA_AUTO_MAIN_VERSION.'</span></span>
        </div>
        <div class="adm-informer-item-section">'.GetMEssage('LM_AUTO_MAIN_NEW_VERSION').': <span class="adm-informer-strong-text">'.$last_version.'</span></div>
        <div class="adm-informer-item-section">'.$updates_str.'</div>';
        
        
        /*
        * А нет ли старого информера?
        * как проверить через апи - непонятно
        */
        
		CAdminInformer::AddItem($qAIParams);
    }
    
    
    /**
     * Очистка
     */
    public static function OnModuleUpdate_clearCache()
    {
	    BXClearCache(true, '/lm_auto/mod_updates/');
    }
    
    
    /**
     * Проверка работы аккаунта в АПИ
     */
    public static function OnAdminInformerInsertItems_addLinemediaAccountCheck()
    {
    	
    	/*
    	 * Будем вызывать проверку раз в час
    	 */
    	$obCache = new CPHPCache();
		if ($obCache->InitCache(3600, __METHOD__, "/")) {
		    return;
        }
    	$obCache->StartDataCache();
    	$obCache->EndDataCache(); 
    	
    	
	    $api = new LinemediaAutoApiDriver();

		try {
			$response = $api->getAccountInfo();
		} catch (Exception $e) {
			$ar = Array(
			   "MESSAGE" => GetMessage('LM_AUTO_MAIN_LINEMEDIA_API_NOT_AVAILABLE') . ' ' . $e->GetMessage(),
			   "TAG" => "LM_API_ERROR",
			   "MODULE_ID" => "linemedia.auto",
			   "ENABLE_CLOSE" => "Y"
			);
			$ID = CAdminNotify::Add($ar);
			
			return;
		}
		
		
		$account = $response['data'];
		
		/*
		 * Дней осталось у текдока
		 */
		$before = strtotime($account['tecdoc']['available_before']);
		if ($before == 0) { // вечный текдок
		    CAdminNotify::DeleteByTag("LM_TECDOC");
		    return;
		}
		
		
		/*
		 * Надо ли проверять текдок?
		 */
		$LM_AUTO_MAIN_API_INFORM_TECDOC = COption::GetOptionString( 'linemedia.auto', 'LM_AUTO_MAIN_API_INFORM_TECDOC', 'Y' ) == 'Y';
		if ($LM_AUTO_MAIN_API_INFORM_TECDOC) {
			$tecdoc_left = ($before - time()) / 86400;
		
			if ($tecdoc_left < 1) {
				$ar = Array(
				   "MESSAGE" => GetMessage('LM_AUTO_MAIN_LINEMEDIA_API_TECDOC_FINISHED'),
				   "TAG" => "LM_TECDOC",
				   "MODULE_ID" => "linemedia.auto",
				   "ENABLE_CLOSE" => "Y"
				);
				$ID = CAdminNotify::Add($ar);
				
				return;
			}
			
			if( $tecdoc_left < 5) {
				$ar = Array(
				   "MESSAGE" => GetMessage('LM_AUTO_MAIN_LINEMEDIA_API_TECDOC_FINISHES_SOON') . (int) $tecdoc_left,
				   "TAG" => "LM_TECDOC",
				   "MODULE_ID" => "linemedia.auto",
				   "ENABLE_CLOSE" => "Y"
				);
				$ID = CAdminNotify::Add($ar);
				
				return;
			}
			
			CAdminNotify::DeleteByTag("LM_TECDOC");
		}
    }
    
    
    /**
     * Добавление кнопок на административную панель
     */
    public static function OnBeforeProlog_addAdminPanelButtons()
    {
	    global $APPLICATION, $USER;
	    
	    if (!is_object($USER) || !$USER->IsAdmin()) {
	    	return;
        }
        
	    $url = parse_url($_SERVER['REQUEST_URI']);
	    parse_str($url['query'], $query);
	    $query['lm_auto_debug'] = ($query['lm_auto_debug'] == 'Y') ? 'N' : 'Y';
	    $url['query'] = http_build_query($query);
	    $debug_url = $url['path'] . '?' . $url['query'];
	    
	    $APPLICATION->AddPanelButton(array(
	        "HREF"      => $debug_url,
	        "SRC"       => '/bitrix/themes/.default/icons/linemedia.auto/misc/debug_'.($query['lm_auto_debug'] == 'N' ? 'disable':'enable').'.png',
	        "ALT"       => GetMessage('LM_AUTO_MAIN_DEBUG_BTN'),
	        "TYPE"      => 'SMALL',
	        "HINT"      => array('TEXT' => GetMessage('LM_AUTO_MAIN_DEBUG_BTN')),
	        "MAIN_SORT" => 10000,
	        "SORT"      => 100
	    ));
    }
    
}
