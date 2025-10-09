-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Nov 30, 2020 at 05:58 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `brp_software`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_revision`
--

CREATE TABLE IF NOT EXISTS `tbl_revision` (
  `revision_id` int(11) NOT NULL AUTO_INCREMENT,
  `drawing_id` int(11) NOT NULL COMMENT 'tbl_drawing ref id',
  `revision_number` varchar(255) NOT NULL,
  `revision_date` date NOT NULL,
  `remark` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `muser_id` int(11) NOT NULL,
  `cdate` timestamp NOT NULL,
  `mdate` timestamp NOT NULL,
  `revision_status` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`revision_id`),
  KEY `revision_number` (`revision_number`,`user_id`,`muser_id`,`revision_status`),
  KEY `company_id` (`company_id`),
  KEY `drawing_id` (`drawing_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `tbl_revision`
--

INSERT INTO `tbl_revision` (`revision_id`, `drawing_id`, `revision_number`, `revision_date`, `remark`, `user_id`, `muser_id`, `cdate`, `mdate`, `revision_status`, `company_id`) VALUES
(1, 0, '001', '2020-11-30', '0', 1, 0, '2020-11-30 04:56:00', '0000-00-00 00:00:00', 3, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
