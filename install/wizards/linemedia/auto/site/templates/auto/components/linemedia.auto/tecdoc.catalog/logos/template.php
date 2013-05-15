<? $bIsAdmin = ($USER->IsAdmin() && $_SESSION['SESS_INCLUDE_AREAS']) ?>

<?
    $APPLICATION->SetAdditionalCSS('http://yandex.st/jquery/cookie/1.0/jquery.cookie.min.js');
    $APPLICATION->SetAdditionalCSS('http://yandex.st/jquery-ui/1.8.23/themes/smoothness/jquery.ui.all.min.css');

    $APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.0/jquery.min.js');
    $APPLICATION->AddHeadScript('http://yandex.st/jquery/cookie/1.0/jquery.cookie.min.js');
    $APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.treeview.js');  
?>

<? $is_brand_car_name = (isset($arParams['SHOW_CAR_BRANDS_IN_URI']) && $arParams['SHOW_CAR_BRANDS_IN_URI'] == 'Y') ?>

<? if (!empty($arResult['ERRORS'])) { ?>
    <? foreach ($arResult['ERRORS'] as $error) { ?>
        <? ShowError($error) ?>
    <? } ?>
<? } ?> 

<script type="text/javascript">
    $(document).ready(function() {
        <? if ($bIsAdmin) { ?>
            $('.f_access').change(function() {
                var CheckItem = this;
                $(CheckItem).attr("disabled", "disabled");
                if (CheckItem.checked) {
                    // показываем
                    $.get('/bitrix/components/linemedia.auto/tecdoc.catalog/ajax.php?a=enable&s=' + encodeURIComponent($(CheckItem).attr('name')) + '&id=' + encodeURIComponent($(CheckItem).val()), function(data){$(CheckItem).removeAttr('disabled');});
                } else {
                    // скрываем
                    $.get('/bitrix/components/linemedia.auto/tecdoc.catalog/ajax.php?a=disable&s=' + encodeURIComponent($(CheckItem).attr('name')) + '&id=' + encodeURIComponent($(CheckItem).val()), function(data){$(CheckItem).removeAttr('disabled');});
                }
            });
            
            <? if (is_array($arResult['ACCESS_LIST_DISABLE']) && count($arResult['ACCESS_LIST_DISABLE']) > 0) { ?>
                var SAllStatus = 0;
            <? } else { ?>
                var SAllStatus = 1;
            <? } ?>
            
            $('#SelectAll').click (function () {
                $(".tecdoc INPUT[type='checkbox']").each(function(){
                    var CheckItem = this;
                    if (SAllStatus == 0) {
                        $(CheckItem).attr('checked', true);
                        $(CheckItem).attr("disabled","disabled");
                        $.get('/bitrix/components/linemedia.auto/tecdoc.catalog/ajax.php?a=enable&s=' + encodeURIComponent($(CheckItem).attr('name')) + '&id=' + encodeURIComponent($(CheckItem).val()), function(data){$(CheckItem).removeAttr('disabled');});
                    } else {
                        $(CheckItem).attr('checked', false);
                        $(CheckItem).attr("disabled","disabled");
                        $.get('/bitrix/components/linemedia.auto/tecdoc.catalog/ajax.php?a=disable&s=' + encodeURIComponent($(CheckItem).attr('name')) + '&id=' + encodeURIComponent($(CheckItem).val()), function(data){$(CheckItem).removeAttr('disabled');});
                    }
                });
                if (SAllStatus == 0) {
                    SAllStatus = 1;
                } else {
                    SAllStatus = 0;
                }
            });
            
            $('#ApplyForAllAuto').click (function () {
                $('#ApplyForAllAuto').attr("disabled","disabled");
                var aDisableItems = Array();
                $(".tecdoc INPUT[type='checkbox']").each(function(){
                    if(!this.checked){
                        aDisableItems.push($(this).val());
                    }
                });
                $.post("/bitrix/components/linemedia.auto/tecdoc.catalog/ajax.php?s=SET_GROUP_FOR_ALL_AUTO", { id: aDisableItems},
                   function(data) {
                     $('#ApplyForAllAuto').removeAttr('disabled');
                   });
                
                console.log(aDisableItems);
            });
        <? } ?>
    });
    <? if ($bIsAdmin === true) { ?>
        function jqCheckAll(flag) {
            if (flag == 0) {
                $(".tecdoc INPUT[type='checkbox']").attr('checked', false);
            } else {
                $(".tecdoc INPUT[type='checkbox']").attr('checked', true);
            }
        }
    <? } ?>
