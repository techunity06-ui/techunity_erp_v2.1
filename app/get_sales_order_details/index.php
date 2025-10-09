<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
include("../../include/function_database_query.php");
//check permission for get sales order details
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    MRP_GET_SALES_ORDER_SLUG_VIEW,MRP_GET_SALES_ORDER_SLUG_CREATE
]);
$companyConfiguration=getCompanyConfiguration($dbcon);
	
$production_pro_search = $companyConfiguration['production_pro_search'];
$pro_search=explode(",", $production_pro_search);
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ 
{ 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "generate_report_min_new") {
			
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/
		
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
		$where_db = check_branch('so_trn', $branch_id);
		
		$appData = array();
		$i=1;
		//+IFNULL(qc_total_rejected,0)

		$aColumns = array('mst.product_icode', 'dr.drawing_number','so.sales_order_no','so.sales_order_date','led.l_name','so_trn.product_qty','so_trn.sales_ordertrn_id','mst.product_name','tc.cat_name','so.delivery_date','bran.branch_name','so_trn.product_id','so_trn.work_order_qty','so_trn.unit_id','(IFNULL(product_qty,0)-IFNULL(stock_add,0)) as pending_qty,so.jobwork_type');

		$sIndexColumn = "so_trn.sales_ordertrn_id";
		$isWhere = array("so_trn.sales_ordertrn_status=0 and so_trn.bom_status=1 and so_trn.production_status=0 and mst.product_type!=8 and so.order_accept_status = 1 and so.approve_status=3".$where_db);

		$sTable = "tbl_sales_ordertrn as so_trn";

		$isJOIN = array("left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id","left join tbl_ledger as led on led.l_id=so.cust_id","left join product_mst as mst on mst.product_id=so_trn.product_id","left join tbl_category as tc on mst.product_category=tc.cat_id","left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.sales_ordertrn_id from tbl_sales_order_production_trn as qc 
		where qc.sales_order_production_status=0 group by qc.sales_ordertrn_id) as qc on qc.sales_ordertrn_id=so_trn.sales_ordertrn_id","left join branch_mst as bran on bran.branch_id=so_trn.branch_id","left join tbl_drawing as dr on dr.drawing_id = mst.drawing_id");
		
		$hOrder = "so.delivery_date desc";
		//$hGroupby = "pro.product_id";
		$having=" pending_qty > 0";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		
		//print_r($sqlReturn);
		foreach($sqlReturn as $row) {
				
			$row_data = array();
			//tbl_sales_order_production_trn
			//$pendingqty=$row['product_qty']-$row['work_order_qty'];
			$pendingqty=$row['pending_qty'];
				
			$cstock=get_current_stock_new($dbcon,$row["product_id"],$row["unit_id"]);
			$rstock=reserve_stock($dbcon,$row["product_id"],$row["unit_id"]);
			$actualstock=$cstock-$rstock; 
			$row_data[] = $row['sales_order_no'];
			$row_data[] = $row['sales_order_date'];
			$row_data[] = $row['l_name'];

			$drawing_number = "";
			$item_code = "";
			 if(in_array('drawing',$pro_search)){
		            $drawing_number = " -- (".$row['drawing_number'].")";
		        }
		        if(in_array('item',$pro_search)){
		            $item_code = " -- (".$row['product_icode'].")";
		        }	


			$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['product_qty'];
			$row_data[] = $pendingqty;
			$row_data[] = $actualstock;
			$row_data[] = date('d M, Y',strtotime($row["delivery_date"]));

			$view='';
			if(in_array(MRP_GET_SALES_ORDER_SLUG_CREATE,$bulkAccessArray)) {

				if($companyConfiguration['trading_stock']==0){
				$view='<a class="btn btn-xs btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" href="'.ROOT.'production/sorequesproduct/'.$row['product_id'].'/'.$row['sales_ordertrn_id'].'"><i class="fa fa-paper-plane"></i> Request</a>';
				}	
				$sno="'".$row['sales_order_no']."'";
					$pno="'".$row['product_name']."'";
				$apprv_btn='<button type="button" class="btn btn-xs btn-success" data-original-title="Alloca" data-toggle="tooltip" data-placement="top" onClick="open_approv_quo1('.$sno.','.$pno.','.$row["sales_ordertrn_id"].','.$row["product_id"].','.$pendingqty.')"><i class="fa fa-exclamation-triangle"></i></button>';

				$stock_allocate='<button type="button" class="btn btn-xs btn-success" data-original-title="Allocate Stock" data-toggle="tooltip" data-placement="top" onClick="open_stock_allocation_so('.$row["sales_ordertrn_id"].')">Allocate Stock</button>';
				
			}
			if($companyConfiguration['outside_jobwork']){

			  	if($row['jobwork_type'] == '0'){
			  		$row_data[] ='<button class="btn btn-xs btn-success" data-original-title="Normal Jobwork" data-toggle="tooltip" data-placement="top">Normal</button>';

			  	}else{
			  		$row_data[] ='<button class="btn btn-xs btn-danger" data-original-title="Outside Jobwork" data-toggle="tooltip" data-placement="top">Outside Jobwork</button>';

			  	}
		  	}
			
			if($_SESSION['branch_id']==0){
				$row_data[] = $row['branch_name'];
			}
			
			$row_data[] = $view.' '.$apprv_btn.' '.$stock_allocate;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode($output );
				
		}
		else if(strtolower($POST['mode']) == "load_entry_stock"){
			$q="select * from tbl_sales_ordertrn as gd where sales_ordertrn_status=0 and sales_ordertrn_id=".$POST['ref_sales_order_trn_id'];
			$rel=$dbcon->query($q);
			//$str=array();
			
			$row=mysqli_fetch_array($rel);
			$godown=get_godown_stock_so($dbcon,$row['product_id'],$row['unit_id']);
			$work_order=get_min_max_work_order_stock($dbcon,$row['product_id']);
			if($companyConfiguration['trading_stock']==0){
				$html="
					<div class='col-md-5' > 
						".$godown."
					</div>
					<div class='col-md-7' >
						".$work_order."
					</div>
					<div class='col-md-12'>
						<center>
							<button type='submit' class='btn btn-success' id='save' name='save'>Save</button>
						</center>
					</div>
				";
			}else{
					$html="
					<div class='col-md-12' > 
						".$godown."
					</div>
					<div class='col-md-12'>
						<center>
							<button type='submit' class='btn btn-success' id='save' name='save'>Save</button>
						</center>
					</div>
				";

			}
			
			echo $html;
		}
		else if(strtolower($POST['mode']) == "add"){
			$q="select * from tbl_sales_ordertrn as gd where sales_ordertrn_status=0 and sales_ordertrn_id=".$POST['ref_sales_order_trn_id'];
			$rel=$dbcon->query($q);
			
			$row=mysqli_fetch_array($rel);
			foreach ($POST['so_godown'] as $i => $name) 
			{
				$godwn_id=$POST['so_godown'][$i];
				$stock=$POST['so_stock'][$i];
				if($stock>0){
					$info_e['sales_ordertrn_id']	=$row['sales_ordertrn_id'];
					$info_e['product_id']			=$row['product_id'];
					$info_e['product_qty']			=$stock;
					$info_e['godown_id']			=$godwn_id;
					$info_e['unit_id']				=$row['unit_id'];
					$info_e['allocate_qty']			=$stock;
					$info_e['remaning_invoice_qty']	=$stock;
					
					$info_e['cdate']				=date("Y-m-d");
					$info_e['company_id']			=$_SESSION['company_id'];
					$info_e['user_id']				=$_SESSION['user_id'];
					$inserinvoiceidexp=add_record('tbl_sales_order_production_trn', $info_e, $dbcon,$row['branch_id']);
					add_so_reserve_stock($dbcon,$stock,$row['unit_id'],$row['product_id'],$row['sales_ordertrn_id'],$godwn_id,"",$row['branch_id']);
				}
			
			}
			
			foreach ($POST['so_req_id'] as $p => $name1) 
			{
				$request_id=$POST['so_req_id'][$p];
				$stock_alo=$POST['so_working_stock'][$p];
				if($stock_alo>0){
					$info_w['sales_ordertrn_id']	=$row['sales_ordertrn_id'];
					$info_w['product_id']			=$row['product_id'];
					$info_w['product_qty']			=$stock_alo;
					$info_w['request_id']			=$request_id;
					$info_w['unit_id']				=$row['unit_id'];
					
					$info_w['cdate']				=date("Y-m-d");
					$info_w['company_id']			=$_SESSION['company_id'];
					$info_w['user_id']				=$_SESSION['user_id'];
					$inserinvoiceidexp1=add_record('tbl_sales_order_production_trn', $info_w, $dbcon,$row['branch_id']);
				}
			
			}
			
			if($inserinvoiceidexp || $inserinvoiceidexp1){
				$arr['msg']="1";
            }else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
			
		}
		else if(strtolower($POST['mode']) == "set_version"){
			$product_id = $_POST['product_id'];
			$sales_ordertrn_id = $_POST['sales_ordertrn_id'];
			$qty = $_POST['qty'];				
			
			$check_sales_order = "select * from tbl_sales_ordertrn where sales_ordertrn_id = '$sales_ordertrn_id' AND bom_id ='0' AND bom_status = '1' ";
			$check_sales_order_res = $dbcon->query($check_sales_order);
			if(brp_mysqli_num_rows($check_sales_order_res)>0)
			{
				$product_bom_query="select * from tbl_bom where bom_version_id IN (SELECT bom_version_id FROM `pro_ms_bom_version` WHERE  is_default_bom = '1' AND  product_id=".$_POST['product_id'].")" ;
				if(brp_mysqli_num_rows($product_bom_query)>0)
				{
					
					$product_bom_row=brp_mysqli_fetch_assoc($dbcon->query($product_bom_query));			
					$info['bom_id'] = $product_bom_row['bom_id'];
					$info['bom_status'] = $product_bom_row['bom_status'];			
					$updateid=update_record('tbl_sales_ordertrn', $info,"sales_ordertrn_id=".$_POST['sales_ordertrn_id'] ,$dbcon,$POST['branch_id']);
				}
				else
				{
					
					$info['bom_status'] = 0;			
					$updateid=update_record('tbl_sales_ordertrn', $info,"sales_ordertrn_id=".$_POST['sales_ordertrn_id'] ,$dbcon,$POST['branch_id']);
					add_requst_rnd($dbcon,$product_id,$sales_ordertrn_id,$qty);
				}
			
			echo "1";	
			
			}
			else
			{
				echo "0";	
			}
			
			
		}
		else if(strtolower($POST['mode']) == "ger_version_by_product"){
			
		$product_id = $_POST['product_id'];
		$sales_ordertrn_id= $_POST['sales_ordertrn_id'];
		$qty= $_POST['qty'];
		
		
		
		$qry="SELECT * from pro_ms_bom_version where product_id=".$POST['product_id'];
		$result=$dbcon->query($qry);
		
		$versionstr = '';
		
		if(brp_mysqli_num_rows($result) > 0)
		{	
			while($row=brp_mysqli_fetch_assoc($result))
			{
				$versionstr .= '<option value="'.$row['bom_version_id'].'">'.$row['version_name'].'</option>';
			}
				$versionstr .= '<option selected="selected"  value="10000">R&D</option>';
		}
		else
		{
			$versionstr .= '<option selected="selected"  value="10000">R&D</option>';
		}
			
			$str='<table class="table table-bordered">	<tr>
								<th colspan="2" style="text-align: center;"> <strong>Bom Version:</strong></th>
								<th colspan="3"><select class="select2 selproduct1" title="Select Bom Version" name="add_bom_version_id" id="add_bom_version_id">'.$versionstr.'</select>
		</th></tr><th colspan="5"  style="text-align: center;"><button type="button" onclick="product_custom_versions('.$product_id.','.$sales_ordertrn_id.','.$qty.');" class="btn btn-success" id="save" name="save">Save</button></th></tr></table>';							
			echo $str;
		}
		
		else if(strtolower($POST['mode']) == "set_custom_version"){
			$product_id = $_POST['product_id'];
			$sales_ordertrn_id = $_POST['sales_ordertrn_id'];
			$version_id = $_POST['version_id'];	
			$qty = $_POST['qty'];
			
			if($version_id == "10000")
			{
				$info['bom_status'] = 0;			
				$updateid=update_record('tbl_sales_ordertrn', $info,"sales_ordertrn_id=".$_POST['sales_ordertrn_id'] ,$dbcon,$POST['branch_id']);
				add_requst_rnd($dbcon,$product_id,$sales_ordertrn_id,$qty,$version_id);				
				echo "1";
			}
			else
			{
				echo  $product_bom_query="select * from tbl_bom where bom_version_id ='$version_id' AND  bom_product=".$_POST['product_id']; 
				$product_bom_res=$dbcon->query($product_bom_query);	
				if(brp_mysqli_num_rows($product_bom_res)>0)
				{
				$product_bom_row=brp_mysqli_fetch_assoc($dbcon->query($product_bom_query));			
				$info['bom_id'] = $product_bom_row['bom_id'];
				$info['bom_status'] = $product_bom_row['bom_status'];	
						
				$updateid=update_record('tbl_sales_ordertrn', $info,"sales_ordertrn_id=".$_POST['sales_ordertrn_id'] ,$dbcon,$POST['branch_id']);
				
				add_requst_rnd($dbcon,$product_id,$sales_ordertrn_id,$qty,$version_id);		
							
				echo "1";	
				}
				else
				{
					echo "0";	
				}
			}	
			
		}
				
	}
    
}
/*
else {
    die("Error - 1");
}*/	
		
