<div class="lm-auto-catalog-original kia vin">
	<b><?=GetMessage('LM_AUTO_ORIG_VIN_DECODER')?></b>
	<form action="<?=$arParams['VIN_URL']?>" method="get" id="lm-auto-vin-frm">
		<input type="text" name="vin" id="lm-auto-vin-inp" placeholder="1G0MG35X07Y0070EX" />
		<input type="submit" value="<?=GetMessage('LM_AUTO_ORIG_VIN_DECODE_SBM')?>" />
	</form>
</div>