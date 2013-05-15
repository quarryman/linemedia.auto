<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

require_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.auto/include.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/linemedia.auto/install/index.php');

$wizard = &$this->GetWizard();

// Установка модуля.
if (!IsModuleInstalled('linemedia.auto')) {
    
    // Экземпляр класса.
    $installer = new linemedia_auto();
    
    /*
     * Установим параметры по умолчанию для установки модуля.
     */
    $installer->setInstallSettings(array(
        'demo_folder' => array(
            'install' => 'Y',
            'rewrite' => 'Y',
            'path'    => WIZARD_SITE_DIR.'auto/'
        )
    ));
    
    
    /*
     * Запрос на регистрацию в API
     */
    $api = new LinemediaAutoApiDriver();
    $version = array('sitename' => $_SERVER['HTTP_HOST']);
    $response = $api->query('requestNewAccount', $version);
    if ($response['status'] == 'error') {
        throw new Exception(GetMessage('LM_AUTO_MAIN_API_REGISTER_ERROR') . ': ' . $response['error']['error_text']);
        die();
    } else {
        $id = (int) $response['data']['id'];
        $secret = (string) $response['data']['secret'];
        
        COption::SetOptionInt("linemedia.auto", "LM_AUTO_MAIN_API_ID", $id);
        COption::SetOptionString("linemedia.auto", "LM_AUTO_MAIN_API_KEY", $secret);
    }
    
    /*
     * Предустановка опций.
     */
     COption::SetOptionString("linemedia.auto", "LM_AUTO_MAIN_PART_SEARCH_PAGE", WIZARD_SITE_DIR.'auto/search/#ARTICLE#/');
    
    
    // Установка базы данных.
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
        throw new Exception(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_DB'));
        exit();
    }
    
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
        throw new Exception(GetMessage('LM_AUTO_MAIN_ERROR_CONNECTING_USER'));
        exit();
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
    $database->Connect($DATABASE_HOST, $DATABASE_NAME, $DATABASE_USER, $DATABASE_PASSWORD);
    
    $errors = $database->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.auto/install/db/".$DBType."/ru/parts-demo-products.sql");
    if (is_array($errors) && count($errors) > 0) {
        throw new Exception(GetMessage('LM_AUTO_MAIN_ERROR_CREATING_DEMO_DATA').': '.print_r($errors, true));
    }
    
    $database->Disconnect();
    
    
    if (!CModule::IncludeModule('sale')) {
        throw new Exception(GetMessage('LM_AUTO_MAIN_ERROR_MODULE_SALE'));
        exit();
    }
    
    /*
     * Если в системе нет плательзиков - добавляем.
     */
    $person = CSalePersonType::GetList(array(), array('LID' => WIZARD_SITE_ID));
    if ($person->SelectedRowsCount <= 0) {
        CSalePersonType::Add(array('LID' => WIZARD_SITE_ID, 'NAME' => GetMessage('LM_AUTO_DEMO_SALE_PERSON_PHYSICAL')));
        CSalePersonType::Add(array('LID' => WIZARD_SITE_ID, 'NAME' => GetMessage('LM_AUTO_DEMO_SALE_PERSON_JURIDICAL')));
    }
    unset($person);
    
    /*
     * Если в системе нет способа оплат - добавляем.
     */
    $payment = CSalePaySystem::GetList(array(), array('LID' => WIZARD_SITE_ID))->Fetch();
    if (!$payment) {
        CSalePaySystem::Add(array('LID' => WIZARD_SITE_ID, 'NAME' => GetMessage('LM_AUTO_DEMO_SALE_PAY_SYSTEM')));
    }
    
    
    // Установка инфоблоков.
    include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/linemedia.auto/install/install-steps/iblocks-save.php");
    
    // Установка событий.
    if (!$installer->InstallEvents()) {
        throw new Exception(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_EVENTS'));
        exit();
    }
    
    // Установка файлов.
    if (!$installer->InstallFiles()) {
        throw new Exception(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_FILES'));
        exit();
    }
    
    // Установка почтовых шаблонов.
    if (!$installer->InstallMessageTemplates()) {
        throw new Exception(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_MESSAGE_TEMPLATES'));
        exit();
    }
    
    // Добавление свойств интернет-магазина.
    if (!$installer->InstallSaleProps()) {
        throw new Exception(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_PROPS'));
        exit();
    }
    
    /* 
     * Зарегистрируем установку модуля.
     * 
     * Регистрация необходима на последнем этапе, т.к. после нее строится подменю модуля.
     * при этом события на добавление главного меню происходят после подключения этого файла.
     */
    RegisterModule('linemedia.auto');
    
    // Установить агенты можно только если модуль уже уставнолен (!)
    if (!$installer->InstallAgents()) {
        throw new Exception(GetMessage('LM_AUTO_MAIN_ERROR_INSTALL_AGENTS'));
        exit();
    }
    
}
