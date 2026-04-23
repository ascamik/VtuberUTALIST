<?php
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'chckdate.php';
require_once 'Relsd2cssclass.php';
?>
<?php

function putHtmlOrdrTable($order, $on_genre, $limit, $nomedia = '',  $having = '', $mode = '')
{


    if ($mode == 'wikip') {

        print '<pre>';
    } else {
?>





        <div class="table">
            <div class="table_head">
                <div class="table-grid">
                    <!--<th class="songid_h">ID</th>-->
                    <div class="cell">曲名</div>
                    <div class="cell">アーティスト/P</div>

                    <!--<th>ボカロP</th>-->
                    <div class="cell">リリース</div>
                    <div class="cell">TieUp </div>
                    <div class="cell">G</div>
                    <div class="cell">直近歌唱</div>
                    <div class="cell">回数</div>
                </div><!-- table-grid close -->
            </div><!-- table_head close -->

            <div class="tbody">
                <?php
            }


            //connection to DB
            try {
                $db = getDb();
                //SELECT
                //$s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,count(*) from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng=tbsong.arrng {$dbsearch} group by tbvocal.songid,tbvocal.arrng order by tbsong.vocap COLLATE utf8mb4_unicode_ci;");

                //$nomedia="evmedia!='2' and";
                //$on_genre ='genre regexp"[VPRGIAo]"';
                //$having ='having max(evdate)<"2022-12-31"';
                //$order = 'order by count(*) DESC';

                $s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,tbsong.relsd,count(*),max(evdate) from tbvocal join tbsong on tbvocal.songid=tbsong.songid and tbvocal.arrng=tbsong.arrng  join tbevent using(evwcode) where {$nomedia}  {$on_genre} group by tbvocal.songid,tbvocal.arrng  {$having} {$order} {$limit} ;");

                while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                    $boldcss4helper = 'table-grid';
                    $relsd = $row['relsd']; //release date NULL treatment
                    $relsd = is_null($relsd) ? '' : $relsd;
                    $relsyear = $relsd == '' ? '' : substr($relsd, 0, 4);
                    //print_r ($row);
                    //Special format output for wikiwiki markup style
                    if ($mode == 'wikip') {
                        print "|" . e($row['sname']) . "|" . e($row['artist']) . "|" . e($row['tieup']) . "|" . e($row['vocap']) . "|" . e($row['count(*)']) . "|" . e($row['genre']) . "|\n";
                    } else {
                        if (intval($row['count(*)']) > 2) {
                            $boldcss4helper = 'table-grid strong4h';
                        }
                        $sid = $row['arrng'] ? e($row['songid']) . '-' . e($row['arrng']) : e($row['songid']);
                ?>
                        <div class="<?= $boldcss4helper ?>">

                            <!--<td class="songid"><?= $row['arrng'] ? e($row['songid']) . '-' . e($row['arrng']) : e($row['songid']) ?></td>-->


                            <div class="cell"><span title="<?= 'ID=' . $sid ?>"><a href="<?php
                                                                                            //
                                                                                            print 'shistory.php?sid=' . e($row['songid']);
                                                                                            ?>"><?= e($row['sname']) ?></a></span></div>



                            <div class="cell"><?= e($row['artist']) . (($row['artist'] and $row['vocap']) ? "/" : "") .  e($row['vocap']) ?></div>
                            <!--<td><?= e($row['vocap']) ?></td>-->
                            <div class="cell <?= $relsd ? relclass($relsd) : '' ?>"><span title="<?= $relsd ?>"><?= e($relsyear) ?></span></div>
                            <div class="cell"><?= e($row['tieup']) ?></div>


                            <div class="cell"><?= e($row['genre']) ?></div>
                            <div class="cell"><?= e($row['max(evdate)']) ?></div>
                            <div class="cell"><?= e($row['count(*)']) ?></div>

                        </div>

                <?php
                    }
                }
            } catch (PDOException $e) {
                print 'DB障害のようです';
                die("Error:{$e->getMessage()}");
            }

            if ($mode == 'wikip') {
                print '</pre>';
            } else {
                ?>
            </div> <!-- tbody close -->
        </div><!-- table close -->




<?php
            }
        } //end of fuction
?>