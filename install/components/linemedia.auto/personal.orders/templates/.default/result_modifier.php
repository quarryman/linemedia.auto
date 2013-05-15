<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arResult['PAYSYSTEMS'] = array();

if (CModule::IncludeModule('sale')) {
     $db = CSalePaySystem::GetList();
     while ($item = $db->Fetch()) {
         $arResult['PAYSYSTEMS'][$item['ID']] = $item;
     }
}

$arResult['TOTAL_COUNT'] = 0;
$arResult['TOTAL_PRICE'] = 0;

foreach ($arResult['GROUPS'] as $group) {
    $basket = $arResult['BASKETS'][reset($group)];
    $arResult['TOTAL_PRICE'] += $basket['ORDER']['PRICE'];
    foreach ($group as $basketID) {
        $basket = $arResult['BASKETS'][$basketID];
        $arResult['TOTAL_COUNT'] += $basket['QUANTITY'];
    } 
}
