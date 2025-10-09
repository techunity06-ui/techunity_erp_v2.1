<?php
session_start();
include('../include/urlfile.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    INQUIRY_SLUG_CREATE,
    INQUIRY_SLUG_EDIT,
    INQUIRY_SLUG_VIEW
]);

$infopage = pathinfo(__FILE__);
$_SESSION['page'] = 'crm/' . $infopage['filename'];
$form = "Inquiry";
$countryid = '101';
$stateid = '1';
$cityid = '1';
$email_template_id = '';
$branch_id = $_SESSION['branch_id'];
$inquiry_type = '';
$edit_branch_id = '';
$inquiry_id = '';
$objection_flag = 0;

if (strpos($_SERVER['REQUEST_URI'], "inquiry_edit") == true) {
    if (!in_array(INQUIRY_SLUG_EDIT, $bulkAccessArray)) {
        header("Location: " . DOMAIN . "permission_access");
    }

    $mode = "Edit";
    $inquiry_id = $dbcon->real_escape_string($_REQUEST['id']);

    $query = "SELECT inquiry.*,usr.user_name FROM tbl_inquiry as inquiry
    LEFT JOIN users as usr ON usr.user_id = inquiry.user_id
    WHERE inquiry.inquiry_id = $inquiry_id";
    $rel = mysqli_fetch_assoc($dbcon->query($query));

    if (!$rel) {
        header("Location: " . ROOT . CRM_ROOT . "inquiry_list");
    }

    if ($rel['opp_id'] == WON) {
        header("Location: " . ROOT . CRM_ROOT . "inquiry_list");
    }

    $inquiry_date = date('d-m-Y', strtotime($rel['inquiry_date']));
    $closing_date = '';
    if ($rel['closing_date'] != "1970-01-01" && $rel['closing_date'] != "0000-00-00") {
        $closing_date = date('d-m-Y', strtotime($rel['closing_date']));
    }
    $cust_id = $rel['cust_id'];
    $user_name = $rel['user_name'];
    $assign_user_inq_ids = $rel['assign_user_inq_ids'];
    $edit_branch_id = $rel['branch_id'];
    $inquiry_type = $rel['inquiry_type'];
    // Amish Soni Start 19-01-2021
    $email_template_id = $rel['email_template_id'];
    // Amish Soni End 19-01-2021

    $objection_flag = $rel['objection_flag'];
} elseif (strpos($_SERVER['REQUEST_URI'], "inquiry_view") == true) {
    if (!in_array(INQUIRY_SLUG_VIEW, $bulkAccessArray)) {
        header("Location: " . DOMAIN . "permission_access");
    }
    $mode = "view";
    $inquiry_id = $dbcon->real_escape_string($_REQUEST['id']);
    $query = "SELECT inquiry.*,usr.user_name FROM tbl_inquiry as inquiry
    LEFT JOIN users as usr ON usr.user_id = inquiry.user_id
    WHERE inquiry.inquiry_id  =  $inquiry_id";
    $rel = mysqli_fetch_assoc($dbcon->query($query));
    $inquiry_date = date('d-m-Y', strtotime($rel['inquiry_date']));
    $closing_date = '';
    if ($rel['closing_date'] != "1970-01-01" && $rel['closing_date'] != "0000-00-00") {
        $closing_date = date('d-m-Y', strtotime($rel['closing_date']));
    }
    $cust_id = $rel['cust_id'];
    $user_name = $rel['user_name'];
    $edit_branch_id = $rel['branch_id'];
    $inquiry_type = $rel['inquiry_type'];
    $assign_user_inq_ids  =  $rel['assign_user_inq_ids'];
    $objection_flag = $rel['objection_flag'];
} else {
    if (!in_array(INQUIRY_SLUG_CREATE, $bulkAccessArray)) {
        header("Location: " . DOMAIN . "permission_access");
    }
    $mode = "Add";
    $inquiry_date = date('d-m-Y');
    // $closing_date = date('d-m-Y');

    $user_name = $_SESSION['user_name'];
    $assign_user_inq_ids  = $_SESSION['user_id'];
}

$set = "select * from tbl_company where company_id = " . $_SESSION['company_id'];
$set_head = mysqli_fetch_assoc($dbcon->query($set));

if ($mode == "Add") {
    $rel['currency_id'] = $set_head['currency_id'];
}

// Amish Soni Start 19-01-2021
$crm_auto_mail = '';
$project_wise_manufacturing = '';
$project_wise_item_rate = '';
$companySettings = getCompanySettings($dbcon);
$max_followup_date = MAX_FOLLOWUP_DATE;
if ($companySettings) {
    $crm_auto_mail = $companySettings['crm_auto_mail'];
    $project_wise_manufacturing = $companySettings['project_wise_manufacturing'];
    $project_wise_item_rate = $companySettings['project_wise_item_rate'];
    if ($companySettings['max_followup_date'] != 0) {
        $max_followup_date = (int)$companySettings['max_followup_date'];
    }
}
$showTemplate = ($crm_auto_mail == 'No');
// Amish Soni End 19-01-2021
$companyConfiguration = getCompanyConfiguration($dbcon);
$crm_pro_type = $companyConfiguration['crm_pro_type'];
$crm_pro_search = $companyConfiguration['crm_pro_search'];
$crm_user_type = $companyConfiguration['crm_user_type'] ? $companyConfiguration['crm_user_type'] : $crm_user_type;
$getspecialConfiguration = getspecialConfiguration($dbcon);

$allow_prev_inquiry_date = 0;

$is_umaboy = false;
if ($getspecialConfiguration['umaboy_permission'] == '1') {
    $is_umaboy = true;
}

if ($getspecialConfiguration['jainflex_permission'] == '1') {
    $allow_prev_inquiry_date = 1;
}

$allow_objection = 0;
if ($getspecialConfiguration['umaboy_permission'] == '1') {
    $allow_objection = 1;
}