</script>

<? $additional_url = (isset($arResult['TECDOC_NEW_URL']) && $arResult['TECDOC_NEW_URL'] === 'Y') ? strtolower($arResult['model_group']) . '/' : '';?>
<? $brand = ($is_brand_car_name) ? strtolower($arResult['brand_name']) : $arResult['brand_id']; ?>

<? if (isset($arResult['BRANDS'])): ?>
    <div class="well only_logos_box">
        <? foreach ($arResult['BRANDS'] as $brand) { ?>
            <a class="only_logo" title="<?= $brand['manuName'] ?>" href="/auto/tecdoc/<?= $brand['manuId'] ?>/">
                <img width="50" height="50" border="0" alt="Запчасти для <?= $brand['manuName'] ?>" src="<?= $brand['LOGO'] ?>">
            </a>
        <? } ?>
    </div>
<? elseif (isset($arResult['MODEL_GROUPS'])): ?>

    <table cellpadding="0" cellspacing="0" class="tecdoc model_select">
        <thead>
            <tr>
                <th><?= GetMessage('HEAD_MODEL') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($arResult['MODEL_GROUPS'] as $sKeyModel => $value) { ?>
                <? if ($bIsAdmin) { ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="f_access" value="<?=$arResult['brand_id']?>;<?=$sKeyModel;?>" name="MODEL_GROUPS" <?=((!isset($arResult['ACCESS_LIST_DISABLE'][$arResult['brand_id'].';'.$sKeyModel]))?' checked="checked"':'');?> />
                           <a href="<?=$arParams['SEF_FOLDER']?><?=$brand?>/<?=strtolower($sKeyModel);?>/"><?=$value;?></a>
                        </td>
                    </tr>
                <? } elseif (!$bIsAdmin && !isset($arResult['ACCESS_LIST_DISABLE'][$arResult['brand_id'].';'.$sKeyModel])) { ?>
                    <tr>
                        <td>
                            <a href="<?= $arParams['SEF_FOLDER'] ?><?= $brand ?>/<?= strtolower($sKeyModel) ?>/"><?= $value ?></a>
                        </td>
                    </tr>
                <? } ?>
            <? } ?>
            <? unset($sKeyModel, $value) ?>
        </tbody>
    </table>
    <? if ($bIsAdmin) { ?>
        <br />
        <input type="button" value="<?= GetMessage('CHECK_ALL') ?> / <?= GetMessage('UNCHECK_ALL') ?>" id="SelectAll" />
    <? } ?>
    
