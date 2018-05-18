<?php
namespace app\admin\controller;

use think\Db;
use think\Config;
use think\cache\driver\Redis;

class Config2Redis
{
    private $redis_config;
    public function __construct()
    {
        $this->redis_config = include __DIR__."/../redis.php";
    }
    public function index()
    {
        return 'admin';
    }

    public function refresh2Redis()
    {
        $game_type_config = Db::query("SELECT * FROM game_type_config");
        $redis = new Redis($this->redis_config);

        foreach ($game_type_config as $val)
        {
            $game_type = $val["game_type"];
            foreach ($val as $key => $value)
            {
                $redis->getHandler()->hset("game_config_$game_type", $key, $value);
            }
        }
        die("OK!!!");
    }
}
?>