<?php
session_start();
include('../../config/config.php');
include('../../config/geoplugin.class.php');
include('../key_api.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ----------------------
// Initialize Variables
// ----------------------
$usr = isset($_POST['loginusername']) ? trim($_POST['loginusername']) : '';
$password = isset($_POST['login_password']) ? trim($_POST['login_password']) : '';
$usertype_id = isset($_POST['loginusertype_id']) ? intval($_POST['loginusertype_id']) : 0;
$company_id  = isset($_POST['logincompany_id']) ? intval($_POST['logincompany_id']) : 0;
$attendance  = $_POST['attendance'] ?? '';
$b  = $_POST['b'] ?? '';
$bv = $_POST['bv'] ?? '';
$os = $_POST['os'] ?? '';
$ip = $_POST['ip'] ?? '';

// Escape inputs
$usr = $dbcon->real_escape_string($usr);
$pwd = md5($dbcon->real_escape_string($password));

// ----------------------
// User Lookup
// ----------------------
$sql = "SELECT `user_id`, `user_name`, `user_mail`,`user_type`, `user_phone`, `user_company`, `user_country`,
               `user_stat`, `user_rid`, `user_tmst`, `user_date`, `setup`, `payment_status`,
               DATEDIFF(CURDATE(),user_tmst) as datedif, print_align, `company_id`,`employee_id`,`branch_id`,ip_add 
        FROM `users` 
        WHERE active=0 
          AND `user_mail` = '$usr' 
          AND `user_key` = '$pwd' 
          AND user_type=$usertype_id 
          AND company_id=$company_id";

$result = $dbcon->query($sql);

if(!$result){
    echo json_encode(['msg'=>'sql_error','error'=>$dbcon->error]);
    exit;
}

$row = $result->fetch_assoc();
if(!$row){
    echo json_encode(['msg'=>'invalid_login']);
    exit;
}

// ----------------------
// License & Key Check
// ----------------------
$key_val = ['msg'=>'Pass']; // default if API fails
// $test_con = function_exists('check_internet_connection') ? check_internet_connection() : false;
if($test_con){
    $query_k = "SELECT cmp_unique_id,cust_key FROM tbl_company WHERE company_id=".$row['company_id'];
    $relk = mysqli_fetch_assoc($dbcon->query($query_k)); 
    if(!empty($relk)){
        $key_check = get_key_api_data($relk['cmp_unique_id'], $relk['cust_key']);
        $key_val = json_decode($key_check,true) ?: ['msg'=>'fail'];
    }
    if($row['user_type']==1){ $key_val['msg']="Pass"; }
}

// ----------------------
// IP Restriction
// ----------------------
$query2="SELECT ip_add_login FROM tbl_company_configuration WHERE company_id=".$row['company_id'];
$rel2=mysqli_fetch_assoc($dbcon->query($query2));
$localIP = $_SERVER['REMOTE_ADDR'] ?? '';

if($rel2 && $rel2['ip_add_login']=="1" && $localIP != ($row['ip_add'] ?? '') && $row['user_type']!=2){
    echo json_encode(['msg'=>'ip_error','company_id'=>$row['company_id'],'ip_add'=>$localIP]);
    exit;
}

// ----------------------
// License Check
// ----------------------
$datedif = (strtotime(date('Y-m-d 00:00:00')) - strtotime($row['user_tmst'])) / (60*60*24);
if(($datedif > 15 && $row['payment_status']=="0") || ($key_val['msg'] ?? '')=="fail"){
    echo json_encode(['msg'=>'licence','company_id'=>$row['company_id']]);
    exit;
}

// ----------------------
// Geo Location (safe)
// ----------------------
$ct=$st=$cont=$lng=$lat="";
if($test_con){
    $geoplugin = new geoPlugin();
    $geoplugin->locate($ip);

    $ip   = $geoplugin->ip ?? $ip;
    $ct   = $geoplugin->city ?? '';
    $st   = $geoplugin->region ?? '';
    $cont = $geoplugin->countryName ?? '';
    $lng  = $geoplugin->longitude ?? '';
    $lat  = $geoplugin->latitude ?? '';
}

// ----------------------
// Insert Login History
// ----------------------
$in = date("Y-m-d H:i:s");
$insert = "INSERT INTO `login_history`
           (`uid`, `in_time`, `ip`, `browser`, `version`, `os`, `city`, `state`, `country`, `lng`, `lat`,`attendance`) 
           VALUES ('{$row['user_id']}','$in','$ip','$b','$bv','$os','$ct','$st','$cont','$lng','$lat','$attendance')";
$dbcon->query($insert);
$session_id = $dbcon->insert_id;

// ----------------------
// Create Session
// ----------------------
$_SESSION['LOGGED_IN'] = true;
$_SESSION['title'] = TITLE;
$_SESSION['domain'] = DOMAIN;
$_SESSION['session_id'] = $session_id;
$_SESSION['user_id'] = $row['user_id'];
$_SESSION['company_id'] = $row['company_id'];
$_SESSION['company_name'] = $row['user_name'];
$_SESSION['user_name'] = ucwords(strtolower($row['user_name']));
$_SESSION['user_type'] = $row['user_type'];
$_SESSION['user_company'] = $row['user_company'];
$_SESSION['attendance'] = $attendance;
$_SESSION['employee_id'] = $row['employee_id'] ?? '';
$_SESSION['branch_id'] = $row['branch_id'] ?? '';

// ----------------------
// Company Currency
// ----------------------
$companySql = "SELECT com.currency_id,cu.currency_name,cu.currency_rate 
               FROM tbl_company as com 
               LEFT JOIN currency_mst as cu ON com.currency_id=cu.currencyid 
               WHERE com.company_id='".$row['company_id']."'";
$comapnayex = $dbcon->query($companySql);
if($comapnayex && $comapnayex->num_rows>0){
    $comapnyrow = $comapnayex->fetch_assoc();
    $_SESSION['currency_id'] = $comapnyrow['currency_id'];
    $_SESSION['currency_name'] = $comapnyrow['currency_name'];
    $_SESSION['currency_rate'] = $comapnyrow['currency_rate'];
}

// ----------------------
// Financial Year
// ----------------------
$chfina = $dbcon->query("SELECT * FROM tbl_financial_year 
                         WHERE isdelete=0 AND current_status=1 
                         AND company_id=".$row['company_id']);
if($chfina && $chfina->num_rows>0){
    $getfi = mysqli_fetch_assoc($chfina);
    $_SESSION['financial_year_id']=$getfi['financial_year_id'];
    $_SESSION['fiancial_year']=$getfi['fiancial_year'];
    $_SESSION['financial_start_date']=$getfi['financial_start_date'];
    $_SESSION['financial_end_date']=$getfi['financial_end_date'];
}

// ----------------------
// Resource Mapping
// ----------------------
$resourceSql = "SELECT * FROM tbl_resource 
                WHERE loggin_id='".$row['user_id']."' 
                  AND company_id='".$row['company_id']."'";
$resourceex = $dbcon->query($resourceSql);
if($resourceex && $resourceex->num_rows>0){
    $resourcerow = $resourceex->fetch_assoc();
    $_SESSION['resource_id'] = $resourcerow['resource_id'];
    $_SESSION['resource_name'] = $resourcerow['resource_name'];
}

// ----------------------
// Print Align
// ----------------------
$_SESSION['print_page'] = match($row['print_align'] ?? '0') {
    '0' => 'print_new',
    '1' => 'print_left',
    '2' => 'print_right',
    default => 'print_new'
};

// ----------------------
// First-time setup update
// ----------------------
if(($row['setup'] ?? 0) == 0){
    $str = "UPDATE `users` 
            SET user_date='server', user_stat='1', setup='1'
            WHERE user_id=".$row['user_id'];
    $dbcon->query($str);
}

// ----------------------
// Success Response
// ----------------------
echo json_encode(['msg'=>'1','user_id'=>$row['user_id']]);
?>
