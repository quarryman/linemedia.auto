<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


/**
 * Linemedia Autoportal
 * Main module
 * ajax parts db create
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

$DATABASE_HOST          = strval($_REQUEST['DATABASE_HOST']);
$DATABASE_PORT          = strval($_REQUEST['DATABASE_PORT']);
$DATABASE_ROOT_USER     = strval($_REQUEST['DATABASE_ROOT_USER']);
$DATABASE_ROOT_PASSWORD = strval($_REQUEST['DATABASE_ROOT_PASSWORD']);
$DATABASE_USER          = strval($_REQUEST['DATABASE_USER']);
$DATABASE_PASSWORD      = strval($_REQUEST['DATABASE_PASSWORD']);
$DATABASE_NAME          = strval($_REQUEST['DATABASE_NAME']);

$DATABASE_USE_BITRIX    = ($_REQUEST['DATABASE_USE_BITRIX'] == 'Y');
$DATABASE_AUTO_ADD      = ($_REQUEST['DATABASE_AUTO_ADD'] == 'Y');
$DATABASE_ADD_DEMO_DATA = ($_REQUEST['DATABASE_ADD_DEMO_DATA'] == 'Y');



/*
* Используем БД битрикса
*/
if ($DATABASE_USE_BITRIX) {
    global $DBHost, $DBLogin, $DBPassword, $DBName, $DB;
    $DATABASE_HOST      = $DBHost;
    $DATABASE_USER      = $DBLogin;
    $DATABASE_PASSWORD  = $DBPassword;
    $DATABASE_NAME      = $DBName;
    
    /*
     * Используем базу Битрикса.
     */
    COption::SetOptionString('linemedia.auto', 'LM_AUTO_MAIN_USE_BITRIX_DB', 'Y');
    
    /*
     * Заполняем таблицы
     */
    $errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.auto/install/db/".$DBType."/parts-structure.sql");
    if (is_array($errors) && count($errors) > 0) {
        foreach ($errors as $error) {
            echo $error;
        }
        ShowError(GetMessage('LM_AUTO_MAIN_ERROR_CREATING_PARTS_DATABASE'));
        exit;
    }
} else {
    
    /*
     * Создать базу данных с рутовым доступом
     */
    if ($DATABASE_AUTO_ADD) {
        /*
         * ВНИМАНИЕ!
         * new CDatabase - перезатирает подключение к БД Bitrix
         */
        $database = new CDatabase();
        $success = $database->Connect($DATABASE_HOST, 'mysql', $DATABASE_ROOT_USER, $DATABASE_ROOT_PASSWORD);
        /*
         * Насильно подключимся к БД
         */
        if (defined("DELAY_DB_CONNECT") && DELAY_DB_CONNECT===true) {
            $success = $database->DoConnect();
        }
        ob_get_contents();
        ob_clean();
        
        /*
         * Подключение НЕ выполнено!
         */
        if (!$success) {
            ShowError(GetMessage('LM_AUTO_MAIN_ERROR_CONNECTING_ROOT'));
            exit;
        }
        
        /*
         * Создаём базу
         */
        $DATABASE_NAME = $database->ForSql($DATABASE_NAME);
        $DATABASE_USER = $database->ForSql($DATABASE_USER);
        if (!$database->Query("CREATE DATABASE IF NOT EXISTS $DATABASE_NAME CHARACTER SET utf8 COLLATE utf8_unicode_ci;", true)) {
            ShowError(GetMessage('LM_AUTO_MAIN_ERROR_CREATING_PARTS_DATABASE'));
            exit;
        }
        
        if (!$database->Query("GRANT ALL PRIVILEGES ON $DATABASE_NAME . * TO '$DATABASE_USER'@'%' WITH GRANT OPTION ;", true)) {
            ShowError(GetMessage('LM_AUTO_MAIN_ERROR_CREATING_PARTS_DATABASE'));
            exit;
        }
        
        /*
         * Переключимся на созданную БД
         */
        $database->Query("FLUSH PRIVILEGES;", true);
        $database->Query("USE $DATABASE_NAME;", true);
        $database->DBName = $DATABASE_NAME;
        
        
        /*
        * Заполняем таблицы
        */
        global $DBType;
        $errors = $database->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.auto/install/db/".$DBType."/parts-structure.sql");
        if(is_array($errors) && count($errors) > 0)
        {
            foreach($errors AS $error)
                echo $error;
                
            ShowError(GetMessage('LM_AUTO_MAIN_ERROR_CREATING_PARTS_DATABASE'));
            exit;
        }
        $database->Disconnect();
    }

} // DATABASE_USE_BITRIX


/*
 * База данных создана вручную или только что, автоматом. 
 * Надо прверить подключение и создать таблицы
 *
 * ВНИМАНИЕ!
 * new CDatabase - перезатирает подключение к БД Bitrix
 */
$database = new CDatabase();
$success = $database->Connect($DATABASE_HOST, $DATABASE_NAME, $DATABASE_USER, $DATABASE_PASSWORD);
/*
 * Насильно подключимся к БД
 */
if (defined("DELAY_DB_CONNECT") && DELAY_DB_CONNECT === true) {
    $success = $database->DoConnect();
}

ob_get_contents();
ob_clean();

/*
 * Подключение НЕ выполнено!
 */
if (!$success) {
    ShowError(GetMessage('LM_AUTO_MAIN_ERROR_CONNECTING_USER'));
    exit;
}

/*
* Запишем результат выполнения в настройки модуля
*/
global $DBHost, $DBLogin, $DBName, $DBPassword, $DB;
$DB->Connect($DBHost, $DBName, $DBLogin, $DBPassword);
COption::SetOptionString("linemedia.auto", "LM_AUTO_MAIN_DB_HOST",       $DATABASE_HOST);
COption::SetOptionString("linemedia.auto", "LM_AUTO_MAIN_DB_PORT",       $DATABASE_PORT);
COption::SetOptionString("linemedia.auto", "LM_AUTO_MAIN_DB_USER",       $DATABASE_USER);
COption::SetOptionString("linemedia.auto", "LM_AUTO_MAIN_DB_PASS",       $DATABASE_PASSWORD);
COption::SetOptionString("linemedia.auto", "LM_AUTO_MAIN_DB_NAME",       $DATABASE_NAME);

/*
 * Залить демо данные
 */
if ($DATABASE_ADD_DEMO_DATA) {
    $database->Connect($DATABASE_HOST, $DATABASE_NAME, $DATABASE_USER, $DATABASE_PASSWORD);
    $errors = $database->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.auto/install/db/".$DBType."/ru/parts-demo-products.sql");
    if (is_array($errors) && count($errors) > 0) {
        echo  GetMessage('LM_AUTO_MAIN_ERROR_CREATING_DEMO_DATA');
        foreach ($errors as $error) {
            echo $error;
        }
    }
}

$database->Disconnect();

die('ok');
