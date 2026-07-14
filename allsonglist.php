<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'chckdate.php';
require_once 'Relsd2cssclass.php';
//require_once 'OrdrSubNcsv.php';
//   require_once 'Relsd2cssclass.php';
function s($word)
{
    if ($word == "") {
        $html = "";
    } else {
        $word = trim($word);
        $jeword = e(str_replace('"', '\"', $word));
        $eword = e($word);

        $html = "<a href=\"javascript:void(0)\"  class=\"search-link\"  data-search-term=\"{$jeword}\">{$eword}</a>";
    }
    return $html;
}

$title = '曲名一覧[RC]（全曲）';
$h2 = "曲名一覧表示[RC]【全曲】";
$aditionalcss = '<link rel="stylesheet" href="table-grid-resp-allsonglist.css?b2e5aa43"><link rel="stylesheet" href="allsonglist.css?b2e5aa5f">';
putHtmlHeader($title, $h2, $aditionalcss);
//putHtmlHeader($title, $h2);
//putHtmlNavibarV2();
?>
<div id="v2navicontainer">
    <div class="v2menubar">
        <a href="./">
            <div class="v2navilink"><img
                    src="vecteezy_fleur-de-lis-heraldic-symbol_colored.svg"></div>
        </a>
        <a href="evlist.php?">
            <div class="v2navilink">配信一覧</div>
        </a>
        <a href="ordrlist.php?">
            <div class="v2navilink">条件指定一覧</div>
        </a>
    </div>
    <div class="search-area"><img src="search_24dp_1F1F1F_FILL0_wght400_GRAD0_opsz24.svg">
        <input
            type="text"
            id="searchBox"
            placeholder="曲名・よみ・歌手・P・アニメで検索"> <button class="clear-btn">×</button>

        <div id="searchCount"></div>
    </div>
</div><!--close navicontainerv2 -->
<div class="tag-cloud"><!-- タグ選択UI  -->
    <img src="edit_attributes_32dp_1F1F1F_FILL0_wght400_GRAD0_opsz40.svg">
    <label class="btn-checkbox">
        <input type="checkbox" name="tag-chkd" value="O1">
        <span class="btn-content" title="１回のみ歌唱">1回</span>
    </label>
    <label class="btn-checkbox">
        <input type="checkbox" name="tag-chkd" value="WITW">
        <span class="btn-content" title="半年以内に歌っていない（歌唱歴２回以上）">最近歌ってない</span>
    </label>
    <!-- タグ機能が追加された場合、将来ここに追加 -->
    <div class="genre-ui">
        <label class="btn-checkbox">
            <input type="checkbox" name="genre-chkd" value="PR" checked>
            <span class="btn-content" title="J-POPなど">P,R</span>
        </label>
        <label class="btn-checkbox">
            <input type="checkbox" name="genre-chkd" value="AG" checked>
            <span class="btn-content" title="アニメ・ゲーム">A,G</span>
        </label>
        <label class="btn-checkbox">
            <input type="checkbox" name="genre-chkd" value="I" checked>
            <span class="btn-content" title="The Idolmaster">I</span>
        </label>
        <label class="btn-checkbox">
            <input type="checkbox" name="genre-chkd" value="V" checked>
            <span class="btn-content" title="ボカロ">V</span>
        </label>
        <label class="btn-checkbox">
            <input type="checkbox" name="genre-chkd" value="o" checked>
            <span class="btn-content" title="オリ曲・Vシンガー曲">o</span>
        </label>
    </div>
</div>

<?php
print '<div class="allsonglist_group">
<div class="allsonglist_link"><a href="#tableoutline1">↑ top</a></div>
<!--
<div class="allsonglist_link"><a href="#song_1">数字／英字</a></div>
<div class="allsonglist_link"><a href="#song_a">あ（ゔ）</a></div>
<div class="allsonglist_link"><a href="#song_k">か</a></div>
<div class="allsonglist_link"><a href="#song_s">さ</a></div>
<div class="allsonglist_link"><a href="#song_t">た</a></div>
<div class="allsonglist_link"><a href="#song_n">な</a></div>
<div class="allsonglist_link"><a href="#song_h">は</a></div>
<div class="allsonglist_link"><a href="#song_m">ま</a></div>
<div class="allsonglist_link"><a href="#song_y">や・ら・わ</a></div>-->
<div class="info">&#9432;見出しの各項目をクリックで並び替えをします（英数曲名はカナ読みに変更）</div>
</div>
<div class="songlistcontainer">';


