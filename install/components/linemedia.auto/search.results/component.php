<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError(GetMessage("LM_AUTO_MODULE_NOT_INSTALL"));
    return;
}


/*
 * ��������� ������� ����������.
 */
$arParams['QUERY']          = trim(strval($arParams['QUERY']));
$arParams['BRAND_TITLE']    = trim(strval($arParams['BRAND_TITLE']));
$arParams['EXTRA']          = (array) $arParams['EXTRA'];
$arParams['PART_ID']        = intval($arParams['PART_ID']);

$arParams['HIDE_FIELDS'] = (array) $arParams['HIDE_FIELDS'];

$arParams['USE_GROUP_SEARCH'] = ($arParams['USE_GROUP_SEARCH'] != 'N');

$arParams['SHOW_SUPPLIER'] = (array) $arParams['SHOW_SUPPLIER'];

$arParams['REMAPPING'] = ($arParams['REMAPPING'] == 'Y');

$arParams['SET_TITLE']  = ($arParams['SET_TITLE'] == 'Y');

$arParams['TITLE'] = trim(strval($arParams['TITLE']));

$arParams['AUTH_URL'] = (isset($arParams['AUTH_URL'])) ? trim(strval($arParams['AUTH_URL'])) : "/auth/";

$arParams['BASKET_URL'] = (isset($arParams['BASKET_URL'])) ? trim(strval($arParams['BASKET_URL'])) : "/auto/cart/";

$arParams['VIN_URL'] = (isset($arParams['VIN_URL'])) ? trim(strval($arParams['VIN_URL'])) : "/auto/vin/";

$arParams['INFO_URL'] = (isset($arParams['INFO_URL'])) ? trim(strval($arParams['INFO_URL'])) : "/auto/part-detail/#BRAND#/#ARTICLE#/";

$arParams['GROUP'] = (count(explode(',', (string) $arParams['QUERY'])) > 1);

$arParams['PARTIAL'] = ($_REQUEST['partial'] == 'Y');

$arParams['SHOW_BLOCKS'] = (!empty($arParams['SHOW_BLOCKS'])) ? (strval($arParams['SHOW_BLOCKS'])) : ('both');

$arParams['ANTI_BOTS']  = ($arParams['ANTI_BOTS'] == 'Y' && !$USER->IsAuthorized());

$arParams['ACTION_VAR'] = (!empty($arParams['ACTION_VAR'])) ? (strval($arParams['ACTION_VAR'])) : ('act');

$arParams['SORT'] = (!empty($arParams['SORT'])) ? (strval($arParams['SORT'])) : ('price_src');

$arParams['ORDER'] = (!empty($arParams['ORDER'])) ? (strval($arParams['ORDER'])) : ('asc');

$arParams['LIMIT'] = (int) $arParams['LIMIT'];

$arParams['PATH_NOTEPAD'] = (isset($arParams['PATH_NOTEPAD'])) ? trim(strval($arParams['PATH_NOTEPAD'])) : "/auto/notepad/";


/*
 * �� ajax-�� ������ ������?
 */
$AJAX = isset($_REQUEST['ajax']);
if ($AJAX) {
    $GLOBALS['APPLICATION']->RestartBuffer();
    header('Content-type: application/json');
}


/*
 * ��� ����� � ������.
 */
$arResult = array();

$arResult['SHOW_SUPPLIER'] = (count(array_intersect(CUser::GetUserGroup(CUser::getID()), $arParams['SHOW_SUPPLIER'])) > 0);


/*
 * ����� ����������� ��� �������������������� �������������
 */
if (!$USER->IsAuthorized() && $arParams['ANTI_BOTS'] && isset($_REQUEST['SHOW_AUTH_FORM'])) {
    $APPLICATION->AuthForm(GetMessage('LM_AUTO_SEARCH_NEED_AUTH'));
}


/*
 * ������� ������.
 */
