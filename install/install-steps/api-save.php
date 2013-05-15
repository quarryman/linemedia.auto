<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Linemedia Autoportal
 * Main module
 * ajax api registration
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__);

/*
 * Что отправлять для регистрации?
 */
$send_version  = $_REQUEST['send_version'] == 'Y';
$send_sitename = (string) $_REQUEST['send_sitename'];
if ($send_version) {
    $version = array(
        'sitename' => $send_sitename,
        'lang' => LANG,
        'encoding' => SITE_CHARSET,
        'version' => SM_VERSION
    );
} else {
    $version = array(
        'sitename' => $send_sitename,
    );
}

/*
 * Запрос на регистрацию в API
 */
$api = new LinemediaAutoApiDriver();
try {
    $response = $api->query('requestNewAccount', $version);
} catch (Exception $e) {
    echo $e->getMessage();
    die();
}

if ($response['status'] == 'error') {
    die(GetMessage('LM_AUTO_MAIN_API_REGISTER_ERROR') . ': ' . $response['error']['error_text']);
} else {
    $id = (int) $response['data']['id'];
    $secret = (string) $response['data']['secret'];
    
    COption::SetOptionInt("linemedia.auto", "LM_AUTO_MAIN_API_ID", $id);
    COption::SetOptionString("linemedia.auto", "LM_AUTO_MAIN_API_KEY", $secret);
    echo 'ok';
}
