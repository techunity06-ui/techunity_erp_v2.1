//var datatable;
$(document).ready(function () {
	if ($('#mode').val() == "Add" && ($('#smode').val() == "" || $("#smode").val() == "quotation_mode")) {
		load_sono($('#invoicetype_id').val());
	}
	load_datatable();
	get_amount();
	currency_change();
	load_trans_add();

	load_inquiry_type_product();
	product_load_pro_l();
	delivery_type_permission();
	var vipul_copper_permission = $('#vipul_copper_permission').val();
	if (vipul_copper_permission != '1') {
		product_load();
	}
	get_symbol();
	show_document_attach();
	load_consignee_detail();
	var mode = $('#mode').val();
	var smode = $('#smode').val();
	//alert(mode);

	if (mode == 'Add' && smode == 'quotation_mode') {
		//alert($('#cust_id').val());
		get_ledger_details($('#cust_id').val());
		currency_rate_c();
	}

	if (mode == 'Edit') {
		//alert($('#cust_id').val());
		quotation_dropdown_data();
	}

	if (mode == 'Add' && $("#smode").val() == '') {

		get_ledger_details($('#cust_id').val());
		currency_rate_c();
	}
	//load_transport_detail_party_wise();
	//$('.acptqty').on('keyup', function() {
	$('body').on('keyup', '.acptqty', function () {
		//alert('ff');
		var el = $(this),
			val = Math.max((0, el.val())),
			max = parseInt(el.attr('max'));

		el.val(isNaN(max) ? val : Math.min(max, val));
	});

	$('body').on('blur', '.acptqty', function () {
		//alert($(this).val());
		var tot_pro_qty = $("#product_qty").val();
		//alert(tot_pro_qty);
		//currentid= $(this).data("id")-1;
		// if(currentid!=0){
		// 	var maxqty=$('#acptqty'+currentid).attr('max');
		// 	var maxqty_val=$('#acptqty'+currentid).val();
		// 	//alert(maxqty_val);

		// 	if(maxqty==tot_pro_qty && maxqty_val==''){
		// 		alert('Please Choose Previous stage');
		// 		$(this).val('');
		// 	}else{
		// 		currentid= $(this).data("id")+1;
		// 	$('#acptqty'+currentid).attr('max',$(this).val());
		// 		// alert('Please Choose Previous stage');
		// 		// $(this).val('');+float($(this).val())
		// 	}
		// }else{
		// 	currentid= $(this).data("id")+1;
		// 	var maxqty=$('#acptqty'+currentid).attr('max');
		// 	alert(maxqty);
		// 	alert($(this).val());
		// 	var add=parseFloat(maxqty)+parseFloat($(this).val());
		// 	$('#acptqty'+currentid).attr('max',add);
		// }
		currentid = $(this).data("id") + 1;
		var maxqty = $('#acptqty' + currentid).attr('max');
		//alert(maxqty);
		//alert($(this).val());
		var add = parseFloat(maxqty) + parseFloat($(this).val());
		$('#acptqty' + currentid).attr('max', add);

	});
	//$(".attribute").change(function(){
	$('body').on('change', '.attribute', function () {
		var checkbox_this = $(this);
		if (checkbox_this.is(":checked") == true) {
			currentid = $(this).data("id") + 1;
			$('#attribute' + currentid).attr('disabled', false);
			currentid = $(this).data("id") - 1;
			$('#attribute' + currentid).attr('disabled', 'disabled');
		} else {
			currentid = $(this).data("id") - 1;
			$('#attribute' + currentid).attr('disabled', false);
			//  alert(currentid);
			currentid = $(this).data("id") + 1;
			$('#attribute' + currentid).attr('disabled', 'disabled');
			//	alert(currentid);
		}
	});
	// validate the comment form when it is submitted        
	// validate vendor add form on keyup and submit
	$("#sales_order_add").validate({
		rules: {
			sales_order_date: {
				required: true
			},
			cust_id: {
				required: true
			},
			branch_id: {
				required: true
			}
		},
		messages: {
			sales_order_date: {
				required: "Enter date"
			},
			cust_id: {
				required: "Select Customer"
			},
			branch_id: {
				required: "select Branch"
			}
		}
	});

	$("#sales_order_stage").validate({
		rules: {
			product_id: {
				required: true
			}

		},
		messages: {
			product_id: {
				required: "Select Product"
			}
		}
	});
});

function getstages(prid) {
	var sales_order_id = $("#sales_order_id").val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "getstages", prid: prid, sales_order_id: sales_order_id },
		success: function (response) {

		}
	});
}
function load_typeswise_terms(sales_order_id) {
	var quot_type = $('input[name="quot_type"]:checked').val();
	var terms_type = $('input[name="terms_type"]:checked').val();
	var cust_id = $("#cust_id").val();
	var so_quotation_type = $('input[name="so_quotation_type"]:checked').val();

	if (so_quotation_type == 1) {
		var quotaion_id = $('#term_quotation_id').val();
	} else {
		var quotaion_id = $('#quotaion_id').val();
	}

	if (terms_type == 1) {
		if (cust_id == "") {
			toastr.warning("Select Customer", "ERROR");
			$("#cust_id").focus();
			$("input[name='terms_type'][value='0']").prop('checked', true);
			return false;
		}
	}




	if (quot_type || quot_type == 0) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "load_typeswise_terms", quot_type: quot_type, sales_order_id: sales_order_id, quotaion_id: quotaion_id, terms_type: terms_type, cust_id: cust_id },
			success: function (response) {
				var resp = JSON.parse(response);
				$('#quot_terms_cond_div').html(resp.resp_html);
				Unloading();
			}
		});
	}
}

function get_quotation_data_so() {
	var quot_type = $('input[name="quot_type"]:checked').val();
	var terms_type = $('input[name="terms_type"]:checked').val();
	var so_quotation_type = $('input[name="so_quotation_type"]:checked').val();
	var user_id = $("#user_id").val();
	var sales_order_id = $("#eid").val();
	if (so_quotation_type == 1) {
		if (terms_type == 2) {
			$('#quot_wise_term').show();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/sales_order/',
				data: { mode: "get_quotation_data_so", user_id: user_id, sales_order_id: sales_order_id },
				success: function (response) {
					var resp = JSON.parse(response);
					$('#term_quotation_id').html(resp.resp_html);
					$('#term_quotation_id').select2("val", resp.term_quotation_id);
					load_typeswise_terms('');
				}
			});
		} else {
			$('#quot_wise_term').hide();
			$('#term_quotation_id').select2("val", "");
			load_typeswise_terms('');
		}
	} else {
		$('#quot_wise_term').hide();
		$('#term_quotation_id').select2("val", "");
		load_typeswise_terms('');
	}
}

