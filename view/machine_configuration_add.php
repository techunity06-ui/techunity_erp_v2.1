<?php 
	session_start();
	$path = '../';
	$include = '../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once($include."common_functions.php");
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page'] = $infopage['filename']; 
	$form="Machine Configuration";
	

	if(strpos($_SERVER[REQUEST_URI], "machine_configuration_edit")==true) {
		$mode="Edit";
		$machine_confi_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select machineconf.* from tbl_machine_configuration as machineconf
		where machineconf.id=$machine_confi_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));

		$queryImage="select machineimage.* from tbl_machine_configuration_image_upload as machineimage where machineimage.machine_conf_id=$machine_confi_id";
		$relImageData = $dbcon->query($queryImage);

		$product_id = $rel['product_id'];
		$process_id = $rel['process_id'];
	} else {
		$mode="Add";
		$product_id = '0';
		$process_id = '0';
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
					<li class="active"><a href="<?=ROOT.'machine_configuration_list'?>"><?=$form?> List</a></li>
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
								<label class="col-md-4 control-label">Product Name*</label>
								<div class="col-md-8">
	   								<select class="select2" id="product_id" name="product_id" onchange="get_related_process(this.value,'process_id','')" placeholder="Choose Products">
	 									<?=getproduct($dbcon,$rel['product_id']);?>
	   								</select>
   								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Process Name*</label>
								<div class="col-md-8">
									<select class="select2" name="process_id" id="process_id">
										<option value="">Select Process</option>	
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Upload Images</label>
								<div class="col-md-8">
									<input type="file" id="upload_machine_file" name="upload_machine_file[]" multiple/>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<?php while($relImg=mysqli_fetch_assoc($relImageData)){	?>
								<?php if($mode=='Edit') { ?>
									<img src="<?php if(isset($relImg['upload_machine_file']) && !empty($relImg['upload_machine_file'])){ echo ROOT .'upload/machine_configuration/'.$relImg['upload_machine_file']; } else { echo ROOT .'upload/machine_configuration/no_profile.png'; } ?>" width="50" height="50" />
								<?php } ?>
							<?php } ?>	
						</div>
					</div>
					<div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Short Count*</label>
								<div class="col-md-8">
									<input class="form-control" type="text" name="short_count" id="short_count" placeholder="Short Count" onkeypress="return isNumberKey(event)" value="<?=($rel['short_count'])?$rel['short_count']:'0'?>" />
								</div>
							</div>
						</div>
					</div>
					
					<input type='hidden' name='eid' id='eid' value='<?=$_REQUEST['id']?>' />	
					<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />	
					
					<div class="clearfix"></div>
					<div class="col-md-12 text-center" style="padding-top: 12px;">
						<button type="submit" id="submit_btn" class="btn btn-success">Submit</button>
						<a href="<?=ROOT.'machine_configuration_list'?>" type="button" class="btn btn-danger">Cancel</a>	
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
<script src="<?=ROOT?>js/app/machine_configuration.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
</script>
<?php 
	if($mode=="Edit"){
		echo "<script>get_related_process(".$product_id.",'process_id',".$process_id.")</script>";
	}else{
		echo "<script>get_related_process(".$product_id.",'process_id',".$process_id.")</script>";
	}
?>
</body>
</html>
