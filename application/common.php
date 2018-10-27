<?php
/*
 * 导出excel
 * @param string $fileName 文件名称
 * @param array $headArr 表头名称
 * @param array $data 表数据
 * Author: Jason_xiaozhi
 */
function toExcell($fileName = '', $headArr = [], $data = [])
{
    $fileName .= '_'.date('Y_m_d', \think\Request::instance()->time()).'.xls';

    $objPHPExcel = new \PHPExcel();

    $objPHPExcel->getProperties();

    $key = ord('A'); // 设置表头

    foreach ($headArr as $v) {
        $colum = chr($key);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum.'1', $v);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum.'1', $v);

        ++$key;
    }

    $column = 2;

    $objActSheet = $objPHPExcel->getActiveSheet();

    foreach ($data as $key => $rows) { // 行写入
        $span = ord('A');

        foreach ($rows as $keyName => $value) { // 列写入
            $objActSheet->setCellValue(chr($span).$column, $value);

            ++$span;
        }

        ++$column;
    }

    $fileName = iconv('utf-8', 'gb2312', $fileName); // 重命名表

    $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表

    header('Content-Type: application/vnd.ms-excel');

    header("Content-Disposition: attachment;filename='$fileName'");

    header('Cache-Control: max-age=0');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    $objWriter->save('php://output'); // 文件通过浏览器下载

    exit();
}

/*
 * 导出自定义格式的excel表格
 * @param string $name 表格名称
 * @param array $pjournal 计划任务列表
 * @param array $tjournal 临时任务列表
 * @param array $plan 下周计划列表
 * @param array $problem 问题列表
 */
