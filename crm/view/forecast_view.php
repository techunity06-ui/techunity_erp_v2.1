<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Forecast";

	$forecast_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select f.*,f_mst.f_period_name from tbl_forecast as f 
	left join forecast_period_mst as f_mst on f_mst.f_period_id=f.f_period_id
	where f.forecast_id=$forecast_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	

$com="select * from tbl_company where company_id=".$_SESSION['company_id'];
$comty=mysqli_fetch_assoc($dbcon->query($com));

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
<div class="row">
<div class="col-lg-12">
<!--breadcrumbs start -->
<section class="panel">
<header class="panel-heading">
		<span class="tools pull-right">
			<a href="<?=ROOT.'forecast_list'?>"><button type="button" class="btn btn-info"><i class="fa fa-long-arrow-left" aria-hidden="true"></i> Go Back</button></a>	
		</span>
	<h3><?=$mode.' '.$form?></h3>
</header>	
<div class="">
	<ul class="breadcrumb">
		<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active"><a href="<?=ROOT.'forecast_list'?>"><?=$form?> List </a></li>
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
		$whr='';
		//Prepare WHERE Condition
		if($rel['f_target_period']=='1'){
			$whr.=" and date_format(inquiry_date,'%b-%Y')='".$rel['f_period_name']."-".$rel['f_year']."'";
		}
		else if($rel['f_target_period']=='2'){
			$exp_f_period_name=explode(" - ",$rel['f_period_name']);
			$s_no=(getMonthNumber($exp_f_period_name[0]));
			$e_no=(getMonthNumber($exp_f_period_name[1]));
			$whr.=" and date_format(inquiry_date,'%Y-%m-01')>='".$rel['f_year']."-".$s_no."-01' and date_format(inquiry_date,'%Y-%m-01')<='".$rel['f_year']."-".$e_no."-01'";
		}
		else if($rel['f_target_period']=='3'){
			$exp_f_period_name=explode(" - ",$rel['f_period_name']);
			$s_no=(getMonthNumber($exp_f_period_name[0]));
			$e_no=(getMonthNumber($exp_f_period_name[1]));
			$whr.=" and date_format(inquiry_date,'%Y-%m-01')>='".$rel['f_year']."-".$s_no."-01' and date_format(inquiry_date,'%Y-%m-01')<='".$rel['f_year']."-".$e_no."-01'";
		}
		else if($rel['f_target_period']=='4'){
			$exp_f_period_name=explode(" - ",$rel['f_period_name']);
			$s_no=(getMonthNumber($exp_f_period_name[0]));
			$e_no=(getMonthNumber($exp_f_period_name[1]));
			$whr.=" and date_format(inquiry_date,'%Y-%m-01')>='".$rel['f_year']."-".$s_no."-01' and date_format(inquiry_date,'%Y-%m-01')<='".$rel['f_year']."-".$e_no."-01'";
		}
		
	?>
</header>	
<div class="panel-body">
		
		<div class="col-md-12">
	
	
	<div class="clearfix"></div>	
