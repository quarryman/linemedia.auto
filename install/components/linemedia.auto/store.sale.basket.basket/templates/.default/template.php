<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<script type="text/javascript">
    var ajaxrecalc  = '<?= $arParams['AJAX_RECALC'] ?>';
    var ajaxurl     = '<?= $arResult['AJAX_URL'] ?>/ajax.php?AJAX=Y&<?= bitrix_sessid_get() ?>';
    var ajaxparams  = {
        'COUNT_DISCOUNT_4_ALL_QUANTITY': '<?= $arParams['COUNT_DISCOUNT_4_ALL_QUANTITY'] ?>',
        'HIDE_COUPON': '<?= $arParams['HIDE_COUPON'] ?>',
        'QUANTITY_FLOAT': '<?= $arParams['QUANTITY_FLOAT'] ?>',
    };
</script>

<?

if (StrLen($arResult["ERROR_MESSAGE"]) <= 0) {
	$arUrlTempl = Array(
		"delete" => $APPLICATION->GetCurPage()."?action=delete&id=#ID#",
		"shelve" => $APPLICATION->GetCurPage()."?action=shelve&id=#ID#",
		"add" => $APPLICATION->GetCurPage()."?action=add&id=#ID#",
	);
	?>
	<script>
    	function ShowBasketItems(val)
    	{
    		if (val == 2) {
    			if (document.getElementById("id-cart-list"))
    				document.getElementById("id-cart-list").style.display = 'none';
    			if (document.getElementById("id-shelve-list"))
    				document.getElementById("id-shelve-list").style.display = 'block';
    		} else if (val == 3) {
    			if (document.getElementById("id-cart-list"))
    				document.getElementById("id-cart-list").style.display = 'none';
    			if (document.getElementById("id-shelve-list"))
    				document.getElementById("id-shelve-list").style.display = 'none';
    		} else {
    			if (document.getElementById("id-cart-list"))
    				document.getElementById("id-cart-list").style.display = 'block';
    			if (document.getElementById("id-shelve-list"))
    				document.getElementById("id-shelve-list").style.display = 'none';
    		}
    	}
	</script>
	<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="basket_form">
		<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items.php"); ?>
        <? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_delay.php"); ?>
	</form>
<?
}
else {
	ShowNote($arResult["ERROR_MESSAGE"]);
}
?>