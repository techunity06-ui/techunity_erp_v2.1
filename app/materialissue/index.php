<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    INVENTORY_MATERIALISSUE_LIST_SLUG_VIEW,INVENTORY_MATERIALISSUE_LIST_SLUG_CREATE,INVENTORY_MATERIALISSUE_LIST_SLUG_UPDATE,INVENTORY_MATERIALISSUE_LIST_SLUG_DELETE
]);
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
			
			
		//check_user('invoice')
			// if(!empty($POST['type_id']))
			// {
			// 	$where .=" and invoice.invoicetype_id=".$POST['type_id'];
			// }
			// $where.="  and invoice_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('material_issue_no','material_issue_date','material_id');
			$sIndexColumn = "material_id";
			$isWhere = array("material_issue_status = '0'".$where);
			$sTable = "tbl_material_issue as tmi";			
			$isJOIN = array('');
			$hOrder = "tmi.material_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'materialissueedit/'.$row['material_id'].'">'.$row["material_issue_no"].'</a>';
				$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'materialissueedit/'.$row['material_id'].'">'.date('d M, Y',strtotime($row["material_issue_date"])).'</a>';
				
				/*if($row['g_total']>$row['paid_amount'])
				{
				$row_data[] = "<div class='external-event label label-warning ui-draggable' style='position: relative;'>DUE (RS. ".($row['g_total']-$row['paid_amount']).")</div>";
				}
				else
				{
					$row_data[]="<div class='external-event label label-success ui-draggable' style='position: relative;'>Paid</div>";;
				}*/
				
				
				$addpayment='';$delete='';$edit='';
				
					// $print='<a class="btn btn-xs btn-info" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'invoicereceipt/'.$row['invoice_id'].'"><i class="fa fa-print"></i></a> ';
				if($_SESSION['user_type']!=2){
					if($_SESSION['user_id']==$row['user_id']){
						if(in_array(INVENTORY_MATERIALISSUE_LIST_SLUG_DELETE,$bulkAccessArray)){
							$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['material_id'].')"><i class="fa fa-trash-o"></i></button>';
						}
						
						if(in_array(INVENTORY_MATERIALISSUE_LIST_SLUG_UPDATE,$bulkAccessArray)){
							$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'materialissueedit/'.$row['material_id'].'"><i class="fa fa-pencil"></i></a>';
						}
						
					}else{
						$delete='';
						$edit=''; 
						
					}
				}else{
					if(in_array(INVENTORY_MATERIALISSUE_LIST_SLUG_DELETE,$bulkAccessArray)){
						$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['material_id'].')"><i class="fa fa-trash-o"></i></button>';
					}
					
					if(in_array(INVENTORY_MATERIALISSUE_LIST_SLUG_UPDATE,$bulkAccessArray)){
						$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'materialissueedit/'.$row['material_id'].'"><i class="fa fa-pencil"></i></a>';
					}
					
				}
				
				$row_data[] =''.$edit.' '.$delete.' '.$addpayment.' ';
				
				
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
			$info['material_issue_no']		= $POST['material_issue_no'];
			$info['material_issue_date']	= date('Y-m-d',strtotime($POST['material_issue_date']));
			$info['remarks']			= text_rnremove($POST['remarks']);
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$info['material_issue_status']		= 0;
			$info['user_id']		= $_SESSION['user_id'];
						//	$info['usertype_id']		= $_SESSION['usertype_id'];
							// if(isset($POST['save_print']))
							// {
							// 	$info['print_status']	= $POST['print_status'];
							// }
			$inserinvoiceid=add_record('tbl_material_issue', $info, $dbcon);
			
			/*Update Trn Table Start*/
			if($inserinvoiceid){
				$infotrn['material_id']			= $inserinvoiceid;
				$infotrn['material_issue_trn_status']	= 0;
				$updatetrnid=update_record('tbl_material_issue_trn', $infotrn,"material_issue_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			}
		// $query="select trn.*,pro_mst.product_base_unit from tbl_invoicetrn as trn
		// left join product_mst as pro_mst on pro_mst.product_id=trn.product_id
		// where trancation_status=0 and invoice_id=".$inserinvoiceid;
		// $result=$dbcon->query($query);
		// while($row=mysqli_fetch_assoc($result)){
		// 	if($row['unit_id']!=0){
		// 		minus_stock($dbcon,$row['product_id'],$row['unit_id'],$info['invoice_date'],"invoice_trn",$row['trancation_id'],$row['product_qty']);
		// 	}else{
		// 		minus_stock($dbcon,$row['product_id'],$row['product_base_unit'],$info['invoice_date'],"invoice_trn",$row['trancation_id'],$row['product_qty']);
		// 	}
		// }
			
			
			
		//Insert LOG
			$log_entry=common_log_entry($dbcon,"material_issue_add",1,"tbl_material_issue",$inserinvoiceid);	
			
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
			$info['material_issue_no']		= $POST['material_issue_no'];
			$info['material_issue_date']	= date('Y-m-d',strtotime($POST['material_issue_date']));
			$info['remarks']			= text_rnremove($POST['remarks']);
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$info['material_issue_status']		= 0;
			$info['user_id']		= $_SESSION['user_id'];
			$updateid=update_record('tbl_material_issue', $info,"material_id=".$POST['eid'] , $dbcon);
			
		//Insert LOG
			$log_entry=common_log_entry($dbcon,"material_issue_edit",2,"tbl_material_issue",$POST['eid']);
			
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
			
			$info['material_issue_trn_status']	= 2;
			
			$info_sales_order['material_issue_status']  = 2;
			
			$updatesalesid=update_record('tbl_material_issue', $info_sales_order,"material_id=".$POST['eid'], $dbcon);
			$updateinvoiceid=update_record('tbl_material_issue_trn', $info,"invoice_id=".$POST['eid'] , $dbcon);	
			
		//Insert LOG
			$log_entry=common_log_entry($dbcon,"delete_material",3,"tbl_material_issue_trn",$POST['eid']);
			echo "1";

			// if($updatetrancationid)
			// 	echo "1";	
			// else
			// 	echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
			
			$info1['product_id']		= $POST['product_id'];
			$info1['issue_qty']		= $POST['product_qty'];
			$info1['work_order_id']		= $POST['work_order_id'];
			$info1['user_id']	= $_SESSION['user_id'];
			$info1['cdate']			= date("Y-m-d H:i:s");
			$info1['user_id']		= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['material_issue_trn_status']	= 3;
			$table='tbl_material_issue_trn';$tableid='material_trn_id';
			if(!empty($POST['eid'])){
				$info1['material_id']= $POST['eid'];
			}
				// if(!empty($POST['material_trn_id'])){
				// 	$info1['material_trn_id']= $POST['material_trn_id'];
				// }
				// else{
				// 	$info1['material_issue_trn_status']	= 3;
				// }

			if(empty($POST['edit_id'])){
				
				$inserid=add_record($table, $info1, $dbcon);
			}
			else{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				$inserid=$POST['edit_id'];
			}
		}
		
		else if(strtolower($POST['mode'])== "load_productdata")
		{
			$pid=$POST['eid'];
			//$qry="select * from tbl_product where product_id=".$POST['eid'];
			$qry="select * from product_mst where product_id=$pid";
			$result=$dbcon->query($qry);
			$row=mysqli_fetch_assoc($result);
			
			$qry1="select led.stateid as lst,com.stateid as cst from tbl_ledger as led 
			left join tbl_company as com on com.company_id=led.company_id
			where l_id=".$POST['cust_id'];
			$result1=$dbcon->query($qry1);
			$row1=mysqli_fetch_assoc($result1);
			
			if($row1['lst']==$row1['cst']){
				$qry2="select * from formula_mst as led 
				where formula_status=0 and tax_cat='INTRA' and tax_per_id=".$row['product_sale_gst'];
				$result2=$dbcon->query($qry2);
				$row2=mysqli_fetch_assoc($result2);
				$row['fom_id']=$row2['formulaid'];
			}else{
				$qry2="select * from formula_mst as led 
				where formula_status=0 and tax_cat='INTER' and tax_per_id=".$row['product_sale_gst'];
				$result2=$dbcon->query($qry2);
				$row2=mysqli_fetch_assoc($result2);
				$row['fom_id']=$row2['formulaid'];
			}
			
			echo json_encode( $row );
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
				$query="select tmit.*,tsmp.po_req_no,product.product_name,product.product_type,cat.unit_name from  tbl_material_issue_trn as tmit
				left join product_mst as product on product.product_id=tmit.product_id  
				left join tbl_set_main_process as tsmp on tsmp.sp_id=tmit.work_order_id  
				left join unit_mst as cat on cat.unitid=product.product_base_qty 
				where tmit.material_issue_trn_status in (0,3) and  tmit.material_id=".$POST['eid'];
			}
			else{
				$query="select tmit.*,tsmp.po_req_no,product.product_name,product.product_type,cat.unit_name from  tbl_material_issue_trn as tmit
				left join product_mst as product on product.product_id=tmit.product_id  
				left join tbl_set_main_process as tsmp on tsmp.sp_id=tmit.work_order_id  
				left join unit_mst as cat on cat.unitid=product.product_base_qty 
				where material_issue_trn_status=3 and tmit.material_id=0 and tmit.user_id=".$_SESSION['user_id'];
			}
			
			$result=$dbcon->query($query);
			echo ' <div class="form-group">
			<div class="col-md-12 col-xs-11">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
			<th class="text-center" width="25%">Work Order No</th>
			<th class="text-center"width="8%">Product</th>
			<th class="text-center"width="8%">Issue Qty</th>
			<th class="text-center"width="10%">Action</th>
			</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					$product_type_arr = array("0", "1", "2", "3", "4", "5");
					if (in_array($rel['product_type'], $product_type_arr)){
						$cnt_pro_stk=get_current_stock_new($dbcon,$rel['product_id'],$rel['unit_id']);
					}
					else{
						$cnt_pro_stk=9999;
					}
					$product_name=$dbcon->real_escape_string($rel['product_name']);
					echo '<tr id="fieldtr'.$id.'" class="dataexist">
					<td style="vertical-align:top;" class="text-center">
					'.$rel['po_req_no'].'
					</td>
					<td style="vertical-align:top;">
					<b>'.$rel['product_name'].'</b>
					</td>
					
					<td style="vertical-align:top;" class="text-center">
					'.$rel['issue_qty'].'
					</td>';
					
					
					echo '
					<td style="vertical-align:top">
					<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['material_trn_id'].',\'  tbl_material_issue_trn\',\'material_trn_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['material_trn_id'].',\' tbl_material_issue_trn\',\'material_trn_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
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
			
			$q = $dbcon->query("SELECT mst.*,pro.product_name FROM tbl_material_issue_trn as mst left join product_mst as pro on mst.product_id=pro.product_id WHERE material_trn_id = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['material_issue_trn_status']=2;	
			
			$updateid=update_record("tbl_material_issue_trn", $info, "material_trn_id=".$POST['eid'] , $dbcon);
			
			$row['res']="1";
			// if($updateid)
			// 	$row['res']="1";
			// else
			// 	$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])=="load_stock_qty")
		{
			$product_id=$POST['product_id'];
			$get_pro_type_qry="select product_type,product_base_unit from product_mst where product_id=".$product_id;
			$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
			$product_type_arr = array("0", "1", "2", "3", "4", "5");
			if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
				if(!empty($POST['unit_id'])){
					$unit_id=$POST['unit_id'];
				}else{
					$unit_id=$get_pro_type_rel['product_base_unit'];
				}
				echo get_current_stock_new($dbcon,$product_id,$unit_id);
			}
			else{
				echo 9999;
			}
		}
		
		?>