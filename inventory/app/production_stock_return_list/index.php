<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
//{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
//	{
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

$company_config = getCompanyConfiguration($dbcon);	

if(strtolower($POST['mode']) == "fetch") {

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];

	$where='';
	
	$appData = array();
	$i=1;
	$aColumns = array('GROUP_CONCAT(rt.return_id) as return_id','p.product_name','gda.gd_name','rt.product_id','umst.unit_name','sum(rt.base_qty) as base_qty','rt.base_unit','rt.godown_id');
	$sIndexColumn = "rt.return_id";
	$isWhere = array('status = 0 and store_accept = 0 and rt.company_id = ' . $_SESSION['company_id']);
	$sTable = "tbl_godown_stock_return as rt";
	
	$isJOIN = array('left join product_mst as p on p.product_id=rt.product_id','left join unit_mst as umst on umst.unitid=rt.base_unit','left join mst_godown as gda on gda.gd_id=rt.godown_id');
	$hOrder = "rt.return_id desc";
	$hGroupby = array("rt.product_id","rt.godown_id");
	include($include.'pagging.php');
	$appData = array();
	$id=1;
			//echo "<pre>"; print_r($sqlReturn);
	foreach($sqlReturn as $row) {
		$row_data = array();

 		$row_data[] = $id;
		$row_data[] = $row['product_name'];
		$row_data[] = $row['gd_name'];
		$row_data[] = $row['base_qty'];
		$row_data[] = $row['unit_name'];

		$stock = get_current_godown_stock_new($dbcon, $row['product_id'], $row['base_unit'],$row['godown_id']);
		$row_data[] = $stock;
		$app_btn="";
		 $app_btn='<button class="btn btn-xs btn-success" data-original-title="Return Stock" data-toggle="tooltip" data-placement="top" onclick="change_stock_status('."'".$row['return_id']."'".','."'".$row['base_qty']."'".','."'".$row['product_id']."'".','."'".$row['godown_id']."'".')"><i class="fa fa-plus"></i> Return Stock</button>';
		 $close='<a onclick="delete_data('."'".$row['return_id']."'".')" class="btn btn-xs btn-danger" data-original-title="Reject" data-toggle="tooltip" data-placement="top"><i class="fa fa-close"></i></a>';


		$row_data[] = $app_btn . "  ".$close;

		$appData[] = $row_data;
		$id++;
			
			}
	
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "load_child_godown_list") {
	$parent_gd_id = $POST['godown_id'];

	$str = get_last_node_godown_list($dbcon,"",$parent_gd_id);

		echo $str;
}
else if(strtolower($POST['mode']) == "fieldadd") {


	$info1['return_id']				= $POST['return_id'];
	$info1['to_godown_id']					= $_POST['godown_id'];
	$info1['from_godown_id']					= $_POST['from_godown_id'];

	$info1['base_qty']						= $_POST['qty'];
	$info1['base_unit']					= $_POST['unit_id'];
	$info1['status']					= 3;

	$info1['product_id']				= $_POST['product_id'];
	$info1['user_id']					= $_SESSION['user_id'];
	$info1['company_id']				= $_SESSION['company_id'];

	$table='tbl_godown_stock_return_trn';$tableid='store_accept_trn_id';

	
		$inserid=add_record($table,$info1, $dbcon);
	
	
}
else if(strtolower($POST['mode']) == "load_tempoutward") {
	
	 $query="select mgs.gd_name,umst.unit_name,trn.return_trn_id, trn.base_qty,trn.return_trn_id from tbl_godown_stock_return_trn as trn left join mst_godown as mgs on mgs.gd_id=trn.to_godown_id left join unit_mst as umst on umst.unitid=trn.base_unit where trn.status=3 and trn.return_id in(".$POST['eid'].")";

			//echo $query;
	$result=$dbcon->query($query);
	echo '<div class="form-group">
	<div class="col-md-12 col-xs-11">
	<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
	<tr id="field">
	<th class="text-center" width="4%">Sr No</th>
	<th class="text-center" width="40%">Godown</th>
	<th class="text-center" width="40%">Quantity</th>
	<th class="text-center" width="8%">Unit</th>
	<th class="text-center" width="8%">Action</th>
	</tr>';

			//echo $query;
	if(mysqli_num_rows($result)>0)
	{
		$i=1;$total_used_qty=0;
		while($rel=brp_mysqli_fetch_assoc($result))
		{
			echo '<tr id="fieldtr'.$i.'">
			<td style="vertical-align:top;">'.$i.'</td>
			<td style="vertical-align:top;">'.$rel["gd_name"].'</td>
			<td style="vertical-align:top;">'.$rel["base_qty"].'</td>
			<td style="vertical-align:top;">'.$rel["unit_name"].'</td>
			<td style="vertical-align:top">
			
			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_temp_data('.$rel['return_trn_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
			</td>	
			</tr>';
			$total_used_qty=$total_used_qty+$rel["base_qty"];
			$i++;
		}
	}else{
		echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
	}
	echo '</table> 
	<input type="hidden" name="used_qty" id="used_qty" value="'.$total_used_qty.'" />
	</div>
	</div>';
}
else if(strtolower($POST['mode'])== "delete_temp_data")
{
	$row=array();
	$info['status']=2;	
	$updateid=update_record("tbl_godown_stock_return_trn", $info,"return_trn_id ='".$POST['id']."'", $dbcon);

	if($updateid){
		$row['res']="1";
	}
	else{
		$row['res']="0";
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "delete_data")
{
	$row=array();
	$info['status']=2;	
	$updateid=update_record("tbl_godown_stock_return", $info,"return_id IN(".$POST['eid'].")" , $dbcon);

	if($updateid){
		$row['res']="1";
	}
	else{
		$row['res']="0";
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "save_store_accept")
{
	$return_id = $POST['return_id'];

	 $qry = "select * from tbl_godown_stock_return_trn where status = 3 and return_id = '" . $return_id . "'";
	$res = $dbcon->query($qry);
// echo "</br></br>";
	while($row_rstock = brp_mysqli_fetch_assoc($res)){
		$reserve_qty=$row_rstock['base_qty'];
			$batch_where="";
			
			  $query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i	
			where ref_name='store_release' and stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(10,5))>cast(used_base_stock AS DECIMAL(10,5)) and i.product_id=".$row_rstock['product_id']." and i.godown_id=".$row_rstock['from_godown_id'];
			// echo "</br></br>";
			$result_dstock=$dbcon->query($query_dstock);
			while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
				$pending_stock=$row_dstock['pending_base_stock'];	
				
				if($reserve_qty>0){
					if($pending_stock>0){
						if($pending_stock>=$reserve_qty){
							$rqty=$reserve_qty;
							$reserve_qty=$reserve_qty-$reserve_qty;
						}else{
							$rqty=$pending_stock;
							$reserve_qty=$reserve_qty-$pending_stock;
						}

						$que="select * from product_mst as ta where product_id=".$row_rstock['product_id'];
						$rs_di=$dbcon->query($que);
						$re=brp_mysqli_fetch_assoc($rs_di);

						$type="conv_unit";
						$base_stock=$rqty;
						$con_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
						
						$stock_date=date("Y-m-d");

						$stock_id=add_stock($dbcon,$row_rstock['product_id'],$re['product_base_unit'],$stock_date,"stock_return",$row_rstock['return_trn_id'],$row_rstock['from_godown_id'],$base_stock,2,$row_dstock['branch_id'],$row_dstock['stock_id'],"",$row_dstock['customer_id'],"","",$row_dstock['base_rate'],$row_dstock['conv_rate']);


						$used_base_stock=$row_dstock['used_base_stock']+$base_stock;
						$used_convert_stock=$row_dstock['used_convert_stock']+$con_stock;
						
						$info_stock['used_base_stock']		= $used_base_stock;
						$info_stock['used_convert_stock']	= $used_convert_stock;
						
						$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);


						$stock_id=add_stock($dbcon,$row_rstock['product_id'],$re['product_base_unit'],$stock_date,"stock_return",$row_rstock['return_trn_id'],$row_rstock['to_godown_id'],$base_stock,1,$row_dstock['branch_id'],"","",$row_dstock['customer_id'],"","",$row_dstock['base_rate'],$row_dstock['conv_rate']);

						if($stock_id){
							$upd_info['status'] = 0;
								$updatetrnid=update_record('tbl_godown_stock_return_trn',$upd_info,"return_trn_id=".$row_rstock['return_trn_id'], $dbcon);

						}

					}
				}
			}
			$upd_info['store_accept'] = 1;
			$updatetrnid=update_record('tbl_godown_stock_return',$upd_info,"return_id in(".$return_id.")", $dbcon);
	}
}


else if(strtolower($POST['mode'])== "get_store_details")
{
	
	$qry = "SELECT gda.gd_name, rt.product_id, sum(rt.base_qty) as base_qty, umst.unit_name, p.product_name, rt.base_unit,rt.godown_id FROM tbl_godown_stock_return as rt  left join product_mst as p on p.product_id=rt.product_id  left join unit_mst as umst on umst.unitid=rt.base_unit left join mst_godown as gda on gda.gd_id=rt.godown_id where rt.return_id in(". $POST['return_id'].") group by product_id,godown_id";
	
	$q_res = $dbcon->query($qry);
	$res=brp_mysqli_fetch_assoc($q_res);
	
	echo json_encode($res);
}

?>