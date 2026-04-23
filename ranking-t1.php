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




$title = 'ランキング付属（β）';
$h2 = "ランキング表示の付属(β)";
$aditionalcss = '<link rel="stylesheet" href="table-grid-resp.css?b2e5aa2f">';
putHtmlHeader($title, $h2, $aditionalcss);
putHtmlNavibar();
?>
<div id="jsbutton">
    <div class="backbutton"><a href="javascript:history.back()"><img src="arrow_back.svg"><span class="jsbuttonspan">戻る</span></a></div>
</div>
<div id="tableoutline">
    <?php
    //
    //
    putHtmlH3("一度だけ歌われた曲（歌った日付が古い順）");
    ?>
    <?php
    $order = $icode2sortby['l'];
    $on_genre = 'genre regexp"[AGPRVIo]"';
    $limit = ''; //'LIMIT 50';
    $nomedia = "evmedia<'10' and";
    $having = 'having count(*)=1';
    $mode = '';
    putHtmlOrdrTable($order, $on_genre, $limit, $nomedia,  $having, $mode);

    ?>
</div><!-- tableoutline -->
<?php
putHtmlContainerClose();
