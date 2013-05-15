<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<div class="lm-auto-detail-description">
    <h2><?= GetMessage('LM_AUTO_DESCRIPTION') ?> <?= $arResult['ARTICLE']['NAME'] ?>:</h2>
    <? if (!empty($arResult['ARTICLE']['DETAIL_PICTURE'])) { ?>
        <img class="lm-auto-main-img" src="<?= $arResult['ARTICLE']['DETAIL_PICTURE']['src'] ?>" height="<?= $arResult['ARTICLE']['DETAIL_PICTURE']['height']?>" width="<?=$arResult['ARTICLE']['DETAIL_PICTURE']['width'] ?>" alt="<?=$arResult['ARTICLE']['NAME'] ?>" title="<?= $arResult['ARTICLE']['NAME'] ?>" />
    <? } ?>
    <?= $arResult['ARTICLE']['DETAIL_TEXT'] ?>
</div>