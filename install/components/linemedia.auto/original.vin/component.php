<?php
/*
 * компонент выводит автокаталог текдока из нашего API
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


/*
* Проверка наличия необходимых модулей
*/
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTOPORTAL_MODULE_NOT_INSTALL"));
    return;
}

/*
* Подключим на страницу jquery
*/
if ($arParams['INCLUDE_JQUERY'] == 'Y')
	CJSCore::Init(array('jquery'));

/*
* Добавим сборщик статистики Linemedia
*/
if($arParams['DISABLE_STATS'] != 'Y')
	$APPLICATION->AddHeadScript('http://api.auto.linemedia.ru/api.js');





$VIN = (string) $_REQUEST['vin'];
$VIN = trim($VIN);
$arResult['VIN_CODE'] = $VIN;

if($VIN != '')
{
	/*
	* Подключаемся к API
	*/
	$api = new LinemediaAutoApiDriver();

	try {
		$data = $api->query('decodeVin', array('vin' => $VIN));
	} catch (Exception $e) {
		$arResult['ERROR'] = $e->GetMessage(); 
		$this->IncludeComponentTemplate();
		return;
	}
	
	
	$arResult['CATALOG_URL'] = str_replace(array('#BRAND#','#MODEL#'), array($data['data']['brand_code'], $data['data']['vin']['ID_mod']), $arParams['CATALOGS_PATH']);
	
	$arResult['VIN'] = $data['data']['vin'];
	
}


/*
* Подключение шаблона
*/
$this->IncludeComponentTemplate();


/*
 *  Хлебные крошки.
 */
if ($arParams['SET_TITLE'] == 'Y') {
	$APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_VIN_TITLE') . ' ' . $VIN);
}
