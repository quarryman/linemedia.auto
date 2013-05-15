<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/install/wizard_sol/wizard.php");

/*
 * Приветствие.
 */
class StartStep extends CWizardStep
{
    function InitStep()
    {
        $this->SetStepID("start");
        $this->SetTitle(GetMessage("LM_AUTO_MAIN_WZ_START_STEP_TITLE"));
        $this->SetSubTitle(GetMessage("LM_AUTO_MAIN_WZ_START_STEP_SUBTITLE"));
        //$this->SetNextStep("license_agreement");
        //$this->SetNextCaption(GetMessage("NEXT_BUTTON"));
        $this->SetNextStep(defined("WIZARD_DEFAULT_SITE_ID") ?  "select_template" : "select_site");
        $this->SetNextCaption(GetMessage("LICENSE_AGREE"));
    }
    
    function ShowStep()
    {
        $text = GetMessage('LM_AUTO_MAIN_WZ_START_HELLO');
        $this->content = <<<HTML
<div>{$text}</div>
HTML;
;
    }
}


/*
 * Лицензионное соглашение.

class LicenseStep extends CWizardStep
{
    function InitStep()
    {
        $this->SetStepID("license_agreement");
        $this->SetTitle(GetMessage("LM_AUTO_MAIN_WZ_LICENSE_STEP_TITLE"));
        $this->SetSubTitle(GetMessage("LM_AUTO_MAIN_WZ_LICENSE_STEP_SUBTITLE"));
        $this->SetPrevStep("start");
        $this->SetPrevCaption(GetMessage("PREVIOUS_BUTTON"));
        $this->SetNextStep(defined("WIZARD_DEFAULT_SITE_ID") ?  "select_template" : "select_site");
        $this->SetNextCaption(GetMessage("LICENSE_AGREE"));
    }
    
    function ShowStep()
    {
        $licenseFile = $_SERVER['DOCUMENT_ROOT']."/bitrix/wizards/linemedia/auto/lang/ru/license.html";
        $agreement = file_get_contents($licenseFile);
        $this->content = <<<HTML
<div class="license">{$agreement}</div>
HTML;
;
    }
}
*/


/*
 * Сайт.
 */
class SelectSiteStep extends CSelectSiteWizardStep
{
    function InitStep()
    {
        parent::InitStep();

        $wizard =& $this->GetWizard();
        $wizard->solutionName = "auto";

        $this->SetPrevStep("start");
        //$this->SetPrevStep("license_agreement");
        $this->SetPrevCaption(GetMessage("PREVIOUS_BUTTON"));
    }
}


/*
 * Шаблон.
 */
class SelectTemplateStep extends CSelectTemplateWizardStep
{
    
}


/*
 * Тема.
 */
class SelectThemeStep extends CSelectThemeWizardStep
{

}


/*
 * Установка решения.
 */
