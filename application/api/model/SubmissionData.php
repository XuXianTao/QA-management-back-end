<?php

namespace app\api\model;

use think\db\Query;
use think\Model;

class SubmissionData extends Model
{
    //
    protected $table = 'submission';
    protected $pk = 'id';

    public function answer()
    {
        /** 依赖其他数据表 */
        return $this->hasOne('AnswerData', 'id', 'aid');
    }

    public function searchQuestionAttr($query, $value, $data)
    {
        $query->where('question', 'like', '%' . $value. '%');
        if (isset($data['sort'])) {
            $query->order($data['sort']);
        }
    }

    public function searchSubmitterAttr($query, $value, $data)
    {
        $query->whereOr('submitter', 'like', '%' . $value. '%');
    }

    public function searchAnswerAttr(Query $query, $value, $data)
    {

        $answers_search_questions = SubmissionData::haswhere('answer', function($query)use($value, $data) {
            $query->where('AnswerData.sid', $data['sid'])
                ->where('answer', 'like', '%' . $value . '%');
        })
            ->buildSql(false);
//        dump($answers_search_questions);
        $query->union($answers_search_questions);
//        dump($query->buildSql());
    }
}
