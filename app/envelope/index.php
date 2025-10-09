<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../include/function_database_query.php");
//include("../../config/session.php");
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ { 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */{
			//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}	
		
		if(strtolower($POST['mode']) == "fetch") {
			$appData = array();
			$i=1;
			$aColumns = array('envelope_design_id', 'env_name','font_size','font_family', 'top_margin','left_margin','env_status','env.user_id');
			$sIndexColumn = "envelope_design_id";
			$isWhere = array("env_status = 0".check_user('env'));
			$sTable = "evelope_design as env";			
			$isJOIN = array();
			$hOrder = "env.envelope_design_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				if(!empty($row['env_name'])){
				$row_data[] = $row['env_name'];
				}else{
					$row_data[] ='-';
				}
			 	$row_data[] = $row['top_margin']."x".$row['left_margin'];
				$row_data[] = $row['font_size'];
				if(!empty($row['font_family'])){
				$row_data[] = $row['font_family'];
				}else{
					$row_data[] ='-';
				}
				
				 
				$row_data[] = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'envelopeedit/'.$row['envelope_design_id'].'"><i class="fa fa-pencil"></i></a>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_env('.$row['envelope_design_id'].')"><i class="fa fa-trash-o"></i></button>
				'; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		
		if(strtolower($POST['type']) == "add") {
		if(!empty($POST['eid']))
		{
			$info['width']			= $POST['width'];
			$info['height']			= $POST['height'];
			$info['env_name']		= $POST['env_name'];
			$info['from_top_margin']	= $POST['from_top_margin'];
			$info['from_left_margin']	= $POST['from_left_margin'];
			$info['font_size']		= $POST['font_size'];
			$info['font_family']	= $POST['font_family'];
			$info['line_height']	= $POST['line_height'];
			$info['top_margin']		= $POST['to_top_margin'];
			$info['left_margin']	= $POST['to_left_margin'];
			$updateid=update_record("evelope_design", $info, "envelope_design_id=".$POST['eid'], $dbcon);
		}
		else
		{
			$info['width']			= $POST['width'];
			$info['height']			= $POST['height'];
			$info['env_name']		= $POST['env_name'];
			$info['from_top_margin']	= $POST['from_top_margin'];
			$info['from_left_margin']	= $POST['from_left_margin'];
			$info['font_size']		= $POST['font_size'];
			$info['font_family']	= $POST['font_family'];
			$info['line_height']	= $POST['line_height'];
			$info['top_margin']		= $POST['to_top_margin'];
			$info['left_margin']	= $POST['to_left_margin'];
			$info['company_id']		= $_SESSION['company_id'];
			$insid=add_record("evelope_design", $info,  $dbcon);
		}				
		if($updateid)
		{	echo "1";	}
		else if($insid)
		{	echo "2"; }	
		else
		{	echo "0";	}

		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['env_status']		= 2;
			$updateid=update_record('evelope_design', $info,"envelope_design_id=".$POST['qid'] , $dbcon);				
			if($updateid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode'])== "load_envelope")
		{
			$row=array();
			 $query1="select * from  evelope_design where envelope_design_id=".$POST['typeid'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			echo json_encode($rows);
		}
		
    }

}	
?>