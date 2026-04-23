<?php
    require_once 'DbMa.php';
    require_once 'Encode.php';

function deleteRecordtbv ($ev,$seq){

    //check parameters for protect sql injection
    //Get list of Event code from DB
    if (preg_match('/^\d{1,3}$/',$seq,$match)){
        $seq=$match[0];
    }else{
        $seq=0;
    }

    try {
        $db = getDb();
        //SELECT event code
        $s = $db->query("select evwcode from tbevent;");
    
        $evwcodeList = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
        // print_r($evwcode);
    }
    catch(PDOException $e){
        die("Error:{$e->getMessage()}");
    }
    if(!(in_array($ev,$evwcodeList) and intval($seq) > 0) ){
        //illegal parameter
        return 1;
    }



    //connection to DB
    try {
        $db = getDb();
        $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        //start transaction
        $db->beginTransaction();
        $s = $db->query("DELETE FROM tbvocal WHERE evwcode=\"{$ev}\" and seqnum=\"{$seq}\";");
        $delcount = $s->rowCount();
        if ($delcount!==1){ //delete record count  is not one record . something wrong??
            return 2;
        }
        // change the seqnum of record  after  delete record ... this is bad operation. because DATABASE design is bad. prikey record must not change the data set at the first time.

        $s = $db->query("UPDATE tbvocal SET seqnum=seqnum-1 WHERE evwcode=\"{$ev}\" and seqnum > \"{$seq}\" ORDER BY seqnum ASC ;");
        $db->commit();

        return 0;
        // delete process success





    }catch(PDOException $e){
        $db->rollback();
        die("Error:{$e->getMessage()}");
    }

}



function insertRecordtbv ($ev,$seq){

    //check parameters for protect sql injection
    //Get list of Event code from DB
    if (preg_match('/^\d{1,3}$/',$seq,$match)){
        $seq=$match[0];
    }else{
        $seq=0;
    }

    try {
        $db = getDb();
        //SELECT event code
        $s = $db->query("select evwcode from tbevent;");
    
        $evwcodeList = $s->fetchAll(PDO::FETCH_COLUMN);   //single array, like  ['data1','data2',...]     
        // print_r($evwcode);
    }
    catch(PDOException $e){
        die("Error:{$e->getMessage()}");
    }
    if(!(in_array($ev,$evwcodeList) and intval($seq) > 0) ){
        //illegal parameter
        return 1;
    }



    //connection to DB
    try {
        $db = getDb();
        $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        //start transaction
        $db->beginTransaction();

        // change the seqnum of record  after  delete record ... this is bad operation. because DATABASE design is bad. prikey record must not change the data set at the first time.

        $s = $db->query("UPDATE tbvocal SET seqnum=seqnum+1 WHERE evwcode=\"{$ev}\" and seqnum >= \"{$seq}\" ORDER BY seqnum DESC ;");
        $inscount = $s->rowCount();
        if($inscount==0){// insert process error ,may be not found evwcode&seq data in setlist
            return 1;
        }
        $db->commit();

        return 0;
        // insert process success





    }catch(PDOException $e){
        $db->rollback();
        die("Error:{$e->getMessage()}");
    }

}