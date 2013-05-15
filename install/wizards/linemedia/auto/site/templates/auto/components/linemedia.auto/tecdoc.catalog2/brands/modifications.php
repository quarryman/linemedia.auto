<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);?>


<table class="tecdoc modifications">
	<thead>
        <tr>
            <th style='width:<?=($arResult['EDIT_MODE'])?50:20?>px'><?= GetMessage('HEAD_INFO') ?></th>
            <th><?= GetMessage('HEAD_TYPE') ?></th>
            <th><?= GetMessage('HEAD_HORSEPOWER') ?></th>
            <th><?= GetMessage('HEAD_YEAR') ?></th>
            <th><?= GetMessage('HEAD_KILOWATTS') ?></th>
            
            <th><?= GetMessage('HEAD_VOLUME') ?></th>
            <th><?= GetMessage('HEAD_FORM_ASSEMBLING') ?></th>
        </tr>
    </thead>
	<tbody>
	    <?
		
	    foreach($arResult['MODIFICATIONS'] AS $modification)
	    {
	    	if($arResult['EDIT_MODE'] == false AND $modification['hidden'] == 'Y')
				continue;
			
	    	$out .= '<tr><td>';
	    	
	    	
	    	/*
	        * Режим правки
	        */
	        if($arResult['EDIT_MODE'])
	        {
		        $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . $modification['carId'] . ']" value="Y" ' . ($modification['hidden'] != 'Y' ? 'checked':'') . ' />';
		        $out .= '<a href="javascript:;" class="tecdoc-item-edit" data-id="' . $modification['carId'] . '" data-mod-id="' . $modification['lm_mod_id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
		        
		        if($modification['lm_mod_id'])
					$out .=  '<a href="javascript:;" class="tecdoc-item-deletet" data-mod-id="' . $modification['lm_mod_id'] . '"><img src="' . $this->GetFolder() . '/images/delete.png" alt=""/></a>';
	        }
	    	
	    	
	    	/*
	    	* Подробная информация
	    	*/
	    	$additional_info = (array) $modification['info'];
	    	
	    	
	    	if(count($additional_info) > 0)
	    	{
	    		/*
	    		* Подробная информация об автомобиле
	    		*/
		    	$additional_popup = "
		    	<table>
                    <tbody>
                        <tr>
                            <td>" .  GetMessage('INFO_YEAR')  . ":</td>
                            <td>
                                <b>
                                    " . $modification['begin'] . " - " . $modification['end'] . "
                                </b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_TYPE_DESIGN')  . ":</td>
                            <td>
                                <b>" . $modification['constrType'] . "</b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_TYPE_DRIVE')  . ":</td>
                            <td>
                               <b>" . $modification['impulsionType'] . "</b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_POWER_KILOWATTS')  . ":</td>
                            <td>
                                <b>" . $modification['powerKW'] . "</b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_POWER_HORSEPOWER')  . ":</td>
                            <td>
                                <b>" . $modification['powerHP'] . "</b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_VOLUME_CM')  . ":</td>
                            <td>
                                <b>" . $additional_info['cylinderCapacityCcm'] . "</b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_VOLUME_LITERS')  . ":</td>
                            <td>
                                <b>" . $modification['cylinderCapacityLiter'] . "</b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_CYLINDER')  . ":</td>
                            <td>
                                <b>" . $modification['cylindres'] . "</b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_COUNT_VALVES')  . ":</td>
                            <td>
                                <b>" . $additional_info['valves'] . "</b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_BRAKE_SYSTEM')  . ":</td>
                            <td>
                                <b>" . $additional_info['brakeSystem'] . "</b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_TYPE_ENGINE')  . ":</td>
                            <td>
                                <b>" .$additional_info['motorType'] . "</b>
                            </td>
                        </tr>
                        <tr>
                            <td>" .  GetMessage('INFO_TYPE_FUEL')  . ":</td>
                            <td>
                                <b>" . $modification['fuelType'] . "</b>
                            </td>
                        </tr>
                    </tbody>
                </table>";
                        
                $arDialogParams = array(
                      'title' => GetMessage('ADDITIONAL_INFO'),
                      'width' => 450,
                      'height' => 500,
                      'min_width' => 300,
                      'min_height' => 400,
                      'resizable' => false,
                      'draggable' => true,
                      'content' => $additional_popup,
                   );
                   
				$href = '(new BX.CDialog('.CUtil::PhpToJsObject($arDialogParams).')).Show(); return false;';
				$out .= '<a href="javascript:void(0)" onclick="javascript:' . $href . '"	title="' . GetMessage('SHOW_ADDITIONAL_INFO') . '"	class="openInfo"><div class="icon-info"></div></a>';

	    	} // additional car info
	    	
	    	
	    	$out .= '</td><td>';
	    	
	        $out .= '<a class="m_select" href="' . $arParams['SEF_FOLDER'] . htmlspecialchars($arResult['brand_id']) . '/' . $arResult['model_id'] . '/' . htmlspecialchars($modification['carId']) . '/"> ' . htmlspecialchars($modification['carName']) . '</a>';
	        
	        
	        $out .= '</td>';
	        
	        $out .= '
		        <td width="50"><b>'  . $modification['powerHP'] . ' ' . GetMessage('LM_AUTO_HP') . '</b></td>
                <td width="150">' . $modification['begin'] . '-' . $modification['end'] . '</td>
	            <td width="50">'  . $modification['powerKW'] . '</td>
	            
	            <td width="70">'  . $modification['ccm'] . '</td>
	            <td width="150">' . $modification['constrType'] . '</td>
	        ';
	        
	        
	        $out .= '</tr>';

	        
		}
				
	    echo $out;
	    ?>
	</tbody>
</table>

<? if($arResult['images'] !== ''){?>
<hr />
<div class="tecdoc_modifications_main_img">
    <? 
    $images = $arResult['images'];
    foreach($images AS $image){?>
        <a href="<?=$image['url']?>" class="zoom polaroid" ><img alt="<?echo($arResult['brand_title'] . ' ' . $arResult['model_title'])?>" src="<?=$image['url']?>?h=100" /></a>
    <?}?>
    
</div>
<?}?>

<? include(dirname(__FILE__) . '/footer.php'); ?>