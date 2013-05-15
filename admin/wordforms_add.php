<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

$modulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");

if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
IncludeModuleLangFile(__FILE__);

ClearVars();


$wordform = new LinemediaAutoWordForm();

$ID = strval($ID);
if ($ID != '') {
    $group = $wordform->getGroupWordforms($ID);
}
$strError = "";
$bInitVars = false;
if ((strlen($save) > 0 || strlen($apply) > 0) && $REQUEST_METHOD == "POST" && check_bitrix_sessid()) {
    
    /*
     * Main
     */
    $groupid = strtoupper(trim(strval($_POST['group'])));
    $oldgroupid = strtoupper(trim(strval($_POST['old_group'])));
    if ($groupid == '') {
        $strError .= GetMessage('ERROR_GROUP') .'<br>';
    }
    
    $brand_titles = (array) $_POST['brand_titles'];
    $brand_titles = array_filter($brand_titles);
    
    if (count($brand_titles) == 0) {
        $strError .= GetMessage('ERROR_BRAND_TITLE') .'<br>';
    }
    
    if (strlen($strError) <= 0) {
        /*
         *
         */
        try {
            $wordform->setGroupWordForms($groupid, $brand_titles, $oldgroupid);
        } catch (Exception $e) {
            $strError .= $e->GetMessage() .'<br>';
        }
    } else {
        LocalRedirect("linemedia.auto_wordforms_add.php?lang=".LANG.GetFilterParams("filter_", false));
    }
    
    if (strlen($strError) > 0) {
        $bInitVars = True;
    }
    if (strlen($save) > 0 && strlen($strError) <= 0) {
        LocalRedirect("linemedia.auto_wordforms.php?lang=".LANG.GetFilterParams("filter_", false));
    }
    if (strlen($apply) > 0 && strlen($strError) <= 0) {
        LocalRedirect("linemedia.auto_wordforms_add.php?ID=".$groupid."&lang=".LANG.GetFilterParams("filter_", false));
    }
}



$sDocTitle = ($ID != '') ? str_replace("#ID#", $ID, GetMessage("LM_AUTO_WORDFORMS_EDIT_GROUP")) : GetMessage("LM_AUTO_WORDFORMS_NEW_GROUP");
$APPLICATION->SetTitle($sDocTitle);

$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.7.1/jquery.min.js');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>
<?
$aMenu = array(
        array(
                "TEXT" => GetMessage("LM_AUTO_WORDFORMS_GROUPS_LIST"),
                "LINK" => "/bitrix/admin/linemedia.auto_wordforms.php?lang=".LANG,
                "ICON" => "btn_list"
            )
    );

if ($ID != '' && $modulePermissions >= "W")
{
    $aMenu[] = array("SEPARATOR" => "Y");

    $aMenu[] = array(
            "TEXT" => GetMessage("LM_AUTO_WORDFORMS_NEW_GROUP"),
            "LINK" => "/bitrix/admin/linemedia.auto_wordforms_add.php?lang=".LANG.GetFilterParams("filter_"),
            "ICON" => "btn_new"
        );

    $aMenu[] = array(
            "TEXT" => GetMessage("LM_AUTO_WORDFORMS_DELETE_GROUP"),
            "LINK" => "javascript:if(confirm('".GetMessage("LM_AUTO_WORDFORMS_DELETE_GROUP_CONFIRM")."')) window.location='/bitrix/admin/linemedia.auto_wordforms.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
            "ICON" => "btn_delete"
        );
}


$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?php
if (strlen($strError) > 0)
    echo CAdminMessage::ShowMessage(Array("DETAILS"=>$strError, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SDEN_ERROR"), "HTML"=>true));?>

<form method="POST" action="/bitrix/admin/linemedia.auto_wordforms_add.php?lang=<?=LANG?>&ID=<?=htmlspecialchars($ID)?>" name="form1" id="lm-auto-down-add-task-frm">
<?= GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y" />
<input type="hidden" name="lang" value="<?= LANG ?>" />
<?= bitrix_sessid_post() ?>

<?
$aTabs = array(
    array("DIV" => "edit1", "TAB" => GetMessage("LM_AUTO_WORDFORMS_TAB_MAIN"), "ICON" => "linemedia.autodownloader.main", "TITLE" => GetMessage("LM_AUTO_WORDFORMS_TAB_MAIN")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<? $tabControl->BeginNextTab(); ?>

    <? if (0 && $ID != '') { //??? ???? ?? ?? ? ?? ? ?? ????????>
        <tr>
            <td width="40%"><?= GetMessage('LM_AUTO_WORDFORMS_ID') ?>:</td>
            <td width="60%"><?= htmlspecialcharsEx($ID) ?></td>
        </tr>
    <? } ?>
        <tr class="adm-detail-required-field">
            <td width="40%"><?=GetMessage('LM_AUTO_WORDFORMS_ID')?>:</td>
            <td width="60%">
                <input type="hidden" name="old_group" value="<?= htmlspecialcharsEx($ID) ?>">
                <input type="text" name="group" value="<?= htmlspecialcharsEx($ID) ?>" size="40">
            </td>
        </tr>
    <tr class="adm-detail-required-field">
        <td width="40%" valign="top"><?echo GetMessage("LM_AUTO_WORDFORMS_BRAND_TITLES");?>:</td>
        <td width="60%" valign="top">
            <div class="wordforms">
            <? if (count($group) == 0) $group[] = '';?>
            <? foreach ($group as $wordform) { ?>
                <div class="wordform">
                    <input type="text" name="brand_titles[]" value="<?= htmlspecialcharsEx($wordform) ?>" />
                    <input type="button" class="wordform-del" value="&ndash;" />
                </div>
            <? } ?>
            </div>
            <input type="button" class="wordform-add" value="+" />
        </td>
    </tr>

<? $tabControl->EndTab() ?>

<? $tabControl->End() ?>

<?
$tabControl->Buttons(
    array(
        "disabled" => ($modulePermissions < "W"),
        "back_url" => "/bitrix/admin/linemedia.auto_wordforms.php?lang=".LANG.GetFilterParams("filter_")
    )
);
?>

</form>

<script>
    $(document).ready(function() {
        var html = '<div class="wordform"><input type="text" name="brand_titles[]" value="" /> <input type="button" class="wordform-del" value="&ndash;" /></div>';
        
        $('.wordform-add').click(function(){
            $('.wordforms').append(html);
        });
        
        $('.wordforms').on('click', '.wordform-del', function(event){
            $(this).parent().remove();
        });
    });
</script>

<? require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php"); ?>