<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("iblock")) {
    return;
}

$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE_ID"], "ACTIVE"=>"Y"));
while ($arr = $rsIBlock->Fetch()) {
    $arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arComponentParameters = array(
    "PARAMETERS" => array(
        "SEF_FOLDER" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_PATH_ROOT'),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT" => '/auto/tecdoc/'
        ),
        "SEF_MODE" => array(
        ),
        "SEARCH_URL" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_PATH_SEARCH'),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT" => "/auto/search/#ARTICLE_ID#/#BRAND_ID#/"
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
        'ADD_SEO_DATA' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_ADD_SEO'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'Y'
        ),
        'TECDOC_NEW_URL' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_GROUP'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'N'
        ),
        'SHOW_CAR_BRANDS_IN_URI' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_REPLACE_IDS'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'N'
        ),
        "CACHE_TIME" => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_CACHE_TIME'),
                "TYPE" => "STRING",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT" => '3600'
        ),
    ),
);
?>
