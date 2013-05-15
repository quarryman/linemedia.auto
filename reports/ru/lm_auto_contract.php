<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?php
if(!CModule::IncludeModule("iblock")){
	echo ShowError("Error include module iblock");
}
if(!CModule::IncludeModule("linemedia.auto")){
	echo ShowError("Error include module autoportal");
}
//$oAutoportal = new CLMAutoportal;
//$oCLMDealer = new CLMAutoportalDealer;
$aPaySystem = CSalePaySystem::GetByID($arOrder['PAY_SYSTEM_ID']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?=LANG_CHARSET?>">
<title langs="ru">Договор с покупателем</title>
<style>
<!--
 /* Style Definitions */
p.MsoNormal, li.MsoNormal, div.MsoNormal
	{mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:12.0pt;
	font-family:"Arial";
	mso-fareast-font-family:"Arial";}
body {
  font-size:12.0pt;
	font-family:"Arial";
	mso-fareast-font-family:"Arial";}
}
p
	{margin-right:0cm;
	mso-margin-top-alt:auto;
	mso-margin-bottom-alt:auto;
	margin-left:0cm;
	mso-pagination:widow-orphan;
	font-size:12.0pt;
	font-family:"Arial";
	mso-fareast-font-family:"Arial";}
@page Section1
	{size:595.3pt 841.9pt;
	margin:2.0cm 42.5pt 2.0cm 3.0cm;
	mso-header-margin:35.4pt;
	mso-footer-margin:35.4pt;
	mso-paper-source:0;}
div.Section1
	{page:Section1;}
-->
</style>
</head>

<body bgcolor=white lang=RU style='tab-interval:35.4pt'>

<div class=Section1>
<table width="100%" align="center">
  <tr>
    <th width="50%" align="left">Продавец:</th>
    <th width="50%" align="left">Покупатель:</th>
  </tr>
  <tr>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td>Адрес: <?= $arParams["COUNTRY"].", ".$arParams["INDEX"].", г. ".$arParams["CITY"].", ".$arParams["ADDRESS"]; ?></td>
    <td>Логин: <u><?= $arUser['LOGIN'] ?></u></td>
  </tr>
  <tr>
    <td>Телефон: <?= $arParams["PHONE"] ?></td>
    <td>Пароль: <input size="26" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="__________________"></td>
  </tr>
  <tr>
    <td>ИНН: <?= $arParams["INN"] ?> / КПП: <?= $arParams["KPP"] ?></td>
    <td>Адрес для входа: <input size="50" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="http://<?=$_SERVER['SERVER_NAME'];?>/"></td>
  </tr>
  <tr>
    <td>Банковские реквизиты:</td>
    <td></td>
  </tr>
  <tr>
    <td>р/с <?=$arParams["RSCH"]?> в <?= $arParams["RSCH_BANK"] ?> г. <?= $arParams["RSCH_CITY"] ?></td>
    <td>Адрес:
    <input size="100" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="
<?
	if (strlen($arOrderProps["ZIP"])>0) echo $arOrderProps["ZIP"].", ";
	
	//$arVal = CSaleLocation::GetByID($arOrderProps["F_LOCATION"], "ru");
	if(strlen($arOrderProps["LOCATION_COUNTRY"])>0 && strlen($arOrderProps["CITY_NAME"])>0)
		echo htmlspecialchars($arOrderProps["LOCATION_COUNTRY"]);
	elseif(strlen($arOrderProps["LOCATION_COUNTRY"])>0 || strlen($arOrderProps["LOCATION_CITY"])>0)
		echo htmlspecialchars($arOrderProps["LOCATION_COUNTRY"]);

	if (strlen($arOrderProps["CITY"])>0) echo ", г. ".$arOrderProps["CITY"];
	if (strlen($arOrderProps["ADDRESS"])>0 && strlen($arOrderProps["CITY"])>0) 
		echo ", ".$arOrderProps["ADDRESS"];
	elseif(strlen($arOrderProps["ADDRESS"])>0)
		echo $arOrderProps["ADDRESS"];
?>
">
    </td>
  </tr>
  <tr>
    <td>к/с <?= $arParams["KSCH"] ?></td>
    <td>Контактное лицо: <input size="50" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="<?echo $arOrderProps["FIO"];?>"></td>
  </tr>
  <tr>
    <td>БИК <?= $arParams["BIK"] ?></td>
    <td>Платежная система: [<?= $arOrder['PAY_SYSTEM_ID'] ?>] <?if(!empty($aPaySystem)):?><?=htmlspecialchars($aPaySystem['NAME']);?><?endif;?></td>
  </tr>
