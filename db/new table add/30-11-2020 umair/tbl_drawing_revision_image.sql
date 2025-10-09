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
-- Table structure for table `tbl_drawing_revision_image`
--

CREATE TABLE IF NOT EXISTS `tbl_drawing_revision_image` (
  `drawing_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `drawing_id` int(11) NOT NULL COMMENT 'tbl_drawing ref id',
  `revision_id` int(11) NOT NULL COMMENT 'tbl_revision ref id',
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `muser_id` int(11) NOT NULL,
  `cdate` timestamp NOT NULL,
  `mdate` timestamp NOT NULL,
  `drawing_revision_status` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`drawing_image_id`),
  KEY `drawing_id` (`drawing_id`,`revision_id`,`file_name`,`file_path`,`user_id`,`muser_id`,`company_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `tbl_drawing_revision_image`
--

INSERT INTO `tbl_drawing_revision_image` (`drawing_image_id`, `drawing_id`, `revision_id`, `file_name`, `file_path`, `user_id`, `muser_id`, `cdate`, `mdate`, `drawing_revision_status`, `company_id`) VALUES
(1, 0, 1, '0_1_196.jpg', '../../view/upload/drawing_images/', 1, 0, '2020-11-30 04:56:00', '0000-00-00 00:00:00', 3, 1),
(2, 0, 1, '0_1_785.jpg', '../../view/upload/drawing_images/', 1, 0, '2020-11-30 04:56:00', '0000-00-00 00:00:00', 3, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
