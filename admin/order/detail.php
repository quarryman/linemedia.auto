<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/include.php");

$crmMode = (defined("BX_PUBLIC_MODE") && BX_PUBLIC_MODE && isset($_REQUEST["CRM_MANAGER_USER_ID"]));

if ($crmMode) {
    CUtil::DecodeUriComponent($_REQUEST);
    CUtil::DecodeUriComponent($_POST);
    
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"/bitrix/themes/.default/sale.css\" />";
}

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
IncludeModuleLangFile(__FILE__);

function CRMModeOutput($text)
{
    while(@ob_end_clean());
    echo $text;
    die();
}


/*
 * get template recomendet & busket product
 */
function fGetFormatedProduct($USER_ID, $LID, $arData, $CNT, $currency, $type, $crmMode = false)
{
    $result = "";

    if (!is_array($arData) || count($arData) <= 0) {
        return $result;
    }
    $result = "<table width=\"100%\">";
    if (CModule::IncludeModule('catalog') && CModule::IncludeModule('iblock')) {
        $arProductId = array();
        $arDataTab = array();
        foreach ($arData as $items) {
            if ($items["MODULE"] == 'catalog') {
                $arProductId[] = $items["PRODUCT_ID"];
                $arDataTab[$items["PRODUCT_ID"]] = $items;
            }
        }
        
        $dbProduct = CIBlockElement::GetList(array(), array("ID" => $arProductId), false, false, array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'DETAIL_PICTURE', 'PREVIEW_PICTURE', 'IBLOCK_TYPE_ID'));
        
        while ($arProduct = $dbProduct->Fetch()) {
            $arProduct = array_merge($arDataTab[$arProduct['ID']], $arProduct);

            if ($arProduct["PREVIEW_PICTURE"] == "" && $arProduct["DETAIL_PICTURE"] == "") {
                $arParent = CCatalogSku::GetProductInfo($arProduct["ID"]);
                if ($arParent) {
                    $dbProductTmp = CIBlockElement::GetList(array(), array("ID" => $arParent["ID"]), false, false, array('DETAIL_PICTURE', 'PREVIEW_PICTURE'));
                    $arProductTmp = $dbProductTmp->Fetch();
                    $arProduct["DETAIL_PICTURE"] = $arProductTmp["DETAIL_PICTURE"];
                    $arProduct["PREVIEW_PICTURE"] = $arProductTmp["PREVIEW_PICTURE"];
                }
            }


            if ($arProduct["IBLOCK_ID"] > 0) {
                $arProduct["EDIT_PAGE_URL"] = "/bitrix/admin/iblock_element_edit.php?ID=".$arProduct["PRODUCT_ID"]."&type=".$arProduct["IBLOCK_TYPE_ID"]."&lang=".LANG."&IBLOCK_ID=".$arProduct["IBLOCK_ID"]."&find_section_section=".$arProduct["IBLOCK_SECTION_ID"];
            }
            if ($arProduct["DETAIL_PICTURE"] > 0) {
                $imgCode = $arProduct["DETAIL_PICTURE"];
            } elseif ($arProduct["PREVIEW_PICTURE"] > 0) {
                $imgCode = $arProduct["PREVIEW_PICTURE"];
            }
            $arProduct["NAME"] = htmlspecialcharsex($arProduct["NAME"]);
            $arProduct["DETAIL_PAGE_URL"] = htmlspecialcharsex($arProduct["DETAIL_PAGE_URL"]);
            $arProduct["CURRENCY"] = htmlspecialcharsex($arProduct["CURRENCY"]);

            if ($imgCode > 0) {
                $arFile = CFile::GetFileArray($imgCode);
                $arImgProduct = CFile::ResizeImageGet($arFile, array('width'=>80, 'height'=>80), BX_RESIZE_IMAGE_PROPORTIONAL, false, false);
                if (is_array($arImgProduct)) {
                    $imgUrl = $arImgProduct["src"];
                    $imgProduct = "<a href=\"".$arProduct["EDIT_PAGE_URL"]."\" target=\"_blank\"><img src=\"".$arImgProduct["src"]."\" alt=\"\" title=\"".$arProduct["NAME"]."\" ></a>";
                }
            } else {
                $imgProduct = "<div class='no_foto'>".GetMessage('SOD_NO_FOTO')."</div>";
            }
            $result .= "<tr>
                            <td class=\"tab_img\">".$imgProduct."</td>
                            <td class=\"tab_text\">
                                <div class=\"order_name\"><a href=\"".$arProduct["EDIT_PAGE_URL"]."\" target=\"_blank\" title=\"".$arProduct["NAME"]."\">".$arProduct["NAME"]."</a></div>
                                <div class=\"order_price\">
                                    ".GetMessage('SOD_ORDER_RECOM_PRICE').": <b>".SaleFormatCurrency($arProduct["PRICE"], $currency)."</b>
                                </div>";

            $arResult = CSaleProduct::GetProductSku($USER_ID, $LID, $arProduct["PRODUCT_ID"], $arProduct["NAME"]);

            $arResult["POPUP_MESSAGE"] = array(
                "PRODUCT_ADD" => GetMEssage('SOD_POPUP_TO_BUSKET'),
                "PRODUCT_NOT_ADD" => GetMEssage('SOD_POPUP_TO_BUSKET_NOT'),
                "PRODUCT_PRICE_FROM" => GetMessage('SOD_POPUP_FROM')
            );

            if (!$crmMode) {
                if (count($arResult["SKU_ELEMENTS"]) > 0) {
                    $result .= "<a href=\"javascript:void(0);\" class=\"get_new_order\" onClick=\"fAddToBusketMoreProductSku(".CUtil::PhpToJsObject($arResult['SKU_ELEMENTS']).", ".CUtil::PhpToJsObject($arResult['SKU_PROPERTIES']).", '', ".CUtil::PhpToJsObject($arResult["POPUP_MESSAGE"])." );\"><span></span>".GetMessage('SOD_SUBTAB_ADD_ORDER')."</a>";
                } else {
                    $url = "/bitrix/admin/linemedia.auto_sale_order_new.php?lang=".LANG."&user_id=".$USER_ID."&LID=".$LID."&product[]=".$arProduct["PRODUCT_ID"];
                    $result .= "<a href=\"".$url."\" target=\"_blank\" class=\"get_new_order\"><span></span>".GetMessage('SOD_SUBTAB_ADD_ORDER')."</a>";
                }
            }

            $result .= "</td></tr>";
        }
    }//end if

    $result .= "<tr><td colspan='2' align='right' class=\"more_product\">";
    if ($CNT > 2)
        $result .= "<a href='javascript:void(0);' onClick=\"fGetMoreProduct('".$type."');\"  class=\"get_more\">".GetMessage('SOD_SUBTAB_MORE')."<span></span></a>";
    $result .= "</td></tr>";

    $result .= "</table>";

    return $result;
}

function fChangeOrderStatus($ID, $STATUS_ID)
{
    $errorMessageTmp = "";

    $STATUS_ID = trim($STATUS_ID);
    if (strlen($STATUS_ID) <= 0) {
        $errorMessageTmp .= GetMessage("ERROR_NO_STATUS").". ";
    }
    if (strlen($errorMessageTmp) <= 0) {
        if (!CSaleOrder::CanUserChangeOrderStatus($ID, $STATUS_ID, $GLOBALS["USER"]->GetUserGroupArray())) {
            $errorMessageTmp .= GetMessage("SOD_NO_PERMS2STATUS").". ";
        }
    }

    if (strlen($errorMessageTmp) <= 0) {
        if (!CSaleOrder::StatusOrder($ID, $STATUS_ID)) {
            if ($ex = $APPLICATION->GetException()) {
                if ($ex->GetID() != "ALREADY_FLAG")
                    $errorMessageTmp .= $ex->GetString();
            } else {
                $errorMessageTmp .= GetMessage("ERROR_CHANGE_STATUS").". ";
            }                
        }
    }

    $arResult = array();

    $dbOrder = CSaleOrder::GetList(
        array("ID" => "DESC"),
        array("ID" => $ID),
        false,
        false,
        array("DATE_STATUS", "EMP_STATUS_ID", "STATUS_ID")
    );
    if ($arOrder = $dbOrder->Fetch()) {
        $arResult["DATE_STATUS"] = $arOrder["DATE_STATUS"];
        if (!$crmMode && IntVal($arOrder["EMP_STATUS_ID"]) > 0) {
            $arResult["EMP_STATUS_ID"] = GetFormatedUserName($arOrder["EMP_STATUS_ID"]);
        }
        $arResult["STATUS_ID"] = $arOrder["STATUS_ID"];
    }
    
    return $arResult;
}


$ID = intval($_REQUEST["ID"]);
$errorMessage = "";

if ($ID <= 0) {
    if ($crmMode) {
        CRMModeOutput("Order is not found");
    } else {
        LocalRedirect("linemedia.auto_sale_orders_list.php?lang=".LANG.GetFilterParams("filter_", false));
    }
}

$customTabber = new CAdminTabEngine("OnAdminSaleOrderView", array("ID" => $ID));

$arTransactTypes = array(
    "ORDER_PAY" => GetMessage("SOD_PAYMENT"),
    "CC_CHARGE_OFF" => GetMessage("SOD_FROM_CARD"),
    "OUT_CHARGE_OFF" => GetMessage("SOD_INPUT"),
    "ORDER_UNPAY" => GetMessage("SOD_CANCEL_PAYMENT"),
    "ORDER_CANCEL_PART" => GetMessage("SOD_CANCEL_SEMIPAYMENT"),
    "MANUAL" => GetMessage("SOD_HAND"),
    "DEL_ACCOUNT" => GetMessage("SOD_DELETE"),
    "AFFILIATE" => GetMessage("SOD1_AFFILIATES_PAY"),
);

$bUserCanViewOrder = CSaleOrder::CanUserViewOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
$bUserCanEditOrder = CSaleOrder::CanUserUpdateOrder($ID, $GLOBALS["USER"]->GetUserGroupArray());
$bUserCanCancelOrder = CSaleOrder::CanUserCancelOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
$bUserCanPayOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "PERM_PAYMENT", $GLOBALS["USER"]->GetUserGroupArray());
$bUserCanDeliverOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "PERM_DELIVERY", $GLOBALS["USER"]->GetUserGroupArray());
$bUserCanDeleteOrder = CSaleOrder::CanUserDeleteOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());

/*
 * Объект заказа.
 */
$lmorder = new LinemediaAutoOrder($ID);

$bUserAllowPay = ($lmorder->getAllowPayemnt() == 'Y');