<? elseif (isset($arResult['MODELS'])): ?>

    <table cellpadding=0 cellspacing=0 class="tecdoc model_select">
        <thead>
            <tr>
                <th><?= GetMessage('HEAD_MODEL') ?></th>
                <th><?= GetMessage('HEAD_START_PRODUCTION') ?></th>
                <th><?= GetMessage('HEAD_END_PRODUCTION') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach($arResult['MODELS'] as $value) { ?>
            <? if (trim($additional_url) == '') {
                $value['begin'] = substr($value['yearOfConstrFrom'], 4, 2)
                                   . '.'
                                   . substr($value['yearOfConstrFrom'], 0, 4);
                $value['end'] = substr($value['yearOfConstrTo'], 4, 2)
                                 . '.'
                                 . substr($value['yearOfConstrTo'], 0, 4);
                if($value['end'] == '.')
                    $value['end'] = GetMessage('AVAILABLE');
            }?>
            <? if($bIsAdmin):?>
                <tr>
                    <td>
                        <input type="checkbox" class="f_access" value="<?=$arResult['brand_id']?>;<?=$arResult['model_group'];?>;<?=$value['modelId']?>" name="MODELS" <?=((!isset($arResult['ACCESS_LIST_DISABLE'][$arResult['brand_id'].';'.$arResult['model_group'].';'.$value['modelId']]))?' checked="checked"':'');?> />
                        <a href="<?=$arParams['SEF_FOLDER']?><?=$brand?>/<?=$additional_url?><?=$value['modelId']?>/"><?=$value['modelname']?></a>
                    </td>
                    <td width="150"><?=$value['begin']?></td>
                    <td width="150"><?=$value['end']?></td>
                </tr>
            <?elseif($bIsAdmin === false && !isset($arResult['ACCESS_LIST_DISABLE'][$arResult['brand_id'].';'.$arResult['model_group'].';'.$value['modelId']])):?>
                <tr>
                    <td>
                        <a href="<?= $arParams['SEF_FOLDER'] ?><?= $brand ?>/<?= $additional_url ?><?= $value['modelId'] ?>/"><?= $value['modelname'] ?></a>
                    </td>
                    <td width="150"><?= $value['begin'] ?></td>
                    <td width="150"><?= $value['end'] ?></td>
                </tr>
            <?endif;?>
        <? } ?>
        </tbody>
    </table>
    <? if ($bIsAdmin) { ?>
        <br />
        <input type="button" value="<?= GetMessage('CHECK_ALL') ?> / <?= GetMessage('UNCHECK_ALL') ?>" id="SelectAll" />
    <? } ?>
    
