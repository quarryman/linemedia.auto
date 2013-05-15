<?php
IncludeModuleLangFile(__FILE__);
$LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES 			= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES', 'Y');
$LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL 	= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL', 'Y');
$LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES 			= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES', 'Y');
$LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES 			= COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES', 'Y');
?>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES">
            <?= GetMessage('LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES" id="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES" value="Y" <?=$LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>

<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL">
            <?= GetMessage('LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL" id="LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL" value="Y" <?=$LM_AUTO_MAIN_SEARCH_TECDOC_CROSSES_ORIGINAL == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>


<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES">
            <?= GetMessage('LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES" id="LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES" value="Y" <?=$LM_AUTO_MAIN_SEARCH_LINEMEDIA_CROSSES == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>


<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES">
            <?= GetMessage('LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES" id="LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES" value="Y" <?=$LM_AUTO_MAIN_SEARCH_SIMPLE_CROSSES == 'Y' ? 'checked="checked"' : ''?> />
    </td>
</tr>
