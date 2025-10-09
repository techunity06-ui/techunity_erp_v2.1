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
		$where.=" sr.stock_status=3";

		/*$where.=" and tsr.company_id=".$_SESSION['company_id'];
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
					
			$where.="  and tsr.cdate >= '".date('Y-m-d',strtotime($s_date[0]))."' AND tsr.cdate <= '".date('Y-m-d',strtotime($s_date[1]))."'";*/
			$appData = array();
			$i=1;
			$aColumns = array('sr.stock_receive_id','sr.stock_date','sr.product_id','sr.base_stock','sr.stock_status','sr.ref_name','p.product_name');
			$sIndexColumn = "sr.stock_receive_id";
			$isWhere = array($where);
			$sTable = "tbl_stock_receive as sr";
			$isJOIN = array('left join product_mst as p on p.product_id=sr.product_id');
			$hOrder = "sr.stock_receive_id";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			//echo "<pre>"; print_r($sqlReturn);
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['stock_receive_id'];
				$row_data[] = date('d M, Y',strtotime($row['stock_date']));
				$row_data[] = $row['product_name'];
				$row_data[] = $row['base_stock'];
				$row_data[] = $row['ref_name'];
				$stock_receive_id = $row['stock_receive_id'];	
				$ref_name = $row['ref_name'];			
				$app_btn='';				
				$app_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve GRN" data-toggle="tooltip" data-placement="top" onclick="change_stock_status('."'".$stock_receive_id."'".','."'".$ref_name."'".')"><i class="fa fa-check"></i></button>';

				
				if($row['stock_status']=='1'){
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
		else if(strtolower($POST['mode']) == "add_apprv_hist") {
			
			

			$approve_status = $POST['approve_status'];
			$approve_remark  = $POST['approve_remark'];
			$stock_receive_id = $POST['stock_receive_id'];
			$ref_name = $POST['ref_name'];
			$logs['stock_receive_id'] = $stock_receive_id;
			$logs['approve_remark'] = $approve_remark;
			$logs['approve_status'] = $approve_status;
			$logs['cdate']		= date("Y-m-d H:i:s");
			$logs['user_id']	= $_SESSION['user_id'];
			$logs['company_id']	= $_SESSION['company_id'];
			$logs['branch_id']	= $_SESSION['branch_id'];
			$req_id = add_record('tbl_store_receive_aprv_log',$logs, $dbcon);
			
			if($approve_status == '1')
			{		
				$q = "select * from tbl_stock_receive where stock_receive_id = '$stock_receive_id'"; 
				$result=$dbcon->query($q);
				if(brp_mysqli_num_rows($result)>0)
				{
					$row = brp_mysqli_fetch_array($result);
					
					//echo "<pre>"; print_r($row);
					$store_data['stock_date']= $row['stock_date'];
					$store_data['product_id']= $row['product_id'];
					$store_data['base_unit']= $row['base_unit'];
					$store_data['base_stock']= $row['base_stock'];
					$store_data['used_base_stock']= $row['used_base_stock'];
					$store_data['convert_unit']= $row['convert_unit'];
					$store_data['convert_stock']= $row['convert_stock'];
					$store_data['used_convert_stock']= $row['used_convert_stock'];
					$store_data['stock_flage']= $row['stock_flage'];
					$store_data['godown_id']= $row['godown_id'];
					$store_data['ref_name']= $row['ref_name'];
					$store_data['ref_id']= $row['ref_id'];
					$store_data['stock_status']= 0;
					$store_data['cdate']= $row['cdate'];
					$store_data['user_id']= $row['user_id'];
					$store_data['branch_id']= $row['branch_id'];
					$store_data['perent_id']= $row['perent_id'];
					$store_data['reserve_id']= $row['reserve_id'];
					$store_data['batch_no']= $row['batch_no'];	
					$store_data['customer_id'] = $row['customer_id'];
					
					
				if(strtolower($ref_name) == 'tbl_reserve_stock' || strtolower($ref_name) == 'tbl_grn_trn' )
				{
					//echo "jayesh <pre>". print_r($_SESSION);
					$info_rese['reserve_date']		= $row['stock_date'];
					$info_rese['product_id']		= $row['product_id'];
					$info_rese['godown_id']			= $row['godown_id'];
					$info_rese['base_unit']			= $row['base_unit'];
					$info_rese['base_stock']		= $row['base_stock'];
					$info_rese['convert_unit']		= $row['convert_unit'];
					$info_rese['convert_stock']		= $row['base_stock'];
					$info_rese['stock_flage']		= $row['stock_flage'];
					$info_rese['request_id']		= $row['ref_id'];
					$info_rese['ref_name']			= $row['ref_name'];
					$info_rese['ref_id']			= $row['ref_id'];
					$info_rese['p_id']				= $row['perent_id'];
					$info_rese['stock_id']			= 0;
					$info_rese['stock_receive_id']			= $row['stock_receive_id'];
					$info_rese['cdate']				= date("Y-m-d H:i:s");
					$info_rese['user_id']			= $_SESSION['user_id'];
					$info_rese['company_id']		= $_SESSION['company_id'];
					$info_rese['branch_id']			= $row['branch_id'];
					
					$info_rese['p_id']		= $row['p_id'];	
					$info_rese['request_id']	= $row['request_id'];		
					$info_rese['customer_id'] = $row['customer_id'];
					
					//echo "<pre>" ; print_r($info_rese); die;

					$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row['branch_id']);
				}
				
				//echo "<pre>"; print_r($store_data);
												
					$req_id = add_record('tbl_stock_trn',$store_data,$dbcon);					
				}
				
				$info['stock_status']= $logs['approve_status'];				
				$updateid=update_record('tbl_stock_receive', $info,"stock_receive_id=".$stock_receive_id, $dbcon, $branch_id);				
			}
			
			
		}
		else if(strtolower($POST['mode']) == "load_hist_datatable") {

			/*$where='';
			$where.=" log.store_request_id=".$POST['release_id'];
			$where.=" AND log.release_type=1";*/
			$appData = array();
			$i=1;
			$aColumns = array('log.store_receive_aprv_log_id', 'log.stock_receive_id', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
			$sIndexColumn = "log.store_receive_aprv_log_id";
			//$isWhere = array(" ".$where." ");
			$sTable = "tbl_store_receive_aprv_log as log";			
			$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
			$hOrder = "log.store_receive_aprv_log_id desc";
			include($include.'/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['grn_id'];

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