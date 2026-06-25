<?php
require_once 'DbMa.php';
//require_once 'Encode.php';
require_once 'htmlpkg.php';
//require_once 'timestamplinker.php';

require_once 'dbAu.php';

if ($auth->isLogged()) {
    // ログインしているアカウントをチェック
    //$user = $auth->getCurrentSessionUserInfo();
    //   putHtmlNavibar('admin');
    //   print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
} else {
    $txt = $e->getMessage();
    $errcode = '403'; //.$txt;
    header('Content - Type: application / json;
    charset = utf - 8');
    echo json_encode([
        'success' => false,
        'message' => $errcode,
    ]);

    exit;
    // ここで終了
}

$evwcode = $_POST['evwcode'] ?? '';

try {
    $db = getDb();
    $s = $db->query("select evwcode from tbevent;");

    $evwcode_db = $s->fetchAll(PDO::FETCH_COLUMN); //single array, like  ['data1','data2',...]

    if (in_array($evwcode, $evwcode_db)) {
        //rint '<div>ok evwcode</div>';
    } else {
        //rint 'evwcode ng';
        $errcode = 'NOT_FOUND_EVENTCODE ';
        header('Content - Type: application / json;
    charset = utf - 8');
        echo json_encode([
            'success' => false,
            'message' => $errcode,
        ]);
        exit;
    }

    $s = $db->prepare("DELETE FROM tbvocal WHERE  evwcode=:evwcode");
    $s->bindValue(':evwcode', $evwcode);
    //$s->bindValue(':drafttype', $mode);
    $s->execute();

//下書きも消去
    $s = $db->query("DELETE FROM tbvodraft WHERE drafttype IN ('D' ,'E')");


} catch (PDOException $e) {
    //die("Error:{$e->getMessage()}");
    $txt = $e->getMessage();
    $errcode = 'DATABASE_ERROR: '; //.$txt;
    header('Content - Type: application / json;
    charset = utf - 8');
    echo json_encode([
        'success' => false,
        'message' => $errcode,
    ]);
}
header('Content - Type: application / json;
    charset = utf - 8');

echo json_encode([
    'success' => true,
    'message' => '消去が完了しました',
]);