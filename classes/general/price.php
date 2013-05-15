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
 
 CModule::IncludeModule("sale");
 
/*
 * Класс расчёта цены запчасти
 */
class LinemediaAutoPrice
{

    protected $part_id;
    protected $part;
    protected $user_id;
    protected $date;
    
    protected $currency = 'RUB';
    
    
    
    /*
    * Включение подробной отладки расчёта цены
    */
    protected $debug_calculations = false;
    protected $debug_calculations_results = false;
    
    /**
     * Проверим в конструкторе объект пользователя.
     */
    public function __construct(LinemediaAutoPartAll $part)
    {
    	
    	if(!CModule::IncludeModule('currency'))
    		throw new Exception('No currency module');
    	
    	$this->currency = CCurrency::GetBaseCurrency();
    	
    	
        global $USER;
        $USER = ($USER) ? $USER : new CUser();
        $this->USER = $USER;
        
        $this->user_id = $USER->getID();
        $this->date = time();
        $this->part = $part;
        
        // эта функция также загружает в объект part данные из БД
        if (!$this->part->exists()) {
            LinemediaAutoDebug::add('Error price calculation, part id=' . $part_id . ' not found', false, LM_AUTO_DEBUG_WARNING);
        }
        
        // нет цены?
        if ($this->part->get('price') <= 0) {
            LinemediaAutoDebug::add('Error price calculation, part id=' . $part_id . ' price is zero!', false, LM_AUTO_DEBUG_WARNING);
        }
    }
    
    
    /**
     * Установка ID пользователя.
     */
    public function setUserID($user_id)
    {
        $this->user_id = (int) $user_id;
    }
    
    
    /**
     * Установка даты
     */
    public function setDate($date)
    {
        $this->date = strtotime(strval($date));
    }
    
    
    /**
     * Непосредственно просчёт цены.
     */
    public function calculate($price_format = '%i')
    {
    
        $part_id = $this->part_id;
        $price = (float) $this->part->get('price');
        
        
        
        /*
        * Отладка калькуляции цены
        */
        if($this->debug_calculations)	
        	$this->debug_calculations_results[] = GetMessage('LM_AUTO_MAIN_PRICE_PRICELIST') . ' <b>' . $price . '</b>';
        
        
        
        /*
         * Вывод отладочной информации
         */
        LinemediaAutoDebug::add('Price calculation [<b>' . $this->part->get('article') . '</b>] (' . $price . ')');
        
        
        /*
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnItemPriceCalculate");
        
		while ($arEvent = $events->Fetch()) {
			ExecuteModuleEventEx($arEvent, array(
			    &$this->part,
			    &$price,
			    &$this->currency,
			    &$this->user_id,
			    &$this->date,
			    &$this->debug_calculations_results
			));
		}
		
		/*
         * Вывод отладочной информации 
         */
        LinemediaAutoDebug::add('Price calc (part ID ' . $part_id . ') after events ' . $price);
		
		/*
        * Отладка калькуляции цены
        */
        if($this->debug_calculations)	
        	$this->debug_calculations_results[] = GetMessage('LM_AUTO_MAIN_PRICE_FINAL') . ' <b>' . number_format($price, 2, '.', ' ') . ' ' . $this->getCurrency() . '</b>';
		
        
        return $price;
    }
    
    
    /**
     * Получение валюты.
     */
    public function getCurrency()
    {
        return $this->currency;
    }
    
    
    
    
    /*
    * Включение подробной отладки расчёта цены
    */
    public function enableDebugCollection()
    {
	    $this->debug_calculations = true;
    }
    
    /*
    * Получение результатов подробной отладки расчёта цены
    */
    public function getDebug()
    {
	    return $this->debug_calculations_results;
    }
}
