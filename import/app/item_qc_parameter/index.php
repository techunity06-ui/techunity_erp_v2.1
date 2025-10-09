<?php 
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

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
				move_uploaded_file($err,PRODUCT_QC_UPING.$file_name);
				$handle = fopen(PRODUCT_QC_UPING.$file_name, "r");
				$row = check_data($file_name,$dbcon);
				
				($data = fgetcsv($handle,","));
				$i=1;$error_array=array();
				if($row['res']!='0'){
					while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
					{
						$error='';
						if(!empty($data['0']))
						{
							$product = "select product_id,product_name from product_mst where company_id=".$_SESSION['company_id']." and product_status !=2 and product_name='".$data['0']."'";
							$product_r = mysqli_fetch_array($dbcon -> query($product));
							if(!empty($product_r))
							{
								$info['product_id']			= $product_r['product_id'];
							}else{
								$error='Product Not Found';
								array_push($error_array,1);	
							} 

							$process = "select mst.*,p.process_name from tbl_product_process as mst 
								left join process_mst as p on p.process_id=mst.process_id
								where mst.status = 0 and  mst.product_id='".$info['product_id']."' and p.process_name='".$data['1']."'";
							$process_r = mysqli_fetch_array($dbcon -> query($process));;
							if(strtolower($data['1'])=='purchase'){
								$info['process_id']			= -1;
							}else{
								if(!empty($process_r))
								{
									$info['process_id']			= $process_r['process_id'];
								}else{
									$error='Process Not Found';
									array_push($error_array,1);	
								}
							}
							
							$parameter = "select p_id,p_name from tbl_qc_param where p_status=0 and company_id=".$_SESSION['company_id']." and p_name='".$data['2']."'";
							$parameter_r = mysqli_fetch_array($dbcon -> query($parameter));
							if(!empty($parameter_r)){
								$info['param_id']			= $parameter_r['p_id'];	
							}else{
								$error='Parameter Not Found';
								array_push($error_array,1);	
							}
							
							$info['param_value']		= $data['3'];
							$info['tolerance_plus']		= $data['4'];
							$info['tolerance_minus']	= $data['5'];

							$unit = "select unitid,unit_name from unit_mst where unit_status=0 and unit_name='".$data['6']."'";
							$unit_r = mysqli_fetch_array($dbcon -> query($unit));
							if(!empty($parameter_r)){
								$info['unit_id']			= $unit_r['unitid'];
							}else{
								$error='Unit Not Found';
								array_push($error_array,1);	
							}
							
							$info['cdate']				= date("Y-m-d H:i:s");
							$info['user_id']			= $_SESSION['user_id'];
							$info['company_id']			= $_SESSION['company_id'];
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
							add_record('product_qc_tempdata', $info1, $dbcon);
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
		unlink(PRODUCT_QC_UPING.$_FILES['excel_file']['name']);
		if(!empty($_FILES['excel_file']['tmp_name']))
		{	
			$file_name = $_FILES['excel_file']['name'];
			$err = $_FILES["excel_file"]["tmp_name"];
			$temp = explode(".", $_FILES["excel_file"]["name"]);
			$dt=date("Y_m_d_h_i_sa");
			$extension = strtolower(end($temp));
			$ile1 = "product_qc_parameter_data_".$dt.".".$extension;
			move_uploaded_file($err,PRODUCT_QC_UPING.$ile1);
			
			$handle = fopen(PRODUCT_QC_UPING.$ile1, "r");
			($data = fgetcsv($handle,","));//get field rows
			$i=1;$error_array=array();
			while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
			{
				$error='';
				if(!empty($data['0']))
				{
					$product = "select product_id,product_name from product_mst where company_id=".$_SESSION['company_id']." and product_status !=2 and product_name='".$data['0']."'";
					$product_r = mysqli_fetch_array($dbcon -> query($product));
					if(!empty($product_r))
					{
						$info['product_id']			= $product_r['product_id'];
					}else{
						$error='Product Not Found';
						array_push($error_array,1);	
					} 

					$process = "select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id
				where mst.status = 0 and  mst.product_id='".$info['product_id']."' and p.process_name='".$data['1']."'";
					$process_r = mysqli_fetch_array($dbcon -> query($process));
					if(strtolower($data['1'])=='purchase'){
						$info['process_id']			= -1;
					}else{
						if(!empty($process_r))
						{
							$info['process_id']			= $process_r['process_id'];
						}else{
							$error='Process Not Found';
							array_push($error_array,1);	
						}
					}
					

					$parameter = "select p_id,p_name from tbl_qc_param where p_status=0 and company_id=".$_SESSION['company_id']." and p_name='".$data['2']."'";
					$parameter_r = mysqli_fetch_array($dbcon -> query($parameter));
					if(!empty($parameter_r)){
						$info['param_id']			= $parameter_r['p_id'];	
					}else{
						$error='Parameter Not Found';
						array_push($error_array,1);	
					}
					
					$info['param_value']		= $data['3'];
					$info['tolerance_plus']		= $data['4'];
					$info['tolerance_minus']	= $data['5'];

					$unit = "select unitid,unit_name from unit_mst where unit_status=0 and unit_name='".$data['6']."'";
					$unit_r = mysqli_fetch_array($dbcon -> query($unit));
					if(!empty($parameter_r)){
						$info['unit_id']			= $unit_r['unitid'];
					}else{
						$error='Unit Not Found';
						array_push($error_array,1);	
					}
					
					$info['cdate']				= date("Y-m-d H:i:s");
					$info['user_id']			= $_SESSION['user_id'];
					$info['company_id']			= $_SESSION['company_id'];
					
					add_record('tbl_product_parameter', $info, $dbcon);
					
					$qcq = "select * from tbl_product_process where status = 0 and product_id=".$info['product_id'];
					$tr_qcq = $dbcon -> query($qcq);
					$cnt=mysqli_num_rows($tr_qcq);
					
					if($cnt>0){
						$qc_pro_para['product_setting_check'] = 'process_product,product_qc';
					}else{
						$qc_pro_para['product_setting_check'] = 'product_qc';
					}

					update_record('product_mst', $qc_pro_para,"product_id=". $info['product_id'] , $dbcon);
					
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
		$temp_custqry='select * from product_qc_tempdata where company_id='.$_SESSION['company_id'];
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
	$fp 	= fopen(PRODUCT_QC_UPING.$filename, 'r');
	$frow 	= fgetcsv($fp);
	if(count($frow)==7) // Define coulmn count Here
	{
		$msg='';
		if ( $frow[0] !== 'Product Name' || $frow[1] !== 'Process Name' || $frow[2] !== 'Parameter name' || $frow[3] !== 'Base value' || $frow[4] !== 'Tolerance(+)' || $frow[5] !== 'Tolerance(-)' || $frow[6] !== 'Unit')
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
			delete_record('product_qc_tempdata', 'company_id='.$_SESSION['company_id'], $dbcon);
		}
	}
	else
	{
		$error['res']="0";
	}
	return $error;
}
?>