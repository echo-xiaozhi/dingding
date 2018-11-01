<?php

namespace app\home\model;

use think\Cache;
use think\Model;
use app\Upload\Upload;
use think\Validate;

class User extends Model
{
    /*
     * 用户关联问题 一对多
     */
    public function userProblem()
    {
        return $this->hasMany('UserProblem', 'user_id', 'id');
    }

    /*
     * 用户关联计划任务表 一对多
     */
    public function userPjournal()
    {
        return $this->hasMany('UserPjournal', 'user_id', 'id');
    }

    /*
     * 用户关联临时任务表 一对多
     */
    public function userTjournal()
    {
        return $this->hasMany('UserTjournal', 'user_id', 'id');
    }

    /*
     * 用户关联下周计划表 一对多
     */
    public function userPlan()
    {
        return $this->hasMany('UserPlan', 'user_id', 'id');
    }

    // 调出用户关联问题表id
//    public function getProblemId($id)
//    {
//        return self::with(['userProblem', 'userProblem.problem'])->find($id);
//    }

    /*
     * 本周日志数量
     */
    public function weekJournalCount($id)
    {
        $userPjournal = new UserPjournal();
        $userTjournal = new UserTjournal();
        // 本周计划日报数量
        $userPjournalCount = count($userPjournal->weekPjournalData($id));
        // 本周临时日报数量
        $userTjournalCount = count($userTjournal->weekTjournalData($id));
        $sumCount = $userPjournalCount + $userTjournalCount;

        return $sumCount;
    }

    /*
     * 本月日志数量
     */
    public function monthJournalCount($id)
    {
        $userPjournal = new UserPjournal();
        $userTjournal = new UserTjournal();
        // 本月计划日报数量
        $userPjournalCount = count($userPjournal->monthPjournalData($id));
        // 本月临时日报数量
        $userTjournalCount = count($userTjournal->monthTjournalData($id));
        $sumCount = $userPjournalCount + $userTjournalCount;

        return $sumCount;
    }

    /*
     * 获取所有日志数量
     */
    public function journalCoutn($id)
    {
        $userPjournal = new UserPjournal();
        $userTjournal = new UserTjournal();
        // 本月计划日报数量
        $userPjournalCount = count($userPjournal->pjournalData($id));
        // 本月临时日报数量
        $userTjournalCount = count($userTjournal->tjournalData($id));
        $sumCount = $userPjournalCount + $userTjournalCount;

        return $sumCount;
    }

    /*
     * 找到当前用户数据库日报最后时间
     */
    public function getLastTime()
    {
        // 先找到当前用户最后一篇日报时间
        $userPjournalModel = new UserPjournal();
        // 计划任务最后一篇日报时间
        $pjournalEndTime = $userPjournalModel->getPjournalLastTime(); // 计划表最后时间
        $userTjournalModel = new UserTjournal();
        $tjournalEndTime = $userTjournalModel->getTjournalLastTime(); // 临时表最后时间
        // 两个表均存在时间
        if (!empty($pjournalEndTime) && !empty($tjournalEndTime)) {
            $time = ($pjournalEndTime['unix_time'] > $tjournalEndTime['unix_time']) ? $pjournalEndTime['unix_time'] : (($pjournalEndTime['unix_time'] == $tjournalEndTime['unix_time']) ? $pjournalEndTime['unix_time'] : $tjournalEndTime['unix_time']);

            return $time;
        }

        $time = isset($pjournalEndTime['unix_time']) ? $pjournalEndTime['unix_time'] : (isset($tjournalEndTime['unix_time']) ? $tjournalEndTime['unix_time'] : '');

        return $time;
    }

    /*
     * 修改用户头像
     */
    public function setUserImg($file)
    {
        $upload = new Upload();
        $user_img = $upload->upload($file);
        $user_id = session('user')->id;
        $data = [
            'user_img' => $user_img,
        ];
        $where = [
            'id' => $user_id,
        ];
        $result = self::update($data, $where);
        if ($result) {
            return [
                'error' => 0,
                'msg' => $file,
            ];
        } else {
            return [
                'error' => 1,
                'msg' => '上传错误，请重试',
            ];
        }
    }

    /*
     * 修改用户密码
     */
    public function editPassword($id, $data)
    {
        $user = self::get($id);
        if ($user['password'] != md5($data['yuan_password'])) {
            return '原密码不正确，请重新输入';
        }

        if ($data['password'] != $data['reset_password']) {
            return '重复输入密码不一致';
        }

        $validate = new Validate([
            ['password', 'require|min:6|max:30', '请输入密码|不得少于6位|不得大于15位'],
        ]);
        if (!$validate->check($data)) {
            return $validate->getError();
        }
        $data = [
            'password' => md5($data['password']),
        ];
        $where = [
            'id' => $id,
        ];
        $result = self::update($data, $where);
        if ($result) {
            return 'success';
        }

        return '操作错误';
    }

