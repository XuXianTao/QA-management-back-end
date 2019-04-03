<?php

namespace app\api\model;

use think\Model;

class QuestionData extends Model
{
    //
    protected $table = 'data_question';
    protected $pk = 'id';

    public function answer()
    {
        return $this->hasOne('answer_data', 'id', 'aid');
    }

    public function searchQuestionAttr($query, $value, $data)
    {
        $query->where('question', 'like', '%' . $value. '%');
        if (isset($data['sort'])) {
            $query->order($data['sort']);
        }
    }

    public function searchAidAttr($query, $value, $data)
    {
        if (is_numeric($value)) {
            $query->where('aid', $value);
        }
    }
}
