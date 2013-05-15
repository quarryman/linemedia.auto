<div class="box">      
<form action="<?= $arResult['FORM_ACTION'] ?>" method="get" class="lm-auto-main-search-form" name="lm-auto-main-search-form" id="lm-auto-main-search-form-id">
    <label for="LM_AUTOPORTAL_F_CODE"><b><?= GetMessage('LM_AUTO_MAIN_SEARCH_BY_NUMBER') ?>:</b></label>
    <div class="search_form_inner">
        <input class="auto-search-top" type="text" name="q" value="" placeholder="<?= GetMessage('LM_AUTO_MAIN_SEARCH_FORM_PLACEHOLDER') ?>" id="lm-auto-main-search-query-id" data-remapping="<?= intval($arParams['REMAPPING']) ?>" />
        <input class="lm-auto-submit btn btn-primary" type="submit" value="<?= GetMessage('LM_AUTO_MAIN_SEARCH_FORM_SUBMIT') ?>" />
    </div>
    <div class="lm-auto-partial-search-block">
        <label class="lm-auto-partial-search">
            <input type="checkbox"name="partial" value="Y" />
            <?= GetMessage('LM_AUTO_MAIN_PARTIAL_SEARCH') ?>
        </label>
    </div>
    <p class="help-block"><?= GetMessage('LM_AUTO_MAIN_FOR_EXAMPLE') ?> GDB1550</p>
</form>
</div>