    /*
     * 用户登录
     */
    public function login($data)
    {
        $validate = new Validate([
            ['username', 'require|min:3|max:15', '请输入用户名|用户名不得少于3位|用户名不得大于15位'],
            ['password', 'require|min:6|max:30', '请输入密码|密码不得少于6位|密码不得大于15位'],
        ]);

        if (!$validate->check($data)) {
            return $validate->getError();
        }

        $password = md5($data['password']);

        $result = self::get(['username' => $data['username'], 'password' => $password]);
        if (!$result) {
            return '用户名密码错误';
        }
        session('user', $result);

        return 'success';
    }

    /*
     * 用户注册
     */
    public function register($data)
    {
        $validate = validate('User');
        if (!$validate->check($data)) {
            // 验证失败 输出错误信息
            return $validate->getError();
        }
        $psd = $data['password'];
        $repsd = $data['repeat_password'];
        if ($psd != $repsd) {
            return '两次密码不一致';
        }

        $user = self::get(['username' => $data['username']]);
        if (false != $user) {
            return '用户名已经存在';
        }

        $data['password'] = md5($data['password']);
        $result = self::create($data);
        if ($result) {
            $users = self::get(['username' => $data['username'], 'password' => $data['password']]);
            session('user', $users);

            return 'success';
        } else {
            return '注册失败';
        }
    }

    /*
     * 获取用户日志，去重返回
     */
    public function unqiueData($userId, $startTime = '', $endTime = '', $count = 50)
    {
        if (empty($startTime) && empty($endTime)) {
            $time = $this->getLastTime();
            //判断时间是否有时间 有-》获取时间到当前时间日报；无-》获取本周一的时间到当前时间
            if (!empty($time)) {
                $startTime = ($time + 60) * 1000;
            } else {
                $startTime = (Cache::get('weekFirst') + 60) * 1000;
            }
            $endTime = time() * 1000;
        }
        // 获取日报
        $accessTokenUser = AccessToken::get(['status' => 1])->access_token;
        $userDataString = $this->getDay($accessTokenUser, $startTime, $endTime, $userId, $count);
        $userData = json_decode(strstr($userDataString, '{'), true);
        // 获取日报数组
        $userData = $userData['result']['data_list'];

        $endUserData = collection([]);
        foreach ($userData as $key => $val) {
//                dump($val);
            $endTask = $val['contents'][0]['value'];
            $noEndTask = $val['contents'][1]['value'];
            $coordinateTask = $val['contents'][2]['value'];
            // 1 2 都不为空
            if (!empty($noEndTask) && !empty($coordinateTask)) {
                $titleString = $endTask.'；'.$noEndTask.'；'.$coordinateTask;
            }
            // 1 不空 2 空
            if (!empty($noEndTask) && empty($coordinateTask)) {
                $titleString = $endTask.'；'.$noEndTask;
            }
            // 1 空 2 不空
            if (empty($noEndTask) && !empty($coordinateTask)) {
                $titleString = $endTask.'；'.$coordinateTask;
            }
            // 1 2 都为空
            if (empty($noEndTask) && empty($coordinateTask)) {
                $titleString = $endTask;
            }
            $endData = collection([]);
//                dump($titleString);
            $data['create_time'] = $val['create_time'];
            $data['creator_name'] = $val['creator_name'];
            $data['dept_name'] = $val['dept_name'];
            if (strpos($titleString, '；')) {
                $titleData = explode('；', $titleString);
                $max = count($titleData);
                for ($i = 0; $i < $max; ++$i) {
                    $titleList = $titleData[$i];
                    $data['title'] = $titleList;
                    $endData->push($data);
                }
            } else {
                $data['title'] = $titleString;
                $endData->push($data);
            }
            $endUserData->push($endData->toArray());
        }
        // 组成一个数组 用来去重
        $unqiueData = collection([]);
        foreach ($endUserData->toArray() as $key => $val) {
            $max = count($val);
            for ($i = 0; $i < $max; ++$i) {
                $unqiueData->push($val[$i]);
            }
        }
        // 最终数组
        $result = $this->removeDuplicate($unqiueData->toArray());

        return $result;
    }

