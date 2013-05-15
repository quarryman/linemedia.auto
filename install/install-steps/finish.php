<?php

IncludeModuleLangFile(__FILE__);

?>

<?= CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_MAIN_INSTALL_SUCCESS"), 'TYPE' => 'OK')) ?>

<ul>
    <li><a target="_blank" href="http://auto.linemedia.ru/"><?= GetMessage('LM_AUTO_MAIN_INSTALL_PRODUCT_URL') ?></a></li>
    <li><a target="_blank" href="http://auto.linemedia.ru/cms/video"><?= GetMessage('LM_AUTO_MAIN_INSTALL_VIDEO_URL') ?></a></li>
    <li><a target="_blank" href="http://auto.linemedia.ru/2.0/features/tecdoc"><?= GetMessage('LM_AUTO_MAIN_INSTALL_TECDOC_URL') ?></a></li>
</ul>

<form action="/bitrix/admin/linemedia.auto_sale_orders_list.php" method="post">
    <input type="submit" value="<?= GetMessage('LM_AUTO_MAIN_INSTALL_GO_TO_MODULE') ?>" />
</form>

<? /*
<form action="/bitrix/admin/wizard_install.php" method="get">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>" />
    <input type="hidden" name="wizardName" value="linemedia.auto:linemedia:auto" />
    <input type="submit" name="" value="<?= GetMessage('LM_AUTO_MAIN_START_WIZARD') ?>" />
</form>
*/ ?>