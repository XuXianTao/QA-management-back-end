<?php

namespace app\api\controller\v1;

use app\api\controller\Send;
use app\api\model\SubjectData;
use app\api\model\SubmissionData;
use think\Controller;
use think\Request;

class Nlp extends Controller
{
    use Send;
    const format = 'export LANG=en_US.UTF-8;';
    const python3 = PATH_PY_PROJECT . '/bin/python3';
    const file_train = PATH_PY_PROJECT . '/DataTrain.py';
    const file_getoutput = PATH_PY_PROJECT . '/GetOutput.py';

    /**
     * POST 方式请求服务器进行训练
     * @param Request $request
     */
    public function dataTrain(Request $request) {
        $sid = $request->param('sid');
        exec(self::python3 . ' ' . self::file_train . ' ' . $sid, $result, $status);
        if ($status) {
            return self::returnMsg(500, '训练指令执行失败，请联系管理员。', $result);
        }
        return self::returnMsg(200, 'Trained Successfully.', $result);
    }

    /**
     * POST方式请求获取正确答案
     * @param Request $request
     */
    public function getAnswer(Request $request) {
        $input = $request->param('msg');
        // 替换换行符
        $input = str_replace(PHP_EOL, '', $input);
        $type = getMsgType($input);
        $content = getMsgContent($input);
        switch ($type) {
            case '查询': {
                $msg_return = self::getSearch($content);
                break;
            }
            default: {
                list($msg, $submitter) = getMsgAndSubmitter($content);

                $subject = SubjectData::where('name', $type)->find();

                if (!$subject) {
                    $msg_return = "该科目不存在,请重试\n可以通过\n`【查询】科目`\n得到已支持的科目信息";
                    break;
                }
                $answer = self::execToGetAnswer($subject, $msg, $submitter);

                $msg_return = $answer ? $answer['answer'] : "该问题暂未搜索到相应答案\n科目：【{$type}】\n问题：$msg\n提交者：$submitter";
                if (!$answer) {
                    Submission::createItem([
                        'sid' => $subject->id,
                        'question' => $msg,
                        'aid' => $answer ? $answer->id : null,
                        'submitter' => $submitter
                    ]);
                }
            }
        }
        return self::returnMsg(200, 'Get Answer successfully.', $msg_return);
    }

    protected function getSearch($input)
    {
        $return = "目前已支持科目如下：";
        switch ($input) {
            case '科目': {
                $subjects = SubjectData::all();
                foreach ($subjects as $item) {
                    $return .= "\n【{$item['name']}】";
                }
                break;
            }
            default: {
                $return = '暂不支持该项搜索';
            }
        }
        return $return;
    }

    /**
     * @param $subject array  对应科目
     * @param $question string 用户发送的问题
     * @param $submitter string 提交者
     * @return mixed string|Array
     */
    protected function execToGetAnswer($subject, $question, $submitter)
    {
        /** 利用数据源实现机器学习自动回答 */
        // 注意输入的中文由于编码问题在python中无法被识别
        $str = exec(self::format . self::python3. ' ' . self::file_getoutput . ' ' . $subject['id'] . ' ' . $question . ' ' . $submitter, $output, $status);
        // 执行失败$status 为1
        if ($status) {
            return null;
        };
        $result = json_decode($str, true);
        return $result[0];
    }
}
