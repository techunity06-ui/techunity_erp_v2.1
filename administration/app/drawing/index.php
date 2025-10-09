<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
							
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

		 $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    	ADMINISTRATOR_DRAWING_LIST,
		        ADMINISTRATOR_DRAWING_CREATE,
		        ADMINISTRATOR_DRAWING_UPDATE,
		        ADMINISTRATOR_DRAWING_DELETE,
		        ADMINISTRATOR_DRAWING_CHECKED,
		        ADMINISTRATOR_DRAWING_APPROVED,
		        ADMINISTRATOR_DRAWING_PREPARED
		    ]);


		
		if(strtolower($POST['mode']) == "fetch") {
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$branch_id = $POST['branch_id'];
		    
		    $where='';
		     if($branch_id != '1000'){
		        $where .= check_branch('dr',$branch_id);
		    }
	    if($branch_id == ""){
	    	 $output = array(
		        "sEcho" => 1,
		        "iTotalRecords" => 0,
		        "iTotalDisplayRecords" => 0,
		        "aaData" => array()
		    );
	     	
	     	echo json_encode( $output );
	     }else{
		
			$where.="  and dr.cdate >= '".date('Y-m-d',strtotime($s_date[0]))."' AND dr.cdate <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('dr.drawing_id','dr.drawing_number','dr.drawing_title','l.l_name','dr.cdate','dr.drawing_status','dr.approve_status');
			$sIndexColumn = "dr.drawing_id";
			$isWhere = array("dr.drawing_status IN (0,1) and dr.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "tbl_drawing as dr";
			$isJOIN = array('left join tbl_ledger as l on dr.vender_id=l.l_id');
			$hOrder = "dr.drawing_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['drawing_number'];
				$row_data[] = $row['drawing_title'];
				$row_data[] = $row["l_name"];
				$row_data[] = date('d M, Y',strtotime($row['cdate']));
				$btn_checked_by = "";
				$btn_prepared_by = "";
				$btn_approved_by = "";

				if($row['drawing_status']=='0'){
					$row_data[] = '<button class="btn btn-xs btn-success" >Active</button>';
				}else{
					$row_data[] = '<button class="btn btn-xs btn-danger" >In-Active</button>';
				}

				if($row['approve_status']=='0'){
					$row_data[] = '<button class="btn btn-xs btn-warning" >Prepared Pending</button>';
					if(in_array(ADMINISTRATOR_DRAWING_CHECKED,$bulkAccessArray)){
						$btn_prepared_by ='<button class="btn btn-xs btn-success" title="Prepared Drawing" data-toggle="tooltip" data-placement="top" onclick="change_drawing_approve_status('.$row['drawing_id'].',1)"><i class="fa fa-check"></i> Prepared</button>';
					}
				}else if($row['approve_status']=='1'){
					$row_data[] = '<button class="btn btn-xs btn-warning" >Checked Pending</button>';
					if(in_array(ADMINISTRATOR_DRAWING_APPROVED,$bulkAccessArray)){
						$btn_checked_by ='<button class="btn btn-xs btn-success" title="Checked Drawing" data-toggle="tooltip" data-placement="top" onclick="change_drawing_approve_status('.$row['drawing_id'].',2)"><i class="fa fa-check"></i> Checked</button>';
					}
					
				}else if($row['approve_status']=='2'){
					$row_data[] = '<button class="btn btn-xs btn-warning" >Approved Pending</button>';
					if(in_array(ADMINISTRATOR_DRAWING_PREPARED,$bulkAccessArray)){
						$btn_approved_by ='<button class="btn btn-xs btn-success" title="Approve Drawing" data-toggle="tooltip" data-placement="top" onclick="change_drawing_approve_status('.$row['drawing_id'].',3)"><i class="fa fa-check"></i> Approve</button>';
					}
				}else{
					$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
				}
				
				$view_image='';$delete='';$edit='';$st_btn='';
				
				if(in_array(ADMINISTRATOR_DRAWING_UPDATE,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.ADMINISTRATION_ROOT.'drawingedit/'.$row['drawing_id'].'"><i class="fa fa-pencil"></i></a>';
				}

				$qry = "SELECT count(revision_id) as totol_rev FROM tbl_revision WHERE revision_status != 2 and drawing_id = " . $row['drawing_id'];

				$cnt_row = brp_mysqli_fetch_assoc($dbcon->query($qry));

				$use_qry = "SELECT count(product_id) as use_rev FROM product_mst WHERE product_status != 2 and drawing_id = " . $row['drawing_id'];

				$use_row = brp_mysqli_fetch_assoc($dbcon->query($use_qry));

				//if($delete_btn_per){
				if(in_array(ADMINISTRATOR_DRAWING_DELETE,$bulkAccessArray)){
					if($cnt_row['totol_rev'] == '0' && $use_row['use_rev'] == '0'){

						$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_po('.$row['drawing_id'].')"><i class="fa fa-trash-o"></i></button>';
					}
				}
				if($row['drawing_status']=="0"){
					$st_btn ='<button class="btn btn-xs btn-success" title="In-Active" data-toggle="tooltip" data-placement="top" onclick="change_drawing_status('.$row['drawing_id'].',1)"><i class="fa fa-check"></i></button>';
				}else if($row['drawing_status']=="1"){
					$st_btn ='<button class="btn btn-xs btn-warning" title="Active" data-toggle="tooltip" data-placement="top" onclick="change_drawing_status('.$row['drawing_id'].',0)"><i class="fa fa-check"></i></button>';
				}

				$view_image='<a class="btn btn-xs btn-info" title="View Image" data-toggle="tooltip" data-id="" data-placement="top" href="javascript:void(0)" onClick="view_drawing_image_list('.$row['drawing_id'].')"><i class="fa fa-eye"></i></a>';
				
				$row_data[] = $edit.' '.$st_btn.' '.$view_image. " ".$delete. " ".$btn_checked_by. " ".$btn_prepared_by. " ".$btn_approved_by;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
			}
		}
		else if(strtolower($POST['mode']) == "add") {
			$query="select * from tbl_invoicetype where status=0 and type_id=20 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);

			$query1="select * from  tbl_invoicetype where invoicetype_id=".$row['invoicetype_id'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			

			$getspecialConfiguration=getspecialConfiguration($dbcon);
			
			if($getspecialConfiguration['invoite_permission']==1){
				$queryno="select max(invoite_series) as sers from  tbl_drawing where drawing_status=0 and invoite_no='".$POST['drawing_number']."'";
				$resmo=$dbcon->query($queryno);
				$rowsmo=mysqli_fetch_assoc($resmo);
				if(!empty($rowsmo['sers'])){
					$id=$rowsmo['sers']+1;
				}else{
					$id=1;
				}
			}else{
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$row['invoicetype_id']);
			}
			$auto_no=str_pad($id,3,"0",STR_PAD_LEFT);

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			
			if($getspecialConfiguration['jet_technologies_permission']==1){
				$info['drawing_number']	= $POST['drawing_number'];
			}else{
				$info['drawing_number']	= $POST['drawing_number'].$auto_no;
			}
			
			$info['drawing_title']	= $POST['drawing_title'];
			$info['vender_id']	= $POST['vender_id'];
			$info['drawing_size']		= $POST['drawing_size'];
			$info['drawing_scale']	= $POST['drawing_scale'];
			$info['drawing_location']	= $POST['drawing_location'];
			$info['sales_order_id']	= $POST['sales_order_id'];
			$info['remark']	= $POST['remark'];
			$info['approve_status']	= 3;
			
			$info['invoite_no']	= $POST['drawing_number'];
			$info['invoite_series']	= $id;

			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['drawing_status']	= 0;
			$info['approve_status']	= 1;
			$info['prepared_by_user_id']	= $_SESSION['user_id'];
			$info['prepared_date'] = date('Y-m-d H:i:s');
			$info['cdate']	= date('Y-m-d');
			
			$inserpoid=add_record('tbl_drawing', $info, $dbcon, $branch_id);
			
			if($inserpoid){
				

				$dbcon->query("UPDATE tbl_revision SET revision_status = 0, drawing_id = ".$inserpoid." WHERE revision_status = 3");

				$dbcon->query("UPDATE tbl_drawing_revision_image SET drawing_revision_status = 0, drawing_id = ".$inserpoid." WHERE drawing_revision_status = 3 and type = 1");


				$qry_trn = "SELECT * from tbl_revision WHERE revision_status = 0 AND drawing_id = " . $inserpoid;
				$trn_res = $dbcon->query($qry_trn);
				while($trn_row = brp_mysqli_fetch_array($trn_res)){
					$dbcon->query("UPDATE tbl_drawing_revision_image SET drawing_id = ".$inserpoid." WHERE drawing_revision_status = 0 and revision_id = " . $trn_row['revision_id']);					
				} 

				$_SESSION['revision_id']='';
				$_SESSION['revision_number']='';
				unset($_SESSION['revision_id']);
				unset($_SESSION['revision_number']);

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
				$info['vender_id']		= $POST['vender_id'];
				$info['drawing_size']	= $POST['drawing_size'];
				$info['drawing_scale']	= $POST['drawing_scale'];
				$info['drawing_location']	= $POST['drawing_location'];
				$info['sales_order_id']	= $POST['sales_order_id'];
				$info['remark']			= $POST['remark'];
				$info['muser_id']		= $_SESSION['user_id'];
				$info['mdate']			= date("Y-m-d H:i:s");
				$info['company_id']		= $_SESSION['company_id'];

				
				$updateid1=update_record('tbl_drawing', $info,"drawing_id=".$POST['eid'] , $dbcon, $branch_id);
			
				if($updateid1)
				{	
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
					$where = '  and revision_status=3';
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
						$st_btn='<button type="button" class="btn btn-xs btn-success" title="In-Active" data-toggle="tooltip" data-placement="top" onclick="change_revision_status('.$rel['revision_id'].',1)"><i class="fa fa-check"></i></button>';
					}
					else{
						$st_btn='<button type="button" class="btn btn-xs btn-warning" title="Active" data-toggle="tooltip" data-placement="top" onclick="change_revision_status('.$rel['revision_id'].',0)"><i class="fa fa-check"></i></button>';
					}

					$delete='<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_revision('.$rel['revision_id'].')"><i class="fa fa-trash-o"></i></button>';

					$view_image = '<a class="btn btn-xs btn-info" title="View Image" data-toggle="tooltip" data-id="'.$rel['revision_id'].'" data-placement="top" href="javascript:void(0)" onClick="view_revision_image('.$rel['revision_id'].')"><i class="fa fa-eye"></i></a>';
					//$r=get_product_tax($dbcon,$rel['purchaseordertrn_id']);
					//$total=$rel['pqty']*$rel['product_purchase_rate'];

				$btn_checked_by = "";
				$btn_prepared_by = "";
				$btn_approved_by = "";

				if($rel['approve_status']=='0'){
					
					if(in_array(ADMINISTRATOR_DRAWING_CHECKED,$bulkAccessArray)){
						$btn_prepared_by ='<button type="button" class="btn btn-xs btn-success" title="Prepared Revision" data-toggle="tooltip" data-placement="top" onclick="change_revision_approve_status('.$rel['revision_id'].',1)"><i class="fa fa-check"></i> Prepared</button>';
					}
				}else if($rel['approve_status']=='1'){
					
					if(in_array(ADMINISTRATOR_DRAWING_APPROVED,$bulkAccessArray)){
						$btn_checked_by ='<button type="button" class="btn btn-xs btn-success" title="Checked Revision" data-toggle="tooltip" data-placement="top" onclick="change_revision_approve_status('.$rel['revision_id'].',2)"><i class="fa fa-check"></i> Checked</button>';
					}
					
				}else if($rel['approve_status']=='2'){
				
					if(in_array(ADMINISTRATOR_DRAWING_PREPARED,$bulkAccessArray)){
						$btn_approved_by ='<button type="button" class="btn btn-xs btn-success" title="Approve Revision" data-toggle="tooltip" data-placement="top" onclick="change_revision_approve_status('.$rel['revision_id'].',3)"><i class="fa fa-check"></i> Approve</button>';
					}
				}
				 
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
							'.$view_image.' '.$st_btn.' '.$delete. " ".$btn_checked_by. " ".$btn_prepared_by. " ".$btn_approved_by.'
						</td>
				
					</tr>';
					$i++;
				}
			}else{
				 echo '<tr class="text-center"><td colspan="5">NO DATA FOUND</td></tr>';
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
								<th class="text-center" width="25%">Image Name</th>
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
							'.$rel['image_name'].'
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

		else if(strtolower($POST['mode'])== "view_revision_temp_image"){
			$qry="SELECT * FROM `tbl_drawing_revision_image` Where drawing_revision_status = 3 and `company_id`='".$_SESSION['company_id']."' AND type = 2";

				$result=$dbcon->query($qry);

				echo '<div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th class="text-center" width="10%">SR No.</th>
								<th class="text-center" width="25%">Image Name</th>
								<th class="text-center" width="25%">View</th>
								<th class="text-center" width="25%">Action</th>
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
							'.$rel['image_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$filetype.'
						</td>

						<td style="vertical-align:top;" class="text-center">
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_image('.$rel['drawing_image_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>					
					</tr>';
					$i++;
				}
			}else{
				 echo '<tr class="text-center"><td colspan="4" style="text-align:center">NO DATA FOUND</td></tr>';
			}
		}
		else if(strtolower($POST['mode'])== "view_drawing_image")
			{
				$id = $POST['id']; 

				if(!empty($id)){	
					$where = ' and drawing_revision_status = 0 and type = 1 and drawing_id= "'.$id.'" ';
				}else{
					$where = ' and drawing_revision_status = 3 and type = 1';
				}
				

				// $qry="SELECT * FROM `tbl_drawing_revision_image` Where `company_id`='".$_SESSION['company_id']."'$where ";
				$qry="SELECT * FROM `tbl_drawing_revision_image` Where `company_id`='".$_SESSION['company_id']."'$where ";

				$result=$dbcon->query($qry);

				echo '<div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th class="text-center" width="10%">SR No.</th>
								<th class="text-center" width="25%">Image Name</th>
								<th class="text-center" width="25%">View</th>
								<th class="text-center" width="25%">Action</th>
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
							'.$rel['image_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$filetype.'
						</td>

						<td style="vertical-align:top;" class="text-center">
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_image('.$rel['drawing_image_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>					
					</tr>';
					$i++;
				}
			}else{
				 echo '<tr class="text-center"><td colspan="4" style="text-align:center">NO DATA FOUND</td></tr>';
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
		else if(strtolower($POST['mode']) == "change_revision_approve_status") {
			
			$id = $POST['eid'];
			$info['approve_status'] = $POST['status'];
			$info['muser_id'] = $_SESSION['user_id'];
			$info['mdate'] = date('Y-m-d H:i:s');

			if($POST['status'] == '1'){
				$info['prepared_by_user_id'] = $_SESSION['user_id'];
				$info['prepared_date'] = date('Y-m-d H:i:s');
			}else if($POST['status'] == '2'){
				$info['checked_by_user_id'] = $_SESSION['user_id'];
				$info['checked_date'] = date('Y-m-d H:i:s');
			}else if($POST['status'] == '3'){
				$info['approved_user_id'] = $_SESSION['user_id'];
				$info['approved_date'] = date('Y-m-d H:i:s');
			}

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
		else if(strtolower($POST['mode']) == "change_drawing_approve_status") {
			
			$id = $POST['eid'];
			$info['approve_status'] = $POST['status'];
			$info['muser_id'] = $_SESSION['user_id'];
			$info['mdate'] = date('Y-m-d H:i:s');

			if($POST['status'] == '1'){
				$info['prepared_by_user_id'] = $_SESSION['user_id'];
				$info['prepared_date'] = date('Y-m-d H:i:s');
			}else if($POST['status'] == '2'){
				$info['checked_by_user_id'] = $_SESSION['user_id'];
				$info['checked_date'] = date('Y-m-d H:i:s');
			}else if($POST['status'] == '3'){
				$info['approved_user_id'] = $_SESSION['user_id'];
				$info['approved_date'] = date('Y-m-d H:i:s');
			}
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

			$sql1 = "UPDATE `tbl_revision` SET revision_status = 2 WHERE drawing_id='".$POST['eid']."' ";	
			$dbcon->query($sql1);	
			
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "save_drawing_image") {
			
			$temp = 1;

			if(!empty($POST['eid'])){
				$temp = 0;				
			}

			$file_id = "";

			if($POST['form'] == "purchaseorder_add"){
				if(!empty($_FILES['dr_file']['tmp_name'][0])) {
					$imgresp = upload_drawing_document($_FILES['dr_file']['tmp_name'][0],$dbcon,$POST['eid'],$POST['revision_id'],$POST['image_name'],$POST['temp_img_type'],$temp);

					if($imgresp > 0){
						echo "1";
					}else{
						echo "0";
					}
				}else{
					echo "2";
				}
			}else{
				if(!empty($_FILES['revision_file']['tmp_name'][0])) {
					$imgresp = upload_revision_document($_FILES,$dbcon,$POST['eid'],$POST['revision_id'],$POST['r_image_name'],$POST['temp_img_type'],$temp);

					if($imgresp > 0){
						echo "1";
					}else{
						echo "0";
					}
				}else{
					echo "2";
				}
			}

			
		}	
		else if(strtolower($POST['mode']) == "delete_revision") {
			$image_de = "select * from tbl_drawing_revision_image where revision_id=".$POST['eid'];
			$result = $dbcon->query($image_de);
			while($row = brp_mysqli_fetch_array($result)){
				unlink($row['file_path'].$row['file_name']);
			}
			
			$info['revision_status'] = 2;
			$updatetrancationid=update_record('tbl_revision', $info,"revision_id=".$POST['eid'], $dbcon);	
			
			
			$sql1 = "UPDATE `tbl_drawing_revision_image` SET drawing_revision_status = 2 WHERE revision_id='".$POST['eid']."' ";	
			$dbcon->query($sql1);	
			
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "delete_image") {
			$id		= $POST['id'];

			$image_de = "select * from tbl_drawing_revision_image where drawing_image_id=".$id;
			$result = $dbcon->query($image_de);
			$row = brp_mysqli_fetch_array($result);

			unlink($row['file_path'].$row['file_name']);

			// $sql = "DELETE FROM `tbl_drawing_revision_image` WHERE drawing_image_id='".$id."' ";	
			$sql = "UPDATE `tbl_drawing_revision_image` SET drawing_revision_status = 2 WHERE drawing_image_id='".$id."' ";	
			$updatetrancationid = $dbcon->query($sql);		
			
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode'])== "get_dr_history")
		{
			$drawing_id = $POST['drawing_id']; // as purchase id
			
			$sql = "SELECT * FROM tbl_drawing  WHERE drawing_id ='".$drawing_id."' AND company_id='".$_SESSION['company_id']."'";
			
			$vrow=$dbcon->query($sql);
			$rel=mysqli_fetch_assoc($vrow);

			
			$created_date='';
			$modify_date='';
			$approve_date='';
			$prepared_by_name= find_user_name($dbcon,$rel['prepared_by_user_id']);
			$checked_by_name= find_user_name($dbcon,$rel['checked_by_user_id']);
			$approved_name= find_user_name($dbcon,$rel['approved_user_id']);

			if(!empty($rel['prepared_date']) && $rel['prepared_date']!="1970-01-01 00:00:00" && $rel['prepared_date']!="0000-00-00 00:00:00")
			{
				$created_date=date('d-M-Y', strtotime($rel["prepared_date"]));
			}
			if(!empty($rel['checked_date']) &&$rel['checked_date']!="1970-01-01 00:00:00" && $rel['checked_date']!="0000-00-00 00:00:00")
			{
				$modify_date=date('d-M-Y', strtotime($rel["checked_date"]));
			}

			if(!empty($rel['approved_date']) &&$rel['approved_date']!="1970-01-01 00:00:00" && $rel['approved_date']!="0000-00-00 00:00:00")
			{
				$approve_date=date('d-M-Y', strtotime($rel["approved_date"]));
			}

				
			if($rel['drawing_status']=='0'){
			 	$stage = 'Active';
			 }elseif($rel['stage']=='1'){
			 	$stage = 'In-Active';
			 }			
			echo '<section class="panel">
                     <div class="panel-body bio-graph-info">
                         <h1>Drawing History</h1>
                         <div class="row">
                             <div class="bio-row">
                                 <p><span>Prepared By </span>: '.$prepared_by_name.'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Prepared Date </span>: '.$created_date.'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified By </span>: '.$checked_by_name.'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified Date</span>: '.$modify_date.'</p>
                             </div>
                            
                             <div class="bio-row">
                                 <p><span>Approved By </span>: '.$approved_name.'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved Date </span>: '.$approve_date.'</p>
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

			$info['revision_number'] = $POST['revision_no'];
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

			if($revision_insertid){
				$dbcon->query("UPDATE tbl_drawing_revision_image SET drawing_revision_status = 0, revision_id = " .$revision_insertid. ", drawing_id = ".$dr_id." WHERE drawing_revision_status = 3  and type = 2");

				$arr['msg']="1";
				$arr['revision_id_ref']=$revision_insertid;
				$arr['revision_number_ref']=$POST['revision_number'];

				$_SESSION['revision_id']=$revision_insertid;
				$_SESSION['revision_number']=$POST['revision_number'];
			}else{
				$arr['msg']="0";
			}

			echo json_encode($arr);	
		}else if(strtolower($POST['mode'])== "load_revision_no"){
			$sql = "SELECT drawing_number FROM `tbl_drawing` as po Where drawing_id=".$POST['eid'];
			$vrow=$dbcon->query($sql);
			$rel=mysqli_fetch_assoc($vrow);

			$sql1 = "SELECT count(revision_id) as tcount FROM `tbl_revision` as po Where drawing_id=".$POST['eid'];
			$vrow1=$dbcon->query($sql1);
			$rel1=mysqli_fetch_assoc($vrow1);
			$count=$rel1['tcount']+1;
			echo $rel['drawing_number'].'/R-'.$count;
		}


    }
}

function upload_drawing_document($FILES,$dbcon,$inserpoid=0,$revision_id,$image_name="",$type=0,$temp = 0){
		if(!empty($_FILES['dr_file']['tmp_name'][0])) {
			$rand=rand(0,999999);
			$test = explode('.', $_FILES["dr_file"]["name"]);
			$ext = brp_strtolower(end($test));
			$name = $inserpoid.'_'.$revision_id.'_'.$rand. '.' . $ext;
             $path='../../../view/upload/drawing_images/';

			$location = $path . $name;  
			move_uploaded_file($_FILES["dr_file"]["tmp_name"], $location);

			$drowing_images['drawing_id'] 	= $inserpoid;
			$drowing_images['revision_id'] 	= $revision_id;
			$drowing_images['file_name'] 	= $name;
			$drowing_images['file_path'] 	= $path;
			$drowing_images['user_id']		= $_SESSION['user_id'];
			$drowing_images['company_id']	= $_SESSION['company_id'];
			$drowing_images['cdate']		= date('Y-m-d H:i:s');
			$drowing_images['drawing_revision_status'] = 0;
			$drowing_images['image_name'] = $image_name;
			$drowing_images['type'] = $type;
			if($temp){
				$drowing_images['drawing_revision_status'] = 3;
			}
			

			$inserid = add_record('tbl_drawing_revision_image', $drowing_images, $dbcon);

			return $inserid;
		}
}
function upload_revision_document($FILES,$dbcon,$inserpoid,$revision_id,$image_name="",$type=0,$temp = 0){
		if(!empty($_FILES['revision_file']['tmp_name'])) {
			$rand=rand(0,999999);

			$test = explode('.', $_FILES["revision_file"]["name"]);
			$ext = brp_strtolower(end($test));
			$name = $inserpoid.'_'.$revision_id.'_'.$rand. '.' . $ext;
			$path='../../../view/upload/drawing_images/';
			$location = $path . $name;  
			move_uploaded_file($_FILES["revision_file"]["tmp_name"], $location);

			$drowing_images['drawing_id'] 	= $inserpoid;
			$drowing_images['revision_id'] 	= $revision_id;
			$drowing_images['file_name'] 	= $name;
			$drowing_images['file_path'] 	= $path;
			$drowing_images['user_id']		= $_SESSION['user_id'];
			$drowing_images['company_id']	= $_SESSION['company_id'];
			$drowing_images['cdate']		= date('Y-m-d H:i:s');
			$drowing_images['drawing_revision_status'] = 0;
			$drowing_images['image_name'] = $image_name;
			$drowing_images['type'] = $type;
			if($temp){
				$drowing_images['drawing_revision_status'] = 3;
			}
			$inserid = add_record('tbl_drawing_revision_image', $drowing_images, $dbcon);

			return $inserid;
		}
}

?>