<? IncludeModuleLangFile(__FILE__); ?>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_COMMON_SETTINGS_PATH_IMPORT_TITLE') ?></td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS">
            <?= GetMessage('LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS') ?>
        </label>
    </td>
    <td>
        <input type="text" name="LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS" id="LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS" size="3" value="<?= COption::GetOptionInt('linemedia.auto', 'LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS', 14) ?>" />
        <?= GetMessage('LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS_D') ?>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES">
            <?= GetMessage('LM_AUTO_MAIN_COMMON_SETTINGS_PATH_IBLOCKS_UPDATE_PRICES') ?>
            <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
            <br />(<?= GetMessage('LM_AUTO_MAIN_COMMON_SETTINGS_PATH_USER_GROUP_PRICE_UPDATE') ?>)
        </label>
    </td>
    <td valign="top" width="50%">
        <?
        $iblocks = array();
        CModule::IncludeModule("catalog");
        $rsIblocks = CCatalog::GetList(array(), array(), false, false, array("IBLOCK_ID", "NAME"));
        while($iblock = $rsIblocks -> Fetch()) {
            $iblocks[] = $iblock;
        }
        if (!empty($iblocks)) { ?>
        <? $LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES')); ?>
        <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES" name="LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES[]">
            <? foreach ($iblocks as $iblock) { ?>
            <option value="<?= $iblock['IBLOCK_ID'] ?>" <?if(in_array($iblock['IBLOCK_ID'], $LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES)) { ?> selected="selected"<? } ?>>
                [<?= $iblock['IBLOCK_ID'] ?>] <?= $iblock['NAME'] ?>
            </option>
            <? } ?>
        </select>
        <? } else { ?>
        <?= GetMessage('LM_AUTO_MAIN_COMMON_SETTINGS_PATH_NO_IBLOCKS_UPDATE_PRICES') ?>
        <? } ?>
    </td>
</tr>
<?

$u = new CAdminPopup(
    "mnu_PART_DETAIL_PAGE_URL",
    "mnu_PART_DETAIL_PAGE_URL",
    array(
        array(
            'TEXT' => "Запчасть",
            'ONCLICK' => "__SetUrlVar('#PART_ID#', 'mnu_PART_DETAIL_PAGE_URL', 'PART_DETAIL_PAGE_URL')",
            'TITLE' => "#PART_ID# - Запчасть"
        ),
        array(
            'TEXT' => "Бренд",
            'ONCLICK' => "__SetUrlVar('#BRAND_ID#', 'mnu_PART_DETAIL_PAGE_URL', 'PART_DETAIL_PAGE_URL')",
            'TITLE' => "#BRAND_ID# - Бренд"
        ),
        array(
            'TEXT' => "Артикул",
            'ONCLICK' => "__SetUrlVar('#ARTICLE#', 'mnu_PART_DETAIL_PAGE_URL', 'PART_DETAIL_PAGE_URL')",
            'TITLE' => "#ARTICLE# - Артикул"
        ),
        array(
            'TEXT' => "Поставщик",
            'ONCLICK' => "__SetUrlVar('#SUPPLIER_ID#', 'mnu_PART_DETAIL_PAGE_URL', 'PART_DETAIL_PAGE_URL')",
            'TITLE' => "#SUPPLIER_ID# - Поставщик"
        ),
    ),
    array("zIndex" => 2000)
);
$u->Show();

$u = new CAdminPopup(
    "mnu_PART_SEARCH_URL",
    "mnu_PART_SEARCH_URL",
    array(
        array(
            'TEXT' => "Запчасть",
            'ONCLICK' => "__SetUrlVar('#PART_ID#', 'mnu_PART_SEARCH_URL', 'PART_SEARCH_URL')",
            'TITLE' => "#PART_ID# - Запчасть"
        ),
        array(
            'TEXT' => "Бренд",
            'ONCLICK' => "__SetUrlVar('#BRAND_ID#', 'mnu_PART_SEARCH_URL', 'PART_SEARCH_URL')",
            'TITLE' => "#BRAND_ID# - Бренд"
        ),
        array(
            'TEXT' => "Артикул",
            'ONCLICK' => "__SetUrlVar('#ARTICLE#', 'mnu_PART_SEARCH_URL', 'PART_SEARCH_URL')",
            'TITLE' => "#ARTICLE# - Артикул"
        ),
    ),
    array("zIndex" => 2000)
);
$u->Show();

