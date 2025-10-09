<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
$incPath = $path.'include/';

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
if(brp_strtolower($POST['mode']) == "fetch") {
	$s_date=explode(' - ',$POST['date']);
	$userid=$_SESSION['user_id'];
	$emp_id=getEmployeeIdUser($dbcon,$userid);
	
	$_SESSION['start']=$s_date[0]; $_SESSION['end']=$s_date[1];
	
	//Amish Soni 05-01-2021
	// $edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	// $delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		COMPLAINT_SLUG_VIEW,
		COMPLAINT_SLUG_EDIT,
		COMPLAINT_SLUG_DELETE
	]);
	 
	$where='';
	$where.=" and complaint_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND complaint_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
	
	$appData = array();
	$i=1;
	$aColumns = array('complaint_id', 'complaint_no', 'complaint_date','emp_id', 'cust.company_name', 'ctype.complaint_type_name', 'complaint_status','followup_status','comp.cdate','comp.user_id','e.employee_name','f.f_status_name');
	$sIndexColumn = "complaint_id";
	
	if($emp_id>0)
	{
		$isWhere = array("complaint_status = 0 and emp_id='$emp_id' and comp.branch_id = $_SESSION[branch_id] and comp.company_id in (0,$_SESSION[company_id])".$where);
	}
	else
	{
        $whbr = check_branch('comp');
		$isWhere = array("complaint_status = 0 $whbr and comp.company_id in (0,$_SESSION[company_id])".$where);
	}
	
	$sTable = " tbl_complaint as comp";			
	$isJOIN = array('left join tbl_customer cust on comp.cust_id=cust.cust_id', 'left join complaint_type_mst as ctype on ctype.complaint_type_id=comp.complaint_type_id','left join employee_mst as e on comp.emp_id=e.employee_id','left join tbl_followup_status as f on comp.followup_status=f.f_id');
	$hOrder = "comp.complaint_id desc";
	include($incPath.'pagging.php');
	//echo $squery;
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['complaint_no']; 
		$row_data[] = date('d M, Y',strtotime($row['complaint_date']));
		$row_data[] = $row['company_name']; 
		$row_data[] = $row['complaint_type_name']; 
		$row_data[] = $row['f_status_name'];
		$row_data[] = $row['employee_name']; 
		
		$edit_btn='';$delete_btn=''; 
		//Amish Soni 05-01-2021
		if(in_array(COMPLAINT_SLUG_EDIT, $bulkAccessArray)) {
		// if($edit_btn_per){
			$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.SERVICE_ROOT.'complaint_edit/'.$row['complaint_id'].'"><i class="fa fa-pencil"></i></a>';

		}

		//Amish Soni 05-01-2021
		if(in_array(COMPLAINT_SLUG_DELETE, $bulkAccessArray)) {
		// if($delete_btn_per){
			$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_complaint('.$row['complaint_id'].')"><i class="fa fa-trash-o"></i></button>'; 
		}
		
		$complain_btn='<a href="'.ROOT.SERVICE_ROOT.'complaint_status/'.$row['complaint_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Complain Status" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';

		//Amish Soni 05-01-2021
		$view_btn = '';
		if(in_array(COMPLAINT_SLUG_VIEW, $bulkAccessArray)) {
			$view_btn = '<button class="btn btn-xs btn-info" data-original-title="View Complain History" data-toggle="tooltip" data-placement="top" onClick="view_complain_history('.$row['complaint_id'].');" id="view_btn"><i class="fa fa-eye"></i></button>';
		}

		$row_data[] = $edit_btn.' '.$delete_btn.' '.$complain_btn.' '.$view_btn; 
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo brp_json_encode( $output );
}
else if(brp_strtolower($POST['mode']) == "add") {

	
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	// follow up data insert 
	$cid=$POST['complain_id'];
	$info['fl_f_status']		= $POST['f_action'];
	$info['fl_e_id']		= $POST['f_emp'];
	$info['f_remark']		= $POST['f_remark'];
	$info['fl_date']			= date("Y-m-d h:i:s");
	$info['user_id']		= $_SESSION['user_id'];
	$info['old_sp_part']		= $POST['old_sp_part'];		
	$info['fl_cid']		= $POST['complain_id'];		
	
	$inserid=add_record('tbl_follow', $info, $dbcon, $branch_id);
	
	//complaint update 
	
	$infotrn['followup_status']	= $POST['f_action'];
	$infotrn['emp_id']	= $POST['f_emp'];
	$infotrn['old_sp_part_status']	= brp_strtolower($POST['old_sp_part']);
	$infotrn['sp_part_status']	= "3";
	$infotrn['mdate']			= date("Y-m-d H:i:s");
	$infotrn['cust_fb_id']		= $POST['cust_fb_id'];

	$compQuery = "SELECT assign_cust_ids,complaint_id FROM tbl_complaint tc WHERE tc.complaint_id=".$POST['complain_id'];
	$cq = $dbcon->query($compQuery);
	$cqRel=brp_mysqli_fetch_assoc($cq);
	$ids = explode(",",$cqRel['assign_cust_ids']);
	if (!in_array($POST['f_emp'], $ids)) { 
		if ($cqRel['assign_cust_ids']) {
			$infotrn['assign_cust_ids'] = $cqRel['assign_cust_ids'].','.$POST['f_emp'];
		} else {
			$infotrn['assign_cust_ids'] = $POST['f_emp'];
		}	
	}
	
	$updatetrnid=update_record('tbl_complaint', $infotrn,"complaint_id=".$POST['complain_id'] ,$dbcon, $branch_id);
	
	//folloup status update
	$infofo['s_status']	= '1';
	$infofo['s_fl_status']	= $POST['f_action'];
	$where="";
	$where.=" and s_fl_status=0";
	$updatetrnid=update_record('tbl_complain_spare_part', $infofo,"s_comp_id=".$POST['complain_id'].$where ,$dbcon, $branch_id);
	
	$infofon['s_status']	= '1';
	$where="";
	$where.=" and s_fl_status!=0 and s_status=0";
	$updatetrnid=update_record('tbl_complain_spare_part', $infofon,"s_comp_id=".$POST['complain_id'].$where,$dbcon, $branch_id);
	
	$spare_count=get_total_spare_count($dbcon,$POST['complain_id']);
	
	//$spare_count=0;tbl_complain_spare_part
	
		$row['res'] ="1";
	
	echo brp_json_encode($row);	
	 
}
else if(brp_strtolower($POST['mode']) == "spare_part_add") {
	
	//$userid=$_SESSION['user_id'];
	$emp_id=getEmployeeIdComplain($dbcon,$POST['comp_id_hid']);

    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	
	$info1['comp_product_id']		= $POST['comp_product_id'];
	$info1['s_product']		= $POST['product_id'];
	$info1['s_qty']		= $POST['product_qty'];
	$info1['s_rate']		= $POST['product_rate'];
	$info1['s_amount']		= $POST['product_amt'];
	$info1['s_courier_name']		= $POST['courier_name'];
	$info1['s_courier_no']		= $POST['courier_no'];
	$info1['s_courier_del_date']		= date("Y-m-d h:i:s",strtotime($POST['courier_del_date']));
	$info1['s_status']		= '0';
	$info1['s_cust_id']		= $POST['cust_id_hid'];
	$info1['s_comp_id']		= $POST['comp_id_hid'];
	$info1['s_user_id']			= $_SESSION['user_id'];
	$info1['s_date']			= date("Y-m-d");
	if((brp_strtolower($POST['sp_sent']))=='yes'){
		$info1['c_type']	= 2;//Changes Spare part sent status as default by courier
	}
	else{
		$info1['c_type']	= 0;
	}
	
	$info1['s_paid_status']=$_POST['sp_free'];
	$info1['sp_sent_status']=$_POST['sp_sent'];
	$info1['sp_old_status']=$_POST['old_sp_sent'];
	$info1['s_emp_id']=$emp_id;
	
	$table='tbl_complain_spare_part';$tableid='s_id';
	
	
	if(empty($POST['edit_id'])) {
		$inserid=add_record($table, $info1, $dbcon, $branch_id);
	}
	else {
		$updateid=update_record($table, $info1, $tableid."=".$POST['edit_id'], $dbcon, $branch_id);
	} 

	//Amish Soni 1-9-2020
	$id=$_REQUEST['id'];
	$comp_sp_approve_request = get_total_spare_count_request($dbcon,$id);
	$r['comp_sp_approve_request'] = $comp_sp_approve_request;

	echo brp_json_encode($r);
	
}
else if(brp_strtolower($POST['mode']) == "load_complain_data") {
	
	$complaint_id=$POST['complaint_id'];
	
	$query="select pr.s_id,pr.s_comp_id,pr.s_cust_id,pr.s_user_id,pr.comp_product_id,pr.s_date,pr.s_product,pr.s_qty,pr.s_rate,pr.s_amount,pr.s_courier_name,pr.s_courier_no,pr.s_courier_del_date,pr.s_status,pr.s_paid_status,pr.sp_sent_status,pr.sp_old_status,pm.product_name as sp_product,pm1.product_name as complain_product from tbl_complain_spare_part as pr inner join product_mst as pm on pr.s_product=pm.product_id inner join product_mst as pm1 on pm1.product_id=pr.comp_product_id where pr.s_comp_id=$complaint_id and pr.s_status!='1'";
	
	$result=$dbcon->query($query);
	if(brp_mysqli_num_rows($result)>0)
	{
		$i=1;
		while($rel=brp_mysqli_fetch_assoc($result))
		{	 
			if($rel['s_courier_del_date']=='0000-00-00' || $rel['s_courier_del_date']=='1970-01-01')
			{
				$date="";
			}
			else
			{
				$date=date("d/m/Y",strtotime($rel['s_courier_del_date']));
			}
			
			if($rel['s_status']=='2')
			{
				$btn_request='  <button type="button" data-original-title="Approve New Part Request" class="btn btn-round btn-success btn-xs" data-toggle="tooltip" data-placement="top" onclick="request_data_complain('.$rel['s_id'].');" id="filerequest'.$i.'"><i class="fa fa-check-circle"></i></button>';
			}
			else
			{
				$btn_request='';
			}
	
			echo '<tr>
				<td style="vertical-align:top;">
					'.$rel['complain_product'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['sp_product'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['s_qty'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['s_rate'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['s_amount'].'
				</td>
				<td style="vertical-align:top;">
					'.ucwords($rel['s_paid_status']).'
				</td>
				<td style="vertical-align:top;">
					<b>Courier Name : </b> '.$rel['s_courier_name'].'<br>
					<b>Courier No : </b> '.$rel['s_courier_no'].'<br>
					<b>Courier Date : </b> '.$date.'
				</td>
				<td style="vertical-align:top;">
					'.$rel['sp_sent_status'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['sp_old_status'].'
				</td>					
				<td>
					<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_complain('.$rel['s_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button> 
					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_complain('.$rel['s_id'].');" id="fieldremove'.$i.'">X</button>'.$btn_request.'
				</td>	
			</tr>';
			$i++;
		}
	}
	else{
		echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	}
}
else if(brp_strtolower($POST['mode'])== "preedit") {
	$q = $dbcon ->query("SELECT * FROM tbl_complain_spare_part WHERE s_id='$POST[complaint_trn_id]'");
	$r = brp_mysqli_fetch_assoc($q);
	//load BOM Products
	$product=$r['comp_product_id'];
	
	
	$getParentNodes = "select * from tbl_bomtrn where sale_product_id='$product'";
	$resParentNodes = $dbcon->query($getParentNodes);
	$response = '';
	$response.='<select>';
	
	ob_start();
	build_category_tree($dbcon,$product,0);
	$response.=ob_get_clean();
	ob_end_clean();
	
	$response.='</select>';
	$r['pro_resp_html'] = $response;
	echo brp_json_encode($r);
}
else if(brp_strtolower($POST['mode'])== "delete_data") {
	
	$q=$dbcon->query("delete from tbl_complain_spare_part where s_id='$POST[complaint_trn_id]'");
	
	$row['res']="1";

	//Amish Soni 1-9-2020
	$id=$_REQUEST['id'];
	$comp_sp_approve_request = get_total_spare_count_request($dbcon,$id);
	$row['comp_sp_approve_request'] = $comp_sp_approve_request;
	
	echo brp_json_encode($row);
}
else if(brp_strtolower($POST['mode'])== "request_complain") {
	
	$q=$dbcon->query("update tbl_complain_spare_part set s_status='0' where s_id='$POST[complaint_trn_id]'");
	
	$row['res']="1";
	
	echo brp_json_encode($row);
}
else if(brp_strtolower($POST['mode']) == "show_complain_history_spare_part") {
	
	$complain_id=$POST['complain_id'];
	
	$appData = array();
	$i=1;
	$aColumns = array('pr.s_id', 'pr.s_comp_id', 'pr.s_cust_id','pr.s_user_id','pr.s_date', 'pr.s_product', 'pr.s_qty','pr.s_rate','pr.s_amount','pr.s_courier_name','pr.s_courier_no','pr.s_courier_del_date','pr.s_status','pm.product_name');
	$sIndexColumn = "s_id";
	
	$isWhere = array("pr.s_comp_id=$complain_id");
	
	$sTable = " tbl_complain_spare_part as pr";			
	$isJOIN = array('inner join product_mst as pm on pr.s_product=pm.product_id');
	$hOrder = "pr.s_id desc";
	include($incPath.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		
		if($row['s_courier_del_date']=='0000-00-00')
		{
			$date="";
		}
		else
		{
			$date=date("d/m/Y",strtotime($row['s_courier_del_date']));
		}
	
		
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['product_name']; 
		$row_data[] = $row['s_qty']; 
		$row_data[] = $row['s_rate']; 
		$row_data[] = $row['s_amount']; 
		$row_data[] = $row['s_courier_name']; 
		$row_data[] = $row['s_courier_no'];
		$row_data[] = $date; 
		
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo brp_json_encode( $output );
	
}
else if(brp_strtolower($POST['mode']) == "show_complain_product") {
	
	$complain_id=$POST['complain_id'];
	
	$appData = array();
	$i=1;
	$aColumns = array('comp.complaint_id','comp.product_id','comp.comp_pro_sts','comp.complaint_trn_status', 'p.product_name');
	$sIndexColumn = "complaint_trn_id";
	
	$isWhere = array("comp.complaint_id=$complain_id");
	
	$sTable = " tbl_complaint_trn as comp";	
	
	$isJOIN = array('inner join product_mst as p on p.product_id=comp.product_id');
	$hOrder = "comp.complaint_id desc";
	include($incPath.'pagging.php');
	//echo $squery;
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		
		if($row['comp_pro_sts']=='1')
		{
			$status="free";
		}
		else
		{
			$status="paid";
		}
		
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['product_name']; 
		$row_data[] = $status; 
		
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo brp_json_encode( $output );
	
}
else if(brp_strtolower($POST['mode']) == "get_product_rate") 
{
	$product_id=$POST['product_id'];
	
	$rate=get_rate_product($dbcon,$product_id);
	
	echo $rate;
}
else if(brp_strtolower($POST['mode']) == "show_request_spare_part") 
{
	$sp_id=$POST['sp_id'];
	
	$sel=$dbcon->query("select pr.s_id,pr.s_comp_id,pr.s_cust_id,pr.s_user_id,pr.comp_product_id,pr.s_date,pr.s_product,pr.s_qty,pr.s_rate,pr.s_amount,pr.s_courier_name,pr.s_courier_no,pr.s_courier_del_date,pr.s_status,pm.product_name as sp_product ,pm1.product_name as complain_product from tbl_complain_spare_part as pr inner join product_mst as pm on pr.s_product=pm.product_id inner join product_mst as pm1 on pm1.product_id=pr.comp_product_id where pr.s_id='$sp_id'");
	$row=brp_mysqli_fetch_array($sel);
	
	if($row['s_courier_del_date']=='0000-00-00')
	{
		$date="";
	}
	else
	{
		$date=date("d/m/Y",strtotime($row['s_courier_del_date']));
	}
	
	$str="";
	
	$str.="<table class='table table-bordered'>";
	
		$str.="
		
		<tr>
			
			<th>Complain Product</th>
			<th>".$row['complain_product']."</th>
		</tr>
		
		<tr>
			
			<th>Product</th>
			<th>".$row['sp_product']."</th>
		</tr>
		
		<tr>
			
			<th>Qty</th>
			<th>".$row['s_qty']."</th>
		</tr>
		
		<tr>
			
			<th>Rate</th>
			<th><input type='text' class='form-control' name='s_rate_p' id='s_rate_p' value='".$row['s_rate']."' onkeyup='get_amount_model()' /></th>
		</tr>
		
		<tr>
			
			<th>Amount</th>
			<th><input type='text' class='form-control' name='s_amount_p' id='s_amount_p' value='".$row['s_amount']."' readonly /></th>
		</tr>
		
		<tr>
			
			<th>Courier Name</th>
			<th><input type='text' class='form-control' name='s_cname_p' id='s_cname_p' value='".$row['s_courier_name']."' /></th>
		</tr>
		
		<tr>
			
			<th>Courier No</th>
			<th><input type='text' class='form-control' name='s_cno_p' id='s_cno_p' value='".$row['s_courier_no']."' /></th>
		</tr>
		
		<tr>
			
			<th>Courier Delivery Date</th>
			<th><input type='text' class='form-control default-date-picker' name='s_cd_p' id='s_cd_p' value='".$date."' />
				<input type='hidden' class='form-control' name='s_sp_id' id='s_sp_id' value='".$sp_id."' />
				<input type='hidden' class='form-control' name='s_comp_id_p' id='s_comp_id_p' value='".$row['s_comp_id']."' />
				<input type='hidden' class='form-control' name='s_cust_id_p' id='s_cust_id_p' value='".$row['s_cust_id']."' />
				<input type='hidden' class='form-control' name='s_comp_product_id_p' id='s_comp_product_id_p' value='".$row['comp_product_id']."' />
				<input type='hidden' class='form-control' name='s_qty_p' id='s_qty_p' value='".$row['s_qty']."' />
				<input type='hidden' class='form-control' name='s_product_p' id='s_product_p' value='".$row['s_product']."' />
			</th>
		</tr>
		
		<tr>
			
			<th>Spare Part Sent</th>
			<th>
				<select class='form-control' name='s_sp_status_p' id='s_sp_status_p'>
					<option>--Spare Part Sent--</option>
					<option value='yes'>YES</option>
					<option value='no'>NO</option>
				</select>
			</th>
		</tr>
		
		<tr>
			
			<th>Old Spare Part</th>
			<th>
				<select class='form-control' name='s_old_sp_status_p' id='s_old_sp_status_p'>
					<option>--Old Spare Part Sent--</option>
					<option value='yes'>YES</option>
					<option value='no'>NO</option>
				</select>
			</th>
		</tr>
		
		<tr>
			<td colspan='2' align='center'><a class='btn btn-success' onclick='add_spare_request_data()'>Submit</a></td>
		</tr>
		";
	
	$str.="</table>";
	
	echo $str;
}
else if(brp_strtolower($POST['mode']) == "add_spare_request_data") 
{
	$emp_id=getEmployeeIdComplain($dbcon,$POST['s_comp_id_p']);

    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	
	$info1['s_rate']=$POST['s_rate_p'];
	$info1['s_amount']=$POST['s_amount_p'];
	$info1['s_courier_name']=$POST['s_cname_p'];
	$info1['s_courier_no']=$POST['s_cno_p'];
	$info1['s_courier_del_date']=date("Y-m-d h:i:s",strtotime($POST['s_cd_p']));
	$info1['sp_sent_status']=$_POST['s_sp_status_p'];
	$info1['sp_old_status']=$_POST['s_old_sp_status_p'];
	$info1['s_status']=0;
	$info1['s_emp_id']=$emp_id;
	
	$table='tbl_complain_spare_part';$tableid='s_id';
	
	$updateid=update_record($table, $info1, $tableid."=".$POST['s_sp_id'], $dbcon, $branch_id);

	if($_POST['s_old_sp_status_p']=='yes')
	{
		$infold['sc_cust_id']		= $POST['s_cust_id_p'];
		$infold['sc_comp_id']		= $POST['s_comp_id_p'];
		$infold['sc_comp_product_id']		= $POST['s_comp_product_id_p'];
		$infold['sc_product']		= $POST['s_product_p'];
		$infold['sc_qty']		= $POST['s_qty_p'];
		$infold['sc_rate']		= $POST['s_rate_p'];
		$infold['sc_amount']		= $POST['s_amount_p'];
		
		$infold['sc_user_id']			= $_SESSION['user_id'];
		$infold['sc_date']			= date("Y-m-d");
		$infold['s_return_status']			= "0";
		$infold['s_emp_id']=$emp_id;
		
		$table='tbl_complain_close_spare_part';$tableid='s_id';
		
		add_record($table, $infold, $dbcon, $branch_id);
	}		
	
	//Amish Soni 1-9-2020
	$id=$_REQUEST['id'];
	$comp_sp_approve_request = get_total_spare_count_request($dbcon,$id);
	$row['comp_sp_approve_request'] = $comp_sp_approve_request;		
	
	echo brp_json_encode($row);
	
}
?>