<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError(GetMesasge('LM_AUTO_MAIN_ERROR_MODULE_INSTALL'));
    return;
}

// apply default param values
$arDefaultValues = array(
	"SHOW_FIELDS" => array(),
	"REQUIRED_FIELDS" => array(),
	"AUTH" => "Y",
	"USE_BACKURL" => "Y",
	"SUCCESS_PAGE" => "",
);

foreach ($arDefaultValues as $key => $value) {
	if (!is_set($arParams, $key)) {
		$arParams[$key] = $value;
    }
}
if (!is_array($arParams["SHOW_FIELDS"])) {
	$arParams["SHOW_FIELDS"] = array();
}
if (!is_array($arParams["REQUIRED_FIELDS"])) {
	$arParams["REQUIRED_FIELDS"] = array();
}


// if user registration blocked - return auth form
if (COption::GetOptionString("main", "new_user_registration", "N") == "N") {
	$APPLICATION->AuthForm(array());
}

// apply core fields to user defined
$arDefaultFields = array(
	"LOGIN",
	"PASSWORD",
	"CONFIRM_PASSWORD",
	"EMAIL",
);

$arResult["USE_EMAIL_CONFIRMATION"] = COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y" ? "Y" : "N";
$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
if ($def_group <> "") {
	$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(explode(",", $def_group));
} else {
	$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(array());
}

$arResult["SHOW_FIELDS"] = array_merge($arDefaultFields, $arParams["SHOW_FIELDS"]);
$arResult["REQUIRED_FIELDS"] = array_merge($arDefaultFields, $arParams["REQUIRED_FIELDS"]);

// Используется ли CAPTCHA
$arResult["USE_CAPTCHA"] = COption::GetOptionString("main", "captcha_registration", "N") == "Y" ? "Y" : "N";

// Используется ли e-mail в качетсве логина.
$arParams['USE_EMAIL_AS_LOGIN'] = ($arParams['USE_EMAIL_AS_LOGIN'] == 'Y');

// Предлагать подписку.
$arParams['GET_SUBSCRIBE'] = ($arParams['GET_SUBSCRIBE'] == 'Y');

if (!is_array($arParams['SUBSCRIBE_RUBRICS'])) {
    $arParams['SUBSCRIBE_RUBRICS'] = array();
}

// Начальные значения.
$arResult["VALUES"] = array();
$arResult["ERRORS"] = array();
$arResult["HTML"]   = '';
$register_done = false;

/*
 * Получаем данные для работы с профилем покупателя.
 */
if (!empty($arParams['PERSON_SALE_PROFILE_FIELDS'])) {
    CModule::IncludeModule('sale');
    
    // Список типов плательзиков.
    $rs = CSalePersonType::GetList(array(), array('LID' => SITE_ID));
    $arResult['PERSON_TYPES'] = array();
    $first = true;
    while ($pt = $rs->Fetch()) {
        if (empty($_REQUEST['PERSON_TYPE_ID']) && $first) {
            $pt['SELECTED'] = 'Y';
            $first = false;
        } else {
            if ($_REQUEST['PERSON_TYPE_ID'] == $pt['ID']) {
                $pt['SELECTED'] = 'Y';
            }
        }
        $arResult['PERSON_TYPES'][$pt['ID']] = $pt;
    }
    
    // Свойства по плательщикам.
    $rs = CSaleOrderProps::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y', 'USER_PROPS' => 'Y'));
    $arResult['SALE_ORDER_PROPS'] = array();
    while ($prop = $rs->Fetch()) {
        if ($prop['TYPE'] == 'SELECT') {
            $db_vars = CSaleOrderPropsVariant::GetList(
                    array('SORT' => 'ASC'),
                    array('ORDER_PROPS_ID' => $prop['ID'])
            );
            while ($vars = $db_vars->Fetch()) {
                $prop['VALUES'][$vars['ID']] = $vars['NAME'];
            }
        }
        $arResult['SALE_ORDER_PROPS'][$prop['PERSON_TYPE_ID']][$prop['ID']] = $prop;
    }
}

// Событие главного модуля: показ формы регистрации.
$events = GetModuleEvents('linemedia.auto', 'OnShowRegisterForm');
while ($arEvent = $events->Fetch()) {
    $arResult['HTML'] .= ExecuteModuleEventEx($arEvent, array(&$arResult['VALUES']));
}

