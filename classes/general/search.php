<?php

/**
 * Linemedia Autoportal
 * Main module
 * Parts search class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
* ����� ��������� � ���� ������
*/
class LinemediaAutoSearch
{
    const SEARCH_SIMPLE     = 'LinemediaAutoSearchSimple'; // ������� �����
    const SEARCH_PARTIAL    = 'LinemediaAutoSearchPartial'; // ����� �� ����� ��������
    const SEARCH_GROUP      = 'LinemediaAutoSearchGroup'; // ��������� �����
    
    
    /*
     * ��� ������
     */
    protected $type = null;
    
    
    /**
     * ������� ������
     */
    protected $search_conditions = array(
        'id' => false,
        'query' => '',
        'brand_title' => null,
        'extra' => array(),
    );
    
    
    /*
     * ��������� ������ ���������
     */
    protected $search_article_results = array();
    
    /*
     * ��������, ���� ��� �������
     */
    protected $search_catalog_results = array();
    
    /*
     * ��� ���������� ������
     */
    protected $result_type = '404';
    
    /*
     * Non-fatal exceptions from different modules
     */
    protected $exceptions = array();
    
    /*
     * �������������� ����������
     */
    protected $result_info = array();
    
    
    
    /**
     * ����������� ������ � ������ ���� �������
     */
    public function __construct()
    {
        /*
         * �� �������� ������� ������ ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchInstanceCreate");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(
                &$this->search_conditions,
                &$this->search_article_results,
                &$this->search_catalog_results,
            ));
        }
        
        /*
         * �� ��������� - ������� �����.
         */
        $this->type = self::SEARCH_SIMPLE;
    }
    
    
    /**
     * �������� ���������� �� TecDoc.
     */
    public function getResultInfo()
    {
        return $this->result_info;
    }
    
    
    /**
     * ��������� ��������� ������
     */
    public function setSearchQuery($string)
    {
        $this->setSearchCondition('query', $string);
    }
    
    
    /**
     * ��������� ��������� �����
     */
    public function setType($type)
    {
        if (in_array(strval($type), array(self::SEARCH_SIMPLE, self::SEARCH_PARTIAL, self::SEARCH_GROUP))) {
            $this->type = (string) $type;
        }
    }
    
    
    /**
     * ��������� ��������� ���������
     */
    public function setSearchCondition($param, $val)
    {
        /*
         * �� �������� ������� ������ ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchConditionChange");
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(
                &$param,
                &$val
            ));
        }
        
        $this->search_conditions[$param] = $val;
    }
    
     
    /**
     * ��� ��������� �����������, ��������� �����
     */
    public function execute()
    {
        /*
         * �������� ������ ��� ������
         * ������� ������ � ������� �� ������������
         * � ���� ����� �������� � ������ ���, ��� �� ���� � ��������� ��
         */ 
        if ($this->search_conditions['id'] > 0) {
            /*
             * ����� �� ID ��������
             */
            $sought_part = array(
                'id'        => $this->search_conditions['id'],
                'sought'    => true         // ������� ������, ��, ��� ���� � ����� ������������
            );
        } else {
            
            /*
             * ����� �� ��������.
             * ������� ������� ��� ������������ ����������� �����������.
             * ��������: Sphinx ������������� ������ (query) �� $search_conditions, � �� �� $articles_to_search.
             */
            $sought_part = array(
                'article'   => (string) $this->search_conditions['query'], // LinemediaAutoPartsHelper::clearArticle($this->search_conditions['query']),
                'sought'    => true         // ������� ������, ��, ��� ���� � ����� ������������
            );

            
            /*
             * �������� ������
             */
            if ($this->search_conditions['brand_title']) {
                $sought_part['brand_title'] = $this->search_conditions['brand_title'];
            }
            
            
            /*
             * ������� ������
             */
            if ($this->search_conditions['extra']) {
                $sought_part['extra'] = $this->search_conditions['extra'];
            }
        }
        $this->articles_to_search[] = $sought_part;
        
        
        /*
         * �� ������ ������ ������ �������
         * � ������� ������� ������ � ������������ ������ � ������ ����������
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchExecuteBegin");
        while ($arEvent = $events->Fetch()) {
            /*
             * ���������:
             * ������    - ������� ������
             * ������    - ������ ���������, ������� ���� ������ � �������� ����
             * ������    - ��������, ���� �����
             * �������� - ��������, ������� ��� "��� �� ��� �������" (�������� �� emex)
             * �����     - ��� ������
             * ������    - �������������� ���������� � �������
             */
            try {
                ExecuteModuleEventEx($arEvent, array(
                    &$this->search_conditions,
                    &$this->articles_to_search,
                    &$this->search_catalog_results,
                    &$this->search_article_results,
                    &$this->type,
                    &$this->result_info,
                ));
            } catch (Exception $e) {
                $this->exceptions []= $e;
            }
        }
        
        /*
         * ��� � ��� � ������? ��������� ��� ��� ������?
         * ������ ������ ��������, ���� ������ �����.
         */
        $has_brand = ($this->search_conditions['brand_title'] != '');
        
        if ($has_brand) {
	        /*
	         * ���������� ������ ������
	         */
	        $this->result_type = 'parts';
        } else {
	        /*
	         * ���������� ������, ���� ��� ���������
	         */
	        if (count($this->search_catalog_results) > 0) {
		        $this->result_type = 'catalogs';
	        } else {
		        $this->result_type = 'parts';
	        }
        }
        
        
        /*
         * ��������
         */
        if ($this->result_type == 'catalogs') {
        	/*
             * ����� ���������� ����������.
             */
            LinemediaAutoDebug::add('Catalogs found', false, LM_AUTO_DEBUG_WARNING);
            
           
            /*
             * �������� �� ������ ��������
             */
            foreach ($this->search_catalog_results as $y => $catalog) {
                $this->search_catalog_results[$y]['title'] = $catalog['title'] ? $catalog['title'] : '-';
                $this->search_catalog_results[$y]['brand_title'] = $catalog['brand_title'] ? $catalog['brand_title'] : '-';
            }
            
            /*
             * ����� ������������� ��������
             */
            $this->search_catalog_results = self::getIntersectCatalogs($this->search_catalog_results);
            

            /*
             * � ��������� ������� ����� ���������, ��� ������� ����� ����(��� ��� ������ ���)
             * ����� ���� ����� �������� ��������
             */
            if (count($this->search_catalog_results) <= 1) {
            	LinemediaAutoDebug::add('Catalogs count reduced to one, showing parts', false, LM_AUTO_DEBUG_WARNING);
            	$this->result_type = 'parts';
            }
        }
        
        /*
         * ������
         */
        if ($this->result_type == 'parts') {
	        /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('No catalogs, parts found', print_r($this->articles_to_search, 1), LM_AUTO_DEBUG_WARNING);
            
            /*
             * �������� ��������������� ��� �����.
             * ��������������, ��� � �������� ���������� ����������� ���� ��������� ����������� ������,
             * � ������ ����� ������� ������ ������� ���������� ������ ������� �� �� ���������.
             */
            try {
            	/*
            	 * ���������� ������ �������� �����������
            	 */
            	$suppliers_active = array();
            	$suppliers_res = LinemediaAutoSupplier::GetList();

            	foreach ($suppliers_res as $supplier) {
            		$supplier_id = $supplier['PROPS']['supplier_id']['VALUE'];
	            	$suppliers_active[$supplier_id] = $supplier['ACTIVE'] == 'Y';
            	}
            	
                $found_local_items = array();
                foreach ($this->articles_to_search as $part) {
                    
                    /*
                     * ����� ���������� ����������
                     */
                    LinemediaAutoDebug::add('Search db for art=' . $part['article'], print_r($part, true));
                    
                    /*
                     * ����� �� �������� ������� �������
                     */
                    if ($part['article'] == $sought_part['article'] && !$part['sought']) {
                        continue;
                    }
                    
                    $search = new $this->type();
                    
                    if ($found_local_item_list = $search->searchLocalDatabaseForPart($part, true)) {
                        foreach ($found_local_item_list as &$found_local_item) {
                            
                            /*
                             * ���������� ���������.
                             */
                            if ($suppliers_active[$found_local_item['supplier_id']] !== true) {
                            	continue;
                            }
                            
                            /*
                             * �� ������ ���������� �������� ������ ���� �������?
                             */
                            $found_local_item['analog-source'] = $part['source'];
                            $found_local_item['origin'] = $part['analog-source'];
                            
                            /*
                             * ����� ���������� ����������.
                             */
                            LinemediaAutoDebug::add('Part found art=' . $part['article'] . ' with analog type=' . $part['analog_type']);
                            
                            $analog_type = (int) $part['analog_type'];
                            
                            /*
                             * ������� �������.
                             */
                            if ($part['sought']) {
                                /*
                                 * ����� ���������� ����������.
                                 */
                                LinemediaAutoDebug::add('Part art=' . $part['article']. ' is marked as sought-for by user');
                                $analog_type = 'N';
                            }
                            
                            /*
                             * ������� ������������ ��������� ��������.
                             */
                            $found_local_item['article'] = $found_local_item['original_article'];
                            
                            /*
                             * ��������� �������������� ���������.
                             */
                            $found_local_item['extra'] = $part['extra'];
                             
                            $found_local_items['analog_type_' . $analog_type][] = $found_local_item;
                        }
                    }
                }
            } catch (Exception $e) {
                throw $e;
            }
            
            /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('Informations result', print_r($this->result_info, true), LM_AUTO_DEBUG_WARNING);
            
            
            /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('Local result', print_r($found_local_items, true), LM_AUTO_DEBUG_WARNING);
            
            
            /*
             * ��������� ��������� ���������� ������ � ���, ��� ��� ���� � ����������.
             * ��� ����� ���� ��������, ���� ���� ������� ��������, ������� �� ����� ���� ��� ��������.
             * �������� Emex.
             */
            $this->search_article_results = array_merge_recursive($this->search_article_results, $found_local_items);
        
            foreach ($this->search_article_results as $i => $group) {
                foreach ($group as $j => $part) {
                    $this->search_article_results[$i][$j]['article'] = LinemediaAutoPartsHelper::clearArticle($part['article']);
                }
            }
            
            
            /*
             * ������ ���������� �������� � ���������.
             */
            $this->search_article_results = self::getIntersectParts($this->search_article_results);
            
            
            if (count($this->search_article_results) == 0) {
                /*
                 * 404
                 */
                $this->result_type = '404';
                
                /*
                 * ����� ���������� ����������
                 */
                LinemediaAutoDebug::add('No catalogs and no parts found', false, LM_AUTO_DEBUG_WARNING);
            }
        }
    
        
        /*
         * �� ����� ������ ������ �������
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchExecuteEnd");
        $result_type = $this->result_type;
        while ($arEvent = $events->Fetch()) {
            /*
             * ���������
             * ������    - ������� ������
             * ������    - ������ ���������, ������� ���� ������ � �������� ����
             * ������    - ��������, ���� �����
             * �������� - ��������, ������� ��� "��� �� ��� �������" (�������� �� emex)
             * �����     - ��� ������
             * ������    - �������������� ���������� � �������
             */
            ExecuteModuleEventEx($arEvent, array(
                &$this->search_conditions,
                &$this->articles_to_search,
                &$this->search_catalog_results,
                &$this->search_article_results,
                &$this->type,
                &$this->result_info,
            ));
        }
        
