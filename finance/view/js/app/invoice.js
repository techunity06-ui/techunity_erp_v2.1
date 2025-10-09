//var datatable;
$(document).ready(function () {
	load_datatable();
	show_data();
	add_product_batch_wise();

	product_load();
	//open checkbox division edit time
	get_symbol();
	tc_format_view();
	var mode = $('#mode').val();

	if (mode == 'Edit') {
		var cust_id = $('#cust_id').val();
		var invoice_id = $('#eid').val();
		var bill_adjustment = $('#bill_adjustment').val();
		//checkbox changes
		dc_change();
		po_change();
		currency_change();

		//customer effects

		get_statecode(cust_id);
		get_grossbalance(cust_id);
		get_ledger_details(cust_id);
		get_all_bill_sundry(invoice_id);
		get_gtotal();
		enable_hypothication();
		load_consignee_new();
		//Popup Details
		get_tax_details_table();
		get_invoice_total_tax();

		if (bill_adjustment == 1) {
			$('.adjust_advance_link').show();
		}

	}

	var viewmode = $('#viewmode').val();
	if (viewmode == 'invoiceso') {
		var cust_id = $('#cust_id').val();

		currency_change();
		get_statecode(cust_id);
		get_grossbalance(cust_id);
		get_ledger_details(cust_id);
		get_gtotal();
	}

	if (mode == 'Add' && viewmode == '') {
		currency_rate_c();
	}


	$('#con_without_stock').click(function () {
		$("#addrow").prop("disabled", false);
		$("#stock_alert").hide();
	});


	jQuery('.numbersOnly').keyup(function () {
		this.value = this.value.replace(/[^0-9\.]/g, '');
		// if((this.value).length > 10){
		// 	$(this).val($(this).val().substr(0, 10));
		// }

	});


	$("#invoice_add").validate({
		rules: {

			invoicetype_id: {
				required: true
			},
			invoice_date: {
				required: true
			},
			sales_ledger_id: {
				required: true
			},
			branch_id: {
				required: true
			},
			cust_id: {
				required: true
			},

		},
		messages: {
			invoicetype_id: {
				required: "Select Type"
			},
			invoice_date: {
				required: "Enter date"
			},
			sales_ledger_id: {
				required: "Select Sales Ledger"
			},
			branch_id: {
				required: "Select Branch id"
			},
			cust_id: {
				required: "Select Customer"
			},


		}
	});


});

function showledger() {
	branch_id = $('#branch_id').val();
	if (!branch_id) {
		toastr.warning("Choose Branch!!!", "ERROR");
		$('#branch_id').select2('focus');
		return false;
	}
	$.fn.modal.Constructor.prototype.enforceFocus = function () { };
	$('#modal-add-ledger').modal('show');
	get_opening_balance('0');
	$("#ledger_add_type").val('invoice');
	$("#ledger_name").focus();
}

function showproduct() {
	product_type_sel = $('#product_type_sel').val();
	if (!product_type_sel) {
		toastr.warning("Choose Product type!!!", "ERROR");
		$('#product_type_sel').select2('focus');
		return false;
	}
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-product').modal('show');

	$('#product_type').select2("val", $("#product_type_sel").val());
	$("#product_type").trigger('change');
	get_opening_balance('0');
	$("#product_add_type").val('invoice');
	//$("#ledger_name").focus();
}

function add_hsn_invoice() {
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-hsn').modal('show');
	$("#hsn_add_type").val('product_invoice');
	$("#hsn_name").focus();
}


function get_symbol() {
	$(".sp_cr").remove();
	$(".currency_icon").html('');

	var symbl = $("#currency_id").find(':selected').attr("data-currency-symbol");
	/*var rate = $("#currency_id").find(':selected').attr("data-currency-rate");*/
	var textt = " (" + symbl + ")";
	$(".currency_icon").each(function () {
		$(this).append(textt);
	});
	/*$('#currency_rate').val(rate);*/
}

function currency_rate_c() {
	var rate = $("#currency_id").find(':selected').attr("data-currency-rate");
	$('#currency_rate').val(rate);
}


function invoice_submit() {
	$("#save_print").val(1);
	$("#invoice_add").submit();
}
$("#invoice_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	var invoiceDate = $('#invoice_date').val();
	var dueDate = $('#invoice_due_date').val();
	var invoice_date = new Date(invoiceDate.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));
	var due_date = new Date(dueDate.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));

	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}

	if (!$("#invoice_add").valid()) {
		return false;
	}
	else if ($("#sales_ledger_id").val() == 0) {
		toastr.warning("Select Sales Ledger First", "ERROR")
		return false;
	}
	else if (!$("#branch_id").valid()) {
		return false;
	}
	else if ($('#same_as').is(':checked') == false && $("#consignee_id").val() == "") {
		toastr.warning("SELECT CONSIGNEE OR SAME AS CONSIGNEE", "ERROR")
		return false;
	}
	else if (parseInt($('#total').val()) <= 0) {
		toastr.warning("AT LEAST ONE PRODUCT REQUIRE", "ERROR")
		return false;
	} else if (due_date < invoice_date) {
		toastr.warning("Invoice date can not be greater then Due date", "ERROR")
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
	if ($("#is_sales_order").val() == 'yes') {
		var transaction_type = $('#transaction_type').val();
		var sales_order = [];
		$(".sales_order").each(function () {
			if ($(this).is(":checked")) {
				sales_order.push($(this).val());
			}

		});
	}


	//Get All Product Stock count
	var trn_pro_stk = (document.getElementsByName('trn_pro_stk[]'));
	var cnt_pro_stk = (document.getElementsByName('cnt_pro_stk[]'));
	var cnt = trn_pro_stk.length;
	for (var i = 0; i < cnt; i++) {
		var trn_stk = parseFloat(trn_pro_stk[i].value);
		var pro_stk = parseFloat(cnt_pro_stk[i].value);
		if (trn_stk > pro_stk) {
			//toastr.warning("Product Out of Stock !!!", "ERROR");
			//return false;
		}
	}

	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");

	var form_data = new FormData(this);
	form_data.append('sales_order', JSON.stringify(sales_order));
	form_data.append('transaction_type', transaction_type);
	update_total();
	$.ajax({
		cache: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData: false,
		success: function (response) {
			//alert(response);
			console.log(response);
			var arr = jQuery.parseJSON(response);
			if (arr.msg == '1') {
				Unloading();
				toastr.success("BILL ADDED SUCCESSFULLY", "SUCCESS");
				if ($("#save_print").val() == '1') {
					window.location = root_domain + print_root_domain + 'invoicereceipt/' + arr.eid;
				}
				else {
					window.location = root_domain + finance_root_domain + 'invoice_list';
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
				toastr.success("BILL UPDATED SUCCESSFULLY", "SUCCESS");

				Unloading();
				if ($("#save_print").val() == '1') {
					window.location = root_domain + print_root_domain + 'invoicereceipt/' + arr.eid;
				}
				else {
					window.location = root_domain + finance_root_domain + 'invoice_list';
				}
			}
			$('#invoice_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});

function delete_invoice(id) {
	var r = confirm(" Are you want to delete ?");

	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "delete", eid: id },
			success: function (response) {
				//console.log(response);
				if (response.trim() == "1") {
					toastr.success("INVOICE DELETE SUCCESSFULLY", "SUCCESS");
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

function demo() {
	var paymentterms = $('#payment_terms').val();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/invoice/',
		data: { mode: "reminder", paymentterms: paymentterms },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$('#payment_reminder').val(obj.payment_days);
		}
	});
}
function add_freight() {
	get_gtotal($('#formulaid').val());
}
function cal_discount() {
	get_gtotal($('#formulaid').val());
}

function get_discount(type) {
	//document.getElementById('bill_value').value="";
	var qty = $('#product_qty').val();
	var taxtype = $("#taxtype").text();
	if (taxtype == 'including') {
		var rate = $("#pro_amt").text();
	} else {
		var rate = $('#product_rate').val();
	}
	var disc = 0;
	if (qty != "" && rate != "") {
		//alert('hi');
		if (type == "amt") {
			if ($('#product_discount').val() != '') {
				disc = Number(100 * parseFloat($('#product_discount').val()) / (qty * rate));
				var disc1 = Number(disc.toFixed(2));
				$('#discount_per').val(disc1);
			}
			else {
				$('#discount_per').val('');
			}
		}
		else if (type == "per") {
			if ($('#discount_per').val() != '') {
				disc = Number(((qty * rate) * parseFloat($('#discount_per').val())) / 100);
				var disc1 = Number(disc.toFixed(2));
				$('#product_discount').val(disc1);
			}
			else {
				$('#product_discount').val('');
			}
		}
		get_amount();
	}
	else {
		$('#product_discount').val('0');
		$('#discount_per').val('0');
	}


}
function load_ven_grn(vender_id, id) {


	if (vender_id) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "load_ven_grn", vender_id: vender_id, id: id },
			success: function (response) {
				//console.log(response);
				var resp = JSON.parse(response);
				// $('#salesorder_div').show();
				// $('#sales_order_id').html(resp.pro_html);
				// $('#sales_order_id').html(resp.pro_html);
				// $('#sales_order_id').select2('val',id);
				$('#is_sales_order').select2('val', 'yes');
				$('#sales_order_link').css('display', 'block');
				$('#transaction_type').val('2');
				get_sales_order_details(2);
				$('#salesorderid').val(id);
				$('#payment_terms').select2('val', resp.payment_terms);
				if (resp.enable_consignee == 0) {
					$("#enable_consignee").prop('checked', false);
					$('#consignee_id_div').css('display', 'block');
				} else {
					$("#enable_consignee").prop('checked', true);
					$('#consignee_id_div').css('display', 'none');
				}
				load_consignee_new();
				$('#consignee_id').val(resp.consignee_id);
				$('#enable_transport').val(resp.enable_transport);
				$(".sales_order").each(function () {
					if ($(this).val() == id) {
						$("#" + id).prop('checked', true);
					} else {
						$(".sales_order").prop('checked', false);
					}
				});
				add_sales();
				Unloading();
			}

		});
	}
}

function load_cust_so(vender_id, id) {
	if (vender_id) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "load_ven_grn", vender_id: vender_id, id: id },
			success: function (response) {
				//console.log(response);
				var resp = JSON.parse(response);
				$('#salesorder_div').show();
				$('#sales_order_id').html(resp.pro_html);
				$('#sales_order_id').select2('val', id);
				$('#salesorderid').val(id);
				if (resp.enable_consignee == 0) {
					$("#enable_consignee").prop('checked', false);
					$('#consignee_id_div').css('display', 'block');
				} else {
					$("#enable_consignee").prop('checked', true);
					$('#consignee_id_div').css('display', 'none');
				}
				load_consignee_new();
				$('#consignee_id').val(resp.consignee_id);
				$('#enable_transport').val(resp.enable_transport);
				Unloading();
			}

		});
	}
}