// Регистрация пользователя.
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["register_submit_button"]) && !CUser::IsAuthorized()) {
    
	if (COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y') {
		// possible encrypted user password
		$sec = new CRsaSecurity();
		if (($arKeys = $sec->LoadKeys())) {
			$sec->SetKeys($arKeys);
			$errno = $sec->AcceptFromForm(array('REGISTER'));
			if ($errno == CRsaSecurity::ERROR_SESS_CHECK) {
				$arResult["ERRORS"][] = GetMessage("main_register_sess_expired");
			} elseif ($errno < 0) {
				$arResult["ERRORS"][] = GetMessage("main_register_decode_err", array("#ERRCODE#" => $errno));
            }
		}
	}
    
    // Использовать e-mail в качаестве логина.
    if ($arParams['USE_EMAIL_AS_LOGIN'] === true){
        // Убираем LOGIN из обязательных полей.
        $iUnsetKey = array_search('LOGIN', $arResult['REQUIRED_FIELDS']);
        if ($iUnsetKey !== false) {
            unset($arResult['REQUIRED_FIELDS'][$iUnsetKey]);
        }
        unset($iUnsetKey);
    }
    
	// Проверка входных значений.
	foreach ($arResult["SHOW_FIELDS"] as $key) {
		if ($key != "PERSONAL_PHOTO" && $key != "WORK_LOGO") {
			$arResult["VALUES"][$key] = $_REQUEST["REGISTER"][$key];
			if (in_array($key, $arResult["REQUIRED_FIELDS"]) && trim($arResult["VALUES"][$key]) == '')
				$arResult["ERRORS"][$key] = GetMessage("REGISTER_FIELD_REQUIRED");
		} else {
			$_FILES["REGISTER_FILES_".$key]["MODULE_ID"] = "main";
			$arResult["VALUES"][$key] = $_FILES["REGISTER_FILES_".$key];
			if (in_array($key, $arResult["REQUIRED_FIELDS"]) && !is_uploaded_file($_FILES["REGISTER_FILES_".$key]["tmp_name"])) {
				$arResult["ERRORS"][$key] = GetMessage("REGISTER_FIELD_REQUIRED");
            }
		}
	}

	if (isset($_REQUEST["REGISTER"]["TIME_ZONE"])) {
		$arResult["VALUES"]["TIME_ZONE"] = $_REQUEST["REGISTER"]["TIME_ZONE"];
    }
    
	// Проверка CAPTCHA.
	if ($arResult["USE_CAPTCHA"] == "Y") {
		if (!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"])) {
			$arResult["ERRORS"][] = GetMessage("REGISTER_WRONG_CAPTCHA");
		}
	}

	if (strlen($arResult["VALUES"]["EMAIL"]) > 0 && COption::GetOptionString("main", "new_user_email_uniq_check", "N") === "Y") {
		$res = CUser::GetList($b, $o, array("=EMAIL" => $arResult["VALUES"]["EMAIL"]));
		if ($res->Fetch()) {
			$arResult["ERRORS"][] = GetMessage("REGISTER_USER_WITH_EMAIL_EXIST", array("#EMAIL#" => htmlspecialcharsbx($arResult["VALUES"]["EMAIL"])));
        }
    }
    
    $arResult['VALUES']['PERSON_TYPE'] = (int) $_REQUEST['PERSON_TYPE_ID'];
    $arResult['VALUES']['SALE_PROPS']  = (array) $_REQUEST['SALE_PROPS'];
    foreach ($arResult['SALE_ORDER_PROPS'][$arResult['VALUES']['PERSON_TYPE']] as $id => $property) {
        if (!in_array($property['ID'], $arParams['PERSON_SALE_PROFILE_FIELDS'])) {
            continue;
        }
        if (empty($arResult['VALUES']['SALE_PROPS'][$id])) {
            $arResult['ERRORS'][] = str_replace("#FIELD_NAME#", $property['NAME'], GetMEssage('LM_AUTO_MAIN_ERROR_SALE_FIELD_EMPTY'));
        }
    }
    
    // Использовать e-mail в качаестве логина.
    if ($arParams['USE_EMAIL_AS_LOGIN']) {
        $arResult['VALUES']['LOGIN'] = $arResult['VALUES']['EMAIL'];
    }
    
	if (count($arResult["ERRORS"]) > 0) {
		if (COption::GetOptionString("main", "event_log_register_fail", "N") === "Y") {
			$arError = $arResult["ERRORS"];
			foreach ($arError as $key => $error) {
				if (intval($key) == 0 && $key !== 0) {
					$arError[$key] = str_replace("#FIELD_NAME#", '"'.$key.'"', $error);
                }
            }
			CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", false, implode("<br>", $arError));
		}
	} else {
        // Создание пользователя.
		$bConfirmReq = COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y";
        
		$arResult['VALUES']["CHECKWORD"] = randString(8);
		$arResult['VALUES']["~CHECKWORD_TIME"] = $DB->CurrentTimeFunction();
		$arResult['VALUES']["ACTIVE"] = $bConfirmReq ? "N": "Y";
		$arResult['VALUES']["CONFIRM_CODE"] = $bConfirmReq ? randString(8): "";
		$arResult['VALUES']["LID"] = SITE_ID;

		$arResult['VALUES']["USER_IP"] = $_SERVER["REMOTE_ADDR"];
		$arResult['VALUES']["USER_HOST"] = @gethostbyaddr($REMOTE_ADDR);
		
		if ($arResult["VALUES"]["AUTO_TIME_ZONE"] <> "Y" && $arResult["VALUES"]["AUTO_TIME_ZONE"] <> "N") {
			$arResult["VALUES"]["AUTO_TIME_ZONE"] = "";
        }
		$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
		if ($def_group != "") {
			$arResult['VALUES']["GROUP_ID"] = explode(",", $def_group);
        }
		$bOk = true;

		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("USER", $arResult["VALUES"]);
        
		$events = GetModuleEvents('main', 'OnBeforeUserRegister');
		while ($arEvent = $events->Fetch()) {
			if (ExecuteModuleEventEx($arEvent, array(&$arResult['VALUES'])) === false) {
				if ($err = $APPLICATION->GetException()) {
					$arResult['ERRORS'] []= $err->GetString();
                }
				$bOk = false;
				break;
			}
		}
		
		// Событие: до регистрации пользователя.
		$events = GetModuleEvents('linemedia.auto', 'OnBeforeUserRegister');
        while ($arEvent = $events->Fetch()) {
            if (ExecuteModuleEventEx($arEvent, array(&$arResult['VALUES'])) === false) {
                if ($err = $APPLICATION->GetException()) {
                    $arResult['ERRORS'] []= $err->GetString();
                }
                $bOk = false;
                break;
            }
        }
		
        
        /*
         * Добавление нового пользователя.
         */
		if ($bOk) {
			$user = new CUser();
			$ID = $user->Add($arResult['VALUES']);
		}

		if (intval($ID) > 0) {
			$register_done = true;

			// Авторизация пользователя
			if ($arParams["AUTH"] == "Y" && $arResult["VALUES"]["ACTIVE"] == "Y") {
				if (!$arAuthResult = $USER->Login($arResult["VALUES"]["LOGIN"], $arResult["VALUES"]["PASSWORD"])) {
					$arResult['ERRORS'] []= $arAuthResult;
                }
			}

			$arResult['VALUES']["USER_ID"] = $ID;

			$arEventFields = $arResult['VALUES'];
			
            
            /*
             * Письмо пользователю с данными о регистрации.
             */
            $event = new CEvent();
            $event->SendImmediate("LM_AUTO_NEW_USER", SITE_ID, $arEventFields);
            
            unset($arEventFields['PASSWORD']);
            unset($arEventFields['CONFIRM_PASSWORD']);
            
            
            /*
             * Оповещение о регистрации нового пользователя.
             */
			$event = new CEvent();
			$event->SendImmediate("NEW_USER", SITE_ID, $arEventFields);
			if ($bConfirmReq) {
				$event->SendImmediate("NEW_USER_CONFIRM", SITE_ID, $arEventFields);
            }
            
            // Событие: поcле регистрации пользователя.
            $events = GetModuleEvents('linemedia.auto', 'OnAfterUserRegister');
            while ($arEvent = $events->Fetch()) {
                if (ExecuteModuleEventEx($arEvent, array($ID, &$arResult['VALUES'])) === false) {
                    if ($err = $APPLICATION->GetException()) {
                        $arResult['ERRORS'] []= $err->GetString();
                    }
                    break;
                }
            }
            
            
            /*
             * Создание профиля пользователя.
             */
            if (!empty($arParams['PERSON_SALE_PROFILE_FIELDS'])) {
                CModule::IncludeModule('sale');
                
                $buyerProfileID = CSaleOrderUserProps::Add(array('USER_ID' => $ID, 'PERSON_TYPE_ID' => $arResult['VALUES']['PERSON_TYPE'], 'NAME' => GetMessage('LM_AUTO_MAIN_DEFAULT_PROFILE')));
                
                $rs = CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $arResult['VALUES']['PERSON_TYPE'], 'USER_PROPS' => 'Y'));
                while ($prop = $rs->GetNext()) {
                    if (isset($arResult['VALUES']['SALE_PROPS'][$prop['ID']])) {
                        $arFields = array(
                            "USER_PROPS_ID"     => $buyerProfileID,
                            "ORDER_PROPS_ID"    => $prop['ID'],
                            "NAME"              => $prop['NAME'],
                            "VALUE"             => $arResult['VALUES']['SALE_PROPS'][$prop['ID']]
                        );
                        CSaleOrderUserPropsValue::Add($arFields);
                    }
                }
            }
            
            /*
             * Подписка.
             */
            if ($arParams['GET_SUBSCRIBE'] && $_REQUEST['GET_SUBSCRIBE'] == 'Y' && count($arParams['SUBSCRIBE_RUBRICS']) > 0) {
                CModule::IncludeModule('subscribe');
                
                $subscribe = new CSubscription();
                $rubrics = $arParams['SUBSCRIBE_RUBRICS'];

                $arSubscribeParams = array(
                    'RUB_ID'        => $rubrics,
                    'SEND_CONFIRM'  =>'N',
                    'USER_ID'       => $ID,
                    'ACTIVE'        => 'Y',
                    'EMAIL'         => $arResult['VALUES']['EMAIL'],
                    'FORMAT'        => 'html',
                    'CONFIRMED'     => 'Y',
                    'DATE_CONFIRM'  => date('d.n.Y'),
                    'TO_FIELD'      => $arResult['VALUES']['EMAIL']
                );
                
                $id = $subscribe->Add($arSubscribeParams, SITE_ID);
            }
            
		} else {
			$arResult['ERRORS'][] = $user->LAST_ERROR;
		}

		if (count($arResult["ERRORS"]) <= 0) {
			if (COption::GetOptionString("main", "event_log_register", "N") === "Y") {
				CEventLog::Log("SECURITY", "USER_REGISTER", "main", $ID);
            }
		} else {
			if (COption::GetOptionString("main", "event_log_register_fail", "N") === "Y") {
				CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", $ID, implode("<br>", $arResult["ERRORS"]));
            }
		}

		$events = GetModuleEvents("main", "OnAfterUserRegister");
		while ($arEvent = $events->Fetch()) {
			ExecuteModuleEventEx($arEvent, array(&$arResult['VALUES']));
        }
	}
}

