<?php

namespace app\home\model;

use think\Model;

class Tjournal extends Model
{
    /*
     * 获取用户临时任务
     */
    public function getTjournal()
    {
        $user_id = session('user')->id;
        $tjournalData = $this->alias('a')
            ->join('user_tjournal b', 'a.id = b.tjournal_id')
            ->where('b.user_id', 'eq', $user_id)
            ->order('a.unix_time', 'desc')
            ->paginate(10);

        return $tjournalData;
    }

    /*
     * 查看用户任务详情
     */
    public function tjournalData($id)
    {
        $user_id = session('user')->id;
        $tjournalData = $this->alias('a')
            ->join('user_tjournal b', 'a.id = b.tjournal_id')
            ->where('a.id', 'eq', $id)
            ->where('b.user_id', 'eq', $user_id)
            ->find();

        return $tjournalData;
    }

    /*
     * 修改任务
     */
    public function edit($id, $data, $file)
    {
        if ($file) {
            $upload = new Upload();
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

        $result = self::update($data, $where);
        return $result;
    }

    /*
     * 删除任务
     */
    public function deTjournal($id)
    {
        // 判断当前用户有没有权限操作这篇文章
        $data = $this->tjournalData($id);
        if ($data) {
            $result = self::destroy($id);
            $userTjournal = new UserTjournal();
            $userTjournal->where('id', 'eq', $data['id'])->delete();

            return $result;
        }
    }
}
