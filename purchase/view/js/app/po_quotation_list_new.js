
//var datatable;
$(document).ready(function() {
	pendingQuotationDetail();	
	load_party_quotation_product();
	load_supplier_quotation_vender();
	load_quotation_comparision();
	load_po_quotation_datatable();
});

function load_po_quotation_datatable()
{
	var date=$('#rep_date').val();
	datatable = $("#po-quot-table").dataTable({
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
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+purchase_domain+'app/po_quotation_list_new/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{ "name": "date", "value": date }
				);
			},
			"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function pendingQuotationDetail(){
	var product_id = $("#product_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "load_pending_quotation", product_id:product_id},
		success: function(responce){
			//console.log(responce);
			$('#pending_quot_data').html(responce);
			check_mode();
		}
	});
}

function check_all()
{
	var max_limit=50;
	
	if($("#all_chk_box").is(':checked')){
		$('.chk_box').each(function(){
			var chelen = $(".chk_box:checked").length;
			if (chelen < max_limit){
				this.checked = true;
			}
			else
			{
				this.checked = false;
			}
		});
	}else{
		$('.chk_box').each(function(){
			this.checked = false;
		});
	}
}

function check_all_item_req()
{
	var max_limit=50;
	if($("#all_chk_box1").is(':checked')){
		$('.chk_box_item_req').each(function(){
			var chelen = $(".chk_box_item_req:checked").length;
			if (chelen < max_limit){
				this.checked = true;
			}
			else
			{
				this.checked = false;
			}
		});
	}else{
		$('.chk_box_item_req').each(function(){
			this.checked = false;
		});
	}
}

function check_all_item_req12()
{
	var max_limit=50;
	if($("#all_chk_box12").is(':checked')){
		$('.chk_box_item_req12').each(function(){
			var chelen = $(".chk_box_item_req12:checked").length;
			if (chelen < max_limit){
				this.checked = true;
			}
			else
			{
				this.checked = false;
			}
		});
	}else{
		$('.chk_box_item_req12').each(function(){
			this.checked = false;
		});
	}
}

function check_box_limit_item_req(cid){
	var max_limit = 50;
	var chelen = $(".chk_box_item_req:checked").length;
	if (chelen > max_limit){
		$('#'+cid).attr('checked', false);
	}
}

function check_box_limit_item_req12(cid){
	var max_limit = 50;
	var chelen = $(".chk_box_item_req12:checked").length;
	if (chelen > max_limit){
		$('#'+cid).attr('checked', false);
	}
}

function updateCounter() {
    var numberOfChecked = $('input[name="che_box[]"]:checked').length;
	$('#chk_sel_count').html(numberOfChecked);
	if(numberOfChecked !== ""){
		$("#save").show();
	}else{
		$("#save").hide();
	}
}

function check_box_limit(cid){
	var max_limit = 50;
	var chelen = $(".chk_box:checked").length;
	if (chelen > max_limit){
		$('#'+cid).attr('checked', false);
	}
}

// function po_quotation_create(){
//     var check = $('#chk_sel_count').text();
//     if(check == '0' || check === ''){
//         toastr.warning("SELECT PRODUCT FIRST ", "ERROR");
//     }
// 	var	approove_id = $("input[name='che_box[]']:checked").map(function(){return $(this).val();}).get();

// 	$.ajax({
// 		type: "POST",
// 		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
// 		data: { mode : "add_new_quotation_ref", approove_id:approove_id,check:check},
// 		success: function(responce){
// 			var arr = jQuery.parseJSON(responce);
// 			if(arr.msg == '1') {
//     				// toastr.success("Indent Approve SuccessFully", "SUCCESS");
//     				window.location=root_domain+purchase_domain+'purchase_quotation/'+arr.insert_id;
// 			}
// 			else if(arr.msg == '0') {
// 				toastr.warning("SOMETHING WRONG", "ERROR");
// 			}
// 		},
// 		error: function(jqXHR, textStatus, errorThrown) {
// 			console.log(textStatus, errorThrown);
// 		}
// 	});	
// }