$icode2sortby = [
    'y' => 'order by tbsong.yomi COLLATE utf8mb4_unicode_ci',
    's' => 'order by tbsong.sname COLLATE utf8mb4_unicode_ci',
    'v' => 'order by tbsong.vocap,tbsong.yomi COLLATE utf8mb4_unicode_ci',
    'c' => 'order by count(*),songid',
    'd' => 'order by count(*) DESC,songid',
    'l' => 'order by max(evdate),songid',
    'm' => 'order by max(evdate) DESC,songid',
    'r' => 'order by tbsong.relsd DESC,songid',
    'a' => 'order by tbsong.artist,tbsong.yomi  COLLATE utf8mb4_unicode_ci',
];
$icode2regex = ['1' => '^[0-9a-zA-Z∀]', 'a' => '^[あいうえおをゔ]', 'k' => '^[かきくけこがぎぐげご]', 's' => '^[さしすせそざじずぜぞ]', 't' => '^[たちつてとだぢづでど]', 'n' => '^[なにぬねの]', 'h' => '^[はひふへほぱぴぷぺぽばびぶべぼ]', 'm' => '^[まみむめも]', 'y' => '^[やゆよらりるれろわん]'];
$icode2char = ['1' => '数字・英字', 'a' => 'あ（ゔ）', 'k' => 'か', 's' => 'さ', 't' => 'た', 'n' => 'な', 'h' => 'は', 'm' => 'ま', 'y' => 'や・ら・わ'];

