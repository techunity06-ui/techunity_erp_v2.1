<?php
session_start();
$AJAX = true;

$path = '../../../';
$include = '../../../include/';

include($path."config/config.php");
//error_reporting(E_ALL);
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
		
	if(strtolower($POST['mode']) == "fetch") {
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
			DEBIT_PENDING_NOTE_UPDATE,DEBIT_PENDING_NOTE_DELETE
		]);
		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$branch=$_SESSION['branch_id'];
		$where='';
			/*if($POST['report']=='all')
			{
				$where.="  and debitnote_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND debitnote_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}
			if($POST['report']=='paid')
			{
				$where.=" and  g_total=paid_amount and debitnote_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND debitnote_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}
			if($POST['report']=='due')
			{
				$where.="  and g_total>paid_amount and debitnote_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND debitnote_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}*/
			$where.="  and debitnote_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND debitnote_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			
			//$where.=" and po.branch_id=$branch";
			$where.=" and po.company_id=$_SESSION[company_id]";
			
			$appData = array();
			$i=1;
			$aColumns = array('debitnote_id','debitnote_no','debitnote_ref_no','debitnote_date','l.l_name','city.city_name','g_total','paid_amount','debit_note_status','po.cdate','po.userid');
			$sIndexColumn = "debitnote_id";
			$isWhere = array("debit_note_status = 0".$where);
			$sTable = "tbl_debitnote as po";			
			$isJOIN = array('inner join  tbl_ledger as l on po.vender_id=l.l_id','left join  city_mst city on l.cityid=city.cityid');
			$hOrder = "po.debitnote_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				if(in_array(DEBIT_PENDING_NOTE_UPDATE,$bulkAccessArray)){
					$row_data[] = '<a class="" data-original-title="Edit '.$row["debitnote_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'debitnote_edit/'.$row['debitnote_id'].'">'.$row["debitnote_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["debitnote_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'debitnote_edit/'.$row['debitnote_id'].'">'.$row["debitnote_ref_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["debitnote_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'debitnote_edit/'.$row['debitnote_id'].'">'.date('d M, Y',strtotime($row["debitnote_date"])).'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["debitnote_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'debitnote_edit/'.$row['debitnote_id'].'">'.$row["l_name"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["debitnote_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'debitnote_edit/'.$row['debitnote_id'].'">'.$row["city_name"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["debitnote_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'debitnote_edit/'.$row['debitnote_id'].'">'.$row["g_total"].'</a>';
					
				}else{
					$row_data[] = $row['debitnote_no'];
					$row_data[] = $row['debitnote_ref_no'];
					$row_data[] = date('d M, Y',strtotime($row['debitnote_date']));
					$row_data[] = $row['l_name'];
					$row_data[] = $row['city_name'];
					$row_data[] = $row['g_total'];
				
				}
				
				$edit='';$delete='';$view='';
				//$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchase_view/'.$row['debitnote_id'].'"><i class="fa fa-eye"></i></a> ';
				
				//$mrn_btn=' <button class="btn btn-xs btn-primary" data-original-title="View MRN" data-toggle="tooltip" data-placement="top" onClick="get_mrn('.$row['debitnote_id'].')"><i class="fa fa-bars"></i></button>'; 
				
				//$poprint='<a class="btn btn-xs btn-primary" data-original-title="Print Debit Note" data-toggle="tooltip" data-placement="top" href="'.ROOT.'debit_note_print/'.$row['debitnote_id'].'"><i class="fa fa-print"></i></a>';
				
				if(in_array(DEBIT_PENDING_NOTE_UPDATE,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'debitnote_edit/'.$row['debitnote_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				if(in_array(DEBIT_PENDING_NOTE_DELETE,$bulkAccessArray)){
					$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_debitnote('.$row['debitnote_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
				$row_data[] = $edit.' '.$delete.' '.$view;
			 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=13 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
			//$trn_purchaseorder_id_up = $POST['trn_purchaseorder_id_up'];
			
			$info['debitnote_type']	= $POST['debitnote_type'];
			$info['debitnote_no']		= $POST['debitnote_no'];
			$info['vender_id']	= $POST['vender_id'];
			$info['debitnote_date']	= date('Y-m-d',strtotime($POST['debitnote_date']));
			$info['debitnote_ref_no']	= $POST['debitnote_ref_no'];
			//$info['order_date']	= date('Y-m-d',strtotime($POST['order_date']));
			$info['round_off']	= $POST['round_off'];
			$info['packing']	= $POST['paking'];
			$info['remark']		= $_POST['remark'];
			$info['g_total']	= $POST['g_total'];
			//$info['exp_total']	= $POST['exp_total'];
			/*$info['formulaid']	= $POST['formulaid'];
			$info['discount']	= $POST['discount'];
			$info['tax1_name']	= $POST['taxname0'];
			$info['tax2_name']	= $POST['taxname1'];
			$info['tax3_name']	= $POST['taxname2'];
			$info['taxvalue1']	= $POST['taxvalue0'];
			$info['taxvalue2']	= $POST['taxvalue1'];
			$info['taxvalue3']	= $POST['taxvalue2'];*/
			
			if(isset($POST['save_print'])){
				$info['print_status']	= $POST['print_status'];
			}
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['mdate']				= date("Y-m-d H:i:s");
			$info['userid']				= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			//$info['usertype_id']		= $_SESSION['user_type'];
			//$info['branch_id']		= $POST['branchid'];
			$inserpoid=add_record('tbl_debitnote', $info, $dbcon);
			
			if($inserpoid){
				$inftrn['debitnote_id'] = $inserpoid;
				$inftrn['debitnote_trn_status'] = 0;
				$updatetrnid=update_record('tbl_debitnote_trn', $inftrn,"user_id=".$_SESSION['user_id']." and  debitnote_trn_status=3" , $dbcon);
			}

			if(isset($POST['save_print'])) {
				$arr['printstatus']=$POST['print_status'];
				$arr['msg']="1";
				$arr['eid']=$inserpoeid;
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"debitnote_add",1,"tbl_debitnote",$inserpoid);
			}
			else {
				if($inserpoid) {	
					$arr['msg']="1";							
				}
				else
					$arr['msg']="0";
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode']) == "edit") {
			 
			$info['debitnote_no']		= $POST['debitnote_no'];
			$info['vender_id']		= $POST['vender_id'];
			$info['debitnote_date']	= date('Y-m-d',strtotime($POST['debitnote_date']));
			$info['debitnote_ref_no']	= $POST['debitnote_ref_no'];
			//$info['order_date']	= date('Y-m-d',strtotime($POST['order_date']));
			$info['round_off']	= $POST['round_off'];
			$info['packing']	= $POST['paking'];
			$info['remark']		= $_POST['remark'];
			//$info['exp_total']	= $POST['exp_total'];
			$info['g_total']	= $POST['g_total'];
			/*$info['formulaid']	= $POST['formulaid'];
			$info['discount']	= $POST['discount'];
			$info['tax1_name']	= $POST['taxname0'];
			$info['tax2_name']	= $POST['taxname1'];
			$info['tax3_name']	= $POST['taxname2'];
			$info['taxvalue1']	= $POST['taxvalue0'];
			$info['taxvalue2']	= $POST['taxvalue1'];
			$info['taxvalue3']	= $POST['taxvalue2'];*/
			if(isset($POST['save_print'])) {
				$info['print_status']	= $POST['print_status'];
			}
			$info['cdate']				= 	date("Y-m-d H:i:s");
			$info['userid']			= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$updateid=update_record('tbl_debitnote', $info,"debitnote_id=".$POST['eid'] , $dbcon);
	
			if(isset($POST['save_print'])) {
				$arr['printstatus']=$POST['print_status'];
				$arr['msg']="update";
				$arr['eid']=$POST['eid'];
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"debitnote_add",2,"tbl_debitnote",$POST['eid']);
			}
			else {
				if($updateid) {	
					$arr['msg']="update";
				}
				else
					$arr['msg']=0;
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['debit_note_status']		= 2;
			$info1['debitnote_trn_status']		= 2;
			
			$updateinvoiceid=update_record('tbl_debitnote', $info,"debitnote_id=".$POST['eid'] , $dbcon);	
			$updatetrancationid=update_record('tbl_debitnote_trn', $info1,"debitnote_id=".$POST['eid'] , $dbcon);	

			//Insert LOG
			$log_entry=common_log_entry($dbcon,"debitnote_add",3,"tbl_debitnote",$POST['eid']);

			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode'])== "load_productdata")
		{
			//$qry="select popro.*, from tbl_purchaseproduct as porpo left join tbl_company as com on com.company_id=".$_SESSION['company_id']." where product_id=".$POST['eid'];
			$qry="select popro.*,com.stateid as com_stateid,ven.stateid as ven_stateid from `product_mst` as popro left join `tbl_company` as com on com.company_id=".$_SESSION['company_id']." left join tbl_ledger as ven on ven.l_id=".$POST['vender_id']." where product_id=".$POST['eid'];
			$result=$dbcon->query($qry);
			$row=mysqli_fetch_assoc($result);
			echo json_encode($row);
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
					$rate=number_format($rate,2,".","");
				}
				else	
				{
					 $rate=($total)*$tax['tax_value']/100;
					 $rate=number_format($rate,2,".","");
				}
				echo '<div class="form-group">
								<label class="col-md-6 control-label">'.$tax['tax_name'].'</label>
								<div class="col-md-4 col-xs-11">
								<input id="taxvalue'.$j.'" name="taxvalue'.$j.'" value= "'.$rate.'"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname'.$j.'" name="taxname'.$j.'" value= "'.$tax['tax_name'].'" type="hidden" class="form-control">';
					$rate_total=$rate_total+$rate;
					$j++;
			}
			$g_total=$rate_total+$c_total;
			$g_total=number_format($g_total,2,".","");

			echo '<input id="rate" name="rate" value= "'.$g_total.'" type="hidden" class="form-control" >';
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
			
				$info1['grn_id']			= $POST['grn_id'];
				$info1['product_id']		= $POST['product_id'];
				$info1['description']		= $_POST['product_des'];
				$info1['product_qty']		= $POST['product_qty'];
			 	$info1['unit_id']			= $POST['unit_id'];
				$info1['product_rate']		= $POST['product_rate'];
				$info1['product_discount']	= $POST['product_discount'];
				$info1['discount_per']		= $POST['discount_per'];
				$info1['formulaid']			= $POST['formulaid'];
				$info1['sel_tax']			= $_POST['sel_tax'];
				$info1['product_amount']	= $POST['taxable_value'];
				$info1['total']				= $POST['product_amount'];
				//$info1['company_id']		= $_SESSION['company_id'];
				$info1['user_id']			= $_SESSION['user_id'];
				//$info=get_product_tax($dbcon,$total,$POST['formulaid']);
				//$info1=array_merge($info1,$info);
				//$info1['total']=$total;
				
			$table='tbl_debitnote_trn';$tableid='debitnote_trn_id';	
			if(!empty($POST['debitnote_id'])) {
				$info1['debitnote_id'] = $POST['debitnote_id'];
			}
			else {
				$info1['debitnote_trn_status'] = 3;
			}
			
			if(empty($POST['edit_id'])) {
				$inserid=add_record($table, $info1, $dbcon);
			}
			else {
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			if($POST['debitnote_id']){
				$query="select trn.*,product.product_name,cat.unit_name,grn.grn_no from tbl_debitnote_trn as trn
				   left join unit_mst as cat on cat.unitid=trn.unit_id 
				   left join product_mst as product on product.product_id=trn.product_id  
				   left join tbl_grn as grn on grn.grn_id=trn.grn_id 
				   where trn.debitnote_trn_status=0 and trn.debitnote_id=".$POST['debitnote_id'];
			}
			else{
				$query="select trn.*,product.product_name,cat.unit_name,grn.grn_no from tbl_debitnote_trn as trn
				   left join unit_mst as cat on cat.unitid=trn.unit_id 
				   left join product_mst as product on product.product_id=trn.product_id  
				   left join tbl_grn as grn on grn.grn_id=trn.grn_id 
				   where trn.debitnote_trn_status=3 and trn.user_id=".$_SESSION['user_id'];
			}
		
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
					  <div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center grn" width="10%">GRN</th>
							<th class="text-center" width="20%">Product Name</th>
							<th class="text-center" width="8%">Qty</th>
							<th class="text-center" width="10%">Rate</th>
							<th class="text-center" width="6%">Per</th>
							<th class="text-center" width="8%">Discount</th>
							<th class="text-center" width="10%">Taxable value</th>
							<th class="text-center" width="15%">Tax</th>
							<th class="text-center" width="12%">Amount</th>
						 	<th class="text-center" width="10%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				
			 echo '<tr id="'.$id.'"  class="grn">
					<td style="vertical-align:top;">
						'.$rel['grn_no'].'
					</td>
					<td style="vertical-align:top;">
						'.$rel['product_name'].'
						'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['product_qty'].'
					</td>					
					<td style="vertical-align:top;" class="text-right">
						'.$rel['product_rate'].'
					</td>				
					<td style="vertical-align:top" class="text-center">
						'.$rel['unit_name'].'
					</td>
					<td style="vertical-align:top" class="text-right">
						'.$rel['product_discount'].' ('.$rel['discount_per'].'%)
					</td>
					<td style="vertical-align:top" class="text-right">
						'.($rel['product_amount']).'
					</td>
					<td style="vertical-align:top" class="text-left">
						'.$rel['sel_tax'].'
					</td>
					<td style="vertical-align:top" class="text-right">
						'.$rel['total'].'
					</td>
				<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.$rel['total'].'"/>
											
					<td style="vertical-align:top">
						<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['debitnote_trn_id'].');" ><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['debitnote_trn_id'].');" ><i class="fa fa-times"></i></button>
					</td>	
			</tr>';
			$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}
			echo '</table>			 
						</div>
                           </div>	';
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$q = $dbcon -> query("SELECT mst.* FROM tbl_debitnote_trn as mst WHERE debitnote_trn_id= '$POST[id]'");
			$r = $q->fetch_assoc();
			if($r['grn_id']){
				$r['producthtml'] = get_grn_trn_for_debitnote($dbcon,$r['grn_id'],$r['product_id'],"Edit");
			}
			else{
				$r['producthtml'] = getrequiredproduct($dbcon,'','');
			}
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "getproduct_amount")
		{
			$arr=get_product_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode'])== "delete_data") {
			$row=array();
			$info['debitnote_trn_status']=2;
			$updateid=update_record('tbl_debitnote_trn', $info, "debitnote_trn_id=".$POST['eid'] , $dbcon);
			
			//$sel_trn_po_qry="select * from ".$_POST['table']." where ".$_POST['whereid']."=".$POST['eid'];
			//$sel_trn_po_rel = mysqli_fetch_assoc($dbcon->query($sel_trn_po_qry));	//$change_potrn_use_status=change_potrn_use_status($dbcon,$sel_trn_po_rel['trn_purchaseorder_id'],$sel_trn_po_rel['product_id'],0);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "load_debit_srs_no") {
			$resp['debitnote_no'] = load_debit_srs_no($dbcon);
			
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_ven_grn") {
			$resp['pro_html'] = get_grn_for_debitnote($dbcon,$POST['vender_id'],$POST['id'],"Add");
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_grn_data") {
			$resp['pro_html']	= get_grn_trn_for_debitnote($dbcon,$POST['grn_id'],"","Add");
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "loadpurchase_productdata")
		{
			$q = $dbcon -> query("SELECT trn.*,potrn.product_rate,mrntrn.rejected_qty from tbl_grn_trn as trn
			left join tbl_purchaseordertrn as potrn on potrn.purchaseorder_id=trn.purchaseorder_id and potrn.product_id=trn.product_id
			left join tbl_mrn as mrn on mrn.grn_no=trn.grn_id and mrn.mrn_status=0
			left join tbl_mrn_trn as mrntrn on mrntrn.mrn_no=mrn.mrn_id and mrntrn.mrn_trn_status=0 and mrntrn.product_id=trn.product_id
			where trn.grn_id=".$POST['grn_id']." and trn.grn_trn_status=0 and trn.product_id=".$POST['product_id']."");
			$resp = $q->fetch_assoc();
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_product_tax")
		{
			$cust_arr=get_cust_data_arr($dbcon,$POST['vendor']);
			$cust_state=$cust_arr['stateid'];
			$r=get_product_tax_formula($dbcon,$POST['pid'],$_POST['tran_type'],$cust_state);
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo $r;
			//echo $cust_state;
		}

function get_product_tax($dbcon,$product_amount,$formulaid)
{
	$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;$tax_total_amount=0;
	while($tax=mysqli_fetch_assoc($row))
	{	
		$info['tax_name'.$i]=$tax['tax_name'];
		$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
		$rate_total+=$tax_amount;
		$tax_total_amount+=$info['tax_amount'.$i];
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$info['tax_name'.$i]='';
		$info['tax_amount'.$i]='';
			
	}
	$info['total']=$rate_total;
	$info['tax_total_amount']=$tax_total_amount;
	return $info;
}
	
function load_debit_srs_no($dbcon){
	
	//Load no by Type ID
	$row=array();
	$query1="select * from tbl_invoicetype where status=0 and type_id=13 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
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
?>