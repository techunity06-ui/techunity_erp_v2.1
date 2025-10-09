$(document).ready(function () {
	show_data_complain();
	$('#old_sp_part').val('yes');
});

$(".btn_close").click(function () {
	$("label.error").hide();
});

function hideAllEmployee(x) {
	if (x == '6' || x == '4') {
		$('#emp_part_id').hide();
		$('#f_emp').select2("val", "");
		if (x == '4') {
			$('#cust_fb_id').show();
		} else {
			$('#cust_fb_id').hide();
		}
	}
	else {
		$('#emp_part_id').show();
		$('#cust_fb_id').hide();
	}
}

/* 
	Author : JS 10-04-2024 
	Change : Remove Mendatory part
**/
$("#comp_not_done_detail_add").validate({
	ignore: "",
	rules: {
		change_status: {
			required: true
		},
		f_emp: {
			required: function (element) {

				var change_status = $.trim($('#change_status').val());
				return change_status.length > 0 && change_status == '2' || change_status == '3';
			}
		},
		// comp_sp_part_status_count:{

		// 	required:true
		// },
		comp_sp_approve_request: {
			required: true
		},
	},
	messages: {
		change_status: {
			required: "Select Status"
		},
		f_emp: {
			required: "Select Employee"
		},
		// comp_sp_part_status_count:{

		// 	required: "Enter Atleast One Spare Part"
		// },

		comp_sp_approve_request: {
			required: "Approve New Spare Part Request"
		}

	}
});

$("#comp_not_done_detail_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#comp_not_done_detail_add").valid()) {

		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");

	var form_data = {
		f_action: $("#change_status").val(),
		f_remark: $("#f_remark").val(),
		f_emp: $("#f_emp").val(),
		complain_id: $("#comp_id_hid").val(),
		old_sp_part: $('#old_sp_part').val(),
		cust_fb_id: $('#cust_fb_id').val(),
		comp_sp_part_status: $('#comp_sp_part_status').val(),
		mode: 'add',
		is_ajax: 1
	};
	$('#submit_btn').prop("disabled", true);

	$.ajax({
		cache: false,
		url: root_domain + service_domain + 'app/complaint_reassign/',
		type: "POST",
		data: form_data,
		success: function (response) {

			var resp = JSON.parse(response);
			var msg = resp.res;
			if (msg.trim() == '1') {
				toastr.success("COMPLAINT DONE SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location = root_domain + service_domain + 'complaint_list';
			}
			else if (msg.trim() == '2') {
				toastr.success("COMPLAINT DONE SUCCESSFULLY", "SUCCESS");
				$("#modal-complain-add").modal("hide");
				Unloading();
			}
			else if (msg.trim() == '0') {
				toastr.error("SPARE PARTS NOT INSERTED", "ERROR");
				Unloading();
			}
			else if (msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();
			}
			$('#comp_status_add').trigger('reset');
			$('#submit_btn').prop("disabled", false);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});

function add_field() {
	if (!$("#comp_product_id").val()) {
		toastr.warning("Select Product", "ERROR");
		$('#comp_product_id').select2('focus');
		return false;
	}
	else if (!$("#product_id").val()) {
		toastr.warning("Select Product", "ERROR");
		$('#product_id').select2('focus');
		return false;
	}
	else if (!$("#product_qty").val()) {
		toastr.warning("Enter Quantity", "ERROR");
		$('#product_qty').focus();
		return false;
	}
	else if (!$("#product_rate").val()) {
		toastr.warning("Enter Rate", "ERROR");
		$('#product_rate').focus();
		return false;
	}
	else if (!$("#sp_free").val()) {
		toastr.warning("Choose Payment Status", "ERROR");
		$('#sp_free').focus();
		return false;
	}
	else if (!$("#sp_sent").val()) {
		toastr.warning("Choose Spare Part Sent Status", "ERROR");
		$('#sp_sent').focus();
		return false;
	}
	else if (!$("#old_sp_sent").val()) {
		toastr.warning("Choose Old Spare Sent Status", "ERROR");
		$('#old_sp_sent').focus();
		return false;
	}

	if ($('#sp_sent').val() == 'yes') {
		if (!$("#courier_name").val()) {
			toastr.warning("Enter Courier Name", "ERROR");
			$('#courier_name').focus();
			return false;
		}
		else if (!$("#courier_no").val()) {
			toastr.warning("Enter Courier No", "ERROR");
			$('#courier_no').focus();
			return false;
		}
		else if (!$("#courier_del_date").val()) {
			toastr.warning("Enter Delivery Date", "ERROR");
			$('#courier_del_date').focus();
			return false;
		}
	}

	var conf_form = {
		mode: 'spare_part_add',
		comp_product_id: $("#comp_product_id").val(),
		product_id: $("#product_id").val(),
		product_qty: $("#product_qty").val(),
		product_rate: $("#product_rate").val(),
		product_amt: $("#product_amt").val(),
		courier_no: $("#courier_no").val(),
		courier_name: $("#courier_name").val(),
		courier_del_date: $("#courier_del_date").val(),
		fl_status: $("#change_status").val(),
		cust_id_hid: $("#cust_id_hid").val(),
		comp_id_hid: $("#comp_id_hid").val(),
		sp_free: $("#sp_free").val(),
		sp_sent: $("#sp_sent").val(),
		old_sp_sent: $("#old_sp_sent").val(),
		edit_id: $("#edit_id").val()
	};
	$('#addrow').prop("disabled", true);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + service_domain + 'app/complaint_reassign/',
		data: conf_form,
		success: function (response) {
			$("#product_id").select2("val", "");
			$('#sp_free').val('');
			$('#product_qty').val('');
			$('#product_rate').val('');
			$('#product_amt').val('');
			$('#addrow').val('Add');
			$('#comp_sp_part_status_count').val('1');
			Unloading();
			$('#addrow').prop("disabled", false);
			show_data_complain();

			var comp_sp_approve_request = (resp.comp_sp_approve_request && resp.comp_sp_approve_request > 0) ? '' : resp.comp_sp_approve_request;
			$("#comp_sp_approve_request").val(comp_sp_approve_request);
		}
	});
}

function show_data_complain() {
	var comp_id = $('#comp_id_hid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + service_domain + 'app/complaint_reassign/',
		data: { mode: "load_complain_data", complaint_id: comp_id },
		success: function (resp) {
			$('#complaint_pro_data').html(resp);
			Unloading();
		}
	});
}

