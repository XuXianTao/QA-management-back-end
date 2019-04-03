<?php

namespace app\api\controller\v1;

use app\api\controller\Csv;
use app\api\controller\Send;
use app\api\controller\Base;
use app\api\model\AnswerData;
use app\api\model\QuestionData;
use app\api\model\SubjectData;
use function League\Csv\delimiter_detect;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;
use SplTempFileObject;
use think\db\Query;
use think\Exception;
use think\Controller;
use think\Request;


class Collection extends Controller
{
    use Send;
    use Csv;
    use Base;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        //
        $fields = AnswerData::getTable() . '.id, answer, sid';
        list($search, $sid) = array_values($request->only(['search' => null, 'sid' => null]));
        $search_options = [
            ['answer', 'questions'], [
                'answer' => $search,
                'questions' => $search,
                'sid' => $sid,
                'fields' => $fields
            ]
        ];
        $result = Base::index($request, AnswerData::class, 'sid', $fields, $search_options,[
            'append' => [['title']],
            'withAttr' => ['answer', function($value) {
                return mb_substr($value, 0, LIMIT_TITLE) . '...';
            }]
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
        $answer = $request->param('answer');
        $sid = $request->param('sid');
        $data = AnswerData::create([
            'answer' => $answer,
            'sid' => $sid
        ]);
        return self::returnMsg(200, 'Created Successfully.', $data);
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
        $data = AnswerData::with('questions')->get($id);
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
        try {
            if (AnswerData::get($id)) {
                $data = AnswerData::update([
                    'id' => $id,
                    'answer' => $request->param('answer')
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
        //
        try {
            $item = AnswerData::get($id, 'questions');
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

    public function search()
    {

    }

    public function import(Request $request)
    {
        $file = $request->file('file');
        $sid = $request->param('sid');
        $wsid = $request->param('from_id');
        $csv_arr = self::getCsvArr($file);
        self::importCsv($csv_arr, $sid, $wsid, $by = 'answer');
        return self::returnMsg(200, 'Import Successfully.');
    }

    public function output(Request $request)
    {
        $sid = $request->param('sid');
        $os = $request->header('user-agent');
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        switch (true) {
            case strpos(strtolower($os), 'linux'): {
                $csv->setNewline("\n");
                break;
            }
            case strpos(strtolower($os), 'windows'): {
                $csv->setNewline("\r\n");
                $csv->setOutputBOM(Reader::BOM_UTF8);
                break;
            }
            case strpos(strtolower($os), 'mac'): {
                $csv->setNewline("\r");
                $csv->setOutputBOM(Reader::BOM_UTF16_LE);
                break;
            }
            default: {
                $csv->setNewline("\r\n");
                $csv->setOutputBOM(Reader::BOM_UTF8);
            }
        }
        $answers = AnswerData::where('sid', $sid)->field(['id'])->all();
        $aids = [];
        $result = [
            ['question', 'class', 'answer']
        ];
        foreach ($answers as $item) {
            array_push($aids, $item['id']);
        }
        $questions = QuestionData::where('aid', 'in', $aids)->with('answer')->all();
        foreach ($questions as $question) {
            array_push($result, [$question['question'], $question['aid'], $question['answer']['answer']]);
        }
        $csv->insertAll($result);
        $csv->output('问答数据系统_' . SubjectData::get($sid)->name . time() . '.csv');
        die();
    }
}
