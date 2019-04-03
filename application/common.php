<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
// 默认的标题字数与列表显示行数
define('LIMIT_TITLE', 10);
define('LIMIT_COLLECTION', 9);

// Swoole Task ID
define('SWOOLE_TASK_ID', 0x70001001);

define('TP_PATH', __DIR__ . '/..');

define('PATH_PY_PROJECT', TP_PATH . '/python_project/automsg');


const INTRODUCE = <<<intro
本公众号用于收集并为学生提供自动问答服务【由于初期数据量较少，可能无法获得理想答案】

提问之前请确认已生效的科目信息，通过发送

`【查询】科目`

来得到相应信息

提问方式为：

`【科目】你的问题`
* 如果需要记录发送者请按照如下格式发送
`【科目】你的问题【发送者】XXX`

intro;

/**
 * @param $data
 * @return string
 */
function buildOutputXML($data)
{
    $result = new \XMLWriter();

    $result->openMemory();
    $result->startElement('xml');
    foreach ($data as $key => $value) {
        $keys = explode('.', $key);
        if (count($keys) === 1) {
            $result->startElement($key);
            $result->writeCData($value);
            $result->endElement();
        }
        else {
            switch ($keys[1]) {
                case 'text': {
                    $result->startElement($keys[0]);
                    $result->text($value);
                    $result->endElement();
                    break;
                }
                case 'CDATA': {
                    $result->startElement($keys[0]);
                    $result->writeCData($value);
                    $result->endElement();
                    break;
                }
            }
        }
    }
    $result->endElement();
    return $result->outputMemory();
}

function isArrayEqual($arr1, $arr2) {
    return count(array_diff($arr1, $arr2)) === 0 ? true : false;
}

function getMsgType($str) {
    $result = [];
    if (preg_match('/(?<=^【)\S+?(?=】)/', $str, $result) > 0) {
        return $result[0];
    }
    else return false;
}

function getMsgContent($str) {
    $result = [];
    if (preg_match('/^【\S+?】([\S\s]+)/', $str, $result) > 0) {
        return $result[1];
    }
    else return false;
}

function getMsgAndSubmitter($str) {
    $result = [];
    if (preg_match('/([\S\s$^]*)【发送者】(\S*)/', $str, $result) > 0) {
        return [
            $result[1],
            $result[2]
        ];
    }
    else return [$str, null];
}