<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

require_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/functions.php");

if (!CModule::IncludeModule("support")) {
    ShowError(GetMessage("MODULE_NOT_INSTALL"));
    return;
}

if (!CModule::IncludeModule("iblock")) {
   ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALL"));
   return;
}

$arParams['CHECK_ACCESS'] = ($arParams['CHECK_ACCESS'] == 'N') ? 'N' : 'Y';

// Permissions
if ($arParams['CHECK_ACCESS'] == 'Y') {
    if (!($USER->IsAuthorized() && (CTicket::IsSupportClient() || CTicket::IsAdmin() || CTicket::IsSupportTeam())) ) {
        $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
    }
}

// Post
$strError = "";

$arParams["TICKET_EDIT_TEMPLATE"] = trim($arParams["TICKET_EDIT_TEMPLATE"]);
$arParams["TICKET_EDIT_TEMPLATE"] = (strlen($arParams["TICKET_EDIT_TEMPLATE"]) > 0 ? htmlspecialchars($arParams["TICKET_EDIT_TEMPLATE"]) : "ticket_edit.php?ID=#ID#");

$arParams['SHOW_COUPON_FIELD'] = (array_key_exists('SHOW_COUPON_FIELD', $arParams) && $arParams['SHOW_COUPON_FIELD'] == 'Y') ? 'Y' : 'N';

$ID = intval($_REQUEST['ID']);

$exist = ($ID <= 0);

/*
 * —обытие дл€ других модулей: получение дополнительного HTML дл€ вывода.
 */
$events = GetModuleEvents("linemedia.auto", "OnVinShowHTML");
while ($arEvent = $events->Fetch()) {
    $html []= ExecuteModuleEventEx($arEvent, array(CUser::getID(), $exist));
}

