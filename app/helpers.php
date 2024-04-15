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