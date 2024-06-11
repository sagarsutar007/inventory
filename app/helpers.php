<?php

if (!function_exists('formatQuantity')) {
    function formatQuantity($number)
    {
        return number_format($number, 3);
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($number)
    {
        return number_format($number, 2);
    }
}

if (!function_exists('floatsAreEqual')) {
    function floatsAreEqual($a, $b, $epsilon = 0.0001) {
        return abs($a - $b) < $epsilon;
    }
}

if (!function_exists('subtractAndNormalize')) {
    function subtractAndNormalize($a, $b, $epsilon = 1.0E-13) {
        $result = $a - $b;
        return abs($result) < $epsilon ? 0 : $result;
    }
}