<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'timestamplinker.php';


//connection to DB
//Get list of songid  from DB
try {
    $db = getDb();
    //SELECT songid
    $s = $db->query("select songid from tbsong;");

    $songid_all = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
    // print_r($songid_all);
} catch (PDOException $e) {
    die("Error:{$e->getMessage()}");
}
//GET クエリ処理

//default setting for display page
$dbwhere = "where tbsong.songid=\"1\""; // not in use?
$sidmarker = '1';
if (isset($_GET['sid'])) {
    if (in_array($_GET['sid'], $songid_all)) { //sql injection protection

        $dbwhere = "where tbsong.songid=\"{$_GET['sid']}\"";
        $sidmarker = $_GET['sid'];
    }
}
$title = '曲情報・歌唱歴';
$h2 = "曲情報・歌唱歴";
$aditionalcss = '<link rel="stylesheet" href="table-grid-resp-shistory.css?b2e5aacd">';
putHtmlHeader($title, $h2, $aditionalcss);
putHtmlNavibar();
?>

<div id="toptableoutline">

    <?php


    //connection to DB
    try {
        $db = getDb();
        //SELECT event detail
        $ssid = $db->query("select * from tbsong where songid=\"{$sidmarker}\" ;");
        // songid arrng sname yomi artist tieup vocap genre

        while ($songdata = $ssid->fetch(PDO::FETCH_ASSOC)) {

            //歌情報詳細表示 arrngがある場合ループ、複数回表示される


            $relsd = $songdata['relsd']; //release date NULL treatment
            $relsd = is_null($relsd) ? '' : $relsd;

    ?>

            <table>
                <thead>
                    <!--     <tr class="info" >
            <th>項目</th>
        
            <td>内容</td>
        </tr> -->
                </thead>
                <tbody>
                    <tr class="info">
                        <th class="label_w">ID</th>
                        <td><?= e($songdata['songid']) ?></td>
                    </tr>
                    <tr class="info">
                        <th>枝番</th>
                        <td><?= e($songdata['arrng']) ?></td>
                    </tr>
                    <tr class="info">
                        <th>曲名</th>
                        <td><?= e($songdata['sname']) ?></td>
                    </tr>
                    <tr class="info">
                        <th>よみ</th>
                        <td><?= e($songdata['yomi']) ?></td>
                    </tr>
                    <tr class="info">
                        <th>アーティスト</th>
                        <td><?= e($songdata['artist']) ?></td>
                    </tr>
                    <tr class="info">
                        <th>Tie up</th>
                        <td><?= e($songdata['tieup']) ?></td>
                    </tr>
                    <tr class="info">
                        <th>ボカロP</th>
                        <td><?= e($songdata['vocap']) ?></td>
                    </tr>
                    <tr class="info">
                        <th>リリース</th>
                        <td><?= e($relsd) ?></td>
                    </tr>
                    <tr class="info">
                        <th>区分</th>
                        <td><?= e($songdata['genre']) ?></td>
                    </tr>

                </tbody>
            </table>


        <?php
        }
        //複数テーブル出力用
        ?>
</div>
<div id="jsbutton">
    <div class="backbutton"><a href="javascript:history.back()"><img src="arrow_back.svg"><span class="jsbuttonspan">戻る</span></a></div>
</div>
<div id="tableoutline">
    <div class="table">
        <div class="table_head">
            <div class="table-grid">
                <div class="cell" class="date_w">日付</div>
                <div class="cell">N</div>
                <div class="cell">time</div>
                <div class="cell">曲名</div>
                <div class="cell">メモ</div>
                <div class="cell">配信タイトル</div>
            </div>
        </div>
        <div class="tbody">

            <?php
            $evtypeIconFlag = 0;

            //SELECT song history
            $s = $db->query("select evdate,seqnum,time,sname,memo,evtitle,tbevent.evwcode,evurl,evmedia,evtype from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng= tbsong.arrng join tbevent on tbvocal.evwcode=tbevent.evwcode where tbvocal.songid=\"{$sidmarker}\" order by evdate;");

            while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                //print_r ($row);
                //print $row['evurl'].$row['time'];
            ?>
                <div class="table-grid">

                    <div class="cell"><?= e($row['evdate']) ?></div>
                    <div class="cell"><?= e($row['seqnum']) ?></div>

                    <?php
                    if (!(empty($row['time']) or empty($row['evurl'])) and ($row['evmedia'] == '1' or $row['evmedia'] == '10')) {
                        print "<div class=\"cell\"><a href=\"" . makeTimestamplink(e($row['evurl']), $row['time']) . "\">" . e($row['time']) . "</a></div>";
                    } else {
                        print "<div class=\"cell\">" . e($row['time']) . "</div>";
                    }



                    ?>



                    <div class="cell"><?= e($row['sname']) ?></div>
                    <div class="cell"><?= e($row['memo']) ?></div>
                    <div class="cell">
                        <?php
                        //print e($row['evurl']);
                        $evtypeIconTag = $row['evtype'] == 4 ? '<img src="handshake_28dp_8C1AF6_FILL0_wght400_GRAD0_opsz24.svg" class="evtype_icon">' : '';
                        print '<a href="setlist.php?ev=' . e($row['evwcode']) . '">' . $evtypeIconTag . e($row['evtitle']) . '</a>';
                        ?>
                    </div>

                </div><!--table-grid close-->

        <?php
                $evtypeIconFlag = $evtypeIconFlag + ($evtypeIconTag == '' ? 0 : 1);
            }
        } catch (PDOException $e) {
            die("Error:{$e->getMessage()}");
        }
        ?>
        </div><!-- table body close -->
    </div><!-- table close -->
    <?php
    if ($evtypeIconFlag >= 1) {
        print '<img src="handshake_28dp_8C1AF6_FILL0_wght400_GRAD0_opsz24.svg" class="evtype_icon">：リレー';
    }
    ?>
</div>
<?php
putHtmlContainerClose();
