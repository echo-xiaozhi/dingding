<?php

namespace app\home\controller;

use think\Db;
use app\home\model\Tjournal as TjournalModel;

class Tjournal extends Base
{
    /*
     * 临时日报列表
     */
    public function index()
    {
        $title = '临时任务列表';
        $user_id = session('user')->id;
        $data = Db::name('user_tjournal')
            ->alias('a')
            ->join('tjournal b', 'a.tjournal_id = b.id')
            ->where('a.user_id', '=', $user_id)
            ->order('b.unix_time', 'desc')
            ->paginate(10);

        return view('index', compact('title', 'data'));
    }

    /*
     * 查看详细任务
     */
    public function show($id)
    {
        $title = '临时任务详细';
        $user_id = session('user')->id;
        $data = Db::name('user_tjournal')
            ->alias('a')
            ->join('tjournal b', 'a.tjournal_id = b.id')
            ->where('b.id', 'eq', $id)
            ->where('a.user_id', 'eq', $user_id)
            ->find();
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
        if ($file) {
            $upload = new Report();
            $data['complete'] = $upload->upload($file);
        }
        if (array_key_exists('timestart', $data)) {
            $data['timestart'] = strtotime($data['timestart']);
        }
        if (array_key_exists('timend', $data)) {
            $data['timend'] = strtotime($data['timend']);
        }
        $where = [
            'id' => $id,
        ];
        // 写入plan表
        TjournalModel::update($data, $where);
        $this->success('修改成功', 'tjournal/index');
    }

    /*
     * 删除行为
     */
    public function delete($id)
    {
        // 判断当前用户有没有权限操作这篇文章
        $user_id = session('user')->id;
        $data = Db::name('user_tjournal')
            ->alias('a')
            ->join('tjournal b', 'a.tjournal_id = b.id')
            ->where('a.user_id', 'eq', $user_id)
            ->where('b.id', 'eq', $id)
            ->find();
        if ($data) {
            TjournalModel::destroy($id);
            Db::name('user_tjournal')->where('id', 'eq', $data['id'])->delete();
            $this->success('删除成功', 'tjournal/index');
        } else {
            $this->error('您没有权限操作此任务', 'tjournal/index');
        }
    }
}
