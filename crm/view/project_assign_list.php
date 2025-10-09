<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    CRM_PROJECT_ASSIGN_SLUG_LIST,
    CRM_PROJECT_ASSIGN_SLUG_CREATE,
    CRM_PROJECT_ASSIGN_SLUG_VIEW,
    CRM_PROJECT_ASSIGN_SLUG_UPDATE,
    CRM_PROJECT_ASSIGN_SLUG_DELETE
]);

if(!in_array(CRM_PROJECT_ASSIGN_SLUG_VIEW,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$branch_id = $_SESSION['branch_id'];
$form="Project Wise Item Assign";
$branch_id = $_SESSION['branch_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PROJECT WISE ASSIGN LIST</title>
    <?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
    <section id="container" >
        <?php include_once($incPath.'include_top_menu.php');?>
        <?php include_once($incPath.'left_menu.php');?>
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <section class="panel">
                            <header class="panel-heading">
                                <h3><?=$form?> List</h3>
                            </header>
                            <div class="">
                                <ul class="breadcrumb">
                                    <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                    <li class="active"><?=$form?> List</li>
                                </ul>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="col-sm-12">
                    <section class="panel">
                        <header class="panel-heading respadlr0">
                            <div class='col-lg-5 col-md-7 col-xs-12'>
                                <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'reload_data()','4','6'); ?>
                            </div>
                            <span class="tools pull-right respadr_15">
                                <?php if(in_array(CRM_PROJECT_ASSIGN_SLUG_CREATE,$bulkAccessArray)){ ?>
                                    <a href="<?=ROOT.CRM_ROOT.'project_assign'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>					
                                <?php } ?>
                            </span>
                            <div class="col-md-12"	style="height:10px;" ></div>
                        </header>
                        <div class="panel-body">
                            <div class="adv-table">
                                <table  class="display table table-bordered table-striped" id="dynamic-table">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Project Name</th>
                                            <th>Project Code</th>
                                            <th>Branch Name</th>
                                            <?php if(in_array(CRM_PROJECT_ASSIGN_SLUG_UPDATE,$bulkAccessArray) && in_array(CRM_PROJECT_ASSIGN_SLUG_DELETE,$bulkAccessArray)){ ?>                                           
                                                <th class="hidden-phone">Action</th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        </section>
        <div class="modal colored-header info" id="ModalViewProject" role="dialog" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
                        <h3><?=$form?></h3>
                    </div>
                    <div class="modal-body form">
                        <div class="row">
                            <div class="col-md-4">
                                <h4>Project Name: <span id="project_name"></span></h4>
                            </div>
                            <div class="col-md-4">
                                <h4>Project Code: <span id="project_code"></span></h4>
                            </div>
                            <div class="col-md-4">
                                <h4>Project Unit: <span id="project_unit"></span></h4>
                            </div>
                            <div class="col-md-12" id="show_product"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <?php include_once($incPath.'footer.php');?>
    </section>
    <?php include_once($incPath.'include_js_file.php');?>   
    <script src="<?= ROOT.CRM_ROOT?>js/app/project_assign.js?<?=time()?>"></script>
    <script>
        $(".select2").select2({
            width: '100%'
        });
    </script>
</body>
</html>