<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

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
		$where='';
		
		$where.="  and voucher_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND voucher_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('vchmst.voucher_mstid','vchmst.voucher_no','vchmst.voucher_date','vchmst.g_total','vchmst.remark','vchmst.cdate','vchmst.user_id','vchmst.voucher_mstid');
		$sIndexColumn = "voucher_mstid";
		$isWhere = array("vchmst.mst_status = 0".$where.check_user('vchmst'));
		$sTable = "account_voucher_mst as vchmst";			
		$isJOIN = array('');
		$hOrder = "vchmst.voucher_mstid desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['voucher_no'];
			$row_data[] = date('d M, Y',strtotime($row['voucher_date']));
			$row_data[] = $row['g_total'];		
			$row_data[] = nl2br($row['remark']);
			
			$delete='';$edit='';
			if($edit_btn_per){
				$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'account-voucher-update/'.$row['voucher_mstid'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per){
				$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_voucher('.$row['voucher_mstid'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit.' '.$delete;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$info['voucher_typeid']	= $POST['voucher_typeid'];
		$info['voucher_no']		= $POST['voucher_no'];
		$info['voucher_date']	= date('Y-m-d',strtotime($POST['voucher_date']));			
		$info['remark']			= $_POST['remark'];
		$info['g_total']		= $POST['debit_total'];
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		$inserestimateid=add_record('account_voucher_mst', $info, $dbcon);
		
		/** TRN Entry Start ***/
		if($inserestimateid){
			$info_trn['trn_status']  	= 0;
			$info_trn['voucher_mstid'] = $inserestimateid;
			$updatetrnid=update_record('account_voucher_trn', $info_trn,"trn_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
		}
		insert_general_book($dbcon,$inserestimateid);
		//insert_general_book($dbcon,$inserestimateid);
		/** TRN Entry End ***/
		
		if($inserestimateid){
			$arr['msg']="1";
		}
		else{
			$arr['msg']="0";
		}
		echo json_encode($arr);
	}		
	else if(strtolower($POST['mode']) == "edit") {
		$info['voucher_date']	= date('Y-m-d',strtotime($POST['voucher_date']));			
		$info['voucher_typeid']	= $POST['voucher_typeid'];
		$info['voucher_no']		= $POST['voucher_no'];
		$info['g_total']		= $POST['debit_total'];
		$info['remark']			= $_POST['remark'];
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		$updateid=update_record('account_voucher_mst', $info,"voucher_mstid=".$POST['eid'] , $dbcon);
		
		//insert_general_book($dbcon,$POST['eid']);
		if($updateid) {	
			$arr['msg']="update";
			$arr['eid']=$POST['eid'];
		}
		else{
			$arr['msg']=0;
		}
		echo json_encode($arr);	
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['mst_status']	= 2;
		$info1['trn_status']	= 2;
		$updatemstid=update_record('account_voucher_mst', $info,"voucher_mstid=".$POST['eid'] , $dbcon);	
		$updatetrnid=update_record('account_voucher_trn', $info1,"voucher_mstid=".$POST['eid'] , $dbcon);	

		$qry122="select * from account_voucher_trn as cert where trn_status=0 and voucher_mstid=".$POST['eid'];
			$ro12=$dbcon->query($qry122);
			$info_gen['genral_book_status']	= 2;
			while($rea=mysqli_fetch_assoc($ro12)){
				
				$updatemstid12=update_record('tbl_general_book', $info_gen,"table_name='account_voucher_trn' and table_id=".$rea['voucher_trnid'] , $dbcon);	
			}
	
			
		if($updatetrnid)
			echo "1";	
		else
			echo "0";			
	}
	else if(strtolower($POST['mode']) == "fieldadd") {
		
		$info1['type_id']		= $POST['type_id'];
		$info1['l_id']			= $POST['l_id'];
		if($POST['type_id']=='1'){
			$info1['cr_amount']	= $POST['input_amt'];
		}
		else{
			$info1['dr_amount']	= $POST['input_amt'];
		}
		
		$info1['user_id']	= $_SESSION['user_id'];
		$table='account_voucher_trn';$tableid='voucher_trnid';
		if(!empty($POST['voucher_mstid'])) {
			$info1['voucher_mstid']= $POST['voucher_mstid'];
		}
		else {
			$info1['trn_status']	= 3;
		}
		
		if(empty($POST['edit_id'])) {
			$inserid=add_record($table, $info1, $dbcon);
		}
		else {
			$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
		}
	}
	else if(strtolower($POST['mode']) == "load_tempoutward") 
	{
		if($POST['voucher_mstid']) {
			$query="SELECT trn.*,ledger.l_name FROM `account_voucher_trn` as trn 
			left join tbl_ledger as ledger on ledger.l_id=trn.l_id 
			where trn_status=0 and trn.voucher_mstid=".$POST['voucher_mstid'];
		}
		else {
			$query="SELECT trn.*,ledger.l_name FROM `account_voucher_trn` as trn 
			left join tbl_ledger as ledger on ledger.l_id=trn.l_id 
			where trn_status=3 and trn.user_id=".$_SESSION['user_id'];
		}
		
		$result=$dbcon->query($query);
		echo ' <div class="form-group">
		<div class="col-md-12 col-xs-11">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="15%">Type</th>
		<th class="text-center" width="50%">Ledger</th>
		<th class="text-center width="14%">Debit Amount</th>
		<th class="text-center" width="14%">Credit Amount</th>
		<th class="text-center" width="7%">Action</th>
		</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				echo '<tr>
				<td class="text-center" style="vertical-align:top;">
					'.(($rel['type_id']=='1')?'Credit':'Debit').'
				</td>
				<td style="vertical-align:top;">
					'.$rel['l_name'].'
				</td>
				<td class="text-right" style="vertical-align:top;">
					'.($rel['type_id']==1?$rel['cr_amount']:'').'
				</td>					
				<td class="text-right" style="vertical-align:top;">
					'.($rel['type_id']==2?$rel['dr_amount']:'').'
				</td>
					<input type="hidden" name="credit_amount[]" id="credit_amount'.$i.'" value="'.($rel['type_id']==1?$rel['cr_amount']:'').'"/>
					<input type="hidden" name="debit_amount[]" id="debit_amount'.$i.'" value="'.($rel['type_id']==2?$rel['dr_amount']:'').'"/>
				<td style="vertical-align:top">
					<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['voucher_trnid'].');" ><i class="fa fa-pencil"></i></button>
					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['voucher_trnid'].');"><i class="fa fa-times"></i></button>
				</td>	
				</tr>';
				($rel['type_id']==1?$credit+=$rel['cr_amount']:$debit+=$rel['dr_amount']);
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="7" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '
		<tr>
		<td colspan="2" class="text-right">
		<strong>Total :</strong>
		</td>
		<td class="text-right">
			<input id="credit_total" name="credit_total" type="text" class="form-control" value="'.$credit.'" placeholder="Grand Total" readonly="readonly">
		</td>
		<td class="text-right">
			<input id="debit_total" name="debit_total" type="text" class="form-control" value="'.$debit.'" placeholder="Grand Total" readonly="readonly">
		</td>
		<td></td>
		</tr>	
		</table>			 
		</div>
		
		</div>	';
	}
	else if(strtolower($POST['mode'])== "preedit")
	{
		$q = $dbcon -> query("SELECT mst.* FROM account_voucher_trn as mst WHERE voucher_trnid = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_data")
	{
		$row=array();
		$info['trn_status']=2;	
		$updateid=update_record("account_voucher_trn", $info, "voucher_trnid=".$POST['eid'], $dbcon);
		if($updateid){
			$row['res']="1";
		}
		else {
			$row['res']="0";
		}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "load_account")
	{
		if($POST['voucher_typeid']=="2")
		{
			$arr['data']=get_all_acc_type($dbcon,$rel['accountid'],' and pid=6');
		}
		else
		{
			$arr['data']=get_all_acc_type($dbcon,0);
		}
		echo json_encode($arr);
	}
function insert_general_book($dbcon,$journal_id)
{
	$qry122="select * from account_voucher_mst as cert where mst_status=0 and voucher_mstid=".$journal_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	$query="select * from account_voucher_trn as mst 
			where trn_status=0 and mst.voucher_mstid=".$journal_id;
		    $result=$dbcon->query($query);
			$table_name="account_voucher_trn";
			while($rel=mysqli_fetch_assoc($result))
			{
				$general_book_id=get_general_book_id($dbcon,$table_name,$rel['voucher_trnid'],$rel['l_id']);
				if($rel['type_id']==1){$amou=$rel['cr_amount'];}else{$amou=$rel['dr_amount'];}
				$info1['ref_date']		= date("Y-m-d",strtotime($rea['voucher_date']));
				$info1['table_name']	= $table_name;
				$info1['table_id']		= $rel['voucher_trnid'];
				$info1['entry_type']	= $rel['type_id'];
				$info1['ledger_id']		= $rel['l_id'];
				$info1['amount']		= $amou;
				$info1['user_id']		= $_SESSION['user_id'];
				$info1['cdate']			= date("Y-m-d H:i:s");
				$info1['company_id']	= $_SESSION['company_id'];
				
				if(empty($general_book_id)){
					$inserid_gen=add_record("tbl_general_book", $info1, $dbcon);
				}else{
					$updateid=update_record('tbl_general_book', $info1,"general_book_id=".$general_book_id , $dbcon);
				}
				
				//$inserid=add_record("tbl_general_book", $info1, $dbcon);
			}
}  
	?>
		