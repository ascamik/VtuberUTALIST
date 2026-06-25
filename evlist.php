<?php
//このスクリプトではcookieを使用しています。アップロード時に
//  document.cookie = `${name}=${value}; path=/; max-age=${maxAge}`;
//の行のpath属性に、このファイルが置かれているあなたのサーバのサブディレクトリを設定してください
//また、レンタルサーバのドメインを使う共有サーバ等ではDomain属性にサブドメインを含むドメイン属性を設定してください
//例）  document.cookie = `${name}=${value}; path=/songlist; Domain=hoge.server.ne.jp; max-age=${maxAge}`;
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
//
$rows = isset($_COOKIE['rows']) ? (int)$_COOKIE['rows'] : 200;
$order = isset($_COOKIE['order']) ? $_COOKIE['order'] : 'desc';
// 
$pageSize = $rows; //event counts of one page

try {
    $db = getDb();
    //SELECT count of event 
    $s = $db->query("select count(*) from tbevent;");

    $eventCount = $s->fetch(PDO::FETCH_COLUMN);   //integer  
    // print_r($evwcode);
} catch (PDOException $e) {
    die("Error:{$e->getMessage()}");
}
//print_r($eventCount);
//print(gettype($eventCount));
if ($pageSize < $eventCount) {
    //multi  page mode
    $pageMax = ceil($eventCount / $pageSize); //小数点切り上げ
    $currentPage = 1;
    if (isset($_GET['p'])) {
        $currentPage = intval($_GET['p']) <= $pageMax ? intval($_GET['p']) : $pageMax;
        if ($currentPage < 1) {
            $currentPage = 1;
        }
    }
    $offset = ($currentPage - 1) * $pageSize;
    $pagenation = "LIMIT {$pageSize} OFFSET {$offset}";
    //
    //make page navigation html
    $pageNavi = " | ";
    for ($p = 1; $p <= $pageMax; $p++) {
        if ($p == $currentPage) {
            $pageNavi .= "【{$p}】";
        } else {
            $pageNavi .= "<a href=evlist.php?p={$p}> ［{$p}］ </a>"; //ファイル名を変えた場合このリンクを変えてください
        }
    }
    $pageNavi .= ' ';
    $pageTitle = "({$currentPage}/{$pageMax})";
} else {
    $pagenation = "";
    //single page mode
    $pageNavi = "";
    $pageTitle = "";
}

$title = '歌配信一覧';
$h2 = "歌配信一覧" . $pageTitle;
$aditionalcss = '<link rel="stylesheet" href="eventlistresp.css?b2e5aacf">';
putHtmlHeader($title, $h2, $aditionalcss);
putHtmlNavibar();
?>

<div id="tableoutline">
    <div class="page_navi">

        <a href="javascript:void(0);" onclick="OnLinkClick();">⇅並び替え　</a>
        <?= $pageNavi . '<button id="openModal">ページ設定</button>' ?>
    </div>
    <table id="event_list">
        <thead>
            <tr>
                <th class="two_em">C</th>
                <th class="date_w">日付</th>
                <th>配信タイトル</th>
                <th>M</th>
                <th>主</th>
            </tr>
        </thead>
        <tbody>
            <?php


            //connection to DB
            if ($order == "asc") {
                $orderSQL = "ASC";
            } else {
                $orderSQL = "DESC";
            }
            try {
                $db = getDb();
                //SELECT
                $s = $db->query("select * from tbevent order by evdate $orderSQL $pagenation;");

                while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                    //print_r ($row);

            ?>
                    <tr>

                        <td class="two_em"><?= e($row['evwcode']) ?></td>
                        <td><?= e($row['evdate']) ?></td>
                        <td><a href="<?php
                                        //print e($row['evurl']);
                                        print 'setlist.php?ev=' . e($row['evwcode']);
                                        ?>"><?= $row['evtype'] == 4 ? '<img src="handshake_28dp_8C1AF6_FILL0_wght400_GRAD0_opsz24.svg" class="evtype_icon">' : '' ?>
                                <?= e($row['evtitle']) ?></a></td>




                        <td class="two_em"><?= e($row['evmedia']) ?></td>
                        <td class="one_em"><?= e($row['evtype']) ?></td>

                    </tr>

            <?php

                }
            } catch (PDOException $e) {
                die("Error:{$e->getMessage()}");
            }
            ?>
        </tbody>
    </table>
    <img src="handshake_28dp_8C1AF6_FILL0_wght400_GRAD0_opsz24.svg" class="evtype_icon">：リレー
</div>
<!-- 設定窓 -->
<div id="settingsModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>ページ設定</h2>
        <label>1ページの行数(10〜200):
            <input type="number" id="rows" min="10" max="200" value="<?= htmlspecialchars($rows) ?>">
        </label>
        <label><input type="radio" name="order" value="asc" <?= $order === 'asc' ? 'checked' : '' ?>> 昇順（古いものが上）</label>
        <label><input type="radio" name="order" value="desc" <?= $order === 'desc' ? 'checked' : '' ?>> 降順（新しいものが上）</label>
        ※設定はブラウザに保存されます<br>
        <button onclick="saveSettings()">保存</button>
    </div>
</div>

<?php
// 
$script = <<<'EOD'
<script type="text/javascript">
function OnLinkClick() {

    // HTML内のテーブルのIDを指定してテーブル要素を取得
    var table = document.getElementById("event_list");
    
    // テーブル内の行を取得
    var rows = table.rows;
    
    // テーブルの行数を取得
    var rowCount = rows.length;
    
    // テーブルの行を逆順にするためのループ
    for (var i = rowCount - 1; i >= 1; i--) {
        // テーブルに行を追加する。既存の場所から削除して追加することで、逆順になる
        table.appendChild(rows[i]);
    }
    

}
</script>
<script>
// --- クッキー操作 ---
function getCookie(name) {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? match[2] : null;
}
function setCookie(name, value, days = 370) {
  const maxAge = days * 24 * 60 * 60;
  document.cookie = `${name}=${value}; path=/; max-age=${maxAge}`;
}

// --- 設定保存 ---
function saveSettings() {
  const newRows = document.getElementById("rows").value;
  const newOrder = document.querySelector('input[name="order"]:checked').value;
  const oldRows = getCookie("rows");
  const oldOrder = getCookie("order");

  setCookie("rows", newRows);
  setCookie("order", newOrder);

  closeModal();

  if (newRows !== oldRows || newOrder !== oldOrder) {
    alert("設定を保存しました。ページを更新します。");
    location.reload();
  } else {
    //alert("設定は変更されていません。");
  }
}

// --- モーダル制御 ---
const modal = document.getElementById("settingsModal");
document.getElementById("openModal").onclick = () => modal.style.display = "flex";
document.getElementById("closeModal").onclick = () => closeModal();
window.onclick = (e) => { if (e.target === modal) closeModal(); };

function closeModal() {
  modal.style.display = "none";
}
</script>



EOD;
putHtmlContainerClose($script);
