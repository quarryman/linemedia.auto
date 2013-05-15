<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage('LM_AUTO_MAIN_SUPPORT_TICKET_LIST_NAME'),
	"DESCRIPTION" => GetMessage('LM_AUTO_MAIN_SUPPORT_TICKET_LIST_DESCRIPTION'),
	"ICON" => "/images/support_list.gif",
    "PATH" => array(
        "ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
        "CHILD" => array(
            "ID" => "LM_AUTO_MAIN_VIN",
            "NAME" => GetMessage("LM_AUTO_MAIN_VIN_SUB_SECTION"),
            "SORT" => 10,
        ),
    ),
);


?>