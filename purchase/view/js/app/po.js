//var datatable;
$(document).ready(function () {
	/*$('#product_amount').hover(function(){
	   var pro_amt = $('#product_amount').val();
		$('#product_amount').attr("title",pro_amt);
	});*/

	if ($('#vender_id').val() != '' && $('#vender_id').val() != undefined) {
		get_vendor_contact_details($('#vender_id').val());
	}
	show_document_attach();
	var mode = $('#mode').val();
	if (mode == 'Edit') {
		var cust_id = $('#vender_id').val();
		var invoice_id = $('#eid').val();

		//customer effects
		currency_change();

		get_statecode(cust_id);
		get_grossbalance(cust_id);
		get_ledger_details(cust_id);
		get_all_bill_sundry(invoice_id);
		get_gtotal();

		//Popup Details
	}
	var viewmode = $('#viewmode').val();
	var prev_purchaseorder_id = $('#prev_purchaseorder_id').val();
	if (viewmode == 'Revise') {
		var cust_id = $('#vender_id').val();
		get_statecode(cust_id);
		get_grossbalance(cust_id);
		get_ledger_details(cust_id);
		get_all_bill_sundry(prev_purchaseorder_id);
		get_gtotal();
	}

	if (mode == 'Add' && $("#viewmode").val() == 'Add') {
		currency_rate_c();
	}
	// validate vendor add form on keyup and submit
	$("#purchaseorder_add").validate({
		rules: {
			vender_id: {
				required: true
			},
			purchaseorder_no: {
				required: true
			},
			purchaseorder_date: {
				required: true
			}
		},
		messages: {
			vender_id: {
				required: "Select Vendor"
			},
			purchaseorder_no: {
				required: "Enter P.O no"
			},
			purchaseorder_date: {
				required: "Enter P.O date"
			}
		}
	});
});
$("#purchaseorder_add").on('submit', function (e) {

	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#purchaseorder_add").valid()) {
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

	if (parseInt($('#total').val()) <= 0) {
		toastr.warning("AT LEAST ONE PRODUCT REQUIRE", "ERROR")
		return false;
	}

	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");

	var form_data = new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache: false,
		url: root_domain + purchase_domain + 'app/purchase_order/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData: false,
		success: function (response) {
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			if (arr.msg == '1') {
				Unloading();
				toastr.success("PURCHASE ORDER ADDED SUCCESSFULLY", "SUCCESS");
				window.location = root_domain + purchase_domain + arr.back;
			}
			else if (arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if (arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			else if (arr.msg == 'update') {
				toastr.success("PO UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location = root_domain + purchase_domain + 'po_list';

			}
			$('#purchaseorder_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});

function delete_po(id) {
	var r = confirm(" Are you want to delete ?");

	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "delete", eid: id },
			success: function (response) {
				//console.log(response)
				if (response.trim() == "1") {
					toastr.success("PO DELETE SUCCESSFULLY", "SUCCESS");
					load_po_datatable();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
		Unloading();
	}

}
function get_discount(type) {
	var rate_unit = $("#rate_unit_id").val();
	var base_unit = $("#unitid").val();
	if (rate_unit != base_unit) {
		var qty = parseFloat($('#product_conv_qty').val());
	} else {
		var qty = parseFloat($('#product_qty').val());
	}
	var rate = parseFloat($('#product_rate').val());
	var disc = 0;
	if (qty != "" && rate != "") {
		if (type == "amt") {
			disc = 100 * parseFloat($('#product_discount').val()) / (qty * rate);
			if(isNaN(disc)){
               disc = 0;
            }
			$('#discount_per').val(disc);
		}
		else if (type == "per") {
			disc = ((qty * rate) * parseFloat($('#discount_per').val())) / 100;
			if(isNaN(disc)){
               disc = 0;
            }
			$('#product_discount').val(disc);
		}
	}
	else {
		$('#product_discount').val('');
		$('#discount_per').val('');
	}
	get_amount();
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
	/*var rate = $("#currency_id").find(':selected').attr("data-currency-rate");*/
	var textt = " (" + symbl + ")";
	$(".currency_icon").each(function () {
		$(this).append(textt);
	});
	/*if($("#mode").val()=='Add' && $("#viewmode").val()=='Add'){
	   $('#currency_rate').val(rate);   
	}*/
}

function currency_rate_c() {
	var rate = $("#currency_id").find(':selected').attr("data-currency-rate");
	$('#currency_rate').val(rate);
}

function get_amount() {
	// pass the selection value in sel_tax field
	var ratcalfiled = $("#pro_cal_type").val();
	var formulaidSel = $("#formulaid option:selected").text();
	if (formulaidSel) {
		$("#sel_tax").val(formulaidSel);
	}
	var id = parseInt($('#fieldcnt').val()) + 1;
	if ($("#" + ratcalfiled).val() != "" && $("#product_rate").val() != "") {
		var q = parseFloat($("#" + ratcalfiled).val());
		var rate = parseFloat($("#product_rate").val());
		var a = q * rate;
		if ($("#product_discount").val() != "")//discount calculation
		{
			var discount = parseFloat($("#product_discount").val());
			a = a - discount;
		}
		

		$("#product_amount").val(parseFloat(a).toFixed(2));
		$("#taxable_value").val(parseFloat(a).toFixed(2));
		if ($("#formulaid").val() != "")//tax calculation
		{
			var total = a;
			var formulaid = $("#formulaid").val();
			$.ajax({
				type: "POST",
				url: root_domain + purchase_domain + 'app/purchase_order/',
				data: { mode: "getproduct_amount", product_amount: total, formulaid: formulaid },
				success: function (response) {
					//alert(response);
					var obj = jQuery.parseJSON(response);
					//alert(obj.tax_total);
					$('#product_amount').val(obj.total);
					$('#product_amount_tax').val(obj.tax_total_amount);
					$('#formula_tax_id').val(obj.tax_id);
				}
			});
		}
	}
	else {
		$("#product_amount").val(0);
	}
	get_gtotal();
}
function get_gtotal(id = "") {
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
	$("#paid_amount").val(g_total.toFixed(2));
	update_total();


}
/*function get_gtotal(eid)
{	
//alert(eid);
var id=parseInt($('#fieldcnt').val());
var t=0;
var p=Number($('#paking').val());
var d=Number($('#discount').val());
var r=Number($('#round_off').val());
//alert(r);

// Calculate Default  Total
var input_amount=(document.getElementsByName('amount[]'));
var cnt=input_amount.length;

if(cnt!=0)
{
	//alert(cnt);
	var total=0;var c_total=0;
	if(total=="")
	{
		total=0;
	}
	for(var i=0;i<cnt;i++)
	{	
		var t=input_amount[i].value;
		if(t>0)
			total=Number(total)+Number(t);
		//alert(t);
	}
	$("#total").val(Number(total).toFixed(2));
			// Dimple Panchal : start
			// if tax on total
			var formula =$("#formula_id").val();
			if(formula > 0)
			{
				get_tax_on_total(formula);
				tcs = $("#tcs_total").val();
				total = parseFloat(total) + parseFloat(tcs);
			} else {
				$('#tcs_total').val(0.00);
			}
			// Dimple Panchal : End
			if(p>0)
			{
				total=total+p;
			}
			if(d>0)
			{
				total=Number(total)-Number(d);
			}
			if(r!=0)
			{
				total=Number(total)+r;
			}
		}
		else
		{
			total=0;
		}
		$("#g_total").val(total.toFixed(2));

// Calculate Currency Total
var m = 0;
var currency_total=(document.getElementsByName('currency_total[]'));
var currency_cnt=currency_total.length;
	
if(currency_cnt!=0)
{
	//alert(cnt);
	var curency_total=0;
	if(curency_total=="")
	{
		curency_total=0;
	}
	for(var i=0;i<currency_cnt;i++)
	{	
		var m=currency_total[i].value;

		if(m>0){
			curency_total=Number(curency_total)+Number(m);
		}
		//alert(t);
	}

	$("#currency_total").val(Number(curency_total).toFixed(2));
}
else
{
	curency_total=0;
}
$("#currency_total").val(curency_total.toFixed(2));

if($('#currency_type_response').val()!=undefined){
	$currency_type_response = $('#currency_type_response').val();
	//alert($currency_type_response);
	if($currency_type_response){
		$('.currency_total_div').css({'display':'block'});
		$('.currency_type_name').html($currency_type_response);
	}
}

	
}*/
function vendor_price_modal() {
	var vender_id = $("#vender_id").val();
	if (vender_id == "") {
		toastr.warning("Select Vendor", "ERROR")
		$("#vender_id").select2('focus');
		return false;
	}
	$('#vn_id').val(vender_id);
	$('#vendor_price_list').modal('show');
	vender_detail();
	vender_price_detail();
}
function vendor_product_price_modal() {
	var vender_id = $("#vender_id").val();
	var product_id = $("#product_id").val();
	if (vender_id == "") {
		toastr.warning("Select Vendor", "ERROR")
		$("#vender_id").select2('focus');
		return false;
	}

	if (product_id == "") {
		toastr.warning("Select Product", "ERROR")
		$("#product_id").select2('focus');
		return false;
	}
	$('#vendor_product_price_list').modal('show');
	vender_detail1();
	vender_product_price_detail();
}
function vender_detail() {
	var vender_id = $("#vender_id").val();

	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "vender_detail", vender_id: vender_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#vendor_detail').html(resp.vender_detail);
			$('#vendor_name').html(resp.vender_name);
		}
	});
}
function vender_detail1() {
	var vender_id = $("#vender_id").val();
	var product_id = $("#product_id").val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "vender_detail", vender_id: vender_id, product_id: product_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#vendor_detail1').html(resp.vender_detail);
			$('#pr_name').html(resp.product_name);
		}
	});
}
function vender_price_detail() {
	var vender_id = $("#vender_id").val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "price_detail", vender_id: vender_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#product_detail').html(resp.product_detail);
		}
	});
}
function vender_product_price_detail() {
	var vender_id = $("#vender_id").val();
	var product_id = $("#product_id").val();

	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "product_price_detail", vender_id: vender_id, product_id: product_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#product_detail1').html(resp.product_detail);
		}
	});
}
function load_productdetail(val) {
	alert(val);
	if (val != 0) {
		$('#addproduct').hide();
	}
	else {
		$('#addproduct').show();
	}
	var vender_id = $('#vender_id').val();
	var currency_id = $('#currency_id').val();
	var conversion_rate = $('#conversion_rate').val();
	if (vender_id == '') {
		toastr.warning("Please Select Vender First", "ERROR");
		$('#vender_id').select2('focus');
		$('#product_id').select2('val', '');
		return false;
	}

	
	/* if(currency_id==''){
		toastr.warning("Please Select Currency","ERROR");
		$('#currency_id').select2('focus');
		return false;
	}
	if(conversion_rate==''){
		toastr.warning("Please Enter Conversion Rate","ERROR");
		$('#conversion_rate').select2('focus');
		return false;
	} */
	alert(vendor_id);
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_productdata", eid: val, vender_id: vender_id,  },
		success: function (response) {
			//console.log(response);
			
			var obj = jQuery.parseJSON(response)
			//$('#product_des').val(obj.product_desc);				
			$('#product_hsn_code').val(obj.product_hsn);
			CKEDITOR.instances['pro_des'].setData(obj.product_desc);
			CKEDITOR.instances['pro_spe'].setData(obj.product_spec);
			//$('#product_rate').val(obj.product_purchase_rate);				
			$('#product_rate').val(obj.prate);
			// $('#product_rate').val(obj.product_rate);
			//$('#unitid').val(obj.product_base_unit);
			//$('#unitid').select2("val",obj.product_base_unit);
			/*if(obj.com_stateid==obj.ven_stateid){
				$('#formulaid').val(obj.intra_tax);
			}else{
				$('#formulaid').val(obj.inter_tax);
			}*/
			load_product_tax(val, 'purchase');
			// alert(val);
			load_product_unit(val, obj.product_base_unit);
			// get_product_price(val);
		}
	});
}
function add_field() {

	var branch_id = $('#branch_id').val();

	/*if($("#product_type").val()=="")
	{
		toastr.warning("Select Product Type", "ERROR")
		$("#product_type").select2('focus');
		return false;
	}*/
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
	/* else if($("#currency_id").val()==="")
	{		
		toastr.warning("Select Currency", "ERROR")
		$("#currency_id").focus();
		return false;
	}
	else if($("#conversion_rate").val()==="")
	{		
		toastr.warning("Enter Conversion Rate", "ERROR")
		$("#conversion_rate").focus();
		return false;
	} */else if (branch_id === "") {
		toastr.warning("Select Branch", "ERROR")
		$("#branch_id").focus();
		return false;
	}
	else if ($("#vender_id").val() === "") {
		toastr.warning("Select Vendor", "ERROR")
		$("#vender_id").focus();
		return false;
	}
	else if ($("#direct_po_create").val() == 0) {
		if ($("#product_conv_qty").val() > $("#p_qty").val()) {
			toastr.warning("Please Less Qty " + $("#p_qty").val(), "ERROR")
			$("#product_conv_qty").focus();
			return false;
		}
	}
	if ($('#currency_enable').is(':checked')) {
		var currency_enable = 1;
	}
	else {
		var currency_enable = 0;
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
		mqty = parseFloat(mqty).toFixed(4);
		for (var i = 0; i < cnt; i++) {
			grandtotal_delivery_qty += parseFloat(total_delivery_qty[i].value);
		}
		var total = parseFloat(grandtotal_delivery_qty).toFixed(4);

		if (mqty != total) {
			toastr.warning("Delivery Qty Wrong", "ERROR")
			return false;
		}
	}

	var vendorID;
	/*if($("#product_rate").attr('data-type')=='1'){
	   vendorID = '';
	   var new_price = parseFloat($("#product_rate").val());
	   var discount = parseFloat($("#product_rate").data('discount'));
	   var tolerance = parseFloat($("#product_rate").data('tolerance'));

	   if(new_price >= tolerance || new_price <= discount){

		  $msg = "Please update your purchase card.";
		  toastr.warning($msg, "WARNING");
		  $($("#product_rate")).focus();
		  return false;
	   }
	}else if($("#product_rate").attr('data-type')=='0'){
		vendorID = $('#vender_id').val();
	} */

	var total_delivery_qty1_arr = [];
	var delivery_date_arr = [];
	var arry_edit_arry = [];
	//var total_delivery_qty1=document.getElementsByName('delivery_qty[]');
	var total_delivery_qty1 = $('input[name="delivery_qty[]"]').val();
	var arry_edit = $('input[name="arry_edit[]"]').val();

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
	//console.log(total_delivery_qty1_arr);
	//alert($("#formula_tax_id").val());
	var e = $("#edit_id").val();
	//alert(e);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: {
			mode: "fieldadd",
			total_delivery_qty: total_delivery_qty1_arr,
			delivery_date: delivery_date_arr,
			arry_edit: arry_edit_arry,
			cat_id: $("#cat_id").val(),
			product_hsn_code: $("#hsncode").text(),
			currency_id: $("#currency_id").val(),
			currency_rate: $("#currency_rate").val(),
			po_sub: $("#po_sub").val(),
			delivery_type: delivery_type,
			purchaseorder_due_date: $("#purchaseorder_due_date").val(),
			conversion_rate: $("#conversion_rate").val(),
			edit_id: $("#edit_id").val(),
			product_type: $("#product_type").val(),
			cust_id: $("#vender_id").val(),
			cust_stateid: $("#cust_stateid").val(),
			process_id: $("#process_id").val(),
			product_id: $("#product_id").val(),
			product_des: $("#product_des").val(),
			viewmode: $("#viewmode").val(),
			pro_des: $("#pro_des").val(),
			pro_spe: $("#pro_spe").val(),
			unit_wise: unit_wise,
			//product_hsn_code:$("#product_hsn_code").val(),
			product_qty: $("#product_qty_hide").val(),
			product_conv_qty: $("#product_conv_qty_hide").val(),
			unit_id: $("#unitid").val(),
			conv_unitid: $("#conv_unitid").val(),
			rate_unitid: $("#rate_unit_id").val(),

			sales_type: $("#sales_type").val(),
			product_rate: $("#product_rate").val(),
			product_disc: $("#product_disc").val(),
			currency_enable: currency_enable,
			formulaid: $("#formulaid").val(),
			product_discount: $("#product_discount").val(),
			purchasecardtrn_id: $("#product_rate").attr('data-pcardid'),
			discount_per: $("#discount_per").val(), product_amount: $("#product_amount").val(),
			purchaseorder_id: $("#eid").val(), formula_tax_id: $("#formula_tax_id").val(),
			taxable_value: $('#taxable_value').val(), sel_tax: $('#sel_tax').val(),
			product_amount_tax: $('#product_amount_tax').val(), vendor_id: vendorID,
			branch_id: branch_id
		},
		success: function (response) {
			var arr = jQuery.parseJSON(response);
			if (arr.msg == '1') {
				Unloading();
				toastr.success("PRODUCT ADDED SUCCESSFULLY", "SUCCESS");

			} else if (arr.msg == '0') {
				Unloading();
				toastr.warning("SOMETHING WENT WRONG", "WARNING");
			}


			//console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$('#bs-po_dispatch_date-modal').modal('hide');
			$("#product_id").select2("val", "");
			$("#cat_id").select2("val", "");

			$("#process_id").select2("val", "");
			$("#product_id").select2('focus');
			$("#rate_unit_id").val("");

			$("#product_des").val("");
			CKEDITOR.instances['pro_des'].setData("");
			CKEDITOR.instances['pro_spe'].setData("");
			$("#product_hsn_code").val("");
			$("#formulaid").val("");
			$("#discount_per").val("");
			$("#product_discount").val("");
			$("#product_qty").val("");
			$("#product_conv_qty").val("");

			$("#product_qty_hide").val("");
			$("#product_conv_qty_hide").val("");
			$("#conv_unitid").val("");
			$("#unitid").val("");

			$("#unit_show").html("");
			$("#convert_unit_show").html("");
			$("#product_rate").attr("data-pcardid", "");
			//$("#convert_unit_block").hide();
			$("#product_rate").val('');
			$("#product_disc").val('');
			$("#taxable_value").val('');
			$("#product_amount").val('');
			$("#edit_id").val('');
			$("#sel_tax").val('');
			$("#formula_tax_id").val('');
			$("#product_amount_tax").val('');
			$("#p_qty").val('');
			$('#addproduct').show();
			$(".hsncode").hide();
			$('#addrow').val('Add');

			if ($("#direct_po_create").val() == 0) {
				$("#addrow").css("visibility", "hidden");
				$("#product_id").attr("disabled", "disabled");
			} else {
				$("#addrow").css("visibility", "visible");
				$("#product_id").removeAttr("disabled", "disabled");
			}
			Unloading();

			show_data();
			get_tax_details_table();
			get_invoice_total_tax();

		}
	});
}
function add_branch_stock_1() {
	Loading();
	var bstock_arr = [];
	var bid_arr = [];
	var bpriority_arr = [];

	var bstock = $('input[name="bstock[]"]').val();
	//var bid = $('input[name="delivery_date[]"]').val();

	i = 0;
	$('input.bstock').each(function () {
		bstock_arr[i++] = $(this).val();
	});

	/* j = 0;
	$('input.bid').each(function(){ 
		  bid_arr[j++]=$(this).val();
	});
	*/
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "add_branch_stock", bstock: bstock_arr },
		success: function (response) {
			//$("#product_opening").val(response);
			Unloading();
		}
	});
}
function field_remove(id) {
	$("#fieldtr" + id).html('');
	var t = get_amount();
}
function reload_data() {
	//datatable.fnReloadAjax();
	load_po_datatable();
}
function load_po_datatable() {
	var po_type_status = $('input[name=po_type_status]:Checked').val();
	var date = $('#rep_date').val();
	/*alert(date);*/
	var branch_id = $('#branch_id').val();
	var vender_id = $('#vender_id').val();
	var filt_status = $('#filt_status').val();
	var short_status = $('#short_status').val();

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

		"aoColumnDefs": [{ "bSearchable": false, "bVisible": false, "aTargets": [6] },
		{ "bVisible": false, "aTargets": [6] },
		{ "bVisible": false, "aTargets": [6] },
		{ "bSearchable": false, "bVisible": false, "aTargets": [7] },
		{ "bVisible": false, "aTargets": [7] },
		{ "bVisible": false, "aTargets": [7] }
		],

		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + purchase_domain + 'app/purchase_order/',
		"fnServerParams": function (aoData) {
			aoData.push(
				{ "name": "mode", "value": "fetch" },
				{ "name": "po_type_status", "value": po_type_status },
				{ "name": "date", "value": date },
				{ "name": "branch_id", "value": branch_id },
				{ "name": "vender_id", "value": vender_id },
				// { "name": "short_status", "value": short_status },
				{ "name": "filt_status", "value": filt_status }
			);
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		},
		"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
			var iPageMarket = 0; var iPageMarkets = 0; var iPageMarketses = 0;
			for (var i = 0; i < aaData.length; i++) {
				iPageMarket += aaData[i][5] * 1;
				iPageMarkets = aaData[i][6] * 1;
				iPageMarketses = aaData[i][7] * 1;
			}

			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = parseFloat(iPageMarket).toFixed(2);
			$("#total_purchase").html('Rs. ' + parseFloat(iPageMarkets).toFixed(2));
			$("#taxable_amt").html('Rs. ' + parseFloat(iPageMarketses).toFixed(2));
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');
}

