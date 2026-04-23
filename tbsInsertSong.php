<?php

require_once 'DbMa.php';
require_once 'chckdate.php';

// this function is to insert new song record  to database 'tbsong'

function insertSongtbs(string  $sname, string  $yomi, string $genre, string $orgsongid = '0', string  $artist = '', string  $tieup = '', string  $vocap = '', string $relsd = ''): array
{
    if ($sname == "" or $yomi == "" or !(preg_match('/^[PAVGoIR]$/', $genre))) { //check parameter data
        return array('err' => 8, 'songid' => 0, 'arrng' => 0);
    }
    if (preg_match('/^\d+$/', $orgsongid) and intval($orgsongid) > 0) { //if new song is arranged version song
        try {
            $db = getDb();
            $s = $db->query("select max(arrng) from tbsong where songid=\"{$orgsongid}\" ;");
            $maxarrng = $s->fetchAll(PDO::FETCH_COLUMN);
            if (is_null($maxarrng[0]) === False) { //既存のアレンジ曲バージョンの最大値/ max arrng of songid in DB, 
                //print'<div>songid found ..ok</div>';
                $narrng = strval(intval($maxarrng[0]) + 1); //for insert to db. actually, type of $maxarrng[0] is integer
                $nsongid = $orgsongid;
            } else { // songid  don't exist
                //print'<div>not found</div>';
                $nsongid = "";
                $narrng = "0";
                //return array('err'=>2, 'songid'=>0, 'arrng'=>0);

            }
        } catch (PDOException $e) {
            //    die("Error:{$e->getMessage()}");
            return array('err' => 9, 'songid' => 0, 'arrng' => 0);
        }
    } else { // no set orgsongid (default -> '0')
        $nsongid = "";
        $narrng = "0";
    }




    try {
        $db = getDb();
        // new song duplication check: if the song already exists in db

        $s = $db->prepare(' select * from tbsong where sname  COLLATE utf8mb4_unicode_ci like :keyword1 or yomi COLLATE utf8mb4_unicode_ci like :keyword2 ');
        $s->bindValue(':keyword1', $sname); //完全一致
        $s->bindValue(':keyword2', $yomi); //完全一致
        $s->execute();

        //$artist=isset($_POST['artist'])?$_POST['artist']:'';
        //$vocap=isset($_POST['vocap'])?$_POST['vocap']:'';

        $duplicateFlag = 0; //initialize
        while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
            if ($row['songid'] !== $nsongid and $row['artist'] == $artist and $row['vocap'] == $vocap) {
                //duplicate song
                $nsongid = $row['songid'];
                $narrng = $row['arrng'];

                $duplicateFlag = 1;
            }
        }

        if ($duplicateFlag) {
            return array('err' => 1, 'songid' => $nsongid, 'arrng' => $narrng); // the song aleady exists in db. 

        }



        if (!($nsongid)) { // called orgsongid is blank or nsongid is set 0 in  preprocess
            //print'<div>setting new songid</div>';
            $s = $db->query("select max(songid) from tbsong;");
            $maxsongid = $s->fetchAll(PDO::FETCH_COLUMN);
            // if (intval($maxsongid[0]) > 0){ // >0 if db is noproblem
            //print'<div>new songid set ..ok</div>';
            $nsongid = strval(intval($maxsongid[0]) + 1); //for insert to db 
            $narrng = '0';
        }

        //insert newsong data to tbsong table
        $s = $db->prepare('INSERT INTO tbsong (songid, arrng, sname, yomi, artist, tieup, vocap, genre, relsd) VALUES(:songid, :arrng, :sname, :yomi, :artist, :tieup, :vocap, :genre, :relsd)');
        $s->bindValue(':songid', $nsongid);
        $s->bindValue(':arrng', $narrng);
        $s->bindValue(':sname', $sname);
        $s->bindValue(':yomi', $yomi);
        $s->bindValue(':artist', $artist);
        $s->bindValue(':tieup', $tieup);
        $s->bindValue(':vocap', $vocap);
        $s->bindValue(':genre', $genre);

        //$relsd=$_POST['relsd']; //shuld be checked  if format is correct DATE format.
        $relsd = chkMysqlDate($relsd); // check validate and format

        if ($relsd != '') {
            $s->bindValue(':relsd', $relsd);
        } else {
            $s->bindValue(':relsd', null, PDO::PARAM_NULL);
        }


        $s->execute();
        //print'<div>曲データを正常に追加しました</div>';

        //$validate = true;
        //success
        return array('err' => 0, 'songid' => $nsongid, 'arrng' => $narrng);
    } catch (PDOException $e) {
        //die("Error:{$e->getMessage()}");
        return array('err' => 9, 'songid' => 0, 'arrng' => 0);
    }
}
