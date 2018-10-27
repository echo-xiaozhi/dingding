<?php

namespace app\home\controller;

use think\Db;

class Toexcel extends Base
{
    /*
     * 周报预览
     */
    public function index()
    {
        $title = '导出周报预览';
        $user_id = session('user')->id;
        // 获取当前时间，获取下周任务
        $time = date('Y-m-d'); //当前时间
        $lastday = date('Y-m-d', strtotime("$time Sunday")); // 本周最后一天时间
        $first = date('Y-m-d', strtotime("$lastday - 6 days")); // 本周第一天
        $next = date('Y-m-d', strtotime("$lastday + 1 day")); // 下周第一天
        $start = strtotime($first);
        $end = strtotime($lastday);
        $next_date = strtotime($next);
        // 钉钉计划任务
        $pjournal = Db::name('user_pjournal')
            ->alias('a')
            ->join('pjournal b', 'a.pjournal_id = b.id')
            ->where('b.unix_time', '<=', $end)
            ->where('b.unix_time', '>=', $start)
            ->where('a.user_id', '=', $user_id)
            ->select();
        // 钉钉临时任务
        $tjournal = Db::name('user_tjournal')
            ->alias('a')
            ->join('tjournal b', 'a.tjournal_id = b.id')
            ->where('b.unix_time', '<=', $end)
            ->where('b.unix_time', '>=', $start)
            ->where('a.user_id', '=', $user_id)
            ->select();
        // 下周计划列表
        $plan = Db::name('user_plan')
            ->alias('a')
            ->join('plan b', 'a.plan_id = b.id')
            ->where('b.plan_time', '>=', $next_date)
            ->where('a.user_id', '=', $user_id)
            ->select();
        // 本周问题
        $problem = Db::name('user_problem')
            ->alias('a')
            ->join('problem b', 'a.problem_id = b.id')
            ->where('b.create_times', '<=', $end)
            ->where('b.create_times', '>=', $start)
            ->where('a.user_id', '=', $user_id)
            ->select();

        return view('index', compact('title', 'pjournal', 'tjournal', 'plan', 'problem'));
    }

    /*
     * 导出自定义格式的周报excel表格
     * @param string $name 表格名称
     * @param array $pjournal 计划任务列表
     * @param array $tjournal 临时任务列表
     * @param array $plan 下周计划列表
     * @param array $problem 问题列表
     */
    public function to_excel()
    {
        $user_id = session('user')->id;
        // 获取当前时间，获取下周任务
        $time = date('Y-m-d'); //当前时间
        $lastday = date('Y-m-d', strtotime("$time Sunday")); // 本周最后一天时间
        $first = date('Y-m-d', strtotime("$lastday - 6 days")); // 本周第一天
        $next = date('Y-m-d', strtotime("$lastday + 1 day")); // 下周第一天
        $start = strtotime($first);
        $end = strtotime($lastday);
        $next_date = strtotime($next);
        $name = '周报';
        // 钉钉计划任务
        $pjournal = Db::name('user_pjournal')
            ->alias('a')
            ->join('pjournal b', 'a.pjournal_id = b.id')
            ->where('b.unix_time', '<=', $end)
            ->where('b.unix_time', '>=', $start)
            ->where('a.user_id', '=', $user_id)
            ->select();
        // 钉钉临时任务
        $tjournal = Db::name('user_tjournal')
            ->alias('a')
            ->join('tjournal b', 'a.tjournal_id = b.id')
            ->where('b.unix_time', '<=', $end)
            ->where('b.unix_time', '>=', $start)
            ->where('a.user_id', '=', $user_id)
            ->select();
        // 下周计划列表
        $plan = Db::name('user_plan')
            ->alias('a')
            ->join('plan b', 'a.plan_id = b.id')
            ->where('b.plan_time', '>=', $next_date)
            ->where('a.user_id', '=', $user_id)
            ->select();
        // 本周问题
        $problem = Db::name('user_problem')
            ->alias('a')
            ->join('problem b', 'a.problem_id = b.id')
            ->where('b.create_times', '<=', $end)
            ->where('b.create_times', '>=', $start)
            ->where('a.user_id', '=', $user_id)
            ->select();
        Custom_Excel($name, $pjournal, $tjournal, $plan, $problem);
    }

