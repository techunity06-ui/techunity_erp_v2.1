-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2020 at 11:44 AM
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
-- Table structure for table `tbl_purchaseorder_aprv_log`
--

CREATE TABLE IF NOT EXISTS `tbl_purchaseorder_aprv_log` (
  `purchaseorder_aprv_id` int(11) NOT NULL AUTO_INCREMENT,
  `purchaseorder_id` int(11) NOT NULL,
  `assign_user_ids` int(11) NOT NULL,
  `approve_remark` text NOT NULL,
  `approve_status` int(11) NOT NULL COMMENT '1=Approved,0=Disapprove',
  `cdate` timestamp NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`purchaseorder_aprv_id`),
  KEY `purchaseorder_id` (`purchaseorder_id`,`assign_user_ids`,`approve_status`,`user_id`,`company_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `tbl_purchaseorder_aprv_log`
--

INSERT INTO `tbl_purchaseorder_aprv_log` (`purchaseorder_aprv_id`, `purchaseorder_id`, `assign_user_ids`, `approve_remark`, `approve_status`, `cdate`, `user_id`, `company_id`) VALUES
(1, 3, 0, 'TEST', 1, '2020-12-08 10:28:13', 1, 1),
(2, 3, 0, 'delete', 0, '2020-12-08 10:28:24', 1, 1),
(3, 3, 0, 'DONE', 1, '2020-12-08 10:29:07', 1, 1),
(4, 1, 0, 'APPROVE', 1, '2020-12-08 10:29:20', 1, 1),
(5, 1, 0, 'asa', 0, '2020-12-08 10:29:28', 1, 1),
(6, 2, 0, 'test', 0, '2020-12-08 10:34:30', 1, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
