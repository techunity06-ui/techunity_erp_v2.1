var datatable;
$(document).ready(function () {
	show_list_data();  

	// validate vendor add form on keyup and submit
	$("#payroll_period_add").validate({
		rules: {
			payroll_period_name: {
				required: true,
				minlength: 2
			},
			payroll_start_date: {
				required: true
			},
			payroll_end_date: {
				required: true
			},
		},
		messages: {
			payroll_period_name: {
				required: "Enter Payroll Period Name",
				minlength: "Your Payroll Period Name must consist of at least 2 characters"
			},
			payroll_start_date: {
				required: "Select Payroll Period Start Date",
			},
			payroll_end_date: {
				required: "Select Payroll Period End Date",
			},
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditPayrollPeriod").validate({
		rules: {
			payroll_period_name: {
				required: true,
				minlength: 2
			},
			payroll_start_date: {
				required: true
			},
			payroll_end_date: {
				required: true
			},
		},
		messages: {
			payroll_period_name: {
				required: "Enter Payroll Period Name",
				minlength: "Your Payroll Period Name must consist of at least 2 characters"
			},
			payroll_start_date: {
				required: "Select Payroll Period Start Date",
			},
			payroll_end_date: {
				required: "Select Payroll Period End Date",
			},
		}
	});

});
$("#payroll_period_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#payroll_period_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		payroll_period_name: $("#payroll_period_name").val(),
		payroll_start_date: $("#payroll_start_date").val(),
		payroll_end_date: $("#payroll_end_date").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/payroll_period/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("PAYROLL PERIOD ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				show_list_data();
			}
			else if (response.trim() == '2') {
				toastr.success("PAYROLL PERIOD ADDED SUCCESSFULLY", "SUCCESS");
				$('#payroll_period_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				$('#payroll_period_add').trigger('reset');
				Unloading();
			}
			$('#payroll_period_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditPayrollPeriod").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditPayrollPeriod").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		payroll_period_name: $("#edit_payroll_period_name").val(),
		payroll_start_date: $("#edit_payroll_start_date").val(),
		payroll_end_date: $("#edit_payroll_end_date").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/payroll_period/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("PAYROLL PERIOD UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditPayrollPeriod").modal("hide");
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
			"sAjaxSource": root_domain + hrms_domain + 'app/payroll_period/',
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
			url: root_domain + hrms_domain + 'app/payroll_period/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("PAYROLL PERIOD DELETE SUCCESSFULLY", "SUCCESS");
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
		url: root_domain + hrms_domain + 'app/payroll_period/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#ModalEditPayrollPeriod").modal("show");
			$("#edit_id").val(id);
			$("#edit_payroll_period_name").val(obj.payroll_period_name);
			$("#edit_payroll_start_date").val(obj.payroll_start_date);
			$("#edit_payroll_end_date").val(obj.payroll_end_date);
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
		url: root_domain + hrms_domain + 'app/payroll_period/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL PERIOD STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			show_list_data();
		}
	});
	Unloading();
}
