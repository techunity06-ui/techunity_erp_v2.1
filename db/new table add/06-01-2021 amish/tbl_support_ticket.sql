-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2021 at 12:38 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bigdatas_umaboy_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_support_ticket`
--

CREATE TABLE IF NOT EXISTS `tbl_support_ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department` varchar(255) NOT NULL,
  `page_link` text NOT NULL,
  `description` text NOT NULL,
  `upload_document` text,
  `change_user` varchar(100) DEFAULT NULL,
  `change_comment` text,
  `support_status_id` int(11) NOT NULL DEFAULT '1',
  `due_date` date DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0=''Active'', 1=''Inactive'', 2=''Deleted''',
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
