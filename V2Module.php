<?php
require_once 'configV2.php';
require_once 'DbMa.php';
require_once 'Encode.php';

function getVtInfo($vtcode = 'GET')
{
    global $defaultVtuber;
    global $enableAllVselect;
    // if (!in_array($defaultVtuber, [1, 'A'])) { //Limit A or 1
    //     $defaultVtuber = 1;
    // }
    if ($vtcode === 'GET') {
        $vtcode = trim($_GET['vtcode'] ?? $defaultVtuber);
    }

    if ($vtcode === 'A') {
        if ($enableAllVselect) {

            return ['', '全表示'];
        } else {
            $vtcode = '1';
        }
    }
    $vtcode = intval($vtcode); //Sanitization process
    try {
        $db = getDb();
        $s = $db->query("select vtcode,vtname from tbvtuber;");
        // FETCH_KEY_PAIR を指定してフェッチ
        $vtListarray = $s->fetchAll(PDO::FETCH_KEY_PAIR);
        if (array_key_exists($vtcode, $vtListarray)) {
            return [$vtcode, $vtListarray[$vtcode]];
        } else {
            return [1, ($vtListarray[1] ?? '!undef')];
        }
    } catch (PDOException $e) {
        die("Error:{$e->getMessage()}");
    }
}

function getVtUrlQuery()
{
    $vtcode = getVtInfo(); //from $_GET['vtcode']

    if ($vtcode[0]) {
        $UrlQuery = "vtcode={$vtcode[0]}";
    } else {
        $UrlQuery = 'vtcode=A';
    }
    return  $UrlQuery;
}

function putHtmlVtSelectListNavi()
{
    global $enableAllVselect;

    $vtcode = getVtInfo();
    if ($vtcode[0] == "") {
        $getvtc = "A";
        $selA = 'selected';
    } else {
        $getvtc = $vtcode[0];
        $selA = '';
    }
    $html = '';
    $count = 0;

    try {
        $db = getDb();
        //SELECT
        $s = $db->query("select vtcode, vtname from tbvtuber;");

        while ($row = $s->fetch(PDO::FETCH_ASSOC)) {
            $count++;
            $vtc = intval($row['vtcode']);
            if ($getvtc == $vtc) {
                $sel = 'selected';
            } else {
                $sel = "";
            }
            $html .= "<option value=\"{$vtc}\" {$sel}>" . e($row['vtname']) . "</option>";
        }
    } catch (PDOException $e) {
        die("Error:{$e->getMessage()}");
    }

    if ($count >= 2) {
        print "<div class=\"v2navilink adminnone\"><select id=\"vtSelect\">
<option value=\"\">選択してください</option>";
        if ($enableAllVselect) {
            print "<option value=\"A\" {$selA}>全表示</option>";
        }
        print $html;
        print "</select></div>";
    }
}
