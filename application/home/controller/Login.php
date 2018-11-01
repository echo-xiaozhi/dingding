<?php

namespace app\home\controller;

use app\home\model\User;
use think\Controller;

class Login extends Controller
{
    /*
     * 登录页
     */
    public function index()
    {
        if (session('user')) { //已经登录时直接跳到首页
            $this->success('用户已经登录', '/');
        } else {
            return view('login');
        }
    }

    /*
     * 登录行为
     */
    public function dologin()
    {
        if (request()->isPost()) {
            $data = input('post.');

            $userModel = new User();
            $result = $userModel->login($data);
            if ('success' == $result) {
                return [
                    'error' => 0,
                    'msg' => '登录成功',
                ];
            }

            return [
                'error' => 1,
                'msg' => $result,
            ];
        }
    }

    /*
     * 注册页
     */
    public function register()
    {
        return view('register');
    }

    /*
     * 注册行为
     */
    public function doregister()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $userModel = new User();
            $result = $userModel->register($data);
            if ('success' == $result) {
                return [
                    'error' => 0,
                    'msg' => '',
                ];
            }

            return [
                'error' => 1,
                'msg' => $result,
            ];
        }
    }

    /*
     * 退出
     */
    public function logout()
    {
        session('user', null);
        $this->redirect('login/index');
    }
}
