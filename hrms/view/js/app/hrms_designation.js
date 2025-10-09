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
			"sEmptyTable": "NO DESIGNATION ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + hrms_domain + 'app/hrms_designation/',
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
	$("#designation_add").validate({
		rules: {
			designation_name: {
				required: true,
				minlength: 5
			},
		},
		messages: {
			designation_name: {
				required: "Enter Designation Name",
				minlength: "Your Designation Name must consist of at least 5 characters"
			},
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditDepartment").validate({
		rules: {
			edit_designation_name: {
				required: true,
				minlength: 5
			},
		},
		messages: {
			edit_designation_name: {
				required: "Enter Designation Name",
				minlength: "Your Designation Name must consist of at least 5 characters"
			},
		}
	});

});
$("#designation_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#designation_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		designation_name: $("#designation_name").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_designation/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			response = obj.res;
			if (response.trim() == '1') {
				toastr.success("DESIGNATION ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
			}
			else if (response.trim() == '2') {
				toastr.success("DESIGNATION ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-designation").modal("hide");
				$('#designation_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				$("#bs-example-modal-designation").modal("hide");
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-designation").modal("hide");
				$('#designation_add').trigger('reset');
				Unloading();
			}
			$('#designation_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditDesignation").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditDesignation").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		designation_name: $("#edit_designation_name").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_designation/',
		type: "POST",
		data: form_data,
		success: function (response) {
			if (response.trim() == '1') {
				toastr.success("DESIGNATION UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditDesignation").modal("hide");
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
			url: root_domain + hrms_domain + 'app/hrms_designation/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("DESIGNATION DELETE SUCCESSFULLY", "SUCCESS");
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
		url: root_domain + hrms_domain + 'app/hrms_designation/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditDesignation").modal("show");
			$("#edit_id").val(id);
			$("#edit_designation_name").val(obj.designation_name);
			$("#edit_status").select2('val',obj.status);
			Unloading();
		}
	});
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_designation/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("DESIGNATION CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}