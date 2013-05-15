<?php

define('LM_AUTO_CUSTOM_FIELDS_NEW_COUNT', 5);

define('LM_AUTO_CUSTOM_FIELDS_FIELDS_COUNT', 7);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$modulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}


/*
 * Обработчик пользовательских свойств.
 */
$lmfields = new LinemediaAutoCustomFields();


$message = null;

if (!empty($_POST) && check_bitrix_sessid()) {
    
    /*
     * Изменение и удаление полей.
     */
    foreach ((array) $_POST['CUSTOM_FIELDS_ID'] as $id) {
        $data = array(
            'code'          => trim((string) $_POST['CUSTOM_FIELDS_CODE'][$id]),
            'name'          => trim((string) $_POST['CUSTOM_FIELDS_NAME'][$id]),
            'type'          => trim((string) $_POST['CUSTOM_FIELDS_TYPE'][$id]),
            'description'   => trim((string) $_POST['CUSTOM_FIELDS_DESCRIPTION'][$id]),
        );
        
        if ($_POST['CUSTOM_FIELDS_REMOVE'][$id] == 'Y') {
            if (empty($data['code'])) {
                continue;
            }
            try {
                $lmfields->remove($id, $data['code']);
            } catch (Exception $e) {
                $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => $data['name'].': '.$e->GetMessage()));
            }
        } else {
            if (empty($data['code']) || empty($data['name'])) {
                $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => $data['name'].': '.GetMessage('LM_AUTO_CUSTOM_FIELDS_ERROR')));
                continue;
            }
            
            try {
                $lmfields->update($id, $data);
            } catch (Exception $e) {
                $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => $data['name'].': '.$e->GetMessage()));
            }
        }
    }
    
    
    /*
     * Добавление полей.
     */
    foreach ((array) $_POST['NEW_CUSTOM_FIELDS_NAME'] as $id => $value) {
        $data = array(
            'code'          => trim((string) $_POST['NEW_CUSTOM_FIELDS_CODE'][$id]),
            'name'          => trim((string) $_POST['NEW_CUSTOM_FIELDS_NAME'][$id]),
            'type'          => trim((string) $_POST['NEW_CUSTOM_FIELDS_TYPE'][$id]),
            'description'   => trim((string) $_POST['NEW_CUSTOM_FIELDS_DESCRIPTION'][$id]),
        );
        
        if (empty($data['code']) && empty($data['name']) && empty($data['description'])) {
            continue;
        }
        if (empty($data['code']) || empty($data['name'])) {
            $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => $data['name'].': '.GetMessage('LM_AUTO_CUSTOM_FIELDS_ERROR')));
            continue;
        }
        
        try {
            $lmfields->add($data);
        } catch (Exception $e) {
            $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => $data['name'].': '.$e->GetMessage()));
        }
    }

    if (empty($message)) {
        $message = new CAdminMessage(array('TYPE' => 'OK', 'MESSAGE' => GetMessage('LM_AUTO_CUSTOM_FIELDS_SAVED')));
    }
}


/*
 * Имеющиеся пользовательские поля.
 */
$arCustomFields = $lmfields->getFields();


$APPLICATION->SetTitle(GetMessage('LM_AUTO_CUSTOM_FIELDS_TITLE'));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");


$aTabs = array();

