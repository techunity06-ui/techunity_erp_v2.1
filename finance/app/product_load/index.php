<?php
session_start(); //start session
ini_set('memory_limit', '-1');
set_time_limit(0);
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if (strtolower($POST['mode']) == "product_load") {
    $drawing_number = '';
    $item_code = '';
    $alias = '';
    $whr = '';

    $companyConfiguration = getCompanyConfiguration($dbcon);
    $so_pro_type = isset($companyConfiguration['so_pro_type']) ? $companyConfiguration['so_pro_type'] : '';
    $sales_pro_search = isset($companyConfiguration['sales_pro_search']) ? $companyConfiguration['sales_pro_search'] : '';

    // Ensure $pro_search is an array
    $pro_search = array_filter(array_map('trim', explode(",", $sales_pro_search)));
    if (!is_array($pro_search)) $pro_search = [];

    // sanitize product_type_sel: accept only numeric values; ignore "undefined" or non-numeric
    $product_type_sel = isset($POST['product_type_sel']) ? trim($POST['product_type_sel']) : '';

    if ($product_type_sel !== "" && is_numeric($product_type_sel)) {
        $whr .= " AND pro.product_type = " . intval($product_type_sel);
    } else {
        // fallback to company-configured product types (assumed comma-separated numbers)
        if ($so_pro_type !== '') {
            // basic validation: remove anything that isn't digit or comma
            $so_pro_type_clean = preg_replace('/[^0-9,]/', '', $so_pro_type);
            if ($so_pro_type_clean !== '') {
                $whr .= " AND pro.product_type IN (" . $so_pro_type_clean . ")";
            }
        }
    }

    // sanitize product_category similarly
    if (isset($POST['product_category']) && $POST['product_category'] !== "" && is_numeric($POST['product_category'])) {
        $whr .= " AND pro.product_category = " . intval($POST['product_category']);
    }

    // Build query (use pro prefix consistently)
    $query = "SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name
              FROM product_mst AS pro
              LEFT JOIN tbl_drawing AS dr ON dr.drawing_id = pro.drawing_id
              WHERE pro.product_status = 0 " . $whr;

    $result = $dbcon->query($query);

    // debug: if query fails, log error and return empty array
    if (!$result) {
        error_log("product_load SQL error: " . $dbcon->error . " -- Query: " . $query);
        echo json_encode([[], []]);
        exit;
    }

    // prepare output arrays so json is consistent even if no rows
    $ids = [];
    $labels = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $drawing_number = in_array('drawing', $pro_search) && !empty($row['drawing_number']) ? " -- (" . $row['drawing_number'] . ")" : '';
        $item_code = in_array('item', $pro_search) && !empty($row['product_icode']) ? " -- (" . $row['product_icode'] . ")" : '';
        $alias = in_array('alias', $pro_search) && !empty($row['product_alias_name']) ? " -- (" . $row['product_alias_name'] . ")" : '';

        $ids[] = $row['product_id'];
        $labels[] = $row['product_name'] . $item_code . $drawing_number . $alias;
    }

    echo json_encode([$ids, $labels]);
}

?>