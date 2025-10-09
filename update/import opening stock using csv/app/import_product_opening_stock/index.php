<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/common_functions.php");
include("../../include/function_database_query.php"); 

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		 
		$where='';
		if($POST['party_type']){
			$where.=' and cust.party_type='.$POST['party_type'];
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('cust_id', 'cc.cc_name', 'party_type', 'cust_name', 'cust_email', 'cust_mobile', 'cust_gst', 'cust_status','cust.cdate','cust.user_id');
		$sIndexColumn = "cust_id";
		$isWhere = array("cust_status = 0 ".$where."  and cust.company_id in (0,$_SESSION[company_id])");
		$sTable = " tbl_customer as cust";			
		$isJOIN = array('left join  tbl_customer_category as cc on cc.cc_id=cust.cust_cat');
		$hOrder = "cust.cust_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			
			/*if($row['party_type']=='1'){
				$row_data[] = "Customer";
			}
			else if($row['party_type']=='2'){
				$row_data[] = "Vendor";
			}
			else{
				$row_data[] = "Both";
			}*/
			
			$row_data[] = $row['cc_name'];
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['cust_email'];
			$row_data[] = $row['cust_mobile'];
			$row_data[] = $row['cust_gst'];
			
			
			$edit_btn=''; $delete_btn=''; 
			
			$view_cust_btn=' <a class="btn btn-xs btn-info" data-original-title="View Customer" data-toggle="tooltip" data-placement="top" href="'.ROOT.'customer_view/'.$row['cust_id'].'"><i class="fa fa-eye"></i></a>';
			
			if($edit_btn_per){ 
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'customeraddedit/'.$row['cust_id'].'"><i class="fa fa-pencil"></i></a>'; 
			}
			if($delete_btn_per){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_cust('.$row['cust_id'].')"><i class="fa fa-trash-o"></i></button>'; 
			}
			
			$row_data[] = $printcheckbox.' '.$edit_btn.' '.$delete_btn;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}else if(strtolower($POST['mode']) == "check_data"){
				$row[] ='';
				if(!empty($_FILES['excel_file']['tmp_name']))
				{
					$file_name = $_FILES['excel_file']['name'];
					$err = $_FILES["excel_file"]["tmp_name"];
					$exts = array('csv'); 
					if(in_array(end(explode('.', $file_name)), $exts))
					{
						move_uploaded_file($err,CUSTOMER_UPING.$file_name);
						$handle = fopen(CUSTOMER_UPING.$file_name, "r");
						$row = check_data($file_name,$dbcon);
					}
					else
					{
						$row['res'] = "-1";
					}
			}
			else
				$row['res'] ='0';
				echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "import_data"){
				if(!empty($_FILES['excel_file']['tmp_name']))
				{
					$file_name = $_FILES['excel_file']['name'];
					$err = $_FILES["excel_file"]["tmp_name"];
					move_uploaded_file($err,CUSTOMER_UPING.$file_name);
					$handle = fopen(CUSTOMER_UPING.$file_name, "r");
					($data = fgetcsv($handle,","));//get field rows
					$i=1;$error_array=array();
					while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
					{
						$error='';
						if(!empty($data['0']))
						{
							$csv_product_name	=$data['0'];
							$csv_branch_name	=$data['1'];
							$csv_godown_name=$data['2'];
							$csv_base_unit		=$data['3'];
							$csv_base_stock		=$data['4'];
							$csv_conv_unit		=$data['5'];
							$csv_conv_stock		=$data['6'];
							
							if(!empty($csv_product_name)){
								$qstate="SELECT `product_id`,`product_name` FROM `product_mst` WHERE product_status=0 and `product_name` ='".$csv_product_name."'";
								$tr_state = brp_mysqli_fetch_array($dbcon -> query($qstate));
								if(!empty($tr_state))
								{
									$csv_product_id=$tr_state['product_id'];
								}				
								else
								{
									$error='Product Not Found';
									array_push($error_array,1);
								}
							}else{
								$error='Product Name Not Add In Excel File';
								array_push($error_array,1);
							}
							
							if(!empty($csv_godown_name)){
								$qstate_1="SELECT `gd_id`,`gd_name` FROM `mst_godown` WHERE g_status=0 and `gd_name`='".$csv_godown_name."'";
								$tr_state_1 = brp_mysqli_fetch_array($dbcon -> query($qstate_1));
								if(!empty($tr_state_1))
								{
									$csv_godown_id=$tr_state_1['gd_id'];
								}				
								else
								{
									$error='Godown Not Found';
									array_push($error_array,1);
								}
							}else{
								$error='Godown Name Not Add In Excel File';
								array_push($error_array,1);
							}
							
							if(!empty($csv_base_unit)){
								$qstate_2="SELECT `unitid`,`unit_name` FROM `unit_mst` WHERE unit_status=0 and `unit_name` ='".$csv_base_unit."'";
								$tr_state_2 = brp_mysqli_fetch_array($dbcon -> query($qstate_2));
								if(!empty($tr_state_2))
								{
									$csv_base_unit_id=$tr_state_2['unitid'];
								}				
								else
								{
									$error='Base Unit Not Found';
									array_push($error_array,1);
								}
							}else{
								$error='Base Unit Name Not Add In Excel File';
								array_push($error_array,1);
							}
							
							/* if(!empty($csv_conv_unit)){
								$qstate_3="SELECT `unitid`,`unit_name` FROM `unit_mst` WHERE unit_status=0 and `unit_name` ='".$csv_conv_unit."'";
								$tr_state_3 = brp_mysqli_fetch_array($dbcon -> query($qstate_3));
								if(!empty($tr_state_3))
								{
									$csv_base_unit_id=$tr_state_3['unitid'];
								}				
								else
								{
									$error='Conv Unit Not Found';
									array_push($error_array,1);
								}
							}else{
								$error='Conv Unit Name Not Add In Excel File';
								array_push($error_array,1);
							} */
							
							if(!empty($csv_branch_name)){
								$qstate_4="SELECT `branch_id`,`branch_name` FROM `branch_mst` WHERE unit_status=0 and `branch_name` ='".$csv_branch_name."'";
								$tr_state_4 = brp_mysqli_fetch_array($dbcon -> query($qstate_4));
								if(!empty($tr_state_4))
								{
									$csv_branch_id=$tr_state_3['branch_id'];
								}				
								else
								{
									$csv_branch_id="10000";
								}
							}else{
								$csv_branch_id="10000";
							}
							
							
							$q=$dbcon->query("select branch_id,product_id from tbl_branch_product_stock where branch_id='".$csv_godown_id."' and product_id='".$csv_product_id."'");
							$count=brp_mysqli_num_rows($q);
							$roq=brp_mysqli_fetch_assoc($q);
							
									$info['product_stock']	=$csv_base_stock;
									$info['branch_id']		=$csv_godown_id;
									$info['priority']		="0";
									$info['user_id']		=$_SESSION['user_id'];
									$info['cdate']			=date("Y-m-d h:i:s");
									$info['company_id']		=$_SESSION['company_id'];
									
									$info['product_id']=$csv_product_id;
							//var_dump($info);
							$table='tbl_branch_product_stock';$tableid='branch_product_stock_id';
							
							if(!empty($info['product_stock'])){
								if($info['product_stock']!="0.00"){
									$inserid=add_record($table, $info, $dbcon);
								}
							}
								$ref_id=$inserid;
							
							$date1=date("Y-m-d");
							$ref_name="opening_stock";
							$info_st['stock_status']=2;
								$updateid_stock=update_record("tbl_stock_trn", $info_st,"godown_id=".$info['branch_id']." and product_id=".$csv_product_id." and ref_name='".$ref_name."'" , $dbcon);
							if(!empty($info['product_stock'])){
								if($info['product_stock']!="0.00"){
									add_stock($dbcon,$csv_product_id,$csv_base_unit_id,$date1,$ref_name,$ref_id,$info['branch_id'],$info['product_stock'],1,$csv_branch_id);
								}
							}
							
							
					/* $q="SELECT `cust_name`,`company_name` FROM `tbl_customer` WHERE cust_status=0 and `company_id` ='".$_SESSION['company_id']."' and `company_name` ='".$info['company_name']."' ";
							$tr = $dbcon -> query($q);
							$cnt=mysqli_num_rows($tr);
							if($cnt>0 ) {
								$error='Company Already Added';
								array_push($error_array,1);
							}
							else if(!empty($error))
							{
								$err='error';
								array_push($error_array,1);
							}
							else
							{
								add_record('tbl_customer', $info, $dbcon);
							} */
							 
						}
						else
						{
							$error='Blank Row';
							array_push($error_array,1);
						}
						if(!empty($error))
						{
								
								$info1['line_num']=$i;
								$info1['error']=$error;
								$info1['company_id']=$_SESSION['company_id'];
								add_record('cust_tempdata', $info1, $dbcon);
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
			{$result['res']='0';}
			echo  json_encode($result);
		}
		else if(strtolower($POST['mode']) == "show_importedcustdata") {
			$temp_custqry='select * from cust_tempdata where company_id='.$_SESSION['company_id'];
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
	$arr = explode(".", $filename);
	$fp = fopen(CUSTOMER_UPING.$filename, 'r');
	$frow = fgetcsv($fp);
	if(count($frow)==5) // Define coulmn count Here
	{
		$msg='';
		foreach($frow as $i)
		if ( !in_array($i, array('Product Name','Branch Name','Godown Name','Unit','Stock'), true ) ) 
		{
			$msg='error';
		}
		
		if(!empty($msg))
		{
			$error['res']="3";
		}
		else
		{
			delete_record('cust_tempdata', 'company_id='.$_SESSION['company_id'], $dbcon);
			$error['res']="1";
		}
	}
	else
	{
		$error['res']="0";
	}
	//$error['res']=count($frow);
	return $error;
	
}
?>