if ($mode == "Add") {
    $closdate = $companyConfiguration['closing_date_diff'];
    if (!empty($closdate)) {
        $closing_date = date('d-m-Y', strtotime('+' . $closdate . ' days'));
        // $closing_date =$closdate;
    } else {
        $closing_date = date('d-m-Y');
    }
}
function get_tax_category_new($dbcon, $eid = '')
{
    $qry = "select * from tbl_tax_category where isdelete='0'";
    $select = $dbcon->query($qry);
    $str = '';
    $str .= '<option value="">--Select Tax Category--</option>';
    while ($row = brp_mysqli_fetch_assoc($select)) {
        $sel = '';

        if ($row['tax_cat_id'] == $eid) {
            $sel = 'selected=selected';
        }

        $str .= '<option value="' . $row['tax_cat_id'] . '" ' . $sel . '>' . $row['tax_cat_name'] . '</option>';
    }
    return $str;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>INQUIRY</title>
    <?php include_once('../../include/include_css_file.php'); ?>
    <style type="text/css">
        .cke_notification_warning {
    display: none !important;
}

        .currency_icon {
            color: green;
            font-size: 12px;
            font-weight: bold;
        }

        .product-item-div {
            position: relative;
            overflow: hidden;
        }

        .product-item-import {
            position: absolute;
            font-size: 50px;
            opacity: 0;
            right: 0;
            top: 0;
        }
    </style>
    <!-- <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2.min.css" />
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-css/1.4.6/select2-bootstrap.min.css" /> -->

</head>

<body>
    <section id="container" class="sidebar-closed">
        <!--class="side bar-closed"-->
        <?php include_once('../../include/include_top_menu.php'); ?>
        <!--side bar start-->
        <?php include_once('../../include/left_menu.php'); ?>
        <!--side bar end-->
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <!--bread crumbs start -->
                        <section class="panel">
                            <header class="panel-heading">
                                <h3><?= $mode . ' ' . $form ?></h3>
                                <!--<div class="text-center">Owner : <strong><?= $user_name ?></strong></div>-->
                            </header>
                            <div class="">
                                <ul class="breadcrumb">
                                    <li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="<?= ROOT . CRM_ROOT . 'inquiry_list' ?>"><?= $form ?> List</a></li>
                                </ul>
                            </div>
                        </section>
                        <!--bread crumbs end -->
                    </div>
                </div>
                <!--state overview start-->
                <div class="row">
                    <div class="col-md-12">
                        <section class="panel">
                            <header class="panel-heading">
                                New <?= $form ?>
                            </header>
                            <div class="panel-body">
                                <form class="form-horizontal" role="form" id="inquiry_add" action="javascript:;" method="post" name="inquiry_add">
                                    <input type="hidden" id="is_umaboy" name="is_umaboy" value="<?= $is_umaboy ?>">
                                    <div class="row">
                                        <?php if ($project_wise_manufacturing == 'Yes') { ?>
                                            <div class="clearfix"></div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Inquiry Type*</label>
                                                    <div class="col-md-8">
                                                        <?php if ($inquiry_type != '0' && $inquiry_type != '') { ?>
                                                            <select class="select2" onchange="product_load();" disabled="">
                                                                <?= getInquiryType($dbcon, $inquiry_type) ?>
                                                            </select>
                                                            <input type="hidden" id="inquiry_type" name="inquiry_type" value="<?= $inquiry_type ?>">
                                                        <?php } else { ?>
                                                            <select class="select2" id="inquiry_type" name="inquiry_type" onchange="product_load();" <?= $is_umaboy ? 'disabled=true' : ''; ?>>
                                                                <?= getInquiryType($dbcon, '1') ?>
                                                            </select>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } else { ?>
                                            <input type="hidden" id="inquiry_type" name="inquiry_type" value="1">
                                        <?php } ?>

                                        <?php if ($mode !== 'Add') { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Inquiry No*</label>
                                                    <div class="col-md-8">
                                                        <input id="inquiry_no" name="inquiry_no" type="text" class="form-control" title="Enter Inquiry No" value="<?= $rel['inquiry_no'] ?>" placeholder="Inquiry No" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Inquiry Date*</label>
                                                <div class="col-md-8">
                                                    <input id="inquiry_date" name="inquiry_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $inquiry_date ?>" placeholder="Inquiry Date" <?phpecho ($mode == "view") ? "readonly" : "" ?>>
                                                </div>
                                            </div>
                                        </div>
                                        <?phpif ($companyConfiguration['branch_wise_manage'] == 1) { ?>
                                            <div class="col-md-4">
                                                <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], $is_umaboy, true, '', '4', '8'); ?>
                                            </div>
                                        <?php} ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Company*</label>
                                                <div class="col-md-6">
                                                    <select class="select2" id="cust_id" name="cust_id" onchange="load_cust_person(this.value);copy_inq_name(<?= $companyConfiguration['inq_name_using_comapany'] ?>);" <?phpecho ($mode == "view") ? "disabled" : "" ?>>
                                                        <?= getcustomer($dbcon, $cust_id) ?>
                                                    </select>
                                                </div>
                                                <?phpif ($mode == 'Add') { ?>
                                                    <div class="col-md-1">
                                                        <button type="button" id="addcust" onclick="addcustomer()" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                                    </div>
                                                <?php} ?>
                                                <div class="col-md-1">
                                                    <button type="button" id="viewcompany" onclick="preview_cust_dtls()" title="View Company" class="btn btn-primary"><i class="fa fa-eye"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Contact Person</label>
                                                <div class="col-md-5">
                                                    <select class="select2" id="c_con_id" name="c_con_id" <?phpecho ($mode == "view") ? "disabled" : "" ?>>
                                                        <?= get_cust_contactperson($dbcon, $rel['c_con_id'], $cust_id); ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" id="addcustper" onclick="open_cust_contact()" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" id="viewcustper" onclick="preview_cust_person()" title="View Contact Persons" class="btn btn-primary"><i class="fa fa-eye"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Inquiry Name</label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control" id="inquiry_name" name="inquiry_name" placeholder="Inquiry Name" value="<?= $rel['inquiry_name'] ?>" <?phpecho ($mode == "view") ? "readonly" : "" ?>>
                                                </div>
                                            </div>
                                        </div>

                                        <?phpif ($getspecialConfiguration['austar_permission'] != 1) { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Closing Date</label>
                                                    <div class="col-md-8">
                                                        <input id="closing_date" name="closing_date" autocomplete="off" type="text" class="form-control default-date-picker" title="Date" value="<?= $closing_date ?>" placeholder="Closing Date" <?phpecho ($mode == "view") ? "readonly" : "" ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php} ?>
                                        <!-- <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Territory</label> 
                                                    <div class="col-md-8">
                                                        <select class="select2" id="t_id" name="t_id" <?phpecho ($mode == "view") ? "disabled" : "" ?>>
                                                            <?php//=get_all_territory($dbcon,$rel['t_id']);
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Stage*</label>
                                                <div class="col-md-8">
                                                    <select class="select2" id="opp_id" name="opp_id" onchange="show_lost_reason();change_inquiry_stage(this.value);" <?phpecho ($mode == "view") ? "disabled" : "" ?>>
                                                        <?= get_inquiry_stage($dbcon, $rel['opp_id']); ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Probability(%)</label>
                                                <div class="col-md-8">
                                                    <input type="text" id="stage_prob" name="stage_prob" class="form-control" value="<?= $rel['stage_prob'] ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <?phpif ($getspecialConfiguration['austar_permission'] != 1) { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Sales Stage</label>
                                                    <div class="col-md-8">
                                                        <select class="select2" id="sales_stage_id" name="sales_stage_id" <?phpecho ($mode == "view") ? "disabled" : "" ?>>
                                                            <option value="">Choose Sales Stage</option>
                                                            <?= get_master_category_dtl($dbcon, $rel['sales_stage_id'], 7,'',''); //7:Sales Stage
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php} ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Type</label>
                                                <div class="col-md-8">
                                                    <select class="select2" id="inquiry_type_id" name="inquiry_type_id" <?phpecho ($mode == "view") ? "disabled" : "" ?>>
                                                        <option value="">Choose Opportunity Type</option>
                                                        <?= get_master_category_dtl($dbcon, $rel['inquiry_type_id'], 8,'',''); //8:Type
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-8 lost_reasons" id="lost_reason_div" style="display: block;">
                                            <?php if ($rel['opp_id'] == LOST) {
                                                $reason_array = json_decode($rel['closed_reason'], true);
                                                foreach ($reason_array as $reason_id => $remark) { ?>
                                                    <div class="form-group">
                                                        <label class="col-md-2 control-label" style="text-align: right;">Reason*</label>
                                                        <div class="col-md-4">
                                                            <select class="select2 reasonid" id="reason_id" name="reason_id[]">
                                                                <?= get_lost_reasons($dbcon, $reason_id) ?>
                                                            </select>
                                                        </div>
                                                        <label class="col-md-2 control-label">Reason Remark*</label>
                                                        <div class="col-md-4">
                                                            <textarea class="form-control reason_remark" name="lost_reason[]" id="lost_reason" style="resize:both;" placeholder="Lost Reason" rows="1" /><?= $remark ?></textarea>
                                                        </div>
                                                    </div>
                                                <?php }
                                            } else { ?>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label" style="text-align: right;">Reason*</label>
                                                    <div class="col-md-4">
                                                        <select class="select2 reasonid" id="reason_id" name="reason_id[]">
                                                            <?= get_lost_reasons($dbcon, $id) ?>
                                                        </select>
                                                    </div>
                                                    <label class="col-md-2 control-label">Reason Remark*</label>
                                                    <div class="col-md-3">
                                                        <textarea class="form-control reason_remark" name="lost_reason[]" id="lost_reason" style="resize:both;" placeholder="Lost Reason" rows="1"></textarea>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" id="reason_btn" class="btn btn-primary" title="View Details" onclick="add_reason_div()"><i class="add_remove_reason fa fa-plus"></i></button>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <input type="hidden" id="counter" name="counter" value="1">
                                        </div>

                                        <!-- <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Source </label>
                                                    <div class="col-md-8"> 
                                                        <select class="select2" id="rb_id" name="rb_id"<?phpecho ($mode == "view") ? "disabled" : "" ?>>
                                                            <?php//=get_refer_by($dbcon,$rel['rb_id']);
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> -->
                                        <?phpif ($getspecialConfiguration['austar_permission'] != 1) { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Inquiry Category</label>
                                                    <div class="col-md-8">
                                                        <select class="select2" id="inquiry_cat_id" name="inquiry_cat_id" <?phpecho ($mode == "view") ? "disabled" : "" ?>>
                                                            <option value="">Choose Inquiry Category</option>
                                                            <?= get_master_category_dtl($dbcon, $rel['inquiry_cat_id'], 9,'',''); //9:Category
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php}
                                        ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Currency</label>
                                                <div class="col-md-8">
                                                    <select class="select2" id="currency_id" name="currency_id" onchange="get_symbol();currency_rate_c(<?php$is_umaboy ?>);" <?phpecho ($mode == "view" || $is_umaboy) ? "disabled" : "" ?>>
                                                        <?= getcurrency($dbcon, $rel['currency_id']) ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Currency Rate</label>
                                                <div class="col-md-8">
                                                    <input <?= $is_umaboy ? "readonly" : ''; ?> id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?= $mode == 'Edit' ? $rel['currency_rate'] : '' ?>" placeholder="">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Total <span class="currency_icon"> </span></label>
                                                <div class="col-md-8">
                                                    <input type="number" min="0" id="g_total" name="g_total" class="form-control" value="<?= $rel['g_total'] ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Assign To* </label>
                                                <div class="col-md-8">
                                                    <select class="select2" id="assign_user_inq_ids" name="assign_user_inq_ids" required onchange="no_of_inquiry(this)">
                                                        <?= get_assign_users($dbcon, $assign_user_inq_ids, " and user_type in(" . $crm_user_type . ")"); ?>
                                                    </select>
                                                    <div id="no_of_inquiry" style="font-size: 12px; color: #337ab7;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <?phpif ($getspecialConfiguration['austar_permission'] != 1) { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Priority</label>
                                                    <div class="col-md-8">
                                                        <select class="select2" id="task_priority_id" name="task_priority_id">
                                                            <?= get_task_priority($dbcon, $rel['task_priority_id']); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php} ?>
                                        <?php  //Show Flp field only if add mode
                                        if ($mode == 'Add') { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Task</label>
                                                    <div class="col-md-8">
                                                        <?php//=get_master_category_dtl($dbcon,$task_type_id,10,"",1);//10:Task
                                                        ?>
                                                        <select class="select2" id="task_type_id" name="task_type_id" title="Choose Task Type">
                                                            <option value="">Choose Task Type</option>
                                                            <?= get_master_category_dtl($dbcon, 16, 10,'',''); //10:Task
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Follow-Up Date*</label>
                                                    <div class="col-md-8">
                                                        <div data-date="<?= $task_due_date ?>" class="input-group date form_datetime-meridian">
                                                            <input type="text" class="form-control" value="<?= $task_due_date ?>" name="task_due_date" id="taskd_ue_date" autocomplete="off">
                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
                                                            </div>
                                                        </div>
                                                        <!-- <input id="task_due_date" name="task_due_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<= $task_due_date ?>" placeholder="Follow Up Date"> -->
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($showTemplate) { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Email Template</label>
                                                    <div class="col-md-8">
                                                        <select class="select2" id="email_template_id" name="email_template_id">
                                                            <?php echo getAllEmailSMSTemplate($dbcon, 2, $email_template_id) ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">GST Type</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="form-control" name="gst_type" id="gst_type">
                                                        <option value="1" <?php if ($rel['gst_type'] == 1) {
                                                                                echo "selected";
                                                                            } else {
                                                                                echo "";
                                                                            } ?>>Item Wise Tax</option>
                                                        <option value="2" <?php if ($rel['gst_type'] == 2) {
                                                                                echo "selected";
                                                                            } else {
                                                                                echo "";
                                                                            } ?>>Merchant</option>
                                                        <option value="3" <?php if ($rel['gst_type'] == 3) {
                                                                                echo "selected";
                                                                            } else {
                                                                                echo "";
                                                                            } ?>>SEZ</option>
                                                        <option value="4" <?php if ($rel['gst_type'] == 4) {
                                                                                echo "selected";
                                                                            } else {
                                                                                echo "";
                                                                            } ?>>GST 0%</option>
                                                        <option value="5" <?php if ($rel['gst_type'] == 5) {
                                                                                echo "selected";
                                                                            } else {
                                                                                echo "";
                                                                            } ?>>GST 5%</option>
                                                        <option value="6" <?php if ($rel['gst_type'] == 6) {
                                                                                echo "selected";
                                                                            } else {
                                                                                echo "";
                                                                            } ?>>GST 12%</option>
                                                        <option value="7" <?php if ($rel['gst_type'] == 7) {
                                                                                echo "selected";
                                                                            } else {
                                                                                echo "";
                                                                            } ?>>GST 18%</option>
                                                        <option value="8" <?php if ($rel['gst_type'] == 8) {
                                                                                echo "selected";
                                                                            } else {
                                                                                echo "";
                                                                            } ?>>GST 24%</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <?phpif ($getspecialConfiguration['elcon_permission'] == 1 || $getspecialConfiguration['filter_concept_permission'] == 1) { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Project Name</label>
                                                    <div class="col-md-8">
                                                        <input type="text" id="project_name" name="project_name" class="form-control" value="<?= $rel['project_name'] ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php} ?>
                                        <?phpif ($allow_objection == 1) { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Objection</label>
                                                    <div class="col-md-8">
                                                        <input type="checkbox" id="objection_flag" name="objection_flag" value="1" <?php if ($objection_flag == 1) {
                                                                                                                                        echo "checked onclick='return false;'";
                                                                                                                                    } ?>> <span class="check_span">Objection </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php} ?>
                                        <div class="clearfix"></div>
                                        <hr />
                                        <div class="col-md-12">
                                            <div class="card">
                                                <!-- <form class="form-horizontal" role="form" id="import_inquiry_product" action="javascript:;" method="post" name="import_inquiry_product" enctype="multipart/form-data"> -->
                                                    <div class="col-md-12 import_inquiry_product_hide" style="margin-top:10px;">
                                                        <div class="col-md-12">
                                                            <div class="col-md-10 col-xs-6"></div>
                                                            <div class="col-md-1 col-xs-3 product-item-div btn btn-success btn-flat">Import
                                                                <input type="file" id="product_import_file" name="product_import_file" class="product-item-import" style="cursor: pointer;" />
                                                                <div id="msg"></div>
                                                            </div>
                                                            <div class="col-md-1 col-xs-3">
                                                                <a href="<?= ROOT . INQUIRY_PRODUCT_FILE_VWING . 'inquiry_product_sample.csv' ?>" target="_blank" class="btn btn-info">Sample Formate File</a>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12" style="margin-top: 10px;">
                                                            <div id="import_filename" class="pull-right"></div>
                                                        </div>
                                                        <div class="col-md-12" style="margin-top:10px;">
                                                            <input type="submit" name="upload_file_data" id="upload_file_data" class="btn btn-primary pull-right hide" value="Upload" style="margin-right:5%;" />
                                                        </div>
                                                    </div>
                                                <!-- </form> -->
                                                <ul class="nav nav-tabs" id="my_tab_id" role="tablist">
                                                    <li role="presentation" id="tab1" class="active"><a href="#product-details" aria-controls="product-details" role="tab" data-toggle="tab">Product Details</a></li>
                                                    <li role="presentation" id="tab2"><a href="#product-desc" aria-controls="product-desc" role="tab" data-toggle="tab">Description</a></li>
                                                </ul>
                                                <!-- Tab panes -->
                                                <div class="tab-content">
                                                    <!-- Remaks Tab Start -->
                                                    <div role="tabpanel" class="tab-pane active" id="product-details">
                                                        <div class="col-md-12">
                                                            <div class="form-group" style="margin-top:20px;overflow-x:scroll;">
                                                                <table class="display table table-bordered table-striped">
                                                                    <thead>
                                                                        <?phpif ($mode != "view") { ?>
                                                                            <tr>
                                                                                <?phpif ($companyConfiguration['category_selection_active'] == 1) { ?>
                                                                                    <th width="15%" class="text-center">Product Category</th>
                                                                                <?php} ?>
                                                                                <th width="25%" class="text-center">Product Name</th>
                                                                                <?phpif ($getspecialConfiguration['reciclar'] == 1) { ?>
                                                                                    <th width="15%" class="text-center">Reciclar Category</th>
                                                                                <?php} ?>
                                                                                <!--<th width="15%" class="text-center">Product Group</th>-->
                                                                                <!--<th width="10%" class="text-center">Level</th>-->
                                                                                <th width="" class="text-center">Unit</th>
                                                                                <th width="" class="text-center">Quantity</th>
                                                                                <th width="" class="text-center">Rate <span class="currency_icon"> </span></th>
                                                                                <th width="" class="text-center">Amount <span class="currency_icon"> </span></th>
                                                                                <th width="2%" class="text-center">Action</th>
                                                                            </tr>
                                                                        <?php} ?>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?phpif ($mode != "view") { ?>
                                                                            <tr>
                                                                                <?phpif ($companyConfiguration['category_selection_active'] == 1) { ?>
                                                                                    <td>
                                                                                        <select class="select2" name="cat_id" id="cat_id" title="Select Category" <?phpif ($companyConfiguration['cat_wise_product_load'] == 1) { ?> onchange="product_load()" <?php} ?>>
                                                                                            <?= get_all_category($dbcon, 0); ?>
                                                                                        </select>
                                                                                    </td>
                                                                                <?php} ?>
                                                                                <td style="max-width: 300px;">
                                                                                    <!--<select class="select2" id="product_id" name="product_id" onchange="load_product_dtls(this.value)">
                                                                            <=getproduct_typewise($dbcon,"",$crm_pro_type,$crm_pro_search);?>
                                                                        </select>-->
                                                                                    <input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onchange="load_product_dtls(this.value);get_hsn(this.value);" />
                                                                                    <br><label id="current_stock" style="display: none;"></label><strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong><br>

                                                                                    <!-- harshil :- tempory close karyu che time layi ne update karavu <button type="button" id="projectItem" onclick="load_project_item()" title="View Project Wise Item List" class="btn btn-primary" style="display: none;">View Item List <i class="fa fa-plus"></i></button>-->&nbsp;&nbsp;&nbsp;
                                                                                    <button type="button" id="productHistory" onclick="load_product_history()" title="View Product History" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></button>&nbsp;&nbsp;&nbsp;
                                                                                    <?phpif ($getspecialConfiguration['invoite_permission'] != 1 && $getspecialConfiguration['smpl_permission'] != '1') { ?>
                                                                                        <button accesskey="n" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" onclick="showproduct()"><i class="fa fa-plus"></i> Add Product</button>
                                                                                    <?php} ?>
                                                                                </td>

                                                                                <?phpif ($getspecialConfiguration['reciclar'] == 1) { ?>
                                                                                    <td width="15%" class="text-center">
                                                                                        <select class="select2" name="parent_cat_id" id="parent_cat_id" title="Parent Category">
                                                                                            <?= get_all_reciclare_category($dbcon, 0); ?>
                                                                                        </select>
                                                                                    </td>
                                                                                <?php} ?>
                                                                                <td>
                                                                                    <!-- <select class="select2" name="unitid" id="unitid" title="Select Unit" onchange="getrate();">
                                                                            <= getunit($dbcon, 0); ?>
                                                                        </select> -->
                                                                                    <select class="form-control" title="Select Unit" placeholder="Unit" name="rate_unit_id" id="rate_unit_id" onchange="load_product_unit();">
                                                                                        <!-- <=getunit($dbcon,0);?> -->
                                                                                        <option value="0">Select Unit</option>
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <div id="convert_unit_block" style="display:none;">
                                                                                        <input type="text" title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty" class="form-control numbersOnly" onkeyup="product_convert_qty(1);" />
                                                                                        <input type="hidden" name="conv_unitid" id="conv_unitid" value="" />
                                                                                        <input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
                                                                                        <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_unit_show"> </span>
                                                                                    </div>
                                                                                    <div id="base_unit_block">
                                                                                        <input type="text" title="Enter Qty" min="0" id="product_qty" name="product_qty" class="form-control numbersOnly" onkeyup="product_convert_qty(2);" />
                                                                                        <input type="hidden" name="unitid" id="unitid" value="" />
                                                                                        <input type="hidden" id="product_qty_hide" name="product_qty_hide" value="" />
                                                                                        <span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show"> </span>
                                                                                    </div>
                                                                                    <!-- <input type="number" min="0" class="form-control" id="product_qty" name="product_qty" onkeyup="get_amount();" value=""> -->
                                                                                </td>
                                                                                <td>
                                                                                    <input type="number" min="0" class="form-control" id="product_rate" name="product_rate" onkeyup="get_amount();" value="">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="number" min="0" class="form-control" id="product_amount" name="product_amount" value="" readonly>
                                                                                </td>
                                                                                <td style="vertical-align:middle;">
                                                                                    <input type="hidden" id="edit_id" name="edit_id" value="">
                                                                                    <input type='hidden' name='pro_cal_type' id='pro_cal_type' value='' />
                                                                                    <?phpif ($getspecialConfiguration['durva_permission'] == 1) { ?>
                                                                                        <input type="button" name="addrow1" id="addrow1" onClick="open_batch_wise_qty()" class="btn btn-primary product_add_batch_wise" value="Add" />
                                                                                        <button type="button" class="btn btn-primary" id="inq_trn_btn" style=" display:none;" onclick="add_field()">Add</button>
                                                                                    <?php} else { ?>
                                                                                        <button type="button" class="btn btn-primary" id="inq_trn_btn" onclick="add_field()">Add</button>
                                                                                    <?php} ?>
                                                                                </td>
                                                                            </tr>
                                                                        <?php} ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" id="product-desc">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
                                                                    <div class="col-md-12">
                                                                        <textarea class="form-control" id="product_desc" name="product_desc" placeholder="Enter Product Description"><?= $rel['product_desc'] ?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="col-md-2 control-label text-left" style="text-align:left;font-weight:bold;">Specification</label>
                                                                    <div class="form-group">
                                                                        <label class="col-md-4 control-label text-left">Choose Specification</label>
                                                                        <div class="col-md-4">
                                                                            <select class="select2 categojj" id="specification_id" name="specification[]" onchange="load_specification_content();" multiple data-placeholder="Choose Annexure">
                                                                                <?= get_specification_types($dbcon, $rel['product_spec_id']); ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <textarea class="form-control" id="product_spec" name="product_spec" placeholder="Enter Product Specification"><?= $rel['product_spec'] ?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group" id="inq_trn_div" style="margin-top:20px;overflow-x:scroll;"></div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <hr />
                                        <div class="clearfix"></div>
                                        <!--tab start-->
                                        <div class="col-md-12">
                                            <div class="card">
                                                <ul class="nav nav-tabs" id="my_tab_id" role="tablist">
                                                    <li role="presentation" id="tab4" class="active"><a href="#task-section" aria-controls="task-section" role="tab" data-toggle="tab">History</a></li>
                                                    <li role="presentation" id="tab1"><a href="#remark-section" aria-controls="remark-section" role="tab" data-toggle="tab">Remark</a></li>
                                                    <li role="presentation" id="tab2"><a href="#attch-section" aria-controls="attch-section" role="tab" data-toggle="tab">Attachments</a></li>
                                                    <li role="presentation" id="tab3"><a href="#note-section" aria-controls="note-section" role="tab" data-toggle="tab">Notes</a></li>
                                                </ul>
                                                <!-- Tab panes -->
                                                <div class="tab-content">
                                                    <!-- Remaks Tab Start -->
                                                    <div role="tabpanel" class="tab-pane" id="remark-section">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
                                                                <div class="col-md-12">
                                                                    <textarea id="inq_desc" name="inq_desc" class="form-control" rows="3" style="resize:both;"><?= $rel['inq_desc'] ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Competition Status</label>
                                                                <div class="col-md-12">
                                                                    <textarea id="inq_comp_desc" name="inq_comp_desc" class="form-control" rows="3" style="resize:both;"><?= $rel['inq_comp_desc'] ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Attachments Tab Start -->
                                                    <div role="tabpanel" class="tab-pane" id="attch-section">
                                                        <div class="form-group" style="margin-top:20px;">
                                                            <?phpif ($mode != 'view') { ?>
                                                                <table class="display table table-bordered table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th width="40%" class="text-center">Document Name</th>
                                                                            <th width="50%" class="text-center">Upload Document</th>
                                                                            <th width="10%" class="text-center">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td>
                                                                                <input type="text" class="form-control" id="inq_attch_doc_name" name="inq_attch_doc_name" value="" placeholder="Document Name">
                                                                            </td>
                                                                            <td>
                                                                                <input type="file" class="form-control" id="inq_attch_file" name="inq_attch_file">
                                                                            </td>
                                                                            <td>
                                                                                <button type="button" class="btn btn-primary" id="inq_attch_btn" onclick="add_inq_attch_field()">Add</button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            <?php} ?>
                                                        </div>
                                                        <div class="form-group" style="margin-top:20px;" id="inq_attch_trn_div"></div>
                                                    </div>
                                                    <!-- Note Tab Start -->
                                                    <div role="tabpanel" class="tab-pane" id="note-section">
                                                        <div class="form-group" style="margin-top:20px;">
                                                            <table class="display table table-bordered table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th width="30%" class="text-center">Title</th>
                                                                        <th width="60%" class="text-center">Description</th>
                                                                        <th width="10%" class="text-center">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>
                                                                            <input type="text" class="form-control" id="inq_note_title" name="inq_note_title" value="" placeholder="Title">
                                                                        </td>
                                                                        <td>
                                                                            <textarea class="form-control" id="inq_note_desc" name="inq_note_desc" placeholder="Description" style="resize:both;"></textarea>
                                                                        </td>
                                                                        <td>
                                                                            <input type="hidden" id="edit_inq_noteid" name="edit_inq_noteid" value="">
                                                                            <button type="button" class="btn btn-primary" id="inq_note_btn" onclick="add_inq_note_field()">Add</button>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="form-group" style="margin-top:20px;" id="inq_notes_trn_div"></div>
                                                    </div>
                                                    <!-- Task Tab Start -->
                                                    <div role="tabpanel" class="tab-pane active" id="task-section">
                                                        <div class="form-group" style="margin-top:20px;">
                                                            <div class="clearfix"></div>
                                                            <?php if ($mode == 'Edit' && $rel['opp_id'] != WON) { ?>
                                                                <!-- <div class="col-md-1">
                                                <a onclick="setFormSubmitting();" href="<?= ROOT . 'task_add/' . $rel['inquiry_id'] ?>" type="button" class="btn btn-primary" ><i class="fa fa-plus"></i> Task</a>
                                                </div>
                                                <div class="col-md-1">
                                                <a onclick="setFormSubmitting();" href="<?= ROOT . 'appointment_add/' . $rel['inquiry_id'] ?>" type="button" class="btn btn-info"><i class="fa fa-plus"></i> Appointment</a>
                                            </div> -->
                                                                <div class="col-md-1">
                                                                    <a onclick="open_add_task_popup(<?= $rel['inquiry_id'] ?>,1);" type="button" class="btn btn-primary"><i class="fa fa-plus"></i> Task</a>
                                                                </div>
                                                                <div class="col-md-1">
                                                                    <a onclick="open_add_task_popup(<?= $rel['inquiry_id'] ?>,2)" type="button" class="btn btn-info"><i class="fa fa-plus"></i> Appointment</a>
                                                                </div>
                                                            <?php } ?>
                                                            <div class="clearfix"></div>
                                                            <table class="display table table-bordered table-striped">
                                                                <tr>
                                                                    <td width="25%" class="text-left">
                                                                        <strong>Remarks</strong>
                                                                    </td>
                                                                    <td width="25%" class="text-left">
                                                                        Description: <?= $rel['inq_desc'] ?>
                                                                    </td>
                                                                    <td width="25%" class="text-left">
                                                                        Competition Status: <?= $rel['inq_comp_desc'] ?>
                                                                    </td>
                                                                    <td width="25%" class="text-left">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="4" class="text-left" style="border-bottom: 1px solid #000 !important;">
                                                                        <?php 
                                                                        if ($mode == 'Edit' && $rel['opp_id'] != WON) {
                                                                        // print_r($rel);exit;
                                                                        $get_quot_qry = "select quotation_id,quotation_no,quotation_date ,approve_status from tbl_quotation where quotation_status != '2' and inquiry_id='" . @$rel['inquiry_id'] . "'";
                                                                        $get_quot_qry_rs = $dbcon->query($get_quot_qry);
                                                                        if (mysqli_num_rows($get_quot_qry_rs)) {
                                                                        ?>
                                                                            <table class="display table table-bordered table-striped">
                                                                                <thead>
                                                                                    <th>Quotation No</th>
                                                                                    <th>Quotation Date</th>
                                                                                    <th>Approve Status</th>
                                                                                    <th>Action</th>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php while ($get_quot_rel = mysqli_fetch_assoc($get_quot_qry_rs)) { ?>
                                                                                        <tr>
                                                                                            <td><?= $get_quot_rel['quotation_no'] ?></td>
                                                                                            <td><?= date("d-m-Y", strtotime($get_quot_rel['quotation_date'])) ?></td>
                                                                                            <td>
                                                                                                <?php
                                                                                                if ($get_quot_rel['approve_status'] == '1') {
                                                                                                    echo '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Authorized</div>';
                                                                                                } else {
                                                                                                    echo '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div>';
                                                                                                }
                                                                                                ?>
                                                                                            </td>
                                                                                            <td>
                                                                                                <a onclick="setFormSubmitting();" href="<?= ROOT . CRM_ROOT . 'quotation_print/' . $get_quot_rel['quotation_id'] ?>" type="button" class="btn btn-primary" target="_blank"> <i class="fa fa-eye"></i> View</a>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php} ?>
                                                                                </tbody>
                                                                            </table>
                                                                        <?php} ?>
                                                                        <?php} ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="4" class="text-left" style="border-bottom: 1px solid #000 !important;"></td>
                                                                </tr>
                                                                <?php 
                                                                if ($mode == 'Edit' && $rel['opp_id'] != WON) {
                                                                $get_task_qry = "select tsk.*,sub.mcd_name as subject,usr.user_name,prior.task_priority_name, user.user_name as assign_name from tbl_task as tsk 
                                            left join tbl_master_category_detail as sub on sub.mcd_id=tsk.task_type_id
                                            left join users as usr on usr.user_id=tsk.user_id
                                            left join users as user on user.user_id=tsk.assign_user_ids
                                            left join task_priority_mst as prior on prior.task_priority_id=tsk.task_priority_id
                                            where tsk.task_status!=2 and tsk.task_rel_id=5 and tsk.inquiry_id='" . @$rel['inquiry_id'] . "' order by tsk.create_date DESC";
                                                                $get_task_qry_rs = $dbcon->query($get_task_qry);
                                                                while ($task_rel = mysqli_fetch_assoc($get_task_qry_rs)) {
                                                                    if ($task_rel['entry_type'] == '1') {

                                                                        $task_completion_date = '';
                                                                        $task_due_date = '';
                                                                        // $task_due_date = date('d-m-Y h:i A',strtotime($task_rel['task_due_date']));
                                                                        if ($task_rel['task_completion_date'] != "1970-01-01 00:00:00" && $task_rel['task_completion_date'] != "0000-00-00 00:00:00") {
                                                                            $task_completion_date = date('d-m-Y h:i A', strtotime($task_rel['task_completion_date']));
                                                                        }
                                                                        if ($task_rel['task_due_date'] != "1970-01-01 00:00:00" && $task_rel['task_due_date'] != "0000-00-00 00:00:00") {
                                                                            $task_due_date = date('d-m-Y h:i A', strtotime($task_rel['task_due_date']));
                                                                        } ?>
                                                                        <tr>
                                                                            <td width="25%" class="text-left"><strong>Task</strong> </td>
                                                                            <?php$tsk_type = "";
                                                                            $tsk_due_time = strtotime($task_rel['task_due_date']);

                                                                            if ($task_rel['task_status'] == '1') {
                                                                                $cur_time = strtotime($task_rel['task_completion_date']);
                                                                                if ($tsk_due_time < $cur_time) {
                                                                                    $tsk_type = "<label style='background:#d9534f;'>(Delayed)</label>";
                                                                                }
                                                                            ?>
                                                                                <td width="25%" class="text-center btn-success">Completed <?= $tsk_type ?></td>
                                                                            <?php} else {
                                                                                $cur_time = strtotime(date('Y-m-d H:i:s'));
                                                                                if ($tsk_due_time < $cur_time) {
                                                                                    $tsk_type = "<label style='background:#d9534f;'>(Delayed)</label>";
                                                                                }
                                                                            ?>
                                                                                <td width="25%" class="text-center btn-warning">Pending <?= $tsk_type ?></td>
                                                                            <?php} ?>
                                                                            <td width="25%" class="text-left">
                                                                                Completion Date: <?= $task_completion_date ?>
                                                                            </td>
                                                                            <td width="25%" class="text-left">
                                                                                Create Date: <?= date('d-m-Y h:i A', strtotime($task_rel['create_date'])); ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="text-left">
                                                                                Subject: <?= $task_rel['subject'] ?>
                                                                            </td>
                                                                            <td class="text-left">
                                                                                Owner: <?= $task_rel['user_name'] ?>
                                                                            </td>
                                                                            <td class="text-left">
                                                                                Priority: <?= $task_rel['task_priority_name'] ?>
                                                                            </td>
                                                                            <td class="text-left">
                                                                                Due Date: <?= $task_due_date ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td colspan="3" class="text-left">
                                                                                Owners Remark : <?= nl2br($task_rel['task_remark']) ?>
                                                                            </td>
                                                                            <td class="text-left">Assign user: <?= $task_rel['assign_name']; ?></td>
                                                                        </tr>
                                                                        <?php $chktaskattach = "SELECT * FROM tbl_task_attachment WHERE task_id = '" . $task_rel['task_id'] . "' AND attch_status != 2 AND company_id = '" . $_SESSION['company_id'] . "'";
                                                                        $get_task_attach = $dbcon->query($chktaskattach);
                                                                        $att = 1;
                                                                        while ($task_attach = mysqli_fetch_assoc($get_task_attach)) { ?>
                                                                            <tr>
                                                                                <td><strong>Attachment <?= $att; ?></strong></td>
                                                                                <td><strong><?= $task_rel['subject'] ?></strong></td>
                                                                                <td><strong><?= $task_attach['attachment_name'] ?></strong></td>
                                                                                <td><a href="<?= ROOT . INQ_ATTACH_VWING . $task_attach['attachment_file'] ?>" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a></td>
                                                                            </tr>
                                                                        <?php $att++;
                                                                        } ?>
                                                                        <tr>
                                                                            <td colspan="4" class="text-left" style="border-bottom: 1px solid #000 !important;">
                                                                                <?
                                                                                $task_flp_qry = "select flp.*,usr.user_name from tbl_followup as flp 
                                               left join users as usr on usr.user_id=flp.user_id
                                               where flp.flp_status=0 and flp.task_id=" . $task_rel['task_id'] . "";
                                                                                $task_flp_qry_rs = $dbcon->query($task_flp_qry);
                                                                                if (mysqli_num_rows($task_flp_qry_rs)) {
                                                                                ?>
                                                                                    <table class="display table table-bordered table-striped">
                                                                                        <thead>
                                                                                            <th>User</th>
                                                                                            <th>Remarks Date</th>
                                                                                            <th>Remarks</th>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <?phpwhile ($flp_rel = mysqli_fetch_assoc($task_flp_qry_rs)) { ?>
                                                                                                <td width="20%"><?= $flp_rel['user_name'] ?></td>
                                                                                                <td width="20%"><?= date("d-M-Y h:i A", strtotime($flp_rel['flp_date'])) ?></td>
                                                                                                <td width="60%"><?= $flp_rel['task_flp_remark'] ?></td>
                                                                                            <?php} ?>
                                                                                        </tbody>
                                                                                    </table>
                                                                                <?php} ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php  } else if ($task_rel['entry_type'] == '2') {
                                                                        //Appointment
                                                                        $appointment_start_time = '';
                                                                        $appointment_end_time = '';
                                                                        if ($task_rel['appointment_start_time'] != "1970-01-01 00:00:00" && $task_rel['appointment_start_time'] != "0000-00-00 00:00:00") {
                                                                            $appointment_start_time = date('d-m-Y h:i A', strtotime($task_rel['appointment_start_time']));
                                                                        }
                                                                        if ($task_rel['appointment_end_time'] != "1970-01-01 00:00:00" && $task_rel['appointment_end_time'] != "0000-00-00 00:00:00") {
                                                                            $appointment_end_time = date('d-m-Y h:i A', strtotime($task_rel['appointment_end_time']));
                                                                        }
                                                                    ?>
                                                                        <tr>
                                                                            <td width="25%" class="text-left">
                                                                                <strong>Appointment</strong>
                                                                            </td>
                                                                            <?
                                                                            $tsk_type = "";
                                                                            $tsk_due_time = strtotime($task_rel['appointment_end_time']);

                                                                            if ($task_rel['task_status'] == '1') {
                                                                                $cur_time = strtotime($task_rel['task_completion_date']);
                                                                                if ($tsk_due_time < $cur_time) {
                                                                                    $tsk_type = "<label style='background:#d9534f;'>(Delayed)</label>";
                                                                                }
                                                                            ?>
                                                                                <td width="25%" class="text-center btn-success">Completed <?= $tsk_type ?></td>
                                                                            <?php} else {
                                                                                $cur_time = strtotime(date('Y-m-d H:i:s'));
                                                                                if ($tsk_due_time < $cur_time) {
                                                                                    $tsk_type = "<label style='background:#d9534f;'>(Delayed)</label>";
                                                                                }
                                                                            ?>
                                                                                <td width="25%" class="text-center btn-warning">Pending <?= $tsk_type ?></td>
                                                                            <?php} ?>
                                                                            <td width="25%" class="text-left">
                                                                                Owner: <?= $task_rel['user_name'] ?>
                                                                            </td>
                                                                            <td width="25%" class="text-left">
                                                                                Create Date: <?= date('d-m-Y h:i A', strtotime($task_rel['create_date'])); ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="25%" class="text-left">
                                                                                Location: <?= $task_rel['task_location'] ?>
                                                                            </td>
                                                                            <td width="25%" class="text-left">
                                                                                Subject: <?= $task_rel['appointment_subject'] ?>
                                                                            </td>
                                                                            <td width="25%" class="text-left">
                                                                                Start Time: <?= $appointment_start_time ?>
                                                                            </td>
                                                                            <td width="25%" class="text-left">
                                                                                End Time: <?= $appointment_end_time ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td colspan="4" class="text-left">
                                                                                Owner Remarks : <?= nl2br($task_rel['task_remark']) ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td colspan="4" class="text-left" style="border-bottom: 1px solid #000 !important;"></td>
                                                                        </tr>
                                                                    <?php} ?>
                                                                <?php} ?>
                                                                <?php} ?>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--tabs end-->
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <?phpif ($mode != 'view') { ?>
                                        <div class="col-md-12 text-center">
                                            <button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
                                            <a href="<?= ROOT . CRM_ROOT . 'inquiry_list' ?>" type="button" class="btn btn-danger">Cancel</a>
                                        </div>
                                    <?php} ?>
                            </div>
                    </div>
                    <!--Vendor row end-->
                    <input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
                    <input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
                    <input type='hidden' name='eid' id='eid' value='<?= $inquiry_id ?>' />
                    <input type='hidden' name='old_product_id' id='old_product_id' value='' />
                    <input type='hidden' name='pro_type' id='pro_type' value='<?= $crm_pro_type ?>' />
                    <input type='hidden' name='pro_search' id='pro_search' value='<?= $crm_pro_search ?>' />
                    <input type='hidden' name='inq_product_required' id='inq_product_required' value='<?= $companyConfiguration['inq_product_required'] ?>' />
                    <input type='hidden' name='quotation_rate_fixed' id='quotation_rate_fixed' value='<?= $companyConfiguration['quotation_rate_fixed'] ?>' />
                    <input type='hidden' name='aeon_permission' id='aeon_permission' value='<?= $getspecialConfiguration['aeon_permission'] ?>' />
                    </form>
                </div>
            </section>
            </div>
            </div>
            <!--state overview end-->
        </section>
    </section>

    <div class="modal fade in" id="direct-inquiry-wrong-data-modal" role="dialog" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3>Inquiry Wrong Import Data </h3>
                </div>
                <div class="modal-body form">
                    <div class="row">
                        <form class="form-horizontal" role="form" id="import-wrong-data" action="javascript:;" method="post" name="dispatch">
                            <span class="tools pull-right"> 
								<a href="javascript:;" onClick="tableToExcel('direct-import-inquiry-data', 'Inquiry Wrong Import Data')" ><button class="btn btn-primary btn-flat" style="margin:10px;" >Export Excel</button></a>	
							</span>
                            <div class="col-md-12">
                                <div class="direct-import-inquiry-data" id="direct-import-inquiry-data"> </div>
                            </div>
                            <input type='hidden' name='mode' id='import-wrong' value='Add' />
                        </form>
                        <!--</div>-->
                    </div>	
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>


    <!--main content end-->
    <!--footer start-->
    <div id="task_model_popup"></div>
    <?php include_once('../include/add_cust.php'); ?>

    <?php include_once('../include/add_person.php'); ?>
    <?php include_once('../../administration/include/add_product.php'); ?>
    <?php include_once('../../administration/include/add_hsn_in_popup.php'); ?>
    <?php include_once('../include/preview_cust_person_dtl.php'); ?>
    <?php include_once('../include/preview_cust_dtls.php'); ?>
    <?php include_once('../include/preview_product_history.php'); ?>
    <?php include_once('../include/add_accessories_product.php'); ?>
    <?php include_once('../include/add_accessories_product_list.php'); ?>
    <?php include_once('../include/add_project_wise_item.php'); ?>

    <?php include_once('../include/add_accessories_wise_item.php'); ?>

    <?php include_once('../../include/footer.php'); ?>


    <!--footer end-->
    </section>
    <!-- js placed at the end of the document so the pages load faster -->
    <?php include_once('../../include/include_js_file.php'); ?>
    <script>
        var formSubmitting = false;
        var setFormSubmitting = function() {
            formSubmitting = true;
        };

        var tableToExcel = (function() {
			var uri = 'data:application/vnd.ms-excel;base64,'
			, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
			, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
			, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
			return function(table, name) {
				if (!table.nodeType) table = document.getElementById(table)
					var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
				window.location.href = uri + base64(format(template, ctx))
			}
		})()



        //window.onload = function() {
        //    window.addEventListener("beforeunload", function (e) {
        //        if (formSubmitting) {
        //            return undefined;
        //        }
        //
        //        var confirmationMessage = 'You sure you want to leave? ';
        //
        //        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        //        //return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
        //    });
        //};
    </script>
    <!-- <script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.15.0/lodash.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2.min.js"></script> -->

    <script src="<?= ROOT . CRM_ROOT ?>js/app/inquiry.js?<?= time() ?>"></script>
    <script src="<?= ROOT . CRM_ROOT ?>js/app/customer.js?<?= time() ?>"></script>
    <script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/product_mst.js?<?php echo time(); ?>"></script>
    <script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/hsn_master.js?<?php echo time(); ?>"></script>
    <script>
        $(".select2").select2({
            width: '100%'
        });
        var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today

        <?php if ($allow_prev_inquiry_date == '1') { ?>
            $('.default-date-picker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
            });
        <?php} else {  ?>
            $('.default-date-picker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                startDate: today,
            });
        <?php}  ?>

        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('product_desc', {
                enterMode: CKEDITOR.ENTER_BR
            });
            CKEDITOR.replace('product_spec', {
                enterMode: CKEDITOR.ENTER_BR
            });
        } else {
            console.error('CKEditor is not loaded');
        }

        <?phpif ($mode == 'Add') { ?>
            $('#task_type_id').select2('readonly', true);
        <?php} ?>
        /*$(".form_datetime-meridian").datetimepicker({
            format: "dd-mm-yyyy HH:ii P",
            showMeridian: true,
            autoclose: true,
            todayBtn: true,
            pickerPosition: "bottom-left"
        });*/
        var max_followup_date = '<?= $max_followup_date ?>';
        var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
        var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(max_followup_date)); //end date should not greater than 15 days
        $(".form_datetime-meridian").datetimepicker({
            format: "dd-mm-yyyy HH:ii P",
            showMeridian: true,
            autoclose: true,
            todayBtn: true,
            pickerPosition: "bottom-left",
            // startDate: today,
            endDate: endDate
        });
        /*disabledDates: [
             moment("12/25/2013"),
             new Date(2013, 11 - 1, 21),
             "11/22/2013 00:53"
             ] */
        /*$(function() { 
            $('#inquiry_date').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
            <?phpif ($mode == 'Add') { ?>
            ,startDate: 'd'//don't allow today and past dates
            <?php} ?>
            });
        });*/
        $(function() {
            setTimeout(function() {
                $('#sidebar > ul').hide();
            }, 1000);
            $('#party_type_ven_div').hide();
        });
    </script>
</body>

</html>