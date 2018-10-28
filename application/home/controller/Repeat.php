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
            $userModel = new User();
            $result = $userModel->editPassword($id, $data);
            if ($result == 'success') {
                $this->success('修改成功', '/user');
            }

            $this->error($result);
        } else {
            return view('repeat', compact('title'));
        }
    }
}
