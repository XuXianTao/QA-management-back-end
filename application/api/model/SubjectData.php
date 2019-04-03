<?php

namespace app\api\model;

use think\Model;

class SubjectData extends Model
{
    //
    protected $table = 'subject';
    protected $pk = 'id';

    protected static function init()
    {
        self::beforeDelete(function ($subject) {
            SubmissionData::where('sid', $subject->id)->delete();
            AnswerData::destroy(['sid' => $subject->id]);
            UserSubjectRelation::where('sid', $subject->id)->delete();
        });

        self::afterInsert(function ($subject) {
            $admin = UserData::where('account', '=', 'admin')->find();

            UserSubjectRelation::create([
                'uid' => $admin->id,
                'sid' => $subject->id
            ]);
        });
    }

    public function answers()
    {
        return $this->hasMany('AnswerData', 'sid', 'id');
    }

    public function relationUser()
    {
        return $this->hasMany('UserSubjectRelation', 'sid', 'id');
    }

    public static function getAllID()
    {
        $data = self::all();
        $result = [];
        foreach ($data as $item) {
            array_push($result, $item['id']);
        }
        return $result;
    }

    public function searchNameAttr($query, $value, $data)
    {
        $query->where('name', 'like', '%' . $value . '%');
    }
}