function invoice_submit() {
	$("#save_print").val(1);
	$("#sales_order_add").submit();
}
$("#sales_order_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#sales_order_add").valid()) {
		return false;
	}
	if ($('#currency_enable').is(":checked")) {
		if ($("#currency_id").val() == "") {
			toastr.warning("Select Currency", "ERROR");
			$("#currency_id").focus();
			return false;
		}
		if ($("#currency_rate").val() == "") {
			toastr.warning("Enter Currency Rate", "ERROR");
			$("#currency_rate").focus();
			return false;
		}
	}
	if ($('#po_document_required').val() == 1) {
		if ($('#po_document_count').val() <= 0) {
			toastr.warning("Select PO Document", "ERROR");
			$("#remark-section").removeClass('active');
			$("#tab2").removeClass('active');
			$("#podoc-section").addClass('active');
			$("#tab3").addClass('active');
			$("#doc_attach").focus();
			return false;
		}
	}

	if (parseFloat($('#total').val()) <= 0) {
		toastr.warning("AT LEAST ONE PRODUCT REQUIRE", "ERROR")
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	var form_data = new FormData(this);
	$.ajax({
		cache: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData: false,
		success: function (response) {
			var arr = jQuery.parseJSON(response);
			var print_path = $('#print_path').val();
			if (arr.msg == '1') {
				Unloading();
				toastr.success("SALES ORDER ADDED SUCCESSFULLY", "SUCCESS");
				if ($("#save_print").val() == '1') {
					window.location = root_domain + print_root_domain + print_path + '/' + arr.eid;
				}
				else {
					window.location = root_domain + crm_domain + 'sales_order_list';
				}
			}
			else if (arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();
			}
			else if (arr.msg == 'update') {
				toastr.success("SALES ORDER UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				if ($("#save_print").val() == '1') {
					window.location = root_domain + print_root_domain + print_path + '/' + arr.eid;
				}
				else {
					window.location = root_domain + crm_domain + 'sales_order_list';
				}
			}
			$('#sales_order_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});

$("#sales_order_stage").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#sales_order_stage").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var chkarr = [];
	$('input[type="checkbox"]').each(function () {
		var checkbox_this = $(this);
		if (checkbox_this.is(":checked") == true) {
			var chekcval = 1;
		} else {
			var chekcval = 0;
		}
		chkarr.push(chekcval);
	});
	var form_data = new FormData(this);
	form_data.append('completedstatus', chkarr);
	$.ajax({
		cache: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData: false,
		success: function (response) {
			var arr = jQuery.parseJSON(response);
			if (arr.msg == '1') {
				Unloading();
				toastr.success("SALES ORDER Stage ADDED SUCCESSFULLY", "SUCCESS");
				window.location = root_domain + crm_domain + 'sales_order_list';
			}
			else if (arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();
			}
			else if (arr.msg == 'update') {
				toastr.success("SALES ORDER UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
			}
			$('#sales_order_stage').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

function delete_sales_order(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "delete", eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("SALES ORDER DELETE SUCCESSFULLY", "SUCCESS");
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
function delivery_type_permission() {
	var delivery_type = $("#delivery_type").val();
	if (delivery_type === "so_wise") {
		$(".delivary_product_wise").hide();
		$(".delivary_so_wise").show();
	} else {
		$(".delivary_product_wise").show();
		$(".delivary_so_wise").hide();
	}
}
function open_approv_quo1() {
	if ($("#product_id").val() === "") {
		toastr.warning("Select Product", "ERROR")
		$("#product_id").select2('focus')
		return false;
	}
	else if ($("#product_qty").val() === "") {
		toastr.warning("Enter Qty", "ERROR")
		$("#product_qty").focus();
		return false;
	}
	else if ($("#unitid").val() === "") {
		toastr.warning("Select Unit", "ERROR")
		//$("#unitid").select2('focus');
		$("#unitid").focus();
		return false;
	}
	else if ($("#product_rate").val() === "") {
		toastr.warning("Enter Rate", "ERROR")
		$("#product_rate").focus();
		return false;
	}
	else if ($("#conversion_rate").val() === "") {
		toastr.warning("Enter Conversion Rate", "ERROR")
		$("#conversion_rate").focus();
		return false;
	}

	if ($('#currency_enable').is(":checked")) {
		if ($("#currency_id").val() == "") {
			toastr.warning("Select Currency", "ERROR")
			$("#currency_id").focus();
			return false;
		}
		if ($("#currency_rate").val() == "") {
			toastr.warning("Enter Currency Rate", "ERROR")
			$("#currency_rate").focus();
			return false;
		}
	}

	var unitid = $('#unitid').val();
	var rate_unitid = $('#rate_unit_id').val();

	if (unitid == rate_unitid) {
		var qty = $("#product_qty").val();
		var unit_show = $("#unit_show").text();
	} else {
		var qty = $("#product_conv_qty").val();
		var unit_show = $("#convert_unit_show").text();
	}
	var trn_id = $("#edit_id").val();
	var unit_show = $("#unit_show").text();
	var product_name = $("#product_id").select2('data').text;

	$("#model_product_name").html(product_name + " --- " + qty + " " + unit_show);
	$("#m_trn_id").val(trn_id);
	$("#m_qty").val(qty);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "delivary_date_model_open", qty: qty, trn_id: trn_id },
		success: function (response) {
			$('#bs-so_dispatch_date-modal').modal('show');
			$("#date_des").html(response);
			//$("#m_addrow").hide();
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

		}
	});
}
function qty_wise_date_validation(count) {
	var delivery_date = $("#delivery_date" + count).val();
	var delivery_qty = $("#delivery_qty" + count).val();
	if (delivery_date === "") {
		toastr.warning("Select Date", "ERROR")
		$("#delivery_date" + count).focus();
		$("#delivery_qty" + count).val("");
	}
}
function validate_dilivary_date() {

	var unitid = $('#unitid').val();
	var unit_wise = $('#unit_wise').val();
	if (unitid == unit_wise) {
		var main_qty = $("#product_qty").val();
	} else {
		var main_qty = $("#product_conv_qty").val();
	}
	var total_delivery_qty = document.getElementsByName('delivery_qty[]');
	var total_arry_sr = document.getElementsByName('arry_sr[]');
	var cnt = total_delivery_qty.length;
	var grandtotal_delivery_qty = 0;
	var count = $("#count").val();
	main_qty = parseFloat(main_qty).toFixed(3);
	var qval = "0";
	for (var i = 0; i < cnt; i++) {
		grandtotal_delivery_qty += parseFloat(total_delivery_qty[i].value);
		var grandtotal_delivery_qty_new = grandtotal_delivery_qty;
		grandtotal_delivery_qty_new = parseFloat(grandtotal_delivery_qty_new).toFixed(3);
		var count1 = total_arry_sr[i].value;

		//alert(count1);
		//alert(qval);
		if (count1 != "1") {
			if (qval === "1") {
				//alert(qval);
				//alert(count1)
				$('#field' + count1).html('');
			}
		}
		if (parseFloat(grandtotal_delivery_qty_new) >= parseFloat(main_qty)) {
			qval = "1";
		} else {
			qval = "0";
		}
	}
	var total = parseFloat(grandtotal_delivery_qty).toFixed(3);
	if (parseFloat(total) > parseFloat(main_qty)) {

		$("#m_addrow").hide();
	} else {
		if (parseFloat(total) < parseFloat(main_qty)) {
			$("#m_addrow").hide();
			count = parseFloat(count) + parseFloat(1);
			$('#count').val(count);
			var pending_qty = parseFloat(main_qty) - parseFloat(total);

			$("#mix_loose_material_table").append('<tr id="field' + count + '"><td   class="text-center" style="vertical-align:center;"><input type="text" class="form-control default-date-picker delivery_date" id="delivery_date' + count + '" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation(' + count + ');" ></td><td class="text-center;" style="vertical-align:center;"><input type="text" class="form-control delivery_qty" id="delivery_qty' + count + '" name="delivery_qty[]" onchange="validate_dilivary_date();" placeholder="' + pending_qty + '" onkeyup="qty_wise_date_validation(' + count + ');" /></td><td class="text-center" style="vertical-align:center;" ><button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date(' + count + ');" id="fieldremove' + count + '"><i class="fa fa-times"></i></button><input type="hidden" name="arry_sr[]" id="arry_sr" value="' + count + '" /></td></tr>')

			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

		} else {
			$("#m_addrow").show();
		}
	}
	if (qval === "1") {
		//validate_dilivary_date();
	}
}
function remove_dilivary_date(count) {
	$('#field' + count).html('');
	validate_dilivary_date();
}
function add_discount(type) {
	var total = $("#total").val();
	var discount_amt = 0; var discount_per = 0;
	if (total != "") {
		if (type == "amt") {
			discount_amt = $('#discount_amt').val();
			discount_per = ((discount_amt * 100) / total).toFixed(2);
			$("#discount_per").val(discount_per);
		}
		else if (type == "per") {
			discount_per = $('#discount_per').val();
			discount_amt = ((total * discount_per) / 100).toFixed(2);
			$("#discount_amt").val(discount_amt);
		}
		get_gtotal($('#formulaid').val());
	}
}
function get_discount(type) {
	var ratecalfiled = $('#pro_cal_type').val();
	var qty = parseFloat($('#' + ratecalfiled).val());
	var taxtype = $("#taxtype").text();
	if (taxtype == 'including') {
		var rate = $("#pro_amt").text();
	} else {
		var rate = $('#product_rate').val();
	}
	var disc = 0;
	if (qty != "" && rate != "") {
		if (type == "amt") {
			disc = 100 * parseFloat($('#product_discount').val()) / (qty * rate);
			$('#discount_per').val(disc);
		}
		else if (type == "per") {
			disc = ((qty * rate) * parseFloat($('#discount_per').val())) / 100;
			$('#product_discount').val(disc);
		}
	}
	else {
		$('#product_discount').val('');
		$('#discount_per').val('');
	}
	get_amount();
}
function add_freight() {
	get_gtotal($('#formulaid').val());
}
function cal_discount() {
	get_gtotal($('#formulaid').val());
}
function get_amount() {
	var ratcalfiled = $("#pro_cal_type").val();
	var id = parseInt($('#fieldcnt').val()) + 1;
	if ($("#" + ratcalfiled).val() != "" && $("#product_rate").val() != "") {
		if ($("#taxtype").text() == 'including') {
			var taxrate = $("#taxper").val();
			var q = $("#" + ratcalfiled).val();
			var rate = (parseFloat($("#product_rate").val()) * 100 / (100 + parseFloat(taxrate))).toFixed(3);
			var a = q * rate;
			$("#pro_amt").text(rate);
			$("#taxrate").text((parseFloat($("#product_rate").val()) - parseFloat(rate)).toFixed(2));
		} else {
			var q = $("#" + ratcalfiled).val();
			var rate = $("#product_rate").val();
			var a = q * rate;
		}


		if ($("#product_discount").val() != "")//discount calculation
		{
			var discount = parseFloat($("#product_discount").val());
			a = a - discount;
		}
		$("#product_amount").val(parseFloat(a).toFixed(2));
		$("#taxable_value").val(parseFloat(a).toFixed(2));
		//alert($("#formulaid").val());
		/* if($("#formulaid").val()!="")//tax calculation
		{
			alert($("#formulaid").val());
			var total=a;
			var formulaid=$("#formulaid").val();
			$.ajax({
				type: "POST",
				url: root_domain+'app/purchase/',
				data: { mode : "getproduct_amount",  product_amount : total ,formulaid:formulaid},
				success: function(response)
				{
					var obj=jQuery.parseJSON(response);
					$('#product_amount').val(obj.total);
				}
			});
		} */
	}
	else {
		$("#product_amount").val(0);
	}
	get_gtotal();
}
function add_field() {

	var inputValues = [];
	var s_value = [];

	var dynamic_data = "";

	

	
	// var fid = $('#'+ inputValues ).val();
	// var dynamicId = 'field_id_' + fid;
	// var dynamic_fields = $('#field_id_' + inputValues).val();
	// console.log(dynamic_fields);
	// alert(dynamic_fields);


	// $.each(fval, function (key, value) {
	// 	alert(key + ": " + value);
	// });

	//   return;
	if ($("#product_id").val() === "") {
		toastr.warning("Select Product Name", "ERROR")
		return false;
	}
	if ($("#product_qty").val() === "") {
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	if ($("#product_rate").val() === "") {
		toastr.warning("Enter Rate", "ERROR")
		return false;
	}
	if ($("#branch_id").val() === "") {
		toastr.warning("Select Branch Id", "ERROR")
		return false;
	}

	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	var delivery_type = $("#delivery_type").val();
	if (delivery_type === "product_wise") {
		var mqty = $("#m_qty").val();

		var total_delivery_qty = document.getElementsByName('delivery_qty[]');
		var cnt = total_delivery_qty.length;
		var grandtotal_delivery_qty = 0;
		mqty = parseFloat(mqty).toFixed(3);
		for (var i = 0; i < cnt; i++) {
			grandtotal_delivery_qty += parseFloat(total_delivery_qty[i].value);
		}
		var total = parseFloat(grandtotal_delivery_qty).toFixed(3);

		if (mqty != total) {
			toastr.warning("Delivery Qty Wrong", "ERROR")
			return false;
		}
	}

	var vendorID;
	var total_delivery_qty1_arr = [];
	var delivery_date_arr = [];
	var arry_edit_arry = [];
	var total_delivery_qty1 = $('input[name="delivery_qty[]"]').val();
	var arry_edit = $('input[name="arry_edit[]"]').val();
	var data = $("#product_id").select2('data');
	var quot_trn_id = data.quotation_trn_id;
	/*alert(quot_trn_id);*/
	i = 0;
	$('input.delivery_qty').each(function () {
		total_delivery_qty1_arr[i++] = $(this).val();
	});

	j = 0;
	$('input.delivery_date').each(function () {
		delivery_date_arr[j++] = $(this).val();
	});

	k = 0;
	$('input.arry_edit').each(function () {
		arry_edit_arry[k++] = $(this).val();
	});

	var unit_wise = $("#unit_wise").val();
	if ($("#delivery_type").val() == 'product_wise') {
		unit_wise = $("#unit_wise").val();
	}

	var e = $("#edit_id").val();

	if ($('#currency_enable').is(':checked')) {
		var currency_enable = 1;
	}
	else {
		var currency_enable = 0;
	}
	if ($('#s_per').val() == '1') {
		var product_item_code = $('#product_item_code').val();
	} else {
		var product_item_code = '';
	}
	var vipul_copper_permission = $('#vipul_copper_permission').val();
	var atlas_permission = $('#atlas_permission').val();
	var product_category_id = '';
	var product_length = '';
	var product_pices = '';
	product_category_id = $('#product_category_id').val();
	if (vipul_copper_permission == '1') {
		product_length = $('#product_length').val();
		product_pices = $('#product_pices').val();
	}
	if (atlas_permission == '1') {
		var cust_stateid = '1';
	} else {
		var cust_stateid = $("#cust_stateid").val();
	}

	if (smpl_permission == 1) {
		var bstock_arr = [];
		var bid_arr = [];

		i = 0;
		$('input.wip_res_stock').each(function () {
			bstock_arr[i++] = $(this).val();
		});

		j = 0;
		$('input.wip_stock_id').each(function () {
			bid_arr[j++] = $(this).val();
		});

		//console.log(bstock_arr);
		//return false;
		var total_st = 0;
		for (var i = 0; i < bstock_arr.length; i++) {
			total_st += bstock_arr[i] << 0;
		}

		/*alert(total_st);*/
		var gstock_total = parseFloat($('#gstock_total').val());
		gstock_total = getNum(gstock_total);
		var tstock = total_st + gstock_total;
		var validate_qty = $("#validate_qty").val();
		if (validate_qty < tstock) {
			toastr.warning("Increase Resverve Qty Please Enter currect Qty", "ERROR");
			return false;
		}
	}	

	var dynamic_data_arr = {};
	$('input[class^="dy_fields\\["]').each(function () {
		var vl = $(this).val();
		var db_name = $(this).attr("data-id");
		var fval = $("#field_id_"+vl).val();
		if (dynamic_data) {
			dynamic_data = dynamic_data + ",";
		}
		dynamic_data_arr[db_name] = fval;
	})
	
	var ajaxData = {
		mode: "fieldadd",
			s_value: s_value,
			quotation_id: $("#mquotation_id").val(),
			bstock: bstock_arr,
			bid: bid_arr,
			quot_trn_id: quot_trn_id,
			product_category_id: product_category_id,
			product_length: product_length,
			product_pices: product_pices,
			total_delivery_qty: total_delivery_qty1_arr,
			delivery_date: delivery_date_arr,
			arry_edit: arry_edit_arry,
			delivery_type: delivery_type,
			edit_id: $("#edit_id").val(),
			product_id: $("#product_id").val(),
			rcat_id: $("#parent_category_id").val(),
			product_disc: CKEDITOR.instances['product_des'].getData(),
			product_spec: CKEDITOR.instances['product_spec'].getData(),
			user_id: $("#user_id").val(),
			product_item_code: product_item_code,
			unit_wise: unit_wise,
			product_qty: $("#product_qty").val(),
			product_rate: $("#product_rate").val(),
			product_hsn_code: $("#hsncode").text(),
			cust_stateid: cust_stateid,
			delivery_type: $("#delivery_type").val(),
			product_qty: $("#product_qty_hide").val(),
			product_conv_qty: $("#product_conv_qty_hide").val(),
			unit_id: $("#unitid").val(),
			conv_unitid: $("#conv_unitid").val(),
			rate_unitid: $("#rate_unit_id").val(),
			formulaid: $("#formulaid").val(),
			product_discount: $("#product_discount").val(),
			discount_per: $("#discount_per").val(),
			product_amount: $("#product_amount").val(),
			sales_order_id: $("#eid").val(),
			taxable_value: $("#taxable_value").val(),
			branch_id: $("#branch_id").val(),
			with_out_stock_invoice: $("#with_out_stock_invoice").val(),
			inquiry_type: $("#inquiry_type").val(),
			old_product_id: $("#old_product_id").val(),
			product_attr: $('#product_id').find('option:selected').attr('data-type'),
			currency_enable: currency_enable,
			currency_rate: $('#currency_rate').val(),
			currency_id: $('#currency_id').val(),
			orange: $('#orange').val(),
			mfg: $('#mfg').val(),
			trading: $('#trading').val(),
			repairing: $('#repairing').val(),
			other: $('#other').val(),
			gst_type: $('#gst_type').val(),
			priority_status: $('#priority_status').val(),
			orange_total: $('#orange_total').val(),
			mfg_total: $('#mfg_total').val(),
			trading_total: $('#trading_total').val(),
			repairing_total: $('#repairing_total').val(),
			other_total: $('#other_total').val(),
			dynamic_data: dynamic_data_arr
	};

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: ajaxData,
		success: function (response) {
			
			$('input[class^="dy_fields\\["]').each(function () {
				var vl = $(this).val();
				// var db_name = $(this).attr("data-id");
				// var fval = $("#field_id_"+vl).val();
				$("#field_id_"+vl).select2("val", "")
			})

			$('#bs-so_dispatch_date-modal').modal('hide');
			$('#bs-stock_allocation-modal').modal('hide');
			$("#product_id").select2("val", "")
			if (aeon_permission != 1) {
				$("#product_category_id").select2("val", "")
			}
			$("#parent_category_id").select2("val", "")

			$("#mquotation_id").select2("val", "")
			$("#product_des").val("")
			$("#product_spec").val("")
			$("#product_hsn_code").val("")
			if ($('#s_per').val() == '1') {
				$("#product_item_code").val("")
			}

			$("#product_qty").val("");
			$("#product_conv_qty").val("");
			$("#product_qty_hide").val("");
			$("#product_conv_qty_hide").val("");
			$("#conv_unitid").val("");
			$("#unitid").val("");
			$("#unit_show").html("");
			$("#convert_unit_show").html("");
			$("#convert_unit_block").hide();
			$("#rate_unit_id").val("");
			$("#formulaid").val("");
			$("#product_discount").val("");
			$("#discount_per").val("");
			$("#taxable_value").val("");
			$("#product_rate").val('');
			$("#product_amount").val('');
			$("#orange").val('');
			$("#mfg").val('');
			$("#trading").val('');
			$("#repairing").val('');
			$("#other").val('');
			$("#orange_total").val('');
			$("#mfg_total").val('');
			$("#trading_total").val('');
			$("#repairing_total").val('');
			$("#other_total").val('');
			$("#edit_id").val('');
			$('#addproduct').show();
			$('#priority_status').select2("val", "Low");
			$('#addrow').val('Add');
			$('#projectItem').css('display', 'none');
			if (vipul_copper_permission == '1') {
				$('#die_master_product_name').css('display', 'none');
				$('#product_category_id').val('');
				$('#product_length').val('');
				$('#product_pices').val('');
			}
			$('#current_stock').css('display', 'none');
			$(".hsncode").hide();
			$(".taxtype").hide();
			$(".pro_amt").hide();
			$(".taxrate").hide();
			Unloading();
			show_data();
			$("#product_id").prop('disabled', false);
			CKEDITOR.instances['product_des'].setData('');
			CKEDITOR.instances['product_spec'].setData('');

			if (durva_permission == 1) {

				$("#addrow1").show();
				$("#addrow").hide();

			}
			else {

				$('#addrow').html('Add');
			}
			$('#bs-batch_wise_stock-modal1').modal('hide');
		}
	});
}

function reload_data() {
	//datatable.fnReloadAjax();
	load_datatable();
}
function load_datatable() {
	var data = $('input[name=report]:Checked').val();
	var date = $('#rep_date').val();
	var type = $('#type_id').val();
	var jobwork_type = $('input[name=jobwork_type]:Checked').val();
	var branch_id = $('#branch_id').val();
	var user_id = $('#user_id').val();
	var so_status = $('#so_status').val();

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
		"sAjaxSource": root_domain + crm_domain + 'app/sales_order/',
		"aoColumnDefs": [
			{ "bSearchable": false, "bVisible": false, "aTargets": [4] },
			{ "bVisible": false, "aTargets": [4] },
			{ "bVisible": false, "aTargets": [9] }
		],
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "fetch" },
				{ "name": "date", "value": date },
				{ "name": "branch_id", "value": branch_id },
				{ "name": "jobwork_type", "value": jobwork_type },
				{ "name": "user_id", "value": user_id },
				{ "name": "so_status", "value": so_status },
			);
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
			var iPageMarket = 0;
			var iPageMarkets = 0;
			for (var i = 0; i < aaData.length; i++) {
				if (aaData[i][9] != 1) {
					iPageMarket += aaData[i][5] * 1;
					iPageMarkets += aaData[i][4] * 1;
				}
				/*alert(iPageMarket);*/
			}

			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = parseFloat(iPageMarket).toFixed(2);
			$('#soamount').html('Rs. ' + parseFloat(iPageMarket).toFixed(2));
			$('#sotaxamount').html('Rs. ' + parseFloat(iPageMarkets).toFixed(2));
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');
}



// $(".attribute").onchange(){
// alert("f");
// });

function show_stage_data(prid) {
	var sales_order_id = $("#sales_order_id").val();
	var so_id = $("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		//	data: { mode : "load_tempoutward",so_id:so_id},
		//data: { mode : "getstages",so_id:so_id},
		data: { mode: "getstages", prid: prid, sales_order_id: sales_order_id },

		success: function (data) {
			var vipul_copper_permission = $('#vipul_copper_permission').val();
			if (vipul_copper_permission == '1') {
				$('#sale_productdata').html(data);
			} else {
				$('#sale_productdata_salesorder').html(data);
			}
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

			var chkarr = [];
			var i = 1;
			var checkedcount_till = 0;
			$('input[type="checkbox"]').each(function () {

				var checkbox_this = $(this);
				// if(checkedcount>0){
				// 	alert(i);
				//        	checkedcount=i;
				//        	//alert(checkedcount);
				//        	//checkbox_this.attr('disabled', 'disabled');
				//        	$('#attribute'+checkedcount).attr('disabled', 'disabled');
				//     }
				if (checkbox_this.is(":checked") == true) {
					//var chekcval=1;
					checkedcount_till = i;
					//alert(checkedcount);
				} else {
					//	var chekcval=0;
				}
				//    alert(checkedcount);


				//chkarr.push(chekcval);
				i++;
			});
			unchecked_val = checkedcount_till + 1;
			var j = 1;
			$('input[type="checkbox"]').each(function () {
				if (j > unchecked_val) {
					$('#attribute' + j).attr('disabled', 'disabled');
				}
				j++;
			});
			unchecked_val = checkedcount_till - 1;
			var j = 1;
			$('input[type="checkbox"]').each(function () {
				if (j <= unchecked_val) {
					$('#attribute' + j).attr('disabled', 'disabled');
				}
				j++;
			});
			//$('.attribute').attr('disabled', 'disabled');
			Unloading();
		}

	});
}
function show_data() {
	/*alert(12121);*/
	var delivery_type = $("#delivery_type").val();
	var user_id = $("#user_id").val();
	var so_id = $("#eid").val();
	/*alert(durva_permission);*/
	if (durva_permission == 1) {
		mode = "load_tempoutward_durva";
	} else {
		mode = "load_tempoutward";
	}
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: mode, so_id: so_id, delivery_type: delivery_type, user_id: user_id },
		success: function (response) {
			console.log(response);
			/*var data = jQuery.parseJSON(response);*/
			/*alert(data);*/
			var vipul_copper_permission = $('#vipul_copper_permission').val();
			if (vipul_copper_permission == '1') {
				$('#sale_productdata').html(response);
			} else {
				$('#sale_productdata_salesorder').html(response);
			}

			// $('#sale_productdata_salesorder').html(data.res);
			/*if(data.count=='0'){
				$('#delivery_type').prop('disabled', false);
			}*/
			get_amount();
			get_tax_details_table();
			get_invoice_total_tax();
			get_gtotal();
			get_symbol();
			//load_company_data();
			Unloading();
		}

	});

}
function delivery_detail(so_trn_id, project_wise) {
	$('#delivery_detail').modal('show');
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "delivery_detail", so_trn_id: so_trn_id, project_wise: project_wise },
		success: function (response) {
			//console.log(response)
			var data = jQuery.parseJSON(response);
			$('#pr_na').html(data.pro_name);
			$('#delivery_schedule').html(data.delivery_schedule);
		}
	});
}
function get_series_no() {
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "get_series_no" },
		success: function (resp) {
			//console.log(resp);
			$('#invoicetype_id').val(resp);
			//alert(hr);
			load_sono(resp);

		}
	});
}
function load_sono(id) {

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_invoiceno", typeid: id },
		success: function (data) {
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#sales_order_no').val(no.invoiceno);
		}
	});
}

