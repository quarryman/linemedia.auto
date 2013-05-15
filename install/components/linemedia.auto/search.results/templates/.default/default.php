<form action="<?= $arResult['FORM_ACTION'] ?>" method="post" class="lm-auto-main-search-form" name="lm-auto-main-search-form" id="lm-auto-main-search-form-id" onsubmit="$(this).attr('action','<?= $arResult['FORM_ACTION'] ?>'+$(this).find('#lm-auto-main-search-query-id').val()+'/');return true;">
    <input type="text" name="q" value="<?= htmlspecialchars($arParams['QUERY']) ?>" placeholder="<?= GetMessage('LM_AUTO_MAIN_SEARCH_FORM_PLACEHOLDER') ?>" id="lm-auto-main-search-query-id" data-remapping="<?= intval($arParams['REMAPPING']) ?>" />
    <input class="lm-auto-submit" type="submit" value="<?= GetMessage('LM_AUTO_MAIN_SEARCH_FORM_SUBMIT') ?>" />
    <? if (!empty($arParams['VIN_URL'])) { ?>
        <a href="<?= $arParams['VIN_URL'] ?>"><?= GetMessage('LM_AUTO_MAIN_SEARCH_REQUEST_VIN') ?></a>
    <? } ?>
    <div class="lm-auto-partial-search-block">
        <label class="lm-auto-partial-search">
            <input type="checkbox" name="partial" value="Y" />
            <?= GetMessage('LM_AUTO_MAIN_PARTIAL_SEARCH') ?>
        </label>
    </div>
</form>