<? elseif (isset($arResult['MODIFICATION'])): ?>

    <table cellpadding="0" cellspacing="0" class="model_select tecdoc">
        <thead>
            <tr>
                <th><?= GetMessage('HEAD_INFO') ?></th>
                <th><?= GetMessage('HEAD_TYPE') ?></th>
                <th><?= GetMessage('HEAD_YEAR') ?></th>
                <th><?= GetMessage('HEAD_KILOWATTS') ?></th>
                <th><?= GetMessage('HEAD_HORSEPOWER') ?></th>
                <th><?= GetMessage('HEAD_VOLUME') ?></th>
                <th><?= GetMessage('HEAD_FORM_ASSEMBLING') ?></th>
            </tr>
        </thead>
    <tbody>
        <?foreach($arResult['MODIFICATION'] as $value):?>
            <?if($bIsAdmin === true || ( $bIsAdmin === false && !isset($arResult['ACCESS_LIST_DISABLE'][$arResult['brand_id'].';'.$arResult['model_group'].';'.$arResult['model_id'].';'.$value['carId']]) )):?>
            <tr>
                <td class="infoTd" align="center">
                    <? if (isset($arResult['MODIFICATION_CARINFO'][$value['carId']])) {
                        $dop_info = $arResult['MODIFICATION_CARINFO'][$value['carId']];
                        
                            ob_start();
                        ?>
                            <table>
                                <tbody>
                                    <tr>
                                        <td><?= GetMessage('INFO_YEAR') ?>:</td>
                                        <td>
                                            <b>
                                                <?= substr($dop_info['vehicleDetails2']['yearOfConstructionFrom'], 4, 2) . '/' . substr($dop_info['vehicleDetails2']['yearOfConstructionFrom'], 0, 4)?> <?=(($dop_info['vehicleDetails2']['yearOfConstructionTo']) ? (' - ' . substr($dop_info['vehicleDetails2']['yearOfConstructionTo'], 4, 2) . '/' . substr($dop_info['vehicleDetails2']['yearOfConstructionTo'], 0, 4)) : ('')) ?>
                                            </b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_TYPE_DESIGN') ?>:</td>
                                        <td>
                                            <b><?= $dop_info['vehicleDetails2']['constructionType'] ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_TYPE_DRIVE') ?>:</td>
                                        <td>
                                            <b><?=$dop_info['vehicleDetails2']['impulsionType']?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_POWER_KILOWATTS') ?>:</td>
                                        <td>
                                            <b><?=$dop_info['vehicleDetails2']['powerKW']?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_POWER_HORSEPOWER') ?>:</td>
                                        <td>
                                            <b><?=$dop_info['vehicleDetails2']['powerHP']?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_VOLUME_CM') ?>:</td>
                                        <td>
                                            <b><?=$dop_info['vehicleDetails2']['cylinderCapacityCcm']?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_VOLUME_LITERS') ?>:</td>
                                        <td>
                                            <b><?=substr($dop_info['vehicleDetails2']['cylinderCapacityLiter'], 0, 1) . '.' . substr($dop_info['vehicleDetails2']['cylinderCapacityLiter'], 1) . '0'?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_CYLINDER') ?>:</td>
                                        <td>
                                            <b><?=$dop_info['vehicleDetails2']['cylinder']?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_COUNT_VALVES') ?>:</td>
                                        <td>
                                            <b><?=$dop_info['vehicleDetails2']['valves']?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_BRAKE_SYSTEM') ?>:</td>
                                        <td>
                                            <b><?=$dop_info['vehicleDetails2']['brakeSystem']?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_TYPE_ENGINE') ?>:</td>
                                        <td>
                                            <b><?=$dop_info['vehicleDetails2']['motorType']?></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= GetMessage('INFO_TYPE_FUEL') ?>:</td>
                                        <td>
                                            <b><?=$dop_info['vehicleDetails2']['fuelType']?></b>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?
                            $content = ob_get_contents();
                            ob_end_clean();
                        ?>
                        
                        <?  // Popup с информацией
                            CUtil::InitJSCore(array('window', 'ajax')); 

                            $arDialogParams = array(
                              'title' => GetMessage('ADDITIONAL_INFO'),
                              'width' => 450,
                              'height' => 500,
                              'min_width' => 300,
                              'min_height' => 400,
                              'resizable' => false,
                              'draggable' => true,
                              'content' => $content,
                           );
                           
                           $href = '(new BX.CDialog('.CUtil::PhpToJsObject($arDialogParams).')).Show()';
                        ?>
                        <a
                            href="javascript:void(0)"
                            onclick="javascript:<?= $href ?>"
                            title="<?= GetMessage('SHOW_ADDITIONAL_INFO') ?>"
                            class="openInfo"
                        >
                            <div class="icon-info"></div>
                        </a>
                    <? } ?>
                </td>
                <td>
                    <? if ($bIsAdmin) { ?>
                        <input
                            type="checkbox"
                            class="f_access"
                            value="<?= $arResult['brand_id'] ?>;<?= $arResult['model_group'] ?>;<?= $arResult['model_id'] ?>;<?=$value['carId']?>"
                            name="MODIFICATION"
                            <?=((!isset($arResult['ACCESS_LIST_DISABLE'][$arResult['brand_id'].';'.$arResult['model_group'].';'.$arResult['model_id'].';'.$value['carId']]))?' checked="checked"':'');?>
                        />
                    <? } ?>
                    <a href="<?=$arParams['SEF_FOLDER']?><?=$brand?>/<?=$additional_url?><?=$arResult['model_id']?>/<?=$value['carId']?>/"><?=$value['carName']?></a>
                </td>
                <td width="150"><?= $value['begin'] ?>-<?= $value['end'] ?></td>
                <td width="50"><?= $value['powerKW'] ?></td>
                <td width="50"><?= $value['powerHP'] ?></td>
                <td width="70"><?= $value['ccm'] ?></td>
                <td width="150"><?= $value['constrType'] ?></td>
            </tr>
            <?endif;?>
        <?endforeach;?>
    </tbody>
    </table>
    <? if ($bIsAdmin) { ?>
        <br />
        <input type="button" value="<?= GetMessage('CHECK_ALL') ?> / <?= GetMessage('UNCHECK_ALL') ?>" id="SelectAll" />
    <? } ?>