function edit_data(id, project_wise) {
	if (durva_permission == 1) {
		$("#addrow1").hide();
		$("#addrow").show();
	}

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "preedit", id: id, project_wise: project_wise },
		success: function (response) {
			var data = jQuery.parseJSON(response);

			// Get Master fields data
			var master_fields_data = data.master_fields_data;
			if (master_fields_data) {
				$.each(master_fields_data, function (k, vl) {
					var labl = vl.master_field_db_name;
					$.map(data, function(dvalue, dkey) {
						if (dkey == labl) {
							if (dvalue == '0') {
								dvalue = "";
							}
							$("#field_id_"+vl.master_field_id).select2("val", dvalue);		
						}
					});
				});
			}
			var vipul_copper_permission = $('#vipul_copper_permission').val();
			var curr = '<?php echo $_SESSION["currency_id"]?>';
			var currency_id = $('#currency_id').val();
			// $("#product_id").select2("val",data.product_id);
			//$("#product_hsn_code").val(data.product_hsn_code);
			$('#hsncode').text(data.product_hsn_code);
			$(".hsncode").show();
			$('#taxtype').text(data.product_gst);
			$(".taxtype").show();
			$("#product_category_id").select2("val", data.product_category_id);
			$("#parent_category_id").select2("val", data.rcat_id);
			if (vipul_copper_permission == '1') {
				$("#product_length").val(data.product_length);
				$("#product_pices").val(data.product_pices);
				$("#product_hsn_code").val(data.product_hsn_code);
				getProductByCategoryID(data.product_category_id, 'preedit', data.product_id);
			}
			$("#product_des").val(data.description);
			$("#mquotation_id").select2("val", data.quotation_id);
			$("#product_qty").val(data.product_qty);
			$("#product_disc").val(data.product_disc);
			$("#product_id").select2('data', { id: data.product_id, text: data.product_name, quotation_trn_id: data.quot_trn_id });
			$("#product_qty_hide").val(data.product_qty);
			$("#product_conv_qty").val(data.product_conv_qty);
			$("#product_conv_qty_hide").val(data.product_conv_qty);
			$("#unitid").val(data.unit_id);
			$("#conv_unitid").val(data.conv_unit_id);
			$("#unit_wise").val(data.unit_wise);
			$("#priority_status").select2('val', data.priority_status);
			$("#formulaid").val(data.formulaid);
			$("#product_rate").val(data.product_rate);
			$("#product_amount").val(data.product_amount);
			$("#discount_per").val(data.discount_per);
			$("#taxable_value").val(data.product_amount);
			$("#product_item_code").val(data.product_item_code);
			$("#orange").val(data.orange);
			$("#mfg").val(data.mfg);
			$("#trading").val(data.trading);
			$("#repairing").val(data.repairing);
			$("#other").val(data.other);
			$("#orange_total").val(data.orange_total);
			$("#mfg_total").val(data.mfg_total);
			$("#trading_total").val(data.trading_total);
			$("#repairing_total").val(data.repairing_total);
			$("#other_total").val(data.other_total);
			$("#edit_id").val(id);
			$('#addrow').val('Update');
			if (project_wise == '1') {
				$('#projectItem').css('display', 'block');
			}
			$("#old_product_id").val(data.product_id);
			$("#salesorder_trn_id").val(data.sales_ordertrn_id);
			CKEDITOR.instances['product_des'].setData(data.product_desc);
			CKEDITOR.instances['product_spec'].setData(data.product_spec);
			$('#rate_unitid').val(data.rate_unit).trigger('change');
			load_product_unit(data.product_id, data.rate_unit);
			$("#product_discount").val(data.product_discount);

			if (currency_id == curr) {
				if (data.product_gst == 'including') {
					var total_tax_per = parseFloat(data.cgst_tax_per) + parseFloat(data.sgst_tax_per) + parseFloat(data.igst_tax_per);
					$("#taxper").val(total_tax_per);
					var total_tax_rate = (parseFloat(data.product_rate) * parseFloat(total_tax_per)) / 100;
					var total_rate = parseFloat(data.product_rate) + parseFloat(total_tax_rate);
					$("#product_rate").val(total_rate);
					$(".pro_amt").show();
					$(".taxrate").show();
					get_amount();
				} else {
					$("#product_rate").val(data.product_rate);
				}

				$("#product_amount").val(data.product_amount);
				$("#product_discount").val(data.product_discount);
				/*$("#taxable_value").val(data.product_amount)
				$("#product_amount_tax").val(data.product_amount_tax)*/
			} else {
				if (data.product_gst == 'including') {
					var total_tax_per = parseFloat(data.cgst_tax_per) + parseFloat(data.sgst_tax_per) + parseFloat(data.igst_tax_per);
					$("#taxper").val(total_tax_per);
					var total_tax_rate = (parseFloat(data.product_rate_conv) * parseFloat(total_tax_per)) / 100;
					var total_rate = parseFloat(data.product_rate_conv) + parseFloat(total_tax_rate);
					$("#product_rate").val(total_rate);
					$(".pro_amt").show();
					$(".taxrate").show();
					get_amount();
				} else {
					$("#product_rate").val(data.product_rate_conv);
				}

				//$("#product_rate").val(data.product_rate_conv);
				$("#product_amount").val(data.product_amount_conv);
				$("#product_discount").val(data.product_discount_conv);
				/*$("#taxable_value").val(data.product_currency_amount)
				$("#product_amount_tax").val(data.product_currency_amount_tax)*/
			}
			// $("#product_id").prop('disabled', true);


			Unloading();
		}
	});
}
function delete_data(id, project_wise) {
	var r = confirm(" Are you want to delete ?");

	if (r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "delete_data", eid: id },
			success: function (response) {
				//console.log(response)
				var data = jQuery.parseJSON(response)
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data();
					Unloading();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				get_gtotal();
			}
		});
	}

}
function delete_attch(so_attch_id) {
	var conf = confirm("Are you sure want to Delete ?");
	if (conf) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "delete_attch", so_attch_id: so_attch_id },
			success: function (response) {
				//console.log(response);
				var data = jQuery.parseJSON(response);
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("ATTACHMENT DELETED SUCCESSFULLY", "SUCCESS");
					location.reload();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				Unloading();
			}
		});
	}
}
// Dimple Panchal : start
function get_tax_on_total(formula_id) {
	if (formula_id)//tax calculation on total 
	{
		var total = $("#g_total").val();
		var formulaid = $("#formula_id").val();
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "get_tax_on_total", total: total, formulaid: formulaid },
			success: function (response) {
				var obj = jQuery.parseJSON(response);
				$('#tcs_total').val(obj.tax_value);
			}
		});
	}
}
// Dimple Panchal : end
function load_quotation_details(id) {
	//alert(id);
	//Loading();
	var delivery_type = $('#delivery_type').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_quotation_details", id: id, delivery_type: delivery_type },
		success: function (response) {
			//console.log(response);
			var data1 = jQuery.parseJSON(response);
			//console.log(data1.cust_data);
			$("#cust_id").html(data1.cust_data);
			$("#cust_id").select2("val", data1.cust_id);
			$("#user_id").select2("val", data1.w_user_id);
			load_consignee_detail(data1.cust_id);
			show_data();
			//alert(data1.cust_id);
			//Unloading();
		}
	});
}
function open_po_approv_payment(sales_order_id, sales_order_no) {
	$('#preview_po_approval_hist_modal').modal('show');
	$('#apprv_po_ref_no').html(sales_order_no);
	$('#ref_ord_id').val(sales_order_id);
	$('#eid').val(sales_order_id);
	load_po_hist_datatable();
	load_party_po_dtl();
	show_document_attach();
	$(".add_so_apprv_hist").css("display", "block");
	$(".add_oa_apprv_hist").css("display", "none");
}
function load_po_hist_datatable() {
	var sales_order_id = $('#ref_ord_id').val();

	$("#order-po-history-datatable").dataTable({
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
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20, "All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + crm_domain + 'app/sales_order/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "load_po_hist_datatable" }, { "name": "sales_order_id", "value": sales_order_id });
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
function load_party_po_dtl() {
	var sales_order_id = $('#ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_party_po_dtl", sales_order_id: sales_order_id },
		success: function (resp) {
			var resp = JSON.parse(resp);
			$('#mod_so_comp_div_sec').html(resp.mod_so_comp_div_sec);
			$('#mod_so_pro_div_sec').html(resp.mod_so_pro_div_sec);
		}
	});
}


function add_po_apprv_hist() {

	var form_data = {
		mode: "add_po_apprv_hist",
		approve_status: $('#po_approve_status').val(),
		approve_remark: $('#po_approve_remark').val(),
		sales_order_id: $('#ref_ord_id').val()
	};

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: form_data,
		success: function (response) {
			var arr = jQuery.parseJSON(response);
			if (arr.msg == '1') {
				$('#po_approve_status').select2("val", "0");
				$('#po_approve_remark').val("");
				toastr.success("APROOVE SUCCESSFULLY", "SUCCESS");
				load_po_hist_datatable();
				$('#preview_po_approval_hist_modal').modal('hide');
				//load_order_confirm_datatable();
				load_datatable();
				Unloading();
			} else {
				$('#po_approve_status').select2("val", "0");
				$('#po_approve_remark').val("");
				toastr.success("REJECT SUCCESSFULLY", "SUCCESS");
				load_po_hist_datatable();
				$('#preview_po_approval_hist_modal').modal('hide');
				//load_order_confirm_datatable();
				load_datatable();
				Unloading();
			}
		}
	});
}
function load_transport_detail_party_wise() {
	var cust_id = $("#cust_id").val();
	var id = $("#transport_edit_id").val();

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_transport_detail_party_wise", cust_id: cust_id, id: id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$('#transport_id').select2("val", "");
			$('#transport_id').html(obj.trans_detail);
			$('#transport_id').select2("val", id);

		}
	});

}
/*
Code By Umair : 13-07-2021
Comment: Load Product Based On the Inquiry Type
START
*/
function load_product_dtls(product_id) {
	var product_attr = $('#product_id').find('option:selected').attr('data-type');
	var data = $("#product_id").select2('data');
	var quot_trn_id = data.quotation_trn_id;
	var currency_id = $("#currency_id").val();
	var branch_id = $('#branch_id').val();
	if (branch_id == '') {
		toastr.warning("Select branch", "ERROR");
		$('#product_id').select2("val", '');
		$("#branch_id").focus();
		return false;
	}
	if (quot_trn_id) {
		get_hsn(product_id);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "load_product_dtls_quotation", quot_trn_id: quot_trn_id, currency_id: currency_id },
			success: function (response) {
				var resp = jQuery.parseJSON(response);
				load_product_unit(product_id, resp.unitid);
				$('.taxtype').show();
				$('#taxtype').text(resp.product_gst);
				$('#parent_category_id').select2("val", resp.product_category);
				$('#product_category_id').select2("val", resp.parent_category);
				$('#product_item_code').val(resp.product_icode);
				$("#product_qty").attr('placeholder', resp.product_qty);
				$("#product_conv_qty").attr('placeholder', resp.product_conv_qty);
				$("#product_rate").val(resp.product_rate);
				$("#discount_per").val(resp.discount_per);
				$('#current_stock').css('display', 'block');
				$('#orange').val(resp.orange);
				$('#mfg').val(resp.mfg);
				$('#trading').val(resp.trading);
				$('#repairing').val(resp.repairing);
				$('#other').val(resp.other);
				$('#orange_total').val(resp.orange_total);
				$('#mfg_total').val(resp.mfg_total);
				$('#trading_total').val(resp.trading_total);
				$('#repairing_total').val(resp.repairing_total);
				$('#other_total').val(resp.other_total);
				CKEDITOR.instances['product_des'].setData(resp.product_desc);
				CKEDITOR.instances['product_spec'].setData(resp.product_spec);
				$('#current_stock').html('Current Stock: ' + resp.current_stock);
				get_amount();
			}
		});
	} else {
		var cust_id = $('#cust_id').val();
		var atlas_permission = $('#atlas_permission').val();
		if (atlas_permission == '0') {
			if (!cust_id) {
				toastr.warning("Please Select Customer First", "ERROR");
				$('#cust_id').select2('focus');
				$('#product_id').select2("val", "");
				return false;
			}
		}
		get_hsn(product_id);
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "load_product_dtls", product_id: product_id, cust_id: cust_id },
			success: function (response) {
				//console.log(response);
				var resp = jQuery.parseJSON(response);
				$('#unitid').val(resp.product_base_unit);
				$('#product_rate').val(resp.product_sale_rate);

				if (resp.disc_per != "") {
					$("#discount_per").val(resp.disc_per);
				} else {
					$("#discount_per").val(0);
				}

				$('.taxtype').show();
				$('#taxtype').text(resp.product_gst);
				$('#product_item_code').val(resp.product_icode);
				$('#product_qty').val('0');
				$('#product_amount').val('0');
				$('#formulaid').val(resp.formula_id);
				CKEDITOR.instances['product_des'].setData(resp.product_desc);
				CKEDITOR.instances['product_spec'].setData(resp.product_spec);
				load_product_unit(product_id);
				$('#current_stock').css('display', 'block');
				$('#current_stock').html('Current Stock: ' + resp.current_stock);

				if (resp.product_gst == 'including') {
					var prouduct_amt = (parseInt(resp.product_sale_rate) * parseInt(100) / (parseInt(100) + parseInt(resp.tax_gst))).toFixed(2);
					var tax_rate = parseFloat(resp.product_sale_rate) - parseFloat(prouduct_amt);
					$('#pro_amt').text(prouduct_amt);
					$('.pro_amt').show();
					$('#taxrate').text(tax_rate);
					$('.taxrate').show();
					$("#taxper").val(resp.tax_gst);
				} else {
					$('.pro_amt').hide();
					$('.taxrate').hide();
				}
				Unloading();
			}
		});
	}
	if (product_attr != 'projectwise') {
		$('#projectItem').css('display', 'none');
	} else {
		$('#projectItem').css('display', 'block');
		add_project_data();
	}
}
/*function load_product_unit(product_id,unit_id){
	if(product_id)//tax calculation on total 
	{
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+ crm_domain +'app/sales_order/',
			data: { mode : "load_product_unit", product_id : product_id},
			success: function(response)
			{
				var obj=jQuery.parseJSON(response);
				//alert(obj.qye);
				$('#unitid').val(obj.product_conv_unit);
				$('#conv_unitid').val(obj.product_base_unit);
				
				$('#unit_show').html(obj.convert_unit_name);
				$('#convert_unit_show').html(obj.base_unit_name);
				$("#convert_unit_block").show();
				if(obj.unit_status==="1"){
					$("#convert_unit_block").show();
				}else{
					$("#convert_unit_block").hide();
				}
			}
		});
	}
}*/
function product_convert_qty(type) {
	if (type == 2) {
		var conv_qty_hide = $("#product_qty").val();
		var s = parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var num = $("#product_qty_hide").val();
		var d = parseFloat(num);
		resultb = d.toFixed(3);
		if (resultb === results) {
			get_amount();
			return false;
		}
		var product_conv_qty_hide = $("#product_conv_qty_hide").val();
	} else {
		var base_qty_hide = $("#product_conv_qty").val();
		var d = parseFloat(base_qty_hide);
		resultb = d.toFixed(3);

		var base_qty_hidess = $("#product_conv_qty_hide").val();
		var s = parseFloat(base_qty_hidess);
		results = s.toFixed(3);

		if (resultb === results) {
			get_amount();
			return false;
		}
		var conv_qty_hide = $("#product_qty").val();
	}

	var base_qty = $("#product_conv_qty").val();
	var conv_qty = $("#product_qty").val();
	var product_id = $("#product_id").val();

	if (product_id) {
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "convert_qty", type: type, base_qty: base_qty_hide, conv_qty: conv_qty_hide, product_id: product_id },
			success: function (response) {

				var arr = jQuery.parseJSON(response);
				if (type === 1) {
					$("#product_conv_qty_hide").val(base_qty);
				} else if (type === 2) {
					$("#product_qty_hide").val(conv_qty);
				}

				if (type === 1) {
					$("#product_qty").val(arr.show_qty);
					$("#product_qty_hide").val(arr.hide_qty);

				} else if (type === 2) {
					$("#product_conv_qty").val(arr.show_qty);
					$("#product_conv_qty_hide").val(arr.hide_qty);

				} else {
					$("#product_conv_qty").val(arr.show_qty);
					$("#product_conv_qty_hide").val(arr.hide_qty);
					$("#product_qty").val(arr.show_qty);
					$("#product_qty_hide").val(arr.hide_qty);
				}
				get_amount();
			}
		});
	} else {
		toastr.warning("Select Product First", "WARNING");
		$("#product_conv_qty").val("0");
		$("#product_conv_qty_hide").val("0");
		$("#product_qty").val("0");
		$("#product_qty_hide").val("0");
	}
}

