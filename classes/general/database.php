<?php

/**
 * Linemedia Autoportal
 * Main module
 * Main database class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 
 
/*
* Search through database
*/
class LinemediaAutoDatabaseAll
{
    protected $connection;
    
    protected $DBHost;
    protected $DBPort;
    protected $DBName;
    protected $DBLogin;
    protected $DBPassword;
    
    
    public function __construct()
    {
        $this->DBHost     = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DB_HOST');
        $this->DBPort     = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DB_PORT');
		$this->DBName     = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DB_NAME');
		$this->DBLogin    = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DB_USER');
		$this->DBPassword = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DB_PASS');

		$this->useBitrix  = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_USE_BITRIX_DB') == 'Y';
    }
    
}
