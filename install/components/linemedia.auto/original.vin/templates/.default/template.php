<?if($arResult['VIN_CODE'] == ''){?>
<div class="lm-auto-catalog-original vin">
	<form action="" method="get" id="lm-auto-vin-frm">
		<input type="text" name="vin" id="lm-auto-vin-inp" placeholder="1G0MG35X07Y0070EX" value="<?=htmlspecialchars($arResult['VIN_CODE'])?>" />
		<input type="submit" value="<?=GetMessage('LM_AUTO_ORIG_VIN_DECODE_SBM')?>" />
	</form>
</div>

<?}else{?>

<div class="lm-auto-catalog-original vin">
	<form action="" method="get" id="lm-auto-vin-frm">
		<input type="text" name="vin" id="lm-auto-vin-inp" value="<?=htmlspecialchars($arResult['VIN_CODE'])?>" placeholder="1G0MG35X07Y0070EX" />
		<input type="submit" value="<?=GetMessage('LM_AUTO_ORIG_VIN_DECODE_SBM')?>" />
	</form>
</div>

<div class="lm-auto-catalog-original vin">

	<h1><?=GetMessage('LM_AUTO_ORIG_VIN_DECODER')?> <?=htmlspecialchars($arResult['VIN_CODE'])?></h1>
	
	
	<?
	if($arResult['ERROR']) {
		ShowError($arResult['ERROR']);
		return;
	}
	?>
	
	<?if($arResult['VIN']['ID_mfa'] > 0) {?>
	
	
	<div class="lm-auto-vin-main">
		<h2><?=GetMessage('LM_AUTO_ORIG_VIN_MODEL')?>:	<span><?=$arResult['VIN']['model']?></span></h2>
		
		<a class="btn btn-primary" href="<?=$arResult['CATALOG_URL']?>"><?=GetMessage('LM_AUTO_ORIG_VIN_OPEN_CATALOG')?> <?=$arResult['VIN']['model']?></a>
	</div>
	
	
	
    
    
    
    <table class="lm-auto-catalog-original vin-table">
    	<tr>
    		<th style="width: 150px;"><?=GetMessage('LM_AUTO_ORIG_VIN_YEAR')?></th>
    		<td><?=$arResult['VIN']['Year']?></td>
    	</tr>
    	<tr>
    		<th><?=GetMessage('LM_AUTO_ORIG_VIN_MAKE')?></th>
    		<td><?=$arResult['VIN']['Make']?></td>
    	</tr>
    	<tr>
    		<th><?=GetMessage('LM_AUTO_ORIG_VIN_BODY')?></th>
    		<td><?=$arResult['VIN']['Body']?></td>
    	</tr>
    	<tr>
    		<th><?=GetMessage('LM_AUTO_ORIG_VIN_CODE')?></th>
    		<td><?=$arResult['VIN']['Code']?></td>
    	</tr>
    	<tr>
    		<th><?=GetMessage('LM_AUTO_ORIG_VIN_ENGINE')?></th>
    		<td><?=$arResult['VIN']['Engine']?></td>
    	</tr>
    	<tr>
    		<th><?=GetMessage('LM_AUTO_ORIG_VIN_ENGINE_TYPE')?></th>
    		<td><?=$arResult['VIN']['EngineType']?></td>
    	</tr>
    	<tr>
    		<th><?=GetMessage('LM_AUTO_ORIG_VIN_ENGINE_NUM')?></th>
    		<td><?=$arResult['VIN']['EngineNum']?></td>
    	</tr>
    	<tr>
    		<th><?=GetMessage('LM_AUTO_ORIG_VIN_TRANSM')?></th>
    		<td><?=$arResult['VIN']['Transm']?></td>
    	</tr>
    </table>
    
    
	<div class="lm-auto-catalog-original-vin decode">
	
		<h3><?=GetMessage('LM_AUTO_ORIG_VIN_DECODE')?></h3>
		
		<?foreach($arResult['VIN']['decode'] AS $decode){?>
			<div class="decode">
				<b><?=$decode['Code']?></b> <?=$decode['Value']?>
			</div>
		<?}?>
	
	</div>
	
	
	<div class="lm-auto-catalog-original-vin options">
	
		<h3><?=GetMessage('LM_AUTO_ORIG_VIN_OPTIONS')?></h3>
		
		<?foreach($arResult['VIN']['options'] AS $option){?>
			<div>
				<?=$option['Description']?>
			</div>
		<?}?>
	
	</div>
	
	<p>
        <a class="btn btn-primary" href="<?=$arResult['CATALOG_URL']?>"><?=GetMessage('LM_AUTO_ORIG_VIN_OPEN_CATALOG')?> <?=$arResult['VIN']['model']?></a>
    </p>
	
	<?}else{?>
		<h3><?=GetMessage('LM_AUTO_ORIG_VIN_DECODE_ERROR')?></h3>
	<?}?>
	
</div>
<?}?>