<?elseif(isset($arResult['GROUPS'])):?>
    <?
    $array = array();
    $i=0;
    $aDisableItem = Array();
    ?>
    <ul id="lm-auto-tecdoc-navigation" class="tecdoc">
        <? foreach ($arResult['GROUPS'] as $car){ ?>
            <?
            $bItemChecked = true;
            if(
               //если существует ограничение на конкретную модель
               isset($arResult['ACCESS_LIST_DISABLE'][$arResult['brand_id'].';'.$arResult['model_group'].';'.$arResult['model_id'].';'.$arResult['car_id'].';'.$car['assemblyGroupNodeId']]) ||
               // или на все авто
               isset($arResult['ACCESS_LIST_DISABLE']['/' . $car['assemblyGroupNodeId']])
              ){$bItemChecked = false;}
            
            if( ($bIsAdmin === false && $bItemChecked === false) || ($bIsAdmin === false && isset($aDisableItem[$car['parentNodeId']]))){
                $aDisableItem[$car['assemblyGroupNodeId']] = true;
                $bItemChecked = false;
            }
            
            if($bIsAdmin === true || ( $bIsAdmin === false && $bItemChecked === true && !isset($aDisableItem[$car['parentNodeId']])) ):
            ?>
                <?if($array[$i] != $car['parentNodeId']){?>
                    <?unset($array[$i]);?>
                    <?$i--;?>
                    
                    </ul></li>
                <?}?>
                <?if($array[$i] != $car['parentNodeId']){?>
                    <?unset($array[$i]);?>
                    <?$i--;?>
                    
                    </ul></li>
                <?}?>
                <?if($array[$i] != $car['parentNodeId']){?>
                    <?unset($array[$i]);?>
                    <?$i--;?>
                    
                    </ul></li>
                <?}?>
                <?if ($car['hasChilds']==1){?>
                    <?$i++?>
                    <?$array[$i] = $car['assemblyGroupNodeId'];?>
                    
                    <li>
                    <?if($bIsAdmin === true):?>
                    
                    <input type="checkbox" class="f_access" value="<?=$arResult['brand_id']?>;<?=$arResult['model_group'];?>;<?=$arResult['model_id']?>;<?=$arResult['car_id']?>;<?=$car['assemblyGroupNodeId'];?>" name="GROUPS" <?=(($bItemChecked === true)?' checked="checked"':'');?> />
                    <?endif;?>
                    
                    <span><?=$car['assemblyGroupName'];?></span>
                    <ul>
                <?} else {?>
                
                    <li>
                    <?if($bIsAdmin === true):?>
                    <input type="checkbox" class="f_access" value="<?=$arResult['brand_id']?>;<?=$arResult['model_group'];?>;<?=$arResult['model_id']?>;<?=$arResult['car_id']?>;<?=$car['assemblyGroupNodeId'];?>" name="GROUPS" <?=(($bItemChecked === true)?' checked="checked"':'');?> />
                    <?endif;?>
                    <a href="<?=$arParams['SEF_FOLDER']?><?=$brand?>/<?=$additional_url?><?=$arResult['model_id']?>/<?=$arResult['car_id']?>/<?=$car['assemblyGroupNodeId']?>/"><?=$car['assemblyGroupName'];?></a>
                    </li>
                    
                <? }?>
            <?
            endif;
            ?>
        <? }?>
        <? foreach($array as $ar) { ?>
            </ul></li>
        <? } ?>
    </ul>
    <? if ($bIsAdmin) { ?>
        <br />
        <input type="button" value="<?= GetMessage('CHECK_ALL') ?> / <?= GetMessage('UNCHECK_ALL') ?>" id="SelectAll" />
        <input type="button" value="<?= GetMessage('APPLY_ALL') ?>" id="ApplyForAllAuto" />
    <? } ?>