//$displayIndex=[];
?>
<div id="tableoutline1">
    <div class="table">
        <div class="table_head">
            <div class="table-grid" id="song-th-row">
                <div class="cell sortable" data-col="0" data-type="text">曲名<span class="sort-mark"></span></div>
                <div class="cell sortable" data-col="1" data-type="text">アーティスト/P<span class="sort-mark"></span></div>
                <div class="cell sortable" data-col="2" data-type="date">リリース<span class="sort-mark"></span></div>
                <div class="cell sortable" data-col="3" data-type="text">TieUp <span class="sort-mark"></span></div>
                <div class="cell sortable" data-col="4" data-type="text">G<span class="sort-mark"></span></div>
                <div class="cell sortable" data-col="5" data-type="date">直近歌唱<span class="sort-mark"></span></div>
                <div class="cell sortable" data-col="6" data-type="number">回数<span class="sort-mark"></span></div>

            </div><!-- table-grid close -->
        </div> <!--table_head close -->
        <!-- </div> table close -->
        <?php
        //print '<div class="table">';
        // }
        print '<div class="tbody" id="song-list">';


        //connection to DB
        try {
            $db = getDb();
            //SELECT
            //$s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,count(*) from tbvodraft join tbsong on tbvodraft.songid=tbsong.songid and tbvodraft.arrng=tbsong.arrng {$dbsearch} group by tbvodraft.songid,tbvodraft.arrng order by tbsong.vocap COLLATE utf8mb4_unicode_ci;");

            //$nomedia="evmedia!='2' and";
            //$on_genre ='genre regexp"[VPRGIAo]"';
            //$having ='having max(evdate)<"2022-12-31"';
            //$order = 'order by count(*) DESC';
            //

            $i = 0; //initialize for jscript Sort function
            foreach ($icode2regex as $index => $regex) {

                $sortby = $icode2sortby['y'];
                $dbsearch = 'tbsong.yomi REGEXP "' . $regex . '"';
                $pagemarker = $icode2char[$index];
                if ($index == '1') {
                    $dbsearch = 'tbsong.sname REGEXP "' . $regex . '"';
                    $sortby = $icode2sortby['s'];
                    //DBの英数字の読みをかなにした場合、英数のページにも表示させるための分岐
                }


                $nomedia = "";
                $on_genre = $dbsearch; //'genre regexp"[VPRGIAo]"';
                $having = '';
                // $order = 'order by tbsong.yomi COLLATE utf8mb4_unicode_ci';
                $order = $sortby;
                $limit = '';

                //$s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.yomi,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,tbsong.relsd from tbsong where {$nomedia}  {$on_genre}   {$having} {$order} {$limit} ;");
                $s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.yomi,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,tbsong.relsd,count(*),max(evdate) from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng=tbsong.arrng  join tbevent using(evwcode) where {$nomedia}  {$on_genre} group by tbvocal.songid,tbvocal.arrng  {$having} {$order} {$limit} ;");


                //print "<div class=\"list_index\" id=\"song_{$index}\"></div><div class=\"songlistanchor\">{$pagemarker}</div>";


                while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                    if ($index != "1" and preg_match('/^[a-zA-Z0-9]/', $row["sname"])) {
                        //Songs with alphanumeric characters are excluded from Hiragana reading search results.
                        //重複掲載しないで読みソートで対応 より良い方法を検討
                        continue;
                    }


                    $boldcss4helper = 'table-grid';
                    $relsd = $row['relsd']; //release date NULL treatment
                    $relsd = is_null($relsd) ? '' : $relsd;
                    $relsyear = $relsd == '' ? '' : substr($relsd, 0, 4);
                    //print_r ($row);
                    //Special format output for wikiwiki markup style

                    //if (intval($row['count(*)']) > 2) {
                    //    $boldcss4helper = 'table-grid strong4h';
                    //}
                    $esongid = e($row['songid']);
                    $earrng = intval(e($row['arrng']));
                    $sid = $earrng ? $esongid . '-' . $earrng : $esongid;

                    //create system TAG
                    $systemTag = "";
                    $count = intval($row['count(*)']);
                    if ($count === 1) {
                        $systemTag = "O1"; //O1  sung only once
                    } elseif ($count > 2) {
                        $boldcss4helper = 'table-grid strong4h';
                    }


                    // 現在から6ヶ月前の日付
                    // 比較する日付（DateTimeオブジェクト）
                    $targetDate = new DateTime($row['max(evdate)']);
                    $sixMonthsAgo = new DateTime('6 months ago');

                    if ($count > 1 && $targetDate < $sixMonthsAgo) {
                        $systemTag = "WITW"; //WITW waiting in the wings 最近半年以内に歌われていない & 2回以上歌われた
                    }
                    $tags = $systemTag;
                    $genre = e($row['genre']);
                    $yomi1 = mb_substr($row['yomi'], 0, 1) . '*';
                    if ($index == '1') {
                        $yomi1 = $yomi . ' ' . mb_substr($row['sname'], 0, 1) . '*';
                    }
                    $search = mb_strtolower(
                        preg_replace(
                            '/\s+/',
                            ' ',
                            mb_convert_kana(
                                implode(' ', [
                                    $row['sname'],
                                    $row['artist'],
                                    $row['vocap'],
                                    $row['tieup'],
                                    $row['yomi'],
                                    $yomi1
                                ]),
                                'asKV'
                            )
                        )
                    );
        ?>
                    <div class="<?= $boldcss4helper ?> song-row" data-songid="<?= $esongid ?>" data-arrng="<?= $earrng ?>" data-search="<?= e($search) ?>" data-tags="<?= e($tags) ?>" data-genre="<?= $genre ?>" data-index="<?= $i++ ?>">
                        <div class="cell" data-sort="<?= e($row['yomi']) ?>"><span title="<?= 'ID=' . $sid ?>"><a href="<?php

                                                                                                                        print 'shistory.php?sid=' . e($row['songid']);
                                                                                                                        ?>"><?= e($row['sname']) ?></a></span></div>


                        <div class="cell"><?= s($row['artist']) . (($row['artist'] and $row['vocap']) ? "/" : "") .  s($row['vocap']) ?></div>
                        <div class="cell <?= $relsd ? relclass($relsd) : '' ?>" data-sorrt="<?= $relsd ?>"><span title="<?= $relsd ?>"><?= e($relsyear) ?></span></div>
                        <div class="cell"><?= e($row['tieup']) ?></div>
                        <div class="cell"><?= $genre ?></div>
                        <div class="cell"><?= e($row['max(evdate)']) ?></div>
                        <div class="cell"><?= e($row['count(*)']) ?></div>


                    </div>

        <?php

                } //while close
                // print '</div><!-- tbody close -->            </div><!-- table close -->';
            } //foreach close
        } catch (PDOException $e) {
            die("Error:{$e->getMessage()}");
        }

        ?>
    </div> <!-- tbody close -->
</div><!-- table close -->
<div>リリース日の色分け <span class="new">現在から1年以内 </span><span class="rei"> 令和(2019.5〜) </span><span class="mil"> 平成中後期(2001〜2019.4) </span><span class="her"> 平成初期(1989〜2000) </span><span class="syo"> 1988年以前 </span></div>
</div><!-- tableoutline close -->
</div><!-- songlist container close -->
<?php
$script = <<<'EOD'
        <script src="allsonglistAX.js"></script>
        <script src="allsonglistSort.js"></script>
EOD;
putHtmlContainerCloseV2($script);
