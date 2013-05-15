<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");

if ($saleModulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

IncludeModuleLangFile(__FILE__);


global $USER;

if (empty($USER)) {
    $USER = new CUser();
}


$APPLICATION->SetTitle(GetMessage('LM_AUTO_ORDERS_LIST_TITLE'));


$arAccessibleSites = array();
$dbAccessibleSites = CSaleGroupAccessToSite::GetList(
        array(),
        array('GROUP_ID' => $GLOBALS['USER']->GetUserGroupArray()),
        false,
        false,
        array('SITE_ID')
    );

while ($arAccessibleSite = $dbAccessibleSites->Fetch()) {
    if (!in_array($arAccessibleSite['SITE_ID'], $arAccessibleSites)) {
        $arAccessibleSites []= $arAccessibleSite['SITE_ID'];
    }
}

$sTableID = "tbl_sale_orders_list";


$oSort = new CAdminSorting($sTableID, 'ID', 'DESC', 'sOrBy', 'sOrOrder');
$lAdmin = new CAdminList($sTableID, $oSort);


$arFilterFields = array(
    "filter_ids",
    "filter_id_from",
    "filter_id_to",
    "filter_date_from",
    "filter_date_to",
    "filter_date_update_from",
    "filter_date_update_to",
    "filter_currency",
    "filter_status",
    "filter_payed",
    "filter_pay_system",
    "filter_canceled",
    "filter_supplier",
    "filter_article",
    "filter_brand",
    "filter_person_type",
    "filter_user_id",
    "filter_user_login",
    "filter_user_email",
);



/*
 * Получаем свойства заказа.
 */
$arOrderProps = array();
$arOrderPropsCode = array();
$dbProps = CSaleOrderProps::GetList(
    array('PERSON_TYPE_ID' => 'ASC', 'SORT' => 'ASC'),
    array(),
    false,
    false,
    array('ID', 'NAME', 'PERSON_TYPE_NAME', 'PERSON_TYPE_ID', 'SORT', 'IS_FILTERED', 'TYPE', 'CODE')
);

while ($arProps = $dbProps->GetNext()) {
    if (strlen($arProps['CODE']) > 0) {
        if (empty($arOrderPropsCode[$arProps["CODE"]])) {
            $arOrderPropsCode[$arProps["CODE"]] = $arProps;
        }
    } else {
        $arOrderProps[IntVal($arProps["ID"])] = $arProps;
    }
}//while

foreach ($arOrderProps as $key => $value){
    if ($value["IS_FILTERED"] == "Y" && $value["TYPE"] != "MULTISELECT") {
        $arFilterFields[] = "filter_prop_".$key;
    }
}

foreach ($arOrderPropsCode as $key => $value){
    if ($value["IS_FILTERED"] == "Y" && $value["TYPE"] != "MULTISELECT") {
        $arFilterFields[] = "filter_prop_".$key;
    }
}

$lAdmin->InitFilter($arFilterFields);



/**
 * Поставщики.
 */
$arListSuppliers = LinemediaAutoSupplier::getList();
$arSuppliers = array();
foreach ($arListSuppliers as $arSupplier) {
    $arSuppliers[ $arSupplier['PROPS']['supplier_id']['VALUE'] ] = $arSupplier;
}



/*
 * Фильтрация.
 */
$lmfilter = new LinemediaAutoBasketFilter();

// Фильр по ID корзины (список).
if (intval($filter_ids) > 0) {
    $lmfilter->setIds(array_filter(array_map('intval', explode(',', strval($filter_ids)))));
}

// Фильр по ID корзины (от).
if (intval($filter_id_from) > 0) {
    $lmfilter->setIdFrom($filter_id_from);
}

// Фильр по ID корзины (до).
if (intval($filter_id_to) > 0) {
    $lmfilter->setIdTo($filter_id_to);
}

// Фильр по дате добавления (от).
if (strlen($filter_date_from) > 0) {
    $lmfilter->setDateFrom($filter_date_from);
}

// Фильр по дате добавления (до).
if (strlen($filter_date_to) > 0) {
    $lmfilter->setDateTo($filter_date_to);
}

// Фильр по дате обновления (от).
if (strlen($filter_date_update_from) > 0) {
    $lmfilter->setDateUpdateFrom($filter_date_update_from);
}

// Фильр по дате обновления (до).
if (strlen($filter_date_update_to) > 0) {
    $lmfilter->setDateUpdateTo($filter_date_update_to);
}

// Фильтр по оплате.
if (!empty($filter_payed)) {
    $lmfilter->setPayed($filter_payed);
}

// Фильтр по отмене.
if (!empty($filter_canceled)) {
    $lmfilter->setCanceled($filter_canceled);
}

// Фильтр по статусам.
if (isset($filter_status) && is_array($filter_status) && !empty($filter_status)) {
    $lmfilter->setStatus($filter_status);
}

// Фильтр по типу плательзика.
if (!empty($filter_person_type)) {
    $lmfilter->setPersonType($filter_person_type);
}

// Фильтр по платежным системам.
if (isset($filter_pay_system) && is_array($filter_pay_system) && !empty($filter_pay_system)) {
    $lmfilter->setPaySystem($filter_pay_system);
}

// Фильтр по доставкам.
if (isset($filter_delivery) && is_array($filter_delivery) && !empty($filter_delivery)) {
    $lmfilter->setDelivery($filter_delivery);
}

// Фильтр по поставщикам.
if (isset($filter_supplier) && !empty($filter_supplier)) {

    //Учет множественного поиска по поставщикам
    $suppliers_ids =array_filter(array_map('intval', (array)$filter_supplier));
    $set_suppliers = array();
    foreach ($suppliers_ids as $key => $id) {
        $set_suppliers[] = $arListSuppliers[$id]['PROPS']['supplier_id']['VALUE'];
    }
    $lmfilter->setSupplier($set_suppliers);

}

// Фильтр по артикулу.
if (strlen($filter_article) > 0) {
    $lmfilter->setArticle($filter_article);
}

// Фильтр по бренду.
if (strlen($filter_brand) > 0) {
    $lmfilter->setBrandTitle($filter_brand);
}

// Фильтр по ID пользователя.
if (intval($filter_user_id) > 0) {
    $lmfilter->setUserId($filter_user_id);
}

// Фильтр по логину пользователя.
if (strlen($filter_user_login) > 0) {
    $lmfilter->setUserLogin($filter_user_login);
}

// Фильтр по e-mail пользователя.
if (strlen($filter_user_email) > 0) {
    $lmfilter->setUserEmail($filter_user_email);
}


/*
 * Создание событий для модуля
 */
$events = GetModuleEvents("linemedia.auto", "OnAdminOrderListFilter");
$arFilterModule = array();
while ($arEvent = $events->Fetch()) {
    $arFilterModule = array_merge($arFilterModule, ExecuteModuleEventEx($arEvent, array($USER->GetID(), &$lmfilter)));
}


// Дополнительные фильтры модулей.
if (!empty($arFilterModule)) {
    $lmfilter->setAdditionalFilter($arFilterModule);
}

$aBasketItemsSFilter = $lmfilter->filter();

if ($lmfilter->isFiltered()) {
    if (!empty($aBasketItemsSFilter)) {
        if (isset($arFilter['ID'])) {
            if (is_array($arFilter['ID'])) {
                $arFilter['ID'] = array_values(array_intersect($arFilter['ID'], $aBasketItemsSFilter));
                if (count($arFilter['ID']) == 0) {
                    $arFilter['ID'] = false;
                }
            }
        } else {
            $arFilter['ID'] = $aBasketItemsSFilter;
        }
    } else {
        $arFilter['ID'] = false;
    }
}



if ($saleModulePermissions == "W") {
    $arFilterTmp = $arFilter;
} else {
    $arFilterTmp = array_merge(
        $arFilter,
        array(
            "STATUS_PERMS_GROUP_ID" => $GLOBALS["USER"]->GetUserGroupArray(),
            ">=STATUS_PERMS_PERM_VIEW" => "Y"
        )
    );
}



// Список платежных систем.
$paysystems = LinemediaAutoOrder::getPaysystemsList();

// Список доставок.
$deliveries = LinemediaAutoOrder::getDeliveryList();

// Список статусов.
$statuses = LinemediaAutoOrder::getStatusesList();

// Список типов плательщиков.
$persons = LinemediaAutoOrder::getPersonTypesList();




/*
 * Групповые операции.
 */
$arID = array();
if ($saleModulePermissions >= 'U') {
    
    // Выполнить для всех записей.
    if ($_REQUEST['action_target'] == 'selected') {
        $dbbaskets = CSaleBasket::GetList(array(), array('!ORDER_ID' => false), false, false, array('ID'));
        while ($basket = $dbbaskets->Fetch()) {
            $arID []= $basket['ID'];
        }
    } else {
        $arID = $lAdmin->GroupAction();
    }
    
    // Типы операций.
    switch ($_REQUEST['action']) {
        case 'pay':
            $obasket = new LinemediaAutoBasket();
            foreach ($arID as $ID) {
                $obasket->payItem($ID, 'Y');
                if ($ex = $APPLICATION->GetException()) {
                    $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_PAY').': '.$ex->GetString(), $ID);
                }
            }
            break;
        
        case 'pay_no':
            $obasket = new LinemediaAutoBasket();
            foreach ($arID as $ID) {
                $obasket->payItem($ID, 'N');
                if ($ex = $APPLICATION->GetException()) {
                    $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_PAY').': '.$ex->GetString(), $ID);
                }
            }
            break;
        
        case 'cancel':
            $obasket = new LinemediaAutoBasket();
            foreach ($arID as $ID) {
                $obasket->cancelItem($ID, 'Y');
                if ($ex = $APPLICATION->GetException()) {
                    $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_CANCEL').': '.$ex->GetString(), $ID);
                }
            }
            break;
            
        case 'cancel_no':
            $obasket = new LinemediaAutoBasket();
            foreach ($arID as $ID) {
                $obasket->cancelItem($ID, 'N');
                if ($ex = $APPLICATION->GetException()) {
                    $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_CANCEL_NO').': '.$ex->GetString(), $ID);
                }
            }
            break;
        
        case 'delivery':
            $obasket = new LinemediaAutoBasket();
            foreach ($arID as $ID) {
                $obasket->deliveryItem($ID, 'Y');
                if ($ex = $APPLICATION->GetException()) {
                    $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_DELIVERY').': '.$ex->GetString(), $ID);
                }
            }
            break;
        
        case 'delivery_no':
            $obasket = new LinemediaAutoBasket();
            foreach ($arID as $ID) {
                $obasket->deliveryItem($ID, 'N');
                if ($ex = $APPLICATION->GetException()) {
                    $lAdmin->AddGroupError(GetMessage('GROUP_ERROR_DELIVERY_NO').': '.$ex->GetString(), $ID);
                }
            }
            break;
        
        case 'delete':
            @set_time_limit(0);
            if (CSaleOrder::CanUserDeleteOrder($ID, $GLOBALS['USER']->GetUserGroupArray(), $GLOBALS['USER']->GetID())) {
                $DB->StartTransaction();
                if (!CSaleOrder::Delete($ID)) {
                    $DB->Rollback();
                    if ($ex = $APPLICATION->GetException()) {
                        $lAdmin->AddGroupError($ex->GetString(), $ID);
                    } else {
                        $lAdmin->AddGroupError(GetMessage('SALE_DELETE_ERROR'), $ID);
                    }
                } else {
                    $DB->Commit();
                }
            } else {
                $lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SO_NO_PERMS2DEL")), $ID);
            }
            break;
    }
    
    // Смена статусов.
    if (strpos(strval($_REQUEST['action']), 'status') !== false) {
        $status_error = false;
        $status = substr((string) $_REQUEST['action'], strlen('status_'), 1);
        $obasket = new LinemediaAutoBasket();
        
        // Установим, чтобы показать обработчикам событий, что не надо отслать письма на каждое изменение.
        $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET'] = true;
        
        foreach ($arID as $ID) {
            $obasket->statusItem($ID, $status);
            if ($ex = $APPLICATION->GetException()) {
                $lAdmin->AddGroupError(GetMessage('GROUP_ACTION_SET_STATUS').': '.$ex->GetString(), $ID);
                $status_error = true;
            }
        }
        
        if (!$status_error) {
            /*
             * Событие на отправку статусов.
             */
            $events = GetModuleEvents("linemedia.auto", "OnAfterBasketStatusesChange");
            while ($arEvent = $events->Fetch()) {
                ExecuteModuleEventEx($arEvent, array(&$arID, &$status));
            }
        }
        
        unset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET']);
    }
    
}



/*
 * Выводимые поля заказа.
 */
$arHeaders = array(
    array('id' => 'ORDER_ID',       'content' => GetMessage('ID'),          'sort' => 'ORDER_ID',       'default' => true),
    array('id' => 'PAYED',          'content' => GetMessage('PAYED'),       'sort' => '',               'default' => true),
    array('id' => 'CANCELED',       'content' => GetMessage('CANCELED'),    'sort' => '',               'default' => true),
    array('id' => 'PERSON_TYPE',    'content' => GetMessage('PERSON_TYPE'), 'sort' => '',               'default' => true),
    array('id' => 'QUANTITY',       'content' => GetMessage('QUANTITY'),    'sort' => 'QUANTITY',       'default' => true),
    array('id' => 'PRICE',          'content' => GetMessage('PRICE'),       'sort' => 'PRICE',          'default' => true),
    array('id' => 'AMOUNT',         'content' => GetMessage('AMOUNT'),       'sort' => '',              'default' => true),
    array('id' => 'USER',           'content' => GetMessage('USER'),        'sort' => 'USER_ID',        'default' => true),
    array('id' => 'STATUS',         'content' => GetMessage('STATUS'),      'sort' => '',               'default' => true),
    array('id' => 'ARTICLE',        'content' => GetMessage('ARTICLE'),     'sort' => '',               'default' => true),
    array('id' => 'BRAND',          'content' => GetMessage('BRAND'),       'sort' => '',               'default' => true),
    array('id' => 'NAME',           'content' => GetMessage('NAME'),        'sort' => 'NAME',           'default' => true),
    array('id' => 'SUPPLIER',       'content' => GetMessage('SUPPLIER'),    'sort' => '',               'default' => true),
    array('id' => 'DELIVERY',       'content' => GetMessage('DELIVERY'),    'sort' => '',               'default' => false),
    array('id' => 'PAYSYSTEM',      'content' => GetMessage('PAYSYSTEM'),   'sort' => '',               'default' => false),
    array('id' => 'BASEPRICE',      'content' => GetMessage('BASEPRICE'),   'sort' => '',               'default' => false),
    array('id' => 'BASEPRICE_AMOUNT','content' => GetMessage('BASEPRICE_AMOUNT'),   'sort' => '',       'default' => false),
    array('id' => 'DELIVERY_TIME',  'content' => GetMessage('DELIVERY_TIME'),'sort' => '',              'default' => false),
    array('id' => 'COMMENTS',       'content' => GetMessage('COMMENTS'),     'sort' => '',              'default' => false),
    array('id' => 'USER_DESCRIPTION',  'content' => GetMessage('USER_DESCRIPTION'),'sort' => '',        'default' => false),
);


/*
 * Создание событий для модуля
 */
$events = GetModuleEvents("linemedia.auto", "OnBeforeAdminShowOrdersList");
while ($arEvent = $events->Fetch()) {
    try {
        ExecuteModuleEventEx($arEvent, array(&$arHeaders));
    } catch (Exception $e) {
        throw $e;
    }
}


$lAdmin->AddHeaders($arHeaders);


if (!isset($arFilterTmp['ORDER_ID']) || $arFilterTmp['ORDER_ID'] === false) {
    $arFilterTmp['!ORDER_ID'] = 'NULL';
}

$arGroupByTmp = false;
$arSelectFields = array();



// Выборка данных заказов.
$dbBasketList = CSaleBasket::GetList(
    array($sOrBy => $sOrOrder),
    $arFilterTmp,
    $arGroupByTmp,
    array('nPageSize' => CAdminResult::GetNavSize($sTableID)),
    $arSelectFields
);


// Инициализация списка - выборка данных.
$dbBasketList = new CAdminResult($dbBasketList, $sTableID);
$dbBasketList->NavStart();


// Установка строки навигации.
$lAdmin->NavText($dbBasketList->GetNavPrint(GetMessage('ORDERS_LIST')));

// Добавление контекстного меню.
$lAdmin->AddAdminContextMenu(array());




/*
 * Вывод списка заказов автопортала (битриксовых корзин).
 */
while ($arBasketItem = $dbBasketList->GetNext()) {
    
    // Формирование строки для вывода.
    $row =& $lAdmin->AddRow($arBasketItem['ID'], $arBasketItem);
    
    // Заказ (битриксовый).
    $arOrder = CSaleOrder::GetByID($arBasketItem['ORDER_ID']);
    
    // Свойства корзины.
    $arBasketProps = LinemediaAutoBasket::getProps($arBasketItem['ID']);
    
    /*
     * Создание событий для модуля
     */
    $events = GetModuleEvents("linemedia.auto", "OnBeforeAdminShowBasketRow");
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array(&$row, &$arBasketItem, &$arBasketProps, &$arOrder));
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    // ID заказа.
    $row->AddViewField('ORDER_ID', "<b><a href='/bitrix/admin/linemedia.auto_sale_order_detail.php?ID=".$arBasketItem['ORDER_ID'].GetFilterParams("filter_")."&lang=".LANGUAGE_ID."' title='".GetMessage("SALE_DETAIL_DESCR")."'>".GetMessage("SO_ORDER_ID_PREF").$arBasketItem['ORDER_ID']."</a></b><br />".GetMessage('SO_FROM').' '.$arOrder['DATE_INSERT']);
    
    // Количество.
    $row->AddViewField('PAYED', ($arBasketProps['payed']['VALUE'] == 'Y') ? (GetMessage('SALE_YES')) : (GetMessage('SALE_NO')));
    
    // Количество.
    $row->AddViewField('CANCELED', ($arBasketProps['canceled']['VALUE'] == 'Y') ? (GetMessage('SALE_YES')) : (GetMessage('SALE_NO')));
    
    // Количество.
    $row->AddField('PERSON_TYPE', $persons[$arOrder['PERSON_TYPE_ID']]['NAME']);
    
    // Количество.
    $row->AddField('QUANTITY', $arBasketItem['QUANTITY']);
    
    // Цена.
    $row->AddField('PRICE', CurrencyFormat($arBasketItem['PRICE'], $arBasketItem['CURRENCY']));
    
    // Сумма.
    $row->AddField('AMOUNT', CurrencyFormat($arBasketItem['PRICE'] * $arBasketItem['QUANTITY'], $arBasketItem['CURRENCY']));
        
    // Пользователь.
    $arUser = CUser::getById($arOrder['USER_ID'])->Fetch();
    $row->AddField('USER', '[<a href="/bitrix/admin/user_edit.php?lang=ru&ID='.$arUser['ID'].'">'.$arUser['ID'].'</a>] '.$arUser['NAME'].' '.$arUser['LAST_NAME'].' ('.$arUser['EMAIL'].')');
    
    // Статус заказа.
    $color = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_STATUS_COLOR_' . $arBasketProps['status']['VALUE'], '#ffffff');
    $row->AddViewField('STATUS', '<span style="font-size: 16px; color: '.$color.';">&bull;</span> '.$statuses[$arBasketProps['status']['VALUE']]['NAME']);
    
    // Артикул.
    $row->AddField('ARTICLE', $arBasketProps['article']['VALUE']);
     
    // Бренд.
    $row->AddField('BRAND', $arBasketProps['brand_title']['VALUE']);
    
    // Закупочная цена.
    $row->AddField('BASEPRICE', CurrencyFormat($arBasketProps['base_price']['VALUE'], $arBasketItem['CURRENCY']));
    
    // Сумма закупки.
    $row->AddField('BASEPRICE_AMOUNT', CurrencyFormat(($arBasketProps['base_price']['VALUE']*$arBasketItem['QUANTITY']), $arBasketItem['CURRENCY']));
     
    // Название товара.
    $row->AddField('NAME',  $arBasketItem['NAME']);
    
    // Поставщик.
    $arSupplier = $arSuppliers[$arBasketProps['supplier_id']['VALUE']];
    $row->AddViewField('SUPPLIER', '[<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?type=linemedia_auto&IBLOCK_ID='.$iblock_supplier_id.'&ID='.$arSupplier['ID'].'">'.$arSupplier['ID'].'</a>] '.$arBasketProps['supplier_title']['VALUE']);
    
    // Доставка.
    $row->AddField('DELIVERY', $deliveries[$arOrder['DELIVERY_ID']]['NAME']);
    
    // Платежная система.
    $row->AddField('PAYSYSTEM', $paysystems[$arOrder['PAY_SYSTEM_ID']]['NAME']);
    
    // Срок доставки.
    $delivery_time = (int) $arBasketProps['delivery_time']['VALUE'];
    if ($delivery_time > 0) {
        if ($delivery_time >= 24) {
            $days = round($delivery_time / 24);
            $delivery_time = '&asymp; ' . $days . ' ' . GetMessage('LM_AUTO_MAIN_DAYS');
        } else {
            $delivery_time .= ' ' . GetMessage('LM_AUTO_MAIN_HOURS');
        }
    } else {
        $delivery_time = '';
    }
    $row->AddField('DELIVERY_TIME', $delivery_time);
    
    
    // Комментарий.
    $row->AddField('COMMENTS',  $arOrder['COMMENTS']);
    
    // Комментарий покупателя к заказу.
    $row->AddField('USER_DESCRIPTION',  $arOrder['USER_DESCRIPTION']);
    
    /*
     * Добавление лействий.
     */
    $arActions = array();
    
    // Редактирование элемента.
    $arActions []= array(
        'ICON' => 'view',
        'DEFAULT' => true,
        'TEXT' => GetMessage('ACTION_DETAIL'),
        'ACTION' => $lAdmin->ActionRedirect("linemedia.auto_sale_order_detail.php?ID=".$arOrder['ID']."&lang=".LANGUAGE_ID.GetFilterParams("filter_"))
    );
    
    // Печать заказа.
    $arActions []= array(
        'ICON' => 'print',
        'DEFAULT' => false,
        'TEXT' => GetMessage('ACTION_PRINT'),
        'ACTION' => $lAdmin->ActionRedirect("linemedia.auto_sale_order_print.php?ID=".$arOrder['ID']."&lang=".LANGUAGE_ID.GetFilterParams("filter_"))
    );
    
    // Изменение заказа.
    if (CSaleOrder::CanUserUpdateOrder($arOrder['ID'], $GLOBALS['USER']->GetUserGroupArray())) {
        $arActions []= array(
            'ICON' => 'edit',
            'DEFAULT' => false,
            'TEXT' => GetMessage('ACTION_EDIT'),
            'ACTION' => $lAdmin->ActionRedirect("linemedia.auto_sale_order_edit.php?ID=".$arOrder['ID']."&lang=".LANGUAGE_ID.GetFilterParams("filter_"))
        );
    }
    
    /*
     * Создание событий для модуля
     */
    $events = GetModuleEvents("linemedia.auto", "OnAfterAdminShowBasketRow");
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array(&$row, &$arBasketItem, &$arBasketProps, &$arOrder, &$arActions));
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    
    $row->AddActions($arActions);
}


