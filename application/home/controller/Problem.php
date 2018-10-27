<?php

namespace app\home\controller;

use think\Db;
use app\home\model\Problem as ProblemModel;

class Problem extends Base
{
    /*
     * 用户问题列表
     */
    public function index()
    {
        $title = '问题列表';
        $user_id = session('user')->id;
        // 获取当前时间，获取下周任务
        $time = date('Y-m-d'); //当前时间
        $lastday = date('Y-m-d', strtotime("$time Sunday")); // 本周最后一天时间
        $first = date('Y-m-d', strtotime("$lastday - 6 days")); // 本周第一天
        $first = strtotime($first);
        $end = strtotime($lastday);
        $data = Db::name('user_problem')
            ->alias('a')
            ->join('problem b', 'a.problem_id = b.id')
            ->where('create_times', '>=', $first)
            ->where('create_times', '<=', $end)
            ->where('a.user_id', 'eq', $user_id)
            ->paginate(10);

        return view('index', compact('title', 'data'));
    }

    /*
     * 新增页面
     */
    public function create()
    {
        $title = '新增问题';

        return view('add', compact('title'));
    }

    /*
     * 新增行为
     */
    public function add()
    {
        $data = input('post.');
        $user_id = session('user')->id;
        $data['create_times'] = time();
        // 写入问题表
        $problem_id = ProblemModel::insert($data, false, true);
        // 写入关联表
        $user_data = [
            'user_id' => $user_id,
            'problem_id' => $problem_id,
        ];
        Db::name('user_problem')->insert($user_data);
        $this->success('添加成功', 'problem/index');
    }

    /*
     * 修改
     */
    public function edit($id)
    {
        $data = input('post.');
        $where = [
            'id' => $id,
        ];
        // 写入plan表
        ProblemModel::update($data, $where);
        $this->success('修改成功', 'problem/index');
    }

    /*
     * 问题详情页面
     */
    public function show($id)
    {
        $title = '问题详情';
        // 判断当前用户有没有权限操作这篇文章
        $user_id = session('user')->id;
        $data = Db::name('user_problem')
            ->alias('a')
            ->join('problem b', 'a.problem_id = b.id')
            ->where('a.user_id', 'eq', $user_id)
            ->where('b.id', 'eq', $id)
            ->find();
        if ($data) {
            return view('show', compact('title', 'data'));
        } else {
            $this->error('您没有权限操作此任务', 'problem/index');
        }
    }

    /*
     * 删除
     */
    public function delete($id)
    {
        // 判断当前用户有没有权限操作这篇文章
        $user_id = session('user')->id;
        $data = Db::name('user_problem')
            ->alias('a')
            ->join('problem b', 'a.problem_id = b.id')
            ->where('a.user_id', 'eq', $user_id)
            ->where('b.id', 'eq', $id)
            ->find();
        if ($data) {
            ProblemModel::destroy($id);
            Db::name('user_problem')->where('id', 'eq', $data['id'])->delete();
            $this->success('删除成功', 'problem/index');
        } else {
            $this->error('您没有权限操作此任务', 'problem/index');
        }
    }
}