if ((strlen($_REQUEST["save"]) > 0 || strlen($_REQUEST["apply"]) > 0) && $_SERVER["REQUEST_METHOD"]=="POST" && check_bitrix_sessid()) {
    
    /*
     * —обытие дл€ других модулей: получение данных формы.
     */
    $events = GetModuleEvents('linemedia.auto', 'OnVinGetRequestData');
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array(&$_REQUEST));
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    if ($ID <= 0) {
        if (intval($_REQUEST['AUTO_ID']) == 0 && strlen(trim($_REQUEST['vin'])) <= 0) {
            $strError .= GetMessage('SUP_ERROR_VIN').'<br />';
        }
                
        if (strlen(trim($_REQUEST["MESSAGE"])) <= 0) {
            $strError .= GetMessage('SUP_ERROR_PARTS').'<br />';
        }
                
        if (!$USER->IsAuthorized()) {
            if (strlen(trim($_REQUEST["NAME"])) <= 0) {
                $strError .= GetMessage('SUP_ERROR_NAME').'<br />';
            }
            if (isset($arParams['REQ_F_LAST_NAME']) && $arParams['REQ_F_LAST_NAME'] == 'Y') {
                if (strlen(trim($_REQUEST["LAST_NAME"])) <= 0) {
                    $strError .= GetMessage('SUP_ERROR_LAST_NAME').'<br />';
                }
            }
            if (isset($arParams['REQ_F_PHONE']) && $arParams['REQ_F_PHONE'] == 'Y') {
                if (strlen(trim($_REQUEST["PHONE"])) <= 0) {
                    $strError .= GetMessage('SUP_ERROR_PHONE').'<br />';
                }
            }
            if (strlen(trim($_REQUEST["EMAIL"]))<=0 || check_email(trim($_REQUEST["EMAIL"])) === false){
                $strError .= GetMessage('SUP_ERROR_EMAIL').'<br />';
            }
        }
        
        if (isset($_REQUEST['REQUIRED']) && is_array($_REQUEST['REQUIRED']) && count($_REQUEST['REQUIRED']) > 0) {
            foreach ($_REQUEST['REQUIRED'] AS $sKey => $sValue) {
                if (empty($_REQUEST['FIELD'][$sKey]) && isset($_REQUEST['FIELD_NAME'][$sKey])) {
                    $strError .= GetMessage('SUP_FIELD').'"'.$_REQUEST['FIELD_NAME'][$sKey]. '"'.GetMessage('SUP_EMPTY').'<br />';
                }
            }
            unset($sKey, $sValue);
        }
    } else {
        if (strlen(trim($_REQUEST['MESSAGE'])) <= 0) {
            $strError .= GetMessage('SUP_ERROR_MESSAGE').'<br />';
        }
    }

    if (strlen(trim($_REQUEST["vin"])) > 0 && CUser::IsAuthorized()) {
        
        $arPropertyCodes = array(
            'vin',
            'extra',
        );

        $arPropertyData = array();
        foreach ($_POST as $k => $v) {
            if (in_array($k, $arPropertyCodes)) {
                $arPropertyData[$k] = trim($v);
                if ('extra' == $k) {
                    $arPropertyData[$k] = array('VALUE' => array('TEXT' => trim($v), 'TYPE' => 'text'));
                }
            }
        }
    }

    $arFILES = array();
    if (is_array($_FILES) && count($_FILES) > 0) {
        foreach ($_FILES as $key => $arFILE) {
            if (strlen($arFILE["name"]) > 0) {
                $arFILE["MODULE_ID"] = "support";
                $arFILES[] = $arFILE;
            }
        }
    }
    
    if (is_array($arFILES) && count($arFILES) > 0) {
        $max_size = COption::GetOptionString("support", "SUPPORT_MAX_FILESIZE");
        $max_size = intval($max_size)*1024;

        foreach ($arFILES as $key => $arFILE) {
            if (intval($arFILE["size"]) > $max_size || intval($arFILE["error"]) > 0) {
                $strError .= str_replace("#FILE_NAME#", $arFILE["name"], GetMessage("SUP_MAX_FILE_SIZE_EXCEEDING"))."<br>";
            }
        }
    }
    
    $arParams["TICKET_LIST_URL"] = trim($arParams["TICKET_LIST_URL"]);
    $arParams["TICKET_LIST_URL"] = (strlen($arParams["TICKET_LIST_URL"]) > 0 ? htmlspecialchars($arParams["TICKET_LIST_URL"]) : "ticket_list.php");

    if ($strError == "") {
        $bSetTicket = false;
        if ($arParams['ID'] > 0) {
            if (CTicket::IsAdmin()) {
                $bSetTicket = true;
            } else {
                $rsTicket = CTicket::GetByID($arParams["ID"], SITE_ID, $check_rights = "Y", $get_user_name = "N", $get_extra_names = "N");
                if ($arTicket = $rsTicket->GetNext()) {
                    $bSetTicket = true;
                }
            }
        } else {
            $bSetTicket = true;
        }
        
        if ($bSetTicket) {
            if ($_REQUEST["OPEN"] == "Y") {
                $_REQUEST["CLOSE"] = "N";
            }
            if ($_REQUEST["CLOSE"] == "Y") {
                $_REQUEST["OPEN"] = "N";
            }
            
            $arFields = array(
                'SITE_ID'                   => SITE_ID,
                'TITLE'                     => GetMessage('SUP_NEW_TICKET_FOR_TITLE'),
                'CLOSE'                     => $_REQUEST['CLOSE'],
                'CRITICALITY_ID'            => $_REQUEST['CRITICALITY_ID'],
                'CATEGORY_ID'               => $arParams['CATEGORY_ID'],
                'MARK_ID'                   => $_REQUEST['MARK_ID'],
                'HIDDEN'                    => 'N',
                'FILES'                     => $arFILES,
                'COUPON'                    => $_REQUEST['COUPON'],
                'PUBLIC_EDIT_URL'           => $APPLICATION->GetCurPage(),
                'SOURCE_SID'                => 'web',
                'CREATED_MODULE_NAME'       => 'linemedia.auto',
                'OWNER_USER_ID'             => CUser::GetID(),
            );

            $sMsg = '';
            
            $arFields['TITLE'] .= (string) $_REQUEST['vin'];
            
            if (!CUser::IsAuthorized()) {
                if (strlen(trim($_REQUEST["LAST_NAME"])) > 0) {
                    $sMsg .= "\n" . GetMessage('SUP_LAST_NAME') . ': ' . trim($_REQUEST["LAST_NAME"]);
                }
                if (strlen(trim($_REQUEST["NAME"])) > 0) {
                    $sMsg .= "\n" . GetMessage('SUP_NAME') . ': ' . trim($_REQUEST["NAME"]);
                }
                if (strlen(trim($_REQUEST["SECOND_NAME"])) > 0) {
                    $sMsg .= "\n" . GetMessage('SUP_SECOND_NAME') . ': ' . trim($_REQUEST["SECOND_NAME"]);
                }
                if (strlen(trim($_REQUEST["PHONE"])) > 0) {
                    $sMsg .= "\n" . GetMessage('SUP_PHONE') . ': ' . trim($_REQUEST["PHONE"]);
                }
                if (strlen(trim($_REQUEST["EMAIL"])) > 0) {
                    $sMsg .= "\n" . GetMessage('SUP_EMAIL') . ': ' . trim($_REQUEST["EMAIL"]);
                }
                $sMsg .= "\n\n";
            }
            
            if (intval($arParams['ID']) == 0) {
                $sMsg .= GetMessage('SUP_VIN') . ': ' . $_REQUEST['vin'];
            }
            if (strlen(trim($_REQUEST['extra'])) > 0) {
                $sMsg .= "\n" . GetMessage('SUP_COMPLETE') . ': ' . $_REQUEST['extra'];
            }
            
            if (isset($_REQUEST['FIELD']) && is_array($_REQUEST['FIELD']) && count($_REQUEST['FIELD']) > 0) {
                foreach ($_REQUEST['FIELD'] as $sKey => $sValue) {
                    if (!empty($sValue) && isset($_REQUEST['FIELD_NAME'][$sKey])) {
                        $sMsg .= "\n" . $_REQUEST['FIELD_NAME'][$sKey] . ': ' . $sValue;
                    }
                }
                unset($sKey, $sValue);
            }

            $arFields['OWNER_SID'] = trim($_REQUEST['EMAIL']);
            
            
            if (intval($arParams['ID']) > 0) {
                $sMsg .= $_REQUEST['MESSAGE'];
            } else {
                $sMsg .= "\n\n" . GetMessage('SUP_PART_DESCRIPTION') . ': ' . $_REQUEST['MESSAGE'];
            }
            
            $arFields['MESSAGE'] = $sMsg;
            unset($sMsg);
            
            /*
             * —обытие дл€ других модулей: до создани€ тикета.
             */
            $events = GetModuleEvents('linemedia.auto', 'OnVinBeforeRequest');
            while ($arEvent = $events->Fetch()) {
                try {
                    ExecuteModuleEventEx($arEvent, array(&$arFields));
                } catch (Exception $e) {
                    throw $e;
                }
            }
            
            
            $ID = CTicket::SetTicket($arFields, $ID, "N", $NOTIFY = "Y");
            
            
            /*
             * —обытие дл€ других модулей: после создани€ тикета.
             */
            $events = GetModuleEvents('linemedia.auto', 'OnVinAfterRequest');
            while ($arEvent = $events->Fetch()) {
                try {
                    ExecuteModuleEventEx($arEvent, array($ID, $arFields));
                } catch (Exception $e) {
                    throw $e;
                }
            }
            
            
            /*
             * –едирект на страницу обращени€.
             */
            if (intval($ID) > 0) {
                if (CUser::IsAuthorized()) {
                    if (intval($arParams["ID"]) > 0) {
                        LocalRedirect(
                            CComponentEngine::MakePathFromTemplate(
                                $arParams["TICKET_EDIT_TEMPLATE"],
                                array(
                                    "ID" => $ID
                                )
                            )
                        );
                    } else {
                        LocalRedirect($arParams['TICKET_LIST_URL']);
                    }
                } else {
                    ShowMessage(array('TYPE' => 'OK', 'MESSAGE' => GetMessage('SUP_REQUEST_SEND')));
                    $arParams['HIDE_FORM'] = true;
                }
            } else {
                $ex = $APPLICATION->GetException();
                if ($ex) {
                    $strError .= $ex->GetString() . '<br>';
                } else {
                    $strError .= GetMessage('SUP_ERROR') . '<br>';
                }
            }
        } else {
            LocalRedirect($arParams["TICKET_LIST_URL"]);
        }
    }
}

