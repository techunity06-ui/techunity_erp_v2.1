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
			"sEmptyTable": "NO LEAVE PERIOD ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + hrms_domain + 'app/hrms_leave_period/',
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
	$("#leave_period_add").validate({
		rules: {
			leave_period_from_date: {
				required: true
			},
			leave_period_to_date: {
				required: true
			},
			status: {
				required: true
			}
		},
		messages: {
			leave_period_from_date: {
				required: "Select Leave Period From Date"
			},
			leave_period_to_date: {
				required: "Select Leave Period To Date"
			},
			status: {
				required: "Select Status"
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditLeavePeriod").validate({
		rules: {
			edit_leave_period_from_date: {
				required: true
			},
			edit_leave_period_to_date: {
				required: true
			},
			edit_status: {
				required: true
			}
		},
		messages: {
			edit_shift_start_time: {
				required: "Select Shift Start Time"
			},
			edit_shift_end_time: {
				required: "Select Shift End Time"
			},
			edit_status: {
				required: "Select Status"
			}
		}
	});

});
$("#leave_period_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#leave_period_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		holiday_list_id: $("#holiday_list_id").val(),
		leave_period_from_date: $("#leave_period_from_date").val(),
		leave_period_to_date: $("#leave_period_to_date").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_leave_period/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			response = obj.res;
			if (response.trim() == '1') {
				toastr.success("LEAVE PERIOD ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
			}
			else if (response.trim() == '2') {
				toastr.success("LEAVE PERIOD ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-hrms-leave-type").modal("hide");
				$('#holiday_list_id').append('<option value=' + obj.holiday_list_id + '>' + obj.holiday_list_id + '</option>');
				$("#holiday_list_id").trigger('change')
				$('#holiday_list_id').select2("val", obj.holiday_list_id);
				$('#leave_period_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				$("#bs-example-modal-hrms-leave-type").modal("hide");
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-hrms-leave-type").modal("hide");
				$('#leave_period_add').trigger('reset');
				Unloading();
			}
			$('#leave_period_add').trigger('reset');
			$('#holiday_list_id').select2("val", holiday_list_id);
			$("#status").select2("val", status);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditLeavePeriod").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditLeavePeriod").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		holiday_list_id: $("#edit_holiday_list_id").val(),
		leave_period_from_date: $("#edit_leave_period_from_date").val(),
		leave_period_to_date: $("#edit_leave_period_to_date").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_leave_period/',
		type: "POST",
		data: form_data,
		success: function (response) {
			if (response.trim() == '1') {
				toastr.success("LEAVE PERIOD UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditLeavePeriod").modal("hide");
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
function delete_leave_period(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_period/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("LEAVE PERIOD DELETE SUCCESSFULLY", "SUCCESS");
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
function edit_leave_period(id) {
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_leave_period/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditLeavePeriod").modal("show");
			$("#edit_id").val(id);
			$("#edit_holiday_list_id").select2("val", obj.holiday_list_id);
			$("#edit_leave_period_from_date").val(obj.leave_period_from_date);
			$("#edit_leave_period_to_date").val(obj.leave_period_to_date);
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
		url: root_domain + hrms_domain + 'app/hrms_leave_period/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("LEAVE PERIOD CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}
