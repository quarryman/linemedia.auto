<?php

/**
 * Linemedia Autoportal
 * Main module
 * Parts search class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__); 
 
/*
 * Search through database
 */
class LinemediaAutoSearchSimple implements LinemediaAutoISearch
{
    /**
     * Поиск запчасти по локальной базе данных
     */
    public function searchLocalDatabaseForPart($part, $multiple = false)
    {
        try {
            $database = new LinemediaAutoDatabase();
        } catch (Exception $e) {
            throw $e;
        }
        
        /*
         * Основные критерии поиска
         */
        $article         = LinemediaAutoPartsHelper::clearArticle($part['article']);
        $id              = (int) $part['id'];
        $brand_title     = (string) $part['brand_title'];
        $supplier_id     = (string) $part['supplier_id'];
        
        
        $brand_title = trim($brand_title);
        $supplier_id = trim($supplier_id);
        
        /*
         * Внешние данные.
         */
        $extra = (array) $part['extra'];
        
        /*
         * Дополнительные критерии поиска, требующие дополнительного поиска по бренду
         */
        $additional_fields = (array) $part['additional_fields'];
        
        /*
         * Составляем запрос
         */
        $where = array(
            '`quantity` > 0',
            '`supplier_id` > 0',
        );
        
        if ($id > 0) {
            $where []= '`id` = ' . $database->ForSql($id);
        }
        
        if ($brand_title != '') {
        	/*
        	 * Добавим словоформы
        	 */
        	$wordforms = new LinemediaAutoWordForm();
        	$brand_titles = $wordforms->getBrandWordforms($brand_title);
        	if (count($brand_titles) > 0) {
        	    $brand_titles = array_map('strval', $brand_titles);
        	    $brand_titles = array_map('strtoupper', $brand_titles);
        		$brand_titles = array_map(array($database, 'ForSql'), $brand_titles);
        		$brand_titles = "'" . join("', '", $brand_titles) . "'";
            	$where[] = "UPPER(`brand_title`) IN ($brand_titles)";
            } else {
                $brand_title = strtoupper((string) $brand_title);
	            $where[] = "UPPER(`brand_title`) = '" . $database->ForSql($brand_title) . "'";
            }
        }
        
        if ($supplier_id != '') {
            $where[] = '`supplier_id` = ' . $database->ForSql($supplier_id);
        }
        
        if ($article != '') {
            /*
             * Удаление / Добавление ведущего нуля
             */
            if(substr($article, 0, 1) == '0')
            {
	            $where[] = "(`article` = '" . $database->ForSql($article) . "' OR `article` = '" . $database->ForSql(substr($article, 1)) . "')";
            } else {
	            $where[] = "(`article` = '" . $database->ForSql($article) . "' OR `article` = '0" . $database->ForSql($article) . "')";
            }
        }
        
        /*
         * Убираем неактивных поставщиков.
         */
        $obCache = new CPHPCache();
        $life_time = 10 * 60;
        $cache_id = 'active_suppliers';
        if ($obCache->InitCache($life_time, $cache_id, '/')) {
            $arSupplierIDs = $obCache->GetVars();
        } else {
            $arSupplierIDs  = array();
            $arSuppliers    = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y'), false, false, array('ID', 'PROPERTY_supplier_id'));
            foreach ($arSuppliers as $arSupplier) {
                $arSupplierIDs []= "'".strval($arSupplier['PROPERTY_SUPPLIER_ID_VALUE'])."'";
            }
            if ($obCache->StartDataCache()) {
                $obCache->EndDataCache($arSupplierIDs);
            }
        }
        
        if (!empty($arSupplierIDs)) {
            $where []= '`supplier_id` IN (' . implode(', ', $arSupplierIDs) . ')';
        }
        
        
        /*
         * Дополнительные критерии поиска.
         */
        if (count($additional_fields) > 0) {
            foreach ($additional_fields as $col => $val) {
                $operator = '=';
                if (in_array($col[0], array('=', '>', '<'))) {
                    $operator = $col[0];
                }
                $col = '`' . $database->ForSql($col) . '`';
                $val = "'" . $database->ForSql($val) . "'";
                $where []= "$col $operator $val";
            }
        }
        
        
        /*
         * Должен быть задан хоть один фильтр кроме количества и активных поставщиков.
         */
        if (count($where) <= 3) {
        	return false;
        }
        
        
        /*
         * Запрос.
         */
        $sql = 'SELECT * FROM `b_lm_products` WHERE ' . join(' AND ', $where);
        
        try {
            $res = $database->Query($sql);
        } catch (Exception $e) {
            throw $e;
        }
        
        /*
         * Мы ищем одну запчасть или много?
         */
        if ($multiple) {
            $parts = array();
            while ($part = $res->Fetch()) {
                /*
                 * Источник поступления ин-ции о запчасти - локальная БД
                 */
                $part['source'] = 'local-database';
                $parts []= $part;
            }
            return $parts;
        } else {
            if ($part = $res->Fetch()) {
                /*
                 * Источник поступления ин-ции о запчасти - локальная БД
                 */
                $part['source'] = 'local-database';
                return $part;
            } else {
                return false;
            }
        }
    }
}
