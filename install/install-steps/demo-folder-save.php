<?php

/*
 * ���� ��� ���������.
 */
$path = strval($_POST['DEMO_FOLDER_PATH']);
$path = str_replace('..', '', $path);

/*
 * �������� ������ � ������.
 */
$_SESSION['linemedia_auto_module_install_settings']['demo_folder'] = array(
    'install' => $_POST['DEMO_FOLDER_INSTALL'] == 'Y',
    'rewrite' => $_POST['DEMO_FOLDER_REWRITE'] == 'Y',
    'path'    => $path
);