function edit_data(id, table, whereid) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "preedit", id: id, table: table, whereid: whereid },
		success: function (response) {
			//console.log(response);
			//console.log(response)
			//alert(response);
			var data = jQuery.parseJSON(response);
			var curr = '<?php echo $_SESSION["currency_id"];?>';
			var currency_id = $('#currency_id').val();
			job_work_process(data.product_id, data.process_id);
			$('#product_id').html(data.producthtml);
			$("#product_id").select2('data', { id: data.product_id, text: data.product_name });
			$("#process_id").select2('data', { id: data.process_id, text: data.process_name });
			if ($("#direct_po_create").val() == 0) {
				$("#product_id").attr("disabled", "disabled");
			}
			//$("#process_id").select2("val",data.process_id)
			//$('#product_id').append('<option value="'+data.product_id+'">'+data.product_name+'</option>');
			$("#product_type").select2("val", data.product_type)
			$("#cat_id").select2("val", data.cat_id)
			//$("#product_id").select2("val",data.product_id)
			$("#product_des").val(data.description)
			//$("#product_hsn_code").val(data.product_hsn_code)
			$('#hsncode').text(data.product_hsn_code);
			$(".hsncode").show();

			$("#p_qty").val(data.pending_qty)
			$("#product_qty").val(data.product_qty_show)
			$("#product_qty_hide").val(data.product_qty)
			$("#product_conv_qty_hide").val(data.product_conv_qty)
			$("#product_conv_qty").val(data.product_conv_qty_show)

			$("#unitid").val(data.unit_id)
			$("#conv_unitid").val(data.conv_unit_id)
			$("#unit_wise").val(data.unit_wise);
			CKEDITOR.instances['pro_des'].setData(data.product_des);
			CKEDITOR.instances['pro_spe'].setData(data.pro_spe);
			//$("#sqr_ft").val(data.sqr_ft)
			//$("#product_rate").val(data.product_rate)
			$("#product_rate").attr("data-pcardid", data.purchasecardtrn_id)
			if (currency_id == curr) {
				$("#product_rate").val(data.product_rate)
				$("#product_amount").val(data.total)
				$("#product_discount").val(data.product_discount)
				$("#taxable_value").val(data.product_amount)
				$("#product_amount_tax").val(data.product_amount_tax)
			} else {
				$("#product_rate").val(data.product_currency_rate)
				$("#product_amount").val(data.currency_total)
				$("#product_discount").val(data.product_discount_conv)
				$("#taxable_value").val(data.product_currency_amount)
				$("#product_amount_tax").val(data.product_currency_amount_tax)
			}
			$("#product_disc").val(data.product_disc)
			//$("#unitid").select2("val",data.unit_id)
			$("#formulaid").val(data.formulaid)
			//$("#product_amount").val(data.total)
			//$("#product_discount").val(data.product_discount)
			$("#discount_per").val(data.discount_per)
			//$("#taxable_value").val(data.product_amount)
			$("#sel_tax").val(data.sel_tax)
			$("#formula_tax_id").val(data.formula_tax_id)
			//$("#product_amount_tax").val(data.product_amount_tax)
			$("#edit_id").val(id)
			$('#addrow').val('Update');
			$("#addrow").css("visibility", "visible");
			load_product_unit(data.product_id, data.rate_unit);

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
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "delete_data", eid: id, table: table, whereid: whereid, purchaseorder_id: $("#eid").val() },
			success: function (response) {
				//console.log(response)
				var data = jQuery.parseJSON(response)
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data()

					Unloading();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}

}

