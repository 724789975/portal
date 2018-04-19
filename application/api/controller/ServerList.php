<?php
namespace app\api\controller;

use think\Db;

class ServerList
{
    public function index()
    {
		$server_list = Db::query('SELECT * FROM server_list;');
        $arrServerInfo = array(
            "server_infos" => $server_list,
        );
        die(json_encode($arrServerInfo));
    }
}
