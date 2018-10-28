<?php

namespace app\home\model;

use app\Upload\Upload;
use think\Model;

class Pjournal extends Model
{
    /*
     * 获取用户计划任务
     */
    public function getPjournal()
    {
        $user_id = session('user')->id;
        $pjournalData = $this->alias('a')
            ->join('user_pjournal b', 'a.id = b.pjournal_id')
            ->where('b.user_id', 'eq', $user_id)
            ->order('a.unix_time', 'desc')
            ->paginate(10);

        return $pjournalData;
    }

    /*
     * 查看用户任务详情
     */
    public function pjournalData($id)
    {
        $user_id = session('user')->id;
        $pjournalData = $this->alias('a')
            ->join('user_pjournal b', 'a.id = b.pjournal_id')
            ->where('a.id', 'eq', $id)
            ->where('b.user_id', 'eq', $user_id)
            ->find();

        return $pjournalData;
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
    public function dePjournal($id)
    {
        // 判断当前用户有没有权限操作这篇文章
        $data = $this->pjournalData($id);
        if ($data) {
            $result = self::destroy($id);
            $userPjournal = new UserPjournal();
            $userPjournal->where('id', 'eq', $data['id'])->delete();

            return $result;
        }
    }
}
