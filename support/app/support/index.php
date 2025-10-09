<?php
session_start();
$AJAX = true;

$path = '../../../';
$incPath = $path . 'include/';

include($path . 'config/config.php');
include($path . 'config/session.php');
include_once($incPath . 'common_functions.php');
include($incPath . 'function_database_query.php');

if ($_POST != NULL) {
    $POST = bulk_filter($dbcon, $_POST);
} else {
    $POST = bulk_filter($dbcon, $_GET);
}
//var_dump($POST);
if (brp_strtolower($POST['mode']) == "fetch") {

    $userid = $_SESSION['user_id'];

    $appData = array();
    $i = 1;

    $where = '';

    if (isset($POST['emp_id']) && $POST['emp_id']) {
        $where .= ' st.user_id = ' . $POST['emp_id'];
    }

    $aColumns = array('st.id', 'st.cmp_unique_id', 'st.company_name', 'st.department', 'st.cdate', 'st.due_date', 'st.support_status_id', 'ssm.name', 'st.user_id'); // -ametr
    $sIndexColumn = "st.id";

    $sTable = " tbl_support_ticket as st";

    if ($_SESSION['user_type'] == '2') {
        if ($where) {
            $isWhere = array($where);
        }
    } else {
        $isWhere = array("st.user_id = $_SESSION[user_id] AND st.company_id in (0,$_SESSION[company_id])");
    }
    $isJOIN = array('left join tbl_support_status_mst as ssm on ssm.id = st.support_status_id');
    $hOrder = "st.id";
    include($incPath . 'pagging.php');
    // echo $sQuery;  die;
    $appData = array();
    $id = 1;
    foreach ($sqlReturn as $row) {
        $status_name = $row['name'];
        if (brp_strtolower($status_name) == 'approved') {
            $status_class = 'success';
        } else if (brp_strtolower($status_name) == 'in progress') {
            $status_class = 'warning';
        } else {
            $status_class = 'primary';
        }

        $row_data = array();
        $row_data[] = $id;
        $row_data[] = $row['cmp_unique_id'];
        $row_data[] = $row['company_name'];
        $row_data[] = $row['department'];
        $row_data[] = date('d M, Y', strtotime($row['cdate']));
        $row_data[] = ($row['due_date'] && $row['due_date'] != '0000-00-00') ? date('d M, Y', strtotime($row['due_date'])) : '-'; // -amter
        $row_data[] = '<span class="label label-' . $status_class . '">' . $status_name . '</span>';
        $status_btn = '';
//        if ($_SESSION['user_type'] == '2') {
//            $row_data[] = find_user_name($dbcon, $row['user_id']);
//        }
        if (brp_strtolower($status_name) != "approved") {
            $status_btn = '<button class="btn btn-xs btn-success" title="Change Status" data-toggle="tooltip" data-placement="top" onclick="change_status(' . $row['id'] . ')"><i class="fa fa-check"></i></button>';
        }

        $view_btn = '<a href="' . ROOT . SUPPORT_ROOT . 'support_view/' . $row['id'] . '" class="btn btn-xs btn-primary" title="View" data-toggle="tooltip" data-placement="top"><i class="fa fa-eye"></i></a>';

        $row_data[] = $status_btn . ' ' . $view_btn;

        $appData[] = $row_data;
        $id++;
    }
    $output['aaData'] = $appData;
    echo brp_json_encode($output);
} else if (brp_strtolower($POST['mode']) == "add") {
    $description = brp_sc_mysql_escape($_POST["description"], $dbcon);
    $description = str_ireplace(array("\r", "\n", '\r', '\n'), '', $description);

    $info['department'] = $POST['department'];
    $info['page_link'] = $POST['page_link'];
    $info['description'] = $description;

    $info['user_id'] = $_SESSION['user_id'];
    $info['company_id'] = $_SESSION['company_id'];
    $info['cdate'] = date("Y-m-d H:i:s");

    $info['cmp_unique_id'] = $POST['cmp_unique_id'];
    $info['company_name'] = $POST['company_name'];

    if ($_FILES["documents"]["name"]) {
        $fileName = explode('.', $_FILES["documents"]["name"]);
        $ext = end($fileName);
        $name = $fileName[0] . '_' . time() . '.' . $ext;
        $path = '../../view/upload/';
        $location = $path . $name;

        if (move_uploaded_file($_FILES["documents"]["tmp_name"], $location)) {
            $info['upload_document'] = $name;
        }
    }

    if (!$info['cmp_unique_id'] || !$info['company_name']) {
        $arr['msg'] = '-2';
    } else {
        $insertid = add_record('tbl_support_ticket', $info, $dbcon);
        $arr['msg'] = ($insertid) ? '1' : '0';
    }

    echo json_encode($arr);
} else if (brp_strtolower($POST['mode']) == "change_status") {
    $info['support_status_id'] = $POST['support_status_id'];
    $info['updated_at'] = date("Y-m-d H:i:s");
    $info['user_id'] = $_SESSION['user_id'];
    $info['company_id'] = $_SESSION['company_id'];

    if ($POST['due_date']) {
        $info['due_date'] = date('Y-m-d', strtotime($POST['due_date']));
    }

    if ($POST['emp_id']) {
        $info['emp_id'] = $POST['emp_id'];
    }

    if ($POST['change_user']) {
        $info['change_user'] = $POST['change_user'];
    }

    if ($POST['change_comment']) {
        $info['change_comment'] = $POST['change_comment'];
    }

    $updateid = update_record('tbl_support_ticket', $info, "id=" . $POST['id'], $dbcon);

    if ($updateid) {
        $statusData = getSupportStatusById($dbcon, $POST['support_status_id']);
        if ($statusData) {
            $info1['support_id'] = $POST['id'];
            $info1['action'] = 'Status Changed to ' . $statusData['name'];
            $info1['cdate'] = date("Y-m-d H:i:s");
            $info1['user_id'] = $_SESSION['user_id'];
            $info1['company_id'] = $_SESSION['company_id'];

            add_record('tbl_support_history', $info1, $dbcon);
        }
        echo '1';
    } else {
        echo '0' . $dbcon->error;
    }
} else if (brp_strtolower($POST['mode']) == "preedit") {
    $q = $dbcon->query("SELECT st.* FROM tbl_support_ticket as st WHERE st.id= '$POST[id]'");
    $r = brp_mysqli_fetch_assoc($q);
    if ($r && $r['due_date']) {
        $r['due_date'] = date('d-m-Y', strtotime($r['due_date']));
    }
    $row['data'] = $r;
    $row['support_status'] = getSupportDetail($dbcon, $r['support_status_id']);
    echo brp_json_encode($row);
}
?>