<?php 
session_start();
include('../include/urlfile.php');
include("../../include/dashboard_common_functions.php"); 
// $incPath = $path.'include/';
$start=date('d-m-Y');
$end=date("d-m-Y", strtotime('+1 month'));
$month = date("m");
$form="Value Wise Target ";
$max_followup_date = MAX_FOLLOWUP_DATE;

$companySettings = getCompanySettings($dbcon);
$crm_auto_mail = $companySettings['crm_auto_mail'];
$showTemplate = ($crm_auto_mail == 'No');

$companyConfiguration=getCompanyConfiguration($dbcon);
$crm_user_type=$companyConfiguration['crm_user_type'];
//echo $max_followup_date;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once($include.'include_css_file.php');?>
	<link href="<?= ROOT ?>assets/morris.js-0.4.3/morris.css" rel="stylesheet" />
<style type="text/css">
.count , .count2
{
  margin:0px !important;
  padding:0px !important

}
.cc_count
{
  margin-left:5%;
}

.panel-heading
{
  text-align:center;
  font-weight:bold;
  FONT-SIZE:16px;
}

.border_line
{
  border-bottom:dotted blue 2px;
}

.link_dash
{
  border-bottom:dotted blue thin;
}

</style>
<style>
.icons{
    width: 13%;
    float: left;
    margin: 25px 0px;
    text-align: center;
    position:relative;

}
.icons12{
    background-color:#fff;
    padding-top:15px;
    border: 8px;
}
.icons p{
 text-align:center;
 font-size:15px;
 font-weight:600;
 padding-top:5px;
 font-color:white

}

.icon1 fa{

}
.icon1.success{background-color: #5cb85c;}
.icon1.primary{background-color: #0275d8;}
.icon1.warning{background-color: #f0ad4e;}
.icon1.info{background-color: #5bc0de;}
.icon1.danger{background-color: #d9534f;}
.icon1.terques{background-color: #6ccac9;}
.icon1.yellow{background-color: #f8d347;}
.icon1.pink{background-color:#E5649A;}
.icon1.mustard{background-color:#F0BD23;}
.icon1.success,.icon1.primary,.icon1.warning,.icon1.danger,.icon1.info,.icon1.terques,.icon1.yellow,.icon1.pink,.icon1.mustard{
    width: 130px;
    height:130px;
    border-radius: 8px;
    text-align:center;
}
.icon1.success i,.icon1.primary i,.icon1.warning i,.icon1.danger i,.icon1.info i,.icon1.terques i,.icon1.yellow i,.icon1.pink i,.icon1.mustard i{
 text-align:center;
 color:#fff;
 padding-top: 27%;
 font-size: 37px;
}
@media (max-width:767px){
    .icons {
        width:265px;
        float: left;
        margin: 30px 4px 25px;
        position:relative;
    }

}
@media (min-width:768px) and (max-width:980px)
{
 .icons12{
    background-color:#fff;
    padding-top:20px;
    padding-bottom:20px;
    border-radius: 8px;
}
.icons {
    width: 17%;
    float: left;
    margin: 30px 4px 25px;
    text-align: center;
    position:relative;
}

}
.icons .badge {
    position: absolute;
    right: 25px;
    top: 0px;
    z-index: 100;
}
.hh {
	font-family: "Segoe UI",Arial,sans-serif;
    font-weight: 400;
    margin: 10px 0;
    font-size: 25px;
    box-sizing: inherit;
    margin-block-start: -0.5em;
    margin-block-end: 0.0em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    color: #fff!important;
    background-color: #009688!important;
}
</style>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($include.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($include.'left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<section class="panel">
					<div class="panel-body ">
						<div class="row">
							<div class="col-md-12">
								<div class="col-lg-12 centeral-align" style="text-align:center;">
									<div class="icons">
										<a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?php echo CRM_ROOT.'quotation_list' ?>" target="new">
											<div class="icon1 danger" >
												<p style="color:white;padding-top:5px;">Current month</p>
												<h3 style="font-size:18px;color:white;padding-top:2px;" ></h3>
												<p style="color:white;" id="quotamount"></p>
												<!-- <p style="color:white;" id="quotamount">Count : <?php //=get_total_current_month_target($dbcon,$_SESSION['user_id']);?></p> -->
											</div>
										</a>
									</div>
									<div class="icons">
										<a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?php echo ROOT.'pending_so_approve_list';?>" target="new">
											<div class="icon1 primary" >
												<p style="color:white;padding-top:5px;">Outstanding</p>
												<h3 style="font-size:18px;color:white;padding-top:2px;" ></h3>
												<p style="color:white;" id="soamount"></p>
												<!-- <p style="color:white;" id="soamount">Count : <?php //=number_format(get_total_outstanding_target($dbcon,$_SESSION['user_id']),2);?></p> -->
											</div>
										</a>
									</div>
									<div class="icons">
										<a class="" data-original-title="" data-toggle="tooltip" data-placement="top" href="<?php echo CRM_ROOT.'order_acceptance_list';?>" target="new">
											<div class="icon1 warning" >
												<p style="color:white;padding-top:5px;">Achieved</p>
												<h3 style="font-size:18px;color:white;padding-top:2px;" ></h3>
												<p style="color:white;" id="oaamount"></p>
												<!-- <p style="color:white;" id="oaamount">Count : <?php //=get_total_achieved_target($dbcon,$_SESSION['user_id']);?></p> -->
											</div>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
				<!--state overview start-->
				<section class="panel">
					<div class="col-md-12" style="margin-top:20px;"></div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-12" style="margin-top:10px;">
									<label class="col-md-4 control-label" style="font-weight: bold;font-size: 20px;color: black;"><?=$form?><strong style="color:red"><?=date('F', mktime(0, 0, 0, $month, 10));?></strong></label>
									<input type="hidden" name="month" id="month" value="<?=$month?>" />
									<div class="col-md-4">
										<div class="form-group">
											<label class="col-md-4 control-label" style="text-align: right; font-weight: bold;">User :</label>
											<div class="col-md-8"> 
												<select class="select2" id="user_id" name="user_id" onChange="load_value_vise_target();">
													<?=get_assign_users_inq($dbcon,''); ?>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="col-md-4 control-label" style="text-align: right;">State :</label>
											<div class="col-md-8"> 
												<select class="select2" id="state_id" name="state_id" onChange="load_value_vise_target();">
													<option value="">Select State</option>
													<?=getstate($dbcon,'')?>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="panel-body">
									<div class="adv-table">
										<table class="table table-bordered table-hover table-striped" id="details_table">
											<thead>
												<th></th>
												<th>Company</th>
												<th>Owner User</th>
												<th>Current Month Target</th>
												<th>Outstanding</th>
												<th>Achieved</th>
												<th>Action</th>
											</thead>
											<tbody></tbody>
											<tfoot>
												<tr>
													<th colspan="3" style="text-align:right">Total:</th>
													<th></th>
													<th></th>
													<th></th>
													<th></th>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
				<!--state overview end-->
				<input type="hidden" value="<?=$max_followup_date?>" id="max_followup_date" />
			</section>
		</section>
		<!--main content end-->
		<!--footer start-->
		<?php include_once($include.'footer.php');?>
		<!--footer end-->
	</section>
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>
	<?php include_once($include1.'add_value_wise_followup.php');?>
	<?php include_once($include1.'value_wise_followup_history.php');?>
	<script src="<?=ROOT.CRM_ROOT?>js/app/dashboard_target.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});

		$(document).ready(function() {
			Loading(true);	
			load_value_vise_target();
			Unloading();
		});
	</script>
</body>
</html>