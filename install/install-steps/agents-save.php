<?php

define('NO_LM_AUTO_MAIN_MODULE_INSTALLED', true);

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


/*
 * Сохранённые в сессии данные предыдущих шагов установщика.
 */
$this->install_settings = (array) $_SESSION['linemedia_auto_module_install_settings'];

// Установка базы данных.
if (!$this->InstallDB()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_DB'));
}

// Установка событий.
if (!$this->InstallEvents()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_EVENTS'));
}

// Установка файлов.
if (!$this->InstallFiles()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_FILES'));
}

// Установка почтовых шаблонов.
if (!$this->InstallMessageTemplates()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_MESSAGE_TEMPLATES'));
}

// Добавление свойств интернет-магазина.
if (!$this->InstallSaleProps()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_PROPS'));
}

// Добавление параметров техподдержки.
if (!$this->InstallSupport()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_SUPPORT'));
}

/* 
 * Зарегистрируем установку модуля.
 * 
 * Регистрация необходима на последнем этапе, т.к. после нее строится подменю модуля.
 * при этом события на добавление главного меню происходят после подключения этого файла.
 */
RegisterModule('linemedia.auto');

// Установить агенты можно только если модуль уже уставнолен (!)
if (!$this->InstallAgents()) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_AGENTS'));
}