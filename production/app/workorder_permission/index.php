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
		}else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(brp_strtolower($POST['mode']) == "fetch") {
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];

			$wher = '';
			if($_SESSION['user_type']!=2){
			$wher=" and `sep`.`user_id`='".$_SESSION['user_id']."'";
			}
			$wher="AND r.approval_status ='0' AND STATUS = 3 AND r.main_request != 1 and r.company_id = " . $_SESSION['company_id'];

			/*$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$where_db = check_branch('estimate', $branch_id);
			$where.="  and sales_order_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND sales_order_date<='".date('Y-m-d',strtotime($s_date[1]))."'".$where_db; 
*/

		  $appData = array();
		  $i=1;
	
		  /*$aColumns = array('sep.*,l.l_name,p.product_name');
		  $sIndexColumn = "sep.sp_id";
		  $isWhere = array("sep.company_id='".$_SESSION['company_id']."'".$wher);
		  $sTable = "tbl_set_main_process as sep";			
		  $isJOIN = array(
		  	'left join tbl_ledger as l ON sep.vendor_id = l.l_id',
		  	'inner join tbl_request_product as r ON sep.sp_id = r.sp_id',
		  	'left join product_mst as p ON sep.product_id = p.product_id',
		  );
		  $hOrder = "sep.po_req_no desc";
		$hGroupby = array("sep.sp_id");*/
		

		  // $aColumns = array('sep.sp_id','sep.po_req_no','p.product_name','r.rp_req_date','r.rp_req_type','r.rp_req_qty','r.rp_po_qty','sep.in_process_qty_main');

		  $aColumns = array('sep.po_req_no','p.product_name','p.product_icode','r.approval_status','r.main_request','r.rp_id','sep.sp_id','r.company_id','r.rp_req_date','sep.sp_status', 'r.rp_req_type','r.rp_req_qty','r.rp_po_qty','sep.in_process_qty_main');

		  $sIndexColumn = "r.rp_id";
		  $isWhere = array("r.company_id='".$_SESSION['company_id']."'".$wher);
		  $sTable = "tbl_request_product as r";			
		  $isJOIN = array(
		  	'left join tbl_set_main_process as sep ON r.sp_id = sep.sp_id',
		  	'left join product_mst as p ON r.rp_pid = p.product_id',
		  );
		 $hOrder = "r.rp_id desc";
		 $hGroupby = array("r.rp_id");
		
		  /*END*/
		include($include."pagging.php");
		 
		  $id=1;
		  foreach($sqlReturn as $row) {
		  	//echo "<pre>"; print_r($row);
		  	$row_data = array();
				if($row['sp_status']=='1'){
					$stage = 'Planning Done';
				}else{
					$stage = 'Partially Planning ';
				}
				if($row['l_name']==''){
					$lname = '--';
				}
				else{
					$lname = $row['l_name'];
				}
				
				/*$wquery="select * from tbl_request_product where rp_id=".$row['rp_id']." group by sp_id"; 
				$wresult=$dbcon->query($wquery);
				$wrow=brp_mysqli_fetch_assoc($wresult); */
		  	
		  		$row_data[] = $row['po_req_no'];
		  		$row_data[] = date('d M, Y',strtotime($row['rp_req_date']));
		  		$row_data[] = $row['product_name'] . " -- (".$row['product_icode'].")";
		  		$row_data[] = $row['rp_req_type'];  
		  		$row_data[] = $row['rp_req_qty'];
		  		$row_data[] = $row['rp_po_qty'];
		  		$row_data[] = number_format($row['rp_req_qty'] - $row['in_process_qty_main'],5);
		  		$row_data[] = '0.00';
		  		$row_data[] = '0.00';
		  		$row_data[] = $stage;
		  		$row_data[] = $lname;
		  		$row_data[] = '--';
		  		
		  		$row_data[] = '<a class="btn btn-xs btn-warning"  data-original-title="View" data-toggle="tooltip"  href="'.ROOT.PRODUCTION_ROOT.'workorder_permission/'.$row['sp_id'].'"><i class="fa fa-eye"></i></a>';
		  		/*<button class="btn btn-xs btn-success" data-original-title="Approve." data-toggle="tooltip" data-placement="top" onclick="get_item_information('.$row['sp_id'].','.$row['product_id'].','.$row['vendor_id'].');"><i class="fa fa-exclamation-triangle"></i></button>';
		  		<a class="btn btn-xs btn-warning" data-original-title="Work Order Details" data-toggle="tooltip" onclick="get_item_information('.$row['sp_id'].','.$row['product_id'].','.$row['vendor_id'].');"><i class="fa fa-eye"></i></a>';
		  		/*<a class="btn btn-xs btn-warning" data-original-title="Reports" data-toggle="tooltip" onclick="reports();"><i class="fa fa-eye"></i></a>
		  		<a class="btn btn-xs btn-warning" data-original-title="Notes" data-toggle="tooltip"  onclick="notes();"><i class="fa fa-eye"></i></a>
		  		<a class="btn btn-xs btn-warning" data-original-title="Login Details" data-toggle="tooltip" onclick="get_vendor_details('.$row['sp_id'].');"><i class="fa fa-eye"></i></a>';*/

		  	$appData[] = $row_data;
		  	$id++;
		  }
		  $output['aaData'] = $appData;
		  echo json_encode( $output );
		}
		else if(brp_strtolower($POST['mode']) == "add") {
		}		
		
		else if(brp_strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=".$POST['type_id']." and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		
		else if(brp_strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];
			echo getrequiredproduct($dbcon,'',' and product_type='.$type_id.'');
		}

		else if(brp_strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
		
		else if(brp_strtolower($POST['mode'])== "get_po_login")
		{
			$id = $POST['id']; // as table id
			$sql = "SELECT `u`.`user_name` as prepared_by,`po`.`cdate`, `mu`.`user_name` as last_modify_by, `po`.`mdate`, `po`.`sp_status` as stage FROM `tbl_set_main_process` as po left join `users` as u ON  `po`.`user_id` = `u`.`user_id` left join `users` as mu ON  `po`.`muser_id` = `mu`.`user_id`  Where `po`.`sp_id`='".$id."' and `po`.`company_id`='".$_SESSION['company_id']."'";
			
			$vrow=$dbcon->query($sql);
			$rel=brp_mysqli_fetch_assoc($vrow);
				
			if($rel['stage']=='1'){
			 	$stage = 'Approved';
			 }else{
			 	$stage = 'Pending';
			 }
					
			echo '<section class="panel">
                     <div class="panel-body bio-graph-info">
                         <h1>Login History</h1>
                         <div class="row">
                             <div class="bio-row">
                                 <p><span>Prepared By </span>: '.$rel["prepared_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Prepared Date </span>: '.(($rel["cdate"]!='' && $rel['cdate']!="1970-01-01" && $rel['cdate']!="0000-00-00")?date('d-M-Y',strtotime($rel["cdate"])):'').'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified By </span>: '.$rel["last_modify_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified Date</span>: '.(($rel["mdate"]!='' && $rel['mdate']!="1970-01-01" && $rel['mdate']!="0000-00-00")?date('d-M-Y',strtotime($rel["mdate"])):'').'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved By </span>: </p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved Date </span>: </p>
                             </div>
                             <div class="bio-row">
                                 <p><span> Stage </span>: '.$stage.'</p>
                             </div>
                             
                         </div>
                     </div>
                 </section>';
		}

		else if(brp_strtolower($POST['mode'])== "get_item_selected_information")
		{
			$id = $POST['id'];
			$product_id = $POST['product_id'];
			$vendor_id = $POST['vendor_id'];


			$sql = "SELECT sep.*,`l`.`l_name`, `pm`.`product_type`, `pm`.`product_name`,`pm`.`product_desc` FROM `tbl_set_main_process` as sep 
				    left join tbl_ledger as l ON `sep`.`vendor_id` = `l`.`l_id` 
				    left join product_mst as pm ON `sep`.`product_id` = `pm`.`product_id` 
				    WHERE  `sep`.`sp_id`='".$id."' AND `sep`.`company_id`='".$_SESSION['company_id']."'";
			$rel=$dbcon->query($sql);
			$result=brp_mysqli_fetch_assoc($rel);

			if($result['sp_status']=='0'){
				$status='Pending';
			}else{
				$status='Approved';
			}

			$arr['po_req_no'] = $result['po_req_no'];
			$arr['po_req_date'] = date('d-m-Y', strtotime($result['po_req_date']));
			$arr['so_no'] = 'NA';
			$arr['so_date'] = date('d-m-Y');
			$arr['status'] = $status;
			$arr['vender_id'] =  $result['l_name'];
			$arr['vendor_po_number'] = $result['po_no'];
			$arr['vender_po_date'] = date('d-m-Y', strtotime($result['po_date']));
			$arr['product_type'] = get_pro_type_name($result['product_type']);
			$arr['product_id'] = $result['product_name'];
			$arr['item_description'] = $result['product_desc'];
			$arr['order_start_date'] = date('d-m-Y');
			$arr['order_delivery_date'] = date('d-m-Y');
			$arr['ds_number'] = 'NA';
			$arr['bom_no'] = $result['bom_no'];
			$arr['bom_id'] = $result['bom_id'];
			$arr['order_qty'] = $result['rp_req_qty'];
			$arr['remark'] = '0';
			$arr['report']= '<a class="btn btn-primary" data-original-title="view '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'work_order_new_print/'.$result['sp_id'].'">Live Production Report</a>';
			
			$arr['vendorId'] = $vendor_id;
			echo json_encode($arr);
		}
		
	}

    
}




?>