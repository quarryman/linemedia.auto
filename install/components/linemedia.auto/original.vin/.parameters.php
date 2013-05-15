<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        'SET_TITLE' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_SET_TITLE'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'Y'
        ),
        'CATALOGS_PATH' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_CATALOGS_PATH'),
                "TYPE" => "STRING",
                "MULTIPLE" => "N",
                "DEFAULT"=>'/auto/original/#BRAND#/#MODEL#/',
        ),
        
        'DISABLE_STATS' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_DISABLE_STATS'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'N',
        ),
        'INCLUDE_JQUERY' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_INCLUDE_JQUERY'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'Y',
        ),
    ),
);
?>
