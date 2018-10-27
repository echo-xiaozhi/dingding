<?php

namespace app\home\controller;

class Index extends Base
{
    /*
     * 用户中心首页
     */
    public function index()
    {
        $title = '用户中心首页';

        return view('index', compact('title'));
    }
}
