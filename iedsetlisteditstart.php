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
    $title = '管理（エラー）';
    $h2 = "管理（エラー）";
    putHtmlHeader($title, $h2);
    putHtmlNavibar();
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";

    putHtmlContainerClose();
    exit;
    // ここで終了
}
$evwcode = $_POST['evwcode'] ?? '';
if ($evwcode == '') {
    //UPDATE mode if evwcode is blank...
} else {
    // checking if table tbvodraft is empty
    try {
        $db = getDb();

        $s = $db->query("select count(*) from tbvodraft");

        $datacount = $s->fetch(PDO::FETCH_ASSOC);
        //print_r($datacount);
        //exit;
        if ($datacount['count(*)'] > 0) {
            print '<div>編集作業中のデータがあります<br>新たにセットリストの編集を始めるには、先に作業中のデータを削除するか、完了処理をしてください</div>';
        } else {
            //evwcode check
            $s = $db->query("select evwcode from tbevent;");

            $evwcode_db = $s->fetchAll(PDO::FETCH_COLUMN); //single array, like  ['data1','data2',...]

            if (in_array($evwcode, $evwcode_db)) {
                // print '<div>ok evwcode</div>';

                $s = $db->prepare("select count(*) FROM tbvocal WHERE evwcode = :evwcode");
                $s->bindValue(':evwcode', $evwcode);
                $s->execute();
                $vocalcount = $s->fetch(PDO::FETCH_ASSOC);
                if ($vocalcount['count(*)'] == 0) {

                    $title = '管理（お知らせ）';
                    $h2 = "管理（お知らせ）";
                    $css = '<link rel="stylesheet" href="integratededitor.css?b2e5aa58"><link rel="stylesheet" href="table-grid-resp-integed.css?b2e5aa58">';
                    putHtmlHeader($title, $h2, $css);

                    putHtmlNavibar(); ?>


                    <div id="eraseSLmodal" class="modal-overlay" style="display:flex;">
                        <div class="modal-dialog">
                            <?php

                            print "<div class=\"normalmessage\">選択されたイベントのセットリストはまだ存在しません<br>セットリストの新規作成をする場合、登録するイベントの選択操作は後で行います。編集開始ボタンを押さず、左欄から曲を選んで登録を開始してください</div>\n\n";
                            print "<div class=\"modal-btnlike\"><a href=\"integeditor.php\"><div>〈管理〉統合編集のページに戻ります</div></a></div>\n\n";
                            ?>
                        </div>
                    </div>
<?php

                    putHtmlContainerClose();
                    exit;
                }

                print '<div>copying setlist to drafttable</div>';
                $s = $db->prepare("INSERT INTO tbvodraft (drafttype, evwcode, seqnum, songid, arrng, time, memo) SELECT 'E', evwcode, seqnum, songid, arrng, time, memo FROM tbvocal WHERE evwcode = :evwcode");

                $s->bindValue(':evwcode', $evwcode);
                $res = $s->execute();
                print '<div>ok</div>';
                header('Location: ./integeditor.php');
            } else {
                print 'evwcode ng';
                exit;
            }
        }
    } catch (PDOException $e) {
        die("Error:{$e->getMessage()}");
    }
}
