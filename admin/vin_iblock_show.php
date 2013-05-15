<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");

IncludeModuleLangFile(__FILE__);

$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");

if ($saleModulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
$POST_RIGHT = 'W';

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("iblock")) {
    ShowError('IBLOCK MODULE NOT INSTALLED');
    return;
}

global $APPLICATION, $DB, $USER;

$ID = (int) $_REQUEST['ID'];
$strError = '';

/*
 * »зменение данных.
 */
if (!empty($_REQUEST) && check_bitrix_sessid()) {
    $success = false;
    
    switch ($_REQUEST['action']) {
        case 'save':
            if ($ID > 0){
                $sAnswer = trim($_REQUEST['answer']);
                if(strlen($sAnswer) > 0){
                    $item_res = CIBlockElement::GetByID($ID)->GetNextElement();
                    $item_fields = $item_res->GetFields();
                    $item_props = $item_res->GetProperties();
                    if(empty($item_props['answer']['~VALUE']['TEXT'])){
                        $aFieldsAdd = Array(
                                        'answer' => Array('VALUE' => Array('TYPE' => 'HTML', 'TEXT' => $sAnswer)), //ответ
                                        'answer_manager' => $USER->GetID(), //кто ответил
                                        'manager' => $USER->GetID(), //замен€ем менеджера на того, кто ответил
                                        'answer_date' => ConvertTimeStamp(false, 'FULL'), //дата ответа
                                        );
                      
                        CIBlockElement::SetPropertyValuesEx($ID, false, $aFieldsAdd); //сохран€ем ответ                        
                        //отправл€ем уведомление
                        if(!empty($item_props['site_id']['VALUE'])){
                            $aUser = CUser::GetByID($item_fields['CREATED_BY'])->Fetch();
                            $aManager = CUser::GetByID($item_props['manager']['VALUE'])->Fetch();
                            $arEventFields = Array(
                                                   'EMAIL' => $aUser['EMAIL'],
                                                   'NAME' => $aUser['NAME'],
                                                   'LAST_NAME' => $aUser['LAST_NAME'],
                                                   'VIN' => $item_props['vin']['VALUE'],
                                                   'ID' => $ID,
                                                   'LINK' => '',
                                                   'ANSWER' => $sAnswer,
                                                   'MANAGER' => $arUser["LAST_NAME"] . ' ' . $arUser["NAME"],
                                                   );
                            $eventId = CEvent::Send('LM_AUTO_VIN_IBLOCK_SEND_MAIL_ANSWER', $item_props['site_id']['VALUE'], $arEventFields);
                            unset($arEventFields);
                        }
                        //$success = true;
                    }else{
                        $strError .=  GetMessage('LM_AUTO_VIN_ERROR_ANSWER_ISSET') . '<br />'; //ответ уже есть
                    }
                    unset($item_props, $item_fields);
                }else{
                    $strError .=  GetMessage('LM_AUTO_VIN_ERROR_ANSWER_EMPTY') . '<br />'; //ответ пустой
                }
                unset($sAnswer);
            } else {
                LocalRedirect('/bitrix/admin/linemedia.auto_vin_iblock_list.php?lang=' . LANGUAGE_ID);
                exit();
            }
            
            if ($success) {
                LocalRedirect('/bitrix/admin/linemedia.auto_vin_iblock_list.php?lang=' . LANGUAGE_ID);
                exit();
            }
        break;
        case 'delete':
            //удал€ем запрос
            if ($ID > 0) {
                $success = CIBlockElement::Delete($ID);
            }
            
            if ($success) {
                LocalRedirect('/bitrix/admin/linemedia.auto_vin_iblock_list.php?lang=' . LANGUAGE_ID);
                exit();
            }
        break;
    }
}

$fields_disabled = Array('request', 'answer', 'manager', 'answer_date', 'answer_manager', 'brand_id', 'model_id', 'modification_id', 'site_id'); //пол€, которые совсем не выводим
$item_res = CIBlockElement::GetByID($ID)->GetNextElement();
if (intval($ID) <=  0 || $item_res === false) {
    LocalRedirect('/bitrix/admin/linemedia.auto_vin_iblock_list.php?lang=' . LANGUAGE_ID);
    exit();
}
$item_properties = $item_res->GetProperties();
$item_fields = $item_res->GetFields();
$user = CUser::GetByID($item_fields['CREATED_BY'])->Fetch();
$is_answer = (!empty($item_properties['answer']['~VALUE']['TEXT']))?true:false;

/*
 *  онтекстное меню
 */

$urlDelete = $APPLICATION->GetCurPage() . '?ID=' . $ID . '&action=delete&' . bitrix_sessid_get();

