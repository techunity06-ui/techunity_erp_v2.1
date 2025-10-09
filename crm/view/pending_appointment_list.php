<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';
$form="Upcoming Appointments";
$task_type_id='';
if($_REQUEST['id']){
	$task_type_id=$dbcon->real_escape_string($_REQUEST['id']);
		/*$tsk_qry="select mcd_name from tbl_master_category_detail where mcd_id=".$task_type_id;
		$tsk_rel=mysqli_fetch_assoc($dbcon->query($tsk_qry));*/
	}
	$start=date('d-m-Y');
	$end=date("d-m-Y", strtotime('+1 month'));
	
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container">
			<?php include_once('../include/include_top_menu.php');?>
			<!--sidebar start-->
			<?php include_once('../include/left_menu.php');?>
			<!--sidebar end-->
			<!--main content start-->
			<section id="main-content">
				<section class="wrapper">

					<!--state overview start-->
					<section class="panel">
						<header class="panel-heading">
							<div class="col-md-5">
								<div class="form-group">
									<label class="control-label col-md-4" style="font-weight:bold;">Due Date:</label>
									<div class="col-md-7">
										<input id="from_date" name="from_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$start?>" placeholder="Start Date" onchange="load_pend_appointment();">
<!--			<div class="input-group date form_datetime-component">
				<input type="hidden" id="from_date" value="<?//=$start?>">
				<input type="hidden" id="to_date" value="<?//=$end?>">
				<input type="text" id="fil_due_date" onChange="load_pend_appointment();" class="form-control datepikerdemo" value="">
				<span class="input-group-btn">
					<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
				</span>
			</div>-->
			
		</div>
	</div>
</div>	
<!--<div class="col-md-5">
	<div class="form-group">
		<label class="control-label col-md-4" style="font-weight:bold;">Task Type</label>
		<div class="col-md-7">
			<select class="select2" id="fil_task_type_id" name="fil_task_type_id" onChange="load_pend_task();">
				<option value="">ALL</option>
				<?//=get_master_category_dtl($dbcon,$task_type_id,10);//10:Task?>
			</select>
		</div>
	</div>
</div>-->
<div class="col-md-12" style="height:20px;"></div>
</header>
<div class="panel-body">
	<div class="row">
		<div class="col-md-12">
			<div class="col-md-12" style="margin-top:10px;">
				<label class="col-md-12 control-label" style="font-weight: bold;font-size: 20px;color: black;">
					<?=$form?></label>
					<div class="col-md-12" id="pend_apt_tbl">

					</div>
				</div>
			</div>
		</div>
	</div>
</section>
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
<script src="<?=ROOT.CRM_ROOT?>js/app/appointment.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
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
	$('.default-date-picker').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	});
	$(document).ready(function() {
		Loading(true);	
		load_pend_appointment();
		Unloading();
	});
</script>
</body>
</html>
