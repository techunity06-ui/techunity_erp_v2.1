<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
		$s_date=explode(' - ',$POST['date']);
		$where=" date>='".date('Y-m-d',strtotime($s_date[0]))."' AND date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		if($POST['status_id']=="0")
		{
			$where .=' and status=0';
		}
		else if($POST['status_id']=="1")
		{
			$where .=' and status=1';
		}
		else
		{
			$where .=' and  status != 2';
		}
			$appData = array();
			$i=1;
			$aColumns = array('todo_id','date', 'task_detail', 'status', 'tmst.user_id');
			$sIndexColumn = "todo_id";
			$isWhere = array($where." and company_id=".$_SESSION['company_id']);
			$sTable = "todo_mst as tmst";			
			$isJOIN = array();
			$hOrder = "tmst.date";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
			
				$row_data = array();
				
				$row_data[] = $row['sr'];
				if($row['status']=="0")
				{
					$row_data[] = date('d/m/Y',strtotime($row['date']));
					$row_data[] = $row['task_detail'];	
					$row_data[] = '<button class="btn btn-xs btn-info" data-original-title="Click To Complete" data-toggle="tooltip" data-placement="top" onClick="change_status('.$row['todo_id'].',1);">Pending</i></button>';
					$row_data[] = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['todo_id'].');"><i class="fa fa-pencil"></i></button>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_catalog('.$row['todo_id'].')"><i class="fa fa-trash-o"></i></button>';
					
				}
				else
				{
					$row_data[] = '<div  style="position: relative;text-decoration: line-through;">'.date('d/m/Y',strtotime($row['date'])).'</div>';
					$row_data[] = '<div  style="position: relative;text-decoration: line-through;">'.$row['task_detail'].'</div>';
					$row_data[] = '<div  style="position: relative;text-decoration: line-through;color:red"><button class="btn btn-xs btn-success" data-original-title="Click To change Status" data-toggle="tooltip" data-placement="top" onClick="change_status('.$row['todo_id'].',0);">Completed</i></button></div>';
					$row_data[] = '
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_catalog('.$row['todo_id'].')"><i class="fa fa-trash-o"></i></button>';
					
				}
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			//if($_POST['token'] == $_SESSION['token']) {
					
					$info['task_detail']= text_rnremove($POST['task_detail']);
					$info['date']		= date('Y-m-d',strtotime($POST['date']));
					$info['cdate']		= date("Y-m-d H:i:s");
					$info['user_id']		= $_SESSION['user_id'];
					$info['company_id']		= $_SESSION['company_id'];
					$inserid=add_record('todo_mst', $info, $dbcon);
					if($inserid)
						echo "1";
					else
						echo "0";
			//}
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `todo_mst` WHERE `todo_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			//if($_POST['token'] == $_SESSION['token']) {
					$info['task_detail']= text_rnremove($POST['task_detail']);
					$info['date']		= date('Y-m-d',strtotime($POST['date']));
					$info['cdate']		= date("Y-m-d H:i:s");
					$info['user_id']	= $_SESSION['user_id'];
					$info['company_id']		= $_SESSION['company_id'];
					$updateid=update_record('todo_mst', $info,"todo_id=".$POST['eid'] , $dbcon);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
				
			//}
		}
		else if(strtolower($POST['mode']) == "delete") {
			//if($_POST['token'] == $_SESSION['token']) {
				$info['status']='2';
				$updateid=update_record('todo_mst', $info,"todo_id=".$POST['eid'] , $dbcon);
				
				if($updateid)
					echo "1";
				else
					echo "0";
			//}
		}
		else if(strtolower($POST['mode']) == "change_status") {
				$info['status']=$POST['todo_status'];
				$updateid=update_record('todo_mst', $info,"todo_id=".$POST['eid'] , $dbcon);
				
				if($updateid)
					echo "1";
				else
					echo "0";
		}
		else if(strtolower($POST['mode']) == "gettodolist") {
			$str = '<table class="table table-hover personal-task">
                              <thead>
							  <tr>
								<td style="text-align:center" width="25%">Task Date</td>
								<td style="text-align:center" width="50%">Task</td>
								<td style="text-align:center" width="25%">Status</td>
							  </tr>
							  </thead>
							  <tbody>
							  ';
			$todoqry='select * from todo_mst where status=0 and company_id='.$_SESSION['company_id'].' order by date ASC Limit 0,5';
			 $result_todo=$dbcon->query($todoqry);
			 if(mysqli_num_rows($result_todo)>0)
			 {
				$i=1;
				while($rel_todo=mysqli_fetch_assoc($result_todo))
				  {
					$color='';
					if(date('Y-m-d')>=$rel_todo['date'])
					{
						$color='color:red';
					}
			$content = strlen($rel_todo['task_detail'])<=25 ? $rel_todo['task_detail'] : substr($rel_todo['task_detail'],0,25).'...'.'<a class="tooltips" data-original-title="'.$rel_todo['task_detail'].'" data-toggle="tooltip" data-placement="top"> <i class="fa fa-comment"></i></a>';
			$str .='<tr style="'.$color.'">
								<td style="text-align:center">'.date('d M Y',strtotime($rel_todo['date'])).'</td>
								<td style="text-align:left">
								'.$content.'
								</td>
								<td style="text-align:center">
								<button class="btn btn-xs btn-primary tooltips" data-original-title="Compete Task" data-toggle="tooltip" data-placement="top" onClick="change_status('.$rel_todo['todo_id'].',1);">Click to Complete</button>
								</td>
							  </tr>';
							  $i++;
				}
			 }
			 else
			 {
				$str .='<tr>
								<td colspan="3" style="text-align:center">No Data Found</td>
						</tr>';
			 }
				$str .='</tbody>
						</table>';
			echo $str;
		}
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
  //  die("Error - 1");
//}
?>