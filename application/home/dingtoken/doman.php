<?php

// 引入获取以及数据库操作文件
include 'GetToken.php';
include 'GetPdo.php';

// 获取无权限 access_token
$GetToken = new \app\home\dingtoken\GetToken();
$appid = 'dingoaemq5dfgda22dqly4';
$appsecret = '6SeOVMWijiKBcDjXSSkVOxUfD5ga7n0kMKrDTbUtouV0WXlzNQae_2Vl7-OAhx-c';
$access_token = $GetToken->get_access_token($appid, $appsecret);

// 查询无权限 access_token 是否存在
$sql = 'select * from access_token where status = 0';
$GetPdo = new GetPdo();
$data = $GetPdo->select_acs($sql);
$acs_token = '';
foreach ($data as $key => $val) {
    $acs_token = $val;
}

if (!empty($acs_token)) {
    // 存在执行更新
    $sql = 'update access_token set access_token = "'.$access_token.'" where status = 0';
    $data = $GetPdo->update_acs($sql);
} else {
    // 不存在执行写入
    $sql = 'insert into access_token(access_token, status) values ("'.$access_token.'", 0) ';
    $data = $GetPdo->update_acs($sql);
}

// 获取有权限 access_token
$corpid = 'dinge30dcf5c15c8ddda35c2f4657eb6378f';
$corpsecret = 'NQjLzv8JXQ5PgHRs0-iZUg64-GBOEzgKOeoCQmFAo0sAmeWWws-z-NxNy4f0TeTX';
$access_token_user = $GetToken->get_access_token_user($corpid, $corpsecret);
// 查询有权限 access_token 是否存在
$sql = 'select * from access_token where status = 1';
$data = $GetPdo->select_acs($sql);
$acs_token_user = '';
foreach ($data as $key => $val) {
    $acs_token_user = $val;
}

if (!empty($acs_token_user)) {
    // 存在执行更新
    $sql = 'update access_token set access_token = "'.$access_token_user.'" where status = 1';
    $data = $GetPdo->update_acs($sql);
} else {
    // 不存在执行写入
    $sql = 'insert into access_token(access_token, status) values ("'.$access_token_user.'", 1) ';
    $data = $GetPdo->update_acs($sql);
}
