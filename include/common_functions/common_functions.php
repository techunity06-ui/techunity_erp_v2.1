<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
ini_set('memory_limit', '-1');
set_time_limit(0);


include_once("common_functions_2.php");

function quotation_sub_data($dbcon, $id, $field_id)
{

    // $str = '';
    $query = "Select * from tbl_item_master_field_value where item_master_field_value_status=0  and item_master_field_id=" . $field_id . " and item_master_field_value_id=" . $id . " and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $row = brp_mysqli_fetch_assoc($rs_type);

    return $row;
}
function get_header($dbcon, $align, $width, $height)
{
    $set_head = get_company_data($dbcon, $_SESSION['company_id']);
    $company_config = getCompanyConfiguration($dbcon);
    $logo = '<td style="border: none;vertical-align:top" width="30%"><img src="' . DOMAIN_F . LOGO . $set_head["logo"] . '" style="width:' . $width . '; height: ' . $height . ';" /></td>';
    $text = '<td style="border: none; text-align: left;text-transform: capitalize;vertical-align:top" width="70%"><h3><b>' . $set_head["company_name"] . ' </b></h3>' . $set_head["address"] . '<br>Contact no. ' . $set_head['contact_no'] . '&nbsp;&nbsp;&nbsp;<br>Email: ' . strtolower($set_head['website']) . '&nbsp;&nbsp;&nbsp;CIN : ' . $set_head['cin'] . '</td>';
    if ($company_config['header_logo'] == 3) {
        $logo = '<td style="border: none;' . $align . '" width="100%"><img src="' . DOMAIN_F . LOGO . $set_head["logo"] . '" style="width:' . $width . '; height: ' . $height . ';" /></td>';
        $header = '<table style="border: none;"><tr style="border: none;">' . $logo . '</tr></table>';
    } else if ($company_config['header_logo'] == 2) {
        $header = '<table style="border: none;"><tr style="border: none;">' . $text . '' . $logo . '</tr></table>';
    } else if ($company_config['header_logo'] == 1) {
        $header = '<table style="border: none;"><tr style="border: none;">' . $logo . '' . $text . '</tr></table>';
    } else if ($company_config['header_logo'] == 0) {
        $text = '<td style="border: none; text-align: center;" width="60%"><h4><b>' . $set_head["company_name"] . ' </b><br/>' . $set_head["address"] . '  Contact no. ' . $set_head['contact_no'] . '&nbsp;&nbsp;&nbsp;Email: ' . $set_head['website'] . '</h4></td>';
        $header = '<table style="border: none;"><tr style="border: none;">' . $text . '</tr></table>';
    } else {
        $header = '<img src="' . DOMAIN_F . LOGO . $set_head["logo"] . '" style="width:8.27in;" />';
    }
    return $header;
}

function get_isd_no($dbcon, $isd_id)
{
    $str = '';
    $query = "select * from isd_code_mst where isd_status=0";
    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose ISD No--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['isd_id'] == $isd_id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['isd_id'] . '">+ ' . $row['phonecode'] . ' ' . $row['nicename'] . ' (' . $row['iso3'] . ')</option>';
    }
    return $str;
}

function get_isd_data_mst($dbcon, $isd_id)
{
    $query = "select * from isd_code_mst where isd_id=" . $isd_id;
    $result = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($result);

    return $row;
}
function get_first_name($dbcon, $id)
{
    $str = '';
    $query = "Select * from mst_first_name where first_name_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose First Name--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['first_name_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['first_name_id'] . '" data-pcode="' . $row['code'] . '">' . $row['first_name'] . '</option>';
    }
    return $str;
}
function get_surface_area($dbcon, $id = null)
{
    $str = '<option value="" >--Choose Surface Area Name--</option>';
    if (!$dbcon || !isset($_SESSION['company_id'])) {
        return $str;
    }

    $company_id = intval($_SESSION['company_id']);
    $query = "SELECT * FROM mst_surface_area WHERE surface_area_status = 0 AND company_id = " . $company_id;
    $rs_type = $dbcon->query($query);

    if ($rs_type) {
        while ($row = brp_mysqli_fetch_assoc($rs_type)) {
            if (!is_array($row))
                continue;

            $sel = '';
            if ($row['surface_area_id'] == $id) {
                $sel = 'selected="selected"';
            }

            $surface_area_id = isset($row['surface_area_id']) ? htmlspecialchars($row['surface_area_id']) : '';
            $code = isset($row['code']) ? htmlspecialchars($row['code']) : '';
            $surface_area_name = isset($row['surface_area_name']) ? htmlspecialchars($row['surface_area_name']) : '';

            $str .= '<option ' . $sel . ' value="' . $surface_area_id . '" data-pcode="' . $code . '">' . $surface_area_name . '</option>';
        }
    }
    return $str;
}
function get_impregnation($dbcon, $id)
{
    $str = '';
    $query = "Select * from mst_impregnation where impregnation_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose Impregnation Name--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['impregnation_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['impregnation_id'] . '" data-pcode="' . $row['code'] . '">' . $row['impregnation_name'] . '</option>';
    }
    return $str;
}
function get_model($dbcon, $id)
{
    $str = '';
    $query = "Select * from mst_product_model where product_model_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose Model Name--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['product_model_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['product_model_id'] . '" data-pcode="' . $row['code'] . '">' . $row['product_model_name'] . '</option>';
    }
    return $str;
}
function get_installation($dbcon, $id)
{
    $str = '';
    $query = "Select * from mst_installation where installation_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose Installation Name--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['installation_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['installation_id'] . '" data-pcode="' . $row['code'] . '">' . $row['installation_name'] . '</option>';
    }
    return $str;
}
function get_mst_type($dbcon, $id)
{
    $str = '';
    $query = "Select * from mst_type where type_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose Type Name--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['type_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['type_id'] . '" data-pcode="' . $row['code'] . '">' . $row['type_name'] . '</option>';
    }
    return $str;
}

function get_type_mst($dbcon, $id)
{
    $str = '';
    $query = "Select * from mst_typefi where type_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose Type Name--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['type_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['type_id'] . '" data-pcode="' . $row['code'] . '">' . $row['type_name'] . '</option>';
    }
    return $str;
}

function get_cartridge_dia($dbcon, $id)
{
    $str = '';
    $query = "Select * from mst_cartridge where cartridge_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose Cartridge Name--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['cartridge_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['cartridge_id'] . '" data-pcode="' . $row['code'] . '">' . $row['cartridge_name'] . '</option>';
    }
    return $str;
}

function get_class_mst($dbcon, $id)
{
    $str = '';
    $query = "Select * from mst_class where class_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose Class Name--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['class_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['class_id'] . '" data-pcode="' . $row['code'] . '">' . $row['class_name'] . '</option>';
    }
    return $str;
}
function getdieallocation($dbcon, $id)
{
    $query = "select * from product_mst where product_status='0' and company_id in (0,$_SESSION[company_id]) and product_category = '80'";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Select die</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . '</option>';
    }
    return $str;
}
function getprintpermission($dbcon, $id)
{
    $str = '';
    $query = $dbcon->query("SELECT * FROM print_permission WHERE status = 0 AND company_id = '" . $_SESSION['company_id'] . "'");
    $chkQuery = mysqli_fetch_assoc($query);
    $qry = $dbcon->query("SELECT * FROM print_setup_mst WHERE id IN (" . $chkQuery['print_permission'] . ")");
    $str .= '<option value="" >--Choose Print name--</option>';
    while ($row = mysqli_fetch_assoc($qry)) {
        $sel = '';
        if ($row['id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['id'] . '">' . $row['page_path'] . '</option>';
    }
    return $str;
}
function getdocumentlist($dbcon, $id)
{
    $str = '';
    $query = "SELECT * FROM tbl_document_type_mst WHERE document_status = 0 ORDER BY document_name ASC";
    $chkQuery = $dbcon->query($query);
    $str .= '<option value="" >--Choose Document--</option>';
    while ($row = mysqli_fetch_assoc($chkQuery)) {
        $sel = '';
        if ($row['document_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['document_id'] . '">' . $row['document_name'] . '</option>';
    }
    return $str;
}
function getpagePermission($dbcon, $id = false)
{
    $query = "SELECT * FROM `tbl_page_permission` WHERE company_id = '" . $_SESSION['company_id'] . "'";

    if ($id) {
        $query .= " AND `permission_id` = $id";
    }

    $query .= " ORDER BY permission_id DESC LIMIT 1";

    $q = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($q);
    return $row;
}
function get_print_type($dbcon, $id)
{
    $str = '';
    $query = "SELECT * FROM print_type_mst WHERE print_status = 0";
    $rs_type = $dbcon->query($query);
    $str .= '<option value="" >--Choose Print Type--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['id'] . '">' . $row['print_type_name'] . '</option>';
    }
    return $str;
}
/* Added by : Maulik Kapatel Start */
function get_po_no_grn($dbcon, $id)
{
    $str = '';
    $query = "select po.purchaseorder_no,po.purchaseorder_id from tbl_grn_trn as gt
        left join tbl_purchaseordertrn as pt on pt.purchaseordertrn_id = gt.purchaseordertrn_id
        left join tbl_purchaseorder as po on po.purchaseorder_id = pt.purchaseorder_id
        where grn_trn_status=0 and po.purchaseorder_id !=0  group by purchaseorder_id";
    $rs_type = $dbcon->query($query);
    $str .= '<option value="" >--Choose PO NO--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['purchaseorder_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['purchaseorder_id'] . '">' . $row['purchaseorder_no'] . '</option>';
    }
    return $str;
}

function get_item_grn($dbcon, $id)
{
    $str = '';
    $query = "select pr.product_id,pr.product_name from tbl_grn_trn as gt
        left join product_mst as pr on pr.product_id = gt.product_id
        where grn_trn_status=0 and pr.product_id!=0 group by product_id";
    $rs_type = $dbcon->query($query);
    $str .= '<option value="" >--Choose Product--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['product_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['product_id'] . '">' . $row['product_name'] . '</option>';
    }
    return $str;
}

function get_vender_grn($dbcon, $id)
{
    $str = '';
    $query = "select led.l_id,led.l_name from tbl_grn_trn as gt 
        left join tbl_grn as gr on gr.grn_id=gt.grn_id
        left join tbl_ledger as led on led.l_id=gr.vender_id
        where grn_trn_status=0 and led.l_id!=0 group by gr.vender_id";
    $rs_type = $dbcon->query($query);
    $str .= '<option value="" >--Choose Vendor--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['l_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['l_id'] . '">' . $row['l_name'] . '</option>';
    }
    return $str;
}

function get_vendor($dbcon, $id)
{
    $str = '';
    $query = 'select * from tbl_ledger where l_group=37 and l_status=0 order by l_id desc';
    $rs_type = $dbcon->query($query);
    $str .= '<option value="" >--Choose Vendor--</option>';
    $str .= '<option value="new" >--New Vendor--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['l_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['l_id'] . '">' . $row['l_name'] . '</option>';
    }
    return $str;
}
function get_pending_sales_order($dbcon, $branch_id, $id = null)
{
    $where = "";
    if ($branch_id != "") {
        $where = "and po.branch_id=" . $branch_id;
    }
    $str = '';
    $query = "select GROUP_CONCAT(po.sp_id) as sp_id,po.rp_id,(select strn.sales_order_id
        from tbl_request_product as re 
        left join tbl_sales_ordertrn as strn on strn.sales_ordertrn_id = re.sales_order_trn_id  
        where main_request=1 and re.sp_id = po.sp_id group by re.sp_id) as so_trn from tbl_request_product as po
        left join (select IFNULL(sum(req.approve_qty),0) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=po.rp_id

        where po.indent_status=1 and po.company_id=" . $_SESSION['company_id'] . " " . $where . " Group by so_trn";
    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Sales Order No--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $get_so = "select sales_order_no from tbl_sales_order
            where sales_order_id=" . $row['so_trn'];
        $exe = $dbcon->query($get_so);
        $rel = brp_mysqli_fetch_array($exe);

        $sel = '';
        if ($row['sp_id'] == $id) {
            $sel = 'selected="selected"';
        }
        if ($rel['sales_order_no'] != "") {
            $str .= '<option ' . $sel . ' value="' . $row['sp_id'] . '">' . $rel['sales_order_no'] . '</option>';
        }
    }
    return $str;
}
function get_pending_work_order($dbcon, $branch_id, $id = null)
{
    $where = "";
    if ($branch_id != "") {
        $where = "and po.branch_id=" . $branch_id;
    }
    $str = '';
    $query = "SELECT SQL_CALC_FOUND_ROWS po.indent_no, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no as work_ono, used_qty, pmst.product_name, tc.cat_name, po.rp_id, bms.branch_name, po.indent_status, po.branch_id

        FROM tbl_request_product as po 

        left join tbl_set_main_process as spro on spro.sp_id=po.sp_id 
        left join product_mst as pmst on pmst.product_id=po.rp_pid 
        left join tbl_category as tc on pmst.product_category=tc.cat_id 
        left join branch_mst as bms on bms.branch_id=po.branch_id 
        left join unit_mst as unit on unit.unitid=po.purchase_unit 
        left join (select IFNULL(sum(req.approve_qty),0) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=po.rp_id 

        where ( 1 AND po.indent_status in (1) and po.company_id=" . $_SESSION['company_id'] . " " . $where . ") Group by spro.po_req_no ORDER BY po.rp_id desc";

    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Work Order No--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['rp_id'] == $id) {
            $sel = 'selected="selected"';
        }
        if ($row['work_ono'] != "") {
            $str .= '<option ' . $sel . ' value="' . $row['work_ono'] . '">' . $row['work_ono'] . '</option>';
        }
    }
    return $str;
}
function get_salesorderwise_workorder_no($dbcon, $sp_id)
{
    $where = "";
    if ($sp_id != "") {
        $where = "and po.sp_id=" . $sp_id;
    }
    //echo $where;exit;
    $str = '';
    $query = "SELECT SQL_CALC_FOUND_ROWS po.indent_no, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no as work_ono, used_qty, pmst.product_name, tc.cat_name, po.rp_id, bms.branch_name, po.indent_status, po.branch_id

            FROM tbl_request_product as po 

            left join tbl_set_main_process as spro on spro.sp_id=po.sp_id 
            left join product_mst as pmst on pmst.product_id=po.rp_pid 
            left join tbl_category as tc on pmst.product_category=tc.cat_id 
            left join branch_mst as bms on bms.branch_id=po.branch_id 
            left join unit_mst as unit on unit.unitid=po.purchase_unit 
            left join (select IFNULL(sum(req.approve_qty),0) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=po.rp_id 

            where ( 1 AND po.indent_status in (1) and po.company_id=" . $_SESSION['company_id'] . " " . $where . ") Group by spro.po_req_no ORDER BY po.rp_id desc";
    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Work Order No--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        // $sel = '';
        if ($row['rp_id'] != "") {
            $sel = 'selected="selected"';
        }
        if ($row['work_ono'] != "") {
            $str .= '<option ' . $sel . ' value="' . $row['work_ono'] . '">' . $row['work_ono'] . '</option>';
        }
    }
    return $str;
}
function get_workorderwise_indent_no($dbcon, $id)
{
    $where = "";
    if ($id != "") {
        $where = "and spro.po_req_no='$id'";
    }
    $str = '';
    $query = "SELECT SQL_CALC_FOUND_ROWS po.indent_no, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no, used_qty, pmst.product_name, tc.cat_name, po.rp_id, bms.branch_name, po.indent_status, po.branch_id

                FROM tbl_request_product as po 

                left join tbl_set_main_process as spro on spro.sp_id=po.sp_id 
                left join product_mst as pmst on pmst.product_id=po.rp_pid 
                left join tbl_category as tc on pmst.product_category=tc.cat_id 
                left join branch_mst as bms on bms.branch_id=po.branch_id 
                left join unit_mst as unit on unit.unitid=po.purchase_unit 
                left join (select IFNULL(sum(req.approve_qty),0) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=po.rp_id 

                where ( 1 AND po.indent_status in (1) and po.company_id=" . $_SESSION['company_id'] . " " . $where . ") Group by po.rp_id ORDER BY po.rp_id desc";

    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Work Order No--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['rp_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['indent_no'] . '">' . $row['indent_no'] . '</option>';
    }
    return $str;
}
function get_indentnowise_pro($dbcon, $id)
{
    $where = "";
    if ($id != "") {
        $where = "and po.indent_no='$id'";
    }
    $str = '';
    $query = "SELECT SQL_CALC_FOUND_ROWS po.indent_no, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no, used_qty, pmst.product_name, tc.cat_name, po.rp_id, bms.branch_name, po.indent_status, po.branch_id

                    FROM tbl_request_product as po 

                    left join tbl_set_main_process as spro on spro.sp_id=po.sp_id 
                    left join product_mst as pmst on pmst.product_id=po.rp_pid 
                    left join tbl_category as tc on pmst.product_category=tc.cat_id 
                    left join branch_mst as bms on bms.branch_id=po.branch_id 
                    left join unit_mst as unit on unit.unitid=po.purchase_unit 
                    left join (select IFNULL(sum(req.approve_qty),0) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=po.rp_id 

                    where ( 1 AND po.indent_status in (1) and po.company_id=" . $_SESSION['company_id'] . " " . $where . ") Group by po.rp_id ORDER BY po.rp_id desc";

    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Product--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['rp_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['product_name'] . '">' . $row['product_name'] . '</option>';
    }
    return $str;
}
function get_pending_indent_no($dbcon, $branch_id, $id = null)
{
    $where = "";
    if ($branch_id != "") {
        $where = "and po.branch_id=" . $branch_id;
    }
    $str = '';
    $query = "SELECT SQL_CALC_FOUND_ROWS po.indent_no, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no, used_qty, pmst.product_name, tc.cat_name, po.rp_id, bms.branch_name, po.indent_status, po.branch_id

                        FROM tbl_request_product as po 

                        left join tbl_set_main_process as spro on spro.sp_id=po.sp_id 
                        left join product_mst as pmst on pmst.product_id=po.rp_pid 
                        left join tbl_category as tc on pmst.product_category=tc.cat_id 
                        left join branch_mst as bms on bms.branch_id=po.branch_id 
                        left join unit_mst as unit on unit.unitid=po.purchase_unit 
                        left join (select IFNULL(sum(req.approve_qty),0) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=po.rp_id 

                        where ( 1 AND po.indent_status in (1) AND po.status !=2 and po.company_id=" . $_SESSION['company_id'] . " " . $where . ") Group by po.rp_id ORDER BY po.rp_id desc";

    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Indent No--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['rp_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['indent_no'] . '">' . $row['indent_no'] . '</option>';
    }
    return $str;
}
function get_indent_pending_product($dbcon, $branch_id, $id = null)
{
    $where = "";
    if ($branch_id != "") {
        $where = "and po.branch_id=" . $branch_id;
    }
    $str = '';
    $query = "SELECT SQL_CALC_FOUND_ROWS po.indent_no, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no, used_qty, pmst.product_name, tc.cat_name, po.rp_id, bms.branch_name, po.indent_status, po.branch_id

                            FROM tbl_request_product as po 

                            left join tbl_set_main_process as spro on spro.sp_id=po.sp_id 
                            left join product_mst as pmst on pmst.product_id=po.rp_pid 
                            left join tbl_category as tc on pmst.product_category=tc.cat_id 
                            left join branch_mst as bms on bms.branch_id=po.branch_id 
                            left join unit_mst as unit on unit.unitid=po.purchase_unit 
                            left join (select IFNULL(sum(req.approve_qty),0) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=po.rp_id 

                            where ( 1 AND po.indent_status in (1) and po.company_id=" . $_SESSION['company_id'] . " " . $where . ") Group by po.rp_id ORDER BY po.rp_id desc";

    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Product Name--</option>';
    while ($row = mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['rp_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['product_name'] . '">' . $row['product_name'] . '</option>';
    }
    return $str;
}
function get_pre_product($dbcon, $pre_id = 0)
{
    if ($pre_id) {
        $query = "select pro.product_name,ven.l_name,mst.* from tbl_pre_trn as mst 
                                    left join product_mst as pro on pro.product_id=mst.product_id
                                    left join tbl_ledger as ven on ven.l_id=mst.vender_id
                                    where mst.pre_trn_status=0 and pre_id=" . $POST['pre_id'];
    } else {
        $query = "select pro.product_name,ven.l_name,mst.* from tbl_pre_trn as mst 
                                    left join product_mst as pro on pro.product_id=mst.product_id
                                    left join tbl_ledger as ven on ven.l_id=mst.vender_id
                                    where mst.pre_trn_status=3 and mst.user_id=" . $_SESSION['user_id'];
    }
    $result = brp_mysqli_query($dbcon, $query);
    $products = brp_mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $products;
}

function get_pricelist_po($dbcon, $vender_id, $product_id)
{
    $where = "";
    if ($product_id != "") {
        $where = "and trn.product_id=" . $product_id;
    }

    $date = date('Y-m-d');
    $qt_qry = "select trn.product_qty,trn.product_rate,po.purchaseorder_date,po.purchaseorder_no,pro.product_name,trn.discount_per from tbl_purchaseordertrn AS trn
                                left join tbl_purchaseorder as po on po.purchaseorder_id = trn.purchaseorder_id
                                left join tbl_ledger as led on led.l_id=po.vender_id
                                left join product_mst as pro on pro.product_id = trn.product_id
                                where po.purchaseorder_date<='$date' and po.vender_id=" . $vender_id . " " . $where . " and trn.purchaseordertrn_status=0 ORDER BY po.purchaseorder_date desc LIMIT 50 ";
    $qt_exe = $dbcon->query($qt_qry);
    //Party PO Details Table View
    $str = '';
    $str .= '<div class="form-group">
                                <table class="display table table-bordered table-striped">
                                <thead>
                                <tr>
                                <td><strong>PO NO</strong></td>
                                <td><strong>PO Date</strong></td>';
    if ($product_id == "") {
        $str .= '<td><strong>Product Name</strong></td>';
    }
    $str .= '<td><strong>Product Qty</strong></td>
                                <td><strong>Product Rate</strong></td>
                                <td><strong>Disc %</strong></td>
                                <td><strong>Actual Rate</strong></td>
                                </tr>
                                </thead><tbody>';
    if (mysqli_num_rows($qt_exe) > 0) {
        while ($qt_rel = brp_mysqli_fetch_assoc($qt_exe)) {
            $actual_rate = $qt_rel['product_rate'] - ($qt_rel['discount_per'] / 100 * $qt_rel['product_rate']);
            $disc_per = "";
            if ($qt_rel['discount_per'] != 0) {
                $disc_per = $qt_rel['discount_per'] . "%";
            }

            $str .= '
                                        <tr>
                                        <td>' . $qt_rel['purchaseorder_no'] . '</td>
                                        <td>' . date('d-m-Y', strtotime($qt_rel['purchaseorder_date'])) . '</td>';
            if ($product_id == "") {
                $str .= '<td>' . $qt_rel['product_name'] . '</td>';
            }
            $str .= '<td>' . $qt_rel['product_qty'] . '</td>
                                        <td>' . $qt_rel['product_rate'] . '</td>
                                        <td>' . $disc_per . '</td>
                                        <td>' . $actual_rate . '</td>
                                        </tr>
                                        ';
        }
    } else {
        $str .= '<tr>
                                    <td colspan="7" style="text-align:center">No Data Yet...!!</td>
                                    </tr>';
    }
    $str .= '</tbody></table></div>
                                <hr/>
                                ';
    return $str;
}
function getgodown($dbcon, $cid)
{
    $query = "select * from mst_godown where g_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_cust = $dbcon->query($query);
    echo '<option value="">Choose Godown</option>';
    while ($rel = mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['gd_id'] == $cid) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['gd_id'] . '">' . $rel['gd_name'] . '</option>';
    }
}
/*function get_indent_no($dbcon, $branch_id, $id=null){
    $whr="";
    if($branch_id=="0"){
        $whr = "and branch_id=".$branch_id;
    }

    $str='';
    $query="Select rp_id,indent_no from tbl_request_product where indent_status=3 ".$whr;
    $rs_type=$dbcon->query($query);
    $str ='<option value="" >--Choose Indent No--</option>';
    while($row=mysqli_fetch_assoc($rs_type))
    {	
        $sel = '';
        if($row['rp_id']==$id){
            $sel = 'selected="selected"';
        }
        $str .= '<option '.$sel.' value="'.$row['rp_id'].'">'.$row['indent_no'].'</option>';
    }
    return $str;
}*/
//End Code Maulik Kapatel
/* Added by : Dimple Panchal Start*/
function get_current_financial_year()
{
    $month = date('m');
    $year = date('Y');
    if ($month > 3) {
        $start_year = $year;
        $end_year = $start_year + 1;
    } else {
        $end_year = $year;
        $start_year = $end_year - 1;
    }
    $sdate['start_date'] = date('01-04-' . $start_year);
    $sdate['end_date'] = date('31-03-' . ($end_year));
    return $sdate;
}
function get_financial_year()
{
    //$today = "01-11-2020";
    $date = date('d-m-Y');
    $month = date('m', strtotime($date));
    $year = date('Y', strtotime($date));
    if ($month > 3) {
        $start_year = $year;
        $end_year = $start_year + 1;
    } else {
        $end_year = $year;
        $start_year = $end_year - 1;
    }
    $sdate['start_date'] = date('01-04-' . $start_year);
    $sdate['end_date'] = date('31-03-' . ($end_year));
    return $sdate;
}
function get_ledger_by_group($dbcon, $group_id)
{
    $sub_groups = array_column($dbcon->query("SELECT g_id FROM `tbl_group` WHERE g_status = 0 And `g_pid`= " . $group_id)->fetch_all(MYSQLI_ASSOC), 'g_id');

    $legders = '';
    if ($sub_groups) {
        foreach ($sub_groups as $subgroup) {
            $sub_ledger_qry = "SELECT group_concat(l_id) as sub_ledger FROM `tbl_ledger` WHERE `l_group` IN (" . $subgroup . ")";
            $sub_ledger = $dbcon->query($sub_ledger_qry)->fetch_object()->sub_ledger;

            $legders .= $sub_ledger;
            get_ledger_by_group($dbcon, $subgroup);
        }
    }
    return $legders;
}
function get_sub_group($dbcon, $group_id, $sub_group_array = array())
{
    if ($group_id) {
        if (!$sub_group_array) {
            array_push($sub_group_array, $group_id);
        }
        $groups = array_column($dbcon->query("SELECT g_id FROM `tbl_group` WHERE g_status = 0 And `g_pid` IN (" . $group_id . ")")->fetch_all(MYSQLI_ASSOC), 'g_id');

        if ($groups) {
            foreach ($groups as $groupid) {
                array_push($sub_group_array, $groupid);
                $subgroup = array_column($dbcon->query("SELECT g_id FROM `tbl_group` WHERE g_status = 0 And `g_pid`= " . $groupid)->fetch_all(MYSQLI_ASSOC), 'g_id');

                if ($subgroup) {
                    foreach ($subgroup as $sub_group) {
                        array_push($sub_group_array, $sub_group);
                        get_sub_group($dbcon, $sub_group, $sub_group_array);
                    }
                }
            }
        }
        //p($sub_group_array);
        //$sub_group_ids = implode(',', $sub_group_array);
        return $sub_group_array;
    }
}
function load_ledger_detail($dbcon, $type, $ref_id, $ledger_id = 0)
{
    if ($type == "tbl_receipt") {
        $qry1 = "select receipt_id,receipt_no,cert.cust_id,payment_mode_id ,l_name as ledger_name,payment_type from tbl_receipt as cert 
                left join tbl_ledger as ledger on ledger.l_id = cert.cust_id
                where receipt_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        //$ret= $re;

    } else if ($type == "tbl_receipt_payment_trn") {
        $qry1 = "select rt.receipt_payment_trn_id,r.receipt_no,rt.ledger_id,r.payment_mode_id,l.l_name as ledger_name,r.payment_type from tbl_receipt_payment_trn as rt left join tbl_receipt as r on r.receipt_id = rt.receipt_id left join tbl_ledger as l on l.l_id = rt.ledger_id where rt.receipt_payment_trn_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        //$ret= $re;

    } else if ($type == "tbl_journal_trn") {
        $journalId = $dbcon->query("SELECT journal_id FROM tbl_journal_trn where journal_trn_id=" . $ref_id)->fetch_object()->journal_id;
        $qry1 = "select jou.journal_id,journal_no,l_name as ledger_name from tbl_journal_trn as cert 
                left join tbl_journal as jou on jou.journal_id=cert.journal_id
                left join tbl_ledger as ledger on ledger.l_id = cert.ledger_id
                WHERE cert.journal_id = " . $journalId . " and ledger_id != " . $ledger_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        //p($re, TRUE);
        //$ret=$re['journal_no'];

    } else if ($type == "tbl_invoice") {
        $qry1 = "select invoice_id,invoice_no,l_name as ledger_name from tbl_invoice as cert
                left join tbl_ledger as ledger on ledger.l_id = cert.cust_id
                where invoice_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        //$ret=$re;

    } else if ($type == "tbl_pono") {
        $qry1 = "select po_id,po_no,l_name as ledger_name from tbl_pono as cert 
                left join tbl_ledger as ledger on ledger.l_id = cert.vender_id
                where po_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        //$ret=$re['po_no'];

    } else if ($type == "tbl_contra_trn") {
        $contra_id = $dbcon->query("SELECT contra_id FROM tbl_contra_trn where contra_trn_id=" . $ref_id)->fetch_object()->contra_id;

        $qry1 = "select jou.contra_id,contra_no,l_name as ledger_name from tbl_contra_trn as cert 
                left join tbl_contra as jou on jou.contra_id=cert.contra_id
                left join tbl_ledger as ledger on ledger.l_id = cert.ledger_id
                WHERE cert.contra_id = " . $contra_id . " and ledger_id != " . $ledger_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        //$ret=$re['contra_no'];

    } else if ($type == 'tbl_bill_sundry_transaction') {

        // $qry1 = "select gb.table_id,bs.sundry_voucher_table,bs.sundry_voucher_type,bs.sundry_voucher_id from tbl_general_book as gb inner join tbl_bill_sundry_transaction as bs on gb.table_id=bs.sundry_id where gb.genral_book_status=0 and gb.company_id='$_SESSION[company_id]'";
        // $ro=$dbcon->query($qry1);
        // $re=brp_mysqli_fetch_assoc($ro);
        $qry2 = "SELECT * FROM `tbl_bill_sundry_transaction` WHERE `sundry_id`=" . $ref_id;
        $ro1 = $dbcon->query($qry2);
        $re1 = brp_mysqli_fetch_assoc($ro1);

        if ($re1['sundry_voucher_table'] == "tbl_invoice") {
            $qry1 = "select * from tbl_invoice as cert 
                    where invoice_id=" . $re1['sundry_voucher_id'];
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
            $re['table_name'] = 'tbl_invoice';
        } else if ($re1['sundry_voucher_table'] == "tbl_pono") {
            $qry1 = "select * from tbl_pono as cert 
                    where po_id=" . $re1['sundry_voucher_id'];
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
            $re['table_name'] = 'tbl_pono';
        } else if ($re1['sundry_voucher_table'] == "tbl_debitnote") {
            $qry1 = "SELECT * FROM `tbl_debitnote` 
                    where debitnote_id=" . $re1['sundry_voucher_id'];
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
            $re['table_name'] = 'tbl_debitnote';
        } else if ($re1['sundry_voucher_table'] == "tbl_sale_return") {
            $qry1 = "SELECT * FROM `tbl_sale_return` 
                    where sale_return_id=" . $re1['sundry_voucher_id'];
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
            $re['table_name'] = 'tbl_sale_return';
        } else {
            $re = '';
        }
    }

    return $re;
}
/* Added by : Dimple Panchal End*/
function get_assign_users_inq($dbcon, $sid)
{
    $qry = "select user_id,user_name from users where active=0 and user_type!=1 AND company_id = " . $_SESSION['company_id'] . " order by user_name asc";
    $rs_state = $dbcon->query($qry);
    $str = '';
    $str .= "<option value=''>Choose User</option>";
    $e_id = explode(",", $sid);
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        // $sel = '';
        //if($row['user_id']==$sid)
        if (in_array($row['user_id'], $e_id)) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        $str .= '<option ' . $sel . ' value="' . $row['user_id'] . '">' . $row['user_name'] . '</option>';
    }
    echo $str;
}
function get_tax_formula($dbcon, $id, $where)
{
    $formula_qry = "select * from  formula_mst where formula_status=0 " . $where . " and company_id=" . $_SESSION['company_id'];
    $rs_formula = $dbcon->query($formula_qry);
    echo '<option value="">Choose Formula</option>';
    while ($formula = brp_mysqli_fetch_assoc($rs_formula)) {
        $sel = '';
        if ($formula['formulaid'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $formula['formulaid'] . '">' . $formula['formula_name'] . '</option>';
    }
}
function get_sales_order_data($dbcon, $id)
{
    $str = '';

    //$query="select trn.*,pro.product_id,pro.product_name from tbl_sales_ordertrn as trn inner join tbl_product as pro on pro.product_id=trn.product_id where sales_ordertrn_status=0 and sales_order_id=".$id;
    $query = "select trn.*,pro.product_id,pro.product_name from tbl_sales_ordertrn as trn 
            inner join product_mst as pro on pro.product_id=trn.product_id 
            where sales_ordertrn_status=0 and trn.product_qty>(select IFNULL(sum(product_qty),0) as qty  from tbl_invoice as chall 
            left join tbl_invoicetrn as chtrn on chtrn.invoice_id=chall.invoice_id 
            where invoice_status=0 and chtrn.trancation_status=0 and chall.sales_order_id=trn.sales_order_id and chtrn.product_id=trn.product_id) 
            and sales_order_id=" . $id;
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Sales Order Products</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        /*if($rel['sales_order_id']==$id)
        {$sel ="selected='selected'";}*/
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . '</option>';
    }
    return $str;
    //return $query;

}
function copy_ledger_cust($dbcon, $quotation_id, $cust_id)
{
    if (!empty($quotation_id)) {
        $cust_qry = "select cust.*, addr.c_add_id, addr.c_add_address, addr.c_add_location, addr.c_add_street, addr.c_add_country, addr.c_add_state, addr.c_add_city, addr.c_add_zip, per.c_con_fname,per.c_con_lname,per.c_con_email,per.c_con_mobile, qt_company_name, qt_com_mno, qt_com_gstno, qt_com_addr, qt_add_country, qt_add_state, qt_add_city, quot.branch_id
        from tbl_customer as cust 
        inner join tbl_quotation as quot on quot.cust_id=cust.cust_id
        left join tbl_cust_address as addr on addr.cust_id=cust.cust_id and addr.c_addr_defult=1
        left join tbl_cust_contact as per on per.cust_id=cust.cust_id
        where  quot.quotation_id=" . $quotation_id;
    } else {
        $cust_qry = "select cust.*,addr.c_add_id, addr.c_add_address, addr.c_add_location, addr.c_add_street, addr.c_add_country, addr.c_add_state, addr.c_add_city, addr.c_add_zip, per.c_con_fname, per.c_con_lname, per.c_con_email, per.c_con_mobile, cust.branch_id
        from tbl_customer as cust 
        left join tbl_cust_address as addr on addr.cust_id=cust.cust_id and addr.c_addr_defult=1
        left join tbl_cust_contact as per on per.cust_id=cust.cust_id
        where  cust.cust_id=" . $cust_id;
    }


    //echo $cust_qry;
    $cust_rel = brp_mysqli_fetch_array($dbcon->query($cust_qry));

    /* var_dump($cust_rel);*/
    //if($cust_rel['cust_id']){
    $cust_qry1 = "select l_id from tbl_ledger as cust 
    where l_status=0 and cust.cust_id=" . $cust_rel['cust_id'];
    $cust_rel1 = mysqli_fetch_assoc($dbcon->query($cust_qry1));

    /*var_dump($cust_qry1);*/
    if (empty($cust_rel1['l_id'])) {
        if (!empty($cust_rel['cust_name'])) {
            $adde = $cust_rel['c_add_address'];
            $info['l_name'] = $cust_rel['cust_name'];
            $info['ledger_alias'] = $cust_rel['cust_name'];
            $info['l_group'] = 38;
            $info['m_address'] = $adde;
            $info['countryid'] = (!empty($cust_rel['c_add_country'])) ? $cust_rel['c_add_country'] : 101;
            $info['stateid'] = (!empty($cust_rel['c_add_state'])) ? $cust_rel['c_add_state'] : 1;
            $info['cityid'] = (!empty($cust_rel['c_add_city'])) ? $cust_rel['c_add_city'] : 1;
            $info['cust_pincode'] = $cust_rel['c_add_zip'];
            $info['company_name'] = $cust_rel['cust_name'];
            $info['cust_cont_name'] = $cust_rel['cust_name'];
            $info['cust_mobile'] = $cust_rel['cust_mobile'];
            $info['cust_email'] = $cust_rel['cust_email'];
            $info['common_email_id'] = $cust_rel['cust_email'];
            $info['cust_remark'] = $cust_rel['cust_desc'];
            $info['gst_no'] = $cust_rel['cust_gst'];
            $info['m_pan'] = $cust_rel['cust_pan'];
            $info['iec_no'] = $cust_rel['cust_iec'];
            $info['branch_id'] = $cust_rel['branch_id'];
            $info['ledger_code'] = $cust_rel['cust_code'];
            $info['cust_assign_user'] = trim(check_crm_find_in_set_new($dbcon, $cust_rel['cust_owner'], 1), ",");
            $info['cust_owner'] = $cust_rel['cust_owner'];
            $info['cust_gst_reg'] = (!empty($cust_rel['cust_gst']) && $cust_rel['cust_gst'] != 0) ? 0 : 1;

            $info['l_status'] = '0'; //Auto Approval
            $info['l_form'] = 'customer_form';
            $info['cust_id'] = $cust_rel['cust_id'];

            //pathik territory_id add 27-01-2022
            $info['territory_id'] = $cust_rel['t_id'];
            //pathik territory_id add 27-01-2022
            $info['cdate'] = date("Y-m-d H:i:s");
            $info['user_id'] = $_SESSION['user_id'];
            $info['company_id'] = $_SESSION['company_id'];

            $inserid = add_record('tbl_ledger', $info, $dbcon);

            $info_terms['ledger_id'] = $inserid;
            update_record('tbl_customer_term_trn', $info_terms, "customer_terms_trn_status=0  and cust_id=" . $cust_rel['cust_id'], $dbcon);
            /*$info1['cust_contact_person_name'] = $cust_rel['c_con_fname'] . $cust_rel['c_con_lname'];
            $info1['cust_contact_person_no'] = $cust_rel['c_con_mobile'];
            $info1['cust_contact_person_email'] = strtolower($cust_rel['c_con_email']);
            $info1['cust_id'] = $inserid;
            $info1['user_id'] = $_SESSION['user_id'];
            $info1['cust_contact_person_direct_status'] = 1;
            $insercntid = add_record("tbl_cust_contact_person", $info1, $dbcon);*/

            /*Code By Umair: 21/06/2021
            Comment: Get consinee from party consinee table and add into ledger consinee table
            START
            */
            $party_consinee = "select * from tbl_party_consignee as cust 
            where cust.cust_status=0 and cust.cust_ref_id=" . $cust_rel['cust_id'];
            $consinee_exec = $dbcon->query($party_consinee);
            if (brp_mysqli_num_rows($consinee_exec) > 0) {
                while ($datainfo = brp_mysqli_fetch_assoc($consinee_exec)) {
                    $consignee['company_name'] = $datainfo['company_name'];
                    $consignee['cust_name'] = $datainfo['cust_name'];
                    $consignee['cust_mobile'] = $datainfo['cust_mobile'];
                    $consignee['cust_email'] = $datainfo['cust_email'];
                    $consignee['cust_address'] = nl2br($datainfo['cust_address']);
                    $consignee['countryid'] = $datainfo['countryid'];
                    $consignee['stateid'] = $datainfo['stateid'];
                    $consignee['cityid'] = $datainfo['cityid'];
                    $consignee['gst_no'] = $datainfo['gst_no'];
                    $consignee['cust_ref_id'] = $inserid;
                    $consignee['user_id'] = $datainfo['user_id'];
                    $consignee['company_id'] = $datainfo['company_id'];
                    $consignee['branch_id'] = $datainfo['branch_id'];

                    add_record('tbl_custmer_consignee', $consignee, $dbcon);
                }
            }

            $party_contact = "select * from tbl_cust_contact as cust 
            where cust.c_con_status=0 and cust.cust_id=" . $cust_rel['cust_id'];
            $contact_exec = $dbcon->query($party_contact);
            if (brp_mysqli_num_rows($contact_exec) > 0) {
                while ($coninfo = brp_mysqli_fetch_array($contact_exec)) {
                    $conper['cust_contact_person_name'] = $coninfo['c_con_fname'] . ' ' . $coninfo['c_con_lname'];
                    $conper['cust_contact_person_no'] = $coninfo['c_con_mobile'];
                    $conper['isd_id'] = $coninfo['isd_id'];
                    $conper['cust_contact_person_email'] = $coninfo['c_con_email'];
                    $conper['cust_contact_person_designation'] = $coninfo['c_con_job'];
                    $conper['cust_id'] = $inserid;
                    $conper['user_id'] = $_SESSION['user_id'];
                    $conper['cust_contact_person_direct_status'] = 1;
                    $conper['cdate'] = date("Y-m-d H:i:s");

                    add_record('tbl_cust_contact_person', $conper, $dbcon);
                }
            }

            /*$party_attach_doc = "select * from tbl_ledger_attach_doc where led_attach_status=0 and cust_id=".$cust_rel['cust_id'];*/

            $led_attach['ref_type'] = 0;
            $led_attach['l_id'] = $inserid;

            update_record('tbl_ledger_attach_doc', $led_attach, "led_attach_status=0 and ref_type=1 and cust_id=" . $cust_rel['cust_id'], $dbcon);

            /*END*/
        }
    } else {
        $inserid = $cust_rel1['l_id'];
    }
    //$inserid=$quotation_id;
    if ($inserid) {
        if (!empty($quotation_id)) {
            $upd_qt_qry = "update tbl_quotation set l_id=" . $inserid . " where quotation_id=" . $quotation_id;
            $upd_qt_qry_rs = $dbcon->query($upd_qt_qry);
        }

        $upd_qt_qrys = "update tbl_customer set ledger_id=" . $inserid . " where cust_id=" . $cust_rel['cust_id'];
        $upd_qt_qry_rss = $dbcon->query($upd_qt_qrys);
    }
    //}
    return $inserid;
}

function get_tax_on_total($dbcon, $total, $formulaid)
{
    $qry = "SELECT tax.tax_value, tax.tax_name FROM `formula_mst` as formula 
    inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) 
    WHERE formulaid=" . $formulaid . " order by tax_value desc";
    $row = $dbcon->query($qry);
    $i = 1;
    while ($tax = mysqli_fetch_assoc($row)) {
        $tax_name = $tax['tax_name'];
        $tax_amount = ($total * $tax['tax_value']) / 100;
        $total += $tax_amount;
        $i++;
    }

    /*for($j=$i;$j<=3;$j++)
    {
    $info['tax_name'.$i]='';
    $info['tax_amount'.$i]='';
}*/
    $info['tax_name'] = $tax_name;
    $info['tax_value'] = $tax_amount;
    $info['total'] = $total;
    return $info;
}
/* Added by : Dimple Panchal End*/

function quotation_bom_show_print($dbcon, $bom_id, $qty, $num, $call, $space)
{
    $rr = "";
    $query_m = "select * from tbl_bom as bom where bom_status=0 and bom_id=" . $bom_id;
    $result_m = $dbcon->query($query_m);
    $rel_m = brp_mysqli_fetch_assoc($result_m);

    $query1 = "select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,dwg.drawing_number from tbl_bomtrn as bom_trn 
    left join product_mst as pro on pro.product_id=bom_trn.product_id
    left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
    left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
    left join tbl_drawing as dwg on dwg.drawing_id=pro.drawing_id
    where bom_trn_status=0 and bom_id=" . $bom_id;
    $result1 = $dbcon->query($query1);
    $k = 1;
    $new_call = $call + 1;
    for ($x = 1; $x <= $call; $x++) {
        $space = $space . "&nbsp;&nbsp;";
    }
    while ($rel1 = mysqli_fetch_assoc($result1)) {

        $new_num = $num . "." . $k;

        $base_one_qty = $rel1['product_base_qty'] / $rel_m['product_base_qty'];
        $base_qty = $base_one_qty * $qty;
        $conv_stock = convert_stock($dbcon, $base_qty, $rel1['product_id'], "conv_unit");
        $typ = get_product_type_by_id($dbcon, $rel1['product_type']);
        // $lst=get_last_purchase($dbcon,$rel1['product_id']);
        // <!--<td style="border:1px #444 solid;">' . $lst . '</td>-->
        $rr .= '<tr>
        <td style="border-right:1px solid; border-bottom:1px solid;">' . $new_num . '</td>
        <td style="border-right:1px solid; border-bottom:1px solid;">' . $rel1["product_name"] . '</td>
        <td style="border-right:1px solid; border-bottom:1px solid;" >' . $typ . '</td>

        <td style="border-right:1px solid; border-bottom:1px solid;" >
        ' . $base_qty . '  ' . $rel1["base_unit_name"] . '
        </td>
        <td style="border-right:1px solid; border-bottom:1px solid;">' . $rel1["base_unit_name"] . '</td>
        
        <td style="border-right:1px solid; border-bottom:1px solid;" >';
        $query = "select mst.*,p.process_name from tbl_product_process as mst 
        left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 and  mst.product_id=" . $rel1['product_id'] . " order by process_priority";
        $result = $dbcon->query($query);
        $cnt = mysqli_num_rows($result);
        if ($cnt > 0) {
            $rr .= '<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
            <tr>
            <th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
            </tr>';
            while ($rel = mysqli_fetch_assoc($result)) {
                if ($rel['process_type'] == 1) {
                    $process_type = "Inhouse";
                } else {
                    $process_type = "Outside";
                }

                $rr .= '<tr>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel["process_priority"] . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $process_type . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel["process_name"] . '</td>
                </tr>';
            }
            $rr .= '</table>';
        }
        $rr .= '</td>

        </tr>';
        $rr .= quotation_bom_show_print($dbcon, $rel1['p_bom_id'], $base_qty, $new_num, $new_call, $space);
        $k++;
    }
    return $rr;
}
//ankit function start
function p($val, $isexit = true)
{
    // echo '<pre>';
    // print_r($val);
    // echo '</pre>';
    // if ($isexit) {
    // 	die();
    // }

}

function get_all_departments($dbcon, $id, $where = '', $showPrimary = false)
{
    $str = '';
    $query = "SELECT id, department_name FROM hrms_department where status IN ('0','1') AND is_group = 'Yes' " . $where;
    $rs_type = $dbcon->query($query);
    if ($id == '0') {
        $psel = 'selected="selected"';
    }
    $str = '<option value="" >--Choose Department--</option>';
    if ($showPrimary) {
        $str .= '<option value="0" ' . $psel . ' >PRIMARY</option>';
    }
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['id'] . '">' . $row['department_name'] . '</option>';
    }
    return $str;
}

function get_leave_policy($dbcon, $id)
{
    $query = "select id from hrms_leave_policy where user_id = '" . $_SESSION['user_id'] . "' AND company_id = '" . $_SESSION['company_id'] . "' AND status IN ('0','1') ";
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Please Select</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $rel['id'] . '">' . $rel['id'] . '</option>';
    }
    return $str;
}

function get_salary_structure($dbcon, $id)
{
    $query = "select id, salary_structure_name from hrms_salary_structure where user_id = '" . $_SESSION['user_id'] . "' AND company_id = '" . $_SESSION['company_id'] . "' AND status IN ('0','1') ";
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Please Select</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $rel['id'] . '">' . $rel['salary_structure_name'] . '</option>';
    }
    return $str;
}

function get_departments($dbcon, $id, $where = '', $showPrimary = false)
{
    $str = '';
    $query = "SELECT id, department_name FROM hrms_department where status IN ('0','1') " . $where;
    $rs_type = $dbcon->query($query);
    if ($id == '0') {
        $psel = 'selected="selected"';
    }
    $str = '<option value="">Please Select</option>';
    if ($showPrimary) {
        $str .= '<option value="0" ' . $psel . ' >PRIMARY</option>';
    }
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['id'] . '">' . $row['department_name'] . '</option>';
    }
    return $str;
}

function getStatusOptions($id)
{
    $option = '<option value="0" ' . ($id == '0' ? 'selected="selected"' : '') . '>Active</option>';
    $option .= '<option value="1" ' . ($id == '1' ? 'selected="selected"' : '') . '>Inactive</option>';

    return $option;
}

function get_approval_status($dbcon, $id, $where = '')
{
    $str = '';
    $query = "SELECT id, status_name FROM hrms_approval_status_master WHERE status IN ('0','1') " . $where;
    $rs_type = $dbcon->query($query);

    $str = '<option value="">SELECT ATTENDANCE STATUS</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['id'] . '">' . $row['status_name'] . '</option>';
    }
    return $str;
}

function get_approval_status_by_id($dbcon, $id)
{
    $query = "SELECT id, status_name FROM hrms_approval_status_master WHERE status IN ('0','1') AND id = $id";
    // return $query;
    $row = $dbcon->query($query);
    $rel = mysqli_fetch_array($row);

    return ($rel && $rel['status_name']) ? $rel['status_name'] : '-';
}

function get_department_name_by_id($dbcon, $id)
{
    $query = "SELECT id, department_name FROM hrms_department WHERE status IN ('0','1') AND id = '$id'";
    $row = $dbcon->query($query);
    $rel = mysqli_fetch_array($row);

    return ($rel && $rel['department_name']) ? $rel['department_name'] : '-';
}

function get_branch_name_by_id($dbcon, $id)
{
    $query = "SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND branch_id = '$id'";
    $row = $dbcon->query($query);
    $rel = mysqli_fetch_array($row);

    return ($rel && $rel['branch_name']) ? $rel['branch_name'] : '-';
}

function get_zone_name_by_id($dbcon, $id)
{
    $query = "SELECT zone_id, zone_name FROM zone_mst WHERE zone_status IN ('0','1') AND zone_id = '$id'";
    $row = $dbcon->query($query);
    $rel = mysqli_fetch_array($row);

    return ($rel && $rel['zone_name']) ? $rel['zone_name'] : '-';
}

function get_series_by_type($dbcon, $invoice_type, $type_id)
{
    $series_id = '';
    $query = "SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 AND `invoice_type` = '$invoice_type' AND company_id = '" . $_SESSION['company_id'] . "' AND `type_id` = '$type_id' AND financial_year_id = " . $_SESSION['financial_year_id'] . " ORDER BY invoicetype_id ";
    $data = $dbcon->query($query);
    $r = $data->fetch_assoc();
    $series_id = $r['format_value'] . $r['taxinvoice_start'] . $r['end_format_value'];

    return $series_id;
}

function updateSeries($dbcon, $field, $table, $invoice_type)
{
    // Series Number Update Code
    $qry = "SELECT $field FROM $table";
    $query = $dbcon->query($qry);
    $total_records = $query->num_rows;
    $updateInfo['taxinvoice_start'] = $total_records;
    $updateinvoiceid = update_record('tbl_invoicetype', $updateInfo, "invoice_type = '$invoice_type'", $dbcon);
}

function get_shift_type_by_id($dbcon, $id)
{
    $query = "SELECT id, shift_type_name FROM hrms_shift_type WHERE status = '0' AND id = '$id'";
    $row = $dbcon->query($query);
    $rel = mysqli_fetch_array($row);

    return ($rel && $rel['shift_type_name']) ? $rel['shift_type_name'] : '-';
}

function getUserFromEmployee($dbcon, $id)
{
    $query = "select * from users where employee_id=$id";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    return $rel;
}
function getEmailTemplate($dbcon, $emailType)
{
    $query = "select * from hrms_email_template where email_template_name='$emailType'";
    $rs_email = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_email);
    return $rel;
}
function getleaveType($dbcon, $leaveType)
{
    $query = "select * from hrms_leave_type where id='$leaveType'";
    $rs_leave_type = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_leave_type);
    return $rel;
}
function getEmployeeSync($dbcon)
{
    $query = "SELECT l.*, u.user_mail, u.user_key, u.user_phone, u.user_type FROM `tbl_ledger` as l JOIN users as u ON u.employee_id = l.l_id WHERE l.`l_group` = '58'";
    $rs_type = $dbcon->query($query);
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $series_id = get_series_by_type($dbcon, 'EMPLOYEE', '16');
        $birth_date = '1990-10-30';
        $joining_date = '2015-10-30';

        // Insert Code HRMS Employee Table
        $info['user_id'] = $row['user_id'];
        $info['company_id'] = $row['company_id'];
        $info['series_id'] = $series_id;
        $info['employee_name'] = $row['l_name'];
        $info['birth_date'] = date('Y-m-d', strtotime($birth_date));
        $info['joining_date'] = date('Y-m-d', strtotime($joining_date));
        $info['gender'] = 'MALE';
        $info['country_id'] = $row['countryid'];
        $info['state_id'] = $row['stateid'];
        $info['city_id'] = $row['cityid'];
        $info['cust_pincode'] = $row['cust_pincode'];
        $info['m_pan'] = $row['m_pan'];
        $info['emp_email'] = strtolower($row['user_mail']);
        $info['emp_password'] = $row['user_key'];
        $info['emp_mobile'] = $row['user_phone'];
        $info['emp_zone_id'] = $row['emp_zone_id'];
        $info['emp_branch_id'] = $row['branch_id_employee'];
        $info['emp_user_type'] = $row['user_type'];
        $info['alloc_state_id'] = implode(",", $row['alloc_stateid']);
        $info['alloc_city_id'] = implode(",", $row['alloc_cityid']);
        $info['report_to_user_type'] = $row['report_to_user_type'];
        $info['report_to_user_id'] = $row['report_to_user_id'];
        $info['open_balance'] = $row['opn_balance'];
        $info['balance_typeid'] = $row['balance_typeid'];

        $insertid = add_record('hrms_employee', $info, $dbcon);

        if ($insertid) {
            // Series Number Update Code
            $query = $dbcon->query("SELECT `id` FROM `hrms_employee`");
            $total_records = $query->num_rows;
            $updateInfo['taxinvoice_start'] = $total_records;
            $updateinvoiceid = update_record('tbl_invoicetype', $updateInfo, "invoice_type = 'EMPLOYEE'", $dbcon);

            //update to ledger
            $updateInfo1['employee_id'] = $insertid;
            $updateinvoiceid = update_record('tbl_ledger', $updateInfo1, "l_id = " . $row['l_id'], $dbcon);
        }
    }
    return 1;
}
// ankit function end
//nikunj function start
function getqcrejectedqty($dbcon, $product_id, $purchase_order_trn_id)
{
    $query = "SELECT sum(tqr.qc_rejected) as rejected
    FROM tbl_grn_trn as tgt
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
    left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
    left JOIN tbl_qc as tq ON tq.grn_trn_id=tgt.grn_trn_id 
    left JOIN tbl_qc_trn as tqr ON tqr.qc_id=tq.qc_id 
    where tgt.product_id=" . $product_id . " and tgt.purchaseordertrn_id=" . $purchase_order_trn_id;
    $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));
    if (isset($result1['rejected'])) {
        return $result1['rejected'];
    } else {
        return '-';
    }
}

function getqcaccpetedqty($dbcon, $product_id, $purchase_order_trn_id)
{
    $query = "SELECT sum(tqr.qc_accepted) as accepted
    FROM tbl_grn_trn as tgt
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
    left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
    left JOIN tbl_qc as tq ON tq.grn_trn_id=tgt.grn_trn_id 
    left JOIN tbl_qc_trn as tqr ON tqr.qc_id=tq.qc_id 
    where tgt.product_id=" . $product_id . " and tgt.purchaseordertrn_id=" . $purchase_order_trn_id;
    $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));
    if (isset($result1['accepted'])) {
        return $result1['accepted'];
    } else {
        return '-';
    }
}
function getgrnno($dbcon, $product_id, $purchase_order_trn_id)
{
    $query = "SELECT tg.grn_no
    FROM tbl_grn_trn as tgt
    left JOIN tbl_grn as tg ON tg.grn_id=tgt.grn_id 
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
    where tpt.purchaseordertrn_status!=2 and tgt.product_id=" . $product_id . " and tgt.purchaseordertrn_id=" . $purchase_order_trn_id;
    $result1 = mysqli_fetch_assoc($dbcon->query($query));

    if (isset($result1['grn_no'])) {
        return $result1['grn_no'];
    } else {
        return '-';
    }
}
function dateDiffInDays1($date1, $date2)
{
    if ($date1 == '1970/01/01' || $date1 == '') {
        $date1 = date("Y-m-d");
    }
    if ($date2 == '1970/01/01' || $date2 == '') {
        $date2 = date("Y-m-d");
    }

    $date1 = str_replace('/', '-', $date1);
    $date1 = date('Y-m-d', strtotime($date1));

    $date2 = str_replace('/', '-', $date2);
    $date2 = date('Y-m-d', strtotime($date2));

    $date1 = date_create($date1);
    $date2 = date_create($date2);
    $diff = date_diff($date1, $date2);
    //var_dump($diff);
    $delaydats = $diff->format("%R%a");
    if ($delaydats >= 0) {
        return 0;
    } else {
        return str_replace('-', '', $delaydats);
    }
}

function getreceivedqty($dbcon, $product_id, $purchase_order_trn_id)
{
    $query = "SELECT sum(tgt.product_qty) as product_qty, sum(tgt.product_conv_qty) as cqty,unit.unit_name, cunit.unit_name as conv_unit,tpt.unit_id,tpt.conv_unit_id
    FROM tbl_grn_trn as tgt
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
    left join unit_mst as unit on unit.unitid = tpt.unit_id
    left join unit_mst as cunit on cunit.unitid = tpt.conv_unit_id
    where tpt.purchaseordertrn_status!=2 and tgt.product_id=" . $product_id . " and tgt.purchaseordertrn_id=" . $purchase_order_trn_id;
    $result1 = brp_mysqli_fetch_array($dbcon->query($query));

    if ($result1['unit_id'] != $result1['conv_unit_id']) {
        $result1['product_qty'] = number_format($result1['product_qty'], 4, ".", "") . ' ' . $result1['unit_name'] . '<br>' . number_format($result1['cqty'], 4, ".", "") . ' ' . $result1['conv_unit'];
    } else {
        $result1['product_qty'] = number_format($result1['product_qty'], 4, ".", "") . ' ' . $result1['unit_name'];
    }

    if (isset($result1['product_qty'])) {
        return $result1['product_qty'];
    } else {
        return '-';
    }
}

function getchallanno($dbcon, $product_id, $purchase_order_trn_id, $field)
{
    $query = "SELECT tg." . $field . "
    FROM tbl_grn_trn as tgt
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
    left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
    where tpt.purchaseordertrn_status!=2  and tgt.product_id=" . $product_id . " and tgt.purchaseordertrn_id=" . $purchase_order_trn_id;
    $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));
    if (isset($result1[$field])) {
        return $result1[$field];
    } else {
        return '-';
    }
}

function processoutside($dbcon, $prdctcategory, $prdctid)
{
    $query_date = date('d-m-Y');
    $firstday = date('Y-m-01', strtotime($query_date));
    $lastday = date('Y-m-t', strtotime($query_date));
    $query = "SELECT  IFNULL(SUM(tsmp.p_qty), 0) as processcount
    FROM tbl_allocate_process as tsmp
    left JOIN product_mst as p ON p.product_id=tsmp.p_product_id where tsmp.pr_process_type=2";
    //if($prdctcategory){
    $query .= ' and p.product_category=' . $prdctcategory;
    //}
    if ($prdctid > 0) {
        $query .= ' and tsmp.p_product_id=' . $prdctid;
    }
    $query .= " and tsmp.p_start_time>='" . $firstday . "' and tsmp.p_start_time<='" . $lastday . "'";
    $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));
    if (isset($result1['processcount'])) {
        return $result1['processcount'];
    } else {
        return 0;
    }
}

function processinside($dbcon, $prdctcategory, $prdctid)
{
    $query_date = date('d-m-Y');
    $firstday = date('Y-m-01', strtotime($query_date));
    $lastday = date('Y-m-t', strtotime($query_date));
    $query = "SELECT  IFNULL(SUM(tsmp.p_qty), 0) as processcount
    FROM tbl_allocate_process as tsmp
    left JOIN product_mst as p ON p.product_id=tsmp.p_product_id where tsmp.pr_process_type=1";
    //if($prdctcategory){
    $query .= ' and p.product_category=' . $prdctcategory;
    //}
    if ($prdctid > 0) {
        $query .= ' and tsmp.p_product_id=' . $prdctid;
    }
    $query .= " and tsmp.p_start_time>='" . $firstday . "' and tsmp.p_start_time<='" . $lastday . "'";
    $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));
    if (isset($result1['processcount'])) {
        return $result1['processcount'];
    } else {
        return 0;
    }
}

function getqccount($dbcon, $prdctcategory, $prdctid)
{
    $query_date = date('d-m-Y');
    $firstday = date('Y-m-01', strtotime($query_date));
    $lastday = date('Y-m-t', strtotime($query_date));
    $query = "SELECT  IFNULL(SUM(tsmp.qc_product_qty), 0) as qccount
    FROM tbl_qc_trn as tsmp
    left JOIN product_mst as p ON p.product_id=tsmp.product_id";
    //if($prdctcategory){
    $query .= ' where p.product_category=' . $prdctcategory;
    //}
    if ($prdctid > 0) {
        $query .= ' and tsmp.product_id=' . $prdctid;
    }
    $query .= " and tsmp.cdate>='" . $firstday . "' and tsmp.cdate<='" . $lastday . "'";
    $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));
    if (isset($result1['qccount'])) {
        return $result1['qccount'];
    } else {
        return 0;
    }
}
function getgrncount($dbcon, $prdctcategory, $prdctid)
{
    $query_date = date('d-m-Y');
    $firstday = date('Y-m-01', strtotime($query_date));
    $lastday = date('Y-m-t', strtotime($query_date));
    $query = "SELECT  IFNULL(SUM(tsmp.product_qty), 0) as grncount
    FROM tbl_grn_trn as tsmp
    left JOIN product_mst as p ON p.product_id=tsmp.product_id";
    //if($prdctcategory){
    $query .= ' where p.product_category=' . $prdctcategory;
    //}
    if ($prdctid > 0) {
        $query .= ' and tsmp.product_id=' . $prdctid;
    }
    $query .= " and tsmp.cdate>='" . $firstday . "' and tsmp.cdate<='" . $lastday . "'";
    //  $query.=" group by tsmp.product_id";
    //exit;
    $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));
    if (isset($result1['grncount'])) {
        return $result1['grncount'];
    } else {
        return 0;
    }
}
function getpocount($dbcon, $prdctcategory, $prdctid)
{
    try {
        $query_date = date('d-m-Y');
        $firstday = date('Y-m-01', strtotime($query_date));
        $lastday = date('Y-m-t', strtotime($query_date));

        $query = "SELECT group_concat(tp.purchaseorder_id) as poids
                  FROM tbl_purchaseorder as tp
                  WHERE tp.status = 0";
        $query .= " AND tp.cdate >= '" . $firstday . "' AND tp.cdate <= '" . $lastday . "'";

        $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));

        // If poids is empty or null, return 0 to avoid SQL errors
        if (empty($result1['poids'])) {
            return 0;
        }

        $query = "SELECT IFNULL(SUM(tpt.product_qty), 0) as pocount
                  FROM tbl_purchaseordertrn as tpt
                  LEFT JOIN product_mst as p ON p.product_id = tpt.product_id
                  WHERE tpt.purchaseorder_id IN (" . $result1['poids'] . ")";

        $query .= ' AND p.product_category = ' . $prdctcategory;

        if ($prdctid > 0) {
            $query .= ' AND tpt.product_id = ' . $prdctid;
        }

        $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));

        if (isset($result1['pocount'])) {
            return $result1['pocount'];
        } else {
            return 0;
        }
    } catch (Exception $e) {
        // Log the error or handle it gracefully
        error_log("Error in getpocount(): " . $e->getMessage());
        return 0; // return default safe value
    }
}

function getmrpcount($dbcon, $prdctcategory, $prdctid)
{
    $query_date = date('d-m-Y');
    $firstday = date('Y-m-01', strtotime($query_date));
    $lastday = date('Y-m-t', strtotime($query_date));
    $query = "SELECT  IFNULL(SUM(tsmp.rp_req_qty), 0) as mrpcount
        FROM tbl_set_main_process as tsmp
        left JOIN product_mst as p ON p.product_id=tsmp.product_id";
    //if($prdctcategory){
    $query .= ' where p.product_category=' . $prdctcategory;
    //}
    if ($prdctid > 0) {
        $query .= ' and tsmp.product_id=' . $prdctid;
    }
    $query .= " and (tsmp.cdate>='" . $firstday . "' and tsmp.cdate<='" . $lastday . "') ";
    //  $query.=" group by tsmp.product_id";
    // echo $query;
    //exit;
    $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));

    if (isset($result1['mrpcount'])) {
        return $result1['mrpcount'];
    } else {
        return 0;
    }
}

function getfinishedproducts($dbcon, $prdctid)
{
    $str = '';
    $query = "Select * from product_mst where product_type=0";
    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Product--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['product_id'] == $prdctid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['product_id'] . '">' . $row['product_name'] . '</option>';
    }
    return $str;
}

function getproductsbycategory($dbcon, $category, $id = null)
{

    $str = '';
    $query = "Select * from product_mst where product_category=" . $category;
    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Product--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['product_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['product_id'] . '">' . $row['product_name'] . '</option>';
    }
    return $str;
}

function getwoqtybyprdctid($dbcon, $prdctid)
{
    $query_date = date('d-m-Y');
    $firstday = date('Y-m-01', strtotime($query_date));
    $lastday = date('Y-m-t', strtotime($query_date));
    $query = "SELECT  sum(tsmp.rp_req_qty) as wo
        FROM tbl_set_main_process as tsmp
        left JOIN product_mst as p ON p.product_id=tsmp.product_id where tsmp.product_id=" . $prdctid;
    $query .= " and (tsmp.cdate>='" . $firstday . "' and tsmp.cdate<='" . $lastday . "') or ( tsmp.mdate>='" . $firstday . "' and  tsmp.mdate<='" . $lastday . "' )";
    $query .= " group by tsmp.product_id";
    return $result1 = brp_mysqli_fetch_assoc($dbcon->query($query));
}

function vendorwiseformat3($dbcon, $postarr)
{
    $str = '';
    $set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
    $set_head = brp_mysqli_fetch_assoc($dbcon->query($set));
    $str .= '
        <table  width="100%"   class="display">
        </table>
        <table  class="display table-bordered" id="data_list">
        <tr id="logo" class="logo" style="display:none">
        <td colspan="8" style="text-align:center;">
        <strong>' . $set_head['company_name'] . '</strong>
        </td>
        </tr>
        <tr style="border-bottom:0.5px #000 solid;">
        <td colspan="7">
        <strong>Item Wise Order Summary</strong>
        </td>
        </tr>';

    $str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
        <th width="10%" style="text-align:center;padding:5px 5px;">NO.</th>
        <th width="20%" style="text-align:center">Vendor Name</th>
        <th width="20%" style="text-align:center">City</th>	
        <!--<th width="20%" style="text-align:center">UOM</th>-->	
        <th width="25%" style="text-align:center">PO Qty</th>
        <th width="25%" style="text-align:center"> Amount</th>
        </tr>';

    $query = "SELECT group_concat(distinct(tp.purchaseorder_id)) as poids,cm.city_name,tl.l_name as vendorname,tp.purchaseorder_id,(select IFNULL(sum(tpt.product_amount),0)) as tot,(select IFNULL(sum(tpt.product_qty),0)) as totqty,(select IFNULL(sum(tpt.product_conv_qty),0)) as totcqty,um.unit_name,cum.unit_name as conv_unit,tpt.unit_id,tpt.conv_unit_id
        FROM tbl_purchaseorder as tp 
        left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
        left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id 
        left JOIN city_mst as cm ON cm.cityid=tl.cityid 
        left JOIN product_mst as p ON p.product_id=tpt.product_id
        left JOIN unit_mst as um ON um.unitid=p.product_base_unit
        left JOIN unit_mst as cum on cum.unitid=p.product_conv_unit
        where tpt.purchaseordertrn_status=0 and tp.status=0";

    $s_date = explode(' - ', $postarr['rep_po_date']);
    $startdate = $s_date[0];
    $enddate = $s_date[1];
    $query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";

    if ($postarr['vendor_id'] > 0) {
        $query .= ' and tp.vender_id=' . $postarr['vendor_id'];
    }
    if ($POST['item_id'] > 0) {
        $query .= ' and tpt.product_id=' . $postarr['item_id'];
    }

    $query .= " group by tp.vender_id";
    //exit;
    $result_summary = $dbcon->query($query);

    if (brp_mysqli_num_rows($result_summary) > 0) {
        $j = 1;
        while ($result_summary_arr = brp_mysqli_fetch_assoc($result_summary)) {
            if ($result_summary_arr['unit_id'] != $result_summary_arr['conv_unit_id']) {
                $qty = '<strong style="color:green">' . $result_summary_arr["totqty"] . ' ' . $result_summary_arr['unit_name'] . '</strong><br><strong style="color:orange">' . $result_summary_arr['totcqty'] . ' ' . $result_summary_arr['conv_unit'] . '</strong>';
            } else {
                $qty = '<strong style="color:green">' . $result_summary_arr["totqty"] . ' ' . $result_summary_arr['unit_name'] . '</strong>';
            }

            $str .= '<tr style="  border: 1px dashed #cccccc;">
                <td width="10%" style="text-align:center;">' . $j . '</td>

                <td width="20%"  style="text-align:center">' . $result_summary_arr["vendorname"] . '</td>
                <td width="20%" style="text-align:center">' . $result_summary_arr["city_name"] . '</td>
                <!--<td width="20%" style="text-align:center">' . $result_summary_arr["unit_name"] . '</td>-->
                <td width="25%" style="text-align:center">' . $qty . '</td>
                <td width="25%" style="text-align:center">' . $result_summary_arr["tot"] . '</td>
                </tr>';
            $j++;
        }
    } else {
        $str .= '<tr>
            <td colspan="8" style="text-align:center">NO DATA FOUND  </td>
            </tr>';
    }
    return $str;
}
function get_product_type_company($dbcon, $producttypeid = '', $all = '', $process_require_yes_type = '')
{
    // $str = '';
    $i = true;
    $where_preq = "";
    if ($process_require_yes_type == 1) {
        $where_preq = ' and process_required=1';
    }
    $query = "SELECT product_type_id, product_type_name FROM pro_ms_product_type WHERE product_type_status IN ('0','1')  and company_id=" . $_SESSION['company_id'] . " " . $where_preq;
    $rs_dispatch = $dbcon->query($query);
    if ($all == '') {
        $str = '<option value="">Select Product Type</option>';
    }
    if ($all != '') {
        $str .= '<option value="">--ALL--</option>';
    }

    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        // $sel = '';
        $producttypeids = explode(",", $producttypeid);
        if (in_array($rel['product_type_id'], $producttypeids)) {
            $sel = "selected='selected'";
        }

        $str .= '<option ' . $sel . ' value="' . $rel['product_type_id'] . '">' . $rel['product_type_name'] . '</option>';
    }
    return $str;
}

function vendorwiseformat2($dbcon, $postarr)
{
    $str = '';
    $set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
    $set_head = brp_mysqli_fetch_assoc($dbcon->query($set));
    $str .= '
        <table  width="100%"   class="display table-bordered">
        </table>
        <table  class="display table-bordered" id="data_list">
        <tr id="logo" class="logo" style="display:none">
        <td colspan="8" style="text-align:center;">
        <strong>' . $set_head['company_name'] . '</strong>
        </td>
        </tr>
        <tr style="border-bottom:0.5px #000 solid;">
        <td colspan="7">
        <strong>Item Wise Order Summary</strong>
        </td>
        </tr>';

    $str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
        <th width="10%" style="text-align:center;padding:5px 5px;">NO.</th>

        <th width="20%" style="text-align:center">Vendor Name</th>
        <th width="20%" style="text-align:center">City</th>	
        <th width="25%" style="text-align:center">Net Amount</th>
        <!--<th width="25%" style="text-align:center">Gross Amount</th>-->
        </tr>';

    $query = "SELECT group_concat(distinct(tp.purchaseorder_id)) as poids,cm.city_name,tl.l_name as vendorname,tp.purchaseorder_id,(select IFNULL(sum(tpt.product_amount),0)) as tot,(select IFNULL(sum(tpt.total),0)) as grossamt
        FROM tbl_purchaseorder as tp 
        left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
        left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id 
        left JOIN city_mst as cm ON cm.cityid=tl.cityid 
        where tpt.purchaseordertrn_status=0 and tp.status=0";
    $s_date = explode(' - ', $postarr['rep_po_date']);
    $startdate = $s_date[0];
    $enddate = $s_date[1];
    $query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";

    if ($postarr['vendor_id'] > 0) {
        $query .= ' and tp.vender_id=' . $postarr['vendor_id'];
    }
    if ($POST['item_id'] > 0) {
        $query .= ' and tpt.product_id=' . $postarr['item_id'];
    }
    $query .= " group by tp.vender_id";
    $result_summary = $dbcon->query($query);

    if (brp_mysqli_num_rows($result_summary) > 0) {
        $j = 1;
        while ($result_summary_arr = brp_mysqli_fetch_assoc($result_summary)) {
            $str .= '<tr style="  border: 1px dashed #cccccc;">
                <td width="10%" style="text-align:center">' . $j . '</td>

                <td width="20%"  style="text-align:center">' . $result_summary_arr["vendorname"] . '</td>
                <td width="20%" style="text-align:center">' . $result_summary_arr["city_name"] . '</td>
                <td width="25%" style="text-align:center">' . $result_summary_arr["tot"] . '</td>
                <!--<td width="25%" style="text-align:center">' . $result_summary_arr["grossamt"] . '</td>-->
                </tr>';
            $j++;
        }
    } else {
        $str .= '<tr>
            <td colspan="8" style="text-align:center">NO DATA FOUND  </td>
            </tr>';
    }
    return $str;
}

function itemwiseformat2($dbcon, $postarr)
{
    $str = '';
    $set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
    $set_head = brp_mysqli_fetch_assoc($dbcon->query($set));
    $str .= '
        <table  width="100%"   class="display">
        </table>
        <table  class="display table-bordered" id="data_list">
        <tr id="logo" class="logo" style="display:none">
        <td colspan="8" style="text-align:center;">
        <strong>' . $set_head['company_name'] . '</strong>
        </td>
        </tr>
        <tr style="border-bottom:0.5px #000 solid;">
        <td colspan="7">
        <strong>Item Wise Order Summary</strong>
        </td>
        </tr>';

    $query = "SELECT sum(tpt.product_qty) as totalqty,sum(tpt.product_conv_qty) as totalcqty,um.unit_name,cum.unit_name as conv_unit,p.product_name,p.product_icode,sum(tpt.product_amount) as totalamt,tpt.unit_id,tpt.conv_unit_id FROM tbl_purchaseordertrn as tpt 
        left JOIN unit_mst as um ON um.unitid=tpt.unit_id
        left JOIN unit_mst as cum ON cum.unitid=tpt.conv_unit_id
        left JOIN product_mst as p ON p.product_id=tpt.product_id
        left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tp.status=0 and tpt.purchaseordertrn_status=0";

    $s_date = explode(' - ', $postarr['rep_po_date']);
    $startdate = $s_date[0];
    $enddate = $s_date[1];
    $query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
    if ($postarr['vendor_id'] > 0) {
        $query .= ' and tp.vender_id=' . $postarr['vendor_id'];
    }
    if ($postarr['item_id'] > 0) {
        $query .= ' and tpt.product_id=' . $postarr['item_id'];
    }
    $query .= " group by tpt.product_id";
    $result_summary = $dbcon->query($query);

    if (brp_mysqli_num_rows($result_summary) > 0) {
        $str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
            <th width="5%" style="text-align:center;padding:10px 5px;">NO.</th>
            <th width="15%" style="text-align:center">Item Code</th>
            <th width="15%" style="text-align:center">Item Description</th>
            <!--<th width="20%" style="text-align:center">UOM</th>-->
            <th width="15%" style="text-align:center">PO Qty</th>
            <th width="15%" style="text-align:center">Amount</th>
            </tr>';
        $total = 0;
        $j = 1;
        while ($result_summary_arr = brp_mysqli_fetch_assoc($result_summary)) {
            if ($result_summary_arr['conv_unit_id'] != $result_summary_arr['unit_id']) {
                $qty = '<strong style="color:green">' . $result_summary_arr["totalqty"] . ' ' . $result_summary_arr['unit_name'] . '</strong><br> <strong style="color:orange">' . $result_summary_arr['totalcqty'] . ' ' . $result_summary_arr['conv_unit'] . '</strong>';
            } else {
                $qty = '<strong style="color:green">' . $result_summary_arr["totalqty"] . ' ' . $result_summary_arr['unit_name'] . '</strong>';
            }
            $str .= '<tr>
                <td style="text-align:center">' . $j . '</td>
                <td style="text-align:center">' . $result_summary_arr["product_icode"] . '</td>
                <td style="text-align:center">' . $result_summary_arr["product_name"] . '</td>
                <!--<td style="text-align:center">' . $result_summary_arr["unit_name"] . '</td>-->
                <td style="text-align:center">' . $qty . '</td>
                <td style="text-align:center">' . $result_summary_arr["totalamt"] . '</td>
                </tr>';
            $j++;
        }
    } else {
        $str .= '<tr>
            <td colspan="8" style="text-align:center">NO DATA FOUND  </td>
            </tr>';
    }
    $str .= '</table>';
    return $str;
}

function getbilldata($dbcon, $purchase_order_trn_id, $productid)
{
    $query = "SELECT tpot.total as billtotal,tg.challan_no,tpot.product_qty as billqty, tpot.product_conv_qty as billcqty,tpo.po_no as billno,tpo.po_date as billdate,tp.purchaseorder_no, tpot.unit_id as billu, tpot.conv_unit_id as billcu, unit.unit_name, cunit.unit_name as conv_unit, tpt.unit_id, tpt.product_id, tpt.product_rate, tpt.product_disc, tpt.product_qty, tpt.used_qty, tp.purchaseorder_due_date, (select sum(tbl_grn_trn.product_qty) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending
        FROM tbl_pono as tpo
        left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
        left JOIN tbl_grn as tg ON tpot.grn_id=tg.grn_id
        left JOIN tbl_grn_trn as tgt ON  tgt.grn_id=tg.grn_id 
        left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id
        left JOIN unit_mst as unit ON unit.unitid = tpot.unit_id
        left JOIN unit_mst as cunit ON cunit.unitid = tpot.conv_unit_id
        left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tpo.status!='2' and tpot.grn_trn_id in (" . $purchase_order_trn_id . ") AND tpot.product_id=" . $productid . " group by tpot.potrancation_id";
    return $result1 = $dbcon->query($query);
}

function getgrndata($dbcon, $product_id, $purchase_order_trn_id)
{
    $query = "SELECT tg.grn_no,tgt.unit_id, tgt.product_conv_unit, um.unit_name, umc.unit_name as conv_unit, qum.unit_name as qc_unit,tg.grn_date,tgt.product_qty,tqr.qc_product_qty,tgt.product_conv_qty, tqr.qc_accepted,tqr.qc_rejected,tq.qc_date,tg.challan_no
            FROM tbl_grn_trn as tgt
            left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id and tgt.product_id=tpt.product_id
            left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
            left JOIN tbl_qc as tq ON tq.grn_trn_id=tgt.grn_trn_id 
            left JOIN tbl_qc_trn as tqr ON tqr.qc_id=tq.qc_id 
            left JOIN unit_mst as um on um.unitid = tgt.unit_id
            left JOIN unit_mst as umc on umc.unitid = tgt.product_conv_unit
            left JOIN unit_mst as qum on qum.unitid = tqr.qc_unit_id
            where tgt.product_id=" . $product_id . " and tgt.purchaseordertrn_id=" . $purchase_order_trn_id . " and tgt.grn_trn_status=0";
    return $result1 = $dbcon->query($query);
}

function getbillno($dbcon, $product_id, $purchase_order_id)
{
    $query = "SELECT tpo.po_no as billno,tpo.po_date as billdate
            FROM tbl_purchaseordertrn as tpt
            left JOIN tbl_grn_trn as tgt ON tgt.purchaseordertrn_id=tpt.purchaseordertrn_id and tgt.product_id=tpt.product_id
            left JOIN tbl_grn as tg ON tgt.grn_id=tg.grn_id 
            left JOIN tbl_potrancation as tpot ON tpot.grn_id=tg.grn_id
            left JOIN tbl_pono as tpo ON tpo.po_id=tpot.po_id  where tpt.product_id=" . $product_id . " and tpt.purchaseorder_id=" . $purchase_order_id . "  and tpo.po_no!='' group by tpt.purchaseordertrn_id";
    $result1 = $dbcon->query($query);
    //print_r($result1);

}

function getallworkorder($dbcon)
{
    $str = '';
    $query = "Select * from tbl_set_main_process where sp_status != 2 and finish_status = 0";
    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Work Order--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        $str .= '<option ' . $row['sp_id'] . ' value="' . $row['sp_id'] . '">' . $row['po_req_no'] . '</option>';
    }
    return $str;
}

function getallsalesorder($dbcon)
{
    $str = '';
    $query = "Select * from tbl_sales_order where sales_order_status!=2";
    $rs_type = $dbcon->query($query);
    $str = '<option value="" >--Choose Sales Order--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        $str .= '<option ' . $row['sales_order_id'] . ' value="' . $row['sales_order_id'] . '">' . $row['sales_order_no'] . '</option>';
    }
    return $str;
}

function get_all_category_modify($dbcon, $id, $where = '')
{
    $str = '';
    $query = "Select * from tbl_category where cat_status=0 " . $where;
    $rs_type = $dbcon->query($query);
    if ($id == '0') {
        $psel = 'selected="selected"';
    }
    $str = '<option value="" >--Choose Category--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['cat_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['cat_id'] . '">' . $row['cat_name'] . '</option>';
    }
    return $str;
}
function getunitname($dbcon, $uid)
{
    $query = "SELECT  * FROM unit_mst as tp
            where tp.unitid=" . $uid;
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($rs_cust);
    return $rel['unit_name'];
}

function get_alter_unit($dbcon, $pid, $uid)
{
    $query = "SELECT  * FROM product_mst as tp
            where tp.product_id=" . $pid;
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($rs_cust);
    //print_r($rel);
    if ($uid == $rel['product_base_unit']) {
        $unitname = getunitname($dbcon, $rel['product_conv_unit']);
    } else {
        $unitname = getunitname($dbcon, $rel['product_base_unit']);
    }
    return $unitname;
}

function getproducttrn($dbcon, $id)
{
    $query = "SELECT sum(tp.product_qty) as tot,group_concat(tp.purchaseordertrn_id) as trnids FROM tbl_purchaseordertrn as tp
            where tp.purchaseordertrn_status=0 and tp.purchaseorder_id=" . $id;
    $rs_cust = $dbcon->query($query);
    return $rel = brp_mysqli_fetch_assoc($rs_cust);
}

function getpendingqtygrn($dbcon, $ids)
{
    $query = "SELECT sum(tg.product_qty) as pending FROM tbl_grn_trn as tg
            where tg.purchaseordertrn_id in(" . $ids . ")";

    $rs_cust = $dbcon->query($query);
    return $rel = brp_mysqli_fetch_assoc($rs_cust);
}
function check_product_qty_completed($dbcon, $purchaseordertrn_id, $pr_id)
{
    $query = "SELECT sum(tp.product_qty) as pendingqty,tp.purchaseordertrn_id,tp.product_id,tp.purchaseorder_id FROM tbl_grn_trn as tp where tp.purchaseordertrn_id=" . $purchaseordertrn_id . " and tp.product_id=" . $pr_id;
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($rs_cust);
    return $rel['pendingqty'];
}

function getAllitemsbyvendorid($dbcon, $venderid)
{
    $pending_po = array();
    if ($venderid) {
        $where = "and tp.vender_id=" . $venderid;
    }
    $query = "SELECT p.product_name,tpt.product_id,tp.purchaseorder_no,tpt.product_qty,tpt.used_qty,tp.purchaseorder_id
                FROM tbl_purchaseorder as tp
                left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
                left JOIN product_mst as p ON p.product_id=tpt.product_id
                where tpt.purchaseordertrn_status=0 " . $where . " group by tpt.product_id";
    $rs_cust = $dbcon->query($query);
    $html = '<option value="">--Select Item--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        $html .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . '</option>';
    }
    echo $html;
    exit;
}

function getAllitemsbyvendoridjobcard($dbcon, $venderid)
{
    $pending_po = array();
    $query = "SELECT p.product_name,tp.product_id
                FROM tbl_purchasecardtrn as tp
                left JOIN product_mst as p ON p.product_id=tp.product_id
                where tp.vendor_id=" . $venderid;
    $rs_cust = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        echo '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . '</option>';
    }
}

function getAllPendingitems($dbcon, $purchaseorder_id)
{
    $pending_po = array();
    $query = "SELECT tp.product_qty,tp.purchaseordertrn_id,tp.product_id,tp.purchaseorder_id FROM tbl_purchaseordertrn as tp  where tp.purchaseorder_id=" . $purchaseorder_id;
    $rs_cust = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $pn_qty = check_product_qty_completed($dbcon, $rel['purchaseorder_id'], $rel['product_id']);
        if ($rel['product_qty'] <= $pn_qty) {
        } else {

            $pending_po[] = $rel;
        }
    }
    $html = '';
    $sel = '';
    if (count($pending_po) > 0) {

        $html .= '<option value="">--Select Item--</option>';
        foreach ($pending_po as $key => $value) {
            //	$html.='qq';
            $html .= '<option ' . $sel . ' value="' . $value['product_id'] . '">' . get_pro_field($dbcon, $value['product_id'], 'product_name') . '</option>';
        }
    }
    echo $html;
}

function checkpopendorcomp($dbcon, $purchaseordderid)
{
    $query = "select count(purchaseordertrn_id) as cnt from tbl_purchaseordertrn as trn where trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id=" . $purchaseordderid;
    $rs_cust = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($rs_cust);
    if ($row['cnt'] > 0) {
        return 0;
    } else {
        return 1;
    }
    /*$tot_prdct_data=getproducttrn($dbcon,$purchaseordderid);
    $pend_prdct_data=getpendingqtygrn($dbcon,$tot_prdct_data['trnids']);
    if($tot_prdct_data['tot']<=$pend_prdct_data['pending']){
    return 1;
    }else{
    return 0;
    //$pending_po[]=$rel;
}*/
}
function getAllPO($dbcon, $id)
{
    $pending_po = array();
    $query = "SELECT tp.purchaseorder_no,tpt.product_qty,tpt.used_qty,tp.purchaseorder_id
    FROM tbl_purchaseorder as tp
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
    left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
    where tp.vender_id=" . $id . " and tp.po_approval_status=1 and tp.status=0 group by tp.purchaseorder_id ";
    $rs_cust = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $tot_prdct_data = getproducttrn($dbcon, $rel['purchaseorder_id']);
        $pend_prdct_data = getpendingqtygrn($dbcon, $tot_prdct_data['trnids']);

        if ($tot_prdct_data['tot'] <= $pend_prdct_data['pending']) {
            //return 1;

        } else {
            //return 0;
            $pending_po[] = $rel;
        }
    }

    $html = '';
    $sel = '';
    if (count($pending_po) > 0) {
        $html .= '<option value="">--Select PO--</option>';
        foreach ($pending_po as $key => $value) {
            $html .= '<option ' . $sel . ' value="' . $value['purchaseorder_id'] . '">' . $value['purchaseorder_no'] . '</option>';
        }
    }
    echo $html;
}

function getAllPOs($dbcon, $id)
{
    $pending_po = array();
    $query = "SELECT tp.purchaseorder_no,tp.purchaseorder_id
    FROM tbl_purchaseorder as tp
    where tp.vender_id=" . $id . " group by tp.purchaseorder_id ";
    $rs_cust = $dbcon->query($query);
    $html = '';
    $sel = '';
    $html .= '<option value="">--Select PO--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $html .= '<option ' . $sel . ' value="' . $rel['purchaseorder_id'] . '">' . $rel['purchaseorder_no'] . '</option>';
    }
    echo $html;
}

function getAllVendor($dbcon, $id)
{
    $query = "select l_id,l_name from tbl_ledger where l_status=0 and l_form='customer_form'";
    $rs_cust = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        // $sel = '';
        if ($rel['l_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['l_id'] . '">' . $rel['l_name'] . '</option>';
    }
}

function getpurchasebilldata($dbcon, $po_id, $vender_id, $postdata)
{
    $response = [];
    $query = "SELECT tpot.total as billtotal,tg.challan_no,tpot.product_id,tpot.unit_id,tpot.conv_unit_id,tpot.product_qty as billqty,tpot.product_conv_qty as billcqty,tpo.po_no as billno,tpo.po_date as billdate,tp.purchaseorder_no,tpt.unit_id,tpt.product_id,tpot.product_rate,tpot.product_discount,tpt.product_qty,tpt.used_qty,tp.purchaseorder_due_date,tg.grn_no,pm.product_name,bunit.unit_name as bunit_name ,cunit.unit_name as cunit_name,tpot.product_amount
    FROM tbl_pono as tpo
    left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
    inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
    left join unit_mst as bunit on bunit.unitid=tpot.unit_id
    left join unit_mst as cunit on cunit.unitid=tpot.conv_unit_id
    left JOIN tbl_grn as tg ON tpot.grn_id=tg.grn_id
    left JOIN tbl_grn_trn as tgt ON  tgt.grn_id=tg.grn_id 
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id
    left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tpo.status!='2' and tpo.vender_id=" . $vender_id . " AND tpot.po_id=" . $po_id;

    if ($postdata['po_date_type']) {
        if ($postdata['po_date_type'] == 'po') {
            $s_date = explode(' - ', $postdata['rep_po_date']);
            $startdate = $s_date[0];
            $enddate = $s_date[1];
            $query .= " and tpot.cdate>='" . date('Y-m-d', strtotime($startdate)) . "' and tpot.cdate<='" . date('Y-m-d', strtotime($enddate)) . "'";
        } else {
            $s_date = explode(' - ', $postdata['rep_del_date']);
            $startdate = $s_date[0];
            $enddate = $s_date[1];
            $query .= " and tpot.cdate>='" . date('Y-m-d', strtotime($startdate)) . "' and tpot.cdate<='" . date('Y-m-d', strtotime($enddate)) . "'";
        }
    }

    //if(isset($postdata['specific_vendor'])){
    if ($postdata['vendor_id']) {
        $query .= ' and tpo.vender_id=' . $postdata['vendor_id'];
    }
    //}
    //if(isset($postdata['specific_item'])){
    if ($postdata['item_id']) {
        $query .= ' and tpot.product_id=' . $postdata['item_id'];
    }
    //}
    //if(isset($postdata['purchase_type_status'])){
    if ($postdata['purchase_type_id']) {
        $query .= ' and tpo.purchase_bill_type=' . $postdata['purchase_type_id'];
    }
    //}
    $query .= ' group by tpot.potrancation_id';
    $result1 = $dbcon->query($query);
    while ($rel = mysqli_fetch_assoc($result1)) {
        array_push($response, $rel);
    }
    //var_dump($response);
    return $response;
}

function groupsummaryreport($dbcon, $postdata)
{
    $response = [];
    $query = "SELECT sum(tpot.total) as billtotal,sum(tpot.product_qty) as billqty,bunit.unit_name,tc.cat_name,tc.cat_id
    FROM tbl_pono as tpo
    left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
    inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
    left JOIN tbl_category as tc ON tc.cat_id=pm.product_category 
    left join unit_mst as bunit on bunit.unitid=tpot.unit_id
    where tpo.status!='2'";

    $query .= " and tpo.po_date>='" . date('Y-m-d', strtotime($postdata['fromdate'])) . "' and tpo.po_date<='" . date('Y-m-d', strtotime($postdata['todate'])) . "'";
    if ($postdata['pr_cat'] != '') {

        $query .= " and  pm.product_type in (" . implode(',', $postdata['pr_cat']) . ")";
    }
    if ($postdata['pr_type']) {
        $query .= " and pm.product_category in (" . implode(',', $postdata['pr_type']) . ")";
    }
    if ($postdata['item_id']) {
        $query .= ' and tpot.product_id=' . $postdata['item_id'];
    }
    if ($postdata['purchase_type_id']) {
        $query .= ' and tpo.purchase_bill_type=' . $postdata['purchase_type_id'];
    }

    $query .= " group by tc.cat_id";
    // /echo $query;
    //echo  $query.=' group by tpot.potrancation_id';
    $result1 = $dbcon->query($query);
    $str = '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
    <th width="9%" style="text-align:center;padding:10px 5px"> NO </th>
    <th width="9%" style="text-align:center">Item Group </th>
    <th width="12%" style="text-align:center">Qty</th>
    <th width="12%" style="text-align:center">Purchase Amount</th></tr>
    ';
    while ($rel = mysqli_fetch_assoc($result1)) {
        array_push($response, $rel);
    }
    $j = 1;
    if (count($response) > 0) {
        $tot_amt = 0;
        $tot_qty = 0;
        $tot_pend_amt = 0;
        foreach ($response as $key => $re) {
            $tot_amt = $tot_amt + $re["billtotal"];
            $str .= '<tr style="border: 1px dashed #cccccc;">
            <td style="text-align:center">' . $j . '</td>
            <td style="text-align:center">' . $re["cat_name"] . '</td>
            <td style="text-align:center"><strong style="color:green">' . $re["billqty"] . '</strong></td>
            <td style="text-align:center">' . $re["billtotal"] . '</td>
            </tr>';
            $j++;
        }
        $str .= '<tr>
        <td  colspan="3" style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:right;"><strong>Net Amount :  </strong></td>
        <td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;">' . number_format($tot_amt, 2) . '</td>
        </tr>';
    } else {
        $str .= '<tr>
        <td colspan="8" style="text-align:center">NO DATA FOUND  </td>
        </tr>';
    }
    return $str;
}

function getpurchasebilldataitemwiese($dbcon, $productid, $postdata)
{
    $response = [];
    $query = "SELECT tpot.total as billtotal,tg.challan_no,tpot.product_id,tpot.unit_id,tpot.conv_unit_id,tpot.product_qty as billqty,tpot.product_conv_qty as billcqty,tpo.order_no as billno,tpo.order_date as billdate,tp.purchaseorder_no,tpt.unit_id,tpt.product_id,tpot.product_rate,tpot.product_disc,tpt.product_qty,tpt.used_qty,tp.purchaseorder_due_date,tg.grn_no,pm.product_name,bunit.unit_name,cunit.unit_name as cunit_name,tpot.product_amount,tl.l_name
    FROM tbl_pono as tpo
    left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
    left JOIN tbl_ledger as tl ON tl.l_id=tpo.vender_id
    inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
    left join unit_mst as bunit on bunit.unitid=tpot.unit_id
    left join unit_mst as cunit on cunit.unitid=tpot.conv_unit_id
    left JOIN tbl_grn as tg ON tpot.grn_id=tg.grn_id
    left JOIN tbl_grn_trn as tgt ON  tgt.grn_id=tg.grn_id 
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id
    left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tpo.status!='2' and tpot.product_id=" . $productid . "";

    if ($postdata['po_date_type']) {
        if ($postdata['po_date_type'] == 'po') {
            $s_date = explode(' - ', $postdata['rep_po_date']);
            $startdate = $s_date[0];
            $enddate = $s_date[1];
            $query .= " and tpot.cdate>='" . date('Y-m-d', strtotime($startdate)) . "' and tpot.cdate<='" . date('Y-m-d', strtotime($enddate)) . "'";
        } else {
            $s_date = explode(' - ', $postdata['rep_del_date']);
            $startdate = $s_date[0];
            $enddate = $s_date[1];
            $query .= " and tpot.cdate>='" . date('Y-m-d', strtotime($startdate)) . "' and tpot.cdate<='" . date('Y-m-d', strtotime($enddate)) . "'";
        }
    }

    if (isset($postdata['specific_vendor'])) {
        if ($postdata['vendor_id']) {
            $query .= ' and tpo.vender_id=' . $postdata['vendor_id'];
        }
    }
    if (isset($postdata['specific_item'])) {
        if ($postdata['item_id']) {
            $query .= ' and tpot.product_id=' . $postdata['item_id'];
        }
    }
    if (isset($postdata['purchase_type_status'])) {
        if ($postdata['purchase_type_id']) {
            $query .= ' and tpo.purchase_bill_type=' . $postdata['purchase_type_id'];
        }
    }
    $query .= ' group by tpot.potrancation_id';
    $result1 = $dbcon->query($query);
    while ($rel = mysqli_fetch_assoc($result1)) {

        array_push($response, $rel);
    }
    return $response;
}

function getpgorupdetailreport($dbcon, $catid = '', $typeid = '', $postdata = '')
{
    $str = '';
    $response = [];
    $query = "SELECT tpot.total as billtotal,pm.product_type,pm.product_category,tg.challan_no,tpot.product_id,tpot.unit_id,tpot.conv_unit_id,tpot.product_qty as billqty,tpot.product_conv_qty as billcqty,tpo.order_no as billno,tpo.po_date as billdate,tp.purchaseorder_no,tpt.unit_id,tpt.product_id,tpot.product_rate,tpot.product_disc,tpt.product_qty,tpt.used_qty,tp.purchaseorder_due_date,tg.grn_no,pm.product_name,bunit.unit_name,cunit.unit_name as cunit_name,tpot.product_amount,tl.l_name
    FROM tbl_pono as tpo
    left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
    left JOIN tbl_ledger as tl ON tl.l_id=tpo.vender_id
    inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
    left join unit_mst as bunit on bunit.unitid=tpot.unit_id
    left join unit_mst as cunit on cunit.unitid=tpot.conv_unit_id
    left JOIN tbl_grn as tg ON tpot.grn_id=tg.grn_id
    left JOIN tbl_grn_trn as tgt ON  tgt.grn_id=tg.grn_id 
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id
    left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tpo.status!='2'";

    if ($typeid != '') {
        $query .= " and  pm.product_type=" . $typeid;
    }
    if ($catid) {
        $query .= " and pm.product_category=" . $catid;
    }

    if (isset($postdata['product_id']) && $postdata['product_id']) {
        $query .= " and pm.product_id=" . $postdata['product_id'];
    }

    if (isset($postdata['vender_id']) && $postdata['vender_id']) {
        $query .= " and tpo.vender_id=" . $postdata['vender_id'];
    }

    if ($postdata['po_date_type']) {
        if ($postdata['po_date_type'] == 'po') {
            $s_date = explode(' - ', $postdata['rep_po_date']);
            $startdate = $s_date[0];
            $enddate = $s_date[1];
            $query .= " and tpot.cdate>='" . date('Y-m-d', strtotime($postdata['fromdate'])) . "' and tpot.cdate<='" . date('Y-m-d', strtotime($postdata['todate'])) . "'";
        } else {
            $s_date = explode(' - ', $postdata['rep_del_date']);
            $startdate = $s_date[0];
            $enddate = $s_date[1];
            $query .= " and tpot.cdate>='" . date('Y-m-d', strtotime($postdata['fromdate'])) . "' and tpot.cdate<='" . date('Y-m-d', strtotime($postdata['todate'])) . "'";
        }
    }

    $query .= ' group by tpot.potrancation_id';
    $result1 = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($result1)) {
        array_push($response, $rel);
    }
    // echo "<pre>";
    // print_r($response);


    $j = 1;
    if (count($response) > 0) {
        $tot_amt = 0;
        $tot_qty = 0;
        $tot_cqty = 0;
        $tot_pend_amt = 0;
        foreach ($response as $key => $re) {
            $unit = get_alter_unit($dbcon, $re["product_id"], $re["unit_id"]);
            $convert_unit_stock = convert_stock($dbcon, $re["product_qty"], $re["product_id"], $unit);
            $qty = '';
            if ($re['unit_id'] != $re['conv_unit_id']) {
                $qty = '<span style="color:green">' . $re['billqty'] . ' ' . $re['unit_name'] . '</span><br>
                <span style="color:orange">' . $re['billcqty'] . ' ' . $re['cunit_name'] . '</span>';
                $tot_cqty = $tot_cqty + $re["billcqty"];
            } else {
                $qty = '<span style="color:green">' . $re['billqty'] . ' ' . $re['unit_name'] . '</span>';
            }
            //$pend_conv=convert_stock($dbcon,$pen,$re["product_id"],$unit);
            $tot_amt = $tot_amt + $re["product_amount"];
            $tot_qty = $tot_qty + $re["billqty"];

            $str .= '<tr style="border: 1px dashed #cccccc;">
            <td style="text-align:center">' . $j . '</td>
            <td style="text-align:center">' . $re["billno"] . '</td>
            <td style="text-align:center">' . $re["l_name"] . '</td>
            <td style="text-align:center">' . $re["product_name"] . '</td>';

            $str .= '<!--<td style="text-align:center">' . $re["unit_name"] . '<br>' . $unit . '</td>-->
            <td style="text-align:center">' . $qty . '</td>
            <td style="text-align:center">' . $re["product_rate"] . '</td>

            <td style="text-align:center">' . $re["product_amount"] . '</td>

            </tr>';
        }

        $str .= '<tr>
        <td colspan="4" style="text-align:right"><strong>Subtotal : </strong></td>
        <td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;"><span style="color:green"><strong>' . number_format($tot_qty, 2) . '</strong></span><br><span style="color:orange"><strong>' . number_format($tot_cqty, 2) . '</strong></span></td>
        <td  style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:right;"><strong>Net Amount :  </strong></td>
        <td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;">' . number_format($tot_amt, 2) . '</td>
        </tr>';
    } else {
        $str .= '<tr>
        <td colspan="8" style="text-align:center">NO DATA FOUND  </td>
        </tr>';
    }
    return $str;
}

function getpurchasebilldatabillnowise($dbcon, $po_id, $postdata)
{
    $response = [];
    $query = "SELECT tpot.total as billtotal,tg.challan_no,tpot.product_id,tpot.unit_id,tpot.conv_unit_id,tpot.product_qty as billqty,tpot.product_conv_qty as billcqty,tpo.po_no as billno,tpo.po_date as billdate,tp.purchaseorder_no,tpt.unit_id,tpt.product_id,tpot.product_rate,tpot.product_discount,tpt.product_qty,tpt.used_qty,tp.purchaseorder_due_date,tg.grn_no,pm.product_name,bunit.unit_name,cunit.unit_name as cunit_name,tpot.product_amount
    FROM tbl_pono as tpo
    left JOIN tbl_potrancation as tpot ON tpo.po_id=tpot.po_id
    inner JOIN product_mst as pm ON pm.product_id=tpot.product_id 
    left join unit_mst as bunit on bunit.unitid=tpot.unit_id
    left join unit_mst as cunit on cunit.unitid=tpot.conv_unit_id
    left JOIN tbl_grn as tg ON tpot.grn_id=tg.grn_id
    left JOIN tbl_grn_trn as tgt ON  tgt.grn_id=tg.grn_id 
    left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseordertrn_id=tgt.purchaseordertrn_id
    left JOIN tbl_purchaseorder as tp ON tpt.purchaseorder_id=tp.purchaseorder_id where tpo.status!='2'  AND tpo.po_id=" . $po_id;

    if (isset($postdata['specific_vendor'])) {
        if ($postdata['vendor_id']) {
            $query .= ' and tpo.vender_id=' . $postdata['vendor_id'];
        }
    }
    if (isset($postdata['specific_item'])) {
        if ($postdata['item_id']) {
            $query .= ' and tpot.product_id=' . $postdata['item_id'];
        }
    }
    if (isset($postdata['purchase_type_status'])) {
        if ($postdata['purchase_type_id']) {
            $query .= ' and tpo.purchase_bill_type=' . $postdata['purchase_type_id'];
        }
    }

    $query .= ' group by tpot.potrancation_id';
    //print_r($postdata);
    //	echo $query;
    $result1 = $dbcon->query($query);
    while ($rel = mysqli_fetch_assoc($result1)) {
        array_push($response, $rel);
    }
    return $response;
}
//nikunj function end
function add_request_reserve_stock_qc($dbcon, $rp_id, $accept_qty, $unit_id, $product_id, $reject_status, $branch_id, $stock_id, $qc_process_trn_id = 0)
{
    //var_dump($accept_qty);
    //var_dump($unit_id);
    if ($reject_status == "0") {

        add_request_reserve_stock($dbcon, $rp_id, $accept_qty, $unit_id, $branch_id, $stock_id, $qc_process_trn_id);
    } else {


        $set11 = "select rp.*,qtrn.reject_qty,qtrn.reject_used_qty,qtrn.qc_process_trn_id from tbl_request_product as rp
        left join tbl_qc_process_trn as qtrn on qtrn.p_ref_id=rp.rp_id
        where rp.reject_status=0 and qtrn.reject_qty>0 and reject_qty>reject_used_qty and rp.company_id=" . $_SESSION['company_id'] . " and rp.branch_id=" . $branch_id . " and rp.rp_pid=" . $product_id;
        $ser = $dbcon->query($set11);
        while ($set_row = mysqli_fetch_assoc($ser)) {
            $pending_qty = $set_row['reject_qty'] - $set_row['reject_used_qty'];
            if ($pending_qty >= $accept_qty) {

                //$accept_qty
                add_request_reserve_stock_qc($dbcon, $set_row['rp_id'], $accept_qty, $unit_id, $product_id, $set_row['reject_status'], $branch_id, $stock_id, $set_row['qc_process_trn_id']);

                $dbcon->query("update tbl_qc_process_trn set reject_used_qty=reject_used_qty+" . $accept_qty . " where qc_process_trn_id=" . $set_row['qc_process_trn_id']);

                $accept_qty = $accept_qty - $accept_qty;
            } else {
                //$pending_qty
                add_request_reserve_stock_qc($dbcon, $set_row['rp_id'], $pending_qty, $unit_id, $product_id, $set_row['reject_status'], $branch_id, $stock_id, $set_row['qc_process_trn_id']);
                $dbcon->query("update tbl_qc_process_trn set reject_used_qty=reject_used_qty+" . $pending_qty . " where qc_process_trn_id=" . $set_row['qc_process_trn_id']);

                $accept_qty = $accept_qty - $pending_qty;
            }
        }
    }
}
/*function getrequiredproducttype($dbcon,$id,$where)
{
 $str='';
 $q='';
 if($where)
 {
   $q = $where;
 }
 $query="select p.product_id,p.product_name,p.product_type from product_mst as p
 where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) ".$q." order by p.product_name";

 $rs_dispatch=$dbcon->query($query);
 $rel=brp_mysqli_fetch_assoc($rs_dispatch);

 return $rel['product_type'];
}*/
function get_breadcum($dbcon, $bomid, $id1, $id2)
{
    //	echo $id1."gh".$id2;
    $query = "SELECT tb.bom_no,tb.bom_id,tbt.bom_id as pbom,pm.product_name FROM tbl_bom as tb 
    inner JOIN product_mst as pm ON pm.product_id=tb.bom_product 
    left JOIN tbl_bomtrn as tbt ON tbt.p_bom_id=tb.bom_id
    where tb.bom_id in($bomid) order by tb.bom_id asc";
    // echo $query="SELECT tb.bom_no,tb.bom_id,pm.product_name FROM tbl_bom as tb inner JOIN product_mst as pm ON pm.product_id=tb.bom_product where tb.bom_id in($bomid) order by tb.bom_id asc";
    $result = $dbcon->query($query);
    //$rel1=mysqli_fetch_assoc($result);
    $st = array();
    $bom_id_array = array();
    $key = 0;
    while ($rel1 = mysqli_fetch_assoc($result)) {

        $bom_id_array[] = $rel1['bom_id'];
        if (trim($rel1['bom_id']) == '') {
        } else {
            array_push($st, $rel1['bom_id']);
        }
        if (count($st) > 0) {
            $rel1['link'] = base64_encode(implode(',', $st));
        }

        $r[] = $rel1;
        $key++;
    }
    // echo "<pre>";
    // print_r($r);
    // exit;
    $i = 0;
    foreach ($r as $key => $r2) {
        if ($key == 0) {
            $href = "href=" . ROOT . "bom_add/";
        } else {
            $href = "href=" . ROOT . "bom_allocate/" . $r2['bom_id'] . "/" . $r2['pbom'] . "/" . $r[$i - 1]['link'];
        }
        echo '<li><a ' . $href . '>
        ' . $r2["product_name"] . '</a></li>';
        $i++;
    }
}
function bom_show_print($dbcon, $bom_id, $qty, $num, $call, $space, $main_bom_id)
{
    $html = '';
    $query_m = "select * from tbl_bom as bom where bom_status=0 and bom_id=" . $bom_id;
    $result_m = $dbcon->query($query_m);
    $rel_m = mysqli_fetch_assoc($result_m);

    $companyConfiguration = getCompanyConfiguration($dbcon);
    $bom_pro_print = explode(",", $companyConfiguration['bom_pro_print']);

    $query1 = "select bom_trn.*,pro.product_name,pro.product_icode,pro.image_name,pro.product_type,pro.product_alias_name,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,dwg.drawing_number, reqqty, (((IFNULL(base_stock_add,0)+IFNULL(con_stock_add,0))-(IFNULL(base_stock_minus,0)+IFNULL(con_stock_minus,0)))+IFNULL(reqqty,0)) as stock, pro.product_alias_name, pro.product_desc from tbl_bomtrn as bom_trn 
    left join product_mst as pro on pro.product_id=bom_trn.product_id
    left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
    left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
    left join tbl_drawing as dwg on dwg.drawing_id=pro.drawing_id
    left join (select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id
    left join (select sum(qc.base_stock) as base_stock_add,qc.product_id,qc.base_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc4 on qc4.product_id=pro.product_id and qc4.base_unit=pro.product_base_unit
    left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id,qc.base_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=2 and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc1 on qc1.product_id=pro.product_id and qc1.base_unit=pro.product_base_unit
    left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id,qc.convert_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc2 on qc2.product_id=pro.product_id and qc2.convert_unit=pro.product_base_unit
    left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id,qc.convert_unit from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=2 and qc.base_unit!=qc.convert_unit and qc.company_id=" . $_SESSION['company_id'] . " group by qc.product_id) as qc3 on qc3.product_id=pro.product_id and qc3.convert_unit=pro.product_base_unit
    where bom_trn_status=0 and bom_id=" . $bom_id;
    $result1 = $dbcon->query($query1);
    $k = 1;
    $new_call = $call + 1;
    /* for ($x = 1; $x <= $call; $x++) {
        $space=$space."&nbsp;&nbsp;";
    } */
    while ($rel1 = brp_mysqli_fetch_assoc($result1)) {
        $alias_name = '';
        if (in_array('alias', $bom_pro_print)) {
            $alias_name = " -- (" . $rel1['product_alias_name'] . ")";
        }
        $drawing_number = '';
        if (in_array('drawing', $bom_pro_print)) {
            $drawing_number = " -- (" . $rel1['drawing_number'] . ")";
        }
        $item_code = '';
        if (in_array('item', $bom_pro_print)) {
            $item_code = " -- (" . $rel1['product_icode'] . ")";
        }
        if ($rel1['image_name'] != null) {
            //$image_name1 = '<a href="'.ROOT.'view/upload/product_images/'.$rel1["image_name"].'" target="_blank"><img src="'.ROOT.'view/upload/product_images/'.$rel1['image_name'].'" style="width: 60px;height: 50px;"></a>';
            $image_name1 = '<img src="' . ROOT . 'view/upload/product_images/' . $rel1['image_name'] . '" style="width: 60px;height: 50px;">';
        } else {
            $image_name1 = '';
        }
        $new_num = $num . "." . $k;

        $base_one_qty = $rel1['product_base_qty'] / $rel_m['product_base_qty'];
        $base_qty = $base_one_qty * $qty;
        $conv_stock = convert_stock($dbcon, $base_qty, $rel1['product_id'], "conv_unit");
        $base_qty1 = number_format($base_qty, 3, '.', '');
        $conv_stock1 = number_format($conv_stock, 3, '.', '');
        $stock = $rel1['stock'] - $rel1['reqqty'];
        $html .= '<tr>
        <td style="border:0.5px #444 solid;">' . $new_num . '</td>';

        if ($companyConfiguration['bom_extra_no'] == 1) {
            $chk_data = check_extra_bom_no($dbcon, $rel1['product_id'], $main_bom_id, $rel1['p_bom_id'], $rel1['bom_id'], $rel1['bom_version_id']);

            $html .= '<td style="border:1px #444 solid;" >' . $chk_data['ext_no'] . '</td>';
        }

        if ($companyConfiguration['enable_item_image'] == 1) {
            $html .= '<td style="border:0.5px #444 solid;">' . $image_name1 . '</td>';
        }
        $html .= '<td style="border:0.5px #444 solid;">' . $rel1['product_name'] . '' . $item_code . '' . $drawing_number . '' . $alias_name . '<br>';
        if ($companyConfiguration['enable_item_description'] == 1) {
            $html .= $rel1['product_desc'];
        }
        $chkMaterial = $dbcon->query("SELECT bmt.*, mp.material_parameter_name FROM tbl_bom_material_trn as bmt LEFT JOIN tbl_material_parameter as mp ON mp.material_parameter_id = bmt.material_parameter_id WHERE bmt.bom_material_trn_status = 0 AND bmt.bom_trn_id='" . $rel1['bom_trn_id'] . "'");
        while ($getMaterial = brp_mysqli_fetch_assoc($chkMaterial)) {
            $html .= $getMaterial['material_parameter_name'] . ' - ' . $getMaterial['material_parameter_value'] . '<br>';
        }
        if (brp_mysqli_num_rows($chkMaterial) > 0) {
            $html .= 'Calculation: ' . $rel1['product_kg'];
        }
        $html .= '</td>
        <td style="border:1px #444 solid;" >' . get_product_type_by_id($dbcon, $rel1['product_type']) . '</td>';

        /*$html .= '<td style="border:1px #444 solid;" >'.$stock.'</td>';*/

        $html .= '<td style="border:1px #444 solid;" >';
        if ($rel1['product_base_unit'] != $rel1['product_conv_unit']) {
            $html .= $base_qty1 . ' ' . $rel1['base_unit_name'] . '<br/>';
            $html .= $conv_stock1 . ' ' . $rel1['conv_unit_name'];
        } else {
            $html .= $base_qty1 . ' ' . $rel1['base_unit_name'];
        }
        $html .= '</td>
        <td style="border:1px #444 solid;" >';
        /*$query="select mst.*,p.process_name,reso.resource_name from tbl_product_process as mst
        left join tbl_resource as reso on reso.resource_id=mst.resource_id
        left join process_mst as p on p.process_id=mst.process_id where mst.product_id=".$rel1['product_id']." order by process_priority";*/
        $query = "select prb.priority, mst.*,p.process_name,reso.resource_name,mst.process_type from pro_bom_process as prb left join tbl_product_process as mst ON mst.pr_process_id= prb.pr_process_id left join tbl_resource as reso on reso.resource_id=mst.resource_id left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 and  prb.product_id=" . $rel1['product_id'] . " and prb.bom_version_id = " . $rel1['bom_version_id'] . " and prb.process_status = 0 order by prb.priority";
        $result = $dbcon->query($query);
        $cnt = mysqli_num_rows($result);
        if ($cnt > 0) {
            $html .= '<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
            <tr>
            <th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Resource Name</th>
            </tr>';
            while ($rel = mysqli_fetch_assoc($result)) {
                if ($rel['process_type'] == 1) {
                    $process_type = "Inhouse";
                } else {
                    $process_type = "Outside";
                }
                $html .= '<tr>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel['priority'] . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $process_type . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel['process_name'] . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel['resource_name'] . '</td>
                </tr>';
            }
            $html .= '</table>';
        }
        $html .= '</td>
        </tr>';

        $html .= bom_show_print($dbcon, $rel1['p_bom_id'], $base_qty, $new_num, $new_call, $space);
        $k++;
    }
    return $html;
}
function count_so_procuct_req($dbcon)
{
    $branch_where = "";
    if (!empty($_SESSION['branch_id'])) {
        $branch_where = " and so_trn.production_branch_id=" . $_SESSION['branch_id'];
    }

    /* changes by jayesh */

    $query = "SELECT so.sales_order_no, so.sales_order_date, led.l_name, so_trn.product_qty, so_trn.sales_ordertrn_id, mst.product_name, tc.cat_name, so.delivery_date, bran.branch_name, so_trn.product_id, so_trn.work_order_qty, so_trn.unit_id, (IFNULL(product_qty,0)-IFNULL(stock_add,0)) as pending_qty,so.jobwork_type FROM tbl_sales_ordertrn as so_trn left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id left join tbl_ledger as led on led.l_id=so.cust_id left join product_mst as mst on mst.product_id=so_trn.product_id left join tbl_category as tc on mst.product_category=tc.cat_id left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.sales_ordertrn_id from tbl_sales_order_production_trn as qc where qc.sales_order_production_status=0 group by qc.sales_ordertrn_id) as qc on qc.sales_ordertrn_id=so_trn.sales_ordertrn_id left join branch_mst as bran on bran.branch_id=so_trn.branch_id where ( 1 AND so_trn.sales_ordertrn_status=0 and so_trn.bom_status=1 and so_trn.short_close_status=0 and so_trn.invoice_status=0 and so_trn.production_status=0 and mst.product_type!=8 and so_trn.production_branch_id!=0 and so.order_accept_status = 1 and so.approve_status=3 " . $branch_where . " and so_trn.with_out_stock_invoice=0 and so.company_id=" . $_SESSION['company_id'] . ") having pending_qty > 0 ORDER BY so.delivery_date desc";

    /*echo $query="select so.sales_order_no, so.sales_order_date, led.l_name, so_trn.product_qty, so_trn.sales_ordertrn_id, mst.product_name, tc.cat_name, so.delivery_date, bran.branch_name, so_trn.product_id, so_trn.work_order_qty, so_trn.unit_id, (IFNULL(product_qty,0)-IFNULL(stock_add,0)) as pending_qty FROM   tbl_sales_ordertrn as so_trn left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id left join tbl_ledger as led on led.l_id=so.cust_id left join product_mst as mst on mst.product_id=so_trn.product_id left join tbl_category as tc on mst.product_category=tc.cat_id left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.sales_ordertrn_id from tbl_sales_order_production_trn as qc where qc.sales_order_production_status=0 group by qc.sales_ordertrn_id) as qc on qc.sales_ordertrn_id=so_trn.sales_ordertrn_id left join branch_mst as bran on bran.branch_id=so_trn.branch_id where ( 1 AND so_trn.sales_ordertrn_status=0 and so_trn.with_out_stock_invoice=0 and so_trn.production_status=0 and mst.product_type!=8 and so.approve_status=3 ".$branch_where.")	having  pending_qty > 0 ";*/
    /*$query="select count(so_trn.sales_ordertrn_id) as qty,(IFNULL(product_qty,0)-IFNULL(stock_add,0)) as pending_qty from tbl_sales_ordertrn as so_trn
    left join product_mst as pmst on pmst.product_id=so_trn.product_id
    left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id
    left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.sales_ordertrn_id from tbl_sales_order_production_trn as qc

            where qc.sales_order_production_status=0 group by qc.sales_ordertrn_id) as qc on qc.sales_ordertrn_id=so_trn.sales_ordertrn_id
            where sales_ordertrn_status=0 and so_trn.production_status=0 and pmst.product_type!=8 and so.order_accept_status = 1 and so.approve_status=3 ".$branch_where." group by so_trn.sales_ordertrn_id having pending_qty > 0";*/

    $rs = $dbcon->query($query);
    $cnt = brp_mysqli_num_rows($rs);
    $row = brp_mysqli_fetch_array($rs);
    if (empty($row['qty'])) {
        $row['qty'] = 0;
    }
    //return $row['qty'];
    return $cnt;
    //return $query;

}
function count_so_procuct_req_branch($dbcon)
{
    $branch_where = "";
    $companyConfiguration = getCompanyConfiguration($dbcon);
    if ($companyConfiguration['sales_wise_branch_planning_before_bom'] == 0) {
        $branch_planning = " and so_trn.bom_status=1";
    } else {
        $branch_planning = "";
    }

    $query = "SELECT so.sales_order_no, so.sales_order_date, led.l_name, so_trn.product_qty, so_trn.sales_ordertrn_id, mst.product_name, tc.cat_name, so.delivery_date, bran.branch_name, so_trn.product_id, so_trn.work_order_qty, so_trn.unit_id, (IFNULL(product_qty,0)-IFNULL(stock_add,0)) as pending_qty,so.jobwork_type FROM tbl_sales_ordertrn as so_trn 
            left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id 
            left join tbl_ledger as led on led.l_id=so.cust_id 
            left join product_mst as mst on mst.product_id=so_trn.product_id 
            left join tbl_category as tc on mst.product_category=tc.cat_id 
            left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.sales_ordertrn_id from tbl_sales_order_production_trn as qc where qc.sales_order_production_status=0 group by qc.sales_ordertrn_id) as qc on qc.sales_ordertrn_id=so_trn.sales_ordertrn_id 
            left join branch_mst as bran on bran.branch_id=so_trn.branch_id 
            where ( 1 AND so_trn.sales_ordertrn_status=0 " . $branch_planning . " and so_trn.production_branch_id=0 and so_trn.production_status=0 and so_trn.with_out_stock_invoice=0 and mst.product_type!=8 and so_trn.short_close_status=0 and so_trn.invoice_status=0  and so.order_accept_status = 1 and so.approve_status=3 " . $branch_where . " and so.company_id=" . $_SESSION['company_id'] . ") having pending_qty > 0 ORDER BY so.delivery_date desc";

    $rs = $dbcon->query($query);
    $cnt = brp_mysqli_num_rows($rs);
    $row = brp_mysqli_fetch_array($rs);
    if (empty($row['qty'])) {
        $row['qty'] = 0;
    }
    //return $row['qty'];
    return $cnt;
    //return $query;

}
function pending_indent_count($dbcon)
{

    /* 	if(!empty($_SESSION['branch_id'])){
    $branch_where=" and branch_id=".$_SESSION['branch_id'];
    }

    $query="select count(rp_id) as pending_cou from tbl_request_product where indent_status=1".$branch_where;
    ======= */
    $where = "";
    $branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
    $where_db = check_branch('rp', $branch_id);
    $where .= " and rp.company_id=" . $_SESSION['company_id'];

    $query = "select count(rp.rp_id) as pending_cou from tbl_request_product as rp where rp.indent_status=1 and status=0" . $where;

    $result = $dbcon->query($query);
    $row = mysqli_fetch_assoc($result);
    return $row['pending_cou'];
}
function check_multiplication($dbcon, $product_id, $bomid)
{
    $query = "select tb_t.tot_standrad_qty from tbl_bomtrn as mst 
    inner join tbl_bom as tb on tb.bom_id=mst.bom_id
    left join tbl_bom as tb_t on tb_t.bom_id=mst.p_bom_id
    left join product_mst as product on product.product_id=mst.product_id 
    left join unit_mst as u on u.unitid=mst.product_base_unit
    left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
    where mst.bom_trn_status=0 and tb.bom_product=" . $product_id . " order by mst.bom_trn_id asc limit 1";
    //		exit;


    $result = $dbcon->query($query);
    $rel = mysqli_fetch_assoc($result);
    return $rel['tot_standrad_qty'];
}
function get_proudct_multiple_qty($dbcon, $product_id, $bomid)
{
    $query = "select * from tbl_bomtrn where bom_trn_status = 0 AND product_id=" . $product_id . " and bom_id=" . $bomid;
    $result = $dbcon->query($query);
    $row = mysqli_fetch_assoc($result);
    return $row['product_base_qty'];
}
function get_dynamic_bom_no($dbcon)
{
    $query = "select * from tbl_invoicetype where status=0 and type_id=5 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $result = $dbcon->query($query);
    $row = mysqli_fetch_assoc($result);
    // echo $row['invoicetype_id'];
    // exit;
    $rows = array();
    $query1 = "select * from  tbl_invoicetype where invoicetype_id=" . $row['invoicetype_id'];
    $rows = mysqli_fetch_assoc($dbcon->query($query1));
    $id = $rows['taxinvoice_start'];
    $id = $id + 1;

    // echo "<pre>";
    // print_r($rows);
    // exit;
    //$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
    //$end = $start+1;
    if ($rows['invoice_format'] == '2') {
        return $row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
    } else if ($rows['invoice_format'] == '1') {
        return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
    } else if ($rows['invoice_format'] == '3') {
        return $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
    } else {
        return $row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
    }
    //return $row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
    //echo json_encode($row);

}
function generate_sitemap_modify($dbcon, $id, $table, $field, $parent, $prev_param_1, $prev_param_2, $param3, $key)
{
    $str = base64_decode($param3);
    $str_arr = explode(',', $str);
    array_pop($str_arr);
    $or_str = implode(',', $str_arr);
    $encoded_string = base64_encode($or_str);
    $query_bom = "SELECT * FROM " . $table . " WHERE " . $field . " = '" . $id . "'";
    $rsCategoryId = $dbcon->query($query_bom);
    $row_rsCategoryId = mysqli_fetch_assoc($rsCategoryId);

    if ($key == 0) {

        if (isset($_SESSION['bom_edit_id'])) {
            echo '<li>';
            echo '<a href="' . ROOT . PRODUCTION_ROOT . "bom_edit/" . $_SESSION['bom_edit_id'] . '">
            ' . get_pro_field($dbcon, $row_rsCategoryId['bom_product'], 'product_name') . ' </a>
            </li>';
        } else {
            echo '<li>';
            echo '<a href="' . ROOT . PRODUCTION_ROOT . "bom_edit/" . $prev_param_1 . '">
            ' . get_pro_field($dbcon, $row_rsCategoryId['bom_product'], 'product_name') . ' </a>
            </li>';
            //   echo '<li>';
            //   echo '<a href="'.ROOT.'bom_add">'.get_pro_field($dbcon,$row_rsCategoryId['bom_product'],'product_name').' </a>
            // </li>';

        }
    } else {
        echo '<li>';
        echo '<a href="' . ROOT . PRODUCTION_ROOT . "bom_allocate/" . $prev_param_1 . '/' . $prev_param_2 . '/' . $encoded_string . '">
        ' . get_pro_field($dbcon, $row_rsCategoryId['bom_product'], 'product_name') . ' </a>
        </li>';
    }
}

function getrequiredproducttype($dbcon, $id, $where)
{
    $str = '';
    $q = '';
    if ($where) {
        $q = $where;
    }
    $query = "select p.product_id,p.product_name,p.product_type from product_mst as p
    where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) " . $q . " order by p.product_name";

    $rs_dispatch = $dbcon->query($query);
    $rel = mysqli_fetch_assoc($rs_dispatch);

    return $rel['product_type'];
}
function start_qty_avalable_24_12_2020($dbcon, $process_id, $process_type, $product_id, $p_id)
{
    if (!empty($product_id)) {
        $ser = " and ap.p_product_id=" . $product_id;
    }
    if (!empty($p_id)) {
        $p_id_val = " and ap.p_id=" . $p_id;
    }

    $user_type = $_SESSION['user_type'];
    $where_user_wise = '';
    if ($user_type != '2') {
        $where_user_wise = 'and ap.resource_id="' . $_SESSION['resource_id'] . '"';
    }
    $q = $dbcon->query("select ap.*,sum(ap.p_qty) as ap_qty,sum(ap.pen_qty) as apen_qty,p.product_type,p.product_name,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
      left join product_mst as p on p.product_id=ap.p_product_id 
      left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
      left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 
      where ap.process_id=" . $process_id . " " . $ser . " and ap.p_status IN(0,1) " . $p_id_val . " and pr_process_type='$process_type' group by ap.p_product_id");

    $cnt = 1;
    $datacheck = "";
    while ($rel = mysqli_fetch_array($q)) {
        $pid = $rel['p_product_id'];

        $where = '';
        if ($rel['p_status'] == 1) {
            $min_machine111 = 0;
            $pending_qty = 0;

            /*$q1=$dbcon->query("select ap.*,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap

            left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id

            left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id

            where ap.process_id=".$process_id." ".$p_id_val." and ap.p_product_id=".$rel['p_product_id']." and ap.p_status=1  and pr_process_type='$process_type'" );*/
            $q1 = $dbcon->query("select ap.*,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 

              left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 

              left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 

              where ap.process_id=" . $process_id . " " . $p_id_val . " and ap.p_product_id=" . $rel['p_product_id'] . " and pr_process_type='$process_type' ");
            while ($rel_n = mysqli_fetch_array($q1)) {

                $min_machine = $rel_n['start_qty'];
                $min_machine1111 = $rel_n['strtt_qty'] - $rel_n['end_qty'];
                $pending_qty1 = $rel_n['pen_qty'];
                if ($min_machine1111 > $pending_qty1) {
                    $min_machine1111 = $pending_qty1;
                }
                $pending_qty = $pending_qty + $pending_qty1;
                $min_machine111 = $min_machine111 + $min_machine1111;
            }

            //var_dump($min_machine111);

        } else if ($rel['previous_process_id'] == 0) {
            $pending_qty = 0;
            $min_machine111 = 0;
            $machine_make_new = array();
            $q1 = $dbcon->query("select * from tbl_allocate_process as ap 
              where ap.process_id=" . $process_id . " and ap.p_product_id=" . $rel['p_product_id'] . " " . $p_id_val . " and ap.p_status=0  and pr_process_type='$process_type' $where_user_wise");
            while ($rel_n = mysqli_fetch_array($q1)) {
                $min_machine1112 = 0;
                $machine_make = array();
                $q12 = $dbcon->query("select * from tbl_request_product as ap 
                   where status=0 and perent_id=" . $rel_n['p_ref_id']);
                while ($rel_n1 = mysqli_fetch_array($q12)) {

                    $o_qty = convert_stock($dbcon, $rel_n1['req_qty_one'], $rel_n1['rp_id'], "base_unit");

                    /*
                    Code By Umair: 09/12/2020
                    Commnet: Round function is commneted to solve the real value
                    */
                    //$o_qty=round($o_qty,6);
                    $o_qty = $o_qty;

                    $required_qty = $rel_n['p_qty'] * $o_qty;

                    /*
                    Code By Umair: 09/12/2020
                    Commnet: Round function is commneted to solve the real value
                    */
                    //$required_qty=round($required_qty,4);
                    $required_qty = $required_qty;

                    //var_dump($required_qty);
                    //$cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit']);
                    $cur_stock = reserve_stock($dbcon, $rel_n1['rp_pid'], $rel_n1['purchase_unit'], "", $rel_n1['rp_id']);
                    //var_dump($cur_stock);
                    $total = $cur_stock;

                    if ($total > $required_qty) {
                        $usable = $required_qty;
                    } else {

                        //var_dump($total."===".$o_qty);	//$usable=round(($total/$rel_n1['req_qty_one']),0,PHP_ROUND_HALF_DOWN);
                        //$usable=round(($total/$o_qty),0,PHP_ROUND_HALF_DOWN);
                        $usable = $total / $o_qty;
                        //var_dump($total/$rel_n1['req_qty_one']);
                        //$usable=$usable*$rel_n1['req_qty_one'];
                        $usable = $usable * $o_qty;
                    }
                    //var_dump($usable);
                    //var_dump($total);
                    //$machine_make[]=round(($usable/$o_qty),0,PHP_ROUND_HALF_DOWN);
                    $chkp = $usable / $o_qty;

                    /*
                    Code By Umair: 09/12/2020
                    Commnet: number_format function is commneted to solve the real value
                    */
                    //$machine_make[]=number_format($chkp,4,".","");
                    $machine_make[] = $chkp;

                    //$machine_make[]=round(($usable/$rel_n1['req_qty_one']),0,PHP_ROUND_HALF_DOWN);
                    $min_machine = min($machine_make);
                    $min_machine1111 = $min_machine;

                    $pending_qty1 = $rel_n['pen_qty'];
                    //var_dump($pending_qty1);
                    if ($min_machine1111 > $pending_qty1) {
                        $min_machine1111 = $pending_qty1;
                    }

                    if ($min_machine1111 != $rel_n['pen_qty']) {
                        /*
                        Code By Umair: 09/12/2020
                        Commnet: floor function is commneted to solve the real value
                        */
                        //$min_machine1111=floor($min_machine1111);
                        $min_machine1111 = $min_machine1111; // $pending_qty1;// code by umair : 09/12/2020

                    }
                    //var_dump($min_machine1111);

                }
                $pending_qty = $pending_qty + $rel_n['pen_qty'];
                $min_machine1112 = $min_machine1112 + $min_machine1111;
                //$machine_make_new[]=$min_machine1111;
                //$min_machine1=min($machine_make_new);
                //$min_machine1112=$min_machine1;
                if ($min_machine1112 > $pending_qty) {
                    $min_machine1112 = $pending_qty;
                }
                $min_machine111 = $min_machine111 + $min_machine1112;
                //var_dump($min_machine111);

            }
        } else {
            $min_machine111 = 0;
            $pending_qty = 0;
            $q1 = $dbcon->query("select * from tbl_allocate_process as ap 
                where ap.process_id=" . $process_id . " " . $p_id_val . " and ap.p_product_id=" . $rel['p_product_id'] . " and ap.p_status=0  and pr_process_type='$process_type'");
            while ($rel_n = mysqli_fetch_array($q1)) {

                $q22 = "select * from tbl_allocate_process as bt 
                where bt.p_id=" . $rel_n['previous_process_id'];
                $q23 = $dbcon->query($q22);
                $row12 = brp_mysqli_fetch_array($q23);

                $min_machine = $row12['process_stock'] - $row12['process_used_stock'];
                //var_dump($min_machine);
                $min_machine1111 = $min_machine;
                //$pending_qty11=$min_machine;
                $pending_qty1 = $rel_n['pen_qty'];
                if ($min_machine1111 > $pending_qty1) {
                    $min_machine1111 = $pending_qty1;
                }
                $pending_qty = $pending_qty + $pending_qty1;
                $min_machine111 = $min_machine111 + $min_machine1111;
            }
            //var_dump($min_machine111);

        }
    }

    return round($min_machine111, 2);
    //echo "11";

}

function bom_show($dbcon, $bom_id, $qty, $num, $call, $space)
{
    $html = '';
    $query_m = "select * from tbl_bom as bom where bom_status=0 and bom_id=" . $bom_id;
    $result_m = $dbcon->query($query_m);
    $rel_m = mysqli_fetch_assoc($result_m);

    $query1 = "select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 

    left join product_mst as pro on pro.product_id=bom_trn.product_id
    left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
    left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
    where bom_trn_status=0 and bom_id=" . $bom_id;
    $result1 = $dbcon->query($query1);
    $k = 1;
    $new_call = $call + 1;
    for ($x = 1; $x <= $call; $x++) {
        $space = $space . "&nbsp;&nbsp;";
    }
    while ($rel1 = mysqli_fetch_assoc($result1)) {
        $new_num = $num . "." . $k;

        $base_one_qty = $rel1['product_base_qty'] / $rel_m['product_base_qty'];
        $base_qty = $base_one_qty * $qty;
        $conv_stock = convert_stock($dbcon, $base_qty, $rel1['product_id'], "conv_unit");

        $html .= '<tr>
        <td style="border:0.5px #444 solid;">' . $space . $new_num . '</td>
        <td style="border:1px #444 solid;" >' . get_product_type_by_id($dbcon, $rel1['product_type']) . '</td>
        <td style="border:0.5px #444 solid;">' . $rel1['product_name'] . '</td>
        <td style="border:1px #444 solid;" >';
        if ($rel1['product_base_unit'] != $rel1['product_conv_unit']) {
            $html .= $base_qty . $rel1['base_unit_name'] . '<br/>';
            $html .= $conv_stock . $rel1['conv_unit_name'];
        } else {
            $html .= $base_qty . $rel1['base_unit_name'];
        }
        $html .= '</td>
        <td style="border:1px #444 solid;" >';
        $query = "select mst.*,p.process_name from tbl_product_process as mst 
        left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 and  mst.product_id=" . $rel1['product_id'] . " order by process_priority";
        $result = $dbcon->query($query);
        $cnt = mysqli_num_rows($result);
        if ($cnt > 0) {
            $html .= '<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
            <tr>
            <th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
            <th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
            </tr>';
            while ($rel = mysqli_fetch_assoc($result)) {
                if ($rel['process_type'] == 1) {
                    $process_type = "Inhouse";
                } else {
                    $process_type = "Outside";
                }

                $html .= '<tr>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel['process_priority'] . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $process_type . '</td>
                <td style="border:0.5px #444 solid;text-align:center;" >' . $rel['process_name'] . '</td>
                </tr>';
            }
            $html .= '</table>';
        }
        $html .= '</td>
        </tr>';
        $html .= bom_show($dbcon, $rel1['p_bom_id'], $base_qty, $new_num, $new_call, $space);
        $k++;
    }
    return $html;
}
function min_max_bom_show($dbcon, $bom_id, $qty, $num, $call, $space, $sp_id, $perent_id, $type, $branch_id, $jobwork_type = 0, $customer_id = 0, $sales_order_id = 0, $store_order_id = 0, $priority_status = 0)
{

    $query_m = "select * from tbl_bom as bom where bom_status=0 and bom_id=" . $bom_id;
    $result_m = $dbcon->query($query_m);
    $rel_m = mysqli_fetch_assoc($result_m);

    $query1 = "select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.reorder_qty,pro.product_desc from tbl_bomtrn as bom_trn 
    left join product_mst as pro on pro.product_id=bom_trn.product_id
    left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
    left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
    where bom_trn_status=0 and bom_id=" . $bom_id;
    $result1 = $dbcon->query($query1);
    $k = 1;
    $new_call = $call + 1;
    for ($x = 1; $x <= $call; $x++) {
        //$space=$space."&nbsp;&nbsp;";

    }
    while ($rel1 = mysqli_fetch_assoc($result1)) {
        $new_num = $num . "." . $k;

        $base_one_qty = $rel1['product_base_qty'] / $rel_m['product_base_qty'];
        $conv_one_qty = convert_stock($dbcon, $base_one_qty, $rel1['product_id'], "conv_unit");
        $base_qty = $base_one_qty * $qty;
        $reorder_qty = 0;

        /* if (!empty($rel1['reorder_qty']) && $rel1['reorder_qty'] > 0)
         {
             $reorder_qty = $rel1['reorder_qty'];
             $chk_qty = ceil($base_qty / $reorder_qty);
             $base_qty = $reorder_qty * $chk_qty;
         }*/
        $conv_stock = convert_stock($dbcon, $base_qty, $rel1['product_id'], "conv_unit");
        /*
        //if($rel1['product_base_unit']!=$rel1['product_conv_unit']){
        $base_qty  $rel1['base_unit_name']
        $conv_stock $rel1['conv_unit_name']
        //}else{
        $base_qty $rel1['base_unit_name']
    }  */

        $info_sub['sp_id'] = $sp_id;
        $info_sub['sr_no'] = $new_num;
        $info_sub['rp_pid'] = $rel1['product_id']; //product_id

        $info_sub['rp_req_qty'] = $base_qty; //required qty
        $info_sub['req_qty_one'] = $base_one_qty; //required qty
        $info_sub['rp_po_qty'] = ""; //po qty
        $info_sub['in_process_qty'] = ""; //process qty
        if (!empty($type)) {
            $info_sub['rp_req_type'] = $type; //type

        } else {
            $info_sub['rp_req_type'] = "min_max"; //type

        }
        $info_sub['process_unit'] = $rel1['product_base_unit'];
        $info_sub['purchase_unit'] = $rel1['product_conv_unit'];
        $info_sub['rp_req_date'] = date('Y-m-d');
        $info_sub['perent_id'] = $perent_id;
        $info_sub['status'] = 3;
        $info_sub['cdate'] = date('Y-m-d H:i:s');
        $info_sub['user_id'] = $_SESSION['user_id'];
        $info_sub['company_id'] = $_SESSION['company_id'];
        //$info_sub['main_request']		= $POST['g_total'];
        $info_sub['product_version'] = $rel1['p_bom_id'];
        $info_sub['bom_id'] = $rel1['p_bom_id'];
        $info_sub['jobwork_type'] = $jobwork_type;
        $info_sub['customer_id'] = $customer_id;
        $info_sub['sales_order_id'] = $sales_order_id;
        $info_sub['store_order_id'] = $store_order_id;
        $info_sub['priority_status'] = $priority_status;
        $info_sub['product_remark'] = $rel1['product_desc'];

        $inserid_sub = add_record('tbl_request_product', $info_sub, $dbcon, $branch_id);

        /* START JAYESH */

        /*   Material Formula */
        $material_query = "select * from tbl_bom_material_trn where bom_trn_id=" . $rel1['bom_trn_id'] . " AND bom_id =" . $rel1['bom_id'];
        $material_result = $dbcon->query($material_query);
        if (brp_mysqli_num_rows($material_result) > 0) {
            while ($mat_rel = mysqli_fetch_assoc($material_result)) {
                $mat_data['sp_id'] = $sp_id;
                $mat_data['rp_id'] = $inserid_sub;
                $mat_data['product_id'] = $rel1['product_id'];
                $mat_data['material_parameter_id'] = $mat_rel['material_parameter_id'];
                $mat_data['material_parameter_value'] = $mat_rel['material_parameter_value'];
                $mat_data['wo_material_trn_status'] = 0;
                $mat_data['user_id'] = $_SESSION['user_id'];
                $mat_data['company_id'] = $_SESSION['company_id'];
                $mat_data['branch_id'] = $_SESSION['branch_id'];
                $inserid_sub22 = add_record('tbl_wo_material_trn', $mat_data, $dbcon, $POST['branch_id']);
            }
        }

        /*   Material Formula */

        /*	$query_pro1="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.product_id = ".$rel1['product_id']; */

        /*$query_pro1="select* from pro_bom_process where product_id = ".$rel1['product_id']." AND bom_id =".$bom_rel['bom_id'];	*/

        $query_pro1 = "SELECT * FROM `tbl_bom` as bom
        left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
        left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
        left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
        WHERE tbl_product_process.status = 0 and  bom.bom_product='" . $rel1['product_id'] . "' and bom.bom_id=" . $info_sub['bom_id'] . " AND pro_bom_process.pr_process_id != '' AND pro_bom_process.process_status = 0 order by pro_bom_process.priority asc";

        $rel_pro1 = $dbcon->query($query_pro1);

        if (brp_mysqli_num_rows($rel_pro1) > 0) {
            while ($product_process_row = brp_mysqli_fetch_assoc($rel_pro1)) {
                $wpp_info['product_id'] = $rel1['product_id'];
                $wpp_info['rp_id'] = $inserid_sub;
                $wpp_info['process_priority'] = $product_process_row['priority'];
                $wpp_info['process_time'] = $product_process_row['process_time'];
                $wpp_info['process_type'] = $product_process_row['process_type'];
                $wpp_info['process_opening'] = $product_process_row['process_opening'];
                $wpp_info['process_id'] = $product_process_row['process_id'];
                $wpp_info['cdate'] = date("Y-m-d H:i:s");
                $wpp_info['user_id'] = $_SESSION['user_id'];
                $wpp_info['company_id'] = $_SESSION['company_id'];
                $wpp_info['branch_id'] = $POST['branch_id'];

                $inserestimateid = add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
            }
        }

        /* END JAYESH */

        min_max_bom_show($dbcon, $rel1['p_bom_id'], $base_qty, $new_num, $new_call, $space, $sp_id, $inserid_sub, $info_sub['rp_req_type'], $branch_id, $jobwork_type, $customer_id, $sales_order_id, $store_order_id, $priority_status);

        $k++;
    }
}
function update_common_no($dbcon, $type_id, $inv_type = null)
{
    $quer = "select invoicetype_id from  tbl_invoicetype where status=0 and type_id=" . $type_id . " and company_id= " . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $ro = mysqli_fetch_assoc($dbcon->query($quer));
    if (!empty($inv_type)) {
        $iid = $inv_type;
    } else {
        $iid = $ro['invoicetype_id'];
    }
    $query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = " . $iid);

}
function load_common_no($dbcon, $type_id)
{
    $row = array();
    $quer = "select invoicetype_id from  tbl_invoicetype where status=0 and type_id=" . $type_id . " and company_id= " . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $ro = mysqli_fetch_assoc($dbcon->query($quer));

    $query1 = "select * from  tbl_invoicetype where invoicetype_id='" . $ro['invoicetype_id'] . "'";
    $rows = mysqli_fetch_assoc($dbcon->query($query1));
    $id = $rows['taxinvoice_start'];
    $id = $id + 1;

    //$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
    //$end = $start+1;
    if ($rows['invoice_format'] == '2') {
        $invoiceno = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
    } else if ($rows['invoice_format'] == '1') {
        $invoiceno = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
    } else if ($rows['invoice_format'] == '3') {
        $invoiceno = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
    } else {
        $invoiceno = str_pad($id, 3, "0", STR_PAD_LEFT);
    }
    //$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
    //echo json_encode($row);
    return $invoiceno;
}


function load_common_no_for_manual_indent($dbcon, $type_id,$invoicetype_id)
{
    $row = array();
    $quer = "select invoicetype_id from  tbl_invoicetype where status=0 and type_id=" . $type_id . " and company_id= " . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $ro = mysqli_fetch_assoc($dbcon->query($quer));

    $query1 = "select * from  tbl_invoicetype where invoicetype_id='" . $invoicetype_id. "'";
    $rows = mysqli_fetch_assoc($dbcon->query($query1));
    $id = $rows['taxinvoice_start'];
    $id = $id + 1;

    //$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
    //$end = $start+1;
    if ($rows['invoice_format'] == '2') {
        $invoiceno = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
    } else if ($rows['invoice_format'] == '1') {
        $invoiceno = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
    } else if ($rows['invoice_format'] == '3') {
        $invoiceno = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
    } else {
        $invoiceno = str_pad($id, 3, "0", STR_PAD_LEFT);
    }
    //$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
    //echo json_encode($row);
    return $invoiceno;
}

function get_user_report($dbcon, $user_id, $defaul_all = false)
{
    $query = "select user_id,user_name from users where user_id in (" . $user_id . ")";
    $rs_dispatch = $dbcon->query($query);

    $str = "";
    $sel = '';
    if ($defaul_all) {
        $str .= '<option value="">All</option>';
    }

    while ($rel = mysqli_fetch_assoc($rs_dispatch)) {
        $str .= '<option ' . $sel . ' value="' . $rel['user_id'] . '">' . $rel['user_name'] . '</option>';
    }
    return $str;
}
function get_year()
{
    $minyear = 2018;
    $maxyear = (date('m') < '04') ? date('Y', strtotime('-1 year')) : date('Y');
    $str = "";
    for ($y = $minyear; $y <= $maxyear; $y++) {
        $sel = '';
        if ($maxyear == $y) {
            $sel = 'selected="selected"';
        }
        $ny = $y + 1;
        $str .= "<option " . $sel . " value='" . $y . "'> " . $y . "-" . $ny . "</option>";
    }
    return $str;
}
function check_user_chein($dbcon, $user_id, $status = 0)
{

    $query = "select user_id,user_name from users where active=0 and user_id=" . $user_id;
    $rs_dispatch = $dbcon->query($query);
    while ($rel = mysqli_fetch_assoc($rs_dispatch)) {

        //$sel[]=$user_id;
        if ($status == 1) {
            //$sel=" and find_in_set(".$user_id.",task.assign_user_ids) ";
            $sel = $user_id;
        } else {
            //$sel .=" or find_in_set(".$user_id.",task.assign_user_ids) ";
            $sel = "," . $user_id;
        }

        $query1 = "select user_id,user_name from users where active=0 and report_to_user_id=" . $user_id;
        $rs_dispatch1 = $dbcon->query($query1);
        $pending_count = mysqli_num_rows($rs_dispatch1);
        while ($rel1 = mysqli_fetch_assoc($rs_dispatch1)) {
            if (!empty($rel1['user_id'])) {
                $sel .= check_user_chein($dbcon, $rel1['user_id'], 0);
            }
        }
    }
    return $sel;
}

function load_series_no($dbcon, $type_id)
{
    //Load no by Type ID
    $row = array();
    $query1 = "select * from tbl_invoicetype where status=0 and type_id=" . $type_id . " and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $rows = mysqli_fetch_assoc($dbcon->query($query1));
    $id = $rows['taxinvoice_start'];
    $id = $id + 1;
    if ($rows['invoice_format'] == '2') {
        $row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
    } else if ($rows['invoice_format'] == '1') {
        $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
    } else if ($rows['invoice_format'] == '3') {
        $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
    } else {
        $row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
    }
    return $row['invoiceno'];
}
function find_qc_process_ids($dbcon, $grn_trn_id)
{
    $process_id = array();
    $query = "select job_work_sub_trn_id from tbl_grn_sub_trn as trn 
    where status=0 and grn_trn_id=" . $grn_trn_id;
    $result = $dbcon->query($query);
    while ($row = mysqli_fetch_assoc($result)) {
        $query1 = "select p_id from tbl_job_work_sub_trn as trn 
        where job_work_sub_trn_status=0 and job_work_sub_trn_id=" . $row['job_work_sub_trn_id'];
        $result1 = $dbcon->query($query1);
        while ($rel = mysqli_fetch_assoc($result1)) {
            array_push($process_id, $rel['p_id']);
        }
    }
    $uniq_ids = array_unique($process_id);
    $str = implode(",", $uniq_ids);
    $asing_user = explode(",", $str);
    $str = array_unique($asing_user);
    $str = implode(",", $str);
    return $str;
    //return $grn_trn_id;

}
function find_jobwork_id($dbcon, $product_id, $process_type, $process_id, $branch_id)
{
    if (!empty($branch_id)) {
        $whre_branch = " and trn.branch_id=" . $branch_id;
    }
    $query13 = "select group_concat(trn.jobwork_id ORDER BY trn.jobwork_id ASC) AS job_id from tbl_jobwork as trn 
    where status=0 and j_qty>used_qty and j_pr_process_id=" . $process_id . " " . $whre_branch . " and trn.company_id=" . $_SESSION['company_id'] . " and j_process_type=" . $process_type . " and j_product_id=" . $product_id;
    $rel3 = mysqli_fetch_assoc($dbcon->query($query13));

    return $rel3['job_id'];
}
function find_jobwork_id_in_des($dbcon, $product_id, $process_type, $process_id, $branch_id)
{
    if (!empty($branch_id)) {
        $whre_branch = " and trn.branch_id=" . $branch_id;
    }
    $query13 = "select group_concat(trn.jobwork_id ORDER BY trn.jobwork_id DESC) AS job_id from tbl_jobwork as trn 
    where status=0 and j_qty>used_qty and j_pr_process_id=" . $process_id . " " . $whre_branch . " and trn.company_id=" . $_SESSION['company_id'] . " and j_process_type=" . $process_type . " and j_product_id=" . $product_id;
    $rel3 = mysqli_fetch_assoc($dbcon->query($query13));

    return $rel3['job_id'];

    //return $query13;

}
function show_user_ids($dbcon, $assing_user_id)
{
    if (is_array($assing_user_id)) {
        $cou = count($assing_user_id);
        $row = array();
        for ($i = 0; $i < $cou; $i++) {
            if ($i == 0) {
            }
            $asing = check_crm_find_in_set_new($dbcon, $assing_user_id[$i], 1);
            //$asing=check_crm_find_in_set($dbcon,$assing_user_id[$i],1);
            array_push($row, $asing);
        }
        array_merge($row, $assing_user_id);
        $uniq_ids = array_unique($row);
        $str = implode(",", $uniq_ids);
        $asing_user = explode(",", $str);
        $str = array_unique($asing_user);
        $str = implode(",", $str);
    } else {
        $str = check_crm_find_in_set_new($dbcon, $assing_user_id, 1);
    }
    /*var_dump($str);*/
    return $str;
}
function check_crm_find_in_set_new($dbcon, $user_id, $status)
{
    $query = "select user_id,user_name,report_to_user_id from users where user_id=" . $user_id;
    $rs_dispatch = $dbcon->query($query);
    while ($rel = mysqli_fetch_assoc($rs_dispatch)) {

        //$sel[]=$user_id;
        if ($status == 1) {
            //$sel=" and find_in_set(".$user_id.",task.assign_user_ids) ";
            $sel = $user_id;
        } else {
            //$sel .=" or find_in_set(".$user_id.",task.assign_user_ids) ";
            $sel = "," . $user_id;
        }

        $query1 = "select user_id,user_name from users where user_id=" . $rel['report_to_user_id'];
        $rs_dispatch1 = $dbcon->query($query1);
        $pending_count = mysqli_num_rows($rs_dispatch1);
        while ($rel1 = mysqli_fetch_assoc($rs_dispatch1)) {
            if (!empty($rel1['user_id'])) {
                $sel .= check_crm_find_in_set_new($dbcon, $rel1['user_id'], 0);
            }
        }
    }
    /*var_dump($sel);*/
    return $sel;
}
function check_crm_find_in_set($dbcon, $user_id, $status)
{
    $query = "select user_id,user_name from users where user_id=" . $user_id;
    $rs_dispatch = $dbcon->query($query);
    while ($rel = mysqli_fetch_assoc($rs_dispatch)) {

        //$sel[]=$user_id;
        if ($status == 1) {
            //$sel=" and find_in_set(".$user_id.",task.assign_user_ids) ";
            $sel = $user_id;
        } else {
            //$sel .=" or find_in_set(".$user_id.",task.assign_user_ids) ";
            $sel = "," . $user_id;
        }

        $query1 = "select user_id,user_name from users where report_to_user_id=" . $user_id;
        $rs_dispatch1 = $dbcon->query($query1);
        $pending_count = mysqli_num_rows($rs_dispatch1);
        while ($rel1 = mysqli_fetch_assoc($rs_dispatch1)) {
            if (!empty($rel1['user_id'])) {
                $sel .= check_crm_find_in_set($dbcon, $rel1['user_id'], 0);
            }
        }
    }
    return $sel;
}
function get_tree_user($dbcon, $user_id, $sel1)
{
    $query = "select us.user_id,us.user_name,rus.user_name as pname from users as us
    left join users as rus on rus.user_id=us.report_to_user_id
    where us.user_id=" . $user_id . " and us.company_id IN (0," . $_SESSION['company_id'] . ")";
    $rs_dispatch = $dbcon->query($query);
    $str = "";
    while ($rel = mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['user_id'] == $sel1) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['user_id'] . '">' . $rel['user_name'] . ' - ' . $rel['pname'] . '</option>';

        $query1 = "select user_id,user_name from users where report_to_user_id=" . $user_id;
        $rs_dispatch1 = $dbcon->query($query1);
        while ($rel1 = mysqli_fetch_assoc($rs_dispatch1)) {
            if (!empty($rel1['user_id'])) {
                $str .= get_tree_user($dbcon, $rel1['user_id'], $sel1);
            }
        }
    }
    return $str;
}
function total_print_tax($dbcon, $ledger, $invoice_id)
{
    $query = "select * from tbl_invoicetrn as pro where trancation_status=0 and invoice_id=" . $invoice_id . " and company_id = $_SESSION[company_id]";
    $rs_dispatch = $dbcon->query($query);
    $tax = 0;
    while ($rel = mysqli_fetch_assoc($rs_dispatch)) {

        $query11 = "select * from tbl_used_tax as pro where tax_used_status=0 and used_transaction_id=" . $rel['trancation_id'] . " and table_name='tbl_invoicetrn' and ledger_id=" . $ledger . " and company_id = $_SESSION[company_id]";
        $rs_dispatch11 = $dbcon->query($query11);
        $rel112 = mysqli_fetch_assoc($rs_dispatch11);

        $tax = $tax + $rel112['tax_amount'];
    }
    return $tax;
}
function print_tax($dbcon, $ledger, $trancation_id, $type)
{

    $query11 = "select * from tbl_used_tax as pro where tax_used_status=0 and used_transaction_id=" . $trancation_id . " and table_name='tbl_invoicetrn' and ledger_id=" . $ledger . " and company_id = $_SESSION[company_id]";
    $rs_dispatch11 = $dbcon->query($query11);
    $rel112 = mysqli_fetch_assoc($rs_dispatch11);

    if ($type == "per") {
        return $rel112['tax_per'];
    } else {
        return $rel112['tax_amount'];
    }

    //return $query11;

}
function count_stock_procuct_req($dbcon)
{
    $query = 'select 
    pro.product_id,pro.product_base_unit,pro.product_name,pro.product_status,pro.product_min_stock, pro.product_opening, reqqty,(IFNULL(((IFNULL((select IFNULL(sum(qc.base_stock),0) as base_stock_add from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=' . $_SESSION['company_id'] . ' 
    group by qc.product_id),0)+IFNULL((select IFNULL(sum(qc.convert_stock),0) as con_stock_add from tbl_stock_trn as qc 
    where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=' . $_SESSION['company_id'] . ' 
    group by qc.product_id),0))-(IFNULL((select IFNULL(sum(qc.base_stock),0) as base_stock_minus from tbl_stock_trn as qc 
    where qc.stock_status != 2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=' . $_SESSION['company_id'] . ' 
    group by qc.product_id),0)+IFNULL((select IFNULL(sum(qc.convert_stock),0) as con_stock_minus from tbl_stock_trn as qc 
    where qc.stock_status != 2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=' . $_SESSION['company_id'] . ' 
    group by qc.product_id),0))),0)+IFNULL(reqqty,0))-(((IFNULL((select sum(base_stock) as base_addqty from tbl_reserve_stock where stock_status != 2 and stock_flage=1 and base_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0)+IFNULL((select sum(convert_stock) as conv_addqty from tbl_reserve_stock where stock_status != 2 and base_unit!=convert_unit and stock_flage=1 and convert_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0))-(IFNULL((select sum(base_stock) as base_usedqty from tbl_reserve_stock where stock_status != 2 and stock_flage=2 and base_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0)+IFNULL((select sum(convert_stock) as conv_usedqty from tbl_reserve_stock where stock_status != 2 and base_unit!=convert_unit and stock_flage=2 and convert_unit=pro.product_base_unit and product_id=pro.product_id group by product_id),0)))+(IFNULL((select sum(s_qty) as base_addqty1 from tbl_complain_spare_part as com
    left join tbl_complaint as c on c.complaint_id=com.s_comp_id
    where c.complaint_status=0 and sp_sent_status="yes" and s_inv_status=0 and s_product=pro.product_id group by s_product),0))) as stock_in_new
    from product_mst as pro 
    left join (select sum(req.rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0  group by req.rp_pid) as rereq on rereq.rp_pid=pro.product_id
    where pro.product_status=0 having stock_in_new < 0';
    die;
    $rs = $dbcon->query($query);
    $pending_count = mysqli_num_rows($rs);
    //$row=mysqli_fetch_array($rs);
    return $pending_count;
}
function get_part_invoice_not_done_send($dbcon, $product_id)
{
    $query = 'select sum(s_qty) as base_addqty1 from tbl_complain_spare_part as com
    left join tbl_complaint as c on c.complaint_id=com.s_comp_id
    where c.complaint_status=0 and sp_sent_status="yes" and s_inv_status=0 and s_product=' . $product_id . ' group by s_product';
    $result = $dbcon->query($query);
    $row = mysqli_fetch_assoc($result);
    return $row['base_addqty1'];
}
function service_reserve_stock($dbcon, $product_id, $type, $ref_id, $qty)
{
    $query = "select * from product_mst where product_id=" . $product_id;
    $result = $dbcon->query($query);
    $row = mysqli_fetch_assoc($result);
    $conv_stock = convert_stock($dbcon, $qty, $product_id, "conv_unit");
    $info['reserve_date'] = date('Y-m-d');
    $info['product_id'] = $product_id;
    $info['base_unit'] = $row['product_base_unit'];
    $info['base_stock'] = $qty;
    $info['convert_unit'] = $row['product_conv_unit'];
    $info['convert_stock'] = $conv_stock;
    $info['stock_flage'] = $type;
    $info['request_id'] = $ref_id;
    $info['ref_name'] = "service";

    $info['cdate'] = date('Y-m-d H:i:s');
    $info['user_id'] = $_SESSION['user_id'];
    $info['company_id'] = $_SESSION['company_id'];
    $inserid = add_record('tbl_reserve_stock', $info, $dbcon);
}
function find_request_id($dbcon, $perent_id, $product_id)
{
    $query = "select * from tbl_request_product as res
    where status=0 and perent_id=" . $perent_id . " and rp_pid=" . $product_id;
    $result = $dbcon->query($query);
    $row = mysqli_fetch_assoc($result);
    return $row['rp_id'];
}
function deduct_remove_stock($dbcon, $request_id, $stock_qty, $unit_id)
{
    $query = "select res.*,pro.product_base_unit,pro.product_conv_unit from tbl_reserve_stock as res
    left join product_mst as pro on pro.product_id=res.product_id
    where stock_status != 2 and ref_name='request' and stock_flage=1 and request_id =" . $request_id;
    $result = $dbcon->query($query);
    while ($row = mysqli_fetch_assoc($result)) {

        //request_id
        $product_id = $row['product_id'];
        $branch_id = $row['branch_id'];
        $stock = reserve_stock($dbcon, $product_id, $unit_id, $row['reserve_id'], "", "", "", $branch_id);

        if ($stock_qty != "") {
            if ($stock_qty != 0) {
                if ($stock_qty >= $stock) {
                    if ($row['product_conv_unit'] == $unit_id) {
                        $type = "base_unit";
                        $con_stock = $stock;
                        $base_stock = convert_stock($dbcon, $stock, $product_id, $type);
                    } else {
                        $type = "conv_unit";
                        $base_stock = $stock;
                        $con_stock = convert_stock($dbcon, $stock, $product_id, $type);
                    }
                    $info['reserve_date'] = date('Y-m-d');
                    $info['product_id'] = $product_id;
                    $info['base_unit'] = $row['product_base_unit'];
                    $info['base_stock'] = $base_stock;
                    $info['convert_unit'] = $row['product_conv_unit'];
                    $info['godown_id'] = $row['godown_id'];
                    $info['stock_id'] = $row['stock_id'];
                    $info['convert_stock'] = $con_stock;
                    $info['stock_flage'] = 2;
                    $info['request_id'] = $row['request_id'];
                    $info['ref_name'] = "request";
                    $info['ref_id'] = $row['reserve_id'];

                    $info['cdate'] = date('Y-m-d H:i:s');
                    $info['user_id'] = $_SESSION['user_id'];
                    $info['company_id'] = $_SESSION['company_id'];
                    $inserid = add_record('tbl_reserve_stock', $info, $dbcon, $branch_id);
                    $stock_qty = $stock_qty - $stock;
                    $q = $dbcon->query("update tbl_reserve_stock set stock_status='1' where reserve_id=" . $row['reserve_id']);
                } else {
                    if ($row['product_conv_unit'] == $unit_id) {
                        $type = "base_unit";
                        $con_stock = $stock_qty;
                        $base_stock = convert_stock($dbcon, $stock_qty, $product_id, $type);
                    } else {
                        $type = "conv_unit";
                        $base_stock = $stock_qty;
                        $con_stock = convert_stock($dbcon, $stock_qty, $product_id, $type);
                    }
                    $info['reserve_date'] = date('Y-m-d');
                    $info['product_id'] = $product_id;
                    $info['base_unit'] = $row['product_base_unit'];
                    $info['base_stock'] = $base_stock;
                    $info['convert_unit'] = $row['product_conv_unit'];
                    $info['godown_id'] = $row['godown_id'];
                    $info['stock_id'] = $row['stock_id'];
                    $info['convert_stock'] = $con_stock;
                    $info['stock_flage'] = 2;
                    $info['request_id'] = $row['request_id'];
                    $info['ref_name'] = "request";
                    $info['ref_id'] = $row['reserve_id'];

                    $info['cdate'] = date('Y-m-d H:i:s');
                    $info['user_id'] = $_SESSION['user_id'];
                    $info['company_id'] = $_SESSION['company_id'];
                    $inserid = add_record('tbl_reserve_stock', $info, $dbcon, $branch_id);

                    if ($row['product_conv_unit'] == $unit_id) {
                        //$con_stock=$stock_qty;
                        $stock_qty = $stock_qty - $con_stock;
                    } else {
                        //$base_stock=$stock_qty;
                        $stock_qty = $stock_qty - $base_stock;
                    }
                }
            }
        }
    }
}
function total_reserve_stock($dbcon, $product_id, $unit_id, $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $customer_id = "", $chk_wo_allocate = "")
{

    if (!empty($reserve_id)) {
        $rwhser = " and reserve_id=" . $reserve_id;
    } else {
        $rwhser = "";
    }
    if (!empty($request_id)) {
        $rwhser1 = " and request_id=" . $request_id;
    } else {
        $rwhser1 = "";
    }
    if (!empty($complaint_id)) {
        $rwhser2 = " and complaint_id=" . $complaint_id;
    } else {
        $rwhser2 = "";
    }
    if (!empty($sales_order_trn_id)) {
        $rwhser23 = " and sales_order_trn_id=" . $sales_order_trn_id;
    } else {
        $rwhser23 = "";
    }

    if (!empty($chk_wo_allocate)) {
        $rwhser24 = " and ref_name!='wo_allocate'";
    } else {
        $rwhser24 = "";
    }

    if (!empty($branch_id)) {
        $branch_where = " and branch_id=" . $branch_id;
    }


    if ($customer_id != "" && $customer_id > 0) {
        $cust_whr = " and customer_id = " . $customer_id;
    } else {
        $cust_whr = " and customer_id = '' and customer_id = 0 ";
    }

    $query1 = "select sum(base_stock) as base_addqty from tbl_reserve_stock where stock_status !=2 and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $branch_where . " " . $cust_whr . " and product_id=" . $product_id . $rwhser24;
    $result1 = $dbcon->query($query1);
    $row1 = mysqli_fetch_assoc($result1);

    $query2 = "select sum(convert_stock) as conv_addqty from tbl_reserve_stock where stock_status !=2 and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $branch_where . " and convert_unit=" . $unit_id . " " . $cust_whr . " and product_id=" . $product_id . $rwhser24;
    $result2 = $dbcon->query($query2);
    $row2 = mysqli_fetch_assoc($result2);

    $query3 = "select sum(base_stock) as base_usedqty from tbl_reserve_stock where stock_status != 2 and stock_flage=2 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $branch_where . " " . $cust_whr . " and product_id=" . $product_id . $rwhser24;
    $result3 = $dbcon->query($query3);
    $row3 = mysqli_fetch_assoc($result3);

    $query4 = "select sum(convert_stock) as conv_usedqty from tbl_reserve_stock where stock_status != 2 and base_unit!=convert_unit " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $branch_where . " " . $cust_whr . " and stock_flage=2 and convert_unit=" . $unit_id . " and product_id=" . $product_id . $rwhser24;
    $result4 = $dbcon->query($query4);
    $row4 = mysqli_fetch_assoc($result4);

    //$res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);
    $res_qty = ($row1['base_addqty'] + $row2['conv_addqty']);

    return $res_qty;
    //return $query3;
    //return $j;

}
// function reserve_stock($dbcon, $product_id, $unit_id, $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $p_id, $godown_id, $batch_id, $customer_id = "", $without_store_release = "")
function reserve_stock(
    $dbcon,
    $product_id,
    $unit_id,
    $reserve_id = 0,
    $request_id = 0,
    $complaint_id = 0,
    $sales_ordertrn_id = 0,
    $branch_id = 0,
    $user_id = 0,
    $date = '',
    $mode = '',
    $remarks = '')
{
    // $rwhser=""; $rwhser1=""; $rwhser2=""; $rwhser23=""; $where_branch=""; $where_godown=""; $where_batch=""; $rwhser22="";
    if (!empty($reserve_id)) {
        $rwhser = " and reserve_id=" . $reserve_id;
        $rwhser22 = " and ref_id=" . $reserve_id;
    } else {
        $rwhser = "";
        $rwhser22 = "";
    }
    if (!empty($request_id)) {
        $rwhser1 = " and request_id=" . $request_id;
    } else {
        $rwhser1 = "";
    }
    if (!empty($complaint_id)) {
        $rwhser2 = " and complaint_id=" . $complaint_id;
    } else {
        $rwhser2 = "";
    }
    if (!empty($sales_order_trn_id)) {
        $rwhser23 = " and sales_order_trn_id=" . $sales_order_trn_id;
    } else {
        $rwhser23 = "";
    }
    if (!empty($branch_id)) {
        $where_branch = " and branch_id=" . $branch_id;
    } else {
        $where_branch = "";
    }

    if (!empty($p_id)) {
        $where_branch = " and p_id=" . $p_id;
    } else {
        $where_branch = "";
    }

    if (!empty($godown_id)) {
        $where_godown = " and godown_id=" . $godown_id;
    } else {
        $where_godown = "";
    }
    if (!empty($batch_id)) {
        $where_batch = " and stock_id in(" . $batch_id . ")";
    } else {
        $where_batch = "";
    }

    $where_ref_name = "";
    if (!empty($without_store_release)) {
        $where_ref_name = " and ref_name !='store_release' ";
    }
    if (!empty($customer_id)) {
        $where_customer = " and customer_id = " . $customer_id;
    } else {
        $where_customer = " and customer_id = 0 and customer_id =''";
    }

    if ($is_store_approval) {
        // $rwhser=""; $rwhser1=""; $rwhser2=""; $rwhser23=""; $where_branch=""; $where_godown=""; $where_batch=""; $rwhser22=""; 
        $query1 = "select IFNULL(sum(approve_base_stock),0) as base_addqty from tbl_reserve_stock where stock_status !=2 and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . $where_ref_name . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id . $where_customer;
        $result1 = $dbcon->query($query1);
        $row1 = mysqli_fetch_assoc($result1);

        $query2 = "select IFNULL(sum(approve_convert_stock),0) as conv_addqty from tbl_reserve_stock where stock_status  !=2 and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . $where_ref_name . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id . $where_customer;
        $result2 = $dbcon->query($query2);
        $row2 = mysqli_fetch_assoc($result2);


        $query3 = "select IFNULL(sum(approve_base_stock),0) as base_usedqty from tbl_reserve_stock where stock_status !=2 and stock_flage=2 and base_unit=" . $unit_id . " " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . $where_ref_name . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id . $where_customer;
        $result3 = $dbcon->query($query3);
        $row3 = mysqli_fetch_assoc($result3);


        $query4 = "select IFNULL(sum(approve_convert_stock),0) as conv_usedqty from tbl_reserve_stock where stock_status !=2 and base_unit!=convert_unit " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . $where_ref_name . " and company_id=" . $_SESSION['company_id'] . " and stock_flage=2 and convert_unit=" . $unit_id . " and product_id=" . $product_id . $where_customer;


        $result4 = $dbcon->query($query4);
        $row4 = mysqli_fetch_assoc($result4);

        $res_qty = ($row1['base_addqty'] + $row2['conv_addqty']) - ($row3['base_usedqty'] + $row4['conv_usedqty']);
    } else {

        $query1 = "select IFNULL(sum(base_stock),0) as base_addqty from tbl_reserve_stock where stock_status  !=2 and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . $where_ref_name . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id . $where_customer;
        $result1 = $dbcon->query($query1);
        $row1 = mysqli_fetch_assoc($result1);

        $query2 = "select IFNULL(sum(convert_stock),0) as conv_addqty from tbl_reserve_stock where stock_status  !=2 and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . $where_ref_name . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id . $where_customer;
        $result2 = $dbcon->query($query2);
        $row2 = mysqli_fetch_assoc($result2);



        $query3 = "select IFNULL(sum(base_stock),0) as base_usedqty from tbl_reserve_stock where stock_status  !=2 and stock_flage=2 and base_unit=" . $unit_id . " " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . $where_ref_name . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id . $where_customer;

        $result3 = $dbcon->query($query3);
        $row3 = mysqli_fetch_assoc($result3);

        $query4 = "select IFNULL(sum(convert_stock),0) as conv_usedqty from tbl_reserve_stock where stock_status  !=2 and base_unit!=convert_unit " . $rwhser22 . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . $where_ref_name . " and company_id=" . $_SESSION['company_id'] . " and stock_flage=2 and convert_unit=" . $unit_id . " and product_id=" . $product_id . $where_customer;

        $result4 = $dbcon->query($query4);
        $row4 = mysqli_fetch_assoc($result4);

        $query5 = "select IFNULL(sum(approve_base_stock),0) as base_addqty from tbl_reserve_stock where stock_status  !=2 and stock_flage=1 and base_unit=" . $unit_id . " " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . $where_ref_name . " and company_id=" . $_SESSION['company_id'] . " and product_id=" . $product_id . $where_customer;
        $result5 = $dbcon->query($query5);
        $row5 = mysqli_fetch_assoc($result5);

        $query6 = "select IFNULL(sum(approve_convert_stock),0) as conv_addqty from tbl_reserve_stock where stock_status  !=2 and base_unit!=convert_unit and stock_flage=1 " . $rwhser . " " . $rwhser1 . " " . $rwhser2 . " " . $rwhser23 . " " . $where_branch . " " . $where_godown . " " . $where_batch . $where_ref_name . " and company_id=" . $_SESSION['company_id'] . " and convert_unit=" . $unit_id . " and product_id=" . $product_id . $where_customer;
        $result6 = $dbcon->query($query6);
        $row6 = mysqli_fetch_assoc($result6);

        $res_qty = ($row1['base_addqty'] + $row2['conv_addqty']) - ($row3['base_usedqty'] + $row4['conv_usedqty']);
    }
    //$j=$row1['base_addqty']."-1".$row2['conv_addqty']."-2".$row3['base_usedqty']."-3".$row4['conv_usedqty'];
    return floatval($res_qty);
    //return $query1;
    //return $j;

}
function pending_reserve_qty($dbcon, $request_id)
{
    $query = "select IFNULL(rp_req_qty,0) as rp_req_qty,purchase_unit from tbl_request_product where rp_id=" . $request_id;
    $result = $dbcon->query($query);
    $row = mysqli_fetch_assoc($result);

    $query1 = "select IFNULL(sum(base_stock),0) as base_usedqty from tbl_reserve_stock where stock_status != 2 and stock_flage=1 and ref_name='request' and base_unit=" . $row['purchase_unit'] . " and request_id=" . $request_id;
    $result1 = $dbcon->query($query1);
    $row1 = mysqli_fetch_assoc($result1);

    $query2 = "select IFNULL(sum(convert_stock),0) as conv_usedqty from tbl_reserve_stock where stock_status != 2 and ref_name='request' and base_unit!=convert_unit and stock_flage=1 and convert_unit=" . $row['purchase_unit'] . " and request_id=" . $request_id;
    $result2 = $dbcon->query($query2);
    $row2 = mysqli_fetch_assoc($result2);

    $penqty = ($row['rp_req_qty'] - ($row1['base_usedqty'] + $row2['conv_usedqty']));

    return $penqty;

    //return $row['rp_req_qty']."---".$row1['base_usedqty']."---".$row2['conv_usedqty'];

}
function add_request_reserve_stock($dbcon, $request_id, $stock_qty, $unit_id, $branch_id, $stock_id, $qc_process_trn_id = 0)
{
    //var_dump($stock_qty);
    //var_dump($unit_id);
    $qry_stc = "SELECT used_convert_stock,used_base_stock, godown_id,stock_id FROM tbl_stock_trn where stock_id = " . $stock_id;
    $res_stck = $dbcon->query($qry_stc);
    $rw_stc = brp_mysqli_fetch_assoc($res_stck);

    $godown_id = $rw_stc['godown_id'];

    $query = "select req.*,pro.product_conv_unit,pro.product_base_unit from tbl_request_product as req
    left join product_mst as pro on pro.product_id=req.rp_pid
    where rp_id in (" . $request_id . ")";
    $result = $dbcon->query($query);

    while ($row = mysqli_fetch_assoc($result)) {
        $product_id = $row['rp_pid'];

        $dbcon->query("update tbl_request_product set finish_used_qty='" . $stock_qty . "', finish_status=1, job_card_status=3 where rp_id=" . $row['rp_id']);

        if ($row['perent_id'] != 0) {

            $query_pid = "select p_id from tbl_allocate_process as req
            where p_ref_id=" . $row['perent_id'] . " and process_priority=1";
            $result_pid = $dbcon->query($query_pid);

            $row_pid = mysqli_fetch_assoc($result_pid);

            //$reserve_stock=reserve_stock($dbcon,$product_id,$row['purchase_unit'],$branch_id);
            $reserve_stock = reserve_stock($dbcon, $product_id, $row['process_unit'], "", "", "", "", $branch_id);
            $current_stock = get_current_stock_new($dbcon, $product_id, $row['process_unit'], $branch_id);
            $stock = $current_stock - $reserve_stock;

            //$reserve_pending_qty=pending_reserve_qty($dbcon,$row['rp_id']);
            $reserve_pending_qty = $stock_qty;

            if ($row['product_conv_unit'] != $row['product_base_unit']) {
                $conv_stock = convert_stock($dbcon, $reserve_pending_qty, $product_id, "conv_unit");
            } else {
                $conv_stock = $reserve_pending_qty;
            }
            if ($reserve_pending_qty > "0") {
                if ($stock >= $reserve_pending_qty) {

                    //	if($row['product_conv_unit']==$unit_id){
                    if ($row['product_base_unit'] == $unit_id) {
                        $type = "conv_unit";
                        //$con_stock=$stock_qty;
                        $base_stock = $reserve_pending_qty;
                        //var_dump($base_stock);
                        //$base_stock=convert_stock($dbcon,$stock_qty,$product_id,$type);
                        $con_stock = convert_stock($dbcon, $reserve_pending_qty, $product_id, $type);
                    } else {
                        $type = "base_unit";
                        //$base_stock=$stock_qty;
                        $con_stock = $reserve_pending_qty;
                        //$con_stock=convert_stock($dbcon,$stock_qty,$product_id,$type);
                        $base_stock = convert_stock($dbcon, $reserve_pending_qty, $product_id, $type);
                    }

                    $info['reserve_date'] = date('Y-m-d');
                    $info['product_id'] = $product_id;
                    $info['base_unit'] = $row['product_base_unit'];
                    $info['base_stock'] = $base_stock;
                    $info['convert_unit'] = $row['product_conv_unit'];
                    $info['convert_stock'] = $con_stock;
                    $info['stock_flage'] = 1;
                    $info['request_id'] = $row['rp_id'];
                    $info['ref_name'] = "request";
                    $info['ref_id'] = $qc_process_trn_id;
                    $info['godown_id'] = $godown_id;

                    $info['p_id'] = $row_pid['p_id'];
                    $info['stock_id'] = $stock_id;

                    if (!empty($row['sales_order_trn_id'])) {
                        $info['sales_order_trn_id'] = $row['sales_order_trn_id'];
                    }

                    $info['cdate'] = date('Y-m-d H:i:s');
                    $info['user_id'] = $_SESSION['user_id'];
                    $info['company_id'] = $_SESSION['company_id'];
                    $inserid = add_record('tbl_reserve_stock', $info, $dbcon, $branch_id);

                    if ($inserid) {
                        if ($qc_process_trn_id > 0) {
                            $stc_up_info['used_base_stock'] = $rw_stc['used_base_stock'] + $base_stock;
                            $stc_up_info['used_convert_stock'] = $rw_stc['used_convert_stock'] + $con_stock;
                            update_record('tbl_stock_trn', $stc_up_info, "stock_id =" . $rw_stc['stock_id'], $dbcon);
                        }
                    }
                    $reserve_pending_qty = pending_reserve_qty($dbcon, $row['rp_id']);
                    if ($reserve_pending_qty <= "0") {
                        $q = $dbcon->query("update tbl_request_product set reserve_status='1' where rp_id=" . $row['rp_id']);
                    }
                } else if ($stock > 0) {
                    if ($row['purchase_unit'] != $row['process_unit']) {
                        $conv_stock = convert_stock($dbcon, $stock, $product_id, "conv_unit");
                    } else {
                        $conv_stock = $stock;
                    }
                    $info['reserve_date'] = date('Y-m-d');
                    $info['product_id'] = $product_id;
                    $info['base_unit'] = $row['purchase_unit'];
                    $info['base_stock'] = $stock;
                    $info['convert_unit'] = $row['process_unit'];
                    $info['convert_stock'] = $conv_stock;
                    $info['stock_flage'] = 1;
                    $info['request_id'] = $row['rp_id'];
                    $info['ref_name'] = "request";
                    $info['ref_id'] = $qc_process_trn_id;
                    $info['godown_id'] = $godown_id;
                    $info['p_id'] = $row_pid['p_id'];
                    $info['stock_id'] = $stock_id;

                    if (!empty($row['sales_order_trn_id'])) {
                        $info['sales_order_trn_id'] = $row['sales_order_trn_id'];
                    }

                    $info['cdate'] = date('Y-m-d H:i:s');
                    $info['user_id'] = $_SESSION['user_id'];
                    $info['company_id'] = $_SESSION['company_id'];
                    $inserid = add_record('tbl_reserve_stock', $info, $dbcon, $branch_id);
                    if ($inserid) {
                        if ($qc_process_trn_id > 0) {
                            $stc_up_info['used_base_stock'] = $rw_stc['used_base_stock'] + $stock;
                            $stc_up_info['used_convert_stock'] = $rw_stc['used_convert_stock'] + $conv_stock;
                            update_record('tbl_stock_trn', $stc_up_info, "stock_id =" . $rw_stc['stock_id'], $dbcon);
                        }
                    }
                }
            } else {
                $set = "select GROUP_CONCAT(request_id) as trn_req_id,sum(transfer_qty) as trn_qty from tbl_work_order_stock_transfer_trn where work_order_stock_transfer_trn_status=0 and branch_id=" . $branch_id . " and transfer_request_id=" . $row['rp_id'];
                $set_head = brp_mysqli_fetch_assoc($dbcon->query($set));

                if (!empty($set_head['trn_req_id'])) {
                    add_request_reserve_stock($dbcon, $set_head['trn_req_id'], $set_head['trn_qty'], $unit_id, $branch_id);
                }
            }
            //var_dump($reserve_pending_qty);
            //var_dump($stock);
            //var_dump($info);
            //var_dump("122");

        } else {
            add_so_reserve_stock_production($dbcon, $request_id, $stock_qty, $unit_id, $branch_id);
        }
    }
    /*
    Code By Umair:  12/12/2020
    Comment:
    */
    resource_schedule_assign($dbcon, $request_id, $stock_qty);
    //return $current_stock;

}

/*
Code By Umair:  15/12/2020
Comment: Check resource schedule and add new record
*/

function resource_schedule_assign($dbcon, $request_id, $stock_qty)
{

    $get_parent_sql = "select * from tbl_request_product where rp_id in (" . $request_id . ")  and company_id = '" . $_SESSION['company_id'] . "' group by perent_id";
    $result = $dbcon->query($get_parent_sql);

    while ($row = brp_mysqli_fetch_assoc($result)) {
        $perent_id = $row['perent_id'];
        $sp_id = $row['sp_id'];
        $request_pro_branch_id = $row['branch_id'];

        $check_inprocess_qty_sql = "select * from tbl_request_product where sp_id='" . $sp_id . "' and in_process_qty!='0' and company_id = '" . $_SESSION['company_id'] . "' and branch_id='" . $request_pro_branch_id . "' ";
        $check_inprocess_qty_exec = $dbcon->query($check_inprocess_qty_sql);

        while ($check_inprocess_qty_data = brp_mysqli_fetch_assoc($check_inprocess_qty_exec)) {

            $p_ref_id = $check_inprocess_qty_data['rp_id'];
            $sp_id = $check_inprocess_qty_data['sp_id'];

            // Get Data From tbl_allocate_process
            $allocate_sql = "select * from tbl_allocate_process where p_ref_id = '" . $p_ref_id . "' and company_id = '" . $_SESSION['company_id'] . "' and pr_process_type = '1' ";
            $allocate_exec = $dbcon->query($allocate_sql);
            $allocate_row = brp_mysqli_fetch_assoc($allocate_exec);

            $process_id = $allocate_row['process_id'];
            $process_type = $allocate_row['pr_process_type'];
            $product_id = $allocate_row['p_product_id'];
            $resource_id = $allocate_row['resource_id'];
            $allocate_process_branch_id = $allocate_row['branch_id'];

            $av_qty = start_qty_avalable($dbcon, $process_id, $process_type, $product_id, "", "");
            // var_dump($av_qty);
            if ($av_qty > 0) {

                $resource_schedule_sql = "select * from tbl_resource_schedule where sp_id = '" . $sp_id . "' and process_id = '" . $process_id . "' and p_product_id = '" . $product_id . "' and work_status in (0,1) and company_id = '" . $_SESSION['company_id'] . "' and branch_id='" . $allocate_process_branch_id . "' ";
                $resource_schedule_exec = $dbcon->query($resource_schedule_sql);
                $resource_schedule_count = brp_mysqli_num_rows($resource_schedule_exec);

                if ($resource_schedule_count <= 0) {

                    // Check resource last data and time work
                    $last_date_time_of_resource = resource_finish_work_date($dbcon, $resource_id, $allocate_process_branch_id);
                    $last_date_time_of_resource = json_decode($last_date_time_of_resource, true);

                    //print_r($last_date_time_of_resource);
                    $last_date = $last_date_time_of_resource['last_date'];
                    $working_hours = $last_date_time_of_resource['resource_working_hour'];

                    // Get process time based on the product_id and process_id
                    $process_sql = "select * from tbl_product_process where status = 0 and product_id = '" . $product_id . "' and process_id = '" . $process_id . "' and company_id = '" . $_SESSION['company_id'] . "' ";
                    $process_exec = $dbcon->query($process_sql);
                    $process_row = mysqli_fetch_assoc($process_exec);

                    $total_time_in_min = $av_qty * $process_row["process_time"];
                    $total_time_in_hours = number_format($total_time_in_min / 60, 2, '.', '');

                    // Get Calcualte Total Hours
                    $previous_hour_info = get_resource_total_hour_based_on_id($dbcon, $resource_id, $allocate_process_branch_id);

                    $previous_hour = $previous_hour_info;
                    $total_hours_of_res = $previous_hour + $total_time_in_hours;
                    //$numberof_days = $total_hours_of_res/$working_hours;


                    // Get First Expected Date
                    $first_expected_date_of_reso = get_resource_first_expected_date($dbcon, $resource_id, $allocate_process_branch_id);
                    $first_date = $first_expected_date_of_reso;

                    //$completd_date = get_completed_date_of_resource_based_on_working_hours($first_date, $numberof_days);
                    $start_shift_time = RESOURCE_START_SHIFT_TIME;
                    $end_shift_time = RESOURCE_END_SHIFT_TIME;

                    $start_shift = strtotime($start_shift_time);
                    $end_shift = strtotime($end_shift_time);

                    $total_work_time = $end_shift - $start_shift;
                    $total_work_time = $total_work_time / (60 * 60);

                    $working_time = $total_hours_of_res;

                    $start_time = strtotime(date('Y-m-d H:i:s'));
                    $remaining_time = ($end_shift - $start_time) / (60 * 60);
                    $remaining_time = number_format((float) $remaining_time, 2, '.', '');

                    $completd_date = calculate_next_date($remaining_time, $working_time, $start_time, $total_work_time, $start_shift_time);

                    $saveresource_sch['resource_id'] = $allocate_row['resource_id'];
                    $saveresource_sch['process_id'] = $allocate_row['process_id'];
                    $saveresource_sch['sp_id'] = $sp_id;
                    $saveresource_sch['rp_id'] = $p_ref_id;
                    $saveresource_sch['job_card_number'] = $check_inprocess_qty_data['job_card_no'];
                    $saveresource_sch['p_qty'] = $stock_qty;
                    $saveresource_sch['pen_qty'] = $stock_qty;
                    $saveresource_sch['total_hour'] = $total_time_in_hours;
                    $saveresource_sch['expected_start_date'] = $last_date;
                    $saveresource_sch['expected_end_date'] = $completd_date;
                    $saveresource_sch['work_status'] = 0;

                    $saveresource_sch['p_product_id'] = $allocate_row['p_product_id'];
                    $saveresource_sch['pr_process_type'] = $allocate_row['pr_process_type'];
                    $saveresource_sch['process_unit'] = $allocate_row['process_unit'];
                    $saveresource_sch['user_id'] = $_SESSION['user_id'];
                    $saveresource_sch['cdate'] = date('Y-m-d H:i:s');
                    $saveresource_sch['company_id'] = $_SESSION['company_id'];

                    add_record('tbl_resource_schedule', $saveresource_sch, $dbcon, $allocate_process_branch_id);
                }
            }
        }
    }
}
/*
Code By Umair:  12/12/2020
Comment: Check resource schedule and add new record old code
*/
function resource_schedule_assign_15122020($dbcon, $request_id, $stock_qty)
{

    $get_parent_sql = "select * from tbl_request_product where rp_id in (" . $request_id . ") and user_id = '" . $_SESSION['user_id'] . "' and company_id = '" . $_SESSION['company_id'] . "' group by perent_id";
    $result = $dbcon->query($get_parent_sql);

    while ($row = brp_mysqli_fetch_assoc($result)) {
        $perent_id = $row['perent_id'];
        $sp_id = $row['sp_id'];

        $get_res_sch_sql = "select * from tbl_resource_schedule where sp_id = '" . $sp_id . "' and rp_id = '" . $perent_id . "' and user_id = '" . $_SESSION['user_id'] . "' and company_id = '" . $_SESSION['company_id'] . "' ";
        $get_res_sch_exc = $dbcon->query($get_res_sch_sql);
        $get_res_sch_count = brp_mysqli_num_rows($get_res_sch_exc);

        if ($get_res_sch_count <= 0) {

            // Check the row material list based on the perent_id from tbl_request_product table
            $child_sql = "select rp_id from tbl_request_product where perent_id = '" . $perent_id . "' and user_id = '" . $_SESSION['user_id'] . "' and company_id = '" . $_SESSION['company_id'] . "' ";
            $child_sql_exec = $dbcon->query($child_sql);
            $child_sql_count = brp_mysqli_num_rows($child_sql_exec);

            $child_reuest_id = [];
            while ($child_sql_row = brp_mysqli_fetch_assoc($child_sql_exec)) {
                $child_reuest_id[] = $child_sql_row['rp_id'];
            }

            $child_reuest_id = implode(',', $child_reuest_id);

            // Check the stock material list based on the request_id from tbl_reserve_stock table
            $stock_sql = "select * from tbl_reserve_stock where request_id in (" . $child_reuest_id . ") and user_id = '" . $_SESSION['user_id'] . "' and company_id = '" . $_SESSION['company_id'] . "' ";
            $stock_sql_exec = $dbcon->query($stock_sql);
            $stock_sql_count = brp_mysqli_num_rows($stock_sql_exec);

            // check both count
            if ($child_sql_count == $stock_sql_count) {

                // Get Data From tbl_allocate_process
                $allocate_sql = "select * from tbl_allocate_process where p_ref_id = '" . $perent_id . "' and user_id = '" . $_SESSION['user_id'] . "' and company_id = '" . $_SESSION['company_id'] . "' ";
                $allocate_exec = $dbcon->query($allocate_sql);
                $allocate_row = brp_mysqli_fetch_assoc($allocate_exec);

                $resource_id = $allocate_row['resource_id'];

                // Check resource last data and time work
                $last_date_time_of_resource = resource_finish_work_date($dbcon, $resource_id);
                $last_date_time_of_resource = json_decode($last_date_time_of_resource, true);

                $last_date = $last_date_time_of_resource['last_date'];
                $working_hours = $last_date_time_of_resource['resource_working_hour'];

                // Get process time based on the product_id and process_id
                $process_sql = "select * from tbl_product_process where status = 0 and product_id = '" . $allocate_row['p_product_id'] . "' and process_id = '" . $allocate_row['process_id'] . "' and user_id = '" . $_SESSION['user_id'] . "' and company_id = '" . $_SESSION['company_id'] . "' ";
                $process_exec = $dbcon->query($process_sql);
                $process_row = brp_mysqli_fetch_assoc($process_exec);

                $total_time_in_min = $allocate_row['p_qty'] * $process_row["process_time"];
                $total_time_in_hours = number_format($total_time_in_min / 60, 2, '.', '');

                // Get Calcualte Total Hours
                $previous_hour_info = get_resource_total_hour_based_on_id($dbcon, $resource_id);

                $previous_hour = $previous_hour_info;
                $total_hours_of_res = $previous_hour + $total_time_in_hours;
                $numberof_total_hours = $total_hours_of_res / $working_hours;

                // Get First Expected Date
                $first_expected_date_of_reso = get_resource_first_expected_date($dbcon, $resource_id);

                $first_date = $first_expected_date_of_reso;

                $completd_date = get_completed_date_of_resource_based_on_working_hours($first_date, $numberof_total_hours);

                $saveresource_sch['resource_id'] = $allocate_row['resource_id'];
                $saveresource_sch['process_id'] = $allocate_row['process_id'];
                $saveresource_sch['sp_id'] = $sp_id;
                $saveresource_sch['rp_id'] = $perent_id;
                $saveresource_sch['job_card_number'] = $allocate_row['job_card_no'];
                $saveresource_sch['p_qty'] = $allocate_row['p_qty'];
                $saveresource_sch['pen_qty'] = $allocate_row['pen_qty'];
                $saveresource_sch['total_hour'] = $total_time_in_hours;
                $saveresource_sch['expected_start_date'] = $last_date;
                $saveresource_sch['expected_end_date'] = $completd_date;
                $saveresource_sch['work_status'] = 0;

                $saveresource_sch['p_product_id'] = $allocate_row['p_product_id'];
                $saveresource_sch['pr_process_type'] = $allocate_row['pr_process_type'];
                $saveresource_sch['process_unit'] = $allocate_row['process_unit'];
                $saveresource_sch['user_id'] = $_SESSION['user_id'];
                $saveresource_sch['cdate'] = date('Y-m-d H:i:s');
                $saveresource_sch['company_id'] = $_SESSION['company_id'];

                add_record('tbl_resource_schedule', $saveresource_sch, $dbcon);
            }
        }
    }
}

/*
Code By Umair:  12/12/2020
Comment: Check last date of particular resource 
*/
function resource_finish_work_date($dbcon, $resource_id, $branch_id = 0)
{

    $fetch_last_date_sql = "select * from tbl_resource_schedule where work_status in (0,1) and resource_id = '" . $resource_id . "' and company_id = '" . $_SESSION['company_id'] . "' and branch_id='" . $branch_id . "' order by resource_schedule_id desc limit 1 ";
    $fetch_last_date_exec = $dbcon->query($fetch_last_date_sql);
    $fetch_last_date_count = brp_mysqli_num_rows($fetch_last_date_exec);

    // Getch resources working hour
    $resource_where = 'resource_id="' . $resource_id . '" and branch_id="' . $branch_id . '" ';
    $get_resource_row = get_resource_info_by_id($dbcon, $resource_where);
    $resource_working_hour = $get_resource_row['working_hours'];

    $return_arr = [];
    if ($fetch_last_date_count > 0) {
        $resource_row = brp_mysqli_fetch_assoc($fetch_last_date_exec);

        $work_date = $resource_row['expected_end_date'];
        $current_date = date('Y-m-d H:i:s');

        $work_date = strtotime($work_date);
        $current_date = strtotime($current_date);

        if ($work_date >= $current_date) {
            $last_date = date('Y-m-d H:i:s', $work_date);
        } else {
            $last_date = date('Y-m-d H:i:s', $current_date);
        }
        //var_dump($last_date);
        $return_arr = array(
            'last_date' => $last_date,
            'resource_working_hour' => $resource_working_hour
        );
    } else {
        $remaining_time = $resource_working_hour;

        //$work_date = date("Y-m-d 10:00:00", strtotime("+1 day"));
        $work_date = date("Y-m-d H:i:s");

        $return_arr = array(
            'last_date' => $work_date,
            'resource_working_hour' => $resource_working_hour
        );
    }
    //var_dump($work_date);
    return json_encode($return_arr);
}

/*
Code By Umair:  14/12/2020
Comment: get the first date based on resource id
*/

function get_resource_first_expected_date($dbcon, $resource_id, $branch_id = 0)
{
    $fetch_last_date_sql = "select * from tbl_resource_schedule where work_status in (0,1) and resource_id = '" . $resource_id . "' and company_id = '" . $_SESSION['company_id'] . "' and branch_id = '" . $branch_id . "' order by resource_schedule_id asc limit 1 ";

    $fetch_last_date_exec = $dbcon->query($fetch_last_date_sql);
    $fetch_last_date_count = brp_mysqli_num_rows($fetch_last_date_exec);

    if ($fetch_last_date_count > 0) {
        $row = brp_mysqli_fetch_assoc($fetch_last_date_exec);
        $expected_start_date = $row['expected_start_date'];
        $current_date = date('Y-m-d H:i:s');

        $expected_start_date = strtotime($expected_start_date);
        $current_date = strtotime($current_date);

        if ($expected_start_date >= $current_date) {
            $row_data = date('Y-m-d H:i:s', $expected_start_date);
        } else {
            $row_data = date('Y-m-d H:i:s', $current_date);
        }
    } else {
        //$row_data = date("Y-m-d 10:00:00", strtotime("+1 day"));
        $row_data = date("Y-m-d H:i:s");
    }

    return $row_data;
}

/*
Code By Umair:  14/12/2020
Comment: Count total hous based on the resource id
*/

function get_resource_total_hour_based_on_id($dbcon, $resource_id, $branch_id = 0)
{

    $get_resource_sql = "select sum(total_hour) as total_hour from tbl_resource_schedule where work_status in (0,1) and resource_id = '" . $resource_id . "'  and company_id = '" . $_SESSION['company_id'] . "' and branch_id = '" . $branch_id . "' ";
    $get_resource_exec = $dbcon->query($get_resource_sql);
    $get_resource_count = brp_mysqli_num_rows($get_resource_exec);

    $get_resource_row = brp_mysqli_fetch_assoc($get_resource_exec);
    if ($get_resource_row['total_hour'] != '') {
        $total_hours = $get_resource_row['total_hour'];
    } else {
        $total_hours = 0;
    }

    return $total_hours;
}

/*
Code By Umair:  15/12/2020
Comment: Calaculate Final Date Of Resource To Complete The Work
*/
function calculate_next_date($remaining_time, $working_time, $start_time, $total_work_time, $start_shift_time)
{

    if ($remaining_time >= $working_time) {

        $get_work_time_in_min = convertDecimalToMinutes($working_time);
        $get_work_time_in_min = round($get_work_time_in_min);
        $new_time = date('Y-m-d H:i:s', strtotime('+' . $get_work_time_in_min . ' minutes', $start_time));
    } elseif ($remaining_time < $working_time) {
        $working_time = $working_time - $remaining_time;

        $j = '1';
        for ($i = $working_time; $i > 0; $i -= ($start_shift_time - 1)) {

            $new_time = calculate_process_hours($i, $start_shift_time, $start_time, $j);
            $j++;
        }
    }

    return $new_time;
}

/*
Code By Umair:  15/12/2020
Comment: Calcaulate Next Day Process Time
*/

function calculate_process_hours($working_time, $start_shift_time, $start_time, $j)
{
    $get_work_time_in_min = convertDecimalToMinutes($working_time);
    $next_day = date('Y-m-d ' . $start_shift_time, strtotime('+' . $j . ' day', $start_time));

    $get_work_time_in_min = round($get_work_time_in_min);
    $new_time = date('Y-m-d H:i:s', strtotime('+' . $get_work_time_in_min . ' minutes', strtotime($next_day)));

    return $new_time;
}

/*
Code By Umair:  15/12/2020
Comment: Convert Decimal To Minutes
*/
function convertDecimalToMinutes($decimal)
{
    return $decimal * 60;
}

/*
Code By Umair:  17/12/2020
Comment: Get Daily Work Qty To Start The Process
*/
function get_resource_daily_qty($dbcon, $pr_process_type, $process_id, $product_id, $date, $branch_id)
{
    $where_db = check_branch('rs', $branch_id);
    $where = " $where_db and rs.company_id=" . $_SESSION['company_id'];

    $resource_schedule_sql = "select `rs`.*, `pp`.`process_time` from tbl_resource_schedule as rs left join tbl_product_process as pp on `rs`.`p_product_id` = `pp`.`product_id` where pp.status = 0 and `rs`.`process_id`='" . $process_id . "' and `rs`.`work_status`='0' and `rs`.`p_product_id`='" . $product_id . "' $where ";
    $resource_schedule_exec = $dbcon->query($resource_schedule_sql);
    $resource_schedule_data = brp_mysqli_fetch_assoc($resource_schedule_exec);

    $expected_end_date = date('Y-m-d', strtotime($resource_schedule_data['expected_end_date']));

    $pen_qty = $resource_schedule_data['pen_qty'];
    $expected_end_date = strtotime($expected_end_date);
    $today = strtotime($date);
    if ($expected_end_date > $today) {

        $expected_start_date = strtotime($resource_schedule_data['expected_start_date']);
        $expected_date = date('Y-m-d', $expected_start_date);

        if ($date == $expected_date) {
            $first_time = date('H:i', $expected_start_date);
        } else {
            $resource_start_shift = strtotime(RESOURCE_START_SHIFT_TIME);
            $first_time = date('H:i', $resource_start_shift);
        }

        $resource_end_shift = strtotime(RESOURCE_END_SHIFT_TIME);
        $end_time = date('H:i', $resource_end_shift);
    } else {
        $resource_start_shift = strtotime(RESOURCE_START_SHIFT_TIME);
        $first_time = date('H:i', $resource_start_shift);

        $expected_end_date = strtotime($resource_schedule_data['expected_end_date']);
        $end_time = date('H:i', $expected_end_date);
    }

    /*$expected_start_date = $resource_schedule_data['expected_start_date'];
    $first_time = date('H:i:s', strtotime($expected_start_date));

    $end_time = RESOURCE_END_SHIFT_TIME;*/

    $start_time = strtotime($first_time);
    $end_time = strtotime($end_time);

    $total_hours = round(abs($end_time - $start_time) / 3600, 2);
    $total_hours = $total_hours * 60;

    $working_qty = ($total_hours / $resource_schedule_data['process_time']);

    if ($pen_qty >= $working_qty) {
        $return_data = round($working_qty);
    } else {
        $return_data = round($pen_qty);
    }
    return abs(round($return_data));
}
/*
Code By Umair:  21/12/2020
Comment: Get Product Rate From tbl_product_party_purchase Based On The Purchase Card
*/
function getItemsPartyRate($dbcon, $product_id, $vendorid)
{
    $query = "select party_rate from tbl_product_party_purchase where party_id = '" . $vendorid . "' and party_product = '" . $product_id . "' and company_id = '" . $_SESSION['company_id'] . "' ";
    $result = $dbcon->query($query);
    $count = brp_mysqli_num_rows($result);
    if ($count > 0) {
        $row = brp_mysqli_fetch_assoc($result);
        $rate = $row['party_rate'];
    } else {
        $rate = 0;
    }

    return $rate;
}

/*
Code By Umair:  22/12/2020
Comment: Get Product Rate From tbl_purchaseordertrn Based On The Product Id
*/
function getItemsPurchaseOrderTrnRate($dbcon, $product_id)
{
    $query = "select product_rate from tbl_purchaseordertrn where product_id = '" . $product_id . "' and company_id = '" . $_SESSION['company_id'] . "' ";
    $result = $dbcon->query($query);
    $count = brp_mysqli_num_rows($result);
    if ($count > 0) {
        $row = brp_mysqli_fetch_assoc($result);
        $rate = $row['product_rate'];
    } else {
        $rate = 0;
    }

    return $rate;
}

function add_request_reserve_stock1($dbcon, $request_id, $stock_qty, $unit_id)
{
    $query = "select req.*,pro.product_conv_unit,pro.product_base_unit from tbl_request_product as req
    left join product_mst as pro on pro.product_id=req.rp_pid
    where rp_id=" . $request_id;
    $result = $dbcon->query($query);
    while ($row = brp_mysqli_fetch_assoc($result)) {
        $product_id = $row['rp_pid'];

        //$reserve_stock=reserve_stock($dbcon,$product_id,$row['purchase_unit']);
        //$current_stock=get_current_stock_new($dbcon,$product_id,$row['purchase_unit']);
        //$stock=$current_stock-$reserve_stock;
        //if($stock>=$reserve_pending_qty){
        /* if($row['product_conv_unit']!=$row['product_base_unit']){
        $conv_stock=convert_stock($dbcon,$reserve_pending_qty,$product_id,"base_unit");
        }else{
        $conv_stock=$reserve_pending_qty;
    } */

        if ($row['product_conv_unit'] == $unit_id) {
            $type = "base_unit";
            $con_stock = $stock_qty;
            $base_stock = convert_stock($dbcon, $stock_qty, $product_id, $type);
        } else {
            $type = "conv_unit";
            $base_stock = $stock_qty;
            $con_stock = convert_stock($dbcon, $stock_qty, $product_id, $type);
        }

        $info['reserve_date'] = date('Y-m-d');
        $info['product_id'] = $product_id;
        $info['base_unit'] = $row['product_base_unit'];
        $info['base_stock'] = $base_stock;
        $info['convert_unit'] = $row['product_conv_unit'];
        $info['convert_stock'] = $con_stock;
        $info['stock_flage'] = 1;
        $info['request_id'] = $row['rp_id'];
        $info['ref_name'] = "request";

        $info['cdate'] = date('Y-m-d H:i:s');
        $info['user_id'] = $_SESSION['user_id'];
        $info['company_id'] = $_SESSION['company_id'];
        $inserid = add_record('tbl_reserve_stock', $info, $dbcon);

        $reserve_pending_qty = pending_reserve_qty($dbcon, $row['rp_id']);
        if ($reserve_pending_qty <= "0") {
            $q = $dbcon->query("update tbl_request_product set reserve_status='1' where rp_id=" . $row['rp_id']);
        }
        /* }else if($stock>0){
        if($row['purchase_unit']!=$row['process_unit']){
        $conv_stock=convert_stock($dbcon,$stock,$product_id,"base_unit");
        }else{
        $conv_stock=$stock;
        }
        $info['reserve_date']		=date('Y-m-d');
        $info['product_id']			=$product_id;
        $info['base_unit']			=$row['purchase_unit'];
        $info['base_stock']			=$stock;
        $info['convert_unit']		=$row['process_unit'];
        $info['convert_stock']		=$conv_stock;
        $info['stock_flage']		=1;
        $info['request_id']			=$row['rp_id'];
        $info['ref_name']			="request";

        $info['cdate']				=date('Y-m-d H:i:s');
        $info['user_id']			=$_SESSION['user_id'];
        $info['company_id']			=$_SESSION['company_id'];
        $inserid=add_record('tbl_reserve_stock', $info, $dbcon);
    } */
        //var_dump($reserve_pending_qty);
        //var_dump($stock);
        //var_dump($info);
        //var_dump("122");

    }
    //return $current_stock;

}
function rquest_qty_deduct($dbcon, $product_id, $stock_qty)
{
    $query = "select * from tbl_request_product where used_status=0 and status=0 and rp_pid=" . $product_id;
    $result = $dbcon->query($query);
    while ($row = brp_mysqli_fetch_assoc($result)) {
        $pending_qty = $row['rp_req_qty'] - $row['used_rp_req_qty'];
        if ($stock_qty > 0) {
            if ($pending_qty <= $stock_qty) {
                $q = $dbcon->query("update tbl_request_product set used_status='1',used_rp_req_qty=used_rp_req_qty+" . $pending_qty . " where rp_id=" . $row['rp_id']);
                $stock_qty = $stock_qty - $pending_qty;
            } else {
                $q = $dbcon->query("update tbl_request_product set used_rp_req_qty=used_rp_req_qty+" . $stock_qty . " where rp_id=" . $row['rp_id']);
                $stock_qty = $stock_qty - $stock_qty;
            }
        }
    }
}
function find_user_name($dbcon, $user_id)
{
    $que_po1 = "select user_name from users where user_id=" . $user_id;
    $resi_grn1 = $dbcon->query($que_po1);
    $re1 = brp_mysqli_fetch_assoc($resi_grn1);
    return $re1['user_name'];
}
function delete_back_process($dbcon, $grn_id, $jobwork_id)
{
    $que_po1 = "select j_alloc_process_id from tbl_jobwork where jobwork_id=" . $jobwork_id;
    $resi_grn1 = $dbcon->query($que_po1);
    $re1 = brp_mysqli_fetch_assoc($resi_grn1);
    $process_id = $re1['j_alloc_process_id'];

    $que_po2 = "select * from tbl_allocate_process_trn where p_status=1 and pt_process_id=" . $jobwork_id . " and grn_id=" . $grn_id;
    $resi_grn2 = $dbcon->query($que_po2);
    $re2 = brp_mysqli_fetch_assoc($resi_grn2);
    $qty = $re2['pt_qty'];

    $info['p_status'] = 2;
    $updateid = update_record('tbl_allocate_process_trn', $info, "pt_id=" . $re2['pt_id'], $dbcon);

    $dbcon->query("update tbl_jobwork set used_qty=used_qty-" . $qty . " where jobwork_id=" . $jobwork_id . "");

    $dbcon->query("update tbl_allocate_process set pen_qty=pen_qty+" . $qty . " where p_id='$process_id'");

    $bb = "update tbl_allocate_process set p_status=1,task_status=1 where p_id='$process_id'";
    $dbcon->query($bb);
}
function close_grn_to_process_19_8_2020($dbcon, $grn_id, $jobwork_id, $qty)
{
    //delete_back_process($dbcon,$grn_id,$jobwork_id);
    $que_po1 = "select j_alloc_process_id from tbl_jobwork where jobwork_id=" . $jobwork_id;
    $resi_grn1 = $dbcon->query($que_po1);
    $re1 = brp_mysqli_fetch_assoc($resi_grn1);
    $process_id = $re1['j_alloc_process_id'];

    $que_po = "select * from tbl_allocate_process where p_id=" . $process_id;
    $resi_grn = $dbcon->query($que_po);
    $re = brp_mysqli_fetch_assoc($resi_grn);

    add_process_trn($dbcon, $process_id, $re['p_ref_id'], $re['p_product_id'], $re['process_id'], $qty, "1", $grn_id);

    $dbcon->query("update tbl_jobwork set used_qty=used_qty+" . $qty . " where jobwork_id=" . $jobwork_id . "");

    $dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-" . $qty . " where p_id='$process_id'");

    $set11 = "select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
    where p_id=" . $process_id;
    $se = mysqli_fetch_assoc($dbcon->query($set11));
    $sss1 = $se['start_qty'] - $se['end_qty'];
    if ($se['start_qty'] <= $se['end_qty']) {
        $bb = "update tbl_allocate_process set p_status=0,task_status=0 where p_id='$process_id'";
        $dbcon->query($bb);
    }
    if ($se['p_qty'] == $se['end_qty']) {
        $date = date("Y-m-d h:i:sa");
        $dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='" . $date . "',p_status=3 where p_id='$process_id'");
    }
}
function close_grn_to_process($dbcon, $grn_id, $jobwork_id, $qty)
{
    $job_i = explode(",", $jobwork_id);
    $nqty = $qty;
    for ($m = 0; $m < count($job_i); $m++) {
        $jobwork_id = $job_i[$m];
        //delete_back_process($dbcon,$grn_id,$jobwork_id);
        delete_back_process($dbcon, $grn_id, $jobwork_id);

        $que_po1 = "select p_id,qty,used_qty,(qty-used_qty) as pen_qty,jobwork_process_id from tbl_jobwork_process where jobwork_id=" . $jobwork_id . " having qty>used_qty";
        $resi_grn1 = $dbcon->query($que_po1);

        while ($re1 = brp_mysqli_fetch_assoc($resi_grn1)) {
            if ($nqty > 0) {
                if ($nqty >= $re1['pen_qty']) {
                    //$re1['pen_qty']
                    $process_id = $re1['p_id'];
                    $que_po = "select * from tbl_allocate_process where p_id=" . $process_id;
                    $resi_grn = $dbcon->query($que_po);
                    $re = brp_mysqli_fetch_assoc($resi_grn);
                    add_process_trn($dbcon, $process_id, $re['p_ref_id'], $re['p_product_id'], $re['process_id'], $re1['pen_qty'], "1", $grn_id);

                    $dbcon->query("update tbl_jobwork_process set used_qty=used_qty+" . $re1['pen_qty'] . " where jobwork_process_id=" . $re1['jobwork_process_id'] . "");

                    $dbcon->query("update tbl_jobwork set used_qty=used_qty+" . $re1['pen_qty'] . " where jobwork_id=" . $jobwork_id . "");

                    $dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-" . $re1['pen_qty'] . " where p_id='$process_id'");

                    $set11 = "select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
                    where p_id=" . $process_id;
                    $se = brp_mysqli_fetch_assoc($dbcon->query($set11));
                    $sss1 = $se['start_qty'] - $se['end_qty'];
                    if ($se['start_qty'] <= $se['end_qty']) {
                        $bb = "update tbl_allocate_process set p_status=0,task_status=0 where p_id='$process_id'";
                        $dbcon->query($bb);
                    }
                    if ($se['p_qty'] == $se['end_qty']) {
                        $date = date("Y-m-d h:i:sa");
                        $dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='" . $date . "',p_status=3 where p_id='$process_id'");
                    }
                    $nqty = $nqty - $re1['pen_qty'];
                } else {
                    //$nqty
                    $process_id = $re1['p_id'];
                    $que_po = "select * from tbl_allocate_process where p_id=" . $process_id;
                    $resi_grn = $dbcon->query($que_po);
                    $re = brp_mysqli_fetch_assoc($resi_grn);
                    add_process_trn($dbcon, $process_id, $re['p_ref_id'], $re['p_product_id'], $re['process_id'], $nqty, "1", $grn_id);

                    $dbcon->query("update tbl_jobwork set used_qty=used_qty+" . $nqty . " where jobwork_id=" . $jobwork_id . "");

                    $dbcon->query("update tbl_jobwork_process set used_qty=used_qty+" . $nqty . " where jobwork_process_id=" . $re1['jobwork_process_id'] . "");

                    $dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-" . $nqty . " where p_id='$process_id'");

                    $set11 = "select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
                    where p_id=" . $process_id;
                    $se = brp_mysqli_fetch_assoc($dbcon->query($set11));
                    $sss1 = $se['start_qty'] - $se['end_qty'];
                    if ($se['start_qty'] <= $se['end_qty']) {
                        $bb = "update tbl_allocate_process set p_status=0,task_status=0 where p_id='$process_id'";
                        $dbcon->query($bb);
                    }
                    if ($se['p_qty'] == $se['end_qty']) {
                        $date = date("Y-m-d h:i:sa");
                        $dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='" . $date . "',p_status=3 where p_id='$process_id'");
                    }
                    $nqty = $nqty - $nqty;
                }
            }
        }
    }
}

function add_process_trn($dbcon, $allocate_id, $ref_id, $product_id, $process_id, $qty, $status, $grn_id, $parent_pt_id, $start_stop_user_id, $grn_trn_sub_id = 0, $diff_qty_plus = 0, $diff_qty_minus = 0)
{


    $info5['pt_alloc_id'] = $allocate_id;
    $info5['pt_ref_id'] = $ref_id;
    $info5['pt_product_id'] = $product_id;
    $info5['pt_process_id'] = $process_id;
    $info5['pt_qty'] = $qty;
    $info5['p_status'] = $status;
    $info5['grn_id'] = $grn_id;
    $info5['grn_trn_sub_id'] = $grn_trn_sub_id;

    $info5['cdate'] = date("Y-m-d H:i:s");
    $info5['process_time'] = date("Y-m-d H:i:s");
    $info5['user_id'] = $_SESSION['user_id'];
    $info5['company_id'] = $_SESSION['company_id'];
    $info5['start_stop_user_id'] = $start_stop_user_id;

    $info5['variation_qty_plus'] = $diff_qty_plus;
    $info5['variation_qty_minus'] = $diff_qty_minus;

    if ($diff_qty_plus > 0 || $diff_qty_minus > 0) {
        $info5['is_qty_variation'] = 1;
    }

    // Get Resource ID from tbl_allocate_process based on the table id
    $query = "select * from tbl_allocate_process where p_id=" . $allocate_id;
    $rows = mysqli_fetch_assoc($dbcon->query($query));
    $info5['resource_id'] = $rows['resource_id'];
    //$info5['branch_id']=$rows['branch_id'];
    if ($status == '1') {
        $query1 = "select * from tbl_allocate_process_trn where pt_alloc_id=" . $allocate_id . " AND pt_ref_id=" . $ref_id . " AND pt_process_id=" . $process_id . " AND pt_process_id=" . $process_id . " AND parent_pt_id='0' ORDER BY pt_id DESC";
        $rows1 = mysqli_fetch_assoc($dbcon->query($query1));
        $info5['parent_pt_id'] = $rows1['pt_id'];
    }

    // var_dump($info5);
    $inserid_alloc = add_record('tbl_allocate_process_trn', $info5, $dbcon, $rows['branch_id']);
    //return $inserid_alloc;

}
// added by sanat :: 17-12-21  for reprocess
function add_reprocess_trn($dbcon, $allocate_id, $ref_id, $product_id, $process_id, $qty, $status, $grn_id, $parent_pt_id)
{

    $info5['pt_alloc_id'] = $allocate_id;
    $info5['pt_ref_id'] = $ref_id;
    $info5['pt_product_id'] = $product_id;
    $info5['pt_process_id'] = $process_id;
    $info5['pt_qty'] = $qty;
    $info5['p_status'] = $status;
    $info5['grn_id'] = $grn_id;

    $info5['cdate'] = date("Y-m-d H:i:s");
    $info5['process_time'] = date("Y-m-d H:i:s");
    $info5['user_id'] = $_SESSION['user_id'];
    $info5['company_id'] = $_SESSION['company_id'];

    // Get Resource ID from tbl_allocate_process based on the table id
    $query = "select * from tbl_allocate_re_process where p_id=" . $allocate_id;
    $rows = mysqli_fetch_assoc($dbcon->query($query));
    $info5['resource_id'] = $rows['resource_id'];
    //$info5['branch_id']=$rows['branch_id'];
    if ($status == '1') {
        $query1 = "select * from tbl_allocate_re_process_trn where pt_alloc_id=" . $allocate_id . " AND pt_ref_id=" . $ref_id . " AND pt_process_id=" . $process_id . " AND pt_process_id=" . $process_id . " AND parent_pt_id='0' ORDER BY pt_id DESC";
        $rows1 = mysqli_fetch_assoc($dbcon->query($query1));
        $info5['parent_pt_id'] = $rows1['pt_id'];
    }

    //var_dump($info5);
    $inserid_alloc = add_record('tbl_allocate_re_process_trn', $info5, $dbcon, $rows['branch_id']);
    //return $inserid_alloc;

}
function load_job_no($dbcon, $type_id)
{
    $row = array();
    $query1 = "select * from  tbl_invoicetype where invoicetype_id='" . $type_id . "'";
    $rows = mysqli_fetch_assoc($dbcon->query($query1));
    $id = $rows['taxinvoice_start'];
    $id = $id + 1;

    //$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
    //$end = $start+1;
    if ($rows['invoice_format'] == '2') {
        $row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
    } else if ($rows['invoice_format'] == '1') {
        $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
    } else if ($rows['invoice_format'] == '3') {
        $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
    } else {
        $row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
    }
    $row['challanno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
    //echo json_encode($row);
    return $row['invoiceno'];
}
function add_process_stock($dbcon, $allocate_process_id, $accept_qty, $reject_qty, $next_process)
{
    $que_po = "select * from tbl_allocate_process where p_id=" . $allocate_process_id;
    $resi_grn = $dbcon->query($que_po);
    $re = brp_mysqli_fetch_assoc($resi_grn);

    if ($re['previous_process_id'] != 0) {
        $minq = $accept_qty + $reject_qty;
        $dbcon->query("update tbl_allocate_process set process_used_stock=process_used_stock+" . $minq . " where p_id=" . $re['previous_process_id'] . "");

        //$info['company_id']	= $_SESSION['company_id'];
        //$updateid=update_record('tbl_allocate_process', $info,"p_id=".$POST['eid'] , $dbcon);

    }
    if (!empty($next_process)) {
        $dbcon->query("update tbl_allocate_process set process_stock=process_stock+" . $accept_qty . " where p_id=" . $allocate_process_id . "");
    } else {
        $dbcon->query("update tbl_allocate_process set process_stock=process_stock+" . $accept_qty . " where p_id=" . $allocate_process_id . "");

        $dbcon->query("update tbl_allocate_process set process_used_stock=process_used_stock+" . $accept_qty . " where p_id=" . $allocate_process_id . "");
    }
}
function deduct_process_stock($dbcon, $allocate_process_id, $accept_qty, $reject_qty, $next_process)
{

    if (!empty($next_process)) {
        $dbcon->query("update tbl_allocate_process set process_used_stock=process_used_stock+" . $reject_qty . " where p_id=" . $allocate_process_id . "");
    } else {
        $dedu = $accept_qty + $reject_qty;
        $dbcon->query("update tbl_allocate_process set process_used_stock=process_used_stock+" . $dedu . " where p_id=" . $allocate_process_id . "");
    }
}
function deduct_reprocess_stock($dbcon, $allocate_process_id, $accept_qty, $reject_qty, $next_process)
{

    if (!empty($next_process)) {
        $dbcon->query("update tbl_allocate_re_process set process_used_stock=process_used_stock+" . $reject_qty . " where p_id=" . $allocate_process_id . "");
    } else {
        $dedu = $accept_qty + $reject_qty;
        $dbcon->query("update tbl_allocate_re_process set process_used_stock=process_used_stock+" . $dedu . " where p_id=" . $allocate_process_id . "");
    }
}
function add_process_stock_new($dbcon, $allocate_process_id, $accept_qty, $minus_stock)
{
    $que_po = "select * from tbl_allocate_process where p_id=" . $allocate_process_id;
    $resi_grn = $dbcon->query($que_po);
    $re = brp_mysqli_fetch_assoc($resi_grn);

    if ($re['previous_process_id'] != 0) {
        //$minq=$accept_qty;
        $minq = $minus_stock;
        $dbcon->query("update tbl_allocate_process set process_used_stock=process_used_stock+" . $minq . " where p_id=" . $re['previous_process_id'] . "");
    }
    $dbcon->query("update tbl_allocate_process set process_stock=process_stock+" . $accept_qty . " where p_id=" . $allocate_process_id . "");
}
function process_allocate($dbcon, $pre_process_id, $process_id, $process_qty, $ref_id, $ref_type, $product_id, $process_type, $process_unit, $process_priority, $grn_type = null, $branch_id = "")
{
    $set11 = "select p_id,p_qty,pen_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty1,alo.product_version from tbl_allocate_process as alo
    where previous_process_id=" . $pre_process_id . " having start_qty1=0";
    $se = mysqli_fetch_assoc($dbcon->query($set11));

    if ($se['p_id']) {
        $dbcon->query("update tbl_allocate_process set pen_qty=pen_qty+" . $process_qty . ",p_qty=p_qty+" . $process_qty . " where p_id=" . $se['p_id']);
        $return_id = $se['p_id'];
    } else {
        $severs = "select alo.product_version from tbl_allocate_process as alo
        where p_id=" . $pre_process_id . "";
        $se_ver = mysqli_fetch_assoc($dbcon->query($severs));

        /*Get Resource ID*/
        $resourceinfo = get_resource_from_product_process($dbcon, $product_id, $process_id, $where = null);

        $info5['process_id'] = $process_id;
        $info5['p_start_time'] = '';
        $info5['p_end_time'] = '';
        $info5['p_qty'] = $process_qty;
        $info5['pen_qty'] = $process_qty;
        $info5['process_unit'] = $process_unit;
        $info5['p_ref_id'] = $ref_id;
        $info5['p_ref_type'] = $ref_type;
        $info5['p_product_id'] = $product_id;
        $info5['pr_process_type'] = $process_type;
        $info5['process_priority'] = $process_priority;
        $info5['previous_process_id'] = $pre_process_id;
        $info5['product_version'] = $se_ver['product_version'];

        if ($info5['pr_process_type'] == '1') {
            $info5['resource_id'] = $resourceinfo['resource_id'];
        }

        $info5['cdate'] = date("Y-m-d H:i:s");
        $info5['user_id'] = $_SESSION['user_id'];
        $info5['company_id'] = $_SESSION['company_id'];

        $inserid_alloc = add_record('tbl_allocate_process', $info5, $dbcon, $branch_id);

        if ($grn_type == '1') {
            resource_schedule_assign_at_process_allocate($dbcon, $ref_id, $process_qty, $inserid_alloc);
        }
        $return_id = $inserid_alloc;
    }
    return $return_id;
}

/*
Code By jayesh:  28/01/2022
Comment: Check resource schedule and add new record
*/
function resource_schedule_assign_at_process_allocate($dbcon, $request_id, $stock_qty, $p_id)
{
    //echo "request_id". $request_id;
    $allocate_sql = "select * from tbl_allocate_process where p_id = '" . $p_id . "' and company_id = '" . $_SESSION['company_id'] . "' and pr_process_type = '1' ";

    $allocate_exec = $dbcon->query($allocate_sql);
    $allocate_row = brp_mysqli_fetch_assoc($allocate_exec);

    $get_parent_sql = "select * from tbl_request_product where rp_id in (" . $request_id . ")  and company_id = '" . $_SESSION['company_id'] . "' ";
    $re_result = $dbcon->query($get_parent_sql);
    $request_row = brp_mysqli_fetch_assoc($re_result);

    $process_id = $allocate_row['process_id'];
    $resource_id = $allocate_row['resource_id'];
    $p_qty = $allocate_row['p_qty'];
    $pen_qty = $allocate_row['pen_qty'];
    $product_id = $allocate_row['p_product_id'];
    $pr_process_type = $allocate_row['pr_process_type'];
    $process_unit = $allocate_row['process_unit'];
    $allocate_process_branch_id = $allocate_row['branch_id'];

    $sp_id = $request_row['sp_id'];
    $job_card_number = $request_row['job_card_no'];

    // Check resource last data and time work
    $last_date_time_of_resource = resource_finish_work_date($dbcon, $resource_id, $allocate_process_branch_id);
    $last_date_time_of_resource = json_decode($last_date_time_of_resource, true);

    $last_date = $last_date_time_of_resource['last_date'];
    $working_hours = $last_date_time_of_resource['resource_working_hour'];

    // Get process time based on the product_id and process_id
    $process_sql = "select * from tbl_product_process where status = 0 and product_id = '" . $product_id . "' and process_id = '" . $process_id . "' and company_id = '" . $_SESSION['company_id'] . "' ";
    $process_exec = $dbcon->query($process_sql);
    $process_row = brp_mysqli_fetch_assoc($process_exec);

    /* START JAYESH */
    if ($request_row['reserve_stock'] == 0) {

        $r_id = $request_row['rp_id'];
        $rp_pid = $request_row['rp_pid'];

        $ge_process_sql = "select * from tbl_product_process status = 0 and where product_id = '$rp_pid' and process_priority<=" . $process_row['process_priority'] . " and company_id = '" . $_SESSION['company_id'] . "' ";

        $ge_process_result = $dbcon->query($ge_process_sql);

        $qty_proces_min = array();
        while ($ge_process_row = brp_mysqli_fetch_assoc($ge_process_result)) {
            $qty_proces_min[] = $stock_qty * $ge_process_row['process_time'];
        }

        //echo $qty_proces_min; die;
        //echo "total process time ". array_sum($qty_proces_min);
        /*echo "<pre>"; print_r($request_row); die;
         */

        $pr_id = $request_row['rp_pid'];
        $product_sql = "select * from tbl_request_product as rp left join product_mst as p ON rp.rp_pid = p.product_id  where rp.perent_id = " . $r_id;
        $product_exec = $dbcon->query($product_sql);
        $product_row = brp_mysqli_fetch_assoc($product_exec);
        $product_lead_time = $product_row['product_lead_time'];
        $product_lead_time = 0;

        $company_config = getCompanyConfiguration($dbcon, $id = false);
        if ($company_config['resource_time'] == '0') {
            $product_lead_time = $product_row['product_lead_time'];

            $total_time_in_min = array_sum($qty_proces_min) + $product_lead_time;
            //var_dump($product_lead_time);
            //echo "</br>";
            //pathik code commit date 19-04-2022 lead time days ma ave so min to day conversation bandh karyu
            $product_lead_time = $product_lead_time / 8 / 60;
            //pathik code end
            //var_dump($product_lead_time);
            //echo "</br>";
            /*if($product_lead_time<=1){
            $product_lead_time=1;
        }*/
        } else {

            /*$resource_time_sql = "select * from tbl_resource as r inner join hrms_shift_type as s ON s.id = r.shift_type  where r.resource_id = ".$resource_id;
            $resource_time_exec = $dbcon->query($resource_time_sql);
            $resource_time_row=brp_mysqli_fetch_assoc($resource_time_exec);
            $shift_total_hours = get_total_hours($resource_time_row['shift_start_time'],$resource_time_row['shift_end_time']);

            $product_lead_time = ($product_row['product_lead_time'])*$shift_total_hours*60;  */

            $product_lead_time = $product_row['product_lead_time'];
        }
        //echo "lead time :". $product_lead_time; die;
        /*echo "process time". $process_row["process_time"];
        echo "<br>stock". $stock_qty; */

        //date('Y-m-d H:i:s',strtotime('+1 day +1 hour +30 minutes +45 seconds',strtotime($startTime)));
        //var_dump($product_lead_time);
        //var_dump($last_date_time_of_resource['last_date']);
        //var_dump(array_sum($qty_proces_min));
        $newstarttimestamp = strtotime($last_date_time_of_resource['last_date'] . '+' . $product_lead_time . 'day ');
        $newtimestamp = strtotime($last_date_time_of_resource['last_date'] . '+' . $product_lead_time . 'day +' . array_sum($qty_proces_min) . ' minute');
        $start_date = date('Y-m-d H:i:s', $newstarttimestamp);
        $last_date = date('Y-m-d H:i:s', $newtimestamp);
    } else {
        $total_time_in_min = $stock_qty * $process_row["process_time"];
    }

    //echo "Total time->".$total_time_in_min;  die;


    $total_time_in_hours = number_format($total_time_in_min / 60, 2, '.', '');

    //echo "total time ".$total_time_in_hours;
    // Get Calcualte Total Hours
    $previous_hour_info = get_resource_total_hour_based_on_id($dbcon, $resource_id, $allocate_process_branch_id);

    $previous_hour = $previous_hour_info;
    $total_hours_of_res = $previous_hour + $total_time_in_hours;
    //$numberof_days = $total_hours_of_res/$working_hours;


    // Get First Expected Date
    $first_expected_date_of_reso = get_resource_first_expected_date($dbcon, $resource_id, $allocate_process_branch_id);
    $first_date = $first_expected_date_of_reso;

    //$completd_date = get_completed_date_of_resource_based_on_working_hours($first_date, $numberof_days);
    $start_shift_time = RESOURCE_START_SHIFT_TIME;
    $end_shift_time = RESOURCE_END_SHIFT_TIME;

    $start_shift = strtotime($start_shift_time);
    $end_shift = strtotime($end_shift_time);

    //echo "end".$end_shift_time;
    /*echo "start".$start_shift_time;
    echo "<br>End".$end_shift_time;  */

    //date_default_timezone_set("America/Los_Angeles");
    //$total_time = work_hours_diff($start_shift ,$end_shift );
    $total_work_time = $end_shift - $start_shift;

    $total_work_time = $total_work_time / (60 * 60);
    $working_time = $total_hours_of_res;

    $start_time = strtotime(date('Y-m-d H:i:s'));
    $remaining_time = ($end_shift - $start_time) / (60 * 60);
    $remaining_time = number_format((float) $remaining_time, 2, '.', '');

    ///echo "<br>rem".$remaining_time;
    $completd_date = calculate_next_date($remaining_time, $working_time, $start_time, $total_work_time, $start_shift_time);

    //echo "complted date".$completd_date; die;
    $saveresource_sch['resource_id'] = $resource_id;
    $saveresource_sch['process_id'] = $process_id;
    $saveresource_sch['sp_id'] = $sp_id;
    $saveresource_sch['rp_id'] = $request_id;
    $saveresource_sch['job_card_number'] = $job_card_number;
    $saveresource_sch['p_qty'] = $stock_qty;
    $saveresource_sch['pen_qty'] = $stock_qty;
    $saveresource_sch['total_hour'] = $total_time_in_hours;
    $saveresource_sch['expected_start_date'] = $start_date;
    $saveresource_sch['expected_end_date'] = $last_date;
    $saveresource_sch['work_status'] = 0;

    $saveresource_sch['p_product_id'] = $product_id;
    $saveresource_sch['pr_process_type'] = $pr_process_type;
    $saveresource_sch['process_unit'] = $process_unit;
    $saveresource_sch['user_id'] = $_SESSION['user_id'];
    $saveresource_sch['cdate'] = date('Y-m-d H:i:s');
    $saveresource_sch['company_id'] = $_SESSION['company_id'];

    //echo "<pre>"; print_r($saveresource_sch);


    add_record('tbl_resource_schedule', $saveresource_sch, $dbcon, $allocate_process_branch_id);
}

function get_current_godown_stock_new($dbcon, $pro_id, $unit_id, $godown_id, $branch_id, $batch_id, $customer_id)
{
    if (!empty($branch_id)) {
        $branch_whre = " and qc.branch_id=" . $branch_id;
    }

    if (!empty($batch_id)) {

        $batch_whre = " and qc.stock_id in(" . $batch_id . ")";
        $batch_p_whre = " and qc.perent_id in(" . $batch_id . ")";

    }
    $whr = '';
    if ($customer_id != "" && $customer_id > 0) {
        $whr .= ' and qc.customer_id = "' . $customer_id . '" ';
    } else {
        $whr .= ' and qc.customer_id = 0 and qc.customer_id = "" ';
    }

    if ($godown_id != "" && $godown_id != 0) {
        $whr .= ' and qc.godown_id="' . $godown_id . '"';
    }

    $query = 'SELECT pro.product_id,IFNULL(base_stock_add,0) as base_stock_add,IFNULL(base_stock_minus,0) as base_stock_minus, IFNULL(con_stock_minus,0) as con_stock_minus, IFNULL(con_stock_add,0) as con_stock_add FROM `product_mst` as pro 

    left join (select IFNULL(sum(qc.base_stock),0) as base_stock_add,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status  !=2 and stock_flage=1  and  qc.base_unit=' . $unit_id . ' ' . $branch_whre . ' ' . $whr . ' ' . $batch_whre . ' and qc.company_id=' . $_SESSION['company_id'] . ' 
      group by qc.product_id) as qc on qc.product_id=pro.product_id

      left join (select IFNULL(sum(qc.base_stock),0) as base_stock_minus,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status  !=2 and stock_flage=2  and qc.base_unit=' . $unit_id . ' ' . $branch_whre . ' ' . $whr . ' ' . $batch_p_whre . '  and qc.company_id=' . $_SESSION['company_id'] . ' 
      group by qc.product_id) as qc1 on qc1.product_id=pro.product_id

      left join (select IFNULL(sum(qc.convert_stock),0) as con_stock_add,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status  !=2 and stock_flage=1  and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' ' . $branch_whre . ' ' . $whr . ' ' . $batch_whre . '  and qc.company_id=' . $_SESSION['company_id'] . ' 
      group by qc.product_id) as qc2 on qc2.product_id=pro.product_id

      left join (select IFNULL(sum(qc.convert_stock),0) as con_stock_minus,qc.product_id from tbl_stock_trn as qc 
      where qc.stock_status  !=2 and stock_flage=2  and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' ' . $branch_whre . ' ' . $whr . ' ' . $batch_p_whre . ' and qc.company_id=' . $_SESSION['company_id'] . ' 
      group by qc.product_id) as qc3 on qc3.product_id=pro.product_id

      where pro.product_id=' . $pro_id;

    $rows = brp_mysqli_fetch_assoc($dbcon->query($query));
    $stock = (floatval($rows['base_stock_add']) + floatval($rows['con_stock_add'])) - (floatval($rows['base_stock_minus']) + floatval($rows['con_stock_minus']));

    return floatval($stock);
    //return floatval($pro_id);

}
function get_current_stock_new($dbcon, $pro_id, $unit_id, $branch_id = "", $customer_id = "")
{
    $whr = "";
    if ($branch_id != "") {
        $whr = " and branch_id = " . $branch_id;
    }

    if ($customer_id != "" && $customer_id > 0) {
        $whr .= ' and qc.customer_id = "' . $customer_id . '" ';
    } else {
        $whr .= ' and qc.customer_id = 0 and qc.customer_id = "" ';
    }

    $query = 'SELECT pro.product_id,IFNULL(base_stock_add,0) as base_stock_add,IFNULL(base_stock_minus,0) as base_stock_minus,IFNULL(con_stock_minus,0) as con_stock_minus,IFNULL(con_stock_add,0) as con_stock_add FROM `product_mst` as pro 

    left join (select sum(qc.base_stock) as base_stock_add,qc.product_id from tbl_stock_trn as qc 
       where qc.stock_status!=2 and stock_flage=1  and qc.base_unit=' . $unit_id . $whr . ' and qc.company_id=' . $_SESSION['company_id'] . ' 
       group by qc.product_id) as qc on qc.product_id=pro.product_id

       left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id from tbl_stock_trn as qc 
       where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=' . $unit_id . $whr . ' and qc.company_id=' . $_SESSION['company_id'] . ' 
       group by qc.product_id) as qc1 on qc1.product_id=pro.product_id

       left join (select sum(qc.convert_stock) as con_stock_add,qc.product_id from tbl_stock_trn as qc 
       where qc.stock_status!=2 and stock_flage=1  and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
       group by qc.product_id) as qc2 on qc2.product_id=pro.product_id

       left join (select sum(qc.convert_stock) as con_stock_minus,qc.product_id from tbl_stock_trn as qc 
       where qc.stock_status!=2 and stock_flage=2  and qc.base_unit!=qc.convert_unit and qc.convert_unit=' . $unit_id . ' and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 

       group by qc.product_id) as qc3 on qc3.product_id=pro.product_id

       where pro.product_id=' . $pro_id;

    $rows = brp_mysqli_fetch_assoc($dbcon->query($query));

    $stock_add = floatval($rows['base_stock_add']) + floatval($rows['con_stock_add']);
    $stock_minus = floatval($rows['base_stock_minus']) + floatval($rows['con_stock_minus']);

    $stock = $stock_add - $stock_minus;

    return floatval($stock);
}

function get_current_process_stock_new($dbcon, $pro_id, $process_id, $unit_id, $branch_id = "", $godown_id = "", $process_stock_id = "")
{
    $whr = "";
    if ($branch_id != "") {
        $whr .= " and branch_id = " . $branch_id;
    }
    if ($godown_id != "") {
        $whr .= " and godown_id = " . $godown_id;
    }
    if ($branch_id != "") {
        $whr .= " and process_stock_id in(" . $process_stock_id . ")";
    }
    $query = 'SELECT pro.product_id,base_stock_add,base_stock_minus,con_stock_minus,con_stock_add FROM `product_mst` as pro 

    left join (select sum(qc.base_stock) as base_stock_add,qc.product_id,qc.process_id from tbl_process_stock_trn as qc 
        where qc.stock_status != 2 and stock_flage=1 and qc.process_id = ' . $process_id . ' and qc.base_unit=' . $unit_id . $whr . ' and qc.company_id=' . $_SESSION['company_id'] . ' 
        group by qc.product_id,qc.process_id) as qc on qc.product_id=pro.product_id

        left join (select sum(qc.base_stock) as base_stock_minus,qc.product_id,qc.process_id from tbl_process_stock_trn as qc 
        where qc.stock_status != 2 and stock_flage=2 and qc.process_id = ' . $process_id . ' and qc.base_unit=' . $unit_id . $whr . ' and qc.company_id=' . $_SESSION['company_id'] . ' 
        group by qc.product_id,qc.process_id) as qc1 on qc1.product_id=pro.product_id

        left join (select sum(qc.conv_stock) as con_stock_add,qc.product_id from tbl_process_stock_trn as qc 
        where qc.stock_status != 2 and stock_flage=1 and qc.process_id = ' . $process_id . ' and qc.base_unit!=qc.conv_unit and qc.conv_unit=' . $unit_id . ' and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
        group by qc.product_id) as qc2 on qc2.product_id=pro.product_id

        left join (select sum(qc.conv_stock) as con_stock_minus,qc.product_id from tbl_process_stock_trn as qc 
        where qc.stock_status != 2 and stock_flage=2 and qc.process_id = ' . $process_id . ' and qc.base_unit!=qc.conv_unit and qc.conv_unit=' . $unit_id . ' and qc.company_id=' . $_SESSION['company_id'] . $whr . ' 
        group by qc.product_id) as qc3 on qc3.product_id=pro.product_id

        where pro.product_id=' . $pro_id;

    $rows = brp_mysqli_fetch_assoc($dbcon->query($query));
    //echo "<pre>"; print_r($rows);
    $stock = ($rows['base_stock_add'] + $rows['con_stock_add']) - ($rows['base_stock_minus'] + $rows['con_stock_minus']);

    //$stock=($row['base_stock_add']+$row['con_stock_add'])-($row['base_stock_minus']+$row['con_stock_minus']);


    return floatval($stock);
    //return $query;

}

function convert_stock($dbcon, $stock, $product_id, $type)
{
    //var_dump($stock);
    $que_po = "select product_base_unit,product_conv_unit,product_conv_qty,product_base_qty from product_mst where product_id=" . $product_id;
    $resi_grn = $dbcon->query($que_po);
    $re = brp_mysqli_fetch_assoc($resi_grn);
    if ($re['product_base_unit'] != $re['product_conv_unit']) {
        if ($type == "base_unit") {
            $ret_qty = ($stock / $re['product_conv_qty']) * $re['product_base_qty'];
        } else {
            $ret_qty = ($stock / $re['product_base_qty']) * $re['product_conv_qty'];
        }
    } else {
        $ret_qty = $stock;
    }
    return $ret_qty;
    //return $stock." ".$re['product_base_qty']." ".$re['product_conv_qty']." ".$type;
    //return $type;

}
function update_grn_status($dbcon, $po_id)
{
    $que_po = "select * from tbl_potrancation where po_id=" . $po_id;
    $resi = $dbcon->query($que_po);
    while ($re_po = mysqli_fetch_assoc($resi)) {
        if ($re_po['grn_id'] != "0") {
            $que_grn = "select grn.grn_id,gtrn.product_qty,grn.grn_no,grn.grn_date,pro.product_name,led.l_name,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty from tbl_grn as grn where grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type=2 and grn.grn_id=" . $re_po['grn_id'] . " having gtrn.product_qty > used_qty";
            $resi_grn = $dbcon->query($que_grn);
            $re_grn = mysqli_fetch_assoc($resi_grn);
            if (!empty($re_grn['grn_id'])) {
                $info['purchase_status'] = 0;
                $updateid = update_record('tbl_grn', $info, "grn_id=" . $re_grn['grn_id'], $dbcon);
            } else {
                $info['purchase_status'] = 1;
                $updateid = update_record('tbl_grn', $info, "grn_id=" . $re_po['grn_id'], $dbcon);
            }
        }
    }
}
function find_leat_vender($dbcon, $product_id)
{
    $today_date = date('Y-m-d');
    $que_po = "select min(price) as mrate from tbl_purchasecardtrn as cardtrn
        left join tbl_product_party_purchase as pcard on 	pcard.party_purchase_id = cardtrn.party_purchase_id
        where pcard.card_status=0 and cardtrn.purchasecardtrn_status=0 and cardtrn.valid_date>='" . $today_date . "' and cardtrn.affected_date<='" . $today_date . "' and pcard.is_aproove=1 and pcard.is_active=0 and cardtrn.product_id=" . $product_id;
    $resi = $dbcon->query($que_po);
    $re_po = mysqli_fetch_assoc($resi);

    $que1 = "select * from tbl_purchasecardtrn as cardtrn
        left join tbl_product_party_purchase as pcard on 	pcard.party_purchase_id = cardtrn.party_purchase_id
        where pcard.card_status=0 and cardtrn.purchasecardtrn_status=0 and cardtrn.valid_date>='" . $today_date . "' and cardtrn.affected_date<='" . $today_date . "' and pcard.is_aproove=1 and pcard.is_active=0 and cardtrn.product_id=" . $product_id . " and price=" . $re_po['mrate'];
    $row1 = $dbcon->query($que1);
    $row = mysqli_fetch_assoc($row1);

    return $row['vendor_id'];
    //return 1;

}
function delete_po_req_status($dbcon, $po_trn_id)
{
    $que_po = "select * from tbl_purchaseordertrn where temptrn_ref_id!='' and purchaseordertrn_id=" . $po_trn_id;
    $resi = $dbcon->query($que_po);
    while ($re_po = mysqli_fetch_assoc($resi)) {
        $infos1['purchaseordertrn_req_status'] = 2;
        update_record('tbl_purchaseorder_req_trn', $infos1, "purchaseordertrn_id=" . $re_po['purchaseordertrn_id'], $dbcon);

        $infos2['po_trn_req_status'] = 0;
        update_record('tbl_purchasetrntemp', $infos2, "purchaseordertrn_id in (" . $re_po['temptrn_ref_id'] . ")", $dbcon);
    }
}
function update_poreq_status_edit($dbcon, $po_trn_id, $branch_id = '')
{
    $que_po = "select * from tbl_purchaseordertrn where temptrn_ref_id!='' and purchaseordertrn_id=" . $po_trn_id;
    $resi = $dbcon->query($que_po);
    while ($re_po = mysqli_fetch_assoc($resi)) {
        $infos1['purchaseordertrn_req_status'] = 2;
        update_record('tbl_purchaseorder_req_trn', $infos1, "purchaseordertrn_id=" . $re_po['purchaseordertrn_id'], $dbcon);

        $infos2['po_trn_req_status'] = 0;
        update_record('tbl_purchasetrntemp', $infos2, "purchaseordertrn_id in (" . $re_po['temptrn_ref_id'] . ")", $dbcon);

        $used_qty = $re_po['product_conv_qty'];
        $used_base_qty = $re_po['product_qty'];

        $que_sub = "select * from tbl_purchasetrntemp where purchaseordertrn_id in (" . $re_po['temptrn_ref_id'] . ")";
        $resub = $dbcon->query($que_sub);
        while ($re_sub = mysqli_fetch_assoc($resub)) {
            $query_p = "select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn where purchaseordertrn_req_status=0 and req_id=" . $re_sub['purchaseordertrn_id'];
            $rels = mysqli_fetch_assoc($dbcon->query($query_p));
            $pending_qty = $re_sub['product_qty'] - $rels['used_qty'];
            $pending_base_qty = $re_sub['product_base_qty'] - $rels['used_base_qty'];
            /*if ((round_up($used_qty, 5) > 0) || (round_up($used_base_qty, 5) > 0))
            {
                if ((!empty(round_up($used_qty, 5))) || (!empty(round_up($used_base_qty, 5))))
                {

                    if ((round_up($pending_qty, 5) > 0) || (round_up($pending_base_qty, 5) > 0))
                    {
                        if ((round_up($pending_qty, 5) <= round_up($used_qty, 5)) || (round_up($pending_base_qty, 5) <= round_up($used_base_qty, 5)) )
                        {*/
            if (round_up($used_base_qty, 5) > 0) {
                if (!empty(round_up($used_base_qty, 5))) {
                    if (round_up($pending_base_qty, 5) > 0) {
                        if (round_up($pending_base_qty, 5) <= round_up($used_base_qty, 5)) {
                            $info1['req_id'] = $re_sub['purchaseordertrn_id'];
                            $info1['purchaseordertrn_id'] = $re_po['purchaseordertrn_id'];
                            $info1['rp_id'] = $re_sub['po_ref_id'];
                            $info1['used_qty'] = round_up($pending_qty, 5);
                            $info1['used_base_qty'] = round_up($pending_base_qty, 5);
                            $info1['base_unit'] = $re_sub['base_unit_id'];
                            $info1['conv_unit'] = $re_sub['unit_id'];
                            $info1['user_id'] = $_SESSION['user_id'];
                            $info1['company_id'] = $_SESSION['company_id'];
                            $ins_id = add_record('tbl_purchaseorder_req_trn', $info1, $dbcon, $re_sub['branch_id']);
                            $used_qty = $used_qty - $pending_qty;

                            $infos['po_trn_req_status'] = 1;
                            update_record('tbl_purchasetrntemp', $infos, "purchaseordertrn_id=" . $re_sub['purchaseordertrn_id'], $dbcon);
                        } else {
                            $info1['req_id'] = $re_sub['purchaseordertrn_id'];
                            $info1['purchaseordertrn_id'] = $re_po['purchaseordertrn_id'];
                            $info1['rp_id'] = $re_sub['po_ref_id'];
                            $info1['used_qty'] = round_up($used_qty, 5);
                            $info1['used_base_qty'] = round_up($pending_base_qty, 5);
                            $info1['base_unit'] = $re_sub['base_unit_id'];
                            $info1['conv_unit'] = $re_sub['unit_id'];
                            $info1['user_id'] = $_SESSION['user_id'];
                            $info1['company_id'] = $_SESSION['company_id'];
                            $ins_id = add_record('tbl_purchaseorder_req_trn', $info1, $dbcon, $re_sub['branch_id']);
                            $used_qty = $used_qty - $pending_qty;
                        }
                    }
                }
            }
        }
    }
}
function update_poreq_status($dbcon, $po_id, $prev_purchaseorder_id, $vender_id)
{
    if (!empty($prev_purchaseorder_id)) {
        $query_pre = "select * from tbl_purchaseordertrn where purchaseorder_id=" . $prev_purchaseorder_id;
        $resi_pre = $dbcon->query($query_pre);
        while ($pre_po = brp_mysqli_fetch_array($resi_pre)) {
            $inf['purchaseordertrn_req_status'] = 2;
            update_record('tbl_purchaseorder_req_trn', $inf, "purchaseordertrn_id=" . $pre_po['purchaseordertrn_id'], $dbcon);
        }
    }

    $que_po = "select * from tbl_purchaseordertrn where temptrn_ref_id!='' and purchaseorder_id=" . $po_id;
    $resi = $dbcon->query($que_po);
    while ($re_po = brp_mysqli_fetch_array($resi)) {
        $infos1['purchaseordertrn_req_status'] = 2;
        update_record('tbl_purchaseorder_req_trn', $infos1, "purchaseordertrn_id=" . $re_po['purchaseordertrn_id'], $dbcon);

        $infos2['po_trn_req_status'] = 0;
        update_record('tbl_purchasetrntemp', $infos2, "purchaseordertrn_id in (" . $re_po['temptrn_ref_id'] . ")", $dbcon);

        $used_qty = $re_po['product_conv_qty'];
        $used_base_qty = $re_po['product_qty'];

        $que_sub = "select * from tbl_purchasetrntemp where purchaseordertrn_id in (" . $re_po['temptrn_ref_id'] . ")";

        $resub = $dbcon->query($que_sub);
        while ($re_sub = brp_mysqli_fetch_array($resub)) {
            $query_p = "select sum(used_qty) as used_qty,sum(used_base_qty) as used_base_qty from tbl_purchaseorder_req_trn where purchaseordertrn_req_status=0 and req_id=" . $re_sub['purchaseordertrn_id'];

            $rels = brp_mysqli_fetch_array($dbcon->query($query_p));


            $pending_qty = $re_sub['product_qty'] - $rels['used_qty'];
            settype($pending_qty, "float");
            $pending_base_qty = $re_sub['product_base_qty'] - $rels['used_base_qty'];
            settype($pending_base_qty, "float");


            /* if ((round_up($used_qty, 5) > 0) || (round_up($used_base_qty, 5) > 0))
             {
                 if ((round_up($pending_qty, 5) > 0) || (round_up($pending_base_qty, 5) > 0))
                 {
                     if ((round_up($pending_qty, 5) <= round_up($used_qty, 5)) || (round_up($pending_base_qty, 5) <= round_up($used_base_qty, 5)))
                     {*/
            if (round_up($used_base_qty, 5) > 0) {
                if (round_up($pending_base_qty, 5) > 0) {
                    if (round_up($pending_base_qty, 5) <= round_up($used_base_qty, 5)) {
                        $info1['req_id'] = $re_sub['purchaseordertrn_id'];
                        $info1['purchaseordertrn_id'] = $re_po['purchaseordertrn_id'];
                        $info1['rp_id'] = $re_sub['po_ref_id'];
                        $info1['vender_id'] = $vender_id;
                        $info1['used_qty'] = round_up($pending_qty, 5);
                        $info1['used_base_qty'] = round_up($pending_base_qty, 5);
                        $info1['base_unit'] = $re_sub['base_unit_id'];
                        $info1['conv_unit'] = $re_sub['unit_id'];
                        $info1['user_id'] = $_SESSION['user_id'];
                        $info1['company_id'] = $_SESSION['company_id'];

                        $ins_id = add_record('tbl_purchaseorder_req_trn', $info1, $dbcon, $re_sub['branch_id']);
                        $used_qty = $used_qty - $pending_qty;
                        $used_base_qty = $used_base_qty - $pending_base_qty;

                        $infos['po_trn_req_status'] = 1;
                        update_record('tbl_purchasetrntemp', $infos, "purchaseordertrn_id=" . $re_sub['purchaseordertrn_id'], $dbcon);
                    } else {
                        $info1['req_id'] = $re_sub['purchaseordertrn_id'];
                        $info1['purchaseordertrn_id'] = $re_po['purchaseordertrn_id'];
                        $info1['rp_id'] = $re_sub['po_ref_id'];
                        $info1['vender_id'] = $vender_id;
                        $info1['used_qty'] = round_up($used_qty, 5);
                        $info1['used_base_qty'] = round_up($used_base_qty, 5);
                        $info1['base_unit'] = $re_sub['base_unit_id'];
                        $info1['conv_unit'] = $re_sub['unit_id'];
                        $info1['user_id'] = $_SESSION['user_id'];
                        $info1['company_id'] = $_SESSION['company_id'];

                        $ins_id = add_record('tbl_purchaseorder_req_trn', $info1, $dbcon, $re_sub['branch_id']);
                        $used_qty = $used_qty - $pending_qty;
                        $used_base_qty = $used_base_qty - $pending_base_qty;
                    }
                }
            }
        }
    }
}

function load_led_no($dbcon, $type, $ref_id)
{
    if ($type == "tbl_payment") {
        $qry1 = "select * from tbl_receipt as cert 
            where receipt_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        //$ret=$re['receipt_no'];
        $type_label = ($re['payment_type'] == '2') ? 'Payment No : ' : 'Recipt No : ';

        //Amish Soni 15-09-2020

        $remark = ($re['payment_type'] == '2') ? ' (' . $re['payment_remark'] . ') ' : '';
        $ret = $type_label . $re['receipt_no'] . $remark;
    } else if ($type == "account_voucher_trn") {
        $qry1 = "select * from account_voucher_trn as cert 
						left join account_voucher_mst as jou on jou.voucher_mstid=cert.voucher_mstid
						where voucher_trnid=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['voucher_no'];
    } else if ($type == "tbl_invoice") {
        $qry1 = "select * from tbl_invoice as cert where invoice_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['invoice_no'] . '=' . $re["invoice_id"];
    } else if ($type == "tbl_pono") {
        $qry1 = "select * from tbl_pono as cert 
						where po_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['po_no'] . '=' . $re["po_id"];
    } else if ($type == "tbl_debitnote") {
        $qry1 = "SELECT * FROM `tbl_debitnote` 
						where debitnote_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['debitnote_no'] . '=' . $re["debitnote_id"];
    } else if ($type == "tbl_sale_return") {
        $qry1 = "SELECT * FROM tbl_sale_return  
						where sale_return_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['sal_return_voucher_no'] . '=' . $re["sale_return_id"];
    } else if ($type == "tbl_receipt") {
        $qry1 = "SELECT * FROM `tbl_receipt` 
						where receipt_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['receipt_no'] . '=' . $re["receipt_id"] . '=' . $re["payment_type"];
    } else if ($type == "tbl_receipt_payment_trn") {
        $qry1 = "SELECT r.* FROM tbl_receipt as r
						left join tbl_receipt_payment_trn as rpt on r.receipt_id=rpt.receipt_id
						where rpt.receipt_payment_trn_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['receipt_no'] . '=' . $re["receipt_id"] . '=' . $re["payment_type"];
    } else if ($type == "tbl_bill_sundry_transaction") {
        $qry2 = "SELECT * FROM `tbl_bill_sundry_transaction` WHERE `sundry_id`=" . $ref_id;

        $ro1 = $dbcon->query($qry2);
        $re1 = brp_mysqli_fetch_assoc($ro1);

        if ($re1['sundry_voucher_table'] == "tbl_invoice") {
            $qry1 = "SELECT cert.*,tl.l_name as customer_name from tbl_invoice as cert left join tbl_ledger as tl on cert.cust_id = tl.l_id  where invoice_id=" . $re1['sundry_voucher_id'];
            //echo $qry1;
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
            //echo '<pre>';print_r($re);
            $ret = $re['invoice_no'] . '=' . $re["invoice_id"] . '=tbl_invoice' . '=' . $re["customer_name"];
            $type_label = ($re['payment_type'] == '2') ? 'Payment No : ' : 'Recipt No : ';

            $remark = ($re['payment_type'] == '2') ? ' (' . $re['payment_remark'] . ') ' : '';
            $ret = $type_label . $re['receipt_no'] . $remark;
        }
        /* else if ($type == "account_voucher_trn")
        {
            $qry1 = "select * from account_voucher_trn as cert 
            left join account_voucher_mst as jou on jou.voucher_mstid=cert.voucher_mstid
            where voucher_trnid=" . $ref_id;
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
            $ret = $re['voucher_no'];
        } */
    } else if ($type == "tbl_invoice") {
        $qry1 = "select * from tbl_invoice as cert 
            where invoice_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['invoice_no'] . '=' . $re["invoice_id"];

    } else if ($type == "tbl_pono") {

        /* $qry1 = "select * from tbl_pono as cert where po_id=" . $re1['sundry_voucher_id']; */
        //echo $qry1;

        $qry1 = "select * from tbl_pono as cert 
           where po_id=" . $ref_id;

        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['po_no'] . '=' . $re["po_id"];
    } else if ($type == "tbl_debitnote") {
        $qry1 = "SELECT * FROM `tbl_debitnote` 
            where debitnote_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['debitnote_no'] . '=' . $re["debitnote_id"];
    } else if ($type == "tbl_sale_return") {
        $qry1 = "SELECT * FROM tbl_sale_return  
            where sale_return_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['sal_return_voucher_no'] . '=' . $re["sale_return_id"];
    } else if ($type == "tbl_receipt") {
        $qry1 = "SELECT * FROM `tbl_receipt` 
            where receipt_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['receipt_no'] . '=' . $re["receipt_id"] . '=' . $re["payment_type"];
    } else if ($type == "tbl_receipt_payment_trn") {
        $qry1 = "SELECT r.* FROM tbl_receipt as r
            left join tbl_receipt_payment_trn as rpt on r.receipt_id=rpt.receipt_id
            where rpt.receipt_payment_trn_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['receipt_no'] . '=' . $re["receipt_id"] . '=' . $re["payment_type"];
    } else if ($type == "tbl_bill_sundry_transaction") {
        $qry2 = "SELECT * FROM `tbl_bill_sundry_transaction` WHERE `sundry_id`=" . $ref_id;
        $ro1 = $dbcon->query($qry2);
        $re1 = brp_mysqli_fetch_assoc($ro1);

        if ($re1['sundry_voucher_table'] == "tbl_invoice") {
            $qry1 = "select * from tbl_invoice as cert 
                where invoice_id=" . $re1['sundry_voucher_id'];
            //echo $qry1;
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
            //echo '<pre>';print_r($re);
            $ret = $re['invoice_no'] . '=' . $re["invoice_id"] . "=tbl_invoice";
        } else if ($re1['sundry_voucher_table'] == "tbl_pono") {
            $qry1 = "select * from tbl_pono as cert 
                where po_id=" . $re1['sundry_voucher_id'];
            //echo $qry1;
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
            $ret = $re['po_no'] . '=' . $re["po_id"] . "=tbl_pono";
        } else if ($re1['sundry_voucher_table'] == "tbl_debitnote") {
            $qry1 = "SELECT * FROM `tbl_debitnote` 
                where debitnote_id=" . $re1['sundry_voucher_id'];
            //echo $qry1;
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
            $ret = $re['debitnote_no'] . '=' . $re["debitnote_id"] . "=tbl_debitnote";
        } else if ($re1['sundry_voucher_table'] == "tbl_sale_return") {
            $qry1 = "SELECT * FROM `tbl_sale_return` 
                where sale_return_id=" . $re1['sundry_voucher_id'];
            //echo $qry1;
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
            $ret = $re['sal_return_voucher_no'] . '=' . $re["sale_return_id"] . "=tbl_sale_return";
        } else {
            $ret = '';
        }

        //$ret=$re['receipt_no'].'='.$re["receipt_id"].'='.$re["payment_type"];

    } else if ($type == "tbl_expense_detail") {
        $qry1 = "select com.complaint_no,cert.remark from tbl_expense_detail as cert 
            left join tbl_complaint as com on com.complaint_id=cert.expense_complain
            where ex_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re['complaint_no'] . '(' . $re['remark'] . ')';
    } else if ($type == "tbl_expense_detail_account") {
        $qry1 = "select com.complaint_no,cert.remark,led.l_name from tbl_expense_detail as cert 
            left join tbl_complaint as com on com.complaint_id=cert.expense_complain
            left join tbl_ledger as led on led.l_id=cert.emp_id
            where ex_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re["complaint_no"] . '=' . $re["l_name"] . '-(' . $re["remark"] . ')';
    } else if ($type == "tbl_journal_trn") {
        $qry1 = "SELECT j.journal_id,j.journal_no FROM tbl_journal as j
            left join tbl_journal_trn as jt on j.journal_id=jt.journal_id
            where jt.journal_trn_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re["journal_no"] . '=' . $re["journal_id"];
    } else if ($type == "tbl_contra_trn") {
        $qry1 = "SELECT c.contra_id,c.contra_no FROM tbl_contra as c
            left join tbl_contra_trn as ct on c.contra_id=ct.contra_id
            where ct.contra_trn_id=" . $ref_id;
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);
        $ret = $re["contra_no"] . '=' . $re["contra_id"];
    }
    return $ret;
}

//created by dhruv on 21-12-2021
function get_tds_details($dbcon, $type, $ref_id)
{
    if ($type == "tbl_bill_sundry_transaction") {
        $qry_1 = "SELECT sundry_voucher_table,sundry_voucher_id  FROM `tbl_bill_sundry_transaction` WHERE `sundry_id` = " . $ref_id . " ";

        //echo $qry_1;exit;
        $ro_1 = $dbcon->query($qry_1);
        $re_1 = brp_mysqli_fetch_assoc($ro_1);

        if ($re_1['sundry_voucher_table'] == 'tbl_pono') {
            $qry1 = "SELECT tds_per as tdstcs,SUM(tp.total) as total,tp.po_no as refno,tl.l_name,tp.order_no as pbillno,tl.m_pan as ppan FROM `tbl_pono` as tp left join tbl_potrancation as tpt on tpt.po_id=tp.po_id
                    left join tbl_ledger as tl on tp.vender_id=tl.l_id
                where tp.po_id=" . $re_1['sundry_voucher_id'] . " group by tpt.po_id ";
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
        } else if ($re_1['sundry_voucher_table'] == 'tbl_debitnote') {
            $qry1 = "SELECT tds_per as tdstcs,SUM(tdt.purchase_return_total_amount) as total,td.debitnote_no as refno,tl.l_name FROM `tbl_debitnote` as td left join tbl_debitnote_trn as tdt on tdt.debitnote_id=td.debitnote_id 
                left join tbl_ledger as tl on td.vender_id=tl.l_id
                where td.debitnote_id=" . $re_1['sundry_voucher_id'] . " group by tdt.debitnote_id  ";
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
        } else if ($re_1['sundry_voucher_table'] == 'tbl_invoice') {
            $qry1 = "SELECT tcs_per as tdstcs,SUM(tpt.total) as total,tp.invoice_no as refno,tl.l_name FROM `tbl_invoice` as tp left join tbl_invoicetrn as tpt on tpt.invoice_id=tp.invoice_id
                left join tbl_ledger as tl on tp.cust_id=tl.l_id
                where tp.invoice_id=" . $re_1['sundry_voucher_id'] . " group by tpt.invoice_id ";
            $ro = $dbcon->query($qry1);
            $re = brp_mysqli_fetch_assoc($ro);
        }

        $ret = $re['tdstcs'] . '=' . $re['total'] . '=' . $re['refno'] . '=' . $re['l_name'] . '=' . $re['pbillno'] . '=' . $re['ppan'];
    } else if ($type == "tbl_receipt_payment_trn") {
        $qry_1 = "SELECT ref_id,tds_per FROM `tbl_receipt_payment_trn` WHERE `receipt_payment_trn_id` = " . $ref_id . " ";
        $ro_1 = $dbcon->query($qry_1);
        $re_1 = brp_mysqli_fetch_assoc($ro_1);

        $qry1 = "SELECT rp.amount as total,tl.l_name,r.receipt_no FROM `tbl_receipt_payment_trn` as rp left join tbl_receipt as r on r.receipt_id=rp.receipt_id 
            left join tbl_ledger as tl on rp.ledger_id=tl.l_id 
            WHERE rp.receipt_payment_trn_id = " . $re_1['ref_id'] . " ";
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);

        $ret = $re_1["tds_per"] . '=' . $re["total"] . '=' . $re['receipt_no'] . '=' . $re['l_name'];
    } else if ($type == "tbl_journal_trn") {
        $qry_1 = "SELECT ref_id,tds_per FROM `tbl_journal_trn` 
            WHERE `journal_trn_id` = " . $ref_id . " ";
        $ro_1 = $dbcon->query($qry_1);
        $re_1 = brp_mysqli_fetch_assoc($ro_1);

        $qry1 = "SELECT rp.amount as total,tl.l_name,r.journal_no FROM `tbl_journal_trn` as rp left join tbl_journal as r on r.journal_id=rp.journal_id 
            left join tbl_ledger as tl on rp.ledger_id=tl.l_id 
            WHERE rp.journal_trn_id = " . $re_1['ref_id'] . " ";
        $ro = $dbcon->query($qry1);
        $re = brp_mysqli_fetch_assoc($ro);

        $ret = $re_1["tds_per"] . '=' . $re["total"] . '=' . $re['journal_no'] . '=' . $re['l_name'];
    }
    return $ret;
}

//Created by dhruv for cashflow by group
function getInflowLedgerGourpTotal($dbcon, $tablename, $tableid)
{

    if ($tablename == "tbl_receipt") {
        $qry_1 = "SELECT tg.g_name,((rpt.amount) - (SELECT IFNULL(SUM(rpt.amount),0) FROM `tbl_receipt_payment_trn` as rpt left join tbl_ledger as tl on tl.l_id=rpt.ledger_id left join tbl_group as tg on tl.l_group=tg.g_id WHERE rpt.`receipt_id` = " . $tableid . " and rpt.isdelete=0 and rpt.entry_type=1)) as amount FROM `tbl_receipt_payment_trn` as rpt left join tbl_ledger as tl on tl.l_id=rpt.ledger_id left join tbl_group as tg on tl.l_group=tg.g_id WHERE rpt.`receipt_id` = " . $tableid . " and rpt.entry_type=2 and rpt.isdelete=0  ";
        $ro_1 = $dbcon->query($qry_1);
        $re_1 = brp_mysqli_fetch_assoc($ro_1);
    } else if ($tablename == "tbl_journal_trn") {
        $qry_1 = "SELECT tg.g_name,jt.amount as amount  FROM `tbl_journal_trn` as jt left join tbl_ledger as tl on tl.l_id=jt.ledger_id left join tbl_group as tg on tl.l_group=tg.g_id WHERE jt.`journal_id` = (select journal_id from tbl_journal_trn where journal_trn_id=" . $tableid . ") and jt.entry_type=2 and jt.journal_trn_status=0 ";
        //echo $qry_1;
        $ro_1 = $dbcon->query($qry_1);
        $re_1 = brp_mysqli_fetch_assoc($ro_1);
    }

    return $re_1;
}

function getOutflowLedgerGourpTotal($dbcon, $tablename, $tableid)
{

    if ($tablename == "tbl_receipt") {
        $qry_1 = "SELECT tg.g_name,((rpt.amount) - (SELECT IFNULL(SUM(rpt.amount),0) FROM `tbl_receipt_payment_trn` as rpt left join tbl_ledger as tl on tl.l_id=rpt.ledger_id left join tbl_group as tg on tl.l_group=tg.g_id WHERE rpt.`receipt_id` = " . $tableid . " and rpt.isdelete=0 and rpt.entry_type=2)) as amount FROM `tbl_receipt_payment_trn` as rpt left join tbl_ledger as tl on tl.l_id=rpt.ledger_id left join tbl_group as tg on tl.l_group=tg.g_id WHERE rpt.`receipt_id` = " . $tableid . " and rpt.entry_type=1 and rpt.isdelete=0  ";
        $ro_1 = $dbcon->query($qry_1);
        $re_1 = brp_mysqli_fetch_assoc($ro_1);
    } else if ($tablename == "tbl_journal_trn") {
        $qry_1 = "SELECT tg.g_name,jt.amount as amount  FROM `tbl_journal_trn` as jt left join tbl_ledger as tl on tl.l_id=jt.ledger_id left join tbl_group as tg on tl.l_group=tg.g_id WHERE jt.`journal_id` = (select journal_id from tbl_journal_trn where journal_trn_id=" . $tableid . ") and jt.entry_type=1 and jt.journal_trn_status=0 ";
        //echo $qry_1;
        $ro_1 = $dbcon->query($qry_1);
        $re_1 = brp_mysqli_fetch_assoc($ro_1);
    }

    return $re_1;
}

//Created by dhruv for trial balance sheet
function get_ledger_dr_cr_amount($dbcon, $ledger_id, $start_date, $end_date)
{

    $query_opening = "select opn_balance as opening_balance,balance_typeid,debitamount,creditamount from tbl_ledger as cust 
                    left join 
                    (select sum(amount) as debitamount,invoice.ledger_id from tbl_general_book as invoice where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=2 and invoice.company_id=" . $_SESSION['company_id'] . " and ref_date <= " . $start_date . "  group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 

                    left join 
                    (select sum(amount) as creditamount,rec.ledger_id from tbl_general_book as rec 
                    where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=1 and company_id=" . $_SESSION['company_id'] . " and ref_date <= " . $start_date . "   group by rec.ledger_id) as creditcust on creditcust.ledger_id=cust.l_id 

                    where cust.l_id=" . $ledger_id;
    $rel_opening = mysqli_fetch_assoc($dbcon->query($query_opening));
    if ($rel_opening['balance_typeid'] == 2) {
        $openingbalance = $rel_opening['creditamount'] - $rel_opening['debitamount'] - $rel_opening['opening_balance'];
    } else {
        $openingbalance = $rel_opening['opening_balance'] + $rel_opening['creditamount'] - $rel_opening['debitamount'];
    }

    $query = "select opn_balance as opening_balance,balance_typeid,debitamount,creditamount from tbl_ledger as cust 
                    left join 
                    (select sum(amount) as debitamount,invoice.ledger_id from tbl_general_book as invoice where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=2 and invoice.company_id=" . $_SESSION['company_id'] . " and ref_date between '" . $start_date . "' and '" . $end_date . "'  group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 

                    left join 
                    (select sum(amount) as creditamount,rec.ledger_id from tbl_general_book as rec 
                    where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=1 and company_id=" . $_SESSION['company_id'] . " and ref_date between '" . $start_date . "' and '" . $end_date . "'  group by rec.ledger_id) as creditcust on creditcust.ledger_id=cust.l_id 

                    where cust.l_id=" . $ledger_id;

    $rel = mysqli_fetch_assoc($dbcon->query($query));

    $balance['creditamount'] = $rel['creditamount'];
    $balance['debitamount'] = $rel['debitamount'];
    $balance['openingbalance'] = $openingbalance;

    return $balance;
}

function get_ledger_amount($dbcon, $ledger_id, $end_date)
{

    $query = "select opn_balance as opening_balance,balance_typeid,debitamount,creditamount from tbl_ledger as cust 
                    left join 
                    (select sum(amount) as debitamount,invoice.ledger_id from tbl_general_book as invoice where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=2 and invoice.company_id=" . $_SESSION['company_id'] . " and ref_date <= '" . date('Y-m-d', strtotime($end_date)) . "'  group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 

                    left join 
                    (select sum(amount) as creditamount,rec.ledger_id from tbl_general_book as rec 
                    where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=1 and company_id=" . $_SESSION['company_id'] . " and ref_date <= '" . date('Y-m-d', strtotime($end_date)) . "'  group by rec.ledger_id) as creditcust on creditcust.ledger_id=cust.l_id 

                    where cust.l_id=" . $ledger_id;

    $rel = mysqli_fetch_assoc($dbcon->query($query));

    /* $op_balance=($rel['balance_typeid']=="2"?(-$rel['opening_balance']):$rel['opening_balance']);
    // $balance=$op_balance+$rel['debitamount']-($rel['creditamount']);
    $balance=($op_balance+$rel['creditamount'])-($rel['debitamount']);
    */
    $op_balance = ($rel['balance_typeid'] == "2" ? ($rel['opening_balance']) : -$rel['opening_balance']);
    $balance = $op_balance + $rel['debitamount'] - ($rel['creditamount']);
    return $balance;
}
function get_group_ledger_amount($dbcon, $group_id, $end_date)
{
    $query = "select debit_op,cradit_op,debitamount,creditamount from tbl_group as cust 
    left join 
    (select sum(opn_balance) as debit_op,led.l_group from tbl_ledger as led 
    where l_status=0 and balance_typeid=2 and led.company_id=" . $_SESSION['company_id'] . "  group by led.l_group) as debit_opening on debit_opening.l_group=cust.g_id 
    left join 
    (select sum(opn_balance) as cradit_op,led.l_group from tbl_ledger as led 
    where l_status=0 and balance_typeid=1 and led.company_id=" . $_SESSION['company_id'] . "  group by led.l_group) as credit_opening on credit_opening.l_group=cust.g_id 

    left join 
    (select sum(amount) as debitamount,lde.l_group from tbl_general_book as invoice 
    left join tbl_ledger as lde on lde.l_id=invoice.ledger_id
    where genral_book_status=0 and lde.l_status=0 and table_name!='tbl_ledger' and entry_type=2 and invoice.company_id=" . $_SESSION['company_id'] . " and ref_date <= '" . date('Y-m-d', strtotime($end_date)) . "'  group by lde.l_group) as debitinvoice on debitinvoice.l_group=cust.g_id 

    left join 
    (select sum(amount) as creditamount,lde.l_group from tbl_general_book as rec
    left join tbl_ledger as lde on lde.l_id=rec.ledger_id
    where rec.genral_book_status=0 and lde.l_status=0 and table_name!='tbl_ledger' and entry_type=1 and rec.company_id=" . $_SESSION['company_id'] . " and ref_date < '" . date('Y-m-d', strtotime($end_date)) . "'  group by lde.l_group) as creditcust on creditcust.l_group=cust.g_id 

    where cust.g_id=" . $group_id;

    $rel = mysqli_fetch_assoc($dbcon->query($query));

    //$op_balance=($rel['balance_typeid']=="2"?(-$rel['opening_balance']):$rel['opening_balance']);
    $balance = ($rel['creditamount'] + $rel['cradit_op']) - ($rel['debitamount'] + $rel['debit_op']);

    return $balance;
    //return $query;

}
function get_general_book_id($dbcon, $table_name, $table_id, $ledger_id)
{
    //pathik created
    $qry1 = "select general_book_id from tbl_general_book as cert where genral_book_status=0 and table_id=" . $table_id . " and table_name='" . $table_name . "' and ledger_id=" . $ledger_id . " ";
    $ro = $dbcon->query($qry1);
    $re = mysqli_fetch_assoc($ro);

    return $re['general_book_id'];
}
function get_ledger($dbcon, $ledger_id, $where)
{
    //add pathik
    $str = '';

    $query = "select * from tbl_ledger as pro where l_status=0 " . $where . " and company_id = $_SESSION[company_id] order by TRIM(l_name) ASC";
    $rs_dispatch = $dbcon->query($query);
    $str .= '<option value="" data-gid="">--select ledger--</option>';
    while ($rel = mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['l_id'] == $ledger_id) {
            $sel = "selected='selected'";
        }

        $str .= '<option ' . $sel . ' value="' . $rel['l_id'] . '" data-gid="' . $rel['l_group'] . '" data-formgroup="' . $rel['l_form'] . '" >' . $rel['l_name'] . '</option>';
    }
    return $str;
}
function group_ledger_amount($dbcon, $end_date, $group_id)
{
    $emp_id = getEmployeeIdUser($dbcon, $_SESSION['user_id']);
    // $s_date = explode(' - ', $postdata['rep_po_date']);

    $query = "select l.opn_balance,l.balance_typeid,exp_amt.exp_amount,pay_amt.pay_amount from tbl_ledger as l

    left join 

    ( select sum(paid_amount) as exp_amount,emp_id,expense_date from tbl_expense_detail where expense_approve_status!=2 and expense_status=0 and expense_date < '" . date('Y-m-d', strtotime($s_date[0])) . "' group by emp_id ) as exp_amt on exp_amt.emp_id=l.l_id

    left join 
    ( select sum(total_paid_amount) as pay_amount,cust_id,receipt_date from tbl_receipt where status=0 and  receipt_date < '" . date('Y-m-d', strtotime($s_date[0])) . "' group by cust_id ) as pay_amt on pay_amt.cust_id=l.l_id

    where l.l_id='$emp_id'
    ";
    $rel = mysqli_fetch_assoc($dbcon->query($query));

    $op_balance = ($rel['balance_typeid'] == "2" ? (-$rel['opn_balance']) : $rel['opn_balance']); //1credit,2debit
    $balance = ($op_balance + $rel['exp_amount']) - $rel['pay_amount'];
}
function expance_paid_amount($dbcon, $ex_id)
{
    //pathik genrated
    $qry = 'SELECT sum(total_amount) as pamount FROM tbl_receipt_trn as quot 
    WHERE status=0 and ex_id=' . $ex_id;
    $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));
    return $qry_rel['pamount'];

    //return $qry;

}
function common_log_entry($dbcon, $log_form, $log_mode, $ref_tbl, $ref_id)
{
    $cdate = date("Y-m-d H:i:s");
    $ins_qry = "INSERT INTO tbl_log_all(`log_form`, `log_mode`, `ref_tbl`, `ref_id`, `user_id`, `cdate`, `company_id`) VALUES ('" . $log_form . "','" . $log_mode . "','" . $ref_tbl . "','" . $ref_id . "','" . $_SESSION['user_id'] . "','" . $cdate . "','" . $_SESSION['company_id'] . "')";
    $ins_qry_rs = $dbcon->query($ins_qry);
}
function count_pend_po_upload($dbcon, $user_id)
{
    $where = '';
    $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND quot.branch_id = " . $_SESSION['branch_id'];
    if ($_SESSION['user_type'] != '2' && $_SESSION['user_type'] != '9') {
        $ser = trim(check_crm_find_in_set($dbcon, $_SESSION['user_id'], 0), ",");
        $where .= ' and inq.won_user_id IN (' . $ser . ')';
    }
    /*else
    {
        $ser = trim(check_crm_find_in_set($dbcon, $user_id, 0) , ",");
        $where .= ' and inq.won_user_id IN (' . $ser . ')';
    }*/

    /*$qry = 'SELECT COUNT(quot.quotation_id) as ttl_pen_po FROM tbl_quotation as quot left join tbl_customer as cust on cust.cust_id=quot.cust_id left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id left join users as usr on usr.user_id=quot.quot_won_user_id where ( 1 AND quot.quotation_status = 0 and quot.revise_status=0 and quot.approve_status=1 and stage_prob>=90 and quot.company_id in (0,'. $_SESSION['company_id'] .') ' . $branch_id . ' ' . $where;*/

    $qry = 'SELECT COUNT(quot.quotation_id) as ttl_pen_po FROM tbl_quotation as quot 
        left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
        WHERE quot.quotation_status = 0 and revise_status=0 and approve_status=1 and stage_prob>=90 and po_approve_status!=3 and quot.company_id in (0, ' . $_SESSION['company_id'] . ') ' . $branch_id . ' ' . $where;
    $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));
    return floatval($qry_rel['ttl_pen_po']);
    //return $qry;
    //return $where;
}
function count_pend_so_approve($dbcon, $user_id)
{
    $where = '';
    $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND sales.branch_id = " . $_SESSION['branch_id'];
    if ($_SESSION['user_type'] != '2' && $_SESSION['user_type'] != '9') {
        $ser = trim(check_crm_find_in_set($dbcon, $_SESSION['user_id'], 0), ",");
        $where .= ' and sales.user_id IN (' . $ser . ')';
    } else {
        $ser = trim(check_crm_find_in_set($dbcon, $user_id, 0), ",");
        $where .= ' and sales.user_id IN (' . $ser . ')';
    }
    $qry = 'SELECT COUNT(sales.sales_order_id) as ttl_pen_po_order FROM tbl_sales_order as sales 
        WHERE sales.sales_order_status = 0 and approve_status=0 and order_accept_status=0 ' . $where . ' and sales.company_id = ' . $_SESSION['company_id'] . ' ' . $branch_id;
    $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));
    return floatval($qry_rel['ttl_pen_po_order']);
}
function count_dis_so_upload($dbcon, $user_id)
{
    $where = '';
    $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND sales.branch_id = " . $_SESSION['branch_id'];
    if ($_SESSION['user_type'] != '2' && $_SESSION['user_type'] != '9') {
        $ser = trim(check_crm_find_in_set($dbcon, $_SESSION['user_id'], 0), ",");
        $where .= ' and sales.user_id IN (' . $ser . ')';
    } else {
        $ser = trim(check_crm_find_in_set($dbcon, $user_id, 0), ",");
        $where .= ' and sales.user_id IN (' . $ser . ')';
    }
    $qry = 'SELECT COUNT(sales.sales_order_id) as ttl_pen_po_order FROM tbl_sales_order as sales 
        WHERE sales.sales_order_status = 0 and approve_status=1 and order_accept_status=3 and revise_status=0 ' . $where . ' and sales.company_id = ' . $_SESSION['company_id'] . ' ' . $branch_id;
    $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));
    return floatval($qry_rel['ttl_pen_po_order']);
}
function count_pend_order_accept($dbcon, $user_id)
{
    $where = '';
    $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND sales.branch_id = " . $_SESSION['branch_id'];
    if ($_SESSION['user_type'] != '2' && $_SESSION['user_type'] != '9') {
        $ser = trim(check_crm_find_in_set($dbcon, $_SESSION['user_id'], 0), ",");
        $where .= ' and sales.user_id IN (' . $ser . ')';
    } else {
        $ser = trim(check_crm_find_in_set($dbcon, $user_id, 0), ",");
        $where .= ' and sales.user_id IN (' . $ser . ')';
    }
    $qry = 'SELECT COUNT(sales.sales_order_id) as ttl_pen_po_order FROM tbl_sales_order as sales 
        WHERE sales.sales_order_status = 0 and approve_status=3 and order_accept_status=0 ' . $where . ' and sales.company_id = ' . $_SESSION['company_id'] . ' ' . $branch_id;
    $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));
    return floatval($qry_rel['ttl_pen_po_order']);
}
function count_pend_appoint($dbcon, $user_id)
{
    $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND task.branch_id = " . $_SESSION['branch_id'];
    if (!$user_id)
        $user_id = $_SESSION['user_id'];
    $qry = 'SELECT count(task.task_id) as ttl_pen_disp
        from tbl_task as task 
        where task.task_status = 0 and task.entry_type=2 and task.company_id = ' . $_SESSION['company_id'] . ' ' . $branch_id . ' and task.alert_date_time!="0000-00-00 00:00:00" and task.alert_date_time!="1970-01-01 05:30:00" and find_in_set(' . $user_id . ',task.assign_user_ids) and alert_date_time<"' . date('Y-m-d H:i:s') . '"';
    $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));
    return floatval($qry_rel['ttl_pen_disp']);
}
function count_pend_disp($dbcon)
{
    $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND so.branch_id = " . $_SESSION['branch_id'];
    $qry = 'SELECT quot.* from tbl_sales_ordertrn as quot 
        left join tbl_sales_order as so on so.sales_order_id=quot.sales_order_id
        where quot.sales_ordertrn_status = 0 and quot.invoice_status=0 and so.approve_status=3 and so.company_id =' . $_SESSION['company_id'] . ' ' . $branch_id;
    $wo = $dbcon->query($qry);
    $k = 0;
    while ($qry_rel = mysqli_fetch_assoc($wo)) {
        if ($qry_rel['with_out_stock_invoice'] == "0") {
            $res_st = reserve_stock($dbcon, $qry_rel['product_id'], $qry_rel['unit_id'], $reserve_id, $request_id, $complaint_id, $qry_rel['sales_ordertrn_id']);
            if ($res_st > 0) {
                $k = $k + 1;
            }
        } else {
            $k = $k + 1;
        }
    }
    return floatval($k);
}
function count_usr_pen_tsk($dbcon, $task_type_id, $user_id)
{
    $fis = check_crm_find_in_set($dbcon, $user_id, 1);
    $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND task.branch_id = " . $_SESSION['branch_id'];
    $fis1 = "  and FIND_IN_SET (" . $user_id . ",task.show_user_ids)";
    if ($task_type_id == 28) {
        $fis1 .= ' and task.entry_type=3';
    } else {
        $fis1 .= ' and task.entry_type=1';
    }
    $qry = 'SELECT count(DISTINCT task.task_id) as ttl_pen_tasks from tbl_task as task
        WHERE task.task_status=0 and DATE_FORMAT(task.task_due_date,"%Y-%m-%d")<="' . date('Y-m-d 23:59:59') . '" and task.task_type_id=' . $task_type_id . '  ' . $fis1 . ' and task.company_id =' . $_SESSION['company_id'] . ' ' . $branch_id . ' order by task_due_date DESC';
    $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));
    return floatval($qry_rel['ttl_pen_tasks']);
}
function count_usr_pen_tsk1($dbcon, $task_type_id, $user_id)
{
    $fis = check_crm_find_in_set($dbcon, $user_id, 1);
    $branch_id = ($_SESSION['branch_id'] == 0) ? "" : " AND task.branch_id = " . $_SESSION['branch_id'];
    $fis1 = "  and FIND_IN_SET (" . $user_id . ",task.assign_user_ids)";
    $qry = 'SELECT count(DISTINCT task.task_id) as ttl_pen_tasks from tbl_task as task WHERE task.task_status=0 and task.entry_type=1 and DATE_FORMAT(task.task_due_date,"%Y-%m-%d")<="' . date('Y-m-d 23:59:59') . '" ' . $fis1 . ' and task.task_type_id=' . $task_type_id . ' and task.company_id =' . $_SESSION['company_id'] . ' order by task_due_date DESC';
    $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));
    return floatval($qry_rel['ttl_pen_tasks']);
}
function get_tree_assign_user($dbcon, $user_id, $res)
{
    $query = "select us.user_id,us.user_name,rus.user_name as pname from users as us
        left join users as rus on rus.user_id=us.report_to_user_id
        where us.user_id=" . $user_id;
    $rs_dispatch = $dbcon->query($query);
    $str = "";
    $row = array();
    array_push($row, $res);
    while ($rel = mysqli_fetch_assoc($rs_dispatch)) {

        array_push($row, $rel['user_id']);
        $query1 = "select user_id,user_name from users where report_to_user_id=" . $user_id;
        $rs_dispatch1 = $dbcon->query($query1);
        while ($rel1 = mysqli_fetch_assoc($rs_dispatch1)) {
            if (!empty($rel1['user_id'])) {
                $str .= get_tree_assign_user($dbcon, $rel1['user_id'], $row);
            }
        }
    }
    $str = implode(",", $row);
    return $str;
}
function get_crm_gst_statecode($dbcon, $cust_id)
{

    $qry = "SELECT cust.c_add_state,sm.gst_state_code,sm.state_name FROM `tbl_cust_address` as cust left join state_mst as sm on sm.stateid= cust.c_add_state where cust.cust_id='" . $cust_id . "' AND cust.c_add_status = 0";
    $result = brp_mysqli_fetch_assoc($dbcon->query($qry));
    return $result['gst_state_code'] . "," . $result['c_add_state'];
    // return $qry;

}
function get_for_cast_by($dbcon, $id)
{
    $pro_tml = '';
    $pro_tml .= '<option value="1" ' . (($id == "1") ? "selected" : "") . '>Calender Year</option>';
    $pro_tml .= '<option value="2" ' . (($id == "2") ? "selected" : "") . '>Financial Year</option>';
    return $pro_tml;
}
function get_for_cast_by_name($id)
{
    switch ($id) {
        case "1":
            $name = "Calender Year";
            break;
        case "2":
            $name = "Financial Year";
            break;
        default:
            $name = "";
    }
    return $name;
}
function get_for_target_p($dbcon, $id)
{
    $html = '';
    $html .= '<option value="1" ' . (($id == "1") ? "selected" : "") . '>Monthly</option>';
    $html .= '<option value="2" ' . (($id == "2") ? "selected" : "") . '>Quarterly</option>';
    $html .= '<option value="3" ' . (($id == "3") ? "selected" : "") . '>Half Yearly</option>';
    $html .= '<option value="4" ' . (($id == "4") ? "selected" : "") . '>Yearly</option>';
    return $html;
}
function get_for_target_p_name($dbcon, $id)
{
    switch ($id) {
        case "1":
            $name = "Monthly";
            break;
        case "2":
            $name = "Quarterly";
            break;
        case "3":
            $name = "Half Yearly";
            break;
        case "4":
            $name = "Yearly";
            break;
        default:
            $name = "";
    }
    return $name;
}
function getMonthNumber($monthStr)
{
    //e.g, $month='Jan' or 'January' or 'JAN' or 'JANUARY' or 'january' or 'jan'
    $m = ucfirst(strtolower(trim($monthStr)));
    switch ($m) {
        case "January":
        case "Jan":
            $m = "01";
            break;
        case "February":
        case "Feb":
            $m = "02";
            break;
        case "March":
        case "Mar":
            $m = "03";
            break;
        case "April":
        case "Apr":
            $m = "04";
            break;
        case "May":
            $m = "05";
            break;
        case "June":
        case "Jun":
            $m = "06";
            break;
        case "July":
        case "Jul":
            $m = "07";
            break;
        case "August":
        case "Aug":
            $m = "08";
            break;
        case "September":
        case "Sep":
            $m = "09";
            break;
        case "October":
        case "Oct":
            $m = "10";
            break;
        case "November":
        case "Nov":
            $m = "11";
            break;
        case "December":
        case "Dec":
            $m = "12";
            break;
        default:
            $m = false;
            break;
    }
    return $m;
}
function get_for_period($dbcon, $f_by_id, $f_target_period, $f_period_id)
{

    $query = "select f_period_id,f_period_name from forecast_period_mst where f_period_status=0 and f_by_id=" . $f_by_id . " and f_target_period=" . $f_target_period . "";

    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Period</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['f_period_id'] == $f_period_id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['f_period_id'] . '">' . $rel['f_period_name'] . '</option>';
    }
    return $str;
}
function load_f_by_year($f_by_id, $f_year)
{
    $str = '';
    if ($f_by_id == '1') { //Calender Year
        $s_year = 2018;
        $e_year = date("Y");
        for ($i = $e_year; $i >= $s_year; $i--) {
            $sel = '';
            if ($f_year == $i) {
                $sel = 'selected="selected"';
            }
            $str .= '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
        }
    } else if ($f_by_id == '2') { //Financial Year
        $minyear = 2018;
        $maxyear = (date('m') < '04') ? date('Y', strtotime('-1 year')) : date('Y');
        $end = $start + 1;
        for ($y = $minyear; $y <= $maxyear; $y++) {
            $sel = '';
            if ($f_year == $y) {
                $sel = 'selected="selected"';
            }
            $str .= '<option ' . $sel . ' value="' . $y . '">' . $y . '-' . ($y + 1) . '</option>';
        }
    }

    return $str;
}
function get_child_users($dbcon, $id)
{
    $query = "select user_id,user_name from users where active=0 and report_to_user_id='$_SESSION[user_id]' and company_id='$_SESSION[company_id]'";
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Users</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['user_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['user_id'] . '">' . $rel['user_name'] . '</option>';
    }
    return $str;
}
function get_inquiry($dbcon, $id)
{
    $query = "select inquiry_id,inquiry_no from tbl_inquiry where inquiry_status=0 and company_id='$_SESSION[company_id]'";
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Inquiry</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        // $sel = '';
        if ($rel['inquiry_id'] == $id) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['inquiry_id'] . '">' . $rel['inquiry_no'] . '</option>';
    }
    return $str;
}
function get_contactperson_all($dbcon, $eid)
{
    $query = "select c_con_id,c_con_fname,c_con_lname from tbl_cust_contact where c_con_status=0";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Contact Person</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        // $sel = '';
        if ($rel['c_con_id'] == $eid) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['c_con_id'] . '">' . $rel['c_con_fname'] . ' ' . $rel['c_con_lname'] . '</option>';
    }
    return $str;
}
function get_rel_task($dbcon, $sid)
{
    $qry = "select * from task_rel_mst where task_rel_status=0";
    $rs_state = $dbcon->query($qry);
    $str = '';
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['task_rel_id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        $str .= '<option ' . $sel . ' value="' . $row['task_rel_id'] . '">' . $row['task_rel_name'] . '</option>';
    }
    return $str;
}
function get_alert_mintes($dbcon, $task_alert_id)
{
    $query = "select * from task_alert_mst where task_alert_status=0 and task_alert_id=" . $task_alert_id;
    $query_rs = $dbcon->query($query);
    $query_rel = brp_mysqli_fetch_assoc($query_rs);
    return floatval($query_rel['task_gap_minutes']);
}
function get_inquiry_stage($dbcon, $eid)
{
    $qry = "select opp_id,opp_stage from tbl_opportunity_mst where opp_status=0 and company_id IN (0,$_SESSION[company_id]) order by opp_priority";
    $rs_state = $dbcon->query($qry);
    $str = "<option value=''>Choose Stage</option>";
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        // $sel = '';
        if ($row['opp_id'] == $eid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['opp_id'] . '">' . $row['opp_stage'] . '</option>';
    }
    return $str;
}
function get_product_common_tax($dbcon, $product_amount, $formulaid)
{
    $qry = "SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=" . $formulaid . " order by tax_value desc";
    $row = $dbcon->query($qry);
    $rate_total = $total = $product_amount;
    $i = 1;
    while ($tax = brp_mysqli_fetch_assoc($row)) {
        $info['tax_name' . $i] = $tax['tax_name'];
        $info['tax_amount' . $i] = $tax_amount = ($total) * $tax['tax_value'] / 100;
        $rate_total += $tax_amount;
        $i++;
    }
    for ($j = $i; $j <= 3; $j++) {
        $info['tax_name' . $j] = '';
        $info['tax_amount' . $j] = '';
    }
    $info['product_total'] = $rate_total;
    return $info;
}
function get_annexure_types($dbcon, $eid)
{
    $eids = explode(",", $eid);
    if ($eid) {
        $order = "ORDER BY FIND_IN_SET(an_id,'" . $eid . "')";
    }
    $query = "select an_id,an_name from tbl_annexure where an_status=0 " . $order;
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Annexure</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if (in_array($rel['an_id'], $eids)) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['an_id'] . '">' . $rel['an_name'] . '</option>';
    }
    return $str;
}

function get_specification_types($dbcon, $eid)
{
    $eids = explode(",", $eid);
    $query = "select specification_id,specification_name from tbl_specification where specification_status=0 ORDER BY FIND_IN_SET(specification_name,'" . $eid . "')";
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Specification</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if (in_array($rel['specification_name'], $eids)) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['specification_id'] . '">' . $rel['specification_name'] . '</option>';
    }
    return $str;
}

function get_cust_inq($dbcon, $eid, $cust_id)
{
    $query = "select inquiry_id,inquiry_name,inquiry_no from tbl_inquiry where inquiry_status=0 and cust_id=" . $cust_id;
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Inquiry</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['inquiry_id'] == $eid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['inquiry_id'] . '">' . $rel['inquiry_no'] . '</option>';
    }
    return $str;
}
function get_cust_contactperson($dbcon, $eid, $cust_id)
{
    $query = "select c_con_id,c_con_fname,c_con_lname from tbl_cust_contact where c_con_status=0 and cust_id='$cust_id'";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Contact Person</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['c_con_id'] == $eid) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['c_con_id'] . '">' . $rel['c_con_fname'] . ' ' . $rel['c_con_lname'] . '</option>';
    }
    return $str;
}
function getcust_crm($dbcon, $id)
{
    //$query="select * from tbl_customer where cust_status=0 and  company_id in (0,$_SESSION[company_id])";
    $query = "select * from tbl_customer  where cust_status=0 and company_id='" . $_SESSION['company_id'];
    $rs_cust = $dbcon->query($query);
    echo '<option value="">Choose Company</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        // $sel = '';
        if ($rel['cust_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['cust_id'] . '">' . $rel['cust_name'] . '</option>';
    }
}
function get_terms_category($dbcon, $sid)
{
    $qry = "select * from terms_condition_category_type where status=0 and company_id=" . $_SESSION['company_id'];
    $rs_state = $dbcon->query($qry);

    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        echo '<option ' . $sel . ' value="' . $row['id'] . '">' . $row['terms_condition_category_name'] . '</option>';
    }
}
function get_all_territory($dbcon, $sid)
{

    $string = '';
    $qry = "select t_id,t_name from territory_mst where t_status=0 and company_id = " . $_SESSION['company_id'] . " order by t_id";
    $rs_state = $dbcon->query($qry);
    $sid = explode(",", $sid);
    //
    $string .= '<option value="">Choose Territory</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if (in_array($row['t_id'], $sid)) {
            $sel = 'selected="selected"';
        }
        $string .= '<option ' . $sel . ' value="' . $row['t_id'] . '">' . $row['t_name'] . '</option>';
    }
    echo $string;
}
function get_master_category($dbcon, $sid)
{
    $qry = "select * from tbl_master_category where mc_status=1";
    $rs_state = $dbcon->query($qry);
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['mc_id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        echo '<option ' . $sel . ' value="' . $row['mc_id'] . '">' . $row['mc_name'] . '</option>';
    }
}

function get_org_currency($dbcon, $sid)
{
    $qry = "select c.currency_name,c.currency_id ,c.currency_code from tbl_org_currency as org left join tbl_currency as c on c.currency_id=org.curren_id where org.comp_id='$_SESSION[company_id]'";
    $rs_state = $dbcon->query($qry);
    $str = "";
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['currency_id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = '';
        }
        $str .= '<option ' . $sel . ' value="' . $row['currency_id'] . '">' . $row['currency_name'] . ' - ' . $row['currency_code'] . '</option>';
    }
    echo $str;
}
function get_task_alert_types($dbcon, $sid)
{
    $qry = "select * from task_alert_mst where task_alert_status=0";
    $rs_state = $dbcon->query($qry);
    $str = '';
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        // $sel = '';
        if ($row['task_alert_id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        $str .= '<option ' . $sel . ' value="' . $row['task_alert_id'] . '">' . $row['task_alert_name'] . '</option>';
    }
    return $str;
}
function get_task_priority($dbcon, $sid)
{
    $qry = "select * from task_priority_mst where task_priority_status=0";
    $rs_state = $dbcon->query($qry);
    $str = '';
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        // $sel = '';
        if ($row['task_priority_id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        $str .= '<option ' . $sel . ' value="' . $row['task_priority_id'] . '">' . $row['task_priority_name'] . '</option>';
    }
    return $str;
}

function get_users_typewise($dbcon, $sid, $whr, $defaul_all = false)
{
    $qry = "select * from users where active=0 and user_type!=1 " . $whr . " and company_id IN (0," . $_SESSION['company_id'] . ")";
    $rs_state = $dbcon->query($qry);
    $str = '';
    $e_id = explode(",", $sid);
    if ($defaul_all) {
        $str .= '<option value="">All</option>';
    } else {
        $str .= '<option value="">Choose User</option>';
    }
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        // $sel = '';
        //if($row['user_id']==$sid)
        if (in_array($row['user_id'], $e_id)) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        $str .= '<option ' . $sel . ' value="' . $row['user_id'] . '">' . $row['user_name'] . '</option>';
    }
    return $str;
}

function get_assign_users($dbcon, $sid, $whr)
{
    $companyConfiguration = getCompanyConfiguration($dbcon);
    $ser = trim(check_crm_find_in_set_new($dbcon, $_SESSION['user_id'], 0), ",");
    $sers = trim(check_crm_find_in_set($dbcon, $_SESSION['user_id'], 0), ",");
    if ($companyConfiguration['hierarchy_inq_assign'] == 0) {
        $usrtype = $companyConfiguration['crm_user_type'];
        $whr = " and ( usr.user_id IN (" . $ser . "," . $sers . ") OR usr.user_type IN (" . $usrtype . "))";
    }
    $qry = "select usr.*,type.usertype_name from users as usr
    left join tbl_usertype as type on type.usertype_id=usr.user_type
    where usr.active=0 and usr.user_type!=1 " . $whr . " and usr.company_id IN (0," . $_SESSION['company_id'] . ")";
    $rs_state = $dbcon->query($qry);
    $str = '';
    $e_id = explode(",", $sid);
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        //if($row['user_id']==$sid)
        if (in_array($row['user_id'], $e_id)) {
            $sel = 'selected="selected"';
        } else {
            if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']) {
                $sel = 'selected="selected"';
            } else {
                $sel = "";
            }
        }
        $str .= '<option ' . $sel . ' value="' . $row['user_id'] . '">' . $row['user_name'] . ' - ' . $row['usertype_name'] . '</option>';
    }
    return $str;
}
function get_master_category_dtl($dbcon, $sid, $mcd_cat_id, $inquiry_id, $type)
{
    $str = '';
    // $wher = "";
    //var_dump($inquiry_id);
    if ($type == "1") {
        $qry1 = "select * from tbl_quotation where quotation_status=0 and inquiry_id=" . $inquiry_id;
        $rs_state1 = $dbcon->query($qry1);
        $row1 = brp_mysqli_fetch_assoc($rs_state1);
        if (empty($row1['quotation_no'])) {
            if ($inquiry_id == 'POST') {
                $wher = " and mcd_id not in (20,21)";
            } else {
                $wher = " and mcd_id not in (20,21,28)";
            }
        } else {
            $wher = " and mcd_id not in (15,16,28)";
        }
    }
    $qry = "select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_id != '" . GENERAL_TASK_TYPE . "' " . $wher . " and mcd_cat_id=" . $mcd_cat_id;
    $rs_state = $dbcon->query($qry);
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['mcd_id'] == $sid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['mcd_id'] . '">' . $row['mcd_name'] . '</option>';
    }
    return $str;
    //return $qry;

}

//Amish Soni Start 01-02-2021
function get_master_category_dtl_general($dbcon, $sid, $mcd_cat_id)
{
    $str = '';
    $qry = "select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 AND mcd_id = '" . GENERAL_TASK_TYPE . "' and mcd_cat_id=" . $mcd_cat_id;
    $rs_state = $dbcon->query($qry);
    while ($row = mysqli_fetch_assoc($rs_state)) {
        // $sel = '';
        if ($row['mcd_id'] == $sid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['mcd_id'] . '">' . $row['mcd_name'] . '</option>';
    }
    return $str;
}

function get_general_rel_task($dbcon, $sid)
{
    $qry = "select * from general_task_rel_mst where task_rel_status = 0";
    $rs_state = $dbcon->query($qry);
    $str = '';
    while ($row = mysqli_fetch_assoc($rs_state)) {
        // $sel = '';
        if ($row['task_rel_id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        $str .= '<option ' . $sel . ' value="' . $row['task_rel_id'] . '">' . $row['task_rel_name'] . '</option>';
    }
    return $str;
}
//Amish Soni End 01-02-2021
function get_refer_by($dbcon, $sid)
{
    $qry = "select * from tbl_refer_by where rb_status=0 AND company_id = " . $_SESSION['company_id'];
    $rs_state = $dbcon->query($qry);
    $str = "<option value=''>Choose Source / Referred By</option>";
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['rb_id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        $str .= '<option ' . $sel . ' value="' . $row['rb_id'] . '">' . $row['rb_name'] . '</option>';
    }
    echo $str;
}

function get_customer_type($dbcon, $sid)
{
    $qry = "select * from tbl_customer_type where ct_status=0";
    $rs_state = $dbcon->query($qry);

    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['ct_id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        echo '<option ' . $sel . ' value="' . $row['ct_id'] . '">' . $row['ct_name'] . '</option>';
    }
}

function get_customer_industries($dbcon, $sid)
{
    $qry = "select * from tbl_customer_industry where ci_status=0 and company_id = " . $_SESSION['company_id'];
    $rs_state = $dbcon->query($qry);

    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['ci_id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        echo '<option ' . $sel . ' value="' . $row['ci_id'] . '">' . $row['ci_name'] . '</option>';
    }
}

function get_customer_category($dbcon, $sid)
{
    $qry = "select * from tbl_customer_category where cc_status=0";
    $rs_state = $dbcon->query($qry);

    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['cc_id'] == $sid) {
            $sel = 'selected="selected"';
        } else {
            $sel = "";
        }
        echo '<option ' . $sel . ' value="' . $row['cc_id'] . '">' . $row['cc_name'] . '</option>';
    }
}
function get_customer_code($dbcon)
{
    $sel = $dbcon->query("select max(cust_code_series) as mid from tbl_customer");
    $row = brp_mysqli_fetch_array($sel);
    $mid = $row['mid'] + 1;

    $month = date("m");
    $year = date("Y");

    $code = "CUS" . $month . $year . '-' . $mid;
    return $code;
}

function get_customer_code_series($dbcon)
{
    $sel = $dbcon->query("select max(cust_code_series) as mid from tbl_customer");
    $row = brp_mysqli_fetch_array($sel);
    $mid = $row['mid'] + 1;

    return $mid;
}
function load_cust_prowise_model($dbcon, $id, $product_id, $cust_id)
{
    $str = '';
    $query = "select sold_pro.model_id,model.model_name from tbl_cust_sold_pro as sold_pro
    inner join model_mst as model on model.model_id=sold_pro.model_id
    where cust_sold_pro_status=0 and cust_id=" . $cust_id . " and sold_pro.product_id=" . $product_id . " and sold_pro.company_id in(0,$_SESSION[company_id]) ";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Model</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['model_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['model_id'] . '">' . $rel['model_name'] . '</option>';
    }
    return $str;
}
function load_prowise_model($dbcon, $id, $product_id)
{
    $str = '';
    $query = "select sold_pro.model_id,model.model_name from tbl_cust_sold_pro as sold_pro
    inner join model_mst as model on model.model_id=sold_pro.model_id
    where cust_sold_pro_status=0 and sold_pro.product_id=" . $product_id . " and sold_pro.company_id in(0,$_SESSION[company_id]) ";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Model</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['model_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['model_id'] . '">' . $rel['model_name'] . '</option>';
    }
    return $str;
}
function load_cust_sold_pro($dbcon, $id, $cust_id)
{
    $str = '';
    $query = "select sold_pro.product_id,pro.product_name from tbl_cust_sold_pro as sold_pro
    inner join product_mst as pro on pro.product_id=sold_pro.product_id left join tbl_invoicetrn as i on i.product_id=sold_pro.product_id where cust_sold_pro_status=0 and cust_id=" . $cust_id . " and sold_pro.company_id in(0,$_SESSION[company_id]) group by sold_pro.cust_sold_pro_id";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Product</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . '</option>';
    }
    return $str;
}
function getproduct_typewise($dbcon, $id = 0, $type = "", $pro_search = "", $cat = "")
{
    $str = '';
    $whr = '';
    $pro_search = explode(",", $pro_search);
    if ($type != '') {
        $whr .= ' and pro.product_type in(' . $type . ')';
    }

    if ($cat != '') {
        $whr .= ' and pro.product_category =' . $cat;
    }

    $query = "select pro.product_id, pro.product_type, pro.product_name, pro.product_icode, dr.drawing_number from product_mst as pro
    left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
    where product_status=0 " . $whr . " AND pro.company_id IN (0," . $_SESSION['company_id'] . ") order by product_name";
    $rs_dispatch = $dbcon->query($query);
    $str .= '<option value="">Choose Product</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if (in_array('drawing', $pro_search)) {
            $drawing_number = " -- (" . $rel['drawing_number'] . ")";
        } else {
            $drawing_number = '';
        }
        if (in_array('item', $pro_search)) {
            $item_code = " -- (" . $rel['product_icode'] . ")";
        } else {
            $item_code = '';
        }
        if ($rel['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '" data-product_type = "' . $rel['product_type'] . '">' . $rel['product_name'] . '' . $drawing_number . '' . $item_code . '</option>';
    }
    return $str;
}

function get_bom_product_typewise($dbcon, $id, $type, $pro_search)
{
    $str = '';
    $whr = '';
    $pro_search = explode(",", $pro_search);
    if ($type != '') {
        // $whr=' and pro.product_type in('.$type.')';
        $whr = ' and pro.product_type in (0,1,2,4)';
    }
    $query = "select pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number from product_mst as pro
    left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
    where product_status=0 " . $whr . " AND pro.company_id IN (0," . $_SESSION['company_id'] . ") order by product_name";
    $rs_dispatch = $dbcon->query($query);
    $str .= '<option value="">Choose Product</option>';
    while ($rel = mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if (in_array('drawing', $pro_search)) {
            $drawing_number = " -- (" . $rel['drawing_number'] . ")";
        } else {
            $drawing_number = '';
        }
        if (in_array('item', $pro_search)) {
            $item_code = " -- (" . $rel['product_icode'] . ")";
        } else {
            $item_code = '';
        }
        if ($rel['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . '' . $drawing_number . '' . $item_code . '</option>';
    }
    return $str;
}
function getproduct($dbcon, $id)
{
    $str = '';
    /*$query="select p.product_id,p.product_name,p.product_desc from product_mst as p
    where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) ";*/

    $query = "select p.product_id,p.product_name,p.product_desc,p.product_type, dr.drawing_number,p.drawing_id,p.product_icode,p.product_icode from product_mst as p

    left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
    where p.product_status=0 and p.company_id in(0," . $_SESSION['company_id'] . ")";

    $rs_product = $dbcon->query($query);
    $str .= '<option value="">Choose Product</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        if ($rel['drawing_id'] != 0) {
            $drawing_number = $rel['drawing_number'];
        } else {
            $drawing_number = '0';
        }
        $sel = '';
        if ($rel['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        $pname = $rel['product_name'] . " (" . $rel['product_icode'] . ")";
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '" data-product_type = "' . $rel['product_type'] . '" data-product_hsn = "' . $rel['product_hsn'] . '" data-product_stk_cn="' . $rel['product_stock_count'] . '"  >' . $pname . '</option>';
    }
    return $str;
}
/* Sanat  add for bom product filter -  30-07-2021  START */

function get_bom_product($dbcon, $id)
{
    $str = '';
    /*$query="select p.product_id,p.product_name,p.product_desc from product_mst as p
    where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) ";*/

    $query = "select p.product_id,p.product_name,p.product_desc,p.product_type, dr.drawing_number,p.drawing_id from product_mst as p
    left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
    where p.product_type IN (0,1,2,4) and p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) ";

    $rs_product = $dbcon->query($query);
    $str .= '<option value="">Choose Product</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        if ($rel['drawing_id'] != 0) {
            $drawing_number = $rel['product_icode'];
        } else {
            $drawing_number = '0';
        }
        // $sel = '';
        if ($rel['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '" data-product_type = "' . $rel['product_type'] . '">' . $rel['product_name'] . "-- (" . $drawing_number . ')' . '</option>';
    }
    return $str;
}

/* Sanat  add for bom product filter -  30-07-2021  END */

function get_complaint_type($dbcon, $id)
{
    $str = '';
    $query = "select `complaint_type_id`,`complaint_type_name` from complaint_type_mst where complaint_type_status=0 and company_id in(0,$_SESSION[company_id]) ";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Complaint Type</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['complaint_type_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['complaint_type_id'] . '">' . $rel['complaint_type_name'] . '</option>';
    }
    return $str;
}

/* 
 * Author : JS
 * Purpose : Get Media
 */
function get_media($dbcon, $id)
{
    $str = '';
    $query = "select * from complaint_media_name_mst where media_status=0 and company_id in(0,$_SESSION[company_id]) ";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Complaint Media</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['media_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['media_id'] . '">' . $rel['media_name'] . '</option>';
    }
    return $str;
}

function get_prowise_model($dbcon, $id, $product_id)
{
    $str = '';
    $query = "select `model_id`,`model_name` from model_mst where model_status=0 and product_id=" . $product_id . " and company_id in(0,$_SESSION[company_id]) ";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Model</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['model_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['model_id'] . '">' . $rel['model_name'] . '</option>';
    }
    return $str;
}
function get_expense_head($dbcon, $id)
{
    $str = '';
    $query = "select `expense_head_id`,`expense_head_name` from expense_head_mst where expense_head_status=0 and company_id in(0,$_SESSION[company_id]) ";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Expense Head</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['expense_head_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['expense_head_id'] . '">' . $rel['expense_head_name'] . '</option>';
    }
    return $str;
}
function get_product_typewise($dbcon, $id, $type)
{
    $str = '';
    $query = "select `product_id`,`product_name`,`product_type` from product_mst where product_status=0 and company_id in(0,$_SESSION[company_id]) and product_type in($type) ";
    $rs_product = $dbcon->query($query);
    $str .= '<option value="">Choose Product</option>';
    $type_arr = array(
        '',
        'FINISH PRODUCT',
        'ASSEMBLY PRODUCT',
        'SEMI-FINISH',
        'RAW MATERIAL',
        'FINISH COMPONENT',
        'SCRAP'
    );
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        $type_name = $type_arr[$rel['product_type']]; //Get Product Type Name
        if ($rel['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . ' - ' . $type_name . '</option>';
    }
    return $str;
}
function get_product($dbcon, $id, $type)
{
    $str = '';
    $query = "select p.product_id,p.product_name,c.cat_name,p.product_hsn,p.product_stock_count from product_mst as p left join tbl_category as c on c.cat_id=p.product_category where p.product_status=0 and p.company_id in(0,$_SESSION[company_id]) and p.product_type in($type) ";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Product</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '" data-product_hsn = "' . $rel['product_hsn'] . '" data-product_stk_cn="' . $rel['product_stock_count'] . '">' . $rel['product_name'] . "-- ( " . $rel['cat_name'] . ')' . '</option>';
    }
    return $str;
}
/*function get_product_type_name($dbcon,$status_type){
    //Dont Change Sort of array, Add only at end
    $type_arr=array('','FINISH PRODUCT','ASSEMBLY PRODUCT','SEMI-FINISH','RAW MATERIAL','FINISH COMPONENT');
    return $type_arr[$status_type];
}*/
function get_product_type_name($dbcon, $status_type)
{
    //Dont Change Sort of array, Add only at end
    /*$type_arr = array(
        'FINISH PRODUCT',
        'ASSEMBLY PRODUCT',
        'SUB ASSEMBLY',
        'RAW MATERIAL',
        'FINISH PART',
        'BOI',
        'CAPITAL GOODS',
        'CONSUMABLE',
        'SERVICE',
        'SCRAP'
    );
    return $type_arr[$status_type];*/

    $query = "select product_type_name from pro_ms_product_type as p where p.product_type_id=" . $status_type;
    $rs_product = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($rs_product);
    return $rel['product_type_name'];
}
function get_comp_pay_sts_name($dbcon, $status_type)
{
    //Dont Change Sort of array, Add only at end
    $type_arr = array(
        '',
        'Free',
        'Paid'
    );
    return $type_arr[$status_type];
}
function get_zone($dbcon, $id)
{
    $str = '';
    $query = "select `zone_id`,`zone_name` from zone_mst where zone_status=0 and company_id in(0,$_SESSION[company_id])";
    $rs_product = $dbcon->query($query);
    $str = '<option value="0">Choose Zone</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['zone_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['zone_id'] . '">' . $rel['zone_name'] . '</option>';
    }
    return $str;
}
function getcust_person($dbcon, $id, $cust_id)
{
    $str = '';
    $query = "select `cust_contact_person_id`,`cust_contact_person_name` from tbl_cust_contact_person where cust_contact_person_status=0 and cust_id=" . $cust_id;
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Person</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['cust_contact_person_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['cust_contact_person_id'] . '">' . $rel['cust_contact_person_name'] . '</option>';
    }
    return $str;
}

function getcust_purchase($dbcon, $id, $product_id)
{
    $str = '';
    //$query="select cust.cust_id,cust.company_name from tbl_customer as cust where cust_status=0 and cust.company_id in (0,$_SESSION[company_id])";
    $query = "select l.l_id,l.l_name,p.po_id,pt.po_id,pt.po_landing_cost from tbl_ledger as l left join tbl_pono as p on p.vender_id=l.l_id left join tbl_potrancation as pt on pt.po_id=p.po_id  where l.l_status=0 and l.l_form='customer_form' and pt.product_id='$product_id' order by pt.po_landing_cost desc";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Company</option>';
    $cnt = 1;
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($id == '') {
            if ($cnt == 1) {
                $sel .= "selected='selected'";
            }
        } else {
            if ($rel['l_id'] == $id) {
                $sel .= "selected='selected'";
            }
        }

        $str .= '<option ' . $sel . ' value="' . $rel['l_id'] . '">' . $rel['l_name'] . '</option>';

        $cnt++;
    }
    //return $str;
    return $query;
}

function get_company_data($dbcon, $company_id)
{
    $query = "select * from tbl_company where company_id='" . $company_id . "'";
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel;
}
function get_cust_data_arr($dbcon, $cust_id)
{
    $query = "select * from tbl_ledger where l_id='" . $cust_id . "'";
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel;
}
function getunit($dbcon, $id)
{
    $str = '';
    $query = "select `unitid`,`unit_name` from unit_mst where unit_status=0 order by unit_name";
    $rs_country = $dbcon->query($query);
    $str = '<option value="">Choose Unit</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_country)) {
        $sel = '';
        if ($rel['unitid'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['unitid'] . '">' . $rel['unit_name'] . '</option>';
    }
    return $str;
}




function getunit_converted($dbcon, $id)
{
    $str = '';
    $query = "select p.*,u.unit_name from tbl_product_unit as p left join unit_mst as u on u.unitid=p.unit_alt_unit where unit_product='$id'";
    $rs_country = $dbcon->query($query);
    $str = '<option value="">Choose Unit</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_country)) {
        $sel = '';
        if ($rel['unit_alt_unit'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['unit_alt_unit'] . '">' . $rel['unit_name'] . '</option>';
    }
    return $str;
}

function get_country($dbcon, $id)
{
    $str = '';
    $query = "select `countryid`,`country_name` from country_mst where country_status=0 order by country_name";
    $rs_country = $dbcon->query($query);
    $str = '<option value="">Choose Country</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_country)) {
        $sel = '';
        if ($rel['countryid'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['countryid'] . '">' . $rel['country_name'] . '</option>';
    }
    return $str;
}
function addDayswithdate($date, $days)
{
    $date = strtotime("+" . $days . " days", strtotime($date));
    return date("Y-m-d", $date);
}
function getquestion($dbcon, $id, $cond)
{
    $query = "select * from tbl_question where status=0 ";
    $rs_cust = $dbcon->query($query);
    $q = '<option value="">Choose Your Security Question </option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['question_id'] == $id) {
            $sel = "selected='selected'";
        }
        $q .= '<option ' . $sel . ' value="' . $rel['question_id'] . '">' . $rel['question'] . '</option>';
    }
    return $q;
}

function getusertype($dbcon, $sid, $con='')
{

    $usertype = '';
    // and company_id IN (0," . $_SESSION['company_id'] . ")
    $qry = "select * from tbl_usertype where status=0  " . $con;
    $rs_type = $dbcon->query($qry);

    //$usertype .='<option value="" selected="selected">Choose User Type</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        //echo "ddq";die();
        $sel = '';
        /*if($row['usertype_id']==$sid)
        {$sel='selected="selected"';}*/

        //echo $row['usertype_id'];
        $producttypeids = explode(",", $sid);

        if (in_array($row['usertype_id'], $producttypeids)) {
            $sel = "selected='selected'";
        }

        $usertype .= '<option ' . $sel . ' value="' . $row['usertype_id'] . '">' . $row['usertype_name'] . '</option>';

    }

    return $usertype;
}

function getalluser($dbcon, $sid)
{
    $usertype = '';
    $qry = "select usr.user_id,usr.user_name,type.usertype_name from users as usr
    left join tbl_usertype as type on type.usertype_id=usr.user_type
    where usr.active=0 and usertype_name IS NOT NULL and usr.company_id='" . $_SESSION['company_id'] . "' order by usr.user_name asc";
    $rs_type = $dbcon->query($qry);
    $usertype .= '<option value="" selected="selected">Choose User</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        // $sel = '';
        if ($row['user_id'] == $sid) {
            $sel = 'selected="selected"';
        }
        $usertype .= '<option ' . $sel . ' value="' . $row['user_id'] . '">' . $row['user_name'] . ' - ' . $row['usertype_name'] . '</option>';
    }
    return $usertype;
}

function getmenu($dbcon, $sid)
{
    $menu = '';
    $qry = "select * from tbl_menu where status=0 and pid=0";
    $rs_menu = $dbcon->query($qry);
    $menu .= '<option value="" selected="selected">Choose Menu</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_menu)) {
        $sel = '';
        if ($row['menu_id'] == $sid) {
            $sel = 'selected="selected"';
        }
        $menu .= '<option ' . $sel . ' value="' . $row['menu_id'] . '">' . $row['menu_name'] . '</option>';
    }
    return $menu;
}
function get_state_all($dbcon, $sid, $cid, $default_all = false)
{
    $qry = "select * from state_mst where state_status=0 and countryid=" . $cid;
    $rs_state = $dbcon->query($qry);
    $str = '';
    $sid = explode(",", $sid);
    if ($default_all) {
        $str .= '<option value="">All</option>';
    } else {
        $str .= '<option value="" disabled>Choose State</option>';
    }

    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if (in_array($row['stateid'], $sid)) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['stateid'] . '">' . $row['state_name'] . '</option>';
    }
    return $str;
}
function get_state($dbcon, $sid, $cid)
{
    $qry = "select * from state_mst where state_status=0 and countryid=" . $cid;
    $rs_state = $dbcon->query($qry);
    $str = '';
    $str .= '<option value="" data-statecode="">Choose State</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['stateid'] == $sid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['stateid'] . '" data-statecode="' . $row['gst_state_code'] . '">' . $row['state_name'] . '-' . $row['gst_state_code'] . '</option>';
    }
    return $str;
}
function getstate($dbcon, $sid)
{
    $qry = "select * from state_mst where state_status=0";
    $rs_state = $dbcon->query($qry);
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['stateid'] == $sid) {
            $sel = 'selected="selected"';
        }
        echo '<option ' . $sel . ' value="' . $row['stateid'] . '">' . $row['state_name'] . '</option>';
    }
}
function get_return_state($dbcon, $sid)
{
    $qry = "select * from state_mst where state_status=0";
    $rs_state = $dbcon->query($qry);
    $str = '';
    $str .= '<option value="" data-statecode="">Choose State</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if ($row['stateid'] == $sid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['stateid'] . '">' . $row['state_name'] . '</option>';
    }
    return $str;
}
function getcity($dbcon, $sid, $cid)
{
    $city = '';
    $whr = "";
    if ($sid)
        $whr = "and stateid=" . $sid;
    $c_qry = "select * from city_mst where city_status=0 $whr order by city_name";
    $rs_city = $dbcon->query($c_qry);
    $city .= '<option value="">Choose City</option>';
    while ($r = brp_mysqli_fetch_assoc($rs_city)) {
        $sel = '';
        if ($r['cityid'] == $cid) {
            $sel = 'selected="selected"';
        }
        $city .= '<option ' . $sel . ' value="' . $r['cityid'] . '">' . $r['city_name'] . '</option>';
    }
    return $city;
}

function get_city_all($dbcon, $sid, $cid)
{
    $qry = "select * from city_mst where city_status=0";

    if (!empty($cid)) {
        $qry .= ' and stateid in(' . $cid . ')';
    }

    $rs_state = $dbcon->query($qry);
    $str = '';
    $sid = explode(",", $sid);
    $str .= '<option value="" disabled>Choose City</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_state)) {
        $sel = '';
        if (in_array($row['cityid'], $sid)) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['cityid'] . '">' . $row['city_name'] . '</option>';
    }
    return $str;
}
function getcity_all($dbcon, $cid)
{
    $city = '';
    $c_qry = "select * from city_mst where city_status=0 order by city_name";
    $rs_city = $dbcon->query($c_qry);
    $city .= '<option value="">Choose City</option>';
    while ($r = brp_mysqli_fetch_assoc($rs_city)) {
        $sel = '';
        if ($r['cityid'] == $cid) {
            $sel = 'selected="selected"';
        }
        $city .= '<option ' . $sel . ' value="' . $r['cityid'] . '">' . $r['city_name'] . '</option>';
    }
    return $city;
}

function get_ledger_bank($dbcon, $ledger_id)
{
    $str = '';

    $query = "select * from tbl_ledger as pro where l_status=0 and l_form in ('bank_form','cash') and company_id = $_SESSION[company_id] order by TRIM(l_name) ASC";
    $rs_dispatch = $dbcon->query($query);
    $str .= '<option value="">--Select Account--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['l_id'] == $ledger_id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['l_id'] . '">' . $rel['l_name'] . '</option>';
    }
    return $str;
}
function get_pay_ledger($dbcon, $id)
{
    $str = '';
    //$query="select cust.cust_id,cust.company_name from tbl_customer as cust where cust_status=0 and cust.company_id in (0,$_SESSION[company_id])";
    $query = "select l.l_id,l.l_name,c.city_name from tbl_ledger as l left join city_mst as c on c.cityid=l.cityid where l.l_status=0 and l.l_form in('customer_form','emp_form') and l.company_id in (0,$_SESSION[company_id])";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Company</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['l_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['l_id'] . '">' . $rel['l_name'] . ' ( ' . $rel['city_name'] . ' )' . '</option>';
    }
    return $str;
}
/*
 *  added by Dimple Panchal 30-sep-2020
 *  when inquiry added from india mart, customer got created, but was not showing in inquiry edit.
 */
function getcustomer($dbcon, $id, $flag = 0)
{
    $str = '';
    $where = '';
    if ($flag == 1) {
        $companyConfiguration = getCompanyConfiguration($dbcon);
        $enable_assing_user = $companyConfiguration['enable_assing_user'];
        if ($enable_assing_user == 1) {
            $where .= " and FIND_IN_SET(" . $_SESSION['user_id'] . ",cust.cust_assign_user)";
        }
    }
    $query = "SELECT cust.cust_id,cust.cust_name from tbl_customer as cust 
    where cust_status=0 and cust.company_id in (0," . $_SESSION['company_id'] . ")" . $where;

    $rs_cust = $dbcon->query($query);
    $str .= '<option value="">Choose Company</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['cust_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['cust_id'] . '">' . $rel['cust_name'] . '</option>';
    }
    return $str;
}
function getcust($dbcon, $id, $group_id, $flag = 0, $indent = '')
{
    $str = '';
    $where = "";
    if (!empty($group_id)) {
        $where .= " and l.l_group IN (" . $group_id . ")";
    }
    if ($flag == 1) {
        $companyConfiguration = getCompanyConfiguration($dbcon);
        $enable_assing_user = $companyConfiguration['enable_assing_user'];
        if ($enable_assing_user == 1) {
            $where .= " and FIND_IN_SET(" . $_SESSION['user_id'] . ",l.cust_assign_user)";
        }
    }
    $query = "SELECT l.l_id,l.l_name,c.city_name, l.l_form, l.l_group from tbl_ledger as l left join city_mst as c on c.cityid=l.cityid where l.l_status=0 " . $where . "  and l.company_id IN (0,$_SESSION[company_id])";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Company</option>';
    if ($indent != '') {
        $str .= '<option value="new" >--New Vendor--</option>';
    }
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['l_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['l_id'] . '">' . $rel['l_name'] . ' ( ' . $rel['city_name'] . ' )' . '</option>';
    }
    return $str;
    // return $query;

}
/*Code By Umair: For Currency*/
function getcurrency($dbcon, $cid)
{
    $query = "SELECT * from tbl_currency where currency_status=0 ORDER BY currency_name ASC";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Currency</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['currency_id'] == $cid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['currency_id'] . '" data-currency-rate="' . $rel['currency_rate'] . '" data-currency-code="' . $rel['currency_code'] . '" data-currency-symbol="' . $rel['currency_symbol'] . '">' . $rel['currency_name'] . '-' . $rel['currency_code'] . '</option>';
    }
    return $str;
}

//Added by Dhruv
function getbasecurrency($dbcon)
{
    $query = "SELECT * from tbl_currency where currency_status=0 and isbasecrncy=1";
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel;
}
function getreportcust($dbcon, $id)
{
    $query = "SELECT * from tbl_customer where cust_status=0 and company_id in (0,$_SESSION[company_id])";
    $rs_cust = $dbcon->query($query);
    echo '<option value="">All Company</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['cust_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['cust_id'] . '">' . $rel['company_name'] . '</option>';
    }
}

function getbalance_type($dbcon, $id, $where = '')
{
    $query = "SELECT * from mst_balance_type where status=0 " . $where . " ";
    $rs_cust = $dbcon->query($query);
    echo '<option value="">Select Type</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['balance_typeid'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['balance_typeid'] . '">' . $rel['balance_type_name'] . '</option>';
    }
}

function getbalance_type_new($dbcon, $id)
{
    $str = '';
    $query = "SELECT * from mst_balance_type where status=0";
    $rs_cust = $dbcon->query($query);
    $str .= '<option value="">Select Type</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['balance_typeid'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['balance_typeid'] . '">' . $rel['balance_type_name'] . '</option>';
    }

    return $str;
}

// get Status From Followup Table
function getFollowupStatus($dbcon, $id)
{
    $query = "select * from tbl_followup_status where f_id=$id";
    $row_fo = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row_fo);
    return $rel['f_status_name'];
}

// get start status id
function getFollowupStatusId($dbcon)
{
    $query = "select * from tbl_followup_status where f_status_name='Start'";
    $row_fo = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row_fo);
    return $rel['f_id'];
}

//get all Followup Status
function getAllStatus($dbcon, $id)
{
    $query = "select * from tbl_followup_status where f_status=0";
    $rs_cust = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        // $sel = '';
        if ($rel['f_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['f_id'] . '">' . $rel['f_status_name'] . '</option>';
    }
}

function get_ledger_accounts($dbcon, $id)
{
    $query = "select l.l_id,l.l_name,g.g_name from tbl_ledger as l
        left join tbl_group as g on g.g_id=l.l_group
        where l.l_status=0 ";
    $rs_cust = $dbcon->query($query);
    $str = '<option value="">Choose Ledger Account</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['l_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['l_id'] . '">' . $rel['l_name'] . ' - ' . $rel['g_name'] . '</option>';
    }
    return $str;
}
//get All Employee


function getAllEmployee($dbcon, $id)
{
    $query = "select l_id,l_name from tbl_ledger where l_status=0 and l_form='emp_form'";
    $rs_cust = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        // $sel = '';
        if ($rel['l_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['l_id'] . '">' . $rel['l_name'] . '</option>';
    }
}

//get user type
function getEmployeeIdUser($dbcon, $id)
{
    $query = "select * from users where user_id=$id";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    return $rel['employee_id'];
}

//get employee details
function getEmployeeDetail($dbcon, $id)
{
    $query = "select * from employee_mst where employee_id=$id";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    return $rel;
}

// get Complain Details
function getComplainDetail($dbcon, $id)
{
    $query = "select comp.branch_id, comp.complaint_id,comp.complaint_no,comp.complaint_date,comp.cust_id,comp.complaint_type_id,comp.cdate,comp.complaint_status,comp.old_sp_part_status,comp.sp_part_status,comp.followup_status,comp.emp_id,l.l_name,comty.complaint_type_name,f.f_status_name,l.m_address,l.cust_mobile from tbl_complaint as comp inner join tbl_ledger as l on comp.cust_id=l.l_id inner join complaint_type_mst as comty on comp.complaint_type_id=comty.complaint_type_id inner join tbl_followup_status as f on comp.followup_status=f.f_id where comp.complaint_id=$id";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($rs_cust);
    return $rel;
}

//get All Status Filter
function getAllStatus_filter($dbcon, $where, $id = '')
{
    $query = "select * from tbl_followup_status where f_status=0 " . $where;
    $rs_cust = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['f_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['f_id'] . '">' . $rel['f_status_name'] . '</option>';
    }
}

/*
 * Author : JS
 * Purpose : Get All service Status
 */
function getAllServiceStatus($dbcon, $where, $id)
{
    $query = "select * from tbl_followup_status where f_status=0 " . $where;
    $rs_cust = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['f_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['f_id'] . '">' . $rel['f_status_name'] . '</option>';
    }
}

//get all Product
function load_all_product($dbcon, $where, $id)
{
    $query = "select * from product_mst where product_status='0'" . $where;
    $rs_product = $dbcon->query($query);

    while ($rsp = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rsp['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rsp['product_id'] . '">' . $rsp['product_name'] . '</option>';
    }
}

function load_all_complain_product($dbcon, $id)
{
    $sel = '';
    $query = "SELECT comp.*,p.product_name from tbl_complaint_trn as comp LEFT join product_mst as p on p.product_id=comp.product_id where comp.complaint_id=$id AND comp.complaint_trn_status = 0";
    //1exit();
    $rs_product = $dbcon->query($query);

    while ($rsp = brp_mysqli_fetch_assoc($rs_product)) {
        echo '<option ' . $sel . ' value="' . $rsp['product_id'] . '">' . $rsp['product_name'] . '</option>';
    }
}

//get All Expense
function get_expense($dbcon, $id)
{
    $query = "select * from expense_mst where expense_status=0";
    $rs_expense = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_expense)) {
        $sel = '';
        if ($rel['expense_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['expense_id'] . '">' . $rel['expense_name'] . '</option>';
    }
}

//get all Complain
function get_all_complain($dbcon, $id, $where)
{

    $query = "select * from tbl_complaint where complaint_status=0" . $where;
    $rs_expense = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_expense)) {
        $sel = '';
        if ($rel['complaint_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['complaint_id'] . '">' . $rel['complaint_no'] . '</option>';
    }
}

//get Employee From User
function getAllEmployeeUser($dbcon)
{
    $query = "select * from users where employee_id>0";
    $rs_cust = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        // $sel = '';
        if ($rel['user_id'] != "") {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['user_id'] . '">' . $rel['user_name'] . '</option>';
    }
}

//get total Spare Part count by complaint id
function get_total_spare_count($dbcon, $id)
{
    $query = "select * from tbl_complain_spare_part where s_comp_id='$id'";
    $rs_comp = $dbcon->query($query);
    $count = brp_mysqli_num_rows($rs_comp);
    return $count;
}

//get total old Spare Part count by complaint id
function get_total_spare_count_old($dbcon, $id)
{
    $query = "select * from tbl_complain_close_spare_part where sc_comp_id='$id'";
    $rs_comp = $dbcon->query($query);
    $count = brp_mysqli_num_rows($rs_comp);
    return $count;
}

//get total old Spare Part count by complaint id
/*function get_total_spare_count_request($dbcon, $id)
{
    $query = "select * from tbl_complain_spare_part where s_comp_id='$id' and s_status='2'";
    $rs_comp = $dbcon->query($query);
    $count = brp_mysqli_num_rows($rs_comp);
    return $count;
}


function get_product_count_close($dbcon, $id)
{
    $query = "select complaint_trn_status,complaint_id from tbl_complaint_trn where complaint_id='$id' and close_status='0'";
    $rs_comp = $dbcon->query($query);
    $count = brp_mysqli_num_rows($rs_comp);
    return $count;
}*/

function get_total_spare_count_request($dbcon, $id)
{
    $query = "select * from tbl_complain_spare_part where s_comp_id='$id' and s_status='2'";
    $rs_comp = $dbcon->query($query);
    $count = brp_mysqli_num_rows($rs_comp);
    return $count;
}

function get_product_count_close($dbcon, $id)
{
    $query = "select complaint_trn_status,complaint_id from tbl_complaint_trn where complaint_id='$id' and close_status='0'";
    $rs_comp = $dbcon->query($query);
    $count = brp_mysqli_num_rows($rs_comp);
    return $count;
}

/*	function get_rate_product($dbcon, $id)
    {
        $query = "select product_rate,product_id from product_mst where product_id='$id'";
        $rs_comp = $dbcon->query($query);
        $row = brp_mysqli_fetch_array($rs_comp);
        return $row['product_rate'];
    }

    function getinvoicetype($dbcon, $id)
    {
        $query = "select * from tbl_invoicetype where status=0 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
        $rs_dispatch = $dbcon->query($query);
        echo '<option value="" selected="selected">Choose Invoice Type</option>';
        while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
            $sel = '';
            if ($rel['invoicetype_id'] == $id) {
                $sel = 'selected="selected"';
            }
            echo '<option ' . $sel . ' value="' . $rel['invoicetype_id'] . '">' . $rel['invoice_type'] . '</option>';
        }
    }


    function getpaymentterms($dbcon, $eid)
    {
        $query = "select * from pay_terms where terms_status=0";
        $rs_dispatch = $dbcon->query($query);
        $str = '';
        $str .= '<option value="">Choose Payment Terms</option>';
        while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
            $sel = '';
            if ($rel['terms_id'] == $eid) {
                $sel = "selected = 'selected'";
            }
            $str .= '<option data-days="' . $rel['payment_days'] . '" value="' . $rel['terms_id'] . '" ' . $sel . '>' . $rel['payment_terms'] . '</option>';
        }
        return $str;
    }

    function getplaceofsupply($dbcon, $eid)
    {
        $query = "select * from supply_place where supply_place_status=0";
        $rs_dispatch = $dbcon->query($query);
        echo '<option value="">Choose Place Of Supply</option>';
        while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
            $sel = '';
            if ($rel['supply_place_id'] == $eid) {
                $sel = 'selected="selected"';
            }
            echo '<option ' . $sel . ' value="' . $rel['supply_place_id'] . '">' . $rel['place_supply'] . '</option>';
        }
    }

    function getmodeofdispache($dbcon, $eid)
    {
        $query = "select * from mode_of_dispatch where mode_des_status=0";
        $rs_dispatch = $dbcon->query($query);
        echo '<option value="">Choose Mode Of Dispatch</option>';
        while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
            $sel = '';
            if ($rel['mode_dis_id'] == $eid) {
                $sel = 'selected="selected"';
            }
            echo '<option ' . $sel . ' value="' . $rel['mode_dis_id'] . '">' . $rel['mode_dispatch'] . '</option>';
        }
    }*/


function get_rate_product($dbcon, $id)
{
    $query = "select product_rate,product_id from product_mst where product_id='$id'";
    $rs_comp = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($rs_comp);
    return $row['product_rate'];
}

function getinvoicetype($dbcon, $id)
{
    $query = "select * from tbl_invoicetype where status=0 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $rs_dispatch = $dbcon->query($query);
    echo '<option value="" selected="selected">Choose Invoice Type</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['invoicetype_id'] == $id) {
            $sel = 'selected="selected"';
        }
        echo '<option ' . $sel . ' value="' . $rel['invoicetype_id'] . '">' . $rel['invoice_type'] . '</option>';
    }
}

function getpaymentterms($dbcon, $eid)
{
    $query = "select * from pay_terms where terms_status=0";
    $rs_dispatch = $dbcon->query($query);
    $str = '';
    $str .= '<option value="">Choose Payment Terms</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['terms_id'] == $eid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' data-days="' . $rel['payment_days'] . '" value="' . $rel['terms_id'] . '">' . $rel['payment_terms'] . '</option>';
    }
    return $str;
}

function getplaceofsupply($dbcon, $eid)
{
    $query = "select * from supply_place where supply_place_status=0";
    $rs_dispatch = $dbcon->query($query);
    echo '<option value="">Choose Place Of Supply</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['supply_place_id'] == $eid) {
            $sel = 'selected="selected"';
        }
        echo '<option ' . $sel . ' value="' . $rel['supply_place_id'] . '">' . $rel['place_supply'] . '</option>';
    }
}

function getmodeofdispache($dbcon, $eid)
{
    $query = "select * from mode_of_dispatch where mode_des_status=0";
    $rs_dispatch = $dbcon->query($query);
    echo '<option value="">Choose Mode Of Dispatch</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['mode_dis_id'] == $eid) {
            $sel = 'selected="selected"';
        }
        echo '<option ' . $sel . ' value="' . $rel['mode_dis_id'] . '">' . $rel['mode_dispatch'] . '</option>';
    }
}

function get_custmer_consignee($dbcon, $parentid, $id, $table = null)
{
    $str = '';
    if ($table == '') {
        $table = 'tbl_custmer_consignee';
    }
    $query = "select * from $table where cust_status=0 and cust_ref_id=" . $parentid . " and company_id in (0,$_SESSION[company_id])";
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Consignee</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['cust_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['cust_id'] . '">' . $rel['company_name'] . '</option>';
    }
    return $str;
}

function getproducttype($dbcon, $id)
{
    //pathik add services option
    $pro_tml = '';
    $pro_tml .= '<option value="">Choose Product Type</option>';
    $pro_tml .= '<option value="0" ' . (($id == "0") ? "selected" : "") . '>Finish Product</option>';
    $pro_tml .= '<option value="1" ' . (($id == "1") ? "selected" : "") . '>Assembly Product</option>';
    $pro_tml .= '<option value="2" ' . (($id == "2") ? "selected" : "") . '>SUB ASSEMBLY</option>';
    $pro_tml .= '<option value="3" ' . (($id == "3") ? "selected" : "") . '>RAW MATERIA</option>';
    $pro_tml .= '<option value="4" ' . (($id == "4") ? "selected" : "") . '>FINISH PART</option>';
    $pro_tml .= '<option value="5" ' . (($id == "5") ? "selected" : "") . '>BOI</option>';
    $pro_tml .= '<option value="6" ' . (($id == "6") ? "selected" : "") . '>CAPITAL GOODS</option>';
    $pro_tml .= '<option value="7" ' . (($id == "7") ? "selected" : "") . '>CONSUMABLE</option>';
    $pro_tml .= '<option value="8" ' . (($id == "8") ? "selected" : "") . '>Services</option>';
    $pro_tml .= '<option value="9" ' . (($id == "9") ? "selected" : "") . '>SCRAP</option>';
    $pro_tml .= '<option value="-1" ' . (($id == "-1") ? "selected" : "") . ' style="display: none;">Project</option>';
    return $pro_tml;
}
function getformula($dbcon, $id)
{
    $formula_qry = "select * from  formula_mst where formula_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_formula = $dbcon->query($formula_qry);
    echo '<option value="">Choose Formula</option>';
    while ($formula = brp_mysqli_fetch_assoc($rs_formula)) {
        $sel = '';
        if ($formula['formulaid'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $formula['formulaid'] . '">' . $formula['formula_name'] . '</option>';
    }
}

function get_product_tax_common($dbcon, $product_amount, $formulaid)
{
    $qry = "SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=" . $formulaid . " order by tax_value desc";
    $row = $dbcon->query($qry);
    $rate_total = $total = $product_amount;
    $i = 1;
    $tax_amt_total = 0;
    $tax_total_amount = 0;
    while ($tax = brp_mysqli_fetch_assoc($row)) {
        $infpotrn['tax_name' . $i] = $tax['tax_name'];
        $infpotrn['tax_amount' . $i] = $tax_amount = ($total) * $tax['tax_value'] / 100;
        $rate_total += $tax_amount;
        $tax_amt_total += $infpotrn['tax_amount' . $i];
        $tax_total_amount += $tax['tax_value'];
        $i++;
    }
    for ($j = $i; $j <= 3; $j++) {
        $infpotrn['tax_name' . $i] = '';
        $infpotrn['tax_amount' . $i] = '';
    }
    $infpotrn['total'] = $rate_total;
    $infpotrn['tax_total'] = $tax_amt_total;
    $infpotrn['tax_total_amount'] = $tax_total_amount;
    return $infpotrn;
}

function get_pro_type_name($product_type)
{
    switch ($product_type) {
        case "0":
            $pro_type_name = "Finish Product";
            break;
        case "1":
            $pro_type_name = "Assembly Product";
            break;
        case "2":
            $pro_type_name = "SUB ASSEMBLY";
            break;
        case "3":
            $pro_type_name = "Raw Material";
            break;
        case "4":
            $pro_type_name = "FINISH PART";
            break;
        case "5":
            $pro_type_name = "BOI";
            break;
        case "6":
            $pro_type_name = "CAPITAL GOODS";
            break;
        case "7":
            $pro_type_name = "CONSUMABLE";
            break;
        case "8":
            $pro_type_name = "Service";
            break;
        case "9":
            $pro_type_name = "SCRAP";
            break;
        default:
            $pro_type_name = "";
    }
    return $pro_type_name;
}

function getlistinvoicetype($dbcon, $id)
{
    $query = "select * from tbl_invoicetype where status=0 and type_id=1 and and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $rs_dispatch = $dbcon->query($query);
    echo '<option value="" selected="selected">All</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        // $sel = '';
        if ($rel['invoicetype_id'] == $id) {
            $sel = 'selected="selected"';
        }
        echo '<option ' . $sel . ' value="' . $rel['invoicetype_id'] . '">' . $rel['invoice_type'] . '</option>';
    }
}

function get_pro_type_name_dynamic($dbcon, $product_type)
{
    $query = "select * from pro_ms_product_type where product_type_id = " . $product_type;
    $result = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($result);
    return $row['product_type_name'];
}
function get_acc_type($dbcon, $parentid, $id)
{
    $str = '';
    $query = "SELECT * FROM `mst_accounts` where edit_status=1 and status!=2 and acc_type_id=0";
    $rs_acc = $dbcon->query($query);
    $str = '<option value="">Choose Account Type</option>';
    while ($rel_acc = brp_mysqli_fetch_assoc($rs_acc)) {
        $str .= '<optgroup label="' . $rel_acc['account_name'] . '" data-select2-id="' . $rel_acc['accountid'] . '">';
        $query = "SELECT * FROM `mst_accounts` as acc where acc.status=0 and edit_status=1   and acc_type_id=" . $rel_acc['accountid'];
        $rs_dispatch = $dbcon->query($query);

        while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
            $sel = '';
            if ($rel['accountid'] == $id) {
                $sel = "selected='selected'";
            }
            $str .= '<option ' . $sel . ' value="' . $rel['accountid'] . '">' . stripslashes($rel['account_name']) . '</option>';
        }
        $str .= '</optgroup>';
    }
    return $str;
}

function get_voucher_type_list_common($eid, $dbcon)
{
    $str = '';
    $query = "Select * from mst_accounts_voucher_type where voucher_status!=2";
    $rs_type = $dbcon->query($query);
    $str = '<option value="" selected="selected">Choose</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['voucher_typeid'] == $eid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $row['voucher_typeid'] . '">' . $row['voucher_type_name'] . '</option>';
    }
    return $str;
}

function get_all_acc_type($dbcon, $id, $condition = '')
{
    $str = '';
    $query = "SELECT * FROM `mst_accounts` where edit_status=1 and status!=2 and acc_type_id!=0 " . $condition;
    $rs_acc = $dbcon->query($query);
    $str = '<option value="">Choose Account</option>';
    while ($rel_acc = brp_mysqli_fetch_assoc($rs_acc)) {
        $str .= '<optgroup label="' . $rel_acc['account_name'] . '" data-select2-id="' . $rel_acc['accountid'] . '">';
        $query = "SELECT * FROM `mst_accounts` as acc where acc.status!=2 and edit_status=0   and acc_type_id=" . $rel_acc['accountid'];
        $rs_dispatch = $dbcon->query($query);

        while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
            $sel = '';
            if ($rel['accountid'] == $id) {
                $sel = "selected='selected'";
            }
            $str .= '<option ' . $sel . ' value="' . $rel['accountid'] . '">' . stripslashes($rel['account_name']) . '</option>';
        }
        $str .= '</optgroup>';
    }

    return $str;
}

function get_accounts_typewise($dbcon, $id, $typeid)
{
    $str = '';
    $query = "SELECT acc.* FROM `mst_accounts` as acc left join mst_accounts as parentacc on parentacc.accountid=acc.acc_type_id left join mst_acc_type as acctype on parentacc.acc_type_id=acctype.acc_type_id where acc.status=0 and acc.view_status=0 and acctype.acc_type_name='" . $typeid . "' and acc.company_id in (0,$_SESSION[company_id]) order by acc.account_name";
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Account Type</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['accountid'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['accountid'] . '">' . $rel['account_name'] . '</option>';
    }
    return $str;
}

function get_opening_balance($acc_id, $dbcon, $acc_type)
{
    $query = "SELECT opn_balance,
        (select sum(tran_amount) from tbl_banktransaction where debit_accid=" . $acc_id . " and status=0) as debit 
           ,(SELECT sum(amount)  FROM `tbl_passbookentry` where acc_id=" . $acc_id . " and status=0 and typeid=1) as pdebit,(select sum(tran_amount) from tbl_banktransaction where credit_accid=" . $acc_id . "  and status=0) as credit ,(SELECT sum(amount)  FROM `tbl_passbookentry` where acc_id=" . $acc_id . " and status=0 and typeid=2) as pcredit
           FROM `account_mst` where acc_id=" . $acc_id . " and acc_status=0 and company_id=" . $_SESSION['company_id'] . " and acc_type=" . $acc_type;
    $rel = mysqli_fetch_assoc($dbcon->query($query));
    $opn_balance = $rel['opn_balance'] + $rel['credit'] + $rel['pcredit'] - ($rel['debit'] + $rel['pdebit']);

    return $opn_balance;
}

function getaccount($dbcon, $bankid, $condition)
{
    $bank = '';
    $qry = "SELECT acc_id,bank_name,branch_name,acc_number,acc_name FROM `account_mst` as accmst left join bank_mst as bmst on bmst.bankid=accmst.bankid where acc_status=0 and accmst.company_id=" . $_SESSION['company_id'] . " and " . $condition;
    $rs_type = $dbcon->query($qry);
    $bank .= '<option value="">Choose Account</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['acc_id'] == $bankid) {
            $sel = 'selected="selected"';
        }
        $bank .= '<option ' . $sel . ' value="' . $row['acc_id'] . '">' . $row['acc_name'] . ' (' . $row['bank_name'] . ' - ' . $row['branch_name'] . ')</option>';
    }
    return $bank;
}

function getpaymentmode($dbcon, $id)
{
    $str = '';
    $query = "select * from tbl_payment_mode where status=0";
    $rs_payment = $dbcon->query($query);
    echo '<option value="">Choose Mode</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_payment)) {
        $sel = '';
        if ($rel['paymentmodeid'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['paymentmodeid'] . '">' . $rel['payment_mode'] . '</option>';
    }
    return $str;
}

function get_product_tax_income($dbcon, $product_amount, $formulaid, $type = 'exclusive')
{
    $qry = "SELECT formula.*,tax.*,(select sum(tax_value)   FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=" . $formulaid . ") as tax_total  FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=" . $formulaid . " order by tax_value desc";
    $row = $dbcon->query($qry);
    $rate_total = $total = $product_amount;
    $i = 1;
    while ($tax = brp_mysqli_fetch_assoc($row)) {
        $info['tax_name' . $i] = $tax['tax_name'];
        if ($type == 'exclusive') {
            $info['tax_amount' . $i] = $tax_amount = ($total) * $tax['tax_value'] / 100;
            $rate_total += $tax_amount;
        } else if ($type == 'inclusive') {
            $tax_amount = $total - (($total * 100) / (100 + $tax['tax_total']));
            $tax_amount = $tax_amount / 2;
            $info['tax_amount' . $i] = $tax_amount;
            $rate_total -= $tax_amount;
        }
        $info['tax_name'][] = $tax['tax_name'];
        $info['tax_amount'][] = $tax_amount;
        $i++;
    }
    for ($j = $i; $j <= 3; $j++) {
        $info['tax_name' . $i] = '';
        $info['tax_amount' . $i] = '';
    }
    $info['total'] = $rate_total;
    return $info;
}

function map_arr($a, $b, $specifiar)
{
    return $a . ' : ' . $b;
}

function add_product_tax_data($mstid, $trn_col_name, $emode, $rs_tax, $dbcon)
{
    $info_tax['mst_id'] = $mstid;
    $info_tax['tax_for'] = $emode;
    while ($rel_tax = brp_mysqli_fetch_assoc($rs_tax)) {
        $info_tax['trn_id'] = $rel_tax[$trn_col_name];
        $info_tax['tax_id'] = $rel_tax['tax_id'];
        $info_tax['tax_rate'] = $rel_tax['tax_value'];
        $info_tax['tax_amount'] = $rel_tax['product_amount'] * $rel_tax['tax_value'] / 100;
        add_record('tbl_tax_trn', $info_tax, $dbcon);
    }
}

function get_company_cash_accounts($dbcon)
{
    $qry = "select acc_id,acc_type from account_mst where acc_type=1 and acc_status=0 and company_id=" . $_SESSION['company_id'];
    $rel_acc = brp_mysqli_fetch_assoc($dbcon->query($qry));
    $acc_id = $rel_acc['acc_id'];
    return $acc_id;
}

function get_p_and_l_stock($startdate, $enddate, $dbcon)
{
    if (isset($enddate) && !empty($enddate)) {
        $where_date = " between '" . $startdate . "' and '" . $enddate . "'";
    } else {
        $where_date = " < '" . $startdate . "'";
    }
    $query = "select sum(productavgprice*open_stock_qty) as stock_amount from (select product_id,((product_stock+purchaseqty+creditqty)-(invoiceqty+debitqty)) as open_stock_qty,((stock_amt+purchaseamt)/(product_stock+purchaseqty)) as productavgprice from (SELECT pdt.product_id,pdt.product_stock,pdt.product_stock_rate,(pdt.product_stock*pdt.product_stock_rate) as stock_amt,COALESCE(purchase.purchaseqty,0) as purchaseqty,COALESCE(purchase.purchaseamt,0) as purchaseamt,COALESCE(purdebit.purdebitqty,0) as debitqty,COALESCE(purdebit.purdebitamt,0) as debitamt,COALESCE(invoice.invoiceqty,0) as invoiceqty,COALESCE(invoice.invoiceamt,0) as invoiceamt,COALESCE(innote.creditqty,0) as creditqty,COALESCE(innote.creditamt,0) as creditamt FROM `product_mst` as pdt 
            left join ( SELECT potrn.product_id,sum(product_qty) as purchaseqty,sum(product_amount) as purchaseamt FROM `tbl_pono` as pomst inner join tbl_potrancation as potrn on potrn.po_id=pomst.po_id where pomst.status=0 and po_date  " . $where_date . " and pomst.company_id=" . $_SESSION['company_id'] . " group by potrn.product_id) as purchase on pdt.product_id=purchase.product_id
            left join (SELECT product_id,sum(product_qty) as purdebitqty,sum(product_amount) as purdebitamt  FROM `tbl_purchasedebitnote` as pomst inner join tbl_purchasedebitnotetrn as potrn on potrn.purchasedebitnote_id=pomst.purchasedebitnote_id where pomst.debitnote_status=0 and debit_date  " . $where_date . " and pomst.company_id=" . $_SESSION['company_id'] . " group by potrn.product_id ) as purdebit on pdt.product_id=purdebit.product_id 
            left join (SELECT product_id,sum(product_qty) as invoiceqty,sum(product_amount)as invoiceamt  FROM `tbl_invoice` as invmst inner join tbl_invoicetrn as invtrn on invmst.invoice_id=invtrn.invoice_id where invmst.invoice_status=0 and invoice_date  " . $where_date . " and invmst.company_id=" . $_SESSION['company_id'] . " group by invtrn.product_id ) as invoice on pdt.product_id=invoice.product_id
            left join (SELECT product_id,sum(product_qty) as creditqty,sum(product_amount) as creditamt  FROM `tbl_invoicenote` as invmst inner join tbl_invoicenotetrn as invtrn on invmst.invoicenote_id=invtrn.invoicenote_id where invmst.noteused_status=0 and note_date  " . $where_date . " and invmst.company_id=" . $_SESSION['company_id'] . " group by invtrn.product_id ) as innote on pdt.product_id=innote.product_id) as product_data  ) as openstock";
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel['stock_amount'];
}
function get_p_and_l_sale($startdate, $enddate, $dbcon)
{
    if (isset($enddate) && !empty($enddate)) {
        $where_date = " between '" . $startdate . "' and '" . $enddate . "'";
    } else {
        $where_date = " < '" . $startdate . "'";
    }
    $query = "select sum(productavgprice) as sale_amount from (select product_id,(invoiceqty-creditqty) as sale_qty,(invoiceamt-creditamt) as productavgprice from (SELECT pdt.product_id,COALESCE(invoice.invoiceqty,0) as invoiceqty,COALESCE(invoice.invoiceamt,0) as invoiceamt,COALESCE(innote.creditqty,0) as creditqty,COALESCE(innote.creditamt,0) as creditamt FROM `product_mst` as pdt 
            left join ( SELECT product_id,sum(product_qty) as invoiceqty,sum(product_amount)as invoiceamt  FROM `tbl_invoice` as invmst inner join tbl_invoicetrn as invtrn on invmst.invoice_id=invtrn.invoice_id where invmst.invoice_status=0 and invoice_date " . $where_date . "  and invmst.company_id=" . $_SESSION['company_id'] . " group by invtrn.product_id ) as invoice on pdt.product_id=invoice.product_id
            left join ( SELECT product_id,sum(product_qty) as creditqty,sum(product_amount) as creditamt  FROM `tbl_invoicenote` as invmst inner join tbl_invoicenotetrn as invtrn on invmst.invoicenote_id=invtrn.invoicenote_id where invmst.noteused_status=0 and note_date " . $where_date . " and invmst.company_id=" . $_SESSION['company_id'] . " group by invtrn.product_id ) as innote on pdt.product_id=innote.product_id) as product_data  ) as openstock";
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel['sale_amount'];
}
function get_p_and_l_purchase($startdate, $enddate, $dbcon)
{
    if (isset($enddate) && !empty($enddate)) {
        $where_date = " between '" . $startdate . "' and '" . $enddate . "'";
    } else {
        $where_date = " < '" . $startdate . "'";
    }
    $query = "select sum(productavgprice*purchase_qty) as purchase_amount from (select product_id,((purchaseqty)-(debitqty)) as purchase_qty,(purchaseamt)/(purchaseqty) as productavgprice from (SELECT pdt.product_id,pdt.product_stock,pdt.product_stock_rate,(pdt.product_stock*pdt.product_stock_rate) as stock_amt,COALESCE(purchase.purchaseqty,0) as purchaseqty,COALESCE(purchase.purchaseamt,0) as purchaseamt,COALESCE(purdebit.purdebitqty,0) as debitqty,COALESCE(purdebit.purdebitamt,0) as debitamt FROM `product_mst` as pdt 
            left join ( SELECT potrn.product_id,sum(product_qty) as purchaseqty,sum(product_amount) as purchaseamt FROM `tbl_pono` as pomst inner join tbl_potrancation as potrn on potrn.po_id=pomst.po_id where pomst.status=0 and po_date " . $where_date . " and pomst.company_id=" . $_SESSION['company_id'] . "  group by potrn.product_id) as purchase on pdt.product_id=purchase.product_id
            left join (SELECT product_id,sum(product_qty) as purdebitqty,sum(product_amount) as purdebitamt  FROM `tbl_purchasedebitnote` as pomst inner join tbl_purchasedebitnotetrn as potrn on potrn.purchasedebitnote_id=pomst.purchasedebitnote_id where pomst.debitnote_status=0 and debit_date " . $where_date . " and pomst.company_id=" . $_SESSION['company_id'] . " group by potrn.product_id ) as purdebit on pdt.product_id=purdebit.product_id 
        ) as product_data  ) as purchase";
    $rel = mysqli_fetch_assoc($dbcon->query($query));
    return $rel['purchase_amount'];
}
function get_p_and_l_direct_expense_spare($startdate, $enddate, $dbcon)
{
    if (isset($enddate) && !empty($enddate)) {
        $where_date = " between '" . $startdate . "' and '" . $enddate . "'";
    } else {
        $where_date = " < '" . $startdate . "'";
    }
    $query = "SELECT sum(s_amount) as direct_expense FROM `tbl_complain_spare_part` where s_date " . $where_date . " and s_status=1 ";
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel['direct_expense'];
}
function get_p_and_l_direct_income($startdate, $enddate, $dbcon)
{
    if (isset($enddate) && !empty($enddate)) {
        $where_date = " between '" . $startdate . "' and '" . $enddate . "'";
    } else {
        $where_date = " < '" . $startdate . "'";
    }
    $query = "SELECT cl_date,sum(cl_amount) as direct_income FROM `tbl_complain_close_detail` where cl_date " . $where_date;
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel['direct_income'];
}

function get_p_and_l_direct_income_spare($startdate, $enddate, $dbcon)
{
    if (isset($enddate) && !empty($enddate)) {
        $where_date = " between '" . $startdate . "' and '" . $enddate . "'";
    } else {
        $where_date = " < '" . $startdate . "'";
    }
    $query = "SELECT sc_date,sum(sc_amount) as direct_income FROM `tbl_complain_close_spare_part` where sc_date " . $where_date;
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel['direct_income'];
}

function get_p_and_l_total_indirect_income($startdate, $enddate, $dbcon)
{
    if (isset($enddate) && !empty($enddate)) {
        $where_date = " between '" . $startdate . "' and '" . $enddate . "'";
    } else {
        $where_date = " < '" . $startdate . "'";
    }
    $query = "SELECT inctrn.account_mst_id,account_name,sum(income_amount) as income_amount FROM income_mst as incmst inner join income_trn as inctrn on inctrn.income_mstid=incmst.incomeid 
        left join mst_accounts as mstacc on inctrn.account_mst_id=mstacc.accountid
        where incmst.income_date " . $where_date . " and incmst.mst_status=0 and incmst.company_id=" . $_SESSION['company_id'];
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel['income_amount'];
}
function get_p_and_l_total_indirect_expense($startdate, $enddate, $dbcon)
{
    if (isset($enddate) && !empty($enddate)) {
        $where_date = " between '" . $startdate . "' and '" . $enddate . "'";
    } else {
        $where_date = " < '" . $startdate . "'";
    }
    $query = "SELECT ex_id,sum(g_total) as expense_amount,expense_date FROM tbl_expense_detail  where expense_date " . $where_date . " and expense_approve_status=1 and expense_status=0";
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel['expense_amount'];
}
function get_p_and_l_indirect_expense($startdate, $enddate, $dbcon)
{
    if (isset($enddate) && !empty($enddate)) {
        $where_date = " between '" . $startdate . "' and '" . $enddate . "'";
    } else {
        $where_date = " < '" . $startdate . "'";
    }
    $query = "select ed.ex_id,ed.expense_date,sum(e.total) as eamount,e.expense_mstid,e.account_mst_id from tbl_expense_detail as ed inner join expense_trn as e on expense_mstid=ed.ex_id where expense_date " . $where_date . " and expense_approve_status=1 and expense_status=0 group by e.account_mst_id";
    $rs = ($dbcon->query($query));
    return $rs;
}

function get_p_and_l_cost_of_good_sold($startdate, $enddate, $dbcon)
{
    $where_date = " between '" . $startdate . "' and '" . $enddate . "'";
    $where_date1 = " < '" . $enddate . "'";
    $query = "select sum(productavgprice*sale_qty) as product_stock_amount from (select product_id,((invoiceqty-creditqty)) as sale_qty,((purchaseamt)/(purchaseqty)) as productavgprice from (SELECT pdt.product_id,pdt.product_stock,pdt.product_stock_rate,(pdt.product_stock*pdt.product_stock_rate) as stock_amt,COALESCE(purchase.purchaseqty,0) as purchaseqty,COALESCE(purchase.purchaseamt,0) as purchaseamt,COALESCE(purdebit.purdebitqty,0) as debitqty,COALESCE(purdebit.purdebitamt,0) as debitamt,COALESCE(invoice.invoiceqty,0) as invoiceqty,COALESCE(invoice.invoiceamt,0) as invoiceamt,COALESCE(innote.creditqty,0) as creditqty,COALESCE(innote.creditamt,0) as creditamt FROM `product_mst` as pdt 
        left join ( SELECT potrn.product_id,sum(product_qty) as purchaseqty,sum(product_amount) as purchaseamt FROM `tbl_pono` as pomst inner join tbl_potrancation as potrn on potrn.po_id=pomst.po_id where pomst.status=0 and po_date " . $where_date1 . " and pomst.company_id=" . $_SESSION['company_id'] . " group by potrn.product_id) as purchase on pdt.product_id=purchase.product_id
        left join (SELECT product_id,sum(product_qty) as purdebitqty,sum(product_amount) as purdebitamt  FROM `tbl_purchasedebitnote` as pomst inner join tbl_purchasedebitnotetrn as potrn on potrn.purchasedebitnote_id=pomst.purchasedebitnote_id where pomst.debitnote_status=0 and debit_date " . $where_date1 . " and pomst.company_id=" . $_SESSION['company_id'] . " group by potrn.product_id ) as purdebit on pdt.product_id=purdebit.product_id 
        left join (SELECT product_id,sum(product_qty) as invoiceqty,sum(product_amount)as invoiceamt  FROM `tbl_invoice` as invmst inner join tbl_invoicetrn as invtrn on invmst.invoice_id=invtrn.invoice_id where invmst.invoice_status=0 and invoice_date " . $where_date . " and invmst.company_id=" . $_SESSION['company_id'] . " group by invtrn.product_id ) as invoice on pdt.product_id=invoice.product_id
        left join (SELECT product_id,sum(product_qty) as creditqty,sum(product_amount) as creditamt  FROM `tbl_invoicenote` as invmst inner join tbl_invoicenotetrn as invtrn on invmst.invoicenote_id=invtrn.invoicenote_id where invmst.noteused_status=0 and note_date " . $where_date . " and invmst.company_id=" . $_SESSION['company_id'] . " group by invtrn.product_id ) as innote on pdt.product_id=innote.product_id) as product_data  ) as openstock ";
    $rel = mysqli_fetch_assoc($dbcon->query($query));

    return $rel['product_stock_amount'];
}

function get_expense_Complain($dbcon, $id)
{
    $query = "select et.expense_trnid,et.expense_mstid,et.account_mst_id,e.account_name  from expense_trn as et left join mst_accounts as e on e.accountid=et.account_mst_id where et.expense_mstid=$id";

    $row = $dbcon->query($query);

    $ams = array();
    while ($rel = brp_mysqli_fetch_assoc($row)) {
        $ams[] = $rel['account_name'];
    }
    return implode(",", $ams);
}

function get_expense_by_id($dbcon, $id)
{
    $query = "select accountid,account_name  from mst_accounts where accountid='$id'";

    $row = $dbcon->query($query);

    $rel = brp_mysqli_fetch_assoc($row);

    return $rel['account_name'];
}

function get_product_detail($dbcon, $id)
{
    $query = "select * from product_mst where product_id='$id'";

    $row = $dbcon->query($query);

    $rel = brp_mysqli_fetch_assoc($row);

    return $rel;
}

function get_party_detail($dbcon, $id)
{
    $query = "select * from tbl_customer where cust_id='$id'";

    $row = $dbcon->query($query);

    $rel = brp_mysqli_fetch_assoc($row);

    return $rel;
}

function get_qty_report($dbcon, $pid, $ctype, $status, $eid, $days)
{
    //$query="select ct.complaint_id,ct.product_id,ct.close_status,c.complaint_type_id,count(product_id) as ct_count from  tbl_complaint_trn as ct left join tbl_complaint as c on c.complaint_id=ct.complaint_id where c.complaint_type_id='$ctype' and ct.product_id='$pid' and ct.close_status='$status'";
    $where = '';
    $where1 = '';

    if ($status == 2) {
        $where .= " and f.fl_f_status='2' or f.fl_f_status='3'";
    } else {
        $where .= " and f.fl_f_status='$status'";
    }

    if ($days != '') {
        $where1 .= " and DATE(c.complaint_date) > (NOW() - INTERVAL 30 DAY)";
    }

    $query = "select count(f.fl_cid) as cid,c.complaint_type_id,c.complaint_status,f.fl_e_id,ct.product_id,c.complaint_date from tbl_follow as f left join tbl_complaint as c on c.complaint_id=f.fl_cid left join tbl_complaint_trn as ct on ct.complaint_id=c.complaint_id where c.complaint_status='0' and ct.product_id='$pid' and f.fl_e_id='$eid' " . $where . "  " . $where1 . "  group by f.fl_f_status";

    $row = $dbcon->query($query);

    $result = brp_mysqli_fetch_array($row);

    return $result['cid'];
}

function get_product_complain($dbcon, $cid)
{
    $qrycust1 = "select c.complaint_trn_id,c.complaint_id,c.product_id,p.product_name from tbl_complaint_trn as c left join product_mst as p on p.product_id=c.product_id where c.complaint_id=" . $cid;

    $rowc1 = $dbcon->query($qrycust1);

    $arr = array();
    while ($result1 = brp_mysqli_fetch_array($rowc1)) {
        $arr[] = $result1['product_name'];
    }

    return implode(",", $arr);
}

function getvender($dbcon, $id)
{
    $query = "select * from tbl_customer where cust_status=0 and company_id in (0," . $_SESSION['company_id'] . ") and party_type in (0,2) ";
    $rs_cust = $dbcon->query($query);
    //echo '<option value="">Choose </option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['cust_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['cust_id'] . '">' . $rel['company_name'] . '</option>';
    }
}

function getincome_billno($dbcon, $vendorid, $eid)
{
    $query = "select * from  income_mst where mst_status=0 AND g_total>paid_amount AND customerid=" . $vendorid;
    $rs_dispatch = $dbcon->query($query);
    echo '<option value="">Choose Invoice</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['incomeid'] == $eid) {
            $sel = 'selected="selected"';
        }
        echo '<option ' . $sel . ' value="' . $rel['incomeid'] . '">' . $rel['invoice_no'] . '</option>';
    }
}

function get_income_customer_due_amount($customerid, $dbcon)
{
    $query = "select cust.opening_balance,cust.balance_typeid,(SELECT sum(g_total) FROM `income_mst` as inv where inv.customerid=cust.cust_id and inv.mst_status!=2) as invoice_amount,(SELECT sum(paid_amount) FROM `tbl_receipt` as rec where rec.cust_id=cust.cust_id and rec.status!=2 and receipt_flag='income') as paid_amount from tbl_customer as cust where cust.cust_id=" . $customerid;
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    $op_balance = 0;
    if (!empty($rel['opening_balance'])) {
        $op_balance = ($rel['balance_typeid'] == "1" ? -($rel['opening_balance']) : $rel['opening_balance']);
    }
    $amount = $op_balance + $rel['invoice_amount'] - $rel['paid_amount'];
    return $amount;
}

function get_chequeno($acc_id, $dbcon)
{
    $query = "SELECT * from account_mst where acc_id=" . $acc_id;
    $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
    return $rel['acc_chequeno'];
}
function get_serise_common($dbcon, $invoicetype)
{

    $row = array();
    $query1 = "select * from  tbl_invoicetype where invoice_type='" . $invoicetype . "' and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    //echo $query1;
    $rows = mysqli_fetch_assoc($dbcon->query($query1));
    $id = $rows['taxinvoice_start'];
    $id = $id + 1;

    //$id=$rows['Max(taxinvoice_start)']+1;
    //$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
    //$end = $start+1;
    if ($rows['invoice_format'] == '2') {
        $info['paymentno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
    } else if ($rows['invoice_format'] == '1') {
        $info['paymentno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
    } else if ($rows['invoice_format'] == '3') {
        $info['paymentno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
    } else {
        $info['paymentno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
    }
    // var_dump($info);
    return $info;
}

function get_all_emp_type($dbcon, $id)
{
    $query = "select * from tbl_emp_type_master where etype_status=0 ";
    $rs_cust = $dbcon->query($query);
    //echo '<option value="">Choose </option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['etype_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['etype_id'] . '">' . $rel['etype_type'] . '</option>';
    }
}

function get_emp_id_transfer($dbcon, $acct)
{
    $query = "select accountid,emp_id from mst_accounts where accountid=$acct";
    $rs_cust = $dbcon->query($query);
    $row = brp_mysqli_fetch_array($rs_cust);
    return $row['emp_id'];
    //echo '<option value="">Choose </option>';

}

function get_customer_complain($dbcon, $customer, $id)
{
    $query = "select * from tbl_complaint where cust_id='$customer' and pay_status='0'";
    $rs_cust = $dbcon->query($query);
    echo '<option value="">Choose Complaint</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['complaint_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['complaint_id'] . '">' . $rel['complaint_no'] . '</option>';
    }
}

function get_customer_complain_closed($dbcon, $customer, $id)
{

    $query = "select c.*,cd.cl_date from tbl_complaint as c left join tbl_complain_close_detail as cd on cd.cl_id=c.complaint_id where c.cust_id='$customer' and c.pay_status='0' and c.followup_status='4' and cd.cl_date < NOW() - 7 ";
    $rs_cust = $dbcon->query($query);
    echo '<option value="">Choose Complaint</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['complaint_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['complaint_id'] . '">' . $rel['complaint_no'] . '</option>';
    }
}

/*
function get_customer_complain_expense($dbcon,$customer,$id)
{
    $query="select * from tbl_complaint where cust_id='$customer'";
    $rs_cust=$dbcon->query($query);	
    echo '<option value="">Choose Complaint</option>';
    while($rel=brp_mysqli_fetch_assoc($rs_cust))
    {	
        $sel='';
        if($rel['complaint_id']==$id)
        {
            $sel="selected='selected'";
        }
        echo '<option '.$sel.' value="'.$rel['complaint_id'].'">'.$rel['complaint_no'].'</option>';
    }
}
*/
function get_customer_complain_expense($dbcon, $id, $mode, $cust_id)
{
    $cur_date = date("Y-m-d");
    $whr = '';
    if ($cust_id) {
        $whr .= " and comp.cust_id=$cust_id";
    }

    //Amish Soni 04-09-2020
    if ($_SESSION['user_type'] == '3' && $mode == 'Add') {
        $emp_id = getEmployeeIdUser($dbcon, $_SESSION['user_id']);
        // $query="SELECT comp.complaint_id,comp.complaint_no,ledger.l_name FROM `tbl_follow` as flp
        // 	inner join tbl_complaint as comp on comp.complaint_id=flp.fl_cid
        // 	left join tbl_ledger as ledger on ledger.l_id=comp.cust_id
        // 	where comp.complaint_status=0 and flp.fl_e_id=$emp_id $whr and DATE(flp.fl_date) > (NOW() - INTERVAL 7 DAY) group by flp.fl_cid ";
        $whr .= " and flp.fl_e_id=$emp_id and DATE(flp.fl_date) > (NOW() - INTERVAL 7 DAY) ";
    } else {
        // $query="SELECT comp.complaint_id,comp.complaint_no,ledger.l_name FROM `tbl_follow` as flp
        // 	inner join tbl_complaint as comp on comp.complaint_id=flp.fl_cid
        // 	left join tbl_ledger as ledger on ledger.l_id=comp.cust_id
        // 	where comp.complaint_status=0 $whr group by flp.fl_cid ";

    }

    //display data only if current date is less than (mdate + 7 days)
    $query = "SELECT comp.complaint_id,comp.complaint_no,ledger.l_name FROM `tbl_follow` as flp
    inner join tbl_complaint as comp on comp.complaint_id=flp.fl_cid
    left join tbl_ledger as ledger on ledger.l_id=comp.cust_id
    where comp.complaint_status=0 $whr AND DATE(NOW()) <= DATE(comp.mdate + INTERVAL 7 DAY) 
    group by flp.fl_cid ";

    //$query="select * from tbl_complaint where complaint_status=0 and DATE(complaint_date) > (NOW() - INTERVAL 7 DAY) ";
    $rs_cust = $dbcon->query($query);
    while ($rel = brp_mysqli_fetch_assoc($rs_cust)) {
        $sel = '';
        if ($rel['complaint_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['complaint_id'] . '">' . $rel['complaint_no'] . ' - ' . $rel['l_name'] . '</option>';
    }
}

function get_complain_payment_pending($dbcon, $complain)
{
    $query = "select  sum(paid_amount) as total,bill_id  from complain_payment_trn  where bill_id='$complain'";
    $rs_cust = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($rs_cust);
    $paid = $rel['total'];

    /*$query1="select  sum(total_amount) as total_due,bill_id  from payment_trn where bill_id='$complain'";
    $rs_cust1=$dbcon->query($query1);
    $rel1=brp_mysqli_fetch_assoc($rs_cust1);
    $due=$rel1['total_due'];*/

    $service_charge = get_service_charge($dbcon, $complain);
    $spare_charge = get_spare_part_rate($dbcon, $complain);
    $due = $service_charge + $spare_charge;

    $grand_total = $due - $paid;

    return $grand_total;
}

function get_all_acc_type_emp($dbcon, $empid)
{
    $query = "select emp_id,accountid from mst_accounts where emp_id='$empid'";
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row);
    return $rel['accountid'];
}
function get_all_group($dbcon, $id, $where = '', $primary = "")
{
    $str = '';
    $query = "Select * from tbl_group where g_status=0 " . $where;
    $rs_type = $dbcon->query($query);
    if ($id == '0') {
        $psel = 'selected="selected"';
    }
    $str = '<option value="" >--Choose Group--</option>';
    if ($primary != '0') {
        $str .= '<option value="0" ' . $psel . ' >PRIMARY</option>';
    }
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['g_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option data-formgroup="' . $row['form_id'] . '" ' . $sel . ' value="' . $row['g_id'] . '">' . $row['g_name'] . '</option>';
    }
    //echo $id;
    return $str;
}
function get_all_groups($dbcon, $id, $where = '', $primary = "")
{
    // $str = '';
    $query = "Select * from tbl_group where g_status=0 " . $where . " ORDER BY g_name ASC";
    $rs_type = $dbcon->query($query);
    $id_array = explode(",", $id);
    if (in_array('0', $id_array)) {
        $psel = 'selected="selected"';
    }
    $str = '<option value="" >--Choose Group--</option>';
    if ($primary != '0') {
        $str .= '<option value="0" ' . $psel . ' >PRIMARY</option>';
    }
    while ($row = mysqli_fetch_assoc($rs_type)) {
        // $sel = '';
        if (in_array($row['g_id'], $id_array)) {
            $sel = 'selected';
        }

        $str .= '<option ' . $sel . ' value="' . $row['g_id'] . '">' . $row['g_name'] . '</option>';
    }
    //echo $id;
    return $str;
}
function get_grp_by_id($dbcon, $id)
{
    $query = "select * from tbl_group where g_id='$id'";
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row);
    return $rel['g_name'];
}

function get_sales_order($dbcon, $id, $branch_id)
{
    $str = '';

    $where = '';
    if ($branch_id) {
        $where .= " and branch_id=" . $branch_id;
    }
    if ($id) {
        $where .= " and cust_id=" . $id;
    }

    $query = "select * from tbl_sales_order where sales_order_status=0 and approve_status!=0  and invoice_status=0 and company_id=" . $_SESSION['company_id'] . $where;
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Sales Order</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        /*if($rel['sales_order_id']==$id)
        {$sel ="selected='selected'";}*/
        $str .= '<option ' . $sel . ' value="' . $rel['sales_order_id'] . '">' . $rel['sales_order_no'] . '</option>';
    }
    return $str;
}

function get_sales_order_due_report($dbcon)
{
    $str = '';

    $query = "select * from tbl_sales_order where sales_order_status=0  and invoice_status=0 and revise_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Sales Order</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        /*if($rel['sales_order_id']==$id)
        {$sel ="selected='selected'";}*/
        $str .= '<option ' . $sel . ' value="' . $rel['sales_order_id'] . '">' . $rel['sales_order_no'] . '</option>';
    }
    return $str;
}

function get_sales_order_indent($dbcon, $edit_id = '')
{
    $str = '';

    $query = "select * from tbl_sales_order where sales_order_status=0 and approve_status!=0 and invoice_status=0 and short_close_status=0 and revise_status = 0 and company_id=" . $_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Sales Order</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        if ($edit_id == $rel['sales_order_id']) {
            $select = 'selected';
        } else {
            $select = '';
        }

        $str .= "<option value='" . $rel['sales_order_id'] . "' " . $select . ">" . $rel['sales_order_no'] . "</option>";
        // $str .= '<option ' . $sel . ' value="' . $rel['sales_order_id'] . '">' . $rel['sales_order_no'] . '</option>';
    }
    return $str;
}

function get_all_expense($dbcon, $id)
{
    $query = "select l_id,l_name from tbl_ledger  where l_status=0 and l_form='expense_form' and company_id in (0,$_SESSION[company_id])";
    $rs_expense = $dbcon->query($query);
    echo '<option value="">--Select Expense--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_expense)) {
        $sel = '';
        if ($rel['l_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['l_id'] . '">' . $rel['l_name'] . '</option>';
    }
}

function get_ledger_expense_by_id($dbcon, $id)
{
    $query = "select l_id,l_name from tbl_ledger where l_id='$id'";
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row);
    return $rel['l_name'];
}

function get_total_qty_by_po($dbcon, $id)
{
    $query = "select sum(product_qty) as pqty from tbl_potrancation where po_id='$id'";
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row);
    return $rel['pqty'];
}

function get_group_from_expense($dbcon, $id)
{
    $query = "select e.expense_id,e.expense_head_id,g.g_id from expense_mst as e left join tbl_group as g  on e.expense_head_id=g.g_id where e.expense_id='$id'";
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row);
    return $rel['g_id'];
}

function get_product_tax_common_expense($dbcon, $product_amount, $formulaid, $type = 'exclusive')
{
    $qry = "SELECT formula.*,tax.*,(select sum(tax_value)   FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=" . $formulaid . ") as tax_total  FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=" . $formulaid . " order by tax_value desc";
    $row = $dbcon->query($qry);
    $rate_total = $total = $product_amount;
    $i = 1;
    while ($tax = brp_mysqli_fetch_assoc($row)) {
        $info['tax_name' . $i] = $tax['tax_name'];
        if ($type == 'exclusive') {
            $info['tax_amount' . $i] = $tax_amount = ($total) * $tax['tax_value'] / 100;
            $rate_total += $tax_amount;
        } else if ($type == 'inclusive') {
            $tax_amount = $total - (($total * 100) / (100 + $tax['tax_total']));
            $tax_amount = $tax_amount / 2;
            $info['tax_amount' . $i] = $tax_amount;
            $rate_total -= $tax_amount;
        }
        $info['tax_name'][] = $tax['tax_name'];
        $info['tax_amount'][] = $tax_amount;
        $i++;
    }
    for ($j = $i; $j <= 3; $j++) {
        $info['tax_name' . $i] = '';
        $info['tax_amount' . $i] = '';
    }
    $info['total'] = $rate_total;
    return $info;
}

function get_income_account($dbcon, $id)
{
    $query = "select * from income_master where inc_status=0";
    $rs_expense = $dbcon->query($query);
    echo '<option value="">--Select Income--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_expense)) {
        $sel = '';
        if ($rel['inc_id'] == $id) {
            $sel = "selected='selected'";
        }
        echo '<option ' . $sel . ' value="' . $rel['inc_id'] . '">' . $rel['inc_name'] . '</option>';
    }
}

function get_group_from_income($dbcon, $id)
{
    $query = "select inc_id,inc_group from income_master where inc_id='$id'";
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row);
    return $rel['inc_group'];
}

function load_cust_prowise_model_invoice($dbcon, $id, $product_id, $cust_id)
{
    $str = '';
    $query = "select sold_pro.model_id,model.model_name from tbl_cust_sold_pro as sold_pro
        inner join model_mst as model on model.model_id=sold_pro.model_id
        where cust_sold_pro_status=0  and sold_pro.product_id=" . $product_id . " and sold_pro.company_id in(0,$_SESSION[company_id]) ";
    $rs_product = $dbcon->query($query);
    $str = '<option value="">Choose Model</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        $sel = '';
        if ($rel['model_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['model_id'] . '">' . $rel['model_name'] . '</option>';
    }
    return $str;
}

function get_expense_by_invoice($dbcon, $id)
{
    $query = "select sum(exp_e_amount) as sum_amount from tbl_invoice_exp where exp_in_id='$id'";
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row);
    return $rel['sum_amount'];
}

function get_all_category($dbcon, $id, $where = '')
{
    $str = '';
    $query = "Select * from tbl_category where cat_status=0 and company_id=" . $_SESSION['company_id'] . $where;
    $rs_type = $dbcon->query($query);
    if ($id == '0') {
        $psel = 'selected="selected"';
    }
    $str = '<option value="">--Choose Category--</option>';
    $str .= '<option value="0"  ' . $psel . '  >PRIMARY</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        // $sel = '';
        if ($row['cat_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['cat_id'] . '">' . $row['cat_name'] . '</option>';
    }
    return $str;
}

function get_all_reciclare_category($dbcon, $id, $where = '')
{
    $str = '';
    $query = "Select * from tbl_category_reciclare where cat_status=0 " . $where;
    $rs_type = $dbcon->query($query);
    if ($id == '0') {
        $psel = 'selected="selected"';
    }
    $str = '<option value="">--Choose Category--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        $sel = '';
        if ($row['rcat_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['rcat_id'] . '">' . $row['cat_name'] . '</option>';
    }
    return $str;
}

function get_category_by_id($dbcon, $id)
{
    $query = "select * from tbl_category where cat_id='$id'";
    $row = $dbcon->query($query);
    $rel = brp_mysqli_fetch_array($row);
    return $rel['cat_name'];
}

function get_branch($dbcon, $eid)
{
    $query = "select branch_id,branch_name from branch_mst where branch_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Branch</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['branch_id'] == $eid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['branch_id'] . '">' . $rel['branch_name'] . '</option>';
    }
    return $str;
}

function get_bom_producttype($dbcon, $id)
{
    $pro_tml = '';
    $pro_tml .= '<option value="">Choose Product Type</option>';
    $pro_tml .= '<option value="0" ' . (($id == "0") ? "selected" : "") . '>Finish Product</option>';
    $pro_tml .= '<option value="1" ' . (($id == "1") ? "selected" : "") . '>Assembly Product</option>';
    $pro_tml .= '<option value="2" ' . (($id == "2") ? "selected" : "") . '>SUB ASSEMBLY</option>';
    $pro_tml .= '<option value="3" ' . (($id == "3") ? "selected" : "") . '>RAW MATERIAL</option>';
    $pro_tml .= '<option value="4" ' . (($id == "4") ? "selected" : "") . '>FINISH PART</option>';
    $pro_tml .= '<option value="5" ' . (($id == "5") ? "selected" : "") . '>BOI</option>';
    //$pro_tml.= '<option value="6" '.(($id=="6")?"selected":"").'>CAPITAL GOODS</option>';
    //$pro_tml.= '<option value="7" '.(($id=="7")?"selected":"").'>CONSUMABLE</option>';
    //$pro_tml.= '<option value="9" '.(($id=="9")?"selected":"").'>SCRAP</option>';
    return $pro_tml;
}

/*
    Code by Sanat ::  get bom product version - 05-08-2021
    START
*/

function get_bom_productversion($dbcon, $product_id, $bom_version_id)
{
    $whr = "";

    if ($bom_version_id != "") {
        $whr = " AND bom_version_id not in(" . $bom_version_id . ")";
    }
    $query = "select bom_version_id,version_name from pro_ms_bom_version where bom_version_status = 0 AND product_id=" . $product_id . $whr;

    $rs_version = $dbcon->query($query);
    $str = '<option value="">Choose Product Version</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_version)) {
        $sel = '';
        /*if($rel['branch_id']==$eid){
        $sel='selected="selected"';
    }*/
        $str .= '<option ' . $sel . ' value="' . $rel['bom_version_id'] . '">' . $rel['version_name'] . '</option>';
    }
    return $str;
}

/*
    Code by Sanat ::  get bom product version - 05-08-2021
    END
*/

function getBomGroup($dbcon, $eid)
{
    $query = "select * from tbl_bom_group where bg_status=0";
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Group</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['bg_id'] == $eid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['bg_id'] . '">' . $rel['bg_name'] . '</option>';
    }
    return $str;
}

function getrequiredproduct($dbcon, $id, $where)
{
    $str = '';
    
    $query = "SELECT p.product_id, p.product_name, p.product_desc 
              FROM product_mst AS p         
              WHERE p.product_status = 0 
              AND p.company_id IN (0, " . intval($_SESSION['company_id']) . ")";
    
    // Validate $where - check if it's not empty and doesn't end with = or incomplete operators
    if (!empty(trim($where))) {
        $where = trim($where);
        // Check if where clause ends with an incomplete operator
        if (!preg_match('/[=<>]\s*$/', $where)) {
            $query .= ' ' . $where;
        } else {
            // Log error or handle incomplete where clause
            error_log("Incomplete WHERE clause detected: " . $where);
        }
    }
    
    $query .= " ORDER BY p.product_name";
    
    $rs_dispatch = $dbcon->query($query);
    
    if (!$rs_dispatch) {
        die("Query Error: " . $dbcon->error . "<br>Query: " . htmlspecialchars($query));
    }
    
    $str .= '<option value="">Choose Product</option>';
    
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = ($rel['product_id'] == $id) ? "selected='selected'" : '';
        $str .= '<option ' . $sel . ' value="' . htmlspecialchars($rel['product_id']) . '">' . 
                htmlspecialchars($rel['product_name']) . " -- ( " . 
                htmlspecialchars($rel['product_desc']) . ')</option>';
    }
    
    return $str;
}

function get_product_type_by_id($dbcon, $type)
{

    $query = "SELECT product_type_id, product_type_name FROM pro_ms_product_type WHERE product_type_id=" . $type . " and product_type_status IN ('0','1')";
    $rs_dispatch = $dbcon->query($query);
    $rel = brp_mysqli_fetch_assoc($rs_dispatch);
    $retrun_type = $rel['product_type_name'];

    /*if ($type == '0') {
    $retrun_type = "Finish Product";
    } else if ($type == '1') {
    $retrun_type = "ASSEMBLY PRODUCT";
    } else if ($type == '2') {
    $retrun_type = "SUB ASSEMBLY";
    } else if ($type == '3') {
    $retrun_type = "RAW MATERIAL";
    } else if ($type == '4') {
    $retrun_type = "FINISH PART";
    } else if ($type == '5') {
    $retrun_type = "BOI";
    } else if ($type == '6') {
    $retrun_type = "CAPITAL GOODS";
    } else if ($type == '7') {
    $retrun_type = "CONSUMABLE";
    } else if ($type == '8') {
    $retrun_type = "SERVICE";
    } else if ($type == '9') {
    $retrun_type = "SCRAP";
    } else if ($type == '-1') {
    $retrun_type = "Project";
}*/

    return $retrun_type;
}

function get_product_purchase_rate($dbcon, $id)
{
    $get_pro_qry = "select product_id,product_purchase_rate from product_mst where product_id=" . $id;
    $get_qry_rrl = brp_mysqli_fetch_assoc($dbcon->query($get_pro_qry));
    return $get_qry_rrl['product_purchase_rate'];
}

function get_images_product($dbcon, $id)
{
    $q = "select * from product_mst_images where im_product=$id";
    $rel = $dbcon->query($q);
    $path = 'view/upload/product_images/';
    $str = "";
    $str .= "<table><tr>";
    while ($row = brp_mysqli_fetch_assoc($rel)) {
        $str .= '<td>
        <div class="img-wrap">
        <span class="close">&times;</span>
        <img src="' . ROOT . 'view/img/close_img.jpg" width="30" height="30">
        </div>
        <img src="' . ROOT . $path . $row['im_name'] . '" height="150" width="225" class="img-thumbnail" />

        </td>';
    }
    $str .= "</tr></table>";
    return $str;
}

function get_all_bank($dbcon, $id)
{
    $q = "select * from bank_mst where bank_status='0' order by bank_name";
    $r = $dbcon->query($q);

    $str = "";
    $str .= '<option value="">Choose Bank</option>';
    while ($rel = brp_mysqli_fetch_assoc($r)) {
        $sel = '';
        if ($rel['bankid'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option  value="' . $rel['bankid'] . '">' . $rel['bank_name'] . '</option>';
    }
    return $str;
}

function get_stock_by_branch($dbcon, $bid, $pid, $type)
{
    $q = "select * from tbl_branch_product_stock where branch_id='$bid' and product_id='$pid'";
    $r = $dbcon->query($q);
    $rel = brp_mysqli_fetch_assoc($r);
    if ($type == "stock") {
        return $rel['product_stock'];
    } else {
        return $rel['priority'];
    }
}

function get_all_customer($dbcon, $id)
{
    $q = "select * from tbl_customer order by company_name";
    $r = $dbcon->query($q);
    $str = "";
    $str .= "<option value=''>--Select customer--</option>";
    while ($rel = brp_mysqli_fetch_assoc($r)) {
        $str .= "<option value='" . $rel['cust_id'] . "'>" . $rel['company_name'] . "</option>";
    }
    return $str;
}

function membersTree($dbcon, $parentKey, $bomid)
{

    $sql = "SELECT * from tbl_bomtrn WHERE parent_id='$parentKey' and bom_id='$bomid' ";

    $result = $dbcon->query($sql);

    while ($value = mysqli_fetch_assoc($result)) {
        $pname = get_pro_field($dbcon, $value['product_id'], 'product_name');
        $id = $value['product_id'];
        $row1[$id]['id'] = $value['product_id'];
        $row1[$id]['name'] = get_pro_field($dbcon, $value['product_id'], 'product_name');
        $row1[$id]['text'] = "<input type='checkbox' name='sp_pr' id='sp_pr' value='" . $value['product_id'] . "'  />" . $pname;
        $row1[$id]['nodes'] = array_values(membersTree($dbcon, $value['product_id'], $bomid));
    }

    return $row1;
}

function get_bom_id($dbcon, $pid)
{
    $q = "select bom_id from tbl_bom where bom_product='$pid'";
    $rel = $dbcon->query($q);
    $row = brp_mysqli_fetch_assoc($rel);
    return $row['bom_id'];
}

function get_process_type($dbcon, $id)
{
    $str = '';
    $query = "select `process_type_id`,`process_type_name` from process_type_mst where process_type_status=0";
    $rs_product = $dbcon->query($query);
    $str .= '<option value="">--select Process Type--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {

        // $sel = '';
        if ($rel['process_type_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['process_type_id'] . '">' . $rel['process_type_name'] . '</option>';
    }
    return $str;
}

function get_all_process($dbcon, $id)
{
    $str = '';
    $query = "select `process_id`,`process_name` from process_mst where process_status=0 and company_id=" . $_SESSION['company_id'];
    $rs_product = $dbcon->query($query);
    $str .= '<option value="">--select Process Type--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        // $sel = '';
        if ($rel['process_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['process_id'] . '">' . $rel['process_name'] . '</option>';
    }
    return $str;
}

function get_all_process_by_type($dbcon, $pid, $id)
{
    $str = '';
    $query = "select `process_id`,`process_name` from process_mst where process_status=0 and process_type=$pid";
    $rs_product = $dbcon->query($query);
    //$str.='<option value="">--Select Process--</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_product)) {
        // $sel = '';
        if ($rel['process_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['process_id'] . '">' . $rel['process_name'] . '</option>';
    }
    return $str;
}

function get_tax_percentage($dbcon, $id)
{
    $str = '';
    $query = "Select * from tbl_tax_per_master where tp_status=0 ";
    $rs_type = $dbcon->query($query);

    $str = '<option value="" >--Choose Tax--</option>';
    while ($row = brp_mysqli_fetch_assoc($rs_type)) {
        // $sel = '';
        if ($row['tp_id'] == $id) {
            $sel = 'selected="selected"';
        }

        $str .= '<option ' . $sel . ' value="' . $row['tp_id'] . '">' . $row['tp_per'] . '</option>';
    }
    return $str;
}

function get_product_tax_formula($dbcon, $pid, $type, $cust_state)
{
    $r = $dbcon->query("select stateid from tbl_company where company_id='$_SESSION[company_id]'");
    $crow = brp_mysqli_fetch_array($r);
    $company_state = $crow['stateid'];

    if ($company_state == $cust_state) {
        $category = "INTRA";
    } else {
        $category = "INTER";
    }

    if ($type == 'purchase') {
        $q = $dbcon->query("select p.product_purchase_gst,f.formula_name,f.formulaid,f.tax_id from product_mst as p left join formula_mst as f on f.tax_per_id=p.product_purchase_gst where p.product_id='$pid' and f.tax_cat='$category'");
    } else {
        $q = $dbcon->query("select p.product_sale_gst,f.formula_name,f.formulaid,f.tax_id from product_mst as p left join formula_mst as f on f.tax_per_id=p.product_sale_gst where p.product_id='$pid'  and f.tax_cat='$category'");
    }
    $row = brp_mysqli_fetch_array($q);

    $res['name'] = $row['formula_name'];
    $res['id'] = $row['formulaid'];
    $res['tax_id'] = $row['tax_id'];

    return json_encode($res);
}

function get_process_type_by_id($dbcon, $id)
{
    $q = "select p.process_id,pt.process_type_id from process_mst as p inner join process_type_mst as pt on pt.process_type_id=p.process_type where p.process_id='$id'";
    $row = $dbcon->query($q);
    $rel = brp_mysqli_fetch_assoc($row);
    return $rel['process_type_id'];
    //return $id;

}

function get_fist_bom($dbcon, $comp_id)
{
    if ($comp_id == 0 || !$comp_id) {
        $id = 0;
    } else {
        $id = $comp_id;
    }
    // $q="select c.product_id,b.bom_id from tbl_complaint_trn as c inner join tbl_bom as b on b.bom_product=c.product_id where c.complaint_id='$id' and complaint_trn_status!=2 and b.bom_status='0' and c.user_id=".$_SESSION['user_id'];
    $q = "select c.product_id,b.bom_id from tbl_complaint_trn as c inner join tbl_bom as b on b.bom_product=c.product_id where c.complaint_id='$id' and complaint_trn_status!=2 and b.bom_status='0'";
    $row = $dbcon->query($q);
    $rel = brp_mysqli_fetch_assoc($row);
    return $rel['bom_id'];
}

function get_fist_comp_product($dbcon, $comp_id)
{
    if ($comp_id == 0) {
        $id = 0;
    } else {
        $id = $comp_id;
    }
    $q = "select product_id from tbl_complaint_trn where complaint_id='$id' and complaint_trn_status!=2";
    $row = $dbcon->query($q);
    $rel = brp_mysqli_fetch_assoc($row);
    return $rel['product_id'];
}

function get_grn_for_debitnote($dbcon, $vender_id, $id, $mode)
{
    if ($mode == 'Add') {
        /*$query="SELECT grn.grn_no,grn.grn_id FROM `tbl_mrn` as mrn
        INNER join tbl_grn as grn on grn.grn_id=mrn.grn_no
        WHERE mrn.mrn_status=0 and grn.grn_status=0 and grn.vender_id=".$vender_id." and grn.grn_id not in (SELECT grn_id from tbl_debitnote_trn WHERE debitnote_trn_status=0)";*/

        if (!empty($id)) {
            $che = " and grn.grn_id=" . $id;
        }

        $query = "SELECT mrn.mrn_id,grn.grn_id,led.l_name,mtrn.rejected_qty,mrn.qc_no,pro.product_name,qc.qc_no,qc.qc_date,grn.grn_no,grn.grn_date,(select IFNULL(sum(product_qty),0) as qty  from tbl_debitnote_trn as chtrn where chtrn.debitnote_trn_status=0 and chtrn.grn_id=mrn.grn_no and mtrn.product_id=chtrn.product_id) as used_qty FROM tbl_mrn as mrn 
        left join tbl_mrn_trn as mtrn on mtrn.mrn_no=mrn.mrn_id
        left join product_mst as pro on pro.product_id=mtrn.product_id
        left join tbl_grn as grn on grn.grn_id=mrn.grn_no
        left join tbl_qc as qc on qc.qc_id=mrn.qc_no
        left join tbl_ledger as led on led.l_id=grn.vender_id
        where mrn.mrn_status=0 and grn.vender_id=" . $vender_id . " having mtrn.rejected_qty > used_qty order by mrn.mrn_id";
    } else {
        $query = "SELECT grn.grn_no,grn.grn_id FROM `tbl_mrn` as mrn 
        INNER join tbl_grn as grn on grn.grn_id=mrn.grn_no
        WHERE mrn.mrn_status=0 and grn.grn_status=0 and grn.vender_id=" . $vender_id . "";
    }
    /*echo $query;*/
    $str = '';
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose GRN</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['grn_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['grn_id'] . '">' . $rel['grn_no'] . '</option>';
    }
    return $str;
}

function get_grn_trn_for_debitnote($dbcon, $grn_id, $id, $mode)
{
    $query = "SELECT trn.product_id,pro.product_name from tbl_grn_trn as trn
    INNER join tbl_mrn as mrn on mrn.grn_no=trn.grn_id and mrn.mrn_status=0
    INNER join tbl_mrn_trn as mrntrn on mrntrn.mrn_no=mrn.mrn_id and mrntrn.mrn_trn_status=0 and mrntrn.product_id=trn.product_id
    left join product_mst as pro on pro.product_id=trn.product_id
    where trn.grn_trn_status=0 and trn.grn_id=" . $grn_id;
    /*echo $query;*/
    $str = '';
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Product</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . '</option>';
    }
    return $str;
}
function get_grn_for_purchase($dbcon, $vender_id, $id, $mode)
{
    /* if($mode=='Add'){

    $query="select grn.grn_id,gtrn.product_qty,grn.grn_no,grn.grn_date,pro.product_name,led.l_name,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty from tbl_grn as grn
    left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id
    left join product_mst as pro on pro.product_id=gtrn.product_id
    left join tbl_ledger as led on led.l_id=grn.vender_id
    where grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.vender_id=".$vender_id." and grn.company_id=".$_SESSION['company_id']." having gtrn.product_qty > used_qty order by grn.grn_id desc";
    }
    else{
    $query="select mst.grn_id,mst.grn_no from tbl_grn as mst
    where mst.grn_status=0 and mst.qc_status=1 and vender_id=".$vender_id." and company_id=".$_SESSION['company_id'];
} */

    if ($id) {
        $query = "select mst.grn_id,mst.grn_no from tbl_grn as mst
    where mst.grn_status=0 and vender_id=" . $vender_id . " and company_id=" . $_SESSION['company_id'];
    } else {
        $query = "select mst.grn_id,mst.grn_no from tbl_grn as mst
    where mst.grn_status=0 and mst.purchase_status=0 and vender_id=" . $vender_id . " and company_id=" . $_SESSION['company_id'];
    }

    $str = '';

    $rs_dispatch = $dbcon->query($query);
    $count = brp_mysqli_num_rows($rs_dispatch);

    if ($count > 0) {
        //$str = '<option value="">Choose GRN</option>';
        $grn_no_array = [];
        while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {

            if (!in_array($rel['grn_no'], $grn_no_array)) {
                $sel = '';
                //var_dump($id);
                if ($rel['grn_id'] == $id) {
                    $sel = "selected='selected'";
                }

                $str .= '<option ' . $sel . ' value="' . $rel['grn_id'] . '">' . $rel['grn_no'] . '</option>';
            }
            $grn_no_array[] = $rel['grn_no'];
        }
    } else {
        $str = '0';
    }
    return $str;
}
function get_service_for_purchase($dbcon, $vender_id, $id, $mode)
{
    if ($id) {
        $query = "select mst.service_id,mst.service_no from tbl_service_notes as mst
        where mst.service_status=0 and vender_id=" . $vender_id . " and company_id=" . $_SESSION['company_id'];
    } else {
        $query = "select mst.service_id,mst.service_no from tbl_service_notes as mst
        where mst.service_status=0 and mst.purchase_status=0 and vender_id=" . $vender_id . " and company_id=" . $_SESSION['company_id'];
    }
    //var_dump($query);
    $str = '';

    $rs_dispatch = $dbcon->query($query);
    $count = brp_mysqli_num_rows($rs_dispatch);
    if ($count > 0) {
        $str = '<option value="">Choose Service No</option>';
        $grn_no_array = [];
        while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {

            if (!in_array($rel['service_no'], $grn_no_array)) {
                $sel = '';
                if ($rel['service_id'] == $id) {
                    $sel = "selected='selected'";
                }
                $str .= '<option ' . $sel . ' value="' . $rel['service_id'] . '">' . $rel['service_no'] . '</option>';
            }
            $grn_no_array[] = $rel['service_no'];
        }
    } else {
        $str = '0';
    }
    return $str;
}
function get_grn_trn_for_purchase($dbcon, $grn_id, $id, $mode)
{
    /*if($mode=='Add'){
    $query="select mst.grn_id,mst.grn_no from tbl_grn as mst
    where mst.grn_status=0 and mst.qc_status=1 and vender_id=".$vender_id." and company_id=".$_SESSION['company_id'];
    }
    else{
    $query="select mst.grn_id,mst.grn_no from tbl_grn as mst
    where mst.grn_status=0 and mst.qc_status=1 and vender_id=".$vender_id." and company_id=".$_SESSION['company_id'];
}*/
    $query = "select trn.product_id,pro.product_name,product_qty,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status!=2 and chtrn.grn_id=trn.grn_id and trn.product_id=chtrn.product_id) as used_qty from tbl_grn_trn as trn
left join product_mst as pro on pro.product_id=trn.product_id
where trn.grn_trn_status=0 and trn.grn_id=" . $grn_id . " having product_qty>=used_qty";
    $str = '';
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Product</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['product_id'] == $id) {
            $sel = "selected='selected'";
        }
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . '</option>';
    }
    return $str;
}
function get_po_for_purchase($dbcon, $id)
{
    $str = '';
    $query = "select * from tbl_purchaseorder where status=0 and po_type_status=1 and purchase_status=0 and po_approval_status=1 and vender_id=" . $id . " and company_id=" . $_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);
    $str = '<option value="">Choose Purchase Order</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        /*if($rel['sales_order_id']==$id)
        {$sel ="selected='selected'";}*/
        $str .= '<option ' . $sel . ' value="' . $rel['purchaseorder_id'] . '">' . $rel['purchaseorder_no'] . '</option>';
    }
    return $str;
}

function get_purchase_order_typewise_data($dbcon, $pro_type, $id)
{
    $str = '';
    $query = "select trn.*,pro.product_id,pro.product_name from tbl_purchaseordertrn as trn inner join product_mst as pro on pro.product_id=trn.product_id where purchaseordertrn_status=0 and use_purchase_status=0 and trn.product_type=" . $pro_type . " and purchaseorder_id=" . $id;
    $rs_dispatch = $dbcon->query($query);
    $que = "select * from product_mst where product_status=0 and product_type=3";
    $rs_dispatch1 = $dbcon->query($que);
    $str = '<option value="">Choose Purchase Order Products</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        /*if($rel['sales_order_id']==$id)
        {$sel ="selected='selected'";}*/
        $str .= '<option ' . $sel . ' value="' . $rel['product_id'] . '">' . $rel['product_name'] . '-' . $rel['product_hsn_code'] . '</option>';
    }
    while ($re = brp_mysqli_fetch_assoc($rs_dispatch1)) {
        $sel = '';
        /*if($rel['sales_order_id']==$id)
        {$sel ="selected='selected'";}*/
        $str .= '<option ' . $sel . ' value="' . $re['product_id'] . '">' . $re['product_name'] . '-' . $re['product_code'] . '</option>';
    }
    return $str;
}

function load_complaint_no($dbcon)
{
    $row = array();
    $query1 = "select * from tbl_invoicetype where status=0 and type_id=1 and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
    $rows = mysqli_fetch_assoc($dbcon->query($query1));
    $id = $rows['taxinvoice_start'];
    $id = $id + 1;

    //$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
    //$end = $start+1;
    if ($rows['invoice_format'] == '2') {
        $row['invoiceno'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
    } else if ($rows['invoice_format'] == '1') {
        $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
    } else if ($rows['invoice_format'] == '3') {
        $row['invoiceno'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
    } else {
        $row['invoiceno'] = str_pad($id, 3, "0", STR_PAD_LEFT);
    }
    $row['challanno'] = str_pad($id, 3, "0", STR_PAD_LEFT);

    return $row['invoiceno'];
}

function get_last_purchase($dbcon, $product_id)
{
    $q = "select p.po_id,p.po_date,pt.po_landing_cost from tbl_pono as p left join tbl_potrancation as pt on pt.po_id=p.po_id where pt.product_id='$product_id' order by p.po_date desc";
    $query = $dbcon->query($q);
    $row = brp_mysqli_fetch_array($query);

    return $row['po_landing_cost'];
    //return $product_id;

}

function get_process_product($dbcon, $product_id)
{
    $q = "select p.process_id,p.process_priority,pr.process_name from tbl_product_process as p left join process_mst as pr on pr.process_id=p.process_id where p.status = 0 and p.product_id='$product_id' order by p.process_priority";
    $query = $dbcon->query($q);
    $arr = array();
    while ($row = brp_mysqli_fetch_array($query)) {
        $arr[] = $row['process_name'];
    }
    $arr = array_unique($arr);
    return implode(",", $arr);
}

function get_tree_bom($dbcon, $product_id, $parent_id, $level, $cnt, $bom, $number, $qty, $bom_trn_id, $unit_name)
{
    if ($level == 0) {
        $pr_value = get_pro_field($dbcon, $product_id, 'product_name');
        $pr_type = get_pro_field($dbcon, $product_id, 'product_type');
        echo '
        <td class="td1">' . $number . '</td>
        <td class="td2"><strong>' . $pr_value . '</strong></td>
        <td class="td2">' . get_product_type_by_id($dbcon, $pr_type) . '</td>
        <td class="td3">' . (round($qty, 2)) . '</td>
        <td class="td3">' . $unit_name . '</td>
        <td class="td3">' . get_last_purchase($dbcon, $product_id) . '</td>
        <td class="td3">' . get_process_product($dbcon, $product_id) . '</td>
        ';
    }

    $getChildNodes = "select b.*,u.unit_name from tbl_bomtrn as b left join unit_mst as u on u.unitid=b.product_uom where b.parent_id = '" . $bom_trn_id . "' and b.bom_id='$bom' order by bom_trn_id Desc";
    $resChildNodes = $dbcon->query($getChildNodes);
    if (brp_mysqli_num_rows($resChildNodes) > 0) {

        echo '<tr>';

        $cntt = 1;
        while ($childNode = brp_mysqli_fetch_assoc($resChildNodes)) {
            $pro_name = get_pro_field($dbcon, $childNode['product_id'], 'product_name');

            $product_desc = get_pro_field($dbcon, $childNode['product_id'], 'product_desc');

            $pr_type = get_pro_field($dbcon, $childNode['product_id'], 'product_type');

            $product_specification = get_pro_field($dbcon, $childNode['product_id'], 'product_specification');

            $getChildNodes1 = "select b.*,u.unit_name from tbl_bomtrn as b left join unit_mst as u on u.unitid=b.product_uom where b.parent_id = '" . $childNode['bom_trn_id'] . "' and b.bom_id='$bom' order by bom_trn_id Desc";
            $resChildNodes1 = $dbcon->query($getChildNodes1);
            if (brp_mysqli_num_rows($resChildNodes1) > 0) {
                $new_number = $number . '.' . $cntt;
                if ($product_desc) {
                    $pro_name .= ' | ' . $product_desc;
                }
                if ($product_specification) {
                    $pro_name .= '<br/><strong>Width:</strong>' . $childNode['product_width'] . '<strong>| Height:</strong>' . $childNode['product_height'] . '<strong>| Thickness:</strong>' . $childNode['product_thickness'] . '<strong>| Kg:</strong>' . $childNode['product_kg'] . ' ';
                }
                echo '<tr>
                <td  class="td1">' . $new_number . '</td>
                <td class="td2">' . $pro_name . '</td>
                <td class="td2">' . get_product_type_by_id($dbcon, $pr_type) . '</td>
                <td class="td2">' . (round($childNode['product_qty'], 2)) . '</td>
                <td class="td3">' . $childNode['unit_name'] . '</td>
                <td class="td2">' . get_last_purchase($dbcon, $childNode['product_id']) . '</td>
                <td class="td3">' . get_process_product($dbcon, $childNode['product_id']) . '</td>
                </tr>';

                $level++;
                $cnt++;
                $cntt++;

                get_tree_bom($dbcon, $childNode['product_id'], $parent_id, $level, $cnt, $bom, $new_number, $childNode['product_qty'], $childNode['bom_trn_id'], $childNode['unit_name']);
            } else {
                $new_number = $number . '.' . $cntt;
                if ($product_desc) {
                    $pro_name .= ' | ' . $product_desc;
                }
                if ($product_specification) {
                    $pro_name .= '<br/><strong>Width:</strong>' . $childNode['product_width'] . '<strong>| Height:</strong>' . $childNode['product_height'] . '<strong>| Thickness:</strong>' . $childNode['product_thickness'] . '<strong>| Kg:</strong>' . $childNode['product_kg'] . ' ';
                }
                echo '<tr>
                <td  class="td1">' . $new_number . '</td>
                <td class="td2">' . $pro_name . '</td>
                <td class="td2">' . get_product_type_by_id($dbcon, $pr_type) . '</td>
                <td class="td2">' . (round($childNode['product_qty'], 2)) . '</td>
                <td class="td3">' . $childNode['unit_name'] . '</td>
                <td class="td2">' . get_last_purchase($dbcon, $childNode['product_id']) . '</td>
                <td class="td3">' . get_process_product($dbcon, $childNode['product_id']) . '</td>
                </tr>';

                $level++;
                $cnt++;
                $cntt++;
                //get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);

            }

            //$cntt++;

        }
    }
}

function check_requested($dbcon, $product, $req_no)
{
    $query = "select rp_id from tbl_request_product where rp_pid='$product' and rp_req_no='$req_no' and status='0'";
    $row = $dbcon->query($query);
    $count = brp_mysqli_num_rows($row);
    $childNode = brp_mysqli_fetch_assoc($row);
    //return $count;
    return $childNode['rp_id'];
}
function check_reserve_stock($dbcon, $product, $req_no)
{
    $query = "select reserve_stock from tbl_request_product where rp_pid='$product' and rp_req_no='$req_no' and status='0'";
    $row = $dbcon->query($query);
    $count = brp_mysqli_num_rows($row);
    $childNode = brp_mysqli_fetch_assoc($row);
    //return $count;
    return $childNode['reserve_stock'];
}
function check_mainrequested($dbcon, $product, $req_no)
{
    $query = "select rp_id from tbl_request_product where rp_pid='$product' and rp_req_no='$req_no' and status='0'";
    $row = $dbcon->query($query);
    $count = brp_mysqli_num_rows($row);
    $childNode = brp_mysqli_fetch_assoc($row);
    return $childNode['rp_id'];
}

/*
function get_tree_request($dbcon,$product_id,$parent_id,$level,$cnt,$bom,$number,$qty,$bom_trn_id,$ptype,$pr_setting)
{
    global $counter_tree;

    if($level==0)
    {
        $pr_value=get_pro_field($dbcon,$product_id,'product_name');

        $pr_setting_arr=explode(",",$pr_setting);

        if(in_array("process_product",$pr_setting_arr))
        {
            $readonly="";
            $in_check_qty="";
        }
        else
        {
            $readonly="readonly";
            $in_check_qty="1";
        }

        if(check_requested($dbcon,$pr_value)==0)
        {
            $btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
        }
        else
        {
            $btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
            //$btn='<a class="btn btn-danger" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-paper-plane"></i> Requested</a>';
        }

        echo '
                <td class="td1">'.$number.'</td>
                <td class="td2">'.$pr_value.'</td>
                <td class="td4">'.get_product_type_by_id($dbcon,get_pro_field($dbcon,$product_id,'product_type')).'</td>
                <td class="td4">'.get_pro_field($dbcon,$product_id,'product_min_stock').'</td>
                <td class="td5">'.get_product_stock($dbcon,$product_id).'</td>
                <td class="td5">
                    <input type="text" class="form-control" name="req_qty'.$counter_tree.'" id="req_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)"  />

                    <input type="hidden" class="form-control" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$qty.'"  />


                </td>
                <td class="td5">
                    <input type="text" class="form-control" name="in_process_qty'.$counter_tree.'" id="in_process_qty'.$counter_tree.'" '.$readonly.' onkeypress="return isNumberKey(event)" />

                    <input type="hidden" class="form-control" name="in_process_qty_check'.$counter_tree.'" id="in_process_qty_check'.$counter_tree.'" value="'.$in_check_qty.'" />
                </td>

                <td class="td5">
                    <input type="text" class="form-control" name="po_qty'.$counter_tree.'" id="po_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" />
                    <input type="hidden" class="form-control" name="pr_id'.$counter_tree.'" id="pr_id'.$counter_tree.'" value="'.$product_id.'" />
                    <input type="hidden" class="form-control" name="pr_type'.$counter_tree.'" id="pr_type'.$counter_tree.'" value="'.$ptype.'" />
                </td>
                <td class="td5">'.$btn.'</td>';

    }

    $getChildNodes = "select * from tbl_bomtrn where parent_id = '".$bom_trn_id."' and bom_id='$bom'";
    $resChildNodes = $dbcon->query($getChildNodes);
    if(brp_mysqli_num_rows($resChildNodes) > 0)
    {

        echo '<tr>';

        $cntt=1;
        while($childNode = brp_mysqli_fetch_assoc($resChildNodes))
        {
            $pro_name=get_pro_field($dbcon,$childNode['product_id'],'product_name');

            $pro_setting=get_pro_field($dbcon,$childNode['product_id'],'product_setting_check');

            $pr_setting_arr=explode(",",$pro_setting);

            if(in_array("process_product",$pr_setting_arr))
            {
                $readonly="";
                $in_check_qty="";
            }
            else
            {
                $readonly="readonly";
                $in_check_qty="1";
            }


            $getChildNodes1 = "select * from tbl_bomtrn where parent_id = '".$childNode['bom_trn_id']."' and bom_id='$bom'";
            $resChildNodes1 = $dbcon->query($getChildNodes1);
            if(brp_mysqli_num_rows($resChildNodes1) > 0)
            {
                $new_number=$number.'.'.$cntt;
                $counter_tree++;


                if(check_requested($dbcon,$childNode['product_id'])==0)
                {
                    $btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
                }
                else
                {
                    $btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
                    //$btn='<a class="btn btn-danger" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-paper-plane"></i> Requested</a>';
                }

                echo '<tr>
                <td  class="td1">'.$number.'</td>
                <td class="td2">'.$pro_name.'</td>
                <td class="td4">'.get_product_type_by_id($dbcon,get_pro_field($dbcon,$childNode['product_id'],'product_type')).'</td>
                <td class="td4">'.get_pro_field($dbcon,$childNode['product_id'],'product_min_stock').'</td>
                <td class="td5">'.get_product_stock($dbcon,$childNode['product_id']).'</td>
                <td class="td5">
                    <input type="text" class="form-control" name="req_qty'.$counter_tree.'" id="req_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" />

                    <input type="hidden" class="form-control" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$childNode['product_qty'].'"  />

                </td>
                <td class="td5">
                    <input type="text" class="form-control" name="in_process_qty'.$counter_tree.'" id="in_process_qty'.$counter_tree.'" '.$readonly.' onkeypress="return isNumberKey(event)" />

                    <input type="hidden" class="form-control" name="in_process_qty_check'.$counter_tree.'" id="in_process_qty_check'.$counter_tree.'" value="'.$in_check_qty.'" />
                </td>

                <td class="td5">
                    <input type="text" class="form-control" name="po_qty'.$counter_tree.'" id="po_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" />
                    <input type="hidden" class="form-control" name="pr_id'.$counter_tree.'" id="pr_id'.$counter_tree.'"  value="'.$childNode['product_id'].'"  />
                    <input type="hidden" class="form-control" name="pr_type'.$counter_tree.'" id="pr_type'.$counter_tree.'" value="'.$childNode['product_type'].'" />
                </td>
                <td class="td5">'.$btn.'</td>
                </tr>';


                $level++;$cnt++;$cntt++;

                get_tree_request($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom,$new_number,$childNode['product_qty'],$childNode['bom_trn_id'],$childNode['product_type'],$pro_setting);

            }
            else
            {
                $pr_setting_arr=explode(",",$childNode['product_setting_check']);

                $pro_setting=get_pro_field($dbcon,$childNode['product_id'],'product_setting_check');

                if(in_array("process_product",$pro_setting))
                {
                    $readonly="";
                    $in_check_qty="";
                }
                else
                {
                    $readonly="readonly";
                    $in_check_qty="1";
                }



                $new_number=$number.'.'.$cntt;
                $counter_tree++;

                if(check_requested($dbcon,$childNode['product_id'])==0)
                {
                    $btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
                }
                else
                {
                    $btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
                    //$btn='<a class="btn btn-danger" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-paper-plane"></i> Requested</a>';
                }

                echo '<tr>
                <td  class="td1">'.$new_number.'</td>
                <td   class="td2">'.$pro_name.'</td>
                <td class="td4">'.get_product_type_by_id($dbcon,get_pro_field($dbcon,$childNode['product_id'],'product_type')).'</td>
                <td class="td4">'.get_pro_field($dbcon,$childNode['product_id'],'product_min_stock').'</td>
                <td class="td5">'.get_product_stock($dbcon,$childNode['product_id']).'</td>
                <td class="td5">
                    <input type="text" class="form-control" name="req_qty'.$counter_tree.'" id="req_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" />

                    <input type="hidden" class="form-control" name="total_qty'.$counter_tree.'" id="total_qty'.$counter_tree.'" value="'.$childNode['product_qty'].'"  />

                </td>
                <td class="td5">
                    <input type="text" class="form-control" name="in_process_qty'.$counter_tree.'" id="in_process_qty'.$counter_tree.'" '.$readonly.' onkeypress="return isNumberKey(event)" />

                    <input type="hidden" class="form-control" name="in_process_qty_check'.$counter_tree.'" id="in_process_qty_check'.$counter_tree.'" value="'.$in_check_qty.'" />
                </td>

                <td class="td5">
                    <input type="text" class="form-control" name="po_qty'.$counter_tree.'" id="po_qty'.$counter_tree.'" onkeypress="return isNumberKey(event)" />
                    <input type="hidden" class="form-control" name="pr_id'.$counter_tree.'" id="pr_id'.$counter_tree.'" value="'.$childNode['product_id'].'"  />
                    <input type="hidden" class="form-control" name="pr_type'.$counter_tree.'" id="pr_type'.$counter_tree.'" value="'.$childNode['product_type'].'" />
                </td>
                <td class="td5">'.$btn.'</td>
                </tr>';


                $level++;$cnt++;$cntt++;
                //get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);

            }

            //$cntt++;
        }


    }

    return $counter_tree;
}
*/

function check_level_open($dbcon, $pr_id, $req_no)
{
    $q = $dbcon->query("select rp_id from tbl_request_product where rp_pid=(select product_id from tbl_bomtrn where bom_trn_id=$pr_id) and rp_req_no='$req_no'");
    $count = brp_mysqli_num_rows($q);
    return $count;
}

function get_tree_request($dbcon, $product_id, $parent_id, $level, $cnt, $bom, $number, $qty, $bom_trn_id, $ptype, $pr_setting, $req_no, $bom_real_level, $pid)
{
    global $counter_tree;

    if ($level == 0) {
        $pr_value = get_pro_field($dbcon, $product_id, 'product_name');

        $pr_setting_arr = explode(",", $pr_setting);

        if (in_array("process_product", $pr_setting_arr)) {
            $readonly = "";
            $in_check_qty = "";
        } else {
            $readonly = "readonly";
            $in_check_qty = "1";
        }

        if ($bom_real_level != 1) {
            $display = 'display:none';
        } else {
            $display = 'display:block';
        }
        //$rno=check_requested($dbcon,$product_id,$req_no);
        $rev_sto = check_reserve_stock($dbcon, $product_id, $req_no);
        if (check_requested($dbcon, $product_id, $req_no) == 0) {
            $kp = str_replace(".", "", $number);
            $btn = '<a class="btn btn-primary dispbtn csb' . $bom_trn_id . ' sho' . $kp . '" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request(' . $counter_tree . ',' . $bom_trn_id . ');show_btn(' . $kp . ',1);change_status(' . $kp . ');" ><i class="fa fa-paper-plane"></i> Request</a>';
            //style="'.$display.'"
            $btn .= '<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';

            $submi = "1";

            $btn .= '<input type="hidden" id="reqsho' . $kp . '" value="1" />';
            $text_readonly = "";
            //get_current_stock_new($dbcon,$product_id,$chil_unit["product_uom"])
            //reserve_stock($dbcon,$product_id,$chil_unit["product_uom"])
            $btn .= ' <input type="hidden" id="savebuttonshow' . $kp . '" name="savebuttonshow[]" value="1" />';

            //$req_value='';

        } else {
            //$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
            $kp = str_replace(".", "", $number);
            $btn = '<a class="btn btn-danger dispbtn csb' . $bom_trn_id . ' rsho' . $kp . ' " data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';

            $btn .= '<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';

            $btn .= ' <input type="hidden" id="reqsho' . $kp . '" value="0" />';

            $btn .= ' <input type="hidden" id="savebuttonshow' . $kp . '" name="savebuttonshow[]" value="0" />';

            $text_readonly = "readonly";

            $submi = "0";
            //$process_value=get_process_value($dbcon,$product_id,$req_no);



        }
        $get_unit = "select btrn.product_uom,btrn.product_base_unit,umst.unit_name as purchase_unit,umst1.unit_name as process_unit from tbl_bomtrn as btrn
        left join unit_mst as umst on umst.unitid=btrn.product_uom
        left join unit_mst as umst1 on umst1.unitid=btrn.product_base_unit
        where bom_trn_id = '" . $bom_trn_id . "'";
        $res_unit = $dbcon->query($get_unit);
        $chil_unit = mysqli_fetch_assoc($res_unit);

        $cstock = get_current_stock_new($dbcon, $product_id, $chil_unit["product_uom"]);
        $rstock = reserve_stock($dbcon, $product_id, $chil_unit["product_uom"]);
        $actualstock = $cstock - $rstock;

        $wip_purchase_stock = wip_purchase_stock($dbcon, $product_id, $chil_unit["product_uom"]);
        $actualstock = $actualstock + $wip_purchase_stock;
        //$actualstock11=$cstock."-".$rstock;

        if ($actualstock < "0") {
            $actualstock = 0;
        }
        $reqhi = "";
        if ($actualstock == 0) {
            $reqhi = "readonly";
        }
        echo '
        <td class="td1">' . $number . '</td>
        <td class="td2" title="' . get_product_type_by_id($dbcon, get_pro_field($dbcon, $product_id, 'product_type')) . '">' . $pr_value . '</td>
        <!--<td class="td4">' . get_product_type_by_id($dbcon, get_pro_field($dbcon, $product_id, 'product_type')) . '</td>-->
        <td class="td4">' . get_pro_field($dbcon, $product_id, 'product_min_stock') . '</td>
        <!--<td class="td5">' . get_current_stock_new($dbcon, $product_id, $chil_unit["product_uom"]) . '</td>
        <td class="td5">' . reserve_stock($dbcon, $product_id, $chil_unit["product_uom"]) . '</td>-->
        <td class="td5">
        <input type="number"  class="form-control" name="at_stock' . $counter_tree . '" id="at_stock' . $counter_tree . '" onkeypress="return isNumberKey(event)" value="' . $actualstock . '" readonly />
        </td>
        <td class="td5">
        <div class="col-md-9">
        <input type="number" min="0" class="form-control rt' . $bom_trn_id . '" name="req_qty' . $counter_tree . '" id="req_qty' . $counter_tree . '" onkeypress="return isNumberKey(event)" ' . $text_readonly . ' value="' . $req_value . '" />
        </div>
        <div class="col-md-3" style="font-size:16px;white-space:nowrap;">
        <strong>' . $chil_unit["purchase_unit"] . '</strong>
        </div>

        <input type="hidden" class="form-control tct' . $bom_trn_id . '" name="total_qty' . $counter_tree . '" id="total_qty' . $counter_tree . '" value="' . $qty . '"  />
        </td>
        <td class="td5">
        <input type="text"  class="form-control" name="at_reserve' . $counter_tree . '" id="at_reserve' . $counter_tree . '" onkeypress="return isNumberKey(event)" value="' . $rev_sto . '" ' . $reqhi . ' ' . $text_readonly . ' onkeyup="check_reseve_qty(' . $counter_tree . ',this.value);" />
        </td>
        <td class="td5">
        <div class="col-md-9">
        <input type="number" min="0" class="form-control pt' . $bom_trn_id . '" name="in_process_qty' . $counter_tree . '" id="in_process_qty' . $counter_tree . '" ' . $text_readonly . ' ' . $readonly . '  onkeypress="return isNumberKey(event)" onkeyup="get_inhouse_inner(' . $counter_tree . ',' . $bom_trn_id . ');check_req_qty(' . $counter_tree . ',this.value);" value="' . $in_req_value . '" />
        </div>
        <div class="col-md-3" style="font-size:16px;">
        <strong>' . $chil_unit["process_unit"] . '</strong>
        <input type="hidden" class="form-control" name="process_unit' . $counter_tree . '" id="process_unit' . $counter_tree . '" value="' . $chil_unit["product_base_unit"] . '"  />
        </div>

        <input type="hidden" class="form-control inpc' . $bom_trn_id . '" name="in_process_qty_check' . $counter_tree . '" id="in_process_qty_check' . $counter_tree . '" value="' . $in_check_qty . '" />
        </td>

        <td class="td5">
        <div class="col-md-9">
        <input type="number" min="0" class="form-control po' . $bom_trn_id . '" name="po_qty' . $counter_tree . '" id="po_qty' . $counter_tree . '" onkeypress="return isNumberKey(event);" onkeyup="check_req_qty(' . $counter_tree . ',this.value);" ' . $text_readonly . ' />
        </div>
        <div class="col-md-3" style="font-size:16px;">
        <strong>' . $chil_unit["purchase_unit"] . '</strong>
        <input type="hidden" class="form-control" name="purchase_unit' . $counter_tree . '" id="purchase_unit' . $counter_tree . '" value="' . $chil_unit["product_uom"] . '"  />
        </div>
        <input type="hidden" class="form-control" name="pr_id' . $counter_tree . '" id="pr_id' . $counter_tree . '" value="' . $product_id . '" />
        <input type="hidden" class="form-control" name="pr_type' . $counter_tree . '" id="pr_type' . $counter_tree . '" value="' . $ptype . '" />
        </td>
        <td class="td5">' . $btn . '
        <input type="hidden" class="perent' . $bom_trn_id . '" name="perent' . $counter_tree . '" id="perent' . $counter_tree . '" value="' . $pid . '" />

        <input type="hidden" class="submi' . $bom_trn_id . '" name="submi[]" id="submi' . $counter_tree . '" value="' . $submi . '" />
        </td>';
    }

    $getChildNodes = "select * from tbl_bomtrn where parent_id = '" . $bom_trn_id . "' and bom_id='$bom'";
    $resChildNodes = $dbcon->query($getChildNodes);
    if (brp_mysqli_num_rows($resChildNodes) > 0) {
        echo '<tr>';

        $cntt = 1;

        $getC = "select product_id from tbl_bomtrn where bom_trn_id	 = '" . $bom_trn_id . "' and bom_id='$bom'";
        $resC = $dbcon->query($getC);
        $chie = brp_mysqli_fetch_assoc($resC);
        $produ_id = $chie['product_id'];

        while ($childNode = brp_mysqli_fetch_assoc($resChildNodes)) {
            $one_q = $childNode['product_qty'] / $childNode['product_base_qty'];
            //$pid1=check_re_entry($dbcon,$produ_id,$req_no);
            $pid1 = check_requested($dbcon, $produ_id, $req_no);

            $pro_name = get_pro_field($dbcon, $childNode['product_id'], 'product_name');

            $pro_setting = get_pro_field($dbcon, $childNode['product_id'], 'product_setting_check');

            $pr_setting_arr = explode(",", $pro_setting);

            if (in_array("process_product", $pr_setting_arr)) {
                $readonly = "";
                $in_check_qty = "";
            } else {
                $readonly = "readonly";
                $in_check_qty = "1";
            }

            $bom_real_level = $childNode['bom_level'];

            $getChildNodes1 = "select * from tbl_bomtrn where parent_id = '" . $childNode['bom_trn_id'] . "' and bom_id='$bom'";
            $resChildNodes1 = $dbcon->query($getChildNodes1);
            if (brp_mysqli_num_rows($resChildNodes1) > 0) {
                $new_number = $number . '.' . $cntt;
                $counter_tree++;
                $level++;

                if ($bom_real_level == 1) {
                    $display = 'display:none';
                } else if (check_level_open($dbcon, $childNode['parent_id'], $req_no) > 0) {
                    $display = 'display:block';
                } else {
                    $display = 'display:none';
                }

                $rev_sto = check_reserve_stock($dbcon, $childNode['product_id'], $req_no);
                if (check_requested($dbcon, $childNode['product_id'], $req_no) == 0) {
                    $kp1 = str_replace(".", "", $new_number);
                    $btn = '<a class="btn btn-primary dispbtn csb' . $childNode['bom_trn_id'] . ' sho' . $kp1 . ' " data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request(' . $counter_tree . ',' . $childNode['bom_trn_id'] . ');show_btn(' . $kp1 . ',1);change_status(' . $kp1 . ');" ><i class="fa fa-paper-plane"></i> Request</a>';
                    //style="'.$display.'"
                    $btn .= '<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';

                    $btn .= '<input type="hidden" id="reqsho' . $kp1 . '" value="1" />';
                    $submi = "1";
                } else {
                    $kp1 = str_replace(".", "", $new_number);
                    $btn = '<a class="btn btn-danger rsho' . $kp1 . '" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';

                    $btn .= '<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';

                    $btn .= '<input type="hidden" id="reqsho' . $kp1 . '" value="0" />';
                    $submi = "0";
                }
                $get_unit1 = "select btrn.product_uom,btrn.product_base_unit,umst.unit_name as purchase_unit,umst1.unit_name as process_unit from tbl_bomtrn as btrn
                left join unit_mst as umst on umst.unitid=btrn.product_uom
                left join unit_mst as umst1 on umst1.unitid=btrn.product_base_unit
                where bom_trn_id = '" . $childNode['bom_trn_id'] . "'";
                $res_unit1 = $dbcon->query($get_unit1);
                $chil_unit1 = mysqli_fetch_assoc($res_unit1);

                $cstock = get_current_stock_new($dbcon, $childNode['product_id'], $chil_unit1['product_uom']);
                $rstock = reserve_stock($dbcon, $childNode['product_id'], $chil_unit1['product_uom']);
                $actualstock = $cstock - $rstock;
                if ($actualstock < "0") {
                    $actualstock = 0;
                }
                $reqhi = "";
                if ($actualstock == 0) {
                    $reqhi = "readonly";
                }
                $tital = get_product_type_by_id($dbcon, get_pro_field($dbcon, $childNode['product_id'], 'product_type'));

                echo '<tr>
                <td  class="td1">' . $new_number . '</td>
                <td class="td2" title="' . $tital . '" >' . $pro_name . '</td>
                <!--<td class="td4"></td>-->
                <td class="td4">' . get_pro_field($dbcon, $childNode['product_id'], 'product_min_stock') . '</td>
                <!--<td class="td5">' . get_current_stock_new($dbcon, $childNode['product_id'], $chil_unit1['product_uom']) . '</td>
                <td class="td5">' . reserve_stock($dbcon, $childNode['product_id'], $chil_unit1['product_uom']) . '</td>-->
                <td class="td5">
                <input type="number"  class="form-control" name="at_stock' . $counter_tree . '" id="at_stock' . $counter_tree . '" onkeypress="return isNumberKey(event)" value="' . $actualstock . '" readonly />
                </td>
                <td class="td5">
                <div class="col-md-9">
                <input type="number" min="0" class="form-control rt' . $childNode['bom_trn_id'] . '" name="req_qty' . $counter_tree . '" id="req_qty' . $counter_tree . '" onkeypress="return isNumberKey(event)"  />
                </div>
                <div class="col-md-3" style="font-size:16px;">
                <strong>' . $chil_unit1['purchase_unit'] . '</strong>
                </div>

                <!--	<input type="text" class="form-control tct' . $childNode['bom_trn_id'] . '" name="total_qty' . $counter_tree . '" id="total_qty' . $counter_tree . '" value="' . $childNode['product_qty'] . '"  />-->
                <input type="hidden" class="form-control tct' . $childNode['bom_trn_id'] . '" name="total_qty' . $counter_tree . '" id="total_qty' . $counter_tree . '" value="' . $one_q . '"  />

                </td>
                <td class="td5">
                <input type="number"  class="form-control" name="at_reserve' . $counter_tree . '" id="at_reserve' . $counter_tree . '" onkeypress="return isNumberKey(event)" value="' . $rev_sto . '" ' . $reqhi . ' ' . $text_readonly . ' onkeyup="check_reseve_qty(' . $counter_tree . ',this.value);" />
                </td>

                <td class="td5">
                <div class="col-md-9">
                <input type="number" min="0" class="form-control pt' . $childNode['bom_trn_id'] . '" name="in_process_qty' . $counter_tree . '" id="in_process_qty' . $counter_tree . '" ' . $readonly . ' onkeypress="return isNumberKey(event)" onkeyup="get_inhouse_inner(' . $counter_tree . ',' . $childNode['bom_trn_id'] . ');check_req_qty(' . $counter_tree . ',this.value);" />
                </div>
                <div class="col-md-3" style="font-size:16px;">
                <strong>' . $chil_unit1['process_unit'] . '</strong>
                <input type="hidden" class="form-control" name="process_unit' . $counter_tree . '" id="process_unit' . $counter_tree . '" value="' . $chil_unit1['product_base_unit'] . '"  />
                </div>

                <input type="hidden" class="form-control inpc' . $childNode['bom_trn_id'] . '" name="in_process_qty_check' . $counter_tree . '" id="in_process_qty_check' . $counter_tree . '" value="' . $in_check_qty . '"  />
                </td>

                <td class="td5">
                <div class="col-md-9">
                <input type="number" min="0" class="form-control po' . $childNode['bom_trn_id'] . '" name="po_qty' . $counter_tree . '" id="po_qty' . $counter_tree . '" onkeypress="return isNumberKey(event);" onkeyup="check_req_qty(' . $counter_tree . ',this.value);" />
                </div>
                <div class="col-md-3" style="font-size:16px;">
                <strong>' . $chil_unit1['purchase_unit'] . '</strong>
                <input type="hidden" class="form-control" name="purchase_unit' . $counter_tree . '" id="purchase_unit' . $counter_tree . '" value="' . $chil_unit1['product_uom'] . '"  />
                </div>
                <input type="hidden" class="form-control" name="pr_id' . $counter_tree . '" id="pr_id' . $counter_tree . '"  value="' . $childNode['product_id'] . '"  />
                <input type="hidden" class="form-control" name="pr_type' . $counter_tree . '" id="pr_type' . $counter_tree . '" value="' . $childNode['product_type'] . '" />
                </td>
                <td class="td5">' . $btn . '
                <input type="hidden" class="perent' . $childNode['bom_trn_id'] . '" name="perent' . $counter_tree . '" id="perent' . $counter_tree . '" value="' . $pid1 . '" />

                <input type="hidden" class="submi' . $childNode['bom_trn_id'] . '" name="submi[]" id="submi' . $counter_tree . '" value="' . $submi . '" />
                </td>
                </tr>';

                $cnt++;
                $cntt++;
                $pid12 = check_requested($dbcon, $childNode['product_id'], $req_no);
                //get_tree_request($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom,$new_number,$childNode['product_qty'],$childNode['bom_trn_id'],$childNode['product_type'],$pro_setting,$req_no,$bom_real_level,$pid12);
                get_tree_request($dbcon, $childNode['product_id'], $parent_id, $level, $cnt, $bom, $new_number, $one_q, $childNode['bom_trn_id'], $childNode['product_type'], $pro_setting, $req_no, $bom_real_level, $pid12);
            } else {

                $pro_setting = get_pro_field($dbcon, $childNode['product_id'], 'product_setting_check');

                $pr_setting_arr = explode(",", $pro_setting);

                if (in_array("process_product", $pr_setting_arr)) {
                    $readonly = "";
                    $in_check_qty = "";
                } else {
                    $readonly = "readonly";
                    $in_check_qty = "1";
                }

                if ($bom_real_level == 1) {
                    $display = 'display:none';
                } else if (check_level_open($dbcon, $childNode['parent_id'], $req_no) > 0) {
                    $display = 'display:block';
                } else {
                    $display = 'display:none';
                }

                $new_number = $number . '.' . $cntt;
                $counter_tree++;
                $rev_sto = check_reserve_stock($dbcon, $childNode['product_id'], $req_no);
                if (check_requested($dbcon, $childNode['product_id'], $req_no) == 0) {
                    $kp2 = str_replace(".", "", $new_number);
                    $btn = '<a class="btn btn-primary dispbtn csb' . $childNode['bom_trn_id'] . ' sho' . $kp2 . ' " data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request(' . $counter_tree . ',' . $childNode['bom_trn_id'] . ');show_btn(' . $kp2 . ',1);change_status(' . $kp2 . ');" ><i class="fa fa-paper-plane"></i> Request</a>';
                    //style="'.$display.'"
                    $btn .= '<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';

                    $btn .= '<input type="hidden" id="reqsho' . $kp2 . '" value="1" />';

                    $submi = "1";
                } else {
                    $kp2 = str_replace(".", "", $new_number);
                    //$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
                    $btn = '<a class="btn btn-danger rsho' . $kp2 . '" data-original-title="" data-toggle="tooltip" data-placement="top" >Requested</a>';

                    $btn .= '<input type="hidden" name="btn_validate" required id="btn_validate" value="1" />';

                    $btn .= '<input type="hidden" id="reqsho' . $kp2 . '" value="0" />';
                    $submi = "0";
                }
                $get_unit2 = "select btrn.product_uom,btrn.product_base_unit,umst.unit_name as purchase_unit,umst1.unit_name as process_unit from tbl_bomtrn as btrn
                left join unit_mst as umst on umst.unitid=btrn.product_uom
                left join unit_mst as umst1 on umst1.unitid=btrn.product_base_unit
                where bom_trn_id = '" . $childNode['bom_trn_id'] . "'";
                $res_unit2 = $dbcon->query($get_unit2);
                $chil_unit2 = mysqli_fetch_assoc($res_unit2);

                $cstock = get_current_stock_new($dbcon, $childNode['product_id'], $chil_unit2["product_uom"]);
                $rstock = reserve_stock($dbcon, $childNode['product_id'], $chil_unit2["product_uom"]);
                $actualstock = $cstock - $rstock;
                if ($actualstock < "0") {
                    $actualstock = 0;
                }
                $reqhi = "";
                if ($actualstock == 0) {
                    $reqhi = "readonly";
                }

                //$tital1 = get_product_type_by_id($dbcon,get_pro_field($dbcon,$childNode['product_id'],'product_type');
                echo '<tr>
                <td  class="td1">' . $new_number . '</td>
                <td   class="td2" title="' . get_product_type_by_id($dbcon, get_pro_field($dbcon, $childNode['product_id'], 'product_type')) . '" >' . $pro_name . '</td>
                <!--<td class="td4">' . get_product_type_by_id($dbcon, get_pro_field($dbcon, $childNode['product_id'], 'product_type')) . '</td>-->
                <td class="td4">' . get_pro_field($dbcon, $childNode['product_id'], 'product_min_stock') . '</td>
                <!--<td class="td5">' . get_current_stock_new($dbcon, $childNode['product_id'], $chil_unit2["product_uom"]) . '</td>
                <td class="td5">' . reserve_stock($dbcon, $childNode['product_id'], $chil_unit2["product_uom"]) . '</td>-->
                <td class="td5">
                <input type="number"  class="form-control" name="at_stock' . $counter_tree . '" id="at_stock' . $counter_tree . '" onkeypress="return isNumberKey(event)" value="' . $actualstock . '" readonly />
                </td>
                <td class="td5">
                <div class="col-md-9">
                <input type="number" min="0" class="form-control rt' . $childNode['bom_trn_id'] . '" name="req_qty' . $counter_tree . '" id="req_qty' . $counter_tree . '" onkeypress="return isNumberKey(event)"  />
                </div>
                <div class="col-md-3" style="font-size:16px;">
                <strong>' . $chil_unit2["purchase_unit"] . '</strong>
                </div>
                <!--<input type="text" class="form-control tct' . $childNode['bom_trn_id'] . '" name="total_qty' . $counter_tree . '" id="total_qty' . $counter_tree . '" value="' . $childNode['product_qty'] . '"  />-->
                <input type="hidden" class="form-control tct' . $childNode['bom_trn_id'] . '" name="total_qty' . $counter_tree . '" id="total_qty' . $counter_tree . '" value="' . $one_q . '"  />

                </td>
                <td class="td5">
                <input type="number"  class="form-control" name="at_reserve' . $counter_tree . '" id="at_reserve' . $counter_tree . '" onkeypress="return isNumberKey(event)" value="' . $rev_sto . '" ' . $reqhi . ' ' . $text_readonly . ' onkeyup="check_reseve_qty(' . $counter_tree . ',this.value);" />
                </td>

                <td class="td5">
                <div class="col-md-9">
                <input type="number" min="0" class="form-control pt' . $childNode['bom_trn_id'] . '" name="in_process_qty' . $counter_tree . '" id="in_process_qty' . $counter_tree . '" ' . $readonly . ' onkeypress="return isNumberKey(event)" onkeyup="get_inhouse_inner(' . $counter_tree . ',' . $childNode['bom_trn_id'] . ');check_req_qty(' . $counter_tree . ',this.value);" />
                </div>
                <div class="col-md-3" style="font-size:16px;">
                <strong>' . $chil_unit2["process_unit"] . '</strong>
                <input type="hidden" class="form-control" name="process_unit' . $counter_tree . '" id="process_unit' . $counter_tree . '" value="' . $chil_unit2["product_base_unit"] . '"  />
                </div>

                <input type="hidden" class="form-control inpc' . $childNode['bom_trn_id'] . '" name="in_process_qty_check' . $counter_tree . '" id="in_process_qty_check' . $counter_tree . '" value="' . $in_check_qty . '"  />
                </td>

                <td class="td5">
                <div class="col-md-9">
                <input type="number" min="0" class="form-control po' . $childNode['bom_trn_id'] . '" name="po_qty' . $counter_tree . '" id="po_qty' . $counter_tree . '" onkeypress="return isNumberKey(event);" onkeyup="check_req_qty(' . $counter_tree . ',this.value);" />
                </div>
                <div class="col-md-3" style="font-size:16px;">
                <strong>' . $chil_unit2["purchase_unit"] . '</strong>
                <input type="hidden" class="form-control" name="purchase_unit' . $counter_tree . '" id="purchase_unit' . $counter_tree . '" value="' . $chil_unit2["product_uom"] . '"  />
                </div>

                <input type="hidden" class="form-control" name="pr_id' . $counter_tree . '" id="pr_id' . $counter_tree . '" value="' . $childNode['product_id'] . '"  />
                <input type="hidden" class="form-control" name="pr_type' . $counter_tree . '" id="pr_type' . $counter_tree . '" value="' . $childNode['product_type'] . '" />
                </td>
                <td class="td5">' . $btn . '
                <input type="hidden" class="perent' . $childNode['bom_trn_id'] . '" name="perent' . $counter_tree . '" id="perent' . $counter_tree . '" value="' . $pid1 . '" />

                <input type="hidden" class="submi' . $childNode['bom_trn_id'] . '" name="submi[]" id="submi' . $counter_tree . '" value="' . $submi . '" />
                </td>
                </tr>';

                $level++;
                $cnt++;
                $cntt++;
                //get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);

            }

            //$cntt++;

        }

        //return $str_tree;

    }

    return $counter_tree;
}
function gettransp($dbcon, $eid)
{
    $query = "select * from transportation_details where status=0";
    $rs_dispatch = $dbcon->query($query);
    $str = '';
    $str .= '<option value="">Choose Transport</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['id'] == $eid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['id'] . '">' . $rel['transportation_name'] . '</option>';
    }
    return $str;
}
function getdlivarytype($dbcon, $eid)
{
    $query = "select * from tbl_apson_dilivary_type where status=0";
    $rs_dispatch = $dbcon->query($query);
    $str = '';
    $str .= '<option value="">Choose DELIVERY</option>';
    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';
        if ($rel['dilivary_type_id'] == $eid) {
            $sel = 'selected="selected"';
        }
        $str .= '<option ' . $sel . ' value="' . $rel['dilivary_type_id'] . '">' . $rel['dilivary_type_name'] . '</option>';
    }
    return $str;
}

function get_assign_users_data_query($dbcon, $crm_user_type)
{
    $whr = " AND usr.user_type IN (" . $crm_user_type . ")";
    if ($_SESSION['user_type'] == 2) {
        $qry = "select usr.user_id,usr.user_name,type.usertype_name from users as usr left join tbl_usertype as type on type.usertype_id=usr.user_type where usr.active=0 and usr.user_type!=1 " . $whr . " and usr.company_id IN (0," . $_SESSION['company_id'] . ")";
    } else {
        $user_id = check_user_chein($dbcon, $_SESSION['user_id'], 1);
        $qry = "select usr.user_id,usr.user_name,type.usertype_name from users as usr left join tbl_usertype as type on type.usertype_id=usr.user_type where usr.user_id in (" . $user_id . ") AND usr.active=0 and usr.user_type!=1 " . $whr . " and usr.company_id IN (0," . $_SESSION['company_id'] . ")";
    }
    return $qry;
}
function get_godown($dbcon, $all = '')
{
    $str = '';
    $query = "SELECT gd_id, gd_name FROM mst_godown WHERE g_status IN ('0','1')  and company_id=" . $_SESSION['company_id'];
    $rs_dispatch = $dbcon->query($query);
    if ($all == '') {
        $str = '<option value="">Select Godown</option>';
    }
    if ($all != '') {
        $str .= '<option value="">--ALL--</option>';
    }

    while ($rel = brp_mysqli_fetch_assoc($rs_dispatch)) {
        $sel = '';

        $str .= '<option ' . $sel . ' value="' . $rel['gd_id'] . '">' . $rel['gd_name'] . '</option>';
    }
    return $str;
}
?>