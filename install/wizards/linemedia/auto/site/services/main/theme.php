<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!defined("WIZARD_TEMPLATE_ID")) {
	return;
}

$newTemplateName = WIZARD_TEMPLATE_ID;

$templateDir = BX_PERSONAL_ROOT."/templates/".$newTemplateName;

CopyDirFiles(
	WIZARD_THEME_ABSOLUTE_PATH,
	$_SERVER["DOCUMENT_ROOT"].$templateDir,
	$rewrite = true, 
	$recursive = true,
	$delete_after_copy = false,
	$exclude = "description.php"
);

COption::SetOptionString("main", "wizard_".WIZARD_TEMPLATE_ID."_theme_id", WIZARD_THEME_ID, "", WIZARD_SITE_ID);

?>