if (isset($_REQUEST[$arParams['ACTION_VAR']]) && $_REQUEST[$arParams['ACTION_VAR']] == 'ADD2BASKET') {

    /*
     * ID �������� � ��������� ��.
     */
    $part_id = (int) $_REQUEST['part_id'];
    $part = new LinemediaAutoPart($part_id);

    /*
     * ID ����������.
     * �� ���� ����� ����� ������, ��� �������� ����� �� � ��������� ��, � � �������� API.
     */
    $supplier_id = (string) $_REQUEST['supplier_id'];
    $supplier_id = ($supplier_id != '') ? $supplier_id : $part->get('supplier_id');

    /*
     * ���������� � ������.
     */
    $quantity = (int) $_REQUEST['quantity'];
    $quantity = ($quantity > 0) ? $quantity : 1;

    /*
     * �������������� ���������.
     */
    $additional = array(
        'article'       => (string) $_REQUEST['q'],
        'brand_title'   => (string) $_REQUEST['brand_title'],
        'extra'         => (array) $_REQUEST['extra'],
    );


    /*
     * ������ ����� ������ � �������.
     */
    $basket = new LinemediaAutoBasket();
    $basket_id = $basket->addItem($part_id, $supplier_id, $quantity, null, $additional);
    /*
     * �������� ���������� �������.
     */
    if (!$AJAX) {
        LocalRedirect($arParams['BASKET_URL']);
        exit();
    } else {
        die(
            json_encode(
                array(
                    'status' => 'ok',
                    'basket_id' => $basket_id,
                )
            )
        );
    }
}


/*
 * �������� � ������ ��� ���������� ���� ������.
 */
if ($arParams['QUERY']) {
    $key = $arParams['QUERY'] . $arParams['BRAND_TITLE'];
    $_SESSION['LM_AUTO_MAIN']['QUERIES'][$key] = array(
        'added' => time(),
        'title' => $arParams['QUERY'],
        'url' => LinemediaAutoUrlHelper::getPartUrl(array(
            'article' => $arParams['QUERY'], 
            'part_id' => $arParams['PART_ID'], 
            'brand_title' => $arParams['BRAND_TITLE'],
            'extra' =>  $arParams['EXTRA'], 
        )),
    );

    if ($arParams['BRAND_TITLE'] != '') {
         $_SESSION['LM_AUTO_MAIN']['QUERIES'][$key]['title'] .= ' (' . $arParams['BRAND_TITLE'] . ')';
    }
}


/*
 * ���� ��� �������.
 */
if ($arParams['QUERY'] == '' && $arParams['PART_ID'] < 1) {
    $arResult['FORM_ACTION'] = LinemediaAutoUrlHelper::getPartUrl();
    $this->IncludeComponentTemplate('default');
    return;
}


/*
 * ������ ������ ������.
 */
try {
    $search = new LinemediaAutoSearch();
} catch (Exception $e) {
    $arResult['ERRORS'][] = $e->GetMessage();
}

/*
* ��� ����� � ������?
*/
$arResult['QUERY'] = htmlspecialchars($arParams['QUERY']);


/*
 * ������������� ��������� ������.
 */
$search->setSearchQuery($arParams['QUERY']);

/*
 * ������������� �����.
 */
if ($arParams['BRAND_TITLE'] != '') {
    $search->setSearchCondition('brand_title', $arParams['BRAND_TITLE']);
}

/*
 * �������������� ��������� ��������� �������.
 */
if (count($arParams['EXTRA']) > 0) {
    $search->setSearchCondition('extra', $arParams['EXTRA']);
}

/*
 * ���� �� ����� �������� ���� ��������, �� �������� � ID � �����.
 */
if ($arParams['PART_ID'] > 0) {
    $search->setSearchCondition('id', $arParams['PART_ID']);
}

/*
 * ����������� ���� ������.
 */
$arParams['TYPE'] = LinemediaAutoSearch::SEARCH_SIMPLE;
if ($arParams['GROUP'] && $arParams['USE_GROUP_SEARCH']) {
    $arParams['TYPE'] = LinemediaAutoSearch::SEARCH_GROUP;
}
if ($arParams['PARTIAL']) {
    $arParams['TYPE'] = LinemediaAutoSearch::SEARCH_PARTIAL;
}
$search->setType($arParams['TYPE']);

