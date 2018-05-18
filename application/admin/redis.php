<?php
/*
*命名
当前最大玩家id current_player_id
玩家信息 hset uid_role
服务器配置 hset game_config_类型
*/
return [
    'host'       => '127.0.0.1',
    'port'       => 16379,
    'password'   => '1',
    'select'     => 0,
    'timeout'    => 0,
    'expire'     => 0,
    'persistent' => true,
    'prefix'     => '',
];
?>
