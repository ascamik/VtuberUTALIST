-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost
-- 生成日時: 2025 年 7 月 08 日 11:08
-- サーバのバージョン： 10.11.11-MariaDB-log
-- PHP のバージョン: 8.2.28

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
-- テーブルの構造 `tbevent`
--

CREATE TABLE `tbevent` (
  `evwcode` varchar(4) NOT NULL,
  `evdate` date DEFAULT NULL,
  `evtitle` varchar(255) DEFAULT NULL,
  `evurl` varchar(255) DEFAULT NULL,
  `evmedia` int(11) DEFAULT NULL,
  `evtype` int(11) DEFAULT NULL,
  `evdesc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- テーブルの構造 `tbsong`
--

CREATE TABLE `tbsong` (
  `songid` int(11) NOT NULL,
  `arrng` int(11) NOT NULL,
  `sname` varchar(255) DEFAULT NULL,
  `yomi` varchar(255) DEFAULT NULL,
  `artist` varchar(100) DEFAULT NULL,
  `tieup` varchar(100) DEFAULT NULL,
  `vocap` varchar(20) DEFAULT NULL,
  `genre` varchar(2) DEFAULT NULL,
  `relsd` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- テーブルの構造 `tbvocal`
--

CREATE TABLE `tbvocal` (
  `evwcode` varchar(4) NOT NULL,
  `seqnum` int(11) NOT NULL,
  `songid` int(11) DEFAULT NULL,
  `arrng` int(11) DEFAULT NULL,
  `time` varchar(8) DEFAULT NULL,
  `memo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `tbevent`
--
ALTER TABLE `tbevent`
  ADD PRIMARY KEY (`evwcode`);

--
-- テーブルのインデックス `tbsong`
--
ALTER TABLE `tbsong`
  ADD PRIMARY KEY (`songid`,`arrng`) USING BTREE,
  ADD KEY `yomi` (`yomi`);

--
-- テーブルのインデックス `tbvocal`
--
ALTER TABLE `tbvocal`
  ADD PRIMARY KEY (`evwcode`,`seqnum`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
