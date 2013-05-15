<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent(
	"linemedia.auto:personal.request.vin.edit", 
	"", 
	Array(
		"ID" => $arResult["VARIABLES"]["ID"],
		"TICKET_LIST_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["ticket_list"],
		"TICKET_EDIT_TEMPLATE" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["ticket_edit"],
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
		"MESSAGE_SORT_ORDER" => $arParams["MESSAGE_SORT_ORDER"],
		"MESSAGE_MAX_LENGTH" => $arParams["MESSAGE_MAX_LENGTH"],
		"SET_PAGE_TITLE" =>$arParams["SET_PAGE_TITLE"],
		"SHOW_COUPON_FIELD" => "N",
        "CATEGORY_ID" => $arParams['CATEGORY_ID'],
        "CHECK_ACCESS" => "N",
	),
	$component,
	array("HIDE_ICONS" => "Y")
);