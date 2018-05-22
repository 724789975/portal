<?php
namespace app\admin\controller;

use think\Controller;
use app\admin\model\PlayerModel;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function modelTest()
    {
        $player_model = new PlayerModel(3668);
        dump($player_model->getPlayerInfo());
    }
}
