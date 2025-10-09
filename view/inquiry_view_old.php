<?php 
	session_start();
	include_once("../config/config.php");
	//error_reporting(E_ALL);
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Inquiry";
	$inquiry_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select inq.*,usr.user_name,cust.cust_name,person.c_con_fname,person.c_con_lname,opp_mst.opp_stage,sales_stg.mcd_name as sales_stage_name,inq_type.mcd_name as inq_type_name,sour.rb_name from tbl_inquiry as inq
	left join tbl_customer as cust on cust.cust_id=inq.cust_id
	left join tbl_cust_contact as person on person.c_con_id=inq.c_con_id
	left join tbl_opportunity_mst as opp_mst on opp_mst.opp_id=inq.opp_id
	left join tbl_master_category_detail as sales_stg on sales_stg.mcd_id=inq.sales_stage_id
	left join tbl_master_category_detail as inq_type on inq_type.mcd_id=inq.inquiry_type_id
	left join tbl_refer_by as sour on sour.rb_id=inq.rb_id
	left join users as usr on usr.user_id=inq.user_id
	where inq.inquiry_id=$inquiry_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$inquiry_date=date('d-m-Y',strtotime($rel['inquiry_date']));
	$closing_date='';
	if($rel['closing_date']!="1970-01-01" && $rel['closing_date']!="0000-00-00"){
		$closing_date=date('d-m-Y',strtotime($rel['closing_date']));
	}
	$user_name=$rel['user_name'];
	
	
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	$back_link = $_SERVER['HTTP_REFERER'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container"> <!--class="sidebar-closed"-->
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
				<a href="<?=$back_link?>" type="button" class="btn btn-info" style="float:right;"><i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back</a>
				<h3><?='View '.$form?></h3>
				<div class="text-center">Owner : <strong><?=$user_name?></strong></div>
			</header>	
			<div class="">
				<?	
					
					/*$url = $_SERVER['HTTP_REFERER'];
					$infopage = basename($url);
					if($infopage=='crm_dashboard'){
						$back_link=ROOT.'crm_dashboard';
					}
					else{
						$back_link=ROOT.'inquiry_list';
					}*/
				?>
				
				<ul class="breadcrumb">
					<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
					<li><a href="<?=ROOT.'inquiry_list'?>"><?=$form?> List</a></li>
				</ul>
			</div>
		</section>
		<!--breadcrumbs end -->
	</div>	
</div>
<!--state overview start-->
<section class="panel">
<div class="row">			
	<div class="col-md-12">
		<header class="panel-heading">View <?=$form?></header>	
		<div class="panel-body">
			<div class="row">
				<div class="col-md-12">
					<header class="panel-heading breadcrumb text-center">
					   <h3>Inquiry Details</h3>
					</header>
					<table class="display table table-bordered table-striped">
						<tr>
							<td><strong>Inquiry No: </strong><?=$rel['inquiry_no']?></td>
							<td><strong>Inquiry Date: </strong><?=date("d-M-Y",strtotime($rel['inquiry_date']))?></td>
						</tr>
						<tr>
							<td><strong>Customer: </strong><?=$rel['cust_name']?> 
								<input type="hidden" id="cust_id" name="cust_id" value="<?=$rel['cust_id']?>">
								<button type="button" class="btn btn-sm btn-primary" data-original-title="View Customer Details" data-toggle="tooltip" data-placement="top" onclick="preview_cust_dtls()"><i class="fa fa-eye"></i></button>
							</td>
							<td><strong>Contact Person: </strong><?=$rel['c_con_fname'].' '.$rel['c_con_lname']?> 
								<input type="hidden" id="c_con_id" name="c_con_id" value="<?=$rel['c_con_id']?>">
								<button type="button" id="viewcustper" onclick="preview_cust_person()" title="View Contact Persons" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></button>
							</td>
						</tr>
						<tr>
							<td>
								<strong>Stage: </strong><?=$rel['opp_stage']?> (<strong><?=$rel['stage_prob']?> %</strong>)
							</td>
							<td><strong>Sales Stage: </strong><?=$rel['sales_stage_name']?></td>
						</tr>
						<tr>
							<td>
								<strong>Type: </strong><?=$rel['inq_type_name']?></td>
							<td><strong>Source: </strong><?=$rel['rb_name']?></td>
						</tr>
						<tr>
							<td>
								<strong>Remark: </strong><?=$rel['inq_desc']?></td>
							<td><strong>Closing Date: </strong><?=$closing_date?></td>
						</tr>
					</table>
				</div>
				<div class="col-md-12" style="padding-top:10px;">
					<hr/>
					<header class="panel-heading breadcrumb text-center">
					   <h3>Product Details</h3>
					</header>
					<div style="overflow:auto;">
					<table class="display table table-bordered table-striped">
						<tr>
							<th width="25%" class="text-center">Product Name</th>
							<th width="" class="text-center">Quantity</th>
							<th width="" class="text-center">Unit</th>
							<th width="" class="text-center">Rate</th>
							<th width="" class="text-center">Amount</th>
							<!--<th width="" class="text-center">Specification</th>-->
						</tr>
				<?
				$trn_quer1y="select quo.quotation_id from tbl_quotation_trn as trn 
				left join tbl_quotation as quo on quo.quotation_id=trn.quotation_id
				where trn.quot_trn_status=0 and quo.quotation_status=0 and quo.inquiry_id=".$rel['inquiry_id']." GROUP BY quo.cust_id order by quo.quotation_id DESC";
				$trn_query_s=$dbcon->query($trn_quer1y);
				$cnt=mysqli_num_rows($trn_query_s);
				if($cnt>0){
					$trn_r=mysqli_fetch_assoc($trn_query_s);
					
					$trn_query="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
					left join tbl_quotation as quo on quo.quotation_id=trn.quotation_id
					left join product_mst as pro on pro.product_id=trn.product_id
					left join unit_mst as unit on unit.unitid=trn.unitid
					where trn.quot_trn_status=0 and quo.quotation_status=0 and quo.quotation_id=".$trn_r['quotation_id']." order by quo.quotation_id DESC";
				}else{
					 $trn_query="select trn.*,pro.product_name,unit.unit_name from tbl_inquiry_trn as trn 
						left join product_mst as pro on pro.product_id=trn.product_id
						left join unit_mst as unit on unit.unitid=trn.unitid
						where trn.inquiry_trn_status=0 and trn.inquiry_id=".$rel['inquiry_id'];
				}
					$trn_query_rs=$dbcon->query($trn_query);
					while($trn_rel=mysqli_fetch_assoc($trn_query_rs))
					{
				?>
					<tr>
						<td>
							<strong><?=$trn_rel['product_name']?></strong><br/>
							<strong>Desc:</strong> <?=(nl2br($trn_rel['product_desc']))?>
						</td>
						<td class="text-center"><?=$trn_rel['product_qty']?></td>
						<td class="text-center"><?=$trn_rel['unit_name']?></td>
						<td class="text-right"><?=$trn_rel['product_rate']?></td>
						<td class="text-right"><?=$trn_rel['product_amount']?></td>
						<!--<td><?=(nl2br($trn_rel['product_spec']))?></td>-->
					</tr>
				<?
					}
				?>
					</table>
					</div>
				</div>
				<div class="col-md-12" style="padding-top:10px;">
					<hr/>
					<header class="panel-heading breadcrumb text-center">
					   <h3>Follow-Up History</h3>
					</header>
					<table class="display table table-bordered table-striped">
							<tr>
								<th>Quotation No</th>
								<th>Quotation Date</th>
								<th>Approve Status</th>
								<th>Action</th>
							</tr>
					<?
						$get_quot_qry="select quotation_id,quotation_no,quotation_date ,approve_status from tbl_quotation where inquiry_id=".$rel['inquiry_id'];
						$get_quot_qry_rs=$dbcon->query($get_quot_qry);
						if(mysqli_num_rows($get_quot_qry_rs)){
						while($get_quot_rel=mysqli_fetch_assoc($get_quot_qry_rs)){
					?>
						<tr>
							<td><?=$get_quot_rel['quotation_no']?></td>
							<td><?=date("d-M-Y", strtotime($get_quot_rel['quotation_date']))?></td>
							<td>
							<?
								if($get_quot_rel['approve_status']=='1'){
									echo '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Authorized</div>';
								}
								else{
									echo '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div>';
								}
							?>
							</td>
							<td>
								<a href="<?=ROOT.'quotation_print/'.$get_quot_rel['quotation_id']?>" type="button" class="btn btn-primary" target="_blank"> <i class="fa fa-eye"></i> View</a>
							</td>
						</tr>
						<?php	}
							}
							else{ ?>
							<tr>
								<td colspan="4" class="text-center">No Quotation Found !!!</td>
							</tr>
						<?	}	?>	
					</table>
					<div style="overflow:auto;">
					<table class="display table table-bordered table-striped">
						<tr>
							<th colspan="7" class="text-center">Appointment List</th>
						</tr>
						<tr>
							<th class="text-center">Sr.</th>
							<th class="text-center">Subject</th>
							<th class="text-center">Location</th>
							<th class="text-center">Start Time</th>
							<th class="text-center">End Time</th>
							<th class="text-center">Remark</th>
							<th class="text-center">Owner</th>
						</tr>
				<?
					$get_apt_qry="select tsk.*,sub.mcd_name as subject,usr.user_name,prior.task_priority_name from tbl_task as tsk 
					left join tbl_master_category_detail as sub on sub.mcd_id=tsk.task_type_id
					left join users as usr on usr.user_id=tsk.user_id
					left join task_priority_mst as prior on prior.task_priority_id=tsk.task_priority_id
					where tsk.task_status!=2 and tsk.task_rel_id=5 and tsk.entry_type=2 and tsk.inquiry_id=".$rel['inquiry_id']." order by tsk.create_date DESC";
					$get_apt_qry_rs=$dbcon->query($get_apt_qry);
					$k=1;
					if(mysqli_num_rows($get_apt_qry_rs)){
					while($apt_rel=mysqli_fetch_assoc($get_apt_qry_rs)){
						$appointment_start_time='';$appointment_end_time='';
						if($apt_rel['appointment_start_time']!="1970-01-01 00:00:00" && $apt_rel['appointment_start_time']!="0000-00-00 00:00:00"){
							$appointment_start_time=date('d-M-Y h:i A',strtotime($apt_rel['appointment_start_time']));
						}
						if($apt_rel['appointment_end_time']!="1970-01-01 00:00:00" && $apt_rel['appointment_end_time']!="0000-00-00 00:00:00"){
							$appointment_end_time=date('d-M-Y h:i A',strtotime($apt_rel['appointment_end_time']));
						}
				?>	
					<tr>
						<td><?=$k?></td>
						<td><?=$apt_rel['appointment_subject']?></td>
						<td><?=$apt_rel['task_location']?></td>
						<td><?=$appointment_start_time?></td>
						<td><?=$appointment_end_time?></td>
						<td><?=nl2br($apt_rel['task_remark'])?></td>
						<td><?=$apt_rel['user_name']?></td>
					</tr>
				<?	$k++;
					}
					}
					else{
				?>
					<tr>
						<td colspan="7" class="text-center">No Appointments Found!!!</td>
					</tr>
				<?
					}
				?>
					</table>
					</div>
					<div style="overflow:auto;">
					<table class="display table table-bordered table-striped">
						<tr>
							<th colspan="8" class="text-center">Follow-Up List</th>
						</tr>
						<tr>
							<th class="text-center">Sr.</th>
							<th class="text-center">Create Date</th>
							<th class="text-center">Subject</th>
							<th class="text-center">Priority</th>
							<th class="text-center">Due Date</th>
							<th class="text-center">Completion Date</th>
							<th class="text-center">Remark</th>
							<th class="text-center">Owner</th>
						</tr>
				<?
					$get_task_qry="select tsk.*,sub.mcd_name as subject,usr.user_name,prior.task_priority_name from tbl_task as tsk 
					left join tbl_master_category_detail as sub on sub.mcd_id=tsk.task_type_id
					left join users as usr on usr.user_id=tsk.user_id
					left join task_priority_mst as prior on prior.task_priority_id=tsk.task_priority_id
					where tsk.task_status!=2 and tsk.task_rel_id=5 and tsk.entry_type=1 and tsk.inquiry_id=".$rel['inquiry_id']." order by tsk.create_date DESC";
					$get_task_qry_rs=$dbcon->query($get_task_qry);
					$k=1;
					if(mysqli_num_rows($get_task_qry_rs)){
					while($task_rel=mysqli_fetch_assoc($get_task_qry_rs)){
						$task_completion_date='';$task_due_date='';
						if($task_rel['task_completion_date']!="1970-01-01 00:00:00" && $task_rel['task_completion_date']!="0000-00-00 00:00:00"){
							$task_completion_date=date('d-M-Y h:i A',strtotime($task_rel['task_completion_date']));
						}
						if($task_rel['task_due_date']!="1970-01-01 00:00:00" && $task_rel['task_due_date']!="0000-00-00 00:00:00"){
							$task_due_date=date('d-M-Y h:i A',strtotime($task_rel['task_due_date']));
						}
				?>	
					<tr>
						<td><?=$k?></td>
						<td><?=date('d-M-Y h:i A',strtotime($task_rel['create_date']));?></td>
						<td><?=$task_rel['subject']?></td>
						<td><?=$task_rel['task_priority_name']?></td>
						<td><?=$task_due_date?></td>
						<td><?=$task_completion_date?></td>
						<td><?=nl2br($task_rel['task_remark'])?></td>
						<td><?=$task_rel['user_name']?></td>
					</tr>
				<?	$k++;
					}
					}
					else{
				?>
					<tr>
						<td colspan="8" class="text-center">No Data Found!!!</td>
					</tr>
				<?
					}
				?>
					</table>
					</div>
					
				</div>
				
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12 text-center">
				<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>	
			</div>	
		</div>
	</div>
</div>	
</section>
</div>
</div>
<!--state overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once('../include/preview_cust_dtls.php');?>
<?php include_once('../include/preview_cust_person_dtl.php');?>
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   

<script src="<?=ROOT?>js/app/inquiry.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
</script>
</body>
</html>