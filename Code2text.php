<?php

$evmediaCodeMx = [
    1 => 'YouTubeLive',
    3 => 'YouTubeLive(非公開)',
    5 => 'TwitCastingLive',
    10 => 'YouTubeVideo',
];

$evtypeCodeMx = [
    1 => '自配信',
    2 => '相手配信',
    3 => '主催配信',
    4 => '自配信（リレー）'
];




//
//配信コードは上に追加できます（入力時に有効性のチェックをしていません:-()
//10以上はビデオ（ordrlist.phpで利用）
//タイムスタンプリンク表示有効なコード1,10はハードコーディングしています。増やす場合はsetlist.php shistory.phpの修正が必要

function evmediaC2t($codenum)
{

    global $evmediaCodeMx;

    if (array_key_exists($codenum, $evmediaCodeMx) === True) {
        return $evmediaCodeMx[$codenum];
    } else {
        return 'undefined';
    }
}
function evtypeC2t($codenum)
{
    global $evtypeCodeMx;
    //配信種別はグローバル化作業途中…


    if (array_key_exists($codenum, $evtypeCodeMx) === True) {
        return $evtypeCodeMx[$codenum];
    } else {
        return 'undefined';
    }
}
