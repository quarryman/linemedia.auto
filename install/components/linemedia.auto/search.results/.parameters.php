<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

CModule::IncludeModule('linemedia.auto');

$db = CGroup::GetList();
$arGroups = array(($by = "NAME"), ($order = "ASC"));
while ($arGroup = $db->Fetch()) {
    $arGroups[$arGroup['ID']] = $arGroup['NAME'];
}

$arHideFields = array(
    'title'         => GetMessage('LM_AUTO_SEARCH_ITEM_TITLE'),
    'article'       => GetMessage('LM_AUTO_SEARCH_ITEM_ARTICLE'),
    'brand'         => GetMessage('LM_AUTO_SEARCH_ITEM_BRAND'),
    'info'          => GetMessage('LM_AUTO_SEARCH_ITEM_INFO'),
    'quantity'      => GetMessage('LM_AUTO_SEARCH_ITEM_QUANTITY'),
    'weight'        => GetMessage('LM_AUTO_SEARCH_ITEM_WEIGHT'),
    'supplier'      => GetMessage('LM_AUTO_SEARCH_ITEM_SUPPLIER'),
    'modified'      => GetMessage('LM_AUTO_SEARCH_ITEM_MODIFIED'),
    'delivery_time' => GetMessage('LM_AUTO_SEARCH_ITEM_DELIVERY_TIME'),
    'price'         => GetMessage('LM_AUTO_SEARCH_ITEM_PRICE'),
    'count'         => GetMessage('LM_AUTO_SEARCH_ITEM_COUNT'),
    'basket'        => GetMessage('LM_AUTO_SEARCH_ITEM_BASKET'),
    'notepad'       => GetMessage('LM_AUTO_SEARCH_ITEM_NOTEPAD'),
    'stats'         =>GetMessage('LM_AUTO_SEARCH_ITEM_STATS'),
);

$arShowBlocks = array(
    'form'      => GetMessage('LM_AUTO_MAIN_SEARCH_SHOW_FORM_FORM'),
    'results'   => GetMessage('LM_AUTO_MAIN_SEARCH_SHOW_FORM_RESULTS'),
    'both'      => GetMessage('LM_AUTO_MAIN_SEARCH_SHOW_FORM_BOT'),
);

$arSorts = array(
    'price_src'         => GetMessage('LM_AUTO_MAIN_SEARCH_SORT_PRICE'),
    'title'             => GetMessage('LM_AUTO_MAIN_SEARCH_SORT_TITLE'),
    'brand_title'       => GetMessage('LM_AUTO_MAIN_SEARCH_SORT_BRAND'),
    'article'           => GetMessage('LM_AUTO_MAIN_SEARCH_SORT_ARTICLE'),
    'quantity'          => GetMessage('LM_AUTO_MAIN_SEARCH_SORT_QUANTITY'),
    'delivery'          => GetMessage('LM_AUTO_MAIN_SEARCH_SORT_DELIVERY'),
    'supplier_title'    => GetMessage('LM_AUTO_MAIN_SEARCH_SORT_SUPPLIER'),
);

$arOrders = array(
    'asc'   => GetMessage('LM_AUTO_MAIN_SEARCH_ORDER_ASC'),
    'desc'  => GetMessage('LM_AUTO_MAIN_SEARCH_ORDER_DESC'),
);

