<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

if ($_POST != NULL) {
  $POST = bulk_filter($dbcon, $_POST);
} else {
  $POST = bulk_filter($dbcon, $_GET);
}

if (brp_strtolower($POST['mode']) == "fetch") {

  if ($POST['start_date'] && $POST['end_date']) {
    $_SESSION['start'] = $start_date = $POST['start_date'];
    $_SESSION['end'] = $end_date = $POST['end_date'];
    
  } else if (
    isset($_SESSION['start']) && !empty($_SESSION['start'])
    && isset($_SESSION['end']) && !empty($_SESSION['end'])
  ) {
    $start_date = $_SESSION['start'];
    $end_date = $_SESSION['end'];
  } else {
    $start_date = date('1-m-Y');
    $end_date = date("d-m-Y");
  }

  $where = '';
  if (!empty($start_date) && !empty($end_date)) {
    $where.="  DATE(drt.date) >= '".date('Y-m-d',strtotime($start_date))."' AND  DATE(drt.date) <= '".date('Y-m-d',strtotime($end_date))."'";
  }
  
  $appData = array();
  $i = 1;
  $aColumns = array('drt.r_id', 'drt.user_id', 'u.user_name', 'drt.file', 'drt.date', 'drt.description');

  $date = date_create($row['date']);
  $sIndexColumn = "drt.r_id";

  if ($POST['userid']) {
    $user_ids = $POST['userid'];
  } else {
    $user_ids = check_user_chein($dbcon,$_SESSION['user_id'],1);		
  }
  $where .= " and drt.user_id IN ($user_ids)";

  $isWhere = array($where . " AND status = 0");

  $sTable = "daily_report as drt";
  $isJOIN = array("LEFT JOIN users AS u ON u.user_id = drt.user_id");
  $hOrder = "drt.r_id desc";
  include($include . 'pagging.php');
  $appData = array();
  $id = 1;

  foreach ($sqlReturn as $row) {
    $row_data = array();
    $row_data[] = $row['sr'];
    $cleanContent = stripcslashes(mysqli_real_escape_string($dbcon, $row['description']));
    $row_data[] = $cleanContent;

    $row_data[] = date("d-m-Y", strtotime($row['date']));
    $row_data[] = $row['user_name'];

    $edit_btn = '';
    $delete_btn = '';
    $download_btn = '';

    $edit_btn = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_report(' . $row['r_id'] . ')"><i class="fa fa-pencil"></i></button>';

    if ($row['file']) {
      $download_btn = '<a class="btn btn-xs btn-primary" data-original-title="'.$row['file'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.'upload/daily_report/'.$row['file'].'"><i class="fa fa-download"></i></a>';
    }

    $delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_report('.$row['r_id'].')"><i class="fa fa-trash-o"></i></button>';

    // $view_hist_btn = '<button class="btn btn-xs btn-info" data-original-title="View History" data-toggle="tooltip" data-placement="top" onClick="view_followup_hist('.$row['inquiry_id'].')"><i class="fa fa-history"></i></button>';

    $row_data[] = $edit_btn . ' ' . $delete_btn . ' '. $download_btn;
    // $row_data[] = $edit_btn.''.$delete_btn; 
    $appData[] = $row_data;
    $id++;
  }
  $output['aaData'] = $appData;
  echo json_encode($output);
}

// add mode
else if (brp_strtolower($POST['mode']) == "add") {

  $tr = $dbcon->query("SELECT `r_id`,`description`,`date`,`user_id` FROM `daily_report` where `r_id` ='" . $POST['eid'] . "'");
  if ($tr->num_rows > 0) {
    $resp['msg'] = '-1';
  } else {

    $info['description'] = $_POST['user_input'];
    $info['user_id'] = $POST['user_id'];
    $ndate = $POST['date'];
    $info['date'] = date("Y-m-d", strtotime($ndate));

    // Upload Document
    if(!empty($_FILES['file_attachment']['tmp_name']))
    {
      $destination_folder_path = DAILY_REPORT_FILE_UPING;
      $filename = upload_file_document($_FILES,$destination_folder_path);
      $info['file'] = $filename;
    }

    $inserid = add_record('daily_report', $info, $dbcon);

    if ($inserid) {
      $resp['msg'] = "1";
    } else {
      $resp['msg'] = "0";
    }
  }
  echo json_encode($resp);

  // pre edit mode
} else if (brp_strtolower($POST['mode']) == "preedit") {
  $q = $dbcon->query("SELECT * FROM `daily_report` WHERE `r_id` = '" . $POST['eid'] . "'");
  $r = $q->fetch_assoc();
  echo json_encode($r);
} else if (brp_strtolower($POST['mode']) == "preedit2") {
  $q = $dbcon->query("SELECT * FROM `daily_report` WHERE `r_id` = '" . $POST['r_id'] . "'");
  $r = $q->fetch_assoc();
  echo json_encode($r);
}
//edit mode
else if (brp_strtolower($POST['mode']) == "edit") {

  $tr = $dbcon->query("SELECT * FROM `daily_report` WHERE `r_id`  != '" . $POST['edit_id']);

  if ($tr->num_rows > 0) {
    echo '-1';
  } else {
    $info = array();
    $info['description'] = $_POST['edit_description'];

    
    if(!empty($_FILES['file_attachment']['tmp_name']))
    {
      $delete_file = "";
      if ($_POST['file_attachment_name']) {
        $delete_file = $_POST['file_attachment_name'];
      }
      
      $destination_folder_path = DAILY_REPORT_FILE_UPING;
      $filename = upload_file_document($_FILES,$destination_folder_path,$delete_file);
      $info['file'] = $filename; 
    }

    $updateid = update_record('daily_report', $info, "r_id=" . $POST['edit_id'], $dbcon);

    if ($dbcon->error)
      echo "0" . $dbcon->error;
    else
      echo "1";
  }
} else if (brp_strtolower($POST['mode']) == "delete") {
  
  $info = array();
  $info['status'] = 1;

  $updateid = update_record('daily_report', $info, "r_id=" . $POST['r_id'], $dbcon);

  if ($updateid)
    echo "1";
  else
    echo "0";

  } else if (brp_strtolower($POST['mode']) == "deletefile") {
  
    $tr = $dbcon->query("SELECT * FROM `daily_report` WHERE `r_id`  = " . $POST['d_id']);

    if ($tr->num_rows > 0) {
   
      $r = $tr->fetch_assoc();
      $delete_file = $r['file'];
      $destination_path = DAILY_REPORT_FILE_UPING;

      // Move the uploaded file to the destination folder with the new name
      if (!empty($delete_file) && file_exists($destination_path)) {
          unlink($destination_path .'/'. $delete_file);
      }

      $info = array();
      $info['file'] = '';
      $updateid = update_record('daily_report', $info, "r_id=" . $POST['d_id'], $dbcon);
    
      echo "1";
    } else {
      echo "0";
    }

}

// Upload file in folder
function upload_file_document($files,$destination_path,$delete_file="") {

  // Checking whether file exists or not 
  if (!file_exists($destination_path)) {       
      // Create a new file or direcotry 
      mkdir($destination_path, 0777, true); 
  }

  // Get the original file name
  $filename = $files['file_attachment']['name'];

  // Extract file extension
  $file_extension = pathinfo($filename, PATHINFO_EXTENSION);

  // Remove extension from the original file name
  $file_name_without_extension = pathinfo($filename, PATHINFO_FILENAME);

  $file_name_without_extension = str_ireplace(" ","_",$file_name_without_extension);

  // Append a timestamp to the file name to make it unique
  $filename = $file_name_without_extension . '_' . time() . '.' . $file_extension;

  // Get the temporary file location
  $tmp_file = $files["file_attachment"]["tmp_name"];

  // Concatenate the destination folder and the new file name
  $destination_path_file = $destination_path . '/' . $filename;

  // Move the uploaded file to the destination folder with the new name
  if (move_uploaded_file($tmp_file, $destination_path_file)) {
    if (!empty($delete_file) && file_exists($destination_path)) {
        unlink($destination_path .'/'. $delete_file);
    }
  }
  return $filename;
}