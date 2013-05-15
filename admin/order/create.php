<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$crmMode = (defined("BX_PUBLIC_MODE") && BX_PUBLIC_MODE && isset($_REQUEST["CRM_MANAGER_USER_ID"]));

/*
 * Добавим стили установщика и jQuery
 */
$APPLICATION->SetAdditionalCSS("/bitrix/modules/linemedia.auto/interface/style.css");
$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError('LINEMEDIA AUTO MODULE NOT INSTALLED');
    return;
}


$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($sMGRight == "D" || $sMGRight == "R") {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}


if ($crmMode) {
    CUtil::DecodeUriComponent($_GET);
    CUtil::DecodeUriComponent($_POST);

    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"/bitrix/themes/.default/sale.css\" />";
}
//double function from sale.ajax.location/process.js
?>
<script>
    function getLocation(country_id, region_id, city_id, arParams, site_id)
    {
        BX.showWait();
    
        property_id = arParams.CITY_INPUT_NAME;
    
        function getLocationResult(res)
        {
            BX.closeWait();
            var obContainer = document.getElementById('LOCATION_' + property_id);
            if (obContainer) {
                obContainer.innerHTML = res;
            }
        }
    
        arParams.COUNTRY = parseInt(country_id);
        arParams.REGION = parseInt(region_id);
        arParams.SITE_ID = site_id;
        
        var url = '/bitrix/components/bitrix/sale.ajax.locations/templates/.default/ajax.php';
        BX.ajax.post(url, arParams, getLocationResult)
    }
</script>
<?

IncludeModuleLangFile(__FILE__);
ClearVars();

$ID = IntVal($ID);
$COUNT_RECOM_BASKET_PROD = 2;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

$arStatusList = False;
$arFilter = array("LID" => LANG, "ID" => "N");
$arGroupByTmpSt = false;
if ($saleModulePermissions < "W") {
    $arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
    $arFilter["PERM_UPDATE"] = "Y";
    $arGroupByTmpSt = array("ID", "NAME", "MAX" => "PERM_UPDATE");
}
$dbStatusList = CSaleStatus::GetList(
    array(),
    $arFilter,
    $arGroupByTmpSt,
    false,
    array("ID", "NAME")
);
$arStatusList = $dbStatusList->Fetch();

if ($saleModulePermissions == "D" || ($saleModulePermissions < "W" && $arStatusList["PERM_UPDATE"] != "Y")) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
$errorMessage = "";

/*****************************************************************************/
/********************* ORDER FUNCTIONS ***************************************/
/*****************************************************************************/

if (!empty($_REQUEST["dontsave"])) {
    CSaleOrder::UnLock($ID);
    LocalRedirect("linemedia.auto_sale_orders_list.php?lang=".LANG.GetFilterParams("filter_", false));
}


// Подключение функций.
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/admin/order/functions.php");


/*****************************************************************************/
/**************************** SAVE ORDER *************************************/
/*****************************************************************************
$bVarsFromForm = false;

/*
 * Создание заказа.
 */
if ($REQUEST_METHOD == "POST" && $save_order_data == "Y" && empty($dontsave) && $saleModulePermissions >= "U" && check_bitrix_sessid()) {
    $ID = IntVal($ID);
    
    //buyer type, new or exist
    $btnNewBuyer = "N";
    if ($btnTypeBuyer == "btnBuyerNew") {
        $btnNewBuyer = "Y";
    }
    
    if (strlen($LID) <= 0) {
        if (!empty($_REQUEST['LID'])) {
            $LID = (string) $_REQUEST['LID'];
        }
        $errorMessage .= GetMessage("SOE_EMPTY_SITE")."<br>";
    }
    $BASE_LANG_CURRENCY = CSaleLang::GetLangCurrency($LID);

    $str_PERSON_TYPE_ID = IntVal($buyer_type_id);
    if ($str_PERSON_TYPE_ID <= 0) {
        $errorMessage .= GetMessage("SOE_EMPTY_PERS_TYPE")."<br>";
    }
    
    if (($str_PERSON_TYPE_ID > 0) && !($arPersonType = CSalePersonType::GetByID($str_PERSON_TYPE_ID))) {
        $errorMessage .= GetMessage("SOE_PERSON_NOT_FOUND")."<br>";
    }
    
    $str_STATUS_ID = trim($STATUS_ID);
    if (strlen($str_STATUS_ID) > 0) {
        if ($saleModulePermissions < "W") {
            $dbStatusList = CSaleStatus::GetList(
                array(),
                array(
                    "GROUP_ID" => $GLOBALS["USER"]->GetUserGroupArray(),
                    "PERM_STATUS" => "Y",
                    "ID" => $str_STATUS_ID
                ),
                array("ID", "MAX" => "PERM_STATUS"),
                false,
                array("ID")
            );
            if (!$dbStatusList->Fetch()) {
                $errorMessage .= str_replace("#STATUS_ID#", $str_STATUS_ID, GetMessage("SOE_NO_STATUS_PERMS"))."<br>";
            }
        }
    }

    $str_PAY_SYSTEM_ID = IntVal($PAY_SYSTEM_ID);
    if ($str_PAY_SYSTEM_ID <= 0) {
        $errorMessage .= GetMessage("SOE_PAYSYS_EMPTY")."<br>";
    }
    if (($str_PAY_SYSTEM_ID > 0) && !($arPaySys = CSalePaySystem::GetByID($str_PAY_SYSTEM_ID, $str_PERSON_TYPE_ID))) {
        $errorMessage .= GetMessage("SOE_PAYSYS_NOT_FOUND")."<br>";
    }
    if (count($_POST["PRODUCT"]) <= 0) {
        $errorMessage .= GetMessage("SOE_EMPTY_ITEMS")."<br>";
    }
    if (isset($DELIVERY_ID) && $DELIVERY_ID != "") {
        $str_DELIVERY_ID = trim($DELIVERY_ID);
        $PRICE_DELIVERY = FloatVal($PRICE_DELIVERY);
    }
    
    $arCupon = fGetCupon($_POST["CUPON"]);
    
    $str_ADDITIONAL_INFO = trim($_POST["ADDITIONAL_INFO"]);
    $str_COMMENTS = trim($_POST["COMMENTS"]);
    
    $profileName = "";
    if (isset($user_profile) && $user_profile != "" && $btnNewBuyer == "N") {
        $userProfileID = IntVal($user_profile);
    }
    //array field send mail
    $FIO = "";
    $rsUser = CUser::GetByID($user_id);
    if ($arUser = $rsUser->Fetch()) {
        if ($arUser["LAST_NAME"] != "") {
            $FIO .= $arUser["LAST_NAME"]." ";
        }
        if ($arUser["NAME"] != "") {
            $FIO .= $arUser["NAME"];
        }
    }

    $arUserEmail = array("PAYER_NAME" => $FIO, "USER_EMAIL" => $arUser["EMAIL"]);

    if ($BREAK_NAME == GetMessage('NEWO_BREAK_NAME'))
        $BREAK_NAME = "";
    if ($BREAK_LAST_NAME == GetMessage('NEWO_BREAK_LAST_NAME'))
        $BREAK_LAST_NAME = "";
    if ($BREAK_SECOND_NAME == GetMessage('NEWO_BREAK_SECOND_NAME'))
        $BREAK_SECOND_NAME = "";

    /*
     * Создание нового пользователя.
     */
    if ($btnNewBuyer == "Y" && strlen($errorMessage) <= 0) {
        if (strlen($NEW_BUYER_EMAIL) <= 0) {
            $emailId = '';
            $dbProperties = CSaleOrderProps::GetList(
                array("ID" => "ASC"),
                array("PERSON_TYPE_ID" => $str_PERSON_TYPE_ID, "ACTIVE" => "Y", "IS_EMAIL" => "Y"),
                false,
                false,
                array("ID")
            );
            while ($arProperties = $dbProperties->Fetch()) {
                if ($emailId == '')
                    $emailId = $arProperties["ID"];

                if ($arProperties["REQUIED"] == "Y")
                    $emailId = $arProperties["ID"];
            }
            $NEW_BUYER_EMAIL = ${"ORDER_PROP_".$emailId};
        }

        if (strlen($NEW_BUYER_EMAIL) <= 0) {
            $errorMessage .= GetMessage("NEWO_BUYER_REG_ERR_MAIL");
        }
        //take default value PHONE for register user
        $dbOrderProps = CSaleOrderProps::GetList(
            array(),
            array("PERSON_TYPE_ID" => $str_PERSON_TYPE_ID, "ACTIVE" => "Y", "CODE" => "PHONE"),
            false,
            false,
            array("ID")
        );
        $arOrderProps = $dbOrderProps->Fetch();
        $NEW_BUYER_PHONE = "";
        if (count($arOrderProps) > 0)
            $NEW_BUYER_PHONE = trim($_POST["ORDER_PROP_".$arOrderProps["ID"]]);

        $NEW_BUYER_SECOND_NAME = '';
        if (strlen($_POST["NEW_BUYER_NAME"]) <= 0 && strlen($_POST["NEW_BUYER_LAST_NAME"]) <= 0) {
            $NEW_BUYER_NAME = trim($_POST["BREAK_NAME"]);
            $NEW_BUYER_LAST_NAME = trim($_POST["BREAK_LAST_NAME"]);
            $NEW_BUYER_SECOND_NAME = trim($_POST["BREAK_SECOND_NAME"]);
        }

        if (strlen($NEW_BUYER_NAME) <= 0 || strlen($NEW_BUYER_LAST_NAME) <= 0)
            $errorMessage .= GetMessage("NEWO_BUYER_REG_ERR_NAME")."<br>";

        $NEW_BUYER_FIO = $NEW_BUYER_LAST_NAME." ".$NEW_BUYER_NAME." ".$NEW_BUYER_SECOND_NAME;

        if (strlen($errorMessage) <= 0) {
            $userRegister = array(
                "NAME" => $NEW_BUYER_NAME,
                "LAST_NAME" => $NEW_BUYER_LAST_NAME,
                "SECOND_NAME" => $NEW_BUYER_SECOND_NAME,
                "PERSONAL_MOBILE" => $NEW_BUYER_PHONE
            );

            $arPersonal = array("PERSONAL_MOBILE" => $NEW_BUYER_PHONE);

            $user_id = CSaleUser::DoAutoRegisterUser($NEW_BUYER_EMAIL, $userRegister, $LID, $arErrors, $arPersonal);
            if (count($arErrors) > 0) {
                foreach($arErrors as $val)
                    $errorMessage .= $val["TEXT"];
            } else {
                $userProfileID = 0;
                $rsUser = CUser::GetByID($user_id);
                $arUser = $rsUser->Fetch();

                $userNew = str_replace("#FIO#", "(".$arUser["LOGIN"].")".(($arUser["NAME"] != "") ? " ".$arUser["NAME"] : "").(($arUser["LAST_NAME"] != "") ? " ".$arUser["LAST_NAME"] : ""), GetMessage("NEWO_BUYER_REG_OK"));
            }
        }
    }

    if (strlen($errorMessage) <= 0) {
        //property order
        $arOrderPropsValues = array();
        $dbOrderProps = CSaleOrderProps::GetList(
            array("SORT" => "ASC"),
            array("PERSON_TYPE_ID" => $str_PERSON_TYPE_ID, "ACTIVE" => "Y"),
            false,
            false,
            array("ID", "NAME", "TYPE", "REQUIED", "IS_LOCATION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "CODE", "SORT")
        );
        while ($arOrderProps = $dbOrderProps->Fetch()) {
            if (!is_array(${"ORDER_PROP_".$arOrderProps["ID"]}))
                //$curVal = trim(${"ORDER_PROP_".$arOrderProps["ID"]});
                $curVal = trim($_POST["ORDER_PROP_".$arOrderProps["ID"]]);
            else
                //$curVal = ${"ORDER_PROP_".$arOrderProps["ID"]};
                $curVal = trim($_POST["ORDER_PROP_".$arOrderProps["ID"]]);

            if ($arOrderProps["TYPE"]=="LOCATION") {
                //$curVal = ${"CITY_ORDER_PROP_".$arOrderProps["ID"]};
                $curVal = $_POST["CITY_ORDER_PROP_".$arOrderProps["ID"]];
                $DELIVERY_LOCATION = IntVal($curVal);
            }
            
            if ($arOrderProps["IS_PAYER"] == "Y") {
                if (strlen($curVal) <= 0) {
                    $curVal = $NEW_BUYER_FIO;
                }
                $arUserEmail["PAYER_NAME"] = trim($curVal);
            }
            
            if ($arOrderProps["IS_EMAIL"] == "Y") {
                $arUserEmail["USER_EMAIL"] = trim($curVal);
            }

            if ($arOrderProps["IS_PROFILE_NAME"] == "Y") {
                $profileName = "";
                if (isset($userProfileID)) {
                    $profileName = $curVal;
                }
            }

            if (
                ($arOrderProps["IS_LOCATION"]=="Y" || $arOrderProps["IS_LOCATION4TAX"]=="Y")
                && IntVal($curVal) <= 0
                ||
                ($arOrderProps["IS_PROFILE_NAME"]=="Y" || $arOrderProps["IS_PAYER"]=="Y")
                && strlen($curVal) <= 0
                ||
                $arOrderProps["REQUIED"]=="Y"
                && $arOrderProps["TYPE"]=="LOCATION"
                && IntVal($curVal) <= 0
                ||
                $arOrderProps["REQUIED"]=="Y"
                && ($arOrderProps["TYPE"]=="TEXT" || $arOrderProps["TYPE"]=="TEXTAREA" || $arOrderProps["TYPE"]=="RADIO" || $arOrderProps["TYPE"]=="SELECT")
                && strlen($curVal) <= 0
                ||
                ($arOrderProps["REQUIED"]=="Y"
                && $arOrderProps["TYPE"]=="MULTISELECT"
                && empty($curVal))
                )
            {
                $errorMessage .= str_replace("#NAME#", $arOrderProps["NAME"], GetMessage("SOE_EMPTY_PROP"))."<br>";
            }

            if ($arOrderProps["TYPE"] == "MULTISELECT") {
                $curVal = "";
                for ($i = 0; $i < count($_POST["ORDER_PROP_".$arOrderProps["ID"]]); $i++) {
                    if ($i > 0) {
                        $curVal .= ",";
                    }
                    $curVal .= $_POST["ORDER_PROP_".$arOrderProps["ID"]][$i];
                }
            }

            $arOrderPropsValues[$arOrderProps["ID"]] = $curVal;
        }
    }

    $str_USER_ID = IntVal($user_id);
    if ($str_USER_ID <= 0 && strlen($errorMessage) <= 0) {
        $str_USER_ID = "";
        $errorMessage .= GetMessage("SOE_EMPTY_USER")."<br>";
    } elseif ($str_USER_ID > 0 && strlen($errorMessage) <= 0) {
        $rsUser = CUser::GetByID($str_USER_ID);
        if (!$rsUser->Fetch()) {
            $errorMessage .= GetMessage("NEWO_ERR_EMPTY_USER")."<br>";
        }
    }
    
    // Saving
    if (strlen($errorMessage) <= 0) {
        //send new user mail
        if ($btnNewBuyer == "Y" && strlen($userNew) > 0) {
            CUser::SendUserInfo($str_USER_ID, $LID, $userNew, true);
        }
        $arShoppingCart = array();
        $arOrderProductPrice = fGetUserShoppingCart($_POST["PRODUCT"], $LID, $BASE_LANG_CURRENCY);
        $arShoppingCart = CSaleBasket::DoGetUserShoppingCart($LID, $str_USER_ID, $arOrderProductPrice, $arErrors, $arCupon);
        $arErrors = array();
        $arWarnings = array();
        
        
        $lmbasket = new LinemediaAutoBasket();
    
        $arShoppingCart = (array) $lmbasket->getOrderedBaskets();
        
        // Параметры заказа.
        $arOrder = CSaleOrder::DoCalculateOrder(
            $LID,
            $str_USER_ID,
            $arShoppingCart,
            $str_PERSON_TYPE_ID,
            $arOrderPropsValues,
            $str_DELIVERY_ID,
            $str_PAY_SYSTEM_ID,
            array(),
            $arErrors,
            $arWarnings
        );
        
        //change delivery price
        if (DoubleVal($arOrder["DELIVERY_PRICE"]) != $PRICE_DELIVERY) {
            $arOrder["PRICE"] = ($arOrder["PRICE"] - $arOrder["DELIVERY_PRICE"]) + $PRICE_DELIVERY;
            $arOrder["DELIVERY_PRICE"] = $PRICE_DELIVERY;
            $arOrder["PRICE_DELIVERY"] = $PRICE_DELIVERY;
        }

        if (count($arShoppingCart) <= 0 && count($arOrderProductPrice) > 0) {
            $errorMessage .= GetMessage('NEWO_ERR_BUSKET_NULL')."<br>";
        } else {
            if (count($arWarnings) > 0) {
                foreach ($arWarnings as $val) {
                    $errorMessage .= $val["TEXT"]."<br>";
                }
            }
            if (count($arErrors) > 0) {
                foreach ($arErrors as $val) {
                    $errorMessage .= $val["TEXT"]."<br>";
                }
            }
        }
    }
    
    if (strlen($errorMessage) <= 0) {
        //another order parametrs
        $arAdditionalFields = array(
            "USER_DESCRIPTION" => $_POST["USER_DESCRIPTION"],
            "ADDITIONAL_INFO" => $str_ADDITIONAL_INFO,
            "COMMENTS" => $str_COMMENTS,
        );
        
        if (count($arOrder) > 0) {
            $arErrors = array();
            $OrderNewSendEmail = false;
            
            $arOldOrder = CSaleOrder::GetByID($ID);
            
            if ($ID <= 0 || $arOldOrder['STATUS_ID'] == $str_STATUS_ID) {
                $arAdditionalFields['STATUS_ID'] = $str_STATUS_ID;
            }
            
            $arOrder['PERSON_TYPE_ID']  = $str_PERSON_TYPE_ID;
            $arOrder['DELIVERY_ID']     = $str_DELIVERY_ID;
            $arOrder['PAY_SYSTEM_ID']   = $str_PAY_SYSTEM_ID;
            
            // mail('igor@linemedia.ru', 'order', print_r($arOrder, true));
            
            /*
             * Создаём событие "Перед добавлением заказа"
             */
            $events = GetModuleEvents("linemedia.auto", "OnBeforeOrderAdd");
            while ($arEvent = $events->Fetch()) {
                ExecuteModuleEventEx($arEvent, array(&$arOrder, &$arAdditionalFields));
            }
            
            $tmpID = CSaleOrder::DoSaveOrder($arOrder, $arAdditionalFields, $ID, $arErrors, $arCupon);
            
            /*
             * Создаём событие "После добавления заказа"
             */
            $events = GetModuleEvents("linemedia.auto", "OnAfterOrderAdd");
            while ($arEvent = $events->Fetch()) {
                ExecuteModuleEventEx($arEvent, array($tmpID, &$arOrder, &$arAdditionalFields));
            }
            
            
            /*
             * Привязка корзин к заказу.
             */
            CSaleBasket::OrderBasket($tmpID, CSaleBasket::GetBasketUserID(), $LID);
            
            
            $arOrderFields = CSaleOrder::GetByID($tmpID);
            
            /*
             * Создаём событие "Полное создание заказа"
             */
            $events = GetModuleEvents("linemedia.auto", "OnOrderComplete");
            while ($arEvent = $events->Fetch()) {
                ExecuteModuleEventEx($arEvent, array($tmpID, $arOrderFields));
            }
            
            
            if ($ID <= 0) {
                $OrderNewSendEmail = true;
            } else {
                if ($arOldOrder["STATUS_ID"] != $str_STATUS_ID) {
                    CSaleOrder::StatusOrder($ID, $str_STATUS_ID);
                }
            }
            
            $ID = $tmpID;
            
            if ($ID > 0 && count($arErrors) <= 0) {
                $CANCELED = trim($_POST["CANCELED"]);
                $REASON_CANCELED = trim($_POST["REASON_CANCELED"]);
                if ($CANCELED != "Y") {
                    $CANCELED = "N";
                }
                $arOrder2Update = array();
                
                if ($arOldOrder["CANCELED"] != $CANCELED) {
                    $bUserCanCancelOrder = CSaleOrder::CanUserCancelOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
                    
                    $errorMessageTmp = "";
                    
                    if (!$bUserCanCancelOrder) {
                        $errorMessageTmp .= GetMessage("SOD_NO_PERMS2CANCEL").". ";
                    }
                    if (strlen($errorMessageTmp) <= 0) {
                        if (!CSaleOrder::CancelOrder($ID, $CANCELED, $REASON_CANCELED)) {
                            if ($ex = $APPLICATION->GetException()) {
                                if ($ex->GetID() != "ALREADY_FLAG") {
                                    $errorMessageTmp .= $ex->GetString();
                                }
                            } else {
                                $errorMessageTmp .= GetMessage("ERROR_CANCEL_ORDER").". ";
                            }
                        }
                    }

                    if ($errorMessageTmp != "") {
                        $arErrors[] = $errorMessageTmp;
                    }
                } else {
                    if ($arOldOrder["REASON_CANCELED"] != $REASON_CANCELED) {
                        $arOrder2Update["REASON_CANCELED"] = $REASON_CANCELED;
                    }
                }
            }
            
            if ($ID > 0 && count($arErrors) <= 0) {
                $PAYED = trim($_POST['PAYED']);
                if ($PAYED != "Y") {
                    $PAYED = "N";
                }
                $PAY_VOUCHER_NUM = trim($_POST["PAY_VOUCHER_NUM"]);
                $PAY_VOUCHER_DATE = trim($_POST["PAY_VOUCHER_DATE"]);
                $PAY_FROM_ACCOUNT = trim($_POST["PAY_FROM_ACCOUNT"]);
                $PAY_FROM_ACCOUNT_BACK = trim($_POST["PAY_FROM_ACCOUNT_BACK"]);
                
                if ($arOldOrder['PAYED'] != $PAYED) {
                    $bUserCanPayOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "P", $GLOBALS["USER"]->GetUserGroupArray());
                    $errorMessageTmp = "";
                    
                    if (!$bUserCanPayOrder) {
                        $errorMessageTmp .= GetMessage("SOD_NO_PERMS2PAYFLAG").". ";
                    }
                    
                    if (strlen($errorMessageTmp) <= 0) {
                        $arAdditionalFields = array(
                            "PAY_VOUCHER_NUM" => ((strlen($PAY_VOUCHER_NUM) > 0) ? $PAY_VOUCHER_NUM : False),
                            "PAY_VOUCHER_DATE" => ((strlen($PAY_VOUCHER_DATE) > 0) ? $PAY_VOUCHER_DATE : False)
                        );

                        $bWithdraw = true;
                        $bPay = true;
                        if ($PAY_CURRENT_ACCOUNT == "Y") {
                            $dbUserAccount = CSaleUserAccount::GetList(
                            array(),
                            array(
                                "USER_ID" => $arOrder["USER_ID"],
                                "CURRENCY" => $arOrder["CURRENCY"],
                                )
                            );
                            if ($arUserAccount = $dbUserAccount->Fetch()) {
                                if (DoubleVal($arUserAccount["CURRENT_BUDGET"]) >= $arOrder["PRICE"]) {
                                    $bPay = false;
                                }
                            }
                        }
                        if ($PAYED == "N" && $PAY_FROM_ACCOUNT_BACK != "Y") {
                            $bWithdraw = false;
                        }
                        
                        if (!CSaleOrder::PayOrder($ID, $PAYED, $bWithdraw, $bPay, 0, $arAdditionalFields)) {
                            if ($ex = $APPLICATION->GetException()) {
                                if ($ex->GetID() != "ALREADY_FLAG") {
                                    $errorMessageTmp .= $ex->GetString();
                                }
                            } else {
                                $errorMessageTmp .= GetMessage("ERROR_PAY_ORDER").". ";
                            }
                        }
                        
                        if ($errorMessageTmp != "") {
                            $arErrors[] = $errorMessageTmp;
                        }
                    }
                } else {
                    if ($arOldOrder["PAY_VOUCHER_NUM"] != $PAY_VOUCHER_NUM) {
                        $arOrder2Update["PAY_VOUCHER_NUM"] = ((strlen($PAY_VOUCHER_NUM) > 0) ? $PAY_VOUCHER_NUM : False);
                    }
                    if ($arOldOrder["PAY_VOUCHER_DATE"] != $PAY_VOUCHER_DATE) {
                        $arOrder2Update["PAY_VOUCHER_DATE"] = ((strlen($PAY_VOUCHER_DATE) > 0) ? $PAY_VOUCHER_DATE : False);
                    }
                }
            }
            
            if ($ID > 0 && count($arErrors) <= 0) {
                $ALLOW_DELIVERY = trim($_POST["ALLOW_DELIVERY"]);
                if ($ALLOW_DELIVERY != "Y") {
                    $ALLOW_DELIVERY = "N";
                }
                $DELIVERY_DOC_NUM = trim($_POST["DELIVERY_DOC_NUM"]);
                $DELIVERY_DOC_DATE = trim($_POST["DELIVERY_DOC_DATE"]);
                
                if ($arOldOrder["ALLOW_DELIVERY"] != $ALLOW_DELIVERY) {
                    $bUserCanDeliverOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "D", $GLOBALS["USER"]->GetUserGroupArray());
                    $errorMessageTmp = "";
                    
                    if (!$bUserCanDeliverOrder) {
                        $errorMessageTmp .= GetMessage("SOD_NO_PERMS2DELIV").". ";
                    }
                    if (strlen($errorMessageTmp) <= 0) {
                        $arAdditionalFields = array(
                            "DELIVERY_DOC_NUM" => ((strlen($DELIVERY_DOC_NUM) > 0) ? $DELIVERY_DOC_NUM : False),
                            "DELIVERY_DOC_DATE" => ((strlen($DELIVERY_DOC_DATE) > 0) ? $DELIVERY_DOC_DATE : False)
                        );

                        if (!CSaleOrder::DeliverOrder($ID, $ALLOW_DELIVERY, 0, $arAdditionalFields)) {
                            if ($ex = $APPLICATION->GetException()) {
                                if ($ex->GetID() != "ALREADY_FLAG") {
                                    $errorMessageTmp .= $ex->GetString();
                                }
                            } else {
                                $errorMessageTmp .= GetMessage("ERROR_DELIVERY_ORDER").". ";
                            }
                        }
                    }
                    
                    if ($errorMessageTmp != "") {
                        $arErrors[] = $errorMessageTmp;
                    }
                } else {
                    if($arOldOrder["DELIVERY_DOC_NUM"] != $DELIVERY_DOC_NUM)
                        $arOrder2Update["DELIVERY_DOC_NUM"] = ((strlen($DELIVERY_DOC_NUM) > 0) ? $DELIVERY_DOC_NUM : False);
                    if($arOldOrder["DELIVERY_DOC_DATE"] != $DELIVERY_DOC_DATE)
                        $arOrder2Update["DELIVERY_DOC_DATE"] = ((strlen($DELIVERY_DOC_DATE) > 0) ? $DELIVERY_DOC_DATE : False);
                }
            }
            
            if ($ID > 0 && count($arErrors) <= 0) {
                if(!empty($arOrder2Update)) {
                    CSaleOrder::Update($ID, $arOrder2Update);
                }
            }

            if ($ID > 0 && count($arErrors) <= 0) {
                //profile saving
                $str_USER_ID = IntVal($str_USER_ID);

                if (isset($userProfileID)) {
                    CSaleOrderUserProps::DoSaveUserProfile($str_USER_ID, $userProfileID, $profileName, $str_PERSON_TYPE_ID, $arOrderPropsValues, $arErrors);
                }
                unset($user_profile);
                //send new order mail
                if ($OrderNewSendEmail) {
                    $strOrderList = "";
                    foreach ($arOrder["BASKET_ITEMS"] as $val) {
                        $strOrderList .= $val["NAME"]." - ".$val["QUANTITY"]." ".GetMessage("SOA_SHT").": ".SaleFormatCurrency($val["PRICE"], $BASE_LANG_CURRENCY);
                        $strOrderList .= "\n";
                    }

                    //send mail
                    $arFields = Array(
                        "ORDER_ID" => $ID,
                        "ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", $LID))),
                        "ORDER_USER" => $arUserEmail["PAYER_NAME"],
                        "PRICE" => SaleFormatCurrency($arOrder["PRICE"], $BASE_LANG_CURRENCY),
                        "BCC" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
                        "EMAIL" => $arUserEmail["USER_EMAIL"],
                        "ORDER_LIST" => $strOrderList,
                        "SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
                        "DELIVERY_PRICE" => $arOrder["DELIVERY_PRICE"],
                    );
                    $eventName = "SALE_NEW_ORDER";

                    $bSend = true;
                    $db_events = GetModuleEvents("sale", "OnOrderNewSendEmail");
                    while ($arEvent = $db_events->Fetch()) {
                        if (ExecuteModuleEventEx($arEvent, Array($ID, &$eventName, &$arFields))===false) {
                            $bSend = false;
                        }
                    }
                    if ($bSend) {
                        $event = new CEvent();
                        $event->Send($eventName, $LID, $arFields, "N");
                    }
                }
            } else {
                foreach ($arErrors as $val) {
                    $errorMessage .= $val["TEXT"]."<br>";
                }
            }
        } elseif (count($arErrors) > 0) {
            foreach ($arErrors as $val) {
                $errorMessage .= $val["TEXT"]."<br>";
            }
        } else {
            $errorMessage .= GetMessage("SOE_SAVE_ERROR")."<br>";
        }
    } //end if save
    
    unset($location);
    unset($BTN_SAVE_BUYER);
    unset($buyertypechange);
    unset($userId);
    unset($user_id);
    
    if (strlen($errorMessage) <= 0 && $ID > 0) {
        
        if ($crmMode) {
            CRMModeOutput($ID);
        }
        
        CSaleOrder::UnLock($ID);
        LocalRedirect("/bitrix/admin/linemedia.auto_sale_orders_list.php?lang=".LANG."&LID=".CUtil::JSEscape($LID));
        
        /*
        if (isset($save) && strlen($save) > 0) {
            CSaleOrder::UnLock($ID);
            LocalRedirect("/bitrix/admin/linemedia.auto_sale_orders_list.php?lang=".LANG."&LID=".CUtil::JSEscape($LID));
        }
        if (isset($apply) && strlen($apply) > 0) {
            LocalRedirect("/bitrix/admin/linemedia.auto_sale_order_new.php?lang=".LANG."&ID=".$ID."&LID=".CUtil::JSEscape($LID));
        }
        */
    }
    if (strlen($errorMessage) > 0) {
        $bVarsFromForm = true;
    }
}

