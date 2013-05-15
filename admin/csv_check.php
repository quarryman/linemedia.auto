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
if ($_GET['ajax'] == 'check_csv') {
	$filename = str_replace('..', '', strval($_POST['CSV_FILE']));
	$filename = $_SERVER['DOCUMENT_ROOT'] . $filename;
	
	$checker = new LinemediaAutoCSVChecker();
	try {
		$checker->checkFile($filename);
		$error = false;
	} catch (Exception $e) {
		$error = $e->GetMessage();
	}
	
	
	if (!$error) {
		echo CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_CSV_CHECK_OK"), 'TYPE' => 'OK'));
	} else {
		echo CAdminMessage::ShowMessage(array('MESSAGE' => $error, 'TYPE' => 'ERROR'));
	}

	$parsed_data = $checker->getParsedData();
		
	echo '<b>'.GetMessage("LM_AUTO_CSV_STRING") . '</b>: ' . htmlspecialchars($parsed_data['string']) . '<br><br>';
	echo '<b>'.GetMessage("LM_AUTO_CSV_ARRAY") . '</b>: <pre>' . htmlspecialchars(print_r($parsed_data['array'], 1)) . '</pre>';

	exit();
}


$APPLICATION->AddHeadScript("http://yandex.st/jquery/1.8.0/jquery.min.js");

$APPLICATION->SetTitle(GetMessage("LM_AUTO_CSV_CHECK_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

?>


<?= BeginNote() ?>
	<b><?= GetMessage('LM_AUTO_CSV_CHECK_MESSAGE') ?></b>
<?= EndNote() ?>


<form action="" method="POST" name="csv_check_frm" id="csv_check_frm">
	<input type="text" name="CSV_FILE" value="<?echo htmlspecialchars($CSV_FILE)?>" size="30">
	<input type="button" value="<?echo GetMessage("SELECT_CSV_FILE") ?>" OnClick="BtnClick()">
	<?
	CAdminFileDialog::ShowScript
	(
		Array(
			"event" => "BtnClick",
			"arResultDest" => array("FORM_NAME" => "csv_check_frm", "FORM_ELEMENT_NAME" => "CSV_FILE"),
			"arPath" => array("SITE" => SITE_ID, "PATH" =>"/".COption::GetOptionString("main", "upload_dir", "upload") . '/linemedia.auto/pricelists/'),
			"select" => 'F',// F - file only, D - folder only
			"operation" => 'O',// O - open, S - save
			"showUploadTab" => true,
			"showAddToMenuTab" => false,
			"fileFilter" => 'csv',
			"allowAllFiles" => true,
			"SaveConfig" => true,
		)
	);
	?>
	<input type="submit" value="<?=GetMessage('SUBMIT_CSV_CHECK')?>" />
</form>
<div id="check-results"></div>


<script>
	$('#csv_check_frm').submit(function(){
		$.ajax({
		  url: "/bitrix/admin/linemedia.auto_csv_check.php?lang=<?=LANG?>&ajax=check_csv",
		  type: 'POST',
		  data: $(this).serialize()
		}).done(function(response) { 
		  $('#check-results').html(response);
		});
		return false;
	})
	
</script>

<?require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
