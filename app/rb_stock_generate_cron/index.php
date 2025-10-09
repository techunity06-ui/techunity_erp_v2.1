<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");

include_once(COMMON_FUNCTION_PATH."common_functions.php");	
include_once(COMMON_FUNCTION_PATH."common_sub_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_functions.php");


//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$branch_id = $POST['branch_id'];
		$whr='';
	    if($branch_id){
	        // $whr .= check_branch('zmst',$branch_id);
	    }

		if($POST['fil_product_type']!=''){
			$whr.=' and product_type='.$POST['fil_product_type'];
		}
			
		$appData = array();
		$i=1;
		$aColumns = array('zmst.product_id', 'zmst.product_type', 'zmst.product_icode','zmst.product_name','zmst.product_alias_name', 'zmst.cdate',  'dr.drawing_number', 'zmst.product_status', 'zmst.user_id', 'zmst.image_name','cron_status');
		$sIndexColumn = "product_id";
		$isWhere = array("zmst.product_status !=2 and zmst.company_id in (0,$_SESSION[company_id])".$whr);
		$sTable = "product_mst as zmst";			
		$isJOIN = array('left join tbl_drawing as dr on dr.drawing_id=zmst.drawing_id');
		$hOrder = "zmst.product_status desc ,zmst.product_name";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;


		foreach($sqlReturn as $row) {

			$row_data = array();
		
				if($row['product_status']==0)
				{  
					$status="<strong style='color:green'>Approved</strong>";
					
				}
				else
				{
					$status="<strong style='color:red' >Pending</strong>"; 
				
				}

				if($row['cron_status']==1)
				{  
					$cron="<strong style='color:green'>Done</strong>";
					
				}
				else
				{
					$cron="<strong style='color:red' >Pending</strong>"; 
				
				}
			
			
			$row_data[] = $row['sr'];
			
		
			$row_data[] = get_product_type_by_id($dbcon,$row['product_type']);
			
			$row_data[] = stripcslashes($row['product_name']); 
			
			$row_data[] = $row['product_icode']; 
			
			$row_data[] = $status; 
			$row_data[] = $cron; 
			
			$row_data[] ='<button class="btn btn-xs btn-info" data-original-title="Cron" data-toggle="tooltip" data-placement="top" onClick="run_cron('.$row['product_id'].')"><i class="fa fa-plus" style="margin-right:5px"></i> Stock Cron</button>'; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}else if(strtolower($POST['mode']) == "stock_cron_run") {
		$product_id = $POST['product_id'];

		$query = "SELECT * FROM opening_stock_mst WHERE status != 2 AND approve_status = 1 AND product_id = " .$product_id . " AND company_id = " . $_SESSION['company_id'];
		$result = $dbcon->query($query);

		if(brp_mysqli_num_rows($result) > 0){
			while($row = brp_mysqli_fetch_assoc($result)){
				$stock = $row['opening_stock_qty'];
				$conv_stock = $row['opening_stock_conv_qty'];

				$info_stockadd['stock_flage']				= "1";
				$info_stockadd['stock_date']				= date("Y-m-d",strtotime($row['cdate']));
				$info_stockadd['product_id']				= $row['product_id'];
				$info_stockadd['base_stock']				= $stock;
				$info_stockadd['base_unit']					= $row['opening_stock_unit'];
				$info_stockadd['convert_stock']				= $conv_stock;
				$info_stockadd['convert_unit']				= $row['opening_stock_conv_unit'];
				$info_stockadd['stock_flage']				= "1";
				$info_stockadd['godown_id']					= $row['location_id'];
				$info_stockadd['ref_name']					= 'opening_stock';
				$info_stockadd['ref_id']					= $POST['opening_stock_id'];
				$info_stockadd['stock_status']				= "0";
				$info_stockadd['cdate']						= date("Y-m-d H:i:s");
				$info_stockadd['user_id']					= $_SESSION['user_id'];
				$info_stockadd['company_id']				= $_SESSION['company_id'];
				$info_stockadd['batch_no']					= $row['batch_no'];
				$info_stockadd['base_rate']					= $row['base_rate'];
				$info_stockadd['conv_rate']					= $row['conv_rate'];

				$opening_stock_id=add_record('tbl_stock_trn',$info_stockadd, $dbcon,$row['branch_id']);
			}	
		}

		$query1 = "SELECT * FROM tbl_grn_trn WHERE ref_type NOT IN(1,3) AND grn_trn_status != 2 AND product_id = " .$product_id . " AND company_id = " . $_SESSION['company_id'];
		$result1 = $dbcon->query($query1);
		if(brp_mysqli_num_rows($result1) > 0){
			while($row1 = brp_mysqli_fetch_assoc($result1)){
				$base_rate = 0;
				$conv_rate = 0;
				if($row1['ref_type'] == '2'){
					$query = "select ptrn.unit_id,ptrn.conv_unit_id,ptrn.rate_unit,avg(ptrn.product_rate) as product_rate, pmst.product_base_qty, pmst.product_conv_qty from tbl_purchaseordertrn as ptrn 
						left join product_mst as pmst on pmst.product_id = ptrn.product_id
						where ptrn.purchaseordertrn_id=".$row1['purchaseordertrn_id']." group by ptrn.product_id";
					$exe = $dbcon->query($query);


					while($row = brp_mysqli_fetch_array($exe)){
						if($row['rate_unit'] == $row['unit_id']){
							$base_rate = $row['product_rate']; //1000
							$conv_rate = ($row['product_base_qty']/$row['product_conv_qty'])*$base_rate;
						}else{
							$conv_rate = $row['product_rate'];
							$base_rate = ($row['product_conv_qty']/$row['product_base_qty'])*$conv_rate;
						}
					}
				}

				$info_gen['stock_date']			= date('Y-m-d',strtotime($row1['cdate']));
				$info_gen['product_id']			= $row1['product_id'];
				$info_gen['base_unit']			= $row1['unit_id'];
				$info_gen['base_stock']			= $row1['product_qty'];
				$info_gen['convert_unit']		= $row1['product_conv_unit'];
				$info_gen['convert_stock']		= $row1['product_conv_qty'];
				$info_gen['stock_flage']		= 1;
				$info_gen['godown_id']			= $row1['grn_godown'];
				$info_gen['ref_name']			= 'tbl_grn_trn';
				$info_gen['ref_id']				= $row1['grn_trn_id'];
				$info_gen['perent_id']			= 0;
				$info_gen['reserve_id']			= 0;
				$info_gen['customer_id'] 		= $row1['customer_id'];
				$info_gen['batch_id'] 			= 0; 
				$info_gen['batch_no']			= '';
				
				$info_gen['base_rate']			= $base_rate;
				$info_gen['conv_rate']			= $conv_rate;

				$info_gen['user_id']			= $_SESSION['user_id'];
				$info_gen['cdate']				= date("Y-m-d H:i:s");
				$info_gen['company_id']			= $_SESSION['company_id'];

				$inserid_gen=add_record("tbl_stock_trn", $info_gen, $dbcon,$branch_id);
			}
		}


		$so_qry = "SELECT sales_ordertrn_id FROM tbl_sales_ordertrn WHERE sales_ordertrn_status != 2 AND product_id = " . $product_id;
		$so_result = $dbcon->query($so_qry);

		if(brp_mysqli_num_rows($so_result) > 0){
			while($so_row = brp_mysqli_fetch_assoc($so_result)){
				//start godown stock
				$query_rstock="select * from work_order_reserve_temp as i
				where i.status = 3 and i.sales_ordertrn_id =".$so_row['sales_ordertrn_id'];
				$result_rstock=$dbcon->query($query_rstock);
				while($row_rstock=brp_mysqli_fetch_assoc($result_rstock)){
					$reserve_qty=$row_rstock['reserve_qty'];
					$batch_where="";
					if(!empty($row_rstock['stock_id'])){
						$batch_where=" and i.stock_id=".$row_rstock['stock_id'];
					}
					 $query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i	
					where stock_status!=2 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) ".$batch_where." and i.product_id=".$row_rstock['product_id']." and i.godown_id=".$row_rstock['godown_id'];
					$result_dstock=$dbcon->query($query_dstock);
					while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
						if($row_dstock['convert_unit']==$row_rstock['unit_id']){
							$pending_stock=$row_dstock['pending_conv_stock'];
						}else{
							$pending_stock=$row_dstock['pending_base_stock'];	
						}
						if($reserve_qty>0){
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


							if($re['product_conv_unit']==$row_rstock['unit_id']){
								$type="base_unit";
								$con_stock=$rqty;
								$base_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
							}else{
								$type="conv_unit";
								$base_stock=$rqty;
								$con_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
							}

							
							$info_rese['reserve_date']		= date('Y-m-d');
							$info_rese['product_id']		= $row_rstock['product_id'];
							$info_rese['godown_id']			= $row_dstock['godown_id'];
							$info_rese['base_unit']			= $re['product_base_unit'];
							$info_rese['base_stock']		= $base_stock;
							$info_rese['convert_unit']		= $re['product_conv_unit'];
							$info_rese['convert_stock']		= $con_stock;
							$info_rese['stock_flage']		= "1";
							$info_rese['request_id']		= $row_rstock['rp_id'];
							$info_rese['ref_name']			= "wo_allocate";
							$info_rese['ref_id']			= "0";
							$info_rese['sales_order_trn_id']= $row_rstock['sales_ordertrn_id'];
							$info_rese['stock_id']			= $row_dstock['stock_id'];

							$info_rese['cdate']					= date("Y-m-d H:i:s");
							$info_rese['user_id']				= $_SESSION['user_id'];
							$info_rese['company_id']			= $_SESSION['company_id'];		
												
							$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row_dstock['branch_id']);
						
							
							$info_stock['used_base_stock']		= $row_dstock['used_base_stock']+$base_stock;
							$info_stock['used_convert_stock']	= $row_dstock['used_convert_stock']+$con_stock;
							
							$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);

						}
					}
				}


				$inv_query="select trn.*,inv.invoice_date,pro_mst.product_base_unit,pro_mst.batch_wise_stock_manage from tbl_invoicetrn as trn
				left join product_mst as pro_mst on pro_mst.product_id=trn.product_id
				left join tbl_invoice as inv on inv.invoice_id=trn.invoice_id
				where trn.trancation_status=0 and trn.sales_ordertrn_id=".$so_row['sales_ordertrn_id'];

				$inv_result=$dbcon->query($inv_query);

				while($inv_row=brp_mysqli_fetch_assoc($inv_result)){
					$res_query = "select res.*,pro.product_base_unit,pro.product_conv_unit from tbl_reserve_stock as res
			        left join product_mst as pro on pro.product_id=res.product_id
			        where stock_status!=2 and stock_flage=1 and sales_order_trn_id=" . $inv_row['sales_ordertrn_id'];
			        $res_result = $dbcon->query($res_query);

			      	$inv_qty = $inv_row['product_qty'];
			        $unit_id = $inv_row['unit_id'];
			        while($res_row = brp_mysqli_fetch_assoc($res_result)){
			        	if($inv_qty > 0){

			        		$stock = 0;

			        		if ($res_row['product_conv_unit'] == $unit_id)
	                        {
	                            $stock = $res_row['convert_stock'];
	                        }
	                        else
	                        {
	                            $stock = $res_row['base_stock'];;
	                        }
	                        $rqty = 0;
	                        if($stock>=$inv_qty){
								$rqty=$inv_qty;
								$inv_qty=$inv_qty-$inv_qty;
							}else{
								$rqty=$stock;
								$inv_qty=$inv_qty-$stock;
							}

			        		if ($res_row['product_conv_unit'] == $unit_id)
	                        {
	                            $type = "base_unit";
	                            $con_stock = $rqty;
	                            $base_stock = convert_stock($dbcon, $con_stock, $inv_row['product_id'], $type);
	                        }
	                        else
	                        {
	                            $type = "conv_unit";
	                            $base_stock = $rqty;
	                            $con_stock = convert_stock($dbcon, $base_stock, $inv_row['product_id'], $type);
	                        }

	                        $info['reserve_date'] = date('Y-m-d');
	                        $info['product_id'] = $inv_row['product_id'];
	                        $info['base_unit'] = $res_row['product_base_unit'];
	                        $info['base_stock'] = $base_stock;
	                        $info['convert_unit'] = $res_row['product_conv_unit'];
	                        $info['convert_stock'] = $con_stock;
	                        $info['godown_id'] = $res_row['godown_id'];
	                        $info['stock_id'] = $res_row['stock_id'];
	                        $info['perent_id'] = $res_row['reserve_id'];
	                        $info['stock_flage'] = 2;
	                        $info['request_id'] = $res_row['request_id'];
	                        $info['ref_name'] = "invoice_trn";
	                        $info['ref_id'] = $inv_row['trancation_id'];
	                        $info['sales_order_trn_id'] = $so_row['sales_ordertrn_id'];

	                        $info['cdate'] = date('Y-m-d H:i:s');
	                        $info['user_id'] = $_SESSION['user_id'];
	                        $info['company_id'] = $_SESSION['company_id'];
	                        $inserid = add_record('tbl_reserve_stock', $info, $dbcon, $branch_id);
	                        
	                        $upd_info['used_base_stock']  = $res_row['used_base_stock'] + $base_stock;
	                        $upd_info['used_convert_stock'] = $res_row['used_convert_stock'] + $con_stock;
	                        $upd_info['stock_status'] = 1;

	                        $res_ins_id = update_record('tbl_reserve_stock',$upd_info,"reserve_id=".$res_row['reserve_id'], $dbcon);

	                        add_stock($dbcon,$res_row['product_id'],$res_row['product_base_unit'],$info['reserve_date'],"invoice_trn",$inv_row['trancation_id'],$res_row['godown_id'],$base_stock,2,$res_row['branch_id'],$res_row['stock_id'],$res_ins_id,$res_row['customer_id']);

			        	}
			        }

			        if($inv_qty > 0){
			        	$query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i
							where stock_status !=2 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and i.product_id=".$inv_row['product_id'];

						$result_dstock=$dbcon->query($query_dstock);
						while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
							if($row_dstock['convert_unit']==$unit_id){
								$pending_stock=$row_dstock['pending_conv_stock'];
							}else{
								$pending_stock=$row_dstock['pending_base_stock'];	
							}

							if($inv_qty>0){
								$rqty = 0;
								if($pending_stock>=$inv_qty){
									$rqty=$inv_qty;
									$inv_qty=$inv_qty-$inv_qty;
								}else{
									$rqty=$pending_stock;
									$inv_qty=$inv_qty-$pending_stock;
								}

								if($row_dstock['convert_unit']==$unit_id){
									$type="base_unit";
									$con_stock=$rqty;
									$base_stock=convert_stock_new($dbcon,$rqty,$inv_row['product_id'],$type);
								}else{
									$type="conv_unit";
									$base_stock=$rqty;
									$con_stock=convert_stock_new($dbcon,$rqty,$inv_row['product_id'],$type);
								}

								$stock_date = date('Y-m-d');

								add_stock($dbcon,$inv_row['product_id'],$row_dstock['base_unit'],$stock_date,"invoice_trn",$inv_row['trancation_id'],$row_dstock['godown_id'],$base_stock,2,$row_dstock['branch_id'],$row_dstock['stock_id'],0,$row_dstock['customer_id']);

								$info_stock['used_base_stock']		= $row_dstock['used_base_stock'] + $base_stock;
								$info_stock['used_convert_stock']	= $row_dstock['used_convert_stock'] + $con_stock;
								
								$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);
							}
						}	
			        }
				}
			}
		}

		$return_qry = "SELECT * FROM tbl_returnable_channal_item WHERE status = 0 AND approve_status = 0 AND item_id = ". $product_id;

		$return_result = $dbcon->query($return_qry);
		if(brp_mysqli_num_rows($return_result) > 0){
			while ($rtc_row = brp_mysqli_fetch_assoc($return_result)) {
				$query_dstock="select i.*,(base_stock-used_base_stock) as  pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i
								where stock_status !=2 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and i.product_id=".$product_id;

		      	$item_qty = $rtc_row['item_qty'];
		        $item_unit_id = $rtc_row['item_unit_id'];
		        while($row_dstock = brp_mysqli_fetch_assoc($res_result)){
		        	if($item_qty > 0){

		        		$stock = 0;

		        		if ($row_dstock['convert_unit'] == $item_unit_id)
	                    {
	                        $stock = $row_dstock['convert_stock'];
	                    }
	                    else
	                    {
	                        $stock = $row_dstock['base_stock'];;
	                    }
	                    $rqty = 0;
	                    if($stock>=$item_qty){
							$rqty=$item_qty;
							$item_qty=$item_qty-$item_qty;
						}else{
							$rqty=$stock;
							$item_qty=$item_qty-$stock;
						}

		        		if ($row_dstock['convert_unit'] == $item_unit_id)
	                    {
	                        $type = "base_unit";
	                        $con_stock = $rqty;
	                        $base_stock = convert_stock($dbcon, $con_stock, $product_id, $type);
	                    }
	                    else
	                    {
	                        $type = "conv_unit";
	                        $base_stock = $rqty;
	                        $con_stock = convert_stock($dbcon, $base_stock, $product_id, $type);
	                    }

	                    $info_rese['reserve_date']		= date('Y-m-d');
						$info_rese['product_id']		= $product_id;
						$info_rese['godown_id']			= $row_dstock['godown_id'];
						$info_rese['base_unit']			= $row_dstock['base_unit'];
						$info_rese['base_stock']		= $base_stock;
						$info_rese['convert_unit']		= $row_dstock['convert_unit'];
						$info_rese['convert_stock']		= $con_stock;
						$info_rese['stock_flage']		= "1";
						$info_rese['request_id']		= "0";
						$info_rese['ref_name']			= "returning_receipt";
						$info_rese['ref_id']			= $rtc_row['id'];
						$info_rese['sales_order_trn_id']= $rtc_row['sales_ordertrn_id'];
						$info_rese['stock_id']			= $row_dstock['stock_id'];

						$info_rese['cdate']					= date("Y-m-d H:i:s");
						$info_rese['user_id']				= $_SESSION['user_id'];
						$info_rese['company_id']			= $_SESSION['company_id'];		
											
						$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row_dstock['branch_id']);
					
						
						$info_stock['used_base_stock']		= $row_dstock['used_base_stock']+$base_stock;
						$info_stock['used_convert_stock']	= $row_dstock['used_convert_stock']+$con_stock;
						
						$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);

		        	}
		        }
			}
		}


		$return_qry = "SELECT * FROM tbl_returnable_channal_item WHERE status = 0 AND approve_status = 1 AND item_id = ". $product_id;

		$return_result = $dbcon->query($return_qry);
		if(brp_mysqli_num_rows($return_result) > 0){
			while ($rtc_row = brp_mysqli_fetch_assoc($return_result)) {
				$query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i
							where stock_status!=2 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and i.product_id=".$inv_row['product_id'];

				$result_dstock=$dbcon->query($query_dstock);

				$item_qty = $rtc_row['item_qty'];
		        $item_unit_id = $rtc_row['item_unit_id'];
				
				while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
					if($row_dstock['convert_unit']==$item_unit_id){
						$pending_stock=$row_dstock['pending_conv_stock'];
					}else{
						$pending_stock=$row_dstock['pending_base_stock'];	
					}

					if($item_qty>0){
						$rqty = 0;
						if($pending_stock>=$item_qty){
							$rqty=$item_qty;
							$item_qty=$item_qty-$item_qty;
						}else{
							$rqty=$pending_stock;
							$item_qty=$item_qty-$pending_stock;
						}

						if($row_dstock['convert_unit']==$item_unit_id){
							$type="base_unit";
							$con_stock=$rqty;
							$base_stock=convert_stock_new($dbcon,$rqty,$product_id,$type);
						}else{
							$type="conv_unit";
							$base_stock=$rqty;
							$con_stock=convert_stock_new($dbcon,$rqty,$product_id,$type);
						}

						$stock_date = date('Y-m-d');

						add_stock($dbcon,$product_id,$row_dstock['base_unit'],$stock_date,"returning_receipt",$rtc_row['id'],$row_dstock['godown_id'],$base_stock,2,$row_dstock['branch_id'],$row_dstock['stock_id'],0,$row_dstock['customer_id']);

						$info_stock['used_base_stock']		= $row_dstock['used_base_stock'] + $base_stock;
						$info_stock['used_convert_stock']	= $row_dstock['used_convert_stock'] + $con_stock;
						
						$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);
					}
				}
			}
		}


		$pro_info['cron_status'] = 1;
		$pro_ins =update_record('product_mst',$pro_info,"product_id=".$product_id, $dbcon);

		if($pro_ins){
			echo '1';
		}else{
			echo '0';
		}

	}

?>