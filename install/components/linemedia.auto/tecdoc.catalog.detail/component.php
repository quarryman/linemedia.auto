<?php

/*
 * Компонент выводит детальную информацию о запчасти текдока.
 */
 
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError(GetMessage('LM_AUTOPORTAL_MODULE_NOT_INSTALL'));
    return;
}

/*
 * Подключим на страницу jquery
 */
CJSCore::Init(array('jquery'));


if (empty($arParams['SEF_FOLDER'])) {
    $arParams['SEF_FOLDER'] = '/auto/tecdoc/';
}

if (empty($arParams['SEARCH_URL'])) {
    $arParams['SEARCH_URL'] = '/auto/search/';
}

if (empty($arParams['ADD_SECTIONS_CHAIN'])) {
    $arParams['ADD_SECTIONS_CHAIN'] = 'Y';
}

if (empty($arParams['SHOW_ORIGINAL_ITEMS'])) {
    $arParams['SHOW_ORIGINAL_ITEMS'] = 'Y';
}

if (empty($arParams['SHOW_APPLICABILITY'])) {
    $arParams['SHOW_APPLICABILITY'] = 'Y';
}

if (empty($arParams['SHOW_SEARCH_FORM'])) {
    $arParams['SHOW_SEARCH_FORM'] = 'Y';
}

if (empty($arParams['CACHED'])) {
    $arParams['CACHED'] = 'Y';
}

if (empty($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 3600;
}
$arParams['CACHE_TIME'] = (int) $arParams['CACHE_TIME'];

$arParams['ARTICLE_ID'] = (string) $_REQUEST['ARTICLE_ID'];
$arParams['ARTICLE_LINK_ID'] = (string) $_REQUEST['ARTICLE_LINK_ID'];

if (empty($arParams['ARTICLE_ID']) || empty($arParams['ARTICLE_LINK_ID'])) {
    CHTTP::SetStatus('404 Not Found');
    ShowError(GetMessage('LM_AUTOPORTAL_DETAIL_NOT_FOUND'));
    return;
}

$arParams['BACKURL'] = null;

// Формарование обратной ссылки (в каталог).
if (!empty($_SESSION['tecdoc_catalog'])) {
    $databack = $_SESSION['tecdoc_catalog'][$arParams['ARTICLE_ID']][$arParams['ARTICLE_LINK_ID']];
    
    $backurl  = $arParams['SEF_FOLDER'];
    $backurl .= $databack['brand']['id'];
    if (!empty($databack['model_group']['title'])) {
        $backurl .= '/'.$databack['model_group']['title'];
    }
    $backurl .= '/'.$databack['model']['id'];
    $backurl .= '/'.$databack['modification']['id'];
    $backurl .= '/'.$databack['group']['id'];
    $backurl .= '/';
    
    $arParams['BACKURL'] = $backurl;
}


$arResult = array();


$api = new LinemediaAutoApiDriver();

$args = array(
	array(
		'article_id' => $arParams['ARTICLE_ID'],
		'article_link_id' => $arParams['ARTICLE_LINK_ID'],
	)
);

try {
	$response = $api->query('getArticleDetailsMultiple', $args);
	$arResult['DATA'] = $arResult['DETAIL'] = $response['data'][0];
} catch (Exception $e) {
	$arResult['ERROR'] = $e->GetMessage(); 
	$this->IncludeComponentTemplate('error');
	return;
}

$arResult['IMAGE'] = $arResult['DETAIL']['articleDocuments']['array'][0]['image'];
$arResult['APPLICABILITY'] = $arResult['DETAIL']['applicability'];




/*
 * Установить название в заголовок.
 */
if ($arParams['SET_TITLE'] == 'Y') {
    $APPLICATION->SetTitle($arResult['DATA']['directArticle']['articleName'].' '.$arResult['DATA']['directArticle']['articleNo']);
}


/*
 * Добавлять в цепочку навигации.
 */
if ($arParams['ADD_SECTIONS_CHAIN'] == 'Y') {
    $APPLICATION->AddChainItem($arResult['DATA']['directArticle']['articleName'].' '.$arResult['DATA']['directArticle']['articleNo'], null);
}


/*
 * Показывать SEO-описение.
 */
if ($arParams['SHOW_SEO'] == 'Y') {
    $seo_iblock_id = COption::GetOptionString('linemedia.auto', 'LM_AUTO_IBLOCK_SEARCH_SEO');
    if (intval($seo_iblock_id) > 0 && IsModuleInstalled('iblock')) {
        CModule::IncludeModule('iblock');
        $data = CIBlockElement::GetList(
                        array('SORT' => 'DESC'),
                        array('IBLOCK_ID' => intval($seo_iblock_id), 'PROPERTY_URL' => trim($_SERVER['REQUEST_URI'])),
                        false, 
                        false,
                        array('ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_URL', 'PROPERTY_TITLE', 'PROPERTY_H1', 'PROPERTY_DESCRIPTION', 'PROPERTY_TEXT')
                )->Fetch();
        if ($data !== false) {
            if (isset($data['PROPERTY_TITLE_VALUE']) && !empty($data['PROPERTY_TITLE_VALUE'])) {
                $APPLICATION->SetTitle(trim($data['PROPERTY_TITLE_VALUE']));
                $arResult['SEO']['TITLE'] = trim($data['PROPERTY_TITLE_VALUE']);
            }
            if (isset($data['PROPERTY_DESCRIPTION_VALUE']) && !empty($data['PROPERTY_DESCRIPTION_VALUE'])) {
                $APPLICATION->SetPageProperty('description', trim($data['PROPERTY_DESCRIPTION_VALUE']));
                $arResult['SEO']['DESCRIPTION'] = trim($data['PROPERTY_DESCRIPTION_VALUE']);
            }
            if (isset($data['PROPERTY_H1_VALUE']) && !empty($data['PROPERTY_H1_VALUE'])) {
                $APPLICATION->SetPageProperty('ADDITIONAL_TITLE', trim($data['PROPERTY_H1_VALUE']));
                $arResult['SEO']['H1'] = trim($data['PROPERTY_H1_VALUE']);
            }
            if (isset($data['PROPERTY_TEXT_VALUE']['TEXT']) && !empty($data['PROPERTY_TEXT_VALUE']['TEXT'])){
                if ($data['PROPERTY_TEXT_VALUE']['TYPE'] == 'text') {
                    $data['PROPERTY_TEXT_VALUE']['TEXT'] = nl2br($data['PROPERTY_TEXT_VALUE']['TEXT']);
                }
                $arResult['SEO']['TEXT'] = trim($data['PROPERTY_TEXT_VALUE']['TEXT']);
            }
        }
        unset($data);
    }
    unset($seo_iblock_id);
}

$this->IncludeComponentTemplate();
