<?php
// -- Multibyte Compatibility functions ---------------------------------------
// http://svn.iphonewebdev.com/lace/lib/mb_compat.php

if (!function_exists('mb_internal_encoding')) {
    /**
     * @param $enc
     */
    function mbInternalEncoding($enc)
    {
        return true;
    }
}

if (!function_exists('mb_regex_encoding')) {
    /**
     * @param $enc
     */
    function mbRegexEncoding($enc)
    {
        return true;
    }
}

if (!function_exists('mb_strlen')) {
    /**
     * @param $str
     */
    function mbStrlen($str)
    {
        return strlen($str);
    }
}

if (!function_exists('mb_strpos')) {
    /**
     * @param $haystack
     * @param $needle
     * @param $offset
     */
    function mbStrpos($haystack, $needle, $offset = 0)
    {
        return strpos($haystack, $needle, $offset);
    }
}

if (!function_exists('mb_stripos')) {
    /**
     * @param $haystack
     * @param $needle
     * @param $offset
     */
    function mbStripos($haystack, $needle, $offset = 0)
    {
        return stripos($haystack, $needle, $offset);
    }
}

if (!function_exists('mb_substr')) {
    /**
     * @param $str
     * @param $start
     * @param $length
     */
    function mbSubstr($str, $start, $length = 0)
    {
        return substr($str, $start, $length);
    }
}

if (!function_exists('mb_substr_count')) {
    /**
     * @param $haystack
     * @param $needle
     */
    function mbSubstrCount($haystack, $needle)
    {
        return substr_count($haystack, $needle);
    }
}