if (!empty($dontsave)) {
    CSaleOrder::UnLock($ID);
    if ($crmMode) {
        CRMModeOutput($ID);
    }
    LocalRedirect("/bitrix/admin/linemedia.auto_sale_orders_list.php?lang=".LANG."&LID=".CUtil::JSEscape($LID).GetFilterParams("filter_", false));
}


/*
 * Удаление корзин перед созданием заказа.
 */
if (!isset($ORDER_AJAX) && $save_order_data != "Y") {
    CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());
}    


/*****************************************************************************/
/************** Processing of requests from the proxy ************************/
/*****************************************************************************/

/*
 * Пересчет заказа ajax.
 */
if (isset($ORDER_AJAX) && $ORDER_AJAX == "Y" && check_bitrix_sessid()) {
    
    
    /*
     * Показ корзины.
     */
    if (isset($_REQUEST['getcarts'])) {
        fGetBaskets();
        die();
    }
    
    
    /*
     * Проверка дополенний HTML сторонних модулей.
     */
    if (isset($_REQUEST['checkhtml'])) {
        $userID = (int) $_REQUEST['userID'];
         
        /*
         * Созаём событие "Отображение формы создания заказа"
         */
        $events = GetModuleEvents("linemedia.auto", "OnShowOrderCreateForm");
        while ($arEvent = $events->Fetch()) {
            $html .= ExecuteModuleEventEx($arEvent, array($userID));
        }
        echo $html;
        die();
    }
    
    /*
     * Удаление корзины.
     */
    if (isset($_REQUEST['delcart']) && !empty($_REQUEST['id'])) {
        $id = (int) $_REQUEST['id'];
        
        $result = CSaleBasket::Delete($id);
        
        echo ($result) ? ('ok') : ('fail');
        die();
    }
    
    
    /*
     * Изменение корзины.
     */
    if (isset($_REQUEST['editcart']) && !empty($_REQUEST['id'])) {
        $quantity = (int) $_REQUEST['quantity'];
        
        $basket = new CSaleBasket();
        
        $result = $basket->Update($id, array('QUANTITY' => $quantity));
        
        echo ($result) ? ('ok') : ('fail');
        die();
    }
    
    
    
    /*
     * Добавление корзины.
     */
    if (isset($_REQUEST['addcart']) && !empty($_REQUEST['cart'])) {
        
        /*
         * ID запчасти в локальной БД.
         */
        $part_id = (int) $_REQUEST['cart']['part_id'];
        $part = new LinemediaAutoPart($part_id);
        
        /*
         * Цены.
         */
        $price = (float) $_REQUEST['cart']['price'];
        
        /*
         * ID поставщика.
         * По нему можно также узнать, что запчасть лежит не в локальной БД, а в удалённом API.
         */
        $supplier_id = (string) $_REQUEST['cart']['supplier_id'];
        $supplier_id = ($supplier_id != '') ? $supplier_id : $part->get('supplier_id');
        
        /*
         * Количество к заказу.
         */
        $quantity = 1;
        
        /*
         * Дополнительные параметры.
         */
        $additional = array(
            'SITE_ID'       => $LID,
            'article'       => (string) $_REQUEST['cart']['article'],
            'brand_title'   => (string) $_REQUEST['cart']['brand_title'],
            'extra'         => (array) $_REQUEST['cart']['extra']
        );
        
        /*
         * Создаём новую запись в корзине.
         */
        $basket = new LinemediaAutoBasket();
        $basket_id = $basket->addItem($part_id, $supplier_id, $quantity, $price, $additional);
        
        echo intval($basket_id);
        die();
    }
    
    
    /*
     * Местоположение
     */
    if (isset($location) && !isset($product) && !isset($locationZip)) {
        $location = IntVal($location);
        $tmpLocation = "";
        
        ob_start();
        $GLOBALS["APPLICATION"]->IncludeComponent(
                'bitrix:sale.ajax.locations',
                '',
                array(
                    "SITE_ID" => $LID,
                    "AJAX_CALL" => "Y",
                    "COUNTRY_INPUT_NAME" => "ORDER_PROP_".$locid,
                    "REGION_INPUT_NAME" => "REGION_ORDER_PROP_".$locid,
                    "CITY_INPUT_NAME" => "CITY_ORDER_PROP_".$locid,
                    "CITY_OUT_LOCATION" => "Y",
                    "ALLOW_EMPTY_CITY" => "Y",
                    "LOCATION_VALUE" => $location,
                    "COUNTRY" => "",
                    "ONCITYCHANGE" => "fRecalProduct('', '', 'N');",
                ),
                null,
                array('HIDE_ICONS' => 'Y')
        );
        $tmpLocation = ob_get_contents();
        ob_end_clean();

        $arData = array();
        if (IntVal($locid) > 0) {
            $arData["status"] = "ok";
            $arData["prop_id"] = $locid;
            $arData["location"] = $tmpLocation;
        }
        $result = CUtil::PhpToJSObject($arData);

        CRMModeOutput($result);
    }

    /*
     * Изменение типа плательщика
     */
    if (isset($buyertypechange)) {
        if (!isset($ID) || $ID == "") $ID = "";
        if (!isset($paysystemid) || $paysystemid == "") $paysystemid = "";

        $arData = array();
        $arData["status"] = "ok";
        $arData["buyertype"] = fGetBuyerType($buyertypechange, $LID, $userId, $ID);
        $arData["buyerdelivery"] = fBuyerDelivery($buyertypechange, $paysystemid);
        $arLocation = fGetLocationID($buyertypechange);

        $arData["location_id"] = $arLocation["LOCATION_ID"];
        $arData["location_zip_id"] = $arLocation["LOCATION_ZIP_ID"];

        $result = CUtil::PhpToJSObject($arData);

        CRMModeOutput($result);
    }


    /*
     * get locationId for geting delivery
     */
    if (isset($persontypeid)) {
        $persontypeid = IntVal($persontypeid);

        $arData = array();
        $arLocation = fGetLocationID($persontypeid);

        $arData["location_id"] = $arLocation["LOCATION_ID"];
        $arData["location_zip_id"] = $arLocation["LOCATION_ZIP_ID"];

        $result = CUtil::PhpToJSObject($arData);

        CRMModeOutput($result);
    }

    /*
     * take a list profile and user busket
     */
    if (isset($userId) && isset($buyerType) && (!isset($profileDefault) || $profileDefault == "")) {
        $id = IntVal($id);
        $userId = IntVal($userId);
        $buyerType = IntVal($buyerType);
        $LID = trim($LID);
        $currency = trim($currency);

        $arFuserItems = CSaleUser::GetList(array("USER_ID" => $userId));
        $fuserId = $arFuserItems["ID"];
        $arData = array();
        $arErrors = array();

        $arData["status"] = "ok";
        $arData["userProfileSelect"] = fUserProfile($userId, $buyerType);
        $arData["userName"] = fGetUserName($userId);

        $arShoppingCart = CSaleBasket::DoGetUserShoppingCart($LID, $userId, $fuserId, $arErrors, array());
        $arShoppingCart = fDeleteDoubleProduct($arShoppingCart, array(), 'N');
        $arData["userBasket"] = fGetFormatedProduct($userId, $LID, $arShoppingCart, $currency, 'busket');

        $arViewed = array();
        $dbViewsList = CSaleViewedProduct::GetList(
                array("DATE_VISIT" => "DESC"),
                array("FUSER_ID" => $fuserId, ">PRICE" => 0, "!CURRENCY" => ""),
                false,
                array('nTopCount' => 10),
                array('ID', 'PRODUCT_ID', 'LID', 'MODULE', 'NAME', 'DETAIL_PAGE_URL', 'PRICE', 'CURRENCY', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
            );
        while ($arViews = $dbViewsList->Fetch()) {
            $arViewed[] = $arViews;
        }
        $arViewedResult = fDeleteDoubleProduct($arViewed, $arFilterRecomendet, 'N');
        $arData["viewed"] = fGetFormatedProduct($userId, $LID, $arViewedResult, $currency, 'viewed');
        
        $result = CUtil::PhpToJSObject($arData);

        CRMModeOutput($result);
    }

    /*
     * script autocomplite profile
     */
    if (isset($userId) && isset($buyerType) && isset($profileDefault)) {
        $userId = IntVal($userId);
        $buyerType = IntVal($buyerType);
        $profileDefault = IntVal($profileDefault);

        $userProfile = array();
        $userProfile = CSaleOrderUserProps::DoLoadProfiles($userId, $buyerType);
        if ($profileDefault != "" && $profileDefault != "0") {
            $arPropValuesTmp = $userProfile[$profileDefault]["VALUES"];
        }
        $dbVariants = CSaleOrderProps::GetList(
            array("SORT" => "ASC"),
            array(
                    "PERSON_TYPE_ID" => $buyerType,
                    "USER_PROPS" => "Y",
                    "ACTIVE" => "Y"
            )
        );
        while ($arVariants = $dbVariants->Fetch()) {
            if (isset($arPropValuesTmp[$arVariants["ID"]])) {
                $arPropValues[$arVariants["ID"]] = $arPropValuesTmp[$arVariants["ID"]];
            } else {
                $arPropValues[$arVariants["ID"]] = $arVariants["DEFAULT_VALUE"];
            }
            
            if ($arVariants["IS_EMAIL"] == "Y" || $arVariants["IS_PAYER"] == "Y") {
                if (strlen($arPropValues[$arVariants["ID"]]) <= 0 && IntVal($userId) > 0) {
                    $rsUser = CUser::GetByID($userId);
                    if ($arUser = $rsUser->Fetch()) {
                        if ($arVariants["IS_EMAIL"] == "Y") {
                            $arPropValues[$arVariants["ID"]] = $arUser["EMAIL"];
                        } else {
                            if (strlen($arUser["LAST_NAME"]) > 0)
                                $arPropValues[$arVariants["ID"]] .= $arUser["LAST_NAME"];
                            if (strlen($arUser["NAME"]) > 0)
                                $arPropValues[$arVariants["ID"]] .= " ".$arUser["NAME"];
                            if (strlen($arUser["SECOND_NAME"]) > 0 AND strlen($arUser["NAME"]) > 0)
                                $arPropValues[$arVariants["ID"]] .= " ".$arUser["SECOND_NAME"];
                        }
                    }
                }
            }

        }
        
        $scriptExec = "<script language=\"JavaScript\">";
        foreach ($arPropValues as $key => $val):
            $val = CUtil::JSEscape(htmlspecialcharsback($val));
            $scriptExec .= "var el = document.getElementById(\"ORDER_PROP_".$key."\");\n";
            $scriptExec .= "if(el)\n{\n";
            $scriptExec .= "var elType = el.getAttribute('type');\n";
            $scriptExec .= "if (elType == \"text\" || elType == \"textarea\" || elType == \"select\")\n";
            $scriptExec .= "{";
                $scriptExec .= "el.value = '".$val."';\n";
            $scriptExec .= "}";
            $scriptExec .= "else if (elType == \"location\")\n";
            $scriptExec .= "{";
            $scriptExec .= "BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_new.php', '".bitrix_sessid_get()."&ORDER_AJAX=Y&locid=".$key."&propID=".$buyerType."&LID=".CUtil::JSEscape($LID)."&location=".$val."', fLocationResult);\n";
            $scriptExec .= "}";
            $scriptExec .= "else if (elType == \"radio\")\n";
            $scriptExec .= "{";
                $scriptExec .= "elRadio = el.getElementsByTagName(\"input\");\n";
                $scriptExec .= "for (var i = 0; i < elRadio.length; i++)\n";
                $scriptExec .= "{";
                    $scriptExec .= "if (elRadio[i].value == '".$val."')\n";
                    $scriptExec .= "{";
                        $scriptExec .= "elRadio[i].checked = true;\n";
                    $scriptExec .= "}";
                    $scriptExec .= "else {";
                        $scriptExec .= "elRadio[i].checked = false;\n";
                    $scriptExec .= "}";
                $scriptExec .= "}";
            $scriptExec .= "}";
            $scriptExec .= "else if (elType == \"checkbox\")\n";
            $scriptExec .= "{";
                if ($val == "Y") {
                    $scriptExec .= "el.checked = true;\n";
                } else {
                    $scriptExec .= "el.checked = false;\n";
                }
            $scriptExec .= "}";
            $scriptExec .= "else if (elType == \"multyselect\")\n";
            $scriptExec .= "{";
                if ($val != "") {
                    $selectedVal = explode(",", $val);
                    foreach ($selectedVal as $k => $v):
                        $scriptExec .= "el.value = '".trim($v)."';\n";
                    endforeach;
                } else {
                    $scriptExec .= "el.selectedIndex = -1;";
                }
            $scriptExec .= "}\n";
            $scriptExec .= "}\n";
        endforeach;
        $scriptExec .= "fRecalProduct('', '', 'N');</script>";

        echo $scriptExec;
        die();
    }

    
    /*
     * get more busket
     */
    if (isset($getmorebasket) && $getmorebasket == "Y") {
        $userId = IntVal($userId);
        $arFuserItems = CSaleUser::GetList(array("USER_ID" => intval($userId)));
        $fuserId = $arFuserItems["ID"];
        $arErrors = array();

        $arOrderProduct = CUtil::JsObjectToPhp($arProduct);
        $arShoppingCart = CSaleBasket::DoGetUserShoppingCart($LID, $userId, $fuserId, $arErrors, array());
        $arShoppingCart = fDeleteDoubleProduct($arShoppingCart, $arOrderProduct, $showAll);

        $result = fGetFormatedProduct($userId, $LID, $arShoppingCart, $CURRENCY, 'busket');

        CRMModeOutput($result);
    }
    
    
    /*
     * get more viewed
     */
    if (isset($getmoreviewed) && $getmoreviewed == "Y") {
        $userId = IntVal($userId);
        $arFuserItems = CSaleUser::GetList(array("USER_ID" => intval($userId)));
        $fuserId = $arFuserItems["ID"];
        $arErrors = array();

        $arOrderProduct = CUtil::JsObjectToPhp($arProduct);
        $arViewed = array();
        $dbViewsList = CSaleViewedProduct::GetList(
                array("DATE_VISIT"=>"DESC"),
                array("FUSER_ID" => $fuserId, ">PRICE" => 0, "!CURRENCY" => ""),
                false,
                array('nTopCount' => 10),
                array('ID', 'PRODUCT_ID', 'LID', 'MODULE', 'NAME', 'DETAIL_PAGE_URL', 'PRICE', 'CURRENCY', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
            );
        while ($arViews = $dbViewsList->Fetch()) {
            $arViewed[] = $arViews;
        }
        $arViewedCart = fDeleteDoubleProduct($arViewed, $arOrderProduct, $showAll);

        $result = fGetFormatedProduct($userId, $LID, $arViewedCart, $CURRENCY, 'viewed');

        CRMModeOutput($result);
    }

    
    /*
     * Пересчет заказа.
     */
    if (isset($product) && isset($user_id)) {
        
        $result = "";
        $id = IntVal($id);
        $userId = IntVal($userId);
        $paySystemId = IntVal($paySystemId);
        $buyerTypeId = IntVal($buyerTypeId);
        $location = IntVal($location);
        $locationID = IntVal($locationID);
        $locationZip = IntVal($locationZip);
        $locationZipID = IntVal($locationZipID);
        $WEIGHT_UNIT = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_unit', "", $LID));
        $WEIGHT_KOEF = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_koef', 1, $LID));
        $arDelivery = array();
        $recomMore = ($recomMore == "Y") ? "Y" : "N";
        
        $currency = CCurrency::GetBaseCurrency();
        
        $arOrderProducts = CUtil::JsObjectToPhp($product);
        
        $arCupon = fGetCupon($cupon);
        $arOrderProductPrice = fGetUserShoppingCart($arOrderProduct, $LID, $currency);
        
        $arOrderPropsValues = array();
        if ($locationID != "" && $location != "") {
            $arOrderPropsValues[$locationID] = $location;
        }
        if ($locationZipID != "" && $locationZip != "") {
            $arOrderPropsValues[$locationZipID] = $locationZip;
        }
        
        foreach ((array) $arOrderProducts as $basketID => $arOrderProduct) {
            $basket = new CSaleBasket();
            $basket->Update($basketID, array('QUANTITY' => (int) $arOrderProduct['quantity']));
        }
        
        
        // enable/disable town for location
        $dbProperties = CSaleOrderProps::GetList(
                array("SORT" => "ASC"),
                array("ID" => $locationID, "ACTIVE" => "Y", ">INPUT_FIELD_LOCATION" => 0),
                false,
                false,
                array("ID", "INPUT_FIELD_LOCATION")
            );
        if ($arProperties = $dbProperties->Fetch()) {
            $bDeleteFieldLocationID = $arProperties["INPUT_FIELD_LOCATION"];
        }
        $rsLocationsList = CSaleLocation::GetList(
                        array(),
                        array("ID" => $location),
                        false,
                        false,
                        array("ID", "CITY_ID")
                    );
        $arCity = $rsLocationsList->GetNext();
        if (IntVal($arCity["CITY_ID"]) <= 0) {
            $bDeleteFieldLocation = "Y";
        } else {
            $bDeleteFieldLocation = "N";
        }
        
        $orderDiscount = 0;
        $arData = array();
        $arFilterRecomendet = array();
        $priceBaseTotal = 0;
        
        
        /*
         * Пересчет корзин
         */
        $basket = new LinemediaAutoBasket();
        
        $arBaskets = (array) $basket->getOrderedBaskets();
        
        
        /*
         * Пересчет данных заказа.
         */
        $arOrder = CSaleOrder::DoCalculateOrder(
            $LID,
            $user_id,
            $arBaskets,
            $buyerTypeId,
            $arOrderPropsValues,
            $deliveryId,
            $paySystemId,
            array(),
            $arErrors,
            $arWarnings
        );
        
        foreach ($arBaskets as $arBasket) {
            $arCurFormat = CCurrencyLang::GetCurrencyFormat($arBasket['CURRENCY']);
            $priceBase = $arBasket['PRICE'] + $arBasket['DISCOUNT_PRICE'];
            $priceDiscountPercent = intval(($arBasket['DISCOUNT_PRICE'] * 100) / $priceBase);
            
            $arData[$arBasket["ID"]]["PRICE_BASE"] = CurrencyFormatNumber($priceBase, CCurrency::GetBaseCurrency());
            $arData[$arBasket["ID"]]["DISCOUNT_REPCENT"] = $priceDiscountPercent;
            $arData[$arBasket["ID"]]["DISCOUNT_PRICE"] = $arBasket["DISCOUNT_PRICE"];
            $arData[$arBasket["ID"]]["PRICE"] = $arBasket["PRICE"];
            $arData[$arBasket["ID"]]["PRICE_DISPLAY"] = CurrencyFormatNumber($arBasket["PRICE"], $arBasket["CURRENCY"]);
            $arData[$arBasket["ID"]]["QUANTITY"] = $arBasket["QUANTITY"];
            $arData[$arBasket["ID"]]["DISCOUNT_PRICE_DISPLAY"] = CurrencyFormatNumber($arBasket["DISCOUNT_PRICE"], $arBasket["CURRENCY"]);
            $arData[$arBasket["ID"]]["SUMMA_DISPLAY"] = CurrencyFormatNumber(($arBasket["PRICE"] * $arBasket["QUANTITY"]), $arBasket["CURRENCY"]);
            $arData[$arBasket["ID"]]["CURRENCY"] = $arBasket["CURRENCY"];
            
            $balance = 0;
            if ($arBasket["MODULE"] == "catalog" && CModule::IncludeModule('catalog')) {
                $ar_res = CCatalogProduct::GetByID($arBasket["PRODUCT_ID"]);
                $balance = floatval($ar_res["QUANTITY"]);
            }
            $arData[$arBasket['ID']]['BALANCE'] = $balance;
            $orderDiscount += $arBasket['DISCOUNT_PRICE'] * $arBasket['QUANTITY'];
            $arFilterRecomendet []= $arBasket['ID'];
            
            $orderWeight += (float) $arBasket['WEIGHT'] * $arBasket['QUANTITY'];
            $priceBaseTotal += ($arBasket['PRICE'] * $arBasket['QUANTITY']);
        }
        
        $arData[0]["ORDER_ERROR"] = "N";
        
        $arOrder["ORDER_WEIGHT"] = $orderWeight;
        $arOrder["PRICE"] = $priceBaseTotal;
        
        // Изменение цены доставки
        $deliveryChangePrice = false;
        if ($delpricechange == "Y") {
            $arOrder["PRICE"] = ($arOrder["PRICE"] - $arOrder["DELIVERY_PRICE"]) + $deliveryPrice;
            $arOrder["DELIVERY_PRICE"] = $deliveryPrice;
            $arOrder["PRICE_DELIVERY"] = $deliveryPrice;
            $deliveryChangePrice = true;
            $arDelivery["DELIVERY_DEFAULT_PRICE"] = $deliveryPrice;
            $arDelivery["DELIVERY_DEFAULT"] = "";
            $arDelivery["DELIVERY_DEFAULT_ERR"] = "";
            $arDelivery["DELIVERY_DEFAULT_DESCRIPTION"] = "";
            $arData[0]["DELIVERY"] = "";
        } else {
            $arDelivery = fGetDelivery($location, $locationZip, $arOrder["ORDER_WEIGHT"], $arOrder["ORDER_PRICE"], $currency, $LID, $deliveryId);
        }
        
        $arData[0]["ORDER_ID"] = $id;
        $arData[0]["DELIVERY"] = $arDelivery["DELIVERY"];
        if (isset($arOrder["PRICE_DELIVERY"]) && floatval($arOrder["PRICE_DELIVERY"]) > 0) {
            $arData[0]["DELIVERY_PRICE"] = $arOrder["PRICE_DELIVERY"];
            $arData[0]["DELIVERY_PRICE_FORMAT"] = SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $currency);
        } else {
            if ($arDelivery["CURRRENCY"] != $currency) {
                $arDelivery["DELIVERY_DEFAULT_PRICE"] = roundEx(CCurrencyRates::ConvertCurrency($arDelivery["DELIVERY_DEFAULT_PRICE"], $arDelivery["CURRENCY"], $currency), SALE_VALUE_PRECISION);
            }
            $arData[0]["DELIVERY_PRICE"] = $arDelivery["DELIVERY_DEFAULT_PRICE"];
            $arData[0]["DELIVERY_PRICE_FORMAT"] = SaleFormatCurrency($arDelivery["DELIVERY_DEFAULT_PRICE"], $currency);
        }
        $arData[0]["DELIVERY_DEFAULT"] = $arDelivery["DELIVERY_DEFAULT"];

        if (strlen($arDelivery["DELIVERY_DEFAULT_ERR"]) > 0) {
            $arData[0]["DELIVERY_DESCRIPTION"] = $arDelivery["DELIVERY_DEFAULT_ERR"];
            $arData[0]["ORDER_ERROR"] = "Y";
        } else {
            $arData[0]["DELIVERY_DESCRIPTION"] = $arDelivery["DELIVERY_DEFAULT_DESCRIPTION"];
        }
        
        if (!isset($arOrder["ORDER_PRICE"]) || $arOrder["ORDER_PRICE"] == "" ) {
            $arOrder["ORDER_PRICE"] = 0;
        }
        if (!isset($arOrder["PRICE"]) || $arOrder["PRICE"] == "") {
            $arOrder["PRICE"] = 0;
        }
        if (!isset($arOrder["DISCOUNT_VALUE"]) || $arOrder["DISCOUNT_VALUE"] == "") {
            $arOrder["DISCOUNT_VALUE"] = 0;
        }
        
        $arData[0]["CURRENCY_FORMAT"] = trim(str_replace("#", '', $arCurFormat["FORMAT_STRING"]));
        $arData[0]["PRICE_TOTAL"] = SaleFormatCurrency($priceBaseTotal, $currency);
        $arData[0]["PRICE_WITH_DISCOUNT_FORMAT"] = SaleFormatCurrency($arOrder["ORDER_PRICE"], $currency);
        $arData[0]["PRICE_WITH_DISCOUNT"] = roundEx($arOrder["ORDER_PRICE"]);
        $arData[0]["PRICE_TAX"] = SaleFormatCurrency(DoubleVal($arOrder["TAX_VALUE"]), $currency);
        $arData[0]["PRICE_WEIGHT_FORMAT"] = roundEx(DoubleVal($arOrder["ORDER_WEIGHT"] / $WEIGHT_KOEF), SALE_VALUE_PRECISION)." ".$WEIGHT_UNIT;
        $arData[0]["PRICE_WEIGHT"] = roundEx(DoubleVal($arOrder["ORDER_WEIGHT"]/$WEIGHT_KOEF), SALE_VALUE_PRECISION);
        $arData[0]["PRICE_TO_PAY"] = SaleFormatCurrency($arOrder["PRICE"], $currency);
        $arData[0]["PRICE_TO_PAY_DEFAULT"] = FloatVal($arOrder["PRICE"]);
        $tmpPay = fGetPayFromAccount($user_id, $currency);
        $arData[0]["PAY_ACCOUNT"] = $tmpPay["PAY_MESSAGE"];
        $arData[0]["PAY_ACCOUNT_CAN_BUY"] = $tmpPay["PAY_BUDGET"];
        $arData[0]["PAY_ACCOUNT_DEFAULT"] = FloatVal($tmpPay["CURRENT_BUDGET"]);
        $arData[0]["DISCOUNT_VALUE"] = $arOrder["DISCOUNT_VALUE"];
        $arData[0]["DISCOUNT_VALUE_FORMATED"] = SaleFormatCurrency($arOrder["DISCOUNT_VALUE"], $currency);
        $arData[0]["DISCOUNT_PRODUCT_VALUE"] = $orderDiscount;
        $arData[0]["LOCATION_TOWN_ID"] = IntVal($bDeleteFieldLocationID);
        $arData[0]["LOCATION_TOWN_ENABLE"] = $bDeleteFieldLocation;

        // recomendet
        $recomendetProduct = "";
        $arProductIdInBasket = array();
        $arData[0]["RECOMMENDET_CALC"] = "N";
        if ($recommendet == "Y") {
            $arRecomendet = CSaleProduct::GetRecommendetProduct($userId, $LID, $arFilterRecomendet);
            $arRecomendetProduct = fDeleteDoubleProduct($arRecomendet, $arFilterRecomendet, $recomMore);

            $recomendetProduct = fGetFormatedProduct($user_id, $LID, $arRecomendetProduct, $currency, 'recom');
            $arData[0]["RECOMMENDET_CALC"] = "Y";
        }
        $arData[0]["RECOMMENDET_PRODUCT"] = $recomendetProduct;

        $result = CUtil::PhpToJSObject($arData);

        CRMModeOutput($result);
    }
}//end ORDER_AJAX=Y


/*****************************************************************************/
/**************************** FORM ORDER *************************************/
/*****************************************************************************/

//date order
$str_DATE_UPDATE = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", $lang)));
$str_DATE_INSERT = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", $lang)));
$str_PRICE = 0;
$str_DISCOUNT_VALUE = 0;

if (isset($ID) && $ID > 0) {
    $dbOrder = CSaleOrder::GetList(
        array("ID" => "DESC"),
        array("ID" => $ID),
        false,
        false,
        array()
    );
    if (!($arOrderOldTmp = $dbOrder->ExtractFields("str_"))) {
        LocalRedirect("linemedia.auto_sale_orders_list.php?lang=".LANG.GetFilterParams("filter_", false));
    }
    $LID = $str_LID;
}
if (!isset($str_TAX_VALUE) || $str_TAX_VALUE == "") {
    $str_TAX_VALUE = 0;
}

if (IntVal($str_PERSON_TYPE_ID) <= 0) {
    $str_PERSON_TYPE_ID = 0;
    $arFilter = array();
    $arFilter["ACTIVE"] = "Y";
    if (strlen($LID) > 0) {
        $arFilter["LID"] = $LID;
    }
    $dbPersonType = CSalePersonType::GetList(array("ID" => "ASC"), $arFilter);
    if ($arPersonType = $dbPersonType->Fetch()) {
        $str_PERSON_TYPE_ID = $arPersonType["ID"];
    }
}

$arFuserItems = CSaleUser::GetList(array("USER_ID" => intval($str_USER_ID)));
$FUSER_ID = $arFuserItems["ID"];

/*
 * form select site
 */
if ((!isset($LID) || $LID == "") && (defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE == 1)) {
    $arSitesShop = array();
    $arSitesTmp = array();
    $rsSites = CSite::GetList($by="id", $order="asc", Array("ACTIVE" => "Y"));
    while ($arSite = $rsSites->Fetch()) {
        $site = COption::GetOptionString("sale", "SHOP_SITE_".$arSite["ID"], "");
        if ($arSite['ID'] == $site) {
            $arSitesShop []= array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
        }
        $arSitesTmp []= array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
    }
    
    $rsCount = count($arSitesShop);
    if ($rsCount <= 0) {
        $arSitesShop = $arSitesTmp;
        $rsCount = count($arSitesShop);
    }
    
    if ($rsCount === 1) {
        $LID = $arSitesShop[0]['ID'];
    } elseif ($rsCount > 1) {
?>
        <div id="select_lid">
            <form action="" name="select_lid">
                <div style="margin:10px auto;text-align:center;">
                    <div><?=GetMessage("NEWO_SELECT_SITE")?></div><br />
                    <select name="LID" onChange="fLidChange(this);">
                        <option selected="selected" value=""><?= GetMessage("NEWO_SELECT_SITE") ?></option>
                        <? foreach ($arSitesShop as $key => $val) { ?>
                            <option value="<?= $val["ID"] ?>"><?= $val["NAME"]." (".$val["ID"].")"; ?></option>
                        <? } ?>
                    </select>
                </div>
                <script type="text/javascript">
                    function fLidChange(el)
                    {
                        BX.showWait();
                        BX.ajax.post("/bitrix/admin/linemedia.auto_sale_order_new.php", "<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&lang=<?=LANGUAGE_ID?>&LID=" + el.value, fLidChangeResult);
                    }
                    
                    function fLidChangeResult(result)
                    {
                        fLidChangeDisableButtons(false);
                        BX.closeWait();
                        if (result.length > 0) {
                            document.getElementById("select_lid").innerHTML = result;
                        }
                    }
                    
                    function fLidChangeDisableButtons(val)
                    {
                        var btn = document.getElementById("btn-save");
                        if (btn) {
                            btn.disabled = val;
                        }
                        btn = document.getElementById("btn-cancel");
                        if (btn) {
                            btn.disabled = val;
                        }
                    }
                    BX.ready(function(){ fLidChangeDisableButtons(true); });
                </script>
            </form>
        </div>
<?
        die();
    } else {
        echo "<div style=\"margin:10px auto;text-align:center;\">";
        echo GetMessage("NEWO_NO_SITE_SELECT");
        echo "<div>";
        die();
    }
}

if (!isset($str_CURRENCY) || $str_CURRENCY == "") {
    $str_CURRENCY = CSaleLang::GetLangCurrency($LID);
}
if (isset($ID) && $ID > 0) {
    $title = GetMessage("SOEN_TAB_ORDER_TITLE");
} else {
    $title = GetMessage("SOEN_TAB_ORDER_NEW_TITLE");
}
$aTabs = array(
    array("DIV" => "edit1", "TAB" => GetMessage("SOEN_TAB_ORDER"), "ICON" => "sale", "TITLE" => $title),
);
$tabControl = new CAdminForm("form_order_buyers", $aTabs, false, true);

if (isset($ID) && $ID > 0) {
    $APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("NEWO_TITLE_EDIT")));
} elseif (isset($LID) && $LID != "") {
    $siteName = $LID;
    $dbSite = CSite::GetByID($LID);
    if ($arSite = $dbSite->Fetch()) {
        $siteName = $arSite["NAME"]." (".$LID.")";
    }
    $APPLICATION->SetTitle(str_replace("#LID#", $siteName, GetMessage("NEWO_TITLE_")));
} else {
    $APPLICATION->SetTitle(GetMessage("NEWO_TITLE_DEFAULT"));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array();
$aMenu = array(
    array(
        "ICON" => "btn_list",
        "TEXT" => GetMessage("SOE_TO_LIST"),
        "LINK" => "/bitrix/admin/linemedia.auto_sale_orders_list.php?lang=".LANGUAGE_ID
    )
);
$link = urlencode(DeleteParam(array("mode")));
$link = urlencode($GLOBALS["APPLICATION"]->GetCurPage())."?mode=settings".($link <> "" ? "&".$link: "");

$bUserCanViewOrder = CSaleOrder::CanUserViewOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
$bUserCanDeleteOrder = CSaleOrder::CanUserDeleteOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
$bUserCanCancelOrder = CSaleOrder::CanUserCancelOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
$bUserCanPayOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "P", $GLOBALS["USER"]->GetUserGroupArray());
$bUserCanDeliverOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "D", $GLOBALS["USER"]->GetUserGroupArray());

