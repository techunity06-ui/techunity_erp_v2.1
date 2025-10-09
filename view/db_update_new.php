<?php
include_once ("../config/config.php");
include_once (COMMON_FUNCTION_OUTER_PATH . "common_functions.php");
include_once ("../include/function_database_query.php");


/*
========================================================
  Sanat db changes START :: Date 12-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='ewaybill_changes_fieldadd_for_invoice'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
 
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `trn_distance` VARCHAR(50) NOT NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_invoice` CHANGE `lr_date` `lr_date` DATETIME NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('ewaybill_changes_fieldadd_for_invoice',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  Sanat db changes END :: Date 12-04-2024 
========================================================
*/

/*
========================================================
  sahaj db changes START :: Date 15-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='ewaybill_changes_column_add_trn_mode_for_invoice'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
 
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_invoice` ADD `trn_mode` VARCHAR(50) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('ewaybill_changes_column_add_trn_mode_for_invoice',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  sahaj db changes END :: Date 15-04-2024 
========================================================
*/

/*
========================================================
  sahaj db changes START :: Date 16-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='make_permission_tbl_company_special_field_permission_column_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
 
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission`  ADD `make_permission` INT NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('make_permission_tbl_company_special_field_permission_column_add',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  sahaj db changes END :: Date 16-04-2024 
========================================================
*/


/*
========================================================
  sahaj db changes START :: Date 16-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='adk_complaint_media_name_mst'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
 
 $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `complaint_media_name_mst` (
    `media_id` int(11) NOT NULL AUTO_INCREMENT,
    `media_name` varchar(250) NOT NULL,
    `media_status` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `is_deletable` int(11) NOT NULL,
    PRIMARY KEY (`media_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('adk_complaint_media_name_mst',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  sahaj db changes END :: Date 16-04-2024 
========================================================
*/


/*
========================================================
  JS db changies START :: Date 16-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='add_assign_users_field_tbl_complaint'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
 
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_complaint` ADD `assign_cust_ids` VARCHAR(255) NULL");
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_follow` ADD `assign_cust_id` INT NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('add_assign_users_field_tbl_complaint',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  JS db changies END :: Date 16-04-2024 
========================================================
*/


/*
========================================================
  JS db changies START :: Date 16-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='add_new_fields_tbl_complaint'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
 
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_complaint` ADD `prod_serial_no` VARCHAR(255) NULL DEFAULT NULL , ADD `pro_model_no` VARCHAR(255) NULL DEFAULT NULL ;");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('add_new_fields_tbl_complaint',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  JS db changies END :: Date 12-04-2024 
========================================================
*/




/*
========================================================
  Sanat db changies START :: Date 12-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='quotation_quriey_updae_changes'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
 
 
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `payment_terms` TEXT NULL");
  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_quotation` ADD `client_id` INT NOT NULL DEFAULT 0");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('quotation_quriey_updae_changes',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  Sanat db changies END :: Date 12-04-2024 
========================================================
*/


/*
========================================================
  sahaj db changes START :: Date 18-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='tbl_purchaseorder_newfield_po_sub'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
 
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_purchaseorder` ADD `po_sub` VARCHAR(50) NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('tbl_purchaseorder_newfield_po_sub',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  sahaj db changes END :: Date 18-04-2024 
========================================================
*/


/*
========================================================
  JS db changies START :: Date 19-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='add_new_fields_media_id_tbl_complaint'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {
 
  $query_invoicetypees = $dbcon->query("ALTER TABLE `tbl_complaint` ADD `media_id` INT NOT NULL");
  $query_invoicetypees1 = $dbcon->query("ALTER TABLE `tbl_complaint` ADD `file` VARCHAR(255) NULL DEFAULT NULL");
  $query_invoicetypees2 = $dbcon->query("ALTER TABLE `tbl_complaint` CHANGE `complaint_date` `complaint_date` DATETIME NOT NULL");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('add_new_fields_media_id_tbl_complaint',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  JS db changies END :: Date 19-04-2024 
========================================================
*/


/*
========================================================
  sahaj db changes START :: Date 18-04-2024 
========================================================
*/

$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='master_field'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}

