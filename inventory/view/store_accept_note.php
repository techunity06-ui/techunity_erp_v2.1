<?php 


session_start();
include('../include/urlfile.php');	
$form="G.I.R";
$countryid='101';
$stateid='1';
$cityid='1';

$branch_id = $_SESSION['branch_id'];
if(strpos($_SERVER[REQUEST_URI], "gir_edit")==true){
	$mode="Edit";
	$gir_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from pro_gir where pro_gir_id=$gir_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	$back="gir_list";
}
else{
	$mode="Add";
	$store_accept_date=date('d-m-Y');
	$back="gir_list";
}
$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once($include.'include_css_file.php');?>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($include.'include_top_menu.php');?>
		<?php include_once($include.'left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<?php//include_once('../include/equick_link.php');?>
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$mode.' '.$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.INVENTORY_ROOT.'gir_list'?>"><?=$form?> List</a></li>
								</ul>
							</div>
						</section>
					</div>	
				</div>
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								New <?=$form?>
							</header>	
							<div class="panel-body">
								<form class="form-horizontal" role="form" id="gir_add" action="javascript:;" method="post" name="gir_add" enctype="multipart/form-data">
									<div class="row"> 
										<div class="col-md-12" style="margin-top:10px;">
											<div class="col-md-4">
												<label class="col-md-4 control-label" style="">Store Accept No *</label>
												<div class="col-md-8" style="padding-left: 9px;">
													<input type="text" id="store_accept_no" name="store_accept_no" class="form-control" title="Store Accept No" value="" placeholder="Store Accept No">
												</div>  
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Store Accept Date *</label>
													<div class="col-md-8 col-xs-11">
														<input id="store_accept_date" name="store_accept_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$store_accept_date?>" placeholder="Store Accept Date">
													</div>
												</div>
											</div>	
										</div>
										<div class="col-md-12" >
											<div class="col-md-2"></div>
											<div class="col-md-4">
											<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
												<tr id="field" >
													<th width="20%" class="text-center">Godown</th>
													<th width="7%" class="text-center">Quantity</th>
													<th width="7%" class="text-center">Unit</th>
													<th width="5%" class="text-center"></th>
												</tr>

											</table>
										</div>
										
										</div>
										
									</div>

									<div class="clearfix"></div>	
								</div>
								<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
								<input type='hidden' name='eid' id='eid' value='<?=$rel['pro_gir_id']?>' />

								<input type='hidden' name='back' id='back' value='<?=$back?>' />
								<div class="clearfix"></div>	
								<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
								<a href="<?=ROOT.'gir_list'?>" type="button" class="btn btn-danger">Cancel</a>
								<div class="col-md-4"></div>
							</div>
						</form>
					</div>
				</section>
			</div>
		</div>		
	</section>
</section>
<?php include_once($include.'footer.php');?>
</section>
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.INVENTORY_ROOT?>js/app/store_accept_note.js?<?=time()?>"></script>
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
			
			
		</script> 
	</body>
	</html>