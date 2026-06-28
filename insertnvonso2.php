<?php
//Supports 4-digit EventID
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'timestamplinker.php';

require_once 'tbsInsertSong.php';
require_once 'Code2text.php';
require_once 'dbAu.php';

// check login account

$title = '管理（セットリスト曲追加）';
$h2 = "管理（セットリスト曲追加）";
putHtmlHeader($title, $h2);

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    $user = $auth->getCurrentSessionUserInfo();
    putHtmlNavibar('admin');
    print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    putHtmlNavibar('');
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\"></a>ログインはこちら</a></div>\n\n";
    putHtmlContainerClose();
    exit;
    // ここで終了
}


//-- insert process  if exists recived POST data
if (isset($_POST['nssw'])) {
    if ($_POST['nssw'] === "1") {
        $sname = isset($_POST['sname']) ? $_POST['sname'] : '';
        $yomi = isset($_POST['yomi']) ? $_POST['yomi'] : '';
        $genre = isset($_POST['genre']) ? $_POST['genre'] : '';

        if ($sname and $yomi and (array_key_exists($genre, $genreCodeMx))) {

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
                print "<div  class=\"editerror\">曲の登録ができませんでした[{$ret_status['err']}</div>";
            } elseif ($ret_status['err'] == 1) {
                print "<div class=\"editerror\">同じ曲が[{$songid}-{$arrng}]で既に登録されているようです。既存曲として処理します</div>"; //このときsongidとarrngは返されるので処理は続けられる
            } else {
                print "<div>曲を[{$songid}-{$arrng}]で登録しました</div>";
                if ($orgsongid and $orgsongid != $songid) {
                    print "<div class=\"editerror\">指定されたidが見つかりません。新規の曲としました</div>";
                }
            }
        }
    } elseif ($_POST['nssw'] === "0") {   //already exists a song in tbsongDB
        $songid = isset($_POST['songid']) ? $_POST['songid'] : '';
        $arrng = isset($_POST['arrng']) ? $_POST['arrng'] : '';
    }

    //insert one of the setlist to tbvocal DB
    if ($songid and isset($_POST['evwcode'])) {
        if (preg_match('/^\d{1,4}$/', $songid) and preg_match('/^[a-zA-Z0-9]{0,1}\d{0,3}$/', $_POST['evwcode']) and preg_match('/^\d$/', $arrng)) {
            //check evwcode
            //Get list of Event code from DB
            print '<div>ok songid and evwcode and arrng.</div>';
            try {
                $db = getDb();
                //SELECT event code
                $s = $db->query("select evwcode from tbevent;");

                $evwcode_db = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
                // print_r($evwcode); //check
                if (in_array($_POST['evwcode'], $evwcode_db)) {
                    print '<div>ok evwcode</div>';
                    //ok evwcode
                    $s = $db->query("select songid from tbsong where songid=\"{$songid}\" and arrng=\"{$arrng}\";");
                    $returnsongid = $s->fetchAll(PDO::FETCH_COLUMN);
                    if ($returnsongid[0]) {
                        print '<div>songid exists</div>';
                        // 
                        $s = $db->query("select max(seqnum) from tbvocal where evwcode=\"{$_POST['evwcode']}\";");
                        $returnseqnum = $s->fetchAll(PDO::FETCH_COLUMN);  //max setlist number or null string
                        if ($returnseqnum[0] > 0) {
                            $seqnum = strval(intval($returnseqnum[0]) + 1);
                        } else {
                            $seqnum = 1;
                        }
                        if ((preg_match("/^(\d{0,2}?:{0,1}\d{1,2}:\d\d)\s*$/", $_POST['time'], $m))) {
                            $ntime = $m[1]; //$_POST['time'];
                        } else {
                            $ntime = "";
                        }
                        // insert record in  db
                        $s = $db->prepare('INSERT INTO tbvocal (evwcode, seqnum, songid, arrng, time, memo) VALUES(:evwcode, :seqnum, :songid, :arrng, :time, :memo)');
                        $s->bindValue(':evwcode', $_POST['evwcode']);
                        $s->bindValue(':seqnum', $seqnum);
                        $s->bindValue(':songid', $songid);
                        $s->bindValue(':arrng', $arrng);
                        $s->bindValue(':time', $ntime);
                        $s->bindValue(':memo', $_POST['memo']);

                        $s->execute();
                        print '<div>セットリストデータを正常に追加しました<br></div>';
                    } else {
                        // songid and arrng pair is not exists
                        print '<div>songid and arrng not exists</div>';
                    };
                } else {
                    //NG evwcode
                    print '<div>not found event id</div>';
                }
            } catch (PDOException $e) {
                die("Error:{$e->getMessage()}");
            }
        } else {
            print '<div>ng songid and evwcode and arrng</div>';
        }
    }
}
//-- insert process END

