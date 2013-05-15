<?php
header('Content-type: application/json');
echo json_encode(array('errors' => $arResult['ERRORS']));
exit();