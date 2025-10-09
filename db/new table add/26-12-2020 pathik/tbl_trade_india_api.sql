-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2020 at 10:12 AM
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
-- Table structure for table `tbl_trade_india_api`
--

CREATE TABLE IF NOT EXISTS `tbl_trade_india_api` (
  `i_id` int(11) NOT NULL AUTO_INCREMENT,
  `trade_india_user_id` varchar(100) NOT NULL,
  `trade_india_profile_id` varchar(100) NOT NULL,
  `trad_india_api_key` varchar(100) NOT NULL,
  `i_status` int(11) NOT NULL,
  `source_id` int(11) NOT NULL,
  PRIMARY KEY (`i_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `tbl_trade_india_api`
--

INSERT INTO `tbl_trade_india_api` (`i_id`, `trade_india_user_id`, `trade_india_profile_id`, `trad_india_api_key`, `i_status`, `source_id`) VALUES
(1, '4923274', '6599838', 'e951dfff5339af725e94f5fe35d23783', 0, 11);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