function is_product_stock_count() {
	var product_id = $('#product_id').val();
	//alert(product_id);
	if (product_id == '') {
		toastr.warning("Please Select product First", "ERROR");
		$('#product_id').select2('focus');
		$('#product_qty').val('');
		return false;
	}
	var isstockcnt = $("#product_stock_count_check").html();
	//alert(isstockcnt);
	var trans_type = $("#trans_type").val();
	var trans_stock = $('#trans_stock').val();
	var p_qty = $("#product_qty").val();

	if ((trans_type == 1 || trans_type == 2) && isstockcnt == 'yes') {
		if ((Number(p_qty) > Number(trans_stock)) && ($('#isstockngative').val() == 0)) {
			toastr.warning("Can not insert more quantity then available", "ERROR");
			$("#product_qty").val(trans_stock);
			return false;
		}
	}

	if ((parseFloat($("#product_qty").val()) > parseFloat($('#product_stock').val()))) {

		if (($('#isstockngative').val() == 1)) {
			$('#stock_alert').show();
			$('#addrow').prop('disabled', true);
		}
		else {
			$('#stock_alert').hide();
			$('#addrow').prop('disabled', true);
			toastr.warning("Can not insert more quantity then available", "ERROR");
		}

	}
	else {
		$('#stock_alert').hide();
		$('#addrow').prop('disabled', false);
	}

}

function get_amount() {
	var ratcalfiled = $("#pro_cal_type").val();
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

		$("#total").val(parseFloat(a));

		if ($("#product_discount").val() != "")//discount calculation
		{
			var discount = parseFloat($("#product_discount").val());
			a = a - discount;
		}

		$("#product_amount").val(parseFloat(a));
		var bill_value = $('#product_amount').val();
	}
	else {
		$("#product_amount").val(0);
	}
	get_gtotal();
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

	}
	$("#total").val(parseFloat(total));

	var gst_arr = document.getElementsByClassName('gst');

	for (var k = 0; k < gst_arr.length; k++) {

		var k1 = gst_arr[k].value;
		total = parseFloat(total) + parseFloat(k1);
		//alert(total);
	}
	//alert(total);
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
	//alert(currency_id);
	if (currency_id == $("#currency_id").val()) {
		round_of = Math.round(g_total).toFixed(2);
		round = round_of - g_total;
		$("#round_of").val(round.toFixed(2));
	} else {
		round_of = g_total;
		round = 0;
		$("#round_of").val(round.toFixed(2));
	}
	$("#g_total").val(round_of);

	if ($("#is_power_drive").val() == '1') {
		round = 0;
		$("#round_of").val(round.toFixed(2));
		$("#g_total").val(g_total);
	}

	
	$("#paid_amount").val(g_total.toFixed(2));
	update_total();

}
function load_productdetail(val, i) {
	//alert(val);
	get_hsn(val);
	/*var sales_order_id = $("#sales_order_id").val();
	
	var so_trn_id = $("#so_trn_id").val();*/
	if (val != 0) {
		$('#addproduct').hide();
	}
	else {
		$('#addproduct').show();
	}
	/*if(sales_order_id){
		$('#addproduct').hide();
		$.ajax({
			type: "POST",
			url: root_domain+ finance_root_domain+'app/invoice/',
			data: { mode : "loadsales_productdata",product_id :val, sales_order_id:sales_order_id,so_trn_id:so_trn_id },
			success: function(response)
			{
				//console.log(response);
				
				var obj =jQuery.parseJSON(response)
				$('#product_des').val(obj.description);				
				$('#product_hsn_code').val(obj.product_hsn_code);
				/* var qty=(obj.product_qty)-(obj.qty);

				alert(obj.qty); */
	/*$('#product_qty').val(obj.rsock);
	//$('#sqr_ft').val(obj.sqr_ft);
	$('#product_rate').val(obj.product_rate);	
	$('#unit_id').select2("val",obj.unit_id);
	$('#product_discount').val(obj.product_discount);
	$('#discount_per').val(obj.discount_per);
	CKEDITOR.instances['product_des'].setData(obj.product_desc);
	CKEDITOR.instances['product_spec'].setData(obj.product_spec);
	//$('#product_amount').val(obj.product_amount);	
	$('#formulaid').val(obj.formulaid);

	load_product_unit(val,obj.product_base_unit);
	get_amount();	
	//Load Stock Function 
	load_stock_qty(val,0);
}
});	
}
else{*/
	var cust_id = $('#cust_id').val();
	if (cust_id == '') {
		toastr.warning("Please Select Customer First", "ERROR");
		$('#cust_id').select2('focus');
		$('#product_id').select2("val", "");
		return false;
	}
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "load_productdata", eid: val, cust_id: cust_id },
		success: function (response) {

			var obj = jQuery.parseJSON(response)
			//$('#product_rate').val(obj.product_mst_rate);	
			$('#product_hsn_code').val(obj.product_hsn);
			$('#taxtype').text(obj.product_gst);
			$('.taxtype').show();
			$('#product_qty').val('0');
			$('#product_des').val(obj.product_desc);
			$('#product_rate').val(obj.product_sale_rate);

			if (obj.disc_per != "") {
				$("#discount_per").val(obj.disc_per);
			} else {
				$("#discount_per").val(0);
			}

			$('#product_amount').val('0');
			$('#unitid').val(obj.product_base_unit);
			$('#isbatchwise').val(obj.batch_wise_stock_manage);
			$('#unit_show').html(obj.unit_name);
			add_product_batch_wise();

			CKEDITOR.instances['product_des'].setData(obj.product_desc);
			CKEDITOR.instances['product_spec'].setData(obj.product_spec);
			if (obj.product_gst == 'including') {
				var prouduct_amt = (parseInt(obj.product_sale_rate) * parseInt(100) / (parseInt(100) + parseInt(obj.tax_gst))).toFixed(2);
				var tax_rate = parseFloat(obj.product_sale_rate) - parseFloat(prouduct_amt);
				$('#pro_amt').text(prouduct_amt);
				$('.pro_amt').show();
				$('#taxrate').text(tax_rate);
				$('.taxrate').show();
				$("#taxper").val(obj.tax_gst);
			}
			//$('#unit_id').select2("val",obj.product_base_unit);
			// Load last customer rate function	
			$('#product_stock_count_check').html(obj.product_stock_count);
			//Load Stock Function 
			load_product_unit(val, obj.product_base_unit);
			load_stock_qty(val, 0);
		}
	});
	//}

}

//Maulik Start
function load_product_unit(product_id, edit_unit) {

	if (product_id) {

	} else {
		var product_id = $("#product_id").val();
	}
	if (edit_unit) {

	} else {
		var edit_unit = $("#rate_unit_id").val();
	}
	/*alert(product_id);
	alert(edit_unit);*/
	//alert(product_id);
	if (product_id)//tax calculation on total 
	{

		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + finance_root_domain + 'app/invoice/',
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
					if (obj.product_base_unit === edit_unit) {
						if (obj.product_base_unit != obj.product_conv_unit) {
							$("#base_unit_block").show();
							$("#convert_unit_block").show();
							$("#product_conv_qty").attr("readonly", "readonly");
							$("#product_qty").removeAttr("readonly", "readonly");
						} else {
							$("#convert_unit_block").hide();
						}
						$("#pro_cal_type").val("product_qty_hide");
					} else {
						if (obj.product_base_unit != obj.product_conv_unit) {
							$("#base_unit_block").show();
							$("#product_qty").attr("readonly", "readonly");
							$("#product_conv_qty").removeAttr("readonly", "readonly");
							$("#convert_unit_block").show();
						} else {
							$("#base_unit_block").hide();
						}
						$("#pro_cal_type").val("product_conv_qty_hide");
					}
				} else {
					$("#base_unit_block").show();
					$("#product_qty").removeAttr("readonly", "readonly");
					$("#product_conv_qty").removeAttr("readonly", "readonly");
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

function product_convert_qty(type) {
	// console.log(type)
	if (type == 2) {
		var conv_qty_hide = $("#product_qty").val();
		var s = parseFloat(conv_qty_hide);
		results = s.toFixed(5);

		var num = $("#product_qty_hide").val();
		var d = parseFloat(num);
		resultb = d.toFixed(5);

		if (resultb === results) {
			get_amount();
			return false;
		}
		var product_conv_qty_hide = $("#product_conv_qty_hide").val();
	} else {
		var base_qty_hide = $("#product_conv_qty").val();
		var d = parseFloat(base_qty_hide);
		resultb = d.toFixed(5);

		var base_qty_hidess = $("#product_conv_qty_hide").val();
		var s = parseFloat(base_qty_hidess);
		results = s.toFixed(5);

		if (resultb === results) {
			get_amount();
			return false;
		}
		var conv_qty_hide = $("#product_qty").val();
	}
	// console.log(base_qty_hide);
	// console.log(conv_qty_hide);
	var base_qty = $("#product_qty").val();
	var conv_qty = $("#product_conv_qty").val();
	var product_id = $("#product_id").val();

	if (product_id) {
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "convert_qty", type: type, base_qty: base_qty_hide, conv_qty: conv_qty_hide, product_id: product_id },
			success: function (response) {
				var arr = jQuery.parseJSON(response);
				if (type === 1) {
					$("#product_conv_qty_hide").val(conv_qty);
				} else if (type === 2) {
					$("#product_qty_hide").val(base_qty);
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

function load_stock_qty(product_id, old_qty) {
	//alert(old_qty);
	Loading(true);
	var unit_id = $("#unitid").val();
	//alert(old_qty);
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "load_stock_qty", product_id: product_id, unit_id: unit_id },
		success: function (data) {
			//console.log(data);
			$("#product_qty").attr("placeholder", data);
			$('#product_stock').val(parseFloat(old_qty) + parseFloat(data));
			$('.product_stock_label').show();
			$('#product_stock_label').html(parseFloat(old_qty) + parseFloat(data));
			//$("#product_qty").attr("max",parseFloat(old_qty)+parseFloat(data));
			Unloading();
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
		"sAjaxSource": root_domain + finance_root_domain + 'app/invoice/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "fetch_batch_qty" },
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

function delete_batch_stock_entry(batchstockid) {

	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "delete_batch_entry", batchstockid: batchstockid },
		success: function (response) {
			var data = jQuery.parseJSON(response);
			var response1 = data.res;
			if (response1.trim() == "1") {
				toastr.success("DATA DELETED SUCCESSFULLY", "SUCCESS");
				load_batch_datatable();
			}
			else if (response1.trim() == "0") {
				toastr.warning("SOMETHING WENT WRONG", "WARNING");
				return false;
			}
			validate_qty(0);
		}
	});
}

function validate_qty(qtyforbatch1) {

	var product_qty = $("#product_qty").val();
	var product_id = $("#product_id").val();
	var edit_id = $("#edit_id").val();
	var qtyforbatch = qtyforbatch1;

	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: {
			mode: "validate_qty", product_qty: product_qty, product_id: product_id,
			qtyforbatch: qtyforbatch, edit_id: edit_id
		},
		success: function (response) {
			var data = jQuery.parseJSON(response);
			var response1 = data.res;

			if (response1.trim() == "0") {
				$("#qtyforbatch").val('0')
				toastr.warning("Batch Quantity can not greater Product quantity", "WARNING");
				$(".addbutton").hide();
				return false;
			} else if (response1.trim() == "1") {
				$(".addbutton").show();
			} else {
				$(".addbutton").hide();
			}
		}
	});
}

function add_product_batch_wise() {
	var isbatchwise = $("#isbatchwise").val();
	if (isbatchwise === "" || isbatchwise === "0") {
		$(".product_add_batch_wise").hide();
		$(".product_add_direct").show();
	} else {
		$(".product_add_batch_wise").show();
		$(".product_add_direct").hide();
	}
}

function add_batch_qty() {

	if ($("#batch_id").val() === "") {
		toastr.warning("Select Batch number", "ERROR")
		$("#batch_id").select2('focus')
		return false;
	}
	else if ($("#qtyforbatch").val() === "") {
		toastr.warning("Enter Qty", "ERROR")
		$("#qtyforbatch").focus();
		return false;
	}

	var stock_id = $("#batch_id").val();
	var qty = $("#qtyforbatch").val();
	var product_id = $("#product_id").val();
	var edit_id = $("#edit_id").val();
	var unit_id = $("#unitid").val();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: {
			mode: "add_batch_qty", qty: qty, product_id: product_id, stock_id: stock_id,
			edit_id: edit_id, unit_id: unit_id
		},
		success: function (response) {
			var data = jQuery.parseJSON(response);
			var response1 = data.res;
			if (response1.trim() == "1") {

				toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
				$("#batch_id").select2("val", "");
				$("#qtyforbatch").val("");
				$("#batch_stock").val("");
				load_batch_datatable();
				validate_qty(0);

			} else if (response1.trim() == "-1") {
				toastr.warning("ALREADY EXISTS", "WARNING");
				return false;
			}
			else if (response1.trim() == "0") {
				toastr.warning("SOMETHING WENT WRONG", "WARNING");
				return false;
			}


		}
	});
}

