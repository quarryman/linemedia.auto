<?php
/*
 * компонент выводит автокаталог текдока из нашего API
 */
 
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTOPORTAL_MODULE_NOT_INSTALL"));
    return;
}

if (!CModule::IncludeModule("iblock")) {
    ShowError('MODULE IBLOCK NOT INSTALL');
    return;
}

if (empty($arParams['SEF_FOLDER'])) {
    $arParams['SEF_FOLDER'] = "/auto/tecdoc/";
}

if (empty($arParams['DETAIL_URL'])) {
    $arParams['DETAIL_URL'] = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DEMO_FOLDER', '/auto/').'part-detail/#ARTICLE_ID#/#ARTICLE_LINK_ID#/';
}

if (empty($arParams['SHOW_ORIGINAL_ITEMS'])) {
    $arParams['SHOW_ORIGINAL_ITEMS'] = 'Y';
}

if (empty($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 3600;
}


$arParams['AJAX_BUY'] = ($arParams['AJAX_BUY'] == 'Y' ? 'Y' : 'N');
$arParams['ADD_SEO_DATA'] = ($arParams['ADD_SEO_DATA'] == 'N' ? 'N' : 'Y');

//BEGIN ПРАВА ДОСТУПА ПО РАЗДЕЛАМ
function GetAccessTecDoc($sSection = '', $sFilter = '', $bIncludeRoot = false)
{
    $sLM_AUTOPORTAL_TECDOC_ACCESS_LIST_IBLOCK_ID = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_TECDOC_ACCESS_LIST', false);
    $oEl = new CIBlockElement();
    $aRes = Array();
    if (!empty($sSection)) {
        $aFilter = Array(
                'IBLOCK_ID' => $sLM_AUTOPORTAL_TECDOC_ACCESS_LIST_IBLOCK_ID,
                'PROPERTY_API_SECTION' => $sSection,
                                'PROPERTY_COMPONENT' => 'LM_TECDOC_CATALOG'
                );
        
        if (!empty($sFilter)) {
            $aFilter['PROPERTY_API_ID'] = $sFilter . '%';
        }
        
        if ($bIncludeRoot === true) {
            if (!empty($sFilter)) {
                unset( $aFilter['PROPERTY_API_ID'] );
                $aFilter[] = Array(
                           'LOGIC' => 'OR',
                           Array('PROPERTY_API_ID' => $sFilter . '%'),
                           Array('PROPERTY_API_ID' => '/%'),
                           );

            } else {
                $aFilter['PROPERTY_API_ID'] = '/%';
            }
        }
        
        $oItemsRes = $oEl->GetList(Array(),
                       $aFilter,
                       false,
                       false,
                       Array(
                         'ID',
                         'IBLOCK_ID',
                         'PROPERTY_API_ID'
                         )
                       );
        while($aAItem = $oItemsRes->Fetch()){
            if(!empty($aAItem['PROPERTY_API_ID_VALUE'])){
                $aRes[$aAItem['PROPERTY_API_ID_VALUE']] = $aAItem['ID'];
            }
        }
        unset($aAItem, $oItemsRes, $aFilter);
        unset($sLM_AUTOPORTAL_TECDOC_ACCESS_LIST_IBLOCK_ID);
    }
    
    return $aRes;
}
//END ПРАВА ДОСТУПА ПО РАЗДЕЛАМ


// Массив шаблонов путей
/*
 * Праметр который указвает на то что урл в тектоке меняется ("#BRAND#/#MODEL_GROUP#/...")
 * Если нет, тогда стандартный ("#BRAND#/#MODEL#/...")
 */
$is_new_url = (isset($arParams['TECDOC_NEW_URL']) && $arParams['TECDOC_NEW_URL'] === 'Y') ? true : false;
if (isset($_GET['from']) && $_GET['from'] == 'garage') {
    $is_new_url = false;
    $arParams['TECDOC_NEW_URL'] = 'N';
}
if ($is_new_url) {
    $arUrlTemplates = array(
        "list" => "index.php",
        "brand" => "#BRAND#/",
        "model_group" => "#BRAND#/#MODEL_GROUP#/",
        "model" => "#BRAND#/#MODEL_GROUP#/#MODEL#/",
        "article_link_id" => "detail-info/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",
        "car_id" => "#BRAND#/#MODEL_GROUP#/#MODEL#/#CAR_ID#/",
        "group_id" => "#BRAND#/#MODEL_GROUP#/#MODEL#/#CAR_ID#/#GROUP_ID#/",
        "dud" => "#BRAND#/#MODEL_GROUP#/#MODEL#/#CAR_ID#/#GROUP_ID#/#DUD#/", /*такая страница не предусмотрена, но есть куча вумников, которые подтирают куски url.*/
        //"article_link_id" => "#BRAND#/#MODEL_GROUP#/#MODEL#/#CAR_ID#/#GROUP_ID#/#ARTICLE_ID#/#ARTICLE_LINK_ID#/"
    );
    $arResult['TECDOC_NEW_URL'] = 'Y';
} else {
    $arUrlTemplates = array(
        "list" => "index.php",
        "brand" => "#BRAND#/",
        "model" => "#BRAND#/#MODEL#/",
        "article_link_id" => "detail-info/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",
        "car_id" => "#BRAND#/#MODEL#/#CAR_ID#/",
        "group_id" => "#BRAND#/#MODEL#/#CAR_ID#/#GROUP_ID#/",
        "dud" => "#BRAND#/#MODEL#/#CAR_ID#/#GROUP_ID#/#DUD#/", /*такая страница не предусмотрена, но есть куча вумников, которые подтирают куски url.*/
        //"article_link_id" => "#BRAND#/#MODEL#/#CAR_ID#/#GROUP_ID#/#ARTICLE_ID#/#ARTICLE_LINK_ID#/"
    );
    $arResult['TECDOC_NEW_URL'] = 'N';
}

$arVariables = array();


/*
 * Обработка адресов.
 */
$url  = $APPLICATION->GetCurPage(true);

$page = CComponentEngine::ParseComponentPath($arParams['SEF_FOLDER'], $arUrlTemplates, $arVariables, $url);

/*
 * Если $page === false, то у нас ни один из $arUrlTemplates не подошёл.
 * Будем считать, что это возможно в случае, когда у нас нет завершающего запрос слеша.
 * Поэтому редиректим на страницу со слешем на конце (но только в случае включённого чпу).
 */
if ($page == false && $arParams['SEF_MODE'] == 'Y') {
    $uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT);
    $path = parse_url($uri, PHP_URL_PATH);
    $path = str_replace('index.php', '', $path);
    if (strrpos($path, '/') != strlen($path)-1) {
        $q = parse_url($uri, PHP_URL_QUERY);
        if (strlen($q)) {
            LocalRedirect($path.'/?'.$q, 1, '301 Moved Permanently');
        } else {
            LocalRedirect($path.'/', 1, '301 Moved Permanently');
        }
        return;
    }
} else {
    /*
     * После удачного вызова ParseComponentPath() в $arVariables лежат полученные из шаблона пути переменные.
     * Превратим их в переменные.
     */
    extract($arVariables);
}