if (isset($ORDER_AJAX) && $ORDER_AJAX == "Y" && check_bitrix_sessid()) {
    CUtil::DecodeUriComponent($_REQUEST);
    
    
    /*
     * Событие дял других модулей: получение данных запроса.
     */
    $events = GetModuleEvents("linemedia.auto", "OnDetailOrderGetRequestData");
    while ($arEvent = $events->Fetch()) {
        ExecuteModuleEventEx($arEvent, array($ID, $_REQUEST));
    }
    
    
    /*
     * get more product
     */
    if (isset($type) && $type != "") {
        $arResult = array();
        $arErrors = array();
        $userId = intval($userId);
        $fuserId = intval($fUserId);
        $arOrderProduct = CUtil::JsObjectToPhp($arProduct);

        if ($type == 'busket') {
            $arShoppingCart = CSaleBasket::DoGetUserShoppingCart($LID, $userId, $fuserId, $arErrors, array());
            if (count($arShoppingCart) > 0) {
                $arResult["ITEMS"] = fGetFormatedProduct($userId, $LID, $arShoppingCart, 1, $currency, $type, $crmMode);
            } else {
                $arResult["ITEMS"] = GetMessage('SOD_SUBTAB_BUSKET_NULL');
            }
        }
        if ($type == 'recom') {
            if (!is_array($arOrderProduct))
                $arOrderProduct = explode(",", $arOrderProduct);
            $arRecomendetResult = CSaleProduct::GetRecommendetProduct($userId, $LID, $arOrderProduct, "Y");
            $arResult["ITEMS"] = fGetFormatedProduct($userId, $LID, $arRecomendetResult, 1, $currency, $type, $crmMode);
        }
        if ($type == 'viewed') {
            $arViewed = array();
            $dbViewsList = CSaleViewedProduct::GetList(
                    array("DATE_VISIT"=>"DESC"),
                    array("FUSER_ID" => $fuserId, ">PRICE" => 0, "!CURRENCY" => ""),
                    false,
                    array('nTopCount' => 10),
                    array('ID', 'PRODUCT_ID', 'LID', 'MODULE', 'NAME', 'DETAIL_PAGE_URL', 'PRICE', 'CURRENCY', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
                );
            while ($arViews = $dbViewsList->Fetch())
                $arViewed[] = $arViews;

            $arResult["ITEMS"] =  fGetFormatedProduct($userId, $LID, $arViewed, 1, $currency, $type, $crmMode);
        }

        $arResult["TYPE"] = $type;
        $result = CUtil::PhpToJSObject($arResult);

        CRMModeOutput($result);
        exit;
    }

    /*
     * Сохранение коммента
     */
    if (strlen($comment) > 0) {
        $ID = IntVal($ID);
        $comment = trim($comment);
        
        $bUserCanEditOrder = CSaleOrder::CanUserUpdateOrder($ID, $GLOBALS["USER"]->GetUserGroupArray());

        if (isset($change) && $change == "Y" && $bUserCanEditOrder && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
            CUtil::DecodeUriComponent($comment);
            CSaleOrder::CommentsOrder($ID, $comment);
        }
        $arResult = array('message' => 'ok');
        $result = CUtil::PhpToJSObject($arResult);

        CRMModeOutput($result);
        exit;
    }

    /*
     * Причина отмены
     */
    if (isset($_REQUEST["change_cancel"]) && $_REQUEST["change_cancel"] == "Y") {
        $errorMessageTmp = "";
        $arResult = array();
        
        if (!$bUserCanCancelOrder) {
            $errorMessageTmp .= GetMessage("SOD_NO_PERMS2CANCEL").". ";
        }
        if (strlen($errorMessageTmp) <= 0) {
            $CANCELED = trim($_REQUEST["CANCELED"]);
            $REASON_CANCELED = trim($_REQUEST["REASON_CANCELED"]);
            if ($CANCELED != "Y") {
                $CANCELED = "N";
            }
            if ($CANCELED != "Y" && $CANCELED != "N")
                $errorMessageTmp .= GetMessage("SOD_WRONG_CANCEL_FLAG").". ";
        }

        if (strlen($errorMessageTmp) <= 0 && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
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

        $arResult["message"] = "ok";
        if (strlen($errorMessageTmp) > 0) {
            $arResult["message"] = $errorMessageTmp;
        } elseif (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
            $dbOrder = CSaleOrder::GetList(
                array("ID" => "DESC"),
                array("ID" => $ID),
                false,
                false,
                array("DATE_CANCELED", "EMP_CANCELED_ID")
            );
            if ($arOrder = $dbOrder->Fetch()) {
                $arResult["DATE_CANCELED"] = CUtil::JSEscape($arOrder["DATE_CANCELED"]);
                if (!$crmMode && IntVal($arOrder["EMP_CANCELED_ID"]) > 0)
                    $arResult["EMP_CANCELED_ID"] = CUtil::JSEscape(GetFormatedUserName($arOrder["EMP_CANCELED_ID"]));
            }
        }

        $result = CUtil::PhpToJSObject($arResult);

        CRMModeOutput($result);
        exit;
    }


    /*
     * Доставка
     */
    if (isset($_REQUEST["change_delivery_form"]) && $_REQUEST["change_delivery_form"] == "Y")
    {
        $errorMessageTmp = "";

        if (!$bUserCanDeliverOrder)
            $errorMessageTmp .= GetMessage("SOD_NO_PERMS2DELIV").". ";

        if (strlen($errorMessageTmp) <= 0)
        {
            $ALLOW_DELIVERY = trim($_REQUEST["ALLOW_DELIVERY"]);
            if ($ALLOW_DELIVERY != "Y")
                $ALLOW_DELIVERY = "N";
            if ($ALLOW_DELIVERY != "Y" && $ALLOW_DELIVERY != "N")
                $errorMessageTmp .= GetMessage("SOD_WRONG_DELIV_FLAG").". ";
        }

        if (strlen($errorMessageTmp) <= 0 && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
            $arAdditionalFields = array(
                "DELIVERY_DOC_NUM" => ((strlen($_REQUEST["DELIVERY_DOC_NUM"]) > 0) ? $_REQUEST["DELIVERY_DOC_NUM"] : False),
                "DELIVERY_DOC_DATE" => ((strlen($_REQUEST["DELIVERY_DOC_DATE"]) > 0) ? $_REQUEST["DELIVERY_DOC_DATE"] : False)
            );

            if ($change_status_popup == "Y")
                $arAdditionalFields["NOT_CHANGE_STATUS"] = "Y";

            if (!CSaleOrder::DeliverOrder($ID, $ALLOW_DELIVERY, 0, $arAdditionalFields)) {
                if ($ex = $APPLICATION->GetException()) {
                    if ($ex->GetID() != "ALREADY_FLAG") {
                        $errorMessageTmp .= $ex->GetString();
                    }
                } else {
                    $errorMessageTmp .= GetMessage("ERROR_DELIVERY_ORDER").". ";
                }
            }

            unset($arAdditionalFields["NOT_CHANGE_STATUS"]);
            
            //update for change data
            $res = CSaleOrder::Update($ID, $arAdditionalFields);
        }

        $arResult["message"] = "ok";
        $arResult["ALLOW_DELIVERY"] = $ALLOW_DELIVERY;
        if (strlen($errorMessageTmp) > 0) {
            $arResult["message"] = $errorMessageTmp;
        } elseif (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
            $dbOrder = CSaleOrder::GetList(
                array("ID" => "DESC"),
                array("ID" => $ID),
                false,
                false,
                array("DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID", "STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID")
            );
            if ($arOrder = $dbOrder->Fetch())
            {
                $arResult["DATE_ALLOW_DELIVERY"] = $arOrder["DATE_ALLOW_DELIVERY"];
                if (!$crmMode && IntVal($arOrder["EMP_ALLOW_DELIVERY_ID"]) > 0)
                    $arResult["EMP_ALLOW_DELIVERY_ID"] = GetFormatedUserName($arOrder["EMP_ALLOW_DELIVERY_ID"]);

                $arResult["DATE_STATUS"] = $arOrder["DATE_STATUS"];
                if (!$crmMode && IntVal($arOrder["EMP_STATUS_ID"]) > 0)
                    $arResult["EMP_STATUS_ID"] = GetFormatedUserName($arOrder["EMP_STATUS_ID"]);

                $arResult["STATUS_ID"] = $arOrder["STATUS_ID"];
            }

            $arResult["DELIVERY_DOC_NUMBER_FORMAT"] = GetMessage("SOD_DELIV_DOC", Array("#NUM#" => htmlspecialcharsEx($_REQUEST["DELIVERY_DOC_NUM"]), "#DATE#" => htmlspecialcharsEx($_REQUEST["DELIVERY_DOC_DATE"])));
        }

        if (isset($_REQUEST["change_status"]) && $_REQUEST["change_status"] == "Y") {
            $arResultTmp = fChangeOrderStatus($ID, $_REQUEST["STATUS_ID"]);
            $arResult = array_merge($arResult, $arResultTmp);
        }

        $result = CUtil::PhpToJSObject($arResult);

        CRMModeOutput($result);
        exit;
    }
    
    
    /*
     * Платежная система
     */
    if (isset($_REQUEST["change_allow_form"]) && $_REQUEST["change_allow_form"] == "Y")
    {
        $ALLOW = trim((string) $_REQUEST['ALLOW']);
        
        if (!$lmorder->setAllowPayemnt($ALLOW)) {
            $errorMessageTmp .= GetMessage("SOD_WRONG_PAYALLOW").". ";
        }
        
        $arResult["message"] = "ok";
        $arResult["ALLOW"] = $ALLOW;
        
        if (strlen($errorMessageTmp) <= 0 && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
            if (isset($_REQUEST["change_status"]) && $_REQUEST["change_status"] == "Y") {
                $arResultTmp = fChangeOrderStatus($ID, $_REQUEST["STATUS_ID"]);
                $arResult = array_merge($arResult, $arResultTmp);
            }
        }
        
        if (strlen($errorMessageTmp) > 0) {
            $arResult["message"] = $errorMessageTmp;
        }
        
        $result = CUtil::PhpToJSObject($arResult);

        CRMModeOutput($result);
        exit;
    }
    
    /*
     * Платежная система
     */
    if (isset($_REQUEST["change_pay_form"]) && $_REQUEST["change_pay_form"] == "Y")
    {
        $errorMessageTmp = "";

        if (!$bUserCanPayOrder) {
            $errorMessageTmp .= GetMessage("SOD_NO_PERMS2PAYFLAG").". ";
        }
        if (strlen($errorMessageTmp) <= 0) {
            $PAYED = trim($_REQUEST["PAYED"]);
            if ($PAYED != "Y") {
                $PAYED = "N";
            }
            if ($PAYED != "Y" && $PAYED != "N") {
                $errorMessageTmp .= GetMessage("SOD_WRONG_PAYFLAG").". ";
            }
        }
        
        if (strlen($errorMessageTmp) <= 0 && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
            $arAdditionalFields = array(
                "PAY_VOUCHER_NUM" => ((strlen($_REQUEST["PAY_VOUCHER_NUM"]) > 0) ? $_REQUEST["PAY_VOUCHER_NUM"] : False),
                "PAY_VOUCHER_DATE" => ((strlen($_REQUEST["PAY_VOUCHER_DATE"]) > 0) ? $_REQUEST["PAY_VOUCHER_DATE"] : False)
            );
            
            $bWithdraw = true;
            $bPay = true;
            if ($_REQUEST["PAY_FROM_ACCOUNT"] == "Y") {
                $bPay = false;
            }
            if ($PAYED == "N" && $_REQUEST["PAY_FROM_ACCOUNT_BACK"] != "Y") {
                $bWithdraw = false;
            }
            if ($change_status_popup == "Y") {
                $arAdditionalFields["NOT_CHANGE_STATUS"] = "Y";
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

            unset($arAdditionalFields["NOT_CHANGE_STATUS"]);
            
            //update for change data
            $res = CSaleOrder::Update($ID, $arAdditionalFields);
        }

        $arResult["message"] = "ok";
        $arResult["PAYED"] = $PAYED;
        $arResult["ALLOW"] = $ALLOW;
        $arResult["BUDGET_ENABLE"] = 'N';
        
        if (strlen($errorMessageTmp) > 0) {
            $arResult["message"] = $errorMessageTmp;
        } elseif (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
            $dbOrder = CSaleOrder::GetList(
                array("ID" => "DESC"),
                array("ID" => $ID),
                false,
                false,
                array("DATE_PAYED", "EMP_PAYED_ID", "STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID", "PRICE", "USER_ID", "CURRENCY")
            );
            if ($arOrder = $dbOrder->Fetch()) {
                $arResult["DATE_PAYED"] = trim($arOrder["DATE_PAYED"]);
                if (!$crmMode && IntVal($arOrder["EMP_PAYED_ID"]) > 0) {
                    $arResult["EMP_PAYED_ID"] = GetFormatedUserName($arOrder["EMP_PAYED_ID"]);
                }
                $arResult["DATE_STATUS"] = $arOrder["DATE_STATUS"];
                if (!$crmMode && IntVal($arOrder["EMP_STATUS_ID"]) > 0) {
                    $arResult["EMP_STATUS_ID"] = GetFormatedUserName($arOrder["EMP_STATUS_ID"]);
                }
                $arResult["STATUS_ID"] = $arOrder["STATUS_ID"];

                //user budget
                $dbUserAccount = CSaleUserAccount::GetList(
                    array(),
                    array(
                        "USER_ID" => $arOrder["USER_ID"],
                        "CURRENCY" => $arOrder["CURRENCY"],
                    )
                );
                $arUserAccount = $dbUserAccount->GetNext();
                if (floatval($arUserAccount["CURRENT_BUDGET"]) >= floatval($arOrder["PRICE"])) {
                    $arResult["BUDGET_ENABLE"] = 'Y';
                    $arResult["BUDGET_USER"] = SaleFormatCurrency(floatval($arUserAccount["CURRENT_BUDGET"]), $arOrder["CURRENCY"]);
                }
            }

            if (strlen(trim($_REQUEST["PAY_VOUCHER_NUM"])) > 0)
                $arResult["PAY_DOC_NUMBER_FORMAT"] = str_replace("#DATE#", $_REQUEST["PAY_VOUCHER_DATE"], str_replace("#NUM#", htmlspecialcharsEx($_REQUEST["PAY_VOUCHER_NUM"]), GetMessage("SOD_PAY_DOC")));
        }

        if (isset($_REQUEST["change_status"]) && $_REQUEST["change_status"] == "Y") {
            $arResultTmp = fChangeOrderStatus($ID, $_REQUEST["STATUS_ID"]);
            $arResult = array_merge($arResult, $arResultTmp);
        }

        $result = CUtil::PhpToJSObject($arResult);

        CRMModeOutput($result);
        exit;
    }

    /*
     * Изменение статуса
     */
    if (isset($_REQUEST["change_status"]) && $_REQUEST["change_status"] == "Y")
    {
        $arResult = array();
        
        if (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
            $arResult = fChangeOrderStatus($ID, $_REQUEST["STATUS_ID"]);
        }
        $result = CUtil::PhpToJSObject($arResult);

        CRMModeOutput($result);
        exit;
    }
}


/****************/
if ($saleModulePermissions >= "U" && check_bitrix_sessid() && empty($dontsave))
{
    if (!$customTabber->Check()) {
        if($ex = $APPLICATION->GetException())
            $errorMessage .= $ex->GetString();
        else
            $errorMessage .= "Error. ";
    } elseif ($REQUEST_METHOD == "POST" && $save_order_data == "Y") {
        if (CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
            $errorMessage .= str_replace(array("#DATE#", "#ID#"), array($dateLock, $lockedBY), GetMessage("SOE_ORDER_LOCKED")).". ";
        } else {
            if (strlen($errorMessage) <= 0) {
                if ($crmMode) {
                    CRMModeOutput($ID);
                }
                LocalRedirect("linemedia.auto_sale_order_detail.php?ID=".$ID."&save_order_result=ok&lang=".LANG.GetFilterParams("filter_", false));
            }
        }
    }
    elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "ps_update")
    {
        $errorMessageTmp = "";

        $arOrder = CSaleOrder::GetByID($ID);
        if (!$arOrder) {
            $errorMessageTmp .= GetMessage("ERROR_NO_ORDER")."<br>";
        }
        if (strlen($errorMessageTmp) <= 0) {
            $psResultFile = "";

            $arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]);

            $psActionPath = $_SERVER["DOCUMENT_ROOT"].$arPaySys["PSA_ACTION_FILE"];
            $psActionPath = str_replace("\\", "/", $psActionPath);
            while (substr($psActionPath, strlen($psActionPath) - 1, 1) == "/") {
                $psActionPath = substr($psActionPath, 0, strlen($psActionPath) - 1);
            }
            if (file_exists($psActionPath) && is_dir($psActionPath))
            {
                if (file_exists($psActionPath."/result.php") && is_file($psActionPath."/result.php"))
                    $psResultFile = $psActionPath."/result.php";
            }
            elseif (strlen($arPaySys["PSA_RESULT_FILE"]) > 0)
            {
                if (file_exists($_SERVER["DOCUMENT_ROOT"].$arPaySys["PSA_RESULT_FILE"])
                    && is_file($_SERVER["DOCUMENT_ROOT"].$arPaySys["PSA_RESULT_FILE"]))
                    $psResultFile = $_SERVER["DOCUMENT_ROOT"].$arPaySys["PSA_RESULT_FILE"];
            }

            if (strlen($psResultFile) <= 0)
                $errorMessageTmp .= GetMessage("SOD_NO_PS_SCRIPT").". ";
        }

        if (strlen($errorMessageTmp) <= 0) {
            $ORDER_ID = $ID;
            CSalePaySystemAction::InitParamArrays($arOrder, $ID, $arPaySys["PSA_PARAMS"]);
            if (!include($psResultFile))
                $errorMessageTmp .= GetMessage("ERROR_CONNECT_PAY_SYS").". ";
        }

        if (strlen($errorMessageTmp) <= 0) {
            $ORDER_ID = IntVal($ORDER_ID);
            $arOrder = CSaleOrder::GetByID($ORDER_ID);
            if (!$arOrder)
                $errorMessageTmp .= str_replace("#ID#", $ORDER_ID, GetMessage("SOD_NO_ORDER")).". ";
        }
        
        if (strlen($errorMessageTmp) <= 0) {
            if ($arOrder["PS_STATUS"] == "Y" && $arOrder["PAYED"] == "N")
            {
                if ($arOrder["CURRENCY"] == $arOrder["PS_CURRENCY"]
                    && doubleval($arOrder["PRICE"]) == doubleval($arOrder["PS_SUM"]))
                {
                    if (!CSaleOrder::PayOrder($arOrder["ID"], "Y", True, True))
                    {
                        if ($ex = $APPLICATION->GetException())
                            $errorMessageTmp .= $ex->GetString();
                        else
                            $errorMessageTmp .= str_replace("#ID#", $ORDER_ID, GetMessage("SOD_CANT_PAY")).". ";
                    }
                }
            }
        }

        if ($errorMessageTmp != "") {
            $errorMessage .= $errorMessageTmp;
        }
        if (strlen($errorMessage) <= 0) {
            if ($crmMode)
                CRMModeOutput($ID);

            if (strlen($apply) > 0 || $_REQUEST["action"] == "ps_update")
                LocalRedirect("linemedia.auto_sale_order_detail.php?ID=".$ID."&save_order_result=ok_ps&lang=".LANG.GetFilterParams("filter_", false));

            CSaleOrder::UnLock($ID);
            LocalRedirect("linemedia.auto_sale_orders_list.php?lang=".LANG.GetFilterParams("filter_", false));
        }
    }
}
elseif (!empty($dontsave))
{
    CSaleOrder::UnLock($ID);
    if ($crmMode)
        CRMModeOutput($ID);

    LocalRedirect("linemedia.auto_sale_orders_list.php?lang=".LANG.GetFilterParams("filter_", false));
}
/****************/

