<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');

		
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(brp_strtolower($POST['mode']) == "add_return_material") {
			
			$remark = $POST['remark'];
			$issue_no = $POST['issue_no'];
			$issue_date = $POST['issue_date'];
			$release_id = $POST['release_id'];
			$release_type = $POST['release_type'];

			$return_user_id =  $POST['user_id'];
			$pid=$POST['pid'];
			$pid_wise_start_qty=$POST['pid_wise_start_qty'];		
			$product_ids=$POST['product_ids'];
			$release_trn_ids=$POST['release_trn_ids'];
				
			for($i=0;$i<count($pid);$i++)
			{

				$query1 = "select product_base_unit,product_conv_unit
			 			from product_mst where product_id = " . $product_ids[$i];

				$result1=$dbcon->query($query1);
				$pro_res =brp_mysqli_fetch_array($result1);

				$info['remark']	= $remark;
				$info['issue_no']	= $issue_no;
				$info['issue_date']	= $issue_date;
				$info['release_id']	= $release_id;	
				$info['return_user_id'] = $return_user_id;
				$info['release_trn_id']	= $release_trn_ids[$i];	
				$info['product_id'] = $product_ids[$i];
				$info['return_qty'] = $pid_wise_start_qty[$i];
				$info['return_unit'] = $pro_res['product_base_unit'];
				$type="conv_unit";
				$ret_qty=convert_stock($dbcon,$pid_wise_start_qty[$i],$product_ids[$i],$type);
				$info['return_conv_qty'] = $ret_qty;
				$info['return_conv_unit'] = $pro_res['product_conv_unit'];
				$info['release_type'] = $release_type;
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				
				$info['company_id']			= $_SESSION['company_id'];
				$info['branch_id']			= $_SESSION['branch_id'];



				$req_id = add_record('tbl_store_return_material',$info, $dbcon);
				// die;
				if($req_id){
					if($release_type){
						
					}else{
					
						$qry = "select *	from tbl_reserve_stock where  stock_status = 0 and stock_flage = 1 and p_id = " . $pid[$i] . " and product_id = " . $product_ids[$i];

						$result2=$dbcon->query($qry);
						$res2 =brp_mysqli_fetch_array($result2);

						$approve_base_stock = 0;
						$approve_convert_stock =0;

						if($res2['approve_base_stock'] != "" && $res2['approve_base_stock'] > 0){
							$approve_base_stock = $res2['approve_base_stock'];
						} 

						if($res2['approve_convert_stock'] != "" && $res2['approve_convert_stock'] > 0){
							$approve_convert_stock = $res2['approve_convert_stock'];
						} 

						if($approve_base_stock > 0) {
							$approve_base_stock = $approve_base_stock - $pid_wise_start_qty[$i];
							$approve_convert_stock = $approve_convert_stock - $ret_qty;
						}
						

						$stock['approve_base_stock'] = $approve_base_stock;
						$stock['approve_convert_stock'] = $approve_convert_stock;

						$table='tbl_reserve_stock';
						$tableid='reserve_id';
						update_record($table, $stock, $tableid."=".$res2['reserve_id'], $dbcon);
					}
				}
			}
			if($req_id > 0){
				echo "1";
			}else{
				echo "0";	
			}
			
		}
		

?>