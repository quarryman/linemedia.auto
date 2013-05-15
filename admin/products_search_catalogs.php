<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<h2><?= GetMessage('LM_AUTO_SEARCH_SELECT_CATALOG') ?></h2>

<? if (!empty($arResult['CATALOGS'])) { ?>
    <table class="lm-auto-search-catalogs">
        <thead>
            <tr>
                <th><?= GetMessage('LM_AUTO_SEARCH_CATALOG_BRAND_TITLE') ?></th>
                <th><?= GetMessage('LM_AUTO_SEARCH_CATALOG_ITEM_TITLE') ?></th>
                <th><?= GetMessage('LM_AUTO_SEARCH_CATALOG_SEARCH') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($arResult['CATALOGS'] as $catalog) { ?>
            <tr>
                <td><?= $catalog['brand_title'] ?></td>
                <td><?= $catalog['title'] ?></td>
                <td>
                    <a href="<?= $catalog['find'] ?>"><?= GetMessage('LM_AUTO_SEARCH_CATALOG_CONTINUE') ?></a>
                </td>
            </tr>
        <? } ?>
        </tbody>
    </table>
<? } else { ?>
    <?= GetMessage('NOT_FOUND') ?>
<? } ?>