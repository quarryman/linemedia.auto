<?php

/**
 * Linemedia Autoportal
 * Wordforms module
 * Parts class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);
 
/*
 * Словоформы.
 */
class LinemediaAutoWordForm
{

    protected $groups;
    protected $titles;
    
    
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        $obCache = new CPHPCache();
        $life_time = 60 * 60 * 24;
        $cache_id = 'b_lm_wordforms'; 
        if ($obCache->InitCache($life_time, $cache_id, "/lm_auto/wordform")) {
            $vars = $obCache->GetVars();
            $this->groups = $vars["groups"];
            $this->titles = $vars["titles"];
        } else {
            if ($obCache->StartDataCache()) {
                global $DB;
                try {
                    $res = $DB->Query('SELECT * FROM `b_lm_wordforms`;');
                } catch (Exception $e) {
                    LinemediaAutoDebug::add('Error loading wordforms for ' . $this->brand_title . ' ' . $e->GetMessage());
                }
                
                $groups = array();
                $titles = array();
                while ($form = $res->Fetch()) {
                    $groups[$form['group']][] = $form['brand_title'];
                    $titles[$form['brand_title']] = $form['group'];
                }
                
                $obCache->EndDataCache(array(
                    "groups"    => $groups,
                    "titles"    => $titles
                ));
                $this->groups = $groups;
                $this->titles = $titles;
            }
        }
        
        /*
         * ?????? ???????
         */
        $events = GetModuleEvents("linemedia.auto", "OnWordformObjectCreate");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$this->groups, &$this->titles));
        }
    }
    
    
    /**
     * Получение группы по бренду.
     */
    public function getBrandWordforms($brand_title)
    {
        $brand_title = strtoupper($brand_title);
        
        $group = $this->titles[$brand_title];
        
        if(isset($this->groups[$group]))
        {
        	$wordforms = $this->groups[$group];
        } else {
        	/*
        	* В качестве бренда передано название группы
        	*/
	        $wordforms = $this->groups[$brand_title];
        }
        return $wordforms;
    }
    
    
    /**
     * Получение групп словоформ
     */
    public function getGroupWordforms($group)
    {
        $group = strtoupper($group);
        $wordforms = $this->groups[$group];
        return $wordforms;
    }
    
    
    public function getBrandGroup($brand_title)
    {
        $brand_title = strtoupper($brand_title);
        return $this->titles[$brand_title];
    }
    
    
    public function clearCache()
    {
        $obCache = new CPHPCache;
        $cache_id = 'b_lm_wordforms'; 
        $obCache->Clean($cache_id, "/");
        BXClearCache(true, "/lm_auto/wordform/");
    }
    
    
    /**
     * Очистка группы.
     */
    public function clearGroup($group)
    {
        global $DB;
        
        $group = "'" . $DB->ForSQL($group) . "'";
        try {
            $DB->Query('DELETE FROM `b_lm_wordforms` WHERE `group` = ' . $group . ';');
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Error clearing wordforms. ' . $e->GetMessage());
        }
        
        $this->clearCache();
    }

    
    /**
     * Проверка на существование словоформы.
     */
    public function isExists($group, $wordform)
    {
        global $DB;
        
        $group      = "'" . $DB->ForSQL($group) . "'";
        $wordform   = "'" . $DB->ForSQL($wordform) . "'";
        
        $DB->Query("SELECT 1 FROM `b_lm_wordforms` WHERE `group` = ".$group." AND `brand_title` = UPPER(".$wordform.");");
        
        return ($DB->SelectedRowsCount() > 0);
    }
    
    
    /**
     * Установка списка брендов для группы.
     */
    public function setGroupWordForms($group, $wordforms, $old_group = false)
    {
        global $DB;
        
        $groupdb = "'" . $DB->ForSQL($group) . "'";
        
        $wordforms = array_map('trim', $wordforms);
        $wordforms = array_map('strtoupper', $wordforms);
        $wordforms = array_filter($wordforms);
        $wordforms = array_map(array($DB, 'ForSQL'), $wordforms);
        
        $unique = array_unique($wordforms);
        
        if (count($unique) < count($wordforms)) {
            throw new Exception(GetMessage('LM_AUTO_WORDFORM_ERROR_NOT_UNIQUE'));
            return;
        }
        
        try {
            $DB->StartTransaction();
            
            $this->clearGroup($old_group ? $old_group : $group);

            foreach ($wordforms as $wordform) {
                $result = $DB->Query('INSERT INTO `b_lm_wordforms` (`group`, `brand_title`) VALUES (' . $groupdb . ', \'' . $wordform . '\')', true);
                if (!$result) {
                    throw new Exception(GetMessage('LM_AUTO_WORDFORM_ERROR_EXIST'));
                }
            }
            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
            throw new Exception($e->GetMessage());
            LinemediaAutoDebug::add('Error updating wordforms. ' . $e->GetMessage());
        }
        
        $this->clearCache();
    }
    
}
