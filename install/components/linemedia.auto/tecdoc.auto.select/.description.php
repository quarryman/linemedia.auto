<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("LM_AUTO_MAIN_TECDOC_AUTO_NAME"),
    "DESCRIPTION" => GetMessage("LM_AUTO_MAIN_TECDOC_AUTO_DESCRIPTION"),
    "ICON" => "/images/component_icon.gif",
        "CACHE_PATH" => "Y",
        "SORT" => 10,
    "PATH" => array(
        "ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
        "CHILD" => array(
            "ID" => "LM_AUTO_MAIN_TECDOC",
            "NAME" => GetMessage("LM_AUTO_MAIN_TECDOC_SUB_SECTION"),
            "SORT" => 10,
        ),
    ),
);
