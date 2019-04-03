<?php
namespace app\api\controller;

use think\Controller;
use think\Request;

trait Send
{

    /**
     * 返回成功
     */
    public static function returnMsg($code = 200,$message = '',$data = [],$header = [], $type = 'json')
    {
        $return['code'] = (int)$code;
        $return['message'] = $message;
        $return['data'] = $data;
        return response($return, $code, $header, $type);
    }
}

