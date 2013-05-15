<?php
/**
 * Linemedia Autoportal
 * Main module
 * Debug all calculations
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 
IncludeModuleLangFile(__FILE__); 

class LinemediaAutoTecDocRights
{
    protected $iblock_id = null;
    
    protected static $instance = null;
    
    
    
    protected function __construct()
    {
        CModule::IncludeModule('iblock');
        
        $this->iblock_id = (int) COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_TECDOC_ACCESS_LIST', false);
        
        if ($this->iblock_id <= 0) {
            throw Exception('TecDoc access iblock ID not exists');
        }
    }
    
    
    /**
     * Получение экземпляра класса (Singleton).
     */
    public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    
    /**
     * Получение доступов к каталогам текдока.
     * 
     * @param string $section
     * @param string $filter
     * @param bool $include_root
     */
    public function getAccess($section = '', $filter = '', $include_root = false)
    {
        $result = array();
        
        if (!empty($section)) {
            $arFilter = array(
                'IBLOCK_ID' => $this->iblock_id,
                'ACTIVE' => 'Y',
                'PROPERTY_API_SECTION' => $sSection,
                'PROPERTY_COMPONENT' => 'LM_TECDOC_CATALOG'
            );
            
            if (!empty($filter)) {
                $aFilter['PROPERTY_API_ID'] = $sFilter . '%';
            }
        
            if ($include_root === true) {
                if (!empty($filter)) {
                    unset($arFilter['PROPERTY_API_ID'] );
                    $arFilter[] = Array(
                               'LOGIC' => 'OR',
                               Array('PROPERTY_API_ID' => $sFilter . '%'),
                               Array('PROPERTY_API_ID' => '/%'),
                               );
    
                } else {
                    $arFilter['PROPERTY_API_ID'] = '/%';
                }
            }
            
            $items = CIBlockElement::GetList(array(), $arFilter, false, false, array('ID', 'IBLOCK_ID', 'PROPERTY_API_ID'));
            while ($item = $items->Fetch()) {
                if (!empty($item['PROPERTY_API_ID_VALUE'])) {
                    $result[$item['PROPERTY_API_ID_VALUE']] = $item['ID'];
                }
            }
        }
        
        return $result;
    }
    
    
    /**
     * Фильтрация для показа.
     * 
     * @param array $params
     * @param array $accesses
     */
    public function filterList($params, &$accesses)
    {
        if (
            (!isset($params['SHOW_ALL_ITEMS']) ||isset($params['SHOW_ALL_ITEMS']) && $params['SHOW_ALL_ITEMS'] !== 'Y')
            && (is_array($accesses['ACCESS_LIST_DISABLE']) && count($accesses['ACCESS_LIST_DISABLE']) > 0 && is_array($accesses['ITEMS']) && count($accesses['ITEMS']) > 0)
        ) {
            foreach ($accesses['ITEMS'] as $index => $value) {
                if (isset($accesses['ACCESS_LIST_DISABLE'][$value['manuId']])) {
                    unset($accesses['ITEMS'][$index]);
                }
            }
        }
    }
}