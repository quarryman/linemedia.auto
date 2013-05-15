<?php

/**
 * Linemedia Autoportal
 * Main module
 * Файл позволяет использовать сайт в качестве удалённого поставщика
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 * 
 * 
 * http://auto.x.linemedia.ru/bitrix/admin/linemedia.auto_search.php
 */


define('NO_KEEP_STATISTIC', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError('LINEMEDIA AUTO MODULE NOT INSTALLED');
    return;
}

$access = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH', 'Y');

if ($access == 'N') {
    CHTTP::SetStatus('403 Forbidden');
    exit();
}


/*
 * HTTP-авторизация.
 */
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Forbidden"');
    header('HTTP/1.0 401 Unauthorized');
    exit();
}

global $USER;

$USER = (!is_object($USER)) ? (new CUser()) : ($USER);

$result = $USER->Login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

if ($result !== true) {
    header('WWW-Authenticate: Basic realm="Forbidden"');
    header('HTTP/1.0 401 Unauthorized');
    exit();
}



/*
 * Подключение комопнента поиска.
 */
define('LM_AUTO_IGNORE_REMOTE_SUPPLIERS', true);

$APPLICATION->IncludeComponent(
    'linemedia.auto:search.results',
    'json',
    array(
        'QUERY'         => (string) $_REQUEST['q'],
        'PART_ID'       => (string) $_REQUEST['part_id'],
        'BRAND_TITLE'   => (string) $_REQUEST['brand_title'],
        'EXTRA'         => (string) $_REQUEST['extra'],
        'SORT'          => (string) $_REQUEST['sort'],
        'ORDER'         => (string) $_REQUEST['order'],
    ),
    false
);