<?php 
	session_start();
	include('../include/urlfile.php');

	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$form="Inquiry";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	if(strpos($_SERVER[REQUEST_URI], "inquiry_edit")==true) {
		$mode="Edit";
		$inquiry_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select inquiry.*,usr.user_name from tbl_inquiry as inquiry
		left join users as usr on usr.user_id=inquiry.user_id
		where inquiry.inquiry_id=$inquiry_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$inquiry_date=date('d-m-Y',strtotime($rel['inquiry_date']));
		$closing_date='';
		if($rel['closing_date']!="1970-01-01" && $rel['closing_date']!="0000-00-00"){
			$closing_date=date('d-m-Y',strtotime($rel['closing_date']));
		}
		$cust_id=$rel['cust_id'];
		$user_name=$rel['user_name'];
		$opp_id=$rel['opp_id'];
	}
	else if(strpos($_SERVER[REQUEST_URI], "inquiry_ind")==true) {
		$mode="Add";
		$inquiry_date=date('d-m-Y');
		$closing_date='';
		$user_name=$_SESSION['user_name'];
		$task_due_date=date('d-m-Y h:i A');
		$task_type_id=16;
		$opp_id=5;
	}
	else {
		$mode="Add";
		$inquiry_date=date('d-m-Y');
		$closing_date='';
		$user_name=$_SESSION['user_name'];
		$task_due_date=date('d-m-Y h:i A');
		$task_type_id=16;
		$opp_id=5;
	}
	
$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));
//$a=array(53);
//echo show_user_ids($dbcon,$a);
//echo "123";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container" class="sidebar-closed"> <!--class="sidebar-closed"-->
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
			<h3><?=$mode .' '.$form?></h3>
			<div class="text-center">Owner : <strong><?=$user_name?></strong></div>
		</header>	
		<div class="">
			<?php 	
				$url = $_SERVER['HTTP_REFERER'];
				$infopage = basename($url);
				if($infopage=='crm_dashboard'){
					$back_link=ROOT.'crm_dashboard';
				}
				else{
					$back_link=ROOT.'inquiry_list';
				}
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
<div class="row">			
<div class="col-md-12">
<section class="panel">
<header class="panel-heading">
	New <?=$form?>
