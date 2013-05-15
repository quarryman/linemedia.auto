<script>
    function MakeOrder(Params)
    {
        var data = {};
        $('#order_result_l').show();
        
        if (Params['action']) { data['type'] = Params['action']; }
        if (Params['after']) { data['after'] = Params['after']; }
        if (Params['id']) { data['id'] = Params['id']; }
        if (Params['SaleGroup']) { data['SaleGroup'] = Params['SaleGroup']; }
        if (Params) { data['PARAMS'] = Params; }
        
        data['site_id'] = $('#site_id').val();
        data['discount_type'] = $('input:radio[name=discount_type]:checked').val();
        data['discount_value'] = $('#discount_value').val();
        data['price_delivery'] = $('#price_delivery').val();
        data['delivery_system'] = $('#delivery_system').val();
        if ($("#pay_system").length) { data['pay_system'] = $('#pay_system').val(); }
        data['person_type'] = $('#person_type').val();
        data['member_id'] = $('#member_id').val();
        data['manager_comment'] = $('#manager_comment').val();
        data['baskets'] = $('#f_order').serialize();
        
        // Свойства заказа.
        data['prop'] = {};
        if ($('#t_member input, #t_member select, #t_member textarea')) {
            $('#t_member input, #t_member select, #t_member textarea').each(function(index) {
                if ($(this).val() !== undefined) {
                    data[$(this).attr('name')] = $(this).val();
                }
            });
        }
        data['prop'] = encodeURIComponent(JSON.stringify(data['prop']));
        
        data['OrderParams'] = $('#order').serialize();
        
        // Дополнительная информация подключенных модулей.
        data['ADDITIONAL_MODULE_PARAMS'] = {};
        $('#module-additional-data').find('input:text, input:checkbox:checked, input:radio:checked, select, textarea').each(function() {
            data['ADDITIONAL_MODULE_PARAMS'][$(this).attr('name')] = $(this).val();
        });
        
        data['order'] = {};
        if ($('#order :text')) {
            $('#order :text').each(function(index) {
                if ($(this).val() !== undefined) {
                    data['order'][$(this).attr('rel')] = $(this).val();
                }
            });
        }
        data['order'] = encodeURIComponent(JSON.stringify(data['order']));
        data['extra'] = Params['extra'];
        $("#b_order_save").attr("disabled", "disabled");
        $("#b_order_make_stay").attr("disabled", "disabled");
        $("#b_order_make_go").attr("disabled", "disabled");
        $('#order_r').html('<p><img src="/bitrix/themes/.default/images/wait.gif" /> <?= GetMessage('LOADING') ?>...</p>');
        $.post('/bitrix/admin/linemedia.auto_sale_order_create.php?step=recalc&AJAX=true', data,
            function (data_r) {
                $('#order_r').html(data_r);
            },
        'html');
        
        return true;
    }
</script>

