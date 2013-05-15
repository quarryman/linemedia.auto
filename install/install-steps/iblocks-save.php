<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);

/*
 * Установка инфоблоков
 */

CModule::IncludeModule('iblock');

/*
 * Тип иноблоков
 */
$db_iblock_type = CIBlockType::GetList(array(), array('ID' => 'linemedia_auto'));
if ($ar_iblock_type = $db_iblock_type->Fetch()) {
   
} else {
    $arFields = array(
        'ID'        =>  'linemedia_auto',
        'SECTIONS'  =>  'Y',
        'IN_RSS'    =>  'N',
        'SORT'      =>  100,
        'LANG'      =>  array(
            'en'    =>  array(
                'NAME'          =>  'Linemedia Autoexpert',
                'SECTION_NAME'  =>  'Sections',
                'ELEMENT_NAME'  =>  'Products'
            ),
            'ru'    =>  array(
                'NAME'          =>  GetMessage('LM_AUTO_MAIN_IBLOCK_AUTOEXPERT'),
                'SECTION_NAME'  =>  GetMessage('LM_AUTO_MAIN_IBLOCK_SECTIONS'),
                'ELEMENT_NAME'  =>  GetMessage('LM_AUTO_MAIN_IBLOCK_GOODS'),
            ),
            'fr'    =>  array(
                'NAME'          =>  'Linemedia Autoexpert',
                'SECTION_NAME'  =>  'Sections',
                'ELEMENT_NAME'  =>  'Articles'
            ),
            'de'    =>  array(
                'NAME'          =>  'Linemedia Autoexpert',
                'SECTION_NAME'  =>  'Sektions',
                'ELEMENT_NAME'  =>  'Waren'
            ),
        )
    );

    $obBlocktype = new CIBlockType();
    global $DB;
    $DB->StartTransaction();
    $res = $obBlocktype->Add($arFields);
    if (!$res) {
       $DB->Rollback();
       echo 'Error: '.$obBlocktype->LAST_ERROR.'<br>';
    } else {
       $DB->Commit();
    }
}


/*
 * какие сайты есть в системе?
 */
$sites = array();
$rsSites = CSite::GetList($by="sort", $order="desc", array());
while ($arSite = $rsSites->Fetch()) {
    $sites[] = $arSite['ID'];
}


/*
 * Добавление инфоблоков в новый тип
 */
