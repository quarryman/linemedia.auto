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
* Поиск запчастей в базе данных
*/
class LinemediaAutoSearch
{
    const SEARCH_SIMPLE     = 'LinemediaAutoSearchSimple'; // Простой поиск
    const SEARCH_PARTIAL    = 'LinemediaAutoSearchPartial'; // Поиск по части артикула
    const SEARCH_GROUP      = 'LinemediaAutoSearchGroup'; // Групповой поиск
    
    
    /*
     * Тип поиска
     */
    protected $type = null;
    
    
    /**
     * Условия поиска
     */
    protected $search_conditions = array(
        'id' => false,
        'query' => '',
        'brand_title' => null,
        'extra' => array(),
    );
    
    
    /*
     * Результат поиска запчастей
     */
    protected $search_article_results = array();
    
    /*
     * Каталоги, если они найдены
     */
    protected $search_catalog_results = array();
    
    /*
     * Тип результата поиска
     */
    protected $result_type = '404';
    
    /*
     * Non-fatal exceptions from different modules
     */
    protected $exceptions = array();
    
    /*
     * Дополнительная информация
     */
    protected $result_info = array();
    
    
    
    /**
     * Конструктор пустой и создан ради события
     */
    public function __construct()
    {
        /*
         * На создание объекта поиска создаём событие
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
         * По умолчанию - простой поиск.
         */
        $this->type = self::SEARCH_SIMPLE;
    }
    
    
    /**
     * Получить информацию из TecDoc.
     */
    public function getResultInfo()
    {
        return $this->result_info;
    }
    
    
    /**
     * Установим поисковую строку
     */
    public function setSearchQuery($string)
    {
        $this->setSearchCondition('query', $string);
    }
    
    
    /**
     * Установим групповой поиск
     */
    public function setType($type)
    {
        if (in_array(strval($type), array(self::SEARCH_SIMPLE, self::SEARCH_PARTIAL, self::SEARCH_GROUP))) {
            $this->type = (string) $type;
        }
    }
    
    
    /**
     * Установим поисковое уточнение
     */
    public function setSearchCondition($param, $val)
    {
        /*
         * На создание объекта поиска создаём событие
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
     * Все настройки установлены, выполняем поиск
     */
    public function execute()
    {
        /*
         * Основная деталь для поиска
         * которая пришла в запросе от пользователя
         * её надо сразу добавить в список тех, что мы ищем в локальной БД
         */ 
        if ($this->search_conditions['id'] > 0) {
            /*
             * Поиск по ID запчасти
             */
            $sought_part = array(
                'id'        => $this->search_conditions['id'],
                'sought'    => true         // искомый арикул, то, что вбил в поиск пользователь
            );
        } else {
            
            /*
             * Поиск по артикулу.
             * Очистим артикул для последующего правильного объединения.
             * Внимание: Sphinx ориентиреутся запрос (query) из $search_conditions, а не из $articles_to_search.
             */
            $sought_part = array(
                'article'   => (string) $this->search_conditions['query'], // LinemediaAutoPartsHelper::clearArticle($this->search_conditions['query']),
                'sought'    => true         // искомый арикул, то, что вбил в поиск пользователь
            );

            
            /*
             * Название бренда
             */
            if ($this->search_conditions['brand_title']) {
                $sought_part['brand_title'] = $this->search_conditions['brand_title'];
            }
            
            
            /*
             * Внешние данные
             */
            if ($this->search_conditions['extra']) {
                $sought_part['extra'] = $this->search_conditions['extra'];
            }
        }
        $this->articles_to_search[] = $sought_part;
        
        
        /*
         * На начало поиска создаём событие
         * в событие передаём массив с результатами поиска и объект поисковика
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchExecuteBegin");
        while ($arEvent = $events->Fetch()) {
            /*
             * Аргументы:
             * Первый    - условия поиска
             * Второй    - список запчастей, которые надо искать в локально базе
             * Третий    - каталоги, если нужно
             * Четвёртый - запчасти, которые там "как бы уже найдены" (например от emex)
             * Пятый     - тип поиска
             * Шестой    - дополнительная информация о деталях
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
         * Что у нас в ответе? Уточнение или уже детали?
         * Нельзя искать каталоги, если пришёл бренд.
         */
        $has_brand = ($this->search_conditions['brand_title'] != '');
        
        if ($has_brand) {
	        /*
	         * Показываем ТОЛЬКО детали
	         */
	        $this->result_type = 'parts';
        } else {
	        /*
	         * Показываем детали, если нет каталогов
	         */
	        if (count($this->search_catalog_results) > 0) {
		        $this->result_type = 'catalogs';
	        } else {
		        $this->result_type = 'parts';
	        }
        }
        
        
        /*
         * Каталоги
         */
        if ($this->result_type == 'catalogs') {
        	/*
             * Вывод отладочной информации.
             */
            LinemediaAutoDebug::add('Catalogs found', false, LM_AUTO_DEBUG_WARNING);
            
           
            /*
             * Проверим на пустые значения
             */
            foreach ($this->search_catalog_results as $y => $catalog) {
                $this->search_catalog_results[$y]['title'] = $catalog['title'] ? $catalog['title'] : '-';
                $this->search_catalog_results[$y]['brand_title'] = $catalog['brand_title'] ? $catalog['brand_title'] : '-';
            }
            
            /*
             * Уберём повторяющиеся значения
             */
            $this->search_catalog_results = self::getIntersectCatalogs($this->search_catalog_results);
            

            /*
             * В некоторых случаях может оказаться, что каталог всего один(или его вообще нет)
             * Тогда надо сразу показать артикулы
             */
            if (count($this->search_catalog_results) <= 1) {
            	LinemediaAutoDebug::add('Catalogs count reduced to one, showing parts', false, LM_AUTO_DEBUG_WARNING);
            	$this->result_type = 'parts';
            }
        }
        
        /*
         * Детали
         */
        if ($this->result_type == 'parts') {
	        /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('No catalogs, parts found', print_r($this->articles_to_search, 1), LM_AUTO_DEBUG_WARNING);
            
            /*
             * Проводим непосредственно сам поиск.
             * Предполагается, что в событиях отработало подключение всех возможных поставщиков данных,
             * а потому здесь остаётся только выбрать полученные номера деталей из БД запчастей.
             */
            try {
            	/*
            	 * Показываем только активных поставщиков
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
                     * Вывод отладочной информации
                     */
                    LinemediaAutoDebug::add('Search db for art=' . $part['article'], print_r($part, true));
                    
                    /*
                     * Уберём из аналогов искомый артикул
                     */
                    if ($part['article'] == $sought_part['article'] && !$part['sought']) {
                        continue;
                    }
                    
                    $search = new $this->type();
                    
                    if ($found_local_item_list = $search->searchLocalDatabaseForPart($part, true)) {
                        foreach ($found_local_item_list as &$found_local_item) {
                            
                            /*
                             * Неактивный поставщик.
                             */
                            if ($suppliers_active[$found_local_item['supplier_id']] !== true) {
                            	continue;
                            }
                            
                            /*
                             * Из какого поставщика аналогов пришёл этот артикул?
                             */
                            $found_local_item['analog-source'] = $part['source'];
                            $found_local_item['origin'] = $part['analog-source'];
                            
                            /*
                             * Вывод отладочной информации.
                             */
                            LinemediaAutoDebug::add('Part found art=' . $part['article'] . ' with analog type=' . $part['analog_type']);
                            
                            $analog_type = (int) $part['analog_type'];
                            
                            /*
                             * Искомый артикул.
                             */
                            if ($part['sought']) {
                                /*
                                 * Вывод отладочной информации.
                                 */
                                LinemediaAutoDebug::add('Part art=' . $part['article']. ' is marked as sought-for by user');
                                $analog_type = 'N';
                            }
                            
                            /*
                             * Покажем оригинальное написание артикула.
                             */
                            $found_local_item['article'] = $found_local_item['original_article'];
                            
                            /*
                             * Передадим дополнительные параметры.
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
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('Informations result', print_r($this->result_info, true), LM_AUTO_DEBUG_WARNING);
            
            
            /*
             * Вывод отладочной информации
             */
            LinemediaAutoDebug::add('Local result', print_r($found_local_items, true), LM_AUTO_DEBUG_WARNING);
            
            
            /*
             * Объединим результат локального поиска с тем, что уже было в переменной.
             * Она может быть непустой, если туда вписаны запчасти, которых на самом деле нет локально.
             * Например Emex.
             */
            $this->search_article_results = array_merge_recursive($this->search_article_results, $found_local_items);
        
            foreach ($this->search_article_results as $i => $group) {
                foreach ($group as $j => $part) {
                    $this->search_article_results[$i][$j]['article'] = LinemediaAutoPartsHelper::clearArticle($part['article']);
                }
            }
            
            
            /*
             * Уберем одинаковые запчасти в каталогах.
             */
            $this->search_article_results = self::getIntersectParts($this->search_article_results);
            
            
            if (count($this->search_article_results) == 0) {
                /*
                 * 404
                 */
                $this->result_type = '404';
                
                /*
                 * Вывод отладочной информации
                 */
                LinemediaAutoDebug::add('No catalogs and no parts found', false, LM_AUTO_DEBUG_WARNING);
            }
        }
    
        
        /*
         * На конец поиска создаём событие
         */
        $events = GetModuleEvents("linemedia.auto", "OnSearchExecuteEnd");
        $result_type = $this->result_type;
        while ($arEvent = $events->Fetch()) {
            /*
             * Аргументы
             * Первый    - условия поиска
             * Второй    - список запчастей, которые надо искать в локально базе
             * Третий    - Каталоги, если нужно
             * Четвёртый - запчасти, которые там "как бы уже найдены" (например от emex)
             * Пятый     - тип поиска
             * Шестой    - дополнительная информация о деталях
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
         * Если после интерсекта остались пусты типы аналогов
         */
        $this->search_article_results = array_filter($this->search_article_results);
        
        
        /*
         * Применим словоформы.
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
         * Вывод отладочной информации
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
     * Получить ошибки, полученные от разных поставщиков поиска
     */
    public function getThrownExceptions()
    {
        return $this->exceptions;
    }
    
    
    /**
     * Получение уникальных запчастей из каталогов.
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
                     * Объединим extra значения
                     */
                    $new_extra = $result[$group_id][$hash]['extra'];
                    foreach ($part['extra'] as $k => $v) {
	                    if ($new_extra[$k] === $v) {
	                    	continue;
                        }
                        /*
                         * Если сливаемый ключ в первом массиве пуст, то функция
                         * array_merge_recursive просто добавит его без значения.
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
                    // Сохраним группу, в которой нашлась первая деталь
                    $hasitems[$hash] = $i;
                }
                $result[$i][$hash] = $part;
            }
        }
        
        // Выраванивание индекса.
        foreach ($result as $i => $parts) {
            $result[$i] = array_values($parts);
        }
       
        return $result;
    }
    
    
    /**
     * Получение уникальных каталогов.
     * 
     * @param array $results
     */
    protected static function getIntersectCatalogs($results)
    {
    	/*
    	 * Объединим каталоги по брендам
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
             * Нет ли нормализованной словоформы?
             */
            $brand_normalized = $wordforms->getBrandGroup($brand);
            if ($brand_normalized) {
            	$brand = $brand_normalized;
            }
            
            $item['extra']['wf_b'][] = $item['brand_title'];
            
            /*
             * Такой бренд уже есть (или словоформа совпала)
             */
            if (isset($brands[$brand])) {
            	$brands[$brand]['extra'] = array_merge_recursive((array) $brands[$brand]['extra'], (array) $item['extra']);
            	$brands[$brand]['sources'][] = $item['source'];
                
            	// Если не было названия, а здесь есть - пропишем его.
            	if ($item['title'] != '' && $brands[$brand]['title'] == '') {
            		$brands[$brand]['title'] = $item['title'];
                }
            	continue;
            }
	  		
	  		/*
	  		 * Если есть группа и мы доавляем запись - выведем нормализованное значение
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
     * Получение уникальных названий каталогов.
     * 
     * @param array $results
     */
    protected static function getIntersectCatalogTitleResults($results)
    {
        $results = array_filter($results, array('LinemediaAutoSearch', 'intersectCatalogTitleResults'));
        return $results;
    }
    
    
    /**
     * Пресечение результатов поиска, чтобы удалить одинаковые каталоги (по параметрам).
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