</header>	
<div class="panel-body">
	<form class="form-horizontal" role="form" id="inquiry_add" action="javascript:;" method="post" name="inquiry_add">
		<div class="row">
			<div class="clearfix"></div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-md-3 control-label">Inquiry No*</label>
					<div class="col-md-6">
						<input id="inquiry_no" name="inquiry_no" type="text" class="form-control" title="Enter Inquiry No" value="<?=$rel['inquiry_no']?>" placeholder="Inquiry No" readonly >		
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-md-3 control-label">Inquiry Date*</label>
					<div class="col-md-6"> 
						<input id="inquiry_date" name="inquiry_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$inquiry_date?>" placeholder="Inquiry Date" autocomplete="off">
					</div>
				</div>	
			</div>
			<div class="clearfix"></div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-md-3 control-label">Customer*</label>
					<div class="col-md-6"> 
						<select class="select2" id="cust_id" name="cust_id" onchange="load_cust_person(this.value);copy_inq_name();get_custwise_user(this.value);">
							<?=getcust_crm($dbcon,$cust_id)?>
						</select>
					</div>
					<?php if($mode=='Add'){?>
					<div class="col-md-1">
						<button type="button" id="addcust" data-toggle="modal" data-target="#bs-example-modal-lg"  class="btn btn-primary"><i class="fa fa-plus"></i></button>
					</div>
					<?php }?>
					<div class="col-md-1">
						<button type="button" class="btn btn-primary" data-original-title="View Customer Details" data-toggle="tooltip" data-placement="top" onclick="preview_cust_dtls()"><i class="fa fa-eye"></i></button>
					</div>
				</div>	
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-md-3 control-label">Contact Person</label>
					<div class="col-md-6"> 
						<select class="select2" id="c_con_id" name="c_con_id">
							<?=get_cust_contactperson($dbcon,$rel['c_con_id'],$cust_id);?>
						</select>
					</div>
					<div class="col-md-1">
						<button type="button" id="addcustper" onclick="open_cust_contact()" class="btn btn-primary"><i class="fa fa-plus"></i></button>
					</div>
					<div class="col-md-1">
						<button type="button" id="viewcustper" onclick="preview_cust_person()" title="View Contact Persons" class="btn btn-primary"><i class="fa fa-eye"></i></button>
					</div>
				</div>	
			</div>
			<!--<div class="col-md-6">
				<div class="form-group">
					<label class="col-md-3 control-label">Opportunity Name*</label>
					<div class="col-md-6"> 
						<input type="text" class="form-control" id="inquiry_name" name="inquiry_name" placeholder="Opportunity Name" value="<?=$rel['inquiry_name']?>">
					</div>
				</div>	
			</div>-->
			<div class="clearfix"></div>
			<!--<div class="col-md-4">
				<div class="form-group">
					<label class="col-md-4 control-label">Territory*</label>
					<div class="col-md-8">
						<select class="select2" id="t_id" name="t_id" onchange="load_ter_users(this.value);">
							<?php //=get_all_territory($dbcon,$rel['t_id']);?>
						</select>
					</div>
				</div>	
			</div>-->
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-md-4 control-label">Stage*</label>
					<div class="col-md-8"> 
						<select class="select2" id="opp_id" name="opp_id" onchange="load_opp_stage_prob(this.value);">
							<?=get_inquiry_stage($dbcon,$opp_id);?>
						</select>
					</div>
				</div>	
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-md-4 control-label">Probability(%)</label>
					<div class="col-md-8"> 
						<input type="text" id="stage_prob" name="stage_prob" class="form-control" value="<?=$rel['stage_prob']?>" readonly>
					</div>	
				</div>	
			</div>
		
		<div class="col-md-4">
			<div class="form-group">
				<label class="col-md-4 control-label">Sales Stage*</label>
				<div class="col-md-8"> 
					<select class="select2" id="sales_stage_id" name="sales_stage_id">
						<option value="">Choose Sales Stage</option>
						<?=get_master_category_dtl($dbcon,$rel['sales_stage_id'],7);//7:Sales Stage?>
					</select>
				</div>
			</div>	
		</div>
		<div class="clearfix"></div>
		<div class="col-md-4">
			<div class="form-group">
				<label class="col-md-4 control-label">Type*</label>
				<div class="col-md-8"> 
					<select class="select2" id="inquiry_type_id" name="inquiry_type_id">
						<option value="">Choose Inquiry Type</option>
						<?=get_master_category_dtl($dbcon,$rel['inquiry_type_id'],8);//8:Type?>
					</select>
				</div>
			</div>	
		</div>
		<div class="col-md-4">
			<div class="form-group">
				<label class="col-md-4 control-label">Source*</label>
				<div class="col-md-8"> 
					<select class="select2" id="rb_id" name="rb_id">
						<?=get_refer_by($dbcon,$rel['rb_id']);?>
					</select>
				</div>
			</div>	
		</div>
		<div class="col-md-4">
			<div class="form-group">
				<label class="col-md-4 control-label">Inquiry Category*</label>
				<div class="col-md-8">  
					<select class="select2" id="inquiry_cat_id" name="inquiry_cat_id">
						<option value="">Choose Inquiry Category</option>
						<?=get_master_category_dtl($dbcon,$rel['inquiry_cat_id'],9);//9:Category?>
					</select>
				</div>
			</div>	
		</div>
		<div class="clearfix"></div>
		<div class="col-md-4" style="display:none;">
			<div class="form-group">
				<label class="col-md-4 control-label">Closing Date</label>
				<div class="col-md-8">
					<input id="closing_date" name="closing_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$closing_date?>" placeholder="Closing Date" autocomplete="off">
				</div>
			</div>	
		</div>
		
