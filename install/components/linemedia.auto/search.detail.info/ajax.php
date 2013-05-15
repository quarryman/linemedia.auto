<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

/*
 * ≈сли надо только уточнить применимость.
 */
if ($_REQUEST['applicability'] == 'Y') {
    
    if (!CModule::IncludeModule('linemedia.auto')) {
        ShowError(GetMessage('LM_AUTO_NOTEPAD_ERROR_AUTO_MODULE'));
        return;
    }
    
    $api = new LinemediaAutoApiDriver();
    
    $article_id         = (int) $_REQUEST['article_id'];
    $manuId             = (int) $_REQUEST['manuId'];
    $template           = (string) $_REQUEST['template'];
    
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
        include(dirname(__FILE__).'/templates/'.$template.'/error.php');
        return;
    }
    include(dirname(__FILE__).'/templates/'.$template.'/applicability.php');
    return;
}

$APPLICATION->ShowHead();

$APPLICATION->IncludeComponent(
    "linemedia.auto:search.detail.info",
    "popup",
    array(
        'AJAX'          => 'N',
        'BRAND'         => $_REQUEST['brand'],
        'ARTICLE'       => $_REQUEST['article'],
        'ARTICLE_ID'    => $_REQUEST['article_id']
    ),
    false
);


