<?php

$LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS = (int) $_POST['LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS'];
COption::SetOptionInt($sModuleId, 'LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS', $LM_AUTO_MAIN_OLD_PRICELISTS_LIFETIME_DAYS);


$LM_AUTO_MAIN_PART_SEARCH_PAGE = (string) $_POST['LM_AUTO_MAIN_PART_SEARCH_PAGE'];
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_PART_SEARCH_PAGE', $LM_AUTO_MAIN_PART_SEARCH_PAGE);

$LM_AUTO_MAIN_PART_SEARCH_BRAND_PAGE = (string) $_POST['LM_AUTO_MAIN_PART_SEARCH_BRAND_PAGE'];
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_PART_SEARCH_BRAND_PAGE', $LM_AUTO_MAIN_PART_SEARCH_BRAND_PAGE);

$LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES = serialize((array) $_POST['LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES']);
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES', $LM_AUTO_MAIN_IBLOCKS_UPDATE_PRICES);

$LM_AUTO_MAIN_SHOW_WORDFORM_PARTS = $_POST['LM_AUTO_MAIN_SHOW_WORDFORM_PARTS'] == 'Y' ? 'Y' : 'N';
COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_SHOW_WORDFORM_PARTS', $LM_AUTO_MAIN_SHOW_WORDFORM_PARTS);

$LM_AUTO_MAIN_ANALOGS_GROUPS = (array) $_POST['LM_AUTO_MAIN_ANALOGS_GROUPS'];
foreach ($LM_AUTO_MAIN_ANALOGS_GROUPS as $GROUP_ID => $TITLE) {
    COption::SetOptionString($sModuleId, 'LM_AUTO_MAIN_ANALOGS_GROUPS_'.$GROUP_ID, $TITLE);
}