<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!defined("WIZARD_DEFAULT_SITE_ID") && !empty($_REQUEST["wizardSiteID"])) {
    define("WIZARD_DEFAULT_SITE_ID", strval($_REQUEST["wizardSiteID"]));
}

$arWizardDescription = array(
    "NAME"             => GetMessage("LM_AUTO_SITE_WIZARD_NAME"), 
    "DESCRIPTION"      => GetMessage("LM_AUTO_SITE_WIZARD_DESC"), 
    "VERSION"          => "1.0.0",
    "WIZARD_TYPE"      => "INSTALL",
    "START_TYPE"       => "WINDOW",
    "PARENT"           => "wizard_sol",
    "IMAGE"            => "images/".LANGUAGE_ID."/auto.gif",
    "TEMPLATES"        => array(
                                array('SCRIPT' => 'scripts/template.php', 'CLASS' => 'WizardTemplate')
    ),
    "STEPS"            => array(
        "StartStep",
        "SelectSiteStep",
        "SelectTemplateStep",
        "SelectThemeStep",
        "SiteSettingsStep",
        "DataInstallStep",
        "FinishStep"
    ),
);

?>