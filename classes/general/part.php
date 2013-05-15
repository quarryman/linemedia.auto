<?php

/**
 * Linemedia Autoportal
 * Main module
 * Parts class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/*
 * 
 */
abstract class LinemediaAutoPartAll
{
    const ANALOG_GROUP_ORIGINAL     = 'N';  // Искомый артикул
    const ANALOG_GROUP_UNORIGINAL   = '0';  // Неоригинальные аналоги
    const ANALOG_GROUP_OEM          = '1';  // OEM аналоги
    const ANALOG_GROUP_TRADE        = '2';  // Продажные номера
    const ANALOG_GROUP_COMPARABLE   = '3';  // Сравнительные номера
    const ANALOG_GROUP_REPLACE      = '4';  // Замены
    const ANALOG_GROUP_OUTDATE      = '5';  // Замены устаревшего артикула
    const ANALOG_GROUP_EAN          = '6';  // EAN
    const ANALOG_GROUP_OTHER        = '10'; // Другое


    protected $part_id;
    
    protected $loaded = false;
    protected $database;
    
    protected $data;
    
    
    
    /**
     * Проверём в конструкторе объект пользователя
     */
    public function __construct($part_id = false, $data = array())
    {
        $this->part_id = $part_id;
        $this->data    = $data;
        
        /*
         * Вывод отладочной информации
         */
        LinemediaAutoDebug::add('Part object created (ID ' . $part_id . ')');
        
        /*
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnPartObjectCreate");
		while ($arEvent = $events->Fetch()) {
			ExecuteModuleEventEx($arEvent, array(&$this->part_id, &$this->data, &$data, &$this->loaded));
		}
        
        /*
         * Connect to DB
         */
        if (!$this->loaded) {
            try {
                $this->database = new LinemediaAutoDatabase();
            } catch (Exception $e) {
                throw $e;
            }
        }
    }
    
    /**
     * Установить поставщика детали
     * нужно для поставщиков вроде EMEX
     * запчасти которых отсутствуют в локальной БД
     */
    public function setSupplierObject(LinemediaAutoSupplier $supplier)
    {
        
    }
    
    
    /**
     * Получить поле
     */
    public function get($field)
    {
        $this->load();
        return $this->data[$field];
        
    }
    
    
    /**
     * Получить все поля
     */
    public function getArray()
    {
        $this->load();
        return $this->data;
        
    }
    
    
    /**
     * Записать поля
     */
    public function setDataArray($data)
    {
        $this->data = array_merge_recursive($this->data, $data);
        
    }
    
    
    /**
     * Проверить наличие
     */
    public function exists()
    {
        $this->load();
        return count($this->data) > 0 AND $this->data['article'] != '';
    }
    
    
    /**
     * Проверка и исправление количества в корзине.
     * 
     * @param int $quantity
     */
    public function fixQuantity($quantity)
    {
        $quantity       = (int) $quantity;
        $partquantity   = (int) $this->get('quantity');
        
        if ($quantity <= 0) {
            $quantity = 1;
        }
        if ($partquantity > 0 && $quantity > $partquantity) {
            $quantity = $partquantity;
        }
        
        /*
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnPartFixQuantity");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$quantity, &$this));
        }
        return $quantity;
    }
    
    
    /**
     * Получение типов аналогов.
     */
    public static function getAnalogGroups()
    {
        $analogs = array(
            self::ANALOG_GROUP_ORIGINAL     => GetMessage('LM_AUTO_MAIN_ANALOG_GROUP_' . self::ANALOG_GROUP_ORIGINAL),
            self::ANALOG_GROUP_UNORIGINAL   => GetMessage('LM_AUTO_MAIN_ANALOG_GROUP_' . self::ANALOG_GROUP_UNORIGINAL),
            self::ANALOG_GROUP_OEM          => GetMessage('LM_AUTO_MAIN_ANALOG_GROUP_' . self::ANALOG_GROUP_OEM),
            self::ANALOG_GROUP_TRADE        => GetMessage('LM_AUTO_MAIN_ANALOG_GROUP_' . self::ANALOG_GROUP_TRADE),
            self::ANALOG_GROUP_COMPARABLE   => GetMessage('LM_AUTO_MAIN_ANALOG_GROUP_' . self::ANALOG_GROUP_COMPARABLE),
            self::ANALOG_GROUP_REPLACE      => GetMessage('LM_AUTO_MAIN_ANALOG_GROUP_' . self::ANALOG_GROUP_REPLACE),
            self::ANALOG_GROUP_OUTDATE      => GetMessage('LM_AUTO_MAIN_ANALOG_GROUP_' . self::ANALOG_GROUP_OUTDATE),
            self::ANALOG_GROUP_EAN          => GetMessage('LM_AUTO_MAIN_ANALOG_GROUP_' . self::ANALOG_GROUP_EAN),
            self::ANALOG_GROUP_OTHER        => GetMessage('LM_AUTO_MAIN_ANALOG_GROUP_' . self::ANALOG_GROUP_OTHER),
        );
        
        return $analogs;
    }
    
    
    /**
     * Получение название типа аналога.
     */
    public static function getAnalogGroupTitle($code, $original = false)
    {
        $code       = (string) $code;
        $original   = (bool) $original;
        
        $analogs = self::getAnalogGroups();
        
        if ($original) {
            return $analogs[$code];
        }
        return COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_ANALOGS_GROUPS_'.$code, $analogs[$code]);
    }
    
    
    abstract protected function load();
    abstract protected function setQuantity($quantity);
    abstract public function save();
}
