<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage('LM_AUTO_NOTEPAD_ERROR_AUTO_MODULE'));
    return;
}

if (!check_bitrix_sessid('sessid')) {
    die(GetMessage('LM_AUTO_NOTEPAD_ERROR_SSID'));
}



$api = new LinemediaAutoApiDriver();




/*
* ≈сли надо только уточнить применимость
*/
if($_REQUEST['applicability'] == 'Y')
{
	$article_id = (int) $_REQUEST['article_id'];
	$article_link_id= (int) $_REQUEST['article_link_id'];
	$manuId 	= (int) $_REQUEST['manuId'];
	
	$args = array(
		'art_id' => $article_id,
		'link_id' => -1,
		'brand_id' => $manuId,
		'include_modifications' => true,
	);
	$function = 'getModelsUsedThisDetail';
	
	
	
	
	try {
		$response = $api->query($function, $args);
		$arResult['APPLICABILITY'] = $response['data'];
	} catch (Exception $e) {
		$arResult['ERROR'] = $e->GetMessage(); 
		include(dirname(__FILE__) . '/templates/.default/error.php');
		return;
	}
	include(dirname(__FILE__) . '/templates/.default/applicability.php');
	return;
}