$arComponentParameters = array(
    "PARAMETERS" => array(
        'ACTION_VAR'     =>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  'act',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_ACTION_VAR')
        ),
        'QUERY'     =>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '={$_REQUEST["q"]}',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_QUERY')
        ),
        'PART_ID'     =>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '={$_REQUEST["part_id"]}',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_PART_ID')
        ),
        'BRAND_TITLE'  =>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '={$_REQUEST["brand_title"]}',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_BRAND_TITLE')
        ),
        'EXTRA'  =>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '={$_REQUEST["extra"]}',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_EXTRA')
        ),
        'AUTH_URL'=>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '/auth/',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_AUTH_URL')
        ),
        'BASKET_URL'=>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '/auto/cart/',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_BASKET_PATH')
        ),
        'VIN_URL'=>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '/auto/vin/',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_VIN_URL')
        ),
        'INFO_URL'=>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '/auto/part-detail/#BRAND#/#ARTICLE#/',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_INFO_URL')
        ),
        'PATH_NOTEPAD' => array(
            'NAME'      => GetMessage('LM_AUTO_MAIN_SEARCH_NOTEPAD_URL'),
            'TYPE'      => 'STRING',
            'DEFAULT'   => '/auto/notepad/',
            'PARENT'    => 'BASE',
        ),
        'SET_TITLE' =>  array(
            'TYPE'      =>  'CHECKBOX',
            'DEFAULT'   =>  'Y',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_SET_TITLE')
        ),
        'TITLE'         =>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  GetMessage('LM_AUTO_MAIN_SEARCH_PAGE_TITLE') . '#QUERY#',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_TITLE_TPL')
        ),
        'HIDE_FIELDS'     =>  array(
            'TYPE'      =>  'LIST',
            'PARENT'    =>  'BASE',
            'VALUES'    => $arHideFields,
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_HIDE_FIELDS'),
            'MULTIPLE'  => 'Y',
        ),
        'USE_GROUP_SEARCH' =>  array(
            'TYPE'      =>  'CHECKBOX',
            'PARENT'    =>  'BASE',
            'DEFAULT'   =>  'Y',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_USE_GROUP_SEARCH'),
        ),
        'SHOW_SUPPLIER' =>  array(
            'TYPE'      =>  'LIST',
            'PARENT'    =>  'BASE',
            'VALUES'    =>  $arGroups,
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_SHOW_SUPPLIER'),
            'MULTIPLE'  => 'Y',
        ),
        'REMAPPING'     =>  array(
            'TYPE'      =>  'CHECKBOX',
            'DEFAULT'   =>  'N',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_REMAPPING'),
        ),
        'SHOW_BLOCKS' => array(
            'TYPE'      => 'LIST',
            'DEFAULT'   => 'N',
            'PARENT'    => 'BASE',
            'VALUES'    => $arShowBlocks,
            'NAME'      => GetMessage('LM_AUTO_MAIN_SEARCH_SHOW_BLOCKS'),
            'DEFAULT'   => 'both',
        ),
        'ANTI_BOTS' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('LM_AUTO_MAIN_SEARCH_ANTI_BOTS'),
            'TYPE' => 'CHECKBOX',
            'ADDITIONAL_VALUES' => 'N',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'N',
        ),
        'SORT' => array(
            'TYPE'      => 'LIST',
            'DEFAULT'   => 'price',
            'PARENT'    => 'BASE',
            'VALUES'    => $arSorts,
            'NAME'      => GetMessage('LM_AUTO_MAIN_SEARCH_SORT'),
            'DEFAULT'   => 'both',
        ),
        'ORDER' => array(
            'TYPE'      => 'LIST',
            'DEFAULT'   => 'ASC',
            'PARENT'    => 'BASE',
            'VALUES'    => $arOrders,
            'NAME'      => GetMessage('LM_AUTO_MAIN_SEARCH_ORDER'),
            'DEFAULT'   => 'both',
        ),
        'LIMIT' => array(
            'NAME'      => GetMessage('LM_AUTO_MAIN_SEARCH_LIMIT'),
            'TYPE'      => 'STRING',
            'DEFAULT'   => '0',
            'PARENT'    => 'BASE',
        ),
        'DISABLE_STATS' => array(
                "PARENT" => "BASE",
                "NAME" => GetMessage('LM_AUTO_MAIN_DISABLE_STATS'),
                "TYPE" => "CHECKBOX",
                "ADDITIONAL_VALUES" => "N",
                "MULTIPLE" => "N",
                "DEFAULT"=>'N',
        ),

    )
);
if (CModule::IncludeModule('form')) {
    $arComponentParameters['PARAMETERS']['USE_REQUEST_FORM'] = array(
            'PARENT'=>'BASE',
            'NAME'=>GetMessage('LM_AUTO_USE_REQUEST_FORM'),
            'TYPE'=>'CHECKBOX',
            'DEFAULT'=>'N'
        );
}