<?php 

session_start();
include('../include/urlfile.php');
 $incPath = $path.'include/';
//$incPath ="../include/";
$form="Data Bank";
$infopage = pathinfo( __FILE__ );
	//print_r($infopage);
$_SESSION['page']=$infopage['filename'];
if(empty($_SESSION['start'])) {
	$start=date('1-m-Y');
	$end=date("d-m-Y");
}
else {
	$start=$_SESSION['start'];
	$end=$_SESSION['end'];
}

if($_REQUEST['id']){
	$child_usr_id=$dbcon->real_escape_string($_REQUEST['id']);
}

    // Amish Soni Start 19-01-2021
$crm_auto_mail = '';
$companySettings = getCompanySettings($dbcon);
if($companySettings) {
	$crm_auto_mail = $companySettings['crm_auto_mail'];
}
$showTemplate = ($crm_auto_mail == 'No');
    // Amish Soni End 19-01-2021

    //check permission for india mart data module
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	INDIA_MART_DATA_SLUG_READ,
	INDIA_MART_DATA_SLUG_LOAD_INQUIRY,
	INDIA_MART_DATA_SLUG_ADD_INQUIRY
]);

if(!in_array(INDIA_MART_DATA_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
$companyConfiguration=getCompanyConfiguration($dbcon);
$getspecialConfiguration=getspecialConfiguration($dbcon);
$crm_user_type=$companyConfiguration['crm_user_type'];
$enable_assing_user=$companyConfiguration['enable_assing_user'];
$branch_id = $_SESSION['branch_id'];

$is_umaboy = false;
if($getspecialConfiguration['umaboy_permission'] == '1'){
    $is_umaboy = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>INDIAMART LIST</title>
	<?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($incPath.'include_top_menu.php');?>
		<?php include_once($incPath.'left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3> <?=$form?> List</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.'indiamart_data_list'?>"><?=$form?> List</a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								<div class="col-md-5">
									<div class="form-group">
										<label class="control-label col-md-4">Choose Date</label>
										<div class="col-md-7">
											<div class="input-group date form_datetime-component">
												<input type="hidden" id="from_date" value="<?=$start?>">
												<input type="hidden" id="to_date" value="<?=$end?>">
												<input type="text" id="rep_date" onChange="load_inquiry_datatable();" class="form-control datepikerdemo" value="">
												<span class="input-group-btn">
													<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>	
								<div class="col-md-4">
									<div class="form-group">
										<label class="col-md-4 control-label">Source </label>
										<div class="col-md-8"> 
											<select class="select2" id="rb_id" name="rb_id" onchange="load_inquiry_datatable();">
												<?=get_refer_by($dbcon,$rel['rb_id']);?>
											</select>
										</div>
									</div>	
								</div>
								<span class="tools pull-right">
									<?php if(in_array(INDIA_MART_DATA_SLUG_LOAD_INQUIRY,$bulkAccessArray)){ ?>
										<button class="btn btn-primary" data-original-title="Load Inquiry" data-toggle="tooltip" data-placement="top" onClick="load_indiamart(1);load_trade_india(1)"><i class="fa fa-download"></i>Load Inquiry</button>
									<?php } ?>
									<?php if(in_array(INDIA_MART_DATA_SLUG_ADD_INQUIRY,$bulkAccessArray)){ ?>
										<button class="btn btn-success" data-original-title="Add To Inquiry" data-toggle="tooltip" data-placement="top" onClick="print_cust_label();"><i class="fa fa-upload"></i> Add To Inquiry</button>
									<?php } ?>		
								</span>
								<div class="col-md-12"	style="height:20px;" ></div>
							</header>	
							<div class="panel-body">
								<div class="adv-table dt-resp">
									<table class="display table table-bordered table-striped table-responsive" id="inquiry-table" style="overflow-x: scroll; width :100%;">
										<thead>
											<tr>
												<td width="6%">Source</td>
												<td width="6%">Id</td>
												<td width="6%">Date</td>
												<td width="6%">Time</td>
												<td width="6%">Sender Name</td>
												<td width="6%">Product Name</td>
												<td width="6%">Company</td>
												<td width="6%">City</td>
												<td width="6%">State</td>
												<td width="6%">Mobile No</td>
												<td width="6%">Email-Id</td>
												<td width="6%">User</td>
												<td width="23%">Remark</td>
												<td width="5%">Action</td>	
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
			</section>
		</section>
		<?php include_once('../include/add_ind_data.php');?>
		<?php include_once('../include/add_lead_user.php');?>
		<?php include_once($incPath.'footer.php');?>
	</section>
	<?php include_once($incPath.'include_js_file.php');?>
	<script src="<?= ROOT.CRM_ROOT ?>js/app/indiamart_data_list.js?<?=time()?>"></script>
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
		$(function(){
			setTimeout(function(){ $('#sidebar > ul').hide(); }, 1000);
		});
	</script>
</body>
</html>