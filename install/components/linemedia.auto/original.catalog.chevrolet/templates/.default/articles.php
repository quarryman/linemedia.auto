<div class="lm-auto-catalog-original-image chevrolet">
    <div class="zoom">
        <img src="<?=htmlspecialchars($arResult['GROUP_SECTION']['Image']) ?>" alt="<?=htmlspecialchars($arResult['GROUP_SECTION']['Name'])?> <?=htmlspecialchars($arResult['BRAND']['Name'])?> <?=htmlspecialchars($arResult['MODEL']['Name'])?>" />
    </div>
</div>

<?
$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.quicksearch.js');
__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

?>

<div class="lm-auto-original chevrolet quicksearch-on-img">
    <span><?=GetMessage('LM_AUTO_QUICK_FILTER')?></span><br />
    <input type="text" name="quick_search_img" id="quick_search_img" placeholder="<?=GetMessage('LM_AUTO_QUICK_FILTER_HINT')?>" />
</div>





<table class="lm-auto-catalog-original chevrolet articles">
	<thead>
		<tr>
            <th style="width: 50px;"><?=GetMessage('LM_AUTO_ORIG_ARTICLE_PIC_NUMBER')?></th>
            <th><?=GetMessage('LM_AUTO_ORIG_ARTICLE_NUMBER')?></th>			
			<th><?=GetMessage('LM_AUTO_ORIG_ARTICLE_TITLE')?></th>
            <th><?=GetMessage('LM_AUTO_ORIG_ARTICLE_SEARCH')?></th>
			
		</tr>
	</thead>
	<tbody>
	    <?
        $group_number = 0;
        foreach($arResult['ARTICLES'] AS $article){?>
	    
	    <?if($article['is_group'] == 1){
	       $group_number ++;
           ?>
	    
	    <tr class="lm-group group_<?=$group_number?>_header">
            <th colspan="5">
	        	<?=nl2br(htmlspecialchars($article['Description']))?>
	        </td>
	        
	    </tr>
	    
	    <?
	    continue;
	    }
	    ?>
	    
	    
	    
	    <tr class="group_<?=$group_number?>">
            <td>
	        	<b class="img_num">#<?=htmlspecialchars($article['PNC'])?></b>
	        </td>
	        
            <td><?=htmlspecialchars($article['Article'])?></td>
	        
	        <td class="ucase">
	        	<?if($article['part_info_url']){?>
	        		<a class="lm-info" href="<?=$article['part_info_url']?>/"><?=htmlspecialchars($article['Name'])?></a>
	        	<?}else{?>
	        		<?=htmlspecialchars($article['Name'])?>
	        	<?}?>
	        	<span class="add_info"><?=htmlspecialchars($article['Description'])?></span>
	        </td>
            <td>
	        	<?if($article['PRICES']){?>
	        		<?if($article['min_price'] == $article['max_price']){?>
	        			<a href="<?=$article['search_url']?>"><?=$article['PRICES'][$article['min_price']]?></a>
	        		<?}else{?>
	        			<a href="<?=$article['search_url']?>"><?=$article['PRICES'][$article['min_price']]?> - <?=$article['PRICES'][$article['max_price']]?></a>
	        		<?}?>
	        	<?}else{?>
	        		<a href="<?=$article['search_url']?>"><?=GetMessage('LM_AUTO_ORIG_ARTICLE_SEARCH')?></a>
	        	<?}?>
	        </td>
	        
	    </tr>
	    <?}?>
	</tbody>
</table>

