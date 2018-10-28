<?php

namespace app\home\model;

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
}
