//var datatable;
$(document).ready(function() {
		load_datatable(); 
// validate vendor add form on keyup and submit
$("#hrms_employee_onboarding_add").validate({
	rules: {
		employee_id: {
			required: true			
		},
		date_of_joining: {
			required: true			
		},
		onboarding_status: {
			required: true			
		},
		emp_onboarding_temp_id: {
			required: true			
		},
		status: {
			required: true			
		}
	},
	messages: {
		employee_id: {
			required: "Select Employee"
		},
		date_of_joining: {
			required: "Select Date Of Joining"
		},
		onboarding_status: {
			required: "Select Onboarding Status"		
		},
		emp_onboarding_temp_id: {
			required: "Select Onboarding Template"			
		},
		status: {
			required: "Select Status"			
		}
	}
}); 
});
$("#hrms_employee_onboarding_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hrms_employee_onboarding_add").valid()) {
		return false;
	}
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_employee_onboarding/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("HRMS EMPLOYEE ONBOARDING ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_employee_onboarding_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == 'update')
			{	
				toastr.success("HRMS EMPLOYEE ONBOARDING UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_employee_onboarding_list';
			}
			$('#hrms_employee_onboarding_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_hrms_employee_onboarding_template(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/hrms_employee_onboarding/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("HRMS EMPLOYEE ONBOARDING DELETE SUCCESSFULLY", "SUCCESS");
						datatable.fnReloadAjax();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
}
function add_employee_onboarding_field()
{
	if($("#activity_name").val()==="")
	{		
		toastr.warning("Select Activity Name", "ERROR")
		return false;
	}
	if($("#activity_user_id").val()==="")
	{		
		toastr.warning("Enter Activity User", "ERROR")
		return false;
	}
	if($("#activity_role_id").val()==="")
	{		
		toastr.warning("Enter Activity Role", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_onboarding/',
			data: { 
				mode : "fieldemployeeonboardingadd", 
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(),
				activity_name:$("#activity_name").val(),
				activity_user_id:$("#activity_user_id").val(),
				activity_role_id:$("#activity_role_id").val() 
			},
			success: function(response)
			{
				$("#activity_name").val("");
				$("#activity_user_id").select2("val","");
				$('#activity_role_id').select2("val","");
				$('#addemployeeonboardingrow').val('Add');
				Unloading();
				show_employee_onboarding_data();
			}
		});
}
function reload_data()
{
	load_datatable();
}	
function load_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	var type=$('#type_id').val();
	datatable = $("#dynamic-table").dataTable({
			"bStateSave": true,
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
			"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_employee_onboarding/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function show_employee_onboarding_data()
{
	var est_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_onboarding/',
		data: { mode : "load_tempoutward",est_id:est_id},
		success: function(data){
			$('#hrms_employee_onboarding_data').html(data);				
			Unloading();
		}		
	});
}
function edit_employee_onboarding_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_onboarding/',
			data: { mode : "preedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#activity_name").val(data.activity_name);
				$("#activity_user_id").select2("val",data.activity_user_id);
				$("#activity_role_id").select2("val",data.activity_role_id);
				$("#edit_id").val(id);
				$('#addemployeeonboardingrow').val('Update');
				Unloading();
			}
		});
}
function delete_employee_onboarding_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_onboarding/',
			data: { 
				mode : "delete_data",
				eid : id
			},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_employee_onboarding_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function getEmployeeOnboarding(){
	var emp_sep_id = $("#emp_onboarding_temp_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_onboarding/',
		data: { mode : "get_employee_onboarding",emp_sep_id:emp_sep_id},
		success: function(response){
			var data = jQuery.parseJSON(response);
			$("#designation_id").select2("val",data.designation_id);
			$("#department_id").select2("val",data.department_id);
			$("#employee_grade_id").select2("val",data.employee_grade_id);			
			Unloading();
		}		
	});
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_onboarding/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS EMPLOYEE ONBOARDING STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable();
		}
	});
	Unloading();
}