// Подвал списка
$lAdmin->AddFooter(
    array(
        array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $dbBasketList->SelectedRowsCount()),
        array("title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0", "counter" => true),
    )
);


// Групповые операции.
$arGroupActions = array(
    'pay' => GetMessage("GROUP_ACTION_PAY"),
    'pay_no' => GetMessage("GROUP_ACTION_PAY_NO"),
    'cancel' => GetMessage("GROUP_ACTION_CANCEL"),
    'cancel_no' => GetMessage("GROUP_ACTION_CANCEL_NO"),
    'delivery' => GetMessage("GROUP_ACTION_DELIVERY"),
    'delivery_no' => GetMessage("GROUP_ACTION_DELIVERY_NO"),
);

// Групповые операции со статусами.
foreach ($statuses as $status) {
    $arGroupActions['status_'.$status['ID']] = GetMessage("GROUP_ACTION_SET_STATUS").' "'.$status['NAME'].'"';
}



$lAdmin->AddGroupActionTable($arGroupActions);

$lAdmin->AddAdminContextMenu();

$lAdmin->CheckListMode();

            
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");


?>

<form name="find_form" method="GET" action="<?= $APPLICATION->GetCurPage() ?>?">
<?
$arFilterFieldsTmp = array(
    GetMessage("SALE_F_DATE_UPDATE"),
    GetMessage("SALE_F_ID"),
    GetMessage("SALE_F_STATUS"),
    GetMessage("SALE_F_PAYED"),
    GetMessage("SALE_F_CANCELED"),
    GetMessage("SALE_F_PERSON_TYPE"),
    GetMessage("SALE_F_PAY_SYSTEM"),
    GetMessage("SALE_F_DELIVERY"),
    GetMessage("SALE_F_SUPPLIER"),
    GetMessage("SALE_F_ARTICLE"),
    GetMessage("SALE_F_BRAND"),
    GetMessage("SALE_F_USER_ID"),
    GetMessage("SALE_F_USER_LOGIN"),
    GetMessage("SALE_F_USER_EMAIL")
);