function get_batch_qty(id) {
	/*var stock = $("#batch_id").find(':selected').attr("data-stock");
	$("#batch_stock").val(stock);*/

	var batch_no = $("#batch_id").val();
	var unit_id = $("#unitid").val();
	var product_id = $("#product_id").val();
	var st_godown_id = $("#godown_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: {
			mode: "get_batch_qty",
			batch_no: batch_no,
			st_godown_id: st_godown_id,
			unit_id: unit_id,
			product_id: product_id
		},
		success: function (response) {
			var stock = response.trim();
			$("#batch_stock").val(response);
			Unloading();
			validate_qty(0);
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
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "batch_stock_model_open", qty: qty, product_id: product_id },
		success: function (response) {
			var data = jQuery.parseJSON(response);
			$('#bs-batch_wise_stock-modal').modal('show');
			$("#batch_data").html(data.html_data);
			$(".batch_select2").select2({
				width: '100%',
				//minimumInputLength: 3
			});
			validate_qty(0);
		}
	});
}

function add_field() {

	if (!$("#product_id").val() && $("#product_id").is(":visible")) {
		toastr.warning("Select Product Name", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if (!$("#product_qty").val() || parseFloat($("#product_qty").val()) == '0') {
		toastr.warning("Enter Qty", "ERROR");
		return false;
	}
	else if (!$("#product_rate").val() || parseFloat($("#product_rate").val()) == '0') {
		toastr.warning("Enter Rate", "ERROR");
		return false;
	}
	else if ((parseFloat($("#product_qty").val()) > parseFloat($('#product_stock').val())) && ($('#isstockngative').val() == 0)) {
		toastr.warning("PRODUCT OUT OF STOCK", "ERROR");
		$("#product_qty").focus();
		return false;
	}

	if ($('#currency_enable').is(':checked')) {
		var currency_enable = 1;
	}
	else {
		var currency_enable = 0;
	}
	//alert($('#currency_enable').val());
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: {
			mode: "fieldadd",
			edit_id: $("#edit_id").val(),
			product_id: $("#product_id").val(),
			product_hsn_code: $("#hsncode").text(),
			product_qty: $("#product_qty").val(),
			product_conv_qty: $("#product_conv_qty_hide").val(),
			product_rate: $("#product_rate").val(),
			unit_id: $("#unitid").val(),
			conv_unitid: $("#conv_unitid").val(),
			rate_unitid: $("#rate_unit_id").val(),
			product_discount: $("#product_discount").val(),
			discount_per: $("#discount_per").val(),
			product_amount: $("#product_amount").val(),
			invoice_id: $("#eid").val(),
			cust_stateid: $("#cust_stateid").val(),
			cust_id: $("#cust_id").val(),
			branch_id: $("#branch_id").val(),
			"trans_type": $('#trans_type').val(),
			"trans_id": $('#trans_id').val(),
			"sales_type": $('#sales_type').val(),
			"trans_stock": $('#trans_stock').val(),
			currency_enable: currency_enable,
			currency_rate: $('#currency_rate').val(),
			currency_id: $('#currency_id').val(),
			orange: $('#orange').val(),
			mfg: $('#mfg').val(),
			trading: $('#trading').val(),
			repairing: $('#repairing').val(),
			other: $('#other').val(),
			orange_total: $('#orange_total').val(),
			mfg_total: $('#mfg_total').val(),
			trading_total: $('#trading_total').val(),
			repairing_total: $('#repairing_total').val(),
			other_total: $('#other_total').val(),
			product_des: CKEDITOR.instances['product_des'].getData(),
			product_spec: CKEDITOR.instances['product_spec'].getData()
		},
		success: function (response) {
			console.log(response);
			$("#isbatchwise").val('')
			add_product_batch_wise();
			$("#product_id").select2("val", "");
			$("#parent_cat_id").select2("val", "");
			$("#cat_id").select2("val", "");
			$("#product_id").select2('focus');
			$("#product_des").val("");
			$("#product_hsn_code").val("");
			$("#product_discount").val("");
			$("#discount_per").val("");
			$("#taxable_value").val("");
			$("#product_qty").val("");
			$("#product_conv_qty").val("");
			$("#product_qty_hide").val("");
			$("#product_conv_qty_hide").val("");
			$("#conv_unitid").val("");
			$('#product_stock').val("");
			$("#unitid").val("");
			$("#unit_show").html("");
			$("#convert_unit_show").html("");
			$("#product_rate").val('');
			$("#product_disc").val('');
			$("#product_amount").val('');
			$("#edit_id").val('');
			$("#start_serial1").val('');
			$("#end_serial1").val('');
			$("#start_serial2").val('');
			$("#end_serial2").val('');
			$("#start_serial3").val('');
			$("#end_serial3").val('');
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
			$('#addproduct').show();
			$('#addrow').val('Add');
			$(".hsncode").hide();
			$(".product_stock_label").hide();
			$(".taxtype").hide();
			$(".pro_amt").hide();
			$(".taxrate").hide();
			Unloading();
			show_data();
			get_tax_details_table();
			get_invoice_total_tax();
			update_total();
			$("#product_id").prop('disabled', false);
			$("#product_type_sel").prop('disabled', false);
			$('#bs-batch_wise_stock-modal').modal('hide');
			$('#product_id').show();
			$('#product_name_hid').hide();
			$('#product_stock_count_check').html();
			CKEDITOR.instances['product_des'].setData('');
			CKEDITOR.instances['product_spec'].setData('');
			product_load();
			get_gtotal();
		}
	});

}
function load_paymentmode(val) {
	$.ajax({
		type: "POST",
		url: root_domain + 'app/invoice/',
		data: { mode: "paymentmode", paymentmodeid: val },
		success: function (response) {
			//console.log(response);
			$('#product_list').append(response);
		}
	});
}


function field_remove(id) {
	$("#fieldtr" + id).html('');
	var t = get_amount();
}

function reload_data() {
	//datatable.fnReloadAjax();
	load_datatable();
}
function load_datatable() {
	var data = $('input[name=report]:Checked').val();
	var date = $('#rep_date').val();
	var type = $('#type_id').val();
	var branch_id = $('#branch_id').val();

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
		"sAjaxSource": root_domain + finance_root_domain + 'app/invoice/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "fetch" }, { "name": "report", "value": data }, { "name": "type_id", "value": type }, { "name": "date", "value": date }, { "name": "branch_id", "value": branch_id });
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
			var iPageMarket = 0;
			var iPageMarkets = 0;
			for (var i = 0; i < aaData.length; i++) {
				iPageMarket += aaData[i][4] * 1;
				iPageMarkets += aaData[i][5] * 1;

			}

			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = parseFloat(iPageMarket).toFixed(2);
			$('#invoiceamount').html('Rs. ' + parseFloat(iPageMarket).toFixed(2));
			$('#invoicetaxamount').html('Rs. ' + parseFloat(iPageMarkets).toFixed(2));
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');
}
// function load_invoiceno(id)
// {
// 	$.ajax({
// 	type: "POST",
// 	url: root_domain+ finance_root_domain+'app/invoice/',
// 	data: { mode : "load_invoiceno", typeid : id},
// 	success: function(data){
// 				//console.log(data);
// 				var no = jQuery.parseJSON(data);
// 				$('#invoice_no').val(no.invoiceno);
// 				$('#challan_no').val(no.invoiceno);

// 	}
// 	});
// }

