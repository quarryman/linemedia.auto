<?php

$bAjaxMode = (isset($_GET['AJAX']));

if ($bAjaxMode == true) {
    define('NO_KEEP_STATISTIC', true); // Не собираем статистику по действиям AJAX.
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

/*
 * Добавим стили установщика и jQuery
 */
$APPLICATION->SetAdditionalCSS("/bitrix/modules/linemedia.auto/interface/style.css");
$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError('LINEMEDIA AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("iblock")) {
    ShowError('IBLOCK MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("catalog")) {
    ShowError('CATALOG MODULE NOT INSTALLED');
    return;
}


$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($sMGRight == "D" || $sMGRight == "R") {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

IncludeModuleLangFile(__FILE__);


$aParams = array();
$aParams['CURRENCY'] = CCurrency::GetBaseCurrency();

$aSites = array();
$aSiteRes = CSite::GetList($by="sort", $order="asc", Array());
while ($aSiteItem = $aSiteRes->Fetch()) {
    $aSites[$aSiteItem['ID']] = $aSiteItem;
}
unset($aSiteItem);

$aParams['LID'] = (isset($_REQUEST['SITE_ID']) && strlen(trim($_REQUEST['SITE_ID'])) > 0) ? trim($_REQUEST['SITE_ID']) : current(reset($aSites));

$sImportMultiplePrices = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_IMPORT_MULTIPLE_PRICES');



if ($bAjaxMode == false) {
    $APPLICATION->SetTitle(GetMessage('LM_AUTO_ORDER_CREATE_TITLE'));
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
    ?>
    <script src="http://yandex.st/json2/2011-01-18/json2.min.js"></script>
    <style>
        label {cursor: pointer;}
    </style>
    <?
}

$a = (isset($_GET['step']) && strlen(trim($_GET['step'])) > 0) ? trim($_GET['step']) : 'main';

switch ($a) {
    case 'main':
    default:
        // Доставки
        $rsDeliverySystems = CSaleDelivery::GetList(array('SORT' => 'ASC', 'NAME' => 'ASC'), array('ACTIVE' => 'Y', 'LID' => $aParams['LID']));
        $aServiceDelivery = array();
        while ($arItem = $rsDeliverySystems->GetNext()) {
            $aServiceDelivery[$arItem['ID']] = $arItem;
        }
        unset($arItem, $rsDeliverySystems);
        
        
        /*
         * Выбор или регистрация пользователя.
         */
        include ('sale_order_create_main.php');
        
        break;
    
    
    // Формирование заказа.
    case 'make':
        $iMember = (isset($_GET['member']) && intval($_GET['member']) > 0) ? intval($_GET['member']) : false;
        $aUser = CUser::GetByID($iMember)->Fetch();
        
        // Список сайтов
        $arSites = array();
        $rsSites = CSite::GetList($b="sort", $o="desc", array());
        while ($arSite = $rsSites->Fetch()) {
            $arSites[$arSite['ID']] = $arSite;
        }
        
        /*
         * Создаём событие "Отображение формы создания заказа"
         */
        $events = GetModuleEvents("linemedia.auto", "OnShowOrderCreateForm");
        while ($arEvent = $events->Fetch()) {
            $html .= ExecuteModuleEventEx($arEvent, array($iMember));
        }
        
        
        // Удаление старых добавленных корзин.
        CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());
        
        if (isset($aUser['ID']) && intval($aUser['ID']) > 0) {
            
            $_SESSION['CREATE_ORDER_USER_ID'] = (int) $aUser['ID']; 
            
            // Формирование списка типов плательщиков.
            $aPersonType = array();
            $oPersonTypeRes = CSalePersonType::GetList(array('SORT' => 'ASC'), array('LID' => $aParams['LID']), false, false, array('ID', 'NAME'));
            while ($aPersonTypeItem = $oPersonTypeRes->Fetch()) {
                $aPersonType[$aPersonTypeItem['ID']] = $aPersonTypeItem['NAME'];
            }
            unset($aPersonTypeItem, $oPersonTypeRes);
            
            if (empty($aPersonType)) {
                ShowError(GetMessage('ERROR_NO_PERSON_TYPE'));
                die();
            }
            
            // Сайт
            $iSiteId = (isset($_REQUEST['site_id']) && !empty($_REQUEST['site_id'])) ? (strval($_REQUEST['site_id'])) : (key($arSites));            
            
            // Текущий тип плательщика.
            $iPersonTypeSelect = (isset($_GET['p_type']) && intval($_GET['p_type']) > 0) ? intval($_GET['p_type']) : key($aPersonType);
            
            
            // Формирование списка платежных систем.
            $aPaySystem = array();
            $oPaySysRes = CSalePaySystem::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y', 'PSA_PERSON_TYPE_ID' => $iPersonTypeSelect), false, false, array('ID', 'NAME'));
            while ($aPaySystemItem = $oPaySysRes->Fetch()) {
                $aPaySystem[$aPaySystemItem['ID']] = $aPaySystemItem['NAME'];
            }
            unset($aPaySystemItem, $oPaySysRes);
            
            
            // Формирование списка групп свойств.
            $aPropsGroup = array();
            $oPropGroupRes = CSaleOrderPropsGroup::GetList(array('SORT' => 'ASC'), array('PERSON_TYPE_ID' => $iPersonTypeSelect), false, false, array('ID', 'NAME'));
            while($aPropsGroupItem = $oPropGroupRes->Fetch()){
                $aPropsGroup[$aPropsGroupItem['ID']] = $aPropsGroupItem;
            }
            unset($aPropsGroupItem, $oPropGroupRes);
            
            
            // Формирование списка свойств.
            $rsProps = CSaleOrderProps::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), array('PERSON_TYPE_ID' => $iPersonTypeSelect) );   // Не выбираем специальные скрытые свойства
            while ($arProp = $rsProps->GetNext()) {
                if (in_array($arProp['CODE'], $aPropIgnore)){
                    continue;
                }
                if (in_array($arProp['TYPE'], array("SELECT", "MULTISELECT", "RADIO"))) {
                    $arProp['VARIANTS'] = array();
                    $rsOrderPropVariants = CSaleOrderPropsVariant::GetList(array("SORT"=>"ASC", "NAME"=>"ASC", "VALUE"=>"ASC"), array('ORDER_PROPS_ID'=>$arProp['ID']));
                    while ($arOrderPropVariant = $rsOrderPropVariants->GetNext()) {
                        $arProp['VARIANTS'][$arOrderPropVariant['ID']] = $arOrderPropVariant;
                    }
                } elseif ($arProp['TYPE'] == "LOCATION") {
                    $location_prop_id = $arProp['ID']; // Запомним ID свойства с типом LOCATION
                    $arProp['CITIES'] = array();
                    $rsLocations = CSaleLocation::GetList(array("CITY_NAME"=>"ASC"), array("COUNTRY_LID"=>LANGUAGE_ID, "CITY_LID"=>LANGUAGE_ID));
                    while ($arCity = $rsLocations->GetNext()) {
                        $arProp['CITIES'][$arCity['ID']] = $arCity['CITY_NAME'];
                    }
                }
                $aPropsGroup[$arProp['PROPS_GROUP_ID']]['PROPS'][$arProp['ID']] = $arProp;
            }
            unset($arProp, $rsProps, $aPropIgnore);
            
                        
            // Формирование списка служб доставки.
            $rsDeliverySystems = CSaleDelivery::GetList(array("PRICE"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), array('ACTIVE' => 'Y', 'LID' => $aParams['LID']));
            $aServiceDelivery = array();
            while ($arItem = $rsDeliverySystems->GetNext()) {
                $aServiceDelivery[$arItem['ID']] = $arItem;
            }
            unset($arItem, $rsDeliverySystems);
            
            // Обработчики служб доставки.
            $rsDeliverySystems = CSaleDeliveryHandler::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), array('ACTIVE' => 'Y', 'SITE_ID' => $aParams['LID']));
            $aServiceDeliveryHandler = array();
            while ($arItem = $rsDeliverySystems->GetNext()){
                $aServiceDeliveryHandler[$arItem['SID']] = $arItem;
            }
            unset($arItem, $rsDeliverySystems);
            
            
            // Отложенная оплата.
            $aParams['DEFERRED_PAYMENT'] = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DEFERRED_PAYMENT', 'N'); 
            
            
            /*
             * Событие перед выводом формы дял создания заказа.
             */
            $events = GetModuleEvents("linemedia.auto", "OnAdminCreateOrderFormShow");
            while ($arEvent = $events->Fetch()) {
                ExecuteModuleEventEx($arEvent, array($arUser['ID']));
            }
            
            
            /*
             * Формирование заказа.
             */
            include ('sale_order_create_make.php');
            
        } else {
            LocalRedirect('/bitrix/admin/linemedia.auto_sale_order_create.php');
            exit();
        }
        break;
        
        
    // Регистрация нового пользователя.
    case 'register_user':
        $user_name1 = trim(strip_tags($_REQUEST['name']));
        $user_name2 = trim(strip_tags($_REQUEST['name2']));
        $user_email = trim(strip_tags($_REQUEST['email']));

        /*
        if (strlen($user_email) == 0) {
            $user_email  = $USER->GetEmail();
        }
        */
        
        $suffix = '';
        
        $user_phone     = (isset($_REQUEST['phone'])) ? trim(strip_tags($_REQUEST['phone'])) : '';
        $user_country   = (isset($_REQUEST['country'])) ? trim(strip_tags($_REQUEST['country'])) : '';
        $user_state     = (isset($_REQUEST['state'])) ? trim(strip_tags($_REQUEST['state'])) : '';
        $user_city      = (isset($_REQUEST['city'])) ? trim(strip_tags($_REQUEST['city'])) : '';
        $user_zip       = (isset($_REQUEST['zip'])) ? trim(strip_tags($_REQUEST['zip'])) : '';
        $user_street    = (isset($_REQUEST['street'])) ? trim(strip_tags($_REQUEST['street'])) : '';

        $aErrors = array();
        $aResult = array('msg' => '', 'status' => false, 'member_id' => 0);

        if (!$user_name1) {
            $aErrors []= GetMessage('USER_REGISTER_ERROR_NO_NAME');
        }
        if (!$user_email) {
            $aErrors []= GetMessage('USER_REGISTER_ERROR_NO_EMAIL');
        }
        if ($user_email) {
            if (!check_email($user_email)) {
                $aErrors []= GetMessage('USER_REGISTER_ERROR_WRONG_EMAIL');
            }
        }
        
        if (count($aErrors) === 0) {
            $user_login = reset(explode("@", $user_email));

            // Проверка логина пользователя на занятость.
            $not_found = true;
            while ($not_found) {
                $existingUser = CUser::GetByLogin($user_login . $suffix)->Fetch();
                if ($existingUser['ID'] > 0) {
                    $suffix++;
                } else {
                    $not_found = false;
                }
            }
            $user_login = $user_login.$suffix; // Новый логин пользователя
            $password = randString(8); // Генерируем пароль из 8 символов. Его потом надо будет на емыл ему отправить
            $sCheckword = randString(8);
            $sCheckwordTime = $DB->CurrentTimeFunction();
        
            $new_user_id = $USER->Add(array(
                'LOGIN' => $user_login,
                'NAME' => $user_name1,
                'LAST_NAME' => $user_name2,
                'EMAIL' => $user_email,
                'PASSWORD' => $password,
                'CONFIRM_PASSWORD' => $password,
                'GROUP_ID' => array(COption::GetOptionInt('main', 'new_user_registration_def_group')), // Назначем группу по умолчанию
                'ACTIVE' => "Y",
                'ADMIN_NOTES' => GetMessage('USER_REGISTER_AUTOMATICALLY'),
                'PERSONAL_PHONE' => $user_phone,
                'PERSONAL_COUNTRY' => $user_country,
                'PERSONAL_STATE' => $user_state,
                'PERSONAL_CITY' => $user_city,
                'PERSONAL_ZIP' => $user_zip,
                'PERSONAL_STREET' => $user_street,
                'CHECKWORD' => $sCheckword,
                '~CHECKWORD_TIME' => $sCheckwordTime,
            ));
            if ($new_user_id > 0) {
                // Отправка сообщения пользователю с его логином и паролем
                CEvent::Send('NEW_USER', $aParams['LID'], array(
                    'USER_ID' => $new_user_id,
                    'NAME' => $user_name1,
                    'LAST_NAME' => $user_name2,
                    'LOGIN' => $user_login,
                    'PASSWORD' => $password,
                    'EMAIL' => $user_email,
                    'USER_IP' => $_SERVER['REMOTE_ADDR'],
                    'USER_HOST' => @gethostbyaddr($_SERVER['REMOTE_ADDR']),
                    'CHECKWORD' => $sCheckword,
                ));
                $aResult = array('msg' => GetMessage('USER_REGISTER_SUCCESSFULLY'), 'status' => true, 'member_id' => $new_user_id);
            } else {
                $aErrors []= $USER->LAST_ERROR;
            }
        }

        if (count($aErrors) > 0) {
            $aResult['msg'] = '';
            foreach ($aErrors as $v) {
                $aResult['msg'] .= $v . '<br />';
            }
            unset($v);
        }
        
        echo json_encode($aResult);
        exit();
        break;
    
    
    // Пересчет цены, изменение корзин.
    case 'recalc':
        $type = (string) $_REQUEST['type'];
        
        $user_id = (int) $_REQUEST['member_id'];
        
        $site_id = (string) $_REQUEST['site_id'];
        $lmbasket = new LinemediaAutoBasket($user_id);
        
        switch ($type) {
            case 'add':
                $part_id        = (int) $_REQUEST['PARAMS']['part_id'];
                $supplier_id    = (int) $_REQUEST['PARAMS']['supplier_id'];
                $quantity       = 1;
                
                $price = (float) $_REQUEST['PARAMS']['price'];
                $addit = array('SITE_ID' => $site_id);
/**
*   для внешних поставщиков собираем пакет нужной им информации
*/
                $addit['extra'] = (array)$_REQUEST['extra'];
                $addit['article'] = $_REQUEST['PARAMS']['article'];
                $addit['brand_id'] = $_REQUEST['PARAMS']['brand_id'];
                if(isset($addit['extra'][ strval($_REQUEST['PARAMS']['part_id']).'bt' ])) {
                    $addit['brand_title'] = $addit['extra'][ strval($_REQUEST['PARAMS']['part_id']).'bt' ];
                }
                $lmbasket->addItem($part_id, $supplier_id, $quantity, $price, $addit);
                break;
                
            case 'del':
                CSaleBasket::Delete(intval($_REQUEST['id']));
                break;
        }
        
        $params = array();
        if (!empty($_REQUEST['OrderParams']) && trim($_REQUEST['OrderParams']) !== 'undefined') {
            parse_str($_REQUEST['OrderParams'], $params);
            $params = $params['basket'];
        }
        
        // Изменение количества товара.
        parse_str(urldecode($_REQUEST['baskets']), $baskets);
        foreach ($baskets['basket'] as $basket_id => $quantity) {
            CSaleBasket::Update(intval($basket_id), array('QUANTITY' => intval($quantity)));
        }
        
        
        // Тип плательщика.
        $person_type = (int) $_REQUEST['person_type'];
        
        // Платежная система.
        $pay_system = (int) $_REQUEST['pay_system'];
        
        // Изменение доставки.
        $delivery_system  = (int) $_REQUEST['delivery_system'];
        $delivery_price   = (float) $_REQUEST['price_delivery'];
        
        // Изменение скидки.
        $discount = 0.0;
        $discount_type  = (string) $_REQUEST['discount_type'];
        $discount_value = (float) $_REQUEST['discount_value'];
        
        
        // Корзины.
        $baskets = $lmbasket->getOrderedBaskets();
        
        $all_price = 0.0;
        $total_price = 0.0;
        foreach ($baskets as $basket) {
            $total_price += $basket['PRICE'] * $basket['QUANTITY'];
        }
        $all_price = $total_price;
        
        if ($discount_type == 'percentage') {
            if ($discount_value > 100) {
                $discount_value = 100;
            }
            $discount = $total_price / 100.0 * $discount_value;
        } else {
            $discount = $discount_value;
        }
        
        $total_price -= $discount;
        $total_price += $delivery_price;    
        
        
        
        // Создание заказа.
        if ($type == 'make') {
            // Создание заказа.
            $arFields = array(
                'LID'               => $aParams['LID'],
                'PERSON_TYPE_ID'    => $person_type,
                'PAYED'             => 'N',
                'CANCELED'          => 'N',
                'STATUS_ID'         => 'N',
                'PRICE'             => ($all_price - $discount + $delivery_price),
                'CURRENCY'          => $aParams['CURRENCY'],
                'USER_ID'           => IntVal($user_id),
                'PAY_SYSTEM_ID'     => $pay_system,
                'PRICE_DELIVERY'    => $delivery_price,
                'DELIVERY_ID'       => $delivery_system,
                'DISCOUNT_VALUE'    => $discount,
                'TAX_VALUE'         => 0.0,
                'COMMENTS'          => strval($_REQUEST['manager_comment'])
            );
            
            /*
             * Создаём событие "Перед добавлением заказа"
             */
            $events = GetModuleEvents("linemedia.auto", "OnBeforeOrderAdd");
            while ($arEvent = $events->Fetch()) {
                ExecuteModuleEventEx($arEvent, array(&$arFields));
            }
            
            $order_id = CSaleOrder::Add($arFields);
            
            /*
             * Создаём событие "После добавления заказа"
             */
            $events = GetModuleEvents("linemedia.auto", "OnAfterOrderAdd");
            while ($arEvent = $events->Fetch()) {
                ExecuteModuleEventEx($arEvent, array($order_id, &$arFields));
            }
            
            if ($order_id > 0) {
                // Свойства заказа.
                $dbproperties = CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $person_type), false, false, array());
                $arProperties = array();
                while ($property = $dbproperties->Fetch()) {
                    $arProperties[$property['ID']] = $property;
                }
                $order_prop = (array) $_REQUEST['prop'];
                if (!empty($arProperties)) {
                    foreach ($arProperties as $property_id => $property) {
                        if (!empty($order_prop)) {
                            CSaleOrderPropsValue::Add(
                                array(
                                    'ORDER_ID'          => $order_id,
                                    'ORDER_PROPS_ID'    => $property_id,
                                    'NAME'              => $arProperties[$property_id]['NAME'],
                                    'VALUE'             => $_REQUEST['prop'][$property_id],
                                    'CODE'              => $arProperties[$property_id]['CODE']
                                )
                            );
                        }
                    }
                }
                
                // Свойство заказа: "разрешение оплаты".
                $dbprops = CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $person_type, 'CODE' => 'ALLOW_PAYMENT'), false, false, array('ID'));
                if ($prop_allow_payment = $dbprops->Fetch()) {
                    CSaleOrderPropsValue::Add(
                        array(
                            'ORDER_ID'          => $order_id,
                            'ORDER_PROPS_ID'    => $prop_allow_payment['ID'],
                            'NAME'              => GetMessage('ALLOW_PAYEMNT'),
                            'VALUE'             => 'N',
                            'CODE'              => 'ALLOW_PAYMENT'
                        )
                    );
                }
                
                CSaleBasket::OrderBasket($order_id, CSaleBasket::GetBasketUserID(), $aParams['LID']);
            }
            
            $arOrder = CSaleOrder::GetByID($order_id);
            
            /*
             * Создаём событие "Полное создание заказа"
             */
            $events = GetModuleEvents("linemedia.auto", "OnOrderComplete");
            while ($arEvent = $events->Fetch()) {
                ExecuteModuleEventEx($arEvent, array($order_id, $arOrder));
            }
            
            // Редирект на список заказов.
            if ($order_id > 0) {
                ?>
                    <div style="text-align: center;">
                        <h1><?= GetMEssage('ORDER_SUCCESSFULL') ?></h1>
                    </div>
                <?
                if (strval($_REQUEST['after']) == 'go') {
                    ?>
                    <script>
                        document.location = '/bitrix/admin/linemedia.auto_sale_order_detail.php?ID=<?= $order_id ?>&lang=<?= LANG ?>';
                    </script>
                    <?
                }
            }
            exit();
        }
        
        
        $arResult = array(
                        'BASKETS' => $baskets, 
                        'DISCOUNT_PRICE' => $discount,
                        'DELIVERY_PRICE' => $delivery_price,
                        'TOTAL_PRICE' => $total_price,
                        'ALL_PRICE' => $all_price,
                        'CURRENCY' => $aParams['CURRENCY']
                    );
        
        include ('sale_order_create_baskets.php');
        exit();
        break;
    
}

require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');