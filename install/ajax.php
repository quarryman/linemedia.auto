<?php

/**
 * Linemedia Autoportal
 * Main module
 * Ajax installation
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


define('AJAX', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

IncludeModuleLangFile(__FILE__);

/*
 * ������ ������������
 */
if (!check_bitrix_sessid()) {
    die('Incorrect session');
}


/*
 * ���������� ������ ������, ������ ��� �� ��� �� ����������
 * ���� �������� ������ � autoloading ������
 */
include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/include.php');


$action = (string) $_REQUEST['action'];

if ($action == 'register_api') {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/install/install-steps/api-save.php');
    exit();
}

/*
 * �������� �� � ���������� � ����������� ������
 */
if ($action == 'create_parts_db') {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/install/install-steps/parts-db-save.php');
    exit();
}