/*
 * Создание событий для модуля: добавление дополнительных фильтров.
 */
$events = GetModuleEvents("linemedia.auto", "OnAdminOrderListBuildFilters");
$arFiltersHTML = array();
while ($arEvent = $events->Fetch()) {
     ExecuteModuleEventEx($arEvent, array($USER->GetID(), &$arFilterFieldsTmp, &$arFiltersHTML));
}

$oFilter = new CAdminFilter(
    $sTableID."_filter",
    $arFilterFieldsTmp
);


$oFilter->Begin();
?>

<tr>
    <td><b><?= GetMessage("SALE_F_DATE") ?>:</b></td>
    <td>
        <?= CalendarPeriod("filter_date_from", $filter_date_from, "filter_date_to", $filter_date_to, "find_form", "Y") ?>
    </td>
</tr>
<tr>
    <td><?= GetMessage("SALE_F_DATE_UPDATE") ?>:</td>
    <td>
        <?= CalendarPeriod("filter_date_update_from", $filter_date_update_from, "filter_date_update_to", $filter_date_update_to, "find_form", "Y") ?>
    </td>
</tr>
<tr>
    <td><?= GetMessage("SALE_F_ID") ?>:</td>
    <td>
        <script language="JavaScript">
            function filter_id_from_change()
            {
                if (document.find_form.filter_id_to.value.length <= 0) {
                    document.find_form.filter_id_to.value = document.find_form.filter_id_from.value;
                }
            }
        </script>
        <?= GetMessage("SALE_F_FROM") ?>
        <input type="text" name="filter_id_from" OnChange="filter_id_from_change()" value="<?= (intval($filter_id_from) > 0) ? intval($filter_id_from) : ""?>" size="10" />
        
        <?= GetMessage("SALE_F_TO") ?>
        <input type="text" name="filter_id_to" value="<?= (intval($filter_id_to) > 0) ? intval($filter_id_to) : ""?>" size="10" />
    </td>
