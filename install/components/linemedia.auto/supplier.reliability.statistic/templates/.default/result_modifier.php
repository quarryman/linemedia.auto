<?php
//нужно для подписей столбчатой диаграммы(поставки по дням)
if(!function_exists('declension')) {
    function declension($digit, $expr) {
            if(!is_array($expr)) $expr = array_filter(explode(' ', $expr));
            if(empty($expr[2])) $expr[2] = $expr[1];
            $i = preg_replace('/[^0-9]+/s', '', $digit) % 100;
            $res = $digit.' ';
            if ($i >= 5 && $i <= 20) {
                $res .= $expr[2];
            } else {
                $i %= 10;
                if ($i == 1) {
                    $res .= $expr[0];
                } elseif ($i >= 2 && $i <= 4) {
                    $res .= $expr[1];
                } else {
                    $res.= $expr[2];
                }
            }
            return trim($res);
    }
}