function po_quotation_create() {
    var check = $('#chk_sel_count').text();
    if (check == '0' || check === '') {
        toastr.warning("SELECT PRODUCT FIRST", "ERROR");
        return; // Exit early if no product is selected
    }

    var approove_id = $("input[name='che_box[]']:checked").map(function() {
        return $(this).val();
    }).get();

    // Add this check
    if (approove_id.length === 0) {
        toastr.warning("SELECT AT LEAST ONE ITEM FOR APPROVAL", "ERROR");
        return; // Exit early if no approval ID is selected
    }

    $.ajax({
        type: "POST",
        url: root_domain + purchase_domain + 'app/po_quotation_list_new/',
        data: { mode: "add_new_quotation_ref", approove_id: approove_id, check: check },
        success: function(responce) {
            var arr = jQuery.parseJSON(responce);
            if (arr.msg == '1') {
                window.location = root_domain + purchase_domain + 'purchase_quotation/' + arr.insert_id;
            } else if (arr.msg == '0') {
                toastr.warning("SOMETHING WRONG", "ERROR");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}



function load_req_quotation(){
	var quotation_ref_id = $("#quotation_ref_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "load_req_quotation",quotation_ref_id:quotation_ref_id},
		success: function(responce){
			// console.log(responce);
			$('#request_for_quotation').html(responce);
		}
	});
}

function mode_change_req_quot(){
	var quotation_ref_id = $("#quotation_ref_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "mode_change_req_quot",quotation_ref_id:quotation_ref_id},
		success: function(responce){
			var obj = jQuery.parseJSON(responce);
			$("#supplier_id").select2("val",obj.vender_id.split(","));
			$("#rq_mode").val('edit');
			check_mode();
		}
	});
}

function check_mode(){
	var mode = $("#rq_mode").val();
	if(mode=='add'){
		$(".editmode1").hide();
		$(".addmode").show();
	}else{
		$(".editmode1").show();
		$(".addmode").hide();
	}
}

function request_quotation_data(){
	var supplier_id = $("#supplier_id").val();
	var	req_quot_id = $("input[name='chk_box_item_req[]']:Checked").map(function(){return $(this).val();}).get();
	var quotation_ref_id = $("#quotation_ref_id").val();

	if(supplier_id==''){
		toastr.warning("Please Select Supplier First","ERROR");
		$('#supplier_id').select2('focus');
		return false;
	}

	if(req_quot_id==''){
		toastr.warning("Please Choose Atleast One Product","ERROR");
		return false;
	}
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "request_quotation_data",supplier_id:supplier_id,req_quot_id:req_quot_id,quotation_ref_id:quotation_ref_id},
		success: function(responce){
			//console.log(responce);
			var obj = jQuery.parseJSON(responce);
			if(obj.msg == '1') {
				toastr.success("REQUEST QUOTATION ADDED SUCCESSFULLY", "SUCCESS");
				load_quotation_comparision();
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			$("#suppliers").html(obj.ledger);
			$("#rq_mode").val('add');
			check_mode();
			load_supplier_quotation_vender();
		}
	});
}

function supplier_quotation_data(){
	if($("#vender_id").val()==''){
		toastr.warning("Please Select Vender First","ERROR");
		$('#vender_id').select2('focus');
		return false;
	}

	if($("#quotation_no").val()==''){
		toastr.warning("Please Enter Quotation No","ERROR");
		$('#quotation_no').focus();
		return false;	
	}

	if($("#quotation_date").val()==''){
		toastr.warning("Please Enter Quotation Date","ERROR");
		$('#quotation_date').focus();
		return false;	
	}
	var	req_quot_id = $("input[name='chk_box_item_req12[]']:Checked").map(function(){return $(this).val();}).get();
	var quotation_ref_id = $("#quotation_ref_id").val();
	var vender_id 		= $("#vender_id").val();
	var quotation_no 	= $("#quotation_no").val();
	var quotation_date 	= $("#quotation_date").val();
	var delivery_priode 	= $("#delivery_priode").val();
	var payment_terms 	= $("#payment_terms").val();
	var ex_delivery 	= $("#ex_delivery").val();
	var discount 	= $("#discount").val();
	var total = parseFloat($('.grand_total').text()) || 0;
	//var delivery_date 	= $("#delivery_date").val();
	//var payment_days	= $("#payment_days").val();
	//,delivery_date:delivery_date,payment_days:payment_days
	var supplier_detail_id	= $("#supplier_detail_id").val();

	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "supplier_quotation_data",req_quot_id:req_quot_id,quotation_ref_id:quotation_ref_id,vender_id:vender_id,quotation_no:quotation_no,quotation_date:quotation_date,supplier_detail_id:supplier_detail_id,delivery_priode:delivery_priode,ex_delivery:ex_delivery,discount:discount,amount:total,payment_terms:payment_terms},
		success: function(responce){
			var obj = jQuery.parseJSON(responce);
			if(obj.msg == 1){
				toastr.success("SUPPLIER QUOTATION ADDED SUCCESSFULLY", "SUCCESS");
				load_quotation_comparision();
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
		}
	});
}

