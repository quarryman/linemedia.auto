<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"linemedia.auto:personal.request.vin.iblock.edit", 
	"", 
	Array(
		"ID" => $arResult["VARIABLES"]["ID"],
		"TICKET_LIST_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["ticket_list"],
		"TICKET_EDIT_TEMPLATE" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["ticket_edit"],
		"SET_PAGE_TITLE" =>$arParams["SET_PAGE_TITLE"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);