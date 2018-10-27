<?php

namespace app\home\controller;

use app\home\model\User as UserModel;

class User extends Base
{
    /*
     * 用户个人信息
     */
    public function index()
    {
        $title = '用户中心';
        $userModel = new UserModel();
        // 获取用户个人信息
        $user = $userModel->get(session('user')->id);
        $user_id = $user->id;
        // 获取用户本周日报数量
        $weekJournalCount = $userModel->weekJournalCount($user_id);
        // 获取用户本月日报数量
        $monthJournalCount = $userModel->monthJournalCount($user_id);
        // 获取用户所有日报数量
        $journalCount = $userModel->journalCoutn($user_id);

        return view('index', compact('title', 'user', 'weekJournalCount', 'monthJournalCount', 'journalCount'));
    }

    /*
     * 修改用户头像
     */
    public function setUserImg()
    {
        $file = request()->file('image');
        $userModel = new UserModel();

        return $userModel->setUserImg($file);
    }

    /*
     * 绑定钉钉
     */
    public function bindDing()
    {
        if (array_key_exists('code', $_GET) && array_key_exists('state', $_GET)) {
            $code = $_GET['code'];
            $userModel = new UserModel();
            $userModel->bindDing($code);

            return redirect('/user');
        }
    }
}
