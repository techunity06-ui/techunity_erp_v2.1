<?php

session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions/common_functions.php");
							
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

			$ledger_id = $POST['vender_id'];
			$sql = "SELECT `l`.`employee_id` as employee_id, `u`.`user_id` as loggin_id FROM `tbl_ledger` as l
					LEFT JOIN users as u ON  `l`.`l_id`= `u`.`employee_id`
					WHERE `l`.`l_id`='".$ledger_id."' AND `l`.`company_id` = '".$_SESSION['company_id']."'";

			$result=$dbcon->query($sql);
			$row=mysqli_fetch_assoc($result);		

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$info['resource_name'] = $POST['resource_name'];
			$info['working_hours'] = $POST['working_hours'];
			$info['hours_cost'] = $POST['hours_cost'];
			$info['resource_value'] = $POST['resource_value'];
			$info['maintance_period'] = $POST['maintance_period'];
			$info['ledger_id'] = $ledger_id;
			$info['loggin_id'] = $row['loggin_id'];
			$info['employee_id'] = $row['employee_id'];
			$info['shift_type'] = $POST['shift_type'];
			$info['remark'] = $POST['remark'];
			$info['cdate'] = date('Y-m-d');
			$info['resource_status'] = 0;
			$info['user_id'] = $_SESSION['user_id'];
			$info['company_id'] = $_SESSION['company_id'];
			
			//echo "<pre>"; print_r($info);

			$insertid = add_record('tbl_resource', $info, $dbcon, $branch_id);
			if($insertid) {
				$arr['msg'] = '1';
			}else{
				$arr['msg'] = '0';
			}
			echo json_encode($arr);

		}
		else if(strtolower($POST['mode']) == "edit") {

			$ledger_id = $POST['vender_id'];
			$eid = $POST['eid'];
			$sql = "SELECT `l`.`employee_id` as employee_id, `u`.`user_id` as loggin_id FROM `tbl_ledger` as l
					LEFT JOIN users as u ON  `l`.`l_id`= `u`.`employee_id`
					WHERE `l`.`l_id`='".$ledger_id."'  AND `l`.`company_id` = '".$_SESSION['company_id']."'";

			$result=$dbcon->query($sql);
			$row=mysqli_fetch_assoc($result);	

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];	

			$info['resource_name'] = $POST['resource_name'];
			$info['working_hours'] = $POST['working_hours'];
			$info['hours_cost'] = $POST['hours_cost'];
			$info['resource_value'] = $POST['resource_value'];
			$info['maintance_period'] = $POST['maintance_period'];
			$info['ledger_id'] = $ledger_id;
			$info['loggin_id'] = $row['loggin_id'];
			$info['employee_id'] = $row['employee_id'];
			$info['shift_type'] = $POST['shift_type'];
			$info['remark'] = $POST['remark'];
			$info['mdate'] = date('Y-m-d');
			$info['resource_status'] = 0;
			$info['muser_id'] = $_SESSION['user_id'];
			$info['company_id'] = $_SESSION['company_id'];
			
			//echo "<pre>"; print_r($info); die;

			$updateid = update_record('tbl_resource', $info,"resource_id = ".$eid , $dbcon, $branch_id);
			if($updateid) {
				$arr['msg'] = 'update';
			}else{
				$arr['msg'] = '0';
			}
			echo json_encode($arr);
		}		
		
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=".$POST['type_id']." and company_id=".$_SESSION['company_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		

		else if(strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
		
		else if(strtolower($POST['mode'])== "get_po_login")
		{
			$id = $POST['id']; // as table id
			$sql = "SELECT `u`.`user_name` as prepared_by,`po`.`cdate`, `mu`.`user_name` as last_modify_by, `po`.`mdate`, `po`.`resource_status` as stage FROM `tbl_resource` as po left join `users` as u ON  `po`.`user_id` = `u`.`user_id` left join `users` as mu ON  `po`.`muser_id` = `mu`.`user_id`  Where `po`.`resource_id`='".$id."' and `po`.`company_id`='".$_SESSION['company_id']."'";
			
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
			$vendor_id = $POST['vendor_id'];
			$branch_id = $POST['branch_id'];

			$sql = "SELECT res.* FROM `tbl_resource` as res 
    		WHERE `res`.`resource_id`='".$id."' AND `res`.`branch_id`='".$branch_id."' AND `res`.`company_id`='".$_SESSION['company_id']."'";
			$rel=$dbcon->query($sql);
			$result=mysqli_fetch_assoc($rel);
			
			$arr['resource_name'] = $result['resource_name'];
			$arr['working_hours'] =  $result['working_hours'];
			$arr['hours_cost'] = $result['hours_cost'];
			$arr['resource_value'] = $result['resource_value'];
			$arr['maintance_period'] = $result['maintance_period'];
			$arr['branch_id'] = get_branch_name_company($dbcon, $result['branch_id']);
			$arr['vender_id'] = getsalaryemployee($dbcon,$result['ledger_id'], $branch_id);
			$arr['remark'] = $result['remark'];
			$arr['shift_type'] = get_shift_type($dbcon,$result['shift_type']);
			//echo "<pre>"; print_r($arr);
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode'])== "fetch_employee_based_on_branch")
		{
			$branch_id = $POST['branch_id'];
			$data['vendor_id'] = getsalaryemployee($dbcon,'',$branch_id);
			echo json_encode($data);
		}	

    }
}




?>