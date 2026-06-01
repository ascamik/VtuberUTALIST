<?php
//曲リスト用のDBユーザとパスワードを設定します
$DBUSR = 'vsuser';
$DBPAS = 'vsuserpasswd';

//管理者ログインシステム用のDBユーザとパスワードを設定します
$PAUSR = 'phpauthuser';
$PAPAS = 'phpauthpasswd';

//管理者ID作成時の使用するkeyを設定します
//UUID作成ツールの使用をおすすめします
//uuidgen (linux)
//New-Guid (windows powershell) で表示された文字に''内を置き換える（置き換えずに使うと他人にログインをされる危険があります）
//設定した文字列は公開しないこと
$SENKEY = '663a6475-e4c4-4503-b294-51e4bc2764f8';