</table>
<br />
<div style="text-align: center;"><h3>Договор № <input size="8" style="border:0px solid #000000;font-size:18px;font-weight:bold;" type="text" value="______"></h3></div>
<br />
Условия договора: <br />
<textarea rows="6" style="width: 100%; border:0px solid #000000;font-size:14px;" wrap="soft">
_____________________________________________________________________________________
_____________________________________________________________________________________
_____________________________________________________________________________________
_____________________________________________________________________________________
</textarea>

<br />
<br />
<p><b>Состав заказа</b> N: <?= $ORDER_ID?>   от   <?= $arOrder["DATE_INSERT_FORMAT"]?></p>

<?
//состав заказа
ClearVars("b_");
$db_basket = CSaleBasket::GetList(($b="NAME"), ($o="ASC"), array("ORDER_ID"=>$ORDER_ID));
if ($db_basket->ExtractFields("b_")):
	?>
	<table border="0" cellspacing="0" cellpadding="2" width="100%">
		<tr bgcolor="#E2E2E2">
			<td align="center" style="border: 1pt solid #000000; border-right:none;">№</td>
			<td align="center" style="border: 1pt solid #000000; border-right:none;">Произв-ль</td>
			<td align="center" style="border: 1pt solid #000000; border-right:none;">Артикул</td>
			<td align="center" style="border: 1pt solid #000000; border-right:none;">Наименование</td>
			<td nowrap align="center" style="border: 1pt solid #000000; border-right:none;">Кол-во</td>
			<td nowrap align="center" style="border: 1pt solid #000000; border-right:none;">Цена, руб</td>
			<td nowrap align="center" style="border: 1pt solid #000000; border-right:none;">Сумма, руб</td>
			<td nowrap align="center" style="border: 1pt solid #000000;">Срок поставки, дней</td>
		</tr>
		<?
		$n = 1;
		$sum = 0.00;
		do{
		    $aItemProps = Array();
		    $oBasketProp = CSaleBasket::GetPropsList(Array('SORT' => 'ASC'), Array('BASKET_ID' => $b_ID), false, false, Array('NAME', 'VALUE', 'CODE', 'SORT'));
		    while($aBaksetPropItem = $oBasketProp->Fetch()){
			$aItemProps[$aBaksetPropItem['CODE']] = $aBaksetPropItem;
		    }
		    unset($aBaksetPropItem, $oBasketProp);
			?>
			<tr valign="top">
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo $n++ ?>
				</td>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?=(!empty($aItemProps['brand_title']['VALUE'])?htmlspecialchars($aItemProps['brand_title']['VALUE']):'---');?>
				</td>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?=(!empty($aItemProps['art']['VALUE'])?htmlspecialchars($aItemProps['art']['VALUE']):'---');?>
				</td>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo $b_NAME; ?>
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo $b_QUANTITY; ?>
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo SaleFormatCurrency(($b_PRICE), $b_CURRENCY, true) ?>
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo SaleFormatCurrency(($b_PRICE)*$b_QUANTITY, $b_CURRENCY, true) ?>
				</td>
				<td align="center" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;">
<?
$sDeliveryTime = '---';
if(!empty($aItemProps['supplier_id']['VALUE'])){
  $iSuppliersIBlockID = COption::GetOptionString('linemedia_auto_portal', 'LM_AUTOPORTAL_SUPPLIERS_IBLOCK_ID');
  $aSupplier = CIBlockElement::GetList(Array(), Array('IBLOCK_ID' => $iSuppliersIBlockID, 'PROPERTY_id' =>  $aItemProps['supplier_id']['VALUE']), false, false, Array('ID', 'IBLOCK_ID', 'PROPERTY_delivery_time'))->Fetch();
  if(!empty($aSupplier['PROPERTY_DELIVERY_TIME_VALUE'])){
	//$sDeliveryTime = $oCLMDealer->SetUserDTimeDealer((int)$aSupplier['PROPERTY_DELIVERY_TIME_VALUE'], $arUser['ID']); //Set new delivery time Dealers params
	//$sDeliveryTime = $oAutoportal->GetFriendlyDeliveryTime($sDeliveryTime); //Set new friendly delivery time;
  }
}
echo $sDeliveryTime;
?>
				</td>
			</tr>
			<?
			$sum += doubleval(($b_PRICE)*$b_QUANTITY);
		}
		while ($db_basket->ExtractFields("b_"));
		?>

		<?if (False && DoubleVal($arOrder["DISCOUNT_VALUE"])>0):?>
			<tr>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo $n++?>
				</td>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					Скидка
				</td>
				<td valign="top" align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">1 </td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo SaleFormatCurrency($arOrder["DISCOUNT_VALUE"], $arOrder["CURRENCY"], true) ?>
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;">
					<?echo SaleFormatCurrency($arOrder["DISCOUNT_VALUE"], $arOrder["CURRENCY"], true) ?>
				</td>
			</tr>
		<?endif?>

		<?if (DoubleVal($arOrder["PRICE_DELIVERY"])>0):?>
			<tr>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo $n?>
				</td>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					Доставка <?
					$arDelivery_tmp = CSaleDelivery::GetByID($arOrder["DELIVERY_ID"]);
					echo ((strlen($arDelivery_tmp["NAME"])>0) ? "([".$arOrder["DELIVERY_ID"]."] " : "" );
					echo $arDelivery_tmp["NAME"];
					echo ((strlen($arDelivery_tmp["NAME"])>0) ? ")" : "" );
					?>
				</td>
				<td colspan="2" align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none; border-right: none;"></td>
				<td valign="top" align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">1 </td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"], true) ?>
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none; border-right: none;">
					<?echo SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"], true) ?>
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;"></td>
			</tr>
		<?endif?>

		<?
		$db_tax_list = CSaleOrderTax::GetList(array("APPLY_ORDER"=>"ASC"), Array("ORDER_ID"=>$ORDER_ID));
		while ($ar_tax_list = $db_tax_list->Fetch()){
			?>
			<tr>
				<td align="right" bgcolor="#ffffff" colspan="4" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?
					if ($ar_tax_list["IS_IN_PRICE"]=="Y")
					{
						echo "В том числе ";
					}
					echo htmlspecialchars($ar_tax_list["TAX_NAME"]); 
					if ($ar_tax_list["IS_PERCENT"]=="Y")
					{
						echo " (".$ar_tax_list["VALUE"]."%)";
					}
					?>:
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;">
					<?echo SaleFormatCurrency($ar_tax_list["VALUE_MONEY"], $arOrder["CURRENCY"], true)?>
				</td>
			</tr>
			<?
		}
		?>

