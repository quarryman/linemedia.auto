<?php

$LM_AUTO_MAIN_API_ID = (int) $_POST['LM_AUTO_MAIN_API_ID'];
COption::SetOptionString( $sModuleId, 'LM_AUTO_MAIN_API_ID', $LM_AUTO_MAIN_API_ID);

$LM_AUTO_MAIN_API_KEY = (string) $_POST['LM_AUTO_MAIN_API_KEY'];
COption::SetOptionString( $sModuleId, 'LM_AUTO_MAIN_API_KEY', $LM_AUTO_MAIN_API_KEY);

$LM_AUTO_MAIN_API_URL = (string) $_POST['LM_AUTO_MAIN_API_URL'];
COption::SetOptionString( $sModuleId, 'LM_AUTO_MAIN_API_URL', $LM_AUTO_MAIN_API_URL);

$LM_AUTO_MAIN_API_FORMAT = (string) $_POST['LM_AUTO_MAIN_API_FORMAT'];
COption::SetOptionString( $sModuleId, 'LM_AUTO_MAIN_API_FORMAT', $LM_AUTO_MAIN_API_FORMAT);


$LM_AUTO_MAIN_API_INFORM_TECDOC = $_POST['LM_AUTO_MAIN_API_INFORM_TECDOC'] == 'Y';
COption::SetOptionString( $sModuleId, 'LM_AUTO_MAIN_API_INFORM_TECDOC', $LM_AUTO_MAIN_API_INFORM_TECDOC ? 'Y':'N');


/*
* ������ ��������� � ������������� ��������� ������ � ���
*/
LinemediaAutoEventMain::OnAdminInformerInsertItems_addLinemediaAccountCheck();