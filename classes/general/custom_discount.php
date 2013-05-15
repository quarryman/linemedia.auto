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


/**
 * Класс, отвечающий за работу со скидками.
 */
class LinemediaAutoCustomDiscount
{
    protected $part     = null;
    
    protected $groups   = array();
    protected $user_id   = array();
    protected $date;
    
    protected $discounts   = array();
    protected $discount_types   = array();
    protected $supplier_ids   = array();
    
    /*
    * ЧПН отладка расчёта цен
    */
    protected $debug = array();
    
    
    public function __construct(LinemediaAutoPartAll $part, $user_id = null)
    {
        $this->part = $part;
        $this->user_id = (intval($user_id) > 0) ? (intval($user_id)) : (CUser::GetID());
        $this->date = time();
        
        $this->loadDiscounts();
    }
    
    
    private function loadDiscounts()
    {
		$obCache = new CPHPCache(); 
		$life_time = 30 * 60; 
		$cache_id = 'iblock/custom_discounts'; 
		if ($obCache->InitCache($life_time, $cache_id, "/lm_auto/custom_discount")) {
		    $cache = $obCache->GetVars();
		    $discounts = $cache['discounts'];
		    $discount_types = $cache['discount_types'];
		    $supplier_ids = $cache['supplier_ids'];
		} else {
		
	        /*
	         * Выборка скидок из инфоблока согласно составленному фильтру
	         */
	        CModule::IncludeModule('iblock');
	        $IBLOCK_ID = COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_DISCOUNT");
	        $discounts = array();
	        $res = CIBlockElement::GetList(array("SORT"=>"ASC"), array('IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y'), false, false, array('ID', 'NAME'));
	        while ($discount = $res->Fetch()) {
	        	$props_res = CIBlockElement::GetProperty($IBLOCK_ID, $discount['ID']);
	        	while ($prop = $props_res->Fetch()) {
	        		if ($prop['MULTIPLE'] == 'Y') {
		        		$discount['PROPS'][$prop['CODE']][] = $prop['VALUE'];
	        		} else {
		        		$discount['PROPS'][$prop['CODE']] = $prop['VALUE'];
	        		}
	        	}
	            $discounts []= $discount;
	        }
	        
	        /*
	         * Выборка типов скидок
	         */
	        $discount_types = array();
	        $property_enums = CIBlockPropertyEnum::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID, "CODE" => "discount_type"));
			while ($enum_fields = $property_enums->Fetch()) {
				$discount_types[$enum_fields["ID"]] = $enum_fields["XML_ID"];
			}
	        
	        
	        /*
	         * Выборка ID поставщиков
	         */
	        $IBLOCK_ID = COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_SUPPLIERS");
	        $supplier_ids = array();
	        $res = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $IBLOCK_ID), false, false, array('ID', 'CODE', 'PROPERTY_supplier_id'));
	        while ($supplier = $res->Fetch()) {
	            $supplier_ids[$supplier['PROPERTY_SUPPLIER_ID_VALUE']] = $supplier['ID'];
	        }
	        
	        if ($obCache->StartDataCache()) {
		        $obCache->EndDataCache(array(
		        	'discounts' => $discounts,
		        	'discount_types' => $discount_types,
		        	'supplier_ids' => $supplier_ids,
		        ));
	        } 
		}
		
		$this->discounts = $discounts;
		$this->discount_types = $discount_types;
		$this->supplier_ids = $supplier_ids;
    }
    
    
    public function getPart()
    {
        return $this->part;
    }
    
    
    public function getGroups()
    {
        if (empty($this->groups)) {
            global $USER;
            
            
            if($USER->GetID() != $this->user_id)
            {
            	$this->groups = CUser::GetUserGroup($this->user_id);
            } else {
	            $this->groups = $USER->GetUserGroupArray();
            }
            
            // Учет группы неавторизованных пользователей.
            if (!$USER->IsAuthorized()) {
                $this->groups []= LinemediaAutoIblockPropertyUserGroup::GROUP_GUEST;
            }
        }
        return $this->groups;
    }
    
    
    public function setGroups($groups)
    {
        $this->groups = (array) $groups;
    }
    
    
    public function setUserId($user_id)
    {
        $this->user_id = (int) $user_id;
    }
    
    public function setDate($date)
    {
        $this->date = (int) $date;
    }
    
    
    /**
     * Рассчет цены.
     * 
     * @param float $price
     */
    public function calculate($price)
    {
        /*
         * Вывод отладочной информации
         */
        LinemediaAutoDebug::add('Linemedia Price custom discount [was ' . $price . ']');
        
        /*
         * Соберём информацию для фильтра, который ищет подходящие скидки
         */
        
        /*
         * Группы
         */
        $user_id        = $this->user_id;
        $groups         = $this->getGroups();
        
        $article        = $this->part->get('article');
        $brand_title    = strtolower($this->part->get('brand_title'));
        $supplier_id    = $this->part->get('supplier_id');
        $base_price     = $this->part->get('price');
        
        
        /*
         * $supplier_id  должен быть битриксовый, потому что в скидках битрикс выбирает свой ID
         * TODO: закешировать или переделать этот алгоритм, а то он очень часто вызывается и это неоптимально!
         * Лучше сделать свойство IB, которое будет вставлять правильный ID
         */
        if ($supplier_id != '') {
            $supplier_id = $this->supplier_ids[$supplier_id];
        }
        
        /*
         * Выборка скидок из инфоблока согласно составленному фильтру
         */
        $applied_discounts = array();
        
        foreach ($this->discounts as $discount) {
        	/*
        	 * Артикул
        	 */
        	$filter_articles = (array) $discount['PROPS']['article'];
        	$filter_articles = array_map('mb_strtolower', $filter_articles);
        	$filter_articles = array_map('trim', $filter_articles);
        	$filter_articles = array_filter($filter_articles);
	        if (count($filter_articles) > 0) {
	        	$article = mb_strtolower($article);
		        if (!in_array($article, $filter_articles)) {
		        	continue;
                }
	        }
	        
	        /*
        	 * Наименование производителя
        	 */
        	$filter_brand_titles = (array) $discount['PROPS']['brand_title'];
        	$filter_brand_titles = array_map('mb_strtolower', $filter_brand_titles);
        	$filter_brand_titles = array_map('trim', $filter_brand_titles);
        	$filter_brand_titles = array_filter($filter_brand_titles);
	        if (count($filter_brand_titles) > 0) {
	        	$brand_title = mb_strtolower($brand_title);
		        if (!in_array($brand_title, $filter_brand_titles)) {
		        	continue;
                }
	        }
	        
	        /*
        	 * Группа пользователей
        	 */
        	$filter_user_groups = (array) $discount['PROPS']['user_group'];
        	$filter_user_groups = array_filter($filter_user_groups);
	        if (count($filter_user_groups) > 0) {
		        if (count(array_intersect($groups, $filter_user_groups)) == 0) {
		        	continue;
                }
	        }
	        
	        /*
        	 * Пользователь
        	 */
        	$filter_user_ids = (array) $discount['PROPS']['user_id'];
        	$filter_user_ids = array_filter($filter_user_ids);
	        if (count($filter_user_ids) > 0) {
		        if (!in_array($user_id, $filter_user_ids)) {
		        	continue;
                }
	        }
	        
	        /*
        	 * Поставщик
        	 */
        	$filter_supplier_ids = (array) $discount['PROPS']['supplier_id'];
        	$filter_supplier_ids = array_map('mb_strtolower', $filter_supplier_ids);
        	$filter_supplier_ids = array_filter($filter_supplier_ids);
	        if (count($filter_supplier_ids) > 0) {
	        	$supplier_id = mb_strtolower($supplier_id);
		        if (!in_array($supplier_id, $filter_supplier_ids)) {
		        	continue;
                }
	        }
	        
	        /*
        	 * Минимальная базовая цена
        	 */
        	$filter_min_price = (float) $discount['PROPS']['price_min'];
	        if ($filter_min_price > 0) {
		        if ($base_price < $filter_min_price) {
		        	continue;
                }
	        }
	        	        
	        /*
        	 * Максимальная базовая цена
        	 */
        	$filter_max_price = (float) $discount['PROPS']['price_max'];
	        if ($filter_max_price > 0) {
		        if ($base_price > $filter_max_price) {
		        	continue;
                }
	        }	       
            
            
            /*
             * Событие для других модулей.
             * Если модуль возвращает false - пропускаем скидку.
             */
	        $events = GetModuleEvents("linemedia.auto", "OnSaleDiscountsCheck");
            while ($arEvent = $events->Fetch()) {
                $result = ExecuteModuleEventEx(
                    $arEvent, 
                    array(
                        &$discount,
                        &$user_id,
                        &$groups,
                        &$article,
                        &$brand_title,
                        &$supplier_id,
                        &$base_price,
                        &$this->debug
                    )
                );
                if (!$result) {
                    continue 2;
                }
            }
	        
	        
	        /*
	         *  Если мы до сюда дошли, значит скидка подходит.
	         */
	        $applied_discounts []= $discount;
        }
        
        /*
         * Нет подходящих скидок
         */
        if (count($applied_discounts) == 0) {
            LinemediaAutoDebug::add('No custom discounts found');
            return $price;
        }
        
        /*
         * Скидка - в процентах от наценки поставщика
         */
        $supplier = new LinemediaAutoSupplier($this->part->get('supplier_id'));
        $markup = (float) $supplier->get('markup');
        
        /*
         * Пройдём по каждой скидке
         */
        foreach ($applied_discounts as $discount) {
            
            $discount_type_id = (int) $discount['PROPS']['discount_type'];
            $discount_type = $this->discount_types[$discount_type_id];
            
            $discount_percent = (float) $discount['PROPS']['discount'];
            
            switch ($discount_type) {
                /*
                 * Скидка от наценки поставщика
                 */
                case 'SUPPLIER_MARKUP_DISCOUNT':
                    $supplier_markup_value = $base_price / 100 * $markup;
                    $diff = ($supplier_markup_value / 100) * $discount_percent;
                    $new_price = $price - $diff;
                    
                    // ЧПН отладка пересчёта
                    $this->debug[] = GetMessage('LM_AUTO_MAIN_CUST_DISCOUNT_DEBUG_SUPPLIER_MARKUP_DISCOUNT', array('#DISCOUNT#' => $discount_percent, '#MARKUP#' => $supplier_markup_value. ' ('.$markup.'%)', '#DIFF#' => $diff, '#RESULT#' => $new_price, '#DISCOUNT_NAME#' => $discount['NAME'], '#DISCOUNT_ID#' => $discount['ID'], '#MARKUP_VALUE#' => $supplier_markup_value));
                    $price = $new_price;
                    
                    break;
                
                /*
                 * Скидка от конечной цены
                 */
                case 'FINAL_PRICE_DISCOUNT':
                    $new_price = $price - ($price / 100) * $discount_percent;
                    
                    // ЧПН отладка пересчёта
                    $this->debug[] = GetMessage('LM_AUTO_MAIN_CUST_DISCOUNT_DEBUG_FINAL_PRICE_DISCOUNT', array('#DISCOUNT#' => $discount_percent, '#RESULT#' => $new_price, '#DISCOUNT_ID#' => $discount['ID'], '#DISCOUNT_NAME#' => $discount['NAME'], '#MINUS#' => ($price / 100) * $discount_percent));
                    $price = $new_price;
                    
                    break;
                
                /*
                 * Наценка от базовой цены
                 */
                case 'BASE_PRICE_MARKUP':
                    $new_price = $price + ($base_price / 100) * $discount_percent;
                    
                    // ЧПН отладка пересчёта
                    $this->debug[] = GetMessage('LM_AUTO_MAIN_CUST_DISCOUNT_DEBUG_BASE_PRICE_MARKUP', array('#DISCOUNT#' => $discount_percent, '#RESULT#' => $new_price, '#DISCOUNT_ID#' => $discount['ID'], '#DISCOUNT_NAME#' => $discount['NAME']));
                    $price = $new_price;
                    
                    break;
            }
            
            
            /*
             * Событие для других модулей.
             * Расчет скидки.
             */
            $events = GetModuleEvents("linemedia.auto", "OnSaleDiscountCalculate");
            while ($arEvent = $events->Fetch()) {
                $result = ExecuteModuleEventEx(
                    $arEvent, 
                    array(
                        &$this->part,
                        &$discount,
                        &$price,
                        &$base_price,
                        &$user_id,
                        &$groups,
                        &$article,
                        &$brand_title,
                        &$supplier_id,
                        &$this->debug
                    )
                );
            }
            
            /*
            * Не применять дальше
            */
            if($discount['PROPS']['last'] == 'Y')
            {
            	// ЧПН отладка пересчёта
                $this->debug[] = GetMessage('LM_AUTO_MAIN_CUST_DISCOUNT_DEBUG_LAST');
            	break;
            }
            
            
            LinemediaAutoDebug::add($discount_type . ' new price = ' . $price);
        }
        
        return $price;
    }
    
    
    public function getDebug()
    {
	    return $this->debug;
    }
}