<? /*
		<?if (DoubleVal($arOrder["TAX_VALUE"])>0):?>
			<tr>
				<td align="right" bgcolor="#ffffff" colspan="4" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					Налоги:
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;">
					<?echo SaleFormatCurrency($arOrder["TAX_VALUE"], $arOrder["CURRENCY"])?>
				</td>
			</tr>
		<?endif?>
   */ ?>

		<tr>
			<td align="right" bgcolor="#ffffff" colspan="6" style="border: 1pt solid #000000; border-right:none; border-top:none;">Итого:</td>
			<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none; border-right: none;"><?echo SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"], true) ?></td>
			<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;"></td>
		</tr>
	</table>
<?endif?>
<p><b>Итого к оплате:</b> 
	<?
	if ($arOrder["CURRENCY"]=="RUR" || $arOrder["CURRENCY"]=="RUB"){
		echo Number2Word_Rus($arOrder["PRICE"]);
	}else{
		echo SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]);
	}
	?>.</p>

<textarea rows="6" style="width: 100%; border:0px solid #000000;font-size:16px; font-family: Arial;" wrap="soft">
<?=$arOrder['COMMENTS'];?>
</textarea>
<? /* <p>Внесена предоплата в размере 3000 руб. (Три тысячи рублей, 00 копеек).</p> */ ?>
<!-- END REPORT BODY -->

<p>&nbsp;</p>
<table border=0 cellspacing=0 cellpadding=0 width="100%">
 <tr>
  <td nowrap>
  <p class=MsoNormal>Продавец:</p>
  </td>
  <td nowrap>
  <p class=MsoNormal>_______________ <input size="16" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="/ <?echo ((strlen($arParams["DIRECTOR"]) > 0) ? $arParams["DIRECTOR"] : "_______________")?> /"></p>
  </td>
  <td>
  <p class=MsoNormal>&nbsp;</p>
  </td>
  <td  nowrap>
  <p class=MsoNormal>Покупатель:</p>
  </td>
  <td  nowrap>
  <p class=MsoNormal>_______________ <input size="16" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="/ <?echo ((strlen($arParams["BUHG"]) > 0) ? $arParams["BUHG"] : "_______________")?> /"></p>
  </td>
 </tr>
</table>
</div>
</body>
</html>