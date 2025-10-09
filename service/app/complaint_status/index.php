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
else if(brp_strtolower($POST['mode']) == "spare_part_add") {
	$info1['comp_product_id']		= $POST['comp_product_id'];
	$info1['s_product']		= $POST['product_id'];
	$info1['s_qty']		= $POST['product_qty'];
	$info1['s_cust_id']		= $POST['cust_id_hid'];
	$info1['s_comp_id']		= $POST['comp_id_hid'];
	$info1['s_user_id']			= $_SESSION['user_id'];
	$info1['s_date']			= date("Y-m-d");
	$info1['s_status']			= "2";
	$info1['sp_sent_status']	= "no";
	$info1['company_id'] = $_SESSION['company_id'];

    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	
	$table='tbl_complain_spare_part';$tableid='s_id';
	
	
	if(empty($POST['edit_id'])) {
		$inserid=add_record($table, $info1, $dbcon, $branch_id);
	}
	else {
		$updateid=update_record($table, $info1, $tableid."=".$POST['edit_id'], $dbcon, $branch_id);
	} 
}
else if(brp_strtolower($POST['mode']) == "load_complain_data") {
	
	$complaint_id=$POST['complaint_id'];
	
	$query="select pr.s_id,pr.s_comp_id,pr.s_cust_id,pr.s_user_id,pr.s_date,pr.comp_product_id,pr.s_product,pr.s_qty,pm.product_name as cproduct,pm1.product_name as nproduct from tbl_complain_spare_part as pr inner join product_mst as pm on pr.s_product=pm.product_id inner join product_mst as pm1 on pr.comp_product_id=pm1.product_id where pr.s_comp_id=$complaint_id and pr.s_status='2'";
	
	$result=$dbcon->query($query);
	if(brp_mysqli_num_rows($result)>0)
	{
		$i=1;
		while($rel=brp_mysqli_fetch_assoc($result))
		{	 
			echo '<tr>
				<td style="vertical-align:top;">
					'.$rel['nproduct'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['cproduct'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['s_qty'].'
				</td>  
				<td>
					<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_complain('.$rel['s_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button> 
					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_complain('.$rel['s_id'].');" id="fieldremove'.$i.'">X</button>
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
	echo brp_json_encode($r);
}
else if(brp_strtolower($POST['mode'])== "delete_data") {
	
	$q=$dbcon->query("delete from tbl_complain_spare_part where s_id='$POST[complaint_trn_id]'");
	
	$row['res']="1";
	
	echo brp_json_encode($row);
}
else if(brp_strtolower($POST['mode']) == "spare_part_add_old") {
	$info1['sc_comp_product_id']		= $POST['comp_product_id'];
	$info1['sc_product']		= $POST['product_id'];
	$info1['sc_qty']		= $POST['product_qty'];
	$info1['sc_rate']		= $POST['product_rate'];
	$info1['sc_amount']		= $POST['product_amt'];
	$info1['courier_name']		= $POST['courier_name'];
	$info1['courier_no']		= $POST['courier_no'];
	$info1['courier_del_date']		= date("Y-m-d",strtotime($POST['courier_del_date']));
	$info1['sc_cust_id']		= $POST['cust_id_hid'];
	$info1['sc_comp_id']		= $POST['comp_id_hid'];
	$info1['sc_remark']		= $POST['product_remark_old'];
	$info1['sc_user_id']			= $_SESSION['user_id'];
	$info1['sc_date']			= date("Y-m-d");

    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	
	$table='tbl_complain_close_spare_part';$tableid='s_id';
	
	
	if(empty($POST['edit_id'])) {
		$inserid=add_record($table, $info1, $dbcon, $branch_id);
	}
	else {
		$updateid=update_record($table, $info1, $tableid."=".$POST['edit_id'], $dbcon, $branch_id);
	} 
}
else if(brp_strtolower($POST['mode']) == "load_close_spare_part_data") {
	
	$complaint_id=$POST['complaint_id'];
	
	$query="select pr.s_id,pr.sc_comp_id,pr.sc_cust_id,pr.courier_name,pr.courier_no,pr.courier_del_date,pr.sc_user_id,pr.sc_date,pr.sc_comp_product_id,pr.sc_product,pr.sc_qty,pr.sc_rate,pr.sc_amount,pr.sc_remark,pr.s_return_status,pm.product_name as nproduct,pm1.product_name as cproduct from tbl_complain_close_spare_part as pr inner join product_mst as pm on pr.sc_product=pm.product_id inner join product_mst as pm1 on pr.sc_comp_product_id=pm1.product_id where pr.sc_comp_id=$complaint_id";
	
	$result=$dbcon->query($query);
	if(brp_mysqli_num_rows($result)>0)
	{
		$i=1;
		while($rel=brp_mysqli_fetch_assoc($result))
		{	 
			if($rel['s_return_status']==0)
			{
				$status="<a class='btn btn-xs btn-danger'>Not Returned</a>";
			}
			else
			{
				$status="<a class='btn  btn-xs btn-success'>Returned</a>";
			}
			
			echo '<tr>
				<td style="vertical-align:top;">
					'.$rel['cproduct'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['nproduct'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['sc_qty'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['sc_rate'].'
				</td>
				<td style="vertical-align:top;">
					'.$rel['sc_amount'].'
				</td>
				<td style="vertical-align:top;">
					Courier Name : '.$rel['courier_name'].'
					<br>Courier No. : '.$rel['courier_no'].'
					<br>Courier Date : '.date("d/m/Y",strtotime($rel['courier_del_date'])).'
				</td>
				<td style="vertical-align:top;">
					'.$rel['sc_remark'].'
				</td>
				<td>
				'.$status.'
				</td>
				<td>
					<a href="#" onclick="changeStatus(\''.$rel['s_id'].'\',\''.$rel['s_return_status'].'\')" style="margin-top:5%;border-bottom:dotted blue thin">change Status</a>
				</td>
				<td>
					<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_close_part('.$rel['s_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button> 
					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_close_part('.$rel['s_id'].');" id="fieldremove'.$i.'">X</button>
				</td>	
			</tr>';
			$i++;
		}
	}
	else{
		echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	}
}
else if(brp_strtolower($POST['mode']) == "change_complaint_status") {
	
	$sid=$POST['sid'];
	$status=$POST['s_status'];
	$com_id=$POST['com_id'];
	
	if($status=='0')
	{
		$date=date("d/m/Y",strtotime($POST['date']));
		$dbcon->query("update tbl_complain_close_spare_part set s_return_status='1',s_return_date='$date' where s_id='$sid'");
		echo "1";
	}
	else if($status=='1')
	{
		$dbcon->query("update tbl_complain_close_spare_part set s_return_status='0',s_return_date='$date' where s_id='$sid'");
		echo "1";
	}
	
}
else if(brp_strtolower($POST['mode'])== "preedit_close") {
	$q = $dbcon ->query("SELECT * FROM tbl_complain_close_spare_part WHERE s_id='$POST[complaint_trn_id]'");
	$r = brp_mysqli_fetch_assoc($q);
	echo brp_json_encode($r);
}
else if(brp_strtolower($POST['mode'])== "delete_data_close_part") {
	
	$q=$dbcon->query("delete from tbl_complain_close_spare_part where s_id='$POST[complaint_trn_id]'");
	
	$row['res']="1";
	
	echo brp_json_encode($row);
}
else if(brp_strtolower($POST['mode']) == "add_operator") {
			
	$info['op_name']=$POST['op_name'];
	$info['op_mobile']=$POST['op_mobile'];
	$info['op_comp_id']=$POST['op_comp_id'];
	$info['op_cust_id']=$POST['op_cust_id'];
	$info['cdate']=date("Y-m-d H:i:s A");
	$info['user_id']=$_SESSION['user_id'];
	$info['company_id']=$_SESSION['company_id'];
	$edit_id=$POST['edit_id'];
	
	if($edit_id==0)
	{
		$inserusrid=add_record('tbl_operator_detail', $info, $dbcon);
	}
	else
	{
		$updateid=update_record('tbl_operator_detail', $info,"op_id=".$POST['edit_id'], $dbcon);	
	}
	
	echo "1";
}
	
else if(brp_strtolower($POST['mode']) == "show_operator_detail") {
	
	$cust_id=$POST['cust_id'];
	$comp_id=$POST['comp_id'];
	
	$str='';
	$str.="<table class='table table-bordered'>
		
		<tr>
			<th>#</th>
			<th>Operatro Name</th>
			<th>Operatro Mobile</th>
			<th>Action</th>
		</tr>";
	

	
	$cnt=1;
	$sel=$dbcon->query("select * from  tbl_operator_detail where op_cust_id='$cust_id' order by op_name ");
	while($row=brp_mysqli_fetch_array($sel))
	{
		$str.="<tr>
		
			<th>".$cnt."</th>
			<th>".$row['op_name']."</th>
			<th>".$row['op_mobile']."</th>
			<th>
				<a class='btn btn-xs btn-warning' data-original-title='Edit' data-toggle='tooltip' data-placement='top' onclick='edit_operator(".$row['op_id'].")'><i class='fa fa-pencil'></i></a>
				
				<a class='btn btn-xs btn-danger' data-original-title='Delete' data-toggle='tooltip' data-placement='top' onclick='delete_operator(".$row['op_id'].")'><i class='fa fa-trash-o'></i></a>
				<input type='hidden' name='operator_cnt[]' value='".$cnt."'>
			</th>
			
		</tr>";
		
		$cnt++;
	}
	
	$str.="</table>";
	
	echo $str;
}
else if(brp_strtolower($POST['mode'])== "preedit_operator")
{
	$q = $dbcon -> query("SELECT * FROM tbl_operator_detail WHERE op_id='$POST[id]'");
	$r = $q->fetch_assoc();
	echo brp_json_encode($r);
}
else if(brp_strtolower($POST['mode'])== "delete_data_operator")
{
	
	$deleteid=delete_record('tbl_operator_detail', "op_id=$POST[eid]", $dbcon);

	if($deleteid)
		echo "1";
	else
		echo "0";
}
?>