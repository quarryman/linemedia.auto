<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


/**
 * Linemedia Autoportal
 * Main module
 * Module events for sale
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */



class LinemediaAutoEventSale
{
    /**
     * ��� ���������� ������ ���������� ��������� ���������� ������� � ����
     * TODO: � ����� ��������� �� �����������
     */
    function OnOrderAdd_DescreasePartsCount($ID, $arFields)
    {
        /*
         * � ���������� ������ ����� ���� ��������� ���������� ���������� �������
         */
        $decrease_quantity = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING') == 'Y';
        if (!$decrease_quantity) {
            return;
        }
        
        /*
         * ������� ������ ������
         */
        $order = new LinemediaAutoOrder($ID);
        $baskets = $order->getBaskets();
        
        $search = new LinemediaAutoSearch();
        foreach ($baskets as $basket) {
            /*
             * ����� �������� �� �������
             */
            $search_part = array(
                'article'       => $basket['PROPS']['article']['VALUE'],
                'brand_title'   => $basket['PROPS']['brand_title']['VALUE'],
                'supplier_id'   => $basket['PROPS']['supplier_id']['VALUE']
            );
            $part_data = $search->getLocalDatabaseArticle($search_part);
            
            LinemediaAutoDebug::add('Decrease part count', print_r($part_data, 1));
            
            /*
             * �������� � ����������
             */
            $part = new LinemediaAutoPart($part_data['id']);
            $part->setQuantity($part_data['quantity'] - $basket['QUANTITY']);
        }
    }


    /**
     * �������� �� �������� ������� ��� ������ � 1C.
     */
    function OnBeforeBasketDelete_checkBasket1CExchange($ID)
    {
        if (strpos($_SERVER['PHP_SELF'], '/bitrix/admin/1c_exchange.php') !== false) {
            return false;
        }
    }
    
    
    /**
     * ����������� ������������ � ������.
     
    function OnSalePayOrder_checkUserGroups($ID, $pay)
    {
        $order = CSaleOrder::GetByID($ID);
        
        // ���������� �� ������������ � ������ ��� ������ ������.
        $goback = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_GROUP_TRANSFER_BACK', 'N');
        
        if ($goback != 'N' || $pay != 'N') {
            //return;
        
        
            try {
                $transfer = new LinemediaAutoGroupTransfer($order['USER_ID']);
                $groups = $transfer->getUserGroups();
                
                $user = new CUser();
                $user->Update($order['USER_ID'], array('GROUP_ID' => $groups));
                
            } catch (Exception $e) {
                // nothing...
            }
        }
    }*/
    
    
    /**
     * ��������� ������� ������.
     */
    function OnSaleStatusOrder_updateBasketStatuses($ID, $val)
    {
        if (!isset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_BASKET']) || $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_BASKET'] != true) {
            $basket = new LinemediaAutoBasket();
            $order  = new LinemediaAutoOrder($ID);
            
            $_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_ORDER'] = true;
            // �������� �������� ������.
            $arBaskets = $order->getBaskets();
            foreach ($arBaskets as $arBasket) {
                $basket->statusItem($arBasket['ID'], $val);
            }
            unset($_SESSION['LM_AUTO_MAIN_EVENT_SELF']['SET_STATUS_ORDER']);
        }
    }
    
    
    /**
     * ��������� ������� "�������" � ������� linemdia ��� ������ � ������� -> ������.
     */
    public function OnSalePayOrder_SetPayBaskets($ID, $val)
    {
        $order    = new LinemediaAutoOrder($ID);
        $lmbasket = new LinemediaAutoBasket();
        
        // ������ ������ ������.
        $baskets = $order->getBaskets();
        
        foreach ($baskets as $key => $basket) {
            $lmbasket->payItem($basket['ID'], $val);
        }
    }
    
    
    /**
     * ��������� ������� "�������" � ������� linemdia ��� ������ � ������� -> ������.
     */
    public function OnSaleCancelOrder_SetCancelBaskets($ID, $val)
    {
        $order    = new LinemediaAutoOrder($ID);
        $lmbasket = new LinemediaAutoBasket();
        
        // ������ ������ ������.
        $baskets = $order->getBaskets();
        
        foreach ($baskets as $key => $basket) {
            $lmbasket->cancelItem($basket['ID'], $val);
        }
    }
    
}