function load_supplier_quotation_vender(){
	var quotation_ref_id = $("#quotation_ref_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "load_supplier_quotation_vender",quotation_ref_id:quotation_ref_id},
		success: function(responce){
			 $("#vender_id").html(responce);
		}
	});
}
function load_supplier_detail(){
	var vender_id = $("#vender_id").val();
	var quotation_ref_id = $("#quotation_ref_id").val();

	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "load_supplier_detail",vender_id:vender_id,quotation_ref_id:quotation_ref_id},
		success: function(responce){
			var obj = jQuery.parseJSON(responce);
			
			$("#quotation_no").val(obj.quotation_no);
			$("#quotation_date").val(obj.quotation_date);
			$("#delivery_date").val(obj.delivery_date);
			$("#payment_days").val(obj.payment_days);
			$("#delivery_priode").val(obj.delivery_priode);
			$("#payment_terms").val(obj.payment_terms);
			$("#ex_delivery").val(obj.ex_delivery);
			$("#discount").val(obj.discount).trigger('change');
			$("#supplier_detail_id").val(obj.supplier_detail_id);
			get_all_bill_sundry(obj.supplier_detail_id);
		}
	});
}

function load_quotation_comparision(){
	var quotation_ref_id = $("#quotation_ref_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "load_quotation_comparision",quotation_ref_id:quotation_ref_id},
		success: function(responce){
			$('#quotation_comparision').html(responce);
			quotation_compare();
		}
	});
}

function quotation_compare(){
	var comparision = $("#comparision").val();
	if(comparision==1){
		$('.comapre_prod_wise').hide();
		$('.comapre_quot_wise').show();
	}else{
		$('.comapre_prod_wise').show();
		$('.comapre_quot_wise').hide();
	}
}

function quotation_item_edit(tbl_name,chked_id,ref_name){
	var	checked_id = $("input[name='"+chked_id+"[]']:Checked").map(function(){return $(this).val();}).get();
	if(checked_id==''){
		toastr.warning("Please Choose Atleast One Product","ERROR");
		return false;
	}
	$('#quotation_item_detail').modal('show');
	add_temp_edit_product(tbl_name,checked_id,ref_name);
}

function quotation_item_delete(tbl_name,chked_id,ref_name){
	var	checked_id = $("input[name='"+chked_id+"[]']:Checked").map(function(){return $(this).val();}).get();
	if(checked_id==''){
		toastr.warning("Please Choose Atleast One Product","ERROR");
		return false;
	}
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/po_quotation_list_new/',
			data: { mode : "delete_data",  tbl_name : tbl_name ,ref_name:ref_name,checked_id:checked_id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_quotation_comparision();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function add_temp_edit_product(tbl_name,checked_id,ref_name){
	var quotation_ref_id = $("#quotation_ref_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "add_temp_edit_product",tbl_name:tbl_name,checked_id:checked_id,ref_name:ref_name,quotation_ref_id:quotation_ref_id},
		success: function(responce){
			var obj = jQuery.parseJSON(responce);
			edit_modal_data_preview(obj.po_quotationtrn_id,obj.ref_name);
		}
	});
}
// edited
// edited
// edited

function edit_modal_data_preview(id,ref_name){
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "edit_modal_data_preview",id:id,ref_name:ref_name},
		success: function(responce){
			$('#product_detail').html(responce);
			CKEDITOR.replace('product_desc_sup', {
				enterMode: CKEDITOR.ENTER_BR
			});

			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
	
			$(".form_datetime").datetimepicker({
				format: 'dd-mm-yyyy hh:ii',
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left"
	
			});
		}
	});
}

