<?php


require_once 'htmlpkg.php';
// Import the necessary classes
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

// Include the composer autoload file
require 'vendor/autoload.php';

// Setup a new Eloquent Capsule instance
require_once 'sen_cnfg0001.php';


$loginform = <<<EOD
<div id="formcontainer">
    <form method="POST" action="sen_nowusr.php">
        <div  class="formparts_2">
            <div class="fml"><label class="label">email</label></div><input name="email" id="email" type="email" size="50" maxlength="50"  required>
        </div>
        <div  class="formparts_2">
        <div class="fml"><label class="label">password</label></div><input name="passwd" id="passwd" type="password" size="50" maxlength="50" required>
        </div>

        <div  class="formparts_2">
       
        <div id="submitbutton">
            <input  type="submit" value="ログイン/login">
        </div>
    
    </form>
</div>
EOD;

$title = '管理（認証）';
$h2 = "管理（トップページ）";
putHtmlHeader($title, $h2);
putHtmltextarea();

if (isset($_POST['email']) and isset($_POST['passwd'])) {


    $credentials = [


        'email'    => $_POST['email'],
        'password' => $_POST['passwd'],
    ];

    $user = Sentinel::authenticateAndRemember($credentials);
    if ($user === false) {
        print '<div>ログイン認証に失敗しました</div>';
    } else {
        print '<div>ログインしました</div>';
    }
}

if ($user = Sentinel::check()) {
    // ログインしているアカウントをチェック
    print "<div>アカウント {$user['email']} でログインしています</div>";
    print "<div class=\"normalmessage\">この管理システムは管理者が使うことを想定した仮設のもので入力値のチェックは最低限しかしていません。</div>";
    //メニュー表示
    print "<div class=\"normalmessage\">通常登録作業は(1)→(2) タイムスタンプ等後で(3)修正・追加することができます</div>";
    putHtmladminmenu();

    print '<div><a href="sen_logout.php">ログアウトはこちらをクリック</a></div>';
} else {
    print $loginform;
    // ログインフォーム表示
}




putHtmltextarea_close();
putHtmlContainerClose();
