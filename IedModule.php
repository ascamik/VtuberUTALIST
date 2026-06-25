<?php
//create a song list table For Integrated Editor
require_once 'DbMa.php';
require_once 'Encode.php';
require_once 'htmlpkg.php';
require_once 'chckdate.php';
require_once 'Relsd2cssclass.php';




//
function chkDraftmode()
{
    //empty == 0
    //editmode  == 1
    //draftmode == 2
    //error == 99

    try {
        $db = getDb();

        $s = $db->query("select count(*) from tbvodraft");

        $datacount = $s->fetch(PDO::FETCH_ASSOC);
        $all = $datacount['count(*)'];
        if ($all == 0) {
            return 0;
        } else {
            $s = $db->query("select count(*) from tbvodraft WHERE drafttype = 'E' ");

            $datacount = $s->fetch(PDO::FETCH_ASSOC);
            $edit = $datacount['count(*)'];
            if ($all == $edit) {
                return 1;
            } else {
                $s = $db->query("select count(*) from tbvodraft WHERE drafttype = 'D' ");

                $datacount = $s->fetch(PDO::FETCH_ASSOC);
                $draft = $datacount['count(*)'];
                if ($all == $draft) {
                    return 2; //but it is in draftmode
                } else {
                    return 99; // count is contradiction
                }
            }
        }
    } catch (PDOException $e) {
        die("Error:{$e->getMessage()}");
    }
}
















function putHtmlSetlistAgent($mode = 'D')
{

    switch ($mode) {
        case 'D':
            $target = 'iedfinalize.php';
            $command = '反映して完了';
            break;
        case 'I':
            $target = 'iedsetlisteditstart.php';
            $command = '選んだセットリストを編集開始';
            break;
        case 'E':
            $target = 'iedfinalize.php';
            $command = '反映して完了';
            break;









        default:
            echo "OTHER";
            return;
    }
    if ($mode === 'E') { // draft mode <button only>
?>
        <div id="formcontainer">
            <div class="formparts">
                <form method="POST" action="<?= $target ?>">

                    <div id="submitbutton"><input type="submit" value="<?= $command ?>"></div>

                </form>
            </div>
        </div>

        <?php

    } else { //pulldown and submit button

        try {
            $db = getDb();
            if ($mode === 'I') {

                $s = $db->query("select evwcode, evdate, evtitle from tbevent order by evdate DESC;");
            } else {

                $s = $db->query("SELECT evwcode, evdate, evtitle FROM tbevent a WHERE NOT EXISTS ( SELECT 1 FROM tbvocal b WHERE b.evwcode = a.evwcode) order by evdate DESC"); //セットリストの空のevwcodeだけに限定
            }
            $rows = $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error:{$e->getMessage()}");
        }
        if (empty($rows)) {

            print '<div class="guidance alert">【注意】選択できるイベントがありません<br>すべてのイベントにセットリストが存在します。すでにあるセットリストへ上書きはできません<br>新しいイベントを登録するか、既存のセットリストを完全に削除してください<br>下書き作業は継続でき、画面を移っても下書きは保存されます<br>最初からやり直す場合は［リセット］をクリックし、下書きを消去します</div>';
        } else {
        ?>
            <div id="formcontainer">
                <div class="formparts">
                    <form method="POST" action="<?= $target ?>">
                        <label>イベント選択</label>
                        <select name="evwcode">
                            <?php
                            foreach ($rows as $row) {
                                $e = e($row['evwcode']);
                                // print "<option value=\"{$e}\">{$e}:".e($row['evdate']).'/'.e($row['evtitle'])."</option>";
                                print "<option value=\"{$e}\">{$e}:" . e($row['evdate']) . '/' . mb_substr(e($row['evtitle']), 0, 40) . "</option>";
                            }



                            ?>
                        </select>
                        <div id="submitbutton"><input type="submit" value="<?= $command ?>"></div>

                    </form>
                </div>
            </div>

        <?php
        }
    }
}


















