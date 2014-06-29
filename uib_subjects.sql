SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `uib_subjects` (
  `id` varchar(16) NOT NULL,
  `studiepoeng` decimal(5,1) NOT NULL,
  `title_en` varchar(256) NOT NULL,
  `title_no` varchar(256) NOT NULL,
  `eb_innhold` text,
  `eb_utbytte` text,
  `eb_undsem` varchar(64) DEFAULT NULL,
  `eb_ekssem` varchar(64) DEFAULT NULL,
  `eb_niva` varchar(32) DEFAULT NULL,
  `eb_institu` varchar(512) DEFAULT NULL,
  `eb_krav` varchar(1024) DEFAULT NULL,
  `eb_anbkrav` varchar(1024) DEFAULT NULL,
  `eb_fagovl` varchar(1024) DEFAULT NULL,
  KEY `title_no` (`title_no`),
  KEY `id` (`id`),
  FULLTEXT KEY `eb_innhold_13` (`eb_innhold`,`eb_utbytte`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
