<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("LM_AUTO_MAIN_PERSONAL_TRANSACTIONS_NAME"),
    "DESCRIPTION" => GetMessage("LM_AUTO_PERSONAL_TRANSACTIONS_DESCRIPTION"),
    "ICON" => "/images/transactions.png",
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
        "CHILD" => array(
            "ID" => GetMessage("LM_AUTO_MAIN_PERSONAL_TRANSACTIONS_NAME"),
            "NAME" => GetMessage("LM_AUTO_MAIN_PERSONAL_TRANSACTIONS_NAME"),
            "SORT" => 10,
        )
    )
);
