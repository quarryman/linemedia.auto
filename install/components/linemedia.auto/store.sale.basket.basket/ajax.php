<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

if (!CModule::IncludeModule('sale')) {
    ShowError(GetMessage('SALE_MODULE_ERROR'));
    return;
}

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError(GetMessage('LM_AUTO_MAIN_MODULE_ERROR'));
    return;
}

if (!check_bitrix_sessid()) {
    ShowError(GetMessage('LM_AUTO_ERROR_SESSID'));
    return;
}


$arResult = array();

if (!empty($_REQUEST) && isset($_REQUEST['BASKET_ID'])) {
    $basket_id  = (int) $_REQUEST['BASKET_ID'];
    $quantity   = (int) $_REQUEST['QUANTITY'];
    
    /*
     * Обновление корзины
     */
    if ($basket_id > 0 && $quantity > 0) {
        $basket = new CSaleBasket();
        $basket->Update($basket_id, array('QUANTITY' => $quantity));
    }
    
    /*
     * Установим параметры.
     */
    $arParams = (array) $_REQUEST['PARAMS'];
    $arParams['AJAX_MODE'] = 'Y';
    
    /*
     * Очистим весь вывод.
     */
    ob_end_clean();
    
    /*
     * Рассчитаем общую стоимость.
     */
    $result = $APPLICATION->IncludeComponent(
        'linemedia.auto:store.sale.basket.basket',
        '',
        $arParams,
        false
    );
    
    $arResult['TOTAL_PRICE'] = $result['allNOVATSum_FORMATED'];
}

echo json_encode($arResult);
exit();