/*function get_series_no(type_id){

	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_order/',
		data: { mode : "get_series_no", type_id:type_id},
		success: function(resp){
			//console.log(resp);
			$('#invoicetype_id').val(resp);	
			load_pono(resp)	
		}		
	});	
}*/
function load_pono(id) {
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_invoiceno", typeid: id },
		success: function (data) {
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#purchaseorder_no').val(no.invoiceno);

		}
	});
}

function load_product_po(type_id) {
	var vender_id = $('#vender_id').val();
	//alert(vender_id);
	//Loading();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_product", type_id: type_id, vender_id: vender_id },
		success: function (data) {
			//console.log(data);
			$('#product_id').html(data);
			//Unloading();
		}
	});
}
function entry_po_req_data(purchaseorder_id) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "entry_po_req_data", purchaseorder_id: purchaseorder_id },
		success: function (data) {
			//console.log(data);
			show_data();
			Unloading();
		}
	});
}

function cancel_po_status(id, po_status) {
	var r = confirm(" Are you want to Change PO Status ?");

	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "cancel_po_status", eid: id, po_status: po_status },
			success: function (response) {
				//console.log(response);
				var resp = JSON.parse(response);
				var response = resp.res;
				if (response.trim() == "1") {
					toastr.success("PO STATUS CHANGED SUCCESSFULLY", "SUCCESS");
					load_po_datatable();
					Unloading();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}

}
function load_consignee(cust_id) {
	var product_id = $('#product_id').val();
	if (product_id) {
		load_productdetail(product_id);
		load_product_tax(product_id, 'purchase')
	}

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/invoice/',
		data: { mode: "load_consignee", cust_id: cust_id },
		success: function (data) {
			//console.log(data);
			$('#consignee_id').html(data);
			$('#consignee_id').select2('val', '');
			Unloading();
		}
	});
}
/*
Code By Umair: 
Comment: Below code is change status at a time
*/


