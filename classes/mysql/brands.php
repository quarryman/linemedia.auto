<?php

/**
 * Linemedia Autoportal
 * Main module
 * Brands class
 *
 * @author  Linemedia
 * @since   25/03/2013
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/*
 * Search through database
 */
class LinemediaAutoBrands
{

    const TABLE = 'b_lm_products';
    /**
     * Поиск всех уникальных брендов в алфав. порядке по локальной базе, по активный поставщикам
     */
    public function getList()
    {
        $select = 'brand_title';

        try {
            $database = new LinemediaAutoDatabase();
        } catch (Exception $e) {
            throw $e;
        }

        /*
         * Составляем запрос
         */
        $where = array(
            '`quantity` > 0',
            '`supplier_id` > 0',
        );

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
         * Запрос.
         */
        $sql = 'SELECT DISTINCT('.$select.') AS ' . $select . ' FROM `' . self::TABLE .'` WHERE ' . join(' AND ', $where) . ' ORDER BY '.$select.' ASC';

        try {
            $res = $database->Query($sql);
        } catch (Exception $e) {
            throw $e;
        }


        $brands = array();
        while ($brand = $res->Fetch()) {

             //Источник поступления ин-ции о запчасти - локальная БД

            $brand['source'] = 'local-database';
            $brands []= $brand;
        }
        return $brands;
    }
}
