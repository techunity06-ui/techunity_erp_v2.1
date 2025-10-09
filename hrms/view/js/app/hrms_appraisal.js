var datatable;
$(document).ready(function () {
	show_list_data();  
	// validate vendor add form on keyup and submit
	$("#appraisal_add").validate({
		rules: {
			appraisal_template_id: {
				required: true,
			}
		},
		messages: {
			appraisal_template_id: {
				required: "Select Appraisal Template",
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditAppraisal").validate({
		rules: {
			appraisal_template_id: {
				required: true,
			}
		},
		messages: {
			appraisal_template_id: {
				required: "Select Appraisal Template",
			}
		}
	});

});
$("#appraisal_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#appraisal_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		series_id: $("#series_id").val(),
		appraisal_template_id: $("#appraisal_template_id").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_appraisal/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("HRMS APPRAISAL ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				show_list_data();
			}
			else if (response.trim() == '2') {
				toastr.success("HRMS APPRAISAL ADDED SUCCESSFULLY", "SUCCESS");
				$('#appraisal_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				$('#appraisal_add').trigger('reset');
				Unloading();
			}
			$('#appraisal_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditAppraisal").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditAppraisal").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		series_id: $("#edit_series_id").val(),
		appraisal_template_id: $("#edit_appraisal_template_id").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_appraisal/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("HRMS APPRAISAL UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditAppraisal").modal("hide");
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});

function show_list_data() {
	
	var datatable = $("#dynamic-table").dataTable({
			"bStateSave": true,
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
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_appraisal/',
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
			url: root_domain + hrms_domain + 'app/hrms_appraisal/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("HRMS APPRAISAL DELETE SUCCESSFULLY", "SUCCESS");
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
		url: root_domain + hrms_domain + 'app/hrms_appraisal/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAppraisal").modal("show");
			$("#edit_id").val(id);
			$("#edit_series_id").val(obj.series_id);
			$("#edit_appraisal_template_id").select2('val',obj.appraisal_template_id);
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
		url: root_domain + hrms_domain + 'app/hrms_appraisal/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS APPRAISAL STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			show_list_data();
		}
	});
	Unloading();
}
