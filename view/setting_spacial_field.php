<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
if(strpos($_SERVER['REQUEST_URI'], "setting")==false) {
	$mode="Add";
	$valid_till_start_date=date('1-m-Y');
	$valid_till_end_date=date("d-m-Y");
}
else {
	$mode="Edit";
	$eid=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from tbl_company where company_id=$eid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
}

// Amish Soni Start 12-01-2021

$getspecialConfiguration=getspecialConfiguration($dbcon);


// Amish Soni End 12-01-2021
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
	<section id="container" >
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
							<header class="panel-heading" style="padding-bottom: 20px;">
								<h3><?=$mode?> Company Setting
								</h3>
							</header>

							<div class="">
								<ul class="breadcrumb no_padding">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="javascript:;">Setting</a></li>
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
								Edit Company Setting
								<!-- Amish Soni Start 12-01-2021 -->
								<span class="tools pull-right">
									<a href="javascript:;" class="fa fa-chevron-down"></a>
								</span>
								<!-- Amish Soni End 12-01-2021 -->
							</header>	
							<div class="panel-body ">
								<form class="form-horizontal" role="form" id="company_configuration" action="javascript:;" method="post" name="company_configuration">
									<div class="row">
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Hermattic special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="hermattic_permission" id="hermattic_permission" required>
														<option value="0" <?=(($getspecialConfiguration['hermattic_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['hermattic_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Elcon special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="elcon_permission" id="elcon_permission" required>
														<option value="0" <?=(($getspecialConfiguration['elcon_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['elcon_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Maruti Machines special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="maruti_permission" id="maruti_permission" required>
														<option value="0" <?=(($getspecialConfiguration['maruti_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['maruti_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">RB Auto special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="rb_auto_permission" id="rb_auto_permission" required>
														<option value="0" <?=(($getspecialConfiguration['rb_auto_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['rb_auto_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Umaboy special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="umaboy_permission" id="umaboy_permission" required>
														<option value="0" <?=(($getspecialConfiguration['umaboy_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['umaboy_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Oilfield special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="oilfield_permission" id="oilfield_permission" required>
														<option value="0" <?=(($getspecialConfiguration['oilfield_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['oilfield_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">JR Fiber Glass special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="jr_fiber_glass_permission" id="jr_fiber_glass_permission" required>
														<option value="0" <?=(($getspecialConfiguration['jr_fiber_glass_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['jr_fiber_glass_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Vipul Copper special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="vipul_copper_permission" id="vipul_copper_permission" required>
														<option value="0" <?=(($getspecialConfiguration['vipul_copper_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['vipul_copper_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Filter Concept special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="filter_concept_permission" id="filter_concept_permission" required>
														<option value="0" <?=(($getspecialConfiguration['filter_concept_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['filter_concept_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Atlas special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="atlas_permission" id="atlas_permission" required>
														<option value="0" <?=(($getspecialConfiguration['atlas_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['atlas_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">SMPL special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="smpl_permission" id="smpl_permission" required>
														<option value="0" <?=(($getspecialConfiguration['smpl_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['smpl_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Durva special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="durva_permission" id="durva_permission" required>
														<option value="0" <?=(($getspecialConfiguration['durva_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['durva_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Aeon special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="aeon_permission" id="aeon_permission" required>
														<option value="0" <?=(($getspecialConfiguration['aeon_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['aeon_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Sreeji Stilix special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="sreeji_stilix_permission" id="sreeji_stilix_permission" required>
														<option value="0" <?=(($getspecialConfiguration['sreeji_stilix_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['sreeji_stilix_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Libra Engineering special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="libra_engineering_permission" id="libra_engineering_permission" required>
														<option value="0" <?=(($getspecialConfiguration['libra_engineering_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['libra_engineering_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Power Drive special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="power_drive" id="power_drive" required>
														<option value="0" <?=(($getspecialConfiguration['power_drive'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['power_drive'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">SSPL special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="sspl" id="sspl" required>
														<option value="0" <?=(($getspecialConfiguration['sspl'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['sspl'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Reciclar special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="reciclar" id="reciclar" required>
														<option value="0" <?=(($getspecialConfiguration['reciclar'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['reciclar'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Meru special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="meru_permission" id="meru_permission" required>
														<option value="0" <?=(($getspecialConfiguration['meru_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['meru_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Austar special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="austar_permission" id="austar_permission" required>
														<option value="0" <?=(($getspecialConfiguration['austar_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['austar_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Invoite special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="invoite_permission" id="invoite_permission" required>
														<option value="0" <?=(($getspecialConfiguration['invoite_permission'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['invoite_permission'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Apson special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="apson_special" id="apson_special" required>
														<option value="0" <?=(($getspecialConfiguration['apson_special'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['apson_special'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Uniter special: </label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="uniter_special" id="uniter_special" required>
														<option value="0" <?=(($getspecialConfiguration['uniter_special'] == 0)? "selected='selected'" : '')?>>No</option>
														<option value="1" <?=(($getspecialConfiguration['uniter_special'] == 1)? "selected='selected'" : '')?>>Yes</option>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-3"></div>
										<div class="col-md-3">
											<button type="submit" class="btn btn-success">Submit</button>
										</div>					 	
									</div><!--Vendor row end-->	
									<input type='hidden' name='mode' id='mode' value='company_configuration' />
									<input type='hidden' name='sp_field_permission_id' id='sp_field_permission_id' value='<?=$getspecialConfiguration['sp_field_permission_id']?>' />
								</form>
							</div>
						</section>
					</div>
					<!--state overview end-->
				</section>
			</section>
			<!--main content end-->
			<!--footer start-->
			<?php include_once('../include/footer.php');?>
			<!--footer end-->
		</section>
		<!-- js placed at the end of the document so the pages load faster -->
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/setting_spacial_field.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$(".form_datetime-meridian").datetimepicker({
				format: "dd-mm-yyyy HH:ii P",
				showMeridian: true,
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left"
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
				"startDate": $('#valid_till_start_date').val(),
				"endDate": $('#valid_till_end_date').val(),	
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
				$('.datepikerdemo').trigger('click')
			});
			function trancate_tables(val)
			{
				var r= confirm(" Are you want to Remove Data ?");
				if(r) {
					Loading(true);	
					window.location=root_domain+'backup/'+val;
				}
			}
			/*CKEDITOR.replace( 'address', {
				enterMode: CKEDITOR.ENTER_BR
			});
			CKEDITOR.replace( 'logo_content', {
				enterMode: CKEDITOR.ENTER_BR
			});
			CKEDITOR.replace( 'condition', {
				enterMode: CKEDITOR.ENTER_BR
			});

			CKEDITOR.replace( 'quotation_print_content', {
				enterMode: CKEDITOR.ENTER_BR
			});*/
		</script>
	</body>
	</html>