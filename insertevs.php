<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'Code2text.php';

require_once 'dbAu.php';



$title = '管理（イベント追加）';
$h2 = "管理（イベント追加）";
putHtmlHeader($title, $h2);
//check login admin  

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    $user = $auth->getCurrentSessionUserInfo();
    putHtmlNavibar('admin');
    print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    putHtmlNavibar();
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";

    putHtmlContainerClose();
    exit;
    // ここで終了
}

























// putHtmlNavibar();
//print_r($_POST);

if (isset($_POST['evtitle']) and isset($_POST['evdate'])) {
    $postevwcode = $_POST['evwcode'];

    if ((preg_match('/[a-zA-Z0-9]{0,1}\d{0,3}/', $postevwcode))) { // check the recived evwcode is no duplicate evwcode used already.
        //イベントコードは非公式Wikiのコードと統一しているためF2、C1のようなフォーマットが存在するが、整数値に統一して特別な意味は排除した運用をしたい
        //現状では許容しているが、まったくおすすめできない
        //connection to DB
        //Get list of Event code from DB
        try {
            $db = getDb();
            //SELECT event code
            $s = $db->query("select evwcode from tbevent;");

            $evwcode = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
            // print_r($evwcode);
            if (in_array($postevwcode, $evwcode)) {
                print '<div>入力したコードが既に使われています<br>コードを自動で割り当てました</div>';
                $postevwcode = '';
            }
        } catch (PDOException $e) {
            die("Error:{$e->getMessage()}");
        }
        // print 'evwcode';
        // print $_POST['evwcode'];




    } else {
        $postevwcode = '';
    } //フォーマットに一致しない場合消す
    if ($postevwcode == '') { //create new evwcode (max +1)  
        //print'no evwecode';
        //connection to DB
        //Get max Event code from DB
        try {
            $db = getDb();
            //SELECT event code, pick up MAX interger code :exclude alphabet-string+number type code
            $s = $db->query("select max(CAST(evwcode AS SIGNED)) from tbevent where  evwcode  regexp \"^[0-9]+\"; ");

            $max_evwcode = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
            // print_r($evwcode);
            $maxinteger_evwcode = intval($max_evwcode[0]);
            $postevwcode = strval($maxinteger_evwcode + 1); //overwrite posted data
        } catch (PDOException $e) {
            die("Error:{$e->getMessage()}");
        }
    }
    print "<div>created a new event of evwcode[{$postevwcode}]</div>";
    //print $postevwcode;

    //print_r($_POST);

    if (preg_match('/^(20\d{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $_POST['evdate']) and intval($_POST['media']) < 11 and intval($_POST['media']) > 0 and intval($_POST['type']) < 5 and intval($_POST['type']) > 0) {
        //print'data ok';
        //insert new record in DB
        $filterurl = filter_var($_POST['evurl'], FILTER_VALIDATE_URL);
        if ($filterurl != $_POST['evurl']) {
            print '<div>URL is filterd!</div>';
        }

        try {
            $db = getDb();
            //prepare
            $s = $db->prepare('INSERT INTO tbevent(evwcode, evdate, evtitle, evurl, evmedia, evtype, evdesc) VALUES(:evwcode, :evdate, :evtitle, :evurl, :evmedia, :evtype, :evdesc )');
            $s->bindValue(':evwcode', $postevwcode);
            $s->bindValue(':evdate', $_POST['evdate']);
            $s->bindValue(':evtitle', $_POST['evtitle']);
            $s->bindValue(':evurl', $filterurl);
            $s->bindValue(':evmedia', $_POST['media']);
            $s->bindValue(':evtype', $_POST['type']);
            $s->bindValue(':evdesc', $_POST['desc']);

            $s->execute();
            print '<div>データを正常に追加しました</div>';

?>
            <div id="toptableoutline">
                <table>
                    <thead>
                        <tr class="info">
                            <th colspan="3">タイトル</th>
                        </tr>
                        <tr class="info">
                            <th>日付</th>
                            <th>媒体</th>
                            <th>配信者</th>
                        </tr>
                        <tr class="info">
                            <th colspan="3">URL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="info">
                            <td colspan="3"><?= e($_POST['evtitle']) ?></td>
                        </tr>
                        <tr class="info">
                            <td><?= e($_POST['evdate']) ?></td>
                            <td><?= e($_POST['media']) ?></td>
                            <td><?= e($_POST['type']) ?></td>
                        </tr>
                        <tr class="info">
                            <td colspan="3"><?= e($_POST['evurl']) ?></td>
                        </tr>
                        <?php
                        // then desc not equal void string
                        $desc = $_POST['desc'];
                        if ($desc) {
                            $desc = e($desc);
                            print "<tr><td colspan=\"3\">[備考] {$desc}</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>


<?php
        } catch (PDOException $e) {
            die("Error:{$e->getMessage()}");
        }
    } else {
        print '<div>recived data contain illegal DATA!. DataBase is not updated.</div>';
    }
} //start from this section when page is loading first (POSTではない処理ここへ直接ジャンプ

?>
<div id="formcontainer">
    <form method="POST" action="insertevs.php">
        <div class="formparts_2">
            <div class="fml"><label class="label">title</label></div><input name="evtitle" id="evtitle" type="text" size="90" maxlength="200" required>
        </div>
        <div class="formparts_2">
            <div class="fml"><label class="label">URL</label></div><input name="evurl" id="evurl" type="url" size="90" maxlength="200">
        </div>

        <div class="formparts_2">
            <div class="fml"><label class="label">Event date</label></div><input name="evdate" id="evdate" type="date" size="12" required>
        </div>

        <div class="formparts_2">
            <div class="fml"> <label class="label">media</label></div>
            <select id="media" name="media">
                <?php
                foreach ($evmediaCodeMx as $codenum => $codecaption) {

                    print "<option value=\"$codenum\">$codenum:$codecaption</option>";
                }
                ?>
                <!--               <option value="1">1:youtube live</option>
                <option value="5">5:twicas live</option>
                <option value="10">10:youtube video</option>-->

                <option></option>
            </select>
        </div>

        <div class="formparts_2">
            <div class="fml"> <label class="label">site owner</label></div>
            <select id="type" name="type">
                <?php
                foreach ($evtypeCodeMx as $codenum => $codecaption) {

                    print "<option value=\"$codenum\">$codenum:$codecaption</option>";
                }
                ?>

            </select>
        </div>

        <div class="formparts_2">
            <div class="fml"><label class="label">code(blank recommended)</label></div><input name="evwcode" id="evwcode" type="text" size="10" maxlength="4">
            <span>※コードは空欄のままで、自動で割り当てます</span>
        </div>
        <div class="formparts_2">
            <div class="fml"><label class="label">説明
                </label></div>
            <textarea id="desc" name="desc" rows="7" cols="100"></textarea>
        </div>
        <div>　※説明欄は通常歌配信ライブ以外のときに解るように入力願います（改行も入力できます）<br>
            ※YouTubeのURLは埋め込みプレーヤ用VideoID抜き出しに利用されますので、対応している形式にする必要があります</div>
        <div id="submitbutton">
            <input type="submit" value="送信／Submit">
        </div>

    </form>
</div>
<?php
putHtmlContainerClose();