<table cellspacing="0" class="edit-tab" style="border: solid 1px #B8C1DD;">
    <tbody>
        <tr>
            <td>
                <div class="edit-tab-inner">
                    <div style="height: 100%;">
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-tab-title">
                            <tbody>
                                <tr>
                                    <td class="icon"><div id="sale"></div></td>
                                    <td class="title"><?= GetMessage('ORDER_NEW_TITLE_ORDER') ?></td>
                                </tr>
                                <tr>
                                    <td class="delimiter" colspan="2"><div class="empty"></div></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        
                        <? /* ========== Покупатель ========== */ ?>
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-table">
                            <tbody>
                                <tr id="tr_order_user" class="heading">
                                    <td colspan="2"><?= GetMessage('ORDER_NEW_TITLE_USER') ?></td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_USER') ?>:</td>
                                    <td width="60%">
                                        <input type="hidden" name="member_id" id="member_id" value="<?= $aUser['ID'] ?>" />
                                        [<a href="/bitrix/admin/user_edit.php?ID=<?= $aUser['ID'] ?>&amp;lang=ru" target="_blank"><?= $aUser['ID'] ?></a>] <?= $aUser['LAST_NAME'] ?> <?= $aUser['NAME'] ?> <?= $aUser['SECOND_NAME'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_LOGIN') ?>:</td>
                                    <td width="60%"><?= $aUser['LOGIN'] ?></td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_EMAIL') ?>:</td>
                                    <td width="60%"><a href="mailto:<?= $aUser['EMAIL'] ?>"><?= $aUser['EMAIL'] ?></a></td>
                                </tr>
                                <? if (!empty($aUser['PERSONAL_PHONE'])) { ?>
                                    <tr>
                                        <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_PHONE') ?>:</td>
                                        <td width="60%"><?=$aUser['PERSONAL_PHONE'];?></td>
                                    </tr>
                                <? } ?>
                                <? if (intval($aUser['PERSONAL_COUNTRY']) > 0) { ?>
                                    <tr>
                                        <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_COUNTRY') ?>:</td>
                                        <td width="60%"><?= GetCountryByID($aUser['PERSONAL_COUNTRY']) ?></td>
                                    </tr>
                                <? } ?>
                                <? if (!empty($aUser['PERSONAL_STATE'])) { ?>
                                    <tr>
                                        <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_STATE') ?>:</td>
                                        <td width="60%"><?=$aUser['PERSONAL_STATE'];?></td>
                                    </tr>
                                <? } ?>
                                <? if (!empty($aUser['PERSONAL_CITY'])) { ?>
                                    <tr>
                                        <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_CITY') ?>:</td>
                                        <td width="60%"><?=$aUser['PERSONAL_CITY'];?></td>
                                    </tr>
                                <? } ?>
                                <? if (!empty($aUser['PERSONAL_ZIP'])) { ?>
                                    <tr>
                                        <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_ZIP') ?>:</td>
                                        <td width="60%"><?=$aUser['PERSONAL_ZIP'];?></td>
                                    </tr>
                                <? } ?>
                                <? if (!empty($aUser['PERSONAL_STREET'])) { ?>
                                    <tr>
                                        <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_STREET') ?>:</td>
                                        <td width="60%"><?= $aUser['PERSONAL_STREET'] ?></td>
                                    </tr>
                                <? } ?>
                            </tbody>
                        </table>
                        
                        
                        <? /* ========== Сайт ========== */ ?>
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-table">
                            <tbody>
                                <tr id="tr_order_site_id" class="heading">
                                    <td colspan="2"><?= GetMessage('ORDER_NEW_TITLE_SITE_ID') ?></td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_SITE_ID') ?>:</td>
                                    <td width="60%">
                                        <? if (count($arSites) > 0) { ?>
                                            <? if (count($arSites) > 1) { ?>
                                                <select style="width: 100%;" name="site_id" id="site_id" onchange="self.location.href='<?= $APPLICATION->GetCurPageParam('', array('p_type'), false);?>&p_type=' + this.value;">
                                                    <? foreach ($arSites as $arSite) { ?>
                                                        <option value="<?= $arSiteID ?>"<? if ($arSite['ID'] == $iSiteId) { ?> selected="selected"<? } ?>>
                                                            <?= $arSite['NAME'] ?> [<?= $arSite['ID'] ?>]
                                                        </option>
                                                    <? } ?>
                                                    <? unset($arSiteID, $arSite); ?>
                                                </select>
                                            <? } else { ?>
                                                <? $arSite = reset($arSites); ?>
                                                <input type="hidden" name="site_id" id="site_id" value="<?= $arSite['ID'] ?>" />
                                                <?= $arSite['NAME'] ?> [<?= $arSite['ID'] ?>]
                                            <? } ?>
                                        <? } else { ?>
                                            <?= GetMessage('ORDER_NEW_NO_SITE_ID') ?>
                                        <? } ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        
                        <? /* ========== Тип плательщика ========== */ ?>
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-table">
                            <tbody>
                                <tr id="tr_order_delivery" class="heading">
                                    <td colspan="2"><?= GetMessage('ORDER_NEW_TITLE_PERSON_TYPE') ?></td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_PERSON_TYPE') ?>:</td>
                                    <td width="60%">
                                        <? if (count($aPersonType) > 0) { ?>
                                            <select style="width: 100%;" name="person_type" id="person_type" onchange="self.location.href='<?= $APPLICATION->GetCurPageParam('', array('p_type'), false);?>&p_type=' + this.value;">
                                                <? foreach ($aPersonType as $iPersonTypeID => $sPersonTypeName) { ?>
                                                    <option value="<?= $iPersonTypeID ?>"<? if ($iPersonTypeID == $iPersonTypeSelect) { ?> selected="selected"<? } ?>>
                                                        <?= $sPersonTypeName ?> [<?= $iPersonTypeID ?>]
                                                    </option>
                                                <? } ?>
                                                <? unset($iPersonTypeID, $sPersonTypeName); ?>
                                            </select>
                                        <? } else{ ?>
                                            <?= GetMessage('ORDER_NEW_NO_PERSON_TYPE') ?>
                                        <? } ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        
                        <? /* ========== Свойства заказа ========== */ ?>
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-table" id="t_member">
                            <tbody>
                                <? foreach ($aPropsGroup as $iPropsGroupID => $aPropsGroupItem) {  ?>
                                     <? if (isset($aPropsGroupItem['PROPS']) && count($aPropsGroupItem['PROPS']) > 0) { ?>
                                        <tr id="tr_order_props" class="heading">
                                            <td colspan="2"><?= $aPropsGroupItem['NAME'] ?></td>
                                        </tr>
                                        <? foreach ($aPropsGroupItem['PROPS'] as $iPropID => $aPropItem) {
                                            if (in_array($aPropItem['CODE'], $aPropsCodeIgnore)) {
                                                continue;
                                            }
                                        ?>
                                        <tr>
                                            <td <? if (in_array($aPropItem['TYPE'], array("TEXTAREA", "LOCATION", "RADIO", "MULTISELECT"))):?> valign="top"<?endif;?> width="40%" class="field-name">
                                                <label for="prop_<?= $aPropItem['ID'] ?>"><?=$aPropItem['NAME']?></label>
                                            </td>
                                            <td width="60%">
                                                <?  // Значение свойства.
                                                    switch ($aPropItem['CODE']) {
                                                        default:
                                                            $value = (isset($aPropItem['DEFAULT_VALUE'])) ? $aPropItem['DEFAULT_VALUE'] : "";
                                                            break;
                                                        case 'FIO':
                                                            $value = (!empty($aUser['NAME'])) ? $aUser['LAST_NAME'] . ' ' . $aUser['NAME'] . ' ' . $aUser['SECOND_NAME'] : $prop['DEFAULT_VALUE'];
                                                            break;
                                                        case 'ZIP':
                                                            $value = (!empty($aUser['PERSONAL_ZIP'])) ? $aUser['PERSONAL_ZIP'] : $aPropItem['DEFAULT_VALUE'];
                                                            break;
                                                        case 'ADDRESS':
                                                            $value = (!empty($aUser['PERSONAL_STREET'])) ? $aUser['PERSONAL_STREET'] : $aPropItem['DEFAULT_VALUE'];
                                                            break;
                                                        case 'EMAIL':
                                                            $value = (!empty($aUser['EMAIL'])) ? $aUser['EMAIL'] : $aPropItem['DEFAULT_VALUE'];
                                                            break;
                                                        case 'PHONE':
                                                            $value = (!empty($aUser['PERSONAL_PHONE'])) ? $aUser['PERSONAL_PHONE'] : $aPropItem['DEFAULT_VALUE'];
                                                            break;
                                                        case 'LOCATION':
                                                            $value = (array_search($aUser['PERSONAL_CITY'], $aPropItem['CITIES'])) ? array_search($aUser['PERSONAL_CITY'], $aPropItem['CITIES']) : '';
                                                            break;
                                                    }
                                                ?>
                                                
                                                <? if ($aPropItem['TYPE'] == "TEXT") { ?>
                                                    <input  style="width: 100%;" id="prop_<?= $aPropItem['ID'] ?>" type="text" name="prop[<?=$aPropItem['ID']?>]" value="<?=$value?>" size="<?=$aPropItem['SIZE1']?$aPropItem['SIZE1']:30?>" />
                                                <? } elseif ($aPropItem['TYPE'] == "TEXTAREA") { ?>
                                                    <textarea style="width: 100%;" id="prop_<?=$aPropItem['ID'];?>" name="prop[<?=$aPropItem['ID']?>]" cols="<?=$aPropItem['SIZE1']?$aPropItem['SIZE1']:30?>" rows="<?=$aPropItem['SIZE2']?$aPropItem['SIZE2']:3?>"><?=$value?></textarea>
                                                <? } elseif ($aPropItem['TYPE'] == "CHECKBOX") { ?>
                                                    <input type="hidden" name="prop[<?=$aPropItem['ID']?>]" value="N" />
                                                    <input id="prop_<?=$aPropItem['ID'];?>" type="checkbox" name="prop[<?=$aPropItem['ID']?>]" value="Y" <?if ($value):?> checked<?endif;?> />
                                                <? } elseif ($aPropItem['TYPE'] == "SELECT") { ?>
                                                    <?if (count($aPropItem['VARIANTS'])):?>
                                                        <select style="width: 100%;" id="prop_<?=$aPropItem['ID'];?>" name="prop[<?=$aPropItem['ID']?>]">
                                                            <option value="0">-- <?= GetMessage('CHOOSE') ?> --</option>
                                                            <? foreach ($aPropItem['VARIANTS'] as $variant) { ?>
                                                                <option value="<?= $variant['VALUE'] ?>" <? if ($value == $variant['VALUE']) { ?> selected="selected"<? } ?>>
                                                                    <?= $variant['NAME'] ?>
                                                                </option>
                                                            <? } ?>
                                                            <? unset($variant);?>
                                                        </select>
                                                    <?else:?>
                                                        <i><?= GetMessage('NO_VARIANTS') ?></i>
                                                    <?endif;?>
                                                <? } elseif ($aPropItem['TYPE'] == "MULTISELECT") { ?>
                                                    <?if (!is_array($value)) $value = explode(",", $value);?>
                                                    <?if (count($aPropItem['VARIANTS'])):?>
                                                        <? if ($aPropItem['SIZE1'] > 0) {
                                                            if (count($aPropItem['VARIANTS']) <= $aPropItem['SIZE1']) $rows = count($aPropItem['VARIANTS']);
                                                        } else {
                                                            if (count($aPropItem['VARIANTS']) <= 5) $rows = 5;
                                                        }
                                                        if (!is_array($value)) $value = array($value);
                                                        ?>
                                                        <input type="hidden" name="prop[<?=$aPropItem['ID']?>]" value="0" />
                                                        <select style="width: 100%;" id="prop_<?=$aPropItem['ID'];?>" name="prop[<?=$aPropItem['ID']?>][]" multiple size="<?=$rows?>">
                                                            <? foreach ($aPropItem['VARIANTS'] as $variant) { ?>
                                                                <option value="<?= $variant['VALUE'] ?>" <?if (in_array($variant['VALUE'], $value)) { ?> selected<? } ?>>
                                                                    <?= $variant['NAME'] ?>
                                                                </option>
                                                            <? } ?>
                                                        </select>
                                                    <?endif;?>
                                                <? } elseif ($aPropItem['TYPE'] == "RADIO") { ?>
                                                    <?if (count($aPropItem['VARIANTS'])):?>
                                                        <table cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <?foreach ($aPropItem['VARIANTS'] as $variant) { ?>
                                                            <tr <?if ($variant['DESCRIPTION']):?> valign="top"<?endif;?>>
                                                                <td><input type="radio" name="prop[<?=$aPropItem['ID']?>]" value="<?=$variant['VALUE']?>" <?if ($value == $variant['VALUE']):?> checked<?endif;?> /></td>
                                                                <td width="100%">
                                                                    <?if ($variant['DESCRIPTION']) { ?>
                                                                        <div><strong><?= $variant['NAME'] ?></strong></div>
                                                                        <div><?= $variant['DESCRIPTION'] ?></div>
                                                                    <? } else { ?>
                                                                        <div><?= $variant['NAME'] ?></div>
                                                                    <? } ?>
                                                                </td>
                                                            </tr>
                                                        <? } ?>
                                                        </table>
                                                    <?endif;?>
                                                <? } elseif ($aPropItem['TYPE'] == "LOCATION") { ?>
                                                    <? if (count($aPropItem['CITIES'])) { ?>
                                                        <select style="width: 100%;" id="prop_<?=$aPropItem['ID'];?>" name="prop[<?=$aPropItem['ID']?>]" size="1">
                                                            <option value="0">-- <?= GetMessage('CHOOSE_CITY') ?> --</option>
                                                            <? foreach ($aPropItem['CITIES'] as $city_id => $city_name) { ?>
                                                                <option value="<?= $city_id ?>" <? if ($value == $city_id) { ?> selected<? } ?>>
                                                                    <?= $city_name ?>
                                                                </option>
                                                            <? } ?>
                                                        </select>
                                                    <? } ?>
                                                <? } ?>
                                            </td>
                                        </tr>
                                    <? } ?>
                                        <? unset($iPropID, $aPropItem); ?>
                                    <? } ?>
                                <? } ?>
                                <? unset($iPropsGroupID, $aPropsGroupItem, $aPropsCodeIgnore); ?>
                            </tbody>
                        </table>
                        
                        
                        
                        <? /* ==========  Служба доставки ========== */ ?>
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-table">
                            <tbody>
                                <tr id="tr_order_delivery" class="heading">
                                    <td colspan="2"><?= GetMessage('ORDER_NEW_TITLE_DELIVERY') ?></td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_DELIVERY') ?></td>
                                    <td width="60%">
                                        <script type="text/javascript">
                                            var aServiceDelivery = {
                                                <?$i=1; foreach ($aServiceDelivery as $delivery_system_id=>$delivery_system):?>
                                                <?= $delivery_system_id ?> : "<?=$delivery_system['PRICE'];?>"<?=(count($aServiceDelivery) !== $i)?",":'';?>
                        
                                                <?$i++; endforeach; unset($delivery_system_id, $delivery_system, $i);?>
                                            };
                                        </script>
                                        <select style="width: 100%;" name="delivery_system" id="delivery_system" onchange="javascript: if (aServiceDelivery[$(this).val()]) { $('#price_delivery').val(aServiceDelivery[$(this).val()]) } else { $('#price_delivery').val('')}; MakeOrder({action : 'save', id : 'false'});">
                                        <? foreach ($aServiceDelivery as $delivery_system_id=>$delivery_system) { ?>
                                            <option value="<?= $delivery_system_id ?>" rel="<?= htmlspecialcharsEx($delivery_system['PRICE']); ?>"><?=$delivery_system['NAME']?> [<?=$delivery_system['ID']?>]</option>
                                        <? } ?>
                                        <? unset($delivery_system_id, $delivery_system) ?>
                                        <? foreach ($aServiceDeliveryHandler as $delivery_system_id => $aDelivery) { ?>
                                            <? foreach ($aDelivery["PROFILES"] as $profile_id => $arProfile) { ?>
                                                <option value="<?=$delivery_system_id.":".$profile_id;?>" rel="<?= htmlspecialcharsEx($delivery_system['PRICE']);?>">
                                                    <?= $aDelivery["NAME"] ?> - <?= $arProfile["TITLE"] ?> [<?= $delivery_system_id ?>]
                                                </option>
                                            <? } ?>
                                            <?  unset($profile_id, $arProfile) ?>
                                        <? } ?>
                                        <? unset($delivery_system_id, $delivery_system) ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name">
                                        <label for="price_delivery"><?= GetMessage('ORDER_NEW_DELIVERY_PRICE') ?></label>
                                    </td>
                                    <td width="60%">
                                        <? reset($aServiceDelivery); $aSDFirst = current($aServiceDelivery);?>
                                            <input
                                                type="text"
                                                value="<?= $aSDFirst['PRICE'] ?>"
                                                name="PRICE_DELIVERY"
                                                id="price_delivery"
                                                size="10"
                                                maxlength="8"
                                                onchange="MakeOrder({action : 'save', id : 'false'});"
                                            />
                                        <? unset($aSDFirst) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                       
                       
                        <? /* ==========  Способы оплаты ========== */ ?>
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-table">
                            <tbody>
                                <tr id="tr_order_paysystem" class="heading">
                                    <td colspan="2"><?= GetMessage('ORDER_NEW_TITLE_PAYSYSTEM') ?></td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name"><?= GetMessage('ORDER_NEW_PAYSYSTEM') ?></td>
                                    <td width="60%">
                                        <? if (!empty($aPaySystem)) { ?>
                                        <select style="width: 100%;" name="pay_system" id="pay_system">
                                            <? foreach ($aPaySystem as $iPaySystemID => $PaySystemName) { ?>
                                                <option value="<?= $iPaySystemID ?>"><?= $PaySystemName ?> [<?= $iPaySystemID ?>]</option>
                                            <? } ?>
                                            <? unset($iPaySystemID, $PaySystemName); ?>
                                        </select>
                                        <? } else { ?>
                                            <?= GetMessage('NO_PAYSYSTEMS') ?>
                                        <? } ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        
                        <? /* ========== Скидки ========== */ ?>
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-table">
                            <tbody>
                                <tr id="tr_order_discount" class="heading">
                                    <td colspan="2"><?= GetMessage('ORDER_NEW_TITLE_DISCOUNT') ?></td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name">
                                        <label for="discount_value"><?= GetMessage('ORDER_NEW_DISCOUNT_SIZE') ?></label>
                                    </td>
                                    <td width="60%">
                                        <input type="text" name="discount" id="discount_value" size="6" maxlength="6" />
                                    </td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name" valign="top"><?= GetMessage('ORDER_NEW_DISCOUNT_TYPE') ?></td>
                                    <td width="60%">
                                        <input type="radio" name="discount_type" value="percentage" id="discount_type_per" checked="checked" /> <label for="discount_type_per"><?= GetMessage('ORDER_NEW_DISCOUNT_TYPE_PERCENT') ?></label><br />
                                        <input type="radio" name="discount_type" value="amount" id="discount_type_amount" /> <label for="discount_type_amount"><?= GetMessage('ORDER_NEW_DISCOUNT_TYPE_SUMM') ?></label><br />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        
                        <? /* ========== Дополнительные параметры от модулей ========== */ ?>
                        <div id="module-additional-data">
                            <?= $html ?>
                        </div>
                        
                        
                        <? /* ========== Дополнительно ========== */ ?>
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-table">
                            <tbody>
                                <tr id="tr_order_additionally" class="heading">
                                    <td colspan="2"><?= GetMessage('ADDITIONALLY') ?></td>
                                </tr>
                                <tr>
                                    <td width="40%" class="field-name" valign="top">
                                        <label for="manager_comment" onclick="$('#manager_comment').focus().select();"><?= GetMessage('ORDER_NEW_MANAGER_COMMENT') ?></label>
                                    </td>
                                    <td width="60%">
                                        <textarea rows="5" style="width: 100%;" name="manager_comment" id="manager_comment"><?= GetMessage('ORDER_NEW_ORDER_MANAGER') ?> [<?= $USER->GetID() ?>] <?= $USER->GetFullName() ?></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        
                        <? /* ========== Состав заказа ========== */ ?>
                        <table cellspacing="0" cellpadding="0" border="0" class="edit-tab-title">
                            <tr style="display: table-row;">
                                <td class="delimiter delimiter-top">
                                    <div class="empty"></div>
                                </td>
                            </tr>
                        </table>
                    
                        <div style="display: block;" class="edit-tab-inner" id="edit2">
                            <div style="height: 100%;">
                                <div id="order_result_l" style="display: none;">
                                    <table cellspacing="0" cellpadding="0" border="0" class="edit-tab-title">
                                        <tbody>
                                            <tr>
                                                <td class="icon"><div id="sale"></div></td>
                                                <td class="title">
                                                    <?= GetMessage('ORDER_NEW_ORDER_STRUCTURE') ?>
                                                    <div id="order_r"></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="delimiter" colspan="2">
                                                    <div class="empty"></div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div style="margin-top: 10px;">
                                    <div style="float: left;">
                                        <input type="button" id="b_search_items" onclick="jsUtils.OpenWindow('/bitrix/admin/linemedia.auto_products_search.php?lang=ru&ShowPriceGroup=Y&PriceForUserID=<?=$iMember;?>&JSFUNC=MakeOrder&JSFUNCParam[action]=add', 1200, 500);" value="<?= GetMessage('ORDER_NEW_BUTTON_ADD_PRODUCT') ?>" />
                                        <input type="button" id="b_order_save" disabled="disabled" onclick="MakeOrder({action : 'save', id : 'false'}); return false;" value="<?= GetMessage('ORDER_NEW_BUTTON_RECALC') ?>" />
                                    </div>
                                    <div style="float: right;">
                                        <input type="button" id="b_order_make_stay" disabled="disabled" onclick="MakeOrder({action: 'make', id: 'false', after: 'stay'}); return false;" value="<?= GetMessage('ORDER_NEW_BUTTON_MAKE_ORDER_STAY') ?>" style="padding: 10px; cursor: pointer; font-size: 14px; font-weight: bold;" />
                                    </div>
                                    <div style="float: right;">
                                        <input type="button" id="b_order_make_go" disabled="disabled" onclick="MakeOrder({action: 'make', id: 'false', after: 'go'}); return false;" value="<?= GetMessage('ORDER_NEW_BUTTON_MAKE_ORDER_GO') ?>" style="padding: 10px; cursor: pointer; font-size: 14px; font-weight: bold;" />
                                    </div>
                                </div>
                                <br clear="all" />
                                <div id="l_items_result" class="smalltext" style="margin-top: 6px;"></div>
                            </div>
                        </div>
                        
                        
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>


