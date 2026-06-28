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
//
//配信コードは上に追加できます（入力時に有効性のチェックをしていません:-()
//10以上はビデオ（ordrlist.phpで利用）
//タイムスタンプリンク表示有効なコード1,10はハードコーディングしています。増やす場合はsetlist.php shistory.phpの修正が必要
//
//ジャンルの記号は以下で変更できます。記号は1文字にしてください
//もしジャンルを修正した場合ジャンル検索を利用している ordrlist.php ranking.php は修正が必要です
//日本語対象表 $genreCodeMxJP は、ordrlist.phpのセットリンク互換CSV出力で使用しています
//
$genreCodeMx = [
    "P" => "J-POP",
    "A" => "Animation song",
    "V" => "Vocalo song",
    "G" => "Game song",
    "o" => "Original song",
    "I" => "for The Idolmaster",
    "R" => "Rock",
];

$genreCodeMxJP = [
    'P' => 'JPOP',
    'A' => 'アニメ',
    'V' => 'ボカロ',
    'G' => 'ゲーム',
    'o' => 'オリ曲',
    'I' => 'アイマス',
    'R' => 'Rock',
];


//$genreCodes = implode('', array_keys($genreCodeMx));//PAVGoIR... strings for regex

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
function genreC2t($code, $lang = 'en')
{
    global $genreCodeMxJP, $genreCodeMx;
    //配信種別はグローバル化作業途中…
    switch ($lang) {

        case 'en':

            if (array_key_exists($code, $genreCodeMx) === True) {
                return $genreCodeMx[$code];
            } else {
                return 'undefined';
            }
            break;
        case 'jp':
            if (array_key_exists($code, $genreCodeMxJP) === True) {
                return $genreCodeMxJP[$code];
            } else {
                return 'undefined';
            }
            break;
        default:
            return 'undefined';
    }
}
