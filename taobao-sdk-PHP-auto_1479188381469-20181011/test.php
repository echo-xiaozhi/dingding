<?php
    include "TopSdk.php";
    date_default_timezone_set('Asia/Shanghai'); 
    $c = new TopClient;
    $c->appkey = 'dingoaemq5dfgda22dqly4';
    $c->secretKey = '6SeOVMWijiKBcDjXSSkVOxUfD5ga7n0kMKrDTbUtouV0WXlzNQae_2Vl7-OAhx-c';

    $req = new HttpdnsGetRequest;

    $req->putOtherTextParam("name","test");
    $req->putOtherTextParam("value",0);

    var_dump($c->execute($req));
?>