// Если пользователь зарегистрирован - делаем редирект на обратную ссылку.
if ($register_done) {
	if ($arParams["USE_BACKURL"] == "Y" && $_REQUEST["backurl"] <> '') {
		LocalRedirect($_REQUEST["backurl"]);
	} elseif ($arParams["SUCCESS_PAGE"] <> '') {
		LocalRedirect($arParams["SUCCESS_PAGE"]);
    }
}

$arResult["VALUES"] = htmlspecialcharsEx($arResult["VALUES"]);

// redefine required list - for better use in template
$arResult["REQUIRED_FIELDS_FLAGS"] = array();
foreach ($arResult["REQUIRED_FIELDS"] as $field) {
	$arResult["REQUIRED_FIELDS_FLAGS"][$field] = "Y";
}
// check backurl existance
$arResult["BACKURL"] = htmlspecialcharsbx($_REQUEST["backurl"]);

// get countries list
if (in_array("PERSONAL_COUNTRY", $arResult["SHOW_FIELDS"]) || in_array("WORK_COUNTRY", $arResult["SHOW_FIELDS"])) 
	$arResult["COUNTRIES"] = GetCountryArray();

// get date format
if (in_array("PERSONAL_BIRTHDAY", $arResult["SHOW_FIELDS"])) 
	$arResult["DATE_FORMAT"] = CLang::GetDateFormat("SHORT");

