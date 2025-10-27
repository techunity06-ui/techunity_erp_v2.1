<?php
session_start(); // Start session
ini_set('memory_limit', '-1');
set_time_limit(0);
$AJAX = true;   
include('../../include/urlfileinner.php');
$incPath = $path . 'include/';
include_once($incPath . "common_send_email.php");

$POST = ($_POST != NULL) ? bulk_filter($dbcon, $_POST) : bulk_filter($dbcon, $_GET);
$getspecialConfiguration = getspecialConfiguration($dbcon);
$companyConfiguration = getCompanyConfiguration($dbcon);

// Extract configuration values
$crm_pro_type = $companyConfiguration['crm_pro_type'];
$so_pro_type = $companyConfiguration['so_pro_type'];
$indent_po_pro_type = $companyConfiguration['indent_po_pro_type'];
$production_pro_type = $companyConfiguration['production_pro_type'];
$crm_pro_search = $companyConfiguration['crm_pro_search'];
$purchase_pro_search = $companyConfiguration['purchase_pro_search'];
$sales_pro_search = $companyConfiguration['sales_pro_search'];
$bom_pro_search = $companyConfiguration['bom_pro_search'];
$production_pro_search = $companyConfiguration['production_pro_search'];
$rejection_pro_type = $companyConfiguration['rejection_pro_type'];
$inventory_pro_type = $companyConfiguration['inventory_pro_type'];

$inquiry_type = $POST['inquiry_type'];
$product_category = $POST['product_category'];
$quotation_id = $POST['quotaion_id'];
$type = strtolower($POST['type']);
$search = strtolower($POST['search']);

// Initialize conditions array
$conditions = [];

// Determine product type condition
if (isset($POST['product_type']) && $POST['product_type'] != "") {
    $conditions[] = "pro.product_type = " . $POST['product_type'];
} elseif ($type == 'crm_pro_type') {
    $conditions[] = "pro.product_type in ($crm_pro_type)";
} elseif ($type == 'so_pro_type') {
    $conditions[] = "pro.product_type in ($so_pro_type)";
} elseif ($type == 'production_pro_type') {
    $conditions[] = "pro.product_type in ($production_pro_type)";
} elseif ($type == 'indent_po_pro_type') {
    $conditions[] = "pro.product_type in ($indent_po_pro_type)";
} elseif ($type == 'rejection_pro_search') {
    $conditions[] = "pro.product_type in ($rejection_pro_type)";
} elseif ($type == 'inventory_pro_type') {
    $conditions[] = "pro.product_type in ($inventory_pro_type)";
}

// Check if process is required
if (isset($POST['is_process_required_check']) && $POST['is_process_required_check'] == '1') {
    $pro_typ_q = "SELECT group_concat(product_type_id) as product_type FROM pro_ms_product_type WHERE product_type_status = 0 AND process_required = 1 AND company_id IN (0, " . $_SESSION['company_id'] . ")";
    $pro_typ_rs = $dbcon->query($pro_typ_q);
    $pro_typ_rw = brp_mysqli_fetch_assoc($pro_typ_rs);
    if (!empty($pro_typ_rw['product_type'])) {
        $conditions[] = "pro.product_type in (" . $pro_typ_rw['product_type'] . ")";
    }
}

// Determine product search type
$pro_search = [];
if ($search == 'crm_pro_search') {
    $pro_search = explode(",", $crm_pro_search);
} elseif ($search == 'purchase_pro_search') {
    $pro_search = explode(",", $purchase_pro_search);
} elseif ($search == 'sales_pro_search') {
    $pro_search = explode(",", $sales_pro_search);
} elseif ($search == 'bom_pro_search') {
    $pro_search = explode(",", $bom_pro_search);
} elseif ($search == 'production_pro_search') {
    $pro_search = explode(",", $production_pro_search);
} elseif ($search == 'rejection_pro_search') {
    $pro_search = explode(",", $rejection_pro_type);
}

// Add product category condition if specified
if (isset($POST['product_category']) && $POST['product_category'] != "") {
    $conditions[] = "pro.product_category = " . $POST['product_category'];
}

// Add company ID condition
$conditions[] = "pro.company_id IN (0, " . $_SESSION['company_id'] . ")";

// Add product status condition
$conditions[] = "pro.product_status = 0";

// Build the WHERE clause
$whereClause = implode(" AND ", $conditions);

// Construct the base query
if (!empty($quotation_id)) {
    $query = "SELECT qtrn.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name, qtrn.quot_trn_id
              FROM tbl_quotation_trn as qtrn
              LEFT JOIN product_mst as pro ON pro.product_id = qtrn.product_id
              LEFT JOIN tbl_drawing as dr ON dr.drawing_id = pro.drawing_id
              WHERE qtrn.quot_trn_status = 0 AND qtrn.quotation_id = $quotation_id";
} else {
    if ($getspecialConfiguration['filter_concept_permission'] == 1) {
        if ($POST['po_type'] == 1) {
            $query = "SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name
                      FROM product_mst as pro
                      LEFT JOIN tbl_drawing as dr ON dr.drawing_id = pro.drawing_id
                      WHERE pro.product_type = 8 AND $whereClause
                      ORDER BY pro.product_name";
        } else {
            $query = "SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name
                      FROM product_mst as pro
                      LEFT JOIN tbl_drawing as dr ON dr.drawing_id = pro.drawing_id
                      WHERE $whereClause
                      ORDER BY pro.product_name";
        }
    } else {
        if ($inquiry_type == '2') {
            $query = "SELECT product_id, product_name, product_alias_name
                      FROM product_mst
                      WHERE product_status = 0 AND product_type = '-1' AND company_id IN (0, " . $_SESSION['company_id'] . ")
                      ORDER BY product_name";
        } else {
            if ($POST['po_type'] == 1) {
                $query = "SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name
                          FROM product_mst as pro
                          LEFT JOIN tbl_drawing as dr ON dr.drawing_id = pro.drawing_id
                          WHERE pro.product_type = 8 AND $whereClause
                          ORDER BY pro.product_name";
            } else {
                if ($getspecialConfiguration['aeon_permission'] == 1 && $product_category != '') {
                    $conditions[] = "pro.product_category = $product_category";
                    $whereClause = implode(" AND ", $conditions);
                }
                $query = "SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name
                          FROM product_mst as pro
                          LEFT JOIN tbl_drawing as dr ON dr.drawing_id = pro.drawing_id
                          WHERE $whereClause
                          ORDER BY pro.product_name";
            }
        }
    }
}

// Execute the query
$result = $dbcon->query($query);

// Prepare the response array
$row1 = [
    [""], // First column (empty)
    ["Select Product"], // Second column header
    [""] // Third column (empty)
];

// Process the results
while ($row = mysqli_fetch_array($result)) {
    $drawing_number = (in_array('drawing', $pro_search)) ? " -- (" . $row['drawing_number'] . ")" : "";
    $item_code = (in_array('item', $pro_search)) ? " -- (" . $row['product_icode'] . ")" : "";
    $alias = (in_array('alias', $pro_search)) ? " -- (" . $row['product_alias_name'] . ")" : "";

    $row1[0][] = $row['product_id'];
    $row1[1][] = $row['product_name'] . ' ' . $item_code . ' ' . $drawing_number . ' ' . $alias;
    $row1[2][] = $row['quot_trn_id'] ?? '';
}

// Output the result as JSON
echo json_encode($row1);
?>
