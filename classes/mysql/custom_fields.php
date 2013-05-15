<?php
/**
 * Linemedia Autoportal
 * Main module
 * Custom database fields
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__); 


class LinemediaAutoCustomFields extends LinemediaAutoCustomFieldsAll
{
    
    /**
     * ���������� ������� � �������.
     * 
     * @param array $data
     */
    public function add($data)
    {
        global $DB;
        
        /*
         * ������ ������� ��� ������ �������.
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeCustomFieldAdd");
        while ($arEvent = $events->Fetch()) {
            try {
                $result = ExecuteModuleEventEx($arEvent, array(&$data));
            } catch (Exception $e) {
                throw $e;
            }
        }
        
         // �������� ������ �� ������������.
        if (!$this->isCorrect($data)) {
            return;
        }
        
        $id = $this->getNextID();
        
        $code = trim((string) $data['code']);
        
        $type = (array_key_exists($data['type'], self::$types)) ? (self::$types[$data['type']]) : (reset(self::$types));
        
        $comment = $DB->ForSql((string) $data['name']);
        
        
        $sql = "ALTER TABLE `".self::TABLE."` ADD COLUMN `".$code."` ".$type." NULL COMMENT '".$comment."';";
        
        $result = $DB->Query($sql, true);
        
        if ($result) {
            $this->incID();
            $this->save($id, $data);
        }
        return $result;
    }
    
    
    /**
     * ��������� ������� � �������.
     * 
     * @param int $id
     * @param array $data
     */
    public function update($id, $data)
    {
        global $DB;
        
        /*
         * ������ ������� ��� ������ �������.
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeCustomFieldAdd");
        while ($arEvent = $events->Fetch()) {
            try {
                $result = ExecuteModuleEventEx($arEvent, array(&$data));
            } catch (Exception $e) {
                throw $e;
            }
        }
        
        // �������� ������ �� ������������.
        if (!$this->isCorrect($data, true)) {
            return;
        }
        
        // ������ ��������.
        $field = $this->get($id);
        
        if (empty($field['code'])) {
            return;
        }
        
        $code = trim((string) $data['code']);
        
        $type = (array_key_exists($data['type'], self::$types)) ? (self::$types[$data['type']]) : (reset(self::$types));
        
        $comment = $DB->ForSql((string) $data['name']);
        
        
        $sql = "ALTER TABLE `".self::TABLE."` CHANGE `".$field['code']."` `".$code."` ".$type." NULL COMMENT '".$comment."';";
        
        $result = $DB->Query($sql, true);
        
        if ($result) {
            $this->save($id, $data);
        }
        return $result;
    }
    
    
    /**
     * �������� ������� � �������.
     */
    public function remove($id, $code)
    {
        global $DB;
        
        /*
         * ������ ������� ��� ������ �������.
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforeCustomFieldRemove");
        while ($arEvent = $events->Fetch()) {
            try {
                $result = ExecuteModuleEventEx($arEvent, array(&$id, &$code));
            } catch (Exception $e) {
                throw $e;
            }
        }
        
        $sql = "ALTER TABLE `".self::TABLE."` DROP ".$code.";";
        
        $result = $DB->Query($sql, true);
        
        if ($result) {
            $this->erase($id);
        }
        return $result;
    }
    
    
    /**
     * �������� �� ������������ ������
     * 
     * @param array $data
     * @param bool $exist - ���� ��� ����������
     */
    public function isCorrect($data, $exist = false)
    {
        $code = trim((string) $data['code']);
        
        // �������� �� ������� ����.
        if (empty($code)) {
            throw new Exception(GetMessage('LM_AUTO_ERROR_EMPTY_CODE'));
            return false;
        }
        
        // �������� �� ������������ ��������� ����.
        if (!preg_match('/^[a-zA-Z]+\w*$/', $code)) {
            throw new Exception(GetMessage('LM_AUTO_ERROR_REGEX_CODE'));
            return false;
        }
        
        // �������� �� ������������ ����.
        if (!$this->isUnique($code, $exist)) {
            throw new Exception(GetMessage('LM_AUTO_ERROR_UNIQUE_CODE'));
            return false;
        }
        
        return true;
    }
    
    
    /**
     * �������� ���� �� ������������.
     * 
     * @param string $code
     * @param bool $exist
     */
    protected function isUnique($code, $exist = false)
    {
        global $DB;
        
        $sql = "SHOW COLUMNS FROM `".self::TABLE."` LIKE '".strval($code)."';";
        
        $res = $DB->Query($sql, true);
        
        if (!$exist) {
            $result = ($res->SelectedRowsCount() <= 0);
        } else {
            $result = ($res->SelectedRowsCount() <= 1);
        }
        
        return $result;
    }
}


