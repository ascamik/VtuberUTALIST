<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'Code2text.php';
require_once 'dbAu.php';
//
// Supports 4-digit EventID
//





$title = '管理（イベントデータ修正）';
$h2 = "管理（イベントデータ修正）";
putHtmlHeader($title, $h2);
//check login admin  

if ($auth->isLogged()) {

    putHtmlNavibar('admin');
    // ログインしているアカウントをチェック
    $user = $auth->getCurrentSessionUserInfo();
    print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    putHtmlNavibar();
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";

    putHtmlContainerClose();
    exit;
    // ここで終了
}





//$_POST['evwcode'] $_POST['evdate'] $_POST['evurl'] $_POST['evmedia'] $_POST['evtype'] $_POST['evdesc']
//print_r($_POST);
if (isset($_POST['evtitle']) and isset($_POST['evdate'])) {
    $postevwcode = $_POST['evwcode'];
    $filterurl = filter_var($_POST['evurl'], FILTER_VALIDATE_URL);
    if ($filterurl != $_POST['evurl']) {
        print '<div>URL is filterd! </div>';
    }

    //check evwcode exists or not in DB
    if ((preg_match('/[a-zA-Z0-9]{0,1}\d{0,3}/', $postevwcode))) {
        //connection to DB
        //Get list of Event code from DB
        try {
            $db = getDb();
            //SELECT event code
            $s = $db->query("select evwcode from tbevent;");

            $evwcodelist = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
            // print_r($evwcode);
            if (in_array($postevwcode, $evwcodelist)) { // if evwcode exists in dbevent

                if (isset($_POST['remove']) and $_POST['remove'] === '1') { //delete EventID process
                    try {

                        $db = getDb();
                        $s = $db->query("select count(*) from tbvocal where evwcode=\"{$postevwcode}\" ;");
                        $count = $s->fetchAll(PDO::FETCH_COLUMN);
                    } catch (PDOException $e) {
                        die("Error:{$e->getMessage()}");
                    }
                    if (intval($count[0]) > 0) { // the song is used in tbvocal(setlist). 
                        print "checking  as if the eventcode is used in tbvocal ...{$count[0]}";
                        print "<div>セットリスト内で曲が登録されているEventID[{$postevwcode}]は削除できません</div>";
                    } else {
                        try {
                            $db = getDb();
                            //PREPAER
                            $s = $db->prepare('DELETE from tbevent where evwcode = :evwcode');
                            $s->bindValue(':evwcode', $postevwcode);
                            $s->execute();
                            print "<div>EventID[{$postevwcode}]の情報は削除されました</div>";
                        } catch (PDOException $e) {
                            die("Error:{$e->getMessage()}");
                        }
                    }



                    // end of delete process

                } else {

                    if (preg_match('/^(20\d{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $_POST['evdate']) and intval($_POST['evmedia']) > 0 and intval($_POST['evmedia']) < 11 and  intval($_POST['evtype']) < 5   and intval($_POST['evtype']) > 0) {

                        $s = $db->prepare('UPDATE tbevent  SET evtitle=:evtitle, evdate=:evdate, evurl=:evurl, evmedia=:evmedia, evtype=:evtype, evdesc=:evdesc WHERE evwcode=:evwcode');
                        $s->bindvalue(':evtitle', $_POST['evtitle']);
                        $s->bindvalue(':evdate', $_POST['evdate']);
                        $s->bindvalue(':evurl', $filterurl);
                        $s->bindvalue(':evmedia', $_POST['evmedia']);
                        $s->bindvalue(':evtype', $_POST['evtype']);
                        $s->bindvalue(':evdesc', $_POST['evdesc']);
                        $s->bindvalue(':evwcode', $postevwcode);
                        $s->execute();
                        print '<div>イベントデータを正常に更新しました</div>';
                    }
                }
            } else {
                print '<div>invalid event code!</div>';
            }
        } catch (PDOException $e) {
            die("Error:{$e->getMessage()}");
        }
    } else {
        print '<div>invalid event code!</div>';
    }
} else {
    //  print'<div>invalid title or date, check your input</div>'; no post 
}














// no post data
//
$evwcode = ''; //initialize
if (isset($_GET['ev'])) {
    if ((preg_match('/[a-zA-Z0-9]{0,1}\d{0,3}/', $_GET['ev'])))
        $evwcode = $_GET['ev'];
}

if ($evwcode) {



    //connection to DB
    //Get list of Event code from DB
    try {
        $db = getDb();
        //SELECT event code
        $s = $db->query("select evwcode from tbevent;");

        $evwcodelist = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
        // print_r($evwcode);
        if (!(in_array($evwcode, $evwcodelist))) { // evwcode not exists in dbevent
            $evwcode = "1";
        }

        $s = $db->query("select evwcode, evdate, evtitle, evurl, evmedia, evtype, evdesc  from tbevent where evwcode=\"{$evwcode}\" order by evdate DESC;");

        while ($row = $s->fetch(PDO::FETCH_ASSOC)) {


?>

            <div id="formcontainer">
                <form method="POST" action="udevent.php">
                    <div class="formparts_2">
                        <div class="fml"><label class="label">code</label></div><input name="evwcode" id="evwcode" type="text" size="10" maxlength="4" value="<?= e($row['evwcode']) ?>" readonly>
                        <span>←コードは変更不可</span>
                    </div>
                    <div class="formparts_2">
                        <div class="fml"><label class="label">title</label></div><input name="evtitle" id="evtitle" type="text" size="90" maxlength="200" required value="<?= e($row['evtitle']) ?>">
                    </div>
                    <div class="formparts_2">
                        <div class="fml"><label class="label">URL</label></div><input name="evurl" id="evurl" type="url" size="90" maxlength="200" value="<?= e($row['evurl']) ?>">
                    </div>

                    <div class="formparts_2">
                        <div class="fml"><label class="label">Event date</label></div><input name="evdate" id="evdate" type="date" size="12" value="<?= e($row['evdate']) ?>" required>
                    </div>

                    <div class="formparts_2">
                        <div class="fml"> <label class="label">media</label></div>
                        <select id="evmedia" name="evmedia">

                            <?php
                            foreach ($evmediaCodeMx as $codenum => $codecaption) {

                                $selectedfg = $row['evmedia'] == $codenum ? 'selected' : '';

                                print "<option value=\"$codenum\" $selectedfg>$codenum:$codecaption</option>";
                            }
                            ?>
                            <option></option>
                        </select>
                    </div>

                    <div class="formparts_2">
                        <div class="fml"> <label class="label">site owner</label></div>
                        <select id="evtype" name="evtype">
                            <?php
                            foreach ($evtypeCodeMx as $codenum => $codecaption) {

                                $selectedfg = $row['evtype'] == $codenum ? 'selected' : '';

                                print "<option value=\"$codenum\" $selectedfg>$codenum:$codecaption</option>";
                            }
                            ?>

                        </select>
                    </div>


                    <div class="formparts_2">
                        <div class="fml"><label class="label">説明
                            </label></div>
                        <textarea id="evdesc" name="evdesc" rows="7" cols="100"><?= e($row['evdesc']) ?></textarea>
                    </div>

                    <div class="formparts_2">
                        <div class="fml">

                            <label class="label">DELETE</label>
                        </div>
                        <input type="checkbox" name="remove" value="1"><label>このEventIDの情報を削除する</label>

                    </div>




                    <div id="submitbutton">
                        <input type="submit" value="更新／Update">
                    </div>

                </form>
            </div>




    <?php
        }
    } catch (PDOException $e) {
        die("Error:{$e->getMessage()}");
    }
}  //if $evwcode set null
//
//if page opened with no option ,no display form for edit and start this line
try {
    $db = getDb();
    //SELECT event code


    $s = $db->query("select evwcode, evdate, evtitle from tbevent order by evdate DESC;");




    ?>

    <div id="formcontainer">
        <div class="formparts">
            <form method="GET" action="udevent.php">
                <label>編集対象の変更</label>
                <select name="ev">
                <?php
                while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                    $e = e($row['evwcode']);
                    // print "<option value=\"{$e}\">{$e}:".e($row['evdate']).'/'.e($row['evtitle'])."</option>";
                    print "<option value=\"{$e}\">{$e}:" . e($row['evdate']) . '/' . mb_substr(e($row['evtitle']), 0, 60) . "</option>";
                }
            } catch (PDOException $e) {
                die("Error:{$e->getMessage()}");
            }

                ?>
                </select>
                <div id="submitbutton"><input type="submit" value="イベント選択"></div>

            </form>
        </div>
    </div>

    <?php
    putHtmlContainerClose();
