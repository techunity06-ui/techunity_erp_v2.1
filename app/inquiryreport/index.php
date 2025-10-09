<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/finance_common_functions.php");
include("../../config/image.php");

$date = get_current_financial_year();
extract($date);
$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
$image = new SimpleImage();
if(strtolower($POST['mode']) == "generate_report") {
        
            $set="select * from tbl_company where company_id=".$_SESSION['company_id'];
            $set_head= brp_mysqli_fetch_assoc($dbcon->query($set));		
			
            $str .='<table  class="display table table-bordered table-striped" id="">
                    <thead class="resdisplay">
                        <tr id="logo">
                                <td class="noborder" colspan="6" style="text-align:center;">
                                <strong>'.$set_head['company_name'].'</strong></td>
                        </tr>
                        <tr>
                                <td colspan="4" class="noborder"><strong>Customer wise Outstanding Statement</strong></td>
                                <td class="noborder">Date :'.date('d/m/Y').'</td>
                        </tr>
                        <tr>
                          <th width="2%" style="text-align:center;vertical-align:top;">Sr. NO.</th>
                          <th width="40%" style="text-align:center;vertical-align:top;">inquiry no </th>
                          <th width="10%" style="text-align:center;vertical-align:top;">inquiry date</th>
                          <th width="10%" style="text-align:center">quotation no</th>
                          <th width="10%" style="text-align:center">type</th>
                          <th width="10%" style="text-align:center">name</th>
                          <th width="10%" style="text-align:center">mobile no</th>
                          <th width="10%" style="text-align:center">email id</th>
                          <th width="10%" style="text-align:center">country</th>
                          <th width="10%" style="text-align:center">state</th>
                          <th width="10%" style="text-align:center">city</th>
                          <th width="10%" style="text-align:center">stage</th>
                          <th width="10%" style="text-align:center">product name</th>
                          <th width="10%" style="text-align:center">due date</th>
                          <th width="10%" style="text-align:center">remark</th>
                          <th width="10%" style="text-align:center">last folloup date</th>
                        </tr>
                    </thead>
                    <tbody>';
					//	and alert_date_time<"' . date('Y-m-d', strtotime($POST['fil_due_date'])) . '"
						echo $qry = 'SELECT task.task_due_date,task.create_date,type.mcd_name as type_name,inqr.inquiry_no,inqr.inquiry_date,cust.cust_name,cust.cust_mobile,cust.cust_email,custadd.c_add_location,custadd.c_add_street,custadd.c_add_zip,cou.country_name,st.state_name,ct.city_name,GROUP_CONCAT(use3.user_name) from tbl_task as task 
							left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id
							left join tbl_inquiry as inqr on inqr.inquiry_id=task.inquiry_id
							left join tbl_customer as cust on cust.cust_id=inqr.cust_id
							left join tbl_cust_address as custadd on custadd.cust_id=cust.cust_id
							left join country_mst as cou on cou.countryid=custadd.c_add_country
							left join state_mst as st on st.stateid= custadd.c_add_state
							left join city_mst as ct on ct.cityid= custadd.c_add_city
							left join users as use3 on use3.user_id in (task.assign_user_ids)
						where task.task_status=0'; 
						
						$str .= $qry;
						$qry_rs = $dbcon->query($qry);
						if (mysqli_num_rows($qry_rs)) {
							$k = 1;
							while ($rel = mysqli_fetch_assoc($qry_rs)) {
								  $str.='<tr>
                                        <td data-label="SR. NO." style="text-align:center">'.$k.'</td>
                                        <td data-label="PARTY NAME" style="text-align:left">'.$rel['inquiry_no'].'</td>
                                        <td data-label="PARTY NAME" style="text-align:left">'.$rel['inquiry_date'].'</td>
                                        <td data-label="PARTY NAME" style="text-align:left"></td>
                                        <td data-label="PARTY NAME" style="text-align:left">'.$rel['type_name'].'</td>
                                        <td data-label="PHONE NO." style="text-align:left">'.$rel['cust_name'].'</td>
                                        <td data-label="PHONE NO." style="text-align:left">'.$rel['cust_mobile'].'</td>
                                        <td data-label="PHONE NO." style="text-align:left">'.$rel['cust_email'].'</td>
                                        <td data-label="PHONE NO." style="text-align:left">'.$rel['cust_email'].'</td>
									</tr>';
								$k++;
							}
						}
			$str .='</tbody>				 
            </table>';
				  
    echo $str;
}
?>