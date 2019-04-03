<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\api\controller\Send;
use app\api\model\SubjectData;
use think\Controller;
use think\Exception;
use think\Request;

class Subject extends Controller
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
        $data = Base::index($request, SubjectData::class, null, null, [
            ['name'], [
                'name' => $request->param('search')
            ]
        ], [
            'order' => ['id']
        ]);
        return self::returnMsg(200, 'Get Successfully', $data);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
        $input = input('');
        try {
            $data = SubjectData::create([
                'name' => $input['title']
            ]);
            return self::returnMsg(201, 'Subject created successfully.', $data);
        }
        catch (Exception $ex) {
            if ($ex->getData()['PDO Error Info']['SQLSTATE'] == '23000') {
                return self::returnMsg(500, '该科目已被注册');
            }
            return self::returnMsg($ex->getCode(), $ex->getMessage());
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
        $subject = SubjectData::get($id);
        if (!empty($subject)) {
            try {
                $subject->delete();
                return self::returnMsg(204);
            }
            catch (Exception $ex) {
                return self::returnMsg($ex->getCode()?:500, $ex->getMessage());
            }
        }
        else {
            return self::returnMsg(500, 'The Id of The Subject is not existent.');
        }
    }

    /**
     * 获取用户下的科目列表
     */
    public function forUser()
    {
        //
        if (session('user-type') === 'admin') {
            session('subject', SubjectData::getAllID());
        }
        $data = SubjectData::whereIn('id', session('subject'))->order('id')->all();
        return self::returnMsg(200, 'Get Successfully', $data);
    }
}
