<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die() ?>

<? // Заголовок.
$title  = $arResult['DATA']['directArticle']['articleName'].' ';
$title .= $arResult['DATA']['directArticle']['brandName'].' ';
$title .= $arResult['DATA']['directArticle']['articleNo'];
?>

<h2><?= $title ?></h2>

<? if (!empty($arParams['BACKURL'])) { ?>
    <div class="lm-auto-main-tecdoc-backurl">
        <a href="<?= $arParams['BACKURL'] ?>"><?= GetMessage('LM_AUTO_MAIN_TCD_RETURN_CATALOG') ?></a>
    </div>
<? } ?>

<? if (isset($arResult['IMAGE'])) { ?>
    <img src="<?= $arResult['IMAGE'] ?>" alt="<?= $title ?>" title="<?= $title ?>" />
<? } ?>

<? if (!empty($arResult['DATA']['immediateAttributs']['array'])) { ?>
    <br/>
    <h3><?= GetMessage('TITLE_ADDITIONAL_FEATURES') ?></h3>
    <div class="standartTable">
        <table class="tecdoc_details silver_table" cellpadding="0" cellspacing="0">
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
    <br/>
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

<? if ($arParams['SHOW_SEARCH_FORM'] == 'Y') { ?>
    <br />
    <h3><?= GetMessage('TITLE_CONFORMITY_SEARCH_FORM') ?></h3>
    <?  // Цены.
        $APPLICATION->IncludeComponent(
            "linemedia.auto:search.results",
            ".default",
            array(
                "QUERY"             => $arResult['DATA']['directArticle']['articleNo'],
                "BRAND_TITLE"       => $arResult['DATA']['directArticle']['brandName'],
                "SET_TITLE"         => "N",
                "SHOW_BLOCKS"       => "results",
                "REMOTE_SUPPLIERS_AJAX" => array(),
            ),
            false
        );
    ?>
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
            
            <input type="hidden" id="article_id" value="<?=$arParams['ARTICLE_ID']?>" />
            <input type="hidden" id="article_link_id" value="<?=$arParams['ARTICLE_LINK_ID']?>" />
            <input type="hidden" id="sessid" value="<?=bitrix_sessid()?>" />
            
            <div class="clear"></div>
            
            <div id="lm-auto-applicability"></div>
            
        </div>
    <? } ?>
<? } ?>

<? if ($arParams['SHOW_SEO'] == 'Y') { ?>
    <div class="seo-description">
        <?= $arResult['SEO']['TEXT'] ?>
    </div>
<? } ?>