function save_trn_data(btnval){
	for (instance in CKEDITOR.instances) 
	{
		CKEDITOR.instances[instance].updateElement();
	}

	var quotation_ref_id = $("#quotation_ref_id").val();
	var ref_name = $("#ref_name").val();
	var po_quotationtrn_id = $("#ref_trn").val();
	var product_desc = $("#product_desc_sup").val();
	
	var delivery_date ='';var payment_days='';var product_rate='';
	if(ref_name == 'supplier_quotation'){
		delivery_date = $('#delivery_date').val(); 
		payment_days  = $('#payment_days').val();
		product_rate  = $('#product_rate').val();
	}
	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "save_trn_data",btnval:btnval,quotation_ref_id:quotation_ref_id,delivery_date:delivery_date,payment_days:payment_days,po_quotationtrn_id:po_quotationtrn_id,ref_name:ref_name,product_desc:product_desc,product_rate:product_rate},
		success: function(responce){
			var obj = jQuery.parseJSON(responce);
			if(btnval==2){
				if(obj.msg==1){
					toastr.success("ITEM UPDATE SUCCESSFULLY", "SUCCESS");
					edit_modal_data_preview(obj.po_quotationtrn_id,obj.ref_name);
					load_req_quotation();
					load_party_quotation_product();
				}else{
					toastr.warning("SOMETHING WENT WRONG", "WARNING");
				}
			}else{
				if(obj.msg==1){
					toastr.success("ITEM UPDATE SUCCESSFULLY", "SUCCESS");
					$('#quotation_item_detail').modal('hide');
					load_req_quotation();
					load_party_quotation_product();
				}else{
					toastr.warning("SOMETHING WENT WRONG", "WARNING");
				}
			}
		}
	});
}

function quotation_comparision_add(){
	var comparision = $("#comparision").val(); 
	var quotation_ref_id = $("#quotation_ref_id").val();
	var supplier_detail_id = $("input[name='abc']:checked").val();
	var po_quotationtrn_id_arr=[];
	var i = 0;
	$("input:radio.comapre_prod_wise:checked").each(function(){
		po_quotationtrn_id_arr[i++] = this.value;
	});
	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "quotation_comparision_add",comparision:comparision,po_quotationtrn_id:po_quotationtrn_id_arr,quotation_ref_id:quotation_ref_id,supplier_detail_id:supplier_detail_id},
		success: function(responce){
			var obj = jQuery.parseJSON(responce);
			if(obj.msg==1){
				toastr.success("QUOTATION COMPARE SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+purchase_domain+'po_quotation_list_new';
			}else{
				toastr.warning("SOMETHING WENT WRONG", "WARNING");
			}
		}
	});
}

function delete_quotation(eid){

	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/po_quotation_list_new/',
			data: { mode : "delete_quotation",eid:eid},
			success: function(responce){
				var obj = jQuery.parseJSON(responce);
				if(obj.msg==1){
					toastr.success("QUOTATION COMPARE SUCCESSFULLY", "SUCCESS");
				}else{
					toastr.warning("SOMETHING WENT WRONG", "WARNING");
				}
			}
		});
	}
}

function disapprove_quotation(id){
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "disapprove_data",id:id},
		success: function(responce){
			var obj = jQuery.parseJSON(responce);
			if(obj.msg==1){
				toastr.success("QUOTATION DISAPPROVED SUCCESSFULLY", "SUCCESS");
				load_po_quotation_datatable();
			}else{
				toastr.warning("SOMETHING WENT WRONG", "WARNING");
			}
		}
	});
}

function approve_quotation(id){
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "approve_data",id:id},
		success: function(responce){
			var obj = jQuery.parseJSON(responce);
			if(obj.msg==1){
				toastr.success("QUOTATION APPROVED SUCCESSFULLY", "SUCCESS");
				load_po_quotation_datatable();
				
			}else{
				toastr.warning("SOMETHING WENT WRONG", "WARNING");
			}
		}
	});
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