</tr>
<tr>
    <td valign="top"><?= GetMessage("SALE_F_STATUS")?>:<br /><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt=""></td>
    <td valign="top">
        <select name="filter_status[]" multiple size="3">
            <?
            $dbStatusList = CSaleStatus::GetList(
                    array("SORT" => "ASC"),
                    array("LID" => LANGUAGE_ID),
                    false,
                    false,
                    array("ID", "NAME")
                );
            while ($arStatusList = $dbStatusList->Fetch()) {
                ?><option value="<?= htmlspecialchars($arStatusList["ID"]) ?>"<?if (is_array($filter_status) && in_array($arStatusList["ID"], $filter_status)) echo " selected"?>>[<?= htmlspecialchars($arStatusList["ID"]) ?>] <?= htmlspecialcharsEx($arStatusList["NAME"]) ?></option><?
            }
            ?>
        </select>
    </td>
</tr>
<tr>
    <td><?= GetMessage("SALE_F_PAYED") ?>:</td>
    <td>
        <select name="filter_payed">
            <option value=""><?= GetMessage("SALE_F_ALL")?></option>
            <option value="Y"<? if ($filter_payed == "Y") echo " selected" ?>><?= GetMessage("SALE_YES")?></option>
            <option value="N"<? if ($filter_payed == "N") echo " selected" ?>><?= GetMessage("SALE_NO")?></option>
        </select>
    </td>
