<?php
require_once 'htmlpkg.php';
require_once 'OrdrSubN.php';



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




$title = 'ランキング';
$h2 = "ランキング表示";
$aditionalcss = '<link rel="stylesheet" href="table-grid-resp.css?b2e5aa2f">';
putHtmlHeader($title, $h2, $aditionalcss);
//putHtmlHeader($title, $h2);
//putHtmlText($note);
putHtmlNavibar();
//putHtmlText('4回以上歌唱曲（オリ曲は3回以上）');
?>
<div id="jsbutton">
    <div class="backbutton"><a href="javascript:history.back()"><img src="arrow_back.svg"><span class="jsbuttonspan">戻る</span></a></div>
</div>
<div id="tableoutline">
    <?php
    //
    putHtmlH3("J-POPなど（回数上位）");
    $order = $icode2sortby['d'];
    $on_genre = 'genre regexp"[PR]"';
    $limit = 'LIMIT 35';
    $nomedia = '';
    $having = 'having count(*)>3';
    $mode = '';
    putHtmlOrdrTable($order, $on_genre, $limit, $nomedia,  $having, $mode);
    //
    putHtmlH3("アニソン・ゲームなど（回数上位）");
    $order = $icode2sortby['d'];
    $on_genre = 'genre regexp"[AGI]"';
    $limit = 'LIMIT 35';
    $nomedia = '';
    $having = 'having count(*)>3';
    $mode = '';
    putHtmlOrdrTable($order, $on_genre, $limit, $nomedia,  $having, $mode);
    //
    putHtmlH3("ボカロ曲（回数上位）");
    $order = $icode2sortby['d'];
    $on_genre = 'genre regexp"[V]"';
    $limit = 'LIMIT 35';
    $nomedia = '';
    $having = 'having count(*)>3';
    $mode = '';
    putHtmlOrdrTable($order, $on_genre, $limit, $nomedia,  $having, $mode);
    //
    putHtmlH3("オリ曲（この項目のみ3回以上）");
    $order = $icode2sortby['d'];
    $on_genre = 'genre regexp"[o]"';
    $limit = 'LIMIT 35';
    $nomedia = '';
    $having = 'having count(*)>2';
    $mode = '';
    putHtmlOrdrTable($order, $on_genre, $limit, $nomedia,  $having, $mode);

    ?>
</div><!-- tableoutline -->
<?php
putHtmlContainerClose();
