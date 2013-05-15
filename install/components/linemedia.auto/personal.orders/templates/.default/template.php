<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<script type="text/javascript" src="<?= $this->getFolder() ?>/js/datatable.js"></script>
<script type="text/javascript" src="<?= $this->getFolder() ?>/js/tablefilter.js"></script>
<script type="text/javascript" src="<?= $this->getFolder() ?>/js/main.js"></script>

<? function showBasketRow($basket, $arParams, $display = 'auto') { ?>
    <tr
        class="mleft <?= ($key == 'W') ? ('') : ('bag1') ?> tr"
        align="center"<? if ($arParams["USE_STATUS_COLOR"] === 'Y' && isset($arResult['STATUS_COLORS'][$sStatusItem])) { ?>
        bgcolor="<?= $arResult['STATUS_COLORS'][$sStatusItem] ?>"<? } ?>
        rel="order-<?= $basket['ORDER_ID'] ?>"
        style="display: <?= $display ?>;"
    >
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
            <? $sum = (float) $basket['QUANTITY'] * $basket['PRICE']; ?>
            <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($sum, 'RUB', $basket['CURRENCY']), $basket['CURRENCY']) ?>
            <br />
        </td>
        <td class="tdb order_delivery_sum">
            <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($basket['ORDER']['PRICE_DELIVERY'], 'RUB', $basket['ORDER']['CURRENCY']), $basket['ORDER']['CURRENCY']) ?>
        </td>
        <td class="tdb  order_status">
            <? if ($basket['PROPS']['payed']['VALUE'] == 'Y') { ?>

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
                                <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($basket['PRICE'] * $basket['QUANTITY'], 'RUB', $basket['CURRENCY']), $basket['CURRENCY']) ?>
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
                        <br />(<a href="<?= $arParams['PATH_TO_PAYMENT'] ?>?ORDER_ID=<?= $basket['ORDER']['ID'] ?>"
                                  id="payed_<?= $basket['ID'] ?>"
                                  target="_blank"
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
            <? if ($arParams['USE_STATUS_COLOR']) { ?>
                <span style="font-size: 16px; color: <?= $basket['STATUS_COLOR'] ?>;">&bull;</span>
            <? } ?>
            <?= $basket['STATUS_NAME'] ?>
        </td>
    </tr>
<? } ?>


<div class="silver-block">
<form method="post">
    <div class="filter_orders">
        <table width="100%">
            <tr>
                <td valign="top" width="50%">
                    <h4><?= GetMessage('SPOL_FILTER_ORDER_ID') ?></h4>
                    <table>
                        <tr>
                            <td><input type="text" name="ORDER_ID" value="<?= (!empty($_REQUEST['ORDER_ID']) ? trim($_REQUEST['ORDER_ID']) : '') ?>" /></td>
                        </tr>
                    </table>
                </td>
                <td valign="top">
                    <h4><?= GetMessage('SPOL_FILTER_NAME') ?></h4>
                    <table>
                        <tr>
                            <td><input type="text" name="NAME" value="<?= (!empty($_REQUEST['NAME']) ? trim($_REQUEST['NAME']) : '') ?>" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <h4><?= GetMessage('SPOL_FILTER_ARTICLE') ?></h4>
                    <table>
                        <tr>
                            <td><input type="text" name="ARTICLE" value="<?= (!empty($_REQUEST['ARTICLE']) ? trim($_REQUEST['ARTICLE']) : '') ?>" /></td>
                        </tr>
                    </table>
                </td>
                <td valign="top">
                    <h4><?= GetMessage('SPOL_FILTER_BRAND') ?></h4>
                    <table>
                        <tr>
                            <td><input type="text" name="BRAND" value="<?= (!empty($_REQUEST['BRAND']) ? trim($_REQUEST['BRAND']) : '') ?>" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="filter_orders ">
        <h4><?= GetMessage('SPOL_FILTER_STATUS') ?></h4>
        <table cellpadding="0" cellspacing="0" class="filter_orders">
            <tr>
                <? $i = 1 ?>
                <? foreach ($arResult['STATUSES'] as $status) { ?>
                <td>
                    <input
                        type="checkbox"
                        name="STATUS[<?= $status['ID'] ?>]"
                        id="status-<?= $status['ID'] ?>-id"
                        value="<?= $status['ID'] ?>"
                        <?= ($_REQUEST['STATUS'][$status['ID']]) ? ('checked') : ('') ?>
                    />
                    <label for="status-<?= $status['ID'] ?>-id">
                        <?= $status['NAME'] ?>
                    </label>
                </td>
                <?
                if ($i++ == 4) {
                    echo "</tr><tr>";
                    $i = 1;
                }
                ?>
                <? } ?>
            </tr>
        </table>
        <br/>
        <input type="submit" value="<?= GetMessage('SPOL_FILTER_SHOW') ?>" />
    </div>
