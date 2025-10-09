-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 13, 2021 at 10:39 AM
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
-- Table structure for table `tbl_payment_cheque_generate`
--

CREATE TABLE IF NOT EXISTS `tbl_payment_cheque_generate` (
  `chequegenerateid` double NOT NULL AUTO_INCREMENT,
  `purchase_payid` double NOT NULL,
  `acc_id` double NOT NULL,
  `amount` double NOT NULL,
  `cheque_date` date NOT NULL,
  `cheque_num` varchar(100) NOT NULL,
  `cheque_id` int(11) NOT NULL,
  `vender_id` double NOT NULL,
  `tansactionid` int(11) NOT NULL,
  `directentryid` int(11) NOT NULL,
  `generat_status` int(11) NOT NULL COMMENT '0:genration peding,1:generated,2:deleted',
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`chequegenerateid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
