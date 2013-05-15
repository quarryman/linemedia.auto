<?IncludeModuleLangFile(__FILE__);?>

<form action="<?=$APPLICATION->GetCurPage()?>" method="post" id="lm_auto_main_frm">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="id" value="linemedia.auto">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="uninstall_step_id" value="finish">
	

    <?=CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	<p><?=GetMessage("MOD_UNINST_SAVE")?></p>
	<p>
	    <input type="checkbox" name="REMOVE_IBLOCKS" id="REMOVE_IBLOCKS" value="Y">
	    <label for="REMOVE_IBLOCKS"><?echo GetMessage("LM_AUTO_MAIN_REMOVE_IBLOCKS_DESC")?></label>
	</p>
	<p>
	    <input type="checkbox" name="REMOVE_PARTS" id="REMOVE_PARTS" value="Y">
	    <label for="REMOVE_PARTS"><?echo GetMessage("LM_AUTO_MAIN_REMOVE_PARTS_DESC")?></label>
	</p>
	<p>
	    <input type="checkbox" name="REMOVE_PRICELISTS" id="REMOVE_PRICELISTS" value="Y">
	    <label for="REMOVE_PRICELISTS"><?echo GetMessage("LM_AUTO_MAIN_REMOVE_PRICELISTS_DESC")?></label>
	</p>
	
	<input type="button" onclick="if(confirm('<?=GetMessage('LM_AUTO_MAIN_CONFIRM_REMOVE')?>')) $('#lm_auto_main_frm').submit()" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>
