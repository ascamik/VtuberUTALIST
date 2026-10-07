-- v2以前の環境からバージョンアップする場合
-- このファイルをあなたが使っている歌リストのデータベースにインポートしてください
-- 複数Vtuberを管理するためのテーブルが追加されます
-- タグ管理用のテーブルも同時に作成されますが、現時点ではこの機能は開発されていません
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost
-- 生成日時: 2026 年 10 月 05 日 08:41
-- サーバのバージョン： 11.8.5-MariaDB-log
-- PHP のバージョン: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `vtsldb`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `tbsongtag`
--

CREATE TABLE `tbsongtag` (
  `songid` int(11) NOT NULL,
  `arrng` int(11) NOT NULL DEFAULT 0,
  `tagid` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- テーブルの構造 `tbtag`
--

CREATE TABLE `tbtag` (
  `tagid` int(11) NOT NULL,
  `tagname` varchar(100) NOT NULL,
  `tagrank` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- テーブルの構造 `tbvtuber`
--

CREATE TABLE `tbvtuber` (
  `vtcode` int(11) NOT NULL,
  `vtname` varchar(32) NOT NULL,
  `flag` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `tbsongtag`
--
ALTER TABLE `tbsongtag`
  ADD PRIMARY KEY (`songid`,`arrng`,`tagid`),
  ADD UNIQUE KEY `songid` (`songid`);

--
-- テーブルのインデックス `tbtag`
--
ALTER TABLE `tbtag`
  ADD PRIMARY KEY (`tagid`),
  ADD UNIQUE KEY `tagid` (`tagid`),
  ADD KEY `tagname` (`tagname`);

--
-- テーブルのインデックス `tbvtuber`
--
ALTER TABLE `tbvtuber`
  ADD PRIMARY KEY (`vtcode`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `tbtag`
--
ALTER TABLE `tbtag`
  MODIFY `tagid` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `tbvtuber`
--
ALTER TABLE `tbvtuber`
  MODIFY `vtcode` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