</tr>
<tr>
    <td><?= GetMessage("SALE_F_CANCELED") ?>:</td>
    <td>
        <select name="filter_canceled">
            <option value=""><?= GetMessage("SALE_F_ALL")?></option>
            <option value="Y"<? if ($filter_canceled == "Y") echo " selected" ?>><?= GetMessage("SALE_YES")?></option>
            <option value="N"<? if ($filter_canceled == "N") echo " selected" ?>><?= GetMessage("SALE_NO")?></option>
        </select>
    </td>
</tr>
<tr>
    <td>
        <?= GetMessage("SALE_F_PERSON_TYPE") ?>:<br />
        <img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td>
        <select name="filter_person_type[]" multiple size="3">
            <option value=""><?= GetMessage("SALE_F_ALL") ?></option>
            <? $l = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array()); ?>
            <? while ($personType = $l->Fetch()) { ?>
                <option value="<?= htmlspecialchars($personType["ID"])?>"<? if (is_array($filter_person_type) && in_array($personType["ID"], $filter_person_type)) echo " selected"?>>
                    [<?= htmlspecialchars($personType["ID"]) ?>] <?= htmlspecialchars($personType["NAME"])?> <?= "(".htmlspecialchars($personType["LID"]).")";?>
                </option>
            <? } ?>
        </select>
    </td>
