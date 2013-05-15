<?php

IncludeTemplateLangFile(__FILE__);

function check404FormExistence()
{
    $rsf = CForm::GetBySID('LM_AUTO_REQUEST_PART_FORM');
    $ret = (!$rsf || $rsf->SelectedRowsCount() > 0);
    if ($ret) {
        $tmp = $rsf->Fetch();
        $ret = $tmp['ID'];
    }
    return $ret;
}

function create404Form()
{

    $arFields = array(
                        'NAME'=>GetMessage('LM_AUTO_404_FORM_NAME'),
                        'SID'=>'LM_AUTO_REQUEST_PART_FORM',
                        'USE_CAPTCHA'=>'Y'
                    );
$fid = CForm::Set($arFields, false, 'N');
global $APPLICATION;
if (!$fid)
    return;

$questions = array(
                    array(
                        'SID'=>'NAME',
                        'ACTIVE'=>'Y',
                        'FIELD_TYPE'=>'text',
                        'TITLE_TYPE'=>'text',
                        'C_SORT'=>'',
                        'REQUIRED'=>'Y',
                    ),
                    array(
                        'SID'=>'PHONE',
                        'ACTIVE'=>'Y',
                        'FIELD_TYPE'=>'text',
                        'TITLE_TYPE'=>'text',
                        'C_SORT'=>'',
                        'REQUIRED'=>'Y',
                    ),
                    array(
                        'SID'=>'EMAIL',
                        'ACTIVE'=>'Y',
                        'FIELD_TYPE'=>'text',
                        'TITLE_TYPE'=>'text',
                        'C_SORT'=>'',
                        'REQUIRED'=>'Y',
                    ),
                    array(
                        'SID'=>'WHAT_FIND',
                        'ACTIVE'=>'Y',
                        'FIELD_TYPE'=>'text',
                        'TITLE_TYPE'=>'text',
                        'C_SORT'=>'',
                        'REQUIRED'=>'Y',
                    ),
                    array(
                        'SID'=>'COMMENT',
                        'ACTIVE'=>'Y',
                        'FIELD_TYPE'=>'text',
                        'TITLE_TYPE'=>'text',
                        'C_SORT'=>'',
                        'REQUIRED'=>'N',
                    )
                );
    foreach ($questions as $sort=>$item) {
        $item['TITLE'] = GetMessage('LM_AUTO_404_FIELD_'.$item['SID']);
        $item['FORM_ID'] = $fid;
        $item['C_SORT'] = $sort;
        $item['arANSWER'] = array(
                                    array(
                                         'MESSAGE'=>GetMessage('LM_AUTO_404_FIELD_'.$item['SID']),
                                        'C_SORT'=>$sort*100,
                                        'FIELD_TYPE'=>$item['FIELD_TYPE'],
                                        'ACTIVE'=>'Y',
                                    )
                            );
        CFormField::Set($item, false, 'N');
    }
    $arFields = array(
                        'FORM_ID'=>$fid,
                        'TITLE'=>GetMessage('LM_AUTO_404_NEW_STATUS'),
                        'ACTIVE'=>'Y',
                        'CSS'=>'statusred',
                        'DEFAULT_VALUE'=>'Y'
                    );
    CFormStatus::Set($arFields, false, 'N');
    $arFields = array(
                        'FORM_ID'=>$fid,
                        'TITLE'=>GetMessage('LM_AUTO_404_COMPLETED_STATUS'),
                        'ACTIVE'=>'Y',
                        'CSS'=>'statusgreen',
                        'DEFAULT_VALUE'=>'N'
                    );
    CFormStatus::Set($arFields, false, 'N');
}