<?php 	//Show Flp field only if add mode
if($mode=='Add'){
?>
		<div class="col-md-4">
			<div class="form-group">
				<label class="col-md-4 control-label">Task*</label>
				<div class="col-md-8">
				<?php //=get_master_category_dtl($dbcon,$task_type_id,10,"",1);//10:Task?>
					<select class="select2" id="task_type_id" name="task_type_id" title="Choose Task Type" required>
						<option value="">Choose Task Type</option>
						<?=get_master_category_dtl($dbcon,$task_type_id,10,"",1);//10:Task?>
					</select>
				</div>
			</div>	
		</div>
		<div class="col-md-4">
			<div class="form-group">
				<label class="col-md-4 control-label">Assign To*</label>
				<div class="col-md-8">
					<select class="select2" id="assign_user_ids" name="assign_user_ids[]" title="Choose Assign User" placeholder="Choose Assign User" multiple="multiple" required>
						<?php //=get_assign_users($dbcon, $rel['assign_user_ids'], " and user_id not in(".$_SESSION['user_id'].")");?>
						<?=get_assign_users($dbcon, $rel['assign_user_ids'], " and user_type in(2,8,9,21)");?>
					</select>
				</div>
			</div>	
		</div>
		<div class="col-md-4">
			<div class="form-group">
				<label class="col-md-4 control-label">Priority*</label>
				<div class="col-md-8">
					<select class="select2" id="task_priority_id" name="task_priority_id">
						<?=get_task_priority($dbcon,$rel['task_priority_id']);?>
					</select>
				</div>
			</div>	
		</div>
		<div class="clearfix"></div>
		<div class="col-md-4">
			<div class="form-group">
				<label class="col-md-4 control-label">Follow-Up Date*</label>
				<div class="col-md-8">
					<div data-date="<?=$task_due_date?>" class="input-group date form_datetime-meridian">
						  <input type="text" class="form-control" value="<?=$task_due_date?>" name="task_due_date" id="task_due_date" autocomplete="off">
						  <div class="input-group-btn">
							  <button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
						  </div>
					</div>
				</div>
			</div>	
		</div>	
		
<?php }?>
	
		<div class="clearfix"></div>
	<hr/>
	<div class="col-md-12">
		<div class="form-group" style="margin-top:20px;overflow-x:auto;">
			<table class="display table table-bordered table-striped">
			  <thead>
				<tr>
					<th width="25%" class="text-center">Product Name</th>
					<!--<th width="15%" class="text-center">Product Category</th>-->
					<!--<th width="15%" class="text-center">Product Group</th>-->
					<!--<th width="10%" class="text-center">Level</th>-->
					<th width="" class="text-center">Quantity</th>
					<th width="" class="text-center">Unit</th>
					<th width="" class="text-center">Rate</th>
					<th width="" class="text-center">Amount</th>
					<th width="2%" class="text-center">Action</th>					  
				</tr>
			  </thead>
			  <tbody>
				<tr>
				<td>
					<select class="select2" id="product_id" name="product_id" onchange="load_product_dtls(this.value)">
						<?=getproduct_typewise($dbcon,"","0");?>
					</select>
				</td>
				<!--<td>
					<select class="select2" id="cat_id" name="cat_id">
						<?php //=get_all_category($dbcon,"");?>
					</select>
				</td>-->
				<!--<td>
					<select class="select2" id="pg_id" name="pg_id">
						<?php //=get_product_group($dbcon,"");?>
					</select>
				</td>-->
				<!--<td>
					<select class="select2" id="level_id" name="level_id">
						<!--<option value="">Choose Level</option>--
						<option value="1">Level 1</option>
					</select>
				</td>-->
				<td>
					<input type="number" min="0" class="form-control" id="product_qty" name="product_qty" onkeyup="get_amount();" value="">
				</td>
				<td>
					<select class="select2" name="unitid" id="unitid" title="Select Unit">
						<?=getunit($dbcon,0);?>
					</select>
				</td>
				<td>
					<input type="number" min="0" class="form-control" id="product_rate" name="product_rate" onkeyup="get_amount();" value="">
				</td>
				<td>
					<input type="number" min="0" class="form-control" id="product_amount" name="product_amount" value="" readonly>
				</td>
				<td rowspan="2" style="vertical-align:middle;">
					<input type="hidden" id="edit_id" name="edit_id" value="">
					<button type="button" class="btn btn-primary" id="inq_trn_btn" onclick="add_field()">Add</button>
				</td>
				</tr>
				<tr>
					<td colspan="2">
						<textarea class="form-control" id="product_desc" name="product_desc" placeholder="Enter Product Description" style="resize:both;"></textarea>
					</td>
					<td colspan="3">
						<textarea class="form-control" id="product_spec" name="product_spec" placeholder="Enter Specification" style="resize:both;"></textarea>
					</td>
				</tr>
			  </tbody>
			</table>
		</div>
		<hr/>
		<div class="form-group" id="inq_trn_div" style="margin-top:20px;overflow-x:scroll;"></div>
	</div>
	<div class="clearfix"></div>
	
		<div class="col-md-12">
		<div class="col-md-6">
			<div class="form-group">
				<label class="col-md-4 control-label">Currency</label>
				<div class="col-md-8">  
					<select class="select2" id="currency_id" name="currency_id">
						<?=get_org_currency($dbcon,$rel['currency_id'])?>
					</select>
				</div>
			</div>	
		</div>
		<div class="col-md-6">
			<div class="form-group">
				<label class="col-md-4 control-label">Total</label>
				<div class="col-md-8">
					<input type="number" min="0" id="g_total" name="g_total" class="form-control" value="<?=$rel['g_total']?>" readonly>
				</div>
			</div>	
		</div>
		</div>	
	<div class="clearfix"></div>
	<hr/>
		<div class="clearfix"></div>
		<!--tab start--> 
