<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
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
		
		if(strtolower($POST['mode']) == "generate_report_new") {
			
			$s_date=explode(' - ',$POST['date']);
			
			$from_month = date("m",strtotime($s_date[0]));
			$to_month = date("m",strtotime($s_date[1]));

			
			$str="<table class='table table-bordered table-hover table-striped'>";
			$str.="<tr>

				<th>#</th>
				<th>Employee</th>
				<th>Target</th>
				<th>Achievement</th>
				<th>%</th>
				<th></th>
			</tr>";
			$cnt=1;
			
			$q = "select u.user_id,u.user_name,utype.usertype_name
				 from users as u
				left join tbl_usertype as utype on u.user_type = utype.usertype_id
			 where  u.active=0 order by u.user_type";
			
			$cnt=1;
			$query = $dbcon->query($q);
			while($row = brp_mysqli_fetch_assoc($query))
			{
				$target = get_target_of_user($dbcon,$row['user_id'],$from_month,$to_month);
				$achieve = get_achievement_of_user($dbcon,$row['user_id'],$from_month,$to_month);

				if($target!=0)
				{
					$achieve_per = ($achieve*100)/$target;
				}
				else
				{
					$achieve_per=0;
				}
				$str.="
					<tr>
						<th>".$cnt."</th>
						<td>".$row['user_name']."<br><strong style='color:red'>".$row['usertype_name']."</strong></td>
						<td>".round($target,2)."</td>
						<td>".round($achieve,2)."</td>
						<td>".round($achieve_per,2)." %</td>
						<td></td>
					</tr>
				";

				$cnt++;
			}	

			echo $str;
    	}


    	// target vs achievement report of customer 

    	if(strtolower($POST['mode']) == "generate_report") {
			$s_date=explode(' - ',$POST['date']);
			$str='';

			 // $financial_year=get_financial_year_new($dbcon); 

		  //   $start_date = date("Y-m-d",strtotime($financial_year['financial_start_date']));
    // 		$end_date = date("Y-m-d",strtotime($financial_year['financial_end_date']));

			$s_date=explode(' - ',$POST['date']);
			
			$start_date = date("Y-m-d",strtotime($s_date[0]));
			$end_date = date("Y-m-d",strtotime($s_date[1]));


			$str="<table class='table table-bordered table-hover table-striped'>";
			$str.="<tr>

				<th>#</th>
				<th>Customer</th>
				<th>Target</th>
				<th>Achievement</th>
				<th>%</th>
				<th></th>
			</tr>";
			$cnt=1;
			
			$q = "select c.cust_name,c.cust_id from tbl_customer as c where c.ledger_id!=0 and c.cust_status=0  ";
			
			$cnt=1;
			$query = $dbcon->query($q);
			while($row = brp_mysqli_fetch_assoc($query))
			{
				$target = get_target_of_customer($dbcon,$row['cust_id'],$start_date,$end_date);
				$achieve = get_achievement_of_customer($dbcon,$row['cust_id'],$start_date,$end_date);

				if($target!=0)
				{
					$achieve_per = ($achieve*100)/$target;
				}
				else
				{
					$achieve_per=0;
				}
				$str.="
					<tr>
						<th>".$cnt."</th>
						<td>".$row['cust_name']."</td>
						<td>".round($target,2)."</td>
						<td>".round($achieve,2)."</td>
						<td>".round($achieve_per,2)." %</td>
						<td>
							<a class='btn btn-primary' href='".ROOT.CRM_ROOT."target_report_details/".$row["cust_id"]."/".$start_date."/".$end_date."'>View</a>
						</td>
					</tr>
				";

				$cnt++;
			}	

			echo $str;

		}
   
	}

}
?>