/*
 * ����������� ����� ������.
 */
$arResult['FORM_ACTION'] = LinemediaAutoUrlHelper::getPartUrl();
if (in_array($arParams['SHOW_BLOCKS'], array('form', 'both'))) {
    $this->IncludeComponentTemplate('default');
}


/*
 * ����������� ������ ����������� ������.
 */
if (!in_array($arParams['SHOW_BLOCKS'], array('results', 'both'))) {
    return;
}


/*
 * ��������� ������.
 */
try {
    $search->execute();
} catch (Exception $e) {
    $arResult['ERRORS'][] = $e->GetMessage();
}

/*
 * ������ �� �������.
 */
$modules_exceptions = $search->getThrownExceptions();
foreach ($modules_exceptions as $exception) {
    $arResult['ERRORS'][] = $exception->GetMessage();
}


/*
 * ��� ������ � �����?
 */
switch ($search->getResultsType()) {
    case 'catalogs':
        $arResult['CATALOGS'] = $search->getResultsCatalogs();
        foreach ($arResult['CATALOGS'] as $id => $catalog) {
            $arResult['CATALOGS'][$id]['url'] = LinemediaAutoUrlHelper::getPartUrl(
                array(
                    'article' => $arParams['QUERY'], // (!empty($catalog['article'])) ? ($catalog['article']) : ($arParams['QUERY']),
                    'brand_title' => strtoupper($catalog['brand_title']),
                    'extra' => $catalog['extra'],
                ),
                $arParams['TYPE']
            );
        }

        ksort($arResult['CATALOGS']);


        if ($AJAX) {
            die(json_encode(array(
                'type' => 'catalogs',
                'data' => $arResult['CATALOGS'],
            )));
        }
        
        $this->IncludeComponentTemplate('catalogs');
        break;
        
    case '404':
    case 'parts':

        $arResult['PARTS'] = $search->getResultsParts();
        
        /*
         * ���������� � �������.
         */
        $info = $search->getResultInfo();
        
        /*
         * ���������� � ������� � TecDoc.
         */
        $tecdoc_brands = LinemediaAutoBrand::getTecdocBrands();
        
        /*
         * ��������� � ������� ���� ������� � ���������.
         */
        foreach ($arResult['PARTS'] as $group_id => $parts) {
            foreach ($parts as $i => $part) {
                $arResult['PARTS'][$group_id][$i]['brand_title'] = strtoupper($part['brand_title']);
                $arResult['PARTS'][$group_id][$i]['article']     = LinemediaAutoPartsHelper::clearArticle($part['article']);
            }
        }
        
        
        /*
         * ���������� ����� �������.
         */
        asort($arResult['PARTS']);
        if (isset($arResult['PARTS']['analog_type_N'])) {
            $N['analog_type_N'] = $arResult['PARTS']['analog_type_N'];
            unset($arResult['PARTS']['analog_type_N']);
            $arResult['PARTS'] = array_merge_recursive($N, $arResult['PARTS']);
        }
        
        /*
         * ����������� �� ����������.
         */
        if ($arParams['LIMIT'] > 0) {
            foreach ($arResult['PARTS'] as $group_id => $parts) {
                $arResult['PARTS'][$group_id] = array_slice($parts, 0, $arParams['LIMIT']);
            }
        }
        
        
        /*
         * ���������� �� ��������� � ...
         */
        foreach ($arResult['PARTS'] as $group_id => $parts) {
            
            foreach ($parts as $i => $part) {
                /*
                 * ���������� ���� ��� �������
                 */
                $part['part_id']        = (int) $part['id'];
                $part['supplier_id']    = (string) $part['supplier_id'];
                
                $buy_url  = LinemediaAutoUrlHelper::getPartUrl($part);
                $buy_url .= '&'.$arParams['ACTION_VAR'].'=ADD2BASKET';
                
                $arResult['PARTS'][$group_id][$i]['buy_url'] = $buy_url;
                
                /*
                 * ������ ��������
                 */
                $part_obj = new LinemediaAutoPart($part['id'], $part);
                
                /*
                 * ��������� ���� ������
                 */
                $price = new LinemediaAutoPrice($part_obj);
                $price_calc = $price->calculate();
                $arResult['PARTS'][$group_id][$i]['price_src'] = $price_calc;
                $arResult['PARTS'][$group_id][$i]['price'] = CurrencyFormat($price_calc, $price->getCurrency()); //$price->calculate() . ' ' . $price->getCurrency();
                
                /*
                 * ��� ������� ������� ���� ������ � ����
                 * ���� �� ����� ��� ������� �� �����������
                 */
                $arResult['PARTS'][$group_id][$i]['buy_url'] .= '&p='.$price_calc;
                
                
                /*
                 * �����
                 */
                $arResult['PARTS'][$group_id][$i]['brand']['title'] = $part['brand_title'];
                
                /*
                 * ���������
                 */
                $supplier = new LinemediaAutoSupplier($part['supplier_id']);
                $arResult['PARTS'][$group_id][$i]['supplier'] = $supplier->getArray();
                
                $arResult['PARTS'][$group_id][$i]['supplier_title'] = $arResult['PARTS'][$group_id][$i]['supplier']['PROPS']['visual_title']['VALUE'];
                
                /*
                 * ���
                 */
                $arResult['PARTS'][$group_id][$i]['weight'] = (float) $arResult['PARTS'][$group_id][$i]['weight'];
                
                /*
                 * ���� ��������
                 */
                if (!$arResult['PARTS'][$group_id][$i]['delivery_time']) {
                    $arResult['PARTS'][$group_id][$i]['delivery_time'] = (int) $supplier->get('delivery_time');
                } else {
                    $arResult['PARTS'][$group_id][$i]['delivery_time'] += (int) $supplier->get('delivery_time');
                }
                $arResult['PARTS'][$group_id][$i]['delivery'] = $arResult['PARTS'][$group_id][$i]['delivery_time'];
                
                /*
                 * ����������� � ���
                 */
                $delivery_time = $arResult['PARTS'][$group_id][$i]['delivery_time'];
                if ($delivery_time >= 24) {
                    $days = round($delivery_time / 24);
                    $delivery_time = '&asymp; ' . $days . ' ' . GetMessage('LM_AUTO_MAIN_DAYS');
                } else {
                    $delivery_time .= ' ' . GetMessage('LM_AUTO_MAIN_HOURS');
                }
                $arResult['PARTS'][$group_id][$i]['delivery_time'] = $delivery_time;
                
                /*
                 * ����
                 */
                
                $arResult['PARTS'][$group_id][$i]['info'] = false;
                if (array_key_exists($part['brand_title'], $tecdoc_brands)) {
                    $arResult['PARTS'][$group_id][$i]['info'] = true;
                    
                    // �������� ���� ���������� �� article_id.
                    $arResult['PARTS'][$group_id][$i]['article_id'] = $info[$part['brand_title']][$part['article']]['tecdoc']['article_id'];
                }
                
                
                /*
                 * URL �������� � �����������.
                 */
                $part_info_url = str_replace(
                    array('#BRAND#', '#ARTICLE#'), 
                    array($part['brand_title'], $part['article']), 
                    $arParams['INFO_URL']
                );
                $arResult['PARTS'][$group_id][$i]['part_info_url'] = $part_info_url;
                
                /*
                 * URL ������ ��������
                 */
                $part_search_url = LinemediaAutoUrlHelper::getPartUrl(array(
                    'article'     => $part['article'],
                    'brand_title' => $part['brand_title'],
                    'extra'       => $part['extra'],
                ));
                
                $arResult['PARTS'][$group_id][$i]['part_search_url'] = $part_search_url;
                
                
                
                /*
                 * �������� ��� ���������.
                 */
                if ($arParams['ANTI_BOTS']) {
                    $arResult['PARTS'][$group_id][$i]['article']            = str_pad(substr($part['article'], 0, 2), strlen($part['article']), '*');
                    $arResult['PARTS'][$group_id][$i]['original_article']   = str_pad(substr($part['original_article'], 0, 2), strlen($part['original_article']), '*');
                    $arResult['PARTS'][$group_id][$i]['part_info_url']      = 'javascript:void(0)';
                    $arResult['PARTS'][$group_id][$i]['part_search_url']    = 'javascript:void(0)';
                    $arResult['PARTS'][$group_id][$i]['buy_url']            = $APPLICATION->GetCurPageParam('SHOW_AUTH_FORM=1');
                    
                    unset($arResult['PARTS'][$group_id][$i]['info']);
                    unset($arResult['PARTS'][$group_id][$i]['article_id']);
                }
            }
        }
        
        /*
         * ���������� ���������.
         */
        $arResult['PARTS'] = LinemediaAutoPartsHelper::sorting($arResult['PARTS'], $arParams['SORT'], $arParams['ORDER']);
        
        /*
         * ���������� �� ����
        foreach ($arResult['PARTS'] as $group_id => $parts) {
            usort($arResult['PARTS'][$group_id], 'linemediaPriceSort');
        }
        */
        
        if ($AJAX) {
            foreach ($arResult['PARTS'] as $group => $parts) {
                foreach ($parts as $i => $part) {
                    unset($arResult['PARTS'][$group][$i]['supplier']);
                    $arResult['PARTS'][$group][$i]['supplier']['PROPS']['visual_title']['VALUE'] = $part['supplier']['PROPS']['visual_title']['VALUE'];
                }
            }
            die(json_encode(array(
                'type' => 'parts',
                'data' => $arResult['PARTS'],
            )));
        }
        $this->IncludeComponentTemplate('parts');
        break;
    
    /*
     * � ���������� � 404 ����� ������ �������� �� ajax
     */
    /*case '404':
        
        if ($AJAX) {
            die(json_encode(array(
                'type' => '404',
            )));
        }
        
        $this->IncludeComponentTemplate('404');
        break;
    */
    default:
        if ($AJAX) {
            die(json_encode(array(
                'type' => 'errors',
            )));
        }
        $this->IncludeComponentTemplate('errors');
}


