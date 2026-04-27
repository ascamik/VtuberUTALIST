<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'chckdate.php';
require_once 'Relsd2cssclass.php';
require_once 'OrdrSubNcsv.php';
/*
   //GET クエリ処理
    $icode2regex=['1'=>'^[0-9a-zA-Z]','a'=> '^[あいうえお]','k'=>'^[かきくけこがぎぐげご]','s'=>'^[さしすせそざじずぜぞ]','t'=>'^[たちつてとだぢづでど]','n'=>'^[なにぬねの]','h'=>'^[はひふへほぱぴぷぺぽばびぶべぼ]','m'=>'^[まみむめも]','y'=>'^[やゆよわん]'];
    $icode2char=['1'=>'数字・英字','a'=>'あ（ゔ）','k'=>'か','s'=>'さ','t'=>'た','n'=>'な','h'=>'は','m'=>'ま','y'=>'や・わ・ん',];
    //default setting for display page
    $dbsearch='tbsong.yomi REGEXP \''.$icode2regex['1'].'\''; 
    $pagemarker=$icode2char['1'];
    if(isset($_GET['i'])){
        if(isset($icode2regex[$_GET['i']])){
        
            $dbsearch='tbsong.yomi REGEXP "'.$icode2regex[$_GET['i']].'"';
            $pagemarker=$icode2char[$_GET['i']] ;
        }

    } */
//excange CSS classname from release date ---> // external function  in the relsd2cssclass //
// function relclass($releasedate){
//     $dateParts = explode('-', $releasedate);
//     if (count($dateParts) === 3 && checkdate($dateParts[1], $dateParts[2], $dateParts[0])) {
//         $unixtimerelsd=strtotime($releasedate);
//         if($unixtimerelsd < strtotime('1989-01-01')){
//             return 'syo'; //syouwa

//         }elseif($unixtimerelsd < strtotime('2001-01-01')){
//             return 'her'; //heisei early

//         }elseif($unixtimerelsd < strtotime('2019-05-01')){
//             return 'mil'; //heisei late millenium

//         }elseif($unixtimerelsd < strtotime('now')-60*60*24*365){
//             return 'rei'; //reiwa

//         }else{
//             return 'new'; // new
//         }




//     }else{
//         return'invalid';

//     }
// }


//クエリ　ソート
$icode2sortby = [
    'y' => 'order by tbsong.yomi COLLATE utf8mb4_unicode_ci',
    'v' => "order by (tbsong.vocap='' AND tbsong.artist='') ASC,CONCAT(tbsong.vocap,tbsong.artist) COLLATE utf8mb4_unicode_ci,tbsong.yomi COLLATE utf8mb4_unicode_ci",
    #   'v' => "order by tbsong.vocap='' ASC,tbsong.artist='' ASC,CONCAT(tbsong.vocap,tbsong.artist),tbsong.yomi COLLATE utf8mb4_unicode_ci",
    #    'v' => "order by tbsong.vocap='' ASC,tbsong.artist='' ASC,tbsong.vocap,tbsong.artist,tbsong.yomi COLLATE utf8mb4_unicode_ci",
    'c' => 'order by count(*),songid',
    'd' => 'order by count(*) DESC,songid',
    'l' => 'order by max(evdate),songid',
    'm' => 'order by max(evdate) DESC,songid',
    'r' => 'order by tbsong.relsd DESC,songid',
    'a' => "order by (tbsong.artist='' AND tbsong.vocap='') ASC,CONCAT(tbsong.artist,tbsong.vocap) COLLATE utf8mb4_unicode_ci,tbsong.yomi  COLLATE utf8mb4_unicode_ci"
    #  'a' => "order by tbsong.artist='' ASC,tbsong.vocap='' ASC,CONCAT(tbsong.artist,tbsong.vocap),tbsong.yomi  COLLATE utf8mb4_unicode_ci"
    #   'a' => "order by tbsong.artist='' ASC,tbsong.vocap='' ASC,tbsong.artist,tbsong.vocap,tbsong.yomi  COLLATE utf8mb4_unicode_ci"
];

$getsortstr = 'y'; // default seting if not set
$order = $icode2sortby[$getsortstr];

