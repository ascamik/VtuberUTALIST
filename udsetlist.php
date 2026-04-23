<?php
//Supports 4-digit EventID
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'timestamplinker.php';


// Import the necessary classes
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

// Include the composer autoload file
require 'vendor/autoload.php';
require_once 'sen_cnfg0001.php';
// Setup a new Eloquent Capsule instance








$title = '管理（セットリストの個別データ修正）';
$h2 = "管理（セットリストの個別データ修正）";
putHtmlHeader($title, $h2);

//check login admin  

if ($user = Sentinel::check()) {
    // ログインしているアカウントをチェック
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
//print_r($_POST);

//$_POST['evwcode'] $_POST['seqnum'] $_POST['songid'] $_POST['arrng'] $_POST['time'] $_POST['memo']

$seqnum = isset($_POST['seqnum']) ? $_POST['seqnum'] : '';
$evwcode = isset($_POST['evwcode']) ? $_POST['evwcode'] : '';
$nsongid = isset($_POST['songid']) ? $_POST['songid'] : '';
$narrng = isset($_POST['arrng']) ? $_POST['arrng'] : '';


if ((preg_match('/^\d+$/', $nsongid) and preg_match('/^\d{1,2}$/', $narrng) and preg_match('/^[a-zA-Z0-9]{0,1}\d{0,3}$/', $evwcode) and preg_match('/^\d{1,3}$/', $seqnum))) {



    try {
        $db = getDb();
        //SELECT tbvocal of evwcode and seqnum
        //
        $s = $db->query("select songid from tbvocal where evwcode =\"{$evwcode}\" and seqnum = \"{$seqnum}\";");
        $rsongid = $s->fetchAll(PDO::FETCH_COLUMN);  // for only  check exists a data   of evwcode and seqnu in the setlist
        if ($rsongid[0]) { //ok
            //check the exist of songid and arrng
            $s = $db->query("select songid from tbsong where songid=\"{$nsongid}\" and arrng=\"{$narrng}\";");
            $returnsongid = $s->fetchAll(PDO::FETCH_COLUMN);
            if ($returnsongid[0]) { // its ok

                if ((preg_match("/^(\d{0,2}?:{0,1}\d{1,2}:\d\d)\s*$/", $_POST['time'], $m))) {
                    $ntime = $m[1]; //$_POST['time'];
                } else {
                    $ntime = "";
                }
                // insert record in  db
                $s = $db->prepare('UPDATE tbvocal SET songid=:songid, arrng=:arrng, time=:time, memo=:memo WHERE evwcode=:evwcode and seqnum=:seqnum');
                $s->bindValue(':evwcode', $evwcode);
                $s->bindValue(':seqnum', $seqnum);
                $s->bindValue(':songid', $nsongid);
                $s->bindValue(':arrng', $narrng);
                $s->bindValue(':time', $ntime);
                $s->bindValue(':memo', $_POST['memo']);

                $s->execute();
                print "<div class=\"normalmessage\">セットリストデータ[{$evwcode}-N:{$seqnum}]を正常に更新しました<br></div>";
            } else { //no songid , arrng
                print "<div>Songid={$nsongid},arrng={$narrng}で指定された曲はありません<br></div>";
            }
        } else {
            print "<div>指定のイベントコード{$evwcode}のセットリストに番号{$seqnum}はありません。<br>すでに削除されたか、不正な処理です。</div>";
        } //no one of setlist

    } catch (PDOException $e) {
        die("Error:{$e->getMessage()}");
    }
}

//test^^

// display setlist
// if ($evwcode == NULL ){ //evwcode POST優先　
//     if(isset($_GET['ev'])){
//         $evwcode=$_GET['ev'];
//     }

// }

if ($evwcode) {

?>
    <div class="normalmessage">各行毎に修正し、行右端の更新ボタンを押す（複数行を同時に更新はできません）</div>
    <div id="tableoutline">
        <table>
            <thead>
                <tr>
                    <th class="two_em">N</th>
                    <th>曲名</th>
                    <th>アーティスト/P</th>
                    <th>TieUp</th>
                    <th>songid/arrng/time/メモ</th>
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
                            //  if(!(empty($row['time']) or empty($event_url)) and $evmedia='1'){
                            //    print "<td class=\"time_w\"><a href=\"".makeTimestamplink(e($event_url),$row['time'])."\">".e($row['time'])."</a></td>";
                            //} else {
                            //    print"<td class=\"time_w\">".e($row['time'])."</td>";
                            //}



                            ?>


                            <td class="s_name"><a href="<?php
                                                        //
                                                        print 'shistory.php?sid=' . e($row['songid']);
                                                        ?>"><?= e($row['sname']) ?></td>



                            <td><?= e($row['artist']) . e($row['vocap']) ?></td>
                            <td><?= e($row['tieup']) ?></td>
                            <!-- <td class="memo_small"><?= e($row['memo']) ?></td> -->
                            <!--  <form  method="POST" action="udsetlist.php"> -->
                            <td class="formcell">
                                <form class="incell" method="POST" action="udsetlist.php">
                                    <input type="text" id="songid<?= $c ?>" name="songid" size="3" maxlength="5" value="<?= e($row['songid']) ?>">

                                    <input type="text" id="arrng<?= $c ?>" name="arrng" size="1" maxlength="2" value="<?= e($row['arrng']) ?>">

                                    <input type="text" id="time<?= $c ?>" name="time" size="6" maxlength="8" value="<?= e($row['time']) ?>">

                                    <input type="text" id="memo<?= $c ?>" name="memo" size="10" maxlength="100" value="<?= e($row['memo']) ?>">

                                    <!--<div id="submitbutton<?= $c ?>">-->
                                    <input type="submit" value="更新"> <input type="hidden" id="evwcode<?= $c ?>" name="evwcode" value="<?= e($evwcode) ?>"><input type="hidden" id="seqnum<?= $c ?>" name="seqnum" value="<?= e($row['seqnum']) ?>">
                                    <!--</div>-->
                                </form>
                            </td>


                        </tr>

                    <?php
                        $c = $c + 1;
                    }
                    ?>
            </tbody>
        </table>
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




?>

<div id="formcontainer">
    <div class="formparts">
        <form method="POST" action="udsetlist.php">
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
putHtmlContainerClose();
