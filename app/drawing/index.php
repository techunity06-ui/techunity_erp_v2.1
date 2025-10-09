<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
		
			$where='';
			$branch_id = $POST['branch_id'];
		    if($branch_id){
		        $where .= check_branch('dr',$branch_id);
		    }
			/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$appr_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);
*/
		
			$where.="  and dr.cdate >= '".date('Y-m-d',strtotime($s_date[0]))."' AND dr.cdate <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('dr.drawing_id','dr.drawing_number','dr.drawing_title','l.l_name','dr.cdate','dr.drawing_status');
			$sIndexColumn = "dr.drawing_id";
			$isWhere = array("dr.drawing_status IN (0,1) and dr.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "tbl_drawing as dr";
			$isJOIN = array('left join tbl_ledger as l on dr.vender_id=l.l_id');
			$hOrder = "dr.drawing_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['drawing_number'];
				
				$row_data[] = $row['drawing_title'];
				$row_data[] = $row["l_name"];
				$row_data[] = date('d M, Y',strtotime($row['cdate']));

				/*if($edit_btn_per){
					$row_data[] = '<a class="" data-original-title="Edit '.$row['drawing_id'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poedit/'.$row['purchaseorder_id'].'">'.$row['purchaseorder_no'].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row['drawing_id'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poedit/'.$row['purchaseorder_id'].'">'.date('d M, Y',strtotime($row['cdate'])).'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row['drawing_id'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poedit/'.$row['purchaseorder_id'].'">'.$row["l_name"].'</a>';
				}*/
				
				if($row['drawing_status']=='0'){
					$row_data[] = '<button class="btn btn-xs btn-success" >Active</button>';
				}else{
					$row_data[] = '<button class="btn btn-xs btn-danger" >In-Active</button>';
				}
				
				$poprint='';$delete='';$edit='';$cancel_po_btn='';$po_app_btn='';$st_btn='';
				
				//if($edit_btn_per){
						$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'drawingedit/'.$row['drawing_id'].'"><i class="fa fa-pencil"></i></a>';
				//}
				/*if($delete_btn_per){
						$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_po('.$row['drawing_id'].')"><i class="fa fa-trash-o"></i></button>';
				}*/
				if($row['drawing_status']=="0"){
					$st_btn ='<button class="btn btn-xs btn-success" title="In-Active" data-toggle="tooltip" data-placement="top" onclick="change_drawing_status('.$row['drawing_id'].',1)"><i class="fa fa-check"></i></button>';
				}else if($row['drawing_status']=="1"){
					$st_btn ='<button class="btn btn-xs btn-warning" title="Active" data-toggle="tooltip" data-placement="top" onclick="change_drawing_status('.$row['drawing_id'].',0)"><i class="fa fa-check"></i></button>';
				}
				
				$row_data[] = $edit.' '.$st_btn;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$query="select * from tbl_invoicetype where status=0 and type_id=20 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);

			$query1="select * from  tbl_invoicetype where invoicetype_id=".$row['invoicetype_id'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			$auto_no=str_pad($id,3,"0",STR_PAD_LEFT);


			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	
			$info['drawing_number']	= $POST['drawing_number'].$auto_no;
			$info['drawing_title']	= $POST['drawing_title'];
			$info['vender_id']	= $POST['vender_id'];
			$info['drawing_size']		= $POST['drawing_size'];
			$info['drawing_scale']	= $POST['drawing_scale'];
			$info['drawing_location']	= $POST['drawing_location'];
			$info['sales_order_id']	= $POST['sales_order_id'];
			$info['remark']	= $POST['remark'];

			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['drawing_status']	= 0;
			$info['cdate']	= date('Y-m-d');
			
			$inserpoid=add_record('tbl_drawing', $info, $dbcon, $branch_id);
			
			if($inserpoid){
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$row['invoicetype_id']);
				// Update tbl_revision table
				$upd_qt_qry="update tbl_revision set drawing_id=".$inserpoid.", revision_status=0 where revision_id='".$_SESSION['revision_id']."' AND company_id='".$_SESSION['company_id']."' ";
				$dbcon->query($upd_qt_qry);	

				// Update tbl_revision table
				$upd_qt_qry="update tbl_drawing_revision_image set drawing_id=".$inserpoid.", drawing_revision_status=0 where revision_id='".$_SESSION['revision_id']."' AND company_id='".$_SESSION['company_id']."' ";
				$dbcon->query($upd_qt_qry);	

				$_SESSION['revision_id']='';
				$_SESSION['revision_number']='';
				unset($_SESSION['revision_id']);
				unset($_SESSION['revision_number']);

				$i=0;
				//foreach($_FILES["file"]["tmp_name"] as $files){
					
					$test = explode('.', $_FILES["file"]["name"]);
					$ext = end($test);
					$name = $dr_id.'_'.$revision_insertid.'_'.time() . '.' . $ext;
					$path='../../view/upload/drawing_images/';
					$location = $path . $name;  
					move_uploaded_file($_FILES["file"]["tmp_name"], $location);

					$drowing_images['drawing_id'] = $inserpoid;
					$drowing_images['revision_id'] = $POST['revision_id'];
					$drowing_images['file_name'] = $name;
					$drowing_images['file_path'] = $path;
					$drowing_images['user_id']	= $_SESSION['user_id'];
					$drowing_images['company_id']	= $_SESSION['company_id'];
					$drowing_images['cdate']	= date('Y-m-d H:i:s');
					$drowing_images['drawing_revision_status'] = 0;

					add_record('tbl_drawing_revision_image', $drowing_images, $dbcon, $branch_id);
					$i++;
				//}

				$arr['msg']="1";							
			}
			else{
				$arr['msg']="0";
			}

			$arr['back']=$POST['back'];
			echo json_encode($arr);					
		 
		}		
		else if(strtolower($POST['mode']) == "edit") {

				$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			 	
			 	//$info['drawing_number']	= $POST['drawing_number'];
				$info['drawing_title']	= $POST['drawing_title'];
				$info['vender_id']	= $POST['vender_id'];
				$info['drawing_size']		= $POST['drawing_size'];
				$info['drawing_scale']	= $POST['drawing_scale'];
				$info['drawing_location']	= $POST['drawing_location'];
				$info['sales_order_id']	= $POST['sales_order_id'];
				$info['remark']	= $POST['remark'];
				$info['muser_id']	= $_SESSION['user_id'];
				$info['mdate']		= date("Y-m-d H:i:s");
				$info['company_id']		= $_SESSION['company_id'];

				
				$updateid1=update_record('tbl_drawing', $info,"drawing_id=".$POST['eid'] , $dbcon, $branch_id);
			
				if($updateid1)
				{	

					if(!empty($_FILES["file"]["tmp_name"])){

						$i=0;

						//foreach($_FILES["file"]["tmp_name"] as $files){
							
							$test = explode('.', $_FILES["file"]["name"]);
							$ext = end($test);
							$name = $dr_id.'_'.$revision_insertid.'_'.time() . '.' . $ext;
							$path='../../view/upload/drawing_images/';
							$location = $path . $name;  
							move_uploaded_file($_FILES["file"]["tmp_name"], $location);

							$drowing_images['drawing_id'] = $POST['eid'];
							$drowing_images['revision_id'] = $POST['revision_id'];
							$drowing_images['file_name'] = $name;
							$drowing_images['file_path'] = $path;
							$drowing_images['user_id']	= $_SESSION['user_id'];
							$drowing_images['company_id']	= $_SESSION['company_id'];
							$drowing_images['cdate']	= date('Y-m-d H:i:s');
							$drowing_images['drawing_revision_status'] = 0;


							add_record('tbl_drawing_revision_image', $drowing_images, $dbcon, $branch_id);
							$i++;
						//}
						
					}
					$arr['msg']="update";
				}
				else{
					$arr['msg']=0;
				}
			echo json_encode($arr);	
			 
		}

			else if(strtolower($POST['mode'])== "get_revision_data")
			{
				$eid = $POST['eid'];
				if($eid!=''){
					$where = ' and drawing_id= "'.$eid.'" and revision_status IN (0,1) ';
				}else{
					$where = '  and revision_status=3 ';
				}

				$qry="SELECT * FROM `tbl_revision` Where `company_id`='".$_SESSION['company_id']."' $where ";
				
				$result=$dbcon->query($qry);
				echo '<div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th class="text-center" width="10%">SR No.</th>
								<th class="text-center" width="25%">Revision Number</th>
								<th class="text-center"width="8%">Date</th>
								<th class="text-center"width="8%">Remark</th>
								<th class="text-center"width="10%">Action</th>
							</tr>';
							
							//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					if($rel['revision_status']=="0"){
						$st_btn='<button class="btn btn-xs btn-success" title="In-Active" data-toggle="tooltip" data-placement="top" onclick="change_revision_status('.$rel['revision_id'].',1)"><i class="fa fa-check"></i></button>';
					}
					else{
						$st_btn='<button class="btn btn-xs btn-warning" title="Active" data-toggle="tooltip" data-placement="top" onclick="change_revision_status('.$rel['revision_id'].',0)"><i class="fa fa-check"></i></button>';
					}

					$view_image = '<a class="btn btn-xs btn-info" title="View Image" data-toggle="tooltip" data-id="'.$rel['revision_id'].'" data-placement="top" href="javascript:void(0)" onClick="view_revision_image('.$rel['revision_id'].')"><i class="fa fa-eye"></i></a>';
					//$r=get_product_tax($dbcon,$rel['purchaseordertrn_id']);
					//$total=$rel['pqty']*$rel['product_purchase_rate'];
				 
				 echo '<tr id="fieldtr'.$i.'">
						
						<td style="vertical-align:top;" class="text-center">
							'.$i.'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$rel['revision_number'].'
						</td>					
						<td style="vertical-align:top;" class="text-right">
							'.date("d-M-Y", strtotime($rel["revision_date"])).'
						</td>				
						<td style="vertical-align:top" class="text-center">
							'.$rel['remark'].'
						</td>
						<td style="vertical-align:top" class="text-center">
							'.$view_image.' '.$st_btn.'
						</td>
				
					</tr>';
					$i++;
				}
			}else{
				 echo '<tr><td colspan="5">NO DATA FOUND</td></tr>';
			}
		}
		else if(strtolower($POST['mode'])== "view_revision_image")
			{
				$id = $POST['id'];
				$where = ' and revision_id= "'.$id.'" ';

				$qry="SELECT * FROM `tbl_drawing_revision_image` Where `company_id`='".$_SESSION['company_id']."' $where order by drawing_image_id desc ";

				$result=$dbcon->query($qry);

				echo '<div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th class="text-center" width="10%">SR No.</th>
								<th class="text-center" width="25%">View</th>
							</tr>';
							
							//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					$exts = array('gif', 'png', 'jpg'); 
					if(in_array(end(explode('.', $rel['file_name'])), $exts)){

						$filetype = '<a href="'.ROOT.'view/upload/drawing_images/'.$rel["file_name"].'" target="_blank"><img src="'.ROOT.'view/upload/drawing_images/'.$rel["file_name"].'" class="img-thumbnail" width="70" height="70"></a>';
					}else{
						$filetype = '<a href="'.ROOT.'view/upload/drawing_images/'.$rel["file_name"].'" target="_blank">Download File</a>';
					}	
					
				 echo '<tr id="fieldtr'.$i.'">
						
						<td style="vertical-align:top;" class="text-center">
							'.$i.'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$filetype.'
						</td>					
					</tr>';
					$i++;
				}
			}else{
				 echo '<tr><td colspan="2">NO DATA FOUND</td></tr>';
			}
		}

		else if(strtolower($POST['mode'])== "view_drawing_image")
			{
				$id = $POST['id']; 
				$where = ' and drawing_id= "'.$id.'" ';

				$qry="SELECT * FROM `tbl_drawing_revision_image` Where `company_id`='".$_SESSION['company_id']."'$where ";

				$result=$dbcon->query($qry);

				echo '<div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th class="text-center" width="10%">SR No.</th>
								<th class="text-center" width="25%">View</th>
							</tr>';
							
							//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{

					$exts = array('gif', 'png', 'jpg'); 
					if(in_array(end(explode('.', $rel['file_name'])), $exts)){

						$filetype = '<a href="../view/upload/drawing_images/'.$rel["file_name"].'" target="_blank"><img src="../view/upload/drawing_images/'.$rel["file_name"].'" class="img-thumbnail" width="70" height="70"></a>';
					}else{
						$filetype = '<a href="../view/upload/drawing_images/'.$rel["file_name"].'" target="_blank">Download File</a>';
					}	
					
				 echo '<tr id="fieldtr'.$i.'">
						
						<td style="vertical-align:top;" class="text-center">
							'.$i.'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$filetype.'
						</td>					
					</tr>';
					$i++;
				}
			}else{
				 echo '<tr><td colspan="2" style="text-align:center">NO DATA FOUND</td></tr>';
			}
		}	
		else if(strtolower($POST['mode']) == "change_revision_status") {
			
			$id = $POST['eid'];
			$info['revision_status'] = $POST['status'];
			$info['muser_id'] = $_SESSION['user_id'];
			$info['mdate'] = date('Y-m-d H:i:s');

			$updatetrancationid=update_record('tbl_revision', $info,"revision_id=".$id , $dbcon);	
			
			if($updatetrancationid){
				$arr['msg'] = '1';
			}else{
				$arr['msg'] = '0';
			}

			echo json_encode($arr);
					
		}
		else if(strtolower($POST['mode']) == "change_drawing_status") {
			
			$id = $POST['eid'];
			$info['drawing_status'] = $POST['status'];
			$info['muser_id'] = $_SESSION['user_id'];
			$info['mdate'] = date('Y-m-d H:i:s');

			$updatetrancationid=update_record('tbl_drawing', $info,"drawing_id=".$id , $dbcon);	
			
			if($updatetrancationid){
				$arr['msg'] = '1';
			}else{
				$arr['msg'] = '0';
			}

			echo json_encode($arr);
					
		}
		
		else if(strtolower($POST['mode']) == "check_drawing_number") {
			$drawing_number		= $POST['drawing_number'];
			$eid		= $POST['eid'];

			$where = '';	
			if($eid!=''){
				$where = ' and drawing_id!="'.$eid.'" ';
			}

			$sql = "SELECT * FROM `tbl_drawing` WHERE drawing_number LIKE '".$drawing_number."%' $where ";	
			$result = $dbcon->query($sql);	
			
			if(mysqli_num_rows($result)>0){
				echo "1";
			}else{
				echo "0";
			}
			
					
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['drawing_status']		= 2;
			$updatetrancationid=update_record('tbl_drawing', $info,"drawing_id=".$POST['eid'] , $dbcon);	
			
			$sql = "DELETE FROM `tbl_drawing_image` WHERE drawing_id='".$POST['eid']."' ";	
			$dbcon->query($sql);	
			
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "delete_image") {
			$id		= $POST['id'];
			$sql = "DELETE FROM `tbl_drawing_image` WHERE drawing_image_id='".$id."' ";	
			$updatetrancationid = $dbcon->query($sql);		
			
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode'])== "get_dr_history")
		{
			$revision_id = $POST['revision_id']; // as purchase id
			
			$sql = "SELECT `u`.`user_name` as prepared_by,`po`.`cdate`, `mu`.`user_name` as last_modify_by, `po`.`mdate`, `po`.`revision_status` as stage  FROM `tbl_revision` as po left join `users` as u ON  `po`.`user_id` = `u`.`user_id` left join `users` as mu ON  `po`.`muser_id` = `mu`.`user_id` Where `po`.`revision_id`='".$revision_id."' and `po`.`company_id`='".$_SESSION['company_id']."'";
			
			$vrow=$dbcon->query($sql);
			$rel=mysqli_fetch_assoc($vrow);
			
			$created_date='';$modify_date='';
			if($rel['cdate']!="1970-01-01 00:00:00" && $rel['cdate']!="0000-00-00 00:00:00")
			{
				$created_date=date('d-M-Y', strtotime($rel["cdate"]));
			}
			if($rel['mdate']!="1970-01-01 00:00:00" && $rel['mdate']!="0000-00-00 00:00:00")
			{
				$modify_date=date('d-M-Y', strtotime($rel["mdate"]));
			}
				
			if($rel['stage']=='0'){
			 	$stage = 'Active';
			 }elseif($rel['stage']=='1'){
			 	$stage = 'In-Active';
			 }			
			echo '<section class="panel">
                     <div class="panel-body bio-graph-info">
                         <h1>Drawing History</h1>
                         <div class="row">
                             <div class="bio-row">
                                 <p><span>Prepared By </span>: '.$rel["prepared_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Prepared Date </span>: '.$created_date.'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified By </span>: '.$rel["last_modify_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified Date</span>: '.$modify_date.'</p>
                             </div>
                            
                             <div class="bio-row">
                                 <p><span>Approved By </span>: NA</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved Date </span>: NA</p>
                             </div>
                           
                             <div class="bio-row">
                                 <p><span> Stage </span>: '.$stage.'</p>
                             </div>
                             
                         </div>
                     </div>
                 </section>';
		}
		
		else if(strtolower($POST['mode'])== "get_so_no")
		{
			$cust_id=$POST['cust_id'];

			$arr['sales_order_id'] = getsalesorder_return($dbcon,$cust_id,'');
			echo json_encode($arr);
			 
		}
		else if(strtolower($POST['mode'])== "get_vendor_contact_details"){
			/* Code By Umair : to return the vendors basic information */
			$cust_id=$POST['cust_id'];
			$venqry = "SELECT `v`.`m_name`, `v`.`company_name`, `v`.`cust_mobile`, `v`.`cust_mobile`, `v`.`cust_email` FROM tbl_ledger as v WHERE `v`.`l_id`='".$cust_id."' AND `v`.`company_id`='".$_SESSION['company_id']."'";
			$vrow=$dbcon->query($venqry);

			$vrel=mysqli_fetch_assoc($vrow);
			
			echo json_encode($vrel);

		}

		else if(strtolower($POST['mode'])== "get_po_history")
		{
			$eid = $POST['eid']; // as purchase id
			$sql = "SELECT `u`.`user_name` as prepared_by,`po`.`cdate`, `mu`.`user_name` as last_modify_by, `po`.`mdate`, `au`.`user_name` as approved_by, `po`.`adate`, `po`.`po_approval_status` as stage, `po`.`purchaseorder_due_date` as delivery_date  FROM `tbl_purchaseorder` as po left join `users` as u ON  `po`.`userid` = `u`.`user_id` left join `users` as mu ON  `po`.`muserid` = `mu`.`user_id` left join `users` as au ON  `po`.`auserid` = `au`.`user_id` Where `po`.`purchaseorder_id`='".$eid."' and `po`.`purchase_status`= 0 and `po`.`company_id`='".$_SESSION['company_id']."'";
			
			$vrow=$dbcon->query($sql);
			$rel=mysqli_fetch_assoc($vrow);
				
			if($rel['stage']=='1'){
			 	$stage = 'Approved';
			 }else{
			 	$stage = 'No';
			 }			
			echo '<section class="panel">
                     <div class="panel-body bio-graph-info">
                         <h1>PO History</h1>
                         <div class="row">
                             <div class="bio-row">
                                 <p><span>Prepared By </span>: '.$rel["prepared_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Prepared Date </span>: '.$rel["cdate"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified By </span>: '.$rel["last_modify_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified Date</span>: '.$rel["mdate"].'</p>
                             </div>
                            
                             <div class="bio-row">
                                 <p><span>Approved By </span>: '.$rel["approved_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved Date </span>: '.$rel["adate"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Delivery Date </span>: '.$rel["delivery_date"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span> Stage </span>: '.$stage.'</p>
                             </div>
                             
                         </div>
                     </div>
                 </section>';
		}
		else if(strtolower($POST['mode'])== "set_vendor_sesion"){
			$vendor_id = $POST['vendor_id'];
			$_SESSION['selected_vendor'] = $vendor_id;
		}

		else if(strtolower($POST['mode'])== "add_revision")
		{

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$info['revision_number'] = $POST['revision_number'];
			$info['drawing_id'] = $POST['drawing_id_ref'];
			$info['revision_date'] = date('Y-m-d', strtotime($POST['revision_date']));
			$info['remark'] = $POST['revision_remark'];

			$info['user_id'] = $_SESSION['user_id'];
			$info['company_id'] = $_SESSION['company_id'];
			$info['cdate'] = date('Y-m-d H:i:s');

			if($POST['drawing_id_ref']!=''){
				$info['revision_status'] = 0;
				$dr_id = $POST['drawing_id_ref'];
			}else{
				$info['revision_status'] = 3;
				$dr_id = 0;
			}

			$revision_insertid=add_record('tbl_revision', $info, $dbcon, $branch_id);

			$i=0;
			foreach($_FILES["revision_file"]["tmp_name"] as $files){
				
				$test = explode('.', $_FILES["revision_file"]["name"][$i]);
				$ext = end($test);
				$name = $dr_id.'_'.$revision_insertid.'_'.time() . '.' . $ext;
				$path='../../view/upload/drawing_images/';
				$location = $path . $name;  
				move_uploaded_file($_FILES["revision_file"]["tmp_name"][$i], $location);

				$drowing_images['drawing_id'] = $POST['drawing_id_ref'];
				$drowing_images['revision_id'] = $revision_insertid;
				$drowing_images['file_name'] = $name;
				$drowing_images['file_path'] = $path;
				$drowing_images['user_id']	= $_SESSION['user_id'];
				$drowing_images['company_id']	= $_SESSION['company_id'];
				$drowing_images['cdate']	= date('Y-m-d H:i:s');

				if($POST['drawing_id_ref']!=''){
					$drowing_images['drawing_revision_status'] = 0;
					
				}else{
					$drowing_images['drawing_revision_status'] = 3;
				}

				add_record('tbl_drawing_revision_image', $drowing_images, $dbcon, $branch_id);
				$i++;
			}

			if($revision_insertid){
				$arr['msg']="1";
				$arr['revision_id_ref']=$revision_insertid;
				$arr['revision_number_ref']=$POST['revision_number'];

				$_SESSION['revision_id']=$revision_insertid;
				$_SESSION['revision_number']=$POST['revision_number'];
			}else{
				$arr['msg']="0";
			}

			echo json_encode($arr);	
		}


    }
}


?>