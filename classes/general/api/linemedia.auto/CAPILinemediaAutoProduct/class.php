<?php

/**
 * Linemedia API
 * API module
 * Product class
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
class CAPILinemediaAutoProduct extends CAPIFrame
{

	public function __construct()
	{
		parent::__construct();
	}

	/*
	* Поиск по запчастям
	*/
    public function LinemediaAutoProduct_Add($products = array())
    {
    	/*
    	* Проверка прав доступа к функции
    	*/
    	$this->checkPermission(__METHOD__);
    	
    	
    	
    	foreach($products AS $part_data)
    	{
    		
    		$part_data['article'] = LinemediaAutoPartsHelper::clearArticle($part_data['original_article']);
    		
	    	/*
			 * Создаём объект запчасти.
			 */
			try {
			    $part = new LinemediaAutoPart(false, $part_data);
			    $id = $part->save();
			} catch (Exception $e) {
			    $this->error($e->GetMessage());
			}
		}		        
		
		return true;
    }
    
    
    /*
	* Удаление запчастей
	*/
    public function LinemediaAutoProduct_Delete($filter = array())
    {
    	/*
    	* Проверка прав доступа к функции
    	*/
    	$this->checkPermission(__METHOD__);
    	
    	$this->decodeArray($filter);
    	
    	$database = new LinemediaAutoDatabase;
    	
    	/*
         * Получаем пользовательские поля.
         */
        $lmfields = new LinemediaAutoCustomFields();
        $custom_fields = $lmfields->getFields();
    	
    	$fields = array();
    	foreach($custom_fields AS $f)
    	{
	    	$fields[] = $f['code'];
    	}
    	$fields = array_merge($fields, array('id', 'title', 'original_article', 'brand_title', 'price', 'quantity', 'group_id', 'weight', 'supplier_id', 'modified'));
    	
    	$where = array();
    	
    	foreach($fields AS $field)
    	{
	    	if(isset($filter[$field]))
	    	{
		    	$where[] = "$field = '" . $database->ForSQL($filter[$field]) . "'";
	    	}
    	}
    	
    	if(count($where) == 0)
    		throw new Exception('no filters');
    	
    	$where_str = join(' AND ', $where);
    	
        $sql = "DELETE FROM `b_lm_products` WHERE $where_str";
        
    	try {
            $database->Query($sql);
        } catch (Exception $e) {
            $this->error('Error delete parts ' . $e->GetMessage());
        }
          
		
		return true;
    }
        
}
