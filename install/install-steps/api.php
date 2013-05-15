<? IncludeModuleLangFile(__FILE__); ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="lm_auto_main" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.auto" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="parts-db" />
	
	<?= BeginNote() ?>
	<?=  GetMessage('LM_AUTO_MAIN_REGISTER_API_DESC') ?>
	<br /><br />
	<?=  GetMessage('LM_AUTO_MAIN_REGISTER_API_WARN') ?>
	<?=  EndNote() ?>
	
	
	<table class="list-table">
		<tr class="head">
			<td colspan="2"><?= GetMessage("LM_AUTO_MAIN_REGISTER_API_HEADER") ?></td>
		</tr>
		<tr>
			<td width="50%" align="right">
                <label for="SEND_BITRIX_VERSION"><?=GetMessage('LM_AUTO_MAIN_REGISTERING_API_SEND_BITRIX_VERSION')?>:</label>
            </td>
			<td width="50%">
			    <input type="checkbox" name="SEND_BITRIX_VERSION" id="SEND_BITRIX_VERSION" value="Y" checked="checked" />
			    (<?= $GLOBALS['MAIN_OPTIONS']['-']['main']['site_name'] . ' (' . LANG . ' / ' . SITE_CHARSET . ') v.' . SM_VERSION?>)
			</td>
		</tr>
		<tr>
			<td width="50%" align="right"><?= GetMessage('LM_AUTO_MAIN_REGISTERING_API_SEND_SITE_NAME') ?>:</td>
			<td>
				<input type="text" id="SEND_SITENAME" value="<?= $_SERVER['SERVER_NAME'] ?>" />
			</td>
		</tr>
	</table>
	
	<p>
        <input type="button" name="register_api" id="register_api" value="<?= GetMessage('LM_AUTO_MAIN_REGISTER_START') ?>" />
        <input type="submit" name="register_api_cancel" id="register_api_cancel" value="<?= GetMessage('LM_AUTO_MAIN_SKIP_STEP') ?>" />
    </p>
    
	<div class="progress progress-success" id="progress" style="display:none">
        <div class="bar" id="bar" style="width: 0%;"></div>
    </div>
</form>


<script type="text/javascript">

var interval, progressbar_percent = 0;

$(document).ready(function() {
    /*
     * show progressbar
     */
    $('#register_api').click(function() {
        $('#bar').css('width', '0%').html('0%');
        $('#progress').css('display', 'block');
        
        
        /*
         * start counting
         */
        interval = setInterval('setProgressBar();', 100);

        /*
         * get settings
         */
        var send_version  = $('#SEND_BITRIX_VERSION').attr('checked');
        var send_sitename = $('#SEND_SITENAME').val();
        
        /*
         * send register query
         */
        $.post(
            '<?= $APPLICATION->GetCurPage() ?>?AJAX=Y&id=linemedia.auto&action=register_api&<?=bitrix_sessid_get()?>', 
            {'send_version':send_version, 'send_sitename':send_sitename},
            function(response)
            {
                /*
                 * stop counting and fulfill progressbar
                 */
                clearInterval(interval);
                $('#bar').css('width', '100%').html('100%');
                
                
                /*
                 * redirect success or alert error
                 */
                if (response == 'ok') {
                    $('#lm_auto_main').submit();
                } else {
                    $('#progress').removeClass('progress-success').addClass('progress-danger');
                    alert(response);
                }
            }
        );
    });
    
});

function setProgressBar()
{
    progressbar_percent += 1;
    $('#bar').css('width', progressbar_percent + '%').html(progressbar_percent + '%');
    
    if (progressbar_percent >= 100) clearInterval(interval);
}

</script>
