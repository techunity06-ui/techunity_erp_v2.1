<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
include("../../include/function_database_query.php");

//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	//var_dump($POST);
	if(strtolower($POST['mode']) == "fetch") {
		$s_date=explode(' - ',$POST['date']);
		$userid=$_SESSION['user_id'];
		//$fol_status=$POST['fol_status'];
		$f_type=$POST['f_type'];
		
		
		$emp_id=getEmployeeIdUser($dbcon,$userid);
		$usertype=$_SESSION['user_type'];
		
		$_SESSION['start']=$s_date[0]; $_SESSION['end']=$s_date[1];
		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		 
		$where='';
		if($f_type=='')
		{
			$where.=' and followup_status in(1,2,3,4,5,6,7,8) ';
			$where.=" and complaint_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND complaint_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		}
		else
		{
			if($f_type=='2')
			{
				$where.=' and followup_status in(2,3) ';
			}
			else
			{
				$where.=' and followup_status="'.$f_type.'"';
			}
			
		}
		
		if($POST['fil_followup_status']){
			$where.=' and comp.followup_status="'.$POST['fil_followup_status'].'"';
		}
		if($POST['emp_id']){
			$where.=' and comp.emp_id="'.$POST['emp_id'].'"';
		}
		/*if($fol_status=='2')
		{
			$where.=" and followup_status=2 or followup_status=3";
		}
		else if($fol_status=='4' || $fol_status=='5')
		{
			$where.=" and followup_status='$fol_status'";
		}
		else
		{
			$where='';
		}*/
		
		
		
		$appData = array();
		$i=1;
		$aColumns = array('complaint_id', 'complaint_no', 'complaint_date','l.l_name','city.city_name','ctype.complaint_type_name','f.f_status_name','spm.spm_name','sp_part_status','emp_id', 'complaint_status','followup_status','comp.cdate','comp.user_id','l1.l_name as emp_name');
		$sIndexColumn = "complaint_id";
		
		if($usertype=='3')
		{
			$isWhere = array("complaint_status = 0 and emp_id='$emp_id' and comp.company_id in (0,$_SESSION[company_id])".$where);
		}
		else
		{
			$isWhere = array("complaint_status = 0 and comp.company_id in (0,$_SESSION[company_id])".$where);
		}
		
		$sTable = " tbl_complaint as comp";			
		$isJOIN = array('left join tbl_ledger as l on comp.cust_id=l.l_id', 'left join complaint_type_mst as ctype on ctype.complaint_type_id=comp.complaint_type_id','left join tbl_ledger as l1 on comp.emp_id=l1.l_id','left join tbl_followup_status as f on comp.followup_status=f.f_id','left join tbl_sp_part_status_mst as spm on spm.spm_id=comp.sp_part_status','left join city_mst as city on city.cityid=l.cityid');
		$hOrder = "comp.complaint_id desc";
		include('../../include/pagging.php');
		//echo $squery;
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
			//$erow=getEmployeeDetail($dbcon,$row['emp_id']);
			
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['complaint_no']; 
			$row_data[] = date('d M, Y',strtotime($row['complaint_date']));
			$row_data[] = $row['l_name']; 
			$row_data[] = $row['city_name']; 
			$row_data[] = $row['complaint_type_name']; 
			$row_data[] = $row['f_status_name'];
			$row_data[] = $row['spm_name'];
			$row_data[] = $row['emp_name']; 
			//$row_data[] = if($row['followup_status']=='2'){ getFollowupStatus($dbcon,$row['followup_status']).$erow['employee_name']; } else { getFollowupStatus($dbcon,$row['followup_status']); } 
			
			$edit_btn='';$delete_btn=''; 
			if($row['followup_status']=='1')
			{
				if($edit_btn_per){
					$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'complaint_edit/'.$row['complaint_id'].'"><i class="fa fa-pencil"></i></a>';
		
				}
				if($delete_btn_per){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_complaint('.$row['complaint_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
			}
			else
			{
				$edit_btn='';$delete_btn='';
			}
			
			//$complain_btn='<button class="btn btn-xs btn-primary" data-original-title="Add Complain Status" data-toggle="tooltip" data-placement="top" onClick="add_complain_status('.$row['complaint_id'].','.$row['followup_status'].');" id="complain_btn"><i class="fa fa-plus"></i></button>';
			
			//if($emp_id>0)
			if($_SESSION['user_type']!='2' && $_SESSION['user_type']!='5')
			{
				//add  complain Status
				
				if($row['followup_status']=='7')
				{
					$complain_btn='<a href="'.ROOT.'complaint_status/'.$row['complaint_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Complain Status" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
				}
				else
				{
					$complain_btn="";
				}
				
				//start button
				
				if($row['followup_status']=='2' || $row['followup_status']=='3')
				{
					$start_btn='<a onclick="startComplain('.$row['complaint_id'].','.$emp_id.')" class="btn btn-xs btn-primary" data-original-title="Start The Complain" data-toggle="tooltip" data-placement="top"><i class="fa fa-tags"></i></a>';
				}
				else
				{
					$start_btn="";
				}
			}
			else
			{
				if($row['followup_status']=='1' || $row['followup_status']=='6')
				{
					$complain_btn='<a href="'.ROOT.'complaint_assign/'.$row['complaint_id'].'" class="btn btn-xs btn-success" data-original-title="Assign Employee" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
				}
				else if($row['followup_status']=='5')
				{
					$complain_btn='<a href="'.ROOT.'complaint_assign/'.$row['complaint_id'].'" class="btn btn-xs btn-success" data-original-title="Re-Assign Employee" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
				}
				else
				{
					$complain_btn='';
				}
			}
			
			$view_btn='<a href="'.ROOT.'complaint_history/'.$row['complaint_id'].'" class="btn btn-xs btn-info" data-original-title="View Complaint History" data-toggle="tooltip" data-placement="top"><i class="fa fa-eye"></i></a>';
			
			
			//$view_btn='<button class="btn btn-xs btn-info" data-original-title="View Complain History" data-toggle="tooltip" data-placement="top"  id="view_btn"><i class="fa fa-eye"></i></button>';
			
			//$row_data[] = $edit_btn.' '.$delete_btn.' '.$complain_btn.' '.$not_done_btn.' '.$view_btn;
			if($_SESSION['attendance']=='yes' || $_SESSION['attendance']=='')
			{
				$row_data[] = $edit_btn.' '.$delete_btn.' '.$complain_btn.' '.$view_btn.' '.$start_btn; 
			}
			else
			{
				$row_data[] = $view_btn; 
			}
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		//echo implode(",",$POST['sp_part']);
		//Update Statr series of Lead No
		$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=1 and company_id=".$_SESSION['company_id']);
		
		//$fstatus=getFollowupStatusId($dbcon);
		$fstatus="1";
		$info['complaint_no']	= $POST['complaint_no'];
		$info['complaint_date']	= date('Y-m-d',strtotime($POST['complaint_date'])); 
		$info['cust_id']		= $POST['cust_id'];
		$info['complaint_type_id']	= $POST['complaint_type_id'];
		$info['sp_part_status']	= $POST['sp_part_status'];
		$info['followup_status']	= $fstatus;
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id']; 
		$info['company_id']		= $_SESSION['company_id'];
		//$info['sp_part']		= implode(",",$POST['sp_part']);
		$inserid=add_record('tbl_complaint', $info, $dbcon);
		 
		/*update spare parts*/
	
		
		
		/*Update Data in Trn Table Start*/
		if($inserid){
			$infotrn['complaint_id']			= $inserid;
			$infotrn['complaint_trn_status']	= 0;
			$updatetrnid=update_record('tbl_complaint_trn', $infotrn,"complaint_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
		}
		/*Update Data in Trn Table End*/
		
		
		/*Insert Data In Followup Table Start*/
		
		$infofw['fl_cid']=$inserid;
		$infofw['fl_f_status']=$fstatus;
		$infofw['fl_date']= date('Y-m-d',strtotime($POST['complaint_date']));
		$infofw['user_id']= $_SESSION['user_id'];
		
		$inseridf=add_record('tbl_follow', $infofw, $dbcon);
		
		/*Insert Data In Followup Table End*/
		
		
		/* insert if employee assign yes Start */
		
			if($POST['ass_emp']=='yes')
			{
				$infoc['fl_f_status']		= $POST['change_status'];
				$infoc['fl_e_id']		= $POST['f_emp'];
				$infoc['f_remark']		= $POST['f_remark'];
				$infoc['fl_date']			= date("Y-m-d h:i:s");
				$infoc['user_id']		= $_SESSION['user_id'];
				$infoc['old_sp_part']		= $POST['old_sp_part'];		
				$infoc['fl_cid']		= $inserid;		
				
				add_record('tbl_follow', $infoc, $dbcon);
			
			
				$infotrnc['followup_status']	= $POST['change_status'];
				$infotrnc['emp_id']	= $POST['f_emp'];
				$infotrnc['old_sp_part_status']	= strtolower($POST['old_sp_part']);
				$infotrnc['sp_part_status']	= "3";
				$updatetrnid=update_record('tbl_complaint', $infotrnc,"complaint_id=".$inserid,$dbcon);
			
			}
			/* insert if employee assign yes End */
			
			/* if assing yes spare part */
			
			if($POST['sp_part_status']=='1')
			{
			
				$sp_array=$POST['sp_part'];
			
				for($k=0;$k<count($sp_array);$k++)
				{
					$loop_id=$sp_array[$k];
					$infos['s_comp_id']=$inserid;
					$infos['s_bom_id']=$POST['bom_first_id'];
					$infos['s_cust_id']=$POST['cust_id'];
					$infos['s_user_id']=$_SESSION['user_id'];
					$infos['s_date']=date('Y-m-d',strtotime($POST['complaint_date']));
					$infos['comp_product_id']=$POST['product_first_id'];
					$infos['s_product']=$POST['sp_pid'][$loop_id];
					$infos['s_qty']=$POST['sp_qty'][$loop_id];
					$infos['s_rate']=$POST['sp_rate'][$loop_id];
					$infos['s_amount']=$POST['sp_amount'][$loop_id];
					$infos['s_courier_name']=$POST['sp_courier_name'][$loop_id];
					$infos['s_courier_no']=$POST['sp_courier_no'][$loop_id];
					$infos['s_courier_del_date']=date("Y-m-d",strtotime($POST['sp_courier_date'][$loop_id]));
					$infos['s_paid_status']=$POST['sp_free'][$loop_id];
					$infos['sp_sent_status']=$POST['sp_sent'][$loop_id];
					$infos['s_fl_status']='1';
					$infos['sp_old_status']=$POST['old_sp_sent'][$loop_id];
					
					$int2=add_record('tbl_complain_spare_part', $infos, $dbcon);
					
					service_reserve_stock($dbcon,$infos['s_product'],1,$int2,$infos['s_qty']);
					
					if($infos['sp_sent_status']=="yes"){
						service_reserve_stock($dbcon,$infos['s_product'],2,$int2,$infos['s_qty']);
					}
					
					//old spare part add
					if($POST['old_sp_sent'][$loop_id]=='yes')
					{
						$infold['sc_cust_id']		= $POST['cust_id'];
						$infold['sc_comp_id']		= $inserid;
						$infold['sc_comp_product_id']		= $POST['product_first_id'];
						$infold['sc_product']		= $POST['sp_pid'][$loop_id];
						$infold['sc_qty']		= $POST['sp_qty'][$loop_id];
						$infold['sc_rate']		= $POST['sp_rate'][$loop_id];
						$infold['sc_amount']		= $POST['sp_amount'][$loop_id];
						
						$infold['sc_user_id']			= $_SESSION['user_id'];
						$infold['sc_date']			= date("Y-m-d");
						$infold['s_return_status']			= "0";
						
						$table='tbl_complain_close_spare_part';$tableid='s_id';
						
						add_record($table, $infold, $dbcon);
					}
				}
			
			}
			/* if assing yes spare part */

			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"complaint_add",1,"tbl_complaint",$inserid);
			
			if($inserid){
				$row['res'] ="1";
			}
			else{
				$row['res'] ="0";
			}
			
			echo json_encode($row);	
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		$info['complaint_no']	= $POST['complaint_no'];
		$info['complaint_date']	= date('Y-m-d',strtotime($POST['complaint_date'])); 
		$info['cust_id']		= $POST['cust_id'];
		$info['complaint_type_id']	= $POST['complaint_type_id'];
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		
		$updateid=update_record('tbl_complaint', $info,"complaint_id=".$POST['eid'] , $dbcon);
		
		$infoc['fl_date']= date('Y-m-d',strtotime($POST['complaint_date']));
		
		$updateid1=update_record('tbl_follow', $infoc,"fl_cid=".$POST['eid']." and fl_f_status=1" , $dbcon);
		
		$dbcon->query("delete from tbl_complain_spare_part where s_comp_id='$POST[eid]'");
		
		if($POST['sp_part_status']=='1')
			{
			
				$sp_array=$POST['sp_part'];
			
				for($k=0;$k<count($sp_array);$k++)
				{
					$loop_id=$sp_array[$k];
					$infos['s_comp_id']=$POST['eid'];
					$infos['s_bom_id']=$POST['bom_first_id'];
					$infos['s_cust_id']=$POST['cust_id'];
					$infos['s_user_id']=$_SESSION['user_id'];
					$infos['s_date']=date('Y-m-d',strtotime($POST['complaint_date']));
					$infos['comp_product_id']=$POST['product_first_id'];
					$infos['s_product']=$POST['sp_pid'][$loop_id];
					$infos['s_qty']=$POST['sp_qty'][$loop_id];
					$infos['s_rate']=$POST['sp_rate'][$loop_id];
					$infos['s_amount']=$POST['sp_amount'][$loop_id];
					$infos['s_courier_name']=$POST['sp_courier_name'][$loop_id];
					$infos['s_courier_no']=$POST['sp_courier_no'][$loop_id];
					$infos['s_courier_del_date']=date("Y-m-d",strtotime($POST['sp_courier_date'][$loop_id]));
					$infos['s_paid_status']=$POST['sp_free'][$loop_id];
					$infos['sp_sent_status']=$POST['sp_sent'][$loop_id];
					$infos['s_fl_status']='1';
					
					add_record('tbl_complain_spare_part', $infos, $dbcon);
					
					//old spare part add
					if($POST['sp_free'][$loop_id]=='free')
					{
						$infold['sc_cust_id']		= $POST['cust_id'];
						$infold['sc_comp_id']		= $POST['eid'];
						$infold['sc_comp_product_id']		= $POST['product_first_id'];
						$infold['sc_product']		= $POST['sp_pid'][$loop_id];
						$infold['sc_qty']		= $POST['sp_qty'][$loop_id];
						$infold['sc_rate']		= $POST['sp_rate'][$loop_id];
						$infold['sc_amount']		= $POST['sp_amount'][$loop_id];
						
						$infold['sc_user_id']			= $_SESSION['user_id'];
						$infold['sc_date']			= date("Y-m-d");
						$infold['s_return_status']			= "0";
						
						$table='tbl_complain_close_spare_part';$tableid='s_id';
						
						add_record($table, $infold, $dbcon);
					}
				}
			
			}
	
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"complaint_add",2,"tbl_complaint",$POST['eid']);
		
		if($updateid){
			$row['res']='update';
		}
		else{
			$row['res']='0';
		}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['complaint_status']= 2;
		$info['cdate']			= date("Y-m-d H:i:s");
		$updateid=update_record('tbl_complaint', $info,"complaint_id=".$POST['eid'] , $dbcon);	
	
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"complaint_add",3,"tbl_complaint",$POST['eid']);
		
		if($updateid)
			echo "1";	
		else
			echo "0";			
	} 	
	else if(strtolower($POST['mode']) == "fieldadd") {
		$info1['product_id']		= $POST['product_id'];
		$info1['model_id']			= $POST['model_id'];
		$info1['comp_pro_sts']		= $POST['comp_pro_sts'];
		$info1['comp_amount']		= $POST['comp_amount'];
		$info1['user_id']			= $_SESSION['user_id'];
		
		$table='tbl_complaint_trn';$tableid='complaint_trn_id';
		if(!empty($POST['complaint_id'])) {
			$info1['complaint_id']= $POST['complaint_id'];
		}
		else{
			$info1['complaint_trn_status']= 3;
		}
		
		if(empty($POST['edit_id'])) {
			$inserid=add_record($table, $info1, $dbcon);
		}
		else {
			$updateid=update_record($table, $info1, $tableid."=".$POST['edit_id'], $dbcon);	
		}
		
				
	}
	else if(strtolower($POST['mode']) == "get_fist_bom") {
		
		$comp_id=$POST['comp_id'];
		$res['bom']=get_fist_bom($dbcon,$comp_id);
		$res['comp']=get_fist_comp_product($dbcon,$comp_id);
		
		echo json_encode($res);
	}
	else if(strtolower($POST['mode']) == "refresh_complaint_data") {
		
		$dbcon->query("delete from tbl_complaint_trn where complaint_id='0' and user_id='$_SESSION[user_id]'");
	}
	else if(strtolower($POST['mode']) == "load_product_trn_data") {
				
		if($POST['complaint_id']){
			$query="select trn.*,pro.product_name from tbl_complaint_trn as trn
			left join product_mst as pro on pro.product_id=trn.product_id 
			left join model_mst as model on model.model_id=trn.model_id
			where complaint_trn_status=0 and trn.complaint_id=".$POST['complaint_id'];
		}
		else{
			
			$query="select trn.*,pro.product_name from tbl_complaint_trn as trn
			left join product_mst as pro on pro.product_id=trn.product_id 
			left join model_mst as model on model.model_id=trn.model_id  
			where complaint_trn_status=3 and trn.user_id=".$_SESSION['user_id'];
		}
		$result=$dbcon->query($query);
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{	 
				echo '<tr>
					<td style="vertical-align:top;">
						'.$rel['product_name'].'
					</td>
				
					<td style="vertical-align:top;text-align:center;">
						'.(get_comp_pay_sts_name($dbcon,$rel['comp_pro_sts'])).'
					</td> 
					
					<td>
						'.$rel['comp_amount'].'
					</td>
					
					<td>
						<input type="hidden" id="cntrow'.$i.'" name="cntrow[]" value="'.$i.'">
						<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['complaint_trn_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button> 
						<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['complaint_trn_id'].');" id="fieldremove'.$i.'">X</button>
					</td>	
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
	}
	else if(strtolower($POST['mode'])== "preedit") {
		$q = $dbcon -> query("SELECT trn.* FROM tbl_complaint_trn as trn WHERE complaint_trn_id= '$POST[complaint_trn_id]'");
		$r = mysqli_fetch_assoc($q);
		$r['pro_resp_html'] = load_cust_sold_pro($dbcon,$r['product_id'],$POST['cust_id']);
		$r['model_resp_html'] = load_cust_prowise_model($dbcon,$r['model_id'],$r['product_id'],$POST['cust_id']);
		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_data") {
		$row=array();
		$info['complaint_trn_status']=2;	
		$updateid=update_record("tbl_complaint_trn", $info, "complaint_trn_id=".$POST['complaint_trn_id'], $dbcon);

		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "load_complaint_no")
	{
		$row=array();
		$query1="select * from tbl_invoicetype where status=0 and type_id=1 and company_id=".$_SESSION['company_id'];
		$rows=mysqli_fetch_assoc($dbcon->query($query1));
		$id=$rows['taxinvoice_start'];
		$id=$id+1;
		//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
		//$end = $start+1;
		if($rows['invoice_format']=='2'){
			$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
		}
		else if($rows['invoice_format']=='1'){
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
	else if(strtolower($POST['mode'])== "load_model_service_status"){
		$get_qry="select sold_inv_foc_date from tbl_cust_sold_pro where cust_id=".$POST['cust_id']." and product_id=".$POST['product_id']." and cust_sold_pro_status=0";
		$get_rel=mysqli_fetch_assoc($dbcon->query($get_qry));
		$ser_end_dt=date("Y-m-d", strtotime(date("Y-m-d", strtotime($get_rel['sold_inv_foc_date'])) . " + 1 year"));
		//$ser_end_dt=strtotime($get_rel['sold_inv_foc_date']);
		$comp_dt=date('Y-m-d',strtotime($POST['complaint_date']));
		//$comp_dt=strtotime($comp_dt);
		/*var_dump($get_rel['sold_inv_foc_date']);
		var_dump(date('Y-m-d',strtotime($POST['complaint_date'])));*/
		if($comp_dt>$ser_end_dt){
			$res['ser_sts']='2';
		}
		else{
			$res['ser_sts']='1';
		}
		//$res['ser_end_dt']=$comp_dt;
		echo json_encode($res);
	}
	else if(strtolower($POST['mode'])== "load_cust_sold_pro") {
		$resp['pro_resp_html'] = load_cust_sold_pro($dbcon,'',$POST['cust_id']);
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "load_cust_prowise_model") {
		$resp['model_resp_html'] = load_cust_prowise_model($dbcon,'',$POST['product_id'],$POST['cust_id']);
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "load_cust_prowise_model_invoice") {
		$resp['model_resp_html'] = load_cust_prowise_model_invoice($dbcon,'',$POST['product_id'],$POST['cust_id']);
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "load_prowise_model") {
		$resp['model_resp_html'] = load_prowise_model($dbcon,'',$POST['product_id'],$POST['cust_id']);
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "add_complain_status") {
		if($POST['change_status']=='8'){
			$infctrn['complaint_id']		= $POST['comp_id_hid'];
			$infctrn['rmrk_trn_date']		= date("Y-m-d H:i:s");
			$infctrn['rmrk_trn_remark']	= $_POST['f_remark'];
			$infctrn['user_id']			= $_SESSION['user_id']; 
			$inserid=add_record('tbl_comp_flp_rmrk_trn', $infctrn, $dbcon);
		}
		else{
				
			$info['fl_cid']	= $POST['comp_id_hid'];
			$info['fl_f_status']		= $POST['change_status'];
			$info['fl_e_id']		= $POST['f_emp'];
			$info['f_remark']		= $POST['f_remark'];
			$info['fl_date']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id']; 
			
			$inserid=add_record('tbl_follow', $info, $dbcon);
			 
			//Insert Data Into Remark Trn
			$infctrn['fl_id']			= $inserid;
			$infctrn['complaint_id']	= $POST['comp_id_hid'];
			$infctrn['rmrk_trn_date']	= date("Y-m-d H:i:s");
			$infctrn['rmrk_trn_remark']	= $_POST['f_remark'];
			$infctrn['user_id']			= $_SESSION['user_id']; 
			$inserctrnid=add_record('tbl_comp_flp_rmrk_trn', $infctrn, $dbcon);
			 
			/*Update Data in Complaint Table Start*/

			$infotrn['followup_status']	= $POST['change_status'];
			
			$infotrn['emp_id']	= $POST['f_emp'];
			
			//$infotrn['sp_part_status']	= $POST['n_spart'];
			if($POST['change_status']=='4')
			{
				$infotrn['sp_part_status']	= $POST['sp_part_close_status'];
				
				$infotrn1['close_status']	= "1";
				
				update_record('tbl_complaint_trn', $infotrn1,"complaint_id=".$POST['comp_id_hid'],$dbcon);
			}
			else
			{
				$infotrn['sp_part_status']=$POST['n_spart'];
			}
			
			$updatetrnid=update_record('tbl_complaint', $infotrn,"complaint_id=".$POST['comp_id_hid'] ,$dbcon);
			
			
			/*Update Data in Complaint Table End*/
			
			/*Close Action Entry Start*/
			
			if($POST['change_status']=='4')
			{
				$infocl['cl_comp_id']	= $POST['comp_id_hid'];
				$infocl['cl_cust_id	']	= $POST['cust_id_hid'];
				$infocl['cl_date']	= date("Y-m-d H:i:s");
				$infocl['cl_service_charge']	= $POST['service_charge'];
				$infocl['cl_amount']	= $POST['c_amount'];
				$infocl['cl_user_id']	= $_SESSION['user_id'];
				$infocl['cl_emp_id']	= $POST['f_emp'];
				
				$insercloseid=add_record('tbl_complain_close_detail', $infocl, $dbcon);
			}
			
			if($POST['change_status']=='4' || $POST['change_status']=='5')
			{
				$infofo['s_fl_status']	= $POST['change_status'];
				$where="";
				$where.=" and s_fl_status=0";
				$updatetrnid1=update_record('tbl_complain_spare_part', $infofo,"s_comp_id=".$POST['comp_id_hid'].$where,$dbcon);
			}
			
			/*Close Action Entry End*/
			
			/* image entry */
				
				
				$test = explode('.', $_FILES["file"]["name"]);
				$ext = end($test);
				$name = rand(100, 999).$POST['comp_id_hid'].'.' . $ext;
				$path='../../view/upload/complaint_img/';
				$location = $path . $name;  
				move_uploaded_file($_FILES["file"]["tmp_name"], $location);
				
				$info_image['ci_comp_id']	=$POST['comp_id_hid'];
				$info_image['ci_image']		=$name;
				$info_image['cdate']        =date("Y-m-d H:i:s");
				$info_image['user_id']		= $_SESSION['user_id'];
				$info_image['company_id']	= $_SESSION['company_id'];
			
				$inserimgid=add_record('tbl_complaint_image', $info_image, $dbcon);
				
			/* image entry close */
			
			
			/*payment transaction entry start */
				
				$payment_status=$POST['pay_status'];
				
				if($payment_status=='1')
				{
					$p_status=0;
					$used_amount=$POST['c_amount'];
				}
				else
				{
					$p_status=1;
					$used_amount=0;
				}
				
				if($payment_status=='0')
				{
					$infopa['partyid']			= $POST['cust_id_hid'];
					$infopa['accountid']		= $POST['accountid'];
					$infopa['payment_date']		=date("Y-m-d H:i:s");
					$infopa['payment_mode']		= $POST['service_charge'];
					$infopa['amount']			= $POST['c_amount'];
					$infopa['emp_id']			= $POST['f_emp'];
					$infopa['mst_status']		= $p_status;
					$infopa['referenceno']		= 'complain amount';
					$infopa['used_amount']		= $used_amount;
					$infopa['credits']			= '';
					$infopa['tax_deducted_flag']	= '';
					$infopa['notes']				= '';
					$infopa['typcomp_id_hid']				= 2;//credit
					$infopa['cdate']				= date("Y-m-d H:i:s");
					$infopa['user_id']			= $_SESSION['user_id'];
					$infopa['company_id']			= $_SESSION['company_id'];
					$infopa['comp_id']			= $POST['comp_id_hid'];
					
					$arr=get_serise_common($dbcon,'4');
					$receiptid=$arr['paymentno'];
					
					$infopa['paymentno']= $receiptid;//paymentno($dbcon,$paymentno,$invoicetype=8);
					$infoptrn['payment_mstid']=$paymentid=add_record("payment_mst",$infopa,$dbcon);
			  
					
					$infoptrn['bill_id']		= $POST['comp_id_hid'];
					$infoptrn['bill_type']	= 'complaint';
					$infoptrn['paid_amount']	= $POST['c_amount'];
					$infoptrn['total_amount']= $POST['c_amount'];
					$infoptrn['pay_status']= $p_status;
					$infoptrn['emp_id']			= $POST['f_emp'];
					$insertidptrn=add_record("payment_trn",$infoptrn,$dbcon);
				}
				else
				{
					$infoptrn['bill_id']		= $POST['comp_id_hid'];
					$infoptrn['bill_type']	= 'complaint';
					$infoptrn['paid_amount']	= $POST['c_amount'];
					$infoptrn['emp_id']= $POST['f_emp'];
					$infoptrn['pay_date']= date("Y-m-d H:i:s");
					$infoptrn['pay_mode']			= $POST['service_charge'];
					$insertidptrn=add_record("complain_payment_trn",$infoptrn,$dbcon);
					
					$paid_payment=get_complain_payment_pending($dbcon,$POST['comp_id_hid']);
					
					if($paid_payment<=0)
					{
					
						$q = $dbcon -> query("update payment_mst set mst_status='0' where comp_id='".$POST['comp_id_hid']."'");
						$r = $dbcon -> query("update payment_trn set pay_status='0' where bill_id='".$POST['comp_id_hid']."'");
						$s = $dbcon -> query("update tbl_complaint set pay_status='1' where complaint_id='".$POST['comp_id_hid']."'");
					}
				}
				
				
			/*payment transaction entry end */
		}
		
		
		if($inserid){
			$row['res'] ="1";
		}
		else{
			$row['res'] ="0";
		}
		
		echo json_encode($row);	
	}
	else if(strtolower($POST['mode'])== "get_complain_data") {
		
		$fstat=$_POST['fstat'];
		
		$html='';

		$html.='<select class="form-control" name="f_action" id="change_status" onchange="ShowEmployee(this.value)">';
		$html.="<option value=''>--Select Status--</option>";
		
		$userid=$_SESSION['user_id'];
		$emp_id=getEmployeeIdUser($dbcon,$userid);
		
		if($emp_id>0)
		{
			
			if($fstat=='2')
			{
				$query="select * from tbl_followup_status where f_status=0 and f_id='4' or f_id='5'";
			}
			if($fstat=='3')
			{
				$query="select * from tbl_followup_status where f_status=0 and f_id='4'  or f_id='5'";
			}
		}
		else
		{
			if($fstat=='1')
			{
				$query="select * from tbl_followup_status where f_status=0 and f_id='2' or f_id='4'";
			}
			else if($fstat=='2')
			{
				$query="select * from tbl_followup_status where f_status=0 and f_id='3' or f_id='4' or f_id='5'";
			}
			else if($fstat=='3')
			{
				$query="select * from tbl_followup_status where f_status=0 and f_id='4'";
			}
			else if($fstat=='5')
			{
				$query="select * from tbl_followup_status where f_status=0 and f_id='4' or f_id='3'";
			}
			
		}	
			$rs_cust=$dbcon->query($query);	
			while($rel=mysqli_fetch_assoc($rs_cust))
			{	
				$sel='';
				if($rel['f_id']==$id) {
					$sel="selected='selected'";
				}
				
				$html.='<option '.$sel.' value="'.$rel['f_id'].'">'.$rel['f_status_name'].'</option>';
			}
		
		$html.="</select>";
		
		
		echo $html;
	}
	else if(strtolower($POST['mode']) == "show_complain_history") {
		
		$complain_id=$POST['complain_id'];
		
		$_SESSION['start']=$s_date[0]; $_SESSION['end']=$s_date[1];
 
		
		$appData = array();
		$i=1;
		$aColumns = array('f.fl_id','f.fl_cid', 'f.fl_f_status', 'f.fl_date','f.f_remark','f.fl_e_id','e.employee_name','fl.f_status_name');
		$sIndexColumn = "f.fl_id";
		
		$isWhere = array("f.fl_cid=$complain_id");
		
		$sTable = " tbl_follow as f";		
		
		$isJOIN = array('left join employee_mst as e on f.fl_e_id=e.employee_id','left join tbl_followup_status as fl on f.fl_f_status=fl.f_id');
		
		$hOrder = "f.fl_id";
		
		include('../../include/pagging.php');
		//echo $squery;
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
			//$erow=getEmployeeDetail($dbcon,$row['emp_id']);
			
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = date('d M, Y',strtotime($row['fl_date']));
			$row_data[] = $row['f_status_name'];
			$row_data[] = $row['employee_name']; 
			$row_data[] = $row['f_remark']; 
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "show_complain_view") {
		
		$complain_id=$POST['complain_id'];
				
		$_SESSION['start']=$s_date[0]; $_SESSION['end']=$s_date[1];
		
		
		$appData = array();
		$i=1;
		$aColumns = array('complaint_id', 'complaint_no', 'complaint_date','emp_id', 'cust.company_name', 'ctype.complaint_type_name', 'complaint_status','followup_status','comp.cdate','comp.user_id','e.employee_name','f.f_status_name');
		$sIndexColumn = "complaint_id";
		
		$isWhere = array("comp.complaint_id=$complain_id");
		
		$sTable = " tbl_complaint as comp";			
		$isJOIN = array('left join tbl_customer cust on comp.cust_id=cust.cust_id', 'left join complaint_type_mst as ctype on ctype.complaint_type_id=comp.complaint_type_id','left join employee_mst as e on comp.emp_id=e.employee_id','left join tbl_followup_status as f on comp.followup_status=f.f_id');
		$hOrder = "comp.complaint_id desc";
		include('../../include/pagging.php');
		//echo $squery;
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
			//$erow=getEmployeeDetail($dbcon,$row['emp_id']);
			
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['complaint_no']; 
			$row_data[] = date('d M, Y',strtotime($row['complaint_date']));
			$row_data[] = $row['company_name']; 
			$row_data[] = $row['complaint_type_name']; 
			$row_data[] = $row['f_status_name'];
			$row_data[] = $row['employee_name']; 
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode'])== "check_customer_status") {
		$q = $dbcon -> query("SELECT cust_id,cust_block_status FROM tbl_customer WHERE cust_id= '$POST[cust_id]'");
		$r = mysqli_fetch_assoc($q);
		$status=$r['cust_block_status'];
		echo $status;
	}
	else if(strtolower($POST['mode'])== "get_total_payment") {
		
		echo get_complain_payment_pending($dbcon,$POST['comp_id']);
	}
	else if(strtolower($POST['mode'])== "check_complian_due") {
		
		$q=$dbcon->query("select pay_status from tbl_complaint where cust_id='$POST[cust_id]' and pay_status=0");
		$count=mysqli_num_rows($q);
		echo $count;
	}
	else if(strtolower($POST['mode'])== "start_complaint") {
		
		$comp_id=$POST['complaint_id'];
		$emp_id=$POST['employee_id'];
		$f_type=$POST['f_type'];
		
		//Get Pending Complaint Query
		$p_qry="select comp.complaint_id from tbl_complaint as comp
		where comp.complaint_status = 0 and comp.followup_status in(7) and comp.emp_id='$emp_id' and comp.company_id in (0,$_SESSION[company_id])";
		$p_qry_rs=$dbcon->query($p_qry);
		$p_qry_num=mysqli_num_rows($p_qry_rs);
		if($p_qry_num>0){//Dont allow to assign if already started one
			echo "0";
		}
		else{
			$q = $dbcon -> query("update tbl_complaint set followup_status='7' where complaint_id='$comp_id'");
			
			$info['fl_cid']	= $comp_id;
			$info['fl_f_status'] = "7";
			$info['fl_e_id'] = $emp_id;
			$info['fl_date'] = date("Y-m-d H:i:s");
			$info['user_id'] = $_SESSION['user_id']; 
			$inserid=add_record('tbl_follow', $info, $dbcon);
			echo "1";
		}
		
	}
	else if(strtolower($POST['mode'])== "generate_report") {
		
		$product_id=$POST['product_id'];
		
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));			
		$set1="select * from product_mst where product_id='$product_id'";
		$row1=mysqli_fetch_assoc($dbcon->query($set1));
		$str .='
				<table  width="100%"   class="display table table-bordered table-striped">
				</table>
			  <table  class="display table table-bordered table-striped" id="data_list">
			  <tr id="logo" class="logo" style="display:none">
					<td colspan="8" style="text-align:center;">
						<strong>'.$set_head['company_name'].'</strong>
					</td>
				</tr>
				<tr>
					<td colspan="2"><strong>Product Wise Complain Report </strong></td>
					<td colspan="2" style="text-align:center"><strong>Product Name :'.$row1['product_name'].'<strong></td>
					<td style="text-align:right"></td>
			
				</tr>
				
			  <tr>
				  <th width="5%" style="text-align:center">Sr. NO.</th>
				  <th width="12%" style="text-align:center">Complain No</th>
				  <th width="47%" style="text-align:center">Description</th>
				  <th width="12%" style="text-align:center">Spare Part Detail</th>
				  <th width="12%" style="text-align:center">Qty</th>
			  </tr>
			 <tbody>';
			 $query="select cs.s_comp_id,cs.comp_product_id,cs.s_date,cs.s_product,cs.s_qty,cs.s_courier_name,cs.s_courier_no,cs.s_courier_del_date,p.product_name,c.complaint_no from tbl_complain_spare_part as cs inner join product_mst as p on p.product_id=cs.s_product inner join tbl_complaint as c on c.complaint_id=cs.s_comp_id where cs.comp_product_id=".$POST['product_id']." order by cs.s_date desc";
			 $row=$dbcon->query($query);
			 if(mysqli_num_rows($row))
			 {
				$cnt=1;
				while($rel=mysqli_fetch_assoc($row))
				{
					$str .='<tr>
						<td style="text-align:center">'.$cnt.'</td>
						<td style="text-align:center">'.$rel['complaint_no'].'</td> 
						<td style="text-align:center">';
					if($rel['s_courier_name']!='')
					{
						$str .='Courier Name : '.$rel['s_courier_name'].', Courier No : '.$rel['s_courier_no'].', Courier Date : '.date("d/m/Y",strtotime($rel['s_courier_del_date']));
					}
						$str.='</td>
						<td style="text-align:center">'.$rel['product_name'].'</td>
						<td style="text-align:center">'.$rel['s_qty'].'</td>
					</tr>';
					
					$cnt++;
				}
			 }
			 else
			 {
				 $str .='<tr>
						<td style="text-align:center;color:red" colspan="4">No Data Found</td>
					</tr>';
			 }
			$str .='</tbody>				 
			  </table>';
			 
			 echo $str;
	}
	else if(strtolower($POST['mode'])== "get_product_tree") {
		
		$q="select * from tbl_complaint_trn where complaint_id='0' and user_id='$_SESSION[user_id]'";
		$sql1=$dbcon->query($q);
		if(mysqli_num_rows($sql1)>0)
		{
			$row=mysqli_fetch_assoc($sql1);
			
			$parentKey = $row['product_id'];
			$bomid=get_bom_id($dbcon,$parentKey);
			$sql = "SELECT * FROM tbl_bomtrn where bom_id='$bomid'";
		  
			$result = $dbcon->query($sql);
		   
			  if(mysqli_num_rows($result) > 0)
			  {
				  //$data=["id"=>"1","name"=>"No Members present in list","text"=>"No Members is present in list","nodes"=>[]];
				  $data = membersTree($dbcon,$parentKey,$bomid);
			  }else{
				  $data=["id"=>"0","name"=>"No Members present in list","text"=>"No Members is present in list","nodes"=>[]];
			  }
		   
			  
			  echo json_encode(array_values($data));
		}
		
		 //echo mysqli_num_rows($result);
		// echo $data;
		
	}
	else if(strtolower($POST['mode'])== "get_complaint_tree") {
		
		$bom=$POST['bom_id'];
		$product=$POST['product'];
		$eid=$POST['eid'];
		
		$getParentNodes = "select * from tbl_bomtrn where bom_id='$bom' and po_visible_status=0 and parent_id='0'";
		
		$resParentNodes = $dbcon->query($getParentNodes);
		$response = '';
		if(mysqli_num_rows($resParentNodes) > 0)
		{
			echo "<thead>
				
				<tr>
					<td>#</td>
					<td>Name</td>
					<td>Use</td>
					<td>Free / Paid</td>
					<td>Qty</td>
					<td>Rate</td>
					<td>amount</td>
					<td>Courier Name</td>
					<td>Courier No</td>
					<td>Courier Date</td>
					<td>Spare Part Sent</td>
					<td>Old Spare Part</td>
				</tr>
				
			</thead>";
			$cnt=1;$counter_tree = 0;
			while($parentNode = mysqli_fetch_assoc($resParentNodes))
			{
				
				$number="1.".$cnt;
				echo '<tr>';
				
				 get_tree_complain($dbcon,$parentNode['product_id'],$parentNode['parent_id'],0,$cnt,$bom,$number,$eid,$parentNode['bom_trn_id']);
				 
				echo '</tr>';
				$cnt++;$counter_tree++;
			}
		}
	}
	else if(strtolower($POST['mode'])== "update_spare_part") {
		
		$c_name=$POST['c_name'];
		$c_no=$POST['c_no'];
		$c_date=$POST['c_date'];
		$s_id=$POST['s_id'];
		$c_type=$POST['c_type'];
		$c_remark=$POST['c_remark'];
		
		$info['s_courier_name']	= $c_name;
		$info['s_courier_no'] 	= $c_no;
		$info['s_courier_del_date'] = date("Y-m-d",strtotime($c_date));
		$info['sp_sent_status'] = "yes";
		$info['c_type'] = $c_type;
		$info['c_remark'] = $c_remark;
		
		$updatetrnid1=update_record('tbl_complain_spare_part', $info,"s_id=".$s_id,$dbcon);
		
		$query="select * from tbl_complain_spare_part where s_id=".$s_id;
	$result=$dbcon->query($query);
	$row=mysqli_fetch_assoc($result);
		service_reserve_stock($dbcon,$row['s_product'],2,$s_id,$row['s_qty']);
		
	}
	else if(strtolower($POST['mode'])== "load_ledger_detail") {
		
		$lid=$POST['lid'];
		
		$sel=$dbcon->query("select m_address,cust_mobile from tbl_ledger where l_id='$lid'");
		$row=mysqli_fetch_array($sel);
		
		$res['address']=$row['m_address'];
		$res['mobile']=$row['cust_mobile'];
		
		echo json_encode($res);
	}
	else if(strtolower($POST['mode'])== "update_spare_part_old") {
		
		$sc_name=$POST['sc_name'];
		$sc_no=$POST['sc_no'];
		$sc_date=$POST['sc_date'];
		$sc_id=$POST['sc_id'];
		$c_type=$POST['c_type1'];
		$c_remark=$POST['c_remark1'];
		
		$info['courier_name']	= $sc_name;
		$info['courier_no'] 	= $sc_no;
		$info['courier_del_date'] = date("Y-m-d",strtotime($sc_date));
		$info['s_return_status'] = "1";
		$info['c_type'] = $c_type;
		$info['c_remark'] = $c_remark;
		
		$updatetrnid1=update_record('tbl_complain_close_spare_part', $info,"s_id=".$sc_id,$dbcon);
		
	}
	else if(strtolower($POST['mode'])== "get_bom_product") {
		
		$product=$POST['com_product'];
		
		$getParentNodes = "select * from tbl_bomtrn where sale_product_id='$product'";
		$resParentNodes = $dbcon->query($getParentNodes);
		$response = '';
		$response.='<select>';
		$response.=build_category_tree($dbcon,$product,0);
		$response.='</select>';
		echo $response;
	}
	  
?>