$dbOrder = CSaleOrder::GetList(
    array("ID" => "DESC"),
    array("ID" => $ID),
    false,
    false,
    array("ID", "LID", "PERSON_TYPE_ID", "PAYED", "DATE_PAYED", "EMP_PAYED_ID", "CANCELED", "DATE_CANCELED", "EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "EMP_STATUS_ID", "PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID", "PRICE", "CURRENCY", "DISCOUNT_VALUE", "SUM_PAID", "USER_ID", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "DATE_INSERT_FORMAT", "DATE_UPDATE", "USER_DESCRIPTION", "ADDITIONAL_INFO", "PS_STATUS", "PS_STATUS_CODE", "PS_STATUS_DESCRIPTION", "PS_STATUS_MESSAGE", "PS_SUM", "PS_CURRENCY", "PS_RESPONSE_DATE", "COMMENTS", "TAX_VALUE", "STAT_GID", "RECURRING_ID", "AFFILIATE_ID", "LOCK_STATUS", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "USER_EMAIL", "DELIVERY_DOC_NUM", "DELIVERY_DOC_DATE")
);
if (!($arOrder = $dbOrder->Fetch())) {
    LocalRedirect("linemedia.auto_sale_orders_list.php?lang=".LANG.GetFilterParams("filter_", false));
}
$WEIGHT_UNIT = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_unit', "", $arOrder["LID"]));
$WEIGHT_KOEF = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_koef', 1, $arOrder["LID"]));

$APPLICATION->SetTitle(GetMessage("SALE_EDIT_RECORD", array("#ID#"=>$ID)));

//get history order list
$arFieldsAll = array(
        "PERSON_TYPE_ID" => GetMessage('SOD_HIST_PERSON_TYPE_ID'),
        "PAYED" => GetMessage('SOD_HIST_PAYED'),
        "DATE_PAYED" => GetMessage('SOD_HIST_DATE_PAYED'),
        "EMP_PAYED_ID" => GetMessage('SOD_HIST_EMP_PAYED_ID'),
        "CANCELED" => GetMessage('SOD_HIST_CANCELED'),
        "DATE_CANCELED" => GetMessage('SOD_HIST_DATE_CANCELED'),
        "EMP_CANCELED_ID" => GetMessage('SOD_HIST_EMP_CANCELED_ID'),
        "REASON_CANCELED" => GetMessage('SOD_HIST_REASON_CANCELED'),
        "STATUS_ID" => GetMessage('SOD_HIST_STATUS_ID'),
        "DATE_STATUS" => GetMessage('SOD_HIST_DATE_STATUS'),
        "EMP_STATUS_ID" => GetMessage('SOD_HIST_EMP_STATUS_ID'),
        "PRICE_DELIVERY" => GetMessage('SOD_HIST_PRICE_DELIVERY'),
        "ALLOW_DELIVERY" => GetMessage('SOD_HIST_ALLOW_DELIVERY'),
        "DATE_ALLOW_DELIVERY" => GetMessage('SOD_HIST_DATE_ALLOW_DELIVERY'),
        "EMP_ALLOW_DELIVERY_ID" => GetMessage('SOD_HIST_EMP_ALLOW_DELIVERY_ID'),
        "PRICE" => GetMessage('SOD_HIST_PRICE'),
        "CURRENCY" => GetMessage('SOD_HIST_CURRENCY'),
        "DISCOUNT_VALUE" => GetMessage('SOD_HIST_DISCOUNT_VALUE'),
        "USER_ID" => GetMessage('SOD_HIST_USER_ID'),
        "PAY_SYSTEM_ID" => GetMessage('SOD_HIST_PAY_SYSTEM_ID'),
        "DELIVERY_ID" => GetMessage('SOD_HIST_DELIVERY_ID'),
        "PS_STATUS" => GetMessage('SOD_HIST_PS_STATUS'),
        "PS_STATUS_CODE" => GetMessage('SOD_HIST_PS_STATUS_CODE'),
        "PS_STATUS_DESCRIPTION" => GetMessage('SOD_HIST_PS_STATUS_DESCRIPTION'),
        "PS_STATUS_MESSAGE" => GetMessage('SOD_HIST_PS_STATUS_MESSAGE'),
        "PS_SUM" => GetMessage('SOD_HIST_PS_SUM'),
        "PS_CURRENCY" => GetMessage('SOD_HIST_PS_CURRENCY'),
        "PS_RESPONSE_" => GetMessage('SOD_HIST_PS_RESPONSE_'),
        "TAX_VALUE" => GetMessage('SOD_HIST_TAX_VALUE'),
        "STAT_GID" => GetMessage('SOD_HIST_STAT_GID'),
        "SUM_PAID" => GetMessage('SOD_HIST_SUM_PAID'),
        "RECURRING_ID" => GetMessage('SOD_HIST_RECURRING_ID'),
        "PAY_VOUCHER_NUM" => GetMessage('SOD_HIST_PAY_VOUCHER_NUM'),
        "PAY_VOUCHER_DATE" => GetMessage('SOD_HIST_PAY_VOUCHER_DATE'),
        "RECOUNT_FLAG" => GetMessage('SOD_HIST_RECOUNT_FLAG'),
        "AFFILIATE_ID" => GetMessage('SOD_HIST_AFFILIATE_ID'),
        "DELIVERY_DOC_NUM" => GetMessage('SOD_HIST_DELIVERY_DOC_NUM'),
        "DELIVERY_DOC_DATE" => GetMessage('SOD_HIST_DELIVERY_DOC_DATE')
    );

//get status order
$arOrderStatus = array();
$dbStatusList = CSaleStatus::GetList(
    array("SORT" => "ASC"),
    array("LID" => LANGUAGE_ID),
    false,
    false,
    array("ID", "NAME")
);
while ($arStatusList = $dbStatusList->Fetch()) {
    $arOrderStatus[htmlspecialcharsbx($arStatusList["ID"])] = htmlspecialcharsbx($arStatusList["NAME"]);
}
//get delivery
$arDelivery = array();
$dbDeliveryList = CSaleDelivery::GetList(
        array("SORT" => "ASC"),
        array()
        );
while ($arDeliveryList = $dbDeliveryList->Fetch()) {
    $arDelivery[$arDeliveryList["ID"]] = htmlspecialcharsbx($arDeliveryList["NAME"]);
}
//get paysystem
$arPaySystem = array();
$dbPaySystemList = CSalePaySystem::GetList(
        array("SORT"=>"ASC"),
        array()
        );
while ($arPaySystemList = $dbPaySystemList->Fetch()) {
    $arPaySystem[$arPaySystemList["ID"]] = htmlspecialcharsbx($arPaySystemList["NAME"]);
}

$sTableID_tab5 = "table_history";
$oSort_tab5 = new CAdminSorting($sTableID_tab5);
$lAdmin_tab5 = new CAdminList($sTableID_tab5, $oSort_tab5);

//FILTER HISTORY
$arFilterFields = array(
    "filter_user",
    "filter_date_history",
    "filter_status_id",
    "filter_payed",
    "filter_allow_delivery",
    "filter_canceled"
);
$lAdmin_tab5->InitFilter($arFilterFields);


if (!isset($_REQUEST["by"]))
    $arHistSort = array("H_DATE_INSERT" => "DESC");
else
    $arHistSort[$by] = $order;

$arFilterHistory = array("H_ORDER_ID" => $ID);

if (strlen($filter_allow_delivery)>0) $arFilterHistory["ALLOW_DELIVERY"] = trim($filter_allow_delivery);
if (strlen($filter_canceled)>0) $arFilterHistory["CANCELED"] = trim($filter_canceled);
if (strlen($filter_payed)>0) $arFilterHistory["PAYED"] = trim($filter_payed);
if (isset($filter_status_id) && !is_array($filter_status_id) && strlen($filter_status_id) > 0)
    $filter_status_id = array($filter_status_id);
if (isset($filter_status_id) && is_array($filter_status_id) && count($filter_status_id) > 0) {
    for ($i = 0; $i < count($filter_status_id); $i++) {
        $filter_status_id[$i] = Trim($filter_status_id[$i]);
        if (strlen($filter_status_id[$i]) > 0)
            $arFilterHistory["STATUS_ID"][] = $filter_status_id[$i];
    }
}
if (IntVal($filter_user)>0) $arFilterHistory["H_USER_ID"] = IntVal($filter_user);

if (strlen($filters_date_history_from)>0) {
    $arFilterHistory["H_DATE_INSERT_FROM"] = Trim($filters_date_history_from);
}
elseif($set_filter!="Y" && $del_filter != "Y")
{
    $filter_date_update_from_DAYS_TO_BACK = COption::GetOptionString("sale", "order_list_date", 30);
    $filters_date_history_from = GetTime(time()-86400*COption::GetOptionString("sale", "order_list_date", 30));
    $arFilterHistory["H_DATE_INSERT_FROM"] = $filters_date_history_from;
}

if (strlen($filters_date_history_to)>0) {
    if ($arDate = ParseDateTime($filters_date_history_to, CSite::GetDateFormat("FULL", SITE_ID))) {
        if (StrLen($filters_date_history_to) < 11) {
            $arDate["HH"] = 23;
            $arDate["MI"] = 59;
            $arDate["SS"] = 59;
        }

        $filters_date_history_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
        $arFilterHistory["H_DATE_INSERT_TO"] = $filters_date_history_to;
    } else {
        $filters_date_history_to = "";
    }
}


/*
 * Метод доступен при обновлениии.
 */
if (method_exists('CSaleOrder', 'GetHistoryList')) {

    $dbHistory = CSaleOrder::GetHistoryList(
            $arHistSort,
            $arFilterHistory,
            false,
            false,
            array("*")
        );
    
    $dbHistory = new CAdminResult($dbHistory, $sTableID_tab5);
    $dbHistory->NavStart();
    $lAdmin_tab5->NavText($dbHistory->GetNavPrint(GetMessage('SOD_HIST_LIST')));
    
    $histdHeader = array(
        array("id"=>"H_DATE_INSERT", "content"=>GetMessage("SOD_HIST_H_DATE"), "sort"=>"H_DATE_INSERT", "default"=>true),
        array("id"=>"H_USER_ID","content"=>GetMessage("SOD_HIST_H_USER"), "sort"=>"H_USER_ID", "default"=>true),
        array("id"=>"STATUS_ID", "content"=>$arFieldsAll["STATUS_ID"], "sort"=>"STATUS_ID", "default"=>true),
        array("id"=>"PAYED", "content"=>$arFieldsAll["PAYED"], "sort"=>"PAYED", "default"=>true),
        array("id"=>"ALLOW_DELIVERY", "content"=>$arFieldsAll["ALLOW_DELIVERY"], "sort"=>"ALLOW_DELIVERY", "default"=>true),
        array("id"=>"CANCELED", "content"=>$arFieldsAll["CANCELED"], "sort"=>"CANCELED", "default"=>true),
        array("id"=>"PRICE", "content"=>$arFieldsAll["PRICE"], "sort"=>"PRICE", "default"=>true),
        array("id"=>"MORE", "content"=>GetMessage("SOD_HIST_H_MORE"), "sort"=>"", "default"=>true),
    );
    
    $lAdmin_tab5->AddHeaders($histdHeader);
    $arDeleteFields = array("ID", "H_USER_ID", "H_DATE_INSERT", "H_CURRENCY", "H_ORDER_ID", "EMP_CANCELED_ID", "EMP_STATUS_ID", "EMP_ALLOW_DELIVERY_ID", "STATUS_ID", "PAYED", "ALLOW_DELIVERY", "CANCELED", "PRICE");
    
    while ($arHistory = $dbHistory->Fetch()) {
        $row =& $lAdmin_tab5->AddRow($arHistory["ID"], $arHistory, '', '');
    
        $stmp = MakeTimeStamp($arHistory["H_DATE_INSERT"], "DD.MM.YYYY HH:MI:SS");
        $row->AddField("H_DATE_INSERT", date("d.m.Y H:i", $stmp));
        $row->AddField("H_USER_ID", GetFormatedUserName($arHistory["H_USER_ID"], false));
        
        $payed = "";
        if ($arHistory["PAYED"] == "Y")
            $payed = GetMessage("SOD_HIST_YES");
        elseif ($arHistory["PAYED"] == "N")
            $payed = GetMessage("SOD_HIST_NO");
        $row->AddField("PAYED", $payed);
    
        $allo_delivery = "";
        if ($arHistory["ALLOW_DELIVERY"] == "Y")
            $allo_delivery = GetMessage("SOD_HIST_YES");
        elseif ($arHistory["ALLOW_DELIVERY"] == "N")
            $allo_delivery = GetMessage("SOD_HIST_NO");
        $row->AddField("ALLOW_DELIVERY", $allo_delivery);
    
        $cancel = "";
        if ($arHistory["CANCELED"] == "Y")
            $cancel = GetMessage("SOD_HIST_YES");
        elseif ($arHistory["CANCELED"] == "N")
            $cancel = GetMessage("SOD_HIST_NO");
        $row->AddField("CANCELED", $cancel);
    
        $row->AddField("STATUS_ID", $arOrderStatus[$arHistory['STATUS_ID']]);
    
        $price = "&nbsp;";
        if (floatval($arHistory["PRICE"]) > 0)
            $price = SaleFormatCurrency($arHistory["PRICE"], $arHistory["H_CURRENCY"]);
    
        $row->AddField("PRICE", $price);
    
        $fieldMore = "";
        foreach ($arHistory as $key => $val) {
            if (strlen($val) > 0 && !in_array($key, $arDeleteFields)) {
                if ($key == "PRICE" || $key == "PRICE_DELIVERY" || $key == "DISCOUNT_VALUE" || $key == "TAX_VALUE" || $key == "SUM_PAID")
                    $val = SaleFormatCurrency($val, $arHistory["H_CURRENCY"]);
    
                if ($key == "STATUS_ID")
                    $val = $arOrderStatus[$val];
                elseif ($key == "DELIVERY_ID")
                    $val = $arDelivery[$val];
                elseif ($key == "PAY_SYSTEM_ID")
                    $val = $arPaySystem[$val];
                elseif ($key == "USER_ID")
                    $val = GetFormatedUserName($val);
                elseif ($key == "EMP_PAYED_ID")
                    $val = GetFormatedUserName($val);
    
                $fieldMore .= $arFieldsAll[$key].": ".trim($val)."<br>";
            }
        }
        $row->AddField("MORE", $fieldMore);
    }
    
    if ($_REQUEST["table_id"]==$sTableID_tab5) {
        $lAdmin_tab5->CheckListMode();
    }
    //end get history order list

} else {
    
    $message = new CAdminMessage(array(
        'TYPE' => 'ERROR',
        'MESSAGE' => GetMessage('LM_AUTO_SOD_INFORM_NEED_UPDATE'),
        'DETAILS' => GetMessage('LM_AUTO_SOD_ERROR_HIST_METHOD'),
        'HTML' => true
    ));
}

$aTabs   = array();
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("SODN_TAB_ORDER"), "TITLE" => GetMessage("SODN_TAB_ORDER_DESCR"), "ICON" => "sale");
$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("SODN_TAB_TRANSACT"), "TITLE" => GetMessage("SODN_TAB_TRANSACT_DESCR"), "ICON" => "sale");
$aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("SODN_TAB_HISTORY"), "TITLE" => GetMessage("SODN_TAB_HISTORY_DESCR"), "ICON" => "sale");

$tabControl = new CAdminForm("order_view", $aTabs);

$tabControl->AddTabs($customTabber);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
        array(
                "TEXT" => GetMessage("SOD_TO_LIST"),
                "LINK" => "/bitrix/admin/linemedia.auto_sale_orders_list.php?ID=".$ID."&dontsave=Y&lang=".LANGUAGE_ID.GetFilterParams("filter_"),
                "ICON"=>"btn_list",
            )
    );

$aMenu[] = array("SEPARATOR" => "Y");

if ($bUserCanEditOrder)
{
    $aMenu[] = array(
            "TEXT" => GetMessage("SOD_TO_EDIT"),
            "LINK" => "/bitrix/admin/linemedia.auto_sale_order_edit.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_"),
            "ICON"=>"btn_edit",
        );
    $aMenu[] = array(
            "TEXT" => GetMessage("SOD_TO_NEW_ORDER"),
            "LINK" => "/bitrix/admin/linemedia.auto_sale_order_new.php?lang=".LANGUAGE_ID."&LID=".$arOrder["LID"],
            "ICON"=>"btn_edit",
        );
}

$aMenu[] = array(
        "TEXT" => GetMessage("SOD_TO_PRINT"),
        "LINK" => "/bitrix/admin/linemedia.auto_sale_order_print.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_"),

    );

if ($saleModulePermissions == "W" || $arOrder["PAYED"] != "Y" && $bUserCanDeleteOrder) {
    $aMenu[] = array(
            "TEXT" => GetMessage("SODN_CONFIRM_DEL"),
            "LINK" => "javascript:if(confirm('".GetMessage("SODN_CONFIRM_DEL_MESSAGE")."')) window.location='linemedia.auto_sale_orders_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get().urlencode(GetFilterParams("filter_"))."'",
            "WARNING" => "Y",
            "ICON"=>"btn_delete",
        );
}

$link = DeleteParam(array("mode"));
$link = $GLOBALS["APPLICATION"]->GetCurPage()."?mode=settings".($link <> ""? "&".$link:"");

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>


<? if (!empty($message)) { ?>
    <?= $message->show() ?>
<? } ?>


<?
CAdminMessage::ShowMessage($errorMessage);

if (strlen($save_order_result) > 0)
{
    $okMessage = "";

    if ($save_order_result == "ok_status")
        $okMessage = GetMessage("SOD_OK_STATUS");
    elseif ($save_order_result == "ok_cancel")
        $okMessage = GetMessage("SOD_OK_CANCEL");
    elseif ($save_order_result == "ok_pay")
        $okMessage = GetMessage("SOD_OK_PAY");
    elseif ($save_order_result == "ok_delivery")
        $okMessage = GetMessage("SOD_OK_DELIVERY");
    elseif ($save_order_result == "ok_comment")
        $okMessage = GetMessage("SOD_OK_COMMENT");
    elseif ($save_order_result == "ok_ps")
        $okMessage = GetMessage("SOD_OK_PS");
    else
        $okMessage = GetMessage("SOD_OK_OK");

    CAdminMessage::ShowNote($okMessage);
}

