<?php

/**
 * Linemedia Autoportal
 * Main module
 * Price calculation class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);
 
 CModule::IncludeModule('currency');
 
/*
 * Класс валют
 */
class LinemediaAutoCurrency
{
    protected $id = null;
    
    
    public function __construct($id)
    {
        $this->id = (int) $id;
    }
    
    
    /**
     * Пересчет цен.
     */
    public static function calculate($currency, $price)
    {
        if (empty($currency)) {
            return $price;
        }
        
        // Загрузим валюты.
        $obCache = new CPHPCache();
        $life_time = 24 * 60 * 60; 
        $cache_id = 'price-currencies'; 
        if ($obCache->InitCache($life_time, $cache_id, "/")) {
            $data = $obCache->GetVars();
            $currencies = $data['currencies'];
            $base_currency = $data['base'];
        } else {
            if (!CModule::IncludeModule('currency')) {
                LinemediaAutoDebug::add('Error price calculation, no currencies module!', false, LM_AUTO_DEBUG_ERROR);
            }
            
            $lcur = CCurrency::GetList(($b="name"), ($order1="asc"), LANGUAGE_ID);
            while ($lcur_res = $lcur->Fetch()) {
                $currencies[$lcur_res['CURRENCY']] = $lcur_res;
            }
            
            $base_currency = CCurrency::GetBaseCurrency();
            
            if ($obCache->StartDataCache()) {
                $obCache->EndDataCache(array('currencies' => $currencies, 'base' => $base_currency));
            }
        }
        
        // Сравним валюты.
        if ($currency !== $base_currency) {
            $price *= $currencies[$currency]['AMOUNT'];
        }
        return $price;
    }
    
}