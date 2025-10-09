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
-- Table structure for table `tbl_drawing`
--

CREATE TABLE IF NOT EXISTS `tbl_drawing` (
  `drawing_id` int(11) NOT NULL AUTO_INCREMENT,
  `drawing_number` varchar(100) NOT NULL,
  `drawing_title` varchar(255) NOT NULL,
  `drawing_size` varchar(255) NOT NULL,
  `drawing_scale` varchar(255) NOT NULL,
  `drawing_location` text NOT NULL,
  `vender_id` int(11) NOT NULL,
  `sales_order_id` int(11) NOT NULL,
  `remark` text NOT NULL,
  `drawing_status` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `muser_id` int(11) NOT NULL,
  `auser_id` int(11) NOT NULL,
  `cdate` date NOT NULL,
  `mdate` timestamp NOT NULL,
  `adate` timestamp NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`drawing_id`),
  KEY `drawing_number` (`drawing_number`,`drawing_title`,`drawing_size`,`drawing_scale`,`vender_id`,`sales_order_id`,`drawing_status`,`user_id`,`muser_id`,`auser_id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
