<?IncludeModuleLangFile(__FILE__);?>

<?= CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_MAIN_IBLOCKS_STEP_SUCCESS"), 'TYPE' => 'OK')); ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="lm_auto_main" class="well" method="post">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANG ?>" />
    <input type="hidden" name="id" value="linemedia.auto" />
    <input type="hidden" name="install" value="Y" />
    <input type="hidden" name="install_step_id" value="agents" />
    
    
    <?= BeginNote() ?>
    <?= GetMessage('LM_AUTO_MAIN_IBLOCKS_DESC') ?>
    <?= EndNote() ?>
    
    <p>
        <input type="submit" value="<?= GetMessage('LM_AUTO_MAIN_CONTINUE') ?>" />
    </p>
</form>