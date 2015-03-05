SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `uib_studyprogrammes` (
  `id` varchar(32) NOT NULL,
  `category` varchar(64) NOT NULL,
  `nuskode` varchar(8) NOT NULL,
  `studiepoeng` decimal(5,1) NOT NULL,
  `title_no` varchar(128) NOT NULL,
  `title_en` varchar(128) NOT NULL,
  `b_re_intro` text,
  `b_re_krav` text,
  `b_re_plass` varchar(64) DEFAULT NULL,
  `b_re_utvek` text,
  `b_re_poeng` varchar(256) DEFAULT NULL,
  `b_re_visst` text,
  `b_re_yrkes` text,
  `sp_arblrel` text,
  `sp_innhold` text,
  `sp_utbytte` text,
  `pictureURL` varchar(256) DEFAULT NULL,
  `sp_obligat` text,
  `emner` varchar(512) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for table `uib_studyprogrammes`
--
ALTER TABLE `uib_studyprogrammes`
 ADD UNIQUE KEY `id_2` (`id`), ADD KEY `id` (`id`), ADD KEY `title_no` (`title_no`);