// Result array
$arResult = Array(
    "HTML" => $html,
    "TICKET" => Array(),
    "MESSAGES" => Array(),
    "ONLINE" => Array(),
    "DICTIONARY" => Array(
        "MARK" => Array(),
        "CRITICALITY" => Array(),
        "CRITICALITY_DEFAULT" => "",
        "CATEGORY" => Array(),
        "CATEGORY_DEFAULT" => "",
    ),
    "ERROR_MESSAGE" => $strError,
    "REAL_FILE_PATH" => (strlen($_SERVER["REAL_FILE_PATH"]) > 0 ? htmlspecialchars($_SERVER["REAL_FILE_PATH"]) : htmlspecialchars($APPLICATION->GetCurPage())),
    "NAV_STRING" => "",
    "NAV_RESULT" => null,
    "OPTIONS" => Array(
        "ONLINE_INTERVAL" => intval(COption::GetOptionString("support", "ONLINE_INTERVAL")),
        "MAX_FILESIZE" => intval(COption::GetOptionString("support", "SUPPORT_MAX_FILESIZE")),
    ),
);

$arParams["ID"] = (intval($arParams["ID"]) > 0 ? intval($arParams["ID"]) : intval($_REQUEST["ID"]));
$rsTicket = CTicket::GetByID($arParams["ID"], SITE_ID, $check_rights = "Y", $get_user_name = "N", $get_extra_names = "N");