if ($cnt <= 0) {
  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `master_name_field` (
    `master_field_id` int(11) NOT NULL AUTO_INCREMENT,
    `master_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `make` int(11) NOT NULL,
    PRIMARY KEY (`master_field_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS tbl_master_field_value (
  master_field_value_id int(11) NOT NULL AUTO_INCREMENT,
  master_field_id int(11) NOT NULL,
  master_field_value varchar(255) NOT NULL,
  user_id int(11) NOT NULL,
  muser_id int(11) NOT NULL,
  cdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  mdate timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  master_field_value_status int(11) NOT NULL,
  company_id int(11) NOT NULL,
  branch_id int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (master_field_value_id),
  KEY revision_number (master_field_id,user_id,muser_id,master_field_value_status),
  KEY company_id (company_id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS tbl_master_field (
  master_field_id int(11) NOT NULL AUTO_INCREMENT,
  master_field varchar(255) NOT NULL,
  master_field_db_name varchar(255) NOT NULL,
  user_id int(11) NOT NULL,
  muser_id int(11) NOT NULL,
  cdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  mdate timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  master_field_status int(11) NOT NULL,
  company_id int(11) NOT NULL,
  branch_id int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (master_field_id),
  KEY revision_number (master_field,user_id,muser_id,master_field_status),
  KEY company_id (company_id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_master_field` ADD `priority` INT NOT NULL AFTER `master_field_db_name`");

  $query_invoicetypes = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `main_master` INT NOT NULL ");

  $query_dy = "select * from tbl_master_field where master_field_status=0  order by priority ASC";
  $dy_result = $dbcon->query($query_dy);
  while ($dy_row = mysqli_fetch_array($dy_result)) {

    /*echo "ALTER TABLE `product_mst` ADD ".$dy_row['master_field_db_name']." INT(11) NOT NULL";
    echo "<br>";*/


    $query_invoicetypes = $dbcon->query("ALTER TABLE `master_name_field` ADD " . $dy_row['master_field_db_name'] . " INT(11) NOT NULL");
  }

  $date = date("Y-m-d H:i:s");
  //$query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('bmr_no_generate',0,'$date')");
}

/*
========================================================
  sahaj db changes END :: Date 18-04-2024 
========================================================
*/



/*
========================================================
  JS db changies START :: Date 24-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='add_new_fields_address_mobileno_tbl_complaint'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetypees2 = $dbcon->query("ALTER TABLE `tbl_complaint` ADD `cust_mobile_no` VARCHAR(20) NOT NULL AFTER `cust_id`, ADD `cust_address` TEXT NOT NULL AFTER `cust_mobile_no`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('add_new_fields_address_mobileno_tbl_complaint',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  JS db changies END :: Date 24-04-2024 
========================================================
*/


/*
========================================================
  JS db changies START :: Date 25-04-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='master_field_new1'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetypes1 = $dbcon->query("DROP TABLE master_name_field");

  $query_invoicetypes = $dbcon->query("CREATE TABLE IF NOT EXISTS `master_name_field` (
    `master_field_id` int(11) NOT NULL AUTO_INCREMENT,
    `master_id` int(11) NOT NULL,
    `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `user_id` int(11) NOT NULL,
    `company_id` int(11) NOT NULL,
    `branch_id` int(11) NOT NULL,
    `make` int(11) NOT NULL,
    PRIMARY KEY (`master_field_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

  $query_dy = "select * from tbl_master_field where master_field_status=0  order by priority ASC";
  $dy_result = $dbcon->query($query_dy);
  while ($dy_row = mysqli_fetch_array($dy_result)) {

    $query_invoicetypes2 = $dbcon->query("ALTER TABLE `master_name_field` ADD " . $dy_row['master_field_db_name'] . " INT(11) NOT NULL");

  }

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('master_field_new1',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  JS db changies END :: Date 25-04-2024 
========================================================
*/



/*
========================================================
  JS db changies START :: Date 02-05-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='add_new_fields_master_type_master_name_field'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetypees2 = $dbcon->query("ALTER TABLE `master_name_field` ADD `master_type` VARCHAR(255) NOT NULL COMMENT 'Quotation, Sale Order, etc ..' AFTER `master_id`");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('add_new_fields_master_type_master_name_field',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  JS db changies END :: Date 02-05-2024 
========================================================
*/





/*
========================================================
  Sanat db changies START :: Date 02-05-2024 
========================================================
*/
$cnt = 0;
$sql = "SELECT * FROM db_update_log where status=0 and db_branch_name='fusiong_tech_permission_add'";
$result = $dbcon->query($sql);
$cnt = mysqli_num_rows($result);
if (empty($cnt)) {
  $cnt = 0;
}
if ($cnt <= 0) {

  $query_invoicetypees2 = $dbcon->query("ALTER TABLE `tbl_company_special_field_permission` ADD `fusiontech_permission` INT(11) NOT NULL DEFAULT '0'");

  //common branch update in db log table start
  $date = date("Y-m-d H:i:s");
  $query_invoicetypes = $dbcon->query("INSERT INTO `db_update_log`(`db_branch_name`, `status`, `cdate`) VALUES ('fusiong_tech_permission_add',0,'$date')");
  //common branch update in db log table end

}
/*
========================================================
  Sanat db changies END :: Date 02-05-2024 
========================================================
*/


if ($query_invoicetypes) {
  echo "Data Import Successfully.......";
} else {
  echo "Database Up to Date..";
}

?>
