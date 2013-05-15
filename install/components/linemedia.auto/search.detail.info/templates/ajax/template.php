<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>

<?  // Сохдание ссылки для ajax-запроса.
    
    CUtil::InitJSCore(array('window', 'ajax')); 
    
    $arDialogParams = array(
      'content_url' => '/bitrix/components/linemedia.auto/search.detail.info/ajax.php?brand='.$arParams['BRAND'].'&article='.$arParams['ARTICLE'].'&article_id='.$arParams['ARTICLE_ID'],  
      'title'       => GetMessage('LM_AUTO_SEARCH_DETAIL_INFO_ADDITIONAL_INFO').' &laquo;'.$arParams['BRAND'].' '.$arParams['ARTICLE'].'&raquo;',
      'width'       => 600,
      'height'      => 700,
      'min_width'   => 400,
      'min_height'  => 400,
      'resizable'   => false,
      'draggable'   => true,
   );
   
   $href = '(new BX.CDialog('.CUtil::PhpToJsObject($arDialogParams).')).Show()';
?>

<a href="javascript:void(0)" onclick="javascript:<?= $href ?>" title="<?= GetMessage('LM_AUTO_SEARCH_ADDITIONAL_INFO') ?>">
    <div class="lm-auto-icon-info"></div>
</a>
