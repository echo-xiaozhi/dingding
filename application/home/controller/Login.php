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
                $this->success('登录成功', 'index/index');
            }
            $this->error($result, 'index');
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
            $result = $this->validate($data, 'User');
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result, 'login/register');
            }
            $psd = $data['password'];
            $repsd = $data['repeat_password'];
            if ($psd != $repsd) {
                $this->error('两次密码不一致', 'login/register');
            }

            $user = User::get(['username' => $data['username']]);
            if (false != $user) {
                $this->error('用户名已经存在', 'login/register');
            }

            $data['password'] = md5($data['password']);
            $result = User::create($data);
            if ($result) {
                $users = User::get(['username' => $data['username'], 'password' => $data['password']]);
                session('user', $users);
                $this->success('注册成功', '/');
            } else {
                $this->error('注册失败', 'login/register');
            }
        }
    }

    /*
     * 退出
     */
    public function logout()
    {
        session('user', null);
        $this->success('退出成功', 'login/index');
    }
}