function add_requst_rnd($dbcon,$product_id,$sales_ordertrn_id,$qty,$version_id)
{
					$query1="select * from  tbl_invoicetype where type_id='9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
					$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
					$id=$rows['taxinvoice_start'];
					$id=$id+1;
					
					$new_query1="update tbl_invoicetype set taxinvoice_start = ".$id." where type_id='9'";
					$dbcon->query($new_query1);				
					
					if($rows['invoice_format']=='2')
					{
					$info1['po_req_no']	 = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
					}
					else if($rows['invoice_format']=='1')
					{
					$info1['po_req_no']	 = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
					}
					else if($rows['invoice_format']=='3'){
					$info1['po_req_no']	 = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
					}
					else{
					$info1['po_req_no']	 = str_pad($id,3,"0",STR_PAD_LEFT);
					}
					
					$info1['po_req_date']	= date("Y-m-d");
					$info1['rp_req_qty']	= $qty;
					$info1['in_process_qty_main']	= $qty;
					$info1['rp_po_qty']	= '0';
					$info1['product_id']		= $product_id;
					$info1['cdate'] 			= date("Y-m-d");
					$info1['mdate'] 			= date("Y-m-d");
					$info1['user_id']			= $_SESSION['user_id'];
					$info1['muser_id']			= $_SESSION['user_id'];
					$info1['auser_is']			= $_SESSION['user_id'];
					$info1['adata']			= '';
					$info1['vendor_id']			= '';
					$info1['bom_id']			= '';
					$info1['bom_no']			= '';
					$info1['sales_order_no']			= '';
					$info1['sales_order_date']			= strtotime(date("Y-m-d"));
					$info1['po_no']						= '';
					$info1['po_date']					= '';	
					$info1['sp_status']					= '';	
					$info1['branch_id']					= $POST['branch_id'];		
					$info1['company_id']				= $_SESSION['company_id'];			
					$info1['sales_order_trn_id']		= $sales_ordertrn_id;
					$info1['bom_version_id']		= '10000';
					$table='tbl_set_main_process';	
					
					
					//echo "<pre>"; print_r($info1); die;
					
					$inserid=add_record($table, $info1, $dbcon);
					
					
					if($inserid)
					{
						
						$info2['sp_id']					= $inserid;
						$info2['sr_no']					= 0;
						$info2['rp_req_no']				= '';
						$info2['rp_req_date']			= date("Y-m-d");
						$info2['rp_pid']				= $product_id;
						$info2['rp_req_qty']			= $qty;
						$info2['req_qty_one']			= 1;
						$info2['rp_po_qty']				= 0;
						$info2['in_process_qty']		= $qty;
						$info2['out_process_qty']		= '';
						$info2['rp_req_type']			= 'work_order';
						$info2['rp_po_req_no']			= '';
						$info2['rp_process_req_no']		= '';
						$info2['cdate']					= strtotime(date("Y-m-d"));
						$info2['user_id']				= $_SESSION['user_id'];		
						$info2['company_id']			= $_SESSION['company_id'];	
						$info2['status']				= 3;	
						$info2['row_cnt']				= 0;	
						$info2['process_unit']			= 3;	
						$info2['purchase_unit']			= 3;
						$info2['reserve_status']		= 0;
						$info2['used_rp_req_qty']		= '';
						$info2['used_status']			= 0;
						$info2['perent_id']				= 0;	
						$info2['reserve_stock']			= '';	
						$info2['main_request']			= 1;
						$info2['indent_no']				= '';	
						$info2['indent_date']			= '';
						$info2['indent_status']			= '';		
						$info2['job_card_no']			= '';
						$info2['job_card_date']			= '';		
						$info2['job_card_status']		= '';
						$info2['reject_status']			= 0;
						$info2['sales_order_trn_id']	= $sales_ordertrn_id;
						$info2['branch_id']				= '';
						$info2['finish_used_qty']		= '';
						$info2['finish_status']			= 0;
						$info2['product_version']		= '';	
						$info2['pre_trn_id']			= 0;	
						$info2['shortclose_qty']		= 0;
						$info2['shortclose_remark']		= '';
						/*$info2['work_order_no']		= '';	
						$info2['work_order_date']		= '';	
						$info2['work_order_status']		= '';	*/												
										
						$table='tbl_request_product';	
						$reqinserid=add_record($table, $info2, $dbcon);
						
						if($version_id != "10000")
						{
				
							$bom_process="SELECT * FROM `tbl_bom` as bom
							left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
							WHERE  prover.bom_version_id='".$version_id."' AND bom.bom_product='".$product_id."'"; 
							$bom_rel=mysqli_fetch_assoc($dbcon->query($bom_process));
							
							$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
							left join product_mst as pro on pro.product_id=bom_trn.product_id
							left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
							left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
							where bom_trn_status=0 and bom_id=".$bom_rel['bom_id'];	
							$result1=$dbcon->query($query1);
							$call=1;$space="";
							$i = 1;
					if(brp_mysqli_num_rows($result1) > 0){
					
					while($rel1=mysqli_fetch_assoc($result1)){  
						
						$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
						$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

						$base_qty=$base_one_qty*$info_su['rp_req_qty'];
						$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

						$info_sub['sp_id']				= $inserid;
						$info_sub['sr_no']				= $i;
						$info_sub['rp_pid']				= $rel1['product_id'];
						$info_sub['rp_req_date']		= date("Y-m-d");
						
						$info_sub['rp_req_qty']			=  $POST['qty'];
						$info_sub['req_qty_one']		= $conv_one_qty;//required qty
						$info_sub['rp_po_qty']			= "";//po qty
						$info_sub['in_process_qty']		= $POST['qty'];//process qty
						$info_sub['rp_req_type']		= "work_order";//type
						$info_sub['process_unit']		= $rel1['product_base_unit'];
						$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
						$info_sub['perent_id']			= $reqinserid;
						$info_sub['status']				= 3;
						$info_sub['user_id']			= $_SESSION['user_id'];
						$info_sub['company_id']			= $_SESSION['company_id'];
						//$info_sub['main_request']		= $POST['g_total'];
						
						$info_sub['product_version']	= $rel1['p_bom_id'];
						$info_sub['bom_id']				= $rel1['p_bom_id'];
						
						//echo "<pre>"; print_r($info_sub);die;
						$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$POST['branch_id']);
						
				/*	$query_pro1="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.status=0 and prod.product_id = ".$rel1['product_id']; */
				
			/*$query_pro1="select* from pro_bom_process where product_id = ".$rel1['product_id']." AND bom_id =".$bom_rel['bom_id'];	*/
			
			$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status=0 and bom.bom_product='".$rel1['product_id']."'"; 
		
			$rel_pro1 = $dbcon->query($query_pro1);
			
			if(brp_mysqli_num_rows($rel_pro1)>0)
			{
				while($product_process_row=brp_mysqli_fetch_assoc($rel_pro1))
				{
					$wpp_info['product_id'] = $rel1['product_id'];		
					$wpp_info['rp_id'] = 	$inserid_sub;
					$wpp_info['process_priority'] = 	$product_process_row['process_priority'];
					$wpp_info['process_time'] = 	$product_process_row['process_time'];
					$wpp_info['process_type'] = 	$product_process_row['process_type'];
					$wpp_info['process_opening'] = 	$product_process_row['process_opening'];
					$wpp_info['process_id'] = 	$product_process_row['pr_process_id'];	
					$wpp_info['cdate']				= date("Y-m-d H:i:s");
					$wpp_info['user_id']			= $_SESSION['user_id'];
					$wpp_info['company_id']			= $_SESSION['company_id'];
					$wpp_info['branch_id']			= $POST['branch_id'];
				
					$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
					
				}
				
			}	
			
					bom_child_tree($dbcon,$rel1['p_bom_id'],$inserid,$inserid_sub,$i,$qty,$version_id);
					
						
					$i++;
					}	
					
				}
					} 
									
			}
			
		}

