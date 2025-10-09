<?php
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../include/function_database_query.php");
	
	
		$qry='ALTER TABLE `tbl_process_reserve_stock` ADD `cloan_entry` INT NOT NULL AFTER `total_conv_rate`';
		$result=$dbcon->query($qry);
		
		$qry3='SELECT apro.*,se.po_req_no FROM `tbl_allocate_process` as apro 
		        left join tbl_request_product as req on req.rp_id=apro.p_ref_id
		        left join tbl_set_main_process as se on se.sp_id=req.sp_id
		        WHERE `p_qty`=`pen_qty` and `start_qty`="" and `previous_process_id`!="" and req.extra_stock=0';
	$result3=$dbcon->query($qry3);
	$i=1;
		while($rel3=mysqli_fetch_assoc($result3))
		{
		    	$qry4='SELECT IFNULL(sum(base_stock),0) as proqty FROM `tbl_process_reserve_stock` WHERE `p_id`='.$rel3["p_id"].' and `stock_flage`="1"';
        	$result4=$dbcon->query($qry4);
		    $rel4=mysqli_fetch_assoc($result4);
		    
		    if($rel4['proqty']<$rel3['p_qty']){
		        echo $i." - ".$rel3["p_id"]." - ".$rel4['proqty']." - ".$rel3['p_qty']." - ".$rel3['cdate']." - ".$rel3['po_req_no'];
		        	$qry5='SELECT job_work_sub_trn_id FROM `tbl_job_work_sub_trn` WHERE `p_id`='.$rel3["previous_process_id"].' and job_work_sub_trn_status=0';
                	$result5=$dbcon->query($qry5);
        		    while($rel5=mysqli_fetch_assoc($result5)){
        		        $qry6='SELECT grn_trn_sub_id FROM `tbl_grn_sub_trn` WHERE `job_work_sub_trn_id`='.$rel5["job_work_sub_trn_id"].' and status=0';
                    	$result6=$dbcon->query($qry6);
            		    while($rel6=mysqli_fetch_assoc($result6)){
            		           $qry7='SELECT * FROM `tbl_process_stock_trn` WHERE `ref_id`='.$rel6["grn_trn_sub_id"].' and stock_status=0 and stock_flage=1 and ref_name="Grn_sub_trn"';
                            	$result7=$dbcon->query($qry7);
                    		    $rel7=mysqli_fetch_assoc($result7);
                    		    
                    		    
                    		    $info_sub['process_reserve_date']		= date("Y-m-d");;
                                $info_sub['product_id']		= $rel7['product_id'];
                                $info_sub['process_id']		= $rel7['process_id'];
                                $info_sub['base_stock']		= $rel7['base_stock'];
                                $info_sub['base_unit']		= $rel7['base_unit'];
                                $info_sub['conv_stock']		= $rel7['conv_stock'];
                                $info_sub['conv_unit']		= $rel7['conv_unit'];
                                $info_sub['stock_flage']		= 1;
                                $info_sub['godown_id']		= $rel7['godown_id'];
                                $info_sub['ref_name']		= $rel7['ref_name'];
                                $info_sub['ref_id']		= $rel7['ref_id'];
                                $info_sub['cdate']		= date("Y-m-d H:i:s");
                                $info_sub['user_id']		= $rel7['user_id'];
                                $info_sub['company_id']		= $rel7['company_id'];
                                $info_sub['branch_id']		= $rel7['branch_id'];
                                $info_sub['process_stock_id']		= $rel7['process_stock_id'];
                                $info_sub['p_id']		= $rel3["p_id"];
                                $info_sub['base_rate']		= $rel7['process_base_rate'];
                                $info_sub['conv_rate']		= $rel7['process_conv_rate'];
                                $info_sub['total_base_rate']		= $rel7['process_stock_base_rate'];
                                $info_sub['total_conv_rate']		= $rel7['process_stock_conv_rate'];
                                $info_sub['cloan_entry']		= 1;
                               
						        $inserid_sub=add_record('tbl_process_reserve_stock', $info_sub, $dbcon);
            		    }
        		    }
        		    echo "-new entry id= ".$inserid_sub;
		        echo "</br>";
		        $i++;
		    }
		}
?>
