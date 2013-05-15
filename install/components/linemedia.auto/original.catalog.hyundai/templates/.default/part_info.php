<div class="lm-auto-catalog-original hyundai part-info">

	<h1><?=$arResult['ARTICLE']['Brand']?> <?=$arResult['ARTICLE']['Article']?></h1>
	<div class="lm-auto-orig-part-descr ucase"><h2><?=$arResult['ARTICLE']['Name']?></h2></div>
	
	<?if($arResult['ARTICLE']['Image']){?>
		<img src="<?=$arResult['ARTICLE']['Image']?>" alt="<?=$arResult['ARTICLE']['Brand']?> <?=$arResult['ARTICLE']['Article']?>" />
	<?}?>
	
	<!--div class="analog-type"><?=($arResult['ARTICLE']['isOriginal'] == 1) ? GetMessage('LM_AUTO_ORIG_ORIGINAL') : GetMessage('LM_AUTO_ORIG_ANALOG')?></div-->
	
	
	
	
	<div class="lm-auto-catalog-original-partinfo prices">
	<?if(count($arResult['ARTICLE']['PRICES']) > 0){?>
		<h3><?=GetMessage('LM_AUTO_ORIG_PRICES')?></h3>
		
		<?if($arResult['ARTICLE']['PRICES']){?>
    		<?if($arResult['ARTICLE']['min_price'] == $arResult['ARTICLE']['max_price']){?>
    			<a class="btn btn-primary" href="<?=$arResult['ARTICLE']['search_url']?>"><?=$arResult['ARTICLE']['PRICES'][$arResult['ARTICLE']['min_price']]?></a>
    		<?}else{?>
    			<a  class="btn btn-primary" href="<?=$arResult['ARTICLE']['search_url']?>"><?=$arResult['ARTICLE']['PRICES'][$arResult['ARTICLE']['min_price']]?> - <?=$arResult['ARTICLE']['PRICES'][$arResult['ARTICLE']['max_price']]?></a>
    		<?}?>
    	<?}else{?>
    		<a  class="btn btn-primary" href="<?=$arResult['ARTICLE']['search_url']?>"><?=GetMessage('LM_AUTO_ORIG_ARTICLE_SEARCH')?></a>
    	<?}?>
		
				
	<?} else {?>
		<a class="btn btn-primary"  href="<?=$arResult['ARTICLE']['search_url']?>"><?=GetMessage('LM_AUTO_ORIG_ARTICLE_SEARCH')?></a>
	<?}?>
	</div>
    
    <hr />

	
	
	
	<?
    /*
    * ??????? ???
    */
    if(count($arResult['ARTICLE']['crosses']) > 0){?>
		
		<div class="lm-auto-catalog-original-partinfo crosses">
		
			<h3><?=GetMessage('LM_AUTO_ORIG_CROSSES')?></h3>
			<table class="cross">
			<?foreach($arResult['ARTICLE']['crosses'] AS $cross){?>
				<tr>
					<?/*($cross['isOriginal'] == 1) ? GetMessage('LM_AUTO_ORIG_ORIGINAL') : GetMessage('LM_AUTO_ORIG_ANALOG')*/?> 
					<td class="lm-brands"><?=$cross['Brand']?></td>
					<td>
                        <a href="<?=$arParams['SEF_FOLDER']?>part-info/<?=$cross['ID_art']?>/"><?=htmlspecialchars($cross['Article'])?></a>
                    </td>
                    <td><?=htmlspecialchars($cross['Name'])?></td>
				</tr>
			<?}?>
            </table>
		
		</div>
		
	<?}?>
    
	<?if(count($arResult['ARTICLE']['packages']) > 0){?>
		
		<div class="lm-auto-catalog-original-partinfo packages">
		
			<h3><?=GetMessage('LM_AUTO_ORIG_PACKAGES')?></h3>
			<table class="cross">
			<?foreach($arResult['ARTICLE']['packages'] AS $package){?>
                <tr class="package">
				    <td class="lm-brands"><?=$package['Brand']?></td>
                    <td><a href="<?=$arParams['SEF_FOLDER']?>part-info/<?=$package['ID_art']?>/"><?=$package['Article']?></a></td>
                    <td><?=htmlspecialchars($package['Name'])?> <?=htmlspecialchars($package['Description'])?></td>	
					<?//=GetMessage('LM_AUTO_ORIG_QUANT')?> <?=$package['Quantity']?>
				
                </tr>
			<?}?>
            </table>
		</div>
		
	<?}?>
    
    
	<?if(count($arResult['ARTICLE']['replacements']) > 0){?>
		
		<div class="lm-auto-catalog-original-partinfo replacements">
		
			<h3><?=GetMessage('LM_AUTO_ORIG_REPLACEMENTS')?></h3>
			<table class="cross">
			<?foreach($arResult['ARTICLE']['replacements'] AS $replacement){?>
				<tr class="replacement">
                    <td><a href="<?=$arParams['SEF_FOLDER']?>part-info/<?=$replacement['ID_art']?>/"><?=htmlspecialchars($replacement['Article'])?></a></td>
					
					<td><?=htmlspecialchars($replacement['Name'])?> <?=htmlspecialchars($replacement['Description'])?></td>
					<?/*=GetMessage('LM_AUTO_ORIG_QUANT').$replacement['Quantity']*/?>
				</tr>
			<?}?>
            </table>
		</div>
		
	<?}?>
	<?
    /*
    * ??????
    */
    if(count($arResult['ARTICLE']['usage']) > 0){?>
		
		<div class="lm-auto-catalog-original-partinfo usage">
		
			<h4><?=GetMessage('LM_AUTO_ORIG_USAGE')?></h4>
			<ul>
			<?foreach($arResult['ARTICLE']['usage'] AS $model){?>
				<li>
                    <div class="usage">
    				
    					<a href="<?=$arParams['SEF_FOLDER']?>"><?=$model['NameMfa']?></a>
                        &rarr;
    					<a href="<?=$arParams['SEF_FOLDER'] . $model['ID_mod']?>/"><?=$model['NameMod']?></a>
    					(<?=$model['Region']?>)
    					
    					<div><?//=GetMessage('LM_AUTO_ORIG_USAGE_SECTION')?> 
    						<a href="<?=$arParams['SEF_FOLDER'] . $model['ID_mod']?>/<?=$model['ID_typ']?>/"><?=$model['NameTyp']?></a>
    						&rarr;
    						<a href="<?=$arParams['SEF_FOLDER'] . $model['ID_mod']?>/<?=$model['ID_typ']?>/<?=$model['ID_grp']?>/"><?=$model['NameGrp']?></a>
    						&rarr;
    						<a href="<?=$arParams['SEF_FOLDER'] . $model['ID_mod']?>/<?=$model['ID_typ']?>/<?=$model['ID_grp']?>/<?=$model['ID_sec']?>/"><?=$model['NameSec']?></a>
    					</div>
    				</div>
                </li>
			<?}?>
            </ul>
		
		</div>
		
	<?}?>
	
	
	
</div>