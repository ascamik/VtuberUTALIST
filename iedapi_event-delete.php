<?php
require_once 'DbMa.php';
require_once 'Encode.php';
//require_once 'htmlpkg.php';
//require_once 'timestamplinker.php';

require_once 'dbAu.php';

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    //$user = $auth->getCurrentSessionUserInfo();
    //   putHtmlNavibar('admin');
    //   print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    //403
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden',
    ]);
    exit;
    // ここで終了
}
try {
    $db = getDb();
    $stmt = $db->prepare(
        'SELECT COUNT(*)
       FROM tbvocal
      WHERE evwcode = ?'
    );

    $stmt->execute([
        $_POST['evwcode'],
    ]);

    if ($stmt->fetchColumn() > 0) {

        echo json_encode([
            'success' => false,
            'message' =>
            '関連するボーカルデータが存在するため削除できません',
        ]);

        exit;
    }
    //evwcode の正常性
    $evwcode = trim($_POST['evwcode'] ?? '');
    $s = $db->query("select evwcode from tbevent;");

    $evwcodelist = $s->fetchAll(PDO::FETCH_COLUMN); //single array, like  ['data1','data2',...]
    // print_r($evwcode);
    if (! (in_array($evwcode, $evwcodelist))) {
        echo json_encode([
            'success' => false,
            'message' =>
            '指定されたコードが見つかりません',
        ]);
        exit;
    }

    $stmt = $db->prepare(
        'DELETE
       FROM tbevent
      WHERE evwcode = ?'
    );

    $stmt->execute([
        $_POST['evwcode'],
    ]);
} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => "DBError:", //{$e->getMessage()}",
    ]);
}
echo json_encode([
    'success' => true,
]);
