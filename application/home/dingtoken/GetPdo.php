<?php

class GetPdo
{
    protected $dsn = 'mysql:host=localhost;dbname=report';

    protected $user = 'root';

    protected $password = 'root';

    public function select_acs($sql)
    {
        try {
            header('Content-Type:text/html; charset=utf-8'); //网页utf8
            $pdo = new PDO($this->dsn, $this->user, $this->password); //连接数据库
            $pdo->query('set names utf8'); //数据库utf8

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            //query执行一条SQL语句，返回一个pdostatement对象
            $stmt = $pdo->query($sql);

            return $stmt;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function update_acs($sql)
    {
        try {
            header('Content-Type:text/html; charset=utf-8'); //网页utf8
            $pdo = new PDO($this->dsn, $this->user, $this->password); //连接数据库
            $pdo->query('set names utf8'); //数据库utf8

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $stmt = $pdo->exec($sql);

            return $stmt;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
