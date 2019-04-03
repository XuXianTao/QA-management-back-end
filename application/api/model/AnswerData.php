<?php

namespace app\api\model;

use think\db\Query;
use think\Model;

class AnswerData extends Model
{
    //
    protected $table = 'data_answer';
    protected $pk = 'id';

    protected static function init()
    {
        self::beforeDelete(function ($answer) {
            QuestionData::where('aid', $answer->id)->delete();
            SubmissionData::where('aid', $answer->id)->update([
                'aid' => null
            ]);
        });
    }

    public function questions()
    {
        /** 被其他数据表依赖 */
        return $this->hasMany('QuestionData', 'aid', 'id');
    }
    public function subject()
    {
        /** 依赖其他数据表 */
        return $this->hasOne('SubjectData', 'id', 'sid');
    }
    public function getTitleAttr()
    {
        $aid = $this->getAttr('id');
        $question = QuestionData::where('aid', $aid)->find();
        return mb_substr($question['question'], 0, LIMIT_TITLE) . '...';
    }

    public function searchAnswerAttr($query, $value, $data)
    {
        $query->where('answer', 'like', '%' . $value. '%');
        if (isset($data['sort'])) {
            $query->order($data['sort']);
        }
    }

    public function searchQuestionsAttr(Query $query, $value, $data)
    {
        $answers_search_questions = AnswerData::haswhere('questions', function($query)use($value) {
            $query->where('question', 'like', '%' . $value . '%');
        }, $data['fields'])
            ->where('sid', $data['sid'])
            ->buildSql(false);
        $query->union($answers_search_questions);
    }
}
