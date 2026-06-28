<?php
require_once 'DbMa.php';
//require_once 'Encode.php';
//require_once 'htmlpkg.php';
//require_once 'timestamplinker.php';
require_once 'chckdate.php';
require_once 'dbAu.php';
require_once 'Code2text.php';

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
//POSTデータ確認
$genre = $_POST['genre'] ?? '';
$songid = $_POST['songid'] ?? '';
$arrng = $_POST['arrng'] ?? '';
$checkSongExists = False;
if ($_POST['sname'] and $_POST['yomi'] and (array_key_exists($genre, $genreCodeMx))) {
    if (preg_match('/^\d+$/', $songid)) {        //if arranged song
        if (preg_match('/^\d+$/', $arrng, $match)) { // arrng==number
            $arrng = $match[0];
        } elseif (! ($arrng)) { // arrng==null or 0
            $arrng = "0";
        } else { //arrng == non-numeric
            echo json_encode([
                'success' => false,
                'message' => 'error! illegal arrng',
            ]);
            exit;
        }

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
    }
    if ($checkSongExists) {
        //Update process

        try {
            $db = getDb();

            $yomi = mb_convert_kana($_POST['yomi'], 'c'); //Katakana => HIRAGANA convert


            //modify song data to tbsong table
            $s = $db->prepare('UPDATE tbsong  SET sname=:sname, yomi=:yomi, artist=:artist, tieup=:tieup, vocap=:vocap, genre=:genre, relsd=:relsd WHERE songid=:songid and arrng=:arrng');
            $s->bindValue(':songid', $songid);
            $s->bindValue(':arrng', $arrng);
            $s->bindValue(':sname', $_POST['sname']);
            $s->bindValue(':yomi', $yomi);
            $s->bindValue(':artist', $_POST['artist'] ?? '');
            $s->bindValue(':tieup', $_POST['tieup'] ?? '');
            $s->bindValue(':vocap', $_POST['vocap'] ?? '');
            $s->bindValue(':genre', $_POST['genre']);

            $relsd = $_POST['relsd'] ?? ''; //shuld be checked  if format is correct DATE format.
            $relsd = chkMysqlDate($relsd);  // check validate and format

            if ($relsd != '') {
                $s->bindValue(':relsd', $relsd);
            } else {
                $s->bindValue(':relsd', null, PDO::PARAM_NULL);
            }

            $s->execute();
            echo json_encode([
                'success' => true,
            ]);
            exit;
        } catch (PDOException $e) {

            echo json_encode([
                'success' => false,
                'message' => "DBError:", //{$e->getMessage()}",
            ]);
        }
    }
} else {
    //if sname or yomi is(are) blank
    echo json_encode([
        'success' => false,
        'message' => '更新できませんでした。<br>曲名とよみは必ず入力してください',
    ]);
    exit;
} {
}