?>
<script type="text/javascript">
    function __SetUrlVar(id, mnu_id, el_id)
    {
        var mnu_list = eval(mnu_id);
        var obj_ta = document.getElementById(el_id);
        obj_ta.focus();
        obj_ta.value += id;

        mnu_list.PopupHide();
        BX.fireEvent(obj_ta, 'change');
        obj_ta.focus();
    }
    
    function __ShUrlVars(div, el_id)
    {
        var pos = jsUtils.GetRealPos(div);
        var mnu_list = eval('mnu_'+el_id);
        setTimeout(function(){mnu_list.PopupShow(pos); }, 10);
    }
</script>

<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_COMMON_SETTINGS_VIEW_TITLE') ?></td>
</tr>
<tr>
    <td width="50%" valign="top">
        <span id="LM_AUTO_MAIN_SHOW_WORDFORM_PARTS_HINT"></span>
        <script>BX.hint_replace(BX('LM_AUTO_MAIN_SHOW_WORDFORM_PARTS_HINT'), '<?= GetMessage('LM_AUTO_MAIN_SHOW_WORDFORM_PARTS_HINT') ?>');</script>
        <label for="LM_AUTO_MAIN_SHOW_WORDFORM_PARTS">
            <?= GetMessage('LM_AUTO_MAIN_SHOW_WORDFORM_PARTS') ?>:
        </label>
    </td>
    <td valign="top">
        <? $LM_AUTO_MAIN_SHOW_WORDFORM_PARTS = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_SHOW_WORDFORM_PARTS', 'N'); ?>
        <input type="checkbox" name="LM_AUTO_MAIN_SHOW_WORDFORM_PARTS" id="LM_AUTO_MAIN_SHOW_WORDFORM_PARTS" value="Y" <?= $LM_AUTO_MAIN_SHOW_WORDFORM_PARTS == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>


<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_COMMON_SETTINGS_PATH_GROUP_TITLE') ?></td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_PART_DETAIL_PAGE">
            <?= GetMessage('LM_AUTO_MAIN_PART_DETAIL_PAGE') ?>:
        </label>
    </td>
</tr>
<tr>
    <td width="50%">
        <label for="LM_AUTO_MAIN_PART_SEARCH_PAGE">
            <?= GetMessage('LM_AUTO_MAIN_PART_SEARCH_PAGE') ?>:
        </label>
    </td>
    <td>
        <input type="text" name="LM_AUTO_MAIN_PART_SEARCH_PAGE" id="PART_SEARCH_URL" size="40" maxlength="255" value="<?= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_PART_SEARCH_PAGE', '') ?>" />
        <input type="button" onclick="__ShUrlVars(this, 'PART_SEARCH_URL')" value='...' />
    </td>
</tr>
<tr class="heading">
    <td colspan="2"><?= GetMessage('LM_AUTO_MAIN_COMMON_SETTINGS_ANALOGS_GROUP_TITLE') ?></td>
</tr>
<? $analogs = array_keys(LinemediaAutoPart::getAnalogGroups()) ?>
<? foreach ($analogs as $id) { ?>
    <tr>
        <td width="50%">
            <label for="'LM_AUTO_ANALOG_GROUP_<?= $id ?>">
                <?= GetMessage('LM_AUTO_ANALOG_GROUP_'.$id) ?>:
            </label>
        </td>
        <td>
            <input type="text" name="LM_AUTO_MAIN_ANALOGS_GROUPS[<?= $id ?>]" id="LM_AUTO_MAIN_ANALOGS_GROUPS_<?= $id ?>" size="40" maxlength="255" value="<?= COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_ANALOGS_GROUPS_'.$id, GetMessage('LM_AUTO_ANALOG_GROUP_'.$id)) ?>" />
        </td>
    </tr>
<? } ?>