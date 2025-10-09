-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2020 at 06:23 AM
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
-- Table structure for table `tbl_resource_allocation_transfer`
--

CREATE TABLE IF NOT EXISTS `tbl_resource_allocation_transfer` (
  `resourse_transfer_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id_by` int(11) NOT NULL COMMENT 'who is transfering',
  `resource_id_to` int(11) NOT NULL COMMENT 'to whom',
  `resource_transfer_number` varchar(100) NOT NULL,
  `resource_transfer_date` timestamp NOT NULL,
  `resource_transfer_allocate_id` int(11) NOT NULL COMMENT 'tbl_work_order_resource_allocate ref id',
  `product_id` int(11) NOT NULL,
  `process_id` int(11) NOT NULL COMMENT 'process_mst ref id ref id',
  `work_order_id` int(11) NOT NULL,
  `qty` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cdate` timestamp NOT NULL,
  `muser_id` int(11) NOT NULL,
  `mdate` timestamp NOT NULL,
  `auser_id` int(11) NOT NULL,
  `adate` timestamp NOT NULL,
  `company_id` int(11) NOT NULL,
  `resourse_allocation_transfer_status` int(11) NOT NULL,
  PRIMARY KEY (`resourse_transfer_id`),
  KEY `resource_id_by` (`resource_id_by`,`resource_id_to`,`resource_transfer_number`,`resource_transfer_allocate_id`,`product_id`,`process_id`,`work_order_id`,`qty`,`user_id`,`muser_id`,`auser_id`,`company_id`,`resourse_allocation_transfer_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `tbl_resource_allocation_transfer`
--

INSERT INTO `tbl_resource_allocation_transfer` (`resourse_transfer_id`, `resource_id_by`, `resource_id_to`, `resource_transfer_number`, `resource_transfer_date`, `resource_transfer_allocate_id`, `product_id`, `process_id`, `work_order_id`, `qty`, `user_id`, `cdate`, `muser_id`, `mdate`, `auser_id`, `adate`, `company_id`, `resourse_allocation_transfer_status`) VALUES
(1, 1, 2, '459960', '2020-11-10 10:46:15', 1, 362, 10, 1, '2', 1, '2020-11-10 10:46:15', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, 0),
(2, 5, 1, '930148', '2020-11-10 11:01:27', 19, 43, 26, 1, '2', 1, '2020-11-10 11:01:27', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, 0),
(3, 5, 1, '546576', '2020-11-10 11:05:03', 19, 43, 26, 1, '2', 1, '2020-11-10 11:05:03', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, 0),
(4, 5, 1, '993381', '2020-11-10 11:06:52', 19, 43, 26, 1, '2', 1, '2020-11-10 11:06:52', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, 0),
(5, 5, 1, '753228', '2020-11-10 11:23:37', 14, 43, 26, 1, '2', 1, '2020-11-10 11:23:37', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
