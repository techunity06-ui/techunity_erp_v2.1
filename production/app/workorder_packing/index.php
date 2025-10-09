<?php

	session_start();
	$AJAX = true;
	include('../../include/urlfileinner.php');
	include "../../../inventory/view/qrcode/qrlib.php";  

	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	}
	else {
		$POST = bulk_filter($dbcon,$_GET);
	}

	$companyConfiguration = getCompanyConfiguration($dbcon);

	if(brp_strtolower($POST['mode']) == "get_packing_size") {
		$packing_id = $POST['packing_id'];

		$query = "SELECT size FROM packing_mst WHERE status = 0 AND packing_id = " . $packing_id;
		$result = $dbcon->query($query);
		$cnt = brp_mysqli_num_rows($result);
		$row = brp_mysqli_fetch_assoc($result);
		if($cnt > 0){
				echo $row['size'];
		}else{
			echo "0";
		}
	}else if(brp_strtolower($POST['mode']) == "get_batch_no") {
		$batch_no = get_batch_no($dbcon,$POST['product_id']);

		echo $batch_no;
	}else if(brp_strtolower($POST['mode']) == "load_tempoutward") {

		$workorder_id = $POST['workorder_id'];
		$query = "SELECT  trn.workorder_packing_trn_id, trn.batch_no, pack.packing_name, trn.packing_size, trn.box_qty, trn.total_box_qty,trn.status FROM tbl_workorder_packing_trn as trn LEFT JOIN packing_mst as pack ON pack.packing_id = trn.packing_id where ( 1 AND trn.status != 2 AND workorder_id = ".$workorder_id.") ORDER BY trn.workorder_packing_trn_id";
		
		$result = $dbcon->query($query);
		$cnt = brp_mysqli_num_rows($result);

		$str = '<table class="display table table-bordered table-striped">
				                 <thead>
				                    <tr>
				                       <th width="5%" class="text-center">Sr.No.</th>
				                          <th width="15%" class="text-center ">Batch No</th>
				                          <th width="15%" class="text-center ">Packing Name</th>
				                          <th width="10%" class="text-center">Box Size</th>
				                          <th width="10%" class="text-center">Box</th>
				                          <th width="10%" class="text-center">Box QTY</th>
				                          <th width="25%" class="text-center">Bardcode</th>
				                          <th class="nosort"  width="5%" class="text-center">Action</th>     
                                      <th class="nosort">  <input id="checkAll" type="checkbox" onclick="check_All();"  name="chk[]"/></th>            
				                    </tr>
				                 </thead>
				                 <tbody>';
				                

		if($cnt == 0){
			 $str .='<tr> <td colspan="9" class="text-center"> NO DATA ADDED YET ! </td></tr>';
		}else{
			$x = 1;
			while($row = brp_mysqli_fetch_assoc($result)){
				$data_string = $row['batch_no'];
				$data_string = strtoupper($data_string);
				
			    $PNG_TEMP_DIR = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
			    
			    $PNG_WEB_DIR = '../temp/';

			    $edit_btn = "";
				$delete_btn = "";
				if($row['status'] == '3'){
			    	$delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_packing('.$row['workorder_packing_trn_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
			    if (!file_exists($PNG_TEMP_DIR))
			        mkdir($PNG_TEMP_DIR);
			    $filename = $PNG_TEMP_DIR.'test.png';
			    $errorCorrectionLevel = 'L';
				$matrixPointSize = 1;
				$filename = $PNG_TEMP_DIR.'test'.md5($data_string.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
				
	       		 QRcode::png($data_string, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
	       		$str .= "<tr>";
	       		$str .= "<td>".$x."</td>";
	       		$str .= "<td>".$row['batch_no']."</td>";
	       		$str .= "<td>".$row['packing_name']."</td>";
	       		$str .= "<td>".$row['packing_size']."</td>";
	       		if($row['status'] == '0'){
			  		$str .= '<td><input type="hidden" class="done_qty" value="'.$row['total_box_qty'].'">'. $row['box_qty'].'</td>';
			  	}else{
			  		$str .= '<td><input type="hidden" class="temp_qty" value="'.$row['total_box_qty'].'">'. $row['box_qty'].'</td>';
			  	}
	       		
	       		$str .= "<td>".$row['total_box_qty']."</td>";
	       		$str .= '<td class="text-center"><img style="height: 80px;" src="'.$PNG_WEB_DIR.basename($filename).'"/></td>';
	       		$str .= "<td>".$edit_btn." ".$delete_btn ."</td>";
	       		if($row['status'] == '0'){
			  		$str .= '<td><input type="checkbox" value="'.$row['workorder_packing_trn_id'].'" name="chk[]"/></td>';	
			  	}else{
			  		$str .= '<td></td>';	
			  	}
	       		
	       		$str .= "</tr>"; 
				
		  		$x++; 
			}
		}	
		 $str .='</tbody></table>';
		
		  echo $str;
	}else if(strtolower($POST['mode']) == "get_product_unit") {
		$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
		left join unit_mst as umst on umst.unitid=promst.product_base_unit
		left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
		WHERE product_id=".$POST['product_id'];
		$rs_type1=$dbcon->query($query1);
		$row1=brp_mysqli_fetch_assoc($rs_type1);

		if($row1['product_base_unit']!=$row1['product_conv_unit']){
			$row1['unit_status']="1";
			$opt='<option  value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
    			$opt .='<option  value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
		}else{
			$row1['unit_status']="0";
			$opt='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
		}
			//$row1['qye']=$query1;
			/*$row1['unit_option']=$opt;		
		echo json_encode($row1);*/
		echo $opt;
	}else if(strtolower($POST['mode']) == "get_workorder_qty") {
		$workorder_id = $POST['workorder_id'];
		$product_id = $POST['product_id'];
		$unit_id = $POST['unit_id'];

		$query="SELECT IFNULL(SUM(base_stock),0) as base_stock,IFNULL(SUM(convert_stock),0) as convert_stock,IFNULL(SUM(used_base_stock),0) as used_base_stock,IFNULL(SUM(used_convert_stock),0) as used_convert_stock,base_unit,convert_unit FROM tbl_stock_trn WHERE stock_flage = 1 AND stock_status != 2 AND product_id = " .$product_id. " AND workorder_id = " .$workorder_id;
		
		$result = $dbcon->query($query);
		$stock = 0;
		if(brp_mysqli_num_rows($result) > 0){
			$row = brp_mysqli_fetch_assoc($result);

			$base_stock = $row['base_stock'] - $row['used_base_stock'];
			$convert_stock = $row['convert_stock'] - $row['used_convert_stock'];

			if($row['base_unit'] == $unit_id){
				$stock = $base_stock;
			}else{
				$stock = $convert_stock;
			}
		}

		echo $stock;

	}else if(strtolower($POST['mode']) == "fieldadd") {
			$packing_id = $POST['packing_id'];
			$size = $POST['size'];
			$box_qty = $POST['box_qty'];
			$total_box_qty =$POST['total_box_qty'];
			$batch_no = $POST['batch_no'];
			$workorder_id = $POST['workorder_id'];
			$product_id = $POST['product_id'];

			$query = "SELECT sr_no FROM tbl_workorder_packing_trn WHERE workorder_id = ".$workorder_id." AND status != 2 AND company_id = " . $_SESSION['company_id'] . " ORDER BY workorder_packing_trn_id DESC LIMIT 1";
			$result = $dbcon->query($query);
			$sr_no = 1;
			if(brp_mysqli_num_rows($result) > 0){
				$row = brp_mysqli_fetch_assoc($result);
				$sr_no = $row['sr_no']+1;
			}

			// var_dump($sr_no);
			$arr['msg'] = 0;

			for($i=1;$i<=$box_qty;$i++){
				$info = array();

				$info['packing_id'] = $packing_id;
				$info['packing_size'] = $size;
				$info['box_qty'] = 1;
				$info['workorder_id'] = $workorder_id;
				$info['sr_no'] = $sr_no;
				$info['batch_no'] = $batch_no .'/'.$sr_no;
				$info['status'] = 3;
				$info['cdate']	= date("Y-m-d H:i:s"); 
				$info['company_id']	= $_SESSION['company_id']; 

				if($total_box_qty > 0){
					if($total_box_qty > $size){
						$info['total_box_qty'] = $size;
						$total_box_qty = $total_box_qty - $size;
					}else{
						$info['total_box_qty'] = $total_box_qty;
						$total_box_qty = $total_box_qty - $total_box_qty; 
					}

					$insert_id = add_record('tbl_workorder_packing_trn', $info, $dbcon);
					if($insert_id){
						$arr['msg'] = 1;
					}
					$sr_no++;
				}

			}
			if($arr['msg'] == '1'){
				update_batch_no($dbcon, $product_id);
			}
			echo json_encode($arr);
	}else if(strtolower($POST['mode']) == "delete") {
		$info['status']='2';
		$updateid=update_record('tbl_workorder_packing_trn', $info,"workorder_packing_trn_id=".$POST['eid'] , $dbcon);
		
		if($updateid){
			echo "1";
		}
		else{
			echo "0";	
		}
	}else if(brp_strtolower($POST['mode']) == "add") {
		$info['product_id'] =$POST['product_id'];
		$info['workorder_id'] =$POST['workorder_id'];
	    $info['unit_id'] =$POST['packing_unit'];
	    $info['packing_qty'] = $POST['total_qty'];
	    $info['remark'] =$POST['remark'];
	    $info['status'] =0;
	    $info['cdate']	= date("Y-m-d H:i:s"); 
		$info['company_id']	= $_SESSION['company_id'];

		$insert_id = add_record('tbl_workorder_packing', $info, $dbcon);
		if($insert_id){
			deduct_workorder_stock($dbcon,$POST['workorder_id'],$POST['total_qty'],$POST['packing_unit'],$insert_id);

		

			$upd_info['status']='0';
			$upd_info['workorder_packing_id']= $insert_id;
			$updateid=update_record('tbl_workorder_packing_trn', $upd_info,"status = 3 AND workorder_id = ".$POST['workorder_id'] ." and company_id = " . $_SESSION['company_id'], $dbcon);


			$sp_query = "SELECT packing_qty,packing_status,rp_req_qty FROM tbl_set_main_process WHERE sp_id = ". $POST['workorder_id'];
			$sp_row = brp_mysqli_fetch_assoc($dbcon->query($sp_query));
			$packing_qty = 0;
			if(!empty($sp_row['packing_qty'])){
				$packing_qty = $sp_row['packing_qty'];	
			}

			$packing_qty = $packing_qty + $info['packing_qty'];
			$sp_info['packing_qty'] = $packing_qty;
			if($packing_qty >= $sp_row['rp_req_qty']){
				$sp_info['packing_status'] = 1;
			}

			$updateid=update_record('tbl_set_main_process', $sp_info,"sp_id = ".$POST['workorder_id'] ." and company_id = " . $_SESSION['company_id'], $dbcon);

				add_workorder_packing_stock($dbcon,$insert_id);
			
			$arr['msg'] = 1;
			
		}else{
			$arr['msg'] = 0;
		}

		echo json_encode($arr);
	  }
	

function deduct_workorder_stock($dbcon,$workorder_id,$reserve_qty,$unit_id,$workorder_packing_id){
	$query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i	
	where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and i.workorder_id=".$workorder_id;
	$result_dstock=$dbcon->query($query_dstock);
	while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
		if($row_dstock['convert_unit']==$unit_id){
			$pending_stock=$row_dstock['pending_conv_stock'];
		}else{
			$pending_stock=$row_dstock['pending_base_stock'];	
		}
		if($reserve_qty>0){
			if($pending_stock>0){
				if($pending_stock>=$reserve_qty){
					$rqty=$reserve_qty;
					$reserve_qty=$reserve_qty-$reserve_qty;
				}else{
					$rqty=$pending_stock;
					$reserve_qty=$reserve_qty-$pending_stock;
				}

				$que="select * from product_mst as ta where product_id=".$row_dstock['product_id'];
				$rs_di=$dbcon->query($que);
				$re=brp_mysqli_fetch_assoc($rs_di);


				if($re['product_conv_unit']==$unit_id){
					$type="base_unit";
					$con_stock=$rqty;
					$base_stock=convert_stock_new($dbcon,$rqty,$row_dstock['product_id'],$type);
				}else{
					$type="conv_unit";
					$base_stock=$rqty;
					$con_stock=convert_stock_new($dbcon,$rqty,$row_dstock['product_id'],$type);
				}

				
				$info_gen['stock_date']			= date('Y-m-d');
				$info_gen['product_id']			= $row_dstock['product_id'];
				$info_gen['base_unit']			= $re['product_base_unit'];
				$info_gen['base_stock']			= $base_stock;
				$info_gen['convert_unit']		= $re['product_conv_unit'];
				$info_gen['convert_stock']		= $con_stock;
				$info_gen['stock_flage']		= 2;
				$info_gen['godown_id']			= $row_dstock['godown_id'];
				$info_gen['ref_name']			= "workorder_packing";
				$info_gen['ref_id']				= $workorder_packing_id;
				$info_gen['perent_id']			= $row_dstock['stock_id'];
				$info_gen['reserve_id']			= $row_dstock['reserve_id'];
				$info_gen['customer_id'] 		= $row_dstock['customer_id'];
				$info_gen['batch_id'] 			= $row_dstock['batch_id']; 
				$info_gen['batch_no']			= $row_dstock['batch_no'];
				$info_gen['base_rate']			= $row_dstock['base_rate'];
				$info_gen['conv_rate']			= $row_dstock['conv_rate'];
				$info_gen['workorder_id']		= $row_dstock['workorder_id'];
				$info_gen['mfg_date'] = $row_dstock['mfg_date'];
				$info_gen['exp_date'] = $row_dstock['exp_date'];

				$info_gen['user_id']			= $_SESSION['user_id'];
				$info_gen['cdate']				= date("Y-m-d H:i:s");
				$info_gen['company_id']			= $_SESSION['company_id'];


				$inserid_gen=add_record("tbl_stock_trn", $info_gen, $dbcon,$branch_id);
				
				if($row_dstock['base_unit']==$re['product_base_unit']){
					$used_base_stock=$row_dstock['used_base_stock']+$base_stock;
					$used_convert_stock=$row_dstock['used_convert_stock']+$con_stock;
				}else{
					$used_base_stock=$row_dstock['used_convert_stock']+$con_stock;
					$used_convert_stock=$row_dstock['used_base_stock']+$base_stock;
				}

				$info_stock['used_base_stock']		= $used_base_stock;
				$info_stock['used_convert_stock']	= $used_convert_stock;
				
				$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);
			}
		}
	}
}	

function add_workorder_packing_stock($dbcon,$workorder_packing_id){
	$query = "SELECT trn.*,wp.unit_id FROM tbl_workorder_packing_trn as trn LEFT JOIN tbl_workorder_packing as wp on wp.workorder_packing_id = trn.workorder_packing_id WHERE trn.workorder_packing_id = " . $workorder_packing_id;

	$result = $dbcon->query($query);
	while($row = brp_mysqli_fetch_assoc($result)){
		$query_dstock="select i.* from tbl_stock_trn as i	
		where stock_status=0 and stock_flage=2 and i.workorder_id=".$row['workorder_id']." AND i.ref_name='workorder_packing' and i.ref_id = " . $workorder_packing_id;
		$result_dstock=$dbcon->query($query_dstock);
		$row_dstock=brp_mysqli_fetch_assoc($result_dstock);

		if($row_dstock['base_unit'] == $row_dstock['convert_unit']){
			$base_stock = $row['total_box_qty'];
			$con_stock = $row['total_box_qty'];
		}else if($row_dstock['product_conv_unit']==$row['unit_id']){
			$type="base_unit";
			$con_stock=$row['total_box_qty'];
			$base_stock=convert_stock_new($dbcon,$row['total_box_qty'],$row_dstock['product_id'],$type);
		}else{
			$type="conv_unit";
			$base_stock=$row['total_box_qty'];
			$con_stock=convert_stock_new($dbcon,$row['total_box_qty'],$row_dstock['product_id'],$type);
		}


			$info_gen['stock_date']			= date('Y-m-d');
			$info_gen['product_id']			= $row_dstock['product_id'];
			$info_gen['base_unit']			= $row_dstock['base_unit'];
			$info_gen['base_stock']			= $base_stock;
			$info_gen['convert_unit']		= $row_dstock['convert_unit'];
			$info_gen['convert_stock']		= $con_stock;
			$info_gen['stock_flage']		= 1;
			$info_gen['godown_id']			= $row_dstock['godown_id'];
			$info_gen['ref_name']			= "workorder_packing";
			$info_gen['ref_id']				= $row['workorder_packing_trn_id'];
			// $info_gen['perent_id']			= $row_dstock['stock_id'];
			// $info_gen['reserve_id']			= $row_dstock['reserve_id'];
			// $info_gen['customer_id'] 		= $row_dstock['customer_id'];
			// $info_gen['batch_id'] 			= $row_dstock['batch_id']; 
			$info_gen['batch_no']			= $row['batch_no'];
			$info_gen['base_rate']			= $row_dstock['base_rate'];
			$info_gen['conv_rate']			= $row_dstock['conv_rate'];
			$info_gen['workorder_id']		= $row_dstock['workorder_id'];
			$info_gen['mfg_date'] = $row_dstock['mfg_date'];
			$info_gen['exp_date'] = $row_dstock['exp_date'];

			$info_gen['user_id']			= $_SESSION['user_id'];
			$info_gen['cdate']				= date("Y-m-d H:i:s");
			$info_gen['company_id']			= $_SESSION['company_id'];


			$inserid_gen=add_record("tbl_stock_trn", $info_gen, $dbcon,$branch_id);
	}
}

?>
