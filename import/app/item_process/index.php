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
				move_uploaded_file($err,PRODUCT_PROCESS_UPING.$file_name);
				$handle = fopen(PRODUCT_PROCESS_UPING.$file_name, "r");
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

							$process = "select process_id,process_name from process_mst where process_status=0 and company_id=".$_SESSION['company_id']." and process_name='".$data['1']."'";
							$process_r = mysqli_fetch_array($dbcon -> query($process));

							if(!empty($process_r))
							{
								$info['process_id']			= $process_r['process_id'];
							}else{
								$error='Process Not Found';
								array_push($error_array,1);
							}

							$info['process_priority']		= $data['2'];

							if($data['3'] == 'Inhouse' || $data['3'] == 'Outside'){
								$info['process_type']			= $data['3'];
							}else{
								$info['process_type']	= 'Inhouse';
							}

							$info['process_rate']		= $data['4'];
							$info['process_time']		= $data['5'];

							$resource = "select resource_id,resource_name from tbl_resource where resource_status=0 and company_id=".$_SESSION['company_id']." and resource_name='".$data['6']."'";

							$resource_r = mysqli_fetch_array($dbcon -> query($resource));
							
							if($data['3'] == 'Outside'){
								$info['resource_id']			= "";
							}else{
								if(!empty($resource_r))
								{
									$info['resource_id']			= $resource_r['resource_id'];
								}else{
									$error='Resource Not Found';
									array_push($error_array,1);
								}
							}
													
							

							$info['process_loss']		= $data['7'];

							$info['process_scrap_tolerance_plus']= $data['8'];

							$info['process_scrap_tolerance_minus']= $data['9'];
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
							add_record('product_process_tempdata', $info1, $dbcon);
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
		unlink(PRODUCT_PROCESS_UPING.$_FILES['excel_file']['name']);
		if(!empty($_FILES['excel_file']['tmp_name']))
		{	
			$file_name = $_FILES['excel_file']['name'];
			$err = $_FILES["excel_file"]["tmp_name"];
			$temp = explode(".", $_FILES["excel_file"]["name"]);
			$dt=date("Y_m_d_h_i_sa");
			$extension = strtolower(end($temp));
			$ile1 = "product_process_data_".$dt.".".$extension;
			move_uploaded_file($err,PRODUCT_PROCESS_UPING.$ile1);
			
			$handle = fopen(PRODUCT_PROCESS_UPING.$ile1, "r");
			($data = fgetcsv($handle,","));//get field rows
			$i=1;$error_array=array();
			while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
			{
				$error='';
				if(!empty($data['0']))
				{
					$product = "select product_id,product_name from product_mst where company_id=".$_SESSION['company_id']." and product_status !=2 and product_name='".$data['0']."'";
					// /var_dump($product);
					$product_r = mysqli_fetch_array($dbcon -> query($product));

					if(!empty($product_r))
					{
						$info['product_id']			= $product_r['product_id'];
					}

					$process = "select process_id,process_name from process_mst where process_status=0 and company_id=".$_SESSION['company_id']." and process_name='".$data['1']."'";
					$process_r = mysqli_fetch_array($dbcon -> query($process));

					if(!empty($process_r))
					{
						$info['process_id']			= $process_r['process_id'];
					}

					$info['process_priority']		= $data['2'];

					if($data['3'] == 'Inhouse' || $data['3'] == 'Outside'){
						if($data['3'] == 'Inhouse'){
							$info['process_type']			= 1;
						}else{
							$info['process_type']			= 2;
						}
					}else{
						$info['process_type']	= 1;
					}

					$info['process_rate']		= $data['4'];
					$info['process_time']		= $data['5'];

					$resource = "select resource_id,resource_name from tbl_resource where resource_status=0 and company_id=".$_SESSION['company_id']." and resource_name='".$data['6']."'";
					
					$resource_r = brp_mysqli_fetch_array($dbcon -> query($resource));
					if(!empty($resource_r['resource_id']))
					{
						$info['resource_id']			= $resource_r['resource_id'];
					}else{
						$info['resource_id']			= "";
					}

					$info['process_loss']		= $data['7'];

					$info['process_scrap_tolerance_plus']= $data['8'];

					$info['process_scrap_tolerance_minus']= $data['9'];

					$info['cdate']			= date("Y-m-d H:i:s");
					$info['user_id']		= $_SESSION['user_id'];
					$info['company_id']		= $_SESSION['company_id'];
					//var_dump($info);
					$qpro="SELECT `product_id`,`process_id`,`pr_process_id` FROM `tbl_product_process` WHERE status = 0 and `company_id` = ".$_SESSION['company_id']." and `product_id` = '".$info['product_id']."' and `process_id`='".$info['process_id']."' and process_type='".$info['process_type']."' and company_id=".$_SESSION['company_id'];
					$tr_pro = $dbcon -> query($qpro);
					$cnt=mysqli_num_rows($tr_pro);
					$tr_prod = mysqli_fetch_array($tr_pro);
					
					
					
					if($cnt>0 ) {
						update_record('tbl_product_process', $info,"pr_process_id=". $tr_prod['pr_process_id'] , $dbcon);
					}
					else
					{
						add_record('tbl_product_process', $info, $dbcon);
					}

					$qcq = "select * from tbl_product_parameter where product_id=".$info['product_id'];
					$tr_qcq = $dbcon -> query($qcq);
					$cnt12=brp_mysqli_num_rows($tr_qcq);
					
					if($cnt12>0){
						$qc_pro_para['product_setting_check'] = 'process_product,product_qc';
					}else{
						$qc_pro_para['product_setting_check'] = 'process_product';
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
		$temp_custqry='select * from product_process_tempdata where company_id='.$_SESSION['company_id'];
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
	$fp 	= fopen(PRODUCT_PROCESS_UPING.$filename, 'r');
	$frow 	= fgetcsv($fp);
	if(count($frow)==10) // Define coulmn count Here
	{
		$msg='';
		if ( $frow[0] !== 'Product Name' || $frow[1] !== 'Process Name' || $frow[2] !== 'Priority' || $frow[3] !== 'Type' || $frow[4] !== 'Rate' || $frow[5] !== 'Time' || $frow[6] !== 'Resource Name' || $frow[7] !== 'Loss(%)' || $frow[8] !== 'Scrap(+)' || $frow[9] !== 'Scrap(-)')
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
			delete_record('product_process_tempdata', 'company_id='.$_SESSION['company_id'], $dbcon);
		}
	}
	else
	{
		$error['res']="0";
	}
	return $error;
}
?>