<?php

$props = array(
    array(
        'NAME' => GetMessage('LM_AUTO_SALE_PROP_ALLOW_PAYMENT'),
        'TYPE' => 'CHECKBOX',
        'REQUIED' => 'N',
        'CODE' => 'ALLOW_PAYMENT',
        'USER_PROPS' => 'N',
        'IS_LOCATION' => 'N',
        'DEFAULT_VALUE' => 'N',
        'UTIL' => 'Y',
    ),
    array(
        'NAME' => GetMessage('LM_AUTO_MAIN_SALE_PROP_LOCATION'),
        'TYPE' => 'LOCATION',
        'REQUIED' => 'N',
        'CODE' => 'LOCATION',
        'USER_PROPS' => 'N',
        'IS_LOCATION' => 'Y',
        'IS_FILTERED' => 'Y',
        'DEFAULT_VALUE' => '',
        'UTIL' => 'Y',
    ),
);
