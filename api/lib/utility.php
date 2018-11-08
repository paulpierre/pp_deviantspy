<?php

function isJson($string) {
    return ((is_string($string) &&
        (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
}

function current_timestamp()
{
    return date("Y/m/d H:i:s");
}

function duration($etime) {

    if ($etime < 1) {
        return 'just now';
    }

    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        24 * 60 * 60            =>  'day',
        60 * 60                 =>  'hour',
        60                      =>  'minute',
        1                       =>  'second'
    );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's' : '');
        }
    }
}


 function time_ago($ptime) {
    $etime = time() - $ptime;

    if ($etime < 1) {
        return 'just now';
    }

    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        24 * 60 * 60            =>  'day',
        60 * 60                 =>  'hour',
        60                      =>  'minute',
        1                       =>  'second'
    );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = number_format($d,1);
            return $r . ' ' . $str . ($r > 1 ? 's' : '');
        }
    }
}

 function aasort (&$array, $key, $sortType=SORT_DESC) {
    if(empty($array)) return true;
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    if($sortType == SORT_DESC)
    {$array=array_reverse($ret,true);}
}

function ds_error($error_message)
{
    if(ENABLE_LOGS) error_log($error_message);
}



