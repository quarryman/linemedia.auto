<?
include(dirname(__FILE__) . '/vin_frm.php');
include(dirname(__FILE__) . '/quicksearch.php');
?>

<table class="lm-auto-catalog-original groups">
	<thead>
		<tr>
			<th><?=GetMessage('LM_AUTO_ORIG_GROUP')?></th>
		</tr>
	</thead>
	<tbody>
        <?foreach($arResult['GROUPS'] AS $group){
            $url = $arParams['SEF_FOLDER'] . intval($arResult['MODEL']['ID_mod']) . '/' . intval($arResult['GROUP_TYPE']['ID_typ']) . '/' . $group['ID_grp'] . '/';	       
        ?>
	    <tr  onclick="javascript:document.location.href='<?=$url?>'">
	        <td>
	        	<a href="<?=$url?>"><?=htmlspecialchars($group['NameGrp'])?></a>	        
	        </td>
	    </tr>
	    <?}?>
	</tbody>
</table>