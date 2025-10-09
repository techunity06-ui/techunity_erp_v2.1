<?php 
	session_start();
	include('../include/urlfile.php');

    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        CREATE_SALESMAN_MASTER,
        UPDATE_SALESMAN_MASTER,
    ]);

	$form="Salesman";

	if(strpos($_SERVER['REQUEST_URI'], "salesman_edit")==false) {
        if(!in_array(CREATE_SALESMAN_MASTER,$bulkAccessArray)){
            header("Location: ".DOMAIN."permission_access");
        }
		$mode="Add";

	}
	else {
        if(!in_array(UPDATE_SALESMAN_MASTER,$bulkAccessArray)){
            header("Location: ".DOMAIN."permission_access");
        }

		$mode="Edit";
		$salesman_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_salesman_master where salesman_id=$salesman_id";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

        if(!$rel){
            header("Location: ".ROOT."salesman_list");
        }

		$form_type = $rel['l_form'];
		$form_id = $rel['l_form_id'];

	}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once($include.'include_css_file.php');?>
	<style>
		.head_margin
		{
			padding:10px;
		}
		.form_class
		{
			
		}
		.back_head_color
		{
			background-color:#337AB7 !important;
			color:#ffffff !important;
		}
		.row_margin
		{
			margin-top:20px;
		}
		.margin_row
		{
			margin-top:20px;
		}
		
		.salesman_forms
		{
			display:none;
		}
	</style>
</head>
<body>
<section id="container" >
<?php include_once($include.'include_top_menu.php');?>
<!--sidebar start-->
<?php include_once($include.'left_menu.php');?>
<!--sidebar end-->
<!--main content start-->
<form class="form-horizontal" role="form" id="salesman_add" action="javascript:;" method="post" name="salesman_add" enctype="multipart/form-data">	
<section id="main-content">
<section class="wrapper">			
<div class="row">
	<div class="col-lg-12">
		<!--breadcrumbs start -->
		<section class="panel">
			<header class="panel-heading">
				<h3><?=$mode.' '.$form?></h3>
			</header>	
			<div class="">
				<ul class="breadcrumb">
					<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
					<li><a href="<?=ROOT.ADMINISTRATION_ROOT.'salesman_list'?>"><?=$form?> List</a></li>
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
				New <?=$form?>
			</header>
			
			<div class="panel-body ">
				<div class="row">
					
					<div class="col-md-12 margin_row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Salesman Name *</label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="Salesman Name" title="Salesman Name" name="salesman_name" id="salesman_name" value="<?=$rel['salesman_name']?>" required  />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Allias Name</label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="Salesman Allias Name" title="Salesman Allias Name" name="salesman_allias" id="salesman_allias" value="<?=$rel['salesman_allias']?>"  />
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-12 margin_row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Print Name</label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="Salesman Print Name" title="Salesman Print Name" name="salesman_print_name" id="salesman_print_name" value="<?=$rel['salesman_print_name']?>"  />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Email *</label>
								<div class="col-md-8 col-xs-11">
									<input type="email" class="form-control" placeholder="Salesman Email" name="salesman_email" id="salesman_email"   value="<?=$rel['salesman_email']?>" required  />
								</div>
							</div>
							
						</div>
					</div>

					<div class="col-md-12 margin_row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Mobile *</label>
								<div class="col-md-8 col-xs-11">
									<input type="number" class="form-control" minlength="10" maxlength="10" placeholder="Salesman Mobile Number" title="Salesman Mobile Number" name="salesman_mobile" id="salesman_mobile" value="<?=$rel['salesman_mobile']?>" required  />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">WhatsUp *</label>
								<div class="col-md-8 col-xs-11">
									<input type="number" class="form-control" minlength="10" maxlength="10" placeholder="Salesman WhatsUp Number" title="Salesman WhatsUp Number" name="salesman_whatsup" id="salesman_whatsup" value="<?=$rel['salesman_whatsup']?>" required  />
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-12 margin_row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Address</label>
								<div class="col-md-8 col-xs-11">
									<textarea class="form-control" rows="5" placeholder="Salesman Address" title="Salesman Address" name="salesman_address" id="salesman_address"><?=$rel['salesman_address']?></textarea>
									
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Commision type *</label>
								<div class="col-md-8 col-xs-11">
									<select class="select2" name="salesman_comm_type" id="salesman_comm_type" autocomplete="off" required >
                                       <option value="">Select Type</option>
                                       <option value="1" <?php if($rel['salesman_comm_type']=='1'){ echo "selected"; } ?>>On Bill</option>
                                       <option value="2" <?php if($rel['salesman_comm_type']=='2'){ echo "selected"; } ?>>Fixed</option>
                                    </select>
								</div>
							</div><br>
							<div class="form-group">
								<label class="col-md-4 control-label">Commision Percentage *</label>
								<div class="col-md-8 col-xs-11">
									<input type="number" required class="form-control" placeholder="Salesman Commision type" name="salesman_commision" id="salesman_commision"   value="<?=$rel['salesman_commision']?>" style="text-transform:uppercase"  />
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12 col-md-offset-5 row_margin" >
				
						<input type="hidden"  value="" id="form_type" name="form_type"  />
						<input type='hidden' name='mode' id='mode' value='<?php if($mode=='Edit') { echo "edit"; } else { echo "add"; } ?>' />
						<input type='hidden' name='salesman_id' id='salesman_id' value='<?php if($mode=='Edit') { echo $salesman_id; } else { echo "0"; } ?>' />				  
						<button type="submit" name="" id="btn_submit" class="btn btn-success">Submit</button>
						<a class="btn btn-danger" href="<?=ROOT.ADMINISTRATION_ROOT.'salesman_list'?>">Cancel</a>
					
					</div>
				</div>
			</div>
		</section>
		
	</div>


</div>
<!--state overview end-->
</section>
</section>

	</form>
<!--main content end-->
<!--footer start-->
<?php include_once($include.'footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/salesman.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '80%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});

</script>
<?php
	
	?>
</body>
</html>