$oAutoportal = new LinemediaAutoApiDriver();

$is_brand_car_name = (isset($arParams['SHOW_CAR_BRANDS_IN_URI']) && $arParams['SHOW_CAR_BRANDS_IN_URI'] == 'Y') ? true : false;
if (isset($BRAND) && trim($BRAND) != '') {
    if ($is_brand_car_name) {
        $brand_nameRes = $oAutoportal->query('getBrandId', 
            $data = array('brand_title' => $BRAND), 
            $in = 'serialized'
        );
        if (is_array($brand_nameRes)) {
            if (isset($brand_nameRes['status']) && $brand_nameRes['status'] == 'ok') {
                $arResult['brand_id'] = intval($brand_nameRes['data']);
                $arResult['brand_name'] = strval($BRAND);
                $BRAND = $arResult['brand_id'];
            }
        }
    } else {
        $arResult['brand_id'] = intval($BRAND);
    }
    
    if (!$arResult['brand_id'] || !preg_match('/^\d+$/', $BRAND)) {
        CHTTP::SetStatus("404 Not Found");
    }
}       

if (isset($MODEL_GROUP)) {
    $arResult['model_group'] = (string) $MODEL_GROUP;
}

if (isset($MODEL) && trim($MODEL) != '') {
    $arResult['model_id'] = intval($MODEL);
    if (!$arResult['model_id'] || !preg_match('/^\d+$/', $MODEL)) {
        CHTTP::SetStatus("404 Not Found");
    }
}       

