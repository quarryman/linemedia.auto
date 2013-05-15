<?
if (!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true) die();
?>

<div class="textblock">
    <?=$arResult['TEXT']?>
	<?if ($APPLICATION->GetShowIncludeAreas()) { ?>
		<a title="Редактировать область" href="javascript:void(0);" data-set-meta="<?=$arParams['SET_META']?>" data-h1="<?=$arParams['SET_H1']?>" data-iblock-id="<?=$arParams['IBLOCK_ID']?>" data-block="<?=$arParams['WHAT_SHOW']?>" data-id="<?=$arResult['FOUND']?$arResult['ELEMENT']['ID']:0?>" data-url="<?=$APPLICATION->GetCurPage(false)?>" class="seoBlockEdit">
			<img src="<?=$this->GetFolder()?>/i/edit.png">
		</a>
	<?}?>
</div>

<script type="text/javascript">
$(function(){
	$('a.seoBlockEdit').unbind().click(function(){
			var $a = $(this);
			$.ajax({
				type:"GET",
				url:"/bitrix/components/linemedia.auto/seo.textblock/edit.php",
				data: {iblock_id:$a.attr('data-iblock-id'),url:$a.attr('data-url'), id:$a.attr('data-id'), block:$a.attr('data-block'), h1:$a.attr('data-h1'), meta:$a.attr('data-set-meta')},
				success:function (html) {
					var params = {
						content : html,
						icon: 'head-block',
						resizable: true,
						draggable: true,
						content_url:"/bitrix/components/linemedia.auto/seo.textblock/edit.php",
						buttons: [
							'',
							BX.CDialog.btnSave, BX.CDialog.btnCancel//, BX.CDialog.btnClose
						]
					};
					(new BX.CDialog(params)).Show();
				},
				error:function (data) {
					alert("window error");
				}
			});
			return false;
	});
});
</script>
