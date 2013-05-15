<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("sale")) {
    ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
    return;
}

if(!CModule::IncludeModule("currency")) {
    ShowError(GetMessage("CURRENCY_MODULE_NOT_INSTALL"));
    return;
}

global $USER, $APPLICATION;

if (!$USER->GetID()) {
    ShowError(GetMessage('LM_AUTO_TRANSACTIONS_ERROR_AUTH'));
    return;
};

/**
 * Обработка входных параметров.
 */

$arParams['TITLE'] = $arParams['TITLE'] ?: GetMessage('LM_AUTO_TRANSACTIONS_SET_TITLE');
$arParams['ADD_SECTION_CHAIN'] = $arParams['ADD_SECTION_CHAIN'] == 'Y' ? 'Y' : "N";
$arParams['SET_TITLE_TRANSACTIONS'] = $arParams['SET_TITLE_TRANSACTIONS'] == 'Y' ? 'Y' : "N";
$arParams['INIT_JQUERY'] = $arParams['INIT_JQUERY'] == 'Y' ? 'Y' : "N";
$arParams['ORDERS_PATH'] = $arParams['ORDERS_PATH'] ?: '/auto/orders/';


/*
* Подключим на страницу jquery
*/
if ($arParams['INIT_JQUERY'] == 'Y') {
    CJSCore::Init(array('jquery'));
}


/**
 * Подключаем 'window' для выбора даты при сортировке
 */
CJSCore::Init(array('window'));

$arResult['transactions'] = array();

$user_id = $USER->GetID();
$current_date = $DB->FormatDate(date("d.m.Y"), 'DD.MM.YYYY', CSite::GetDateFormat("SHORT"));

/**
 *
 * Получаем транзакции текущего клиента
 */

$arFilter = array("USER_ID" => $user_id);

$arFilter['>=TRANSACT_DATE'] = !empty($_REQUEST['date_from']) > 0 ? trim(strip_tags($_REQUEST['date_from'])) : '01.01.1970';
$arFilter['<=TRANSACT_DATE'] = !empty($_REQUEST['date_to']) ? trim(strip_tags($_REQUEST['date_to'])) : $current_date;

if (isset($_REQUEST['trans_id']) && (int)$_REQUEST['trans_id'] > 0) {
    $arFilter['ID'] = (int)trim(strip_tags($_REQUEST['trans_id']));
}

if (isset($_REQUEST['order_id']) && (int)$_REQUEST['order_id'] > 0) {
    $arFilter['ORDER_ID'] = (int)trim(strip_tags($_REQUEST['order_id']));
}

$site_base_currency = CCurrency::GetBaseCurrency();
$arResult['filte']=$arFilter;
$res_transactions = CSaleUserTransact::GetList(Array("ID" => "DESC"), $arFilter, false, false, array());
while ($transaction = $res_transactions->Fetch()) {
    $transaction['AMOUNT'] = CCurrencyRates::ConvertCurrency($transaction['AMOUNT'] , $transaction["CURRENCY"], $site_base_currency);
    $arResult['transactions'][$transaction['ID']] = $transaction;
}

/**
 * Текущий счет клиента
 */
$res_user_account = CSaleUserAccount::GetByUserID($user_id, $site_base_currency);
$arResult['cash'] = $res_user_account["CURRENT_BUDGET"] > 0
    ? SaleFormatCurrency($res_user_account["CURRENT_BUDGET"], $res_user_account["CURRENCY"])
    : SaleFormatCurrency(0, $site_base_currency);

/**
 * Получаем сумму "долга по заказам", в которые входят не оплаченные + не омтененные + нефинальные заказы +
 * учет возможной частичной оплаты.
 */
$orders =array();
$res_orders = CSaleOrder::GetList(
    array('ID' => 'DESC'),
    array('PAYED' => 'N', 'CANCELED' => 'N', '!STATUS_ID' => 'F', 'USER_ID' => $user_id),
    false,
    false,
    array('PRICE', 'CURRENCY', 'SUM_PAID', 'USER_ID')
);
while ($order = $res_orders -> Fetch()) {
    $arResult['sum_to_pay']+= CCurrencyRates::ConvertCurrency($order['PRICE'] - $order['SUM_PAID'] , $order['CURRENCY'], $site_base_currency);
    $orders[] = $order;
}
$arResult['orders'] =$orders;
$arResult['sum_to_pay_currency'] = $arResult['sum_to_pay'] > 0
    ? SaleFormatCurrency((int)$arResult['sum_to_pay'], $site_base_currency)
    : SaleFormatCurrency(0, $site_base_currency);


/*
 *  Хлебные крошки  + заголовок
 */
if ($arParams['SET_TITLE_TRANSACTIONS'] == 'Y') {
    $APPLICATION->SetTitle($arParams['TITLE']);
}

if ($arParams['ADD_SECTION_CHAIN'] == 'Y') {
    $APPLICATION->AddChainItem(GetMessage("LM_AUTO_TRANSACTIONS_SET_TITILE"), $APPLICATION->GetCurPage());
}


$arResult['date_from'] = isset($_REQUEST['date_from']) ? strip_tags(trim($_REQUEST['date_from'])) : '';
$arResult['date_to']    = isset($_REQUEST['date_to']) ? strip_tags(trim($_REQUEST['date_to'])) : $current_date;
$arResult['trans_id'] = isset($_REQUEST['trans_id']) && (int)$_REQUEST['trans_id'] > 0 ? strip_tags(trim($_REQUEST['trans_id'])) : '';
$arResult['order_id'] = isset($_REQUEST['order_id']) && (int)$_REQUEST['order_id'] > 0 ? strip_tags(trim($_REQUEST['order_id'])) : '';
$arResult['site_base_currency'] = $site_base_currency;

$this->IncludeComponentTemplate();
