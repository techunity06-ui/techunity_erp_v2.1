CREATE TABLE tbl_table_mast (
    table_id int NOT NULL AUTO_INCREMENT,
    table_name varchar(255),
	table_desc varchar(255),
    table_status int(11),
    cdate TIMESTAMP,
    user_id int(11),
	company_id int(11),
	PRIMARY KEY (table_id)
);
CREATE TABLE tbl_table_template (
    table_temp_id int NOT NULL AUTO_INCREMENT,
    template_name varchar(255),
	table_names text,
    table_temp_status int(11),
    cdate TIMESTAMP,
    user_id int(11),
	company_id int(11),
	PRIMARY KEY (table_temp_id)
);
CREATE TABLE tbl_table_log (
    table_log_id int NOT NULL AUTO_INCREMENT,
	action text,
    cdate TIMESTAMP,
    user_id int(11),
	company_id int(11),
	PRIMARY KEY (table_log_id)
);

//CRM Modules
ALTER TABLE `tbl_sales_ordertrn` ADD `delivery_date` date  AFTER branch_id;