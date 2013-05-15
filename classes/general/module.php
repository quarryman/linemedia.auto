<?php




class LinemediaAutoModule {
	
	public static function checkUpdates($module_id = 'linemedia.auto')
	{
		
		
		
		$obCache = new CPHPCache();
		$life_time = 30 * 60; 
		$cache_id = __CLASS__ . __METHOD__ . $module_id;
		
		if ($obCache->InitCache($life_time, $cache_id, "/lm_auto/mod_updates")) {
		    $vars = $obCache->GetVars();
		    
		    return $vars['response'];
		} else {
	        
	        $response = self::__checkUpdates($module_id);
	        
	        if ($obCache->StartDataCache()) {
		        $obCache->EndDataCache(array(
		        	'response' => $response,
		        ));
	        } 
		}
		
		return $response;
	}
	
	
	public static function __checkUpdates($module_id)
	{
		include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client_partner.php');
		$response = CUpdateClientPartner::GetUpdatesList();
		$modules = (array) $response['MODULE'];
		foreach($modules AS $module)
		{
			if($module['@']['ID'] != $module_id)
				continue;
			
			$updates = array();
			foreach($module['#']['VERSION'] AS $ver)
			{
				$updates[$ver['@']['ID']] = $ver['#']['DESCRIPTION'][0]['#'];
			}
			return $updates;	
		}
		
		return false;
	}
	
}