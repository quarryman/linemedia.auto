<?IncludeModuleLangFile(__FILE__);
global $DBHost, $DBLogin, $DBName, $DBType;
?>

<?
if (!isset($_REQUEST['register_api_cancel'])) {
    echo CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_MAIN_API_STEP_SUCCESS"), 'TYPE' => 'OK'));
}
?>

<?= BeginNote() ?>
<?= GetMessage('LM_AUTO_MAIN_PARTS_DATABASE_DESC') ?>
<?= EndNote() ?>


<form action="<?=$APPLICATION->GetCurPage()?>" id="parts-db-create-frm" class="well" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.auto" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="demo-folder" />
	
	<table class="list-table">
		<tr class="head">
			<td colspan="2">
	        <?= GetMessage('LM_AUTO_MAIN_GROUP_SERVER') ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
			    <label style="float:left">
			        <?= GetMessage('LM_AUTO_MAIN_USE_BITRIX_Y') ?>:
    			    <input type="radio" name="DATABASE_USE_BITRIX" value="Y" checked="checked" />
			    </label>
			    <label style="float:right">
			        <?= GetMessage('LM_AUTO_MAIN_USE_BITRIX_N') ?>:
    			    <input type="radio" name="DATABASE_USE_BITRIX" value="N" />
			    </label>
			    
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_ADD_DEMO_DATA') ?>:</td>
			<td>
			    <input type="checkbox" name="DATABASE_ADD_DEMO_DATA" value="Y" checked />
			</td>
		</tr>
	</table>

    
	<table class="list-table" id="separate-db" style="display:none">
		<tr class="head">
			<td colspan="2">
	        <?= GetMessage('LM_AUTO_MAIN_GROUP_USE_BITRIX_DB') ?>
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_HOST') ?>:</td>
			<td>
			    <input type="text" name="DATABASE_HOST" value="<?=$DBHost?>" />
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_PORT') ?>:</td>
			<td>
			    <input type="text" name="DATABASE_PORT" value="3306" />
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_USER') ?>:</td>
			<td>
			    <input type="text" name="DATABASE_USER" value="<?=$DBLogin?>" />
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_PASSWORD') ?>:</td>
			<td>
			    <input type="password" name="DATABASE_PASSWORD" value="" />
			</td>
		</tr>
		
		<tr>
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_NAME') ?>:</td>
			<td>
			    <input type="text" name="DATABASE_NAME" value="autoparts" />
			</td>
		</tr>
		<tr class="head">
			<td colspan="2">
			<?= GetMessage('LM_AUTO_MAIN_GROUP_AUTO_ADD') ?>
			</td>
		</tr>
		
		<tr>
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_AUTO_ADD_Y') ?>:</td>
			<td>
			    <input type="radio" name="DATABASE_AUTO_ADD" value="Y" checked />
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_AUTO_ADD_N') ?>:</td>
			<td>
			    <input type="radio" name="DATABASE_AUTO_ADD" value="N" />
			</td>
		</tr>
		<tr class="head db-auto-create">
			<td colspan="2"><?= GetMessage('LM_AUTO_MAIN_GROUP_ROOT_USER') ?></td>
		</tr>
		<tr class="db-auto-create">
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_ROOT_USER') ?>:</td>
			<td>
			    <input type="text" name="DATABASE_ROOT_USER" value="root" />
			</td>
		</tr>
		<tr class="db-auto-create">
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_ROOT_PASSWORD') ?>:</td>
			<td>
			    <input type="password" name="DATABASE_ROOT_PASSWORD" value="" />
			</td>
		</tr>
		
		<tr class="head db-manual-create">
			<td colspan="2"><?= GetMessage('LM_AUTO_MAIN_DEMO_DATABASE_MANUAL_ADD') ?></td>
		</tr>
		<tr class="db-manual-create" style="display:none">
			<td colspan="2">
            <textarea cols="120" rows="14">CREATE DATABASE IF NOT EXISTS `autoparts` CHARACTER SET utf8 COLLATE utf8_unicode_ci;

GRANT ALL PRIVILEGES ON `autoparts` . * TO '<?=$DBLogin?>'@'%' WITH GRANT OPTION ;

FLUSH PRIVILEGES;

USE autoparts;

<?=htmlspecialchars(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.auto/install/db/' . $DBType . '/parts-structure.sql'))?>
            </textarea>
			</td>
		</tr>
		
		
		
	</table>
	
    
    <p>
        <input type="button" id="parts-db-create-btn" value="<?= GetMessage('LM_AUTO_MAIN_PARTS_DATABASE_CREATE') ?>" />
        <input type="submit" id="parts-db-ignore-btn" name="parts_db_cancel" value="<?= GetMessage('LM_AUTO_MAIN_SKIP_STEP') ?>" />
    </p>
</form>



<script type="text/javascript">
    var request_in_progress = false;
    $(document).ready(function() {
        if (request_in_progress) {
            return;
        }
        request_in_progress = true;
        
        $('#parts-db-create-btn').click(function() {
            $('div.parts-db-error').remove();
            $('#parts-db-create-btn').addClass('disabled');
            
            $.post(
                '<?= $APPLICATION->GetCurPage() ?>?AJAX=Y&id=linemedia.auto&action=create_parts_db&<?=bitrix_sessid_get()?>', 
                $('#parts-db-create-frm').serialize(),
                function(response) {
                    if(response == 'ok') {
                        $('#parts-db-create-frm').submit();
                    } else {
                        $('#parts-db-create-frm').after('<div class="alert alert-error parts-db-error">' + response + '</div>');
                    }
                    
                    request_in_progress = false;
                    $('#parts-db-create-btn').removeClass('disabled');
                }
            );
            return false;
        });
        
        $('input[name=DATABASE_AUTO_ADD]').click(function() {
            var auto = $(this).val() == 'Y';
            if (auto) {
                $('.db-auto-create').show();
                $('.db-manual-create').hide();
            } else {
                $('.db-auto-create').hide();
                $('.db-manual-create').show();
            }
        });
        
        $('input[name=DATABASE_USE_BITRIX]').click(function() {
            var use_bitrix_db = $(this).val() == 'Y';
            if(use_bitrix_db) {
                $('#separate-db').hide();
            } else {
                $('#separate-db').show();
            }
        });
        
    });
</script>
