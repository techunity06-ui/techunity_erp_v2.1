-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2020 at 10:15 AM
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
-- Table structure for table `email_merge_fields`
--

CREATE TABLE IF NOT EXISTS `email_merge_fields` (
  `email_merge_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` varchar(255) NOT NULL,
  `field_value` varchar(255) NOT NULL,
  `status` int(11) NOT NULL COMMENT '0=active,1=in-active,2=delete',
  `user_id` int(11) NOT NULL,
  `cdate` datetime NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`email_merge_id`),
  KEY `name` (`field_name`,`field_value`,`status`,`user_id`,`company_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `email_merge_fields`
--

INSERT INTO `email_merge_fields` (`email_merge_id`, `field_name`, `field_value`, `status`, `user_id`, `cdate`, `company_id`) VALUES
(1, 'NAME', 'name', 0, 1, '2020-12-26 11:06:20', 1),
(2, 'CUSTOMER NAME', 'customer_name', 0, 1, '2020-12-26 11:08:04', 1),
(3, 'CONTACT FIRST NAME', 'contact_first_name', 0, 1, '2020-12-26 11:07:06', 1),
(4, 'CONTACT LAST NAME', 'contact_last_name', 0, 1, '2020-12-26 11:07:24', 1),
(5, 'CUSTOMER EMAIL', 'customer_email', 0, 1, '2020-12-26 11:07:56', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
