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
 * �����-������� ��� ������ � ��������.
 */
class LinemediaAutoBasket
{
    protected $USER;
    protected $fuser_id = null;
    
    
    /**
     * ������� � ������������ ������ ������������
     */
    public function __construct($user_id = null)
    {
        if (is_null($user_id)) {
            global $USER;
            $user_id = $USER->getId();
        }
        
        $this->USER = CUser::getByID(intval($user_id))->Fetch();
        
        CModule::IncludeModule('sale');
    }
    
    
    /**
     * ��������� ������ �������.
     */
    public function getData($id)
    {
        return CSaleBasket::getByID(intval($id));
    }
    
    
    /**
     * ��������� ��������� FUSER_ID.
     */
    public function getFuserId()
    {
        if (is_null($this->fuser_id)) {
            $sale_user = CSaleUser::GetList(array('USER_ID' => $this->USER['ID']));
            $this->fuser_id = $sale_user['ID'];
        }
        return CSaleBasket::GetBasketUserID(); //$this->fuser_id;
    }
    
    
    /**
     * ��������� ������� �������.
     */
    public static function getProps($basket_id)
    {
        CModule::IncludeModule('sale');
        
        $dbprops = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => intval($basket_id)), false, false, array());
        $props = array();
        while ($prop = $dbprops->Fetch()) {
            $props[$prop['CODE']] = $prop;
        }
        return $props;
    }
    
    
    /**
     * ��������� �������� �������� �������.
     * 
     * @param int $basket_id - ID �������
     * @param array $properties - ������ ������� ��� ��������� (��������� ���������� Bitrix)
     */
    public static function setProperty($basket_id, $properties)
    {
        $props = self::getProps($basket_id);
        
        foreach ($props as $code => $prop) {
            unset($props[$code]['ID']);
            unset($props[$code]['BASKET_ID']);
        }
        
        foreach ($properties as $property) {
            $props[$property['CODE']] = $property;
        }

        CSaleBasket::Update($basket_id, array('PROPS' => $props));
    }
    
    
    /**
     * ��������� ������ ������.
     */
    public function payItem($basket_id, $payed = 'Y')
    {
        $arProps = array();
        
        /*
         * ������ ������� "������ ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemPay");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$payed));
        }
        
        // ���� ������.
        $arProps []= array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED'),
            "CODE" => "payed",
            "VALUE" => (string) $payed
        );
        
        // ���� ������ ������
        $arProps []= array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED_DATE'),
            "CODE" => "payed_date",
            "VALUE" => date('d.m.Y H:i:s')
        );
        
        // ��� �������� ������
        $arProps []= array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_PAYED_ID'),
            "CODE" => "emp_payed_id",
            "VALUE" => $this->USER['ID']
        );
        
        self::setProperty($basket_id, $arProps);
        
        /*
         * ������ ������� "������ ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemPay");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$payed));
        }
    }
    
    
    /**
     * ��������� ������ ������.
     */
    public function cancelItem($basket_id, $canceled = 'Y')
    {
        $arProps = array();
        
        /*
         * ������ ������� "������ ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemCancel");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$canceled));
        }
        
        // ������ ������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED'),
            "CODE" => "canceled",
            "VALUE" => (string) $canceled
        );
        
        // ���� ������ ������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DATE'),
            "CODE" => "canceled_date",
            "VALUE" => date('d.m.Y H:i:s')
        );
        
        // ��� ������� �����
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_CANCELED_ID'),
            "CODE" => "emp_canceled_id",
            "VALUE" => $this->USER['ID']
        );
        
        self::setProperty($basket_id, $arProps);
        
        /*
         * ������ ������� "������ ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemCancel");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$canceled));
        }
    }
    
    
    /**
     * ��������� �������.
     */
    public function statusItem($basket_id, $status)
    {
        $arProps = array();
        
        /*
         * ������ ������� "��������� ������� ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemStatus");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$status));
        }
        
        // ������ ������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS'),
            "CODE" => "status",
            "VALUE" => (string) $status
        );
        
        // ���� ��������� �������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DATE'),
            "CODE" => "date_status",
            "VALUE" => date('d.m.Y H:i:s')
        );
        
        // ��� ������� ������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_STATUS_ID'),
            "CODE" => "emp_status_id",
            "VALUE" => $this->USER['ID']
        );

        self::setProperty($basket_id, $arProps);
        
        /*
         * ������ ������� "��������� ������� ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemStatus");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$status));
        }
    }

    
    /**
     * ��������� ��������.
     */
    public function deliveryItem($basket_id, $delivery)
    {
        $arProps = array();
        
        /*
         * ������ ������� "��������� �������� ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemDelivery");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$delivery));
        }
        
        // ����������� ��������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVREY'),
            "CODE" => "delivery",
            "VALUE" => (string) $delivery
        );
        
        // ���� ��������� ������� ��������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_DATE'),
            "CODE" => "date_delivery",
            "VALUE" => date('d.m.Y H:i:s')
        );
        
        // ��� ������� ������ ��������
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_DELIVERY_ID'),
            "CODE" => "emp_delivery_id",
            "VALUE" => $this->USER['ID']
        );
        
        self::setProperty($basket_id, $arProps);
        
        
        /*
         * ������ ������� "��������� �������� ������ (�������)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemDelivery");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$delivery));
        }
    }
    
    
    /**
     * ���������� ������ � ������� ������������
     * supplier_id ����� ������� �� ID ��������, �� ���� ����������, �������� ������� �� �������� � ��
     * ��� ����������� � ���������� ����������
     */
    public function addItem($part_id, $supplier_id = null, $quantity = 1, $price = null, $additional = array())
    {
    	/*
    	 * ���� ��� �� ����� ��������� � �������.
    	 */
    	if (LinemediaAutoUserHelper::isSearchRobot()) {
    		CHTTP::SetStatus(404);
	    	exit;
    	}
    	
    	
        $arFields = array();
        
        /*
         * ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemAdd");
        while ($arEvent = $events->Fetch()) {
        	try {
            	ExecuteModuleEventEx($arEvent, array(&$part_id, &$supplier_id, &$quantity, &$arFields, &$additional));
            } catch (Exception $e) {
            	
	            return false;
            }
        }
        
        /*
         * ����.
         */
        $site_id = (!empty($additional['SITE_ID'])) ? (strval($additional['SITE_ID'])) : (SITE_ID);
        
        
        /*
         * ����� ��������
         */
        $part = new LinemediaAutoPart($part_id, $additional);
        
        
        /*
         * ����� ����������
         */
        $supplier = new LinemediaAutoSupplier($supplier_id);
        
        
        /*
         * �������� ����������
         */
        $quantity = $part->fixQuantity($quantity);
        
        
        /*
         * ��������� ����
         */
        $price_obj = new LinemediaAutoPrice($part);
        
        
        /*
         * ������� �����
         */
        $brand_title = $part->get('brand_title');
        
        
        /*
         * ���� ��������
         */
        $delivery_time  = (int) $additional['delivery_time'];
        $delivery_time += (int) $supplier->get('delivery_time');
        
        
        /*
         * ���� � ������ ��������
         */
        $url = $additional;
        $url['article'] = $part->get('article');
        $part_path = LinemediaAutoUrlHelper::getPartUrl($url);
        
        
        if (!is_null($price)) {
            $price      = (float) $price;
            $currency   = CCurrency::GetBaseCurrency();
        } else {
            $price      = (float) $price_obj->calculate();
            $currency   = $price_obj->getCurrency();
        }
        
        $arFields = array_merge_recursive(
            array(
                "PRODUCT_ID"            => $part_id,
                "FUSER_ID"              => $this->getFuserId(),
                "PRICE"                 => $price,
                "CURRENCY"              => $currency,
                "WEIGHT"                => $part->get('weight'),
                "QUANTITY"              => $quantity,
                "LID"                   => $site_id,
                "DELAY"                 => "N",
                "CAN_BUY"               => "Y",
                "NAME"                  => $brand_title . ' [' . $part->get('article') . '] ' . $part->get('title'),
                "MODULE"                => "linemedia.auto",
                "NOTES"                 => "",
                "DETAIL_PAGE_URL"       => $part_path
            ), 
            $arFields
        );
        
        $arProps = array();
        
        /*
         * ID ����������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_ID'),
            "CODE" => "supplier_id",
            "VALUE" => $supplier_id
        );
        
        /*
         * �������� ����������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_TITLE'),
            "CODE" => "supplier_title",
            "VALUE" => $supplier->get('NAME')
        );
        
        /*
         * �������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_ARTICLE'),
            "CODE" => "article",
            "VALUE" => $part->get('article')
        );
        
        /*
         * �������� �������������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_TITLE'),
            "CODE" => "brand_title",
            "VALUE" => $brand_title
        );
        
        /*
         * ���������� ����
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BASE_PRICE'),
            "CODE" => "base_price",
            "VALUE" => $part->get('price')
        );
        
        /*
         * ������ ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED'),
            "CODE" => "payed",
            "VALUE" => 'N'
        );
        
        /*
         * ���� ������ ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED_DATE'),
            "CODE" => "payed_date",
            "VALUE" => ''
        );
        
        /*
         * ��� �������� ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_PAYED_ID'),
            "CODE" => "emp_payed_id",
            "VALUE" => ''
        );
        
        /*
         * ������ ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED'),
            "CODE" => "canceled",
            "VALUE" => 'N'
        );
        
        /*
         * ���� ������ ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DATE'),
            "CODE" => "canceled_date",
            "VALUE" => ''
        );
        
        /*
         * ��� ������� �����
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_CANCELED_ID'),
            "CODE" => "emp_canceled_id",
            "VALUE" => ''
        );
        
        /*
         * ������ ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS'),
            "CODE" => "status",
            "VALUE" => 'N'
        );
        
        /*
         * ���� ��������� �������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DATE'),
            "CODE" => "date_status",
            "VALUE" => date('d.m.Y H:i:s')
        );
        
        /*
         * ��� ������� ������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_STATUS_ID'),
            "CODE" => "emp_status_id",
            "VALUE" => $this->USER['ID']
        );
        
        /*
         * ����������� ��������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVREY'),
            "CODE" => "delivery",
            "VALUE" => 'N'
        );
        
        /*
         * ���� ��������� ������� ��������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_DATE'),
            "CODE" => "date_delivery",
            "VALUE" => ''
        );
        
        /*
         * ��� ������� ������ ��������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_DELIVERY_ID'),
            "CODE" => "emp_delivery_id",
            "VALUE" => ''
        );
        
        /*
         * ID ����������
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_TIME'),
            "CODE" => "delivery_time",
            "VALUE" => $delivery_time
        );
        
        $arFields['PROPS'] = array_merge((array)$arFields['PROPS'], $arProps);
        
        /*
         * ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnBasketItemAdd");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$part_id, &$supplier_id, &$quantity, &$arFields, &$additional));
        }
        
        $basket_id = CSaleBasket::Add($arFields);
        
        /*
         * ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemAdd");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$part_id, &$supplier_id, &$quantity, &$basket_id, &$arFields));
        }
        
        return $basket_id;
    }
    
    
    /**
     * ��������� ������ ������, ������� � ������.
     */
    public function getOrderedBaskets()
    {
        $dbbaskets = CSaleBasket::GetList(array(), array('ORDER_ID' => false, 'FUSER_ID' => $this->getFuserId()), false, false, array());
        $baskets = array();
        while ($basket = $dbbaskets->Fetch()) {
            $baskets[$basket['ID']] = $basket;
        }
        return $baskets;
    }
    
    
    /**
     * ����������� ���������� � �������.
     */
    public function fixQuantity($quantity, LinemediaAutoPart $part)
    {
        $quantity       = (int) $quantity;
        $partquantity   = (int) $part->get('quantity');
        
        if ($quantity <= 0) {
            $quantity = 1;
        }
        if ($quantity > $partquantity) {
            $quantity = $partquantity;
        }
        
        /*
         * ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnBasketFixQuantity");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$quantity, &$part, &$this));
        }
        
        return $quantity;
    }
}
