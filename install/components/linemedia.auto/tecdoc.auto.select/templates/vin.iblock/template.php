<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<script type="text/javascript">
    var langs = {'change': '<?= GetMessage('CHANGE') ?>'};
</script>
<? if (is_array($arResult['brands']) && count($arResult['brands']) > 0) { ?>
    <tr id="tr_brand">
        <td class="field-name" valign="top"><?= GetMessage('MARK') ?>:</th>
        <td id="td_brand">
            <div class="dialog-content" style="display: none;"></div>
            <? if ($arParams['BRAND_ID'] > 0) {?>
                <strong>
                    <?= $arResult['brands'][$arParams['BRAND_ID']]['manuName'] ?>
                </strong>
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
                      'content_url' => $templateFolder . '/ajax_get_info.php?a=getBrand',
                   );
                   
                   $dialog = 'new BX.CDialog('.htmlspecialchars(CUtil::PhpToJsObject($arDialogParams)).')';
                ?>
                <small>(<a href="javascript: void(0);" onclick="javascript: getListBrands(this, <?= $dialog ?>); return false;"><?= GetMessage('CHANGE') ?></a>)</small>
            <? } else { ?>
                <? ob_start() ?>
                <table cellpadding="0" cellspacing="0" border="0" class="br_table" id="br_table">
                <?
                    $i = 1;
                    foreach ($arResult['brands'] as $aBrand) {
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
                                      'content_url' => $templateFolder . '/ajax_get_info.php?a=getModel&BrandID='.$aBrand['manuId'],
                                   );
                                   
                                   $dialog = 'new BX.CDialog('.htmlspecialchars(CUtil::PhpToJsObject($arDialogParams)).')';
                                ?>
                                <a rel="<?= $aBrand['manuId'] ?>" href="<?= $arDialogParams['content_url'] ?>" class="info" onclick="javascript: getListModels(this, <?= $dialog ?>); return false;">
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
                    $content = ob_get_contents();
                    ob_end_clean();
                ?>
                
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
                      'content'     => $content,
                   );
                   
                   $dialog = '(new BX.CDialog('.htmlspecialchars(CUtil::PhpToJsObject($arDialogParams)).'))';
                ?>
                <a href="javascript:void(0)" id="getBrands" onclick="javascript: getListBrands(this, <?= $dialog ?>);"><?= GetMessage('CHOOSE_AUTO_MARK') ?></a>
            <? } ?>
        </td>
    </tr>
<? } ?>

<tr id="tr_model"<? if ($arParams['MODEL_ID'] == 0 && $arParams['BRAND_ID'] == 0) { ?> class="auto-info-hide-tr"<? } ?>>
    <td class="field-name" valign="top"><?= GetMessage('MODEL') ?>:</th>
    <td id="td_model">
        <div class="dialog-content" style="display: none;"></div>
        <? if ($arParams['MODEL_ID'] > 0 && $arParams['BRAND_ID'] > 0) { ?>
            <strong>
                <?= $arResult['models'][$arParams['MODEL_ID']]['modelname'] ?>
            </strong> 
            <?  // Popup с информацией
                CUtil::InitJSCore(array('window', 'ajax'));
                
                $arDialogParams = array(
                  'title'       => GetMessage('CHOOSE_AUTO_MODEL'),
                  'width'       => 650,
                  'height'      => 600,
                  'min_width'   => 300,
                  'min_height'  => 400,
                  'resizable'   => false,
                  'draggable'   => true,
                  'content_url' => $templateFolder . '/ajax_get_info.php?a=getModel&BrandID='.$arParams['BRAND_ID'],
               );
               
               $dialog = 'new BX.CDialog('.htmlspecialchars(CUtil::PhpToJsObject($arDialogParams)).')';
            ?>
            <small>
                (<a rel="<?= $arParams['BRAND_ID'] ?>" href="<?= $arDialogParams['content_url'] ?>" class="info" onclick="javascript: getListModels(this, <?= $dialog ?>, true); return false;"><?= GetMessage('CHANGE') ?></a>)
            </small>
        <? } else { ?>
            <table cellpadding="0" cellspacing="0" border="0" class="br_table">
            <?
                $i = 1;
                foreach ($arResult['models'] as $aModel) {
                    if ($i % 3 - 1 === 0){?><tr><? }
                    ?>
                        <td><a href="javascript: void(0);" onclick="GetModification('<?=htmlspecialcharsEx($aModel["modelId"]);?>', $(this).html()); return false;"><?=htmlspecialcharsEx($aModel["modelname"]);?></a></td>
                    <?
                    if ($i % 3 === 0) { ?></tr><? }
                        $i++;
                }
                unset($aModel, $i);
            ?>
            </table>
        <? } ?>
    </td>
