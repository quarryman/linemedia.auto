<?php

$sTableID = "b_lm_search_catalogs"; // ID таблицы

$oSort = new CAdminSorting($sTableID, "title", "asc"); // объект сортировки

$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

$lAdmin->AddHeaders(array(
    array(
        "id"        => "id",
        "content"   => "ID",
        "default"   => false,
    ),
    array(
        "id"        => "brand_title",
        "content"   => GetMessage("LM_AUTO_MAIN_BRAND_TITLE"),
        "default"   => true,
    ),
    array(
        "id"        => "title",
        "content"   => GetMessage("LM_AUTO_MAIN_TITLE"),
        "default"   => true,
    ),
));

foreach ($arResult['CATALOGS'] as $arRes) {
    // Создаем строку. Результат - экземпляр класса CAdminListRow.
    $row =& $lAdmin->AddRow($arRes['id'], $arRes);
    
    $url = parse_url($arRes['find']);
    
    $row->AddViewField('brand_title', "<a href='".$APPLICATION->GetCurPage()."?".$url['query']."&lang=".LANG."&LID=".$LID."&from=catalogs'>".$arRes['brand_title']."</a>");
}


/*
 * Отображение списка.
 */
$lAdmin->DisplayList();