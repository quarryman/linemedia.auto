<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"linemedia.auto:personal.request.vin.iblock.list", 
	"", 
	array(
		"TICKET_EDIT_TEMPLATE"    => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["ticket_edit"],
		"TICKETS_PER_PAGE"        => $arParams["TICKETS_PER_PAGE"],
		"SET_PAGE_TITLE"          => $arParams["SET_PAGE_TITLE"],
		"TICKET_ID_VARIABLE"      => $arResult["ALIASES"]["ID"],
                "TICKET_SORT_ORDER"       => $arParams["TICKET_SORT_ORDER"],
    ),
    $component,
	array("HIDE_ICONS" => "Y")
);