<?php

define('NO_LM_AUTO_MAIN_MODULE_INSTALLED', true);

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


/*
 * ���������� � ������ ������ ���������� ����� �����������.
 */
$this->install_settings = (array) $_SESSION['linemedia_auto_module_install_settings'];

// ��������� ���� ������.
if (!$this->InstallDB()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_DB'));
}

// ��������� �������.
if (!$this->InstallEvents()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_EVENTS'));
}

// ��������� ������.
if (!$this->InstallFiles()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_FILES'));
}

// ��������� �������� ��������.
if (!$this->InstallMessageTemplates()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_MESSAGE_TEMPLATES'));
}

// ���������� ������� ��������-��������.
if (!$this->InstallSaleProps()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_PROPS'));
}

// ���������� ���������� ������������.
if (!$this->InstallSupport()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_SUPPORT'));
}

/* 
 * �������������� ��������� ������.
 * 
 * ����������� ���������� �� ��������� �����, �.�. ����� ��� �������� ������� ������.
 * ��� ���� ������� �� ���������� �������� ���� ���������� ����� ����������� ����� �����.
 */
RegisterModule('linemedia.auto');

// ���������� ������ ����� ������ ���� ������ ��� ���������� (!)
if (!$this->InstallAgents()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_AGENTS'));
}