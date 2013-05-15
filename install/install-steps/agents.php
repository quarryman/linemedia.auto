<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? IncludeModuleLangFile(__FILE__); ?>


<?= CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_MAIN_IBLOCKS_STEP_SUCCESS"), 'TYPE' => 'OK')); ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="lm_auto_main" class="well" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.auto" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="finish" />
	
	<h2><?= GetMessage('LM_AUTO_MAIN_AGENT_HEADER') ?></h2>
	
	<?
	echo BeginNote();
	echo GetMessage('LM_AUTO_MAIN_AGENT_DESC');
	echo EndNote();
	
	if (defined('BX_CRONTAB') && BX_CRONTAB == true) {
	    echo CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_MAIN_AGENT_CRON_OK"), 'HTML' => true, 'TYPE' => 'OK'));
	} else {
	    echo CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_MAIN_AGENT_CRON_ERROR"), 'HTML' => true));
	    echo '<div class="lm-auto-agents-instruction">' . GetMessage("LM_AUTO_MAIN_AGENT_INSTRUCTIONS") . '</div>';
	}
	
	?>
    
    <p>
        <input type="submit" value="<?=GetMessage('LM_AUTO_MAIN_CONTINUE')?>" />
    </p>
</form>
