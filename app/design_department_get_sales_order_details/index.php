<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
include("../../include/function_database_query.php");
//check permission for get sales order details
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    DESIGN_DEPARTMENT_GET_SALES_ORDER_SLUG_VIEW,DESIGN_DEPARTMENT_GET_SALES_ORDER_SLUG_CREATE,PRODUCTION_BOM_LIST_SLUG_UPDATE,PRODUCTION_BOM_LIST_SLUG_VIEW,PRODUCTION_BOM_LIST_SLUG_DELETE,PRODUCTION_BOM_LIST_SLUG_UPDATE
]);

$company_config = getCompanyConfiguration($dbcon);		
$production_pro_search = $company_config['production_pro_search'];
$pro_search=explode(",", $production_pro_search);


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
			
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('so', $branch_id);
			
		$appData = array();
		$i=1;
		
		$aColumns = array('pro.bom_required','pro.product_icode', 'dr.drawing_number','so.sales_order_no','so_trn.sales_ordertrn_id','so.sales_order_date','pro.product_name','so_trn.product_id','bov.bom_no','bom.bom_id','so_trn.product_qty','so.branch_id');

		$sIndexColumn = "so_trn.sales_ordertrn_id";
		$isWhere = array("so_trn.bom_status=0 AND so.order_accept_status = '1'".$where_db);

		$sTable = "tbl_sales_ordertrn as so_trn";

		$isJOIN = array("left join product_mst as pro on pro.product_id=so_trn.product_id 
						left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
						left join tbl_sales_order as so on so.sales_order_id = so_trn.sales_order_id 
						left join pro_ms_bom_version as bov on bov.product_id = so_trn.product_id and is_default_bom=1",
						"left join tbl_bom as bom on bom.bom_version_id=bov.bom_version_id");
		
		$hOrder = "so.sales_order_no desc";
		$hGroupby = array("so_trn.sales_ordertrn_id");

		$having="";
	
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
	
		foreach($sqlReturn as $row) {
			
			$so_query="SELECT sales_order_id from tbl_sales_order";
	$so_row=mysqli_fetch_assoc($dbcon->query($so_query));
	$so_id = $so_row['sales_order_id'];
			$row_data = array();			
			$row_data[] = '<input type="checkbox" chk name="chk[]" data-soid="'.$row['sales_ordertrn_id'].'" data-bomid="'.$row['bom_id'].'" data-branchid="'.$row['branch_id'].'"  value="'.$row['product_id'].'"/>';
			$row_data[] = $row['sales_order_no'];
			$row_data[] = $row['sales_order_date'];			
			$row_data[] = $row['bom_no'];		

			$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$row['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$row['product_icode'].")";
				        }	


			$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
			$row_data[] = $row['product_qty'];
			$view='';
			
			if($bom_so_customization == "1")
			{
				$add_button='<a href="'.ROOT.PRODUCTION_ROOT.'bom_assign/'.$row['bom_id'].'/'.$so_id.'/'.$row['sales_ordertrn_id'].'/'.$row['bom_id'].'"><button type="button" class="btn btn-xs btn-success" data-original-title="Bom" data-toggle="tooltip" data-placement="top" ><i class="fa fa-plus"></i></button></a>';

			}
		
			
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
			
		}
		
		if(strtolower($POST['mode']) == "product_bom_data_fetch") {
		
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
		include('../../include/pagging.php');
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
	}
		
		
		
	}
    
}
/*
else {
    die("Error - 1");
}*/

?>