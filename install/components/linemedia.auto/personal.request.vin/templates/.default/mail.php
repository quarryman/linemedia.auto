<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent(
    'linemedia.auto:personal.request.vin.mail', 
    '', 
    array(),
    $component,
    array('HIDE_ICONS' => 'Y')
);