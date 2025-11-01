<?php 
session_start();
include('../include/urlfile.php');

// $bulkAccessArray = canCheckPermissionAccess($dbcon, [
//     INQUIRY_SLUG_CREATE,
//     INQUIRY_SLUG_EDIT,
//     INQUIRY_SLUG_VIEW
// ]);

$infopage = pathinfo( __FILE__ );
$_SESSION['page'] = 'calibration/'.$infopage['filename'];
$form = "Calibration";

if(strpos($_SERVER['REQUEST_URI'], "calibration_edit") == true) {
    // if(!in_array(INQUIRY_SLUG_EDIT,$bulkAccessArray)){
    //     header("Location: ".DOMAIN."permission_access");
    // }
    $mode = "Edit";
    $calibration_id = $dbcon->real_escape_string($_REQUEST['id']);
    $query = "SELECT * FROM tbl_calibration WHERE calibration_id = $calibration_id";
    $rel = mysqli_fetch_assoc($dbcon->query($query));

    $calibration_req_date = date("d-m-Y", strtotime($rel['calibration_req_date']));
    $bill_date = date("d-m-Y", strtotime($rel['bill_date']));
    $due_date = date("d-m-Y", strtotime($rel['due_date']));
    $remind_date = date("d-m-Y", strtotime($rel['remind_date']));
    $tc_date = date("d-m-Y", strtotime($rel['tc_date']));
    $maintenance_id = $rel['maintenance_id'];

    $product_name = get_product_name($dbcon,$rel['product_id']);

}
else {
    // if(!in_array(INQUIRY_SLUG_CREATE,$bulkAccessArray)){
    //     header("Location: ".DOMAIN."permission_access");
    // }
    $mode = "Add";
    $calibration_req_date = date("d-m-Y");
    $bill_date = date("d-m-Y");
    $due_date = date("d-m-Y");
    $remind_date = date("d-m-Y");
    $tc_date = date("d-m-Y");
    $maintenance_id = $dbcon->real_escape_string($_REQUEST['id']);
}

$set = "select * from tbl_company where company_id = ".$_SESSION['company_id'];
$set_head = mysqli_fetch_assoc($dbcon->query($set));

$companySettings = getCompanySettings($dbcon);

$companyConfiguration=getCompanyConfiguration($dbcon);

$getspecialConfiguration=getspecialConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang = "en">
<head>
    <title>CALIBRATION</title>
    <?php include_once('../../include/include_css_file.php');?>
</head>
<body>
    <section id="container" class="sidebar-closed">
        <!--class="side bar-closed"-->
        <?php include_once('../../include/include_top_menu.php');?>
        <!--side bar start-->
        <?php include_once('../../include/left_menu.php');?>
        <!--side bar end-->
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <!--bread crumbs start -->
                        <section class="panel">
                            <header class="panel-heading">
                                <h3><?=$mode .' '.$form?></h3>
                                <!--<div class="text-center">Owner : <strong><?=$user_name?></strong></div>-->
                            </header>
                            <div class="">
                                <ul class="breadcrumb">
                                    <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="<?=ROOT.MAINTENANCE_ROOT.'maintenance_list'?>">Maintenance List</a></li>
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
                                New <?=$form?>
                            </header>
                            <div class="panel-body">
                                <form class="form-horizontal" role="form" id="calibration_add" action="javascript:;" method="post" name="calibration_add">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Calibration No*</label>
                                                <div class="col-md-8">
                                                    <input id="calibration_req_no" name="calibration_req_no" type="text" class="form-control" title="Enter Calibration No" value="<?=$rel['calibration_req_no']?>" placeholder="Calibration No" readonly>        
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Calibration Date*</label>
                                                <div class="col-md-8"> 
                                                    <input id="calibration_req_date" name="calibration_req_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$calibration_req_date?>" placeholder="Calibration Date">
                                                </div>
                                            </div>
                                        </div>
                                        <?php if($companyConfiguration['branch_wise_manage']==1){?>
                                            <div class="col-md-4">
                                                <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'','4','8'); ?>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Bill No*</label>
                                                <div class="col-md-8">
                                                    <input id="bill_no" name="bill_no" type="text" class="form-control" title="Enter Bill No" value="<?=$rel['bill_no']?>" placeholder="Bill No" >        
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Bill Date*</label>
                                                <div class="col-md-8"> 
                                                    <input id="bill_date" name="bill_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$bill_date?>" placeholder="Bill Date">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Amount</label>
                                                <div class="col-md-8">
                                                    <input type="number" min="0" id="amount" name="amount" class="form-control" value="<?=$rel['amount']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Company*</label>
                                                <div class="col-md-8"> 
                                                    <select class="select2" id="cust_id" name="cust_id" <?php echo ($mode=="view")?"disabled":""?>>
                                                        <?= getcust($dbcon,$rel['cust_id']) ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="due_date" class="col-md-4 control-label">Due Date</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="due_date" name="due_date" class="form-control default-date-picker" value="<?=$due_date?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="remind_date" class="col-md-4 control-label">Remind Date</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="remind_date" name="remind_date" class="form-control default-date-picker" value="<?=$remind_date?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="lci_used" class="col-md-4 control-label">LCI Used</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="lci_used" name="lci_used" class="form-control" value="<?=$rel['lci_used']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="acceptance" class="col-md-4 control-label">Acceptance</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="acceptance" name="acceptance" class="form-control" value="<?=$rel['acceptance']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="tc_date" class="col-md-4 control-label">TCDdate</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="tc_date" name="tc_date" class="form-control default-date-picker" value="<?=$tc_date?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="clearfix"></div>
                                        <div class="col-md-12 text-center">
                                            <button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
                                            <a href="<?=ROOT.calibration_ROOT.'calibration_list'?>" type="button" class="btn btn-danger">Cancel</a>    
                                        </div>
                                    </div>
                                </div>
                                <!--Vendor row end--> 
                                <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                                <input type='hidden' name='eid' id='eid' value='<?=$calibration_id?>' />
                                <input type='hidden' name='maintenance_id' id='maintenance_id' value='<?=$maintenance_id?>' />
                            </form>
                        </div>
                    </section>
                </div>
            </div>
            <!--state overview end-->
        </section>
    </section>
    <?php include_once('../../include/footer.php');?>
    <!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<script>
    var formSubmitting = false;
    var setFormSubmitting = function() { formSubmitting = true; };
</script>

<script src="<?=ROOT.MAINTENANCE_ROOT?>js/app/calibration.js?<?=time()?>"></script>
<script>
    $(".select2").select2({
        width: '100%'
    });
    var date = new Date();
    var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
    $('.default-date-picker').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        startDate: today,

    });
<?php if($mode=='Add'){?>
        get_series_no();
    <?php }?>
</script>
</body>
</html>