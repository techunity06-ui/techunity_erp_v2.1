<?php 
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
$getspecialConfiguration=getspecialConfiguration($dbcon);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

	if(strtolower($POST['mode']) == "check_data")
	{
		$row[] ='';

		if(!empty($_FILES['excel_file']['tmp_name']))
		{
			$file_name = $_FILES['excel_file']['name'];
			$err = $_FILES["excel_file"]["tmp_name"];
			$exts = array('csv'); 
			if(in_array(end(explode('.', $file_name)), $exts))
			{
				move_uploaded_file($err,PRODUCT_UPING.$file_name);
				$handle = fopen(PRODUCT_UPING.$file_name, "r");
				$row = check_data($file_name,$dbcon);
				
				($data = fgetcsv($handle,","));
				$i=1;$error_array=array();
				if($row['res']!='0'){
					while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
					{
						$error='';
						if(!empty($data['0']))
						{
							$pro_type = "select product_type_id,product_type_name from pro_ms_product_type where company_id=".$_SESSION['company_id']." and product_type_status=0 and product_type_name='".$data['0']."'";

							$tr_pty = mysqli_fetch_array($dbcon -> query($pro_type));
				
							if(!empty($tr_pty))
							{
								$info['product_type']			= $tr_pty['product_type_id'];
							}else{
								$error='Product Type Not Found';
								array_push($error_array,1);
							}
							
							if(!empty($data['1'])){
								$pro_cat = "select cat_id,cat_name from tbl_category where cat_status=0 and company_id=".$_SESSION['company_id']." and cat_name='".$data['1']."'";
								$tr_cat = mysqli_fetch_array($dbcon -> query($pro_cat));
								if(!empty($tr_cat))
								{
									$info['product_category']		= $tr_cat['cat_id'];
								}else if(strtolower($data['1']) == 'primary'){
									$info['product_category']		= 0;
								}else{
									$error='Product Category Not Found';
									array_push($error_array,1);
								}
							}
							
							
							$pro_icode = "select product_icode from product_mst where company_id=".$_SESSION['company_id']." and product_status=0 and product_icode='".$data['2']."'";

							$tr_code = mysqli_fetch_array($dbcon -> query($pro_icode));
							if(!empty($tr_code))
							{
								$error='Product Item Code Already Exist';
								array_push($error_array,1);
							}else{
								$info['product_icode']			= $data['2'];
							}
							$where="";
							if($getspecialConfiguration['chemitek_permission']==1){
								$where = ' and product_icode='.$info['product_icode'];
							}
							$pro_name = "select product_name from product_mst where company_id=".$_SESSION['company_id']." and product_status=0 and product_name='".$data['3']."' and product_type = '".$tr_pty['product_type_id']."' AND `product_category`='".$info['product_category']."' ".$where;

							$tr_pname = mysqli_fetch_array($dbcon -> query($pro_name));

							if(!empty($tr_pname))
							{
								$error='Product Name Already Exist';
								array_push($error_array,1);
							}else{
								$info['product_name']			= $data['3'];
							}

							$pro_bran = "select branch_id,branch_name from branch_mst where branch_status=0 and company_id=".$_SESSION['company_id']." and branch_name='".$data['4']."'";

							$tr_bran = mysqli_fetch_array($dbcon -> query($pro_bran));

							if(!empty($tr_bran))
							{
								$info['branch_id']				= $tr_bran['branch_id'];
							}else{
								$error='Branch Not Found';
								array_push($error_array,1);
							}

							$pro_hsn = "select hsn_id,hsn_code from mst_hsn_code where hsn_status=0 and company_id=".$_SESSION['company_id']." and hsn_code='".$data['5']."'";
							
							$tr_hsn = mysqli_fetch_array($dbcon -> query($pro_hsn));
							if(!empty($tr_hsn))
							{
								$info['product_hsn']			= $tr_hsn['hsn_id'];
							}else{
								$error='HSN Code Not Found';
								array_push($error_array,1);
							}
							
							$pro_bunit = "select unitid,unit_name from unit_mst where unit_status=0 and unit_name='".$data['6']."'";
							$tr_bunit = mysqli_fetch_array($dbcon -> query($pro_bunit));

							if(!empty($tr_bunit))
							{
								$info['product_base_unit']		= $tr_bunit['unitid'];
							}else{
								$error='Base Unit Not Found';
								array_push($error_array,1);
							}
							
							if(!empty($data['7'])){
								$info['product_base_qty']		= $data['7'];
							}else{
								$error='Base Qty Not Found';
								array_push($error_array,1);
							}

							$pro_cunit = "select unitid,unit_name from unit_mst where unit_status=0 and unit_name='".$data['8']."'";
							$tr_cunit = mysqli_fetch_array($dbcon -> query($pro_cunit));

							if(!empty($tr_cunit))
							{
								$info['product_conv_unit']		= $data['8'];
							}else{
								$error='Conv Unit Not Found';
								array_push($error_array,1);
							}

							if(!empty($data['9'])){
								$info['product_conv_qty']		= $data['9'];
							}else{
								$error='Conv Qty Not Found';
								array_push($error_array,1);
							}
							if(!empty($data['10'])){
								$pro_material_speci = "select ms_id,ms_name from mst_material_spec where ms_status=0 and company_id=".$_SESSION['company_id']." and ms_name='".$data['10']."' ";
								$tr_pro_material_speci = mysqli_fetch_array($dbcon -> query($pro_material_speci));
								if(!empty($tr_pro_material_speci))
								{
									$info['product_specification']	= $tr_pro_material_speci['ms_id'];
								}else{
									$error='Product Material Not Found';
									array_push($error_array,1);
								}	
							}
							

							
							$info['product_opening_valuation']= $data['11'];
							$info['product_barcode']		= $data['12'];
							$info['product_net_weight']		= $data['13'];
							$info['product_making_time']	= $data['14'];
							$info['product_lead_time']		= $data['15'];

							if($data['16'] == 'including' || $data['16'] == 'excluding')
							{
								$info['product_gst']			= $data['16'];
							}else{
								$info['product_gst']			= 'excluding';
							}
							
							$info['product_sale_rate']		= $data['17'];
							$info['product_purchase_rate']	= $data['18'];
							$info['weight']					= $data['19'];
							$info['product_min_stock']		= $data['20'];
							$info['product_max_stock']		= $data['21'];

							if($data['22'] == 'yes' || $data['22'] == 'no'){
								$info['is_grn']					= $data['22'];
							}else{
								$info['is_grn']					= 'yes';
							}
							
							$info['reorder_qty']			= $data['23'];
							$info['self_life_days']			= $data['24'];
							$info['warrenty_period']		= $data['25'];
							$info['model_no']				= $data['26'];


							$info['item_type']				= '';

							if(!empty($data['28'])){
								$pro_ma_c = "select gd_id,gd_name from mst_godown where g_status=0 and company_id=".$_SESSION['company_id']." and gd_name='".$data['28']."'";
								$tr_pro_ma = mysqli_fetch_array($dbcon -> query($pro_ma_c));
								if(!empty($tr_pro_ma))
								{
									$info['product_mat_center']		= $tr_pro_ma['gd_id'];
								}else{
									$error='Product Material Center Not Found';
									array_push($error_array,1);
								}
							}
							

							if($data['29'] == 'yes' || $data['29'] == 'no'){
								$info['product_stock_count']	= $data['29'];
							}else{
								$info['product_stock_count']	= 'yes';
							}

							if($data['30'] == 'yes' || $data['30'] == 'no'){
								$info['bom_required']			= $data['30'];
							}else{
								$info['bom_required']	= 'yes';
							}
							
							
							$info['item_status']			= '';

							if($data['32'] == 'yes' || $data['32'] == 'no'){
								$info['batch_wise_stock_manage']= $data['32'];
							}else{
								$info['batch_wise_stock_manage']	= 'no';
							}
						}
						else
						{
							$error='Blank Row';
							array_push($error_array,1);
						}
						if(!empty($error))
						{
							$info1['line_num']=$i+1;
							$info1['error']=$error;
							$info1['company_id']=$_SESSION['company_id'];
							add_record('product_tempdata', $info1, $dbcon);
						}
						$i++;
					}
					if(in_array(1,$error_array))
					{
						$row['res']='5';
					}
					else
					{
						$row['res']='1';
					}	
					fclose($handle);
				}else{
					$row['res'] ='0';
				}
			}
			else
			{
				$row['res'] = "-1";
			}
		}
		else
		{
			$row['res'] ='0';
		}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode']) == "import_data"){
		unlink(PRODUCT_UPING.$_FILES['excel_file']['name']);
		if(!empty($_FILES['excel_file']['tmp_name']))
		{	
			$file_name = $_FILES['excel_file']['name'];
			$err = $_FILES["excel_file"]["tmp_name"];
			$temp = explode(".", $_FILES["excel_file"]["name"]);
			$dt=date("Y_m_d_h_i_sa");
			$extension = strtolower(end($temp));
			$ile1 = "product_data_".$dt.".".$extension;
			move_uploaded_file($err,PRODUCT_UPING.$ile1);
			
			$handle = fopen(PRODUCT_UPING.$ile1, "r");
			($data = fgetcsv($handle,","));//get field rows
			$i=1;$error_array=array();
			while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
			{
				$error='';
				if(!empty($data['0']))
				{
					$pro_type = "select product_type_id,product_type_name from pro_ms_product_type where company_id=".$_SESSION['company_id']." and product_type_status=0 and product_type_name='".$data['0']."'";

						$tr_pty = mysqli_fetch_array($dbcon -> query($pro_type));
			
						if(!empty($tr_pty))
						{
							$info['product_type']			= $tr_pty['product_type_id'];
						}
						
						$pro_cat = "select cat_id,cat_name from tbl_category where cat_status=0 and company_id=".$_SESSION['company_id']." and cat_name='".$data['1']."'";
						$tr_cat = mysqli_fetch_array($dbcon -> query($pro_cat));
						if(!empty($tr_cat))
						{
							$info['product_category']		= $tr_cat['cat_id'];
						}else if(strtolower($data['1']) == 'primary'){
							$info['product_category']		= 0;
						}
						
						$info['product_icode']			= $data['2'];
						
						$info['product_name']			= $data['3'];
					

						$pro_bran = "select branch_id,branch_name from branch_mst where branch_status=0 and company_id=".$_SESSION['company_id']." and branch_name='".$data['4']."'";

						$tr_bran = mysqli_fetch_array($dbcon -> query($pro_bran));

						if(!empty($tr_bran))
						{
							$info['branch_id']				= $tr_bran['branch_id'];
						}

						$pro_hsn = "select hsn.hsn_id,hsn.hsn_code,tax.tax_gst from mst_hsn_code as hsn
						left join tbl_tax_category as tax on tax.tax_cat_id = hsn.sale_gst
						 where hsn.hsn_status=0 and hsn.company_id=".$_SESSION['company_id']." and hsn.hsn_code='".$data['5']."'";
						// var_dump($pro_hsn);

						$tr_hsn = mysqli_fetch_array($dbcon -> query($pro_hsn));
						if(!empty($tr_hsn))
						{
							$info['product_hsn']			= $tr_hsn['hsn_id'];
							$info['product_sale_gst']		= $tr_hsn['tax_gst'];
							$info['product_purchase_gst']	= $tr_hsn['tax_gst'];
						}
						
						$pro_bunit = "select unitid,unit_name from unit_mst where unit_status=0 and unit_name='".$data['6']."'";
						$tr_bunit = mysqli_fetch_array($dbcon -> query($pro_bunit));

						if(!empty($tr_bunit))
						{
							$info['product_base_unit']		= $tr_bunit['unitid'];
						}
					
						$info['product_base_qty']		= $data['7'];

						$pro_cunit = "select unitid,unit_name from unit_mst where unit_status=0 and unit_name='".$data['8']."'";
						$tr_cunit = mysqli_fetch_array($dbcon -> query($pro_cunit));

						if(!empty($tr_cunit))
						{
							$info['product_conv_unit']		= $tr_cunit['unitid'];
						}

						$info['product_conv_qty']		= $data['9'];
						
						$pro_material_speci = "select ms_id,ms_name from mst_material_spec where ms_status=0 and company_id=".$_SESSION['company_id']." and ms_name='".$data['10']."' ";
						$tr_pro_material_speci = mysqli_fetch_array($dbcon -> query($pro_material_speci));
						if(!empty($tr_pro_material_speci))
						{
							$info['product_specification']	= $tr_pro_material_speci['ms_id'];
						}

						
						$info['product_opening_valuation']= $data['11'];
						$info['product_barcode']		= $data['12'];
						$info['product_net_weight']		= $data['13'];
						$info['product_making_time']	= $data['14'];
						$info['product_lead_time']		= $data['15'];

						if(strtolower($data['16']) == 'including' || strtolower($data['16']) == 'excluding')
						{
							$info['product_gst']			= strtolower($data['16']);
						}else{
							$info['product_gst']			= 'excluding';
						}
						
						$info['product_sale_rate']		= $data['17'];
						$info['product_purchase_rate']	= $data['18'];
						$info['weight']					= $data['19'];
						$info['product_min_stock']		= $data['20'];
						$info['product_max_stock']		= $data['21'];

						if(strtolower($data['22']) == 'yes' || strtolower($data['22']) == 'no'){
							$info['is_grn']					= strtolower($data['22']);
						}else{
							$info['is_grn']					= 'yes';
						}
						
						$info['reorder_qty']			= $data['23'];
						$info['self_life_days']			= $data['24'];
						$info['warrenty_period']		= $data['25'];
						$info['model_no']				= $data['26'];


						$info['item_type']				= '';

						$pro_ma_c = "select gd_id,gd_name from mst_godown where g_status=0 and company_id=".$_SESSION['company_id']." and gd_name='".$data['28']."'";
						$tr_pro_ma = mysqli_fetch_array($dbcon -> query($pro_ma_c));
						if(!empty($tr_pro_ma))
						{
							$info['product_mat_center']		= $tr_pro_ma['gd_id'];
						}

						if(strtolower($data['29']) == 'yes' || strtolower($data['29']) == 'no'){
							$info['product_stock_count']	= strtolower($data['29']);
						}else{
							$info['product_stock_count']	= 'yes';
						}

						if(strtolower($data['30']) == 'yes' || strtolower($data['30']) == 'no'){
							if(strtolower($data['30']) == 'yes'){
								$info['bom_required']			= 1;	
							}else{
								$info['bom_required']			= 0;
							}
							
						}else{
							$info['bom_required']	= '1';
						}
						
						
						$info['item_status']			= '';

						if(strtolower($data['32']) == 'yes' || strtolower($data['32']) == 'no'){
							if(strtolower($data['32']) == 'yes'){
								$info['batch_wise_stock_manage']			= 1;	
							}else{
								$info['batch_wise_stock_manage']			= 0;
							}
						}else{
							$info['batch_wise_stock_manage']	= 0;
						}

					$info['cdate']			= date("Y-m-d H:i:s");
					$info['user_id']		= $_SESSION['user_id'];
					$info['company_id']		= $_SESSION['company_id'];
					
					$where="";
					if($getspecialConfiguration['chemitek_permission']==1){
						$where = ' and product_icode='.$info['product_icode'];
					}

					$qpro="SELECT `product_name`,`product_id` FROM `product_mst` WHERE `product_status` = 0 and `company_id` = ".$_SESSION['company_id']." and `product_name` = '".$info['product_name']."' and  product_type	= '".$tr_pty['product_type_id']."' AND `product_category`='".$info['product_category']."' ".$where;
					$tr_pro = $dbcon -> query($qpro);
					$cnt=mysqli_num_rows($tr_pro);
					$tr_prod = mysqli_fetch_array($tr_pro);
					
					
					if($cnt>0 ) {
						//var_dump($cnt);
						update_record('product_mst', $info,"product_id=". $tr_prod['product_id'] , $dbcon);
					}
					else
					{
						add_record('product_mst', $info, $dbcon);
					}
				}
				else
				{
					$error='Blank Row';
					array_push($error_array,1);
				}
				$i++;
			}
			if(in_array(1,$error_array))
			{
				$result['res']='5';
			}
			else
			{
				$result['res']='4';
			}	
			fclose($handle);//close file reading
		}
		else
		{
			$result['res']='0';
		}
		echo json_encode($result);
	}
	else if(strtolower($POST['mode']) == "show_importedcustdata") {
		$temp_custqry='select * from product_tempdata where company_id='.$_SESSION['company_id'];
		$temp_result=$dbcon->query($temp_custqry);
		if(mysqli_num_rows($temp_result)>0)
		{
		echo '<table  class="display table table-bordered table-striped">
							<tr>
							<td>Line Number</td>
							<td>Error</td>
							</tr>';
		 
	 
		while($temp_rel=mysqli_fetch_assoc($temp_result))
		{
			echo '<tr>';
			echo '<td>'.$temp_rel['line_num'].'</td>'; 
			echo '<td>'.$temp_rel['error'].'</td>'; 
			echo '</tr>';
		}
			echo '</table>';
		}
	}
