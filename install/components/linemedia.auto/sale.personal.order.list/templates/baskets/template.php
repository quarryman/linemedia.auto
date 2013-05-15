<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<? $bNoOrder = (count($arResult['BASKETS']) == 0) ?>

<script type="text/javascript" src="<?= $this->getFolder() ?>/js/datatable.js"></script>
<script type="text/javascript" src="<?= $this->getFolder() ?>/js/tablefilter.js"></script>
<script type="text/javascript" src="<?= $this->getFolder() ?>/js/main.js"></script>

<div style="clear: both;"></div>
<? if ($bNoOrder) { ?>
    <center><?= GetMessage('STPOL_NO_ORDERS') ?></center>
<? } else { ?>
    <? if (!empty($arResult['BASKETS'])) { ?>
        <?
            $totalPrice = 0.0;
            $totalCount = 0;
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="orders_table_id">
        <thead>
            <tr align="center" valign="top">
                <th filter="false">
                    <div class="tt" style="margin: 5px 5px;"><?= GetMessage('SPOL_HEAD_DATE') ?></div>
                    <div class="dcal"></div>
                </th>
                <th>
                    <div class="tt"><?= GetMessage('SPOL_ORDER') ?></div>
                </th>
                <th>
                    <div class="tt"><?= GetMessage('SPOL_FIRM') ?></div>
                </th>
                <th>
                    <div class="tt"><?= GetMessage('SPOL_ARTICLE') ?></div>
                </th>
                <th>
                    <div class="tt"><?= GetMessage('SPOL_DESCRIPTION') ?></div>
                </th>
                <th filter="false">
                    <div class="tt"><?= GetMessage('SPOL_QUANTITY') ?></div>
                </th>
                <th filter="false">
                    <div class="tt"><?= GetMessage('SPOL_PRICE') ?></div>
                </th>
                <th filter="false">
                    <div class="tt"><?= GetMessage('SPOL_AMOUNT') ?></div>
                </th>
                <th filter="false">
                    <div class="tt"><?= GetMessage('SPOL_PAYED') ?></div>
                </th>
                <th filter="false">
                    <div class="tt"><?= GetMessage('SPOL_CHANGED') ?></div>
                    <div class="dcal"></div>
                </th>
                <th filter="false">
                    <div class="tt"><?= GetMessage('SPOL_STATE') ?></div>
                    <div class="dque"></div>
                </th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($arResult['BASKETS'] as $basket) {
                $sStatusItem = $basket['STATUS_ID'];
                if (isset($basket['PROPERTIES']['STATUS']['VALUE']) && strlen(trim($basket['PROPERTIES']['STATUS']['VALUE'])) > 0) {
                    $sStatusItem = $basket['PROPERTIES']['STATUS']['VALUE'];
                }
                $key = $basket['STATUS_ID'] ?>
            <tr class="mleft <?= ($key == 'W') ? ('') : ('bag1') ?> tr"
                align="center"<? if ($arParams["USE_STATUS_COLOR"] === 'Y' && isset($arResult['STATUS_COLOR'][$sStatusItem])) { ?>
                bgcolor="<?= $arResult['STATUS_COLOR'][$sStatusItem] ?>"<? } ?>>
                <td class="tda order_date">
                    <?= $basket['DATE_INSERT'] ?>
                </td>
                <td class="tdb order_id">
                    <?= $basket['ORDER_ID'] ?>
                </td>
                <td class="tdb order_brand">
                    <?= $basket['PROPS']['brand_title']['VALUE'] ?>
                </td>
                <td class="tdb order_art">
                    <?= $basket['PROPS']['article']['VALUE'] ?>
                </td>
                <td class="tdb order_name">
                    <span id="OrItemTitleID_<?= $basket['ID'] ?>"><?= $basket['NAME'] ?></span>
                </td>
                <td class="tdb order_quantity">
                    <span id="OrItemQuantityID_<?= $basket['ID'] ?>"><?= $basket['QUANTITY'] ?></span>
                </td>
                <td class="tdb order_currency">
                    <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($basket['PRICE'], 'RUB', $basket['CURRENCY']), $basket['CURRENCY']) ?>
                    <br />
                </td>
                <td class="tdb order_sum">
                    <?
                        $price = CCurrencyRates::ConvertCurrency($basket['PRICE'], 'RUB', $basket['CURRENCY']);
                    
                        $totalCount += $basket['QUANTITY'];
                        $totalPrice += $basket['QUANTITY'] * $price;
                    ?>
                    <? $sum = (float) $basket['QUANTITY'] * $basket['PRICE']; ?>
                    <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($sum, 'RUB', $basket['CURRENCY']), $basket['CURRENCY']) ?>
                    <br />
                </td>
                <td class="tdb  order_status">
                    <? if ($basket['PROPERTIES']['payed']['VALUE'] == 'Y') { ?>
        
                    <a href="#" id="payed_link_<?= $basket['ID'] ?>" class="showpaylink"><?= GetMessage('SPOL_YES') ?></a>
                    <div style="display: none;" class="showpaydialog" id="dialog_payed_link_<?= $basket['ID'] ?>">
                        <h4 style="margin: 0 0 5px 0;">
                            <?= str_replace('#ID#', $basket['ORDER']['ID'], GetMessage('SPOL_POPUP_TITLE')) ?>.
                        </h4>
                        <table>
                            <tr>
                                <td align="right"><?= GetMessage('SPOL_POPUP_ORDER_DATE') ?>:</td>
                                <td style="font-weight: bold;">
                                    <?= $basket['ORDER']['DATE_INSERT_FORMAT'] ?>
                                </td>
                            </tr>
                            <? if (isset($basket['PROPERTIES']['DATE_PAYED']['VALUE'])) { ?>
                                <tr>
                                    <td align="right"><?= GetMessage('SPOL_POPUP_PAYED_DATE') ?>:</td>
                                    <td style="font-weight: bold;">
                                        <?= $basket['PROPERTIES']['DATE_PAYED']['VALUE'] ?>
                                    </td>
                                </tr>
                            <? } ?>
                            <tr>
                                <td align="right"><?= GetMessage('SPOL_POPUP_AMOUNT') ?>:</td>
                                <td style="font-weight: bold;">
                                    <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($basket['PRICE'] * $basket['QUANTITY'], 'RUB', $arResult['CURRENCY']), $arResult['CURRENCY']) ?>
                                </td>
                            </tr>
                            <? if (isset($basket['ORDER']['PAY_SYSTEM']['NAME']) && !empty($basket['ORDER']['PAY_SYSTEM']['NAME'])) { ?>
                                <tr>
                                    <td align="right"><?= GetMessage('SPOL_POPUP_PAYSYSTEM') ?>:</td>
                                    <td style="font-weight: bold;">
                                        <?= $basket['ORDER']['PAY_SYSTEM']['NAME'] ?>
                                    </td>
                                </tr>
                            <? } ?>
                            <? if (isset($basket['ORDER']['DELIVERY']['NAME']) && !empty($basket['ORDER']['DELIVERY']['NAME'])) { ?>
                                <tr>
                                    <td align="right"><?= GetMessage('SPOL_POPUP_DELIVERY') ?>:</td>
                                    <td style="font-weight: bold;">
                                        <?= $basket['ORDER']['DELIVERY']['NAME'] ?>
                                    </td>
                                </tr>
                            <? } ?>
                        </table>
                    </div>
        
                <? } else { ?>
                        <? $ORDER_ID = $basket['ORDER_ID'] ?>
                        <? if ($basket['ORDER']['PAY_SYSTEM']['PSA_NEW_WINDOW'] == 'Y') { ?>
                            <?= GetMessage('SPOL_NO') ?>
                            <? if (
                                !isset($basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE']) ||
                                (isset($basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE']) && $basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE'] === 'Y')
                            ) { 
                            ?>
                                <br />
                                (<a href="<?= $basket['ORDER']['PAY_SYSTEM']['PSA_ACTION_FILE'] ?>" class="paylink2" target="_blank"><?= GetMessage('SPOL_RECEIPT') ?></a>)
                            <? } ?>
                        <? } else { ?>
                                <?= GetMessage('SPOL_NO') ?>
                            <? if (
                                !isset($basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE']) ||
                                (isset($basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE']) && $basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE'] === 'Y')
                            ) {
                                ?>
                                <br />(<a href="/personal/order/make/?ORDER_ID=<?= $basket['ORDER']['ID'] ?>"
                                          id="payed_<?= $basket['ID'] ?>"
                                          class="paylink2"><?= GetMessage('SPOL_PAY') ?></a>)
                                <div style="display: none;" class="payblock">
                                    <? include($basket['ORDER']['PAY_SYSTEM']['PSA_ACTION_FILE']); ?>
                                </div>
                                <? } ?>
                            <? } ?>
                    <? } ?>
                </td>
                <td class="tdbt order_update">
                    <?= $basket['DATE_UPDATE'] ?>
                </td>
                <td class="tdb order_status">
                    <?= $arResult['STATUSES'][$basket['PROPS']['status']['VALUE']]['NAME'] ?>
                </td>
            </tr>
            <? } ?>
        </tbody>
        <tfoot>
            <tr class="tr" align="center" style="height: 40px; font-size: 18px;">
                <td colspan="5">
                    <?= GetMessage('SPOL_TOTAL') ?>:
                </td>
                <td align="center">
                    <?= $totalCount ?>
                </td>
                <td></td>
                <td colspan="4" align="right">
                    <?= CurrencyFormat($totalPrice, 'RUB') ?>
                </td>
            </tr>
        </tfoot>
        </table>
        <? } ?>
        
        <? if (intval($arResult["NAVRECORDCOUNT"]) > $arResult['COUNT_ON_PAGE'][0] && !isset($_REQUEST['SHOWALL_1'])) { ?>
            <div class="pstr">
                <span><?= GetMessage('SPOL_SHOW_ROWS') ?>:</span>
                <? foreach ($arResult['COUNT_ON_PAGE'] as $countOnPage) { ?>
                    <? if ($countOnPage == $_SESSION['COUNT_ON_PAGE']) { ?>
                        <span class="vid"><?= $countOnPage ?></span> |
                    <? } else { ?>
                        <a href="?pagesize=<?= $countOnPage ?>"><?= $countOnPage ?></a> |
                    <? } ?>
                <? } ?>
            </div>
        <? } ?>
    <br />
    <?= $arResult['NAV_STRING'] ?>
      
<? } ?>
