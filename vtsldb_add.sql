-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost
-- 生成日時: 2026 年 6 月 23 日 14:39
-- サーバのバージョン： 11.8.5-MariaDB-log
-- PHP のバージョン: 8.3.29

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
-- テーブルの構造 `tbvodraft`
--

CREATE TABLE `tbvodraft` (
  `drafttype` varchar(4) NOT NULL,
  `evwcode` varchar(8) NOT NULL,
  `seqnum` int(11) NOT NULL,
  `songid` int(11) DEFAULT NULL,
  `arrng` int(11) DEFAULT NULL,
  `time` varchar(8) DEFAULT NULL,
  `memo` varchar(255) DEFAULT NULL,
  `comment` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `tbvodraft`
--
ALTER TABLE `tbvodraft`
  ADD PRIMARY KEY (`drafttype`,`evwcode`,`seqnum`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
