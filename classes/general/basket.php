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
 * Класс-обертка для работы с корзиной.
 */
class LinemediaAutoBasket
{
    protected $USER;
    protected $fuser_id = null;
    
    
    /**
     * Проверём в конструкторе объект пользователя
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
     * Полученеи данных корзины.
     */
    public function getData($id)
    {
        return CSaleBasket::getByID(intval($id));
    }
    
    
    /**
     * Получение параметра FUSER_ID.
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
     * Получение свойств корзины.
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
     * Установка значения свойства корзины.
     * 
     * @param int $basket_id - ID корзины
     * @param array $properties - Массив свойств для изменения (структура аналогична Bitrix)
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
     * Установка оплаты товара.
     */
    public function payItem($basket_id, $payed = 'Y')
    {
        $arProps = array();
        
        /*
         * Создаём событие "Оплата товара (корзины)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemPay");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$payed));
        }
        
        // Флаг оплаты.
        $arProps []= array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED'),
            "CODE" => "payed",
            "VALUE" => (string) $payed
        );
        
        // Дата оплата товара
        $arProps []= array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED_DATE'),
            "CODE" => "payed_date",
            "VALUE" => date('d.m.Y H:i:s')
        );
        
        // Кем изменена оплата
        $arProps []= array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_PAYED_ID'),
            "CODE" => "emp_payed_id",
            "VALUE" => $this->USER['ID']
        );
        
        self::setProperty($basket_id, $arProps);
        
        /*
         * Создаём событие "Оплата товара (корзины)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemPay");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$payed));
        }
    }
    
    
    /**
     * Установка отмены товара.
     */
    public function cancelItem($basket_id, $canceled = 'Y')
    {
        $arProps = array();
        
        /*
         * Создаём событие "Отмена товара (корзины)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemCancel");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$canceled));
        }
        
        // Отмена заказа
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED'),
            "CODE" => "canceled",
            "VALUE" => (string) $canceled
        );
        
        // Дата отмены заказа
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DATE'),
            "CODE" => "canceled_date",
            "VALUE" => date('d.m.Y H:i:s')
        );
        
        // Кем отменен заказ
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_CANCELED_ID'),
            "CODE" => "emp_canceled_id",
            "VALUE" => $this->USER['ID']
        );
        
        self::setProperty($basket_id, $arProps);
        
        /*
         * Создаём событие "Отмена товара (корзины)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemCancel");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$canceled));
        }
    }
    
    
    /**
     * Установка статуса.
     */
    public function statusItem($basket_id, $status)
    {
        $arProps = array();
        
        /*
         * Создаём событие "Установка статуса товара (корзины)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemStatus");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$status));
        }
        
        // Статус товара
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS'),
            "CODE" => "status",
            "VALUE" => (string) $status
        );
        
        // Дата изменения статуса
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DATE'),
            "CODE" => "date_status",
            "VALUE" => date('d.m.Y H:i:s')
        );
        
        // Кем изменен статус
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_STATUS_ID'),
            "CODE" => "emp_status_id",
            "VALUE" => $this->USER['ID']
        );

        self::setProperty($basket_id, $arProps);
        
        /*
         * Создаём событие "Установка статуса товара (корзины)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemStatus");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$status));
        }
    }

    
    /**
     * Установка доставки.
     */
    public function deliveryItem($basket_id, $delivery)
    {
        $arProps = array();
        
        /*
         * Создаём событие "Установка доставки товара (корзины)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeBasketItemDelivery");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$delivery));
        }
        
        // Возможность доставки
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVREY'),
            "CODE" => "delivery",
            "VALUE" => (string) $delivery
        );
        
        // Дата изменения статуса доставки
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_DATE'),
            "CODE" => "date_delivery",
            "VALUE" => date('d.m.Y H:i:s')
        );
        
        // Кем изменен статус доставки
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_DELIVERY_ID'),
            "CODE" => "emp_delivery_id",
            "VALUE" => $this->USER['ID']
        );
        
        self::setProperty($basket_id, $arProps);
        
        
        /*
         * Создаём событие "Установка доставки товара (корзины)"
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemDelivery");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$basket_id, &$delivery));
        }
    }
    
    
    /**
     * Добавление товара в корзину пользователя
     * supplier_id можно полчить из ID запчасти, но есть поставщики, запчасти которых не хранятся в БД
     * Это проверяется в настройках поставщика
     */
    public function addItem($part_id, $supplier_id = null, $quantity = 1, $price = null, $additional = array())
    {
    	/*
    	 * Гугл бот не может добавлять в корзину.
    	 */
    	if (LinemediaAutoUserHelper::isSearchRobot()) {
    		CHTTP::SetStatus(404);
	    	exit;
    	}
    	
    	
        $arFields = array();
        
        /*
         * Создаём событие
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
         * Сайт.
         */
        $site_id = (!empty($additional['SITE_ID'])) ? (strval($additional['SITE_ID'])) : (SITE_ID);
        
        
        /*
         * Найдём запчасть
         */
        $part = new LinemediaAutoPart($part_id, $additional);
        
        
        /*
         * Найдём поставщика
         */
        $supplier = new LinemediaAutoSupplier($supplier_id);
        
        
        /*
         * Проверим количество
         */
        $quantity = $part->fixQuantity($quantity);
        
        
        /*
         * Посчитаем цену
         */
        $price_obj = new LinemediaAutoPrice($part);
        
        
        /*
         * Получим бренд
         */
        $brand_title = $part->get('brand_title');
        
        
        /*
         * Срок доставки
         */
        $delivery_time  = (int) $additional['delivery_time'];
        $delivery_time += (int) $supplier->get('delivery_time');
        
        
        /*
         * Путь к поиску запчасти
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
         * ID поставщика
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_ID'),
            "CODE" => "supplier_id",
            "VALUE" => $supplier_id
        );
        
        /*
         * Название поставщика
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_SUPPLIER_TITLE'),
            "CODE" => "supplier_title",
            "VALUE" => $supplier->get('NAME')
        );
        
        /*
         * Артикул
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_ARTICLE'),
            "CODE" => "article",
            "VALUE" => $part->get('article')
        );
        
        /*
         * Название производителя
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BRAND_TITLE'),
            "CODE" => "brand_title",
            "VALUE" => $brand_title
        );
        
        /*
         * Закупочная цена
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_BASE_PRICE'),
            "CODE" => "base_price",
            "VALUE" => $part->get('price')
        );
        
        /*
         * Оплата товара
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED'),
            "CODE" => "payed",
            "VALUE" => 'N'
        );
        
        /*
         * Дата оплата товара
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_PAYED_DATE'),
            "CODE" => "payed_date",
            "VALUE" => ''
        );
        
        /*
         * Кем изменена оплата
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_PAYED_ID'),
            "CODE" => "emp_payed_id",
            "VALUE" => ''
        );
        
        /*
         * Отмена заказа
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED'),
            "CODE" => "canceled",
            "VALUE" => 'N'
        );
        
        /*
         * Дата отмены заказа
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_CANCELED_DATE'),
            "CODE" => "canceled_date",
            "VALUE" => ''
        );
        
        /*
         * Кем отменен заказ
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_CANCELED_ID'),
            "CODE" => "emp_canceled_id",
            "VALUE" => ''
        );
        
        /*
         * Статус товара
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS'),
            "CODE" => "status",
            "VALUE" => 'N'
        );
        
        /*
         * Дата изменения статуса
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_STATUS_DATE'),
            "CODE" => "date_status",
            "VALUE" => date('d.m.Y H:i:s')
        );
        
        /*
         * Кем изменен статус
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_STATUS_ID'),
            "CODE" => "emp_status_id",
            "VALUE" => $this->USER['ID']
        );
        
        /*
         * Возможность доставки
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVREY'),
            "CODE" => "delivery",
            "VALUE" => 'N'
        );
        
        /*
         * Дата изменения статуса доставки
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_DATE'),
            "CODE" => "date_delivery",
            "VALUE" => ''
        );
        
        /*
         * Кем изменен статус доставки
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_EMP_DELIVERY_ID'),
            "CODE" => "emp_delivery_id",
            "VALUE" => ''
        );
        
        /*
         * ID поставщика
         */
        $arProps[] = array(
            "NAME" => GetMessage('LM_AUTO_MAIN_BASKET_DELIVERY_TIME'),
            "CODE" => "delivery_time",
            "VALUE" => $delivery_time
        );
        
        $arFields['PROPS'] = array_merge((array)$arFields['PROPS'], $arProps);
        
        /*
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnBasketItemAdd");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$part_id, &$supplier_id, &$quantity, &$arFields, &$additional));
        }
        
        $basket_id = CSaleBasket::Add($arFields);
        
        /*
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterBasketItemAdd");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$part_id, &$supplier_id, &$quantity, &$basket_id, &$arFields));
        }
        
        return $basket_id;
    }
    
    
    /**
     * Получение списка корзин, готовых к заказу.
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
     * Исправление количества в корзине.
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
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnBasketFixQuantity");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$quantity, &$part, &$this));
        }
        
        return $quantity;
    }
}
