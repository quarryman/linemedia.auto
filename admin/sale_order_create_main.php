<script>
    function PostStep(member_id, SITE_ID, member)
    {
        var str_location = '/bitrix/admin/linemedia.auto_sale_order_create.php?step=make';
        if (member_id) {
            str_location += '&member=' + member_id;
        }
        if (SITE_ID) {
            str_location += '&SITE_ID=' + SITE_ID;
        }
        window.location.replace(str_location);
    }

    function MakeStep(action)
    {
        if (action == 'isset') {
            if ($('#f_search_member').val().length > 0) {
                PostStep($('#f_search_member').val(), $('#f_search_LID').val());
            }
        } else if (action == 'create') {
            $("#b_next").attr("disabled","disabled");
            $("#f_add_name").attr("disabled","disabled");
            $("#f_add_name2").attr("disabled","disabled");
            $("#f_add_email").attr("disabled","disabled");
            $("#f_add_phone").attr("disabled","disabled");
            $("#f_add_country").attr("disabled","disabled");
            $("#f_add_state").attr("disabled","disabled");
            $("#f_add_city").attr("disabled","disabled");
            $("#f_add_zip").attr("disabled","disabled");
            $("#f_add_zip").attr("disabled","disabled");
            $("#f_add_street").attr("disabled","disabled");
            $("#f_add_LID").attr("disabled","disabled");
            
            $('#r_member_register').html('<img src="/bitrix/themes/.default/images/wait.gif" /> <?= GetMessage('LOADING') ?>...');
            $.ajax({
                url : '/bitrix/admin/linemedia.auto_sale_order_create.php?step=register_user&AJAX=true',
                data : {
                    name: $('#f_add_name').val(),
                    name2: $('#f_add_name2').val(),
                    email: $('#f_add_email').val(),
                    phone: $('#f_add_phone').val(),
                    country: $('#f_add_country').val(),
                    state: $('#f_add_state').val(),
                    city: $('#f_add_city').val(),
                    zip: $('#f_add_zip').val(),
                    street: $('#f_add_street').val(),
                    baskets: $('#f_order').serialize()
                },
                type : 'post',
                dataType : 'json',
                success : function(json) {
                    $('#r_member_register').html(json.msg);
                    if (json.status == true && json.member_id > 0) {
                        PostStep(json.member_id, $('#f_add_LID').val());
                    } else {
                        $("#b_next").removeAttr("disabled");
                        $("#f_add_name").removeAttr("disabled");
                        $("#f_add_name2").removeAttr("disabled");
                        $("#f_add_email").removeAttr("disabled");
                        $("#f_add_phone").removeAttr("disabled");
                        $("#f_add_country").removeAttr("disabled");
                        $("#f_add_state").removeAttr("disabled");
                        $("#f_add_city").removeAttr("disabled");
                        $("#f_add_zip").removeAttr("disabled");
                        $("#f_add_street").removeAttr("disabled");
                        $("#f_add_LID").removeAttr("disabled");
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#r_member_register').html('Error');
                    $("#b_next").removeAttr("disabled");
                    $("#f_add_name").removeAttr("disabled");
                    $("#f_add_name2").removeAttr("disabled");
                    $("#f_add_email").removeAttr("disabled");
                    $("#f_add_phone").removeAttr("disabled");
                    $("#f_add_country").removeAttr("disabled");
                    $("#f_add_state").removeAttr("disabled");
                    $("#f_add_city").removeAttr("disabled");
                    $("#f_add_zip").removeAttr("disabled");
                    $("#f_add_street").removeAttr("disabled");
                }
            });
        }
    }
</script>

<? /* ========== Поиск существующего пользователя ========== */ ?>
<div class="filter-form" id="member_search" style="float: left; width: 50%;">
    <table cellspacing="0" cellpadding="0" border="0" class="filter-form" style="width: 99%;">
        <tbody>
            <tr class="top">
                <td class="left"><div class="empty"></div></td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                            <tr>
                                <td><div class="section-separator first"></div></td>
                                <td width="100%"><?= GetMessage('USER_SEARCH') ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="right"><div class="empty"></div></td>
            </tr>
            <tr>
                <td class="left"><div class="empty"></div></td>
                <td class="content">
                    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="display: table;" class="filtercontent">
                        <tbody>
                            <tr>
                                <td colspan="2">
                                    <b><?= GetMessage('SET_ID_FOR_SEARCH_USER') ?>:</b>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <form name="form_member_search" action="" onsubmit="return false;">
                                        <?= FindUserID('f_search_member', '', '', 'form_member_search', '3', '', GetMessage('USER_NEW_FIND')); ?>
                                    </form>
                                </td>
                            </tr>
                            <? if (is_array($aSites) && count($aSites) > 1) { ?>
                                <tr>
                                    <td colspan="2">
                                        <b><label for="f_search_LID"><?= GetMessage('SALE_SITE') ?>:</label></b><br />
                                        <select name="f_search_LID" id="f_search_LID">
                                            <? foreach ($aSites as $aItem) { ?>
                                                <option value="<?= $aItem['LID'] ?>">
                                                    <?= $aItem['NAME'] ?>
                                                </option>
                                            <? } ?>
                                            <? unset($aItem); ?>
                                        </select>
                                    </td>
                                </tr>
                            <? } ?>
                        </tbody>
                    </table>
                    <div class="buttons"></div>
                </td>
                <td class="right"><div class="empty"></div></td>
            </tr>
            <tr>
                <td class="left"><div class="empty"></div></td>
                <td>
                    <input type="hidden" name="member" value="isset" />
                    <input
                        type="button"
                        id="b_next"
                        onclick="javascript: MakeStep('isset');"
                        value="<?= GetMessage('CONTINUE') ?>"
                    />
                </td>
                <td class="right"><div class="empty"></div></td>
            </tr>
            <tr class="bottom">
                <td class="left">
                    <div class="empty"></div>
                </td>
                <td>
                    <div class="empty"></div>
                </td>
                <td class="right">
                    <div class="empty"></div>
                </td>
            </tr>
        </tbody>
    </table>
</div>


<? /* ========== Создание нового пользователя ========== */ ?>
<input type="hidden" name="member" value="create" />
<div class="filter-form" id="member_create" style="float: right; width: 50%;">
    <table cellspacing="0" cellpadding="0" border="0" class="filter-form" style="width: 99%;">
        <tbody>
            <tr class="top">
                <td class="left"><div class="empty"></div></td>
                <td>
                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                            <tr>
                                <td><div class="section-separator first"></div></td>
                                <td width="100%"><?= GetMessage('REGISTER_NEW_USER') ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="right"><div class="empty"></div></td>
            </tr>
            <tr>
                <td class="left"><div class="empty"></div></td>
                <td class="content">
                    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="display: table;" class="filtercontent">
                        <tbody>
                            <tr>
                                <td colspan="2"><div id="r_member_register"></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="f_add_name"><b><?= GetMessage('USER_NEW_NAME') ?>:</b></label>
                                </td>
                                <td><input type="text" value="" maxlength="255" style="width: 100%;" id="f_add_name" name="user[name]"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="delimiter"><div class="empty"></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="f_add_name2"><?= GetMessage('USER_NEW_LAST_NAME') ?>:</label>
                                </td>
                                <td><input type="text" value="" maxlength="255" style="width: 100%;" id="f_add_name2" name="user[name2]"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="delimiter"><div class="empty"></div></td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="f_add_email"><b><?= GetMessage('USER_NEW_EMAIL') ?>:</b></label>
                                </td>
                                <? $sMainEmail = COption::GetOptionString("main", "email_from", 'main@example.com'); ?>
                                <td colspan="2"><input type="text" value="" maxlength="255" style="width: 100%;" id="f_add_email" name="user[email]"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="delimiter"><div class="empty"></div></td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <div id="delivery_target"<?= (isset($aServiceDelivery[8])) ? ' style="display:none;"' : '' ?>>
                                        <table width="100%" align="left" border="0">
                                            <tr>
                                                <td><label for="f_add_phone"><?= GetMessage('USER_NEW_PHONE') ?>:</label></td>
                                                <td colspan="2"><input type="text" value="" maxlength="255" style="width: 100%;" id="f_add_phone" name="user[phone]"></td>
                                            </tr>
                                            <tr>
                                                <td><label for="f_add_country"><?= GetMessage('USER_NEW_COUNTRY') ?>:</label></td>
                                                <td colspan="2">
                                                    <? $countries = LinemediaAutoDirections::getCountriesList(); ?>
                                                    <select size="1" id="f_add_country" name="user[country]">
                                                        <option value=""><?= GetMessage('USER_NEW_UNKNOWN') ?></option>
                                                        <? foreach ($countries as $country_id => $country) { ?>
                                                            <option value="<?= $country_id ?>"><?= $country ?></option>
                                                        <? } ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="f_add_state"><?= GetMessage('USER_NEW_REGION') ?>:</label></td>
                                                <td colspan="2"><input type="text" value="" maxlength="255" style="width: 100%;" id="f_add_state" name="user[state]"></td>
                                            </tr>
                                            <tr>
                                                <td><label for="f_add_city"><?= GetMessage('USER_NEW_CITY') ?>:</label></td>
                                                <td colspan="2"><input type="text" value="" maxlength="255" style="width: 100%;" id="f_add_city" name="user[city]"></td>
                                            </tr>
                                            <tr id="tr_zip">
                                                <td><label for="f_add_zip"><?= GetMessage('USER_NEW_POSTCODE') ?>:</label></td>
                                                <td colspan="2"><input type="text" value="" maxlength="255" style="width: 100%;" id="f_add_zip" name="user[zip]"></td>
                                            </tr>
                                            <tr>
                                                <td><label for="f_add_street"><?= GetMessage('USER_NEW_ADDRESS') ?>:</label></td>
                                                <td colspan="2"><input type="text" value="" maxlength="255" style="width: 100%;" id="f_add_street" name="user[street]"></td>
                                            </tr>
                                            <? if (is_array($aSites) && count($aSites) > 1) { ?>
                                                <tr>
                                                    <td>
                                                        <label for="f_add_LID"><?= GetMessage('SALE_SITE') ?>:</label>
                                                    </td>
                                                    <td colspan="2">
                                                        <select name="f_add_LID" id="f_add_LID">
                                                        <? foreach ($aSites as $aItem) { ?>
                                                            <option value="<?= $aItem['LID'] ?>">
                                                                <?= $aItem['NAME'] ?>
                                                            </option>
                                                        <? } ?>
                                                        <? unset($aItem) ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="buttons"></div>
                </td>
                <td class="right"><div class="empty"></div></td>
            </tr>
            <tr>
                <td class="left"><div class="empty"></div></td>
                <td>
                    <input
                        type="button"
                        id="b_next"
                        onclick="javascript: MakeStep('create');"
                        value="<?= GetMessage('CONTINUE') ?>"
                    />
                </td>
                <td class="right"><div class="empty"></div></td>
            </tr>
            <tr class="bottom">
                <td class="left"><div class="empty"></div></td>
                <td><div class="empty"></div></td>
                <td class="right"><div class="empty"></div></td>
            </tr>
        </tbody>
    </table>
</div>