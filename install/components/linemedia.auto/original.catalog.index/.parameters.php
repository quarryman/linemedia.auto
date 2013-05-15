<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


/*
* Проверка наличия необходимых модулей
*/
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTOPORTAL_MODULE_NOT_INSTALL"));
    return;
}


/*
* Подключаемся к API
*/
$api = new LinemediaAutoApiDriver();
try {
	$data = $api->query('getAccountInfo', array());
} catch (Exception $e) {
	echo $e->GetMessage();
	return;
}

$catalogs = (array) $data['data']['original_catalogs'];

$arComponentParameters = array(
    "PARAMETERS" => array(
        
    ),
);


foreach($catalogs AS $catalog)
{
	$available = ($catalog['available']) ? '' : ' ' . GetMessage('LM_AUTO_MAIN_CATALOG_UNAVAILABLE');
	$arComponentParameters['PARAMETERS']['CATALOG_' . strtoupper($catalog['brand_code'])] = array(
		"PARENT" => "BASE",
        "NAME" => GetMessage('LM_AUTO_MAIN_CATALOG') . ' ' .$catalog['brand_title'] . $available,
        "TYPE" => "STRING",
        "ADDITIONAL_VALUES" => "N",
        "MULTIPLE" => "N",
        "DEFAULT"=>'/auto/original/'.$catalog['brand_code'].'/',
	);
}