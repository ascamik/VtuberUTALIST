<?php

require_once 'DbMa.php';

require_once 'Encode.php';
require_once 'htmlpkg.php';

function putHtmlsearchsongresult($keyword){ 




//print_r($_POST);
//check the post keyword
if($keyword){
    if(preg_match("/(\w+)\s*(.*)/u",mb_substr($keyword,0,50),$keywordlist)){

        $keyword1='%'.$keywordlist[1].'%';
        $keyword2='%'.$keywordlist[2].'%';
         //connection to DB
        try {
        $db = getDb();
        //PREPAER
       // $s = $db->prepare(' select * from tbsong where sname COLLATE utf8mb4_unicode_ci like :keyword1 and sname COLLATE utf8mb4_unicode_ci like :keyword2 or yomi COLLATE utf8mb4_unicode_ci like :keyword1 and yomi COLLATE utf8mb4_unicode_ci like :keyword2 or vocap COLLATE utf8mb4_unicode_ci like :keyword1 and vocap COLLATE utf8mb4_unicode_ci like :keyword2 or artist COLLATE utf8mb4_unicode_ci like :keyword1 and artist COLLATE utf8mb4_unicode_ci like :keyword2 or tieup COLLATE utf8mb4_unicode_ci like :keyword1 and tieup COLLATE utf8mb4_unicode_ci like :keyword2 LIMIT 50');
        $s = $db->prepare(' select * from tbsong where sname COLLATE utf8mb4_unicode_ci like :keyword1 and sname COLLATE utf8mb4_unicode_ci like :keyword2 or yomi COLLATE utf8mb4_unicode_ci like :keyword1 and yomi COLLATE utf8mb4_unicode_ci like :keyword2 LIMIT 50');
        $s->bindValue(':keyword1',$keyword1);
        $s->bindValue(':keyword2',$keyword2);
       // $s->bindValue(':keyword3',$keyword1);
       // $s->bindValue(':keyword4',$keyword2);
        $s->execute();
             
        ?>
        <div id="tableoutline">
            <table>
                <thead>
                    <tr>
                    <th class="songid_h">ID</th><th class="one_em">#</th><th class="s_name">曲名</th><th>アーティスト</th><th>Tie Up</th><th>ボカロP</th><th class="one_em">G</th>
                    </tr>
                </thead>
            <tbody>
        <?php 


            while($row = $s->fetch(PDO::FETCH_ASSOC)){
    //print_r ($row);
    
    ?>
        <tr>
    
            <td class="songid"><?=e($row['songid'])?></td>
            <td class="one_em"><?=e($row['arrng'])?></td>
           
    
            <td  class="s_name"><?=e($row['sname'])?></td>
    
    
    
            <td><?=e($row['artist'])?></td>
            <td><?=e($row['tieup'])?></td>
            <td><?=e($row['vocap'])?></td>
            <td class="one_em"><?=e($row['genre'])?></td>
         
        
        </tr>
          
    <?php
    
            }

        }catch(PDOException $e){
            die("Error:{$e->getMessage()}");
        }
    }
}
?>
</tbody>
</table>
</div>
<?php

}