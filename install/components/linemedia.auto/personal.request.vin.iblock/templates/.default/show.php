<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>
<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] AS $error) { ?>
        <? ShowError($error) ?>
    <?}?>
<?}?>

<?if(!empty($arResult['MESSAGE'])){
    ShowMessage(array('MESSAGE' => $arResult['MESSAGE'], 'TYPE' => 'OK'));
    return;
}?>
<a class="btn" href="<?=$arResult['TICKET_LIST_URL'];?>"><?=GetMessage('LM_AUTO_VIN_RETURN_LIST_REQUEST');?></a>
<br />
<div class="lm-auto-vin">
    <div>
        <table class="lm-auto-vin-table table">
            <thead>
                <tr class="lm-auto-vin-tr-header">
                    <th colspan="2" class="lm-auto-vin-th-header"><?= GetMessage('LM_AUTO_VIN_MAIN_HEADER', Array('#ID#' => $arResult['ID'])) ?></th>
                </tr>
            </thead>
            <tbody>
            
                
                <tr><td colspan="2" class="lm-auto-vin-td-answer">
                <?if(empty($arResult['DETAIL']['ANSWER'])){?>
                <?if(!empty($arResult['DETAIL']['MANAGER'])){?>
                    <strong><?=GetMessage('LM_AUTO_VIN_YOUR_MANAGER');?> <?=$arResult['DETAIL']['MANAGER']['LAST_NAME'];?> <?=$arResult['DETAIL']['MANAGER']['NAME'];?> <?=$arResult['DETAIL']['MANAGER']['SECOND_NAME'];?><br /></strong>
                <?}?>
                    <div class="alert alert-warning"><?=GetMessage('LM_AUTO_VIN_ANSWER_NOT_ISSET');?></div>
                <?}else{?>
                    <div class="alert alert-success">
                    <strong><?=GetMessage('LM_AUTO_VIN_ANSWER_MANAGER');?> <?=$arResult['DETAIL']['ANSWER_MANAGER']['LAST_NAME'];?><?=$arResult['DETAIL']['ANSWER_MANAGER']['NAME'];?> <?=$arResult['DETAIL']['ANSWER_MANAGER']['SECOND_NAME'];?> (<?=$arResult['DETAIL']['ANSWER_DATE'];?>)</strong><br /><br />
                    <?=$arResult['DETAIL']['ANSWER']['TEXT'];?>
                    </div>
                <?}?>
                </td></tr>            
            
            
            
            
                <tr><td colspan="2" align="center" class="lm-auto-vin-td-auto-info"><strong><?=GetMessage('LM_AUTO_VIN_AUTO_INFO');?></strong></td></tr>
                <?
                if(is_array($arResult['DETAIL']['PROPS']) && count($arResult['DETAIL']['PROPS']) > 0){
                    foreach($arResult['DETAIL']['PROPS'] AS $field_code => $field){
                        switch($field_code){
                            default:
                                if(!empty($field['VALUE'])){
?>
<tr class="lm-auto-vin-tr-prop-<?=$field['CODE'];?> car_prop">
    <td  class="lm-auto-vin-td-title-<?=$field['CODE'];?> left_col" width="50%" valign="top">
        <?=$field['NAME'];?>:
    </td>
    <td valign="top" class="lm-auto-vin-td-value-<?=$field['CODE'];?> right_col">
        <?if($field['USER_TYPE'] === 'HTML'){?>
        <?=$field['VALUE']['TEXT'];?>
        <?}elseif($field['PROPERTY_TYPE'] === 'L'){
            if($field['MULTIPLE'] === 'Y'){?>
                    <?=implode(', ', $field['VALUE']);?>
            <?}else{?>
                <?=$field['VALUE'];?>
            <?}
        }else{?>
            <?=$field['VALUE'];?>
        <?}?>
    </td>
</tr>
<?
                                }
                            break;
                        }
                    }
                    unset($field, $field_code);
                ?>
                <?}?>
                <tr><td colspan="2" class="lm-auto-vin-td-request-info" align="center"><strong><?=GetMessage('LM_AUTO_VIN_REQUEST_INFO');?></strong></td></tr>
                <tr><td colspan="2" class="lm-auto-vin-td-request-table">
                <?if(is_array($arResult['DETAIL']['REQUEST']) && count($arResult['DETAIL']['REQUEST']) > 0){?>
                <table cellpadding="0" cellspacing="0" border="0" width="100%" class="lm-auto-vin-request-table table table-bordered ">
                    <tbody>
                        <tr class="lm-auto-vin-request-tr-header">
                            <th class="lm-auto-vin-request-td-header-title"><?= GetMessage('LM_AUTO_VIN_REQUEST_TITLE') ?></th>
                            <th class="lm-auto-vin-request-td-header-art"><?= GetMessage('LM_AUTO_VIN_REQUEST_ART') ?></th>
                            <th class="lm-auto-vin-request-td-header-quantity"><?= GetMessage('LM_AUTO_VIN_REQUEST_QUANTITY') ?></th>
                            <th class="lm-auto-vin-request-td-header-comment"><?= GetMessage('LM_AUTO_VIN_REQUEST_COMMENT') ?></th>
                        </tr>
                        <tr></tr>
                        <?foreach($arResult['DETAIL']['REQUEST'] AS $request_item){?>
                        <tr>
                            <td class="lm-auto-vin-request-td-item-title"><div><?=$request_item['title'];?></div></td>
                            <td class="lm-auto-vin-request-td-item-art"><div><?=$request_item['art'];?></div></td>
                            <td class="lm-auto-vin-request-td-item-quantity"><div><?=$request_item['quantity'];?></div></td>
                            <td class="lm-auto-vin-request-td-item-comment"><div><?=$request_item['comment'];?></div></td>
                        </tr>
                        <?}
                        unset($request_item);
                        ?>
                    </tbody>
                </table>
                <?}?>
                </td></tr>
                
            </tbody>
        </table>
    </div>
</div>
<a href="<?=$arResult['TICKET_LIST_URL'];?>"><?=GetMessage('LM_AUTO_VIN_RETURN_LIST_REQUEST');?></a>