<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="sale_basket_small">
    <? if (!empty($arResult['ITEMS'])) { ?>
        <div class="h4"><?= GetMessage('YOUR_BASKET') ?></div>
        <br/>
        <ol class="small_cart_ol">
            <? foreach ($arResult['ITEMS'] as $item) { ?>
                <li>
                    <a title="<?= $item['NAME'] ?>" href="<?= $item['SEARCH_URL'] ?>">
                        <b><?= $item['NAME'] ?></b>
                    </a>
                    <div class="nums">
                        <b><?= CurrencyFormat($item['PRICE'], CCurrency::GetBaseCurrency()) ?></b>
                        <?= $item['QUANTITY'] ?> רע.
                    </div>
                </li>
            <? } ?>
        </ol>
        <form method="get" action="<?= $arParams['PATH_TO_BASKET'] ?>">
            <input class="btn btn-mini btn-info" type="submit" value="<?= GetMessage('GO_TO_BASKET') ?>" />
        </form>
    <? } else { ?>
        <div class="h4"><?= GetMessage('BASKET_EMPTY') ?></div>
    <? } ?>
</div>