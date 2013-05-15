<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$actions = array(
    'getBrands' => GetMessage('LM_AUTO_MAIN_TECDOC_AUTO_ACTION_BRANDS'),
    'getModels' => GetMessage('LM_AUTO_MAIN_TECDOC_AUTO_ACTION_MODELS'),
    'getModifications' => GetMessage('LM_AUTO_MAIN_TECDOC_AUTO_ACTION_MODIFICATIONS'),
);

$arComponentParameters = array(
    'PARAMETERS' => array(
        'ACTION' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_AUTO_ACTION'),
                'TYPE' => 'LIST',
                'VALUES' => $actions,
                'DEFAULT' => 'getBrands'
        ),
        'BRAND_ID' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_AUTO_BRAND_ID'),
                'TYPE' => 'STRING',
        ),
        'MODEL_ID' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_AUTO_MODEL_ID'),
                'TYPE' => 'STRING',
        ),
        'MODIFICATION_ID' => array(
                'PARENT' => 'BASE',
                'NAME' => GetMessage('LM_AUTO_MAIN_TECDOC_AUTO_MODIFICATION_ID'),
                'TYPE' => 'STRING',
        ),
    )
);
