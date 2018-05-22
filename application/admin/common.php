<?php

use think\Db;
use think\Config;
use think\cache\driver\Redis;

function get_hash_id($userOpenId)
{
    $has_result = 0;
    for($i=0; $i<strlen($userOpenId); ++$i)
    {
        $has_result += ord($userOpenId[$i]);
    }
    return $has_result % 256;
}

function get_int_hash_id($uid)
{
    return $uid % 256;
}

function get_database_cfg($userOpenId)
{
    $has_result = get_hash_id($userOpenId);
    $arr_database = Config::get("database_cfg");
    $has_result = intval($has_result) % (count($arr_database));
    return $arr_database[$has_result];
}

function get_database_cfg_by_uid($uid)
{
    $has_result = get_int_hash_id($uid);
    $arr_database = Config::get("database_cfg");
    $has_result = intval($has_result) % (count($arr_database));
    return $arr_database[$has_result];
}

?>