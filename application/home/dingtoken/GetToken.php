<?php

namespace app\home\dingtoken;

class GetToken
{
    /*
     * 定时获取无权限 access_token
     */
    public function get_access_token($appid = '', $appsecret = '')
    {
        // 初始化
        $curl = curl_init();
        // 访问的url
        $url = 'https://oapi.dingtalk.com/sns/gettoken?appid='.$appid.'&appsecret='.$appsecret;
        curl_setopt($curl, CURLOPT_URL, $url);
        //绕过ssl验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 2);
        // 执行命令
        $data = curl_exec($curl);
        // 关闭
        curl_close($curl);

        $data = json_decode($data);

        return $data->access_token;
    }

    /*
     * 定时获取有权限 access_token
     */
    public function get_access_token_user($corpid = '', $corpsecret = '')
    {
        // 初始化
        $curl = curl_init();
        // 访问的url
        $url = 'https://oapi.dingtalk.com/gettoken?corpid='.$corpid.'&corpsecret='.$corpsecret;
        curl_setopt($curl, CURLOPT_URL, $url);
        //绕过ssl验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 2);
        // 执行命令
        $data = curl_exec($curl);
        // 关闭
        curl_close($curl);

        $data = json_decode($data);

        return $data->access_token;
    }
}
