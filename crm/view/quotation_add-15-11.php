<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$form="Quotation";
$countryid='101';
$stateid='1';
$cityid='1';
$quot_remark = ' ';

if(strpos($_SERVER[REQUEST_URI], "quotation_edit")==true) {
	$mode="Edit";
	$viewmode="Edit";
	$quotation_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select quot.*,usr.user_name from tbl_quotation as quot
	left join users as usr on usr.user_id=quot.user_id
	where quot.quotation_id=$quotation_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$quotation_date=date('d-m-Y',strtotime($rel['quotation_date']));
	$user_name=$rel['user_name'];
	$cust_id=$rel['cust_id'];
	$inquiry_id=$rel['inquiry_id'];
	$quot_type=$rel['quot_type'];
	$quot_subject=$rel['quot_subject'];
	$quot_remark = $rel['quot_remark'];
	$c_con_id=$rel['c_con_id'];
	$quotation_valid_date='';
	if($rel['quotation_valid_date']!="1970-01-01" && $rel['quotation_valid_date']!="0000-00-00"){
		$quotation_valid_date=date('d-m-Y',strtotime($rel['quotation_valid_date']));
	}
}
else {
	$mode="Add";
	$viewmode="Add";
	$quotation_date=date('d-m-Y');
	$quotation_valid_date=date('d-m-Y');
	$user_name=$_SESSION['user_name'];
	$task_type_id=21;
	$task_due_date=date('d-m-Y h:i A');
	$cust_id='';$inquiry_id='';$quot_subject='';$c_con_id='';$quot_type=0;
	if(strpos($_SERVER[REQUEST_URI], "inq_to_quot")==true) {
		$inq_to_quot=true;
		$inquiry_id=$dbcon->real_escape_string($_REQUEST['id']);
		$inq_qry="select inq.*,(SELECT group_concat(assign_user_ids) FROM `tbl_task` where task_status!=2 and inquiry_id=inq.inquiry_id) as assign_user_ids from tbl_inquiry as inq
		where inquiry_id=".$inquiry_id;
		$inq_rel=mysqli_fetch_assoc($dbcon->query($inq_qry));
		
		$inq_qry1="select * from tbl_task as task where inquiry_id=".$inquiry_id." and task_status=0";
		$inq_rel1=mysqli_fetch_assoc($dbcon->query($inq_qry1));
		
		$cust_id=$inq_rel['cust_id'];
		$c_con_id=$inq_rel['c_con_id'];
		$assign_user_ids=array_unique(explode(",",$inq_rel['assign_user_ids']));
		unset( $assign_user_ids[array_search( $_SESSION['user_id'], $assign_user_ids )] );
		
			//$assign_user_ids=implode(",",$assign_user_ids);
		$assign_user_ids=$inq_rel1['assign_user_ids'];
	}
		else if($dbcon->real_escape_string($_REQUEST['id'])){//Check Revise Mode
			$prev_quotation_id=$dbcon->real_escape_string($_REQUEST['id']);
			$viewmode="Revise";
			$revise_status=true;
			$query="select quot.*,usr.user_name from tbl_quotation as quot
			left join users as usr on usr.user_id=quot.user_id
			where quot.quotation_id=$prev_quotation_id";
			$rel=mysqli_fetch_assoc($dbcon->query($query));
			$cust_id=$rel['cust_id'];
			$inquiry_id=$rel['inquiry_id'];
			$quot_type=$rel['quot_type'];
			$start_quotation_id=$rel['start_quotation_id'];
			$quot_subject=$rel['quot_subject'];
			$c_con_id=$rel['c_con_id'];
			$quot_remark = $rel['quot_remark'];
			//Get Prev Quotation user for assign process
			$inq_qry="select inq.*,(SELECT group_concat(assign_user_ids) FROM `tbl_task` where task_status!=2 and inquiry_id=inq.inquiry_id) as assign_user_ids from tbl_inquiry as inq
			where inquiry_id=".$inquiry_id;
			$inq_rel=mysqli_fetch_assoc($dbcon->query($inq_qry));
			
			$inq_qry1="select * from tbl_task as task where inquiry_id=".$inquiry_id." and task_status=0";
			$inq_rel1=mysqli_fetch_assoc($dbcon->query($inq_qry1));
			
			$cust_id=$inq_rel['cust_id'];
			$c_con_id=$inq_rel['c_con_id'];
			$assign_user_ids=array_unique(explode(",",$inq_rel['assign_user_ids']));
			unset( $assign_user_ids[array_search( $_SESSION['user_id'], $assign_user_ids )] );
			//$assign_user_ids=implode(",",$assign_user_ids);
			$assign_user_ids=$inq_rel1['assign_user_ids'];
		}
		else{
			$cust_id=$_SESSION['def_quot_cust_id'];
			$inquiry_id=$_SESSION['def_quot_inquiry_id'];
			$quot_subject=$_SESSION['def_quot_subject'];
			$c_con_id=$_SESSION['def_c_con_id'];
		}
	}
	
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
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
									<h3><?=$viewmode .' '.$form?></h3>
									<div class="text-center">Owner : <strong><?=$user_name?></strong></div>
								</header>	
								<div class="">
									<?	
									$url = $_SERVER['HTTP_REFERER'];
									$infopage = basename($url);
									if($infopage=='crm_dashboard'){
										$back_link=ROOT.'crm_dashboard';
									}
									else{
										$back_link=ROOT.'quotation_list';
									}
									?>
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.'quotation_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="quotation_add" action="javascript:;" method="post" name="quotation_add">
										<div class="row">
											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Quotation No*</label>
													<div class="col-md-6">
														<input id="quotation_no" name="quotation_no" type="text" class="form-control" title="Enter Quotation No" value="<?=$rel['quotation_no']?>" placeholder="Quotation No" readonly >		
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Quotation Date*</label>
													<div class="col-md-6"> 
														<input id="quotation_date" name="quotation_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$quotation_date?>" placeholder="Quotation Date">
													</div>
												</div>	
											</div>
											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Customer*</label>
													<div class="col-md-6"> 
														<select class="select2" id="cust_id" name="cust_id" onchange="load_cust_person(this.value);load_cust_inq(this.value);">
															<?=getcust_crm($dbcon,$cust_id)?>
														</select>
													</div>
												</div>	
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Contact Person*</label>
													<div class="col-md-6"> 
														<select class="select2" id="c_con_id" name="c_con_id">
															<?=get_cust_contactperson($dbcon,$c_con_id,$cust_id);?>
														</select>
													</div>
													<div class="col-md-1">
														<button type="button" id="addcustper" onclick="open_cust_contact()" class="btn btn-primary"><i class="fa fa-plus"></i></button>
													</div>
												</div>	
											</div>
											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Inquiry*</label>
													<div class="col-md-6"> 
														<select class="select2" id="inquiry_id" name="inquiry_id" onchange="load_inq_pro(this.value);">
															<?=get_cust_inq($dbcon,$inquiry_id,$cust_id);?>
														</select>
													</div>
												</div>	
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Subject*</label>
													<div class="col-md-6"> 
														<input type="text" class="form-control" id="quot_subject" name="quot_subject" placeholder="Subject" value="<?=$quot_subject?>">
													</div>
												</div>	
											</div>
											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Quotation Type</label>
													<div class="col-md-6"> 
														<label class="col-md-5 col-sm-6" style="font-weight:bold;"><input type="radio" id="quot_type_domestic" name="quot_type" style="width: 15px;height: 15px;" <?if($mode=='Add'){?>onclick="load_typeswise_terms(this.value,'');"<?}?> value="0" <?=($quot_type!='1')?'checked':''?>> Domestic</label>
														<label class="col-md-6 col-sm-6 " style="font-weight:bold;"><input type="radio" id="quot_type_export" name="quot_type" style="width: 15px;height: 15px;" <?if($mode=='Add'){?>onclick="load_typeswise_terms(this.value,'');"<?}?> value="1" <?=($quot_type=='1')?'checked':''?>> Export</label>
													</div>
												</div>	
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Valid Till</label>
													<div class="col-md-6"> 
														<input id="quotation_valid_date" name="quotation_valid_date" type="text" class="form-control default-date-picker  valid" title="Date" value="<?=$quotation_valid_date?>" placeholder="Quotation Date">
													</div>
												</div>	
											</div>
											<div class="clearfix"></div>
											<!--<div class="col-md-6">-->
												<!--	<div class="form-group">-->
													<!--		<label class="col-md-3 control-label">Reference</label>-->
													<!--		<div class="col-md-6"> -->
														<!--			<input type="text" id="quotation_ref" name="quotation_ref" class="form-control" title="Reference" value="<?=$rel['quotation_ref']?>" placeholder="Reference">-->
														<!--		</div>-->
														<!--	</div>	-->
														<!--</div>-->
														<!--<div class="col-md-6">-->
															<!--	<div class="form-group">-->
																<!--		<label class="col-md-3 control-label">Kind Attn</label>-->
																<!--		<div class="col-md-6"> -->
																	<!--			<input type="text" id="kind_attn" name="kind_attn" class="form-control" title="Kind Attn" value="<?=$rel['kind_attn']?>" placeholder="Kind Attn">-->
																	<!--		</div>-->
																	<!--	</div>	-->
																	<!--</div>-->
																	
