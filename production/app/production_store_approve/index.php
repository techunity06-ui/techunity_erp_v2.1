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
		
		
		if(brp_strtolower($POST['mode']) == "add_store_approve") {
			
			$branch_id=$POST['branch_id'];
			$start_qty=$POST['start_qty'];
			$pid=$POST['p_id'];

			$process_id	= $POST['process_id'];
			$product_id = $POST['product_id'];
			$product_base_qty	= $POST['start_qty'];
			$product_base_unit	= $POST['product_base_unit'];
			$product_con_qty	= $POST['start_qty'];
			$product_con_unit	= $POST['product_base_unit'];
			$product_version	= $POST['product_version'];
			

			$query="select * from tbl_reserve_stock where stock_flage=1 and company_id=".$_SESSION['company_id']." and p_id=".$pid;
			
			$result=$dbcon->query($query);
			$row1=brp_mysqli_fetch_assoc($result);			

			$base_qty = ($row1['approve_base_stock'] == "") ? 0 : $row1['approve_base_stock'];
			$conv_qty = ($row1['approve_convert_stock'] == "") ? 0 : $row1['approve_convert_stock'];

			$info['approve_base_stock'] = $base_qty + $product_base_qty;
			$info['approve_convert_stock'] = $conv_qty + $product_con_qty;
			

			update_record('tbl_reserve_stock',$info,"reserve_id=".$row1['reserve_id'] , $dbcon);

			echo '1';
		}
		
		else if(brp_strtolower($POST['mode']) == "show_material_list_new") {
			
			$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
				WHERE rpro.p_status!=2 AND rpro.p_id in (".$POST['p_id'].") and rpro.company_id = " . $_SESSION['company_id'];
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
				$o_qty=round($o_qty,6);
				//$o_qty=round($rel["req_qty_one"],6);
				$total_req_qty=$POST['pending_qty']*$o_qty;
				$total_req_qty=round($total_req_qty,4);
				$used_qty=$POST['max_start_qty']*$o_qty;
				$used_qty=round($used_qty,4);
				$cur_stock=reserve_stock($dbcon,$rel['rp_pid'],$rel['process_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id);
				
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