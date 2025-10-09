-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2021 at 09:27 AM
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
-- Table structure for table `tbl_jobwork_main`
--

CREATE TABLE IF NOT EXISTS `tbl_jobwork_main` (
  `jobwork_main_id` int(11) NOT NULL AUTO_INCREMENT,
  `jobwork_no` varchar(100) NOT NULL,
  `jobwork_date` date NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `vehicle_no` varchar(255) NOT NULL,
  `remark` text NOT NULL,
  `jobwork_status` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cdate` datetime NOT NULL,
  `muser_id` int(11) NOT NULL,
  `mdate` datetime NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`jobwork_main_id`),
  KEY `jobwork_no` (`jobwork_no`,`vendor_id`,`vehicle_no`,`jobwork_status`,`user_id`,`muser_id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