if (!$bUserCanViewOrder) {
    CAdminMessage::ShowMessage(str_replace("#ID#", $ID, GetMessage("SOD_NO_PERMS2VIEW")).". ");
} else {
    if (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock)) {
        CSaleOrder::Lock($ID);
    }
    $customOrderView = COption::GetOptionString("sale", "path2custom_view_order", "");
    if (strlen($customOrderView) > 0
        && file_exists($_SERVER["DOCUMENT_ROOT"].$customOrderView)
        && is_file($_SERVER["DOCUMENT_ROOT"].$customOrderView))
    {
        include($_SERVER["DOCUMENT_ROOT"].$customOrderView);
    } else {
        $tabControl->BeginEpilogContent();
        ?>
        <?= GetFilterHiddens("filter_"); ?>
        <?= bitrix_sessid_post(); ?>
        <input type="hidden" name="lang" value="<?= LANG ?>">
        <input type="hidden" name="ID" value="<?= $ID ?>">
        <input type="hidden" name="save_order_data" value="Y">
        <?
        $tabControl->EndEpilogContent();

        $tabControl->Begin();

        $tabControl->BeginNextFormTab();

            $tabControl->AddSection("order_id", GetMessage("P_ORDER_ID"));
                $tabControl->BeginCustomField("ORDER_DATE_CREATE", GetMessage("SOD_ORDER_DATE_CREATE"));
                    ?>
                    <tr>
                        <td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td width="60%"><?echo $arOrder["DATE_INSERT"]?></td>
                    </tr>
                    <?
                $tabControl->EndCustomField("ORDER_DATE_CREATE", '');

                $tabControl->BeginCustomField("DATE_UPDATE", GetMessage("SOD_DATE_UPDATE"));
                    ?>
                    <tr>
                        <td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td width="60%"><?echo $arOrder["DATE_UPDATE"]?></td>
                    </tr>
                    <?
                $tabControl->EndCustomField("DATE_UPDATE", '');

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
                        <td width="60%"><?=htmlspecialcharsbx($arSitesShop[$arOrder["LID"]]["NAME"])." (".$arOrder["LID"].")"?>
                        </td>
                    </tr>
                    <?
                    $tabControl->EndCustomField("ORDER_SITE");
                }

                $tabControl->BeginCustomField("ORDER_STATUS", GetMessage("SOD_CUR_STATUS"));
                    ?>
                    <tr>
                        <td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td width="60%">
                                    <?
                                    $arStatusList = False;
                                    $arFilter = array("LID" => LANG);
                                    $arGroupByTmp = false;
                                    if ($saleModulePermissions < "W")
                                    {
                                        $arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
                                        $arFilter["PERM_STATUS_FROM"] = "Y";
                                        $arFilter["ID"] = $arOrder["STATUS_ID"];
                                        $arGroupByTmp = array("ID", "NAME", "MAX" => "PERM_STATUS_FROM");
                                    }
                                    $dbStatusList = CSaleStatus::GetList(
                                            array(),
                                            $arFilter,
                                            $arGroupByTmp,
                                            false,
                                            array("ID", "NAME", "PERM_STATUS_FROM")
                                        );
                                    $arStatusList = $dbStatusList->GetNext();

                                    $statusOrder = "";

                                    ?>
                                    <div id="editStatusDIV">
                                        <select name="STATUS_ID" id="STATUS_ID" onChange="BX('change_status').value='Y';">
                                            <?
                                            if ($arStatusList)
                                            {
                                                $arFilter = array("LID" => LANG);
                                                $arGroupByTmp = false;
                                                if ($saleModulePermissions < "W")
                                                {
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
                                                    $select = "";
                                                    if ($arStatusListTmp["ID"] == $arOrder["STATUS_ID"]) {
                                                        $select = " selected";
                                                    }
                                                    $statusOrder .= "<option value=\"".$arStatusListTmp["ID"]."\" ".$select.">[".$arStatusListTmp["ID"]."] ".$arStatusListTmp["NAME"]."</option>";
                                                }
                                            }

                                            echo $statusOrder;
                                        ?>
                                        </select>
                                        <input type="hidden" name="change_status" id="change_status" value="N">
                                        <input type="hidden" name="change_status_popup" id="change_status_popup" value="N">
                                        <a href="#" onClick="fChangeStatus();return false;" class="adm-btn"><?=GetMessage('SALE_SAVE');?></a>
                                        <script>
                                            function fChangeStatus()
                                            {
                                                BX.showWait();
                                                BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&change_status=Y&STATUS_ID='+BX('STATUS_ID').value+'&ID=<?=$ID?>', fChangeStatusResult);

                                                return false;
                                            }

                                            function fChangeStatusResult(res)
                                            {
                                                var rs = eval( '('+res+')' );
                                                BX.closeWait();

                                                if (BX('date_status_change') && rs['DATE_STATUS'] && rs['DATE_STATUS'].length > 0)
                                                    BX('date_status_change').innerHTML = rs['DATE_STATUS'] + ' ' + rs['EMP_STATUS_ID'];

                                                BX('change_status').value = 'N';
                                            }
                                        </script>
                                    </div>
                        </td>
                    </tr>

                    <?if(strlen($arOrder["DATE_STATUS"]) > 0):?>
                        <tr>
                            <td><?=GetMessage('SOD_DATE_STATUS');?>:</td>
                            <td id="date_status_change"><?=$arOrder["DATE_STATUS"]?>
                                <?if (!$crmMode && IntVal($arOrder["EMP_STATUS_ID"]) > 0)
                                    echo GetFormatedUserName($arOrder["EMP_STATUS_ID"]);
                                ?>
                            </td>
                        </tr>
                    <?endif;?>
                    <?
                $tabControl->EndCustomField("ORDER_STATUS", '');

                $tabControl->BeginCustomField("ORDER_CANCELED", GetMessage("SOD_CANCEL_Y"));
                    ?>

                    <tr id="btn_show_cancel" style="display:<?=($arOrder["CANCELED"] == "N" && $bUserCanCancelOrder) ? 'table-row' : 'none'?>">
                        <td width="40%">&nbsp;</td>
                        <td valign="middle">
                            <a title="<?=GetMessage('SOD_CANCEL_Y')?>" onClick="fShowCancelOrder(this, '');" class="adm-btn-wrap" href="javascript:void(0);"><span class="adm-btn"><?=GetMessage('SOD_CANCEL_Y')?></span></a>
                        </td>
                    </tr>
                    <tr id="user_can_cancel" style="display:<?=($arOrder["CANCELED"] == "N" && !$bUserCanCancelOrder) ? 'table-row' : 'none'?>">
                        <td width="40%">
                            <?=GetMessage("SOD_CANCELED")?>
                        </td>
                        <td valign="middle">
                            <?=GetMessage("SALE_NO")?>
                        </td>
                    </tr>
                    <tr id="btn_cancel_cancel" style="display:<?=($arOrder["CANCELED"] != "N") ? 'table-row' : 'none'?>">
                        <td>
                            <span class="order_cancel_left"><?=GetMessage("SOD_CANCELED")?>:</span>
                        </td>
                        <td valign="top">
                            <span class="order_cancel_right"><?=GetMessage("SALE_YES")?></span>
                            <?if($bUserCanCancelOrder):?>&nbsp;&nbsp;<a href="javascript:void(0);" onClick="fCancelCancelOrder();" class="adm-btn-wrap"><span class="adm-btn"><?=GetMessage('SOD_CANCEL_N');?></span></a><?endif;?>
                        </td>
                    </tr>
                    <tr id="date_change_cancel" style="display:<?=(strlen($arOrder["DATE_CANCELED"]) > 0) ? 'table-row' : 'none'?>">
                        <td>
                            <?=GetMessage('SOD_DATE_CANCELED');?>:
                        </td>
                        <td id="date_change_cancel_user">
                            <?=$arOrder["DATE_CANCELED"]?>
                            <?if (!$crmMode && IntVal($arOrder["EMP_CANCELED_ID"]) > 0)
                                echo GetFormatedUserName($arOrder["EMP_CANCELED_ID"]);
                            ?>
                        </td>
                    </tr>
                    <tr id="reason_cancel" style="display:<?=($arOrder["CANCELED"] != "N") ? 'table-row' : 'none'?>">
                        <td>
                            <?=GetMessage('SOD_CANCEL_REASON_TITLE')?>:
                        </td>
                        <td id="reason_cancel_text">
                            <?=htmlspecialcharsbx($arOrder["REASON_CANCELED"])?>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <div id="popup_cancel_order_form" class="sale_popup_form" style="display:none; font-size:13px;">
                                <table>
                                    <tr>
                                        <td colspan="2"><?=GetMessage('SOD_CANCEL_REASON_TITLE')?><br />
                                            <textarea name="FORM_REASON_CANCELED" id="FORM_REASON_CANCELED" rows="3" cols="30"><?= htmlspecialcharsEx($arOrder["REASON_CANCELED"]) ?></textarea><br />
                                            <small><?=GetMessage('SOD_CANCEL_REASON_ADIT')?></small>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <script>
                                function fCancelCancelOrder()
                                {
                                    BX.showWait();
                                    BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&'+'&change_cancel=Y&CANCELED=N&ID=<?=$ID?>', fCancelCancelOrderResult);
                                }

                                function fCancelCancelOrderResult(res)
                                {
                                    var rs = eval( '('+res+')' );
                                    BX.closeWait();
                                    if (rs["message"] == "ok")
                                    {
                                        BX('btn_cancel_cancel').style.display = "none";
                                        BX('btn_show_cancel').style.display = "table-row";
                                        BX('reason_cancel').style.display = "none";

                                        if (rs["DATE_CANCELED"].length > 0)
                                            BX('date_change_cancel_user').innerHTML = rs["DATE_CANCELED"] + ' ' + rs["EMP_CANCELED_ID"];

                                        BX('date_change_cancel').style.display = "table-row";
                                    }
                                }

                                function fChangeCancelResult(res)
                                {
                                    var rs = eval( '('+res+')' );
                                    BX.closeWait();
                                    if (rs["message"] == "ok")
                                    {
                                        var emp_cancel_user = '';

                                        BX('btn_show_cancel').style.display = "none";
                                        BX('btn_cancel_cancel').style.display = "table-row";

                                        if (rs["DATE_CANCELED"] && rs["DATE_CANCELED"].length > 0)
                                            emp_cancel_user = rs["DATE_CANCELED"];

                                        if (rs["EMP_CANCELED_ID"] && rs["EMP_CANCELED_ID"].length > 0)
                                            emp_cancel_user += ' ' + rs["EMP_CANCELED_ID"];

                                        if (BX('date_change_cancel_user') && emp_cancel_user.length > 0)
                                            BX('date_change_cancel_user').innerHTML = emp_cancel_user;

                                        BX('date_change_cancel').style.display = "table-row";
                                        BX('reason_cancel_text').innerHTML = BX('FORM_REASON_CANCELED').value;
                                        BX('reason_cancel').style.display = "table-row";
                                    }
                                }

                                function fShowCancelOrder(el, type)
                                {
                                    formCancelOrder = BX.PopupWindowManager.create("sale-popup-cancel", el, {
                                        offsetTop : -100,
                                        offsetLeft : -150,
                                        autoHide : true,
                                        closeByEsc : true,
                                        closeIcon : true,
                                        titleBar : true,
                                        draggable: {restrict:true},
                                        titleBar: {content: BX.create("span", {html: '<?=GetMessageJS('SOD_CANCEL_ORDER')?>', 'props': {'className': 'sale-popup-title-bar'}})},
                                        content : BX("popup_cancel_order_form")
                                    });
                                    formCancelOrder.setButtons([
                                        new BX.PopupWindowButton({
                                            text : "<?=GetMessage('SOD_POPUP_SAVE')?>",
                                            className : "",
                                            events : {
                                                click : function()
                                                {
                                                    BX.showWait();
                                                    BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&'+'&change_cancel=Y&CANCELED=Y&REASON_CANCELED='+BX('FORM_REASON_CANCELED').value+'&ID=<?=$ID?>', fChangeCancelResult);
                                                    formCancelOrder.close();
                                                }
                                            }
                                        }),
                                        new BX.PopupWindowButton({
                                            text : "<?=GetMessage('SOD_POPUP_CANCEL')?>",
                                            className : "",
                                            events : {
                                                click : function()
                                                {
                                                    BX('FORM_REASON_CANCELED').value = '';
                                                    formCancelOrder.close();
                                                }
                                            }
                                        })
                                    ]);

                                    formCancelOrder.show();
                                    BX('FORM_REASON_CANCELED').focus();
                                }
                            </script>
                        </td>
                    </tr>
                    <?
                $tabControl->EndCustomField("ORDER_CANCELED", '');

            $tabControl->AddSection("order_user", GetMessage("P_ORDER_USER_ACC"));

                $tabControl->BeginCustomField("ORDER_PROPS", GetMessage("SOD_ORDER_PROPS"));
                    $dbUser = CUser::GetByID($arOrder["USER_ID"]);
                    $arUser = $dbUser->Fetch();
                ?>
                    <tr>
                        <td valign="top" width="40%"><?=GetMessage('SOD_BUYER_LOGIN')?>:</td>
                        <td valign="middle"><a href="/bitrix/admin/sale_buyers_profile.php?ID=<?=$arOrder["USER_ID"]?>&lang=<?=LANG?>"><?= htmlspecialcharsEx($arUser["LOGIN"]); ?></a></td>
                    </tr>
                    <tr>
                        <td valign="top"><?echo GetMessage("P_ORDER_PERS_TYPE")?>:</td>
                        <td valign="middle"><?
                            echo "[";
                            if ($saleModulePermissions >= "W")
                                echo "<a href=\"/bitrix/admin/sale_person_type_edit.php?ID=".$arOrder["PERSON_TYPE_ID"]."&lang=".LANG."\">";
                            echo $arOrder["PERSON_TYPE_ID"];
                            if ($saleModulePermissions >= "W")
                                echo "</a>";
                            echo "] ";
                            $arPersonType = CSalePersonType::GetByID($arOrder["PERSON_TYPE_ID"]);
                            echo htmlspecialcharsEx($arPersonType["NAME"]);
                            ?>
                        </td>
                    </tr>
                <?
                    $dbOrderProps = CSaleOrderPropsValue::GetOrderProps($ID);
                    $iGroup = -1;
                    
                    $arOrderProperties = array();
                    while ($arOrderProps = $dbOrderProps->Fetch()) {
                        $arOrderProperties []= $arOrderProps;
                    }
                    
                    /*
                     * Событие дял других модулей: список свойтв заказа.
                     */
                    $modulehtml = '';
                    $events = GetModuleEvents("linemedia.auto", "OnDetailOrderPropsList");
                    while ($arEvent = $events->Fetch()) {
                        $modulehtml .= ExecuteModuleEventEx($arEvent, array($ID, &$arOrderProperties));
                    }
                    
                    foreach ($arOrderProperties as $arOrderProps) {
                        if ($iGroup != IntVal($arOrderProps["PROPS_GROUP_ID"])) {
                            ?>
                            <tr>
                                <td colspan="2" style="text-align:center;font-weight:bold;font-size:14px;color:rgb(75, 98, 103);"><?=htmlspecialcharsEx($arOrderProps["GROUP_NAME"]);?></td>
                            </tr>
                            <?
                            $iGroup = IntVal($arOrderProps["PROPS_GROUP_ID"]);
                        }
                        ?>
                        <tr>
                            <td valign="top"><?= htmlspecialcharsEx($arOrderProps["NAME"]) ?>:</td>
                            <td valign="middle">
                            <?
                            if ($arOrderProps["TYPE"] == "CHECKBOX") {
                                if ($arOrderProps["VALUE"] == "Y")
                                    echo GetMessage("SALE_YES");
                                else
                                    echo GetMessage("SALE_NO");
                            } elseif ($arOrderProps["TYPE"] == "TEXT" || $arOrderProps["TYPE"] == "TEXTAREA") {
                                if ($arOrderProps["CODE"] == 'phone' &&
                                    $arOrderProps["IS_EMAIL"] == "N" &&
                                    $arOrderProps["IS_PAYER"] == "N" &&
                                    $arOrderProps["IS_PROFILE_NAME"] == "N")
                                {
                                    echo "<a href='callto:".htmlspecialcharsEx($arOrderProps["VALUE"])."'>".htmlspecialcharsEx($arOrderProps["VALUE"])."</a>";
                                }
                                elseif ($arOrderProps["IS_EMAIL"] == "Y")
                                    echo "<a href=\"mailto:".htmlspecialcharsEx($arOrderProps["VALUE"])."\">".htmlspecialcharsEx($arOrderProps["VALUE"])."</a>";
                                else
                                    echo htmlspecialcharsEx($arOrderProps["VALUE"]);
                            } elseif ($arOrderProps["TYPE"] == "SELECT" || $arOrderProps["TYPE"] == "RADIO") {
                                $arVal = CSaleOrderPropsVariant::GetByValue($arOrderProps["ORDER_PROPS_ID"], $arOrderProps["VALUE"]);
                                echo htmlspecialcharsEx($arVal["NAME"]);
                            } elseif ($arOrderProps["TYPE"] == "MULTISELECT") {
                                $curVal = explode(",", $arOrderProps["VALUE"]);
                                for ($i = 0; $i < count($curVal); $i++) {
                                    $arVal = CSaleOrderPropsVariant::GetByValue($arOrderProps["ORDER_PROPS_ID"], $curVal[$i]);
                                    if ($i > 0) {
                                        echo ", ";
                                    }
                                    echo htmlspecialcharsEx($arVal["NAME"]);
                                }
                            } elseif ($arOrderProps["TYPE"] == "LOCATION") {
                                $arVal = CSaleLocation::GetByID($arOrderProps["VALUE"], LANG);
                                echo htmlspecialcharsEx($arVal["COUNTRY_NAME"].((strlen($arVal["COUNTRY_NAME"])<=0 || strlen($arVal["CITY_NAME"])<=0) ? "" : " - ").$arVal["CITY_NAME"]);
                            } else {
                                echo htmlspecialcharsEx($arOrderProps["VALUE"]);
                            }
                            ?>
                            </td>
                        </tr>
                    <?
                    }
                $tabControl->EndCustomField("ORDER_PROPS", '');

            $tabControl->AddSection("order_delivery", GetMessage("P_ORDER_DELIVERY_TITLE"));

                $tabControl->BeginCustomField("ORDER_DELIVERY", GetMessage("P_ORDER_DELIVERY"));
                    ?>
                    <tr>
                        <td width="40%"><?= $tabControl->GetCustomLabelHTML()?>:</td>
                        <td>
                            <span id="allow_delivery_name">
                            <?
                            if (strpos($arOrder["DELIVERY_ID"], ":") !== false) {
                                $arId = explode(":", $arOrder["DELIVERY_ID"]);

                                $dbDelivery = CSaleDeliveryHandler::GetBySID($arId[0]);
                                $arDelivery = $dbDelivery->Fetch();

                                echo "[".$arDelivery["SID"]."] ".htmlspecialcharsEx($arDelivery["NAME"])." (".$arOrder["LID"].")";
                                echo "<br />[".htmlspecialcharsEx($arId[1])."] ".htmlspecialcharsEx($arDelivery["PROFILES"][$arId[1]]["TITLE"]);
                            } elseif (IntVal($arOrder["DELIVERY_ID"]) > 0) {
                                $arDelivery = CSaleDelivery::GetByID($arOrder["DELIVERY_ID"]);
                                echo "[".$arDelivery["ID"]."] ".$arDelivery["NAME"]." (".$arDelivery["LID"].")";
                            } else {
                                echo GetMessage("SOD_NONE");
                            }
                            ?>
                            </span>
                        </td>
                    </tr>
                <?
                $tabControl->EndCustomField("ORDER_DELIVERY", '');

                $tabControl->BeginCustomField("ORDER_ALLOW_DELIVERY", GetMessage("P_ORDER_ALLOW_DELIVERY"));
                ?>
                    <tr id="btn_allow_delivery" style="display:<?=($arOrder["ALLOW_DELIVERY"] == "N" && $bUserCanDeliverOrder) ? 'table-row' : 'none'?>">
                        <td width="40%">&nbsp;</td>
                        <td valign="middle" class="btn_order">
                            <a title="<?=GetMessage('SOD_ALLOW_DELIVERY_DO_Y')?>" onClick="fShowAllowDelivery(this, '');" class="adm-btn adm-btn-green" href="javascript:void(0);"><?=GetMessage('SOD_ALLOW_DELIVERY_DO_Y')?></a>
                        </td>
                    </tr>
                    <tr id="allow_delivery_no_user" style="display:<?=($arOrder["ALLOW_DELIVERY"] == "N" && !$bUserCanDeliverOrder) ? 'table-row' : 'none'?>">
                        <td><?=GetMessage("SOD_DELIVERY_IS_ALLOW")?>:</td>
                        <td><?=GetMessage("SALE_NO")?></td>
                    </tr>
                    <tr id="allow_delivery_date" style="display:<?=($arOrder["ALLOW_DELIVERY"] == "N" && strlen($arOrder["DATE_ALLOW_DELIVERY"]) > 0) ? 'table-row' : 'none'?>">
                        <td><?=GetMessage('SOD_DATE_ALLOW_DELIVERY');?>:</td>
                        <td><?=$arOrder["DATE_ALLOW_DELIVERY"]?>
                            <?if (!$crmMode && IntVal($arOrder["EMP_ALLOW_DELIVERY_ID"]) > 0)
                                echo GetFormatedUserName($arOrder["EMP_ALLOW_DELIVERY_ID"]);
                            ?>
                        </td>
                    </tr>
                    <tr id="allow_delivery_number" style="display:<?=($arOrder["ALLOW_DELIVERY"] != "N" && (strlen($arOrder["DELIVERY_DOC_NUM"]) > 0 || strlen($arOrder["DELIVERY_DOC_DATE"]))) ? 'table-row' : 'none'?>">
                        <td valign="top"><?=GetMessage('SOD_NUMBER_ALLOW_DELIVERY');?>:</td>
                        <td valign="middle" id="allow_delivery_doc_number_format"><?=GetMessage("SOD_DELIV_DOC", Array("#NUM#" => htmlspecialcharsEx($arOrder["DELIVERY_DOC_NUM"]), "#DATE#" => htmlspecialcharsEx($arOrder["DELIVERY_DOC_DATE"]))) ?></td>
                    </tr>
                    <tr id="allow_delivery_date2" style="display:<?=($arOrder["ALLOW_DELIVERY"] != "N") ? 'table-row' : 'none'?>">
                        <td><?=GetMessage('SOD_DATE_ALLOW_DELIVERY2');?>:</td>
                        <td id="allow_delivery_date2_user"><?=$arOrder["DATE_ALLOW_DELIVERY"]?>
                            <?if (!$crmMode && IntVal($arOrder["EMP_ALLOW_DELIVERY_ID"]) > 0)
                                echo GetFormatedUserName($arOrder["EMP_ALLOW_DELIVERY_ID"]);
                            ?>
                        </td>
                    </tr>
                    <tr id="allow_delivery_is_allow" style="display:<?= ($arOrder["ALLOW_DELIVERY"] != "N") ? 'table-row' : 'none'?>">
                        <td><span class="alloy_payed_left"><?= GetMessage("SOD_DELIVERY_IS_ALLOW") ?>:</span></td>
                        <td><span class="alloy_payed_right"><?= GetMessage("SOD_DELIVERY_YES") ?></span><?if($bUserCanDeliverOrder):?>&nbsp;&nbsp;<a href="javascript:void(0);" onClick="fShowAllowDelivery(this, 'cancel');"><?=GetMessage('SOD_DELIVERY_EDIT');?></a><?endif;?></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div id="popup_form" class="sale_popup_form adm-workarea" style="display:none; font-size:13px;">
                                <table>
                                    <tr>
                                        <td class="head"><?=GetMessage('SOD_POPUP_ORDER_STATUS')?>:</td>
                                        <td><select name="FORM_STATUS_ID" id="FORM_STATUS_ID" onChange="fChangeOrderStatus();"><?=$statusOrder?></select></td>
                                    </tr>
                                    <tr>
                                        <td class="head"><?=GetMessage('SOD_POPUP_NUMBER_DOC')?>:</td>
                                        <td>
                                            <input type="text" class="popup_input" id="FORM_DELIVERY_DOC_NUM" name="FORM_DELIVERY_DOC_NUM" value="<?= htmlspecialcharsEx($arOrder["DELIVERY_DOC_NUM"]) ?>" size="30" maxlength="20" class="typeinput">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="head"><?=GetMessage('SOD_POPUP_DATE_DOC')?>:</td>
                                        <td>
                                            <?= CalendarDate("FROM_DELIVERY_DOC_DATE", $arOrder["DELIVERY_DOC_DATE"], "change_delivery_form", "10", "class=\"typeinput\""); ?>
                                        </td>
                                    </tr>
                                    <tr id="cancel_allow_delivery" style="display:none;">
                                        <td class="head"><label for="FORM_ALLOW_DELIVERY_CANCEL"><?=GetMessage('SOD_POPUP_DELIVERY_CANCEL')?>:</label></td>
                                        <td>
                                            <input type="checkbox" name="ALLOW_DELIVERY_CANCEL" id="FORM_ALLOW_DELIVERY_CANCEL" value="N" />
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <script>
                                function fChangeOrderStatus()
                                {
                                    BX('change_status').value='Y';
                                    BX('change_status_popup').value='Y';
                                    BX('STATUS_ID').value = BX.findChild(BX('sale-popup-delivery'), {'attr': {id: 'FORM_STATUS_ID'}}, true, false).value;
                                }

                                function fChangeDeliveryResult(res)
                                {
                                    var rs = eval( '('+res+')' );
                                    BX.closeWait();
                                    if (rs["message"] == "ok")
                                    {
                                        if (rs["ALLOW_DELIVERY"] == "Y")
                                        {
                                            var emp_allow = '';

                                            BX('btn_allow_delivery').style.display = "none";
                                            BX('allow_delivery_date').style.display = "none";
                                            BX('allow_delivery_date2').style.display = "table-row";
                                            BX('allow_delivery_is_allow').style.display = "table-row";
                                            BX('allow_delivery_number').style.display = "table-row";

                                            if (rs["DATE_ALLOW_DELIVERY"].length > 0)
                                                emp_allow = rs["DATE_ALLOW_DELIVERY"];

                                            if (rs["EMP_ALLOW_DELIVERY_ID"] && rs["EMP_ALLOW_DELIVERY_ID"].length > 0)
                                                emp_allow += ' ' + rs["EMP_ALLOW_DELIVERY_ID"];

                                            if (BX('allow_delivery_date2_user') && emp_allow.length > 0)
                                                BX('allow_delivery_date2_user').innerHTML = emp_allow;

                                            if (rs["DELIVERY_DOC_NUMBER_FORMAT"].length > 0)
                                                BX('allow_delivery_doc_number_format').innerHTML = rs["DELIVERY_DOC_NUMBER_FORMAT"];
                                        }
                                        else
                                        {
                                            BX('allow_delivery_date').style.display = "table-row";
                                            BX('btn_allow_delivery').style.display = "table-row";
                                            BX('allow_delivery_date2').style.display = "none";
                                            BX('allow_delivery_is_allow').style.display = "none";
                                            BX('allow_delivery_number').style.display = "none";
                                        }

                                        if (BX('date_status_change') && rs['DATE_STATUS'].length > 0)
                                            BX('date_status_change').innerHTML = rs['DATE_STATUS'] + ' ' + rs['EMP_STATUS_ID'];

                                        if (rs['STATUS_ID'].length > 0)
                                            BX('STATUS_ID').value = rs['STATUS_ID'];
                                    }
                                    
                                    BX('change_status').value='N';
                                    BX('change_status_popup').value='N';
                                }

                                function fShowAllowDelivery(el, type)
                                {
                                    if (type == 'cancel') {
                                        BX("cancel_allow_delivery").style.display = 'table-row';
                                    }
                                    BX('FORM_STATUS_ID').value = BX('STATUS_ID').value;
                                    if (BX('allow_delivery_is_allow').style.display == "none") {
                                        BX('FORM_ALLOW_DELIVERY_CANCEL').checked = false;
                                        BX('cancel_allow_delivery').style.display = "none";
                                    }
                                    
                                    formAllowDelivery = BX.PopupWindowManager.create("sale-popup-delivery", BX('allow_delivery_name'), {
                                        offsetTop : -100,
                                        offsetLeft : -150,
                                        autoHide : true,
                                        closeByEsc : true,
                                        closeIcon : true,
                                        titleBar : true,
                                        draggable: {restrict:true},
                                        titleBar: {content: BX.create("span", {html: '<?=GetMessageJS('SOD_POPUP_DELIVE_TITLE')?>', 'props': {'className': 'sale-popup-title-bar'}})},
                                        content : BX("popup_form")
                                    });
                                    formAllowDelivery.setButtons([
                                        new BX.PopupWindowButton({
                                            text : "<?=GetMessage('SOD_POPUP_SAVE')?>",
                                            className : "",
                                            events : {
                                                click : function()
                                                {
                                                    BX.showWait();
                                                    if (BX.findChild(BX('sale-popup-delivery'), {'attr': {id: 'FORM_ALLOW_DELIVERY_CANCEL'}}, true, false).checked)
                                                        allow_delivery = 'N';
                                                    else
                                                        allow_delivery = "Y";
                                                    delivery_date = BX.findChild(BX('sale-popup-delivery'), {'attr': {name: 'FROM_DELIVERY_DOC_DATE'}}, true, false).value;

                                                    var change_status = 'N';
                                                    var status_id = '';
                                                    if (BX('change_status') && BX('change_status').value == 'Y') {
                                                        change_status = BX('change_status').value;
                                                        status_id = BX('STATUS_ID').value;
                                                    }

                                                    var change_status_popup = 'N';
                                                    if (BX('change_status_popup') && BX('change_status_popup').value == 'Y')
                                                        change_status_popup = BX('change_status_popup').value;

                                                    BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&STATUS_ID='+status_id+'&change_status='+change_status+'&change_status_popup='+change_status_popup+'&change_delivery_form=Y&ALLOW_DELIVERY='+allow_delivery+'&DELIVERY_DOC_NUM='+BX('FORM_DELIVERY_DOC_NUM').value+'&DELIVERY_DOC_DATE='+delivery_date+'&ID=<?=$ID?>', fChangeDeliveryResult);
                                                    formAllowDelivery.close();
                                                }
                                            }
                                        }),
                                        new BX.PopupWindowButton({
                                            text : "<?=GetMessage('SOD_POPUP_CANCEL')?>",
                                            className : "",
                                            events : {
                                                click : function()
                                                {
                                                    formAllowDelivery.close();
                                                }
                                            }
                                        })
                                    ]);

                                    formAllowDelivery.show();
                                    BX('FORM_DELIVERY_DOC_NUM').focus();
                                }
                            </script>
                        </td>
                    </tr>
                <?
                $tabControl->EndCustomField("ORDER_ALLOW_DELIVERY", '');

                $tabControl->AddSection("order_payment", GetMessage("P_ORDER_PAYMENT"));

                $tabControl->BeginCustomField("ORDER_PAYMENT", GetMessage("P_ORDER_PAYMENT"));
                ?>
                    <tr>
                        <td valign="top"><?= GetMessage("P_ORDER_PAY_SYSTEM") ?>:</td>
                        <td valign="middle">
                            <span id="payed_name">
                            <?
                                if (intval($arOrder["PAY_SYSTEM_ID"]) > 0) {
                                    $arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]);
                                    if ($arPaySys) {
                                        echo '[';
                                        if ($saleModulePermissions >= "W") {
                                            echo '<a href="/bitrix/admin/sale_pay_system_edit.php?ID='.$arPaySys["ID"].'&lang='.LANGUAGE_ID.'">';
                                        }
                                        echo $arPaySys['ID'];
                                        if ($saleModulePermissions >= "W") {
                                            echo '</a>';
                                        }
                                        echo '] '.htmlspecialcharsEx($arPaySys["NAME"]."");
                                    } else {
                                        echo "<font color=\"#FF0000\">".GetMessage("SOD_PAY_SYS_DISC")."</font>";
                                    }
                                } else {
                                    GetMessage("SOD_NONE");
                                }
                            ?>
                            </span>
                        </td>
                    </tr>
                <?
                $tabControl->EndCustomField("ORDER_PAYMENT", '');

                $tabControl->BeginCustomField("ORDER_PAYED", GetMessage("P_ORDER_PAYED"));
                ?>  
                    
                    <tr id="summary_pay" style="display:<?=($arOrder["PAYED"] == "N") ? 'table-row' : 'none'?>">
                        <td valign="top"><?=GetMessage('SOD_PAYED_SUM');?>:</td>
                        <td valign="middle"><?=SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"])?></td>
                    </tr>
                    
                    <tr id="pay_date_pay" style="display:<?=(strlen($arOrder["DATE_PAYED"]) > 0) ? 'table-row' : 'none'?>">
                        <td><?=GetMessage('SOD_DATE_ALLOW_PAY_CHANGE');?>:</td>
                        <td id="pay_date_pay_format"><?=$arOrder["DATE_PAYED"]?>
                            <?if (!$crmMode && IntVal($arOrder["EMP_PAYED_ID"]) > 0)
                                echo GetFormatedUserName($arOrder["EMP_PAYED_ID"]);
                            ?>
                        </td>
                    </tr>
                    
                    <tr id="pay_pay_allow" style="display:<?= (!$bUserAllowPay) ? 'table-row' : 'none' ?>">
                        <td>&nbsp;</td>
                        <td valign="middle" class="btn_order">
                            <a title="<?= GetMessage('SOD_ORDER_ALLOW_ALLOW') ?>" onClick="fShowAllowPayAllow(this);" class="adm-btn adm-btn-green" href="javascript:void(0);"><?=GetMessage('SOD_ORDER_ALLOW_ALLOW')?></a>
                        </td>
                    </tr>
                    
                    <tr id="pay_pay_allow_no" style="display:<?= (!$bUserAllowPay && !$bUserCanPayOrder) ? 'table-row' : 'none'?>">
                        <td><?=GetMessage("SOD_ALLOWED_IS_ALLOW")?>:</td>
                        <td><?=GetMessage("SALE_NO")?></td>
                    </tr>
                    <tr id="pay_is_allow" style="display:<?= ($bUserAllowPay) ? 'table-row' : 'none' ?>">
                        <td><span class="alloy_payed_left"><?=GetMessage("SOD_ALLOWED_IS_ALLOW")?>:</span></td>
                        <td>
                            <span class="alloy_payed_right"><?= GetMessage("SOD_ALLOWED_YES") ?></span>
                            <? if ($bUserCanPayOrder) { ?>
                                <a href="javascript:void(0);" onClick="fShowAllowPayAllow(this);">
                                    <?= GetMessage('SOD_ALLOW_EDIT') ?>
                                </a>
                            <? } ?>
                        </td>
                    </tr>
                    
                    
                    
                    <tr id="pay_pay_user" style="display:<?=($arOrder["PAYED"] == "N" && $bUserCanPayOrder) ? 'table-row' : 'none'?>">
                        <td>&nbsp;</td>
                        <td valign="middle" class="btn_order">
                            <a title="<?=GetMessage('SOD_DO_PAY_ORDER')?>" onClick="fShowAllowPay(this);" class="adm-btn adm-btn-green" href="javascript:void(0);"><?=GetMessage('SOD_DO_PAY_ORDER')?></a>
                        </td>
                    </tr>
                    
                    <tr id="pay_pay_user_no" style="display:<?=($arOrder["PAYED"] == "N" && !$bUserCanPayOrder) ? 'table-row' : 'none'?>">
                        <td><?=GetMessage("SOD_PAYED_IS_ALLOW")?>:</td>
                        <td><?=GetMessage("SALE_NO")?></td>
                    </tr>
                    <tr id="pay_allow_pay" style="display:<?=($arOrder["PAYED"] != "N" && strlen($arOrder["PAY_VOUCHER_NUM"]) > 0) ? 'table-row' : 'none'?>">
                        <td><?=GetMessage('SOD_NUMBER_ALLOW_PAY');?>:</td>
                        <td id="payed_doc_number_format"><?= str_replace("#DATE#", $arOrder["PAY_VOUCHER_DATE"], str_replace("#NUM#", htmlspecialcharsEx($arOrder["PAY_VOUCHER_NUM"]), GetMessage("SOD_PAY_DOC"))) ?></td>
                    </tr>
                    <tr id="pay_is_pay" style="display:<?=($arOrder["PAYED"] != "N") ? 'table-row' : 'none'?>">
                        <td><span class="alloy_payed_left"><?=GetMessage("SOD_PAYED_IS_ALLOW")?>:</span></td>
                        <td>
                            <span class="alloy_payed_right"><?= GetMessage("SOD_PAYED_YES") ?></span>
                            <? if ($bUserCanPayOrder) { ?>
                                <a href="javascript:void(0);" onClick="fShowAllowPay(this);">
                                    <?= GetMessage('SOD_DELIVERY_EDIT') ?>
                                </a>
                            <? } ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <td colspan="2">
                            <div id="popup_form_pay" class="sale_popup_form adm-workarea" style="display:none; font-size:13px;">
                                <table>
                                    <tr>
                                        <td class="head"><?=GetMessage('SOD_POPUP_PAY_STATUS')?>:</td>
                                        <td><select name="FORM_PAY_STATUS_ID" id="FORM_PAY_STATUS_ID" onChange="fPayChangeOrderStatus('sale-popup-pay', 'FORM_PAY_STATUS_ID');"><?=$statusOrder?></select></td>
                                    </tr>
                                    <tr>
                                        <td class="head"><?=GetMessage('SOD_POPUP_PAY_NUMBER_DOC')?>:</td>
                                        <td>
                                            <input type="text" id="FORM_PAY_VOUCHER_NUM" class="popup_input" name="FORM_PAY_VOUCHER_NUM" value="<?= htmlspecialcharsEx($arOrder["PAY_VOUCHER_NUM"]) ?>" size="30" maxlength="20" class="typeinput">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="head"><?=GetMessage('SOD_POPUP_PAY_DATE_DOC')?>:</td>
                                        <td>
                                            <?= CalendarDate("FROM_PAY_VOUCHER_DATE", $arOrder["PAY_VOUCHER_DATE"], "change_pay_form", "10", "class=\"typeinput\""); ?>
                                        </td>
                                    </tr>
                                    <?
                                    $dbUserAccount = CSaleUserAccount::GetList(
                                        array(),
                                        array(
                                            "USER_ID" => $arOrder["USER_ID"],
                                            "CURRENCY" => $arOrder["CURRENCY"],
                                        )
                                    );
                                    $arUserAccount = $dbUserAccount->GetNext();
                                    ?>
                                    <tr id="user_budget" style="display:<?=($arOrder["PAYED"] == "N" && floatval($arUserAccount["CURRENT_BUDGET"]) >= $arOrder["PRICE"]) ? 'table-row' : 'none'?>">
                                        <td class="head" nowrap><?= GetMessage('SOD_ORDER_USER_BUDGET') ?>:</td>
                                        <td id="price_user_budget"><b><?= SaleFormatCurrency($arUserAccount["CURRENT_BUDGET"], $arOrder["CURRENCY"]);?></b></td>
                                    </tr>
                                    <tr id="pay_from_account" style="display:<?=($arOrder["PAYED"] == "N" && floatval($arUserAccount["CURRENT_BUDGET"]) >= $arOrder["PRICE"]) ? 'table-row' : 'none'?>">
                                        <td class="head" nowrap><label for="FORM_PAY_FROM_ACCOUNT"><?=GetMessage('SOD_PAY_ACCOUNT')?>:</label></td>
                                        <td>
                                            <input type="checkbox" value="Y" name="FORM_PAY_FROM_ACCOUNT" id="FORM_PAY_FROM_ACCOUNT" />
                                        </td>
                                    </tr>
                                    <tr id="cancel_allow_pay" style="display:<?= ($arOrder['PAYED'] == 'Y') ? 'table-row' : 'none' ?>">
                                        <td class="head"><label for="FORM_ALLOW_PAY_CANCEL"><?=GetMessage('SOD_POPUP_PAY_CANCEL')?>:</label></td>
                                        <td>
                                            <input type="checkbox" name="FORM_ALLOW_PAY_CANCEL" id="FORM_ALLOW_PAY_CANCEL" value="N" />
                                        </td>
                                    </tr>
                                    <tr id="repay_to_account" style="display:<?= ($arOrder['PAYED'] == 'Y') ? 'table-row' : 'none' ?>">
                                        <td class="head"><label for="FORM_PAY_FROM_ACCOUNT_BACK"><?=GetMessage('SOD_PAY_ACCOUNT_BACK')?>:</label></td>
                                        <td>
                                            <input type="checkbox" name="FORM_PAY_FROM_ACCOUNT_BACK" id="FORM_PAY_FROM_ACCOUNT_BACK" value="N" />
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            
                            
                            
                            
                            
                            <div id="popup_form_allow" class="sale_popup_form adm-workarea" style="display:none; font-size:13px;">
                                <table>
                                    <tr>
                                        <td class="head"><?=GetMessage('SOD_POPUP_PAY_STATUS')?>:</td>
                                        <td>
                                            <select name="FORM_ALLOW_STATUS_ID" id="FORM_ALLOW_STATUS_ID" onChange="fPayChangeOrderStatus('sale-popup-allow', 'FORM_ALLOW_STATUS_ID');">
                                                <?= $statusOrder ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr id="cancel_allow_allow" style="display: <?= ($bUserAllowPay) ? 'table-row' : 'none' ?>">
                                        <td class="head"><label for="FORM_ALLOW_CANCEL"><?= GetMessage('SOD_POPUP_PAY_ALLOW_CANCEL') ?>:</label></td>
                                        <td>
                                            <input type="checkbox" name="FORM_ALLOW_CANCEL" id="FORM_ALLOW_CANCEL" value="Y" />
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            
                            
                            
                            
                            
                            
                            
                            <script>
                                function fPayChangeOrderStatus(wrapperID, elementID)
                                {
                                    BX('change_status').value = 'Y';
                                    BX('change_status_popup').value = 'Y';
                                    BX('STATUS_ID').value = BX.findChild(BX(wrapperID), {'attr': {id: elementID}}, true, false).value;
                                }
                                
                                function fChangePayResult(res)
                                {
                                    var rs = eval( '('+res+')' );
                                    BX.closeWait();

                                    if (rs["message"] == "ok") {
                                        if (rs["PAYED"] == "Y") {
                                            var emp_payed = '';

                                            BX('summary_pay').style.display = "none";
                                            BX('pay_pay_user').style.display = "none";
                                            BX('pay_is_pay').style.display = "table-row";

                                            if (rs["DATE_PAYED"] && rs["DATE_PAYED"].length > 0) {
                                                emp_payed = rs["DATE_PAYED"];

                                                if (rs["EMP_PAYED_ID"] && rs["EMP_PAYED_ID"].length > 0) {
                                                    emp_payed += rs["EMP_PAYED_ID"];
                                                }
                                                if (BX('pay_date_pay_format') && emp_payed.length > 0) {
                                                    BX('pay_date_pay_format').innerHTML = emp_payed;
                                                }
                                                BX('pay_date_pay').style.display = "table-row";
                                            }

                                            if (rs["PAY_DOC_NUMBER_FORMAT"] && rs["PAY_DOC_NUMBER_FORMAT"].length > 0) {
                                                BX('payed_doc_number_format').innerHTML = rs["PAY_DOC_NUMBER_FORMAT"];
                                                BX('pay_allow_pay').style.display = "table-row";
                                            }

                                            BX('user_budget').style.display = "none";
                                            BX('pay_from_account').style.display = "none";
                                            BX('cancel_allow_pay').style.display = "table-row";
                                            BX('repay_to_account').style.display = "table-row";
                                        }
                                        else
                                        {
                                            BX('summary_pay').style.display = "table-row";
                                            BX('pay_pay_user').style.display = "table-row";
                                            BX('pay_allow_pay').style.display = "none";
                                            BX('pay_date_pay').style.display = "table-row";
                                            BX('pay_is_pay').style.display = "none";

                                            if (rs["BUDGET_ENABLE"] && rs["BUDGET_ENABLE"] == "Y")
                                            {
                                                BX('price_user_budget').innerHTML = "<b>"+rs["BUDGET_USER"]+"</b>";

                                                BX('user_budget').style.display = "table-row";
                                                BX('pay_from_account').style.display = "table-row";
                                            }

                                            BX('cancel_allow_pay').style.display = "none";
                                            BX('repay_to_account').style.display = "none";
                                        }

                                        if (BX('date_status_change') && rs['DATE_STATUS'].length > 0)
                                            BX('date_status_change').innerHTML = rs['DATE_STATUS'] + ' ' + rs['EMP_STATUS_ID'];

                                        if (rs['STATUS_ID'] && rs['STATUS_ID'].length > 0)
                                            BX('STATUS_ID').value = rs['STATUS_ID'];
                                    }

                                    BX('change_status').value='N';
                                    BX('change_status_popup').value='N';
                                }
                                
                                
                                function fChangeAllowResult(res)
                                {
                                    var rs = eval( '('+res+')' );
                                    BX.closeWait();
                                    console.log(rs);
                                    if (rs['message'] == 'ok') {
                                        if (rs['ALLOW'] == 'Y') {
                                            var emp_payed = '';
                                            
                                            BX('pay_pay_allow').style.display = "none";
                                            BX('pay_is_allow').style.display = "table-row";
                                            BX('cancel_allow_allow').style.display = "table-row";
                                        } else {
                                            BX('pay_pay_allow').style.display = "table-row";
                                            BX('pay_is_allow').style.display = "none";
                                            BX('cancel_allow_allow').style.display = "none";
                                        }
                                        
                                        if (rs['STATUS_ID'] && rs['STATUS_ID'].length > 0) {
                                            BX('STATUS_ID').value = rs['STATUS_ID'];
                                        }
                                    }

                                    BX('change_status').value = 'N';
                                    BX('change_status_popup').value = 'N';
                                }
                                
                                
                                function fShowAllowPay(el)
                                {
                                    BX('FORM_PAY_STATUS_ID').value = BX('STATUS_ID').value;

                                    if (BX('FORM_ALLOW_PAY_CANCEL'))
                                        BX('FORM_ALLOW_PAY_CANCEL').checked = false;

                                    if (BX('FORM_PAY_FROM_ACCOUNT_BACK'))
                                        BX('FORM_PAY_FROM_ACCOUNT_BACK').checked = false;
                                    
                                    if (BX('FORM_PAY_FROM_ACCOUNT'))
                                        BX('FORM_PAY_FROM_ACCOUNT').checked = false;
                                    
                                    formAllowPay = BX.PopupWindowManager.create("sale-popup-pay", BX('payed_name'), {
                                        offsetTop : -100,
                                        offsetLeft : -150,
                                        autoHide : true,
                                        closeByEsc : true,
                                        closeIcon : true,
                                        titleBar : true,
                                        draggable: {restrict:true},
                                        titleBar: {content: BX.create("span", {html: '<?=GetMessageJS('SOD_POPUP_PAY_TITLE')?>', 'props': {'className': 'sale-popup-title-bar'}})},
                                        content : document.getElementById("popup_form_pay")
                                    });
                                    formAllowPay.setButtons([
                                        new BX.PopupWindowButton({
                                            text : "<?=GetMessage('SOD_POPUP_SAVE')?>",
                                            className : "",
                                            events : {
                                                click : function()
                                                {
                                                    BX.showWait();
                                                    
                                                    payed = "Y";
                                                    if (BX('FORM_ALLOW_PAY_CANCEL') && BX.findChild(BX('sale-popup-pay'), {'attr': {id: 'FORM_ALLOW_PAY_CANCEL'}}, true, false).checked)
                                                        payed = "N";

                                                    pay_date = BX.findChild(BX('popup_form_pay'), {'attr': {name: 'FROM_PAY_VOUCHER_DATE'}}, true, false).value;
                                                    pay_num = BX('FORM_PAY_VOUCHER_NUM').value;

                                                    var change_status = 'N';
                                                    var status_id = '';
                                                    if (BX('change_status') && BX('change_status').value == 'Y') {
                                                        change_status = BX('change_status').value;
                                                        status_id = BX('STATUS_ID').value;
                                                    }

                                                    var pay_from_account = "";
                                                    if (BX('FORM_PAY_FROM_ACCOUNT') && BX.findChild(BX('sale-popup-pay'), {'attr': {id: 'FORM_PAY_FROM_ACCOUNT'}}, true, false).checked)
                                                        pay_from_account = 'Y';
                                                    
                                                    var pay_from_account_back = "";
                                                    if (BX('FORM_PAY_FROM_ACCOUNT_BACK') && BX.findChild(BX('sale-popup-pay'), {'attr': {id: 'FORM_PAY_FROM_ACCOUNT_BACK'}}, true, false).checked)
                                                        pay_from_account_back = 'Y';

                                                    var change_status_popup = 'N';
                                                    if (BX('change_status_popup') && BX('change_status_popup').value == 'Y')
                                                        change_status_popup = BX('change_status_popup').value;

                                                    BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&STATUS_ID='+status_id+'&change_status='+change_status+'&change_status_popup='+change_status_popup+'&change_pay_form=Y&PAYED='+payed+'&PAY_VOUCHER_NUM='+pay_num+'&PAY_VOUCHER_DATE='+pay_date+'&PAY_FROM_ACCOUNT='+pay_from_account+'&PAY_FROM_ACCOUNT_BACK='+pay_from_account_back+'&ID=<?= $ID ?>', fChangePayResult);

                                                    formAllowPay.close();
                                                }
                                            }
                                        }),
                                        new BX.PopupWindowButton({
                                            text : "<?=GetMessage('SOD_POPUP_CANCEL')?>",
                                            className : "",
                                            events : {
                                                click : function()
                                                {
                                                    formAllowPay.close();
                                                }
                                            }
                                        })
                                    ]);

                                    formAllowPay.show();
                                    BX('FORM_PAY_VOUCHER_NUM').focus();
                                }
                                
                                
                                
                                
                                function fShowAllowPayAllow(el)
                                {
                                    BX('FORM_ALLOW_STATUS_ID').value = BX('STATUS_ID').value;
                                    
                                    if (BX('FORM_ALLOW_CANCEL')) {
                                        BX('FORM_ALLOW_CANCEL').checked = false;
                                    }
                                    
                                    formAllowPayAllow = BX.PopupWindowManager.create("sale-popup-allow", BX('payed_name'), {
                                        offsetTop : -100,
                                        offsetLeft : -150,
                                        autoHide : true,
                                        closeByEsc : true,
                                        closeIcon : true,
                                        titleBar : true,
                                        draggable: {restrict:true},
                                        titleBar: {content: BX.create("span", {html: '<?= GetMessageJS('SOD_POPUP_ALLOW_TITLE') ?>', 'props': {'className': 'sale-popup-title-bar'}})},
                                        content : document.getElementById("popup_form_allow")
                                    });
                                    formAllowPayAllow.setButtons([
                                        new BX.PopupWindowButton({
                                            text : "<?= GetMessage('SOD_POPUP_SAVE') ?>",
                                            className : "",
                                            events : {
                                                click : function()
                                                {
                                                    BX.showWait();
                                                    
                                                    allow = "Y";
                                                    if (BX('FORM_ALLOW_CANCEL') && BX.findChild(BX('sale-popup-allow'), {'attr': {id: 'FORM_ALLOW_CANCEL'}}, true, false).checked) {
                                                        allow = "N";
                                                    }
                                                    
                                                    var change_status = 'N';
                                                    var status_id = '';
                                                    if (BX('change_status') && BX('change_status').value == 'Y') {
                                                        change_status = BX('change_status').value;
                                                        status_id = BX('STATUS_ID').value;
                                                    }
                                                    
                                                    var change_status_popup = 'N';
                                                    if (BX('change_status_popup') && BX('change_status_popup').value == 'Y') {
                                                        change_status_popup = BX('change_status_popup').value;
                                                    }
                                                    
                                                    BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_detail.php', '<?= CUtil::JSEscape(bitrix_sessid_get()) ?>&ORDER_AJAX=Y&save_order_data=Y&STATUS_ID='+status_id+'&change_status='+change_status+'&change_status_popup='+change_status_popup+'&change_allow_form=Y&ALLOW=' + allow + '&ID=<?= $ID ?>', fChangeAllowResult);

                                                    formAllowPayAllow.close();
                                                }
                                            }
                                        }),
                                        new BX.PopupWindowButton({
                                            text : "<?= GetMessage('SOD_POPUP_CANCEL') ?>",
                                            className : "",
                                            events : {
                                                click : function()
                                                {
                                                    formAllowPayAllow.close();
                                                }
                                            }
                                        })
                                    ]);
                                    formAllowPayAllow.show();
                                }
                                
                            </script>
                        </td>
                    </tr>
                <?
                $tabControl->EndCustomField("ORDER_PAYED", '');
            
                $arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]);
                if (strlen($arOrder["PS_STATUS"]) > 0)
                {
                    $tabControl->BeginCustomField("ORDER_PS_STATUS", GetMessage("P_ORDER_PS_STATUS"));
                    ?>
                    <tr>
                        <td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td>
                            <?
                            echo (($arOrder["PS_STATUS"]=="Y") ? "OK" : "N");

                            if (!$crmMode && $arPaySys["PSA_HAVE_RESULT"] == "Y" || strlen($arPaySys["PSA_RESULT_FILE"]) > 0)
                            {
                                ?>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="/bitrix/admin/linemedia.auto_sale_order_detail.php?ID=<?= $ID ?>&action=ps_update&lang=<?= LANG ?><?echo GetFilterParams("filter_")?>&<?= bitrix_sessid_get() ?>"><?echo GetMessage("P_ORDER_PS_STATUS_UPDATE") ?> &gt;&gt;</a>
                                <?
                            }
                            ?>
                        </td>
                    </tr>
                    <?
                    $tabControl->EndCustomField("ORDER_PS_STATUS", '');

                    $tabControl->BeginCustomField("ORDER_PS_STATUS_CODE", GetMessage("P_ORDER_PS_STATUS"));
                    ?>
                    <tr>
                        <td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td><?echo $arOrder["PS_STATUS_CODE"] ;?></td>
                    </tr>
                    <?
                    $tabControl->EndCustomField("ORDER_PS_STATUS_CODE", '');

                    $tabControl->BeginCustomField("ORDER_PS_STATUS_DESCRIPTION", GetMessage("P_ORDER_PS_STATUS_DESCRIPTION"));
                    ?>
                    <tr>
                        <td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td><?echo $arOrder["PS_STATUS_DESCRIPTION"] ;?></td>
                    </tr>
                    <?
                    $tabControl->EndCustomField("ORDER_PS_STATUS_DESCRIPTION", '');

                    $tabControl->BeginCustomField("ORDER_PS_STATUS_MESSAGE", GetMessage("P_ORDER_PS_STATUS_MESSAGE"));
                    ?>
                    <tr>
                        <td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td><?echo $arOrder["PS_STATUS_MESSAGE"] ;?></td>
                    </tr>
                    <?
                    $tabControl->EndCustomField("ORDER_PS_STATUS_MESSAGE", '');

                    $tabControl->BeginCustomField("ORDER_PS_SUM", GetMessage("P_ORDER_PS_SUM"));
                    ?>
                    <tr>
                        <td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td><?echo $arOrder["PS_SUM"] ;?></td>
                    </tr>
                    <?
                    $tabControl->EndCustomField("ORDER_PS_SUM", '');

                    $tabControl->BeginCustomField("ORDER_PS_CURRENCY", GetMessage("P_ORDER_PS_CURRENCY"));
                    ?>
                    <tr>
                        <td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td><?echo $arOrder["PS_CURRENCY"] ;?></td>
                    </tr>
                    <?
                    $tabControl->EndCustomField("ORDER_PS_CURRENCY", '');

                    $tabControl->BeginCustomField("ORDER_PS_RESPONSE_DATE", GetMessage("P_ORDER_PS_RESPONSE_DATE"));
                    ?>
                    <tr>
                        <td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td><?echo $arOrder["PS_RESPONSE_DATE"]; ?></td>
                    </tr>
                    <?
                    $tabControl->EndCustomField("ORDER_PS_RESPONSE_DATE", '');
                }
                elseif (!$crmMode && $arPaySys["PSA_HAVE_RESULT"] == "Y" || strlen($arPaySys["PSA_RESULT_FILE"]) > 0)
                {
                    $tabControl->BeginCustomField("ORDER_PS_STATUS_REC", GetMessage("P_ORDER_PS_STATUS"));
                    ?>
                    <tr>
                        <td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
                        <td><a href="/bitrix/admin/linemedia.auto_sale_order_detail.php?ID=<?= $ID ?>&action=ps_update&lang=<?= LANG ?><?= GetFilterParams("filter_") ?>&<?= bitrix_sessid_get() ?>"><?= GetMessage("P_ORDER_PS_STATUS_UPDATE") ?> &gt;&gt;</a></td>
                    </tr>
                    <?
                    $tabControl->EndCustomField("ORDER_PS_STATUS_REC", '');
                }

            $tabControl->AddSection("order_comments", GetMessage("SOD_COMMENTS"));

                $tabControl->BeginCustomField("ORDER_COMMENTS", GetMessage("SOD_COMMENTS"));

                    if (strlen($arOrder["USER_DESCRIPTION"])>0)
                    {
                        ?>
                        <tr>
                            <td valign="top"><?echo GetMessage("P_ORDER_USER_COMMENTS")?>:</td>
                            <td valign="middle"><?echo htmlspecialcharsEx($arOrder["USER_DESCRIPTION"]); ?></td>
                        </tr>
                        <?
                    }

                    if (strlen($arOrder["ADDITIONAL_INFO"])>0)
                    {
                        ?>
                        <tr>
                            <td valign="top"><?echo GetMessage("P_ORDER_ADDITIONAL_INFO")?>:</td>
                            <td valign="middle"><?echo htmlspecialcharsEx($arOrder["ADDITIONAL_INFO"]); ?></td>
                        </tr>
                        <?
                    }
                ?>
                <tr>
                    <td valign="top"><?echo GetMessage('SOD_ORDER_COMMENT_MANAGER_TITLE');?>:</td>
                    <td valign="middle">
                        <div id="hover_comment">
                            <span id="manager-comment-title" onClick="fShowComment(this);">
                                <? 
                                if(strlen($arOrder["COMMENTS"]) > 0) 
                                    echo htmlspecialcharsbx($arOrder["COMMENTS"]);
                                else 
                                    echo GetMessage('SOD_ORDER_COMMENT_MANAGER');
                                ?>
                            </span>
                            <span class="pencil"></span>
                        </div>
                        
                        <textarea id="manager-comment-text"  name="COMMENTS" class="comment" onChange="fEditComment(this, 'change');" onblur="fEditComment(this, 'exit');"><?= htmlspecialcharsbx($arOrder["COMMENTS"]) ?></textarea>
                        <input type="hidden" name="change_comments" id="id_change_comments_hidden" value="N">
                        
                        <script>
                            function fShowComment(el)
                            {
                                BX(el).style.display = 'none';
                                BX('manager-comment-text').style.display = 'block';
                                BX('manager-comment-text').focus();

                            }
                            function fEditComment(el, type)
                            {
                                if (type == 'change')
                                {
                                    BX.showWait();

                                    BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&change=Y&comment='+el.value+'&ID=<?=$ID?>', fEditCommentResult);

                                    if (BX('manager-comment-text').value.length > 0)
                                        BX('manager-comment-title').innerHTML = BX('manager-comment-text').value;
                                    else
                                        BX('manager-comment-title').innerHTML = '<?=GetMessage('SOD_ORDER_COMMENT_MANAGER')?>';
                                }

                                BX('manager-comment-title').style.display = 'inline-block';
                                BX('manager-comment-text').style.display = 'none';
                            }
                            
                            function fEditCommentResult(res)
                            {
                                BX.closeWait();
                            }
                        </script>
                    </td>
                </tr>
                <?
                $tabControl->EndCustomField("ORDER_COMMENTS", '');