<div class="col-md-12">
	<div class="card">
		<ul class="nav nav-tabs" id="my_tab_id" role="tablist"> 
			<li role="presentation" id="tab1" class="<?=($mode=='Add')?'active':''?>"><a href="#remark-section" aria-controls="remark-section" role="tab" data-toggle="tab">Remark</a></li>
			<li role="presentation" id="tab2"><a href="#attch-section" aria-controls="attch-section" role="tab" data-toggle="tab">Attachments</a></li>
			<li role="presentation" id="tab3"><a href="#note-section" aria-controls="note-section" role="tab" data-toggle="tab">Notes</a></li>
			<li role="presentation" id="tab4" class="<?=($mode=='Edit')?'active':''?>"><a href="#task-section" aria-controls="task-section" role="tab" data-toggle="tab">History</a></li>
		</ul> 
		<!-- Tab panes -->
		<div class="tab-content"> 
			<!-- Remaks Tab Start -->
			<div role="tabpanel" class="tab-pane <?=($mode=='Add')?'active':''?>" id="remark-section">
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
						<div class="col-md-12">
							<textarea id="inq_desc" name="inq_desc" class="form-control" rows="3" style="resize:both;"><?=$rel['inq_desc']?></textarea> 
						</div>
					</div> 
				</div> 
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Competition Status</label>
						<div class="col-md-12">
							<textarea id="inq_comp_desc" name="inq_comp_desc" class="form-control" rows="3" style="resize:both;"><?=$rel['inq_comp_desc']?></textarea> 
						</div>
					</div> 
				</div> 
			</div> 
			<!-- Attachments Tab Start -->
			<div role="tabpanel" class="tab-pane" id="attch-section">
				<div class="form-group" style="margin-top:20px;">
					<table class="display table table-bordered table-striped">
					  <thead>
						<tr>
							<th width="40%" class="text-center">Document Name</th>
							<th width="50%" class="text-center">Upload Document</th>
							<th width="10%" class="text-center">Action</th>					  
						</tr>
					  </thead>
					  <tbody>
						<tr>
							<td>
								<input type="text" class="form-control" id="inq_attch_doc_name" name="inq_attch_doc_name" value="" placeholder="Document Name">
							</td>
							<td>
								<input type="file" class="form-control" id="inq_attch_file" name="inq_attch_file">
							</td>
							<td>
								<button type="button" class="btn btn-primary" id="inq_attch_btn" onclick="add_inq_attch_field()">Add</button>
							</td>
						</tr>
					  </tbody>
					</table>
				</div> 
				<div class="form-group" style="margin-top:20px;" id="inq_attch_trn_div"></div> 
			</div> 
			<!-- Note Tab Start -->
			<div role="tabpanel" class="tab-pane" id="note-section">
				<div class="form-group" style="margin-top:20px;">
					<table class="display table table-bordered table-striped">
					  <thead>
						<tr>
							<th width="30%" class="text-center">Title</th>
							<th width="60%" class="text-center">Description</th>
							<th width="10%" class="text-center">Action</th>					  
						</tr>
					  </thead>
					  <tbody>
						<tr>
							<td>
								<input type="text" class="form-control" id="inq_note_title" name="inq_note_title" value="" placeholder="Title">
							</td>
							<td>
								<textarea class="form-control" id="inq_note_desc" name="inq_note_desc" placeholder="Description" style="resize:both;"></textarea>
							</td>
							<td>
								<input type="hidden" id="edit_inq_noteid" name="edit_inq_noteid" value="">
								<button type="button" class="btn btn-primary" id="inq_note_btn" onclick="add_inq_note_field()">Add</button>
							</td>
						</tr>
					  </tbody>
					</table>
				</div> 
				
				<div class="form-group" style="margin-top:20px;" id="inq_notes_trn_div"></div> 
			</div> 
			<!-- Task Tab Start -->
			<div role="tabpanel" class="tab-pane <?=($mode=='Edit')?'active':''?>" id="task-section">
				<div class="form-group" style="margin-top:20px;">
					<div class="clearfix"></div>
				<?php if($mode=='Edit'){?>
					<div class="col-md-2">
						<a onclick="setFormSubmitting();" href="<?=ROOT.'task_add/'.$rel['inquiry_id']?>" type="button" class="btn btn-primary" ><i class="fa fa-plus"></i> Add Follow-Up</a>
					</div>
					<div class="col-md-2">
						<a onclick="setFormSubmitting();" href="<?=ROOT.'appointment_add/'.$rel['inquiry_id']?>" type="button" class="btn btn-info"><i class="fa fa-plus"></i> Appointment</a>
					</div>
					<div class="clearfix"></div>
					<div class="clearfix" style="margin-top:10px;"></div>
					<div class="col-md-2">
						<strong>Quotation Created :</strong>
					</div>
					<div class="col-md-5">
						<table class="display table table-bordered table-striped">
							<tr>
								<th>Quotation No</th>
								<th>Quotation Date</th>
								<th>Approve Status</th>
								<th>Action</th>
							</tr>
					<?php 
						$get_quot_qry="select quotation_id,quotation_no,quotation_date ,approve_status from tbl_quotation where inquiry_id=".$rel['inquiry_id'];
						$get_quot_qry_rs=$dbcon->query($get_quot_qry);
						if(mysqli_num_rows($get_quot_qry_rs)){
						while($get_quot_rel=mysqli_fetch_assoc($get_quot_qry_rs)){
					?>
						<tr>
							<td><?=$get_quot_rel['quotation_no']?></td>
							<td><?=date("d-m-Y", strtotime($get_quot_rel['quotation_date']))?></td>
							<td>
							<?php 
								if($get_quot_rel['approve_status']=='1'){
									echo '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Authorized</div>';
								}
								else{
									echo '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div>';
								}
							?>
							</td>
							<td>
								<a onclick="setFormSubmitting();" href="<?=ROOT.'quotation_print/'.$get_quot_rel['quotation_id']?>" type="button" class="btn btn-primary" target="_blank"> <i class="fa fa-eye"></i> View</a>
							</td>
						</tr>
						<?php	}
							}
							else{ ?>
							<tr>
								<td colspan="3" class="text-center">No Data Found !!!</td>
							</tr>
						<?php 	}	?>	
						</table>
					</div>
				<?php }?>
					<div class="clearfix"></div>
					<div class="col-md-12 margin_row" style="margin-top: 10px;">
					<table class="display table table-bordered table-striped">
	<?php 
		$get_task_qry="select tsk.*,sub.mcd_name as subject,usr.user_name,prior.task_priority_name from tbl_task as tsk 
		left join tbl_master_category_detail as sub on sub.mcd_id=tsk.task_type_id
		left join users as usr on usr.user_id=tsk.user_id
		left join task_priority_mst as prior on prior.task_priority_id=tsk.task_priority_id
		where tsk.task_status!=2 and tsk.task_rel_id=5 and tsk.inquiry_id=".$rel['inquiry_id']." order by tsk.create_date DESC";
		$get_task_qry_rs=$dbcon->query($get_task_qry);
		while($task_rel=mysqli_fetch_assoc($get_task_qry_rs)){
			if($task_rel['entry_type']=='1'){//Task 
			
			$task_completion_date='';$task_due_date='';
			if($task_rel['task_completion_date']!="1970-01-01 00:00:00" && $task_rel['task_completion_date']!="0000-00-00 00:00:00"){
				$task_completion_date=date('d-m-Y h:i A',strtotime($task_rel['task_completion_date']));
			}
			if($task_rel['task_due_date']!="1970-01-01 00:00:00" && $task_rel['task_due_date']!="0000-00-00 00:00:00"){
				$task_due_date=date('d-m-Y h:i A',strtotime($task_rel['task_due_date']));
			}
			
			//Get Task Timing
			
	?>
			<tr>
				<td width="25%" colspan="2" class="text-left">
					<strong>Task</strong>
				</td>
			<?php 	
				/*$tsk_type="";
				$tsk_due_time=strtotime($task_rel['task_due_date']);
				
				
				if($task_rel['task_status']=='1'){ 
					$cur_time=strtotime($task_rel['task_completion_date']);
					if($tsk_due_time<$cur_time){
						$tsk_type="<label style='background:#d9534f;'>(Delayed)</label>";
					}
				?>
				<td width="25%" class="text-center btn-success">Completed <?=$tsk_type?></td>
			<?php 	}
				else{
					$cur_time=strtotime(date('Y-m-d H:i:s'));
					if($tsk_due_time<$cur_time){
						$tsk_type="<label style='background:#d9534f;'>(Delayed)</label>";
					}	
				?>
				<td width="25%" class="text-center btn-warning">Pending <?=$tsk_type?></td>
			<?php 	}*/?>
				<td width="25%" class="text-left">
					<strong>Completion Date:</strong> <?=$task_completion_date?>
				</td>
				<td width="25%" class="text-left">
					<strong>Create Date:</strong> <?=date('d-m-Y h:i A',strtotime($task_rel['create_date']));?>
				</td>
			</tr>
			<tr>
				<td class="text-left">
					<strong>Subject:</strong> <?=$task_rel['subject']?>
				</td>
				<td class="text-left">
					<strong>Owner:</strong> <?=$task_rel['user_name']?>
				</td>
				<td class="text-left">
					<strong>Priority:</strong> <?=$task_rel['task_priority_name']?>
				</td>
				<td class="text-left">
					<strong>Due Date:</strong> <?=$task_due_date?>
				</td>
			</tr>
			<tr>
				<td colspan="4" class="text-left">
					<strong>Owners Remark :</strong> <?=nl2br($task_rel['task_remark'])?>
				</td>
			</tr>
			<tr>
				<td colspan="4" class="text-left" style="border-bottom: 1px solid #000 !important;">
					<?php 
						$task_flp_qry="select flp.*,usr.user_name from tbl_followup as flp 
						left join users as usr on usr.user_id=flp.user_id
						where flp.flp_status=0 and flp.task_id=".$task_rel['task_id']."";
						$task_flp_qry_rs=$dbcon->query($task_flp_qry);
						if(mysqli_num_rows($task_flp_qry_rs)){
					?>
					<table class="display table table-bordered table-striped">
						<thead>
							<th>User</th>
							<th>Remarks Date</th>
							<th>Remarks</th>
						</thead>
						<tbody>
						<?php 
							while($flp_rel=mysqli_fetch_assoc($task_flp_qry_rs)){
						?>
							<td width="20%"><?=$flp_rel['user_name']?></td>
							<td width="20%"><?=date("d-M-Y h:i A",strtotime($flp_rel['flp_date']))?></td>
							<td width="60%"><?=$flp_rel['task_flp_remark']?></td>
						<?php 
							}
						?>	
						</tbody>
					</table>
					<?php}?>
				</td>
			</tr>
		<?php 	}
		else if($task_rel['entry_type']=='2'){//Appointment
			$appointment_start_time='';$appointment_end_time='';
			if($task_rel['appointment_start_time']!="1970-01-01 00:00:00" && $task_rel['appointment_start_time']!="0000-00-00 00:00:00"){
				$appointment_start_time=date('d-m-Y h:i A',strtotime($task_rel['appointment_start_time']));
			}
			if($task_rel['appointment_end_time']!="1970-01-01 00:00:00" && $task_rel['appointment_end_time']!="0000-00-00 00:00:00"){
				$appointment_end_time=date('d-m-Y h:i A',strtotime($task_rel['appointment_end_time']));
			}
		?>
			<tr>
				<td width="25%" class="text-left">
					<strong>Appointment</strong>
				</td>
				<td width="25%" class="text-left">
					Subject: <?=$task_rel['appointment_subject']?>
				</td>
				<td width="25%" class="text-left">
					Location: <?=$task_rel['task_location']?>
				</td>
				<td width="25%" class="text-left">
					Owner: <?=$task_rel['user_name']?>
				</td>
			<tr>
			<tr>
				<td colspan="2" class="text-left">
					Start Time: <?=$appointment_start_time?>
				</td>
				<td colspan="2" class="text-left">
					End Time: <?=$appointment_end_time?>
				</td>
			<tr>
			<tr>
				<td colspan="4" class="text-left">
					Owner Remarks : <?=nl2br($task_rel['task_remark'])?>
				</td>
			</tr>
			<tr>
				<td colspan="4" class="text-left" style="border-bottom: 1px solid #000 !important;"></td>
			</tr>
		<?php }?>	
	<?php 	}?>				
					</table>
					</div>
				</div> 
				
			</div>               
		</div>
	</div>      		
