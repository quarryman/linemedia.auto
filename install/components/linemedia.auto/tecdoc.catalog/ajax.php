<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", true);

$sLM_AUTOPORTAL_TECDOC_ACCESS_LIST_IBLOCK_ID = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_TECDOC_ACCESS_LIST', false);

$sAction = (isset($_REQUEST['a']) && strlen(trim($_REQUEST['a'])) > 0) ? trim($_REQUEST['a']) : false;
$sSection = (isset($_REQUEST['s']) && strlen(trim($_REQUEST['s'])) > 0) ? trim($_REQUEST['s']) : false;

$sID = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : false;

$sComponentID = 'LM_TECDOC_CATALOG';

if ($USER->IsAdmin()) {
    if (CModule::IncludeModule("linemedia.auto") && CModule::IncludeModule('iblock')) {
        
        $oEl = new  CIBlockElement();
        if ($sSection !== false) {
            switch ($sSection) {
                case 'BRANDS':
                case 'MODEL_GROUPS':
                case 'MODELS':
                case 'MODIFICATION':
                case 'GROUPS':
                // Обработка брендов, групп моделей, модели, модификации, групп запчастей
                if ($sID !== false) {
                    if ($sAction == 'enable') {
                        // Показываем раздел
                        $aItem = $oEl->GetList(Array('ID' => 'DESC'), Array('PROPERTY_API_SECTION' => $sSection, 'PROPERTY_API_ID' => $sID, 'PROPERTY_COMPONENT' => $sComponentID, 'IBLOCK_ID' => $sLM_AUTOPORTAL_TECDOC_ACCESS_LIST_IBLOCK_ID), false, false, Array('ID', 'IBLOCK_ID'))->Fetch();
                        if (isset($aItem['ID'])) {
                            $oEl->Delete($aItem['ID']);
                        }
                    } elseif ($sAction == 'disable') {
                        // Скрываем раздел
                        $a = $oEl->Add(array(
                                'IBLOCK_ID' => $sLM_AUTOPORTAL_TECDOC_ACCESS_LIST_IBLOCK_ID,
                                'IBLOCK_SECTION_ID' => false,
                                'MODIFIED_BY' => $USER->GetID(),
                                'ACTIVE' => 'Y',
                                'NAME' => $sSection . ' - ' . $sID,
                                'PROPERTY_VALUES' => Array(
                                    'api_section' => $sSection,
                                    'api_id' => $sID,
                                    'component' => $sComponentID,
                                )
                            ));
                    }
                }
                break;
            
            case 'SET_GROUP_FOR_ALL_AUTO':
                // Почистим все настройки для групп
                $oItemsRes = $oEl->GetList(Array('ID' => 'DESC'), Array('PROPERTY_API_SECTION' => 'GROUPS', 'PROPERTY_COMPONENT' => $sComponentID, 'IBLOCK_ID' => $sLM_AUTOPORTAL_TECDOC_ACCESS_LIST_IBLOCK_ID), false, false, Array('ID', 'IBLOCK_ID'));
                while ($aItem = $oItemsRes->Fetch()) {
                    $oEl->Delete($aItem['ID']);
                }
                unset($oItemsRes, $aItem);
                
                if (is_array($sID) && count($sID) > 0) {
                    foreach ($sID AS $sItem) {
                        $sCode = substr($sItem, strrpos($sItem, ';') + 1, (strlen($sItem) - strrpos($sItem, ';') + 1));
                        if (strlen($sCode) > 0) {
                            $oEl->Add(Array(
                                    'IBLOCK_ID' => $sLM_AUTOPORTAL_TECDOC_ACCESS_LIST_IBLOCK_ID,
                                    'IBLOCK_SECTION_ID' => false,
                                    'MODIFIED_BY' => $USER->GetID(),
                                    'ACTIVE' => 'Y',
                                    'NAME' => 'GROUPS - /' . $sCode,
                                    'PROPERTY_VALUES' => Array(
                                        'api_section' => 'GROUPS',
                                        'api_id' => '/' . $sCode,
                                        'component' => $sComponentID,
                                    )
                                ));
                        }
                    }
                    unset($sItem);
                }
                break;
            }
        } else {
            echo ShowError(GetMessage('ERROR_NO_SET_SECTION'));
        }
    } else {
        echo ShowError(GetMessage('ERROR_NO_INSTALL_MODULES'));
    }
} else {
    echo ShowError(GetMessage('ERROR_PERMISSION_DENIED'));
}

unset($sLM_AUTOPORTAL_TECDOC_ACCESS_LIST_IBLOCK_ID);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
