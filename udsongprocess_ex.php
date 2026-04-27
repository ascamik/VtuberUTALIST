<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'chckdate.php'; //for check mysql DATE format

// Import the necessary classes
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

// Include the composer autoload file
require 'vendor/autoload.php';
require_once 'sen_cnfg0001.php';
// Setup a new Eloquent Capsule instance



$title = '修正処理';
$h2 = "曲情報修正処理";
putHtmlHeader($title, $h2);
//putHtmlNavibar('admin');
//check login admin  

if ($user = Sentinel::check()) {
    // ログインしているアカウントをチェック
    print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";

    putHtmlContainerClose();
    exit;
    // ここで終了
}















putHtmltextarea();

$checkSongExists = False;
if ($_POST['sname'] and $_POST['yomi']) {
    if (preg_match('/^\d+$/', $_POST['songid'])) { //if arranged song
        if (preg_match('/^\d+$/', $_POST['arrng'], $match)) {
            $arrng = $match[0];
        } elseif (!($_POST['arrng'])) {
            $arrng = "0";
        } else {
            //arrng error
            print '<div>error! check the number of arrng</div>';
            $arrng = "0";
        }
        //if(preg_match('/^\d+$/',$POST['orgsongid']) and preg_match('/^\d{1,2}/',$POST['addarrng'])){ //if arranged song
        print '<div>songid searching...</div>';
        try {
            $db = getDb();
            $s = $db->query("select songid from tbsong where songid=\"{$_POST['songid']}\" and arrng=\"{$arrng}\" ;");
            $anssongid = $s->fetchAll(PDO::FETCH_COLUMN);
            if ($anssongid[0]) {
                print '<div>songid found ..ok</div>';
                $songid = $_POST['songid'];
                $checkSongExists = True;
            } else {
                print '<div>not found</div>';
            }
        } catch (PDOException $e) {
            die("Error:{$e->getMessage()}");
        }
    }
    if ($checkSongExists) {
        if (isset($_POST['remove']) and $_POST['remove'] === '1') {
            // Delete process
            try {

                $db = getDb();
                $s = $db->query("select count(*) from tbvocal where songid=\"{$songid}\" and arrng=\"{$arrng}\" ;");
                $count = $s->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                die("Error:{$e->getMessage()}");
            }
            if (intval($count[0]) > 0) { // the song is used in tbvocal(setlist). 
                print '<div>セットリスト内で使用されている曲は削除できません</div>';
                print "<div><a href=\"udsong.php?songid={$songid}&arrng={$arrng}\">曲のデータ修正ページに戻る</a></div>";
            } else {
                try {
                    $db = getDb();
                    //PREPAER
                    $s = $db->prepare('DELETE from tbsong where songid = :songid and arrng = :arrng');
                    $s->bindValue(':songid', $songid);
                    $s->bindValue(':arrng', $arrng);
                    $s->execute();
                } catch (PDOException $e) {
                    die("Error:{$e->getMessage()}");
                }
            }
            print "<div>id[{$songid}-{$arrng}]\">の曲データは削除されました</div>";
            print "<div><a href=\"udsong.php\">曲のデータ修正ページに戻る</a></div>";
        } else {

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
                $s->bindValue(':artist', $_POST['artist']);
                $s->bindValue(':tieup', $_POST['tieup']);
                $s->bindValue(':vocap', $_POST['vocap']);
                $s->bindValue(':genre', $_POST['genre']);

                $relsd = $_POST['relsd']; //shuld be checked  if format is correct DATE format.
                $relsd = chkMysqlDate($relsd); // check validate and format

                if ($relsd != '') {
                    $s->bindValue(':relsd', $relsd);
                } else {
                    $s->bindValue(':relsd', null, PDO::PARAM_NULL);
                }

                $s->execute();
                print '<div>曲データを正常に更新しました</div>';

                //$validate = true;

            } catch (PDOException $e) {
                die("Error:{$e->getMessage()}");
            }




            //final process
            print "<div><a href=\"udsong_ex.php?songid={$songid}&arrng={$arrng}\">曲のデータ修正ページに戻る</a></div>";
        }
        putHtmltextarea_close();
        putHtmlContainerClose();

        //  header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/udsong.php?songid='.$songid.'&arrng='.$arrng);
    }
} else {
    //if sname or yomi is(are) blank
    print '<div>更新できませんでした。<br>曲名とよみは必ず入力してください</div>';

    putHtmltextarea_close();
    putHtmlContainerClose();
}
