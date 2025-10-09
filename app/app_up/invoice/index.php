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
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where='';
			/*if($POST['report']=='all')
			{
				
			}
			if($POST['report']=='paid')
			{
				$where.=" and  g_total=paid_amount and invoice_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}
			if($POST['report']=='due')
			{
				$where.="  and g_total>paid_amount and invoice_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}*/
			
		
		
			if(!empty($POST['type_id']))
			{
				$where .=" and invoice.invoicetype_id=".$POST['type_id'];
			}
			$where.="  and invoice_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('invoice_id','invoice_no','cust.l_name','invoice_date','invoicetype.invoice_type','g_total','paid_amount','invoice_status','invoice.cdate','invoice.user_id','invoice.usertype_id','invoice.invoicetype_id','invoice.gst_flag');
			$sIndexColumn = "invoice_id";
			$isWhere = array("invoice_status = 0".$where.check_user('invoice'));
			$sTable = "tbl_invoice as invoice";			
			$isJOIN = array('inner join tbl_ledger cust on invoice.cust_id=cust.l_id','left join tbl_invoicetype invoicetype on invoice.invoicetype_id=invoicetype.invoicetype_id');
			$hOrder = "invoice.invoice_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['invoice_type'];
				$row_data[] = $row['invoice_no'];
				$row_data[] = date('d M, Y',strtotime($row['invoice_date']));
				$row_data[] = $row['l_name'];
				$row_data[] = $row['g_total'];
				/*if($row['g_total']>$row['paid_amount'])
				{
				$row_data[] = "<div class='external-event label label-warning ui-draggable' style='position: relative;'>DUE (RS. ".($row['g_total']-$row['paid_amount']).")</div>";
				}
				else
				{
					$row_data[]="<div class='external-event label label-success ui-draggable' style='position: relative;'>Paid</div>";;
				}*/
			
				 
					$addpayment='';$delete='';$edit='';
					if($row["g_total"]>$row["paid_amount"]){
						//$addpayment='<a class="btn btn-xs btn-primary" data-original-title="Payable '.($row['g_total']-$row['paid_amount']).' Rs." data-toggle="tooltip" data-placement="top" href="invoicepaymentmode/'.$row['invoice_id'].'"><i class="fa fa-plus"></i></a>';
						
					}
					$print='<a class="btn btn-xs btn-info" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'invoicereceipt/'.$row['invoice_id'].'"><i class="fa fa-print"></i></a> ';
					
					
					$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['invoice_id'].')"><i class="fa fa-trash-o"></i></button>';
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'invoiceedit/'.$row['invoice_id'].'"><i class="fa fa-pencil"></i></a>';
					$row_data[] = $print.'<a class="btn btn-xs btn-success" data-original-title="Print Chalan" data-toggle="tooltip" data-placement="top" href="'.ROOT.'invoicechalan/'.$row['invoice_id'].'"><i class="fa fa-print"></i></a>
					  '.$edit.' '.$delete.' '.$addpayment.' ';
				 
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$com="select * from tbl_finance_setting where company_id=".$_SESSION['company_id'];
			$comty=mysqli_fetch_assoc($dbcon->query($com));	
		
		if($comty['series_same']=="1"){
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=1 and company_id=".$_SESSION['company_id']);
		}else{
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
		}
			
							$info['complaint_id']	= $POST['complaint_id'];
							$info['quotation_id']	= $POST['quotation_id'];
							$info['invoicetype_id']	= $POST['invoicetype_id'];
							$info['invoice_no']		= $POST['invoice_no'];
							$info['invoice_date']	= date('Y-m-d',strtotime($POST['invoice_date']));
							$info['challan_no']		= $POST['challan_no'];
							$info['challan_date']	= date('Y-m-d',strtotime($POST['challan_date']));
							$info['num_of_parcel']	= $POST['num_of_parcel'];
							$info['dispatch_doc_no']= text_rnremove($POST['dispatch_doc_no']);
							$info['dispatch_date']  = date('Y-m-d H:i:s',strtotime($POST['dispatch_date']));
							$info['vehicle_no']		= $POST['vehicle_no'];
							$info['order_no']		= $POST['order_no'];
							$info['order_date']	= date('Y-m-d',strtotime($POST['order_date']));
							$info['dispatch_by']	= $POST['dispatch_by'];
							$info['destination']	= $POST['destination'];
							$info['payment_terms']	= $POST['payment_terms'];
							
							$info['docket_no']		= $POST['docket_no'];
							$info['packing_boxes']	= $POST['packing_boxes'];
							$info['total_weight']	= $POST['total_weight'];
							
							$info['cust_id']		= $POST['cust_id'];
							//$info['machine_name']	= $POST['machine_name'];
							$info['consignee_id']	= $POST['consignee_id'];
							$info['packing']		= $POST['packing'];
							$info['cutting']		= $POST['cutting'];
							$info['freight']		= $POST['freight'];
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
							$info['remark']			= text_rnremove($POST['remark']);
							$info['reverse_charge']	= $POST['reverse_charge_check'];
							$info['install_type']	= $POST['install_type'];
							$info['gst_flag']		= '2';
							$info['cdate']			= date("Y-m-d H:i:s");
							$info['user_id']		= $_SESSION['user_id'];
							$info['company_id']		= $_SESSION['company_id'];
							if(isset($POST['save_print']))
							{
								$info['print_status']	= $POST['print_status'];
							}
							$inserinvoiceid=add_record('tbl_invoice', $info, $dbcon);
		
		/*Update Trn Table Start*/
		if($inserinvoiceid){
			$infotrn['invoice_id']			= $inserinvoiceid;
			$infotrn['trancation_status']	= 0;
			$updatetrnid=update_record('tbl_invoicetrn', $infotrn,"trancation_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
		}
		add_general_book_entry($dbcon,"tbl_invoice",$inserinvoiceid,2,$POST['cust_id'],$POST['g_total'],$general_book_id,$POST['invoice_date']);
		
		general_book_tax_entry($dbcon,$inserinvoiceid);
		general_book_sercices_entry($dbcon,$inserinvoiceid);
		
		/*Update Trn Table End*/					
		/*$qry ='INSERT INTO tbl_invoicetrn (ref_s_id,ref_quot_trn_id,product_id,model_id, ser_status,description,product_hsn_code,product_qty,product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,start_serial1,end_serial1,start_serial2,end_serial2,start_serial3,end_serial3,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,company_id,bill_value,bill_black_value,taxable_value,invoice_id)
		SELECT ref_s_id,ref_quot_trn_id,product_id,model_id,ser_status,description,product_hsn_code,product_qty, product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,start_serial1,end_serial1,start_serial2,end_serial2,start_serial3,end_serial3,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,company_id,bill_value,bill_black_value,taxable_value,'.$inserinvoiceid.' FROM  tbl_invoicetrntemp where temp_status=0 and user_id='.$_SESSION['user_id'];	
		$dbcon->query($qry);*/
		
		
		if($POST['install_type']=='yes')
		{
			$qry1="select * from tbl_invoicetrn where trancation_status=0 and invoice_id='$inserinvoiceid'";
			$row1=$dbcon->query($qry1);
			while($rel1=mysqli_fetch_assoc($row1))
			{
				$infoc['complaint_no']=load_complaint_no($dbcon);
				$infoc['complaint_date']=date('Y-m-d',strtotime($POST['invoice_date']));
				$infoc['cust_id']=$POST['cust_id'];
				$infoc['complaint_type_id']='1';
				$infoc['cdate']=date("Y-m-d H:i:s");
				$infoc['followup_status']='1';
				$infoc['sp_part_status']='4';
				$infoc['old_sp_part_status']='no';
				$infoc['user_id']=$_SESSION['user_id'];
				$infoc['company_id']=$_SESSION['company_id'];
				$infoc['invoice_id']=$inserinvoiceid;
				$insercomplainid=add_record('tbl_complaint', $infoc, $dbcon);
				
				/*$qry ='INSERT INTO tbl_complaint_trn (complaint_id,product_id,comp_pro_sts,user_id)
				SELECT '.$insercomplainid.',product_id,ser_status,user_id FROM  tbl_invoicetrn where invoice_id='.$inserinvoiceid; */
				
				$qryx="INSERT INTO tbl_complaint_trn (complaint_id,product_id,comp_pro_sts,comp_amount,user_id) values ('$insercomplainid','$rel1[product_id]','$rel1[ser_status]','$rel1[total]','$_SESSION[user_id]')";
							
				$dbcon->query($qryx);
				
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=1 and company_id='$_SESSION[company_id]'");
			}
		}
		
		if($POST['quotation_id']){
			$upd_qt_sts=upd_qt_done_sts($dbcon,$POST['quotation_id'],$inserinvoiceid);
		}
		if($POST['complaint_id']){
			$upd_spare_inv_sts=upd_spare_inv_sts($dbcon,$POST['complaint_id'],$inserinvoiceid);
		}
		
		//Update Serial No.
		$upd_inv_srl_no=upd_inv_srl_no($dbcon,$inserinvoiceid);
		
		//Copy Serial No.
		$cpy_srl_no=copy_srl_no($dbcon,$inserinvoiceid);
		//$deleteid=delete_record('tbl_invoicetrntemp',"user_id=".$_SESSION['user_id'], $dbcon);	
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"invoice_add",1,"tbl_invoice",$inserinvoiceid);	
		
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
			
				$info['invoicetype_id']	= $POST['invoicetype_id'];
				$info['invoice_no']		= $POST['invoice_no'];
				$info['invoice_date']	= date('Y-m-d',strtotime($POST['invoice_date']));
				$info['challan_no']		= $POST['challan_no'];
				$info['challan_date']	= date('Y-m-d',strtotime($POST['challan_date']));
				$info['vehicle_no']		= $POST['vehicle_no'];
				$info['order_no']		= $POST['order_no'];
				$info['order_date']		= date('Y-m-d',strtotime($POST['order_date']));
				$info['num_of_parcel']	= $POST['num_of_parcel'];
				$info['dispatch_doc_no']= $POST['dispatch_doc_no'];
				$info['dispatch_date']  = date('Y-m-d H:i:s',strtotime($POST['dispatch_date']));
				$info['dispatch_by']	= $POST['dispatch_by'];
				$info['destination']	= $POST['destination'];
				$info['payment_terms']	= $POST['payment_terms'];
				
				$info['docket_no']		= $POST['docket_no'];
				$info['packing_boxes']	= $POST['packing_boxes'];
				$info['total_weight']	= $POST['total_weight'];
				//$info['machine_name']		= $POST['machine_name'];
				$info['cust_id']		= $POST['cust_id'];
				$info['consignee_id']	= $POST['consignee_id'];
				$info['packing']		= $POST['packing'];
				$info['cutting']		= $POST['cutting'];
				$info['freight']		= $POST['freight'];
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
				$info['remark']			= text_rnremove($POST['remark']);
				$info['reverse_charge']			= $POST['reverse_charge_check'];
				$info['cdate']			= date("Y-m-d H:i:s");
				$info['user_id']		= $_SESSION['user_id'];
				$info['company_id']		= $_SESSION['company_id'];
				if(isset($POST['save_print'])){
					$info['print_status']	= $POST['print_status'];
				}
				$updateid=update_record('tbl_invoice', $info,"invoice_id=".$POST['eid'] , $dbcon);
				
				$general_book_id=get_general_book_id($dbcon,'tbl_invoice',$POST['eid'],$POST['cust_id']);
						
				add_general_book_entry($dbcon,"tbl_invoice",$POST['eid'],2,$POST['cust_id'],$POST['g_total'],$general_book_id,$POST['invoice_date']);
				
				general_book_tax_entry($dbcon,$POST['eid']);
				general_book_sercices_entry($dbcon,$inserinvoiceid);
						
			
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"invoice_add",2,"tbl_invoice",$POST['eid']);
		
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
					 
			$info['invoice_status']	= 2;
			$info1['trancation_status']	= 2;
			$informdr['status'] = 2;
			$info_sales_order['invoice_status']  = 0;
			$info_srl['inv_srl_trn_status']  = 0;
			$updatesalesid=update_record('tbl_sales_order', $info_sales_order,"used_invoice_id=".$POST['eid'], $dbcon);
			$updateinvoiceid=update_record('tbl_invoice', $info,"invoice_id=".$POST['eid'] , $dbcon);	
			$updatetrancationid=update_record('tbl_invoicetrn', $info1,"invoice_id=".$POST['eid'] , $dbcon);	
			$updatesrlid=update_record('tbl_inv_srl_trn', $info_srl,"invoice_id=".$POST['eid'] , $dbcon);	
			//Update Payment Reminder
			$updatermdrid=update_record('todo_mst', $informdr,"ref_id=".$POST['eid']." and ref_table='tbl_invoice'" , $dbcon);
			//Update Serial Number
			//$deleteid=delete_record('tbl_serialtrn',"invoice_id=".$POST['eid'], $dbcon);
			
			$info_gen['genral_book_status']		= 2;
			$updateinvoiceid=update_record('tbl_general_book', $info_gen,"table_name='tbl_invoice' and table_id=".$POST['eid'] , $dbcon);	
			
			
			$qry="select * from `tbl_invoicetrn` as popro where invoice_id=".$POST['eid'];
			$result=$dbcon->query($qry);
			$info_ta['tax_used_status']		= 2;
			while($row=mysqli_fetch_assoc($result)){
				
				$updateinvoiceid=update_record('tbl_used_tax', $info_ta,"table_name='tbl_invoicetrn' and used_transaction_id=".$row['trancation_id'] , $dbcon);
				
				$info_ser['genral_book_status']=2;	
				$updateid1=update_record("tbl_general_book", $info_ser, "table_name='tbl_invoicetrn' and table_id=".$row['trancation_id'] , $dbcon);
			}
			
			
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"invoice_add",3,"tbl_invoice",$POST['eid']);
		
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
			
				$info1['product_id']		= $POST['product_id'];
				$info1['model_id']			= $POST['model_id'];
				$info1['ser_status']			= $POST['ser_status'];
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
				$info1['bill_value']		= $POST['bill_value'];
				$info1['bill_black_value']	= $POST['bill_black_value'];
				$info1['taxable_value']		= $POST['taxable_value'];
				$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
				$info1=array_merge($info1,$info);
				$info1['user_id']	= $_SESSION['user_id'];
			$table='tbl_invoicetrn';$tableid='trancation_id';
			if(!empty($POST['invoice_id'])){
				$info1['invoice_id']= $POST['invoice_id'];
			}
			else{
				$info1['trancation_status']	= 3;
			}
			if(empty($POST['edit_id'])){
				$inserid=add_record($table, $info1, $dbcon);
			}
			else{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				$inserid=$POST['edit_id'];
			}
			$insert_tax=add_tax_record($dbcon,$inserid,"tbl_invoicetrn","trancation_id",$POST['formulaid'],$POST['taxable_value']);
			
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
		else if(strtolower($POST['mode'])== "load_podata")
		{
				getpono($dbcon,$POST['cust_id']);
		}
		else if(strtolower($POST['mode'])== "load_podate")
		{
			$qry2="select * from tbl_pono where po_id=".$POST['po_id'];
			$result2=mysqli_fetch_assoc($dbcon->query($qry2));
			echo json_encode($result2);	
		}
		else if(strtolower($POST['mode'])== "reminder")
		{
			$qry2="select * from pay_terms where terms_id=".$POST['paymentterms'];
			$result2=mysqli_fetch_assoc($dbcon->query($qry2));
			echo json_encode($result2);	
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
				$query="select mst.*,product.product_name,product.product_type,cat.unit_name from  tbl_invoicetrn as mst
					left join unit_mst as cat on cat.unitid=mst.unit_id 
					left join product_mst as product on product.product_id=mst.product_id  
					where trancation_status=0 and invoice_id=".$POST['eid'];
			}
			else{
				$query="select mst.*,product.product_name,product.product_type,cat.unit_name from  tbl_invoicetrn as mst
					left join unit_mst as cat on cat.unitid=mst.unit_id 
					left join product_mst as product on product.product_id=mst.product_id  
					where trancation_status=3 and mst.user_id=".$_SESSION['user_id'];
			}
			/*$query="select mst.*,product.product_name,cat.unit_name,m.model_name from  tbl_invoicetrntemp as mst 
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
				$product_type_arr = array("0", "1", "2", "3", "4", "5");
				if (in_array($rel['product_type'], $product_type_arr)){
					$cnt_pro_stk=get_current_stock($dbcon,$rel['product_id']);
				}
				else{
					$cnt_pro_stk=9999;
				}
				$product_name=$dbcon->real_escape_string($rel['product_name']);
			 echo '<tr id="fieldtr'.$id.'" >
					
					<td style="vertical-align:top;">
						<b>'.$rel['product_name'].'</b>
					</td>
					
					<td style="vertical-align:top;" class="text-center">
						'.$rel['product_hsn_code'].'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['product_qty'].'
						<input type="hidden" id="trn_pro_stk'.$i.'" name="trn_pro_stk[]" value="'.$rel['product_qty'].'">
						<input type="hidden" id="cnt_pro_stk'.$i.'" name="cnt_pro_stk[]" value="'.$cnt_pro_stk.'">
						<br/><strong>Current Stock: '.$cnt_pro_stk.'</strong>
					';
					
					if($rel['product_type']=='0'){
						echo '<br/><button type="button" class="btn btn-primary" onclick="open_inv_srl_no('.$rel['trancation_id'].',\''.$product_name.'\');" title="Add Serail No.">Serial No.</button>';
					}
						
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
						<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['trancation_id'].',\' tbl_invoicetrntemp\',\'tempinvoicetrn_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['trancation_id'].',\' tbl_invoicetrntemp\',\'tempinvoicetrn_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
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
			$q = $dbcon -> query("SELECT mst.*,pro.product_name FROM tbl_invoicetrn as mst left join product_mst as pro on mst.product_id=pro.product_id WHERE trancation_id = '$POST[id]'");
			$r = $q->fetch_assoc();
			/*if(strtolower($POST['table'])=='tbl_invoicetrntemp')
			{
				$row['producthtml']=getproduct($dbcon,0,'0,2');
			}
			else
			{
					$row['producthtml']=getproduct($dbcon,0,'0,2');
			}*/
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');
			
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['trancation_status']=2;	
			
			$updateid=update_record("tbl_invoicetrn", $info, "trancation_id=".$POST['eid'] , $dbcon);
			
			$info_gen['genral_book_status']=2;	
			$updateid1=update_record("tbl_general_book", $info_gen, "table_name='tbl_invoicetrn' and table_id=".$POST['eid'] , $dbcon);
			

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "last_rate")
		{
			$query="select product_rate,trancation_id,trancation_status,product_id from tbl_invoicetrn as trn left join tbl_invoice as mst on mst.invoice_id=trn.invoice_id where cust_id=".$POST["cust_id"]." and product_id=".$POST["product_id"]." and trancation_status=0 order by trancation_id DESC";
			$prel=mysqli_fetch_assoc($dbcon->query($query));
			echo $prel['product_rate'];
		}
		else if(strtolower($POST['mode'])== "load_consignee")
		{
				echo get_custmer_consignee($dbcon,$POST['cust_id']);
		}
		else if(strtolower($POST['mode'])== "load_sales_order")
		{
				echo get_sales_order($dbcon,$POST['cust_id']);
		}
		else if(strtolower($POST['mode'])== "load_sales_order_data")
		{
			$q = $dbcon -> query("SELECT * from tbl_sales_order where sales_order_id=".$POST['sales_order_id']);
			$rel = $q->fetch_assoc();
			
			$resp['sales_order_no'] = $rel['sales_order_no'];
			$resp['sales_order_date'] = date("d-m-Y",strtotime($rel['sales_order_date']));
			//$resp['pro_html'] = get_sales_order_data($dbcon,$POST['sales_order_id']);
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_sales_pro")
		{
			$resp['pro_html']=getproduct($dbcon,0,'0,2,3');
			echo json_encode($resp);
		}
		
		else if(strtolower($POST['mode'])== "loadsales_producttypedata")
		{
			$resp['pro_html'] 			= get_sales_order_typewise_data($dbcon,$POST['type_id'],$POST['sales_order_id']);
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "loadsales_productdata")
		{
			$q = $dbcon -> query("SELECT * from tbl_sales_ordertrn where sales_order_id=".$POST['sales_order_id']." and sales_ordertrn_status=0 and product_id=".$POST['product_id']." ");
			$resp = $q->fetch_assoc();
			
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_qty")
		{
		    
				echo getsale_productqty($dbcon,$POST['product_id']);
			
			
		}
		else if(strtolower($POST['mode'])== "load_rate_hist")
		{
			$resp='';
			$query="select inv.*,cust.company_name,pro.product_name,trn.product_rate from tbl_invoice as inv
					inner join tbl_invoicetrn as trn on inv.invoice_id=trn.invoice_id 
					inner join tbl_customer as cust on cust.cust_id=inv.cust_id
					inner join tbl_product as pro on pro.product_id=trn.product_id
					where inv.invoice_status=0 and trn.trancation_status=0 and inv.cust_id=".$POST["cust_id"]." and trn.product_id=".$POST["product_id"]." order by trn.trancation_id DESC LIMIT 10";
				
			$rs_prel=$dbcon->query($query);
			$rs_prel_num_rows=mysqli_num_rows($rs_prel);
				
			if($rs_prel_num_rows>0){
				while($prel=mysqli_fetch_assoc($rs_prel)){
			
					$resp.='<tr>
								<td class="text-center">'.$prel['invoice_no'].'</td>
								<td class="text-center">'.date('d-m-y',strtotime($prel['invoice_date'])).'</td>
								<td class="text-center">'.$prel['product_rate'].'</td>
							</tr>';
					$row['cust_name']=$prel['company_name'];
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
		else if(strtolower($POST['mode'])=="load_stock_qty")
		{
			$product_id=$POST['product_id'];
			$get_pro_type_qry="select product_type from product_mst where product_id=".$product_id;
			$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
			
			$product_type_arr = array("0", "1", "2", "3", "4", "5");
			if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
				echo get_current_stock($dbcon,$product_id);
			}
			else{
				echo 9999;
			}
			
		}
		else if(strtolower($POST['mode'])=="copy_quot_trn_data"){
			$deleteid=delete_record('tbl_invoicetrn',"trancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
			
			$qt_qry="select * from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$POST['quotation_id'];
			$qt_qry_rs=$dbcon->query($qt_qry);
			while($qt_trn=mysqli_fetch_assoc($qt_qry_rs)){
				$info1=array();
				
				$info1['ref_quot_trn_id']	= $qt_trn['quot_trn_id'];
				$info1['product_id']		= $qt_trn['product_id'];
				$info1['description']		= $qt_trn['product_desc'];
				$info1['product_qty']		= $qt_trn['product_qty'];
				$info1['product_rate']		= $qt_trn['product_rate'];
				$info1['unit_id']			= $qt_trn['unit_id'];
				$info1['product_discount']	= $qt_trn['product_discount'];
				$info1['discount_per']		= $qt_trn['discount_per'];
				$info1['formulaid']			= $qt_trn['formulaid'];
				$info1['product_amount']	= $qt_trn['product_amount'];
				$info1['taxable_value']		= $qt_trn['taxable_value'];
				$info=get_product_tax($dbcon,$qt_trn['product_amount'],$qt_trn['formulaid']);
				$info1=array_merge($info1,$info);
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				$info1['trancation_status']	= 3;
				$inserid=add_record('tbl_invoicetrn', $info1, $dbcon);
			}
		}
		else if(strtolower($POST['mode'])=="copy_comp_spare_trn_data"){
			$deleteid=delete_record('tbl_invoicetrn',"trancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
			
			$qt_qry="select * from tbl_complain_spare_part where s_inv_status=0 and s_paid_status='paid' and s_comp_id=".$POST['complaint_id'];
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
				$info1['trancation_status']	= 3;
				$inserid=add_record('tbl_invoicetrn', $info1, $dbcon);
			}
			
			//Entry Service Charge
			$comp_trn_qry="select trn.* from tbl_complaint_trn as trn
			where trn.comp_pro_sts=2 and trn.complaint_trn_status=0 and trn.complaint_id=".$POST['complaint_id'];
			$comp_trn_rel=mysqli_fetch_assoc($dbcon->query($comp_trn_qry));
			if($comp_trn_rel['product_id']){
				$info1=array();
				
				$info1['product_id']		= 2862;//Fixed Product ID
				$info1['product_qty']		= 1;
				$info1['product_rate']		= $comp_trn_rel['comp_amount'];
				$info1['product_amount']	= $comp_trn_rel['comp_amount'];
				$info1['taxable_value']		= $comp_trn_rel['comp_amount'];
				$info=get_product_tax($dbcon,$info1['product_amount'],$info1['formulaid']);
				$info1=array_merge($info1,$info);
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				$info1['trancation_status']	= 3;
				$inserid=add_record('tbl_invoicetrn', $info1, $dbcon);
			}
		}
		else if(strtolower($POST['mode'])=="add_pro_srl_no"){
			$info1['pro_srl_no']	= $POST['pro_srl_no'];
			$info1['trancation_id']	= $POST['trancation_id'];
			$info1['user_id']		= $_SESSION['user_id'];
			$table='tbl_inv_srl_trn';$tableid='inv_srl_trn_id';
			if(!empty($POST['invoice_id'])) {
				$info1['invoice_id']= $POST['invoice_id'];
			}
			$inserid=add_record($table, $info1, $dbcon);
		}
	else if(strtolower($POST['mode'])=="show_pro_srl_no") {
		$str='';
		if($POST['trancation_id']){
			$query="select trn.* from tbl_inv_srl_trn as trn 
			where trn.inv_srl_trn_status=0 and trn.trancation_id=".$POST['trancation_id'];
		}
		
		$result=$dbcon->query($query);
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				$str.='<tr> 
					<td style="vertical-align:top;">
						<strong>'.$i.'</strong>
					</td>
					<td style="vertical-align:top;">
						<strong>'.$rel['pro_srl_no'].'</strong>
					</td>
					<td style="vertical-align:middle"> 
						<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_inv_srl_data('.$rel['inv_srl_trn_id'].')">X</button>
					</td>
				</tr>';
				$i++;
			}
		}
		else{
			$str.= '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
		
		echo $str;
	}
	else if(strtolower($POST['mode'])== "delete_inv_srl_data") {
		$row=array();
		$info['inv_srl_trn_status']=2;	
		$updateid=update_record('tbl_inv_srl_trn', $info, "inv_srl_trn_id=".$POST['inv_srl_trn_id'] , $dbcon);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "count_pro_srl_no") {
		$cnt_srl_qry="select count(inv_srl_trn_id) srl_qty,(select product_qty from tbl_invoicetrn where trancation_id=".$POST['trancation_id'].") as act_qty from tbl_inv_srl_trn where inv_srl_trn_status=0 and trancation_id=".$POST['trancation_id'];
		$cnt_srl_rel=mysqli_fetch_assoc($dbcon->query($cnt_srl_qry));
		if(floatval($cnt_srl_rel['act_qty'])>floatval($cnt_srl_rel['srl_qty'])){
			echo "1";
		}
		else{
			echo "0";
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
function upd_qt_done_sts($dbcon,$quotation_id,$invoice_id){
	$qt_trn_qry="select sum(product_qty) as qt_qty from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$quotation_id;
	$qt_trn_rel=mysqli_fetch_assoc($dbcon->query($qt_trn_qry));
	//Invoice Qty
	$inv_trn_qry="select sum(product_qty) as inv_qty from tbl_invoicetrn as trn
	inner join tbl_invoice as inv on inv.invoice_id=trn.invoice_id
	where trn.trancation_status=0 and inv.invoice_status=0 and inv.quotation_id=".$quotation_id;
	$inv_trn_rel=mysqli_fetch_assoc($dbcon->query($inv_trn_qry));
	
	if(floatval($inv_trn_rel['inv_qty'])>=$qt_trn_rel['qt_qty']){
		$upd_qt="update tbl_quotation set inv_done_status=1 where quotation_id=".$quotation_id;
		$upd_qt_rs=$dbcon->query($upd_qt);
	}
	
	//Update Quotation trn rows
	$upd_qt_trn_qry="update tbl_quotation_trn set inv_done_status=1 where quot_trn_status=0 and find_in_set(quot_trn_id,(select group_concat(ref_quot_trn_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=".$invoice_id."))";
	$upd_qt_trn_qry_rs=$dbcon->query($upd_qt_trn_qry);
}
function upd_spare_inv_sts($dbcon,$complaint_id,$invoice_id){
	//Update Quotation trn rows
	$upd_qt_trn_qry="update tbl_complain_spare_part set s_inv_status=1 where s_inv_status=0 and find_in_set(s_id,(select group_concat(ref_s_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=".$invoice_id."))";
	$upd_qt_trn_qry_rs=$dbcon->query($upd_qt_trn_qry);
	
	$upd_comp_trn_qry="update tbl_complaint_trn set inv_done_status=1 where complaint_id=".$complaint_id;
	$upd_comp_trn_qry_rs=$dbcon->query($upd_comp_trn_qry);
}
function upd_inv_srl_no($dbcon,$invoice_id){
	$upd_qry="update `tbl_inv_srl_trn` set invoice_id=$invoice_id where find_in_set(trancation_id,(select group_concat(trancation_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=$invoice_id));";
	$upd_qry_rs=$dbcon->query($upd_qry);
}
function copy_srl_no($dbcon,$invoice_id){
	//Invoice DATA
	$inv_qry="select cust_id,invoice_no,invoice_date from tbl_invoice where invoice_id=".$invoice_id;
	$inv_rel=mysqli_fetch_assoc($dbcon->query($inv_qry));
	
	$srl_qry="select srl.pro_srl_no,(select product_id from tbl_invoicetrn where trancation_id=srl.trancation_id) as pro_id from tbl_inv_srl_trn as srl where srl.inv_srl_trn_status=0 and srl.invoice_id=".$invoice_id;
	$srl_qry_rs=$dbcon->query($srl_qry);
	while($srl_rel=mysqli_fetch_assoc($srl_qry_rs)){
		$info1['cust_id']				= $inv_rel['cust_id'];
		$info1['sold_inv_foc_date']		= date("Y-m-d",strtotime($inv_rel['invoice_date']));
		$info1['product_id']			= $srl_rel['pro_id'];
		$info1['sold_pro_srl_no']		= $srl_rel['pro_srl_no'];
		$info1['cdate']					= date("Y-m-d H:i:s");
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];
		
		$table='tbl_cust_sold_pro';$tableid='cust_sold_pro_id';
		$inserid=add_record($table, $info1, $dbcon);
		
	}
}
function general_book_tax_entry($dbcon,$invoice_id,$ref_date){
	$qry1="select group_concat(trancation_id) as tid from tbl_invoicetrn as cert where trancation_status=0 and invoice_id=".$invoice_id;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	$qry122="select * from tbl_invoice as cert where invoice_status=0 and invoice_id=".$invoice_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	$qry="SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax WHERE tax_used_status=0 and used_transaction_id in (".$re["tid"].") and table_name='tbl_invoicetrn' group by ledger_id order by tax_used_id desc";
	$row=$dbcon->query($qry);
	while($tax=mysqli_fetch_assoc($row))
	{
		$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$invoice_id." and table_name='tbl_invoice'";
			$ros=$dbcon->query($qry12);
			$re2=mysqli_fetch_assoc($ros);
		
	
		$info1['table_name']	= "tbl_invoice";
		$info1['table_id']		= $invoice_id;
		$info1['ref_date']		= date("Y-m-d",strtotime($rea['invoice_date']));
		$info1['entry_type']	= 1;
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
function general_book_sercices_entry($dbcon,$invoice_id){
	$qry1="select group_concat(trancation_id) as tid from tbl_invoicetrn as cert where trancation_status=0 and invoice_id=".$invoice_id;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	$qry122="select * from tbl_invoice as cert where invoice_status=0 and invoice_id=".$invoice_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	$qry="SELECT itrn.*,promst.ledger_id FROM `tbl_invoicetrn` as itrn 
			left join product_mst as promst on promst.product_id=itrn.product_id
			WHERE itrn.trancation_status=0 and promst.product_type=8 and itrn.invoice_id=".$invoice_id." order by itrn.trancation_id desc";
	$row=$dbcon->query($qry);
	while($tax=mysqli_fetch_assoc($row))
	{
		$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$tax['trancation_id']." and table_name='tbl_invoicetrn'";
			$ros=$dbcon->query($qry12);
			$re2=mysqli_fetch_assoc($ros);
		
	
		$info1['table_name']	= "tbl_invoicetrn";
		$info1['table_id']		= $tax['trancation_id'];
		$info1['ref_date']		= date("Y-m-d",strtotime($rea['invoice_date']));
		$info1['entry_type']	= 1;
		$info1['ledger_id']		= $tax['ledger_id'];
		$info1['amount']		= $tax['product_amount'];
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