<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
    'PARAMETERS' => array(
        'SEF_MODE' => array(
        ),
        'SEARCH_URL' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_DETAIL_PATH_SEARCH'),
            'TYPE' => 'STRING',
            'ADDITIONAL_VALUES' => 'N',
            'MULTIPLE' => 'N',
            'DEFAULT' => '/auto/search/#ARTICLE_ID#/#BRAND_ID#/'
        ),
        'SET_TITLE' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_DETAIL_SET_TITLE'),
                'TYPE' => 'CHECKBOX',
                'ADDITIONAL_VALUES' => 'N',
                'MULTIPLE' => 'N',
                'DEFAULT' => 'Y'
        ),
        'ADD_SECTIONS_CHAIN' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_DETAIL_ADD_CHAIN'),
                'TYPE' => 'CHECKBOX',
                'ADDITIONAL_VALUES' => 'N',
                'MULTIPLE' => 'N',
                'DEFAULT' => 'N'
        ),
        'SHOW_ORIGINAL_ITEMS' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_DETAIL_SHOW_ORIGINAL'),
                'TYPE' => 'CHECKBOX',
                'ADDITIONAL_VALUES' => 'N',
                'MULTIPLE' => 'N',
                'DEFAULT' => 'Y'
        ),
        'SHOW_SEARCH_FORM' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_DETAIL_SHOW_SEARCH_FORM'),
                'TYPE' => 'CHECKBOX',
                'ADDITIONAL_VALUES' => 'N',
                'MULTIPLE' => 'N',
                'DEFAULT' => 'Y'
        ),
        'SHOW_APPLICABILITY' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_DETAIL_SHOW_APPLICABILITY'),
                'TYPE' => 'CHECKBOX',
                'ADDITIONAL_VALUES' => 'N',
                'MULTIPLE' => 'N',
                'DEFAULT' => 'Y'
        ),
        'SHOW_SEO' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_DETAIL_SHOW_SEO'),
                'TYPE' => 'CHECKBOX',
                'ADDITIONAL_VALUES' => 'N',
                'MULTIPLE' => 'N',
                'DEFAULT' => 'N'
        ),
        'CACHE' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_DETAIL_CACHE'),
                'TYPE' => 'CHECKBOX',
                'ADDITIONAL_VALUES' => 'N',
                'MULTIPLE' => 'N',
                'DEFAULT' => 'N'
        ),
        'CACHE_TIME' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_CATALOG_DETAIL_CACHE_TIME'),
                'TYPE' => 'STRING',
                'ADDITIONAL_VALUES' => 'N',
                'MULTIPLE' => 'N',
                'DEFAULT' => '3600'
        ),
    ),
);
