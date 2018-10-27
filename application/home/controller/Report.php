<?php

namespace app\home\controller;

use app\home\model\AccessToken;
use app\home\model\User;
use think\Db;

class Report extends Base
{
    /*
     * 日报列表
     */
    public function index()
    {
    }

    /*
     * 选择时间
     */
    public function times()
    {
        $user_id = User::get(session('user')->id)->user_id;
        if (!empty($user_id)) {
            $user_id = session('user')->id;
            // 先找到当前用户最后一篇日报时间
            $end_time_p = Db::name('user_pjournal')
                ->alias('a')
                ->join('pjournal b', 'a.pjournal_id = b.id')
                ->where('a.user_id', '=', $user_id)
                ->order('b.id', 'desc')
                ->limit('0', '1')
                ->field('create_times')
                ->find();
            $end_time_t = Db::name('user_tjournal')
                ->alias('a')
                ->join('tjournal b', 'a.tjournal_id = b.id')
                ->where('a.user_id', '=', $user_id)
                ->order('b.id', 'desc')
                ->limit('0', '1')
                ->field('create_times')
                ->find();
            // 两个表均存在时间
            if (!empty($end_time_p) && !empty($end_time_t)) {
                // 判断哪个时间最新
                if (strtotime($end_time_p['create_times']) > strtotime($end_time_t['create_times'])) {
                    $time = $end_time_p['create_times'];
                // 如果两者相等
                } elseif (strtotime($end_time_p['create_times']) == strtotime($end_time_t['create_times'])) {
                    $time = $end_time_p['create_times'];
                } else {
                    $time = $end_time_t['create_times'];
                }
                // 如果只是计划表存在时间
            } elseif (!empty($end_time_p)) {
                $time = $end_time_p['create_times'];
            // 如果只是临时表存在时间
            } elseif (!empty($end_time_t)) {
                $time = $end_time_p['create_times'];
            } else {
                $time = '';
            }
            $title = '编写日报';

            return view('in', compact('title', 'time'));
        } else {
            $this->error('请先绑定钉钉', 'home/user/index');
        }
    }

    /*
     * 钉钉所有日报选择导入
     */
    public function add()
    {
        if (request()->isPost()) {
            // 获取某段时间的日志详情
            $data = input('post.');
            $start_time = strtotime($data['start_time']) * 1000;
            $end_time = strtotime($data['end_time']) * 1000;
            $user_id = User::get(session('user')->id)->user_id;
            $access_token_user = AccessToken::get(['status' => 1])->access_token;
            $user_day = $this->get_day($access_token_user, $start_time, $end_time, $user_id, $data['num']);
            $user_data = json_decode(strstr($user_day, '{'), true);
            // 日志详情
            if (array_key_exists('errmsg', $user_data)) {
                $this->error('开始时间和结束时间不能超过180天。', 'report/times');
            }
            $user_data_list = $user_data['result']['data_list'];

            $title = '日志列表';

            return view('add', compact('title', 'user_data_list'));
        } else {
            $this->error('不能直接访问，请返回导入', 'home/report/index');
        }
    }

    /*
     * 钉钉日报第二步
     */
    public function addnext()
    {
        if (request()->isPost()) {
            // 获取提交过来的日报信息
            $data = input('post.');
            $title = '第二步';

            return view('addnext', compact('title', 'data'));
        } else {
            $this->error('错误操作，请返回重新操作', 'home/report/index');
        }
    }

    /*
     * 钉钉日报第三步存入数据库
     */
    public function addend()
    {
        if (request()->isPost()) {
            $user_id = session('user')->id;
            $data = input('post.');
            // 逻辑
            for ($i = 0; $i < count($data['create_time']); ++$i) {
                //上传文件
                $file = request()->file('complete'.$i);
                if ($file) {
                    $complete = $this->upload($file); //输出物
                } else {
                    $complete = '';
                }
                $create_times = $data['create_time'][$i]; //钉钉发布时间
                $charge = $data['creator_name'][$i]; //负责人
                $title = $data['title'][$i]; //任务名称
//                $complete = $data['complete'][$i]; //输出物
                $time_start = strtotime($data['time_start'][$i]); //任务开始时间
                $time_end = strtotime($data['time_end'][$i]); //任务结束时间
                $priority = $data['priority'.$i]; //优先级
                $complate = $data['complate'][$i]; //完成度
                $partake = $data['partake'][$i]; //参与人
                $check = $data['check'][$i]; //核查人
                $remark = $data['remark'][$i]; //问题备注
                $datas = [
                    'title' => $title,
                    'complete' => $complete,
                    'timestart' => $time_start,
                    'timend' => $time_end,
                    'priority' => $priority,
                    'complate' => $complate,
                    'charge' => $charge,
                    'partake' => $partake,
                    'check' => $check,
                    'remark' => $remark,
                    'create_times' => $create_times,
                    'unix_time' => strtotime($create_times),
                ];
                // 0计划任务 1临时任务
                if (1 == $data['shuxing'.$i]) {
                    // 写入临时任务表 tjournal
                    $tjournal_id = Db::name('tjournal')->insert($datas, false, true);
                    // 写入关联表
                    $user_data = [
                        'user_id' => $user_id,
                        'tjournal_id' => $tjournal_id,
                    ];
                    Db::name('user_tjournal')->insert($user_data);
                } else {
                    // 写入计划任务表 pjournal
                    $pjournal_id = Db::name('pjournal')->insert($datas, false, true);
                    // 写入关联表
                    $user_data = [
                        'user_id' => $user_id,
                        'pjournal_id' => $pjournal_id,
                    ];
                    Db::name('user_pjournal')->insert($user_data);
                }
            }
            $this->success('导入成功', '/pjournal/index');
        } else {
            $this->error('错误操作，请返回重新操作', 'home/report/times');
        }
    }

    public function show($id)
    {
        // 判断当前用户是否有权限查看此日报
        $user_id = session('user')->id;

        $title = '日报详情';

        return view('show', compact('title'));
    }

    public function test()
    {
        if (request()->isPost()) {
            $file = request()->file('image')[0];
            $data = $this->upload($file);
            dump($data);
        } else {
            $title = 'ceshi';

            return view('test', compact('title'));
        }
    }

    /*
     * 文件上传
     */
    public function upload($file)
    {
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH.'public'.DS.'uploads');
            if ($info) {
                // 成功上传后 获取上传信息
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                return '\uploads\\'.$info->getSaveName();
            } else {
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
    }

    /*
     * 获取当前用户日报
     */
    protected function get_day($access_token = '', $start_time = '', $end_time = '', $userid = '', $size = 10, $template_name = '日报', $cursor = 0)
    {
        $url = 'https://oapi.dingtalk.com/topapi/report/list?access_token='.$access_token;
        //初始化
        $curl = curl_init();
        $header = array('Content-Type: application/json; charset=utf-8');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //绕过ssl验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 2);
        //设置post数据
        $post_data = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'template_name' => $template_name,
            'userid' => $userid,
            'cursor' => $cursor,
            'size' => $size,
        ];
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }
}