<? elseif (isset($arResult['DETAILS'])): ?>

    <table cellpadding="0" cellspacing="0" class="model_select tecdoc" id="tecdoc_search_result">
        <thead>
            <tr>
                <th><?= GetMessage('HEAD_NAME') ?></th>
                <th><?= GetMessage('HEAD_BRAND') ?></th>
                <th><?= GetMessage('HEAD_ARTICLE') ?></th>
                <th><?= GetMessage('HEAD_INFO') ?></th>
                <th><?= GetMessage('HEAD_BUY') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach($arResult['DETAILS'] as $value) { ?>
                <tr>
                    <td>
                        <?= $value['genericArticleName'] ?>
                    </td>
                    <td class="brand" width="110">
                        <? /* a class="manufacture_info"  href="javascript:void(0);" title="<?= GetMessage('MANUFACTURER_INFO') ?>"><?=$value['brandName']?></a */ ?>
                        <?= $value['brandName'] ?>
                    </td>
                    <td width="110">
                        <?= $value['articleNo'] ?>
                    </td>
                    <td width="110">
                        <? $folder = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_DEMO_FOLDER'); ?>
                        <a href="<?= $folder ?>part-detail/<?= $value['articleId'] ?>/<?= $value['articleLinkId'] ?>/">
                            <?= GetMessage('INFO') ?>
                        </a>
                    </td>
                    <td width="120">
                        <a href="<?= $value['search_url'] ?>"><?= GetMessage('GET_PRICE') ?></a>
                    </td>
                </tr>
            <? } ?>
        </tbody>
    </table>

<? elseif (isset($arResult['DETAIL'])): ?>
    <h1><?= GetMessage('TITLE_ARTICLE_NUMBER') ?>: <?= $arResult['DETAIL'][0]['assignedArticle']['articleNo'] ?></h1>

    <? if (!empty($arResult['DETAIL'][0]['immediateAttributs']['array'])) { ?>
        <h3><?= GetMessage('TITLE_ADDITIONAL_FEATURES') ?></h3>
        <div class="standartTable">
            <table class="tecdoc_details" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <th><?= GetMessage('HEAD_PROPERTY') ?></th>
                <th><?= GetMessage('HEAD_VALUE') ?></th>
            </tr>
            </thead>
            <tbody>
            <? foreach ($arResult['DETAIL'][0]['immediateAttributs']['array'] as $val) { ?>
                <tr>
                    <td><?= $val['attrName'] ?>:</td>
                     <td><?= $val['attrValue'] ?></td>
                 </tr>
            <? } ?>
            </tbody>
            </table>
        </div>
    <? } ?>
    <? if (!empty($arResult['DETAIL'][0]['oenNumbers']['array'])) { ?>
        <h3><?= GetMessage('TITLE_CONFORMITY_ORIGINAL_NUMBERS') ?></h3>
        <div class="standartTable">
            <table class="tecdoc_details" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <th><?= GetMessage('HEAD_MARK') ?></th>
                    <th><?= GetMessage('HEAD_NUMBER') ?></th>
                </tr>
                </thead>
                <tbody>
                <? foreach ($arResult['DETAIL'][0]['oenNumbers']['array'] as $value) { ?>
                    <tr>
                        <td><?= $value['brandName'] ?></td>
                        <td><?= $value['oeNumber'] ?></td>
                    </tr>
                <? } ?>
                </tbody>
            </table>
        </div>
    <? } ?>
    
    <? if (isset($arResult['DETAIL']['img'])) { ?>
        <img src="<?= $arResult['DETAIL']['img'] ?>" alt="" />
    <? } ?>
    <br /><br />
    
    <?  // Цены.
        $APPLICATION->IncludeComponent(
            "linemedia.auto:search.results",
            ".default",
            array(
                "QUERY"         => $arResult['DETAIL'][0]['assignedArticle']['articleNo'],
                "PART_ID"       => '',
                "BRAND_ID"      => '',
                "BRAND_TITLE"   => '',
                "EXTRA"         => $arResult['DETAIL'][0]['assignedArticle'],
                "BASKET_URL"    => "/personal/cart/",
                "TITLE"         => "Поиск запчасти #QUERY#",
                "SET_TITLE"     => "N"
            ),
            false
        );
    ?>
    
<?endif;?>

<? if (isset($arResult['SEO']['TEXT']) && !empty($arResult['SEO']['TEXT'])) { ?>
    <div class="seo-description">
        <?= $arResult['SEO']['TEXT'] ?>
    </div>
<? } ?>
