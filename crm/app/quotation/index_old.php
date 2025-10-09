<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//include_once("../common_send_email.php");
//include("../../view/export/direct_email_quot.php");

//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	if(strtolower($POST['mode']) == "fetch") {
		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$where='';
		$where.="  and quot.quotation_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND quot.quotation_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$user_id=$_SESSION['user_id'];
		$fis=check_crm_find_in_set($dbcon,$user_id,1);
		$where.=' and tsk.assign_user_ids in ('.$fis.')';
		
		$appData = array();
		$i=1;
		$aColumns = array('quot.quotation_id', 'quot.quotation_no','city.city_name', 'quot.quotation_date', 'cust.cust_name', 'inq.inquiry_no', 'quot.quot_subject', 'usr.user_name', 'quot.quotation_status','quot.cdate','quot.revise_status','quot.prev_quotation_id','quot.approve_status','quot.cust_id' ,'inq.stage_prob','tsk.assign_user_ids');
		$sIndexColumn = "quot.quotation_id";
		$isWhere = array("quot.quotation_status = 0 ".$where.check_user_inq('inq'));
		$sTable = "tbl_quotation as quot";
		$isJOIN = array('left join tbl_customer as cust on cust.cust_id=quot.cust_id', 
                    'left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id', 
                    'left join users as usr on usr.user_id=inq.user_id',
                    'left join tbl_task as tsk on tsk.inquiry_id=inq.inquiry_id',
                    'left join tbl_cust_address as addr on addr.cust_id=cust.cust_id', 
                    'left join state_mst as state on state.stateid=addr.c_add_state',
                    'left join city_mst as city on city.cityid=addr.c_add_city');
		$hOrder = "quot.quotation_id desc";
		$hGroupby = array("quot.quotation_id");
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['quotation_no'];
			$row_data[] = date('d M, Y',strtotime($row['quotation_date']));
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['inquiry_no'];
			$row_data[] = $row['city_name'];
			$row_data[] = $row['user_name'];
			
			$query_i="select GROUP_CONCAT(DISTINCT mst.user_name SEPARATOR ',<br/>') as asinguser from users as mst
				where mst.user_id in (".$row['assign_user_ids'].")";
			$result_i=$dbcon->query($query_i);
			$rel_i=mysqli_fetch_assoc($result_i);
			
			$row_data[] = $rel_i['asinguser'];
			
			if($row['approve_status']=='1'){
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Authorized</div>';
			}
			else{
				$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div>';
			}
			
			
			$quotation_no=$dbcon->real_escape_string($row['quotation_no']);
			$edit='';$delete='';$print_btn='';$revise_btn='';$apprv_btn='';
			if($edit_btn_per) {
				$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'quotation_edit/'.$row['quotation_id'].'"><i class="fa fa-pencil"></i></a>';
				
				if($row['revise_status']=='1'){
					$revise_btn='<button type="button" class="btn btn-xs btn-success" data-original-title="Quotation Revised" data-toggle="tooltip" data-placement="top"><i class="fa fa-check"></i></button>';
				}
				else{
					$revise_btn='';
					//$revise_btn='<a class="btn btn-xs btn-info" data-original-title="Revise Quotation" data-toggle="tooltip" data-placement="top" href="'.ROOT.'quotation_revise/'.$row['quotation_id'].'"><i class="fa fa-repeat"></i></a>';
				}
			}
			if($delete_btn_per) {
				$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_quotation('.$row['quotation_id'].',\''.$quotation_no.'\','.$row['prev_quotation_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			if($row['revise_status']=='1'){
				$edit='';$delete='';
			}
			
			$print_btn='<a class="btn btn-xs btn-primary" data-original-title="Print" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.'quotation_print/'.$row['quotation_id'].'"><i class="fa fa-print"></i></a>';
			
			/*if($_SESSION['user_type']=='2'){
				if($row['approve_status']=='1'){
					$apprv_btn='<button class="btn btn-xs btn-danger" data-original-title="Cancel Quotation" data-toggle="tooltip" data-placement="top" onClick="approv_quot('.$row['quotation_id'].',0)"><i class="fa fa-times"></i></button>';
				}
				else{
					$apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve Quotation" data-toggle="tooltip" data-placement="top" onClick="approv_quot('.$row['quotation_id'].',1)"><i class="fa fa-check"></i></button>';
				}
			}*/
			$apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Quotation" data-toggle="tooltip" data-placement="top" onClick="open_approv_quot('.$row['quotation_id'].',\''.$quotation_no.'\')"><i class="fa fa-exclamation-triangle"></i></button>';
			
			//$send_email='<button class="btn btn-xs btn-primary" data-original-title="Send Email" data-toggle="tooltip" data-placement="top" onClick="open_quot_email('.$row['quotation_id'].','.$row['cust_id'].')"><i class="fa fa-envelope"></i></button>'; 
			
			if($row['stage_prob']=='100' && $row['approve_status']=='1'){
				$edit='<button type="button" class="btn btn-xs btn-success" data-original-title="Inquiry Won" data-toggle="tooltip" data-placement="top">Won</button>';
				$delete='';$revise_btn='';
			}
			
			$row_data[] = $edit.' '.$delete.' '.$print_btn.' '.$revise_btn.' '.$apprv_btn.' '.$send_email;

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
	
		if($POST['revise_status']){//Get Revise Count No
			$get_rev_cnt="select count(quotation_id) as ttl_cnt,(select quotation_no from tbl_quotation where quotation_id=".$POST['start_quotation_id'].") as qt_no from tbl_quotation where quotation_status=0 and start_quotation_id=".$POST['start_quotation_id'];
			$rev_cnt=mysqli_fetch_assoc($dbcon->query($get_rev_cnt));
			$info['quotation_no'] 			= $rev_cnt['qt_no']."/R-".$rev_cnt['ttl_cnt'];
			$info['start_quotation_id']		= $POST['start_quotation_id'];			
			$info['prev_quotation_id']		= $POST['prev_quotation_id'];	
			$upd_prev_qt_sts=$dbcon->query("update tbl_quotation set revise_status=1 where quotation_id=".$POST['prev_quotation_id']);
		}
		else{
			$info['quotation_no']		= load_quotation_no($dbcon);
			//Update Start series of No
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=3 and company_id=".$_SESSION['company_id']);
		}

		$info['quotation_date']	= date('Y-m-d',strtotime($POST['quotation_date']));
		$info['cust_id']		= $POST['cust_id'];
		$info['c_con_id']		= $POST['c_con_id'];
		$info['inquiry_id']		= $POST['inquiry_id'];
		$info['quot_subject']	= $POST['quot_subject'];
		$info['quot_type']		= $POST['quot_type'];
		$info['quotation_valid_date']	= date('Y-m-d',strtotime($POST['quotation_valid_date']));
		$info['quotation_ref']	= $POST['quotation_ref'];
		$info['quot_remark']	= $_POST['quot_remark'];
		$info['g_total']		= $POST['g_total'];
		$info['currency_id']	= $POST['currency_id'];
		$info['an_id']			= $POST['an_id'];
		$info['quot_annex_content']			= $_POST['quot_annex_content'];
		$info['quot_address']			= $_POST['quot_address'];
		
		$info['create_date']	= date('Y-m-d H:i:s');
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		$ins_quotation_id=add_record('tbl_quotation', $info, $dbcon);
		
		if(!$POST['revise_status']){
			$upd_strt_qry=$dbcon->query("update tbl_quotation set start_quotation_id=".$ins_quotation_id." where quotation_id=".$ins_quotation_id);
		}
		
		/*Update Trn Table Start*/
		if($ins_quotation_id){
			$infotrn['quotation_id']		= $ins_quotation_id;
			$infotrn['quot_trn_status']	= 0;
			$updatetrnid=update_record('tbl_quotation_trn', $infotrn,"quot_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
		}
		/*Update Trn Table End*/
		
		/* Terms and Condition Start */
		foreach ($POST['tc_id'] as $key => $name) {
			$infotrm['tc_id']			= $POST['tc_id'][$key];
			$infotrm['tc_priority']		= $POST['tc_priority'][$key];
			$infotrm['tc_details']		= $_POST['tc_details'][$key];
			$infotrm['quotation_id']	= $ins_quotation_id;
			$infotrm['cdate']			= date("Y-m-d H:i:s");
			if(in_array($POST['tc_id'][$key],$POST['disp_term_flag'])){
				$insertrmid=add_record('tbl_quotation_terms_trn', $infotrm, $dbcon);
			}
		}
		/* Terms and Condition End */
		
		//Update quotation id in budget TRN data-original-title
		/*$upd_qt_id_qry="update `tbl_quot_budget_trn` set quotation_id=".$ins_quotation_id." where find_in_set(quot_trn_id,(select group_concat(DISTINCT quot_trn_id) from tbl_quotation_trn where quotation_id=".$ins_quotation_id."))";
		$upd_qt_id_qry_rs=$dbcon->query($upd_qt_id_qry);*/
		
		
		/*Update Attach Trn Table Start*/
		if($ins_quotation_id){
			$infonote['quotation_id']			= $ins_quotation_id;
			$infonote['dfd_attach_status']	= 0;
			$updatetrnid=update_record('tbl_quot_dfd_attach', $infonote,"dfd_attach_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
		}
		/*Update Attach Trn Table End*/
		
		
	/*Direct Task Entry Start*/
	if($POST['inquiry_id']){//Auto Complete Prev Flp Task Before Add
		$upd_qry="update tbl_task set task_status=1,task_completion_date='".date("Y-m-d H:i:s")."' where task_status=0 and inquiry_id=".$POST['inquiry_id'];
		$upd_qry_rs=$dbcon->query($upd_qry);
		
		//Add new Task
		
		$show_user_ids				= show_user_ids($dbcon,$POST['assign_user_ids']);
		$infotsk['show_user_ids']		= $show_user_ids;
		$infotsk['task_type_id']		= $POST['task_type_id'];
		$infotsk['task_rel_id']			= 5;//Fixed Type Inquiry
		$infotsk['inquiry_id']			= $POST['inquiry_id'];
		$infotsk['assign_user_ids']		= implode(",",array_filter($POST['assign_user_ids']));
		$infotsk['task_priority_id']            = $POST['task_priority_id'];
		$infotsk['task_due_date']		= date('Y-m-d H:i:s',strtotime($POST['task_due_date']));
		$infotsk['task_alert_id']		= 2;
		$infotsk['alert_date_time']		= date('Y-m-d H:i:s',strtotime($POST['task_due_date']));
		
		$infotsk['task_remark']                 = $_POST['quot_remark'];
		$infotsk['create_date']                 = date('Y-m-d H:i:s');
		$infotsk['entry_type']                  = 1;//Fixed Task Type
		$infotsk['cdate']			= date("Y-m-d H:i:s");
		$infotsk['user_id']			= $_SESSION['user_id'];
		$infotsk['company_id']                  = $_SESSION['company_id'];
		$ins_task_id=add_record('tbl_task', $infotsk, $dbcon);
		
		$infoinq['show_user_ids']	= $show_user_ids;
		$updateinqid=update_record('tbl_inquiry', $infoinq, "inquiry_id=".$POST['inquiry_id'], $dbcon);
	}	
	/*Direct Task Entry End*/
	

	//Auto approve if allowed
	$final_btn_per=check_permission("quotation_list",$_SESSION['user_id'],'final_aprv',$dbcon);
	if($final_btn_per){
		$infoaprvqt['approve_status']	= 1;
		$updateid=update_record('tbl_quotation', $infoaprvqt,"quotation_id=".$ins_quotation_id , $dbcon);
	}
		
		if($ins_quotation_id){	
			$arr['msg']="1";
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"quotation_add",1,"tbl_quotation",$ins_quotation_id);
		}
		else{
			$arr['msg']="0";
		}
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$info['quotation_date']	= date('Y-m-d',strtotime($POST['quotation_date']));
		$info['cust_id']		= $POST['cust_id'];
		$info['c_con_id']		= $POST['c_con_id'];
		$info['inquiry_id']		= $POST['inquiry_id'];
		$info['quot_subject']	= $POST['quot_subject'];
		$info['quot_type']		= $POST['quot_type'];
		$info['quotation_valid_date']	= date('Y-m-d',strtotime($POST['quotation_valid_date']));
		$info['quotation_ref']	= $POST['quotation_ref'];
		$info['quot_remark']	= $_POST['quot_remark'];
		$info['g_total']		= $POST['g_total'];
		$info['currency_id']	= $POST['currency_id'];
		$info['an_id']			= $POST['an_id'];
		$info['quot_annex_content']			= $_POST['quot_annex_content'];
		$info['quot_address']			= $_POST['quot_address'];
		
		$info['cdate']			= date("Y-m-d H:i:s");
		//$info['user_id']		= $_SESSION['user_id'];
		//$info['company_id']		= $_SESSION['company_id'];
		$updateid=update_record('tbl_quotation', $info, "quotation_id=".$POST['eid'], $dbcon);
		
		/* Terms and Condition Start */
		$deltrmid=delete_record('tbl_quotation_terms_trn',"quotation_id=".$POST['eid'], $dbcon);
		foreach ($POST['tc_id'] as $key => $name) {
			$infotrm['tc_id']			= $POST['tc_id'][$key];
			$infotrm['tc_priority']		= $POST['tc_priority'][$key];
			$infotrm['tc_details']		= $_POST['tc_details'][$key];
			$infotrm['quotation_id']	= $POST['eid'];
			$infotrm['cdate']			= date("Y-m-d H:i:s");
			if(in_array($POST['tc_id'][$key],$POST['disp_term_flag'])){
				$insertrmid=add_record('tbl_quotation_terms_trn', $infotrm, $dbcon);
			}
		}
		/* Terms and Condition End */
		
		//Update quotation id in budget TRN data-original-title
		/*$upd_qt_id_qry="update `tbl_quot_budget_trn` set quotation_id=".$POST['eid']." where find_in_set(quot_trn_id,(select group_concat(DISTINCT quot_trn_id) from tbl_quotation_trn where quotation_id=".$POST['eid']."))";
		$upd_qt_id_qry_rs=$dbcon->query($upd_qt_id_qry);*/
		
		if($updateid){	
			$arr['msg']="update";
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"quotation_add",2,"tbl_quotation",$POST['eid']);
		}
		else{
			$arr['msg']=0;
		}
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['quotation_status']	= 2;
		$updateid=update_record('tbl_quotation', $info, "quotation_id=".$POST['quotation_id'], $dbcon);
		
		$infotrn['quot_trn_status']	= 2;
		$updatetrnid=update_record('tbl_quotation_trn', $infotrn, "quotation_id=".$POST['quotation_id'], $dbcon);
		
		//Update Prev quotation ID
		$prev_quotation_id=$POST['prev_quotation_id'];
		
		if($prev_quotation_id){
			$upd_prev_qt_sts=$dbcon->query("update tbl_quotation set revise_status=0 where quotation_id=".$prev_quotation_id);
		}
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"quotation_add",3,"tbl_quotation",$POST['quotation_id']);
		
		if($updateid)
			echo "1";	
		else
			echo "0";			
	}
	else if(strtolower($POST['mode']) == "add_field") {
		$info1['product_id']	= $POST['product_id'];
		$info1['product_desc']	= $_POST['product_desc'];
		$info1['product_spec']	= $_POST['product_spec'];
		$info1['level_id']		= $POST['level_id'];
		$info1['unitid']		= $POST['unitid'];
		$info1['act_amt_flag']	= $POST['act_amt_flag'];
		$info1['product_qty']	= $POST['product_qty'];
		$info1['product_rate']	= $POST['product_rate'];
		$info1['product_discount']	= $POST['product_discount'];
		$info1['discount_per']	= $POST['discount_per'];
		$info1['product_amount']= $POST['product_amount'];
		$info1['formulaid']= $POST['formulaid'];
		//$info1['product_total']= $POST['product_total'];
		$info=get_product_common_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
		$info1=array_merge($info1,$info);
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['company_id']	= $_SESSION['company_id'];
		//var_dump($info1);
		$table='tbl_quotation_trn';$tableid='quot_trn_id';
		if(!empty($POST['quotation_id'])) {
			$info1['quotation_id']= $POST['quotation_id'];
		}
		else{
			$info1['quot_trn_status']= 3;
		}
		
		if(empty($POST['edit_id'])) {
			$inserid=add_record($table, $info1, $dbcon);
		}
		else {
			$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
		}
	}
	else if(strtolower($POST['mode'])=="show_data") {
		$str='';
		if($POST['quotation_id']){
			$query="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.unitid
			where trn.quot_trn_status=0 and trn.quotation_id=".$POST['quotation_id'];
		}
		else{
			$query="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
			left join product_mst as pro on pro.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.unitid
			where trn.quot_trn_status=3 and trn.user_id=".$_SESSION['user_id'];
		}
		$result=$dbcon->query($query);
		$str.='<table class="display table table-bordered table-striped" style="width:110%;">
				<tr>		
					<th width="3%" class="text-center">Action</th>	
					<th width="20%" class="text-center">Product Name</th>
					<!--<th width="5%" class="text-center">Level</th>-->
					<th width="5%" class="text-center">Quantity</th>
					<th width="8%" class="text-center">Rate</th>
					<th width="5%" class="text-center">Discount</th>
					<th width="10%" class="text-center">Taxable Value</th>		
					<th width="8%" class="text-center">Tax</th>		
					<th width="12%" class="text-center">Amount</th>		  			  
					<th width="5%" class="text-center">Extra<br/>Actual</th>		  			  
					<th width="10%" class="text-center">Specification</th>		  			  
				</tr>
				<tbody>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				$act_amt_flag="No";
				if($rel['act_amt_flag']=='1'){
					$act_amt_flag="Yes";
				}
				
				$str.='<tr> 
					<td style="vertical-align:middle"> 
						<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_trn_data('.$rel['quot_trn_id'].')"><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_trn_data('.$rel['quot_trn_id'].')">X</button><br/><br/>
						<!--<a onClick="setFormSubmitting()" href="'.ROOT.'quotation_budget_get/'.$rel['quot_trn_id'].'" class="btn btn-xs btn-primary" ><i class="fa fa-calculator" aria-hidden="true"></i> Budget</a>-->
					</td>
					<td style="vertical-align:top;">
						<strong>'.$rel['product_name'].'</strong><br/>
						<strong>Desc:</strong> '.(nl2br($rel['product_desc'])).'
					</td>
					<!--<td style="vertical-align:top;" class="text-center">
						'.$rel['level_id'].'
					</td>-->
					<td style="vertical-align:top;" class="text-center">
						'.$rel['product_qty'].' '.$rel['unit_name'].'
					</td>
					<td style="vertical-align:top;" class="text-right">
						'.$rel['product_rate'].'
					</td>
					<td style="vertical-align:top;" class="text-right">
						'.$rel['product_discount'].' ('.$rel['discount_per'].'%)
					</td>
					<td style="vertical-align:top;" class="text-right">
						'.$rel['product_amount'].'	
					</td>	
					<td style="vertical-align:top;" class="text-left">';
						if(empty($rel['formulaid'])){
							$str.= '-';
						}else{
							$str.= (empty($rel['tax_name1']) ? " " : $rel['tax_name1'] .' : '. $rel['tax_amount1']).'<br/>';
							$str.= (empty($rel['tax_name2']) ? " " : $rel['tax_name2'] .' : '. $rel['tax_amount2']).'<br/>';
							$str.= (empty($rel['tax_name3']) ? " " : $rel['tax_name3'] .' : '. $rel['tax_amount3']).'<br/>';
						}
					$str.='</td>
					<td style="vertical-align:top;" class="text-right">
						<input type="hidden" name="amount[]" value="'.$rel['product_total'].'">
						'.$rel['product_total'].'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$act_amt_flag.'
					</td>
					<td style="vertical-align:top;">
						'.$rel['product_spec'].'
					</td>
				</tr>';
				$i++;
			}
		}
		else{
			$str.= '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
		
		$str.= '</tbody>
				</table>';
		echo $str;
	}
	else if(strtolower($POST['mode'])== "edit_trn_data") {
		$q = $dbcon -> query("SELECT trn.* FROM tbl_quotation_trn as trn WHERE quot_trn_id = '$POST[quot_trn_id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_trn_data") {
		$row=array();
		$info['quot_trn_status']=2;	
		$updateid=update_record('tbl_quotation_trn', $info, "quot_trn_id=".$POST['quot_trn_id'] , $dbcon);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "getproduct_amount")
	{
		$arr=get_product_common_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "load_cust_inq") {
		$_SESSION['def_quot_cust_id']=$POST['cust_id'];
		$resp['resp_html']	= get_cust_inq($dbcon,"",$POST['cust_id']);
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "load_inq_pro") {
		$_SESSION['def_quot_inquiry_id']=$POST['inquiry_id'];
		$_SESSION['def_quot_subject']=$_POST['def_quot_subject'];
		$_SESSION['def_c_con_id']=$POST['c_con_id'];
		$inquiry_id=$POST['inquiry_id'];
		//Delete temp DATA
		$deltempid=delete_record('tbl_quotation_trn',"quot_trn_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
		//Get Inq Data
		$inq_qry="select * from tbl_inquiry_trn where inquiry_trn_status=0 and inquiry_id=".$inquiry_id;
		$inq_qry_rs=$dbcon->query($inq_qry);
		// Get Formula id null for now
		$formulaid=0;
		while($inq_rel=mysqli_fetch_assoc($inq_qry_rs)){
			$info1['product_id']	= $inq_rel['product_id'];
			$info1['product_desc']	= $inq_rel['product_desc'];
			$info1['product_spec']	= $inq_rel['product_spec'];
			$info1['level_id']		= $inq_rel['level_id'];
			$info1['unitid']		= $inq_rel['unitid'];
			$info1['product_qty']	= $inq_rel['product_qty'];
			$info1['product_rate']	= $inq_rel['product_rate'];
			$info1['product_amount']= $inq_rel['product_amount'];
			$info1['formulaid']		= $formulaid;
			//$info1['product_total']= $POST['product_total'];
			$info=get_product_common_tax($dbcon,$inq_rel['product_amount'],$formulaid);
			$info1=array_merge($info1,$info);
			$info1['user_id']		= $_SESSION['user_id'];
			$info1['company_id']	= $_SESSION['company_id'];
			$info1['quot_trn_status']= 3;
			$inserid=add_record("tbl_quotation_trn", $info1, $dbcon);
		}
	}
	else if(strtolower($POST['mode']) == "load_annex_content") {
		$annex_qry="select * from tbl_annexure where an_id=".$POST['an_id'];
		$annex_rel=mysqli_fetch_assoc($dbcon->query($annex_qry));
		echo json_encode($annex_rel);
	}
	else if(strtolower($POST['mode'])== "load_product_dtls") {
		$pro_qry="select * from product_mst where product_id=".$POST['product_id'];
		$pro_rel=mysqli_fetch_assoc($dbcon->query($pro_qry));
		echo json_encode($pro_rel);
	}
	else if(strtolower($POST['mode']) == "load_typeswise_terms") {
		$quot_type=$POST['quot_type'];
		$quotation_id=$POST['quotation_id'];
		$str='';
		$str.='<table class="display table table-bordered table-striped">
			  <thead>
				<tr>
                                        <th width="5%" class="text-center">
                                            <input type="checkbox" class="check_all_terms" style="height: 20px;width: 20px;" id="check_all_terms" name="check_all_terms" onClick="terms_check_all(this);">
                                        </th>
					<th width="25%" class="text-center">Term Name</th>
					<th width="5%" class="text-center">Priority</th>
					<th width="65%" class="text-center">Term And Condition</th>				  
				</tr>
			  </thead>
			  <tbody>';
		//Get All Terms
		$terms_qry="select * from tbl_terms_condition where tc_status=0 and tc_category=1 and find_in_set(".$quot_type.",tc_for) order by tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);$t=1;
		while($terms_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$tc_priority=$terms_rel['tc_priority'];
			$tc_details=$terms_rel['tc_details'];
			if($quotation_id){
				$quot_term_qry="select * from tbl_quotation_terms_trn where quotation_terms_trn_status=0 and quotation_id=".$quotation_id." and tc_id=".$terms_rel['tc_id']."";
				$quot_term_rel=mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if($quot_term_rel['tc_priority']){
					$tc_priority=$quot_term_rel['tc_priority'];
				}
				if($quot_term_rel['tc_details']){
					$tc_details=$quot_term_rel['tc_details'];
				}
			}
			
			$str.='<tr>
					<td width="5%" class="text-center">
						<input type="checkbox" class="terms_checkbox" style="height: 20px;width: 20px;" id="disp_term_flag'.$t.'" name="disp_term_flag[]" value="'.$terms_rel['tc_id'].'" '.(($terms_rel['tc_id']==$quot_term_rel['tc_id'])?'checked':'').'>
						<input type="hidden" id="tc_id'.$t.'" name="tc_id[]" value="'.$terms_rel['tc_id'].'">
					</td>
                                        <td>'.$terms_rel['tc_name'].'</td>
					<td>
						<input type="number" class="form-control" min="0" id="tc_priority'.$t.'" name="tc_priority[]" value="'.$tc_priority.'">
					</td>
					<td>
						<textarea class="form-control" id="tc_details'.$t.'" name="tc_details[]">'.$tc_details.'</textarea>
					</td>
				</tr>';
			
			$t++;
		}	  
			
		$str.='</tbody> 
					</table>';	  
					
		$resp['resp_html']=$str;
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "copy_prev_quot_trn") {
		$del_trn=delete_record('tbl_quotation_trn',"quot_trn_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
		$prev_quotation_id=$POST['prev_quotation_id'];
		$copy_qry="Insert into tbl_quotation_trn (product_id,product_desc,product_spec,level_id,product_qty,unitid,product_rate, product_discount,discount_per,product_amount,formulaid,tax_name1,tax_amount1,tax_name2, tax_amount2,tax_name3,tax_amount3,product_total,company_id,user_id,quot_trn_status) 
		select product_id,product_desc,product_spec,level_id,product_qty,unitid,product_rate, product_discount,discount_per,product_amount,formulaid,tax_name1,tax_amount1,tax_name2, tax_amount2,tax_name3,tax_amount3,product_total,company_id,".$_SESSION['user_id'].",3 from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$prev_quotation_id;
		$copy_qry_rs=$dbcon->query($copy_qry);
	}
	else if(strtolower($POST['mode']) == "add_budget_trn_data") {
		$info1['req_product_id']	= $POST['req_product_id'];
		$info1['req_product_desc']	= $_POST['req_product_desc'];
		$info1['req_product_qty']	= $POST['req_product_qty'];
		$info1['req_product_rate']	= $POST['req_product_rate'];
		$info1['req_unitid']		= $POST['req_unitid'];
		$info1['req_product_amount']= $POST['req_product_amount'];
		$info1['quot_trn_id']		= $POST['quot_trn_id'];
		
		$info1['user_id']		= $_SESSION['user_id'];
		//var_dump($info1);
		$table='tbl_quot_budget_trn';$tableid='quot_budget_trn_id';
		$info1['quot_budget_trn_status']= 0;
		
		if(empty($POST['budget_trn_edit_id'])) {
			$get_dupl_qry="select quot_budget_trn_id from tbl_quot_budget_trn where quot_budget_trn_status=0 and quot_trn_id=".$POST['quot_trn_id']." and req_product_id=".$POST['req_product_id'];
			$dupl_qry_rs=$dbcon->query($get_dupl_qry);
			if(mysqli_num_rows($dupl_qry_rs)){
				echo '-1';
			}
			else{
				$inserid=add_record($table, $info1, $dbcon);
				echo '1';
			}
		}
		else {
			$updateid=update_record($table, $info1,$tableid."=".$POST['budget_trn_edit_id'] , $dbcon);	
			echo '1';
		}
	}
	else if(strtolower($POST['mode'])=="show_budget_trn_data") {
		$str='';
		
		$query="select trn.*,pro.product_name,unit.unit_name from tbl_quot_budget_trn as trn 
			left join tbl_product as pro on pro.product_id=trn.req_product_id
			left join unit_mst as unit on unit.unitid=trn.req_unitid
			where trn.quot_budget_trn_status=0 and trn.quot_trn_id=".$POST['quot_trn_id'];
		
		$result=$dbcon->query($query);
		$str.='<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
				<tr>
					<th width="35%" class="text-center">Item Details</th>
					<th width="" class="text-center">Quantity</th>
					<th width="" class="text-center">Rate</th>
					<th width="" class="text-center">Amount</th>
					<th width="7%" class="text-center">Action</th>
				</tr>
				<tbody>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				$str.='<tr> 
					<td style="vertical-align:top;">
						<strong>'.$rel['product_name'].'</strong><br/>
						<strong>Desc:</strong> '.(nl2br($rel['req_product_desc'])).'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['req_product_qty'].' '.$rel['unit_name'].'
					</td>
					<td style="vertical-align:top;" class="text-right">
						'.$rel['req_product_rate'].'
					</td>
					<td style="vertical-align:top;" class="text-right">
						'.$rel['req_product_amount'].'
						<input type="hidden" name="req_product_amount_ttl[]" value="'.$rel['req_product_amount'].'">
					</td>';
						
					$str.='
					<td style="vertical-align:middle"> 
						<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_budget_trn_data('.$rel['quot_budget_trn_id'].')"><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_budget_trn_data('.$rel['quot_budget_trn_id'].')">X</button>
					</td>
				</tr>';
				$i++;
			}
		}
		else{
			$str.= '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
		
		$str.= '</tbody>
				</table>';
		echo $str;
	}
	else if(strtolower($POST['mode'])=="edit_budget_trn_data") {
		$q = $dbcon -> query("SELECT trn.* FROM tbl_quot_budget_trn as trn WHERE quot_budget_trn_id = '$POST[quot_budget_trn_id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_budget_trn_data") {
		$row=array();
		$info['quot_budget_trn_status']=2;	
		$updateid=update_record('tbl_quot_budget_trn', $info, "quot_budget_trn_id=".$POST['quot_budget_trn_id'] , $dbcon);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "get_budget") {
		$info['budget_trn_ttl']		= $POST['budget_trn_ttl'];
		$info['budget_margin_per']	= $POST['budget_margin_per'];
		$info['budget_margin_amt']	= $POST['budget_margin_amt'];
		$info['budget_trn_g_total']	= $POST['budget_trn_g_total'];
		$updateid=update_record('tbl_quotation_trn', $info, "quot_trn_id=".$POST['quot_trn_id'] , $dbcon);
		
		$row['msg']="1";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "copy_master_bom_data") {
		$del_trn=delete_record('tbl_quot_budget_trn',"quot_trn_id=".$POST['quot_trn_id'], $dbcon);
		$product_id=$POST['product_id']; $product_qty=floatval($POST['product_qty']);
		$copy_qry="Insert into tbl_quot_budget_trn (req_product_id,req_product_desc,req_unitid,req_product_qty,req_product_rate,req_product_amount,user_id,quot_trn_id)

		SELECT alloctrn.req_product_id,alloctrn.req_product_desc,req_unitid,act_req_qty*".$product_qty.",(select product_purchase_mst_rate from tbl_product where product_id=alloctrn.req_product_id) as p_rate,((select product_purchase_mst_rate from tbl_product where product_id=alloctrn.req_product_id)*act_req_qty*".$product_qty.") as p_amt,".$_SESSION['user_id'].",".$POST['quot_trn_id']." FROM `tbl_master_bom_trn` as alloctrn
		left join tbl_master_bom as alloc on alloc.bom_mst_id=alloctrn.bom_mst_id
		where alloctrn.bom_mst_trn_status=0 and alloc.bom_mst_status=0 and alloc.product_id=".$product_id;
		$copy_qry_rs=$dbcon->query($copy_qry);
	}
	else if(strtolower($POST['mode']) == "approv_quot") {
		
		$info['approve_status']=$POST['approve_status'];
		$updateid=update_record('tbl_quotation', $info,"quotation_id=".$POST['quotation_id'] , $dbcon);
		
		if($updateid){
			echo 1;
		}
		else{
			echo 0;
		}
	}
	else if(strtolower($POST['mode']) == "add_apprv_hist") {
		
		$info1['assign_user_ids']	= $POST['assign_user_ids'];
		$info1['approve_remark']	= $_POST['approve_remark'];
		$info1['approve_status']	= $POST['approve_status'];
		$info1['quotation_id']		= $POST['quotation_id'];
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		
		$inserid=add_record("tbl_quot_aprv_log", $info1, $dbcon);
		
		//Hide approve btn if not allowed
		$final_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'final_aprv',$dbcon);

		if($final_btn_per){
			$infoso['approve_status']	= $POST['approve_status'];
			$updateid=update_record('tbl_quotation', $infoso,"quotation_id=".$POST['quotation_id'] , $dbcon);
		}
		
	}
	else if(strtolower($POST['mode']) == "load_quot_hist_datatable") {
		
		$where='';
		$where.="  and log.quotation_id=".$POST['quotation_id'];
		
		$appData = array();
		$i=1;
		$aColumns = array('log.quot_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
		$sIndexColumn = "log.quot_aprv_log_id";
		$isWhere = array("log.quot_aprv_log_status=0 ".$where." ");
		$sTable = "tbl_quot_aprv_log as log";			
		$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
		$hOrder = "log.quot_aprv_log_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['user_name'];
			
			if($row['approve_status']=='1'){
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
			}
			else{
				$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Reject</div>';
			}
			
			$row_data[] = nl2br($row['approve_remark']);
			$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode'])== "open_quot_email") {
		$set="select quot_email_content from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));
		$email_content = $set_head['quot_email_content'];
		$resp['email_content']	= $email_content;
		
		//Get Customer Detail
		$custqry="select cust_email from tbl_customer where cust_id=".$POST['cust_id'];
		$cust_rel=mysqli_fetch_assoc($dbcon->query($custqry));
		$resp['to_email_id']	= strtolower($cust_rel['cust_email']);
		
		//Get Quot Subject
		$qt_qry="select qt.quot_subject from tbl_quotation as qt where qt.quotation_id=".$POST['quotation_id'];
		$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));
		$resp['email_subject']	= $qt_rel['quot_subject'];
		
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "send_mail") {
		//var_dump($POST);
		//exit;
		$files=array();
		$inquiry_id=strtolower($POST['email_ref_id']);
		$to_email_id=strtolower($POST['to_email_id']);
		$ccemail_id=strtolower($POST['ccemail_id']);
		$bccemail_id=strtolower($POST['bccemail_id']);
		$email_subject=$_POST['email_subject'];
		$email_content=$_POST['email_content'];
		if(!empty($_FILES['email_attach']['tmp_name'])) {
			$file = upload_mail_attch_file($_FILES,$dbcon);
			array_push($files,$file);
		}
		
		//Direct PDF Generate
		//$quotation_pdf=email_quotation_pdf($inquiry_id,$dbcon);
		//array_push($files,$quotation_pdf);
	
		//final_send_email($to_email_id,$ccemail_id,$bccemail_id,$email_subject,$email_content,$files);		
		$resp=final_send_email($to_email_id,$ccemail_id,$bccemail_id,$email_subject,$email_content,$files);
		
		
		$arr['msg']=array();
		if($resp['code']=='success'){
			$arr['msg']='1';
			if($file){
				unlink(MAIL_ATTACH_UPING.$file);
			}
			unlink(MAIL_ATTACH_UPING.$quotation_pdf);
		}
		else{
			$arr['msg']='0';
		}
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode'])== "add_dfd_attch_field") {
		$info1['dfd_attch_file']	= upload_attch_file($_FILES);
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		
		$table='tbl_quot_dfd_attach';$tableid='dfd_attach_id';
		if(!empty($POST['quotation_id'])) {
			$info1['quotation_id']= $POST['quotation_id'];
		}
		else{
			$info1['dfd_attach_status']= 3;
		}
		
		$inserid=add_record($table, $info1, $dbcon);
	}
	else if(strtolower($POST['mode'])== "show_dfd_attach_data") {
		
		if($POST['quotation_id']){
			$query="select mst.* from tbl_quot_dfd_attach as mst 
			where dfd_attach_status=0 and mst.quotation_id=".$POST['quotation_id'];
		}
		else{
			$query="select mst.* from tbl_quot_dfd_attach as mst 
			where dfd_attach_status=3 and mst.user_id=".$_SESSION['user_id'];
		}
		$result=$dbcon->query($query);
		echo '<table class="display table table-bordered table-striped">
				<tr>
					<th width="5%" class="text-center">Sr.</th>
					<th width="60%" class="text-center">Attached Image</th>
					<th width="10%" class="text-center">Action</th>					  
				</tr>
				<tbody>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				$file_path=$dbcon->real_escape_string(DOMAIN.INQ_ATTACH_VWING.$rel['dfd_attch_file']);
				echo '<tr> 
					<td style="vertical-align:top;">
						<strong>'.$i.'</strong>
					</td>
					<td style="vertical-align:top;" class="text-center">
						<a href="'.ROOT.INQ_ATTACH_VWING.$rel['dfd_attch_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>
						
						<button type="button" onclick="copyToClipboard(\''.$file_path.'\')" class="btn btn-primary" target="_blank"><i class="fa fa-clipboard"></i> Copy Path</button>
					</td>
					<td style="vertical-align:top">';
			
				echo ' <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_dfd_attach_data('.$rel['dfd_attach_id'].')">X</button>';
			
					echo '</td>	
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
		
		echo '</tbody>
				</table>';
	}
	else if(strtolower($POST['mode'])== "delete_dfd_attach_data") {
		$row=array();
		$del_attch_qry="select dfd_attch_file from tbl_quot_dfd_attach where dfd_attach_id=".$POST['dfd_attach_id'];
		$del_attch_rel=mysqli_fetch_assoc($dbcon->query($del_attch_qry));
		unlink(INQ_ATTACH_UPING.$del_attch_rel['dfd_attch_file']);
		
		$info['dfd_attach_status']=2;	
		$updateid=update_record('tbl_quot_dfd_attach', $info, "dfd_attach_id=".$POST['dfd_attach_id'] , $dbcon);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "load_def_quotation_no") {
		$resp['quot_no']=load_quotation_no($dbcon);
		echo json_encode($resp);
	}

function upload_mail_attch_file($FILES)
{
	$rand=rand(0,99999999);
	if(!empty($FILES['email_attach']['tmp_name'])) {
		$temp = explode(".", $FILES["email_attach"]["name"]);
		$extension = strtolower(end($temp));
		$File = "mail_attch_".$rand.".".$extension;
		$tmp_name = $FILES["email_attach"]["tmp_name"];
		move_uploaded_file($tmp_name,MAIL_ATTACH_UPING.$File);

		return  $File;				
	}
}
function load_quotation_no($dbcon){
	//Load no by Type ID
	$row=array();
	$query1="select * from tbl_invoicetype where status=0 and type_id=3 and company_id=".$_SESSION['company_id'];
	$rows=mysqli_fetch_assoc($dbcon->query($query1));
	$id=$rows['taxinvoice_start'];
	$id=$id+1;
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
	return $row['invoiceno'];
}	

function upload_attch_file($FILES)
{
	$rand=rand(0,99999999);
	if(!empty($FILES['dfd_attch_file']['tmp_name'])) {
		$temp = explode(".", $FILES["dfd_attch_file"]["name"]);
		$extension = strtolower(end($temp));
		$File = "dfd_attch_".$rand.".".$extension;
		$tmp_name = $FILES["dfd_attch_file"]["tmp_name"];
		move_uploaded_file($tmp_name,INQ_ATTACH_UPING.$File);

		return  $File;				
	}
}

?>