if ($bUserCanViewOrder && $ID > 0) {
    $aMenu[] = array(
        "TEXT" => GetMessage("NEWO_DETAIL"),
        "TITLE"=>GetMessage("NEWO_DETAIL_TITLE"),
        "LINK" => "/bitrix/admin/linemedia.auto_sale_order_detail.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
    );
}

if ($ID > 0) {
    $aMenu[] = array(
        "TEXT" => GetMessage("NEWO_TO_PRINT"),
        "TITLE"=>GetMessage("NEWO_TO_PRINT_TITLE"),
        "LINK" => "/bitrix/admin/linemedia.auto_sale_order_print.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
    );
}

if (($saleModulePermissions == "W" || $str_PAYED != "Y") && $bUserCanDeleteOrder && $ID > 0) {
    $aMenu[] = array(
            "TEXT" => GetMessage("NEWO_ORDER_DELETE"),
            "TITLE"=>GetMessage("NEWO_ORDER_DELETE_TITLE"),
            "LINK" => "javascript:if(confirm('".GetMessage("NEWO_CONFIRM_DEL_MESSAGE")."')) window.location='linemedia.auto_sale_orders_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get().urlencode(GetFilterParams("filter_"))."'",
            "WARNING" => "Y"
        );
}

//delete context menu for remote query
if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1) {
    $context = new CAdminContextMenu($aMenu);
    $context->Show();
}



