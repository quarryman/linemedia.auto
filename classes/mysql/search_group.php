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
class LinemediaAutoSearchGroup implements LinemediaAutoISearch
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
        
        $partarts       = explode(',', $part['article']);
        $articles       = array();
        foreach ($partarts as $article) {
            $articles []= LinemediaAutoPartsHelper::clearArticle($article);
        }
        $id              = (int) $part['id'];
        $brand_title     = (string) $part['brand_title'];
        $supplier_id     = (string) $part['supplier_id'];
        
        /*
         * Дополнительные критерии поиска, требующие дополнительного поиска по бренду
         */
        $extra = (array) $part['extra'];
        
        /*
         * составляем запрос
         */
        $where = array(
            '`quantity` > 0',
        );
        
        if ($id > 0) {
            $where[] = '`id` = ' . $database->ForSql($id);
        }
        
        if ($brand_title) {
            $brand_title = strtoupper((string) $brand_title);
            $where[] = "UPPER(`brand_title`) = '" . $database->ForSql($brand_title) . "'";
        }
        
        
        if ($supplier_id) {
            $where[] = '`supplier_id` = ' . $database->ForSql($supplier_id);
        }
        
        if ($articles) {
            $articles = array_map(array($database, 'ForSql'), $articles);
            $article  = implode("', '", $articles);
            $where[]  = "`article` IN ('" . $article . "')";
        }
        
        /*
         * Убираем неактивных поставщиков.
         */
        $arSupplierIDs  = array();
        $arSuppliers    = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y'), false, false, array('ID', 'PROPERTY_supplier_id'));
        foreach ($arSuppliers as $arSupplier) {
            $arSupplierIDs []= "'".strval($arSupplier['PROPERTY_SUPPLIER_ID_VALUE'])."'";
        }
        if (!empty($arSupplierIDs)) {
            $where[] = '`supplier_id` IN (' . implode(', ', $arSupplierIDs) . ')';
        }
        
        
        /*
         * Дополнительные критерии поиска
         */
        if (count($extra) > 0) {
            foreach ($extra as $col => $val) {
                $operator = '=';
                if (in_array($col[0], array('=', '>', '<'))) {
                    $operator = $col[0];
                }
                $col = '`' . $database->ForSql($col) . '`';
                $val = "'" . $database->ForSql($val) . "'";
                $where[] = "$col $operator $val";
            }
        }
        
        try {
            $res = $database->Query('SELECT * FROM `b_lm_products` WHERE ' . join(' AND ', $where));
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
                $part['data-source'] = 'local-database';
                $parts []= $part;
            }
            return $parts;
        } else {
            if ($part = $res->Fetch()) {
                /*
                 * Источник поступления ин-ции о запчасти - локальная БД
                 */
                $part['data-source'] = 'local-database';
                return $part;
            } else {
                return false;
            }
        }
    }
}
