<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SBB_DEFAULT_TEMPLATE_NAME"),
	"DESCRIPTION" => GetMessage("SBB_DEFAULT_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/sale_basket.gif",
	"PATH" => array(
		"ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
		"CHILD" => array(
			"ID" => "LM_AUTO_MAIN_BASKET",
			"NAME" => GetMessage("LM_AUTO_MAIN_BASKET_SUB_SECTION"),
			"SORT" => 10,
		),
	),
);
?>