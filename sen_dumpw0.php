<?php
   require_once 'DbMa.php';
   require_once 'Encode.php';
   require_once 'htmlpkg.php';

//データベースダンプ出力用スクリプトですmysqldumpコマンドが使えるサーバで動作可能です
//41行目 データベース名と出力ディレクトリ・ファイル名は環境に合わせて修正してください
// Import the necessary classes
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

// Include the composer autoload file
require 'vendor/autoload.php';
require_once 'sen_cnfg0001.php';
// Setup a new Eloquent Capsule instance





   $title='管理（DBダンプ出力）';
   $h2="管理（DBdumpout）";
   putHtmlHeader($title,$h2);
   putHtmlNavibar('admin');
   //check login admin  
   
   if ($user = Sentinel::check()) {
       // ログインしているアカウントをチェック
      print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
       
   }else {
       print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
       print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";
       
       putHtmlContainerClose();
       exit;
       // ここで終了
   }

   // DB dump out for backup 次の行を修正すること
   $cmd = "/usr/bin/mysqldump -u {$DBUSR} -p{$DBPAS} krsstmgrvtsldb --default-character-set=utf8mb4  > /home/krsstmgr/pub/sul/dbb_foldr/krssfansite_db_mysql.dump";
   //$cmd = "/usr/bin/mysqldump -u {$DBUSR} -p{$DBPAS} krssfansite --default-character-set=utf8mb4 ";//  > /home/userdir/public_html/smrgphp/vtsldb_mysql.dump ";
   //$cmd = "mysqldump -u {$DBUSR} -p{$DBPAS} krssfansite --default-character-set=utf8mb4  > krssfansite_mysql.dump ";
   $s=system($cmd,$r);
  // print"<div>$cmd</div>";
   print "<div class=\"normalmessage\">Dumpout {$r}</div>\n\n";
   print "<div class=\"normalmessage\">please save the link for download;  vsdb/dbb_foldr/krssfansite_db_mysql.dump</div>\n\n";
   putHtmlContainerClose();
