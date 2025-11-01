<?php 
	session_start();
	include('../include/urlfile.php');	
	$form="Allocate Process";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	if(strpos($_SERVER['REQUEST_URI'], "grn_edit")==false){
		$mode="Add";
		$grn_date=date('d-m-Y'); 
	}
	else{
		$mode="Edit";
		$qc_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_qc where qc_id='$qc_id'";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		
		//echo get_product_process($dbcon,$rel['qc_product']);
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	$process=get_product_process($dbcon,$rel['qc_product']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once($incldue.'include_css_file.php');?>
</head>
<body>
<section id="container" class="sidebar-closed">
<?php include_once($incldue.'include_top_menu.php');?>
<!--sidebar start-->
<?php include_once($incldue.'left_menu.php');?>
<!--sidebar end-->
<!--main content start-->
<section id="main-content">
<section class="wrapper">
<?php //include_once('../include/equick_link.php');?>
<div class="row">
	<div class="col-lg-12">
		<!--breadcrumbs start -->
		<section class="panel">
			<header class="panel-heading">
				<h3><?=$mode.' '.$form?> For <?php echo $process; ?></h3>
			</header>	
			<div class="">
				<ul class="breadcrumb">
					<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
					<li><a href="<?=ROOT.PRODUCTION_ROOT.'cutting_list'?>"><?=$form?> List</a></li>
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
		
			<div class="panel-body">
				<form class="form-horizontal" role="form" id="grn_add" action="javascript:;" method="post" name="grn_add" enctype="multipart/form-data">
					<div class="row"> 
						<div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-4 control-label">Process No.*</label>
									<div class="col-md-6 col-xs-11">
										<input type="text" id="grn_no" name="grn_no" class="form-control" title="GRN No." value="<?=$rel['grn_no']?>" placeholder="GRN No" readonly>
									</div>
								</div>
							</div>	
							<div class="col-md-6">  	
								<div class="form-group">  	
									<label class="col-md-3 control-label">Process Date & Time*</label>
									<div class="col-md-5 col-xs-11">
										<input type="text" id="grn_date" name="grn_date" class="form-control default-date-picker" title="Date" value="<?php echo date("d/m/Y h:i:s A"); ?>" placeholder="Purchase Date" readonly>
									</div>
								</div>	
							</div>	
						</div>	
						
						<div class="col-md-12">
							
							
							 <table  class="display table table-bordered table-striped" id="dynamic-table">
								 
								 
								 
							 </table>
							
							<div class="clearfix"></div>	
						</div>
						
						<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
						<input type='hidden' name='eid' id='eid' value='<?=$rel['qc_id']?>' />
						<div class="clearfix"></div>	
						<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
						<a href="<?=ROOT.PRODUCTION_ROOT.'grn_list'?>" type="button" class="btn btn-danger">Cancel</a>
						<div class="col-md-4"></div>					
					</div>
					<!--Vendor row end-->				  
				</form>
			</div>
			
		</section>
	</div>
</div>		

<!--state overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->

<?php include_once($incldue.'footer.php');?>
<!--footer end-->
</section>
<!-- JS placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>  
<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/allocate_process.js?<?=time()?>"></script>

<script>
//$('#container').addClass('sidebar-closed');
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
$(".form_datetime").datetimepicker({
	format: 'dd-mm-yyyy hh:ii',
	autoclose: true,
	todayBtn: true,
	pickerPosition: "bottom-left"
});  
<?php if($mode=='Edit'){?>
$('#vender_id').select2('readonly',true);
$('#branch_id').select2('readonly',true);
$('#purchaseorder_id').select2('readonly',true);
<?php }?>

</script> 
</body>
</html>