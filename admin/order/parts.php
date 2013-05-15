<?php

$sTableID = "b_lm_search_parts"; // ID таблицы

$oSort = new CAdminSorting($sTableID, "title", "asc"); // объект сортировки

$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка


// Поставщики
$suppliers_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
$suppliers = array();
$arsuppliers = LinemediaAutoSupplier::GetList();
foreach ($arsuppliers as $supplier) {
    $suppliers[$supplier['PROPS']['supplier_id']['VALUE']] = $supplier;
}


$lAdmin->AddHeaders(array(
    array(
        "id"        => "id",
        "content"   => "ID",
        "default"   => false,
    ),
    array(
        "id"        => "title",
        "content"   => GetMessage("LM_AUTO_MAIN_TITLE"),
        "default"   => true,
    ),
    array(
        "id"        => "article",
        "content"   => GetMessage("LM_AUTO_MAIN_ARTICLE"),
        "default"   => true,
    ),
    array(
        "id"        => "original_article",
        "content"   => GetMessage("LM_AUTO_MAIN_ORIGINAL_ARTICLE"),
        "default"   => false,
    ),
    array(
        "id"        => "brand_title",
        "content"   => GetMessage("LM_AUTO_MAIN_BRAND_TITLE"),
        "default"   => true,
    ),
    array(
        "id"        => "prices",
        "content"   => GetMessage("LM_AUTO_MAIN_PRICE"),
        "default"   => true,
    ),
    array(
        "id"        => "supplier_id",
        "content"   => GetMessage("LM_AUTO_MAIN_SUPPLIER_ID"),
        "default"   => true,
    ),
    array(
        "id"        => "quantity",
        "content"   => GetMessage("LM_AUTO_MAIN_QUANTITY"),
        "default"   => true,
    ),
    array(
        "id"        => "weight",
        "content"   => GetMessage("LM_AUTO_MAIN_WEIGHT"),
        "default"   => false,
    ),
    array(
        "id"        => "modified",
        "content"   => GetMessage("LM_AUTO_MAIN_MODIFIED"),
        "default"   => false,
    ),
));



foreach ($arResult['PARTS'] as $arCatalogs) {
    foreach ($arCatalogs as $arRes) {
        // Создаем строку. Результат - экземпляр класса CAdminListRow.
        $row =& $lAdmin->AddRow($arRes['id'], $arRes);
        
        $supplier = $suppliers[$arRes['supplier_id']];
        $row->AddViewField('supplier_id', "[<a href='/bitrix/admin/iblock_element_edit.php?ID=" . $supplier['ID'] . "&type=linemedia_auto&lang=ru&IBLOCK_ID=" . $suppliers_iblock_id . "&find_section_section=0'>".$arRes['supplier_id']."</a>] " . $supplier['NAME']);
        
        $arRes['hash'] = md5($arRes['supplier_id'].$arRes['brand_title'].$arRes['article'].$arRes['price']);
        
        ob_start();
        ?>
            <select name="PRICE[<?= $arRes['hash'] ?>]" id="price-<?= $arRes['hash'] ?>" style="width: 150px;">
                <? foreach ($arRes['prices'] as $group_id => $price) { ?>
                    <option value="<?= round($price, 2) ?>" rel="<?= $groups[$group_id]['NAME'] ?>">
                        <?= $groups[$group_id]['NAME'] ?>:
                        <?= CurrencyFormat($price, CCurrency::GetBaseCurrency()) ?>
                    </option>
                <? } ?>
            </select>
        <?
        $content = ob_get_contents();
        ob_clean();
        
        $row->AddViewField('prices', $content);
        
        // Сформируем контекстное меню.
        $arActions = array();
        
        // Выбор элемента.
        $arActions []= array(
            "ICON"      => "select",
            "DEFAULT"   => true,
            "TEXT"      => GetMessage("NEWO_SELECT"),
            "ACTION"    => "SelEl('".$arRes['id']."', ".json_encode($arRes).");"
        );
        
        // Применим контекстное меню к строке.
        $row->AddActions($arActions);
    }
}


/*
 * Отображение списка.
 */
$lAdmin->DisplayList();