//order list
            $tabControl->AddSection("buyer_order", GetMessage("SOD_ORDER"));

                $tabControl->BeginCustomField("orders_list", GetMessage("SOD_ORDER"));
                ?>
                <tr>
                    <td colspan="2" valign="top">
                        <table cellpadding="3" cellspacing="1" border="0" width="100%" class="internal" id="BASKET_TABLE">
                        <tr class="heading">
                            <?/*<td><?echo GetMessage("SOD_ORDER_PHOTO")?></td>*/?>
                            <td><?echo GetMessage("SOD_ORDER_NAME")?></td>
                            <td><?echo GetMessage("SOD_ORDER_QUANTITY")?></td>
                            <?/*<td><?echo GetMessage("SOD_ORDER_BALANCE")?></td>*/?>
                            <td><?echo GetMessage("SOD_ORDER_PROPS")?></td>
                            <td><?echo GetMessage("SOD_ORDER_PRICE")?></td>
                            <td><?echo GetMessage("SOD_ORDER_SUMMA")?></td>
                        </tr>

                        <?
                        $arCurFormat = CCurrencyLang::GetCurrencyFormat($arOrder["CURRENCY"]);
                        $CURRENCY_FORMAT = trim(str_replace("#", '', $arCurFormat["FORMAT_STRING"]));

                        $ORDER_TOTAL_PRICE = 0;
                        $ORDER_TOTAL_WEIGHT = 0;
                        $arFilterRecomendet = array();
                        $dbBasket = CSaleBasket::GetList(
                            array("ID" => "DESC"),
                            array("ORDER_ID" => $ID),
                            false,
                            false,
                            array("ID", "PRODUCT_ID", "PRODUCT_PRICE_ID", "PRICE", "CURRENCY", "WEIGHT", "QUANTITY", "NAME", "MODULE", "CALLBACK_FUNC", "NOTES", "DETAIL_PAGE_URL", "DISCOUNT_PRICE", "DISCOUNT_VALUE", "ORDER_CALLBACK_FUNC", "CANCEL_CALLBACK_FUNC", "PAY_CALLBACK_FUNC", "CATALOG_XML_ID", "PRODUCT_XML_ID", "VAT_RATE", "DISCOUNT_NAME", "DISCOUNT_COUPON")
                        );
                        while ($arBasket = $dbBasket->GetNext())
                        {
                            $ORDER_TOTAL_PRICE += ($arBasket["PRICE"] + $arBasket["DISCOUNT_PRICE"]) * $arBasket["QUANTITY"];
                            $arFilterRecomendet[] = $arBasket["PRODUCT_ID"];
                            $ORDER_TOTAL_WEIGHT += FloatVal($arBasket["WEIGHT"]);
                        ?>
                        <tr>
                            <? /*
                            <td class="photo">
                                <?
                                $productImg = "";
    
                                if (CModule::IncludeModule('iblock'))
                                {
                                    $rsProductInfo = CIBlockElement::GetByID($arBasket["PRODUCT_ID"]);
                                    $arProductInfo = $rsProductInfo->GetNext();

                                    if ($arProductInfo["IBLOCK_ID"] > 0)
                                        $arBasket["EDIT_PAGE_URL"] = "/bitrix/admin/iblock_element_edit.php?ID=".$arBasket["PRODUCT_ID"]."&type=".$arProductInfo["IBLOCK_TYPE_ID"]."&lang=".LANG."&IBLOCK_ID=".$arProductInfo["IBLOCK_ID"]."&find_section_section=".IntVal($arProductInfo["IBLOCK_SECTION_ID"]);

                                    if ($productImg == ""
                                            //&& is_array($val["PROPS"])
                                            //&& count($val["PROPS"]) > 0
                                            && $arProductInfo["PREVIEW_PICTURE"] == ""
                                            && $arProductInfo["DETAIL_PICTURE"] == "")
                                    {
                                        $arParent = CCatalogSku::GetProductInfo($arBasket["PRODUCT_ID"]);

                                        if ($arParent)
                                        {
                                            $res = CIBlockElement::GetList(array(), array("ID" => $arParent["ID"]), false, false, array("PREVIEW_PICTURE", "DETAIL_PICTURE"));
                                            if ($ar_fields = $res->Fetch())
                                            {
                                                $arProductInfo["PREVIEW_PICTURE"] = $ar_fields["PREVIEW_PICTURE"];
                                                $arProductInfo["DETAIL_PICTURE"] = $ar_fields["DETAIL_PICTURE"];
                                            }
                                        }
                                    }

                                    if($arProductInfo["PREVIEW_PICTURE"] != "")
                                        $productImg = $arProductInfo["PREVIEW_PICTURE"];
                                    elseif($arProductInfo["DETAIL_PICTURE"] != "")
                                        $productImg = $arProductInfo["DETAIL_PICTURE"];

                                }

                                if ($productImg != "")
                                {
                                    $arFile = CFile::GetFileArray($productImg);
                                    $productImg = CFile::ResizeImageGet($arFile, array('width'=>80, 'height'=>80), BX_RESIZE_IMAGE_PROPORTIONAL, false, false);
                                    $arBasket["PICTURE"] = $productImg;
                                }

                                if (is_array($arBasket["PICTURE"]))
                                        echo "<img src=\"".$arBasket["PICTURE"]["src"]."\" alt=\"\" width=\"80\" border=\"0\" />";
                                    else
                                        echo "<div class=\"no_foto\">".GetMessage('SOD_NO_FOTO')."</div>";
                                ?>
                            </td>
                            */ ?>
                            <td class="order_name">
                                <?if (strlen($arBasket["EDIT_PAGE_URL"]) > 0):?>
                                    <a href="<?echo $arBasket["EDIT_PAGE_URL"]?>" target="_blank">
                                <?endif;?>
                                <?echo trim($arBasket["NAME"])?>
                                <?if (strlen($arBasket["EDIT_PAGE_URL"]) > 0):?>
                                    </a>
                                <?endif;?>
                            </td>
                            <td class="order_count">
                                <?echo $arBasket["QUANTITY"];?>
                            </td>
                            <? /*
                            <td class="balance_count">
                                <?
                                $balance = 0;
                                if ($arBasket["MODULE"] == "catalog" && CModule::IncludeModule('catalog'))
                                {
                                    $ar_res = CCatalogProduct::GetByID($arBasket["PRODUCT_ID"]);
                                    $balance = FloatVal($ar_res["QUANTITY"]);
                                }
                                ?>
                                <?echo $balance?>
                            </td>
                            */ ?>
                            <td class="props">
                                <?
                                $dbBasketProps = CSaleBasket::GetPropsList(
                                        array("SORT" => "ASC", "NAME" => "ASC"),
                                        array("BASKET_ID" => $arBasket["ID"], "!CODE" => Array("PRODUCT.XML_ID", "CATALOG.XML_ID")),
                                        false,
                                        false,
                                        array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
                                    );
                                while ($arBasketProps = $dbBasketProps->GetNext()) {
                                    echo htmlspecialcharsEx($arBasketProps["NAME"]).": <b>".htmlspecialcharsEx($arBasketProps["VALUE"])."</b><br />";
                                }
                                ?>
                            </td>

                            <td class="order_price" nowrap>
                                    <?
                                    $priceDiscount = $priceBase = ($arBasket["DISCOUNT_PRICE"] + $arBasket["PRICE"]);
                                    if(DoubleVal($priceBase) > 0)
                                        $priceDiscount = roundEx(($arBasket["DISCOUNT_PRICE"] * 100) / $priceBase, SALE_VALUE_PRECISION);
                                    ?>

                                    <div class="edit_price">
                                        <span class="default_price_product" >
                                            <span class="formated_price"><?=CurrencyFormatNumber($arBasket["PRICE"], $arBasket["CURRENCY"]);?></span>
                                        </span>
                                        <span class='currency_price'><?=$CURRENCY_FORMAT?></span>
                                    </div>
                                    <?if ($priceDiscount > 0):?>
                                        <div class="base_price" id="DIV_BASE_PRICE_WITH_DISCOUNT_<?=$arBasket["PRODUCT_ID"]?>">
                                            <?=CurrencyFormatNumber($priceBase, $arBasket["CURRENCY"]);?>
                                            <span class='currency_price'><?=$CURRENCY_FORMAT?></span>
                                        </div>
                                        <!--<div class="discount">(<?=getMessage('SOD_PRICE_DISCOUNT')." ".$priceDiscount?>%)</div>-->

                                        <?
                                        $discountName = "";
                                        if (strlen($arBasket["DISCOUNT_NAME"]) > 0)
                                            $discountName = preg_replace("/\[[0-9]+\] /", "", $arBasket["DISCOUNT_NAME"]);

                                        if (strlen($arBasket["DISCOUNT_COUPON"]) > 0)
                                        {
                                            $discountName .= " (".preg_replace("/\[[0-9]+\] /", "", $arBasket["DISCOUNT_COUPON"]).")";
                                        }

                                        if (strlen($discountName) <= 0)
                                            $discountName = GetMessage('SOD_PRICE_DISCOUNT');

                                        ?>
                                        <div class="discount"><?=$discountName." ".$priceDiscount?>%</div>


                                    <?endif;?>
                                    <div class="base_price_title"><?=$arBasket["NOTES"];?></div>
                            </td>
                            <td class="product_summa" nowrap>
                                <div><?=CurrencyFormatNumber(($arBasket["QUANTITY"] * $arBasket["PRICE"]), $arBasket["CURRENCY"]);?> <span><?=$CURRENCY_FORMAT?></span></div>
                            </td>
                        </tr>
                        <?
                        }//end while order
                        ?>
                        </table>
                    </td>
                </tr>
                <?
                $tabControl->EndCustomField("orders_list");

                $tabControl->BeginCustomField("orders_itog", GetMessage("SOD_ORDER_ITOG"));
                ?>
                <tr>
                    <td colspan="2" valign="top">
                        <br>
                        <table width="100%" class="order_summary">
                        <tr>
                            <? /*
                            <td class="load_product" valign="top">
                                <table width="100%" class="itog_header"><tr><td><?=GetMessage('SOD_SUBTAB_RECOM_REQUEST');?></td></tr></table>
                                <br>

                                <div id="tabs">
                                    <?
                                    $displayNone = "block";
                                    $displayNoneBasket = "block";
                                    $displayNoneViewed = "block";

                                    $arRecomendetResult = CSaleProduct::GetRecommendetProduct($arOrder["USER_ID"], $arOrder["LID"], $arFilterRecomendet);
                                    $recomCnt = count($arRecomendetResult);

                                    if ($recomCnt > 2)
                                    {
                                        $arTmp = array();
                                        $arTmp[] = $arRecomendetResult[0];
                                        $arTmp[] = $arRecomendetResult[1];
                                        $arRecomendetResult = $arTmp;
                                    }
                                    if ($recomCnt <= 0) {
                                        $displayNone = "none";
                                    }
                                    $arErrors = array();
                                    $arFuserItems = CSaleUser::GetList(array("USER_ID" => intval($arOrder["USER_ID"])));

                                    $arShoppingCart = CSaleBasket::DoGetUserShoppingCart($arOrder["LID"], $arOrder["USER_ID"], $arFuserItems["ID"], $arErrors, array());
                                    $busketCnt = count($arShoppingCart);
                                    if ($busketCnt > 2) {
                                        $arTmp = array();
                                        $arTmp[] = $arShoppingCart[0];
                                        $arTmp[] = $arShoppingCart[1];
                                        $arShoppingCart = $arTmp;
                                    }
                                    if ($busketCnt <= 0) {
                                        $displayNoneBasket = "none";
                                    }
                                    $arViewed = array();
                                    $dbViewsList = CSaleViewedProduct::GetList(
                                            array(),
                                            array("FUSER_ID" => $arFuserItems["ID"], ">PRICE" => 0, "!CURRENCY" => "", "LID" => $arOrder["LID"]),
                                            array("COUNT" => "PRODUCT_ID"),
                                            false,
                                            array()
                                            );
                                    $arViewsTmp = $dbViewsList->Fetch();
                                    $viewedCnt = $arViewsTmp['PRODUCT_ID'];

                                    $dbViewsList = CSaleViewedProduct::GetList(
                                            array("DATE_VISIT"=>"DESC"),
                                            array("FUSER_ID" => $arFuserItems["ID"], ">PRICE" => 0, "!CURRENCY" => "", "LID" => $arOrder["LID"]),
                                            false,
                                            array('nTopCount' => 2),
                                            array('ID', 'PRODUCT_ID', 'LID', 'MODULE', 'NAME', 'DETAIL_PAGE_URL', 'PRICE', 'CURRENCY', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
                                        );
                                    while ($arViews = $dbViewsList->Fetch()) {
                                        $arViewed[] = $arViews;
                                    }
                                    if (count($arViewed) <= 0) {
                                        $displayNoneViewed = "none";
                                    }
                                    $tabBasket = "tabs";
                                    $tabViewed = "tabs";

                                    if ($displayNoneBasket == 'none' && $displayNone == 'none' && $displayNoneViewed == 'block')
                                        $tabViewed .= " active";
                                    if ($displayNoneBasket == 'block' && $displayNone == 'none')
                                        $tabBasket .= " active";

                                    ?>
                                    <div id="tab_1" style="display:<?=$displayNone?>"       class="tabs active"     onClick="fTabsSelect('buyer_recmon', this);" ><?=GetMessage('SOD_SUBTAB_RECOMENET')?></div>
                                    <div id="tab_2" style="display:<?=$displayNoneBasket?>" class="<?=$tabBasket?>" onClick="fTabsSelect('buyer_busket', this);"><?=GetMessage('SOD_SUBTAB_BUSKET')?></div>
                                    <div id="tab_3" style="display:<?=$displayNoneViewed?>" class="<?=$tabViewed?>" onClick="fTabsSelect('buyer_viewed', this);"><?=GetMessage('SOD_SUBTAB_LOOKED')?></div>

                                    <?
                                    if ($displayNone == 'block')
                                    {
                                        $displayNoneBasket = 'none';
                                        $displayNoneViewed = 'none';
                                    }
                                    if ($displayNoneBasket == 'block')
                                    {
                                        $displayNone = 'none';
                                        $displayNoneViewed = 'none';
                                    }
                                    if ($displayNoneViewed == 'block')
                                    {
                                        $displayNone = 'none';
                                        $displayNoneBasket = 'none';
                                    }
                                    ?>
                                    <div id="buyer_recmon" class="tabstext active" style="display:<?=$displayNone?>">
                                        <?echo fGetFormatedProduct($arOrder["USER_ID"], $arOrder["LID"], $arRecomendetResult, $recomCnt, $arOrder["CURRENCY"], 'recom', $crmMode);?>
                                    </div>

                                    <div id="buyer_busket" class="tabstext active" style="display:<?=$displayNoneBasket?>">
                                    <?
                                        if (count($arShoppingCart) > 0)
                                            echo fGetFormatedProduct($arOrder["USER_ID"], $arOrder["LID"], $arShoppingCart, $busketCnt, $arOrder["CURRENCY"], 'busket', $crmMode);
                                    ?>
                                    </div>

                                    <div id="buyer_viewed" class="tabstext active" style="display:<?=$displayNoneViewed?>">
                                    <?
                                        if (count($arViewed) > 0)
                                            echo fGetFormatedProduct($arOrder["USER_ID"], $arOrder["LID"], $arViewed, $viewedCnt, $arOrder["CURRENCY"], 'viewed', $crmMode);
                                    ?>
                                    </div>
                                </div>
                                <script>
                                function fTabsSelect(tabText, el)
                                {
                                    BX('tab_1').className = "tabs";
                                    BX('tab_2').className = "tabs";
                                    BX('tab_3').className = "tabs";

                                    BX(el).className = "tabs active";
                                    BX(el).className = "tabs active";
                                    BX(el).style.display = 'block';

                                    BX('buyer_recmon').className = "tabstext";
                                    BX('buyer_busket').className = "tabstext";
                                    BX('buyer_viewed').className = "tabstext";
                                    BX('buyer_recmon').style.display = 'none';
                                    BX('buyer_busket').style.display = 'none';
                                    BX('buyer_viewed').style.display = 'none';

                                    BX(tabText).style.display = 'block';
                                    BX(tabText).className = "tabstext active";
                                }
                                </script>
                            </td>
                            */?>
                            <td class="summary" valign="top">
                                <div class="order-itog">
                                    <table>
                                        <tr>
                                            <td class="title"><?= GetMessage("SOD_TOTAL_PRICE")?></td>
                                            <td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($ORDER_TOTAL_PRICE, $arOrder["CURRENCY"]);?></td>
                                        </tr>
                                        <tr class="price">
                                            <td class="title"><?= GetMessage("SOD_TOTAL_PRICE_WITH_DISCOUNT_MARGIN")?></td>
                                            <td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($arOrder["DISCOUNT_VALUE"] + $arOrder["PRICE"]-$arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"]);?></td>
                                        </tr>
                                        <tr>
                                            <td class="title"><?= GetMessage("SOD_TOTAL_PRICE_DELIVERY")?></td>
                                            <td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"]);?></td>
                                        </tr>
                                        <tr>
                                            <td class="title"><?= GetMessage("SOD_TOTAL_PRICE_TAX")?></td>
                                            <td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($arOrder["TAX_VALUE"], $arOrder["CURRENCY"]);?></td>
                                        </tr>

                                        <?if (floatval($arOrder["DISCOUNT_VALUE"]) > 0):?>
                                        <tr class="price">
                                            <td class="title" >
                                                <?echo GetMessage("NEWO_TOTAL_DISCOUNT_PRICE_VALUE")?>
                                            </td>
                                            <td nowrap style="white-space:nowrap;">
                                                <div><?=SaleFormatCurrency($arOrder["DISCOUNT_VALUE"], $arOrder["CURRENCY"]);?></div>
                                            </td>
                                        </tr>
                                        <?endif;?>
                                        
                                        <? if ($ORDER_TOTAL_WEIGHT > 0) { ?>
                                            <tr>
                                                <td class="title"><?echo GetMessage("NEWO_TOTAL_WEIGHT")?></td>
                                                <td nowrap style="white-space:nowrap;">
                                                    <?=roundEx(DoubleVal($ORDER_TOTAL_WEIGHT/$WEIGHT_KOEF), SALE_VALUE_PRECISION)." ".$WEIGHT_UNIT;?>
                                                </td>
                                            </tr>
                                        <? } ?>

                                        <tr class="itog">
                                            <td class='ileft'><div style="white-space:nowrap;"><?echo GetMessage("SOD_TOTAL_PRICE_TOTAL")?></div></td>
                                            <td class='iright' nowrap><div style="white-space:nowrap;"><?=SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]);?></div></td>
                                        </tr>
                                        <? if (floatval($arOrder["SUM_PAID"]) > 0) { ?>
                                            <tr class="price">
                                                <td class="title"><?echo GetMessage("SOD_TOTAL_PRICE_PAYED")?></td>
                                                <td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($arOrder["SUM_PAID"], $arOrder["CURRENCY"]);?></td>
                                            </tr>
                                        <? } ?>
                                        <? if ($arOrder["PAYED"] == "Y") { ?>
                                            <tr class="price">
                                                <td class="title"><?echo GetMessage("SOD_TOTAL_PRICE_PAYED")?></td>
                                                <td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]);?></td>
                                            </tr>
                                        <? } ?>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        </table>

                        <script>
                                /*
                                * click on recommendet More
                                */
                                function fGetMoreProduct(type)
                                {
                                    BX.showWait();
                                    productData = <?=CUtil::PhpToJSObject($arFilterRecomendet)?>;
                                    var userId = '<?=$arOrder["USER_ID"]?>';
                                    var fUserId = '<?=$arFuserItems["ID"]?>';
                                    var currency = '<?=$arOrder["CURRENCY"]?>';
                                    var lid = '<?=$arOrder["LID"]?>';

                                    BX.ajax.post('/bitrix/admin/linemedia.auto_sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&type='+type+'&arProduct='+productData+'&currency='+currency+'&LID='+lid+'&userId='+userId+'&fUserId='+fUserId+'&ID=<?=$ID?>', fGetMoreProductResult);
                                }

                                function fGetMoreProductResult(res)
                                {
                                    BX.closeWait();
                                    var rs = eval( '('+res+')' );

                                    if (rs["ITEMS"].length > 0) {
                                        if (rs["TYPE"] == 'busket') {
                                            BX("buyer_busket").innerHTML = rs["ITEMS"];
                                        }
                                        if (rs["TYPE"] == 'recom') {
                                            BX("buyer_recmon").innerHTML = rs["ITEMS"];
                                        }
                                        if (rs["TYPE"] == 'viewed') {
                                            BX("buyer_viewed").innerHTML = rs["ITEMS"];
                                        }
                                    }
                                }
                        </script>
                    </td>
                </tr>
                <?
                $tabControl->EndCustomField("orders_itog");

        $tabControl->BeginNextFormTab();

            $tabControl->BeginCustomField("TRANSACT", GetMessage("SODN_TAB_TRANSACT"));
                ?>
                <tr>
                    <td colspan="2">
                    <?
                    $dbTransact = CSaleUserTransact::GetList(
                            array("TRANSACT_DATE" => "DESC"),
                            array("ORDER_ID" => $ID),
                            false,
                            false,
                            array("ID", "USER_ID", "AMOUNT", "CURRENCY", "DEBIT", "ORDER_ID", "DESCRIPTION", "NOTES", "TIMESTAMP_X", "TRANSACT_DATE")
                        );
                    ?>
                    <table cellpadding="3" cellspacing="1" border="0" width="100%" class="internal">
                        <tr class="heading">
                            <td><?echo GetMessage("SOD_TRANS_DATE")?></td>
                            <td><?echo GetMessage("SOD_TRANS_USER")?></td>
                            <td><?echo GetMessage("SOD_TRANS_SUM")?></td>
                            <td><?echo GetMessage("SOD_TRANS_DESCR")?></td>
                            <td><?echo GetMessage("SOD_TRANS_COMMENT")?></td>
                        </tr>
                        <?
                        $bNoTransact = True;
                        while ($arTransact = $dbTransact->Fetch())
                        {
                            $bNoTransact = False;
                            ?>
                            <tr>
                                <td><?= $arTransact["TRANSACT_DATE"]; ?></td>
                                <td>
                                    <?echo GetFormatedUserName($arTransact["USER_ID"]);?>
                                </td>
                                <td>
                                    <?
                                    echo (($arTransact["DEBIT"] == "Y") ? "+" : "-");
                                    echo SaleFormatCurrency($arTransact["AMOUNT"], $arTransact["CURRENCY"]);
                                    ?>
                                </td>
                                <td>
                                    <?
                                    if (array_key_exists($arTransact["DESCRIPTION"], $arTransactTypes))
                                        echo htmlspecialcharsEx($arTransactTypes[$arTransact["DESCRIPTION"]]);
                                    else
                                        echo htmlspecialcharsEx($arTransact["DESCRIPTION"]);
                                    ?>
                                </td>
                                <td align="right">
                                    <?echo htmlspecialcharsEx($arTransact["NOTES"]) ?>
                                </td>
                            </tr>
                            <?
                        }

                        if ($bNoTransact) {
                            ?>
                            <tr>
                                <td colspan="5" align="center">
                                    <?echo GetMessage("SOD_NO_TRANS")?>
                                </td>
                            </tr>
                            <?
                        }
                        ?>
                    </table>
                    </td>
                </tr>
                <?
            $tabControl->EndCustomField("TRANSACT", '');

        $tabControl->BeginNextFormTab();
            $tabControl->BeginCustomField("ORDER_HISTORY", GetMessage("SODN_TAB_HISTORY"));
            ?>
                <div id="trans-history"></div>
            <?
            $tabControl->EndCustomField("ORDER_HISTORY", '');

        $tabControl->Show();
    }
}
?>

