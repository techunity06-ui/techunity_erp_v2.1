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
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		//$branch=$_SESSION['branch_id'];
	
		//	$where.="  and purchaseorder_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND purchaseorder_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			
		//	$where.=" and po.branch_id=$branch";
			
			$appData = array();
			$i=1;
			$aColumns = array('po.purchaseorder_id','po.purchaseorder_no','po.purchaseorder_date','po.g_total','po.paid_amount','po.status','po.purchase_status','po.cdate','po.userid','po.po_type_status','po.po_req_status','pr.product_name','sum(po.po_qty) as pqty','po.po_ref_type','po.product_ref_id');
			$sIndexColumn = "po.purchaseorder_id";
			$isWhere = array("po.status = 0");
			$sTable = "tbl_purchaseorder as po";			
			$isJOIN = array('left join product_mst as pr on pr.product_id=po.product_ref_id');
			$hOrder = "po.purchaseorder_id desc";
			$hGroupby = array("po.product_ref_id");
			include('../../include/pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $id;
				$row_data[] = date('d M, Y',strtotime($row['purchaseorder_date']));
				$row_data[] = $row['product_name'];
				$row_data[] = $row['pqty'];
				$row_data[] = $row['po_type_status'];
				
				$poprint='<a class="btn btn-xs btn-info" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poprint/'.$row['purchaseorder_id'].'"><i class="fa fa-print"></i></a>';
				
				$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create PO" data-toggle="tooltip" data-placement="top" href="'.ROOT.'po_req_add/'.$row['product_ref_id'].'/'.$row['po_ref_type'].'"><i class="fa fa-plus"></i></a>';
					
				$row_data[] = $add_po_btn.' '.$poprint;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			
				$info['po_req_mode']		= 1;
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				//$updateid=update_record('tbl_purchaseorder', $info, "purchaseorder_id=".$POST['eid'] , $dbcon);
		/* Entry In trn Table Start */
			//$deleteid=delete_record('tbl_purchaseordertrn', "purchaseorder_id=".$POST['eid'], $dbcon);	
			foreach ($POST['product_id'] as $key => $name) {
				//var_dump($name);
				$info1['po_trn_req_status']	= $POST['po_trn_req_status'][$key];
				$info1['product_type']		= $POST['product_type'][$key];
				$info1['product_id']		= $POST['product_id'][$key];
				$info1['main_pro_status']	= $POST['main_pro_status'][$key];
				$info1['product_qty']		= $POST['product_qty'][$key];
				$info1['purchaseorder_id']	= $POST['eid'];
				$info1['user_id']			= $_SESSION['user_id'];
				//var_dump($info1);
				//$inserid=add_record('tbl_purchaseordertrn', $info1, $dbcon);
			}
		/* Entry In trn Table End */
		
				if($inserestimateid){	
					$arr['msg']="1";
				}
				else{
					$arr['msg']="0";
				}
					
			echo json_encode($arr);
			
		}
		else if(strtolower($POST['mode']) == "req_po_to_main_po") {
			
			$eid=$POST['eid'];
			
		//	$dbcon->query("delete from tbl_purchaseordertrn where purchaseorder_id='$eid' and po_trn_req_status='0'");
			
			$sp_array=$POST['purchaseordertrn_id'];
			
			for($k=0;$k<count($sp_array);$k++)
			{
				
				$pr_rate=get_pro_field($dbcon,$row_temp['product_id'],'product_purchase_rate');
				
				$loop_id=$k;
				
				
				$potemp_id=$POST['potemp_id'][$loop_id];
				
			/*	$sel_ptemp=$dbcon->query("select * from tbl_purchasetrntemp where purchaseordertrn_id='$potemp_id'");
				$row_temp=mysqli_fetch_array($sel_ptemp);
				
				$infos['product_type']=$row_temp['product_type'];
				$infos['product_id']=$row_temp['product_id'];
				$infos['po_ref_id']=$row_temp['po_ref_id'];
				$infos['po_ref_type']=$row_temp['po_ref_type'];
				$infos['po_bom_id']=$row_temp['po_bom_id'];
				$infos['po_bom_trn_id']=$row_temp['po_bom_trn_id'];
				$infos['parent_pro']=$row_temp['parent_pro'];
				$infos['purchaseorder_id']=$row_temp['purchaseorder_id']; */
				
				$infos['product_base_unit']=$POST['product_base_unit'][$loop_id];
				$infos['product_uom']=$POST['product_uom'][$loop_id];
				$infos['product_alloc_qty']=$POST['product_alloc_qty'][$loop_id];
				$infos['product_rate']=$pr_rate;
				
				$infos['product_amount']=$pr_rate*$POST['product_alloc_qty'][$loop_id];
				
				$infos['cdate']				= date("Y-m-d H:i:s");
				$infos['user_id']			= $_SESSION['user_id'];
				$infos['company_id']		= $_SESSION['company_id'];
				
				//add_record('tbl_purchaseordertrn', $infos, $dbcon);
				
				update_record('tbl_purchasetrntemp', $infos,"purchaseordertrn_id=".$potemp_id, $dbcon);
				
				//echo $row['loop'.$k]=$row_temp['po_ref_type'];
				
			}
			
			$row['msg']="1";
				//$row['']=;
				
			echo json_encode($row);
			
		}	
		else if(strtolower($POST['mode'])== "cancel_po_status")
		{
			$row=array();
			$info['po_type_status'] = $POST['po_status'];	
			
			$updateid=update_record("tbl_purchaseorder", $info,"purchaseorder_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "close_po_status")
		{
			$row=array();
			$info['po_req_status'] = $POST['po_req_status'];	
			
			$updateid=update_record("tbl_purchaseorder", $info,"purchaseorder_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_alt_qty")
		{
			
			$unit = $POST['unit'];	
			$product = $POST['product'];	
			
			$sel=$dbcon->query("select * from tbl_product_unit where unit_alt_unit='$unit' and unit_product='$product'");
			$count=mysqli_num_rows($sel);
			$row=mysqli_fetch_assoc($sel);
			
			$data['alt_qty']=$row['unit_alt_qty'];
			$data['base_qty']=$row['unit_basic_qty'];
			$data['count']=$count;
			
			echo json_encode($data);
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
		$info['tax_name'.$i]='';
		$info['tax_amount'.$i]='';		
	}
	$info['total']=$rate_total;
	return $info;
}
function change_po_trn_status($dbcon, $purchaseordertrn_id, $purchaseorder_id){
	$upd_po_trn['po_trn_req_status'] = 1;
	//$upd_po_trn['cdate'] = date("Y-m-d H:i:s");
	$updatepotrnid=update_record('tbl_purchaseordertrn', $upd_po_trn, "purchaseordertrn_id in(".$purchaseordertrn_id.") and purchaseorder_id=".$purchaseorder_id, $dbcon);
	
	//Update Main status if all used
	$sel_po_ord_qry="select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and po_trn_req_status=0 and purchaseorder_id=".$purchaseorder_id;
	$po_num_row=mysqli_num_rows($dbcon->query($sel_po_ord_qry));
	if(!$po_num_row){
		$upd_po['po_req_status']	= 1;
		$upd_po['cdate']			= date("Y-m-d H:i:s");
		$updateslsid = update_record('tbl_purchaseorder', $upd_po, "purchaseorder_id=".$purchaseorder_id, $dbcon);
	}
	else{
		$upd_po['po_req_status'] 	= 0;
		$upd_po['cdate'] 			= date("Y-m-d H:i:s");
		$updateslsid = update_record('tbl_purchaseorder', $upd_po, "purchaseorder_id=".$purchaseorder_id, $dbcon);
	}
}

?>