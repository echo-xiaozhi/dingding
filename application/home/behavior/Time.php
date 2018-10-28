<?php

namespace app\home\behavior;

class Time
{
    /*
     * 把时间存入缓存
     */
    public function run(&$params)
    {
        //当前时间 windows
        $timeWin = date('Y-m-d');
        // 本周最后一天时间 windows
        $weekLastWin = date('Y-m-d', strtotime("$timeWin Sunday"));
        // 本周第一天 windows
        $weekFirstWin = date('Y-m-d', strtotime("$weekLastWin - 6 days"));
        // 下周第一天 windows
        $nextWeekWin = date('Y-m-d', strtotime("$weekLastWin + 1 day"));
        // 本周第一天 unix
        $weekFirstUinx = strtotime($weekFirstWin);
        // 本周最后一天 unix
        $weekLastUinx = strtotime($weekLastWin);
        //下周第一天 unix
        $nextWeekUinx = strtotime($nextWeekWin);
        // 本月起始时间 unix
        $monthFirstUinx = strtotime(date('Y-m-01', strtotime(date('Y-m-d'))));
        // 本月结束时间 unix
        $monthFirstWin = date('Y-m-01', strtotime(date('Y-m-d')));
        $monthLastUinx = strtotime(date('Y-m-d', strtotime("$monthFirstWin +1 month -1 day")));
        //存入缓存
        cache('weekFirst', $weekFirstUinx);
        cache('weekLast', $weekLastUinx);
        cache('nextWeekFirst', $nextWeekUinx);
        cache('monthFirst', $monthFirstUinx);
        cache('monthLast', $monthLastUinx);
    }
}
