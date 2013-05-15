<? $APPLICATION->SetAdditionalCSS('/bitrix/modules/linemedia.auto/interface/colorpicker/css/colorpicker.css') ?>
<? $APPLICATION->AddHeadScript('/bitrix/modules/linemedia.auto/interface/colorpicker/colorpicker.js') ?>

<? $statuses = LinemediaAutoOrder::getStatusesList(); ?>

<? if (!empty($statuses)) { ?>
    <?
        $inputs = array();
        foreach ($statuses as $status) {
            $inputs []= '#LM_AUTO_MAIN_STATUS_COLOR_'.$status['ID'];
            $inputs []= '#LM_AUTO_MAIN_PUBLIC_STATUS_COLOR_'.$status['ID'];
        }
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('<?= implode(', ', $inputs) ?>').ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    $(el).val('#' + hex.toUpperCase());
                    $(el).ColorPickerHide();
                    $(el).siblings('div.color').css('backgroundColor', '#' + hex);
                },
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                },
                onShow: function (colpkr) {
                    $(colpkr).fadeIn(300);
                    return false;
                },
                onHide: function (colpkr) {
                    $(colpkr).fadeOut(300);
                    return false;
                },
            })
            .bind('keyup', function() {
                $(this).ColorPickerSetColor(this.value);
                $(this).siblings('div.color').css('backgroundColor', this.value);
            });
        });
    </script>
<? } ?>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_GOODS_STATUSES_GROUP_TITLE') ?></td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_CHANGE_STATUS_AFTER_PAY">
            <?= GetMessage('LM_AUTO_MAIN_CHANGE_STATUS_AFTER_PAY') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_CHANGE_STATUS_AFTER_PAY" id="LM_AUTO_MAIN_CHANGE_STATUS_AFTER_PAY" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_CHANGE_STATUS_AFTER_PAY', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_STATUS_ID_AFTER_PAY">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_ID_AFTER_PAY') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_ID_AFTER_PAY = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_ID_AFTER_PAY'); ?>
            <select style="width: 100%;" id="LM_AUTO_MAIN_STATUS_ID_AFTER_PAY" name="LM_AUTO_MAIN_STATUS_ID_AFTER_PAY">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if($status['ID'] == $LM_AUTO_MAIN_STATUS_ID_AFTER_PAY) { ?> selected="selected"<? } ?>>
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
        <label for="LM_AUTO_MAIN_CANCEL_STATUS_ID">
            <?= GetMessage('LM_AUTO_MAIN_CANCEL_STATUS_ID') ?>:
        </label>
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_CANCEL_STATUS_ID = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_CANCEL_STATUS_ID'); ?>
            <select style="width: 100%;" id="LM_AUTO_MAIN_CANCEL_STATUS_ID" name="LM_AUTO_MAIN_CANCEL_STATUS_ID">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <?if($status['ID'] == $LM_AUTO_MAIN_CANCEL_STATUS_ID) { ?> selected="selected"<? } ?>>
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
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST" name="LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_USER_CANCEL_ACCESS_LIST)) { ?> selected="selected"<? } ?>>
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
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST" name="LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_CHANGE_SUPPLIER_ACCESS_LIST)) { ?> selected="selected"<? } ?>>
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
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT">
            <?= GetMessage('LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top">
        <? if (!empty($statuses)) { ?>
            <? $LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT')); ?>
            <select multiple="multiple" id="LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT" name="LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT[]">
                <? foreach ($statuses as $status) { ?>
                    <option value="<?= $status['ID'] ?>" <? if (in_array($status['ID'], $LM_AUTO_MAIN_STATUS_NOTIFY_EMAIL_DEFAULT)) { ?> selected="selected"<? } ?>>
                        [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        <? } else { ?>
            Нет статусов
        <? } ?>
    </td>
</tr>
<? if (IsModuleInstalled('support')) { ?>
    <tr>
        <td width="50%">
            <label for="LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID">
                <?= GetMessage('LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID') ?>:
            </label>
        </td>
        <td valign="top">
            <? if (!empty($statuses)) { ?>
                <? $LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID = COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID'); ?>
                <select style="width: 100%;" id="LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID" name="LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID">
                    <? foreach ($statuses as $status) { ?>
                        <option value="<?= $status['ID'] ?>" <? if ($status['ID'] == $LM_AUTO_MAIN_TICKET_NOT_FOUND_STATUS_ID) { ?> selected="selected"<? } ?>>
                            [<?= $status['ID'] ?>] <?= $status['NAME'] ?>
                        </option>
                    <? } ?>
                </select>
            <? } else { ?>
                Нет статусов
            <? } ?>
        </td>
    </tr>
<? } ?>


<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_GOODS_STATUSES_COLOR_TITLE') ?></td>
</tr>
<? foreach ($statuses as $status) { ?>
    <? $color = trim(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_STATUS_COLOR_' . $status['ID'], '#ffffff')); ?>
    <tr>
        <td width="50%">
            <label for="LM_AUTO_MAIN_STATUS_COLOR_<?= $status['ID'] ?>">
                <?= $status['NAME'] ?> [<?= $status['ID'] ?>]:
            </label>
        </td>
        <td width="50%">
            <input type="text" value="<?= $color ?>" id="LM_AUTO_MAIN_STATUS_COLOR_<?= $status['ID'] ?>" name="LM_AUTO_MAIN_STATUS_COLOR_<?= $status['ID'] ?>" />
            <div class="color" style="background-color: <?= $color ?>;"></div>
        </td>
    </tr>
<? } ?>


<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_GOODS_PUBLIC_STATUSES_COLOR_TITLE') ?></td>
</tr>
<? foreach ($statuses as $status) { ?>
    <? $color = trim(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_PUBLIC_STATUS_COLOR_' . $status['ID'], '#FFFFFF')); ?>
    <tr>
        <td width="50%">
            <label for="LM_AUTO_MAIN_PUBLIC_STATUS_COLOR_<?= $status['ID'] ?>">
                <?= $status['NAME'] ?> [<?= $status['ID'] ?>]:
            </label>
        </td>
        <td width="50%">
            <input type="text" value="<?= $color ?>" id="LM_AUTO_MAIN_PUBLIC_STATUS_COLOR_<?= $status['ID'] ?>" name="LM_AUTO_MAIN_PUBLIC_STATUS_COLOR_<?= $status['ID'] ?>" />
            <div class="color" style="background-color: <?= $color ?>;"></div>
        </td>
    </tr>
<? } ?>

