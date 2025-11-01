<?php 
	session_start();
	$path = '../';
	$include = '../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once($include."common_functions.php");
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page'] = $infopage['filename']; 
	$form="Store Accept Approval";
	

	if(strpos($_SERVER['REQUEST_URI'], "store_accept_edit")==true) {
		$mode="Edit";
		$machine_confi_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select machineconf.* from tbl_store_accept_configuration as machineconf
		where machineconf.id=$machine_confi_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));

		$queryImage="select machineimage.* from tbl_store_accept_image_upload as machineimage where machineimage.store_accept_id=$machine_confi_id";
		$relImageData = $dbcon->query($queryImage);
		
		if($rel['approve_date']!="1970-01-01" && $rel['approve_date']!="0000-00-00")
		{
			$approve_date=date('d-m-Y',strtotime($rel['approve_date']));
		}
		if($rel['approve_status']=="0"){
			$pen="selected";
		}
		if($rel['approve_status']=="1"){
			$app="selected";
		}
	} else {
		$mode="Add";
		$approve_date=date('d-m-Y');
	}
	
?>
<!DOCTYPE html>
<html lang="en">
		<head>
		<?php include_once($include.'include_css_file.php');?>
		</head>
<body>
<section id="container" >
<?php include_once($include.'include_top_menu.php');?>
<!--sidebar start-->
<?php include_once($include.'left_menu.php');?>
<!--sidebar end-->
<!--main content start-->
<section id="main-content">
<section class="wrapper">
<div class="row">
	<div class="col-md-12">
		<!--breadcrumbs start -->
		<section class="panel">
			<header class="panel-heading">
				<h3>New <?=$form; ?></h3>
			</header>	
			<div class="">
				<ul class="breadcrumb">
					<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
					<li class="active"><a href="<?=ROOT.'store_accept_list'?>"><?=$form?> List</a></li>
				</ul>
			</div>
		</section>
		<!--breadcrumbs end -->
	</div>	
</div>
<!--unit overview start-->
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				New <?=$form?>
			</header>	
			<div class="panel-body">
				<form role="form" id="machine_configuration_add" action="javascript:;" method="post" name="machine_configuration_add" enctype="multipart/form-data">
			        <div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Store Accept Date*</label>
								<div class="col-md-8">
	   								<input id="approve_date" name="approve_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$approve_date?>" placeholder="Accept Date">
   								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Approval Status*</label>
								<div class="col-md-8">
									<select class="select2" name="approve_status" id="approve_status">
										<option value="0" <?=$pen?>>Pending</option>	
										<option value="1" <?=$app?>>Approved</option>	
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Remark</label>
								<div class="col-md-8">
									<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Upload Images</label>
								<div class="col-md-8">
									<input type="file" id="upload_machine_file" name="upload_machine_file[]" multiple />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<?php while($relImg=mysqli_fetch_assoc($relImageData)){	?>
								<?php if($mode=='Edit') { ?>
									<img src="<?php if(isset($relImg['upload_machine_file']) && !empty($relImg['upload_machine_file'])){ echo ROOT .'upload/store_accept/'.$relImg['upload_machine_file']; } else { echo ROOT .'upload/store_accept/no_profile.png'; } ?>" width="50" height="50" />
								<?php } ?>
							<?php } ?>	
						</div>
					</div>
					<input type='hidden' name='eid' id='eid' value='<?=$_REQUEST['id']?>' />	
					<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />	
					
					<div class="clearfix"></div>
					<div class="col-md-12 text-center" style="padding-top: 12px;">
						<button type="submit" id="submit_btn" class="btn btn-success">Submit</button>
						<a href="<?=ROOT.'store_accept_list'?>" type="button" class="btn btn-danger">Cancel</a>	
					</div>
				</form>
				
			</div>
		</section>
	</div>
	
</div>

<!--unit overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once($include.'footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT?>js/app/store_accept_list.js?<?=time()?>"></script>
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
</script>
<?php 
	
?>
</body>
</html>
