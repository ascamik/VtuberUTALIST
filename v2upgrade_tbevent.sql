-- v2以前の環境からバージョンアップした場合
-- このファイルをテーブルtbeventにインポートしてください
-- イベント情報に複数Vtuberを管理するためのカラムが追加されます
-- 既存のイベントはすべて最初に登録したコード番号1のVTuberに設定されます
ALTER TABLE `tbevent` ADD `vtcode` INT NOT NULL DEFAULT '1' AFTER `evdesc`;
