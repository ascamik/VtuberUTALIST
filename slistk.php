<?php
//require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'OrdrSubN.php';
//   require_once 'Relsd2cssclass.php';

$icode2sortby = [
    'y' => 'order by tbsong.yomi COLLATE utf8mb4_unicode_ci',
    's' => 'order by tbsong.sname COLLATE utf8mb4_unicode_ci',
    'v' => 'order by tbsong.vocap,tbsong.yomi COLLATE utf8mb4_unicode_ci',
    'c' => 'order by count(*),songid',
    'd' => 'order by count(*) DESC,songid',
    'l' => 'order by max(evdate),songid',
    'm' => 'order by max(evdate) DESC,songid',
    'r' => 'order by tbsong.relsd DESC,songid',
    'a' => 'order by tbsong.artist,tbsong.yomi  COLLATE utf8mb4_unicode_ci'
];


//GET クエリ処理
$icode2regex = ['1' => '^[0-9a-zA-Z∀]', 'a' => '^[あいうえおをゔ]', 'k' => '^[かきくけこがぎぐげご]', 's' => '^[さしすせそざじずぜぞ]', 't' => '^[たちつてとだぢづでど]', 'n' => '^[なにぬねの]', 'h' => '^[はひふへほぱぴぷぺぽばびぶべぼ]', 'm' => '^[まみむめも]', 'y' => '^[やゆよらりるれろわん]'];
$icode2char = ['1' => '数字・英字', 'a' => 'あ（ゔ）', 'k' => 'か', 's' => 'さ', 't' => 'た', 'n' => 'な', 'h' => 'は', 'm' => 'ま', 'y' => 'や・ら・わ',];
//default setting for display page
//$dbsearch = 'tbsong.yomi REGEXP \'' . $icode2regex['1'] . '\'';
$dbsearch = 'tbsong.sname REGEXP \'' . $icode2regex['1'] . '\'';
$sortby = $icode2sortby['s'];
$pagemarker = $icode2char['1'];
if (isset($_GET['i'])) {
    if (isset($icode2regex[$_GET['i']])) {
        $sortby = $icode2sortby['y'];
        $dbsearch = 'tbsong.yomi REGEXP "' . $icode2regex[$_GET['i']] . '"';
        $pagemarker = $icode2char[$_GET['i']];
        if ($_GET['i'] == '1') {
            $dbsearch = 'tbsong.sname REGEXP "' . $icode2regex[$_GET['i']] . '"';
            $sortby = $icode2sortby['s'];
            //DBの英数字の読みをかなにした場合、英数のページにも表示させるための分岐
        }
    }
}
$title = '曲名一覧（歌った回数）';
$h2 = "曲名一覧表示　【{$pagemarker}】";
$aditionalcss = '<link rel="stylesheet" href="table-grid-resp.css?b2e5aa2f">';
putHtmlHeader($title, $h2, $aditionalcss);
//putHtmlHeader($title, $h2);
putHtmlNavibar();
?>
<div id="tableoutline">
    <?php
    $order = $sortby;
    $on_genre = $dbsearch; //'genre regexp"[AGPRVIo]"';
    $limit = '';
    //$limit = 'LIMIT 50';
    $nomedia = ''; //"evmedia<'10' and";
    $having = '';
    $mode = '';
    putHtmlOrdrTable($order, $on_genre, $limit, $nomedia,  $having, $mode);
    //

    ?>
    <div>リリース日の色分け <span class="new">現在から1年以内 </span><span class="rei"> 令和(2019.5〜) </span><span class="mil"> 平成中後期(2001〜2019.4) </span><span class="her"> 平成初期(1989〜2000) </span><span class="syo"> 1988年以前 </span></div>
</div><!-- tableoutline -->
<?php
putHtmlContainerClose();