if (isset($CAR_ID) && trim($CAR_ID) != '') {
    $arResult['car_id'] = intval($CAR_ID);
    if (!$arResult['car_id'] || !preg_match('/^\d+$/', $CAR_ID)) {
        CHTTP::SetStatus("404 Not Found");
    }
}   
 
if (isset($GROUP_ID) && trim($GROUP_ID) != '') {
    $arResult['group_id'] = intval($GROUP_ID);
    if (!$arResult['group_id'] || !preg_match('/^\d+$/', $GROUP_ID)) {
        CHTTP::SetStatus("404 Not Found");
    }
} else {
    $arResult['group_id'] = null;
}

if (isset($DUD) && trim($DUD) != '') {
    $arResult['dud'] = intval($DUD);
    if (!$arResult['article_id'] || !preg_match('/^\d+$/', $ARTICLE_ID)) {
        CHTTP::SetStatus("404 Not Found");
    }
}
    
if (isset($ARTICLE_ID) && trim($ARTICLE_ID) != '') {
    $arResult['article_id'] = intval($ARTICLE_ID);
    if (!$arResult['article_id'] || !preg_match('/^\d+$/', $ARTICLE_ID)) {
        CHTTP::SetStatus("404 Not Found");
    }
}

if (isset($ARTICLE_LINK_ID) && trim($ARTICLE_LINK_ID) != '') {
    $arResult['article_link_id'] = intval($ARTICLE_LINK_ID);
    if (!$arResult['article_link_id'] || !preg_match('/^\d+$/', $ARTICLE_LINK_ID)) {
        CHTTP::SetStatus("404 Not Found");
    }
}

$additional_url = ($is_new_url) ? $arResult['model_group'] . '/' : '';
$brand = ($is_brand_car_name) ? strtolower($arResult['brand_name']) : $arResult['brand_id'];
   
/*
 *  Хлебные крошки.
 */
