<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$GLOBALS['templateFolder'] = $templateFolder;

?>

<script type="text/javascript">
    function SetValue(param)
    {
        param['price'] = $('#part_price_' + param['part_id']).val();
        
        if (window.opener.MakeOrder(param)) {
            window.close();
        }
    }
</script>

<?

/*
 * Распечатаем группы одну за другой
 */
if (!empty($arResult['PARTS'])) {
    foreach ($arResult['PARTS'] as $group_name => $parts) {
        $group_id = end(explode('_', $group_name));
        
        echo '<h2>' . GetMessage('LM_AUTO_SEARCH_GROUP_' . $group_id) . '</h2>';
        printPartsTable($parts, $arResult['GROUPS']);
    }
} else {
    echo GetMessage('NOT_FOUND');
}


function printPartsTable($parts, $groups) {
?>

<table width="100%" cellspacing="1" cellpadding="3" border="1" class="lm-auto-search-parts">
    <thead>
        <tr>
            <th><?= GetMessage('LM_AUTO_SEARCH_ITEM_TITLE') ?></th>
            <th><?= GetMessage('LM_AUTO_SEARCH_ITEM_ARTICLE') ?></th>
            <th><?= GetMessage('LM_AUTO_SEARCH_ITEM_BRAND') ?></th>
            <th><?= GetMessage('LM_AUTO_SEARCH_ITEM_QUANTITY') ?></th>
            <th><?= GetMessage('LM_AUTO_SEARCH_ITEM_WEIGHT') ?></th>
            <th><?= GetMessage('LM_AUTO_SEARCH_ITEM_SUPPLIER') ?></th>
            <th><?= GetMessage('LM_AUTO_SEARCH_ITEM_MODIFIED') ?></th>
            <th><?= GetMessage('LM_AUTO_SEARCH_ITEM_PRICE') ?></th>
            <th><?= GetMessage('LM_AUTO_SEARCH_ITEM_CHOOSE') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($parts as $part) { ?>
        <tr class="hproduct">
            <td class="fn"><?= $part['title'] ?></td>
            <td class="sku"><?= $part['article'] ?></td>
            <td><?= $part['brand_title'] ?></td>
            <td class="instock"><?= $part['quantity'] ?></td>
            <td class="weight"><?= $part['weight'] ?></td>
            <td class="brand"><?= $part['supplier']['NAME'] ?></td>
            <? if (intval($part['modified']) > 0) { ?>
                <td><time datetime="<?= date('c', strtotime($part['modified'])) ?>"><?= $part['modified'] ?></time></td>
            <? } else { ?>
                <td>-</td>
            <? } ?>
            <td class="price">
                <select name="part_price" id="part_price_<?= $part['id'] ?>">
                    <? foreach ($part['prices'] as $group_id => $price) { ?>
                        <option value="<?= $price ?>">
                            [<?= $groups[$group_id]['NAME'] ?>]:
                            <?= CurrencyFormat($price, CCurrency::GetBaseCurrency()) ?>
                        </option>
                    <? } ?>
                </select>
            </td>
            <td align="center">
                <a href="javascript: void(0);" onclick="SetValue({'action': 'add',extra:<?=str_replace('"',"'", json_encode($part['extra']))?>, 'part_id': '<?= $part['id'] ?>', 'supplier_id': '<?= $part['supplier_id'] ?>', 'article': '<?= $part['article'] ?>', 'price': '<?= $part['price'] ?>', 'brand_id': '<?= $part['brand_id'] ?>'}); return false;">
                    <img src="/bitrix/themes/.default/images/signplus.gif" width="11" height="11" alt="" border="0" />
                </a>
            </td>
        </tr>
    <? } ?>
    </tbody>
</table>

<? } ?>
