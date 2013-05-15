<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);?>

<script type="text/javascript">
    var langs = {'LM_AUTO_EDIT_MODE': '<?= GetMessage('LM_AUTO_EDIT_MODE') ?>', 'LM_AUTO_SAVE': '<?= GetMessage('LM_AUTO_SAVE') ?>'};
</script>

<? $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.form.js'); ?>

<table class="tecdoc parts model_select silver_table_none_top">
	<thead>
        <tr>
            <? if ($arParams['INCLUDE_PARTS_IMAGES'] == 'Y') { ?>
                <th><?= GetMessage('HEAD_IMG') ?></th>
            <? } ?>
            <th><?= GetMessage('HEAD_NAME') ?></th>
            <th><?= GetMessage('HEAD_BRAND') ?></th>
            <th><?= GetMessage('HEAD_ARTICLE') ?></th>
            <th><?= GetMessage('HEAD_INFO') ?></th>
            <th><?= GetMessage('HEAD_BUY') ?></th>
        </tr>
    </thead>
	<tbody>
	    <? foreach ($arResult['DETAILS'] as $part) { 
		    
		    if ($arResult['EDIT_MODE'] == false && $part['hidden'] == 'Y') {
		        continue;
            }
	    ?>
            <tr>
                <? if ($arParams['INCLUDE_PARTS_IMAGES'] == 'Y') { ?>
                    <td class="lm-auto-tecdoc-img">
                    	<? if ($part['image']) { ?>
                        	<a href="<?= htmlspecialcharsEx($part['image']) ?>" class="zoom">
                        		<img src="<?= htmlspecialcharsEx($part['image']) ?>?w=50&h=50" alt="<?= htmlspecialcharsEx($part['genericArticleName'] . ' ' . $part['brandName'] . ' ' . $part['articleNo'])?>" title="<?=htmlspecialcharsEx($part['genericArticleName'] . ' ' . $part['brandName'] . ' ' . $part['articleNo']) ?>" width="50" />
                        	</a>
                    	<? } ?>
                    </td>
                <? } ?>
                <td>
                    <?
                    /*
			         * Режим правки
			         */
			        if ($arResult['EDIT_MODE']) {
			            
                        if ($part['lm_mod_id']) {
                            // Пользовательский элемент.
                            echo '<input type="checkbox" name="' . $arResult['type'] . '[' . $part['source_id'] . ']" value="Y" ' . ($part['hidden'] != 'Y' ? 'checked':'') . ' />';
                            echo '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . $part['articleId'] . '" data-mod-id="' . $part['id'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt=""/></a>';
                            echo '<a href="javascript:void(0);" class="tecdoc-item-delete" data-id="' . $part['id'] . '"><img src="' . $this->GetFolder() . '/images/delete.png" alt="" /></a>';
                        } else {
                            // Элемент TecDoc.
                            echo '<input type="checkbox" name="' . $arResult['type'] . '[' . $part['articleId'] . ']" value="Y" ' . ($part['hidden'] != 'Y' ? 'checked':'') . ' />';
                            echo '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . $part['articleId'] . '"><img src="' . $this->GetFolder() . '/images/edit.png" alt="" /></a>';
                        }
			        }
                    ?>
                    
                    <?= $part['genericArticleName'] ?>
                </td>
                <td class="brand" width="110">
                    <?= $part['brandName'] ?>
                </td>
                <td width="110">
                    <?= $part['articleNo'] ?>
                </td>
                <td width="110">
                	<? if ($part['detail_url']) { ?>
                        <a href="<?= $part['detail_url'] ?>">
                            <?= GetMessage('INFO') ?>
                        </a>
                    <? } ?>
                </td>
                <td width="120">
                	<? if (count($part['PRICES']) > 0) {
	                		$min = $part['min_price'];
	                		$max = $part['max_price'];
	                		if ($min == $max) {
	                		?>
	                			<a href="<?= $part['search_url'] ?>"><?= $part['PRICES'][$min] ?></a>
	                		<? } else { ?>
	                			<a href="<?= $part['search_url'] ?>"><?= $part['PRICES'][$min] ?> - <?= $part['PRICES'][$max] ?></a>
	                		<? } ?>
                	<? } else { ?>
	                    <a href="<?= $part['search_url'] ?>"><?= GetMessage('GET_PRICE') ?></a>
                    <? } ?>
                </td>
            </tr>
        <? } ?>
	</tbody>
</table>


<? include(dirname(__FILE__) . '/footer.php'); ?>