<?php
namespace app\api\controller;

use think\Db;
class Dbtest
{
    public function index()
    {
        print_r(Db::query("select * from groups"));
        return 'test';
    }
}
