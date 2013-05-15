<tr>
    <td valign="top" width="50%">
        <label for="LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE">
            <?= GetMessage('LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE') ?>:
        </label>
        <br /><img src="/bitrix/images/main/mouse.gif" width="44" height="21" border="0" alt="" />
    </td>
    <td valign="top" width="50%">
        <?  
        // Печатные шаблоны.
        $arSysLangs = array();
        $db_lang = CLangAdmin::GetList(($b='sort'), ($o='asc'), array('ACTIVE' => 'Y'));
        while ($arLang = $db_lang->Fetch()) {
            $arSysLangs[] = $arLang['LID'];
        }
        unset($arLang, $db_lang);

        $aPrintTemplate = array();
        if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/reports/')) {
            while (($file = readdir($handle)) !== false) {
                if ($file == "." || $file == "..") {
                    continue;
                }

                if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$file) && strtoupper(substr($file, strlen($file)-4))==".PHP"){
                    $rep_title = $file;
                    $file_handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$file, "rb");
                    $file_contents = fread($file_handle, 1500);
                    fclose($file_handle);

                    $rep_langs = "";
                    $arMatches = array();
                    if (preg_match("#<title([\s]+langs[\s]*=[\s]*\"([^\"]*)\"|)[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches)){
                        $arMatches[3] = Trim($arMatches[3]);
                        if (strlen($arMatches[3]) > 0) $rep_title = $arMatches[3];
                        $arMatches[2] = Trim($arMatches[2]);
                        if (strlen($arMatches[2]) > 0) $rep_langs = $arMatches[2];
                    }
                    unset($file_contents);

                    if (strlen($rep_langs)>0){
                        $bContinue = True;
                        for ($ic = 0; $ic < count($arSysLangs); $ic++) {
                            if (strpos($rep_langs, $arSysLangs[$ic]) !== false) {
                                $bContinue = False;
                                break;
                            }
                        }
                        if ($bContinue){
                            continue;
                        }
                    }
                    
                    $aPrintTemplate[] = array(
                            "PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$file,
                            "FILE" => $file,
                            "TITLE" => $rep_title
                        );
                }
            }
        }
        closedir($handle);

        if ($handle = opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/")){
            while (($file = readdir($handle)) !== false){
                if ($file == "." || $file == ".."){
                    continue;
                }

                if (
                    is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/".$file)
                    && !in_array($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/".$file, $aPrintTemplate)
                    && strtoupper(substr($file, strlen($file) - 4)) == ".PHP"
                ) {
                    $rep_title = $file;
                    if (
                        is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/reports/".$file)
                        && strtoupper(substr($file, strlen($file) - 4)) == ".PHP"
                    ) {
                        $file_handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/reports/".$file, "rb");
                        $file_contents = fread($file_handle, 1500);
                        fclose($file_handle);
                    } else {
                        $file_handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/".$file, "rb");
                        $file_contents = fread($file_handle, 1500);
                        fclose($file_handle);
                    }

                    $rep_langs = "";
                    $arMatches = array();
                    if (preg_match("#<title([\s]+langs[\s]*=[\s]*\"([^\"]*)\"|)[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches)){
                        $arMatches[3] = Trim($arMatches[3]);
                        if (strlen($arMatches[3])>0) $rep_title = $arMatches[3];
                        $arMatches[2] = Trim($arMatches[2]);
                        if (strlen($arMatches[2])>0) $rep_langs = $arMatches[2];
                    }
                    unset($file_contents);

                    if (strlen($rep_langs) > 0) {
                        $bContinue = True;
                        for ($ic = 0; $ic < count($arSysLangs); $ic++) {
                            if (strpos($rep_langs, $arSysLangs[$ic]) !== false) {
                                $bContinue = False;
                                break;
                            }
                        }
                        if ($bContinue) {
                            continue;
                        }
                    }

                    $aPrintTemplate[] = array(
                            "PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/".$file,
                            "FILE" => $file,
                            "TITLE" => $rep_title
                        );
                }
            }
        }
        unset($arSysLangs);
        closedir($handle);

        if (count($aPrintTemplate) > 0) {
            $aOptionValue['LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE'] = unserialize(COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE'));
        ?>
            <select style="width: 100%;" multiple="multiple" id="LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE" name="LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE[]">
            <? foreach ($aPrintTemplate as $aTemplateItem) { ?>
                <option value="<?= $aTemplateItem['FILE'] ?>" <? if (in_array($aTemplateItem['FILE'], $aOptionValue['LM_AUTO_MAIN_PRINT_TEMPLATE_DISABLE'])) { ?> selected="selected"<? } ?>>
                    <?= $aTemplateItem['TITLE'] ?>
                </option>
            <? } ?>
            <? unset($aTemplateItem, $aPrintTemplate); ?>
            </select>
        <? } else { ?>
            Нет шаблонов
        <? } ?>
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_DEFERRED_PAYMENT">
            <?= GetMessage('LM_AUTO_MAIN_DEFERRED_PAYMENT') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_DEFERRED_PAYMENT" id="LM_AUTO_MAIN_DEFERRED_PAYMENT" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_DEFERRED_PAYMENT', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <label for="LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING">
            <?= GetMessage('LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING" id="LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_DECREASE_QUANTITY_PRODUCT_ORDERING', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>
<tr>
    <td width="50%" valign="top">
        <span id="LM_AUTO_MAIN_GROUP_TRANSFER_BACK_HINT"></span>
        <script>BX.hint_replace(BX('LM_AUTO_MAIN_GROUP_TRANSFER_BACK_HINT'), '<?= GetMessage('LM_AUTO_MAIN_GROUP_TRANSFER_BACK_HINT') ?>');</script>
        <label for="LM_AUTO_MAIN_GROUP_TRANSFER_BACK">
            <?= GetMessage('LM_AUTO_MAIN_GROUP_TRANSFER_BACK') ?>:
        </label>
    </td>
    <td valign="top">
        <input type="checkbox" name="LM_AUTO_MAIN_GROUP_TRANSFER_BACK" id="LM_AUTO_MAIN_GROUP_TRANSFER_BACK" value="Y" <?= (COption::GetOptionString($sModuleId, 'LM_AUTO_MAIN_GROUP_TRANSFER_BACK', 'N') == 'Y') ? ('checked="checked"') : ('') ?>" />
    </td>
</tr>


