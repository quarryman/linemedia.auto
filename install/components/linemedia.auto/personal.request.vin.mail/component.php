<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/*
 * Компонент отправляет запрос по VIN через почту.
 */

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError(GetMessage('LM_AUTOPORTAL_MODULE_NOT_INSTALL'));
    return;
}

$arResult['HTML'] = array();

/*
 * Событие для других модулей: получение дополнительного HTML для вывода.
 */
$events = GetModuleEvents("linemedia.auto", "OnVinShowHTML");
while ($arEvent = $events->Fetch()) {
    $arResult['HTML'] []= ExecuteModuleEventEx($arEvent, array(CUser::getID(), true));
}


if (!empty($_REQUEST['save']) && $_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid()) {
    
    $arResult['FIELDS'] = $_REQUEST;
    
    $arResult['ERRORS'] = array();
    
    /*
     * Событие для других модулей: получение данных формы.
     */
    $events = GetModuleEvents('linemedia.auto', 'OnVinGetRequestData');
    while ($arEvent = $events->Fetch()) {
        try {
            ExecuteModuleEventEx($arEvent, array(&$_REQUEST));
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    if (strlen(trim($_REQUEST['vin'])) <= 0) {
        $arResult['ERRORS'] []= GetMessage('SUP_ERROR_VIN');
    }
    
    if (strlen(trim($_REQUEST['MESSAGE'])) <= 0) {
        $arResult['ERRORS'] []= GetMessage('SUP_ERROR_PARTS');
    }
    
    if (!CUser::IsAuthorized()) {
        if (strlen(trim($_REQUEST['NAME'])) <= 0) {
            $arResult['ERRORS'] []= GetMessage('SUP_ERROR_NAME');
        }
        if (strlen(trim($_REQUEST['PHONE'])) <= 0) {
            $arResult['ERRORS'] []= GetMessage('SUP_ERROR_PHONE');
        }
        if (strlen(trim($_REQUEST['EMAIL'])) <= 0 || check_email(trim($_REQUEST['EMAIL'])) === false) {
            $arResult['ERRORS'] []= GetMessage('SUP_ERROR_EMAIL');
        }
    }
    
    
    /*
     * Формирование письма.
     */
    if (empty($arResult['ERRORS'])) {
        $arFields = array();
        
        $arFields['TITLE'] = (string) $_REQUEST['vin'];
        
        if (!CUser::IsAuthorized()) {
            if (strlen(trim($_REQUEST['NAME'])) > 0) {
                $message .= "\n".GetMessage('SUP_NAME').": ".trim((string) $_REQUEST['NAME']);
            }
            if (strlen(trim($_REQUEST['SECOND_NAME'])) > 0) {
                $message .= "\n".GetMessage('SUP_SECOND_NAME').": ".trim((string) $_REQUEST['SECOND_NAME']);
            }
            if (strlen(trim($_REQUEST['LAST_NAME'])) > 0) {
                $message .= "\n".GetMessage('SUP_LAST_NAME').": ".trim((string) $_REQUEST['LAST_NAME']);
            }
            if (strlen(trim($_REQUEST['PHONE'])) > 0) {
                $message .= "\n".GetMessage('SUP_PHONE').": ".trim((string) $_REQUEST['PHONE']);
            }
            if (strlen(trim($_REQUEST['EMAIL'])) > 0) {
                $message .= "\n".GetMessage('SUP_EMAIL').": ".trim((string) $_REQUEST['EMAIL']);
            }
        } else {
            global $USER;
            $message .= "\n".GetMessage('SUP_NAME').": ".$USER->GetFullName();
            $message .= "\n".GetMessage('SUP_EMAIL').": ".$USER->GetParam('EMAIL');
            $message .= "\n".GetMessage('SUP_UID').": ".$USER->GetID();
        }
        
        if (strlen(trim($_REQUEST['extra'])) > 0) {
            $message .= "\n\n".GetMessage('SUP_COMPLETE').":\n".trim((string) $_REQUEST['extra']);
        }
        $message .= "\n\n".GetMessage('SUP_PART_DESCRIPTION').":\n".trim((string) $_REQUEST['MESSAGE']);
        
        $arFields['MESSAGE'] = $message;
        
        /*
         * Событие для других модулей: до отправки письма.
         */
        $events = GetModuleEvents('linemedia.auto', 'OnVinBeforeRequest');
        while ($arEvent = $events->Fetch()) {
            try {
                ExecuteModuleEventEx($arEvent, array(&$arFields));
            } catch (Exception $e) {
                throw $e;
            }
        }
        
        $arResult['FIELDS'] = array();
        
        /*
         * Отправка письма.
         */
        $arEventFields = array( 
            "TITLE"     => $arFields['TITLE'],
            "MESSAGE"   => $arFields['MESSAGE'],
        );
        if ($eventId = CEvent::Send('LM_AUTO_VIN_SEND_MAIL', SITE_ID, $arEventFields)) {
            $arResult['MESSAGE'] = GetMessage('SUP_REQUEST_SEND');
        } else {
            $arResult['ERRORS'] []= GetMessage('SUP_ERROR_SEND');
        }
        
         
        /*
         * Событие для других модулей: после отправки письма.
         */
        $events = GetModuleEvents('linemedia.auto', 'OnVinAfterRequest');
        while ($arEvent = $events->Fetch()) {
            try {
                ExecuteModuleEventEx($arEvent, array($eventId, $arEventFields));
            } catch (Exception $e) {
                throw $e;
            }
        }
    }
}


$this->IncludeComponentTemplate();
