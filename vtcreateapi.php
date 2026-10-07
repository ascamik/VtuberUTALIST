<?php
require_once 'DbMa.php';
require_once 'Encode.php';

//Authentication process
require_once 'dbAu.php';

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    //$user = $auth->getCurrentSessionUserInfo();
    //   putHtmlNavibar('admin');
    //   print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    //403 
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden',
    ]);
    exit;
    // ここで終了
}

$vtname = trim($_POST['vtname'] ?? '');
$vtced = trim($_POST['vtced'] ?? '');

if ($vtname === '') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'message' => '名前が空白または使えない文字列です',
    ]);
    exit;
}
if ($vtced === 'new') {

    try {
        $db = getDb();
        $s = $db->prepare("select count(*) from tbvtuber where vtname=:vtname");
        $s->bindValue(':vtname', $vtname);
        $s->execute();

        $exists = $s->fetchColumn() > 0;

        if ($exists) {
            //$checkSongExists = True;
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => '同じ名前がすでにあります',
            ]);
            // error_log("$songid $arrng $exists");
            exit;
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => "DBError:", //{$e->getMessage()}",
        ]);
    }
    //insert new vtuber
    try {
        $db = getDb();
        $s = $db->prepare('INSERT INTO tbvtuber (vtname) VALUES(:vtname)');
        $s->bindValue(':vtname', $vtname);

        $s->execute();
        $resmesg = '登録';
    } catch (PDOException $e) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => "DBError:", //{$e->getMessage()}",
        ]);
    }
} elseif ($vtced) { //get data check
    try {
        $db = getDb();
        $s = $db->prepare("select vtname from tbvtuber where vtcode=:vtcode");
        $s->bindValue(':vtcode', $vtced);
        $s->execute();
        $currentvtname = $s->fetchColumn();
        if (! $currentvtname) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => 'コードが見つかりません',
            ]);
            // error_log("$songid $arrng $exists");
            exit;
        } elseif ($currentvtname === $vtname) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => '修正後の名前が同じです',
            ]);
            // error_log("$songid $arrng $exists");
            exit;
        } else {
            //modify vtname
            $s = $db->prepare('UPDATE tbvtuber SET vtname=? WHERE vtcode=?');
            $s->execute([$vtname, $vtced]);
            $resmesg = '修正';
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => "DBError:", //{$e->getMessage()}",
        ]);
    }
} //modfy


header('Content-Type: application/json; charset=UTF-8');

echo json_encode([
    'success' => true,
    'message' => "{$resmesg}が完了しました",
]);
