<?php

/**
 * Linemedia Autoportal
 * Main module
 * Suppliers
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
 * ����������.
 */
class LinemediaAutoSupplier
{
    protected $id;
    
    protected $loaded = false;
    
    protected $data = array();
    
    
    public function __construct($id)
    {
        /*
         * ����� ���������� ����������
         */
        LinemediaAutoDebug::add('Supplier object created (ID ' . $id . ')');
        
        $this->id = (string) $id;
    }
    
    
    /**
     * �������� ������ � ����������.
     */
    protected function load()
    {
        if (empty($this->id)) {
            return;
        }
        
        if ($this->loaded) {
            return;
        }
        
        $this->loaded = true;
        
        
        $obCache = new CPHPCache();
		$life_time = 30 * 60; 
		$cache_id = 'supplier_' . $this->id; 
		if ($obCache->InitCache($life_time, $cache_id, "/lm_auto/suppliers")) {
		    $cache = $obCache->GetVars();
		    $supplier = $cache['supplier'];
		} else {
	        CModule::IncludeModule('iblock');
	        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
	        
	        $supplier_res = CIBlockElement::GetList(
	            array(),
	            array(
	                'IBLOCK_ID' => $iblock_id,
	                'PROPERTY_supplier_id' => $this->id
	            ),
	            false,
	            false,
	            array('ID', 'NAME', 'CODE', 'ACTIVE')
	        );
	        
	        if ($supplier = $supplier_res->Fetch()) {
	            
	            $db_props = CIBlockElement::GetProperty($iblock_id, $supplier['ID'], array('sort' => 'asc'));
	            
	            while ($prop = $db_props->Fetch()) {
	                $supplier['PROPS'][$prop['CODE']] = $prop;
	            }
	            
	            /*
	             * ����� ���������� ����������
	             */
	            LinemediaAutoDebug::add('Supplier object loaded (ID ' . $this->id . ')', print_r($supplier, true));
	            
			    
	        } else {
	            /*
	             * ����� ���������� ����������
	             */
	            LinemediaAutoDebug::add('Supplier object load error 404 (ID ' . $this->id . ')');
	            //$obCache->AbortDataCache();
	        }
	        
	        if ($obCache->StartDataCache()) {
		        $obCache->EndDataCache(array(
		        	'supplier' => $supplier,
		        ));
	        } 
		}
		
        /*
         * C����� �������
         */
        $events = GetModuleEvents('linemedia.auto', 'OnAfterSupplierLoaded');
	    while ($arEvent = $events->Fetch()) {
		    ExecuteModuleEventEx($arEvent, array(&$supplier));
	    }
		
		$this->data = $supplier;
                
    }
    
    
    /**
     * ��������� ����.
     */
    public function get($field)
    {
        $this->load();
        if (isset($this->data[$field])) {
            return $this->data[$field];
        }
        return $this->data['PROPS'][$field]['VALUE'];
    }
    
    
    /**
     * �������� ��� ����.
     */
    public function getArray()
    {
        $this->load();
        return $this->data;
        
    }
    
    
    /**
     * ������������� ����������.
     */
    public function exists()
    {
        $this->load();
        return count($this->data) > 0;
    }
    
    
    /**
     * ��������� ������ �����������
     */
    public static function GetList($order = array("SORT" => "ASC"), $filter = array(), $group = false, $nav = false, $select = array('ID', 'NAME', 'CODE', 'ACTIVE'), $code = 'id')
    {
        if (!CModule::IncludeModule('iblock')) {
            return;
        }
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        $filter['IBLOCK_ID'] = $iblock_id;
        
        $suppliers = array();
        $supplier_res = CIBlockElement::GetList($order, $filter, $group, $nav, $select);
        while ($supplier = $supplier_res->Fetch()) {
            $db_props = CIBlockElement::GetProperty($iblock_id, $supplier['ID'], array('sort' => 'asc'));
            while ($prop = $db_props->Fetch()) {
                $supplier['PROPS'][$prop['CODE']] = $prop;
            }
            
            // ����
            $key = $supplier['ID'];
            switch ($code) {
                case 'id':
                    $key = $supplier['ID'];
                    break;
                case 'supplier_id':
                    $key = $supplier['PROPS']['supplier_id']['VALUE'];
                    break;
            }
            $suppliers[$key] = $supplier;
        }
        
        return $suppliers;
    }
    
    
    /**
     * ��������� ������ ��������� �����������.
     */
    public static function getRemoteSuppliersList()
    {
        $suppliers = array();
        $events = GetModuleEvents('linemedia.auto', 'OnRemoteSuppliersGet');
        while ($arEvent = $events->Fetch()) {
            ExecuteModuleEventEx($arEvent, array(&$suppliers));
        }
        return $suppliers;
    }
    
    
    /**
     * �������� ID ���������� �� ������������.
     * 
     * @param string $id
     * @return bool
     */
    public static function isUniqueSupplierId($id)
    {
        if (!CModule::IncludeModule('iblock')) {
            return;
        }
        $id = (string) $id;
        
        if (empty($id)) {
            return false;
        }
        
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        $db = CIBlockElement::GetList(
            array(),
            array('IBLOCK_ID' => $iblock_id, 'PROPERTY_supplier_id' => $id),
            false,
            false,
            array('ID')
        );
        
        return ($db->SelectedRowsCount() <= 1);
    }
    
    
    /**
     * �������� ���������� �� ����� ID ����������.
     * 
     * @param string $id
     * @return bool
     */
    public static function existsSupplierId($id)
    {
        if (!CModule::IncludeModule('iblock')) {
            return;
        }
        $id = (string) $id;
        
        if (empty($id)) {
            return false;
        }
        
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        $db = CIBlockElement::GetList(
            array(),
            array('IBLOCK_ID' => $iblock_id, 'PROPERTY_supplier_id' => $id),
            false,
            false,
            array('ID')
        );
        
        return ($db->SelectedRowsCount() > 0);
    }
    
    
    /**
     * ��������� ID ����������.
     */
    public static function generateSupplierId()
    {
        if (!CModule::IncludeModule('iblock')) {
            return;
        }
        $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
        
        $db = CIBlockElement::GetList(
                array(),
                array('IBLOCK_ID' => $iblock_id),
                false,
                false,
                array('ID', 'PROPERTY_supplier_id')
        );
        $supplier_ids = array();
        while ($item = $db->Fetch()) {
            $supplier_ids []= (string) $item['PROPERTY_SUPPLIER_ID_VALUE'];
        }
        
        $id = '1';
        while (in_array($id, $supplier_ids)) {
            $id++;
        }
        return strval($id);
    }


