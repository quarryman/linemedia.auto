<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>

<? $APPLICATION->AddHeadScript($this->GetFolder() . '/ru/script.js'); ?>
<?= ShowError($arResult["ERROR_MESSAGE"]) ?>

<div class="lm-auto-vin">

<? if (!isset($arParams['HIDE_FORM']) || isset($arParams['HIDE_FORM']) && $arParams['HIDE_FORM'] !== true):?>

<? if (!empty($arResult["TICKET"])):?>

<?if (!empty($arResult["ONLINE"])):?>
    <p>
        <? $time = intval($arResult["OPTIONS"]["ONLINE_INTERVAL"]/60)." ".GetMessage("SUP_MIN");?>
        <?= str_replace("#TIME#", $time, GetMessage("SUP_USERS_ONLINE"));?>:<br />
        <?foreach($arResult["ONLINE"] as $arOnlineUser):?>
           <small>(<?=$arOnlineUser["USER_LOGIN"]?>) <?=$arOnlineUser["USER_NAME"]?> [<?=$arOnlineUser["TIMESTAMP_X"]?>]</small><br />
        <?endforeach?>
    </p>
<?endif?>



<p><b><?= $arResult['TICKET']['TITLE'] ?></b></p>

<table class="support-ticket-edit data-table">
    <tr>
        <th><?= GetMessage('SUP_TICKET') ?></th>
    </tr>
    <tr>
        <td>
            <?= GetMessage("SUP_FROM") ?>:
            <? if (intval($arResult["TICKET"]["OWNER_USER_ID"])>0) { ?>
                <?= $arResult["TICKET"]["OWNER_NAME"] ?>
            <? } ?>
            <br />
            <?= GetMessage("SUP_CREATE") ?>: <?= $arResult["TICKET"]["DATE_CREATE"] ?>

            <?= $arResult["TICKET"]["CREATED_NAME"] ?>
            <br />
            
            <? if ($arResult["TICKET"]["DATE_CREATE"] != $arResult["TICKET"]["TIMESTAMP_X"]) { ?>
                <?= GetMessage("SUP_TIMESTAMP") ?>: <?= $arResult["TICKET"]["TIMESTAMP_X"] ?>
                <?= $arResult["TICKET"]["MODIFIED_BY_NAME"] ?>
                <br />
            <? } ?>

            <? if (strlen($arResult["TICKET"]["DATE_CLOSE"]) > 0) { ?>
                <?= GetMessage("SUP_CLOSE") ?>: <?= $arResult["TICKET"]["DATE_CLOSE"] ?>
            <? } ?>

            <? if (strlen($arResult["TICKET"]["STATUS_NAME"])>0) { ?>
                <?= GetMessage("SUP_STATUS") ?>:
                <span title="<?= $arResult["TICKET"]["STATUS_DESC"] ?>"><?= $arResult["TICKET"]["STATUS_NAME"] ?></span>
                <br />
            <? } ?>
        </td>
    </tr>
    <tr>
        <th><?= GetMessage("SUP_DISCUSSION") ?></th>
    </tr>
    <tr>
        <td>
            <?= $arResult["NAV_STRING"] ?>
            <? foreach ($arResult["MESSAGES"] as $arMessage):?>
                <div class="ticket-edit-message">
                    <div align="left">
                        <b><?= GetMessage("SUP_TIME") ?></b>: <?= $arMessage["DATE_CREATE"] ?>
                    </div>
                    <b><?=GetMessage("SUP_FROM")?></b>:
                    
                    <?= $arMessage["OWNER_SID"] ?>
                    <? if (intval($arMessage["OWNER_USER_ID"]) > 0) { ?>
                        <? if (intval($arResult["TICKET"]["OWNER_USER_ID"]) > 0 && intval($arResult["TICKET"]["OWNER_USER_ID"]) !== intval($arMessage["OWNER_USER_ID"])) { ?>
                            <?= GetMessage('SUP_STORE_EMPLOYEE') ?>
                        <? } ?>
                        <?= $arMessage["OWNER_NAME"] ?>
                        <? if (intval($arResult["TICKET"]["OWNER_USER_ID"]) > 0 && intval($arResult["TICKET"]["OWNER_USER_ID"]) !== intval($arMessage["OWNER_USER_ID"])) { ?>
                            <? if (isset($arMessage["CREATED_EMAIL"]) && strlen(trim($arMessage["CREATED_EMAIL"])) > 0) { ?>
                                (<a href="mailto:<?= $arMessage["CREATED_EMAIL"] ?>"><?= $arMessage["CREATED_EMAIL"] ?></a>)
                            <? } ?>
                        <? } ?>
                    <? } ?>
                    <br />

                    <?
                    $aImg = array("gif", "png", "jpg", "jpeg", "bmp");
                    foreach ($arMessage["FILES"] as $arFile):
                    ?>
                        <div class="support-paperclip"></div>
                        <? if (in_array(strtolower(GetFileExtension($arFile["NAME"])), $aImg)) { ?>
                            <a title="<?=GetMessage("SUP_VIEW_ALT")?>" href="<?=$componentPath?>/ticket_show_file.php?hash=<?echo $arFile["HASH"]?>&amp;lang=<?=LANG?>"><?=$arFile["NAME"]?></a>
                        <? } else { ?>
                            <?=$arFile["NAME"]?>
                        <? } ?>
                      <?
                            $size = $arFile["FILE_SIZE"];
                            $a = array("b", "kb", "mb", "gb");
                            $pos = 0;
                
                            while($size >= 1024)
                            {
                                $size /= 1024;
                                $pos++;
                            }
                
                            $size = round($size, 2)." ".$a[$pos];
                        ?>
    
                      (<?=$size?>)
                      [ <a title="<?=str_replace("#FILE_NAME#", $arFile["NAME"], GetMessage("SUP_DOWNLOAD_ALT"))?>" href="<?=$componentPath?>/ticket_show_file.php?hash=<?=$arFile["HASH"]?>&amp;lang=<?=LANG?>&amp;action=download"><?=GetMessage("SUP_DOWNLOAD")?></a> ]
                      <br class="clear" />
                <? endforeach ?>
                <br /><?=$arMessage["MESSAGE"]?>
                </div>
            <?endforeach?>

            <?= $arResult["NAV_STRING"] ?>
        </td>
    </tr>
</table>


<br />
<?endif;?>
<script>
<?if(
     ($_REQUEST["AUTO_ID"] != '0' || isset($arResult['GARAGE']['AUTO_INSERT_ID'])) &&
     (isset($arResult['GARAGE']['ITEMS']) && is_array($arResult['GARAGE']['ITEMS']) && count($arResult['GARAGE']['ITEMS']) > 0)
     ):?>
$(document).ready(function(){
    $('#tr_vin').hide();
    $('#tr_brand').hide();
    $('#tr_model').hide();
    $('#tr_modification').hide();
});
<?endif;?>
function HideAddAuto(){
    $('#tr_vin').hide();
    $('#tr_brand').hide();
    $('#tr_model').hide();
    $('#tr_modification').hide();
}

function ShowAddAuto(){
    $("#tr_vin").css('display', 'table-row');
    $("#tr_brand").css('display', 'table-row');
    if($('#f_model').val() != ''){
        $("#tr_model").css('display', 'table-row');
    }
    if($('#f_modification').val() != ''){
        $("#tr_modification").css('display', 'table-row');
    }
}
</script>

<? /* if(empty($arResult["TICKET"])): */ ?>
<?if (strlen($arResult["TICKET"]["DATE_CLOSE"]) <= 0):?>
<form name="support_edit" method="post" action="<?=$arResult["REAL_FILE_PATH"]?>" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="set_default" value="Y" />
<input type="hidden" name="ID" value=<?=(empty($arResult["TICKET"]) ? 0 : $arResult["TICKET"]["ID"])?> />
<input type="hidden" name="lang" value="<?=LANG?>" />
<table class="support-ticket-edit-form data-table">


    <thead>
        <tr>
            <th colspan="2"><?=GetMessage("SUP_TICKET")?></th>
        </tr>
    </thead>

    <tbody>
    
    <tr>
        <th colspan="2"><label for="MESSAGE"><?=GetMessage("SUP_ANSWER")?></label></th>
    </tr>
    
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
    
    <?if(empty($arResult["TICKET"])):
            if(isset($arResult['GARAGE']['ITEMS']) && is_array($arResult['GARAGE']['ITEMS']) && count($arResult['GARAGE']['ITEMS']) > 0):
        ?>

    <tr>
        <td class="field-name"></td>
        <td>
    <?
            if(isset($arResult['GARAGE']['ITEMS']) && is_array($arResult['GARAGE']['ITEMS']) && count($arResult['GARAGE']['ITEMS']) > 0):
            $i=0;
            foreach($arResult['GARAGE']['ITEMS'] AS $aAutoItem):
    ?>
        <input type="radio"<?=(($i==0 && $_REQUEST["AUTO_ID"] != '0') || ($arResult['GARAGE']['AUTO_INSERT_ID'] == $aAutoItem['ID']))?' checked="checked"':'';?> onclick="$('#extra').val('<?=htmlspecialcharsEx($aAutoItem['PROPERTY_EXTRA_VALUE']['TEXT']);?>'); HideAddAuto();" name="AUTO_ID" id="auto_<?=$aAutoItem['ID'];?>" value="<?=$aAutoItem['ID'];?>" /> <strong><label for="auto_<?=$aAutoItem['ID'];?>"><?=$aAutoItem['NAME'];?></label></strong><br />
        <small>
        <? if ($aAutoItem['PROPERTY_VIN_VALUE']): ?><?=$aAutoItem['PROPERTY_VIN_VALUE']?> | <? endif; ?>
        <? if ($aAutoItem['PROPERTY_BRAND_VALUE']): ?><?=$aAutoItem['PROPERTY_BRAND_VALUE']?> | <? endif; ?>
        <? if ($aAutoItem['PROPERTY_MODEL_VALUE']): ?><?=$aAutoItem['PROPERTY_MODEL_VALUE']?> | <? endif; ?>
        <? if ($aAutoItem['PROPERTY_MODIFICATION_VALUE']): ?><?=$aAutoItem['PROPERTY_MODIFICATION_VALUE']?><? endif; ?>
        </small>
        <br />
    <?      $i++;
            endforeach;
            unset($i, $aAutoItem);
    ?>
    <?endif;?>
        <input type="radio"<?if($_REQUEST["AUTO_ID"] == '0' && !isset($arResult['GARAGE']['AUTO_INSERT_ID'])):?> checked="checked"<?endif;?> name="AUTO_ID" id="auto_add" onclick="$('#extra').val(''); ShowAddAuto();" value="0" /> <strong><label for="auto_add">Добавить автомобиль</label></strong><br />
        </td>
    </tr>
    <tr id="tr_vin">
        <td class="field-name"><?=GetMessage("VIN_CODE")?><span class="starrequired">*</span>:</td>
        <td><input size="57" type="text" name="vin" maxlength="17" value="<?=htmlspecialchars($_REQUEST["vin"])?>" /></td>
    </tr>

        <?else:?>

    <tr>
        <td class="field-name"><?=GetMessage("VIN_CODE")?><span class="starrequired">*</span>:</td>
        <td><input id="lm-auto-vin-input" size="57" type="text" name="vin" maxlength="17" value="<?=htmlspecialchars($_REQUEST["vin"])?>" /></td>
    </tr>

        <?endif;?>
    <?endif?>

    <tr>
        <td class="field-name"><?if(empty($arResult["TICKET"])):?><?=GetMessage("SUP_MESSAGE")?><?else:?><?=GetMessage("MESSAGE")?><?endif;?><span class="starrequired">*</span>:</td>
        <td><textarea name="MESSAGE" id="lm-auto-vin-message" rows="20" cols="45" wrap="virtual"><?=htmlspecialchars($_REQUEST["MESSAGE"])?></textarea></td>
    </tr>
        <?if(empty($arResult["TICKET"])):?>
    <tr>
        <td class="field-name"><label for="FEATURES"><?=GetMessage("COMPLECTATION")?>:</label></td>
        <td>
            <textarea name="extra" id="lm-auto-vin-extra"><?= ($_REQUEST['extra']) ? htmlspecialchars($_REQUEST['extra']) : ('') ?></textarea>
        </td>
    </tr>
            <?if(!CUser::IsAuthorized()) { ?>
    <tr>
        <td></td>
        <td><?= GetMessage('SUP_AUTH_LIKE_UNREGISTER_USER') ?></td>
    </tr>
    <tr>
        <td class="field-name"><label for="NAME"><?=GetMessage("NAME")?><span class="starrequired">*</span>:</label></td>
        <td><input type="text" size="57" name="NAME" id="NAME" value="<?=htmlspecialchars($_REQUEST["NAME"])?>"></td>
    </tr>
    <tr>
        <td class="field-name"><label for="LAST_NAME"><?=GetMessage("SURNAME")?><?if(isset($arParams['REQ_F_LAST_NAME']) && $arParams['REQ_F_LAST_NAME'] == 'Y'){?><span class="starrequired">*</span><?}?>:</label></td>
        <td><input type="text" size="57" name="LAST_NAME" id="LAST_NAME" value="<?=htmlspecialchars($_REQUEST["LAST_NAME"])?>"></td>
    </tr>
    <tr>
        <td class="field-name"><label for="PHONE"><?=GetMessage("PHONE")?><?if(isset($arParams['REQ_F_PHONE']) && $arParams['REQ_F_PHONE'] == 'Y'){?><span class="starrequired">*</span><?}?>:</label></td>
        <td><input type="text" size="57" name="PHONE" id="PHONE" value="<?=htmlspecialchars($_REQUEST["PHONE"])?>"></td>
    </tr>
    <tr>
        <td class="field-name"><label for="EMAIL"><?=GetMessage("EMAIL")?><span class="starrequired">*</span>:</label></td>
        <td><input type="text" size="57" name="EMAIL" id="EMAIL" value="<?=htmlspecialchars($_REQUEST["EMAIL"])?>"></td>
    </tr>
            <?}?>
        <?endif?>

    </tbody>
</table>
</div>
<br />
<input type="submit" name="save" value="<?=GetMessage("SUP_SAVE")?>" />&nbsp;
<input type="button" onclick="$('form[name=support_edit] input[type=text], form[name=support_edit] textarea').val('');" value="<?=GetMessage("SUP_RESET")?>" />
<input type="hidden" value="Y" name="apply" />
<?if (empty($arResult["TICKET"]) && isset($arParams['CATEGORY_ID']) && intval($arParams['CATEGORY_ID']) >0):?>
<input type="hidden" value="<?=$arParams['CATEGORY_ID'];?>" name="CATEGORY_ID"  id="CATEGORY_ID" />
</form>
<?endif?>

<p><span class="starrequired">*</span><?=GetMessage("SUP_REQ")?></p>
    <? endif; ?>
<? endif; ?>