if (isset($_GET['sort'])) {
    if (isset($icode2sortby[$_GET['sort']])) {

        $order = $icode2sortby[$_GET['sort']];
        $getsortstr = $_GET['sort'];
    }
}
//クエリ処理 genre
$pre_ongenre = '';
if (isset($_GET['gp'])) {
    if ($_GET['gp'] == '1') {
        $pre_ongenre = $pre_ongenre . 'PR';
    }
}
if (isset($_GET['ga'])) {
    if ($_GET['ga'] == '1') {
        $pre_ongenre = $pre_ongenre . 'AG';
    }
}
if (isset($_GET['gi'])) {
    if ($_GET['gi'] == '1') {
        $pre_ongenre = $pre_ongenre . 'I';
    }
}
if (isset($_GET['gv'])) {
    if ($_GET['gv'] == '1') {
        $pre_ongenre = $pre_ongenre . 'V';
    }
}
if (isset($_GET['go'])) {
    if ($_GET['go'] == '1') {
        $pre_ongenre = $pre_ongenre . 'o';
    }
}
if ($pre_ongenre == '') {
    $pre_ongenre = 'PRAGIVo'; //if all nothing
}
$on_genre = 'genre regexp"[' . $pre_ongenre . ']"';
//   print $pre_ongenre.','.$on_genre;

//クエリ処理 動画除外 mediatype>=10 is video.

if (isset($_GET['ex'])) {
    if ($_GET['ex'] == '1') {
        $nomedia = "evmedia<'10' and";
        $ex_v = 1;
    } else {
        $ex_v = 0;
        $nomedia = "";
    }
} else {
    $ex_v = 0;
    $nomedia = "";
}

// クエリ期間処理
$select_y = '2022'; // default setting
$select_m = '07';

if (isset($_GET['spantype']) and isset($_GET['cy']) and isset($_GET['cm'])) {
    if ($_GET['spantype'] == '1') {
        $yandm = chkYearMonth($_GET['cy'], $_GET['cm']);
        $having = 'having max(evdate)<"' . $yandm[0] . '-' . $yandm[1] . '-01"';
        $spantype = '1';
        $select_y = $yandm[0];
        $select_m = $yandm[1];
    } else if ($_GET['spantype'] == '2') {
        $yandm = chkYearMonth($_GET['cy'], $_GET['cm']);
        $having = 'having max(evdate)>"' . $yandm[0] . '-' . $yandm[1] . '-01"';
        $spantype = '2';
        $select_y = $yandm[0];
        $select_m = $yandm[1];
    } else {
        $spantype = '0';
        $having = '';
    }
} else {
    $spantype = '0';
    $having = '';
}

// query LIMIT
if (isset($_GET['lim'])) {
    if (intval($_GET['lim']) > 0 and intval($_GET['lim']) < 101) {
        $limit = "LIMIT " . strval(intval($_GET['lim']));
    } else {
        $limit = "";
    }
} else {
    $limit = "";
}

if (isset($_GET['mode']) and $_GET['mode'] == 'wikip') {
    $mode = 'wikip';
} elseif (isset($_GET['mode']) and $_GET['mode'] == 'csvexprt') {
    $mode = 'csv';
} else {
    $mode = '';
}
//print $having;

//    $dbsearch=""; //仮

$title = '全曲（条件指定）';
$h2 = "全曲一覧・条件指定表示";

/*
$note = <<<EOD
原曲リリース日の色分けは試験的実装です。今後区分・色が変更されるかもしれません。
EOD;
*/

$aditionalcss = '<link rel="stylesheet" href="table-grid-resp.css?b2e5aa2e">';
putHtmlHeader($title, $h2, $aditionalcss);
//putHtmlHeader($title, $h2);
//putHtmlText($note);
putHtmlNavibar();
?>

