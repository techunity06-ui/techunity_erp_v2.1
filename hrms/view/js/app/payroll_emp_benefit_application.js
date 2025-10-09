//var datatable;
$(document).ready(function() {
		load_datatable(); 
// validate vendor add form on keyup and submit
$("#payroll_emp_benefit_application_add").validate({
	rules: {
		employee_id: {
			required: true			
		},
		payroll_period_id: {
			required: true			
		},
		benefit_application_date: {
			required: true			
		},
	},
	messages: {
		employee_id: {
			required: "Select Employee"
		},
		payroll_period_id: {
			required: "Select Payroll Period"		
		},
		benefit_application_date: {
			required: "Select Benefit Application Date"		
		},
	}
}); 
});
$("#payroll_emp_benefit_application_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#payroll_emp_benefit_application_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/payroll_emp_benefit_application/',
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
				toastr.success("PAYROLL EMPLOYEE BENEFIT APPLICATION ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'payroll_emp_benefit_application_list';
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
				toastr.success("PAYROLL EMPLOYEE BENEFIT APPLICATION UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'payroll_emp_benefit_application_list';
			}
			$('#payroll_emp_benefit_application_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_payroll_emp_benefit_application(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/payroll_emp_benefit_application/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("PAYROLL EMPLOYEE BENEFIT APPLICATION DELETE SUCCESSFULLY", "SUCCESS");
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
			"sAjaxSource": root_domain + hrms_domain + 'app/payroll_emp_benefit_application/',
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

function add_field()
{
	if($("#earning_component_id").val()==="")
	{		
		toastr.warning("Select Earning Component Name", "ERROR")
		return false;
	}
	if($("#earning_amount").val()==="")
	{		
		toastr.warning("Enter Earning Amount", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_emp_benefit_application/',
			data: { 
				mode : "fieldadd",
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(),
				earning_component_id:$("#earning_component_id").val(),
				maximum_benefit_amount:$("#maximum_benefit_amount").val(),
				earning_amount:$("#earning_amount").val()
			},
			success: function(response)
			{
				$("#earning_component_id").select2("val","");
				$("#maximum_display_benefit_amount").val("");
				$("#maximum_benefit_amount").val("");
				$("#earning_amount").val("");
				$('#addrow').val('Add');
				Unloading();
				show_data();
			}
		});
}

function show_data()
{
	var benefit_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_emp_benefit_application/',
		data: { mode : "load_tempoutward",benefit_id:benefit_id},
		success: function(data){
			$('#payroll_employee_benefits_applied').html(data);				
			Unloading();
		}		
	});
}

function edit_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_emp_benefit_application/',
			data: { mode : "preedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#earning_component_id").select2('val',data.earning_component_id);
				$("#maximum_display_benefit_amount").val(data.maximum_benefit_amount);
				$("#maximum_benefit_amount").val(data.maximum_benefit_amount);
				$("#earning_amount").val(data.earning_amount);
				$("#edit_id").val(id);
				$('#addrow').val('Update');
				Unloading();
			}
		});
}

function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_emp_benefit_application/',
			data: { mode : "delete_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data();
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
		url: root_domain + hrms_domain + 'app/payroll_emp_benefit_application/',
		data: { mode : "change_status", eid : id,p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL EMPLOYEE BENEFIT APPLICATION STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable(); 
		}
	});
	Unloading();
}

function getEarningComponentData(){
	var component_id = $("#earning_component_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_emp_benefit_application/',
		data: { mode : "get_earning_component_data",component_id:component_id},
		success: function(response){
			var data = jQuery.parseJSON(response);
			if(data.is_fexible_benefit_flag == 'Yes'){
				$("#maximum_display_benefit_amount").val(data.max_benefit_amount_yearly);
				$("#maximum_benefit_amount").val(data.max_benefit_amount_yearly);
			}else{
				$("#maximum_display_benefit_amount").val('0.00');
				$("#maximum_benefit_amount").val('0.00');
			}				
			Unloading();
		}		
	});
}