<?php
namespace app\api\controller;

class ServerList
{
    public function index()
    {
        $arrServerInfo = array(
            "server_infos" => array(
                array(
                    "login_port" => 31002,
                    "login_ip" => "127.0.0.1",
                    "url_host" => "http://127.0.0.1",
                ),
                array(
                    "login_port" => 31002,
                    "login_ip" => "quchifan.wang",
                    "url_host" => "http://quchifan.wang",
                ),
            ),
        );
        die(json_encode($arrServerInfo));
    }
}
