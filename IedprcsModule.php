<?php
//

require_once 'DbMa.php';
//require_once 'Encode.php';
//require_once 'htmlpkg.php';
//require_once 'timestamplinker.php';


function draftdbRowEditor($mode, $evwcode, $seqnum, $act) {
    if ((! in_array($mode, ['D', 'E'])) || is_null($evwcode) || (intval($seqnum) <= 0)) {
        return [0, 'ILLIGALPARAM'];
    }
    switch ($act) {
    case 'DEL':
        try {
            $db = getDb();
            $s = $db->prepare("DELETE FROM tbvodraft WHERE  evwcode=:evwcode AND drafttype=:drafttype AND seqnum=:seqnum ");
            $s->bindValue(':evwcode', $evwcode);
            $s->bindValue(':drafttype', $mode);
            $s->bindValue(':seqnum', $seqnum);
            $s->execute();

        } catch (PDOException $e) {
            //die("Error:{$e->getMessage()}");
            $txt = $e->getMessage();
            return [0, 'DB_ERROR:' . $txt];
        }

        return [1, 'OK'];
        break;




    case 'MVUP':
        if ($seqnum <= 1) {
            return [0, 'SEQNUMEQLT1'];
        }
        $seqnum = $seqnum - 1; //Swap with above converts to swap with bottom
//上と入れ替えは下と入れ替えに変更（つまりseqnumを1減らす）して、このままcase'MVDOWN':へ処理を続けます
//No break to continue executing below
    case 'MVDOWN':
        try {
            $db = getDb();
            $s = $db->prepare("SELECT count(*) ,max(seqnum) FROM tbvodraft WHERE  evwcode=:evwcode AND drafttype=:drafttype ");
            $s->bindValue(':evwcode', $evwcode);
            $s->bindValue(':drafttype', $mode);
            //$s->bindValue(':drafttype', $mode);
            $s->execute();
            $cnt = $s->fetch(PDO::FETCH_ASSOC);
            $count = $cnt['count(*)'];
            $max = $cnt['max(seqnum)'];
            if ($count != $max) { //seqnumは連続である必要があります　連続番号でない
                return [0, 'NOT_SEQNUM_CONSECUTIVE'];
            }

            if ($seqnum >= $max) { //seqnumが最大値−１以下でない場合。下と入れ替えできない
                return [0, 'SEQNUM_IS_LAST'];

            }

            //入れ替え開始
            $seqnumA = $seqnum;
            $seqnumB = $seqnum + 1;
            $drafttype = $mode;
            // SQL文の定義（名前付きプレースホルダを使用）
            $sql = "UPDATE tbvodraft AS t1
        JOIN tbvodraft AS t2
            ON (t1.drafttype = :drafttype AND t1.evwcode = :evwcode AND t1.seqnum = :seqnumA AND t2.drafttype = :drafttype AND t2.evwcode = :evwcode AND t2.seqnum = :seqnumB)
            OR (t1.drafttype = :drafttype AND t1.evwcode = :evwcode AND t1.seqnum = :seqnumB AND t2.drafttype = :drafttype AND t2.evwcode = :evwcode AND t2.seqnum = :seqnumA)
        SET
            t1.songid  = t2.songid,
            t1.arrng   = t2.arrng,
            t1.time    = t2.time,
            t1.memo    = t2.memo,
            t1.comment = t2.comment";

            // プリペアドステートメントの準備
            $s = $db->prepare($sql);

            // パラメータをバインドして実行
            $s->execute([
                ':drafttype' => $drafttype,
                ':evwcode' => $evwcode,
                ':seqnumA' => $seqnumA,
                ':seqnumB' => $seqnumB,
            ]);

            return [1, 'OK'];






        } catch (PDOException $e) {
            //die("Error:{$e->getMessage()}");
            $txt = $e->getMessage();
            return [0, 'PDOERROR:' . $txt];
        }
        break;




    default:
        return [0, "UNDEF_ACTION"];
    }
}