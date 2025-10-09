//var datatable;
$(document).ready(function () {
	load_product_datatable();
	load_stock_list_datatable();
product_load();

	$("#stock_add").validate({
		rules: {
			branch_id: {
				required: true
			},
			location_id: {
				required: true
			},

		},
		messages: {
			branch_id: {
				required: "Select Branch"
			},
			location_id: {
				required: "Select location"
			},

		}
	});

	$("#import_stock").validate({
		rules: {
			excel_file: {
				required: true
			}
		},
		messages: {
			excel_file: {
				required: "Select CSV File"
			}
		}
	});


});

$("#stock_add").on('submit', function (e) {
	var product_id = $("#selected_product_id").val();
	var product_base_qty = $("#selected_product_base_qty").val();
	var product_conv_qty = $("#selected_product_conv_qty").val();
	var same_unit = $("#same_unit").val();
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#stock_add").valid()) {
		return false;
	}

	if ($("#branch_id").val() == "1000") {
		toastr.warning("PLEASE SELECT ANY ONE BRANCH", "INFO");
		return false;
	}

	if ($("#opening_stock").val() == "") {
		toastr.warning("PLEASE ENTER OPENING STOCK", "INFO");
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	/*for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}*/
	var form_data = new FormData(this);
	$.ajax({
		cache: false,
		url: root_domain + inventory_domain + 'app/stock/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData: false,
		success: function (response) {
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			if (arr.msg == '1') {
				Unloading();
				toastr.success("STOCK ADDED SUCCESSFULLY", "SUCCESS");

				var r = confirm(" Are you want to add stock in other branch ?");

				if (r) {
					$("#stock_add_modal").modal('hide');
					$('#stock_add').trigger('reset');
					show_add_stock_modal(product_id,same_unit,product_base_qty,product_conv_qty);
				} else {
					$("#stock_add_modal").modal('hide');
					$('#stock_add').trigger('reset');
				}

			}
			else if (arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (arr.msg == '-1') {
				toastr.info("STOCK ALREADY ADDED", "INFO");
				$("#stock_add_modal").modal('hide');
				$('#stock_add').trigger('reset');
				Unloading();
			}


		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});


function reload_data() {

	load_stock_list_datatable();
}

function load_stock_list_datatable() {

	var branch_id = $('#branch_id').val();
	var product_id = $('#product_id').val();
	var location_id = $('#location_id').val();
	var approve_status = $('input[name=approve_status]:Checked').val();
	datatable = $("#stock_list").dataTable({
		"bAutoWidth": false,
		"bFilter": true,
		"bSort": true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide": true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + inventory_domain + 'app/stock/',
		"fnServerParams": function (aoData) {
			aoData.push(
				{ "name": "mode", "value": "fetch_stock_list" },
				{ "name": "branch_id", "value": branch_id },
				{ "name": "product_id", "value": product_id },
				{ "name": "location_id", "value": location_id },
				{ "name": "approve_status", "value": approve_status },
			);
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		initComplete: function () {
			$('#dynamic-table thead tr').clone(true).appendTo('#dynamic-table thead');
			$('#dynamic-table thead tr:eq(1) th').each(function (i) {
				var title = $(this).text();
				$(this).html('<input type="text" placeholder="Search ' + title + '" />');

				$('input', this).on('keyup change', function () {
					if (table.column(i).search() !== this.value) {
						table
							.column(i)
							.search(this.value)
							.draw();
					}
				});
			});
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');


}
function load_product_datatable() {

	var product_type = $('#product_type').val();
	var sel_product = [];
	// $("#product_ids :selected").each(function (i) {
	// 	sel_product[i] = $(this).val();
	// });

	var product_id = $("#product_ids").val(); 


	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth": false,
		"bFilter": true,
		"bSort": true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide": true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + inventory_domain + 'app/stock/',
		"fnServerParams": function (aoData) {
			aoData.push(
				{ "name": "mode", "value": "fetch" },
				{ "name": "product_type", "value": product_type },
				{ "name": "product_id", "value": product_id }
			);
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		initComplete: function () {
			$('#dynamic-table thead tr').clone(true).appendTo('#dynamic-table thead');
			$('#dynamic-table thead tr:eq(1) th').each(function (i) {
				var title = $(this).text();
				$(this).html('<input type="text" placeholder="Search ' + title + '" />');

				$('input', this).on('keyup change', function () {
					if (table.column(i).search() !== this.value) {
						table
							.column(i)
							.search(this.value)
							.draw();
					}
				});
			});
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');


}


function load_product(type_id) {

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock/',
		data: { mode: "load_product", type_id: type_id },
		success: function (data) {
			//console.log(data);	
			$('#product_ids').html(data);
			Unloading();
		}
	});
}



function show_add_stock_modal(product_id,same_unit,product_base_qty,product_conv_qty) {
	$('#stock_add').trigger('reset');
	$("#selected_product_id").val(product_id);
	$("#selected_product_base_qty").val(product_base_qty);
	$("#selected_product_conv_qty").val(product_conv_qty);
	$("#same_unit").val(same_unit);
	$('#opening_stock_history').html("");

	check_batch_permission(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock/',
		data: {
			mode: 'get_product_process_data',
			product_id: product_id,
			same_unit:same_unit
		},
		success: function (data) {


			var arr = jQuery.parseJSON(data);
			// console.log(arr);
			$("#product_name").val(arr.product_data.product_name);
			if (arr.process_counter > 0) {
				$('.process_list').show();


			} else {
				$('.process_list').hide();

			}
			$('#process_list').empty().html(arr.html);

			$('.default-date-picker').datepicker({
			    format: 'dd-mm-yyyy',
			    autoclose: true
			});

			/*$(".form_datetime").datetimepicker({
			    format: 'dd-mm-yyyy hh:ii',
			    autoclose: true,
			    todayBtn: true,
			    pickerPosition: "bottom-left"

			});*/

			$("#stock_add_modal").modal('show');
			$("#location_id,#location,#branch_id").select2({
				width: '100%'
			});
			$("#branch_id").val(arr.product_data.branch_id).trigger('change');
			load_product_unit(product_id, arr.product_data.product_base_unit);
			Unloading();

		}
	});
}



/*
$('#branch_id').on('change',function(){
	reload_data();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/stock/',
		data: { 
			mode : 'get_godown_list',
			branch_id: $(this).val(),
		},
		success: function(data){
				
				$("#location_id,#location").empty().html(data);
				$("#location_id,#location").select2({
					  width: '100%'
				});
				Unloading();
			
			}		
		});
});*/

function product_convert_qty_main(type, id = "", edit = "") {

	var same_unit = $("#same_unit").val();
	var product_base_qty=$("#selected_product_base_qty").val();
	var product_conv_qty=$("#selected_product_conv_qty").val();

	if(same_unit == '1'){
		var base_qty_hide = $("#opening_stock" + id).val();
		$("#opening_stock_qty_hide" + id).val(base_qty_hide);
		$("#opening_stock_conv_qty" + id).val(base_qty_hide);
		$("#opening_stock_conv_qty_hide" + id).val(base_qty_hide);
	}else{

		var base_qty=$("#opening_stock" + id).val();
		var conv_qty=$("#opening_stock_conv_qty" + id).val();

		if(type=="1"){
			ret_qty=(base_qty/product_base_qty)*product_conv_qty;
		}else if(type=="2"){
			ret_qty=(conv_qty/product_conv_qty)*product_base_qty;
		}else{
			ret_qty="1";
		}

		if(type=='1'){
			$("#opening_stock_conv_qty" + id).val(ret_qty);
			$("#opening_stock_conv_qty_hide" + id).val(ret_qty);
		}else if(type===2){
			$("#opening_stock" + id).val(ret_qty);
			$("#opening_stock_qty_hide" + id).val(ret_qty);
		}else{
			$("#opening_stock" + id).val(ret_qty);
			$("#opening_stock_qty_hide" + id).val(ret_qty);
			$("#opening_stock_conv_qty" + id).val(ret_qty);
			$("#opening_stock_conv_qty_hide" + id).val(ret_qty);
		}

		return false;
		
		/*if (type == 2) {
			var conv_qty_hide = $("#opening_stock_conv_qty" + id).val();
			var s = parseFloat(conv_qty_hide);
			results = s.toFixed(5);

			var num = $("#opening_stock_conv_qty_hide" + id).val();
			var d = parseFloat(num);
			resultb = d.toFixed(5);

			// if(resultb===results){
			// 	return false;
			// }
			var product_base_qty_hide = $("#opening_stock_qty_hide" + id).val();
		} else {
			var base_qty_hide = $("#opening_stock" + id).val();
			var d = parseFloat(base_qty_hide);
			resultb = d.toFixed(5);

			var base_qty_hidess = $("#opening_stock_qty_hide" + id).val();
			var s = parseFloat(base_qty_hidess);
			results = s.toFixed(5);

			// if(resultb===results){
			// 	return false;
			// }
			var conv_qty_hide = $("#opening_stock_conv_qty" + id).val();
		}*/

		var base_qty = $("#opening_stock" + id).val();
		var conv_qty = $("#opening_stock_conv_qty" + id).val();



		//var base_qty_hide=$("#product_base_qty_hide").val();
		//var conv_qty_hide=$("#product_conv_qty_hide").val();

		//var base_qty=$("#product_base_qty").val();

		//var conv_qty=$("#product_conv_qty").val();
		var product_id = $("#selected_product_id").val();
		if (edit != "") {
			product_id = $("#edit_product_id").val();
		}
		if (product_id) {
			$.ajax({
				type: "POST",
				url: root_domain + inventory_domain + 'app/stock/',
				data: { mode: "convert_qty", type: type, base_qty: base_qty_hide, conv_qty: conv_qty_hide, product_id: product_id },
				success: function (response) {

					var arr = jQuery.parseJSON(response);
					//arr.show_qty
					//arr.hide_qty
					//alert(type);
					//alert(arr.show_qty);
					//alert(arr.hide_qty);
					// console.log(arr);
					if (type === 1) {
						$("#opening_stock_qty_hide" + id).val(base_qty);
					} else if (type === 2) {
						$("#opening_stock_conv_qty_hide" + id).val(conv_qty);
					}

					if (type === 1) {
						$("#opening_stock_conv_qty" + id).val((arr.show_qty));
						$("#opening_stock_conv_qty_hide" + id).val(arr.hide_qty);

					} else if (type === 2) {
						$("#opening_stock" + id).val((arr.show_qty));
						$("#opening_stock_qty_hide" + id).val(arr.hide_qty);

					} else {
						$("#opening_stock" + id).val((arr.show_qty));
						$("#opening_stock_qty_hide" + id).val(arr.hide_qty);
						$("#opening_stock_conv_qty" + id).val((arr.show_qty));
						$("#opening_stock_conv_qty_hide" + id).val(arr.hide_qty);
					}
				}
			});

		} else {
			toastr.warning("Select Product First", "WARNING");
			$("#opening_stock" + id).val("1");
			$("#opening_stock_qty_hide" + id).val("1");
			$("#opening_stock_conv_qty" + id).val("1");
			$("#opening_stock_conv_qty_hide" + id).val("1");
		}
	}
}


function load_product_unit(product_id, unit_id) {
	if (product_id)//tax calculation on total 
	{
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + inventory_domain + 'app/stock/',
			data: { mode: "load_product_unit", product_id: product_id },
			success: function (response) {
				var obj = jQuery.parseJSON(response);
				//alert(obj.qye);
				$('.unitid').val(obj.product_base_unit);
				$('.conv_unitid').val(obj.product_conv_unit);

				$('.unit_show').html(obj.base_unit_name);
				$('.convert_unit_show').html(obj.convert_unit_name);
				$(".convert_unit_block").show();
				if (obj.unit_status === "1") {
					$(".convert_unit_block").show();
				} else {
					$(".convert_unit_block").hide();
				}
			}
		});
	}
}


function product_convert_qty(type, id = "", edit = "") {

	var same_unit = $("#same_unit").val();
	var product_base_qty=$("#selected_product_base_qty").val();
	var product_conv_qty=$("#selected_product_conv_qty").val();

	if(same_unit == '1'){
		var base_qty_hide = $("#opening_stock" + id).val();
		$("#opening_stock_qty_hide" + id).val(base_qty_hide);
		$("#opening_stock_conv_qty" + id).val(base_qty_hide);
		$("#opening_stock_conv_qty_hide" + id).val(base_qty_hide);
	}else{
		var base_qty=$("#opening_stock" + id).val();
		var conv_qty=$("#opening_stock_conv_qty" + id).val();

		if(type=="1"){
			ret_qty=(base_qty/product_base_qty)*product_conv_qty;
		}else if(type=="2"){
			ret_qty=(conv_qty/product_conv_qty)*product_base_qty;
		}else{
			ret_qty="1";
		}

		if(type=='1'){
			$("#opening_stock_conv_qty" + id).val(ret_qty);
			$("#opening_stock_conv_qty_hide" + id).val(ret_qty);
		}else if(type===2){
			$("#opening_stock" + id).val(ret_qty);
			$("#opening_stock_qty_hide" + id).val(ret_qty);
		}else{
			$("#opening_stock" + id).val(ret_qty);
			$("#opening_stock_qty_hide" + id).val(ret_qty);
			$("#opening_stock_conv_qty" + id).val(ret_qty);
			$("#opening_stock_conv_qty_hide" + id).val(ret_qty);
		}

		return false;

		if (type == 2) {
			var conv_qty_hide = $("#opening_stock_conv_qty" + id).val();
			var s = parseFloat(conv_qty_hide);
			results = s.toFixed(5);

			var num = $("#opening_stock_conv_qty_hide" + id).val();
			var d = parseFloat(num);
			resultb = d.toFixed(5);

			// if(resultb===results){
			// 	return false;
			// }
			var product_base_qty_hide = $("#opening_stock_qty_hide" + id).val();
		} else {
			var base_qty_hide = $("#opening_stock" + id).val();
			var d = parseFloat(base_qty_hide);
			resultb = d.toFixed(5);

			var base_qty_hidess = $("#opening_stock_qty_hide" + id).val();
			var s = parseFloat(base_qty_hidess);
			results = s.toFixed(5);

			// if(resultb===results){
			// 	return false;
			// }
			var conv_qty_hide = $("#opening_stock_conv_qty" + id).val();
		}

		var base_qty = $("#opening_stock" + id).val();
		var conv_qty = $("#opening_stock_conv_qty" + id).val();

		//var base_qty_hide=$("#product_base_qty_hide").val();
		//var conv_qty_hide=$("#product_conv_qty_hide").val();

		//var base_qty=$("#product_base_qty").val();

		//var conv_qty=$("#product_conv_qty").val();
		var product_id = $("#selected_product_id").val();

		if (edit != "") {
			product_id = $("#edit_product_id").val();
		}
		// alert(product_id)
		if (product_id) {
			$.ajax({
				type: "POST",
				url: root_domain + inventory_domain + 'app/stock/',
				data: { mode: "convert_qty", type: type, base_qty: base_qty_hide, conv_qty: conv_qty_hide, product_id: product_id },
				success: function (response) {

					var arr = jQuery.parseJSON(response);
					//arr.show_qty
					//arr.hide_qty
					//alert(type);
					//alert(arr.show_qty);
					//alert(arr.hide_qty);
					// console.log(arr);
					if (type === 1) {
						$("#opening_stock_qty_hide" + id).val(base_qty);
					} else if (type === 2) {
						$("#opening_stock_conv_qty_hide" + id).val(conv_qty);
					}

					if (type === 1) {
						$("#opening_stock_conv_qty" + id).val((arr.show_qty));
						$("#opening_stock_conv_qty_hide" + id).val(arr.hide_qty);

					} else if (type === 2) {
						$("#opening_stock" + id).val((arr.show_qty));
						$("#opening_stock_qty_hide" + id).val(arr.hide_qty);

					} else {
						$("#opening_stock" + id).val((arr.show_qty));
						$("#opening_stock_qty_hide" + id).val(arr.hide_qty);
						$("#opening_stock_conv_qty" + id).val((arr.show_qty));
						$("#opening_stock_conv_qty_hide" + id).val(arr.hide_qty);
					}
				}
			});

		} else {
			toastr.warning("Select Product First", "WARNING");
			$("#opening_stock" + id).val("1");
			$("#opening_stock_qty_hide" + id).val("1");
			$("#opening_stock_conv_qty" + id).val("1");
			$("#opening_stock_conv_qty_hide" + id).val("1");
		}
	}
}

function open_stock_approv_model(opening_stock_id, status) {
	$('#preview_stock_approval_modal').modal('show');
	$('#apprv_stock_id').html(opening_stock_id);
	$("#opening_stock_id").val(opening_stock_id);
	$("#current_status").val(status);
	load_stock_details(opening_stock_id);
	load_stock_hist_datatable();
}

function load_stock_details(opening_stock_id) {
	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock/',
		data: { mode: "load_stock_details", opening_stock_id: opening_stock_id },
		success: function (resp) {
			var resp = JSON.parse(resp);

			$('#mod_stock_div_sec').html(resp.mod_stock_div_sec);
			$("#stock_approve_status").select2({
				width : "100%"
			})
		}
	});
}

function add_stock_apprv_hist() {

	var form_data = {
		mode: "add_stock_apprv_hist",
		approve_status: $('#stock_approve_status').val(),
		approve_remark: $('#stock_approve_remark').val(),
		opening_stock_id: $('#opening_stock_id').val(),
		current_status: $('#current_status').val()
	};
	var status = 'Approved';
	if ($('#stock_approve_status').val() === '0') {
		status = 'Rejected';
	}
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock/',
		data: form_data,
		success: function (response) {
			if (response) {
				$('#stock_approve_status').select2("val", "0");
				$('#stock_approve_remark').val("");
				load_stock_hist_datatable();
				reload_data();

			} else {
				toastr.warning("You have already " + status, "ERROR");
				$('#stock_approve_status').select2("val", "0");
				$('#stock_approve_remark').val("");
			}
			$('#preview_stock_approval_modal').modal('hide');
			Unloading();
		}
	});
}

function load_stock_hist_datatable() {
	var opening_stock_id = $('#opening_stock_id').val();

	$("#stock-history-datatable").dataTable({
		"bAutoWidth": false,
		"bFilter": true,
		"bSort": true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide": true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20, "All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + inventory_domain + 'app/stock/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "load_stock_hist_datatable" }, { "name": "opening_stock_id", "value": opening_stock_id });
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted  

}


function show_view_stock_modal(product_id) {
	$('#preview_stock_view_modal').modal('show');
	$('#view_product_id').val(product_id);
	load_stock_brach_wise_details();

}


function load_stock_brach_wise_details() {
	var product_id = $('#view_product_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock/',
		data: { mode: "load_stock_brach_wise_details", product_id: product_id },
		success: function (resp) {
			var resp = JSON.parse(resp);

			$('#mod_stock_div_view').html(resp.mod_stock_div_view);
		}
	});
}

function delete_stock(id) {
	var r = confirm(" Are you want to delete ?");

	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + inventory_domain + 'app/stock/',
			data: { mode: "delete", eid: id },
			success: function (response) {
				//console.log(response)
				if (response.trim() == "1") {
					toastr.success("STOCK DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_stock_brach_wise_details();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
					Unloading();
				}
			}
		});
	}

}