/*
 * ������������� ��������� ��������.
 */
if ($arParams['SET_TITLE']) {
    /*
     * ��� ���������� ��������� �������� ������� ������ ����������.
     */
    if ($arParams['PART_ID'] > 0) {
        $part = $arResult['PARTS']['N'][0];
        $TITLE = str_replace('#QUERY#', $part['title'] . ' ' . $part['brand_title'] . ' ' .$part['article'], $arParams['TITLE']);
    } else {
        $TITLE = str_replace('#QUERY#', $arParams['QUERY'], $arParams['TITLE']);
        if ($arParams['BRAND_TITLE'] != '') {
            $TITLE .= ' (' . $arParams['BRAND_TITLE'] . ')';
        }
    }
    $APPLICATION->SetTitle($TITLE);
}



/*
 * ������� ������� ���������� Linemedia
 */
if ($arParams['DISABLE_STATS'] != 'Y') {
    if($arParams['QUERY'] !== '' && $arParams['BRAND_TITLE'] != '')
        $APPLICATION->AddHeadScript('http://api.auto.linemedia.ru/api.js?article=' . urlencode($arParams['QUERY']) . '&brand_title=' . urldecode($arParams['BRAND_TITLE']));
    else
        $APPLICATION->AddHeadScript('http://api.auto.linemedia.ru/api.js');
}



if (!function_exists('linemediaPriceSort')) {
    function linemediaPriceSort($a, $b)
    {
        if ($a['price_src'] == $b['price_src']) {
            return 0;
        }
        return (int) $a['price_src'] > (int) $b['price_src'] ? 1 : -1;
    }
}