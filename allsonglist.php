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
        $jeword = e(str_replace("'", "\'", $word));
        $eword = e($word);

        $html = "<a href=\"javascript:void(0)\" onclick=\"sendSearchBox('$jeword')\">{$eword}</a>";
    }
    return $html;
}

$title = '曲名一覧β（全曲）';
$h2 = "曲名一覧表示β【全曲】";
$aditionalcss = '<link rel="stylesheet" href="table-grid-resp-allsonglist.css?b2e5aa40"><link rel="stylesheet" href="allsonglist.css?b2e5aa5b">';
putHtmlHeader($title, $h2, $aditionalcss);
//putHtmlHeader($title, $h2);
putHtmlNavibar();
?>
<div class="search-area"><img src="search_24dp_1F1F1F_FILL0_wght400_GRAD0_opsz24.svg">
    <input
        type="text"
        id="searchBox"
        placeholder="曲名・よみ・歌手・P・アニメで検索"> <button class="clear-btn">×</button>

    <div id="searchCount"></div>
</div>


<?php
print '<div class="allsonglist_group">
<div class="allsonglist_link"><a href="#tableoutline1">↑</a></div>
<div class="allsonglist_link"><a href="#song_1">数字／英字</a></div>
<div class="allsonglist_link"><a href="#song_a">あ（ゔ）</a></div>
<div class="allsonglist_link"><a href="#song_k">か</a></div>
<div class="allsonglist_link"><a href="#song_s">さ</a></div>
<div class="allsonglist_link"><a href="#song_t">た</a></div>
<div class="allsonglist_link"><a href="#song_n">な</a></div>
<div class="allsonglist_link"><a href="#song_h">は</a></div>
<div class="allsonglist_link"><a href="#song_m">ま</a></div>
<div class="allsonglist_link"><a href="#song_y">や・ら・わ</a></div>
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
            <div class="table-grid">
                <!--<th class="songid_h">ID</th>-->
                <div class="cell">曲名</div>
                <div class="cell">アーティスト/P</div>

                <div class="cell">リリース</div>
                <div class="cell">TieUp </div>
                <div class="cell">G</div>
                <div class="cell">直近歌唱</div>
                <div class="cell">回数</div>

            </div><!-- table-grid close -->
        </div><!-- table_head close -->
    </div><!-- table close -->
    <?php



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

            // print "<div class=\"table-grid\" id=\"song_{$index}\"><div class=\"songlistanchor\">{$pagemarker}</div><div class=\"songlistanchor\"></div><div class=\"songlistanchor\"></div><div class=\"songlistanchor\"></div><div class=\"songlistanchor\"></div><div class=\"songlistanchor\"></div></div>";
            print "<div class=\"list_index\" id=\"song_{$index}\"></div><div class=\"songlistanchor\">{$pagemarker}</div>
                        ";
            // if ($index != '1') { //2回目以降は出力
            print '<div class="table">';
            // }
            print '<div class="tbody">';

            while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
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
                $scripthtml = "<button class=\"appendSLBtn\" data-songid=\"{$esongid}\" data-arrng=\"{$earrng}\")\">▷</button>";
                if (intval($row['count(*)']) > 2) {
                    $boldcss4helper = 'table-grid strong4h';
                }
                $search = mb_strtolower(
                    mb_convert_kana(
                        implode(' ', [
                            $row['sname'],
                            $row['artist'],
                            $row['vocap'],
                            $row['tieup'],
                            $row['yomi']
                        ]),
                        'asKV'
                    )
                );
    ?>
                <div class="<?= $boldcss4helper ?> song-row" data-songid="<?= $esongid ?>" data-arrng="<?= $earrng ?>" data-search="<?= e($search) ?>">
                    <div class="cell"><span title="<?= 'ID=' . $sid ?>"><a href="<?php

                                                                                    print 'shistory.php?sid=' . e($row['songid']);
                                                                                    ?>"><?= e($row['sname']) ?></a></span></div>


                    <div class="cell"><?= s($row['artist']) . (($row['artist'] and $row['vocap']) ? "/" : "") .  s($row['vocap']) ?></div>
                    <div class="cell <?= $relsd ? relclass($relsd) : '' ?>"><span title="<?= $relsd ?>"><?= e($relsyear) ?></span></div>
                    <div class="cell"><?= e($row['tieup']) ?></div>
                    <div class="cell"><?= e($row['genre']) ?></div>
                    <div class="cell"><?= e($row['max(evdate)']) ?></div>
                    <div class="cell"><?= e($row['count(*)']) ?></div>
                    <div class="cell hidden"><?= e($row['yomi']) ?></div>

                </div>

    <?php


            } //while close
            print '</div><!-- tbody close -->            </div><!-- table close -->';
        } //foreach close
    } catch (PDOException $e) {
        die("Error:{$e->getMessage()}");
    }

    //   </div> <!-- tbody close -->
    // </div><!-- table close -->
    ?>
    <div>リリース日の色分け <span class="new">現在から1年以内 </span><span class="rei"> 令和(2019.5〜) </span><span class="mil"> 平成中後期(2001〜2019.4) </span><span class="her"> 平成初期(1989〜2000) </span><span class="syo"> 1988年以前 </span></div>