function load_inquiry_type_product() {
	var pro_type = $('#pro_type').val();
	var pro_search = $('#pro_search').val();
	var inquiry_type = $('#inquiry_type').val();
	$('#projectItem').css('display', 'none');
	if (inquiry_type) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "load_inquiry_type_product", inquiry_type: inquiry_type, pro_type: pro_type, pro_search: pro_search },
			success: function (response) {
				//console.log(response);
				var obj = jQuery.parseJSON(response);
				$('#product_id').empty().append(obj.product_list);
				// $("#product_id").select2({
				// 	width: '100%'
				// });
				Unloading();
			}
		});
	}
}

function load_project_item() {
	/*var branch_id = $('#branch_id').val();
	if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$("#branch_id").focus();
		return false;
	}*/
	$('#add_project_wise_item_modal').modal('show');

	/*var eid = $('#eid').val();
	if(eid==''){
		add_project_data();
	}*/
	show_project_data();
}

function add_project_data() {
	var inquiry_type = $('#inquiry_type').val();
	var project_assign_id = $('#product_id').val();
	var eid = $('#eid').val();
	var branch_id = $('#branch_id').val();
	var quotation_id = $("#quotaion_id").val();
	var inquiry_id = $("#project_inquiry_id").val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: {
			mode: "add_project_data",
			project_assign_id: project_assign_id,
			inquiry_type: inquiry_type,
			eid: eid,
			branch_id: branch_id,
			inquiry_id: inquiry_id,
			quotation_id: quotation_id
		},
		success: function (data) {
		}
	});
}

function show_project_data() {
	var inquiry_type = $('#inquiry_type').val();
	var project_assign_id = $('#product_id').val();
	var eid = $('#eid').val();
	var salesorder_trn_id = $('#salesorder_trn_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: {
			mode: "load_project_tempoutward",
			project_assign_id: project_assign_id,
			inquiry_type: inquiry_type,
			eid: eid,
			salesorder_trn_id: salesorder_trn_id
		},
		success: function (data) {
			$('#sale_productdata').html(data);
		}
	});
}

function add_project_field() {
	if ($("#project_product_id").val() === "") {
		toastr.warning("Select Product Name", "ERROR")
		return false;
	}
	if ($("#project_product_qty").val() === "") {
		toastr.warning("Enter Qty", "ERROR")
		return false;
	}
	if ($("#project_product_rate").val() === "") {
		toastr.warning("Enter Rate", "ERROR")
		return false;
	}
	if ($("#branch_id").val() === "") {
		toastr.warning("Select Branch Id", "ERROR")
		return false;
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: {
			mode: "add_project_field",
			edit_id: $("#project_edit_id").val(),
			salesorder_trn_id: $("#salesorder_trn_id").val(),
			product_id: $("#project_product_id").val(),
			product_des: $("#project_product_des").val(),
			product_spec: $("#project_product_spec").val(),
			product_hsn_code: $("#project_product_hsn_code").val(),
			product_qty: $("#project_product_qty").val(),
			product_rate: $("#project_product_rate").val(),
			project_assign_id: $("#product_id").val(),
			inquiry_type: $("#inquiry_type").val(),
			branch_id: $("#branch_id").val(),
			inquiry_id: $('#project_inquiry_id').val(),
			formulaid: $("#project_formulaid").val(),
			quotation_id: $("#quotaion_id").val(),
			eid: $('#eid').val()
		},
		success: function (response) {
			$("#project_product_id").select2("val", "")
			$("#project_product_des").val("")
			$("#project_product_spec").val("")
			$("#project_product_hsn_code").val("")
			$("#project_product_qty").val("")
			$("#project_product_rate").val('')
			$("#project_edit_id").val('')
			$('#project_addrow').val('Add');
			$("#project_formulaid").val("");
			Unloading();
			show_project_data();
		}
	});
}
// function load_productdetail(val) {
// 	/*if(val!=0)
// 	{
// 		$('#addproduct').hide();
// 	}
// 	else
// 	{
// 		$('#addproduct').show();
// 	}*/

// 	$.ajax({
// 		type: "POST",
// 		url: root_domain + crm_domain +'app/sales_order/',
// 		data: { mode : "load_productdata",eid :val },
// 		success: function(response)
// 		{
// 			var obj =jQuery.parseJSON(response);
// 			CKEDITOR.instances['project_product_des'].setData(obj.product_desc);
// 			CKEDITOR.instances['project_product_spec'].setData(obj.product_spec);	
// 			$('#project_product_hsn_code').val(obj.product_hsn);
// 			$('#project_product_rate').val(obj.product_sale_rate);
// 			$('#project_formulaid').val(obj.fom_id);

// 		}
// 	});
// }
function edit_project_data(id) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "edit_project_data", id: id },
		success: function (response) {
			//console.log(response)
			var data = jQuery.parseJSON(response);
			$("#project_product_id").select2("val", data.product_id)
			$("#project_product_hsn_code").val(data.product_hsn_code)
			$("#project_product_des").val(data.description)
			$("#project_product_qty").val(data.product_qty)
			$("#project_product_rate").val(data.product_rate)
			$("#project_formulaid").val(data.formulaid);
			$("#project_edit_id").val(id)
			$('#project_addrow').val('Update');
			CKEDITOR.instances['project_product_des'].setData(data.product_desc);
			CKEDITOR.instances['project_product_spec'].setData(data.product_spec);
			Unloading();
		}
	});
}
function delete_project_data(id) {
	var r = confirm(" Are you want to delete ?");

	if (r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "delete_project_data", eid: id },
			success: function (response) {
				//console.log(response)
				var data = jQuery.parseJSON(response)
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_project_data();
					Unloading();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}
}
function get_project_amount() {
	var product_qty = parseFloat($("#project_product_qty").val());
	var product_rate = parseFloat($("#project_product_rate").val());

	if (product_qty && product_rate && product_qty != '0' && product_rate != '0') {
		var product_amount = parseFloat((product_qty) * (product_rate));
		/*$("#product_amount").val(parseFloat(product_amount).toFixed(2));
		$("#product_total").val(parseFloat(product_amount).toFixed(2));*/
		if ($("#project_formulaid").val() != "")//tax calculation
		{
			var formulaid = $("#project_formulaid").val();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/quotation/',
				data: { mode: "get_project_amount", product_amount: product_amount, formulaid: formulaid },
				success: function (response) {
					var obj = jQuery.parseJSON(response);
					//$('#product_total').val(obj.product_total);
				}
			});
		}
	}
	else {
		//$("#product_amount").val(0);
	}
}
function preview_cust_dtls() {
	var cust_id = $('#cust_id').val();
	if (cust_id) {

		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "preview_cust_dtls", cust_id: cust_id },
			success: function (response) {
				//console.log(response);
				var obj = jQuery.parseJSON(response);
				$('#preview_cust_dtls_modal1').modal('show');
				$('#preview_cust_dtls_div').html(obj.html_resp);
				$('#preview_cust_pro_div').html(obj.row);
				Unloading();
			}
		});
	} else {
		toastr.warning("Select Company First", "ERROR");
	}
}
function load_product_history() {
	//$('#preview_product_history_modal').modal('show');
	show_product_history_data();
}
function show_product_history_data() {
	var cust_id = $('#cust_id').val();
	var product_id = $('#product_id').val();
	var eid = $('#eid').val();
	if (cust_id == '') {
		toastr.warning("Select Company First", "ERROR");
		$("#cust_id").focus();
		return false;
	}
	if (product_id == '') {
		toastr.warning("Select Product First", "ERROR");
		$("#product_id").focus();
		return false;
	}
	if (product_id && cust_id) {
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "load_product_history", product_id: product_id, cust_id: cust_id, eid: eid },
			success: function (data) {
				$('#preview_product_history_modal').modal('show');
				$('#preview_product_history_div').html(data);
			}
		});
	}
	// else{
	// 	toastr.warning("Select Company and Product First", "ERROR");
	// }
}
function terms_check_all(obj,) {
	$('.terms_checkbox').prop('checked', obj.checked);
}
var vipul_copper_permission = $('#vipul_copper_permission').val();
if (vipul_copper_permission != '1') {
	function product_load() {
		var testData = [];
		var inquiry_type = $("#inquiry_type").val();
		var product_category = $("#product_category_id").val();
		//$("#product_id").html("");
		var mainurl = root_domain + crm_domain + 'app/product_load/index.php?mode=product_load&inquiry_type=' + inquiry_type + '&type=so_pro_type&search=sales_pro_search';
		$.getJSON(mainurl, function (json) {
			var arr = new Array();
			var len = json[0].length;
			// console.log(len);

			for (var i = 0; i < len; i++) {
				testData.push({ id: json['0'][i], text: json['1'][i] });
				//alert(json['1'][i]);
			}
		});

		return testData;
	}

	$('#product_id').select2({
		data: product_load(),
		placeholder: 'search',
		multiple: false,
		// query with pagination
		query: function (q) {
			var pageSize,
				results,
				that = this;
			pageSize = 20; // or whatever pagesize
			results = [];
			if (q.term && q.term !== '') {
				// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
				results = _.filter(that.data, function (e) {
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
}
function add_document_attach() {
	var ext = $('#doc_attach').val().split('.').pop().toLowerCase();
	// if($.inArray(ext, ['pdf','doc','docx']) === -1) {
	// 	toastr.warning("Only image type pdf/doc/docx is allowed", "ERROR");
	// 	$("#doc_attach").focus();
	// 	return false;
	// }

	if (!$("#doc_attach").val()) {
		toastr.warning("Choose File", "ERROR");
		$("#doc_attach").focus();
		return false;
	}

	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_document_attach");
	form_data.append('design_dept', $("#design_dept").val());
	form_data.append('doc_name', $("#doc_name").val());
	form_data.append('sales_order_id', $("#eid").val());
	form_data.append("doc_attach", document.getElementById('doc_attach').files[0]);

	$.ajax({
		type: "POST",
		url: root_domain + 'crm/app/sales_order/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function (response) {
			//console.log(response);
			$("#doc_name").val("").focus();
			$("#design_dept").select2("val", "0").focus();
			$("#doc_attach").val("").focus();
			$('#dfd_attch_btn').val('Add');
			Unloading();
			show_document_attach();
			var cnt = $('#po_document_count').val();
			cnt = parseInt(cnt) + parseInt(1);
			$('#po_document_count').val(cnt);
		}
	});
}

function show_document_attach() {
	var eid = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + 'crm/app/sales_order/',
		data: { mode: "show_document_attach", sales_order_id: eid },
		success: function (resp) {
			//console.log(resp);
			$('#po_doc_list').html(resp);
			Unloading();
		}
	});
}
function delete_document_attach(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + 'crm/app/sales_order/',
			data: { mode: "delete_document_attach", attach_id: id },
			success: function (response) {
				//console.log(response);
				var data = jQuery.parseJSON(response);
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_document_attach();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				Unloading();
			}
		});
	}
}

