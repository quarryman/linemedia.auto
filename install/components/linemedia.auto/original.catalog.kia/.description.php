<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LM_AUTO_MAIN_ORIGINAL_CATALOG_KIA_NAME"),
	"DESCRIPTION" => GetMessage("LM_AUTO_MAIN_ORIGINAL_CATALOG_KIA_DESCRIPTION"),
	"ICON" => "/images/component_icon.gif",
        "CACHE_PATH" => "Y",
        "SORT" => 10,
	"PATH" => array(
		"ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
		"CHILD" => array(
			"ID" => "LM_AUTO_MAIN_ORIGINAL",
			"NAME" => GetMessage("LM_AUTO_MAIN_ORIGINAL_SUB_SECTION"),
			"SORT" => 10,
		),
	),
);