function edit_data_complain(complaint_trn_id) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + service_domain + 'app/complaint_reassign/',
		data: { mode: "preedit", complaint_trn_id: complaint_trn_id },
		success: function (response) {

			var resp = jQuery.parseJSON(response);
			$("#comp_product_id").select2("val", resp.comp_product_id);
			get_bom_product(resp.comp_product_id, resp.s_product);
			//$("#product_id").html(resp.pro_resp_html);
			//$("#product_id").select2("val",resp.s_product);
			//alert(resp.s_product);

			$("#product_qty").val(resp.s_qty);
			$("#product_rate").val(resp.s_rate);
			$("#product_amt").val(resp.s_amount);
			$("#sp_free").val(resp.s_paid_status);
			$("#courier_name").val(resp.s_courier_name);
			$("#courier_no").val(resp.s_courier_no);
			if (resp.s_courier_del_date == '0000-00-00' || resp.s_courier_del_date == '1970-01-01') {
				$("#courier_del_date").val();
			}
			else {
				var nowDate = new Date(resp.s_courier_del_date);
				var date = ("0" + nowDate.getDate()).slice(-2);
				var month = ("0" + (nowDate.getMonth() + 1)).slice(-2);
				$("#courier_del_date").val(date + "-" + month + '-' + nowDate.getFullYear());
			}
			$("#sp_sent").val(resp.sp_sent_status);
			$("#old_sp_sent").val(resp.sp_old_status);
			$("#edit_id").val(complaint_trn_id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}

function request_data_complain(complaint_trn_id) {
	$('#modal-request-spare-part').modal('show');
	show_request_spare_part(complaint_trn_id);
}

function show_request_spare_part(sp_id) {
	$.ajax({
		type: "POST",
		url: root_domain + service_domain + 'app/complaint_reassign/',
		data: { mode: "show_request_spare_part", sp_id: sp_id },
		success: function (response) {
			$('#request_spare_part_form_id').html(response);
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true,
				startDate: today
			});
			Unloading();
		}
	});
}

