<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LM_AUTO_MAIN_PERSONAL_REQUEST_VIN_IBLOCK_NAME"),
	"DESCRIPTION" => GetMessage("LM_AUTO_MAIN_PERSONAL_REQUEST_VIN_IBLOCK_DESCRIPTION"),
	"ICON" => "/images/component_icon.gif",
        "CACHE_PATH" => "Y",
        "SORT" => 10,
	"PATH" => array(
		"ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
		"CHILD" => array(
			"ID" => "LM_AUTO_MAIN_VIN",
			"NAME" => GetMessage("LM_AUTO_MAIN_VIN_SUB_SECTION"),
			"SORT" => 10,
		),
	),
);
