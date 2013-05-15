<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<h2><?= GetMessage('LM_AUTO_SEARCH_CATALOG_HEADER') ?></h2>

<table class="lm-auto-search-catalogs silver-table">
    <thead>
        <tr>
            <th><?=GetMessage('LM_AUTO_SEARCH_CATALOG_BRAND_TITLE')?></th>
            <th><?=GetMessage('LM_AUTO_SEARCH_CATALOG_ITEM_TITLE')?></th>
            <th><?=GetMessage('LM_AUTO_SEARCH_CATALOG_SEARCH')?></th>
            <? if (LinemediaAutoDebug::enabled()) { ?>
                <th><?= GetMessage('LM_AUTO_SEARCH_CATALOG_DEBUG') ?></th>
            <? } ?>
        </tr>
    </thead>
    <tbody>
    <? foreach ($arResult['CATALOGS'] as $catalog) { 
	    
        $extra_brands_arr = array_map('htmlspecialchars', (array) $catalog['extra']['wf_b']);        
        $extra_brands_arr = array_diff($extra_brands_arr, array($catalog['brand_title']));
	    $extra_brands = join(', ', $extra_brands_arr);
    ?>
        <tr onclick="javascript:document.location.href='<?=$catalog['url']?>'">
            <td class="lm-auto-search-catalogs-brand"><?=$catalog['brand_title']?> <span class="extra-brands"><?=$extra_brands?></span></td>
            <td class="lm-auto-search-catalogs-title"><?=$catalog['title']?></td>
            <td class="lm-auto-search-catalogs-go"><a href="<?=$catalog['url']?>"><?=GetMessage('LM_AUTO_SEARCH_CATALOG_CONTINUE')?></a></td>
            <? if (LinemediaAutoDebug::enabled()) { ?>
                <td style="min-width:400px"><pre><? unset($catalog['url']); echo print_r($catalog, true) ?></pre></td>
            <? } ?>
        </tr>
    <? } ?>
    </tbody>
   
</table>