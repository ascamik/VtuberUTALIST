<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'chckdate.php';
require_once 'Code2text.php';
require_once 'dbAu.php';
require_once 'IedModule.php';

//mode matrix (0,1,2)=>
$modemx = ['I', 'E', 'D'];

$title = '統合編集〈管理〉';
$h2 = "〈管理〉統合編集";
$css = '<link rel="stylesheet" href="integratededitor.css?b2e5aa58"><link rel="stylesheet" href="table-grid-resp-integed.css?b2e5aa58">';
putHtmlHeader($title, $h2, $css);

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    $user = $auth->getCurrentSessionUserInfo();
    putHtmlNavibar('admin');
    print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
    // $admin = 'admin'; //Not used
} else {
    putHtmlNavibar();
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";
    // $admin = '';

    putHtmlContainerClose();
    exit;
    // ここで終了
}
//check the tbvodraft, empty is initial, draft mode if drafttype is "D", modify mode if drafttype is "E"
//


$setlistModeDesc['D'] = '［新規モード］このセットリストは下書きです.各行をクリックするとタイムやメモを編集できます<br>編集が完了したら、下のボタンでセットリストが直ちに反映されます<br>［リセット］ボタンで作業中の下書きを破棄します';

$setlistModeDesc['E'] = '［修正モード］このセットリストは編集中の下書きです.各行をクリックするとタイムやメモを編集できます。左欄の▷ボタンで曲の追加ができます<br>編集が完了したら、下のボタンでセットリストの修正が直ちに反映されます。ボタンを押すまでは反映されません．画面を閉じても保存されます．［リセット］ボタンで下書きを破棄します';

$setlistModeDesc['I'] = 'セットリストの新規作成をする場合は、このまま曲のリストの▷でセットリスト作成を開始しましょう。既存のセットリストの修正／曲の追加はイベントを選んで下のボタンをクリックしてください。';

?>
<!-- 切り替えボタン -->
<div class="pane-tabs">
    <button class="tab-btn active" data-pane="leftpane">曲のリスト</button>
    <button class="tab-btn" data-pane="centerpane">セットリスト</button>
    <button class="tab-btn" data-pane="rightpane">イベント（配信）リスト</button>
</div>
<?php
print '<div class="editorcontainer">';
print '<div class="leftpane active">';
print '<div class="integnavi_group">
<div class="integnavi_link"><a href="#newSongBtn">↑</a></div>
<div class="integnavi_link"><a href="#song_1">数字／英字</a></div>
<div class="integnavi_link"><a href="#song_a">あ（ゔ）</a></div>
<div class="integnavi_link"><a href="#song_k">か</a></div>
<div class="integnavi_link"><a href="#song_s">さ</a></div>
<div class="integnavi_link"><a href="#song_t">た</a></div>
<div class="integnavi_link"><a href="#song_n">な</a></div>
<div class="integnavi_link"><a href="#song_h">は</a></div>
<div class="integnavi_link"><a href="#song_m">ま</a></div>
<div class="integnavi_link"><a href="#song_y">や・ら・わ</a></div>
</div>
<div class="songlistcontainer">';
print ' <div class="stickynavi"><button type="button" id="newSongBtn">新曲登録</button><div class="guidance">曲のリストから▷でセットリスト末尾へ登録します。この表にない曲はまず新曲登録します. 曲の情報を修正する場合はその行をクリックします</div></div>';
//putHtmlSongList();
putHtmlQuickPickerSongList();
print '</div></div>'; //leftpane close

$chk = chkDraftmode();
$mdflag = $modemx[$chk]; //E or D or I
print "<div class=\"centerpane\" id=\"editorpanel\" data-mode=\"{$mdflag}\">";
print " <div class=\"stickynavi\"><button type=\"button\" id=\"purgeBtn\">リセット</button><div class=\"guidance\">{$setlistModeDesc[$mdflag]}</div></div>";

