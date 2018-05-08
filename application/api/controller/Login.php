<?php
namespace app\api\controller;

use think\Db;
use think\Config;
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
        $login_server_id = $_POST["server_id"];

        // connect_database($oauth_user["openid"]);
        $db = Db::connect(get_database_cfg($oauth_user["openid"]));
        $redis = new Redis($this->redis_config);

        $last_login_server_id = 0;

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

        $find_oauth_user = $db->table("oauth_user")->where(array("openid"=>$oauth_user['openid']))->order("id ASC")->find();
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
            $find_user = $db->table("users")->where(array("id"=>$find_oauth_user['uid']))->find();
            if(!empty($find_user))
            {
                $last_login_server_id = $find_user["login_server_id"];
                $db->table("oauth_user")->where(array("openid"=>$oauth_user['openid']))->order("id ASC")->update($oauth_user);
                $find_user["login_server_id"] = $login_server_id;
                $db->table("users")->where(array("id"=>$find_oauth_user['uid']))->update($find_user);
                $need_register = false;
            }
        }
        
        if($need_register)
        {
            $new_user_id = $redis->getHandler()->incr("current_player_id");
            $balance = 0;
            $game_coin = 0;
            $new_user_data = array(
                "id" => $new_user_id,
                "nick_name" => $oauth_user["name"],
                "create_time" => date("Y-m-d H:i:s"),
                "balance"   => $balance,
                "game_coin" => $game_coin,
                "login_server_id" => $login_server_id,
            );
            $new_user_id = $db->table("users")->insert($new_user_data, true, true);

            if($new_user_id)
            {
                //第三方用户表中创建数据
                if(empty($find_oauth_user))
                {
                    $oauth_user["user_status"] = 1;
                    $oauth_user["uid"] = $new_user_id;
                    $new_oauth_user_id = $db->table("oauth_user")->insert($oauth_user, true, true);
                    if($new_oauth_user_id)
                    {
                        $login_status = true;
                    }else
                    {
                        $db->table("users")->where(array("id"=>$new_user_id))->delete();
                        $login_status = false;
                    }
                }else
                {
                    $oauth_user["user_status"] = 1;
                    $oauth_user["uid"] = $new_user_id;
                    $db->table("oauth_user")->where(array("openid"=>$oauth_user['openid']))->order("id ASC")->update($oauth_user);
                }
            }else
            {
                $login_status = false;
            }
        }

        if($login_status)
        {
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

            $redis->getHandler()->hset($uid."_role", "login_server_id", $login_server_id);

			$s = array(
				"uid" => $uid,
				"create_time" => $create_time,
                "last_login_time" => date("Y-m-d H:i:s"),
                "last_login_ip" => request()->ip(),
            );
            $db->table("last_enter_game")->insert($s);
            
            $ret = array('code'=>200, 'token'=>$oauth_user["access_token"],
            'last_login_server_id' => $last_login_server_id, 'data'=>$data, 'descrp'=>'登录成功');
    	}else{
    		$ret = array("code"=>500,"descrp"=>"登录失败");
		}
		\Think\Log::record("sendOauthUserInfo ret : " .json_encode($ret),'INFO');
    	die(json_encode($ret));
    }
}
