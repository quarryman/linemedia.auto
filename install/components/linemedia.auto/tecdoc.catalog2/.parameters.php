<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("linemedia.auto")) {
    return;
}

if (!CModule::IncludeModule("iblock")) {
    return;
}

$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE_ID"], "ACTIVE"=>"Y"));
while ($arr = $rsIBlock->Fetch()) {
    $arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}


$sets_ids = LinemediaAutoApiModifications::getSetsIds();

$arComponentParameters = array(
    "PARAMETERS" => array(
        "SEF_MODE" => array(
        ),
        "DETAIL_URL" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_PATH_DETAIL'),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT" => "/auto/part-detail/#ARTICLE_ID#/#ARTICLE_LINK_ID#/"
        ),
        "COLUMNS_COUNT" => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_COLUMNS'),
                "TYPE" => "STRING",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'4'
        ),
        'ADD_SECTIONS_CHAIN' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_ADD_CHAIN'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'Y'
        ),
        'SHOW_ORIGINAL_ITEMS' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_SHOW_ORIGINAL'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'Y'
        ),
        'GROUP_MODELS' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_GROUP'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'N'
        ),
        'TECDOC_BRAND_TYPES' => array( 
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_BRAND_TYPES'),
                "TYPE" => "LIST",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "Y",
                "DEFAULT"=>array('1', '2', '3'),
                'VALUES' => array(
                	'1' => GetMessage('LM_AUTO_MAIN_TECDOC_BRAND_TYPES_1'),
                	'2' => GetMessage('LM_AUTO_MAIN_TECDOC_BRAND_TYPES_2'),
                	'3' => GetMessage('LM_AUTO_MAIN_TECDOC_BRAND_TYPES_3'),
                )
        ),
        'MODIFICATIONS_SET' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_MODIFICATIONS_SET'),
                "TYPE" => "LIST",
                "ADDITIONAL_VALUES" => "Y",
                "MULTIPLE" => "N",
                "DEFAULT"=>'default',
                'VALUES' => array_combine($sets_ids, $sets_ids),
        ),
        'HIDE_UNAVAILABLE' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_HIDE_UNAVAILABLE'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'N',
        ),
        'DISABLE_STATS' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_DISABLE_STATS'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'N',
        ),
        'INCLUDE_PARTS_IMAGES' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_INCLUDE_PARTS_IMAGES'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'Y',
        ),
        'ANTI_BOTS' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_MAIN_ANTI_BOT'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y',
        )
    ),
);
?>