putHtmlDrafttable($mdflag);
if ($chk == 0) {
    putHtmlSetlistAgent('I');
} elseif ($chk == 1) {
    putHtmlSetlistAgent('E');
    $eraseSLDesc = "現在編集対象となっているイベントのセットリストを一括消去します．クリックすると確認画面に進みます";
    print " <div class=\"navi\"><button type=\"button\" id=\"eraseSLBtn\">⚠️一括消去</button><div class=\"guidance\">{$eraseSLDesc}</div></div>";
} elseif ($chk == 2) {
    putHtmlSetlistAgent('D');
} else {
    print 'error';
}
print '</div>'; //centerpane close


print '<div class="rightpane">';
print ' <div class="stickynavi"><div class="guidance_r"><button id="newEventBtn">新規イベント追加</button>イベントの削除・内容を修正する場合は修正したい行をクリックします</div></div>';

putHtmlEventList();
print '</div>'; //rightpane close

print '</div>'; //editorcontainer close
?>
<div id="newSongModal" class="modal-overlay" style="display:none;">
    <div class="modal-dialog">

        <h2>新曲登録</h2>

        <div class="form-row">
            <label for="fieldNS1">SongID（通常空欄／枝番にしたいリミックス曲のみ元のIDを入力）</label>
            <input type="text" id="fieldNS1">
        </div>

        <div class="form-row">
            <label for="fieldNS2">枝番（リミックス曲等）</label>
            <input type="text" id="fieldNS2" disabled>
        </div>

        <div class="form-row">
            <label for="fieldNS3">曲名*</label>
            <input type="text" id="fieldNS3" required>
        </div>

        <div class="form-row">
            <label for="fieldNS4">よみ*（ひらがな）</label>
            <input type="text" id="fieldNS4" required>
        </div>

        <div class="form-row">
            <label for="fieldNS5">アーティスト</label>
            <input type="text" id="fieldNS5">
        </div>

        <div class="form-row">
            <label for="fieldNS6">タイアップアニメ/ゲーム(op,ed,ins,th,cs...)</label>
            <input type="text" id="fieldNS6">
        </div>

        <div class="form-row">
            <label for="fieldNS7">ボカロP名</label>
            <input type="text" id="fieldNS7">
        </div>

        <div class="form-row">
            <label for="fieldNS8">ジャンル</label>
            <select id="fieldNS8" name="genre">
                <option value="P">P:J-POP</option>
                <option value="A">A:Animation song</option>
                <option value="V">V:Vocalo song</option>
                <option value="G">G:Game song</option>
                <option value="o">o:Original song</option>
                <option value="I">I:for The Idolmaster </option>
                <option value="R">R:Rock</option>

                <option></option>
            </select>
        </div>

        <div class="form-row">
            <label for="fieldNS9">リリース日(yyyy-MM-dd or yyyyMMdd)</label>
            <input type="text" id="fieldNS9">
        </div>


        <div class="modal-buttons">
            <button type="button" id="addNSBtn">登録</button>
            <button type="button" id="closeNSBtn">キャンセル</button>
        </div>

    </div>
</div>
<div id="modalOverlay" class="modal-overlay" style="display:none;">
    <div class="modal-dialog">

        <h2 id="editing">編集</h2>

        <div class="form-row">
            <label for="field1">SongID</label>
            <input type="text" id="field1">
        </div>

        <div class="form-row">
            <label for="field2">アレンジ曲枝番</label>
            <input type="text" id="field2">
        </div>

        <div class="form-row">
            <label for="field3">タイムスタンプ</label>
            <input type="text" id="field3">
        </div>

        <div class="form-row">
            <label for="field4">メモ</label>
            <input type="text" id="field4">
        </div>

        <div class="modal-buttons">
            <button type="button" id="saveBtn">保存</button>
            <button type="button" id="closeBtn">キャンセル</button>
        </div>

    </div>
