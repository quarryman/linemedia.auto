<? IncludeModuleLangFile(__FILE__); ?>

<?
if (!isset($_REQUEST['parts_db_cancel'])) {
    echo CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_MAIN_PARTS_DB_STEP_SUCCESS"), 'TYPE' => 'OK'));
}
?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="lm_auto_main" class="well" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.auto" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="iblocks" />
	
	<?
	echo BeginNote();
	echo GetMessage('LM_AUTO_MAIN_DEMO_FOLDER_DESC');
	
	echo '<br/>';
	
	$images = array('analogi.jpg', 'articuls.jpg', 'my_orders.jpg');
	
    CUtil::InitJSCore(array('window', 'ajax')); 

	foreach ($images as $image) {

        $arDialogParams = array(
          'title' => GetMessage('ADDITIONAL_INFO'),
          'width' => 650,
          'height' => 600,
          'min_width' => 300,
          'min_height' => 400,
          'resizable' => true,
          'draggable' => true,
          'content' => '<img src=\'/bitrix/modules/linemedia.auto/interface/img/' . $image . '\' />',
       );
       
       $href = '(new BX.CDialog('.CUtil::PhpToJsObject($arDialogParams).')).Show()';
       echo '<a href="#" onclick="' . $href . '" ><img src="/bitrix/modules/linemedia.auto/interface/img/' . $image . '" width="80" alt="" /></a>';
   }
	
	echo EndNote();
	?>
	
	
	<table class="list-table">
		<tr class="head">
			<td colspan="2"><?=GetMessage('LM_AUTO_MAIN_DEMO_FOLDER_INSTALL_HEADER')?></td>
		</tr>
		<tr>
			<td width="50%" align="right"><?=GetMessage('LM_AUTO_MAIN_DEMO_FOLDER_INSTALL')?>:</td>
			<td>
			    <input type="checkbox" id="DEMO_FOLDER_INSTALL" name="DEMO_FOLDER_INSTALL" value="Y" checked="checked" />
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?=GetMessage('LM_AUTO_MAIN_DEMO_FOLDER_PATH')?>:</td>
			<td>
			    <input class="input-large" type="text" id="DEMO_FOLDER_PATH" name="DEMO_FOLDER_PATH" value="/auto/" />
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?=GetMessage('LM_AUTO_MAIN_DEMO_FOLDER_REWRITE')?>:</td>
			<td>
			    <input type="checkbox" id="DEMO_FOLDER_REWRITE" name="DEMO_FOLDER_REWRITE" value="Y" />
			</td>
		</tr>
	</table>
	
    <p>
        <input type="submit" value="<?=GetMessage('LM_AUTO_MAIN_INSTALL_FOLDER')?>" />
    </p>
</form>



<script type="text/javascript">
    $(document).ready(function() {
        $('#DEMO_FOLDER_INSTALL').click(function() {
            $('#DEMO_FOLDER_PATH').attr('disabled',    $('#DEMO_FOLDER_INSTALL').attr('checked') != 'checked');
            $('#DEMO_FOLDER_REWRITE').attr('disabled', $('#DEMO_FOLDER_INSTALL').attr('checked') != 'checked');
        });
    });
</script>
