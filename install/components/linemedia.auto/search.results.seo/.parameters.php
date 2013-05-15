<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if (!CModule::IncludeModule('linemedia.auto')) {
    return;
}

if (!CModule::IncludeModule('iblock')) {
    return;
}

$default_iblock_type = 'linemedia_auto';
$default_iblock_id   = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SEARCH_SEO');


$arTypesEx = CIBlockParameters::GetIBlockTypes(array());

$arIBlocks = array();
$dbIBlock = CIBlock::GetList(array('SORT' => 'ASC'), array('SITE_ID' => $_REQUEST['site'], 'TYPE' => ($arCurrentValues['IBLOCK_TYPE'] != "-" ? $arCurrentValues['IBLOCK_TYPE'] : "")));
while ($arRes = $dbIBlock->Fetch()) {
    $arIBlocks[$arRes['ID']] = $arRes['NAME'];
}

$arComponentParameters = array(
    "PARAMETERS" => array(
		'ARTICLE'     =>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '={$_REQUEST["q"]}',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_SEO_ARTICLE')
		),
		'BRAND_ID'  =>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '={$_REQUEST["brand_id"]}',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_SEO_BRAND_ID')
		),
		'IBLOCK_TYPE'  =>  array(
            'TYPE'      =>  'LIST',
            'VALUES'    =>  $arTypesEx,
            'DEFAULT'   =>  $default_iblock_type,
            'PARENT'    =>  'BASE',
            'REFRESH'   =>  'Y',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_SEO_IBLOCK_TYPE')
        ),
		'IBLOCK_ID'  =>  array(
            'TYPE'      =>  'LIST',
            'VALUES'    =>  $arIBlocks,
            'DEFAULT'   =>  $default_iblock_id,
            'PARENT'    =>  'BASE',
            'REFRESH'   =>  'N',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_SEO_IBLOCK_ID')
        ),
    )
);