/*function change_po_approval_status(id, po_approval_status,order_no) 
{
	var r= confirm(" Are you want to Change PO Approval Status ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+purchase_domain+'app/purchase_order/',
				data: { mode : "change_po_approval_status", eid:id, po_approval_status:po_approval_status },
				success: function(response)
				{
					//console.log(response);
					var resp = JSON.parse(response);
					var response = resp.res;
					if(response.trim() == "1") {
						toastr.success("PO APPROVAL STATUS CHANGED SUCCESSFULLY", "SUCCESS");
						load_po_datatable();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
	}*/

function change_po_approval_status(id, po_approval_status, order_no) {
	$('#preview_po_approval_hist_modal').modal('show');
	$('#apprv_po_ref_no').html(order_no);
	$('#ref_ord_id').val(id);
	$('#eid').val(id);
	load_purchase_hist_datatable();
	load_party_po_dtl();
	load_party_pro_dtl();
	show_document_attach();
}
function load_party_po_dtl() {
	var purchase_order_id = $('#ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_party_purchase_dtl", purchase_order_id: purchase_order_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#mod_po_comp_div_sec').html(resp.mod_po_comp_div_sec);
		}
	});
}
function load_party_pro_dtl() {
	var purchase_order_id = $('#ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_pro_purchase_dtl", purchase_order_id: purchase_order_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#mod_po_pro_div_sec').html(resp.mod_po_pro_div_sec);
		}
	});
}
function load_purchase_hist_datatable() {
	var purchase_order_id = $('#ref_ord_id').val();

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
		"sAjaxSource": root_domain + purchase_domain + 'app/purchase_order/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "load_purchase_hist_datatable" }, { "name": "purchase_order_id", "value": purchase_order_id });
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
function add_po_apprv_hist() {

	var form_data = {
		mode: "add_po_apprv_hist",
		approve_status: $('#po_approve_status').val(),
		approve_remark: $('#po_approve_remark').val(),
		purchase_order_id: $('#ref_ord_id').val()
	};

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: form_data,
		success: function (response) {
			$('#preview_po_approval_hist_modal').modal('hide');
			$('#po_approve_status').select2("val", "2");
			$('#po_approve_remark').val("");
			load_purchase_hist_datatable();
			//load_order_confirm_datatable();
			load_po_datatable();
			Unloading();
		}
	});
}
function load_product_tax(pid, tran_type) {
	//alert(pid);
	Loading();

	var vendor = $('#vender_id').val();

	if (vendor != '') {

		$.ajax({
			type: "POST",
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "load_product_tax", pid: pid, tran_type: tran_type, vendor: vendor },
			success: function (response) {
				//alert(response);
				//console.log(response);
				var resp = JSON.parse(response);

				//$('#sel_tax').val(resp.name);
				//$('#formulaid').val(resp.id);
				//$('#formula_tax_id').val(resp.tax_id);

				Unloading();
			}
		});

	}
	Unloading();
}

function show_data() {
	//Loading();
	var eid = $('#eid').val();
	var po_type = $('#po_type').val();
	var viewmode = $('#viewmode').val();
	var delivery_type = $("#delivery_type").val();
	//alert(eid);
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_tempoutward", eid: eid, po_type: po_type, viewmode: viewmode, delivery_type: delivery_type },
		success: function (data) {
			//console.log(data);
			$('#sale_productdata').html(data);
			get_amount()
			//Unloading();
			get_tax_details_table();
			get_invoice_total_tax();
			// get_symbol();
		}

	});

}

function delivery_detail(po_trn_id) {
	$('#delivery_detail').modal('show');
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "delivery_detail", po_trn_id: po_trn_id },
		success: function (response) {
			//console.log(response)
			var data = jQuery.parseJSON(response);
			$('#pr_na').html(data.pro_name);
			$('#delivery_schedule').html(data.delivery_schedule);
		}
	});
}

