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
			"sEmptyTable": "NO LEAVE ENCASHMENT ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + hrms_domain + 'app/hrms_leave_encashment/',
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
	$("#leave_encashment_add").validate({
		rules: {
			employee_id: {
				required: true
			},
			leave_period_id: {
				required: true
			},
			leave_type_id: {
				required: true
			},
			encashment_date: {
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
			leave_period_id: {
				required: "Select Leave Period"
			},
			leave_type_id: {
				required: "Select Leave Type"
			},
			encashment_date: {
				required: "Select Encashment Date"
			},
			status: {
				required: "Select Status"
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditLeaveEncashment").validate({
		rules: {
			edit_employee_id: {
				required: true
			},
			edit_leave_period_id: {
				required: true
			},
			edit_leave_type_id: {
				required: true
			},
			edit_encashment_date: {
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
			edit_leave_period_id: {
				required: "Select Period"
			},
			edit_leave_type_id: {
				required: "Select Leave Type"
			},
			edit_encashment_date: {
				required: "Enter Annual Allocation"
			},
			edit_status: {
				required: "Select Status"
			}
		}
	});

});
$("#leave_encashment_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#leave_encashment_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		employee_id: $("#employee_id").val(),
		leave_period_id: $("#leave_period_id").val(),
		leave_type_id: $("#leave_type_id").val(),
		encashment_date: $("#encashment_date").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_leave_encashment/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			response = obj.res;
			if (response.trim() == '1') {
				toastr.success("LEAVE ENCASHMENT ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
			}
			else if (response.trim() == '2') {
				toastr.success("LEAVE ENCASHMENT ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-hrms-leave-encashment").modal("hide");
				$('#employee_id').append('<option value=' + obj.employee_id + '>' + obj.employee_id + '</option>');
				$("#employee_id").trigger('change')
				$('#employee_id').select2("val", obj.employee_id);
				$('#leave_period_id').append('<option value=' + obj.leave_period_id + '>' + obj.leave_period_id + '</option>');
				$("#leave_period_id").trigger('change')
				$('#leave_period_id').select2("val", obj.leave_period_id);
				$('#leave_type_id').append('<option value=' + obj.leave_type_id + '>' + obj.leave_type_id + '</option>');
				$("#leave_type_id").trigger('change')
				$('#leave_type_id').select2("val", obj.leave_type_id);
				$('#leave_encashment_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				$("#bs-example-modal-hrms-leave-encashment").modal("hide");
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-hrms-leave-encashment").modal("hide");
				$('#leave_encashment_add').trigger('reset');
				Unloading();
			}
			$('#leave_encashment_add').trigger('reset');
			$('#employee_id').select2("val", employee_id);
			$('#leave_period_id').select2("val", leave_period_id);
			$('#leave_type_id').select2("val", leave_type_id);
			$("#status").select2("val", status);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditLeaveEncashment").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditLeaveEncashment").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		employee_id: $("#edit_employee_id").val(),
		leave_period_id: $("#edit_leave_period_id").val(),
		leave_type_id: $("#edit_leave_type_id").val(),
		encashment_date: $("#edit_encashment_date").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_leave_encashment/',
		type: "POST",
		data: form_data,
		success: function (response) {
			if (response.trim() == '1') {
				toastr.success("LEAVE ENCASHMENT UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditLeaveEncashment").modal("hide");
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
function delete_leave_encashment(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_encashment/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("LEAVE ENCASHMENT DELETE SUCCESSFULLY", "SUCCESS");
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
function edit_leave_encashment(id) {
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_leave_encashment/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditLeaveEncashment").modal("show");
			$("#edit_id").val(id);
			$("#edit_employee_id").select2("val", obj.employee_id);
			$("#edit_leave_period_id").select2("val", obj.leave_period_id);
			$("#edit_leave_type_id").select2("val", obj.leave_type_id);
			$("#edit_encashment_date").val(obj.encashment_date);
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
		url: root_domain + hrms_domain + 'app/hrms_leave_encashment/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("LEAVE ENCASHMENT CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}