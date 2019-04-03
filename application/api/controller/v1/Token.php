<?php

namespace app\api\controller\v1;

use app\api\controller\Send;
use think\Controller;
use think\Request;

class Token extends Controller
{
    use Send;
    /**
     * 请求时间差
     */
    public static $timeDif = 10000;

    public static $accessTokenPrefix = 'accessToken_';
    public static $refreshAccessTokenPrefix = 'refreshAccessToken_';
    public static $expires = 7200;
    public static $refreshExpires = 60*60*24*30;   //刷新token过期时间

    public function token()
    {
        //参数验证
        $validate = new \app\api\validate\Token;
        if (!$validate->check(input(''))) {
            return self::returnMsg(401, $validate->getError());
        }
        self::checkParams(input(''));  //参数校验


    }
    /**
     * 参数检测
     */
    public static function checkParams($params = [])
    {
        //时间戳校验
        if(abs($params['timestamp'] - time()) > self::$timeDif){
            self::returnMsg(401,'请求时间戳与服务器时间戳异常','timestamp：'.time());
            return null;
        }

        //签名检测
        $sign = Oauth::makeSign($params,self::$appsercet);
        if($sign !== $params['sign']){
            self::returnMsg(401,'sign错误','sign：'.$sign);
            return null;
        }
    }

}
