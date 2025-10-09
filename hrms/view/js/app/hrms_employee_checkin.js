var datatable;
$(document).ready(function () {
	datatable = $("#dynamic-table").dataTable({
		"bStateSave": true,
		"bAutoWidth": false,
		"bFilter": true,
		"bSort": true,
		"bProcessing": true,
		"bServerSide": true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO EMPLOYEE CHECKIN ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + hrms_domain + 'app/hrms_employee_checkin/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "fetch" });
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted        

	// validate vendor add form on keyup and submit
	$("#employee_checkin_add").validate({
		rules: {
			employee_id: {
				required: true
			},
			log_type: {
				required: true
			},
			log_time: {
				required: true
			},
			location_device_detail: {
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
			log_type: {
				required: "Select Log Type"
			},
			log_time: {
				required: "Select Log Time"
			},
			location_device_detail: {
				required: "Enter Location Device Detial"
			},
			status: {
				required: "Select Status"
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditEmployeeCheckIn").validate({
		rules: {
			edit_employee_id: {
				required: true
			},
			edit_log_type: {
				required: true
			},
			edit_log_time: {
				required: true
			},
			edit_location_device_detail: {
				required: true
			},
			edit_status: {
				required: true
			}
		},
		messages: {
			edit_employee_id: {
				required: "Select Employee"
			},
			edit_log_type: {
				required: "Select Log Type"
			},
			edit_log_time: {
				required: "Select Log Time"
			},
			edit_location_device_detail: {
				required: "Enter Location Device Detail"
			},
			edit_status: {
				required: "Select Status"
			}
		}
	});

});
$("#employee_checkin_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#employee_checkin_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		employee_id: $("#employee_id").val(),
		log_type: $("#log_type").val(),
		log_time: $("#log_time").val(),
		location_device_detail: $("#location_device_detail").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_employee_checkin/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			response = obj.res;
			if (response.trim() == '1') {
				toastr.success("EMPLOYEE CHECKIN ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
			}
			else if (response.trim() == '2') {
				toastr.success("EMPLOYEE CHECKIN ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-hrms-employee-checkin").modal("hide");
				$('#employee_id').append('<option value=' + obj.employee_id + '>' + obj.employee_id + '</option>');
				$("#employee_id").trigger('change')
				$('#employee_id').select2("val", obj.employee_id);
				$('#log_type').append('<option value=' + obj.log_type + '>' + obj.log_type + '</option>');
				$("#log_type").trigger('change')
				$('#log_type').select2("val", obj.log_type);
				$('#employee_checkin_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				$("#bs-example-modal-hrms-employee-checkin").modal("hide");
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-hrms-employee-checkin").modal("hide");
				$('#employee_checkin_add').trigger('reset');
				Unloading();
			}
			$('#employee_checkin_add').trigger('reset');
			$('#employee_id').select2("val", employee_id);
			$('#log_type').select2("val", log_type);
			$("#status").select2("val", status);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditEmployeeCheckIn").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditEmployeeCheckIn").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		employee_id: $("#edit_employee_id").val(),
		log_type: $("#edit_log_type").val(),
		log_time: $("#edit_log_time").val(),
		location_device_detail: $("#edit_location_device_detail").val(),
		skip_auto_attendance_flag: $("#edit_skip_auto_attendance_flag").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_employee_checkin/',
		type: "POST",
		data: form_data,
		success: function (response) {
			if (response.trim() == '1') {
				toastr.success("EMPLOYEE CHECKIN UPDATED SUCCESSFULLY", "SUCCESS");
				datatable.fnReloadAjax();
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();
			}
			$("#ModalEditEmployeeCheckIn").modal("hide");
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
function delete_employee_checkin(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_checkin/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("EMPLOYEE CHECKIN DELETE SUCCESSFULLY", "SUCCESS");
					datatable.fnReloadAjax();
					Unloading();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}
}
function edit_employee_checkin(id) {
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_checkin/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditEmployeeCheckIn").modal("show");
			$("#edit_id").val(id);
			$("#edit_employee_id").select2("val", obj.employee_id);
			$("#edit_log_type").select2("val", obj.log_type);
			$("#edit_log_time").val(obj.log_time);
			$("#edit_location_device_detail").val(obj.location_device_detail);
			$("#edit_skip_auto_attendance_flag").val(obj.skip_auto_attendance_flag),
			$("#edit_status").select2("val", obj.status);
			Unloading();
		}
	});
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_checkin/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("EMPLOYEE CHECKIN CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}