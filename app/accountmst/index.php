<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include("../../config/image.php");
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
			$aColumns = array('acc_id', 'acc_name','acc_number','bank.bank_name','branch_name','city.city_name','acc_chequeleft','opn_balance','acc_status','acc.cdate','acc.user_id');
			$sIndexColumn = "acc_id";
			$isWhere = array("acc_status != 2".check_user('acc'));
			$sTable = " account_mst as acc";			
			$isJOIN = array("left join bank_mst as bank on bank.bankid=acc.bankid","left join city_mst as city on city.cityid=acc.cityid");
			$hOrder = "acc.acc_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = ($row['acc_name']);
				$row_data[] = $row['acc_number'];
				$row_data[] = $row['bank_name'];
                $row_data[] = $row['branch_name'];
                $row_data[] = $row['city_name'];
                $row_data[] = $row['acc_chequeleft'];
                $row_data[] = $row['opn_balance'];
                $btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['acc_id'].')"><i class="fa fa-pencil"></i></button> ';
				$btn.= '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_accountmst('.$row['acc_id'].')"><i class="fa fa-trash-o"></i></button>';
                
                $row_data[] = $btn;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			  $tr = $dbcon -> query("SELECT `acc_id`,`branch_name`,`acc_status` FROM `account_mst` WHERE `acc_number` = '".$POST['acc_number']."' and company_id=".$_SESSION['company_id']." and bankid=".$POST['bankid']);
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['acc_status'] != 0) {
						$info['acc_status']=0;
						$updateid=update_record('account_mst', $info,"acc_id=".$r['acc_id'] , $dbcon);						
						if($updateid)
							echo "1";
						else
							echo "0";
					}
					else {
						echo '-1';
					}
				}
				else {
                            $info['bankid']		        = $POST['bankid'];
							if($POST['bankid']==0)
							{
								$info['acc_type']		= 1;
							}
							else
							{
								$info['acc_type']		= 2;
							}
							$info['b_grp']		= $POST['gr_id'];
							$info['branch_name']		= $POST['branch_name'];
							$info['cityid']		        = $POST['b_cityid'];
							$info['acc_name']			= $POST['acc_name'];
							$info['acc_number']			= $POST['acc_number'];
                                                        $info['acc_chequeno']		= $POST['acc_chequeno'];
                                                        $info['acc_chequeleft']		= $POST['acc_chequeleft'];
                                                        $info['opn_balance']		= $POST['opn_balance'];
							$info['cdate']				= date("Y-m-d H:i:s");
							$info['user_id']			= $_SESSION['user_id'];
							$info['company_id']			= $_SESSION['company_id'];
							$inserid=add_record('account_mst', $info, $dbcon);
							
						
							$info_l['l_name']=$POST['ledger_name'];
							$info_l['l_group']=$POST['gr_id'];
							$info_l['l_form']='bank_form';
							$info_l['l_form_id']=$inserid;
							$info_l['l_form_table']='account_mst';
							$info_l['cdate']	   = date("Y-m-d H:i:s");
							$info_l['user_id']	   = $_SESSION['user_id'];
							$info_l['company_id']  = $_SESSION['company_id'];
							
							add_record("tbl_ledger", $info_l, $dbcon);
						
						/* Add Record in ledger End */
						
					if($inserid)
                    {
                        if(strtolower($POST['model'])=="model")
						{
							$query="SELECT acc_id,bank_name,branch_name,acc_number,acc_name FROM `account_mst` as accmst left join bank_mst as bmst on bmst.bankid=accmst.bankid where acc_status=0 and acc_id=".$inserid;
							$rel=mysqli_fetch_assoc($dbcon->query($query));		
							//$row = $rel;
                            $row['id']=$rel['acc_id'];
                            $row['name']=$rel['acc_name'].' ('.$rel['bank_name'].' - '.$rel['branch_name'].')';
							$row['res']="2"; 
						}
						else
						{
							$row['res'] ="1";
						}
                    }
                    else
						$row['res'] ="0";
				}
			 
         echo json_encode($row);
		}		
        else if(strtolower($POST['mode']) == "preedit") {
            $tr = $dbcon -> query("SELECT * FROM `account_mst` WHERE `acc_id` =".$POST['id']);
            $rel=mysqli_fetch_assoc($tr);
            echo json_encode($rel);
        }
		else if(strtolower($POST['mode']) == "edit") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
						
                $info['bankid']		        = $POST['bankid'];
                if($POST['bankid']==0)
				{
						$info['acc_type']		= 1;
				}
				else
				{
					$info['acc_type']		= 2;
				}
				$info['b_grp']		= $POST['edit_gid'];
				$info['branch_name']		= $POST['branch_name'];
                $info['cityid']		        = $POST['cityid'];
                $info['acc_name']			= $POST['acc_name'];
                $info['acc_number']			= $POST['acc_number'];
                $info['acc_chequeno']		= $POST['acc_chequeno'];
                $info['acc_chequeleft']		= $POST['acc_chequeleft'];
                $info['opn_balance']		= $POST['opn_balance'];
                $info['cdate']				= date("Y-m-d H:i:s");
                $info['user_id']			= $_SESSION['user_id'];
				$info_vender['vender_name']	= $POST['acc_name'];
				$info_vender['vendor_cat']	= "3";
				$info_vender['venderacc_id']= $POST['edit_id'];
				$info_vender['mdate']		= date("Y-m-d H:i:s");
				$info_vender['user_id']		= $_SESSION['user_id'];
				$info_vender['usertype_id']	= $_SESSION['user_type'];
				$updateid=update_record('tbl_vender', $info_vender,"venderacc_id=".$POST['edit_id'] , $dbcon);
                $updateid=update_record('account_mst', $info,"acc_id=".$POST['edit_id'] , $dbcon);
				if($updateid)
					echo "2";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['acc_status']		= 2;
			$updateid=update_record('account_mst', $info,"acc_id=".$POST['eid'] , $dbcon);					
			if($updateid)
				echo "1";	
			else
				echo "0";			
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