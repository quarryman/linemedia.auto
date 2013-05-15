<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {

    die();

}



$APPLICATION->AddHeadScript($templateFolder . '/js/jquery.tablesorter.min.js');



if (!empty($arResult)) { ?>

<div class="silver-block">

<p><strong><?=GetMessage('LM_AUTO_TRANSACTIONS_CASH')?></strong>:    <span class="big-font-price"><?=$arResult['cash']?></span></p>

<p><strong><?=GetMessage('LM_AUTO_TRANSACTIONS_TO_PAY')?></strong>: <span class="big-font-price"><?=$arResult['sum_to_pay_currency']?></span> (<a target="_blank" href="<?=$arParams['ORDERS_PATH']?>?PAYED=N&CANCELED=N"><?=GetMessage('LM_AUTO_TRANSACTIONS_TO_PAY_ORDERS')?></a>)</p>



<label><?=GetMessage('LM_AUTO_TRANSACTIONS_DATE_FILTER')?>:</label>

<form action="<?=$APPLICATION->GetCurPage()?>" method="POST" name="time_period">

    <?echo CalendarPeriod("date_from", "{$arResult['date_from']}", "date_to", "{$arResult['date_to']}", "time_period", "N")?>

<table class="transactions_filter">

    <tr>

        <td>

            <label for="trans_id"><?=GetMessage('LM_AUTO_TRANSACTIONS_ID_FILTER')?>:</label>

            <input type="text" size="40" name="trans_id" id="trans_id" value="<?=$arResult['trans_id']?>">

        </td>

        <td>

            <label for="order_id"><?=GetMessage('LM_AUTO_TRANSACTIONS_ORDER_ID_FILTER')?>:</label>

            <input type="text" size="40" name="order_id" id="order_id" value="<?=$arResult['order_id']?>">

        </td>

    </tr>

</table>

    <input type="submit" class="btn" value="<?=GetMessage('LM_AUTO_TRANSACTIONS_SHOW')?>">

</form>

</div>

<table class="lm-auto-transactions">

    <thead>

        <tr>

            <th class="id">¹ </th>

            <th class="sum"><?=GetMessage('LM_AUTO_TRANSACTIONS_SUM')?></th>

            <th class="description"><?=GetMessage('LM_AUTO_TRANSACTIONS_DESCRIPTION')?></th>

            <th class="order-id"><?=GetMessage('LM_AUTO_TRANSACTIONS_ORDER_ID')?></th>

            <th class="date"><?=GetMessage('LM_AUTO_TRANSACTIONS_TRANS_DATE')?></th>

        </tr>

    </thead>

    <tbody>



    <?php foreach ($arResult['transactions'] as $key => $transaction) { ?>

        <tr>

            <td><?=$transaction['ID']?></td>

            <td><span title="<?=(int)$transaction["AMOUNT"]?>"><?=($transaction["DEBIT"]=="Y")?"+ ":"- "?><?=SaleFormatCurrency($transaction["AMOUNT"], $arResult['site_base_currency'])?><br /><small>(<?=($transaction["DEBIT"]=="Y")? GetMessage('LM_AUTO_TRANSACTIONS_PLUS_SUM'):GetMessage('LM_AUTO_TRANSACTIONS_MIN_SUM')?>)</small></span></td>

             <td><?=$transaction['NOTES']?></td>

            <td><a target="_blank" href="<?=$arParams['ORDERS_PATH']?>?ORDER_ID=<?=$transaction['ORDER_ID']?>"><?=$transaction['ORDER_ID']?></a></td>

            <td><span title="<?=MakeTimeStamp($transaction['TRANSACT_DATE'])?>"><?=$transaction['TRANSACT_DATE']?></span></td>

        </tr>

    <?php } ?>



    </tbody>

</table>

    <?php

}