if ($arParams['ADD_SECTIONS_CHAIN'] == 'Y') {
    $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_TITLE_CATALOG'));
    $APPLICATION->AddChainItem(GetMessage('LM_AUTOPORTAL_ALL_MARKS'), $arParams['SEF_FOLDER']);
    
    // Бренд.
    if (!empty($arResult['brand_id'])) {
        $brand_nameRes = $oAutoportal->query('getBrandNameById',
            $data = array('brand_id' => $arResult['brand_id']),
            $in = 'serialized'
        );
        $brand_name = $brand_nameRes['data'];
        $APPLICATION->AddChainItem($brand_name, $arParams['SEF_FOLDER'].$brand.'/');
        $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_CATALOG_FOR').' '.$brand_name);
        
        // Группа моделей.
        if (!empty($arResult['model_group']) || !$is_new_url) {
            if ($arParams['TECDOC_NEW_URL'] == 'Y') {
                $APPLICATION->AddChainItem(strtoupper($arResult['model_group']), $arParams['SEF_FOLDER'].$brand.'/'.$additional_url);
                $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_CATALOG_FOR').' '.$brand_name . ' ' . ucfirst($arResult['model_group']));
            }
            
            // Группа модель.
            if (!empty($arResult['model_id'])) {
                $model_nameRes = $oAutoportal->query('getVehicleModelNameById',
                                     $data = array('brand_id'=>$arResult['brand_id'],
                                               'model_id'=>$arResult['model_id']),
                                     $in = 'serialized');
                $model_name = $model_nameRes['data'];
                $APPLICATION->AddChainItem($model_name, $arParams['SEF_FOLDER'].$brand.'/'.$additional_url.$arResult['model_id'].'/');
                $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_CATALOG_FOR').' '.$brand_name . ' ' . $model_name);
                
                // Модификация.
                if (!empty($arResult['car_id'])) {
                    $type_nameRes = $oAutoportal->query('getModelVariantNameById',
                                        $data = array('brand_id'=>$arResult['brand_id'],
                                                  'model_id'=>$arResult['model_id'],
                                                  'car_id'=>$arResult['car_id']),
                                        $in = 'serialized');
                    $type_name = $type_nameRes['data'];
                    $APPLICATION->AddChainItem($type_name, $arParams['SEF_FOLDER'].$brand.'/'.$additional_url.$arResult['model_id'].'/'.$arResult['car_id'].'/');
                    $APPLICATION->SetTitle(GetMessage('LM_AUTOPORTAL_CATALOG_FOR').' '. $brand_name . ' ' . $model_name . ' ' . $type_name);
                    
                    // Группа запчастей.
                    if (!empty($arResult['group_id'])) {
                        $group_nameRes = $oAutoportal->query('getGroupNameById',
                                             $data = array('type_id'=>$arResult['car_id'],
                                                       'group_id'=>$arResult['group_id']),
                                             $in = 'serialized');
                        $group_name = $group_nameRes['data'];
                        $APPLICATION->AddChainItem($group_name, 
                                                    $arParams['SEF_FOLDER'].$brand.'/'.$additional_url.$arResult['model_id'].'/'.$arResult['car_id'].'/'.$arResult['group_id'].'/');
                        $APPLICATION->SetTitle($group_name . ' ' . $brand_name . ' ' . $model_name . ' ' . $type_name);
                    }
                }
            }
        }
    }
}



if(!function_exists('brands_sort'))
{
	function brands_sort($a, $b) {
		if(!isset($a['sort']) && !isset($b['sort']))
		{
			if ($a['manuName'] == $b['manuName']) {
		        return 0;
		    }
		    return ($a['manuName'] < $b['manuName']) ? -1 : 1;
		}
		
	    if ($a['sort'] == $b['sort']) {
	        return 0;
	    }
	    return ($a['sort'] < $b['sort']) ? -1 : 1;
	}
}




