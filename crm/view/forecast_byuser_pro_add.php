<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';

$form="Forecast";
$com="select * from tbl_company where company_id=".$_SESSION['company_id'];
$comty=mysqli_fetch_assoc($dbcon->query($com));
$branch_id = $_SESSION['branch_id'];
	//check permission for forcast by user pro list
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FORECAST_BY_USER_PRO_SLUG_ADD,
	FORECAST_BY_USER_PRO_SLUG_EDIT
]);

$getspecialConfiguration=getspecialConfiguration($dbcon);

if(strpos($_SERVER['REQUEST_URI'], "forecast_byuser_pro_edit")==false) {

	$mode_action = "Add";
	if ($getspecialConfiguration["umaboy_permission"] == 1) {
		$mode_action = "add_new";
	}
	$mode="Add";

	if(!in_array(FORECAST_BY_USER_PRO_SLUG_ADD,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}

	$f_by_id='1';
	$f_target_period='1';
	$f_period_id= $getspecialConfiguration["umaboy_permission"] == 1 ? '1' : '';
}
else {
	$mode="Edit";
	$mode_action="Edit";
	if ($getspecialConfiguration["umaboy_permission"] == 1) {
		$mode_action = "edit_new";
	}

	if(!in_array(FORECAST_BY_USER_PRO_SLUG_EDIT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	
	$forecast_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from tbl_forecast_byuser_pro where forecast_id=$forecast_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	$f_by_id=$rel['f_by_id'];
	$f_target_period=$rel['f_target_period'];
	$f_period_id=$rel['f_period_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>FORECAST</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<?php include_once('../../include/include_css_file.php');?>
</head>
<body>
	<section id="container">
		<?php include_once('../../include/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$mode.' '.$form?>
		<!--<a href="<?=ROOT.'import_product'?>" >
			<button class="btn btn-primary btn-flat pull-right">Import <?=$form?></button></a>-->
		</h3>
	</header>	
	<div class="">
		<ul class="breadcrumb">
			<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
			<li class="active"><a href="<?=ROOT.CRM_ROOT.'forecast_byuser_pro_list'?>"><?=$form?> List </a></li>
		</ul>
	</div>
</section>
<!--breadcrumbs end -->
</div>	
</div>
<!--Customer overview start-->

<div class="row">
	<div class="col-sm-12">
		<section class="panel">
			<header class="panel-heading">
				New <?=$form?> 
				<span class="tools pull-right">
					<a href="javascript:;" class="fa fa-chevron-down"></a>
				</span>
				<?php 
		/*$s_year=2016;
		$e_year=date("Y");
		for($i=$e_year;$i>=$s_year;$i--){
			echo $i."<br/>";
		}*/
		?>
	</header>	
	<div class="panel-body">
		<form class="form-horizontal" role="form" id="forecast_add" action="javascript:;" method="post" name="forecast_add">
			
			<div class="col-md-12">
				
			<!--<div class="col-md-4">
				<div class="form-group">
					<label for="t_id" class="col-md-4 control-label">Territory*</label>
					<div class="col-md-8">
						<select class="select2" id="t_id" name="t_id" >
							<option value="0">India</option>
						</select>
					</div>
				</div>							 
			</div>-->
			<div class="col-md-6">
				<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'','4','8'); ?>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="f_by_id" class="col-md-4 control-label">Forecast By *</label>
					<div class="col-md-8">
						<select class="select2" id="f_by_id" name="f_by_id" onchange="load_f_by_year(this.value);load_f_period();">
							<?=get_for_cast_by($dbcon,$f_by_id);?>
						</select>
					</div>
				</div>							 
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="f_year" class="col-md-4 control-label">Year *</label>
					<div class="col-md-8">
						<select class="select2" id="f_year" name="f_year" >
							<?=load_f_by_year($f_by_id,$rel['f_year'])?>
						</select>
					</div>
				</div>							 
			</div>
			<div class="clearfix"></div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="f_target_period" class="col-md-4 control-label">Forecast Target Period *</label>
					<div class="col-md-8">
						<select class="select2" id="f_target_period" name="f_target_period" onchange="load_f_period();">
							<?=get_for_target_p($dbcon,$f_target_period);?>
						</select>
					</div>
				</div>							 
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="f_period_id" class="col-md-4 control-label">Period Name *</label>
					<div class="col-md-8">
						<select class="select2" id="f_period_id" name="f_period_id" >
							<?=get_for_period($dbcon,$f_by_id,$f_target_period,$f_period_id);?>
						</select>
					</div>
				</div>							 
			</div>
			<div class="clearfix"></div>	
			<div class="col-md-6">
				<div class="form-group">
					<label for="f_target_amt" class="col-md-4 control-label">Target Amount *</label>
					<div class="col-md-8">
						<input type="number" min="0" class="form-control" id="f_target_amt" name="f_target_amt" value="<?=$rel['f_target_amt']?>" onkeypress="return isNumberKey(event)" >
					</div>
				</div>							 
			</div>	
			<div class="col-md-6">
				<div class="form-group">
					<label for="f_target_qty" class="col-md-4 control-label">Target Qty</label>
					<div class="col-md-8">
						<input type="number" min="0" class="form-control" id="f_target_qty" name="f_target_qty" value="<?=$rel['f_target_qty']?>" onkeypress="return isNumberKey(event)">
					</div>
				</div>							 
			</div>
			
			<div class="clearfix"></div>	

			<header class="panel-heading breadcrumb text-left">
				<h3>Target For User & Products</h3>
			</header>
			<div class="clearfix"></div>	
			
			<!-- Accordian Start -->
			<?php 

			$company_config = getCompanyConfiguration($dbcon);
			$crm_user_type = $company_config['crm_user_type'];
			$get_usr_qry = get_assign_users_data_query($dbcon, $crm_user_type);
			
			$k=1;$t=1;
			$get_usr_qry_rs=$dbcon->query($get_usr_qry);
			while($get_usr_rel=mysqli_fetch_assoc($get_usr_qry_rs)){
				?>
				<div class="col-md-12">
					<div class="panel-group m-bot20" id="accordion<?=$k?>">
						<div class="panel panel-default">
							
							<div id="main_div<?=$k?>" style="border: 1px solid;">	
								<div class="panel-heading" style="padding-bottom: 20px;">
									<div class="col-md-12">
										<div class="col-md-4 text-left">
											<h3 class="panel-title">
												<strong><?=$get_usr_rel['user_name']?></strong> - <?=$get_usr_rel['usertype_name']?>
												<input type="hidden" id="user_id<?=$k?>" name="user_id[]" value="<?=$get_usr_rel['user_id']?>">
											</h3>
										</div>
										<div class="col-md-3">
											<input type="number" min="0" class="form-control" id="usr_target_amt<?=$k?>" name="usr_target_amt[]" value="<?=$get_usr_rel['usr_target_amt']?>" placeholder="Target Amount" onkeypress="return isNumberKey(event)">
										</div>
										<div class="col-md-3">
											<input type="number" min="0" class="form-control" id="usr_target_qty<?=$k?>" name="usr_target_qty[]" value="<?=$get_usr_rel['usr_target_qty']?>" placeholder="Target Qty" onkeypress="return isNumberKey(event)">
										</div>
									</div>
									
									<h3 class="panel-title">
										<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion<?=$k?>" href="#divcnt<?=$k?>">
											Product Target For <?=$get_usr_rel['user_name']?> :<i class="fa fa-chevron-down"></i>
										</a>
									</h3>
								</div> 
								<div id="divcnt<?=$k?>" class="panel-collapse collapse">
									<div class="panel-body">
										<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
											<thead>
												<tr>
													<th width="60%">Product</th>
													<th width="20%">Target Amount</th>
													<th width="20%">Target Qty.</th>
												</tr>
											</thead>
											<tbody>
												<?php 
												
												// $get_ter_qry="select ter.product_id,ter.product_name,trn.ter_target_amt,trn.ter_target_qty from product_mst as ter 
												// left join tbl_f_byuser_pro_inrtrn as trn on trn.product_id=ter.product_id and trn.f_ter_trn_status=0 and trn.forecast_id='".$rel['forecast_id']."' and trn.ref_user_id='".$get_usr_rel['user_id']."'
												// where ter.product_status=0 and ter.product_type=0 order by ter.product_name";

												
												if ($getspecialConfiguration["umaboy_permission"] == 1) {
													$get_ter_qry = "select ter.cat_id as product_id,ter.cat_name as product_name,trn.ter_target_amt,trn.ter_target_qty from tbl_category as ter left join product_mst as pro on pro.product_category=ter.cat_id left join tbl_f_byuser_pro_inrtrn as trn on trn.product_id=ter.cat_id and trn.f_ter_trn_status=0 and trn.forecast_id='" . $rel['forecast_id'] . "' and trn.ref_user_id='" . $get_usr_rel['user_id'] . "' where pro.product_status=0 and pro.product_type=0 and ter.cat_status=0 and ter.company_id=".$_SESSION['company_id']." group by ter.cat_id order by ter.cat_name";
												} else {
													$get_ter_qry = "select ter.product_id,ter.product_name,trn.ter_target_amt,trn.ter_target_qty from product_mst as ter left join tbl_f_byuser_pro_inrtrn as trn on trn.product_id=ter.product_id and trn.f_ter_trn_status=0 and trn.forecast_id='" . $rel['forecast_id'] . "' and trn.ref_user_id='" . $get_usr_rel['user_id'] . "' where ter.product_status=0 and ter.product_type=0 order by ter.product_name";
												}
												
												$get_ter_qry_rs=$dbcon->query($get_ter_qry);
												while($get_ter_rel=mysqli_fetch_assoc($get_ter_qry_rs)){
													?>
													<tr>
														<td style="vertical-align: middle;text-align:left;"><?=$get_ter_rel['product_name']?>
														<input type="hidden" id="product_id<?=$t?>" name="product_id[]" value="<?=$get_ter_rel['product_id']?>">
														<input type="hidden" id="ref_user_id<?=$t?>" name="ref_user_id[]" value="<?=$get_usr_rel['user_id']?>">
													</td>
													<td style="vertical-align: middle;text-align:center;">
														<input type="number" min="0" class="form-control" id="ter_target_amt<?=$t?>" name="ter_target_amt[]" value="<?=$get_ter_rel['ter_target_amt']?>">
													</td>
													<td style="vertical-align: middle;text-align:center;">
														<input type="number" min="0" class="form-control" id="ter_target_qty<?=$t?>" name="ter_target_qty[]" value="<?=$get_ter_rel['ter_target_qty']?>">
													</td>
												</tr>
												<?php 
												$t++;
											}
											?>
										</tbody>
									</table>
									
								</div>
							</div>
						</div>
						
					</div>
				</div>
			</div>
			<?php 
			$k++;
		}
		?>
		<!-- Accordian End -->

		<div class="clearfix"></div>	
		
		<div class="col-md-12 text-center">					  
			<input type='hidden' name='eid' id='eid' value='<?=$rel['forecast_id']?>' />				  
			<input type='hidden' name='mode' id='mode' value='<?php echo $mode_action; ?>' />				  
			<button type="submit" id="submit_btn" class="btn btn-shadow btn-success">Submit</button>
			<a class="btn btn-shadow btn-danger" href="<?=ROOT.CRM_ROOT.'forecast_byuser_pro_list'?>">Cancel</a>
		</div>
	</div>
</form>

</div>
</section>
</div>
</div>

<!--Customer overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once('../../include/footer.php');?>
<!--footer end-->
</section>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<script src="<?=ROOT.CRM_ROOT?>js/app/forecast_byuser_pro.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	});
//load_f_by_year(<?=$f_by_id?>);
$('#f_target_period').select2('readonly',true);
</script>
</body>
</html>