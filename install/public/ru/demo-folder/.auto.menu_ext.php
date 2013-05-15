<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();



/*
 * событие для других модулей
 */
$events = GetModuleEvents("linemedia.auto", "OnPublicMenuBuild");
while ($arEvent = $events->Fetch()) {
    try {
	    ExecuteModuleEventEx($arEvent, array(&$aMenuLinks));
	} catch (Exception $e) {
	    throw $e;
	}
}