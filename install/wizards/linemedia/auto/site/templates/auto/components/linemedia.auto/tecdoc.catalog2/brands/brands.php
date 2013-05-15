<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);?>

        <ul class="tecdoc-brands-ul">
	        <?
	        $letters = 0;
	        $out = '';
	        foreach($arResult['BRANDS'] AS $letter => $brands) {
		        
		        if (count($brands) < 1) {
		        	continue;
                }
		        foreach ($brands AS $brand) {

                    /*
                     * Может есть лого?
                     */
                    $logo_filename = '/upload/linemedia.auto/images/brands/' . strtoupper($brand['manuName']) . '.png';
                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $logo_filename)) {
                        continue;
                    }
                    
		        	if ($arResult['EDIT_MODE'] == false AND $brand['hidden'] == 'Y')
		        		continue;
		        	
		        	if ($brand['hidden'] == 'Y')
		        		$out .= '<li class="hidden">';
			        else
			        	$out .= '<li>';
			        
			        /*
			         * Режим правки
			         */
			        if ($arResult['EDIT_MODE']) {
				        $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . htmlspecialchars($brand['manuId']) . ']" value="Y" ' . ($brand['hidden'] != 'Y' ? 'checked':'') . ' />';
				        $out .= '<a href="javascript:;" class="tecdoc-item-edit" data-id="' . htmlspecialchars($brand['manuId']) . '" data-mod-id="' . $brand['lm_mod_id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
				        
				        if ($brand['lm_mod_id'])
					       $out .=  '<a href="javascript:;" class="tecdoc-item-deletet" data-mod-id="' . $brand['lm_mod_id'] . '"><img src="' . $this->GetFolder() . '/images/delete.png" alt=""/></a>';
			        }

				    $out .= '<a class="m_select" href="' . $arParams['SEF_FOLDER'] . htmlspecialchars($brand['manuId']) . '/"> <img width="50" height="50" src="'.$logo_filename.'" /></a>';
			        
			        $out .= '</li>';
		        }

		        $letters ++;
	        }

	        echo $out;
	        ?>
	     </ul>
		<div class="clr"></div>

<? include(dirname(__FILE__) . '/footer.php'); ?>