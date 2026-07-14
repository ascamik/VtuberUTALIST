<?php
// within YouTube Player blanch
//
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'timestamplinker.php';
require_once 'Code2text.php';
require_once 'Relsd2cssclass.php';



//connection to DB
//Get list of Event code from DB
try {
    $db = getDb();
    //SELECT event code
    $s = $db->query("select evwcode from tbevent;");

    $evwcode = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
    // print_r($evwcode);
} catch (PDOException $e) {
    die("Error:{$e->getMessage()}");
}
//GET クエリ処理

//default setting for display page
$dbwhere = "where tbvocal.evwcode=\"1\"";
$evmarker = '1';
if (isset($_GET['ev'])) {
    if (in_array($_GET['ev'], $evwcode)) { //sql injection protection

        $dbwhere = "where tbvocal.evwcode=\"{$_GET['ev']}\"";
        $evmarker = $_GET['ev'];
    }
}
// $title='歌配信詳細情報';
// $h2="歌配信セットリスト[".e($evmarker)."]";
// putHtmlHeader($title,$h2);
// putHtmlNavibar();
?>



<?php


//connection to DB
try {
    $db = getDb();
    //SELECT event detail
    $sev = $db->query("select evwcode,evdate,evtitle,evurl,evmedia,evtype,evdesc from tbevent where evwcode=\"{$evmarker}\" ;");

    while ($evdata = $sev->fetch(PDO::FETCH_ASSOC)) {

        //イベント詳細表示 ループを想定しているが、DBでevwcodeの重複を禁じているので、while2回めはないはず
        $event_url = $evdata['evurl'];
        $evmedia = $evdata['evmedia'];

        if (intval($evmedia) > 9) {
            $ev_subtitle = "動画曲目リスト";
        } else {
            $ev_subtitle = "歌配信セットリスト";
        }

        $title = '配信詳細情報/' . $ev_subtitle;
        $h2 = $ev_subtitle . "[" . e($evmarker) . "]";
        $aditionalcss = '<link rel="stylesheet" href="table-grid-resp-setlist.css?b2e5ab03">';
        putHtmlHeader($title, $h2, $aditionalcss);
        //putHtmltext('タイムスタンプの時刻をクリック／タップすると動画の再生が開始します。音量等にご注意ください。');
        putHtmlNavibarV2();






?>
        <div id="toptableoutline">
            <table>
                <thead>
                    <tr class="info">
                        <th colspan="3">タイトル</th>
                    </tr>
                    <tr class="info">
                        <th>日付</th>
                        <th>メディア</th>
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
                        <td><?= e(evmediaC2t($evdata['evmedia'])) ?></td>
                        <td><?= e(evtypeC2t($evdata['evtype'])) ?></td>
                    </tr>
                    <tr class="info">
                        <td colspan="3">
                            <?php
                            $videoID = ''; //JSコード挿入判定に使うので未定義じゃだめ
                            if ($eventValidateUrl = filter_var($event_url, FILTER_VALIDATE_URL)) {
                                $videoID = videoIDfromYTurl($event_url);
                                print "<a href=\"" . $eventValidateUrl . "\">" . e($event_url) . "</a>"; //. ($videoID ? "/ID:{$videoID}" : "");
                            } else {
                                print e($event_url);
                            }
                            if ($videoID != '' and in_array($evdata['evmedia'], [1, 10]) === false) { //非公開判定
                                $videoID = '';
                            }
                            ?>

                        </td>
                    </tr>
                    <?php
                    // then desc not equal void string
                    $desc = $evdata['evdesc'];
                    if ($desc) {
                        $desc = e($desc);
                        $desc = nl2br($desc, false); //convert  \n into BR tag 
                        $descindent = str_replace("<br>", "<br>&nbsp;", $desc);
                        print "<tr class=\"info\"><td class=\"description\" colspan=\"3\">【備考】 {$descindent}</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <?php
            // $desc = $evdata['evdesc'];
            // if ($desc) {
            //     $desc = e($desc);
            //     print "<div>[備考] {$desc}</div>";
            // }
            ?>
        </div>
        <div id="playerContainer">
            <div id="player"></div>
        </div>
    <?php
    }
    ?>
    <div id="jsbutton">
        <div class="backbutton"><a href="javascript:history.back()"><img src="arrow_back.svg"><span class="jsbuttonspan">戻る</span></a></div>

        <?php if ($videoID) {
            print ' <div class="playbutton"><a href="javascript:void(0);" onclick="pause();" title="停止・固定解除"><span class="jsbuttonspan"></span><img src="pause.svg"><img src="eject.svg"></a></div>';
        }
        ?>

        <div class="copybutton">
            <a href="javascript:void(0);" onclick="OnCopyTitleOnlyClick();" title="クリップボードへコピー(タイトルのみ)"><img src="content_copy.svg"><span class="jsbuttonspan">ss</span></a>
        </div>
        <div class="copybutton_2">
            <a href="javascript:void(0);" onclick="OnCopyClick();" title="クリップボードへコピー"><img src="content_copy.svg"><span class="jsbuttonspan">full</span></a>
        </div>

    </div>
    <div id="tableoutline">
        <div class="table">
            <div class="table_head">
                <div class="table-grid">
                    <div class="cell">▷</div>

                    <div class="cell">N</div>
                    <div class="cell">time</div>
                    <div class="cell">曲 名</div>
                    <div class="cell">アーティスト/P</div>
                    <div class="cell">G</div>
                    <div class="cell">Rel.</div>
                    <div class="cell">TieUp</div>
                    <div class="cell">メモ</div>
                </div><!-- table-grid close -->
            </div><!-- table_head close -->

            <div class="tbody">

                <?php
                //initialize
                $stock1 = ""; // no timestamp 
                $stock_ts = ""; //time title / artist
                $stock_spl = ''; //time title
                //SELECT setlist
                $s = $db->query("select seqnum,time,sname,artist,vocap,tieup,tbsong.songid,tbsong.arrng,memo,tbsong.genre,tbsong.relsd from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng= tbsong.arrng {$dbwhere} order by tbvocal.seqnum;");

                while ($row = $s->fetch(PDO::FETCH_ASSOC)) {

                    $relsd = $row['relsd']; //release date NULL treatment
                    $relsd = is_null($relsd) ? '' : $relsd;
                    $relsyear = $relsd == '' ? '' : substr($relsd, 0, 4);


                    //print_r ($row);
                    //  for wiki or youtube comment
                    $memotext = $row['memo'] ? "〔" . e($row['memo']) . "〕" : "";
                    $diffN = e($row['sname']);
                    $diff1 = $diffN . ($row['artist'] . $row['vocap'] ? " / " . e($row['artist']) . (($row['artist'] and $row['vocap']) ? "," : "") . e($row['vocap']) : "") . $memotext . "\n"; // 
                    $stock1 = $stock1 . $diff1;
                    $stock_ts = $stock_ts . e($row['time']) . " " . $diff1;
                    $stock_spl = $stock_spl . e($row['time']) . " " . $diffN . "\n";

                ?>
                    <div class="table-grid">


                        <?php
                        if (!(empty($row['time']) or empty($event_url)) and ($evmedia == '1' or $evmedia == '10')) {
                            print "<div  class=\"cell\"><a href=\"javascript:void(0);\" onclick=\"seekAndPlay(" . timeStamp2Seconds($row['time']) . ");\">" . "▷Play" . "</a></div><div class=\"cell\">" . e($row['seqnum']) . "</div><div class=\"cell\"> <a href=\"" . makeTimestamplink(e($event_url), $row['time']) . "\">" . e($row['time']) . "</a></div>";
                        } else {
                            print "<div class=\"cell\"></div><div class=\"cell\">" . e($row['seqnum']) . "</div><div class=\"cell\">" . e($row['time']) . "</div>";
                        }



                        ?>


                        <div class="cell">
                            <?php
                            //
                            print '<a href="shistory.php?sid=' . e($row['songid']) . '">' . e($row['sname']) . '</a>';
                            ?>
                        </div>



                        <div class="cell"><?= e($row['artist']) . (($row['artist'] and $row['vocap']) ? "/" : "") .  e($row['vocap']) ?></div>
                        <div class="cell"><?= e($row['genre']) ?></div>
                        <div class="cell <?= $relsd ? relclass($relsd) : '' ?>" title="<?= $relsd ?>"><?= e($relsyear) ?></div>
                        <div class="cell"><?= e($row['tieup']) ?></div>
                        <div class="cell"><?= e($row['memo']) ?></div>
                    </div><!-- table-grid close -->

            <?php

                }
            } catch (PDOException $e) {
                die("Error:{$e->getMessage()}");
            }
            ?>
            </div> <!-- tbody close -->
        </div><!-- table close -->
        <div>リリース日の色分け <span class="new">現在から1年以内 </span><span class="rei"> 令和(2019.5〜) </span><span class="mil"> 平成中後期(2001〜2019.4) </span><span class="her"> 平成初期(1989〜2000) </span><span class="syo"> 1988年以前 </span></div>

    </div>
    <!-- for wiki and youtube comment
<?php
print $stock1 . "\n\n";
print $stock_ts . "\n\n";
print "-->\n";
$setlistWithTs = str_replace(['`', '&apos;', '&amp;', '&quot;', '&lt;', '&gt;'], [' ', "'", '&', '"', '<', '>'], $stock_ts);
$setlistWithTsTO = str_replace(['`', '&apos;', '&amp;', '&quot;', '&lt;', '&gt;'], [' ', "'", '&', '"', '<', '>'], $stock_spl);
//$videoID = '6JXHC96S9Qc';

$scriptYTPlayerAPI = '';
if ($videoID) {
    $scriptYTPlayerAPI = <<<EOD
<script>
  var tag = document.createElement('script');

  tag.src = "https://www.youtube.com/iframe_api";
  var firstScriptTag = document.getElementsByTagName('script')[0];
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

var player;
function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
        videoId : '{$videoID}',
        width   : 360,
        height  : 202,
          playerVars : {
          controls:1,
          playsinline:1,
          mute:1,
        },
        events: {
      'onStateChange': onPlayerStateChange,
        }
    });
}
function seekAndPlay(seconds=0){
// document.getElementById("playerContainer").classList.add("styleSticky");
//player.unMute();
if (player.isMuted()) {
    player.unMute();
    player.setVolume(5);
    }

player.seekTo(seconds, true);
player.playVideo();
}

