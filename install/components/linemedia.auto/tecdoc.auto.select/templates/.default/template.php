<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<? if (!empty($arResult['brands']) && in_array('getBrands', $arParams['ACTIONS'])) { ?>
    <select name="auto-select-brands">
        <? foreach ($arResult['brands'] as $item) { ?>
            <option value="<?= $item['manuId'] ?>"><?= $item['manuName'] ?></option>
        <? } ?>
    </select>
<? } ?>

<? if (!empty($arResult['models']) && in_array('getModels', $arParams['ACTIONS'])) { ?>
    <select name="auto-select-models">
        <? foreach ($arResult['models'] as $item) { ?>
            <option value="<?= $item['modelId'] ?>"><?= $item['modelname'] ?></option>
        <? } ?>
    </select>
<? } ?>

<? if (!empty($arResult['modifications']) && in_array('getModifications', $arParams['ACTIONS'])) { ?>
    <select name="auto-select-modifications">
        <? foreach ($arResult['modifications'] as $item) { ?>
            <option value="<?= $item['carId'] ?>"><?= $item['carName'] ?></option>
        <? } ?>
    </select>
<? } ?>