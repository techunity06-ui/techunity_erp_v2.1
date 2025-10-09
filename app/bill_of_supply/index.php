<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include_once("../../include/common_functions.php");
include("../../include/function_database_query.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FINANCE_BILL_OF_SUPPLY_EDIT,
	FINANCE_BILL_OF_SUPPLY_PRINT,
	FINANCE_BILL_OF_SUPPLY_DELETE
]);

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	if(strtolower($POST['mode']) == "fetch") {
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where='';
		
		
		if(!empty($POST['type_id']))
		{
			$where .=" and invoice.invoicetype_id=".$POST['type_id'];
		}
		$where.="  and bill_of_supply_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND bill_of_supply_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		$appData = array();
		$i=1;
		$aColumns = array('bill_of_supply_id','bill_of_supply_no','bill_of_supply_date','cust.l_name','g_total','paid_amount','bill_of_supply_status','invoice.cdate','invoice.user_id','invoice.usertype_id','invoice.invoicetype_id','invoice.gst_flag');
		$sIndexColumn = "bill_of_supply_id";
		$isWhere = array("bill_of_supply_status = 0".$where.check_user('invoice'));
		$sTable = "tbl_bill_of_supply as invoice";			
		$isJOIN = array('inner join tbl_ledger cust on invoice.cust_id=cust.l_id');
		$hOrder = "invoice.bill_of_supply_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			if(in_array(FINANCE_BILL_OF_SUPPLY_EDIT,$bulkAccessArray)){
				$row_data[] = '<a class="" data-original-title="Edit '.$row["bill_of_supply_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bill_of_supply_edit/'.$row['bill_of_supply_id'].'">'.$row["sr"].'</a>';
				$row_data[] = '<a class="" data-original-title="Edit '.$row["bill_of_supply_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bill_of_supply_edit/'.$row['bill_of_supply_id'].'">'.$row["bill_of_supply_no"].'</a>';
				$row_data[] = '<a class="" data-original-title="Edit '.$row["bill_of_supply_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bill_of_supply_edit/'.$row['bill_of_supply_id'].'">'.date('d M, Y',strtotime($row["bill_of_supply_date"])).'</a>';
				$row_data[] = '<a class="" data-original-title="Edit '.$row["bill_of_supply_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bill_of_supply_edit/'.$row['bill_of_supply_id'].'">'.$row["l_name"].'</a>';
				$row_data[] = '<a class="" data-original-title="Edit '.$row["bill_of_supply_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bill_of_supply_edit/'.$row['bill_of_supply_id'].'">'.$row["g_total"].'</a>';
			}
			
			$delete='';$edit='';
			
			if(in_array(FINANCE_BILL_OF_SUPPLY_PRINT,$bulkAccessArray)){
				$print='<a class="btn btn-xs btn-info" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bill_of_supply_print/'.$row['bill_of_supply_id'].'"><i class="fa fa-print"></i></a> ';
			}
			
			//$chln_print='<a class="btn btn-xs btn-success" data-original-title="Print Chalan" data-toggle="tooltip" data-placement="top" href="'.ROOT.'invoicechalan/'.$row['bill_of_supply_id'].'"><i class="fa fa-print"></i></a>';
			if(in_array(FINANCE_BILL_OF_SUPPLY_DELETE,$bulkAccessArray)){
				$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['bill_of_supply_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			if(in_array(FINANCE_BILL_OF_SUPPLY_EDIT,$bulkAccessArray)){
				$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bill_of_supply_edit/'.$row['bill_of_supply_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			
			$row_data[] = $print.' '.$edit.' '.$delete;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=11 and company_id=".$_SESSION['company_id']);
		
		$info['complaint_id']	= $POST['complaint_id'];
		$info['invoicetype_id']	= $POST['invoicetype_id'];
		$info['bill_of_supply_no']		= $POST['bill_of_supply_no'];
		$info['bill_of_supply_date']	= date('Y-m-d',strtotime($POST['bill_of_supply_date']));
		$info['order_no']		= $POST['order_no'];
		$info['order_date']		= date('Y-m-d',strtotime($POST['order_date']));
		
		$info['cust_id']		= $POST['cust_id'];
		$info['g_total']		= $POST['g_total'];
		
		/*$info['formulaid']	= $POST['formulaid'];
			$info['discount']		= $POST['discount_amt'];
			$info['discount_per']	= $POST['discount_per'];
			$info['tax1_name']		= $POST['taxname0'];
			$info['tax2_name']		= $POST['taxname1'];
			$info['tax3_name']		= $POST['taxname2'];
			$info['taxvalue1']		= $POST['taxvalue0'];
			$info['taxvalue2']		= $POST['taxvalue1'];
			$info['taxvalue3']		= $POST['taxvalue2'];
		$info['round_off']		= $POST['round_off'];*/
		$info['remark']			= ($_POST['remark']);
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		if(isset($POST['save_print']))
		{
			$info['print_status']	= $POST['print_status'];
		}
		$inserinvoiceid=add_record('tbl_bill_of_supply', $info, $dbcon);
		
		/*Update Trn Table Start*/
		if($inserinvoiceid){
			$infotrn['bill_of_supply_id']		= $inserinvoiceid;
			$infotrn['bill_of_supply_trn_status']	= 0;
			$updatetrnid=update_record('tbl_bill_of_supplytrn', $infotrn,"bill_of_supply_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
		}
		/*Update Trn Table End*/	
		
		if($POST['complaint_id']){
			$upd_spare_inv_sts=upd_spare_inv_sts($dbcon,$POST['complaint_id'],$inserinvoiceid);
		}
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"bill_of_supply_add",1,"tbl_bill_of_supply",$inserinvoiceid);	
		
		if(isset($POST['save_print'])){
			$arr['printstatus']=$POST['print_status'];
			$arr['msg']="1";
			$arr['eid']=$inserinvoiceid;
		}
		else{
			if($inserinvoiceid){	
				$arr['msg']="1";							
			}
			else{
				$arr['msg']="0";
			}
		}
		echo json_encode($arr);
	}		
	else if(strtolower($POST['mode']) == "edit") {
		
		$info['bill_of_supply_no']		= $POST['bill_of_supply_no'];
		$info['bill_of_supply_date']	= date('Y-m-d',strtotime($POST['bill_of_supply_date']));
		$info['order_no']		= $POST['order_no'];
		$info['order_date']		= date('Y-m-d',strtotime($POST['order_date']));
		
		$info['cust_id']		= $POST['cust_id'];
		$info['g_total']		= $POST['g_total'];
		
		/*$info['formulaid']		= $POST['formulaid'];
			$info['discount']		= $POST['discount_amt'];
			$info['discount_per']	= $POST['discount_per'];
			$info['tax1_name']		= $POST['taxname0'];
			$info['tax2_name']		= $POST['taxname1'];
			$info['tax3_name']		= $POST['taxname2'];
			$info['taxvalue1']		= $POST['taxvalue0'];
			$info['taxvalue2']		= $POST['taxvalue1'];
			$info['taxvalue3']		= $POST['taxvalue2'];
		$info['round_off']		= $POST['round_off'];*/
		$info['remark']			= ($_POST['remark']);
		
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		if(isset($POST['save_print'])){
			$info['print_status']	= $POST['print_status'];
		}
		$updateid=update_record('tbl_bill_of_supply', $info,"bill_of_supply_id=".$POST['eid'] , $dbcon);
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"bill_of_supply_add",2,"tbl_bill_of_supply",$POST['eid']);
		
		if(isset($POST['save_print'])){
			$arr['printstatus']=$POST['print_status'];
			$arr['msg']="update";
			$arr['eid']=$POST['eid'];
		}
		else{
			if($updateid){	
				$arr['msg']="update";
			}
			else{
				$arr['msg']=0;
			}
		}
		echo json_encode($arr);	
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['bill_of_supply_status']	= 2;
		$info1['bill_of_supply_trn_status']	= 2;
		$updateinvoiceid=update_record('tbl_bill_of_supply', $info,"bill_of_supply_id=".$POST['eid'] , $dbcon);	
		$updatetrancationid=update_record('tbl_bill_of_supplytrn', $info1,"bill_of_supply_id=".$POST['eid'] , $dbcon);	
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"bill_of_supply_add",3,"tbl_bill_of_supply",$POST['eid']);
		
		if($updatetrancationid)
		echo "1";	
		else
		echo "0";			
	}
	else if(strtolower($POST['mode']) == "fieldadd") {
		
		$info1['product_id']		= $POST['product_id'];
		$info1['description']		= $_POST['product_des'];
		$info1['product_hsn_code']	= $POST['product_hsn_code'];
		$info1['product_qty']		= $POST['product_qty'];
		$info1['product_rate']		= $POST['product_rate'];
		$info1['product_disc']		= $POST['product_disc'];
		$info1['unit_id']			= $POST['unit_id'];
		//$info1['product_amount']	= $POST['product_amount'];
		$info1['product_discount']	= $POST['product_discount'];
		$info1['discount_per']		= $POST['discount_per'];
		$info1['formulaid']			= $POST['formulaid'];
		$info1['company_id']		= $_SESSION['company_id'];
		$info1['product_amount']	= $POST['product_amount'];
		$info1['taxable_value']	= $POST['taxable_value'];
		$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
		$info1=array_merge($info1,$info);
		$info1['user_id']	= $_SESSION['user_id'];
		$table='tbl_bill_of_supplytrn';$tableid='bill_of_supply_trnid';
		if(!empty($POST['bill_of_supply_id'])){
			$info1['bill_of_supply_id']= $POST['bill_of_supply_id'];
		}
		else{
			$info1['bill_of_supply_trn_status']	= 3;
		}
		
		if(empty($POST['edit_id'])){
			$inserid=add_record($table, $info1, $dbcon);
		}
		else{
			$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
		}
	}
	else if(strtolower($POST['mode']) == "formulavalue") 
	{
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
			echo '<div class="form-group">
			<label class="col-md-5 control-label">'.$tax['tax_name'].'</label>
			<div class="col-md-5 col-xs-11">
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
	else if(strtolower($POST['mode'])== "load_productdata")
	{
		$pid=$POST['eid'];
		//$qry="select * from tbl_product where product_id=".$POST['eid'];
		$qry="select * from product_mst where product_id=$pid";
		$result=$dbcon->query($qry);
		$row=mysqli_fetch_assoc($result);
		
		echo json_encode( $row );
		
	}	
	else if(strtolower($POST['mode'])== "load_product_typeiwse")
	{
		echo get_product($dbcon,"",$POST['type_id']);
	}
	else if(strtolower($POST['mode'])== "get_product_amount")
	{
		$arr=get_product_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode'])== "load_invoiceno")
	{
		$row=array();
		$type_id=11;
		$query1="select * from tbl_invoicetype where status=0 and type_id=".$type_id." and company_id=".$_SESSION['company_id'];
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
	else if(strtolower($POST['mode']) == "load_tempoutward") {
		if($POST['eid']){
			$query="select mst.*,product.product_name,product.product_type,cat.unit_name from tbl_bill_of_supplytrn as mst
			left join unit_mst as cat on cat.unitid=mst.unit_id 
			left join product_mst as product on product.product_id=mst.product_id  
			where bill_of_supply_trn_status=0 and bill_of_supply_id=".$POST['eid'];
		}
		else{
			$query="select mst.*,product.product_name,product.product_type,cat.unit_name from tbl_bill_of_supplytrn as mst
			left join unit_mst as cat on cat.unitid=mst.unit_id 
			left join product_mst as product on product.product_id=mst.product_id  
			where bill_of_supply_trn_status=3 and mst.user_id=".$_SESSION['user_id'];
		}
		/*$query="select mst.*,product.product_name,cat.unit_name,m.model_name from  tbl_bill_of_supplytrntemp as mst 
		left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id left join model_mst as m on m.model_id=mst.model_id  where temp_status=0 and mst.user_id=".$_SESSION['user_id']." order by tempinvoicetrn_id Desc";*/
		$result=$dbcon->query($query);
		echo ' <div class="form-group">
		<div class="col-md-12 col-xs-11">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="25%">Product Name</th>
		<th class="text-center"width="8%">HSN Code</th>
		<th class="text-center"width="8%">Qty</th>
		<th class="text-center"width="8%">Rate</th>
		<th class="text-center"width="6%">Per</th>
		<th class="text-center"width="8%">Discount</th>
		<th class="text-center"width="15%">Tax</th>
		<th class="text-center"width="12%">Amount</th>
		<th class="text-center"width="10%">Action</th>
		</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				$product_name=$dbcon->real_escape_string($rel['product_name']);
				echo '<tr id="fieldtr'.$id.'" >
				
				<td style="vertical-align:top;">
					<b>'.$rel['product_name'].'</b><br/>
					'.nl2br($rel['description']).'
				</td>
				
				<td style="vertical-align:top;" class="text-center">
				'.$rel['product_hsn_code'].'
				</td>
				<td style="vertical-align:top;" class="text-center">
				'.$rel['product_qty'].'';
				
				/*if($rel['product_type']=='0'){
					echo '<br/><button type="button" class="btn btn-primary" onclick="open_inv_srl_no('.$rel['trancation_id'].',\''.$product_name.'\');" title="Add Serail No.">Serial No.</button>';
				}*/
				
				echo '</td>
				<td style="vertical-align:top;" class="text-right">
				'.$rel['product_rate'].'
				</td>				
				<td style="vertical-align:top" class="text-center">
				'.$rel['unit_name'].'
				</td>
				<td style="vertical-align:top" class="text-right">
				'.$rel['product_discount'].' ('.$rel['discount_per'].'%)
				</td>
				<td style="vertical-align:top" class="text-left">
				'.(empty($rel['tax_name1']) ? "" : $rel['tax_name1'].' : '.$rel['tax_amount1']).'<br/>
				'.(empty($rel['tax_name2']) ? "" : $rel['tax_name2'].' : '.$rel['tax_amount2']).'<br/>
				'.(empty($rel['tax_name3']) ? "" : $rel['tax_name3'].' : '.$rel['tax_amount3']).'<br/>
				</td>
				<td style="vertical-align:top" class="text-right">
				'.($rel['product_amount']).'
				</td>
				<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.$rel['product_amount'].'"/>
				<td style="vertical-align:top">
				<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['bill_of_supply_trnid'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['bill_of_supply_trnid'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
				</td>	
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}
		
		echo '</table>			 
		</div></div>	';
	}
	else if(strtolower($POST['mode'])== "preedit")
	{
		$q = $dbcon -> query("SELECT mst.* FROM tbl_bill_of_supplytrn as mst WHERE bill_of_supply_trnid = '$POST[id]'");
		$r = $q->fetch_assoc();
		
		//$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');
		
		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_data")
	{
		$row=array();
		$info['bill_of_supply_trn_status']=2;	
		$updateid=update_record("tbl_bill_of_supplytrn", $info, "bill_of_supply_trnid=".$POST['eid'] , $dbcon);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "load_qty")
	{
		echo getsale_productqty($dbcon,$POST['product_id']);	
	}
	else if(strtolower($POST['mode'])=="load_stock_qty")
	{
		$product_id=$POST['product_id'];
		echo get_product_stock($dbcon,$product_id);
	}
	else if(strtolower($POST['mode'])=="copy_comp_spare_trn_data"){
		$deleteid=delete_record('tbl_bill_of_supplytrn',"bill_of_supply_trn_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
		
		$qt_qry="select * from tbl_complain_spare_part where s_inv_status=0 and s_paid_status='free' and s_comp_id=".$POST['complaint_id'];
		$qt_qry_rs=$dbcon->query($qt_qry);
		while($qt_trn=mysqli_fetch_assoc($qt_qry_rs)){
			$info1=array();
			
			$info1['ref_s_id']			= $qt_trn['s_id'];
			$info1['product_id']		= $qt_trn['s_product'];
			//$info1['description']		= $qt_trn['product_desc'];
			$info1['product_qty']		= $qt_trn['s_qty'];
			$info1['product_rate']		= $qt_trn['s_rate'];
			//$info1['unit_id']			= $qt_trn['unit_id'];
			//$info1['product_discount']= $qt_trn['product_discount'];
			//$info1['discount_per']	= $qt_trn['discount_per'];
			$info1['formulaid']			= $qt_trn['formulaid'];
			$info1['product_amount']	= $qt_trn['s_amount'];
			$info1['taxable_value']		= $qt_trn['s_amount'];
			$info=get_product_tax($dbcon,$info1['product_amount'],$info1['formulaid']);
			$info1=array_merge($info1,$info);
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['bill_of_supply_trn_status']	= 3;
			$inserid=add_record('tbl_bill_of_supplytrn', $info1, $dbcon);
		}
		
	}

function get_product_tax($dbcon,$product_amount,$formulaid)
{
	$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;
	while($tax=mysqli_fetch_assoc($row))
	{	
		$info['tax_name'.$i]=$tax['tax_name'];
		$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
		$rate_total+=$tax_amount;
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$info['tax_name'.$j]='';
		$info['tax_amount'.$j]='';		
	}
	$info['total']=$rate_total;
	return $info;
}
function upd_spare_inv_sts($dbcon,$complaint_id,$bill_of_supply_id){
	//Update Quotation trn rows
	$upd_qt_trn_qry="update tbl_complain_spare_part set s_inv_status=1 where s_inv_status=0 and find_in_set(s_id,(select group_concat(ref_s_id) from tbl_bill_of_supplytrn where bill_of_supply_trn_status=0 and bill_of_supply_id=".$bill_of_supply_id."))";
	$upd_qt_trn_qry_rs=$dbcon->query($upd_qt_trn_qry);
}
?>