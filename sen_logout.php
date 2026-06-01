<?php


require_once 'htmlpkg.php';
require_once 'dbAu.php';


$title = '管理';
$h2 = "管理";
putHtmlHeader($title, $h2);
putHtmltextarea();
print '<a href="index.html">トップページへ</a><br><br>';

if ($auth->isLogged()) {

    $hash = $auth->getCurrentSessionHash();
    $re = $auth->logout($hash);

    if ($re) {
        //

        print '<div>ログアウトしました</div>';
    } else {
        print '<div>fail</div>';
        // 
    }
} else {
    print '<div>ログインしていません</div>';
}
putHtmltextarea_close();
putHtmlContainerClose();