/*
 * Созаём событие "Отображение формы создания заказа"
 */
$events = GetModuleEvents("linemedia.auto", "OnShowOrderCreateForm");
while ($arEvent = $events->Fetch()) {
    $html .= ExecuteModuleEventEx($arEvent, array($str_USER_ID));
}


/*
 * Событие перед выводом формы для создания заказа.
 */
$events = GetModuleEvents("linemedia.auto", "OnAdminCreateOrderFormShow");
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array($arUser['ID']));
}

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/

CAdminMessage::ShowMessage($errorMessage);

echo "<div id=\"form_content\">";
$tabControl->BeginEpilogContent();

if (isset($_REQUEST["user_id"]) && IntVal($_REQUEST["user_id"]) > 0) {
    $str_USER_ID = IntVal($_REQUEST["user_id"]);
}

/*
 * Список сайтов.
 */
$rsSites = CSite::GetList($by='SORT', $order='ASC', array('ACTIVE' => 'Y'));
$arSites = array();
while ($arSite = $rsSites->Fetch()) {
    $arSites []= array('ID' => $arSite['ID'], 'NAME' => $arSite['NAME']);
}

?>
<?= bitrix_sessid_post(); ?>
<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
<!--input type="hidden" name="LID" value="<?= htmlspecialcharsbx($LID) ?>" -->
<input type="hidden" name="ID" value="<?= $ID ?>">
<input type="hidden" name="save_order_data" value="Y">
<? if (isset($_REQUEST["user_id"]) && IntVal($_REQUEST["user_id"]) > 0) { ?>
    <input type="hidden" name="user_id" value="<?=IntVal($_REQUEST["user_id"])?>">
<? } ?>
<?
if (isset($_REQUEST["product"]) && count($_REQUEST["product"]) > 0) {
    foreach ($_REQUEST["product"] as $val) {
        if (intval($val) > 0) {
            ?><input type="hidden" name="product[]" value="<?= intval($val) ?>"><?
        }
    }
}

$tabControl->EndEpilogContent();

if (!isset($LID) || $LID == "") {
    foreach ($arSites as $arSite) {
        if ($arSite['DEF'] == 'Y') {
            $LID = $arSite['ID'];
        }
    }
}

$urlForm = "";
if (isset($ID) && $ID != "") {
    $urlForm = "&ID=".$ID."&LID=".CUtil::JSEscape($LID);
    CSaleOrder::Lock($ID);
}
$tabControl->Begin(array(
    'FORM_ACTION' => $APPLICATION->GetCurPage().'?lang='.LANG.$urlForm
));

//TAB ORDER
$tabControl->BeginNextFormTab();

$tabControl->AddSection("NEWO_TITLE_STATUS", GetMessage("NEWO_TITLE_STATUS"));

