<?php

namespace app\home\controller;

use think\Db;
use app\home\model\Plan as PlanModel;
use app\home\model\User;

class Plan extends Base
{
    /*
     * 获取用户下周计划任务
     */
    public function index()
    {
        $title = '下周任务列表';
        $user_id = session('user')->id;
        // 获取当前时间，获取下周任务
        $time = date('Y-m-d'); //当前时间
        $lastday = date('Y-m-d', strtotime("$time Sunday")); // 本周最后一天时间
        $first = date('Y-m-d', strtotime("$lastday - 6 days")); // 本周第一天
        $next = date('Y-m-d', strtotime("$lastday + 1 day")); // 下周第一天
        $times = strtotime($lastday);
        // 获取任务
        $data = Db::name('user_plan')
            ->alias('a')
            ->join('plan b', 'a.plan_id = b.id')
            ->where('a.user_id', '=', $user_id)
            ->where('plan_time', '>=', $times)
            ->order('b.id', 'desc')
            ->paginate(10);
        // 渲染
        return view('index', compact('title', 'data'));
    }

    /*
     * 编写下周任务页面
     */
    public function create()
    {
        $title = '新增下周任务';

        return view('add', compact('title'));
    }

    /*
     * 新增任务行为
     */
    public function add($id)
    {
        if (request()->isPost()) {
            $data = input('post.');
            $file = request()->file('complete');
            $upload = new Report();
            $data['complete'] = $upload->upload($file);

            $time = date('Y-m-d'); //当前时间
            $lastday = date('Y-m-d', strtotime("$time Sunday")); // 本周最后一天时间
            $next = date('Y-m-d', strtotime("$lastday + 1 day")); // 下周第一天
            $times = strtotime($next);
            $data['plan_time'] = $times;
            // 写入plan表
            $plan_id = PlanModel::insert($data, false, true);
            // 写入关联表 user_plan
            $user_data = [
                'user_id' => $id,
                'plan_id' => $plan_id,
            ];
            Db::name('user_plan')->insert($user_data);
            $this->success('添加成功', 'plan/index');
        } else {
            $this->error('错误操作，3秒返回', 'plan/create');
        }
    }

    /*
     * 查看某一任务详情页面
     */
    public function show($id)
    {
        $title = '任务详情';
        // 判断当前用户有没有权限操作这篇文章
        $user_id = session('user')->id;
        $data = Db::name('user_plan')
            ->alias('a')
            ->join('plan b', 'a.plan_id = b.id')
            ->where('a.user_id', 'eq', $user_id)
            ->where('b.id', 'eq', $id)
            ->find();
        if ($data) {
            return view('show', compact('title', 'data'));
        } else {
            $this->error('您没有权限操作此任务', 'plan/index');
        }
    }

    /*
     * 修改行为
     */
    public function edit($id)
    {
        $data = input('post.');
        $file = request()->file('complete');
        if ($file) {
            $upload = new Report();
            $data['complete'] = $upload->upload($file);
        }
        $where = [
            'id' => $id,
        ];
        // 写入plan表
        PlanModel::update($data, $where);
        $this->success('修改成功', 'plan/index');
    }

    /*
     * 删除行为
     */
    public function delete($id)
    {
        // 判断当前用户有没有权限操作这篇文章
        $user_id = session('user')->id;
        $data = Db::name('user_plan')
            ->alias('a')
            ->join('plan b', 'a.plan_id = b.id')
            ->where('a.user_id', 'eq', $user_id)
            ->where('b.id', 'eq', $id)
            ->find();
        if ($data) {
            PlanModel::destroy($id);
            Db::name('user_plan')->where('id', 'eq', $data['id'])->delete();
            $this->success('删除成功', 'plan/index');
        } else {
            $this->error('您没有权限操作此任务', 'plan/index');
        }
    }
}
