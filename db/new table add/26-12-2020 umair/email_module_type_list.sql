-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2020 at 10:14 AM
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
-- Table structure for table `email_module_type_list`
--

CREATE TABLE IF NOT EXISTS `email_module_type_list` (
  `email_module_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `email_template_name` varchar(255) NOT NULL,
  `status` int(11) NOT NULL COMMENT '0=active,1=in-active,2=delete',
  `user_id` int(11) NOT NULL,
  `cdate` datetime NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`email_module_type_id`),
  KEY `module_id` (`module_id`,`email_template_name`,`status`,`user_id`,`company_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `email_module_type_list`
--

INSERT INTO `email_module_type_list` (`email_module_type_id`, `module_id`, `email_template_name`, `status`, `user_id`, `cdate`, `company_id`) VALUES
(1, 1, 'COMPANY INTRODUCTION EMAIL WHERE WE WILL SHARE OUR ERP FLOW DIAGRAM WITH BROUCHER', 0, 1, '2020-12-26 13:24:43', 1),
(2, 1, 'INQUIRY FOLLOWUP EMAIL', 0, 1, '2020-12-26 13:24:53', 1),
(3, 1, 'DEMO SCHEDULE EMAIL', 0, 1, '2020-12-26 13:25:02', 1),
(4, 1, 'THANK YOU EMAIL AFTER COMPLETING OF DEMO', 0, 1, '2020-12-26 13:25:20', 1),
(5, 1, 'APPOINTMENT SCHEDULE EMAIL ( FOR PRODUCT DETAILING)', 0, 1, '2020-12-26 13:25:27', 1),
(6, 1, 'QUOTATION EMAIL ( I HAVE SHARED OUR EXISTING EMAIL CONTENT BELOW AFTER THE LIST)', 0, 1, '2020-12-26 13:25:40', 1),
(7, 1, 'QUOTATION FOLLOWUP EMAIL', 0, 1, '2020-12-26 13:25:55', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
