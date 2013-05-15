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
     * Объект базы данных
     */
    protected $database;
    
    
    /**
     * Основная функция дебага
     */
    public static function run() 
    {
    	/*
    	* Включим запись в лог
    	*/
    	LinemediaAutoDebug::setOutputFilename($_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/logs/import.log');
    
    	/*
		 * А есть ли крон?
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
			 * Запрещено выполнять импорт не из под крона
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
             * Парсим файлы
             */
            foreach ($files as $file) {
                $agent->parseFile($file);
            }
        }
        
        
        /*
         * Очистим старые прайсы
         */
        $agent->cleanupOldFiles();
        
        
        /*
         * Cобытие окончания отработки агента
         * Были ли импортированы файлы и сколько
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
     * Проверка наличия новых файлов
     */
    public static function getNewFiles()
    {
    	$path = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/new/';
    	$files = array();
    	foreach (glob($path . '*.csv') as $filename) {
    		$files[] = basename($filename);
    	}
    	    	
        /*
         * Нет прайсов
         */
        if (count($files) < 1) {
            return;
        }
        
        /*
         * Проверяем файл за файлом
         */
        foreach ($files as $i => $file) {
            $filename = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/new/' . $file;
            
            /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_FOUND', array('#FILE#' => $file)));
            
            /*
             * Читаем ли файл
             */
            if (!is_readable($filename)) {
                LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_NOT_READABLE', array('#FILE#' => $file)));
                
                self::moveIncorrectFile($file);
                unset($files[$i]);
            }
            
            /*
             * Загружен ли файл до конца?
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
     * Соберём информацию полезную для импорта
     */
    public function prepareImportData()
    {
        /*
         * Подключаем модуль
         */
        CModule::IncludeModule('linemedia.auto');
        
        /*
         * Объект доступа в API
         */
        $api = new LinemediaAutoApiDriver();
        
        
        /*
         * Подключение к БД
         */
        $this->database = new LinemediaAutoDatabase();
        
        
        /*
         * Кодировка для импорта всегда UTF, потому что кодировка таблицы продуктов прописана насильно
         */
        $this->database->Query("SET NAMES 'utf8'");
    }
        
        
    /**
     * Разбор файла.
     */
    public function parseFile($file)
    {
        $filename = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.auto/pricelists/new/' . $file;
        
        /*
         * Вывод отладочной информации
         */
        LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_START', array('#FILE#' => $file)));
        
        
        $filename_parts = explode('_', $file);
        $supplier_id = (int) $filename_parts[0];
        
        /*
         * Получим объект поставщика
         */
        $supplier = new LinemediaAutoSupplier($supplier_id);
        if (!$supplier->exists()) {
            LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_SUPPLIER_NOT_FOUND', array('#SUPPLIER#' => $supplier_id)), false, LM_AUTO_DEBUG_ERROR);
            $this->moveIncorrectFile($file);
            return;
        }
        
        
        /*
         * Cобытие начала загрузки прайса
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
         * Обнулим товары поставщика
         */ 
        $this->zeroSupplierProducts($supplier_id);
        
        /*
         * Открываем файл
         */
        try {
            $handle = fopen($filename, "r");
            
            // Попытаемся заблокировать файл.
            if (!flock($handle, LOCK_EX)) {
                fclose($handle);
                return;
            }
        } catch (Exception $e) {
            LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_OPEN_ERROR', array('#ERROR#' => $e->GetMessage())));
            return;
        }
        
        /*
         * Проверим кодировку файла.
         * Она должна быть UTF-8.
         */
        $cmd = 'file -bi ' . escapeshellarg($filename);
        $cmd_result = system($cmd, $cmd_result);
        $response = explode(';', $cmd_result);
        $charset  = explode('=', $response[1]);
        $encoding = trim($charset[1]);
        if ($encoding != 'utf-8') {
            $from = 'cp1251'; // потому что file -bi приравнивает cp1251 к iso-8859-1. но у нас-то или 1251 или юникод
	        $cmd = 'iconv -f ' . $from . ' -t utf8 "' . $filename . '" -o "' . $filename . '.tmp"';
	        system($cmd, $cmd_result);
	        unlink($filename);
	        rename($filename . '.tmp', $filename);
        }
        
        /*
         * Установка локали.
         */
        @setlocale(LC_ALL, "ru_RU");
        
        
        /*
         * Получаем пользовательские поля.
         */
        $lmfields = new LinemediaAutoCustomFields();
        
        $custom_fields = $lmfields->getFields();
        
        
        /*
         * Построчно импортируем данные
         */
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            /*
             * Определение полей в CSV
             *
             * Старые прайсы:
             * AN113K;AKEBONO;"Название детали AN-113K";1052;10;-1
             *
             * Новые прайсы
             * AN113K;AKEBONO;"Название детали AN-113K";1052;10;-1;300;custom1;custom2;custom3
             */
            $brand_title    = trim($data[0]);
            $article        = trim($data[1]);
            $title          = trim($data[2]);
            $price          = trim($data[3]);
            $quantity       = trim($data[4]);
            $group_id       = trim($data[5]); // некогда group_id
            $weight         = trim($data[6]);
            
            /*
             * Количество фиксированных полей.
             */
            $index = 7;
            
            /*
             * Не импортируем товар с количеством 0
             */
            if (floatval($quantity) == 0) {
            	continue;
            }
            $original_article = $article;
            $article = LinemediaAutoPartsHelper::clearArticle($article);
            
            /*
             * Вставим значение в БД
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
             * Вставим кастомные значения в БД.
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
         * Переместим файл в успешно добавленные
         */
        $this->moveCorrectFile($file);
        
        /*
         * событие окончания загрузки прайса
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
     * Переместить ошибочный файл в соответствующую папку
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
     * Переместить успшно добавленный файл в бекап
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
     * Обнулить количество товаров у поставщика
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
         * Вывод отладочной информации
         */
        LinemediaAutoDebug::add(GetMessage('LM_PRICELIST_SUPPLIER_PRODUCTS_REMOVED', array('#SUPPLIER#' => $supplier_id)));
    }
    
    
    
    /**
     * Удалить старые прайсы.
     */
    public function cleanupOldFiles()
    {
	    $lifetime_days = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS', 14);
	    $lifetime = $lifetime_days * 86400;
	    
	    /*
	    * Удалим успешно загруженные прайслисты
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
	     * Не импортированные прайсы тоже удалим, потому что они
	     */
	    $root = $_SERVER['DOCUMENT_ROOT'] . '/upload/linemedia.auto/pricelists/error/'; 
   		try {
       		$this->cleanupDir($root, $lifetime, false);
   		} catch (Exception $e) {
       		
   		}
    }
    
    
    /**
     * Чистка одной директории
     */
    private function cleanupDir($path, $lifetime, $rm_emty_dirs = true)
    {
    	/*
    	 * Удалить все CSV
    	 */
    	foreach (glob($path . '*.csv') as $filename) {
    		if (time() - filemtime($filename) > $lifetime) {
	    		unlink($filename);
    		}
    	}
    	
    	
    	/*
    	 * Может можно и папку удалить?
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
     * Проверка правильности настроек системы для работы агента из CRON
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
