<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* String Helpers Class
*
* Extends String Helpers
*
*/

function app_name()
{
    return get_instance()->config->item('app_name');
}

function starts_with($haystack, $needle)
{
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function ends_with($haystack, $needle)
{
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function s3_key_name($key, $prefix = '')
{
    $name = trim($key, '/');
    
    if ($name <= $prefix AND !empty($name))
    {
        $name_list = explode('/', $name);
        if (count($name_list)) $name = end($name_list);
    }
    else
    {
        if (starts_with($name, $prefix)) $name = substr($name, strlen($prefix));
    }

    return $name;
}

function time_ago($time)
{
    $time = time() - $time; // to get the time since that moment
    $time = ($time<1)? 1 : $time;
    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
    }
}