if ($rsTicket && $arTicket = $rsTicket->GetNext()) {
    // +Ticket and user names
    $arResult["TICKET"] = $arTicket +
    _GetUserInfo($arTicket["RESPONSIBLE_USER_ID"], "RESPONSIBLE") +
    _GetUserInfo($arTicket["OWNER_USER_ID"], "OWNER") +
    _GetUserInfo($arTicket["CREATED_USER_ID"], "CREATED") +
    _GetUserInfo($arTicket["MODIFIED_USER_ID"], "MODIFIED_BY");
    
    // Dictionary table
    $arDictionary = Array(
        "C" => Array("CATEGORY", intval($arTicket["CATEGORY_ID"])),
        "K" => Array("CRITICALITY", intval($arTicket["CRITICALITY_ID"])),
        "S" => Array("STATUS", intval($arTicket["STATUS_ID"])),
        "M" => Array("MARK", intval($arTicket["MARK_ID"])),
        "SR" => Array("SOURCE", intval($arTicket["SOURCE_ID"]))
    );

    // +Ticket dictionary
    $arResult["TICKET"] += _GetDictionaryInfoEx($arDictionary);


    // +Sla
    $arResult["TICKET"]["SLA_NAME"] = $arResult["TICKET"]["SLA_DESCRIPTION"] = "";
    $rsSla = CTicketSLA::GetByID($arTicket["SLA_ID"]);
    if ($arSla = $rsSla->Fetch()) {
        $arResult["TICKET"]["SLA_NAME"] = htmlspecialchars($arSla["NAME"]);
        $arResult["TICKET"]["SLA_DESCRIPTION"] = htmlspecialchars($arSla["DESCRIPTION"]);
    }

    // Messages files
    $arMessagesFiles = Array();
    $rsFiles = CTicket::GetFileList($v1="s_id", $v2="asc", array("TICKET_ID" => $arParams["ID"]));
    {
        while ($arFile = $rsFiles->Fetch()) {
            $name = strlen($arFile["ORIGINAL_NAME"])>0 ? $arFile["ORIGINAL_NAME"] : $arFile["FILE_NAME"];
            if (strlen($arFile["EXTENSION_SUFFIX"]) > 0) {
                $suffix_length = strlen($arFile["EXTENSION_SUFFIX"]);
                $name = substr($name, 0, strlen($name)-$suffix_length);
            }
            $arMessagesFiles[$arFile["MESSAGE_ID"]][] = array("HASH" => $arFile["HASH"], "NAME" => htmlspecialchars($name), "FILE_SIZE" => $arFile["FILE_SIZE"]);
        }
    }

    // +Messages
    $arParams["MESSAGES_PER_PAGE"] = (intval($arParams["MESSAGES_PER_PAGE"]) <= 0 ? 20 : intval($arParams["MESSAGES_PER_PAGE"]));

    $arFilter = Array(
        "TICKET_ID" => $arParams["ID"],
        "TICKET_ID_EXACT_MATCH" => "Y",
        "IS_MESSAGE" => "Y"
    );

    CPageOption::SetOptionString("main", "nav_page_in_session", "N");

    // Sort config
    $order = $arParams["MESSAGE_SORT_ORDER"];

    $rsMessage = CTicket::GetMessageList($by, $order, $arFilter, $is_filtered, $check_rights = "Y", $get_user_name = "Y");
    $rsMessage->NavStart($arParams["MESSAGES_PER_PAGE"]);

    $arResult["NAV_STRING"] = $rsMessage->GetPageNavString(GetMessage("SUP_PAGES"));
    $arResult["NAV_RESULT"] = $rsMessage;

    while ($arMessage = $rsMessage->GetNext()) {
        if (array_key_exists($arMessage["ID"], $arMessagesFiles)) {
            $arFiles["FILES"] = $arMessagesFiles[$arMessage["ID"]];
        } else {
            $arFiles["FILES"] = Array();
        }
        
        $arMessage["MESSAGE"] =TxtToHTML(
            $arMessage["~MESSAGE"],
            $bMakeUrls = true,
            $iMaxStringLen = $arParams["MESSAGE_MAX_LENGTH"],
            $QUOTE_ENABLED = "Y",
            $NOT_CONVERT_AMPERSAND = "N",
            $CODE_ENABLED = "Y",
            $BIU_ENABLED ="Y",
            $quote_table_class      = "support-quote-table",
            $quote_head_class       = "support-quote-head",
            $quote_body_class       = "support-quote-body",
            $code_table_class       = "support-code-table",
            $code_head_class        = "support-code-head",
            $code_body_class        = "support-code-body",
            $code_textarea_class    = "support-code-textarea",
            $link_class                 = ""
        );

        $arResult["MESSAGES"][] =
            $arMessage +
            $arFiles +
            _GetUserInfo($arMessage["OWNER_USER_ID"], "OWNER") +
            _GetUserInfo($arMessage["CREATED_USER_ID"], "CREATED") +
            _GetUserInfo($arMessage["MODIFIED_USER_ID"], "MODIFIED_BY");
    }
    
    // Online
    CTicket::UpdateOnline($arParams["ID"], $USER->GetID());
    $rsOnline = CTicket::GetOnline($arParams["ID"]);
    while ($arOnline = $rsOnline->GetNext()) {
        $arResult["ONLINE"][] = $arOnline;
    }

    $ticketSite = $arTicket["SITE_ID"];
    $ticketSla = $arTicket["SLA_ID"];
} else {
    $ticketSite = SITE_ID;
    $ticketSla = CTicketSLA::GetForUser();
    $arResult["DICTIONARY"]["CRITICALITY_DEFAULT"] = CTicketDictionary::GetDefault("K", $ticketSite);
    $arResult["DICTIONARY"]["CATEGORY_DEFAULT"] = CTicketDictionary::GetDefault("C", $ticketSite);
}


