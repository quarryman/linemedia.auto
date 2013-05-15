<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("LM_AUTO_MAIN_PERSONAL_NOTEPAD_NAME"),
    "DESCRIPTION" => GetMessage("LM_AUTO_PERSONAL_NOTEPAD_DESCRIPTION"),
    "ICON" => "/images/notepad.ico",
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
        "CHILD" => array(
            "ID" => GetMessage("LM_AUTO_MAIN_PERSONAL_NOTEPAD_NAME"),
            "NAME" => GetMessage("LM_AUTO_MAIN_PERSONAL_NOTEPAD_NAME"),
            "SORT" => 10,
        )
    )
);
