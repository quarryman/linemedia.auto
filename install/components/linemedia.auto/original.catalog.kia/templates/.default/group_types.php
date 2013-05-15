<?
include(dirname(__FILE__) . '/vin_frm.php');

include(dirname(__FILE__) . '/quicksearch.php');
?>

<table class="lm-auto-catalog-original modifications">
	<thead>
		<tr>
			<th><?=GetMessage('LM_AUTO_ORIG_GROUP_TYPE')?></th>
		</tr>
	</thead>
	<tbody>
        <?foreach($arResult['MODIFICATIONS'] AS $modification){
            $url = $arParams['SEF_FOLDER'] . intval($arResult['MODEL']['ID_mod']) . '/' . $modification['ID_typ'] . '/';	       
        ?>
	    <tr onclick="javascript:document.location.href='<?=$url?>'">
	        <td>
	        	<a href="<?=$url?>"><?=htmlspecialchars($modification['NameTyp'])?></a>	        
	        </td>
	    </tr>
	    <?}?>
	</tbody>
</table>