<?php


//$url1='https://www.youtube.com/live/XjC2cxRCq6k?feature=share';
//$url='https://youtu.be/6z_wtU_BMOM';
//$t1='21:29';
require_once 'Encode.php';

function makeTimestamplink($url, $t)
{


    if (preg_match("/(\d{0,2}?):{0,1}(\d{1,2}):(\d{2})\s*$/", $t, $match)) {
        $sec = (intval($match[1]) * 60 * 60 + intval($match[2]) * 60 + intval($match[3]));
    } else {
        $sec = 0;
    }

    $url = e($url);

    if (str_contains($url, '?')) {
        $ret = $url . '&t=' . strval($sec);
    } else {
        $ret = $url . '?t=' . strval($sec);
    }
    return $ret;
}

function timeStamp2Seconds($t)
{


    if (preg_match("/(\d{0,2}?):{0,1}(\d{1,2}):(\d{2})\s*$/", $t, $match)) {
        $sec = (intval($match[1]) * 60 * 60 + intval($match[2]) * 60 + intval($match[3]));
    } else {
        $sec = 0;
    }
    return $sec;
}

function videoIDfromYTurl($url)
{
    if (strpos($url, 'https://youtu.be/') !== false) {
        $videoID = str_replace(['https://youtu.be/'], [''], $url);
        $extstrLocate = strpos($videoID, '?si=');
        if ($extstrLocate !== false) {
            $videoID = substr($videoID, 0, $extstrLocate);
        }
        return $videoID;
    } elseif (strpos($url, 'www.youtube.com') !== false) {

        $videoID = str_replace(['?feature=share', 'https://www.youtube.com/watch?v=', 'https://www.youtube.com/live/'], ['', '', ''], $url);
        $extstrLocate = strpos($videoID, '?si=');
        if ($extstrLocate !== false) {
            $videoID = substr($videoID, 0, $extstrLocate);
        }
        return $videoID;
    } else {
        $videoID = "";
        return $videoID;
    }
}
