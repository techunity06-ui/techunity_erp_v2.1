var datatable;
$(document).ready(function() {
	show_list_data();
	// validate add form on keyup and submit
	$("#hrms_employee_add").validate({
		ignore:[],
		rules: {
			employee_name: {
				required: true
			},
			emp_profile_img: {
				extension: "jpg|jpeg|png|gif"
			},
			birth_date: {
				required: true
			},
			joining_date: {
				required: true,
			},
			gender: {
				required: true
			},
			country_id: {
				required: true
			},
			state_id: {
				required: true
			},
			city_id: {
				required: true
			},
			emp_email: {
				required: true
			},
			emp_mobile: {
				required: true
			},
			emp_zone_id: {
				required: true
			},
			branch_id_emp: {
				required: true
			},
			emp_user_type: {
				required: true
			},
			per_day_salary:{
				required: true
			},
			status: {
				required: true
			}
		},
		messages: {
			employee_name: {
				required: "Enter Employee Name"
			},
			emp_profile_img: {
				extension: "Only image type jpg/png/jpeg/gif is allowed"
			},
			birth_date: {
				required: "Enter Birth Name"
			},
			joining_date: {
				required: "Enter Joining Date"
			},
			country_id: {
				required: "Select Country"
			},
			state_id: {
				required: "Select State"
			},
			city_id: {
				required: "Select City"
			},
			emp_email:{
				email:"Enter Valid Email",
				required: "Enter Email ID"
			},
			emp_mobile: {
				number:"Enter Only Number ",
				required: "Enter Mobile Number"
			},
			emp_zone_id: {
				required: "Select Zone"
			},
			branch_id_emp: {
				required: "Select Branch"
			},
			emp_user_type: {
				required: "Select User Type"
			},
			per_day_salary:{
				required: "Enter Per Day Salary"
			},
			status: {
				required: "Select Status"
			}
		}
	}); 
});

$("#hrms_employee_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();

	if (!$("#hrms_employee_add").valid()) {
		return false;
	}
	
	form.submitted = true;	
	
	
	Loading();	
	$(this).attr("disabled","disabled");		
	
	var form_data = new FormData(this);
	var token = $("#token").val();	
	form_data.append('file', $('#emp_profile_img').prop('files')[0]);
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_employee/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resnse)
		{		
			var data = JSON.parse(resnse);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				toastr.success("EMPLOYEE ADDED SUCCESSFULLY", "SUCCESS")
				$('#hrms_employee_add').trigger('reset');		
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_employee';
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("EMPLOYEE UPDATED SUCCESSFULLY", "SUCCESS")
				$('#hrms_employee_add').trigger('reset');					
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_employee';
			}
			else if(responsevalue.trim() == '0') {
				toastr.error("SOMETHING WRONG", "ERROR")
				$('#hrms_employee_add').trigger('reset');	
				Unloading();
			} else if(responsevalue.trim() == '1') {
				toastr.error("ENTER VALID DATA", "ERROR")
				$('#hrms_employee_add').trigger('reset');	
				Unloading();
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function show_list_data() {
	
	var datatable = $("#dynamic-table").dataTable({
			"bStateSave" : true,
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
				"sLengthMenu": "_MENU_",
				"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
				"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[20, 50, 100, -1], [20, 50, 100,"All"]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_employee/',
			"fnServerParams": function ( aoData ) {
				aoData.push({ "name": "mode", "value": "fetch" });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee/',
		data: { mode : "change_status", eid : id,p_status:p_status },
		success: function(response)
		{
			toastr.success("EMPLOYEE STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			show_list_data();
		}
	});
	Unloading();
}

function delete_record(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("EMPLOYEE DELETED SUCCESSFULLY", "SUCCESS");
					// datatable.fnReloadAjax();
					Unloading();
					show_list_data();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
		Unloading();
	}
}

function changeZone(val) {
	$('#branch_id').select2('val', '');
	$('#employee_ids').select2('val', '');
	$('#employee_ids').html('');

	if(val != '') {
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { mode : "load_branch", val : val},
			success: function(response){
				$('#branch_id').html(response);	
			}
		});
	}
}

