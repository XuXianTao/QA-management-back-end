<?php

namespace app\weixin\controller;

use app\api\controller\v1\Submission;
use app\api\model\AnswerData;
use app\api\model\SubjectData;
use think\Controller;
use think\Exception;
use think\facade\Log;
use think\Request;

class AutoMsg extends Controller
{

    /** Token: QA_data */
    const Token = 'QA_data';
    /** AESKey: mFyEtCX0iQnL6nlMh9Skkfb5ibV7GpDdesC263o40Df */
    const AESKey = 'mFyEtCX0iQnL6nlMh9Skkfb5ibV7GpDdesC263o40Df';

    const Fields = ['signature', 'timestamp', 'nonce', 'echostr'];

    const FieldsTextInput = [
        'ToUserName',
        'FromUserName',
        'CreateTime',
        'MsgType',
        'Content',
        'MsgId'
    ];

    const FieldsEventInput = [
        'ToUserName',
        'FromUserName',
        'CreateTime',
        'MsgType',
        'Event'
    ];

    const FieldsMsgReturn = [
        'ToUserName',
        'FromUserName',
        'CreateTime',
        'MsgType',
        'Content'
    ];


    /** https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1472017492_58YV5
     * Get
     * 用于公众号测试接口可用
     */
    public function test(Request $request)
    {
        $data = $request->param();

        try {
            if (count($data) === 0) return 'hello, this is handle view';
            $echostr = $data['echostr'];
            $signature = $data['signature'];
            $list = [
                'token' => self::Token,
                'timestamp' => $data['timestamp'],
                'nonce' => $data['nonce']
            ];
            sort($list);
            $list_str = implode($list);
            $hashcode = sha1($list_str);
            if ($hashcode !== $signature) throw Exception('Authentication failure');
            else {
                return $echostr;
            }
        }
        catch (Exception $ex) {
            return $ex->getMessage();
        }
    }


    public function refreshAccessToken()
    {

    }

    /**自动消息回复
     * @param Request $request
     * @return \think\Response
     */
    public function main(Request $request)
    {
        $params = $request->param();
        $data = (array) new \SimpleXMLElement($request->getContent(), LIBXML_NOCDATA);
        $data_keys = array_keys($data);
        $msg_return = '';
        switch ($data['MsgType']) {
            case 'event': {
                switch ($data['Event']) {
                    case 'subscribe': {
                        $msg_return = INTRODUCE;
                        break;
                    }
                    default: {
                        $msg_return = INTRODUCE;
                    }
                }
                break;
            }
            case 'text': {
                $input = $data['Content'];
                $type = getMsgType($input);
                $content = getMsgContent($input);
                if ($type === '查询') {
                    $msg_return = self::getSearch($content);
                    break;
                }
                else {
                    list($msg, $submitter) = getMsgAndSubmitter($content);

                    $subject = SubjectData::where('name', $type)->find();

                    if (!$subject) {
                        $msg_return = "该科目不存在,请重试\n可以通过\n`【查询】科目`\n得到已支持的科目信息";
                        break;
                    }
                    $answer = self::getAnswer($type, $msg);

                    $msg_return = $answer ? $answer['answer'] : "该问题暂未搜索到相应答案\n科目：【{$type}】\n问题：$msg\n提交者：$submitter";

                    self::saveSubmission([
                        'sid' => $subject->id,
                        'question' => $msg,
                        'aid' => $answer ? $answer->id : null,
                        'submitter' => $submitter
                    ]);
                }
                break;
            }
        }
        $result = buildOutputXML([
            'ToUserName' => $params['openid'],
            'FromUserName' => $data['ToUserName'],
            'CreateTime' => time(),
            'MsgType.text' => 'text',
            'Content' => $msg_return
        ]);
        return response($result, 200, [], 'xml');
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

    protected function saveSubmission($data)
    {
        $submission = Submission::createItem($data);
        return $submission;
    }

    /**
     * @param $subject string  对应科目
     * @param $question string 用户发送的问题
     * @return AnswerData
     */
    protected function getAnswer($subject, $question)
    {
        /** @todo 利用数据源实现机器学习自动回答 */

        return null;
    }
}
