<?php
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../include/hrms_common_functions.php");
$token = md5(rand(1000, 9999));
$_SESSION['token'] = $token;
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
$title = 'HRMS Employee';
function catgeory_view($dbcon,$id=0) // Recursive function
{
  $select_category="select userdata.*,led.emp_profile_img,led.l_id from users as userdata left join tbl_ledger as led on led.l_id = userdata.employee_id where userdata.report_to_user_id ='".$id."' and userdata.user_type != '1'  and led.l_status=0";
  $result_category=$dbcon->query($select_category);
  $output="";
  if($result_category)
  {
    $row_count=mysqli_num_rows($result_category);
    if($row_count>0)
    {
      $output.="<ul>";
      while($row_data=mysqli_fetch_array($result_category))
      {
      		if(isset($row_data['emp_profile_img']) && !empty($row_data['emp_profile_img'])){
        		$imagePath = ROOT . HRMS_ROOT .'upload/emp_profile_image/'.$row_data['emp_profile_img'];
        	}else{
        		$imagePath = ROOT . HRMS_ROOT .'upload/emp_profile_image/admin.png';
        	}
            $output.="<li><div class='member-view-box'><div class='member-image'>
            		<img src=".$imagePath." alt='Member'>";
            if($row_data['employee_id'] != '0'){
            	$output.="<span class='status_action' title='Click for Active/InActive' style='display: none;' data-id=".$row_data['employee_id']."></span>
            		<span class='add_action' title='Click for Add' style='display: none;' data-id=".$row_data['employee_id']."></span>
		            <span class='delete_action' title='Click for Delete' style='display: none;' data-id=".$row_data['employee_id']."></span>
		            <span class='edit_action' title='Click for Edit' style='display: none;' data-id=".$row_data['employee_id']."></span>";
		    }
		    $output.="<div class='member-details'><h3 class='member_name_display'><span class='title_name' style='margin-left: -50px;'>".$row_data['user_name']."".catgeory_view($dbcon,$row_data['user_id'])."</span></h3></div></div></div></li>"; 
      }
      $output.="</ul>";    
    }
    else 
    {
        $output.="<ul class='space'></ul>";
    }
  }
  return $output;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<body>
	<style>
		tbody>tr>:nth-child(2){ 
			 text-align: center;
		}
	</style>
	<link rel="stylesheet" type="text/css" href="<?= ROOT . HRMS_ROOT ?>css/treeview.css?<?= time() ?>">
	<section id="container">
		<?php include_once('../../include/include_top_menu.php'); ?>
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php'); ?>
		<!--sidebar end-->

		
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3>New <?php echo $title; ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active"><?php echo $title; ?></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>
				<!-- Tab Section Start By Umair -->
		         <section class="panel" style="margin-top: 15px">
			      	 <header class="panel-heading tab-bg-dark-navy-blue ">
			          <ul class="nav nav-tabs">
			              <li class="active">
			              	  <a data-toggle="tab" href="#emp_treeview_listing" aria-expanded="true">Tree View Users Listing</a>
			              </li>
			              <li>
			                  <a data-toggle="tab" href="#emp_listing" aria-expanded="true">Employee Listing</a>
			              </li>
			          </ul>
			      </header>
			      <div class="panel-body">
		              <div class="tab-content">
		              	  <div id="emp_treeview_listing" class="tab-pane active">
			              	  	<?php
										echo "<div class='body genealogy-body genealogy-scroll'><div class='genealogy-tree'><ul>";     
									    $select_category="select userdata.*,led.emp_profile_img,led.l_id from users as userdata left join tbl_ledger as led on led.l_id = userdata.employee_id where userdata.report_to_user_id ='0' and userdata.user_type != '1' ";
									    $result_category=$dbcon->query($select_category);
									    $output="";
									    if($result_category)
									    {
									      $row_count=mysqli_num_rows($result_category);
									      if($row_count>0)
									      {
									        while($row_data=mysqli_fetch_array($result_category))
									        {
									        	if(isset($row_data['emp_profile_img']) && !empty($row_data['emp_profile_img'])){
									        		$imagePath = ROOT . HRMS_ROOT .'upload/emp_profile_image/'.$row_data['emp_profile_img'];
									        	}else{
									        		$imagePath = ROOT . HRMS_ROOT .'upload/emp_profile_image/admin.png';
									        	}
									            $output.= "<li><div class='member-view-box'><div class='member-image'><img src=".$imagePath." alt='Member'>";
									            if($row_data['employee_id'] != '0'){
										            $output.= "<span class='status_action' title='Click for Active/InActive' style='display: none;' data-id=".$row_data['employee_id']."></span>
										            	<span class='add_action' title='Click for Add' style='display: none;' data-id=".$row_data['employee_id']."></span>
										            	<span class='delete_action' title='Click for Delete' style='display: none;' data-id=".$row_data['employee_id']."></span>
										            	<span class='edit_action' title='Click for Edit' style='display: none;' data-id=".$row_data['employee_id']."></span>";
									            }
									            $output.= "<div class='member-details'><h3 class='member_name_display'><span class='title_name' style='margin-left: -50px;'>".$row_data['user_name']."".catgeory_view($dbcon,$row_data['user_id'])."</span></h3></div></div></div></li>";
									            
									        }
									      }
									    }
									    echo $output;  
										echo "</ul></div></div>"; 
								?>
		              	  </div>
		                  <div id="emp_listing" class="tab-pane" >
		                  		<div class="row">
									<div class="col-sm-12">
										<section class="panel">
											<?php 

												$add_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'add',$dbcon); 
												if($add_btn_per != ""){
											?>
												<header class="panel-heading">
													<span class="tools pull-right"> 	
														<a href="<?=ROOT . HRMS_ROOT . 'hrms_employee_add'?>" ><button class="btn btn-success btn-flat" >Add <?=$title?></button></a>
													</span> 
												</header>
											<?php } ?>
											<div class="panel-body">
												<div class="adv-table">
													<table class="display table table-bordered table-striped" id="dynamic-table">
														<thead>
															<tr>
																<th>Sr. NO.</th>
																<th>Employee Image</th>
																<th>Series ID</th>
																<th>Employee Name</th>
																<th>Employee Email</th>
																<th>Employee Group</th>
																<th>Created Date</th>
																<th>Status</th>
																<th class="hidden-phone">Action</th>
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
								<!--state overview end-->
		                  </div>
		              </div>
		          </div>
		  		</section>
				
				
			</section>
		</section>
		<!--main content end-->
		<!--footer start-->
		<?php include_once('../../include/footer.php'); ?>
		<!--footer end-->
	</section>

	<!-- Modal -->