function get_po_tax(cust_id) {
	//alert(cust_id);
	var eid = $('#eid').val();
	$('.nav-tabs a[href="#po_items"]').tab('show');
	//alert(eid);
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "get_po_tax", cust_id: cust_id, eid: eid },
		success: function (data) {

			//alert(data);
			//console.log(data);
			//$('#sale_productdata').html(data);				
			//get_amount()
			// gen vendor details

			get_vendor_contact_details(cust_id);
			show_data();
			Unloading();
		}

	});
}

function get_vendor_contact_details(cust_id) {
	// body...
	Loading()
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "get_vendor_contact_details", cust_id: cust_id },
		success: function (data) {
			var vendor = JSON.parse(data);
			$('#vendor_email').val(vendor.cust_email);
			$('#vendor_mobile').val(vendor.cust_mobile);
			Unloading();
		}

	});
}


function get_vendor_details(tab) {
	var vendor_id = $('#vender_id').val();
	var mode = "get_" + tab;
	var eid = $('#eid').val();

	//alert("dsa");
	if (vendor_id) {
		if (tab != "des") {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain + purchase_domain + 'app/purchase_order/',
				data: { mode: mode, vendor_id: vendor_id, eid: eid },
				success: function (data) {
					//console.log(data);
					$('#' + tab).html(data);
					//get_amount()
					show_data();
					Unloading();
				}
			});
		} else {
			$(".tab-pane").removeClass("active");
			$("#des").addClass("active");
		}
	} else {
		$msg = "Please Select Vendor First.";
		toastr.warning($msg, "WARNING");
		$('#' + tab).html($msg);
	}
}

function get_vendor_name(vid) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: 'set_vendor_sesion', vendor_id: vid },
		success: function (data) {
			window.location.href = root_domain + purchase_domain + 'po';
			Unloading();
		}
	});
}

function get_product_price(product_id = "") {
	if (product_id == "") {
		product_id = $("#product_id").val();
	}
	//alert(product_id);
	var vender_id = $("#vender_id").val();
	var unit_id = $("#rate_unit_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_rate", product_id: product_id, vender_id: vender_id, unit_id: unit_id },
		success: function (response) {
			var resp = jQuery.parseJSON(response);

			$('#product_rate').val(resp.rate);
			$('#product_rate').attr('data-pcard', resp.rate);
			$('#product_rate').attr('data-pcardid', resp.purchasecardtrn_id);
			$('#discount_per').val(resp.discount_percentage);
			get_discount('per');
			Unloading();
		}
	});
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
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "get_tax_on_total", total: total, formulaid: formulaid },
			success: function (response) {
				var obj = jQuery.parseJSON(response);
				$('#tcs_total').val(obj.tax_value);
			}
		});
	}
}
// Dimple Panchal : end
//pathik start
// function load_product_unit(product_id,unit_id){
// 	if(product_id)//tax calculation on total 
// 	{
// 		$.ajax({
// 			type: "POST",
// 			async: false,
// 			url: root_domain+purchase_domain+'app/purchase_order/',
// 			data: { mode : "load_product_unit", product_id : product_id},
// 			success: function(response)
// 			{
// 				var obj=jQuery.parseJSON(response);
// 					//alert(obj.qye);
// 					$('#unitid').val(obj.product_base_unit);
// 					$('#conv_unitid').val(obj.product_conv_unit);

// 					$('#unit_show').html(obj.base_unit_name);
// 					$('#convert_unit_show').html(obj.convert_unit_name);
// 					$("#convert_unit_block").show();
// 					if(obj.unit_status==="1"){
// 						$("#convert_unit_block").show();
// 					}else{
// 						$("#convert_unit_block").hide();
// 					}
// 				}
// 			});
// 	}
// }
// Maulik Start
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
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "load_product_unit", product_id: product_id },
			success: function (response) {
				//console.log(response);
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
						$("#pro_cal_type").val("product_qty");
					} else {
						if (obj.product_base_unit != obj.product_conv_unit) {
							$("#base_unit_block").show();
							$("#product_qty").attr("readonly", "readonly");
							$("#product_conv_qty").removeAttr("readonly", "readonly");
							$("#convert_unit_block").show();
						} else {
							$("#base_unit_block").hide();
						}
						$("#pro_cal_type").val("product_conv_qty");
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
//Maulik End
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
			url: root_domain + purchase_domain + 'app/purchase_order/',
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
function open_approv_quo1() {
	/*if($("#product_type").val()=="")
	{
		toastr.warning("Select Product Type", "ERROR")
		$("#product_type").select2('focus');
		return false;
	}*/
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
	var product_name = $("#product_id").select2('data').text;
	$("#model_product_name").html(product_name + " --- " + qty + " " + unit_show);
	$("#m_trn_id").val(trn_id);
	$("#m_qty").val(qty);

	//alert();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "delivary_date_model_open", qty: qty, trn_id: trn_id },
		success: function (response) {
			$('#bs-po_dispatch_date-modal').modal('show');
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
	main_qty = parseFloat(main_qty).toFixed(4);
	var qval = "0";
	for (var i = 0; i < cnt; i++) {
		grandtotal_delivery_qty += parseFloat(total_delivery_qty[i].value);
		var grandtotal_delivery_qty_new = grandtotal_delivery_qty;
		grandtotal_delivery_qty_new = parseFloat(grandtotal_delivery_qty_new).toFixed(4);
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
	var total = parseFloat(grandtotal_delivery_qty).toFixed(4);

	if (parseFloat(total) > parseFloat(main_qty)) {
		$("#m_addrow").hide();
	} else {
		if (parseFloat(total) < parseFloat(main_qty)) {
			$("#m_addrow").hide();
			count = parseFloat(count) + parseFloat(1);
			$('#count').val(count);
			var pending_qty = parseFloat(main_qty) - parseFloat(total);

			$("#mix_loose_material_table").append('<tr id="field' + count + '"><td class="text-center" style="vertical-align:center;"><input type="text" class="form-control default-date-picker delivery_date" id="delivery_date' + count + '" name="delivery_date[]" placeholder="Delivery Date" onchange="qty_wise_date_validation(' + count + ');" ></td><td class="text-center;" style="vertical-align:center;"><input type="text" class="form-control delivery_qty" id="delivery_qty' + count + '" name="delivery_qty[]" onchange="validate_dilivary_date();" placeholder="' + pending_qty + '" onkeyup="qty_wise_date_validation(' + count + ');" /></td><td class="text-center" style="vertical-align:center;" ><button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date(' + count + ');" id="fieldremove' + count + '"><i class="fa fa-times"></i></button><input type="hidden" name="arry_sr[]" id="arry_sr" value="' + count + '" /></td></tr>')

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
function delivery_type_permission() {
	var delivery_type = $("#delivery_type").val();
	if (delivery_type === "po_wise") {
		$(".delivary_product_wise").hide();
		$(".delivary_po_wise").show();
	} else {
		$(".delivary_product_wise").show();
		$(".delivary_po_wise").hide();
	}
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
function send_purchase_order(purchaseorder_id) {
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "send_purchase_order", purchaseorder_id: purchaseorder_id },
		success: function (response) {
			console.log(response);
			// toastr.success("QUOTATION SEND SUCCESSFULLY", "SUCCESS");
			// var data=jQuery.parseJSON(response);
			// var response=data.res;
			if (response.status == "success") {
				toastr.success("PURCHASE ORDER SEND SUCCESSFULLY", "SUCCESS");
			} else {
				toastr.warning("NUMBER IS INVALID / SOMETHING WENT WRONG", "ERROR");
			}
		}
	});
}
//pathik end
function shortclosepo(id, order_no) {
	var r = confirm(" Are you want to full po short close ?");
	$('#ref_pord_id').val(id);
	$('#ref_po_ref_id').val(id);
	if (r) {
		$('#full_po_shortclose_reason').modal('show');
		$('#shortclose_pofull_ref_no').html(order_no);
		load_party_po_detail();
		po_close_reason();
	} else {
		$('#manual_po_shortclose_reason').modal('show');
		$('#shortclose_poman_ref_no').html(order_no);
		load_trn_tbl();
		load_party_po_det();
		m_po_close_reason();
	}
}
function load_party_po_detail() {
	var purchase_order_id = $('#ref_pord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_party_purchase_dtl", purchase_order_id: purchase_order_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#po_company_detail').html(resp.mod_po_comp_div_sec);
		}
	});
}
function load_party_po_det() {
	var purchase_order_id = $('#ref_po_ref_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_party_purchase_dtl", purchase_order_id: purchase_order_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#po_comp_detail').html(resp.mod_po_comp_div_sec);
		}
	});
}
function load_trn_tbl() {
	po_id = $('#ref_po_ref_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "po_trn_tbl", po_id: po_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#po_trn_tbl').html(resp.po_trn_tbl);
		}
	});
}
function add_full_poshort_close() {
	var branch_id = $('#branch_id').val();
	po_id = $('#ref_po_ref_id').val();
	close_reson = $('#po_close_reson').val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "full_poshort_close", po_id: po_id, close_reson: close_reson, branch_id: branch_id },
		success: function (resp) {
			$('#po_close_reson').val('');
			window.location.href = root_domain + purchase_domain + 'po_list';
		}
	});
}
function add_manualpo_short_close() {
	var branch_id = $('#branch_id').val();
	po_id = $('#ref_po_ref_id').val();
	close_reson = $('#m_close_remark').val();
	var po_trn_id = $("input[name='po_trn_id[]']:Checked").map(function () { return $(this).val(); }).get();
	if (po_trn_id == "") {
		toastr.warning("Please Select Product", "ERROR")
		return false;
	} else {
		$.ajax({
			type: "POST",
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "manual_poshort_close", po_id: po_id, close_reson: close_reson, po_trn_id: po_trn_id, branch_id: branch_id },
			success: function (resp) {
				//console.log(resp);
				$('#m_close_remark').val('');
				window.location.href = root_domain + purchase_domain + 'po_list';
			}
		});
	}
}
function po_close_reason() {
	po_id = $('#ref_po_ref_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "po_close_reason", po_id: po_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#m_close_remark').val('');
			$('#f_po_close_reason').html(resp.f_po_close_reason);
		}
	});
}
function m_po_close_reason() {
	po_id = $('#ref_po_ref_id').val();

	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "m_po_close_reason", po_id: po_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#m_po_close_reason').html(resp.m_po_close_reason);
		}
	});
}
function product_load(po_type = '') {
	var testData = [];
	var inquiry_type = $("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain + crm_domain + 'app/product_load/index.php?mode=product_load&inquiry_type=' + inquiry_type + '&type=indent_po_pro_type&search=purchase_pro_search&po_type=' + po_type;
	$.getJSON(mainurl, function (json) {
		var arr = new Array();
		var len = json[0].length;
		//console.log(json);

		for (var i = 0; i < len; i++) {
			testData.push({ id: json['0'][i], text: json['1'][i] });
			//alert(json['1'][i]);
		}
	});

	return testData;
}
function load_products($po_type = '') {
	$('#product_id').select2({
		data: product_load($po_type),
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
function copy_prev_purchase_trn(prev_purchaseorder_id) {
	if (prev_purchaseorder_id) {
		$.ajax({
			type: "POST",
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "copy_prev_purchase_trn", prev_purchaseorder_id: prev_purchaseorder_id },
			success: function (response) {
				//console.log(response);
				show_data();
			}
		});
	}
}
function get_revise_po_no(purchaseorder_id, start_purchaseorder_id) {
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "get_revise_po_no", purchaseorder_id: purchaseorder_id, start_purchaseorder_id: start_purchaseorder_id },
		success: function (data) {
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#purchaseorder_no').val(no.purchaseorder_no);

		}
	});
}

