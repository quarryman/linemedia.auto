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
class LinemediaAutoPart extends LinemediaAutoPartAll
{
    public function __construct($part_id = false, $data = array())
    {
        parent::__construct($part_id, $data);
    }
    
    
    /**
     * Изменить количество товара в базе
     */
    public function setQuantity($quantity)
    {
        $quantity = (float) $quantity;
        try {
            $this->database->Query("UPDATE `b_lm_products` SET `quantity` = '$quantity' WHERE id = " . $this->part_id . ' LIMIT 1');
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error updating part quantity (ID ' . $part_id . ') ' . $e->GetMessage());
        }
    }
    
    
    /*
     * Загрузить данные о запчасти из БД
     */
    protected function load()
    {
        if ($this->loaded) {
            return;
        }
        $this->loaded = true;
        
        /*
         * составим фильтр для поиска запчасти
         */
        $where = array();
        if ($this->part_id > 0) {
            $where['id'] = $this->part_id;
        } elseif ($this->article) {
            $where['article'] = $this->article;
        }
        $where = $where + (array) $this->extra;
        
        
        /*
         * Ошибка выбора
         */
        if (count($where) == 0) {
            LinemediaAutoDebug::add('Error loading part! No "where" conditions', false, LM_AUTO_DEBUG_CRITICAL, __FILE__, __LINE__);
            return;
        }
        
        /*
         * Обезопасим данные
         */
        $where_cond = array();
        foreach ($where as $column => $val) {
            $where_cond[] = "`" . $this->database->ForSQL($column) . "` = '" . $this->database->ForSQL($val) . "'";
        }
        
        
        /*
         * выполним запрос
         */
        try {
            $res = $this->database->Query('SELECT * FROM `b_lm_products` WHERE ' . join(' AND ', $where_cond));
        } catch (Exception $e) {
            throw $e;
        }

        /*
         * Запчасть найдена?
         */
        if ($part = $res->Fetch()) {
            /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('Part object loaded', print_r($part, true));
            
            /*
             * Создаём событие
             */
            $events = GetModuleEvents("linemedia.auto", "OnAfterPartLoaded");
		    while ($arEvent = $events->Fetch()) {
			    ExecuteModuleEventEx($arEvent, array(
			        &$part
			    ));
		    }
		    $this->data = $part;
        } else {
            /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('Error loading part object, 404', print_r($where, 1));
        }
        
    }
    
    
    /**
     * Сохранение запчасти.
     */
    public function save()
    {
        if (empty($this->data['original_article'])) {
            $this->data['original_article'] = $this->data['article'];
        }
        
        if (empty($this->data['article'])) {
            throw new Exception(GetMessage('LM_AUTO_MAIN_PART_ERROR_EMPTY_ARTICLE'));
        }
        
        if (empty($this->data['brand_title'])) {
            throw new Exception(GetMessage('LM_AUTO_MAIN_PART_ERROR_EMPTY_BRAND_TITLE'));
        }
        
        if (empty($this->data['supplier_id'])) {
            throw new Exception(GetMessage('LM_AUTO_MAIN_PART_ERROR_EMPTY_SUPPLIER_ID'));
        }
        
        /*
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforePartSave");
        while ($arEvent = $events->Fetch()) {
            try {
                $result = ExecuteModuleEventEx($arEvent, array($this->part_id, $this->data));
            } catch (Exception $e) {
                throw $e;
            }
            
            if ($result == false) {
                return;
            }
        }
        
        if ($this->part_id > 0) {
            $result = $this->update();
        } else {
            $result = $this->add();
        }
        
        /*
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterPartSave");
        while ($arEvent = $events->Fetch()) {
            try {
                $result = ExecuteModuleEventEx($arEvent, array($this->part_id, $this->data));
            } catch (Exception $e) {
                throw $e;
            }
        }
        
        return $result;
    }
    
    
    /**
     * Добавление данных.
     */
    public function add()
    {
        /*
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforePartAdd");
        while ($arEvent = $events->Fetch()) {
            try {
                $result = ExecuteModuleEventEx($arEvent, array($this->part_id, $this->data));
            } catch (Exception $e) {
                throw $e;
            }
            
            if ($result == false) {
                return;
            }
        }
        
        $title              = "'" . $this->database->ForSQL($this->data['title']) . "'";
        $article            = "'" . $this->database->ForSQL($this->data['article']) . "'";
        $original_article   = "'" . $this->database->ForSQL($this->data['original_article']) . "'";
        $brand_title        = "'" . $this->database->ForSQL($this->data['brand_title']) . "'";
        $price              = "'" . $this->database->ForSQL($this->data['price']) . "'";
        $quantity           = "'" . $this->database->ForSQL($this->data['quantity']) . "'";
        $group_id           = "'" . $this->database->ForSQL($this->data['group_id']) . "'";
        $weight             = "'" . $this->database->ForSQL($this->data['weight']) . "'";
        $supplier_id        = "'" . $this->database->ForSQL($this->data['supplier_id']) . "'";
        
        
        /*
         * Вставим кастомные значения в БД.
         */
        $lmfields = new LinemediaAutoCustomFields();
         
        $custom_fields = $lmfields->getFields();
        
        $sql_custom_fields = '';
        $sql_custom_values = '';
        if (!empty($custom_fields)) {
            $custom_fields_vars = array();
            $custom_values_vars = array();
            foreach ($custom_fields as $custom_field) {
                $code = $custom_field['code'];
                $custom_fields_vars []= "`".$code."`";
                $custom_values_vars []= "'".$this->database->ForSQL(trim($this->data[$code]))."'";
            }
            $sql_custom_fields = ', '.implode(', ', $custom_fields_vars);
            $sql_custom_values = ', '.implode(', ', $custom_values_vars);
        }
        
        try {
            $this->database->Query("
                INSERT INTO `b_lm_products` (
                    title,
                    article,
                    original_article,
                    brand_title,
                    price,
                    quantity,
                    group_id,
                    weight,
                    supplier_id
                    $sql_custom_fields
                ) VALUES (
                    $title,
                    $article,
                    $original_article,
                    $brand_title,
                    $price,
                    $quantity,
                    $group_id,
                    $weight,
                    $supplier_id
                    $sql_custom_values
                );
            ");
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error adding part ' . $e->GetMessage());
        }
        return $this->database->LastID();
    }
    
    
    /**
     * Обновление данных.
     */
    public function update()
    {
        /*
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforePartUpdate");
        while ($arEvent = $events->Fetch()) {
            try {
                $result = ExecuteModuleEventEx($arEvent, array($this->part_id, $this->data));
            } catch (Exception $e) {
                throw $e;
            }
            
            if ($result == false) {
                return;
            }
        }
        
        $part_id            = "'" . $this->database->ForSQL($this->part_id) . "'";
        $title              = "'" . $this->database->ForSQL($this->data['title']) . "'";
        $article            = "'" . $this->database->ForSQL($this->data['article']) . "'";
        $original_article   = "'" . $this->database->ForSQL($this->data['original_article']) . "'";
        $brand_title        = "'" . $this->database->ForSQL($this->data['brand_title']) . "'";
        $price              = "'" . $this->database->ForSQL($this->data['price']) . "'";
        $quantity           = "'" . $this->database->ForSQL($this->data['quantity']) . "'";
        $group_id           = "'" . $this->database->ForSQL($this->data['group_id']) . "'";
        $weight             = "'" . $this->database->ForSQL($this->data['weight']) . "'";
        $supplier_id        = "'" . $this->database->ForSQL($this->data['supplier_id']) . "'";
        
        /*
         * Вставим кастомные значения в БД.
         */
        $lmfields = new LinemediaAutoCustomFields();
         
        $custom_fields = $lmfields->getFields();
        
        $sql_custom_values = '';
        if (!empty($custom_fields)) {
            $custom_values_vars = array();
            foreach ($custom_fields as $custom_field) {
                $code = $custom_field['code'];
                $custom_values_vars []= "`".$code."` = '".$this->database->ForSQL(trim($this->data[$code]))."'"; 
            }
            $sql_custom_values = ', '.implode(', ', $custom_values_vars);
        }
        
        try {
            $this->database->Query("
                UPDATE `b_lm_products`
                SET 
                    `title` = $title,
                    `article` = $article,
                    `original_article` = $original_article,
                    `brand_title` = $brand_title,
                    `price` = $price,
                    `quantity` = $quantity,
                    `group_id` = $group_id,
                    `weight` = $weight,
                    `supplier_id` = $supplier_id
                     $sql_custom_values
                WHERE `id` = $part_id;
            ");
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error update part ' . $e->GetMessage());
        }
        return $this->part_id;
    }
    
    
    /**
     * Удаление запчасти.
     */
    public function delete()
    {
        $part_id = "'" . $this->database->ForSQL($this->part_id) . "'";
        
        /*
         * Создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforePartDelete");
        while ($arEvent = $events->Fetch()) {
            try {
                $result = ExecuteModuleEventEx($arEvent, array($this->part_id, $this->data));
            } catch (Exception $e) {
                throw $e;
            }
            
            if ($result == false) {
                return;
            }
        }
        
        try {
            $this->database->Query("DELETE FROM `b_lm_products` WHERE `id` = $part_id;");
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error delete part ' . $e->GetMessage());
        }
        return true;
    }
}
