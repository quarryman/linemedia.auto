<?
include(dirname(__FILE__) . '/vin_frm.php');

include(dirname(__FILE__) . '/quicksearch.php');

$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.cookie.js');
$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.treeview.js');

$url_prefix = $arParams['SEF_FOLDER'] . intval($arResult['MODEL']['ID_mod']);

?>


<div class="lm-auto-catalog-original opel group_types">
	<h4><?=GetMessage('LM_AUTO_ORIG_GROUP_TYPE')?></h4>
	
    <ul class="treeview">
    <?foreach($arResult['GROUP_TYPES'] AS $group_type){?>
    	<li>
    		<?if(count($group_type['groups']) == 1){?>
    			<a href="<?=$url_prefix?>/<?=$group_type['ID_typ']?>/<?=$group_type['groups'][0]['ID_grp']?>/"><?=htmlspecialchars($group_type['groups'][0]['NameGrp'])?></a>
    		<?}elseif(count($group_type['groups'] > 0)){?>
    			<span><?=htmlspecialchars($group_type['NameTyp'])?></span>
    			<ul>
    				<?foreach($group_type['groups'] AS $group){?>
    					<li>
    						<a href="<?=$url_prefix?>/<?=$group_type['ID_typ']?>/<?=$group['ID_grp']?>/"><?=htmlspecialchars($group['NameGrp'])?></a>
    					</li>
    				<?}?>
    			</ul>
    		<?}?>
    	</li>
    <?}?>
    </ul>
    
</div>