<?
if (!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true) die();
/**
	компонент показа сео-текстов.
	позволяет на одной странице показывать несколько блоков текста.
	также предоставляет возможность установки meta-информации о странице
	Тексты для показа берутся из инфоблока по условию ($APPLICATION->GetCurPage(false) == название_элемента)
*/
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID'])>0?intval($arParams['IBLOCK_ID']):0;
$arParams['WHAT_SHOW'] = strlen(trim($arParams['WHAT_SHOW'])) > 0?trim($arParams['WHAT_SHOW']):'SEO_BLOCK_1';

$arSelect = array('ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_'.$arParams['WHAT_SHOW']);

/*
	если устанавливаем мета-информацию, то запрашиваем её
*/
if($arParams['SET_META'] == 'Y') {
	foreach (array('keywords','title','description') as $prop) {
			$arSelect[] = 'PROPERTY_'.$prop;
	}//foreach
}
if($arParams['SET_H1'] == 'Y') {
	$arSelect[] = 'PROPERTY_h1';
}

CModule::IncludeModule('iblock');
global $APPLICATION;

$url = trim($APPLICATION->GetCurPage(false));
$filter = array('IBLOCK_TYPE'=>$arParams['IBLOCK_TYPE'], 'ACTIVE'=>'Y','IBLOCK_ID'=>$arParams['IBLOCK_ID'], '=NAME'=>$url);

$cache_id = md5(print_r($filter, 1)).$arParams['WHAT_SHOW'];
$c = new CPHPCache;

if ($c->InitCache(3600, $cache_id, '/lm_seo_blocks/')) {
	$arResult = $c->GetVars();
} else {
	$rs = CIBlockElement::GetList(array(), $filter, 0, 0, $arSelect);
	$arResult['FOUND'] = ($rs->SelectedRowsCount() >= 1);

	if ($arResult['FOUND']) {
		$arResult['ELEMENT'] = $rs->Fetch();
		$arResult['TEXT'] = $arResult['ELEMENT'][ 'PROPERTY_'.$arParams['WHAT_SHOW'].'_VALUE' ]['TEXT'];
	} else {

	}
	$c->StartDataCache();
	$c->EndDataCache($arResult);
}


$this->IncludeComponentTemplate();


if ('Y' == $arParams['SET_H1']) {
	$APPLICATION->SetTitle($arResult['ELEMENT']['PROPERTY_H1_VALUE']);
}

if ('Y' == $arParams['SET_META']) {
	$APPLICATION->SetPageProperty('title', $arResult['ELEMENT']['PROPERTY_TITLE_VALUE']);
	$APPLICATION->SetPageProperty('description', $arResult['ELEMENT']['PROPERTY_DESCRIPTION_VALUE']);
	$APPLICATION->SetPageProperty('keywords', $arResult['ELEMENT']['PROPERTY_KEYWORDS_VALUE']);
}
