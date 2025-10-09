<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$form="Inquiry";
$infopage = pathinfo( __FILE__ );
    //echo '<pre>';print_r($_SESSION);exit;

    $_SESSION['page']= 'crm/'.$infopage['filename'];
    $branch_id = $_SESSION['branch_id'];
    if(isset($_SESSION['summary_start_date']) && !empty($_SESSION['summary_start_date']) && isset($_SESSION['summary_end_date']) && !empty($_SESSION['summary_end_date'])){
        $start_date = $_SESSION['summary_start_date'];
        $end_date = $_SESSION['summary_end_date'];
    } else if(isset($_SESSION['start_date']) && !empty($_SESSION['start_date']) && isset($_SESSION['end_date']) && !empty($_SESSION['end_date'])){
        $start_date = $_SESSION['start_date'];
        $end_date = $_SESSION['end_date'];
    }else {
        $start_date = date("01-m-Y");
        $end_date = date("t-m-Y");
    }

    //check paermission for inquiry add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        INQUIRY_SLUG_CREATE
    ]);

    $stage_id = (isset($_REQUEST['stage_id']) && !empty($_REQUEST['stage_id'])) ? $_REQUEST['stage_id'] : '';
    $assign_user_ids = (isset($_REQUEST['assign_user_id']) && !empty($_REQUEST['assign_user_id'])) ? $_REQUEST['assign_user_id'] : '';
    $sales_stage_id = (isset($_REQUEST['sales_stage_id']) && !empty($_REQUEST['sales_stage_id'])) ? $_REQUEST['sales_stage_id'] : '';
    $stateid = (isset($_REQUEST['stateid']) && !empty($_REQUEST['stateid'])) ? $_REQUEST['stateid'] : '';
    $source_id = (isset($_REQUEST['source_id']) && !empty($_REQUEST['source_id'])) ? $_REQUEST['source_id'] : '';

    $cityid = (isset($_REQUEST['cityid']) && !empty($_REQUEST['cityid'])) ? $_REQUEST['cityid'] : '';

    $request_date_flag = false;
    
    // Objection filter from CRM Dashboard
    $_SESSION['objection_month'] = "";
    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_flt_objection") == true) {
        if(isset($_REQUEST['month']) && !empty($_REQUEST['month'])) {
            $objection_month = $dbcon->real_escape_string($_REQUEST['month']);
        } else {
            $objection_month = "";
        }
        $_SESSION['objection_month'] = $objection_month;
        $assign_user_ids = (isset($_REQUEST['assign_user_id']) && !empty($_REQUEST['assign_user_id'])) ? $_REQUEST['assign_user_id'] : '';

        $request_date_flag = true;
    }    

    $product_id = "";
    $category_id = "";

    // inquiry list filter from CRM Dashboard
    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_flt_inq") == true) {
        $stage_id = $dbcon->real_escape_string($_REQUEST['opp_id']);
        $category_id = $dbcon->real_escape_string($_REQUEST['category_id']);
        $assign_user_ids = (isset($_REQUEST['assign_user_id']) && !empty($_REQUEST['assign_user_id'])) ? $_REQUEST['assign_user_id'] : '';
        $request_date_flag = true;
    }

    // source filter from CRM Dashboard
    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_flt_source") == true) {
        $source_id = $dbcon->real_escape_string($_REQUEST['source_id']);   
        $request_date_flag = true;
    }

    // funnel stage filter from CRM Dashboard
    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_flt_funnel") == true) {
        $stage_id = $dbcon->real_escape_string($_REQUEST['stage_id']);        
    }

    // state filter from CRM Dashboard
    $country_id = '';
    $stateid = '';
    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_flt_state") == true) {
        $stateid = $dbcon->real_escape_string($_REQUEST['stateid']);
        $assign_user_ids = $dbcon->real_escape_string($_REQUEST['assign_user_id']);
        $country_id = 101;
        $request_date_flag = true;
    }

    // city filter from CRM Dashboard
    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_flt_city") == true) {
        $stateid = $dbcon->real_escape_string($_REQUEST['stateid']);
        $cityid = $dbcon->real_escape_string($_REQUEST['cityid']);
        $assign_user_ids = $dbcon->real_escape_string($_REQUEST['assign_user_id']);
        $country_id = 101;
        $request_date_flag = true;
    } else {
        
    }

    $show_owner = TRUE; //please chenge in inquiry index - fetch mode also
    $user_id = (isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id'])) ? $_REQUEST['user_id'] : '';
    
    // user stage filter from CRM Dashboard
    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_usr_stage") == true ) {
        $assign_user_ids = (isset($_REQUEST['assign_user_id']) && !empty($_REQUEST['assign_user_id'])) ? $_REQUEST['assign_user_id'] : '';
    }

    // user stage filter from CRM Dashboard
    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_flt_prod") == true) {
        $product_id = $dbcon->real_escape_string($_REQUEST['product_id']);
        $category_id = $dbcon->real_escape_string($_REQUEST['category_id']);
        $assign_user_ids = (isset($_REQUEST['assign_user_id']) && !empty($_REQUEST['assign_user_id'])) ? $_REQUEST['assign_user_id'] : '';
        $request_date_flag = true;
    }

    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_stage_lost") == true) {
        $stage_id = $dbcon->real_escape_string($_REQUEST['stage_id']);
        $request_date_flag = true;
    }

    
    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_sales_stage") == true) {
        $sales_stage_id = (isset($_REQUEST['sales_stage_id']) && !empty($_REQUEST['sales_stage_id'])) ? $_REQUEST['sales_stage_id'] : '';
        $request_date_flag = true;
    }

    $sales_stage_cat_id = "";
    if(strpos($_SERVER['REQUEST_URI'], "inquiry_list_sales_stage_category") == true) {
        $sales_stage_cat_id = (isset($_REQUEST['sales_stage_cat_id']) && !empty($_REQUEST['sales_stage_cat_id'])) ? $_REQUEST['sales_stage_cat_id'] : '';
        $request_date_flag = true;
    }
    
    if($request_date_flag) {
        $start_date = $dbcon->real_escape_string($_REQUEST['start_date']);
        $end_date = $dbcon->real_escape_string($_REQUEST['end_date']);
    }
    
    // $source_id = (isset($_REQUEST['source_id']) && !empty($_REQUEST['source_id'])) ? $_REQUEST['source_id'] : '';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>INQUIRY LIST</title>
        <?php include_once('../../include/include_css_file.php');?>
    </head>
    <body>
        <section id="container" class="sidebar-closed">
            <?php include_once('../../include/include_top_menu.php');?>
            <!--sidebar start-->
            <?php include_once('../../include/left_menu.php');?>
            <!--sidebar end-->
            <!--main content start-->
            <section id="main-content">
                <section class="wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <!--breadcrumbs start -->
                            <section class="panel">
                                <header class="panel-heading">
                                    <h3> <?=$form?> List</h3>
                                </header>
                                <ul class="breadcrumb">
                                    <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="<?=ROOT.CRM_ROOT.'inquiry_list'?>"><?=$form?> List</a></li>
                                </ul>
                            </section>
                            <!--breadcrumbs end -->
                        </div>
                    </div>
                    <div class="row">		
                        <!--state overview start-->
                        <div class="row">			
                            <div class="col-sm-12">
                                <section class="panel">
                                    <header class="panel-heading">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <span class="tools pull-right">
                                                    <a href="javascript:;"><button onClick="exportCsv()" class="btn btn-success btn-flat" >Export Excel</button></a>	
                                                </span>
                                                <?php if(in_array(INQUIRY_SLUG_CREATE,$bulkAccessArray)) { ?>
                                                    <span class="tools pull-right">
                                                        <a href="<?=ROOT.CRM_ROOT.'inquiry_add'?>"><button class="btn btn-success btn-flat">Add <?=$form?></button></a>
                                                    </span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </header>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label col-md-4" style="text-align: right;">Start :</label>
                                                    <div class="col-md-8">
                                                        <input id="start_date" name="start_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$start_date?>" placeholder="Start Date" onchange="load_inquiry_datatable();">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" style="text-align: right;">End :</label>
                                                    <div class="col-md-8"> 
                                                        <input id="end_date" name="end_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$end_date?>" placeholder="End Date" onchange="load_inquiry_datatable();">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label col-md-4" style="text-align: right;">Stage :</label>
                                                    <div class="col-md-8">
                                                        <select class="select2" name="stage_id" id="stage_id" onChange="load_inquiry_datatable();">
                                                            <?= get_inquiry_stage($dbcon,$stage_id); ?>	
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                           <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" style="text-align: right;">Owner User :</label>
                                                    <div class="col-md-8"> 
                                                        <select class="select2" id="user_id" name="user_id" onChange="load_inquiry_datatable();">
                                                            <?php //= get_child_users($dbcon,$user_id);//get_assign_users_inq($dbcon,$user_id); ?>
                                                            <?= get_assign_users_inq($dbcon,$user_id); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" style="text-align: right;">Source :</label>
                                                    <div class="col-md-8"> 
                                                        <select class="select2" id="source_id" name="source_id" onChange="load_inquiry_datatable();">
                                                            <?=get_refer_by($dbcon,$source_id);?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" style="text-align: right;">Sales Stage :</label>
                                                    <div class="col-md-8"> 
                                                        <select class="select2" id="sales_stage_id" name="sales_stage_id" onChange="load_inquiry_datatable();">
                                                            <option value="">Choose Sales Stage</option>
                                                            <?=get_master_category_dtl($dbcon,$sales_stage_id,7,'','');//7:Sales Stage?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" style="text-align: right;">Assign User :</label>
                                                    <div class="col-md-8"> 
                                                        <select class="select2" id="assign_user_id" name="assign_user_id" onChange="load_inquiry_datatable();">
                                                            <?php //= get_child_users($dbcon,$user_id);//get_assign_users_inq($dbcon,$user_id); ?>
                                                            <?= get_assign_users_inq($dbcon,$assign_user_ids); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" style="text-align: right;">Country :</label>
                                                    <div class="col-md-8"> 
                                                        <select class="select2" id="country_id" name="country_id" onChange="load_inquiry_datatable();load_state(this.value,'state_id',<?=$stateid?>);">
                                                            <?=get_country($dbcon,$country_id)?> 
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" style="text-align: right;">State :</label>
                                                    <div class="col-md-8"> 
                                                        <select class="select2" id="state_id" name="state_id" onChange="load_inquiry_datatable();load_city(this.value,'cityid',<?=$cityid?>);">
                                                            <?= $stateid ? getstate($dbcon,$stateid) : ''?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="col-md-4">
                                                <?php echo getBranchBox($dbcon, $branch_id, $_SESSION['branch_id'], false, true,'load_inquiry_datatable()','4','8'); ?>
                                            </div>
                                            <!-- <div class="clearfix"></div> -->
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" style="text-align: right;">City :</label>
                                                    <div class="col-md-8"> 
                                                        <select class="select2" id="cityid" name="cityid" onChange="load_inquiry_datatable();">
                                                            <?= getcity($dbcon,$stateid,$cityid)?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label" style="text-align: right;">Category :</label>
                                                    <div class="col-md-8">
                                                        <select class="form-control" name="category_id" id="category_id" onchange="load_inquiry_datatable();" >
                                                            <?= get_all_category($dbcon,$category_id); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <input type="hidden" name="category_product_id" id="category_product_id" value="<?php echo $product_id; ?>"/>
                                        <input type="hidden" name="sales_stage_cat_id" id="sales_stage_cat_id" value="<?= $sales_stage_cat_id; ?>" >
                                        
                                        <div class="col-md-12"	style="height:20px;" ></div>
                                        
                                        <div class="adv-table">
                                            <table class="display table table-bordered table-striped" id="inquiry-table">
                                                <thead>
                                                    <tr>
                                                        <th>Inquiry Date</th>
                                                        <th>Inquiry No</th>
                                                        <th>Company</th>
                                                        <th>Mobile No</th>
                                                        <th>Customer Type</th>
                                                        <th>Source</th>
                                                        <th>Address</th>
                                                        <th>City / State / Country</th>
                                                        <th>Product</th>
                                                        <th>Stage</th>
                                                        <th>Remark</th>
                                                        <?php if($show_owner){ ?>
                                                            <th>Owner</th>
                                                        <?php } ?>
                                                        <th>Assign User</th>
                                                        <th>Last Updated</th>
                                                        <th>Action</th>	
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
                    </div>
                </section>
            </section>
            <!--main content end-->
            <!--footer start-->
            <div id="task_model_popup"></div>
            <?php include_once('../../include/preview_flp_hist.php');?>
            <?php include_once('../include/preview_acknowledge.php');?>
            <?php include_once('../include/preview_attached_doc.php');?>
            <?php include_once('../include/send_email.php');?>
            <?php include_once('../../include/footer.php');?>
            <!--footer end-->
        </section>
        <!-- js placed at the end of the document so the pages load faster -->
        <?php include_once('../../include/include_js_file.php');?>   
        <script src="<?= ROOT.CRM_ROOT ?>js/app/inquiry.js?<?=time()?>"></script>
        <script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
        <script>
            $(".select2").select2({
               width: '100%'
           });
            $('.default-date-picker').datepicker({
               format: 'dd-mm-yyyy',
               autoclose: true
           });
            function cb(start, end) {
               $('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
           }
           cb(moment().subtract(29, 'days'), moment());

           $(document).ready(function() {
            var city = $("#city_id").val();
            var state = $("#state_id").val();
            // load_state(country,'state_id',state);
            load_city(state,'city_id',city);
        });

           var date = new Date();
var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate()+15);

$(".form_datetime-meridian").datetimepicker({
    format: "dd-mm-yyyy HH:ii P",
    showMeridian: true,
    autoclose: true,
    todayBtn: true,
    pickerPosition: "bottom-left",
    startDate: today,
    endDate: endDate
});

$('.datepikerdemo').daterangepicker({       
   locale: {
      format: 'DD-MM-YYYY'
  },
  "autoApply": true,	
  "startDate": $('#from_date').val(),
  "endDate": $('#to_date').val(),	
  ranges: {
      'Today': [moment(), moment()],
      'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Last 7 Days': [moment().subtract(6, 'days'), moment()],
      'Last 30 Days': [moment().subtract(29, 'days'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
  }
}, cb);
$('.date-set').click(function(){
   $('.datepikerdemo').trigger('click');
});
$(function(){
   setTimeout(function(){ $('#sidebar > ul').hide(); }, 1000);
});

CKEDITOR.replace( 'email_content', {
   enterMode: CKEDITOR.ENTER_BR
});
</script>
</body>
</html>