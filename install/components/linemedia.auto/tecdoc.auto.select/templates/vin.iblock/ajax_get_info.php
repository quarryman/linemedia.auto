<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", true);

include (dirname(__FILE__).'/lang/'.LANGUAGE_ID.'/ajax_get_info.php');

$sAction    = (isset($_GET['a']) && strlen(trim($_GET['a'])) > 0) ? trim($_GET['a']) : false;
$iBrandID   = (isset($_GET['BrandID']) && intval($_GET['BrandID']) > 0) ? intval($_GET['BrandID']) : false;
$iModelID   = (isset($_GET['ModelID']) && intval($_GET['ModelID']) > 0) ? intval($_GET['ModelID']) : false;

if (CModule::IncludeModule('linemedia.auto')) {
    $api = new LinemediaAutoApiDriver();

    if ($sAction !== false) {
        switch ($sAction) {
            case 'getBrand':               
                $response = $api->query('getBrands2', $data = array());
                if (is_array($response) && $response['status'] === 'ok' && ($response['data']) > 0 ) {
                    $aBrandRes = $response['data']['brands'];
                }
                if (is_array($aBrandRes) && count($aBrandRes) > 0) { ?>
                <table cellpadding="0" cellspacing="0" border="0" class="br_table" id="br_table">
                    <?
                    $i = 1;
                    foreach ($aBrandRes as $aBrand) {
                        if ($aModel['hidden'] == 'Y') continue;
                        if (!isset($arResult['ACCESS_LIST_DISABLE'][$aBrand['manuId']])) {
                            if ($i % 4 - 1 === 0) { ?>
                                <tr>
                            <? } ?>
                            <td>
                                <?  // Popup с информацией
                                    CUtil::InitJSCore(array('window', 'ajax'));
                                    
                                    $arDialogParams = array(
                                      'title'       => GetMessage('CHOOSE_AUTO_MARK'),
                                      'width'       => 650,
                                      'height'      => 600,
                                      'min_width'   => 300,
                                      'min_height'  => 400,
                                      'resizable'   => false,
                                      'draggable'   => true,
                                      'content_url' => '/bitrix/components/linemedia.auto/tecdoc.auto.select/templates/vin.iblock/ajax_get_info.php?a=getModel&BrandID='.$aBrand['manuId'],
                                   );
                                   
                                   $dialog = 'new BX.CDialog('.htmlspecialchars(CUtil::PhpToJsObject($arDialogParams)).')';
                                ?>
                                <a rel="<?= $aBrand['manuId'] ?>" href="javascript: void(0);" class="info" onmousedown="javascript: void(0); getListModels(this, <?= $dialog ?>); return false;">
                                    <?= $aBrand['manuName'] ?>
                                </a>
                            </td>
                        <?
                            if ($i % 4 === 0) { ?>
                                </tr>
                            <? }
                            $i++;
                        }
                    }
                    unset($aBrand, $i);
                ?>
                </table>
                <?
                    } else {
                        echo GetMessage('NO_MARKS');
                    }
                break;
                
                case 'getModel':
                    if ($iBrandID !== false) {
                        
                        $response = $api->query('getVehicleModels2', $data = array('brand_id' => $iBrandID));
                        if (is_array($response) && $response['status'] === 'ok' && ($response['data']) > 0 ) {
                            $aModels = $response['data']['models'];
                        }

                        if (is_array($aModels) && count($aModels) > 0) { ?>
                            <div class="select_popup">
                                <table cellpadding="0" cellspacing="0" border="0" class="tecdoc model_select">
                                    <thead>
                                        <tr>
                                            <th><?= GetMessage('MODEL') ?></th>
                                            <th><?= GetMessage('YEAR_BEGIN') ?></th>
                                            <th><?= GetMessage('YEAR_END') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <? foreach ($aModels as $aModel) { ?>
                                        <? if ($aModel['hidden'] == 'Y') continue; ?>
                                        <tr>
			                                 <td>
			                                     <?  // Popup с информацией
                                                    CUtil::InitJSCore(array('window', 'ajax'));
                                                    
                                                    $arDialogParams = array(
                                                      'title'       => GetMessage('CHOOSE_AUTO_MODOFICATION'),
                                                      'width'       => 650,
                                                      'height'      => 600,
                                                      'min_width'   => 300,
                                                      'min_height'  => 400,
                                                      'resizable'   => false,
                                                      'draggable'   => true,
                                                      'content_url' => '/bitrix/components/linemedia.auto/tecdoc.auto.select/templates/vin.iblock/ajax_get_info.php?a=GetModification&BrandID='.$iBrandID.'&ModelID='.$aModel['modelId'],
                                                   );
                                                   
                                                   $dialog = '(new BX.CDialog('.htmlspecialchars(CUtil::PhpToJsObject($arDialogParams)).'))';
                                                ?>
                                                <a rel="<?= $aModel['modelId'] ?>" class="info" href="javascript: void(0);" onmousedown="javascript: getListModifications(this, <?= $dialog ?>); return false;">
                                                    <?= $aModel['modelname'] ?>
                                                </a>
                                            </td>
			<td><?= substr($aModel['yearOfConstrFrom'], 4, 2) . '.' .substr($aModel['yearOfConstrFrom'], 0, 4); ?></td>
			<td>
				<?
				$end = substr($aModel['yearOfConstrTo'], 4, 2) . '.' .substr($aModel['yearOfConstrTo'], 0, 4);
				if ($end == '.') {
					echo GetMessage('AVAILABLE');
				} else {
					echo $end;
				}
				?>
			</td>
		</tr> 
              
	<?}
      unset($aModel, $i);
      ?>
	</tbody>
    </table>
	</div>
<?
                        } else {
                            echo GetMessage('NO_MODELS');
                        }
                    }
                break;
                case 'GetModification':
                    if ($iBrandID !== false && $iModelID !== false) {
                        $response = $api->query('getModelVariantsWithCarInfo2', $data = array('brand_id' => $iBrandID, 'model_id' => $iModelID));
                        if (is_array($response) && $response['status'] === 'ok' && ($response['data']) > 0 ) {
                            $aModifications = $response['data']['modifications'];
                        }
                        
                        if (is_array($aModifications) && count($aModifications) > 0){
?>


<div class="select_popup">
<table id="modification" class="tecdoc model_select" cellpadding="0" cellspacing="0">
<thead>
	<tr>
		<th><?= GetMessage('TYPE') ?></th>
		<th><?= GetMessage('YEAR') ?></th>
		<th><?= GetMessage('KILOWATTS') ?></th>
		<th><?= GetMessage('HORSEPOWER') ?></th>
		<th><?= GetMessage('VOLUME') ?></th>
		<th><?= GetMessage('FORM_ASSEMBLING') ?></th>
	</tr>
</thead>
<tbody>
      <? foreach ($aModifications as $aModification) { ?>
        <? if ($aModification['hidden'] == 'Y') continue; ?>
		<tr>
			<td><a href="javascript: void(0);" onmousedown="SetModification('<?= htmlspecialcharsEx($aModification["carId"]) ?>', $(this).html());"><?= $aModification['carName'] ?></a></td>
			<td><?= $aModification['begin'] ?>-<?= $aModification['end'] ?></td>
			<td><?= $aModification['powerKW'] ?></td>
			<td><?= $aModification['powerHP'] ?></td>
			<td><?= $aModification['ccm'] ?></td>
			<td><?= $aModification['impulsionType'] ?></td>
		</tr>

		<? }
      unset($aModification, $i);
      ?>

</tbody>
</table>
</div>

<?
                        } else {
                            echo GetMessage('NO_MODIFICATIONS');
                        }
                    }
                break;
                case 'GetModificationInfo':
                    $iModificationID = (isset($_GET['ModificationID']) && intval($_GET['ModificationID']) > 0) ? intval($_GET['ModificationID']) : false;
                    if($iModificationID !== false && $iBrandID !== false && $iModelID !== false){
                        $response = $api->query('getModelVariantsWithInfo', $data = array('brand_id' => $iBrandID, 'model_id' => $iModelID));
                        if (is_array($response) && $response['status'] === 'ok' && ($response['data']) > 0 ) {
                            $aModifications = $response['data'];
                        }
                        
                        if(is_array($aModifications) && count($aModifications) > 0){
                            foreach ($aModifications as $aModification){
                                if($aModification["carId"] == $iModificationID){
                                    $aModification['begin_month'] = (strpos($aModification['begin'], '.'))?substr($aModification['begin'], 0, 2):false;
                                    $aModification['begin_year'] = (strpos($aModification['begin'], '.'))?substr($aModification['begin'], 3, 7):false;
                                    $aModification['end_month'] = (strpos($aModification['end'], '.'))?substr($aModification['end'], 0, 2):false;
                                    $aModification['end_year'] = (strpos($aModification['end'], '.'))?substr($aModification['end'], 3, 7):false;
                                    echo json_encode($aModification);
                                    break;
                                }
                            }
                        }
                    }
                break;
            }
        } else {
            echo ShowError(GetMessage('ERROR_NO_ACTION'));
        }
    } else {
        echo ShowError(GetMessage('ERROR_NO_MODULE_INSTALL'));
    }
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");