function load_party_quotation_product(){
    var vender_id = $("#vender_id").val();
    var quotation_ref_id = $("#quotation_ref_id").val();
	
    $.ajax({
        type: "POST",
        url: root_domain + purchase_domain + 'app/po_quotation_list_new/',
        data: { mode: "load_supplier_quotation", vender_id: vender_id, quotation_ref_id: quotation_ref_id},
        success: function(response) {
            $('#supplier_quotation').html(response);
            $('.delivery-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
            update_gtotal();
            load_quotation_comparision();
        }
    });
}

function update_gtotal() {
    // Get the base total and other values
    var total = parseFloat($('#total').val()) || 0;  // base total (before charges)
    var g_total = parseFloat($('#g_total').val()) || 0;  // current grand total
    var discount = parseFloat($('#discount').val()) || 0;  // discount percentage

    // Calculate the discount amount
    var discountAmount = (discount / 100) * g_total;

    // Calculate the new total after applying the discount
    var new_total = g_total - discountAmount;

    // Update the fields
    $('#g_total').val(new_total);
    $('.grand_total').text(new_total.toFixed(2));
    $('.sub_total').text(total.toFixed(2));

    // Update the discount row
    $('.discount_row').each(function() {
        $(this).find('td:eq(1)').text(discountAmount.toFixed(2));
        $(this).find('td:eq(0)').html('<b>Discount(' + discount + '%)</b>');
    });

    // Update the totals after modifying the grand total
    updateTotals();
}

var rowIdx = 0;

function addBillSundry() {
    Loading(true); // Show loading indicator

    var taxableamount = 0;
    var totalsundryexist = 0;
    var basic_amount = $("#total").val();
    var netamount = $("#g_total").val();

    $(".gst").each(function () {
        var gstVal = $(this).val();
        taxableamount = Number(taxableamount) + Number(gstVal);
    });

    $(".billsundryclass").each(function () {
        var billsundryclass = $(this).val();
        totalsundryexist = Number(totalsundryexist) + Number(billsundryclass);
    });

    var eid = $("#supplier_detail_id").val();
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
            url: root_domain + purchase_domain + 'app/po_quotation_list_new/',
            data: {
                mode: "get_bill_sundry_details",
                sundry_ledger_id: bill_sundry_value,
                totalsundryexist: totalsundryexist,
                taxableamount: taxableamount,
                basic_amount: basic_amount,
                netamount: netamount,
                gst_type: gst_type,
                default_amount: bill_sundry_amount,
                invoice_id: eid,
                currency_enable: currency_enable,
                currency_id: currency_id,
                currency_rate: currency_rate,
                invoice_date: $('#invoice_date').val(),
                user_id: user_id
            },
            success: function(response) {
                var arr1 = JSON.parse(response);
                var arr = arr1.split(",");

                if (arr[3]) {
                    get_all_bill_sundry(eid);
                } else {
                    if (arr[0]) {
                        if (arr[4] != 0) {
                            var rowHtml = `<tr class="charge_row">
                                <input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[1]}">
                                <input class="addontax" name="bill_sundry_addon_tax[${bill_sundry_value}]" type="hidden" value="${arr[4]}-${arr[5]}-${arr[1]}" >
                                <td class="text-center"><b>${bill_sundry} ${arr[2]}</b></td>
                                <td class="text-center amount-column">${arr[1]}</td>
                                <td class="text-center">
                                    <button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" 
                                            title="remove" 
                                            type="button" 
                                            onclick="removeCharge(this,${bill_sundry_value})">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </td>
                            </tr>`;
                        }else{
                            var rowHtml = `<tr class="charge_row">
                                <input id="sundry_name" name="bill_sundry_addon[${bill_sundry_value}]" type="hidden" value="${arr[1]}">
                                <td class="text-center"><b>${bill_sundry} ${arr[2]}</b></td>
                                <td class="text-center amount-column">${arr[1]}</td>
                                <td class="text-center">
                                    <button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" 
                                            title="remove" 
                                            type="button" 
                                            onclick="removeCharge(this,${bill_sundry_value})">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </td>
                            </tr>`;
                        }
                        $(".charges_table_body .grand_total_row").before(rowHtml);

                        $('#bill_sundry').val('0');
                        $('#bill_sundry_amount').val('');

                        // After adding the charge, recalculate totals
                        updateTotals();
                    }
                }
            }
        });
    }
    Unloading(); // Hide loading indicator
}

function get_all_bill_sundry(invoice_id) {
	$.ajax({
		type: 'POST',
		url: root_domain + purchase_domain + 'app/po_quotation_list_new/',
		data: { mode: 'get_all_bill_sundry', invoice_id: invoice_id },
		success: function (response) {
			// console.log(response);
			$(".charges_table_body .charge_row").remove();
			$(".charges_table_body .grand_total_row").before(response);
			updateTotals();
		}
	});
}

