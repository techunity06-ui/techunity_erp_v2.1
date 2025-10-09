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
-- Table structure for table `tbl_make`
--

CREATE TABLE IF NOT EXISTS `tbl_make` (
  `make_id` int(11) NOT NULL AUTO_INCREMENT,
  `make_name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `muser_id` int(11) NOT NULL,
  `cdate` timestamp NOT NULL,
  `mdate` timestamp NOT NULL,
  `make_status` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`make_id`),
  KEY `revision_number` (`make_name`,`user_id`,`muser_id`,`make_status`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
