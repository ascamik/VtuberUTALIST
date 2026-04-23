<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'chckdate.php';


// Import the necessary classes
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

// Include the composer autoload file
require 'vendor/autoload.php';
require_once 'sen_cnfg0001.php';
// Setup a new Eloquent Capsule instance

$title = '全曲（条件指定）〈管理〉';
$h2 = "〈管理〉全曲一覧・条件指定表示";

putHtmlHeader($title, $h2);

if ($user = Sentinel::check()) {
    // ログインしているアカウントをチェック
    print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
    $admin = 'admin';
} else {
    putHtmlNavibar();
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";
    $admin = '';

    putHtmlContainerClose();
    exit;
    // ここで終了
}















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

//クエリ　ソート
$icode2sortby = [
    'y' => 'order by tbsong.yomi COLLATE utf8mb4_unicode_ci',
    'v' => 'order by tbsong.vocap,tbsong.yomi COLLATE utf8mb4_unicode_ci',
    'c' => 'order by count(*),songid',
    'd' => 'order by count(*) DESC,songid',
    'l' => 'order by max(evdate),songid',
    'm' => 'order by max(evdate) DESC,songid',
    'r' => 'order by tbsong.relsd DESC,songid',
    'a' => 'order by tbsong.artist,tbsong.yomi  COLLATE utf8mb4_unicode_ci'
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

//print $having;

//    $dbsearch=""; //仮

//$title='全曲（条件指定）〈管理〉';
//$h2="〈管理〉全曲一覧・条件指定表示";
// $note = <<<EOD
// こちらは実験的ページです。データベースの負荷により、動作しない場合があります。もし、応答がない／エラーになる場合は1分ほど待って再読込をお試し下さい。再度エラーになるようなら操作を中止して、ページを閉じてください
// EOD;
$note = <<<EOD
こちらは実験的ページです。もし、続けて応答がない／エラーになる場合は操作を中止して、ページを閉じてください
EOD;

//putHtmlHeader($title,$h2);
//putHtmlText($note);
putHtmlNavibar($admin);
?>

<!-- form start -->
<div id="formcontainer">
    <form method="GET" action="ordrlist_ex.php">
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
            <input type="radio" name="sort" value="v" <?= $getsortstr == 'v' ? 'checked' : '' ?>><label>ボカロP名</label>
            <input type="radio" name="sort" value="a" <?= $getsortstr == 'a' ? 'checked' : '' ?>><label>アーティスト名</label>
            <input type="radio" name="sort" value="c" <?= $getsortstr == 'c' ? 'checked' : '' ?>><label>回数（↗昇順）</label>
            <input type="radio" name="sort" value="d" <?= $getsortstr == 'd' ? 'checked' : '' ?>><label>回数（↘降順）</label>
            <input type="radio" name="sort" value="l" <?= $getsortstr == 'l' ? 'checked' : '' ?>><label>直近日付（昔→今）</label>
            <input type="radio" name="sort" value="m" <?= $getsortstr == 'm' ? 'checked' : '' ?>><label>直近日付（今→昔）</label>
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
<?php
if (isset($_GET['mode']) and $_GET['mode'] == 'wikip') {

    print '<pre>';
} else {
?>
    <!-- リリース日はwikipedia記載のシングル・アルバム発売日または配信開始日の早い方、ボカロはニコニコ等投稿日　ゲーム等の初出が発売よりかなり早いか、CD・配信販売がない場合は初出日　この日付は制作発表年代の参考のためのものです　確実性の要求される用途には使えません-->
    <div id="tableoutline">
        <table>
            <thead>
                <tr>
                    <th class="songid_h">ID</th>
                    <th class="s_name">曲名</th>
                    <th>アーティスト</th>
                    <th>ボカロP</th>
                    <th>TieUp</th>
                    <th class="one_em">G</th>
                    <th class="date_w">直近</th>
                    <th class="two_em">回数</th>
                    <th class="date_w">リリース</th>
                </tr>
            </thead>
            <tbody>
                <?php
            }


            //connection to DB
            try {
                $db = getDb();
                //SELECT
                //$s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,count(*) from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng=tbsong.arrng {$dbsearch} group by tbvocal.songid,tbvocal.arrng order by tbsong.vocap COLLATE utf8mb4_unicode_ci;");

                //$nomedia="evmedia!='2' and";
                //$on_genre ='genre regexp"[VPRGIAo]"';
                //$having ='having max(evdate)<"2022-12-31"';
                //$order = 'order by count(*) DESC';

                $s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,tbsong.relsd,count(*),max(evdate) from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng=tbsong.arrng  join tbevent using(evwcode) where {$nomedia}  {$on_genre} group by tbvocal.songid,tbvocal.arrng  {$having} {$order} ;");

                while ($row = $s->fetch(PDO::FETCH_ASSOC)) {

                    $relsd = $row['relsd']; //release date NULL treatment
                    $relsd = is_null($relsd) ? '' : $relsd;
                    //print_r ($row);
                    //Special format output for wikiwiki markup style
                    if (isset($_GET['mode']) and $_GET['mode'] == 'wikip') {
                        print "|" . e($row['sname']) . "|" . e($row['artist']) . "|" . e($row['tieup']) . "|" . e($row['vocap']) . "|" . e($row['count(*)']) . "|" . e($row['genre']) . "|\n";
                    } else {
                ?>
                        <tr>

                            <td class="songid"><a href="udsong_ex.php?songid=<?= $row['songid'] ?>&arrng=<?= e($row['arrng']) ?>" onclick="window.open(this.href,'edior','menubar=no,top=100,left=500,width=735,height=980'); return false;"><?= $row['arrng'] ? e($row['songid']) . '-' . e($row['arrng']) : e($row['songid']) ?></a></td>


                            <td class="s_name"><a href="
        <?php
                        //
                        print 'shistory.php?sid=' . e($row['songid']);
        ?>
        "><?= e($row['sname']) ?></td>



                            <td><?= e($row['artist']) ?></td>
                            <td><?= e($row['vocap']) ?></td>

                            <td><?= e($row['tieup']) ?></td>


                            <td class="one_em"><?= e($row['genre']) ?></td>
                            <td><?= e($row['max(evdate)']) ?></td>
                            <td class="two_em"><?= e($row['count(*)']) ?></td>
                            <td><?= e($relsd) ?></td>
                        </tr>

                <?php
                    }
                }
            } catch (PDOException $e) {
                die("Error:{$e->getMessage()}");
            }

            if (isset($_GET['mode']) and $_GET['mode'] == 'wikip') {
                print '</pre>';
            } else {
                ?>
            </tbody>
        </table>
    </div>


<?php
            }
            //putHtmlText($note);
            putHtmlContainerClose();
