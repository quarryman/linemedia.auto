<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>

<?
    $APPLICATION->SetAdditionalCSS('http://yandex.st/jquery-ui/1.8.23/themes/smoothness/jquery.ui.all.min.css');

    $APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.0/jquery.min.js');
    $APPLICATION->AddHeadScript('http://yandex.st/jquery-ui/1.8.16/jquery-ui.min.js');
    $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.treeview.js');  
?>

<? $title = $arParams['BRAND'].' '.$arParams['ARTICLE'] ?>

<h2><?= GetMessage('TITLE_ARTICLE_NUMBER') ?>: <?= $title ?></h2>

<? if (isset($arResult['IMAGE'])) { ?>
    <img src="<?= $arResult['IMAGE'] ?>" alt="<?= $title ?>" title="<?= $title ?>" />
<? } ?>

<? if (!empty($arResult['DATA']['immediateAttributs']['array'])) { ?>
    <h3><?= GetMessage('TITLE_ADDITIONAL_FEATURES') ?></h3>
    <div class="standartTable">
        <table class="tecdoc_details" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th><?= GetMessage('HEAD_PROPERTY') ?></th>
                    <th><?= GetMessage('HEAD_VALUE') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($arResult['DETAIL']['immediateAttributs']['array'] as $val) { ?>
                    <tr>
                        <td><?= $val['attrName'] ?>:</td>
                        <td><?= $val['attrValue'] ?></td>
                    </tr>
                <? } ?>
            </tbody>
        </table>
    </div>
<? } ?>

<? if ($arParams['SHOW_ORIGINAL_ITEMS'] == 'Y') { ?>
    <h3><?= GetMessage('TITLE_CONFORMITY_ORIGINAL_NUMBERS') ?></h3>
    <? if (!empty($arResult['DETAIL']['oenNumbers']['array'])) { ?>
        <div class="standartTable">
            <table class="tecdoc_details" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th><?= GetMessage('HEAD_MARK') ?></th>
                        <th><?= GetMessage('HEAD_NUMBER') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($arResult['DETAIL']['oenNumbers']['array'] as $value) { ?>
                        <tr>
                            <td><?= $value['brandName'] ?></td>
                            <td><?= $value['oeNumber'] ?></td>
                        </tr>
                    <? } ?>
                </tbody>
            </table>
        </div>
    <? } ?>
<? } ?>

<? if ($arParams['SHOW_APPLICABILITY'] == 'Y') { ?>
    <? if (!empty($arResult['APPLICABILITY'])) { ?>
        <h3><?= GetMessage('TITLE_CONFORMITY_SHOW_APPLICABILITY') ?></h3>
        <div class="applicability">
            <div class="applicability-firms">
                <? foreach ($arResult['APPLICABILITY'] as $firm) { ?>
                    <a href="javascript:void(0)" class="applicability-firm" rel="<?= $firm['manuId'] ?>"><?= $firm['manuName'] ?></a>
                <? } ?>
            </div>
            <div class="clear"></div>
            
            <? foreach ($arResult['APPLICABILITY'] as $firm) { ?>
                <div id="applicability-model-<?= $firm['manuId'] ?>" class="applicability-models" style="display: none;">
                    <? foreach ($firm['MODELS'] as $model) { ?>
                        <a href="javascript:void(0)" class="applicability-model" rel="<?= md5($model['MODEL_NAME']) ?>"><?= $model['MODEL_NAME'] ?></a>
                    <? } ?>
                </div>
            <? } ?>
            <div class="clear"></div>
            
            <? foreach ($arResult['APPLICABILITY'] as $firm) { ?>
                <? foreach ($firm['MODELS'] as $model) { ?>
                    <div id="applicability-modification-<?= md5($model['MODEL_NAME']) ?>" class="applicability-modifications" style="display: none;">
                        <table class="applicability-modifications-table">
                            <thead>
                                <tr>
                                    <th><?= GetMessage('HEAD_TYPE') ?></th>
                                    <th><?= GetMessage('HEAD_YEAR') ?></th>
                                    <th><?= GetMessage('HEAD_KILOWATTS') ?></th>
                                    <th><?= GetMessage('HEAD_HORSEPOWER') ?></th>
                                    <th><?= GetMessage('HEAD_VOLUME') ?></th>
                                    <th><?= GetMessage('HEAD_FORM_ASSEMBLING') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <? foreach ($model['MODIFICATIONS'] as $modification) { ?>
                                    <tr>
                                        <td align="center">
                                            <?= $modification['carDesc'] ?>
                                        </td>
                                        <td align="right">
                                            <?= substr($modification['yearOfConstructionFrom'], -2, 2) ?>.<?= substr($modification['yearOfConstructionFrom'], 0, 4) ?>
                                        </td>
                                        <td align="right">
                                            <?= $modification['powerKwFrom'] ?>
                                        </td>
                                        <td align="right">
                                            <?= $modification['powerHpFrom'] ?>
                                        </td>
                                        <td align="right">
                                            <?= $modification['cylinderCapacity'] ?>
                                        </td>
                                        <td align="right">
                                            <?= $modification['constructionType'] ?>
                                        </td>
                                    </tr> 
                                <? } ?>
                            </tbody>
                        </table>
                    </div>
                <? } ?>
            <? } ?>
        </div>
    <? } ?>
<? } ?>