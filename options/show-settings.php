<?
if (!function_exists('GetMenuTreeHtml')) {
    // Функция построения меню с рекурсией
    function GetMenuTreeHtml($a_items = array(), $i = 0, $a_parent_id = array(), $i_level = 0)
    {
        global $aOptionValue;
        
        if (is_array($a_items) && count($a_items) > 0) {
            foreach ($a_items as $i_key => $a_item) {
                $i++;
                $s_input_key = ((count($a_parent_id) > 0) ? implode('_', $a_parent_id) . '_' : '') . $i_key;
                $s_input_id = 'LM_AUTO_MAIN_MENU_HIDE_STORE_' . $s_input_key;
                $s_input_name = 'LM_AUTO_MAIN_MENU_HIDE[STORE][' . $s_input_key . ']';
            ?>
            <div><input type="checkbox" name="<?= $s_input_name; ?>" id="<?= $s_input_id; ?>" value="Y" <?= (isset($aOptionValue['LM_AUTO_MAIN_MENU_HIDE']['STORE'][$s_input_key])) ? ' checked="checked"' : '' ?>/>
            <?
            if ($i_level > 0) {
                echo str_repeat('..', $i_level);
            }
            ?>
            <label for="<?= $s_input_id ?>"><?= $a_item['text'] ?></label></div>
            <?
                if (isset($a_item['items']) && is_array($a_item['items']) && count($a_item['items']) > 0) {
                    $a_parent_id_sum = $a_parent_id;
                    $a_parent_id_sum[] = $i_key;
                    GetMenuTreeHtml($a_item['items'], $i, $a_parent_id_sum, $i_level+1);
                    unset($a_parent_id_sum);
                }
            }
            unset($a_items, $a_item);
        }
    }
}
?>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK">
            <?= GetMessage('LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK" id="LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_LOCAL_SHOW_ONLY_IN_STOCK', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE">
            <?= GetMessage('LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE" id="LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>

<? /* if (file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT.'/modules/sale/admin/menu.php') && is_readable($_SERVER["DOCUMENT_ROOT"].BX_ROOT.'/modules/sale/admin/menu.php')) { ?>
    <? require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT.'/modules/sale/admin/menu.php'); ?>
    <? $aOptionValue['LM_AUTO_MAIN_MENU_HIDE'] = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_MENU_HIDE', array())) ?>
    
    <tr id="LM_AUTO_MAIN_MENU_HIDE_STORE_TR" <? if (isset($aOptionValue['LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE']) && $aOptionValue['LM_AUTO_MAIN_GLOBAL_MENU_HIDE_STORE'] == 'Y') { ?> style="display: none;"<? } ?>>
        <td valign="top" width="50%">
            <?= GetMessage('LM_AUTO_MAIN_MENU_HIDE') ?>:
        </td>
        <td valign="top" width="50%">
            <? GetMenuTreeHtml($aMenu) ?>
        </td>
    </tr>
<? } */ ?>