</tr>
<tr id="tr_modification"<?if($arParams['MODIFICATION_ID'] == 0 && $arParams['MODEL_ID'] == 0){?> class="auto-info-hide-tr"<?}?>>
    <td class="field-name" valign="top"><?= GetMessage('MODIFICATION') ?>:</th>
    <td id="td_modification">
        <div class="dialog-content" style="display: none;"></div>
        <? if ($arParams['MODIFICATION_ID'] > 0 && $arParams['MODEL_ID'] > 0) { ?>
        <strong>
            <?= $arResult['modifications'][$arParams['MODIFICATION_ID']]['carName'] ?>
        </strong>
        <?  // Popup с информацией
            CUtil::InitJSCore(array('window', 'ajax'));
            
            $arDialogParams = array(
              'title'       => GetMessage('CHOOSE_AUTO_MODIFICATION'),
              'width'       => 650,
              'height'      => 600,
              'min_width'   => 300,
              'min_height'  => 400,
              'resizable'   => false,
              'draggable'   => true,
              'content_url' => $templateFolder . '/ajax_get_info.php?a=GetModification&BrandID='.$arParams['BRAND_ID'].'&ModelID='.$arParams['MODEL_ID'],
           );
           
           $dialog = 'new BX.CDialog('.htmlspecialchars(CUtil::PhpToJsObject($arDialogParams)).')';
        ?>
        <small>
            (<a rel="<?= $arParams['MODIFICATION_ID'] ?>" href="<?= $arDialogParams['content_url'] ?>" class="info" onclick="javascript: getListModifications(this, <?= $dialog ?>, true); return false;"><?= GetMessage('CHANGE') ?></a>)
        </small>
      <? } else { ?>
        <table cellpadding="0" cellspacing="0" border="0" class="br_table">
          <?
          $i = 1;
          foreach ($arResult['modifications'] as $aModification) {
              if ($i % 3- 1  === 0) { ?><tr><? }
                  ?>
                  <td><a href="javascript: void(0);" onclick="SetModification(this, '<?=htmlspecialcharsEx($aModification["carId"]);?>', $(this).html()); return false;"><?=htmlspecialcharsEx($aModification["carName"]);?></a></td>
                  <?
              if ($i % 3 === 0) { ?></tr><? }
              $i++;
          }
          unset($aModification, $i);
          ?>
        </table>
      <? } ?>
    </td>
  </tr>
  <tr class="auto-info-hide-tr">
    <td colspan="2">
        <input type="hidden" name="brand" id="f_brand" value="<?= ($arResult['brands'][$arParams['BRAND_ID']]['manuName']) ? htmlspecialcharsEx($arResult['brands'][$arParams['BRAND_ID']]['manuName']) : ''; ?>" />
        <input type="hidden" name="model" id="f_model" value="<?= ($arResult['models'][$arParams['MODEL_ID']]['modelname']) ? htmlspecialcharsEx($arResult['models'][$arParams['MODEL_ID']]['modelname']) : ''; ?>" />
        <input type="hidden" name="modification" id="f_modification" value="<?= ($arResult['modifications'][$arParams['MODIFICATION_ID']]['carName']) ? htmlspecialcharsEx($arResult['modifications'][$arParams['MODIFICATION_ID']]['carName']) : ''; ?>" />
        <input type="hidden" name="brand_id" id="f_brand_id" value="<?= ($arParams['BRAND_ID']) ? htmlspecialcharsEx($arParams['BRAND_ID']) : ''; ?>" />
        <input type="hidden" name="model_id" id="f_model_id" value="<?= ($arParams['MODEL_ID']) ? htmlspecialcharsEx($arParams['MODEL_ID']) : ''; ?>" />
        <input type="hidden" name="modification_id" id="f_modification_id" value="<?= ($arParams['MODIFICATION_ID']) ? htmlspecialcharsEx($arParams['MODIFICATION_ID']) : ''; ?>" />  
    </td>
  </tr>