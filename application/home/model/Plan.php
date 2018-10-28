<?php

namespace app\home\model;

use app\Upload\Upload;
use think\Cache;
use think\Model;

class Plan extends Model
{
    /*
     * 获取用户下周计划
     */
    public function getPlan()
    {
        $user_id = session('user')->id;
        $nextWeekFirst = Cache::get('nextWeekFirst');

        $data = $this->alias('a')
            ->join('user_plan b', 'a.id = b.plan_id')
            ->where('b.user_id', 'eq', $user_id)
            ->where('a.plan_time', '>=', $nextWeekFirst)
            ->order('a.id', 'desc')
            ->paginate(10);

        return $data;
    }

    /*
     * 新增任务
     */
    public function addPlan($user_id, $data, $file)
    {
        $upload = new Upload();
        $data['complete'] = $upload->upload($file);
        $data['plan_time'] = Cache::get('nextWeekFirst');
        // 写入plan表
        $plan_id = self::insert($data, false, true);
        // 写入关联表 user_plan
        $user_data = [
            'user_id' => $user_id,
            'plan_id' => $plan_id,
        ];
        UserPlan::create($user_data);
    }

    /*
     * 查看某一任务，并判断权限
     */
    public function showPlan($id)
    {
        $user_id = session('user')->id;
        $data = $this->alias('a')
            ->join('user_plan b', 'a.id = b.plan_id')
            ->where('b.user_id', 'eq', $user_id)
            ->where('a.id', 'eq', $id)
            ->find();

        return $data;
    }

    /*
     * 修改任务
     */
    public function editPlan($id, $data, $file)
    {
        $power = $this->showPlan($id);
        if ($power) {
            if ($file) {
                $upload = new Upload();
                $data['complete'] = $upload->upload($file);
            }
            $where = [
                'id' => $id,
            ];
            // 写入plan表
            self::update($data, $where);

            return 'success';
        }

        return '您没有权限修改此任务';
    }

    /*
     * 删除任务
     */
    public function dePlan($id)
    {
        $power = $this->showPlan($id);
//        return $power['id'];
        if ($power) {
            self::destroy($id);
            $userPlan = new UserPlan();
            $userPlan->where('plan_id', 'eq', $power['id'])->delete();

            return 'success';
        }

        return '您没有权限删除此任务';
    }
}
