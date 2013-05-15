<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LM_AUTO_BRANCHES_PERSONAL_DEALER_REGISTER_NAME"),
	"DESCRIPTION" => GetMessage("LM_AUTO_BRANCHES_PERSONAL_DEALER_REGISTER_DESCRIPTION"),
	"ICON" => "/images/user_register.gif",
        "CACHE_PATH" => "Y",
        "SORT" => 10,
	"PATH" => array(
		"ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
		"CHILD" => array(
			"ID" => "LM_AUTO_MAIN_REG",
			"NAME" => GetMessage("LM_AUTO_MAIN_REG_SUB_SECTION"),
			"SORT" => 10,
		),
	),
);
