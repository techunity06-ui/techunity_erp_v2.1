<?php

session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

$image = new SimpleImage();
							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		//print_r($_FILES);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			$appData = array();
			$i=1;
			$aColumns = array('vender_id','vender_name','state.state_name','city.city_name','tin_no','vender_mobile','vender_status','vender.cdate','vender.user_id');
			$sIndexColumn = "vender_id";
			$isWhere = array("vender_status = 0 and vendor_cat in(1,2) and vender.company_id in (0,$_SESSION[company_id])");
			$sTable = " tbl_vender as vender";			
			$isJOIN = array('left join state_mst state on vender.stateid=state.stateid','left join city_mst city on vender.cityid=city.cityid');
			$hOrder = "vender.vender_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['vender_name'];
				$row_data[] = $row['city_name'];
				$row_data[] = $row['state_name'];
				$row_data[] = $row['tin_no'];
				$row_data[] = $row['vender_mobile'];
				
			$printcheckbox='<input type="checkbox" class="form-control" style="width:26px;" id="allchk'.$id.'" name="chk" value="'.$row["vender_id"].'">'; 
				$row_data[] = $printcheckbox.'<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'venderaddedit/'.$row['vender_id'].'"><i class="fa fa-pencil"></i></a>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_vender('.$row['vender_id'].')"><i class="fa fa-trash-o"></i></button>
				<a class="btn btn-xs btn-info" data-original-title="Envelope Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'envelopeprint/vendor/'.$row['vender_id'].'"><i class="fa fa-print"></i></a>
				<!--<a class="btn btn-xs btn-primary" data-original-title="Label Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'labourprint/vendor/'.$row['vender_id'].'"><i class="fa fa-print"></i></a>-->
				'; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
				$tr = $dbcon -> query("SELECT `vender_id`,`vender_name`,`vender_status` FROM `tbl_vender` WHERE vender_status=0 and `vender_name` = '$POST[vender_name]' and vendor_cat=".$POST['cat_id']." company_id != $_SESSION[company_id]");
				if($tr->num_rows > 0) {
					$row['res']='-1';
				}
				else {
							$info['vender_name']	= $POST['vender_name'];
							$info['vender_address']	= text_rnremove($_POST['vender_address']);
							$info['countryid']		= $POST['countryid'];
							$info['stateid']		= $POST['stateid'];
							$info['cityid']			= $POST['cityid'];
							$info['opening_balance']	= $POST['opening_balance'];
							$info['balance_typeid']		= $POST['balance_typeid'];
							$info['vender_mobile']	= $POST['vender_mobile'];
							$info['tin_no']			= $POST['tin_no'];
							$info['vendor_cat']		= $POST['cat_id'];
							$info['cdate']			= date("Y-m-d H:i:s");
							$info['mdate']			= date("Y-m-d H:i:s");
							$info['user_id']		= $_SESSION['user_id'];
							$info['usertype_id']	= $_SESSION['user_type'];
							$info['multi_company']	= $POST['multi_company'];
							if(!$POST['multi_company'])
								$info['company_id']			= $_SESSION['company_id'];
							else
								$info['company_id']			= 0;
							$inserid=add_record('tbl_vender', $info, $dbcon);
							$row['res']='';
					if($inserid)
					{
						if(strtolower($POST['model'])=="model")
						{
							$query="select * from tbl_vender where vender_id=".$inserid;
							$rel=mysqli_fetch_assoc($dbcon->query($query));		
							$row = $rel;
							$row['res']="2"; 
						}
						else
						{
							$row['res'] ="1";
						}
					}
					else
					{
						$row['res'] ="0";
					}
					
				}
				echo json_encode($row);	
		}		
		else if(strtolower($POST['mode']) == "edit") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
							$info['vender_name']	= $POST['vender_name'];
							$info['vender_address']	= text_rnremove($_POST['vender_address']);
							$info['countryid']		= $POST['countryid'];
							$info['stateid']		= $POST['stateid'];
							$info['cityid']			= $POST['cityid'];
							$info['opening_balance']	= $POST['opening_balance'];
							$info['balance_typeid']		= $POST['balance_typeid'];
							$info['vender_mobile']	= $POST['vender_mobile'];
							$info['tin_no']			= $POST['tin_no'];
							$info['vendor_cat']		= $POST['cat_id'];
							$info['mdate']			= date("Y-m-d H:i:s");
							$info['user_id']		= $_SESSION['user_id'];
							$info['usertype_id']	= $_SESSION['user_type'];
							$info['multi_company']	= $POST['multi_company'];
							if(!$POST['multi_company'])
								$info['company_id']			= $_SESSION['company_id'];
							else
								$info['company_id']			= 0;
							$updateid=update_record('tbl_vender', $info,"vender_id=".$POST['eid'] , $dbcon);
							$row['res']='';
				if($updateid)
				{
					$row['res']='update';
				}
				else
				{
					$row['res']='0';
				}
				echo json_encode($row);
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['vender_status']		= 2;
			$updateid=update_record('tbl_vender', $info,"vender_id=".$POST['eid'] , $dbcon);				
			if($updateid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "load_city") {
			 	$cityid=$POST['id'];				
				echo $str=getcity($dbcon,$cityid,0);
		}
				
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
?>