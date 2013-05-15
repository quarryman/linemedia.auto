<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Linemedia Autoportal
 * Main module
 * Module events for module itself
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

class LinemediaAutoEventSelf
{

    /**
     * ��������� � ������ ������ ���������� �� Linemedia API
     */
    public function OnSearchExecuteBegin_addLinemediaApiAnalogs(&$search_conditions, &$articles_to_search, &$catalogs_to_search, &$search_article_results, $type, &$result_info)
    {
        /*
         * ���� ����� �� �� ���������� �������� - �� ���� �� ������� �����������.
         */
        if ($type != LinemediaAutoSearch::SEARCH_SIMPLE) {
            return;
        }

        /*
         * ����� ���������� ����������
         */
        LinemediaAutoDebug::add('Linemedia API search module added');

        /*
         * ������ ������� � API
         */
        $api = new LinemediaAutoApiDriver();

        /*
         * ��������� �������
         * �������� �������
         */
        $query = LinemediaAutoPartsHelper::clearArticle($search_conditions['query']);
        $api_request_args = array(
            'article' => $query
        );
        
        
        /*
         * ���� ������������ ������ ����� TecDoc � ��� ������������� ��������� �������
         */
        if ($search_conditions['brand_title']) {
            $api_request_args['brand_title'] = $search_conditions['brand_title'];
        }
        if ($search_conditions['extra']['gid']) {
            $api_request_args['generic_article_id'] = $search_conditions['extra']['gid'];
        }


        /*
         * � ��� ����� ���� ������������� ������, ���� ���������� ���������� ����� ������� � ����
         * � ����� ������ � ������ ������������ ������ genericArticleId (gid) � ������ ���� ������� (wf_b)
         */
        if (is_array($search_conditions['extra']['gid'])) {
            $gids 	= array_map('intval', $search_conditions['extra']['gid']);
            $brands = array_map('strval', $search_conditions['extra']['wf_b']);

            $api_arguments = array();
            for ($i = 0; $i < count($gids); $i++) {
                $api_request_args['generic_article_id'] = $gids[$i];
                $api_request_args['brand_title'] = $brands[$i];
                $api_arguments[] = $api_request_args;
            }
        } else {
            /*
             * �������������� ������� ���
             * �� ����� �� ������� ������� �� �� ����� ���������� ������������� �����
             * ������� ���������� ��������� ������ ��������
             */
            $api_arguments = array($api_request_args);
        }
        
        
        /*
         * ���������� ��������� ������ (������� �����)
         * ����� ����������, ����� ������� �� ����� �������� � ������
         *
         * ��������: 
         * 	������ ������ ������ � TecDoc
         *	������ ������������ ������ � TecDoc
         *	������ ������ � �� Linemedia
         *	������ ������ � ��������� ��    ----   ������������ � ������ ������� ��������
         *
         */
        $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES 			= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES', 			'Y');
        $LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL 	= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL', 'Y');
        $LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES 			= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES', 		'Y');

        foreach ($api_arguments as &$api_argument) {
            $api_argument['tecdoc_crosses'] = ($LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES == 'Y');
            $api_argument['tecdoc_crosses_original'] = ($LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL == 'Y');
            $api_argument['linemedia_crosses'] = ($LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES == 'Y');

        }
        
        /*
         * ������
         */
        try {
            $response = $api->query('getAnalogs2Multiple', $api_arguments);
        } catch (Exception $e) {
            LinemediaAutoDebug::add('Search Linemedia API:' . $e->GetMessage(), false, LM_AUTO_DEBUG_ERROR);
            return;
        }
        
        /*
         * ���� ������ �� ������
         */
        if ($response['status'] == 'error') {
            LinemediaAutoDebug::add('Linemedia API error:' . $response['error']['code'] . '('.$response['error']['error_text'].')', false, LM_AUTO_DEBUG_USER_ERROR);
            return;
        }
        
        /*
         * ��������� ��������� �������������� ������� � ���� ��������.
         * ������ ��� �� �������� ����������, � ��� �� ����� �� ����� ������ ����� �����.
         */
        $parts = array();
        $catalogs = array();
        foreach ($response['data'] as $req) {
            $parts 		= array_merge_recursive($parts, 	(array) $req['analogs']['parts']);
            $catalogs 	= array_merge_recursive($catalogs, 	(array) $req['analogs']['catalogs']);
        }
        
        $response['data'] = array(
            'parts' => $parts,
            'catalogs' => $catalogs,
        );
        
        
        /*
         * � ������ �������� ��� ������?
         */
        $api_catalogs = (array) $response['data']['catalogs'];
        
        /*
         * ���� ������� brand_title - ������ ��� ��� ��������� � �������� �������� ������
         */
        if ($search_conditions['brand_title'] != '') {
            $api_catalogs = array();
        }
        
        if (count($api_catalogs) > 0) {
            $catalogs = array();
            foreach ($api_catalogs as $catalog) {
                $catalogs []= array(
                    'title' 		=> $catalog['title'],
                    'brand_title' 	=> $catalog['brand_title'],
                    'source' 		=> $catalog['source'],
                    'analog-source' => 'linemedia-api',
                    'extra' => array(
                        'gid'  => $catalog['generic_article_id'],
                    ),
                );
                
                $catalogs = self::getIntersectCatalogs($catalogs);
            }
            
            /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('Linemedia API returned catalogs', print_r($catalogs, 1));

            $catalogs_to_search = array_merge_recursive($catalogs_to_search, $catalogs);
            return;
        }
        
                
        /*
         * ����� ��������� (������)
         */
        $analogs = array();
        $brands_cache = array();
        foreach ($response['data']['parts'] as $item) {
            $part = array(
                'title'         => $item['title'],
                'article'       => $item['article'],
                'source' 		=> $item['source'],
                'analog_type' 	=> $item['analog_type'],
                'analog-source' => 'linemedia-api',
                'extra' => array(
                    'gid'  => $item['generic_article_id'],
                ),
            );
            
            if ($item['brand_title'] != '') {
                $part['brand_title'] = $item['brand_title'];
            }
            
            /*
             * ���� ������ ������ �� TecDoc, ������ �� ��� ���� �������������� ����������.
             * ����������� ���������� �� ������ � ��������.
             */
            $brand   = strtoupper($part['brand_title']);
            $article = LinemediaAutoPartsHelper::clearArticle($part['article']);
            $result_info[$brand][$article]['tecdoc'] = array(
                'article_id'            => $item['article_id'],
                'generic_article_id'    => $item['generic_article_id']
            );
            
            $analogs []= $part;
        }
        
        /*
         * ��������� ������, ������� ��� ����, � ������
         */
        $articles_to_search = array_merge_recursive($articles_to_search, $analogs);
    }
    
    
    

