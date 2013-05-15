<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>

<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] as $error) { ?>
        <? ShowError($error) ?>
    <? } ?>
<? } ?>

<? if (!empty($arResult['MESSAGE'])) { ?>
    <? ShowMessage(array('MESSAGE' => $arResult['MESSAGE'], 'TYPE' => 'OK')) ?>
<? } ?>

<div class="lm-auto-vin">
    <form id="lm-auto-vin-form" method="post">
        <?= bitrix_sessid_post() ?>
        <div>
            <table class="lm-auto-vin-table">
                <thead>
                    <tr>
                        <th colspan="2"><?= GetMessage('SUP_SUPPORT') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <div class="html">
                                <? if (!empty($arResult['HTML'])) { ?>
                                    <? foreach ($arResult['HTML'] as $html) { ?>
                                        <?= $html ?>
                                    <? } ?>
                                <? } ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="field-name">
                            <?= GetMessage('SUP_VIN') ?><span class="starrequired">*</span>:
                        </td>
                        <td class="value">
                            <input id="lm-auto-vin-input" type="text" name="vin" value="<?= htmlspecialchars((string) $arResult['FIELDS']['vin']) ?>" maxlength="17" />
                        </td>
                    </tr>
                    <tr>
                        <td class="field-name">
                            <?= GetMessage('SUP_PART_DESCRIPTION') ?><span class="starrequired">*</span>:
                        </td>
                        <td class="value">
                            <textarea name="MESSAGE"><?= htmlspecialchars((string) $arResult['FIELDS']['MESSAGE']) ?></textarea>
                        </td>
                    </tr>
                    <? if (!CUser::IsAuthorized()) { ?>
                        <tr>
                            <td class="field-name">
                                <?= GetMessage('SUP_EMAIL') ?><span class="starrequired">*</span>:
                            </td>
                            <td class="value">
                                <input type="text" name="EMAIL" value="<?= htmlspecialchars((string) $arResult['FIELDS']['EMAIL']) ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="field-name">
                                <?= GetMessage('SUP_NAME') ?><span class="starrequired">*</span>:
                            </td>
                            <td class="value">
                                <input type="text" name="NAME" value="<?= htmlspecialchars((string) $arResult['FIELDS']['NAME']) ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="field-name">
                                <?= GetMessage('SUP_LAST_NAME') ?>:
                            </td>
                            <td class="value">
                                <input type="text" name="LAST_NAME" value="<?= htmlspecialchars((string) $arResult['FIELDS']['LAST_NAME']) ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="field-name">
                                <?= GetMessage('SUP_PHONE') ?><span class="starrequired">*</span>:
                            </td>
                            <td class="value">
                                <input type="text" name="PHONE" value="<?= htmlspecialchars((string) $arResult['FIELDS']['PHONE']) ?>" />
                            </td>
                        </tr>
                    <? } ?>
                    <tr>
                        <td class="field-name">
                            <?= GetMessage('SUP_COMPLETE') ?>:
                        </td>
                        <td class="value">
                            <textarea name="extra"><?= htmlspecialchars((string) $arResult['FIELDS']['extra']) ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <input type="submit" name="save" value="<?= GetMessage('SUP_SUBMIT') ?>" />
            <input type="reset" name="reset" value="<?= GetMessage('SUP_RESET') ?>" />
        </div>
    </form>
</div>

