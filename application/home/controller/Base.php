<?php

namespace app\home\controller;

use think\Controller;
use app\home\model\User;

class Base extends Controller
{
    /*
     * 判断用户是否登录
     */
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $user = session('user');
        if (!$user) {
            $this->redirect('login/index');
        }
        $user_info = User::get(['id' => $user['id']]);
        $this->assign('user_info', $user_info);
        $this->fetch('layout/nav');
    }
}
