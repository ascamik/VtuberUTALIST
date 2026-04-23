<?php
//変更箇所は17,18行目の
//    'host'      => 'db01.lsv.jp',
//    'database'  => 'krsstmgrsentinel',
//および12行目のrequire_once '/home/krsstmgr/Secret.php';です
//host右辺（=>'この部分'）を利用するサーバのDBサーバに合わせて変えてください
//detabase右辺は管理用データベースのDB名に設定します。歌リスト用DB名とは異なるDB名です。間違って歌リスト用DB名を設定すると、ダンプリスト出力等でユーザ情報が流出します
//12行目は''内をSecret.phpを設置したパス名（ディレクトリ/ファイル名）にします　レンタルサーバでusernameのユーザディレクトリ直下においた場合は/home/username/Secret.phpかもしれませんが、違う場合もあります。サーバ管理者のマニュアル等を確認するか、ユーザフォルダの絶対パスを管理者に確認すると確実でしょう
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

require_once '/home/krsstmgr/Secret.php';

// Setup a new Eloquent Capsule instance
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'db01.lsv.jp',
    'database'  => 'krsstmgrsentinel',
    'username'  => $SENUSR,
    'password'  => $SENPAS,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
]);

$capsule->bootEloquent();
