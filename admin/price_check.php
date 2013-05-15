<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$modulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


IncludeModuleLangFile(__FILE__);

CUtil::InitJSCore(array('window', 'ajax'));

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}


/*
 * Просмотр логов
 */
if ($_GET['ajax'] == 'check_price') {
	
	$article 		= (string) $_POST['article'];
	$brand_title 	= (string) $_POST['brand_title'];
	$extra 			= (string) $_POST['extra'];
	try {
		$extra = json_decode($extra, true);
	} catch (Exception $e) {
		$extra = array();
	}
	$user 	 		= (int)    $_POST['user'];
	$date 	 		= (string) $_POST['date'];
	
	
	
	/*
	 * Создаём объект поиска.
	 */
	try {
	    $search = new LinemediaAutoSearch();
	} catch (Exception $e) {
	    die($e->GetMessage());
	}
	
	$search->setSearchQuery($article);
    $search->setSearchCondition('brand_title', $brand_title);
    $search->setSearchCondition('extra', $extra);

	/*
	 * Выполняем запрос.
	 */
	try {
	    $search->execute();
	} catch (Exception $e) {
	    die($e->GetMessage());
	}
	
	
	/*
	 * Ошибки от модулей.
	 */
	$modules_exceptions = $search->getThrownExceptions();
	
	if(count($modules_exceptions))
		echo '<h2>' . GetMessage('LM_AUTO_PRICE_WARNINGS') . '</h2>';
	foreach ($modules_exceptions as $exception) {
	    echo '<div class="module-exception">' . $exception->GetMessage() . '</div>';
	}
	
	
	
	switch ($search->getResultsType()) {
	    case 'catalogs':
	    	
	    	
	    	echo '<h2>' . GetMessage('LM_AUTO_PRICE_ADJUST') . '</h2>';
	    	
	        $catalogs = $search->getResultsCatalogs();

	        foreach ($catalogs as $id => $catalog) {
    	        $brand_title = strtoupper($catalog['brand_title']);
    	        $extra = (array) $catalog['extra'];

	            ?><div><a href="javascript:;" class="lm-details" data-brand-title="<?=htmlspecialchars($brand_title)?>" data-extra="<?=htmlspecialchars(json_encode($extra))?>"><?=htmlspecialchars($brand_title)?></a></div><?
	        }
	        
	        exit;
	    break;
	    case '404':
	    	die(GetMessage('LM_AUTO_PRICE_404'));
	    break;
	    case 'parts':
	    
	        $parts = $search->getResultsParts();
	        
	        /*
	         * Сортировка групп деталей.
	         */
	        asort($parts);
	        if (isset($parts['analog_type_N'])) {
	            $N['analog_type_N'] = $parts['analog_type_N'];
	            unset($parts['analog_type_N']);
	            $parts = array_merge_recursive($N, $parts);
	        }
	        	        
	        /*
	         * Пробежимся по запчастям и ...
	         */
	        foreach ($parts as $group_id => $sparts) {
	            
	            foreach ($sparts as $i => $part) {
	                /*
	                 * Объект запчасти
	                 */
	                $part_obj = new LinemediaAutoPart($part['id'], $part);
	                
	                /*
	                 * Посчитаем цену товара
	                 */
	                $price = new LinemediaAutoPrice($part_obj);
	                $price->enableDebugCollection();
	                $price->setUserID($user);
	                $price->setDate($date);
	                
	                $price_calc = $price->calculate();
	                $parts[$group_id][$i]['price_src'] = $price_calc;
	                $parts[$group_id][$i]['price'] = CurrencyFormat($price_calc, $price->getCurrency());
	                
	                $parts[$group_id][$i]['price_debug'] = $price->getDebug();
	                
	                
	                
	                
	                /*
			         * Закешируем данные
			         */
			        static $suppliers = array();
			        $supplier_id = $part['supplier_id'];
			        if (!isset($suppliers[$supplier_id])) {
			            $supplier = new LinemediaAutoSupplier($supplier_id);
			            $suppliers[$supplier_id] = $supplier->getArray();
			        }
			        
			        $parts[$group_id][$i]['supplier'] = $suppliers[$supplier_id];
	                
	                
	            }
	        }
	        
	        
	        $parts = LinemediaAutoPartsHelper::sorting($parts, 'price', 'asc');
	        
	        
	        $discount_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_DISCOUNT');
	        $supplier_iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');
	        					
	        
	        ?>
	        
	        <table class="lm-auto-price-debug">
	        
	        <?foreach ($parts as $group_id => $parts) {?>
	        	
	        	<tr>
	        		<th colspan="5">
		        		<?=LinemediaAutoPart::getAnalogGroupTitle(substr($group_id, -1))?>
	        		</th>
	        	</tr>
	        	
	        	<?foreach ($parts as $i => $part) {?>
	        		<tr>
	        			<td class="article"><?=$part['article']?></td>
	        			<td class="brand_title"><?=$part['brand_title']?></td>
	        			<td class="title"><?=$part['title']?></td>
	        			<td class="supplier_id"><a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=<?=$supplier_iblock_id?>&type=linemedia_auto&lang=<?=LANG?>&ID=<?=$part['supplier']['ID']?>"><?=$part['supplier']['NAME']?></a></td>
	        			<td class="price"><?=$part['price']?></td>
	        			<td class="debug">
	        			
	        				<?
	        					
	        					$discount_url = '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$discount_iblock_id.'&type=linemedia_auto&lang='.LANG.'&ID=';
	        					$supplier_url = '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$supplier_iblock_id.'&type=linemedia_auto&lang='.LANG.'&ID=';
	        					$debug = '<ol class="lm-auto-popup-prc-dbg">';
	        					foreach($part['price_debug'] AS $d)
	        					{
	        						
	        						$d = str_replace('[[DISCOUNT_URL]]', $discount_url, $d);
	        						$d = str_replace('[[SUPPLIER_URL]]', $supplier_url, $d);
	        						
	        						
	        						$debug .= '<li>'.$d.'</li>';
	        					}
	        					$debug .= '</ol>';
	        					$debug = htmlspecialchars($debug);
	        					
	        					$arDialogParams = array(
							      //'content_url' => $arUrl['URL'],
							      'title' => GetMessage('LM_AUTO_PRICE_DEBUG'),
							      'width' => 900,
							      'content' => $debug,
							   );
							   $href = '(new BX .CDialog('.CUtil::PhpToJsObject($arDialogParams).')).Show()';
	        				?>
	        				<a href="javascript:;" onclick="<?=$href?>" class="debug-open">?</a>
	        			</td>
	        		</tr>
	        	<?}?>
	        <?}?>
	        
	        </table>
	       
	        
	        
	        <?
	    	exit;	        
	    break;
	}
	
	
	
	die('OKK');
	exit;
}


