<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult["ORDER_BY_STATUS"] = array();

CModule::IncludeModule('linemedia.auto');

$arResult['STATUSES'] = LinemediaAutoOrder::getStatusesList();

$arResult['BASKETS'] = array();

foreach ($arResult['ORDERS'] as $val) {
    $arOrder = $val['ORDER'];
    
    $order = new LinemediaAutoOrder($arOrder['ID']);
    $props = $order->getProps();
    foreach ($val['BASKET_ITEMS'] as $basket) {
        
        $basket['PROPS'] = LinemediaAutoBasket::getProps($basket['ID']);
        $basket['ORDER'] = $arOrder;
        $basket['ORDER']['PROPS'] = $props;
        
        $arResult['BASKETS'][$basket['ID']] = $basket;
    }
    
	$arResult['ORDER_BY_STATUS'][$val['ORDER']['STATUS_ID']][] = $val;
}

?>