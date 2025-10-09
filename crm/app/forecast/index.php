<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
	
	if(strtolower($POST['mode']) == "fetch") {
		
		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where='';
		//$where.="  and fc.bom_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND fc.bom_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('fc.forecast_id', 'fc.f_year', 'f_mst.f_period_name', 'fc.f_target_amt', 'fc.f_target_qty', 'fc.cdate','fc.forecast_status','fc.user_id');
		$sIndexColumn = "fc.forecast_id";
		$isWhere = array("fc.forecast_status=0 ".$where." and fc.company_id in (0,$_SESSION[company_id])");
		$sTable = "tbl_forecast as fc";
		$isJOIN = array('left join forecast_period_mst as f_mst on f_mst.f_period_id=fc.f_period_id');
		$hOrder = "fc.forecast_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['f_year'];
			$row_data[] = $row['f_period_name'];
			$row_data[] = $row['f_target_amt'];
			$row_data[] = $row['f_target_qty'];
			
			$edit_btn='';$delete_btn='';$view_btn='';
			
			if($edit_btn_per){
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'forecast_edit/'.$row['forecast_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_forecast('.$row['forecast_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$view_btn='<a class="btn btn-xs btn-primary" data-original-title="Preview Forecast" data-toggle="tooltip" data-placement="top" href="'.ROOT.'forecast_view/'.$row['forecast_id'].'"><i class="fa fa-eye"></i></a>';
			
			$row_data[] = $view_btn.' '.$edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		//$info['t_id']				= $POST['t_id'];
		$info['f_by_id']			= $POST['f_by_id'];
		$info['f_year']				= $POST['f_year'];
		$info['f_target_period']	= $POST['f_target_period'];
		$info['f_period_id']		= $POST['f_period_id'];
		$info['f_target_amt']		= $POST['f_target_amt'];
		$info['f_target_qty']		= $POST['f_target_qty'];
		
		$info['cdate']				= date("Y-m-d H:i:s");
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		
		$inserid=add_record('tbl_forecast', $info, $dbcon);
		
		
		/* Add Ter TRN Data Start */	
		foreach ($POST['t_id'] as $key => $name) 
		{
			$inftrn['forecast_id']		= $inserid;
			$inftrn['t_id']				= $POST['t_id'][$key];
			$inftrn['ter_target_amt']	= $POST['ter_target_amt'][$key];
			$inftrn['ter_target_qty']	= $POST['ter_target_qty'][$key];
			$inftrn['user_id']			= $_SESSION['user_id'];
			if(floatval($inftrn['ter_target_amt']) || floatval($inftrn['ter_target_qty'])){
				$updtrnqry=add_record('tbl_f_ter_trn', $inftrn, $dbcon);
			}
		}
		/* Add Ter TRN Data End */
		
		/* Add User TRN Data Start */
		foreach ($POST['user_id'] as $key => $name) 
		{
			$usrtrn['forecast_id']		= $inserid;
			$usrtrn['user_id']			= $POST['user_id'][$key];
			$usrtrn['usr_target_amt']	= $POST['usr_target_amt'][$key];
			$usrtrn['usr_target_qty']	= $POST['usr_target_qty'][$key];
			$usrtrn['userid']			= $_SESSION['user_id'];
			if(floatval($usrtrn['usr_target_amt']) || floatval($usrtrn['usr_target_qty'])){
				$updtrnqry=add_record('tbl_f_user_trn', $usrtrn, $dbcon);
			}
		}
		/* Add User TRN Data End */
		
		/* Add Group TRN Data Start */
		foreach ($POST['pg_id'] as $key => $name) 
		{
			$grptrn['forecast_id']		= $inserid;
			$grptrn['pg_id']			= $POST['pg_id'][$key];
			$grptrn['grp_target_amt']	= $POST['grp_target_amt'][$key];
			$grptrn['grp_target_qty']	= $POST['grp_target_qty'][$key];
			$grptrn['user_id']			= $_SESSION['user_id'];
			if(floatval($grptrn['grp_target_amt']) || floatval($grptrn['grp_target_qty'])){
				$updtrnqry=add_record('tbl_f_grp_trn', $grptrn, $dbcon);
			}
		}
		/* Add Group TRN Data End */
		
		if($inserid){
			$resp['msg']='1';
		}
		else{
			$resp['msg']='0';
		}
		
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		//var_dump($POST);
		//exit;
		//$info['t_id']				= $POST['t_id'];
		$info['f_by_id']			= $POST['f_by_id'];
		$info['f_year']				= $POST['f_year'];
		$info['f_target_period']	= $POST['f_target_period'];
		$info['f_period_id']		= $POST['f_period_id'];
		$info['f_target_amt']		= $POST['f_target_amt'];
		$info['f_target_qty']		= $POST['f_target_qty'];
		
		$info['cdate']				= date("Y-m-d H:i:s");
		$info['user_id']			= $_SESSION['user_id'];
		
		/* Add Ter TRN Data Start */
		$deleteid=delete_record('tbl_f_ter_trn',"forecast_id=".$POST['eid'], $dbcon);	
		foreach ($POST['t_id'] as $key => $name) 
		{
			$inftrn['forecast_id']		= $POST['eid'];
			$inftrn['t_id']				= $POST['t_id'][$key];
			$inftrn['ter_target_amt']	= $POST['ter_target_amt'][$key];
			$inftrn['ter_target_qty']	= $POST['ter_target_qty'][$key];
			$inftrn['user_id']			= $_SESSION['user_id'];
			if(floatval($inftrn['ter_target_amt']) || floatval($inftrn['ter_target_qty'])){
				$updtrnqry=add_record('tbl_f_ter_trn', $inftrn, $dbcon);
			}
		}
		/* Add Ter TRN Data End */
		
		/* Add User TRN Data Start */
		$deleteid=delete_record('tbl_f_user_trn',"forecast_id=".$POST['eid'], $dbcon);	
		foreach ($POST['user_id'] as $key => $name) 
		{
			$usrtrn['forecast_id']		= $POST['eid'];
			$usrtrn['user_id']			= $POST['user_id'][$key];
			$usrtrn['usr_target_amt']	= $POST['usr_target_amt'][$key];
			$usrtrn['usr_target_qty']	= $POST['usr_target_qty'][$key];
			$usrtrn['userid']			= $_SESSION['user_id'];
			if(floatval($usrtrn['usr_target_amt']) || floatval($usrtrn['usr_target_qty'])){
				$updtrnqry=add_record('tbl_f_user_trn', $usrtrn, $dbcon);
			}
		}
		/* Add User TRN Data End */
		
		/* Add Group TRN Data Start */
		$deleteid=delete_record('tbl_f_grp_trn',"forecast_id=".$POST['eid'], $dbcon);	
		foreach ($POST['pg_id'] as $key => $name) 
		{
			$grptrn['forecast_id']		= $POST['eid'];
			$grptrn['pg_id']			= $POST['pg_id'][$key];
			$grptrn['grp_target_amt']	= $POST['grp_target_amt'][$key];
			$grptrn['grp_target_qty']	= $POST['grp_target_qty'][$key];
			$grptrn['user_id']			= $_SESSION['user_id'];
			if(floatval($grptrn['grp_target_amt']) || floatval($grptrn['grp_target_qty'])){
				$updtrnqry=add_record('tbl_f_grp_trn', $grptrn, $dbcon);
			}
		}
		/* Add Group TRN Data End */
		
		$updateid=update_record('tbl_forecast', $info,"forecast_id=".$POST['eid'] , $dbcon);
		
		
		if($updateid){
			$resp['msg']='2';
		}
		else{
			$resp['msg']='0';
		}
		
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['forecast_status']='2';
		$updateid=update_record('tbl_forecast', $info,"forecast_id=".$POST['forecast_id'] , $dbcon);
		
		
		if($updateid){
			echo 1;
		}
		else{
			echo 0;
		}
	}
	else if(strtolower($POST['mode']) == "load_f_by_year") {
		$resp['html_resp']=load_f_by_year($POST['f_by_id'],'');
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "load_f_period") {
		$resp['html_resp']=get_for_period($dbcon,$POST['f_by_id'],$POST['f_target_period'],"");
		echo json_encode($resp);
	}


?>	