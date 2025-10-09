-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 13, 2021 at 10:44 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: bigdatas_umaboy_erp
--

-- --------------------------------------------------------

--
-- Table structure for table `coro_vouchers`
--

CREATE TABLE IF NOT EXISTS `coro_vouchers` (
  `v_id` double NOT NULL AUTO_INCREMENT,
  `v_cheque` double NOT NULL,
  `v_tds` float NOT NULL,
  `v_rec_name` varchar(50) NOT NULL,
  `v_rec_mobno` varchar(20) NOT NULL,
  `v_tmst` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `v_of` double NOT NULL,
  PRIMARY KEY (`v_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
