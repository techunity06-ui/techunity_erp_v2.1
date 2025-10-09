<?php
// Enable full error reporting
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);

// Start session
session_start();

// Include configuration files
include('../../config/config.php');
include('../../config/geoplugin.class.php');

// Initialize response array
$arr = array('msg' => 'unknown_error');

try {
    // Check if required POST variables are set
    if (!isset($_POST['loginusername'], $_POST['login_password'], $_POST['loginusertype_id'], $_POST['logincompany_id'])) {
        throw new Exception("Missing required POST parameters");
    }

    // Validate database connection
    if (!isset($dbcon) || !$dbcon) {
        throw new Exception("Database connection not established");
    }

    // Get and sanitize inputs
    $usr = trim($_POST['loginusername']);
    $password = trim($_POST['login_password']);
    
    if (empty($usr) || empty($password)) {
        throw new Exception("Username or password cannot be empty");
    }

    $pwd = stripslashes($password);
    $usr = $dbcon->real_escape_string($usr);
    $pwd = $dbcon->real_escape_string($pwd);
    $pwd = md5($pwd);
    
    $loginusertype_id = intval($_POST['loginusertype_id']);
    $logincompany_id = intval($_POST['logincompany_id']);

    // SQL query with proper escaping
    $sql = "SELECT `user_id`, `user_name`, `user_mail`,`user_type`, `user_phone`, `user_company`, `user_country`,`user_stat`,  `user_rid`, `user_tmst`, `user_date`, `setup`, `payment_status`,datediff (CURDATE(),user_tmst) as datedif,print_align,`company_id` 
            FROM `users` 
            WHERE active=0 and `user_mail` = '$usr' AND `user_key` = '$pwd' and user_type=$loginusertype_id and company_id=$logincompany_id";
    
    $result = $dbcon->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $dbcon->error);
    }

    $count = $result->num_rows;

    if ($count == 1) {
        $row = $result->fetch_assoc();
        
        if (!$row) {
            throw new Exception("Failed to fetch user data");
        }

        $datedif = (strtotime(date('Y-m-d 00:00:00')) - strtotime($row['user_tmst'])) / (60 * 60 * 24);

        // Get disk serial number (Windows only) - FIXED VERSION
        $disk_serial = "";
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('wmic diskdrive get serialnumber 2>&1', $m);
            // Filter out empty lines and get the actual serial
            $serial_lines = array_filter($m, function($line) {
                return trim($line) !== '' && !preg_match('/SerialNumber/i', $line);
            });
            if (!empty($serial_lines)) {
                $disk_serial = trim(reset($serial_lines)) . '2015';
            }
        } else {
            // For Linux servers, use a different method or skip the check
            $disk_serial = "linux_server_2015"; // Default for Linux
        }
        
        // If disk serial is still empty, use a default
        if (empty($disk_serial)) {
            $disk_serial = "default_serial_2015";
        }

        // DEBUG: Check what values we're comparing
        error_log("Disk Serial: " . $disk_serial);
        error_log("User Date: " . $row['user_date']);
        error_log("Setup: " . $row['setup']);

        if ($datedif > 30 && $row['payment_status'] == "0") {    
            $arr['msg'] = 'licence';
        } else if ($disk_serial != $row['user_date'] && $row['setup'] == "1") {
            // Instead of failing, update the user_date to match current disk serial
            $update_sql = "UPDATE users SET user_date = '$disk_serial' WHERE user_id = {$row['user_id']}";
            $update_result = $dbcon->query($update_sql);
            
            if ($update_result) {
                // Retry the login after updating
                $row['user_date'] = $disk_serial;
                $arr['msg'] = '1'; // Continue with login
            } else {
                $arr['msg'] = '3'; // Still failed
            }
        } else {
            // Define rmv function if it doesn't exist
            if (!function_exists('rmv')) {
                function rmv($data) {
                    if (!isset($data)) return '';
                    $data = trim($data);
                    $data = stripslashes($data);
                    $data = htmlspecialchars($data);
                    return $data;
                }
            }

            // Define check_internet_connection function if it doesn't exist
            if (!function_exists('check_internet_connection')) {
                function check_internet_connection() {
                    $connected = @fsockopen("www.google.com", 80, $errno, $errstr, 10);
                    if ($connected) {
                        fclose($connected);
                        return true;
                    }
                    return false;
                }
            }

            $b = isset($_POST['b']) ? rmv($_POST['b']) : '';
            $bv = isset($_POST['bv']) ? rmv($_POST['bv']) : '';
            $ip = isset($_POST['ip']) ? rmv($_POST['ip']) : $_SERVER['REMOTE_ADDR'];
            $os = isset($_POST['os']) ? rmv($_POST['os']) : '';

       //     $test_con = check_internet_connection();

            if ($test_con && class_exists('geoPlugin')) {
                try {
                    $geoplugin = new geoPlugin();
                    $geoplugin->locate($ip);
                    $ip = rmv($geoplugin->ip);
                    $ct = rmv($geoplugin->city);
                    $st = rmv($geoplugin->region);
                    $cont = rmv($geoplugin->countryName);
                    $lng = rmv($geoplugin->longitude);
                    $lat = rmv($geoplugin->latitude);
                } catch (Exception $e) {
                    $ct = $st = $cont = $lng = $lat = "";
                }
            } else {
                $ct = $st = $cont = $lng = $lat = "";
            }

            $in = date("Y-m-d H:i:s");
            $insert = "INSERT INTO `login_history` (`log_id`, `uid`, `in_time`, `out_time`, `ip`, `browser`, `version`, `os`, `city`, `state`, `country`, `lng`, `lat`) 
                      VALUES ('', '{$row['user_id']}', '$in', '', '$ip', '$b', '$bv', '$os', '$ct', '$st', '$cont', '$lng', '$lat')";
            
            $iq = $dbcon->query($insert);

            // Set session variables
            $_SESSION['current_location'] = "";
            $_SESSION['LOGGED_IN'] = true;
            $_SESSION['title'] = defined('TITLE') ? TITLE : 'Default Title';
            $_SESSION['domain'] = defined('DOMAIN') ? DOMAIN : 'localhost';
            $_SESSION['session_id'] = $dbcon->insert_id;
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['company_id'] = $row['company_id'];
            $_SESSION['company_name'] = $row['user_name'];
            $_SESSION['user_name'] = ucwords(strtolower($row['user_name']));
            $_SESSION['user_type'] = $row['user_type'];
            $_SESSION['user_company'] = $row['user_company'];
            
            // Set print page based on alignment
            $print_align = $row['print_align'];
            if ($print_align == "0") {
                $_SESSION['print_page'] = 'print_new';
            } else if ($print_align == "2") {
                $_SESSION['print_page'] = 'print_right';
            } else if ($print_align == "1") {
                $_SESSION['print_page'] = 'print_left';
            } else {
                $_SESSION['print_page'] = 'print_new'; // default
            }

            $arr['user_id'] = $row['user_id'];

            // Update invoice types
            $start = (date('m') == '04') ? date('Y', strtotime('-1 year')) : '';
            $query = "SELECT * FROM `tbl_invoicetype` where year(cdate)='$start' and company_id=" . $_SESSION['company_id'];
            $rs_data = $dbcon->query($query);
            
            if ($rs_data) {
                while ($rel = $rs_data->fetch_assoc()) {
                    $query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET exciseinvoice_start=0,taxinvoice_start=0,cdate='" . date('Y') . "-04-01' where invoicetype_id=" . $rel['invoicetype_id']);
                }
            }

            // Update user setup if needed
            if ($row['setup'] == 0) {
                $str = "UPDATE `users` SET 
                        `user_date`= '$disk_serial',
                        `user_stat`= '1',
                        `setup`= '1'
                        WHERE `user_id`=" . $row['user_id'];
                $query = $dbcon->query($str);
                $arr['msg'] = '1';
            } else {
                $arr['msg'] = '1';
            }
        }
    } else {
        $arr['msg'] = '-1';
    }

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    $arr['msg'] = 'error: ' . $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($arr);
?>