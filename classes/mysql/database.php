<?php

/**
 * Linemedia Autoportal
 * Main module
 * Mysql database class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
* Подключение к локальной базе данных для выбора запчастей
*/
class LinemediaAutoDatabase extends LinemediaAutoDatabaseAll
{
    public function __construct()
    {
        parent::__construct();
        
        
        if ($this->useBitrix) {
        	global $DB;
	        $this->connection = $DB;
	        return;
        }
        
        /*
         * кеширование подключения в рамках одного хита
         */
        if (isset($GLOBALS['LM_AUTO_DB_CONNECTION'])) {
			$this->connection = $GLOBALS['LM_AUTO_DB_CONNECTION'];
		} else {
        	
			if ($this->DBPort != 3306 && $this->DBPort > 0) {
			    $this->DBHost .= ':' . $this->DBPort;
			}
			
			/*
			 * Попробуем подключиться
			 */
			try {
			    
			    /*
	             * ВНИМАНИЕ!
	             * new CDatabase - перезатирает подключение к БД Bitrix
	             */
	            $database = new CDatabase();
	            $success = $database->Connect($this->DBHost, $this->DBName, $this->DBLogin, $this->DBPassword);
	            
	            /*
	            * Насильно подключимся к БД
	            */
	            if (defined("DELAY_DB_CONNECT") && DELAY_DB_CONNECT === true)
	                $success = $database->DoConnect();
	            
	            //ob_get_contents(); // fucks main template
	            //ob_clean();
	            
	            /*
	             * Подключение НЕ выполнено!
	             */
	            if (!$success) {
	                throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_CONNECTING_DB'));
	                return;
			    }
			    
			    $this->connection = $database;
			    
			    if (defined('BX_UTF') && BX_UTF == true) {
	                $this->connection->Query("SET NAMES 'utf8'");
	            } else {
	                $this->connection->Query("SET NAMES 'cp1251'");
	            }
			} catch (Exception $e) {
			    throw $e;
			}
			
			
			$GLOBALS['LM_AUTO_DB_CONNECTION'] = $database;
		}	
    }
    
    /*
    * Все вызовы транслируются в CDatabase.
    * TODO: Возможно стоить заменить на extends к LinemediaAutoDatabaseAll
    */
    public function __call($name, $arguments) {
        //if($name == 'Query') {
            
        //}
        return call_user_func_array(array($this->connection, $name), $arguments);
    }
    
}
