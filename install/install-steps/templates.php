<? IncludeModuleLangFile(__FILE__) ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="lm_auto_main" class="well" method="post">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="linemedia.auto">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="install_step_id" value="templates">
    
    <?= BeginNote() ?>
    <?= GetMessage('LM_AUTO_MAIN_TEMPLATES_DESC') ?>
    <?= EndNote() ?>
    
    <p>
        <input type="submit" value="<?= GetMessage('LM_AUTO_MAIN_CONTINUE') ?>" />
    </p>
</form>