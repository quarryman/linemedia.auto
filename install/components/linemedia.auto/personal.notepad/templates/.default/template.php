<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$APPLICATION->SetAdditionalCSS($templateFolder . '/css/tablesorter.css');
$APPLICATION->AddHeadScript($templateFolder . '/js/jquery.tablesorter.min.js');

if (!empty($arResult)) {
    ?>
    <table class="lm-auto-notepad tablesorter">
        <thead>
            <tr>
                <th><img class="add" src="<?=$templateFolder?>/images/add.png" title="<?=GetMessage("LM_AUTO_NOTEPAD_ADD")?>" alt="<?=GetMessage("LM_AUTO_NOTEPAD_ADD")?>"/></th>
                <th><?=GetMessage("LM_AUTO_NOTEPAD_TITILE")?></th>
                <th><?=GetMessage("LM_AUTO_NOTEPAD_BREND")?></th>
                <th><?=GetMessage("LM_AUTO_NOTEPAD_ARTICLE")?></th>
                <th><?=GetMessage("LM_AUTO_NOTEPAD_AUTO")?></th>
                <th><?=GetMessage("LM_AUTO_NOTEPAD_COMMENTS")?></th>
                <th><?=GetMessage("LM_AUTO_NOTEPAD_QUANTITY")?></th>
                <th><?=GetMessage("LM_AUTO_NOTEPAD_PRICE")?></th>
                <th class="delete-column"></th>
            </tr>
        </thead>

        <tbody>

    <?php
    foreach ($arResult['DETAILS'] as $key => $detail) {
        ?>
            <tr id="detail_id_<?=$detail['id']?>">

                <td>
                    <img class="change" src="<?=$templateFolder?>/images/change_new.png" title="<?=GetMessage("LM_AUTO_NOTEPAD_CHANGE")?>" alt="<?=GetMessage("LM_AUTO_NOTEPAD_CHANGE")?>"/>
                    <img class="save" src="<?=$templateFolder?>/images/save.png" title="<?=GetMessage("LM_AUTO_NOTEPAD_SAVE")?>" alt="<?=GetMessage("LM_AUTO_NOTEPAD_SAVE")?>"/>
                    <img class="cancel" src="<?=$templateFolder?>/images/cancel.png" title="<?=GetMessage("LM_AUTO_NOTEPAD_CANCEL")?>" alt="<?=GetMessage("LM_AUTO_NOTEPAD_CANCEL")?>"/>
                </td>

                <td class="title">
                    <input type="hidden" id="notepad_part_id" name="id" value="<?=$detail['id']?>">

                    <input class="title-new" type="text" value="<?=$detail['title']?>">
                    <p class="title-old"><?=$detail['title']?></p>
                </td>

                <td class="brand">
                    <input class="brand-new" type="text" value="<?=$detail['brand_title']?>">
                    <p class="brand-old"><?=$detail['brand_title']?></p>
                </td>

                <td class="article">
                    <input class="article-new" type="text" value="<?=$detail['article']?>">
                    <p class="article-old"><?=$detail['article']?></p>
                </td>

                <td class="auto">
                    <input class="auto-new" type="text" value="<?=$detail['auto']?>">
                    <p class="auto-old"><?=$detail['auto']?></p>
                </td>

                <td class="comments">
                    <textarea class="comments-new" rows="2" cols="20"><?=$detail['notes']?></textarea>
                    <p class="comments-old"><?=$detail['notes']?></p>
                </td>

                <td class="quantity">
                    <input class="quantity-new" type="text" value="<?=$detail['quantity']?>">
                    <p class="quantity-old"><?=$detail['quantity']?></p>
                </td>

                <td class="price">
                    <?php
                    /**
                     * Показываем цену мин-макс (если такие есть) или кнопку "узнать цену"
                     */
                    if (count($detail['PRICES']) > 0) {
                        $min = $detail['min_price'];
                        $max = $detail['max_price'];

                        if ($min == $max) {
                            ?>
                            <a href="<?= $detail['search_url'] ?>" target="_blank"><?=$detail['PRICES'][$min]?></a>
                            <?php
                        } else { ?>
                            <a href="<?= $detail['search_url'] ?>" target="_blank"><?=$detail['PRICES'][$min]?> - <?=$detail['PRICES'][$max]?></a>
                            <?php
                        }
                    } else { ?>
                        <a href="<?= $detail['search_url'] ?>" target="_blank"><?=GetMessage('LM_AUTO_NOTEPAD_GET_PRICE') ?></a>
                        <?php
                    } ?>
                </td>
                <td class="delete-column">
                    <img class="delete" src="<?=$templateFolder?>/images/delete.png" title="<?=GetMessage("LM_AUTO_NOTEPAD_DELETE")?>" alt="<?=GetMessage("LM_AUTO_NOTEPAD_DELETE")?>"/>
                </td>
            </tr>
    <?php
    }
    ?>
        </tbody>

    </table>

    <?php
    /**
     * Создание параметров для js
     */
    ?>
    <script>
        lang_change   = '<?=GetMessage('LM_AUTO_NOTEPAD_CHANGE')?>';
        lang_save   = '<?=GetMessage('LM_AUTO_NOTEPAD_SAVE')?>';
        lang_delete = '<?=GetMessage('LM_AUTO_NOTEPAD_DELETE') ?>';
        lang_cancel = '<?=GetMessage('LM_AUTO_NOTEPAD_CANCEL')?>';
        lang_empty_article ='<?=GetMessage('LM_AUTO_NOTEPAD_EMPTY_ARTICLE')?>';
        lang_price  = '<?=GetMessage('LM_AUTO_NOTEPAD_GET_PRICE') ?>';
        lang_delete_confirm = '<?=GetMessage('LM_AUTO_NOTEPAD_DELETE_CONFIRM')?>';
        lang_all_auto = '<?=GetMessage('LM_AUTO_NOTEPAD_ALL_AUTO')?>';
        sessid = '<?=bitrix_sessid()?>';
        path_to_ajax = '<?=$arResult['path'].'/ajax.php'?>';
        path_to_images = '<?=$templateFolder.'/images/'?>';
    </script>

    <?php
}