// Function to update the Sub Total, Discount, and Grand Total
function updateTotals() {
    var subTotal = parseFloat($('.sub_total').text()) || 0;  // Start with the base sub total (no charges included yet)
    var totalAmount = subTotal;

    // Sum up amounts from the charge rows (charges will be added after discount is applied)
    $(".charges_table_body .charge_row").each(function () {
        var amount = parseFloat($(this).find(".amount-column").text()) || 0;
        totalAmount += amount;  // Add charges to the totalAmount
    });

    // Get the discount value (if any)
    var discount = parseFloat($('#discount').val()) || 0;

    // Calculate discount on the base sub total (not on the totalAmount which includes charges)
    var discountAmount = (discount / 100) * subTotal;  // Apply discount only to the subTotal
    var grandTotal = totalAmount - discountAmount;  // Subtract discount from the total after charges are added

    // Update the Sub Total, Discount, and Grand Total in the table
    $(".sub_total").text(subTotal.toFixed(2));  // Display the base sub total
    $(".discount").text(discountAmount.toFixed(2));  // Display the discount
    $(".grand_total").text(grandTotal.toFixed(2));  // Display the grand total after discount
}

// Function to remove a charge row and update the totals
// function removeCharge(button) {
//     $(button).closest("tr").remove();
//     updateTotals();  // Recalculate the totals after removal
// }

function removeCharge(button,ledger_id) {
	Loading(true);
	var edit_id = $('#quotation_ref_id').val();
	if (edit_id != '' || edit_id != '0') {
        $.ajax({
			type: 'post',
			url: root_domain + purchase_domain + 'app/po_quotation_list_new/',
			data: { mode: 'remove_sundry', edit_id: edit_id, ledger_id: ledger_id },
			success: function (result) {
				toastr.success("SUNDRY REMOVE SUCCESSFULLY", "SUCCESS");
			}
		})
	}
	$(button).closest("tr").remove();
    updateTotals();
	Unloading();
}

function product_edit(po_quotationtrn_id){
	$("#p_rate_"+po_quotationtrn_id).removeAttr("readonly");
	$("#d_date_"+po_quotationtrn_id).removeAttr("readonly");
	$("#remark_"+po_quotationtrn_id).removeAttr("readonly");
	$("#product_update_btn_"+po_quotationtrn_id).show();
	$("#product_edit_btn_"+po_quotationtrn_id).hide();
	$("#product_delete_btn_"+po_quotationtrn_id).hide();
}

function product_delete(po_quotationtrn_id){
	var product_id = $("product_id").val();
	var ref_name = $("#ref_name").val();
	// var po_quotationtrn_id = $("#ref_trn").val();
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/po_quotation_list_new/',
			data: { mode : "delete_product", product_id:product_id, ref_name:ref_name,po_quotationtrn_id:po_quotationtrn_id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_quotation_comparision();
					Unloading();
				}
				else if(response == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}


function product_update(po_quotationtrn_id){
	var product_id = $("product_id").val();
	var quotation_ref_id = $("#quotation_ref_id").val();
	var ref_name = $("#ref_name").val();
	// var po_quotationtrn_id = $("#ref_trn").val();
	
	
	var product_desc = '';var delivery_date ='';var payment_days='';var product_rate='';
	if(ref_name == 'supplier_quotation'){
		product_desc = $("#remark_"+po_quotationtrn_id).val();
		delivery_date = $("#d_date_"+po_quotationtrn_id).val(); 
		payment_days  = $("#payment_days").val();
		product_rate  = $("#p_rate_"+po_quotationtrn_id).val();
	}
	
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/po_quotation_list_new/',
		data: { mode : "product_update" ,product_id:product_id ,quotation_ref_id:quotation_ref_id,delivery_date:delivery_date,payment_days:payment_days,po_quotationtrn_id:po_quotationtrn_id,ref_name:ref_name,product_desc:product_desc,product_rate:product_rate},
		success: function(responce){
			
			var obj = jQuery.parseJSON(responce);
			
				if(obj.msg==1){
					$("#p_rate_"+po_quotationtrn_id).attr('readonly', true);
					$("#d_date_"+po_quotationtrn_id).attr('readonly', true);
					$("#remark_"+po_quotationtrn_id).attr('readonly', true);
					$("#product_update_btn_"+po_quotationtrn_id).hide();
					$("#product_edit_btn_"+po_quotationtrn_id).show();
					$("#product_delete_btn_"+po_quotationtrn_id).show();
					toastr.success("ITEM UPDATE SUCCESSFULLY", "SUCCESS");
					// $('#quotation_item_detail').modal('hide');
					load_req_quotation();
					load_party_quotation_product();
				}else{
					toastr.warning("SOMETHING WENT WRONG", "WARNING");
				}
			
		}
	});
}