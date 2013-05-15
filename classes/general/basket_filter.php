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
 * Класс для фильтрации корзин.
 */
class LinemediaAutoBasketFilter
{
    
    protected $ids          = array();
    protected $is_empty     = false;
    protected $is_filtered  = false;
    
    /*
     * Фильтры.
     */
    protected $filter_basket_props  = array();
    protected $filter_baskets       = array();
    protected $filter_orders        = array();
    protected $filter_users         = array();
    
    
    
    public function __construct()
    {
        CModule::IncludeModule('sale');
    }
    
    
    public function isFiltered()
    {
        return $this->is_filtered;
    }
    
    
    public function presetIds($ids)
    {
        $this->ids = (array) $ids;
    }
    
    
    public function setIDs($ids)
    {
        $this->filter_baskets['ID'] = (array) $ids;
    }
    
    
    public function setNotIDs($ids)
    {
        $this->filter_baskets['!ID'] = (array) $ids;
    }
    
    
    public function setIdFrom($id)
    {
        $this->filter_orders['>=ID'] = $id;
    }
    
    
    public function setIdTo($id)
    {
        $this->filter_orders['<=ID'] = $id;
    }
    
    
    public function setName($name)
    {
        $this->filter_baskets['%NAME'] = $name;
    }
    
    
    public function setDateFrom($date_from)
    {
        global $DB;
        
        if ($date = ParseDateTime($date_from, CSite::GetDateFormat('FULL'))) {
            if (strlen($date_from) < 11) {
                $date["HH"] = 0;
                $date["MI"] = 0;
                $date["SS"] = 0;
            }
            $date = date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), mktime($date["HH"], $date["MI"], $date["SS"], $date["MM"], $date["DD"], $date["YYYY"]));
            $this->filter_orders['>=DATE_INSERT'] = $date;
        }
    }
    
    
    public function setDateTo($date_to)
    {
        global $DB;
        
        if ($date = ParseDateTime($date_to, CSite::GetDateFormat('FULL'))) {
            if (strlen($date_to) < 11) {
                $date["HH"] = 23;
                $date["MI"] = 59;
                $date["SS"] = 59;
            }
            $date = date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), mktime($date["HH"], $date["MI"], $date["SS"], $date["MM"], $date["DD"], $date["YYYY"]));
            $this->filter_orders['<=DATE_INSERT'] = $date;
        }
    }
    
    
    public function setDateUpdateFrom($date_from)
    {
        global $DB;
        
        if ($date = ParseDateTime($date_from, CSite::GetDateFormat('FULL'))) {
            if (strlen($date_from) < 11) {
                $date["HH"] = 0;
                $date["MI"] = 0;
                $date["SS"] = 0;
            }
            $date = date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), mktime($date["HH"], $date["MI"], $date["SS"], $date["MM"], $date["DD"], $date["YYYY"]));
            $this->filter_orders['>=DATE_UPDATE'] = $date;
        }
    }
    
    
    public function setDateUpdateTo($date_to)
    {
        global $DB;
        
        if ($date = ParseDateTime($date_to, CSite::GetDateFormat('FULL'))) {
            if (strlen($date_to) < 11) {
                $date["HH"] = 23;
                $date["MI"] = 59;
                $date["SS"] = 59;
            }
            $date = date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), mktime($date["HH"], $date["MI"], $date["SS"], $date["MM"], $date["DD"], $date["YYYY"]));
            $this->filter_orders['<=DATE_UPDATE'] = $date;
        }
    }
    
    
    public function setOrderPayed($payed)
    {
        $this->filter_orders['PAYED'] = (string) $payed;
    }
    
    
    public function setOrderCanceled($payed)
    {
        $this->filter_orders['CANCELED'] = (string) $payed;
    }
    
    
    public function setPayed($payed)
    {
        $this->filter_basket_props['PAYED'] = (string) $payed;
    }
    
    
    public function setCanceled($canceled)
    {
        $this->filter_basket_props['CANCELED'] = (string) $canceled;
    }
    
    
    public function setStatus($statuses)
    {
        $statuses = (array) $statuses;
        foreach ($statuses as $status) {
            $status = (string) $status;
            if (strlen($status) > 0) {
                $this->filter_basket_props['status'] []= $status;
            }
        }
    }
    
    
    public function setPersonType($persons)
    {
        $this->filter_orders['PERSON_TYPE_ID'] = array();
        foreach ($persons as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $this->filter_orders['PERSON_TYPE_ID'][] = $id;
            }
        }
    }
    
    
    public function setPaySystem($paysytems)
    {
        $this->filter_orders['PAY_SYSTEM_ID'] = array();
        foreach ($paysytems as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $this->filter_orders['PAY_SYSTEM_ID'][] = $id;
            }
        }
    }
    
    
    public function setDelivery($deliveries)
    {
        $this->filter_orders['DELIVERY_ID'] = array();
        foreach ($deliveries as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $this->filter_orders['DELIVERY_ID'][] = $id;
            }
        }
    }
    
    
    public function setArticle($article)
    {
        $this->filter_basket_props['article'] = LinemediaAutoPartsHelper::clearArticle((string) $article);
    }
    
    
    public function setSupplier($supplier)
    {
        $this->filter_basket_props['supplier_id'] = (array) $supplier;
    }

    
    public function setBrandTitle($brand)
    {
        $this->filter_basket_props['brand_title'] = (string) $brand;
    }
    
    
    public function setOrderId($order_id)
    {
        $this->filter_orders['ID'] = (int) $order_id;
    }
    
    
    public function setOrderIDs($order_ids)
    {
        $this->filter_orders['ID'] = array_map('intval', (array) $order_ids);
    }
    
    
    public function setUserId($user_id)
    {
        if (is_array($user_id)) {
            $user_id = array_map('intval', $user_id);
        } else {
            $user_id = (int) $user_id;
        }
        $this->filter_orders['USER_ID'] = $user_id;
    }
    
    
    public function setUserLogin($login)
    {
        $this->filter_orders['USER_LOGIN'] = (string) $login;
    }
    
    
    public function setUserEmail($email)
    {
        $this->filter_orders['USER_EMAIL'] = (string) $email;
    }
    
    
    public function setOrderProperty($code, $value)
    {
        $this->filter_orders['PROPERTY_VAL_BY_CODE_'.strval($code)] = $value;
    }
    
    
    public function setAdditionalFilter($filters)
    {
        if (empty($filters)) {
            return;
        }
        $this->filter_baskets = array_merge($this->filter_baskets, (array) $filters);
    }
    
    
    
    /*
     * Фильтрация данных.
     * 
     * @return array
     */
    public function filter()
    {
        if (!empty($this->filter_users) && !$this->is_empty) {
            $ids = $this->filterUsers();
            $this->filter_orders['USER_ID'] = (!empty($ids)) ? ($ids) : (false);
            $this->is_filtered = true;
        }
        if (!empty($this->filter_orders) && !$this->is_empty) {
            $ids = $this->filterOrders();
            $this->filter_baskets['ORDER_ID'] = (!empty($ids)) ? ($ids) : (false);
            $this->is_filtered = true;
        }
        if (!empty($this->filter_basket_props) && !$this->is_empty) {
            $this->unionIds($this->filterBasketProps());
            $this->is_filtered = true;
        }
        if (!empty($this->filter_baskets) && !$this->is_empty) {
            $this->unionIds($this->filterBaskets());
            $this->is_filtered = true;
        }
        
        // Выравнивание ключей массива.
        $this->ids = array_values($this->ids);
        
        return $this->ids;
    }
    
    
    /**
     * Фильтрация по пользователям.
     */
    protected function filterUsers()
    {
        $ids = array();
        
        return $ids;
    }
    
    
    /**
     * Фильтрация по заказам.
     */
    protected function filterOrders()
    {
        $ids = array();
        $orders = CSaleOrder::GetList(array(), $this->filter_orders, false, false, array('ID'));
        while ($order = $orders->Fetch()) {
            $ids []= (int) $order['ID'];
        }
        return $ids;
    }
    
    
    /**
     * Фильтрация по свойствам корзины.
     */
    protected function filterBasketProps()
    {
        $ids = array();
        $first = true;
        foreach ($this->filter_basket_props as $code => $value) {
            $items = array();
            $baskets = CSaleBasket::GetPropsList(array(), array('CODE' => $code, 'VALUE' => $value), false, false, array('BASKET_ID'));
            while ($basket = $baskets->Fetch()) {
                $items []= (int) $basket['BASKET_ID'];
            }
            if ($first) {
                $ids = $items;
                $first = false;
            } else {
                $ids = array_intersect($ids, $items);
            }
        }
        return $ids;
    }
    
    
    /**
     * Фильтрация по корзинам.
     */
    protected function filterBaskets()
    {
        $ids = array();
        $baskets = CSaleBasket::GetList(array(), $this->filter_baskets, false, false, array('ID'));
        while ($basket = $baskets->Fetch()) {
            $ids []= (int) $basket['ID'];
        }
        return $ids;
    }
    
    
    /**
     * Пуст ли фильтр.
     */
    protected function isEmpty()
    {
        return $this->is_empty;
    }
    
    
    /**
     * Объединение полученных данных.
     */
    protected function unionIds($ids)
    {
        // Если после фильтрации пришли пустые данные.
        if (empty($ids)) {
            $this->is_empty = true;
            $this->ids = array();
            return;
        }
        
        if (empty($this->ids)) {
            $this->ids = (array) $ids;
        } else {
            $this->ids = array_intersect($this->ids, $ids);
        }
    }
}
    
