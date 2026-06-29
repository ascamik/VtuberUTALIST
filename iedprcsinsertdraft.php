<?php
//This is a script that receives a POST request from Integrated editor

require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'timestamplinker.php';
require_once 'IedprcsModule.php';


//require_once 'tbsInsertSong.php';

require_once 'dbAu.php';

// check login account

/*
$title = '管理（セットリスト曲追加）';
$h2 = "管理（セットリスト曲追加）";
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
$errcode = '';
//print_r($_POST);
//exit;
$seqnum = intval(isset($_POST['seqnum']) ? $_POST['seqnum'] : '');
$songid = intval(isset($_POST['songid']) ? $_POST['songid'] : '');
$arrng = intval(isset($_POST['arrng']) ? $_POST['arrng'] : '');
$time = $_POST['time'] ?? ''; // 上と同じ意味 :-p
$memo = $_POST['memo'] ?? '';
$comment = $_POST['comment'] ?? '';
$evwcode = $_POST['evwcode'] ?? '';

$mode = $_POST['mode'] ?? 'D'; //D:Draft P:Plan E:Edit
if ($mode === 'I') {
    $mode = 'D';
}
$drafttype = $mode;
$updatemode = ($seqnum == 0) ? 0 : 1;
$res = 0; //For Judgment when returning execution results in json
//print $songid;
//print $arrng;
$action = $_POST['action'] ?? '';

switch ($action) {

    // case 'edit':

    //     This process in the default process :-p

    //     // UPDATE

    //     break;

    case 'delete':

        // DELETE
        $resp = draftdbRowEditor($mode, $evwcode, $seqnum, 'DEL');
        $res = $resp[0];
        $errcode = $resp[1];
        break;

    case 'move_up':
        // 上と入れ替え
        $resp = draftdbRowEditor($mode, $evwcode, $seqnum, 'MVUP');
        $res = $resp[0];
        $errcode = $resp[1];
        break;

    case 'move_down':
        // 下と入れ替え
        $resp = draftdbRowEditor($mode, $evwcode, $seqnum, 'MVDOWN');
        $res = $resp[0];
        $errcode = $resp[1];

        break;

    default:

        // default process  :append a song at last of  the list, change  the data that is time or memo in the row
        if ($songid > 0) {
            try {
                $db = getDb();
                // check songid
                $s = $db->query("select songid from tbsong where songid=\"{$songid}\" and arrng=\"{$arrng}\";");
                $returnsongid = $s->fetchAll(PDO::FETCH_COLUMN);
                if ($returnsongid[0]) {
                    //print "songid ok";
                    if ($seqnum == 0) { // true => append mode

                        $s = $db->query("select max(seqnum) from tbvodraft where drafttype=\"{$mode}\";");
                        //   $s = $db->query("select max(seqnum) from tbvodraft where drafttype=\"D\" and evwcode=\"tmp\";");
                        $returnseqnum = $s->fetchAll(PDO::FETCH_COLUMN); //max setlist number or null string
                        if ($returnseqnum[0] > 0) {
                            $nextseqnum = strval(intval($returnseqnum[0]) + 1);
                        } else {
                            $nextseqnum = 1;
                        }
                        $seqnum = $nextseqnum;
                    }
                    //               $evwcode = $_POST['evwcode'] ?? '';
                    if ((preg_match("/^(\d{0,2}?:{0,1}\d{1,2}:\d\d)\s*$/", $time, $m))) {
                        $ntime = $m[1]; //$_POST['time'];
                    } else {
                        $ntime = "";
                    }
                    $s = $db->prepare('INSERT INTO tbvodraft (drafttype, evwcode, seqnum, songid, arrng, time, memo, comment) VALUES(:drafttype, :evwcode, :seqnum, :songid, :arrng, :time, :memo, :comment) ON DUPLICATE KEY UPDATE songid=VALUES(songid), arrng=VALUES(arrng), time=VALUES(time),memo=VALUES(memo),comment=VALUES(comment) ');
                    $s->bindValue(':drafttype', $drafttype);
                    $s->bindValue(':evwcode', $evwcode);
                    $s->bindValue(':seqnum', $seqnum);
                    $s->bindValue(':songid', $songid);
                    $s->bindValue(':arrng', $arrng);
                    $s->bindValue(':time', $ntime);
                    $s->bindValue(':memo', $memo);
                    $s->bindValue(':comment', $comment);

                    $s->execute();

                    $res = 1; //success

                }
                $errcode = 'IDの曲が見つかりません';
            } catch (PDOException $e) {
                die("Error:{$e->getMessage()}");
            }
        }
}
if ($updatemode) { //for Ajax response
    if ($res) {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => true,
        ]);
    } else {
        // response NG
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $errcode,
        ]);
    }
} else {
    //print 'done';
    //normal response
    header('Location: ./integeditor.php');
}
