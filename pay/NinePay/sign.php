<?php

function ninepay_sign(array $params, string $md5Key)
{
    ksort($params); // ASCII sort

    $str = '';
    foreach ($params as $k => $v) {
        if ($v !== '' && $v !== null) {
            $str .= $k . '=' . $v . '&';
        }
    }

    $str = rtrim($str, '&');
    $str .= $md5Key;

    return strtoupper(md5($str));
}