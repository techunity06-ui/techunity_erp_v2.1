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
			"sEmptyTable": "NO SHIFT TYPE ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + 'app/shift_type/',
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
	$("#shift_type_add").validate({
		rules: {
			shift_type_name: {
				required: true,
				minlength: 5
			},
			shift_start_time: {
				required: true
			},
			shift_end_time: {
				required: true
			},
			status: {
				required: true
			}
		},
		messages: {
			shift_type_name: {
				required: "Enter Shift Type Name",
				minlength: "Your Shift Type Name must consist of at least 5 characters"
			},
			shift_start_time: {
				required: "Select Shift Start Time"
			},
			shift_end_time: {
				required: "Select Shift End Time"
			},
			status: {
				required: "Select Status"
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditShiftType").validate({
		rules: {
			edit_shift_type_name: {
				required: true,
				minlength: 5
			},
			edit_shift_start_time: {
				required: true
			},
			edit_shift_end_time: {
				required: true
			},
			edit_status: {
				required: true
			}


		},
		messages: {
			edit_shift_type_name: {
				required: "Enter Shift Type Name",
				minlength: "Your Shift Type Name must consist of at least 5 characters"
			},
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
$("#shift_type_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#shift_type_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		shift_type_name: $("#shift_type_name").val(),
		shift_start_time: $("#shift_start_time").val(),
		shift_end_time: $("#shift_end_time").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + 'app/shift_type/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			response = obj.res;
			if (response.trim() == '1') {
				toastr.success("SHIFT TYPE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
			}
			else if (response.trim() == '2') {
				toastr.success("SHIFT TYPE ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-designation").modal("hide");
				$('#shift_type_add').trigger('reset');
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
				$('#shift_type_add').trigger('reset');
				Unloading();
			}
			$('#shift_type_add').trigger('reset');
			$("#status").select2("val", status);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditShiftType").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditShiftType").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		shift_type_name: $("#edit_shift_type_name").val(),
		shift_start_time: $("#edit_shift_start_time").val(),
		shift_end_time: $("#edit_shift_end_time").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + 'app/shift_type/',
		type: "POST",
		data: form_data,
		success: function (response) {
			if (response.trim() == '1') {
				toastr.success("SHIFT TYPE UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditShiftType").modal("hide");
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
			url: root_domain + 'app/shift_type/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("SHIFT TYPE DELETE SUCCESSFULLY", "SUCCESS");
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
		url: root_domain + 'app/shift_type/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditShiftType").modal("show");
			$("#edit_id").val(id);
			$("#edit_shift_type_name").val(obj.shift_type_name);
			$("#edit_shift_start_time").val(obj.shift_start_time);
			$("#edit_shift_end_time").val(obj.shift_end_time);
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
		url: root_domain + 'app/shift_type/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("SHIFT TYPE STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}