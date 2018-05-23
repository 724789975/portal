<?php
namespace app\api\controller;

use app\admin\model\ServerListModel;

class ServerList
{
    public function index()
    {
        $server_list_model = new ServerListModel();
        $server_info = array(
            "server_infos" => $server_list_model->getServerListInfo(),
        );
        die(json_encode($server_info));
    }
}