        /*
         * ���� ����� ���������� �������� ����� ���� ��������
         */
        $this->search_article_results = array_filter($this->search_article_results);
        
        
        /*
         * �������� ����������.
         */
        $use_wordform = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SHOW_WORDFORM_PARTS' ,'N');
        if ($use_wordform == 'Y') {
            $wordforms = new LinemediaAutoWordForm();
            foreach ($this->search_article_results as &$group) {
                foreach ($group as &$part) {
                    $wordform = $wordforms->getBrandGroup($part['brand_title']);
                    if (!empty($wordform)) {
                        $part['brand_title'] = $wordform;
                    }
                }
            }
        }
        
        
        /*
         * ����� ���������� ����������
         */
        LinemediaAutoDebug::add('Final result', print_r($this, true), LM_AUTO_DEBUG_WARNING);
        
        $this->result = $result;
    }
    
    
    /**
     * Search return type (catalogs || parts || 404)
     */
    public function getResultsType()
    {
        return $this->result_type;
    }
    
    
    public function getResultsParts()
    {
        return $this->search_article_results;
    }
    
    
    public function getResultsCatalogs()
    {
        return $this->search_catalog_results;
    }
    
    
    /**
     * �������� ������, ���������� �� ������ ����������� ������
     */
    public function getThrownExceptions()
    {
        return $this->exceptions;
    }
    
    
    /**
     * ��������� ���������� ��������� �� ���������.
     * 
     * @param array $results
     */
    protected static function getIntersectParts($groups)
    {
        $result   = array();
        $hasitems = array();
        
        foreach ($groups as $i => $parts) {
            $result[$i] = array();
            foreach ($parts as $j => $part) {                
                $hash = md5($part['supplier_id'].$part['article'].$part['price'].$part['brand_title'].$part['extra']['hash']);
                if (array_key_exists($hash, $hasitems)) {
                    $group_id = $hasitems[$hash];
                    
                    /*
                     * ��������� extra ��������
                     */
                    $new_extra = $result[$group_id][$hash]['extra'];
                    foreach ($part['extra'] as $k => $v) {
	                    if ($new_extra[$k] === $v) {
	                    	continue;
                        }
                        /*
                         * ���� ��������� ���� � ������ ������� ����, �� �������
                         * array_merge_recursive ������ ������� ��� ��� ��������.
                         */
                        if (!array_key_exists($k, $new_extra) || empty($new_extra[$k])) {
                            $new_extra[$k] = $part['extra'][$k];
                        } elseif (!empty($part['extra'][$k])) {
                            $new_extra[$k] = array_merge_recursive($new_extra[$k], $part['extra'][$k]);
                        }
                    }
                    $new_extra['wf_b'] = array_unique((array) $new_extra['wf_b']);
                    $result[$group_id][$hash]['extra'] = $new_extra;
                    
                    continue;
                } else {
                    // �������� ������, � ������� ������� ������ ������
                    $hasitems[$hash] = $i;
                }
                $result[$i][$hash] = $part;
            }
        }
        
        // ������������� �������.
        foreach ($result as $i => $parts) {
            $result[$i] = array_values($parts);
        }
       
        return $result;
    }
    
    
    /**
     * ��������� ���������� ���������.
     * 
     * @param array $results
     */
    protected static function getIntersectCatalogs($results)
    {
    	/*
    	 * ��������� �������� �� �������
    	 */
    	$wordforms = new LinemediaAutoWordForm();
    	
        $brands = array();
        
        foreach ($results as $i => $item) {
        	$brand = strtoupper(trim($item['brand_title']));
            if ($brand == '') {
            	//unset($results[$i]);
            	continue;
            }
            
            /*
             * ��� �� ��������������� ����������?
             */
            $brand_normalized = $wordforms->getBrandGroup($brand);
            if ($brand_normalized) {
            	$brand = $brand_normalized;
            }
            
            $item['extra']['wf_b'][] = $item['brand_title'];
            
            /*
             * ����� ����� ��� ���� (��� ���������� �������)
             */
            if (isset($brands[$brand])) {
            	$brands[$brand]['extra'] = array_merge_recursive((array) $brands[$brand]['extra'], (array) $item['extra']);
            	$brands[$brand]['sources'][] = $item['source'];
                
            	// ���� �� ���� ��������, � ����� ���� - �������� ���.
            	if ($item['title'] != '' && $brands[$brand]['title'] == '') {
            		$brands[$brand]['title'] = $item['title'];
                }
            	continue;
            }
	  		
	  		/*
	  		 * ���� ���� ������ � �� �������� ������ - ������� ��������������� ��������
	  		 */
            if ($brand_normalized) {
            	$item['brand_title_original'] = $item['brand_title'];
            	$item['brand_title'] = $brand_normalized;
            }
            
            $item['sources'][] = $item['source'];
            $brands[$brand] = $item;
        }
        
        return $brands;
    }
    
    
    /**
     * ��������� ���������� �������� ���������.
     * 
     * @param array $results
     */
    protected static function getIntersectCatalogTitleResults($results)
    {
        $results = array_filter($results, array('LinemediaAutoSearch', 'intersectCatalogTitleResults'));
        return $results;
    }
    
    
    /**
     * ���������� ����������� ������, ����� ������� ���������� �������� (�� ����������).
     * 
     * @param array $item
     */
    protected static function intersectCatalogTitleResults($item)
    {
        $item = array_map('strtolower', $item);
        
        static $has = array();
        
        foreach ($has as $hasitem) {
            $hasitem = array_map('strtolower', $hasitem);
            if ($item['brand_title'] == $hasitem['brand_title']) {
                return false;
            }
        }
        $has []= $item;
        
        return true;
    }
}
