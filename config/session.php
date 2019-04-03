<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

$tmp = function ($url) {
    $count = preg_match('/http:\/\/(\S*):(\d+)$/', $url, $output);
    if ($count) {
        return $output[1];
    } else {
        return '';
    }
};

return [
    'id' => '',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // SESSION 前缀
    'prefix' => 'think',
    // 驱动方式 支持redis memcache memcached
    'type' => '',
    'domain' => array_key_exists('HTTP_ORIGIN', $_SERVER) ? $tmp($_SERVER['HTTP_ORIGIN']) : '',
    //'domain' => 'dev.local',
    // 'httponly'  => false,
    // 是否自动开启 SESSION
    'auto_start' => true,
];
