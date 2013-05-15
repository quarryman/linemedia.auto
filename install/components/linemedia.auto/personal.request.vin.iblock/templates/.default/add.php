<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>
<?php
if(!function_exists('vin_show_input')){
    function vin_show_input ($field = false) {
        if(!is_array($field)){
            return false;
        }
        ?>
        <tr class="lm-auto-vin-tr-prop-<?=$field['CODE'];?>">
            <td class="lm-auto-vin-td-prop-title-<?=$field['CODE'];?> left_col">
                <?if($field['PROPERTY_TYPE'] !== 'L'){?><label for="lm-auto-vin-field-<?=$field['CODE'];?>"><?}?>
                <?=$field['NAME'];?>
                <?if($field['PROPERTY_TYPE'] !== 'L'){?></label><?}?>
                <?if($field['IS_REQUIRED'] === 'Y'){?><span class="starrequired">*</span><?}?>
            </td>
            <td class="lm-auto-vin-td-prop-input-<?=$field['CODE'];?> right_col">
        <?
        switch($field['PROPERTY_TYPE']){
            default:
            case 'S':
                ?>
                <input id="lm-auto-vin-field-<?=$field['CODE'];?>" type="text" maxlength="255" name="<?=$field['CODE'];?>" value="<?=$field['VALUE'];?>" />
                <?
            break;
            case 'L':
                if($field['LIST_TYPE'] == 'L'){
                ?>
                <select id="lm-auto-vin-field-<?=$field['CODE'];?>" name="<?=$field['CODE'];?>">
                    <?if($field['IS_REQUIRED'] === 'N'){?><option><?=GetMessage('LM_AUTO_VIN_NO_SELECTED');?></option><?}?>
                    <?if(is_array($field['ENUM']) && count($field['ENUM']) > 0){?>
                        <?foreach($field['ENUM'] AS $enum){?>
                            <option value="<?=$enum['ID'];?>"<?=($field['VALUE'] === $enum['ID'])?' selected="selected"':'';?>><?=$enum['VALUE'];?></option>
                        <?}?>
                    <?}else{?>
                        <option><?=GetMessage('LM_AUTO_VIN_NO_OPTIONS');?></option>
                    <?}?>
                </select>
                <?
                }elseif($field['LIST_TYPE'] == 'C'){
                    if($field['MULTIPLE'] === 'Y'){
                    ?>
                        <?if(is_array($field['ENUM']) && count($field['ENUM']) > 0){?>
                            <?foreach($field['ENUM'] AS $enum){?>
                                <input type="checkbox" id="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>" name="<?=$field['CODE'];?>[]" value="<?=$enum['ID'];?>"<?=(in_array($enum['ID'], $field['VALUE']))?' checked="checked"':'';?>> <label for="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>"><?=$enum['VALUE'];?></label>
                            <?}?>
                        <?}else{?>
                            <?=GetMessage('LM_AUTO_VIN_NO_OPTIONS');?>
                        <?}?>
                    <?
                    }else{
                    ?>
                        <?if(is_array($field['ENUM']) && count($field['ENUM']) > 0){?>
                            <?
                            if(empty($field['VALUE']) && $field['IS_REQUIRED'] === 'Y'){
                                $first_enum = current($field['ENUM']);
                                $field['VALUE'] = $first_enum['ID'];
                                unset($first_enum);
                            }elseif($field['IS_REQUIRED'] === 'N'){
                                ?>
                                <input type="radio" id="lm-auto-vin-field-<?=$field['CODE'];?>-empty" name="<?=$field['CODE'];?>" value=""<?=(empty($field['VALUE']))?' checked="checked"':'';?>> <label for="lm-auto-vin-field-<?=$field['CODE'];?>-empty"><?=GetMessage('LM_AUTO_VIN_NO_SELECTED');?></label>
                                <?
                            }
                            foreach($field['ENUM'] AS $enum){?>
                                <input type="radio" id="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>" name="<?=$field['CODE'];?>" value="<?=$enum['ID'];?>"<?=($field['VALUE'] == $enum['ID'])?' checked="checked"':'';?>> <label for="lm-auto-vin-field-<?=$field['CODE'];?>-<?=$enum['ID'];?>"><?=$enum['VALUE'];?></label>
                            <?
                            }
                            ?>
                        <?}else{?>
                            <?=GetMessage('LM_AUTO_VIN_NO_OPTIONS');?>
                        <?}?>
                    <?  
                    }
                }
            break;
        }
        ?>
            </td>
        </tr>
        <?
    }
}
?>
<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] as $error) { ?>
        <? ShowError($error) ?>
    <? } ?>
<? } ?>