    /**
     * �������� ���������� ������� ���������� � ���������� �������� �������� ����������� (�� ����).
     */
    public static function getStat($id)
    {
        if (!CModule::IncludeModule('sale')) {
            return array();
        }

        $c = new CPHPCache;
        $cache_id = 'supplier_stat_'.$id;
        if (0&&$c->InitCache(12*3600, $cache_id, '/supplier_stat/'.$id.'/')) {
            return $c->GetVars();
        } else {
            $stock_id       = COption::GetOptionString('linemedia.auto', "LM_AUTO_MAIN_STATUS_STORED", "");
            $rejected_id    = COption::GetOptionString('linemedia.auto', "LM_AUTO_MAIN_STATUS_REJECTED", "");
            $requested_id   = COption::GetOptionString('linemedia.auto', "LM_AUTO_MAIN_STATUS_REQUESTED", "");
            
            if (!($stock_id && $rejected_id && $requested_id)) {
                return array();
            }
            $rs = CSaleBasket::GetPropsList(array('BASKET_ID' => 'ASC'), array('CODE' => 'supplier_id', 'VALUE' => $id), 0, 0, array('BASKET_ID'));
            $basket_ids = array();

            while ($cart = $rs->Fetch()) {
                $basket_ids[] = $cart['BASKET_ID'];
            }
            if (empty($basket_ids)) { // ���� ��� ��� ������, �� ������� ������� ����������.
                return array();
            }
            $counters = array();
            $total = 0;

            $rs = CSaleBasket::GetPropsList( array('BASKET_ID'=>'ASC'), array('BASKET_ID'=>$basket_ids,'CODE'=>'status'), array('VALUE'));

            while ($cart = $rs->Fetch()) {
                if (!in_array($cart['VALUE'], array($stock_id, $rejected_id))) {
                    continue;
                }
                $counters[ $cart['VALUE'] ] = $cart['CNT'];
                $total += $cart['CNT'];
            }

            /*
             * ���������� �� ������� ��������. ������� ������� ����� �������� � ������ "�� ������", ����� ������ �� ���� �����,����� ��������� ������ ����������.
             * � ������ ���������� � ������ ���-�� ����=> % ����������� �������.
             */
            $rs = CSaleBasket::GetPropsList(
                array('BASKET_ID' => 'ASC'),
                array('BASKET_ID' => $basket_ids, 'CODE' => 'status_time_'.$stock_id ),
                false,
                false,
                array('CODE', 'BASKET_ID', 'VALUE')
            );
            $timings = array();

            while ($cart = $rs->Fetch()) {
                $timings[ $cart['BASKET_ID'] ] = $cart["VALUE"];
            }
            
            $rs = CSaleBasket::GetPropsList(
                array('BASKET_ID' => 'ASC'),
                array('BASKET_ID' => $basket_ids, 'CODE' => 'status_time_'.$requested_id ),
                false,
                false,
                array('CODE', 'BASKET_ID', 'VALUE')
            );

            $n = 0;

            while ($cart = $rs->Fetch()) {
                $key = (int)round( ((int)$timings[ $cart['BASKET_ID'] ] - (int)$cart["VALUE"]) / (3600 * 24) ); // ������� � ���
                if ($key < 0) {
                    continue; // ���� ��������� ��������
                }
                if (!isset($counters['timings'][ $key ])) {
                    $counters['timings'][ $key ] = 0;
                }
                $counters['timings'][ $key ]++;
                $n++;
            }

            $n = 100 / $n; // ����� ����� ��� �� �������� ����� �� 100 ��� ��������� ���������.

            /*
             * ���������� � ��������� ���������� �������� � ��������.
             */
            foreach ($counters['timings'] as $k => $v) {
                $counters['timings'][$k] = round($v * $n);
            }

            unset($timings);

            ksort($counters['timings']);

            $ret = array(
                'delivery_time' => $counters['timings'],
                'rejected'      => round(intval($counters[$rejected_id]) / $total * 100),
                'completed'     => round(intval($counters[$stock_id]) / $total * 100)
            );
            $c->StartDataCache();
            $c->EndDataCache($ret);
            return $ret;
        }
    }

}



