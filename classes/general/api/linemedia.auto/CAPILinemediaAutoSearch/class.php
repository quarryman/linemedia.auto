<?php

/**
 * Linemedia API
 * API module
 * Search class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://www.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
* Основной класс модуля
*/
class CAPILinemediaAutoSearch extends CAPIFrame
{

	public function __construct()
	{
		parent::__construct();
	}

	/*
	* Поиск по запчастям
	*/
    public function LinemediaAuto_Search($query, $brand_title = '', $extra = array(), $type = false) 
    {
    	/*
    	* Проверка прав доступа к функции
    	*/
    	$this->checkPermission(__METHOD__);
    	
    	/*
		 * Создаём объект поиска.
		 */
		try {
		    $search = new LinemediaAutoSearch();
		} catch (Exception $e) {
		    $this->error($e->GetMessage());
		}
		
		/*
		 * Устанавливаем поисковый запрос.
		 */
		$search->setSearchQuery($query);
		
		/*
		 * Устанавливаем бренд.
		 */
		if ($brand_title != '') {
		    $search->setSearchCondition('brand_title', $brand_title);
		}  
		
		/*
		* Установить extra
		*/
		if (count($extra) > 0) {
		    $search->setSearchCondition('extra', $extra);
		}
		
		/*
		* Установим тип поиска
		*/
		switch($type)
		{
			case 'SEARCH_GROUP':
				$type = LinemediaAutoSearch::SEARCH_GROUP;
			break;
			case 'SEARCH_PARTIAL':
				$type = LinemediaAutoSearch::SEARCH_PARTIAL;
			break;
			default:
				$type = LinemediaAutoSearch::SEARCH_SIMPLE;
		}
		$search->setType($type);
		
		
		
		/*
		 * Выполняем запрос.
		 */
		try {
		    $search->execute();
		} catch (Exception $e) {
		    $this->error($e->GetMessage());
		}
		
		/*
		 * Ошибки от модулей.
		 */
		$modules_exceptions = $search->getThrownExceptions();
		
		/*
		 * Что пришло в ответ?
		 */
		switch ($search->getResultsType())
		{
		    case 'catalogs':
		        $catalogs = $search->getResultsCatalogs();
		        foreach ($catalogs as $id => $catalog) {
		            $catalogs[$id]['url'] = LinemediaAutoUrlHelper::getPartUrl(
		                array(
		                    'article' => $query, // (!empty($catalog['article'])) ? ($catalog['article']) : ($arParams['QUERY']),
		                    'brand_title' => $brand_title,
		                    'extra' => $extra,
		                ),
		                $type
		            );
		        }
		        
		        
		        $result = array();
		        foreach($catalogs AS $i => $catalog)
		        {
			        $this->encodeArray($catalogs[$i]['extra']);
			        
			        /*
		    		* Bitrix путает обязательность параметров
		    		* /bitrix/modules/webservice/classes/general/soap/soapcodec.php 334
		    		* Обращение # 337967
		    		* 
		    		*/
		    		foreach($catalogs[$i]['extra'] AS $y => $extra)
		    		{
			    		$catalogs[$i]['extra'][$y]['CODE'] = trim($extra['CODE']);
			    		$catalogs[$i]['extra'][$y]['VALUE'] = trim($extra['VALUE']);
		    		}
			        
			        $this->formatResponse($catalogs[$i], 'Struct_SearchCatalog');
			        
		        }
		        $catalogs = array_values($catalogs);
		        
		        $response = array(
		        	'type' => 'catalogs',
		        	'catalogs' => $catalogs,
		        	'parts' => array()
		        );
		        
		        $this->formatResponse($response, 'Struct_SearchResults');
		        
		        return $response;
		        
		    case '404':
		    	$response = array(
		        	'type' => '404',
		        	'catalogs' => array(),
		        	'parts' => array()
		        );
		    break;
		    case 'parts':
		    
		        $source_parts = $search->getResultsParts();
		        
		        /*
		         * Сортировка групп деталей.
		         */
		        asort($source_parts);
		        if (isset($source_parts['analog_type_N'])) {
		            $N['analog_type_N'] = $source_parts['analog_type_N'];
		            unset($source_parts['analog_type_N']);
		            $source_parts = array_merge_recursive($N, $source_parts);
		        }
		        
		        /*
		         * Пробежимся по запчастям и ...
		         */
		        foreach ($source_parts as $group_id => $parts) {
		            foreach ($parts as $i => $part) {
		                /*
		                 * Сформируем путь для покупки
		                 */
		                $part['part_id']        = (int) $part['id'];
		                $part['supplier_id']    = (string) $part['supplier_id'];
		                
		                $buy_url  = LinemediaAutoUrlHelper::getPartUrl($part);
		                $buy_url .= '&action=ADD2BASKET';
		                
		                $source_parts[$group_id][$i]['buy_url'] = $buy_url;
		                
		                /*
		                 * Объект запчасти
		                 */
		                $part_obj = new LinemediaAutoPart($part['id'], $part);
		                
		                /*
		                 * Посчитаем цену товара
		                 */
		                $price = new LinemediaAutoPrice($part_obj);
		                $source_parts[$group_id][$i]['price'] = (float) $price->calculate();
		                $source_parts[$group_id][$i]['currency'] = $price->getCurrency();
		                	                
		                /*
		                 * Бренд
		                 */
		                $source_parts[$group_id][$i]['brand']['title'] = $part['brand_title'];
		                
		                /*
		                 * Поставщик
		                 */
		                $supplier = new LinemediaAutoSupplier($part['supplier_id']);
		                $source_parts[$group_id][$i]['supplier'] = $supplier->getArray();
		                
		                /*
		                 * Вес
		                 */
		                $source_parts[$group_id][$i]['weight'] = (float) $parts[$group_id][$i]['weight'];
		                
		                /*
		                 * Срок доставки
		                 */
		                if (!$source_parts[$group_id][$i]['delivery_time']) {
		                    $source_parts[$group_id][$i]['delivery_time'] = (int) $supplier->get('delivery_time');
		                } else {
		                    $source_parts[$group_id][$i]['delivery_time'] += (int) $supplier->get('delivery_time');
		                }
		                
		                
		                
		                /*
		                 * URL поиска запчасти
		                 */
		                $part_search_url = LinemediaAutoUrlHelper::getPartUrl(array(
		                    'article'     => $part['article'],
		                    'brand_title' => $part['brand_title'],
		                    'extra'       => $part['extra'],
		                ));
		                $source_parts[$group_id][$i]['part_search_url'] = $part_search_url;
		                
		                
		                
		                $this->formatResponse($source_parts[$group_id][$i], 'Struct_SearchPart');
		                
		            }
		        }
		        
		        /*
		        * Переделаем массив на правильный тип данных
		        */
		        $result = array();
		        foreach($source_parts AS $group => $parts)
		        {
			        $result[] = array(
			        	'analog_type' => $group,
			        	'parts' => $parts,
			        );
		        }
		        
		        $response = array(
		        	'type' => 'parts',
		        	'parts' => $result,
		        	'catalogs' => array()
		        );
		        
		        return $response;
		        
		    break;
		    default:
		        $this->error('System error 1');
		}
		
    }
        
}
