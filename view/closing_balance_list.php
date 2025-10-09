<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename']; 

$to_date = date("d-m-Y");
if(!empty($_SESSION['start'])){
    $to_date = $_SESSION['to_date'];
}
//Ankit Sompura 09-01-2021
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    FINANCE_CLOSING_BALANCE_LIST,
    FINANCE_CLOSING_BALANCE_CREATE
]);
if(!in_array(FINANCE_CLOSING_BALANCE_LIST,$bulkAccessArray)){
   header("Location: ".DOMAIN."permission_access");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('../include/include_css_file.php');?>
</head>
    <body>
        <section id="container" >
        <?php include_once('../include/include_top_menu.php');?>
        <!--sidebar start-->
        <?php include_once('../include/left_menu.php');?>
        <!--sidebar end-->
        <!--main content start-->
            <section id="main-content">
                <section class="wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <!--breadcrumbs start -->
                            <section class="panel">
                                <header class="panel-heading">
                                    <h3>Closing Balance List </h3>
                                </header>	
                                <div class="">
                                    <ul class="breadcrumb">
                                        <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                        <li class="active">Closing Balance List</li>
                                    </ul>
                                </div>
                            </section>
                            <!--breadcrumbs end -->
                        </div>	
                    </div>
                    <!--unit overview start-->
                    <div class="row">
			<div class="col-sm-12">
                            <section class="panel">
                                <div class="panel-body">
                                    <form role="form" id="add_closing_balance" action="javascript:;" method="post" name="add_closing_balance">
                                        <div class="col-md-12"  style="margin-top:10px;">
                                            <div class='col-lg-5 col-md-7 col-xs-12 '>
                                                <div class="form-group">
                                                    <label class="control-label col-lg-4 col-md-4 col-xs-4 respad-l0">Choose Date</label>
                                                    <div class=" col-lg-8 col-md-8 col-xs-8 respad-r0">
                                                        <div class="input-group date form_datetime-component">
                                                            <input type="text" id="to_date" name="to_date" onChange="reload_data();" class="form-control default-date-picker" value="<?= $to_date ?>">
                                                            <span class="input-group-btn">
                                                                <button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                    
                                        
                                        <table  class="display table table-bordered table-striped" id="dynamic-table">
                                            <thead>
                                                <tr>
                                                    <th>Ledger Name</th>
                                                    <th>Opening Balance</th>
                                                    <th>Closing Balance</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                        <?php if(in_array(FINANCE_CLOSING_BALANCE_CREATE,$bulkAccessArray)){ ?>
                                            <button type="submit" class="btn btn-info">Save</button>
                                        <?php } ?>
                                        <input type="hidden" name="mode" id="mode" value="add_closing_balance" />
                                    </form>
                                </div>
                            </section>
			</div>
                    </div>
                </section>
            </section>
        <!--main content end-->
        <!--footer start-->
        <?php include_once('../include/footer.php');?>
        <!--footer end-->
        </section>

        <!-- Modal -->
        <div class="modal colored-header info" id="closing_balance_edit_modal" role="dialog" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog custom-width">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3>Edit Closing Balance</h3>
                    </div>
                    <div class="modal-body form" id="closing_balance_edit_form">
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
        <!-- js placed at the end of the document so the pages load faster -->
        <?php include_once('../include/include_js_file.php');?>   
        <script src="<?=ROOT?>js/app/closing_balance_mst.js?<?=time()?>"></script>
        <script>
            $('.default-date-picker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            });
            
            $(".select2").select2({
                width: '100%'
            });
        </script>
    </body>
</html>
