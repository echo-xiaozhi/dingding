<?php

namespace app\home\model;

use think\Cache;
use think\Model;
use think\Validate;

class Problem extends Model
{
    /*
     * 获取用户本周问题
     */
    public function getProblem()
    {
        $user_id = session('user')->id;
        $weekFirst = Cache::get('weekFirst');
        $nextWeekFirst = Cache::get('nextWeekFirst');

        $data = $this->alias('a')
            ->join('user_problem b', 'a.id = b.problem_id')
            ->where('b.user_id', 'eq', $user_id)
            ->where('a.create_times', '>=', $weekFirst)
            ->where('a.create_times', '<', $nextWeekFirst)
            ->order('a.id', 'desc')
            ->paginate(10);

        return $data;
    }

    /*
     * 新增任务
     */
    public function addProblem($data)
    {
        $user_id = session('user')->id;
        $data['create_times'] = time();
        $validate = validate('problem');
        if (!$validate->check($data)) {
            // 验证失败 输出错误信息
            return $validate->getError();
        }
        // 写入plan表
        $problem_id = self::insert($data, false, true);
        // 写入关联表 user_plan
        $user_data = [
            'user_id' => $user_id,
            'problem_id' => $problem_id,
        ];
        UserProblem::create($user_data);

        return 'success';
    }

    /*
     * 查看某一任务，并判断权限
     */
    public function showProblem($id)
    {
        $user_id = session('user')->id;
        $data = $this->alias('a')
            ->join('user_problem b', 'a.id = b.problem_id')
            ->where('b.user_id', 'eq', $user_id)
            ->where('a.id', 'eq', $id)
            ->find();

        return $data;
    }

    /*
     * 修改任务
     */
    public function editProblem($id, $data)
    {
        $power = $this->showProblem($id);
        if ($power) {
            $where = [
                'id' => $id,
            ];
            // 写入problem表
            self::update($data, $where);

            return 'success';
        }

        return '您没有权限修改此任务';
    }

    /*
     * 删除任务
     */
    public function deProblem($id)
    {
        $power = $this->showProblem($id);
//        return $power['id'];
        if ($power) {
            self::destroy($id);
            $userProblem = new UserProblem();
            $userProblem->where('problem_id', 'eq', $power['id'])->delete();

            return 'success';
        }

        return '您没有权限删除此任务';
    }

    /*
     * 获取用户本月所有问题
     */
    public function getMonthProblem()
    {
        $monthFirst = Cache::get('monthFirst');
        $monthLast = Cache::get('monthLast');
        $userId = session('user')->id;

        $data = $this->alias('a')
            ->join('user_problem b', 'a.id = b.problem_id')
            ->where('b.user_id', 'eq', $userId)
            ->where('a.create_times', '>=', $monthFirst)
            ->where('a.create_times', '<=', $monthLast)
            ->select();

        return $data;
    }
}
