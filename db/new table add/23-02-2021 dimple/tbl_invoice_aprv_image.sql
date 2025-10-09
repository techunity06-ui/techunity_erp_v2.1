CREATE TABLE `bigdatas_umaboy_erp`.`tbl_invoice_aprv_image` ( 
`invoice_aprv_image_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , 
`invoice_id` INT NOT NULL , 
`invoice_aprv_log_id` INT NOT NULL , 
`image_file` VARCHAR(255) NOT NULL COMMENT 'file name' , 
`status` INT NOT NULL , 
`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ) ENGINE = InnoDB;