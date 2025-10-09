<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$countryid='101';
	$stateid='1';
	$cityid='1';
	$end = date("d-m-Y");
	$type=isset($_GET['type'])?$_GET['type']:'';
	$form="Employee ".$type;
	//echo $type;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>EMPLOYEE LIST</title>
<?php include_once('../include/include_css_file.php');?>

</head>
<body onload="generate_daily_log_report()">
<section id="container" class="sidebar-closed">
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
						<h3><?=$form?> List</h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<!--<li><a href="<?=ROOT.'employee_list'?>"><?=$form?> list </a></li>-->
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
					<?php if($type=='') { ?><header class="panel-heading"> 
						<span class="tools pull-right"> 
							<a href="<?=ROOT.'employee_add'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
						</span> 
					</header>	
					<?php } ?>
					
					<div class="col-md-12">
							
							<div class="form-group" style="margin-top:20px;">
								  <label class="control-label col-md-2" ><span class="english">Choose Date</span></label>
								  <div class="col-md-3">
									<div class="input-group date form_datetime-component">
										<?php 
										//  $start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
											$start = date('01-m-Y')
										?>
											<input type="hidden" id="from_date"  value="<?=date('d-m-Y')?>">
											<input type="hidden" id="to_date"  value="<?=date('d-m-Y')?>">
											<input type="text" id="rep_date"  onChange="generate_daily_log_report();" class="form-control default-date-picker" value="<?=date('d-m-Y')?>">
											<span class="input-group-btn">
												<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
										</div>
								   </div>
								   
							</div>
					</div>
					
					<div class="clear"></div>
					
					<div class="panel-body">
				  <div class="adv-table">
				  <table class="display table table-bordered table-striped" id="dynamic-table">
					  <thead>
						  <tr>
							  <th>#</th>
							  <th>Name</th>				  
							  <th>Type</th>				  
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
		<input type="hidden" name="type" id="type" value="<?=$type?>">	
		<!--state overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php  
	include_once('../include/footer.php');
?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/employee_mst.js?<?=time()?>"></script>

<!--<script src="js/count.js"></script>-->
<script>
$(".select2").select2({
	width: '100%'
});	

$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});

$('.date-set').click(function(){
       $('.default-date-picker').trigger('click')
});


</script>


</body>
</html>
