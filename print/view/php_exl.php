<?php
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	include("../../include/function_database_query.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

	
	header("Content-Disposition: attachment; filename=student_list.xls");  
    header("Content-Type: application/vnd.ms-excel"); 
 
	$output = "";
 
	$output .="
		<table>
			<thead>
				<tr>
					<th>Student ID</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Year</th>
					<th>Section</th>
				</tr>
			<tbody>
	";
 
	$query = $conn->query("SELECT * FROM `tbl_ledger`") or die(mysqli_errno());
	while($fetch = $query->fetch_array()){
 
	$output .= "
				<tr>
					<td>".$fetch['l_id']."</td>
					<td>".$fetch['l_name']."</td>
					<td>".$fetch['ledger_code']."</td>
					<td>".$fetch['l_form']."</td>
					<td>".$fetch['common_email_id']."</td>
				</tr>
	";
	}
 
	$output .="
			</tbody>
 
		</table>
	";

	echo $output;
?>