<?php
session_start();
$path = '../../';
$include = '../../include/';
$incPath = $path . 'include/common_functions/';

include_once($path . 'config/config.php');
include_once($path . 'config/session.php');
include_once($incPath . 'common_functions.php');
include_once($incPath . 'function_database_query.php');

$token = md5(rand(1000, 9999));
$_SESSION['token'] = $token;
$form = "Support";
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = $infopage['filename'];

//Amish Soni 05-01-2021
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    SUPPORT_SLUG_CREATE
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>SUPPORT LIST</title>
    <?php include_once($include.'include_css_file.php'); ?>
    <style>
        .datepicker td.disabled.day {
            color: #ccc;
        }
    </style>
</head>
<body>
<section id="container">
    <?php include_once($include . 'include_top_menu.php'); ?>
    <!--sidebar start-->
    <?php include_once($include . 'left_menu.php'); ?>
    <!--sidebar end-->
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <!--breadcrumbs start -->
                    <section class="panel">
                        <header class="panel-heading">
                            <h3><?= $mode . ' ' . $form ?> List</h3>
                        </header>
                        <div class="">
                            <ul class="breadcrumb">
                                <li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
                                <li class="active"><?= $form ?> list</li>
                            </ul>
                        </div>
                    </section>
                    <!--breadcrumbs end -->
                </div>
            </div>
            <!--state overview start-->
            <div class="row">
                <div class="col-sm-12">
                    <section class="panel">
                        <header class="panel-heading">
                            <div class='col-lg-5 col-md-7 col-xs-9'>
                                <?php /* if ($_SESSION['user_type'] == '2') { ?>
                                    <div class="form-group">
                                        <label class="control-label col-md-4">Select Employee</label>
                                        <div class="col-md-7">
                                            <select class="select2" name="user_id" id="user_id"
                                                    onChange="load_support_list(this.value);">
                                                <option value="">Please Select</option>
                                                <?= getAllEmployeeUser($dbcon); ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php } */ ?>
                            </div>
                            <?php //Amish Soni 06-01-2021
                            if (in_array(SUPPORT_SLUG_CREATE, $bulkAccessArray)) { ?>
                                <span class="tools pull-right">
                                    <a href="<?= ROOT . SUPPORT_ROOT . 'support_add' ?>"><button
                                                class="btn btn-success btn-flat">Create <?= $form ?></button></a>
                                </span>
                            <?php } ?>
                        </header>
                        <div class="panel-body">
                            <div class="adv-table">
                                <table class="display table table-bordered table-striped" id="dynamic-table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Company ID</th>
                                        <th>Company Name</th>
                                        <th>Department</th>
                                        <th>Created Date</th>
                                        <th>Due Date</th>
                                        <th>Support Status</th>
                                        <?php /* if ($_SESSION['user_type'] == '2') { ?>
                                            <th>User</th>
                                        <?php } */ ?>
                                        <th class="hidden-phone">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <!--state overview end-->
        </section>
    </section>
    <!--main content end-->
    <!--footer start-->
    <?php include_once($include . 'footer.php'); ?>
    <!--footer end-->
</section>
<!-- Modal -->
<div class="modal colored-header info" id="modalChangeStatus" role="dialog" data-keyboard="false"
     data-backdrop="static">
    <div class="modal-dialog custom-width">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 style="margin-top:-6px; important!"><?= $form ?> Change Status</h3>
            </div>
            <div class="modal-body form">
                <form id="FormEditField" role="form" method="post" novalidate>
                    <div class="form-group">
                        <label>Status *</label>
                        <select class="form-control" id="support_status_id" name="support_status_id"
                                onchange="setFields(this.value)">
                            <?php echo getSupportDetail($dbcon); ?>
                        </select>
                    </div>
                    <div class="pendingCls">
                        <div class="form-group">
                            <label>Due Date *</label>
                            <input type="text" class="form-control default-date-picker" autocomplete="off" id="due_date"
                                   name="due_date"/>
                        </div>
                        <div class="form-group">
                            <label>Select Employee *</label>
                            <select class="select2" name="emp_id" id="emp_id">
                                <option value="">Please Select</option>
                                <?= getAllEmployeeUser($dbcon); ?>
                            </select>
                        </div>
                    </div>
                    <div class="approveCls">
                        <div class="form-group">
                            <label>Approver Name *</label>
                            <input type="text" class="form-control" id="change_user" name="change_user"/>
                        </div>

                        <div class="form-group">
                            <label>Approver Comment *</label>
                            <textarea class="form-control" id="change_comment" name="change_comment"></textarea>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="edit_id" id="edit_id" value=""/>
                <input type="hidden" name="ses_user_id" id="ses_user_id" value="<?php echo $_SESSION['user_id']; ?>"/>
                <input type="hidden" name="company_id" id="company_id" value="<?php echo $_SESSION['company_id']; ?>"/>
                <input type="hidden" name="user_type" id="user_type" value="<?php echo $_SESSION['user_type']; ?>"/>
                <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                <button class="btn btn-info btn-flat" type="submit">Update</button>
            </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include . 'include_js_file.php'); ?>
<script src="<?= ROOT ?><?= SUPPORT_ROOT ?>js/app/support.js"></script>
<!--<script src="js/count.js"></script>-->
<script>
    $(".select2").select2({
        width: '100%'
    });

    $('.default-date-picker').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true
    });
</script>
</body>
</html>