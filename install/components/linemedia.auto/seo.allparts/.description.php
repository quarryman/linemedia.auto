<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_NAME"),
    "DESCRIPTION" => GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_DESCRIPTION"),
    "ICON" => "/images/allparts.png",
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => GetMessage("LM_AUTO_MAIN_SECTION"),
        "CHILD" => array(
            "ID" => GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_NAME"),
            "NAME" => GetMessage("LM_AUTO_MAIN_SEO_ALLPARTS_NAME"),
            "SORT" => 10,
        )
    )
);
