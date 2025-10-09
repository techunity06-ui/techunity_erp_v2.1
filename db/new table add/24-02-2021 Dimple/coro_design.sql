-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2021 at 10:56 AM
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
-- Table structure for table `coro_design`
--

CREATE TABLE IF NOT EXISTS `coro_design` (
  `design_id` double NOT NULL AUTO_INCREMENT,
  `design_bank` double NOT NULL,
  `design_payee` varchar(200) NOT NULL,
  `design_payeeWidth` float NOT NULL,
  `design_date` varchar(200) NOT NULL,
  `design_dateWidth` float NOT NULL,
  `design_dateLspace` float NOT NULL,
  `design_amount_text` varchar(200) NOT NULL,
  `design_amount_textWidth` float NOT NULL,
  `design_amount_textIndent` float NOT NULL,
  `design_amount_textLHeight` float NOT NULL,
  `design_amount_number` varchar(200) NOT NULL,
  `design_amount_numberWidth` float NOT NULL,
  `design_bearer` varchar(200) NOT NULL,
  `design_bearerWidth` float NOT NULL,
  `design_mark` varchar(100) NOT NULL,
  `design_notmore` varchar(100) NOT NULL,
  `design_notmoreWidth` float NOT NULL,
  `design_preview` varchar(500) NOT NULL,
  `design_status` tinyint(1) NOT NULL DEFAULT '1',
  `company_id` int(11) NOT NULL,
  `design_tmst` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `design_of` double NOT NULL,
  PRIMARY KEY (`design_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=53 ;

--
-- Dumping data for table `coro_design`
--

INSERT INTO `coro_design` (`design_id`, `design_bank`, `design_payee`, `design_payeeWidth`, `design_date`, `design_dateWidth`, `design_dateLspace`, `design_amount_text`, `design_amount_textWidth`, `design_amount_textIndent`, `design_amount_textLHeight`, `design_amount_number`, `design_amount_numberWidth`, `design_bearer`, `design_bearerWidth`, `design_mark`, `design_notmore`, `design_notmoreWidth`, `design_preview`, `design_status`, `company_id`, `design_tmst`, `design_of`) VALUES
(1, 2, '{"top":"78.9914779663086","left":"60.99431610107422"}', 422.182, '{"top":"33.99147415161133","left":"575.9801025390625"}', 138.182, 11, '{"top":"102.99715423583984","left":"20.980112075805664"}', 599.182, 80, 30, '{"top":"142.9971466064453","left":"583.991455078125"}', 108.182, '{"top":"84.98579406738281","left":"664.9857788085938"}', 46.1818, '{"top":"163.99147033691406","left":"286.9886169433594"}', '{"top":"225.9943084716797","left":"347.9971618652344"}', 199.182, 'bob-9008168689.jpg', 1, 0, '2015-02-07 15:54:30', 0),
(4, 4, '{"top":"83","left":"98"}', 515, '{"top":"30","left":"596"}', 136, 11, '{"top":"108","left":"22"}', 538, 80, 35, '{"top":"137","left":"605"}', 125, '{"top":"65","left":"650"}', 110, '{"top":"173","left":"605"}', '{"top":"186","left":"262"}', 240, 'icici-4708158136.jpg', 1, 0, '2015-02-07 16:01:41', 0),
(12, 7, '{"top":"79","left":"70"}', 450, '{"top":"38","left":"543"}', 158, 13, '{"top":"105","left":"44"}', 455, 76, 31, '{"top":"136","left":"562"}', 113, '{"top":"83","left":"643"}', 50, '{"top":"219","left":"257"}', '{"top":"225","left":"391"}', 181, 'cbi-294380415.jpg', 1, 0, '2016-01-25 04:38:59', 0),
(14, 9, '{"top":"82","left":"62"}', 526, '{"top":"30","left":"593"}', 155, 12, '{"top":"107","left":"56"}', 534, 72, 28, '{"top":"143","left":"595"}', 104, '{"top":"84","left":"631"}', 83, '{"top":"201","left":"375"}', '{"top":"186","left":"345"}', 178, 'canctscopy-1388119199.jpg', 1, 0, '2016-03-06 12:50:55', 0),
(15, 3, '{"top":"74","left":"56"}', 552, '{"top":"22","left":"585"}', 146, 12, '{"top":"96","left":"41"}', 534, 70, 32, '{"top":"136","left":"603"}', 104, '{"top":"80","left":"658"}', 42, '{"top":"208","left":"275"}', '{"top":"248","left":"255"}', 168, 'chaque2255393621-1214658089.jpg', 1, 0, '2016-03-26 05:26:15', 0),
(22, 8, '{"top":"101","left":"121"}', 54, '{"top":"67","left":"550"}', 90, 5, '{"top":"129","left":"74"}', 550, 40, 35, '{"top":"163","left":"514"}', 120, '{"top":"104","left":"615"}', 58, '{"top":"245","left":"360"}', '{"top":"227","left":"395"}', 184, 'kotakrameshbhaihuf1408473232-8711140588.jpg', 1, 0, '2016-12-03 10:25:57', 0),
(29, 15, '{"top":"74","left":"73"}', 522, '{"top":"40","left":"598"}', 142, 12, '{"top":"101","left":"91"}', 647, 40, 35, '{"top":"139","left":"642"}', 116, '{"top":"88","left":"689"}', 77, '{"top":"218","left":"132"}', '{"top":"218","left":"282"}', 180, 'thenavnirmancoopbankltd-395763292.jpg', 1, 0, '2017-03-25 21:07:30', 1),
(31, 16, '{"top":"92","left":"115"}', 421, '{"top":"39","left":"607"}', 150, 12, '{"top":"116","left":"29"}', 499, 80, 31, '{"top":"150","left":"621"}', 118, '{"top":"92","left":"699"}', 56, '{"top":"197","left":"259"}', '{"top":"243","left":"247"}', 182, 'maharashtrabankedited-1157640979.jpg', 1, 0, '2017-04-09 17:00:35', 0),
(32, 17, '{"top":"80","left":"75"}', 441, '{"top":"30","left":"588"}', 155, 12, '{"top":"100","left":"60"}', 552, 75, 35, '{"top":"140","left":"595"}', 122, '{"top":"85","left":"623"}', 86, '{"top":"204","left":"343"}', '{"top":"190","left":"312"}', 186, 'bhagyoday-1269910896.jpg', 1, 0, '2017-07-13 14:57:58', 0),
(33, 18, '{"top":"65","left":"68"}', 529, '{"top":"12","left":"580"}', 156, 13, '{"top":"90","left":"51"}', 552, 70, 35, '{"top":"126","left":"596"}', 122, '{"top":"61","left":"637"}', 78, '{"top":"183","left":"339"}', '{"top":"166","left":"309"}', 186, 'syndicatebank192610252-721858130.jpg', 1, 0, '2017-08-09 10:07:52', 0),
(34, 10, '{"top":"78","left":"68"}', 592, '{"top":"32","left":"587"}', 157, 13, '{"top":"102","left":"62"}', 542, 40, 35, '{"top":"140","left":"601"}', 112, '{"top":"79","left":"682"}', 69, '{"top":"179","left":"353"}', '{"top":"172","left":"552"}', 176, 'editedcheque-211113918.jpg', 1, 0, '2017-11-18 16:02:49', 0),
(36, 34, '{"top":"85","left":"60"}', 576, '{"top":"37","left":"586"}', 161, 13, '{"top":"110","left":"63"}', 550, 40, 35, '{"top":"150","left":"612"}', 120, '{"top":"92","left":"688"}', 58, '{"top":"265","left":"318"}', '{"top":"246","left":"298"}', 184, 'hdfc6960256216-409920748.jpg', 1, 0, '2018-07-14 15:08:42', 0),
(37, 40, '{"top":"75.98958587646484","left":"42.986114501953125"}', 552.021, '{"top":"27.986112594604492","left":"577.9861450195312"}', 155.021, 12, '{"top":"97.98611450195312","left":"48.99305725097656"}', 676.021, 40, 35, '{"top":"137.98611450195312","left":"582.9861450195312"}', 119.021, '{"top":"81.97917175292969","left":"644.982666015625"}', 76.0208, '{"top":"206.9965362548828","left":"324.982666015625"}', '{"top":"253.99307250976562","left":"262.9861145019531"}', 184.021, 'kotakfinal-289130538.png', 1, 0, '2018-07-23 11:15:59', 0),
(40, 36, '{"top":"91","left":"131"}', 487, '{"top":"40","left":"574"}', 179, 15, '{"top":"116","left":"55"}', 624, 75, 35, '{"top":"156","left":"607"}', 122, '{"top":"94","left":"682"}', 77, '{"top":"233","left":"344"}', '{"top":"216","left":"316"}', 186, 'indusindcheque-981296739.jpg', 1, 0, '2018-08-13 17:44:15', 0),
(41, 1, '{"top":"77","left":"61"}', 509, '{"top":"23","left":"597"}', 136, 11, '{"top":"98","left":"74"}', 516, 40, 35, '{"top":"132","left":"598"}', 127, '{"top":"57","left":"609"}', 132, '{"top":"180","left":"580"}', '{"top":"164","left":"571"}', 192, 'sbi-1043994044.png', 1, 0, '2018-12-04 09:11:46', 0),
(44, 20, '{"top":"80","left":"57"}', 545, '{"top":"27","left":"587"}', 138, 11, '{"top":"101","left":"59"}', 532, 40, 35, '{"top":"137","left":"593"}', 126, '{"top":"63","left":"586"}', 122, '{"top":"181","left":"327"}', '{"top":"176","left":"559"}', 203, 'kalupur-498221963.png', 1, 0, '2018-12-04 09:19:28', 0),
(45, 158, '{"top":"78","left":"57"}', 538, '{"top":"25","left":"589"}', 136, 11, '{"top":"97","left":"66"}', 492, 40, 35, '{"top":"135","left":"589"}', 131, '{"top":"62","left":"618"}', 149, '{"top":"212","left":"596"}', '{"top":"193","left":"564"}', 182, 'union-1106992572.png', 1, 0, '2018-12-04 09:19:53', 0),
(46, 34, '{"top":"72","left":"59"}', 560, '{"top":"20","left":"593"}', 159, 12.5, '{"top":"98","left":"73"}', 628, 40, 35, '{"top":"137","left":"612"}', 122, '{"top":"69","left":"642"}', 77, '{"top":"249","left":"326"}', '{"top":"222","left":"291"}', 186, 'hdfcshakti-1123043427.jpg', 1, 0, '2018-12-18 22:04:15', 3),
(47, 46, '{"top":"80.98959350585938","left":"59.982635498046875"}', 557.021, '{"top":"32.916656494140625","left":"560.9896392822266"}', 158.021, 13, '{"top":"101.97915649414062","left":"67.98611450195312"}', 627.021, 40, 35, '{"top":"138.99307250976562","left":"575.9896392822266"}', 125.021, '{"top":"79.98263549804688","left":"655.9896392822266"}', 70.0208, '{"top":"246.97918701171875","left":"279.98265075683594"}', '{"top":"199.98263549804688","left":"325.9722442626953"}', 188.021, 'yesbankmspindustries-1111338782.jpg', 1, 0, '2018-12-20 19:58:40', 3),
(48, 21, '{"top":"80.98959350585938","left":"64.98263549804688"}', 491.021, '{"top":"28.9757080078125","left":"560.9896392822266"}', 150.021, 12, '{"top":"99.96527099609375","left":"71.99653625488281"}', 618.021, 40, 35, '{"top":"137.98611450195312","left":"577.9861602783203"}', 124.021, '{"top":"83.9757080078125","left":"643.9930572509766"}', 62.0208, '{"top":"235.98959350585938","left":"330.98960876464844"}', '{"top":"191.97918701171875","left":"310.9722442626953"}', 188.021, 'idbibank-1049200890.jpg', 1, 0, '2018-12-20 20:21:26', 0),
(49, 22, '{"top":"82","left":"59"}', 541, '{"top":"29","left":"589"}', 140, 11, '{"top":"104","left":"70"}', 628, 40, 35, '{"top":"141","left":"590"}', 122, '{"top":"85","left":"670"}', 83, '{"top":"193","left":"358"}', '{"top":"263","left":"295"}', 186, 'allahabadbankin-222818563.png', 1, 0, '2019-02-12 14:42:50', 0),
(50, 159, '{"top":"77","left":"102"}', 543, '{"top":"36","left":"569"}', 154, 12.5, '{"top":"99","left":"66"}', 554, 40, 35, '{"top":"139","left":"578"}', 124, '{"top":"86","left":"654"}', 62, '{"top":"178","left":"285"}', '{"top":"226","left":"276"}', 188, 'cosmossamplecheque-78705127.jpg', 1, 0, '2019-03-17 17:39:47', 6),
(52, 27, '{"top":"74","left":"73"}', 470, '{"top":"22","left":"597"}', 152, 12.5, '{"top":"98","left":"36"}', 630, 40, 35, '{"top":"137","left":"591"}', 122, '{"top":"82","left":"623"}', 60, '{"top":"183","left":"273"}', '{"top":"230","left":"260"}', 186, 'axisbank203798516-42214177.jpg', 1, 0, '2019-04-10 14:53:58', 3);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