</form>

</div>


<div style="clear: both;"></div>
<? if (empty($arResult['BASKETS'])) { ?>
    <center><?= GetMessage('STPOL_NO_ORDERS') ?></center>
<? } else { ?>
    <? if (!empty($arResult['BASKETS'])) { ?>
        <?
            $totalPrice = 0.0;
            $totalCount = 0;
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="lm-auto-orders-table-id">
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
                        <div class="tt"><?= GetMessage('SPOL_DELIVERY_PRICE') ?></div>
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
                <? $first = true; ?>
                <? foreach ($arResult['GROUPS'] as $group) { ?>
                    <? if ($arParams['UNION_BY_ORDERS']) { ?>
                        <? $basket = $arResult['BASKETS'][reset($group)]; ?>
                        <? $class   = ($first) ? ('lm-auto-order-toggle-expand') : ('lm-auto-order-toggle-turn'); ?>
                        <? $display = ($first) ? ('auto') : ('none'); ?>
                        <? $first = false; ?>
                        <tr class="lm-auto-order-group">
                            <td>
                                <?= $basket['DATE_INSERT'] ?>
                            </td>
                            <td colspan="2">
                                <?= GetMessage('SPOL_ORDER') ?>: <b><?= $basket['ORDER_ID'] ?></b>
                            </td>
                            <td colspan="3"></td>
                            <td colspan="2">
                                <b><?= CurrencyFormat($basket['ORDER']['PRICE'], $basket['ORDER']['CURRENCY']) ?></b>
                            </td>
                            <td>
                                <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($basket['ORDER']['PRICE_DELIVERY'], 'RUB', $basket['ORDER']['CURRENCY']), $basket['ORDER']['CURRENCY']) ?>
                            </td>
                            <td colspan="3" align="right">
                                <div class="lm-auto-order-toggle <?= $class ?>" rel="<?= $basket['ORDER_ID'] ?>"></div>
                            </td>
                        </tr>
                    <? } ?>
                    <?  // Подсчет общих сумм и вывод корзины.
                        $totalCount += $basket['QUANTITY'];
                        $totalPrice += CCurrencyRates::ConvertCurrency($basket['ORDER']['PRICE'], 'RUB', $basket['ORDER']['CURRENCY']);
                    ?>
                    <? foreach ($group as $basketID) { 
                         $basket = $arResult['BASKETS'][$basketID];
                         showBasketRow($basket, $arParams, $display); ?>
                    <? } ?>
                <? } ?>
            </tbody>
            <tfoot>
                <tr class="tr" align="center" style="height: 40px; font-size: 18px;">
                    <td colspan="5">
                        <?= GetMessage('SPOL_TOTAL') ?>:
                    </td>
                    <td align="center">
                        <?= $arResult['TOTAL_COUNT'] ?>
                    </td>
                    <td colspan="6" align="right">
                        <?= CurrencyFormat($arResult['TOTAL_PRICE'], 'RUB') ?>
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
