<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php"); 

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/include.php"); // инициализация модуля


if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

$modulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($modulePermissions < "W") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);

global $APPLICATION;

$ID = (int) $_REQUEST['ID'];


/*
 * Изменение данных.
 */
if (!empty($_REQUEST) && check_bitrix_sessid()) {
    $success = false;
    
    $part = new LinemediaAutoPart($ID, $_REQUEST);
    
    switch ($_REQUEST['action']) {
        case 'save':
            
            try {
                $partID = $part->save();
            } catch (Exception $e) {
                $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => $e->GetMessage()));
            }
            
            if (empty($message)) {
                if (isset($_REQUEST['save'])) {
                    LocalRedirect('/bitrix/admin/linemedia.auto_products.php');
                    exit();
                } else {
                    LocalRedirect($APPLICATION->GetCurPageParam('ID='.$partID, array('ID')));//'/bitrix/admin/linemedia.auto_products.php');
                    exit();
                }
            }
            break;
            
        case 'delete':
            try {
                $success = $part->delete();
            } catch (Exception $e) {
                $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => $e->GetMessage()));
            }
            
            if ($success) {
                LocalRedirect('/bitrix/admin/linemedia.auto_products.php');
                exit();
            }
            break;
    }
}


$arFields = $_REQUEST;
if ($ID > 0) {
    $part = new LinemediaAutoPart($ID);
    $arFields = $part->getArray();
}

/*
 * Дополнительные поля.
 */
$lmfields = new LinemediaAutoCustomFields();

$arCustomFields = $lmfields->getFields();

/*
 * Поставщики
 */
$arSuppliers = array();
$arResSuppliers = LinemediaAutoSupplier::GetList();
foreach ($arResSuppliers as $arSupplier) {
    // Пропускаем удаленных поставщиков
    if (!empty($arSupplier['PROPS']['api']['VALUE'])) {
        continue;
    }
    $arSuppliers[$arSupplier['PROPS']['supplier_id']['VALUE']] = $arSupplier;
}


/*
 * Контекстное меню
 */

$urlDelete = $APPLICATION->GetCurPage().'?ID='.$ID.'&action=delete&'.bitrix_sessid_get();

$aMenu = array(
    array(
        'TEXT' => GetMessage('LM_AUTO_MAIN_BACK_TO_LIST'),
        'LINK' => '/bitrix/admin/linemedia.auto_products.php',
        'ICON' => 'btn_list',
    ),
    array('SEPARATOR' => 'Y'),
    
);

if ($ID > 0) {
    $aMenu []= array(
        'TEXT' => GetMessage('LM_AUTO_PART_DELETE'),
        'LINK' => "javascript: if(confirm('".GetMessage("LM_AUTO_PART_DELETE_CONFIRM")."')) window.location='".CUtil::JSEscape($urlDelete)."';",
        'ICON' => 'btn_delete',
    );
}

$context = new CAdminContextMenu($aMenu);
$context->Show();


/*
 * Описываем табы административной панели битрикса.
 */
if ($ID > 0) {
    $aTabs = array(
        array(
            'DIV'   => 'edit',
            'TAB'   => GetMessage('LM_AUTO_MAIN_EDIT_TAB'),
            'ICON'  => 'edit',
            'TITLE' => GetMessage('LM_AUTO_MAIN_EDIT_TAB_TITLE')
        )
    );
} else {
        $aTabs = array(
        array(
            'DIV'   => 'edit',
            'TAB'   => GetMessage('LM_AUTO_MAIN_ADD_TAB'),
            'ICON'  => 'edit',
            'TITLE' => GetMessage('LM_AUTO_MAIN_ADD_TAB_TITLE')
        )
    );
}

/*
 * Инициализируем табы
 */
$oTabControl = new CAdmintabControl('tabControl', $aTabs);

?>

<? if (!empty($message)) { ?>
    <?= $message->show() ?>
<? } ?>

<? $oTabControl->Begin() ?>