$sCacheID = 'tecdoc_auto_catalog_' . serialize($arVariables) . serialize($USER->GetGroups()) . $_REQUEST['sort'].'|'.$_REQUEST['dir'];
if ($this->StartResultCache(false, $sCacheID)) {

    // SEO BLOCK
    $sURLQuery = trim($_SERVER['REQUEST_URI']);
    if ($arParams['ADD_SEO_DATA'] == 'Y' && strlen($sURLQuery) > 0) {
        $iSEOTecDocIBlockID = COption::GetOptionString('linemedia.auto', 'LM_AUTOPORTAL_TECDOC_SEO_IBLOCK_ID');
        if (intval($iSEOTecDocIBlockID) > 0 && IsModuleInstalled('iblock')) {
            CModule::IncludeModule('iblock');
            $aSEOData = CIBlockElement::GetList(Array('SORT' => 'DESC'), Array(
                                                                                'IBLOCK_ID' => intval($iSEOTecDocIBlockID), 
                                                                                'PROPERTY_URL' => $sURLQuery), 
                                                                                false, 
                                                                                false, 
                                                                                Array('ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_URL', 'PROPERTY_TITLE', 'PROPERTY_H1', 'PROPERTY_DESCRIPTION', 'PROPERTY_TEXT'))->Fetch();
            if ($aSEOData !== false) {
                if (isset($aSEOData['PROPERTY_TITLE_VALUE']) && !empty($aSEOData['PROPERTY_TITLE_VALUE'])){
                    $APPLICATION->SetTitle(trim($aSEOData['PROPERTY_TITLE_VALUE']));
                    $arResult['SEO']['TITLE'] = trim($aSEOData['PROPERTY_TITLE_VALUE']);
                }
                if (isset($aSEOData['PROPERTY_DESCRIPTION_VALUE']) && !empty($aSEOData['PROPERTY_DESCRIPTION_VALUE'])){
                    $APPLICATION->SetPageProperty("description", trim($aSEOData['PROPERTY_DESCRIPTION_VALUE']));
                    $arResult['SEO']['DESCRIPTION'] = trim($aSEOData['PROPERTY_DESCRIPTION_VALUE']);
                }
                if (isset($aSEOData['PROPERTY_H1_VALUE']) && !empty($aSEOData['PROPERTY_H1_VALUE'])){
                    $APPLICATION->SetPageProperty("ADDITIONAL_TITLE", trim($aSEOData['PROPERTY_H1_VALUE']));
                    $arResult['SEO']['H1'] = trim($aSEOData['PROPERTY_H1_VALUE']);
                }
                if (isset($aSEOData['PROPERTY_TEXT_VALUE']['TEXT']) && !empty($aSEOData['PROPERTY_TEXT_VALUE']['TEXT'])){
                    if($aSEOData['PROPERTY_TEXT_VALUE']['TYPE'] == 'text'){
                        $aSEOData['PROPERTY_TEXT_VALUE']['TEXT'] = nl2br($aSEOData['PROPERTY_TEXT_VALUE']['TEXT']);
                    }
                    $arResult['SEO']['TEXT'] = trim($aSEOData['PROPERTY_TEXT_VALUE']['TEXT']);
                }
            }
            unset($aSEOData);
        }
        unset($iSEOTecDocIBlockID);
    }
    unset($sURLQuery);
    // END SEO BLOCK

    if (empty($arResult['brand_id'])) {
        /*
         * Выборка производителей.
         */
        try {
            $aBrandRes = $oAutoportal->query('getBrands', $data = array(), $in = 'serialized');
            
                        
            /*
            * Применим сортировку
            */
            uasort($aBrandRes['data'], 'brands_sort');
            
            
            $arResult['BRANDS'] = $aBrandRes['data'];
            
            $arResult['BRANDS_BY_LETTER_COUNT'] = array();
            foreach ($arResult['BRANDS'] as &$val) {
                $logo = '/upload/linemedia.auto/images/brands/'.str_replace(array(' '), array('_'), $val['manuName']).'.png';
                if (is_readable($_SERVER['DOCUMENT_ROOT'].$logo)) {
                    $val['LOGO'] = $logo;
                }
                if (!isset($arResult['BRANDS_BY_LETTER_COUNT'][ $val['manuName']{0} ])) {
                    $arResult['BRANDS_BY_LETTER_COUNT'][$val['manuName']{0}] = 1;
                } else {
                    $arResult['BRANDS_BY_LETTER_COUNT'][$val['manuName']{0}]++;
                }
            }
            $arResult['ACCESS_LIST_DISABLE'] = GetAccessTecDoc('BRANDS');
        } catch (Exception $e) {
            $arResult['ERRORS'] []= $e->GetMessage();
        }
        
    } elseif ((!empty($arResult['brand_id'])) && (empty($arResult['model_id'])) && (empty($arResult['model_group']))) {
        
        /*
         * Выборка группы моделей.
         */
        $aModelRes = $oAutoportal->query('getVehicleModels', $data = array('brand_id'=>$arResult['brand_id']), $in = 'serialized');
        if ($is_new_url) {
            $arResult['MODEL_GROUPS'] = Array();
            foreach($aModelRes['data'] as $key=>$value){
                if (preg_match('/^\S+/i', $value['modelname'], $aMName)){
                    $arResult['MODEL_GROUPS'][$aMName[0]] = ((isset($brand_name))?$brand_name . ' ':'') . $aMName[0];
                }
            }
            $arResult['ACCESS_LIST_DISABLE'] = GetAccessTecDoc('MODEL_GROUPS', $arResult['brand_id'] . ';');
        } else {
            $arResult['MODELS'] = Array();
            $arResult['MODELS'] = $aModelRes['data'];
            $arResult['ACCESS_LIST_DISABLE'] = GetAccessTecDoc('MODELS', $arResult['brand_id'] . ';');
        }
    
    } elseif ((!empty($arResult['brand_id'])) && (empty($arResult['model_id'])) && (!empty($arResult['model_group']))) {
        
        /*
         * Выборка моделей из указанной группы.
         */
        try {
            $aModelRes = $oAutoportal->query('getVehicleModels',
                $data = array('brand_id' => $arResult['brand_id']),
                $in = 'serialized'
            );
            
            $arResult['MODEL_GROUPS_ORIGINAL'] = array();
            foreach ($aModelRes['data'] as $key => $value) {
                if (preg_match('/^\S+/i', $value['modelname'], $aMName)) {
                    $arResult['MODEL_GROUPS_ORIGINAL'][strtoupper($aMName[0])] = ((isset($brand_name)) ? $brand_name . ' ':'') . $aMName[0];
                }
            }
            
            $mod_gr = strtoupper($arResult['model_group']);
            if (isset($arResult['MODEL_GROUPS_ORIGINAL'][$mod_gr])) {
                unset($arResult['MODEL_GROUPS_ORIGINAL']);
            } else {
                CHTTP::SetStatus("404 Not Found");
            }   
            
            $arResult['MODELS'] = array();
            foreach ($aModelRes['data'] as $key => $value) {
                if (preg_match('/^' . $arResult['model_group'] . '+/i', $value['modelname'])){
                    $value['begin'] = substr($value['yearOfConstrFrom'], 4, 2)
                                       . '.'
                                       . substr($value['yearOfConstrFrom'], 0, 4);
                    $value['end'] = substr($value['yearOfConstrTo'], 4, 2)
                                     . '.'
                                     . substr($value['yearOfConstrTo'], 0, 4);
                    if ($value['end'] == '.') {
                        $value['end'] = GetMessage('LM_AUTOPORTAL_IN_PRODUCTION');
                    }
                    $arResult['MODELS'][] = $value;
                }
            }
            $arResult['ACCESS_LIST_DISABLE'] = GetAccessTecDoc('MODELS', $arResult['brand_id'] . ';' . $arResult['model_group'] . ';');
        
        } catch (Exception $e) {
            $arResult['ERRORS'] []= $e->GetMessage();
        }
        
    } elseif ((!empty($arResult['model_id'])) && (empty($arResult['car_id']))) {
        
        /*
         * Выборка типов.
         */
        try {
            $aModRes = $oAutoportal->query('getModelVariantsWithInfo',
                                       $data = array('brand_id'=>$arResult['brand_id'],
                                             'model_id'=>$arResult['model_id']),
                                       $in = 'serialized');
            $arResult['MODIFICATION'] = $aModRes['data'];
    
            /*
             * Заполним информацией об авто (привод/двигатель/тип конструкции и т.д.)
             */
            $ids = array();
            foreach ($arResult['MODIFICATION'] as $v) {
                $ids []= $v['carId'];
            }
        } catch (Exception $e) {
            $arResult['ERRORS'] []= $e->GetMessage();
        }
        
        try {
            $arr = $oAutoportal->query('getCarInfoByIds',
                                 $data = array('car_ids' => $ids),
                                 $in = 'serialized');
            $arResult['MODIFICATION_CARINFO'] = $arr['data'];
            $arResult['ACCESS_LIST_DISABLE'] = GetAccessTecDoc('MODIFICATION', $arResult['brand_id'] . ';' . $arResult['model_group'] . ';' . $arResult['model_id'] . ';');
        } catch (Exception $e) {
            $arResult['ERRORS'][] = $e->GetMessage();
        }
    
    } elseif ((!empty($arResult['car_id'])) && (empty($arResult['group_id']))) {
        
        try {
            $aGroupsRes = $oAutoportal->query('getListOfGroups', $data = array('type_id'=>$arResult['car_id'], 'group_id'=>0), $in = 'serialized');
            $arResult['GROUPS'] = $aGroupsRes['data'];
            foreach ($arResult['GROUPS'] as $key => $tree) {
                if ($tree['assemblyGroupNodeId'] == $this->view->Group_id) {
                    $this->view->Tree = $tree['assemblyGroupName'];
                }
            }
            $arResult['ACCESS_LIST_DISABLE'] = GetAccessTecDoc('GROUPS', $arResult['brand_id'] . ';' . $arResult['model_group'] . ';' . $arResult['model_id'] . ';' . $arResult['car_id'] . ';', true);
        } catch (Exception $e) {
            $arResult['ERRORS'] []= $e->GetMessage();
        }
        
    } elseif ((!empty($arResult['group_id'])) && (empty($arResult['article_id']))) {
    
        /*
         * Выборка деталей
         */
        try {
            $aDetRes = $oAutoportal->query(
                'getDetails',
                $data = array('type_id' => $arResult['car_id'], 'group_id' => $arResult['group_id']),
                $in = 'serialized'
            );
            $arResult['DETAILS'] = $aDetRes['data'];
    
            if ($arParams['SHOW_ORIGINAL_ITEMS'] === 'Y') {
                $arResult['OENUMBERS'] = array();
                foreach ($arResult['DETAILS'] as $key => $value) {
                    // получаем аттрибуты и оригинальные номера
                    $arResult['OENUMBERS'][] = $value['articleId'];
                }
            }
            
            $where = array('#ARTICLE_ID#','#BRAND_ID#');
            foreach ($arResult['DETAILS'] as $key => $detail) {
                $what = array(
                            str_replace(
                                array('&', '<', '>', '/'),
                                array('&amp;', '&lt;', '&gt;', '__S__'),
                                $detail['articleNo']
                            ),
                            $detail['brandNo']
                        );
                
                $findurl = LinemediaAutoUrlHelper::getPartUrl(array(
                    'article' => $detail['articleNo'],
                    //'brand_id' => $detail['brandNo'],
                    'brand_title' => $detail['brandName'],
                    'extra' => array(
                      'gid' => $detail['genericArticleId'],
                    ),
                ));
                
                $backurl = $arParams['SEF_FOLDER'].$brand.'/'.$additional_url.$arResult['model_id'].'/'.$arResult['car_id'].'/'.$arResult['group_id'].'/';
                
                $arResult['DETAILS'][$key]['detail_url']  = str_replace(array('#ARTICLE_ID#','#ARTICLE_LINK_ID#'), array($detail['articleId'], $detail['articleLinkId']), $arParams['DETAIL_URL']);
                $arResult['DETAILS'][$key]['detail_url'] .= '?backurl='.$backurl;
                
                $arResult['DETAILS'][$key]['search_url'] = $findurl; // str_replace($where, $what, $arParams['SEARCH_URL']);
            }
        } catch (Exception $e) {
            $arResult['ERRORS'] []= $e->GetMessage();
        }
        
    }

    /*
     * Условие по названию бренда, 404 ошибка
     */
    if (isset($arResult['model_group']) && !empty($arResult['model_group'])) {
        if (!isset($aModelRes['status'])) {
            $aModelRes = $oAutoportal->query('getVehicleModels', $data = array('brand_id'=>$arResult['brand_id']), $in = 'serialized');
            
            $arResult['MODEL_GROUPS_ORIGINAL'] = array();
            foreach ($aModelRes['data'] as $key => $value) {
                if (preg_match('/^\S+/i', $value['modelname'], $aMName)) {
                    $arResult['MODEL_GROUPS_ORIGINAL'][$aMName[0]] = ((isset($brand_name))?$brand_name . ' ':'') . $aMName[0];
                }
            }
            $mod_gr = strtoupper($arResult['model_group']);
            if (isset($arResult['MODEL_GROUPS_ORIGINAL'][$mod_gr])) {
                unset($arResult['MODEL_GROUPS_ORIGINAL']);  
            } else {
                CHTTP::SetStatus("404 Not Found");
            }   
        }
    }
    
    $this->IncludeComponentTemplate();
}