/* END */
function load_consignee_detail() {
	var cust_id = $("#cust_id").val();
	var consignee_id = $("#consignee_id").val();

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_consignee_detail", cust_id: cust_id, consignee_id: consignee_id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$('#consignee_id').select2("val", "");
			$('#consignee_id').html(obj.consignee_detail);
			$('#consignee_id').select2("val", consignee_id);
		}
	});

}

// added by sanat :: 24-09-2021

function get_hsn(product_id) {
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "get_hsn_code", product_id: product_id },
		success: function (response) {
			if (response != '') {
				$('#hsncode').text(response);
				$('#product_hsn_code').val(response);

				$(".hsncode").show();
			} else {
				toastr.warning("Please select valid HSN code product", "WARNING");
				$(".hsncode").hide();
				$(".product_stock_label").hide();
				$('#product_id').select2("val", "");
				return false;
			}
		}
	});

}


function get_statecode(cust_id) {
	if (cust_id) {
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "get_gst_statecode", cust_id: cust_id },
			success: function (response) {
				var res = response.split(",");
				if (res) {
					$("#statecode").show();
					$(".statecode").text(res[0]);
					$("#cust_stateid").val(res[1]);
				} else {
					$("#statecode").hide();
				}
			}
		});
	} else {
		$("#statecode").hide();
	}
}

function get_statecodes(cust_id) {
	if (cust_id) {
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "get_gst_statecode", cust_id: cust_id },
			success: function (response) {
				var res = response.split(",");
				if (res) {
					$("#statecode").show();
					$(".statecode").text(res[0]);
					$("#cust_stateid").val(res[1]);
				} else {
					$("#statecode").hide();
				}
			}
		});
	}
}
function get_grossbalance(cust_id) {
	if (cust_id) {
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "get_grossbalance", cust_id: cust_id },
			success: function (response) {
				if (response) {
					$("#gross").show();
					$(".gross").text(response);
				} else {
					$("#gross").hide();
				}
			}
		});
	} else {
		$("#gross").hide();
		$(".gross").text('');
	}
}
function load_product_unit(product_id, edit_unit) {
	if (product_id) {

	} else {
		var product_id = $("#product_id").val();
	}
	if (edit_unit) {

	} else {
		var edit_unit = $("#rate_unit_id").val();
	}
	//alert(product_id);
	if (product_id)//tax calculation on total 
	{

		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "load_product_unit", product_id: product_id },
			success: function (response) {
				console.log(response);
				var obj = jQuery.parseJSON(response);
				//alert(obj.qye);
				$("#rate_unit_id").html(obj.unit_option);
				//alert(edit_unit);
				if (edit_unit != "0") {
					//alert(edit_unit);
					$("#rate_unit_id").val(edit_unit);
					if (edit_unit === obj.product_base_unit) {
						$("#rate_unit_id").val(edit_unit);
					} else if (edit_unit === obj.product_conv_unit) {
						$("#rate_unit_id").val(edit_unit);
					} else {
						$("#rate_unit_id").val(obj.product_conv_unit);
					}
					if (obj.product_base_unit === edit_unit) {
						$("#base_unit_block").show();
						$("#convert_unit_block").hide();
						$("#pro_cal_type").val("product_qty_hide");
					} else {
						$("#base_unit_block").hide();
						$("#convert_unit_block").show();
						$("#pro_cal_type").val("product_conv_qty_hide");
					}
				} else {
					$("#base_unit_block").show();
					$("#convert_unit_block").hide();
					$("#pro_cal_type").val("product_qty_hide");
				}

				$('#unitid').val(obj.product_base_unit);
				$('#conv_unitid').val(obj.product_conv_unit);

				$('#unit_show').html(obj.base_unit_name);
				$('#convert_unit_show').html(obj.convert_unit_name);
				get_amount(); get_discount('per');
				/*$("#convert_unit_block").show();
				if(obj.unit_status==="1"){
					$("#convert_unit_block").show();
				}else{
					$("#convert_unit_block").hide();
				}*/
			}
		});
	}
}


function get_invoice_total_tax() {

	var eid = $('#eid').val();
	if (eid == "") {
		eid = 0;
	}
	var addontax1 = 0;
	$(".addontax").each(function () {
		var addontax = (this.value).split("-");
		addontax1 = Number(addontax1) + Number(addontax[0]);
	});
	var currency_id = $("#currency_id").val();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "get_invoice_total_tax", cust_id: $('#cust_id').val(), gross: $('.gross').text(), inv_total: $('#total').val(), invoice_id: eid, addontax1: addontax1, user_id: $('#user_id').val(), currency_id: currency_id },
		success: function (response) {
			// console.log(response);
			var arr = JSON.parse(response);
			if (arr) {
				$(".invoiceTotalTax").html(arr.resp);
				if (arr.isTcs == 1) {
					$('.tcs_details').show();
				} else {
					$('.tcs_details').hide();
				}
				//$(".gross").text(response);
			}
		}
	});
}


function get_ledger_details(ledger_id) {
	//alert(ledger_id);
	var company_cost_center = $('#company_cost_center').val();
	var company_tcs = $('#company_tcs').val();
	var company_eway = $('#company_eway').val();
	var company_salesman = $('#company_salesman').val();
	var company_trans = $('#company_trans').val();
	var kind_attn_hidden = $('#kind_attn_hidden').val();
	//alert(company_trans);

	$.ajax({

		type: 'POST',
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "get_ledger_details", ledger_id: ledger_id },
		success: function (result) {
			var obj = JSON.parse(result);
			//Cost Center popup
			if (obj.enable_cost_center == 1 && company_cost_center == 1) {
				$('#div_cost_center').show();
			}

			$("#kind_attn").empty().html(obj.c_person);
			$("#kind_attn").val(kind_attn_hidden);
			//TCS Popup
			if (obj.enable_tcs == 1 && company_tcs == 1) {
				$('#tcs_div').show();
			}

			//Eway Bill Popup
			if (company_eway == 1) {
				$('#eway_div').show();
			}

			//Salesman Popup

			if (company_salesman == 1) {
				$('#salesman_div').show();
			}
			else {
				$('#salesman_div').hide();
			}

			//Check SEZ Enable

			if (obj.enable_sez == 1) {
				$('#sez_enable_text').show();
			}
			else {
				$('#sez_enable_text').hide();
			}

			//Transport Popup
			if (company_trans == 1) {
				$('#tran_div').show();
			}
			else {
				$('#tran_div').hide();
			}

		}
	})

}

function get_peyment_terms_details(ledger_id) {

	$.ajax({

		type: 'POST',
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "get_peyment_terms_details", ledger_id: ledger_id },
		success: function (result) {

			var obj = JSON.parse(result);

			$("#payment_terms").val(obj.terms_id);
		}
	})

}


function get_tax_details_table() {

	var eid = $('#eid').val();
	if (eid == "") {
		eid = 0;
	}
	var cust_id = $('#cust_id').val();
	var user_id = $('#user_id').val();
	var currency_id = $('#currency_id').val();
	var addontax1 = [];
	$(".addontax").each(function () {
		//alert(this.value);
		addontax1.push(this.value);
	});

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "get_tax_details_table", invoice_id: eid, cust_id: cust_id, addontax1: addontax1, user_id: user_id, currency_id: currency_id },
		success: function (response) {

			var arr = JSON.parse(response);
			if (arr) {
				$(".tax_details").html(arr.resp);
				//$(".gross").text(response);
			}
		}
	});
}

function get_gtotal(id) {
	var input_amount = (document.getElementsByName('amount[]'));
	var default_amount = document.getElementsByName('default_amount[]');

	var cnt = input_amount.length;
	var cnt1 = default_amount.length;
	//alert(cnt1);
	var total = 0;
	var c_total = 0;
	var gst = 0;


	if (total == "") {
		total = 0;
	}
	for (var i = 0; i < cnt; i++) {
		var t = input_amount[i].value;
		if (t > 0)
			total = parseFloat(total) + parseFloat(t);
		// alert(total);
	}

	var totalFloat = parseFloat(total);
	var formattedTotal = totalFloat.toFixed(2);

	// $("#total").val(formattedTotal);
	$("#total").val(formattedTotal).trigger("change");

	var gst_arr = document.getElementsByClassName('gst');

	for (var k = 0; k < gst_arr.length; k++) {

		var k1 = gst_arr[k].value;
		total = parseFloat(total) + parseFloat(k1);
		//alert(total);
	}

	for (var j = 0; j < cnt1; j++) {
		var t1 = default_amount[j].value;
		total = parseFloat(total) + parseFloat(t1);

	}

	/*var cgst = $('#CGST').val();
	var sgst = $('#SGST').val();
	var igst = $('#IGST').val();
	var tcs = $('#TCS').val();

	if((cgst!= 0) && (sgst!= 0) && (typeof cgst  != "undefined") && (typeof sgst  != "undefined")){
		gst = Number(cgst)+Number(sgst);
	}else if(igst!='' && (typeof igst  != "undefined")){
		gst = Number(igst);
	}else{
		gst = 0;
	}

	if((tcs != '') && (typeof tcs  != "undefined")){
		tcs = Number(tcs);
	}else{
		tcs = 0;
	} */



	//g_total= Number(gst) + Number(total.toFixed(2)) + Number(tcs);
	g_total = Number(total.toFixed(2));
	$("#g_total").val(g_total.toFixed(2));
	$("#paid_amount").val(g_total.toFixed(2));

	update_total();

	$("#g_total").val(g_total.toFixed(2)).trigger("change");
	$("#paid_amount").val(g_total.toFixed(2));
	if ($("#advance_payment").val() != "") {
		total = parseFloat(total) - parseFloat($("#advance_payment").val());
	}
	if ($("#adv_amt").val() != "") {
		total = parseFloat(total) - parseFloat($("#adv_amt").val());
	}
	$("#pending_amount").html(Number(total.toFixed(2)) + "/-");
	$("#pen_amt").val(Number(total.toFixed(2)));
}

function get_advance(type) {
	var net_amt = parseFloat($("#g_total").val());
	var disc = 0;
	if (net_amt != "") {
		if (type == "amt") {
			disc = 100 * parseFloat($('#adv_amt').val()) / net_amt;
			if (isNaN(disc)) {
				var disc = 0;
			}
			$('#adv_per').val(disc);
		}
		else if (type == "per") {
			disc = (net_amt * parseFloat($('#adv_per').val())) / 100;
			if (isNaN(disc)) {
				var disc = 0;
			}
			$('#adv_amt').val(disc);
		}
	}
	else {
		$('#adv_amt').val('');
		$('#adv_per').val('');
	}
	get_gtotal();
}


function update_total() {
	var eid = $('#eid').val();
	var g_total = $('#g_total').val();
	var basic_total = $('#total').val();
	var branch_id = $('#branch_id').val();
	var currency_id = $("#currency_id").val();
	var currency_rate = $("#currency_rate").val();
	//var bill_sundry_tax = document.getElementsByName('bill_sundry_tax[]');
	/*var gst=[];
	var addonsundry = {};
	
	var values = $("input.gst");
	$.each(values, function(key, value) {
		
		var new_key = this.name.match(/\d+/);
		gst[new_key] = $(this).val();
	});*/
	var gst1 = [];
	var gst2 = [];
	var addonsundry = {};
	var values = $("input.gst");
	$.each(values, function (key, value) {
		var new_key = this.name.match(/\d+/);
		gst1.push(new_key[0]);
		gst2.push($(this).val());
		/*var new_key = this.name.match(/\d+/);
		gst[new_key] = $(this).val();*/
		//gst.push($(this).val());
		//var new_key = this.name.match(/\d+/);
		//console.log("-->"+key+" :: "+new_key + "  :: " +  $(this).val());
	});


	$.ajax({

		type: 'POST',
		data: { mode: 'update_total', invoice_id: eid, g_total: g_total, basic_total: basic_total, branch_id: branch_id, currency_id: currency_id, currency_rate: currency_rate, bill_sundry_tax: gst1, bill_sundry_tax1: gst2 },
		url: root_domain + crm_domain + 'app/sales_order/',
		success: function (result) {
			// console.log(result);
			//alert(result);
		}

	})

}

function get_sundry_label(sundry_id) {
	//alert(sundry_id);

	$.ajax({

		type: 'POST',
		url: root_domain + finance_root_domain + 'app/salereturn/',
		data: { mode: "get_bill_sundry_label", sundry_id: sundry_id },
		success: function (data) {
			//alert(data);
			if (data == 1) {
				$('#bill_sundry_amount').attr("placeholder", "Amount");
			}
			else {
				$('#bill_sundry_amount').attr("placeholder", "%");
			}
		}
	})

}

