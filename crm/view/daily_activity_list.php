<?php 
	session_start();
	include('../include/urlfile.php');
	$path = '../../';
	$form="Daily Activity Log";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']='crm/'.$infopage['filename'];
	$countryid='101';
	$stateid='1';
	$cityid='1';
	$end = date("d-m-Y");
	$branch_id = $_SESSION['branch_id'];
	$user_id = "";
	//check permission for terms and condition
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	CUSTOMER_DAILY_UPDATE_SLUG_READ,
        CUSTOMER_DAILY_UPDATE_SLUG_CREATE
    ]);

    if(!in_array(CUSTOMER_DAILY_UPDATE_SLUG_READ,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once($include.'include_css_file.php');?>
<style>

@media (min-width: 1200px){
#custom_sold_modal {
    width: 1150px;
}
}
</style>
</head>
<body>
<section id="container" class="sidebar-closed">
<?php include_once($include.'include_top_menu.php');?>
<!--sidebar start-->
<?php include_once($include.'left_menu.php');?>
<!--sidebar end-->
<!--main content start-->

<section id="main-content">
	
	<section class="wrapper">
		
		<div class="row">
			<div class="col-lg-12">
				<!--breadcrumbs start -->
				<section class="panel">
					<header class="panel-heading">
						<h3><?=$form?> List</h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<li class="active"><?=$form?> list</li>
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
						<div class="col-md-12" style="height:20px;" ></div>
                        <div class="col-md-12">
                            <div class="col-md-4">
                                <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_daily_activity_datatable()','4','6'); ?>
                            </div>
                            <div class="col-md-4">
                            	<?php if($_SESSION['user_type'] == '2'): ?>
	                                <div class="form-group">
	                                    <label class="col-md-4 control-label" style="text-align: right;">User :</label>
	                                    <div class="col-md-6"> 
	                                        <select class="select2" id="user_id" name="user_id" onChange="load_daily_activity_datatable();">
	                                            <?= get_assign_users_inq($dbcon,$user_id); ?>
	                                        </select>
	                                    </div>
	                                </div>
	                            <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                            	<span class="tools pull-right">
									<?php if(in_array(CUSTOMER_DAILY_UPDATE_SLUG_CREATE,$bulkAccessArray)){ ?> 
										<a href="<?=ROOT.CRM_ROOT.'daily_activity_add'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
									<?php } ?>
								</span>
                            </div>
                        </div> 
						 
					</header>	
					<div class="panel-body">
						<div class="adv-table" id="adv-table">
							<table  class="display table table-bordered table-striped" id="dynamic-table">
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Username</th>
										<th>Date</th>
										<th>Description</th>
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
		<input type="hidden" name="custno" id="custno" value="<?=$end?>">	
		<!--state overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php 

	include_once($include.'footer.php');
?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.CRM_ROOT?>js/app/daily_activity.js?<?=time()?>"></script>
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
