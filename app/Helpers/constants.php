<?php

if (!function_exists('constant_value')) {
    function constant_value($key)
    {
        return config('constants.' . $key);
    }
}