</div>
<div id="eraseSLmodal" class="modal-overlay" style="display:none;">
    <div class="modal-dialog">

        <h2 id="editing">セットリスト一括消去</h2>
        <div class="modal-desc">このイベントのセットリストを一括消去します.
            この操作で消去されるデータは元のセットリストのデータです.今表示されている下書きの状態に関係なく、対象のイベントのセットリストがまるごと消去されます．現在表示されている編集中の下書きも消去されます
            <br>
            それでもよければ、確認欄に上と同じイベントコードを入力してください
        </div>

        <div class="form-row">
            <label for="fieldevc">対象のイベントコード</label>
            <input type="text" id="fieldevc" readonly>
        </div>

        <div class="form-row">
            <label for="fieldevc2">対象のイベントコート（確認）*</label>
            <input type="text" id="fieldevc2">
        </div>


        <div class="modal-buttons">
            <button type="button" id="runEraseSLBtn">⚠️消去</button>
            <button type="button" id="closeEraseSLBtn">キャンセル</button>
        </div>

    </div>
</div>
<dialog id="eventDialog">

    <form id="eventForm">

        <label for="evwcode" id="label-evwcode">コード</label>
        <input type="text" id="evwcode" readonly>

        <label for="evdate">日付</label>
        <input type="date" id="evdate">

        <label for="evtitle">タイトル</label>
        <input type="text" id="evtitle">

        <label for="evurl">URL</label>
        <input type="text" id="evurl">

        <label for="evmedia">媒体</label>
        <!--<input type="number" id="evmedia"> --> <select id="evmedia" name="evmedia">
            <?php
            foreach ($evmediaCodeMx as $codenum => $codecaption) {

                print "<option value=\"$codenum\">$codenum:$codecaption</option>";
            }
            ?></select>

        <label for="evtype">種別</label>
        <!--<input type="number" id="evtype">--><select id="evtype" name="evtype">
            <?php
            foreach ($evtypeCodeMx as $codenum => $codecaption) {

                print "<option value=\"$codenum\">$codenum:$codecaption</option>";
            }
            ?></select>

        <label for="evdesc">説明</label>
        <textarea id="evdesc"></textarea>

        <div class="dialog-buttons">
            <button type="button" id="saveEvBtn">保存</button>
            <button type="button" id="deleteEvBtn">削除</button>
            <button type="button" id="closeEvBtn">閉じる</button>
        </div>

    </form>
    <!-- 曲の修正用-->
</dialog>
<dialog id="songDialog">

    <form id="songForm">

        <label for="songid" id="label-songid">SongID</label>
        <input type="text" id="songid" readonly>

        <label for="arrng" id="label-arrng">枝番（リミックス曲など）</label>
        <input type="text" id="arrng" readonly>

        <label for="sname">曲名</label>
        <input type="text" id="sname">

        <label for="yomi">よみ*（ひらがな）</label>
        <input type="text" id="yomi">

        <label for="artist">アーティスト</label>
        <input type="text" id="artist">

        <label for="tieup">タイアップアニメ/ゲーム(op,ed,ins,th,cs...)</label>
        <input type="text" id="tieup">

        <label for="vocap">ボカロP名</label>
        <input type="text" id="vocap">


        <label for="genre">ジャンル</label>

        <select id="genre" name="genre">
            <option value="P">P:J-POP</option>
            <option value="A">A:Animation song</option>
            <option value="V">V:Vocalo song</option>
            <option value="G">G:Game song</option>
            <option value="o">o:Original song</option>
            <option value="I">I:for The Idolmaster </option>
            <option value="R">R:Rock</option>

        </select>



        <label for="relsd">リリース日(yyyy-MM-dd or yyyyMMdd)</label>
        <input type="text" id="relsd">



        <div class="dialog-buttons">
            <button type="button" id="saveSongBtn">保存</button>
            <button type="button" id="deleteSongBtn">削除</button>
            <button type="button" id="closeSongBtn">閉じる</button>
        </div>

    </form>

</dialog>
<?php
$jscript = <<<'EOD'
 <script src=iedslsupport.js></script>
 <script>



/*
  *****************     a new Song Data insert    ********************
*/
const modalNS = document.getElementById('newSongModal');

const fieldNS1 = document.getElementById('fieldNS1');
const fieldNS2 = document.getElementById('fieldNS2');
const fieldNS3 = document.getElementById('fieldNS3');
const fieldNS4 = document.getElementById('fieldNS4');
const fieldNS5 = document.getElementById('fieldNS5');
const fieldNS6 = document.getElementById('fieldNS6');
const fieldNS7 = document.getElementById('fieldNS7');
const fieldNS8 = document.getElementById('fieldNS8');
const fieldNS9 = document.getElementById('fieldNS9');