</div>      		
	<!--tabs end-->	
		<div class="clearfix"></div>
			
		</div>
		<div class="clearfix"></div>
		<div class="col-md-12 text-center">
			<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
			<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>	
		</div>	
	</div>
</div><!--Vendor row end-->	
<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
<input type='hidden' name='eid' id='eid' value='<?=$inquiry_id?>' />

</form>
</div>	
</section>
</div>
</div>
<!--state overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->
	
<?php include_once('../include/add_cust.php');?>
<?php include_once('../include/add_person.php');?>
<?php include_once('../include/preview_cust_person_dtl.php');?>
<?php include_once('../include/preview_cust_dtls.php');?>
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script>
var formSubmitting = false;
var setFormSubmitting = function() { formSubmitting = true; };
window.onload = function() {
    window.addEventListener("beforeunload", function (e) {
        if (formSubmitting) {
            return undefined;
        }

        var confirmationMessage = 'You sure you want to leave? ';

        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
    });
};
</script>
<script src="<?=ROOT.CRM_ROOT?>js/app/inquiry.js?<?=time()?>"></script>
<script src="<?=ROOT.CRM_ROOT?>js/app/customer.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
$('#cust_id').select2({
	minimumInputLength: 2,
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
/*$(function() { 
	$('#inquiry_date').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
	<?php if($mode=='Add')
	{?>
	,startDate: 'd'//don't allow today and past dates
	<?php }?>
	});
});*/
$(function(){
	setTimeout(function(){ $('#sidebar > ul').hide(); }, 1000);
	$('#party_type_ven_div').hide();
});
<?php if($mode=='Add'){?>
load_def_inq_no();
load_opp_stage_prob(<?=$opp_id?>);
<?php }
else{?>
$('#cust_id').select2('readonly', true);
<?php }?>
load_state(<?=$countryid?>,'c_add_state','');

/*$("#product_id").select2({
	width: '100%',
	matcher: function(params, data) {
        // If there are no search terms, return all of the data
        if ($.trim(params.term) === '') { return data; }

        // Do not display the item if there is no 'text' property
        if (typeof data.text === 'undefined') { return null; }

        // `params.term` is the user's search term
        // `data.id` should be checked against
        // `data.text` should be checked against
        var q = params.term.toLowerCase();
        if (data.text.toLowerCase().indexOf(q) > -1 || data.id.toLowerCase().indexOf(q) > -1) {
            return $.extend({}, data, true);
        }

        // Return `null` if the term should not be displayed
        return null;
    }
});*/
</script>
</body>
</html>