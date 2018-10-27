<?php
namespace app\home\model;

use think\Model;

class UserPlan extends Model
{
    public function plan()
    {
        return $this->belongsTo('Plan', 'plan_id', 'id');
    }
}