$aMenu = array(
    array(
        'TEXT' => GetMessage('LM_AUTO_VIN_BACK_TO_LIST'),
        'LINK' => '/bitrix/admin/linemedia.auto_vin_iblock_list.php?lang=' . LANGUAGE_ID,
        'ICON' => 'btn_list',
    ),
    array('SEPARATOR' => 'Y'),
);

if ($ID > 0) {
    $aMenu []= array(
        'TEXT' => GetMessage('LM_AUTO_VIN_DELETE'),
        'LINK' => "javascript: if(confirm('".GetMessage("LM_AUTO_VIN_DELETE_CONFIRM")."')) window.location='".CUtil::JSEscape($urlDelete)."';",
        'ICON' => 'btn_delete',
    );
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

/*
 * ќписываем табы административной панели битрикса.
 */
if ($ID > 0) {
    $aTabs = array(
        array(
            'DIV'   => 'edit',
            'TAB'   => GetMessage('LM_AUTO_VIN_EDIT_TAB'),
            'ICON'  => 'edit',
            'TITLE' => GetMessage('LM_AUTO_VIN_EDIT_TAB_TITLE')
        )
    );
} else {
        $aTabs = array(
        array(
            'DIV'   => 'edit',
            'TAB'   => GetMessage('LM_AUTO_VIN_ADD_TAB'),
            'ICON'  => 'edit',
            'TITLE' => GetMessage('LM_AUTO_VIN_ADD_TAB_TITLE')
        )
    );
}

if(strlen($strError)>0){
    echo CAdminMessage::ShowMessage(Array("DETAILS"=>$strError, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SDEN_ERROR"), "HTML"=>true));
}

/*
 * »нициализируем табы
 */
$oTabControl = new CAdmintabControl('tabControl', $aTabs);
$oTabControl->Begin();
?>
<script src="http://yandex.st/jquery/1.8.0/jquery.min.js"></script>
<form method="GET" enctype="multipart/form-data" action="<?= $APPLICATION->GetCurPage() ?>?&lang=<?= LANG ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="ID" value="<?= $ID ?>" />
    <input type="hidden" name="action" value="save" />
    <? $oTabControl->BeginNextTab() ?>
    <tr class="heading" id="tr_LM_AUTO_VIN_TITLE_INFO_CLIENT"><td colspan="2"><?= GetMessage('LM_AUTO_VIN_TITLE_INFO_CLIENT') ?></td></tr>
    <tr>
        <td width="50%" valign="top"><strong><?= GetMessage('LM_AUTO_VIN_REQUEST_ID') ?>:</strong></td>
        <td valign="top"><?=$ID;?></td>
    </tr>
    <tr>
        <td width="50%" valign="top"><strong><?= GetMessage('LM_AUTO_VIN_CLIENT_NAME') ?>:</strong></td>
        <td valign="top">[<a href="/bitrix/admin/user_edit.php?ID=<?=$user['ID'];?>&lang=<?=LANG;?>"><?=$user['ID'];?></a>] <?=$user["LAST_NAME"];?> <?=$user["NAME"];?> (<?=$user['LOGIN'];?>)</td>
    </tr>
    <tr>
        <td width="50%" valign="top"><strong><?= GetMessage('LM_AUTO_VIN_CLIENT_EMAIL') ?>:</strong></td>
        <td valign="top"><a href="mailto:<?=$user['EMAIL'];?>"><?=$user['EMAIL'];?></a></td>
    </tr>
    <tr class="heading" id="tr_LM_AUTO_VIN_TITLE_INFO_AUTO"><td colspan="2"><?= GetMessage('LM_AUTO_VIN_TITLE_INFO_AUTO') ?></td></tr>
    <?
    if (count($item_properties)){
        foreach ($item_properties AS $property_code => $property){
            if (in_array($property_code, $fields_disabled) || empty($property['VALUE'])){
                continue;
            }
            ?>
    <tr>
        <td width="50%" valign="top">
            <?=$property['NAME'];?>:
        </td>
        <td valign="top">
            <?if($property['USER_TYPE'] === 'HTML'){?>
            <?=$property['VALUE']['TEXT'];?>
            <?}elseif($property['PROPERTY_TYPE'] === 'L'){
                if($property['MULTIPLE'] === 'Y'){?>
                        <?=implode(', ', $property['VALUE']);?>
                <?}else{?>
                    <?=$property['VALUE'];?>
                <?}
            }else{?>
                <?=$property['VALUE'];?>
            <?}?>
        </td>
    </tr>
            <?
        }
        unset($property);
    }
    ?>
    <tr class="heading" id="tr_LM_AUTO_VIN_TITLE_REQUEST"><td colspan="2"><?= GetMessage('LM_AUTO_VIN_TITLE_REQUEST') ?></td></tr>
    <tr>
        <td width="100%" valign="top" colspan="2">
            <?
            $requst_data = @unserialize($item_properties['request']['~VALUE']['TEXT']);
            if(is_array($requst_data) && count($requst_data) > 0){
                ?>
                <table cellpadding="3" cellspacing="1" border="0" width="100%" class="internal" id="REQUEST_TABLE">
                    <tbody>
                        <tr class="heading">
                            <td><?= GetMessage('LM_AUTO_VIN_REQUEST_TITLE') ?></td>
                            <td><?= GetMessage('LM_AUTO_VIN_REQUEST_ART') ?></td>
                            <td><?= GetMessage('LM_AUTO_VIN_REQUEST_QUANTITY') ?></td>
                            <td><?= GetMessage('LM_AUTO_VIN_REQUEST_COMMENT') ?></td>
                        </tr>
                        <tr></tr>
                        <?foreach($requst_data AS $request_item){?>
                        <tr>
                            <td class="request_title"><div><?=$request_item['title'];?></div></td>
                            <td class="request_title"><div><?=$request_item['art'];?></div></td>
                            <td class="request_title"><div><?=$request_item['quantity'];?></div></td>
                            <td class="request_title"><div><?=$request_item['comment'];?></div></td>
                        </tr>
                        <?}
                        unset($request_item);
                        ?>
                    </tbody>
                </table>
                <?
            }
            ?>
        </td>
    </tr>
    <tr class="heading" id="tr_LM_AUTO_VIN_TITLE_ANSWER"><td colspan="2"><?= GetMessage('LM_AUTO_VIN_TITLE_ANSWER') ?></td></tr>
    <?if($is_autobranches && !empty($item_properties['manager']['VALUE'])){
    $arUser = CUser::GetByID($item_properties['manager']['VALUE'])->Fetch();
    ?>
    <tr>
        <td width="100%" valign="top" colspan="2"><?= GetMessage('LM_AUTO_VIN_RESPONSIBLE_MANAGER') ?>: [<a href="/bitrix/admin/user_edit.php?ID=<?=$arUser['ID'];?>&lang=<?=LANG;?>"><?=$arUser['ID'];?></a>]
        <?=$arUser["NAME"];?> <?=$arUser["LAST_NAME"];?> (<?=$arUser["LOGIN"];?>)
        </td>
    </tr>
    <?}?>
    <?if($is_answer && !empty($item_properties['answer_manager']['VALUE'])){
        $arUser = CUser::GetByID($item_properties['answer_manager']['VALUE'])->Fetch();
    ?>
     <tr>
        <td width="100%" valign="top" colspan="2"><?= GetMessage('LM_AUTO_VIN_ANSWER_MANAGER') ?>: [<a href="/bitrix/admin/user_edit.php?ID=<?=$arUser['ID'];?>&lang=<?=LANG;?>"><?=$arUser['ID'];?></a>]
        <?=$arUser["NAME"];?> <?=$arUser["LAST_NAME"];?> (<?=$arUser["LOGIN"];?>)
        </td>
    </tr>
    <?}?>
    <tr>
        <td width="100%" valign="top" colspan="2">
            <?
            if(!$is_answer){
		$LHE = new CLightHTMLEditor;
		$LHE->Show(array(
			'id' => 'lm_auto_vin_answer',
			'width' => '100%',
			'height' => '200px',
			'inputName' => 'answer',
			'content' => $item_properties['answer']['~VALUE']['TEXT'],
			'bUseFileDialogs' => false,
			'bFloatingToolbar' => false,
			'bArisingToolbar' => false,
			'toolbarConfig' => array(
				'Bold', 'Italic', 'Underline', 'RemoveFormat',
				'CreateLink', 'DeleteLink', 'Image', 'Video',
				'BackColor', 'ForeColor',
				'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull',
				'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent',
				'StyleList', 'HeaderList',
				'FontList', 'FontSizeList',
			),
		));
            }else{
                ?>
                <br /><div class="order-itog"><?=$item_properties['answer']['~VALUE']['TEXT'];?></div>
                <?
            }
            ?>
        </td>
    </tr>
    
    <? $oTabControl->EndTab() ?>
    
    <?if(!$is_answer){?>
    <?$oTabControl->Buttons() ?>
        <input type="submit" name="update" value="<?= GetMessage('LM_AUTO_VIN_BUTTON_SAVE') ?>" />
        <input type="reset" name="reset" value="<?= GetMessage('LM_AUTO_VIN_BUTTON_RESET') ?>" />
    <?}?>    
    <? $oTabControl->End() ?>
</form>

<? require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php") ?>