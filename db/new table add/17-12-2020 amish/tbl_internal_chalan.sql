-- phpMyAdmin SQL Dump
-- version 4.5.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2020 at 11:34 AM
-- Server version: 5.7.11
-- PHP Version: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brp_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_internal_chalan`
--

CREATE TABLE `tbl_internal_chalan` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `int_chalan_no` varchar(400) NOT NULL,
  `sp_id` int(11) NOT NULL,
  `sp_name` varchar(255) NOT NULL,
  `req_qty` varchar(100) NOT NULL,
  `total_qty` varchar(100) DEFAULT NULL,
  `received_qty` varchar(100) DEFAULT NULL,
  `return_qty` varchar(100) DEFAULT NULL,
  `status` enum('sent','receive') DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_internal_chalan`
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_internal_chalan`
--
ALTER TABLE `tbl_internal_chalan`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_internal_chalan`
--
ALTER TABLE `tbl_internal_chalan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