var rowIdx = 0;
// jQuery button click event to add a row
function addBillSundry() {

	Loading(true);

	var taxableamount = 0;
	var totalsundryexist = 0;
	var basic_amount = $("#total").val();
	var netamount = $("#g_total").val();
	//alert(netamount);
	$(".gst").each(function () {
		var gstVal = $('.gst').val();
		taxableamount = Number(taxableamount) + Number(gstVal);
	});

	$(".billsundryclass").each(function () {
		var billsundryclass = $(this).val();
		totalsundryexist = Number(totalsundryexist) + Number(billsundryclass);
	});

	var eid = $('#eid').val();

	var bill_sundry_value = $("#bill_sundry").val();

	var bill_sundry = $("#bill_sundry option:selected").text();
	var bill_sundry_amount = $('#bill_sundry_amount').val();

	var currency_enable = $('#currency_enable').val();
	var currency_id = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();
	var gst_type = $('#gst_type').val();
	var user_id = $('#user_id').val();

	if (bill_sundry_value == 0) {
		toastr.warning("Please Select Bill Sundry", "ERROR");
		$("#bill_sundry").focus();
		return false;
	} else if (bill_sundry_amount == '') {
		toastr.warning("Please insert Bill Sundry Amount", "ERROR");
		$("#bill_sundry_amount").focus();
		return false;
	} else {
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "get_bill_sundry_details", sundry_ledger_id: bill_sundry_value, totalsundryexist: totalsundryexist, taxableamount: taxableamount, basic_amount: basic_amount, netamount: netamount, gst_type: gst_type, default_amount: bill_sundry_amount, invoice_id: eid, currency_enable: currency_enable, currency_id: currency_id, currency_rate: currency_rate, invoice_date: $('#invoice_date').val(), user_id: user_id },
			success: function (response) {

				var arr1 = JSON.parse(response);
				var arr = arr1.split(",");
				if (arr[3]) {
					get_all_bill_sundry(eid);
					//get_gtotal();
				}
				else {
					if (arr[0]) {

						if (arr[4] != 0) {
							$('.sundryadded').append(`<div class="form-group R${++rowIdx}">
								<label class="col-md-3 control-label">${bill_sundry}${arr[2]} <span class="currency_icon"></span></label>
								<div class="col-md-6 col-xs-12">
								<input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[1]}">
								<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="${arr[1]}" readonly placeholder="Amount">
								<input class="addontax" name="bill_sundry_addon_tax[${bill_sundry_value}]" type="hidden" value="${arr[4]}-${arr[5]}-${arr[1]}" >
								</div>
								<div class="col-md-3">
								<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
								type="button" value="R${rowIdx}" onclick="removeSundry(${bill_sundry_value},${arr[1]},this.value)"><i class="fa fa-times"></i></button>
								</div>
								
								</div>`);

							get_invoice_total_tax();
							get_tax_details_table();
						} else {
							$('.sundryadded').append(`<div class="form-group R${++rowIdx}">
								<label class="col-md-3 control-label">${bill_sundry}${arr[2]} <span class="currency_icon"></span></label>
								<div class="col-md-6 col-xs-12">
								<input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[1]}">
								<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="${arr[1]}" readonly placeholder="Amount">
								</div>
								<div class="col-md-3">
								<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
								type="button" value="R${rowIdx}" onclick="removeSundry(${bill_sundry_value},${arr[1]},this.value)"><i class="fa fa-times"></i></button>
								</div>
								</div>`);
						}
						$('#bill_sundry').val('0');
						$('#bill_sundry_amount').val('');
						get_gtotal();
					}
				}
			}
		});
		get_symbol();

	}

	Unloading();

}

function removeSundry(bill_sundry_value, bill_sundry_amount, id, ledger_id = '') {

	Loading(true);

	var edit_id = $('#eid').val();
	var cust_ledger_id = $("#cust_id").val();
	//alert(ledger_id);

	if (edit_id == '' || edit_id == '0') {
		var netamount = $("#g_total").val();

		var finalNetAmount = Number(netamount) - Number(bill_sundry_amount);

		$("#g_total").val(finalNetAmount);

		$('.' + id).remove();
		get_invoice_total_tax();
		get_tax_details_table();
		get_gtotal();
	}
	else {

		$.ajax({

			type: 'post',
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: 'remove_sundry', edit_id: edit_id, ledger_id: ledger_id, cust_ledger_id: cust_ledger_id },
			success: function (result) {
				get_invoice_total_tax();
				get_all_bill_sundry(edit_id);
				get_gtotal();
			}
		})
	}

	Unloading();
}


function get_all_bill_sundry(invoice_id) {

	var smode = $('#smode').val();

	if (smode == 'quotation_mode') {
		get_all_bill_sundrys(invoice_id);
	} else {
		$.ajax({

			type: 'POST',
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: 'get_all_bill_sundry', invoice_id: invoice_id },
			success: function (response) {
				// console.log(response);
				$('.sundryadded').html(response);
				get_tax_details_table();
				get_invoice_total_tax();
				get_gtotal();
				/*var arr1 = JSON.parse(response);
				var arr = arr1.split(",");
	
				if(arr[0]){
	
					$("#g_total").val(arr[0]);
					
					$('.sundryadded').append(`<div class="form-group R${++rowIdx}">
						<label class="col-md-5 control-label">${bill_sundry}${arr[2]}</label>
						<div class="col-md-4">
							<input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[1]}">
							<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="${arr[1]}" readonly placeholder="Amount">
						</div>
						<div class="col-md-3">
							<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
								type="button" value="R${rowIdx}" onclick="removeSundry(${bill_sundry_value},${arr[1]},this.value)"><i class="fa fa-times"></i></button>
						</div>
					</div>`);	
					$('#bill_sundry').val('0');
					$('#bill_sundry_amount').val('');
				} */
			}
		});
	}
}
function get_all_bill_sundrys(invoice_id) {
	$.ajax({

		type: 'POST',
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: 'get_all_bill_sundrys', invoice_id: invoice_id },
		success: function (response) {
			//console.log(response);
			$('.sundryadded').html(response);
			get_tax_details_table();
			get_invoice_total_tax();
			get_gtotal();
			/*var arr1 = JSON.parse(response);
			var arr = arr1.split(",");

			if(arr[0]){

				$("#g_total").val(arr[0]);
				
				$('.sundryadded').append(`<div class="form-group R${++rowIdx}">
					<label class="col-md-5 control-label">${bill_sundry}${arr[2]}</label>
					<div class="col-md-4">
						<input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[1]}">
						<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="${arr[1]}" readonly placeholder="Amount">
					</div>
					<div class="col-md-3">
						<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
							type="button" value="R${rowIdx}" onclick="removeSundry(${bill_sundry_value},${arr[1]},this.value)"><i class="fa fa-times"></i></button>
					</div>
				</div>`);	
				$('#bill_sundry').val('0');
				$('#bill_sundry_amount').val('');
			} */
		}
	});
}
function currency_change() {
	/*if($('#currency_enable').is(":checked"))
	{*/
	$('.currency_div').show();
	/*}
	else
	{
		$('.currency_div').hide();
	}*/
}

function get_symbol() {

	$(".sp_cr").remove();
	$(".currency_icon").html('');

	var symbl = $("#currency_id").find(':selected').attr("data-currency-symbol");
	//var rate = $("#currency_id").find(':selected').attr("data-currency-rate");
	var textt = " (" + symbl + ")";
	$(".currency_icon").each(function () {
		$(this).append(textt);
	});
	//$('#currency_rate').val(rate);
}

function currency_rate_c() {
	var rate = $("#currency_id").find(':selected').attr("data-currency-rate");
	$('#currency_rate').val(rate);
}
function copy_prev_so_trn(prev_sales_order_id) {
	if (prev_sales_order_id) {
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "copy_prev_so_trn", prev_sales_order_id: prev_sales_order_id },
			success: function (response) {
				//console.log(response);
				show_data();
			}
		});
	}
}
function get_revise_so_no(sales_order_id, start_sales_order_id) {

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "get_revise_so_no", sales_order_id: sales_order_id, start_sales_order_id: start_sales_order_id },
		success: function (data) {
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#sales_order_no').val(no.sales_order_no);

		}
	});
}
function showproduct() {
	branch_id = $('#branch_id').val();
	if (!branch_id) {
		toastr.warning("Choose Branch!!!", "ERROR");
		$('#branch_id').select2('focus');
		return false;
	}
	cust_id = $('#cust_id').val();
	if (!cust_id) {
		toastr.warning("Choose Company!!!", "ERROR");
		$('#cust_id').select2('focus');
		return false;
	}
	$('#modal-add-product').modal('show');
	$("#product_add_type").val('sales_order');
	//$("#ledger_name").focus();
}
function add_hsn_invoice() {
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-hsn').modal('show');
	$("#hsn_add_type").val('product_sales_order');
	$("#hsn_name").focus();
}
function showledger() {
	branch_id = $('#branch_id').val();
	if (!branch_id) {
		toastr.warning("Choose Branch!!!", "ERROR");
		$('#branch_id').select2('focus');
		return false;
	}
	$.fn.modal.Constructor.prototype.enforceFocus = function () { };
	$('#modal-add-ledger').modal('show');
	// get_opening_balance('0');
	$("#ledger_add_type").val('sales_order');
	$("#ledger_name").focus();
	var country = $("#countryid").val();
	var state = $("#stateid").val();
// 	load_state(country, 'stateid', '');
	load_city(state, 'cityid', '');
}
function getProductByCategoryID(category_id, type = '', product_id = '') {
	if (type == '') {
		var cust_id = $('#cust_id').val();
		if (cust_id == '') {
			toastr.warning("Please Select Customer First", "ERROR");
			$('#product_category_id').select2('val', '');
			$('#cust_id').select2('focus');
			return false;
		}
	}

	if (category_id != '') {
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: 'get_product_by_category', category_id: category_id, cust_id: cust_id },
			success: function (data) {
				$('#product_id').empty().append(data);
				$('#product_id').select2('val', product_id);
				$("#product_id").select2({
					width: '100%'
				});

			}
		});
	} else {
		toastr.warning('Please select category first.', "WARNING");
		$("#product_category_id").focus();
		return false;
	}
}
function load_productdetail(val) {
	if (val != 0) {
		$('#addproduct').hide();
	}
	else {
		$('#addproduct').show();
	}
	var cust_id = $('#cust_id').val();
	if (cust_id == '') {
		toastr.warning("Please Select Customer First", "ERROR");
		$('#product_id').select2('val', '');
		$('#cust_id').select2('focus');
		return false;
	}
	var product_category_id = $('#product_category_id').val();
	if (product_category_id == '') {
		toastr.warning("Please Select Category", "ERROR");
		$('#product_id').select2('val', '');
		$('#product_category_id').select2('focus');
		return false;
	}
	getdiemastername(val, cust_id);
	get_hsn(val);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_productdata", eid: val, cust_id: cust_id },
		success: function (response) {

			var obj = jQuery.parseJSON(response)
			$('#unitid').val(obj.product_base_unit);
			$('#product_rate').val(obj.product_sale_rate);
			$('.taxtype').show();
			$('#taxtype').text(obj.product_gst);
			$('#product_item_code').val(obj.product_icode);
			$('#product_qty').val('0');
			$('#product_amount').val('0');
			$('#formulaid').val(obj.formula_id);
			CKEDITOR.instances['product_des'].setData(obj.product_desc);
			CKEDITOR.instances['product_spec'].setData(obj.product_spec);
			load_product_unit(val);
			$('#current_stock').css('display', 'block');
			$('#current_stock').html('Current Stock: ' + obj.current_stock);

			if (obj.product_gst == 'including') {
				var prouduct_amt = (parseInt(obj.product_sale_rate) * parseInt(100) / (parseInt(100) + parseInt(obj.tax_gst))).toFixed(2);
				var tax_rate = parseFloat(obj.product_sale_rate) - parseFloat(prouduct_amt);
				$('#pro_amt').text(prouduct_amt);
				$('.pro_amt').show();
				$('#taxrate').text(tax_rate);
				$('.taxrate').show();
				$("#taxper").val(obj.tax_gst);
			} else {
				$('.pro_amt').hide();
				$('.taxrate').hide();
			}
			$('#product_hsn_code').val(obj.product_hsn);
		}
	});
}
function getdiemastername(prod_id, cust_id) {
	if (prod_id != '') {
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "get_die_master_name", prod_id: prod_id, cust_id: cust_id },
			success: function (response) {
				var obj = jQuery.parseJSON(response);
				if (obj.die_master_name) {
					$('#die_master_product_name').css("display", "block");
					$('#die_master_name').text(obj.die_master_name);
					$('#die_product_id').val(obj.die_product_id);
				} else {
					$('#die_master_product_name').css("display", "none");
					$('#product_qty').val("");
					$("#product_length").val("");
					$("#product_pices").val("");
					$('#die_product_id').val("");
				}
			}
		});
	}
}

function get_product_detail_calc(length_val, pices_val) {
	var length_val = $('#product_length').val();
	if (length_val != 0) {
		length_val = $('#product_length').val();
	}
	var pices_val = $('#product_pices').val();
	if (pices_val != 0) {
		pices_val = $('#product_pices').val();
	}
	var die_product_id = $('#die_product_id').val();
	if (die_product_id != '') {
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: {
				mode: "get_die_master_cal",
				length_val: length_val,
				pices_val: pices_val,
				die_product_id: die_product_id
			},
			success: function (response) {
				var obj = jQuery.parseJSON(response);
				if (obj.product_qty_calc) {
					$('#product_qty').val(obj.product_qty_calc);
				} else {
					$('#product_qty').val("");
				}
			}
		});
	} else {
		$('#product_qty').val("");
		$("#product_length").val("");
		$("#product_pices").val("");
		toastr.warning("Please select die master product otherwise please enter direct qty in textbox", "ERROR")
		return false;
	}
}
function short_close_so(sales_order_id) {
	var r = confirm(" Are you want to short close this sales order?");
	if (r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: {
				mode: "short_close_so",
				sales_order_id: sales_order_id
			},
			success: function (response) {
				var arr = jQuery.parseJSON(response);
				if (arr.msg == '1') {
					load_datatable();
					Unloading();
				} else {
					toastr.warning("SOMETHING WENT WRONG!!", "WARNING");
					load_datatable();
					Unloading();
				}
			}
		});
	}
}
function getrate() {
	var product_id = $('#product_id').val();
	var unit_id = $('#rate_unit_id').val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode: "getrate", product_id: product_id, unit_id: unit_id },
		success: function (response) {
			var data = jQuery.parseJSON(response);
			$('#product_rate').val(data.price);
			get_amount();
		}
	});
}
function open_po_approv_payments(sales_order_id, sales_order_no) {
	$('#preview_po_approval_hist_modal').modal('show');
	$('#apprv_po_ref_no').html(sales_order_no);
	$('#ref_ord_id').val(sales_order_id);
	$('#eid').val(sales_order_id);
	load_po_hist_datatables();
	load_party_po_dtl();
	show_document_attach();
	$(".add_so_apprv_hist").css("display", "none");
	$(".add_oa_apprv_hist").css("display", "block");
}
function load_po_hist_datatables() {
	var sales_order_id = $('#ref_ord_id').val();

	$("#order-po-history-datatable").dataTable({
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
		"sAjaxSource": root_domain + crm_domain + 'app/order_acceptance/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "load_po_hist_datatable" }, { "name": "sales_order_id", "value": sales_order_id });
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
function add_po_apprv_hists() {

	var form_data = {
		mode: "add_po_apprv_hists",
		approve_status: $('#po_approve_status').val(),
		approve_remark: $('#po_approve_remark').val(),
		sales_order_id: $('#ref_ord_id').val()
	};
	var status = 'Approved';
	if ($('#po_approve_status').val() === '0') {
		status = 'Rejected';
	}
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/order_acceptance/',
		data: form_data,
		success: function (response) {
			if (response) {
				$('#po_approve_status').select2("val", "0");
				$('#po_approve_remark').val("");
			} else {
				toastr.warning("You have already " + status, "ERROR");
				$('#po_approve_status').select2("val", "0");
				$('#po_approve_remark').val("");
			}
			load_po_hist_datatables();
			load_datatable();
			$('#preview_po_approval_hist_modal').modal('hide');
			Unloading();
		}
	});
}
function delete_approve_log(sales_order_id, approve_id, approve_status, type) {
	var r = confirm(" Are you want to delete this log?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/order_acceptance/',
			data: { mode: "delete_approve_log", sales_order_id: sales_order_id, approve_id: approve_id, approve_status: approve_status, type: type },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
					if (type == 1) {
						load_po_hist_datatable();
					} else {
						load_po_hist_datatables();
					}
					Unloading();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				$('#preview_po_approval_hist_modal').modal('hide');
				load_datatable();
			}
		});
	}
}

