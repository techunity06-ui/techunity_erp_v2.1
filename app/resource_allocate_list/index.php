<?php

session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
		}

		else if(strtolower($POST['mode']) == "add") {
		}

		else if(strtolower($POST['mode']) == "edit") {
			
			$eid = $POST['eid'];
			$info['resource_id'] = $POST['resource_id'];
			$info['muser_id'] = $_SESSION['user_id'];
			$info['mdate'] = date('Y-m-d H:i:s');

			$updateid = update_record('tbl_work_order_resource_allocate', $info,"resource_allocate_id = ".$eid , $dbcon);
			if($updateid) {
				$arr['msg'] = 'update';
			}else{
				$arr['msg'] = '0';
			}
			echo json_encode($arr);
		}		
		
		else if(strtolower($POST['mode'])== "get_po_login")
		{
			$id = $POST['id']; // as table id
			$sql = "SELECT `u`.`user_name` as prepared_by,`po`.`cdate`, `mu`.`user_name` as last_modify_by, `po`.`mdate`, `po`.`resourse_allocation_status` as stage FROM `tbl_work_order_resource_allocate` as po left join `users` as u ON  `po`.`user_id` = `u`.`user_id` left join `users` as mu ON  `po`.`muser_id` = `mu`.`user_id`  Where `po`.`resource_allocate_id`='".$id."' and `po`.`company_id`='".$_SESSION['company_id']."'";
			
			$vrow=$dbcon->query($sql);
			$rel=mysqli_fetch_assoc($vrow);
				
			if($rel['stage']=='0'){
			 	$stage = 'Approved';
			 }else{
			 	$stage = 'Pending';
			 }
					
			echo '<section class="panel">
                     <div class="panel-body bio-graph-info">
                         <h1>Login History</h1>
                         <div class="row">
                             <div class="bio-row">
                                 <p><span>Prepared By </span>: '.$rel["prepared_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Prepared Date </span>: '.(($rel["cdate"]!='' && $rel['cdate']!="1970-01-01" && $rel['cdate']!="0000-00-00")?date('d-M-Y',strtotime($rel["cdate"])):'').'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified By </span>: '.$rel["last_modify_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified Date</span>: '.(($rel["mdate"]!='' && $rel['mdate']!="1970-01-01" && $rel['mdate']!="0000-00-00")?date('d-M-Y',strtotime($rel["mdate"])):'').'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved By </span>: NA</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved Date </span>: NA</p>
                             </div>
                             <div class="bio-row">
                                 <p><span> Stage </span>: '.$stage.'</p>
                             </div>
                             
                         </div>
                     </div>
                 </section>';
		}

		else if(strtolower($POST['mode'])== "get_item_selected_information")
		{
			$id = $POST['id'];
			$sql = "SELECT wra.*,`p`.`product_name`,`r`.`resource_name`, `rp`.`sp_id`, (SELECT po_req_no FROM tbl_set_main_process WHERE sp_id=`rp`.`sp_id`) as work_order_no  
				FROM `tbl_work_order_resource_allocate` as wra 
			    LEFT JOIN product_mst as p ON `wra`.`product_id` = `p`.`product_id`
			    LEFT JOIN tbl_resource as r ON `wra`.`resource_id` = `r`.`resource_id`
			    LEFT JOIN tbl_request_product as rp ON `wra`.`request_id` = `rp`.`rp_id`
			    WHERE `wra`.`resource_allocate_id`='".$id."' AND `wra`.`resourse_allocation_status`=0 AND `wra`.`user_id`='".$_SESSION['user_id']."' AND `wra`.`company_id`='".$_SESSION['company_id']."' ";
			$rel=$dbcon->query($sql);
			$result=mysqli_fetch_assoc($rel);
			
			$arr['product_name'] = $result['product_name'];
			$arr['resource_id'] =  get_all_resource($dbcon,$result['resource_id']);
			$arr['work_order_no'] = $result['work_order_no'];
			$arr['qty'] = $result['qty'];
			$arr['time_per_qty'] = $result['time_per_qty'];
			$arr['total_time'] = $result['total_time'];
			
			echo json_encode($arr);
		}

    }
}

?>