$aTabs[] = array(
    'DIV'   => 'log',
    'TAB'   => GetMessage('LM_AUTO_CUSTOM_FIELDS_TAB_FIELDS'),
    'ICON'  => 'iblock',
    'TITLE' => GetMessage('LM_AUTO_CUSTOM_FIELDS_TAB_FIELDS'),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

?>

<?= BeginNote() ?>
    <b><?= GetMessage('LM_AUTO_CUSTOM_FIELDS_MESSAGE') ?></b>
<?= EndNote() ?>

<? if (!empty($message)) { ?>
    <?= $message->show() ?>
<? } ?>

<? $tabControl->Begin(); ?>
<form  action="" method="POST" name="custom_fields_frm" id="custom_fields_frm">
    <?= bitrix_sessid_post() ?>
    <? $tabControl->BeginNextTab(); ?>
        <tr>
            <td>
                <table border="0" cellspacing="0" cellpadding="0" class="internal" align="center" id="ib_prop_list">
                    <thead>
                        <tr class="heading">
                            <td><?= GetMessage("LM_AUTO_CF_ID") ?></td>
                            <td><?= GetMessage("LM_AUTO_CF_ORDER") ?></td>
                            <td><?= GetMessage("LM_AUTO_CF_CODE") ?></td>
                            <td><?= GetMessage("LM_AUTO_CF_TITLE") ?></td>
                            <td><?= GetMessage("LM_AUTO_CF_DESCRIPTION") ?></td>
                            <td><?= GetMessage("LM_AUTO_CF_TYPE") ?></td>
                            <td><?= GetMessage("LM_AUTO_CF_REMOVE") ?></td>
                        </tr>
                    </thead>
                    <tbody>
                        <? $order = LM_AUTO_CUSTOM_FIELDS_FIELDS_COUNT; ?>
                        <? foreach ($arCustomFields as $arCustomField) { ?>
                            <? $cid = $arCustomField['id'] ?>
                            <tr>
                                <td style="text-align: center; vertical-align: middle;">
                                    <?= $arCustomField['id'] ?>
                                    <input type="hidden" name="CUSTOM_FIELDS_ID[<?= $cid ?>]" id="CUSTOM_FIELDS_ID_<?= $cid ?>" value="<?= $cid ?>" />
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <?= ++$order ?>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="text" size="25" maxlength="255" name="CUSTOM_FIELDS_CODE[<?= $cid ?>]" id="CUSTOM_FIELD_CODE_<?= $cid ?>" value="<?= $arCustomField['code'] ?>" />
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="text" size="25" maxlength="255" name="CUSTOM_FIELDS_NAME[<?= $cid ?>]" id="CUSTOM_FIELD_NAME_<?= $cid ?>" value="<?= $arCustomField['name'] ?>" />
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <textarea cols="25" name="CUSTOM_FIELDS_DESCRIPTION[<?= $cid ?>]" id="CUSTOM_FIELDS_DESCRIPTION_<?= $cid ?>"><?= $arCustomField['description'] ?></textarea>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <select name="CUSTOM_FIELDS_TYPE[<?= $cid ?>]" style="width: 120px;">
                                        <? foreach (LinemediaAutoCustomFields::getTypes() as $type => $title) { ?>
                                            <option value="<?= $type ?>" <?= ($arCustomField['type'] == $type) ? ('selected') : ('') ?>>
                                                <?= $title ?>
                                            </option>
                                        <? } ?>
                                    </select>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="CUSTOM_FIELDS_REMOVE[<?= $cid ?>]" id="CUSTOM_FIELDS_REMOVE_<?= $cid ?>" value="Y" class="adm-designed-checkbox" />
                                    <label class="adm-designed-checkbox-label" for="CUSTOM_FIELDS_REMOVE_<?= $cid ?>" title="<?= GetMessage('LM_AUTO_CF_CHECK_TO_REMOVE') ?>"></label>
                                </td>
                            </tr>
                        <? } ?>
                        <? for ($i = 0; $i < LM_AUTO_CUSTOM_FIELDS_NEW_COUNT; $i++) { ?>
                            <tr>
                                <td style="text-align: center; vertical-align: middle;" colspan="2">
                                    <span style="color: #ccc;"><?= GetMessage('LM_AUTO_CF_NEW_CUSTOM_FIELD') ?></span>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="text" size="25" maxlength="255" name="NEW_CUSTOM_FIELDS_CODE[]" value="" />
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="text" size="25" maxlength="255" name="NEW_CUSTOM_FIELDS_NAME[]" value="" />
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <textarea cols="25" name="NEW_CUSTOM_FIELDS_DESCRIPTION[]"></textarea>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <select name="NEW_CUSTOM_FIELDS_TYPE[]" style="width: 120px;">
                                        <? foreach (LinemediaAutoCustomFields::getTypes() as $type => $title) { ?>
                                            <option value="<?= $type ?>">
                                                <?= $title ?>
                                            </option>
                                        <? } ?>
                                    </select>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    &nbsp;
                                </td>
                            </tr>
                        <? } ?>
                    </tbody>
                </table>
            </td>
        </tr>
    <? $tabControl->Buttons(array("disabled" => false)); ?>
    <? $tabControl->End(); ?>
</form>


<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php'); ?>
