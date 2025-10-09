<?php

session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	} else {
		$POST = bulk_filter($dbcon,$_GET);
	}
		
	    if(strtolower($POST['mode']) == "req_returnable_to_main_grn") {
			
			$sp_array=$POST['check_status'];
			
			for($k=0;$k<count($sp_array);$k++)
			{
				if($POST['check_status'][$k]=="2")
				{
					$loop_id=$k;
					$eid=$POST['product_id'][$loop_id];
					$rrtemp_id=$POST['rrtemp_id'][$loop_id];
					$info1['item_unit_id']	= $POST['product_uom'][$loop_id];
					$info1['rr_approve_qty']	= $POST['product_approv_qty'][$loop_id];
					$info1['approve_status']	= 1;
					
					$updateid=update_record('tbl_returnable_channal_item', $info1, "id =".$rrtemp_id, $dbcon);
				}
			}
			
			$row['msg']="1";
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
		else if(strtolower($POST['mode'])== "get_product")
		{
			$where = "";
			if(!empty($_POST['channal_id'])){
				$where .= " and retc.id = '".$_POST['channal_id']."'";
			}
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$query = "select item.*,retc.id,retc.returnable_type	,pro.product_name,pro.product_type,cat.unit_name,led.l_name,led.l_id 
						from tbl_returnable_channal_item as item 
						left join tbl_returnable_channal as retc on retc.id=item.returnable_id
						left join product_mst as pro on pro.product_id=item.item_id
						left join unit_mst as cat on cat.unitid=item.item_unit_id
						left join tbl_ledger as led on led.l_id=retc.cust_id
		 				where item.status = 0 and item.approve_status = 1 and `item`.`user_id` = $userID and and grn_status= 0 and retc.returnable_type != 'non-returnable' and `item`.`company_id` = $companyID and led.l_id = '".$_POST['vender_id']."' $where";
			
			$result=$dbcon->query($query);
			$count=mysqli_num_rows($result);
			if($count){
			echo '<div class="form-group">
					<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th width="10%" class="text-center">
									<input type="checkbox" id="all_chk_box" style="width: 23px;height: 23px;margin-top: 0px;" onclick="check_all();">
								</th>
								<th width="15%" class="text-center">Type</th>
								<th width="15%" class="text-center">Product Type</th>
								<th width="15%" class="text-center">Product Name</th>
								<th width="10%" class="text-center">Product Category</th>
								<th width="8%" class="text-center">Qty</th>
								<th width="8%" class="text-center">Unit Of Per </th>
								<th width="8%" class="text-center">Returning Receipt Qty</th>
							</tr>';
							$i=1;
							while($rel_trn=mysqli_fetch_assoc($result))
							{
								$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
								if($rel_trn['returnable_type'] == 'returnable'){
									$type = "Returnable";
								}else{
									$type = "Non Returnable";
								}
								echo '<tr>
									<td style="vertical-align:top;text-align:center;">
										<input type="checkbox" name="che_box[]" class="chk_box" id="che_box'.$i.'" value="'.$rel_trn['id'].'" onclick="check_box('.$i.');" style="width: 23px;height: 23px;margin-top: 0px;">
										
										<input type="hidden" name="returnreceiptitem_id[]" id="returnreceiptitem_id'.$i.'" value="'.$rel_trn['id'].'" />
										
										<input type="hidden" class="chk_box_st" name="check_status[]" id="check_status'.$i.'" value="1" />
										
										<input type="hidden" name="rrtemp_id[]" id="rrtemp_id'.$i.'" value="'.$rel_trn['id'].'" />
									</td>
									<td style="vertical-align:top;">
										'.$type.'
									</td>
									<td style="vertical-align:top;">
										'.get_pro_type_name($rel_trn['product_type']).'
									</td>
									<td style="vertical-align:top;">
										<b>'.$rel_trn['product_name'].'</b>
										
										<input type="hidden" name="product_id[]" id="product_id'.$i.'" value="'.$rel_trn['item_id'].'" />
									</td>
									<td style="vertical-align:top;">
										'.$cat_name.'
									</td>
									<td style="vertical-align:top;" class="text-center">
										<input type="text" class="form-control" name="product_qty[]" id="product_qty'.$i.'" value="'.$rel_trn['item_qty'].'"  readonly />
									</td>	
									<td style="vertical-align:top;" class="text-center">
										<select class="form-control" id="product_uom'.$i.'" name="product_uom[]" onchange="get_alt_qty(this.value,'.$rel_trn['product_id'].','.$i.')" >
											'.getunit($dbcon,$rel_trn['item_unit_id']).'
										</select>
									</td>
									<td style="vertical-align:top;" class="text-center">
										<input type="number" class="form-control" name="product_approv_qty[]" onkeypress="return isNumberKey(event)" id="product_approv_qty'.$i.'" value="'.$rel_trn['item_qty'].'" max="'.$rel_trn['item_qty'].'" onKeyUp="if(this.value>'.$rel_trn['item_qty'].'){this.value='.trim($rel_trn['item_qty']).';}else if(this.value<0){this.value=0;}" readonly />
									</td>
								</tr>';
								$i++;
							}
						echo '</table>
						</div>
					</div>';
			}else{
				echo '<div class="form-group">
						<div class="col-md-12 col-xs-11">
							<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
								<tr id="field">
									<th class="text-center" style="font-size: 20px;background-color: #9a9a9a;color: #040404;">
										<strong>No Product Found....</strong>
									</th>
								</tr>
							</table>
						</div>
					</div>';
			}
			
		}
		else if(strtolower($POST['mode'])== "load_channal")
		{
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			echo $query = "select retc.*,led.l_name,led.l_id 
						from tbl_returnable_channal as retc 
						left join tbl_ledger as led on led.l_id=retc.cust_id
		 				where retc.status = 0 and retc.grn_status = 0  and `retc`.`user_id` = $userID and `retc`.`company_id` = $companyID and led.l_id = '".$_POST['vender_id']."'";
			$rs_state=$dbcon->query($query);		
			$str='';
			$str.= '<option value="">Select Channal</option>';
			while($row=mysqli_fetch_assoc($rs_state))
			{	
				$sel='';
				if($row['id']==$sid)
				{ $sel='selected="selected"'; }
				$str.='<option '.$sel.' value="'.$row['id'].'">'.$row['channal_id'].'</option>';
			}
			echo $str;
		}

?>