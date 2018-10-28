<?php

namespace app\home\controller;

use app\home\model\Pjournal as PjournalModel;

class Pjournal extends Base
{
    /*
     * 计划日报列表
     */
    public function index()
    {
        $title = '计划任务列表';
        $pjournalModel = new PjournalModel();
        $data = $pjournalModel->getPjournal();

        return view('index', compact('title', 'data'));
    }

    /*
     * 查看详细任务
     */
    public function show($id)
    {
        $title = '计划任务详细';
        $pjournalModel = new PjournalModel();
        $data = $pjournalModel->pjournalData($id);

        if ($data) {
            return view('show', compact('title', 'data'));
        } else {
            $this->error('您没有权限查看此任务', 'pjournal/index');
        }
    }

    /*
     * 修改行为
     */
    public function edit($id)
    {
        $data = input('post.');
        $file = request()->file('complete');
        $pjournalModel = new PjournalModel();
        $result = $pjournalModel->edit($id, $data, $file);

        $this->success('修改成功', 'pjournal/index');
    }

    /*
     * 删除行为
     */
    public function delete($id)
    {
        // 判断当前用户有没有权限操作这篇文章
        $pjournalModel = new PjournalModel();
        $data = $pjournalModel->dePjournal($id);
        if ($data) {
            $this->success('删除成功', 'pjournal/index');
        } else {
            $this->error('您没有权限操作此任务', 'pjournal/index');
        }
    }
}
