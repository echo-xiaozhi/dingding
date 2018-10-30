<?php

namespace app\home\controller;

use app\home\model\Plan as PlanModel;

class Plan extends Base
{
    /*
     * 获取用户下周计划任务
     */
    public function index()
    {
        $title = '下周任务列表';
        $planModel = new PlanModel();
        $data = $planModel->getPlan();
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
    public function add($user_id)
    {
        if (request()->isPost()) {
            $data = input('post.');
            $file = request()->file('complete');
            $planModel = new PlanModel();
            $planModel->addPlan($user_id, $data, $file);
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
        $planModel = new PlanModel();
        $data = $planModel->showPlan($id);
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
        $planModel = new PlanModel();
        $data = $planModel->editPlan($id, $data, $file);

        if ('success' == $data) {
            $this->success('修改成功', 'plan/index');
        }

        $this->error($data, 'plan/index');
    }

    /*
     * 删除行为
     */
    public function delete($id)
    {
        $planModel = new PlanModel();
        $data = $planModel->dePlan($id);

        if ('success' == $data) {
            return [
                'error' => 0,
                'msg' => '删除成功',
            ];
        }

        return [
            'error' => 1,
            'msg' => $data,
        ];
    }
}
