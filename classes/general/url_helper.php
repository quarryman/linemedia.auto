<?php


/**
 * Linemedia Autoportal
 * Main module
 * Price calculation class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);



/**
 * Класс, отвечающий за работу с заказами.
 */
class LinemediaAutoUrlHelper
{
    
    
    /**
     * Получение URL к детали или поиску
     */
    public static function getPartUrl($data = array(), $type = 'LinemediaAutoSearchSimple')
    {
        $url = '';
        
        /*
         * Событие на формирование URL
         */
        $events = GetModuleEvents("linemedia.auto", "OnBeforePartUrlCreate");
		while ($arEvent = $events->Fetch()) {
		    ExecuteModuleEventEx($arEvent, array(&$url, &$data));
	    }

        /*
         * Возможные переменные
         */
        $article        = (string) $data['article'];
        $brand_title    = (string) $data['brand_title'];
        $part_id        = (int)    $data['part_id'];
        $supplier_id    = (string) $data['supplier_id'];
        $extra          = (array)  $data['extra'];
        
	    
        /*
         * Ссылка на поиск
         */
        if ($part_id) {
            /*
             * Ссылка на конкретную запчасть
             */
            $tpl  = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PART_DETAIL_PAGE');
            $url .= str_replace(array('#PART_ID#', '#SUPPLIER_ID#'), array($part_id, $supplier_id), $tpl);
        } elseif ($article) {
            /*
             * Ссылка на поиск
             */
            $tpl = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PART_SEARCH_PAGE');
            $url .= str_replace('#ARTICLE#', $article, $tpl);
            
            /*
             * Уточнения URL
             */
            $url_params = array();
            if ($brand_title) {
                $url_params['brand_title'] = $brand_title;
            }
            if ($supplier_id) {
                $url_params['supplier_id'] = $supplier_id;
            }
            if (count($extra) > 0) {
                $url_params['extra'] = $extra;
            }
            
            if ($type == LinemediaAutoSearch::SEARCH_PARTIAL) {
                $url_params['partial'] = 'Y';
            }
            
            $url_params = count($url_params) > 0 ? '?' . http_build_query($url_params) : '';
            $url .= $url_params;
            
        } else {
            $tpl = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PART_SEARCH_PAGE');
            $url .= str_replace('#ARTICLE#', '', $tpl);
            $url = str_replace('//', '/', $url);
        }
        
        
        /*
         * Событие на формирование URL
         */
        $events = GetModuleEvents("linemedia.auto", "OnAfterPartUrlCreate");
		while ($arEvent = $events->Fetch()) {
		    ExecuteModuleEventEx($arEvent, array(&$url, $data));
	    }
	    
	    return $url;
        
    }
}
