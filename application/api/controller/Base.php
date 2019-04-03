<?php
/**
 * Created by PhpStorm.
 * User: xxt
 * Date: 19-2-18
 * Time: 下午2:13
 */
namespace app\api\controller;
trait Base {
    /**
     * @param \think\Request $request 请求对象
     * @param $Model mixed 使用的模型
     * @param $fk string 主键
     * @param $fields string 过滤字段
     * @param array $search_options array 搜索的相关配置
     * @param null $extras array 额外的query功能添加
     * @return array
     */
    static function index(\think\Request $request, $Model, $fk,
                          $fields, $search_options = [], $extras = null)
    {
        $search = $request->param('search');
        $id = input($fk);
        $page = input('page')?:0;
        $limit = input('limit')?:LIMIT_COLLECTION;
        $query = new $Model();
        if (!empty($fk)) $query = $query->where($fk, $id);
        if (!empty($fields)) $query = $query->field($fields);
        if (!empty($extras)) {
            foreach ($extras as $k => $v) {
                $query = $query->$k(...$v);
            }
        }
        if ($search && !empty($search_options)) {
            $query = $query->withSearch(...$search_options);
        }
//        dump($query->buildSql());
        $total = $query->select()->count();
        $data = $query->limit($page * $limit, $limit)->all();
        $result  =  [
            'limit' => $limit,
            'page' => $page,
            'total' => $total,
            'list' => $data
        ];
        return $result;
    }
}