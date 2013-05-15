<?php


/*
* Удаление инфоблоков
*/
if ($_POST['REMOVE_IBLOCKS'] == 'Y') {
    global $DB;
    CModule::IncludeModule('iblock');
    
    $DB->StartTransaction();
    if (!CIBlockType::Delete('linemedia_auto')) {
        $DB->Rollback();
        ShowError('Error removing iblocks');
    }
    $DB->Commit();
}



/*
* Удаление таблиц в БД запчастей
* Удаление производится через отдельное подключение, поскольку неизвестно, где хранятся таблицы
*/
if ($_POST['REMOVE_PARTS'] == 'Y') {

    $DATABASE_HOST     = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DB_HOST');
    $DATABASE_PORT     = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DB_PORT');
	$DATABASE_NAME     = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DB_NAME');
	$DATABASE_USER     = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DB_USER');
	$DATABASE_PASSWORD = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DB_PASS');

    if ($DATABASE_PORT && $DATABASE_PORT != 3306) {
        $DATABASE_HOST .= ':' . $DATABASE_PORT;
    }

    /*
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

    /*
     * Подключение НЕ выполнено!
     */
    if (!$success) {
        die (GetMessage('LM_AUTO_MAIN_ERROR_CONNECTING_USER'));
    }
    
    global $DBType;
    $errors = $database->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.auto/install/db/".$DBType."/parts-structure-uninstall.sql");
    if (is_array($errors) && count($errors) > 0) {
        echo  GetMessage('LM_AUTO_MAIN_ERROR_REMOVING_PARTS_DATA');
        foreach ($errors as $error) {
            ShowError($error);
        }
    }
    
    $database->Disconnect();
}




/*
* Удаление прайслистов
*/
if($_POST['REMOVE_PRICELISTS'] == 'Y')
{
    DeleteDirFilesEx("/upload/linemedia.auto/");
}




if (!$this->UnInstallDB() || !$this->UnInstallEvents() || !$this->UnInstallFiles()) {
    return;
}
UnRegisterModule( $this->MODULE_ID );
