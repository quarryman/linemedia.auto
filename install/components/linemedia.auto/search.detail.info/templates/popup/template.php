<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>

<? $APPLICATION->SetAdditionalCSS($this->GetFolder().'/style.css'); ?>
<? $APPLICATION->AddHeadScript($this->GetFolder().'/script.js'); ?>

<? $title = $arParams['BRAND'].' '.$arParams['ARTICLE'] ?>

<h2><?= GetMessage('TITLE_ARTICLE_NUMBER') ?>: <?= $title ?></h2>

<? if (isset($arResult['IMAGE'])) { ?>
    <div class="lm_popup_img"><img src="<?= $arResult['IMAGE'] ?>" alt="<?= $title ?>" title="<?= $title ?>" /></div>
<? } ?>

<? if (!empty($arResult['DATA']['immediateAttributs']['array'])) { ?>
    <h3><?= GetMessage('TITLE_ADDITIONAL_FEATURES') ?></h3>
    <div class="standartTable">
        <table class="tecdoc_details_info" cellpadding="0" cellspacing="0">
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
    <br/>
<? } ?>

<? if ($arParams['SHOW_ORIGINAL_ITEMS'] == 'Y') { ?>
    <h3><?= GetMessage('TITLE_CONFORMITY_ORIGINAL_NUMBERS') ?></h3>
    <? if (!empty($arResult['DETAIL']['oenNumbers']['array'])) { ?>
        <div class="standartTable">
            <table class="tecdoc_details_info" cellpadding="0" cellspacing="0">
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
         <br/>
    <? } ?>
<? } ?>
<? if ($arParams['SHOW_APPLICABILITY'] == 'Y') { ?>
    <br/>
    <h3><?= GetMessage('TITLE_CONFORMITY_SHOW_APPLICABILITY') ?></h3>
    <? if (!empty($arResult['APPLICABILITY'])) { ?>
        <div class="applicability">
            <div class="applicability-firms">
                <? foreach ($arResult['APPLICABILITY'] as $firm) { ?>
                    <a href="javascript:void(0)" class="applicability-firm" data-manuid="<?= $firm['manuId'] ?>"><?= $firm['manuName'] ?></a>
                <? } ?>
            </div>
            
            <input type="hidden" id="template" value="<?= $this->getName() ?>" />
            <input type="hidden" id="article_id" value="<?= $arParams['ARTICLE_ID'] ?>" />
            <input type="hidden" id="article_link_id" value="<?= $arParams['ARTICLE_LINK_ID'] ?>" />
            <input type="hidden" id="sessid" value="<?= bitrix_sessid() ?>" />
            
            <div class="clear"></div>
            
            <div id="lm-auto-applicability"></div>
        </div>
    <? } ?>
<? } ?>