</tr>
<tr>
    <td>
        <?= GetMessage("SALE_F_PAY_SYSTEM") ?>:<br />
        <img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td>
        <select name="filter_pay_system[]" multiple size="3">
            <option value=""><?= GetMessage("SALE_F_ALL") ?></option>
            <? $l = CSalePaySystem::GetList(Array("SORT"=>"ASC", "NAME"=>"ASC"), Array()); ?>
            <? while ($paySystem = $l->Fetch()) { ?>
                <option value="<?= htmlspecialchars($paySystem["ID"])?>"<? if (is_array($filter_pay_system) && in_array($paySystem["ID"], $filter_pay_system)) echo " selected" ?>>
                    [<?= htmlspecialchars($paySystem["ID"]) ?>] <?= htmlspecialchars($paySystem["NAME"])?> <?= "(".htmlspecialchars($paySystem["LID"]).")";?>
                </option>
            <? } ?>
        </select>
    </td>
</tr>
<tr>
    <td>
        <?= GetMessage("SALE_F_DELIVERY") ?>:<br />
        <img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td>
        <select name="filter_delivery[]" multiple size="3">
            <option value=""><?= GetMessage("SALE_F_ALL") ?></option>
            <?
            $rsDeliveryServicesList = CSaleDeliveryHandler::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array());
            $arDeliveryServicesList = array();
            while ($arDeliveryService = $rsDeliveryServicesList->Fetch()) {
                if (!is_array($arDeliveryService) || !is_array($arDeliveryService["PROFILES"])) {
                    continue;
                }
                foreach ($arDeliveryService["PROFILES"] as $profile_id => $arDeliveryProfile) {
                    $delivery_id = $arDeliveryService["SID"].":".$profile_id;
                    ?><option value="<?echo htmlspecialchars($delivery_id)?>"<?if (is_array($filter_delivery) && in_array($delivery_id, $filter_delivery)) echo " selected"?>>[<?echo htmlspecialchars($delivery_id)?>] <?echo htmlspecialchars($arDeliveryService["NAME"].": ".$arDeliveryProfile["TITLE"])?></option><?
                }
            }
            
            $dbDelivery = CSaleDelivery::GetList(
                        array("SORT"=>"ASC", "NAME"=>"ASC"),
                        array(
                                "ACTIVE" => "Y",
                            )
                );

            while ($arDelivery = $dbDelivery->GetNext()) { ?>
                <option value="<?= $arDelivery["ID"]?>"<? if (is_array($filter_delivery) && in_array($delivery_id, $filter_delivery)) echo " selected"?>>
                    [<?= $arDelivery["ID"]?>] <?= $arDelivery["NAME"]?>
                </option>
            <? } ?>
        </select>
    </td>
