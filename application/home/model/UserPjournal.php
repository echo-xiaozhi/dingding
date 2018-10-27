<?php

namespace app\home\model;

use think\Cache;
use think\Model;

class UserPjournal extends Model
{
    /*
     * 获取本周计划任务
     */
    public function weekPjournalData($user_id)
    {
        // 获取时间
        $weekFirst = Cache::get('weekFirst');
        $weekLast = Cache::get('weekLast');
        // 根据条件查询
        $pjournalData = $this->alias('a')
            ->join('pjournal b', 'a.pjournal_id = b.id')
            ->where('a.user_id', 'eq', $user_id)
            ->where('b.unix_time', '<=', $weekLast)
            ->where('b.unix_time', '>=', $weekFirst)
            ->select();

        return $pjournalData;
    }

    /*
     * 获取本月计划任务
     */
    public function monthPjournalData($user_id)
    {
        // 获取时间
        $monthFirst = Cache::get('monthFirst');
        $monthLast = Cache::get('monthLast');
        // 根据条件查询
        $pjournalData = $this->alias('a')
            ->join('pjournal b', 'a.pjournal_id = b.id')
            ->where('a.user_id', 'eq', $user_id)
            ->where('b.unix_time', '<=', $monthLast)
            ->where('b.unix_time', '>=', $monthFirst)
            ->select();

        return $pjournalData;
    }

    /*
     * 获取所有计划任务
     */
    public function pjournalData($user_id)
    {
        return $this->alias('a')->join('pjournal b', 'a.pjournal_id = b.id')->where('a.user_id', 'eq', $user_id)->select();
    }
}