<!-- form start -->
<div id="formcontainer">
    <form method="GET" action="ordrlist.php">
        <div class="formparts">

            <label class=label>表示ジャンル</label>
            <!-- <?= $pre_ongenre ?> -->
            <input type="checkbox" name="gp" value="1" <?= (false !== strpos($pre_ongenre, 'PR') ? 'checked' : '') ?>><label>P,R(J-POP)</label>
            <input type="checkbox" name="ga" value="1" <?= (false !== strpos($pre_ongenre, 'AG') ? 'checked' : "") ?>><label>A,G(anime,Game)</label>
            <input type="checkbox" name="gi" value="1" <?= (false !== strpos($pre_ongenre, 'I') ? 'checked' : "") ?>><label>I(The Idolmaster)</label>
            <input type="checkbox" name="gv" value="1" <?= (false !== strpos($pre_ongenre, 'V') ? 'checked' : "") ?>><label>V(Vocalo)</label>
            <input type="checkbox" name="go" value="1" <?= (false !== strpos($pre_ongenre, 'o') ? 'checked' : "") ?>><label>o(original)</label>
        </div>

        <div class="formparts">

            <label class="label">並べ替え</label>
            <input type="radio" name="sort" value="y" <?= $getsortstr == 'y' ? 'checked' : '' ?>><label>曲名</label>
            <input type="radio" name="sort" value="a" <?= $getsortstr == 'a' ? 'checked' : '' ?>><label>アーティスト/ボカロP </label>
            <input type="radio" name="sort" value="v" <?= $getsortstr == 'v' ? 'checked' : '' ?>><label>同左（ボカロP優先）</label>
            <input type="radio" name="sort" value="c" <?= $getsortstr == 'c' ? 'checked' : '' ?>><label>回数（少）</label>
            <input type="radio" name="sort" value="d" <?= $getsortstr == 'd' ? 'checked' : '' ?>><label>回数（多）</label>
            <input type="radio" name="sort" value="l" <?= $getsortstr == 'l' ? 'checked' : '' ?>><label>直近日付（昔）</label>
            <input type="radio" name="sort" value="m" <?= $getsortstr == 'm' ? 'checked' : '' ?>><label>直近日付（新）</label>
            <input type="radio" name="sort" value="r" <?= $getsortstr == 'r' ? 'checked' : '' ?>><label>リリース日</label>
        </div>
        <div class="formparts">

            <label class="label">動画(L:LilieLied)の扱い</label>
            <input type="radio" name="ex" value="0" <?= $ex_v != '1' ? 'checked' : '' ?>><label>ライブと同様に扱う（表示する）
                <input type="radio" name="ex" value="1" <?= $ex_v == '1' ? 'checked' : '' ?>><label>除外する


                </label>
        </div>

        <div class="formpack">

            <div class="formparts">

                <label class="label">直近日付で選別</label>
                <input type="radio" name="spantype" value="0" <?= $spantype == '0' ? 'checked' : '' ?>><label>しない</label>
                <input type="radio" name="spantype" value="1" <?= $spantype == '1' ? 'checked' : '' ?>><label>未来側除外（最近歌っていない曲を探す）</label>
                <input type="radio" name="spantype" value="2" <?= $spantype == '2' ? 'checked' : '' ?>><label>過去側除外（最近歌っている曲を探す）</label>
            </div>
            <div class="formparts">

                <label class="label">選別の起点を指定</label>
                <select name="cy">
                    <?php
                    $currentYear = intval(date('Y'));
                    for ($y = 2020; $y <= $currentYear; $y++) {
                        print "<option value=\"{$y}\"" . (intval($select_y) == $y ? ' selected' : '') . ">{$y}年</option>\n";
                    }
                    ?>

                </select>
                <!-- <label class="label"></label>-->
                <select name="cm">
                    <option value="01" <?= $select_m == '01' ? 'selected' : '' ?>>1月</option>
                    <option value="02" <?= $select_m == '02' ? 'selected' : '' ?>>2月</option>
                    <option value="03" <?= $select_m == '03' ? 'selected' : '' ?>>3月</option>
                    <option value="04" <?= $select_m == '04' ? 'selected' : '' ?>>4月</option>
                    <option value="05" <?= $select_m == '05' ? 'selected' : '' ?>>5月</option>
                    <option value="06" <?= $select_m == '06' ? 'selected' : '' ?>>6月</option>
                    <option value="07" <?= $select_m == '07' ? 'selected' : '' ?>>7月</option>
                    <option value="08" <?= $select_m == '08' ? 'selected' : '' ?>>8月</option>
                    <option value="09" <?= $select_m == '09' ? 'selected' : '' ?>>9月</option>
                    <option value="10" <?= $select_m == '10' ? 'selected' : '' ?>>10月</option>
                    <option value="11" <?= $select_m == '11' ? 'selected' : '' ?>>11月</option>
                    <option value="12" <?= $select_m == '12' ? 'selected' : '' ?>>12月</option>

                </select>

            </div>
        </div>
        <div id="submitbutton">
            <input type="submit" value="送信／Submit">
        </div>
    </form>
</div>
<!-- form area -->
<div id="tableoutline">
    <?php
    if ($mode == '') {

    ?>
        <div>
            <div class="backbutton"><a href="javascript:history.back()"><img src="arrow_back.svg"><span class="jsbuttonspan">戻る</span></a></div>
        </div>
        <div>
            &nbsp;※リリース日の色分け <span class="new">現在から1年以内 </span><span class="rei"> 令和(2019.5〜) </span><span class="mil"> 平成中後期(2001〜2019.4) </span><span class="her"> 平成初期(1989〜2000) </span><span class="syo"> 1988年以前 </span>
        </div>
    <?php
    }

    putHtmlOrdrTable($order, $on_genre, $limit, $nomedia,  $having, $mode);

    if ($mode == '') {

    ?>


        <div>リリース日の色分け <span class="new">現在から1年以内 </span><span class="rei"> 令和(2019.5〜) </span><span class="mil"> 平成中後期(2001〜2019.4) </span><span class="her"> 平成初期(1989〜2000) </span><span class="syo"> 1988年以前 </span></div>
    <?php
    }
    ?>


</div><!-- tableoutline -->
<?php
//putHtmlText($note);
putHtmlContainerClose();
