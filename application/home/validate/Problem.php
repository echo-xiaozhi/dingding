<?php

namespace app\home\validate;

use think\Validate;

class Problem extends Validate
{
    protected $rule = [
        'problem_info' => 'require',
        'person' => 'require',
        'programme' => 'require',
    ];

    protected $message = [
        'problem_info.require' => '问题描述必须写',
        'person.require' => '负责人必须写',
        'programme.require' => '解决方案必须填写',
    ];
}
