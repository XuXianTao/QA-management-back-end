<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\api\controller\Send;
use app\api\model\SubjectData;
use app\api\model\UserData;
use think\Controller;
use think\Exception;
use think\Request;

class User extends Controller
{
    use Send;
    use Base;
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //
        $data = Base::index($request, UserData::class, null, 'id, account', [
            ['account'], [
                'account' => $request->param('search')
            ]
        ], [
            'with' => ['usersubject'],
            'order' => ['id']
        ]);
        return self::returnMsg(200, 'Get Successfully', $data);
    }

    /**
     * 显示创建资源表单页.
     * @return \think\Response
     */
    public function create()
    {
        //
    }


    /**
     * 保存新建的资源
     *
     * account String
     * secret  String 前端md5加密后再传输
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
        $input = input('');
        try {
            $user = UserData::create([
                'account' => $input['account'],
                'secret' => $input['secret'],
                'sids' => $input['sids'],
                'type' => 'create'
            ]);
            return self::returnMsg(201, 'Account registered successfully.', ['account' => $user['account']]);
        } catch (Exception $ex) {
            //dump($ex);
            if ($ex->getData()['PDO Error Info']['SQLSTATE'] == '23000') {
                return self::returnMsg(500, '该账号已被注册');
            }
            return self::returnMsg($ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //

        $user = UserData::get($id);

        if (!$user) return self::returnMsg(404, 'The User '.$id.' is not existent.');
        $data = $request->param();

        switch ($data['type']) {
            case 'update_user': {
                if (session('user-type') !== 'admin') {
                    return self::returnMsg(403, 'You have no auth to manage users.');
                }
                if ($data['sids'] !== null) $user->sids = $data['sids'];

                if ($data['password']) $user->secret = $data['password'];

                $user->save();

                break;
            }
            case 'change_pw': {
                if ($user['secret'] !== $data['password_old']) {
                    return self::returnMsg(401, '密码错误。');
                }
                else {
                    $user->secret = $data['password_new'];
                    $user->save();

                    break;
                }
            }
            default: {
                return self::returnMsg(400, 'The `type` field is required in request body.');
            }
        }


        return self::returnMsg(200, 'Update Successfully.', $user->append(['sids']));
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
        try {
            $user = UserData::get($id);
            if (!$user) return self::returnMsg(404, '该账号' . $id . '不存在');
            $user->delete();
            return self::returnMsg(204);
        }
        catch (Exception $ex) {
            return self::returnMsg($ex->getCode(), $ex->getMessage());
        }
    }


    /**
     * POST 检查用户登录是否正确
     *
     * account String
     * secret  String 前端md5加密后再传输
     */
    public function login(Request $request)
    {
        $input = $request->param();
        $account = UserData::where('account', $input['account'])->append(['sids'])->find();
        switch(true) {
            case (empty($account)): {
                return self::returnMsg(200, 'NoAccount');
                break;
            }
            case ($account['secret'] !== $input['secret']): {
                return self::returnMsg(200, 'FailAuth');
                break;
            }
            case ($account['secret'] && $account['secret'] === $input['secret']): {
                session('subject', $account['sids']);
                session('account', $account['account']);
                session('uid', $account['id']);
                if ($account['account'] === 'admin') session('user-type', 'admin');
                else session('user-type', 'normal');
                $data = SubjectData::whereIn('id', session('subject'))->order('id')->all();
                return self::returnMsg(200, 'LoginSuccessfully', ['subjects' => $data, 'type' => session('user-type'), 'account' => session('account'), 'uid' => session('uid')]);
                break;
            }
            default: {
                return self::returnMsg(404, 'NoInput');
                break;
            }
        }
    }

    /**
     * GET 检查用户是否登录
     */
    public function check()
    {
        if (session('user-type')) {
            $data = [];
            if (session('user-type') === 'admin') {
                $data = SubjectData::order('id')->all();
                session('subject', UserData::where('account', '=', 'admin')->find()->sids);
            }
            else $data = SubjectData::whereIn('id', session('subject'))->order('id')->all();
            return self::returnMsg(200, 'Logged',['subjects' => $data, 'type' => session('user-type'), 'account' => session('account'), 'uid' => session('uid')]);
        }
        else {
            return self::returnMsg(200, 'NotLogged');
        }
    }

    /**
     * Post 用户退出
     */
    public function logout()
    {
        session('user-type', null);
        session('subjects', null);
        return self::returnMsg(200, 'Logout successfully.');
    }
}
