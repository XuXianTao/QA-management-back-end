<?php

namespace app\api\model;

use think\Model;

class UserData extends Model
{
    //
    protected $table = 'user';
    protected $pk = 'id';
    protected $sids;

    protected static function init()
    {
        self::afterInsert(function($user) {
            foreach ($user->sids as $item) {
                UserSubjectRelation::create([
                    'uid' => $user->id,
                    'sid' => $item
                ]);
            }
        });
        self::beforeDelete(function($user) {
            UserSubjectRelation::where('uid', $user->id)->delete();
        });
    }

    public function usersubject()
    {
        return $this->belongsToMany('SubjectData','user_subject', 'sid', 'uid');
    }

    public function getSidsAttr($value, $data)
    {
        $result = [];
        $sids = UserSubjectRelation::where('uid', self::getAttr('id')?:$data['id'])->field('sid')->all();
        foreach ($sids as $sid) {
            array_push($result, (int)$sid['sid']);
        }
        return $result;
    }

    public function setSidsAttr($value, $data)
    {

        if (key_exists('type', $data) && $data['type'] === 'create') {
            $this->sids = $data['sids'];
            return null;
        }

        function getDiff($old, $new) {
            $added = [];
            $reduced = [];
            foreach ($old as $item) {
                if (!in_array($item, $new)) array_push($reduced, $item);
            }
            foreach ($new as $item) {
                if (!in_array($item, $old)) array_push($added, $item);
            }
            return [
                'added' => $added,
                'reduced' => $reduced
            ];
        }

        $sid_objs = UserSubjectRelation::where('uid', self::getAttr('id')?:$data['id'])->field('sid')->all();
        $sids = [];
        foreach ($sid_objs as $item) { array_push($sids, $item['sid']); }
        $diff = getDiff($sids, $value);
        UserSubjectRelation::where('uid', self::getAttr('id'))->where('sid', 'in', $diff['reduced'])->delete();
        foreach ($diff['added'] as $item) {
            UserSubjectRelation::create([
                'uid' => $this->id,
                'sid' => $item
            ]);
        }
        return null;
    }

    public function searchAccountAttr($query, $value, $data)
    {
        $query->where('account', 'like', '%' . $value . '%');
    }
}
