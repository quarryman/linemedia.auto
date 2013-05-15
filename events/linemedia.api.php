<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);

/**
 * Linemedia Autoportal
 * Main module
 * Module events for API
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
class LinemediaEventApi
{

    /**
     * Проверка и изменение главного меню в зависимости от настроек.
     */
    public function OnModulesScan_AddAPI(&$folders)
    {
        $folders []= $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/classes/general/api/';
    }
    
    /*
    * Добавление в вывод апи информации о статусе в заявке поставщика
    */
    public static function OnBeforeBasketItemStatus($basket_id, $status)
    {
        $stock_id       = COption::GetOptionString('linemedia.auto', "LM_AUTO_MAIN_STATUS_STORED", "");
        $rejected_id    = COption::GetOptionString('linemedia.auto', "LM_AUTO_MAIN_STATUS_REJECTED", "");
        $requested_id   = COption::GetOptionString('linemedia.auto', "LM_AUTO_MAIN_STATUS_REQUESTED", "");
        
        if ($status == $stock_id || $status == $rejected_id || $status == $requested_id) {
            $props = array(0 => array(
                    'NAME'  => 'Status change time '.$status,
                    'CODE'  => 'status_time_'.$status,
                    'VALUE' => time(),
                    'SORT'  => 4096
                )
            );
//             file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.__FUNCTION__.'.log', print_r(func_get_args(), 1)."\n\n", FILE_APPEND);
            LinemediaAutoBasket::setProperty($basket_id, $props);
        }
    }

}
