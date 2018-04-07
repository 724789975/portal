<?php
namespace app\api\controller;

use think\Db;
use think\cache\driver\Redis;

class Login
{
    private $redis_config;
    public function __construct()
    {
        $this->redis_config = include __DIR__."/../redis.php";
    }
    public function index()
    {
        return 'login';
    }

    public function sendOauthUserInfo()
    {
        \Think\Log::record("sendOauthUserInfo request : " . json_encode($_REQUEST, true),'INFO');
        $oauth_user["platform"] = $_POST["platform"];
        $oauth_user["name"] = $_POST["name"];
        $oauth_user["head_img"] = $_POST["head_img"];
        $oauth_user["access_token"] = $_POST["access_token"];
        $oauth_user["expires_date"] = $_POST["expires_date"];
        $oauth_user["openid"] = $_POST["openid"];

        if (isset($_POST["sex"]))
        {
            $oauth_user['sex'] = $_POST["sex"];
        }
        if (isset($_POST["mobile"]))
        {
            $oauth_user['mobile'] = $_POST["mobile"];
        }
        if (isset($_POST["location"]))
        {
            $oauth_user['location'] = $_POST["location"];
        }
        if (isset($_POST["birthday"]))
        {
            $oauth_user['birthday'] = $_POST["birthday"];
        }
        if (isset($_POST["user_email"]))
        {
            $oauth_user['user_email'] = $_POST["user_email"];
        }

        $rules = array
        (
            array('platform', 'require', '来源不能为空', 1 ),
            array('name', 'require', '名称不能为空', 1 ),
            array('head_img', 'require', '头像不能为空！', 1 ),
            array('access_token','require','access_token不能为空！',1),
            array('expires_date','require','过期时长不能为空！',1),
            array('openid','require','openid不能为空！',1),
        );
        // if($oauth_user_model->validate($rules)->create() === false)
        // {
        //     $ret = array("code"=>501,"descrp"=>$oauth_user_model->getError());
        //     die(json_encode($ret));
        // }

        $find_oauth_user = Db::table("oauth_user")->where(array("openid"=>$oauth_user['openid']))->order("id ASC")->find();
        $need_register = true;
        $login_status = true;
        $user_data = array();
        if(!empty($find_oauth_user))
        {
            if($find_oauth_user['user_status'] == '0')
            {
                $ret = array("code"=>502,"descrp"=>"您可能已经被列入黑名单，请联系管理员！");
                die(json_encode($ret));
            }
            $find_user = Db::table("users")->where(array("id"=>$find_oauth_user['uid']))->find();
            if(!empty($find_user))
            {
                Db::table("oauth_user")->where(array("openid"=>$oauth_user['openid']))->order("id ASC")->update($oauth_user);
                $need_register = false;
            }
        }
        
        if($need_register)
        {
            $balance = 0;
            $game_coin = 0;
            $new_user_data = array(
                "nick_name" => $oauth_user["name"],
                "create_time" => date("Y-m-d H:i:s"),
                "balance"   => $balance,
                "game_coin" => $game_coin,
            );
            $new_user_id = Db::table("users")->insert($new_user_data, true, true);

            if($new_user_id)
            {
                //第三方用户表中创建数据
                if(empty($find_oauth_user))
                {
                    $oauth_user["user_status"] = 1;
                    $oauth_user["uid"] = $new_user_id;
                    $new_oauth_user_id = Db::table("oauth_user")->insert($oauth_user, true, true);
                    if($new_oauth_user_id)
                    {
                        $login_status = true;
                    }else
                    {
                        Db::table("users")->where(array("id"=>$new_user_id))->delete();
                        $login_status = false;
                    }
                }else
                {
                    $oauth_user["user_status"] = 1;
                    $oauth_user["uid"] = $new_user_id;
                    Db::table("oauth_user")->where(array("openid"=>$oauth_user['openid']))->order("id ASC")->update($oauth_user);
                }
            }else
            {
                $login_status = false;
            }
        }

        if($login_status)
        {
            $redis = new Redis($this->redis_config);
            $redis->getHandler()->set('test','hello redis');
            if($need_register)
            {
    			$data = array(
	    			"id" => $new_user_id,
	    			"nick_name" => $oauth_user["name"],
	    			"avatar" => $oauth_user["head_img"],
	    			"sex" => $oauth_user["sex"],
	    			"balance" => $new_user_data["balance"],
                );
                $create_time = date("Y-m-d H:i:s"); 
                $uid = $new_user_id;

                foreach ($oauth_user as $key => $value)
                {
                    $redis->getHandler()->hset("$new_user_id"."_role", $key, $value);
                }
                foreach ($new_user_data as $key => $value)
                {
                    $redis->getHandler()->hset("$new_user_id"."_role", $key, $value);
                }
            }else
            {
    			$data = array(
	    			"id" => $find_oauth_user["uid"],
	    			"nick_name" => $find_user["nick_name"],
	    			"avatar" => $find_oauth_user['head_img'],
	    			"sex" => $find_oauth_user["sex"],
	    			"balance" => $find_user["balance"],
	    		);
                $create_time = $find_user["create_time"]; 
                $uid = $find_oauth_user["uid"];
                foreach ($find_oauth_user as $key => $value)
                {
                    $redis->getHandler()->hset($find_oauth_user["uid"]."_role", $key, $value);
                }
                foreach ($find_user as $key => $value)
                {
                    $redis->getHandler()->hset($find_oauth_user["uid"]."_role", $key, $value);
                }
            }

			$s = array(
				"uid" => $uid,
				"create_time" => $create_time,
                "last_login_time" => date("Y-m-d H:i:s"),
                "last_login_ip" => request()->ip(),
            );
            Db::table("last_enter_game")->insert($s);
            
    		$ret = array('code'=>200, 'token'=>$oauth_user["access_token"], 'data'=>$data, 'descrp'=>'登录成功');
    	}else{
    		$ret = array("code"=>500,"descrp"=>"登录失败");
		}
		\Think\Log::record("sendOauthUserInfo ret : " .json_encode($ret),'INFO');
    	die(json_encode($ret));
    }
}
