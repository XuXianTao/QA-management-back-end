<?php

namespace app\http\middleware;

use think\Request;
use think\Response;

class CheckAuth
{
    const version = 'v1';
    const MSG_NOT_LOG = 'You are not logged in.';
    const MSG_NOT_AUTH_SUBJECT = 'You have no auth to operate the subject.';
    const MSG_NOT_AUTH_ADMIN = 'Require admin auth.';
    protected $exclude = [
        [
            'url' => '/' . self::version . '/user/login',
            'method' => 'POST'
        ],
        [
            'url' => '/' . self::version . '/user',
            'method' => 'POST'
        ],
        [
            'url' => '/' . self::version . '/subject',
            'method' => 'GET'
        ],
        [
            'url' => '/' . self::version . '/user/check',
            'method' => 'GET'
        ],
        [
            'url' => '/' . self::version . '/submission',
            'method' => 'POST'
        ],
        [
            'url' => '/' . self::version . '/nlp/getanswer',
            'method' => 'POST'
        ]
    ];
    protected $auth_admin = [
        [
            'url' => '/' . self::version . '/user',
            'method' => 'GET'
        ],
        [
            'url' => '/' . self::version . '/user',
            'method' => 'DELETE'
        ]
    ];
    public function handle(Request $request, \Closure $next)
    {
        // 前置行为
        $req = [
            'url' => '/' . $request->path(),
            'method' => $request->method()
        ];
        if (!preg_match('/^\/' . self::version . '/',$req['url'])) {
            return $next($request);
        }

        if ($req['method'] === 'OPTIONS') return $next($request);
        return in_array($req, $this->exclude)?$next($request)
            :self::checkLogged($req)?
                :self::checkAdmin($req, $request)?
                    :$next($request);
    }

    protected function resError($msg) {
        return Response::create($msg, 'json', 403);
    }

    protected function checkLogged($req)
    {
        if (!in_array($req, $this->exclude)) {
            if (!session('user-type')) {
                return self::resError(self::MSG_NOT_LOG);
            }
        }
        return false;
    }

    protected function checkAdmin($req, Request $request)
    {
        $sid = $request->param('sid');
        if (!$sid) {
            if (preg_match('/\/subject\/\d+/', $req['url'])) $sid = $request->param('id');
            else return false;
        }
        if (!in_array($sid, session('subject'))) {
            return self::resError(self::MSG_NOT_AUTH_SUBJECT);
        }
        if (in_array($req, $this->auth_admin)) {
            if (session('user-type') !== 'admin') {
                return self::resError(self::MSG_NOT_AUTH_ADMIN);
            }
        }
        return false;
    }
}
