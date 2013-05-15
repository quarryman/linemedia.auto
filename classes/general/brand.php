<?php

/**
 * Linemedia Autoportal
 * Main module
 * Basket management class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
 * Класс-обертка для работы с брендами.
 */
class LinemediaAutoBrand
{
    
    /**
     * Получение ID бренда TecDoc.
     */
    public static function getTecdocBrandID($brand_title)
    {
        $brand_title = (string) $brand_title;
        
        $items = self::getTecdocBrands();
        
        if (!empty($items[$brand_title])) {
            return $items[$brand_title]['brandNo'];
        }
        return null;
    }
    
    
    /**
     * Получение полного списка брендов из TecDoc.
     */
    public static function getTecdocBrands()
    {
        $api = new LinemediaAutoApiDriver();
        
        $items = array();
        
        $obCache = new CPHPCache();
        $cache_time = 60 * 24 * 60 * 60;
        $cache_id = 'all-tecdoc-brands';
        
        if ($obCache->InitCache($cache_time, $cache_id, '/')) {
            $items = $obCache->GetVars();
        } else {
            try {
                $response = $api->query('getDetailBrands', array());
                if (!empty($response['data'])) {
                    foreach ($response['data'] as $item) {
                        $items[$item['brandName']] = $item;
                    }
                }
            } catch (Exception $e) {
                throw $e;
            }
            
            if ($obCache->StartDataCache()) {
                $obCache->EndDataCache($items);
            }
        }
        return $items;
    }
}
