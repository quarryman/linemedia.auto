<?php
/**
 * Linemedia Autoportal
 * Main module
 * Import prices agent
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 
/*
CModule::IncludeModule('linemedia.auto');
LinemediaAutoImportAgent::run();
*/
 
 
IncludeModuleLangFile(__FILE__);

class LinemediaAutoImportAgent
{
    static $run_string = 'LinemediaAutoImportAgent::run();';
        
    
    /*
     * ������ ���� ������
     */
    protected $database;
    
    
    /**
     * �������� ������� ������
     */
    public static function run() 
    {
    	/*
    	* ������� ������ � ���
    	*/
    	LinemediaAutoDebug::setOutputFilename($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/logs/import.log');
    
    	/*
		 * � ���� �� ����?
		 */
		if (!self::checkCron()) {
			$ar = Array(
			   "MESSAGE" => GetMessage('LM_AUTO_MAIN_NEED_CRON'),
			   "TAG" => "LM_NEED_CRON",
			   "MODULE_ID" => "linemedia.auto",
			   "ENABLE_CLOSE" => "N"
			);
			$ID = CAdminNotify::Add($ar);
			
			/*
			 * ��������� ��������� ������ �� �� ��� �����
			 */
			return self::$run_string;
			
		} else {
			CAdminNotify::DeleteByTag("LM_NEED_CRON");
		}
        
    
        $agent = new LinemediaAutoImportAgent();
        $files = $agent->getNewFiles();
        
        if (count($files) > 0) {
            $agent->prepareImportData();
            
            /*
             * ������ �����
             */
            foreach ($files as $file) {
                $agent->parseFile($file);
            }
        }
        
        
        /*
         * ������� ������ ������
         */
        $agent->cleanupOldFiles();
        
        
        /*
         * C������ ��������� ��������� ������
         * ���� �� ������������� ����� � �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterPriceListAllImport");
		while ($arEvent = $events->Fetch()) {
		    try {
			    ExecuteModuleEventEx($arEvent, array(count($files), $files));
			} catch (Exception $e) {
			    throw $e;
			}
	    }
        
        return self::$run_string;
    }
    
    
    /**
     * �������� ������� ����� ������
     */
    public static function getNewFiles()
    {
    	$path = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/new/';
    	$files = array();
    	foreach (glob($path . '*.csv') as $filename) {
    		$files[] = basename($filename);
    	}
    	    	
        /*
         * ��� �������
         */
        if (count($files) < 1) {
            return;
        }
        
        /*
         * ��������� ���� �� ������
         */
        foreach ($files as $i => $file) {
            $filename = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/new/' . $file;
            
            /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_FOUND', array('#FILE#' => $file)));
            
            /*
             * ������ �� ����
             */
            if (!is_readable($filename)) {
                LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_NOT_READABLE', array('#FILE#' => $file)));
                
                self::moveIncorrectFile($file);
                unset($files[$i]);
            }
            
            /*
             * �������� �� ���� �� �����?
             */
            if ($handle = fopen($filename, 'r')) {
                // http://php.net/manual/en/function.flock.php
                if (!flock($handle, LOCK_EX)) {
                    unset($files[$i]);
                } else {
	                flock($handle, LOCK_UN);
                }                
                fclose($handle);
            }
        }
        
        return $files;
    }
            
            
            
    /**
     * ������ ���������� �������� ��� �������
     */
    public function prepareImportData()
    {
        /*
         * ���������� ������
         */
        CModule::IncludeModule('linemedia.auto');
        
        /*
         * ������ ������� � API
         */
        $api = new LinemediaAutoApiDriver();
        
        
        /*
         * ����������� � ��
         */
        $this->database = new LinemediaAutoDatabase();
        
        
        /*
         * ��������� ��� ������� ������ UTF, ������ ��� ��������� ������� ��������� ��������� ��������
         */
        $this->database->Query("SET NAMES 'utf8'");
    }
        
        
    /**
     * ������ �����.
     */
    public function parseFile($file)
    {
        $filename = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/new/' . $file;
        
        /*
         * ����� ���������� ����������
         */
        LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_START', array('#FILE#' => $file)));
        
        
        $filename_parts = explode('_', $file);
        $supplier_id = (int) $filename_parts[0];
        
        /*
         * ������� ������ ����������
         */
        $supplier = new LinemediaAutoSupplier($supplier_id);
        if (!$supplier->exists()) {
            LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_SUPPLIER_NOT_FOUND', array('#SUPPLIER#' => $supplier_id)), false, LM_AUTO_DEBUG_ERROR);
            $this->moveIncorrectFile($file);
            return;
        }
        
        
        /*
         * C������ ������ �������� ������
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforePriceListImport");
		while ($arEvent = $events->Fetch()) {
		    try {
			    ExecuteModuleEventEx($arEvent, array(&$filename, &$supplier_id));
			} catch (Exception $e) {
			    throw $e;
			}
	    }
        
        /*
         * ������� ������ ����������
         */ 
        $this->zeroSupplierProducts($supplier_id);
        
        /*
         * ��������� ����
         */
        try {
            $handle = fopen($filename, "r");
            
            // ���������� ������������� ����.
            if (!flock($handle, LOCK_EX)) {
                fclose($handle);
                return;
            }
        } catch (Exception $e) {
            LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_OPEN_ERROR', array('#ERROR#' => $e->GetMessage())));
            return;
        }
        
        /*
         * �������� ��������� �����.
         * ��� ������ ���� UTF-8.
         */
        $cmd = 'file -bi ' . escapeshellarg($filename);
        $cmd_result = system($cmd, $cmd_result);
        $response = explode(';', $cmd_result);
        $charset  = explode('=', $response[1]);
        $encoding = trim($charset[1]);
        if ($encoding != 'utf-8') {
            $from = 'cp1251'; // ������ ��� file -bi ������������ cp1251 � iso-8859-1. �� � ���-�� ��� 1251 ��� ������
	        $cmd = 'iconv -f ' . $from . ' -t utf8 "' . $filename . '" -o "' . $filename . '.tmp"';
	        system($cmd, $cmd_result);
	        unlink($filename);
	        rename($filename . '.tmp', $filename);
        }
        
        /*
         * ��������� ������.
         */
        @setlocale(LC_ALL, "ru_RU");
        
        
        /*
         * �������� ���������������� ����.
         */
        $lmfields = new LinemediaAutoCustomFields();
        
        $custom_fields = $lmfields->getFields();
        
        
        /*
         * ��������� ����������� ������
         */
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            /*
             * ����������� ����� � CSV
             *
             * ������ ������:
             * AN113K;AKEBONO;"�������� ������ AN-113K";1052;10;-1
             *
             * ����� ������
             * AN113K;AKEBONO;"�������� ������ AN-113K";1052;10;-1;300;custom1;custom2;custom3
             */
            $brand_title    = trim($data[0]);
            $article        = trim($data[1]);
            $title          = trim($data[2]);
            $price          = trim($data[3]);
            $quantity       = trim($data[4]);
            $group_id       = trim($data[5]); // ������� group_id
            $weight         = trim($data[6]);
            
            /*
             * ���������� ������������� �����.
             */
            $index = 7;
            
            /*
             * �� ����������� ����� � ����������� 0
             */
            if (floatval($quantity) == 0) {
            	continue;
            }
            $original_article = $article;
            $article = LinemediaAutoPartsHelper::clearArticle($article);
            
            /*
             * ������� �������� � ��
             */
            $sql_title              = $this->database->ForSQL($title);
            $sql_article            = $this->database->ForSQL($article);
            $sql_original_article   = $this->database->ForSQL($original_article);
            $sql_brand_title        = $this->database->ForSQL($brand_title);
            $sql_price              = $this->database->ForSQL($price);
            $sql_quantity           = $this->database->ForSQL($quantity);
            $sql_group_id           = $this->database->ForSQL($group_id);
            $sql_weight             = $this->database->ForSQL($weight);
            
            /*
             * ������� ��������� �������� � ��.
             */
            $sql_custom_fields = '';
            $sql_custom_values = '';
            if (!empty($custom_fields)) {
                $custom_fields_vars = array();
                $custom_values_vars = array();
                foreach ($custom_fields as $custom_field) {
                    $custom_fields_vars []= "`".$custom_field['code']."`";
                    $custom_values_vars []= "'".$this->database->ForSQL(trim($data[$index]))."'";
                    $index++;
                }
                $sql_custom_fields = ', '.implode(', ', $custom_fields_vars);
                $sql_custom_values = ', '.implode(', ', $custom_values_vars);
            }
            
            $sql = "
                INSERT INTO `b_lm_products` (
                    `title`,
                    `article`,
                    `original_article`,
                    `brand_title`,
                    `price`,
                    `quantity`,
                    `group_id`,
                    `weight`,
                    `supplier_id`
                     $sql_custom_fields
                ) VALUES (
                    '$sql_title',
                    '$sql_article',
                    '$sql_original_article',
                    '$sql_brand_title',
                    '$sql_price',
                    '$sql_quantity',
                    '$sql_group_id',
                    '$sql_weight',
                    '$supplier_id'
                     $sql_custom_values
                );
            ";
            
            $this->database->Query($sql);
            
            $count++;
            
        }
        fclose($handle);
        
        LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_FINISHED', array('#FILE#' => $file)));
        
        /*
         * ���������� ���� � ������� �����������
         */
        $this->moveCorrectFile($file);
        
        /*
         * ������� ��������� �������� ������
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterPriceListImport");
		while ($arEvent = $events->Fetch()) {
		    try {
			    ExecuteModuleEventEx($arEvent, array($supplier_id, $count));
			} catch (Exception $e) {
			    throw $e;
			}
	    }
    }
    
    
    /**
     * ����������� ��������� ���� � ��������������� �����
     */
    public static function moveIncorrectFile($file)
    {
        $old_filename = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/new/' . $file;
        $new_filename = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/error/' . $file;
        try {
            rename($old_filename, $new_filename);
            LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_INCORRECT_MOVED', array('#FILE#' => $file)));
        } catch (Exception $e) {
        	LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_INCORRECT_MOVED_ERROR', array('#FILE#' => $file)));
        }
    }
    
    
    /**
     * ����������� ������ ����������� ���� � �����
     */
    public static function moveCorrectFile($file)
    {
        $new_folder = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/success/' . date('Y_m_d')  . '/';
        
        if (!file_exists($new_folder)) {
            mkdir($new_folder);
        }
        $old_filename = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/new/' . $file;
        $new_filename = $new_folder . $file;
        try {
            rename($old_filename, $new_filename);
            LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_CORRECT_MOVED', array('#FILE#' => $file)));
        } catch (Exception $e) {
            LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_CORRECT_MOVED_ERROR', array('#FILE#' => $file)));
        }
    }
    
    
    /**
     * �������� ���������� ������� � ����������
     */
    public function zeroSupplierProducts($supplier_id)
    {
        $supplier_id = (int) $supplier_id;
        try {
	    	$this->database->Query('DELETE FROM b_lm_products WHERE supplier_id=' . $supplier_id);
        } catch (Exception $e) {
	        throw $e;
	        return false;
        }
        
        /*
         * ����� ���������� ����������
         */
        LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_SUPPLIER_PRODUCTS_REMOVED', array('#SUPPLIER#' => $supplier_id)));
    }
    
    
    
    /**
     * ������� ������ ������.
     */
    public function cleanupOldFiles()
    {
	    $lifetime_days = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS', 14);
	    $lifetime = $lifetime_days * 86400;
	    
	    /*
	    * ������ ������� ����������� ����������
	    */
	    $root = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/success/'; 
	    $success_folders = scandir($root);
	    foreach ($success_folders as $folder) {
	       if (!in_array($folder, array(".", "..")) && is_dir($root . $folder)) {
	       		try {
		       		$this->cleanupDir($root . $folder . '/', $lifetime);
	       		} catch (Exception $e) {
		       		
	       		}
            }
	    }
	    
	    /*
	     * �� ��������������� ������ ���� ������, ������ ��� ���
	     */
	    $root = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/error/'; 
   		try {
       		$this->cleanupDir($root, $lifetime, false);
   		} catch (Exception $e) {
       		
   		}
    }
    
    
    /**
     * ������ ����� ����������
     */
    private function cleanupDir($path, $lifetime, $rm_emty_dirs = true)
    {
    	/*
    	 * ������� ��� CSV
    	 */
    	foreach (glob($path . '*.csv') as $filename) {
    		if (time() - filemtime($filename) > $lifetime) {
	    		unlink($filename);
    		}
    	}
    	
    	
    	/*
    	 * ����� ����� � ����� �������?
    	 */
    	if ($rm_emty_dirs) {
	    	$count = 0;
	    	$files = scandir($path);
		    foreach ($files as $file) {
		       if (!in_array($file, array(".", ".."))) {
		          $count++;
		       } 
		    }
		    if ($count == 0) {
		    	rmdir($path);
            }
	    }
    }
    
    
    
    /**
     * �������� ������������ �������� ������� ��� ������ ������ �� CRON
     */
    public static function checkCron()
    {
    	$a = COption::GetOptionString("main", "agents_use_crontab", "Y") == 'N';
    	if (!$a) {
    		return false;
        }
        
    	$b = COption::GetOptionString("main", "check_agents", "Y") == 'N';
    	if (!$b) {
    		return false;
        }
        
    	if (defined('BX_CRONTAB') && BX_CRONTAB == true) {
    		return false;
        }
        
		if (!defined("CHK_EVENT") || CHK_EVENT !== true) {
    		if (!defined("BX_CRONTAB_SUPPORT") || BX_CRONTAB_SUPPORT !== true) {
	    		return false;
    		}
		}
	    return true;
    }
}
