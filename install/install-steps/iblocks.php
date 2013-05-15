<?IncludeModuleLangFile(__FILE__);?>


<?
if($_REQUEST['DEMO_FOLDER_INSTALL'] == 'Y')
    echo CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_MAIN_DEMO_FOLDER_STEP_SUCCESS"), 'TYPE' => 'OK'));
?>


<form action="<?= $APPLICATION->GetCurPage() ?>" id="lm_auto_main" class="well" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.auto" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="agents" />
	
	
	<?
	echo BeginNote();
	echo GetMessage('LM_AUTO_MAIN_IBLOCKS_DESC');
	echo EndNote();
	?>
	
    <p>
        <input type="submit" value="<?=GetMessage('LM_AUTO_MAIN_INSTALL_FOLDER')?>" />
    </p>
</form>