function get_series_no() {

	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "get_series_no" },
		success: function (resp) {
			//console.log(resp);
			$('#invoicetype_id').val(resp);
			load_pono(resp);
		}
	});
}
function load_pono(id) {
	if (id != '') {
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "load_invoiceno", typeid: id },
			success: function (data) {
				//console.log(data);
				var no = jQuery.parseJSON(data);
				$('#invoice_no').val(no.invoiceno);
				$('#challan_no').val(no.invoiceno);
				//$('#journal_entry_no').val(no.invoiceno);

			}
		});
	} else {
		$('#invoice_no').val('');
		$('#challan_no').val('');
		toastr.warning("Select Series", "ERROR")
		return false;
	}
}

function show_data() {
	var eid = $('#eid').val();
	var isstockngative = $("#isstockngative").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "load_tempoutward", eid: eid, 'isstockngative': isstockngative },
		success: function (data) {
			//console.log(data);		
			var obj = jQuery.parseJSON(data);
			$('#sale_productdata').html(obj.html_data);
			if (obj.stock == 1) {
				$('#save').attr('disabled', 'disabled');
				$('#saveprint').attr('disabled', 'disabled');
			} else {
				$('#save').removeAttr('disabled');
				$('#saveprint').removeAttr('disabled');
			}
			get_amount();
			get_symbol();
			Unloading();
		}
	});

}

function update_total() {
	var eid = $('#eid').val();
	var g_total = $('#g_total').val();
	var basic_total = $('#total').val();
	var branch_id = $('#branch_id').val();
	var invoice_date = $('#invoice_date').val();
	var currency_id = $("#currency_id").val();
	var currency_rate = $("#currency_rate").val();
	var round_off = $("#round_of").val();
	//var bill_sundry_tax = document.getElementsByName('bill_sundry_tax[]');
	var gst1 = [];
	var gst2 = [];
	var addonsundry = {};

	var values = $("input.gst");
	$.each($(".gst"), function (key, value) {

		var new_key = this.name.match(/\d+/);
		gst1.push(new_key[0]);
		gst2.push($(this).val());

	});

	//console.log(gst1);
	$.ajax({

		type: 'POST',
		data: {
			mode: 'update_total', invoice_id: eid, g_total: g_total, basic_total: basic_total,
			branch_id: branch_id, invoice_date: invoice_date, currency_id: currency_id, currency_rate: currency_rate, bill_sundry_tax: gst1, bill_sundry_tax1: gst2, round_off: round_off
		},
		url: root_domain + finance_root_domain + 'app/invoice/',
		success: function (result) {
			//console.log(result);
			//alert(result);
		}

	})

}

function edit_data(id, table, whereid) {
	$("#product_id").select2('destroy');
	$('#product_id').hide();

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "preedit", id: id, table: table, whereid: whereid },
		success: function (response) {

			//console.log(response);
			var data = jQuery.parseJSON(response);
			var curr = '<?php echo $_SESSION["currency_id"]?>';
			var currency_id = $('#currency_id').val();
			//alert(data.product_type);
			//product_load(data.product_type);					
			//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
			//alert(data.product_id);
			//$("#product_id").select2("val",data.product_id);
			//var load_product=load_prowise_model(data.product_id,data.model_id);
			//$("#model_id").html(load_produc);
			//$("#model_id").select2("val",data.model_id);
			$('#product_id').val(data.product_id);
			$('#parent_cat_id').select2("val", data.parent_category);
			$('#cat_id').select2("val", data.product_category);
			$('#product_type_sel').select2("val", data.product_type);
			$('#product_name_hid').show();

			$('#product_name_hid').val(data.product_name);
			$('#taxtype').text(data.product_gst);
			$(".taxtype").show();


			$('#hsncode').text(data.product_hsn_code);
			$(".hsncode").show();
			//$("#product_hsn_code").val(data.product_hsn_code);
			//Load Product STOCK
			load_stock_qty(data.product_id, data.product_qty);

			$("#product_qty").val(data.product_qty);
			$("#product_qty_hide").val(data.product_qty)
			$("#product_conv_qty_hide").val(data.product_conv_qty)
			$("#product_conv_qty").val(data.product_conv_qty)
			//$("#product_rate").val(data.product_rate);
			$("#product_disc").val(data.product_disc)
			//$("#unit_id").select2("val",data.unit_id);
			$("#unitid").val(data.unit_id);
			$("#conv_unitid").val(data.conv_unit_id)
			$("#unit_show").html(data.unit_name);
			//$("#formulaid").val(data.formulaid);
			$("#discount_per").val(data.discount_per)
			//$("#taxable_value").val(data.taxable_value)
			//$("#bill_value").val(data.bill_value)
			//$("#product_amount").val(data.total)
			$('#trans_type').val(data.transaction_type);
			$('#trans_id').val(data.sales_ordertrn_id);
			$('#trans_stock').val(data.remaning_invoice_qty);
			$("#isbatchwise").val(data.batch_wise_stock_manage);
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
			if (data.batch_wise_stock_manage == 1) {
				$('#addrow1').val('Update');
			} else {
				$('#addrow').val('Update');
			}

			$("#product_id").prop('disabled', true);
			$("#product_type_sel").prop('disabled', true);
			CKEDITOR.instances['product_des'].setData(data.description);
			CKEDITOR.instances['product_spec'].setData(data.product_spec);
			if (currency_id == curr) {
				if (data.product_gst == 'including') {
					var total_tax_per = parseFloat(data.cgst_tax_per) + parseFloat(data.sgst_tax_per) + parseFloat(data.igst_tax_per);
					$("#taxper").val(total_tax_per);
					var totaltax_rate = (parseFloat(data.product_rate) * parseFloat(total_tax_per)) / 100;
					var total_rate = parseFloat(data.product_rate) + parseFloat(totaltax_rate);
					$("#product_rate").val(total_rate);
					$(".pro_amt").show();
					$(".taxrate").show();
					get_amount();
				} else {
					$("#product_rate").val(data.product_rate);
				}
				$("#product_discount").val(data.product_discount)
				$("#product_amount").val(data.product_amount)
			} else {
				if (data.product_gst == 'including') {
					var total_tax_per = parseFloat(data.cgst_tax_per) + parseFloat(data.sgst_tax_per) + parseFloat(data.igst_tax_per);
					$("#taxper").val(total_tax_per);
					var totaltax_rate = (parseFloat(data.product_rate_conv) * parseFloat(total_tax_per)) / 100;
					var total_rate = parseFloat(data.product_rate_conv) + parseFloat(totaltax_rate);
					$("#product_rate").val(total_rate);
					$(".pro_amt").show();
					$(".taxrate").show();
					get_amount();
				} else {
					$("#product_rate").val(data.product_rate_conv);
				}
				$("#product_discount").val(data.product_discount_conv)
				$("#product_amount").val(data.product_amount_conv)
			}
			load_product_unit(data.product_id, data.rate_unit);
			get_tax_details_table();
			get_invoice_total_tax();
			add_product_batch_wise();
			Unloading();
		}
	});
}
function delete_data(id, table, whereid) {
	var r = confirm(" Are you want to delete ?");

	if (r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "delete_data", eid: id, table: table, whereid: whereid, invoice_id: $("#eid").val() },
			success: function (response) {
				console.log(response)
				var data = jQuery.parseJSON(response)
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					show_data();
					get_tax_details_table();
					get_invoice_total_tax();
					update_total();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}

}
function last_rate(mst_rate) {
	Loading()
	var cust_id = $("#cust_id").val();
	var product_id = $("#product_id").val();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "last_rate", product_id: product_id, cust_id: cust_id },
		success: function (resp) {
			//console.log(resp);
			if (resp) {
				$('#product_rate').val(resp);
			} else {
				$('#product_rate').val(mst_rate);
			}

			Unloading();
		}

	});

}
function load_consignee(cust_id, per) {
	//alert(cust_id);
	var product_id = $('#product_id').val();
	if (product_id) {
		load_productdetail(product_id);
	}
	//alert(product_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "load_consignee", cust_id: cust_id },
		success: function (data) {
			//console.log(data);
			$('#consignee_id').html(data);
			$('#consignee_id').select2('val', '');
			Unloading();
			if (per != "1") {
				load_sales_order(cust_id);
			}

		}

	});

}
function open_consignee_click() {
	var cust_id = $('#cust_id').val();
	if (cust_id == "") {
		toastr.warning("Please Select Customer", "WARNING");
	}
	else {
		consignee_modal_open(cust_id);
	}
}

function load_sales_order(cust_id, so_id) {
	var branch_id = $("#branch_id").val();
	if (branch_id) {
		if (cust_id) {
			$('#sales_order_div').attr("style", "display:block");
			//var so_id=$("#sales_order_id").val();
			//alert(so_id);
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain + finance_root_domain + 'app/invoice/',
				data: { mode: "load_sales_order", cust_id: cust_id, branch_id: branch_id },
				success: function (data) {
					//console.log(data);
					$('#sales_order_id').html(data);
					$('#sales_order_id').select2('val', so_id);
					Unloading();
				}

			});
		} else {
			$('#sales_order_div').attr("style", "display:none");
		}
	} else {
		toastr.warning("Please Select Branch", "WARNING");
	}
}
function load_sales_order_data(sales_order_id) {
	if (sales_order_id) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "load_sales_order_data", sales_order_id: sales_order_id },
			success: function (response) {
				/* console.log(response);
				if(response!=""){
					var resp = 	JSON.parse(response);
					$('#order_no').val(resp.sales_order_no);
					$('#order_date').val(resp.sales_order_date);
					$('#product_id').html(resp.pro_html);
					$('#product_id').select2('val','');
					$('#transport_id').select2('val',resp.transport_id);
				} */
				Unloading();
				show_data();
			}
		});
	}
	/*else{
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/invoice/',
			data: { mode : "load_sales_pro"},
			success: function(response){
				console.log(response);
				if(response!="")
				{
					var resp = 	JSON.parse(response);
					$('#order_no').val("");
					$('#order_date').val("");
					$('#product_id').html(resp.pro_html);
					$('#product_id').select2('val','');
				}
				Unloading();
			}
		});
	}*/
}
function load_rate_hist() {

	var cust_id = $("#cust_id").val();
	var product_id = $("#product_id").val();
	if (cust_id == '') {
		toastr.warning("Please Select Customer", "WARNING");
		return false;
	}
	else if (product_id == '') {
		toastr.warning("Please Select Product", "WARNING");
		return false;
	}
	else {

		// Loading();
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "load_rate_hist", cust_id: cust_id, product_id: product_id },
			success: function (response) {
				console.log(response);
				var arr = JSON.parse(response);
				$('#vendor_product_price_list').modal('show');
				$('#vendor_detail1').html(arr.cust_name);
				$('#pr_name').html(arr.product_name);
				$('#product_detail1').html(arr.resp);
			}
		});

	}

}
function open_serial_number() {
	var product_id = $('#product_id').val();
	if (product_id == "") {
		toastr.warning("Please Select Product", "WARNING");
		$("#product_id").select2('focus');
		$('#product_id').select2('open');
	}
	else {
		$('#bs-serial-modal-lg').modal();
	}
}
function load_qty(product_id, old_qty) {

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "load_qty", product_id: product_id },
		success: function (resp) {
			//console.log(resp);
			if (resp != "") {
				$('#product_qty').attr("placeholder", resp);
				//$('#product_qty').attr("max",resp);
				$('#product_stock').val(parseFloat(old_qty) + parseFloat(resp));
				//$("#product_qty").attr("max",parseFloat(old_qty)+parseFloat(resp));
			}
			Unloading();
		}
	});
}