    /**
     * ��������� � ���������� ������ ���������� �� ��������� ��
     */
    public function OnSearchExecuteBegin_addLocalDBData(&$search_conditions, &$articles_to_search, &$catalogs_to_search, &$search_article_results, $type)
    {
        /*
         * ����� ���������� ����������
         */
        LinemediaAutoDebug::add('Linemedia Local DB search module added');
        
        /*
         * ���� � ��� �� ����� ����� � � �� ���� ����� ������ ������������� � ����� ��������� - ���� �������� ��������
         */
        if ($search_conditions['query'] != '' && $search_conditions['brand_title'] == '') {
            /*
             * ����� ���������� ����������
             */
            LinemediaAutoDebug::add('No brand, but article exists, check for local catalogs', false, LM_AUTO_DEBUG_WARNING);
            
            /*
             * �����.
             * � ��������� $type ���������� �������� ������ �� ���� ������.
             */
            if ($type == LinemediaAutoSearch::SEARCH_GROUP) {
                return;
            }
            
            $query = LinemediaAutoPartsHelper::clearArticle($search_conditions['query']);
            
            $search = new $type();
            $result = $search->searchLocalDatabaseForPart(array('article' => $query), true);
            
            
            /*
             * ������� ��������� ��������
             * ���� ���� �������� - ������� � ���������
             * ���� ��� - �� ������� ��������, ���� �������� ����� ��� ���� � ������ �������
             */
            if (count($result) > 0) {
                // if (count($result) > 1 || count($catalogs_to_search) > 0) { // Ilya Pyatin 04.04.13 kodauto 6554V5 	tiket 3213
                $brands = array();
                $catalogs = array();
                foreach ($result as $part) {
                    $brands[$part['brand_title']] = false;
                    $catalogs[$part['brand_title']] = $part;
                }

                /*
                 * ��������� ��������� �����.
                 * ��� ���������� ������ �� ���������� �� ���������, ������� ��� ������.
                 */
                if (count($catalogs) > 0 || count($brands) > 1) {
                    $catalogs_to_search = array_merge_recursive($catalogs_to_search, $catalogs);
                    LinemediaAutoDebug::add('Local catalogs added', print_r($catalogs, 1), LM_AUTO_DEBUG_WARNING);
                }
            }
        }
    }