$iblocks = array(
    
    /*
     * Поставщики
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS'),
        'CODE' => 'suppliers',
        'ELEMENT_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_ELEMENT_NAME'),
        'ELEMENTS_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_ELEMENTS_NAME'),
        'ELEMENT_ADD' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_ELEMENT_ADD'),
        'ELEMENT_EDIT' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_ELEMENT_EDIT'),
        'ELEMENT_DELETE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_ELEMENT_DELETE'),
        'PROPERTIES' => array(
            /*
             * ID поставщика
             */
            array(
                'CODE' => 'supplier_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_ID'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "Y",
            ),
            /*
             * Наценка
             */
            array(
                'CODE' => 'markup',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_MARKUP'),
                "PROPERTY_TYPE" => "N",
            ),
            /*
             * Срок доставки
             */
            array(
                'CODE' => 'delivery_time',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_DELIVERY_TIME'),
                "PROPERTY_TYPE" => "N",
            ),
            /*
             * Название для пользователей
             */
            array(
                'CODE' => 'visual_title',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_VISUAL_TITLE'),
                "PROPERTY_TYPE" => "S",
                "REQUIRED"      => 'Y',
            ),
            /*
             * email
             */
            array(
                'CODE' => 'email',
                'NAME' => 'e-mail',
                "PROPERTY_TYPE" => "S",
                "REQUIRED"      => 'N',
            ),
            /*
             * Валюта прайслиста
             */
            array(
                'CODE' => 'currency',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_CURRENCY'),
                "PROPERTY_TYPE" => "N",
                "USER_TYPE" 	=> "currency",
                "REQUIRED"      => 'Y',
            ),
            /*
             * CSS
             */
            array(
                'CODE' => 'css',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_CSS'),
                "PROPERTY_TYPE" => "S",
                "REQUIRED"      => 'N',
            ),
        ),
        /*
         * Примеры
         */
        'ELEMENTS' => array(
            array(
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TEST_SUPPLIER_1'),
                'PROPERTY_VALUES' => array(
                    'supplier_id' => '1',
                    'markup' => '10',
                    'visual_title' => 'brg',
                ),
            ),
            array(
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TEST_SUPPLIER_2'),
                'PROPERTY_VALUES' => array(
                    'supplier_id' => '2',
                    'markup' => '15',
                    'visual_title' => 'world',
                ),
            ),
        ),
        'FORMS' => array(
        	'LIST' => 'NAME,ACTIVE,PROPERTY_#PROP_SUPPLIER_ID#,PROPERTY_#PROP_VISUAL_TITLE#,PROPERTY_#PROP_DELIVERY_TIME#,PROPERTY_#PROP_API#',
        	'EDIT' => array(
        		array(
		          'CODE' => 'edit1',
		          'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIER'),
		          'FIELDS' => array(
		               array(
		                    'NAME' => 'NAME',
		                    'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_NAME'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_MARKUP#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_MARKUP'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_VISUAL_TITLE#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_VISUAL_TITLE'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_SUPPLIER_ID#',
		                   'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_ID'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_EMAIL#',
		                   'TITLE' => 'e-mail',
		               ),
		               array(
		                   'NAME' => 'IBLOCK_ELEMENT_PROP_VALUE',
		                   'TITLE' => '--' . GetMessage('LM_AUTO_MAIN_IBLOCK_ADDITIONAL_SETTINGS'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_DELIVERY_TIME#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_DELIVERY_TIME'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_CURRENCY#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SUPPLIERS_PROP_CURRENCY'),
		               ),
		           ),
		       ),
		    ),
        ),
    ),
    
    /*
     * SEO текст в поиске
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO'),
        'CODE' => 'search_seo',
        'PROPERTIES' => array(
            /*
             * Артикул
             */
            array(
                'CODE' => 'article',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO_PROP_ARTICLE'),
                "PROPERTY_TYPE" => "S",
            ),
            /*
             * Название бренда
             */
            array(
                'CODE' => 'brand_title',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO_PROP_BRAND_TITLE'),
                "PROPERTY_TYPE" => "N",
            ),
        ),
        /*
        * Примеры
        */
        'ELEMENTS' => array(
            array(
                'NAME' => 'gdb1550',
                "DETAIL_TEXT" => GetMessage('LM_AUTO_MAIN_IBLOCK_TEST_GOOD_DESCR'),
                "DETAIL_PICTURE" => CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.auto/install/images/linemedia.auto/gdb1550.jpg"),
                'PROPERTY_VALUES' => array(
                    'article' => 'gdb1550',
                    'brand_title' => 'TRW',
                ),
            ),
        ),
        'FORMS' => array(
        	'LIST' => 'NAME,ACTIVE,PROPERTY_#PROP_ARTICLE#,PROPERTY_#PROP_BRAND_TITLE#,DETAIL_PICTURE',
        	'EDIT' => array(
        		array(
		          'CODE' => 'edit1',
		          'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO'),
		          'FIELDS' => array(
		               array(
		                    'NAME' => 'NAME',
		                    'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_NAME'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_ARTICLE#',
		                   'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO_PROP_ARTICLE'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_BRAND_TITLE#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEARCH_SEO_PROP_BRAND_TITLE'),
		               ),
		               array(
		                   'NAME' => 'DETAIL_TEXT',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEO_DETAIL_TEXT'),
		               ),
		               array(
		                   'NAME' => 'DETAIL_PICTURE',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SEO_DETAIL_PICTURE'),
		               ),
		           ),
		       ),
		    ),
        ),
    ),
    
    
    
    /*
     * Tecdoc права доступа
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TECDOC_ACCESS_LIST'),
        'CODE' => 'tecdoc_access_list',
        'PROPERTIES' => array(
            /*
             * Раздел API
             */
            array(
                'CODE' => 'api_section',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TECDOC_ACCESS_LIST_PROP_API_SECTION'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Компонент
             */
            array(
                'CODE' => 'component',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TECDOC_ACCESS_LIST_PROP_COMPONENT'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * ID элемента API
             */
            array(
                'CODE' => 'api_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TECDOC_ACCESS_LIST_PROP_API_ID'),
                "PROPERTY_TYPE" => "N",
                "IS_REQUIRED" => "N",
            ),
        ),
        /*
         * Примеры
         */
        'ELEMENTS' => array(
        )
    ),
    
    
    /*
     * Скидки
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT'),
        'CODE' => 'discount',
        'ELEMENT_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_ELEMENT_NAME'),
        'ELEMENTS_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_ELEMENTS_NAME'),
        'ELEMENT_ADD' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_ELEMENT_ADD'),
        'ELEMENT_EDIT' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_ELEMENT_EDIT'),
        'ELEMENT_DELETE' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_ELEMENT_DELETE'),
        'PROPERTIES' => array(
            /*
            * Артикул
            */
            array(
                'CODE' => 'article',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_ARTICLE'),
                "PROPERTY_TYPE" => "S",
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
            ),
            /*
             * Название бренда
             */
            array(
                'CODE' => 'brand_title',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_BRAND_TITLE'),
                "PROPERTY_TYPE" => "S",
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
            ),
            /*
             * Група пользователя
             */
            array(
                'CODE' => 'user_group',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_USER_GROUP'),
                "PROPERTY_TYPE" => "S",
                'USER_TYPE' => "user_group",
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
            ),
            /*
             * Пользователь
             */
            array(
                'CODE' => 'user_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_USER_ID'),
                "PROPERTY_TYPE" => "S",
                'USER_TYPE' => "UserID",
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
            ),
            /*
             * Поставщик
             */
            array(
                'CODE' => 'supplier_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_SUPPLIER_ID'),
                "PROPERTY_TYPE" => "E",
                //'USER_TYPE' => "EAutocomplete",
                'LINK_IBLOCK_ID'=> COption::GetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_SUPPLIERS"),
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
            ),
            /*
             * Мин цена
             */
            array(
                'CODE' => 'price_min',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_PRICE_MIN'),
                "PROPERTY_TYPE" => "N",
            ),
            /*
             * Макс цена
             */
            array(
                'CODE' => 'price_max',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_PRICE_MAX'),
                "PROPERTY_TYPE" => "N",
            ),
            /*
             * Изменение (%)
             */
            array(
                'CODE' => 'discount',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT'),
                "PROPERTY_TYPE" => "N",
                //'PROPERTY_HINT' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_HINT'),
            ),
            /*
             * Тип скидки (Способ расчёта цены)
             */
            array(
                'CODE' => 'discount_type',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_TYPE'),
                "PROPERTY_TYPE" => "L",
                'REQUIRED' => 'Y',
                "VALUES" => array(
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_TYPE_MARKUP_DISCOUNT'),
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'SUPPLIER_MARKUP_DISCOUNT',
                    ),
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_TYPE_FINAL_PRICE_DISCOUNT'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'FINAL_PRICE_DISCOUNT',
                    ),
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_DISCOUNT_PROP_DISCOUNT_TYPE_BASE_PRICE_MARKUP'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'BASE_PRICE_MARKUP',
                    ),
                ),
            ),
            
            
        ),
        /*
         * Примеры
         */
        'ELEMENTS' => array(
            array(
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_TEST_SALE'),
                'PROPERTY_VALUES' => array(
                    'article' => 'gdb1550',
                    'user_group' => Array("VALUE" => 1),
                    'discount' => 30,
                ),
            ),
        ),
        'FORMS' => array(
        	'LIST' => 'NAME,ACTIVE,PROPERTY_#PROP_ARTICLE#,PROPERTY_#PROP_BRAND_TITLE#,PROPERTY_#PROP_SUPPLIER_ID#,PROPERTY_#PROP_DISCOUNT#,PROPERTY_#PROP_DISCOUNT_TYPE#',
        	'EDIT' => array(
        		array(
		          'CODE' => 'edit1',
		          'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE'),
		          'FIELDS' => array(
		               array(
		                    'NAME' => 'ACTIVE',
		                    'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_ACTIVE'),
		               ),
		               array(
		                    'NAME' => 'ACTIVE_FROM',
		                    'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_ACTIVE_FROM'),
		               ),
		               array(
		                    'NAME' => 'ACTIVE_TO',
		                    'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_ACTIVE_TO'),
		               ),
		               array(
		                    'NAME' => 'NAME',
		                    'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_NAME'),
		               ),
		               array(
		                    'NAME' => 'CONDITIONS',
		                    'TITLE' => '--' . GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_CONDITIONS'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_ARTICLE#',
		                   'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_ARTICLE'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_BRAND_TITLE#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_BRAND_TITLE'),
		               ),
		               
		               array(
		                   'NAME' => 'PROPERTY_#PROP_USER_GROUP#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_USER_GROUP'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_USER_ID#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_USER_ID'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_SUPPLIER_ID#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_SUPPLIER_ID'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_PRICE_MIN#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_PRICE_MIN'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_PRICE_MAX#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_PRICE_MAX'),
		               ),
		               
		               array(
		                   'NAME' => 'ACTION',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_ACTION'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_DISCOUNT_TYPE#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_DISCOUNT_TYPE'),
		               ),
		               array(
		                   'NAME' => 'PROPERTY_#PROP_DISCOUNT#',
		                   'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_SALE_DISCOUNT'),
		               ),
		               
		           ),
		       ),
		    ),
        ),
    ),
    
    
    /*
     * Автопереводы в группы
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER'),
        'CODE' => 'group_transfer',
        'ELEMENT_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_ELEMENT_NAME'),
        'ELEMENTS_NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_ELEMENTS_NAME'),
        'ELEMENT_ADD' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_ELEMENT_ADD'),
        'ELEMENT_EDIT' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_ELEMENT_EDIT'),
        'ELEMENT_DELETE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_ELEMENT_DELETE'),
        'PROPERTIES' => array(
            /*
             * Сумма для перехода
             */
            array(
                'CODE' => 'summ',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_SUMM'),
                'SORT' => '100',
                'PROPERTY_TYPE' => 'N',
                'MULTIPLE' => 'N',
                'WITH_DESCRIPTION' => 'N',
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'IS_REQUIRED' => 'Y',
                'USER_TYPE' => NULL,
            ),
            /*
             * Группы, в которые входит пользователь.
             */
            array(
                'CODE' => 'groups_in',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_IN'),
                'HINT' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_IN_HINT'),
                'SORT' => '200',
                'PROPERTY_TYPE' => 'S',
                'MULTIPLE' => 'Y',
                'MULTIPLE_CNT' => '5',
                'WITH_DESCRIPTION' => 'N',
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'IS_REQUIRED' => 'N',
                'USER_TYPE' => 'user_group',
            ),
            /*
             * Группы, из которых выходит пользователь.
             */
            array(
                'CODE' => 'groups_out',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_OUT'),
                'HINT' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_OUT_HINT'),
                'SORT' => '300',
                'PROPERTY_TYPE' => 'S',
                'MULTIPLE' => 'Y',
                'MULTIPLE_CNT' => '5',
                'WITH_DESCRIPTION' => 'N',
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'IS_REQUIRED' => 'N',
                'USER_TYPE' => 'user_group',
            ),
        ),
        'FORMS' => array(
            'LIST' => 'NAME,ACTIVE,PROPERTY_#PROP_SUMM#,PROPERTY_#PROP_GROUPS_IN#,PROPERTY_#PROP_GROUPS_OUT#',
            'EDIT' => array(
                array(
                  'CODE' => 'edit1',
                  'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER'),
                  'FIELDS' => array(
                       array(
                            'NAME' => 'NAME',
                            'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_NAME'),
                       ),
                       array(
                            'NAME' => 'ACTIVE',
                            'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_FORM_GROUP_ACTIVE'),
                       ),
                       array(
                            'NAME' => 'ACTIVE_FROM',
                            'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_FORM_GROUP_ACTIVE_FROM'),
                       ),
                       array(
                            'NAME' => 'ACTIVE_TO',
                            'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_FORM_GROUP_ACTIVE_TO'),
                       ),
                       array(
                           'NAME' => 'PROPERTY_#PROP_SUMM#',
                           'TITLE' => '*' . GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_SUMM'),
                       ),
                       array(
                           'NAME' => 'PROPERTY_#PROP_GROUPS_IN#',
                           'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_IN'),
                       ),
                       array(
                           'NAME' => 'PROPERTY_#PROP_GROUPS_OUT#',
                           'TITLE' => GetMessage('LM_AUTO_MAIN_IBLOCK_GROUP_TRANSFER_PROP_GROUPS_OUT'),
                       ),
                   ),
               ),
            ),
        ),
    ),

    
    /*
     * Запросы по VIN
     */
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN'),
        'CODE' => 'vin',
        'VERSION' => '2',
        'WORKFLOW' => 'N',
        'RIGHTS_MODE' => 'E',
        'PROPERTIES' => array(
            /*
             * Код VIN
             */
            array(
                'CODE' => 'vin',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_VIN'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "Y",
            ),
            
            /*
             * Год выпуска
             */
            array(
                'CODE' => 'year',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_YEAR'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Месяц выпуска
             */
            array(
                'CODE' => 'month',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MONTH'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Марка
             */
            array(
                'CODE' => 'brand',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BRAND'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Марка (ID)
             */
            array(
                'CODE' => 'brand_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BRAND_ID'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Модель
             */
            array(
                'CODE' => 'model',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MODEL'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Модель (ID)
             */
            array(
                'CODE' => 'model_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MODEL_ID'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Модификация
             */
            array(
                'CODE' => 'modification',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MODIFICATION'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Модификация (ID)
             */
            array(
                'CODE' => 'modification_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MODIFICATION_ID'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Мощность, л.с.
             */
            array(
                'CODE' => 'horsepower',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_HORSEPOWER'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Объем двигателя, см3
             */
            array(
                'CODE' => 'displacement',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DISPLACEMENT'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Дополнительная информация
             */
            array(
                'CODE' => 'extra',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_EXTRA'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "HTML",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Цилиндров
             */
            array(
                'CODE' => 'cylinders',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CYLINDERS'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Клапанов
             */
            array(
                'CODE' => 'valve',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_VALVE'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Тип кузова
             */
            array(
                'CODE' => 'body_type',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'L',
                'REQUIRED' => 'N',
                "VALUES" => array(
                    //Седан
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_SEDAN'),
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'SEDAN',
                    ),
                    //Хэтчбэк
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_HATCHBACK'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'HATCHBACK',
                    ),
                    //Универсал
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_UNIVERSAL'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'UNIVERSAL',
                    ),
                    //Джип
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_JEEP'),
                        "DEF" => "N",
                        "SORT" => "4",
                        'XML_ID' => 'JEEP',
                    ),
                    //Купе
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_COUPE'),
                        "DEF" => "N",
                        "SORT" => "5",
                        'XML_ID' => 'COUPE',
                    ),
                    //Кабриолет
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_CABRIOLET'),
                        "DEF" => "N",
                        "SORT" => "6",
                        'XML_ID' => 'CABRIOLET',
                    ),
                    //Минивэн
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_MINIVAN'),
                        "DEF" => "N",
                        "SORT" => "7",
                        'XML_ID' => 'MINIVAN',
                    ),
                    //Микроавтобус
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_BODY_TYPE_MINIBUS'),
                        "DEF" => "N",
                        "SORT" => "8",
                        'XML_ID' => 'MINIBUS',
                    ),
                ),
            ),
            
            /*
             * Число дверей
             */
            array(
                'CODE' => 'doors',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DOORS'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'L',
                'REQUIRED' => 'N',
                "VALUES" => array(
                    array(
                        "VALUE" => "2",
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'DOOR_1',
                    ),
                    array(
                        "VALUE" => "3",
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'DOOR_2',
                    ),
                    array(
                        "VALUE" => "4",
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'DOOR_3',
                    ),
                    array(
                        "VALUE" => "5",
                        "DEF" => "N",
                        "SORT" => "4",
                        'XML_ID' => 'DOOR_4',
                    ),
                    array(
                        "VALUE" => "6",
                        "DEF" => "N",
                        "SORT" => "5",
                        'XML_ID' => 'DOOR_5',
                    ),
                    array(
                        "VALUE" => "7",
                        "DEF" => "N",
                        "SORT" => "6",
                        'XML_ID' => 'DOOR_6',
                    ),
                ),
            ),
            
            /*
             * Тип/буквы двигателя
             */
            array(
                'CODE' => 'engine_type',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_ENGINE_TYPE'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Привод
             */
            array(
                'CODE' => 'drive',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DRIVE'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'L',
                'REQUIRED' => 'N',
                "VALUES" => array(
                    //Передний
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DRIVE_FRONT'),
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'FRONT',
                    ),
                    //Задний
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DRIVE_BACK'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'BACK',
                    ),
                    //Полный
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_DRIVE_FULL'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'FULL',
                    ),
                ),
            ),
            
            /*
             * Тип кпп
             */
            array(
                'CODE' => 'transmission',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_TRANSMISSION'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'L',
                'REQUIRED' => 'N',
                "VALUES" => array(
                    //Механическая
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_TRANSMISSION_MANUAL'),
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'MANUAL',
                    ),
                    //Автоматическая
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_TRANSMISSION_AUTOMATIC'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'AUTOMATIC',
                    ),
                    //Вариатор
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_TRANSMISSION_VARIATOR'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'VARIATOR',
                    ),
                ),
            ),
            
            /*
             * Номер кпп
             */
            array(
                'CODE' => 'transmission_number',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_TRANSMISSION_NUMBER'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Руль
             */
            array(
                'CODE' => 'steering_wheel',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_STEERING_WHEEL'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'C',
                'REQUIRED' => 'N',
                "VALUES" => array(
                    //Слева
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_STEERING_WHEEL_LEFT'),
                        "DEF" => "Y",
                        "SORT" => "1",
                        'XML_ID' => 'LEFT',
                    ),
                    //Справа
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_STEERING_WHEEL_RIGHT'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'RIGHT',
                    ),
                ),
            ),
            
            /*
             * Опции комплектации
             */
            array(
                'CODE' => 'configuration',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION'),
                "PROPERTY_TYPE" => "L",
                'LIST_TYPE' => 'C',
                'MULTIPLE'      => 'Y',
                'MULTIPLE_CNT' => 1,
                'REQUIRED' => 'N',
                "VALUES" => array(
                    //ABS
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION_ABS'),
                        "DEF" => "N",
                        "SORT" => "1",
                        'XML_ID' => 'LEFT',
                    ),
                    //ESP
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION_ESP'),
                        "DEF" => "N",
                        "SORT" => "2",
                        'XML_ID' => 'RIGHT',
                    ),
                    //УР
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION_UR'),
                        "DEF" => "N",
                        "SORT" => "3",
                        'XML_ID' => 'RIGHT',
                    ),
                    //Кондиционер
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION_AC'),
                        "DEF" => "N",
                        "SORT" => "4",
                        'XML_ID' => 'RIGHT',
                    ),
                    //Катализатор
                    array(
                        "VALUE" => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_CONFIGURATION_ACCELERANT'),
                        "DEF" => "N",
                        "SORT" => "5",
                        'XML_ID' => 'RIGHT',
                    ),
                ),
            ),
            
            /*
             * Запрос
             */
            array(
                'CODE' => 'request',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_REQUEST'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "HTML",
                "IS_REQUIRED" => "Y",
            ),
            
            /*
             * Ответ
             */
            array(
                'CODE' => 'answer',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_ANSWER'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "HTML",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Ответил 
             */
            array(
                'CODE' => 'answer_manager',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_ANSWER_MANAGER'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "UserID",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Дата ответа
             */
            array(
                'CODE' => 'answer_date',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_ANSWER_DATE'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "DateTime",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Менеджер
             */
            array(
                'CODE' => 'manager',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_MANAGER'),
                "PROPERTY_TYPE" => "S",
                "USER_TYPE" 	=> "UserID",
                "IS_REQUIRED" => "N",
            ),
            
            /*
             * Сайт
             */
            array(
                'CODE' => 'site_id',
                'NAME' => GetMessage('LM_AUTO_MAIN_IBLOCK_VIN_PROP_SITE_ID'),
                "PROPERTY_TYPE" => "S",
                "IS_REQUIRED" => "N",
            ),
            
        ),
        /*
         * Примеры
         */
        'ELEMENTS' => array(
        )
    ),

    
);


