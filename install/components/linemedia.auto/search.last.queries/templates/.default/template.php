<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(count($arResult['QUERIES']) == 0) return;
?>

<div class="lm-auto-last-queries">
    <h4><?=GetMessage('LM_AUTO_LAST_QUERIES_TITLE')?></h4>
    <?foreach($arResult['QUERIES'] AS $query){?>
        <a href="<?=$query['url']?>"><?=$query['title']?></a>
    <?}?>
</div>