<div class="sale_popup_form" id="popup_form_sku_order" style="display:none;">
    <table width="100%">
        <tr><td></td></tr>
        <tr>
            <td><small><span id="listItemPrice"></span>&nbsp;<span id="listItemOldPrice"></span></small></td>
        </tr>
        <tr>
            <td><hr></td>
        </tr>
    </table>

    <table width="100%" id="sku_selectors_list">
        <tr>
            <td colspan="2"></td>
        </tr>
    </table>

    <span id="prod_order_button"></span>
    <input type="hidden" value="" name="popup-params-product" id="popup-params-product" >
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
                content : BX("popup_form_sku_order"),
                buttons: [
                    new BX.PopupWindowButton({
                        text : '<?=GetMessageJS('SOD_POPUP_CAN_BUY_NOT');?>',
                        id : "popup_sku_save",
                        events : {
                            click : function() {
                                if (BX('popup-params-product') && BX('popup-params-product').value.length > 0)
                                {
                                    window.location = BX('popup-params-product').value;
                                    wind.close();
                                }
                            }
                        }
                    }),
                    new BX.PopupWindowButton({
                        text : '<?=GetMessageJS('SOD_POPUP_CLOSE');?>',
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

                for (var i = prop_num; i < properties_num; i++)
                {
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

                if (prop_num < properties_num-1)
                    buildSelect(cont_name, prop_num + 1, arSKU, arProperties, type);
            }

            function checkSKU(SKU, prop_num, arProperties)
            {
                for (var i = 0; i < prop_num; i++)
                {
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

                for (var i = 0; i < arSKU.length; i++)
                {
                    if (arSKU[i]["ID"] == selectedSkuId)
                    {
                        BX('popup-window-titlebar-popup_sku').innerHTML = '<span class="sale-popup-title-bar">'+arSKU[i]["NAME"]+'</span>';

                        if (arSKU[i]["DISCOUNT_PRICE"] != "")
                        {
                            BX("listItemPrice").innerHTML = arSKU[i]["DISCOUNT_PRICE_FORMATED"]+" "+arSKU[i]["VALUTA_FORMAT"];
                            BX("listItemOldPrice").innerHTML = arSKU[i]["PRICE_FORMATED"]+" "+arSKU[i]["VALUTA_FORMAT"];
                            summaFormated = arSKU[i]["DISCOUNT_PRICE_FORMATED"];
                            price = arSKU[i]["DISCOUNT_PRICE"];
                            priceFormated = arSKU[i]["DISCOUNT_PRICE_FORMATED"];
                            priceDiscount = arSKU[i]["PRICE"] - arSKU[i]["DISCOUNT_PRICE"];
                        }
                        else
                        {
                            BX("listItemPrice").innerHTML = arSKU[i]["PRICE_FORMATED"]+" "+arSKU[i]["VALUTA_FORMAT"];
                            BX("listItemOldPrice").innerHTML = "";
                            summaFormated = arSKU[i]["PRICE_FORMATED"];
                            price = arSKU[i]["PRICE"];
                            priceFormated = arSKU[i]["PRICE_FORMATED"];
                            priceDiscount = 0;
                        }

                        if (arSKU[i]["CAN_BUY"] == "Y")
                        {
                            BX('popup-params-product').value = "/bitrix/admin/linemedia.auto_sale_order_new.php?lang=<?=LANG?>&user_id="+arSKU[i]["USER_ID"]+"&LID="+arSKU[i]["LID"]+"&product[]="+arSKU[i]["ID"];
                            message = BX.message('PRODUCT_ADD');
                        }
                        else
                        {
                            BX('popup-params-product').value = '';
                            message = BX.message('PRODUCT_NOT_ADD');
                        }

                        BX.findChild(BX('popup_sku_save'), {'attr': {class: 'popup-window-button-text'}}, true, false).innerHTML = message;
                    }

                    if (arSKU[i]["ID"] == selectedSkuId)
                        break;
                }
            }
    </script>

<div id="tr-sourse" style="display:none;">
    <form name="find_form5" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
    <input type="hidden" name="ID" value="<?=$ID?>">

    <?
    $arFilterFieldsTmp = array(
        "filter_user" => GetMessage("SOA_ROW_BUYER"),
        "filter_date_history" => GetMessage("SALE_F_DATE"),
        "filter_status_id" => GetMessage("SALE_F_DATE_UPDATE"),
        "filter_payed" => GetMessage("SALE_F_ID"),
        "filter_allow_delivery" => GetMessage("SALE_F_LANG_CUR"),
        "filter_canceled" => GetMessage("SOA_F_PRICE")
    );

    $oFilter = new CAdminFilter(
        $sTableID_tab5."_filters",
        $arFilterFieldsTmp
    );

    $oFilter->SetDefaultRows(array("filter_user"));

    $oFilter->Begin();
    ?>
    <tr>
        <td><?=GetMessage('SOD_HIST_H_USER')?>:</td>
        <td>
            <?echo FindUserID("filter_user", $filter_user, "", "order_view_form");?>
        </td>
    </tr>
    <tr>
        <td><?=GetMessage('SOD_HIST_H_DATE')?>:</td>
        <td>
            <?echo CalendarPeriod("filters_date_history_from", $filters_date_history_from, "filters_date_history_to", $filters_date_history_to, "order_view_form", "Y")?>

        </td>
    </tr>
    <tr>
        <td><?=GetMessage('SOD_HIST_STATUS_ID')?>:</td>
        <td>
            <select name="filter_status_id[]" multiple size="3">
                <?
                foreach ($arOrderStatus as $key => $val)
                {
                    ?><option value="<?=$key?>"<?if (is_array($filter_status_id) && in_array($key, $filter_status_id)) echo " selected"?>>[<?= $key?>] <?= $val ?></option><?
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?=GetMessage('SOD_HIST_PAYED')?>:</td>
        <td>
            <select name="filter_payed">
                <option value=""><?echo GetMessage("SOD_HIST_ALL")?></option>
                <option value="Y"<?if ($filter_payed=="Y") echo " selected"?>><?echo GetMessage("SOD_HIST_YES")?></option>
                <option value="N"<?if ($filter_payed=="N") echo " selected"?>><?echo GetMessage("SOD_HIST_NO")?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?=GetMessage('SOD_HIST_ALLOW_DELIVERY')?>:</td>
        <td>
            <select name="filter_allow_delivery">
                <option value=""><?echo GetMessage("SOD_HIST_ALL")?></option>
                <option value="Y"<?if ($filter_allow_delivery=="Y") echo " selected"?>><?echo GetMessage("SOD_HIST_YES")?></option>
                <option value="N"<?if ($filter_allow_delivery=="N") echo " selected"?>><?echo GetMessage("SOD_HIST_NO")?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?=GetMessage('SOD_HIST_CANCELED')?>:</td>
        <td>
            <select name="filter_canceled">
                <option value=""><?echo GetMessage("SOD_HIST_ALL")?></option>
                <option value="Y"<?if ($filter_canceled=="Y") echo " selected"?>><?echo GetMessage("SOD_HIST_YES")?></option>
                <option value="N"<?if ($filter_canceled=="N") echo " selected"?>><?echo GetMessage("SOD_HIST_NO")?></option>
            </select>
        </td>
    </tr>
    <?
    $oFilter->Buttons(
        array(
            "table_id" => $sTableID_tab5,
            "url" => $APPLICATION->GetCurPage(),
            "form" => "find_form5"
        )
    );
    $oFilter->End();
    ?>
</form>
<?$lAdmin_tab5->DisplayList(array("FIX_HEADER" => false, "FIX_FOOTER" => false));?>
</div>


<script>
    //BX.ready(function(){setTimeout(function(){BX('trans-history').appendChild(BX('tr-sourse'));  BX.show(BX('tr-sourse'));},300);})
    BX.ready(function(){
        BX('trans-history').appendChild(BX('tr-sourse'));
        BX.show(BX('tr-sourse'));
    })
</script>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>