<form method="POST" enctype="multipart/form-data" action="<?= $APPLICATION->GetCurPage() ?>?&lang=<?= LANG ?>&ID=<?= intval($ID) ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="ID" value="<?= $ID ?>" />
    <input type="hidden" name="action" value="save" />
    
    <? $oTabControl->BeginNextTab() ?>
    
    <tr class="heading">
        <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_PRIMARY_FIELDS') ?>:</td>
    </tr>
    
    <tr>
        <td width="50%" valign="top">
            <label for="title-id">
                 <?= GetMessage('LM_AUTO_MAIN_TITLE_TITLE') ?>:
            </label>
        </td>
        <td valign="top">
            <textarea id="title-id" name="title" rows="5" cols="22"><?= $arFields['title'] ?></textarea>
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <label for="article-id">
                 <span class="adm-required-field"><?= GetMessage('LM_AUTO_MAIN_ARTICLE_TITLE') ?></span>:
            </label>
        </td>
        <td valign="top">
            <input type="text" id="article-id" name="article" value="<?= $arFields['article'] ?>" />
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <label for="original-article-id">
                <?= GetMessage('LM_AUTO_MAIN_ORIGINAL_ARTICLE_TITLE') ?>:
            </label>
        </td>
        <td valign="top">
            <input type="text" id="original-article-id" name="original_article" value="<?= $arFields['original_article'] ?>" />
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <label for="brand-title-id">
                <span class="adm-required-field"><?= GetMessage('LM_AUTO_MAIN_BRAND_TITLE') ?></span>:
            </label>
        </td>
        <td valign="top">
            <input type="text" id="brand-title-id" name="brand_title" value="<?= $arFields['brand_title'] ?>" />
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <label for="price-id">
                <?= GetMessage('LM_AUTO_MAIN_PRICE_TITLE') ?>:
            </label>
        </td>
        <td valign="top">
            <input type="text" id="price-id" name="price" value="<?= $arFields['price'] ?>" />
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <label for="quantity-id">
                <?= GetMessage('LM_AUTO_MAIN_QUANTITY_TITLE') ?>:
            </label>
        </td>
        <td valign="top">
            <input type="text" id="quantity-id" name="quantity" value="<?= $arFields['quantity'] ?>" />
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <label for="group-id">
                <?= GetMessage('LM_AUTO_MAIN_GROUP_TITLE') ?>:
            </label>
        </td>
        <td valign="top">
            <input type="text" id="group-id" name="group_id" value="<?= $arFields['group_id'] ?>" />
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <label for="weight-id">
                <?= GetMessage('LM_AUTO_MAIN_WEIGHT_TITLE') ?>:
            </label>
        </td>
        <td valign="top">
            <input type="text" id="weight-id" name="weight" value="<?= $arFields['weight'] ?>" />
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <label for="supplier-id">
                <span class="adm-required-field"><?= GetMessage('LM_AUTO_MAIN_SUPPLIER_TITLE') ?></span>:
            </label>
        </td>
        <td valign="top">
            <select name="supplier_id" id="supplier-id">
                <? foreach ($arSuppliers as $id => $arSupplier) { ?>
                    <option value="<?= $id ?>" <?= ($id == $arFields['supplier_id']) ? ('selected') : ('') ?>>
                        <?= htmlspecialchars($arSupplier['NAME']) ?>
                    </option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <label for="modified-id">
                <?= GetMessage('LM_AUTO_MAIN_MODIFIED_TITLE') ?>:
            </label>
        </td>
        <td valign="top">
            <input type="text" id="modified-id" value="<?= $arFields['modified'] ?>" disabled />
        </td>
    </tr>
    
    <tr class="heading">
        <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_CUSTOM_FIELDS') ?>:</td>
    </tr>
    
    <? foreach ($arCustomFields as $arCustomField) { ?>
        <? $code = $arCustomField['code'] ?>
        <tr>
            <td width="50%" valign="top">
                <label for="<?= $code ?>-id">
                    <?= $arCustomField['name'] ?>:
                </label>
            </td>
            <td valign="top">
                <input type="text" id="<?= $code ?>-id" name="<?= $code ?>" value="<?= $arFields[$code] ?>" />
            </td>
        </tr>
    <? } ?>
    
    <? $oTabControl->EndTab() ?>
    
    <? $oTabControl->Buttons() ?>
        <input type="submit" name="save" value="<?= GetMessage('LM_AUTO_TO_BUTTON_SAVE') ?>" class="adm-btn-save" />
        <input type="submit" name="apply" value="<?= GetMessage('LM_AUTO_TO_BUTTON_APPLY') ?>" />
        <input type="reset" name="reset" value="<?= GetMessage('LM_AUTO_TO_BUTTON_RESET') ?>" onclick="javascript: document.location = '/bitrix/admin/linemedia.auto_products.php';" />
    <? $oTabControl->End() ?>
</form>

<? require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php") ?>

