<?IncludeModuleLangFile(__FILE__);?>

<tr>
    <td colspan="2">
        <?= BeginNote();?>
            <?= GetMessage('LM_AUTO_MAIN_DB_REMOTE_DESC') ?>
        <?= EndNote(); ?>
    </td>
</tr>

<? $use_bitrix_db = (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_USE_BITRIX_DB', 'N') == 'Y'); ?>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_USE_BITRIX_DB">
            <?= GetMessage('LM_AUTO_MAIN_USE_BITRIX_DB') ?>:
        </label>
    </td>
    <td valign="top">
        <input
            type="checkbox"
            name="LM_AUTO_MAIN_USE_BITRIX_DB"
            id="LM_AUTO_MAIN_USE_BITRIX_DB"
            value="Y"
            <?= ($use_bitrix_db) ? ('checked="checked"') : ('') ?>"
        />
    </td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_DB_HOST"><?= GetMessage('LM_AUTO_MAIN_DB_HOST') ?>:
    </td>
    <td valign="top">
        <input class="db-settings" <?= ($use_bitrix_db) ? ('disabled="disabled') : ('') ?> size="50" type="text" name="LM_AUTO_MAIN_DB_HOST" id="LM_AUTO_MAIN_DB_HOST" value="<?= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_DB_HOST', '' ) ?>" />
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_DB_PORT"><?= GetMessage('LM_AUTO_MAIN_DB_PORT') ?>:
    </td>
    <td valign="top">
        <input class="db-settings" <?= ($use_bitrix_db) ? ('disabled="disabled') : ('') ?> size="50" type="text" name="LM_AUTO_MAIN_DB_PORT" id="LM_AUTO_MAIN_DB_PORT" value="<?= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_DB_PORT', '' ) ?>" />
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_DB_NAME"><?= GetMessage('LM_AUTO_MAIN_DB_NAME') ?>:
    </td>
    <td valign="top">
        <input class="db-settings" <?= ($use_bitrix_db) ? ('disabled="disabled') : ('') ?> size="50" type="text" name="LM_AUTO_MAIN_DB_NAME" id="LM_AUTO_MAIN_DB_NAME" value="<?= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_DB_NAME', '' ) ?>" />
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_DB_USER"><?= GetMessage('LM_AUTO_MAIN_DB_USER') ?>:
    </td>
    <td valign="top">
        <input class="db-settings" <?= ($use_bitrix_db) ? ('disabled="disabled') : ('') ?> size="50" type="text" name="LM_AUTO_MAIN_DB_USER" id="LM_AUTO_MAIN_DB_USER" value="<?= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_DB_USER', '' ) ?>" />
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_DB_PASS"><?= GetMessage('LM_AUTO_MAIN_DB_PASS') ?>:
    </td>
    <td valign="top">
        <input class="db-settings" <?= ($use_bitrix_db) ? ('disabled="disabled') : ('') ?> size="50" type="text" name="LM_AUTO_MAIN_DB_PASS" id="LM_AUTO_MAIN_DB_PASS" value="<?= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_DB_PASS', '' ) ?>" />
    </td>
</tr>
