<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'dbAu.php';

//データベースダンプ出力用スクリプトですmysqldumpコマンドが使えるサーバで動作可能です
//★以下の 出力ディレクトリ・ファイル名は環境に合わせて修正してください





$title = '管理（DBダンプ出力）';
$h2 = "管理（DBdumpout）";
putHtmlHeader($title, $h2);
putHtmlNavibar('admin');
//check login admin  

if ($auth->isLogged()) {
   // ログインしているアカウントをチェック
   $user = $auth->getCurrentSessionUserInfo();
   print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
   print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
   print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";

   putHtmlContainerClose();
   exit;
   // ここで終了
}

// DB dump out for backup 次の行を修正すること★
$cmd = "/usr/bin/mysqldump -u {$DBUSR} -p{$DBPAS} krsstmgrvtsldb --default-character-set=utf8mb4  > /home/krsstmgr/pub/sul/dbb_foldr/archive_db_mysql.dump";

$s = system($cmd, $r);
// print"<div>$cmd</div>";
print "<div class=\"normalmessage\">Dumpout {$r}</div>\n\n";
print "<div class=\"normalmessage\">please save the link for download;  /dbb_foldr/archive_db_mysql.dump</div>\n\n";
putHtmlContainerClose();
