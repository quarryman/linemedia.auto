<?php
/**
 * Linemedia Autoportal
 * Main module
 * Debug all calculations
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 
IncludeModuleLangFile(__FILE__); 

class LinemediaAutoTecDocAuto
{
    protected $api = null;
    
    
    public function __construct()
    {
        $this->api = new LinemediaAutoApiDriver();
    }
    
    
    /**
     * Получение брендов.
     */
    public function getBrands()
    {
        $result = array();
        
        $response = $this->api->query('getBrands', $data = array());
        if (is_array($response) && $response['status'] === 'ok' && ($response['data']) > 0 ) {
            $result = $response['data'];
        }
        return $result;
    }
    
    
    /**
     * Получение модели.
     * 
     * @param int $brand_id
     */
    public function getModels($brand_id)
    {
        $result = array();
        
        $response = $this->api->query('getVehicleModels', $data = array('brand_id' => $brand_id));
        if (is_array($response) && $response['status'] === 'ok' && ($response['data']) > 0 ) {
            $result = $response['data'];
        }
        return $result;
    }
    
    
    /**
     * Получение модификаций.
     * 
     * @param int $brand_id
     * @param int $model_id
     */
    public function getModifications($brand_id, $model_id)
    {
        $result = array();
        
        $response = $this->api->query('getModelVariantsWithInfo', $data = array('brand_id' => $brand_id, 'model_id' => $model_id));
        if (is_array($response) && $response['status'] === 'ok' && ($response['data']) > 0 ) {
            $result = $response['data'];
        }
        return $result;
    }
}

