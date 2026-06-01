<?php
require_once 'Encode.php';
require_once 'htmlpkg.php';

require_once 'dbAu.php';






$title = '管理（新規ユーザ作成）';
$h2 = "管理（新規ユーザ作成）";
putHtmlHeader($title, $h2);


if (isset($_POST['usrac0']) and isset($_POST['ipswd0']) and isset($_POST['mkey0'])) {
    if ($_POST['mkey0'] == $SENKEY) {


        $email    = $_POST['usrac0'] ?? '';
        $password =  $_POST['ipswd0'] ?? '';
        $password_confirm = $password;
        // ユーザー登録処理
        $result = $auth->register($email, $password, $password_confirm);

        if ($result['error']) {
            // エラーメッセージの表示
            print '<div>error! :' . htmlspecialchars($result['message']) . '</div>';
        } else {
            print "<div>ユーザアカウントが作成されました /" . htmlspecialchars($result['message']) . "</div>";
        }
    } else {
        print '<div>!401 forbidden</div>';
    }
} else {
    print '<div>!400</div>';
}
?>
<div id="formcontainer">
    <form method="POST" action="sen_resist.php">
        <div class="formparts_2">
            <div class="fml"><label class="label">e-mail(new account)</label></div><input name="usrac0" id="usrac0" type="email" size="35" maxlength="50">
        </div>
        <div class="formparts_2">
            <div class="fml"><label class="label">password</label></div><input name="ipswd0" id="ipswd0" type="password" size="35" minlength="8" maxlength="20">
        </div>
        <div class="formparts_2">
            <div class="fml"><label class="label">secret key</label></div><input name="mkey0" id="mkey0" type="text" size="40" maxlength="50">
        </div>


        <div class="submitbutton">
            <input type="submit" value="送信／Submit">
        </div>

    </form>
</div>
<?php
putHtmlContainerClose();