    /*
     * 导入
     */
    public function insertTask($data)
    {
        $userId = session('user')->id;
        // 逻辑
        for ($i = 0; $i < count($data['create_time']); ++$i) {
            //上传文件
            $file = request()->file('complete'.$i);
            if ($file) {
                $upload = new Upload();
                $complete = $upload->upload($file); //输出物
            } else {
                $complete = '';
            }
            $create_times = date('Y-m-d H:i:s', $data['create_time'][$i] / 1000); //钉钉发布时间
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
                $tjournalModel = new Tjournal();
                $tjournal_id = $tjournalModel->insert($datas, false, true);
//                $tjournal_id = Db::name('tjournal')->insert();
                // 写入关联表
                $user_data = [
                    'user_id' => $userId,
                    'tjournal_id' => $tjournal_id,
                ];
                $userTjournalModel = new UserTjournal();
                $userTjournalModel->insert($user_data);
            } else {
                // 写入计划任务表 pjournal
                $pjournalModel = new Pjournal();
                $pjournal_id = $pjournalModel->insert($datas, false, true);
                // 写入关联表
                $user_data = [
                    'user_id' => $userId,
                    'pjournal_id' => $pjournal_id,
                ];
                $userPjournalModel = new UserPjournal();
                $userPjournalModel->insert($user_data);
            }
        }
    }

    /*
    * 数组去重方法
    */
    public function removeDuplicate($array)
    {
        $result = array();
        foreach ($array as $key => $value) {
            $has = false;
            foreach ($result as $val) {
                if ($val['title'] == $value['title']) {
                    $has = true;
                    break;
                }
            }
            if (!$has) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /*
     * 绑定钉钉
     */
    public function bindDing($code)
    {
        $data = AccessToken::get(1);
        $accessToken = $data->access_token;
        // 获取用户永久授权码
        $userInfo = $this->getPersistentCode($code, $accessToken);
        $userJson = strstr($userInfo, '{');
        $userData = json_decode($userJson, true);
        // 获取用户sns_token
        $openId = $userData['openid'];
        $persistentCode = $userData['persistent_code'];
        $unionId = $userData['unionid'];
        $snsInfo = $this->getSnsToken($accessToken, $openId, $persistentCode);
        $snsJson = strstr($snsInfo, '{');
        $snsData = json_decode($snsJson, true);
        $snsToken = $snsData['sns_token'];
        // 获取用户个人信息
        $userInfos = json_decode($this->getUserInfo($snsToken), true);
        $dingNick = $userInfos['user_info']['nick'];
        // 获取用户userid
        $accessTokenUser = AccessToken::get(2)->access_token;
        $userIds = json_decode($this->getUserUserId($accessTokenUser, $unionId), true);
        $userId = $userIds['userid'];

        // 更改用户表
        $data = [
            'open_id' => $openId,
            'persistent_code' => $persistentCode,
            'union_id' => $unionId,
            'user_id' => $userId,
            'ding_nick' => $dingNick,
        ];
        $where = [
            'id' => session('user')->id,
        ];
        self::update($data, $where);
    }

    /*
     * 获取用户持久授权码
     */
    protected function getPersistentCode($code = '', $access_token = '')
    {
        $url = 'https://oapi.dingtalk.com/sns/get_persistent_code?access_token='.$access_token;
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
            'tmp_auth_code' => $code,
            'access_token' => $access_token,
        ];
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }

    /*
     * 获取sns_token
     */
    protected function getSnsToken($access_token = '', $openid = '', $persistent_code = '')
    {
        $url = 'https://oapi.dingtalk.com/sns/get_sns_token?access_token='.$access_token;
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
            'access_token' => $access_token,
            'openid' => $openid,
            'persistent_code' => $persistent_code,
        ];
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }

    /*
     * 获取用户个人信息
     */
    protected function getUserInfo($sns_token = '')
    {
        $url = 'https://oapi.dingtalk.com/sns/getuserinfo?sns_token='.$sns_token;
        // 初始化
        $curl = curl_init();
        // 访问的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //绕过ssl验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 2);
        // 执行命令
        $data = curl_exec($curl);
        // 关闭
        curl_close($curl);

        return $data;
    }

    /*
     * 获取用户userid
     */
    protected function getUserUserId($access_token = '', $unionid = '')
    {
        $url = 'https://oapi.dingtalk.com/user/getUseridByUnionid?access_token='.$access_token.'&unionid='.$unionid;
        // 初始化
        $curl = curl_init();
        // 访问的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //绕过ssl验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 2);
        // 执行命令
        $data = curl_exec($curl);
        // 关闭
        curl_close($curl);

        return $data;
    }

    /*
     * 获取当前用户日报
     */
    protected function getDay($access_token = '', $start_time = '', $end_time = '', $userid = '', $size = 10, $template_name = '日报', $cursor = 0)
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
