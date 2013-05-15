<?php

/**
 * Linemedia Autoportal
 * Main module
 * Module settings
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

/*
 * Module settings are available for administrator only
 */
if (!$USER->IsAdmin()) {
	return;
}

/**
 * Идентификатор модуля
 */
$sModuleId  = 'linemedia.auto';
 
/**
 * Подключаем модуль (выполняем код в файле include.php)
 */
CModule::IncludeModule($sModuleId);
 
/**
 * Языковые константы (файл lang/ru/options.php)
 */
global $MESS;
IncludeModuleLangFile( __FILE__ );
 
 




if ($REQUEST_METHOD == 'POST' && $_POST['Update'] == 'Y') {
    
    /*
    * событие для других модулей
    */
    $events = GetModuleEvents("linemedia.auto", "OnBeforeOptionsSave");
	while ($arEvent = $events->Fetch())
	{
	    try {
		    ExecuteModuleEventEx($arEvent, array(&$_POST));
		} catch (Exception $e) {
		    throw $e;
		}
    }
    
    
    /*
     * Если форма была сохранена, устанавливаем значение опций модуля.
     */
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/api-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/db-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/common-settings-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/search-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/users-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/goods-statuses-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/show-settings-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/orders-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/passwords-save.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/reliability-stats-save.php');
    
    
    /*
     * событие для других модулей
     */
    $events = GetModuleEvents("linemedia.auto", "OnAfterOptionsSave");
	while ($arEvent = $events->Fetch()) {
	    try {
		    ExecuteModuleEventEx($arEvent, array($_POST));
		} catch (Exception $e) {
		    throw $e;
		}
    }
    
}




 
/*
 * Описываем табы административной панели битрикса.
 */
$aTabs = array(
    array(
        'DIV'   => 'api',
        'TAB'   => GetMessage('LM_AUTO_MAIN_API_TAB_SET'),
        'ICON'  => 'api_settings',
        'TITLE' => GetMessage('LM_AUTO_MAIN_API_TAB_TITLE_SET')
    ),
    array(
        'DIV'   => 'db',
        'TAB'   => GetMessage('LM_AUTO_MAIN_DB_TAB_SET'),
        'ICON'  => 'db_settings',
        'TITLE' => GetMessage('LM_AUTO_MAIN_DB_TAB_TITLE_SET')
    ),
    array(
        'DIV'   => 'common',
        'TAB'   => GetMessage('LM_AUTO_MAIN_COMMON_TAB_SET'),
        'ICON'  => 'common_settings',
        'TITLE' => GetMessage('LM_AUTO_MAIN_COMMON_TAB_TITLE_SET')
    ),
    array(
        'DIV'   => 'search',
        'TAB'   => GetMessage('LM_AUTO_MAIN_SEARCH_TAB_SET'),
        'ICON'  => 'search_settings',
        'TITLE' => GetMessage('LM_AUTO_MAIN_SEARCH_TAB_TITLE_SET')
    ),
    array(
        'DIV'   => 'goods_statuses',
        'TAB'   => GetMessage('LM_AUTO_MAIN_GOODS_STATUSES_TAB_SET'),
        'ICON'  => 'goods_statuses_settings',
        'TITLE' => GetMessage('LM_AUTO_MAIN_GOODS_STATUSES_TAB_TITLE_SET')
    ),
    array(
        'DIV'   => 'show',
        'TAB'   => GetMessage('LM_AUTO_MAIN_SHOW_SETTINGS_TAB_SET'),
        'ICON'  => 'show_settings',
        'TITLE' => GetMessage('LM_AUTO_MAIN_SHOW_SETTINGS_TAB_TITLE_SET')
    ),
    array(
        'DIV'   => 'orders',
        'TAB'   => GetMessage('LM_AUTO_MAIN_ORDERS_TAB_SET'),
        'ICON'  => 'orders_settings',
        'TITLE' => GetMessage('LM_AUTO_MAIN_ORDERS_TAB_TITLE_SET')
    ),
    array(
        'DIV'   => 'passwords',
        'TAB'   => GetMessage('LM_AUTO_MAIN_PASSWORDS_TAB_SET'),
        'ICON'  => 'passwords',
        'TITLE' => GetMessage('LM_AUTO_MAIN_PASSWORDS_TAB_TITLE_SET')
    ),
);



/*
 * Событие для других модулей
 */
$events = GetModuleEvents("linemedia.auto", "OnOptionsTabsAdd");
while ($arEvent = $events->Fetch()) {
    try {
	    ExecuteModuleEventEx($arEvent, array(&$aTabs));
	} catch (Exception $e) {
	    throw $e;
	}
}


/**
 * Инициализируем табы
 */
$oTabControl = new CAdmintabControl('tabControl', $aTabs);
$oTabControl->Begin();


/**
 * Ниже пошла форма страницы с настройками модуля
 */
?>
<? $APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.0/jquery.min.js') ?>
<? $APPLICATION->AddHeadScript('/bitrix/modules/linemedia.auto/interface/options/script.js') ?>
<? $APPLICATION->SetAdditionalCSS('/bitrix/modules/linemedia.auto/interface/options/style.css') ?>

<form method="POST" enctype="multipart/form-data" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialchars($sModuleId) ?>&lang=<?= LANG ?>&mid_menu=1">
<?
    echo bitrix_sessid_post();
    
    
    /* API */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/api.php');
    $oTabControl->EndTab();
    
    
    /* DB */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/db.php');
    $oTabControl->EndTab();
    
    
    /* COMMON SETTINGS */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/common-settings.php');
    $oTabControl->EndTab();
    
    /* SEARCH SETTINGS */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/search.php');
    $oTabControl->EndTab();
    
    
    /* GOODS STATUSES */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/goods-statuses.php');
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/reliability-stats.php');
    $oTabControl->EndTab();
    
    
    /* SHOW SETTINGS */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/show-settings.php');
    $oTabControl->EndTab();
    
    
    /* ORDERS SETTINGS */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/orders.php');
    $oTabControl->EndTab();
    
    
    /* PASSWORDS SETTINGS */ 
    $oTabControl->BeginNextTab();
    include ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/options/passwords.php');
    $oTabControl->EndTab();
    
    
    /*
     * Событие для других модулей
     */
    $events = GetModuleEvents("linemedia.auto", "OnOptionsTabsShow");
	while ($arEvent = $events->Fetch()) {
	    try {
		    ExecuteModuleEventEx($arEvent, array(&$oTabControl));
		} catch (Exception $e) {
		    throw $e;
		}
    }
    
    
    
    $oTabControl->Buttons();
    ?>
    <input type="submit" name="Update" value="<?= GetMessage('LM_AUTO_MAIN_BUTTON_SAVE') ?>" />
    <input type="reset" name="reset" value="<?= GetMessage('LM_AUTO_MAIN_BUTTON_RESET') ?>" />
    <input type="hidden" name="Update" value="Y" />
    <? $oTabControl->End(); ?>
</form>
