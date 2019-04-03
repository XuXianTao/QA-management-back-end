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

use think\facade\Route;

Route::allowCrossDomain(true, [
    'Access-Control-Allow-Origin' => array_key_exists('HTTP_ORIGIN', $_SERVER)?$_SERVER['HTTP_ORIGIN']:'*',
    'Access-Control-Allow-Credentials' => 'true',
    'Access-Control-Allow-Headers' => 'Authorization, Content-Type, Origin, Cookies, Accept, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With'
]);

Route::post(':version/token', 'api/:version.token/token');

Route::resource(':version/user', 'api/:version.user');

Route::post(':version/user/login', 'api/:version.user/login');

Route::post(':version/user/logout', 'api/:version.user/logout');

Route::get(':version/user/check', 'api/:version.user/check');

Route::resource(':version/submission', 'api/:version.submission');

Route::resource(':version/subject', 'api/:version.subject');

Route::resource(':version/collection', 'api/:version.collection');

Route::resource(':version/question', 'api/:version.question');

Route::post(':version/collection/import', 'api/:version.collection/import');

Route::get(':version/collection/output', 'api/:version.collection/output');

Route::post(':version/submission/add_to_data', 'api/:version.submission/addToData');

Route::resource(':version/answer', 'api/:version.answer');

Route::get(':version/subject/for_user', 'api/:version.subject/forUser');

Route::get('weixin/auto_msg', 'weixin/AutoMsg/test');
Route::post('weixin/auto_msg', 'weixin/AutoMsg/main');

Route::post(':version/nlp/datatrain', 'api/:version.Nlp/dataTrain');
Route::post(':version/nlp/getanswer', 'api/:version.Nlp/getAnswer');

return [

];
