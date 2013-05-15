<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>

<? if ($arResult['FORM_TYPE'] == 'login') { ?>
    <p class="cart">
        <noindex>
            <a class="btn btn-success" rel="nofollow" href="<?= $arResult['AUTH_URL'] ?>">
                <i class="icon-ok icon-white"></i>
                <?= GetMessage('SAF_AUTHORIZE') ?>
            </a>
        </noindex>
        <noindex>
            <a class="btn" rel="nofollow" style="margin-top:5px" href="<?= $arResult['AUTH_REGISTER_URL'] ?>">
                <i class="icon-user "></i>
                <?= GetMessage('SAF_REGISTER') ?>
            </a>
        </noindex>
    </p>
<? } else { ?>
    <?
        $name = trim($USER->GetFullName());  
        if (strlen($name) <= 0) {
            $name = $USER->GetLogin();
        }
    ?>
    <a href="<?= $arResult['PROFILE_URL'] ?>"><?= htmlspecialcharsEx($name) ?></a>
    <a class="btn btn-mini pull-right" href="<?= $APPLICATION->GetCurPageParam("logout=yes", array("logout")) ?>"><?= GetMessage('SAF_LOGOUT') ?></a>
<? } ?>