    /**
     * � ������� ���� ����������� ������� ����������
     */
    public function OnItemPriceCalculate_addSupplierMarkup(&$part, &$price, &$currency, &$user_id, &$date, &$debug_calculations_results)
    {
        /*
         * ������� - � ���������
         */
        $supplier_id = $part->get('supplier_id');

        /*
         * ���������� ������
         */
        static $suppliers = array();
        if (!isset($suppliers[$supplier_id])) {
            $supplier = new LinemediaAutoSupplier($supplier_id);
            $markup = (float) $supplier->get('markup');
            $suppliers[$supplier_id] = $supplier;
        } else {
            $markup = (float) $suppliers[$supplier_id]->get('markup');
        }

        $new_price = $price + ($price * ($markup / 100));

        // ������� � ������� ����������
        $debug_calculations_results[] = GetMessage('LM_AUTO_SUPPLIER_MARKUP_DEBUG', array('#MARKUP#' => $markup, '#MARKUP_VALUE#' => ($price * ($markup / 100)), '#RESULT#' => $new_price, '#SUPPLIER_ID#' => $suppliers[$supplier_id]->get('ID')));

        $price = $new_price;
    }




    /**
     * ���� �������������� � ������������ � ������� ����������
     * ����� ������ ��� � ����� ����� ����� ���� ���������
     */
    public function OnItemPriceCalculate_convertSupplierCurrency(&$part, &$price, &$currency, &$user_id, &$date, &$debug_calculations_results)
    {
        /*
         * ������� - � ���������
         */
        $supplier_id = $part->get('supplier_id');

        /*
         * ���������� ������
         */
        static $suppliers = array();
        if (!isset($suppliers[$supplier_id])) {
            $supplier = new LinemediaAutoSupplier($supplier_id);
            $suppliers[$supplier_id] = $supplier;
        }
        
        
        /*
         * ����� ����� ����������� ������ ����������
         *
         * �������� ������
         */
        $obCache = new CPHPCache();
        $life_time = 24 * 60 * 60;
        $cache_id = 'price-currencies';
        if ($obCache->InitCache($life_time, $cache_id, "/")) {
            $data = $obCache->GetVars();
            $currencies = $data['currencies'];
            $base_currency = $data['base'];
        } else {
            if (!CModule::IncludeModule('currency')) {
                LinemediaAutoDebug::add('Error price calculation, no currencies module!', false, LM_AUTO_DEBUG_ERROR);
            }

            $lcur = CCurrency::GetList(($b="name"), ($order1="asc"), LANGUAGE_ID);
            while ($lcur_res = $lcur->Fetch()) {
                $currencies[$lcur_res["CURRENCY"]] = $lcur_res;
            }

            $base_currency = CCurrency::GetBaseCurrency();

            if ($obCache->StartDataCache()) {
                $obCache->EndDataCache(array('currencies' => $currencies, 'base' => $base_currency));
            }
        }


        /*
         * ������� ������
         */
        $supplier_currency_id = $suppliers[$supplier_id]->get('currency');
        if ($supplier_currency_id !== $base_currency && $supplier_currency_id != '') {
            $new_price = $price * $currencies[$supplier_currency_id]['AMOUNT'];

            // ������� � ����������� ������
            $debug_calculations_results[] = GetMessage('LM_AUTO_SUPPLIER_CURRENCY_DEBUG', array('#AMOUNT#' => $currencies[$supplier_currency_id]['AMOUNT'], '#SUPPLIER_CUR#' => $supplier_currency_id, '#BASE_CUR#' => $base_currency)) . ' <b>' . $new_price . '</b>';
        } else {
            // ����������� ������ �� ���������
            $new_price = $price;
            $debug_calculations_results[] = GetMessage('LM_AUTO_SUPPLIER_CURRENCY_NOT_APPLIED_DEBUG');
        }

        $price = $new_price;        
    }


