<?php

namespace app\admin\model;

use think\Model;
use think\Db;

class PlayerModel extends Model
{
    protected $connection;
    protected $openId;
    public function __construct($openId)
    {
        $this->openId = $openId;
        $this->connection = get_database_cfg($openId);
    }

    public function getOauthInfo()
    {
        $ret = $this->query("SELECT * FROM oauth_user WHERE openid = $this->uid");
        return $ret;
    }
}