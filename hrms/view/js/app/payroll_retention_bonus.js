var datatable;
$(document).ready(function () {
	show_list_data();  

	// validate vendor add form on keyup and submit
	$("#payroll_retention_bonus_add").validate({
		rules: {
			employee_id: {
				required: true,
			},
			bonus_payment_date: {
				required: true,
			},
			bonus_amount: {
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
			bonus_payment_date: {
				required: "Select Bonus Payment Date",
			},
			bonus_amount: {
				required: "Enter Bonus Amount",
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
	$("#FormEditPayrollRetention").validate({
		rules: {
			employee_id: {
				required: true,
			},
			bonus_payment_date: {
				required: true,
			},
			bonus_amount: {
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
			bonus_payment_date: {
				required: "Select Bonus Payment Date",
			},
			bonus_amount: {
				required: "Enter Bonus Amount",
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
$("#payroll_retention_bonus_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#payroll_retention_bonus_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		series_id: $("#series_id").val(),
		employee_id: $("#employee_id").val(),
		bonus_payment_date: $("#bonus_payment_date").val(),
		bonus_amount: $("#bonus_amount").val(),
		salary_component_id: $("#salary_component_id").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/payroll_retention_bonus/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("PAYROLL RETENTION BONUS ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				show_list_data();
			}
			else if (response.trim() == '2') {
				toastr.success("PAYROLL RETENTION BONUS ADDED SUCCESSFULLY", "SUCCESS");
				$('#payroll_retention_bonus_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				$('#payroll_retention_bonus_add').trigger('reset');
				Unloading();
			}
			$('#payroll_retention_bonus_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditPayrollRetention").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditPayrollRetention").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		series_id: $("#edit_series_id").val(),
		employee_id: $("#edit_employee_id").val(),
		bonus_payment_date: $("#edit_bonus_payment_date").val(),
		bonus_amount: $("#edit_bonus_amount").val(),
		salary_component_id: $("#edit_salary_component_id").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/payroll_retention_bonus/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("PAYROLL RETENTION BONUS UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditPayrollRetention").modal("hide");
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
			"sAjaxSource": root_domain + hrms_domain + 'app/payroll_retention_bonus/',
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
			url: root_domain + hrms_domain + 'app/payroll_retention_bonus/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("PAYROLL RETENTION BONUS DELETE SUCCESSFULLY", "SUCCESS");
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
		url: root_domain + hrms_domain + 'app/payroll_retention_bonus/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#ModalEditPayrollRetention").modal("show");
			$("#edit_id").val(id);
			$("#edit_series_id").val(obj.series_id);
			$("#edit_employee_id").select2('val',obj.employee_id);
			$("#edit_bonus_payment_date").val(obj.bonus_payment_date);
			$("#edit_bonus_amount").val(obj.bonus_amount);
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
		url: root_domain + hrms_domain + 'app/payroll_retention_bonus/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL RETENTION BONUS STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			show_list_data();
		}
	});
	Unloading();
}