function putHtmlDrafttable($mode = 'D')
{

    $dbwhere = "where tbvodraft.drafttype=\"D\"";
    if ($mode == 'E') {

        $dbwhere = "where tbvodraft.drafttype=\"E\"";
    }

    $ecode = ''; //初期値 IまたはDモード
    $ecodetxt = '';

    try {
        $db = getDb();
        ?>
        <div id="tableoutline2">
            <div class="table">
                <div class="table_head">
                    <div class="table-grid">
                        <div class="cell">SID</div>
                        <div class="cell">a</div>
                        <div class="cell">N</div>
                        <div class="cell">time</div>
                        <div class="cell">曲 名</div>
                        <div class="cell">Artist/P</div>
                        <div class="cell">G</div>
                        <!--<div class="cell">Rel.</div>-->
                        <div class="cell">TieUp</div>
                        <div class="cell">メモ</div>
                        <div class="cell actions">操作</div>
                    </div><!-- table-grid close -->
                </div><!-- table_head close -->

                <div class="tbody">

                    <?php

                    //SELECT setlist
                    $s = $db->query("select evwcode,seqnum,time,sname,artist,vocap,tieup,tbsong.songid,tbsong.arrng,memo,tbsong.genre,tbsong.relsd from tbvodraft join tbsong on tbvodraft.songid=tbsong.songid and tbvodraft.arrng= tbsong.arrng {$dbwhere} order by tbvodraft.seqnum;");

                    while ($row = $s->fetch(PDO::FETCH_ASSOC)) {

                        $relsd = $row['relsd']; //release date NULL treatment
                        $relsd = is_null($relsd) ? '' : $relsd;
                        $relsyear = $relsd == '' ? '' : substr($relsd, 0, 4);


                        $ecode = e($row['evwcode']);

                    ?>
                        <div class="table-grid row" data-id="<?= e($row['evwcode']) ?>" data-num="<?= e($row['seqnum']) ?>">


                            <?php

                            $songid = e($row['songid']);
                            $arrng = e($row['arrng']);

                            print "<div class=\"cell\">{$songid}</div><div class=\"cell\">{$arrng}</div><div class=\"cell\">" . e($row['seqnum']) . "</div><div class=\"cell\">" . e($row['time']) . "</div>";




                            ?>


                            <div class="cell">
                                <?php
                                //
                                //            print '<a href="shistory.php?sid=' . e($row['songid']) . '">' . e($row['sname']) . '</a>';
                                print e($row['sname']);
                                ?>
                            </div>



                            <div class="cell"><?= e($row['artist']) . (($row['artist'] and $row['vocap']) ? "/" : "") .  e($row['vocap']) ?></div>
                            <div class="cell"><?= e($row['genre']) ?></div>
                            <!--<div class="cell <?= $relsd ? relclass($relsd) : '' ?>" title="<?= $relsd ?>"><?= e($relsyear) ?></div>-->
                            <div class="cell"><?= e($row['tieup']) ?></div>
                            <div class="cell"><?= e($row['memo']) ?></div>
                            <div class="cell actions">
                                <button class="move-up">↑</button>
                                <button class="move-down">↓</button>
                                <button class="delete-row">削除</button>
                            </div>
                        </div><!-- table-grid close -->

                <?php

                    }
                } catch (PDOException $e) {
                    die("Error:{$e->getMessage()}");
                }
                if ($ecode) {
                    $ecodetxt = '作業中のイベントコード:' . e($ecode);
                }
                ?>
                </div> <!-- tbody close -->
            </div><!-- table close -->
        </div><!-- table outline close -->
        <div id="evwcodeMemory" data-ec="<?= $ecode ?>"><?= $ecodetxt ?></div>
    <?php


}


// integrated editor left pane