function changeBranch(val) {
	$('#employee_ids').select2('val', '');
	$('#employee_ids').html('');
	
	if(val != '') {
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { mode : "load_emp", val : val},
			success: function(response){
				$('#employee_ids').html(response);	
			}
		});
	}
}

function load_state(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain+'app/customer/',
		data: { mode : "load_state",  id : parentid},
		success: function(responce){
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}
function add_state()
{
	if($("#countryid").val()=='')
	{
		toastr.warning("Please Select the Country", "WARNING");
	}
	else{
		$("#bs-example-modal-state").modal("show");
		$("#countryid").val($("#countryid").val());
	}
}
function load_city(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain+'app/vender/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}
function add_city()
{
	if($("#stateid").val()=='')
	{
		toastr.warning("Please Select the State", "WARNING");
	}
	else{
		$("#bs-example-modal-city").modal("show");
		$("#state_id").val($("#stateid").val());
	}
}
function checkUsername(uname)
{
	var emp_email1=$('#emp_email_hid').val();
	if(emp_email1!=uname)
	{
		$.ajax({
				type: "POST",  
				url: root_domain+'app/ledger/',
				data: { mode : "check_username",uname:uname },
				success: function(response)  
				{
					if(response>0)
					{
						$('#user_error').html("<strong style='color:red'>Sorry.This Username Already Exist</strong><br>");
						$('#btn_submit').attr('disabled',true);
					}
					else
					{
						$('#user_error').html("<strong style='color:green'>Username Available</strong><br>");
						$('#btn_submit').attr('disabled',false);
					}
				}   
		  });
	}
}
function get_branch_by_zone(zid,sindex,bid)
{
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger/',
		data: { mode : "get_branch_by_zone",zid : zid,bid : bid,sindex:sindex },
		success: function(resnse)
		{
			$('#'+sindex).html(resnse);
			$('#'+sindex).select2('focus');
			$('#'+sindex).select2('val',bid);
			Unloading();			
		}
	});	
}
function load_city_all(){
	var alloc_stateid = $('#alloc_stateid').val();
	
	$.ajax({
		type: "POST",  
		url: root_domain+'app/ledger/',
		data: { mode : "load_city_all", alloc_stateid:alloc_stateid },
		success: function(response)  
		{
			var resp=JSON.parse(response);
			$('#alloc_cityid').html(resp.html_resp);
			$('#alloc_cityid').select2("val","");
			
		}   
  });
}
function load_report_to_users(report_to_user_type){	
	$.ajax({
		type: "POST",  
		url: root_domain+'app/ledger/',
		data: { mode : "load_report_to_users", report_to_user_type:report_to_user_type },
		success: function(response)  
		{
			//console.log(response);
			var resp=JSON.parse(response);
			$('#report_to_user_id').html(resp.html_resp);
			$('#report_to_user_id').select2("val","");
			
		}   
  });
}
// History Tab Functions
function show_history_data()
{
	var he_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee/',
		data: { mode : "load_history_company",he_id:he_id},
		success: function(data){
			$('#historycompanydata').html(data);				
			Unloading();
		}		
	});
}
function add_history_field()
{
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { 
				mode : "fieldhistoryadd", 
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(),
				history_branch_id:$("#history_branch_id").val(),
				history_department_id:$("#history_department_id").val(),
				history_designation_id:$("#history_designation_id").val(),
				history_from_date:$("#history_from_date").val(),
				history_to_date:$("#history_to_date").val() 
			},
			success: function(response)
			{
				$("#history_branch_id").select2('val','');
				$("#history_department_id").select2('val','');
				$("#history_designation_id").select2('val','');
				$("#history_from_date").val("")
				$("#history_to_date").val("")
				$('#addproduct').show();
				$('#addrow').val('Add');
				Unloading();
				show_history_data();
			}
		});
}
function edit_history_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { mode : "prehistoryedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#history_branch_id").select2('val',data.history_branch_id);
				$("#history_department_id").select2('val',data.history_department_id);
				$("#history_designation_id").select2('val',data.history_designation_id);
				$("#history_from_date").val(data.history_from_date)
				$("#history_to_date").val(data.history_to_date)
				$("#edit_id").val(id);
				$('#addrow').val('Update');
				Unloading();
			}
		});
}
function delete_history_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { mode : "delete_history_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_history_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
// Previous Work Tab Functions
function show_previous_data()
{
	var pre_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee/',
		data: { mode : "load_previous_company",pre_id:pre_id},
		success: function(data){
			$('#previouscompanydata').html(data);				
			Unloading();
		}		
	});
}
function add_previous_field()
{
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { 
				mode : "fieldpreviousadd", 
				eid : $("#eid").val(), 
				edit_previous_id:$("#edit_previous_id").val(),
				company_name:$("#company_name").val(),
				designation:$("#designation").val(),
				salary_amount:$("#salary_amount").val(),
				address:$("#address").val(),
				contact:$("#contact").val(),
				total_experience:$("#total_experience").val()  
			},
			success: function(response)
			{
				$("#company_name").val("");
				$("#designation").val("");
				$("#salary_amount").val("");
				$("#address").val("");
				$("#contact").val("");
				$("#total_experience").val("");
				$('#addproduct').show();
				$('#addpreviousrow').val('Add');
				Unloading();
				show_previous_data();
			}
		});
}
function edit_previous_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { mode : "prepreviousedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#company_name").val(data.company_name);
				$("#designation").val(data.designation);
				$("#salary_amount").val(data.salary_amount);
				$("#address").val(data.address);
				$("#contact").val(data.contact);
				$("#total_experience").val(data.total_experience);
				$("#edit_previous_id").val(id);
				$('#addpreviousrow').val('Update');
				Unloading();
			}
		});
}
function delete_previous_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { mode : "delete_previous_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_previous_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
// Educational Work Tab Functions
function show_educational_data()
{
	var edu_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee/',
		data: { mode : "load_educational_company",edu_id:edu_id},
		success: function(data){
			$('#educationalcompanydata').html(data);				
			Unloading();
		}		
	});
}
function add_educational_field()
{
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { 
				mode : "fieldeducationaladd", 
				eid : $("#eid").val(), 
				edit_educational_id:$("#edit_educational_id").val(),
				education_school_university:$("#education_school_university").val(),
				education_qualification:$("#education_qualification").val(),
				education_level:$("#education_level").val(),
				year_of_passing:$("#year_of_passing").val(),
				class_percentage:$("#class_percentage").val(),
				optional_subjects:$("#optional_subjects").val()  
			},
			success: function(response)
			{
				$("#education_school_university").val("");
				$("#education_qualification").val("");
				$("#education_level").select2('val','');
				$("#year_of_passing").val("");
				$("#class_percentage").val("");
				$("#optional_subjects").val("");
				$('#addproduct').show();
				$('#addeducationalrow').val('Add');
				Unloading();
				show_educational_data();
			}
		});
}
function edit_educational_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { mode : "preeducationaledit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#education_school_university").val(data.education_school_university);
				$("#education_qualification").val(data.education_qualification);
				$("#education_level").select2('val',data.education_level);
				$("#year_of_passing").val(data.year_of_passing);
				$("#class_percentage").val(data.class_percentage);
				$("#optional_subjects").val(data.optional_subjects);
				$("#edit_educational_id").val(id);
				$('#addeducationalrow').val('Update');
				Unloading();
			}
		});
}
function delete_educational_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee/',
			data: { mode : "delete_educational_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_educational_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}