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
 * Класс, отвечающий за работу с заказами.
 */
class LinemediaAutoOrder
{
    /*
     * ID заказа
     */
    protected $id = null;
    
    
    public function __construct($id = false)
    {
        $this->id = (int) $id;
        
        CModule::IncludeModule('sale');
    }
    
    
    public function getID()
    {
        return $this->id;
    }
    
    
    /**
     * Получение свойств заказа.
     */
    public function getProps($code = 'ID')
    {
        $dbprops = CSaleOrderPropsValue::GetOrderProps($this->getID());
        
        $properties = array();
        while ($property = $dbprops->Fetch()) {
            $properties[$property[strval($code)]] = $property;
        }
        return $properties;
    }
    
    
    /**
     * Получить корзины заказа со всеми свойствами.
     */
    public function getBaskets()
    {
        $baskets = array();
        $dbBasketItems = CSaleBasket::GetList(array(), array('ORDER_ID' => $this->id), false, false, array('ID', 'PRODUCT_ID', 'WEIGHT', 'QUANTITY', 'NAME', 'NOTES', 'FUSER_ID', 'DETAIL_PAGE_URL'));
        while ($basket = $dbBasketItems->Fetch()) {
            $db_res = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $basket['ID']));
            while ($prop = $db_res->Fetch()) {
               $basket['PROPS'][$prop['CODE']] = $prop;
            }
            $baskets []= $basket;
        }
        return $baskets;
    }
    
    
    /**
     * Разрешена ли оплата по заказу.
     */
    public function getAllowPayemnt($order_id)
    {
        if (!CModule::IncludeModule('sale')) {
            return;
        }
        
        $dbproperty = CSaleOrderPropsValue::GetList(array(), array('ORDER_ID' => $this->id, 'CODE' => 'ALLOW_PAYMENT'), false, false, array());
        $property = $dbproperty->Fetch();
        
        return $property['VALUE'];
    }
    
    
    /**
     * Установка разрешения оплаты по заказу.
     */
    public function setAllowPayemnt($allow)
    {
        if (!CModule::IncludeModule('sale')) {
            return;
        }
        
        $dbproperty = CSaleOrderPropsValue::GetList(array(), array('ORDER_ID' => $this->id, 'CODE' => 'ALLOW_PAYMENT'), false, false, array());
        
        if ($property = $dbproperty->Fetch()) {
            if (CSaleOrderPropsValue::Update($property['ID'], array('VALUE' => (string) $allow))) {
                return true;
            }
        } else {
            $arOrder = CSaleOrder::getByID($this->id);
            
            $prop = CSaleOrderProps::getList(array(), array('CODE' => 'ALLOW_PAYMENT', 'PERSON_TYPE_ID' => $arOrder['PERSON_TYPE_ID']), false, false, array('ID', 'CODE'))->Fetch();
            
            $arFields = array(
                'ORDER_ID'          => $this->id,
                'ORDER_PROPS_ID'    => $prop['ID'],
                'NAME'              => GetMessage('LM_AUTO_MAIN_ALLOW_PAYMENT'),
                'CODE'              => 'ALLOW_PAYMENT',
                'VALUE'             => $allow
            );
            
            if (CSaleOrderPropsValue::Add($arFields)) {
                return true;
            }
        }
        return false;
    }
    
    
    
    /**
     * Получение списка статусов.
     */
    public static function getStatusesList()
    {
        CModule::IncludeModule('sale');
        
        $statuses = array();
        
        $dbstatuses = CSaleStatus::GetList(
            array('SORT' => 'ASC'),
            array('LID' => LANGUAGE_ID),
            false,
            false,
            array('ID', 'NAME')
        );
        
        while ($status = $dbstatuses->Fetch()) {
            $statuses[$status['ID']] = $status;
        }
        
        return $statuses;
    }


    /**
     * Получение списка платежных систем.
     */
    public static function getPaysystemsList()
    {
        CModule::IncludeModule('sale');
        
        $paysystems = array();
        
        $dbpaysystems = CSalePaySystem::GetList(array('SORT' => 'ASC', 'NAME' => 'ASC'), array());
        
        while ($paysystem = $dbpaysystems->Fetch()) {
            $paysystems[$paysystem['ID']] = $paysystem;
        }
        
        return $paysystems;
    }


    /**
     * Получение списка доставок.
     */
    public static function getDeliveryList()
    {
        CModule::IncludeModule('sale');
        
        $deliveries = array();
        
        $dbdeliveries = CSaleDelivery::GetList(array('SORT' => 'ASC', 'NAME' => 'ASC'), array());
        
        while ($delivery = $dbdeliveries->Fetch()) {
            $deliveries[$delivery['ID']] = $delivery;
        }
        
        return $deliveries;
    }
    

    /**
     * Получение списка типов плательщиков.
     */
    public static function getPersonTypesList()
    {
        CModule::IncludeModule('sale');
        
        $persons = array();
        
        $dbpersons = CSalePersonType::GetList(
            array('SORT' => 'ASC'),
            array(),
            false,
            false,
            array('ID', 'NAME')
        );
        
        while ($person = $dbpersons->Fetch()) {
            $persons[$person['ID']] = $person;
        }
        
        return $persons;
    }
}
