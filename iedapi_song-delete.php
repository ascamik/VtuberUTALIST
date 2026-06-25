<?php
require_once 'DbMa.php';
//require_once 'Encode.php';
//require_once 'htmlpkg.php';
//require_once 'timestamplinker.php';
//require_once 'chckdate.php';
require_once 'dbAu.php';

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    $user = $auth->getCurrentSessionUserInfo();
} else {
    //403
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden',
    ]);
    exit;
    // ここで終了
}
$songid = $_POST['songid'] ?? '';
$arrng = $_POST['arrng'] ?? '';
if (preg_match('/^\d+$/', $songid)) {        //if arranged song
    if (preg_match('/^\d+$/', $arrng, $match)) { //arrng== numeric
        $arrng = $match[0];
    } elseif (! ($arrng)) { // arrng == 0 or null
        $arrng = "0";
    } else { // arrng == non-numeric
        echo json_encode([
            'success' => false,
            'message' => 'error! illegal arrng',
        ]);
        exit;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'error! illegal songID',
    ]);
    exit;
}
//Check existence of Songid
try {
    $db = getDb();
    $s = $db->prepare("select count(*) from tbsong where songid=:songid and arrng=:arrng");
    $s->bindValue(':songid', $songid);
    $s->bindValue(':arrng', $arrng);
    $s->execute();

    $exists = $s->fetchColumn() > 0;

    if ($exists) {
        $checkSongExists = True;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'IDのデータはありません',
        ]);
        // error_log("$songid $arrng $exists");
        exit;
    }
} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => "DBError:", //{$e->getMessage()}",
    ]);
}


// Delete process
try {

    $db = getDb();
    $s = $db->prepare("select count(*) from tbvocal where songid = :songid and arrng = :arrng");
    $s->bindValue(':songid', $songid);
    $s->bindValue(':arrng', $arrng);
    $s->execute();
    $count = $s->fetchAll(PDO::FETCH_COLUMN);

    if (intval($count[0]) > 0) { // the song is used in tbvocal(setlist).
        echo json_encode([
            'success' => false,
            'message' => 'セットリスト内で使用されている曲は削除できません',
        ]);
        exit;
    } else {

        $db = getDb();
        //PREPAER
        $s = $db->prepare('DELETE from tbsong where songid = :songid and arrng = :arrng');
        $s->bindValue(':songid', $songid);
        $s->bindValue(':arrng', $arrng);
        $s->execute();
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