function po_type_product_load(po_type) {
	product_load(po_type);
	load_products(po_type);
}
function job_work_process(prod_id = '', proc = '') {
		alert(po_type);

	po_type = $('#po_type').val();
	if (po_type == 2) {
		//$('#process_id').select2('display','block');
		$('#process_id').removeClass('hidden');
		$('#job_proc').removeClass('hidden');
		$('#job_proc1').removeClass('hidden');
		$.ajax({
			type: "POST",
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "load_process_out_side", prod_id: prod_id, proc: proc },
			success: function (resp) {
				//console.log(resp);
				var resp = JSON.parse(resp);
				$('#process_id').html(resp.process_list);
			}
		});
	} else {
		$('#process_id').addClass('hidden');
		$('#job_proc1').addClass('hidden');
		$('#job_proc').addClass('hidden');
	}
}
function get_statecode(cust_id) {
	if (cust_id) {
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + purchase_domain + 'app/purchase_order/',
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
function get_hsn(product_id) {
	// alert(product_id);
	alert(product_id);
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "get_hsn_code", product_id: product_id },
		success: function (response) {
			if (response != '') {
				$('#hsncode').text(response);
				$(".hsncode").show();
			} else {
				toastr.warning("Please select valid HSN code product", "WARNING");
				$(".hsncode").hide();
				$('#product_id').select2("val", "");
				return false;
			}
		}
	});
}
function get_tax_details_table() {

	var eid = $('#eid').val();
	var cust_id = $('#vender_id').val();
	var viewmode = $('#viewmode').val();
	var addontax1 = [];
	var currency_id = $('#currency_id').val();
	$(".addontax").each(function () {
		//alert(this.value);
		addontax1.push(this.value);
	});
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "get_tax_details_table", invoice_id: eid, cust_id: cust_id, addontax1: addontax1, viewmode: viewmode, currency_id: currency_id },
		success: function (response) {

			var arr = JSON.parse(response);
			if (arr) {
				$(".tax_details").html(arr.resp);
				//$(".gross").text(response);
				get_symbol();
			}
		}
	});
}

function get_grossbalance(cust_id) {
	if (cust_id) {
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + purchase_domain + 'app/purchase_order/',
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
	}
}

