<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "generate_report") {

    $owner_query = "select group_concat(distinct(report_to_user_id)) as owner_ids from users where active=0 AND company_id IN (0,".$_SESSION['company_id'].")";
    $owner_ids = $sub_ledger = $dbcon->query($owner_query)->fetch_object()->owner_ids;

    $str='';
    $str.='<table class="display table table-bordered table-striped">
    <thead>
    <tr>
    <th>Lead Owner</th>
    <th>Full Name</th>				  
    <th>Email</th>				  
    <th>Phone</th>				  
    <th>Company</th>				  
    <th>Lead Creation Date</th>				  
    <th>First Name</th>				  
    <th>Last Name</th>				  
    </tr>
    </thead>
    <tbody>';
    if($owner_ids){
        $owner_query = "SELECT * FROM `users` WHERE user_id IN (".$owner_ids.")";
        $result = mysqli_query($dbcon,$owner_query);
        $owner_data = mysqli_fetch_all($result,MYSQLI_ASSOC);

        if($owner_data){
            $chart_data = array();
            $total = 0;
            foreach ($owner_data as $i => $owner) {
                $leads_query = "SELECT * FROM `users` WHERE report_to_user_id IN (".$owner['user_id'].")";
                $result = mysqli_query($dbcon,$leads_query);
                $leads_data = mysqli_fetch_all($result,MYSQLI_ASSOC);

                $owner_count = count($leads_data);
                $owner_count++;
                        //$chart_data[$owner['user_name']] = $owner_count;
                $chart_data[$i]['label'] = $owner['user_name'];
                $chart_data[$i]['y'] = intval($owner_count);

                $total = $total + $owner_count;
                $name = explode(' ',$owner['user_name']);
                $firstname = $name[0];
                $lastname = $name[1];
                $str .= '<tr style="background-color: beige;">
                <td class="text-left">'.$owner['user_name'].'('.$owner_count.')</td>
                <td class="text-left">'.$owner['user_name'].'</td>
                <td class="text-left" style="white-space:nowrap;">'.$owner['user_mail'].'</td>
                <td class="text-left" style="white-space:nowrap;">'.$owner['user_phone'].'</td>
                <td class="text-left">'.$owner['user_company'].'</td>
                <td class="text-left" style="white-space:nowrap;">'.date('d-m-Y', strtotime($owner['user_tmst'])).'</td>
                <td class="text-left">'.$firstname.'</td>
                <td class="text-left" style="white-space:nowrap;">'.$lastname.'</td>
                ';
                $str .= '</tr>';

                if($leads_data){
                    foreach ($leads_data as $lead) {
                        $name = explode(' ',$lead['user_name']);
                        $firstname = $name[0];
                        $lastname = $name[1];
                        $str.='<tr>
                        <td class="text-left"></td>
                        <td class="text-left">'.$lead['user_name'].'</td>
                        <td class="text-left" style="white-space:nowrap;">'.$lead['user_mail'].'</td>
                        <td class="text-left" style="white-space:nowrap;">'.$lead['user_phone'].'</td>
                        <td class="text-left">'.$lead['user_company'].'</td>
                        <td class="text-left" style="white-space:nowrap;">'.date('d-m-Y', strtotime($lead['user_tmst'])).'</td>
                        <td class="text-left">'.$firstname.'</td>
                        <td class="text-left" style="white-space:nowrap;">'.$lastname.'</td>
                        ';
                        $str.='</tr>';
                    }
                }
            }
            $str .= '<tr>
            <td colspan="4" class="text-right"><strong>TOTAL RECORDS IN THIS PAGE :</strong></td>
            <td colspan="4" class="text-left"><strong>'.$total.' RECORDS</strong></td>
            </tr>';
        }
    }
    else {
      $str .= '<tr><td colspan="8" class="text-center">NO DATA FOUND !!!</td></tr>';
  }

  $str .= '</tbody></table>';
            //p($chart_data);
  $resp['html_resp'] = $str;
  $resp['chart_data'] = $chart_data;
  echo json_encode($resp);
}
?>