function load_product_typeiwse(type_id) {

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "load_product_typeiwse", type_id: type_id },
		success: function (data) {
			//console.log(data);
			$('#product_id').html(data);
			$('#product_id').select2({
				width: '100%',
				//minimumInputLength: 3
			});
			Unloading();
		}
	});
}

function getBillValue(bill_value) {
	var taxable_value = $('#taxable_value').val();
	var bill_total = taxable_value - bill_value;

	if (bill_total < 0 || bill_value == 0) {
		$('#err_id').html('Enter Value Less Than Taxable Value');
		$("#addrow").attr("disabled", true);
	}
	else {
		$("#addrow").attr("disabled", false);
		$('#err_id').html('');

		$('#bill_black_value').val(bill_total);
		$('#product_amount').val(bill_value);
	}

}


function load_cust_prowise_model(product_id) {
	var cust_id = $('#cust_id').val();

	if (!cust_id) {
		toastr.warning("Select Customer", "ERROR");
		$('#cust_id').select2('focus');
		return false;
	}
	if (product_id) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + 'app/complaint/',
			data: { mode: "load_cust_prowise_model_invoice", product_id: product_id, cust_id: cust_id },
			success: function (response) {
				//console.log(response);
				var resp = jQuery.parseJSON(response);
				//$('#model_id').show(); 
				$('#model_id').html(resp.model_resp_html);
				$('#model_id').select2("val", "");
				Unloading();
			}
		});
	}
}

function load_prowise_model(product_id, model_id) {

	if (product_id) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + 'app/complaint/',
			data: { mode: "load_prowise_model", product_id: product_id },
			success: function (response) {
				//console.log(response);
				var resp = jQuery.parseJSON(response);
				//$('#model_id').show(); 
				$('#model_id').html(resp.model_resp_html);
				$('#model_id').select2("val", model_id);
				Unloading();
			}
		});
	}
}

function load_model_service_status() {
	var cust_id = $('#cust_id').val();
	var product_id = $('#product_id').val();
	var model_id = $('#model_id').val();
	var complaint_date = $('#invoice_date').val();

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + 'app/complaint/',
		data: { mode: "load_model_service_status", model_id: model_id, product_id: product_id, cust_id: cust_id, complaint_date: complaint_date },
		success: function (response) {
			console.log(response);
			var resp = JSON.parse(response);
			$('#ser_status').val(resp.ser_sts);

			Unloading();
		}
	});
}
function copy_quot_trn_data(quotation_id) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/invoice/',
		data: { mode: "copy_quot_trn_data", quotation_id: quotation_id },
		success: function (response) {
			//console.log(response); 
			Unloading();
			show_data();
		}
	});
}
function copy_comp_spare_trn_data(complaint_id) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "copy_comp_spare_trn_data", complaint_id: complaint_id },
		success: function (response) {
			//alert(response);
			//console.log(response); 
			Unloading();
			show_data();
		}
	});
}
function open_inv_srl_no(trancation_id, product_name) {
	$('#inv_srl_modal').modal('show');
	$('#ref_trancation_id').val(trancation_id);
	$('#head_inv_srl_modal_pro_name').html(product_name);
	show_pro_srl_no();
}
function add_pro_srl_no() {
	if (!$("#pro_srl_no").val()) {
		toastr.warning("Enter Serail No.", "ERROR");
		$("#pro_srl_no").focus();
		return false;
	}

	var form_data = {
		mode: "add_pro_srl_no",
		pro_srl_no: $("#pro_srl_no").val(),
		trancation_id: $("#ref_trancation_id").val(),
		invoice_id: $("#eid").val()
	};

	$('#add_pro_srl_no_btn').prop("disabled", true);
	$.ajax({
		type: "POST",
		url: root_domain + 'app/invoice/',
		data: form_data,
		success: function (response) {
			//console.log(response);
			$("#pro_srl_no").val("");
			$('#add_pro_srl_no_btn').html('Add');
			$('#add_pro_srl_no_btn').prop("disabled", false);
			Unloading();
			show_pro_srl_no();
		}
	});
}
function show_pro_srl_no() {
	var trancation_id = $('#ref_trancation_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/invoice/',
		data: { mode: "show_pro_srl_no", trancation_id: trancation_id },
		success: function (resp) {
			//console.log(resp);
			$('#inv-srlno-modal-datatable').html(resp);
			Unloading();
			count_pro_srl_no();
		}
	});
}
function count_pro_srl_no() {
	var trancation_id = $('#ref_trancation_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/invoice/',
		data: { mode: "count_pro_srl_no", trancation_id: trancation_id },
		success: function (resp) {
			//console.log(resp);
			if (resp.trim() == '1') {
				$('#add_pro_srl_div').show();
			}
			else {
				$('#add_pro_srl_div').hide();
			}
		}
	});
}
function delete_inv_srl_data(inv_srl_trn_id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + 'app/invoice/',
			data: { mode: "delete_inv_srl_data", inv_srl_trn_id: inv_srl_trn_id },
			success: function (response) {
				//console.log(response);
				var data = jQuery.parseJSON(response);
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_pro_srl_no();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				Unloading();
			}
		});
	}
}

function check_due_payment(cid) {
	if (cid) {
		$.ajax({
			type: "POST",
			url: root_domain + 'app/complaint/',
			data: { mode: "check_complian_due", cust_id: cid },
			success: function (resnse) {
				if (resnse > 0) {
					$('#cust_status_due_show').show();
					$('#save').prop('disabled', true);
					$('#saveprint').prop('disabled', true);
					$('#addrow').prop('disabled', true);
					$('#check_due_div').show();
				}
				else {
					$('#cust_status_due_show').hide();
					$('#save').prop('disabled', false);
					$('#saveprint').prop('disabled', false);
					$('#addrow').prop('disabled', false);
					$('#check_due_div').hide();
				}
			}
		});
	}
}

function enable_invoice() {
	if ($('#check_due').is(":checked")) {
		$('#cust_status_due_show').hide();
		$('#save').prop('disabled', false);
		$('#saveprint').prop('disabled', false);
		$('#addrow').prop('disabled', false);
	}
	else {
		$('#cust_status_due_show').show();
		$('#save').prop('disabled', true);
		$('#saveprint').prop('disabled', true);
		$('#addrow').prop('disabled', true);
	}
}
// Dimple Panchal : start
function get_tax_on_total(formula_id) {
	if (formula_id)//tax calculation on total 
	{
		var total = $("#total").val();
		var formulaid = $("#formula_id").val();
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + 'app/invoice/',
			data: { mode: "get_tax_on_total", total: total, formulaid: formulaid },
			success: function (response) {
				var obj = jQuery.parseJSON(response);
				$('#tcs_total').val(obj.tax_value);
			}
		});
	}
}
function paymentmode(id) {
	$.ajax({
		type: "POST",
		url: root_domain + 'app/payment_new/',
		data: { mode: "bank_type1", id: id },
		success: function (data) {

			var data = JSON.parse(data);

			if (data.type == "cash") {
				$('#cheque_data').hide();
			} else {
				$('#save_cheque').show();
				$('#cheque_dtl').val('');
				$('#cheque_data').show();
				get_chequeno(id, 'cheque_dtl');
			}
		}
	});
}
function show_tcs_row(cust_id) {
	if (cust_id) {
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + 'app/invoice/',
			data: { mode: "show_tcs_row", cust_id: cust_id },
			success: function (response) {
				if (response > 0) {
					$(".tcs_tax").show();
				} else {
					$(".tcs_tax").hide();
				}
			}
		});
	}
}
// Dimple Panchal : end

//Dhruv Start Code
function get_statecode(cust_id) {
	if (cust_id) {

		if ($("#branch_id").val() == "") {
			toastr.warning("Select Branch Id", "ERROR");
			$("#branch_id").select2("focus");
			$("#cust_id").select2('val', '');
			return false;
		}

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
				//alert(response);
				if (response) {
					$("#gross").show();
					$(".gross").text(response);
				} else {
					$("#gross").hide();
				}
			}
		});
	}
}

function get_hsn(product_id) {
	//alert(product_id);
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "get_hsn_code", product_id: product_id },
		success: function (response) {
			if (response != '') {
				$('#hsncode').text(response);
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

function get_tax_details_table() {

	var eid = $('#eid').val();
	var cust_id = $('#cust_id').val();
	var company_tax_editable = $("#company_tax_editable").val();
	var currency_id = $('#currency_id').val();
	var addontax1 = [];
	$(".addontax").each(function () {
		//alert(this.value);
		addontax1.push(this.value);
	});

	var salestype = $("#sales_type").val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: {
			mode: "get_tax_details_table", invoice_id: eid, cust_id: cust_id,
			addontax1: addontax1, salestype: salestype, currency_id: currency_id
		},
		success: function (response) {
			//alert(response);
			var arr = JSON.parse(response);
			if (arr) {
				if (company_tax_editable == 0) {
					$(".tax_details").html(arr.resp);
				}

				//$(".gross").text(response);
			}
		}
	});
	get_symbol();
}

function update_netbalance() {
	get_gtotal();
}

