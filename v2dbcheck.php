<?php
require_once 'DbMa.php';
require_once 'Encode.php';


function v2dbSchemaCheck()
{
    try {
        $db = getDb();
        //V2テーブルtbvtuberチェック
        $tableName = 'tbvtuber'; // 調べたいテーブル名

        $stmt = $db->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$tableName]);
        $tableExists = $stmt->fetch() !== false;

        if ($tableExists) {
            $msg = ""; // 'テーブルは存在します。';
        } else {
            $msg = 'データベースにテーブルtbvtuberが存在しません。';
        }
        // カラムの存在を確認するSQL（DATABASE()で現在のDBを自動指定）
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :tablename 
          AND COLUMN_NAME = :colname";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':tablename' => "tbevent",
            ':colname'   => "vtcode",
        ]);

        $columnExists = (bool) $stmt->fetchColumn();
        if ($columnExists) {
            $msg .= '';
        } else {
            $msg .=  'テーブルtbeventにカラムvtcodeが存在しません。';
        }
    } catch (PDOException $e) {
        echo "DB error!{$e->getMessage()}";
        return [false, 'DB error'];
    }
    return [$tableExists && $columnExists, $msg];
}

function defaultVtCheck()
{
    try {
        $db = getDb();
        $tableName = 'tbvtuber'; // チェックしたいテーブル名

        // 1件でもデータが存在すれば「1」を返すクエリ
        $sql = "SELECT 1 FROM {$tableName} LIMIT 1";
        $stmt = $db->query($sql);

        // fetch()で結果が取得できればデータは1件以上ある
        if ($stmt->fetch()) {
            return [true, 'OK']; // データは1件以上登録されています
        } else {
            return [false, 'Vtuber名が登録されていません。必ず1人は登録してください']; //データは登録されていません
        }
    } catch (PDOException $e) {
        echo "DB error!{$e->getMessage()}";
        return [false, 'DB error'];
    }
}
