<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
	//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode'])== "add_consignee")
		{
			//check if already exist contact person
			$person_qry = "Select cust_name,cust_contact_person_email 
				From tbl_custmer_consignee 
				WHERE cust_ref_id = ".$POST['cust_id']." and cust_status = 0
					and cust_name = '".$POST['con_name']."' 
					and cust_email = '".$POST['con_email']."' 
					"; 
			$q = $dbcon->query($person_qry);
			$row = mysqli_fetch_all($q);
                        
				if(!$row){
					$info1['company_name']  = $POST['comp_name'];
					$info1['cust_name']     = $POST['con_name'];
					$info1['cust_mobile']   = $POST['con_mobile'];
					$info1['cust_email']    = $POST['con_email'];
					$info1['cust_address']  = stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['con_address']));//nl2br($POST['con_address']);
					$info1['countryid']     = $POST['country_consinee_id'];
					$info1['stateid']       = $POST['state_consinee_id'];
					$info1['cityid']        = $POST['city_consinee_id'];
					$info1['gst_no']        = $POST['gst_consinee_no'];
					$info1['cust_ref_id']   = $POST['cust_id'];
					$info1['user_id']       = $_SESSION['user_id'];
					$info1['cdate']         = date("Y-m-d h:i:s");
	
					$table='tbl_custmer_consignee';
					$tableid='cust_id';
				

					if(empty($POST['edit_id']))
					{
							$inserid=add_record($table, $info1, $dbcon);
					}
					else
					{
							$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
					}
					
					if(strtolower($POST['model'])=="model")
					{
						$query="select * from tbl_custmer_consignee where cust_id=".$inserid;
						$rel=mysqli_fetch_assoc($dbcon->query($query));		
						$r = $rel;
						$r['msg']="3";								
					}else{
						 $r['msg']="1";
					}
				   
				} else {
				   $r['msg']="2";
				}
			echo json_encode($r);
		} else if(strtolower($POST['mode'])== "load_consignee_detail")
		{
                    if(strtolower($POST['form_mode']) == "edit"){
				$query="select tbcon.*,cit.city_name,sta.state_name,coun.country_name from tbl_custmer_consignee as tbcon
					left join country_mst as coun on coun.countryid=tbcon.countryid
					left join state_mst as sta on sta.stateid=tbcon.stateid 
					left join city_mst as cit on cit.cityid=tbcon.cityid
					where tbcon.cust_ref_id=".$_POST['cust_id']." and tbcon.user_id=".$_SESSION['user_id']." order by tbcon.cust_id Desc";
			}
			else{
				$query="select tbcon.*,cit.city_name,sta.state_name,coun.country_name from tbl_custmer_consignee as tbcon
					left join country_mst as coun on coun.countryid=tbcon.countryid
					left join state_mst as sta on sta.stateid=tbcon.stateid 
					left join city_mst as cit on cit.cityid=tbcon.cityid
					where tbcon.cust_ref_id='0' and tbcon.user_id=".$_SESSION['user_id']." order by tbcon.cust_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
                            <th width="10%">Company Name</th>
                            <th width="10%">Person Name</th>
                            <th width="8%">Mobile</th>
                            <th width="10%">Email</th>
                            <th width="10%">Address</th>
                            <th width="12%">Country</th>
                            <th width="12%">State</th>
                            <th width="12%">City</th>
                            <th width="8%">GST No</th>
                            <td width="8%"></td>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
                        <td style="vertical-align:top;">
							'.$rel['company_name'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['cust_name'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['cust_mobile'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['cust_email'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['cust_address'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['country_name'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['state_name'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['city_name'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['gst_no'].'
						</td>
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_consignee_data('.$rel['cust_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_consignee_data('.$rel['cust_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
                    
                }
                else if(strtolower($POST['mode'])== "preedit_consignee"){
                    $q = $dbcon -> query("SELECT * FROM tbl_custmer_consignee WHERE cust_id=".$POST['id']);
                    $r = $q->fetch_assoc();
                    echo json_encode($r);
                }
                else if(strtolower($POST['mode'])== "delete_consignee"){
                        $deleteid=delete_record('tbl_custmer_consignee', "cust_id=".$POST['eid'], $dbcon);

						if($deleteid)
							$row['res']="1";
						else
							$row['res']="0";
						echo json_encode($row);
                }
				else if(strtolower($POST['mode']) == "load_state") {
					$countryid=$POST['id'];				
					echo get_state($dbcon,'',$countryid);
				}
				else if(strtolower($POST['mode']) == "load_city") {
					$cityid=$POST['id'];				
					echo $str=getcity($dbcon,$cityid,0);
				}
		
   
?>