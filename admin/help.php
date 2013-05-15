<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$modulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}
            
$APPLICATION->SetTitle(GetMessage("LM_AUTO_HELP_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>


<?=BeginNote()?>
<?=GetMessage('LM_AUTO_HELP_TOP_MESSAGE')?>
<?=EndNote()?>


<iframe class="lm-auto-frame" src="http://docs.linemedia.ru/"></iframe>


<?
require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');