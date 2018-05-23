<?php

namespace app\admin\model;

use think\Model;

class ServerListModel extends Model
{
    public function getServerListInfo()
    {
        $ret = $this->query("SELECT * FROM server_list");
        return $ret;
    }
}



?>


