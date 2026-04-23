<?php
   require_once 'DbMa.php';
   require_once 'Encode.php';
   require_once 'htmlpkg.php';
   require_once 'timestamplinker.php';
   require_once 'processpkg.php';


// Import the necessary classes
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

// Include the composer autoload file
require 'vendor/autoload.php';
require_once 'sen_cnfg0001.php';
// Setup a new Eloquent Capsule instance



$title='管理（曲のデータ修正）';
$h2="管理（曲のデータ修正）";
putHtmlHeader($title,$h2);
//check login admin  

if ($user = Sentinel::check()) {
    // ログインしているアカウントをチェック
    putHtmlNavibar('admin');
    print "<div class=\"normalmessage\">アカウント {$user['email']} でログインしています</div>";
    
}else {
    putHtmlNavibar();
    print "<div class=\"normalmessage\">ログインしていません</div>\n\n";
    print "<div class=\"normalmessage\"><a href=\"sen_nowusr.php\">ログインはこちら</a></div>\n\n";
    
    putHtmlContainerClose();
    exit;
    // ここで終了
}









?>
<div id="formcontainer">
    <form method="GET" action="udsong_ex.php">
        <div  class="formparts_2">
            <div class="fml"><label class="label">songid</label></div><input name="songid" id="songid" type="text" size="5" maxlength="5" >
        </div>
        <div  class="formparts_2">
        <div class="fml"><label class="label">arrange ver</label></div><input name="arrng" id="arrng" type="text" size="3" maxlength="3" >
        </div>
        <div class="submitbutton">
        <input  type="submit" value="開始／PickUp">
        </div>

    </form>
    <form method="POST" action="udsong_ex.php">
        <div  class="formparts_2">
        <div class="fml"><label class="label">search  title or yomi (max two words divide by space)</label></div><input name="keyword" id="keyword" type="text" size="20" maxlength="30">
        </div>


        <div class="submitbutton">
        <input  type="submit" value="検索／Search">
        </div>

    </form>
</div>
<?php
// display search result
if(isset($_POST['keyword'])){
    $keyword=$_POST['keyword'];
    putHtmlsearchsongresult($keyword);


}
// display song data for edit 
if(isset($_GET['songid'])){
    if(preg_match('/^\d{1,4}$/',$_GET['songid'],$match)){
        $songid=$match[0];
        if(preg_match('/^\d{1,2}$/',$_GET['arrng'],$match2)){
            $arrng=$match2[0];

        }else{
        $arrng="0" ;//if arrng is blank or no number
        }
    }else{//songid is no match regex 
        $songid="";
    }

}else{// $_GET songid is nothing
    $songid="";
}
//
// display search result  in a form
if ($songid!==""){

    try {
        $db = getDb();
        //PREPAER
        $s = $db->prepare(' select * from tbsong where songid = :songid and arrng = :arrng');
        $s->bindValue(':songid',$songid);
        $s->bindValue(':arrng',$arrng);
        $s->execute();
        while($row = $s->fetch(PDO::FETCH_ASSOC)){

            $songid = $row['songid'];
            $arrng = $row['arrng'];
            $sname = $row['sname'];
            $yomi = $row['yomi'];
            $artist = $row['artist'];
            $tieup = $row['tieup'];
            $vocap = $row['vocap'];
            $genre = $row['genre'];
            $relsd = $row['relsd'];
            $relsd = is_null($relsd)?'':$relsd;

            ?>
<div id="formcontainer">
    <form method="POST" action="udsongprocess_ex.php">
        
        <div id="pack"> 
    <div id="newsong">
        <div  class="formparts_2">
            <div class="fml"><label class="label">songid</label></div><input name="songid" id="songid" type="text" size="10" maxlength="10" readonly value="<?=e($songid)?>">
        </div>
        <div  class="formparts_2">
            <div class="fml"><label class="label">arrng</label></div><input name="arrng" id="arrng" type="text" size="5" maxlength="5" readonly value="<?=e($arrng)?>">
        </div>
        <div  class="formparts_2">
            <div class="fml"><label class="label">song title</label></div><input name="sname" id="sname" type="text" size="50" maxlength="120" value="<?=e($sname)?>">
        </div>
        <div  class="formparts_2">
            <div class="fml"><label class="label">song yomi a-Z,0-9 or hiragana</label></div><input name="yomi" id="yomi" type="yomi" size="50" maxlength="120" value="<?=e($yomi)?>">
        </div>
        <div  class="formparts_2">
            <div class="fml"><label class="label">artist</label></div><input name="artist" id="artist" type="text" size="50" maxlength="120" value="<?=e($artist)?>">
        </div>
        <div  class="formparts_2">
            <div class="fml"><label class="label">tie up anime etc.</label></div><input name="tieup" id="tieup" type="text" size="50" maxlength="120" value="<?=e($tieup)?>">
        </div>
        <div  class="formparts_2">
            <div class="fml"><label class="label">vocalo P name</label></div><input name="vocap" id="vocap" type="text" size="50" maxlength="120" value="<?=e($vocap)?>">
        </div>
        <div  class="formparts_2">
            <div class="fml"> <label class="label">media</label></div>
            <select id="genre" name="genre">
                <option value="P"  <?=$genre=='P'?'selected':''?>>P:J-POP</option>
                <option value="A"  <?=$genre=='A'?'selected':''?>>A:Animation song</option>
                <option value="V"  <?=$genre=='V'?'selected':''?>>V:Vocalo song</option>
                <option value="G"  <?=$genre=='G'?'selected':''?>>G:Game song</option>
                <option value="o"  <?=$genre=='o'?'selected':''?>>o:Original song</option>
                <option value="I"  <?=$genre=='I'?'selected':''?>>I:for The Idolmaster </option>
                <option value="R"  <?=$genre=='R'?'selected':''?>>R:Rock</option>
                
                <option></option>
            </select>
        </div>
        <div  class="formparts_2">
            <div class="fml"><label class="label">released (yyyymmdd or yyyy-mm-dd)</label></div><input name="relsd" id="relsd" type="text" size="10" maxlength="10" value="<?=e($relsd)?>">
        </div>
        <div  class="formparts_2">
            <div class="fml">

                <label class="label">DELETE</label>
            </div>
        <input type="checkbox" name="remove" value="1" ><label>このSongID(arrnge id)の曲を削除する(セットリストにある場合は削除できません)</label>

        </div>


        <div>songid,arrngは編集できません。この編集ページは誤字の訂正等のために用意されています。歌唱データが参照していますので、別の曲に変えると参照している全セットリストに影響します。同名の曲と混同していた場合など、影響がないか検討する必要があります。そのような必要がある場合は新しい曲を登録し、歌唱データのsongidを変更する方がよいかもしれません。</div>
        <div id="submitbutton">
            <input  type="submit" value="更新／Update">
        </div>
    </div>

        </form>
</div>
<?php
        }
        
    }catch(PDOException $e){
        die("Error:{$e->getMessage()}");
    }   


}


putHtmlContainerClose();