function view_attach_document(sales_order_id, sales_order_no) {
	$('#view_attach_document_modal').modal('show');
	$('#ref_no').html(sales_order_no);
	$('#eid').val(sales_order_id);
	show_document_attach();
}

/*function load_attach_document(){
	var sales_order_id=$('#eid').val();
	
	$("#attachments-doc-datatable").dataTable({
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
		"aLengthMenu": [[ 10, 20, 50, 100, -1], [ 10, 20, 50, 100, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain + 'app/sales_order/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "load_attach_document"},
				{"name": "sales_order_id", "value": sales_order_id});
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}*/

function preview_update_user(id, no, preview_user) {
	$('#preview_user_update').modal('show');
	$('#ref_mod_no').html(no);
	$('#ref_mod_id').val(id);
	$('#preview_user').val(preview_user);
	load_user_update_log();
}

function load_user_update_log() {
	var sales_order_id = $('#ref_mod_id').val();

	$("#update_user_log_history").dataTable({
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
		"aLengthMenu": [[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain + 'app/sales_order/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "update_user_log_history" },
				{ "name": "sales_order_id", "value": sales_order_id });
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');
}

function user_update() {

	var form_data = {
		mode: "user_update",
		previous_user_id: $('#preview_user').val(),
		updated_user_id: $('#updated_user_id').val(),
		ref_id: $('#ref_mod_id').val(),
		remark: $("#user_update_remark").val()
	};

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: form_data,
		success: function (response) {
			$('#updated_user_id').select2("val", "1");
			$('#user_update_remark').val("");

			load_user_update_log();
			load_datatable();
			$('#preview_user_update').modal('hide');
			Unloading();
		}
	});
}

function open_batch_wise_qty() {
	load_batch_datatable();
	if ($("#product_id").val() === "") {
		toastr.warning("Select Product", "ERROR")
		$("#product_id").select2('focus')
		return false;
	}
	else if ($("#product_qty").val() === "") {
		toastr.warning("Enter Qty", "ERROR")
		$("#product_qty").focus();
		return false;
	}

	var qty = $("#product_qty").val();
	var product_id = $("#product_id").val();


	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "accessories_model_open", qty: qty, product_id: product_id },
		success: function (response) {

			var data = jQuery.parseJSON(response);

			$('#bs-batch_wise_stock-modal1').modal('show');

			$("#batch_data").html(data.html_data);
			product_load_pro();

			CKEDITOR.replace('acc_product_desc', {
				enterMode: CKEDITOR.ENTER_BR
			});

			//validate_qty(0);	
		}
	});
}

function add_accessories_data() {

	var inquiry_type = $('#inquiry_type').val();
	var product_id = $('#product_id').val();

	var eid = $('#eid').val();
	var branch_id = $('#branch_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "add_accessories_data", product_id: product_id },
		success: function (data) {
			//console.log(data);
		}
	});
}

function load_batch_datatable() {

	var product_id = $('#product_id').val();

	var edit_id = $("#edit_id").val();

	datatable = $("#batch_stock_table").dataTable({
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
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + crm_domain + 'app/sales_order/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "fetch_accessories_qty" },
				{ "name": "product_id", "value": product_id },
				{ "name": "edit_id", "value": edit_id });
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');
}
function open_accesorice_wise_product_list(id) {


	var cust_id = $('#cust_id').val();
	get_statecode(cust_id);
	//alert(cust_id);

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "open_accesorice_wise_product_list", product_id: id },
		success: function (response) {
			//alert(response);
			var data = jQuery.parseJSON(response);

			$('#bs-batch_wise_stock-modal2').modal('show');

			$("#batch_data1").html(data.html_data);
			product_load_pro_l();

			CKEDITOR.replace('acc_product_desc_l', {
				enterMode: CKEDITOR.ENTER_BR
			});

			//validate_qty(0);	
		}
	});
}


function product_load_pro_l() {

	var testData = [];

	//$("#product_id").html("");
	var mainurl = root_domain + crm_domain + 'app/product_load/index.php?mode=product_load';
	$.getJSON(mainurl, function (json) {
		var arr = new Array();
		var len = json[0].length;
		////console.log(len);

		for (var i = 0; i < len; i++) {
			testData.push({ id: json['0'][i], text: json['1'][i] });
			////alert(json['1'][i]);
		}
	});
	load_cat_product_so('acc_product_id_l', testData);
	// return testData;
}

function add_accessories_product_pop() {

	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}

	if ($("#acc_product_id").val() === "") {
		toastr.warning("Select Product Id", "ERROR");
		$("#acc_product_id").select2("focus");
		return false;
	}
	if ($("#acc_product_qty").val() === "") {
		toastr.warning("Enter Product Qty", "ERROR");
		$("#acc_product_qty").val("focus");
		return false;
	}

	/* var specification = new Array();
		var selected = $('.categojj').select2("data");
	for (var i = 0; i <= selected.length-1; i++) {
		specification.push(selected[i].text);
		} */

	var form_data = {
		mode: "add_accessories_product_pop",
		edit_id: $("#edit_id_accessories").val(),
		acc_product_id: $("#acc_product_id").val(),
		pid: $("#pid").val(),
		acc_product_qty: $("#acc_product_qty").val(),
		acce_rate: $("#acce_rate").val(),
		acc_amount: $("#acc_amount").val(),
		acc_product_desc: $("#acc_product_desc").val()
		//specification:specification
	};

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: form_data,
		success: function (response) {
			////console.log(response)
			$("#acc_product_id").select2("val", "");
			$("#acc_product_qty").val('');
			$("#acce_rate").val('');
			$("#acc_amount").val('');
			CKEDITOR.instances['acc_product_desc'].setData("");
			$("#edit_id_accessories").val('')
			$("#add_party_purchase").val("Add");
			Unloading();
			load_batch_datatable();
		}
	});
}


function edit_data_accessories_product_pop(id) {

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "preedit_accessories_product", id: id },
		success: function (response) {
			//////console.log(response);
			var data = jQuery.parseJSON(response);
			$("#acc_product_id").select2('data', { id: data.product_id, text: data.product_name });
			$("#acc_product_qty").val(data.qty);
			$("#acce_rate").val(data.acce_rate);
			$("#acc_amount").val(data.acc_amount);
			$("#edit_id_accessories").val(id);
			CKEDITOR.instances['acc_product_desc'].setData(data.product_desc);
			//$("#add_alternative_btn").val("Update");
			Unloading();
		}
	});
}

function delete_data_accessories_product_pop(id) {

	var r = confirm(" Are you sure want to delete ?");
	if (r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "delete_data_alternative_product_pop", eid: id },
			success: function (response) {
				var data = jQuery.parseJSON(response)
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_batch_datatable();
					Unloading();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}
}

function get_amount_pop_list() {
	var product_qty = parseFloat($("#acc_product_qty_l").val());
	var product_rate = parseFloat($("#acce_rate_l").val());
	if (product_qty && product_rate && product_qty != '0' && product_rate != '0') {
		var product_amount = parseFloat((product_qty) * (product_rate));
		$("#acc_amount_l").val(product_amount);
	}
	else {
		$("#acc_amount_l").val(0);
	}
}

function add_field_list() {


	if (!$("#acc_product_id_l").val()) {
		toastr.warning("Choose Product", "ERROR");
		$("#acc_product_id_l").select2('focus');
		return false;
	}
	else if (!$("#acc_product_qty_l").val()) {
		toastr.warning("Enter Quantityyyyyy", "ERROR");
		$("#acc_product_qty_l").focus();
		return false;
	}
	else if ($("#acc_product_qty_l").val() <= 0) {
		toastr.warning("Quantity must be greater than 0", "ERROR");
		$("#acc_product_qty_l").focus();
		return false;
	}
	else if (!$("#acce_rate_l").val()) {
		toastr.warning("Enter Rate", "ERROR");
		$("#acce_rate").focus();
		return false;
	}
	else if ($("#acce_rate_l").val() <= 0) {
		toastr.warning("Rate must be greater than 0", "ERROR");
		$("#acce_rate_l").focus();
		return false;
	}
	else if (!$("#acc_amount_l").val()) {
		toastr.warning("Enter Rate", "ERROR");
		$("#acc_amount_l").focus();
		return false;
	}


	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}



	var form_data = {
		mode: "add_field_list",
		product_id: $("#acc_product_id_l").val(),
		pid: $("#pid_l").val(),
		product_qty: $("#acc_product_qty_l").val(),
		product_rate: $("#acce_rate_l").val(),
		product_amount: $("#acc_amount_l").val(),
		cust_stateid: $("#cust_stateid").val(),
		gst_type: $('#gst_type').val(),
		product_desc: $("#acc_product_desc_l").val(),
		user_id: $("#user_id").val(),
		sales_order_id: $("#eid").val()

	};

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: form_data,
		success: function (response) {
			console.log(response);
			$("#acc_product_id_l").select2("val", "");
			$("#pid_l").val("");
			$("#acc_product_qty_l").val("");
			$("#acce_rate_l").val("");
			$("#acc_amount_l").val("");
			CKEDITOR.instances['acc_product_desc_l'].setData("");
			$('#bs-batch_wise_stock-modal2').modal('hide');
			Unloading();
			show_data();
			dataget();
			get_tax_details_table();
			get_invoice_total_tax();
		}
	});
}

function load_product_dtls_pop_list(product_id) {

	var product_attr = $('#product_id').find('option:selected').attr('data-type');
	var branch_id = $('#branch_id').val();
	var inquiry_type = $('#inquiry_type').val();
	var quotation_rate_fixed = $('#quotation_rate_fixed').val();
	var currency_id = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();

	/* if(branch_id==''){
		toastr.warning("Select branch", "ERROR");
		$('#product_id').select2("val",'');
		$("#branch_id").focus();
		return false;
	} */

	if (product_id) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "load_product_dtls", product_id: product_id, inquiry_type: inquiry_type },
			success: function (response) {
				////console.log(response);
				if (quotation_rate_fixed == '1') {
					$('#product_rate').attr('readonly', true);
				}
				var resp = jQuery.parseJSON(response);
				var rate = 0;
				var curr = '<?php echo $_SESSION["currency_id"]?>';
				////console.log(resp.product_sale_rate);
				CKEDITOR.instances['acc_product_desc_l'].setData(resp.product_desc);
				//CKEDITOR.instances['product_spec'].setData(resp.product_spec);
				if (currency_id != curr) {
					rate = parseFloat(resp.product_sale_rate) / parseFloat(currency_rate);
				} else {
					rate = resp.product_sale_rate;
				}

				$('#acce_rate_l').val(rate.toFixed(2));
				//$('#unitid').select2("val",resp.product_base_unit);
				$('#current_stock_pop_l').css('display', 'block');
				$('#current_stock_pop_l').html('Current Stock: ' + resp.current_stock);
				$('.unit_pop_l').css('display', 'block');
				$('#unit_pop_l').html('Unit: ' + resp.unit_name);
				Unloading();
			}
		});
	}
}

function get_hsn_pop_list(product_id) {
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "get_hsn_code", product_id: product_id },
		success: function (response) {
			if (response != '') {
				$('#hsncode_pop_l').text(response);
				$(".hsncode_pop_l").show();
			} else {
				toastr.warning("Please select valid HSN code product", "WARNING");
				$(".hsncode_pop_l").hide();

				$('#acc_product_id_l').select2("val", "");
				return false;
			}
		}
	});

}

function dataget(product_spec_id, product_spec_id_id) {



	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/inquiry/',
		data: { mode: "dataget", product_spec_id: product_spec_id },
		success: function (response) {
			console.log(response);

			var data = jQuery.parseJSON(response);

			$('#specification_id').html(data.res);

			$('#specification_id').select2("val", product_spec_id_id.split(','));

			Unloading();
		}
	});

}

function order_review(id) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "order_review", sales_order_id: id },
		success: function (response) {
			var data = jQuery.parseJSON(response);
			$('#oreder_review_modal').modal('show');
			$('#ref_sale_ord_no').html(data.sales_order_no);
			$('#sales_product').html(data.sales_product);
			Unloading();
		}
	});
}
function order_review_add() {
	var sales_ordertrn_id = $('#sales_product').val();
	if (sales_ordertrn_id == '') {
		toastr.warning("Choose Product First", "ERROR");
		$('#sales_product').select2('val', '');
		$('#sales_product').select2('focus');
		return false;
	}
	window.location = root_domain + crm_domain + 'order_review_form' + '/' + sales_ordertrn_id;
}
function order_review_print() {
	var sales_ordertrn_id = $('#sales_product').val();
	if (sales_ordertrn_id == '') {
		toastr.warning("Choose Product First", "ERROR");
		$('#sales_product').select2('val', '');
		$('#sales_product').select2('focus');
		return false;
	}
	window.location = root_domain + print_root_domain + 'order_review_print_libra' + '/' + sales_ordertrn_id;
}

function load_quotation_popup(id) {
	if (!$("#cust_id").val()) {
		toastr.warning("Choose Customer", "ERROR");
		$("#cust_id").select2('focus');
		return false;
	}

	if (id == 'yes') {
		$("#modal-quotation").modal("show");
		$('#quotation_link').show();
	}
	else {
		$('#quotation_link').hide();
	}
	get_quotation_details();
}
function get_quotation_details() {
	if (!$("#cust_id").val()) {
		toastr.warning("Choose Customer", "ERROR");
		$("#cust_id").select2('focus');
		return false;
	}

	var so_quotation_type = $('input[name="so_quotation_type"]:checked').val();
	var cust_id = $("#cust_id").val();
	$(".quotation_det").html('');
	Loading();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "get_quotation_details", cust_id: cust_id, so_quotation_type: so_quotation_type },
		success: function (response) {
			var arr = JSON.parse(response);
			//alert(arr);
			if (arr) {
				$(".quotation_det").html(arr);
			}
		}
	});
}

function quotation_dropdown_data() {
	/*if(!$("#cust_id").val()){		
		toastr.warning("Choose Customer", "ERROR");
		$("#cust_id").select2('focus');
		return false;
	}*/
	var so_quotation_type = $('input[name="so_quotation_type"]:checked').val();
	var cust_id = $("#cust_id").val();
	/*alert(cust_id);*/
	/*alert(cust_id);*/
	Loading();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "get_quotation_dropdown_data", cust_id: cust_id, so_quotation_type: so_quotation_type },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$('#mquotation_id').empty().append(obj);
			$("#mquotation_id").select2({
				width: '100%'
			});
		}
	});
}

