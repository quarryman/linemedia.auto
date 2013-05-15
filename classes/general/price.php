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
 * ����� ������� ���� ��������
 */
class LinemediaAutoPrice
{

    protected $part_id;
    protected $part;
    protected $user_id;
    protected $date;
    
    protected $currency = 'RUB';
    
    
    
    /*
    * ��������� ��������� ������� ������� ����
    */
    protected $debug_calculations = false;
    protected $debug_calculations_results = false;
    
    /**
     * �������� � ������������ ������ ������������.
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
        
        // ��� ������� ����� ��������� � ������ part ������ �� ��
        if (!$this->part->exists()) {
            LinemediaAutoDebug::add('Error price calculation, part id=' . $part_id . ' not found', false, LM_AUTO_DEBUG_WARNING);
        }
        
        // ��� ����?
        if ($this->part->get('price') <= 0) {
            LinemediaAutoDebug::add('Error price calculation, part id=' . $part_id . ' price is zero!', false, LM_AUTO_DEBUG_WARNING);
        }
    }
    
    
    /**
     * ��������� ID ������������.
     */
    public function setUserID($user_id)
    {
        $this->user_id = (int) $user_id;
    }
    
    
    /**
     * ��������� ����
     */
    public function setDate($date)
    {
        $this->date = strtotime(strval($date));
    }
    
    
    /**
     * ��������������� ������� ����.
     */
    public function calculate($price_format = '%i')
    {
    
        $part_id = $this->part_id;
        $price = (float) $this->part->get('price');
        
        
        
        /*
        * ������� ����������� ����
        */
        if($this->debug_calculations)	
        	$this->debug_calculations_results[] = GetMessage('LM_AUTO_MAIN_PRICE_PRICELIST') . ' <b>' . $price . '</b>';
        
        
        
        /*
         * ����� ���������� ����������
         */
        LinemediaAutoDebug::add('Price calculation [<b>' . $this->part->get('article') . '</b>] (' . $price . ')');
        
        
        /*
         * ������ �������
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
         * ����� ���������� ���������� 
         */
        LinemediaAutoDebug::add('Price calc (part ID ' . $part_id . ') after events ' . $price);
		
		/*
        * ������� ����������� ����
        */
        if($this->debug_calculations)	
        	$this->debug_calculations_results[] = GetMessage('LM_AUTO_MAIN_PRICE_FINAL') . ' <b>' . number_format($price, 2, '.', ' ') . ' ' . $this->getCurrency() . '</b>';
		
        
        return $price;
    }
    
    
    /**
     * ��������� ������.
     */
    public function getCurrency()
    {
        return $this->currency;
    }
    
    
    
    
    /*
    * ��������� ��������� ������� ������� ����
    */
    public function enableDebugCollection()
    {
	    $this->debug_calculations = true;
    }
    
    /*
    * ��������� ����������� ��������� ������� ������� ����
    */
    public function getDebug()
    {
	    return $this->debug_calculations_results;
    }
}
