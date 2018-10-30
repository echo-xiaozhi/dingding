<?php

namespace app\home\controller;

use app\home\model\User;

class Report extends Base
{
    /*
     * 导入最新日报去重
     */
    public function times()
    {
        $userId = User::get(session('user')->id)->user_id;
        if (!empty($userId)) {
            $userModel = new User();
            $userData = $userModel->unqiueData($userId);
            $title = '日志列表';

            return view('in', compact('title', 'userData'));
        } else {
            $this->error('请先绑定钉钉', 'home/user/index');
        }
    }

    /*
     * 钉钉日报第二步存入数据库
     */
    public function addend()
    {
        if (request()->isPost()) {
            $data = input('post.');
            // 逻辑
            $userModel = new User();
            $userModel->insertTask($data);
            $this->success('导入成功', '/pjournal/index');
        } else {
            $this->error('错误操作，请返回重新操作', 'home/report/times');
        }
    }
}
