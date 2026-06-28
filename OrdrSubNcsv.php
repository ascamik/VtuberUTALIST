<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'chckdate.php';
require_once 'Relsd2cssclass.php';
require_once 'Code2text.php';
?>
<?php
//link to search page
function s($word)
{
    if ($word == "") {
        $html = "";
    } else {
        $eword = e($word);
        $html = "<a href=\"javascript:void(0)\" onclick=\"sendPost('$eword', 'artistandp')\">{$eword}</a>";
    }
    return $html;
}



// comma escape for CSV
function ece($source)
{
    return str_replace([','], ['，'], e($source));
}
global $genreCodeMxJP;
$gname = $genreCodeMxJP;
//$gname = ['A' => 'アニメ', 'G' => 'ゲーム', 'I' => 'アイマス', 'P' => 'JPOP', 'R' => 'Rock', 'V' => 'ボカロ', 'o' => 'オリ曲',];

function putHtmlOrdrTable($order, $on_genre, $limit, $nomedia = '',  $having = '', $mode = '')
{
    global $gname;

    if ($mode == 'wikip') {

        print '<pre>';
    } elseif ($mode == 'csv') {
        print '<p>CSVの制限でデータ内の半角カンマは全角カンマに変換されています。（エクスポート先の仕様で\,などにエスケープできる場合はエディタなどで置換してください）<br>備考の日付はリリース日です。Setlinkのフォーマットにあわせていますが、読み込みを保証しません。</p>';
        print "<pre style=\"background-color:#d4e7ff; margin:30px; padding:10px\">\n";
        print e("曲名,フリガナ,アーティスト,ジャンル,タグ1,タグ2,タグ3,タグ4,タグ5,カラオケ音源のYoutubeURL,歌った回数,熟練度,備考") . "\n";
    } else {
?>





        <div class="table">
            <div class="table_head">
                <div class="table-grid">
                    <!--<th class="songid_h">ID</th>-->
                    <div class="cell">曲名</div>
                    <div class="cell">アーティスト/P</div>

                    <!--<th>ボカロP</th>-->
                    <div class="cell">リリース</div>
                    <div class="cell">TieUp </div>
                    <div class="cell">G</div>
                    <div class="cell">直近歌唱</div>
                    <div class="cell">回数</div>
                </div><!-- table-grid close -->
            </div><!-- table_head close -->

            <div class="tbody">
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

                $s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.yomi,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,tbsong.relsd,count(*),max(evdate) from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng=tbsong.arrng  join tbevent using(evwcode) where {$nomedia}  {$on_genre} group by tbvocal.songid,tbvocal.arrng  {$having} {$order} {$limit} ;");
                #  $s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.yomi,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,tbsong.relsd,count(*),max(evdate),CONCAT(tbsong.vocap,tbsong.artist),CONCAT(tbsong.artist,tbsong.vocap) from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng=tbsong.arrng  join tbevent using(evwcode) where {$nomedia}  {$on_genre} group by tbvocal.songid,tbvocal.arrng  {$having} {$order} {$limit} ;");

                while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                    $boldcss4helper = 'table-grid';
                    $relsd = $row['relsd']; //release date NULL treatment
                    $relsd = is_null($relsd) ? '' : $relsd;
                    $relsyear = $relsd == '' ? '' : substr($relsd, 0, 4);
                    //print_r ($row);
                    //Special format output for wikiwiki markup style
                    if ($mode == 'wikip') {
                        print "|" . e($row['sname']) . "|" . e($row['artist']) . "|" . e($row['tieup']) . "|" . e($row['vocap']) . "|" . e($row['count(*)']) . "|" . e($row['genre']) . "|\n";
                    } elseif ($mode == 'csv') {
                        //曲名,フリガナ,アーティスト,ジャンル,タグ1,タグ2,タグ3,タグ4,タグ5,カラオケ音源のYoutubeURL,歌った回数,熟練度,備考   *****Export for Setlink web service
                        print ece($row['sname']) . ',' . ece(str_replace('ウ゛', 'ヴ', mb_convert_kana($row['yomi'], "C"))) . ',' . ece($row['artist']) . ',' . ece($gname[$row['genre']]) . ',' . ece($row['tieup']) . ',' . ece($row['vocap']) . ',,,,,' . ece($row['count(*)']) . ",," . ece($relsd) . "\n";
                    } else {
                        if (intval($row['count(*)']) > 2) {
                            $boldcss4helper = 'table-grid strong4h';
                        }
                        $sid = $row['arrng'] ? e($row['songid']) . '-' . e($row['arrng']) : e($row['songid']);
                ?>
                        <div class="<?= $boldcss4helper ?>">

                            <!--<td class="songid"><?= $row['arrng'] ? e($row['songid']) . '-' . e($row['arrng']) : e($row['songid']) ?></td>-->


                            <div class="cell"><span title="<?= 'ID=' . $sid ?>"><a href="<?php
                                                                                            //
                                                                                            print 'shistory.php?sid=' . e($row['songid']);
                                                                                            ?>"><?= e($row['sname']) ?></a></span></div>



                            <div class="cell"><?= s($row['artist']) . (($row['artist'] and $row['vocap']) ? "/" : "") .  s($row['vocap']) ?></div>
                            <!--<td><?= e($row['vocap']) ?></td>-->
                            <div class="cell <?= $relsd ? relclass($relsd) : '' ?>"><span title="<?= $relsd ?>"><?= e($relsyear) ?></span></div>
                            <div class="cell"><?= e($row['tieup']) ?></div>


                            <div class="cell"><?= e($row['genre']) ?></div>
                            <div class="cell"><?= e($row['max(evdate)']) ?></div>
                            <div class="cell"><?= e($row['count(*)']) ?></div>

                        </div>

                <?php
                    }
                }
            } catch (PDOException $e) {
                die("Error:{$e->getMessage()}");
            }

            if ($mode == 'wikip' or $mode == 'csv') {
                print '</pre>';
            } else {
                ?>
            </div> <!-- tbody close -->
        </div><!-- table close -->
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



<?php
            }
        } //end of fuction
?>