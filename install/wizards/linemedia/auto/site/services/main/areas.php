<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (!defined("WIZARD_TEMPLATE_ID"))
	return;

function writeToAreasFile($fn, $text)
{
	if (file_exists($fn) && !is_writable($fn) && defined("BX_FILE_PERMISSIONS")) {
		@chmod($abs_path, BX_FILE_PERMISSIONS);
    }
	$fd = @fopen($fn, "wb");
	if (!$fd) {
		return false;
    }
	if (false === fwrite($fd, $text)) {
		fclose($fd);
		return false;
	}
    
	fclose($fd);

	if (defined("BX_FILE_PERMISSIONS")) {
		@chmod($fn, BX_FILE_PERMISSIONS);
    }
}

$wizard = &$this->GetWizard();

$bitrixTemplateDir = $_SERVER['DOCUMENT_ROOT'];//$_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."";

$phone = $wizard->GetVar('phoneCode').' '.$wizard->GetVar('phoneNumber');

writeToAreasFile($bitrixTemplateDir."/include/phone.php", $phone);

writeToAreasFile($bitrixTemplateDir."/include/copyright.php", $wizard->GetVar('siteCopy'));

writeToAreasFile($bitrixTemplateDir."/include/company_name.php", $wizard->GetVar('siteCompanyName'));

writeToAreasFile($bitrixTemplateDir."/include/schedule.php", $wizard->GetVar('siteShedule'));


$siteLogo = $wizard->GetVar("siteLogo");
$sWizardTemplatePath = WizardServices::GetTemplatesPath(WIZARD_RELATIVE_PATH."/site")."/".WIZARD_TEMPLATE_ID."/";
$sTemplatePath = BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."/";


if ($siteLogo > 0) {
	$ff = CFile::GetByID($siteLogo);
	if ($zr = $ff->Fetch()) {
		$strOldFile = str_replace("//", "/", WIZARD_SITE_ROOT_PATH."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$zr["SUBDIR"]."/".$zr["FILE_NAME"]);
        @copy($strOldFile, $_SERVER['DOCUMENT_ROOT'].$sTemplatePath."images/logo.png");
        //CopyDirFiles($strOldFile, $_SERVER['DOCUMENT_ROOT'].$sTemplatePath.'images');
        writeToAreasFile($_SERVER['DOCUMENT_ROOT']."/include/logo.php", '<img src="'.$sTemplatePath.'images/logo.png" alt="'.$wizard->GetVar('siteCompanyName').'" />');
		CFile::Delete($siteLogo);
	}
} elseif (!file_exists($sTemplatePath."include/logo.php")) {
    writeToAreasFile($_SERVER['DOCUMENT_ROOT'].$sTemplatePath."include/logo.php", '<img src="'.$sTemplatePath.'images/logo.png"  />');
}

if ($siteImage > 0) {
	$ff = CFile::GetByID($siteImage);
	if ($zr = $ff->Fetch()) {
		$strOldFile = str_replace("//", "/", WIZARD_SITE_ROOT_PATH."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$zr["SUBDIR"]."/".$zr["FILE_NAME"]);
        @copy($strOldFile, $_SERVER['DOCUMENT_ROOT'].$sTemplatePath."images/im1.png");
        //CopyDirFiles($strOldFile, $_SERVER['DOCUMENT_ROOT'].$sTemplatePath.'images');
        writeToAreasFile($_SERVER['DOCUMENT_ROOT']."/include/site_image.php", '<img src="'.$sTemplatePath.'images/im1.png"  />');
		CFile::Delete($siteImage);
	}
} elseif (!file_exists($sTemplatePath."include/site_image.php")) {
    writeToAreasFile($_SERVER['DOCUMENT_ROOT'].$sTemplatePath."include/site_image.php", '<img src="'.$sTemplatePath.'images/im1.png"  />');
}

