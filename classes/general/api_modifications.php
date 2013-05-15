<?php
/**
 * Linemedia Autoportal
 * Main module
 * Low-level modifications to Linemedia API
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__); 

class LinemediaAutoApiModifications
{
	const LOCAL_ID_KEY = 'lm_mod_id';
    
    
	/*
	 * ID ����
	 */
	protected $set_id = 'default';
	
    
    
	public function changeSetId($set_id)
	{
		$this->set_id = (string) $set_id;
	}
	
    
	public static function getSetsIds()
	{
		global $DB;
	    $ids = array();
	    
	    $sql = "SELECT DISTINCT `set_id` FROM `b_lm_api_modifications`";
	    $res = $DB->Query($sql);
	    while ($mod = $res->Fetch()) {
		    $ids []= $mod['set_id'];
	    }
	    return $ids;
	}


    protected function iconvArray($array, $from = 'UTF-8', $to = 'cp1251')
    {
        if (empty($array) || !is_array($array)) {
            return array();
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::iconvArray($value, $from, $to);
            } else {
                $result[$key] = iconv($from, $to, $value);
            }
        }
        return $result;
    }
/**
    ��� ��� json_decode �� �������� � ���������� winddows-1251, � ����� � ��� ��� ����������, �� ���������� ������ ��� ����� �����.
    ��������� ���� ������ ���� �� ����, �.�. ������ �� ������ �������� ������ �� ������ �����, ���� ��� �������� ����� ajax, � ����
    �������� � utf-8.
*/
    protected function json2arr($data, $assoc)
    {
        try {
            $data = json_decode($data, $assoc);
        } catch (Exception $e) {
            $data = array();
        }
        if (!defined('BX_UTF') || BX_UTF!==true) {
            return self::iconvArray($data);
        }
        return $data;
    }
	
    /**
     * ��������� ��������� � ������.
     * 
     * @param string $cmd
     * @param array $args
     * @param mixed $response
     */
    public function applyModifications($cmd, $args, &$response)
    {
    	switch ($cmd) {
	    	case 'getBrands2':
	    	case 'getDetailBrands':
	    		$modifications = $this->getModificationsList('brand');
	    		
	    		/*
	    		 * ��� ��������� �� �������
	    		 */
	    		foreach ($modifications as $mod) {
	    			/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			
	    			/*
		    		 * ����� ����� ���� ������?
		    		 */
	    			$source_id = (string) $mod['source_id'];
	    			
	    			/*
		    		 * ���� ���� �������� ����� �����
		    		 */
		    		if ($data[self::LOCAL_ID_KEY] == 'Y') {
		    		    // ������� ID �� ��������� ����, ���� ����.
		    		    $data['id']         = (int) $mod['id'];
                        $data['source_id']  = (int) $mod['source_id'];
                        
			    		$response['brands'] []= $data;
		    		} else {
		    			/*
			    		 * ����� ������ �������, ������� ��� � ������� � ��������� �����������
			    		 */
			    		foreach ($response['brands'] as $i => $item) {
				    		if ($item['manuId'] == $source_id) {
				    		    
					    		$response['brands'][$i] = array_replace_recursive($item, $data);
					    		break;
				    		}
			    		}
		    		}
	    		}
	    	    break;
                
	    	case 'getBrandId':
	    		$modifications = $this->getModificationsList('brand');
	    		
	    		/*
	    		 * ��� ��������� �� �������
	    		 */
	    		foreach($modifications AS $mod) {
	    			/*
		    		* ���������� ���������
		    		*/
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			
	    			if ($data['manuName'] != $args['brand_title']) {
	    				continue;
                    }
	    			$response = $mod['source_id'];
	    		}
	    	    break;
                
	    	case 'getBrandNameById':
	    		$modifications = $this->getModificationsList('brand', $args['brand_id']);
	    		
	    		/*
	    		 * ��� ��������� �� �������
	    		 */
	    		foreach ($modifications as $mod) {
	    			if ($mod['source_id'] != $args['brand_id']) {
	    				continue;
                    }
                    
	    			/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			$response = $data['manuName'];
	    		}
	    	    break;
                
	    	case 'getVehicleModels2':
	    		$modifications = $this->getModificationsList('model', false, $args['brand_id']);
	    		/*
	    		 * ��� ��������� �� �������
	    		 */
	    		foreach ($modifications as $mod) {
	    			/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			
	    			/*
		    		 * ����� ����� ���� ������?
		    		 */
	    			$source_id = (string) $mod['source_id'];
	    			
	    			/*
		    		 * ���� ���� �������� ����� �����
		    		 */
		    		if ($data[self::LOCAL_ID_KEY] == 'Y') {
		    		    // ������� ID �� ��������� ����, ���� ����.
                        $data['id']         = (int) $mod['id'];
                        $data['source_id']  = (int) $mod['source_id'];
                        
			    		$response['models'][] = $data;
		    		} else {
		    			/*
			    		 * ����� ������ �������, ������� ��� � ������� � ��������� �����������
			    		 */
			    		foreach($response['models'] as $i => $item) {
				    		if ($item['modelId'] == $source_id) {
					    		$response['models'][$i] = array_replace_recursive($item, $data);
					    		break;
				    		}
			    		}
		    		}
	    		}
	    		
	    		
	    		/*
	    		 * ���������� ����� � ������
	    		 */
	    		$modifications = $this->getModificationsList('brand', $args['brand_id']);
	    		if (count($modifications)) {
	    			$mod = $modifications[0];
		    		/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			if ($data['manuName']) {
	    				$response['brand']['title'] = $data['manuName'];
                    }
	    		}
	    	    break;
                
	    	case 'getModelVariantsWithCarInfo2':
	    		$modifications = $this->getModificationsList('modification', false, $args['brand_id'].':'.$args['model_id']);
	    		
	    		/*
	    		 * ��� ��������� �� �������
	    		 */
	    		foreach ($modifications as $mod) {
	    			/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			
	    			/*
		    		 * ����� ����� ���� ������?
		    		 */
	    			$source_id = (string) $mod['source_id'];
	    			
	    			/*
		    		 * ���� ���� �������� ����� �����
		    		 */
		    		if ($data[self::LOCAL_ID_KEY] == 'Y') {
		    		    // ������� ID �� ��������� ����, ���� ����.
                        $data['id']         = (int) $mod['id'];
                        $data['source_id']  = (int) $mod['source_id'];
                        
			    		$response['modifications'][] = $data;
		    		} else {
		    			/*
			    		 * ����� ������ �������, ������� ��� � ������� � ��������� �����������
			    		 */
			    		foreach ($response['modifications'] as $i => $item) {
				    		if ($item['carId'] == $source_id) {
					    		$response['modifications'][$i] = array_replace_recursive($item, $data);
					    		break;
				    		}
			    		}
		    		}
	    		}
	    		
	    		
	    		/*
	    		 * ���������� ����� � ������
	    		 */
	    		$modifications = $this->getModificationsList('brand', $args['brand_id']);
	    		if (count($modifications)) {
	    			$mod = $modifications[0];
		    		/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			if ($data['manuName']) {
	    				$response['brand']['title'] = $data['manuName'];
                    }
	    		}

	    		/*
	    		 * ���������� ������ � ������
	    		 */
	    		$modifications = $this->getModificationsList('model', $args['model_id']);
	    		if (count($modifications)) {
	    			$mod = $modifications[0];
		    		/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			if ($data['modelname']) {
	    				$response['model']['title'] = $data['modelname'];
                    }
	    		}
	    	    break;
	    	
	    	
	    	case 'getListOfGroups2':
	    		$modifications = $this->getModificationsList('group', false, $args['type_id']);
	    		
	    		/*
	    		 * ��� ��������� �� �������
	    		 */
	    		foreach ($modifications as $mod) {
	    			/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			
	    			/*
		    		 * ����� ����� ���� ������?
		    		 */
	    			$source_id = (string) $mod['source_id'];
	    			
	    			/*
		    		 * ���� ���� �������� ����� �����
		    		 */
		    		if ($data[self::LOCAL_ID_KEY] == 'Y') {
		    		    // ������� ID �� ��������� ����, ���� ����.
                        $data['id']         = (int) $mod['id'];
                        $data['source_id']  = (int) $mod['source_id'];
                        
			    		$response['groups'][] = $data;
		    		} else {
		    			/*
			    		 * ����� ������ �������, ������� ��� � ������� � ��������� �����������
			    		 */
                        $found = false;
			    		foreach ($response['groups'] as $i => $item) {
				    		if ($item['assemblyGroupNodeId'] == $source_id) {
					    		$response['groups'][$i] = array_replace_recursive($item, $data);
                                $found = true;
					    		break;
				    		}
			    		}//foreach
                        /**
                            ���� ��� ��������� ������,������� ���� �� �����, �� ��� ������� ���������� ����� -- ��������� ���.
                        */
                        if (!$found && (!isset($data['hidden']) || $data['hidden']!='Y') && !empty($data['assemblyGroupName']) ) {
                            $data['id']         = (int) $mod['id'];
                            $data['source_id']  = (int) $mod['source_id'];
                            $response['groups'][] = $data;
                        }
		    		}
	    		}
	    		
	    		/*
	    		 * ���������� ����� � ������
	    		 */
	    		$modifications = $this->getModificationsList('brand', $args['brand_id']);
	    		if (count($modifications)) {
	    			$mod = $modifications[0];
		    		/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			if ($data['manuName']) {
	    				$response['brand']['title'] = $data['manuName'];
                    }
	    		}

	    		/*
	    		 * ���������� ������ � ������
	    		 */
	    		$modifications = $this->getModificationsList('model', $args['model_id']);
	    		if (count($modifications)) {
	    			$mod = $modifications[0];
		    		/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			if ($data['modelname']) {
	    				$response['model']['title'] = $data['modelname'];
                    }
	    		}
	    		
	    		
	    		/*
	    		 * ���������� ����������� � ������
	    		 */
	    		$modifications = $this->getModificationsList('modification', $args['type_id']);
	    		if (count($modifications)) {
	    			$mod = $modifications[0];
		    		/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			if ($data['carName']) {
	    				$response['modification']['title'] = $data['carName'];
                    }
	    		}
                break;
                
	    	case 'getDetails2':
	    		
	    		$modifications = $this->getModificationsList('part', false, $args['type_id'].':'.$args['group_id']);
	    		
	    		/*
	    		 * ��� ��������� �� �������
	    		 */
	    		foreach ($modifications as $mod) {
	    			/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			
	    			/*
		    		 * ����� ����� ���� ������?
		    		 */
	    			$source_id = (string) $mod['source_id'];
	    			
	    			/*
		    		 * ���� ���� �������� ����� �����
		    		 */
		    		if ($data[self::LOCAL_ID_KEY] == 'Y') {
		    		    // ������� ID �� ��������� ����, ���� ����.
                        $data['id']         = (int) $mod['id'];
                        $data['source_id']  = (int) $mod['source_id'];
                        
			    		$response['parts'][] = $data;
		    		} else {
		    			/*
			    		 * ����� ������ �������, ������� ��� � ������� � ��������� �����������
			    		 */
			    		foreach ($response['parts'] as $i => $item) {
				    		if ($item['articleId'] == $source_id) {
					    		$response['parts'][$i] = array_replace_recursive($item, $data);
					    		break;
				    		}
			    		}
		    		}
	    		}
	    		
                
	    		/*
	    		 * ���������� ����� � ������
	    		 */
	    		$modifications = $this->getModificationsList('brand', $args['brand_id']);
	    		if (count($modifications)) {
	    			$mod = $modifications[0];
		    		/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			if ($data['manuName']) {
	    				$response['brand']['title'] = $data['manuName'];
                    }
	    		}

	    		/*
	    		 * ���������� ������ � ������
	    		 */
	    		$modifications = $this->getModificationsList('model', $args['model_id']);
	    		if (count($modifications)) {
	    			$mod = $modifications[0];
		    		/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			if ($data['modelname']) {
	    				$response['model']['title'] = $data['modelname'];
                    }
	    		}
	    		
	    		
	    		/*
	    		 * ���������� ����������� � ������
	    		 */
	    		$modifications = $this->getModificationsList('modification', $args['type_id']);
	    		if (count($modifications)) {
	    			$mod = $modifications[0];
		    		/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			if ($data['carName']) {
	    				$response['modification']['title'] = $data['carName'];
                    }
	    		}
	    		
	    		/*
	    		 * ���������� ������ � ������
	    		 */
	    		$modifications = $this->getModificationsList('group', $args['group_id']);
	    		if (count($modifications)) {
	    			$mod = $modifications[0];
		    		/*
		    		 * ���������� ���������
		    		 */
	    			try {
		    			$data = self::json2arr($mod['data'], true);
	    			} catch (Exception $e) {
		    			$data = array();
	    			}
	    			if ($data['assemblyGroupName']) {
	    				$response['group']['title'] = $data['assemblyGroupName'];
	    		    }
	    		}
	    		break;
    	}
	}
    
    
    /**
     * �������� ������ ����������� ���������.
     * 
     * @param string $type
     * @param mixed $source_id
     * @param mixed $parent_id
     */
    public function getModificationsList($type, $source_id = false, $parent_id = false, $exact = false)
    {
    	global $DB;
	    $modifications = array();
	    
	    $set_id  	= $DB->ForSql($this->set_id);
	    
	    $type  		= $DB->ForSql($type);
	    $source_id  = $DB->ForSql($source_id);
	    $parent_id  = $DB->ForSql($parent_id);
	    
	    
	    $sql = "SELECT * FROM `b_lm_api_modifications` WHERE `type` = '$type' AND `set_id` = '$set_id'";
	    if ($source_id != false) {
	    	$sql .= " AND `source_id` = '$source_id'";
        }
	    if ($parent_id != false) {
            if (!$exact) {
                $sql .= " AND (`parent_id` = '$parent_id' OR `parent_id`='*') ORDER BY `parent_id` ASC";
            } else {
                $sql .= " AND `parent_id` = '$parent_id'";
            }
        }
        
	    $res = $DB->Query($sql);
	    while ($mod = $res->Fetch()) {
		    $modifications []= $mod;
	    }
	    return $modifications;
    }
    
    
    private function cleanStr($str)
    {
        return preg_replace('#[^a-z0-9\-_:]#is', '', $str);
    }
    /**
     * �������� ���������.
     * 
     * @param string $type
     * @param mixed $source_id
     * @param mixed $parent_id
     * @param mixed $data
     */
    public function addModification($type, $source_id, $parent_id, $data)
    {
    	global $DB;
	    
	    /*
	     * ��� ����� ����������� ���� ��������� �������!
	     */
	    if ($source_id == '') {
		    switch ($type) {
			    case 'brand':
                    $data['manuId'] = $this->cleanStr($data['manuId']);
                    if (empty($data['manuId'])) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_NO_BRAND_ID'));
                    }
                    if (empty($data['manuName'])) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_NO_BRAND_NAME'));
                    }
			    	$source_id = $data['manuId'] = '_' . $data['manuId'];
                    $tmp = $this->getModificationsList('brand', $source_id, false);
                    if (!empty($tmp)) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_DUP_BRAND'));
                    }
                    break;
			    case 'model':
                    $data['modelId'] = $this->cleanStr($data['modelId']);
                    if (empty($data['modelId'])) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_NO_MODEL_ID'));
                    }
                    if (empty($data['modelname'])) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_NO_MODEL_NAME'));
                    }

			    	$source_id = $data['modelId'] = '_' . $data['modelId'];

                    $tmp = $this->getModificationsList('model', $source_id, $parent_id);
                    if (!empty($tmp)) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_DUP_MODEL'));
                    }

                    break;
			    case 'modification':
                    $data['carId'] = $this->cleanStr($data['carId']);
                    if (empty($data['carId'])) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_NO_CAR_ID'));
                    }
                    if (empty($data['carName'])) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_NO_CAR_NAME'));
                    }
			    	$source_id = $data['carId'] = '_' . $data['carId'];

                    $tmp = $this->getModificationsList('modification', $source_id, $parent_id);
                    if (!empty($tmp)) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_DUP_MODIF'));
                    }
                    break;
			    case 'group':
                    $data['assemblyGroupNodeId'] = $this->cleanStr($data['assemblyGroupNodeId']);
                    if (empty($data['assemblyGroupName'])) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_NO_GROUP_NAME'));
                    }
                    if (empty($data['assemblyGroupNodeId'])) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_NO_GROUP_ID'));
                    }
			    	$source_id = $data['assemblyGroupNodeId'] = '_' . $data['assemblyGroupNodeId'];
                    /*if($parent_id!='*') {
                        $tmp = $this->getModificationsList('group', $source_id, $parent_id);
                        if (!empty($tmp)) {
                            throw new Exception(GetMessage('LM_AUTO_APIM_DUP_GROUP'));
                        }
                    }*/
                    break;
                case 'part':
                    if (empty($data['articleNo']) || empty($data['brandName'])) {
                        throw new Exception(GetMessage('LM_AUTO_APIM_NO_BRAND_OR_ARTICLE'));
                    }
                    if (empty($data['articleId'])) {//������� ������� ������������ �������������� ������� ��� ����.
                        $data['articleId'] = md5($data['articleNo'].'|'.$data['brandName']);
                    }
                    break;
                default:
                    throw new Exception(GetMessage('LM_AUTO_APIM_UNKNOWN_ENTITY_TYPE', array('#TYPE#'=>$type)));
                
		    }
            $source_id = $this->cleanStr($source_id);
            $data[self::LOCAL_ID_KEY] = 'Y';
	    }//if empty source_id
	    
	    // ���� ��� ���������������� �������, �������� ��� ������.
 	    if (empty($source_id)) {
 	        $source_id = time();
            $data[self::LOCAL_ID_KEY] = 'Y';
        }
	    
	    $set_id  	= $DB->ForSql($this->set_id);
	    $type  		= $DB->ForSql($type);
	    $source_id  = $DB->ForSql($source_id);
	    $parent_id  = $DB->ForSql($parent_id);
	    $data 		= $DB->ForSql(json_encode($data));
	    
	    $sql = "
	       INSERT INTO `b_lm_api_modifications` (`type`, `set_id`, `source_id`, `parent_id`, `data`)
	       VALUES ('$type', '$set_id', '$source_id', '$parent_id', '$data')
	       ON DUPLICATE KEY UPDATE data = '$data';
        ";
	    $DB->Query($sql);
        
	    return (int) $DB->LastID();
	}
	
    
	/**
     * ������� ���������.
     * 
     * @param string $type
     * @param mixed $source_id
     * @param mixed $parent_id
     */
    public function delModification($type, $source_id, $parent_id)
    {
    	global $DB;
    	
    	$set_id  	= $DB->ForSql($this->set_id);
    	$type  		= $DB->ForSql($type);
	    $source_id  = $DB->ForSql($source_id);
	    $parent_id  = $DB->ForSql($parent_id);

	    $sql = "
	       DELETE FROM `b_lm_api_modifications`
	       WHERE   `type` = '$type'
	           AND `source_id` = '$source_id'
	           AND `parent_id` = '$parent_id'
	           AND `set_id` = '$set_id';
        ";
	    $DB->Query($sql);
	}
    
    
    /**
     * ��� ������� ��������� ��� �������������� ��������� ������� ������.
     * 
     * @param int $id
     * @param mixed $data
     */
    public function updateModificationById($id, $data)
    {
	    global $DB;
	    $data  = $DB->ForSql(json_encode($data));
	    $id    = $DB->ForSql($id);
	    
	    $sql = "UPDATE `b_lm_api_modifications` SET `data` = '$data' WHERE `id` = '$id';";
	    $DB->Query($sql);
    }
    
    
    /**
     * �������� ���������.
     * 
     * @param int $id
     */
    public function deleteModificationById($id)
    {
	    global $DB;
	    $id = $DB->ForSql($id);
        
	    $sql = "DELETE FROM `b_lm_api_modifications` WHERE `id` = '$id';";
	    $DB->Query($sql);
    }
    
    
    /**
     * ��������� ���������.
     * 
     * @param string $type
     * @param mixed $source_id
     * @param mixed $parent_id
     * @param bool $visible
     */
    public function setVisibility($type, $source_id, $parent_id, $visible = true)
    {
	    $existing = $this->getModificationsList($type, $source_id, $parent_id);

	    $existing = isset($existing[0]) ? self::json2arr($existing[0]['data'], true) : false;
	    if ($visible) {
		    /*
		     * ���� ������ ��� � �������� ����� - �� ���� ������ ���������
		     */
		    if ($existing == false) {
		    	return;
            }
            
		    /*
		     * ���� ������ �������� ������ �� ����������� - ������ �� ��� �������, ��� �������� ���������(����� �� ������� �������� ���������)
		     */
		    if ($existing && count($existing) == 1 && $parent_id == $existing['parent_id'] && isset($existing['hidden']) ) {
		    	$this->delModification($type, $source_id, $parent_id);
		    	return;
		    }
		    
		    /*
		     * ��������� � ������������ ������ �������� ���������
		     */
		    $existing['hidden'] = 'N';
		    $this->addModification($type, $source_id, $parent_id, $existing);
		    
	    } else {
	    	/*
		     * ��������� � ������ �������� ���������
		     */
	    	$existing['hidden'] = 'Y';
		    $this->addModification($type, $source_id, $parent_id, $existing);
	    }
    }
    
    
    
    /**
     * �������� ������� ������ ����� ����.
     * 
     * @param string $type
     * @param mixed $source_id
     * @param mixed $parent_id
     * @param bool $ignore
     */
    public function getTypeExample($type, $source_id = false, $parent_id = false, $ignore = true)
    {
	    switch ($type) {
		    case 'brand':
		    	$data = $this->getItemById($type, $source_id, $parent_id, $ignore);
		    	if ($source_id) {
			    	return $data;
                }
			    return $data[0];
                break;
                
		    case 'model':
		    	$data = $this->getItemById($type, $source_id, $parent_id, $ignore);
		    	
		    	/*
		    	 * ���� ������ �� �������, ����� ������� �� ���������
		    	 */
		    	if (count($data) == 0) {
		    		$data = $this->getItemById($type, false, 120); // VOLVO
		    		return $data[0];
		    	}
		    	
		    	if ($source_id) {
			    	return $data;
                }
			    return $data[0];
                break;
                
		    case 'modification':
		    	$data = $this->getItemById($type, $source_id, $parent_id, $ignore);
		    	
		    	
		    	/*
		    	 * ���� ������ �� �������, ����� ������� �� ���������
		    	 */
		    	if (count($data) == 0) {
		    		$data = $this->getItemById($type, false, '5:8554'); // AUDI 80  1.7
		    		
		    		return $data;
		    	}
		    	
		    	return $data;
                break;
                
		    case 'group':
		    	$data = $this->getItemById($type, $source_id, $parent_id, $ignore);
		    	/*
		    	 * ���� ������ �� �������, ����� ������� �� ���������
		    	 */
		    	if (count($data) == 0) {
		    		$data = $this->getItemById($type, false, 6913); // AUDI 80  1.7 
		    		
			    	return $data;
		    	}
		    	
		    	return $data;
                break;
                
		    case 'part':
		    	$data = $this->getItemById($type, $source_id, $parent_id, $ignore);
		    	/*
		    	 * ���� ������ �� �������, ����� ������� �� ���������
		    	 */
		    	if (count($data) == 0) {
		    		$data = $this->getItemById($type, false, '6913:100260'); // AUDI 80  1.7 �������� ������ KNECHT LX 181
		    		
			    	return $data;
		    	}
		    	return $data;
                break;
	    }
    }
    
    
    /**
     * �������� �������� �� ID.
     * 
     * @param string $type
     * @param mixed $source_id
     * @param mixed $parent_id
     * @param bool $ignore
     */
    public function getItemById($type, $source_id = false, $parent_id = false, $ignore = true)
    {
	    $api = new LinemediaAutoApiDriver();
	    if ($ignore) {
		    $api->ignoreModifications();
		} else {
			$api->changeModificationsSetId($this->set_id);
        }
        
	    switch ($type) {
		    case 'brand':
		    	$res = $api->query('getBrands2');
		    	
		    	if (!$source_id) {
		    		return $res['data']['brands'];
                }
		    	foreach ($res['data']['brands'] as $brand) {
			    	if ($brand['manuId'] == $source_id) {
			    		return $brand;
                    }
		    	}
                break;
                
		    case 'model':
		    	$res = $api->query('getVehicleModels2', array('brand_id' => $parent_id));
		    	
		    	if (!$source_id) {
		    		return $res['data']['models'];
                }
		    	foreach ($res['data']['models'] as $model) {
			    	if ($model['modelId'] == $source_id) {
			    		return $model;
                    }
		    	}
                break;
                
		    case 'modification':
		    	$parent_id = explode(':', $parent_id); // brand_id:model_id
		    	$res = $api->query('getModelVariantsWithCarInfo2', array('brand_id' => $parent_id[0], 'model_id' => $parent_id[1]));
		    	$res['data']['modifications'] = array_values($res['data']['modifications']);
		    	if (!$source_id) {
			    	return $res['data']['modifications'][0];
                }
			    foreach ($res['data']['modifications'] as $model) {
			    	if ($model['carId'] == $source_id) {
			    		return $model;
                    }
		    	}
                break;
                
		    case 'group':
		    	$res = $api->query('getListOfGroups2', array('type_id' => $parent_id, 'group_id' => 0));
		    	if (!$source_id) {
		    		return $res['data']['groups'][0];
                }
		    	foreach ($res['data']['groups'] as $group) {
			    	if ($group['assemblyGroupNodeId'] == $source_id) {
			    		return $group;
                    }
		    	}
                break;
                
		    case 'part':
		    	$parent_id = explode(':', $parent_id);
			    $res = $api->query('getDetails2', array('type_id' => $parent_id[0], 'group_id' => $parent_id[1], 'include_oem' => true, 'include_info' => true));
		    	
		    	if (!$source_id) {
		    	    // ������ ��� ������ ����������, ����� �����������.
		    	    unset($res['data']['info']);
		    		return $res['data']['parts'][0];
                }
		    	foreach ($res['data']['parts'] as $group) {
		    	    // ������ ��� ������ ����������, ����� �����������.
                    unset($group['info']);
			    	if ($group['articleId'] == $source_id) {
			    		return $group;
                    }
		    	}
                break;
	    }
    }
    
    
    /**
     * �������� �������� �� ID.
     * 
     * @param string $type
     * @param mixed $parent_id
     * @param bool $ignore
     */
    public function getItemsIds($type, $parent_id = false, $ignore = true)
    {
	    $api = new LinemediaAutoApiDriver();
	    if ($ignore) {
		    $api->ignoreModifications();
        }
		$ids = array();
		
        global $DB;
        
        $sql = "SELECT `source_id` FROM `b_lm_api_modifications` WHERE `type` = '$type' AND `parent_id` = '$parent_id';";
        $res = $DB->Query($sql);
        
        while ($item = $res->Fetch()) {
            $ids []= $item['source_id'];
        }
        
	    switch ($type) {
		    case 'brand':
		    	$res = $api->query('getBrands2');
		    	foreach ($res['data']['brands'] as $el) {
		    		$ids[] = $el['manuId'];
                }
                break;
                
		    case 'model':
		    	$res = $api->query('getVehicleModels2', array('brand_id' => $parent_id));
		    	foreach ($res['data']['models'] as $el) {
		    		$ids[] = $el['modelId'];
                }
                break;
                
		    case 'modification':
		    	$parent_id = explode(':', $parent_id); // brand_id:model_id
		    	$res = $api->query('getModelVariantsWithCarInfo2', array('brand_id' => $parent_id[0], 'model_id' => $parent_id[1]));
		    	foreach ($res['data']['modifications'] as $el) {
		    		$ids[] = $el['carId'];
                }
                break;
                
		    case 'group':
		    	$res = $api->query('getListOfGroups2', array('type_id' => $parent_id, 'group_id' => 0));
		    	foreach ($res['data']['groups'] as $el) {
		    		$ids[] = $el['assemblyGroupNodeId'];
                }
                break;
                
		    case 'part':
		    	$parent_id = explode(':', $parent_id);
			    $res = $api->query('getDetails2', array('type_id' => $parent_id[0], 'group_id' => $parent_id[1]));
			    foreach ($res['data']['parts'] as $el) {
		    		$ids[] = $el['articleId'];
                }
                break;
	    }
	    
	    return $ids;
    }
    
    
}
