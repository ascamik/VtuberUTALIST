<?php
//Supports 4-digit EventID
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'timestamplinker.php';
require_once 'tbvInsertDelete.php';
require_once 'tbsInsertSong.php';
require_once 'dbAu.php';









$title = '管理（セットリスト修正〈曲の削除・挿入〉）';
$h2 = "管理（セットリスト修正〈曲の削除・挿入〉）";
putHtmlHeader($title, $h2);
//check login admin  

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    $user = $auth->getCurrentSessionUserInfo();
    putHtmlNavibar('admin');
    print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    putHtmlNavibar();
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";

    putHtmlContainerClose();
    exit;
    // ここで終了
}



//
print_r($_POST);
// main update setlist  process: delete a song or insert a song
$evwcode = isset($_POST['evwcode']) ? $_POST['evwcode'] : '';
if ($evwcode and preg_match('/^[a-zA-Z0-9]{0,1}\d{0,3}$/', $evwcode)) {
    $edit = isset($_POST['edit']) ? $_POST['edit'] : '';
    $seq = isset($_POST['target_seq']) ? $_POST['target_seq'] : '';

    //delete process
    if ($edit == 'del' and preg_match('/^\d{1,3}$/', $seq)) {
        //print $evwcode;

        $err_status = deleteRecordtbv($evwcode, $seq);

        switch ($err_status) {
            case 1:
                print '<div>エラー：パラメータが不正です</div>';
            case 2:
                print "<div>指定されたレコード[{$evwcode}-N{$seq}]はありません。削除されませんでした</div>";
            case 0:
                print "<div>削除処理を完了しました[{$evwcode}-N{$seq}]</div>";
            default:
                print "<div>Error {$err_status}</div>";
        }



        //Insert process
    } elseif ($edit == 'ins' and preg_match('/^\d{1,3}$/', $seq)) {
        //print $evwcode;
        //insert new song
        $nssw = isset($_POST['nssw']) ? $_POST['nssw'] : '';
        $sname = isset($_POST['sname']) ? $_POST['sname'] : '';
        $yomi = isset($_POST['yomi']) ? $_POST['yomi'] : '';
        $genre = isset($_POST['genre']) ? $_POST['genre'] : '';

        if ($nssw == '1' and $sname and $yomi and $genre) {

            $orgsongid = isset($_POST['orgsongid']) ? $_POST['orgsongid'] : '';
            $artist = isset($_POST['artist']) ? $_POST['artist'] : '';
            $tieup = isset($_POST['tieup']) ? $_POST['tieup'] : '';
            $vocap = isset($_POST['vocap']) ? $_POST['vocap'] : '';
            $relsd = isset($_POST['relsd']) ? $_POST['relsd'] : '';

            $ret_status = insertSongtbs($sname, $yomi, $genre, $orgsongid, $artist, $tieup, $vocap, $relsd);

            $songid = $ret_status['songid'];
            $arrng = $ret_status['arrng'];
            if ($ret_status['err'] > 1) {
                //preprocess function error
                //
                print "<div>曲の登録ができませんでした[{$ret_status['err']}</div>";
            } elseif ($ret_status['err'] == 1) {
                print "<div>曲が既に登録されているようです</div>"; //このときsongidとarrngは返されるので処理は続けられる
            }
        } elseif ($nssw === '0') {
            $songid = isset($_POST['songid']) ? $_POST['songid'] : '';
            $arrng = isset($_POST['arrng']) ? $_POST['arrng'] : '';
        }

        $time = isset($_POST['time']) ? $_POST['time'] : '';
        $memo = isset($_POST['memo']) ? $_POST['memo'] : '';

        if ($songid) { // insert song in the setlist database

            $ret = insertRecordtbv($evwcode, $seq);
            if ($ret === 0) {

                //insertnvprocess start
                try {
                    $db = getDb();
                    //SELECT event code
                    // $s = $db->query("select evwcode from tbevent;");

                    // $evwcode_db = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
                    // // print_r($evwcode); //check
                    // if(in_array($_POST['evwcode'],$evwcode_db)){
                    //     print'<div>ok evwcode</div>';
                    //ok evwcode
                    $s = $db->query("select songid from tbsong where songid=\"{$songid}\" and arrng=\"{$arrng}\";");
                    $returnsongid = $s->fetchAll(PDO::FETCH_COLUMN);
                    if ($returnsongid[0]) {
                        //print'<div>songid exists</div>';
                        // 
                        // $s = $db->query("select max(seqnum) from tbvocal where evwcode=\"{$_POST['evwcode']}\";");
                        // $returnseqnum = $s->fetchAll(PDO::FETCH_COLUMN);  //max setlist number or null string
                        // if($returnseqnum[0]>0){
                        //     $seqnum = strval(intval($returnseqnum[0]) +1);
                        // }else{
                        //     $seqnum = 1;
                        // };
                        if ((preg_match("/^(\d{0,2}?:{0,1}\d{1,2}:\d\d)\s*$/", $time, $m))) {
                            $time = $m[1]; //$_POST['time'];
                        } else {
                            $time = "";
                        }
                        // insert record in  db
                        $s = $db->prepare('INSERT INTO tbvocal (evwcode, seqnum, songid, arrng, time, memo) VALUES(:evwcode, :seqnum, :songid, :arrng, :time, :memo)');
                        $s->bindValue(':evwcode', $evwcode);
                        $s->bindValue(':seqnum', $seq);
                        $s->bindValue(':songid', $songid);
                        $s->bindValue(':arrng', $arrng);
                        $s->bindValue(':time', $time);
                        $s->bindValue(':memo', $memo);

                        $s->execute();
                        print '<div>セットリストデータを正常に挿入しました<br></div>';
                    } else {
                        // songid and arrng pair is not exists
                        print '<div>songid and arrng not exists</div>';
                    };
                    // }else{
                    //     //NG evwcode
                    //     print'<div>not found event id</div>';
                    // }

                } catch (PDOException $e) {
                    die("Error:{$e->getMessage()}");
                } //insertnvprocess end






            } else {
                print "<div>insert process error</div>";
            }
        } else {
            print "<div>songid no set </div>";
        }
    } else {
        //probably neither ins or del flag
        //print"<div>illegal seq number </div>";
    }
}




