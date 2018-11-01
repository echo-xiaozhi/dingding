<?php

namespace app\home\controller;

use app\home\model\Problem as ProblemModel;

class Problem extends Base
{
    /*
     * 用户问题列表
     */
    public function index()
    {
        $title = '问题列表';
        $problemModel = new ProblemModel();
        $data = $problemModel->getProblem();

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
        $problemModel = new ProblemModel();
        $result = $problemModel->addProblem($data);

        if ('success' == $result) {
            return [
               'error' => 0,
               'msg' => '',
            ];
        }

        return [
            'error' => 1,
            'msg' => $result,
        ];
    }

    /*
     * 修改
     */
    public function edit($id)
    {
        $data = input('post.');
        $problemModel = new ProblemModel();
        $data = $problemModel->editProblem($id, $data);
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
     * 问题详情页面
     */
    public function show($id)
    {
        $title = '问题详情';

        $problemModel = new ProblemModel();
        $data = $problemModel->showProblem($id);
        if ($data) {
            return view('show', compact('title', 'data'));
        } else {
            $this->error('别没事越个权玩，你没权限看这个', 'problem/index');
        }
    }

    /*
     * 删除
     */
    public function delete($id)
    {
        $problemModel = new ProblemModel();
        $data = $problemModel->deProblem($id);

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
