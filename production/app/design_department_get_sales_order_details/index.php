<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
//check permission for get sales order details
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	DESIGN_DEPARTMENT_GET_SALES_ORDER_SLUG_VIEW,DESIGN_DEPARTMENT_GET_SALES_ORDER_SLUG_CREATE,PRODUCTION_BOM_LIST_SLUG_UPDATE,PRODUCTION_BOM_LIST_SLUG_VIEW,PRODUCTION_BOM_LIST_SLUG_DELETE,PRODUCTION_BOM_LIST_SLUG_UPDATE
]);

$company_config = getCompanyConfiguration($dbcon);		
$production_pro_search = $company_config['production_pro_search'];
$pro_search=explode(",", $production_pro_search);

$companyConfiguration=getCompanyConfiguration($dbcon);
{ 
	/*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
		//print_r($_POST);
		
		$bom_version_query="SELECT design_so_customization from tbl_company_configuration";
		$bom_version_row=mysqli_fetch_assoc($dbcon->query($bom_version_query));
		$bom_so_customization = $bom_version_row['design_so_customization'];
		
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		

		if(strtolower($POST['mode']) == "generate_report_min_new") {
			
			/*$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('so', $branch_id);*/
			

			
			if($companyConfiguration['sales_wise_branch_planning_before_bom']==0){

				$bomsetting="";
					if($_SESSION['user_type'] != '2'){
						$where_db=" and so_trn.branch_id=".$_SESSION['branch_id'];
					}else{
						if($POST['branch_id']=="1000" || $POST['branch_id']=="" || $POST['branch_id']=="0"){
							$where_db="";
						}else{
							$where_db=" and so_trn.branch_id=".$POST['branch_id'];
						}
					}
				}else{

					
					$bomsetting=" and so_trn.production_branch_id!=0";
					if($_SESSION['user_type'] != '2'){
						$where_db=" and so_trn.production_branch_id=".$_SESSION['branch_id'];
					}else{
						if($POST['branch_id']=="1000" || $POST['branch_id']=="" || $POST['branch_id']=="0"){
							$where_db="";
						}else{
							$where_db=" and so_trn.production_branch_id=".$POST['branch_id'];
						}
					}
				}	
			
			
			$appData = array();
			$i=1;
			$aColumns = array('pro.product_icode', 'dr.drawing_number','so.sales_order_no','so_trn.sales_ordertrn_id','so.sales_order_date','pro.product_name','so_trn.priority_status','so_trn.product_id','bov.bom_no','bom.bom_id','so_trn.product_qty','so.branch_id','pro.bom_required','so.jobwork_type','bov.version_name','so_trn.description','so.sales_order_id','(IFNULL(product_qty,0)-IFNULL(stock_add,0)) as pending_qty');
			$sIndexColumn = "so_trn.sales_ordertrn_id";
			$isWhere = array("1 and so_trn.bom_status=0 AND so_trn.sales_ordertrn_status = 0 and so_trn.short_close_status=0 and so_trn.invoice_status=0 and so.order_accept_status = '1' AND so.company_id = ".$_SESSION['company_id']." ".$where_db ."".$bomsetting);
			$sTable = "tbl_sales_ordertrn as so_trn";
			$isJOIN = array("left join product_mst as pro on pro.product_id=so_trn.product_id 
				left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
				left join tbl_sales_order as so on so.sales_order_id = so_trn.sales_order_id 
				left join pro_ms_bom_version as bov on bov.product_id = so_trn.product_id and bov.is_default_bom=1 and bov.bom_version_status = 0",
				"left join tbl_bom as bom on bom.bom_version_id=bov.bom_version_id and bom.bom_status = 0","left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.sales_ordertrn_id from tbl_sales_order_production_trn as qc 
			where qc.sales_order_production_status=0 group by qc.sales_ordertrn_id) as qc on qc.sales_ordertrn_id=so_trn.sales_ordertrn_id");
			$hOrder = "so.sales_order_no desc";
			$hGroupby = array("so_trn.sales_ordertrn_id");
			$having="";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				//$so_query="SELECT sales_order_id from tbl_sales_order";
				//$so_row=mysqli_fetch_assoc($dbcon->query($so_query));
				//$so_id = $so_row['sales_order_id'];
				
				$row_data = array();	
				$so_id = $row['sales_order_id'];		
				$row_data[] = '<input type="checkbox" chk name="chk[]" data-soid="'.$row['sales_ordertrn_id'].'" data-bomid="'.$row['bom_id'].'" data-branchid="'.$row['branch_id'].'"  value="'.$row['product_id'].'"/>';
				$row_data[] = $row['sales_order_no'];
				$row_data[] = $row['sales_order_date'];
				$row_data[] = $row['version_name'];
				$drawing_number = "";
				$item_code = "";
				if(in_array('drawing',$pro_search)){
					$drawing_number = " -- (".$row['drawing_number'].")";
				}
				if(in_array('item',$pro_search)){
					$item_code = " -- (".$row['product_icode'].")";
				}	
				$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
				$row_data[] = $row['pending_qty'];
				if($companyConfiguration['outside_jobwork']){
					if($row['jobwork_type'] == '0'){
						$row_data[] ='<button class="btn btn-xs btn-success" data-original-title="Normal Jobwork" data-toggle="tooltip" data-placement="top">Normal</button>';
					}else{
						$row_data[] ='<button class="btn btn-xs btn-danger" data-original-title="Outside Jobwork" data-toggle="tooltip" data-placement="top">Outside Jobwork</button>';
					}
				}
				if($bom_so_customization == "1")
				{
					$add_button='<a href="'.ROOT.PRODUCTION_ROOT.'bom_assign/'.$row['product_id'].'/'.$so_id.'/'.$row['sales_ordertrn_id'].'/'.$row['bom_id'].'"><button type="button" class="btn btn-xs btn-success" data-original-title="Bom" data-toggle="tooltip" data-placement="top" ><i class="fa fa-plus"></i></button></a>';
				}
				$view='';
				//if(!empty($row['description'])){
					$view='<button class="btn btn-xs btn-primary" data-original-title="Sales Order Detail" data-toggle="tooltip" data-placement="top" type="button" onclick="open_so_trn_modal('.$row['sales_ordertrn_id'].')"><i class="fa fa-eye"></i></button>';
				//}
				$row_data[] = $row['priority_status'];
				$row_data[] = $view.' '.$apprv_btn.' '.$add_button;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode($output );

		}else if(strtolower($POST['mode']) == "load_entry_stock"){
			$q="select * from tbl_sales_ordertrn as gd where sales_ordertrn_status=0 and sales_ordertrn_id=".$POST['ref_sales_order_trn_id'];
			$rel=$dbcon->query($q);
			//$str=array();

			$row=mysqli_fetch_array($rel);
			$godown=get_godown_stock_so($dbcon,$row['product_id'],$row['unit_id']);
			$work_order=get_min_max_work_order_stock($dbcon,$row['product_id']);

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

			echo $html;
		}else if(strtolower($POST['mode']) == "add"){
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

		else if(strtolower($POST['mode']) == "assign_standard_bom"){

			$so_array =  $_POST['so_trn_id'];
			$product_array = $_POST['product_id'];		
			$bom_array = $_POST['bom_id'];		
			$branch_array = $_POST['branch_id'];		
			$i=0;
			foreach($product_array as $prd)
			{
				/* $q="select * from pro_ms_bom_version as bv 
				inner join tbl_bom as b on b.bom_version_id = bv.bom_version_id 
				where b.bom_product='$prd' AND bv.is_default_bom ='1'";
				$rel=$dbcon->query($q);
				$row=mysqli_fetch_array($rel); */
				
				if(!empty($bom_array[$i]))
				{
					$info_e['so_trn_id']		=$so_array[$i];
					$info_e['bom_id']			=$bom_array[$i];
					$info_e['product_id']		=$prd;										
					$info_e['cdate']			=date("Y-m-d");
					$info_e['company_id']		=$_SESSION['company_id'];
					$info_e['user_id']			=$_SESSION['user_id'];
					
					$insertbomassign=add_record('pro_so_asssign_bom_version', $info_e, $dbcon,$branch_array[$i]);
					
					if($insertbomassign){
						$info_update['bom_status']	= 1;
						$info_update['bom_id']		= $info_e['bom_id'];
						
						$updateid=update_record('tbl_sales_ordertrn', $info_update,"sales_ordertrn_id=".$info_e['so_trn_id'] , $dbcon);
					}
				}
				$i++;
			}
			
			if($insertbomassign ){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
			
		}
		
		else if(strtolower($POST['mode']) == "assign_bom"){
			
			$pr_id = $_POST['pr_id'];
			$so_id =  $_POST['so_id'];
			$so_trans_id =  $_POST['so_trans_id'];
			$bom_id = $_POST['bom_id'];			
			$bom_version_id = $_POST['bom_version_id'];
			
			
			$q="select * from pro_so_asssign_bom_version where so_id = '$so_id' AND so_trn_id = '$so_trans_id' AND  bom_id = '$bom_version_id' AND  bom_version_id = '$bom_version_id' AND product_id = '$pr_id'";
			$rel=$dbcon->query($q);
			$row=mysqli_fetch_array($rel);

			if(mysqli_num_rows($rel)<1)
			{
				$info_e['so_id']			=  $so_id;
				$info_e['so_trn_id']		=$so_trans_id;
				$info_e['bom_version_id']	=$bom_version_id;
				$info_e['bom_id']			=$bom_id;					
				$info_e['product_id']		=$pr_id;										
				$info_e['cdate']			=date("Y-m-d");
				$info_e['company_id']		=$_SESSION['company_id'];
				$info_e['user_id']			=$_SESSION['user_id'];

				$insertbomassign=add_record('pro_so_asssign_bom_version', $info_e, $dbcon,$row['branch_id']);

				$info_b['bom_status']			=  1;
				$info_b['bom_id']			=$bom_id;
				$updatebomassign=update_record('tbl_sales_ordertrn', $info_b, "sales_ordertrn_id=".$so_trans_id , $dbcon);
				$arr['msg']="1";
			}
			else{
				$arr['msg']="-1";
			}
			echo json_encode($arr);			
		} else if(strtolower($POST['mode']) == "product_bom_data_fetch") {

			$product_id = $POST['product_id'];
			$so_id = $POST['so_id'];

			$where='';
			$where.=" and bom.bom_product=".$product_id;
			
			$appData = array();
			$i=1;
			$aColumns = array('bom_id','bom_no','bom_date','bom_close_status','product.product_name','bom_status','bom.cdate','bom.user_id','bom.bom_product','bom.bom_qty','product.image_name');
			$sIndexColumn = "bom_id";
			$isWhere = array("bom_status = 0".$where);
			$sTable = "tbl_bom as bom";			
			$isJOIN = array('left join product_mst as product on product.product_id=bom.bom_product');
			$hOrder = "bom.bom_id desc";
			include($include.'pagging.php');
			$appData = array();

			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();

				$row_data[] = '<input type="checkbox" chk name="chk[]"   value="'.$product_id.'"/>';
				if($row['image_name']!=null){
					$image_name = '<a href="'.ROOT.'view/upload/product_images/'.$row["image_name"].'" target="_blank"><img src="'.ROOT.'view/upload/product_images/'.$row['image_name'].'" style="width: 60px;height: 50px;"></a>';
				}else{
					$image_name = '';
				}

				$row_data[] = '<a class="" data-original-title="Edit '.$row["bom_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bom_edit/'.$row['bom_id'].'">'.$row["sr"].'</a>';
				$row_data[] = '<a class="" data-original-title="Edit '.$row["bom_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bom_edit/'.$row['bom_id'].'">'.$row["bom_no"].'</a>';
				$row_data[] = $image_name;
				$row_data[] = '<a class="" data-original-title="Edit '.$row["bom_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bom_edit/'.$row['bom_id'].'">'.date('d M, Y',strtotime($row["bom_date"])).'</a>';
				$row_data[] = '<a class="" data-original-title="Edit '.$row["bom_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bom_edit/'.$row['bom_id'].'">'.$row["product_name"].'</a>';
			//$row_data[] = $row['bom_qty'];

				if($row['bom_close_status']=='0')
				{ 
					$status_show="<strong style='color:green'>Open</strong>"; 
				} 
				else 
				{ 
					$status_show="<strong style='color:red'>Closed</strong>";  
				}

				$row_data[] = $status_show;

				$sales_order_print='';$invoicestatus='';$delete='';$edit='';
				
				if(in_array(PRODUCTION_BOM_LIST_SLUG_UPDATE,$bulkAccessArray)){
					if($row['bom_close_status']=='0')
					{
						$close_status='<a class="btn btn-xs btn-success" data-original-title="change BOM Status" data-toggle="tooltip" data-placement="top" onClick="change_bom_status('.$row['bom_id'].','.$row['bom_close_status'].')"><i class="fa fa-check-circle"></i></a>';
					}
					else
					{
						$close_status='<a class="btn btn-xs btn-danger" data-original-title="BOM Status Close" data-toggle="tooltip" data-placement="top" onClick="change_bom_status('.$row['bom_id'].','.$row['bom_close_status'].')"><i class="fa fa-window-close"></i></a>';
					}
				}
				
				if(in_array(PRODUCTION_BOM_LIST_SLUG_VIEW,$bulkAccessArray)){
					$sales_order_print='<a class="btn btn-xs btn-primary" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bom_print/'.$row['bom_id'].'"><i class="fa fa-print"></i></a>';
				}
				
				
				if(in_array(PRODUCTION_BOM_LIST_SLUG_DELETE,$bulkAccessArray)){
					$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bom('.$row['bom_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
				
				if(in_array(PRODUCTION_BOM_LIST_SLUG_UPDATE,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bom_edit/'.$row['bom_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				/* $clone_btn='<button type="button" class="btn btn-xs btn-success" data-original-title="Clone BOM" data-toggle="tooltip" data-placement="top" onclick="open_copy_bom_model('.$row['bom_id'].');"><i class="fa fa-undo"></i></button>';
				 */
				$clone_btn='<button type="button" class="btn btn-xs btn-success" data-original-title="Assign BOM" data-toggle="tooltip" data-placement="top" onclick="assign_custom_bom('.$product_id.','.$so_id.','.$row['bom_id'].');"><i class="fa fa-undo"></i></button>';
				
				
				$row_data[] = $sales_order_print.' '.$edit.' '.$delete.' '.$req_po_btn.' '.$clone_btn.' '.$close_status;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		} else if(strtolower($POST['mode']) == "preview_so_trn_pro_description") {
			$str = '';
			$qry = $dbcon->query("SELECT so_trn.*, pro.product_name, so.sales_order_date, so.sales_order_no FROM tbl_sales_ordertrn as so_trn LEFT JOIN product_mst as pro on pro.product_id=so_trn.product_id LEFT JOIN tbl_sales_order as so on so.sales_order_id = so_trn.sales_order_id  WHERE so_trn.sales_ordertrn_status = 0 and so_trn.sales_ordertrn_id = ".$POST['so_trn_id']);
			$res= brp_mysqli_fetch_assoc($qry);
			$str.= '<table class="display table table-bordered table-striped">
			<tbody>
			<tr>
			<td><strong>Sales Order No :</strong> '.$res['sales_order_no'].'</td>
			<td><strong>Sales Order Date :</strong> '.date("d-M-Y", strtotime($res['sales_order_date'])).'</td>
			</tr>
			<tr>
			<td><strong>Product Name :</strong> '.$res['product_name'].'</td>
			<td><strong>Request Qty :</strong> '.$res['product_qty'].'</td>
			</tr>
			<tr>
			<td colspan="2"><strong>Product Description:</strong><br>'.$res['description'].'</td>
			</tr>
			</tbody>
			</table><br><br>';
			
			$query_img 	= "select mst.* from tbl_so_attch as mst 
		where mst.attach_status=0 and mst.design_dept=1 and mst.sales_order_id=".$res['sales_order_id'];
			$result_img = $dbcon->query($query_img);

			$str .='<h1 style="text-align:center">View Document</h1>
			<table class="display table table-bordered table-striped">
			<thead>
				<tr>
					<th>Sr.</th>
					<th>Document Name</th>
					<th>Attachment Document</th>
				</tr>
			</thead>
			<tbody>';
			$i=1;
			$cnt = brp_mysqli_num_rows($result_img);
			if($cnt>0){
				while($row = brp_mysqli_fetch_array($result_img)){
					$file_path=$dbcon->real_escape_string(DOMAIN.SO_ATTACH_VIEWING.$row['attach_file']);
					$str.='<tr>
						<td>'.$i.'</td>
						<td>'.$row['attach_doc_name'].'</td>
						<td>
							<a href="'.ROOT.SO_ATTACH_VIEWING.$row['attach_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>
						</td>
					</tr>';
					$i++;
				}
			}else{
				$str.='<tr>
					<td colspan="3" style="text-align:center">No Data Yet...!!!</td>
				</tr>';
			}
			$str .='</tbody></table>';



			echo $str;
		}
	}
}
/*
else {
    die("Error - 1");
}*/

?>