    /**
     * ������ ������.
     */
    public function OnItemPriceCalculate_customDiscounts(&$part, &$price, &$currency, &$user_id, &$date, &$debug_calculations_results)
    {   
        $odiscount = new LinemediaAutoCustomDiscount($part, $user_id);
        $odiscount->setUserId($user_id);
        $odiscount->setDate($date);

        $price = $odiscount->calculate($price);
        $debug_calculations_results = array_merge($debug_calculations_results, $odiscount->getDebug());
    }


    /**
     * ���������� �������� �������.
     */
    public function OnRequirementsListGet_addChecks(&$check)
    {
        $add = array();

        /*
         * ����
         */
        $add[] = array(
            'title' => GetMessage('LM_AUTO_CRONTAB'),
            'requirements' => GetMessage('LM_AUTO_CRONTAB_HOWTO'),
            'status' => (bool) LinemediaAutoImportAgent::checkCron(),
        );

        /*
         * CURL
         */
        $add[] = array(
            'title' => GetMessage('LM_AUTO_CURL'),
            'requirements' => GetMessage('LM_AUTO_CURL_HOWTO'),
            'status' => function_exists('curl_init'),
        );


        /*
         * JSON
         */
        $add[] = array(
            'title' => GetMessage('LM_AUTO_JSON'),
            'requirements' => GetMessage('LM_AUTO_CRONTAB_JSON'),
            'status' => function_exists('json_decode'),
        );


        /*
         * PHP 5.3
         */
        $add[] = array(
            'title' => GetMessage('LM_AUTO_PHP53'),
            'requirements' => GetMessage('LM_AUTO_PHP53_HOWTO'),
            'status' => version_compare(PHP_VERSION, '5.3.0') >= 0,
        );


        /*
         * ������� ����������� ������� �������
         */
        $files = (array) LinemediaAutoImportAgent::getNewFiles();
        $add[] = array(
            'title' => GetMessage('LM_AUTO_PRICELISTS_IMPORT_WAITING'),
            'requirements' => GetMessage('LM_AUTO_PRICELISTS_IMPORT_WAITING_HOWTO') . join(', ', $files),
            'status' => count($files) < 2,
        );


        $check['linemedia.auto'] = $add;
    }


