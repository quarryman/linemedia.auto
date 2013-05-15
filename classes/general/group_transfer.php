<?php


/**
 * Linemedia Autoportal
 * Main module
 * Price calculation class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);


/*
 * Класс, отвечающий за перевод пользователей по группам.
 */
class LinemediaAutoGroupTransfer
{
    protected $user_id = null;
    
    
    public function __construct($user_id)
    {
        $this->user_id = (int) $user_id;
        
        if ($this->user_id <= 0) {
            throw new Exception('Wrong user ID');
        }
    }
    
    
    /**
     * Получение ID пользователя.
     */
    public function getUserID()
    {
        return $this->user_id;
    }
    
    
    /**
     * Расчет общей суммы всех оплаченных пользователем заказов.
     */
    public function getUserSumm()
    {
        if (!CModule::IncludeModule('sale')) {
            return;
        }
        
        $summ = 0.0;
        
        $dbOrders = CSaleOrder::getList(array(), array('USER_ID' => $this->getUserID(), 'PAYED' => 'Y'), false, false, array('PRICE'));
        while ($arOrder = $dbOrders->Fetch()) {
            $summ += (float) $arOrder['PRICE'];
        }
        return $summ;
    }
    
    
    /**
     * Нахождение подходящих переводов.
     * 
     * @param float $summ
     */
    public function getSuitableGroupsList($summ = null)
    {
        // Сумма для поиска подходящих переводов.
        $summ = (!empty($summ)) ? (floatval($summ)) : ($this->getUserSumm());
        
        $arSort = array(
            'PROPERTY_SUMM' => 'ASC'
        );
        
        $arFilter = array(
            'ACTIVE'            => 'Y',
            'ACTIVE_DATE'       => 'Y',
            '<=PROPERTY_SUMM'   => $summ
        );
        
        $arGroupTransfers = self::getList($arSort, $arFilter);
        
        return $arGroupTransfers;
    }
    
    
    /**
     * Получение нужных групп пользователей.
     */
    public function getUserGroups()
    {
        // Группы, удовлетворяющие накопленной сумме пользователя.
        $arGroupTransfers = $this->getSuitableGroupsList();
        
        $arUserGroupsIn     = array(); // Группы для добавления
        $arUserGroupsOut    = array(); // Группы для удаления
        
        // Текущие группы пользователя.
        $arUserGroups = (array) CUser::GetUserGroup($this->getUserID());
        
        foreach ($arGroupTransfers as $arGroupTransfer) {
            $arUserGroupsIn  = array_filter((array) $arGroupTransfer['PROPS']['groups_in']['VALUE']);
            $arUserGroupsOut = array_filter((array) $arGroupTransfer['PROPS']['groups_out']['VALUE']);
            
            // Уберем лишние группы.
            $arUserGroups = array_diff($arUserGroups, $arUserGroupsOut);
            
            // Добавим нужные группы.
            $arUserGroups = array_merge($arUserGroups, $arUserGroupsIn);
        }
        $arUserGroups = array_unique($arUserGroups);
        
        return $arUserGroups;
    }
    
    
    /**
     * Список переводов по группам.
     * 
     * @param array $arSort
     * @param array $arFilter
     */
    public static function getList($arSort = array(), $arFilter = array())
    {
        if (!CModule::IncludeModule('iblock')) {
            return;
        }
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_GROUP_TRANSFER');
        
        $arSort     = (array) $arSort;
        $arFilter   = (array) $arFilter;
        
        $arFilter['IBLOCK_ID'] = $iblock_id;
        
        $arTransfers = array();
        $dbres = CIBlockElement::GetList($arSort, $arFilter, false, false, array());
        while ($dbTransfer = $dbres->GetNextElement()) {
            $arTransfer          = $dbTransfer->GetFields();
            $arTransfer['PROPS'] = $dbTransfer->GetProperties();
            
            $arTransfers[$arTransfer['ID']] = $arTransfer;
        }
        return $arTransfers;
    }
    
}