// ********************* User properties ***************************************************
$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
if (is_array($arUserFields) && count($arUserFields) > 0) {
	if (!is_array($arParams["USER_PROPERTY"])) {
		$arParams["USER_PROPERTY"] = array($arParams["USER_PROPERTY"]);
    }
	foreach ($arUserFields as $FIELD_NAME => $arUserField) {
		if (!in_array($FIELD_NAME, $arParams["USER_PROPERTY"]) && $arUserField["MANDATORY"] != "Y") {
			continue;
        }
		$arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
		$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
		$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
		$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
	}
}
if (!empty($arResult["USER_PROPERTIES"]["DATA"])) {
	$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
	$arResult["bVarsFromForm"] = (count($arResult['ERRORS']) <= 0) ? false : true;
}
// ******************** /User properties ***************************************************

// initialize captcha
if ($arResult["USE_CAPTCHA"] == "Y") {
	$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
}
// set title
if ($arParams["SET_TITLE"] == "Y") {
	$APPLICATION->SetTitle(GetMessage("REGISTER_DEFAULT_TITLE"));
}
//time zones
$arResult["TIME_ZONE_ENABLED"] = CTimeZone::Enabled();
if ($arResult["TIME_ZONE_ENABLED"]) {
	$arResult["TIME_ZONE_LIST"] = CTimeZone::GetZones();
}
$arResult["SECURE_AUTH"] = false;
if (!CMain::IsHTTPS() && COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y') {
	$sec = new CRsaSecurity();
	if (($arKeys = $sec->LoadKeys())) {
		$sec->SetKeys($arKeys);
		$sec->AddToForm('regform', array('REGISTER[PASSWORD]', 'REGISTER[CONFIRM_PASSWORD]'));
		$arResult["SECURE_AUTH"] = true;
	}
}

// all done
$this->IncludeComponentTemplate();