class SiteSettingsStep extends CSiteSettingsWizardStep
{
    function InitStep()
    {
        $wizard = &$this->GetWizard();
        $wizard->solutionName = "auto";
        parent::InitStep();
        
        $templateID = $wizard->GetVar("templateID");
        $themeID = $wizard->GetVar($templateID."_themeID");
        
        $templatePath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$templateID."/";
        $wizardTemplatePath = "/bitrix/wizards/linemedia/auto/site/templates/".$templateID;
        
        $siteLogo = $this->GetFileContentImgSrc($templatePath."include/logo.php", $wizardTemplatePath."/themes/".$themeID."/images/logo.png");
        if (!file_exists($templatePath."include/logo.gif")) {
            $siteLogo = $wizardTemplatePath."/themes/".$themeID."/images/logo.png";
        }
        $siteImage = $this->GetFileContentImgSrc($templatePath."include/site_image.php", $wizardTemplatePath."/themes/".$themeID."/images/im1.png");
        
        $wizard->SetDefaultVars(
            array(
                "phoneCode"     => GetMessage('LM_AUTO_MAIN_DEFAULT_PHONE_CODE'),
                "phoneNumber"   => GetMessage('LM_AUTO_MAIN_DEFAULT_PHONE_NUMBER'),
                "siteLogo"      => $siteLogo,
                "siteImage"     => $siteImage,
                "siteCopy"      => GetMessage('LM_AUTO_MAIN_DEFAULT_SITE_COPY'),
            )
        );
    }
    
    
    function ShowStep()
    {
        $wizard =& $this->GetWizard();
        $siteLogo = $wizard->GetVar("siteLogo", true);
        $siteLogoShow = CFile::ShowImage($siteLogo, 170, 60, "border=0 vspace=15");
        $siteImage = $wizard->GetVar("siteImage", true);
        $siteImageShow = CFile::ShowImage($siteImage, 320, 200, "border=0 vspace=15");
        
        $siteCompanyName        = GetMessage('LM_AUTO_MAIN_YOUR_SITE_COMPANY_NAME');
        $phoneMess              = GetMessage('LM_AUTO_MAIN_YOUR_PHONE');
        $siteCopyMess           = GetMessage('LM_AUTO_MAIN_YOUR_SITE_COPY');
        $logoMess               = GetMessage('LM_AUTO_MAIN_YOUR_LOGO');
        $siteImageMess          = GetMessage('LM_AUTO_MAIN_YOUR_SITE_IMAGE');
        $siteShedule            = GetMessage('LM_AUTO_MAIN_YOUR_SITE_SHEDULE');
        
        $wizard->SetDefaultVar("siteCopy", "&copy; ");
        
        $this->content = <<<HTML
            <table width="100%" cellspacing="10">
                <tr>
                    <td>{$siteCompanyName}</td>
                    <td>
                        {$this->ShowInputField("text", "siteCompanyName", array("id" => "siteCompanyName", "style"=>"width: 254px;"))}
                    </td>
                </tr>
                <tr>
                    <td>{$phoneMess}</td>
                    <td>
                        {$this->ShowInputField("text", "phoneCode", array("id" => "phoneCode", "style"=>"width: 50px;"))}
                        {$this->ShowInputField("text", "phoneNumber", array("id" => "phoneNumber", "style"=>"width: 200px;"))}
                    </td>
                </tr>
                <tr>
                    <td>{$siteCopyMess}</td>
                    <td>
                        {$this->ShowInputField("textarea", "siteCopy", array("id" => "siteCopy", "style" => "width:100%", "rows" => "3"))}
                    </td>
                </tr>
                <tr>
                    <td>{$logoMess}</td>
                    <td>
                        {$siteLogoShow}
                        <br/>
                        {$this->ShowFileField("siteLogo", array("show_file_info" => "N", "id" => "site-logo"))}
                    </td>
                </tr>
                <tr>
                    <td>{$siteImageMess}</td>
                    <td>
                        {$siteImageShow}
                        <br/>
                        {$this->ShowFileField("siteImage", array("show_file_info" => "N", "id" => "site-image"))}
                    </td>
                </tr>
                <tr>
                    <td>{$siteShedule}</td>
                    <td>
                        {$this->ShowInputField("text", "siteShedule", array("id" => "siteShedule", "style"=>"width: 254px;"))}
                    </td>
                </tr>
            </table>
            
            {$this->ShowHiddenField("install_news","Y")}
            {$this->ShowHiddenField("tableExist", $tableExist)}
            
HTML;
    }
    
    
    function OnPostForm()
    {
        $wizard = &$this->GetWizard();
        
        $bError = false;
        if (!$bError) {
            $res = $this->SaveFile("siteLogo", array("extensions" => "gif,jpg,jpeg,png", "max_height" => 60, "max_width" => 170, "make_preview" => "Y"));
            $res = $this->SaveFile("siteImage", array("extensions" => "gif,jpg,jpeg,png", "max_height" => 200, "max_width" => 320, "make_preview" => "Y"));
        }
    }
}


/*
 * Установка данных.
 */
class DataInstallStep extends CDataInstallWizardStep
{
    function CorrectServices(&$arServices)
    {
        $wizard = &$this->GetWizard();
        if ($wizard->GetVar("installDemoData") != "Y") {
            
        }
    }
}


/*
 * Заключение.
 */
class FinishStep extends CFinishWizardStep
{
    
}


