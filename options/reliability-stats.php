<?
IncludeModuleLangFile(__FILE__);
$statuses = LinemediaAutoOrder::getStatusesList();
?>
<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_STAT_PREFS') ?></td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_STATUS_REQUESTED">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_REQUESTED') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $status_requested = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_REQUESTED','R'); ?>
            <select style="width: 100%;" id="LM_AUTO_MAIN_STATUS_REQUESTED" name="LM_AUTO_MAIN_STATUS_REQUESTED">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if($status['ID'] == $status_requested) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_STATUS_REJECTED">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_REJECTED') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $status_requested = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_REJECTED','C'); ?>
            <select style="width: 100%;" id="LM_AUTO_MAIN_STATUS_REJECTED" name="LM_AUTO_MAIN_STATUS_REJECTED">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if($status['ID'] == $status_requested) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_STATUS_STORED">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_STORED') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $status_requested = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_STORED','S'); ?>
            <select style="width: 100%;" id="LM_AUTO_MAIN_STATUS_STORED" name="LM_AUTO_MAIN_STATUS_STORED">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if($status['ID'] == $status_requested) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>