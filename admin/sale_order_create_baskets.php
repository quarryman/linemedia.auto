<? if (!empty($arResult['BASKETS'])) { ?>
    <script type="text/javascript">
        $('#b_order_save').removeAttr('disabled');
        $('#b_order_make_stay').removeAttr('disabled');
        $('#b_order_make_go').removeAttr('disabled');
    </script>
<? } ?>

<form id="f_order" onsubmit="return false;" action="" method="post">
    <table cellspacing="0" cellpadding="0" border="0" id="edit2_edit_table" class="edit-table">
        <tbody>
            <tr>
                <td colspan="2">
                    <table width="100%" cellspacing="1" cellpadding="3" border="0" class="internal">
                        <tbody>
                            <tr class="heading">
                                <td width="14"></td>
                                <td><?= GetMessage('BASKET_FIRM') ?></td>
                                <td><?= GetMessage('BASKET_ARTICLE') ?></td>
                                <td><?= GetMessage('BASKET_SUPPLIER_NAME') ?></td>
                                <td><?= GetMessage('BASKET_NAME') ?></td>
                                <td width="100"><?= GetMessage('BASKET_QUANTITY') ?></td>
                                <td width="120"><?= GetMessage('BASKET_PRICE') ?></td>
                                <td width="120"><?= GetMessage('BASKET_SUMM') ?></td>
                            </tr>
                            <? $obasket = new LinemediaAutoBasket(); ?>
                            <? foreach ($arResult['BASKETS'] as $basket) { ?>
                                <? $props = $obasket->getProps($basket['ID']); ?>
                                <tr>
                                    <td align="center">
                                        <a href="javascript: void(0);" onclick="MakeOrder({'action': 'del', 'id': '<?= $basket['ID'] ?>'}); return false;">
                                            <img src="/bitrix/themes/.default/images/signminus.gif" width="11" height="11" alt="" border="0" />
                                        </a>
                                    </td>
                                    <td align="center">
                                        <?= $props['brand_title']['VALUE'] ?>
                                    </td>
                                    <td align="center">
                                        <?= $props['article']['VALUE'] ?>
                                    </td>
                                    <td align="center">
                                        <?= $props['supplier_title']['VALUE'] ?>
                                    </td>
                                    <td align="center">
                                        <?= $basket['NAME'] ?>
                                    </td>
                                    <td align="right">
                                        <input type="text" size="3" name="basket[<?= $basket['ID'] ?>]" value="<?= (int) $basket['QUANTITY'] ?>" style="text-align: right;" />
                                    </td>
                                    <td align="right">
                                        <?= CurrencyFormat($basket['PRICE'], $basket['CURRENCY']) ?>
                                    </td>
                                    <td align="right">
                                        <?= CurrencyFormat($basket['PRICE'] * $basket['QUANTITY'], $basket['CURRENCY']) ?>
                                    </td>
                                </tr>
                            <? } ?>
                            <tr>
                                <td colspan="2">
                                    <b><?= GetMessage('BASKET_COMMON_SUMM') ?>:</b>
                                </td>
                                <td colspan="7" align="right">
                                    <?= CurrencyFormat($arResult['ALL_PRICE'], $arResult['CURRENCY']) ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <b><?= GetMessage('BASKET_COMMON_DISCOUNT') ?>:</b>
                                </td>
                                <td colspan="7" align="right">
                                    <?= CurrencyFormat($arResult['DISCOUNT_PRICE'], $arResult['CURRENCY']) ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <b><?= GetMessage('BASKET_COMMON_DELIVERY') ?>:</b>
                                </td>
                                <td colspan="7" align="right">
                                    <?= CurrencyFormat($arResult['DELIVERY_PRICE'], $arResult['CURRENCY']) ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <b><?= GetMessage('BASKET_COMMON_TOTAL') ?>:</b>
                                </td>
                                <td colspan="7" align="right">
                                    <?= CurrencyFormat($arResult['TOTAL_PRICE'], $arResult['CURRENCY']) ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</form>