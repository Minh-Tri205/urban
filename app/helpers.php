<?php

if (! function_exists('n0')) {
    /** Tương đương ToString("N0") theo định dạng Việt Nam: 1.234.567 */
    function n0(int|float|string|null $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }
}

if (! function_exists('vnd')) {
    /** Định dạng tiền: 199.000đ */
    function vnd(int|float|string|null $value): string
    {
        return n0($value) . 'đ';
    }
}
