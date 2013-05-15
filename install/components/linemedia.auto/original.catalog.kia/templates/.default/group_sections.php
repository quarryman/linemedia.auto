<?
include(dirname(__FILE__) . '/vin_frm.php');
include(dirname(__FILE__) . '/quicksearch.php');
?>
<div class="lm-auto-catalog-original kia group_sections">
    <?foreach($arResult['GROUP_SECTIONS'] AS $group_section){?>
    <div class="group_section">
        <div class="group_sections_img">
            <a href="<?=$arParams['SEF_FOLDER'] . intval($arResult['MODEL']['ID_mod'])?>/<?=intval($arResult['GROUP_TYPE']['ID_typ'])?>/<?=intval($arResult['GROUP']['ID_grp'])?>/<?=$group_section['ID_sec']?>/">
                <img src="<?=htmlspecialchars($group_section['Image'])?>" alt="<?=htmlspecialchars($group_section['Name'])?> <?=htmlspecialchars($arResult['BRAND']['Name'])?> <?=htmlspecialchars($arResult['MODEL']['Name'])?>" />
            </a>
        </div>
                
        <div class="group_sections_title">
            <a href="<?=$arParams['SEF_FOLDER'] . intval($arResult['MODEL']['ID_mod'])?>/<?=intval($arResult['GROUP_TYPE']['ID_typ'])?>/<?=intval($arResult['GROUP']['ID_grp'])?>/<?=$group_section['ID_sec']?>/"><?=htmlspecialchars($group_section['Name'])?></a>
        </div>
        
        <!--div class="group_sections_desc"><?=nl2br(htmlspecialchars(str_replace('</br>',"\n",$group_section['Description'])))?></div-->
    </div>
    <?}?>

</div>
