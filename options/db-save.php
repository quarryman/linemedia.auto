<?php

$LM_AUTO_MAIN_USE_BITRIX_DB = (string) $_POST['LM_AUTO_MAIN_USE_BITRIX_DB'];
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_USE_BITRIX_DB', $LM_AUTO_MAIN_USE_BITRIX_DB);

if ($LM_AUTO_MAIN_USE_BITRIX_DB != 'Y') {
    $LM_AUTO_MAIN_DB_HOST = (string) $_POST['LM_AUTO_MAIN_DB_HOST'];
    COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_DB_HOST', $LM_AUTO_MAIN_DB_HOST);
    
    $LM_AUTO_MAIN_DB_PORT = (string) $_POST['LM_AUTO_MAIN_DB_PORT'];
    COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_DB_PORT', $LM_AUTO_MAIN_DB_PORT);
    
    $LM_AUTO_MAIN_DB_NAME = (string) $_POST['LM_AUTO_MAIN_DB_NAME'];
    COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_DB_NAME', $LM_AUTO_MAIN_DB_NAME);
    
    $LM_AUTO_MAIN_DB_USER = (string) $_POST['LM_AUTO_MAIN_DB_USER'];
    COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_DB_USER', $LM_AUTO_MAIN_DB_USER);
    
    $LM_AUTO_MAIN_DB_PASS = (string) $_POST['LM_AUTO_MAIN_DB_PASS'];
    COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_DB_PASS', $LM_AUTO_MAIN_DB_PASS);
}