function pause(){
if (player.getPlayerState()===1){
    player.pauseVideo();
    //document.getElementById("playerContainer").classList.remove("styleSticky");
//}else{
//    player.playVideo();
    }
document.getElementById("playerContainer").classList.remove("styleSticky");
}

function onPlayerStateChange(event){

if(event.data ===1){ //play mode
  document.getElementById("playerContainer").classList.add("styleSticky");

//}else if(event.data ===2){ //pause mode
//  document.getElementById("playerContainer").classList.remove("styleSticky");

    }



}
</script>
EOD;
}
$script = <<<EOD
{$scriptYTPlayerAPI}
<script type="text/javascript">
    const copyTxt=`{$setlistWithTs}`;
    const copyTxtTO=`{$setlistWithTsTO}`;
    function OnCopyClick() {
    navigator.clipboard.writeText(copyTxt).then(function() {
         alert("フルフォーマットコピーしました");
     }, function(err) {
         console.error('Error: ', err);
     });
        }
     function OnCopyTitleOnlyClick() {
    navigator.clipboard.writeText(copyTxtTO).then(function() {
         alert("ショートフォーマットでコピーしました");
     }, function(err) {
         console.error('Error: ', err);
     });
        }
</script>
EOD;
putHtmlContainerCloseV2($script);
