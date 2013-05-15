<? IncludeModuleLangFile(__FILE__) ?>

<tr>
    <td colspan="2">
        <?= BeginNote();?>
            <?= GetMessage('LM_AUTO_MAIN_PASSWORDS_DESC') ?>
        <?= EndNote(); ?>
    </td>
</tr>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_SUPPLIERS_LIST_GROUP_TITLE') ?></td>
</tr>
<tr>
    <td colspan="2">
        <?= BeginNote() ?>
            <?= GetMessage('LM_AUTO_MAIN_SUPPLIERS_LIST_DESC') ?>
        <?= EndNote() ?>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN">
            <?= GetMessage('LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN') ?>:
        </label>
    </td>
    <td>
        <input type="text" autocomplete="off" name="LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN" id="LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN" size="40" maxlength="255" value="<?= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN', '') ?>" />
    </td>
</tr>

<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD">
            <?= GetMessage('LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD') ?>:
        </label>
    </td>
    <td>
        <input type="password" autocomplete="off" name="LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD" id="LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD" size="40" maxlength="255" value="<?= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD', '') ?>" />
    </td>
</tr>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_ACCESS_REMOTE_GROUP_TITLE') ?></td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH">
            <?= GetMessage('LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH') ?>:
        </label>
    </td>
    <td>
        <input type="checkbox" name="LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH" id="LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH" value="Y" <?= $LM_AUTO_MAIN_ACCESS_REMOTE_SEARCH != 'N' ? 'checked="checked"' : '' ?> />
    </td>
</tr>