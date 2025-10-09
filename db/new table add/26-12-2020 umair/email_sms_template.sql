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
-- Table structure for table `email_sms_template`
--

CREATE TABLE IF NOT EXISTS `email_sms_template` (
  `email_sms_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_module_id` int(11) NOT NULL COMMENT 'email_module_list ref id',
  `email_module_type_id` int(11) NOT NULL COMMENT 'email_module_type_list ref id',
  `template_title` varchar(255) NOT NULL,
  `email_content` longtext NOT NULL,
  `sms_content` text NOT NULL,
  `status` int(11) NOT NULL COMMENT '0=active,1=in-active,2=delete',
  `user_id` int(11) NOT NULL,
  `cdate` datetime NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`email_sms_id`),
  KEY `email_module_type_id` (`email_module_type_id`,`template_title`,`status`,`user_id`,`company_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `email_sms_template`
--

INSERT INTO `email_sms_template` (`email_sms_id`, `email_module_id`, `email_module_type_id`, `template_title`, `email_content`, `sms_content`, `status`, `user_id`, `cdate`, `company_id`) VALUES
(1, 1, 1, 'COMPANY INTRODUCTION EMAIL WHERE WE WILL SHARE OUR ERP FLOW DIAGRAM WITH BROUCHER', '<p>Dear : [NAME]</p><p>I would like to know you. We have started the new ERP system.</p><p>Please check my users [CUSTOMER_NAME] account.</p>', '<p>Dear : [NAME]</p><p>I would like to know you. We have started the new ERP system.</p><p>Please check my users [CUSTOMER_NAME] account.</p>', 0, 1, '2020-12-26 13:26:33', 1),
(2, 1, 2, 'INQUIRY FOLLOWUP EMAIL', '<p>Dear : [NAME]</p><p>I will send the details of your inquiry.</p>', '<p>Dear : [NAME]</p><p>I will send the details of your inquiry.</p>', 0, 1, '2020-12-26 13:27:26', 1),
(3, 1, 3, 'DEMO SCHEDULE EMAIL', '<p>Dear : [NAME]</p><p>I would like to know you. We have started the new ERP system.</p><p>Please check my users [CUSTOMER_NAME] account.</p>', '<p>Dear : [NAME]</p><p>I would like to know you. We have started the new ERP system.</p><p>Please check my users [CUSTOMER_NAME] account.</p>', 0, 1, '2020-12-26 13:27:47', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