<? if (!empty($arResult['MESSAGE'])) {
?>
<a href="<?=$arResult['TICKET_LIST_URL'];?>"><span class="return-icon"></span><?=GetMessage('LM_AUTO_VIN_RETURN_LIST_REQUEST');?></a>
<?
    ShowMessage(array('MESSAGE' => $arResult['MESSAGE'], 'TYPE' => 'OK'));
    return;
} ?>

<div class="lm-auto-vin">
    <h2 class="blue-title"><?= GetMessage('LM_AUTO_VIN_MAIN_HEADER') ?></h2>    
    <div class="silver-block">
        <form id="lm-auto-vin-form" method="post">
            <?= bitrix_sessid_post() ?>
   <!-- <div class="yellow-block">
    	 <?if(!empty($arResult['MANAGER'])){?>
              <?=GetMessage('LM_AUTO_VIN_YOUR_MANAGER');?> <?=$arResult['MANAGER']['LAST_NAME'];?> <?=$arResult['MANAGER']['NAME'];?> <?=$arResult['MANAGER']['SECOND_NAME'];?>
         <?}?>
    </div>
    <?if($USER->IsAuthorized()){?><a class="return_list_orders" href="<?=$arResult['TICKET_LIST_URL'];?>"><span class="return-icon"></span><?=GetMessage('LM_AUTO_VIN_RETURN_LIST_REQUEST');?></a><div class="clr"></div><?}?>	 
</div>  -->
     
<div class="html">
    <? if (!empty($arResult['HTML'])) { ?>
		<? foreach ($arResult['HTML'] as $html) { ?>
			<?= $html ?>
		<? } ?>
	<? } ?>
