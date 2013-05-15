<?php

/*
 * Данный файл выводит список поставщиков и предназначен в первую очередь для совместимости системы с предыдущими версиями оффлайнового клиента
 */

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$login      = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SUPPLIERS_LIST_LOGIN', 'x');
$password   = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SUPPLIERS_LIST_PASSWORD', 'y');

$use = (!empty($login) || !empty($password));

/*
 * HTTP-авторизация.
 */
if ($use) {
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="Forbidden"');
        header('HTTP/1.0 401 Unauthorized');
        exit();
    }
    
    if ($_SERVER['PHP_AUTH_USER'] != $login || $_SERVER['PHP_AUTH_PW'] != $password) {
        header('WWW-Authenticate: Basic realm="Forbidden"');
        header('HTTP/1.0 401 Unauthorized');
        exit();
    }
}


CModule::IncludeModule('iblock');


/*
* Вывод поставщиков в ответ
*/
$iSuppliersIBlockID = COption::GetOptionString('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');

$oSuppRes = CIBlockElement::GetList(
                array(),
                array('IBLOCK_ID' => $iSuppliersIBlockID),
                false,
                false,
                array('ID', 'NAME', 'SORT', 'IBLOCK_TYPE_ID', 'IBLOCK_ID', 'PROPERTY_supplier_id')
            );


header('Content-Type: text/html; charset=utf-8');

while ($aSuppItem = $oSuppRes->Fetch()) {
    $name = $aSuppItem['NAME'];
    if (!defined('BX_UTF') || BX_UTF != true) {
        $name = iconv('cp1251', 'UTF-8', $name);
    }
	echo $aSuppItem['PROPERTY_SUPPLIER_ID_VALUE'] . '->' . $name . "<br />";
}
