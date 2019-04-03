<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\api\controller\Send;
use app\api\model\AnswerData;
use app\api\model\QuestionData;
use app\api\model\SubjectData;
use app\api\model\SubmissionData;
use think\Controller;
use think\Exception;
use think\Request;

class Submission extends Controller
{
    use Send;
    use Base;

    const Fields = ['id', 'sid', 'question', 'aid', 'submitter'];

    public static function createItem($data) {
        try {
            return SubmissionData::create($data);
        }
        catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * 显示资源列表
     * $type Array ['submit'] | ['import'] | ['submit', 'import']
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //
        $fields = ['aid', 'id', 'question', 'sid', 'submitter'];
        $fields = implode($fields, ',');
//        $fields_answer = AnswerData::getTable() . '.id, answer, sid';
        $result = Base::index($request, SubmissionData::class, 'sid', $fields, [
            ['question', 'submitter', 'answer'], [
                'submitter' => $request->param('search'),
                'question' => $request->param('search'),
                'answer' => $request->param('search'),
                'sid' => $request->param('sid'),
//                'fields' => $fields_answer
            ]
        ], [
            'with' => ['answer']
        ]);
        return self::returnMsg(200, 'Get Successfully.', $result);
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
        $params = $request->param();
        $item = self::createItem([
            'sid' => $params['sid'],
            'question' => $params['question'],
            'aid' => $params['aid'],
            'submitter' => $params['submitter']
        ]);
        if (get_class($item) === 'Exception') {
            return self::returnMsg($item->getCode(), $item->getMessage());
        }
        else {
            return self::returnMsg(201, 'Created Successfully.', $item);
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
        $query = new SubmissionData;
        $data = SubmissionData::get($id);
        if ($data['aid']) {
            $query = $query->with('answer');
        }
        $data = $query->get($id);
        return self::returnMsg(200, 'Get ' . $id . ' information successfully.', $data);
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
        $data = $request->param();

        $fields = array_keys(array_intersect_key($data, array_fill_keys(self::Fields, self::Fields)));

        try {
            $result = SubmissionData::update($data, $fields);
            return self::returnMsg(200, 'Update Successfully.', $result);
        }
        catch (Exception $ex) {
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
            $item = SubmissionData::get($id);
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

    /**
     * POST 学生提交数据导入数据源中
     */
    public function addToData(Request $request)
    {
        $input = $request->param();
        try {
            $data = QuestionData::create([
                'aid' => $input['aid'],
                'question' => $input['question']
            ]);
            return self::returnMsg(201, 'Add Successfully.', $data);
        }
        catch (Exception $ex) {
            return self::returnMsg($ex->getCode(), $ex->getMessage());
        }
    }
}
