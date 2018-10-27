<?php

namespace app\home\model;

use think\Cache;
use think\Model;

class UserTjournal extends Model
{
    public function tjournal()
    {
        return $this->belongsTo('Tjournal', 'tjournal_id', 'id');
    }

    /*
     * 获取本周临时计划
     */
    public function weekTjournalData($user_id)
    {
        // 获取时间
        $weekFirst = Cache::get('weekFirst');
        $weekLast = Cache::get('weekLast');
        // 根据条件查询
        $tjournalData = $this->alias('a')
            ->join('tjournal b', 'a.tjournal_id = b.id')
            ->where('a.user_id', 'eq', $user_id)
            ->where('b.unix_time', '<=', $weekLast)
            ->where('b.unix_time', '>=', $weekFirst)
            ->select();

        return $tjournalData;
    }

    /*
     * 获取本月临时任务
     */
    public function monthTjournalData($user_id)
    {
        // 获取时间
        $monthFirst = Cache::get('monthFirst');
        $monthLast = Cache::get('monthLast');
        // 根据条件查询
        $tjournalData = $this->alias('a')
            ->join('tjournal b', 'a.tjournal_id = b.id')
            ->where('a.user_id', 'eq', $user_id)
            ->where('b.unix_time', '<=', $monthLast)
            ->where('b.unix_time', '>=', $monthFirst)
            ->select();

        return $tjournalData;
    }

    /*
     * 获取所有临时任务
     */
    public function tjournalData($user_id)
    {
        return $this->alias('a')->join('tjournal b', 'a.tjournal_id = b.id')->where('a.user_id', 'eq', $user_id)->select();
    }
}
