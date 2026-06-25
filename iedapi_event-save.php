<?php
require_once 'DbMa.php';
require_once 'Encode.php';
//require_once 'htmlpkg.php';
//require_once 'timestamplinker.php';

require_once 'dbAu.php';

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    //$user = $auth->getCurrentSessionUserInfo();
    //   putHtmlNavibar('admin');
    //   print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    //403
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden',
    ]);
    exit;
    // ここで終了
}
//先にPOSTデータの正規性チェックではじく
if (! (preg_match('/^(20\d{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $_POST['evdate']) and intval($_POST['evmedia']) > 0 and intval($_POST['evmedia']) < 11 and intval($_POST['evtype']) < 5 and intval($_POST['evtype']) > 0 and $_POST['evtitle'] != '')) {
    echo json_encode([
        'success' => false,
        'message' => "日付とタイトルまたは選択肢などが正しくありません",
    ]);
    exit;
}

//$_POST['evwcode']が空白なら新規作成モードにしていたが、
// 間違って既存のコードを手入力してしまうとトラブルので、モードを分けた

$evwcode = trim($_POST['evwcode'] ?? '');

if ($evwcode === '') {
    //Evwcode automatic creation if it'is blank
    try {
        $db = getDb();
        //SELECT event code, pick up MAX interger code :exclude alphabet-string+number type code
        $s = $db->query("select max(CAST(evwcode AS SIGNED)) from tbevent where  evwcode  regexp \"^[0-9]+\"; ");

        $max_evwcode = $s->fetchAll(PDO::FETCH_COLUMN); //single array, like  ['data1','data2',...]
        // print_r($evwcode);
        $maxinteger_evwcode = intval($max_evwcode[0]);
        $evwcode = strval($maxinteger_evwcode + 1); //overwrite posted data
    } catch (PDOException $e) {

        echo json_encode([
            'success' => false,
            'message' => "Error:{$e->getMessage()}",
        ]);
    }
}
//EVWCODE の存在チェックでUPDATEかINSERTか分岐
try {
    $db = getDb();
    $stmt = $db->prepare(
        'SELECT COUNT(*)
       FROM tbevent
      WHERE evwcode = ?'
    );

    $stmt->execute([$evwcode]);

    $exists =
        $stmt->fetchColumn() > 0;



    if ($exists and ($_POST['mode'] ?? '')) { // exist, but new mode!! : mode==1
        echo json_encode([
            'success' => false,
            'message' => '既存のコードは設定できません',
        ]);
        exit;
    }

    if ($exists) {

        $stmt = $db->prepare(
            'UPDATE tbevent
            SET
                evdate=?,
                evtitle=?,
                evurl=?,
                evmedia=?,
                evtype=?,
                evdesc=?
          WHERE evwcode=?'
        );

        $stmt->execute([
            $_POST['evdate'],
            $_POST['evtitle'],
            $_POST['evurl'],
            $_POST['evmedia'],
            $_POST['evtype'],
            $_POST['evdesc'],
            $evwcode,
        ]);
    } else {

        $stmt = $db->prepare(
            'INSERT INTO tbevent
        (
            evwcode,
            evdate,
            evtitle,
            evurl,
            evmedia,
            evtype,
            evdesc
        )
        VALUES
        (
            ?,?,?,?,?,?,?
        )'
        );

        $stmt->execute([
            $evwcode,
            $_POST['evdate'],
            $_POST['evtitle'],
            $_POST['evurl'],
            $_POST['evmedia'],
            $_POST['evtype'],
            $_POST['evdesc'],
        ]);
    }
} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => "DBError:", //{$e->getMessage()}",
    ]);
}
echo json_encode([
    'success' => true,
]);
