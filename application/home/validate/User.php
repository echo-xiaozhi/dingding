<?php

namespace app\home\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'username' => 'require|min:3|max:15',
        'password' => 'require|min:6|max:30',
        'repeat_password' => 'require',
    ];

    protected $message = [
        'username.require' => '用户名必须填写',
        'username.min' => '用户名不得少于3位',
        'username.max' => '用户名不得多于15位',
        'password.require' => '密码必须存在',
        'password.min' => '密码不得少于6位',
        'password.max' => '密码不得多于30位',
        'repeat_password' => '没有再次输入密码',
    ];

    protected $scene = [
        'add' => ['username', 'password'],
        'edit' => ['password'],
    ];
}