$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.9.1/jquery.min.js");

$APPLICATION->SetTitle(GetMessage("LM_AUTO_PRICE_CHECK_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

?>


<?= BeginNote() ?>
	<b><?= GetMessage('LM_AUTO_PRICE_CHECK_MESSAGE') ?></b>
<?= EndNote() ?>


<form action="" method="POST" name="price_check_frm" id="price_check_frm">
	
	<div class="article">
		<input type="text" name="article" value="gdb1550" size="30" placeholder="<?=GetMessage("LM_AUTO_PRICE_ARTICLE")?>">
	</div>
	<div class="user">
		<?echo FindUserID("user", CUser::GetId(), CUser::GetFullName(), "price_check_frm", 4)?>
		<span><?=GetMessage("LM_AUTO_PRICE_USER")?></span>
	</div>
	<!--div class="date">
		<?=CalendarDate("date", htmlspecialcharsbx(date('d.m.Y')), "price_check_frm", 20)?>
		<span><?=GetMessage("LM_AUTO_PRICE_DATE")?></span>
	</div-->
	
	<div class="sbm">
		<input type="submit" id="price_check_sbm" value="<?=GetMessage('LM_AUTO_PRICE_CHECK')?>" />
		<!--input type="button" id="price_check_public" value="<?=GetMessage('LM_AUTO_PRICE_CHECK_PUBLIC')?>" /-->
	</div>
	
	<input type="hidden" name="brand_title" id="brand_title_inp" />
	<input type="hidden" name="extra" id="extra_inp" />
	
</form>

<div id="check-results"></div>


<script>
	
	
	function updatePriceForm()
	{
		$('#check-results').html('');
		$('#price_check_sbm').prop('disabled', true);
		
		$.ajax({
		  url: "/bitrix/admin/linemedia.auto_price_check.php?lang=<?=LANG?>&ajax=check_price",
		  type: 'POST',
		  cache:false,
		  data: $('#price_check_frm').serialize()
		}).done(function(response) { 
		  $('#check-results').html(response);
		  
		  $('#price_check_sbm').prop('disabled', false);
		  
		});
		return false;
	}
	
	
	$('#price_check_frm').submit(function(){
		return updatePriceForm();
	})
	
	// уточнение
	$('#check-results').on('click', 'a.lm-details', function(e){
		e.preventDefault();
		
		var brand_title = $(this).data('brand-title');
		var extra 		= $(this).data('extra');
		extra = JSON.stringify(extra);
		$('#brand_title_inp').val(brand_title);
		$('#extra_inp').val(extra);
		
		updatePriceForm();
	})
	
	$('#price_check_public').click(function(){
		var tpl = '<?=urldecode(LinemediaAutoUrlHelper::getPartUrl(array('article' => '#ARTICLE#', 'brand_title' => '#BRAND_TITLE#')))?>';
		
		var article 	= $('#price_check_frm input[name=article]').val();
		var brand_title = $('#price_check_frm input[name=brand_title]').val();
		var extra 		= $('#price_check_frm input[name=extra]').val();
		
		
		var url = tpl.replace('#ARTICLE#', article);
		url = tpl.replace('#BRAND_TITLE#', brand_title);
		url = tpl.replace('#EXTRA#', extra);
		
		alert(url);
	})
	
	
</script>

<?require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
