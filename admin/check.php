<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$modulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}


/*
 * Просмотр логов
 */
if ($_GET['ajax'] == 'show_log') {
	$filename = str_replace('..', '', strval($_GET['file']));
	$filename = $_SERVER['DOCUMENT_ROOT'] . $filename . '.log';
	
	if (!file_exists($filename))
		die('No log found<script>setTimeout("window.location=window.location;", 2000);</script>');
	
	
	$lines = 100;
	$buffer = 4096;
	
	$f = fopen($filename, "rb");
	fseek($f, -1, SEEK_END);
	if (fread($f, 1) != "\n") $lines -= 1;
	$output = '';
	$chunk = '';
	while (ftell($f) > 0 && $lines >= 0) {
		$seek = min(ftell($f), $buffer);
		fseek($f, -$seek, SEEK_CUR);
		$output = ($chunk = fread($f, $seek)).$output;
		fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
		$lines -= substr_count($chunk, "\n");
	}
	while ($lines++ < 0) {
		$output = substr($output, strpos($output, "\n") + 1);
	}
	fclose($f);
	
	
	$output = explode("\n", $output);
	$output = array_reverse($output);
	$output = join('<br>', $output);
	
	$output .= '<script>setTimeout("window.location=window.location;", 2000);</script>';
	
	die($output);
}


/*
 * Чистка логов
 */
if ($_GET['ajax'] == 'clear_log') {
	$filename = str_replace('..', '', strval($_GET['file']));
	$filename = $_SERVER['DOCUMENT_ROOT'] . $filename . '.log';
	@unlink($filename);
	exit;
}



            
/*
 * Получение названия модуля по его ID
 */
function getModuleTitle($module_id)
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $module_id . '/install/index.php';
	$classname = str_replace('.', '_', $module_id);
	if(!class_exists($classname))
		return $module_id;
	$instance = new $classname;
	return $instance->MODULE_NAME;
}


function lm_auto_main_conv_size($size) {
    $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
}


/*
 * Создаём событие Проверки
 */
$check = array();
$events = GetModuleEvents("linemedia.auto", "OnRequirementsListGet");
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$check));
}


/*
 * Создаём событие получения списка логов системы
 */
$logs = array();
$events = GetModuleEvents("linemedia.auto", "OnLogsListGet");
while ($arEvent = $events->Fetch()) {
    ExecuteModuleEventEx($arEvent, array(&$logs));
}



/* 
* TableStatus
*/
$sql = "SHOW TABLE STATUS WHERE Name = 'b_lm_products'";
$db = new LinemediaAutoDatabase;
$res = $db->Query($sql);
$table_data = $res->Fetch();

$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");

$APPLICATION->SetTitle(GetMessage("LM_AUTO_CHECK_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

?>


<?= BeginNote() ?>
	<b><?= GetMessage('LM_AUTO_CHECK_MESSAGE') ?></b>
<?= EndNote() ?>


<table class="lm-auto-check lm-auto-check-main">
<? foreach ($check as $module_id => $checks) { ?>
	<thead>
		<tr class="module">
			<th colspan="3"><?=GetMessage('LM_AUTO_CHECK_MODULE')?> <span><?=getModuleTitle($module_id)?></span></th>
		</tr>
		<tr class="titles">
			<th><?=GetMessage('LM_AUTO_CHECK_CHECK')?></th>
			<th><?=GetMessage('LM_AUTO_CHECK_REQUIREMENTS')?></th>
			<th><?=GetMessage('LM_AUTO_CHECK_STATUS')?></th>
		</tr>
	</thead>
	<tbody>
		<?foreach($checks AS $check){?>
			<tr class="check check-<?=$check['status']?'ok':'fail'?>">
				<td><?=$check['title']?></td>
				<td>
					<?if(!$check['status']){?>
					<?=$check['requirements']?>
					<?}?>
				</td>
				<td><?=$check['status'] ? GetMessage('STATUS_CHECK_OK') : GetMessage('STATUS_CHECK_FAIL')?></td>
			</tr>
		<?}?>
	</tbody>
<? } ?>
</table>


<table class="lm-auto-check lm-auto-check-products">
	<thead>
		<tr>
			<th colspan="2"><?=GetMessage('LM_AUTO_PRODUCTS_TABLE')?></th>
		</tr>
	</thead>
	<tbody>
		<tr<?=$table_data['Engine']=='InnoDB' ? '' : ' class="check-fail"'?>>
			<td><?=GetMessage('LM_AUTO_PRODUCTS_TABLE_ENGINE')?></td>
			<td><?=$table_data['Engine']?></td>
		</tr>
		<tr>
			<td><?=GetMessage('LM_AUTO_PRODUCTS_TABLE_ROWS')?></td>
			<td>&asymp;<?=$table_data['Rows']?></td>
		</tr>
		<tr>
			<td><?=GetMessage('LM_AUTO_PRODUCTS_TABLE_SIZE')?></td>
			<td><?=lm_auto_main_conv_size($table_data['Data_length'])?></td>
		</tr>
		<tr>
			<td><?=GetMessage('LM_AUTO_PRODUCTS_TABLE_INDEX_SIZE')?></td>
			<td><?=lm_auto_main_conv_size($table_data['Index_length'])?></td>
		</tr>
		<tr<?=(in_array($table_data['Collation'], array('utf8_general_ci', 'utf8_unicode_ci')) ? '' : ' class="check-fail"')?>>
			<td><?=GetMessage('LM_AUTO_PRODUCTS_TABLE_COLLATION')?></td>
			<td><?=$table_data['Collation']?></td>
		</tr>
	</tbody>
</table>


<table class="lm-auto-check lm-auto-check-logs">
	<thead>
		<tr class="module">
			<th colspan="3"><?=GetMessage('LM_AUTO_CHECK_MODULE_LOGS')?></th>
		</tr>
	</thead>
	
	<?foreach($logs AS $module_id => $module_logs){
		$title = getModuleTitle($module_id);
		$module_id = str_replace('.', '-', $module_id);
	?>	
		<thead>
			<tr>
				<th colspan="3"><?=$title?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="log-selector">
				<td>
					<select id="log-view-<?=$module_id?>">
						<?foreach($module_logs AS $log){?>
							<option value="<?=$log['filename']?>"><?=$log['title']?></option>
						<?}?>
					</select>
				</td>
				<td><input type="button" value="<?=GetMessage('LM_AUTO_CHECK_MODULE_LOGS_SHOW')?>" class="log-view-btn" data-module="<?=$module_id?>" /></td>
				<td><input type="button" value="<?=GetMessage('LM_AUTO_CHECK_MODULE_LOGS_CLEAR')?>" class="log-clear-btn" data-module="<?=$module_id?>" /></td>
			</tr>
		</tbody>
	<?}?>
</table>


<script>
	$('.log-view-btn').click(function(){
		var module = $(this).data('module');
		var file = $('#log-view-' + module).val();
		var popup = window.open("/bitrix/admin/linemedia.auto_check.php?ajax=show_log&file=" + file, "log", "width=1000,height=500");
		popup.focus();
	})
	
	$('.log-clear-btn').click(function(){
		var module = $(this).data('module');
		var file = $('#log-view-' + module).val();
		$.get("/bitrix/admin/linemedia.auto_check.php?ajax=clear_log&file=" + file);
		alert('<?=GetMessage('LM_AUTO_CHECK_MODULE_LOG_CLEARED')?>');
	})
</script>

<?require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