// Mark, Category, Criticality dictionary list
$ticketDictionary = CTicketDictionary::GetDropDownArray($ticketSite, $ticketSla);
$arResult["DICTIONARY"]["MARK"] = _GetDropDownDictionary("M", $ticketDictionary);
$arResult["DICTIONARY"]["CRITICALITY"] = _GetDropDownDictionary("K", $ticketDictionary);
$arResult["DICTIONARY"]["CATEGORY"] = _GetDropDownDictionary("C", $ticketDictionary);

unset($rsTicket);
unset($rsMessage);
unset($arMessagesFiles);
unset($ticketDictionary);

// Set Title
$arParams["SET_PAGE_TITLE"] = ($arParams["SET_PAGE_TITLE"] == "N" ? "N" : "Y" );

if ($arParams["SET_PAGE_TITLE"] == "Y") {
    if (empty($arResult["TICKET"])) {
        $APPLICATION->SetTitle(GetMessage("SUP_NEW_TICKET_TITLE"));
    } else {
        $APPLICATION->SetTitle(GetMessage("SUP_EDIT_TICKET_TITLE", array("#ID#" => $arResult["TICKET"]["ID"], "#TITLE#" => $arResult["TICKET"]["TITLE"])));
    }
}

$this->IncludeComponentTemplate();
