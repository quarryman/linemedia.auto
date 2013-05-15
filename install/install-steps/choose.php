<? IncludeModuleLangFile(__FILE__); ?>

<?= BeginNote() ?>
<?= GetMessage('LM_AUTO_MAIN_REGISTER_CHOOSE_DESC') ?>
<?= EndNote() ?>
    
<table class="list-table">
    <tr class="head">
        <td colspan="2"><?= GetMessage("LM_AUTO_MAIN_REGISTER_CHOOSE_HEADER") ?></td>
    </tr>
    <tr>
        <td width="50%" align="right">
            <form action="<?= $APPLICATION->GetCurPage() ?>" id="lm_auto_main" method="post">
                <?= bitrix_sessid_post() ?>
                <input type="hidden" name="lang" value="<?= LANG ?>" />
                <input type="hidden" name="id" value="linemedia.auto" />
                <input type="hidden" name="install" value="Y" />
                <input type="hidden" name="install_step_id" value="api" />
                <input type="submit" id="CHOOSE_MODULE" value="<?= GetMessage('LM_AUTO_MAIN_REGISTERING_CHHOSE_MODULE') ?>" />
            </form>
        </td>
        <td>
            <form action="/bitrix/admin/wizard_install.php" method="get">
                <?= bitrix_sessid_post() ?>
                <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>" />
                <input type="hidden" name="wizardName" value="linemedia.auto:linemedia:auto" />
                <input type="submit" id="CHOOSE_WIZARD" value="<?= GetMessage('LM_AUTO_MAIN_REGISTERING_CHHOSE_WIZARD') ?>" />
            </form>
        </td>
    </tr>
</table>