function edit_stock_data(opening_stock_id) {

	$("#opening_stock_id").val(opening_stock_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock/',
		data: {
			mode: 'get_edit_product_process_data',
			opening_stock_id: opening_stock_id,
		},
		success: function (data) {

			var arr = jQuery.parseJSON(data);
			// console.log(arr);
			$("#edit_product_id").val(arr.product_data.product_id);

			check_batch_permission(arr.product_data.product_id);
			$("#edit_product_name").val(arr.product_data.product_name);
			$("#opening_stock_edit_main").val(arr.product_data.opening_stock_qty);
			$("#opening_stock_qty_hide_edit_main").val(arr.product_data.opening_stock_qty);
			$("#opening_stock_conv_qty_edit_main").val(arr.product_data.opening_stock_conv_qty);
			$("#opening_stock_conv_qty_hide_edit_main").val(arr.product_data.opening_stock_conv_qty);
			var base_qty = arr.product_data.opening_stock_qty
			var conv_qty = arr.product_data.opening_stock_conv_qty
			var base_rate = arr.product_data.base_rate;
			var conv_rate = arr.product_data.conv_rate;

			var total_base_rate = parseFloat(base_qty) * parseFloat(base_rate);
			var total_conv_rate = parseFloat(conv_qty) * parseFloat(conv_rate);
			$("#edit_base_rate").val(total_base_rate);
			$("#edit_conv_rate").val(total_conv_rate);
			if (arr.process_counter > 0) {
				$('.e_process_list').show();
				$('#e_process_list').empty().html(arr.html);

			} else {
				$('.e_process_list').hide();
				$('#e_process_list').empty();
			}


			$("#stock_edit_modal").modal('show');
			$("#edit_location_id").select2({
				width: '100%'
			});
			$("#edit_branch_id").select2({
				width: '100%'
			});
			$("#edit_branch_id").val(arr.product_data.branch_id).trigger('change');
			setTimeout(function () {
				$("#edit_location_id").val(arr.product_data.location_id).trigger('change');
				$("#edit_batch_no").val(arr.product_data.batch_no);
			}, 500)


			load_product_unit(arr.product_data.product_id, arr.product_data.product_base_unit);
			Unloading();

		}
	});
}