$tabControl->BeginCustomField("ORDER_STATUS", GetMessage("SOE_STATUS"), true);
?>
    <tr class="adm-detail-required-field">
        <td width="40%"><?= GetMessage("SOE_SITE") ?>:</td>
        <td width="60%">
            <script>
                function fChangeSite(el)
                {
                    var id = el.value;
                    
                    document.location = '<?= $GLOBALS['APPLICATION']->GetCurPage() ?>?lang=<?= LANG ?>&LID=' + id;
                }
            </script>
            <select name="LID" id="select-lid-id" onchange="javascript:fChangeSite(this);">
                <? foreach ($arSites as $arSite) { ?>
                    <option value="<?= $arSite['ID'] ?>" <?= ($arSite['ID'] == $LID || $arSite['DEF'] == 'Y') ? ('selected="selected"') : ('') ?>>
                        <?= $arSite['NAME'] ?>
                    </option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr class="adm-detail-required-field">
        <td width="40%"><?=GetMessage("SOE_STATUS")?>:</td>
        <td width="60%">
            <?
            $arFilter = array("LID" => LANGUAGE_ID);
            $arGroupByTmp = false;

            if ($saleModulePermissions < "W") {
                $arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
                $arFilter["PERM_STATUS_FROM"] = "Y";
                if (strlen($str_STATUS_ID) > 0)
                    $arFilter["ID"] = $str_STATUS_ID;
                $arGroupByTmp = array("ID", "NAME", "MAX" => "PERM_STATUS_FROM");
            }
            $dbStatusList = CSaleStatus::GetList(
                array(),
                $arFilter,
                $arGroupByTmp,
                false,
                array("ID", "NAME", "SORT")
            );

            if ($dbStatusList->GetNext()) {
            ?>
                <select name="STATUS_ID" id="STATUS_ID">
                    <?
                    $arFilter = array("LID" => LANG);
                    $arGroupByTmp = false;
                    if ($saleModulePermissions < "W") {
                        $arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
                        $arFilter["PERM_STATUS"] = "Y";
                    }
                    $dbStatusListTmp = CSaleStatus::GetList(
                        array("SORT" => "ASC"),
                        $arFilter,
                        $arGroupByTmp,
                        false,
                        array("ID", "NAME", "SORT")
                    );
                    while ($arStatusListTmp = $dbStatusListTmp->GetNext()) {
                        ?><option value="<?= $arStatusListTmp["ID"] ?>"<?if ($arStatusListTmp["ID"]==$str_STATUS_ID) echo " selected"?>>[<?echo $arStatusListTmp["ID"] ?>] <?echo $arStatusListTmp["NAME"] ?></option><?
                    }
                    ?>
                </select>
                <?
            } else {
                $arStatusLand = CSaleStatus::GetLangByID($str_STATUS_ID, LANGUAGE_ID);
                echo htmlspecialcharsEx("[".$str_STATUS_ID."] ".$arStatusLand["NAME"]);
            }
            ?>
            <input type="hidden" name="user_id" id="user_id" value="<?=$str_USER_ID?>" onChange="fUserGetProfile(this);" >
        </td>
    </tr>
<?
$tabControl->EndCustomField("ORDER_STATUS");

if (IntVal($ID) > 0) {
    $arSitesShop = array();
    $rsSites = CSite::GetList($by="id", $order="asc", Array("ACTIVE" => "Y"));
    while ($arSite = $rsSites->Fetch()) {
        $site = COption::GetOptionString("sale", "SHOP_SITE_".$arSite["ID"], "");
        if ($arSite["ID"] == $site) {
            $arSitesShop[$arSite["ID"]] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
        }
    }

    if (count($arSitesShop) > 1) {
        $tabControl->BeginCustomField("ORDER_SITE", GetMessage("ORDER_SITE"), true);
        ?>
        <tr>
            <td width="40%">
                <?= GetMessage("ORDER_SITE") ?>:
            </td>
            <td width="60%"><?=htmlspecialcharsbx($arSitesShop[$str_LID]["NAME"])." (".$str_LID.")"?>
            </td>
        </tr>
        <?
        $tabControl->EndCustomField("ORDER_SITE");
    }

    $tabControl->BeginCustomField("ORDER_CANCEL", GetMessage("SOE_CANCELED"), true);
    ?>
    <tr>
        <td width="40%">
            <?= GetMessage("SOE_CANCELED") ?>:
        </td>
        <td width="60%">
            <input type="checkbox"<?if (!$bUserCanCancelOrder) echo " disabled";?> name="CANCELED" id="CANCELED" value="Y"<?if ($str_CANCELED == "Y") echo " checked";?>>&nbsp;<label for="CANCELED"><?=GetMessage("SO_YES")?></label>
            <? if (strlen($str_DATE_CANCELED) > 0) {
                echo "&nbsp;(".$str_DATE_CANCELED.")";
            }
            ?>
        </td>
    </tr>
    <tr>
        <td width="40%" valign="top">
            <?= GetMessage("SOE_CANCEL_REASON") ?>:
        </td>
        <td width="60%" valign="top">
            <textarea name="REASON_CANCELED"<?if (!$bUserCanCancelOrder) echo " disabled";?> rows="2" cols="40"><?= $str_REASON_CANCELED ?></textarea>
        </td>
    </tr>
    <?
    $tabControl->EndCustomField("ORDER_CANCEL");
}

$tabControl->AddSection("NEWO_TITLE_BUYER", GetMessage("NEWO_TITLE_BUYER"));

$tabControl->BeginCustomField("NEWO_BUYER", GetMessage("NEWO_BUYER"), true);
?>

<?if ($ID <= 0):?>
<tr>
    <td width="40%" align="right">
        <a onClick="fButtonCurrent('btnBuyerNew')" href="javascript:void(0);" id="btnBuyerNew" class="adm-btn<?if ($_REQUEST["btnTypeBuyer"] == 'btnBuyerNew' || !isset($_REQUEST["btnTypeBuyer"])) echo ' adm-btn-active';?>"><?=GetMessage("NEWO_BUYER_NEW")?></a>
    </td>
    <td width="60%" align="left"><a onClick="fButtonCurrent('btnBuyerExist')" href="javascript:void(0);" id="btnBuyerExist" class="adm-btn<? if ($_REQUEST["btnTypeBuyer"] == 'btnBuyerExist') echo ' adm-btn-active';?>"><?=GetMessage("NEWO_BUYER_SELECT")?></a>
        <?
        $typeBuyerTmp = "btnBuyerNew";
        if ($bVarsFromForm && isset($_REQUEST["btnTypeBuyer"]))
            $typeBuyerTmp = htmlspecialcharsbx($_REQUEST["btnTypeBuyer"]);
        ?>

        <input type="hidden" name="btnTypeBuyer" id="btnTypeBuyer" value="<?=$typeBuyerTmp?>" />
    </td>
</tr>
<?endif?>

<tr>
    <td id="buyer_type_change" colspan="2">
        <?= fGetBuyerType($str_PERSON_TYPE_ID, $LID, $str_USER_ID, $ID, $bVarsFromForm); ?>

        <script>
        
        /*
         * Смена пользователя.
         */
        function fButtonCurrent(el)
        {
            if (el == 'btnBuyerNew') {
                BX.removeClass(BX("btnBuyerExist"), 'adm-btn-active');
                BX.addClass(BX("btnBuyerNew"), 'adm-btn-active');

                BX("btnBuyerExistField").style.display = 'none';
                BX("btnBuyerNewField").style.display = 'table-row';
                BX("btnTypeBuyer").value = 'btnBuyerNew';
                BX("buyer_profile_display").style.display = 'none';

                if (BX("BREAK_NAME")) {
                    BX("BREAK_NAME").style.display = 'block';
                    BX("NO_BREAK_NAME").style.display = 'none';
                }
            }
            else if (el == 'btnBuyerExist' || el == 'btnBuyerExistRemote')
            {
                BX.addClass(BX("btnBuyerExist"), 'adm-btn-active');
                BX.removeClass(BX("btnBuyerNew"), 'adm-btn-active');

                BX("btnBuyerExistField").style.display = 'table-row';
                if (BX("btnBuyerNewField")) {
                    BX("btnBuyerNewField").style.display = 'none';
                }
                if (BX("btnTypeBuyer")) {
                    BX("btnTypeBuyer").value = 'btnBuyerExist';
                }
                if (BX("BREAK_NAME")) {
                    BX("BREAK_NAME").style.display = 'none';
                    BX("NO_BREAK_NAME").style.display = 'block';
                }
                
                if (el == 'btnBuyerExist') {
                    window.open('/bitrix/admin/user_search.php?lang=<?= $lang ?>&FN=form_order_buyers_form&FC=user_id', '', 'scrollbars=yes,resizable=yes,width=840,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 840)/2-5));
                }
            }
        }

        var orderID = '<?= $ID ?>';
        var orderPaySysyemID = '<?= $str_PAY_SYSTEM_ID ?>';

        /*
         * Смена типа плательщика
         */
        function fBuyerChangeType(el)
        {
            var userId = "";

            if (BX("user_id").value != "") {
                userId = BX("user_id").value;
            }
            BX.showWait();
            BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_new.php', '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&paysystemid=' + orderPaySysyemID + '&ID=' + orderID + '&LID=<?=CUtil::JSEscape($LID)?>&buyertypechange=' + el.value + '&userId=' + userId, fBuyerChangeTypeResult);
        }
        
        function fBuyerChangeTypeResult(res)
        {
            BX.closeWait();
            var rss = eval( '('+res+')' );

            if (rss["status"] == "ok") {
                var userEl = document.getElementById("user_id");
                var orderID = '<?=$ID?>';

                locationID = rss["location_id"];
                locationZipID = rss["location_zip_id"];

                document.getElementById("buyer_type_change").innerHTML = rss["buyertype"];
                document.getElementById("buyer_type_delivery").innerHTML = rss["buyerdelivery"];
                if (userEl.value != "" && (orderID == '' || orderID == 0)) {
                    fUserGetProfile(userEl);
                } else {
                    fRecalProduct('', '', 'N');
                }
            }
        }
        
        function fChangeProfile(el)
        {
            var userId = document.getElementById("user_id").value;
            var buyerType = document.getElementById("buyer_type_id").value;

            if (userId != "" && buyerType != "") {
                fGetExecScript(userId, buyerType, el.value);
            } else {
                BX.closeWait();
            }
        }
        
        function fLocationResult(result)
        {
            var res = eval( '('+result+')' );

            if (res["status"] == "ok") {
                document.getElementById("LOCATION_CITY_ORDER_PROP_" + res["prop_id"]).innerHTML = res["location"];
                fRecalProduct('', '', 'N');
            }
        }
        //////////
        
        function fUserGetProfile(el)
        {
            var userId = el.value;
            var buyerType = document.getElementById("buyer_type_id").value;
            document.getElementById("buyer_profile_display").style.display = "none";

            if (userId != "" && buyerType != "") {
                BX.showWait();
                BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_new.php', '<?= bitrix_sessid_get() ?>&ORDER_AJAX=Y&id=<?= $ID ?>&LID=<?= CUtil::JSEscape($LID) ?>&currency=<?= $str_CURRENCY ?>&userId=' + userId + '&buyerType=' + buyerType, fUserGetProfileResult);
            }
            RefreshHTML(userId);
        }
        
        function fUserGetProfileResult(res)
        {
            var rs = eval( '('+res+')' );
            if (rs["status"] == "ok") {
                BX.closeWait();
                document.getElementById("buyer_profile_display").style.display = "table-row";
                document.getElementById("buyer_profile_select").innerHTML = rs["userProfileSelect"];
                document.getElementById("user_name").innerHTML = rs["userName"];

                if (rs["viewed"].length > 0) {
                    document.getElementById("buyer_viewed").innerHTML = rs["viewed"];
                    fTabsSelect('buyer_viewed', 'tab_3');
                } else {
                    document.getElementById("buyer_viewed").innerHTML = '';
                    BX('tab_3').style.display = "none";
                    BX('buyer_viewed').style.display = "none";

                    if (BX('tab_1').style.display == "block")
                        fTabsSelect('user_recomendet', 'tab_1');
                    else if (BX('tab_2').style.display == "block")
                        fTabsSelect('user_basket', 'tab_2');

                }
                if (rs["userBasket"].length > 0) {
                    document.getElementById("user_basket").innerHTML = rs["userBasket"];
                    fTabsSelect('user_basket', 'tab_2');
                } else {
                    document.getElementById("user_basket").innerHTML = '';
                    BX('tab_2').style.display = "none";
                    BX('user_basket').style.display = "none";

                    if (BX('tab_1').style.display == "block") {
                        fTabsSelect('user_recomendet', 'tab_1');
                    } else if (BX('tab_3').style.display == "block") {
                        fTabsSelect('buyer_viewed', 'tab_3');
                    }
                }
                var profile = document.getElementById("user_profile");
                fChangeProfile(profile);
            } else {
                BX.closeWait();
            }
        }
        
        function fGetExecScript(userId, buyerType, profileDefault)
        {
            BX.ajax({
                url: '/bitrix/admin/linemedia.auto_sale_order_new.php',
                method: 'POST',
                data : '<?= bitrix_sessid_get() ?>&ORDER_AJAX=Y&LID=<?= CUtil::JSEscape($LID) ?>&userId=' + userId + '&buyerType=' + buyerType + '&profileDefault=' + profileDefault,
                dataType: 'html',
                timeout: 10,
                async: true,
                processData: true,
                scriptsRunFirst: true,
                emulateOnload: true,
                start: true,
                cache: false
            });
            BX.closeWait();
        }
        </script>
    </td>
</tr>
<?
$tabControl->EndCustomField("NEWO_BUYER");

$tabControl->AddSection("BUYER_DELIVERY", GetMessage("SOE_DELIVERY"));

$tabControl->BeginCustomField("DELIVERY_SERVICE", GetMessage("NEWO_DELIVERY_SERVICE"), true);
$arDeliveryOrder = fGetDelivery($locationID, $locationZipID, $productWeight, ($str_PRICE-$str_PRICE_DELIVERY), $str_CURRENCY, $LID, $str_DELIVERY_ID);
?>
    <tr>
        <td class="adm-detail-content-cell-l" width="40%">
            <?=GetMessage("SOE_DELIVERY_COM")?>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
            <div id="DELIVERY_SELECT"><?=$arDeliveryOrder["DELIVERY"]; ?></div>
            <div id="DELIVER_ID_DESC"><?=$arDeliveryOrder["DELIVERY_DEFAULT_DESCRIPTION"]?></div>
        </td>
    </tr>
    <tr>
        <td class="adm-detail-content-cell-l">
            <?= GetMessage("SOE_DELIVERY_PRICE") ?>:
        </td>
        <td class="adm-detail-content-cell-r">
            <?
                $deliveryPrice = roundEx($str_PRICE_DELIVERY, SALE_VALUE_PRECISION);;
                if ($bVarsFromForm) {
                    $deliveryPrice = roundEx($PRICE_DELIVERY, SALE_VALUE_PRECISION);
                }
            ?>
            <input type="text" onChange="fChangeDeliveryPrice();" name="PRICE_DELIVERY" id="DELIVERY_ID_PRICE" size="10" maxlength="20" value="<?=$deliveryPrice;?>" >
            <input type="hidden" name="change_delivery_price" value="N" id="change_delivery_price">
            <script type="text/javascript"> 
                /*
                 * Смена стоимости доставки.
                 */
                function fChangeDeliveryPrice()
                {
                    document.getElementById("change_delivery_price").value = "Y";
                    fRecalProduct('', '', 'N');
                }
                
                /*
                 * Смена способа доставки.
                 */
                function fChangeDelivery()
                {
                    document.getElementById("change_delivery_price").value = "N";
                    fRecalProduct('', '', 'N');
                }
            </script>
        </td>
    </tr>
<?
$tabControl->EndCustomField("DELIVERY_SERVICE");

if (IntVal($ID) > 0) {
    $tabControl->BeginCustomField("ORDER_ALLOW_DELIVERY", GetMessage("SOE_DELIVERY_ALLOWED"), true);
    ?>
    <tr>
        <td width="40%">
            <?= GetMessage("SOE_DELIVERY_ALLOWED") ?>:
        </td>
        <td width="60%">
            <input type="checkbox" name="ALLOW_DELIVERY" id="ALLOW_DELIVERY"<?if (!$bUserCanDeliverOrder) echo " disabled";?> value="Y"<?if ($str_ALLOW_DELIVERY == "Y") echo " checked";?>>&nbsp;<label for="ALLOW_DELIVERY"><?=GetMessage("SO_YES")?></label>
            <? if (strlen($str_DATE_ALLOW_DELIVERY) > 0) { ?>
                &nbsp;(<?= $str_DATE_ALLOW_DELIVERY ?>)
            <? } ?>
        </td>
    </tr>
    <tr>
        <td width="40%">
            <?= GetMessage("SOE_DEL_VOUCHER_NUM") ?>:
        </td>
        <td width="60%">
            <input type="text" name="DELIVERY_DOC_NUM" value="<?= $str_DELIVERY_DOC_NUM ?>" size="20" maxlength="20" />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <?= GetMessage("SOE_DEL_VOUCHER_DATE") ?>:
        </td>
        <td width="60%">
            <?= CalendarDate("DELIVERY_DOC_DATE", $str_DELIVERY_DOC_DATE, "form_order_buyers_form", "10", "class=\"typeinput\""); ?>
        </td>
    </tr>
    <?
    $tabControl->EndCustomField("ORDER_ALLOW_DELIVERY");
}

$tabControl->AddSection("BUYER_PAYMENT", GetMessage("SOE_PAYMENT"));

$tabControl->BeginCustomField("BUYER_PAY_SYSTEM", GetMessage("SOE_PAY_SYSTEM"), true);
?>
<tr>
    <td id="buyer_type_delivery" colspan="2">
        <?= fBuyerDelivery($str_PERSON_TYPE_ID, $str_PAY_SYSTEM_ID) ?>
    </td>
</tr>
<?
$tabControl->EndCustomField("BUYER_PAY_SYSTEM");

if (IntVal($ID) > 0) {
    $tabControl->BeginCustomField("ORDER_PAYED", GetMessage("SOE_ORDER_PAID"), true);
    ?>
    <tr>
        <td width="40%" valign="top">
            <?= GetMessage("SOE_ORDER_PAID") ?>:
        </td>
        <td width="60%">
            <input type="checkbox"<?if (!$bUserCanPayOrder) echo " disabled";?> name="PAYED" id="PAYED" value="Y"<?if ($str_PAYED == "Y") echo " checked";?> onchange="BX.show(BX('ORDER_PAYED_MORE'))">&nbsp;<label for="PAYED"><?=GetMessage("SO_YES")?></label>
            <? if (strlen($str_DATE_PAYED) > 0) {
                echo "&nbsp;(".$str_DATE_PAYED.")";
            }
            ?><div id="ORDER_PAYED_MORE" style="display:none;"><?
            $arPayDefault = fGetPayFromAccount($str_USER_ID, $str_CURRENCY);
            if ($str_PAYED == "Y") {
                ?>
                <input type="checkbox" name="PAY_FROM_ACCOUNT_BACK" id="PAY_FROM_ACCOUNT_BACK" value="Y"/>&nbsp;<label for="PAY_FROM_ACCOUNT_BACK"><?=GetMessage('SOD_PAY_ACCOUNT_BACK')?></label>
                <?
            } else {
                $buyerCanPay = "none";
                if (DoubleVal($arPayDefault["PAY_BUDGET"]) > 0):
                    $buyerCanPay = "block";
                endif;
                ?>
                <span id="buyerCanBuy" style="display:<?=$buyerCanPay?>">
                    <input type="checkbox" name="PAY_CURRENT_ACCOUNT" id="PAY_CURRENT_ACCOUNT" value="Y" <?if ($PAY_CURRENT_ACCOUNT == "Y") echo " checked";?><?if (!$bUserCanPayOrder) echo " disabled";?>/>&nbsp;<label for="PAY_CURRENT_ACCOUNT"><?=GetMessage("NEWO_CURRENT_ACCOUNT")?> (<span id="PAY_CURRENT_ACCOUNT_DESC"><?=$arPayDefault["PAY_MESSAGE"]?></span>)</label>
                </span>
                <?
            }
            ?>
            </div>
        </td>
    </tr>
    <tr>
        <td width="40%">
            <?= GetMessage("SOE_VOUCHER_NUM") ?>:
        </td>
        <td width="60%">
            <input type="text" name="PAY_VOUCHER_NUM" value="<?= $str_PAY_VOUCHER_NUM ?>" size="20" maxlength="20">
        </td>
    </tr>
    <tr>
        <td width="40%">
            <?= GetMessage("SOE_VOUCHER_DATE") ?>:
        </td>
        <td width="60%">
            <?= CalendarDate("PAY_VOUCHER_DATE", $str_PAY_VOUCHER_DATE, "form_order_buyers_form", "10", "class=\"typeinput\"".((!$bUserCanPayOrder) ? " disabled" : "")); ?>
        </td>
    </tr>
    <?
    $tabControl->EndCustomField("ORDER_PAYED");
}

$tabControl->BeginCustomField("NEWO_HTML_EVENTS", GetMessage("NEWO_HTML_EVENTS"), true);

?>
<tr>
    <td colspan="2">
        <div id="module-html-id"><?= $html ?></div>
    </td>
</tr>
<?

$tabControl->EndCustomField("NEWO_HTML_EVENTS");


$tabControl->AddSection("NEWO_COMMENTS", GetMessage("NEWO_COMMENTS"));
$tabControl->BeginCustomField("NEWO_COMMENTS_A", GetMessage("NEWO_COMMENTS"), true);
?>
<tr>
    <td width="40%" valign="top"><?=GetMessage("SOE_COMMENT")?>:<br /><small><?=GetMessage("SOE_COMMENT_NOTE")?></small></td>
    <td width="60%">
        <textarea name="COMMENTS" cols="40" rows="5"><?=$str_COMMENTS?></textarea>
    </td>
</tr>
<?
$tabControl->EndCustomField("NEWO_COMMENTS_A");

$tabControl->BeginCustomField("NEWO_TITLE_ORDER", GetMessage("NEWO_TITLE_ORDER"), true);
?>
<tr>
    <td colspan="2" valign="top">
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="88%" align="left" class="heading" ><?=GetMessage("NEWO_TITLE_ORDER")?></td>
                <td align="right" nowrap>
                    <a title="<?=GetMessage("SOE_ADD_ITEMS")?>" onClick="AddProductSearch(1);" class="adm-btn adm-btn-green adm-btn-add"  style="white-space:nowrap;" href="javascript:void(0);"><?=GetMessage("SOE_ADD_ITEMS")?></a>
                </td>
            </tr>
        </table>
    </td>
</tr>
<?
$tabControl->EndCustomField("NEWO_TITLE_ORDER");

$tabControl->BeginCustomField("BASKET_CONTAINER", GetMessage("NEWO_BASKET_CONTAINER"), true);
?>
<tr>
    <td colspan="2" id="ID_BASKET_CONTAINER">
        <?
        if (!empty($_REQUEST["productDelay"]) || !empty($_REQUEST["productSub"]) || !empty($_REQUEST["productNA"])) {
            echo BeginNote();
            echo GetMessage("NEWO_PRODUCTS_MES")."<br />";
            if (!empty($_REQUEST["productSub"])) {
                $dbItem = CIBlockElement::GetList(Array(), Array("ID" => $_REQUEST["productSub"]), false, false, Array("ID", "NAME", "IBLOCK_ID", "IBLOCK_SECTION_ID"));
                while ($arItem = $dbItem->Fetch())
                    echo "<b>"."<a href=\"/bitrix/admin/iblock_element_edit.php?ID=".$arItem["ID"]."&type=catalog&lang=".LANG."&IBLOCK_ID=".$arItem["IBLOCK_ID"]."&find_section_section=".$arItem["IBLOCK_SECTION_ID"]."\">".htmlspecialcharsbx($arItem["NAME"])."</a></b> (".GetMessage("NEWO_PRODUCTS_SUB").")<br />";
            }
            if (!empty($_REQUEST["productDelay"])) {
                $dbItem = CIBlockElement::GetList(Array(), Array("ID" => $_REQUEST["productDelay"]), false, false, Array("ID", "NAME", "IBLOCK_ID", "IBLOCK_SECTION_ID"));
                while ($arItem = $dbItem->Fetch())
                    echo "<b>"."<a href=\"/bitrix/admin/iblock_element_edit.php?ID=".$arItem["ID"]."&type=catalog&lang=".LANG."&IBLOCK_ID=".$arItem["IBLOCK_ID"]."&find_section_section=".$arItem["IBLOCK_SECTION_ID"]."\">".htmlspecialcharsbx($arItem["NAME"])."</a></b> (".GetMessage("NEWO_PRODUCTS_DELAY").")<br />";
            }
            if (!empty($_REQUEST["productNA"])) {
                $dbItem = CIBlockElement::GetList(Array(), Array("ID" => $_REQUEST["productNA"]), false, false, Array("ID", "NAME", "IBLOCK_ID", "IBLOCK_SECTION_ID"));
                while ($arItem = $dbItem->Fetch())
                    echo "<b>"."<a href=\"/bitrix/admin/iblock_element_edit.php?ID=".$arItem["ID"]."&type=catalog&lang=".LANG."&IBLOCK_ID=".$arItem["IBLOCK_ID"]."&find_section_section=".$arItem["IBLOCK_SECTION_ID"]."\">".htmlspecialcharsbx($arItem["NAME"])."</a></b> (".GetMessage("NEWO_PRODUCTS_NA").")<br />";
            }
            echo EndNote();
        }
        ?>
        <script language="JavaScript">
            var arProduct = [];
            var arProductEditCountProps = [];
        </script>
        <?
        $arCurFormat = CCurrencyLang::GetCurrencyFormat($str_CURRENCY);
        $CURRENCY_FORMAT = trim(str_replace("#", '', $arCurFormat["FORMAT_STRING"]));
        
        $arBasketItem = array();
        if ((isset($PRODUCT) && count($PRODUCT) > 0) && $bVarsFromForm) {
            foreach ($PRODUCT as $key => $val) {
                foreach ($val as $k => $v) {
                    if (!is_array($v)) {
                        $val[$k] = htmlspecialcharsbx($v);
                    } else {
                        foreach ($v as $kp => $vp) {
                            foreach ($vp as $kkp => $vvp) {
                                $val[$k][$kp][$kkp] = htmlspecialcharsbx($vvp);
                            }
                        }
                    }
                }
                $val["PRODUCT_ID"] = $key;
                $arBasketItem[] = $val;
            }
        } elseif (isset($ID) && $ID > 0) {
            $dbBasket = CSaleBasket::GetList(
                array("NAME" => "ASC"),
                array("ORDER_ID" => $ID),
                false,
                false,
                array("ID", "PRODUCT_ID", "PRODUCT_PRICE_ID", "PRICE", "CURRENCY", "WEIGHT", "QUANTITY", "NAME", "MODULE", "CALLBACK_FUNC", "NOTES", "DETAIL_PAGE_URL", "DISCOUNT_PRICE", "DISCOUNT_VALUE", "ORDER_CALLBACK_FUNC", "CANCEL_CALLBACK_FUNC", "PAY_CALLBACK_FUNC", "CATALOG_XML_ID", "PRODUCT_XML_ID", "VAT_RATE")
            );
            while ($arBasket = $dbBasket->GetNext()) {
                $arBasket["PROPS"] = Array();
                $dbBasketProps = CSaleBasket::GetPropsList(
                        array("SORT" => "ASC", "NAME" => "ASC"),
                        array("BASKET_ID" => $arBasket["ID"]),
                        false,
                        false,
                        array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
                    );
                while ($arBasketProps = $dbBasketProps->GetNext()) {
                    $arBasket["PROPS"][$arBasketProps["ID"]] = $arBasketProps;
                }

                $arBasketItem[$arBasket["ID"]] = $arBasket;
            }
        }

        foreach ($arBasketItem as $key => $val) {
            if ($val["MODULE"] == "catalog" && CModule::IncludeModule('catalog')) {
                $res = CIBlockElement::GetList(array(), array("ID" => $val["PRODUCT_ID"]), false, false, array('IBLOCK_ID', 'IBLOCK_SECTION_ID'));
                if ($arCat = $res->Fetch()) {
                    if ($arCat["IBLOCK_ID"] > 0 && $arCat["IBLOCK_SECTION_ID"] > 0)
                        $arBasketItem[$key]["EDIT_PAGE_URL"] = "/bitrix/admin/iblock_element_edit.php?ID=".$val["PRODUCT_ID"]."&type=catalog&lang=".LANG."&IBLOCK_ID=".$arCat["IBLOCK_ID"]."&find_section_section=".$arCat["IBLOCK_SECTION_ID"];
                }
            }
        }

        $ORDER_TOTAL_PRICE = 0;
        $ORDER_PRICE_WITH_DISCOUNT = 0;
        $productCountAll = 0;
        $productWeight = 0;
        $arFilterRecomendet = array();
        $WEIGHT_UNIT = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_unit', "", $LID));
        $WEIGHT_KOEF = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_koef', 1, $LID));

        $QUANTITY_FACTORIAL = COption::GetOptionString('sale', 'QUANTITY_FACTORIAL', "N");
        if (!isset($QUANTITY_FACTORIAL) || $QUANTITY_FACTORIAL == "") {
            $QUANTITY_FACTORIAL = 'N';
        }
        
        //edit form props
        $formTemplate = '';
    ?>
    <br>
    
    <div id="basket-table-wrapper-id">
        <? fGetBaskets(); ?>
    </div>
    
    </td>
</tr>
<tr>
    <td valign="top" align="left" colspan="2">
        <br/>
        <div class="set_cupon">
            <?= GetMessage("NEWO_BASKET_CUPON") ?>:
            <input type="text" name="CUPON" id="CUPON" value="<?= htmlspecialcharsbx($CUPON) ?>" />
            <a href="javascript:void(0)" onClick="fRecalProduct('', '', 'N');"><?= GetMessage("NEWO_CUPON_RECALC") ?></a>
            <div><?= GetMessage("NEWO_CUPON_DESC") ?></div>
        </div>
        <div style="float:right">
            <script>
                function fMouseOver(el)
                {
                    el.className = 'tr_hover';
                }
                
                function fMouseOut(el)
                {
                    el.className = '';
                }
                
                function fEditPrice(item, type)
                {
                    return;
                    /*
                    if (type == 'on') {
                        BX('DIV_PRICE_' + item).className = 'edit_price edit_enable';
                        BX('PRODUCT['+item+'][PRICE]').focus();
                    }
                    if (type == 'exit') {
                        BX('DIV_PRICE_' + item).className = 'edit_price';
                    }
                    */
                }
                
                function AddProductSearch(index)
                {
                    var quantity = 1;
                    var BUYER_ID = document.form_order_buyers_form.user_id.value;
                    var BUYER_CUPONS = document.getElementById("CUPON").value;

                    window.open('/bitrix/admin/linemedia.auto_sale_search_new.php?lang=<?= LANGUAGE_ID ?>&LID=<?=CUtil::JSEscape($LID)?>&func_name=FillProductFields&index=' + index + '&QUANTITY=' + quantity + '&BUYER_ID=' + BUYER_ID + '&BUYER_COUPONS=' + BUYER_CUPONS, '', 'scrollbars=yes,resizable=yes,width=840,height=550,top='+parseInt((screen.height - 500)/2-14)+',left='+parseInt((screen.width - 840)/2-5));
                }
            </script>
            <? $productAddBool = COption::GetOptionString('sale', 'SALE_ADMIN_NEW_PRODUCT', 'N'); ?>
            <? if ($productAddBool == "Y") { ?>
                <a title="<?= GetMessage("SOE_NEW_ITEMS") ?>" onClick="ShowProductEdit('', 'Y');" class="adm-btn adm-btn-green" href="javascript:void(0);"><?= GetMessage("SOE_NEW_ITEMS") ?></a>
            <? } ?>
            <a title="<?= GetMessage("SOE_ADD_ITEMS") ?>" onClick="AddProductSearch(1);" class="adm-btn adm-btn-green adm-btn-add" href="javascript:void(0);"><?= GetMessage("SOE_ADD_ITEMS") ?></a>
        </div>

<script language="JavaScript">
    var currencyBase = '<?= CSaleLang::GetLangCurrency($LID) ?>';
    var orderWeight = '<?= $productWeight ?>';
    var orderPrice = '<?= $str_PRICE ?>';

    window.onload = function () {
        <? if ($bVarsFromForm) { ?>
            <?= "fRecalProduct('', '', 'N');"; ?>
        <? } ?>
    };
    
    function fEnableSub()
    {
        if (document.getElementById('tbl_sale_order_edit')) {
            document.getElementById('tbl_sale_order_edit').style.zIndex  = 10000;
        }
    }
    
    function pJCFloatDiv()
    {
        var _this = this;
        this.floatDiv = null;
        this.x = this.y = 0;

        this.Show = function(div, left, top)
        {
            var zIndex = parseInt(div.style.zIndex);
            if (zIndex <= 0 || isNaN(zIndex)) {
                zIndex = 1100;
            }
            div.style.zIndex = zIndex;
            div.style.left = left + "px";
            div.style.top = top + "px";

            if (jsUtils.IsIE()) {
                var frame = document.getElementById(div.id+"_frame");
                if (!frame) {
                    frame = document.createElement("IFRAME");
                    frame.src = "javascript:''";
                    frame.id = div.id+"_frame";
                    frame.style.position = 'absolute';
                    frame.style.zIndex = zIndex-1;
                    document.body.appendChild(frame);
                }
                frame.style.width = div.offsetWidth + "px";
                frame.style.height = div.offsetHeight + "px";
                frame.style.left = div.style.left;
                frame.style.top = div.style.top;
                frame.style.visibility = 'visible';
            }
        }
        this.Close = function(div)
        {
            if (!div) {
                return;
            }
            var frame = document.getElementById(div.id+"_frame");
            if (frame) {
                frame.style.visibility = 'hidden';
            }
        }
    }
    var pjsFloatDiv = new pJCFloatDiv();

    function SaleBasketEdit()
    {
        var _this = this;
        this.active = null;

        this.PopupShow = function(div, pos)
        {
            this.PopupHide();
            if (!div) {
                return;
            }
            if (typeof(pos) != "object") {
                pos = {};
            }
            this.active = div.id;
            div.ondrag = jsUtils.False;

            jsUtils.addEvent(document, "keypress", _this.OnKeyPress);

            div.style.width = div.offsetWidth + 'px';
            div.style.visibility = 'visible';

            var res = jsUtils.GetWindowSize();
            pos['top'] = parseInt(res["scrollTop"] + res["innerHeight"]/2 - div.offsetHeight/2);
            pos['left'] = parseInt(res["scrollLeft"] + res["innerWidth"]/2 - div.offsetWidth/2);
            if (pos['top'] < 5) {
                pos['top'] = 5;
            }
            if (pos['left'] < 5) {
                pos['left'] = 5;
            }
            pjsFloatDiv.Show(div, pos["left"], pos["top"]);
        }

        this.PopupHide = function()
        {
            var div = document.getElementById(_this.active);
            if (div) {
                pjsFloatDiv.Close(div);
                div.parentNode.removeChild(div);
            }
            this.active = null;
            jsUtils.removeEvent(document, "keypress", _this.OnKeyPress);
        }

        this.OnKeyPress = function(e)
        {
            if (!e) e = window.event
            if (!e) return;
            if (e.keyCode == 27)
                _this.PopupHide();
        },

        this.IsVisible = function()
        {
            return (document.getElementById(this.active).style.visibility != 'hidden');
        }
    }

    check_ctrl_enter = function(e)
    {
        if (!e) {
            e = window.event;
        }
        
        if ((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey) {
            alert('submit!');
        }
    }
    SaleBasketEditTool = new SaleBasketEdit();
    
    
    function ShowProductEdit(id, newElement)
    {
        var div = document.createElement("DIV");
        div.id = "product_edit";
        div.style.visible = 'hidden';
        div.style.position = 'absolute';
        div.innerHTML = '<?=$formTemplate?>';

        document.body.appendChild(div);
        SaleBasketEditTool.PopupShow(div);

        if (id != "") {
            document.getElementById('FORM_NEWPROD_CODE').style.display = 'none'
            document.getElementById('FORM_BASKET_PRODUCT_ID').value = id;
            document.getElementById('FORM_PROD_BASKET_ID').value = id;
            document.getElementById('FORM_PROD_BASKET_NAME').value = document.getElementById('PRODUCT[' + id + '][NAME]').value;
            document.getElementById('FORM_PROD_BASKET_DETAIL_URL').value = document.getElementById('PRODUCT[' + id + '][DETAIL_PAGE_URL]').value;
            document.getElementById('FORM_PROD_BASKET_NOTES').value = document.getElementById('PRODUCT[' + id + '][NOTES]').value;
            document.getElementById('FORM_BASKET_CATALOG_XML').value = document.getElementById('PRODUCT[' + id + '][CATALOG_XML_ID]').value;
            document.getElementById('FORM_PROD_BASKET_PRODUCT_XML').value = document.getElementById('PRODUCT[' + id + '][PRODUCT_XML_ID]').value;
            document.getElementById('FORM_PROD_BASKET_PRICE').value = document.getElementById('PRODUCT[' + id + '][PRICE]').value;
            document.getElementById('FORM_PROD_BASKET_WEIGHT').value = document.getElementById('PRODUCT[' + id + '][WEIGHT]').value;
            document.getElementById('FORM_PROD_BASKET_QUANTITY').value = document.getElementById('PRODUCT[' + id + '][QUANTITY]').value;
        }
        if (id != "" && arProductEditCountProps[id]) {
            propCnt = parseInt(arProductEditCountProps[id]);
            for (i = 1; i <= propCnt; i++) {
                if (document.getElementById("PRODUCT_PROPS_NAME_" + id + "_" + i)) {
                    nameProp = document.getElementById("PRODUCT_PROPS_NAME_" + id + "_" + i).value;
                    codeProp = document.getElementById("PRODUCT_PROPS_CODE_" + id + "_" + i).value;
                    valueProp = document.getElementById("PRODUCT_PROPS_VALUE_" + id + "_" + i).value;
                    sortProp = document.getElementById("PRODUCT_PROPS_SORT_" + id + "_" + i).value;

                    BasketAddPropSection(i, nameProp, codeProp, valueProp, sortProp);
                }
            }
        } else if (id != "") {
            arProductEditCountProps[id] = 0;
        }
    }
    
    
    function BasketAddPropSection(id, nameProp, codeProp, valueProp, sortProp)
    {
        var error = '';

        if (!nameProp) {
            nameProp = "";
        }
        if (!codeProp) {
            codeProp = "";
        }
        if (!valueProp) {
            valueProp = "";
        }
        if (!sortProp) {
            sortProp = "";
        }
        if (!id) {
            id = "";
        }

        prod_id = document.getElementById('FORM_PROD_BASKET_ID').value;
        prod_id = parseInt(prod_id);

        if (prod_id.length <= 0 || isNaN(prod_id)) {
            error += '<?=GetMessage("SOE_NEW_ERR_PROD_ID")?><br />';
        }
        if (error.length > 0) {
            document.getElementById('basketError').style.display = 'block';
            document.getElementById('basketErrorText').innerHTML = error;
        } else {
            if (id == '') {
                if (!arProductEditCountProps[prod_id]) {
                    arProductEditCountProps[prod_id] = 0;
                }
                countProp = parseInt(arProductEditCountProps[prod_id]);
                countProp = countProp + 1;
                arProductEditCountProps[prod_id] = countProp;
            } else {
                countProp = id;
            }

            var oTbl = document.getElementById("BASKET_PROP_TABLE");
            if (!oTbl) {
                return;
            }
            var oRow = oTbl.insertRow(-1);
            var oCell = oRow.insertCell(-1);
            oCell.innerHTML = '<input type="text" maxlength="250" size="20" name="FORM_PROD_PROP_' + prod_id + '_NAME_' + countProp + '" id="FORM_PROD_PROP_' + prod_id + '_NAME_' + countProp + '" value="'+BX.util.htmlspecialchars(nameProp)+'" />';
            var oCell = oRow.insertCell(-1);
            oCell.innerHTML = '<input type="text" maxlength="250" size="20" name="FORM_PROD_PROP_' + prod_id + '_VALUE_' + countProp + '" id="FORM_PROD_PROP_' + prod_id + '_VALUE_' + countProp + '" value="'+BX.util.htmlspecialchars(valueProp)+'" />';
            var oCell = oRow.insertCell(-1);
            oCell.innerHTML = '<input type="text" maxlength="250" size="3" name="FORM_PROD_PROP_' + prod_id + '_CODE_' + countProp + '" id="FORM_PROD_PROP_' + prod_id + '_CODE_' + countProp + '" value="'+BX.util.htmlspecialchars(codeProp)+'" />';
            var oCell = oRow.insertCell(-1);
            oCell.innerHTML = '<input type="text" maxlength="10" size="2" name="FORM_PROD_PROP_' + prod_id + '_SORT_' + countProp + '" id="FORM_PROD_PROP_' + prod_id + '_SORT_' + countProp + '" value="'+BX.util.htmlspecialchars(sortProp)+'" />';
        }
    }
    
    
    /*
     * Обновление корзины.
     */
    function RefreshBasket()
    {
        dateURL = '<?= bitrix_sessid_get() ?>&ORDER_AJAX=Y&id=<?= $ID ?>&LID=<?= CUtil::JSEscape($LID) ?>&getcarts=Y';
        
        BX.showWait();
        BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_new.php', dateURL, RefreshBasketResult);
    }
    
    function RefreshBasketResult(result)
    {
        var div = document.getElementById('basket-table-wrapper-id');
        div.innerHTML = result;
        
        BX.closeWait();
    }
    
    
    /*
     * Добавление новой корзины.
     */
    function FillProductFields(index, arParams, price)
    {
        var ID = arParams['hash'];
        
        dateURL  = '<?= bitrix_sessid_get() ?>&ORDER_AJAX=Y&id=<?= $ID ?>&LID=<?= CUtil::JSEscape($LID) ?>&addcart=Y';
        dateURL += '&cart[part_id]=' + arParams['id'];
        dateURL += '&cart[article]=' + arParams['article'];
        dateURL += '&cart[brand_title]=' + arParams['brand_title'];
        dateURL += '&cart[price]=' + price;
        dateURL += '&cart[supplier_id]=' + arParams['supplier_id'];
        dateURL += '&cart[extra][]=' + arParams['extra'];
        for (var index in arParams['extra']) {
            dateURL += '&cart[extra][' + index + ']=' + arParams['extra'][index];
        }
        
        BX.showWait();
        BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_new.php', dateURL, RefreshBasket);
        
        // Обновление данных.
        fRecalProduct(0, null, null);
    }
    
    
    /*
     * Удаление корзины.
     */
    function DeleteProduct(el, id)
    {
        $.ajax({
            url: '/bitrix/admin/linemedia.auto_sale_order_new.php?<?= bitrix_sessid_get() ?>&ORDER_AJAX=Y&delcart=Y&id=' + id,
        }).done(function(response) {
            RefreshBasket();
        });
        
        return false;
    }
    
    
    /*
     * Обновление HTML.
     */
    function RefreshHTML(userId)
    {
        $.ajax({
            url: '/bitrix/admin/linemedia.auto_sale_order_new.php?<?= bitrix_sessid_get() ?>&ORDER_AJAX=Y&checkhtml=Y&userID=' + userId,
        }).done(function(response) {
            $('#module-html-id').html(response);
        });
        
        return false;
    }
    

    function fRecalProduct(id, type, recommendet)
    {
        var location = '';
        var locationZip = '';
        var paySystemId = '';
        var deliveryId = '';
        var buyerTypeId = '';
        var cupon = '';
        var user_id = 0;
        if (BX('user_id')) {
            user_id = BX('user_id').value;
        }
        //var productData = "{";
        var j = 0;

        if (type != "" && type == "price") {
            document.getElementById('CALLBACK_FUNC_' + id).value = "Y";
        }
        productData = "";
        if (id > 0) {
            var quantity = $('#PRODUCT_' + id + '_QUANTITY').val();
            productData = "{'" + id + "': {'quantity':'" + quantity + "'}}";
        }
        
        if (BX('CITY_ORDER_PROP_' + locationID)) {
            var selectedIndex = BX('CITY_ORDER_PROP_' + locationID).selectedIndex;
            var selectedOption = BX('CITY_ORDER_PROP_' + locationID).options;
        } else if (BX('ORDER_PROP_' + locationID)) {
            var selectedIndex = BX('ORDER_PROP_' + locationID).selectedIndex;
            var selectedOption = BX('ORDER_PROP_' + locationID).options;
        }

        if (locationID > 0 && selectedIndex > 0) {
            location = selectedOption[selectedIndex].value;
        }
        if (BX('ORDER_PROP_' + locationZipID)) {
            locationZip = BX('ORDER_PROP_' + locationZipID).value;
        }
        deliveryId = document.getElementById('DELIVERY_ID').value;
        deliveryPrice = parseFloat(document.getElementById('DELIVERY_ID_PRICE').value);
        if (isNaN(deliveryPrice)) {
            deliveryPrice = 0;
        }
        paySystemId = document.getElementById('PAY_SYSTEM_ID').value;
        buyerTypeId = document.getElementById('buyer_type_id').value;
        cupon = document.getElementById('CUPON').value;

        var deliveryPriceChange = document.getElementById("change_delivery_price").value;
        var recomMore = document.getElementById('recom_more').value;

        dateURL = '<?= bitrix_sessid_get() ?>&ORDER_AJAX=Y&id=<?= $ID ?>&LID=<?= CUtil::JSEscape($LID) ?>&recomMore='+recomMore+'&recommendet='+recommendet+'&delpricechange='+deliveryPriceChange+'&user_id=' + user_id + '&cupon=' + cupon + '&currency=' + currencyBase + '&deliveryId=' + deliveryId + '&paySystemId=' + paySystemId + '&deliveryPrice=' + deliveryPrice + '&buyerTypeId=' + buyerTypeId + '&locationID=' + locationID + '&location=' + location + '&locationZipID=' + locationZipID + '&locationZip=' + locationZip + '&product=' + productData;
        
        BX.showWait();
        BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_new.php', dateURL, fRecalProductResult);
        
        RefreshBasket();
    }
    
    
    function fRecalProductResult(result)
    {
        BX.closeWait();
        
        if (result.length > 0) {
            var res = eval( '('+result+')' );
            
            var changePriceProduct = "N";
            
            BX('DELIVER_ID_DESC').innerHTML = res[0]["DELIVERY_DESCRIPTION"];
            BX('DELIVERY_ID_PRICE').value = res[0]["DELIVERY_PRICE"];
            if (res[0]["DELIVERY"].length > 0) {
                BX('DELIVERY_SELECT').innerHTML = res[0]["DELIVERY"];
            }
            
            if (res[0]["ORDER_ERROR"] == "N") {
                if (BX('town_location_'+res[0]["LOCATION_TOWN_ID"])) {
                    if (res[0]["LOCATION_TOWN_ENABLE"] == 'Y') {
                        BX('town_location_'+res[0]["LOCATION_TOWN_ID"]).style.display = 'table-row';
                    } else {
                        BX('town_location_'+res[0]["LOCATION_TOWN_ID"]).style.display = 'none';
                    }
                }

                BX('ORDER_TOTAL_PRICE').innerHTML = res[0]["PRICE_TOTAL"];
                
                if (res[0]["DISCOUNT_PRODUCT_VALUE"] > 0) {
                    BX('ORDER_PRICE_WITH_DISCOUNT_DESC_VISIBLE').style.display = 'table-row';
                    BX('ORDER_PRICE_WITH_DISCOUNT').innerHTML = res[0]["PRICE_WITH_DISCOUNT_FORMAT"];
                } else {
                    if (changePriceProduct == 'N') {
                        BX('ORDER_PRICE_WITH_DISCOUNT_DESC_VISIBLE').style.display = 'none';
                    } else {
                        BX('ORDER_PRICE_WITH_DISCOUNT_DESC_VISIBLE').style.display = 'table-row';
                        BX('ORDER_PRICE_WITH_DISCOUNT').innerHTML = res[0]["PRICE_WITH_DISCOUNT_FORMAT"];
                    }
                }

                if (parseInt(res[0]["ORDER_ID"]) > 0) {
                    if (parseFloat(res[0]["PAY_ACCOUNT_DEFAULT"]) >= parseFloat(res[0]["PRICE_TO_PAY_DEFAULT"])) {
                        BX('PAY_CURRENT_ACCOUNT_DESC').innerHTML = res[0]["PAY_ACCOUNT"];
                        BX('buyerCanBuy').style.display = 'block';
                    } else {
                        BX('buyerCanBuy').style.display = 'none';
                    }
                }
                
                BX('ORDER_DELIVERY_PRICE').innerHTML = res[0]["DELIVERY_PRICE_FORMAT"];
                BX('ORDER_TAX_PRICE').innerHTML = res[0]["PRICE_TAX"];
                BX('ORDER_WAIGHT').innerHTML = res[0]["PRICE_WEIGHT_FORMAT"];
                BX('ORDER_PRICE_ALL').innerHTML = res[0]["PRICE_TO_PAY"];
                
                if (parseFloat(res[0]["DISCOUNT_VALUE"]) > 0) {
                    BX('ORDER_DISCOUNT_PRICE_VALUE').style.display = "table-row";
                    BX('ORDER_DISCOUNT_PRICE_VALUE_VALUE').innerHTML = res[0]["DISCOUNT_VALUE_FORMATED"];
                }
                
                if (res[0]["RECOMMENDET_CALC"] == "Y") {
                    if (res[0]["RECOMMENDET_PRODUCT"].length == 0) {
                        BX('tab_1').style.display = "none";
                        BX('user_recomendet').style.display = "none";
                        
                        if (BX('user_basket').style.display == "block")
                            fTabsSelect('user_basket', 'tab_2');
                        else if (BX('buyer_viewed').style.display == "block")
                            fTabsSelect('buyer_viewed', 'tab_3');
                        else if (BX('tab_2').style.display == "block")
                            fTabsSelect('user_basket', 'tab_2');
                        else if (BX('tab_3').style.display == "block")
                            fTabsSelect('buyer_viewed', 'tab_3');
                    } else {
                        BX('user_recomendet').innerHTML = res[0]["RECOMMENDET_PRODUCT"];
                    }
                }

                orderWeight = res[0]["PRICE_WEIGHT"];
                orderPrice = res[0]["PRICE_WITH_DISCOUNT"];
            }
        }
    }

    /*
     * click on recommendet More
     */
    function fGetMoreRecom()
    {
        BX('recom_more').value = "Y";
        fRecalProduct('', '', 'Y');
    }

</script>
    </td>
</tr>
<tr>
    <td colspan="2"><br>
        <input type="hidden" name="recom_more" id="recom_more" value="N" />
        <input type="hidden" name="recom_more_busket" id="recom_more_busket" value="N" />
        <input type="hidden" name="recom_more_viewed" id="recom_more_viewed" value="N" />
        
        <table width="100%" class="order_summary">
            <tr>
                <td valign="top" class="summary">
                    <div class="order-itog">
                        <table width="100%">
                                <tr>
                                <td class="title">
                                    <?= GetMessage("NEWO_TOTAL_PRICE")?>
                                </td>
                                <td nowrap class="title">
                                    <div id="ORDER_TOTAL_PRICE" style="white-space:nowrap;">
                                        <?= SaleFormatCurrency($ORDER_TOTAL_PRICE, $str_CURRENCY); ?>
                                    </div>
                                </td>
                            </tr>
                            <tr class="price" style="display:<?echo (($ORDER_PRICE_WITH_DISCOUNT > 0) ? 'table-row' : 'none');?>" id="ORDER_PRICE_WITH_DISCOUNT_DESC_VISIBLE">
                                <td id="ORDER_PRICE_WITH_DISCOUNT_DESC" class="title" >
                                    <div><?echo GetMessage("NEWO_TOTAL_PRICE_WITH_DISCOUNT_MARGIN")?></div>
                                </td>
                                <td nowrap>
                                    <div id="ORDER_PRICE_WITH_DISCOUNT">
                                        <?= SaleFormatCurrency($ORDER_PRICE_WITH_DISCOUNT, $str_CURRENCY); ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                            <td class="title">
                                <?= GetMessage("NEWO_TOTAL_DELIVERY")?>
                            </td>
                            <td nowrap>
                                <div id="ORDER_DELIVERY_PRICE" style="white-space:nowrap;">
                                    <?= SaleFormatCurrency($deliveryPrice, $str_CURRENCY); ?>
                                </div>
                            </td>
                            </tr>
                            <tr>
                                <td class="title">
                                    <?= GetMessage("NEWO_TOTAL_TAX")?>
                                </td>
                                <td nowrap>
                                    <div id="ORDER_TAX_PRICE" style="white-space:nowrap;">
                                        <?= SaleFormatCurrency($str_TAX_VALUE, $str_CURRENCY); ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="title">
                                    <?= GetMessage("NEWO_TOTAL_WEIGHT")?>
                                </td>
                                <td nowrap>
                                    <div id="ORDER_WAIGHT" style="white-space:nowrap;">
                                        <?=roundEx(DoubleVal($productWeight/$WEIGHT_KOEF), SALE_VALUE_PRECISION)." ".$WEIGHT_UNIT;?>
                                    </div>
                                </td>
                            </tr>
                            <tr style="display:none;">
                                <td class="title">
                                    <?= GetMessage("NEWO_TOTAL_PAY_ACCOUNT2")?>
                                </td>
                                <td nowrap>
                                    <div id="ORDER_PAY_FROM_ACCOUNT" style="white-space:nowrap;">
                                        <?= SaleFormatCurrency(roundEx($str_SUM_PAID, SALE_VALUE_PRECISION), $str_CURRENCY); ?>
                                    </div>
                                </td>
                            </tr>
                            <tr class="price" style="display:<?= (($str_DISCOUNT_VALUE > 0) ? 'table-row' : 'none');?>" id="ORDER_DISCOUNT_PRICE_VALUE">
                                <td class="title" >
                                    <?= GetMessage("NEWO_TOTAL_DISCOUNT_PRICE_VALUE")?>
                                </td>
                                <td nowrap>
                                    <div id="ORDER_DISCOUNT_PRICE_VALUE_VALUE" style="white-space:nowrap;">
                                        <?= SaleFormatCurrency($str_DISCOUNT_VALUE, $str_CURRENCY); ?>
                                    </div>
                                </td>
                            </tr>
                            <tr class="itog">
                            <td class='ileft'>
                                <div><?= GetMessage("NEWO_TOTAL_TOTAL")?></div>
                            </td>
                            <td class='iright' nowrap>
                                <div id="ORDER_PRICE_ALL" style="white-space:nowrap;">
                                    <?= SaleFormatCurrency($str_PRICE, $str_CURRENCY); ?>
                                </div>
                            </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </td>
</tr>
<?
$tabControl->EndCustomField("BASKET_CONTAINER");

if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1) {
    $tabControl->Buttons(array("back_url"=>"/bitrix/admin/linemedia.auto_sale_order_new.php?lang=".LANGUAGE_ID."&ID=".$ID."&dontsave=Y"));
}

$tabControl->Show();

//order busket user by manadger
if (isset($_REQUEST["user_id"]) && intval($_REQUEST["user_id"]) > 0 && !$bVarsFromForm) {
    $str_USER_ID = IntVal($_REQUEST["user_id"]);
    
    $arParams = array();
    echo "<script>";
    echo "window.onload = function () {";
    echo "fUserGetProfile(BX(\"user_id\"));\n";
    
    
    /* 
     * 
     * Обработка цен в заказе.
     * 
     */
    if (isset($_REQUEST['product']) && count($_REQUEST['product']) > 0) {
        foreach ($_REQUEST["product"] as $val) {
            $val = intval($val);
            
            if (CModule::IncludeModule('catalog') && CModule::IncludeModule('iblock')) {
                $res = CIBlockElement::GetList(array(), array("ID" => $val), false, false, array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "PREVIEW_PICTURE", "DETAIL_PICTURE", "NAME", "DETAIL_PAGE_URL"));
                
                if ($arItems = $res->Fetch()) {
                    $productImg = "";
                    if ($arItems["PREVIEW_PICTURE"] != "") {
                        $productImg = $arItems["PREVIEW_PICTURE"];
                    } elseif ($arItems["DETAIL_PICTURE"] != "") {
                        $productImg = $arItems["DETAIL_PICTURE"];
                    }
                    $ImgUrl = "";
                    if ($productImg != "") {
                        $arFile = CFile::GetFileArray($productImg);
                        $productImg = CFile::ResizeImageGet($arFile, array('width'=>80, 'height'=>80), BX_RESIZE_IMAGE_PROPORTIONAL, false, false);
                        $ImgUrl = $productImg["src"];
                    }
                    
                    $arBuyerGroups = CUser::GetUserGroup($str_USER_ID);
                    $arPrice = CCatalogProduct::GetOptimalPrice($val, 1, $arBuyerGroups, "N", array(), $LID);

                    $arCurFormat = CCurrencyLang::GetCurrencyFormat($arPrice["PRICE"]["CURRENCY"]);
                    $priceValutaFormat = str_replace("#", '', $arCurFormat["FORMAT_STRING"]);

                    if (!is_array($arPrice["DISCOUNT"]) || count($arPrice["DISCOUNT"]) <= 0) {
                        $arPrice["DISCOUNT_PRICE"] = 0;
                        $price = $arPrice["PRICE"]["PRICE"];
                    } else {
                        $price = $arPrice["DISCOUNT_PRICE"];
                    }

                    $summaFormated = CurrencyFormatNumber($price, $arPrice["PRICE"]["CURRENCY"]);
                    $currentTotalPriceFormat = CurrencyFormatNumber($price, $arPrice["PRICE"]["CURRENCY"]);

                    $balance = 0;
                    $weight = 0;

                    if ($ar_res = CCatalogProduct::GetByID($val)) {
                        $balance = FloatVal($ar_res["QUANTITY"]);
                        $weight = FloatVal($ar_res["WEIGHT"]);
                    }

                    $discountPercent = 0;
                    if ($arPrice["DISCOUNT_PRICE"] > 0) {
                        $discountPercent = IntVal((($arPrice["PRICE"]["PRICE"]-$arPrice["DISCOUNT_PRICE"]) * 100) / $arPrice["PRICE"]["PRICE"]);
                        $priceDiscount = $arPrice["PRICE"]["PRICE"] - $arPrice["DISCOUNT_PRICE"];
                    }

                    $urlEdit = "/bitrix/admin/iblock_element_edit.php?ID=".$arItems["ID"]."&type=catalog&lang=".LANG."&IBLOCK_ID=".$arItems["IBLOCK_ID"]."&find_section_section=".IntVal($arItems["IBLOCK_SECTION_ID"]);
                    
                    $arParams = array(
                        'id' => $val,
                        'name' => CUtil::JSEscape($arItems["NAME"]),
                        'url' => CUtil::JSEscape($arItems["DETAIL_PAGE_URL"]),
                        'urlImg' => CUtil::JSEscape($ImgUrl),
                        'urlEdit' => CUtil::JSEscape($urlEdit),
                        'price' => CUtil::JSEscape($price),
                        'priceFormated' => CUtil::JSEscape($price),
                        'priceBase' => CUtil::JSEscape($arPrice["PRICE"]["PRICE"]),
                        'priceBaseFormat' => CUtil::JSEscape($arPrice["PRICE"]["PRICE"]),
                        'valutaFormat' => CUtil::JSEscape($priceValutaFormat),
                        'priceDiscount' => CUtil::JSEscape($priceDiscount),
                        'summaFormated' => CUtil::JSEscape($summaFormated),
                        'priceTotalFormated' => CUtil::JSEscape($currentTotalPriceFormat),
                        'discountPercent' => CUtil::JSEscape($discountPercent),
                        'balance'  => CUtil::JSEscape($balance),
                        'quantity' => '1',
                        'module' => 'catalog',
                        'currency' => CUtil::JSEscape($arPrice["PRICE"]["CURRENCY"]),
                        'weight' => $weight,
                        'vatRate' => DoubleVal('0'),
                        'priceType' => '',
                        'catalogXmlID' => '',
                        'productXmlID' => '',
                        'callback' => 'LMAutoCatalogBasketCallback',
                        'orderCallback' => 'LMAutoCatalogBasketOrderCallback',
                        'cancelCallback' => 'LMAutoCatalogBasketCancelCallback',
                        'payCallback' => 'LMAutoCatalogPayOrderCallback'
                    );
                    $arParams = CUtil::PhpToJSObject($arParams);
                    
                    echo "FillProductFields(0, ".$arParams.", 0);\n";
                }
            }
        } // end foreach
    } // end if
    echo "fButtonCurrent('btnBuyerExistRemote');";
    echo "};";
    echo "</script>";
}
echo "</div>";//end div for form
?>

<div class="sale_popup_form" id="popup_form_sku_order" style="display:none;">
    <table width="100%">
        <tr><td></td></tr>
        <tr>
            <td><small><span id="listItemPrice"></span>&nbsp;<span id="listItemOldPrice"></span></small></td>
        </tr>
        <tr>
            <td><hr/></td>
        </tr>
    </table>

    <table width="100%" id="sku_selectors_list">
        <tr>
            <td colspan="2"></td>
        </tr>
    </table>

    <span id="prod_order_button"></span>
    <input type="hidden" value="" name="popup-params-product" id="popup-params-product" >
    <input type="hidden" value="" name="popup-params-type" id="popup-params-type" >
</div>
    <script>
            var wind = new BX.PopupWindow('popup_sku', this, {
                offsetTop : 10,
                offsetLeft : 0,
                autoHide : true,
                closeByEsc : true,
                closeIcon : true,
                titleBar : true,
                draggable: {restrict:true},
                titleBar: {content: BX.create("span", {html: '', 'props': {'className': 'sale-popup-title-bar'}})},
                content : document.getElementById("popup_form_sku_order"),

                buttons: [
                    new BX.PopupWindowButton({
                        text : '<?=GetMessageJS('NEWO_POPUP_CAN_BUY_NOT');?>',
                        id : "popup_sku_save",
                        events : {
                            click : function() {
                                if (BX('popup-params-product').value.length > 0) {
                                    if (BX('popup-params-type').value == 'neworder') {
                                        window.location = BX('popup-params-product').value;
                                    } else {
                                        var res = eval( '('+BX('popup-params-product').value+')' );
                                        FillProductFields(0, res, 0);
                                    }

                                    wind.close();
                                }
                            }
                        }
                    }),
                    new BX.PopupWindowButton({
                        text : '<?=GetMessageJS('NEWO_POPUP_CLOSE');?>',
                        id : "popup_sku_cancel",
                        events : {
                            click : function() {
                                wind.close();
                            }
                        }
                    })
                ]
            });
            
            function fAddToBusketMoreProductSku(arSKU, arProperties, type, message)
            {
                BX.message(message);
                wind.show();
                buildSelect("sku_selectors_list", 0, arSKU, arProperties, type);
                var properties_num = arProperties.length;
                var lastPropCode = arProperties[properties_num-1].CODE;
                addHtml(lastPropCode, arSKU, type);
            }
            
            function buildSelect(cont_name, prop_num, arSKU, arProperties, type)
            {
                var properties_num = arProperties.length;
                var lastPropCode = arProperties[properties_num-1].CODE;

                for (var i = prop_num; i < properties_num; i++) {
                    var q = BX('prop_' + i);
                    if (q)
                        q.parentNode.removeChild(q);
                }

                var select = BX.create('SELECT', {
                    props: {
                        name: arProperties[prop_num].CODE,
                        id :  arProperties[prop_num].CODE
                    },
                    events: {
                        change: (prop_num < properties_num-1)
                            ? function() {
                                buildSelect(cont_name, prop_num + 1, arSKU, arProperties, type);
                                if (this.value != "null")
                                    BX(arProperties[prop_num+1].CODE).disabled = false;
                                addHtml(lastPropCode, arSKU, type);
                            }
                            : function() {
                                if (this.value != "null")
                                    addHtml(lastPropCode, arSKU, type)
                            }
                    }
                });
                if (prop_num != 0) select.disabled = true;

                var ar = [];
                select.add(new Option(arProperties[prop_num].NAME, 'null'));

                for (var i = 0; i < arSKU.length; i++) {
                    if (checkSKU(arSKU[i], prop_num, arProperties) && !BX.util.in_array(arSKU[i][prop_num], ar)) {
                        select.add(new Option(
                                arSKU[i][prop_num],
                                prop_num < properties_num-1 ? arSKU[i][prop_num] : arSKU[i]["ID"]
                        ));
                        ar.push(arSKU[i][prop_num]);
                    }
                }

                var cont = BX.create('tr', {
                    props: {id: 'prop_' + prop_num},
                    children:[
                        BX.create('td', {html: arProperties[prop_num].NAME + ': '}),
                        BX.create('td', { children:[
                            select
                        ]}),
                    ]
                });

                var tmp = BX.findChild(BX(cont_name), {tagName:'tbody'}, false, false);

                tmp.appendChild(cont);

                if (prop_num < properties_num-1) {
                    buildSelect(cont_name, prop_num + 1, arSKU, arProperties, type);
                }
            }

            function checkSKU(SKU, prop_num, arProperties)
            {
                for (var i = 0; i < prop_num; i++) {
                    code = BX.findChild(BX('popup_sku'), {'attr': {name: arProperties[i].CODE}}, true, false).value;
                    if (SKU[i] != code)
                        return false;
                }
                return true;
            }
            
            function addHtml(lastPropCode, arSKU, type)
            {
                var selectedSkuId = BX(lastPropCode).value;
                var btnText = '';

                BX('popup-window-titlebar-popup_sku').innerHTML = '<span class="sale-popup-title-bar">'+arSKU[0]["PRODUCT_NAME"]+'</span>';
                BX("listItemPrice").innerHTML = BX.message('PRODUCT_PRICE_FROM')+" "+arSKU[0]["MIN_PRICE"];
                BX("listItemOldPrice").innerHTML = '';

                for (var i = 0; i < arSKU.length; i++) {
                    if (arSKU[i]["ID"] == selectedSkuId) {
                        BX('popup-window-titlebar-popup_sku').innerHTML = '<span class="sale-popup-title-bar">'+arSKU[i]["NAME"]+'</span>';

                        if (arSKU[i]["DISCOUNT_PRICE"] != "") {
                            BX("listItemPrice").innerHTML = arSKU[i]["DISCOUNT_PRICE_FORMATED"]+" "+arSKU[i]["VALUTA_FORMAT"];
                            BX("listItemOldPrice").innerHTML = arSKU[i]["PRICE_FORMATED"]+" "+arSKU[i]["VALUTA_FORMAT"];
                            summaFormated = arSKU[i]["DISCOUNT_PRICE_FORMATED"];
                            price = arSKU[i]["DISCOUNT_PRICE"];
                            priceFormated = arSKU[i]["DISCOUNT_PRICE_FORMATED"];
                            priceDiscount = arSKU[i]["PRICE"] - arSKU[i]["DISCOUNT_PRICE"];
                        } else {
                            BX("listItemPrice").innerHTML = arSKU[i]["PRICE_FORMATED"]+" "+arSKU[i]["VALUTA_FORMAT"];
                            BX("listItemOldPrice").innerHTML = "";
                            summaFormated = arSKU[i]["PRICE_FORMATED"];
                            price = arSKU[i]["PRICE"];
                            priceFormated = arSKU[i]["PRICE_FORMATED"];
                            priceDiscount = 0;
                        }

                        if (arSKU[i]["CAN_BUY"] == "Y") {
                            var arParams = "{'id' : '"+arSKU[i]["ID"]+"',\n\
                            'name' : '"+arSKU[i]["NAME"]+"',\n\
                            'url' : '',\n\
                            'urlEdit' : '"+arSKU[i]["URL_EDIT"]+"',\n\
                            'urlImg' : '"+arSKU[i]["ImageUrl"]+"',\n\
                            'price' : '"+price+"',\n\
                            'priceFormated' : '"+priceFormated+"',\n\
                            'valutaFormat' : '"+arSKU[i]["VALUTA_FORMAT"]+"',\n\
                            'priceDiscount' : '"+priceDiscount+"',\n\
                            'priceBase' : '"+arSKU[i]["PRICE"]+"',\n\
                            'priceBaseFormat' : '"+arSKU[i]["PRICE_FORMATED"]+"',\n\
                            'priceTotalFormated' : '"+arSKU[i]["DISCOUNT_PRICE"]+"',\n\
                            'discountPercent' : '"+arSKU[i]["DISCOUNT_PERCENT"]+"',\n\
                            'summaFormated' : '"+summaFormated+"',\n\
                            'quantity' : '1','module' : 'catalog',\n\
                            'currency' : '"+arSKU[i]["CURRENCY"]+"',\n\
                            'weight' : '0','vatRate' : '0','priceType' : '',\n\
                            'balance' : '0','catalogXmlID' : '','productXmlID' : '','callback' : 'CatalogBasketCallback','orderCallback' : 'CatalogBasketOrderCallback','cancelCallback' : 'CatalogBasketCancelCallback','payCallback' : 'CatalogPayOrderCallback'}";

                            BX('popup-params-type').value = type;

                            if (type != 'neworder') {
                                message = BX.message('PRODUCT_ADD');
                                BX('popup-params-product').value = arParams;
                            } else {
                                message = BX.message('PRODUCT_ORDER');
                                BX('popup-params-product').value = "/bitrix/admin/linemedia.auto_linemedia.auto_sale_order_new.php?lang=<?=LANG?>&user_id="+arSKU[i]["USER_ID"]+"&LID="+arSKU[i]["LID"]+"&product[]="+arSKU[i]["ID"];
                            }
                        } else {
                            BX('popup-params-product').value = '';
                            message = BX.message('PRODUCT_NOT_ADD');
                        }
                        
                        BX.findChild(BX('popup_sku_save'), {'attr': {class: 'popup-window-button-text'}}, true, false).innerHTML = message;
                    }
                    
                    if (arSKU[i]["ID"] == selectedSkuId) {
                        break;
                    }
                }
            }
    </script>

    <style type="text/css">
        .order-itog table tr:hover td {
            background-color: #dfe8bb;
        }
    </style>
<? require ($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php"); ?>