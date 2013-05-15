<?php

$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.quicksearch.js');
$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery-ui-1.10.0.custom.min.js');
$APPLICATION->AddHeadScript($this->GetFolder().'/js/zoom.js');

$APPLICATION->SetAdditionalCSS($this->GetFolder().'/js/css/jquery-ui-1.10.0.custom.min.css');
$APPLICATION->SetAdditionalCSS($this->GetFolder().'/js/css/zoom.css');

if (!empty($arResult['ERRORS'])) {
    foreach ($arResult['ERRORS'] as $error) {
        ShowError($error);
    }
}


if ($arResult['EDIT_MODE']) {
	?><form action="" method="post" id="tecdoc-items-edit"><?
}


?>

