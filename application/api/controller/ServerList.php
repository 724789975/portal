<?php
namespace app\api\controller;

use app\admin\model\ServerListModel;

class ServerList
{
    public function index()
    {
        $ServerListModel = new ServerListModel();
        die(json_encode($ServerListModel->getServerListInfo()));
    }
}
