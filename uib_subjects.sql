SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `uib_subjects` (
  `id` varchar(16) NOT NULL,
  `studiepoeng` decimal(5,1) NOT NULL,
  `title_en` varchar(256) NOT NULL,
  `title_no` varchar(256) NOT NULL,
  `eb_innhold` text,
  `eb_utbytte` text,
  `eb_undsem` text,
  `eb_ekssem` text,
  `eb_niva` text,
  `eb_institu` text,
  `eb_krav` text,
  `eb_anbkrav` text,
  `eb_fagovl` text,
  `eb_obligat` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for table `uib_subjects`
--
ALTER TABLE `uib_subjects`
 ADD UNIQUE KEY `id_2` (`id`), ADD KEY `title_no` (`title_no`), ADD KEY `id` (`id`), ADD FULLTEXT KEY `eb_innhold_13` (`eb_innhold`,`eb_utbytte`);

