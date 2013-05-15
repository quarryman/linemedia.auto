<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>
<form method="post" action="<?=$arResult["NEW_TICKET_PAGE"]?>">
    <input type="submit" name="add" class="btn" value="<?=GetMessage("LM_AUTO_VIN_ASK")?>">
</form>
<?if (strlen($arResult["NAV_STRING"]) > 0):?>
    <?=$arResult["NAV_STRING"]?><br />
<?endif?>
<table cellspacing="0" class="lm-auto-vin-ticket-list data-table table">
    <tr class="lm-auto-vin-header-tr">
        <th class="lm-auto-vin-th-id"><?=GetMessage("LM_AUTO_VIN_ID")?></th>
        <th class="lm-auto-vin-th-vin"><?=GetMessage("LM_AUTO_VIN_VIN")?></th>
        <th class="lm-auto-vin-th-date"><?=GetMessage("LM_AUTO_VIN_DATE")?></th>
        <th class="lm-auto-vin-th-answer"><?=GetMessage("LM_AUTO_VIN_ISSET_ANSWER")?></th>
    </tr>
    <?foreach ($arResult["TICKETS"] as $arTicket):?>
    <tr class="lm-auto-vin-tr-request">
        <td width="10%" align="center" class="lm-auto-vin-td-id"><?=$arTicket["ID"]?></td>
        <td class="lm-auto-vin-td-vin">
                <a href="<?=$arTicket["TICKET_SHOW_URL"]?>" title="<?=GetMessage("LM_AUTO_VIN_VIEW_TICKET")?>"><?=$arTicket["PROPERTY_VIN_VALUE"]?></a>
        </td>
        <td class="lm-auto-vin-td-date"><?=$arTicket["DATE_CREATE"]?></td>
        <td class="lm-auto-vin-td-answer"><?if(!empty($arTicket["PROPERTY_ANSWER_VALUE"])){?><?=GetMessage("LM_AUTO_VIN_YES")?><?}else{?><?=GetMessage("LM_AUTO_VIN_NO")?><?}?></td>
    </tr>
    <?endforeach?>
    <tr class="lm-auto-vin-footer-tr"><th colspan="4" class="lm-auto-vin-footer-total"><?=GetMessage("LM_AUTO_VIN_TOTAL")?>: <?=$arResult["TICKETS_COUNT"]?></th></tr>
</table>

<?if (strlen($arResult["NAV_STRING"]) > 0):?>
    <br /><?=$arResult["NAV_STRING"]?><br />
<?endif?>