<div class="modal colored-header info" id="ModalEmployee" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width" style="width: 1100px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Add Employee</h3>
			</div>
			<div class="modal-body form">
			<form role="form" id="hrms_employee_add" action="javascript:;" method="post" name="hrms_employee_add" enctype="multipart/form-data">
				<div class="row">
					<div class="col-md-12 margin_row">
						<div class="col-md-6">
							<div class="form-group">
						  		<label for="series_id" class="col-md-4 control-label">Series*</label>
							  	<div class="col-md-8 col-xs-11">
							  		<?php $series_id = ($mode == "Edit" && $rel['series_id']) ? $rel['series_id'] : get_series_by_type($dbcon, 'EMPLOYEE', '16'); ?>
							  		<input type="text" class="form-control" id="series_id" name="series_id" value="<?php echo $series_id; ?>" readonly />
							  	</div>
							</div>							 
						</div>
					</div>  
					<div class="col-md-12 margin_row" style="padding-top: 10px;">
						<div class="col-md-6 typeled">
							<div class="form-group">
						  		<label for="employee_name" class="col-md-4 control-label">Employee Name*</label>
							  	<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" id="employee_name" name="employee_name" placeholder="Enter Employee Name" value="<?php echo $rel['employee_name']; ?>" />
							  	</div>
								  
							</div>							 
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Profile Photo* </label>
								<div class="col-md-8 col-xs-12">
									<div class="col-md-7">
										<input type="file" id="emp_profile_img" name="emp_profile_img"  title="Select Profile Photo" <?php if($mode=='Add') { ?>required <?php } ?> >
									</div>
									<div class="col-md-1">
										<?php if($mode=='Edit') { ?>
											<img src="<?php if(isset($rel['emp_profile_img']) && !empty($rel['emp_profile_img'])){ echo ROOT . HRMS_ROOT .'upload/emp_profile_image/'.$rel['emp_profile_img']; } else { echo ROOT . HRMS_ROOT .'upload/emp_profile_image/no_profile.png'; } ?>" width="50" height="50" />
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12 margin_row" style="padding-top: 10px;">
						<div class="col-md-6">
							<div class="form-group">
							  	<label for="birth_date" class="col-md-4 control-label">Birth Date*</label>
							  	<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control datepickerPrev" id="birth_date" name="birth_date" placeholder="Enter Birth Date" value="<?=($rel['birth_date'] && $rel['birth_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['birth_date'])) : ''?>" autocomplete="off" />
							  	</div>
							</div>
						</div>
						<div class="col-md-6 typeled">
							<div class="form-group">
						  		<label for="joining_date" class="col-md-4 control-label">Joining Date*</label>
							  	<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control datepicker" id="joining_date" name="joining_date" placeholder="Enter Joining Date" value="<?=($rel['joining_date'] && $rel['joining_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['joining_date'])) : ''?>" autocomplete="off" />
							  	</div>
								  
							</div>							 
						</div>
					</div>
					<div class="col-md-12 margin_row" style="padding-top: 10px;">
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">Select Country *</label>
								<div class="col-md-8 col-xs-11">
									<select class="select2" name="country_id" id="countryid" onChange="load_state(this.value,'stateid','')">
										<?=get_country($dbcon,$countryid)?>				
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label for="gender" class="col-md-4 control-label">Gender*</label>
							  	<div class="col-md-8 col-xs-11">
									<select class="select2" id="gender" name="gender">
										<?php $gender = $rel['gender'];
										$ms = ($gender == 'male' || $gender == 'MALE') ? 'selected' : '';
										$fs = ($gender == 'female' || $gender == 'FEMALE') ? 'selected' : '';
										$os = ($gender == 'other' || $gender == 'OTHER') ? 'selected' : ''; ?>
										<option value="">Please Select</option>
										<option value="male" <?php echo $ms; ?>>Male</option>
										<option value="female" <?php echo $fs; ?>>Female</option>
										<option value="other" <?php echo $os; ?>>Other</option>
									</select>	
							  	</div>  	
							</div>							 
						</div>
					</div>
					<div class="col-md-12 margin_row" style="padding-top: 10px;">
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">Select State *</label>
								<div class="col-md-6 col-xs-11">
									<select class="select2" name="state_id" id="stateid" onChange="load_city(this.value,'cityid','')">
										<option value="">Select State</option>	
									</select>
								</div>
								<div class="col-md-2">
									<input type="button"  name="addState" id="addState" data-toggle="modal" data-target="" onclick="add_state();" class="btn btn-primary" value="+ Add State"/>
								</div>
							</div>
						</div>
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">Select City *</label>
								<div class="col-md-6 col-xs-11">
									<select class="select2" name="city_id" id="cityid">
										<option value="">Select City</option>	
									</select>
								</div>
								<div class="col-md-2">
								<input type="button" name="addCity" id="addCity" data-toggle="modal" data-target="" onclick="add_city();" class="btn btn-primary" value="+ Add city"/>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12 margin_row" style="padding-top: 10px;">
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">Pin Code</label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="Customer Pincode" name="cust_pincode" id="cust_pincode"   value="<?=$rel['cust_pincode']?>"  />
								</div>
							</div>
						</div>
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">PAN / IT No.</label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="Customer PAN" name="m_pan" id="m_pan"   value="<?=$rel['m_pan']?>" style="text-transform:uppercase"  />
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12 margin_row" style="padding-top: 10px;">
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">Email(User name)*</label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="Email" title="Email" name="emp_email" id="emp_email" value="<?=$rel['emp_email']?>" onkeyup="checkUsername(this.value)" required />
									
									<input type="hidden" class="form-control" placeholder="Email" title="Email" name="" id="emp_email_hid" value="<?=$rel['emp_email']?>"   />
									
									<div id="user_error"></div>
								</div>	
							</div> 
						</div>
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">Password</label>
								<div class="col-md-8 col-xs-11">
									<input type="password" class="form-control" placeholder="Password" title="Password" name="emp_password" id="emp_password" <?=($mode=='Add')?'required':''?>  />
								</div>	
							</div> 
						</div>
					</div>
					<div class="col-md-12 margin_row" style="padding-top: 10px;">
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">Mobile No. </label>
								<div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" placeholder="Mobile No." name="emp_mobile" id="emp_mobile" value="<?=$rel['emp_mobile']?>" required  />
								</div>
							</div>
						</div>
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">Zone</label>
								<div class="col-md-8 col-xs-11">
									<select class="select2" name="emp_zone_id" id="emp_zone_id" onchange="get_branch_by_zone(this.value,'branch_id_emp')" required>
										<?=get_zone($dbcon,$rel['emp_zone_id'])?>				
									</select>
								</div>	
							</div>
						</div>
					</div>
					<div class="col-md-12 margin_row" style="padding-top: 10px;">
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">Branch*</label>
								<div class="col-md-8 col-xs-11">
									<select class="select2" name="branch_id_emp" id="branch_id_emp" required>
														
									</select>
								</div>	
							</div>
						</div>
						<div class="col-md-6 typeled">
							<div class="form-group">
								<label class="col-md-4 control-label">Employee User Type*</label>
								<div class="col-md-8 col-xs-11">
									<select class="select2" name="emp_user_type" id="emp_user_type" title="Select Type" required>
										<option value="">Select Type</option>
										<?php
											$query = $dbcon->query("select * from tbl_usertype where status=0 and (usertype_id!=1 or company_id=".$_SESSION['company_id'].") ");
											while ($r = $query->fetch_assoc()) {
												echo '<option value="' . $r['usertype_id'] . '">' .$r['usertype_name']. '</option>';
											}
										?>			
									</select>
								</div>	
							</div>
						</div>
					</div>
					<div class="col-md-12 margin_row" style="padding-top: 10px;">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Allocated State</label>
								<div class="col-md-8 col-xs-11">
									<select class="select2" name="alloc_stateid[]" id="alloc_stateid" onChange="load_city_all();" placeholder="Allocated State" multiple>
										<?=get_state_all($dbcon,$rel['alloc_state_id'],"101")?>				
									</select>
								</div>	
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Allocated City</label>
								<div class="col-md-8 col-xs-11">
									<select class="select2" name="alloc_cityid[]" id="alloc_cityid" placeholder="Allocated City" multiple>
										<?=get_city_all($dbcon,$rel['alloc_city_id'],$rel['alloc_state_id'])?>	
									</select>
								</div>	
							</div>
						</div>
					</div>
					

				</div>	
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Save</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_employee.js?<?= time() ?>"></script>
	<script>
		$(function () {
		    $('.genealogy-tree ul').hide();
		    $('.genealogy-tree>ul').show();
		    $('.genealogy-tree ul.active').show();
		    $('.genealogy-tree li').on('click', function (e) {
		        var children = $(this).children('.member-view-box').children('.member-image').children('.member-details').children('.member_name_display').children('.title_name').find('> ul');
		        if (children.is(":visible")) children.hide('fast').removeClass('active');
		        else children.show('fast').addClass('active');
		        e.stopPropagation();
		    });
		    $('.member-image').mouseenter(function() {
		        $(this).children("span.add_action").css("display","inline");
		        $(this).children("span.edit_action").css("display","inline");
		        $(this).children("span.delete_action").css("display","inline");
		        $(this).children("span.status_action").css("display","inline");
		    }).mouseleave(function() {
		        $("span.add_action").css("display","none");
		        $("span.edit_action").css("display","none");
		        $("span.delete_action").css("display","none");
		        $("span.status_action").css("display","none");
		    });
		    $(this).find('img').siblings("span.add_action").on('click', function (e) {
		    	var current_id = $(this).data('id');
		    	$("#ModalEmployee").modal("show");	
		    });
		    $(this).find('img').siblings("span.edit_action").on('click', function (e) {
		    	var current_id = $(this).data('id');
		    	alert(current_id);
		    });
		    $(this).find('img').siblings("span.delete_action").on('click', function (e) {
		    	var current_id = $(this).data('id');
		    	alert(current_id);
		    });
		    $(this).find('img').siblings("span.status_action").on('click', function (e) {
		    	var current_id = $(this).data('id');
		    	alert(current_id);
		    });
		});
		$(".select2").select2({
			width: '100%'
		});
		$(".default-date-picker").datepicker({
	        format: "dd-mm-yyyy",
	        autoclose: true,
	        todayHighlight: true
	    });
	    $('#date_of_issue').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
		$(".datepicker").datepicker({
			format: "dd-mm-yyyy",
		    startDate: "1d",
		    autoclose: true,
		    todayHighlight: true
		});
		$(".datepickerPrev").datepicker({
			format: "dd-mm-yyyy",
		    endDate: "-1y",
		    autoclose: true,
		    todayHighlight: true
		});
	</script>
</body>
</html>