    /**
     * �������� ��������� � ����� �������.
     */
    public function OnAfterBasketItemStatus_sendMessage(&$basket_id, &$status)
    {
        $basket = new LinemediaAutoBasket();

        $arData  = $basket->getData($basket_id);
        $arProps = $basket->getProps($basket_id);
        $arOrder = CSaleOrder::GetByID($arData['ORDER_ID']);
        $arUser  = CUser::GetByID($arOrder['USER_ID'])->Fetch();

        // ���� ������ � ����� ������� �� ���������� ��� ����� ������� ������.
        if (!isset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_ORDER']) || $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_ORDER'] != true) {
            $order = new LinemediaAutoOrder($arOrder['ID']);

            // �������� �������� ������.
            $arBaskets = $order->getBaskets();
            $same = true;
            foreach ($arBaskets as $arBasket) {
                $arBasketProps = $basket->getProps($arBasket['ID']);
                if ($arBasketProps['status']['VALUE'] != $status) {
                    $same = false;
                    break;
                }
            }

            // ���� ��� ������� ���������� - ������ ������ ������.
            if ($same) {
                $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_BASKET'] = true;
                CSaleOrder::StatusOrder($order->getID(), $status);
                unset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_BASKET']);
            } else {
                // ���� ��� ��������� ����� �������� �������.
                if (!isset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET']) || $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_GROUP_STATUS_BASKET'] != true) {
                    self::sendBasketItemStatusMessage($basket_id, $status);
                }
            }
        }
    }


    /**
     * �������� ��������� � ����� �������� �������.
     */
    public function OnAfterBasketStatusesChange_sendMessages(&$basket_ids, &$status)
    {
        $basket = new LinemediaAutoBasket();

        // ��������� ������ ID ������� �� ���� ������.
        $order_ids = array();
        foreach ($basket_ids as $basket_id) {
            $arData = $basket->getData($basket_id);
            if (!in_array($arData['ORDER_ID'], $order_ids)) {
                $order_ids []= (int) $arData['ORDER_ID'];
            }
        }

        // ������� �� ���� ������� � ��������� � ����� ������� ���������� �����.
        foreach ($order_ids as $order_id) {
            // ����� �����.
            $order = new LinemediaAutoOrder($order_id);

            // �������� ���������� �������.
            $arBaskets = $order->getBaskets();

            $order_basket_ids = array();
            foreach ($arBaskets as $arBasket) {
                $order_basket_ids []= (int) $arBasket['ID'];
            }

            // ���������, ��� �� ������ �� ������ ���� �������.
            $send_basket_ids = array_intersect($basket_ids, $order_basket_ids);
            sort($send_basket_ids);
            sort($order_basket_ids);

            if (count($send_basket_ids) == 1) {

                // �������� �������� ������.
                $same = true;
                foreach ($arBaskets as $arBasket) {
                    $arBasketProps = $basket->getProps($arBasket['ID']);
                    if ($arBasketProps['status']['VALUE'] != $status) {
                        $same = false;
                        break;
                    }
                }
                if ($same) {
                    return;
                }
            }

            /*
             * ���� ���������� �� ��� ������� ������� ������ - ���������� ������ �� �������.
             * � ��������� ������ ����� ������ �� ��������� ������� ������ ������.
             */
            if ($send_basket_ids != $order_basket_ids) {
                foreach ($send_basket_ids as $basket_id) {
                    self::sendBasketItemStatusMessage($basket_id, $status);
                }
            }
        }
    }

    /**
     * ������������ ������� �� ��������� ���� ��� ��������� ���������� ����� ������� �������
     */

    public function OnAfterPriceListAllImport_UpdateCatalogPrices($files_count, $files)
    {
        CModule::IncludeModule("iblock");
        CModule::IncludeModule("catalog");

        //���������� ��������
        $iblocks_id = unserialize(COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES'));

        //����������� ������� ����
        $ar_base_price = CCatalogGroup::GetBaseGroup();
        $price_type_id = $ar_base_price['ID'];

        $search = new LinemediaAutoSearchSimple();

        foreach ($iblocks_id as $iblock_id) {
            //�������� �������� ��������
            $rsDetails = CIBlockElement::GetList(
                array(),
                array('IBLOCK_ID' => $iblock_id),
                false,
                false,
                array('ID', 'PROPERTY_ARTICLE', 'PROPERTY_BRAND_TITLE', 'PROPERTY_ARTNUMBER', 'PROPERTY_MANUFACTURER')
            );

            //������ ��������
            while ($detail = $rsDetails -> Fetch()) {

                //��������� �� ������� ����� 'article' � 'brand_title'
                if ( (!empty($detail["PROPERTY_ARTICLE_VALUE"]) && !empty($detail["PROPERTY_BRAND_TITLE_VALUE"])) || (!empty($detail["PROPERTY_ARTNUMBER_VALUE"]) && !empty($detail["PROPERTY_MANUFACTURER_VALUE"])) ){

                    $detail['article'] = $detail["PROPERTY_ARTICLE_VALUE"] ?: $detail["PROPERTY_ARTNUMBER_VALUE"];
                    $detail['brand_title'] = $detail["PROPERTY_BRAND_TITLE_VALUE"] ?: $detail["PROPERTY_MANUFACTURER_VALUE"];

                    //������ ��������� �������� � ��������� ����
                    $parts = (array) $search->searchLocalDatabaseForPart(array(
                        'article' => $detail['article'],
                        'brand_title' => $detail['brand_title']
                    ), true);


                    foreach($parts as $part) {
                        $part_obj = new LinemediaAutoPart($part['id']);

                        //��������� ���� ������
                        $price = new LinemediaAutoPrice($part_obj);
                        $price_calc = $price->calculate();
                        $formatted = CurrencyFormat($price_calc, $price->getCurrency());

                        $detail['PRICES'][$price_calc] = $formatted;
                    }

                    if(count($detail['PRICES']) > 0) {
                        $detail['min_price'] = min(array_keys($detail['PRICES']));

                        //���������� ���� � ���������
                        $arFields = Array(
                            "PRODUCT_ID" => $detail['ID'],
                            "CATALOG_GROUP_ID" => $price_type_id,
                            "PRICE" => $detail['PRICES'][$detail['min_price']]
                        );

                        $res_detail_price = CPrice::GetList(
                            array(),
                            array(
                                "PRODUCT_ID" => $detail['ID'],
                                "CATALOG_GROUP_ID" => $price_type_id
                            )
                        );

                        if ($detail_price = $res_detail_price->Fetch()) {
                            CPrice::Update($detail_price["ID"], $arFields);
                        }
                    }
                }
            }
        }
    }



    /**
     * �������� ������ � ����� ������� � ������.
     */
    protected static function sendBasketItemStatusMessage($basket_id, $status)
    {
        if (empty($basket_id) || empty($status)) {
            return;
        }

        $basket = new LinemediaAutoBasket();

        $arData  = $basket->getData($basket_id);
        $arProps = $basket->getProps($basket_id);
        $arOrder = CSaleOrder::GetByID($arData['ORDER_ID']);
        $arUser  = CUser::GetByID($arOrder['USER_ID'])->Fetch();


        // ������ ��������.
        $statuses = LinemediaAutoOrder::getStatusesList();

        /*
         * �������� ������ �� ��������� ������� ������:
         * 1. ���� ��� ������� ������ ������, �� ���������� ������ ��������� �� ����. 
         * 2. ���� ��� ������� ������ ������ ������, �������� ������ �� ����� ������, �� ���������� ��������� � ��������� ������� ����� ������. 
         * 3. ���� �� ������ ������ � ���� ������� � ������, �� ������������ ������ ������������ ������ ������ � ����� ������� ����� ������, � �� �� ������� ������.
         */
        $arEventFields = array(
            'EMAIL'         => $arUser['EMAIL'],
            'ORDER_ID'      => $arOrder['ID'],
            'ORDER_DATE'    => $arOrder['DATE_INSERT'],
            'ITEM_NAME'     => $arData['NAME'],
            'ITEM_STATUS'   => '['.$statuses[$status]['ID'].']'.' '.$statuses[$status]['NAME'],
            'ITEM_ART'      => $arProps['article']['VALUE'],
            'ITEM_BRAND'    => $arProps['brand_title']['VALUE'],
            'ITEM_PRICE'    => CurrencyFormat($arData['PRICE'], $arData['CURRENCY']),
            'ITEM_QUANTITY' => $arData['QUANTITY'],
            'ITEM_AMOUNT'   => CurrencyFormat($arData['PRICE'] * $arData['QUANTITY'], $arData['CURRENCY'])
        );

        CEvent::SendImmediate('LM_AUTO_SALE_STATUS_CHANGED', $arOrder['LID'], $arEventFields);
    }






    /************************************************************************
    * ��������� �������
    *************************************************************************/

    /**
     * ������� ���������.
     */
    protected static function getIntersectCatalogs($catalogs)
    {
        $items = array();

        foreach ($catalogs as $catalog) {
            $hash = md5($catalog['brand_title']);
            if (!in_array($hash, array_keys($items))) {
                $items[$hash] = $catalog;
            }
            $items[$hash]['genertic_articles'] []= $catalog['extra']['gid'];
        }

        foreach ($items as &$item) {
            $item['extra']['gid'] = implode(',', $item['genertic_articles']);
            unset($item['genertic_articles']);
        }

        return $items;
    }



}
