<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);


$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.cookie.js');
$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.treeview.js');


if(!function_exists('lm_recPrintTree')) {
	function lm_recPrintTree($parent_id, &$arResult, $arParams, $folder)
	{
		$out = '<ul>';
		if($parent_id == 0)
			$out = '<ul id="lm-auto-tecdoc-catalog-groups">';
		
		foreach($arResult['GROUPS'] AS $i => $group)
		{
			if($group['parentNodeId'] != $parent_id)
				continue;
			
			if($arResult['EDIT_MODE'] == false AND $group['hidden'] == 'Y')
				continue;
			
			$out .= '<li>';
			
			/*
	        * Режим правки
	        */
	        if($arResult['EDIT_MODE'])
	        {
		        $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . $group['assemblyGroupNodeId'] . ']" value="Y" ' . ($group['hidden'] != 'Y' ? 'checked':'') . ' />';
		        $out .= '<a href="javascript:;" class="tecdoc-item-edit" data-id="' . $group['assemblyGroupNodeId'] . '" data-mod-id="' . $group['lm_mod_id'] . '"><img src="' . $folder . '/images/edit.png" alt=""/></a>';
		        
		        if($group['lm_mod_id'])
					$out .=  '<a href="javascript:;" class="tecdoc-item-delete" data-mod-id="' . $group['lm_mod_id'] . '"><img src="' . $this->GetFolder() . '/images/delete.png" alt=""/></a>';
	        }
	        
			//$out .=  '<a href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $additional_url . $arResult['model_id']. '/' . $arResult['modification_id'] . '/' . $group['assemblyGroupNodeId'] . '/">' . $group['assemblyGroupName'] . '</a>';
			//$out .= $group['assemblyGroupName'];
			
			if($group['hasChilds']){
			    $out .=  '<span>' . $group['assemblyGroupName'] . '</span>';
                
			    $out .= lm_recPrintTree($group['assemblyGroupNodeId'], $arResult, $arParams, $folder);
            } else { 
                $out .=  '<a href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['model_id']. '/' . $arResult['modification_id'] . '/' . $group['assemblyGroupNodeId'] . '/">' . $group['assemblyGroupName'] . '</a>';
            }
			$out .= '</li>';
		}
		
		$out .= '</ul>';
		
		return $out;
	}
}

echo lm_recPrintTree(0, $arResult, $arParams, $this->GetFolder());


echo $out;
?>


<? include(dirname(__FILE__) . '/footer.php'); ?>