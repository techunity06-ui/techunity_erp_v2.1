-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2021 at 11:37 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `billing360_v4_db1`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_proforma_trntemp`
--

CREATE TABLE IF NOT EXISTS `tbl_proforma_trntemp` (
  `tempinvoicetrn_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `product_hsn_code` varchar(200) NOT NULL,
  `start_serial1` bigint(20) NOT NULL,
  `end_serial1` bigint(20) NOT NULL,
  `start_serial2` bigint(20) NOT NULL,
  `end_serial2` bigint(20) NOT NULL,
  `start_serial3` bigint(20) NOT NULL,
  `end_serial3` bigint(20) NOT NULL,
  `product_qty` double(10,2) NOT NULL,
  `sqr_ft` double(12,2) NOT NULL,
  `product_rate` double(10,2) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `product_disc` double(5,2) NOT NULL,
  `product_amount` double(10,2) NOT NULL,
  `product_discount` double(10,2) NOT NULL,
  `discount_per` double(10,2) NOT NULL,
  `formulaid` int(11) NOT NULL,
  `tax_name1` varchar(200) NOT NULL,
  `tax_amount1` double(10,2) NOT NULL,
  `tax_name2` varchar(200) NOT NULL,
  `tax_amount2` double(10,2) NOT NULL,
  `tax_name3` varchar(200) NOT NULL,
  `tax_amount3` double(10,2) NOT NULL,
  `total` double(10,2) NOT NULL,
  `temp_status` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`tempinvoicetrn_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
