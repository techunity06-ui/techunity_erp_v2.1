-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2021 at 11:14 AM
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
-- Table structure for table `tbl_proforma_invoice`
--

CREATE TABLE IF NOT EXISTS `tbl_proforma_invoice` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(200) NOT NULL,
  `invoice_date` date NOT NULL,
  `challan_no` varchar(100) NOT NULL,
  `challan_date` date NOT NULL,
  `sales_order_id` int(11) NOT NULL,
  `order_no` varchar(250) NOT NULL,
  `order_date` date NOT NULL,
  `num_of_parcel` varchar(100) NOT NULL,
  `dispatch_doc_no` text NOT NULL,
  `dispatch_date` datetime NOT NULL,
  `dispatch_by` varchar(100) NOT NULL,
  `vehicle_no` varchar(200) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `payment_terms` varchar(50) NOT NULL,
  `docket_no` varchar(400) NOT NULL,
  `packing_boxes` varchar(400) NOT NULL,
  `total_weight` varchar(400) NOT NULL,
  `cust_id` int(11) NOT NULL,
  `consignee_id` int(11) NOT NULL,
  `packing` double(10,2) NOT NULL,
  `cutting` double(10,2) NOT NULL,
  `freight` double(10,2) NOT NULL,
  `discount` double(10,2) NOT NULL,
  `discount_per` double(6,2) NOT NULL,
  `formulaid` int(11) NOT NULL,
  `tax1_name` varchar(50) NOT NULL,
  `tax2_name` varchar(50) NOT NULL,
  `tax3_name` varchar(50) NOT NULL,
  `taxvalue1` double(10,2) NOT NULL,
  `taxvalue2` double(10,2) NOT NULL,
  `taxvalue3` double(10,2) NOT NULL,
  `round_off` double NOT NULL,
  `g_total` bigint(20) NOT NULL,
  `paid_amount` bigint(20) NOT NULL,
  `remark` text NOT NULL,
  `reverse_charge` int(11) NOT NULL,
  `gst_flag` int(11) NOT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `invoice_status` int(11) NOT NULL,
  `print_status` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `usertype_id` int(11) NOT NULL,
  `invoicetype_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`invoice_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