function add_spare_request_data() {
	if (!$("#s_rate_p").val()) {
		toastr.warning("Enter rate", "ERROR");
		$('#s_rate_p').focus();
		return false;
	}
	if (!$("#s_sp_status_p").val()) {
		toastr.warning("Select Spare Part Status", "ERROR");
		$('#s_sp_status_p').focus();
		return false;
	}
	if (!$("#s_old_sp_status_p").val()) {
		toastr.warning("Select Spare Part Status", "ERROR");
		$('#s_old_sp_status_p').focus();
		return false;
	}

	var s_rate_p = $('#s_rate_p').val();
	var s_amount_p = $('#s_amount_p').val();
	var s_cname_p = $('#s_cname_p').val();
	var s_cno_p = $('#s_cno_p').val();
	var s_cd_p = $('#s_cd_p').val();
	var s_sp_status_p = $('#s_sp_status_p').val();
	var s_old_sp_status_p = $('#s_old_sp_status_p').val();
	var s_sp_id = $('#s_sp_id').val();
	var s_comp_id_p = $('#s_comp_id_p').val();
	var s_cust_id_p = $('#s_cust_id_p').val();
	var s_comp_product_id_p = $('#s_comp_product_id_p').val();
	var s_qty_p = $('#s_qty_p').val();
	var s_product_p = $('#s_product_p').val();

	$.ajax({
		type: "POST",
		url: root_domain + service_domain + 'app/complaint_reassign/',
		data: { mode: "add_spare_request_data", s_rate_p: s_rate_p, s_amount_p: s_amount_p, s_cname_p: s_cname_p, s_cno_p: s_cno_p, s_cd_p: s_cd_p, s_sp_status_p: s_sp_status_p, s_old_sp_status_p: s_old_sp_status_p, s_sp_id: s_sp_id, s_comp_id_p: s_comp_id_p, s_cust_id_p: s_cust_id_p, s_comp_product_id_p: s_comp_product_id_p, s_qty_p: s_qty_p, s_product_p: s_product_p },
		success: function (response) {
			$('#modal-request-spare-part').modal('hide');
			show_data_complain();
			Unloading();
			var data = jQuery.parseJSON(response);
			var comp_sp_approve_request = (data.comp_sp_approve_request && data.comp_sp_approve_request > 0) ? '' : data.comp_sp_approve_request;
			$("#comp_sp_approve_request").val(comp_sp_approve_request);
		}
	});
}

function delete_data_complain(complaint_trn_id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + service_domain + 'app/complaint_reassign/',
			data: { mode: "delete_data", complaint_trn_id: complaint_trn_id },
			success: function (response) {
				var data = jQuery.parseJSON(response);
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data_complain();
					Unloading();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				var comp_sp_approve_request = (data.comp_sp_approve_request && data.comp_sp_approve_request > 0) ? '' : data.comp_sp_approve_request;
				$("#comp_sp_approve_request").val(comp_sp_approve_request);
			}
		});
	}
}

function get_spare_part_complain(complaint_id) {
	if (!complaint_id) {
		toastr.info("Please Select Complain First !!!", "INFO");
		return false;
	}
	$('#modal-complain-history-spare-part').modal('show');
	$('#comp_id').val(complaint_id);
	show_complain_history_spare_part_datatable();
}

function show_complain_history_spare_part_datatable() {
	var comp_id = $('#comp_id').val();
	datatable = $("#table_complain_history_spare_part").dataTable({
		"bAutoWidth": true,
		"bFilter": false,
		"bSort": true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide": true,
		"bPaginate": false,
		"bInfo": false,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[5, 10, 20, 30, 50], [5, 10, 20, 30, 50]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + service_domain + 'app/complaint_reassign/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "show_complain_history_spare_part" }, { "name": "complain_id", "value": comp_id });
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');
}

function getProductAmount() {
	var product_qty = Number($('#product_qty').val());
	var product_rate = Number($('#product_rate').val());
	var product_amt = product_qty * product_rate;
	$('#product_amt').val(product_amt);
}

function getComplainProduct() {
	var comp_id = $('#comp_id_hid').val();
	$('#table_complain_product').modal('show');
	$('#comp_id').val(comp_id);
	show_complain_product_datatable();
}

function show_complain_product_datatable() {
	var comp_id = $('#comp_id_hid').val();
	datatable = $("#table-product").dataTable({
		"bAutoWidth": true,
		"bFilter": false,
		"bSort": true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide": true,
		"bPaginate": false,
		"bInfo": false,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[5, 10, 20, 30, 50], [5, 10, 20, 30, 50]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + service_domain + 'app/complaint_reassign/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "show_complain_product" }, { "name": "complain_id", "value": comp_id });
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');

}

function getProductRate(pr) {
	$.ajax({
		type: 'POST',
		url: root_domain + service_domain + 'app/complaint_reassign/',
		data: { mode: "get_product_rate", product_id: pr },
		success: function (resp) {
			$('#product_rate').val(resp);
			Unloading();
		}
	})
}

function get_amount_model() {
	var s_rate_p = Number($('#s_rate_p').val());
	var s_qty_p = Number($('#s_qty_p').val());
	var s_amount_p = s_rate_p * s_qty_p;
	$('#s_amount_p').val(s_amount_p);
}

function get_bom_product(com_product, s_product) {
	$.ajax({
		type: 'POST',
		url: root_domain + service_domain + 'app/complaint/',
		data: { mode: "get_bom_product", com_product: com_product },
		success: function (resp) {
			$('#product_id').html(resp);
			$("#product_id").select2("val", s_product);
			Unloading();
		}
	})
}