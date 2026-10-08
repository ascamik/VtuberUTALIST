<?php

//Vtuber1人を管理する場合はこのまま使用します。
//パラメータを付けずにサイトへアクセスしたときの挙動を決定します。デフォルトのVtuberの歌リストを表示するか、全部あわせたリストを表示するかです
//
$defaultVtuber = '1'; //デフォルトでコード1のVtuberを表示します
//$defaultVtuber = 'A'; //デフォルト表示を全表示に変える場合は、この行頭のコメントを外し、上の行をコメントアウトします
//
//以下の設定を変更する場合は注意してください
$enableAllVselect = true; //プルダウンやパラメータで'A'（全表示）を使用する場合true
//$enableAllVselect = false; //falseにすると禁止します $defaultVtuber='A' の設定がある場合は強制的に $defaultVtuber='1'とします