function Custom_Excel($name = '', $pjournal = [], $tjournal = [], $plan = [], $problem = [])
{
    $name .= '_'.date('Y_m_d', \think\Request::instance()->time()).'.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename='$name'");

    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body{
                font-family: "微软雅黑";
                margin: 0;
                padding: 0;
                font-size: 12px;
            }
            table{
                border-collapse:collapse;
            }
            th, td{
                border: 1px solid #BDD7EE;
                text-align: center;
            }
        </style>
    </head>
    <body>
    <table width="1440">
        <tr>
            <th colspan="12" style="font-size: 22px;">山东锦尚网络科技有限公司（运营部）周报</th>
        </tr>
        <tr>
            <th colspan="12" style="font-size: 12px;">填报单位:____________ 填报人:_______ 审阅人：_______ 填报时间___月 第___周  (____年__月__日-____年__月__日)</th>
        </tr>
        <tr>
            <th colspan="12" style="background-color: #BDD7EE;">本周计划完成情况</th>
        </tr>
        <tr>
            <th>序号</th>
            <th style="width: 50px;">类型</th>
            <th>计划任务</th>
            <th>完成输出物</th>
            <th>计划开始时间</th>
            <th>计划完成时间</th>
            <th>优先级</th>
            <th>完成度</th>
            <th>负责人</th>
            <th>参与人</th>
            <th>核查人</th>
            <th>问题备注</th>
        </tr>';
    foreach ($pjournal as $key => $val) {
        echo '
            <tr>
                <td>'.($key + 1).'</td>';
        if (0 == $key) {
            echo '<td rowspan="'.count($pjournal, 0).'">计划任务</td>';
        }
        echo '
            <td>'.$val['title'].'</td>';
        if (!empty($val['complete'])) {
            echo '<td>'.$_SERVER['HTTP_HOST'].$val['complete'].'</td>';
        } else {
            echo '<td></td>';
        }
        if (!empty($val['timestart']) && !empty($val['timend'])) {
            echo '
            <td>'.date('Y-m-d', $val['timestart']).'</td>
            <td>'.date('Y-m-d', $val['timend']).'</td>
            ';
        } else {
            echo '
                <td></td>
                <td></td>
            ';
        }
        echo '
            <td>'.$val['priority'].'</td>
            <td>'.$val['complate'].'</td>
            <td>'.$val['charge'].'</td>
            <td>'.$val['partake'].'</td>
            <td>'.$val['check'].'</td>
            <td>'.$val['remark'].'</td>
        </tr>';
    }
    foreach ($tjournal as $key => $val) {
        echo '
            <tr>
                <td>'.($key + 1).'</td>';
        if (0 == $key) {
            echo '<td rowspan="'.count($tjournal, 0).'">计划任务</td>';
        }
        echo '
            <td>'.$val['title'].'</td>';
        if (!empty($val['complete'])) {
            echo '<td>'.$_SERVER['HTTP_HOST'].$val['complete'].'</td>';
        } else {
            echo '<td></td>';
        }
        if (!empty($val['timestart']) && !empty($val['timend'])) {
            echo '
            <td>'.date('Y-m-d', $val['timestart']).'</td>
            <td>'.date('Y-m-d', $val['timend']).'</td>
            ';
        } else {
            echo '
                <td></td>
                <td></td>
            ';
        }
        echo '
            <td>'.$val['priority'].'</td>
            <td>'.$val['complate'].'</td>
            <td>'.$val['charge'].'</td>
            <td>'.$val['partake'].'</td>
            <td>'.$val['check'].'</td>
            <td>'.$val['remark'].'</td>
        </tr>';
    }
    echo '
        <tr>
            <th colspan="12" style="background-color: #E2F0D9;">下周工作计划安排</th>
        </tr>
        <tr>
            <th>序号</th>
            <th style="width: 50px;">类型</th>
            <th>计划任务</th>
            <th>完成输出物</th>
            <th>计划开始时间</th>
            <th>计划完成时间</th>
            <th>优先级</th>
            <th>完成度</th>
            <th>负责人</th>
            <th>参与人</th>
            <th>核查人</th>
            <th>问题备注</th>
        </tr>';
    foreach ($plan as $key => $val) {
        echo '
            <tr>
                <td>'.($key + 1).'</td>';
        if (0 == $key) {
            echo '<td rowspan="'.count($plan, 0).'">计划任务</td>';
        }
        echo '
            <td>'.$val['plan_info'].'</td>';
        if (!empty($val['complete'])) {
            echo '<td>'.$_SERVER['HTTP_HOST'].$val['complete'].'</td>';
        } else {
            echo '<td></td>';
        }
        echo'
            <td>'.$val['start_time'].'</td>
            <td>'.$val['end_time'].'</td>
            <td>'.$val['priority'].'</td>
            <td>'.$val['complate'].'</td>
            <td>'.$val['charge'].'</td>
            <td>'.$val['partake'].'</td>
            <td>'.$val['check'].'</td>
            <td>'.$val['remark'].'</td>
        </tr>';
    }
    echo '
        <tr>
            <th colspan="12" style="background-color: #FBE5D6;">出现的问题以及解决方案</th>
        </tr>
        <tr>
            <th>编号</th>
            <th colspan="4">问题描述</th>
            <th colspan="3">提出人</th>
            <th>责任人</th>
            <th>解决时间期限</th>
            <th colspan="2">工作组成员</th>
        </tr>
    ';
    foreach ($problem as $key => $val) {
        echo '
        <tr>
            <td>'.($key + 1).'</td>
            <td colspan="4">'.$val['problem_info'].'</td>
            <td colspan="3">'.$val['propose'].'</td>
            <td>'.$val['person'].'</td>
            <td>'.$val['solve_time'].'</td>
            <td colspan="2">'.$val['work_people'].'</td>
        </tr>
        ';
    }
    echo '
        <tr>
            <td>说明</td>
            <td colspan="11" style="text-align: left;">
                1.在制定计划时必须与直接责任人沟通具体安排，产出定义以及具体执行时间。<br />
                2.多人协同完成的工作，责任人只能有一人，必须明确责任。<br />
                3.重点项：<br />
                （1）完成输出物：必须为具体文件(包含但不仅限于：文本/图片/代码等)<br />
                (2)  优先级依次为：A:重要紧急B:紧急但不重要C:重要但不紧急D:一般性E:自主性<br />
                (3)  完成度：任务未开始（0%），任务执行中(n%),任务执行完成(完成日期)<br />
                4.组长严密把控和跟踪任务过程，及时发现问题及时解决，完成后认真核查，客观考核。<br />
                5.任务出现延迟，必须有备注原因和问题解决情况。<br />
            </td>
        </tr>
    </table>
    </body>
    </html>
';
}

/*
 * 导出自定义格式的excel表格月报
 * @param string $name 表格名称
 * @param array $pjournal 计划任务列表
 * @param array $tjournal 临时任务列表
 * @param array $problem 问题列表
 */
