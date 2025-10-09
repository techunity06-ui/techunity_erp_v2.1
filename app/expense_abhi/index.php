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
		//print_r($_POST);
		//print_r($_FILES);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "fetch") {
		$s_date=explode(' - ',$POST['date']);
		$delete_btn=true;
		$where='';
		$_SESSION['expense']['filter']['payment_status']=$POST['report'];
			switch($POST['report'])
			{
				case '2' : $where.=" and paid_amount=0 ";break; //unpaid
				case '3' : $where.="  and g_total>paid_amount and paid_amount!=0 ";break; //partial paid
				case '4' : $where.="  and  g_total=paid_amount ";break; //paid
				default : $where.=""; //all
			}
			
			$where.="  and expense_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND expense_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			
			$appData = array();
			$i=1;
			$aColumns = array('cust.company_name','expmst.expense_date','expmst.invoice_no','expmst.g_total','expmst.paid_amount','expmst.mst_status','expmst.cdate','expmst.user_id','expmst.expenseid');
			$sIndexColumn = "expenseid";
			$isWhere = array("expmst.mst_status = 0".$where.check_user('expmst'));
			$sTable = "expense_mst as expmst";			
			$isJOIN = array('inner join  tbl_customer cust on expmst.vendorid=cust.cust_id');
			$hOrder = "expmst.expenseid desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				//$row_data[] = $id;
				$row_data[] = $row['invoice_no'];
				$row_data[] = $row['company_name'];
				$row_data[] = date('d M, Y',strtotime($row['expense_date']));				
				//$row_data[] = $row['city_name'];
				$row_data[] = $row['g_total'];
				/*if($row['g_total']>$row['paid_amount'])
				{
					
					if(empty($row['paid_amount']) || $row['paid_amount']=="0.00")
					{	
						$row_data[] = "<div class='external-event label label-danger ui-draggable' style='position: relative;'>Unpaid (".($row['g_total']).")</div>";
					}
					else
					{
						$row_data[] = "<div class='external-event label label-warning ui-draggable' style='position: relative;'>Partially Paid (".($row['g_total']-$row['paid_amount']).")</div>";
					}
				}
				else
				{
					$row_data[]="<div class='external-event label label-success ui-draggable' style='position: relative;'>Paid</div>";;
				}*/
					$addpayment='';$delete='';$edit='';
					if($delete_btn)//$pagename,$usetype,$permission,$dbcon
					{
						$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_expense('.$row['expenseid'].')"><i class="fa fa-trash-o"></i></button>';
					}
					/*if($row["g_total"]>$row["paid_amount"]){
						$addpayment='<a class="btn btn-xs btn-primary" data-original-title="Payable '.($row['g_total']-$row['paid_amount']).'" data-toggle="tooltip" data-placement="top" href="expensepaymentdirect/'.$row['expenseid'].'"><i class="fa fa-plus"></i></a>';
					}*/					
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'expense-update/'.$row['expenseid'].'"><i class="fa fa-pencil"></i></a>';
					$row_data[] = $edit.' '.$delete.' '.$addpayment;
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		
		else if(strtolower($POST['mode']) == "add") {		

			if($_SESSION['employee_id']=='0')
			{
				$ap_status='1';
			}
			else
			{
				$ap_status='0';
			}
			
			$test = explode('.', $_FILES["file"]["name"]);
			$ext = end($test);
			$name = rand(100, 999).'.' . $ext;
			$path='../../view/upload/expense_img/';
			$location = $path . $name;  
			move_uploaded_file($_FILES["file"]["tmp_name"], $location);
			
			$info['expense_date']	= date('Y-m-d',strtotime($POST['expense_date']));			
			$info['vendorid']		= $_SESSION['last_exp_cust_id'] = $POST['cust_id'];
			$info['exp_accountid']		= $POST['accountid'];
			$info['expense_complain']	= $_SESSION['last_exp_comp_id']	= $POST['comp_id'];
			$info['g_total']		= $POST['expense_amount'];
			$info['exp_formula']		= $POST['formulaid'];
			$info['paid_amount']		= $POST['paid_amount'];
			$info['tax_amt']		= $POST['tax_amt'];
			$info['expense_approve_status']		= $ap_status;
			$info['remark']			= $_POST['remark'];
			$info['paid_status']			= $POST['e_status'];
			$info['c_img']			= $name;
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			$info['emp_id']	= $_SESSION['employee_id'];
			
			$inserestimateid=add_record('tbl_expense_detail', $info, $dbcon);
			
			$info1['eh_ex_id']	= $inserestimateid;							
			$info1['eh_emp_id']	= $_SESSION['employee_id'];							
			$info1['eh_date']	= date("Y-m-d H:i:s");						
			$info1['eh_status']	= $ap_status;
			$info1['eh_remark']	= $_POST['remark'];
			$info1['eh_amount']	= $POST['paid_amount'];
			$info1['cdate']		= date("Y-m-d H:i:s");
			$info1['user_id']	= $_SESSION['user_id'];
			$info1['company_id']	= $_SESSION['company_id'];
			$info1['usertype_id']= $_SESSION['user_type'];
			
			add_record('tbl_expense_status_history', $info1, $dbcon);

			//Insert LOG
			$log_entry=common_log_entry($dbcon,"estimate_add",1,"tbl_expense_detail",$inserestimateid);

			/* Tax Transaction Table Entry Start*/		
			/*$query="select trn.expense_trnid,trn.formulaid,tax.tax_id,tax.tax_value,trn.expense_amount as product_amount from expense_trn as trn
			left join `formula_mst` as formula on formula.formulaid=trn.formulaid inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) where expense_mstid=".$inserestimateid." and trn_status=0";
			$rs_tax=$dbcon->query($query);
			add_product_tax_data($inserestimateid,'expense_trnid','expense',$rs_tax,$dbcon);
			/* Tax Transaction Table Entry End*/
			if($inserestimateid)
			{	
				$arr['msg']="1";
			}
			else
			{
				$arr['msg']="0";
			}
			echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "edit") {
			//if($_POST['token'] == $_SESSION['token']) 
						$info['expense_date']	= date('Y-m-d',strtotime($POST['expense_date']));			
						$info['vendorid']		= $POST['cust_id'];
						$info['exp_accountid']		= $POST['accountid'];
						$info['expense_complain']		= $POST['comp_id'];
						$info['g_total']		= $POST['expense_amount'];
						$info['exp_formula']		= $POST['formulaid'];
						$info['paid_amount']		= $POST['paid_amount'];
						$info['tax_amt']		= $POST['tax_amt'];
						$info['remark']			= $POST['remark'];
						$info['cdate']			= date("Y-m-d H:i:s");
						$info['user_id']		= $_SESSION['user_id'];
						$info['usertype_id']	= $_SESSION['user_type'];
						$info['emp_id']	= $_SESSION['employee_id'];
						$updateid=update_record('tbl_expense_detail', $info,"ex_id=".$POST['eid'] , $dbcon);
					if($updateid)
					{	
						
						$arr['msg']="update";
						$arr['eid']=$POST['eid'];
						//Insert LOG
						$log_entry=common_log_entry($dbcon,"estimate_add",2,"tbl_expense_detail",$POST['eid']);
					}
					else
						$arr['msg']=0;
			echo json_encode($arr);	
				
			
		}
		else if(strtolower($POST['mode']) == "request") {
			//if($_POST['token'] == $_SESSION['token']) 
						$info['expense_date']	= date('Y-m-d',strtotime($POST['expense_date']));			
						$info['vendorid']		= $POST['cust_id'];
						$info['exp_accountid']		= $POST['accountid'];
						$info['expense_complain']		= $POST['comp_id'];
						$info['g_total']		= $POST['expense_amount'];
						$info['exp_formula']		= $POST['formulaid'];
						$info['paid_amount']		= $POST['paid_amount'];
						$info['expense_approve_status']='0';
						$info['tax_amt']		= $POST['tax_amt'];
						$info['remark']			= $POST['remark'];
						$info['cdate']			= date("Y-m-d H:i:s");
						$info['user_id']		= $_SESSION['user_id'];
						$info['usertype_id']	= $_SESSION['user_type'];
						$info['emp_id']	= $_SESSION['employee_id'];
						$updateid=update_record('tbl_expense_detail', $info,"ex_id=".$POST['eid'] , $dbcon);
						
						$info1['eh_ex_id']	= $POST['eid'];							
						$info1['eh_emp_id']	= $_SESSION['employee_id'];							
						$info1['eh_date']	= date("Y-m-d H:i:s");						
						$info1['eh_status']	= '0';
						$info1['eh_remark']	= $POST['remark'];
						$info1['eh_amount']	= $POST['paid_amount'];
						$info1['cdate']		= date("Y-m-d H:i:s");
						$info1['user_id']	= $_SESSION['user_id'];
						$info1['company_id']	= $_SESSION['company_id'];
						$info1['usertype_id']= $_SESSION['user_type'];
						
						add_record('tbl_expense_status_history', $info1, $dbcon);
						
					
						//Insert LOG
						$log_entry=common_log_entry($dbcon,"estimate_add",2,"tbl_expense_detail",$POST['eid']);
						
						if($updateid)
						{	
							
							$arr['msg']="update";
							$arr['eid']=$POST['eid'];
						}
						else
							$arr['msg']=0;
						
						echo json_encode($arr);	
				
			
		}
		else if(strtolower($POST['mode']) == "delete") {
					
			$info['mst_status']	= 2;
			$info1['trn_status']	= 2;
			$updateestimateid=update_record('expense_mst', $info,"expenseid=".$POST['eid'] , $dbcon);	
			$updatetrancationid=update_record('expense_trn', $info1,"expense_mstid=".$POST['eid'] , $dbcon);
			$query="select paid_amount from expense_mst where expenseid=".$POST['eid'];	
			$rel_mst=mysqli_fetch_assoc($dbcon->query($query));
			if(intval($rel_mst['paid_amount'])>0 || !empty($rel_mst['paid_amount']))//check paid amount for payment entry
			{
				$info_rec['status']	= 2;
				$updateestimateid=update_record('tbl_purchasereceipt', $info_rec,"purchasebill_id=".$POST['eid']." and receipt_flag='expense'" , $dbcon);
				$query="select group_concat(purchasereceipt_id) as receipt_trnid from tbl_purchasereceipt where purchasebill_id=".$POST['eid']." and receipt_flag='expense'";	
				$rel_mst=mysqli_fetch_assoc($dbcon->query($query));	
				$updateestimateid=update_record('tbl_passbookentry', $info_rec,"trn_id in (".$rel_mst['receipt_trnid'].") and trn_table='tbl_purchasereceipt'" , $dbcon);
			}
			
	
				
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		
		else if(strtolower($POST['mode']) == "change_tax_type") {
		if(!empty($POST['eid']))
			{
				$where=' expense_mstid='.$POST['eid'];
			}
			else
			{
				$where=' trn.trn_status=3';
			}
		   $query="SELECT trn.* FROM `expense_trn` as trn where ".$where."  and trn.user_id=".$_SESSION['user_id'];
		   $rs=$dbcon->query($query);
		   while($rel=mysqli_fetch_assoc($rs))
		   {
			   $info_up=array();
				if($_POST['tax_type']=="inclusive")
				{
					$info= get_product_tax_income($dbcon,$rel['expense_amount'],$rel['formulaid'],$_POST['tax_type']);
					//$info_up=array_merge($info_up,$info);
					$info_up['total']		= $rel['expense_amount'];
					$info_up['expense_amount']	= $info['total'];
				}
				else
				{
					$info= get_product_tax_income($dbcon,$rel['total'],$rel['formulaid'],$_POST['tax_type']);
					//$info_up=array_merge($info_up,$info);
					$info_up['total']=$info['total'];
					$info_up['expense_amount']= $rel['total'];
				}	
				 update_record('expense_trn', $info_up,"expense_trnid=".$rel['expense_trnid'], $dbcon);
			 }
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
			
				$info1['account_mst_id']	= $POST['accountid'];
				$info1['expense_grp']	= get_group_from_expense($dbcon,$POST['accountid']);
				$info1['expense_amount']	= $POST['expense_amount'];
				$info1['expense_notes']		= stripslashes($POST['expense_notes']);
			 	$info1['formulaid']			= $POST['formulaid'];
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['total']				= $POST['expense_gtotal'];
				$info1['emp_transfer']		= $POST['emp_transfer'];
				$info=get_product_tax_common($dbcon,$POST['expense_amount'],$POST['formulaid']);
				$info1=array_merge($info1,$info);
				
				$info1['total']				= $POST['expense_gtotal'];
				$info1['expense_amount']		= $POST['expense_amount'];
			
				$table='expense_trn';$tableid='expense_trnid';
				if(!empty($POST['expenseid']))
				{
						$info1['expense_mstid']= $POST['expenseid'];
				}
				else
				{
					$info1['trn_status']	= 3;
				}
				if(empty($POST['edit_id']))
				{
					$inserid=add_record($table, $info1, $dbcon);
				}
				else
				{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				}
			}
			else if(strtolower($POST['mode']) == "formulavalue") {
			$rate_total=0;$c_total=$POST['c_total'];
			$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$POST['eid']." order by tax_value desc";
			$row=$dbcon->query($qry);
			$j=0;
				//$dis=$POST['total']*$POST['t_dis']/100;
				$rate_total=$total=$POST['total'];
			while($tax=mysqli_fetch_assoc($row))
			{	
				if(strpos(strtolower(" ".$tax['tax_name']), "excise")==true)
				{
					$rate=$total*$tax['tax_value']/100;
					
					$total+=$rate;
				}
				else	
				{
					 $rate=($total)*$tax['tax_value']/100;
				}
				$rate=round($rate,2,PHP_ROUND_HALF_UP);
				echo '<div class="form-group">
								<label class="col-md-3 control-label">'.$tax['tax_name'].'</label>
								<div class="col-md-6 col-xs-11">
								<input id="taxvalue'.$j.'" name="taxvalue'.$j.'" value= "'.$rate.'"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname'.$j.'" name="taxname'.$j.'" value= "'.$tax['tax_name'].'" type="hidden" class="form-control">';
					$rate_total=$rate_total+$rate;
					$j++;
			}
			$g_total=$rate_total+$c_total;
			echo '<input id="rate" name="rate" value= "'.$g_total.'" type="hidden" class="form-control" >';
		}
		else if(strtolower($POST['mode'])== "getproduct_amount")
		{
			$arr=get_product_tax_common($dbcon,$POST['amount'],$POST['formulaid']);
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") 
		{
			if(!empty($POST['expenseid']))
			{
				$where=' expense_mstid='.$POST['expenseid'];
			}
			else
			{
				$where=' trn.trn_status=3';
			}
		  $query="SELECT trn.*,e.expense_name FROM `expense_trn` as trn inner join expense_mst as e on e.expense_id=trn.account_mst_id  where ".$where."  and trn.user_id=".$_SESSION['user_id'];
			$result=$dbcon->query($query);
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center"width="30%"><span class="english">Expense Accounts</span></th>
							<th class="text-center" width="12%"><span class="english">Amount</span></th>
							<th class="text-center"width="12%"><span class="english">Tax</span></th>
							<th class="text-center"width="12%"><span class="english">Total Amount</span></th>
							<th class="text-center"width="20%"><span class="english">Notes</span></th>
							<th class="text-center"width="7%"><span class="english">Action</span></th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				$tax_arr=get_product_tax_common_expense($dbcon,$rel['expense_amount'],$rel['formulaid'],'exclusive');
				$combine_arr=array_map(map_arr, $tax_arr['tax_name'], $tax_arr['tax_amount']);
				$tax_str=implode(" <br/>",$combine_arr);
			 echo '<tr id="fieldtr'.$id.'" >
					<td style="vertical-align:top;">
						'.$rel['expense_name'].'
					</td>
					<td style="vertical-align:top;">
							'.$rel['expense_amount'].'
					</td>
						
					<td style="vertical-align:top" class="text-left">
						'.$tax_str.'
						</td>
					<td style="vertical-align:top;">
						'.$rel['total'].'
					</td>					
					<td style="vertical-align:top;">
						'.$rel['expense_notes'].'
					</td>
					<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.$rel['total'].'"/>
					 <td style="vertical-align:top">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['expense_trnid'].',\' expense_trn\',\'expense_trnid\');" id="fieldremove'.$id.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['expense_trnid'].',\' expense_trn\',\'expense_trnid\');" id="fieldremove'.$id.'"><i class="fa fa-trash-o"></i></button>
					</td>	
			</tr>';
			$i++;
			}
		}
		else{
		echo '<tr><td colspan="7" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '
	 
		</table>			 
							</div>
                           
							</div>	';
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$q = $dbcon -> query("SELECT mst.*,pro.account_name FROM ".$_POST['table']." as mst left join mst_accounts as pro on mst.account_mst_id=pro.accountid WHERE ".$_POST['whereid']." = '$POST[id]'");
			$r = $q->fetch_assoc();
			
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['trn_status']=2;	
			$updateid=update_record($_POST['table'], $info,$_POST['whereid']."=".$POST['eid'] , $dbcon);
			if($updateid){
				$row['res']="1";
			}
			else
			{
				$row['res']="0";
			}
			echo json_encode($row);
		}
    }
    /*else {
        die("Error - 2");
    }*/
}

/*
else {
    die("Error - 1");
}*/
?>