function get_invoice_total_tax() {

	var eid = $('#eid').val();
	var addontax1 = 0;
	var viewmode = $('#viewmode').val();
	$(".addontax").each(function () {
		var addontax = (this.value).split("-");
		addontax1 = Number(addontax1) + Number(addontax[0]);
	});
	var currency_id = $("#currency_id").val();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "get_invoice_total_tax", cust_id: $('#vender_id').val(), gross: $('.gross').text(), inv_total: $('#total').val(), invoice_id: eid, addontax1: addontax1, viewmode: viewmode, currency_id: currency_id },
		success: function (response) {
			//console.log(response);
			var arr = JSON.parse(response);
			if (arr) {
				$(".invoiceTotalTax").html(arr.resp);
				if (arr.isTcs == 1) {
					$('.tcs_details').show();
				} else {
					$('.tcs_details').hide();
				}
				get_symbol();
				//$(".gross").text(response);
			}
		}
	});
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
	//var i=1;
	$.each(values, function(key, value) {	
		var new_key = this.name.match(/\d+/);
		gst[new_key] = $(this).val();
		//i++;
	});*/
	//alert(i);

	var gst1 = [];
	var gst2 = [];
	var addonsundry = {};

	var values = $("input.gst");
	$.each(values, function (key, value) {
		var new_key = this.name.match(/\d+/);
		gst1.push(new_key[0]);
		gst2.push($(this).val());
	});

	$.ajax({

		type: 'POST',
		data: { mode: 'update_total', invoice_id: eid, g_total: g_total, basic_total: basic_total, branch_id: branch_id, currency_id: currency_id, currency_rate: currency_rate, bill_sundry_tax: gst1, bill_sundry_tax1: gst2 },
		url: root_domain + purchase_domain + 'app/purchase_order/',
		success: function (result) {
			//console.log(result);
			//alert(result);
		}

	})
}
function get_ledger_details(ledger_id) {
	var company_cost_center = $('#company_cost_center').val();
	var company_tcs = $('#company_tcs').val();
	var company_eway = $('#company_eway').val();
	var company_salesman = $('#company_salesman').val();
	var kind_attn_hidden = $('#kind_attn_hidden').val();


	$.ajax({

		type: 'POST',
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "get_ledger_details", ledger_id: ledger_id },
		success: function (result) {
			var obj = JSON.parse(result);
			//Cost Center popup
			if (obj.enable_cost_center == 1 && company_cost_center == 1) {
				$('#div_cost_center').show();
			}
			$("#kind_attn").html(obj.c_person);
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

		}
	})
}
var rowIdx = 0;
//added by maulik Kapatel
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

	if (bill_sundry_value == 0) {
		toastr.warning("Please Select Bill Sundry", "ERROR")
		Unloading();
		return false;
	} else if (bill_sundry_amount == '') {
		Unloading();
		toastr.warning("Please insert Bill Sundry Amount", "ERROR")
		return false;
	} else {
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: {
				mode: "get_bill_sundry_details", sundry_ledger_id: bill_sundry_value, totalsundryexist: totalsundryexist, taxableamount: taxableamount,
				basic_amount: basic_amount, netamount: netamount, default_amount: bill_sundry_amount, invoice_id: eid, currency_enable: currency_enable, currency_id: currency_id, currency_rate: currency_rate, invoice_date: $('#purchaseorder_date').val()
			},
			success: function (response) {

				var arr1 = JSON.parse(response);
				var arr = arr1.split(",");

				if (arr[3]) {
					get_all_bill_sundry(eid);
					//get_gtotal();
				}
				else {
					if (arr[0]) {

						//$("#g_total").val(arr[0]);
						//alert(arr[4]);
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

							get_invoice_total_tax();
							get_tax_details_table();
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
				//alert(arr);
				/*if(arr[4])
				{
					get_all_bill_sundry(edit_id);
					//get_gtotal();
				}
				else
				{
					if(arr[0]){

						//$("#g_total").val(arr[0]);
						
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
						
						get_gtotal();
					}
				}*/
				get_symbol();
			}
		});


	}

	Unloading();
}
function get_all_bill_sundry(invoice_id) {

	$.ajax({

		type: 'POST',
		url: root_domain + purchase_domain + 'app/purchase_order/',
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

	Loading(true);

	var edit_id = $('#eid').val();

	//alert(ledger_id);

	if (edit_id == '' || edit_id == '0') {
		var netamount = $("#g_total").val();

		var finalNetAmount = Number(netamount) - Number(bill_sundry_amount);

		$("#g_total").val(finalNetAmount);

		$('.' + id).remove();
	}
	else {

		$.ajax({

			type: 'post',
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: 'remove_sundry', edit_id: edit_id, ledger_id: ledger_id },
			success: function (result) {
				get_all_bill_sundry(edit_id);
				get_gtotal();
			}
		})
	}

	Unloading();
}
function get_sundry_label(sundry_id) {
	//alert(sundry_id);

	$.ajax({

		type: 'POST',
		url: root_domain + purchase_domain + 'app/purchase_order/',
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
function change_po_finance_approval_status(id, po_approval_status, order_no) {
	$('#preview_po_finance_approval_hist_modal').modal('show');
	$('#fin_apprv_po_ref_no').html(order_no);
	$('#fin_ref_ord_id').val(id);
	load_finance_purchase_hist_datatable();
	load_finance_party_po_dtl();
	load_finance_pro_po_dtl();
	show_document_fin_attach();
}
function load_finance_purchase_hist_datatable() {
	var purchase_order_id = $('#fin_ref_ord_id').val();

	$("#order-finance-po-history-datatable").dataTable({
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
		"sAjaxSource": root_domain + purchase_domain + 'app/po_approoval_finance/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "load_purchase_hist_datatable" }, { "name": "purchase_order_id", "value": purchase_order_id });
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
function add_finance_po_apprv_hist() {

	var form_data = {
		mode: "add_po_apprv_hist",
		approve_status: $('#finance_po_approve_status').val(),
		approve_remark: $('#finance_po_approve_remark').val(),
		purchase_order_id: $('#fin_ref_ord_id').val()
	};

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/po_approoval_finance/',
		data: form_data,
		success: function (response) {
			$('#preview_po_finance_approval_hist_modal').modal('hide');
			$('#finance_po_approve_status').select2("val", "4");
			$('#finance_po_approve_remark').val("");
			load_finance_purchase_hist_datatable();
			load_po_datatable();
			Unloading();
		}
	});
}
function load_finance_party_po_dtl() {
	var purchase_order_id = $('#fin_ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_party_purchase_dtl", purchase_order_id: purchase_order_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#mod_fin_po_comp_div_sec').html(resp.mod_po_comp_div_sec);
		}
	});
}
function load_finance_pro_po_dtl() {
	var purchase_order_id = $('#fin_ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_pro_purchase_dtl", purchase_order_id: purchase_order_id },
		success: function (resp) {
			//console.log(resp);
			var resp = JSON.parse(resp);
			$('#mod_fin_po_pro_div_sec').html(resp.mod_po_pro_div_sec);
		}
	});
}
function load_typeswise_terms(purchaseorder_id) {
	var quot_type = $('input[name="quot_type"]:checked').val();
	var terms_type = $("input[name='terms_type']:checked").val();
	// var cust_id = $("#vender_id").val();
	 var cust_id    = $("#cust_id").val();
	/*var quotation_id = $("#eid").val();*/

	if (purchaseorder_id > 0) {
		if ($("#vender_id").val() == "") {
			toastr.warning("Choose Vendor", "ERROR")
			$("#vender_id").focus();
			return false;
		}
	}

	if($("#cust_id").val()==""){
               toastr.warning("Choose Customer", "ERROR")
               $("#cust_id").focus();
              return false;
        }

	if (quot_type || quot_type == 0) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "load_typeswise_terms", quot_type: quot_type, terms_type: terms_type, cust_id: cust_id, purchaseorder_id: purchaseorder_id },
			success: function (response) {
				var resp = JSON.parse(response);
				$('#po_terms_cond_div').html(resp.resp_html);
				Unloading();
			}
		});
	}
}
function terms_check_all(obj) {
	$('.terms_checkbox').prop('checked', obj.checked);
}
function load_paymentterms_vendor_wise() {
	var vendor_id = $("#vender_id").val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_payment_terms", vendor_id: vendor_id },
		success: function (response) {
			var resp = JSON.parse(response);
			$('#payment_terms').html(resp.resp_html);
			Unloading();
		}
	});
}
function load_transportation_vendor_wise() {
	var vendor_id = $("#vender_id").val();
	var trans_id = $("#trnsp_id").val();
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "load_transportation", vendor_id: vendor_id, trans_id: trans_id },
		success: function (response) {
			var resp = JSON.parse(response);
			$('#dispatch_doc_no').html(resp.resp_html);
			Unloading();
		}
	});
}
function showproduct() {

	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-product').modal('show');

	$('#product_type').select2("val", 0);
	$("#product_type").trigger('change');
	$("#product_add_type").val('purchase_order');
}

