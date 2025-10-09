-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2021 at 10:59 AM
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
-- Table structure for table `coro_cheques`
--

CREATE TABLE IF NOT EXISTS `coro_cheques` (
  `cheque_id` double NOT NULL AUTO_INCREMENT,
  `cheque_number` double NOT NULL,
  `cheque_acc` double NOT NULL,
  `cheque_date` date NOT NULL,
  `cheque_payee` double NOT NULL,
  `cheque_amount` float NOT NULL,
  `cheque_note` varchar(500) NOT NULL,
  `cheque_mode` int(11) NOT NULL,
  `cheque_morethen` tinyint(4) NOT NULL,
  `cheque_iscancel` tinyint(4) NOT NULL DEFAULT '0',
  `cheque_tmst` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paytype` varchar(250) NOT NULL,
  `cheque_of` double NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`cheque_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `coro_cheques`
--

INSERT INTO `coro_cheques` (`cheque_id`, `cheque_number`, `cheque_acc`, `cheque_date`, `cheque_payee`, `cheque_amount`, `cheque_note`, `cheque_mode`, `cheque_morethen`, `cheque_iscancel`, `cheque_tmst`, `paytype`, `cheque_of`, `company_id`) VALUES
(1, 22, 22, '2020-07-13', 3, 5000, '', 1, 1, 0, '2020-07-13 12:14:01', 'Ven', 4, 0),
(2, 25, 22, '2020-08-29', 3, 7000, '', 3, 1, 0, '2020-08-29 09:08:25', 'Ven', 4, 0),
(3, 28, 22, '2020-09-11', 3, 15576, '', 3, 1, 0, '2020-09-11 13:27:33', 'Ven', 4, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