</div> 

            
                <table class="lm-auto-mew-vin-table table">
                    <tbody>
                         
                        <?
                        if(is_array($arResult['FIELDS']['MAIN']) && count($arResult['FIELDS']['MAIN']) > 0){
                            foreach($arResult['FIELDS']['MAIN'] AS $field_code => $field){
                                switch($field_code){
                                    default:
                                        vin_show_input($field);
                                    break;
                                    case 'vin':
                                        vin_show_input($field);
                                        //после поля вин выведем подбор авто
                                        $info_actions = Array('getBrands');
                                        if(!empty($arResult['FIELDS']['HIDDEN']['brand_id']['VALUE'])){
                                            $info_actions[] = 'getModels';
                                        }
                                        if(!empty($arResult['FIELDS']['HIDDEN']['model_id']['VALUE'])){
                                            $info_actions[] = 'getModifications';
                                        }
                                        $APPLICATION->IncludeComponent(
                                            "linemedia.auto:tecdoc.auto.select",
                                            "vin.iblock",
                                            array(
                                                "ACTIONS" => $info_actions,
                                                "BRAND_ID" => $arResult['FIELDS']['HIDDEN']['brand_id']['VALUE'],
                                                "MODEL_ID" => $arResult['FIELDS']['HIDDEN']['model_id']['VALUE'],
                                                "MODIFICATION_ID" => $arResult['FIELDS']['HIDDEN']['modification_id']['VALUE']
                                            ),
                                            false
                                        );
                                    break;
                                    case 'extra':
                                        ?>
                                        <tr class="lm-auto-vin-tr-prop-extra">
                                            <td class="lm-auto-vin-td-prop-title-extra left_col">
                                                <label for="lm-auto-vin-field-<?=$field_code;?>"><?=$field['NAME'];?></label>
                                                <?if($field['IS_REQUIRED'] === 'Y'){?><span class="starrequired">*</span><?}?>
                                            </td>
                                            <td class="lm-auto-vin-td-prop-input-extra right_col"><textarea id="lm-auto-vin-field-<?=$field_code;?>" name="<?=$field_code;?>"><?=$field['VALUE'];?></textarea></td>
                                        </tr>
                                        <?
                                    break;
                                }
                            }
                            unset($field, $field_code);
                        ?>
                        <?}?>
                        
                        
                        <?if($arResult['IS_GARAGE'] === true){?>
                            <tr class="lm-auto-vin-tr-auto-add" id="lm-auto-vin-tr-auto-add"<?if(!empty($_REQUEST['garage-item-id'])){?> style="display: none;"<?}?>><td class="lm-auto-vin-td-auto-add" colspan="2">
                                <input type="checkbox" name="AutoAdd" value="Y" id="lm-auto-vin-auto-add"<?if($arResult['AUTO_ADD'] === 'Y'){?> checked="checked"<?}?> />&nbsp;&nbsp;<label for="lm-auto-vin-auto-add"> <?= GetMessage('LM_AUTO_VIN_AUTO_ADD') ?></label>
                            </td></tr>
                        <?}?>
                        
                        
                        
                    </tbody>
                </table>
                
                
                <table class="lm-auto-vin-table table">
                    <thead>
                        <tr>
                            <th colspan="2" class="lm-auto-vin-extra-header"><a href="javascript: void(0);" class="blue-italic-link"><?= GetMessage('LM_AUTO_VIN_EXTRA_HEADER') ?><img src="<?=$templateFolder;?>/images/down.png" width="4" height="4" /></a></th>
                        </tr>
                    </thead>
                    <tbody class="lm-auto-vin-extra-tbody" id="lm-auto-vin-extra-tbody"<?=(isset($_REQUEST['show_extra_info']) && $_REQUEST['show_extra_info'] === 'Y')?'':' style="display: none;"';?>>
                        <?
                        if(is_array($arResult['FIELDS']['EXTRA']) && count($arResult['FIELDS']['EXTRA']) > 0){
                            foreach($arResult['FIELDS']['EXTRA'] AS $field_code => $field){
                                switch($field_code){
                                    default:
                                        vin_show_input($field);
                                    break;
                                }
                            }
                            unset($field, $field_code);
                        ?>
                        <?}?>
                    </tbody>
                </table>
                <input type="hidden" name="show_extra_info" id="lm-auto-vin-show-extra-info" value="<?=(isset($_REQUEST['show_extra_info']) && $_REQUEST['show_extra_info'] === 'Y')?'Y':'N';?>" />
                 
            </div>

  		<h2 class="blue-title"><?= GetMessage('LM_AUTO_VIN_REQUEST_HEADER') ?></h2>
           
                <table class="lm-auto-vin-request-table" id="lm-auto-vin-table-request">
                    <thead>
                        <tr>
                            <th class="lm-auto-vin-th-request-action"></th>
                            <th class="lm-auto-vin-th-request-title"><?= GetMessage('LM_AUTO_VIN_REQUEST_TITLE') ?></th>
                            <th class="lm-auto-vin-th-request-art"><?= GetMessage('LM_AUTO_VIN_REQUEST_ART') ?></th>
                            <th class="lm-auto-vin-th-request-quantity"><?= GetMessage('LM_AUTO_VIN_REQUEST_QUANTITY') ?></th>
                            <th class="lm-auto-vin-th-request-comment"><?= GetMessage('LM_AUTO_VIN_REQUEST_COMMENT') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?if(is_array($arResult['REQUEST']['VALUE']) && count($arResult['REQUEST']['VALUE']) > 0){
                            $i=1;
                            foreach($arResult['REQUEST']['VALUE'] AS $request_item){
                        ?>
                        <tr class="part_row">
                            <td class="lm-auto-vin-td-request-action"><?=$i?><?if($i>1){?><a href="javascript: void(0);" class="lm-auto-vin-row-del">
                            <img src="<?=$templateFolder;?>/images/delete.png" width="14" height="15" />
                            </a><?}?></td>
                            <td class="lm-auto-vin-td-request-title"><input type="text" name="request[title][]" value="<?=$request_item['title'];?>" /></td>
                            <td class="lm-auto-vin-td-request-art"><input type="text" name="request[art][]" value="<?=$request_item['art'];?>" /></td>
                            <td class="lm-auto-vin-td-request-quantity"><input type="text" name="request[quantity][]" value="<?=$request_item['quantity'];?>" /></td>
                            <td class="lm-auto-vin-td-request-comment"><input type="text" name="request[comment][]" value="<?=$request_item['comment'];?>" /></td>
                        </tr>
                        <?
                            $i++; 
                            }
                        }else{?>
                        <tr  class="part_row">
                            <td class="lm-auto-vin-td-request-action"></td>
                            <td class="lm-auto-vin-td-request-title"><input type="text" name="request[title][]" value="" /></td>
                            <td class="lm-auto-vin-td-request-art"><input type="text" name="request[art][]" value="" /></td>
                            <td class="lm-auto-vin-td-request-quantity"><input type="text" name="request[quantity][]" value="" /></td>
                            <td class="lm-auto-vin-td-request-comment"><input type="text" name="request[comment][]" value="" /></td>
                        </tr>
                        <?}?>
                        <tr class="lm-auto-vin-tr-request-add-row">
                            <td class="lm-auto-vin-td-request-add-row-icon"><a href="javascript: void(0);" class="lm-auto-vin-row-add"><img src="<?=$templateFolder;?>/images/add.png" width="14" height="15" /></a></td>
                            <td class="lm-auto-vin-td-request-add-row-link" colspan="4"><a href="javascript: void(0);" class="lm-auto-vin-row-add blue-italic-link"><?= GetMessage('LM_AUTO_VIN_REQUEST_ADD_ROW') ?></a></td>
                        </tr>
                    </tbody>
                </table>
                
           		<div class="vin-buttons">
                    <input type="submit" name="save" class="btn" value="<?= GetMessage('LM_AUTO_VIN_SUBMIT') ?>" />&nbsp;&nbsp;
                    <input type="reset" name="reset" class="btn" value="<?= GetMessage('LM_AUTO_VIN_RESET') ?>"/>
                </div>
        </form>
</div>