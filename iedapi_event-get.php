<?php
require_once 'DbMa.php';
require_once 'Encode.php';
//require_once 'htmlpkg.php';
//require_once 'timestamplinker.php';

require_once 'dbAu.php';

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    $user = $auth->getCurrentSessionUserInfo();
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
        'SELECT *
       FROM tbevent
      WHERE evwcode = ?'
    );

    $stmt->execute([
        $_GET['evwcode'],
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $row) {

        echo json_encode([
            'success' => false,
            'message' => 'データが存在しません',
        ]);

        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => $row,
    ]);
} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        //   'message' => 'データ取得に失敗しました',
        'message' => "DBError:", //{$e->getMessage()}",
    ]);
}