</tr>
<tr>
    <td><?= GetMessage("SALE_F_SUPPLIER") ?>:</td>
    <td>
        <select name="filter_supplier[]" size="5" multiple="multiple">
            <?
                $list = LinemediaAutoSupplier::getList();
                foreach ($list as $key=>$item) {?>
                    <option value="<?=$item['ID']?>" <?if (in_array($item['ID'], $_REQUEST['filter_supplier'])) {?>selected="selected"<?}?>>[<?=$item['PROPS']['supplier_id']['VALUE']?>] <?=$item['NAME']?></option>
                <?}
            ?>
        </select>
    </td>
</tr>
<tr>
    <td><?= GetMessage("SALE_F_ARTICLE") ?>:</td>
    <td>
        <input type="text" name="filter_article" value="<?= htmlspecialcharsEx($filter_article)?>" size="40" />
    </td>
</tr>
<tr>
    <td><?= GetMessage("SALE_F_BRAND") ?>:</td>
    <td>
        <input type="text" name="filter_brand" value="<?= htmlspecialcharsEx($filter_brand)?>" size="40" />
    </td>
</tr>
<tr>
    <td><?= GetMessage("SALE_F_USER_ID") ?>:</td>
    <td>
        <?= FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
    </td>
</tr>
<tr>
    <td><?= GetMessage("SALE_F_USER_LOGIN") ?>:</td>
    <td>
        <input type="text" name="filter_user_login" value="<?= htmlspecialcharsEx($filter_user_login)?>" size="40" />
    </td>
</tr>
<tr>
    <td><?= GetMessage("SALE_F_USER_EMAIL") ?>:</td>
    <td>
        <input type="text" name="filter_user_email" value="<?= htmlspecialcharsEx($filter_user_email)?>" size="40" />
    </td>
</tr>

<? foreach ($arFiltersHTML as $arFilterHTML) { ?>
    <?= $arFilterHTML ?>
<? } ?>

<?
$oFilter->Buttons(
    array(
        "table_id" => $sTableID,
        "url" => $APPLICATION->GetCurPage(),
        "form" => "find_form"
    )
);
$oFilter->End();
?>

</form>

<!-- Данные -->
<? $lAdmin->DisplayList(); ?>

<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php'); ?>
