<?php
// for PHPAuth
require __DIR__ . '/vendor/autoload.php';
require_once '/home/krsstmgr/Secret.php';
try {
    $dbh = new PDO("mysql:host=localhost;dbname=phpauthdb;charset=utf8mb4", $PAUSR, $PAPAS);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 設定クラスと認証クラスの初期化
    $config = new \PHPAuth\Config($dbh);
    $auth = new \PHPAuth\Auth($dbh, $config);
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
}
