<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        'ADD_SECTION_CHAIN' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_MAIN_SEO_ALLPARTS_ADD_CHAIN'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
        'SET_TITLE_ALLPARTS' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_MAIN_SEO_ALLPARTS_SET_TITLE'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
        'SET_DESCRIPTION_ALLPARTS' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_MAIN_SEO_ALLPARTS_SET_DESCRIPTION'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
        'SET_KEYWORDS_ALLPARTS' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_MAIN_SEO_ALLPARTS_SET_KEYWORDS'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
        'INIT_JQUERY' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_MAIN_SEO_ALLPARTS_TITLE_INIT_JQUERY'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
        "PARTS_PER_PAGE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_MAIN_SEO_ALLPARTS_PARTS_PER_PAGE'),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT" => '40'
        ),
    ),
);
