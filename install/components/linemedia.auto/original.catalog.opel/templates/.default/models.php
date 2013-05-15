<?
include(dirname(__FILE__) . '/vin_frm.php');
include(dirname(__FILE__) . '/quicksearch.php');
?>

<div class="lm_car_type_filter">
    <span class="lm-active lm-filter-button show-all"><?=GetMessage('LM_AUTO_ORIG_ALL_FILTER')?></span>
    
</div>

<table class="lm-auto-catalog-original models">
	<thead>
		<tr>
			<th><?=GetMessage('LM_AUTO_ORIG_IMAGE')?></th>
            <th><?=GetMessage('LM_AUTO_ORIG_MODEL')?></th>
			<th><?=GetMessage('LM_AUTO_ORIG_YEARS')?></th>
			<th><?=GetMessage('LM_AUTO_ORIG_TYPE')?></th>
			
		</tr>
	</thead>
	<tbody>
	    <?foreach($arResult['MODELS'] AS $model){?>
	    <tr onclick="javascript:document.location.href='<?=$arParams['SEF_FOLDER'] . intval($model['ID_mod'])?>/'">
	        <td class="car_img">
	        	<img src="<?=htmlspecialchars($model['Image'])?>" alt="<?=htmlspecialchars($model['Name'])?>" />
	        </td>
            
            <td>
	        	<a href="<?=$arParams['SEF_FOLDER'] . intval($model['ID_mod'])?>/"><?=htmlspecialchars($model['Name'])?></a>	        
	        </td>
	        <td>
	        	<?=$model['DateStart']?> - <?=$model['DateEnd']?>
	        </td>
	        <td class="lm-car-type">
	        	<?=$model['Type']?>
	        </td>
	        
	        
	    </tr>
	    <?}?>
	</tbody>
</table>