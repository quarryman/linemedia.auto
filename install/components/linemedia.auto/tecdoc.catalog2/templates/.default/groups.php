<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);?>

<script type="text/javascript">
    var langs = {'LM_AUTO_EDIT_MODE': '<?= GetMessage('LM_AUTO_EDIT_MODE') ?>', 'LM_AUTO_SAVE': '<?= GetMessage('LM_AUTO_SAVE') ?>'};
</script>
<?
$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.cookie.js');
$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.treeview.js');
$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.form.js');

if (!function_exists('lm_recPrintTree')) {
    function lm_recPrintTree($parent_id, &$arResult, $arParams, $folder)
    {
        $out = '<ul>';
        if ($parent_id == 0) {
            $out = '<ul id="lm-auto-tecdoc-catalog-groups">';
        }
        foreach ($arResult['GROUPS'] as $i => $group) {
            if ($group['parentNodeId'] != $parent_id) {
                continue;
            }
            if ($arResult['EDIT_MODE'] == false && $group['hidden'] == 'Y') {
                continue;
            }
            $out .= '<li>';

            /*
             * Режим правки
             */
            if ($arResult['EDIT_MODE']) {
                if ($group['lm_mod_id']) {
                    $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . $group['source_id'] . ']" value="Y" ' . ($group['hidden'] != 'Y' ? 'checked':'') . ' />';
                    $out .= '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . $group['assemblyGroupNodeId'] . '" data-mod-id="' . $group['id'] . '"><img src="' . $folder . '/images/edit.png" alt="" /></a>';
                    $out .= '<a href="javascript:void(0);" class="tecdoc-item-delete" data-id="' . $group['id'] . '"><img src="' . $folder . '/images/delete.png" alt="'.GetMessage('LM_AUTO_DELETE').'" /></a>';
                } else {
                    $out .= '<input type="checkbox" name="' . $arResult['type'] . '[' . $group['assemblyGroupNodeId'] . ']" value="Y" ' . ($group['hidden'] != 'Y' ? 'checked':'') . ' />';
                    $out .= '<a href="javascript:void(0);" class="tecdoc-item-edit" data-id="' . $group['assemblyGroupNodeId'] . '"><img src="' . $folder . '/images/edit.png" alt="" /></a>';
                }
            }

            //$out .=  '<a href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $additional_url . $arResult['model_id']. '/' . $arResult['modification_id'] . '/' . $group['assemblyGroupNodeId'] . '/">' . $group['assemblyGroupName'] . '</a>';
            //$out .= $group['assemblyGroupName'];

            if ($group['hasChilds']) {
                $out .=  '<span>' . $group['assemblyGroupName'] . '</span>';
                if ($arResult['EDIT_MODE']) {
                    $out .=  '<a href="javascript:;" class="tecdoc-item-add-child" data-id="' . $group['assemblyGroupNodeId'] . '"><img src="' . $folder . '/images/add_child.png" alt="" /></a>';
                }
                $out .= lm_recPrintTree($group['assemblyGroupNodeId'], $arResult, $arParams, $folder);
            } else { 
                $out .=  '<a href="' . $arParams['SEF_FOLDER'] . $arResult['brand_id'] . '/' . $arResult['model_id']. '/' . $arResult['modification_id'] . '/' . $group['assemblyGroupNodeId'] . '/">' . $group['assemblyGroupName'] . '</a>';
            }
            $out .= '</li>';
        }

        $out .= '</ul>';

        return $out;
    }
}

echo lm_recPrintTree(false, $arResult, $arParams, $this->GetFolder());

echo $out;
?>


<? include(dirname(__FILE__) . '/footer.php'); ?>