/*
 * 新規登録クリック
 */


   document.getElementById('newSongBtn').addEventListener('click', () => {



        modalNS.style.display = 'flex';
    });



/*
 * 閉じる
 */
document.getElementById('closeNSBtn').addEventListener('click', () => {
    modalNS.style.display = 'none';
});

/*
 * 保存
 */
document.getElementById('addNSBtn').addEventListener('click', async () => {

    const formData = new FormData();


    formData.append('orgsongid', fieldNS1.value);
    formData.append('arrng', fieldNS2.value);
    formData.append('sname', fieldNS3.value);
    formData.append('yomi', fieldNS4.value);
    formData.append('artist', fieldNS5.value);
    formData.append('tieup', fieldNS6.value);
    formData.append('vocap', fieldNS7.value);
    formData.append('genre', fieldNS8.value);
    formData.append('relsd', fieldNS9.value);
    try {

        const response = await fetch('iedprcsNewsongIns.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('HTTP Error');
        }

        const result = await response.json();

        if (result.success) {

            alert(result.message || 'Done');
            modalNS.style.display = 'none';
            location.reload();
        } else {

            alert(result.message || '登録失敗');

        }

    } catch (err) {

        console.error(err);
        alert('通信エラー');

    }

});

//下書きセットリスト消去
// ボタン要素を取得
const purgeBtn = document.getElementById('purgeBtn');

// クリックイベントを設定
purgeBtn.addEventListener('click', () => {
  // 確認ダイアログを表示
  const result = confirm('本当に下書きを消去しますか？');

  // OKが押された場合のみ遷移
  if (result) {
    window.location.href = 'iedpurge.php';
  }
});

//
//セットリスト一括消去
//
const fieldevc = document.getElementById('fieldevc');
const fieldevc2 = document.getElementById('fieldevc2');

/*
 * 一括消去クリック
 */

    const eraseSLmodal = document.getElementById('eraseSLmodal');



//eraseSLBtnは無いときもありますのでオプショナルチェーンでつないでます
   document.getElementById('eraseSLBtn')?.addEventListener('click', () => {

   fieldevc.value=ecodeElement.dataset.ec;

        eraseSLmodal.style.display = 'flex';
    });
/*
 * 閉じる
 */

   document.getElementById('closeEraseSLBtn').addEventListener('click', () => {
    eraseSLmodal.style.display = 'none';
});

/*
 * 消去クリック（消去実行:画面遷移）
 */
document.getElementById('runEraseSLBtn').addEventListener('click', async () => {


// イコール（一致）なら処理、異なるならアラートを表示
if (fieldevc2.value === ecodeElement.dataset.ec) {

    if (!confirm('消去を実行しますか？')) {
         return;
     }

    const formData = new FormData();


    formData.append('evwcode', fieldevc2.value);
      try {

        const response = await fetch('iedprcseraseSL.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('HTTP Error');
        }

        const result = await response.json();

        if (result.success) {

            alert(result.message || 'Done');
            location.reload();

        } else {

            alert(result.message || '更新失敗');

        }

    } catch (err) {

        console.error(err);
        alert('通信エラー');

    }




} else {
  // 異なっていた場合のアラート表示
  alert("イベントコードが一致しません。確認してください。");
}


});
/* タブ切り替え*/
document.addEventListener('DOMContentLoaded', () => {

    const buttons = document.querySelectorAll('.tab-btn');

    function showPane(className) {

        document
            .querySelectorAll('.leftpane, .centerpane, .rightpane')
            .forEach(pane => {
                pane.classList.remove('active');
            });

        document
            .querySelector('.' + className)
            .classList.add('active');

        buttons.forEach(btn => {
            btn.classList.remove('active');
        });

        document
            .querySelector(`[data-pane="${className}"]`)
            .classList.add('active');
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            showPane(btn.dataset.pane);
        });
    });

    // 初期表示
    showPane('leftpane');

});



        </script>
EOD;
putHtmlContainerClose($jscript);