    /*
     * 月报预览
     */
    public function mouth()
    {
        $title = '导出月报预览';
        $user_id = session('user')->id;
        $beginThismonth = strtotime(date('Y-m-01', strtotime(date('Y-m-d')))); // 本月起始时间 unix
        $begin = date('Y-m-01', strtotime(date('Y-m-d')));
        $endThismonth = strtotime(date('Y-m-d', strtotime("$begin +1 month -1 day"))); // 本月结束时间 unix

        //本月所有计划任务
        $mouth_pjournal = Db::name('user_pjournal')
            ->alias('a')
            ->join('pjournal b', 'a.pjournal_id = b.id')
            ->where('user_id', 'eq', $user_id)
            ->where('b.unix_time', '>=', $beginThismonth)
            ->where('b.unix_time', '<=', $endThismonth)
            ->select();
        //本月所有临时任务
        $mouth_tjournal = Db::name('user_tjournal')
            ->alias('a')
            ->join('tjournal b', 'a.tjournal_id = b.id')
            ->where('user_id', 'eq', $user_id)
            ->where('b.unix_time', '>=', $beginThismonth)
            ->where('b.unix_time', '<=', $endThismonth)
            ->select();
        // 本月所有问题
        $mouth_problem = Db::name('user_problem')
            ->alias('a')
            ->join('problem b', 'a.problem_id = b.id')
            ->where('b.create_times', '<=', $endThismonth)
            ->where('b.create_times', '>=', $beginThismonth)
            ->where('a.user_id', '=', $user_id)
            ->select();

        return view('mouth', compact('title', 'mouth_pjournal', 'mouth_tjournal', 'mouth_problem'));
    }

    /*
     * 导出自定义格式的周报excel表格月报
     * @param string $name 表格名称
     * @param array $pjournal 计划任务列表
     * @param array $tjournal 临时任务列表
     * @param array $problem 问题列表
     */
    public function to_mouth_excel()
    {
        $user_id = session('user')->id;
        $beginThismonth = strtotime(date('Y-m-01', strtotime(date('Y-m-d')))); // 本月起始时间 unix
        $begin = date('Y-m-01', strtotime(date('Y-m-d')));
        $endThismonth = strtotime(date('Y-m-d', strtotime("$begin +1 month -1 day"))); // 本月结束时间 unix
        // 表格名称
        $name = '月报';

        //本月所有计划任务
        $mouth_pjournal = Db::name('user_pjournal')
            ->alias('a')
            ->join('pjournal b', 'a.pjournal_id = b.id')
            ->where('user_id', 'eq', $user_id)
            ->where('b.unix_time', '>=', $beginThismonth)
            ->where('b.unix_time', '<=', $endThismonth)
            ->select();
        //本月所有临时任务
        $mouth_tjournal = Db::name('user_tjournal')
            ->alias('a')
            ->join('tjournal b', 'a.tjournal_id = b.id')
            ->where('user_id', 'eq', $user_id)
            ->where('b.unix_time', '>=', $beginThismonth)
            ->where('b.unix_time', '<=', $endThismonth)
            ->select();
        // 本月所有问题
        $mouth_problem = Db::name('user_problem')
            ->alias('a')
            ->join('problem b', 'a.problem_id = b.id')
            ->where('b.create_times', '<=', $endThismonth)
            ->where('b.create_times', '>=', $beginThismonth)
            ->where('a.user_id', '=', $user_id)
            ->select();
        Custom_Mouth_Excel($name, $mouth_pjournal, $mouth_tjournal, $mouth_problem);
    }
}
