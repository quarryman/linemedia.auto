<?php
/*
 * ��������� ������� ����������� ������� �� ������ API
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


/*
* �������� ������� ����������� �������
*/
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTOPORTAL_MODULE_NOT_INSTALL"));
    return;
}
/*
* ������������ � API
*/
$api = new LinemediaAutoApiDriver();
try {
	$data = $api->query('getAccountInfo', array());
} catch (Exception $e) {
	echo $e->GetMessage();
	return;
}

$arResult['catalogs'] = array();
$catalogs = (array) $data['data']['original_catalogs'];
foreach($catalogs AS $catalog)
{
	if(!$catalog['available'])
		continue;
	
	
	$catalog['URL'] = $arParams['CATALOG_' . strtoupper($catalog['brand_code'])];
	if($catalog['URL'] == '')
		$catalog['URL'] = '/auto/original/' . $catalog['brand_code'] . '/';
	$arResult['catalogs'][] = $catalog;
}


/*
* ����������� �������
*/
$this->IncludeComponentTemplate();
