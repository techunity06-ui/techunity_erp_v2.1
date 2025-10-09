<?php
include_once ("../config/config.php");
include_once (COMMON_FUNCTION_OUTER_PATH . "common_functions.php");
include_once ("../include/function_database_query.php");

$query_invoicetype = $dbcon->query("UPDATE tbl_company_configuration SET store_approval = 1");
$query_invoicetype = $dbcon->query("UPDATE tbl_bom set bom_trn = 2 where bom_product in (SELECT product_id FROM product_mst WHERE product_status = 2)");
$query_invoicetype = $dbcon->query("UPDATE tbl_bom_trn set bom_trn_status = 2 where product_id in (SELECT product_id FROM product_mst WHERE product_status = 2)");

$query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company` ADD `cin` VARCHAR(200) NOT NULL AFTER `lut_no`");
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='old_query'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `product_mst` ADD `batch_wise_stock_manage` INT(11) NOT NULL COMMENT '0 - no , 1 - yes'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sale_return_transaction` CHANGE `sale_return_qty` `sale_return_qty` DOUBLE(22,3) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sale_return_transaction` CHANGE `sale_return_rate` `sale_return_rate` DOUBLE(22,2) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sale_return_transaction` CHANGE `sale_return_amount` `sale_return_amount` DOUBLE(22,2) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sale_return_transaction` CHANGE `sale_return_cgst_tax_per` `sale_return_cgst_tax_per` DOUBLE(22,2) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sale_return_transaction` CHANGE `sale_return_cgst_tax_amt` `sale_return_cgst_tax_amt` DOUBLE(22,2) NOT NULL, CHANGE `sale_return_igst_tax_per` `sale_return_igst_tax_per` DOUBLE(22,2) NOT NULL, CHANGE `sale_return_igst_tax_amt` `sale_return_igst_tax_amt` DOUBLE(22,2) NOT NULL, CHANGE `sale_return_total_amount` `sale_return_total_amount` DOUBLE(22,2) NOT NULL, CHANGE `currency_rate` `currency_rate` DOUBLE(22,2) NOT NULL DEFAULT '1'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_trn` ADD `rate_unit` INT(11) NOT NULL ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `batch_stock` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - manually, 1 - automatic' ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `qc_status` INT(11) NOT NULL , ADD `accept_qty` VARCHAR(100) NOT NULL , ADD `reject_qty` VARCHAR(100) NOT NULL , ADD `reprocess_qty` VARCHAR(100) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `enable_count_outstanding_target` INT NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `grn_trn_id` INT(11) NOT NULL AFTER `grn_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `batch_unit` INT(11) NOT NULL , ADD `base_qty` VARCHAR(100) NOT NULL , ADD `base_unit` INT(11) NOT NULL , ADD `conv_qty` VARCHAR(100) NOT NULL , ADD `conv_unit` INT(11) NOT NULL ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `qc_qty` VARCHAR(100) NOT NULL ");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_check_master_delete_setting` (
                          `check_mst_id` int(11) NOT NULL AUTO_INCREMENT,
                          `master_name` int(11) NOT NULL COMMENT '1.complaint type list 2.category list 3.drawing 4.godown list 5.item list 6.make 7.make number 8.material parameter 9.material specification 10.process list 11.process type list 12.product type list 13.qc parameter list 14.bank list 15.common category 16.common master 17.cost center 18.cost center group 19.currency list 20.group list 21.hsn master 22.ledger list 23.tax category 24.tds tax category 25.transportation detail 27.branch list 28.city list 29.company configuration 30.country list 31.narration master 32.series type 33.state list 34.unit list 35.zone list',
                          `tbl_name` varchar(200) NOT NULL,
                          `column_name` varchar(200) NOT NULL,
                          `column_status` varchar(200) NOT NULL,
                          `used_status` int(11) NOT NULL,
                          `isdelete` tinyint(4) NOT NULL DEFAULT '0',
                          `cdate` timestamp NOT NULL,
                          `user_id` int(11) NOT NULL,
                          `company_id` int(11) NOT NULL,
                          `usertype_id` int(11) NOT NULL,
                          PRIMARY KEY (`check_mst_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1  ");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_module_type` (
                              `module_type_id` int(11) NOT NULL AUTO_INCREMENT,
                              `module_name` varchar(300) NOT NULL,
                              `status` int(11) NOT NULL COMMENT '0.Active 2.Delete',
                              `cdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                              PRIMARY KEY (`module_type_id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1  ");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `stock_approval_status` INT(11) NOT NULL COMMENT '0 - no , 1 - yes' ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_store_accept` ADD `batch_id` INT(11) NOT NULL COMMENT 'reference id of tbl_batch_data'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_store_accept_trn` ADD `batch_id` INT(11) NOT NULL COMMENT 'reference id of tbl_batch_data'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_stock_trn` ADD `batch_id` INT(11) NOT NULL COMMENT 'reference id of tbl_batch_data'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_stock_trn` CHANGE `batch_no` `batch_no` VARCHAR(255) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `basic_total` VARCHAR(150) NOT NULL AFTER `round_off`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_qc` ADD `batch_id` INT(11) NOT NULL COMMENT 'reference id of tbl_batch_data'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process` ADD `batch_no` VARCHAR(255) NOT NULL ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `pro_bom_process` DROP FOREIGN KEY `pro_bom_process_ibfk_2`; ALTER TABLE `pro_bom_process` DROP FOREIGN KEY `pro_bom_process_ibfk_6` ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `production_start_type` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - manually , 1 - FIFO wise'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_store_accept` ADD `batch_no` VARCHAR(255) NOT NULL ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_product_party_purchase` CHANGE `cdate` `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `product_item_code` VARCHAR(150) NOT NULL ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` CHANGE `batch_qty` `batch_qty` VARCHAR(255) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_product_party_purchase` CHANGE `card_status` `card_status` INT(11) NOT NULL COMMENT '0.active 2.delete'");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_page_permission` (
                      `permission_id` int(11) NOT NULL AUTO_INCREMENT,
                      `crm_partymst_cust_name` int(11) NOT NULL,
                      `crm_partymst_cust_mobile` int(11) NOT NULL,
                      `crm_partymst_cust_email` int(11) NOT NULL,
                      `crm_partymst_cust_gst` int(11) NOT NULL,
                      `crm_partymst_cust_ind` int(11) NOT NULL,
                      `crm_partymst_cust_source` int(11) NOT NULL,
                      `crm_partymst_t_id` int(11) NOT NULL,
                      `crm_partymst_c_add_location` int(11) NOT NULL,
                      `crm_partymst_c_add_street` int(11) NOT NULL,
                      `crm_partymst_c_add_country` int(11) NOT NULL,
                      `crm_partymst_c_add_state` int(11) NOT NULL,
                      `crm_partymst_c_add_city` int(11) NOT NULL,
                      `crm_partymst_c_add_zip` int(11) NOT NULL,
                      `crm_partymst_cust_cat` int(11) NOT NULL,
                      `crm_partymst_cust_type` int(11) NOT NULL,
                      `status` int(11) NOT NULL,
                      `user_id` int(11) NOT NULL,
                      `company_id` int(11) NOT NULL,
                      `branch_id` int(11) NOT NULL,
                      `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      PRIMARY KEY (`permission_id`)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_product_party_purchase` ADD `is_aproove` INT NOT NULL COMMENT '0.disaproove 1.aproove' AFTER `card_status`, ADD `is_active` INT NOT NULL COMMENT '0.active 1.in-active' AFTER `is_aproove`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_product_party_purchase` CHANGE `card_type` `card_type` INT(11) NOT NULL COMMENT '0.vendor wise 1.product wise'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `stock_type_a` VARCHAR(111) NOT NULL AFTER `production_start_type`, ADD `stock_type_b` VARCHAR(111) NOT NULL AFTER `stock_type_a`, ADD `stock_type_c` VARCHAR(111) NOT NULL AFTER `stock_type_b`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasetrntemp` CHANGE `po_ref_type` `po_ref_type` INT NOT NULL COMMENT '0:min_max, 1 : WO , 2 : Direct'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `purchaseordertrn_id` INT NOT NULL AFTER `customer_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_task` CHANGE `entry_type` `entry_type` INT(11) NOT NULL COMMENT '1:Task, 2:Appointment,3:post crm value task'");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `product_tempdata` (
    `product_tempdata_id` int(11) NOT NULL AUTO_INCREMENT,
    `line_num` int(11) NOT NULL,
    `error` varchar(200) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`product_tempdata_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work` ADD `g_total` VARCHAR(100) NOT NULL ");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `product_process_tempdata` ( `product_tempdata_id` int(11) NOT NULL AUTO_INCREMENT, `line_num` int(11) NOT NULL, `error` varchar(200) NOT NULL, `company_id` int(11) NOT NULL, PRIMARY KEY (`product_tempdata_id`) ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `product_qc_tempdata` ( `product_tempdata_id` int(11) NOT NULL AUTO_INCREMENT, `line_num` int(11) NOT NULL, `error` varchar(200) NOT NULL, `company_id` int(11) NOT NULL, PRIMARY KEY (`product_tempdata_id`) ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work` ADD `release_status` INT(11) NOT NULL , ADD `chalan_no` VARCHAR(255) NOT NULL , ADD `chalan_date` DATE NOT NULL ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work` ADD `chalan_status` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_task` CHANGE `entry_type` `entry_type` INT(11) NOT NULL COMMENT '1:Task, 2:Appointment,3:post crm value task,4:Product wise followup'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_cust_relation` CHANGE `gender` `gender` TINYINT NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_cust_relation` CHANGE `gender` `gender` TINYINT(4) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_cust_price_list` (
    `price_list_cust_id` int(11) NOT NULL AUTO_INCREMENT,
    `cust_price_month` varchar(255) NOT NULL,
    `cust_price_year` varchar(255) NOT NULL,
    `cust_price_version_id` int(11) NOT NULL,
    `isdelete` tinyint(4) NOT NULL DEFAULT '0',
    `cdate` timestamp NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `usertype_id` int(11) NOT NULL,
    PRIMARY KEY (`price_list_cust_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_cust_price_list` ADD `customer_id` INT NOT NULL AFTER `price_list_cust_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_trn` ADD `release_status` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn` ADD `release_status` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_store_request` ADD `job_work_sub_trn_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work` ADD `request_status` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_trn` ADD `request_status` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn` ADD `request_status` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_batch_stock_tmp` (
    `batch_stk_id` int(11) NOT NULL AUTO_INCREMENT,
    `invoice_trn_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `stock_id` int(11) NOT NULL,
    `reserve_id` int(11) NOT NULL,
    `qty` int(11) NOT NULL,
    `unitid` int(11) NOT NULL,
    `status` tinyint(4) NOT NULL,
    `cdate` datetime NOT NULL,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    PRIMARY KEY (`batch_stk_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_purchaseorder_terms_trn` (
    `po_terms_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `purchaseorder_id` int(11) NOT NULL,
    `tc_id` int(11) NOT NULL,
    `tc_priority` int(11) NOT NULL,
    `tc_details` longtext NOT NULL,
    `po_terms_trn_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`po_terms_trn_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasetrntemp` CHANGE `product_qty` `product_qty` DOUBLE(10,3) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal_item` ADD `rr_disapprove_qty` INT NOT NULL AFTER `rr_approve_qty`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_delivery_date` CHANGE `product_qty` `product_qty` DOUBLE(10,3) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_delivery_date` CHANGE `used_qty` `used_qty` DOUBLE(10,3) NOT NULL");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_returnable_return_date` (
    `return_date_id` int(11) NOT NULL AUTO_INCREMENT,
    `return_item_id` int(11) NOT NULL,
    `return_date` date NOT NULL,
    `item_qty` double(10,3) NOT NULL,
    `used_qty` double(10,3) NOT NULL,
    `grn_status` int(11) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `return_date_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`return_date_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoicetrn` ADD `product_spec` LONGTEXT NOT NULL AFTER `description`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoicetrn` CHANGE `description` `description` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn` CHANGE `ref_type` `ref_type` INT(11) NOT NULL COMMENT '1:jobwork , 2 :PO , 3: service, 4. direct workorder, 5. outside so, 6. Returnable Chalan GRN'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal` ADD `challan_date` DATE NOT NULL AFTER `channal_id`, ADD `challan_type` VARCHAR(50) NOT NULL AFTER `challan_date`, ADD `challan_return_type` VARCHAR(50) NOT NULL AFTER `challan_type`, ADD `return_date` DATE NOT NULL AFTER `challan_return_type`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal_item` ADD `item_hsn` VARCHAR(50) NOT NULL AFTER `item_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal` ADD `grn_status` INT(11) NOT NULL COMMENT '0-grn pending, 1 - grn created");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal_item` ADD `grn_status` INT(11) NOT NULL COMMENT '0-grn pending, 1 - grn created'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `enable_transport` INT NOT NULL ");
  $query_invoicetype = $dbcon->query("INSERT INTO `print_type_mst` (`id`, `print_type_name`, `print_status`, `cdate`, `user_id`, `company_id`, `branch_id`) VALUES ('14', 'Returnable Challan', '0', CURRENT_TIMESTAMP, '1', '1', '1') ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `gross_balance_tds_limit` DOUBLE(10,2) NOT NULL DEFAULT '0' ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn` ADD `returnable_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_trn` ADD `returnable_id` INT(11) NOT NULL , ADD `returnable_trn_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `returnable_trn_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_re_process` ADD `process_priority` INT(11) NOT NULL , ADD `process_revision_count` INT(11) NOT NULL , ADD `ref_pid` INT(11) NOT NULL , ADD `qc_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_trn` CHANGE `cgst_tax_per` `cgst_tax_per` DOUBLE(22,2) NOT NULL, CHANGE `cgst_tax_rate` `cgst_tax_rate` DOUBLE(22,2) NOT NULL, CHANGE `sgst_tax_per` `sgst_tax_per` DOUBLE(22,2) NOT NULL, CHANGE `sgst_tax_rate` `sgst_tax_rate` DOUBLE(22,2) NOT NULL, CHANGE `igst_tax_per` `igst_tax_per` DOUBLE(22,2) NOT NULL, CHANGE `igst_tax_rate` `igst_tax_rate` DOUBLE(22,2) NOT NULL");
  $query_invoicetype = $dbcon->query("
  CREATE TABLE IF NOT EXISTS `tbl_wororder_product_reprocess` (
    `pr_process_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `qc_id` int(11) NOT NULL,
    `rp_id` int(11) NOT NULL,
    `process_priority` int(11) NOT NULL,
    `process_time` varchar(100) NOT NULL,
    `process_type` int(11) NOT NULL COMMENT '1:Inhouse , 2:Outside',
    `process_opening` varchar(100) NOT NULL,
    `process_id` int(11) NOT NULL,
    `cdate` date NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) DEFAULT '0',
    PRIMARY KEY (`pr_process_id`),
    KEY `branch_id` (`branch_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ");


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_salesorder_delivery_date` CHANGE `product_qty` `product_qty` DOUBLE(10,3) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_salesorder_delivery_date` CHANGE `used_qty` `used_qty` DOUBLE(10,3) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_aprv_log` CHANGE `approve_status` `approve_status` INT(11) NOT NULL COMMENT '1=Approved,2=Disapprove'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_re_process` ADD `start_qty` VARCHAR(255) NOT NULL AFTER `pen_qty`, ADD `end_qty` VARCHAR(255) NOT NULL AFTER `start_qty`");

  //maulik start

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_allocate_re_process_trn` (
    `pt_id` int(11) NOT NULL AUTO_INCREMENT,
    `pt_alloc_id` int(11) NOT NULL,
    `pt_ref_id` int(11) NOT NULL,
    `pt_product_id` int(11) NOT NULL,
    `pt_process_id` int(11) NOT NULL,
    `qc_reporcess_godown` int(11) NOT NULL COMMENT 'mst_godown ref id',
    `pt_qty` varchar(255) NOT NULL,
    `p_status` int(11) NOT NULL,
    `cdate` date NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`pt_id`),
    KEY `pt_id` (`pt_id`,`pt_alloc_id`,`pt_ref_id`,`pt_product_id`,`pt_process_id`,`pt_qty`,`p_status`,`user_id`,`company_id`),
    KEY `qc_reporcess_godown` (`qc_reporcess_godown`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_aprv_log` CHANGE `approve_status` `approve_status` INT(11) NOT NULL COMMENT '3=Approved,2=Disapprove'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_re_process` ADD `previous_process_id` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_finance_aprv_log` CHANGE `approve_status` `approve_status` INT(11) NOT NULL COMMENT '3:approve pending;4: disapproved;1: approved'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_re_process` ADD `batch_id` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_order` CHANGE `g_total` `g_total` DOUBLE(22,2) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` CHANGE `cgst_tax_per` `cgst_tax_per` DOUBLE(22,2) NOT NULL DEFAULT '0', CHANGE `sgst_tax_per` `sgst_tax_per` DOUBLE(22,2) NOT NULL DEFAULT '0', CHANGE `igst_tax_per` `igst_tax_per` DOUBLE(22,2) NOT NULL DEFAULT '0'");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_reprocess_trn_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `process_id` int(11) NOT NULL,
    `re_pro_p_id` int(11) NOT NULL,
    `pt_alloc_id` int(11) NOT NULL,
    `qty` varchar(255) NOT NULL,
    `process_type` int(11) NOT NULL COMMENT '1-start, 2 - end',
    `status` int(11) NOT NULL,
    `cdate` date NOT NULL,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `reprocess_qc` INT(11) NULL DEFAULT '0'");

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_ledger` (`l_id`, `l_name`, `ledger_code`, `emp_profile_img`, `l_group`, `l_form`, `countryid`, `stateid`, `cityid`, `cust_pincode`, `m_name`, `m_address`, `m_pan`, `company_name`, `cust_cont_name`, `cust_mobile`, `cust_email`, `cust_website`, `zone_id`, `cust_assign_user`, `branch_id_customer`, `party_sez`, `branch_id_employee`, `cust_remark`, `gst_no`, `party_type`, `cust_gst_reg`, `pay_terms`, `pay_method`, `bill_type`, `balance_typeid`, `acc_type`, `bankid`, `branch_name`, `acc_name`, `acc_number`, `acc_chequeno`, `acc_chequeleft`, `emp_mobile`, `emp_email`, `emp_password`, `emp_zone_id`, `emp_user_type`, `tax_value`, `opn_balance`, `usertype_terr`, `alloc_stateid`, `alloc_cityid`, `report_to_user_type`, `report_to_user_id`, `cdate`, `user_id`, `company_id`, `l_status`, `print_priority`, `cust_id`, `employee_id`, `branch_id`, `credit_limit`, `credit_days`, `is_deletable`, `ledger_alias`, `enable_multi_currency_opening`, `enable_branch_opening`, `ledger_opening_balance_type`, `enable_cost_center`, `enable_tds`, `party_pay_cat`, `enable_tcs`, `enable_depreciation`, `enable_monthly_budget`, `ledger_Tax_type`, `ledger_gst_applicable`, `ledger_tax_category`, `ledger_hsn`, `ledger_itc`, `ledger_rcm`, `enable_bill_sunfry`, `enable_sez`, `enable_cheque_deposit`, `enable_billbybill_opening`, `default_sundry`, `enable_salesman`, `default_ledger`, `shift_time`, `emp_signature_img`) VALUES ('9895', 'TDS(on purchase of goods)', '', '', '31', 'tax_form', '101', '1', '1', '380015', '0', '0', 'CXNSHSSD', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0.00', '0', '0', '0', '0', '0', '0', '9.00', '0.00', '0', '0', '0', '0', '0', '2021-01-26 14:35:03', '1', '1', '0', '2', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '1', '0', '0', '0', '1', '0', '1', '0', '')");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_alias` = 'TDS_PURCHASE' WHERE `tbl_ledger`.`l_id` = 9895");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 9895");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24449");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24450");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24451");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24452");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24453");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24454");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24455");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24456");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24457");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24458");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24459");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9891' WHERE `tbl_ledger`.`l_id` = 24460");



  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `wip_stock_allocate` (
    `wip_stock_allocate_id` int(11) NOT NULL AUTO_INCREMENT,
    `rp_id` int(11) NOT NULL,
    `type_flag` int(11) NOT NULL COMMENT '1: indent,2:job card, 3: work order',
    `po_trn_id` int(11) NOT NULL,
    `sales_order_trn_id` int(11) NOT NULL,
    `allocate_for_rp_id` int(11) NOT NULL,
    `allocate_table_name` varchar(111) NOT NULL,
    `allocate_table_id` int(11) NOT NULL,
    `allocate_base_qty` varchar(111) NOT NULL,
    `allocate_base_qty_used` varchar(101) NOT NULL,
    `allocate_base_unit` int(11) NOT NULL,
    `allocate_conv_qty` varchar(111) NOT NULL,
    `allocate_conv_qty_used` varchar(111) NOT NULL,
    `allocate_conv_unit` int(11) NOT NULL,
    `perent_id` int(11) NOT NULL,
    `stock_flag` int(11) NOT NULL COMMENT '1: add ,2:deduct',
    `status` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` double NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`wip_stock_allocate_id`),
    KEY `acc_id` (`wip_stock_allocate_id`,`user_id`,`company_id`),
    KEY `acc_id_2` (`wip_stock_allocate_id`,`user_id`,`company_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `work_order_reserve_temp` (
    `work_order_reserve_temp_id` int(11) NOT NULL AUTO_INCREMENT,
    `rp_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `reserve_qty` varchar(111) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `godown_id` int(11) NOT NULL,
    `stock_id` int(11) NOT NULL COMMENT 'batch_id',
    `status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` double NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`work_order_reserve_temp_id`),
    KEY `acc_id` (`work_order_reserve_temp_id`,`status`,`user_id`,`company_id`),
    KEY `acc_id_2` (`work_order_reserve_temp_id`,`status`,`user_id`,`company_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pono` ADD `tds_amount` DOUBLE(10,2) NOT NULL , ADD `tds_per` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_debitnote` ADD `tds_amount` DOUBLE(10,2) NOT NULL , ADD `tds_per` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `sales_type` VARCHAR(50) NOT NULL AFTER `enable_transport`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal_item` ADD `sales_ordertrn_id` INT NOT NULL AFTER `grn_status`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_receipt_payment_trn` ADD `ref_id` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_receipt_payment_trn` ADD `tds_per` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_journal_trn` ADD `ref_id` INT(11) NOT NULL , ADD `tds_per` INT(11) NOT NULL");


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `returnable_status` INT NOT NULL COMMENT '0:Pending 1:Returnable Done' AFTER `product_item_code`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal` ADD `issue_date` DATETIME NOT NULL AFTER `challan_date`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal` ADD `sales_order_id` INT NOT NULL AFTER `grn_status`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_ledger` ADD `common_email_id` VARCHAR(150) NOT NULL AFTER `cust_pincode`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `email_sms_template` ADD `email_cc` VARCHAR(111) NOT NULL AFTER `company_id`, ADD `email_bcc` VARCHAR(111) NOT NULL AFTER `email_cc`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `users` ADD `common_email_id` VARCHAR(150) NOT NULL AFTER `user_type`");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_tds_tax_deduction_reference` (
    `deduction_ref_id` int(11) NOT NULL AUTO_INCREMENT,
    `payment_id` int(11) NOT NULL,
    `payment_date` date NOT NULL,
    `payment_amount` double(10,2) NOT NULL,
    `payment_tds_ledger_id` int(11) NOT NULL,
    `pay_chalanno` varchar(200) NOT NULL,
    `pay_cheque_no` varchar(200) NOT NULL,
    `isdelete` tinyint(4) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`deduction_ref_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_req_trn` CHANGE `used_qty` `used_qty` DOUBLE(10,3) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn` ADD `receive_datetime` DATETIME NOT NULL");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_tds_tax_deduction_reference_detail` (
    `deduction_ref_detl_id` int(11) NOT NULL AUTO_INCREMENT,
    `deduction_ref_id` int(11) NOT NULL,
    `ref_payment_id` int(11) NOT NULL COMMENT 'general book id',
    `ref_pay_amount` int(11) NOT NULL,
    `isdelete` tinyint(4) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`deduction_ref_detl_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_tds_tax_deduction_reference_detail` CHANGE `ref_pay_amount` `ref_pay_amount` DOUBLE(10,2) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `oilfield_permission` INT NOT NULL AFTER `umaboy_permission`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation`  ADD `terms` VARCHAR(500) NOT NULL  AFTER `basic_total`,  ADD `shipped_via` VARCHAR(500) NOT NULL  AFTER `terms`,  ADD `delivery_no` VARCHAR(500) NOT NULL  AFTER `shipped_via`,  ADD `order_no` VARCHAR(500) NOT NULL  AFTER `delivery_no`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder`  ADD `terms` VARCHAR(500) NOT NULL  AFTER `financial_year_id`,  ADD `shipped_via` VARCHAR(500) NOT NULL  AFTER `terms`,  ADD `fob` VARCHAR(500) NOT NULL  AFTER `shipped_via`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `product_amount` `product_amount` DOUBLE(10,2) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `total` `total` DOUBLE(10,2) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_delivery_date` ADD `delay_days` INT NOT NULL AFTER `used_qty`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_bill_by_bill_adjustment_transaction` CHANGE `bill_ref_type` `bill_ref_type` TINYINT(4) NOT NULL COMMENT '0-invoice,1-ledger,2:purchase'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `email_sms_template`  ADD `print_page_id` INT NOT NULL  AFTER `sms_content`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pono` ADD `payment_status` INT NOT NULL COMMENT '0:due , 1 :paid' ");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasecardtrn` ADD `valid_date` DATE NOT NULL AFTER `affected_date`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasecardtrn` CHANGE `purchasecardtrn_status` `purchasecardtrn_status` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `tcs_amount` DOUBLE(10,2) NOT NULL , ADD `tcs_per` DOUBLE(10,2) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_tds_tax_deduction_reference` ADD `payment_type` TINYINT NOT NULL DEFAULT '1' COMMENT '1-tds,2-tcs'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasecardtrn` CHANGE `price` `price` DOUBLE(10,2) NOT NULL DEFAULT '0'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pre_trn` ADD `purchasecardtrn_id` INT NOT NULL AFTER `pre_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` ADD `purchasecardtrn_id` INT NOT NULL AFTER `product_tax_cat`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `enable_hypothication` INT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_tds_tax_deduction_reference` ADD `payment_tbl` VARCHAR(255) NOT NULL AFTER `payment_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `check_hypothication` INT NOT NULL , ADD `hypo_bank` INT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `product_rate` `product_rate` DOUBLE(10,2) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` ADD `purchasecardtrn_id` INT NOT NULL AFTER `purchaseorder_id`");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_Tax_type` = '9892' WHERE `tbl_ledger`.`l_id` = 9892");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `po_document_required` INT NOT NULL  AFTER `enable_hypothication`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `enable_consignee` INT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pono` CHANGE `tds_per` `tds_per` DOUBLE(10,2) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_debitnote` CHANGE `tds_per` `tds_per` DOUBLE(10,2) NOT NULL");

  $query_invoicetype = $dbcon->query("INSERT INTO `print_setup_mst` (`id`, `print_type`, `print_name`, `fa_icon`, `page_path`, `icon_color`, `priority`, `approve_status`, `status`, `cdate`, `user_id`, `company_id`, `branch_id`) VALUES (NULL, '7', 'Export Invoice Receipt', 'fa fa-print', 'invoicereceipt', '#337ab7', '2', '1', '0', '2021-11-29 14:37:54', '1', '1', '1')");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_jobwork_rate_card` (
    `jobwork_card_id` int(11) NOT NULL AUTO_INCREMENT,
    `card_type` int(11) NOT NULL,
    `jobwork_card_no` varchar(150) NOT NULL,
    `party_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `jobwork_card_date` date NOT NULL,
    `card_status` int(11) NOT NULL,
    `is_aproove` int(11) NOT NULL,
    `is_active` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`jobwork_card_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_jobwork_rate_cardtrn` (
    `jobwork_card_trnid` int(11) NOT NULL AUTO_INCREMENT,
    `jobwork_card_id` int(11) NOT NULL,
    `vendor_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `rate_tolerance` varchar(100) NOT NULL,
    `discount_percentage` varchar(100) NOT NULL,
    `jobwork_cardtrn_status` int(11) NOT NULL COMMENT '0.active 2.delete',
    `quotation_number` varchar(100) NOT NULL,
    `quotation_date` date NOT NULL,
    `affected_date` date NOT NULL,
    `valid_date` date NOT NULL,
    `price` double(10,2) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`jobwork_card_trnid`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_jobwork_ratecard_aprv_log` (
    `jobworkcard_aprv_id` int(11) NOT NULL AUTO_INCREMENT,
    `jobwork_card_id` int(11) NOT NULL,
    `approve_remark` text NOT NULL,
    `approve_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`jobworkcard_aprv_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation` CHANGE `an_id` `an_id` VARCHAR(150) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_jobwork_rate_cardtrn` ADD `process_id` INT NOT NULL AFTER `price`");

  $query_invoicetype = $dbcon->query("
  CREATE TABLE IF NOT EXISTS `tbl_aging_slab` (
    `slab_id` int(11) NOT NULL AUTO_INCREMENT,
    `slab_name` int(11) NOT NULL,
    `slab_start_day` int(11) NOT NULL,
    `slab_end_day` int(11) NOT NULL,
    `isdelete` tinyint(4) NOT NULL DEFAULT '0',
    `cdate` timestamp NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `usertype_id` int(11) NOT NULL,
    PRIMARY KEY (`slab_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6");

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_aging_slab` (`slab_id`, `slab_name`, `slab_start_day`, `slab_end_day`, `isdelete`, `cdate`, `user_id`, `company_id`, `usertype_id`) VALUES
  (1, 1, 1, 30, 0, '2021-12-24 06:41:00', 0, 1, 0),
  (2, 2, 31, 60, 0, '2021-12-24 06:41:00', 0, 1, 0),
  (3, 3, 61, 90, 0, '2021-12-24 06:41:24', 0, 1, 0),
  (4, 4, 91, 120, 0, '2021-12-24 06:41:24', 0, 1, 0),
  (5, 5, 121, 191, 0, '2021-12-24 06:41:35', 0, 1, 0)");

  $query_invoicetype = $dbcon->query("ALTER TABLE `opening_stock_mst` ADD `active_status` INT(11) NOT NULL COMMENT '0 - active, 1 - deactive'");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_company_configuration` SET `crm_task_order` = 'DESC' WHERE `tbl_company_configuration`.`company_conf_id` = 1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `apply_gst` TINYINT(4) NOT NULL , ADD `sundry_gst` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_bill_sundry_transaction` ADD `sundry_gst_per` DOUBLE(10,2) NOT NULL , ADD `sundry_gst_amount` DOUBLE(10,2) NOT NULL");

  //maulik end 

  //hardi start
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_temp_process_desc` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `rp_id` int(11) NOT NULL,
    `process_id` int(11) NOT NULL,
    `description` longtext NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_bill_by_bill_adjustment_transaction` ADD `bill_ref_manual` VARCHAR(255) NOT NULL AFTER `bill_ref`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_wororder_product_process` ADD `description` LONGTEXT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process` ADD `description` LONGTEXT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_temp_process_desc` CHANGE `desc` `description` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_order`  ADD `revise_status` INT NOT NULL COMMENT '1:Yes,0:No' ,  ADD `start_sales_order_id` INT NOT NULL ,  ADD `prev_sales_order_id` INT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_order` CHANGE `approve_status` `approve_status` INT(11) NOT NULL COMMENT '3:approve,0:pending,1:reject', CHANGE `order_accept_status` `order_accept_status` INT(11) NOT NULL COMMENT '3:reject,0:pending,1:approved'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `enable_bill_adjustment` INT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `workorder_planning` INT(11) NOT NULL COMMENT '0-all , 1 - single' , ADD `production_start_stop_time` INT(11) NOT NULL COMMENT '0- automatic, 1 - manually'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` CHANGE `workorder_planning` `workorder_planning` INT(11) NOT NULL DEFAULT '0' COMMENT '0-all , 1 - single'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` CHANGE `production_start_stop_time` `production_start_stop_time` INT(11) NOT NULL DEFAULT '0' COMMENT '0- automatic, 1 - manually'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_bill_by_bill_adjustment_transaction` ADD `bill_adjustment_status` INT NOT NULL AFTER `bill_transaction_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_bill_by_bill_adjustment_transaction` ADD `bill_adjustment_id` INT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process_trn` ADD `start_stop_user_id` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `customer_req_material` VARCHAR(255) NOT NULL , ADD `customer_req_grade` VARCHAR(255) NOT NULL , ADD `customer_req_size` VARCHAR(50) NOT NULL , ADD `customer_req_id` VARCHAR(50) NOT NULL , ADD `customer_req_length` VARCHAR(50) NOT NULL , ADD `customer_req_heat` VARCHAR(50) NOT NULL , ADD `customer_req_coc` VARCHAR(50) NOT NULL , ADD `customer_ref_no` VARCHAR(50) NOT NULL , ADD `customer_asset_serial` VARCHAR(50) NOT NULL , ADD `customer_bevel_spec` VARCHAR(255) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `customer_req_material` VARCHAR(255) NOT NULL , ADD `customer_req_grade` VARCHAR(255) NOT NULL , ADD `customer_req_size` VARCHAR(50) NOT NULL , ADD `customer_req_id` VARCHAR(50) NOT NULL , ADD `customer_req_length` VARCHAR(50) NOT NULL , ADD `customer_req_heat` VARCHAR(50) NOT NULL , ADD `customer_req_coc` VARCHAR(50) NOT NULL , ADD `customer_ref_no` VARCHAR(50) NOT NULL , ADD `customer_asset_serial` VARCHAR(50) NOT NULL , ADD `customer_bevel_spec` VARCHAR(255) NOT NULL");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_so_stock_transfer` (
    `so_stock_transfer_id` int(11) NOT NULL AUTO_INCREMENT,
    `stock_transfer_no` varchar(110) NOT NULL,
    `stock_transfer_date` date NOT NULL,
    `product_id` int(11) NOT NULL,
    `main_so_trn_id` int(11) NOT NULL,
    `main_qty` varchar(111) NOT NULL,
    `transfer_so_trn_id` int(11) NOT NULL,
    `transfer_qty` varchar(100) NOT NULL,
    `remark` text NOT NULL,
    `so_stock_transfer_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL,
    `approval_status` int(11) NOT NULL COMMENT '0: no effects,1:approved,2: disapprov',
    PRIMARY KEY (`so_stock_transfer_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pono` ADD `enable_bill_adjustment` INT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `hierarchy_inq_assign` INT NOT NULL COMMENT '1:Yes,0:No'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_followup` ADD `folloup_date` DATE NOT NULL AFTER `purchaseorder_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_common_mst` CHANGE `common_mst_desc` `common_mst_desc` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_followup` CHANGE `folloup_date` `folloup_date` DATETIME NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_journal` ADD `jv_remark` LONGTEXT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `opening_stock_mst` ADD `batch_no` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `process_opening_stock_mst` ADD `batch_no` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_process_stock_trn` ADD `batch_no` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_followup` CHANGE `followup_status` `followup_status` INT(11) NOT NULL COMMENT '0.deactive 1.active'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_followup` ADD `branch_id` INT NOT NULL AFTER `company_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_followup` ADD `follow_date` DATE NOT NULL AFTER `folloup_date`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `process_opening_stock_mst` ADD `active_status` INT(11) NOT NULL COMMENT '0 - active, 1 - deactive'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_cron_send_email` ADD `email_user_id` INT(11) NOT NULL COMMENT 'users pk key user_id' AFTER `send_email_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasedebitnotetrn` CHANGE `product_qty` `product_qty` DOUBLE(10,4) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `product_qty` `product_qty` DOUBLE(15,4) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `product_conv_qty` `product_conv_qty` DOUBLE(15,4) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `used_qty` `used_qty` DOUBLE(15,4) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_delivery_date` CHANGE `product_qty` `product_qty` DOUBLE(10,4) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_delivery_date` CHANGE `used_qty` `used_qty` DOUBLE(10,4) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_req_trn` CHANGE `used_qty` `used_qty` DOUBLE(10,4) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasetrntemp` CHANGE `product_qty` `product_qty` DOUBLE(10,4) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pre_trn` CHANGE `product_qty` `product_qty` DOUBLE(15,4) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pre_trn` CHANGE `product_conv_qty` `product_conv_qty` DOUBLE(15,4) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `sending_blue_api_key` TEXT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `sendinblue_mail_id` TEXT NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_ledger` ADD `l_code_id` INT NOT NULL AFTER `ledger_code`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal` ADD `for_jobwork` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes' , ADD `for_sample` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes' , ADD `on_loan` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes' , ADD `for_replacement` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes' , ADD `for_repairing` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes' , ADD `rejected` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes' , ADD `loan_returns` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes' , ADD `non_returnable_matl` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes'");

  $query_invoicetype = $dbcon->query("update `tbl_ledger` set default_sundry='1' WHERE `ledger_Tax_type` = 9891");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `product_conv_qty` `product_conv_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `used_qty` `used_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasedebitnotetrn` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_req_trn` CHANGE `used_qty` `used_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasetrntemp` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `product_conv_qty` `product_conv_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pre_trn` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pre_trn` CHANGE `product_conv_qty` `product_conv_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission`  ADD `jr_fiber_glass_permission` INT NOT NULL  AFTER `oilfield_permission`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation`  ADD `delivery_from` VARCHAR(500) NOT NULL ,  ADD `po_address_to` VARCHAR(500) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` CHANGE `product_conv_qty` `product_conv_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_trn` CHANGE `product_conv_qty` `product_conv_qty` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_trn` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasecardtrn` ADD `unit_id` INT NOT NULL AFTER `valid_date`");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_alias` = 'TDS_PURCHASE_GROSS' WHERE `tbl_ledger`.`l_id` = 24453");

  $query_invoicetype = $dbcon->query("UPDATE  `tbl_ledger` SET `enable_bill_sunfry` = '1' WHERE `tbl_ledger`.`l_id` = 24453");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `inq_product_required` INT NOT NULL DEFAULT '1' COMMENT '1:Yes,0:No'");

  $query_invoicetype = $dbcon->query("update `tbl_ledger` set l_group='24' WHERE `l_name` LIKE '%purchase A/C%'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_general_book` ADD `general_percentage` DOUBLE(10,2) NOT NULL");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_purchaseorder` SET `start_purchaseorder_id`=`purchaseorder_id` WHERE `start_purchaseorder_id`=0");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_bill_sundry_transaction` ADD `tds_per` DOUBLE(10,2) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_trn` ADD `ref_type` INT NOT NULL COMMENT '1:jobwork , 2 :PO , 3: service, 4. direct grn, 5. outside so, 6. Returnable Chalan GRN'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_delivery_date` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_delivery_date` CHANGE `used_qty` `used_qty` VARCHAR(100) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_trn` ADD `sales_order_id` INT(11) NOT NULL DEFAULT '0' , ADD `customer_id` INT(11) NOT NULL DEFAULT '0'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_group` ADD `group_priority` INT NOT NULL");

  $query_invoicetype = $dbcon->query("TRUNCATE TABLE `tbl_common_mst`");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_common_mst` (
    `common_mst_id` int(11) NOT NULL AUTO_INCREMENT,
    `common_category_id` int(11) NOT NULL,
    `common_mst_name` varchar(255) NOT NULL,
    `common_mst_desc` longtext NOT NULL,
    `isactive` tinyint(4) NOT NULL DEFAULT '1',
    `isdelete` tinyint(4) NOT NULL DEFAULT '0',
    `cdate` timestamp NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `usertype_id` int(11) NOT NULL,
    `is_deletable` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-yes,1-no',
    PRIMARY KEY (`common_mst_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_common_mst` (`common_mst_id`, `common_category_id`, `common_mst_name`, `common_mst_desc`, `isactive`, `isdelete`, `cdate`, `user_id`, `company_id`, `usertype_id`, `is_deletable`) VALUES
  (1, 3, 'SALES', '', 1, 0, '2021-06-23 09:03:04', 2, 1, 2, 1),
  (2, 3, 'PURCHASE', '', 1, 0, '2021-06-23 09:03:13', 2, 1, 2, 1),
  (3, 3, 'PURCHASE RETURN', '', 1, 0, '2021-06-23 09:03:25', 2, 1, 2, 1),
  (6, 6, 'DOMESTIC COMPANY', '', 1, 0, '2021-06-24 06:41:17', 2, 1, 2, 1),
  (7, 6, 'PARTNERSHIP FIRM', '', 1, 0, '2021-06-24 06:41:40', 2, 1, 2, 1),
  (8, 6, 'GOVT. BODY', '', 1, 0, '2021-06-24 06:42:09', 2, 1, 2, 1),
  (10, 12, 'CASH', '', 1, 0, '2021-07-08 05:54:30', 2, 1, 2, 1),
  (11, 12, 'CHEQUE', '', 1, 0, '2021-07-08 05:54:38', 2, 1, 2, 1),
  (12, 12, 'RTGS', '', 1, 0, '2021-07-08 05:54:47', 2, 1, 2, 1),
  (13, 12, 'UPI', '', 1, 0, '2021-07-08 05:54:55', 2, 1, 2, 1),
  (14, 12, 'IMPS', '', 1, 0, '2021-07-08 05:55:37', 2, 1, 2, 1),
  (15, 14, 'GST 5%', '', 1, 0, '2021-07-08 12:14:27', 2, 1, 2, 1),
  (16, 14, 'GST 12%', '', 1, 0, '2021-07-08 12:14:39', 2, 1, 2, 1),
  (17, 14, 'GST 18%', '', 1, 0, '2021-07-08 12:15:15', 2, 1, 2, 1),
  (18, 14, 'EXEMPT', '', 1, 0, '2021-07-08 12:15:47', 2, 1, 2, 1),
  (29, 3, 'SALES RETURN', '', 1, 0, '2021-07-30 09:55:26', 2, 1, 2, 1),
  (30, 21, 'SUPPLY', '', 1, 0, '2021-08-03 05:41:43', 2, 1, 2, 1),
  (31, 21, 'JOB WORK', '', 1, 0, '2021-08-03 05:41:55', 2, 1, 2, 1),
  (32, 21, 'EXPORT', '', 1, 0, '2021-08-03 05:42:29', 2, 1, 2, 1),
  (33, 22, 'REGULAR', '', 1, 0, '2021-08-03 05:42:54', 2, 1, 2, 1),
  (34, 22, 'BILL TO SHIP TO', '', 1, 0, '2021-08-03 05:43:11', 2, 1, 2, 1),
  (35, 23, 'ROAD', '1', 1, 0, '2021-08-09 12:33:05', 2, 1, 2, 1),
  (36, 23, 'RAIL', '2', 1, 0, '2021-08-09 12:32:18', 2, 1, 2, 1),
  (37, 23, 'IN TRANSIT', '5', 1, 0, '2021-08-09 12:32:46', 2, 1, 2, 1),
  (38, 23, 'SHIP', '4', 1, 0, '2021-08-09 12:32:02', 2, 1, 2, 1),
  (42, 25, 'SUPPLY', '', 1, 1, '2021-08-09 07:14:17', 2, 1, 2, 1),
  (43, 25, 'EXPORT', '', 1, 1, '2021-08-09 07:14:26', 2, 1, 2, 1),
  (44, 25, 'JOB WORK', '', 1, 1, '2021-08-09 07:14:38', 2, 1, 2, 1),
  (45, 25, 'FOR OWN USE', '', 1, 1, '2021-08-09 07:14:49', 2, 1, 2, 1),
  (46, 25, 'OTHERS', '', 1, 1, '2021-08-09 07:14:58', 2, 1, 2, 1),
  (51, 25, 'SUPPLY', '1', 1, 0, '2021-08-09 12:09:42', 2, 1, 2, 1),
  (52, 25, 'EXPORT', '3', 1, 0, '2021-08-09 12:09:57', 2, 1, 2, 1),
  (53, 25, 'JOB WORK', '4', 1, 0, '2021-08-09 12:10:12', 2, 1, 2, 1),
  (54, 25, 'FOR OWN USE', '5', 1, 0, '2021-08-09 12:10:33', 2, 1, 2, 1),
  (55, 25, 'OTHERS', '8', 1, 0, '2021-08-09 12:10:48', 2, 1, 2, 1),
  (56, 25, 'SKD/CKD', '9', 1, 0, '2021-08-09 12:13:10', 2, 1, 2, 1),
  (57, 25, 'LINE SALES', '11', 1, 0, '2021-08-09 12:14:46', 2, 1, 2, 1),
  (58, 25, 'RECIPIENT NOT KNOWN', '11', 1, 0, '2021-08-09 12:15:01', 2, 1, 2, 1),
  (59, 25, 'EXHIBITION OR FAIRS', '12', 1, 0, '2021-08-09 12:16:37', 2, 1, 2, 1),
  (60, 27, 'SUPPLY', '1', 1, 0, '2021-08-09 12:16:50', 2, 1, 2, 1),
  (61, 27, 'IMPORT', '2', 1, 0, '2021-08-09 12:17:01', 2, 1, 2, 1),
  (62, 27, 'FOR OWN USE', '5', 1, 0, '2021-08-09 12:17:16', 2, 1, 2, 1),
  (63, 27, 'JOB WORK RETURNS', '6', 1, 0, '2021-08-09 12:17:31', 2, 1, 2, 1),
  (64, 27, 'SALES RETURN', '7', 1, 0, '2021-08-09 12:17:52', 2, 1, 2, 1),
  (65, 27, 'OTHERS', '8', 1, 0, '2021-08-09 12:18:03', 2, 1, 2, 1),
  (66, 27, 'SKD/CKD', '9', 1, 0, '2021-08-09 12:18:16', 2, 1, 2, 1),
  (67, 27, 'EXHIBITION OR FAIRS', '12', 1, 0, '2021-08-09 12:18:31', 2, 1, 2, 1),
  (68, 23, 'AIR', '3', 1, 0, '2021-08-09 12:33:43', 2, 1, 2, 1),
  (69, 29, 'NOT APPLICABLE', 'GST Not Applicable on this transaction', 1, 0, '2021-08-18 07:22:02', 2, 1, 2, 1),
  (70, 29, 'REGISTERED EXPENSE (B2B)', 'GST paid expense from Reg. suppliers with B2B invoice (Your name & GST mentioned on invoice). will be reflected in GSTR-2 as B2B supplu inward , input available', 1, 0, '2021-08-18 07:21:16', 2, 1, 2, 1),
  (71, 29, 'RCM EXPENSE', 'Expense on Which RCM is applicable like ''Transportation charges''(All RCM Entries will be consolidated at day end to generate ''consolidated RCM Payable'')', 1, 0, '2021-08-18 07:20:31', 2, 1, 2, 1),
  (72, 29, 'REFUND AGAINST ADVANCE RECEIPT', 'Refund of advance received from customer', 1, 0, '2021-08-18 07:20:05', 2, 1, 2, 1),
  (73, 29, 'GST PAYMENT TO GOVERNMENT', 'Payment of GST to government', 1, 0, '2021-08-18 07:19:15', 2, 1, 2, 1),
  (74, 29, 'TAX PAID EXPENSE (B2C)', 'GST Paid expense from Reg. supplierwith B2c Invoice (Your name & GST mentioned in invoice) like food invoice from restaturants. No input , No RCM', 1, 0, '2021-08-18 07:17:54', 2, 1, 2, 1),
  (75, 29, 'EXEMPT EXPENSE', 'Fully exempt expense like ''Books & Periodicals'' or otherwise taxable expenses but this transaction is exempt like ''transportation charges'' less than a specified amount', 1, 0, '2021-08-18 07:16:45', 2, 1, 2, 1),
  (76, 29, 'COMPOSITION EXPENSE', 'Expense Booked through composition dealer . No input , No RCM', 1, 0, '2021-08-18 07:13:37', 2, 1, 2, 1),
  (77, 31, 'GST TAX ADJUSTMENT', 'gst tax adjsutment like input - output adjustment , cross adjustment , RCM TO regular , ITC reversal/Redeem etc.', 1, 0, '2021-08-18 07:12:11', 2, 1, 2, 1),
  (78, 29, 'NON GST EXPENSE', 'All Non -GST Expense like electricity , petrol & diesel', 1, 0, '2021-08-18 07:11:10', 2, 1, 2, 1),
  (79, 31, 'CR.NOTE RECEIVED AGAINST SALE', 'credit note issued to customer against supply outward', 1, 0, '2021-08-18 07:08:44', 2, 1, 2, 1),
  (80, 31, 'DR.NOTE RECEIVED AGAINST SALE', '', 1, 0, '2021-08-18 07:06:55', 2, 1, 2, 1),
  (81, 31, 'CONSOLIDATED RCM PAYABLE', 'To generate ''consolidated RCM Payable'' for a day based on vouchers fed as '' RCM Applicable expense ''. It is recomended to generate voucher.', 1, 0, '2021-08-18 07:06:34', 2, 1, 2, 1),
  (82, 3, 'PAYMENT', '', 1, 0, '2021-08-13 04:53:56', 2, 1, 2, 1),
  (83, 3, 'RECEIPT', '', 1, 0, '2021-08-13 04:55:03', 2, 1, 2, 1),
  (84, 3, 'JV', '', 1, 0, '2021-08-13 04:55:13', 2, 1, 2, 1),
  (85, 3, 'CONTRA', '', 1, 0, '2021-08-13 04:55:23', 2, 1, 2, 1),
  (86, 33, 'DR.NOTE RECEIVED AGAINST PURCHASE ', 'debit note received from supplier against supplier inward', 1, 0, '2021-08-18 07:07:39', 2, 1, 2, 1),
  (87, 34, 'CR. NOTE RECEIVED AGAINST PURCHASE ', 'credit note received from supplier against supplier inward', 1, 0, '2021-08-18 07:10:42', 2, 1, 2, 1),
  (88, 31, 'NONE GST  EXPENSE ', '0', 1, 0, '2021-08-18 07:11:40', 2, 1, 2, 1),
  (89, 31, 'COMPOSITION EXPENSE ', 'Expense Booked through composition dealer . No input , No RCM', 1, 0, '2021-08-18 07:14:00', 2, 1, 2, 1),
  (90, 31, 'EXEMPT EXPENSE ', 'Fully exempt expense like ''Books & Periodicals'' or otherwise taxable expenses but this transaction is exempt like ''transportation charges'' less than a specified amount', 1, 0, '2021-08-18 07:17:11', 2, 1, 2, 1),
  (91, 31, 'TAX PAID EXPENSE (B2C)', 'GST Paid expense from Reg. supplierwith B2c Invoice (Your name & GST mentioned in invoice) like food invoice from restaturants. No input , No RCM', 1, 0, '2021-08-18 07:18:21', 2, 1, 2, 1),
  (92, 31, 'GST PAYMENT TO GOVERMENT ', '0', 1, 0, '2021-08-18 07:19:32', 2, 1, 2, 1),
  (93, 31, 'RCM EXPENSE ', 'Expense on Which RCM is applicable like ''Transportation charges''(All RCM Entries will be consolidated at day end to generate ''consolidated RCM Payable'')', 1, 0, '2021-08-18 07:20:44', 2, 1, 2, 1),
  (94, 31, 'REGISTERED EXPENSE (B2B)', 'GST paid expense from Reg. suppliers with B2B invoice (Your name & GST mentioned on invoice). will be reflected in GSTR-2 as B2B supplu inward , input available', 1, 0, '2021-08-18 07:21:40', 2, 1, 2, 1),
  (95, 30, 'NOT APPLICABLE ', 'GST Not Applicable on this transaction', 1, 0, '2021-08-18 07:22:28', 2, 1, 2, 1),
  (96, 31, 'NOT APPLICABLE ', 'GST Not Applicable on this transaction', 1, 0, '2021-08-18 07:22:42', 2, 1, 2, 1),
  (97, 33, 'NOT APPLICABLE ', 'GST Not Applicable on this transaction', 1, 0, '2021-08-18 07:22:55', 2, 1, 2, 1),
  (98, 34, 'NOT APPLICABLE ', 'GST Not Applicable on this transaction', 1, 0, '2021-08-18 07:23:08', 2, 1, 2, 1),
  (99, 30, 'ADVANCE RECEIPT ', 'Advance REceipt from customer which may attract GST if supply is not made with same tax period', 1, 0, '2021-08-18 07:24:43', 2, 1, 2, 1),
  (100, 33, 'CR. NOTE ISSUED AGAINST SALE ', '', 1, 0, '2021-08-18 07:25:25', 2, 1, 2, 1),
  (101, 34, 'DR. NOTE ISSUED AGAINST SALE ', 'debit note issued to customer against supply outward', 1, 0, '2021-08-18 07:25:51', 2, 1, 2, 1),
  (102, 6, 'INDIVIDUAL - RESIDENTS', '0', 1, 0, '2021-08-18 10:01:06', 2, 1, 2, 1),
  (103, 6, 'INDIVIDUAL - NON RESIDENTS', '0', 1, 0, '2021-08-18 10:01:52', 2, 1, 2, 1),
  (104, 6, 'HINDU - UNVIDIDED FAMILY', '0', 1, 0, '2021-08-18 10:02:44', 2, 1, 2, 1),
  (105, 6, 'ASSOCIATION OF PERSONS', '0', 1, 0, '2021-08-18 10:03:09', 2, 1, 2, 1),
  (106, 6, 'BODY OF INDIVIDUALS', '0', 1, 0, '2021-08-18 10:03:38', 2, 1, 2, 1),
  (107, 6, 'CO-OPERATIVE SOCIETY ', '0', 1, 0, '2021-08-18 10:03:59', 2, 1, 2, 1),
  (108, 6, 'TRUST ', '0', 1, 0, '2021-08-18 10:04:10', 2, 1, 2, 1),
  (109, 6, 'FOREIGN COMPANY ', '0', 1, 0, '2021-08-18 10:07:17', 2, 1, 2, 1),
  (110, 36, 'SALE PURCHASE RETURN', '0', 1, 0, '2021-09-13 05:25:12', 2, 1, 2, 1),
  (111, 36, 'POST SALE/PURCHASE DISCOUNT', '0', 1, 0, '2021-09-13 05:25:25', 2, 1, 2, 1),
  (112, 36, 'DEFICIENCY IN SERVICE', '0', 1, 0, '2021-09-13 05:25:52', 2, 1, 2, 1),
  (113, 36, 'CORRECTION IN INVOICE', '0', 1, 0, '2021-09-13 05:26:10', 2, 1, 2, 1),
  (114, 36, 'CHANGE IN POS', '0', 1, 0, '2021-09-13 05:26:20', 2, 1, 2, 1),
  (115, 36, 'FINALIZATION OF PROVISIONAL ASSESMENT', '0', 1, 0, '2021-09-13 05:26:40', 2, 1, 2, 1),
  (116, 36, 'OTHER', '0', 1, 0, '2021-09-13 05:27:26', 2, 1, 2, 1),
  (117, 37, 'REGISTERED', '0', 1, 0, '2021-09-13 05:37:45', 2, 1, 2, 1),
  (118, 37, 'REGISTERED', '0', 1, 0, '2021-09-13 05:38:47', 2, 1, 2, 1),
  (119, 37, 'UNREGISTERED', '0', 1, 0, '2021-09-13 05:38:58', 2, 1, 2, 1),
  (120, 37, 'COMPOSITION DEALER', '0', 1, 0, '2021-09-13 05:39:11', 2, 1, 2, 1),
  (121, 37, 'DEALER', '0', 1, 0, '2021-09-13 05:39:21', 2, 1, 2, 1),
  (124, 3, 'PO VOUCHER', '0', 1, 0, '2021-09-27 07:08:51', 1, 1, 2, 1),
  (125, 3, 'SO VOUCHER', '0', 1, 0, '2021-09-27 07:15:27', 1, 1, 2, 1),
  (126, 3, 'QUOTATION', '0', 1, 0, '2021-10-06 06:42:29', 1, 1, 2, 1),
  (128, 3, 'COMMON VOUCHER', '0', 1, 1, '2021-10-20 10:13:47', 1, 1, 2, 1),
  (129, 10000, 'OI', '0', 1, 1, '2021-10-20 13:02:42', 1, 1, 2, 1),
  (130, 4, 'QUOTATION', '0', 1, 1, '2021-10-20 13:03:57', 1, 1, 2, 1),
  (131, 13, 'COMPULSARY', '', 1, 0, '2021-12-22 12:07:49', 1, 1, 2, 0),
  (132, 13, 'SERVICE IMPORT', '', 1, 0, '2021-12-22 12:08:09', 1, 1, 2, 0),
  (133, 13, 'BASED ON DAILY LIMIT', '', 1, 0, '2021-12-22 12:08:27', 1, 1, 2, 0),
  (134, 13, 'NOT APPLICABLE', '', 1, 0, '2021-12-22 12:08:44', 1, 1, 2, 0),
  (135, 15, 'INPUT GOODS', '', 1, 0, '2022-01-19 06:25:22', 1, 1, 2, 0),
  (136, 15, 'INPUT SERVICES', '', 1, 0, '2022-01-19 06:25:29', 1, 1, 2, 0),
  (137, 15, 'CAPITAL GOODS', '', 1, 0, '2022-01-19 06:25:37', 1, 1, 2, 0),
  (138, 15, 'NONE', '', 1, 0, '2022-01-19 06:25:43', 1, 1, 2, 0)");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '1' WHERE `tbl_group`.`g_id` = 13");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '2' WHERE `tbl_group`.`g_id` = 22");
  //hardi end

  //sanat start
  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '3' WHERE `tbl_group`.`g_id` = 15");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '4' WHERE `tbl_group`.`g_id` = 18");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '5' WHERE `tbl_group`.`g_id` = 21");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '6' WHERE `tbl_group`.`g_id` = 14");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '7' WHERE `tbl_group`.`g_id` = 25");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '8' WHERE `tbl_group`.`g_id` = 24");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '9' WHERE `tbl_group`.`g_id` = 16");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '10' WHERE `tbl_group`.`g_id` = 20");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '11' WHERE `tbl_group`.`g_id` = 19");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_group` SET `group_priority` = '12' WHERE `tbl_group`.`g_id` = 153");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_ledger` SET `l_group` = '24' WHERE `tbl_ledger`.`l_name` = 'PURCHASE ACCOUNT'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_trn` ADD `rate_unit` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_group` (`g_id`, `g_name`, `g_pid`, `g_type`, `g_open_balance`, `g_status`, `g_description`, `balance_typeid`, `cdate`, `user_id`, `company_id`, `is_deletable`, `emp_id`, `form_id`, `branch_id`, `group_start_series`, `group_format`, `format_value`, `end_format_value`, `group_priority`) VALUES ('1000', 'PROFIT & LOSS', '0', '', '0', '0', '', '0', '2022-01-19 12:28:56', '1', '1', '0', '0', '', '0', '0', '3', 'PL/', '/2021-22', '12')");
  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_ledger` (`l_id`, `l_name`, `ledger_code`, `l_code_id`, `emp_profile_img`, `l_group`, `l_form`, `countryid`, `stateid`, `cityid`, `cust_pincode`, `common_email_id`, `m_name`, `m_address`, `m_pan`, `company_name`, `cust_cont_name`, `cust_mobile`, `cust_email`, `cust_website`, `zone_id`, `cust_assign_user`, `branch_id_customer`, `party_sez`, `branch_id_employee`, `cust_remark`, `gst_no`, `party_type`, `cust_gst_reg`, `pay_terms`, `pay_method`, `bill_type`, `balance_typeid`, `acc_type`, `bankid`, `branch_name`, `acc_name`, `acc_number`, `acc_chequeno`, `acc_chequeleft`, `emp_mobile`, `emp_email`, `emp_password`, `emp_zone_id`, `emp_user_type`, `tax_value`, `opn_balance`, `usertype_terr`, `alloc_stateid`, `alloc_cityid`, `report_to_user_type`, `report_to_user_id`, `cdate`, `user_id`, `company_id`, `l_status`, `print_priority`, `cust_id`, `employee_id`, `branch_id`, `credit_limit`, `credit_days`, `is_deletable`, `ledger_alias`, `enable_multi_currency_opening`, `enable_branch_opening`, `ledger_opening_balance_type`, `enable_cost_center`, `enable_tds`, `party_pay_cat`, `enable_tcs`, `enable_depreciation`, `enable_monthly_budget`, `ledger_Tax_type`, `ledger_gst_applicable`, `ledger_tax_category`, `ledger_hsn`, `ledger_itc`, `ledger_rcm`, `enable_bill_sunfry`, `enable_sez`, `enable_cheque_deposit`, `enable_billbybill_opening`, `default_sundry`, `enable_salesman`, `default_ledger`, `shift_time`, `emp_signature_img`) VALUES (NULL, 'Profit & Loss', '', '0', '', '1000', '', '101', '1', '1', '', '', '', '0', '', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '', '', '0', '1', '0', '0', '0', '0', '0', '0.00', '0', '0', '0', '', '0', '0', '0.00', '0.00', '', '', '', '', '', '2021-09-24 10:42:46', '1', '1', '0', '0', '0', '0', '0', '0', '0', '1', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', '')");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `customer_id` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission`  ADD `vipul_copper_permission` INT NOT NULL  AFTER `jr_fiber_glass_permission`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_price_list` ADD `version_relase` INT NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `tax_editable` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `work_order_reserve_temp` ADD `customer_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `customer_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_page_permission`  ADD `crm_partymst_c_add_address` INT NOT NULL  AFTER `crm_partymst_t_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `grn_diff_from_po` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `cgst` DOUBLE(10,2) NOT NULL AFTER `enable_bill_adjustment`, ADD `sgst` DOUBLE(10,2) NOT NULL AFTER `cgst`, ADD `igst` DOUBLE(10,2) NOT NULL AFTER `sgst`, ADD `tcs` DOUBLE(10,2) NOT NULL AFTER `igst`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_po_grn_used` ADD `conv_used_qty` VARCHAR(100) NOT NULL AFTER `used_qty`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pono` ADD `cgst` DOUBLE(10,2) NOT NULL , ADD `sgst` DOUBLE(10,2) NOT NULL , ADD `igst` DOUBLE(10,2) NOT NULL , ADD `tds` DOUBLE(10,2) NOT NULL");


  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `mrp_process_reserve_temp` (
    `mrp_process_reserve_temp_id` int(11) NOT NULL AUTO_INCREMENT,
    `rp_id` int(11) NOT NULL,
    `process_id` int(11) NOT NULL,
    `godown_id` int(11) NOT NULL,
    `qty` varchar(111) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `cdate` date NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) DEFAULT '0',
    PRIMARY KEY (`mrp_process_reserve_temp_id`),
    KEY `branch_id` (`branch_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("UPDATE `branch_mst` SET `company_id` = '1' WHERE `branch_mst`.`branch_id` = 1000");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn` ADD `customer_id` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `resource_time` INT(11) NOT NULL COMMENT '1= day , o = minute'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `shift_count` INT(11) NOT NULL AFTER `resource_time`, ADD `shift_days` VARCHAR(255) NOT NULL AFTER `shift_count`");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `days_mst` (
    `days_id` int(11) NOT NULL AUTO_INCREMENT,
    `days_name` varchar(225) NOT NULL,
    `status` int(11) NOT NULL,
    PRIMARY KEY (`days_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8");




  $query_invoicetype = $dbcon->query("INSERT INTO `days_mst` (`days_id`, `days_name`, `status`) VALUES
  (1, 'Mon', 1),
  (2, 'Tue', 1),
  (3, 'Wed', 1),
  (4, 'Thu', 1),
  (5, 'Fri', 1),
  (6, 'Sat', 1),
  (7, 'Sun', 1)");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_ledger` ADD `territory_id` INT NOT NULL AFTER `emp_signature_img`");
  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`) VALUES (NULL, 'INHOUSE GRN', '428', '0', '41', '3', 'GRN/INH/', '/19-20', '1', '0', '2021-04-01 05:30:00', '1', '2', '1', '0')");


  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`) VALUES (NULL, 'OUTSIDE GRN', '428', '0', '42', '3', 'GRN/OUT/', '/19-20', '1', '0', '2021-04-01 05:30:00', '1', '2', '1', '0')");


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `so_description_required` INT NOT NULL  AFTER `grn_diff_from_po`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `direct_po_create` INT NOT NULL AFTER `po_work_order_wise`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_ledger` CHANGE `cust_gst_reg` `cust_gst_reg` INT(11) NOT NULL COMMENT '0:Registered,1:Unregistered,2:Composition,3:Govt.body,4:UIN Holder'");


  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_work_order_auto_mrp_trn` (
    `work_order_auto_mrp_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `work_order_id` int(11) NOT NULL,
    `question_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL,
    PRIMARY KEY (`work_order_auto_mrp_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn`  ADD `prev_sales_ordertrn_id` INT NOT NULL  AFTER `returnable_status`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `production_branch_id` INT NOT NULL AFTER `prev_sales_ordertrn_id`");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_grn_rp_id_wise_trn` (
    `grn_rp_id_wise_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `rp_id` int(11) NOT NULL,
    `grn_trn_sub_id` int(11) NOT NULL,
    `purchaseordertrn_id` int(11) NOT NULL,
    `jobwork_id` int(11) NOT NULL,
    `job_work_trn_id` int(11) NOT NULL,
    `job_work_sub_trn_id` int(11) NOT NULL,
    `process_allocate_id` int(11) NOT NULL,
    `product_qty` varchar(100) NOT NULL,
    `product_stock_used_qty` varchar(110) NOT NULL,
    `product_base_unit` int(11) NOT NULL,
    `product_conv_qty` varchar(100) NOT NULL,
    `product_conv_unit` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL DEFAULT '0',
    `purchase_status` int(11) NOT NULL,
    `purchase_qty` varchar(110) NOT NULL,
    `job_work_po_trn_id` int(11) NOT NULL,
    `returnable_trn_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    PRIMARY KEY (`grn_rp_id_wise_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_req_trn` ADD `used_conv_qty` VARCHAR(100) NOT NULL AFTER `used_qty`, ADD `base_unit` INT NOT NULL");
  $query_invoicetype = $dbcon->query("AFTER `used_conv_qty`, ADD `conv_unit` INT NOT NULL AFTER `base_unit`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal_item` ADD `remove_from_grn` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes'");
  $query_invoicetype = $dbcon->query("INSERT INTO `print_type_mst` (`id`, `print_type_name`, `print_status`, `cdate`, `user_id`, `company_id`, `branch_id`) VALUES (NULL, 'Purchase Bill', '0', CURRENT_TIMESTAMP, '1', '1', '1')");
  $query_invoicetype = $dbcon->query("UPDATE `menu_master_access` SET `parent_id` = '26', `menu_path` = 'inventory/stock_list', `updated_at` = NULL WHERE `menu_master_access`.`id` = 435");
  $query_invoicetype = $dbcon->query("UPDATE `menu_master_access_routes` SET `route_path_name` = 'inventory/stock_list' WHERE `menu_master_access_routes`.`id` = 847");
  $query_invoicetype = $dbcon->query("UPDATE `menu_master_access_routes` SET `route_path_name` = 'inventory/stock_add' WHERE `menu_master_access_routes`.`id` = 848");
  $query_invoicetype = $dbcon->query("UPDATE `menu_master_access_routes` SET `route_path_name` = 'inventory/stock_list' WHERE `menu_master_access_routes`.`id` = 849");
  $query_invoicetype = $dbcon->query("UPDATE `menu_master_access_routes` SET `route_path_name` = 'inventory/stock_list' WHERE `menu_master_access_routes`.`id` = 850");
  $query_invoicetype = $dbcon->query("UPDATE `menu_master_access_routes` SET `route_path_name` = 'inventory/stock_list' WHERE `menu_master_access_routes`.`id` = 851");
  $query_invoicetype = $dbcon->query("UPDATE `menu_master_access_routes` SET `route_path_name` = 'inventory/stock_list' WHERE `menu_master_access_routes`.`id` = 852");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_log_po_short_close` ADD `unit_id` INT NOT NULL AFTER `short_close_qty`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_po_shortclose_aprv_log` CHANGE `approve_status` `approve_status` INT(11) NOT NULL COMMENT '1=Approved,2=Disapprove'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `resource_display` INT(11) NOT NULL AFTER `shift_days`, ADD `automrp_display` INT(11) NOT NULL AFTER `resource_display`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_log_po_short_close` CHANGE `aproove_status` `aproove_status` INT(11) NOT NULL COMMENT '1.aproove 2.disaproove'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `batchno_as_grnno` INT NOT NULL");


  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_returnable_chalan_grn_trn` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `returnable_chalan_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `grn_id` int(11) NOT NULL,
    `grn_trn_id` int(11) NOT NULL,
    `product_qty` varchar(100) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `product_conv_qty` varchar(100) NOT NULL,
    `product_conv_unit` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` int(11) NOT NULL,
    `grn_status` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `godown_id` int(11) NOT NULL,
    `rate_unit` int(11) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` CHANGE `purchaseorder_no` `purchaseorder_no` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_reason_mst`  ADD `company_id` INT NOT NULL  AFTER `created_date`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `territory_mst`  ADD `is_delete` INT NOT NULL COMMENT '0:yes,1:no'  AFTER `branch_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process_trn` ADD `grn_trn_sub_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process_material` ADD `status` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_reserve_stock` ADD `grn_trn_sub_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `igst_tax_per` `igst_tax_per` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `sgst_tax_per` `sgst_tax_per` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `cgst_tax_per` `cgst_tax_per` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `igst_tax_rate` `igst_tax_rate` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `sgst_tax_rate` `sgst_tax_rate` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `cgst_tax_rate` `cgst_tax_rate` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn` ADD `vehicle_no` VARCHAR(100) NOT NULL AFTER `receive_datetime`, ADD `mode_dispatch` INT NOT NULL AFTER `vehicle_no`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `product_mst` ADD `cat_no` VARCHAR(150) NOT NULL AFTER `batch_wise_stock_manage`");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_po_item_agains_grn` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `purchaseorder_id` int(11) NOT NULL,
    `purchaseordertrn_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `grn_id` int(11) NOT NULL,
    `grn_trn_id` int(11) NOT NULL,
    `product_qty` varchar(100) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `product_conv_qty` varchar(100) NOT NULL,
    `product_conv_unit` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` int(11) NOT NULL,
    `grn_status` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `godown_id` int(11) NOT NULL,
    `rate_unit` int(11) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `po_valid_date` DATE NOT NULL AFTER `purchaseorder_due_date`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_wororder_product_process` ADD `item_pr_process_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_temp_process_desc` ADD `pr_process_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` ADD `remove_from_grn` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_chalan_grn_trn` ADD `returnable_channal_item_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_po_item_agains_grn` ADD `purchaseordertrn_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` ADD `used_grn_qty` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` ADD `used_grn_conv_qty` INT(11) NOT NULL");
  //sanat end


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('old_query',0,'$date')");
  //common branch update in db log table end
}

//pathik db changies first_branch date 2-3-2022 Start

$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='first_branch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("UPDATE `tbl_company_configuration` SET `direct_po_create`=1 WHERE `direct_po_create`=0");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `round_of` DOUBLE(10,2) NOT NULL AFTER `fob`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pono` ADD `round_of` DOUBLE(10,2) NOT NULL AFTER `tds`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `sales_type` TINYINT(4) NOT NULL DEFAULT '1'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pono` ADD `sales_type` TINYINT(4) NOT NULL DEFAULT '1'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal` ADD `vehicle_no` VARCHAR(150) NOT NULL AFTER `non_returnable_matl`, ADD `mode_dispatch` INT NOT NULL AFTER `vehicle_no`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_jobwork_rate_cardtrn` ADD `process_rate_unit` VARCHAR(110) NOT NULL AFTER `cdate`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_trn` ADD `material_unit` INT NOT NULL AFTER `request_status`, ADD `material_qty` VARCHAR(111) NOT NULL AFTER `material_unit`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `quotation_rate_fixed` INT NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `pro_bom_process` ADD `description` TEXT NOT NULL");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_temp_bom_process_desc` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `bom_id` int(11) NOT NULL,
      `process_id` int(11) NOT NULL,
      `description` longtext NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_jobwork_rate_cardtrn` ADD `unit_id` INT NOT NULL AFTER `process_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_jobwork_rate_card` ADD `remark` TEXT NOT NULL AFTER `branch_id`, ADD `terms_condition` TEXT NOT NULL AFTER `remark`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_jobwork_rate_card` ADD `quot_ref` VARCHAR(250) NOT NULL AFTER `jobwork_card_date`");

  $query_invoicetype = $dbcon->query("INSERT INTO `print_type_mst` (`id`, `print_type_name`, `print_status`, `cdate`, `user_id`, `company_id`, `branch_id`) VALUES ('16', 'jobwork rate card print', '0', CURRENT_TIMESTAMP, '1', '1', '1')");


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_trn` ADD `product_scrap_id` INT(11) NOT NULL , ADD `scrap_unit` INT(11) NOT NULL , ADD `scrap_qty` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `product_scrap_id` INT(11) NOT NULL , ADD `scrap_unit` INT(11) NOT NULL , ADD `scrap_qty` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `is_scrap` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes'");


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `cgst_tax_per` `cgst_tax_per` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `sgst_tax_per` `sgst_tax_per` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `igst_tax_per` `igst_tax_per` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `automatic_approval` INT NOT NULL AFTER `quotation_rate_fixed`");

  $query_invoicetype = $dbcon->query("TRUNCATE `email_module_list`");
  $query_invoicetype = $dbcon->query("TRUNCATE `email_merge_fields`");

  $query_invoicetype = $dbcon->query("INSERT INTO `email_module_list` (`email_module_id`, `name`, `status`, `user_id`, `cdate`, `company_id`) VALUES
      (1, 'GENERAL', 0, 1, '2020-12-28 16:37:07', 1),
      (2, 'CRM', 0, 1, '2020-12-28 16:37:11', 1),
      (3, 'PURCHASE', 0, 1, '2021-08-12 11:10:38', 1),
      (4, 'PROFORMA', 0, 1, '2021-12-25 14:57:02', 1),
      (5, 'SALES ORDER', 0, 1, '2021-12-27 18:24:05', 1);

      ");

  $query_invoicetype = $dbcon->query("INSERT INTO `email_merge_fields` (`email_merge_id`, `field_name`, `table_name`, `replace_with`, `primary_id`, `module_id`, `status`, `user_id`, `cdate`, `company_id`) VALUES
      (1, 'CUSTOMER NAME', 'tbl_customer', 'cust_name', 'cust_id', 2, 0, 1, '2021-01-13 18:37:52', 1),
      (2, 'CUSTOMER EMAIL', 'tbl_customer', 'cust_email', 'cust_id', 2, 0, 1, '2021-01-18 14:09:07', 1),
      (3, 'CUSTOMER NAME', 'tbl_ledger', 'l_name', 'l_id', 3, 0, 1, '2021-08-12 11:12:50', 1),
      (4, 'CUSTOMER NAME', 'tbl_ledger', 'l_name', 'l_id', 4, 0, 1, '2021-12-28 11:58:32', 1),
      (5, 'CUSTOMER EMAIL', 'tbl_ledger', 'cust_email', 'l_id', 4, 0, 1, '2021-12-28 11:59:09', 1),
      (6, 'CUSTOMER NAME', 'tbl_ledger', 'l_name', 'l_id', 5, 0, 1, '2021-12-28 11:58:32', 1),
      (7, 'CUSTOMER EMAIL', 'tbl_ledger', 'cust_email', 'l_id', 5, 0, 1, '2021-12-28 11:59:09', 1),
      (8, 'CUSTOMER EMAIL', 'tbl_ledger', 'cust_email', 'l_id', 3, 0, 1, '2021-12-28 11:59:09', 1),
      (9, 'CUSTOMER MOBILE', 'tbl_customer', 'cust_mobile', 'cust_id', 2, 0, 1, '2021-01-18 14:09:07', 1),
      (10, 'CUSTOMER MOBILE', 'tbl_ledger', 'cust_mobile', 'l_id', 3, 0, 1, '2021-12-28 11:59:09', 1),
      (11, 'CUSTOMER MOBILE', 'tbl_ledger', 'cust_mobile', 'l_id', 4, 0, 1, '2021-12-28 11:59:09', 1),
      (12, 'CUSTOMER MOBILE', 'tbl_ledger', 'cust_mobile', 'l_id', 5, 0, 1, '2021-12-28 11:59:09', 1),
      (13, 'INQUIRY DATE', 'tbl_inquiry', 'inquiry_date', 'inquiry_id', 2, 0, 1, '2021-01-18 14:09:07', 1),
      (14, 'INQUIRY NO', 'tbl_inquiry', 'inquiry_no', 'inquiry_id', 2, 0, 1, '2022-02-23 15:36:45', 1),
      (15, 'QUOTATION NO', 'tbl_quotation', 'quotation_no', 'quotation_id', 2, 0, 1, '2022-02-23 15:37:36', 1),
      (16, 'QUOTATION DATE', 'tbl_quotation', 'quotation_date', 'quotation_id', 2, 0, 1, '2022-02-23 15:38:25', 1),
      (17, 'SALES ORDER NO', 'tbl_sales_order', 'sales_order_no', 'sales_order_id', 5, 0, 1, '2022-02-23 15:39:03', 1),
      (18, 'SALES ORDER DATE', 'tbl_sales_order', 'sales_order_date', 'sales_order_id', 5, 0, 1, '2022-02-23 15:39:45', 1),
      (19, 'NEXT FOLLOWUP DATE', 'tbl_task', 'task_due_date', 'task_id', 2, 0, 1, '2022-02-23 15:40:50', 1),
      (20, 'PROFORMA DATE', 'tbl_proforma_invoice', 'invoice_date', 'invoice_id', 4, 0, 1, '2022-02-23 15:43:26', 1),
      (21, 'PROFORMA NO', 'tbl_proforma_invoice', 'invoice_no', 'invoice_id', 4, 0, 1, '2022-02-23 15:42:52', 1),
      (22, 'CUSTOMER ADDRESS', 'tbl_cust_address', 'c_add_address', 'c_add_id', 2, 0, 1, '2022-02-23 16:27:34', 1)");


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` CHANGE `automatic_approval` `automatic_approval_indent` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `automatic_approval_po` INT NOT NULL AFTER `automatic_approval_indent`, ADD `automatic_finance_approval_po` INT NOT NULL AFTER `automatic_approval_po`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `automatic_shortclose_approval_po` INT NOT NULL AFTER `automatic_finance_approval_po`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pre_trn` ADD `sp_id` INT NOT NULL AFTER `pre_trn_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_order`  ADD `short_close_status` INT NOT NULL COMMENT '0:no, 1:yes' ");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn`  ADD `short_close_status` INT NOT NULL COMMENT '0:no, 1:yes' ,  ADD `short_close_product_qty` INT NOT NULL ,  ADD `short_close_conv_qty` INT NOT NULL ,  ADD `short_close_unit_id` INT NOT NULL ,  ADD `short_close_conv_unit_id` INT NOT NULL ");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_req_trn` ADD `rp_id` INT NOT NULL AFTER `purchaseordertrn_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `rp_id` INT(11) NOT NULL DEFAULT '0' ");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `con_type` INT NOT NULL COMMENT '1.unit 2.vendor 3.manual' AFTER `round_of`, ADD `con_vender_id` INT NOT NULL AFTER `con_type`, ADD `con_branch` INT NOT NULL AFTER `con_vender_id`, ADD `con_address` INT NOT NULL AFTER `con_branch`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` CHANGE `con_address` `con_address` TEXT NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `cons_same_as` INT NOT NULL AFTER `con_address`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `automatic_approval_quotation` INT NOT NULL ,  ADD `automatic_approval_proforma` INT NOT NULL ,  ADD `automatic_approval_so` INT NOT NULL ,  ADD `automatic_approval_order_acceptance` INT NOT NULL");






  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('first_branch',0,'$date')");
  //common branch update in db log table end
}
//pathik end

//hardi db changies dev_branch_approve_permission date 2-3-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_branch_approve_permission'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `automatic_approval_quotation` INT NOT NULL ,  ADD `automatic_approval_proforma` INT NOT NULL ,  ADD `automatic_approval_so` INT NOT NULL ,  ADD `automatic_approval_order_acceptance` INT NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_branch_approve_permission',0,'$date')");
  //common branch update in db log table end
}

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sales_card_dev'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_product_party_sales` (
    `party_sales_id` int(11) NOT NULL AUTO_INCREMENT,
    `card_type` int(11) NOT NULL COMMENT '0.vendor wise 1.product wise',
    `sales_card_no` varchar(50) NOT NULL,
    `party_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `product_party_sales_status` int(11) NOT NULL,
    `sales_card_date` date NOT NULL,
    `card_status` int(11) NOT NULL COMMENT '0.active 2.delete',
    `is_aproove` int(11) NOT NULL COMMENT '0.disaproove 1.aproove',
    `is_active` int(11) NOT NULL COMMENT '0.active 1.in-active',
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`party_sales_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_salescardtrn` (
    `salescardtrn_id` int(11) NOT NULL AUTO_INCREMENT,
    `party_sales_id` int(11) NOT NULL,
    `sales_card_id` int(11) NOT NULL DEFAULT '0',
    `sales_type` int(11) NOT NULL DEFAULT '0' COMMENT '0=Vendor,1=Product',
    `vendor_id` int(11) NOT NULL DEFAULT '0',
    `product_id` int(11) NOT NULL DEFAULT '0',
    `currency_id` int(11) NOT NULL,
    `price` double(10,2) NOT NULL DEFAULT '0.00',
    `discount_percentage_value` varchar(255) NOT NULL COMMENT 'discount value',
    `discount_percentage` varchar(100) NOT NULL COMMENT 'discount value in percentag',
    `salescardtrn_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `affected_date` date DEFAULT NULL,
    `valid_date` date NOT NULL,
    `unit_id` int(11) NOT NULL,
    PRIMARY KEY (`salescardtrn_id`),
    KEY `sales_card_id` (`sales_card_id`,`sales_type`,`vendor_id`,`product_id`,`price`),
    KEY `discount_percentage` (`discount_percentage`),
    KEY `currency_id` (`currency_id`),
    KEY `company_id` (`company_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_socard_aprv_log` (
    `socard_aprv_id` int(11) NOT NULL AUTO_INCREMENT,
    `party_sales_id` int(11) NOT NULL,
    `approve_remark` text NOT NULL,
    `approve_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`socard_aprv_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_product_sales_elcon` (
    `elcon_sales_id` int(11) NOT NULL AUTO_INCREMENT,
    `card_type` int(11) NOT NULL COMMENT '0.vendor wise 1.product wise',
    `sales_card_no` varchar(50) NOT NULL,
    `sales_card_date` date NOT NULL,
    `card_status` int(11) NOT NULL COMMENT '0.active 2.delete',
    `is_approve` int(11) NOT NULL COMMENT '0.disapprove 1.approve',
    `is_active` int(11) NOT NULL COMMENT '0.active 1.in-active',
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`elcon_sales_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_salescardelcontrn` (
    `salescardelcontrn_id` int(11) NOT NULL AUTO_INCREMENT,
    `elcon_sales_id` int(11) NOT NULL,
    `sales_type` int(11) NOT NULL DEFAULT '0' COMMENT '0=Vendor,1=Product',
    `product_cat_id` int(11) NOT NULL DEFAULT '0',
    `currency_id` int(11) NOT NULL,
    `price` double(10,2) NOT NULL DEFAULT '0.00',
    `rate1` double(10,2) NOT NULL DEFAULT '0.00',
    `rate2` double(10,2) NOT NULL DEFAULT '0.00',
    `rate3` double(10,2) NOT NULL DEFAULT '0.00',
    `unit_id` int(11) NOT NULL,
    `salescardelcontrn_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`salescardelcontrn_id`),
    KEY `currency_id` (`currency_id`),
    KEY `company_id` (`company_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_elconsocard_aprv_log` (
    `socard_aprv_id` int(11) NOT NULL AUTO_INCREMENT,
    `elcon_sales_id` int(11) NOT NULL,
    `approve_remark` text NOT NULL,
    `approve_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`socard_aprv_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_salescardtrn` CHANGE `sales_card_id` `salescardelcontrn_id` INT(11) NOT NULL");

  $query_invoicetypees = $dbcon->query("INSERT INTO `tbl_module_type` (`module_type_id`, `module_name`, `status`, `cdate`) VALUES (NULL, 'Sales Card', '0', CURRENT_TIMESTAMP)");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `product_mst`  ADD `base_weight` DOUBLE(10,2) NOT NULL ,  ADD `conv_weight` DOUBLE(10,2) NOT NULL");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_salescardelcontrn`  ADD `valid_date` DATE NOT NULL ,  ADD `effected_date` DATE NOT NULL");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sales_card_dev',0,'$date')");
  //common branch update in db log table end
}

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='filter_concept_spe12'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `enable_item_description` INT NOT NULL COMMENT '0:no,1:yes' ,  ADD `enable_item_image` INT NOT NULL COMMENT '0:no,1:yes'");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `product_mst`  ADD `product_alias_name` VARCHAR(1000) NOT NULL");

  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('filter_concept_spe12',0,'$date')");
}

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='filter_concept_spe'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("CREATE TABLE `mst_first_name` (
    `first_name_id` int(10) NOT NULL AUTO_INCREMENT,
    `first_name` varchar(255) NOT NULL,
    `code` varchar(100) NOT NULL,
    `first_name_status` int(10) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(10) NOT NULL,
    `company_id` int(10) NOT NULL,
    `branch_id` int(11) NOT NULL DEFAULT '0',
    `is_deletable` int(11) NOT NULL,
    PRIMARY KEY (`first_name_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE `mst_surface_area` (
    `surface_area_id` int(10) NOT NULL AUTO_INCREMENT,
    `surface_area_name` varchar(255) NOT NULL,
    `code` varchar(100) NOT NULL,
    `surface_area_status` int(10) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(10) NOT NULL,
    `company_id` int(10) NOT NULL,
    `branch_id` int(11) NOT NULL DEFAULT '0',
    `is_deletable` int(11) NOT NULL,
    PRIMARY KEY (`surface_area_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE `mst_impregnation` (
    `impregnation_id` int(10) NOT NULL AUTO_INCREMENT,
    `impregnation_name` varchar(255) NOT NULL,
    `code` varchar(100) NOT NULL,
    `impregnation_status` int(10) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(10) NOT NULL,
    `company_id` int(10) NOT NULL,
    `branch_id` int(11) NOT NULL DEFAULT '0',
    `is_deletable` int(11) NOT NULL,
    PRIMARY KEY (`impregnation_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE `mst_product_model` (
    `product_model_id` int(10) NOT NULL AUTO_INCREMENT,
    `product_model_name` varchar(255) NOT NULL,
    `code` varchar(100) NOT NULL,
    `product_model_status` int(10) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(10) NOT NULL,
    `company_id` int(10) NOT NULL,
    `branch_id` int(11) NOT NULL DEFAULT '0',
    `is_deletable` int(11) NOT NULL,
    PRIMARY KEY (`product_model_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE `mst_installation` (
    `installation_id` int(10) NOT NULL AUTO_INCREMENT,
    `installation_name` varchar(255) NOT NULL,
    `code` varchar(100) NOT NULL,
    `installation_status` int(10) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(10) NOT NULL,
    `company_id` int(10) NOT NULL,
    `branch_id` int(11) NOT NULL DEFAULT '0',
    `is_deletable` int(11) NOT NULL,
    PRIMARY KEY (`installation_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE `mst_type` (
    `type_id` int(10) NOT NULL AUTO_INCREMENT,
    `type_name` varchar(255) NOT NULL,
    `code` varchar(100) NOT NULL,
    `type_status` int(10) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(10) NOT NULL,
    `company_id` int(10) NOT NULL,
    `branch_id` int(11) NOT NULL DEFAULT '0',
    `is_deletable` int(11) NOT NULL,
    PRIMARY KEY (`type_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE `mst_typefi` (
    `type_id` int(11) NOT NULL AUTO_INCREMENT,
    `type_name` varchar(250) NOT NULL,
    `code` varchar(100) NOT NULL,
    `type_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `is_deletable` int(11) NOT NULL,
    PRIMARY KEY (`type_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE `mst_cartridge` (
    `cartridge_id` int(11) NOT NULL AUTO_INCREMENT,
    `cartridge_name` varchar(250) NOT NULL,
    `code` varchar(100) NOT NULL,
    `cartridge_status` int(11) NOT NULL,
    `cdate` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `is_deletable` int(11) NOT NULL,
    PRIMARY KEY (`cartridge_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  $query_invoicetypees = $dbcon->query("CREATE TABLE `mst_class` (
    `class_id` int(11) NOT NULL AUTO_INCREMENT,
    `class_name` varchar(250) NOT NULL,
    `code` varchar(100) NOT NULL,
    `class_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `is_deletable` int(11) NOT NULL,
    PRIMARY KEY (`class_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission`  ADD `filter_concept_permission` INT NOT NULL  AFTER `vipul_copper_permission`");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `inquiry_user_lock` INT NOT NULL COMMENT '0:inquiry, 1:user'");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `users`  ADD `user_lock` INT NOT NULL  AFTER `user_tmst`,  ADD `user_lock_date` DATETIME NULL DEFAULT NULL  AFTER `user_lock`");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `mst_inquiry_type` (
    `inquiry_type_id` int(11) NOT NULL AUTO_INCREMENT,
    `inquiry_type_name` varchar(250) NOT NULL,
    `status` int(11) NOT NULL COMMENT '0:active, 2:delete',
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`inquiry_type_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("INSERT INTO `mst_inquiry_type` (`inquiry_type_id`, `inquiry_type_name`, `status`, `cdate`, `user_id`, `company_id`) VALUES (NULL, 'Product Wise', '0', '2022-03-15 15:52:44', '1', '1'), (NULL, 'Project Wise', '0', '2022-03-15 15:54:15', '1', '1')");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_task`  ADD `quotation_followup_lock` INT NOT NULL COMMENT '0=unlock,1=locked' ,  ADD `followup_start_date` TIMESTAMP NULL DEFAULT NULL ,  ADD `quotation_to_quotation_followup_lock` INT NOT NULL COMMENT '0=unlock,1=locked' ,  ADD `quotation_followup_start_date` TIMESTAMP NULL DEFAULT NULL");


  $query_invoicetypees = $dbcon->query("CREATE TABLE `tbl_userlock_log` (
    `user_log_id` int(11) NOT NULL AUTO_INCREMENT,
    `locked_uname` varchar(200) NOT NULL,
    `user_locked_date` datetime NOT NULL,
    `user_unlock_date` datetime NOT NULL,
    `unlocked_by` varchar(200) NOT NULL,
    `user_locked_reason` varchar(300) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_userlock_log` ADD PRIMARY KEY( `user_log_id`)");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `product_mst`  ADD `first_name_id` INT NOT NULL ,  ADD `product_surface_area` INT NOT NULL ,  ADD `product_impregnation` INT NOT NULL ,  ADD `product_model_name` INT NOT NULL ,  ADD `product_installation` INT NOT NULL ,  ADD `product_mst_type` INT NOT NULL ,  ADD `pro_mst_type` INT NOT NULL ,  ADD `pro_cartridge_mst` INT NOT NULL ,  ADD `pro_class_mst` INT NOT NULL");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_inquiry`  ADD `acknowlegement_sent` SMALLINT NOT NULL ,  ADD `acknowlegement_allow` SMALLINT NOT NULL ,  ADD `acknowlegement_assign_time` DATETIME NULL DEFAULT NULL ,  ADD `acknowlegement_sent_time` DATETIME NULL DEFAULT NULL ,  ADD `type_of_inquiry` SMALLINT NULL DEFAULT NULL ,  ADD `inquiry_project_name` VARCHAR(255) NULL DEFAULT NULL ,  ADD `end_user_details` TEXT NULL DEFAULT NULL ,  ADD `scope_of_work` TEXT NULL DEFAULT NULL ,  ADD `payment_terms` TEXT NULL DEFAULT NULL ,  ADD `delivery_time` DATETIME NULL DEFAULT NULL ,  ADD `estimated_timeline_for_closing` VARCHAR(255) NULL DEFAULT NULL ,  ADD `quotation_required_date` DATETIME NULL DEFAULT NULL");

  $query_invoicetypees = $dbcon->query("CREATE TABLE `tbl_inquiry_pending_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `company_id` int(11) DEFAULT NULL,
    `inquiry_id` int(11) DEFAULT NULL,
    `task_id` int(11) NOT NULL,
    `type` enum('inquiry','quotation') NOT NULL DEFAULT 'inquiry',
    `owner_id` int(11) DEFAULT NULL,
    `branch_id` int(11) DEFAULT '0',
    `assign_user_id` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` smallint(1) NOT NULL DEFAULT '0' COMMENT '0 - Active, 1 - InActive, 2 - Deleted',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('filter_concept_spe',0,'$date')");
  //common branch update in db log table end
}
//hardi end




//Sanat db changies  date 2-3-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='delete_wo_product'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `rp_po_base_qty` INT(11) NOT NULL AFTER `rp_po_qty`");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `in_process_conv_qty` INT(11) NOT NULL AFTER `in_process_qty`");


  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_qc` ADD `qc_qty` INT(11) NOT NULL AFTER `accepted_base_qty`, ADD `qc_unit` INT(11) NOT NULL AFTER `qc_qty`");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_qc_process_trn` ADD `grn_trn_id` INT(11) NOT NULL , ADD `grn_sub_trn_id` INT(11) NOT NULL");


  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_qc_process_trn` ADD `qc_unit` INT(11) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('delete_wo_product',0,'$date')");
  //common branch update in db log table end

}
//Sanat end


//Dhruv db changies  date 2-3-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_d_dhruv_branch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_ledger` ADD `tdstax_cat` INT(11) NOT NULL AFTER `party_pay_cat`");

  $query_invoicetypees = $dbcon->query("UPDATE `tbl_ledger` SET `l_name` = 'TDS(PURCHASE)' WHERE `tbl_ledger`.`l_id` = 24453");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_d_dhruv_branch',0,'$date')");
  //common branch update in db log table end
}
//Dhruv end




//Sanat db changies  date 8-3-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_branch_create_batch_seprate'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_allocate_process` ADD `batch_process_start_time` INT(11) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes'");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_branch_create_batch_seprate',0,'$date')");
  //common branch update in db log table end

}
//Sanat end


//Sanat db changies  date 11-3-2022 Start

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='so_product_qty_type_change'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('so_product_qty_type_change',0,'$date')");
  //common branch update in db log table end

}
//Sanat end

//Sanat db changies  date 8-3-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='work_order_reserve_temp_branch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `work_order_reserve_temp` ADD `sales_ordertrn_id` INT(11) NOT NULL AFTER `customer_id`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('work_order_reserve_temp_branch',0,'$date')");
  //common branch update in db log table end

}
//Sanat end

//Maulik db changies  date 16-3-2022 Start

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='maulik_dev2'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `sales_wise_branch_planning` INT NOT NULL COMMENT '0.no 1.yes' AFTER `quotation_rate_fixed`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('maulik_dev2',0,'$date')");
  //common branch update in db log table end

}
//Maulik end
// hardi start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev123'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_quotation`  ADD `project_name` VARCHAR(500) NOT NULL");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev123',0,'$date')");
  //common branch update in db log table end
}
//pathik db changies  date 21-3-2022 Start

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_eway_bill'";
$result = $dbcon->query($sql);

$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS eway_sub_type (
    eway_sub_type_id int(11) NOT NULL AUTO_INCREMENT,
    eway_sub_type_name varchar(400) NOT NULL,
    code varchar(112) NOT NULL,
    supply_type int(11) NOT NULL COMMENT '1:outward,2:inword',
    status int(11) NOT NULL,
    cdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id int(11) NOT NULL,
    company_id int(11) NOT NULL,
    branch_id int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (eway_sub_type_id),
    KEY process_type_id (eway_sub_type_id,eway_sub_type_name,supply_type,user_id,company_id),
    KEY process_type_id_2 (eway_sub_type_id,eway_sub_type_name,supply_type,user_id,company_id)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  $query_invoicetypees = $dbcon->query("INSERT INTO eway_sub_type (eway_sub_type_id, eway_sub_type_name, code, supply_type, status, cdate, user_id, company_id, branch_id) VALUES
  (1, 'Supply', '1', 1, 0, '2021-10-21 06:07:53', 1, 1, 1),
  (2, 'Export', '3', 1, 0, '2021-10-21 06:07:47', 1, 1, 1),
  (3, 'Job Work\n', '4', 1, 0, '2021-07-15 07:22:20', 1, 1, 10000),
  (4, 'For Own Use', '5', 1, 0, '2022-01-08 10:13:50', 1, 1, 1000),
  (5, 'Others', '8', 1, 0, '2021-07-15 07:22:20', 1, 1, 10000),
  (6, 'SKD/CKD 9\n', '9', 1, 0, '2021-07-15 07:22:20', 1, 1, 10000),
  (7, 'Line Sales', '10', 1, 0, '2021-07-15 07:22:20', 1, 1, 10000),
  (8, 'Recipient Not Known', '11', 1, 0, '2021-10-20 09:36:25', 1, 1, 1000),
  (9, 'Exhibition or Fairs', '12', 1, 0, '2021-11-11 13:43:49', 1, 1, 1000),
  (10, 'Supply', '1', 2, 0, '2021-10-21 06:07:53', 1, 1, 1),
  (11, 'Import', '2', 2, 0, '2021-10-21 06:07:53', 1, 1, 1),
  (12, 'For Own Use', '5', 2, 0, '2021-10-21 06:07:53', 1, 1, 1),
  (13, 'Job work Returns', '6', 2, 0, '2021-10-21 06:07:53', 1, 1, 1),
  (14, 'Sales Return', '7', 2, 0, '2021-10-21 06:07:53', 1, 1, 1),
  (15, 'Others', '8', 2, 0, '2021-10-21 06:07:53', 1, 1, 1),
  (16, 'SKD/CKD', '9', 2, 0, '2021-10-21 06:07:53', 1, 1, 1),
  (17, 'Exhibition or Fairs', '12', 2, 0, '2021-10-21 06:07:53', 1, 1, 1)");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_invoicetype` ADD `gst_code` VARCHAR(110) NOT NULL AFTER `branch_id`");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company` CHANGE `city_name` `city_id` INT NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `ewb_username` VARCHAR(150) NOT NULL ,  ADD `ewb_password` VARCHAR(150) NOT NULL ,  ADD `einv_username` VARCHAR(150) NOT NULL ,  ADD `einv_password` VARCHAR(150) NOT NULL");
  $query_invoicetypees = $dbcon->query("UPDATE `tbl_module_type` SET `module_name` = 'Invoice' WHERE `tbl_module_type`.`module_type_id` = 37");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_invoice`  ADD `einv_Irn` TEXT NOT NULL ,  ADD `einv_AckDate` DATETIME NOT NULL ,  ADD `einv_AckNo` VARCHAR(150) NOT NULL ,  ADD `einv_SignedQRCode` LONGTEXT NOT NULL ,  ADD `einv_SignedInvoice` LONGTEXT NOT NULL ,  ADD `einv_Remarks` TEXT NOT NULL");
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS eway_transport_mode (
    eway_transport_mode_id int(11) NOT NULL AUTO_INCREMENT,
    eway_bill_transport_type varchar(400) NOT NULL,
    gst_code int(11) NOT NULL COMMENT '1:outward,2:inword',
    status int(11) NOT NULL,
    cdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id int(11) NOT NULL,
    company_id int(11) NOT NULL,
    branch_id int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (eway_transport_mode_id),
    KEY process_type_id (eway_transport_mode_id,eway_bill_transport_type,gst_code,user_id,company_id),
    KEY process_type_id_2 (eway_transport_mode_id,eway_bill_transport_type,gst_code,user_id,company_id)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("INSERT INTO eway_transport_mode (eway_transport_mode_id, eway_bill_transport_type, gst_code, status, cdate, user_id, company_id, branch_id) VALUES
  (1, 'Road', 1, 0, '2022-03-21 12:55:34', 1, 1, 1),
  (2, 'Rail', 2, 0, '2022-03-21 12:55:34', 1, 1, 1),
  (3, 'Air', 3, 0, '2022-03-21 12:55:34', 1, 1, 1),
  (4, 'Ship', 4, 0, '2022-03-21 12:55:34', 1, 1, 1),
  (5, 'In Transit', 5, 0, '2022-03-21 12:55:34', 1, 1, 1)");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_eway_bill',0,'$date')");
  //common branch update in db log table end

}
//pathik end
// hardi start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_atlas'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission`  ADD `atlas_permission` INT NOT NULL  AFTER `filter_concept_permission`");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_terms_condition` CHANGE `tc_for` `tc_for` VARCHAR(400) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_atlas',0,'$date')");
  //common branch update in db log table end
}
//pathik product series start 07-04-2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_pros_series'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_product_code_series` ADD `company_id` INT NOT NULL AFTER `pr_code_series`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_pros_series',0,'$date')");
  //common branch update in db log table end
}
//pathik product series start 07-04-2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_post_crm_report'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_customer`  ADD `state_id` INT NOT NULL");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_post_crm_report',0,'$date')");
  //common branch update in db log table end
}
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_dash_report'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `trans_dash_user_type` VARCHAR(500) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `header_logo` VARCHAR(150) NOT NULL COMMENT '0:none,1:left,2:right,3:all' ,  ADD `header_text` VARCHAR(150) NOT NULL COMMENT '0:none,1:left,2:right,3:all'");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_dash_report',0,'$date')");
  //common branch update in db log table end
}

//Maulik end

//Maulik db changies  date 29-3-2022 Start

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='maulik_dev4'";
$result = $dbcon->query($sql);

$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_stock_trn` ADD `base_rate` DOUBLE(10,2) NOT NULL AFTER `used_convert_stock`, ADD `conv_rate` DOUBLE(10,2) NOT NULL AFTER `base_rate`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('maulik_dev4',0,'$date')");
  //common branch update in db log table end

}

//hardi db changes 14/04/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_sales_type'";
$result = $dbcon->query($sql);

$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_sales_order`  ADD `invoicetype_id` INT NOT NULL");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_sales_type',0,'$date')");
  //common branch update in db log table end

}

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_sales_type1'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("UPDATE `tbl_invoicetype` SET `gst_code` = 'INV' WHERE `invoice_type` = 'TAX INVOICE' OR `invoice_type` = 'INVOICE'");
  $query_invoicetypees = $dbcon->query("INSERT INTO `tbl_module_type` (`module_type_id`, `module_name`, `status`, `cdate`) VALUES ('45', 'Sales Order', '0', CURRENT_TIMESTAMP)");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_currency` CHANGE `currency_symbol` `currency_symbol` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_currency`  ADD `currency_rate` DOUBLE(20,2) NOT NULL ,  ADD `isbasecrncy` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0-no,1-yes' ,  ADD `is_deletable` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0-yes,1-no'");
  $query_invoicetypees = $dbcon->query("TRUNCATE TABLE tbl_currency");
  $query_invoicetypees = $dbcon->query("INSERT INTO `tbl_currency` (`currency_id`, `currency_name`, `currency_code`, `currency_symbol`, `currency_status`, `currency_in_word`, `currency_in_word_end`, `currency_rate`, `isbasecrncy`, `is_deletable`) VALUES
  (1, 'Andorran Peseta', 'ADP', '', '0', '', '', 0.00, 0, 0),
  (2, 'United Arab Emirates Dirham', 'AED', 'AED', '0', 'Dinar', 'Dinar', 20.00, 0, 0),
  (3, 'Afghanistan Afghani', 'AFN', '&#1547;', '0', '', '', 0.00, 0, 0),
  (4, 'Albanian Lek', 'ALL', '&#76;&#101;&#107;', '0', '', '', 0.00, 0, 0),
  (5, 'Netherlands Antillian Guilder', 'ANG', '&#402;', '0', '', '', 0.00, 0, 0),
  (6, 'Angolan Kwanza', 'AOK', '', '0', '', '', 0.00, 0, 0),
  (7, 'Argentine Peso', 'ARS', '&#36;', '0', '', '', 0.00, 0, 0),
  (9, 'Australian Dollar', 'AUD', '&#36;', '0', '', '', 0.00, 0, 0),
  (10, 'Aruban Florin', 'AWG', '&#402;', '0', '', '', 0.00, 0, 0),
  (11, 'Barbados Dollar', 'BBD', '&#36;', '0', '', '', 0.00, 0, 0),
  (12, 'Bangladeshi Taka', 'BDT', '', '0', '', '', 0.00, 0, 0),
  (14, 'Bulgarian Lev', 'BGN', '&#1083;&#1074;', '0', '', '', 0.00, 0, 0),
  (15, 'Bahraini Dinar', 'BHD', '', '0', '', '', 0.00, 0, 0),
  (16, 'Burundi Franc', 'BIF', 'FBu', '0', '', '', 0.00, 0, 0),
  (17, 'Bermudian Dollar', 'BMD', '&#36;', '0', '', '', 0.00, 0, 0),
  (18, 'Brunei Dollar', 'BND', '&#36;', '0', '', '', 0.00, 0, 0),
  (19, 'Bolivian Boliviano', 'BOB', '$b&#36;&#98;', '0', '', '', 0.00, 0, 0),
  (20, 'Brazilian Real', 'BRL', '&#82;&#36;', '0', '', '', 0.00, 0, 0),
  (21, 'Bahamian Dollar', 'BSD', '&#36;', '0', '', '', 0.00, 0, 0),
  (22, 'Bhutan Ngultrum', 'BTN', 'Nu.', '0', '', '', 0.00, 0, 0),
  (23, 'Burma Kyat', 'BUK', '&#75;', '0', '', '', 0.00, 0, 0),
  (24, 'Botswanian Pula', 'BWP', '&#80;', '0', '', '', 0.00, 0, 0),
  (25, 'Belize Dollar', 'BZD', '&#66;&#90;&#36;', '0', '', '', 0.00, 0, 0),
  (26, 'Canadian Dollar', 'CAD', '&#36;', '0', '', '', 0.00, 0, 0),
  (27, 'Swiss Franc', 'CHF', '&#67;&#72;&#70;', '0', '', '', 0.00, 0, 0),
  (28, 'Chilean Unidades de Fomento', 'CLF', '&#36;', '0', '', '', 0.00, 0, 0),
  (29, 'Chilean Peso', 'CLP', '&#36;', '0', '', '', 0.00, 0, 0),
  (30, 'Yuan (Chinese) Renminbi', 'CNY', '&#165;', '0', '', '', 0.00, 0, 0),
  (31, 'Colombian Peso', 'COP', '&#36;', '0', '', '', 0.00, 0, 0),
  (32, 'Costa Rican Colon', 'CRC', '&#8353;', '0', '', '', 0.00, 0, 0),
  (33, 'Czech Republic Koruna', 'CZK', '&#75;&#269;', '0', '', '', 0.00, 0, 0),
  (34, 'Cuban Peso', 'CUP', '&#8369;', '0', '', '', 0.00, 0, 0),
  (35, 'Cape Verde Escudo', 'CVE', 'Esc', '0', '', '', 0.00, 0, 0),
  (36, 'Cyprus Pound', 'CYP', '&#163;&#83;', '0', '', '', 0.00, 0, 0),
  (40, 'Danish Krone', 'DKK', '&#107;&#114;', '0', '', '', 0.00, 0, 0),
  (41, 'Dominican Peso', 'DOP', '&#82;&#68;&#36;', '0', '', '', 0.00, 0, 0),
  (42, 'Algerian Dinar', 'DZD', '', '0', '', '', 0.00, 0, 0),
  (43, 'Ecuador Sucre', 'ECS', '', '0', '', '', 0.00, 0, 0),
  (44, 'Egyptian Pound', 'EGP', '&#163;', '0', '', '', 0.00, 0, 0),
  (45, 'Estonian Kroon (EEK)', 'EEK', '', '0', '', '', 0.00, 0, 0),
  (46, 'Ethiopian Birr', 'ETB', '&#66;&#114;', '0', '', '', 0.00, 0, 0),
  (47, 'Euro', 'EUR', '&#8364;', '0', 'Euro', 'Cent', 100.00, 0, 1),
  (49, 'Fiji Dollar', 'FJD', '&#36;', '0', '', '', 0.00, 0, 0),
  (50, 'Falkland Islands Pound', 'FKP', '&#163;', '0', '', '', 0.00, 0, 0),
  (52, 'British Pound', 'GBP', '&#163;', '0', 'Pounds', 'Pence', 99.00, 0, 1),
  (53, 'Ghanaian Cedi', 'GHS', '&#162;', '0', '', '', 0.00, 0, 0),
  (54, 'Gibraltar Pound', 'GIP', '&#163;', '0', '', '', 0.00, 0, 0),
  (55, 'Gambian Dalasi', 'GMD', '&#68;', '0', '', '', 0.00, 0, 0),
  (56, 'Guinea Franc', 'GNF', '&#70;', '0', '', '', 0.00, 0, 0),
  (58, 'Guatemalan Quetzal', 'GTQ', '&#81;', '0', '', '', 0.00, 0, 0),
  (59, 'Guinea-Bissau Peso', 'GWP', '&#70;', '0', '', '', 0.00, 0, 0),
  (60, 'Guyanan Dollar', 'GYD', '&#36;', '0', '', '', 0.00, 0, 0),
  (61, 'Hong Kong Dollar', 'HKD', '&#36;', '0', '', '', 0.00, 0, 0),
  (62, 'Honduran Lempira', 'HNL', '&#76;', '0', '', '', 0.00, 0, 0),
  (63, 'Haitian Gourde', 'HTG', '&#71;', '0', '', '', 0.00, 0, 0),
  (64, 'Hungarian Forint', 'HUF', '&#70;&#116;', '0', '', '', 0.00, 0, 0),
  (65, 'Indonesian Rupiah', 'IDR', '&#82;&#112;', '0', '', '', 0.00, 0, 0),
  (66, 'Irish Punt', 'IEP', '', '0', '', '', 0.00, 0, 0),
  (67, 'Israeli Shekel', 'ILS', '&#8362;', '0', '', '', 0.00, 0, 0),
  (68, 'Indian Rupee', 'INR', '&#8377;', '0', 'RUPEES', 'PAISA', 1.00, 1, 1),
  (69, 'Iraqi Dinar', 'IQD', '', '0', '', '', 0.00, 0, 0),
  (70, 'Iranian Rial', 'IRR', '&#65020;', '0', '', '', 0.00, 0, 0),
  (73, 'Jamaican Dollar', 'JMD', '&#74;&#36;', '0', '', '', 0.00, 0, 0),
  (74, 'Jordanian Dinar', 'JOD', '', '0', '', '', 0.00, 0, 0),
  (75, 'Japanese Yen', 'JPY', '&#165;', '0', '', '', 0.00, 0, 0),
  (76, 'Kenyan Schilling', 'KES', 'Ksh', '0', '', '', 0.00, 0, 0),
  (77, 'Kampuchean (Cambodian) Riel', 'KHR', '', '0', '', '', 0.00, 0, 0),
  (78, 'Comoros Franc', 'KMF', '&#70;', '0', '', '', 0.00, 0, 0),
  (79, 'North Korean Won', 'KPW', '&#8361;', '0', '', '', 0.00, 0, 0),
  (80, 'South Korean Won', 'KRW', '&#8361;', '0', '', '', 0.00, 0, 0),
  (81, 'Kuwaiti Dinar', 'KWD', '', '0', '', '', 0.00, 0, 0),
  (82, 'Cayman Islands Dollar', 'KYD', '&#36;', '0', '', '', 0.00, 0, 0),
  (83, 'Lao Kip', 'LAK', '&#8365;', '0', '', '', 0.00, 0, 0),
  (84, 'Lebanese Pound', 'LBP', '&#163;', '0', '', '', 0.00, 0, 0),
  (85, 'Sri Lanka Rupee', 'LKR', '&#8360;', '0', '', '', 0.00, 0, 0),
  (86, 'Liberian Dollar', 'LRD', '&#36;', '0', '', '', 0.00, 0, 0),
  (87, 'Lesotho Loti', 'LSL', '&#77;', '0', '', '', 0.00, 0, 0),
  (89, 'Libyan Dinar', 'LYD', '', '0', '', '', 0.00, 0, 0),
  (90, 'Moroccan Dirham', 'MAD', '', '0', '', '', 0.00, 0, 0),
  (91, 'Malagasy Franc', 'MGF', 'Ar', '0', '', '', 0.00, 0, 0),
  (92, 'Mongolian Tugrik', 'MNT', '&#8366;', '0', '', '', 0.00, 0, 0),
  (93, 'Macau Pataca', 'MOP', 'MOP$', '0', '', '', 0.00, 0, 0),
  (94, 'Mauritanian Ouguiya', 'MRO', 'UM', '0', '', '', 0.00, 0, 0),
  (95, 'Maltese Lira', 'MTL', 'Lm', '0', '', '', 0.00, 0, 0),
  (96, 'Mauritius Rupee', 'MUR', '&#8360;', '0', '', '', 0.00, 0, 0),
  (97, 'Maldive Rufiyaa', 'MVR', 'Rf.', '0', '', '', 0.00, 0, 0),
  (98, 'Malawi Kwacha', 'MWK', '&#75;', '0', '', '', 0.00, 0, 0),
  (99, 'Mexican Peso', 'MXP', '&#36;', '0', '', '', 0.00, 0, 0),
  (100, 'Malaysian Ringgit', 'MYR', '&#82;&#77;', '0', '', '', 0.00, 0, 0),
  (101, 'Mozambique Metical', 'MZM', 'MT', '0', '', '', 0.00, 0, 0),
  (102, 'Namibian Dollar', 'NAD', '&#36;', '0', '', '', 0.00, 0, 0),
  (103, 'Nigerian Naira', 'NGN', '&#8358;', '0', '', '', 0.00, 0, 0),
  (104, 'Nicaraguan Cordoba', 'NIO', '&#67;&#36;', '0', '', '', 0.00, 0, 0),
  (105, 'Norwegian Kroner', 'NOK', '&#107;&#114;', '0', '', '', 0.00, 0, 0),
  (106, 'Nepalese Rupee', 'NPR', '&#8360;', '0', '', '', 0.00, 0, 0),
  (107, 'New Zealand Dollar', 'NZD', '&#36;', '0', '', '', 0.00, 0, 0),
  (108, 'Omani Rial', 'OMR', '&#65020;', '0', '', '', 0.00, 0, 0),
  (109, 'Panamanian Balboa', 'PAB', '&#66;&#47;&#46;', '0', '', '', 0.00, 0, 0),
  (110, 'Peruvian Nuevo Sol', 'PEN', '&#83;&#47;&#46;', '0', '', '', 0.00, 0, 0),
  (111, 'Papua New Guinea Kina', 'PGK', '&#75;', '0', '', '', 0.00, 0, 0),
  (112, 'Philippine Peso', 'PHP', '&#8369;', '0', '', '', 0.00, 0, 0),
  (113, 'Pakistan Rupee', 'PKR', '&#8360;', '0', '', '', 0.00, 0, 0),
  (114, 'Polish Zloty', 'PLN', '&#122;&#322;', '0', '', '', 0.00, 0, 0),
  (116, 'Paraguay Guarani', 'PYG', '&#71;&#115;', '0', '', '', 0.00, 0, 0),
  (117, 'Qatari Rial', 'QAR', '&#65020;', '0', '', '', 0.00, 0, 0),
  (118, 'Romanian Leu', 'RON', '&#108;&#101;&#105;', '0', '', '', 0.00, 0, 0),
  (119, 'Rwanda Franc', 'RWF', 'FRw', '0', '', '', 0.00, 0, 0),
  (120, 'Saudi Arabian Riyal', 'SAR', '﷼', '0', '', '', 0.00, 0, 0),
  (121, 'Solomon Islands Dollar', 'SBD', 'S', '0', '', '', 0.00, 0, 0),
  (122, 'Seychelles Rupee', 'SCR', '&#8360;', '0', '', '', 0.00, 0, 0),
  (123, 'Sudanese Pound', 'SDP', '&#163;SD', '0', '', '', 0.00, 0, 0),
  (124, 'Swedish Krona', 'SEK', '&#107;&#114;', '0', '', '', 0.00, 0, 0),
  (125, 'Singapore Dollar', 'SGD', '&#36;', '0', '', '', 0.00, 0, 0),
  (126, 'St. Helena Pound', 'SHP', '&#163;', '0', '', '', 0.00, 0, 0),
  (127, 'Sierra Leone Leone', 'SLL', 'Le', '0', '', '', 0.00, 0, 0),
  (128, 'Somali Schilling', 'SOS', '&#83;', '0', '', '', 0.00, 0, 0),
  (129, 'Suriname Guilder', 'SRG', '', '0', '', '', 0.00, 0, 0),
  (130, 'Sao Tome and Principe Dobra', 'STD', 'Db', '0', '', '', 0.00, 0, 0),
  (131, 'Russian Ruble', 'RUB', '&#1088;&#1091;&#1073;', '0', '', '', 0.00, 0, 0),
  (132, 'El Salvador Colon', 'SVC', '&#36;', '0', '', '', 0.00, 0, 0),
  (133, 'Syrian Potmd', 'SYP', '&#163;', '0', '', '', 0.00, 0, 0),
  (134, 'Swaziland Lilangeni', 'SZL', '&#69;', '0', '', '', 0.00, 0, 0),
  (135, 'Thai Baht', 'THB', '&#3647;', '0', '', '', 0.00, 0, 0),
  (136, 'Tunisian Dinar', 'TND', '', '0', '', '', 0.00, 0, 0),
  (137, 'Tongan Paanga', 'TOP', '&#84;&#36;', '0', '', '', 0.00, 0, 0),
  (138, 'East Timor Escudo', 'TPE', '', '0', '', '', 0.00, 0, 0),
  (139, 'Turkish Lira', 'TRY', '&#;', '0', '', '', 0.00, 0, 0),
  (140, 'Trinidad and Tobago Dollar', 'TTD', '&#84;&#84;&#36;', '0', '', '', 0.00, 0, 0),
  (141, 'Taiwan Dollar', 'TWD', '&#78;&#84;&#36;', '0', '', '', 0.00, 0, 0),
  (142, 'Tanzanian Schilling', 'TZS', 'Tsh', '0', '', '', 0.00, 0, 0),
  (143, 'Uganda Shilling', 'UGX', 'USh', '0', '', '', 0.00, 0, 0),
  (144, 'US DOLLAR', 'USD', '&#36;', '0', 'DOLLAR', 'CENTS', 70.00, 0, 1),
  (145, 'Uruguayan Peso', 'UYU', '&#36;&#85;', '0', '', '', 0.00, 0, 0),
  (146, 'Venezualan Bolivar', 'VEF', '&#66;&#115;', '0', '', '', 0.00, 0, 0),
  (147, 'Vietnamese Dong', 'VND', '&#8363;', '0', '', '', 0.00, 0, 0),
  (148, 'Vanuatu Vatu', 'VUV', '&#66;&#115;', '0', '', '', 0.00, 0, 0),
  (149, 'Samoan Tala', 'WST', 'WS$', '0', '', '', 0.00, 0, 0),
  (150, 'CommunautÃƒÂ© FinanciÃƒÂ¨re Africaine BEAC, Francs', 'XAF', '', '0', '', '', 0.00, 0, 0),
  (151, 'Silver, Ounces', 'XAG', '', '0', '', '', 0.00, 0, 0),
  (152, 'Gold, Ounces', 'XAU', '', '0', '', '', 0.00, 0, 0),
  (153, 'East Caribbean Dollar', 'XCD', '&#36;', '0', '', '', 0.00, 0, 0),
  (154, 'International Monetary Fund (IMF) Special Drawing Rights', 'XDR', 'SDR', '0', '', '', 0.00, 0, 0),
  (155, 'CommunautÃƒÂ© FinanciÃƒÂ¨re Africaine BCEAO - Francs', 'XOF', '', '0', '', '', 0.00, 0, 0),
  (156, 'Palladium Ounces', 'XPD', '', '0', '', '', 0.00, 0, 0),
  (157, 'Comptoirs FranÃƒÂ§ais du Pacifique Francs', 'XPF', '', '0', '', '', 0.00, 0, 0),
  (158, 'Platinum, Ounces', 'XPT', '', '0', '', '', 0.00, 0, 0),
  (159, 'Democratic Yemeni Dinar', 'YDD', '', '0', '', '', 0.00, 0, 0),
  (160, 'Yemeni Rial', 'YER', '&#65020;', '0', '', '', 0.00, 0, 0),
  (161, 'New Yugoslavia Dinar', 'YUD', '', '0', '', '', 0.00, 0, 0),
  (162, 'South African Rand', 'ZAR', '&#82;', '0', '', '', 0.00, 0, 0),
  (163, 'Zambian Kwacha', 'ZMK', '&#75;', '0', '', '', 0.00, 0, 0),
  (164, 'Zaire Zaire', 'ZRZ', '', '0', '', '', 0.00, 0, 0),
  (165, 'Zimbabwe Dollar', 'ZWD', '&#90;&#36;', '0', '', '', 0.00, 0, 0),
  (166, 'Slovak Koruna', 'SKK', '&#36;', '0', '', '', 0.00, 0, 0),
  (167, 'ARMENIAN DRAM', 'AMD', '', '0', '', '', 0.00, 0, 0)");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_sales_type1',0,'$date')");
}
//hardi db changes end 15/04/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_sales_wo_status_done'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `finish_status` INT(11) NOT NULL DEFAULT '0'");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_sales_wo_status_done',0,'$date')");
  //common branch update in db log table end
}
//hardi db changes start 18/04/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_financial_year'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_invoicetype`  ADD `financial_year_id` INT NOT NULL");
  $query_invoicetypees = $dbcon->query("UPDATE `tbl_invoicetype` SET `branch_id`='1000', `financial_year_id`= '1'");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_financial_year',0,'$date')");
  //common branch update in db log table end
}
//hardi db changes end 18/04/2022
//hardi db changes start 20/04/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_quot_filter'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `enable_material_center` INT NOT NULL COMMENT '0:no,1:yes' ,  ADD `so_invo_descri_transfer` INT NOT NULL COMMENT '0:no,1:yes' ,  ADD `branch_wise_manage` INT NOT NULL COMMENT '0:no,1:yes'");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `branch_mst`  ADD `isdefault` INT NOT NULL COMMENT '0:no,1:yes'");
  $query_invoicetypees = $dbcon->query("UPDATE `branch_mst` SET `isdefault` = '1' WHERE `branch_id` = '1'");
  $query_invoicetypees = $dbcon->query("UPDATE `tbl_invoicetype` SET `type_id` = '45'  WHERE `invoice_type` LIKE 'PC'");
  $query_invoicetypees = $dbcon->query("UPDATE `tbl_company_configuration` SET `branch_wise_manage` = '1'  WHERE 1");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_quot_filter',0,'$date')");
  //common branch update in db log table end
}
//hardi db changes end 20/04/2022
//hardi db changes start 21/04/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_print_letterpad'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `crm_print_letterhead_per` INT NOT NULL COMMENT '0:no,1:yes' ,  ADD `purchase_print_letterhead_per` INT NOT NULL COMMENT '0-no,1-yes' ,  ADD `finance_print_letterhead_per` INT NOT NULL COMMENT '0:no,1:yes' ,  ADD `sales_print_letterhead_per` INT NOT NULL COMMENT '0:no,1:yes' ,  ADD `production_print_letterhead_per` INT NOT NULL COMMENT '0:no,1:yes'");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_print_letterpad',0,'$date')");
  //common branch update in db log table end
}
//hardi db changes end 21/04/2022


//Sanat db changes end 22/04/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_branch_jobwork_description'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_job_work_trn` ADD `description` TEXT NOT NULL");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_branch_jobwork_description',0,'$date')");
  //common branch update in db log table end
}

//Sanat db changes end 22/04/2022
//hardi db changes start 22/04/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_user_lock'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("UPDATE `tbl_inquiry` SET `closing_date` = `inquiry_date` WHERE 1");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `enable_installation_type` INT NOT NULL COMMENT '0:no,1:yes'");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_user_lock',0,'$date')");
  //common branch update in db log table end

}
//hardi db changes end 22/04/2022



//sanat db changes start 26/04/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='opening_stock_rate_add'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `opening_stock_mst` ADD `base_rate` DOUBLE(10,2) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `opening_stock_mst` ADD `conv_rate` DOUBLE(10,2) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `process_opening_stock_mst` ADD `process_base_rate` DOUBLE(10,2) NOT NULL , ADD `process_conv_rate` DOUBLE(10,2) NOT NULL , ADD `process_stock_base_rate` DOUBLE(10,2) NOT NULL , ADD `process_stock_conv_rate` DOUBLE(10,2) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_process_stock_trn` ADD `process_base_rate` DOUBLE(10,2) NOT NULL , ADD `process_conv_rate` DOUBLE(10,2) NOT NULL , ADD `process_stock_base_rate` DOUBLE(10,2) NOT NULL , ADD `process_stock_conv_rate` DOUBLE(10,2) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_grn_trn` ADD `product_process_rate` DECIMAL(10,4) NOT NULL , ADD `product_process_unit` INT(11) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `product_process_rate` DECIMAL(10,4) NOT NULL , ADD `product_process_unit` INT(11) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('opening_stock_rate_add',0,'$date')");
  //common branch update in db log table end

}
//sanat db changes end 26/04/2022
//maulik db changes start 29/04/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='reverse_indent_change'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `sales_wise_branch_planning` INT NOT NULL COMMENT '0.no 1.yes' AFTER `quotation_rate_fixed`");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_stock_trn` ADD `base_rate` DOUBLE(10,2) NOT NULL AFTER `used_convert_stock`, ADD `conv_rate` DOUBLE(10,2) NOT NULL AFTER `base_rate`");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_returnable_channal_item` CHANGE `item_qty` `item_qty` VARCHAR(100) NULL DEFAULT NULL");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_purchasetrntemp` ADD `approve_indent_id` INT NOT NULL AFTER `pending_approve_status`");

  $query_invoicetypees = $dbcon->query("UPDATE tbl_purchasetrntemp SET tbl_purchasetrntemp.approve_indent_id=(SELECT approve_indent.approve_indent_id FROM approve_indent WHERE approve_indent.rp_id = tbl_purchasetrntemp.po_ref_id and approve_indent.approve_qty=tbl_purchasetrntemp.product_qty)");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `tc_format` INT NOT NULL AFTER `cons_same_as`");
}
/////////////////////////////////////Harshil - 5-4-2022////////////////////////////////////////////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sales_return_branch_id'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_sale_return` ADD `branch_id` INT(11) NOT NULL DEFAULT '0' AFTER `company_id`");
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sales_return_branch_id',0,'$date')");
}
////////////////////////////////////////////////////////////harshil end 5-4-2022/////////////////////////////////////////////////////

//pathik db changes start 9/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='day_book'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_general_book` ADD `module_name` INT NOT NULL AFTER `general_percentage`, ADD `module_id` INT NOT NULL AFTER `module_name`");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_general_book` CHANGE `module_name` `module_name` VARCHAR(110) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('day_book',0,'$date')");
  //common branch update in db log table end
}
//pathik db changes stop 9/05/2022

// Maulik db changes start 13/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='po_approval_godown_stock_transfer'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_purchaseorder_aprv_log` ADD `is_delete` INT NOT NULL COMMENT '0.Active 2.Delete' AFTER `approve_status`");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_purchaseorder_finance_aprv_log` ADD `is_delete` INT NOT NULL COMMENT '0.Active 2.Delete' AFTER `approve_status`");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_stock_transfer_trn` ADD `stock_qty` INT NOT NULL AFTER `godown_id`, ADD `stock_unit` INT NOT NULL AFTER `stock_qty`");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_stock_transfer_trn` (
        `stock_transfer_trn_id` int(11) NOT NULL AUTO_INCREMENT,
        `stock_transfer_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `godown_id` int(11) NOT NULL,
        `stock_qty` int(11) NOT NULL,
        `stock_unit` int(11) NOT NULL,
        `base_qty` varchar(50) NOT NULL,
        `base_unit` int(11) NOT NULL,
        `conv_qty` varchar(50) NOT NULL,
        `conv_unit` int(11) NOT NULL,
        `status` int(11) NOT NULL,
        `grn_status` int(11) NOT NULL,
        `grn_base_qty` varchar(50) NOT NULL,
        `grn_conv_qty` varchar(50) NOT NULL,
        `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        PRIMARY KEY (`stock_transfer_trn_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_stock_transfer` (
        `stock_transfer_id` int(11) NOT NULL AUTO_INCREMENT,
        `stock_transfer_doc_no` varchar(50) NOT NULL,
        `stock_transfer_doc_date` date NOT NULL,
        `from_godown_id` int(11) NOT NULL,
        `to_godown_id` int(11) NOT NULL,
        `from_branch_id` int(11) NOT NULL,
        `to_branch_id` int(11) NOT NULL,
        `status` int(11) NOT NULL,
        `approve_status` int(11) NOT NULL COMMENT '0-pending, 1-approve, 2-reject',
        `grn_status` int(11) NOT NULL COMMENT '0-pending, 1-done ',
        `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `remark` text NOT NULL,
        PRIMARY KEY (`stock_transfer_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_stock_transfer_aprv_log` (
      `stock_aprv_log_id` int(11) NOT NULL AUTO_INCREMENT,
      `stock_transfer_id` int(11) NOT NULL,
      `approve_remark` mediumtext NOT NULL,
      `approve_status` int(11) NOT NULL,
      `stock_aprv_log_status` int(11) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      `branch_id` int(11) NOT NULL,
      PRIMARY KEY (`stock_aprv_log_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `gst_type` INT NOT NULL AFTER `invoicetype_id`");

  $date = date("Y-m-d H:i:s");
  //$query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('po_approval_godown_stock_transfer',0,'$date')");
}
// Maulik db changes end 14/05/2022




//sanat db changes start 13/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_min_max_changes'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_store_order_min_max` (
    `order_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `product_category` int(11) NOT NULL,
    `base_qty` varchar(100) NOT NULL,
    `conv_qty` varchar(100) NOT NULL,
    `base_unit` int(11) NOT NULL,
    `conv_unit` int(11) NOT NULL,
    `base_request_qty` varchar(100) NOT NULL,
    `conv_request_qty` varchar(100) NOT NULL,
    `wo_base_qty` varchar(100) NOT NULL,
    `wo_conv_qty` varchar(100) NOT NULL,
    `status` int(11) NOT NULL,
    `wo_complete_status` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`order_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `store_order_id` INT(11) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `store_order_id` INT(11) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_min_max_changes',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 13/05/2022




//sanat db changes start 02/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_costing_report'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_allocate_process_material` ADD `rate` DOUBLE(10,4) NOT NULL DEFAULT '0' COMMENT 'for one qty' , ADD `total_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' COMMENT 'sum of all qty'");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_allocate_process_material` ADD `conv_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' COMMENT 'for one qty' , ADD `total_conv_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' COMMENT 'sum of all qty'");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `material_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' COMMENT 'total qty rate' , ADD `process_pus_material_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' COMMENT 'total qty rate with matrial rate plus sum'");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_process_reserve_stock` ADD `base_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' COMMENT 'for one qty' , ADD `conv_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' COMMENT 'for one qty' , ADD `total_base_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' COMMENT 'total rate',ADD `total_conv_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' COMMENT 'total rate'");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `material_conv_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' , ADD `process_pus_material_conv_rate` DOUBLE(10,4) NOT NULL DEFAULT '0'");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `total_process_rate` DOUBLE(10,4) NOT NULL DEFAULT '0' , ADD `total_process_conv_rate` DOUBLE(10,4) NOT NULL DEFAULT '0'");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_process_stock_trn` CHANGE `process_base_rate` `process_base_rate` DOUBLE(10,5) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_process_stock_trn` CHANGE `process_conv_rate` `process_conv_rate` DOUBLE(10,5) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_process_stock_trn` CHANGE `process_stock_base_rate` `process_stock_base_rate` DOUBLE(10,5) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_process_stock_trn` CHANGE `process_stock_conv_rate` `process_stock_conv_rate` DOUBLE(10,5) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_allocate_re_process` ADD `product_process_rate` DECIMAL(10,4) NOT NULL , ADD `product_process_unit` INT(11) NOT NULL , ADD `material_rate` DECIMAL(10,4) NOT NULL , ADD `process_pus_material_rate` DECIMAL(10,4) NOT NULL , ADD `material_conv_rate` DECIMAL(10,4) NOT NULL , ADD `process_pus_material_conv_rate` DECIMAL(10,4) NOT NULL , ADD `total_process_rate` DECIMAL(10,4) NOT NULL , ADD `total_process_conv_rate` DECIMAL(10,4) NOT NULL");
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_bom_costing` (
      `bom_costing_id` int(11) NOT NULL AUTO_INCREMENT,
      `costing_no` varchar(100) NOT NULL,
      `costing_date` date NOT NULL,
      `product_id` int(11) NOT NULL,
      `qty` varchar(50) NOT NULL,
      `bom_version_id` int(11) NOT NULL,
      `bom_id` int(11) NOT NULL,
      `purchase_rate` int(11) NOT NULL COMMENT '1 - last purchase rate, 2 average rate, 3 - last po rate, 4- purchase card rate',
      `template_id` int(11) NOT NULL,
      `cdate` date NOT NULL,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      PRIMARY KEY (`bom_costing_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_bom_costing_trn` (
    `bom_costing_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `bom_costing_id` int(11) NOT NULL,
    `sr_no` varchar(100) NOT NULL,
    `product_id` int(11) NOT NULL,
    `base_qty` int(11) NOT NULL,
    `conv_qty` int(11) NOT NULL,
    `base_unit` int(11) NOT NULL,
    `conv_unit` int(11) NOT NULL,
    `base_rate` decimal(10,4) NOT NULL,
    `conv_rate` decimal(10,4) NOT NULL,
    `parent_id` int(11) NOT NULL,
    `cdate` date NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`bom_costing_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_bom_costing_process` (
    `bom_costing_process_id` int(11) NOT NULL AUTO_INCREMENT,
    `bom_costing_trn_id` int(11) NOT NULL,
    `process_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `process_type` int(11) NOT NULL COMMENT '1-inhouse, 2-outside',
    `priority` int(11) NOT NULL,
    `rate` decimal(10,4) NOT NULL,
    `cdate` date NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`bom_costing_process_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("INSERT INTO `tbl_module_type` (`module_type_id`, `module_name`, `status`, `cdate`) VALUES (NULL, 'BOM COSTING', '0', CURRENT_TIMESTAMP)");
  $query_invoicetypees = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'BOM COSTING', '0', '0', '47', '3', 'BOM/COSTING/', '/22-23', '1', '0', '2022-04-01 00:00:00', '1', '2', '1', '1', '', '1')");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_bom_costing_trn` ADD `total_base_rate` DECIMAL(10,4) NOT NULL , ADD `total_conv_rate` DECIMAL(10,4) NOT NULL");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_bom_costing_process` ADD `total_rate` DECIMAL(10,4) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_bom_costing` ADD `status` INT(11) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_bom_costing_process` ADD `status` INT(11) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_bom_costing_trn` ADD `status` INT(11) NOT NULL");
  $query_invoicetypees = $dbcon->query("
  CREATE TABLE IF NOT EXISTS `tbl_bom_costing_extra_rate` (
    `extra_rate_id` int(11) NOT NULL AUTO_INCREMENT,
    `bom_costing_id` int(11) NOT NULL,
    `type_name` varchar(50) NOT NULL,
    `per` varchar(5) NOT NULL DEFAULT '0',
    `amount` varchar(20) NOT NULL DEFAULT '0',
    `type` int(11) NOT NULL COMMENT '0:plus,1:minus',
    `status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`extra_rate_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_workorder_costing_extra_rate` (
    `extra_rate_id` int(11) NOT NULL AUTO_INCREMENT,
    `sp_id` int(11) NOT NULL,
    `type_name` varchar(50) NOT NULL,
    `per` varchar(5) NOT NULL DEFAULT '0',
    `amount` varchar(20) NOT NULL DEFAULT '0',
    `type` int(11) NOT NULL COMMENT '0:plus,1:minus',
    `status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`extra_rate_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_bom_costing` ADD `total_process_rate` DECIMAL(10,4) NOT NULL AFTER `template_id`, ADD `total_raw_material_rate` DECIMAL(10,4) NOT NULL AFTER `total_process_rate`, ADD `total_costing_rate` DECIMAL(10,4) NOT NULL AFTER `total_raw_material_rate`");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `bom_costing_id` INT(11) NOT NULL");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_bom_costing_template` (
    `bom_costing_template_id` int(11) NOT NULL AUTO_INCREMENT,
    `template_name` varchar(210) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`bom_costing_template_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_bom_costing_template_trn` (
    `bom_costing_template_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `bom_costing_template_id` int(11) NOT NULL,
    `type_name` varchar(210) NOT NULL,
    `type` int(11) NOT NULL COMMENT '0:Additive,1:Subtractive',
    `per` double(10,2) NOT NULL,
    `amount` double(10,2) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`bom_costing_template_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_workorder_direct_material_issue` (
    `material_issue_id` int(11) NOT NULL AUTO_INCREMENT,
    `material_issue_no` varchar(100) NOT NULL,
    `material_issue_date` date NOT NULL,
    `workorder_id` int(11) NOT NULL,
    `rp_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `process_id` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `flag` int(11) NOT NULL,
    `allocate_user_id` int(11) NOT NULL,
    `remark` text NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`material_issue_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_workorder_direct_material_issue_trn` (
    `material_issue_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `material_issue_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `base_qty` varchar(50) NOT NULL,
    `conv_qty` varchar(50) NOT NULL,
    `base_unit` int(11) NOT NULL,
    `conv_unit` int(11) NOT NULL,
    `batch_flag` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `flag` int(11) NOT NULL,
    PRIMARY KEY (`material_issue_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_workorder_direct_material_issue_aprv_log` (
    `material_aprv_log_id` int(11) NOT NULL,
    `material_issue_id` int(11) NOT NULL,
    `approve_remark` text NOT NULL,
    `approve_status` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

  //common branch update in db log table start

  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_costing_report',0,'$date')");
  //common branch update in db log table end

}
//sanat db changes end 02/05/2022

//sanat db changes start 16/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_workorder_material_direct_sisuse'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_workorder_direct_material_issue` ADD `branch_id` INT(11) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_workorder_direct_material_issue_trn` ADD `branch_id` INT(11) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_workorder_material_direct_sisuse',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 16/05/2022

//hardi db changes start 19/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dev_assign_user'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_ledger`  ADD `cust_owner` INT NOT NULL  AFTER `territory_id`");
  $query_invoicetypees = $dbcon->query("UPDATE `tbl_ledger` SET `cust_owner`= `user_id` WHERE 1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dev_assign_user',0,'$date')");
  //common branch update in db log table end
}
//hardi db changes stop 19/05/2022
//maulik start user limit wise auto approval

//sanat db changes start 13/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='user_limitwise_approval'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_userwise_approval_setting` (
    `aprv_setting_id` int(11) NOT NULL AUTO_INCREMENT,
    `permission_user_id` int(11) NOT NULL,
    `amount` double(10,2) NOT NULL,
    `auto_approval` int(11) NOT NULL COMMENT '0-no, 1-yes',
    `module_type` int(11) NOT NULL COMMENT '1.quotation 2.sale_order 3.order_acceptance 4.po 5.po finance',
    `user_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`aprv_setting_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_userwise_approval_setting` ADD `status` INT NOT NULL COMMENT '0.active 2.delete' AFTER `module_type`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('user_limitwise_approval',0,'$date')");
  //common branch update in db log table end
}


//sanat db changes start 19/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_batch_wise_stock_allocate_returnable'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_returnable_batch_stock_tmp` (
    `batch_stk_id` int(11) NOT NULL AUTO_INCREMENT,
    `returnable_trn_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `stock_id` int(11) NOT NULL,
    `reserve_id` int(11) NOT NULL,
    `qty` int(11) NOT NULL,
    `unitid` int(11) NOT NULL,
    `status` tinyint(4) NOT NULL,
    `cdate` datetime NOT NULL,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    PRIMARY KEY (`batch_stk_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_batch_wise_stock_allocate_returnable',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 19/05/2022

//hardi db changes start 25/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='hardi_forecast_user'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `forecast_base` INT NOT NULL DEFAULT '1' COMMENT '1:userwise, 2:productwise, 3:product categorywise'");

  $query_invoicetypees = $dbcon->query("INSERT INTO `tbl_module_type` (`module_type_id`, `module_name`, `status`, `cdate`) VALUES ('49', 'FORECAST NO', '0', CURRENT_TIMESTAMP)");

  $query_invoicetypees = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'FORECAST NO', '0', '0', '49', '3', 'FC/', '/22-23', '1', '0', '2022-05-23 12:15:24', '1', '2', '1', '10000', '', '1')");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_forecast_user` (
    `forecast_user_id` int(11) NOT NULL AUTO_INCREMENT,
    `forecast_no` varchar(64) NOT NULL,
    `forecast_date` date NOT NULL,
    `financial_year_id` int(11) NOT NULL,
    `forecast_type` int(11) NOT NULL COMMENT '1: monthly, 2: quaterly, 3:half-yearly, 4:yearly',
    `remark` text NOT NULL,
    `forecast_status` int(11) NOT NULL COMMENT '0: active, 2: deleted, 1:inactive',
    `branch_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `forecast_base` int(11) NOT NULL COMMENT '1: userwise,2: productwise,3: product categorywise',
    `user_id` int(11) NOT NULL,
    `f_user_id` int(11) NOT NULL,
    PRIMARY KEY (`forecast_user_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypees = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_forecast_user_trn` (
    `forecast_user_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `forecast_usertable_id` int(11) NOT NULL,
    `forecast_base` int(11) NOT NULL,
    `forecast_month` int(11) NOT NULL,
    `forecast_start_date` date NOT NULL,
    `forecast_end_date` date NOT NULL,
    `f_user_id` int(11) NOT NULL,
    `target_amount` double(15,2) NOT NULL,
    `target_qty` double(15,2) NOT NULL,
    `f_product` int(11) NOT NULL COMMENT '2:product, 3:product category',
    `cdate` datetime NOT NULL,
    `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `branch_id` int(11) NOT NULL,
    `status` int(11) NOT NULL COMMENT '0:active, 2:deleted',
    `company_id` int(11) NOT NULL,
    `financial_year_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `forecast_type` int(11) NOT NULL COMMENT '1: monthly, 2: quaterly, 3:half-yearly, 4:yearly',
    PRIMARY KEY (`forecast_user_trn_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_forecast_user`  ADD `approve_status` INT NOT NULL COMMENT '0:approved, 1:pending, 2: disapproved'");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_forecast_user_approve_log` (
    `forecast_log_id` int(11) NOT NULL AUTO_INCREMENT,
    `forecast_usertable_id` int(11) NOT NULL,
    `approve_status` int(11) NOT NULL COMMENT '1: approved, 2:disapproved',
    `approve_remark` text NOT NULL,
    `user_id` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`forecast_log_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("INSERT INTO `print_type_mst` (`id`, `print_type_name`, `print_status`, `cdate`, `user_id`, `company_id`, `branch_id`) VALUES ('20', 'Forecast Print', '0', CURRENT_TIMESTAMP, '1', '1', '1')");

  $query_invoicetypes = $dbcon->query("INSERT INTO `print_setup_mst` (`id`, `print_type`, `print_name`, `fa_icon`, `page_path`, `icon_color`, `priority`, `approve_status`, `status`, `cdate`, `user_id`, `company_id`, `branch_id`) VALUES (NULL, '20', 'Forecast Print', 'fa fa-print', 'forecast_user_print', '#17cf2c', '1', '1', '0', '2022-05-26 14:37:54', '1', '1', '1')");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('hardi_forecast_user',0,'$date')");
  //common branch update in db log table end
}
//hardi db changes stop 25/05/2022

//sanat db changes start 24/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_workorder_material_direct_issue'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`) VALUES (NULL, 'WORKORDER MATERIAL ISSUE', '0', '0', '48', '3', 'WO/MAT/ISSUE/', '/22-23', '1', '0', '2021-04-01 05:30:00', '1', '2', '1', '0')");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_workorder_material_direct_issue',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 24/05/2022

//sanat db changes start 24/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_live_wo_report_date_add'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder_req_trn` ADD `cdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_live_wo_report_date_add',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 24/05/2022



//sanat db changes start 26/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_stock_transfer_grn'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn` CHANGE `ref_type` `ref_type` INT(11) NOT NULL COMMENT '1:jobwork , 2 :PO , 3: service, 4. direct workorder, 5. outside so, 6. Returnable Chalan GRN, 7. Stock Transfer'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_trn` CHANGE `ref_type` `ref_type` INT(11) NOT NULL COMMENT '1:jobwork , 2 :PO , 3: service, 4. direct workorder, 5. outside so, 6. Returnable Chalan GRN, 7. Stock Transfer'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_trn` ADD `stock_transfer_id` INT(11) NOT NULL , ADD `stock_transfer_trn_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `stock_transfer_id` INT(11) NOT NULL , ADD `stock_transfer_trn_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `to_godown_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn` ADD `stock_transfer_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("INSERT INTO `print_type_mst` (`id`, `print_type_name`, `print_status`, `cdate`, `user_id`, `company_id`, `branch_id`) VALUES (NULL, 'Godown stock transfer', '0', CURRENT_TIMESTAMP, '1', '1', '1')");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_stock_transfer_grn',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 26/05/2022


//Maulik db changes start 03/06/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='product_to_product_stock_transfer_menu'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("INSERT INTO `menu_master_access` ( `user_id`, `parent_id`, `process_id`, `menu_name`, `menu_path`, `menu_description`, `menu_order`, `menu_fa_icon`, `menu_image_url`, `report_status_flag`, `created_at`, `updated_at`, `status`) VALUES ('1', '26', '0', 'Stock General', 'inventory/stock_general', 'Stock General', '7', 'fa-dot-circle-o', '', 'No', '2021-03-18 20:50:36', '2022-02-14 16:38:59', '0')");

  $last_id = $dbcon->insert_id;

  $query_invoicetype = $dbcon->query("INSERT INTO `menu_master_access_routes` (`user_id`, `access_id`, `access_type`, `slug_name`, `route_path_name`, `created_at`, `updated_at`, `status`) VALUES ('1', '$last_id', 'C', 'inventory-stock-general-add', 'stock_general_add', '2021-03-18 20:50:36', '2022-02-14 16:38:59', '0')");

  $query_invoicetype = $dbcon->query("INSERT INTO `menu_master_access_routes` ( `user_id`, `access_id`, `access_type`, `slug_name`, `route_path_name`, `created_at`, `updated_at`, `status`) VALUES ( '1', '$last_id', 'R', 'inventory-stock-general-list', 'stock_general_list', '2021-03-18 20:50:36', '2022-02-14 16:38:59', '0')");

  $query_invoicetype = $dbcon->query("INSERT INTO `menu_master_access_routes` (`user_id`, `access_id`, `access_type`, `slug_name`, `route_path_name`, `created_at`, `updated_at`, `status`) VALUES ( '1', '$last_id', 'U', 'inventory-stock-general-update', 'stock_general_edit', '2021-03-18 20:50:36', '2022-02-14 16:38:59', '0')");

  $query_invoicetype = $dbcon->query("INSERT INTO `menu_master_access_routes` ( `user_id`, `access_id`, `access_type`, `slug_name`, `route_path_name`, `created_at`, `updated_at`, `status`) VALUES ( '1', '$last_id', 'D', 'inventory-stock-general-delete', '0', '2021-03-18 20:50:36', '2022-02-14 16:38:59', '0')");

  $query_invoicetype = $dbcon->query("INSERT INTO `menu_master_access_routes` ( `user_id`, `access_id`, `access_type`, `slug_name`, `route_path_name`, `created_at`, `updated_at`, `status`) VALUES ( '1', '$last_id', 'A', 'inventory-stock-general-approve', '0', '2021-03-23 22:44:40', '2022-02-14 16:38:59', '0')");

  $query_invoicetype = $dbcon->query("INSERT INTO `menu_master_access_routes` ( `user_id`, `access_id`, `access_type`, `slug_name`, `route_path_name`, `created_at`, `updated_at`, `status`) VALUES ( '1', '$last_id', 'O', 'inventory-stock-general-print', 'stock_general_print', '2021-12-15 17:28:46', '2022-02-14 16:38:59', '0')");


  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('product_to_product_stock_transfer_menu',0,'$date')");
}
//Maulik db changes end

//hardi db changes start 26/05/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='hardi_print_header_content'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}


if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `quotation_header_content` TEXT NOT NULL ,  ADD `so_header_content` TEXT NOT NULL ,  ADD `po_header_content` TEXT NOT NULL ,  ADD `invoice_header_content` TEXT NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('hardi_print_header_content',0,'$date')");
  //common branch update in db log table end
}



$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='hardi_sales_card_changes'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}


if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_salescardtrn` CHANGE `sales_type` `sales_type` INT(11) NOT NULL DEFAULT '0' COMMENT '2.vendor wise 1.normal, 3: group wise'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_product_party_sales` CHANGE `card_type` `card_type` INT(11) NOT NULL COMMENT '2.vendor wise 1.normal, 3: group wise'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('hardi_sales_card_changes',0,'$date')");
  //common branch update in db log table end
}
//hardi db changes stop 26/05/2022

//Maulik Changes field add sales card Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='maulik_changes_sales_card";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}


if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_product_party_sales` ADD `trn_type` INT NOT NULL AFTER `card_type`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_salescardtrn` ADD `trn_type` INT NOT NULL AFTER `sales_type`, ADD `category_id` INT NOT NULL AFTER `trn_type`");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_salescardtrn` SET `trn_type`=1 WHERE `trn_type`=0");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_product_party_sales` ADD `group_id` INT NOT NULL AFTER `party_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_salescardtrn` ADD `group_id` INT NOT NULL AFTER `product_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_general_stock_trn` ADD `rate_unit` INT NOT NULL AFTER `product_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `so_discount_editable` INT NOT NULL COMMENT '0.no 1.yes' AFTER `forecast_base`, ADD `so_calculation_discount_show` INT NOT NULL COMMENT '0.no 1.yes' AFTER `so_discount_editable`, ADD `invoice_discount_editable` INT NOT NULL COMMENT '0.no 1.yes' AFTER `so_calculation_discount_show`, ADD `invoice_calculation_discount_show` INT NOT NULL COMMENT '0.no 1.yes' AFTER `invoice_discount_editable`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('maulik_changes_sales_card',0,'$date')");
  //common branch update in db log table end
}
//Maulik Changes field add sales card End

//sanat db changes start 09/06/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_qc_reject_new_product'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `qc_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `rejection_pro_type` VARCHAR(200) NOT NULL");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_qc_reject_new_product` (
    `qc_rej_id` int(11) NOT NULL AUTO_INCREMENT,
    `qc_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `qty` varchar(100) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `batch_id` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`qc_rej_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_qc_reject_new_product',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 09/06/2022


//Hardi Changes field add sales card Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sales_order_user_selection";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}


if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `crm_sales_order_user_selecation` INT NOT NULL COMMENT '0:no,1:yes'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `crm_sales_order_user_type_selecation` VARCHAR(500) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sales_order_user_selection',0,'$date')");
  // common branch update in db log table end
}

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='forecast_calculation_setting";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}


if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration`  ADD `forecast_calculation` INT NOT NULL DEFAULT '1' COMMENT '1: quotation, 2: so, 3: invoice'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('forecast_calculation_setting',0,'$date')");
  // common branch update in db log table end
}

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='maintenance_module'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}


if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_maintenance` (
    `maintenance_id` int(11) NOT NULL AUTO_INCREMENT,
    `maintenance_no` varchar(64) NOT NULL,
    `maintenance_date` date NOT NULL,
    `cust_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `product_icode` varchar(64) NOT NULL,
    `drawing_no` varchar(64) NOT NULL,
    `product_category` int(11) NOT NULL,
    `ranges` varchar(150) NOT NULL,
    `make` varchar(150) NOT NULL,
    `accuracy` varchar(150) NOT NULL,
    `modal` varchar(150) NOT NULL,
    `use_for` varchar(250) NOT NULL,
    `bill_no` varchar(64) NOT NULL,
    `bill_date` date NOT NULL,
    `price` double(20,2) NOT NULL,
    `calibration_period` varchar(125) NOT NULL,
    `calibration_req` int(11) NOT NULL COMMENT '0:no, 1:yes',
    `remind_before` varchar(125) NOT NULL,
    `remark` text NOT NULL,
    `location` varchar(500) NOT NULL,
    `use_status` int(11) NOT NULL COMMENT '0: no use, 1: in use',
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `maintenance_status` int(11) NOT NULL COMMENT '0: active, 2: deleted',
    `cdate` datetime NOT NULL,
    `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`maintenance_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_calibration` (
    `calibration_id` int(11) NOT NULL AUTO_INCREMENT,
    `calibration_req_no` varchar(64) NOT NULL,
    `calibration_req_date` date NOT NULL,
    `bill_no` varchar(64) NOT NULL,
    `bill_date` date NOT NULL,
    `due_date` date NOT NULL,
    `remind_date` date NOT NULL,
    `amount` double(20,2) NOT NULL,
    `lci_used` varchar(500) NOT NULL,
    `acceptance` varchar(500) NOT NULL,
    `tc_date` date NOT NULL,
    `cust_id` int(11) NOT NULL,
    `cust_address` varchar(250) NOT NULL,
    `calibration_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`calibration_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_calibration_date_trn` (
    `calibration_date_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `maintenance_id` int(11) NOT NULL,
    `calculate_date` date NOT NULL,
    `calibration_date` date NOT NULL,
    `calibration_id` int(11) NOT NULL,
    `calibration_date_trn_status` int(11) NOT NULL COMMENT '0: pending, 1: done, 2: deleted',
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`calibration_date_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'MAINTENANCE', '0', '0', '52', '3', 'MAIN/', '/22-23', '1', '0', '2021-04-01 05:30:00', '1', '2', '1', '1', '', '1')");

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'CALIBRATION', '0', '0', '53', '3', 'CALI/', '/22-23', '1', '0', '2021-04-01 05:30:00', '1', '2', '1', '1', '', '1')");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('maintenance_module',0,'$date')");
  // common branch update in db log table end
}
//Hardi Changes field add sales card End


//sanat db changes start 09/06/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_jobcard_serias_add'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_module_type` (`module_type_id`, `module_name`, `status`, `cdate`) VALUES (NULL, 'JOB CARD', '0', CURRENT_TIMESTAMP)");
  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'JOBCARD NO', '0', '0', '54', '3', 'JOBCARD/', '/22-23', '1', '0', '2021-04-01 05:30:00', '1', '2', '1', '1', '', '1')");



  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_jobcard_serias_add',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 09/06/2022



//maulik changes field add production bypass
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='production_bypass_stock_general";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_general_batch_stock_tmp` (
      `batch_stk_id` int(11) NOT NULL AUTO_INCREMENT,
      `general_stock_trn_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      `stock_id` int(11) NOT NULL,
      `reserve_id` int(11) NOT NULL,
      `qty` double NOT NULL,
      `unitid` int(11) NOT NULL,
      `status` int(11) NOT NULL,
      `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `company_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      PRIMARY KEY (`batch_stk_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_general_stock` (
      `general_stock_id` int(11) NOT NULL AUTO_INCREMENT,
      `general_stock_no` varchar(100) NOT NULL,
      `general_stock_date` date NOT NULL,
      `remark` text NOT NULL,
      `status` int(11) NOT NULL COMMENT '0.active 2.delete',
      `stock_approval` int(11) NOT NULL COMMENT '0.pending 1.approval',
      `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      `branch_id` int(11) NOT NULL,
      PRIMARY KEY (`general_stock_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_general_stock_trn` (
      `general_stock_trn_id` int(11) NOT NULL AUTO_INCREMENT,
      `general_stock_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      `rate_unit` int(11) NOT NULL,
      `unitid` int(11) NOT NULL,
      `conv_unitid` int(11) NOT NULL,
      `product_qty` varchar(100) NOT NULL,
      `product_conv_qty` varchar(100) NOT NULL,
      `product_rate` double(10,2) NOT NULL,
      `product_conv_rate` double(10,2) NOT NULL,
      `status` int(11) NOT NULL COMMENT '0.active 2.delete',
      `stock_type` int(11) NOT NULL COMMENT '1.in 2.deduct',
      `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      `branch_id` int(11) NOT NULL,
      PRIMARY KEY (`general_stock_trn_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_batch_stock_trn_in` (
      `batch_stock_id` int(11) NOT NULL AUTO_INCREMENT,
      `batch_stock_no` varchar(200) NOT NULL,
      `godown_id` int(11) NOT NULL,
      `qty` double NOT NULL,
      `unitid` int(11) NOT NULL,
      `general_stock_trn_id` int(11) NOT NULL,
      `status` int(11) NOT NULL,
      `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      `branch_id` int(11) NOT NULL,
      PRIMARY KEY (`batch_stock_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_stock_general_aprv_log` (
    `stock_general_aprv_id` int(11) NOT NULL AUTO_INCREMENT,
    `general_stock_id` int(11) NOT NULL,
    `approve_remark` text NOT NULL,
    `approve_status` int(11) NOT NULL,
    `is_delete` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`stock_general_aprv_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_general_batch_stock_tmp` ADD `godown_id` INT NOT NULL AFTER `product_id`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('production_bypass_stock_general',0,'$date')");
}
//end maulik production bypass


//sanat db changes start 21/06/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_qstore_accept_stock'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_store_accept` ADD `reprocess_qc` INT(11) NOT NULL DEFAULT '0'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_qstore_accept_stock',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 09/06/2022


//pathik db changes start 29/06/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='pathik_po_kindatt'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `kind_attn` INT NOT NULL AFTER `tax_type`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('pathik_po_kindatt',0,'$date')");
  //common branch update in db log table end
}
//pathik db changes stop 29/06/2022

//pathik db changes start 30/06/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='pathik_so_branchall'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `sales_wise_branch_planning_before_bom` INT NOT NULL COMMENT '0: after bom, 1: before bom' AFTER `forecast_calculation`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('pathik_so_branchall',0,'$date')");
  //common branch update in db log table end
}
//pathik db changes stop 30/06/2022

//bug fix in po 

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='bug_fix_kind_attn'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `kind_attn` INT NOT NULL");



  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('bug_fix_kind_attn',0,'$date')");
  //common branch update in db log table end
}

//bug fix in po -- "Maulik - Kapatel"

//series type Start -- "Maulik - Kapatel"

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='seriestype_in_purchase'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `invoicetype_id` INT NOT NULL AFTER `purchase_ledger_id`");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_purchaseorder` SET `invoicetype_id`=12 WHERE `invoicetype_id`=0");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pono` ADD `invoicetype_id` INT NOT NULL AFTER `purchase_bill_type`");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_pono` SET `invoicetype_id`=18 WHERE `invoicetype_id`=0");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `order_user_id` INT NOT NULL AFTER `user_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `sales_type` INT NOT NULL");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_update_user_log` (
    `user_log_id` int(11) NOT NULL AUTO_INCREMENT,
    `updated_user_id` int(11) NOT NULL,
    `previous_user_id` int(11) NOT NULL,
    `remark` text NOT NULL,
    `ref_name` varchar(150) NOT NULL,
    `ref_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`user_log_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `quot_revise_time_rate_with_discount` INT NOT NULL COMMENT '0.No 1.Yes' AFTER `sales_wise_branch_planning_before_bom`");
  //common branch update in db log table start

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` ADD `product_discount_conv` DOUBLE(15,2) NOT NULL AFTER `used_grn_conv_qty`, ADD `sgst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `product_discount_conv`, ADD `cgst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `sgst_tax_rate_conv`, ADD `igst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `cgst_tax_rate_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_tax_trn` ADD `tx_taxable_value_conv` DOUBLE(15,2) NOT NULL AFTER `currency_rate`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `g_total_conv` DOUBLE(15,2) NOT NULL AFTER `kind_attn`, ADD `round_of_conv` DOUBLE(15,2) NOT NULL AFTER `g_total_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_bill_sundry_transaction` ADD `sundry_amount_conv` DOUBLE(15,2) NOT NULL AFTER `tds_per`, ADD `sundry_gst_amount_conv` DOUBLE(15,2) NOT NULL AFTER `sundry_amount_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `product_rate_dollar` VARCHAR(10) NOT NULL AFTER `branch_id`, ADD `product_amount_dollar` VARCHAR(10) NOT NULL AFTER `product_rate_dollar`, ADD `product_total_dollar` VARCHAR(10) NOT NULL AFTER `product_amount_dollar`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_potrancation` ADD `product_rate_conv` DOUBLE(15,2) NOT NULL AFTER `purchasecardtrn_id`, ADD `product_amount_conv` DOUBLE(15,2) NOT NULL AFTER `product_rate_conv`, ADD `product_discount_conv` DOUBLE(15,2) NOT NULL AFTER `product_amount_conv`, ADD `total_conv` DOUBLE(15,2) NOT NULL AFTER `product_discount_conv`, ADD `taxable_value_conv` DOUBLE(15,2) NOT NULL AFTER `total_conv`, ADD `cgst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `taxable_value_conv`, ADD `sgst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `cgst_tax_rate_conv`, ADD `igst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `sgst_tax_rate_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pono`  ADD `g_total_conv` DOUBLE(15,2) NOT NULL  AFTER `sales_type`,  ADD `total_conv` DOUBLE(15,2) NOT NULL  AFTER `g_total_conv`,  ADD `tds_amount_conv` DOUBLE(15,2) NOT NULL  AFTER `total_conv`,  ADD `cgst_conv` DOUBLE(15,2) NOT NULL  AFTER `tds_amount_conv`,  ADD `sgst_conv` DOUBLE(15,2) NOT NULL  AFTER `cgst_conv`,  ADD `igst_conv` DOUBLE(15,2) NOT NULL  AFTER `sgst_conv`,  ADD `round_of_conv` DOUBLE(15,2) NOT NULL  AFTER `igst_conv`");

  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('seriestype_in_purchase',0,'$date')");
  //common branch update in db log table end
}

//series type end -- "Maulik - Kapatel"


//Import Quatation,Sales Order,Invoice Start -- "Maulik - Kapatel"
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='quot_so_invoice_po_pur_import'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("");

  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `currency_enable` INT NOT NULL COMMENT '0-no,1-yes' AFTER `paid_amount`, ADD `currency_rate` DOUBLE NOT NULL AFTER `currency_enable`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `g_total_conv` DOUBLE(15,2) NOT NULL AFTER `project_name`, ADD `basic_total_conv` DOUBLE(15,2) NOT NULL AFTER `g_total_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `currency_enable` INT NOT NULL AFTER `currency_rate`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `gst_type` INT NOT NULL AFTER `basic_total_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation_trn`  ADD `product_rate_conv` DOUBLE(15,2) NOT NULL  AFTER `product_tax_cat`,  ADD `product_amount_conv` DOUBLE(15,2) NOT NULL  AFTER `product_rate_conv`,  ADD `product_discount_conv` DOUBLE(15,2) NOT NULL  AFTER `product_amount_conv`,  ADD `cgst_tax_rate_conv` DOUBLE(15,2) NOT NULL  AFTER `product_discount_conv`,  ADD `sgst_tax_rate_conv` DOUBLE(15,2) NOT NULL  AFTER `cgst_tax_rate_conv`,  ADD `igst_tax_rate_conv` DOUBLE(15,2) NOT NULL  AFTER `sgst_tax_rate_conv`,  ADD `product_total_conv` DOUBLE(15,2) NOT NULL  AFTER `igst_tax_rate_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `currency_id` INT NOT NULL AFTER `product_total_conv`, ADD `currency_rate` DOUBLE(15,2) NOT NULL AFTER `currency_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_inquiry` ADD `currency_rate` DOUBLE(15,2) NOT NULL AFTER `currency_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_inquiry_trn` ADD `currency_id` INT NOT NULL AFTER `product_hp`, ADD `currency_rate` DOUBLE(15,2) NOT NULL AFTER `currency_id`, ADD `product_rate_conv` DOUBLE(15,2) NOT NULL AFTER `currency_rate`, ADD `product_amount_conv` DOUBLE(15,2) NOT NULL AFTER `product_rate_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_inquiry` ADD `g_total_conv` DOUBLE(15,2) NOT NULL AFTER `g_total`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `g_total_conv` DOUBLE(15,2) NOT NULL AFTER `gst_type`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `product_rate_conv` DOUBLE(15,2) NOT NULL AFTER `short_close_conv_unit_id`, ADD `product_discount_conv` DOUBLE(15,2) NOT NULL AFTER `product_rate_conv`, ADD `product_amount_conv` DOUBLE(15,2) NOT NULL AFTER `product_discount_conv`, ADD `cgst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `product_amount_conv`, ADD `sgst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `cgst_tax_rate_conv`, ADD `igst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `sgst_tax_rate_conv`, ADD `total_conv` DOUBLE(15,2) NOT NULL AFTER `igst_tax_rate_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `round_off_conv` DOUBLE(15,2) NOT NULL AFTER `einv_Remarks`, ADD `basic_total_conv` DOUBLE(15,2) NOT NULL AFTER `round_off_conv`, ADD `g_total_conv` DOUBLE(15,2) NOT NULL AFTER `basic_total_conv`, ADD `cgst_conv` DOUBLE(15,2) NOT NULL AFTER `g_total_conv`, ADD `sgst_conv` DOUBLE(15,2) NOT NULL AFTER `cgst_conv`, ADD `igst_conv` DOUBLE(15,2) NOT NULL AFTER `sgst_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `tcs_amount_conv` DOUBLE(15,2) NOT NULL AFTER `igst_conv`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `tcs_conv` DOUBLE(15,2) NOT NULL AFTER `tcs_amount_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoicetrn` ADD `product_rate_conv` DOUBLE(15,2) NOT NULL AFTER `product_tax_cat`, ADD `product_amount_conv` DOUBLE(15,2) NOT NULL AFTER `product_rate_conv`, ADD `product_discount_conv` DOUBLE(15,2) NOT NULL AFTER `product_amount_conv`, ADD `total_conv` DOUBLE(15,2) NOT NULL AFTER `product_discount_conv`, ADD `taxable_value_conv` DOUBLE(15,2) NOT NULL AFTER `total_conv`, ADD `cgst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `taxable_value_conv`, ADD `sgst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `cgst_tax_rate_conv`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_invoicetrn` ADD `igst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `sgst_tax_rate_conv`");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_inquiry` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_inquiry` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_inquiry_trn` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_inquiry_trn` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_quotation` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_quotation` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_quotation_trn` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_quotation_trn` SET `currency_rate`=1  WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_sales_order` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_sales_order` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_sales_ordertrn` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_sales_ordertrn` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_purchaseorder` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_purchaseorder` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_purchaseordertrn` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_purchaseordertrn` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_pono` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_pono` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_potrancation` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_potrancation` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_invoice` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_invoice` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_invoicetrn` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_invoicetrn` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_bill_sundry_transaction` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_bill_sundry_transaction` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_tax_trn` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_tax_trn` SET `currency_rate`=1 WHERE `currency_id`=68");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_general_book` SET `currency_id`=68 WHERE `currency_id`=0");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_general_book` SET `currency_rate`=1 WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `advance_payment` DOUBLE(10,2) NOT NULL AFTER `g_total`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `payable_amt` DOUBLE(10,2) NOT NULL AFTER `advance_payment`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `payable_per` DOUBLE NOT NULL AFTER `payable_amt`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `pending_amt` DOUBLE(10,2) NOT NULL AFTER `payable_per`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` CHANGE `payment_terms` `payment_terms` VARCHAR(400) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");


  $query_invoicetype = $dbcon->query("UPDATE `tbl_inquiry` SET `g_total_conv`=`g_total` WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_inquiry_trn` SET `product_rate_conv`=`product_rate`,`product_amount_conv`=`product_amount`  WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_quotation` SET `g_total_conv`=`g_total`,`basic_total_conv`=`basic_total`  WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_quotation_trn` SET `product_rate_conv`=`product_rate`, `product_amount_conv`=`product_amount`, `product_discount_conv`=`product_discount` ,`cgst_tax_rate_conv`=`cgst_tax_rate` ,`sgst_tax_rate_conv`=`sgst_tax_rate` ,`igst_tax_rate_conv`=`igst_tax_rate` ,`product_total_conv`=`product_total`  WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_sales_order` SET `g_total_conv`=`g_total`  WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_sales_ordertrn` SET `product_rate_conv`=`product_rate`, `product_amount_conv`=`product_amount`, `product_discount_conv`=`product_discount` ,`cgst_tax_rate_conv`=`cgst_tax_rate` ,`sgst_tax_rate_conv`=`sgst_tax_rate` ,`igst_tax_rate_conv`=`igst_tax_rate` ,`total_conv`=`total`  WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_purchaseorder` SET `round_of_conv`=`round_of` , `g_total_conv`=`g_total` WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_purchaseordertrn` SET `product_currency_rate`=`product_rate`, `product_currency_amount`=`product_amount`, `product_currency_amount_tax`=`product_amount_tax` ,`product_discount_conv`=`product_discount` ,`cgst_tax_rate_conv`=`cgst_tax_rate` , `sgst_tax_rate_conv`=`sgst_tax_rate` ,`igst_tax_rate_conv`=`igst_tax_rate` ,`currency_total`=`total`  WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_pono` SET `g_total_conv`=`round_of` , `total_conv`=`g_total`, `tds_amount_conv`=`tds_amount`, `cgst_conv`=`cgst`, `sgst_conv`=`sgst`, `igst_conv`=`igst`, `round_of_conv`=`round_of`  WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_potrancation` SET `product_rate_conv`=`product_rate`, `product_amount_conv`=`product_amount`, `product_discount_conv`=`product_discount` ,`total_conv`=`total` , `taxable_value_conv`=`taxable_value` ,`cgst_tax_rate_conv`=`cgst_tax_rate` , `sgst_tax_rate_conv`=`sgst_tax_rate` ,`igst_tax_rate_conv`=`igst_tax_rate`  WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_invoice` SET `round_off_conv`=`round_off` , `basic_total_conv`=`basic_total`, `g_total_conv`=`g_total`, `cgst_conv`=`cgst`, `sgst_conv`=`sgst`, `igst_conv`=`igst`, `tcs_amount_conv`=`tcs_amount`, `tcs_conv`=`tcs`  WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("UPDATE `tbl_invoicetrn` SET `product_rate_conv`=`product_rate`, `product_amount_conv`=`product_amount`, `product_discount_conv`=`product_discount` ,`total_conv`=`total` , `taxable_value_conv`=`taxable_value` ,`cgst_tax_rate_conv`=`cgst_tax_rate` , `sgst_tax_rate_conv`=`sgst_tax_rate` ,`igst_tax_rate_conv`=`igst_tax_rate`  WHERE `currency_id`=68");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_trn` ADD `product_hsn_code` VARCHAR(200) NOT NULL AFTER `stock_transfer_trn_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pre` ADD `remark` TEXT NOT NULL AFTER `pre_status`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` ADD `required_date` DATE NOT NULL AFTER `igst_tax_rate`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('quot_so_invoice_po_pur_import',0,'$date')");
  //common branch update in db log table end
}
//Import Quatation,Sales Order,Invoice End -- "Maulik - Kapatel"
//Sanat db changies  date 2-3-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='bom_casting_upd_qry'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_bom_costing_trn` CHANGE `base_qty` `base_qty` VARCHAR(100) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_bom_costing_trn` CHANGE `conv_qty` `conv_qty` VARCHAR(100) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('bom_casting_upd_qry',0,'$date')");
  //common branch update in db log table end

}
//Sanat end

//harshil db changies  date 20-7-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='project_wise_inq'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_inquiry_project_trn` CHANGE `tax_name1` `cgst_tax_per` DOUBLE NOT NULL, CHANGE `tax_amount1` `cgst_tax_rate` DOUBLE NOT NULL, CHANGE `tax_name2` `sgst_tax_per` DOUBLE NOT NULL, CHANGE `tax_amount2` `sgst_tax_rate` DOUBLE NOT NULL, CHANGE `tax_name3` `igst_tax_per` DOUBLE NOT NULL, CHANGE `tax_amount3` `igst_tax_rate` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation_project_trn` CHANGE `tax_name1` `cgst_tax_per` DOUBLE NOT NULL, CHANGE `tax_amount1` `cgst_tax_rate` DOUBLE NOT NULL, CHANGE `tax_name2` `sgst_tax_per` DOUBLE NOT NULL, CHANGE `tax_amount2` `sgst_tax_rate` DOUBLE NOT NULL, CHANGE `tax_name3` `igst_tax_per` DOUBLE NOT NULL, CHANGE `tax_amount3` `igst_tax_rate` DOUBLE NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_salesorder_project_trn` CHANGE `tax_name1` `cgst_tax_per` DOUBLE NOT NULL, CHANGE `tax_amount1` `cgst_tax_rate` DOUBLE NOT NULL, CHANGE `tax_name2` `sgst_tax_per` DOUBLE NOT NULL, CHANGE `tax_amount2` `sgst_tax_rate` DOUBLE NOT NULL, CHANGE `tax_name3` `igst_tax_per` DOUBLE NOT NULL, CHANGE `tax_amount3` `igst_tax_rate` DOUBLE NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('project_wise_inq',0,'$date')");
  //common branch update in db log table end	

}
//Harshil end

//harshil db changies  date 25-7-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='post_crm_yes_no_field'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_customer` ADD `post_crm_yes_no` INT NOT NULL AFTER `state_id`");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('post_crm_yes_no_field',0,'$date')");
  //common branch update in db log table end	

}
//Harshil end

//harshil db changies -SMPL  date 1-8-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_Specialchanges'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `smpl_permission` INT NOT NULL AFTER `atlas_permission`");
  ;

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_Specialchanges',0,'$date')");
  //common branch update in db log table end	

}
//Harshil end


//Sanat db changies  date 2-3-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='document_series_genrat'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_store_order_min_max` ADD `doc_no` VARCHAR(100) NOT NULL AFTER `order_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_store_order_min_max` CHANGE `base_qty` `base_qty` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_store_order_min_max` CHANGE `conv_qty` `conv_qty` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_module_type` (`module_type_id`, `module_name`, `status`, `cdate`) VALUES (NULL, 'DOCUMENT NO', '0', CURRENT_TIMESTAMP)");
  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'DOCUMENT NO', '0', '0', '55', '3', 'DOCUMENT/', '/22-23', '1', '0', '2021-04-01 05:30:00', '1', '2', '1', '1', '', '1')");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('document_series_genrat',0,'$date')");
  //common branch update in db log table end

}

//pathik db changies -SMPL  date 1-8-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_Specialchanges_pathik_qr'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE tbl_company_configuration ADD store_relese_first_process INT NOT NULL AFTER quot_revise_time_rate_with_discount");
  $query_invoicetype = $dbcon->query("ALTER TABLE tbl_batch_data ADD auto_store_relese INT NOT NULL COMMENT '1: yes,0:no' AFTER qc_id");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_Specialchanges_pathik_qr',0,'$date')");
  //common branch update in db log table end	

}
//pathik end



//Sanat db changies -  date 3-8-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='return_non_returnable_batch_stock'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_batch_stock_tmp` ADD `batch_no` VARCHAR(100) NOT NULL AFTER `product_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_batch_stock_tmp` CHANGE `stock_id` `stock_id` VARCHAR(111) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('return_non_returnable_batch_stock',0,'$date')");
  //common branch update in db log table end	
}
//sanat end




//harshil db changies -SMPL  date 8-8-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_compny_Setting'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {




  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `smpl_batch_prefix` VARCHAR(100) NOT NULL AFTER `store_relese_first_process`");
  ;

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_compny_Setting',0,'$date')");
  //common branch update in db log table end	

}
//Harshil end

//Sanat db changies -  date 3-8-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='store_order_wise_bom_design_dept'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_store_order_min_max` ADD `bom_id` INT(11) NOT NULL DEFAULT '0' , ADD `bom_status` INT(11) NOT NULL DEFAULT '0' , ADD `bom_version_id` INT(11) NOT NULL DEFAULT '0'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('store_order_wise_bom_design_dept',0,'$date')");
  //common branch update in db log table end	
}
//sanat end


//Sanat db changies -  date 3-8-2022 Start
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='auto_store_approveal_smpl'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `grn_godown` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `resource_wise_production` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - no , 1 - yes'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetype = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('auto_store_approveal_smpl',0,'$date')");
  //common branch update in db log table end	
}
//sanat end


//sanat db changes start 09/06/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_jobcard_serias_add_update'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_module_type` (`module_type_id`, `module_name`, `status`, `cdate`) VALUES (NULL, 'JOB CARD', '0', CURRENT_TIMESTAMP)");
  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'JOBCARD NO', '0', '0', '54', '3', 'JOBCARD/', '/22-23', '1', '0', '2021-04-01 05:30:00', '1', '2', '1', '1', '', '1')");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_jobcard_serias_add_update',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 09/06/2022


//sanat db changes start 17/08/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_unrequest_process_stock_clear'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {


  $query_invoicetype = $dbcon->query("ALTER TABLE `mrp_process_reserve_temp` ADD `status` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `quot_type` INT NOT NULL COMMENT '0.Domestic 1.Export' AFTER `g_total_conv`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_unrequest_process_stock_clear',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 09/06/2022


//pathik db changes start 17/08/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='process_dashbord_priority'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `process_mst` ADD `dashbord_priority` INT NOT NULL AFTER `branch_id`;");



  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('process_dashbord_priority',0,'$date')");
  //common branch update in db log table end
}
//pathik db changes stop 17/08/2022

//Maulik Start 17/08/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='quotation_common_filter'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `kind_attn` INT NOT NULL AFTER `g_total_conv`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `quot_type` INT NOT NULL AFTER `kind_attn`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `quot_type` INT NOT NULL COMMENT '0.Domestic 1.Export' AFTER `sales_type`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `kind_attn` INT NOT NULL AFTER `tcs_conv`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `quot_type` INT NOT NULL COMMENT '0.Domestic 1.Export' AFTER `kind_attn`");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_invoice_terms_trn` (
    `invoice_terms_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `tc_id` int(11) NOT NULL,
    `tc_priority` int(11) NOT NULL,
    `tc_details` longtext NOT NULL,
    `invoice_terms_trn_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `invoice_id` int(11) NOT NULL,
    PRIMARY KEY (`invoice_terms_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `tc_format` INT NOT NULL AFTER `quot_type`, ADD `invoice_condition` TEXT NOT NULL AFTER `tc_format`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_trn` ADD `currency_id` INT NOT NULL AFTER `igst_tax_rate`, ADD `currency_rate` DOUBLE(15,2) NOT NULL AFTER `currency_id`, ADD `cgst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `currency_rate`, ADD `sgst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `cgst_tax_rate_conv`, ADD `igst_tax_rate_conv` DOUBLE(15,2) NOT NULL AFTER `sgst_tax_rate_conv`, ADD `product_rate_conv` DOUBLE(15,2) NOT NULL AFTER `igst_tax_rate_conv`, ADD `product_discount_conv` DOUBLE(15,2) NOT NULL AFTER `product_rate_conv`, ADD `product_amount_conv` DOUBLE(15,2) NOT NULL AFTER `product_discount_conv`, ADD `total_conv` DOUBLE(15,2) NOT NULL AFTER `product_amount_conv`");



  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_proforma_terms_trn` (
    `proforma_terms_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `proforma_id` int(11) NOT NULL,
    `tc_id` int(11) NOT NULL,
    `tc_priority` int(11) NOT NULL,
    `tc_details` longtext NOT NULL,
    `proforma_terms_trn_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`proforma_terms_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `quot_type` INT NOT NULL COMMENT '0.Domestic 1.Export' , ADD `tc_format` INT NOT NULL COMMENT '1.format-1 2.format-2'");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `currency_rate` DOUBLE NOT NULL AFTER `currency_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `g_total_conv` DOUBLE(10,2) NOT NULL ");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `advance_payment_conv` DOUBLE(10,2) NOT NULL ");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `payable_amt_conv` DOUBLE(10,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `pending_amt_conv` DOUBLE(10,2) NOT NULL");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `production_up_to` VARCHAR(500) NOT NULL AFTER `quot_subject`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `cat_id` INT NOT NULL AFTER `product_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `quot_revise_type` INT NOT NULL AFTER `quotation_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_general_book` ADD `ledger_id_ref` INT NOT NULL AFTER `table_id`");

  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('quotation_common_filter',0,'$date')");
}

//Maulik End 

//sanat db changes start 18/08/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_wip_stock_allocate_changes'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `wip_stock_allocate` ADD `finish_qty` VARCHAR(100) NOT NULL , ADD `finish_conv_qty` VARCHAR(100) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_wip_stock_allocate_changes',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 18/08/2022

//sanat db changes start 31/08/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_kesar_jobowrk_changes'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn` ADD `temp_delete` INT(11) NOT NULL COMMENT '0 - no , 1 - yes'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_kesar_jobowrk_changes',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 31/08/2022


//harshil db changes start 24/08/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='durva_special_field'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `durva_permission` INT NOT NULL AFTER `smpl_permission`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `aeon_permission` INT NOT NULL AFTER `durva_permission`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('durva_special_field',0,'$date')");
  //common branch update in db log table end
}
//harshil db changes stop 24/08/2022

//sanat db changes start 02/09/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_smpl_round_up_qty'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `round_up_qty` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - no , 1 - yes'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_smpl_round_up_qty',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 02/09/2022

//sanat db changes start 05/09/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_smpl_workorder_wise_production_merge'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `workorder_wise_production_merge` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - no , 1 - yes'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_smpl_workorder_wise_production_merge',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 05/09/2022


//sanat db changes start 05/09/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_qc_changes_endtime'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `process_end_time_qc` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - no , 1 - yes'");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_temp_qc` (
    `temp_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `process_id` int(11) NOT NULL,
    `p_id` int(11) NOT NULL,
    `rp_id` int(11) NOT NULL,
    `qty` varchar(50) NOT NULL DEFAULT 0,
    `unit_id` int(11) NOT NULL,
    `type` int(11) NOT NULL COMMENT '1-accept, 2-reject, 3-reprocess',
    `new_product_id` int(11) NOT NULL,
    `new_unit_id` int(11) NOT NULL,
    `new_process_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `remark` text NOT NULL,
    `status` int(11) NOT NULL,
    `grn_trn_id` int(11) NOT NULL,
    `grn_sub_trn_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`temp_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_qc` ADD `qc_type` INT(11) NOT NULL  DEFAULT '1' COMMENT '1 - qc, 2 - qc temp process end time' AFTER `qc_date`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_temp_qc` ADD `batch_id` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_temp_qc` ADD `new_godown_id` INT(11) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_qc_changes_endtime',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 05/09/2022


//harshil db changes start 12/9/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='specificationl_field_add'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `product_mst` ADD `product_spec_id` TEXT NOT NULL AFTER `product_alias_name`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `product_spec_id` TEXT NOT NULL AFTER `cgst_tax_rate_conv`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_inquiry_trn` ADD `product_spec_id` TEXT NOT NULL AFTER `currency_rate`");
  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_specification` (
    `specification_id` int(11) NOT NULL AUTO_INCREMENT,
    `specification_name` varchar(500) NOT NULL,
    `specification_priority` int(11) NOT NULL,
    `specification_detail` longtext NOT NULL,  
    `specification_status` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL, 
    PRIMARY KEY (`specification_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('specificationl_field_add',0,'$date')");
  //common branch update in db log table end
}
//harshil db changes stop 12/09/2022


//sanat db changes start 05/09/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_non_return_without_stock'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_returnable_channal` CHANGE `returnable_type` `returnable_type` ENUM('returnable','non-returnable','without_stock') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'returnable'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_non_return_without_stock',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 05/09/2022


//Maulik db changes start 16/09/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='aeone_quot_cutome_change'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_page_permission` ADD `crm_partymst_cust_iec` INT NOT NULL AFTER `crm_partymst_cust_gst`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_customer` ADD `cust_iec` VARCHAR(255) NOT NULL AFTER `cust_gst`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_page_permission` ADD `crm_partymst_cust_pan` INT NOT NULL AFTER `crm_partymst_cust_iec`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_customer` ADD `cust_pan` VARCHAR(255) NOT NULL AFTER `cust_iec`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger` ADD `iec_no` VARCHAR(255) NOT NULL AFTER `gst_no`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `pid` INT NOT NULL AFTER `cat_id`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_inquiry_trn` ADD `pid` INT NOT NULL AFTER `cat_id`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `terms_delivery` TEXT NOT NULL AFTER `pending_amt_conv`, ADD `lr_rr_no` VARCHAR(200) NOT NULL AFTER `terms_delivery`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `chemitek_permission` INT NOT NULL AFTER `aeon_permission`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `product_mst` ADD `rack_no` VARCHAR(200) NOT NULL AFTER `product_spec_id`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('aeone_quot_cutome_change',0,'$date')");
  //common branch update in db log table end
}
//Maulik db changes stop 16/09/2022



//sanat db changes start 05/09/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_extra_stock_module'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `extra_stock` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `extra_stock` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `extra_stock` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process` ADD `extra_stock` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process` ADD `extra_stock_material_reserve` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `extra_stock` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `extra_stock` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `ext_stock_vendor_id` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `ext_stock_vendor_id` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `smpl_extra_stock` (
    `extra_stock_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `stock_id` varchar(111) NOT NULL,
    `batch_no` varchar(50) NOT NULL,
    `base_qty` varchar(100) NOT NULL,
    `used_base_qty` varchar(100) NOT NULL,
    `base_unit` int(11) NOT NULL,
    `conv_qty` varchar(100) NOT NULL,
    `used_conv_qty` varchar(100) NOT NULL,
    `conv_unit` int(11) NOT NULL,
    `vendor_id` int(11) NOT NULL,
    `remark` text NOT NULL,
    `status` int(11) NOT NULL DEFAULT '0',
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`extra_stock_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `work_order_extra_reserve_temp` (
    `wo_reserve_temp_id` int(11) NOT NULL AUTO_INCREMENT,
    `rp_id` varchar(111) NOT NULL,
    `product_id` int(11) NOT NULL,
    `reserve_qty` varchar(111) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `extra_stock_id` varchar(111) NOT NULL COMMENT 'batch_id',
    `status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` double NOT NULL,
    `company_id` int(11) NOT NULL,
    `batch_no` varchar(100) NOT NULL,
    PRIMARY KEY (`wo_reserve_temp_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_extra_stock_module',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 05/09/2022

//Harshil db changes start 26/09/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='Accessorice_Update'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {


  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_product_acc_product` (
    `acc_id` int(11) NOT NULL AUTO_INCREMENT,
    `acc_product_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `acc_product_qty` int(11) NOT NULL,
    `acc_product_desc` longtext NOT NULL,
    `cdate` date NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`acc_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_inq_access_trn` (
    `inq_acc_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `pid` int(11) NOT NULL,
    `qty` int(11) NOT NULL,
    `acce_rate` double(11,2) NOT NULL,
    `acc_amount` double(11,2) NOT NULL,
    `product_desc` longtext NOT NULL,
    `inq_access_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`inq_acc_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_quto_access_trn` (
    `inq_acc_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `pid` int(11) NOT NULL,
    `qty` int(11) NOT NULL,
    `acce_rate` double(11,2) NOT NULL,
    `acc_amount` double(11,2) NOT NULL,
    `product_desc` longtext NOT NULL,
    `inq_access_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`inq_acc_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('Accessorice_Update',0,'$date')");
  //common branch update in db log table end
}
//Harshil db changes stop 26/09/2022


//Harshil db changes start 27/09/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='manual_indent'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {



  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_pre_trn` ADD `so_id` INT NOT NULL AFTER `pre_trn_id`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `sales_order_id` INT NOT NULL AFTER `rp_id`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('manual_indent',0,'$date')");
  //common branch update in db log table end
}
//Harshil db changes stop 27/09/2022


$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='process_end_time_qc_new'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {




  $query_invoicetype = $dbcon->query("DROP TABLE tbl_temp_qc");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS tbl_temp_qc (
    temp_id int(11) NOT NULL AUTO_INCREMENT,
    product_id int(11) NOT NULL,
    process_id int(11) NOT NULL,
    p_id int(11) NOT NULL,
    rp_id int(11) NOT NULL,
    qty varchar(50) NOT NULL,
    type int(11) NOT NULL COMMENT '1-accept, 2-reject, 3-reprocess',
    unit_id int(11) NOT NULL,
    cdate datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id int(11) NOT NULL,
    company_id int(11) NOT NULL,
    remark text NOT NULL,
    status int(11) NOT NULL,
    grn_trn_id int(11) NOT NULL,
    grn_sub_trn_id int(11) NOT NULL,
    branch_id int(11) NOT NULL,
    new_product_id int(11) NOT NULL,
    new_unit_id int(11) NOT NULL,
    new_process_id int(11) NOT NULL,
    batch_id int(11) NOT NULL,
    new_godown_id int(11) NOT NULL,
    PRIMARY KEY (temp_id)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('process_end_time_qc_new',0,'$date')");
  //common branch update in db log table end
}

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='process_end_time_qc_new1'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_qc` ADD `qc_type` INT NOT NULL AFTER `qc_date`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `grn_accept_qty` VARCHAR(50) NOT NULL , ADD `grn_reject_qty` VARCHAR(50) NOT NULL , ADD `grn_reprocess_qty` VARCHAR(50) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process` ADD `ext_stock_vendor_id` INT NOT NULL AFTER `extra_stock_material_reserve`");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('process_end_time_qc_new1',0,'$date')");
  //common branch update in db log table end
}


//Harshil db changes stop 27/09/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='indent_approv_time_dec'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {




  $query_invoicetype = $dbcon->query("ALTER TABLE `approve_indent` ADD `product_desc` LONGTEXT NOT NULL AFTER `approve_unit`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchasetrntemp` CHANGE `product_des` `product_desc` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('indent_approv_time_dec',0,'$date')");
  //common branch update in db log table end
}
//Harshil db changes stop 27/09/2022


//sanat db changes start 29/09/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_release_material_changes";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_module_type` (`module_type_id`, `module_name`, `status`, `cdate`) VALUES (NULL, 'RELEASE MATERIAL', '', CURRENT_TIMESTAMP)");

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'RELEASE MATERIAL', '0', '0', '51', '3', 'REL/MAT/', '/22-23', '1', '0', '2021-04-01 05:30:00', '1', '2', '1', '1', '', '1')");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_material_release` (
    `material_id` int(11) NOT NULL AUTO_INCREMENT,
    `release_no` varchar(50) NOT NULL,
    `release_date` date NOT NULL,
    `to_godown_id` int(11) NOT NULL,
    `to_user_id` int(11) NOT NULL,
    `release_qty` varchar(255) NOT NULL,
    `release_unit` int(11) NOT NULL,
    `rp_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `process_id` int(11) NOT NULL,
    `p_id` varchar(111) NOT NULL,
    `cdate` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`material_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_start_stop_production` (
    `start_stop_id` int(11) NOT NULL AUTO_INCREMENT,
    `material_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `p_id` int(11) NOT NULL,
    `release_qty` varchar(100) NOT NULL,
    `release_unit` int(11) NOT NULL,
    `rp_id` int(11) NOT NULL,
    `pending_qty` VARCHAR(100) NOT NULL,
    `start_qty` VARCHAR(100) NOT NULL,
    `end_qty` VARCHAR(100) NOT NULL,
    `complete_status` INT(11) NOT NULL COMMENT '0-pending, 1- started, 3-complete',
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`start_stop_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_store_material_return` (
    `return_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `godown_id` int(11) NOT NULL,
    `base_qty` varchar(100) NOT NULL,
    `base_unit` int(11) NOT NULL,
    `conv_qty` varchar(100) NOT NULL,
    `conv_unit` int(11) NOT NULL,
    `from_user_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    PRIMARY KEY (`return_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_material_release_trn` (
    `material_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `material_id` int(11) NOT NULL,
    `start_stop_id` int(11) NOT NULL,
    `rp_id` int(11) NOT NULL,
    `parent_rp_id` int(11) NOT NULL,
    `p_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `base_qty` varchar(111) NOT NULL,
    `used_base_qty` VARCHAR(100) NOT NULL,
    `base_unit` int(11) NOT NULL,
    `conv_qty` varchar(11) NOT NULL,
    `used_conv_qty` VARCHAR(100) NOT NULL,
    `conv_unit` int(11) NOT NULL,
    `godown_id` int(11) NOT NULL,
    `to_godown_id` int(11) NOT NULL,
    `stock_id` int(11) NOT NULL,
    `to_user_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `batch_no` VARCHAR(30) NOT NULL,
    `release_status` INT(11) NOT NULL DEFAULT '0',
    `return_status` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - no , 1 - yes',
    PRIMARY KEY (`material_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_stock_transfer_batch_stock_tmp` (
    `batch_stk_id` int(11) NOT NULL AUTO_INCREMENT,
    `stock_transfer_trn_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `stock_id` int(11) NOT NULL,
    `reserve_id` int(11) NOT NULL,
    `qty` int(11) NOT NULL,
    `unitid` int(11) NOT NULL,
    `status` tinyint(4) NOT NULL,
    `cdate` datetime NOT NULL,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    PRIMARY KEY (`batch_stk_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_godown_stock_return` (
    `return_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `material_trn_id` int(11) NOT NULL,
    `base_qty` varchar(100) NOT NULL,
    `base_unit` int(11) NOT NULL,
    `conv_qty` varchar(100) NOT NULL,
    `conv_unit` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `store_accept` int(11) NOT NULL COMMENT '0-pending, 1-approve, 2-reject',
    `godown_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`return_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_godown_stock_return_trn` (
    `return_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `return_id` VARCHAR(111) NOT NULL,
    `product_id` VARCHAR(111) NOT NULL,
    `base_qty` varchar(100) NOT NULL,
    `base_unit` int(11) NOT NULL,
    `status` int(11) NOT NULL COMMENT '3-pending,0-done',
    `from_godown_id` int(11) NOT NULL,
    `to_godown_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `remark` int(11) NOT NULL,
    PRIMARY KEY (`return_trn_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_release_material_changes',0,'$date')");
  // common branch update in db log table end
}
//Sanat Changes 15/06/22 End

//sanat db changes start 25/07/2022
$cnt = 0;

$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_bom_extra_no_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `bom_extra_no` VARCHAR(50) NOT NULL");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_bom_extra_no` (
    `ext_id` int(11) NOT NULL AUTO_INCREMENT,
    `ext_no` varchar(50) NOT NULL,
    `bom_id` int(11) NOT NULL,
    `main_bom_id` int(11) NOT NULL,
    `parent_bom_id` int(11) NOT NULL,
    `bom_version_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`ext_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_bom_extra_no_add',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 29/09/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='bill_sun_mis_que'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `adjust_sale_amt` INT NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `acc_post_sale_amt` INT NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `adjust_sale_party_amt` INT NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `party_acc_post_sale_amt` INT NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `adjust_purchase_amt` INT NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `acc_post_purchase_amt` INT NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `party_acc_post_purchase_amt` INT NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `adjust_purchase_party_amt` INT NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `sundry_hsn` INT NOT NULL AFTER `apply_gst`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger_bill_sundry` ADD `bill_sundry_name` VARCHAR(200) NOT NULL AFTER `bill_sundry_id`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('bill_sun_mis_que',0,'$date')");
  //common branch update in db log table end
}

//harshil 15-10-2022///////////


$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sales_order_print_after_approval'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `sales_order_print_after_approval` INT(11) NOT NULL COMMENT '0-No, 1-yes' AFTER `bom_extra_no`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `sreeji_stilix` INT(11) NOT NULL AFTER `chemitek_permission`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sales_order_print_after_approval',0,'$date')");
  //common branch update in db log table end
}

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sreeji_stilix_special_permission'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `sreeji_stilix_permission` INT(11) NOT NULL AFTER `chemitek_permission`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sreeji_stilix_special_permission',0,'$date')");
  //common branch update in db log table end
}

/////Harshil  15-10-2022//////////////

////////////pathik 17-10-2022  start////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='onebranchwise'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `default_branch_id` INT NOT NULL AFTER `branch_wise_manage`");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_company_configuration` SET `branch_wise_manage` = '1' WHERE 1");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('onebranchwise',0,'$date')");
  //common branch update in db log table end
}

////////////pathik 17-10-2022  end////////////



//sanat db changes start 17/10/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_label_print_process'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `label_print_process_id` INT(11) NOT NULL DEFAULT '0'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_label_print_process',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 17/10/2022


//Maulik db changes Start 18/10/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='aeon_cat_change'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` ADD `cat_id` INT NOT NULL AFTER `product_id`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('aeon_cat_change',0,'$date')");
}
//Maulik db changes stop 18/10/2022


//sanat db changes start 18/10/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_supllier__tc_no'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `supplier_tc_no` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `supplier_tc_no` VARCHAR(255) NULL DEFAULT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_supllier__tc_no',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 18/10/2022

//MAULIK db changes start 19/10/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_heat_no_saperator'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `heat_no_saperator` VARCHAR(200) NOT NULL AFTER `supplier_tc_no`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_qc` ADD `check_tc_no` VARCHAR(200) NOT NULL AFTER `qc_date`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_inquiry` ADD `gst_type` INT NOT NULL AFTER `quotation_required_date`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_terms_condition` CHANGE `tc_for` `tc_for` VARCHAR(400) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `followup_inquiry_show` INT NOT NULL COMMENT '0.no 1.yes' AFTER `heat_no_saperator`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company` ADD `header_logo_height` VARCHAR(100) NOT NULL AFTER `letter_head_right_margin`, ADD `header_logo_width` VARCHAR(100) NOT NULL AFTER `header_logo_height`");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_ledger_attach_doc` (
    `led_attach_id` int(11) NOT NULL AUTO_INCREMENT,
    `l_id` int(11) NOT NULL,
    `cust_id` int(11) NOT NULL,
    `led_doc_name` varchar(400) NOT NULL,
    `led_attch_file` varchar(400) NOT NULL,
    `led_attach_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`led_attach_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_ledger_attach_doc` ADD `ref_type` INT NOT NULL COMMENT '0.ledger 1.party' AFTER `led_attch_file`");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` ADD `unit_wise` INT NOT NULL AFTER `rate_unit`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_heat_no_saperator',0,'$date')");
  //common branch update in db log table end
}
//MAULIK db changes stop 19/10/2022




//sanat db changes start 04/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='workorder_shortclose_db_changes'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `workorder_short_close` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `workorder_short_close` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `shortclose_qty` VARCHAR(50) NOT NULL DEFAULT '0'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('workorder_shortclose_db_changes',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 04/11/2022




//sanat db changes start 09/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_drawing_delete_imgname'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_drawing_revision_image` ADD `image_name` VARCHAR(50) NOT NULL AFTER `revision_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_drawing_image` ADD `image_name` VARCHAR(50) NOT NULL AFTER `revision_id`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_drawing_delete_imgname',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 09/11/2022



//sanat db changes start 10/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_drawing_delete_imgname2'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_drawing_revision_image` ADD `type` INT(11) NOT NULL COMMENT '1-drawing, 2-revision'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_drawing_delete_imgname2',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 10/11/2022


//pathik db changes start 11/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='pathik_aeon'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `closing_date_diff` VARCHAR(10) NOT NULL AFTER `followup_inquiry_show`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `inq_name_using_comapany` INT NOT NULL AFTER `closing_date_diff`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('pathik_aeon',0,'$date')");
  //common branch update in db log table end
}
//pathik db changes stop 11/11/2022




//sanat db changes start 10/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_woroder_material_attc'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_workorder_attachments` (
    `attach_id` int(11) NOT NULL AUTO_INCREMENT,
    `sp_id` int(11) NOT NULL,
    `image_name` varchar(100) NOT NULL,
    `file_name` varchar(150) NOT NULL,
    `file_path` text NOT NULL,
    `user_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`attach_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_woroder_material_attc',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 10/11/2022




//sanat db changes start 14/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='store_coment_add_for_desc'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_store_request` CHANGE `rp_id` `rp_id` INT(11) NOT NULL COMMENT 'product_id'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('store_coment_add_for_desc',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 14/11/2022



//sanat db changes start 16/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='drawing_slab_wise_apprv'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_drawing` ADD `approve_status` INT(11) NOT NULL DEFAULT '0' COMMENT ' ''0-pending, 1- prepare, 2 - check, 3 - approve'' ' AFTER `drawing_status`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_revision` ADD `approve_status` INT(11) NOT NULL DEFAULT '0' COMMENT ' ''0-pending, 1- prepare, 2 - check, 3 - approve'' ' AFTER `revision_status`");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_drawing` SET `approve_status`= 3");
  $query_invoicetype = $dbcon->query("UPDATE `tbl_revision` SET `approve_status`= 3");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('drawing_slab_wise_apprv',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 16/11/2022



//sanat db changes start 16/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='drawing_byuser_bydate'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_drawing` ADD `prepared_by_user_id` INT(11) NOT NULL , ADD `prepared_date` DATETIME NOT NULL , ADD `checked_by_user_id` INT(11) NOT NULL , ADD `checked_date` DATETIME NOT NULL , ADD `approved_user_id` INT(11) NOT NULL , ADD `approved_date` DATETIME NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_revision` ADD `prepared_by_user_id` INT(11) NOT NULL , ADD `prepared_date` DATETIME NOT NULL , ADD `checked_by_user_id` INT(11) NOT NULL , ADD `checked_date` DATETIME NOT NULL , ADD `approved_user_id` INT(11) NOT NULL , ADD `approved_date` DATETIME NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('drawing_byuser_bydate',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 16/11/2022


//sanat db changes start 21/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='workordr_batch_wise_stock_allocation'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `wo_bw_alloc_stock` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - no , 1 - yes'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('workordr_batch_wise_stock_allocation',0,'$date')");
  //common branch update in db log table end
}

//sanat db changes stop 21/11/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sspl_industries'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `salesorder_product_tempdata` (
      `product_tempdata_id` int(11) NOT NULL AUTO_INCREMENT,
      `line_num` int(11) NOT NULL,
      `error` varchar(200) NOT NULL,
      `company_id` int(11) NOT NULL,
      PRIMARY KEY (`product_tempdata_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `insert_prouct` INT NOT NULL COMMENT '0.System / 1.Import ' AFTER `total_conv`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `inward_date` DATE NOT NULL AFTER `quot_type`, ADD `status` INT NOT NULL COMMENT '0.partial 1.full 2.partial/hold' AFTER `inward_date`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `sspl` INT NOT NULL AFTER `power_drive`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `so_product_import` INT NOT NULL AFTER `smpl_mfg_licence`");

  $date = date("Y-m-d H:i:s");
  //$query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sspl_industries',0,'$date')");
}


//sanat db changes start 25/07/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='sanat_workorder_shortage'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}


if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `workorder_type` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - regular, 1 - shortage'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `workorder_type` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - regular, 1 - shortage'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `shortage_complete_qty` VARCHAR(100) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `work_order_reserve_temp` CHANGE `rp_id` `rp_id` VARCHAR(111) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_material_release_trn` ADD `batch_no` VARCHAR(100) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_material_release_trn` CHANGE `stock_id` `stock_id` VARCHAR(111) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `work_order_reserve_temp` ADD `batch_no` VARCHAR(100) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `work_order_reserve_temp` CHANGE `stock_id` `stock_id` VARCHAR(111) NOT NULL COMMENT 'batch_id'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('sanat_workorder_shortage',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 25/07/2022


//sanat db changes start 28/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_dl_no_comp'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `smpl_dl_no` VARCHAR(50) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_dl_no_comp',0,'$date')");

}

//common branch update in db log table end

//sanat db changes stop 28/11/2022



//Maulik db changes start 05-12-2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dur_so_prof_sub'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `pid` INT NOT NULL AFTER `project_wise`");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_so_access_trn` (
      `inq_acc_id` int(11) NOT NULL AUTO_INCREMENT,
      `product_id` int(11) NOT NULL,
      `pid` int(11) NOT NULL,
      `qty` varchar(200) NOT NULL,
      `acce_rate` double(10,2) NOT NULL,
      `acc_amount` double(10,2) NOT NULL,
      `product_desc` text NOT NULL,
      `inq_access_status` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      `branch_id` int(11) NOT NULL,
      `cdate` timestamp NOT NULL,
      PRIMARY KEY (`inq_acc_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_proforma_access_trn` (
    `inq_acc_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `pid` int(11) NOT NULL,
    `qty` varchar(200) NOT NULL,
    `acce_rate` double(10,2) NOT NULL,
    `acc_amount` double(10,2) NOT NULL,
    `product_desc` text NOT NULL,
    `inq_access_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL,
    PRIMARY KEY (`inq_acc_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_trn` ADD `pid` INT NOT NULL AFTER `invoice_id`");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS tbl_item_master_field (
    item_master_field_id int(11) NOT NULL AUTO_INCREMENT,
    item_master_field varchar(255) NOT NULL,
    item_master_field_db_name varchar(255) NOT NULL,
    user_id int(11) NOT NULL,
    muser_id int(11) NOT NULL,
    cdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    mdate timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    item_master_field_status int(11) NOT NULL,
    company_id int(11) NOT NULL,
    branch_id int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (item_master_field_id),
    KEY revision_number (item_master_field,user_id,muser_id,item_master_field_status),
    KEY company_id (company_id)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS tbl_item_master_field_value (
    item_master_field_value_id int(11) NOT NULL AUTO_INCREMENT,
    item_master_field_id int(11) NOT NULL,
    item_master_field_value varchar(255) NOT NULL,
    user_id int(11) NOT NULL,
    muser_id int(11) NOT NULL,
    cdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    mdate timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    item_master_field_value_status int(11) NOT NULL,
    company_id int(11) NOT NULL,
    branch_id int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (item_master_field_value_id),
    KEY revision_number (item_master_field_id,user_id,muser_id,item_master_field_value_status),
    KEY company_id (company_id)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dur_so_prof_sub',0,'$date')");
}
//Maulik db changes stop 


//sanat db changes start 14/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='reserve_stock_deduct_from_entry'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_reserve_stock` ADD `perent_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_reserve_stock` ADD `used_base_stock` VARCHAR(100) NOT NULL DEFAULT '0' AFTER `approve_base_stock`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_reserve_stock` ADD `used_convert_stock` VARCHAR(100) NOT NULL DEFAULT '0' AFTER `approve_convert_stock`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_process_stock_trn` ADD `used_convert_stock` VARCHAR(50) NOT NULL AFTER `used_base_stock`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_process_reserve_stock` ADD `used_conv_stock` VARCHAR(100) NOT NULL DEFAULT '0' AFTER `approve_convert_stock`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('reserve_stock_deduct_from_entry',0,'$date')");
  //common branch update in db log table end
}



//sanat db changes start 28/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='jobwork_grn_company_seting'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `jobwork_grn` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - FIFO, 1 - SEPARATE'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('jobwork_grn_company_seting',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 28/11/2022


//sanat db changes start 30/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='bmr_no_generate'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `smpl_mfg_licence` VARCHAR(100) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `qc_sample_qty` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'BMR NO', '0', '0', '56', '3', 'BMR/', '/22-23', '1', '0', '2021-04-01 05:30:00', '1', '2', '1', '1', '', '1')");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_smpl_bmr_no` (
    `bmr_id` int(11) NOT NULL AUTO_INCREMENT,
    `bmr_no` varchar(50) NOT NULL,
    `p_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`bmr_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('bmr_no_generate',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 30/11/2022

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='power_drive'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `product_name_field` (
        `product_field_id` int(11) NOT NULL AUTO_INCREMENT,
        `cdate` timestamp NOT NULL,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        PRIMARY KEY (`product_field_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `product_name_field` ADD `product_id` INT NOT NULL AFTER `product_field_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `product_name_field` ADD `branch_id` INT NOT NULL AFTER `company_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_item_master_field` ADD `priority` INT NOT NULL AFTER `item_master_field_db_name`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `power_drive` INT NOT NULL AFTER `sreeji_stilix`");

  $query_dy = "select * from tbl_item_master_field where item_master_field_status=0  order by priority ASC";
  $dy_result = $dbcon->query($query_dy);
  while ($dy_row = mysqli_fetch_array($dy_result)) {

    /*echo "ALTER TABLE `product_mst` ADD ".$dy_row['item_master_field_db_name']." INT(11) NOT NULL";
    echo "<br>";*/


    $query_invoicetypes = $dbcon->query("ALTER TABLE `product_name_field` ADD " . $dy_row['item_master_field_db_name'] . " INT(11) NOT NULL");
  }

  $date = date("Y-m-d H:i:s");
  //$query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('bmr_no_generate',0,'$date')");
}


$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='po_so_attach_doc'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_po_attch` (
    `po_attach_id` int(11) NOT NULL AUTO_INCREMENT,
    `attach_doc_name` varchar(250) NOT NULL,
    `attach_file` varchar(250) NOT NULL,
    `attach_status` int(11) NOT NULL,
    `purchaseorder_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`po_attach_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_inquiry` ADD `task_priority_id` INT NOT NULL AFTER `gst_type`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_followup` ADD `assign_user_ids_read` INT NOT NULL COMMENT '0.unread 1.read' AFTER `branch_id`, ADD `user_id_read` INT NOT NULL COMMENT '0.unread 1.read' AFTER `assign_user_ids_read`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `dispatch_status` INT NOT NULL COMMENT '0.Pending 1.Finally done 2.done' AFTER `invoice_condition`");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_dispatch_log` (
    `dispatch_logid` int(11) NOT NULL AUTO_INCREMENT,
    `invoice_id` int(11) NOT NULL,
    `dispatch_status` int(11) NOT NULL COMMENT '0.Pending 1.Finally Done 2.Done',
    `remark` text NOT NULL,
    `attach_document` varchar(200) NOT NULL,
    `dispatch_ref` varchar(200) NOT NULL,
    `ref_table_id` int(11) NOT NULL,
    `is_delete` int(11) NOT NULL COMMENT '0.active 1.delete',
    `cdate` timestamp NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`dispatch_logid`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_so_attch` ADD `design_dept` INT NOT NULL COMMENT '0.No 1.Yes' AFTER `so_attach_id`");


  $query_invoicetypes = $dbcon->query("UPDATE `tbl_so_attch` SET `design_dept`=1 WHERE `design_dept`=0");

  $date = date("Y-m-d H:i:s");
  //$query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('bmr_no_generate',0,'$date')");
}

//sanat db changes start 28/11/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='process_end_time_qty_variation'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `is_qty_variation` INT(11) NOT NULL COMMENT '0 - no , 1 - yes'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `variation_qty_plus` VARCHAR(50) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `variation_qty_minus` VARCHAR(50) NOT NULL DEFAULT '0'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process` ADD `is_qty_variation` INT(11) NOT NULL COMMENT '0 - no , 1 - yes'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process` ADD `variation_qty_plus` VARCHAR(50) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process` ADD `variation_qty_minus` VARCHAR(50) NOT NULL DEFAULT '0'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn` ADD `is_qty_variation` INT(11) NOT NULL COMMENT '0 - no , 1 - yes'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn` ADD `variation_qty_plus` VARCHAR(50) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn` ADD `variation_qty_minus` VARCHAR(50) NOT NULL DEFAULT '0'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `is_qty_variation` INT(11) NOT NULL COMMENT '0 - no , 1 - yes'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `variation_qty_plus` VARCHAR(50) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn_sub_trn` ADD `variation_qty_minus` VARCHAR(50) NOT NULL DEFAULT '0'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_start_stop_production` ADD `is_qty_variation` INT(11) NOT NULL COMMENT '0 - no , 1 - yes'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_start_stop_production` ADD `variation_qty_plus` VARCHAR(50) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_start_stop_production` ADD `variation_qty_minus` VARCHAR(50) NOT NULL DEFAULT '0'");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process_trn` ADD `is_qty_variation` INT(11) NOT NULL COMMENT '0 - no , 1 - yes'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process_trn` ADD `variation_qty_plus` VARCHAR(50) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_allocate_process_trn` ADD `variation_qty_minus` VARCHAR(50) NOT NULL DEFAULT '0'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('process_end_time_qty_variation',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 28/11/2022


//sanat db changes start 21/12/2022
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='store_request_isue_changs_chmtk'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("UPDATE tbl_store_request SET store_request_status = 1 WHERE (base_qty-release_qty) <= 0");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('store_request_isue_changs_chmtk',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 21/12/2022

//sanat db changes start 04/01/2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='jobwork_short_close_qry_upd'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work` ADD `short_close` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_trn` ADD `short_close` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn` ADD `short_close` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_trn` ADD `short_close_qty` VARCHAR(50) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_trn` ADD `short_close_conv_qty` VARCHAR(50) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn` ADD `short_close_qty` VARCHAR(50) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn`  ADD `short_close_conv_qty` VARCHAR(50) NOT NULL DEFAULT '0'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('jobwork_short_close_qry_upd',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 04/01/2023

//pathik db changes start 09-01-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_2_special_field_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `product_mst` ADD `smpl_size` VARCHAR(110) NOT NULL AFTER `pro_class_mst`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `product_mst` ADD `smpl_material` VARCHAR(110) NOT NULL AFTER `smpl_size`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_2_special_field_add',0,'$date')");
  //common branch update in db log table end
}
//pathik db changes stop 09-01-2023


//sanat db changes start 10/01/2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='jet_techno_task_changes_crm'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_task` ADD `task_in_out` INT(11) NOT NULL DEFAULT '1' COMMENT '0 - out, 1 - IN '");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `jet_technologies_permission` INT(11) NOT NULL DEFAULT '0' AFTER `sp_field_permission_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_stock_trn` ADD `mfg_date` VARCHAR(30) NOT NULL , ADD `exp_date` VARCHAR(30) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_process_stock_trn` ADD `mfg_date` VARCHAR(30) NOT NULL , ADD `exp_date` VARCHAR(30) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `opening_stock_mst` ADD `mfg_date` VARCHAR(30) NOT NULL , ADD `exp_date` VARCHAR(30) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `process_opening_stock_mst` ADD `mfg_date` VARCHAR(30) NOT NULL , ADD `exp_date` VARCHAR(30) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_item_wise_qc` ADD `sr_no` INT(11) NOT NULL AFTER `item_qc_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_item_wise_qc` ADD `batch_id` INT(11) NOT NULL AFTER `item_qc_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn` ADD `resource_id` INT(11) NOT NULL DEFAULT '0'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work` ADD `resource_id` INT(11) NOT NULL DEFAULT '0'");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('jet_techno_task_changes_crm',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 10/01/2023



//sanat db changes start 19/01/2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='packing_master_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `packing_mst` (
    `packing_id` int(11) NOT NULL AUTO_INCREMENT,
    `packing_name` varchar(50) NOT NULL,
    `size` varchar(100) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) DEFAULT '0',
    `branch_id` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`packing_id`),
    KEY `packing_id` (`packing_id`,`packing_name`,`status`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `creative_fastners_permission` INT(11) NOT NULL DEFAULT '0' AFTER `sp_field_permission_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `packing_qty` VARCHAR(50) NOT NULL , ADD `packing_status` INT(11) NOT NULL DEFAULT '0' COMMENT '0- pending, 1 - done'");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_stock_trn` ADD `workorder_id` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_workorder_packing` (
    `workorder_packing_id` int(11) NOT NULL AUTO_INCREMENT,
    `workorder_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `packing_qty` varchar(20) NOT NULL,
    `remark` varchar(100) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`workorder_packing_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_workorder_packing_trn` (
    `workorder_packing_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `workorder_packing_id` int(11) NOT NULL,
    `workorder_id` int(11) NOT NULL,
    `packing_id` int(11) NOT NULL,
    `packing_size` varchar(20) NOT NULL,
    `box_qty` int(11) NOT NULL,
    `total_box_qty` varchar(20) NOT NULL,
    `batch_no` varchar(100) NOT NULL,
    `sr_no` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`workorder_packing_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('packing_master_add',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 19/01/2023

//Maulik db changes Start 09-01-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='libra_special_field_add'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `libra_engineering_permission` INT NOT NULL AFTER `sspl`");
  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_inquiry_review` (
    `inquiry_review_id` int(11) NOT NULL AUTO_INCREMENT,
    `customer_address` int(11) NOT NULL COMMENT '0.No 1.Yes',
    `inquiry_no_date` int(11) NOT NULL,
    `technical_spacification` int(11) NOT NULL,
    `pro_speci_req` int(11) NOT NULL,
    `cust_draw_enclose` int(11) NOT NULL,
    `scope_inspection` int(11) NOT NULL,
    `delivery` int(11) NOT NULL,
    `pricing_available` int(11) NOT NULL,
    `com_term_clear` int(11) NOT NULL,
    `earn_money_deposit` int(11) NOT NULL,
    `bank_guarantee_dd_tdr` int(11) NOT NULL,
    `sep_cov_price_techbid` int(11) NOT NULL,
    `del_due_date` int(11) NOT NULL,
    `any_other_comment` int(11) NOT NULL,
    `ref_wo_no` varchar(250) NOT NULL,
    `reviewed_by` varchar(250) NOT NULL,
    `wo_date` date NOT NULL,
    `approved_by` varchar(250) NOT NULL,
    `inquiry_review_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `inquiry_id` int(11) NOT NULL,
    PRIMARY KEY (`inquiry_review_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_sales_order_review_data` (
    `order_review_id` int(11) NOT NULL AUTO_INCREMENT,
    `sales_order_id` int(11) NOT NULL,
    `sales_ordertrn_id` int(11) NOT NULL,
    `project` varchar(300) NOT NULL,
    `datasheet` varchar(300) NOT NULL,
    `our_quotation_ref` varchar(400) NOT NULL,
    `legal_requirements` varchar(400) NOT NULL,
    `operating_temperature` varchar(400) NOT NULL,
    `operating_pressure` varchar(400) NOT NULL,
    `fluid_service_application` varchar(400) NOT NULL,
    `design_mfg_standard` varchar(400) NOT NULL,
    `testing_standard` varchar(400) NOT NULL,
    `qsl` varchar(400) NOT NULL,
    `qty` varchar(400) NOT NULL,
    `required_del_date` date NOT NULL,
    `body_bonnet_cover` varchar(400) NOT NULL,
    `gate_ball_disc_plug` varchar(400) NOT NULL,
    `seat_ring` varchar(400) NOT NULL,
    `steam` varchar(400) NOT NULL,
    `stud_nut` varchar(400) NOT NULL,
    `back_seat_bush` varchar(400) NOT NULL,
    `gasket` varchar(400) NOT NULL,
    `packing_seals` varchar(400) NOT NULL,
    `end_connection` varchar(400) NOT NULL,
    `valve_ot` int(11) NOT NULL,
    `inspection_by` int(11) NOT NULL,
    `scope_of_inspaction` varchar(400) NOT NULL,
    `applicable_nde` varchar(400) NOT NULL,
    `af_sales_service` varchar(400) NOT NULL,
    `coating_painting_req` varchar(400) NOT NULL,
    `packing_req` varchar(400) NOT NULL,
    `making_product` varchar(400) NOT NULL,
    `making_packing` varchar(400) NOT NULL,
    `api_monogram_making` varchar(400) NOT NULL,
    `delivery_due_date` date NOT NULL,
    `customer_contact_detail` varchar(400) NOT NULL,
    `delivery_location` varchar(400) NOT NULL,
    `documents_submit` varchar(400) NOT NULL,
    `payment_terms` varchar(400) NOT NULL,
    `insurance` varchar(400) NOT NULL,
    `freight` varchar(400) NOT NULL,
    `remark` text NOT NULL,
    `bore_type_size` varchar(400) NOT NULL,
    `face_end_dimension` varchar(400) NOT NULL,
    `intermediate_design_pressure` varchar(400) NOT NULL,
    `service_compatibillity` varchar(400) NOT NULL,
    `valve_orentation` varchar(400) NOT NULL,
    `pressure_balance_hole` varchar(400) NOT NULL,
    `end_connectors_type` varchar(400) NOT NULL,
    `external_loads` varchar(400) NOT NULL,
    `flow_coefficient_cvkv` varchar(400) NOT NULL,
    `valve_topwork_diamention` varchar(400) NOT NULL,
    `bto` varchar(400) NOT NULL,
    `btc` varchar(400) NOT NULL,
    `rto` varchar(400) NOT NULL,
    `rtc` varchar(400) NOT NULL,
    `eto` varchar(400) NOT NULL,
    `etc` varchar(400) NOT NULL,
    `valve_drive_train_mast` varchar(400) NOT NULL,
    `length_direction_stroke_oc_linear_valve` varchar(400) NOT NULL,
    `angle_rotation_partturn_checkvalve` varchar(400) NOT NULL,
    `direction_rotation_number_multiturn_valve` varchar(400) NOT NULL,
    `enable_valve_maintain_position` varchar(400) NOT NULL,
    `breakaway_anglepercent_stroke` varchar(400) NOT NULL,
    `num_turns_manualy_opevalve` varchar(400) NOT NULL,
    `flange_bolting_studded_outlet_endconnector` varchar(400) NOT NULL,
    `chemcomp_prescontai_controlling_material` varchar(400) NOT NULL,
    `valve_seat_functionality` varchar(400) NOT NULL,
    `extended_steam_shaft_assemblies` varchar(400) NOT NULL,
    `boulting_sour_service` varchar(400) NOT NULL,
    `locking_device` varchar(400) NOT NULL,
    `position_indicator` varchar(400) NOT NULL,
    `drain` varchar(400) NOT NULL,
    `vent` varchar(400) NOT NULL,
    `drain_pressure_ventlines` varchar(400) NOT NULL,
    `sealant_injection` varchar(400) NOT NULL,
    `drain_vent_injection_valves` varchar(400) NOT NULL,
    `paggabillity` varchar(400) NOT NULL,
    `welding_overlay_iron_dilution` varchar(400) NOT NULL,
    `weld_repair_forgings` varchar(400) NOT NULL,
    `pressure_boundary_bolting_hardness_testing` varchar(400) NOT NULL,
    `inservice_field_testing` varchar(400) NOT NULL,
    `anti_static_device_test` varchar(400) NOT NULL,
    `torque_test` varchar(400) NOT NULL,
    `fire_safe_test` varchar(400) NOT NULL,
    `drive_train_strength_test` varchar(400) NOT NULL,
    `supplementry_test` varchar(400) NOT NULL,
    `cavity_relief_test` varchar(400) NOT NULL,
    `dbb_valve_test` varchar(400) NOT NULL,
    `dib1_test` varchar(400) NOT NULL,
    `dib2_seat_test` varchar(400) NOT NULL,
    `dib1_dib2_test_valves` varchar(400) NOT NULL,
    `hardness_test` varchar(400) NOT NULL,
    `charpy_impact_test` varchar(400) NOT NULL,
    `hic_test` varchar(400) NOT NULL,
    `high_pressure_gas_test` varchar(400) NOT NULL,
    `fugitive_emission_test` varchar(400) NOT NULL,
    `gauge_drift_test` varchar(400) NOT NULL,
    `pressure_testing_valve_hydrostatic` varchar(400) NOT NULL,
    `special_flanges_mechanical_joints` varchar(400) NOT NULL,
    `thirdparty_witness` varchar(400) NOT NULL,
    `hydroshell_nonassembled_cond` varchar(400) NOT NULL,
    `corrosion_protection_measures_longterm_storage` varchar(400) NOT NULL,
    `external_coating_painting_valves` varchar(400) NOT NULL,
    `corrosion_resistant_metalic_surfaces` varchar(400) NOT NULL,
    `disassembly_maintainance_tool` varchar(400) NOT NULL,
    `support_rib_legs` varchar(400) NOT NULL,
    `valve_lifting` varchar(400) NOT NULL,
    `use_assembly_lubricant` varchar(400) NOT NULL,
    `additional_requirements` varchar(400) NOT NULL,
    `auxilliary_connope` varchar(400) NOT NULL,
    `valve_orientation` int(11) NOT NULL,
    `hard_facing_body_wedge_guides` varchar(400) NOT NULL,
    `bonnet_gaskettype` int(11) NOT NULL,
    `bonnet_joint_flange_facing` int(11) NOT NULL,
    `tapped_openings` varchar(400) NOT NULL,
    `type_wedge` int(11) NOT NULL,
    `lantern_ring` varchar(400) NOT NULL,
    `chain_wheel_cable` varchar(400) NOT NULL,
    `handwheel_gearbox_actuatortype` varchar(400) NOT NULL,
    `alternate_stem_packing_materials` varchar(400) NOT NULL,
    `bonnet_bolting_material` varchar(400) NOT NULL,
    `stuffing_box_surface_finish` int(11) NOT NULL,
    `valve_paint_color` varchar(400) NOT NULL,
    `additional_req` varchar(400) NOT NULL,
    `ref_approve_gad` varchar(200) NOT NULL,
    `raf_approve_qap` varchar(200) NOT NULL,
    `reference_wo` varchar(200) NOT NULL,
    `prepared_by` varchar(200) NOT NULL,
    `prepared_name` varchar(200) NOT NULL,
    `consulted_by1` varchar(200) NOT NULL,
    `consulted_name1` varchar(200) NOT NULL,
    `consulted_by2` varchar(200) NOT NULL,
    `consulted_name2` varchar(200) NOT NULL,
    `review_approve_by` varchar(200) NOT NULL,
    `reviewed_approve_name` varchar(200) NOT NULL,
    `cdate` timestamp NOT NULL,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `review_status` int(11) NOT NULL,
    PRIMARY KEY (`order_review_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseorder_req_trn` ADD `vender_id` INT NOT NULL AFTER `rp_id`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  //$query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('libra_special_field_add',0,'$date')");
  //common branch update in db log table end
}
//Maulik db changes End 09-01-2023



//sanat db changes start 27/01/2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='starttime_material_deduct_entry'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {


  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_material_start_time_deduct` (
    `mt_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `rp_id` int(11) NOT NULL,
    `p_id` int(11) NOT NULL,
    `deduct_qty` varchar(100) NOT NULL,
    `godown_id` int(11) NOT NULL,
    `is_process_stock` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`mt_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('starttime_material_deduct_entry',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 27/01/2023

//pathik db changes start 09-01-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='task_trasfer_module'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_task` ADD `perent_id` INT NOT NULL AFTER `quotation_followup_start_date`, ADD `transfer_flag` INT NOT NULL AFTER `perent_id`");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_task_transfer` (
    `task_transfer_id` int(11) NOT NULL AUTO_INCREMENT,
    `old_user_id` int(11) NOT NULL,
    `new_user_id` int(11) NOT NULL,
    `approve_status` int(11) NOT NULL COMMENT '1-approve;2--disapprove',
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`task_transfer_id`),
    KEY `task_id` (`task_transfer_id`),
    KEY `task_status` (`status`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('task_trasfer_module',0,'$date')");
  //common branch update in db log table end
}
//pathik db changes stop 09-01-2023



//SANAT db changes start 30-01-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='STOCK_RESERVE_DEDECT_STOCK_FORM'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'SO DEALLOCATE NO', '0', '0', '57', '3', 'SO/DAS/', '/22-23', '1', '0', '2022-04-01 00:00:00', '1', '2', '1', '1000', '', '1')");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_so_stock_deallocate` (
    `de_allo_id` int(11) NOT NULL AUTO_INCREMENT,
    `de_allo_no` varchar(30) NOT NULL,
    `de_allo_date` date NOT NULL,
    `remark` text NOT NULL,
    `status` int(11) NOT NULL,
    `approve_status` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`de_allo_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_so_stock_deallocate_trn` (
    `de_allo_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `de_allo_id` int(11) NOT NULL,
    `sales_order_id` int(11) NOT NULL,
    `sales_ordertrn_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `de_allocate_qty` int(11) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`de_allo_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_so_deallocate_batch_stock_tmp` (
    `batch_stk_id` int(11) NOT NULL AUTO_INCREMENT,
    `de_allo_trn_id` int(11) NOT NULL,
    `sales_order_trn_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `batch_no` varchar(100) NOT NULL,
    `stock_id` varchar(111) NOT NULL,
    `reserve_id` int(11) NOT NULL,
    `qty` int(11) NOT NULL,
    `unitid` int(11) NOT NULL,
    `status` tinyint(4) NOT NULL,
    `cdate` datetime NOT NULL,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    PRIMARY KEY (`batch_stk_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_so_deallocate_stock_aprv_log` (
    `deallo_aprv_log_id` int(11) NOT NULL,
    `de_allo_id` int(11) NOT NULL,
    `approve_remark` text NOT NULL,
    `approve_status` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`deallo_aprv_log_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('STOCK_RESERVE_DEDECT_STOCK_FORM',0,'$date')");
  //common branch update in db log table end
}
//SANAT db changes stop 30-01-2023


//SANAT db changes start 02-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='grn_gri_date_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_grn` ADD `gir_date` DATETIME NOT NULL AFTER `gir_no`");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('grn_gri_date_add',0,'$date')");
  //common branch update in db log table end
}
//SANAT db changes stop 02-02-2023



//SANAT db changes start 02-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='bom_document_upload_db'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_bom_documents` (
    `doc_id` int(11) NOT NULL AUTO_INCREMENT,
    `bom_id` int(11) NOT NULL COMMENT 'tbl_drawing ref id',
    `bom_version_id` int(11) NOT NULL COMMENT 'tbl_revision ref id',
    `image_name` varchar(50) NOT NULL,
    `file_name` varchar(255) NOT NULL,
    `file_path` varchar(255) NOT NULL,
    `status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`doc_id`),
    KEY `bom_id` (`bom_id`,`bom_version_id`,`file_name`,`file_path`,`user_id`,`company_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('bom_document_upload_db',0,'$date')");
  //common branch update in db log table end
}
//SANAT db changes stop 02-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='multiple_quot_to_so'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `quotation_id` INT NOT NULL AFTER `quot_trn_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `so_quotation_type` INT NOT NULL COMMENT '0.direct so 1.Multiple Quotation' AFTER `sales_order_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `is_quotation` INT NOT NULL AFTER `cust_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `sales_order_status` INT NOT NULL COMMENT '0.Pending 1.Done' AFTER `currency_rate`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `sales_order_status` INT NOT NULL COMMENT '0.pending 1.done' AFTER `gst_type`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_inquiry_trn` ADD `product_conv_qty` VARCHAR(100) NOT NULL AFTER `unitid`, ADD `conv_unit_id` INT NOT NULL AFTER `product_conv_qty`, ADD `rate_unit` INT NOT NULL AFTER `conv_unit_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `product_conv_qty` VARCHAR(100) NOT NULL AFTER `unitid`, ADD `conv_unit_id` INT NOT NULL AFTER `product_conv_qty`, ADD `rate_unit` INT NOT NULL AFTER `conv_unit_id`");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_quotation_delivery_date` (
    `quo_delivery_date_id` int(11) NOT NULL AUTO_INCREMENT,
    `quot_trn_id` int(11) NOT NULL,
    `delivery_date` int(11) NOT NULL,
    `product_qty` int(11) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `cdate` int(11) NOT NULL,
    `mdate` int(11) NOT NULL,
    `po_delivery_date_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`quo_delivery_date_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_delivery_date` CHANGE `cdate` `cdate` TIMESTAMP NOT NULL');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_delivery_date` CHANGE `po_delivery_date_status` `quo_delivery_date_status` INT(11) NOT NULL');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_delivery_date` CHANGE `mdate` `mdate` TIMESTAMP NOT NULL');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_delivery_date` CHANGE `delivery_date` `delivery_date` DATE NOT NULL');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_delivery_date` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_trn` ADD `unit_wise` INT NOT NULL AFTER `rate_unit`');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation` ADD `delivery_type` VARCHAR(100) NOT NULL AFTER `inquiry_id`');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation` ADD `delivery_date` DATE NOT NULL AFTER `delivery_type`');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_sales_ordertrn` ADD `unit_wise` INT NOT NULL AFTER `conv_unit_id`');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation` ADD `orange` VARCHAR(200) NOT NULL AFTER `sales_order_status`, ADD `mfg` VARCHAR(200) NOT NULL AFTER `orange`, ADD `trading` VARCHAR(200) NOT NULL AFTER `mfg`, ADD `repairing` VARCHAR(200) NOT NULL AFTER `trading`, ADD `other` VARCHAR(200) NOT NULL AFTER `repairing`');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_sales_order` ADD `orange` VARCHAR(300) NOT NULL, ADD `mfg` VARCHAR(300) NOT NULL AFTER `orange`, ADD `trading` VARCHAR(300) NOT NULL AFTER `mfg`, ADD `repairing` VARCHAR(300) NOT NULL AFTER `trading`, ADD `other` VARCHAR(300) NOT NULL AFTER `repairing`');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_invoice` ADD `orange` VARCHAR(300) NOT NULL AFTER `dispatch_status`, ADD `mfg` VARCHAR(300) NOT NULL AFTER `orange`, ADD `trading` VARCHAR(300) NOT NULL AFTER `mfg`, ADD `repairing` VARCHAR(300) NOT NULL AFTER `trading`, ADD `other` VARCHAR(300) NOT NULL AFTER `repairing`');

  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('multiple_quot_to_so',0,'$date')");
}



//sanat db changes start 15-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='reserve_stock_parent_id_addigm'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_reserve_stock` ADD `perent_id` INT(11) NOT NULL");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('reserve_stock_parent_id_addigm',0,'$date')");
  //common branch update in db log table end
}
//sanat db changes stop 15-02-2023


//Maulik db changes start 15-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='ledger_wise_payment_terms'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_terms_condition` ADD `print_name` VARCHAR(255) NOT NULL AFTER `tc_name`");


  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_customer_term_trn` (
      `customer_term_id` int(11) NOT NULL AUTO_INCREMENT,
      `cust_id` int(11) NOT NULL,
      `ledger_id` int(11) NOT NULL,
      `tc_id` int(11) NOT NULL,
      `tc_priority` int(11) NOT NULL,
      `tc_details` longtext NOT NULL,
      `customer_terms_trn_status` int(11) NOT NULL,
      `cdate` timestamp NOT NULL,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      `branch_id` int(11) NOT NULL,
      `tc_for` int(11) NOT NULL COMMENT '0.Domestic 1.Export',
      PRIMARY KEY (`customer_term_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `terms_type` INT NOT NULL COMMENT '0.Common Term 1.Party Wise' AFTER `other`");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_salesorder_multiple_quot` (
      `so_multi_quot_id` int(11) NOT NULL AUTO_INCREMENT,
      `sales_order_id` int(11) NOT NULL,
      `quotation_id` int(11) NOT NULL,
      `so_multi_quot_status` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      PRIMARY KEY (`so_multi_quot_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_salesorder_multiple_quot` ADD `cdate` TIMESTAMP NOT NULL AFTER `company_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `terms_type` INT NOT NULL COMMENT '0.common terms 1.party wise 2.quotation wise' AFTER `other`, ADD `term_quotation_id` INT NOT NULL AFTER `terms_type`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `terms_type` INT NOT NULL COMMENT '0.common term 1.party term 2.salesorder term' AFTER `other`, ADD `term_salesorder_id` INT NOT NULL AFTER `terms_type`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `orange` VARCHAR(200) NOT NULL , ADD `mfg` VARCHAR(200) NOT NULL AFTER `orange`, ADD `trading` VARCHAR(200) NOT NULL AFTER `mfg`, ADD `repairing` VARCHAR(200) NOT NULL AFTER `trading`, ADD `other` VARCHAR(200) NOT NULL AFTER `repairing`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `orange` VARCHAR(200) NOT NULL , ADD `mfg` VARCHAR(200) NOT NULL AFTER `orange`, ADD `trading` VARCHAR(200) NOT NULL AFTER `mfg`, ADD `repairing` VARCHAR(200) NOT NULL AFTER `trading`, ADD `other` VARCHAR(200) NOT NULL AFTER `repairing`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_invoicetrn` ADD `orange` VARCHAR(200) NOT NULL , ADD `mfg` VARCHAR(200) NOT NULL AFTER `orange`, ADD `trading` VARCHAR(200) NOT NULL AFTER `mfg`, ADD `repairing` VARCHAR(200) NOT NULL AFTER `trading`, ADD `other` VARCHAR(200) NOT NULL AFTER `repairing`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('ledger_wise_payment_terms',0,'$date')");
  //common branch update in db log table end
}
//Maulik db changes stop 15-02-2023

//SANAT db changes start 16-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='jobwork_reprocess_qry_add'";



$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work` ADD `is_reprocess` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_trn` ADD `is_reprocess` INT(11) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_job_work_sub_trn` ADD `is_reprocess` INT(11) NOT NULL");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('jobwork_reprocess_qry_add',0,'$date')");
  //common branch update in db log table end
}
//SANAT db changes stop 16-02-2023

//Maulik db changes start 23-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='reciclar_changes'";



$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `reciclar` INT NOT NULL AFTER `libra_engineering_permission`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `product_mst` ADD `parent_category` INT NOT NULL AFTER `product_category`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_indiamart_data` ADD `cust_owner` INT NOT NULL AFTER `user_ids`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_inquiry_trn` ADD `rcat_id` INT NOT NULL AFTER `cat_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `rcat_id` INT NOT NULL AFTER `other`");


  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_category_reciclare` (
        `rcat_id` int(11) NOT NULL AUTO_INCREMENT,
        `cat_name` varchar(250) NOT NULL,
        `cat_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        PRIMARY KEY (`rcat_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `rcat_id` INT NOT NULL AFTER `product_category_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_indiamart_data` ADD `cat_id` INT NOT NULL AFTER `inquiry_id`, ADD `parent_cat_id` INT NOT NULL AFTER `cat_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `product_currency_rate` `product_currency_rate` DOUBLE(10,2) NOT NULL");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `product_currency_amount` `product_currency_amount` DOUBLE(10,2) NOT NULL");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `product_currency_amount_tax` `product_currency_amount_tax` DOUBLE(10,2) NOT NULL");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `inventory_party_show` VARCHAR(250) NOT NULL AFTER `purchase_party_show`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `inventory_pro_type` VARCHAR(250) NOT NULL AFTER `production_pro_type`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `packing_module` INT NOT NULL AFTER `followup_inquiry_show`, ADD `direct_sales_allocate` INT NOT NULL AFTER `packing_module`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `task_id` INT NOT NULL AFTER `terms_type`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_task` ADD `quotation_id` INT NOT NULL AFTER `inquiry_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `quotation_task_id` INT NOT NULL AFTER `task_id`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('reciclar_changes',0,'$date')");
  //common branch update in db log table end
}
//Maulik db Changes End 




//Sanat db changes start 28-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='process_stock_deallocation_change'";



$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `mrp_process_unreserved_log` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
    `rp_id` int(11) NOT NULL,
    `process_id` int(11) NOT NULL,
    `qty` varchar(111) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `cdate` date NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) DEFAULT '0',
    `status` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`log_id`),
    KEY `branch_id` (`branch_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('process_stock_deallocation_change',0,'$date')");
  //common branch update in db log table end
}
//Sanat db Changes End 



//Sanat db changes start 28-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='mrp_item_wise_add_remark'";



$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `product_remark` TEXT NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `remark` TEXT NOT NULL");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('mrp_item_wise_add_remark',0,'$date')");
  //common branch update in db log table end
}
//Sanat db Changes End 


//Sanat db changes start 28-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='production_customer_show_permission'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `customer_show_in_production` INT(11) NOT NULL DEFAULT '0' COMMENT '0 - no , 1 - yes'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('production_customer_show_permission',0,'$date')");
  //common branch update in db log table end
}
//Sanat db Changes End 

//pathik db changes start 03-03-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='ip_wise_login'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` ADD `wip_po_stock` VARCHAR(110) NOT NULL AFTER `igst_tax_rate_conv`, ADD `wip_po_stock_conv` VARCHAR(110) NOT NULL AFTER `wip_po_stock`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` ADD `wip_po_used_stock` VARCHAR(110) NOT NULL AFTER `wip_po_stock_conv`, ADD `wip_po_used_stock_conv` VARCHAR(110) NOT NULL AFTER `wip_po_used_stock`");
  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `wip_purchase_stock_allocate` (
    `purchase_allocate_id` int(11) NOT NULL AUTO_INCREMENT,
    `rp_id` int(11) NOT NULL,
    `allocate_base_qty` varchar(110) NOT NULL,
    `allocate_base_used_qty` varchar(110) NOT NULL,
    `allocate_base_unit` int(11) NOT NULL,
    `allocate_conv_qty` varchar(110) NOT NULL,
    `allocate_conv_used_qty` varchar(110) NOT NULL,
    `allocate_conv_unit` int(11) NOT NULL,
    `purchaseordertrn_id` int(11) NOT NULL,
    `purchase_allocate_status` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(10) NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`purchase_allocate_id`),
    KEY `purchaseordertrn_req__id` (`purchase_allocate_id`,`purchase_allocate_status`,`user_id`,`company_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `users` ADD `ip_add` VARCHAR(110) NOT NULL AFTER `device_id`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger` ADD `ip_add` VARCHAR(110) NOT NULL AFTER `cust_owner`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `ip_add_login` INT NOT NULL AFTER `smpl_mfg_licence`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('ip_wise_login',0,'$date')");
  //common branch update in db log table end
}
//pathik db Changes End 03-03-2023




//Sanat db changes start 28-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='next_process_stock_reserve_siiue'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE tbl_process_stock_trn ADD used_convert_stock VARCHAR(50) NOT NULL AFTER used_base_stock");
  $query_invoicetypes = $dbcon->query("ALTER TABLE tbl_process_stock_trn ADD mfg_date VARCHAR(30) NOT NULL , ADD exp_date VARCHAR(30) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE tbl_process_reserve_stock ADD used_conv_stock VARCHAR(50) NOT NULL AFTER used_base_stock");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('next_process_stock_reserve_siiue',0,'$date')");
  //common branch update in db log table end
}
//Sanat db Changes End 



//Sanat db changes start 06-03-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='material_issue_printset'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("INSERT INTO `print_type_mst` (`id`, `print_type_name`, `print_status`, `cdate`, `user_id`, `company_id`, `branch_id`) VALUES (NULL, 'Material Issue Print', '0', '2022-05-26 17:02:09', '1', '1', '1')");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('material_issue_printset',0,'$date')");
}

//Sanat db changes start 28-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='libra_workorder_print_sety'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_libra_workorder_fields` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `workorder_id` int(11) NOT NULL,
    `po_item_sr` varchar(10) NOT NULL,
    `po_item` varchar(255) NOT NULL,
    `datasheet` varchar(100) NOT NULL,
    `gad` varchar(100) NOT NULL,
    `qap` varchar(100) NOT NULL,
    `valve_type` varchar(100) NOT NULL,
    `size_class` varchar(100) NOT NULL,
    `qsl` varchar(100) NOT NULL,
    `qty` varchar(100) NOT NULL,
    `valve_sr` varchar(100) NOT NULL,
    `moc` varchar(100) NOT NULL,
    `service` varchar(50) NOT NULL,
    `design_standard` varchar(255) NOT NULL,
    `testing_standard` varchar(100) NOT NULL,
    `mfg_req` varchar(30) NOT NULL,
    `test_req` varchar(30) NOT NULL,
    `tpi_scope` varchar(50) NOT NULL,
    `sales_service_req` varchar(50) NOT NULL,
    `coating_painting_req` text NOT NULL,
    `packing_req` varchar(100) NOT NULL,
    `marking_on_product` varchar(100) NOT NULL,
    `marking_on_packing` varchar(100) NOT NULL,
    `api_monogram_marking` varchar(100) NOT NULL,
    `del_dua_date` date NOT NULL,
    `customer_cont_details` varchar(50) NOT NULL,
    `del_location` varchar(100) NOT NULL,
    `documents` varchar(255) NOT NULL,
    `payment_terms` varchar(20) NOT NULL,
    `insurance` varchar(20) NOT NULL,
    `freight` varchar(20) NOT NULL,
    `additional_req` varchar(100) NOT NULL,
    `prepared_by` varchar(50) NOT NULL,
    `approved_by` varchar(50) NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('libra_workorder_print_sety',0,'$date')");

  //common branch update in db log table end
}
//Sanat db Changes End 



//Sanat db changes start 28-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='saleorder_deallocation_stock_print'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("INSERT INTO `print_type_mst` (`id`, `print_type_name`, `print_status`, `cdate`, `user_id`, `company_id`, `branch_id`) VALUES (NULL, 'Sales Order Deallocation', '0', '2021-10-04 17:20:10', '1', '1', '1')");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('saleorder_deallocation_stock_print',0,'$date')");

  //common branch update in db log table end
}
//Sanat db Changes End 





//Sanat db changes start 28-02-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='print_type_master_set'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("TRUNCATE TABLE `print_type_mst`");
  $query_invoicetypes = $dbcon->query("INSERT INTO `print_type_mst` (`id`, `print_type_name`, `print_status`, `cdate`, `user_id`, `company_id`, `branch_id`) VALUES
  (1, 'Quotation', 0, '2021-10-04 11:49:42', 1, 1, 1),
  (2, 'Proforma Invoice', 0, '2021-10-04 11:49:42', 1, 1, 1),
  (3, 'Sales Order', 0, '2021-10-04 11:50:10', 1, 1, 1),
  (4, 'Purchase Order', 0, '2021-10-04 11:50:43', 1, 1, 1),
  (5, 'GRN', 0, '2021-10-04 11:50:33', 1, 1, 1),
  (6, 'Invoice Challan', 0, '2021-10-05 05:27:10', 1, 1, 1),
  (7, 'Invoice Receipt', 0, '2021-10-05 05:27:02', 1, 1, 1),
  (8, 'BOM', 0, '2021-10-04 11:51:12', 1, 1, 1),
  (9, 'Order Acceptance', 0, '2021-10-04 11:51:34', 1, 1, 1),
  (10, 'Job Work', 0, '2021-10-04 11:51:34', 1, 1, 1),
  (11, 'Work Order', 0, '2021-10-04 11:51:34', 1, 1, 1),
  (12, 'Delivery Challan Print', 0, '2021-10-04 11:51:34', 1, 1, 1),
  (13, 'Compliance Certificate Print', 0, '2021-10-04 11:51:34', 1, 1, 1),
  (14, 'Returnable Challan', 0, '2021-12-14 14:06:15', 1, 1, 1),
  (15, 'Purchase Bill', 0, '2022-02-03 04:54:22', 1, 1, 1),
  (16, 'jobwork rate card print', 0, '2022-02-22 05:08:27', 1, 1, 1),
  (17, 'Manual Indent', 0, '2022-03-02 08:53:02', 1, 1, 1),
  (18, 'Purchase Bill', 0, '2022-04-11 09:43:03', 1, 1, 1),
  (20, 'Forecast Print', 0, '2022-05-26 11:32:09', 1, 1, 1),
  (21, 'Godown Stock Transfer', 0, '2022-05-26 11:32:09', 1, 1, 1),
  (22, 'Stock General Print', 0, '2023-03-09 12:32:31', 1, 1, 1),
  (23, 'RM REQUISITION CUM ISSUE SLIP', 0, '2023-03-09 12:32:33', 1, 1, 1),
  (24, 'Inspaction & Test Report', 0, '2023-03-09 12:32:46', 1, 1, 1),
  (25, 'first inspaction report', 0, '2023-03-09 12:32:59', 1, 1, 1),
  (26, 'Material Issue Print', 0, '2022-05-26 11:32:09', 1, 1, 1),
  (27, 'Sales Order Deallocation', 0, '2021-10-04 11:50:10', 1, 1, 1)");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('print_type_master_set',0,'$date')");

  //common branch update in db log table end
}
//Sanat db Changes End 




//Sanat db changes start 21-03-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='workorder_reserve_both_unit_save'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `reserve_base_stock` VARCHAR(100) NOT NULL AFTER `reserve_stock`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('workorder_reserve_both_unit_save',0,'$date')");

  //common branch update in db log table end
}
//Sanat db Changes End 

//pathik db changes start 24-03-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='grn_purchase_id'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_batch_data` ADD `purchaseordertrn_id` INT NOT NULL AFTER `qc_sample_qty`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('grn_purchase_id',0,'$date')");

  //common branch update in db log table end
}
//pathik db Changes End 

//Maulik db changes start 31-03-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='quotation_term_customise'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_terms_trn` ADD `ref_tc_id` INT NOT NULL AFTER `tc_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_salesorder_terms_trn` ADD `ref_tc_id` INT NOT NULL AFTER `tc_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_invoice_terms_trn` ADD `ref_tc_id` INT NOT NULL AFTER `tc_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `terms_type` INT NOT NULL COMMENT '0.Common terms 1.Party Wise 2.Multi Condition' AFTER `quot_type`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseorder_terms_trn` ADD `ref_tc_id` INT NOT NULL AFTER `tc_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `terms_type` INT NOT NULL COMMENT '0.Common Terms 1.Party Terms 2.Ledger Terms 3. Quotation Terms 4.Sales Order Terms 5.Multi Condition' AFTER `lr_rr_no`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_terms_trn` ADD `ref_tc_id` INT NOT NULL AFTER `tc_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_returnable_channal_item` CHANGE `approve_status` `approve_status` SMALLINT(1) NOT NULL DEFAULT '0' COMMENT '0.Pending 1.Approved 2.Disapproved'");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_returnablechallan_aprv_log` (
    `returnable_aprv_id` int(11) NOT NULL AUTO_INCREMENT,
    `returnablechallan_item_id` int(11) NOT NULL,
    `approve_remark` text NOT NULL,
    `approve_status` int(11) NOT NULL COMMENT '0.Pending 1.Approve 2.Disapprove',
    `is_delete` int(11) NOT NULL,
    `reserve_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`returnable_aprv_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `approve_indent` ADD `approve_base_unit` INT NOT NULL AFTER `approve_unit`, ADD `approve_base_qty` VARCHAR(200) NOT NULL AFTER `approve_base_unit`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchasetrntemp` ADD `product_base_qty` VARCHAR(200) NOT NULL AFTER `product_qty`, ADD `base_unit_id` INT NOT NULL AFTER `product_base_qty`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseorder_req_trn` CHANGE `used_conv_qty` `used_base_qty` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `meru_permission` INT NOT NULL AFTER `reciclar`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `inquiry_ref_date` DATE NOT NULL AFTER `quotation_date`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_pre_trn` ADD `product_desc` TEXT NOT NULL AFTER `product_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_inq_attach` ADD `task_id` INT NOT NULL AFTER `inquiry_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseorder_req_trn` ADD `conv_unit` INT NOT NULL AFTER `base_unit`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `basic_total_conv` DOUBLE(10,2) NOT NULL AFTER `tc_format`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('quotation_term_customise',0,'$date')");

  //common branch update in db log table end
}
//Maulik db Changes End

//pathik db changes start 31-03-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='financeyearchange'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_financial_year` ADD `perent_id` INT NOT NULL AFTER `usertype_id`");

  $query_invoicetypes = $dbcon->query("update tbl_financial_year set `isdelete`=2 where `current_status`!=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('financeyearchange',0,'$date')");

  //common branch update in db log table end
}
//pathik db Changes End




//Sanat db changes start 06-04-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='salesorder_priority_set_for_production'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `priority_status` ENUM('Low','Medium','High') NOT NULL DEFAULT 'Low'");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_set_main_process` ADD `priority_status` ENUM('Low','Medium','High') NOT NULL DEFAULT 'Low'");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_request_product` ADD `priority_status` ENUM('Low','Medium','High') NOT NULL DEFAULT 'Low'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('salesorder_priority_set_for_production',0,'$date')");

  //common branch update in db log table end
}
//Sanat db Changes End 


//Sanat db changes start 12-04-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='process_delete_validation_chk'";


$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_product_process` ADD `status` INT(11) NOT NULL");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('process_delete_validation_chk',0,'$date')");

  //common branch update in db log table end
}
//Sanat db Changes End 


//Sanat db changes start 28-04-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='reserve_stock_add_use_stock'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_reserve_stock` ADD `used_base_stock` VARCHAR(100) NOT NULL AFTER `approve_base_stock`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_reserve_stock` ADD `used_convert_stock` VARCHAR(100) NOT NULL AFTER `approve_convert_stock`");
  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('reserve_stock_add_use_stock',0,'$date')");

  //common branch update in db log table end
}
//Sanat db Changes End 



//Sanat db changes start 02-08-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='misssing_table_create_qryy'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("CREATE TABLE `po_quotation_ref` (
    `quotation_ref_id` int(11) NOT NULL,
    `ref_quotation_no` varchar(200) NOT NULL,
    `ref_quotation_date` date NOT NULL,
    `vender_id` varchar(200) NOT NULL,
    `comparision` int(11) NOT NULL,
    `ref_quotation_status` int(11) NOT NULL,
    `approve_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

  $query_invoicetypes = $dbcon->query("CREATE TABLE `po_quotationtrn_ref` (
    `po_quotationtrn_id` int(11) NOT NULL,
    `approve_indent_id` int(11) NOT NULL,
    `supplier_detail_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `product_qty` varchar(200) NOT NULL,
    `product_conv_qty` varchar(200) NOT NULL,
    `unit_id` int(11) NOT NULL,
    `conv_unit_id` int(11) NOT NULL,
    `product_rate` double(10,2) NOT NULL,
    `delivery_date` date NOT NULL,
    `payment_days` varchar(200) NOT NULL,
    `remark` text NOT NULL,
    `ref_name` varchar(200) NOT NULL,
    `ref_id` int(11) NOT NULL,
    `po_quotationtrn_status` int(11) NOT NULL,
    `quotation_copm` int(11) NOT NULL COMMENT '0.no 1.yes',
    `quotation_copm_aprv` int(11) NOT NULL COMMENT '0.no 1.yes',
    `vender_id` int(11) NOT NULL,
    `parent_req_id` int(11) NOT NULL,
    `quotation_no` varchar(200) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('misssing_table_create_qryy',0,'$date')");

  //common branch update in db log table end
}
//Sanat db Changes End 




//Maulik Db Update Query 
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='setting_wise_cat_selection'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_invoicetrn` ADD `product_conv_qty` DOUBLE(15,3) NOT NULL AFTER `product_qty`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_invoicetrn` ADD `conv_unit_id` INT NOT NULL AFTER `unit_id`, ADD `rate_unit` INT NOT NULL AFTER `conv_unit_id`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `category_selection_active` INT NOT NULL AFTER `direct_sales_allocate`, ADD `cat_wise_product_load` INT NOT NULL AFTER `category_selection_active`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_trn` ADD `product_conv_qty` INT NOT NULL AFTER `product_qty`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_trn` ADD `conv_unit_id` INT NOT NULL AFTER `unit_id`, ADD `rate_unit` INT NOT NULL AFTER `conv_unit_id`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_trn` ADD `cat_id` INT NOT NULL AFTER `product_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_debitnote_trn` ADD `conv_unit_id` INT NOT NULL AFTER `unit_id`, ADD `rate_unit` INT NOT NULL AFTER `conv_unit_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_debitnote_trn` CHANGE `product_qty` `product_qty` VARCHAR(100) NOT NULL");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_debitnote_trn` ADD `product_conv_qty` VARCHAR(100) NOT NULL AFTER `product_qty`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `sfg_date` DATE NOT NULL AFTER `po_date`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `sfg_date` DATE NOT NULL AFTER `po_date`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `sfg_date` DATE NOT NULL AFTER `po_date`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `po_quotationtrn_ref` ADD `quotation_no` VARCHAR(200) NOT NULL AFTER `vender_id`");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_supplier_quotation_detail` (
      `supplier_detail_id` int(11) NOT NULL AUTO_INCREMENT,
      `quotation_ref_id` int(11) NOT NULL,
      `vender_id` int(11) NOT NULL,
      `quotation_no` varchar(200) NOT NULL,
      `quotation_date` date NOT NULL,
      `delivery_date` date NOT NULL,
      `payment_days` varchar(200) NOT NULL,
      `supplier_status` int(11) NOT NULL,
      `cdate` timestamp NOT NULL,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      PRIMARY KEY (`supplier_detail_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `po_quotationtrn_ref` ADD `parent_req_id` INT NOT NULL AFTER `vender_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `po_quotationtrn_ref` ADD `product_rate` DOUBLE(10,2) NOT NULL AFTER `conv_unit_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `po_quotationtrn_ref` ADD `quotation_copm` INT NOT NULL COMMENT '0.no 1.yes' AFTER `po_quotationtrn_status`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `po_quotationtrn_ref` ADD `supplier_detail_id` INT NOT NULL AFTER `approve_indent_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `po_quotationtrn_ref` ADD `delivery_date` DATE NOT NULL AFTER `product_rate`, ADD `payment_days` VARCHAR(200) NOT NULL AFTER `delivery_date`");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_multiple_product_edit_flag_temp1` (
      `edit_flag_id` int(11) NOT NULL AUTO_INCREMENT,
      `quotation_ref_id` int(11) NOT NULL,
      `po_quotationtrn_id` int(11) NOT NULL,
      `ref_name` varchar(200) NOT NULL,
      `cdate` timestamp NOT NULL,
      `user_id` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      PRIMARY KEY (`edit_flag_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `po_quotation_ref` ADD `comparision` INT NOT NULL AFTER `vender_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `po_quotationtrn_ref` ADD `quotation_copm_aprv` INT NOT NULL COMMENT '0.no 1.yes' AFTER `quotation_copm`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `approve_indent` ADD `used_document` INT NOT NULL COMMENT '0.no 1.yes' AFTER `quotation_requirement`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `approve_indent` ADD `quotation_ref_id` INT NOT NULL AFTER `used_document`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `multiple_dispatch` VARCHAR(200) NOT NULL AFTER `term_quotation_id`, ADD `priority` INT NOT NULL AFTER `multiple_dispatch`, ADD `prev_priority` INT NOT NULL AFTER `priority`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `po_quotation_ref` ADD `approve_status` INT NOT NULL AFTER `ref_quotation_status`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_supplier_quotation_detail` ADD `quotation_copm_aprv` INT NOT NULL AFTER `supplier_status`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `po_quotation` ADD `po_quotationtrn_id` INT NOT NULL AFTER `po_quotation_status`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_trn` ADD `product_conv_qty` VARCHAR(100) NOT NULL AFTER `product_qty`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_trn` ADD `conv_unit_id` INT NOT NULL AFTER `unit_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_trn` ADD `rate_unit` INT NOT NULL AFTER `conv_unit_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_trn` ADD `cat_id` INT NOT NULL AFTER `product_id`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_returnable_batch_stock_tmp` ADD `godown_id` INT NOT NULL AFTER `batch_no`");
  //$query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('setting_wise_cat_selection',0,'$date')");
}


//Sanat db changes start 16-03-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dashboard_counter_cron_set'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_allocate_process` ADD `cron_status` INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_store_release` ADD `cron_status` INT(11) NOT NULL");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('dashboard_counter_cron_set',0,'$date')");
  //common branch update in db log table end
}

//Sanat db Changes End 

//pathik db changes start 02-04-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='direct_so_stock_allocate'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `so_temp_auto_allocate` INT NOT NULL AFTER `customer_show_in_production`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_reserve_stock` ADD `temp_stock_allocate` INT NOT NULL COMMENT '0: no,1:yes' AFTER `perent_id`");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('direct_so_stock_allocate',0,'$date')");
  //common branch update in db log table end
}

//pathik db Changes End 02-04-2023

//Sanat db changes start 17-05-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='batch_wise_dedudct_material_start_time'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_batch_temp_material_start_time_deduct` (
    `tmp_id` int(11) NOT NULL AUTO_INCREMENT,
    `mt_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `rp_id` int(11) NOT NULL,
    `p_id` int(11) NOT NULL,
    `reserve_id` int(11) NOT NULL,
    `batch_no` varchar(100) NOT NULL,
    `type` INT(11) NOT NULL COMMENT '1-start time ,2-end time',
    `deduct_qty` varchar(100) NOT NULL,
    `base_unit` varchar(100) NOT NULL,
    `deduct_conv_qty` varchar(100) NOT NULL,
    `conv_unit` varchar(100) NOT NULL,
    `is_process_stock` int(11) NOT NULL,
    `status` int(11) NOT NULL,
    `cdate` datetime NOT NULL,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    PRIMARY KEY (`tmp_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('batch_wise_dedudct_material_start_time',0,'$date')");
  //common branch update in db log table end
}

//Sanat db Changes End 



//Sanat db changes start 23-05-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='printing_field_add_for_solidege'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE product_mst ADD printing_material INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE product_mst ADD printing_balty INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE product_mst ADD printing_req INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE product_mst ADD extrusion_material INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE product_mst ADD extrusion_size INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE product_mst ADD mixing_batch_size INT(11) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('printing_field_add_for_solidege',0,'$date')");
  //common branch update in db log table end
}

//Sanat db Changes End 




//Sanat db changes start 23-05-2023
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='smpl_extra_stock_etnry_foelds_changes'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_store_request` CHANGE `rp_id` `product_id` INT(11) NOT NULL COMMENT 'product_id'");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_store_request` ADD `rp_id` INT(11) NOT NULL AFTER `p_id`");


  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('smpl_extra_stock_etnry_foelds_changes',0,'$date')");
  //common branch update in db log table end
}

//Sanat db Changes End 


///////////Harshil/////////////////////

$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='Austor_special_setting'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `austar_permission` INT NOT NULL AFTER `meru_permission`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('Austor_special_setting',0,'$date')");
}
//////////////////////////Harshil////////////////////


///////////SANAT/////////////////////

$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='on_floor_godown_entry'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `mst_godown` (`gd_id`, `p_gd_id`, `gd_name`, `gd_address`, `g_status`, `cdate`, `user_id`, `company_id`, `branch_id`, `show_in_list`) VALUES ('-111', '0', 'ON FLOOR GODOWN', '', '0', '2021-09-21 19:00:06', '1', '1', '1', '1')");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('on_floor_godown_entry',0,'$date')");
}
//////////////////////////SANAT////////////////////



///////////SANAT/////////////////////  05-06-2023

$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='workorder_to_workorder_stock_trnsfr'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'WORKORDER STOCK TRANSFER', '0', '0', '60', '3', 'WO/STK/TRANSFER/', '/23-24', '1', '0', '2023-04-01 00:00:00', '1', '2', '1', '1', '', '1')");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_workorder_transfer` (
    `wo_stk_transfer_id` int(11) NOT NULL AUTO_INCREMENT,
    `wo_stk_transfer_no` varchar(50) NOT NULL,
    `wo_stk_transfer_date` date NOT NULL,
    `status` int(11) NOT NULL,
    `approved_status` int(11) NOT NULL,
    `remark` text NOT NULL,
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`wo_stk_transfer_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_workorder_transfer_trn` (
    `wo_stk_trn_id` int(11) NOT NULL AUTO_INCREMENT,
    `wo_stk_transfer_id` int(11) NOT NULL,
    `from_workorder_id` int(11) NOT NULL,
    `to_workorder_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `from_rp_id` int(11) NOT NULL,
    `to_rp_id` int(11) NOT NULL,
    `qty` varchar(50) NOT NULL,
    `unit_id` INT(11) NOT NULL,
    `status` int(11) NOT NULL,
    `return_qty` VARCHAR(100) NOT NULL ,
    `return_complete_status` INT(11) NOT NULL DEFAULT '0',
    `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `company_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`wo_stk_trn_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_workorder_transfer_aprv_log` (
    `workorder_aprv_log_id` int(11) NOT NULL AUTO_INCREMENT,
    `wo_stk_transfer_id` int(11) NOT NULL,
    `approve_remark` mediumtext NOT NULL,
    `approve_status` int(11) NOT NULL,
    `workorder_aprv_log_status` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    PRIMARY KEY (`workorder_aprv_log_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('workorder_to_workorder_stock_trnsfr',0,'$date')");
}
//////////////////////////SANAT//////////////////// 

/////////////////////////Maulik///////////////////
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='isd_code_data'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_cust_contact` ADD `isd_id` INT NOT NULL AFTER `c_con_email`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_cust_contact_person` ADD `isd_id` INT NOT NULL AFTER `cust_contact_person_name`");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `isd_code_mst` (
      `isd_id` int(11) NOT NULL AUTO_INCREMENT,
      `iso` varchar(100) NOT NULL,
      `country_name` varchar(100) NOT NULL,
      `nicename` varchar(100) NOT NULL,
      `iso3` varchar(100) NOT NULL,
      `numcode` int(11) NOT NULL,
      `phonecode` int(11) NOT NULL,
      `isd_status` int(11) NOT NULL,
      `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `userid` int(11) NOT NULL,
      PRIMARY KEY (`isd_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("INSERT INTO `isd_code_mst` (`isd_id`, `iso`, `country_name`, `nicename`, `iso3`, `numcode`, `phonecode`, `isd_status`, `cdate`, `userid`) VALUES
    (1, 'AF', 'AFGHANISTAN', 'Afghanistan', 'AFG', 4, 93, 0, '2023-07-04 09:00:50', 1),
    (2, 'AL', 'ALBANIA', 'Albania', 'ALB', 8, 355, 0, '2023-07-04 09:00:50', 1),
    (3, 'DZ', 'ALGERIA', 'Algeria', 'DZA', 12, 213, 0, '2023-07-04 09:00:50', 1),
    (4, 'AS', 'AMERICAN SAMOA', 'American Samoa', 'ASM', 16, 1684, 0, '2023-07-04 09:00:50', 1),
    (5, 'AD', 'ANDORRA', 'Andorra', 'AND', 20, 376, 0, '2023-07-04 09:00:50', 1),
    (6, 'AO', 'ANGOLA', 'Angola', 'AGO', 24, 244, 0, '2023-07-04 09:00:50', 1),
    (7, 'AI', 'ANGUILLA', 'Anguilla', 'AIA', 660, 1264, 0, '2023-07-04 09:00:50', 1),
    (8, 'AQ', 'ANTARCTICA', 'Antarctica', '', 0, 0, 0, '2023-07-04 09:00:50', 1),
    (9, 'AG', 'ANTIGUA AND BARBUDA', 'Antigua and Barbuda', 'ATG', 28, 1268, 0, '2023-07-04 09:00:50', 1),
    (10, 'AR', 'ARGENTINA', 'Argentina', 'ARG', 32, 54, 0, '2023-07-04 09:00:50', 1),
    (11, 'AM', 'ARMENIA', 'Armenia', 'ARM', 51, 374, 0, '2023-07-04 09:00:50', 1),
    (12, 'AW', 'ARUBA', 'Aruba', 'ABW', 533, 297, 0, '2023-07-04 09:00:50', 1),
    (13, 'AU', 'AUSTRALIA', 'Australia', 'AUS', 36, 61, 0, '2023-07-04 09:00:50', 1),
    (14, 'AT', 'AUSTRIA', 'Austria', 'AUT', 40, 43, 0, '2023-07-04 09:00:50', 1),
    (15, 'AZ', 'AZERBAIJAN', 'Azerbaijan', 'AZE', 31, 994, 0, '2023-07-04 09:00:50', 1),
    (16, 'BS', 'BAHAMAS', 'Bahamas', 'BHS', 44, 1242, 0, '2023-07-04 09:00:50', 1),
    (17, 'BH', 'BAHRAIN', 'Bahrain', 'BHR', 48, 973, 0, '2023-07-04 09:00:50', 1),
    (18, 'BD', 'BANGLADESH', 'Bangladesh', 'BGD', 50, 880, 0, '2023-07-04 09:00:50', 1),
    (19, 'BB', 'BARBADOS', 'Barbados', 'BRB', 52, 1246, 0, '2023-07-04 09:00:50', 1),
    (20, 'BY', 'BELARUS', 'Belarus', 'BLR', 112, 375, 0, '2023-07-04 09:00:50', 1),
    (21, 'BE', 'BELGIUM', 'Belgium', 'BEL', 56, 32, 0, '2023-07-04 09:00:50', 1),
    (22, 'BZ', 'BELIZE', 'Belize', 'BLZ', 84, 501, 0, '2023-07-04 09:00:50', 1),
    (23, 'BJ', 'BENIN', 'Benin', 'BEN', 204, 229, 0, '2023-07-04 09:00:50', 1),
    (24, 'BM', 'BERMUDA', 'Bermuda', 'BMU', 60, 1441, 0, '2023-07-04 09:00:50', 1),
    (25, 'BT', 'BHUTAN', 'Bhutan', 'BTN', 64, 975, 0, '2023-07-04 09:00:50', 1),
    (26, 'BO', 'BOLIVIA', 'Bolivia', 'BOL', 68, 591, 0, '2023-07-04 09:00:50', 1),
    (27, 'BA', 'BOSNIA AND HERZEGOVINA', 'Bosnia and Herzegovina', 'BIH', 70, 387, 0, '2023-07-04 09:00:50', 1),
    (28, 'BW', 'BOTSWANA', 'Botswana', 'BWA', 72, 267, 0, '2023-07-04 09:00:50', 1),
    (29, 'BV', 'BOUVET ISLAND', 'Bouvet Island', '', 0, 0, 0, '2023-07-04 09:00:50', 1),
    (30, 'BR', 'BRAZIL', 'Brazil', 'BRA', 76, 55, 0, '2023-07-04 09:00:50', 1),
    (31, 'IO', 'BRITISH INDIAN OCEAN TERRITORY', 'British Indian Ocean Territory', '', 0, 246, 0, '2023-07-04 09:00:50', 1),
    (32, 'BN', 'BRUNEI DARUSSALAM', 'Brunei Darussalam', 'BRN', 96, 673, 0, '2023-07-04 09:00:50', 1),
    (33, 'BG', 'BULGARIA', 'Bulgaria', 'BGR', 100, 359, 0, '2023-07-04 09:00:50', 1),
    (34, 'BF', 'BURKINA FASO', 'Burkina Faso', 'BFA', 854, 226, 0, '2023-07-04 09:00:50', 1),
    (35, 'BI', 'BURUNDI', 'Burundi', 'BDI', 108, 257, 0, '2023-07-04 09:00:50', 1),
    (36, 'KH', 'CAMBODIA', 'Cambodia', 'KHM', 116, 855, 0, '2023-07-04 09:00:50', 1),
    (37, 'CM', 'CAMEROON', 'Cameroon', 'CMR', 120, 237, 0, '2023-07-04 09:00:50', 1),
    (38, 'CA', 'CANADA', 'Canada', 'CAN', 124, 1, 0, '2023-07-04 09:00:50', 1),
    (39, 'CV', 'CAPE VERDE', 'Cape Verde', 'CPV', 132, 238, 0, '2023-07-04 09:00:50', 1),
    (40, 'KY', 'CAYMAN ISLANDS', 'Cayman Islands', 'CYM', 136, 1345, 0, '2023-07-04 09:00:50', 1),
    (41, 'CF', 'CENTRAL AFRICAN REPUBLIC', 'Central African Republic', 'CAF', 140, 236, 0, '2023-07-04 09:00:50', 1),
    (42, 'TD', 'CHAD', 'Chad', 'TCD', 148, 235, 0, '2023-07-04 09:00:50', 1),
    (43, 'CL', 'CHILE', 'Chile', 'CHL', 152, 56, 0, '2023-07-04 09:00:50', 1),
    (44, 'CN', 'CHINA', 'China', 'CHN', 156, 86, 0, '2023-07-04 09:00:50', 1),
    (45, 'CX', 'CHRISTMAS ISLAND', 'Christmas Island', '', 0, 61, 0, '2023-07-04 09:00:50', 1),
    (46, 'CC', 'COCOS (KEELING) ISLANDS', 'Cocos (Keeling) Islands', '', 0, 672, 0, '2023-07-04 09:00:50', 1),
    (47, 'CO', 'COLOMBIA', 'Colombia', 'COL', 170, 57, 0, '2023-07-04 09:00:50', 1),
    (48, 'KM', 'COMOROS', 'Comoros', 'COM', 174, 269, 0, '2023-07-04 09:00:50', 1),
    (49, 'CG', 'CONGO', 'Congo', 'COG', 178, 242, 0, '2023-07-04 09:00:50', 1),
    (50, 'CD', 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'Congo, the Democratic Republic of the', 'COD', 180, 242, 0, '2023-07-04 09:00:50', 1),
    (51, 'CK', 'COOK ISLANDS', 'Cook Islands', 'COK', 184, 682, 0, '2023-07-04 09:00:50', 1),
    (52, 'CR', 'COSTA RICA', 'Costa Rica', 'CRI', 188, 506, 0, '2023-07-04 09:00:50', 1),
    (53, 'CI', 'COTE D''IVOIRE', 'Cote D''Ivoire', 'CIV', 384, 225, 0, '2023-07-04 09:00:50', 1),
    (54, 'HR', 'CROATIA', 'Croatia', 'HRV', 191, 385, 0, '2023-07-04 09:00:50', 1),
    (55, 'CU', 'CUBA', 'Cuba', 'CUB', 192, 53, 0, '2023-07-04 09:00:50', 1),
    (56, 'CY', 'CYPRUS', 'Cyprus', 'CYP', 196, 357, 0, '2023-07-04 09:00:50', 1),
    (57, 'CZ', 'CZECH REPUBLIC', 'Czech Republic', 'CZE', 203, 420, 0, '2023-07-04 09:00:50', 1),
    (58, 'DK', 'DENMARK', 'Denmark', 'DNK', 208, 45, 0, '2023-07-04 09:00:50', 1),
    (59, 'DJ', 'DJIBOUTI', 'Djibouti', 'DJI', 262, 253, 0, '2023-07-04 09:00:50', 1),
    (60, 'DM', 'DOMINICA', 'Dominica', 'DMA', 212, 1767, 0, '2023-07-04 09:00:50', 1),
    (61, 'DO', 'DOMINICAN REPUBLIC', 'Dominican Republic', 'DOM', 214, 1809, 0, '2023-07-04 09:00:50', 1),
    (62, 'EC', 'ECUADOR', 'Ecuador', 'ECU', 218, 593, 0, '2023-07-04 09:00:50', 1),
    (63, 'EG', 'EGYPT', 'Egypt', 'EGY', 818, 20, 0, '2023-07-04 09:00:50', 1),
    (64, 'SV', 'EL SALVADOR', 'El Salvador', 'SLV', 222, 503, 0, '2023-07-04 09:00:50', 1),
    (65, 'GQ', 'EQUATORIAL GUINEA', 'Equatorial Guinea', 'GNQ', 226, 240, 0, '2023-07-04 09:00:50', 1),
    (66, 'ER', 'ERITREA', 'Eritrea', 'ERI', 232, 291, 0, '2023-07-04 09:00:50', 1),
    (67, 'EE', 'ESTONIA', 'Estonia', 'EST', 233, 372, 0, '2023-07-04 09:00:50', 1),
    (68, 'ET', 'ETHIOPIA', 'Ethiopia', 'ETH', 231, 251, 0, '2023-07-04 09:00:50', 1),
    (69, 'FK', 'FALKLAND ISLANDS (MALVINAS)', 'Falkland Islands (Malvinas)', 'FLK', 238, 500, 0, '2023-07-04 09:00:50', 1),
    (70, 'FO', 'FAROE ISLANDS', 'Faroe Islands', 'FRO', 234, 298, 0, '2023-07-04 09:00:50', 1),
    (71, 'FJ', 'FIJI', 'Fiji', 'FJI', 242, 679, 0, '2023-07-04 09:00:50', 1),
    (72, 'FI', 'FINLAND', 'Finland', 'FIN', 246, 358, 0, '2023-07-04 09:00:50', 1),
    (73, 'FR', 'FRANCE', 'France', 'FRA', 250, 33, 0, '2023-07-04 09:00:50', 1),
    (74, 'GF', 'FRENCH GUIANA', 'French Guiana', 'GUF', 254, 594, 0, '2023-07-04 09:00:50', 1),
    (75, 'PF', 'FRENCH POLYNESIA', 'French Polynesia', 'PYF', 258, 689, 0, '2023-07-04 09:00:50', 1),
    (76, 'TF', 'FRENCH SOUTHERN TERRITORIES', 'French Southern Territories', '', 0, 0, 0, '2023-07-04 09:00:50', 1),
    (77, 'GA', 'GABON', 'Gabon', 'GAB', 266, 241, 0, '2023-07-04 09:00:50', 1),
    (78, 'GM', 'GAMBIA', 'Gambia', 'GMB', 270, 220, 0, '2023-07-04 09:00:50', 1),
    (79, 'GE', 'GEORGIA', 'Georgia', 'GEO', 268, 995, 0, '2023-07-04 09:00:50', 1),
    (80, 'DE', 'GERMANY', 'Germany', 'DEU', 276, 49, 0, '2023-07-04 09:00:50', 1),
    (81, 'GH', 'GHANA', 'Ghana', 'GHA', 288, 233, 0, '2023-07-04 09:00:50', 1),
    (82, 'GI', 'GIBRALTAR', 'Gibraltar', 'GIB', 292, 350, 0, '2023-07-04 09:00:50', 1),
    (83, 'GR', 'GREECE', 'Greece', 'GRC', 300, 30, 0, '2023-07-04 09:00:50', 1),
    (84, 'GL', 'GREENLAND', 'Greenland', 'GRL', 304, 299, 0, '2023-07-04 09:00:50', 1),
    (85, 'GD', 'GRENADA', 'Grenada', 'GRD', 308, 1473, 0, '2023-07-04 09:00:50', 1),
    (86, 'GP', 'GUADELOUPE', 'Guadeloupe', 'GLP', 312, 590, 0, '2023-07-04 09:00:50', 1),
    (87, 'GU', 'GUAM', 'Guam', 'GUM', 316, 1671, 0, '2023-07-04 09:00:50', 1),
    (88, 'GT', 'GUATEMALA', 'Guatemala', 'GTM', 320, 502, 0, '2023-07-04 09:00:50', 1),
    (89, 'GN', 'GUINEA', 'Guinea', 'GIN', 324, 224, 0, '2023-07-04 09:00:50', 1),
    (90, 'GW', 'GUINEA-BISSAU', 'Guinea-Bissau', 'GNB', 624, 245, 0, '2023-07-04 09:00:50', 1),
    (91, 'GY', 'GUYANA', 'Guyana', 'GUY', 328, 592, 0, '2023-07-04 09:00:50', 1),
    (92, 'HT', 'HAITI', 'Haiti', 'HTI', 332, 509, 0, '2023-07-04 09:00:50', 1),
    (93, 'HM', 'HEARD ISLAND AND MCDONALD ISLANDS', 'Heard Island and Mcdonald Islands', '', 0, 0, 0, '2023-07-04 09:00:50', 1),
    (94, 'VA', 'HOLY SEE (VATICAN CITY STATE)', 'Holy See (Vatican City State)', 'VAT', 336, 39, 0, '2023-07-04 09:00:50', 1),
    (95, 'HN', 'HONDURAS', 'Honduras', 'HND', 340, 504, 0, '2023-07-04 09:00:50', 1),
    (96, 'HK', 'HONG KONG', 'Hong Kong', 'HKG', 344, 852, 0, '2023-07-04 09:00:50', 1),
    (97, 'HU', 'HUNGARY', 'Hungary', 'HUN', 348, 36, 0, '2023-07-04 09:00:50', 1),
    (98, 'IS', 'ICELAND', 'Iceland', 'ISL', 352, 354, 0, '2023-07-04 09:00:50', 1),
    (99, 'IN', 'INDIA', 'India', 'IND', 356, 91, 0, '2023-07-04 09:00:50', 1),
    (100, 'ID', 'INDONESIA', 'Indonesia', 'IDN', 360, 62, 0, '2023-07-04 09:00:50', 1),
    (101, 'IR', 'IRAN, ISLAMIC REPUBLIC OF', 'Iran, Islamic Republic of', 'IRN', 364, 98, 0, '2023-07-04 09:00:50', 1),
    (102, 'IQ', 'IRAQ', 'Iraq', 'IRQ', 368, 964, 0, '2023-07-04 09:00:50', 1),
    (103, 'IE', 'IRELAND', 'Ireland', 'IRL', 372, 353, 0, '2023-07-04 09:00:50', 1),
    (104, 'IL', 'ISRAEL', 'Israel', 'ISR', 376, 972, 0, '2023-07-04 09:00:50', 1),
    (105, 'IT', 'ITALY', 'Italy', 'ITA', 380, 39, 0, '2023-07-04 09:00:50', 1),
    (106, 'JM', 'JAMAICA', 'Jamaica', 'JAM', 388, 1876, 0, '2023-07-04 09:00:50', 1),
    (107, 'JP', 'JAPAN', 'Japan', 'JPN', 392, 81, 0, '2023-07-04 09:00:50', 1),
    (108, 'JO', 'JORDAN', 'Jordan', 'JOR', 400, 962, 0, '2023-07-04 09:00:50', 1),
    (109, 'KZ', 'KAZAKHSTAN', 'Kazakhstan', 'KAZ', 398, 7, 0, '2023-07-04 09:00:50', 1),
    (110, 'KE', 'KENYA', 'Kenya', 'KEN', 404, 254, 0, '2023-07-04 09:00:50', 1),
    (111, 'KI', 'KIRIBATI', 'Kiribati', 'KIR', 296, 686, 0, '2023-07-04 09:00:50', 1),
    (112, 'KP', 'KOREA, DEMOCRATIC PEOPLE''S REPUBLIC OF', 'Korea, Democratic People''s Republic of', 'PRK', 408, 850, 0, '2023-07-04 09:00:50', 1),
    (113, 'KR', 'KOREA, REPUBLIC OF', 'Korea, Republic of', 'KOR', 410, 82, 0, '2023-07-04 09:00:50', 1),
    (114, 'KW', 'KUWAIT', 'Kuwait', 'KWT', 414, 965, 0, '2023-07-04 09:00:50', 1),
    (115, 'KG', 'KYRGYZSTAN', 'Kyrgyzstan', 'KGZ', 417, 996, 0, '2023-07-04 09:00:50', 1),
    (116, 'LA', 'LAO PEOPLE''S DEMOCRATIC REPUBLIC', 'Lao People''s Democratic Republic', 'LAO', 418, 856, 0, '2023-07-04 09:00:50', 1),
    (117, 'LV', 'LATVIA', 'Latvia', 'LVA', 428, 371, 0, '2023-07-04 09:00:50', 1),
    (118, 'LB', 'LEBANON', 'Lebanon', 'LBN', 422, 961, 0, '2023-07-04 09:00:50', 1),
    (119, 'LS', 'LESOTHO', 'Lesotho', 'LSO', 426, 266, 0, '2023-07-04 09:00:50', 1),
    (120, 'LR', 'LIBERIA', 'Liberia', 'LBR', 430, 231, 0, '2023-07-04 09:00:50', 1),
    (121, 'LY', 'LIBYAN ARAB JAMAHIRIYA', 'Libyan Arab Jamahiriya', 'LBY', 434, 218, 0, '2023-07-04 09:00:50', 1),
    (122, 'LI', 'LIECHTENSTEIN', 'Liechtenstein', 'LIE', 438, 423, 0, '2023-07-04 09:00:50', 1),
    (123, 'LT', 'LITHUANIA', 'Lithuania', 'LTU', 440, 370, 0, '2023-07-04 09:00:50', 1),
    (124, 'LU', 'LUXEMBOURG', 'Luxembourg', 'LUX', 442, 352, 0, '2023-07-04 09:00:50', 1),
    (125, 'MO', 'MACAO', 'Macao', 'MAC', 446, 853, 0, '2023-07-04 09:00:50', 1),
    (126, 'MK', 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'Macedonia, the Former Yugoslav Republic of', 'MKD', 807, 389, 0, '2023-07-04 09:00:50', 1),
    (127, 'MG', 'MADAGASCAR', 'Madagascar', 'MDG', 450, 261, 0, '2023-07-04 09:00:50', 1),
    (128, 'MW', 'MALAWI', 'Malawi', 'MWI', 454, 265, 0, '2023-07-04 09:00:50', 1),
    (129, 'MY', 'MALAYSIA', 'Malaysia', 'MYS', 458, 60, 0, '2023-07-04 09:00:50', 1),
    (130, 'MV', 'MALDIVES', 'Maldives', 'MDV', 462, 960, 0, '2023-07-04 09:00:50', 1),
    (131, 'ML', 'MALI', 'Mali', 'MLI', 466, 223, 0, '2023-07-04 09:00:50', 1),
    (132, 'MT', 'MALTA', 'Malta', 'MLT', 470, 356, 0, '2023-07-04 09:00:50', 1),
    (133, 'MH', 'MARSHALL ISLANDS', 'Marshall Islands', 'MHL', 584, 692, 0, '2023-07-04 09:00:50', 1),
    (134, 'MQ', 'MARTINIQUE', 'Martinique', 'MTQ', 474, 596, 0, '2023-07-04 09:00:50', 1),
    (135, 'MR', 'MAURITANIA', 'Mauritania', 'MRT', 478, 222, 0, '2023-07-04 09:00:50', 1),
    (136, 'MU', 'MAURITIUS', 'Mauritius', 'MUS', 480, 230, 0, '2023-07-04 09:00:50', 1),
    (137, 'YT', 'MAYOTTE', 'Mayotte', '', 0, 269, 0, '2023-07-04 09:00:50', 1),
    (138, 'MX', 'MEXICO', 'Mexico', 'MEX', 484, 52, 0, '2023-07-04 09:00:50', 1),
    (139, 'FM', 'MICRONESIA, FEDERATED STATES OF', 'Micronesia, Federated States of', 'FSM', 583, 691, 0, '2023-07-04 09:00:50', 1),
    (140, 'MD', 'MOLDOVA, REPUBLIC OF', 'Moldova, Republic of', 'MDA', 498, 373, 0, '2023-07-04 09:00:50', 1),
    (141, 'MC', 'MONACO', 'Monaco', 'MCO', 492, 377, 0, '2023-07-04 09:00:50', 1),
    (142, 'MN', 'MONGOLIA', 'Mongolia', 'MNG', 496, 976, 0, '2023-07-04 09:00:50', 1),
    (143, 'MS', 'MONTSERRAT', 'Montserrat', 'MSR', 500, 1664, 0, '2023-07-04 09:00:50', 1),
    (144, 'MA', 'MOROCCO', 'Morocco', 'MAR', 504, 212, 0, '2023-07-04 09:00:50', 1),
    (145, 'MZ', 'MOZAMBIQUE', 'Mozambique', 'MOZ', 508, 258, 0, '2023-07-04 09:00:50', 1),
    (146, 'MM', 'MYANMAR', 'Myanmar', 'MMR', 104, 95, 0, '2023-07-04 09:00:50', 1),
    (147, 'NA', 'NAMIBIA', 'Namibia', 'NAM', 516, 264, 0, '2023-07-04 09:00:50', 1),
    (148, 'NR', 'NAURU', 'Nauru', 'NRU', 520, 674, 0, '2023-07-04 09:00:50', 1),
    (149, 'NP', 'NEPAL', 'Nepal', 'NPL', 524, 977, 0, '2023-07-04 09:00:50', 1),
    (150, 'NL', 'NETHERLANDS', 'Netherlands', 'NLD', 528, 31, 0, '2023-07-04 09:00:50', 1),
    (151, 'AN', 'NETHERLANDS ANTILLES', 'Netherlands Antilles', 'ANT', 530, 599, 0, '2023-07-04 09:00:50', 1),
    (152, 'NC', 'NEW CALEDONIA', 'New Caledonia', 'NCL', 540, 687, 0, '2023-07-04 09:00:50', 1),
    (153, 'NZ', 'NEW ZEALAND', 'New Zealand', 'NZL', 554, 64, 0, '2023-07-04 09:00:50', 1),
    (154, 'NI', 'NICARAGUA', 'Nicaragua', 'NIC', 558, 505, 0, '2023-07-04 09:00:50', 1),
    (155, 'NE', 'NIGER', 'Niger', 'NER', 562, 227, 0, '2023-07-04 09:00:50', 1),
    (156, 'NG', 'NIGERIA', 'Nigeria', 'NGA', 566, 234, 0, '2023-07-04 09:00:50', 1),
    (157, 'NU', 'NIUE', 'Niue', 'NIU', 570, 683, 0, '2023-07-04 09:00:50', 1),
    (158, 'NF', 'NORFOLK ISLAND', 'Norfolk Island', 'NFK', 574, 672, 0, '2023-07-04 09:00:50', 1),
    (159, 'MP', 'NORTHERN MARIANA ISLANDS', 'Northern Mariana Islands', 'MNP', 580, 1670, 0, '2023-07-04 09:00:50', 1),
    (160, 'NO', 'NORWAY', 'Norway', 'NOR', 578, 47, 0, '2023-07-04 09:00:50', 1),
    (161, 'OM', 'OMAN', 'Oman', 'OMN', 512, 968, 0, '2023-07-04 09:00:50', 1),
    (162, 'PK', 'PAKISTAN', 'Pakistan', 'PAK', 586, 92, 0, '2023-07-04 09:00:50', 1),
    (163, 'PW', 'PALAU', 'Palau', 'PLW', 585, 680, 0, '2023-07-04 09:00:50', 1),
    (164, 'PS', 'PALESTINIAN TERRITORY, OCCUPIED', 'Palestinian Territory, Occupied', '', 0, 970, 0, '2023-07-04 09:00:50', 1),
    (165, 'PA', 'PANAMA', 'Panama', 'PAN', 591, 507, 0, '2023-07-04 09:00:50', 1),
    (166, 'PG', 'PAPUA NEW GUINEA', 'Papua New Guinea', 'PNG', 598, 675, 0, '2023-07-04 09:00:50', 1),
    (167, 'PY', 'PARAGUAY', 'Paraguay', 'PRY', 600, 595, 0, '2023-07-04 09:00:50', 1),
    (168, 'PE', 'PERU', 'Peru', 'PER', 604, 51, 0, '2023-07-04 09:00:50', 1),
    (169, 'PH', 'PHILIPPINES', 'Philippines', 'PHL', 608, 63, 0, '2023-07-04 09:00:50', 1),
    (170, 'PN', 'PITCAIRN', 'Pitcairn', 'PCN', 612, 0, 0, '2023-07-04 09:00:50', 1),
    (171, 'PL', 'POLAND', 'Poland', 'POL', 616, 48, 0, '2023-07-04 09:00:50', 1),
    (172, 'PT', 'PORTUGAL', 'Portugal', 'PRT', 620, 351, 0, '2023-07-04 09:00:50', 1),
    (173, 'PR', 'PUERTO RICO', 'Puerto Rico', 'PRI', 630, 1787, 0, '2023-07-04 09:00:50', 1),
    (174, 'QA', 'QATAR', 'Qatar', 'QAT', 634, 974, 0, '2023-07-04 09:00:50', 1),
    (175, 'RE', 'REUNION', 'Reunion', 'REU', 638, 262, 0, '2023-07-04 09:00:50', 1),
    (176, 'RO', 'ROMANIA', 'Romania', 'ROM', 642, 40, 0, '2023-07-04 09:00:50', 1),
    (177, 'RU', 'RUSSIAN FEDERATION', 'Russian Federation', 'RUS', 643, 70, 0, '2023-07-04 09:00:50', 1),
    (178, 'RW', 'RWANDA', 'Rwanda', 'RWA', 646, 250, 0, '2023-07-04 09:00:50', 1),
    (179, 'SH', 'SAINT HELENA', 'Saint Helena', 'SHN', 654, 290, 0, '2023-07-04 09:00:50', 1),
    (180, 'KN', 'SAINT KITTS AND NEVIS', 'Saint Kitts and Nevis', 'KNA', 659, 1869, 0, '2023-07-04 09:00:50', 1),
    (181, 'LC', 'SAINT LUCIA', 'Saint Lucia', 'LCA', 662, 1758, 0, '2023-07-04 09:00:50', 1),
    (182, 'PM', 'SAINT PIERRE AND MIQUELON', 'Saint Pierre and Miquelon', 'SPM', 666, 508, 0, '2023-07-04 09:00:50', 1),
    (183, 'VC', 'SAINT VINCENT AND THE GRENADINES', 'Saint Vincent and the Grenadines', 'VCT', 670, 1784, 0, '2023-07-04 09:00:50', 1),
    (184, 'WS', 'SAMOA', 'Samoa', 'WSM', 882, 684, 0, '2023-07-04 09:00:50', 1),
    (185, 'SM', 'SAN MARINO', 'San Marino', 'SMR', 674, 378, 0, '2023-07-04 09:00:50', 1),
    (186, 'ST', 'SAO TOME AND PRINCIPE', 'Sao Tome and Principe', 'STP', 678, 239, 0, '2023-07-04 09:00:50', 1),
    (187, 'SA', 'SAUDI ARABIA', 'Saudi Arabia', 'SAU', 682, 966, 0, '2023-07-04 09:00:50', 1),
    (188, 'SN', 'SENEGAL', 'Senegal', 'SEN', 686, 221, 0, '2023-07-04 09:00:50', 1),
    (189, 'CS', 'SERBIA AND MONTENEGRO', 'Serbia and Montenegro', '', 0, 381, 0, '2023-07-04 09:00:50', 1),
    (190, 'SC', 'SEYCHELLES', 'Seychelles', 'SYC', 690, 248, 0, '2023-07-04 09:00:50', 1),
    (191, 'SL', 'SIERRA LEONE', 'Sierra Leone', 'SLE', 694, 232, 0, '2023-07-04 09:00:50', 1),
    (192, 'SG', 'SINGAPORE', 'Singapore', 'SGP', 702, 65, 0, '2023-07-04 09:00:50', 1),
    (193, 'SK', 'SLOVAKIA', 'Slovakia', 'SVK', 703, 421, 0, '2023-07-04 09:00:50', 1),
    (194, 'SI', 'SLOVENIA', 'Slovenia', 'SVN', 705, 386, 0, '2023-07-04 09:00:50', 1),
    (195, 'SB', 'SOLOMON ISLANDS', 'Solomon Islands', 'SLB', 90, 677, 0, '2023-07-04 09:00:50', 1),
    (196, 'SO', 'SOMALIA', 'Somalia', 'SOM', 706, 252, 0, '2023-07-04 09:00:50', 1),
    (197, 'ZA', 'SOUTH AFRICA', 'South Africa', 'ZAF', 710, 27, 0, '2023-07-04 09:00:50', 1),
    (198, 'GS', 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'South Georgia and the South Sandwich Islands', '', 0, 0, 0, '2023-07-04 09:00:50', 1),
    (199, 'ES', 'SPAIN', 'Spain', 'ESP', 724, 34, 0, '2023-07-04 09:00:50', 1),
    (200, 'LK', 'SRI LANKA', 'Sri Lanka', 'LKA', 144, 94, 0, '2023-07-04 09:00:50', 1),
    (201, 'SD', 'SUDAN', 'Sudan', 'SDN', 736, 249, 0, '2023-07-04 09:00:50', 1),
    (202, 'SR', 'SURINAME', 'Suriname', 'SUR', 740, 597, 0, '2023-07-04 09:00:50', 1),
    (203, 'SJ', 'SVALBARD AND JAN MAYEN', 'Svalbard and Jan Mayen', 'SJM', 744, 47, 0, '2023-07-04 09:00:50', 1),
    (204, 'SZ', 'SWAZILAND', 'Swaziland', 'SWZ', 748, 268, 0, '2023-07-04 09:00:50', 1),
    (205, 'SE', 'SWEDEN', 'Sweden', 'SWE', 752, 46, 0, '2023-07-04 09:00:50', 1),
    (206, 'CH', 'SWITZERLAND', 'Switzerland', 'CHE', 756, 41, 0, '2023-07-04 09:00:50', 1),
    (207, 'SY', 'SYRIAN ARAB REPUBLIC', 'Syrian Arab Republic', 'SYR', 760, 963, 0, '2023-07-04 09:00:50', 1),
    (208, 'TW', 'TAIWAN, PROVINCE OF CHINA', 'Taiwan, Province of China', 'TWN', 158, 886, 0, '2023-07-04 09:00:50', 1),
    (209, 'TJ', 'TAJIKISTAN', 'Tajikistan', 'TJK', 762, 992, 0, '2023-07-04 09:00:50', 1),
    (210, 'TZ', 'TANZANIA, UNITED REPUBLIC OF', 'Tanzania, United Republic of', 'TZA', 834, 255, 0, '2023-07-04 09:00:50', 1),
    (211, 'TH', 'THAILAND', 'Thailand', 'THA', 764, 66, 0, '2023-07-04 09:00:50', 1),
    (212, 'TL', 'TIMOR-LESTE', 'Timor-Leste', '', 0, 670, 0, '2023-07-04 09:00:50', 1),
    (213, 'TG', 'TOGO', 'Togo', 'TGO', 768, 228, 0, '2023-07-04 09:00:50', 1),
    (214, 'TK', 'TOKELAU', 'Tokelau', 'TKL', 772, 690, 0, '2023-07-04 09:00:50', 1),
    (215, 'TO', 'TONGA', 'Tonga', 'TON', 776, 676, 0, '2023-07-04 09:00:50', 1),
    (216, 'TT', 'TRINIDAD AND TOBAGO', 'Trinidad and Tobago', 'TTO', 780, 1868, 0, '2023-07-04 09:00:50', 1),
    (217, 'TN', 'TUNISIA', 'Tunisia', 'TUN', 788, 216, 0, '2023-07-04 09:00:50', 1),
    (218, 'TR', 'TURKEY', 'Turkey', 'TUR', 792, 90, 0, '2023-07-04 09:00:50', 1),
    (219, 'TM', 'TURKMENISTAN', 'Turkmenistan', 'TKM', 795, 7370, 0, '2023-07-04 09:00:50', 1),
    (220, 'TC', 'TURKS AND CAICOS ISLANDS', 'Turks and Caicos Islands', 'TCA', 796, 1649, 0, '2023-07-04 09:00:50', 1),
    (221, 'TV', 'TUVALU', 'Tuvalu', 'TUV', 798, 688, 0, '2023-07-04 09:00:50', 1),
    (222, 'UG', 'UGANDA', 'Uganda', 'UGA', 800, 256, 0, '2023-07-04 09:00:50', 1),
    (223, 'UA', 'UKRAINE', 'Ukraine', 'UKR', 804, 380, 0, '2023-07-04 09:00:50', 1),
    (224, 'AE', 'UNITED ARAB EMIRATES', 'United Arab Emirates', 'ARE', 784, 971, 0, '2023-07-04 09:00:50', 1),
    (225, 'GB', 'UNITED KINGDOM', 'United Kingdom', 'GBR', 826, 44, 0, '2023-07-04 09:00:50', 1),
    (226, 'US', 'UNITED STATES', 'United States', 'USA', 840, 1, 0, '2023-07-04 09:00:50', 1),
    (227, 'UM', 'UNITED STATES MINOR OUTLYING ISLANDS', 'United States Minor Outlying Islands', '', 0, 1, 0, '2023-07-04 09:00:50', 1),
    (228, 'UY', 'URUGUAY', 'Uruguay', 'URY', 858, 598, 0, '2023-07-04 09:00:50', 1),
    (229, 'UZ', 'UZBEKISTAN', 'Uzbekistan', 'UZB', 860, 998, 0, '2023-07-04 09:00:50', 1),
    (230, 'VU', 'VANUATU', 'Vanuatu', 'VUT', 548, 678, 0, '2023-07-04 09:00:50', 1),
    (231, 'VE', 'VENEZUELA', 'Venezuela', 'VEN', 862, 58, 0, '2023-07-04 09:00:50', 1),
    (232, 'VN', 'VIET NAM', 'Viet Nam', 'VNM', 704, 84, 0, '2023-07-04 09:00:50', 1),
    (233, 'VG', 'VIRGIN ISLANDS, BRITISH', 'Virgin Islands, British', 'VGB', 92, 1284, 0, '2023-07-04 09:00:50', 1),
    (234, 'VI', 'VIRGIN ISLANDS, U.S.', 'Virgin Islands, U.s.', 'VIR', 850, 1340, 0, '2023-07-04 09:00:50', 1),
    (235, 'WF', 'WALLIS AND FUTUNA', 'Wallis and Futuna', 'WLF', 876, 681, 0, '2023-07-04 09:00:50', 1),
    (236, 'EH', 'WESTERN SAHARA', 'Western Sahara', 'ESH', 732, 212, 0, '2023-07-04 09:00:50', 1),
    (237, 'YE', 'YEMEN', 'Yemen', 'YEM', 887, 967, 0, '2023-07-04 09:00:50', 1),
    (238, 'ZM', 'ZAMBIA', 'Zambia', 'ZMB', 894, 260, 0, '2023-07-04 09:00:50', 1),
    (239, 'ZW', 'ZIMBABWE', 'Zimbabwe', 'ZWE', 716, 263, 0, '2023-07-04 09:00:50', 1)");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('isd_code_data',0,'$date')");
}


///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='production_on_dashboard_changes'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `production_on_dashboard` INT(11) NOT NULL DEFAULT '1' COMMENT '0 - no , 1 - yes'");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('production_on_dashboard_changes',0,'$date')");
}
//////////////////////////Sanat////////////////////



///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='tarnsfor_module_add_ss'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `transid` INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `trans_add` INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `transid` INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `trans_add` INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `transid` INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `trans_add` INT(11) NOT NULL");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('tarnsfor_module_add_ss',0,'$date')");
}
//////////////////////////Sanat////////////////////



///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='power_drive_iso_in_product'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `product_mst` ADD `iso_verify` INT(11) NOT NULL COMMENT '0 - no , 1 - yes'");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('power_drive_iso_in_product',0,'$date')");
}
//////////////////////////Sanat////////////////////




///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='jainflect_quotation_field_add'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `payment_tems` VARCHAR(255) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `mode_of_dispatch` INT(11) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `destination` VARCHAR(255) NOT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('jainflect_quotation_field_add',0,'$date')");
}
//////////////////////////Sanat////////////////////

///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='jainflect_quotation_field_add'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `destination` VARCHAR(255) NOT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('jainflect_quotation_field_add',0,'$date')");
}
//////////////////////////Sanat////////////////////

///////////pathik/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='apson_chang'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `payment_tems_apson` VARCHAR(650) NOT NULL AFTER `destination`, ADD `delivary_time_apson` VARCHAR(650) NOT NULL AFTER `payment_tems_apson`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `apson_validity_date` DATE NOT NULL AFTER `trans_add`, ADD `apson_trans_scop_of` INT NOT NULL AFTER `apson_validity_date`, ADD `apson_dilivary_type` INT NOT NULL AFTER `apson_trans_scop_of`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `invoite_permission` INT NOT NULL AFTER `austar_permission`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_drawing` ADD `invoite_no` VARCHAR(50) NOT NULL AFTER `approved_date`, ADD `invoite_series` VARCHAR(50) NOT NULL AFTER `invoite_no`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_drawing` CHANGE `invoite_series` `invoite_series` INT NOT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('apson_chang',0,'$date')");
}
//////////////////////////pathik////////////////////




///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='FLOWJET_GRN_SERIESAS_UPDATE_SET'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'DIRECT GRN', '0', '0', '61', '3', 'GRN/DR/', '/23-24', '1', '0', '2023-04-01 01:20:42', '1', '0', '1', '1000', '', '3')");
  $query_invoicetypes = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'OUTSIDE SO GRN', '0', '0', '62', '3', 'GRN/OUT/SO/', '/23-24', '1', '0', '2023-04-01 01:20:42', '1', '0', '1', '1000', '', '3')");
  $query_invoicetypes = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'RETURNABLE CHALAN GRN', '0', '0', '63', '3', 'GRN/RET/CHN/', '/23-24', '1', '0', '2023-04-01 01:20:42', '1', '0', '1', '1000', '', '3')");
  $query_invoicetypes = $dbcon->query("INSERT INTO `tbl_invoicetype` (`invoicetype_id`, `invoice_type`, `taxinvoice_start`, `exciseinvoice_start`, `type_id`, `invoice_format`, `format_value`, `end_format_value`, `deletable`, `status`, `cdate`, `user_id`, `usertype_id`, `company_id`, `branch_id`, `gst_code`, `financial_year_id`) VALUES (NULL, 'STOCK TRANSFER GRN', '0', '0', '64', '3', 'GRN/ST/TRF/', '/23-24', '1', '0', '2023-04-01 01:20:42', '1', '0', '1', '1000', '', '3')");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('FLOWJET_GRN_SERIESAS_UPDATE_SET',0,'$date')");
}
//////////////////////////Sanat////////////////////



///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='workotder_rejectyed_qty_fireld_add'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE ` tbl_request_product` ADD `reject_qty` VARCHAR(50) NOT NULL DEFAULT '0'");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('workotder_rejectyed_qty_fireld_add',0,'$date')");
}
//////////////////////////Sanat////////////////////



///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='flowjet_special_permission_add_fined'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `flowjet_permission` INT(11) NOT NULL AFTER `sp_field_permission_id`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('flowjet_special_permission_add_fined',0,'$date')");
}
//////////////////////////Sanat////////////////////

///////////pathik/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='printsetupfadd'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `print_setup_mst` ADD `with_out_logo` INT NOT NULL AFTER `approve_status`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('printsetupfadd',0,'$date')");
}
//////////////////////////pathik////////////////////


///////////pathik/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='apsonspe'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `enable_old_dashbord` INT NOT NULL AFTER `production_on_dashboard`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `apson_special` INT NOT NULL AFTER `sreeji_stilix_permission`");
  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `tbl_apson_dilivary_type` (
      `dilivary_type_id` int(11) NOT NULL AUTO_INCREMENT,
      `dilivary_type_name` varchar(255) NOT NULL,
      `user_id` int(11) NOT NULL,
      `muser_id` int(11) NOT NULL,
      `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `mdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
      `status` int(11) NOT NULL,
      `company_id` int(11) NOT NULL,
      `branch_id` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`dilivary_type_id`),
      KEY `revision_number` (`dilivary_type_name`,`user_id`,`muser_id`,`status`),
      KEY `company_id` (`company_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('apsonspe',0,'$date')");
}
//////////////////////////pathik////////////////////
///////////pathik/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='apsonspenew'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `payment_terms_id` INT NOT NULL AFTER `payment_tems`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `quot_general_terms_condition_content` TEXT NOT NULL AFTER `apson_dilivary_type`");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_order` ADD `ship_address` TEXT NOT NULL AFTER `quot_general_terms_condition_content`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('apsonspenew',0,'$date')");
}
//////////////////////////pathik////////////////////




///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='flowjet_special_permission_add_fined_missing_field'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `packing` VARCHAR(100) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `cutting` VARCHAR(100) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `freight` VARCHAR(100) NOT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('flowjet_special_permission_add_fined_missing_field',0,'$date')");
}
//////////////////////////Sanat////////////////////


///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='global_eng_permission_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `global_eng_permission` INT NOT NULL AFTER `sp_field_permission_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `item_no` VARCHAR(50) NOT NULL AFTER `product_id`, ADD `item_size` VARCHAR(50) NOT NULL AFTER `item_no`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('global_eng_permission_add',0,'$date')");
}
//////////////////////////Sanat////////////////////


///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='austar_purchase_reserve_godown_auto'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `set_reserve_godown` INT(11) NOT NULL COMMENT '0 - no , 1 - yes' , ADD `default_godown_id` INT(11) NOT NULL");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_material_release_trn` ADD `batch_no` VARCHAR(25) NOT NULL AFTER `return_status`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('austar_purchase_reserve_godown_auto',0,'$date')");
}
//////////////////////////Sanat////////////////////

///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='jainflex_permission_filed_addin_special'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `jainflex_permission` INT(11) NOT NULL AFTER `sp_field_permission_id`");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `payment_tems_jainflex` TEXT NOT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('jainflex_permission_filed_addin_special',0,'$date')");
}

///////////Jayesh/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='table_inquiry_add_objection_field'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_inquiry` ADD `objection_flag` TINYINT(4) NOT NULL DEFAULT '0' AFTER `task_priority_id`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('table_inquiry_add_objection_field',0,'$date')");
}

///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='inter_power_generatl_stok_so_user'";

$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_general_stock_trn` ADD `sales_order_id` INT(11) NOT NULL DEFAULT 0 AFTER `general_stock_id`, ADD `for_user_id` INT(11) NOT NULL  DEFAULT 0 AFTER `sales_order_id`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('inter_power_generatl_stok_so_user',0,'$date')");
}
//////////////////////////Sanat////////////////////



///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='3a_global_engg_extra_field_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");


  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `lut_no` VARCHAR(50) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `place_of_receipt` VARCHAR(50) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `pre_carriage_by` VARCHAR(50) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `port_of_loading` VARCHAR(50) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `port_of_discharge` VARCHAR(50) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `final_destination` VARCHAR(50) NOT NULL");
  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `container_no` VARCHAR(50) NOT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('3a_global_engg_lut_field_add',0,'$date')");
}



///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='global_eng_permission_add_profoma_field_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_proforma_trn` ADD `item_size` VARCHAR(50) NOT NULL AFTER `product_id`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('global_eng_permission_add_profoma_field_add',0,'$date')");
}
//////////////////////////Sanat////////////////////


///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='uniter_special_fields'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `uniter_permission` INT NOT NULL AFTER `invoite_permission`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('uniter_special_fields',0,'$date')");
}

//////////////////////////sahaj////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='daily_report_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");




  $query_invoicetype = $dbcon->query("CREATE TABLE IF NOT EXISTS `daily_report` (
      `r_id` int(11) NOT NULL AUTO_INCREMENT,
      `description` varchar(1000) NOT NULL,
      `date` date NOT NULL,
      `user_id` int(11) NOT NULL,
      `show_user_id` int(11) NOT NULL,
      PRIMARY KEY (`r_id`),
      UNIQUE KEY `r_id` (`r_id`),
      KEY `r_id_2` (`r_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('daily_report_add',0,'$date')");
}
//////////////////////////sahaj////////////////////





///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='powerdrive_orange_other_total_sSet'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_sales_ordertrn` ADD `orange_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_sales_ordertrn` ADD `trading_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_sales_ordertrn` ADD `mfg_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_sales_ordertrn` ADD `repairing_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_sales_ordertrn` ADD `other_total` VARCHAR(200) NULL');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_invoicetrn` ADD `orange_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_invoicetrn` ADD `trading_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_invoicetrn` ADD `mfg_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_invoicetrn` ADD `repairing_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_invoicetrn` ADD `other_total` VARCHAR(200) NULL');

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_trn` ADD `orange_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_trn` ADD `trading_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_trn` ADD `mfg_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_trn` ADD `repairing_total` VARCHAR(200) NULL');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_trn` ADD `other_total` VARCHAR(200) NULL');


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('powerdrive_orange_other_total_sSet',0,'$date')");
}
//////////////////////////Sanat////////////////////



//////////////////////////sahaj////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='kaivanya_extrusion_permission_inspecial'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetype = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `kaivanya_extrusion_permission` INT(11) NOT NULL DEFAULT '0' AFTER `sp_field_permission_id`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('kaivanya_extrusion_permission_inspecial',0,'$date')");
}
//////////////////////////sahaj////////////////////




///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='powerdrive_orange_other_total_sSet_cron'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_sales_ordertrn` ADD `cron_status_total` INT(11) NULL DEFAULT 0');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_invoicetrn` ADD `cron_status_total` INT(11) NULL DEFAULT 0');
  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_quotation_trn` ADD `cron_status_total` INT(11) NULL DEFAULT 0');


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('powerdrive_orange_other_total_sSet_cron',0,'$date')");
}
//////////////////////////Sanat////////////////////
///////////sahaj/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='gst_type_column_add_1'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");


  // $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_proforma_invoice` ADD `gst_type` INT NOT NULL DEFAULT '0'');
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `gst_type` INT NOT NULL DEFAULT 0");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('gst_type_column_add_1',0,'$date')");
}
//////////////////////////sahaj////////////////////



///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='jet_technologies_bom_factor_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_bomtrn` ADD `conversation_factor` INT(11) NOT NULL DEFAULT '1'");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('jet_technologies_bom_factor_add',0,'$date')");
}
//////////////////////////Sanat////////////////////


///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='qc_unit_set_base_or_conv'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `qc_unit` INT(11) NOT NULL DEFAULT '2' COMMENT '1-base ,2-conv'");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('qc_unit_set_base_or_conv',0,'$date')");
}
//////////////////////////Sanat////////////////////


////////////////////////// Jayesh /////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='stage_template_file_in_stage_master'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query('ALTER TABLE `tbl_opportunity_mst` ADD `opp_template` VARCHAR(255) NOT NULL , ADD `opp_file` VARCHAR(255) NOT NULL');


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('stage_template_file_in_stage_master',0,'$date')");
}

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='whatsapp_msg_config_company_setting'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_configuration` ADD `enable_whatsapp` TINYINT(4) NOT NULL , ADD `whatsapp_api_url` TEXT NOT NULL , ADD `whatsapp_api_key` TEXT NOT NULL , ADD `whatsapp_template` VARCHAR(255) NOT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('whatsapp_msg_config_company_setting',0,'$date')");
}
////////////////////////// Jayesh /////////////////

////////////////////////// sahaj /////////////////


$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='transportation_address_table_new'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `transportation_address` (
        `address_id` int(11) NOT NULL AUTO_INCREMENT,
        `cdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `transportation_address` text NOT NULL,
        `transportation_id` int(11) NOT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `company_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `status` int(11) NOT NULL,
        `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`address_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('transportation_address_table_new',0,'$date')");
}
////////////////////////// sahaj /////////////////


$cnt = 0;
$sql = "SELECT * FROM db_update_log WHERE status=0 AND db_branch_name='transportation_details_table_new'";
$result = $dbcon->query($sql);

if ($result) {
  $cnt = mysqli_num_rows($result);
}


$date = date("Y-m-d H:i:s");

// Corrected ALTER TABLE syntax
$query_invoicetypes = $dbcon->query("ALTER TABLE transportation_details 
                                           ADD transportation_branch varchar(200) NOT NULL,
                                           ADD transportation_email_id varchar(200) NOT NULL,
                                           ADD transportation_phone_num varchar(20) NOT NULL");

$query_log = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) 
                                  VALUES ('transportation_details_table_new', 0, '$date')");

////////////////////////// sahaj /////////////////



///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='chetak_permission_add_in_specflg'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `chetak_permission` INT(11) NOT NULL AFTER `sp_field_permission_id`");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('chetak_permission_add_in_specflg',0,'$date')");
}
//////////////////////////Sanat////////////////////


////////////////////////// Jayesh ////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='add_field_tbl_userwise_approval_setting'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_userwise_approval_setting` ADD `amount_type` TINYINT NOT NULL DEFAULT '1' COMMENT '1 : Amount 2 : Percentage' , ADD `percentage` DOUBLE(10,2) NOT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('add_field_tbl_userwise_approval_setting',0,'$date')");
}


$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='add_field_tbl_daily_report'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `daily_report` ADD `file` TEXT NOT NULL , ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('add_field_tbl_daily_report',0,'$date')");
}

////////////////////////// Jayesh ////////////////////

///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='jet_technologies_release_usuue_fixed'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_material_release_trn` ADD `is_temp_delete` INT(11) NOT NULL DEFAULT '0'");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('jet_technologies_release_usuue_fixed',0,'$date')");
}
//////////////////////////Sanat////////////////////




///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='datbase_double_datatype_changes'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` CHANGE `cgst_tax_rate` `cgst_tax_rate` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` CHANGE `sgst_tax_per` `sgst_tax_per` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` CHANGE `sgst_tax_rate` `sgst_tax_rate` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` CHANGE `igst_tax_per` `igst_tax_per` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` CHANGE `igst_tax_rate` `igst_tax_rate` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` CHANGE `cgst_tax_per` `cgst_tax_per` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` CHANGE `currency_rate` `currency_rate` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `cgst_tax_per` `cgst_tax_per` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `sgst_tax_per` `sgst_tax_per` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_purchaseordertrn` CHANGE `igst_tax_per` `igst_tax_per` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `cgst_tax_per` `cgst_tax_per` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `sgst_tax_per` `sgst_tax_per` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `igst_tax_per` `igst_tax_per` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `cgst_tax_rate` `cgst_tax_rate` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `sgst_tax_rate` `sgst_tax_rate` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_potrancation` CHANGE `igst_tax_rate` `igst_tax_rate` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_invoicetrn` CHANGE `currency_rate` `currency_rate` DOUBLE(22,2) NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_batch_temp_material_start_time_deduct` ADD `mt_trn_id` INT(11) NOT NULL");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('datbase_double_datatype_changes',0,'$date')");
}
//////////////////////////Sanat////////////////////


////////////////////////// sahaj ////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='interpower_induction_special_config_column_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `interpower_permission` INT NOT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('interpower_induction_special_config_column_add',0,'$date')");
}

////////////////////////// sahaj ////////////////////

// ////////////////////////// sahaj ////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='tbl_quotation_ADD_payment_terms_ADD_client_id_1'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `payment_terms` TEXT NOT NULL , ADD `client_id` INT NOT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('tbl_quotation_ADD_payment_terms_ADD_client_id_1',0,'$date')");
}


// ////////////////////////// sahaj ////////////////////

/// Jayesh ///
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='rb_product_mst_add_make_selection1'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `product_mst` ADD `r_make_id` INT NULL DEFAULT NULL , ADD `r_make_name` VARCHAR(255) NULL DEFAULT NULL");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('rb_product_mst_add_make_selection1',0,'$date')");
}


// Add new field in 
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='tbl_ledger_add_ledger_type'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_ledger` ADD `ledger_type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0 : New Ledger 1 : Existing Ledger'");

  $query_invoicetypes = $dbcon->query("UPDATE `tbl_ledger` SET `ledger_type`=1 WHERE 1");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('tbl_ledger_add_ledger_type',0,'$date')");
}
/// End ///


////////////////////////// sahaj ////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='dintech_valve_permission_special_config_column_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `dintech_valve_permission` INT NOT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation_trn` ADD `item_class` TEXT NOT NULL ;
      ");

  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('interpower_induction_special_config_column_add',0,'$date')");
}

////////////////////////// sahaj ////////////////////


///////////Sanat/////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='datbase_double_datatype_changes_chetak'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` CHANGE `remaning_invoice_qty` `remaning_invoice_qty` DOUBLE(22,2) NOT NULL");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_sales_ordertrn` ADD `remaning_invoice_conv_qty` DECIMAL(22,2) NOT NULL AFTER `remaning_invoice_qty`");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('datbase_double_datatype_changes_chetak',0,'$date')");
}
//////////////////////////Sanat////////////////////


/////////// JS /////////////////////
$cnt=0;
$sql="SELECT * FROM db_update_log where status=0 and db_branch_name='daily_report_description_type_change'";

$result=$dbcon->query($sql);
$cnt=mysqli_num_rows($result);
if(empty($cnt)){
	$cnt=0;
}

if($cnt<=0){
	$date= date("Y-m-d H:i:s");
	$query_invoicetypes = $dbcon->query("ALTER TABLE `daily_report` CHANGE `description` `description` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `daily_report` ADD `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0 : Active , 1 : Deleted'");

	$query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('daily_report_description_type_change',0,'$date')");
}
/////////// JS /////////////////////


/////////// JS /////////////////////
$cnt=0;
$sql="SELECT * FROM db_update_log where status=0 and db_branch_name='tbl_proforma_invoice_add_client_id'";

$result=$dbcon->query($sql);
$cnt=mysqli_num_rows($result);
if(empty($cnt)){
	$cnt=0;
}

if($cnt<=0){
	$date= date("Y-m-d H:i:s");
	$query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_proforma_invoice` ADD `client_id` INT(11) NOT NULL");

	$query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('tbl_proforma_invoice_add_client_id',0,'$date')");
}
/////////// JS /////////////////////


////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_mst_design_for_dintech_0'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `mst_design` (
        `design_id` int(11) NOT NULL AUTO_INCREMENT,
        `design_name` varchar(250) NOT NULL,
        `design_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`design_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_mst_design_for_dintech_0',0,'$date')");
}

////////////////////////// sahaj ////////////////////


////////////////////////// sahaj ////////////////////
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_body_moc_mst_for_dintech_1'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `body_moc_mst` (
        `body_id` int(11) NOT NULL AUTO_INCREMENT,
        `body_moc` varchar(250) NOT NULL,
        `body_moc_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`body_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_body_moc_mst_for_dintech_1',0,'$date')");
}

////////////////////////// sahaj ////////////////////


$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_bonnet_moc_mst_for_dintech_2'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `bonnet_moc_mst` (
        `bonnet_id` int(11) NOT NULL AUTO_INCREMENT,
        `bonnet_moc` varchar(250) NOT NULL,
        `bonnet_moc_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`bonnet_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_bonnet_moc_mst_for_dintech_2',0,'$date')");
}

////////////////////////// sahaj ////////////////////


$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_wedge_mst_for_dintech_2'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `wedge_mst` (
        `wedge_id` int(11) NOT NULL AUTO_INCREMENT,
        `wedge` varchar(250) NOT NULL,
        `wedge_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`wedge_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_wedge_mst_for_dintech_2',0,'$date')");
}

////////////////////////// sahaj ////////////////////


$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_spindle_mst_for_dintech_2'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `spindle_mst` (
        `spindle_id` int(11) NOT NULL AUTO_INCREMENT,
        `spindle` varchar(250) NOT NULL,
        `spindle_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`spindle_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_spindle_mst_for_dintech_2',0,'$date')");
}

////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_bonnet_bolt_for_dintech'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `bonnet_bolt_mst` (
        `bonnet_bolt_id` int(11) NOT NULL AUTO_INCREMENT,
        `bonnet_bolt` varchar(250) NOT NULL,
        `bonnet_bolt_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`bonnet_bolt_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_bonnet_bolt_for_dintech',0,'$date')");
}

////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_bonnet_gasket_for_dintech'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `bonnet_gasket_mst` (
        `bonnet_gasket_id` int(11) NOT NULL AUTO_INCREMENT,
        `bonnet_gasket` varchar(250) NOT NULL,
        `bonnet_gasket_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`bonnet_gasket_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_bonnet_gasket_for_dintech',0,'$date')");
}

////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_end_mst_for_dintech_0'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `end_mst` (
        `end_id` int(11) NOT NULL AUTO_INCREMENT,
        `end` varchar(250) NOT NULL,
        `end_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`end_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_end_mst_for_dintech_0',0,'$date')");
}

////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_operation_mst_for_dintech_0'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `operation_mst` (
        `operation_id` int(11) NOT NULL AUTO_INCREMENT,
        `operation` varchar(250) NOT NULL,
        `operation_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`operation_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_operation_mst_for_dintech_0',0,'$date')");
}

////////////////////////// sahaj ////////////////////


$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_testing_std_mst_for_dintech_0'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `testing_std_mst` (
        `testing_std_id` int(11) NOT NULL AUTO_INCREMENT,
        `testing_std` varchar(250) NOT NULL,
        `testing_std_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`testing_std_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_testing_std_mst_for_dintech_0',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_stem_nut_mst_for_dintech'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `stem_nut_mst` (
        `stem_nut_id` int(11) NOT NULL AUTO_INCREMENT,
        `stem_nut` varchar(250) NOT NULL,
        `stem_nut_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`stem_nut_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_stem_nut_mst_for_dintech',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_gbush_mst_for_dintech_0'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `gbush_mst` (
        `gbush_id` int(11) NOT NULL AUTO_INCREMENT,
        `gbush` varchar(250) NOT NULL,
        `gbush_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`gbush_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_gbush_mst_for_dintech_0',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_disc_nut_mst_for_dintech'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `disc_nut_mst` (
        `disc_nut_id` int(11) NOT NULL AUTO_INCREMENT,
        `disc_nut` varchar(250) NOT NULL,
        `disc_nut_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`disc_nut_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_disc_nut_mst_for_dintech',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_cover_nut_mst_for_dintech'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `cover_nut_mst` (
        `cover_nut_id` int(11) NOT NULL AUTO_INCREMENT,
        `cover_nut` varchar(250) NOT NULL,
        `cover_nut_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`cover_nut_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_cover_nut_mst_for_dintech',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_gland_flange_mst_for_dintech'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `gland_flange_mst` (
        `gland_flange_id` int(11) NOT NULL AUTO_INCREMENT,
        `gland_flange` varchar(250) NOT NULL,
        `gland_flange_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`gland_flange_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_gland_flange_mst_for_dintech',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_hand_wheel_mst_for_dintech'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `hand_wheel_mst` (
        `hand_wheel_id` int(11) NOT NULL AUTO_INCREMENT,
        `hand_wheel` varchar(250) NOT NULL,
        `hand_wheel_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`hand_wheel_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_hand_wheel_mst_for_dintech',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_bonnet_nut_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `bonnet_nut_mst` (
        `bonnet_nut_id` int(11) NOT NULL AUTO_INCREMENT,
        `bonnet_nut` varchar(250) NOT NULL,
        `bonnet_nut_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`bonnet_nut_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_bonnet_nut_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_eyebolt_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `eyebolt_mst` (
        `eyebolt_id` int(11) NOT NULL AUTO_INCREMENT,
        `eyebolt` varchar(250) NOT NULL,
        `eyebolt_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`eyebolt_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_eyebolt_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_f_to_f_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `f_to_f_mst` (
        `f_to_f_id` int(11) NOT NULL AUTO_INCREMENT,
        `f_to_f` varchar(250) NOT NULL,
        `f_to_f_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`f_to_f_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_f_to_f_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_extra_para_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `extra_para_mst` (
        `extra_para_id` int(11) NOT NULL AUTO_INCREMENT,
        `extra_para` varchar(250) NOT NULL,
        `extra_para_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`extra_para_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_extra_para_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_disc_moc_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `disc_moc_mst` (
        `disc_moc_id` int(11) NOT NULL AUTO_INCREMENT,
        `disc_moc` varchar(250) NOT NULL,
        `disc_moc_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`disc_moc_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_disc_moc_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_cover_moc_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `cover_moc_mst` (
        `cover_moc_id` int(11) NOT NULL AUTO_INCREMENT,
        `cover_moc` varchar(250) NOT NULL,
        `cover_moc_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`cover_moc_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_cover_moc_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_cover_stud_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `cover_stud_mst` (
        `cover_stud_id` int(11) NOT NULL AUTO_INCREMENT,
        `cover_stud` varchar(250) NOT NULL,
        `cover_stud_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`cover_stud_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_cover_stud_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_cover_gasket_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `cover_gasket_mst` (
        `cover_gasket_id` int(11) NOT NULL AUTO_INCREMENT,
        `cover_gasket` varchar(250) NOT NULL,
        `cover_gasket_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`cover_gasket_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_cover_gasket_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_gland_packing_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `gland_packing_mst` (
        `gland_packing_id` int(11) NOT NULL AUTO_INCREMENT,
        `gland_packing` varchar(250) NOT NULL,
        `gland_packing_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`gland_packing_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_gland_packing_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_cover_stud__mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `cover_stud__mst` (
        `cover_stud__id` int(11) NOT NULL AUTO_INCREMENT,
        `cover_stud_` varchar(250) NOT NULL,
        `cover_stud__status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`cover_stud__id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_cover_stud__mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////


$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_screen_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `screen_mst` (
        `screen_id` int(11) NOT NULL AUTO_INCREMENT,
        `screen` varchar(250) NOT NULL,
        `screen_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`screen_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_screen_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_drain_plug_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `drain_plug_mst` (
        `drain_plug_id` int(11) NOT NULL AUTO_INCREMENT,
        `drain_plug` varchar(250) NOT NULL,
        `drain_plug_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`drain_plug_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_drain_plug_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_spring_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `spring_mst` (
        `spring_id` int(11) NOT NULL AUTO_INCREMENT,
        `spring` varchar(250) NOT NULL,
        `spring_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`spring_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_spring_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_hinge_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `hinge_mst` (
        `hinge_id` int(11) NOT NULL AUTO_INCREMENT,
        `hinge` varchar(250) NOT NULL,
        `hinge_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`hinge_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_hinge_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_hinge_pin_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `hinge_pin_mst` (
        `hinge_pin_id` int(11) NOT NULL AUTO_INCREMENT,
        `hinge_pin` varchar(250) NOT NULL,
        `hinge_pin_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`hinge_pin_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_hinge_pin_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_bracket_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `bracket_mst` (
        `bracket_id` int(11) NOT NULL AUTO_INCREMENT,
        `bracket` varchar(250) NOT NULL,
        `bracket_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`bracket_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_bracket_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_yoke_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `yoke_mst` (
        `yoke_id` int(11) NOT NULL AUTO_INCREMENT,
        `yoke` varchar(250) NOT NULL,
        `yoke_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`yoke_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_yoke_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_ball_moc_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `ball_moc_mst` (
        `ball_moc_id` int(11) NOT NULL AUTO_INCREMENT,
        `ball_moc` varchar(250) NOT NULL,
        `ball_moc_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`ball_moc_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_ball_moc_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_fastners_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `fastners_mst` (
        `fastners_id` int(11) NOT NULL AUTO_INCREMENT,
        `fastners` varchar(250) NOT NULL,
        `fastners_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`fastners_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_fastners_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_side_piece_connectors_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `side_piece_connectors_mst` (
        `side_piece_connectors_id` int(11) NOT NULL AUTO_INCREMENT,
        `side_piece_connectors` varchar(250) NOT NULL,
        `side_piece_connectors_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`side_piece_connectors_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_side_piece_connectors_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_seat_insert_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `seat_insert_mst` (
        `seat_insert_id` int(11) NOT NULL AUTO_INCREMENT,
        `seat_insert` varchar(250) NOT NULL,
        `seat_insert_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`seat_insert_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_seat_insert_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='s_created_seatring_insert_mst_for_dintecch'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $date = date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `seatring_mst` (
        `seatring_id` int(11) NOT NULL AUTO_INCREMENT,
        `seatring` varchar(250) NOT NULL,
        `seatring_status` int(11) NOT NULL,
        `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` int(11) NOT NULL,
        `company_id` int(11) NOT NULL,
        `branch_id` int(11) NOT NULL,
        `is_deletable` int(11) NOT NULL,
        PRIMARY KEY (`seatring_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");


  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('s_created_seatring_insert_mst_for_dintecch',0,'$date')");
}
////////////////////////// sahaj ////////////////////

/////////// JS /////////////////////
$cnt=0;
$sql="SELECT * FROM db_update_log where status=0 and db_branch_name='tbl_company_special_field_permission_add_adk_perm'";

$result=$dbcon->query($sql);
$cnt=mysqli_num_rows($result);
if(empty($cnt)){
	$cnt=0;
}

if($cnt<=0){
	$date= date("Y-m-d H:i:s");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `adk_permission` INT NOT NULL DEFAULT '0'");

	$query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('tbl_company_special_field_permission_add_adk_perm',0,'$date')");
}
/////////// JS /////////////////////



if ($query_invoicetypes) {
  echo "Data Import Successfully.......";
} else {
  echo "Database Up to Date..";
}

?>