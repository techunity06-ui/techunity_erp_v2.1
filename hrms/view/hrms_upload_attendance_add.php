<?php 
    session_start();
    include_once("../../config/config.php");
    include_once("../../config/session.php");
    include_once("../../include/common_functions.php");
    include_once("../../include/hrms_common_functions.php");

    $token = md5(rand(1000,9999));
    $_SESSION['token'] = $token;
    $form="Hrms Attendance Tools";

    if(strpos($_SERVER['REQUEST_URI'], "hrms_attendance_edit")==false) {
        $mode="Add";
    }
    else {
        $mode="Edit";
        $hrmsAttendanceId = $dbcon->real_escape_string($_REQUEST['id']);
        $query="select * from hrms_attendance where id = $hrmsAttendanceId and company_id = $companyID and user_id = $userID";
        $rel=mysqli_fetch_assoc($dbcon->query($query));
        if($rel){
        }else{
            header("Location: " . DOMAIN . HRMS_ROOT . "hrms/hrms_attendance_list");
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('../../include/include_css_file.php');?>
    
    <style type="text/css">
        .margin_row {
            margin-top:10px !important;
        }
        .datepicker td.disabled {
            color: #ccc;
        }
    </style>
    <script type="text/javascript" src="<?php echo ROOT . HRMS_ROOT ?>js/jquery.form.min.js"></script>
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
                          <h3>Upload <?=$form?>
                          
                          </h3>
                        </header>   
                        <div class="">
                          <ul class="breadcrumb">
                              <li><a href="<?=ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li class="active"><a href="<?=ROOT . HRMS_ROOT . 'hrms_attendance_list'?>"><?=$form?> List </a></li>
                          </ul>
                        </div>
                    </section>
                  <!--breadcrumbs end -->
                </div>  
             </div>
              <!--Customer overview start-->
            <form role="form" id="download_attendance" action="javascript:;" method="post" name="download_attendance">
                <div class="row">
                    <div class="col-sm-12">
                        <section class="panel">
                            <header class="panel-heading">
                              Download <?=$form?> 
                                <span class="tools pull-right">
                                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                                </span>
                            </header>   
                            <div class="panel-body">
                                <div class="col-md-12" style="padding-top: 25px;">
                                    <div class="col-md-12 margin_row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="from_date" class="col-md-4 control-label">From Date*</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="from_date" name="from_date" class="form-control datepicker" required=""autocomplete="off" />
                                                </div>
                                            </div>                     
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="to_date" class="col-md-4 control-label">To Date*</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="text" id="to_date" name="to_date" class="form-control datepicker" required="" autocomplete="off" />
                                                </div>
                                            </div>                     
                                        </div>
                                    </div>
                                    <div class="col-md-12 margin_row text-center">
                                        <button type="submit" class="btn btn-shadow btn-success" style="box-shadow: 3px 3px #61a642;">Download</button>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </form>

            <form role="form" id="upload_attendance" action="javascript:;" method="post" name="upload_attendance">
                <div class="row">
                    <div class="col-sm-12">
                        <section class="panel">
                            <header class="panel-heading">
                              Upload <?=$form?> 
                                <span class="tools pull-right">
                                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                                </span>
                            </header>   
                            <div class="panel-body">
                                <div class="col-md-12" style="padding-top: 25px;">
                                    <div class="col-md-12 margin_row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="attendance_date" class="col-md-4 control-label">Import csv file*</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <input type="file" id="excel_file" name="excel_file" class="form-control"  accept=".csv" required title="Select File" />
                                                    <div id="msg"></div>
                                                </div>
                                            </div>                     
                                        </div>
                                    </div>
                                    <div class="col-md-12 margin_row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">File Format</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <a href="<?=ROOT.'attendance_sample.csv'?>" target="_blank" class="btn btn-info">Click to View Csv File Format</a>
                                                </div>                                                
                                             </div>                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12"> 
                    <div style="background-color: #fff; padding: 10px 0; text-align: center;">  
                        <input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />                             
                        <input type='hidden' name='eid' id='eid' value='<?php if($mode=='Edit'){ echo $rel['id']; } else { echo "0"; } ?>' />

                        <input type="hidden" name="mode" id="mode" value="check_data" />

                        <button type="submit" class="btn btn-shadow btn-success" style="box-shadow: 3px 3px #61a642;">Submit</button>
                    </div>
                    </div>
                </div>
            </form>
        </section>
    </section>
    <!--main content end-->
    <!--footer start-->
    <?php include_once('../../include/footer.php');?>
    <!--footer end-->
</section>
<!-- Modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<script src="<?= ROOT. HRMS_ROOT ?>js/app/hrms_attendance.js?<?php echo time(); ?>"></script>
<script type="text/javascript">
    $(".datepicker").datepicker({
        format: "dd-mm-yyyy",
        // startDate: "1d",
        autoclose: true,
        todayHighlight: true
    });
    
    $('#from_date').datepicker()
    .on('changeDate', function(e) {
        var start_date = e.format(0,"dd-mm-yyyy");
        var end_date = $('#to_date').val();

        job_start_date = start_date.split('-');
        job_end_date = end_date.split('-');

        var new_start_date = new Date(job_start_date[2],job_start_date[0],job_start_date[1]);
        var new_end_date = new Date(job_end_date[2],job_end_date[0],job_end_date[1]);

        $('#to_date').datepicker('setStartDate', e.format(0,"dd-mm-yyyy"));
        
        if(end_date == '' || new_start_date > new_end_date) {
            $('#to_date').datepicker('setDate', e.format(0,"dd-mm-yyyy"));
        }

    });
</script>
</body>
</html>