function check_data($filename,$dbcon)
{
	$error=array();
	//$qry="SELECT * FROM `productfield_mst` where status=0 and user_id=".$_SESSION['user_rid']." limit 2";
	//$row=$dbcon->query($qry);
	$arr 	= explode(".", $filename);
	$fp 	= fopen(PRODUCT_UPING.$filename, 'r');
	$frow 	= fgetcsv($fp);
	if(count($frow)==33) // Define coulmn count Here
	{
		$msg='';
		if ( $frow[0] !== 'Product Type' || $frow[1] !== 'Product Category' || $frow[2] !== 'Item Code' || $frow[3] !== 'Product Name' || $frow[4] !== 'Branch' || $frow[5] !== 'Hsn Code' || $frow[6] !== 'Base Unit' || $frow[7] !== 'Base Qty' || $frow[8] !== 'Conv Unit' || $frow[9] !== 'Conv Qty' || $frow[10] !== 'Product Material' || $frow[11] !== 'Product Valuation' || $frow[12] !== 'Product Barcode' || $frow[13] !== 'Net weight' || $frow[14] !== 'Making time' || $frow[15] !=='Lead time' || $frow[16] !=='Gst type' || $frow[17] !=='Sale rate' || $frow[18] !== 'Purchase Rate' || $frow[19] !== 'Weight' || $frow[20] !== 'Minimum stock' || $frow[21] !== 'Maximum stock' || $frow[22] !== 'Grn required' || $frow[23] !== 'Reorder qty' || $frow[24] !== 'Self life days' || $frow[25] !=='Warranty period' || $frow[26] !== 'Model no' || $frow[27] !== 'Item type' || $frow[28] !== 'Material Center' || $frow[29] !== 'Stock Count' || $frow[30] !== 'Bom required' || $frow[31] !== 'Item Status' || $frow[32] !== 'Batch wise stock manage')
		{
			$msg='error';
		}
		if(!empty($msg))
		{
			$error['res']="0";
		}
		else
		{
			$error['res']="1";
			delete_record('product_tempdata', 'company_id='.$_SESSION['company_id'], $dbcon);
		}
	}
	else
	{
		$error['res']="0";
	}
	return $error;
}
?>