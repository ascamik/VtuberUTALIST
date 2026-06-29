<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'timestamplinker.php';

require_once 'dbAu.php';

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    $user = $auth->getCurrentSessionUserInfo();
    //   putHtmlNavibar('admin');
    //   print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    putHtmlNavibar();
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";

    putHtmlContainerClose();
    exit;
    // ここで終了
}

$mode = 'D';
$evwcode = $_POST['evwcode'] ?? '';
if ($evwcode == '') {
    //UPDATE mode if evwcode is blank...
    // get evwcode from tbvodraft
    $mode = 'E';
    try {
        $db = getDb();
        $s = $db->query("SELECT evwcode FROM tbvodraft WHERE drafttype='E' and seqnum ='1'");
        $evarray = $s->fetch(PDO::FETCH_ASSOC);
        $evwcode = $evarray["evwcode"];
        //for prepare ,delete setlist of evwcode  from tbvocal
        $s = $db->prepare("DELETE FROM tbvocal WHERE evwcode=:evwcode");
        $s->bindValue(':evwcode', $evwcode);
        $s->execute();
    } catch (PDOException $e) {
        die("Error:{$e->getMessage()}");
    }
}
try {
    $db = getDb();
    //evwcode check
    $s = $db->query("select evwcode from tbevent;");

    $evwcode_db = $s->fetchAll(PDO::FETCH_COLUMN); //single array, like  ['data1','data2',...]

    if (in_array($evwcode, $evwcode_db)) {
        print '<div>ok evwcode</div>';
    } else {
        print 'evwcode ng';
        exit;
    }
    //check initial data?
    $s = $db->prepare("SELECT count(*) FROM tbvocal WHERE evwcode=:evwcode");
    $s->bindValue(':evwcode', $evwcode);
    $s->execute();
    $count = $s->fetch(PDO::FETCH_ASSOC);
    if ($count['count(*)'] > 0) {
        print_r($count);
        print 'already data exist!';
        exit;
    }
    //print 'done';
    //exit;
    //
    $s = $db->prepare("INSERT INTO tbvocal (evwcode, seqnum, songid, arrng, time, memo) SELECT :evwcode, seqnum, songid, arrng, time, memo FROM tbvodraft WHERE drafttype = :drafttype");

    $s->bindValue(':drafttype', $mode);
    $s->bindValue(':evwcode', $evwcode);
    $res = $s->execute();
    if ($res) {
        $s = $db->query("DELETE FROM tbvodraft WHERE drafttype ='{$mode}'");
        print 'done ,delete draft data';
        header('Location: ./integeditor.php');
    }
} catch (PDOException $e) {
    die("Error:{$e->getMessage()}");
}
