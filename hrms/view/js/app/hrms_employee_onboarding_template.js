//var datatable;
$(document).ready(function() {
		load_datatable(); 
// validate vendor add form on keyup and submit
$("#hrms_employee_onboarding_template_add").validate({
	rules: {
		designation_id: {
			required: true			
		},
		department_id: {
			required: true			
		},
		employee_grade_id: {
			required: true			
		},
	},
	messages: {
		designation_id: {
			required: "Select Designation"
		},
		department_id: {
			required: "Select Department"
		},
		employee_grade_id: {
			required: "Select Employee Grade"		
		},
	}
}); 
});
$("#hrms_employee_onboarding_template_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hrms_employee_onboarding_template_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_employee_onboarding_template/',
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
				toastr.success("HRMS EMPLOYEE ONBOARDING TEMPLATE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_employee_onboarding_template_list';
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
				toastr.success("HRMS EMPLOYEE ONBOARDING TEMPLATE UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_employee_onboarding_template_list';
			}
			$('#sales_order_add').trigger('reset');	
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
				url: root_domain + hrms_domain + 'app/hrms_employee_onboarding_template/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("HRMS EMPLOYEE ONBOARDING TEMPLATE DELETE SUCCESSFULLY", "SUCCESS");
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
function add_employee_onboarding_template_field()
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
			url: root_domain + hrms_domain + 'app/hrms_employee_onboarding_template/',
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
				$('#addemployeeonboardingtemplaterow').val('Add');
				Unloading();
				show_employee_onboarding_template_data();
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
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_employee_onboarding_template/',
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

function show_employee_onboarding_template_data()
{
	var est_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_onboarding_template/',
		data: { mode : "load_tempoutward",est_id:est_id},
		success: function(data){
			$('#hrms_employee_onboarding_template_data').html(data);				
			Unloading();
		}		
	});
}
function edit_employee_onboarding_template_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_onboarding_template/',
			data: { mode : "preedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#activity_name").val(data.activity_name);
				$("#activity_user_id").select2("val",data.activity_user_id);
				$("#activity_role_id").select2("val",data.activity_role_id);
				$("#edit_id").val(id);
				$('#addemployeeonboardingtemplaterow').val('Update');
				Unloading();
			}
		});
}
function delete_employee_onboarding_template_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_onboarding_template/',
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
					show_employee_onboarding_template_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_onboarding_template/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS EMPLOYEE ONBOARDING TEMPLATE STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}