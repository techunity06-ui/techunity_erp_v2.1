<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
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
		//$s_date=explode(' - ',$POST['date']);
		//$where.="  and cust.upload_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND cust.upload_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		$appData = array();
		$i=1;
		$aColumns = array('bom_temp_id', 'cc.product_name','upload_date', 'bom_temp_status','cust.cdate','cust.user_id');
		$sIndexColumn = "bom_temp_id";
		$isWhere = array("bom_temp_status = 0 and sr_no='main' ".$where."  and cust.company_id in (0,$_SESSION[company_id])");
		$sTable = " bom_temp as cust";			
		$isJOIN = array('left join  product_mst as cc on cc.product_id=cust.product_id');
		$hOrder = "cust.bom_temp_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			
			$row_data[] = $row['product_name'];
			$row_data[] = $row['upload_date'];
			$row_data[] = $row['cust_email'];
			
			
			$edit_btn=''; $delete_btn=''; 
			
			//$view_cust_btn=' <a class="btn btn-xs btn-info" data-original-title="View Customer" data-toggle="tooltip" data-placement="top" href="'.ROOT.'customer_view/'.$row['cust_id'].'"><i class="fa fa-eye"></i></a>';
			 $delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bom('.$row['bom_temp_id'].')"><i class="fa fa-trash-o"></i></button>';
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'import_bom_print/'.$row['bom_temp_id'].'"><i class="fa fa-pencil"></i></a>'; 
			
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
		else if(brp_strtolower($POST['mode']) == "delete") {

			
				$info['bom_temp_status'] = 2;
				$updateestimateid=update_record('bom_temp', $info, "bom_temp_id=".$POST['bom_temp_id'], $dbcon);	
				
				if($updateestimateid){
					echo "1";	
				}else{
					echo "0";
				}
				
				$log_entry=common_log_entry($dbcon,"bom_temp_delete",3,"bom_temp",$POST['bom_temp_id']);
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
					$perent_id=0;$main_id=0;
					while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
					{
						$error='';
						$fikecount1=count($data);
						//$fikecount1=$fikecount1-4;
						
						if(!empty($data['0']))
						{
							$csv_srno			=$data['0'];
							$csv_product_name	=$data['1'];
							$csv_qty			=$data['2'];
							$csv_base_unit		=$data['3'];
							
							if($csv_srno!="main"){
								$seri=explode(".",$csv_srno);
								$count_leval=count($seri);
								if($count_leval>1){
									$remove = array_pop($seri);  
									$perent_series_no=implode(".",$seri);
									$qperent="SELECT bom_temp_id FROM `bom_temp` WHERE bom_temp_status=0 and main_id=$main_id and `sr_no`='".$perent_series_no."'";
								}else{
									$qperent="SELECT bom_temp_id FROM `bom_temp` WHERE bom_temp_status=0 and main_id=$main_id and `sr_no` ='main'";
								}
								$tr_perent = brp_mysqli_fetch_array($dbcon->query($qperent));
								$perent_id=$tr_perent['bom_temp_id'];

							}else{
								$perent_id=0;
							}
							if(!empty($csv_product_name)){
								
								$qstate="SELECT `product_id`,`product_name` FROM `product_mst` WHERE product_status=0 and `product_name` ='".$csv_product_name."'";
								$tr_state = brp_mysqli_fetch_array($dbcon -> query($qstate));
								if(!empty($tr_state))
								{ 
									$csv_product_id=$tr_state['product_id'];
								 }				
								else
								{
									$csv_product_id="";
									//$error='Product Not Found '.$csv_product_name;
									//array_push($error_array,1);
								} 
							}else{
								$error='Product Name Not Add In Excel File';
								array_push($error_array,1);
							}
							
							if(!empty($csv_base_unit)){
								/*  $qstate_2="SELECT `unitid`,`unit_name` FROM `unit_mst` WHERE unit_status=0 and `unit_name` ='".$csv_base_unit."'";  */
								if(!empty($csv_product_id)){
									$qstate_2="SELECT `unitid` FROM `product_mst` as pro
										left join unit_mst as unit on unit.unitid=pro.product_base_unit
										WHERE `product_id` ='".$csv_product_id."'";
									$tr_state_2 = brp_mysqli_fetch_array($dbcon -> query($qstate_2));
									if(!empty($tr_state_2))
									{  
										$csv_base_unit_id=$tr_state_2['unitid'];
									  }				
									else
									{
										//$error='Base Unit Not Found';
										//array_push($error_array,1);
										$csv_base_unit_id="";
									} 
								}else{
									$csv_base_unit_id="";
								}
							}else{
								$error='Base Unit Name Not Add In Excel File';
								array_push($error_array,1);
							} 
							
							
							
							$q=$dbcon->query("select branch_id,product_id from tbl_branch_product_stock where branch_id='".$csv_godown_id."' and product_id='".$csv_product_id."'");
							$count=brp_mysqli_num_rows($q);
							$roq=brp_mysqli_fetch_assoc($q);
									
									$info['upload_date']	= date("Y-m-d");
									$info['main_id']		= $main_id;
									$info['perent_id']		= $perent_id;
									$info['product_id']		= $csv_product_id;
									$info['product_name']	= $csv_product_name;
									$info['sr_no']			= $csv_srno;
									$info['qty']			= $csv_qty;
									$info['unit_id']		= $csv_base_unit_id;
									$info['unit_name']		= $csv_base_unit;
									$info['user_id']		= $_SESSION['user_id'];
									$info['cdate']			= date("Y-m-d h:i:s");
									$info['company_id']		= $_SESSION['company_id'];
									
									
							//var_dump($info);
							$table='bom_temp';$tableid='bom_temp_id';
							
									$inserid=add_record($table, $info, $dbcon);
								if($info['perent_id']=="0"){
									$main_id=$inserid;
									
									$info_mai['main_id']					= $main_id;
									$updat2eid=update_record('bom_temp', $info_mai, "bom_temp_id=".$inserid, $dbcon);
								}
							
								if($fikecount1>0){
									$prio=1;
									$processc=4;
									//var_dump($processc);
									//var_dump($fikecount1);
									while($processc < $fikecount1) {
										if($data[$processc]==="inhouse"){
											$process_type=1;
										}else{
											$process_type=2;
										}
										
									 	$infoproxewews['bom_temp_id']		    = $inserid;
									 	$infoproxewews['process_type_name']		= $data[$processc];
										$infoproxewews['process_type']			= $process_type;
										$processc=$processc+1;
										
										$query_proc="select process_id from process_mst as bom where process_status=0 and bom.process_name='".$data[$processc]."'";
										$rel_proc=mysqli_fetch_assoc($dbcon->query($query_proc));
										if(!empty($rel_proc['process_id'])){
											$pid=$rel_proc['process_id'];
										}else{
											$query_procty="select process_type_id from process_type_mst as bom where process_type_status=0 and bom.company_id='".$_SESSION['company_id']."'";
											$rel_procty=mysqli_fetch_assoc($dbcon->query($query_procty));
											
											$infoproxe['process_name']			= $data[$processc];
											$infoproxe['process_type']			= $rel_procty['process_type_id'];
											$infoproxe['cdate']					= $data[$processc];
											$infoproxe['user_id']				= $data[$processc];
											$infoproxe['company_id']			= $data[$processc];
											$pid=add_record("process_mst", $infoproxe, $dbcon);

											$info_m['user_id'] = $_SESSION['user_id'];
								            $info_m['parent_id'] = '104';
								            $info_m['process_id'] = $pid;
								            $info_m['menu_name'] = $info['process_name'];
								            $info_m['menu_path'] = '#';
								            $info_m['menu_description'] = $info['process_name'];
								            $info_m['menu_order'] = $pid;
								            $info_m['menu_fa_icon'] = 'FA-DASHBOARD';
								            $info_m['report_status_flag'] = 'Yes';
								            $info_m['status'] = '0';
								            $access_id = add_record('menu_master_access', $info_m, $dbcon);
								            
								            $infoRoutes['user_id'] = $_SESSION['user_id'];
								            $infoRoutes['access_id'] = $access_id;
								            $infoRoutes['access_type'] = 'V';
								            $infoRoutes['slug_name'] = 'dashboard-inhouse-'.str_replace(' ', '-', strtolower($info['process_name']));
								            $infoRoutes['route_path_name'] = '0';
								            $infoRoutes['status'] = '0';
								            $insertRoutesid = add_record('menu_master_access_routes', $infoRoutes, $dbcon);
										}
										$infoproxewews['process_name']			= $data[$processc];
										$infoproxewews['process_id']			= $pid;
										
										$infoproxewews['priority']				= $prio;
										$infoproxewews['user_id']				= $_SESSION['user_id'];
										$infoproxewews['cdate']					= date("Y-m-d h:i:s");
										$infoproxewews['company_id']			= $_SESSION['company_id'];
										if(!empty($infoproxewews['process_type_name']) && !empty($infoproxewews['process_name'])){
											$inserid2=add_record("bom_process_temp", $infoproxewews, $dbcon);
										}
										$processc=$processc+1;
										$prio++;
									}
								}
							 
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
							$result['temp_id']=$main_id;
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
		else if(strtolower($POST['mode']) == "add_bom_new") {
			 $query1="select * from bom_temp as bom
					where product_id='' and unit_id=''and main_id=".$POST['main_id'];
					$resui1=$dbcon->query($query1);
				$cnt=mysqli_num_rows($resui1);
				
			if($cnt>0){
				echo "-1";
			}else{	
				
			 $query="select bom.*,product.product_name,product.image_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name,product.product_type,dwg.drawing_number from bom_temp as bom
				left join product_mst as product on product.product_id=bom.product_id
						left join unit_mst as bunit on bunit.unitid=bom.unit_id
						left join unit_mst as cunit on cunit.unitid=bom.unit_id
						left join tbl_drawing as dwg on dwg.drawing_id=product.drawing_id
					where bom_temp_status=0 and main_id=".$POST['main_id'];
					$resui=$dbcon->query($query);
				while($rel=mysqli_fetch_assoc($resui)){
					
					$query_po="select bom_temp_id from bom_temp as bom where perent_id=".$rel['bom_temp_id'];
					$result=$dbcon->query($query_po);
					$cnt=mysqli_num_rows($result);
					//var_dump($cnt);
					if($cnt>0){
						
					}else{
						$bom_id=check_bom_excel($dbcon,$rel['bom_temp_id']);
						//var_dump($bom_id);
						if(!empty($bom_id)){
							add_perent_bom($dbcon,$rel['bom_temp_id']);
						}
					}
				}
				//$query_po1="TRUNCATE TABLE `bom_process_temp`";
				//$dbcon->query($query_po1);
				
				//$query_po2="TRUNCATE TABLE `bom_temp`";
				//$dbcon->query($query_po2);
				
			echo 1;
			}
		}else if(strtolower($POST['mode']) == "preedit"){
			$query="select * from bom_temp as temp 
					where bom_temp_id=".$POST['i_id'];
			$result=$dbcon->query($query);
			$rel=mysqli_fetch_assoc($result);
			
			echo json_encode($rel);
		}else if(strtolower($POST['mode']) == "add"){
			$info_cust['product_id']			= $POST['product_id'];
			$info_cust['unit_id']				= $POST['unit_id'];
			$info_cust['qty']					= $POST['qty'];
			$updateid=update_record('bom_temp', $info_cust, "bom_temp_id=".$POST['bom_temp_id'], $dbcon);
			if($updateid){
				$rel['msg']="update";
			}else{
				$rel['msg']="0";
			}
			
			echo json_encode($rel);
			
		}else if(strtolower($POST['mode']) == "check_bom"){
			$query="select unit.unit_name,temp.product_base_unit from product_mst as temp
					left join unit_mst as unit on unit.unitid=temp.product_base_unit
					where product_id=".$POST['product_id'];
			$result=$dbcon->query($query);
			$rel=mysqli_fetch_assoc($result);
			echo json_encode($rel);
		}
function check_data($filename,$dbcon)
{
	$error=array();
	$arr = explode(".", $filename);
	$fp = fopen(CUSTOMER_UPING.$filename, 'r');
	$frow = fgetcsv($fp);
	$fikecount=count($frow);
	if($fikecount>=4) // Define coulmn count Here
	{ 
		$hname=array('Sr. No.','Item Description','Qty ','Unit');
		$fikecount=$fikecount-4;
		if($fikecount>0){
			for ($x = 1; $x <= $fikecount; $x++) {
			  array_push($hname,"");
			}
		}
		$msg='';
		foreach($frow as $i)
		if ( !in_array($i,$hname, true ) ) 
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
function check_bom_excel($dbcon,$bom_temp_id){
	//var_dump("ds");
	 $query="select product_id from bom_temp as bom where bom.bom_temp_id=".$bom_temp_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
		 $query_bom_tem="select GROUP_CONCAT(product_id) as bom_trn_temp_product,sr_no,GROUP_CONCAT(bom_version_id) as bom_trn_temp_version from bom_temp as bom where bom.perent_id=".$bom_temp_id;
			$result_bom_temp=$dbcon->query($query_bom_tem);
			$cnt_bom_temp=mysqli_num_rows($result_bom_temp);
			$rel_bom_pro_temp=mysqli_fetch_assoc($result_bom_temp);
			if(!empty($rel_bom_pro_temp['sr_no'])){
				//$bom_tr_product_temp=explode(",",$rel_bom_pro_temp['bom_trn_temp_product']);
				//$bom_trn_pro_temp_count=count($bom_tr_product_temp);
				$bom_tr_product_temp=explode(",",$rel_bom_pro_temp['bom_trn_temp_version']);
				$bom_trn_pro_temp_count=count($bom_tr_product_temp);
			}
	
	$query_bom="select bom_product,bom_id from tbl_bom as bom where bom.bom_status=0 and bom.bom_product=".$rel['product_id'];
	$resultq=$dbcon->query($query_bom);
	$cnt=mysqli_num_rows($resultq);
	if($cnt>0){
		$bom_id=0;
		while($rel_bom=mysqli_fetch_assoc($resultq)){
			if($bom_id==0){
				if(!empty($rel_bom_pro_temp['sr_no'])){
					//var_dump($rel_bom_pro_temp['sr_no']);
					$query_bom_trn="select GROUP_CONCAT(product_id) as bom_trn_product,GROUP_CONCAT(bom_version_id) as bom_trn_version from tbl_bomtrn as bom where bom.bom_trn_status=0 and bom.bom_id=".$rel_bom['bom_id'];
					$result_bom_trn=$dbcon->query($query_bom_trn);
					$cnt_bom_trn=mysqli_num_rows($result_bom_trn);
					if($cnt_bom_trn>0){
						$rel_bom_pro=mysqli_fetch_assoc($result_bom_trn);
						//$bom_tr_product=explode(",",$rel_bom_pro['bom_trn_product']);
						$bom_tr_product=explode(",",$rel_bom_pro['bom_trn_version']);
						$bom_trn_pro_count=count($bom_tr_product);
						
						if($bom_trn_pro_temp_count===$bom_trn_pro_count){
							$result_mix=array_intersect($bom_tr_product_temp,$bom_tr_product);
							$result_mix_count=count($result_mix);
							if($result_mix_count===$bom_trn_pro_temp_count){
								$bom_id=$rel_bom['bom_id'];
							}
						}
					}
					
				}else{
					$bom_id=$rel_bom['bom_id'];
					//var_dump($bom_id);
				}
			}
		}
		if($bom_id==0){
			$bom_id=add_bom_entry($dbcon,$bom_temp_id);
		}
	}else{
		$bom_id=add_bom_entry($dbcon,$bom_temp_id);
		
	}
	//var_dump($bom_id);
	if(!empty($bom_id)){
		$query_v="select bom_version_id from tbl_bom as bom where bom_id=".$bom_id;
		$result_v=$dbcon->query($query_v);
		$rel_v=mysqli_fetch_assoc($result_v);
	
		$invtrn['bom_id']	= $bom_id;
		$invtrn['bom_version_id']	= $rel_v['bom_version_id'];
    	$updatetrnid=update_record('bom_temp', $invtrn,"bom_temp_id=".$bom_temp_id, $dbcon);
	}
	return $bom_id;
}
function add_perent_bom($dbcon,$bom_temp_id){
	 $query_po="select perent_id from bom_temp as bom where bom_temp_id=".$bom_temp_id;
	$result=$dbcon->query($query_po);
	$rel_bom=mysqli_fetch_assoc($result);
	
	if(!empty($rel_bom['perent_id'])){
		$query="select bom_temp_id from bom_temp as bom where bom_temp_status=0 and bom_temp_id=".$rel_bom['perent_id'];
		$result1=$dbcon->query($query);
		$rel_bom1=mysqli_fetch_assoc($result1);
		
		$query_pere="select bom_temp_id from bom_temp as bom where bom_id=0 and bom_version_id=0 and perent_id=".$rel_bom1['bom_temp_id'];
		$result_per=$dbcon->query($query_pere);
		$cnt=mysqli_num_rows($result_per);
		if($cnt>0){
			
		}else{
			$bom_id=check_bom_excel($dbcon,$rel_bom1['bom_temp_id']);
			//var_dump($bom_id);
			if(!empty($bom_id)){
				add_perent_bom($dbcon,$rel_bom1['bom_temp_id']);
			}
		}
	}
}
function add_bom_entry($dbcon,$bom_temp_id){
	
	$query="select bom.*,product.product_name,product.image_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name,product.product_type,dwg.drawing_number from bom_temp as bom
	left join product_mst as product on product.product_id=bom.product_id
			left join unit_mst as bunit on bunit.unitid=bom.unit_id
			left join unit_mst as cunit on cunit.unitid=bom.unit_id
			left join tbl_drawing as dwg on dwg.drawing_id=product.drawing_id
		where bom.bom_temp_id=".$bom_temp_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	$queryc="select * from pro_ms_bom_version as bom where bom.bom_version_status = 0 and bom.product_id=".$rel['product_id'];
	$result_per=$dbcon->query($queryc);
	$cnt=mysqli_num_rows($result_per);
	if($cnt>0){
		$def=0;
	}else{
		$def=1;
	}
	//var_dump($def);
	$name=date("Ymd")."-".$cnt;

	$query_pp="select product_base_unit,product_conv_unit from product_mst as bom where bom.product_id=".$rel['product_id'];
		$restpp=$dbcon->query($query_pp);
		$rel_subpp=mysqli_fetch_assoc($restpp);
		
		if($rel_subpp['product_base_unit']!=$rel_subpp['product_conv_unit']){
			$type="conv_unit";
			$conqty=convert_stock($dbcon,$rel['qty'],$rel['product_id'],$type);
			
		}else{
			$conqty=$rel['qty'];
		}
	
	$info_ver['bom_no']						= $name;
	$info_ver['product_id']					= $rel['product_id'];
	$info_ver['version_name']				= $name;
	$info_ver['is_default_bom']				= $def;
	$info_ver['bom_version_status']			= 0;
	$info_ver['bom_active_status']			= 1;
	$info_ver['bom_version_date']			= date("Y-m-d");
	$info_ver['bom_unit_qty']				= $rel['qty'];
	$info_ver['bom_conv_qty']				= $conqty;
	$info_ver['bom_unit']					= $rel_subpp['product_base_unit'];
	$info_ver['bom_conv_unit']				= $rel_subpp['product_conv_unit'];
	$info_ver['user_id']					= $_SESSION['user_id'];
	$info_ver['company_id']					= $_SESSION['company_id'];
	$info_ver['cdate']						= date("Y-m-d H:i:s");
	$inse_vers=add_record('pro_ms_bom_version', $info_ver, $dbcon);
	
	$info['bom_no']					= $name;
	$info['bom_date']				= date("Y-m-d");
	$info['bom_product']			= $rel['product_id'];
	$info['bom_qty']				= $rel['qty'];
	$info['product_base_unit']		= $rel_subpp['product_base_unit'];
	$info['product_base_qty']		= $rel['qty'];
	$info['product_conv_unit']		= $rel_subpp['product_conv_unit'];
	$info['product_conv_qty']		= $conqty;
	$info['bom_version_id']			= $inse_vers;
	$info['cdate']					= date("Y-m-d H:i:s");
	//$info['user_id']				= $_SESSION['user_id'];
	$info['user_id']				= $bom_temp_id;
	$info['company_id']				= $_SESSION['company_id'];
	
	$inserinvoiceid=add_record('tbl_bom', $info, $dbcon);
	
	$query_proc="select * from bom_process_temp as bom where bom.bom_temp_id=".$bom_temp_id;
	$result_proce=$dbcon->query($query_proc);
	$cnt_proc=mysqli_num_rows($result_proce);
	if($cnt_proc>0){	
		while($rel_process=mysqli_fetch_assoc($result_proce)){
			$query_proc_ch="select pr_process_id from tbl_product_process as bom where bom.product_id=".$rel['product_id']." and process_type=".$rel_process['process_type']." and bom.status=0 and  process_id=".$rel_process['process_id'];
			$result_proce_ch=$dbcon->query($query_proc_ch);
			$rel_process_ch=mysqli_fetch_assoc($result_proce_ch);
			
			if(!empty($rel_process_ch['pr_process_id'])){
				$ppid=$rel_process_ch['pr_process_id'];
			}else{
				$info_process_n['product_id']			= $rel['product_id'];
				$info_process_n['process_priority']		= $rel_process['priority'];
				$info_process_n['process_type']			= $rel_process['process_type'];
				$info_process_n['process_id']			= $rel_process['process_id'];
				$info_process_n['cdate']				= date("Y-m-d H:i:s");
				$info_process_n['user_id']				= $_SESSION['user_id'];
				$info_process_n['company_id']			= $_SESSION['company_id'];
				$ppid=add_record('tbl_product_process', $info_process_n, $dbcon);
			}
			
			$info_process['bom_version_id']			= $inse_vers;
			$info_process['product_id']				= $rel['product_id'];
			$info_process['bom_id']					= $inserinvoiceid;
			$info_process['priority']				= $rel_process['priority'];
			$info_process['pr_process_id']			= $ppid;
			$info_process['cdate']					= date("Y-m-d H:i:s");
			$info_process['user_id']				= $_SESSION['user_id'];
			$info_process['company_id']				= $_SESSION['company_id'];
			$inseriprocess=add_record('pro_bom_process', $info_process, $dbcon);
		}
	}
	
	$query_sub="select * from bom_temp as bom where bom.perent_id=".$bom_temp_id;
	$resty=$dbcon->query($query_sub);
	while($rel_sub=mysqli_fetch_assoc($resty)){
		
		

		$query_pp="select product_base_unit,product_conv_unit from product_mst as bom where bom.product_id=".$rel_sub['product_id'];
		$restpp=$dbcon->query($query_pp);
		$rel_subpp=mysqli_fetch_assoc($restpp);
		
		if($rel_subpp['product_base_unit']!=$rel_subpp['product_conv_unit']){
			$type="conv_unit";
			$conqty=convert_stock($dbcon,$rel_sub['qty'],$rel_sub['product_id'],$type);
			
		}else{
			$conqty=$rel_sub['qty'];
		}

		$info_trn['bom_id']						= $inserinvoiceid;
		$info_trn['bom_version_id']				= $rel_sub['bom_version_id'];
		$info_trn['product_id']					= $rel_sub['product_id'];
		$info_trn['p_bom_id']					= $rel_sub['bom_id'];
		$info_trn['p_bom_version_id']			= $inse_vers;
		$info_trn['product_base_qty']			= $rel_sub['qty'];
		$info_trn['product_base_unit']			= $rel_subpp['product_base_unit'];
		$info_trn['product_conv_unit']			= $rel_subpp['product_conv_unit'];
		$info_trn['product_conv_qty']			= $conqty;
		$info_trn['user_id']					= $_SESSION['user_id'];
		$info_trn['company_id']					= $_SESSION['company_id'];
		$inserin_trn=add_record('tbl_bomtrn', $info_trn, $dbcon);
	}
	return $inserinvoiceid;
}
?>