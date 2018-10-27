<?php

namespace app\home\controller;

use app\home\model\User;

class Repeat extends Base
{
    /*
     * 修改密码
     */
    public function repeat_password($id)
    {
        $title = '修改密码';
        if (request()->isPost()) {
            $data = input('post.');
            $user = User::get($id);
            if ($user['password'] != md5($data['yuan_password'])) {
                $this->error('原密码不正确，请重新输入', '/repeat/repeat_password/id/'.$id);
            }

            if ($data['password'] != $data['reset_password']) {
                $this->error('重复输入密码不一致', '/repeat/repeat_password/id/'.$id);
            }

            $data = [
                'password' => md5($data['password']),
            ];
            $where = [
                'id' => $id,
            ];
            $result = User::update($data, $where);
            if ($result) {
                $this->success('修改成功', '/user');
            } else {
                $this->error('修改失败', '/repeat/repeat_password/id/'.$id);
            }
        } else {
            return view('repeat', compact('title'));
        }
    }
}
