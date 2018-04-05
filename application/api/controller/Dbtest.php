<?php
namespace app\api\controller;

use think\Db;
use think\cache\driver\Redis;

class Dbtest
{
    public function index()
    {
        $ret = Db::query("select * from groups");
        $redis_config = include __DIR__."/../redis.php";
        $redis = new Redis($redis_config);
        $redis->set('test','hello redis');
        return 'test';
    }
}
