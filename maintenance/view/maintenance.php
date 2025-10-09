<?php 
session_start();
include('../include/urlfile.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    MAINTENANCE_ADD_SLUG_CREATE,
    MAINTENANCE_ADD_SLUG_UPDATE,
    MAINTENANCE_ADD_SLUG_READ
]);

$infopage = pathinfo( __FILE__ );
$_SESSION['page'] = 'maintenance/'.$infopage['filename'];
$form = "Maintenance";

if(strpos($_SERVER[REQUEST_URI], "maintenance_edit") == true) {
    if(!in_array(MAINTENANCE_ADD_SLUG_UPDATE,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    $mode = "Edit";
    $maintenance_id = $dbcon->real_escape_string($_REQUEST['id']);
    $query = "SELECT * FROM tbl_maintenance WHERE maintenance_id = $maintenance_id";
    $rel = mysqli_fetch_assoc($dbcon->query($query));

    $maintenance_date = date("d-m-Y", strtotime($rel['maintenance_date']));
    $bill_date = date("d-m-Y", strtotime($rel['bill_date']));

    $product_name = get_product_name($dbcon,$rel['product_id']);

}
else {
    if(!in_array(MAINTENANCE_ADD_SLUG_CREATE,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    $mode = "Add";
    $maintenance_date = date("d-m-Y");
    $bill_date = date("d-m-Y");
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
    <title>MAINTENANCE</title>
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
                                    <li><a href="<?=ROOT.MAINTENANCE_ROOT.'maintenance_list'?>"><?=$form?> List</a></li>
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
                                <form class="form-horizontal" role="form" id="maintenance_add" action="javascript:;" method="post" name="maintenance_add">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Maintenance No*</label>
                                                <div class="col-md-8">
                                                    <input id="maintenance_no" name="maintenance_no" type="text" class="form-control" title="Enter Maintenance No" value="<?=$rel['maintenance_no']?>" placeholder="Maintenance No" readonly>        
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Maintenance Date*</label>
                                                <div class="col-md-8"> 
                                                    <input id="maintenance_date" name="maintenance_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$maintenance_date?>" placeholder="Maintenance Date">
                                                </div>
                                            </div>
                                        </div>
                                        <?php if($companyConfiguration['branch_wise_manage']==1){?>
                                            <div class="col-md-4">
                                                <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'','4','8'); ?>
                                            </div>
                                        <?php} ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Company*</label>
                                                <div class="col-md-8"> 
                                                    <select class="select2" id="cust_id" name="cust_id" <?phpecho ($mode=="view")?"disabled":""?>>
                                                        <?= getcust($dbcon,$rel['cust_id']) ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Product*</label>
                                                <div class="col-md-8"> 
                                                    <input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onchange="load_product_dtls(this.value)" />
                                                    <input type="hidden" id="pro_id" value="<?=$rel['product_id']?>">
                                                    <input type="hidden" id="product_name" value="<?=$product_name?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="product_category" class="col-md-4 control-label">Product Category</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="select2" name="product_category" id="product_category">
                                                        <?= get_all_category($dbcon, $rel['product_category']); ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="product_icode" class="col-md-4 control-label">Product Item Code</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="product_icode" name="product_icode" class="form-control" value="<?=$rel['product_icode']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="drawing_no" class="col-md-4 control-label">Product Drg No.</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="drawing_no" name="drawing_no" class="form-control" value="<?=$rel['drawing_no']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="ranges" class="col-md-4 control-label">Range</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="ranges" name="ranges" class="form-control" value="<?=$rel['ranges']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="make" class="col-md-4 control-label">Make</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="make" name="make" class="form-control" value="<?=$rel['make']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="accuracy" class="col-md-4 control-label">Accuracy</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="accuracy" name="accuracy" class="form-control" value="<?=$rel['accuracy']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="modal" class="col-md-4 control-label">Modal</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="modal" name="modal" class="form-control" value="<?=$rel['modal']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="use_for" class="col-md-4 control-label">Use For</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="use_for" name="use_for" class="form-control" value="<?=$rel['use_for']?>">
                                                </div>
                                            </div>
                                        </div>
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
                                                <label class="col-md-4 control-label">Price</label>
                                                <div class="col-md-8">
                                                    <input type="number" min="0" id="price" name="price" class="form-control" value="<?=$rel['price']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Calibration Period</label>
                                                <div class="col-md-8">
                                                    <input type="number" min="0" id="calibration_period" name="calibration_period" class="form-control" value="<?=$rel['calibration_period']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Remind Before</label>
                                                <div class="col-md-8">
                                                    <input type="number" min="0" id="remind_before" name="remind_before" class="form-control" value="<?=$rel['remind_before']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Calibration Req.</label>
                                                <div class="col-md-8">
                                                    <select class="select2" id="calibration_req" name="calibration_req">
                                                        <option value="1" <?=(($rel['calibration_req']==1) ? "selected" : "selected")?>>Yes</option>
                                                        <option value="0" <?=(($rel['calibration_req']==0) ? "selected" : "")?>>No</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Status</label>
                                                <div class="col-md-8">
                                                    <select class="select2" id="use_status" name="use_status">
                                                        <option value="1" <?=(($rel['use_status']==1) ? "selected" : "selected")?>>In Use</option>
                                                        <option value="0" <?=(($rel['use_status']==0) ? "selected" : "")?>>No Use</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Location</label>
                                                <div class="col-md-8">
                                                    <input type="text" id="location" name="location" class="form-control" value="<?=$rel['location']?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Remark</label>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" id="remark" name="remark" rows="4"><?=$rel['remark']?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="clearfix"></div>
                                        <div class="col-md-12 text-center">
                                            <button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
                                            <a href="<?=ROOT.MAINTENANCE_ROOT.'maintenance_list'?>" type="button" class="btn btn-danger">Cancel</a>    
                                        </div>
                                    </div>
                                </div>
                                <!--Vendor row end--> 
                                <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                                <input type='hidden' name='eid' id='eid' value='<?=$maintenance_id?>' />
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

<script src="<?=ROOT.MAINTENANCE_ROOT?>js/app/maintenance.js?<?=time()?>"></script>
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
    <?php}?>
</script>
</body>
</html>