function putHtmlSongList()
{
    ?>
        <div id="tableoutline1">
            <div class="table">
                <div class="table_head">
                    <div class="table-grid">
                        <!--<th class="songid_h">ID</th>-->
                        <div class="cell">曲名</div>
                        <div class="cell">アーティスト/P</div>

                        <div class="cell">リリース</div>
                        <div class="cell">TieUp </div>
                        <div class="cell">G</div>
                        <!--<div class="cell">・</div>-->
                        <div class="cell">操作</div>
                    </div><!-- table-grid close -->
                </div><!-- table_head close -->

                <div class="tbody">
                    <?php



                    //connection to DB
                    try {
                        $db = getDb();
                        //SELECT
                        //$s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,count(*) from tbvodraft join tbsong on tbvodraft.songid=tbsong.songid and tbvodraft.arrng=tbsong.arrng {$dbsearch} group by tbvodraft.songid,tbvodraft.arrng order by tbsong.vocap COLLATE utf8mb4_unicode_ci;");

                        //$nomedia="evmedia!='2' and";
                        //$on_genre ='genre regexp"[VPRGIAo]"';
                        //$having ='having max(evdate)<"2022-12-31"';
                        //$order = 'order by count(*) DESC';
                        //
                        $nomedia = "";
                        $on_genre = 'genre regexp"[VPRGIAo]"';
                        $having = '';
                        $order = 'order by tbsong.yomi COLLATE utf8mb4_unicode_ci';
                        $limit = '';

                        $s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.yomi,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,tbsong.relsd from tbsong where {$nomedia}  {$on_genre}   {$having} {$order} {$limit} ;");

                        //$s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.yomi,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,tbsong.relsd,count(*),max(evdate) from tbvodraft join tbsong on tbvodraft.songid=tbsong.songid and tbvodraft.arrng=tbsong.arrng  join tbevent using(evwcode) where {$nomedia}  {$on_genre} group by tbvodraft.songid,tbvodraft.arrng  {$having} {$order} {$limit} ;");


                        while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                            $boldcss4helper = 'table-grid';
                            $relsd = $row['relsd']; //release date NULL treatment
                            $relsd = is_null($relsd) ? '' : $relsd;
                            $relsyear = $relsd == '' ? '' : substr($relsd, 0, 4);
                            //print_r ($row);
                            //Special format output for wikiwiki markup style

                            //if (intval($row['count(*)']) > 2) {
                            //    $boldcss4helper = 'table-grid strong4h';
                            //}
                            $esongid = e($row['songid']);
                            $earrng = intval(e($row['arrng']));
                            $sid = $earrng ? $esongid . '-' . $earrng : $esongid;
                            $scripthtml = "<a class=\"no-click\" href=\"javascript:void(0)\" onclick=\"sendPost('{$esongid}','{$earrng}')\">➡️</a>";
                    ?>
                            <div class="<?= $boldcss4helper ?> song-row" data-songid="<?= $esongid ?>" data-arrng="<?= $earrng ?>">



                                <div class="cell"><span title="<?= 'ID=' . $sid ?>"><?= e($row['sname']) ?></a></span></div>



                                <div class="cell"><?= e($row['artist']) . (($row['artist'] and $row['vocap']) ? "/" : "") .  e($row['vocap']) ?></div>
                                <!--<td><?= e($row['vocap']) ?></td>-->
                                <div class="cell <?= $relsd ? relclass($relsd) : '' ?>"><span title="<?= $relsd ?>"><?= e($relsyear) ?></span></div>
                                <div class="cell"><?= e($row['tieup']) ?></div>


                                <div class="cell"><?= e($row['genre']) ?></div>
                                <!-- <div class="cell"></div>-->
                                <div class="cell no-click"><span title="<?= 'ID=' . $sid ?>"><?= $scripthtml ?></span></div>

                            </div>

                    <?php

                        }
                    } catch (PDOException $e) {
                        die("Error:{$e->getMessage()}");
                    }

                    ?>
                </div> <!-- tbody close -->
            </div><!-- table close -->
        </div><!-- tableoutline1 close -->
    <?php
}
function putHtmlQuickPickerSongList()
{

    $icode2sortby = [
        'y' => 'order by tbsong.yomi COLLATE utf8mb4_unicode_ci',
        's' => 'order by tbsong.sname COLLATE utf8mb4_unicode_ci',
        'v' => 'order by tbsong.vocap,tbsong.yomi COLLATE utf8mb4_unicode_ci',
        'c' => 'order by count(*),songid',
        'd' => 'order by count(*) DESC,songid',
        'l' => 'order by max(evdate),songid',
        'm' => 'order by max(evdate) DESC,songid',
        'r' => 'order by tbsong.relsd DESC,songid',
        'a' => 'order by tbsong.artist,tbsong.yomi  COLLATE utf8mb4_unicode_ci',
    ];
    $icode2regex = ['1' => '^[0-9a-zA-Z∀]', 'a' => '^[あいうえおをゔ]', 'k' => '^[かきくけこがぎぐげご]', 's' => '^[さしすせそざじずぜぞ]', 't' => '^[たちつてとだぢづでど]', 'n' => '^[なにぬねの]', 'h' => '^[はひふへほぱぴぷぺぽばびぶべぼ]', 'm' => '^[まみむめも]', 'y' => '^[やゆよらりるれろわん]'];
    $icode2char = ['1' => '数字・英字', 'a' => 'あ（ゔ）', 'k' => 'か', 's' => 'さ', 't' => 'た', 'n' => 'な', 'h' => 'は', 'm' => 'ま', 'y' => 'や・ら・わ'];

    //$displayIndex=[];
    ?>
        <div id="tableoutline1">
            <div class="table">
                <div class="table_head">
                    <div class="table-grid">
                        <!--<th class="songid_h">ID</th>-->
                        <div class="cell">曲名</div>
                        <div class="cell">アーティスト/P</div>

                        <div class="cell">リリース</div>
                        <div class="cell">TieUp </div>
                        <div class="cell">G</div>
                        <!--<div class="cell">・</div>-->
                        <div class="cell">操作</div>
                    </div><!-- table-grid close -->
                </div><!-- table_head close -->
            </div><!-- table close -->
            <?php



            //connection to DB
            try {
                $db = getDb();
                //SELECT
                //$s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,count(*) from tbvodraft join tbsong on tbvodraft.songid=tbsong.songid and tbvodraft.arrng=tbsong.arrng {$dbsearch} group by tbvodraft.songid,tbvodraft.arrng order by tbsong.vocap COLLATE utf8mb4_unicode_ci;");

                //$nomedia="evmedia!='2' and";
                //$on_genre ='genre regexp"[VPRGIAo]"';
                //$having ='having max(evdate)<"2022-12-31"';
                //$order = 'order by count(*) DESC';
                //


                foreach ($icode2regex as $index => $regex) {

                    $sortby = $icode2sortby['y'];
                    $dbsearch = 'tbsong.yomi REGEXP "' . $regex . '"';
                    $pagemarker = $icode2char[$index];
                    if ($index == '1') {
                        $dbsearch = 'tbsong.sname REGEXP "' . $regex . '"';
                        $sortby = $icode2sortby['s'];
                        //DBの英数字の読みをかなにした場合、英数のページにも表示させるための分岐
                    }


                    $nomedia = "";
                    $on_genre = $dbsearch; //'genre regexp"[VPRGIAo]"';
                    $having = '';
                    // $order = 'order by tbsong.yomi COLLATE utf8mb4_unicode_ci';
                    $order = $sortby;
                    $limit = '';

                    $s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.yomi,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,tbsong.relsd from tbsong where {$nomedia}  {$on_genre}   {$having} {$order} {$limit} ;");

                    //$s = $db->query("select tbsong.songid,tbsong.arrng,tbsong.sname,tbsong.yomi,tbsong.artist,tbsong.tieup,tbsong.vocap,tbsong.genre,tbsong.relsd,count(*),max(evdate) from tbvodraft join tbsong on tbvodraft.songid=tbsong.songid and tbvodraft.arrng=tbsong.arrng  join tbevent using(evwcode) where {$nomedia}  {$on_genre} group by tbvodraft.songid,tbvodraft.arrng  {$having} {$order} {$limit} ;");
                    // print "<div class=\"table-grid\" id=\"song_{$index}\"><div class=\"songlistanchor\">{$pagemarker}</div><div class=\"songlistanchor\"></div><div class=\"songlistanchor\"></div><div class=\"songlistanchor\"></div><div class=\"songlistanchor\"></div><div class=\"songlistanchor\"></div></div>";
                    print "<div class=\"list_index\" id=\"song_{$index}\"></div><div class=\"songlistanchor\">{$pagemarker}</div>
                        ";
                    // if ($index != '1') { //2回目以降は出力
                    print '<div class="table">';
                    // }
                    print '<div class="tbody">';

                    while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                        $boldcss4helper = 'table-grid';
                        $relsd = $row['relsd']; //release date NULL treatment
                        $relsd = is_null($relsd) ? '' : $relsd;
                        $relsyear = $relsd == '' ? '' : substr($relsd, 0, 4);
                        //print_r ($row);
                        //Special format output for wikiwiki markup style

                        //if (intval($row['count(*)']) > 2) {
                        //    $boldcss4helper = 'table-grid strong4h';
                        //}
                        $esongid = e($row['songid']);
                        $earrng = intval(e($row['arrng']));
                        $sid = $earrng ? $esongid . '-' . $earrng : $esongid;
                        $scripthtml = "<button class=\"appendSLBtn\" data-songid=\"{$esongid}\" data-arrng=\"{$earrng}\")\">▷</button>";
            ?>
                        <div class="<?= $boldcss4helper ?> song-row" data-songid="<?= $esongid ?>" data-arrng="<?= $earrng ?>">


                            <div class="cell"><span title="<?= 'ID=' . $sid ?>"><?= e($row['sname']) ?></a></span></div>



                            <div class="cell"><?= e($row['artist']) . (($row['artist'] and $row['vocap']) ? "/" : "") .  e($row['vocap']) ?></div>
                            <!--<td><?= e($row['vocap']) ?></td>-->
                            <div class="cell <?= $relsd ? relclass($relsd) : '' ?>"><span title="<?= $relsd ?>"><?= e($relsyear) ?></span></div>
                            <div class="cell"><?= e($row['tieup']) ?></div>


                            <div class="cell"><?= e($row['genre']) ?></div>
                            <!-- <div class="cell"></div>-->
                            <div class="cell no-click"><?= $scripthtml ?></div>

                        </div>

            <?php


                    } //while close
                    print '</div><!-- tbody close -->            </div><!-- table close -->';
                } //foreach close
            } catch (PDOException $e) {
                die("Error:{$e->getMessage()}");
            }

            //   </div> <!-- tbody close -->
            // </div><!-- table close -->
            ?>
        </div><!-- tableoutline1 close -->
    <?php
}
function putHtmlEventList()
{
    ?>

        <div id="tableoutline3">
            <table id="eventTable">
                <thead>
                    <tr>
                        <th class="two_em">C</th>
                        <th class="date_w">日付</th>
                        <th>配信タイトル</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orderSQL = "DESC";
                    $pagenation = ""; //$pagenation = "LIMIT {$pageSize} OFFSET {$offset}";
                    try {
                        $db = getDb();
                        //SELECT
                        $s = $db->query("select * from tbevent order by evdate $orderSQL $pagenation;");

                        while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                            //print_r ($row);

                    ?>
                            <tr class="event-row"
                                data-evwcode="<?= htmlspecialchars($row['evwcode']) ?>">

                                <td class="two_em"><?= e($row['evwcode']) ?></td>
                                <td><?= e($row['evdate']) ?></td>
                                <td><?= $row['evtype'] == 4 ? '<img src="handshake_28dp_8C1AF6_FILL0_wght400_GRAD0_opsz24.svg" class="evtype_icon">' : '' ?>
                                    <?= e($row['evtitle']) ?></td>

                            </tr>

                    <?php

                        }
                    } catch (PDOException $e) {
                        die("Error:{$e->getMessage()}");
                    }
                    ?>
                </tbody>
            </table>
            <img src="handshake_28dp_8C1AF6_FILL0_wght400_GRAD0_opsz24.svg" class="evtype_icon">：リレー
        </div><!-- close tableoutline -->


    <?php
}