//connection to DB
//Get list of Event code from DB
try {
    $db = getDb();
    //SELECT event code
    $s = $db->query("select evwcode from tbevent;");

    $evwcodeList = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
    // print_r($evwcode);
} catch (PDOException $e) {
    die("Error:{$e->getMessage()}");
}
//GET クエリ処理

//default setting for display page
$dbwhere = "where tbvocal.evwcode=\"1\"";
$evmarker = '1';
if (isset($_POST['evwcode'])) {
    if (in_array($_POST['evwcode'], $evwcodeList)) { //Anti SQL injection process

        $dbwhere = "where tbvocal.evwcode=\"{$_POST['evwcode']}\"";
        $evmarker = $_POST['evwcode'];
    }
}
//$title='歌配信詳細情報';
//$h2="歌配信セットリスト";
//putHtmlHeader($title,$h2);
//putHtmlNavibar('admin');
?>

<div id="toptableoutline">

    <?php


    //connection to DB
    try {
        $db = getDb();
        //SELECT event detail
        $sev = $db->query("select evwcode,evdate,evtitle,evurl,evmedia,evtype,evdesc from tbevent where evwcode=\"{$evmarker}\" ;");

        while ($evdata = $sev->fetch(PDO::FETCH_ASSOC)) {

            //イベント詳細表示 ループを想定しているが、DBでevwcodeの重複を禁じているのでそうはならないはず
            $event_url = $evdata['evurl'];
            $evmedia = $evdata['evmedia']
    ?>

            <table>
                <thead>
                    <tr class="info">
                        <th colspan="3">タイトル</th>
                    </tr>
                    <tr class="info">
                        <th>日付</th>
                        <th>媒体</th>
                        <th>配信者</th>
                    </tr>
                    <tr class="info">
                        <th colspan="3">URL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="info">
                        <td colspan="3"><?= e($evdata['evtitle']) ?></td>
                    </tr>
                    <tr class="info">
                        <td><?= e($evdata['evdate']) ?></td>
                        <td><?= e($evdata['evmedia']) ?></td>
                        <td><?= e($evdata['evtype']) ?></td>
                    </tr>
                    <tr class="info">
                        <td colspan="3"><?= e($evdata['evurl']) ?></td>
                    </tr>
                    <?php
                    // then desc not equal void string
                    $desc = $evdata['evdesc'];
                    if ($desc) {
                        $desc = e($desc);
                        print "<tr><td colspan=\"3\">[備考] {$desc}</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
</div>
<?php
        }
?>

<div id="tableoutline">
    <table>
        <thead>
            <tr>
                <th class="two_em">N</th>
                <th>time</th>
                <th>曲名</th>
                <th>アーティスト/P</th>
                <th>TieUp</th>
                <th>メモ</th>
            </tr>
        </thead>
        <tbody>

            <?php

            //SELECT setlist
            $s = $db->query("select seqnum,time,sname,artist,vocap,tieup,tbsong.songid,tbsong.arrng,memo from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng= tbsong.arrng {$dbwhere} order by tbvocal.seqnum;");

            while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                //print_r ($row);

            ?>
                <tr>

                    <td class="two_em"><?= e($row['seqnum']) ?></td>

                    <?php
                    if (!(empty($row['time']) or empty($event_url)) and $evmedia = '1') {
                        print "<td class=\"time_w\"><a href=\"" . makeTimestamplink(e($event_url), $row['time']) . "\">" . e($row['time']) . "</a></td>";
                    } else {
                        print "<td class=\"time_w\">" . e($row['time']) . "</td>";
                    }



                    ?>


                    <td class="s_name"><a href="
        <?php
                //
                print 'shistory.php?sid=' . e($row['songid']);
        ?>
        "><?= e($row['sname']) ?></td>



                    <td><?= e($row['artist']) . e($row['vocap']) ?></td>
                    <td><?= e($row['tieup']) ?></td>
                    <td><?= e($row['memo']) ?></td>
                </tr>

        <?php

            }
        } catch (PDOException $e) {
            die("Error:{$e->getMessage()}");
        }
        ?>
        </tbody>
    </table>
</div>


<?php
//------------------------------------------------------------------------
// if(isset($_GET['err'])){
//     if($_GET['err']===1){
//         print"<div>【登録結果の確認をしてください】同名の曲がすでに登録されているため、新規の曲としては登録できません。既存曲として処理されました。</div>";
//     }
// }


