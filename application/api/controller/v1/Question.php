<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\api\controller\Send;
use app\api\model\QuestionData;
use think\Controller;
use think\Exception;
use think\Request;

class Question extends Controller
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
        $result = Base::index($request, QuestionData::class, 'aid', 'id, aid, question', [
            ['aid', 'question'], [
                'aid' => $request->param('aid'),
                'question' => $request->param('search')
            ]
        ]);
        return self::returnMsg(200, 'Get Data Successfully.', $result);
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
            $model = QuestionData::create([
                'aid' => $data['aid'],
                'question' => $data['question']
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
        try {
            if (QuestionData::get($id)) {
                $data = QuestionData::update([
                    'id' => $id,
                    'question' => $request->param('question')
                ]);
                return self::returnMsg(200, 'Update Successfully', $data);
            }
            else {
                return self::returnMsg(500, 'ID is not existent.');
            }
        }
        catch (Exception $ex) {
            /** $ex->getCOde() 超过500时候头部Code会自动变成500 */
            return self::returnMsg($ex->getCode(), $ex->getMessage());
        }
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
        try {
            $item = QuestionData::get($id);
            if ($item) {
                $item->delete();
                return self::returnMsg(204);
            }
            else {
                return self::returnMsg(500, 'ID is not existent.');
            }
        }
        catch (Exception $ex) {
            /** $ex->getCOde() 超过500时候头部Code会自动变成500 */
            return self::returnMsg($ex->getCode(), $ex->getMessage());
        }
    }
}