function Custom_Mouth_Excel($name = '', $pjournal = [], $tjournal = [], $problem = [])
{
    $name .= '_'.date('Y_m_d', \think\Request::instance()->time()).'.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename='$name'");

    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body{
                font-family: "微软雅黑";
                margin: 0;
                padding: 0;
                font-size: 12px;
            }
            table{
                border-collapse:collapse;
            }
            th, td{
                border: 1px solid #BDD7EE;
                text-align: center;
            }
        </style>
    </head>
    <body>
    <table width="1440">
        <tr>
            <th colspan="11" style="font-size: 22px;">山东锦尚网络科技有限公司（运营部） '.date('m').'  月份工作汇报</th>
        </tr>
        <tr>
            <th colspan="11" style="font-size: 12px;">填报单位:____________ 填报人:_______ 审阅人：_______ 填报时间_____年__月__日</th>
        </tr>
        <tr>
            <th colspan="11" style="background-color: #BDD7EE;">本周计划完成情况</th>
        </tr>
        <tr>
            <th>序号</th>
            <th style="width: 50px;">类型</th>
            <th>工作任务</th>
            <th>完成描述</th>
            <th>任务开始时间</th>
            <th>任务完成时间</th>
            <th>权重</th>
            <th>完成度</th>
            <th>负责人</th>
            <th>核查人</th>
            <th>问题备注</th>
        </tr>';
    foreach ($pjournal as $key => $val) {
        echo '
            <tr>
                <td>'.($key + 1).'</td>';
        if (0 == $key) {
            echo '<td rowspan="'.count($pjournal, 0).'">计划任务</td>';
        }
        echo '
            <td>'.$val['title'].'</td>';
        if (!empty($val['complete'])) {
            echo '<td>'.$_SERVER['HTTP_HOST'].$val['complete'].'</td>';
        } else {
            echo '<td></td>';
        }
        if (!empty($val['timestart']) && !empty($val['timend'])) {
            echo '
            <td>'.date('Y-m-d', $val['timestart']).'</td>
            <td>'.date('Y-m-d', $val['timend']).'</td>
            ';
        } else {
            echo '
                <td></td>
                <td></td>
            ';
        }
        echo '
            <td>'.$val['priority'].'</td>
            <td>'.$val['complate'].'</td>
            <td>'.$val['charge'].'</td>
            <td>'.$val['check'].'</td>
            <td>'.$val['remark'].'</td>
        </tr>';
    }
    foreach ($tjournal as $key => $val) {
        echo '
            <tr>
                <td>'.($key + 1).'</td>';
        if (0 == $key) {
            echo '<td rowspan="'.count($tjournal, 0).'">计划任务</td>';
        }
        echo '
            <td>'.$val['title'].'</td>';
        if (!empty($val['complete'])) {
            echo '<td>'.$_SERVER['HTTP_HOST'].$val['complete'].'</td>';
        } else {
            echo '<td></td>';
        }
        if (!empty($val['timestart']) && !empty($val['timend'])) {
            echo '
            <td>'.date('Y-m-d', $val['timestart']).'</td>
            <td>'.date('Y-m-d', $val['timend']).'</td>
            ';
        } else {
            echo '
                <td></td>
                <td></td>
            ';
        }
        echo '
            <td>'.$val['priority'].'</td>
            <td>'.$val['complate'].'</td>
            <td>'.$val['charge'].'</td>
            <td>'.$val['check'].'</td>
            <td>'.$val['remark'].'</td>
        </tr>';
    }
    echo '
        <tr>
            <th colspan="11" style="background-color: #FBE5D6;">出现的问题以及解决方案</th>
        </tr>
        <tr>
            <th colspan="5">问题描述</th>
            <th>提出人</th>
            <th colspan="5">解决方案</th>
        </tr>
    ';
    foreach ($problem as $key => $val) {
        echo '
        <tr>
            <td colspan="5">'.$val['problem_info'].'</td>
            <td>'.$val['propose'].'</td>
            <td colspan="5">'.$val['programme'].'</td>
        </tr>
        ';
    }
    echo '
        <tr>
            <td colspan="2">说明</td>
            <td colspan="9" style="text-align: left;">
                1.在制定计划时必须与直接责任人沟通具体安排，产出定义以及具体执行时间。<br />
                2.多人协同完成的工作，责任人只能有一人，必须明确责任。<br />
                3.重点项：<br />
                （1）完成输出物：必须为具体文件(包含但不仅限于：文本/图片/代码等)<br />
                (2)  优先级依次为：A:重要紧急B:紧急但不重要C:重要但不紧急D:一般性E:自主性<br />
                (3)  完成度：任务未开始（0%），任务执行中(n%),任务执行完成(完成日期)<br />
                4.组长严密把控和跟踪任务过程，及时发现问题及时解决，完成后认真核查，客观考核。<br />
                5.任务出现延迟，必须有备注原因和问题解决情况。<br />
            </td>
        </tr>
    </table>
    </body>
    </html>
';
}
