<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arServices = Array(
    "main" => Array(
        "NAME" => GetMessage("SERVICE_MAIN_SETTINGS"),
        "STAGES" => Array(
            "module.php",   // Install module
            "files.php",    // Copy bitrix files
            "template.php", // Install template
            "theme.php",    // Install theme
            "areas.php",    // Install areas
        ),
    ),
);
?>