<?	//Show Flp field only if add mode
if($mode=='Add'){
	?>
	<div class="col-md-6">
		<div class="form-group">
			<label class="col-md-3 control-label">Task*</label>
			<div class="col-md-6">
				<select class="select2" id="task_type_id" name="task_type_id" title="Choose Task Type" required>
					<option value="">Choose Task Type</option>
					<?=get_master_category_dtl($dbcon,$task_type_id,10);//10:Task?>
				</select>
			</div>
		</div>	
	</div>
	
	<div class="clearfix"></div>
	<div class="col-md-4">
		<div class="form-group">
			<label class="col-md-4 control-label">Assign To*</label>
			<div class="col-md-8">
				<select class="select2" id="assign_user_ids" name="assign_user_ids[]" title="Choose Assign User" placeholder="Choose Assign User" multiple="multiple" required>
					<?//=get_assign_users($dbcon, $rel['assign_user_ids'], " and user_id not in(".$_SESSION['user_id'].")");?>
					<?=get_assign_users($dbcon, $assign_user_ids, "");?>
				</select>
			</div>
		</div>	
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label class="col-md-4 control-label">Priority*</label>
			<div class="col-md-8">
				<select class="select2" id="task_priority_id" name="task_priority_id">
					<?=get_task_priority($dbcon,"");?>
				</select>
			</div>
		</div>	
	</div>
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
	<?}?>
	<div class="clearfix"></div>
	<!--<div class="col-md-6">-->
		<!--	<div class="form-group">-->
			<!--		<label class="col-md-2 control-label">Greeting</label>-->
			<!--		<div class="col-md-10">-->
				<!--			<textarea class="form-control" id="quatation_greeting" name="quatation_greeting" placeholder="Enter Quatation Greeting"><?if($mode=='Add'){ echo $set_head['quotation_greeting'];}else{ echo $rel['quatation_greeting']; }?></textarea>-->
				<!--		</div>-->
				<!--	</div>-->
				<!--</div>-->
				<!--<div class="col-md-6">-->
					<!--	<div class="form-group">-->
						<!--		<label class="col-md-2 control-label">Attached Part</label>-->
						<!--		<div class="col-md-10">-->
							<!--			<textarea class="form-control" id="attach_part" name="attach_part" placeholder="Enter Attached Part"><?if($mode=='Add'){ echo $set_head['attach_part'];}else{ echo $rel['attach_part']; }?></textarea>-->
							<!--		</div>-->
							<!--	</div>-->
							<!--</div>-->

							<div class="clearfix"></div>
							<hr/>
							<div class="col-md-12">
								<div class="form-group" style="margin-top:20px;overflow-x:scroll;">
									<table class="display table table-bordered table-striped">
										<thead>
											<tr>
												<th width="25%" class="text-center">Product Name</th>
												<!--<th width="5%" class="text-center">Level</th>-->
												<th width="" class="text-center">Quantity</th>
												<th width="" class="text-center">Rate</th>
												<th width="" class="text-center">Unit</th>
												<th width="" class="text-center">Discount</th>
												<th width="" class="text-center">Taxable Value</th>
												<th width="" class="text-center">Tax</th>
												<th width="" class="text-center">Amount</th>
												<th width="2%" class="text-center">Action</th>					  
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>
													<select class="select2" id="product_id" name="product_id" onchange="load_product_dtls(this.value);">
														<?=getproduct_typewise($dbcon,"","");?>
													</select>
												</td>
					<!--<td>
						<select class="select2" id="level_id" name="level_id">
							<!--<option value="">Choose Level</option>--
							<option value="1">Level 1</option>
						</select>
					</td>-->
					<td>
						<input type="number" min="0" class="form-control" id="product_qty" name="product_qty" onkeyup="get_amount();get_discount('per');" value="">
					</td>
					<td>
						<input type="number" min="0" class="form-control" id="product_rate" name="product_rate" onkeyup="get_amount();get_discount('per');" value="">
					</td>
					<td>
						<select class="select2" name="unitid" id="unitid" title="Select Unit">
							<?=getunit($dbcon,0);?>
						</select>
					</td>
					<td>
						<input type="number" title="Enter Discount (In value)" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control" placeholder="in value"/>
					</td>
					<td>
						<input type="number" min="0" class="form-control" id="product_amount" name="product_amount" value="" readonly>
					</td>
					<td>
						<select class="form-control" name="formulaid" id="formulaid" onChange="get_amount();">
							<?=getformula($dbcon,$rel['formulaid']);?>
						</select>
					</td>
					<td>
						<input type="number" min="0" class="form-control" id="product_total" name="product_total" value="" readonly>
					</td>
					<td rowspan="2" style="vertical-align:middle;">
						<input type="hidden" id="edit_id" name="edit_id" value="">
						
						<button type="button" style="display:none;" class="btn btn-primary serv" id="quot_trn_btn" onclick="add_field()">Add</button>
						
						<button type="button" title="Add Row Product" name="img_product" id="img_product" onclick="choose_product_img();" class="btn btn-info prod">Choose Image and Edit</button>
					</td>
				</tr>
				<tr>
					<td>
						<textarea class="form-control" id="product_desc" name="product_desc" placeholder="Enter Product Description" style="resize:both;"></textarea>
					</td>
					<td colspan="3">
						<textarea class="form-control" id="product_spec" name="product_spec" placeholder="Enter Specification" style="resize:both;display:none;"></textarea>
					</td>
					<td>
						<input type="number"  title="Enter Discount (In %)" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control" placeholder="in %" max="100"/>
					</td>
					<td></td>
					<td></td>
					<td>
						<!--<strong>Extra At Actual :</strong>
							<input type="checkbox" class="form-control" id="act_amt_flag" name="act_amt_flag" value="1">-->
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="form-group" id="quot_trn_div" style="margin-top:20px;overflow-x:scroll;"></div>
	</div>
	<div class="clearfix"></div>
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
			<label class="col-md-4 control-label">Grand Total</label>
			<div class="col-md-8">  
				<input type="number" min="0" id="g_total" name="g_total" class="form-control" value="<?=$rel['g_total']?>" readonly>
			</div>
		</div>	
	</div>
	<hr/>
	<div class="clearfix"></div>
	<!--tab start--> 
	<div class="col-md-12">
		<div class="card">
			<ul class="nav nav-tabs" id="my_tab_id" role="tablist"> 
				<li role="presentation" id="tab2" class="active"><a href="#terms-section" aria-controls="terms-section" role="tab" data-toggle="tab">Terms And Condition</a></li>
				<li role="presentation" id="tab1"><a href="#remark-section" aria-controls="remark-section" role="tab" data-toggle="tab">Remark</a></li>
				<li role="presentation" id="tab3" style="display:none;"><a href="#annexure-section" aria-controls="annexure-section" role="tab" data-toggle="tab">Annexure</a></li>
				<li role="presentation" id="tab5" style="display:none;"><a href="#dfd-section" aria-controls="dfd-section" role="tab" data-toggle="tab">Annex DFD</a></li>
				<li role="presentation" id="tab4"><a href="#address-section" aria-controls="address-section" role="tab" data-toggle="tab">Address</a></li>
			</ul>
			<!-- Tab panes -->
			<div class="tab-content"> 
				<!-- Remaks Tab Start -->
				<div role="tabpanel" class="tab-pane" id="remark-section">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
							<div class="col-md-12">
								<textarea id="quot_remark" name="quot_remark" class="form-control" rows="3" style="resize:both;"><?=$quot_remark?></textarea> 
							</div>
						</div> 
					</div>
				</div>
				<!-- Terms Tab Start -->
				<div role="tabpanel" class="tab-pane active" id="terms-section">
