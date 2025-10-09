var datatable;
$(document).ready(function () {
	show_list_data();  

	// validate vendor add form on keyup and submit
	$("#payroll_emp_incentive_add").validate({
		rules: {
			employee_id: {
				required: true,
			},
			payroll_date: {
				required: true,
			},
			incentive_amount: {
				required: true,
			},
			salary_component_id: {
				required: true
			},
			status: {
				required: true
			},
		},
		messages: {
			employee_id: {
				required: "Select Employee Name",
			},
			payroll_date: {
				required: "Select Payroll Date",
			},
			incentive_amount: {
				required: "Enter Incentive Amount",
			},
			salary_component_id: {
				required: "Select Salary Component Name",
			},
			status: {
				required: "Select Status",
			},
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditPayrollEmpIncentive").validate({
		rules: {
			employee_id: {
				required: true,
			},
			payroll_date: {
				required: true,
			},
			incentive_amount: {
				required: true,
			},
			salary_component_id: {
				required: true
			},
			status: {
				required: true
			},
		},
		messages: {
			employee_id: {
				required: "Select Employee Name",
			},
			payroll_date: {
				required: "Select Payroll Date",
			},
			incentive_amount: {
				required: "Enter Incentive Amount",
			},
			salary_component_id: {
				required: "Select Salary Component Name",
			},
			status: {
				required: "Select Status",
			},
		}
	});

});
$("#payroll_emp_incentive_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#payroll_emp_incentive_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		series_id: $("#series_id").val(),
		employee_id: $("#employee_id").val(),
		payroll_date: $("#payroll_date").val(),
		incentive_amount: $("#incentive_amount").val(),
		salary_component_id: $("#salary_component_id").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/payroll_emp_incentive/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("PAYROLL EMPLOYEE INCENTIVE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				show_list_data();
			}
			else if (response.trim() == '2') {
				toastr.success("PAYROLL EMPLOYEE INCENTIVE ADDED SUCCESSFULLY", "SUCCESS");
				$('#payroll_emp_incentive_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				$('#payroll_emp_incentive_add').trigger('reset');
				Unloading();
			}
			$('#payroll_emp_incentive_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditPayrollEmpIncentive").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditPayrollEmpIncentive").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		series_id: $("#edit_series_id").val(),
		employee_id: $("#edit_employee_id").val(),
		payroll_date: $("#edit_payroll_date").val(),
		incentive_amount: $("#edit_incentive_amount").val(),
		salary_component_id: $("#edit_salary_component_id").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/payroll_emp_incentive/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("PAYROLL EMPLOYEE INCENTIVE UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				show_list_data();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();
			}
			$("#ModalEditPayrollEmpIncentive").modal("hide");
		},
		error: function (jqXHR, textStatus, errorThrown) {
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
			"sAjaxSource": root_domain + hrms_domain + 'app/payroll_emp_incentive/',
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

function delete_catalog(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_emp_incentive/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("PAYROLL EMPLOYEE INCENTIVE DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					show_list_data();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}
}
function edit_test(id) {
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_emp_incentive/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#ModalEditPayrollEmpIncentive").modal("show");
			$("#edit_id").val(id);
			$("#edit_series_id").val(obj.series_id);
			$("#edit_employee_id").select2('val',obj.employee_id);
			$("#edit_payroll_date").val(obj.payroll_date);
			$("#edit_incentive_amount").val(obj.incentive_amount);
			$("#edit_salary_component_id").select2('val',obj.salary_component_id);
			$("#edit_status").select2('val',obj.status);
			Unloading();
			show_list_data();
		}
	});
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_emp_incentive/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL EMPLOYEE INCENTIVE STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			show_list_data();
		}
	});
	Unloading();
}
