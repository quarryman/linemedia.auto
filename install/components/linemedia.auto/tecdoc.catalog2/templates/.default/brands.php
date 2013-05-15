<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__); ?>

<script type="text/javascript">
    var langs = {'LM_AUTO_EDIT_MODE': '<?= GetMessage('LM_AUTO_EDIT_MODE') ?>', 'LM_AUTO_SAVE': '<?= GetMessage('LM_AUTO_SAVE') ?>'};
</script>

<? $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.form.js'); ?>

<table class="tecdoc brands">
	<tbody>
	    <tr>
	        <td>
	        <?
	        $letters_in_column = count($arResult['BRANDS']) / $arParams['COLUMNS_COUNT'];
	        $letters_in_column = round($letters_in_column);

            $shown_letters = 0;
	        $letters = 0;
	        $out = '';
	        foreach ($arResult['BRANDS'] as $letter => $brands) {
		        ++$shown_letters;
		        if (count($brands) < 1) {
		        	continue;
                }
		        
		        $out .= '<h2 class="letter">' . $letter . '</h2>';
		        $out .= '<ul>';
		        foreach ($brands as $brand) {
		        	if ($arResult['EDIT_MODE'] == false && $brand['hidden'] == 'Y') {
		        		continue;
                    }
		        	if ($brand['hidden'] == 'Y') {
		        		$out .= '<li class="lm-auto-hidden">';
			        } else {
			        	$out .= '<li>';
                    }
			        
			        /*
			         * Режим правки
			         */
			        if ($arResult['EDIT_MODE']) {
			            
                        if ($brand['lm_mod_id']) {
                            // Пользовательский элемент.
                            $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . htmlspecialcharsEx($brand['source_id']) . ']" value="Y" ' . ($brand['hidden'] != 'Y' ? 'checked' : '') . ' />';
                            $out .= '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . htmlspecialcharsEx($brand['manuId']) . '" data-mod-id="' . $brand['id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
                            $out .= '<a href="javascript:void(0);" class="tecdoc-item-delete" data-id="'.$brand['id'].'"><img src="' . $this->GetFolder() . '/images/delete.png" alt="'.GetMessage('LM_AUTO_DELETE').'" /></a>';
                        } else {
                            // Элемент TecDoc.
    				        $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . htmlspecialcharsEx($brand['manuId']) . ']" value="Y" ' . ($brand['hidden'] != 'Y' ? 'checked' : '') . ' />';
    				        $out .= '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . htmlspecialcharsEx($brand['manuId']) . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
                        }
			        }
			        
			        /*
			         * Может есть лого?
			         */
			        $logo_filename = '/upload/linemedia.auto/images/logo/' . strtolower($brand['manuName']) . '.png';
                    $logo_class = htmlspecialcharsEx(strtolower($brand['manuName']));
                    $logo_style = '';
                    
		            if (!empty($brand['image']) && file_exists($_SERVER['DOCUMENT_ROOT'].$brand['image'])) {
                        $logo_style = 'style="background-image:url(' . htmlspecialcharsEx($brand['image']) . ')"';
		                $logo_class = '';
		            } else {
		                if (file_exists($_SERVER['DOCUMENT_ROOT'].$logo_filename)) {
                            $logo_style = 'style="background-image: url(' . htmlspecialcharsEx($logo_filename) . ');"';
                        }
		            }
                    $out .= '<div class="car-logo ' . $logo_class . ' selflogo" '.$logo_style.'></div>';
                    
				    $out .= '<a class="m_select" href="' . $arParams['SEF_FOLDER'] . htmlspecialcharsEx($brand['manuId']) . '/"> ' . htmlspecialcharsEx($brand['manuName']) . '</a>';
			        $out .= '</li>';
		        }
		        $out .= '</ul>';
		        
		        if ($letters % $letters_in_column == 0 && $letters > 0 && $shown_letters < count($arResult['BRANDS'])) {
		        	$out .= '</td><td>';
                    ++$cols;
	        	}
		        $letters ++;
	        }
	        
	        echo $out;
	        ?>
	        
	        </td>
	    </tr>
	</tbody>
</table>


<? include(dirname(__FILE__) . '/footer.php'); ?>