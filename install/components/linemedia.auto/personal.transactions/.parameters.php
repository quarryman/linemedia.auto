<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        'ADD_SECTION_CHAIN' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_TRANSACTIONS_ADD_CHAIN'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
        'SET_TITLE_TRANSACTIONS' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_TRANSACTIONS_SET_TITLE'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
        "TITLE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_TRANSACTIONS_TITLE'),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT" => GetMessage('LM_AUTO_PERSONAL_TRANSACTIONS_TITLE_DEFAULT')
        ),
        'INIT_JQUERY' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_TRANSACTIONS_INIT_JQUERY'),
            "TYPE" => "CHECKBOX",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'Y'
        ),
        'ORDERS_PATH' => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('LM_AUTO_PERSONAL_TRANSACTIONS_ORDERS_PATH'),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "N",
            "MULTIPLE" => "N",
            "DEFAULT"=>'/auto/orders/'
        ),
    ),
);
