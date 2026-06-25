<?php
//This is a script that receives a POST request from Integrated editor

require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
//require_once 'timestamplinker.php';

require_once 'tbsInsertSong.php';

require_once 'dbAu.php';

// check login account

/*
$title = '管理（曲追加）';
$h2 = "管理（曲追加）";
putHtmlHeader($title, $h2);
*/

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    $user = $auth->getCurrentSessionUserInfo();
    //   putHtmlNavibar('admin');
    //print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    putHtmlNavibar('');
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    //   print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\"></a>ログインはこちら</a></div>\n\n";
    putHtmlContainerClose();
    exit;
    // ここで終了
}

$sname = isset($_POST['sname']) ? $_POST['sname'] : '';
$yomi = isset($_POST['yomi']) ? $_POST['yomi'] : '';
$genre = isset($_POST['genre']) ? $_POST['genre'] : '';

if ($sname and $yomi and (preg_match('/^[PAVGoIR]$/', $genre))) {

    $orgsongid = isset($_POST['orgsongid']) ? $_POST['orgsongid'] : '';
    $artist = isset($_POST['artist']) ? $_POST['artist'] : '';
    $tieup = isset($_POST['tieup']) ? $_POST['tieup'] : '';
    $vocap = isset($_POST['vocap']) ? $_POST['vocap'] : '';
    $relsd = isset($_POST['relsd']) ? $_POST['relsd'] : '';

    $ret_status = insertSongtbs($sname, $yomi, $genre, $orgsongid, $artist, $tieup, $vocap, $relsd);

    $songid = $ret_status['songid'];
    $arrng = $ret_status['arrng'];
    if ($ret_status['err'] > 1) { //$songid is set 0 for use next process
                                      //preprocess function error
                                      //
        $message = "登録ができませんでした[{$ret_status['err']}";
    } elseif ($ret_status['err'] == 1) {
        $message = "同じ曲が[{$songid}-{$arrng}]で既に登録されています（リミックス等は「〇〇リミックス」と付加するなど、タイトルを変えてください）";
    } else {
        $message = "[{$songid}-{$arrng}]で登録しました";
        if ($orgsongid and $orgsongid != $songid) {
            $message = "指定されたidが見つかりません。新規の曲としました";
        }
    }
} else {
    $message = 'タイトル・よみ・ジャンル指定 が空欄か正しくありません';
    echo json_encode([
        'success' => true,
        'message' => $message,
    ]);
    exit;

}

header('Content-Type: application/json; charset=utf-8');
if (! ($ret_status['err'] >= 1)) { //success 成功

    echo json_encode([
        'success' => true,
        'message' => $message,
    ]);
    exit;
} else {
    echo json_encode([
        'success' => false,
        'message' => $message,
    ]);
    exit;
}
