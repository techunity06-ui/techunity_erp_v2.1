<?php

session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET) ;
		
if(strtolower($POST['mode']) == "load_bom_product_detail") {
	
    
	$product = $POST['product'];
	$eid = $POST['eid'];
	$check_price = $POST['check_price'];
	$_SESSION["check_price"] = $POST["check_price"];
	
	$count_check=check_product_price_list($dbcon,$product,$eid);
	
	//echo $count_check;exit;
		$sel = $dbcon->query("select * from product_mst where product_id='$product'");
		$row = brp_mysqli_fetch_array($sel);
		
		if($count_check>0)
		{
			$price_detail=check_parent_price_list($dbcon,$product);
			
			$landing_cost = $price_detail['product_sale_price'];
			
			$href=ROOT.FINANCE_ROOT.'cost_detail/'.$price_detail['price_list_id'].'/'.$product.'/'.$eid;
		}
		else
		{
			$landing_cost = get_landing_cost($dbcon,$product,$check_price,$eid);
			
			$query = $dbcon->query("Select bom_version_id,product_id 
					from pro_ms_bom_version
					where bom_version_status = 0 and is_default_bom='1' and product_id='$product'" );
			
			$count = brp_mysqli_num_rows($query);
			
			if($count>0)
			{
				$row1 = brp_mysqli_fetch_array($query);
				//print_r($row1);exit;
				$href=ROOT.FINANCE_ROOT.'cost_detail/'.$row1['bom_version_id'].'/'.$product.'/'.$eid;
			}
			else
			{
				$href="";
			}
		}
		
		
		$str.='
			
			<table class="table table-bordered table-hover table-stripped">
															
				<tr>
					<th class="table_th">Product Name</th>
					<td><a>'.$row['product_name'].'</a></td>
				</tr>
				<tr>
					<th class="table_th">Landing Cost</th>
					<td>
						<a href="'.$href.'" style="border-bottom:dashed blue thin">'.$landing_cost.'</a>
						<input type="hidden" class="form-control" name="landing_cost" id="landing_cost" value="'.$landing_cost.'" />
					</td>
				</tr>
				<tr>
					<th class="table_th">Profit</th>
					<td>
						Amount:<input type="text" class="form-control" name="profit_amt" id="profit_amt" onkeyup="get_profit_amt(\'amt\')" /><br>
						Percentage:<input type="text" class="form-control" name="profit_per" id="profit_per" onkeyup="get_profit_amt(\'per\')" />
					</td>
				</tr>
				<tr>
					<th class="table_th">Other Expense</th>
					<td>
						Amount:<input type="text" class="form-control" name="expense_amt" id="expense_amt" onkeyup="get_expense_amt(\'amt\')" /><br>
						Percentage:<input type="text" class="form-control" name="expense_per" id="expense_per" onkeyup="get_expense_amt(\'per\')" />
					</td>
				</tr>
				<tr>
					<th class="table_th">Total</th>
					<td>
						<input type="text" class="form-control" name="total" id="total" />
					</td>
				</tr>
				<tr>
					<th class="table_th">Discount</th>
					<td>
						Amount:<input type="text" class="form-control" name="disc_amt" id="disc_amt" onkeyup="get_disc_amt(\'amt\')" /><br>
						Percentage:<input type="text" class="form-control" name="disc_per" id="disc_per" onkeyup="get_disc_amt(\'per\')" />
					</td>
				</tr>
				<tr>
					<th class="table_th">Sales Price</th>
					<td>
						<input type="text" class="form-control" name="sales_price" id="sale_price" />
					</td>
				</tr>
				<tr>
					<th colspan="2"  style="text-align:center">
						<input type="hidden" class="form-control" id="product_id" name="product_id" value="'.$product.'" />
						<input type="hidden" class="form-control" id="price_list_id" name="price_list_id" value="'.$eid.'" />
						<input type="button" value="ADD" class="btn btn-success" onclick="add_product_price()" />
					</th>
				</tr>
				
			</table>
		';
		
	if($count_check==0)
	{
		//add record in price list table 
		
		$info1['price_list_id']=$eid;
		$info1['product_id']=$product;
		$info1['parent_id']=0;
		$info1['product_sale_price']=$landing_cost;
		$info1['user_id']=$_SESSION['user_id'];
		$info1['company_id']=$_SESSION['company_id'];
		$info1['usertype_id']=$_SESSION['usertype_id'];
		$info1['main_product']=1;
		$info1['main_product_id']=$product;
		
		$inserinvoiceid=add_record('tbl_price_list_details',$info1,$dbcon);
			
	}
	
	
	echo $str;
	
}
else if(brp_strtolower($POST['mode']) == "load_tempoutward") {
	
		$check_price=$_SESSION['check_price'];
		$bom_version_id = $POST['bom_version_id'];
		$bom_product_id = $POST['bom_product_id'];
		$eid = $POST['eid'];
		$check_count=check_count_product_price_list_parent($dbcon,$bom_product_id);
		
		//echo $check_count;exit;
		//check current level 
		
		$bom_current_level = bom_current_level_pricelist($dbcon,$bom_product_id);
		//print_r($POST);
		if($check_count==0)
		{
			/* Sanat ::  added bom version id condition -  04-08-2021*/
			$query="select mst.*,tb.bom_id as bid,product.product_name,product.product_icode, product.product_type as ptype,product.product_desc,product.product_setting_check,product.product_id,product.product_base_unit,u.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,dr.drawing_number,product.drawing_id,product.image_name 

			from tbl_bomtrn as mst 

			inner join tbl_bom as tb on tb.bom_id=mst.bom_id
			left join product_mst as product on product.product_id=mst.product_id 
			left join unit_mst as u on u.unitid=mst.product_base_unit
			left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
			left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
			where mst.bom_trn_status=0 and tb.bom_product=".$POST['bom_product_id']." and mst.p_bom_version_id = ". $POST['bom_version_id'] ." order by mst.bom_trn_id asc";
			//exit;

				// echo $query;exit;
			
			$result=$dbcon->query($query);

			if(brp_mysqli_num_rows($result)>0)
			{

				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					
					$landing_cost = get_landing_cost($dbcon,$rel['product_id'],$check_price);
					
					$count_check=check_product_price_list_child($dbcon,$bom_product_id,$rel['product_id'],$eid);
					
					if($count_check==0)
					{
						//add record in price list table 
				
						$info1['price_list_id']=$eid;
						$info1['product_id']=$rel['product_id'];
						$info1['parent_id']=$bom_product_id;
						$info1['product_sale_price']=$landing_cost;
						$info1['bom_version_id']=$rel['bom_version_id'];
						$info1['main_product_id']=$bom_product_id;
						$info1['bom_level']=$bom_current_level+1;
						$info1['user_id']=$_SESSION['user_id'];
						$info1['company_id']=$_SESSION['company_id'];
						$info1['usertype_id']=$_SESSION['usertype_id'];
						
						$inserinvoiceid=add_record('tbl_price_list_details',$info1,$dbcon);
					
					}
					
					$i++;
				}
			}
		
		}
		
		$query="select mst.*,product.product_name,product.product_icode, product.product_type as ptype,product.product_desc,product.product_setting_check,product.product_id,product.product_base_unit,u.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,dr.drawing_number,product.drawing_id,product.image_name from tbl_price_list_details as mst 

		left join product_mst as product on product.product_id=mst.product_id 
		left join unit_mst as u on u.unitid=product.product_base_unit
		left join unit_mst as cunit on cunit.unitid=product.product_conv_unit
		left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
		where mst.price_list_id='$eid' and mst.parent_id='$bom_product_id'";
		//exit;

			// echo $query;die;
		
		$result=$dbcon->query($query);

		echo '<div class="col-md-12">
		<div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="8%">#</th>
		<th class="text-center" width="15%">Product Type</th>
		<th class="text-center" width="28%">Product Name
		</th>
		<th class="text-center" width="28%">Product Itemcode
		</th>
		<th class="text-center hide_act_add" width="8%">Unit </th>
		<th class="text-center hide_act_add" width="10%"> Qty </th>

		<th class="text-center hide_act_add" width="8%">UOM </th>
		<th class="text-center hide_act_add" width="10%">Actual Qty.</th>
		
		<th class="text-center hide_act_add" width="10%">Landing Cost.</th>
		</tr>
		<tbody id="fil_product_tbl">';
		if(brp_mysqli_num_rows($result)>0)
		{


			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				
				$landing_cost = $rel['product_sale_price'];
									
				$setting_array=explode(",",$rel['product_setting_check']);

				if($rel['ptype']=='3'  || $rel['ptype']=='5')
				{
					$href="";
					$a=base64_encode ('1,2');
					$style="style='color:black !important;'";

				}
				else
				{
					$product_id=$rel['product_id'];
					$bom_version_id = $rel['bom_version_id'];
					//$href="href='".ROOT.FINANCE_ROOT."cost_allocate/".$rel['p_bom_id']."/".$rel['bom_id']."/".$last_param."/".$rel['p_bom_version_id']."/".$eid."'";
					
					$href="href='".ROOT.FINANCE_ROOT."cost_allocate/".$bom_product_id."/".$rel['parent_id']."/".$rel['product_id']."/".$rel['bom_version_id']."/".$eid."'";
					

					$style="style='border-bottom:dotted 2px blue;cursor:pointer;'";
				}
				
				$product_base_qty=number_format($rel['product_base_qty'],3,'.','');
				$product_conv_qty=number_format($rel['product_conv_qty'],3,'.','');

				if($rel['drawing_id']!=0){
					$drawing_number = $rel['drawing_number'];
				}else{
					$drawing_number = '0';
				}

				if($rel['image_name']!=null){
					$image_name = '<a href="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel["image_name"].'" target="_blank"><img src="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;"></a>';
				}else{
					$image_name = '';
				}
				if($rel['ptype']=='3'  || $rel['ptype']=='5'){
					$add_process = "";
				}else{
					$add_process = '<a class="btn btn-xs btn-primary" data-original-title="Add Process" data-toggle="tooltip" onclick="direct_show_product_process('.$rel['product_id'].','.$rel['bom_version_id'].','.$rel['bom_trn_id'].')" data-placement="top"><i class="fa fa-plus"></i></a>';
				}

				echo '<tr id="fieldtr'.$id.'" >

				
				<td style="vertical-align:top;">
				'.$i.'
				</td>
				
				<td style="vertical-align:top;">
				'.get_product_type_by_id($dbcon,$rel['ptype']).'
				</td>

			
				<td style="vertical-align:top;">
				<a '.$href.' '.$style.' >'.$rel['product_name'].'</a>'. $button. '
				<br/>'.$drawing_number.'
				</td>
				<td style="vertical-align:top;">
				'.$rel['product_icode'].'
				</td>
				<td style="vertical-align:top;">
				'.$rel['base_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$product_base_qty.'
				</td>
				<td style="vertical-align:top;">
				'.$rel['conv_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$product_conv_qty.'
				</td>
				
				<td style="vertical-align:top;" contenteditable="true" data-old_value="'.$landing_cost.'" >
				<input type="text" class="form-control" name="" id="'.$rel['price_list_detail_id'].'" value="'.$landing_cost.'" onBlur="saveInlineEdit1(this.id,this.value)" />
				</td>
				
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="8" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</tbody></table>			 
		</div>
		</div>	';
		

	}

else if(brp_strtolower($POST['mode']) == "load_alloted_tempoutward") {
	
		$check_price=$_SESSION['check_price'];
		$check_count=check_count_product_price_list_parent($dbcon,$POST['sel_product_id'],$POST['eid']);
		//echo $POST['sel_product_id'];
		//check current level 
		
		$bom_current_level = bom_current_level_pricelist($dbcon,$POST['sel_product_id'],$POST['eid']);
		
		//echo $bom_current_level;exit;
		//echo $check_count;exit;
		if($check_count==0)
		{
				/* Sanat ::  added bom version id condition -  04-08-2021*/
				$query="select mst.*,tb.bom_id as bid,tb_t.tot_standrad_qty,product.product_name, product.product_icode,product.product_type as ptype,product.product_desc,product.product_setting_check,product.product_id,product.product_base_unit,u.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as mst 
				left  join tbl_bom as tb on tb.bom_id=mst.bom_id
				left join tbl_bom as tb_t on tb_t.bom_id=mst.p_bom_id
				left join product_mst as product on product.product_id=mst.product_id 
				left join unit_mst as u on u.unitid=mst.product_base_unit
				left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
				where mst.bom_trn_status=0 and mst.p_bom_version_id = ". $POST['bom_version_id'] ." order by mst.bom_trn_id asc";
				
			//	echo $query;exit;
			//exit;
				
				$multiplication=check_multiplication($dbcon,$POST['sel_product_id'],'');

			$eid = $POST['eid'];
			
			
			
			$result=$dbcon->query($query);

			if(brp_mysqli_num_rows($result)>0)
			{

				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					
					$count_check=check_product_price_list_child($dbcon,$POST['sel_product_id'],$rel['product_id'],$eid);
					
					$landing_cost = get_landing_cost($dbcon,$rel['product_id'],$check_price);
					
					if($count_check==0)
					{
						//add record in price list table 
				
						$info1['price_list_id']=$eid;
						$info1['product_id']=$rel['product_id'];
						$info1['parent_id']=$POST['sel_product_id'];
						$info1['product_sale_price']=$landing_cost;
						$info1['bom_version_id']=$rel['bom_version_id'];
						$info1['main_product_id']=$POST['main_product_id'];
						$info1['bom_level']=$bom_current_level+1;
						$info1['user_id']=$_SESSION['user_id'];
						$info1['company_id']=$_SESSION['company_id'];
						$info1['usertype_id']=$_SESSION['usertype_id'];
						
						$inserinvoiceid=add_record('tbl_price_list_details',$info1,$dbcon);
					
					}
				}
			}
		}
		
		
		//Show record from tbl_price_list_details
		
		$query="select mst.*,product.product_name,product.product_icode, product.product_type as ptype,product.product_desc,product.product_setting_check,product.product_id,product.product_base_unit,u.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,dr.drawing_number,product.drawing_id,product.image_name from tbl_price_list_details as mst 

		left join product_mst as product on product.product_id=mst.product_id 
		left join unit_mst as u on u.unitid=product.product_base_unit
		left join unit_mst as cunit on cunit.unitid=product.product_conv_unit
		left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
		where mst.price_list_id='$eid' and mst.parent_id='$POST[sel_product_id]'";
		//exit;

			// echo $query;die;
		
		$result=$dbcon->query($query);

		echo '<div class="col-md-12">
		<div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="8%">#</th>
		<th class="text-center" width="15%">Product Type</th>
		<th class="text-center" width="28%">Product Name
		</th>
		<th class="text-center" width="28%">Product Itemcode
		</th>
		<th class="text-center hide_act_add" width="8%">Unit </th>
		<th class="text-center hide_act_add" width="10%"> Qty </th>

		<th class="text-center hide_act_add" width="8%">UOM </th>
		<th class="text-center hide_act_add" width="10%">Actual Qty.</th>
		
		<th class="text-center hide_act_add" width="10%">Landing Cost.</th>
		</tr>
		<tbody id="fil_product_tbl">';
		if(brp_mysqli_num_rows($result)>0)
		{


			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				
				$landing_cost = $rel['product_sale_price'];
									
				$setting_array=explode(",",$rel['product_setting_check']);

				if($rel['ptype']=='3'  || $rel['ptype']=='5')
				{
					$href="";
					$a=base64_encode ('1,2');
					$style="style='color:black !important;'";

				}
				else
				{
					$product_id=$rel['product_id'];
					$bom_version_id = $rel['bom_version_id'];
				
					//$href="href='".ROOT.FINANCE_ROOT."cost_allocate/".$rel['p_bom_id']."/".$rel['bom_id']."/".$last_param."/".$rel['p_bom_version_id']."/".$eid."'";
					
					
					$href="href='".ROOT.FINANCE_ROOT."cost_allocate/".$POST['main_product_id']."/".$rel['parent_id']."/".$rel['product_id']."/".$rel['bom_version_id']."/".$POST['eid']."'";
					
					$style="style='border-bottom:dotted 2px blue;cursor:pointer;'";
				}
				
				$product_base_qty=number_format($rel['product_base_qty'],3,'.','');
				$product_conv_qty=number_format($rel['product_conv_qty'],3,'.','');

				if($rel['drawing_id']!=0){
					$drawing_number = $rel['drawing_number'];
				}else{
					$drawing_number = '0';
				}

				if($rel['image_name']!=null){
					$image_name = '<a href="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel["image_name"].'" target="_blank"><img src="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;"></a>';
				}else{
					$image_name = '';
				}
				if($rel['ptype']=='3'  || $rel['ptype']=='5'){
					$add_process = "";
				}else{
					$add_process = '<a class="btn btn-xs btn-primary" data-original-title="Add Process" data-toggle="tooltip" onclick="direct_show_product_process('.$rel['product_id'].','.$rel['bom_version_id'].','.$rel['bom_trn_id'].')" data-placement="top"><i class="fa fa-plus"></i></a>';
				}

				echo '<tr id="fieldtr'.$id.'" >

				
				<td style="vertical-align:top;">
				'.$i.'
				</td>
				
				<td style="vertical-align:top;">
				'.get_product_type_by_id($dbcon,$rel['ptype']).'
				</td>

			
				<td style="vertical-align:top;">
				<a '.$href.' '.$style.' >'.$rel['product_name'].'</a>'. $button. '
				<br/>'.$drawing_number.'
				</td>
				<td style="vertical-align:top;">
				'.$rel['product_icode'].'
				</td>
				<td style="vertical-align:top;">
				'.$rel['base_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$product_base_qty.'
				</td>
				<td style="vertical-align:top;">
				'.$rel['conv_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$product_conv_qty.'
				</td>
				
				<td style="vertical-align:top;" contenteditable="true" data-old_value="'.$landing_cost.'" >
				<input type="text" class="form-control" name="" id="'.$rel['price_list_detail_id'].'" value="'.$landing_cost.'" onBlur="saveInlineEdit1(this.id,this.value)" />
				</td>
				
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="8" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</tbody></table>			 
		</div>
		</div>	';
		
		
		
	}
	
else if(brp_strtolower($POST['mode']) == "save_pro_price") {
	
	$id = $POST['text_id'];
	$value = $POST['text_value'];
	$info['product_sale_price'] = $value;
	
	$dbcon->query("update tbl_price_list_details set product_sale_price='$value' where price_list_detail_id='$id'");
	//update_record('tbl_price_list_details', $info,"price_list_detail_id='".$id."'" , $dbcon);
	
	echo $id."--".$value;
}
else if(brp_strtolower($POST['mode']) == "add_product_price_list") {
	
	//print_r($POST);
	
	$profit_amt = $POST['profit_amt'];
	$profit_per = $POST['profit_per'];
	$expense_amt = $POST['expense_amt'];
	$expense_per = $POST['expense_per'];
	$total = $POST['total'];
	$disc_amt = $POST['disc_amt'];
	$disc_per = $POST['disc_per'];
	$sale_price = $POST['sale_price'];
	$product_id = $POST['product_id'];
	$price_list_detail_id = $POST['price_list_detail_id'];
	$landing_price = $POST['landing_cost'];
	
	$info['profit_amt']=$profit_amt;
	$info['profit_per']=$profit_per;
	$info['expense_amt']=$expense_amt;
	$info['expense_per']=$expense_per;
	$info['total']=$total;
	$info['disc_amt']=$disc_amt;
	$info['disc_per']=$disc_per;
	$info['landing_price']=$landing_price;
	$info['product_sale_price']=$sale_price;
	
	$update_id = update_record('tbl_price_list_details', $info," price_list_id='$price_list_detail_id' and product_id='$product_id'" ,$dbcon);
	//print_r($info);
	echo $update_id;
}

else if(brp_strtolower($POST['mode']) == "list_price_list_products") {
	
	$price_list_id = $POST['price_list_id'];
	$product_id = $POST['product_id'];
	
	$str="";
	
	$str.="
		<table class='table table-bordered table-stripped'>
		
			<tr class='table_th'>
				<th class='text_center'>#</th>
				<th class='text_center'>Product</th>
				<th class='text_center'>Landing Cost</th>
				<th class='text_center'>Profit</th>
				<th class='text_center'>Other Expense</th>
				<th class='text_center'>Total</th>
				<th class='text_center'>Discount</th>
				<th class='text_center'>Sale Price</th>
				<th class='text_center'>Action</th>
			</tr>
	";
	
	$cnt=1;
	$sel = $dbcon->query("select * from tbl_price_list_details where price_list_id='$price_list_id' and main_product='1'");
	while($row = brp_mysqli_fetch_array($sel))
	{
		$product_name = get_id_detail($dbcon,'product_mst','product_id',$row['product_id'],'product_name');
		
		$str.="
		
			<tr>
				<td>".$cnt."</td>
				<td>".$product_name."</td>
				<td>".$row['landing_price']."</td>
				<td><strong>Amount : </strong> ".$row['profit_amt']."<br> <strong>Percentage : </strong>".$row['profit_per']." </td>
				<td><strong>Amount : </strong> ".$row['expense_amt']."<br> <strong>Percentage : </strong>".$row['expense_per']." </td>
				<td>".$row['total']."</td>
				<td><strong>Amount : </strong> ".$row['disc_amt']."<br> <strong>Percentage : </strong>".$row['disc_per']." </td>
				<td>".$row['product_sale_price']."</td>
				<td>
					<a class='btn btn-xs btn-warning' data-original-title='Edit' data-toggle='tooltip' data-placement='top' onclick='edit_price_product(".$row['product_id'].",".$row['price_list_id'].")'><i class='fa fa-pencil'></i></a>
					
				</td>
				
			</tr>
		
		";
		
		$cnt++;
	}
	
	$str."</table>";
	
	echo $str;
	
}

else if(brp_strtolower($POST['mode']) == "edit_price_product") {
	
	$product_id=$POST['product_id'];
	$eid=$POST['eid'];
	
	$sel = $dbcon->query("select * from tbl_price_list_details where product_id='$product_id' and price_list_id='$eid'");
	$row = brp_mysqli_fetch_assoc($sel);
	
	echo json_encode($row);
	
}
else if(strtolower($POST['mode']) == "fetch") {
		
		
			$appData = array();
			$i=1;
			$aColumns = array('price_list_id','price_list_effective_date','price_list_expire_date','price_list_version','price_list_allocate_type','price_list_allocate_to','price_list_status','price_list_version','version_relase');
			$sIndexColumn = "price_list_id";
			$isWhere = array("price_list_status = 0 and company_id = ".$_SESSION['company_id']."");
			$sTable = "tbl_price_list";			
			$isJOIN = array();
			$hOrder = "price_list_id desc";
			include($path.'include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {

				$edit_btn='';$print_btn='';$del_btn='';
				
				$view_btn='<a class="btn btn-xs btn-primary" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'price_list_view/'.$row['price_list_id'].'"><i class="fa fa-eye"></i></a>';
				
				
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'price_list_edit/'.$row['price_list_id'].'"><i class="fa fa-pencil"></i></a>';
				
				
				
				
				if($row['version_relase']==1)
				{
					$release_btn='<button class="btn btn-xs btn-danger" data-original-title="un-Relase" data-toggle="tooltip" data-placement="top" onClick="unrelase_version('.$row['price_list_id'].')"><i class="fa fa-refresh"></i></button>';

					$status = "<a class='btn btn-success'>Released</a>";
				}
				else
				{

					$release_btn='<button class="btn btn-xs btn-success" data-original-title="Relase" data-toggle="tooltip" data-placement="top" onClick="relase_version('.$row['price_list_id'].')"><i class="fa fa-refresh"></i></button>';

					$status = "<a class='btn btn-danger'>Not Released</a>";	
				}

				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = date("d/m/Y",strtotime($row['price_list_effective_date']));
				$row_data[] = date("d/m/Y",strtotime($row['price_list_expire_date']));
				$row_data[] = $row['price_list_version'];
				$row_data[] = $status;
				$row_data[] = $view_btn." ".$edit_btn." ".$release_btn;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
	
else if(strtolower($POST['mode']) == "add") {                

          

	        $info['branch_id']			= $POST['branch_id'];
	        $info['price_list_effective_date']		= date('Y-m-d',strtotime($POST['effective_date']));
	        $info['price_list_expire_date']	= date('Y-m-d',strtotime($POST['expiry_date']));
	        $info['price_list_version'] 			= $POST['price_version']; //Added new by dhruv
	        $info['price_list_allocate_type'] 			= $POST['relase_version_to']; //Added new by dhruv
	        $info['price_list_allocate_to'] 			= implode(",",$POST['relase_version']); //Added new by dhruv
	       
	        $info['cdate']				= date("Y-m-d H:i:s");
	        $info['user_id']			= $_SESSION['user_id'];
	        $info['company_id']			= $_SESSION['company_id'];
	        $info['usertype_id']		= $_SESSION['usertype_id'];
			
			//print_r($POST);exit;
			
	        $inserinvoiceid=add_record('tbl_price_list',$info,$dbcon);
			
			if($inserinvoiceid)
			{
				$inv_trn['price_list_id']=$inserinvoiceid;

				$updatetrnid=update_record('tbl_price_list_details',$inv_trn,"user_id=".$_SESSION['user_id']." and price_list_id=0 " , $dbcon);
			}
			
			if($inserinvoiceid)
			{
				$arr['eid']=$inserinvoiceid;	
				$arr['msg']=1;
			}
			else
			{
				$arr['msg']=0;
			}
			
			echo json_encode($arr);	


		}		

else if(strtolower($POST['mode']) == "edit") {  

	$info['branch_id']			= $POST['branch_id'];
    $info['price_list_effective_date']		= date('Y-m-d',strtotime($POST['effective_date']));
    $info['price_list_expire_date']	= date('Y-m-d',strtotime($POST['expiry_date']));
    $info['price_list_version'] 			= $POST['price_version']; //Added new by dhruv
    $info['price_list_allocate_type'] 			= $POST['relase_version_to']; //Added new by dhruv
    $info['price_list_allocate_to'] 			= implode(",",$POST['relase_version']); //Added new by dhruv
   
    $info['cdate']				= date("Y-m-d H:i:s");
    $info['user_id']			= $_SESSION['user_id'];
    $info['company_id']			= $_SESSION['company_id'];
    $info['usertype_id']		= $_SESSION['usertype_id'];
	
	//print_r($POST);exit;
	
   $updateid=update_record('tbl_price_list',$info,"price_list_id=".$POST['eid'] , $dbcon, $POST['branch_id']);
	
	
	if($updateid)
	{
		$arr['eid']=$updateid;	
		$arr['msg']=1;
	}
	else
	{
		$arr['msg']=0;
	}
	
	echo json_encode($arr);	

}
		   
	else if(strtolower($POST['mode']) == "relase_version") {  

		$id= $POST['id'];

		$update = $dbcon->query("update tbl_price_list set version_relase='1' where  price_list_id='$id'");

		echo $update;

	}
	else if(strtolower($POST['mode']) == "unrelase_version") {  

		$id= $POST['id'];

		$update = $dbcon->query("update tbl_price_list set version_relase='0' where  price_list_id='$id'");

		echo $update;

	}	
	else if(strtolower($POST['mode']) == "get_group_customer") {  

		$type= $POST['type'];
		
		$eid = $POST['eid'];
		$sel = $dbcon->query("select price_list_allocate_to from tbl_price_list where price_list_id='$eid'");
		$row = brp_mysqli_fetch_array($sel);

		if($type==0)
		{
			$row1['result']=get_all_groups($dbcon,$row['price_list_allocate_to']);
		}
		else
		{
			$row1['result']=f_get_group_ledger($dbcon,'37,38',$row['price_list_allocate_to'],'');
		}

		$row1['edit_id'] = json_encode(explode(",",$row['price_list_allocate_to']));

		echo json_encode($row1);
	}	
	function calculate_pro_base_qty($dbcon, $parent_id, $child_id, $parent_qty){

				$parentsql = "SELECT * FROM `tbl_bom` where bom_status!=2 and bom_product = '".$parent_id."' ";
				$parentrows=brp_mysqli_fetch_assoc($dbcon->query($parentsql));

				$childsql = "SELECT * FROM `tbl_bomtrn` where bom_trn_status!=2 and product_id = '".$child_id."' and bom_id = '".$parentrows['bom_id']."' ";
				$childrows=brp_mysqli_fetch_assoc($dbcon->query($childsql));

				$parent_base_qty = $parentrows['product_base_qty'];
				$child_base_qty = $childrows['product_base_qty'];


 		//$single_qty = floatval($child_base_qty) / floatval($parent_base_qty);
				$single_qty = $child_base_qty/$parent_base_qty;

				$req_qty = $single_qty * $parent_qty;

 		//return number_format($req_qty,3,'.','');
				return $req_qty;

			}



function calculate_pro_conv_qty($dbcon, $parent_id, $child_id, $parent_qty){
	$parentsql = "SELECT * FROM `tbl_bom` where bom_status!=2 and bom_product = '".$parent_id."' ";
	$parentrows=brp_mysqli_fetch_assoc($dbcon->query($parentsql));

	$childsql = "SELECT * FROM `tbl_bomtrn` where bom_trn_status!=2 and product_id = '".$child_id."' and bom_id = '".$parentrows['bom_id']."' ";
	$childrows=brp_mysqli_fetch_assoc($dbcon->query($childsql));

	$parent_base_qty = $parentrows['product_conv_qty'];
	$child_base_qty = $childrows['product_conv_qty'];

//$single_qty = floatval($child_base_qty) / floatval($parent_base_qty);
	$single_qty = $child_base_qty / $parent_base_qty;

	$req_qty = $single_qty * $parent_qty;

//return number_format($req_qty,3,'.','');
	return $req_qty;

}