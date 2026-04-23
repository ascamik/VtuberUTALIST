<?php
//次の行をSecret.phpを設置したパスに修正します.ホーム直下においた場合、/home/あなたのユーザ名/Secret.php かもしれませんが、XREAは/virtual/ユーザ名/Secret.php になるなどサーバによります。利用するサーバのマニュアルを見るか、確認が必要です
require_once '/home/krsstmgr/Secret.php';
//    $dsn = 'mysql:dbname=krsstmgrvtsldb; host=db01.lsv.jp; charset=utf8mb4';の行(12行目)を修正します
//    $dsn = 'mysql:dbname=歌リスト用DB名; host=DBサーバアドレス;port=3306; charset=utf8mb4';
//      DBサーバがlocalhostの場合は127.0.0.1としても良いでしょう。設置する提供サーバのDBサーバの指定に従ってください。ポート番号がデフォルト(3306)でない場合はport=3307;などを追加してください
function getDb() : PDO {
    global $DBUSR;
    global $DBPAS;


    $dsn = 'mysql:dbname=krsstmgrvtsldb; host=db01.lsv.jp; charset=utf8mb4';
    $usr = $DBUSR;
    $password = $DBPAS;

    $db = new PDO($dsn, $usr, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;

}
