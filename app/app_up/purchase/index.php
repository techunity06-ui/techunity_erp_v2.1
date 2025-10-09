<?php

session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

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
		$branch=$_SESSION['branch_id'];
		$where='';
			/*if($POST['report']=='all')
			{
				$where.="  and po_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}
			if($POST['report']=='paid')
			{
				$where.=" and  g_total=paid_amount and po_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}
			if($POST['report']=='due')
			{
				$where.="  and g_total>paid_amount and po_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}*/
			$where.="  and po_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			
			//$where.=" and po.branch_id=$branch";
			$where.=" and po.company_id=$_SESSION[company_id]";
			
			$appData = array();
			$i=1;
			$aColumns = array('po_id','po_no','l.l_name','city.city_name','po_date','order_no','g_total','approve_status','paid_amount','status','po.cdate','po.userid');
			$sIndexColumn = "po_id";
			$isWhere = array("status = 0".$where);
			$sTable = "tbl_pono as po";			
			$isJOIN = array('inner join  tbl_ledger as l on po.vender_id=l.l_id','left join  city_mst city on l.cityid=city.cityid');
			$hOrder = "po.po_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['po_no'];
				$row_data[] = $row['order_no'];
				$row_data[] = date('d M, Y',strtotime($row['po_date']));
				$row_data[] = $row['l_name'];
				$row_data[] = $row['city_name'];
				$row_data[] = $row['g_total'];
				
				if($row['approve_status']=='1'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Authorized</div>';
				}
				else{
					$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div>';
				}
				
				$addpayment='';$delete='';$edit='';
					
					if($delete_btn_per){
						$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['po_id'].')"><i class="fa fa-trash-o"></i></button>';
					}
					
					$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchase_view/'.$row['po_id'].'"><i class="fa fa-eye"></i></a> ';
					
					if($edit_btn_per){
						$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchaseedit/'.$row['po_id'].'"><i class="fa fa-pencil"></i></a>';
					}
					
					//$mrn_btn=' <button class="btn btn-xs btn-primary" data-original-title="View MRN" data-toggle="tooltip" data-placement="top" onClick="get_mrn('.$row['po_id'].')"><i class="fa fa-bars"></i></button>'; 
					
					//$poprint='<a class="btn btn-xs btn-primary" data-original-title="Print Debit Note" data-toggle="tooltip" data-placement="top" href="'.ROOT.'debit_note_print/'.$row['po_id'].'"><i class="fa fa-print"></i></a>';
					
					$po_no=$dbcon->real_escape_string($row['po_no']);
					$apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Purchase" data-toggle="tooltip" data-placement="top" onClick="open_approv_pbill('.$row['po_id'].',\''.$po_no.'\')"><i class="fa fa-exclamation-triangle"></i></button>';
			
					$row_data[] = $edit.' '.$delete.' '.$view.' '.$apprv_btn;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=12 and company_id=".$_SESSION['company_id']);
			
			//$trn_purchaseorder_id_up = $POST['trn_purchaseorder_id_up'];
			
			$info['purchase_type']	= $POST['purchase_type'];
			$info['po_no']		= $POST['po_no'];
			$info['vender_id']	= $POST['vender_id'];
			$info['po_date']	= date('Y-m-d',strtotime($POST['po_date']));
			$info['order_no']	= $POST['order_no'];
			$info['order_date']	= date('Y-m-d',strtotime($POST['order_date']));
			$info['round_off']	= $POST['round_off'];
			$info['packing']	= $POST['paking'];
			$info['remark']		= $POST['remark'];
			$info['g_total']	= $POST['g_total'];
			$info['exp_total']	= $POST['exp_total'];
			/*$info['formulaid']	= $POST['formulaid'];
			$info['discount']	= $POST['discount'];
			$info['tax1_name']	= $POST['taxname0'];
			$info['tax2_name']	= $POST['taxname1'];
			$info['tax3_name']	= $POST['taxname2'];
			$info['taxvalue1']	= $POST['taxvalue0'];
			$info['taxvalue2']	= $POST['taxvalue1'];
			$info['taxvalue3']	= $POST['taxvalue2'];*/
			
			
			if(isset($POST['save_print']))
			{
				$info['print_status']	= $POST['print_status'];
			}
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['mdate']				= date("Y-m-d H:i:s");
			$info['userid']			= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$info['usertype_id']		= $_SESSION['user_type'];
			$info['branch_id']			= $POST['branchid'];
			$inserpoid=add_record('tbl_pono', $info, $dbcon);
		
		//Auto approve if allowed
		$final_btn_per=check_permission("purchase_list",$_SESSION['user_id'],'final_aprv',$dbcon);
		if($final_btn_per){
			$infoaprvqt['approve_status']	= 1;
			$updateperid=update_record('tbl_pono', $infoaprvqt,"po_id=".$inserpoid , $dbcon);
		}
		
			if($inserpoid){
				$inftrn['po_id'] = $inserpoid;
				$inftrn['potrancation_status'] = 0;
				$updatetrnid=update_record('tbl_potrancation', $inftrn,"user_id=".$_SESSION['user_id']." and  potrancation_status=3" , $dbcon);
			}
	/*$qry ='INSERT INTO tbl_potrancation (trn_purchaseorder_id,product_type,product_id, description,product_hsn_code,product_qty,product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,branch_id,company_id,po_id)
	SELECT trn_purchaseorder_id,product_type,product_id,description,product_hsn_code,product_qty, product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,branch_id,company_id,'.$inserpoid.' FROM   tbl_potrntemp where temp_status=0 and user_id='.$_SESSION['user_id'];
		
	$dbcon->query($qry);
	$deleteid=delete_record('tbl_potrntemp',"user_id=".$_SESSION['user_id'], $dbcon);*/		
		
	/** Purchase Order Entry Start ***/
		/*if($POST['purchaseorder_id']){
			$info_purchase_order['purchase_status']  = 1;
			$info_purchase_order['purchaseorder_id'] = $inserpoid;
			$updatepurchaseid=update_record('tbl_pono', $info_purchase_order,"purchaseorder_id=".$POST['purchaseorder_id'], $dbcon);
		}*/
	/** Purchase Order Entry End ***/
		
		
		$total_qty=get_total_qty_by_po($dbcon,$inserpoid);
		
		$total_lending_cost=$POST['exp_total']/$total_qty;
		
		$q_pt=$dbcon->query("select * from tbl_potrancation where po_id='$inserpoid'");
		while($r_pt=mysqli_fetch_assoc($q_pt))
		{
			$pr_pt_qty=$r_pt['product_qty'];
			
			$per_landing=$pr_pt_qty*$total_lending_cost;
			
			$total_after_landing=$per_landing+$r_pt['product_rate'];
			
			$dbcon->query("update tbl_potrancation set po_landing_cost='$total_after_landing' where potrancation_id='$r_pt[potrancation_id]'");
		}
		
		
		$dbcon->query("update tbl_grn set purchase_id='$inserpoid',purchase_status='1' where purchaseorder_id='$trn_purchaseorder_id_up' and purchase_status='0'");
		
		$dbcon->query("update tbl_mrn set purchase_id='$inserpoid',purchase_status='1' where purchaseorder_id='$trn_purchaseorder_id_up' and purchase_status='0'");
		
		
		foreach ($POST['ename_a'] as $i => $name) 
		{
			$info_e['exp_e_name']=$POST['ename_a'][$i];
			$info_e['exp_e_amount']=$POST['eamount_a'][$i];
			$info_e['exp_in_id']=$inserpoid;
			$info_e['cdate']=date("Y-m-d");
			$info_e['company_id']=$_SESSION['company_id'];
			$info_e['branch_id']=$_SESSION['branch_id'];
			$info_e['user_id']=$_SESSION['user_id'];
			$inserinvoiceidexp=add_record('tbl_purchase_exp', $info_e, $dbcon);
			
			add_general_book_entry($dbcon,"tbl_purchase_exp",$inserinvoiceidexp,2,$info_e['exp_e_name'],$info_e['exp_e_amount'],$general_book_id,$POST['po_date']);
		}
			// add pathik start
				add_general_book_entry($dbcon,"tbl_purchase",$inserpoid,1,$POST['vender_id'],$POST['g_total'],$general_book_id,$POST['po_date']);
				
				general_book_tax_entry($dbcon,$inserpoid);
			// pathik end
		$check_purchase_rate_status=check_purchase_rates_status($dbcon, $inserpoid);

					if(isset($POST['save_print']))
					{
						$arr['printstatus']=$POST['print_status'];
						$arr['msg']="1";
						$arr['eid']=$inserpoeid;
						//Insert LOG
						$log_entry=common_log_entry($dbcon,"po_add",1,"tbl_pono",$inserpoid);
					}
					else
					{
						if($inserpoid)
						{	
							$arr['msg']="1";							
						}
						else
							$arr['msg']="0";
					}
			echo json_encode($arr);					
		 
		}		
		else if(strtolower($POST['mode']) == "edit") {
			 
					$info['po_no']		= $POST['po_no'];
					$info['vender_id']	= $POST['vender_id'];
					$info['po_date']	= date('Y-m-d',strtotime($POST['po_date']));
					$info['order_no']	= $POST['order_no'];
					$info['order_date']	= date('Y-m-d',strtotime($POST['order_date']));
					$info['round_off']	= $POST['round_off'];
					$info['packing']	= $POST['paking'];
					$info['remark']		= $POST['remark'];
					$info['exp_total']	= $POST['exp_total'];
					$info['g_total']	= $POST['g_total'];
					/*$info['formulaid']	= $POST['formulaid'];
					$info['discount']	= $POST['discount'];
					$info['tax1_name']	= $POST['taxname0'];
					$info['tax2_name']	= $POST['taxname1'];
					$info['tax3_name']	= $POST['taxname2'];
					$info['taxvalue1']	= $POST['taxvalue0'];
					$info['taxvalue2']	= $POST['taxvalue1'];
					$info['taxvalue3']	= $POST['taxvalue2'];*/
					$info['mdate']		= date("Y-m-d H:i:s");
					$info['userid']		= $_SESSION['user_id'];
					$info['company_id']		= $_SESSION['company_id'];
					$info['approve_status']	= 0;
					if(isset($POST['save_print']))
					{
						$info['print_status']	= $POST['print_status'];
					}
					$info['cdate']				= 	date("Y-m-d H:i:s");
					$info['userid']			= $_SESSION['user_id'];
					$updateid=update_record('tbl_pono', $info,"po_id=".$POST['eid'] , $dbcon);
		
		//Auto approve if allowed
		$final_btn_per=check_permission("purchase_list",$_SESSION['user_id'],'final_aprv',$dbcon);
		if($final_btn_per){
			$infoaprvqt['approve_status']	= 1;
			$updateperid=update_record('tbl_pono', $infoaprvqt,"po_id=".$POST['eid'] , $dbcon);
		}
		
		//Update Charges
		$deleteid=delete_record('tbl_purchase_exp',"exp_in_id=".$POST['eid'], $dbcon);
		
		$qry_d="select * from tbl_purchase_exp as cert where exp_in_id=".$POST['eid'] ;
			$ro_d=$dbcon->query($qry_d);
			while($re_d=mysqli_fetch_assoc($ro_d)){
				$info_gen['genral_book_status']	= 2;
				$updateperid=update_record('tbl_general_book', $info_gen,"table_name='tbl_purchase_exp' and table_id=".$ro_d['exp_id'] , $dbcon);
			}
		//for($i=0;$i<$row_cnt;$i++)
		foreach ($POST['ename_a'] as $i => $name) 
		{
			$info_e['exp_e_name']=$POST['ename_a'][$i];
			$info_e['exp_e_amount']=$POST['eamount_a'][$i];
			$info_e['exp_in_id']=$POST['eid'];
			$info_e['cdate']=date("Y-m-d");
			$info_e['company_id']=$_SESSION['company_id'];
			$info_e['branch_id']=$_SESSION['branch_id'];
			$info_e['user_id']=$_SESSION['user_id'];
			$inserinvoiceidexp=add_record('tbl_purchase_exp', $info_e, $dbcon);
			
			add_general_book_entry($dbcon,"tbl_purchase_exp",$inserinvoiceidexp,2,$info_e['exp_e_name'],$info_e['exp_e_amount'],$general_book_id,$POST['po_date']);
		}
		//pathik start
		$general_book_id=get_general_book_id($dbcon,'tbl_purchase',$POST['eid'],$POST['vender_id']);	
		add_general_book_entry($dbcon,"tbl_purchase",$POST['eid'],1,$POST['vender_id'],$POST['g_total'],$general_book_id,$POST['po_date']);
	
		general_book_tax_entry($dbcon,$POST['eid']);
		
		//pathik end
			$check_purchase_rate_status=check_purchase_rates_status($dbcon, $POST['eid']);
			
				if(isset($POST['save_print']))
				{
					$arr['printstatus']=$POST['print_status'];
					$arr['msg']="update";
					$arr['eid']=$POST['eid'];
					//Insert LOG
					$log_entry=common_log_entry($dbcon,"po_add",2,"tbl_pono",$POST['eid']);
				}
				else
				{
					if($updateid)
					{	
						$arr['msg']="update";
						
					}
					else
						$arr['msg']=0;
				}
			echo json_encode($arr);	
			 
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['status']		= 2;
			$info1['potrancation_status']		= 2;
			$q="select * from tbl_pono where po_id=".$POST['eid'];
			$row=mysqli_fetch_assoc($dbcon->query($q));
			$file=$row['po_pdf'];
			unlink(POPDF_A.$file);
			$updateinvoiceid=update_record(' tbl_pono', $info,"po_id=".$POST['eid'] , $dbcon);	
			$updatetrancationid=update_record('tbl_potrancation', $info1,"po_id=".$POST['eid'] , $dbcon);

			$info_gen['genral_book_status']		= 2;
			$updateinvoiceid=update_record('tbl_general_book', $info_gen,"table_name='tbl_purchase' and table_id=".$POST['eid'] , $dbcon);	
			
			$qry="select * from `tbl_potrancation` as popro where po_id=".$POST['eid'];
			$result=$dbcon->query($qry);
			$info_ta['tax_used_status']		= 2;
			while($row=mysqli_fetch_assoc($result)){
				
				$updateinvoiceid=update_record('tbl_used_tax', $info_ta,"table_name='tbl_potrancation' and used_transaction_id=".$row['potrancation_id'] , $dbcon);
			}
			
			 $qry_d="select * from tbl_purchase_exp as cert where exp_in_id=".$POST['eid'] ;
			$ro_d=$dbcon->query($qry_d);
			$info_gen1['genral_book_status']	= 2;
			while($re_d=mysqli_fetch_assoc($ro_d)){
				
				$updateperid=update_record('tbl_general_book', $info_gen1,"table_name='tbl_purchase_exp' and table_id=".$re_d['exp_id'] , $dbcon);
			} 
			
			// Update Purchase Order Status
			/*$info_purchase_order['purchase_status']  = 0;
			$updatepurchaseid=update_record('tbl_purchaseorder', $info_purchase_order,"used_purchase_id=".$POST['eid'], $dbcon);*/	
						
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"po_add",3,"tbl_pono",$POST['eid']);
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
				$info1['company_id']		= $_SESSION['company_id'];
				$info1['user_id']			= $_SESSION['user_id'];
				//$info=get_product_tax($dbcon,$total,$POST['formulaid']);
				//$info1=array_merge($info1,$info);
				//$info1['total']=$total;
				
			$table='tbl_potrancation';$tableid='potrancation_id';	
			if(!empty($POST['po_id'])) {
				$info1['po_id'] = $POST['po_id'];
			}
			else {
				$info1['potrancation_status'] = 3;
			}
			
			if(empty($POST['edit_id'])) {
				$inserid=add_record($table, $info1, $dbcon);
				$insert_tax=add_tax_record($dbcon,$inserid,"tbl_potrancation","po_id",$POST['formulaid'],$POST['taxable_value']);
			}
			else {
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				$insert_tax=add_tax_record($dbcon,$POST['edit_id'],"tbl_potrancation","po_id",$POST['formulaid'],$POST['taxable_value']);
			}
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			if($POST['po_id']){
				$query="select trn.*,product.product_name,cat.unit_name,grn.grn_no from tbl_potrancation as trn
				   left join unit_mst as cat on cat.unitid=trn.unit_id 
				   left join product_mst as product on product.product_id=trn.product_id  
				   left join tbl_grn as grn on grn.grn_id=trn.grn_id 
				   where trn.potrancation_status=0 and trn.po_id=".$POST['po_id'];
			}
			else{
				$query="select trn.*,product.product_name,cat.unit_name,grn.grn_no from tbl_potrancation as trn
				   left join unit_mst as cat on cat.unitid=trn.unit_id 
				   left join product_mst as product on product.product_id=trn.product_id  
				   left join tbl_grn as grn on grn.grn_id=trn.grn_id 
				   where trn.potrancation_status=3 and trn.user_id=".$_SESSION['user_id'];
			}
		
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
					  <div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center" width="10%">GRN</th>
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
				
			 echo '<tr id="'.$id.'" >
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
						<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['potrancation_id'].');" ><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['potrancation_id'].');" ><i class="fa fa-times"></i></button>
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
			$q = $dbcon -> query("SELECT mst.* FROM tbl_potrancation as mst WHERE potrancation_id= '$POST[id]'");
			$r = $q->fetch_assoc();
			if($r['grn_id']){
				$r['producthtml'] = get_grn_trn_for_purchase($dbcon,$r['grn_id'],$r['product_id'],"Edit");
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
			$info['potrancation_status']=2;
			$updateid=update_record('tbl_potrancation', $info, "potrancation_id=".$POST['eid'] , $dbcon);
			
			
			
			//$sel_trn_po_qry="select * from ".$_POST['table']." where ".$_POST['whereid']."=".$POST['eid'];
			//$sel_trn_po_rel = mysqli_fetch_assoc($dbcon->query($sel_trn_po_qry));	//$change_potrn_use_status=change_potrn_use_status($dbcon,$sel_trn_po_rel['trn_purchaseorder_id'],$sel_trn_po_rel['product_id'],0);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "load_purchase_srs_no") {
			$resp['po_no'] = load_purchase_srs_no($dbcon);
			
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_ven_grn") {
			$resp['pro_html'] = get_grn_for_purchase($dbcon,$POST['vender_id'],"","Add");
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_grn_data") {
			$resp['pro_html']	= get_grn_trn_for_purchase($dbcon,$POST['grn_id'],"","Add");
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_purchase_order") {
			echo get_po_for_purchase($dbcon,$POST['vender_id']);
		}
		else if(strtolower($POST['mode'])== "load_purhcase_order_data")
		{
			$q = $dbcon -> query("SELECT * from tbl_purchaseorder where purchaseorder_id=".$POST['purchaseorder_id']);
			$rel = $q->fetch_assoc();
			
			$resp['purchaseorder_no']	= $rel['purchaseorder_no'];
			$resp['purchaseorder_date'] = date("d-m-Y",strtotime($rel['purchaseorder_date']));
			//$resp['pro_html'] 		= get_purchase_order_data($dbcon,$POST['purchaseorder_id']);
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "loadpurchase_producttypedata")
		{
			$resp['pro_html'] 			= get_purchase_order_typewise_data($dbcon,$POST['type_id'],$POST['purchaseorder_id']);
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_purhcase_pro")
		{
			$resp['pro_html'] 			= getproduct($dbcon,0,'0,1,3');
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "loadpurchase_productdata")
		{
			$q = $dbcon -> query("SELECT trn.*,potrn.product_rate,potrn.unit_id from tbl_grn_trn as trn
			left join tbl_purchaseordertrn as potrn on potrn.purchaseorder_id=trn.purchaseorder_id and potrn.product_id=trn.product_id
			where trn.grn_id=".$POST['grn_id']." and trn.grn_trn_status=0 and trn.product_id=".$POST['product_id']."");
			$resp = $q->fetch_assoc();
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "last_rate")
		{
			 $query="select product_rate,potrancation_id,potrancation_status,product_id from tbl_potrancation as trn left join tbl_pono as mst on mst.po_id=trn.po_id where product_id=".$POST["product_id"]." and potrancation_status=0 order by potrancation_id DESC";
			$prel=mysqli_fetch_assoc($dbcon->query($query));
			echo $prel['product_rate'];
		}
		else if(strtolower($POST['mode'])== "load_rate_hist")
		{
			$resp='';
			$query="select inv.*,ven.vender_name,pro.product_name,trn.product_rate from tbl_pono as inv
					inner join tbl_potrancation as trn on inv.po_id=trn.po_id 
					inner join tbl_vender as ven on ven.vender_id=inv.vender_id
					inner join product_mst as pro on pro.product_id=trn.product_id
					where inv.status=0 and trn.potrancation_status=0 and inv.vender_id=".$POST["vender_id"]." and trn.product_id=".$POST["product_id"]." order by trn.potrancation_id DESC LIMIT 10";
			$rs_prel=$dbcon->query($query);
			$rs_prel_num_rows=mysqli_num_rows($rs_prel);
			if($rs_prel_num_rows>0){
				while($prel=mysqli_fetch_assoc($rs_prel)){
					$resp.='<tr>
								<td class="text-center">'.$prel['po_no'].'</td>
								<td class="text-center">'.date('d-m-y',strtotime($prel['po_date'])).'</td>
								<td class="text-center">'.$prel['product_rate'].'</td>
							</tr>';
					$row['cust_name']=$prel['vender_name'];
					$row['product_name']=$prel['product_name'];		
				}
			}
			else{
				$resp.='<tr>
							<td colspan="3" class="text-center">NO DATA FOUND !!</td>
						</tr>';
				$row['cust_name']="";
				$row['product_name']="";
			}
			
			
			$row['resp']=$resp;
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];
			echo getrequiredproduct($dbcon,'',' and product_type='.$type_id.'');
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
		else if(strtolower($POST['mode'])=="expense_by_id")
		{
			$eid=$POST['eid'];
			echo get_ledger_expense_by_id($dbcon,$eid);
		}
		else if(strtolower($POST['mode']) == "add_apprv_hist") {
			
			$info1['assign_user_ids']	= $POST['assign_user_ids'];
			$info1['approve_remark']	= $_POST['approve_remark'];
			$info1['approve_status']	= $POST['approve_status'];
			$info1['po_id']				= $POST['po_id'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$inserid=add_record("tbl_purchasebill_aprv_log", $info1, $dbcon);
			
			//Hide approve btn if not allowed
			$final_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'final_aprv',$dbcon);

			if($final_btn_per){
				$infoso['approve_status']	= $POST['approve_status'];
				$updateid=update_record('tbl_pono', $infoso,"po_id=".$POST['po_id'] , $dbcon);
			}
			
		}
	else if(strtolower($POST['mode']) == "load_purchase_hist_datatable") {
		
		$where='';
		$where.=" and log.po_id=".$POST['po_id'];
		
		$appData = array();
		$i=1;
		$aColumns = array('log.p_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
		$sIndexColumn = "log.p_aprv_log_id";
		$isWhere = array("log.p_aprv_log_status=0 ".$where." ");
		$sTable = "tbl_purchasebill_aprv_log as log";			
		$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
		$hOrder = "log.p_aprv_log_id desc";
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

function check_purchase_rates_status($dbcon,$po_id){
	$sel_pro_rate = "select * from tbl_potrancation where potrancation_status=0 and po_id=".$po_id;
	$rate_flag=false;
	$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);
	while($sel_pro_rate_rel=mysqli_fetch_assoc($sel_pro_rate_rs)){
		if($sel_pro_rate_rel['trn_purchaseorder_id']){
			$get_protrn_rate_qry = "select product_rate from tbl_purchaseordertrn where purchaseordertrn_id=".$sel_pro_rate_rel['trn_purchaseorder_id'];
			$pro_rt_rel = mysqli_fetch_assoc($dbcon->query($get_protrn_rate_qry));
			$pro_mst_rate = $pro_rt_rel['product_rate'];
		}
		else{
			$pro_mst_rate = get_pro_field($dbcon, $sel_pro_rate_rel['product_id'], 'product_purchase_mst_rate');
			
		}
		
		if($pro_mst_rate && $sel_pro_rate_rel['product_rate']> $pro_mst_rate){
			$rate_flag=true;
			
			break;
		}
	}
	
	if($rate_flag){
		$upd_stst=$dbcon->query("update tbl_pono set mismatch_rate_status=1 where po_id=".$po_id);
	}
	else{
		$upd_stst=$dbcon->query("update tbl_pono set mismatch_rate_status=0 where po_id=".$po_id);
	}
}
function change_potrn_use_status($dbcon,$trn_purchaseorder_id,$product_id,$use_purchase_status){
	$upd_sts = $dbcon->query("update tbl_purchaseordertrn set use_purchase_status=".$use_purchase_status." where purchaseorder_id=".$trn_purchaseorder_id." and purchaseordertrn_status=0 and product_id in(".$product_id.")");
	
	$upd_qry = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and use_purchase_status=0 and purchaseorder_id=".$trn_purchaseorder_id." ";
	$upd_qry_rs = $dbcon->query($upd_qry);
	$upd_qry_nums = mysqli_num_rows($upd_qry_rs);
	if($upd_qry_nums){
		$updmain_sts = $dbcon->query("update tbl_purchaseorder set purchase_status=0 where purchaseorder_id=".$trn_purchaseorder_id." ");
	}
	else{
		$updmain_sts = $dbcon->query("update tbl_purchaseorder set purchase_status=1 where purchaseorder_id=".$trn_purchaseorder_id." ");
	}
}
	
function load_purchase_srs_no($dbcon){
	
	//Load no by Type ID
	$row=array();
	$query1="select * from tbl_invoicetype where status=0 and type_id=12 and company_id=".$_SESSION['company_id'];
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
function general_book_tax_entry($dbcon,$invoice_id){
	$qry1="select group_concat(potrancation_id) as tid from tbl_potrancation as cert where potrancation_status=0 and po_id=".$invoice_id;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	$qry122="select * from tbl_pono as cert where status=0 and po_id=".$invoice_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	
	$qry="SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax WHERE tax_used_status=0 and used_transaction_id in (".$re["tid"].") and table_name='tbl_potrancation' group by ledger_id order by tax_used_id desc";
	$row=$dbcon->query($qry);
	while($tax=mysqli_fetch_assoc($row))
	{
		$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$invoice_id." and table_name='tbl_purchase'";
			$ros=$dbcon->query($qry12);
			$re2=mysqli_fetch_assoc($ros);
		
	
		$info1['table_name']	= "tbl_purchase";
		$info1['table_id']		= $invoice_id;
		$info1['ref_date']		= date("Y-m-d",strtotime($rea['po_date']));
		$info1['entry_type']	= 2;
		$info1['ledger_id']		= $tax['ledger_id'];
		$info1['amount']		= $tax['tamount'];
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['cdate']			= date("Y-m-d H:i:s");
		$info1['company_id']	= $_SESSION['company_id'];
		
		if(!empty($re2['general_book_id'])){
			$updateid=update_record("tbl_general_book", $info1,"general_book_id=".$re2['general_book_id'] , $dbcon);
		}else{
			$inserid=add_record("tbl_general_book", $info1, $dbcon);
		}
		//var_dump($re2['general_book_id']);
	}
	
}
?>