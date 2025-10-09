var datatable;
$(document).ready(function () {
	show_list_data();  

	// validate vendor add form on keyup and submit
	$("#payroll_emp_other_income_add").validate({
		rules: {
			employee_id: {
				required: true
			},
			payroll_period_id: {
				required: true
			},
			income_amount: {
				required: true
			}
		},
		messages: {
			employee_id: {
				required: "Select Employee Name"
			},
			payroll_period_id: {
				required: "Select Payroll Period Name"
			},
			income_amount: {
				required: "Enter Income Amount"
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditEmpOtherIncome").validate({
		rules: {
			employee_id: {
				required: true
			},
			payroll_period_id: {
				required: true
			},
			income_amount: {
				required: true
			}
		},
		messages: {
			employee_id: {
				required: "Select Employee Name"
			},
			payroll_period_id: {
				required: "Select Payroll Period Name"
			},
			income_amount: {
				required: "Enter Income Amount"
			}
		}
	});

});
$("#payroll_emp_other_income_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#payroll_emp_other_income_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		employee_id: $("#employee_id").val(),
		payroll_period_id: $("#payroll_period_id").val(),
		income_source: $("#income_source").val(),
		income_amount: $("#income_amount").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/payroll_emp_other_income/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("PAYROLL EMPLOYEE OTHER INCOME ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				show_list_data();
			}
			else if (response.trim() == '2') {
				toastr.success("PAYROLL EMPLOYEE OTHER INCOME ADDED SUCCESSFULLY", "SUCCESS");
				$('#payroll_emp_other_income_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				$('#payroll_emp_other_income_add').trigger('reset');
				Unloading();
			}
			$('#payroll_emp_other_income_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditEmpOtherIncome").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditEmpOtherIncome").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		employee_id: $("#edit_employee_id").val(),
		payroll_period_id: $("#edit_payroll_period_id").val(),
		income_source: $("#edit_income_source").val(),
		income_amount: $("#edit_income_amount").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/payroll_emp_other_income/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("PAYROLL EMPLOYEE OTHER INCOME UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditEmpOtherIncome").modal("hide");
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
			"sAjaxSource": root_domain + hrms_domain + 'app/payroll_emp_other_income/',
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
			url: root_domain + hrms_domain + 'app/payroll_emp_other_income/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("PAYROLL EMPLOYEE OTHER INCOME DELETE SUCCESSFULLY", "SUCCESS");
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
		url: root_domain + hrms_domain + 'app/payroll_emp_other_income/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#ModalEditEmpOtherIncome").modal("show");
			$("#edit_id").val(id);
			$("#edit_series_id").val(obj.series_id);
			$("#edit_employee_id").select2('val',obj.employee_id);
			$("#edit_payroll_period_id").select2('val',obj.payroll_period_id);
			$("#edit_income_source").val(obj.income_source);
			$("#edit_income_amount").val(obj.income_amount);
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
		url: root_domain + hrms_domain + 'app/payroll_emp_other_income/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL EMPLOYEE OTHER INCOME STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			show_list_data();
		}
	});
	Unloading();
}