function bom_child_tree($dbcon,$bom_id,$sp_id,$rp_parent_id,$num,$qty,$bom_version_id)
		{			
			$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
			left join product_mst as pro on pro.product_id=bom_trn.product_id
			left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
			left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
			where bom_trn_status=0 and bom_id=".$bom_id;	 
			$result1=$dbcon->query($query1);
			
			$k=1;
			$call=1;$space="";
			if(brp_mysqli_num_rows($result1)>0){
				
			
		while($rel1=mysqli_fetch_assoc($result1)){ 
			$sr_no = $num.'.'.$k; 
			
			$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
			$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

			$base_qty=$base_one_qty*$info_su['rp_req_qty'];
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

			$info_sub['sp_id']				= $sp_id;
			$info_sub['sr_no']				= $sr_no;
			$info_sub['rp_pid']				= $rel1['product_id'];
			$info_sub['rp_req_qty']			=  $qty;
			$info_sub['rp_req_date']		= date("Y-m-d");
			//$info_sub['rp_req_qty']			= $conv_stock;//required qty
			$info_sub['req_qty_one']		= 1;//required qty
			$info_sub['rp_po_qty']			= "";//po qty
			$info_sub['in_process_qty']		= $qty;//process qty
			$info_sub['rp_req_type']		= "work_order";//type
			$info_sub['process_unit']		= $rel1['product_base_unit'];
			$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
			$info_sub['perent_id']			= $rp_parent_id;
			$info_sub['status']				= 3;
			$info_sub['user_id']			= $_SESSION['user_id'];
			$info_sub['company_id']			= $_SESSION['company_id'];
			$info_sub['product_version']	= $rel1['p_bom_id'];
			$info_sub['bom_id']				= $rel1['p_bom_id'];
			
			$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$POST['branch_id']);
		
			$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status=0 and bom.bom_product='".$rel1['product_id']."'"; 
		
			$rel_pro1 = $dbcon->query($query_pro1);
			
			if(brp_mysqli_num_rows($rel_pro1)>0)
			{
				while($product_process1=brp_mysqli_fetch_assoc($rel_pro1))
				{
					$wpp_info['product_id'] = $rel1['product_id'];		
					$wpp_info['rp_id'] = 	$inserid_sub;
					$wpp_info['process_priority'] = 	$product_process1['process_priority'];
					$wpp_info['process_time'] = 	$product_process1['process_time'];
					$wpp_info['process_type'] = 	$product_process1['process_type'];
					$wpp_info['process_opening'] = 	$product_process1['pr_process_id'];
					$wpp_info['process_id'] = 	    $product_process1['pr_process_id'];	
					$wpp_info['cdate']				= date("Y-m-d H:i:s");
					$wpp_info['user_id']			= $_SESSION['user_id'];
					$wpp_info['company_id']			= $_SESSION['company_id'];
					$wpp_info['branch_id']			= $POST['branch_id'];
					
					$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
				}
			}
			bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub,$sr_no,$qty,$bom_version_id);
			$k++;	
		}
		}
}
?>