function add_hsn_invoice() {
	//$.fn.modal.Constructor.prototype.enforceFocus = function() {};
	$('#modal-add-hsn').modal('show');
	$("#hsn_add_type").val('product_purchase');
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
	get_opening_balance('0');
	$("#ledger_add_type").val('purchase');
	$("#ledger_name").focus();
}
function direct_po_create_no() {
	//alert(1);
	if ($("#direct_po_create").val() == 0) {
		$("#addrow").css("visibility", "hidden");
		$("#product_id").attr("disabled", "disabled");
	} else {
		$("#addrow").css("visibility", "visible");
		$("#product_id").removeAttr("disabled", "disabled");
	}
}
function open_consignee_concept() {
	$('#modal-add-consignee-concept').modal('show');
	cons_type();
}
function cons_type() {
	var con = $('input[name="con_type"]:checked').val();
	//CKEDITOR.instances['con_address'].setData("");
	if (con == 1) {
		$("#con_ve").hide();
		$("#con_uni").show();
	} else if (con == 2) {
		$("#con_ve").show();
		$("#con_uni").hide();
	} else {

		$("#con_ve").hide();
		$("#con_uni").hide();
	}
}
function get_vendor_address(vender) {
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "vender_address", vender: vender },
		success: function (response) {
			var resp = JSON.parse(response);
			CKEDITOR.instances['con_address'].setData(resp.resp_html);
			Unloading();
		}
	});
}
function get_branch_address(branch) {
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "branch_address", branch: branch },
		success: function (response) {
			var resp = JSON.parse(response);
			CKEDITOR.instances['con_address'].setData(resp.resp_html);
			Unloading();
		}
	});
}
function close_consignee_concept() {
	var con = $('input[name="con_type"]:checked').val();

	if (con == 1) {
		if ($("#con_branch").val() === "") {
			toastr.warning("Choose Branch", "ERROR")
			$("#con_branch").select2('focus')
			return false;
		}
	} else if (con == 2) {
		if ($("#con_vender_id").val() === "") {
			toastr.warning("Choose Vendor", "ERROR")
			$("#con_vender_id").select2('focus')
			return false;
		}
	}
	$('#modal-add-consignee-concept').modal('hide');
	$("#purchaseorder_add").submit();
}
function consinee_change() {
	var val = $('input[name="same_as"]:checked').val();
	if (val == '1') {
		/*$('#consignee_id').select2("val","");
		$('#consignee').hide();*/
		$("#save_con").hide();
		$("#save").show();
	}
	else {
		/*$('#consignee').show();*/
		$("#save").hide();
		$("#save_con").show();
	}
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

function delete_po_approval(aprv_id, tbl, tbl_id, status, purchaseorder_id) {
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "delete_po_approval", aprv_id: aprv_id, tbl: tbl, tbl_id: tbl_id, status: status, purchaseorder_id: purchaseorder_id },
		success: function (response) {
			var resp = JSON.parse(response);

			if (resp.approval == 'finance_approval') {
				load_finance_purchase_hist_datatable();
			} else {
				load_purchase_hist_datatable();
			}
			load_po_datatable();
			Unloading();
		}
	});
}

function load_unit_product() {
	var product_id = $("#product_id").val();
	var rate_unit = $("#rate_unit_id").val();
	var edit_id = $("#edit_id").val();
	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + purchase_domain + 'app/purchase_order/',
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
		url: root_domain + purchase_domain + 'app/purchase_order/',
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
	form_data.append('doc_name', $("#doc_name").val());
	form_data.append('purchaseorder_id', $("#eid").val());
	form_data.append("doc_attach", document.getElementById('doc_attach').files[0]);

	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function (response) {
			//console.log(response);
			$("#doc_name").val("").focus();
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
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "show_document_attach", purchaseorder_id: eid },
		success: function (resp) {
			//console.log(resp);
			$('#po_doc_list').html(resp);
			Unloading();
		}
	});
}

function add_fin_document_attach() {
	var ext = $('#fin_doc_attach').val().split('.').pop().toLowerCase();
	// if($.inArray(ext, ['pdf','doc','docx']) === -1) {
	// 	toastr.warning("Only image type pdf/doc/docx is allowed", "ERROR");
	// 	$("#doc_attach").focus();
	// 	return false;
	// }

	if (!$("#fin_doc_attach").val()) {
		toastr.warning("Choose File", "ERROR");
		$("#fin_doc_attach").focus();
		return false;
	}

	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_document_attach");
	form_data.append('doc_name', $("#fin_doc_name").val());
	form_data.append('purchaseorder_id', $("#fin_ref_ord_id").val());
	form_data.append("doc_attach", document.getElementById('fin_doc_attach').files[0]);

	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: form_data,
		contentType: false,
		processData: false,
		success: function (response) {
			//console.log(response);
			$("#fin_doc_name").val("").focus();
			$("#fin_doc_attach").val("").focus();
			$('#fin_dfd_attch_btn').val('Add');
			Unloading();
			show_document_fin_attach();
			var cnt = $('#po_document_count').val();
			cnt = parseInt(cnt) + parseInt(1);
			$('#po_document_count').val(cnt);
		}
	});
}

function show_document_fin_attach() {
	var eid = $('#fin_ref_ord_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "show_document_attach", purchaseorder_id: eid },
		success: function (resp) {
			//console.log(resp);
			$('#fin_po_doc_list').html(resp);
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
			url: root_domain + purchase_domain + 'app/purchase_order/',
			data: { mode: "delete_document_attach", attach_id: id },
			success: function (response) {
				//console.log(response);
				var data = jQuery.parseJSON(response);
				var response = data.res;
				if (response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_document_attach();
					show_document_fin_attach();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				Unloading();
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

function get_terms_detail(id) {
	var tc_id = $("#ref_tc_id" + id).val();

	$.ajax({
		type: "POST",
		async: false,
		url: root_domain + purchase_domain + 'app/purchase_order/',
		data: { mode: "get_terms_detail", tc_id: tc_id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#tc_details" + id).val(obj.tc_details);
		}
	});
}

function open_mail_dir_modal(po_id,cust_mail,email_page_path=null){
	$('#send_email_via_po_modal').modal("show");	

	Loading(true);
	
	$('#to_email_po').val(cust_mail);
	$('#email_page_path').val(email_page_path);
	$('#email_po_id').val(po_id);
	
	Unloading();
}


$("#send_email_add_po").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#send_email_add_po").valid()) {
		return false;
	} 
	
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#send_mail_btn_po').prop('disabled', true);
	var form_data=new FormData(form);

	$.ajax({
		cache:false,
		url: root_domain + purchase_domain + 'app/purchase_order/',
		type: "POST",
		// data: { mode : "send_mail_quotation" , form_data:form_data},
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	return false;
			var arr = jQuery.parseJSON(response);			
			//console.log(arr);
			if(arr.msg == '1') {
				toastr.success("MAIL SENT SUCCESSFULLY", "SUCCESS");
				$('#send_email_via_quotation_dir_modal').modal('hide');
				load_quotation_datatable();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			Unloading();
			$('#send_mail_btn_po').prop('disabled', false);
			$('#send_email_add_po').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
			Unloading();
		}
	});
	
});

function exportCsv() {
	var po_type_status = $('input[name=po_type_status]:Checked').val();
	var date = $('#rep_date').val();
	var branch_id = $('#branch_id').val();
	var vender_id = $('#vender_id').val();
	var filt_status = $('#filt_status').val();
	var short_status = $('#short_status').val();
	
	var url = root_domain +'generate_export?mode=purchase_order_list&po_type_status=' + encodeURIComponent(po_type_status) + "&date=" + encodeURIComponent(date) + "&vender_id=" + encodeURIComponent(vender_id) + "&branch_id=" + encodeURIComponent(branch_id) + "&filt_status=" + encodeURIComponent(filt_status) + "&short_status=" + encodeURIComponent(short_status);
	window.location.href = url;
}