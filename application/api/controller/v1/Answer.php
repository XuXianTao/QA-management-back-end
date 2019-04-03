<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\api\controller\Send;
use app\api\model\AnswerData;
use think\Controller;
use think\Exception;
use think\Request;

class Answer extends Controller
{
    use Send;
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //
        $sid = $request->param('sid');
//        $data = AnswerData::where('sid', $sid)->all();
        $data = Base::index($request, AnswerData::class, null, null, [
            ['answer'], [
                'answer' => $request->param('search')
            ]
        ], [
            'where' => ['sid', $sid]
        ]);
        return self::returnMsg(200, 'GetSuccessfully.', $data);
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
        $data = $request->param();
        try {
            $model = AnswerData::create([
                'sid' => $data['sid'],
                'answer' => $data['answer']
            ]);

            return self::returnMsg(201, 'Question Created successfully.', $model);
        }
        catch (Exception $ex) {
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
        $data = AnswerData::get($id);
        return self::returnMsg(200, 'Get successfully.', $data);
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
    }
}
