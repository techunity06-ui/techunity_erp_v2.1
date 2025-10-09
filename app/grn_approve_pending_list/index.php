<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
//{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
//	{
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
		$where.=" tsr.release_type=1";

		$where.=" and tsr.company_id=".$_SESSION['company_id'];
		switch($POST['release_status']){
			case "0":
			$where.="  and release_status=0";
			break;
			
			case "1":
			$where.="  and release_status=1";
			break;
			
			default:
			$where.="";
		}
					
			$where.="  and tsr.cdate >= '".date('Y-m-d',strtotime($s_date[0]))."' AND tsr.cdate <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('release_id','issue_no','issue_date','user_name','branch_name','release_status');
			$sIndexColumn = "release_id";
			$isWhere = array($where);
			$sTable = "tbl_store_release as tsr";
			$isJOIN = array('left join users as u on u.user_id=tsr.to_user_id','left join branch_mst as bms on bms.branch_id=tsr.branch_id');
			$hOrder = "tsr.release_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['issue_no'];
				$row_data[] = date('d M, Y',strtotime($row['issue_date']));
				$row_data[] = $row["user_name"];
				$row_data[] = $row['branch_name'];
				
				$app_btn='';
				
				if($row['release_status']=="1"){
					$app_btn='<button class="btn btn-xs btn-success" data-original-title="Approved Material Release" data-toggle="tooltip" data-placement="top" onclick="change_release_status('.$row['release'].',0,\''.$row['issue_no'].'\')"><i class="fa fa-check"></i></button>';
				}
				else{
					$app_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_release_status('.$row['release_id'].',1,\''.$row['issue_no'].'\')"><i class="fa fa-check"></i></button>';
				}
			
				
				if($row['release_status']=='1'){
					$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
				}
				else{
					$row_data[] = '<button class="btn btn-xs btn-warning">Approval Pending</button>';
				}	
				$row_data[] = $app_btn;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "load_hist_datatable") {

			$where='';
			$where.=" log.store_request_id=".$POST['release_id'];
			$where.=" AND log.release_type=1";
			$appData = array();
			$i=1;
			$aColumns = array('log.store_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.request_user_id');
			$sIndexColumn = "log.store_aprv_log_id";
			$isWhere = array(" ".$where." ");
			$sTable = "tbl_store_request_aprv_log as log";			
			$isJOIN = array('left join users as usr on usr.user_id=log.request_user_id');
			$hOrder = "log.store_aprv_log_id desc";
			include($include.'/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['user_name'];

				if($row['approve_status']=='1'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
				}
				else{
					$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Approve Pending</div>';
				}

				$row_data[] = nl2br($row['approve_remark']);
				$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}

		else if(strtolower($POST['mode']) == "load_release_dtl") {
			$qt_qry="select tsr.*,user_name,branch_name from  tbl_store_release as tsr
			left join users as u on u.user_id=tsr.to_user_id left join branch_mst as bms on bms.branch_id=tsr.branch_id
			where tsr.release_id=".$POST['release_id'];
			$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($qt_qry));

		//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td colspan="2"><strong>Issue No:</strong> '.$qt_rel['issue_no'].'</td>
			<td><strong>Issue_date:</strong> '.$qt_rel['issue_date'].'</td>
			</tr>
			<tr>
			<td colspan="2"><strong>User:</strong> '.$qt_rel['user_name'].'</td>
			<td><strong>Branch:</strong> '.$qt_rel['branch_name'].'</td>
			</tr>
			';
			$str.='</table></div>
			<hr/>
			';

			$qry="select tsr.*,product_name from  tbl_store_release_trn as tsr
			 left join product_mst as pmst on pmst.product_id=tsr.product_id
			where tsr.release_status = 0 and tsr.release_id=".$POST['release_id'];

			$result=$dbcon->query($qry);

			$cnt=brp_mysqli_num_rows($rel);

			if($cnt > 0){
				$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">';
			$str .='<tr>
				<th>Product</td>
				<th>Qty</td>
				<th>Returnable</td>
				</tr>';
			while($rel=brp_mysqli_fetch_assoc($result)){
				
				$str .='<tr>
				<td>'.$rel['product_name'].'</td>
				<td>'.$rel['release_qty'].'</td>
				<td>'.($rel['returnable'] == '1') ? 'Yes' : 'No'.'</td>
				</tr>';
			}

			$str.='</table></div>
			<hr/>
			';
			}
		

			$qt_rel['mod_comp_div_sec'] = $str;

			echo json_encode($qt_rel);
		}
		
?>