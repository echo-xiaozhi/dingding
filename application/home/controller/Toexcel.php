<?php

namespace app\home\controller;

use app\home\model\Pjournal;
use app\home\model\Tjournal;
use app\home\model\Plan;
use app\home\model\Problem;

class Toexcel extends Base
{
    /*
     * 周报预览
     */
    public function index()
    {
        $title = '导出周报预览';
        // 钉钉计划任务
        $pjournalModel = new Pjournal();
        $pjournal = $pjournalModel->getWeekPjournal();
        // 钉钉临时任务
        $tjournalModel = new Tjournal();
        $tjournal = $tjournalModel->getWeekTjournal();
        // 下周计划列表
        $planModel = new Plan();
        $plan = $planModel->nextWeekPlan();
        // 本周问题
        $problemModel = new Problem();
        $problem = $problemModel->getProblem();

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
        $name = '周报';
        // 钉钉计划任务
        $pjournalModel = new Pjournal();
        $pjournal = $pjournalModel->getWeekPjournal();
        // 钉钉临时任务
        $tjournalModel = new Tjournal();
        $tjournal = $tjournalModel->getWeekTjournal();
        // 下周计划列表
        $planModel = new Plan();
        $plan = $planModel->nextWeekPlan();
        // 本周问题
        $problemModel = new Problem();
        $problem = $problemModel->getProblem();

        Custom_Excel($name, $pjournal, $tjournal, $plan, $problem);
    }

    /*
     * 月报预览
     */
    public function mouth()
    {
        $title = '导出月报预览';
        //本月所有计划任务
        $pjournalModel = new Pjournal();
        $mouth_pjournal = $pjournalModel->getMonthPjournal();
        //本月所有临时任务
        $tjournalModel = new Tjournal();
        $mouth_tjournal = $tjournalModel->getMonthTjournal();
        // 本月所有问题
        $problemModel = new Problem();
        $mouth_problem = $problemModel->getMonthProblem();

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
        // 表格名称
        $name = '月报';
        //本月所有计划任务
        $pjournalModel = new Pjournal();
        $mouth_pjournal = $pjournalModel->getMonthPjournal();
        //本月所有临时任务
        $tjournalModel = new Tjournal();
        $mouth_tjournal = $tjournalModel->getMonthTjournal();
        // 本月所有问题
        $problemModel = new Problem();
        $mouth_problem = $problemModel->getMonthProblem();

        Custom_Mouth_Excel($name, $mouth_pjournal, $mouth_tjournal, $mouth_problem);
    }

    /*
     * 判定选择月份的起始时间
     */
    protected function getThemMonth($date)
    {
        $firstday = date('Y-m-01', strtotime($date)); //月初
        $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));//月末
        return [
            'monthFirstDay' => $firstday,
            'monthLastDay' => $lastday
        ];
    }

}
