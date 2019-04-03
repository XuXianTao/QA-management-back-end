<?php
/**
 * Created by PhpStorm.
 * User: xxt
 * Date: 18-12-23
 * Time: 下午11:22
 */
namespace app\api\controller;

use app\api\model\AnswerData;
use app\api\model\QuestionData;
use app\http\SwooleTask;
use think\swoole\facade\Task;

trait Csv
{
    public static function getCsvArr(\think\File $file)
    {
        $str = $file->fread($file->getSize());

        /** 去除utf-8 BOM头部 */
        $BOM = "\xEF\xBB\xBF";
        $str = preg_replace('/^' . $BOM .'/', '', $str);

        $delimiter = ',';
        switch (true) {
            case strpos($str, "\t"): {
                $delimiter = "\t";
                break;
            }
            default: {
                $delimiter = ',';
            }
        }
        $file->setCsvControl($delimiter);
        $linefeeds = ["\r\n", "\n", "\r"]; // 0x0d => \r  0x0a => \n
        $the_linefeed = '';
        $delimiter = $file->getCsvControl()[0];
        foreach ($linefeeds as $linefeed) {
            if (strpos($str, $linefeed)) {
                $the_linefeed = $linefeed;
                break;
            }
        }
        $lines = explode($the_linefeed, $str);
        $head = array_shift($lines);
        $titles = explode($delimiter, $head);
        $result = array(count($lines));
        foreach ($lines as $k => $line) {
            $values = explode($delimiter, $line, count($titles));
            if (count($values) < count($titles)) {
                continue;
            };
            $result[$k] = array_fill_keys($titles, '');
            foreach ($titles as $kk => $title) {
                $result[$k][$title] = $values[$kk];
            }
        }
        return $result;
    }

    public static function importCsv(array $data, $sid, $wsid, $by = 'class')
    {
        $CHUNK_LENGTH = 50;
        $exsist = [];
        $last = '';
        $questions = [];
        $answers = [];
        $total_count = count($data);
        $task = new SwooleTask(SWOOLE_TASK_ID);
        foreach ($data as $data_index => $item) {

            /* 判断的字段如果为空，则补充为上一行的对应数据 */
            if (!$item[$by]) $item[$by] = $last;

            /* 如果该行数据在判断的字段中没有出现过，则新建一个回答系列 */
            if (strlen($item['answer']) && !array_key_exists($item[$by], $exsist)) {

                $answer = [
                    'answer' => $item['answer'],
                    'sid' => $sid
                ];
                // 将该系列问题的下标存储到对应的判断字段map
                $exsist[$item[$by]] = [
                    'a_index' => count($answers),
                    'a_aid' => null
                ];
                array_push($answers, $answer);

                // 存储该行判断字段的数据
                $last = $item[$by];
            }
            $question_item = [
                'question' => $item['question'],
                'aid' => $exsist[$item[$by]]['a_aid'],
            ];
            // 如果map中不存在aid则存入其对应的index
            if ($exsist[$item[$by]]['a_aid'] === null) {
                $question_item['a_index'] = $exsist[$item[$by]]['a_index'];
            }
            array_push($questions, $question_item);

            // 非第一次且回答达到目标个数或者到最后一个数据开始存入数据库
            if ((count($questions) > 0 && count($answers) >= $CHUNK_LENGTH) || ( $data_index === count($data)-1 )) {
                $a = new AnswerData();
                $answer_models = $a->saveAll($answers)->all();
                // 处理questions 判断aid是否存在
                foreach ($questions as $k => $question) {
                    if ($question['aid'] === null) {
                        $a_index = $question['a_index'];
                        $questions[$k]['aid'] = $answer_models[$a_index]->id;
                        unset($question['a_index']);
                    }

                }
                $q = new QuestionData();
                $q->saveAll($questions);
                // 刷新判断map数组
                foreach ($exsist as $by_value => $ex_item) {
                    $exsist[$by_value]['a_aid'] = $exsist[$by_value]['a_aid']?:$answer_models[$ex_item['a_index']]->id;
                }

                // 清空数组
                $answers = [];
                $questions = [];

                // 异步触发websocket
                $task->dispatch([
                    'id' => $wsid,
                    'type' => 'progress',
                    'finished' => $data_index + 1,
                    'total' => $total_count
                ]);

            }
        }

    }

}