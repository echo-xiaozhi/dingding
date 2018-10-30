<?php

namespace app\home\controller;

use app\home\model\Tjournal as TjournalModel;

class Tjournal extends Base
{
    /*
     * 临时日报列表
     */
    public function index()
    {
        $title = '临时任务列表';
        $tjournalModel = new TjournalModel();
        $data = $tjournalModel->getTjournal();

        return view('index', compact('title', 'data'));
    }

    /*
     * 查看详细任务
     */
    public function show($id)
    {
        $title = '临时任务详细';
        $tjournalModel = new TjournalModel();
        $data = $tjournalModel->tjournalData($id);

        if ($data) {
            return view('show', compact('title', 'data'));
        } else {
            $this->error('您没有权限查看此任务', 'tjournal/index');
        }
    }

    /*
     * 修改行为
     */
    public function edit($id)
    {
        $data = input('post.');
        $file = request()->file('complete');
        $tjournalModel = new TjournalModel();
        $tjournalModel->edit($id, $data, $file);

        $this->success('修改成功', 'tjournal/index');
    }

    /*
     * 删除行为
     */
    public function delete($id)
    {
        $tjournalModel = new TjournalModel();
        $data = $tjournalModel->deTjournal($id);

        if ($data) {
            return [
                'error' => 0,
                'msg' => '删除成功'
            ];
        } else {
            return [
                'error' => 1,
                '您没有权限操作此任务'
            ];
        }
    }
}