// display setlist table  and form 
if ($evwcode and preg_match('/^[a-zA-Z0-9]{0,1}\d{0,3}$/', $evwcode)) { // Because $evwcode is null when initial display page

?>
    <div class="normalmessage">処理を実行する行を右端のボタンで選ぶ</div>
    <div id="formcontainer">
        <form method="POST" action="udwsetlist.php">
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
                            <th class="five_em">○</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        // if ($evwcode == NULL ){ //evwcode POST優先　
                        //     if(isset($_GET['ev'])){
                        //         $evwcode=$_GET['ev'];
                        //     }
                        //     else{
                        //         $evwcode="1";
                        //     }
                        // }


                        $dbwhere = "where tbvocal.evwcode=\"{$evwcode}\"";

                        try {
                            $db = getDb();
                            //SELECT setlist
                            $s = $db->query("select seqnum,time,sname,artist,vocap,tieup,tbsong.songid,tbsong.arrng,memo from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng= tbsong.arrng {$dbwhere} order by tbvocal.seqnum;");

                            $c = 0;
                            while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                                //print_r ($row);
                                //  for wiki or youtube comment
                                // $diff1=e($row['sname']).($row['artist'].$row['vocap']?" / ".e($row['artist']).e($row['vocap']):"")."\n";// 
                                // $stock1=$stock1.$diff1;
                                // $stock_ts=$stock_ts.e($row['time'])." ".$diff1;

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


                                    <td class="s_name"><a href="<?php
                                                                //
                                                                print 'shistory.php?sid=' . e($row['songid']);
                                                                ?>"><?= e($row['sname']) ?></td>



                                    <td><?= e($row['artist']) . e($row['vocap']) ?></td>
                                    <td><?= e($row['tieup']) ?></td>
                                    <td class="memo_small"><?= e($row['memo']) ?></td>

                                    <!--  <form  method="POST" action="udsetlist.php"> -->
                                    <td>


                                        <input type="radio" name="target_seq" required value="<?= e($row['seqnum']) ?>"><label>対象</label>
                                    </td>
                                </tr>

                            <?php
                                $c = $c + 1;
                            }
                            ?>
                    </tbody>
                </table>
            </div>
            <div class="formpack">

                <div class="formparts">
                    <input type="radio" name="edit" value="del" required><label>曲を削除</label>
                    <input type="radio" name="edit" value="ins"><label>曲を挿入（選択位置にある曲は後ろにずれます）</label>
                </div>
            </div>
            <div id="pack">
                <div id="dbinsong">

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
                        <div class="fml"><label class="label">song yomi a-Z,0-9 or hiragana</label></div><input name="yomi" id="yomi" type="yomi" size="50" maxlength="120">
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
                        <div class="fml"> <label class="label">media</label></div>
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
                        <div class="fml"><label class="label"><del>add arrange ver.</del></label></div><input disabled name="addarrng" id="addarrng" type="text" size="2" maxlength="2">
                    </div>
                    <div>改編曲の場合(＊)入力、タイトルは「タイトル（〇〇Ver）」などにします。（伴奏・カラオケのバリエーション（例：ピアノバージョン）は改編曲として追加しないで、セットリストのmemoに記入で対応し既存の曲として処理</div>

                </div>
            </div><!-- pack -->
            <div id="submitbutton">
                <input type="submit" value="送信／Submit"><input type="hidden" name="evwcode" value="<?= e($evwcode) ?>">
            </div>

        </form>
    </div>


<?php
                        } catch (PDOException $e) {
                            die("Error:{$e->getMessage()}");
                        }
                    }

                    // if open page with no option ,no display table and start this line
                    try {
                        $db = getDb();
                        $s = $db->query("select evwcode, evdate, evtitle from tbevent order by evdate DESC;");



                        // event select form ***
?>
<div id="formcontainer">
    <div class="formparts">
        <form method="POST" action="udwsetlist.php">
            <label>編集対象の変更</label>
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
            <div id="submitbutton"><input type="submit" value="選択したイベントを編集"></div>

        </form>
    </div>
</div>
<?php
//                     ***
?>

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
<?php
putHtmlContainerClose();
