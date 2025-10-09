<?php

session_start();
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/hrms_common_functions.php");

	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	} else {
		$POST = bulk_filter($dbcon,$_GET);
	}
	if(strtolower($POST['mode']) == "file_deleted") {
		// Remove file
		if( $delete_file == 1 ){
		    $file_path = $_POST['target_file'];

		    // Check if file is exists
		    if ( file_exists($file_path) ) {

		        // Delete the file
		        unlink($file_path);

		        // Be sure we deleted the file
		        if ( !file_exists($file_path) ) {
		            $response = array (
		                'status' => 'success',
		                'info'   => 'Successfully Deleted.'
		            );
		        } else {
		            // Check the directory's permissions
		            $response = array (
		                'status' => 'error',
		                'info'   => 'We screwed up, the file can\'t be deleted.'
		            );
		        }
		    } else {
		        // Something weird happend and we lost the file
		        $response = array (
		            'status' => 'error',
		            'info'   => 'Couldn\'t find the requested file :('
		        );
		    }

		    // Return the response
		    echo json_encode($response);
		    exit;
		}

	}else if(strtolower($POST['mode']) == "file_upload") {
		/**
		 * Dropzone PHP file upload/delete
		 */

		// Check if the request is for deleting or uploading
		$delete_file = 0;
		if(isset($_POST['delete_file'])){ 
		    $delete_file = $_POST['delete_file'];
		}

		$targetPath = '../../view/upload/attendance_file/';
		
		// Check if it's an upload or delete and if there is a file in the form
		if ( !empty($_FILES) && $delete_file == 0 ) {

		    // Check if the upload folder is exists
		    if ( file_exists($targetPath) && is_dir($targetPath) ) {

		        // Check if we can write in the target directory
		        if ( is_writable($targetPath) ) {

		            /**
		             * Start dancing
		             */
		            $path_parts = pathinfo($_FILES["file"]["name"]);
					$image_path = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];
		            $tempFile = $_FILES['file']['tmp_name'];

		            $targetFile = $targetPath . $image_path;

		            // Check if there is any file with the same name
		            if ( !file_exists($targetFile) ) {

		                // Upload the file
		                move_uploaded_file($tempFile, $targetFile);

		                // Be sure that the file has been uploaded
		                if ( file_exists($targetFile) ) {
		                    $response = array (
		                        'status'    => 'success',
		                        'info'      => 'Your file has been uploaded successfully.',
		                        'file_link' => $targetFile
		                    );
		                } else {
		                    $response = array (
		                        'status' => 'error',
		                        'info'   => 'Couldn\'t upload the requested file :(, a mysterious error happend.'
		                    );
		                }

		            } else {
		                // A file with the same name is already here
		                $response = array (
		                    'status'    => 'error',
		                    'info'      => 'A file with the same name is exists.',
		                    'file_link' => $targetFile
		                );
		            }

		        } else {
		            $response = array (
		                'status' => 'error',
		                'info'   => 'The specified folder for upload isn\'t writeable.'
		            );
		        }
		    } else {
		        $response = array (
		            'status' => 'error',
		            'info'   => 'No folder to upload to :(, Please create one.'
		        );
		    }

		    // Return the response
		    echo json_encode($response);
		    exit;
		}
	}else if(strtolower($POST['mode']) == "fetch") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$branch=$_SESSION['branch_id'];
			$where='';
			$where.="hrmsatten.status IN (0,1) and hrmsatten.company_id = $companyID".check_user('hrmsatten');
			$appData = array();
			$i=1;

			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			$aColumns = array('hrmsatten.id','hrmsatten.attendance_date', 'shifttype.shift_type_name', 'hrmsatten.attendance_status', 'hrmsatten.late_entry_flag', 'hrmsatten.early_exit_flag', 'empusers.l_name', 'hrmsatten.series_id', 'hrmsatten.status', 'comp.company_name');
			$sIndexColumn = "hrmsatten.id";
			$isWhere = array($where);
			$sTable = "hrms_attendance as hrmsatten";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = hrmsatten.company_id", "left join tbl_ledger as empusers on empusers.l_id=hrmsatten.employee_id", "left join tbl_invoicetype as invoicetype on invoicetype.invoicetype_id=hrmsatten.series_id","left join hrms_shift_type as shifttype on shifttype.id=hrmsatten.shift_type_id");
			$hOrder = "hrmsatten.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['id'];
				$row_data[] = $row['series_id'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['l_name'];
				$row_data[] = $row['shift_type_name'];
				$row_data[] = $row['attendance_date'];
				$row_data[] = get_approval_status_by_id($dbcon, $row['attendance_status']);
				$row_data[] = $row['late_entry_flag'];
				$row_data[] = $row['early_exit_flag'];
				
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}

				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'hrms_attendance_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per){
				    	$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_attendance('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
				    }
				}
				if($other_btn_per) {
					if($row['status'] == '0')
					{  
						$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
					} else {
						$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
					}
				}
				$row_data[] = $edit_btn.' '.$delete_btn.' '.$change_status; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {

			// Add New Attendance List
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id']	= $POST['series_id'];
			$info['employee_id'] = $POST['employee_id'];
			$info['shift_type_id']	= $POST['shift_type_id'];
			$info['attendance_date'] = date('Y-m-d', strtotime($POST['attendance_date']));
			$info['attendance_status'] = $POST['attendance_status'];
			$info['late_entry_flag'] = ($POST['late_entry_flag'])?$POST['late_entry_flag']:'No';
			$info['early_exit_flag'] = ($POST['early_exit_flag'])?$POST['early_exit_flag']:'No';
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$inserattenid = add_record('hrms_attendance', $info, $dbcon);

			$query = $dbcon->query("SELECT `id` FROM `hrms_attendance`");
			$total_records = $query->num_rows;
			$updateInfo['taxinvoice_start'] = $total_records;
			$updateinvoiceid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = 'ATTENDANCE'" , $dbcon);
			
			if($inserattenid){	
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {

			// Edit New Attendance List
			$info['employee_id'] = $POST['employee_id'];
			$info['series_id']	= $POST['series_id'];
			$info['shift_type_id']	= $POST['shift_type_id'];
			$info['attendance_date'] = date('Y-m-d', strtotime($POST['attendance_date']));
			$info['attendance_status'] = $POST['attendance_status'];
			$info['late_entry_flag'] = ($POST['late_entry_flag'])?$POST['late_entry_flag']:'No';
			$info['early_exit_flag'] = ($POST['early_exit_flag'])?$POST['early_exit_flag']:'No';
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$updateattenid = update_record('hrms_attendance', $info,"id=".$POST['eid'] , $dbcon);
	
			if($updateattenid){	
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('hrms_attendance', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_attendance', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}	
		
?>