</div><!-- tableoutline close -->
</div><!-- songlist container close -->
<?php
$script = <<<'EOD'
        <script>
            function sendSearchBox(searchWord) {
            console.log(searchWord);
            const inputElement = document.getElementById("searchBox");
            inputElement.value = searchWord;
            filterRows();
            }
            const inputElement = document.getElementById("searchBox");
            const clearBtn = document.querySelector('.clear-btn');
            
            // Xボタンをクリックしたときの処理
            clearBtn.addEventListener('click', () => {
                inputElement.value = ''; // 文字列を空にする
                inputElement.focus();   // 入力欄にフォーカスを戻す
                filterRows();
            });
        </script>
        <script>
            function sendPost(searchWord, target) {
                // 送信用の隠しフォームを動的に生成
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'searchsong4u.php'; // 送信先URL

                // 1つ目のデータ（キーワード）
                const inputWord = document.createElement('input');
                inputWord.type = 'hidden';
                inputWord.name = 'keyword';
                inputWord.value = searchWord;
                form.appendChild(inputWord);

                // 2つ目のデータ（検索ターゲット）
                const inputTarget = document.createElement('input');
                inputTarget.type = 'hidden';
                inputTarget.name = 'target';
                inputTarget.value = target;
                form.appendChild(inputTarget);

                // フォームを本体に追加して送信
                document.body.appendChild(form);
                form.submit();
            }
        </script>
        <script>
        // 逐次検索スクリプト
        const rows = [...document.querySelectorAll('.song-row')];

const searchBox = document.getElementById('searchBox');
const searchCount = document.getElementById('searchCount');

let timer;

searchBox.addEventListener('input', () => {

    clearTimeout(timer);

    timer = setTimeout(filterRows, 120);

});

function filterRows() {

    const words = searchBox.value
        .normalize('NFKC')
        .toLowerCase()
        .trim()
        .split(/\s+/)
        .filter(Boolean);


        // 検索文字が空なら全件表示
    if (words.length === 0) {
        rows.forEach(row => row.classList.remove('song-hidden'));
        searchCount.textContent = `${rows.length} / ${rows.length} 件`;
        return;
    }

    let visible = 0;

    rows.forEach(row => {

        const target = row.dataset.search;

        const match = words.every(word => target.includes(word));

        if (match) {
            row.classList.remove('song-hidden');
            visible++;
        } else {
            row.classList.add('song-hidden');
        }


    });

    searchCount.textContent =
        `${visible} / ${rows . length} 件`;
}
        </script>
EOD;
putHtmlContainerClose($script);
