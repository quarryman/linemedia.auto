<?php
/**
 * Linemedia Autoportal
 * Main module
 * Connection to Linemedia API
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__); 


abstract class LinemediaAutoCustomFieldsAll
{
    const TABLE             = 'b_lm_products';
    const CODE_COUNT_FIELDS = 'COUNT';
    
    protected static $types = array(
        'string'    => 'VARCHAR(255)', // CHARACTER SET utf8 COLLATE utf8_general_ci',
        'integer'   => 'FLOAT(11, 2)',
        'text'      => 'TEXT',
    );
    
    
    /**
     * Получение данных поля.
     * 
     * @param int $id
     */
    public function get($id)
    {
        $id = strtoupper((string) $id);
        
        $field = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_CUSTOM_FIELD_'.$id, array()));
        
        return $field;
    }
    
    
    /**
     * Получение данных поля по коду.
     * 
     * @param string $code
     */
    public function getByCode($code)
    {
        $code = (string) $code;
        
        $fields = $this->getFields('code');
        
        return $fields[$code];
    }
    
    
    /**
     * Увеличение следующего значения поля.
     */
    public function getFields($key = 'id')
    {
        $id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_CUSTOM_FIELD_'.self::CODE_COUNT_FIELDS, 1);
        
        $key = (string) $key;
        
        $fields = array();
        for ($i = 1; $i <= $id; $i++) {
            $field = $this->get($i);
            if (!empty($field)) {
                $fields[$field[$key]] = $field;
            }
        }
        
        /*
         * Создаём событие для других модулей.
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeCustomFieldGetFields");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$fields));
        }
        
        return $fields;
    }
    
    
    /**
     * Получение типов.
     */
    public static function getTypes()
    {
        $result = array();
        
        foreach (self::$types as $type => $value) {
            $result[$type] = GetMessage('LM_AUTO_MAIN_CUSTOM_FIELDS_'.strtoupper($type));
        }
        return $result;
    }
    
    
    /**
     * Сохранение данных о столбце.
     * 
     * @param int $id
     * @param array $data
     */
    public function save($id, $data)
    {
        $id   = (int) $id;
        $data = (array) $data;
        
        $data['id'] = $id;
        
        
        /*
         * Создаём событие для других модулей.
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeCustomFieldSave");
        while ($arEvent = $events->Fetch()) {
            try {
                $result = ExecuteModuleEventEx($arEvent, array(&$id, &$data));
            } catch (Exception $e) {
                throw $e;
            }
        }
        
        return COption::SetOptionString('linemedia.auto', 'LM_AUTO_CUSTOM_FIELD_'.$id, serialize($data));
    }
    
    
    /**
     * Получение следующего значения поля.
     */
    protected function getNextID()
    {
        $id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_CUSTOM_FIELD_'.self::CODE_COUNT_FIELDS, 1);
        
        return (++$id);
    }
    
    
    /**
     * Увеличение следующего значения поля.
     */
    protected function incID()
    {
        $id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_CUSTOM_FIELD_'.self::CODE_COUNT_FIELDS, 1);
        
        COption::SetOptionInt('linemedia.auto', 'LM_AUTO_CUSTOM_FIELD_'.self::CODE_COUNT_FIELDS, ++$id);
    }
    
    
    /**
     * Удаление данных о столбце.
     * 
     * @param int $id
     */
    protected function erase($id)
    {
        COption::SetOptionString('linemedia.auto', 'LM_AUTO_CUSTOM_FIELD_'.$id, null);
    }
    
    
    abstract public function add($data);
    
    abstract public function update($id, $data);
    
    abstract public function remove($id, $code);
}


