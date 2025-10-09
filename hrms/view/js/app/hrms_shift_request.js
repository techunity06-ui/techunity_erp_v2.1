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
			"sEmptyTable": "NO SHIFT REQUEST ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + hrms_domain + 'app/hrms_shift_request/',
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
	$("#shift_request_add").validate({
		rules: {
			employee_id: {
				required: true
			},
			shift_type_id: {
				required: true,
			},
			shift_from_date: {
				required: true
			},
			shift_to_date: {
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
			shift_type_id: {
				required: "Select Shift Type"
			},
			shift_from_date: {
				required: "Select Shift From Date"
			},
			shift_to_date: {
				required: "Select Shift To Date"
			},
			status: {
				required: "Select Status"
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditShiftRequest").validate({
		rules: {
			edit_employee_id: {
				required: true
			},
			edit_shift_type_id: {
				required: true
			},
			edit_shift_from_date: {
				required: true
			},
			edit_shift_to_date: {
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
			edit_shift_type_id: {
				required: "Select Shift Type"
			},
			edit_shift_from_date: {
				required: "Select Shift From Date"
			},
			edit_shift_to_date: {
				required: "Select Shift To Date"
			},
			edit_status: {
				required: "Select Status"
			}
		}
	});

});
$("#shift_request_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#shift_request_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var employee_id = $("#employee_id").val();
	var shift_type_id = $("#shift_type_id").val();
	var form_data = {
		employee_id: employee_id,
		shift_type_id: shift_type_id,
		shift_from_date: $("#shift_from_date").val(),
		shift_to_date: $("#shift_to_date").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_shift_request/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			response = obj.res;
			if (response.trim() == '1') {
				toastr.success("SHIFT REQUEST ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
			}
			else if (response.trim() == '2') {
				toastr.success("SHIFT REQUEST ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-designation").modal("hide");
				$('#employee_id').append('<option value=' + obj.employee_id + '>' + obj.employee_name + '</option>');
				$("#employee_id").trigger('change')
				$('#employee_id').select2("val", obj.employee_id);
				$('#shift_type_id').append('<option value=' + obj.shift_type_id + '>' + obj.shift_type_name + '</option>');
				$("#shift_type_id").trigger('change')
				$('#shift_type_id').select2("val", obj.shift_type_id);
				$('#shift_request_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				$("#bs-example-modal-shift-type").modal("hide");
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-shift-type").modal("hide");
				$('#shift_request_add').trigger('reset');
				Unloading();
			}
			$('#shift_request_add').trigger('reset');
			$('#employee_id').select2("val", employee_id);
			$('#shift_type_id').select2("val", shift_type_id);
			$("#status").select2("val", status);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditShiftRequest").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditShiftRequest").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		employee_id: $("#edit_employee_id").val(),
		shift_type_id: $("#edit_shift_type_id").val(),
		shift_from_date: $("#edit_shift_from_date").val(),
		shift_to_date: $("#edit_shift_to_date").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_shift_request/',
		type: "POST",
		data: form_data,
		success: function (response) {
			if (response.trim() == '1') {
				toastr.success("SHIFT REQUEST UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditShiftRequest").modal("hide");
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
function delete_catalog(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_shift_request/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("SHIFT REQUEST DELETE SUCCESSFULLY", "SUCCESS");
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
function edit_test(id) {
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_shift_request/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditShiftRequest").modal("show");
			$("#edit_id").val(id);
			$("#edit_employee_id").select2("val", obj.employee_id);
			$("#edit_shift_type_id").select2("val", obj.shift_type_id);
			$("#edit_shift_from_date").val(obj.shift_from_date);
			$("#edit_shift_to_date").val(obj.shift_to_date);
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
		url: root_domain + hrms_domain + 'app/hrms_shift_request/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("SHIFT REQUEST STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}