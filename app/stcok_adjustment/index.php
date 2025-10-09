<?php

session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    INVENTORY_STOCK_ADJUSTMENT_SLUG_VIEW,INVENTORY_STOCK_ADJUSTMENT_SLUG_UPDATE,INVENTORY_STOCK_ADJUSTMENT_SLUG_DELETE
]);							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
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
		
		//echo $_SESSION['page'];
			/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/
			//$appr_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);

		
			$where.="  and stcok_adjustment_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND stcok_adjustment_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('stcok_adjustment_id','stcok_adjustment_no','stcok_adjustment_date','po.cdate','po.userid');
			$sIndexColumn = "stcok_adjustment_id";
			$isWhere = array("stcok_adjustment_status = 0".$where);
			$sTable = "tbl_stcok_adjustment as po";
			$isJOIN = array();
			$hOrder = "po.stcok_adjustment_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				if(in_array(INVENTORY_STOCK_ADJUSTMENT_SLUG_UPDATE,$bulkAccessArray)){
					$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'stcok_adjustment_edit/'.$row['stcok_adjustment_id'].'">'.$row["stcok_adjustment_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'stcok_adjustment_edit/'.$row['stcok_adjustment_id'].'">'.date('d M, Y',strtotime($row["stcok_adjustment_date"])).'</a>';
				}else{
					$row_data[] = $row['stcok_adjustment_no'];
					$row_data[] = date('d M, Y',strtotime($row['stcok_adjustment_date']));
				}
				
				if(in_array(INVENTORY_STOCK_ADJUSTMENT_SLUG_DELETE,$bulkAccessArray)){
					$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_po('.$row['stcok_adjustment_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
				if(in_array(INVENTORY_STOCK_ADJUSTMENT_SLUG_UPDATE,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'stcok_adjustment_edit/'.$row['stcok_adjustment_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				$row_data[] = $poprint.' '.$edit.' '.$delete.' '.$po_app_btn;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
		
		$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
		
			
			$info['stcok_adjustment_no']	= $POST['stcok_adjustment_no'];
			$info['stcok_adjustment_date']	= date('Y-m-d',strtotime($POST['stcok_adjustment_date']));
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['userid']			= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$inserpoid=add_record('tbl_stcok_adjustment', $info, $dbcon);
			
			if($inserpoid){
				$inftrn['stcok_adjustment_id'] = $inserpoid;
				$inftrn['stcok_adjustment_trn_status'] = 0;
				$updatetrnid=update_record('tbl_stcok_adjustment_trn', $inftrn,"user_id=".$_SESSION['user_id']." and stcok_adjustment_trn_status=3" , $dbcon);
			}
			
					if(isset($POST['save_print']))
					{
						$arr['printstatus']=$POST['print_status'];
						$arr['msg']="1";
						$arr['eid']=$inserpoeid;
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
			 
				$info['stcok_adjustment_no']	= $POST['stcok_adjustment_no'];
				$info['stcok_adjustment_date']	= date('Y-m-d',strtotime($POST['stcok_adjustment_date']));
				$info['userid']					= $_SESSION['user_id'];
				$info['company_id']				= $_SESSION['company_id'];
				$info['cdate']					= 	date("Y-m-d H:i:s");
				$info['userid']					= $_SESSION['user_id'];
				$updateid1=update_record('tbl_stcok_adjustment', $info,"stcok_adjustment_id=".$POST['eid'] , $dbcon);
							
				if(isset($POST['save_print']))
				{
					//var_dump($updateid1);
					$arr['printstatus']=$POST['print_status'];
					$arr['msg']="update";
					$arr['eid']=$POST['eid'];
				}
				else
				{ 
					
					if($updateid1)
					{	
						$arr['msg']="update";
						
					}
					else{
						$arr['msg']=0;
					}
				}
			echo json_encode($arr);	
			 
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['stcok_adjustment_status']		= 2;
			$info1['stcok_adjustment_trn_status']		= 2;
			
			$updateinvoiceid=update_record('tbl_stcok_adjustment', $info,"stcok_adjustment_id=".$POST['eid'] , $dbcon);	
			$updatetrancationid=update_record('tbl_stcok_adjustment_trn', $info1,"stcok_adjustment_id=".$POST['eid'] , $dbcon);	
				
			
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode'])== "load_productdata")
		{
			//$qry="select popro.*, from tbl_purchaseproduct as porpo left join tbl_company as com on com.company_id=".$_SESSION['company_id']." where product_id=".$POST['eid'];
			$qry="select popro.*,com.stateid as com_stateid,ven.stateid as ven_stateid from `product_mst` as popro 
			left join `tbl_company` as com on com.company_id=".$_SESSION['company_id']." 
			left join tbl_ledger as ven on ven.l_id=".$POST['vender_id']." where product_id=".$POST['eid'];
			$result=$dbcon->query($qry);

			$row=mysqli_fetch_assoc($result);

			echo json_encode( $row );
		}
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=".$POST['type_id']." and company_id=".$_SESSION['company_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
		
				
				$info1['product_type']	= $POST['product_type'];
				$info1['product_id']	= $POST['product_id'];
				$info1['description']	= $_POST['product_des'];
				$info1['current_stock']	= $POST['current_stock'];
				$info1['stock_qty']		= $POST['stock_qty'];
				$info1['add_adjustment_qty']= $POST['add_adjustment_qty'];
				$info1['remove_adjustment_qty']= $POST['remove_adjustment_qty'];
				$info1['user_id']		= $_SESSION['user_id'];
				$info1['company_id']	= $_SESSION['company_id'];
				
				$table='tbl_stcok_adjustment_trn';$tableid='stcok_adjustment_trn_id';
				if(!empty($POST['eid']))
				{
					$info1['stcok_adjustment_id']= $POST['eid'];
						
				}else{
					$info1['stcok_adjustment_trn_status']= 3;
				}
				
				$inserid=add_record($table, $info1, $dbcon);
					
				
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			if($POST['eid']){ 
				$query="select trn.*,product.product_name,product.product_type from tbl_stcok_adjustment_trn as trn
					left join product_mst as product on product.product_id=trn.product_id  
					where trn.stcok_adjustment_trn_status=0 and trn.stcok_adjustment_id=".$POST['eid'];
			}
			else{
				$query="select trn.*,product.product_name,product.product_type from tbl_stcok_adjustment_trn as trn
					left join product_mst as product on product.product_id=trn.product_id  
					where trn.stcok_adjustment_trn_status=3 and trn.user_id=".$_SESSION['user_id'];
			}
			
			$result=$dbcon->query($query);
			echo '<div class="form-group">
					<div class="col-md-12 col-xs-11">
					<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center" width="10%">Product Type</th>
							<th class="text-center" width="25%">Product Name</th>
							<th class="text-center"width="8%">Current Stock</th>
							<th class="text-center"width="8%">Adjustment Stock</th>
							<th class="text-center"width="10%">Action</th>
						</tr>';
						
						//echo $query;
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
					
			 echo '<tr id="fieldtr'.$i.'">
					<td style="vertical-align:top;">
						'.get_pro_type_name($rel['product_type']).'
					</td>
					<td style="vertical-align:top;">
						'.$rel['product_name'].'
						'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['current_stock'].'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['stock_qty'].'
					</td>					
					<td style="vertical-align:top">
							<!--<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['stcok_adjustment_trn_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>-->
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['stcok_adjustment_trn_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
					</td>	
				</tr>';
				$i++;
			}
		}
		
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}
			echo '</table> </div>
                           </div>';
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$q = $dbcon -> query("SELECT mst.*,pro.product_name,pro.product_type FROM ".$_POST['table']." as mst left join product_mst as pro on mst.product_id=pro.product_id WHERE ".$_POST['whereid']." = '$POST[id]'");
			$r = $q->fetch_assoc();
			
			$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');
			
			echo json_encode($r);
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
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
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
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			
				$info['stcok_adjustment_trn_status']=2;	
				
			$updateid=update_record("tbl_stcok_adjustment_trn", $info,"stcok_adjustment_trn_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];
			echo getrequiredproduct($dbcon,'',' and product_type='.$type_id.'');
		}
		else if(strtolower($POST['mode'])== "current_stock")
		{
			$stock=get_current_stock($dbcon,$POST['product_id']);

			echo $stock;
		}
    }
}


?>