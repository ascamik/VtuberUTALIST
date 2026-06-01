<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'timestamplinker.php';
require_once 'dbAu.php';










$title = '検索（曲）';
$h2 = "検索（曲）";
putHtmlHeader($title, $h2);

//check login admin  

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    $user = $auth->getCurrentSessionUserInfo();
    putHtmlNavibar('admin');
    print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    // print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    // print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";

    // putHtmlContainerClose();
    // exit;
    putHtmlNavibar();
}
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
?>

<div id="formcontainer">
    <form method="POST" action="searchsong.php">
        <div class="formparts_2">
            <div class="fml"><label class="label">search title or yomi (max two words divide by space)</label></div><input name="keyword" id="keyword" type="text" size="25" maxlength="50" value="<?php print $keyword ? e($keyword)  : ""; ?>">
        </div>


        <div class="submitbutton">
            <input type="submit" value="検索／Search">
        </div>

    </form>
</div>
<div class="normalmessage">検索結果は最大50件まで表示されます</div>
<?php


//print_r($_POST);
//check the post keyword
if (isset($_POST['keyword'])) {
    if (preg_match("/(\w+)\s*(.*)/u", mb_substr($_POST['keyword'], 0, 50), $keywordlist)) {

        $keyword1 = '%' . $keywordlist[1] . '%';
        $keyword2 = '%' . $keywordlist[2] . '%';
        //connection to DB
        try {
            $db = getDb();
            //PREPAER
            // $s = $db->prepare(' select * from tbsong where sname COLLATE utf8mb4_unicode_ci like :keyword1 and sname COLLATE utf8mb4_unicode_ci like :keyword2 or yomi COLLATE utf8mb4_unicode_ci like :keyword1 and yomi COLLATE utf8mb4_unicode_ci like :keyword2 or vocap COLLATE utf8mb4_unicode_ci like :keyword1 and vocap COLLATE utf8mb4_unicode_ci like :keyword2 or artist COLLATE utf8mb4_unicode_ci like :keyword1 and artist COLLATE utf8mb4_unicode_ci like :keyword2 or tieup COLLATE utf8mb4_unicode_ci like :keyword1 and tieup COLLATE utf8mb4_unicode_ci like :keyword2 LIMIT 50');
            $s = $db->prepare(' select * from tbsong where sname COLLATE utf8mb4_unicode_ci like :keyword1 and sname COLLATE utf8mb4_unicode_ci like :keyword2 or yomi COLLATE utf8mb4_unicode_ci like :keyword1 and yomi COLLATE utf8mb4_unicode_ci like :keyword2 LIMIT 50');
            $s->bindValue(':keyword1', $keyword1);
            $s->bindValue(':keyword2', $keyword2);
            // $s->bindValue(':keyword3',$keyword1);
            // $s->bindValue(':keyword4',$keyword2);
            $s->execute();

?>
            <div id="tableoutline">
                <table>
                    <thead>
                        <tr>
                            <th class="songid_h">ID</th>
                            <th class="one_em">#</th>
                            <th class="s_name">曲名</th>
                            <th>アーティスト</th>
                            <th>Tie Up</th>
                            <th>ボカロP</th>
                            <th class="one_em">G</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php


                        while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                            //print_r ($row);

                        ?>
                            <tr>

                                <td class="songid"><?= e($row['songid']) ?></td>
                                <td class="one_em"><?= e($row['arrng']) ?></td>


                                <td class="s_name"><?= e($row['sname']) ?></td>



                                <td><?= e($row['artist']) ?></td>
                                <td><?= e($row['tieup']) ?></td>
                                <td><?= e($row['vocap']) ?></td>
                                <td class="one_em"><?= e($row['genre']) ?></td>


                            </tr>

            <?php

                        }
                    } catch (PDOException $e) {
                        die("Error:{$e->getMessage()}");
                    }
                }
            }


            ?>
                    </tbody>
                </table>
            </div>
            <?php
            putHtmlContainerClose();
