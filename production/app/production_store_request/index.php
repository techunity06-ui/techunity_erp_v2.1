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
		
		
		if(brp_strtolower($POST['mode']) == "add_store_request") {
			
			$p_id = $POST['p_id'];
			$branch_id=$POST['branch_id'];
			$start_qty=$POST['start_qty'];
			
			$info['p_id']		= $POST['p_id'];
			$info['product_id']		= $POST['product_id'];
			
			$info['process_id']		= $POST['process_id'];
			
			$info['remark']		= $POST['remark'];
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $branch_id;

			$query1 = "select ap.p_id,ap.batch_no,ap.process_id,p.product_base_unit,p.product_conv_unit,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,
			(select if(sum(base_qty), sum(base_qty),0) as total_req_qty from tbl_store_request 			
		where p_id= ap.p_id and store_request_status = 0) as total_req_qty
			 from tbl_allocate_process as ap
					left join product_mst as p on p.product_id=ap.p_product_id 
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join unit_mst as umst on umst.unitid=ap.process_unit

					where ap.p_id in (".$p_id.")" ;

			$result1=$dbcon->query($query1);
			// $start_qty=0;
			$s=1;
			while($row=brp_mysqli_fetch_array($result1)){
				$req_qty=production_start_count_using_p_id($dbcon,$row['p_id'],0);
				$info['p_id']		= $row['p_id'];
				$info['rp_id']		= $row['p_ref_id'];
				$info['base_unit']	= $row['product_base_unit'];
					$info['conv_unit']	= $row['product_conv_unit'];
				if($start_qty <= $req_qty){
					$info['base_qty']	= $start_qty;
					$info['conv_qty']	= $start_qty;

				}else{
					$info['base_qty']	= $req_qty;
					$info['conv_qty']	= $req_qty;
				}
				
				$req_id=add_record('tbl_store_request',$info, $dbcon,$branch_id);

				if($start_qty <= $req_qty){
					break;
				}
			} 

			if($req_id > 0){
				echo "1";
			}else{
				echo "0";
			}
			
		}
		if(brp_strtolower($POST['mode']) == "add_store_request_using_model") {

			
			$query1 = "select product_base_unit,product_conv_unit
			 			from product_mst where product_id = " . $POST['product_id'];

			$result1=$dbcon->query($query1);
			// $start_qty=0;
			$s=1;
			$pro_res =brp_mysqli_fetch_array($result1);

			$branch_id=$POST['branch_id'];
			$start_qty=$POST['start_qty'];
			$process_id = $POST['process_id'];
			
			$info['product_id']		= $POST['product_id'];
			$info['process_id']		= $POST['process_id'];
			$info['remark']		= $POST['remark'];
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $branch_id;
			$info['base_unit'] = $pro_res['product_base_unit'];
			$info['conv_unit'] = $pro_res['product_conv_unit'];
			
			$pid=$POST['pid'];
			$rp_id=$POST['pid'];
			$pid_wise_start_qty=$POST['pid_wise_start_qty'];
			for($i=0;$i<count($pid);$i++)
			{
				$conv_stock=convert_stock_new($dbcon,$pid_wise_start_qty[$i],$info['rp_id'],"conv_unit");
				
				$queryrp = "select p_ref_id from tbl_allocate_process where p_id = " . $pid[$i];
				$resultrp=$dbcon->query($queryrp);
				$pro_resrp =brp_mysqli_fetch_array($resultrp);
				
				$info['p_id']		= $pid[$i];
				$info['rp_id']		= $pro_resrp['p_ref_id'];
				$info['base_qty']	= $pid_wise_start_qty[$i];
				$info['conv_qty']	= $conv_stock;

				$req_id = add_record('tbl_store_request',$info, $dbcon,$branch_id);
				
			}
			if($req_id > 0){
				echo "1";
			}else{
				echo "0";	
			}
			
		}
		
		else if(brp_strtolower($POST['mode']) == "show_material_list_new") {


			$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
				WHERE rpro.p_status!=2 AND rpro.p_id in (".$POST['p_id'].")";
			$resul=$dbcon->query($bom);
			$rel1=brp_mysqli_fetch_assoc($resul);
			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			
			$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join unit_mst as bunit on bunit.unitid=rpro.process_unit
			left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
			WHERE rpro.status!=2 AND rpro.perent_id in (".$rel1['views'].") group by rpro.rp_pid" ;
			$result=$dbcon->query($bom1);


			
			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result)){
				$o_qty=convert_stock($dbcon,$rel["req_qty_one"],$rel['rp_pid'],"base_unit");
				$rel["req_qty_one"]=round($rel["req_qty_one"],6);
				// echo 'pen'. $POST['max_start_qty'];
				$o_qty=round($o_qty,6);
				//$o_qty=round($rel["req_qty_one"],6);
				$total_req_qty=$POST['pending_qty']*$o_qty;
				$total_req_qty=round($total_req_qty,4);
				$used_qty=$POST['max_start_qty']*$o_qty;
				$used_qty=round($used_qty,4);
				$cur_stock=reserve_stock($dbcon,$rel['rp_pid'],$rel['process_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,0);
				// echo '-->'.$cur_stock . "</br>";
				$cur_stock=round($cur_stock,4);
				$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
				//$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['process_unit'],"",$rel_n1['rp_id']);
				echo '<tr>
						<td>'.$rel["product_name"].'
							<input type="hidden" class="" name="row_product_id[]" id="row_product_id'.$i.'" value="'.$rel['rp_pid'].'" />
						</td>
						<td>'.$cat_name.'</td>
						<td>'.$o_qty.'
							<input type="hidden" class="" name="row_req_qty_one[]" id="row_req_qty_one'.$i.'" value="'.$o_qty.'" />
						</td>
						<td>'.$total_req_qty.'</td>
						<td>'.$cur_stock.'</td>
						<td>'.$used_qty.'</td>
						<td>'.$rel["base_unit_name"].'
							<input type="hidden" class="" name="row_unit_id[]" id="row_unit_id'.$i.'" value="'.$rel['process_unit'].'" />
						</td>
				</tr>
				';
				$i++;
			}
		}
		else if(brp_strtolower($POST['mode'])== "get_series_no"){
			$query="select * from tbl_invoicetype where status=0 and type_id=11 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		else if(brp_strtolower($POST['mode'])== "load_invoiceno"){
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno'] = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}

?>