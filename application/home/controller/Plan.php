<?php

namespace app\home\controller;

use app\home\model\Plan as PlanModel;
use app\Upload\Upload;

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
            $planModel = new PlanModel();
            $result = $planModel->addPlan($user_id, $data);
            if ('success' == $result) {
                return [
                    'error' => 0,
                    'msg' => '',
                ];
            }

            return [
                'error' => 1,
                'msg' => '添加失败',
            ];
        } else {
            return [
                'error' => 1,
                'msg' => '错误操作',
            ];
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
        $planModel = new PlanModel();
        $data = $planModel->editPlan($id, $data);

        if ('success' == $data) {
            return [
                'error' => 0,
                'msg' => '',
            ];
        }

        return [
            'error' => 1,
            'msg' => $data,
        ];
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

    /*
     * 单独上传图片返回路径
     */
    public function uploadImg()
    {
        $file = request()->file('image');
        $upload = new Upload();
        $src = $upload->upload($file);

        return $src;
    }
}