?>
<div class="normalmessage">上記イベント[<?= e($evmarker) ?>]のセットリストの最後に曲(新規曲は同時に登録)を追加します。eventidを変更すると、別のイベントに追加します。</div>
<div id="formcontainer">
    <form method="POST" action="insertnvonso2.php">

        <div id="pack">
            <div id="dbinsong">
                <div class="formparts_2">
                    <div class="fml"><label class="label">eventid</label></div><input name="evwcode" id="evwcode" type="text" size="5" maxlength="5" value="<?= e($evmarker) ?>" required>
                </div>
                <div class="formparts_2">
                    <div class="fml"><label class="label">songid</label></div><input name="songid" id="songid" type="text" size="5" maxlength="5">
                </div>
                <div class="formparts_2">
                    <div class="fml"><label class="label">arrange</label></div><input name="arrng" id="arrng" type="text" size="2" maxlength="2" value="0">
                </div>

                <div class="formparts_2">
                    <div class="fml"><label class="label">time stamp</label></div><input name="time" id="time" type="text" size="8" maxlength="9">
                </div>

                <div class="formparts_2">
                    <div class="fml"><label class="label">memo</label></div><input name="memo" id="memo" type="memo" size="30" maxlength="100">
                </div>
                <div class="formparts_2"><input type="button" value="新規の曲/add new song" onclick="clickBtn1()" /></div>
                <input type="hidden" name="nssw" id="nssw" value="0">
            </div>
            <div id="newsong">
                <div class="formparts_2">
                    <div class="fml"><label class="label">song title</label></div><input name="sname" id="sname" type="text" size="50" maxlength="120">
                </div>
                <div class="formparts_2">
                    <div class="fml"><label class="label">song yomi ひらがな</label></div><input name="yomi" id="yomi" type="yomi" size="50" maxlength="120">
                </div>
                <div class="formparts_2">
                    <div class="fml"><label class="label">artist</label></div><input name="artist" id="artist" type="text" size="50" maxlength="120">
                </div>
                <div class="formparts_2">
                    <div class="fml"><label class="label">tie up anime etc.</label></div><input name="tieup" id="tieup" type="text" size="50" maxlength="120">
                </div>
                <div class="formparts_2">
                    <div class="fml"><label class="label">vocalo P name</label></div><input name="vocap" id="vocap" type="text" size="50" maxlength="120">
                </div>
                <div class="formparts_2">
                    <div class="fml"> <label class="label">genre</label></div>
                    <select id="genre" name="genre">
                        <option value="P">P:J-POP</option>
                        <option value="A">A:Animation song</option>
                        <option value="V">V:Vocalo song</option>
                        <option value="G">G:Game song</option>
                        <option value="o">o:Original song</option>
                        <option value="I">I:for The Idolmaster </option>
                        <option value="R">R:Rock</option>

                        <option></option>
                    </select>
                </div>

                <div class="formparts_2">
                    <div class="fml"><label class="label">released (yyyymmdd or yyyy-mm-dd)</label></div><input name="relsd" id="relsd" type="text" size="10" maxlength="10">
                </div>

                <div class="formparts_2">
                    <div class="fml"><label class="label">*origin songid</label></div><input name="orgsongid" id="orgsongid" type="text" size="5" maxlength="5">
                </div>
                <div class="formparts_2">
                    <div class="fml"><label class="label">add arrange ver.</label></div><input disabled name="addarrng" id="addarrng" type="text" size="2" maxlength="2">
                </div>
                <div>改編曲の場合(＊)入力、タイトルは「タイトル（〇〇Ver）」などにします。（伴奏・カラオケのバリエーション（例：ピアノバージョン）は改編曲として追加しないで、セットリストのmemoに記入で対応し既存の曲として処理</div>

            </div>
        </div><!-- pack -->





        <script type="text/javascript">
            document.getElementById("newsong").style.display = "none";

            function clickBtn1() {
                const dn = document.getElementById("newsong");
                const inputs = document.getElementById("songid");
                const inputa = document.getElementById("arrng");
                const nflag = document.getElementById("nssw");

                if (dn.style.display == "block") {
                    // noneで非表示
                    dn.style.display = "none";
                    inputs.disabled = false;
                    inputa.disabled = false;
                    nflag.setAttribute("value", "0");
                } else {
                    // blockで表示
                    dn.style.display = "block";
                    inputs.disabled = true;
                    inputa.disabled = true;
                    nflag.setAttribute("value", "1");
                }
            }
        </script>





















        <div id="submitbutton">
            <input type="submit" value="送信／Submit">
        </div>

    </form>
</div>
<?php

try {
    $db = getDb();



    $s = $db->query("select evwcode, evdate, evtitle from tbevent order by evdate DESC;");




?>

    <div id="formcontainer">
        <div class="formparts">
            <form method="POST" action="insertnvonso2.php">
                <label>追加する対象の変更します（編集前に選んでください。フォームはリセットされます）</label>
                <select name="evwcode">
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
                <div id="submitbutton"><input type="submit" value="別のイベントへ"></div>

            </form>
        </div>
    </div>


    <?php
    putHtmlContainerClose();