$('#edit_branch_id').on('change', function () {
	reload_data();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock/',
		data: {
			mode: 'get_godown_list',
			branch_id: $(this).val(),
		},
		success: function (data) {

			$("#edit_location_id").empty().html(data);
			$("#edit_location_id").select2({
				width: '100%'
			});
			Unloading();

		}
	});
});


$("#stock_edit").validate({
	rules: {
		edit_branch_id: {
			required: true
		},
		edit_location_id: {
			required: true
		},

	},
	messages: {
		edit_branch_id: {
			required: "Select Branch"
		},
		edit_location_id: {
			required: "Select location"
		},

	}
});


$("#stock_edit").on('submit', function (e) {
	// var product_id=$("#edit_product_id").val();
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#stock_edit").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	/*for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}*/
	var form_data = new FormData(this);
	$.ajax({
		cache: false,
		url: root_domain + inventory_domain + 'app/stock/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData: false,
		success: function (response) {
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			if (arr.msg == '1') {
				Unloading();
				$("#stock_edit_modal").modal('hide');
				toastr.success("STOCK UPDATED SUCCESSFULLY", "SUCCESS");
				load_stock_brach_wise_details();

			}
			else if (arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (arr.msg == '-1') {
				toastr.info("STOCK ALREADY ADDED", "INFO");
				Unloading();
			}


		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});


function show_stock_status_history(opening_stock_id) {

	$("#opening_stock_id").val(opening_stock_id);
	load_stock_hist_datatable();
	$("#stock_status_history_modal").modal('show');
}

function show_import_stock_model() {
	$('#import_stock_modal').modal('show');

}


$("#import_stock").on('submit', function (e) {

	return;
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#import_stock").valid()) {
		return false;
	}
	form.submitted = true;
	Loading();
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = new FormData(this);
	$.ajax({
		cache: false,
		url: root_domain + inventory_domain + 'app/stock/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData: false,
		success: function (response) {
			// console.log(response);
			var data = JSON.parse(response);
			var response = data.res;
			Unloading();
			if (response == '1') {
				$('#msg').html('<span style="color:green">Data Cheked Successfully</span>');

				$('#mode').val('import_data');

			}
			else if (response == '-1') {
				toastr.info("SELECT WRONG FILE", "INFO")
				$('#import_stock').trigger('reset');
				Unloading();
			}
			else if (response == '0') {
				$('#msg').html('<span style="color:red"> Coloums Does Not Match Please Check With demo File</span>');
				$('#import_stock').trigger('reset');
				Unloading();
			}
			else if (response == '3') {
				$('#msg').html('<span style="color:red"> Coloum Name Does Not Match Please Check With demo File</span>');
				$('#import_stock').trigger('reset');
				Unloading();
			}
			else if (response == '4') {
				toastr.success("CUSTOMER IMPORT SUCCESSFULLY", "SUCCESS");
				$('#import_stock').trigger('reset');
				Unloading();
				window.location = root_domain + inventory_domain + 'stock_list';

			}
			else if (response == '5') {
				$('#import_stock').trigger('reset');
				$('#mode').val('check_data');
				load_stock_list_datatable();
				Unloading();
			}
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});


function get_opening_stock_hist(location_id) {
	var product_id = $("#selected_product_id").val();
	var branch_id = $("#branch_id").val();

	if (branch_id == "") {
		toastr.warning("PLEASE SELECT BRANCH", "INFO");
		return false;
	} else if (branch_id == "1000") {
		toastr.warning("PLEASE SELECT ANY ONE BRANCH", "INFO");
		return false;
	}
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock/',
		data: { mode: "get_opening_stock_hist", product_id: product_id, branch_id: branch_id, location_id: location_id },
		success: function (data) {
			//console.log(data);	
			$('#opening_stock_history').html(data);
			Unloading();
		}
	});
}


function check_batch_permission(product_id) {
	// Loading();

	$.ajax({
		type: "POST",
		url: root_domain + inventory_domain + 'app/stock/',
		data: { mode: "check_batch_permission", product_id: product_id },
		success: function (res) {
			var arr = jQuery.parseJSON(res);
			if (arr.batch_wise_stock_manage == '1') {
				$(".batch_no").show();
				$("#batch_no").attr("required", "required");
			} else {
				$(".batch_no").hide();
				$("#batch_no").removeAttr("required");
			}

			$("#batch_no").val(arr.batch_no);
			if (arr.batch_no == "") {
				$("#batch_no").attr("readonly", false);
				$("#edit_batch_no").attr("readonly", false);
			} else {
				$("#batch_no").attr("readonly", true);
				$("#edit_batch_no").attr("readonly", true);
			}

			// Unloading();
		}
	});
}


function product_convert_rate(type, mode = "") {

	if (mode == "") {

		var base_qty = $("#opening_stock").val();
		var conv_qty = $("#opening_stock_conv_qty").val();
	} else {
		var base_qty = $("#opening_stock_edit_main").val();
		var conv_qty = $("#opening_stock_conv_qty_edit_main").val();
	}

	if (base_qty == "") {
		$("#" + mode + "conv_rate").val("");
		$("#" + mode + "base_rate").val("");
		toastr.warning("PLEASE ENTER OPENING STOCK", "INFO");
		return false;
	}


	var base_rate = $("#" + mode + "base_rate").val();
	var conv_rate = $("#" + mode + "conv_rate").val();

	if (type == "1") {
		rate = (base_qty * base_rate) / conv_qty;
	} else if (type == "2") {
		rate = (conv_qty * conv_rate) / base_qty;
	} else {
		rate = "1";
	}

	if (type === 1) {
		$("#" + mode + "conv_rate").val(rate);
	} else if (type === 2) {
		$("#" + mode + "base_rate").val(rate);
	} else {
		$("#" + mode + "base_rate").val(rate).trigger('onkeyup');
		$("#" + mode + "conv_rate").val(rate);
	}
}


function process_convert_rate(type, r_type, process_id, mode = "") {

	if (mode == "") {
		var base_qty = $("#opening_stock" + process_id).val();
		var conv_qty = $("#opening_stock_conv_qty" + process_id).val();
	} else {
		var base_qty = $("#opening_stock" + process_id).val();
		var conv_qty = $("#opening_stock_conv_qty" + process_id).val();
	}

	if (base_qty == "") {
		$("#" + mode + "conv_rate" + process_id).val("");
		$("#" + mode + "base_rate" + process_id).val("");
		$("#" + mode + "stock_conv_rate" + process_id).val("");
		$("#" + mode + "stock_base_rate" + process_id).val("");
		toastr.warning("PLEASE ENTER OPENING STOCK", "INFO");
		return false;
	}
	if (r_type == "rate") {
		var base_rate = $("#" + mode + "base_rate" + process_id).val();
		var conv_rate = $("#" + mode + "conv_rate" + process_id).val();
	} else {
		var base_rate = $("#" + mode + "stock_base_rate" + process_id).val();
		var conv_rate = $("#" + mode + "stock_conv_rate" + process_id).val();
	}


	if (type == "1") {
		rate = (base_qty  / conv_qty)  * base_rate;
	} else if (type == "2") {
		rate = (conv_qty / base_qty) * conv_rate;
	} else {
		rate = "1";
	}

	if (type === 1) {
		if (r_type == "rate") {
			$("#" + mode + "conv_rate" + process_id).val(rate);
		} else {
			$("#" + mode + "stock_conv_rate" + process_id).val(rate);
		}
	} else if (type === 2) {
		if (r_type == "rate") {
			$("#" + mode + "base_rate" + process_id).val(rate);
		} else {
			$("#" + mode + "stock_base_rate" + process_id).val(rate);
		}
	} else {
		if (r_type == "rate") {
			$("#" + mode + "base_rate" + process_id).val(rate).trigger('onkeyup');
			$("#" + mode + "conv_rate" + process_id).val(rate);
		} else {
			$("#" + mode + "stock_base_rate" + process_id).val(rate).trigger('onkeyup');
			$("#" + mode + "stock_conv_rate" + process_id).val(rate);
		}

	}
}



function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=production_pro_type&search=bom_pro_search';
	$.getJSON(mainurl, function(json) {
		// console.log(json);
		var arr=new Array();
		var len=json[0].length;
		// console.log(len);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}

$('.selproduct').select2({
	data: product_load(),
	placeholder: 'search',
	multiple: false,
    // query with pagination
    query: function(q) {
    	var pageSize,
    	results,
    	that = this;
      	pageSize = 20; // or whatever pagesize
      	results = [];
      	if (q.term && q.term !== '') {
        	// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
        	results = _.filter(that.data, function(e) {
        		return e.text.toUpperCase().indexOf(q.term.toUpperCase()) >= 0;
        	});
        } else if (q.term === '') {
        	results = that.data;
        }
        q.callback({
        	results: results.slice((q.page - 1) * pageSize, q.page * pageSize),
        	more: results.length >= q.page * pageSize,
        });
	  //$("#product_id").select2('data', { id:1, title: "UPS 3200B"});
	},
});

function exportCsv() {
	var branch_id = $('#branch_id').val();
	var product_id = $('#product_id').val();
	var location_id = $('#location_id').val();
	var approve_status = $('input[name=approve_status]:Checked').val();

	var url = root_domain +'generate_export?mode=inventory_opening_stock&branch_id=' + encodeURIComponent(branch_id) + '&product_id=' + encodeURIComponent(product_id) + '&location_id=' + encodeURIComponent(location_id) + '&approve_status=' + encodeURIComponent(approve_status);
	window.location.href = url;
}