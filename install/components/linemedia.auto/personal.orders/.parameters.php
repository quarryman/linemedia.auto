<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();


$arComponentParameters = array(
    'PARAMETERS' => array(
        'COUNT_ON_PAGE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('LM_AUTO_MAIN_COUNT_ON_PAGE'),
            'TYPE' => 'STRING',
            'ADDITIONAL_VALUES' => 'N',
            'MULTIPLE' => 'N',
            'DEFAULT' => '20'
        ),
        'USE_STATUS_COLOR' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('LM_AUTO_MAIN_USE_STATUS_COLOR'),
            'TYPE' => 'CHECKBOX',
            'ADDITIONAL_VALUES' => 'N',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'N'
        ),
        'PATH_TO_PAYMENT' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('LM_AUTO_MAIN_PATH_TO_PAYMENT'),
            'TYPE' => 'STRING',
            'ADDITIONAL_VALUES' => 'N',
            'MULTIPLE' => 'N',
            'DEFAULT' => '/auto/order/make/'
        ),
        'SET_TITLE' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('LM_AUTO_MAIN_SET_TITLE'),
            'TYPE' => 'CHECKBOX',
            'ADDITIONAL_VALUES' => 'N',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'N'
        ),
        'UNION_BY_ORDERS' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('LM_AUTO_MAIN_UNION_BY_ORDERS'),
            'TYPE' => 'CHECKBOX',
            'ADDITIONAL_VALUES' => 'N',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'N'
        ),
    )
);