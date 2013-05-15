<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!defined("WIZARD_SITE_ID")) {
	return;
}
if (!defined("WIZARD_SITE_DIR")) {
	return;
}

$path = str_replace("//", "/", WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/");

CopyDirFiles($path, $_SERVER['DOCUMENT_ROOT'].WIZARD_SITE_DIR, true, true);

require_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.auto/include.php');

LinemediaAutoFileHelper::fileStrReplace($_SERVER['DOCUMENT_ROOT'].WIZARD_SITE_DIR, '/auto/', WIZARD_SITE_DIR.'auto/');