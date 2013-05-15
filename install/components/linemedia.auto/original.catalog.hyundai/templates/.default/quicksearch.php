<?php

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.quicksearch.js');


?>

<div class="tlm-auto-original hyundai quicksearch">
    <span><strong><?=GetMessage('LM_AUTO_QUICK_FILTER')?></strong></span><br />
    <input type="text" placeholder="<?=GetMessage('LM_AUTO_QUICK_FILTER_PLACEHOLDER')?>" name="quick_search" id="quick_search" placeholder="<?=GetMessage('LM_AUTO_QUICK_FILTER_HINT')?>" />
</div>