function add_quotation() {

	var quotation = [];
	$(".quotation").each(function () {
		if ($(this).is(":checked")) {
			quotation.push($(this).val());
		}

	});

	if (quotation.length === 0) {
		toastr.warning("Please Select quotation and then submit", "ERROR")
		return false;
	}

	var currency_id = $("#currency_id").val();
	var currency_rate = $("#currency_rate").val();
	var delivery_type = $("#delivery_type").val();
	var branch_id = $("#branch_id").val();
	var inquiry_type = $("#inquiry_type").val();
	var user_id = $("#user_id").val();
	var sales_order_id = $("#sales_order_id").val();
	var with_out_stock_invoice = $("#with_out_stock_invoice").val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "add_quotation", quotation: quotation, cust_id: $('#cust_id').val(), cust_stateid: $('#cust_stateid').val(), currency_id: currency_id, currency_rate: currency_rate, delivery_type: delivery_type, branch_id: branch_id, inquiry_type: inquiry_type, user_id: user_id, sales_order_id: sales_order_id, with_out_stock_invoice: with_out_stock_invoice },
		success: function (response) {
			var arr = JSON.parse(response);
			if (arr.msg == '1') {
				$('#modal-quotation').modal('toggle');
				show_data();
			} else {
				toastr.warning("Something went wrong", "WARNING");
				return false;
			}
		}
	});
}

function load_company_data() {
	var so_quotation_type = $('input[name="so_quotation_type"]:checked').val();

	if (so_quotation_type == 1) {
		$('#modal-cust-company').modal('show');
		$(".quotation_detail").show();
	} else {
		$(".quotation_detail").hide();
	}
}

function add_customer_to_company() {
	var so_quotation_type = $('input[name="so_quotation_type"]:checked').val();
	var crm_cust_id = $("#crm_cust_id").val();
	/*alert(crm_cust_id);*/
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		async: false,
		data: { mode: "add_customer_to_company", so_quotation_type: so_quotation_type, crm_cust_id: crm_cust_id },
		success: function (resp) {
			var obj = jQuery.parseJSON(resp);
			$('#modal-cust-company').modal('hide');
			$('#cust_id').empty().append(obj.data);
			$('#cust_id').select2("val", obj.cust_id);
			$("#cust_id").select2({
				width: '100%'
			});

			load_consignee_detail(obj.cust_id);
			get_statecode(obj.cust_id);
			get_grossbalance(obj.cust_id);
			get_invoice_total_tax();
			get_gtotal();
			get_ledger_details(obj.cust_id);
			get_peyment_terms_details(obj.cust_id);
			quotation_dropdown_data();
		}
	});
}

function load_product_data() {
	var so_quotation_type = $('input[name="so_quotation_type"]:checked').val();
	var mquotation_id = $("#mquotation_id").val();
	quotation_wise_product_load();
}

function quotation_wise_product_load() {
	var testData = [];
	var inquiry_type = $("#inquiry_type").val();
	var quotaion_id = $("#mquotation_id").val();
	var product_category = '';
	var cat = '';
	if (comp_config.cat_wise_product_load == 1) {
		product_category = $("#product_category_id").val();
		cat = '&product_category=' + product_category;
	}
	//$("#product_id").html("");
	var mainurl = root_domain + crm_domain + 'app/product_load/index.php?mode=product_load&inquiry_type=' + inquiry_type + '&type=so_pro_type&search=sales_pro_search&quotaion_id=' + quotaion_id + cat;
	$.getJSON(mainurl, function (json) {
		var arr = new Array();
		var len = json[0].length;
		// console.log(len);

		for (var i = 0; i < len; i++) {
			testData.push({ id: json['0'][i], text: json['1'][i], quotation_trn_id: json['2'][i] });
			//alert(json['1'][i]);
		}
	});

	load_cat_product_so('product_id', testData);
	//return testData;
}

function load_cat_product_so(id, testData) {

	$('#' + id).select2({
		data: testData,
		placeholder: 'search',
		multiple: false,
		// query with pagination
		query: function (q) {
			var pageSize,
				results,
				that = this;
			pageSize = 20; // or whatever pagesize
			results = [];
			if (q.term && q.term !== '') {
				// HEADS UP; for the _.filter function i use underscore (actually lo-dash) here
				results = _.filter(that.data, function (e) {
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
}
///Maulik Start
function load_unit_product() {
	var product_id = $("#product_id").val();
	var rate_unit = $("#rate_unit_id").val();
	var edit_id = $("#edit_id").val();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_product_unit", product_id: product_id, rate_unit: rate_unit, edit_id: edit_id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#unit_wise").html(obj.unit_option);

		}
	});
}

function delivery_schedule() {
	var unitid = $('#unitid').val();
	var unit_wise = $('#unit_wise').val();
	if (unitid == unit_wise) {
		var qty = $("#product_qty").val();
		var unit_show = $("#unit_show").text();
	} else {
		var qty = $("#product_conv_qty").val();
		var unit_show = $("#convert_unit_show").text();
	}

	var trn_id = $("#edit_id").val();
	var product_name = $("#product_id").select2('data').text;
	$("#model_product_name").html(product_name + " --- " + qty + " " + unit_show);
	$("#m_trn_id").val(trn_id);
	$("#m_qty").val(qty);

	//alert();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "delivary_date_model_open", qty: qty, trn_id: trn_id },
		success: function (response) {
			$("#date_des").html(response);
			if (trn_id == '') {
				$("#m_addrow").hide();
			}
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

		}
	});
}

/*function load_parent_cat(){
	var parent_id = $("#parent_category_id").val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/sales_order/',
		data: { mode : "load_parent_cat",parent_id :parent_id },
		success: function(response)
		{
			$("#product_category_id").html(response);
		}
	});
}*/

function stock_allocate() {
	var validate_qty = $("#product_qty").val();
	var sales_order_trn_id = $("#edit_id").val();
	var product_id = $("#product_id").val();
	var branch_id = $("#branch_id").val();
	var base_unit = $("#unitid").val();
	var bunit_name = $("#unit_show").html();

	if ($('#delivery_type').val() == 'product_wise') {
		$('#bs-so_dispatch_date-modal').modal('hide');
	}

	$('#bs-stock_allocation-modal').modal('show');
	$("#sales_ordertrn_id_model").val(sales_order_trn_id);
	$("#validate_qty").val(validate_qty);
	$("#show_res_qty").html(validate_qty + ' ' + bunit_name);

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: {
			mode: "show_stock_new",
			sales_order_trn_id: sales_order_trn_id,
			product_id: product_id,
			branch_id: branch_id,
			base_unit: base_unit
		},
		success: function (data) {
			$("#sstock").html(data);
			$("#st_godown_id").select2({
				width: '100%'
			});
			$("#st_stock_id").select2({
				width: '100%'
			});
			show_reserve_temp_data();
		}
	});
}

function show_reserve_temp_data() {
	//Loading();

	var sales_ordertrn_id = $('#sales_ordertrn_id_model').val();
	var batch_wise_stock_manage = $('#batch_wise_stock_manage').val();
	var product_id = $("#product_id").val();

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_reserve_data", sales_ordertrn_id: sales_ordertrn_id, batch_wise_stock_manage: batch_wise_stock_manage, product_id: product_id },
		success: function (data) {
			//console.log(data);
			$('#reserve_productdata').html(data);
		}
	});
}

function load_godown_wise_stock() {
	var st_godown_id = $("#st_godown_id").val();
	var product_id = $("#product_id_model").val();
	var unit_id = $("#unit_id_model").val();
	var batch_id = $("#st_stock_id").val();

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: {
			mode: "godown_stock",
			st_godown_id: st_godown_id,
			unit_id: unit_id,
			product_id: product_id,
			batch_id: batch_id
		},
		success: function (response) {
			//alert(response);
			var current_stock = response.trim();
			$('#st_stock_total').val(current_stock);
			$('#st_stock_reserve').attr('max', current_stock);
		}
	});
}

function delete_data_stock(id) {
	var r = confirm(" Are you want to delete ?");

	if (r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/sales_order/',
			data: { mode: "delete_data_stock", eid: id },
			success: function (response) {
				//console.log(response)
				var data = jQuery.parseJSON(response)
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_reserve_temp_data();
					Unloading();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}

}

function add_reserve_temp() {
	var st_godown_id = $('#st_godown_id').val();
	var st_stock_id = $('#st_stock_id').val();
	var st_stock_total = $('#st_stock_total').val();
	var st_stock_reserve = $('#st_stock_reserve').val();
	var sales_ordertrn_id = $('#sales_ordertrn_id_model').val();
	var unit_id = $('#unit_id_model').val();
	var product_id = $('#product_id_model').val();

	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: {
			mode: "add_reserve_data_temp",
			st_godown_id: st_godown_id,
			st_stock_id: st_stock_id,
			st_stock_total: st_stock_total,
			st_stock_reserve: st_stock_reserve,
			sales_ordertrn_id: sales_ordertrn_id,
			unit_id: unit_id,
			product_id: product_id
		},
		success: function (response) {
			//console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#st_godown_id").select2("val", "");
			$("#st_stock_id").select2("val", "");
			$("#st_godown_id").val("");
			$("#st_stock_id").val("");

			$("#st_stock_total").val("");
			$("#st_stock_reserve").val("");
			$('#addrow').val('Add');

			show_reserve_temp_data();
		}
	});
}

function load_batch_no() {
	var godwn_id = $("#st_godown_id").val();
	var product_id = $("#product_id_model").val();
	var unit_id = $("#unit_id_model").val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_batch_no", godwn_id: godwn_id, product_id: product_id, unit_id: unit_id },
		success: function (responce) {

			$('#st_stock_id').html(responce);
			$("#st_stock_id").select2("val", "");
		}
	});
}
function getNum(val) {
	if (isNaN(val)) {
		return 0;
	}
	return val;
}

function get_terms_detail(id) {
	var tc_id = $("#ref_tc_id" + id).val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "get_terms_detail", tc_id: tc_id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#tc_details" + id).val(obj.tc_details);
		}
	});
}
///MAulik End

function show_product_search_modal() {
	Loading();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "get_product_filter_option" },
		success: function (response) {
			Unloading();
			$("#product_filter_row").empty().html(response);
			$("#product_filter_search_modal").modal('show');

			$(".dynamic_field").select2({
				width: "100%"
			});

		}
	});

}

function generate_product_name_for_search() {

	var i = 1;
	var c = ' ';
	var dynamic_field = $('#dynamic_field').val();

	for (i = 1; i <= dynamic_field; i++) {
		var name = $("#field_id" + i).find('option:selected').attr('data-pcode');
		if (name != '') {
			var seprator = '';
			if (i != 1) {
				seprator = '-';
			}
			c += seprator + name;
		}
	}

	$("#product_search").val(c);
}


function product_searching() {
	var search_text = $("#product_search").val();

	if (search_text == "") {
		toastr.warning("PLEASE SELECT FILTER FOR SEARCH PRODUCT", "WARNING");
		return false;
	} else {
		$("#product_filter_search_modal").modal('hide');
		$("#product_id").select2("search", search_text);
	}

}
function load_trans_add() {
	var tc_id = $("#transid").val();
	var edit_id = $("#trans_add_ed").val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_trans_add", tc_id: tc_id, edit_id: edit_id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#trans_add").html(obj.html);
		}
	});
}
function load_consingy_address() {
	var consignee_id = $("#consignee_id").val();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + crm_domain + 'app/sales_order/',
		data: { mode: "load_consingy_address", consignee_id: consignee_id },
		success: function (response) {

			//$("#quot_general_terms_condition_content").val(response);
			CKEDITOR.instances['ship_address'].setData(response);
		}
	});
}
function open_print(url) {

	var r = confirm("Are you print with header ?");
	if (r) {
		url = url + "/1";
		window.open(url, '_blank');
	} else {
		window.open(url, '_blank');
	}


}



function calculate_orange() {

	var qty = 0;
	var orange = 0;
	if ($("#product_qty").val() != '') {
		qty = $("#product_qty").val();
	}

	if ($("#orange").val() != '') {
		orange = $("#orange").val();
	}

	var orange_total = parseFloat(qty) * parseFloat(orange);

	$("#orange_total").val(orange_total);

}





function calculate_mfg() {

	var qty = 0;
	var mfg = 0;
	if ($("#product_qty").val() != '') {
		qty = $("#product_qty").val();
	}

	if ($("#mfg").val() != '') {
		mfg = $("#mfg").val();
	}

	var mfg_total = parseFloat(qty) * parseFloat(mfg);

	$("#mfg_total").val(mfg_total);

}





function calculate_trading() {

	var qty = 0;
	var trading = 0;
	if ($("#product_qty").val() != '') {
		qty = $("#product_qty").val();
	}

	if ($("#trading").val() != '') {
		trading = $("#trading").val();
	}

	var trading_total = parseFloat(qty) * parseFloat(trading);

	$("#trading_total").val(trading_total);

}




function calculate_repairing() {

	var qty = 0;
	var repairing = 0;
	if ($("#product_qty").val() != '') {
		qty = $("#product_qty").val();
	}

	if ($("#repairing").val() != '') {
		repairing = $("#repairing").val();
	}

	var repairing_total = parseFloat(qty) * parseFloat(repairing);

	$("#repairing_total").val(repairing_total);

}




function calculate_other() {

	var qty = 0;
	var other = 0;
	if ($("#product_qty").val() != '') {
		qty = $("#product_qty").val();
	}

	if ($("#other").val() != '') {
		other = $("#other").val();
	}

	var other_total = parseFloat(qty) * parseFloat(other);

	$("#other_total").val(other_total);

}


function calculate_special_total() {
	var qty = 0;
	var orange = 0;
	var mfg = 0;
	var trading = 0;
	var repairing = 0;
	var other = 0;

	if ($("#product_qty").val() != '') {
		qty = $("#product_qty").val();
	}

	if ($("#orange").val() != '') {
		orange = $("#orange").val();
	}

	if ($("#mfg").val() != '') {
		mfg = $("#mfg").val();
	}

	if ($("#trading").val() != '') {
		trading = $("#trading").val();
	}

	if ($("#repairing").val() != '') {
		repairing = $("#repairing").val();
	}

	if ($("#other").val() != '') {
		other = $("#other").val();
	}

	var orange_total = parseFloat(qty) * parseFloat(orange);
	var mfg_total = parseFloat(qty) * parseFloat(mfg);
	var trading_total = parseFloat(qty) * parseFloat(trading);
	var repairing_total = parseFloat(qty) * parseFloat(repairing);
	var other_total = parseFloat(qty) * parseFloat(other);

	$("#orange_total").val(orange_total);
	$("#mfg_total").val(mfg_total);
	$("#trading_total").val(trading_total);
	$("#repairing_total").val(repairing_total);
	$("#other_total").val(other_total);
}

function exportCsv() {
	var data = $('input[name=report]:Checked').val();
	var date = $('#rep_date').val();
	var type = $('#type_id').val();
	var jobwork_type = $('input[name=jobwork_type]:Checked').val();
	var branch_id = $('#branch_id').val();
	var user_id = $('#user_id').val();
	var so_status = $('#so_status').val();

	var url = root_domain + 'generate_export?mode=sales_order_list&date=' + encodeURIComponent(date) + "&data=" + encodeURIComponent(data) + "&type=" + encodeURIComponent(type) + "&jobwork_type=" + encodeURIComponent(jobwork_type) + "&branch_id=" + encodeURIComponent(branch_id) + "&user_id=" + encodeURIComponent(user_id) + "&so_status=" + encodeURIComponent(so_status);
	window.location.href = url;
}