<!--				<div class="col-md-12" style="padding-top: 15px;">
					<center>
						<button type="button" class="btn btn-primary " id="" onclick="check_field()">Check All</button>
					</center>
				</div>-->
				<div class="form-group" style="padding-top: 75px;" id="quot_terms_cond_div">
					
				</div>  
			</div>
			<!-- Annexure Tab Start -->
			<div role="tabpanel" class="tab-pane" id="annexure-section" style="display:none;">
				<div class="col-md-12">
					<div class="form-group">
						<label class="col-md-4 control-label text-left">Choose Annexure</label>
						<div class="col-md-4">
							<select class="select2" id="an_id" name="an_id" onchange="load_annex_content(this.value);">
								<?=get_annexure_types($dbcon,$rel['an_id']);?>
							</select>
						</div>
					</div> 
				</div> 
				<div class="col-md-12">
					<div class="form-group">
						<label class="col-md-12 control-label text-center" style="text-align:left;font-weight:bold;">Annexure Content</label>
						<div class="col-md-12">
							<textarea id="quot_annex_content" name="quot_annex_content" class="form-control"><?=$rel['quot_annex_content']?></textarea>
						</div>
					</div> 
				</div> 
			</div>
			<!-- DFD Tab Start -->
			<div role="tabpanel" class="tab-pane" id="dfd-section" style="display:none;">
				<div class="col-md-8 col-md-offset-2">
					<div class="form-group" style="margin-top:20px;">
						<table class="display table table-bordered table-striped">
							<thead>
								<tr>
									<th width="50%" class="text-center">Upload Image</th>
									<th width="10%" class="text-center">Action</th>					  
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<input type="file" class="form-control" id="dfd_attch_file" name="dfd_attch_file" accept="image/*">
									</td>
									<td>
										<button type="button" class="btn btn-primary" id="dfd_attch_btn" onclick="add_dfd_attch_field()">Add</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div> 
					<div class="form-group" style="margin-top:20px;" id="dfd_attch_trn_div"></div> 
				</div> 
			</div>
			<!-- Address Tab Start -->
			<div role="tabpanel" class="tab-pane" id="address-section">
				<div class="col-md-12 text-center">
					<div class="form-group">
						<div class="col-md-12">
							<button type="button" class="btn btn-primary" onclick="view_cust_address()">View Address</button>
						</div>
					</div> 
				</div> 
				<div class="col-md-12">
					<div class="form-group">
						<div class="col-md-12">
							<textarea id="quot_address" name="quot_address" class="form-control" placeholder="Enter Address" style="resize:both;" rows="4"><?=$rel['quot_address']?></textarea>
						</div>
					</div> 
				</div> 
			</div>
		</div>
	</div>      		
