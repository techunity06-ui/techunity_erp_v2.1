<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Expense";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	//echo $_SESSION['user_id'];
	//Ankit Sompura 09-01-2021
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FINANCE_EXPENSE_DETAIL_LIST,
		FINANCE_EXPENSE_DETAIL_CREATE
	]);
	if(!in_array(FINANCE_EXPENSE_DETAIL_LIST,$bulkAccessArray)){
       header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container"  class="sidebar-closed" >
<?php include_once('../include/include_top_menu.php');?>
<!--sidebar start-->
<?php include_once('../include/left_menu.php');?>
<!--sidebar end-->
<!--main content start-->
<section id="main-content" >
	<section class="wrapper">
		<div class="row">
			<div class="col-lg-12">
				<!--breadcrumbs start -->
				<section class="panel">
					<header class="panel-heading">
						<h3>New <?=$form?></h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<li class="active"><?=$form?> List</li>
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
					<header class="panel-heading">
						<?=$form?> List
						<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>
						</span>
					</header>
					<header class="panel-heading">
					
						<?php if($_SESSION['attendance']=='yes' || $_SESSION['attendance']=='') { ?>
						<span class="tools pull-right">
							<?php if(in_array(FINANCE_EXPENSE_DETAIL_CREATE,$bulkAccessArray)){ ?>
								<a href="<?=ROOT.'expense-entry'?>" >
									<button class="btn btn-success btn-flat" >Add <?=$form?></button>
								</a>	
							<?php } ?>						
						</span>
						<?php } ?>
				 
					</header>
					<div class="panel-body">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="expense-table">
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Date</th> 
										<th>Company Name</th> 
										<th>Complaint</th> 
										<th>Amount</th> 
										<th>Remark</th> 
										<th>Status</th> 
										<th>Payment Status</th> 
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
		
		<!--unit overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once('../include/add_expense_head.php');?>
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>
<script src="<?=ROOT?>js/app/expense_detail.js?<?=time()?>"></script>
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
