<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'timestamplinker.php';


// // Import the necessary classes
// use Cartalyst\Sentinel\Native\Facades\Sentinel;
// use Illuminate\Database\Capsule\Manager as Capsule;

// // Include the composer autoload file
// require 'vendor/autoload.php';
// require_once 'sen_cnfg0001.php';
// // Setup a new Eloquent Capsule instance








$title = '検索（曲）';
$h2 = "検索（曲）(experimental)";
$aditionalcss = '<link rel="stylesheet" href="table-grid-resp-search.css?b2e5aa31">';
putHtmlHeader($title, $h2, $aditionalcss);
//putHtmlHeader($title, $h2);

//check login admin  

// if ($user = Sentinel::check()) {
//     // ログインしているアカウントをチェック
//     putHtmlNavibar('admin');
//     print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";

// }else {
//     // print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
//     // print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";

// putHtmlContainerClose();
// exit;
putHtmlNavibar();
//}
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
$target = isset($_POST['target']) ? $_POST['target'] : 'songtitle';

putHtmltext('※試験実装中です  検索に問題がある場合（あるはずが表示されない、間違った結果が表示される等）はお知らせいただければありがたいです');

?>

<div id="formcontainer">
    <form method="POST" action="searchsong4u.php">
        <div class=formparts_2>
            <div class="fml">
                <label class="label">検索する項目</label>
                <input type="radio" name="target" value="songtitle" <?= $target == 'songtitle' ? 'checked' : '' ?>><label>曲名・よみ</label>
                <input type="radio" name="target" value="artistandp" <?= $target == 'artistandp' ? 'checked' : '' ?>><label>アーティスト・ボカロP</label>
                <input type="radio" name="target" value="anime" <?= $target == 'anime' ? 'checked' : '' ?>><label>タイアップアニメ作品</label>
            </div>
        </div>
        <div class="formparts_2">
            <div class="fml"><label class="label">検索キーワード</label></div><input name="keyword" id="keyword" type="text" maxlength="100" value="<?php print $keyword ? e($keyword)  : ""; ?>">
        </div>
        <div>(半角スペースで区切って2語まで入力可能です (曲名よみはAND検索 他はOR検索))</div>


        <div class="submitbutton">
            <input type="submit" value="検索／Search">
        </div>

    </form>
</div>
<div class="normalmessage">検索結果は最大100件まで表示されます</div>
<?php


//print_r($_POST);
//check the post keyword
if (isset($_POST['keyword'])) {
    if (preg_match("/(\S+)\s*(.*)/u", mb_substr($_POST['keyword'], 0, 50), $keywordlist)) {

        $keyword1escaped = str_replace(array('\\', '%', '_'),    array('\\\\', '\%', '\_'), $keywordlist[1]);
        $keyword2escaped = str_replace(array('\\', '%', '_'),    array('\\\\', '\%', '\_'), $keywordlist[2]);

        $keyword1 = '%' . $keyword1escaped . '%';
        if ($keywordlist[2] == "") {
            $keyword2 = $keyword1;
        } else {
            $keyword2 = '%' . $keyword2escaped . '%';
        }
        //connection to DB
        try {
            $db = getDb();
            //PREPAER

            switch ($target) {
                case 'artistandp':
                    $s = $db->prepare(' select *,count(*),max(evdate)  from tbsong join tbvocal on tbvocal.songid=tbsong.songid and tbvocal.arrng=tbsong.arrng join tbevent using(evwcode) where artist COLLATE utf8mb4_unicode_ci like :keyword1 or artist COLLATE utf8mb4_unicode_ci like :keyword2 or vocap COLLATE utf8mb4_unicode_ci like :keyword3 or vocap COLLATE utf8mb4_unicode_ci like :keyword4  group by tbvocal.songid,tbvocal.arrng LIMIT 100');
                    break;

                case 'anime':
                    $s = $db->prepare(' select *,count(*),max(evdate) from tbsong join tbvocal on tbvocal.songid=tbsong.songid and tbvocal.arrng=tbsong.arrng join tbevent using(evwcode)  where tieup COLLATE utf8mb4_unicode_ci like :keyword1 or tieup COLLATE utf8mb4_unicode_ci like :keyword2 group by tbvocal.songid,tbvocal.arrng LIMIT 100');


                    break;


                default:
                    $s = $db->prepare(' select *,count(*),max(evdate) from tbsong join tbvocal on tbvocal.songid=tbsong.songid and tbvocal.arrng=tbsong.arrng join tbevent using(evwcode)  where sname COLLATE utf8mb4_unicode_ci like :keyword1 and sname COLLATE utf8mb4_unicode_ci like :keyword2 or yomi COLLATE utf8mb4_unicode_ci like :keyword3 and yomi COLLATE utf8mb4_unicode_ci like :keyword4 group by tbvocal.songid,tbvocal.arrng LIMIT 100');
            }


            $s->bindValue(':keyword1', $keyword1);
            $s->bindValue(':keyword2', $keyword2);
            if ($target != 'anime') {
                $s->bindValue(':keyword3', $keyword1);
                $s->bindValue(':keyword4', $keyword2);
            }
            $s->execute();

            //           print("<p>$keyword1 , $keyword2</p>")
?>
            <div id="jsbutton">
                <div class="backbutton"><a href="javascript:history.back()"><img src="arrow_back.svg"><span class="jsbuttonspan">戻る</span></a></div>
            </div>
            <div id="tableoutline">
                <div class="table">
                    <div class="table_head">
                        <div class="table-grid">
                            <div class="cell">ID</div>
                            <div class="cell">曲名</div>
                            <div class="cell">アーティスト</div>
                            <div class="cell">Tie Up</div>
                            <div class="cell">ボカロP</div>
                            <div class="cell">G</div>
                            <div class="cell">直近</div>
                            <div class="cell">回数</div>
                        </div>
                    </div>
                    <div class="tbody">
                        <?php


                        while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                            //print_r ($row);

                        ?>
                            <div class="table-grid">

                                <div class="cell"><?= $row['arrng'] ? e($row['songid']) . '-' . e($row['arrng']) : e($row['songid']) ?></div>
                                <!-- <td class="one_em"><?= e($row['arrng']) ?></td> -->


                                <div class="cell"><a href="shistory.php?sid=<?= e($row['songid']) ?>">
                                        <?= e($row['sname']) ?></a></div>



                                <div class="cell"><?= e($row['artist']) ?></div>
                                <div class="cell"><?= e($row['tieup']) ?></div>
                                <div class="cell"><?= e($row['vocap']) ?></div>
                                <div class="cell" class="one_em"><?= e($row['genre']) ?></div>
                                <div class="cell"><?= e($row['max(evdate)']) ?></div>
                                <div class="cell"><?= e($row['count(*)']) ?></div>

                            </div>

            <?php

                        }
                    } catch (PDOException $e) {
                        die("Error:{$e->getMessage()}");
                    }
                }
            }


            ?>
                    </div><!-- tbody close -->
                </div><!-- table close -->
            </div>
            <?php
            putHtmlContainerClose();