<!-- Accordian Start -->
<div class="col-md-12">
<div class="panel-group m-bot20" id="accordion1">
	<div class="panel panel-default">
		
		<div id="main_div1" style="border: 1px solid;">	
		  <div class="panel-heading" style="padding-bottom: 20px;">
			  <h3 class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#divcnt1">
					Target For Territories <i class="fa fa-chevron-down"></i>
				</a>
			  </h3>
		  </div> 
		  <div id="divcnt1" class="panel-collapse collapse">
			  <div class="panel-body">
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
					<thead>
						<tr>
							<th width="40%">Territory</th>
							<th width="10%">Target Amount</th>
							<th width="10%">Achieved Amount</th>
							<th width="10%">Pipeline Amount</th>
							<th width="10%">Target Qty.</th>
							<th width="10%">Achieved Qty.</th>
							<th width="10%">Pipeline Qty.</th>
						</tr>
					</thead>
					<tbody>
			<?php 
				$k=1;
				$get_ter_qry="select ter.t_id,ter.t_name,trn.ter_target_amt,trn.ter_target_qty,
				(SELECT sum(g_total) from tbl_inquiry WHERE inquiry_status=0 and stage_prob='100' and t_id=ter.t_id ".$whr.") as ach_amt,
				(SELECT sum(g_total) from tbl_inquiry WHERE inquiry_status=0 and stage_prob!='100' and stage_prob!='0' and t_id=ter.t_id ".$whr.") as pipe_amt,
				(SELECT sum(product_qty) from tbl_inquiry_trn 
				inner join tbl_inquiry on tbl_inquiry.inquiry_id=tbl_inquiry_trn.inquiry_id WHERE inquiry_status=0 and stage_prob='100' and inquiry_trn_status=0 and t_id=ter.t_id ".$whr.") as ach_qty,
				(SELECT sum(product_qty) from tbl_inquiry_trn 
				inner join tbl_inquiry on tbl_inquiry.inquiry_id=tbl_inquiry_trn.inquiry_id WHERE inquiry_status=0 and stage_prob!='100' and stage_prob!='0' and inquiry_trn_status=0 and t_id=ter.t_id ".$whr.") as pipe_qty
				from territory_mst as ter
				left join tbl_f_ter_trn as trn on trn.t_id=ter.t_id and trn.f_ter_trn_status=0 and trn.forecast_id='".$rel['forecast_id']."'
				where t_status=0";
				$get_ter_qry_rs=$dbcon->query($get_ter_qry);
				while($get_ter_rel=mysqli_fetch_assoc($get_ter_qry_rs)){
			?>
			<tr>
				<td style="vertical-align: middle;text-align:left;">
					<?=$get_ter_rel['t_name']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_ter_rel['ter_target_amt']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_ter_rel['ach_amt']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_ter_rel['pipe_amt']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_ter_rel['ter_target_qty']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_ter_rel['ach_qty']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_ter_rel['pipe_qty']?>
				</td>
			</tr>
			<?php 
				$k++;
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
<!-- Accordian End -->
<!-- Accordian Start -->
<div class="col-md-12">
<div class="panel-group m-bot20" id="accordion2">
	<div class="panel panel-default">
		
		<div id="main_div1" style="border: 1px solid;">	
		  <div class="panel-heading" style="padding-bottom: 20px;">
			  <h3 class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#divcnt2">
					Target For Users <i class="fa fa-chevron-down"></i>
				</a>
			  </h3>
		  </div> 
		  <div id="divcnt2" class="panel-collapse collapse">
			  <div class="panel-body">
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
					<thead>
						<tr>
							<th width="25%">User</th>
							<th width="15%">Role</th>
							<th width="10%">Target Amount</th>
							<th width="10%">Achieved Amount</th>
							<th width="10%">Pipeline Amount</th>
							<th width="10%">Target Qty.</th>
							<th width="10%">Achieved Qty.</th>
							<th width="10%">Pipeline Qty.</th>
						</tr>
					</thead>
					<tbody>
			<?php 
				$k=1;
				$get_usr_qry="select usr.user_id,usr.user_mail,type.usertype_name,trn.usr_target_amt,trn.usr_target_qty,
				(SELECT sum(g_total) from tbl_inquiry WHERE inquiry_status=0 and stage_prob='100' and user_id=usr.user_id ".$whr.") as ach_amt,
				(SELECT sum(g_total) from tbl_inquiry WHERE inquiry_status=0 and stage_prob!='100' and stage_prob!='0' and user_id=usr.user_id ".$whr.") as pipe_amt,
				(SELECT sum(product_qty) from tbl_inquiry_trn 
				inner join tbl_inquiry on tbl_inquiry.inquiry_id=tbl_inquiry_trn.inquiry_id WHERE inquiry_status=0 and stage_prob='100' and inquiry_trn_status=0 and tbl_inquiry.user_id=usr.user_id ".$whr.") as ach_qty,
				(SELECT sum(product_qty) from tbl_inquiry_trn 
				inner join tbl_inquiry on tbl_inquiry.inquiry_id=tbl_inquiry_trn.inquiry_id WHERE inquiry_status=0 and stage_prob!='100' and stage_prob!='0' and inquiry_trn_status=0 and tbl_inquiry.user_id=usr.user_id ".$whr.") as pipe_qty
				from users as usr 
				left join tbl_usertype as type on type.usertype_id=usr.user_type
				left join tbl_f_user_trn as trn on trn.user_id=usr.user_id and trn.f_user_trn_status=0 and trn.forecast_id='".$rel['forecast_id']."'
				where usr.active=0 and usr.user_type!=1";
				$get_usr_qry_rs=$dbcon->query($get_usr_qry);
				while($get_usr_rel=mysqli_fetch_assoc($get_usr_qry_rs)){
			?>
			<tr>
				<td style="vertical-align: middle;text-align:left;">
					<?=$get_usr_rel['user_mail']?>
				</td>
				<td style="vertical-align: middle;text-align:left;">
					<?=$get_usr_rel['usertype_name']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_usr_rel['usr_target_amt']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_usr_rel['ach_amt']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_usr_rel['pipe_amt']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_usr_rel['usr_target_qty']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_usr_rel['ach_qty']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_usr_rel['pipe_qty']?>
				</td>
			</tr>
			<?php 
				$k++;
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
<!-- Accordian End -->

<!-- Accordian Start -->
<div class="col-md-12">
<div class="panel-group m-bot20" id="accordion3">
	<div class="panel panel-default">
		
		<div id="main_div1" style="border: 1px solid;">	
		  <div class="panel-heading" style="padding-bottom: 20px;">
			  <h3 class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion3" href="#divcnt3">
					Target For Product Group <i class="fa fa-chevron-down"></i>
				</a>
			  </h3>
		  </div> 
		  <div id="divcnt3" class="panel-collapse collapse">
			  <div class="panel-body">
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
					<thead>
						<tr>
							<th width="40%">Product Group</th>
							<th width="10%">Target Amount</th>
							<th width="10%">Achieved Amount</th>
							<th width="10%">Pipeline Amount</th>
							<th width="10%">Target Qty.</th>
							<th width="10%">Achieved Qty.</th>
							<th width="10%">Pipeline Qty.</th>
						</tr>
					</thead>
					<tbody>
			<?php 
				$k=1;
				$get_grp_qry="select grp.pg_id,grp.pg_name,grp_trn.grp_target_amt,grp_trn.grp_target_qty,
				(SELECT sum(product_amount) from tbl_inquiry_trn 
				inner join tbl_inquiry on tbl_inquiry.inquiry_id=tbl_inquiry_trn.inquiry_id WHERE inquiry_status=0 and stage_prob='100' and inquiry_trn_status=0 and tbl_inquiry_trn.pg_id=grp.pg_id ".$whr.") as ach_amt,
				(SELECT sum(product_amount) from tbl_inquiry_trn 
				inner join tbl_inquiry on tbl_inquiry.inquiry_id=tbl_inquiry_trn.inquiry_id WHERE inquiry_status=0 and stage_prob!='100' and stage_prob!='0' and inquiry_trn_status=0 and tbl_inquiry_trn.pg_id=grp.pg_id ".$whr.") as pipe_amt,
				(SELECT sum(product_qty) from tbl_inquiry_trn 
				inner join tbl_inquiry on tbl_inquiry.inquiry_id=tbl_inquiry_trn.inquiry_id WHERE inquiry_status=0 and stage_prob='100' and inquiry_trn_status=0 and tbl_inquiry_trn.pg_id=grp.pg_id ".$whr.") as ach_qty,
				(SELECT sum(product_qty) from tbl_inquiry_trn 
				inner join tbl_inquiry on tbl_inquiry.inquiry_id=tbl_inquiry_trn.inquiry_id WHERE inquiry_status=0 and stage_prob!='100' and stage_prob!='0' and inquiry_trn_status=0 and tbl_inquiry_trn.pg_id=grp.pg_id ".$whr.") as pipe_qty
				from tbl_product_grp as grp 
				left join tbl_f_grp_trn as grp_trn on grp_trn.pg_id=grp.pg_id and grp_trn.f_grp_trn_status=0 and grp_trn.forecast_id='".$rel['forecast_id']."'
				where grp.pg_status=0";
				$get_grp_qry_rs=$dbcon->query($get_grp_qry);
				while($get_grp_rel=mysqli_fetch_assoc($get_grp_qry_rs)){
			?>
			<tr>
				<td style="vertical-align: middle;text-align:left;">
					<?=$get_grp_rel['pg_name']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_grp_rel['grp_target_amt']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_grp_rel['ach_amt']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_grp_rel['pipe_amt']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_grp_rel['grp_target_qty']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_grp_rel['ach_qty']?>
				</td>
				<td style="vertical-align: middle;text-align:right;">
					<?=$get_grp_rel['pipe_qty']?>
				</td>
			</tr>
			<?php 
				$k++;
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
<!-- Accordian End -->
	<div class="clearfix"></div>	
		
		<div class="col-md-12 text-center">	
			<a class="btn btn-shadow btn-danger" href="<?=ROOT.'forecast_list'?>">Back</a>
		</div>
	</div>

	
</div>
</section>
</div>
</div>

<!--Customer overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<!--<script src="<?=ROOT.CRM_ROOT?>js/app/forecast.js?<?=time()?>"></script>-->
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
</script>
</body>
</html>