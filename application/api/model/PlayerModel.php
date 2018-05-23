<?php

namespace app\admin\model;

use think\Model;
use think\Db;

class PlayerModel extends Model
{
    protected $connection;
    protected $uid;
    public function __construct($uid)
    {
        $this->uid = $uid;
        $this->connection = get_database_cfg_by_uid($uid);
    }

    public function getPlayerInfo()
    {
        $ret = $this->query("SELECT * FROM users WHERE id = $this->uid");
        return $ret;
    }
}