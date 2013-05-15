<?php

/**
 * Linemedia Autoportal
 * Main module
 * Default options
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

/* Здесь вписываются значения по умолчанию для всех настроек модуля */
$linemedia_auto_default_option = array(
    'LM_AUTO_MAIN_API_ID'          		        => '',                      // ID клиента в API
    'LM_AUTO_MAIN_API_KEY'		                => '',                      // ключ клиента в API
    'LM_AUTO_MAIN_API_URL'          	        => 'api.auto.linemedia.ru', // api.auto.linemedia.ru
    'LM_AUTO_MAIN_API_FORMAT'                   => 'json',                  // json xml serialized urlencoded
    
    'LM_AUTO_MAIN_PART_DETAIL_PAGE'             => '/auto/search/?part_id=#PART_ID#',
    'LM_AUTO_MAIN_PART_SEARCH_PAGE'             => '/auto/search/#ARTICLE#/',
    
    'LM_AUTO_ANALOG_GROUP_N'                    => GetMessage('LM_AUTO_ANALOG_GROUP_N'),
    'LM_AUTO_ANALOG_GROUP_0'                    => GetMessage('LM_AUTO_ANALOG_GROUP_0'),
    'LM_AUTO_ANALOG_GROUP_1'                    => GetMessage('LM_AUTO_ANALOG_GROUP_1'),
    'LM_AUTO_ANALOG_GROUP_2'                    => GetMessage('LM_AUTO_ANALOG_GROUP_2'),
    'LM_AUTO_ANALOG_GROUP_3'                    => GetMessage('LM_AUTO_ANALOG_GROUP_3'),
    'LM_AUTO_ANALOG_GROUP_4'                    => GetMessage('LM_AUTO_ANALOG_GROUP_4'),
    'LM_AUTO_ANALOG_GROUP_5'                    => GetMessage('LM_AUTO_ANALOG_GROUP_5'),
    'LM_AUTO_ANALOG_GROUP_6'                    => GetMessage('LM_AUTO_ANALOG_GROUP_6'),
    'LM_AUTO_ANALOG_GROUP_10'                   => GetMessage('LM_AUTO_ANALOG_GROUP_10'),
);