</div>      		
<!--tabs end-->	
<div class="clearfix"></div>
<hr/>	
</div>
<div class="clearfix"></div>
<div class="col-md-12 text-center">
	<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
	<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>	
</div>	
</div>
</div><!--Vendor row end-->	
<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
<input type='hidden' name='eid' id='eid' value='<?=$quotation_id?>' />
<input type='hidden' name='revise_status' id='revise_status' value='<?=$revise_status?>' />
<input type='hidden' name='start_quotation_id' id='start_quotation_id' value='<?=$start_quotation_id?>' />
<input type='hidden' name='prev_quotation_id' id='prev_quotation_id' value='<?=$prev_quotation_id?>' />

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

<?php include_once('../include/add_person.php');?>
<?php include_once('../include/row_product.php');?>
<?php include_once('../include/prod_img.php');?>
<?php include_once('../include/quotation_specification.php');?>
<?php include_once('../include/preview_cust_address.php');?>
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
<script src="<?=ROOT?>js/app/quotation.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/customer.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/row_product.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/prod_img.js?<?=time()?>"></script>
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
	<?if($mode=='Edit'){?>
		$('#cust_id').select2('readonly',true);
		$('#c_con_id').select2('readonly',true);
		$('#inquiry_id').select2('readonly',true);
	//Disable not selected Radio Button
	$(':radio:not(:checked)').attr('disabled', true);
	load_typeswise_terms(<?=$quot_type?>,<?=$quotation_id?>);
	<?}
	else{?>
		load_typeswise_terms(<?=$quot_type?>,'');
		<?}?>
		<?if($prev_quotation_id){?>
			copy_prev_quot_trn(<?=$prev_quotation_id?>);
			<?}?>
/*$(function() { 
	$('#quotation_date').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
	<?if($mode=='Add')
	{?>
	,startDate: 'd'//don't allow today and past dates
	<?}?>
	});
});*/
<?if($inq_to_quot){//check inq to quot for copy inq pro?>
	load_inq_pro(<?=$inquiry_id?>);
	<?}?>
	<?if($viewmode=="Add"){?>
		load_def_quotation_no();
		<?}?>
		CKEDITOR.replace( 'quot_annex_content', {
			enterMode: CKEDITOR.ENTER_BR
		});
		$(function(){
			setTimeout(function(){ $('#sidebar > ul').hide(); }, 1000);
		});
		CKEDITOR.replace( 'quatation_greeting', {
			enterMode: CKEDITOR.ENTER_BR
		});
		CKEDITOR.replace( 'attach_part', {
			enterMode: CKEDITOR.ENTER_BR
		});
	</script>
</body>
</html>