foreach ($iblocks as $SORT => $iblock) {
    /*
     * Если инфоблок уже есть - не создаём его
     */
    $res = CIBlock::GetList(array(), array('TYPE' => 'linemedia_auto', 'ACTIVE' => 'Y', 'CODE' => 'lm_auto_' . $iblock['CODE']), true);
    if ($found_iblock = $res->Fetch()) {
        COption::SetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_" . $iblock['CODE'], $found_iblock['ID']);
    } else {
        /*
         * Инфоблока нет - создадим его
         */
        $CODE = strtoupper($iblock['CODE']);
        
        $ib = new CIBlock();
        
        $iblock['ACTIVE'] = 'Y';
        $iblock['CODE'] = 'lm_auto_' . $iblock['CODE'];
        $iblock['IBLOCK_TYPE_ID'] = 'linemedia_auto';
        $iblock['SITE_ID'] = $sites;
        $iblock['SORT'] = $SORT;
        $iblock['INDEX_ELEMENT'] = 'N';
        
        
        $IBLOCK_ID = $ib->Add($iblock);
        if ($IBLOCK_ID > 0) {
            COption::SetOptionInt("linemedia.auto", "LM_AUTO_IBLOCK_" . $CODE, $IBLOCK_ID);
        } else {
            throw new Exception('Error adding iblock ' . $iblock['CODE']);
        }
        
        
        /*
         * Сохраним ID свойств, чтобы прописать их в визуальные настройки
         */
        $PROP_IDS = array();
        
        /*
         * Добавление свойств инфоблока
         */
        foreach ($iblock['PROPERTIES'] as $i => $PROP) {
            $PROP['ACTIVE'] = 'Y';
            $PROP['IBLOCK_ID'] = $IBLOCK_ID;
            $PROP['SORT'] = $i;
            
            $ibp = new CIBlockProperty();
            if (!$PropID = $ibp->Add($PROP)) {
                throw new Exception('Error adding iblock property ' . print_r($PROP, 1));
            }
            $PROP_IDS['#PROP_' . strtoupper($PROP['CODE']) . '#'] = $PropID;
        }
        
        
        /*
         * Добавление элементов в инфоблок
         */
        foreach ($iblock['ELEMENTS'] as $ELEMENT) {
            $ELEMENT['ACTIVE'] = 'Y';
            $ELEMENT['IBLOCK_ID'] = $IBLOCK_ID;
            
            $el = new CIBlockElement();
            if (!$ELEMENT_ID = $el->Add($ELEMENT)) {
                throw new Exception('Error adding iblock element ' . $ELEMENT['NAME']);
            }
        }
        
        
        /*
		 * Настройка форм и списков инфоблоков
		 */
		$iblock_type = 'linemedia_auto';
		$columns = trim(strval($iblock['FORMS']['LIST']));
		$edit_tabs = array_filter((array) $iblock['FORMS']['EDIT']);
        
        /*
         * Список
         */
        if ($columns != '') {
	        $columns = str_replace(array_keys($PROP_IDS), array_values($PROP_IDS), $columns);
	        
	        $option_hash = "tbl_iblock_list_".md5($iblock_type.".".$IBLOCK_ID);
			$arOptions = array(
			     array(
			          'c' => 'list',
			          'n' => $option_hash,
			          'd' => 'Y',
			          'v' => array(
			               'columns' => $columns,
			               'by' => 'timestamp_x',
			               'order' => 'desc',
			               'page_size' => '20',
			          ),
			     )
			);
			CUserOptions::SetOptionsFromArray($arOptions);
        }
        
        /*
         * Форма редактирования
         */
        if (count($edit_tabs) > 0) {
	        /*
	         * Подставим ID свойств
	         */
	        foreach ($edit_tabs AS $i => $tab) {
		        foreach ($tab['FIELDS'] AS $y => $field) {
			        $edit_tabs[$i]['FIELDS'][$y]['NAME'] = str_replace(array_keys($PROP_IDS), array_values($PROP_IDS), $field['NAME']);
		        }
	        }
	        
	        /*
	         * Составим этот адский хеш
	         */
	        $tabs_string = '';
			foreach($edit_tabs as $tab) {
			    $tabs_string .= $tab['CODE'] . '--#--' . $tab['TITLE'] . '--,--';
			    foreach ($tab['FIELDS'] as $field) {
			        $tabs_string .= $field['NAME'] . '--#--' . $field['TITLE'] . '--,--';
			    }
			}
			$arOptions = array(
			    array(
			        'c' => 'form',
			        'n' => 'form_element_' . $IBLOCK_ID,
			        'd' => 'Y',
			        'v' => array(
			            'tabs' => $tabs_string,
			        ),
			    )
			);
			CUserOptions::SetOptionsFromArray($arOptions);
        }
    }

}


/*
 * Установка прав в инфоблоках.
 */

$obIblockRights = new CIBlockRights(COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_VIN'));

$rights = array(
    'n0' => array(
        'GROUP_CODE'    => 'G2',
        'DO_CLEAN'      => 'Y',
        'TASK_ID'       => $obIblockRights->LetterToTask('R'),
    )
);

$obIblockRights->SetRights($rights);
unset($obIblockRights, $rights);