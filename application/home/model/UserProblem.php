<?php
namespace app\home\model;

use think\Model;

class UserProblem extends Model
{
    public function problem()
    {
        return $this->belongsTo('Problem', 'problem_id', 'id');
    }
}