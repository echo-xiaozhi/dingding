<?php

namespace app\home\controller;

use app\home\model\User;

class Report extends Base
{
    /*
     * 用户选择时间或者自动导入本周
     */
    public function index()
    {
        $userId = User::get(session('user')->id)->user_id;
        if (!empty($userId)) {
            $title = '选择导入方式';

            return view('index', compact('title'));
        } else {
            $this->error('请先绑定钉钉', 'home/user/index');
        }
    }

    /*
     * 用户选择时间导入
     */
    public function userTime()
    {
        $title = '选择时间';

        return view('time', compact('title'));
    }

    /*
     * 导入最新日报去重
     */
    public function times()
    {
        $title = '导入日志列表';
        $userId = User::get(session('user')->id)->user_id;
        $userModel = new User();
        if (request()->isPost()) {
            $data = input('post.');
            $startTime = strtotime($data['start_time']) * 1000;
            $endTime = strtotime($data['end_time']) * 1000;
            $userData = $userModel->unqiueData($userId, $startTime, $endTime, 100);

            return view('in', compact('title', 'userData'));
        }

        $userData = $userModel->unqiueData($userId);

        return view('in', compact('title', 'userData'));
    }

    /*
     * 钉钉日报第二步存入数据库
     */
    public function addend()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if ($data) {
                // 逻辑
                $userModel = new User();
                $userModel->insertTask($data);
                $this->success('导入成功', '/pjournal/index', '', 1);
            }
            $this->error('没有提交任何数据', '/report/times', '', 1);
        } else {
            $this->error('错误操作，请返回重新操作', 'home/report/times');
        }
    }
}
