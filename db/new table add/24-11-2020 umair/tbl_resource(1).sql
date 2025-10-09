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
-- Table structure for table `tbl_resource`
--

CREATE TABLE IF NOT EXISTS `tbl_resource` (
  `resource_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_name` varchar(200) DEFAULT NULL,
  `working_hours` varchar(100) NOT NULL DEFAULT '0',
  `hours_cost` varchar(200) NOT NULL DEFAULT '0',
  `resource_value` varchar(255) DEFAULT NULL,
  `maintance_period` varchar(10) DEFAULT '0',
  `ledger_id` int(11) NOT NULL COMMENT 'ledger table ref',
  `loggin_id` int(11) NOT NULL COMMENT 'user table ref',
  `employee_id` int(11) NOT NULL COMMENT 'employee table ref',
  `user_id` int(11) NOT NULL,
  `muser_id` int(11) NOT NULL COMMENT 'modified user',
  `auser_id` int(11) NOT NULL COMMENT 'approval user',
  `cdate` timestamp NOT NULL,
  `mdate` timestamp NOT NULL,
  `adate` timestamp NOT NULL,
  `resource_status` int(11) NOT NULL COMMENT '0=active,3=delete',
  `company_id` int(11) NOT NULL,
  `remark` text NOT NULL,
  PRIMARY KEY (`resource_id`),
  KEY `resource_name` (`resource_name`,`working_hours`,`hours_cost`,`resource_value`,`maintance_period`,`ledger_id`,`loggin_id`,`employee_id`,`user_id`,`muser_id`,`auser_id`,`company_id`),
  KEY `resource_status` (`resource_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `tbl_resource`
--

INSERT INTO `tbl_resource` (`resource_id`, `resource_name`, `working_hours`, `hours_cost`, `resource_value`, `maintance_period`, `ledger_id`, `loggin_id`, `employee_id`, `user_id`, `muser_id`, `auser_id`, `cdate`, `mdate`, `adate`, `resource_status`, `company_id`, `remark`) VALUES
(1, 'CNC MACHINE', '4', '100', 'CUTTING', '1000', 1849, 95, 74, 1, 1, 1, '2020-11-03 12:33:13', '2020-11-08 18:30:00', '2020-11-03 12:33:13', 0, 1, 'TEST MY DATA'),
(2, 'VMC MACHINE', '8', '100', 'MANUFACTURING', '700', 1849, 95, 74, 1, 1, 1, '2020-11-03 12:41:30', '2020-11-05 18:30:00', '2020-11-03 12:41:30', 0, 1, ''),
(3, 'LATH MACHINE', '10', '10', 'LATH', '10', 13, 22, 0, 1, 1, 0, '2020-11-03 18:30:00', '2020-11-09 18:30:00', '0000-00-00 00:00:00', 0, 1, '0'),
(4, 'DRILLING MACHINE', '8', '50.90', 'DRILLING', '60', 14, 23, 0, 1, 1, 0, '2020-11-03 18:30:00', '2020-11-09 18:30:00', '0000-00-00 00:00:00', 0, 1, '0'),
(5, 'MACHINE', '8', '12', 'MACHINE PART', '100', 0, 0, 0, 1, 0, 0, '2020-11-09 18:30:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 1, '0');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