function get_invoice_total_tax() {

	var eid = $('#eid').val();
	var addontax1 = 0;
	$(".addontax").each(function () {
		var addontax = (this.value).split("-");
		addontax1 = Number(addontax1) + Number(addontax[0]);
	});
	var salestype = $("#sales_type").val();
	var currency_id = $("#currency_id").val();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: {
			mode: "get_invoice_total_tax", cust_id: $('#cust_id').val(),
			gross: $('.gross').text(), inv_total: $('#total').val(), invoice_id: eid, addontax1: addontax1,
			salestype: salestype, currency_id: currency_id
		},
		success: function (response) {

			//alert(response);
			//console.log(response);
			var arr = JSON.parse(response);
			if (arr) {
				$(".invoiceTotalTax").html(arr.resp);
				if (arr.isTcs == 1) {
					$('.tcs_details').show();
				} else {
					$('.tcs_details').hide();
				}
				//$(".gross").text(response);
				get_symbol();
			}
		}
	});
}

function authToken() {
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "eway_api", },
		success: function (response) {

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


var rowIdx = 0;
// jQuery button click event to add a row
function addBillSundry() {

	var taxableamount = 0;
	var totalsundryexist = 0;
	var basic_amount = $("#total").val();
	var netamount = $("#g_total").val();
	var cust_id = $("#cust_id").val();
	//alert(netamount);
	$(".gst").each(function () {
		var gstVal = $('.gst').val();
		taxableamount = Number(taxableamount) + Number(gstVal);
	});

	$(".billsundryclass").each(function () {
		var billsundryclass = $(this).val();
		totalsundryexist = Number(totalsundryexist) + Number(billsundryclass);
	});

	//alert(totalsundryexist);

	var eid = $('#eid').val();

	var bill_sundry_value = $("#bill_sundry").val();

	var bill_sundry = $("#bill_sundry option:selected").text();
	var bill_sundry_amount = $('#bill_sundry_amount').val();

	var currency_enable = $('#currency_enable').val();
	var currency_id = $('#currency_id').val();
	var currency_rate = $('#currency_rate').val();
	var company_tax_editable = $("#company_tax_editable").val();
	var sales_type = $("#sales_type").val();
	if (bill_sundry_value == 0) {
		Unloading();
		toastr.warning("Please Select Bill Sundry", "ERROR")
		return false;

	} else if (bill_sundry_amount == '') {
		Unloading();
		toastr.warning("Please insert Bill Sundry Amount", "ERROR")
		return false;
	} else {
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: {
				mode: "get_bill_sundry_details", sundry_ledger_id: bill_sundry_value, totalsundryexist: totalsundryexist, taxableamount: taxableamount,
				basic_amount: basic_amount, netamount: netamount, default_amount: bill_sundry_amount,
				invoice_id: eid, sales_type: sales_type, currency_enable: currency_enable, currency_id: currency_id, currency_rate: currency_rate, invoice_date: $('#invoice_date').val(), cust_id: cust_id
			},
			success: function (response) {
				var arr1 = JSON.parse(response);
				var arr = arr1.split(",");
				console.log(arr);
				if (arr[3]) {
					get_all_bill_sundry(eid);
					//get_gtotal();
				}
				else {
					if (arr[0]) {

						if (arr[4] != 0) {
							$('.sundryadded').append(`<div class="form-group R${++rowIdx}">
								<label class="col-md-5 control-label">${bill_sundry}${arr[2]} <span class="currency_icon"></span></label>
								<div class="col-md-4">
								<input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[1]}">
								<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="${arr[1]}" readonly placeholder="Amount">
								<input class="addontax" name="bill_sundry_addon_tax[${bill_sundry_value}]" type="hidden" value="${arr[4]}-${arr[5]}-${arr[1]}" >
								</div>
								<div class="col-md-3">
								<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
								type="button" value="R${rowIdx}" onclick="removeSundry(${bill_sundry_value},${arr[1]},this.value)"><i class="fa fa-times"></i></button>
								</div>
								
								</div>`);
							if (company_tax_editable != 1) {
								get_invoice_total_tax();
							}
							get_tax_details_table();
							get_gtotal();
						} else {
							$('.sundryadded').append(`<div class="form-group R${++rowIdx}">
								<label class="col-md-5 control-label">${bill_sundry}${arr[2]} <span class="currency_icon"></span></label>
								<div class="col-md-4">
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

						// <br><br>
						// <label class="col-md-5 control-label">${bill_sundry}${arr[2]} TAX (${arr[5]} %)</label>
						// <div class="col-md-4">
						// 	<input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[4]}">
						// 	<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="${arr[4]}" readonly placeholder="Amount">
						// </div>
						// <div class="col-md-3">
						// 	<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
						// 		type="button" value="R${rowIdx}" onclick="removeSundry(${bill_sundry_value},${arr[4]},this.value)"><i class="fa fa-times"></i></button>
						// </div>
					}
				}

				Unloading();
			}
		});


	}
	get_symbol();
	//Unloading();

}


function get_all_bill_sundry(invoice_id) {
	$.ajax({
		type: 'POST',
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: 'get_all_bill_sundry', invoice_id: invoice_id },
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
	})
}

function removeSundry(bill_sundry_value, bill_sundry_amount, id, ledger_id = '') {

	var edit_id = $('#eid').val();
	var cust_ledger_id = $("#cust_id").val();
	var company_tax_editable = $("#company_tax_editable").val();

	/*alert(ledger_id);*/
	//alert(id);
	if (edit_id == '' || edit_id == '0') {
		//alert(ledger_id);
		var netamount = $("#g_total").val();

		var finalNetAmount = Number(netamount) - Number(bill_sundry_amount);

		$("#g_total").val(finalNetAmount);

		$('.' + id).remove();

		if (company_tax_editable != 1) {
			get_invoice_total_tax();
		}
		get_tax_details_table();
		get_gtotal();
	}
	else {

		$.ajax({

			type: 'post',
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: 'remove_sundry', edit_id: edit_id, ledger_id: ledger_id, cust_ledger_id: cust_ledger_id },
			success: function (result) {
				get_all_bill_sundry(edit_id);
				get_invoice_total_tax();
				get_tax_details_table();
				get_gtotal();
			}
		})
	}


}

function load_sales_order_popup(id) {
	if (id == 'yes') {
		$("#modal-sales-order").modal("show");
		$('#sales_order_link').show();
	}
	else {
		$('#sales_order_link').hide();
	}
}

function get_sales_order_details(id) {
	//if(id =='yes'){

	var cust_id = $('#cust_id').val();
	var branch_id = $('#branch_id').val();
	var user_id = $('#user_id').val();
	$("#modal-sales-order").modal("show");
	$(".so_orders").html('');
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "get_sales_order_details", transaction_type: id, cust_id: cust_id, branch_id: branch_id, isallocate: id, user_id: user_id },
		success: function (response) {
			var arr = JSON.parse(response);
			if (arr) {

				$(".so_orders").html(arr);

			}
		}
	});

	//}
}


function add_sales() {


	//alert($('#transaction_type').val());
	if ($('#transaction_type').val() == 0) {
		toastr.warning("Please Select transaction type first", "ERROR")
		return false;
	}

	var sales_order = [];
	$(".sales_order").each(function () {
		if ($(this).is(":checked")) {
			sales_order.push($(this).val());
		}

	});
	//alert(sales_order);
	if (sales_order.length === 0) {
		toastr.warning("Please Select order and then submit", "ERROR")
		return false;
	}

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "add_sales_order", transaction_type: $('#transaction_type').val(), sales_order: sales_order, cust_id: $('#cust_id').val(), cust_stateid: $('#cust_stateid').val() },
		success: function (response) {
			console.log(response);
			var arr = JSON.parse(response);
			if (arr.msg == '1') {

				$('#modal-sales-order').modal('toggle');
				$('#payment_terms').val(arr.payment_terms);
				if (arr.enable_consignee == 0) {
					$("#enable_consignee").prop('checked', false);
					$('#consignee_id_div').css('display', 'block');
					$('#edit_consignee_party').val(arr.consignee_id);
				} else {
					$("#enable_consignee").prop('checked', true);
					$('#consignee_id_div').css('display', 'none');
				}
				load_consignee_new();
				$('#consignee_id').val(arr.consignee_id);
				$('#enable_transport').val(arr.enable_transport);
				$('#edit_id_transport').val(sales_order);
				show_data();
				get_tax_details_table();
				get_invoice_total_tax();
				get_sales_bill_sundry(sales_order, 1);
			} else {
				toastr.warning("Something went wrong", "WARNING");
				return false;
			}
		}
	});
}

function get_ledger_details(ledger_id) {
	var company_cost_center = $('#company_cost_center').val();
	var company_tcs = $('#company_tcs').val();
	var company_eway = $('#company_eway').val();
	var company_salesman = $('#company_salesman').val();
	var company_trans = $('#company_trans').val();
	var so_enable_transport = $('#so_enable_transport').val();
	var kind_attn_hidden = $('#kind_attn_hidden').val();

	$('#is_sales_order').select2('val', 'no');
	$('#sales_order_link').hide();
	$('#transaction_type').val('0');
	$(".so_orders").html('');

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
			$("#kind_attn").html(obj.c_person);
			if (kind_attn_hidden != 0) {
				$("#kind_attn").val(kind_attn_hidden);
			}

			//TCS Popup
			if (obj.enable_tcs == 1 && company_tcs == 1) {
				$('#tcs_div').show();
			}

			//Eway Bill Popup
			if (company_eway == 1) {
				$('#eway_div').show();
			}

			//Transport Popup
			if (company_trans == 1 || so_enable_transport == 1) {
				$('#tran_div').show();
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

			load_consignee_new();

		}
	})

}

function get_tcs_popup(id) {

	Loading(true);

	if (id == 'yes') {
		var cgst = $('#CGST').val();
		var sgst = $('#SGST').val();
		var igst = $('#IGST').val();
		var tcs = $('#TCS').val();

		if ((cgst != 0) && (sgst != 0) && (typeof cgst != "undefined") && (typeof sgst != "undefined")) {
			gst = Number(cgst) + Number(sgst);
		} else if (igst != '' && (typeof igst != "undefined")) {
			gst = Number(igst);
		} else {
			gst = 0;
		}


		$("#modal-tcs-details").modal("show");
		load_tcs_details();
		$("#tcs_ref_no").val($('#invoice_no').val());
		$("#tcs_amt").val(Number($('#total').val()) + Number(gst));
		$("#tcs_percentage").val($('#tcs_per').val());
		$("#tcs_amount").val(tcs);
		$("#tcs_total_tax").val(Number(tcs) + Number($('#tcs_sur_percentage_amount').val()));

		Unloading();
		$('#tcs_detail_link').show();
	}
	else {
		$('#tcs_detail_link').hide();
		Unloading();
	}
}

function load_tcs_details() {
	var eid = $('#eid').val();

	$.ajax({

		type: 'POST',
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "load_tcs_detail", invoice_id: eid },
		success: function (result) {
			var obj = JSON.parse(result);
			//console.log(result);

			$('#tcs_lower_rate').select2("val", obj.tcs_lower_rate);
			$('#tcs_lower_rate_reason').val(obj.tcs_lower_rate_reason);
			$('#tcs_section').val(obj.tcs_section);
			$('#tcs_collection_code').val(obj.tcs_collection_code);
			$('#tcs_ref_no').val(obj.tcs_ref_no);
			$('#tcs_amt').val(obj.tcs_amt);
			$('#tcs_collected_on').val(obj.tcs_collected_on);
			$('#tcs_invoice_date').val(obj.tcs_invoice_date);
			$('#tcs_percentage').val(obj.tcs_percentage);
			$('#tcs_amount').val(obj.tcs_amount);
			$('#tcs_sur_percentage').val(obj.tcs_sur_percentage);
			$('#tcs_sur_percentage_amount').val(obj.tcs_sur_percentage_amount);
			$('#tcs_total_tax').val(obj.tcs_total_tax);
			$('#edit_tcs_id').val(obj.tcs_deduct_id);

		}
	})

}

function add_tcs_field() {

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: {
			mode: "add_tcs_details", edit_id: $('#edit_tcs_id').val(), tcs_lower_rate: $('#tcs_lower_rate').val(), tcs_lower_rate_reason: $('#tcs_lower_rate_reason').val(),
			tcs_section: $('#tcs_section').val(), tcs_collection_code: $('#tcs_collection_code').val(), tcs_ref_no: $('#tcs_ref_no').val(), tcs_amt: $('#tcs_amt').val(),
			tcs_collected_on: $('#tcs_collected_on').val(), tcs_invoice_date: $('#tcs_invoice_date').val(), tcs_percentage: $('#tcs_percentage').val(), tcs_amount: $('#tcs_amount').val(),
			tcs_sur_percentage: $('#tcs_sur_percentage').val(), tcs_sur_percentage_amount: $('#tcs_sur_percentage_amount').val(), tcs_total_tax: $('#tcs_total_tax').val(),

		},
		success: function (response) {
			//alert(response);
			//var arr = JSON.parse(response);
			if (response == 1) {
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
				Unloading();
			} else if (response == 2) {
				toastr.success("DATA UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
			} else {
				toastr.warning("Something went wrong", "WARNING");
				return false;
			}
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
// Added By Maulik Kapatel
function get_so(vender_id, so_id, modee) {
	$('#sales_order_id').empty();

	$.ajax({
		type: 'post',
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: 'get_so_by_vendor', vender_id: vender_id, so_id: so_id, modee: modee },
		success: function (result) {

			//alert(result);
			if (result != 0) {
				$('#salesorder_div').show();
				$('#sales_order_id').append(result);
			}
			else {
				$('#salesorder_div').hide();
			}
		}
	});
}
function insert_product(sales_order_ids) {
	if ($("#cust_id").val() == "") {
		toastr.warning("Select Customer Name", "ERROR")
		return false;
	}
	if (sales_order_ids == '') {
		var sales_order_id = $('#salesorderid').val();
	} else {
		var sales_order_id = sales_order_ids;
	}
	var eid = $('#eid').val();
	var cust_stateid = $('#cust_stateid').val();
	var vender_id = $('#cust_id').val();
	var branch_id = $('#branch_id').val();

	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "insert_product", sales_order_id: sales_order_id, eid: eid, cust_stateid: cust_stateid, cust_id: vender_id, branch_id: branch_id },
		success: function (data) {
			console.log(data);
			//var no = jQuery.parseJSON(data);
			show_data();
			get_tax_details_table();
			get_invoice_total_tax();
		}
	});
}
// $("#sales_order_add").on('submit',function(e) {
// 	// var form = this;
// 	e.preventDefault();

// 	e.stopPropagation();	

// 	// form.submitted = true;	

// });

// $("#sales_order_add").on('submit',function(e) {



// });


//Dhruv End Code

function check_previos_date(due_date) {
	//alert(due_date);
	var invoice_date = $('#invoice_date').val();


	var prv_date = new Date(invoice_date.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));
	var new_date = new Date(due_date.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));


	if (new_date < prv_date) {
		$('.invoice_due_date_error').html("select date greater than invoice date");
	}
	else {
		$('.invoice_due_date_error').html('');
	}
	//alert(prv_date);

}

//product loading in pagination
function product_load(pr_type = "") {

	//alert(pr_type);
	if (pr_type == '') {
		var product_type_sel = $('#product_type_sel').val();
	}
	else {
		var product_type_sel = pr_type;
	}
	var product_category = '';
	var cat = '';
	if (reciclar == 1) {
		product_category = $("#cat_id").val();
		cat = '&product_category=' + product_category;
	}
	//alert(product_type_sel);
	var testData = [];
	//$("#product_id").html("");
	var mainurl = root_domain + finance_root_domain + 'app/product_load/index.php?mode=product_load&product_type_sel=' + product_type_sel + cat;
	$.getJSON(mainurl, function (json) {
		var arr = new Array();
		var len = json[0].length;
		//console.log(len);

		for (var i = 0; i < len; i++) {
			testData.push({ id: json['0'][i], text: json['1'][i] });
			//alert(json['1'][i]);
		}
	});
	load_cat_product('product_id', testData)
	// return testData;
	//console.log(testData);
}
function load_cat_product(id, testData) {
	//console.log(id);
	//console.log(testData);
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
function get_so_detail(cust) {
	//alert(cust);
	$.ajax({

		type: 'POST',
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: 'mode=get_so_detail' + '&cust=' + cust,
		success: function (result) {

		}
	})
}
function enable_hypothication() {
	var check_hypothication = $('#check_hypothication').val();

	if ($('#check_hypothication').is(':checked') == true) {
		$('#hypo_bank_div').show();
	}
	else {
		$('#hypo_bank_div').hide();
	}
}
function load_consignee_new() {
	//alert(cust_id);
	if ($('#enable_consignee').is(':checked') == false) {

		$('#consignee_id_div').show();

		// Loading();

		var cust_id = $('#cust_id').val();
		var edit_consignee_party = $('#edit_consignee_party').val();

		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "load_consignee", cust_id: cust_id },
			success: function (data) {
				//console.log(data);
				$('#consignee_id').html(data);
				$('#consignee_id').select2({
					width : "100%"
				});
				$('#consignee_id').val(edit_consignee_party);
				Unloading();

			}

		});

	}
	else {
		$('#consignee_id_div').hide();
	}

}

function change_due_date(id) {
	var days = $("#payment_terms").find(':selected').data('days');
	$('.due_date_class').datepicker('setDate', new Date());
	var date2 = $('.due_date_class').datepicker('getDate', '+1d');
	date2.setDate(date2.getDate() + days);
	$('.due_date_class').datepicker('setDate', date2);
}
function create_eway_bill(invoice_id) {
	var r = confirm(" Are you want to Genrate E-Way Bill ?");

	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/index_eway',
			data: { mode: "detail_show_eway_bill", invoice_id: invoice_id },
			success: function (response) {
				var obj = JSON.parse(response);

				$('#ModalEwayBill_new').modal('show');
				$("#invoice_id").val(invoice_id);
				get_sub_type(1);
				$("#supply_type").val(obj.supply_type);
				$("#sub_type").val(obj.sub_type);
				$("#doc_type").val(obj.doc_type);
				$("#doc_no").val(obj.doc_no);
				$("#doc_date").val(obj.doc_date);
				$("#sup_gst_no").val(obj.sup_gst_no);
				$("#sup_name").val(obj.sup_name);
				$("#sup_add1").val(obj.sup_add1);
				$("#sup_city").val(obj.sup_city);
				$("#sup_state").val(obj.sup_state);
				$("#sup_pincode").val(obj.sup_pincode);
				$("#rec_gst_no").val(obj.rec_gst_no);
				$("#rec_name").val(obj.rec_name);
				$("#rec_add1").val(obj.rec_add1);
				$("#rec_city").val(obj.rec_city);
				$("#rec_state").val(obj.rec_state);
				$("#rec_pincode").val(obj.rec_pincode);
				$("#eway_product_detail").html(obj.eway_product_detail);
				Unloading();
			}
		});
	}
}

function get_sub_type(type) {
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/index_eway',
		data: { mode: "get_sub_type", type: type },
		success: function (response) {
			$("#sub_type").html(response);
		}
	});

}
function add_eway_bill() {
	var sub_type = $("#sub_type").val();
	var doc_date = $("#doc_date").val();
	var trn_name = $("#trn_name").val();
	var trn_doc_no = $("#trn_doc_no").val();
	var trn_doc_date = $("#trn_doc_date").val();
	var trn_distance = $("#trn_distance").val();
	var vehicle_no = $("#vehicle_no").val();
	var TransporterId = $("#TransporterId").val();
	var trn_mode = $("#trn_mode").val();
	var invoice_id = $("#invoice_id").val();

	if (sub_type == "") {
		toastr.warning("Please Select Sub Type", "ERROR");
		return false;
	}
	if (doc_date == "") {
		toastr.warning("Please Enter Document Date", "ERROR");
		return false;
	}
	if (trn_mode == "") {
		toastr.warning("Please Select Transport Mode", "ERROR");
		return false;
	}
	if (trn_mode == 1) {
		if (trn_name == "") {
			toastr.warning("Please Select Transporter Name", "ERROR");
			return false;
		}
		if (TransporterId == "" || vehicle_no == "") {
			toastr.warning("Please Enter TransporterId OR  Vehicle No", "ERROR");
			return false;
		}
	} else {
		if (trn_doc_no == "") {
			toastr.warning("Please Enter Transport Document No", "ERROR");
			return false;
		}
		if (trn_doc_date == "") {
			toastr.warning("Please Enter Transport Document Date", "ERROR");
			return false;
		}
	}
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/index_eway',
		data: { mode: "add_eway_bill", sub_type: sub_type, doc_date: doc_date, trn_mode: trn_mode, trn_name: trn_name, trn_doc_no: trn_doc_no, trn_doc_date: trn_doc_date, trn_distance: trn_distance, vehicle_no: vehicle_no, invoice_id: invoice_id, TransporterId:TransporterId },
		success: function (response) {
			console.log(response);
			var obj = JSON.parse(response);
			if (obj.status == '1') {
				toastr.success(obj.msg, "Success");
				$('#ModalEwayBill_new').modal('hide');
			} else {
				toastr.warning(obj.msg, "ERROR");
				return false;
			}
		}
	});

}
//E-Invoice code Start By pathik 
function create_einv_bill(invoice_id) {
	var r = confirm(" Are you want to Genrate E-Invoice ?");

	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/index_eway',
			data: { mode: "detail_show_e_invoice", invoice_id: invoice_id },
			success: function (response) {
				var obj = JSON.parse(response);

				$('#Modaleinv_new').modal('show');
				$("#einv_invoice_id").val(invoice_id);
				$("#einv_doc_type").val(obj.einv_doc_type);
				$("#einv_doc_no").val(obj.einv_doc_no);
				$("#einv_doc_date").val(obj.einv_doc_date);
				$("#einv_seller_gstn").val(obj.einv_seller_gstn);
				$("#einv_seller_name").val(obj.einv_seller_name);
				$("#einv_seller_add").val(obj.einv_seller_add);
				$("#einv_seller_state").val(obj.einv_seller_state);
				$("#einv_seller_statecode").val(obj.einv_seller_statecode);
				$("#einv_seller_pincode").val(obj.einv_seller_pincode);
				$("#einv_seller_phoneno").val(obj.einv_seller_phoneno);
				$("#einv_seller_email").val(obj.einv_seller_email);


				$("#einv_product_detail").html(obj.einv_product_detail);

				Unloading();
			}
		});
	}
}
function add_einv_bill() {
	var einv_supply_type = $("#einv_supply_type").val();
	var rev_charg = $("#rev_charg").val();
	var invoice_id = $("#einv_invoice_id").val();

	if (einv_supply_type == "") {
		toastr.warning("Please Select Type of Supply", "ERROR");
		return false;
	}
	if (rev_charg == "") {
		toastr.warning("Please Select Reverse Charge", "ERROR");
		return false;
	}

	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/index_eway',
		data: { mode: "add_einv_bill", einv_supply_type: einv_supply_type, rev_charg: rev_charg, invoice_id: invoice_id },
		success: function (response) {
			console.log(response);
			var obj = JSON.parse(response);
			if (obj.status == '1') {
				toastr.success(obj.msg, "Success");
				$('#Modaleinv_new').modal('hide');
			} else {
				toastr.warning(obj.msg, "ERROR");
				return false;
			}
		}
	});

}
//E-Invoice code End By pathik 
function get_sales_bill_sundry(id, type) {
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "get_sales_bill_sundry", id: id, type: type },
		success: function (response) {
			// var arr = JSON.parse(response);
			$('.sundryadded').append(response);
			get_amount();
			get_tax_details_table();
			get_invoice_total_tax();
			get_gtotal();
		}
	});
}

function preview_update_user(id, no, preview_user) {
	$('#preview_user_update').modal('show');
	$('#ref_mod_no').html(no);
	$('#ref_mod_id').val(id);
	$('#preview_user').val(preview_user);
	load_user_update_log();
}

function load_user_update_log() {
	var invoice_id = $('#ref_mod_id').val();

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
		"sAjaxSource": root_domain + finance_root_domain + 'app/invoice/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "update_user_log_history" },
				{ "name": "invoice_id", "value": invoice_id });
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
		url: root_domain + finance_root_domain + 'app/invoice/',
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
function tc_format_view() {
	var tc_for = $("input[name='tc_format']:checked").val();
	if (tc_for == 1) {
		$("#format_1").show();
		$("#format_2").hide();
	} else {
		$("#format_1").hide();
		$("#format_2").show();
	}
}

function load_typeswise_terms(invoice_id) {
	var quot_type = $('input[name="quot_type"]:checked').val();
	var cust_id = $("#cust_id").val();
	var terms_type = $('input[name="terms_type"]:checked').val();
	var sales_order_id = $('#term_salesorder_id').val();
	/*if(invoice_id == ''){
		invoice_id = $("#eid").val();
	}*/
	if (quot_type || quot_type == 0) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "load_typeswise_terms", quot_type: quot_type, invoice_id: invoice_id, cust_id: cust_id, terms_type: terms_type, sales_order_id: sales_order_id },
			success: function (response) {
				var resp = JSON.parse(response);
				$('#po_terms_cond_div').html(resp.resp_html);
				Unloading();
			}
		});
	}
}

function get_so_data_invoice() {
	var quot_type = $('input[name="quot_type"]:checked').val();
	var terms_type = $('input[name="terms_type"]:checked').val();
	var user_id = $("#user_id").val();
	var invoice_id = $("#eid").val();
	var cust_id = $("#cust_id").val();
	var branch_id = $("#branch_id").val();

	if (terms_type == 2) {
		$('#salesorder_wise_term').show();
		$.ajax({
			type: "POST",
			url: root_domain + finance_root_domain + 'app/invoice/',
			data: { mode: "get_so_invoice_data", user_id: user_id, invoice_id: invoice_id, cust_id: cust_id, branch_id: branch_id },
			success: function (response) {
				var resp = JSON.parse(response);
				$('#term_salesorder_id').html(resp.resp_html);
				$('#term_salesorder_id').select2("val", resp.term_salesorder_id);
				load_typeswise_terms(invoice_id);
			}
		});
	} else {
		$('#salesorder_wise_term').hide();
		$('#term_salesorder_id').select2("val", "");
		load_typeswise_terms(invoice_id);
	}
}

function terms_check_all(obj) {
	$('.terms_checkbox').prop('checked', obj.checked);
}

function load_parent_cat() {
	var parent_id = $("#parent_cat_id").val();
	$.ajax({
		type: "POST",
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "load_parent_cat", parent_id: parent_id },
		success: function (response) {
			$("#cat_id").html(response);
		}
	});
}

function get_terms_detail(id) {
	var tc_id = $("#ref_tc_id" + id).val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + finance_root_domain + 'app/invoice/',
		data: { mode: "get_terms_detail", tc_id: tc_id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#tc_details" + id).val(obj.tc_details);
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



function get_gtotal_roundoff() {
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

	}
	$("#total").val(parseFloat(total));

	var gst_arr = document.getElementsByClassName('gst');

	for (var k = 0; k < gst_arr.length; k++) {

		var k1 = gst_arr[k].value;
		total = parseFloat(total) + parseFloat(k1);
		//alert(total);
	}
	//alert(total);
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
	
	if ($("#is_power_drive").val() == '1') {
		$("#g_total").val(g_total);
	} else {		
		var round_of = parseFloat($("#round_of").val()) || 0;
		var round_of_total = g_total + round_of;
		$("#g_total").val(round_of_total);
	}
	
	$("#paid_amount").val(g_total.toFixed(2));
	update_total();
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


function add_consignee_open(){
	var cust_id = $('#cust_id').val();
	var cust_name = $("#cust_id option:selected").text();
	if(cust_id){
		$('#bs-consignee-modal-lg').modal('show');
		$("#cuname").html(cust_name);
		$("#ledger_id").val(cust_id);
		//$('#preview_cust_dtls_div').html(obj.html_resp);
			
	}else{
            toastr.warning("Select Company First", "ERROR");
        }
}


function led_add_consignee(){

	if($("#lconsignee_comp_name").val()===""){		
		toastr.warning("Enter Consignee Company Name", "ERROR");
		return false;
	}
	if($("#lconsignee_name").val()===""){		
		toastr.warning("Enter Consignee Name", "ERROR");
		return false;
	}
    var comp_name=$('#lconsignee_comp_name').val();
    var con_name=$('#lconsignee_name').val();
	var con_mobile=$('#lconsignee_mobile').val();
	var con_email=$('#lconsignee_email').val();
    var con_address = $('#lconsignee_address').val();
    var country_consinee_id = $('#lcountry_consinee_id').val();
    var state_consinee_id = $('#lstate_consinee_id').val();
    var city_consinee_id = $('#lcity_consinee_id').val();
    var gst_consinee_no = $('#lgst_consinee_no').val();
    var pin_consinee_no = $('#lpin_consinee_no').val();
	var model=$('#lmodel').val();
	var cust_id=$('#cust_id').val();
	
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger_consignee/',
		data: { mode : "add_consignee",
					edit_id:$("#edit_id_consignee").val(),
                    comp_name:comp_name,
                    con_name:con_name,
                    con_mobile:con_mobile,
                    con_address : con_address,
                    con_email:con_email,
                    cust_id:cust_id,
                    country_consinee_id: country_consinee_id,
                    state_consinee_id: state_consinee_id,
                    city_consinee_id: city_consinee_id,
                    gst_consinee_no: gst_consinee_no,
                    pin_consinee_no: pin_consinee_no,
                    model: model
        },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
           if(data.msg == '2'){
                toastr.warning("Consignee already exist", "ERROR");
            }else if(data.msg == '3') {
            	$("#consignee_comp_name").val("");
				$("#consignee_name").val("");
			    $("#consignee_mobile").val("");
			    $("#consignee_email").val("");
                $("#consignee_address").val("");
                $("#gst_consinee_no").val("");
                $("#pin_consinee_no").val("");
                $("#add_consignee_btn").val("Add");
				$("#bs-consignee-modal-lg").modal("hide");
				$('#consignee_id').append('<option value='+data.cust_id+'>'+data.company_name+'</option>');	
                $("#consignee_id").trigger('change')
				$('#consignee_id').select2("val",data.cust_id);
				$('#consignee_add').trigger('reset');
				$("#country_consinee_id").val('101').trigger("change");
				load_consinee_state('101','state_consinee_id','');
				$("#state_consinee_id").val('1').trigger("change");
				load_consinee_city('1','city_consinee_id','');
				$("#city_consinee_id").val('1').trigger("change");
				toastr.success("Consignee Editd SUCCESSFULLY", "success");
				
			}
            Unloading();
		}
	});
}

function load_consinee_state(parentid,control,val1)
{	
        $.ajax({
		type: "POST",
		url: root_domain+'app/ledger_consignee/',
		data: { mode : "load_state",  id : parentid},
		success: function(responce){
			//console.log(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}
function load_consinee_city(parentid,control,val1)
{	
	//alert(parentid);
	$.ajax({
		type: "POST",
		url: root_domain+'app/ledger_consignee/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			//console.log(responce);
			//alert(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}

// Export Invoice list data
function exportCsv() {
	var rep_date = $('#rep_date').val();
	
	var url = root_domain +'generate_export_finance?mode=invoice_list&rep_date=' + encodeURIComponent(rep_date);
	window.location.href = url;
}