//var datatable;
$(document).ready(function() {
	load_salesorder_deallocate_stock_datatable();
	add_product_batch_wise();
	load_tempout_data();
	$("#so_deallocate").validate({
		rules: {
			so_deallocate_no: {
				required: true			
			},
			so_deallocate_date: {
				required: true			
			},
		},
		messages: {
			so_deallocate_no: {
				required: "Enter Material Issue No"			
			},
			so_deallocate_date: {
				required: "Enter Material Issue Date"			
			},
		}
	}); 
});

function load_salesorder_deallocate_stock_datatable()
{
	// var date=$('#rep_date').val();
	
	datatable = $("#dynamic-table").dataTable({
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
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" }
				);
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();


		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}

$("#so_deallocate").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#so_deallocate").valid()) {
		return false;
	}
	if($("#so_temp_data").val() == 0){
		toastr.warning("PLEASE ADD SOME DETAILS", "ERROR");
		return false;
	}

	form.submitted = true;
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	
    var form_data=new FormData(this);	
    $.ajax({
    	cache:false,
    	url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
    	type: "POST",
    	data: form_data,
    	contentType: false,
    	processData:false,
    	success: function(response)
    	{

			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("SALEORDER DEALLOCATE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+inventory_domain+'salesorder_deallocate_stock_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			
			// $('#so_deallocate').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
			Unloading();
		}
	});
});

function get_so_deallocate_no(){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: { mode : "load_so_deallocate_no"},
		success: function(response)
		{
			$('#so_deallocate_no').val(response);
			Unloading();
		}
	});
}

function get_salesorder_product(sales_order_id){
	
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
			data: { mode : "get_salesorder_product",  sales_order_id : sales_order_id},
			success: function(response)
			{
				$('#sales_ordertrn_id').empty().append(response);
				$("#sales_ordertrn_id").select2({
					width: '100%'
				});

				Unloading();
			}
		});
}

function load_product_detail(sales_ordertrn_id) {
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: { mode : "load_productdata",sales_ordertrn_id :sales_ordertrn_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response); 
				$('.unitname').empty().html(resp.base_unit_name);
				$('#unit_id').val(resp.product_base_unit);
				$('#so_qty').val(resp.product_qty);
				$('#product_id').val(resp.product_id);
				$('#reserve_qty').val(resp.pending_stock);
				$('#isbatchwise').val(resp.batch_wise_stock_manage);
				add_product_batch_wise();
				Unloading();
			}
		});
}


function add_field()
{
	if($("#sales_order_id").val()===""){
		toastr.warning("Select Salesorder", "ERROR");
		$("#sales_order_id").select2('focus');
		return false;
	}else if($("#product_id").val()===""){
		toastr.warning("Select Product", "ERROR");
		$("#sales_ordertrn_id").select2('focus');
		return false;
	}else if($("#de_allocate_qty").val()===""){
		toastr.warning("Enter Quantity", "ERROR");
		$("#de_allocate_qty").focus();
		return false;
	}

	var de_allocate_qty = $("#de_allocate_qty").val();
	var reserve_stock = $("#reserve_qty").val();

	if(parseFloat(de_allocate_qty) >  parseFloat(reserve_stock)){
		toastr.warning("YOU CAN'T ENTER MORE THAN RESERVE STOCK QTY", "ERROR");
		return false;
	}
	
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: { 
			mode : "fieldadd",
			sales_order_id:$("#sales_order_id").val(),
			sales_ordertrn_id:$("#sales_ordertrn_id").val(),
			product_id:$("#product_id").val(),
			unit_id:$("#unit_id").val(),
			de_allocate_qty : de_allocate_qty
		},
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);

			if(arr.msg == '1') {
				toastr.success("DEALLOCATE QAUNTITY ADDED SUCCESSFULLY", "SUCCESS");
				load_tempout_data();
			}else{
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			$('#bs-batch_wise_stock-modal').modal('hide');
			reset_data();
			Unloading();
		}
	});
}

function reset_data(){
	$("#sales_order_id").select2('val','');
	$("#sales_ordertrn_id").select2('val','');
	$('#addrow').val('Add');
	
	$("#product_id").val("");
	$("#unit_id").val("1");
	$("#de_allocate_qty").val('');
	$("#reserve_qty").val('');
	$("#so_qty").val('');

}
function load_tempout_data(){
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: { 
			mode : "load_tempoutward",
		},
		success: function(response)
		{
			$('#so_deallocate_temp_div').empty().html(response);
			Unloading();
		}
	});
}


function delete_tempout_data(){
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: { 
			mode : "delete_tempout_data",
		},
		success: function(response)
		{
			
			Unloading();
		}
	});
}

function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
			data: { mode : "delete_data", eid:id},
			success: function(response)
			{
				
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_tempout_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}


function delete_deallocate_data(id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
			data: { mode : "delete_main_data", eid:id},
			success: function(response)
			{
				
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_salesorder_deallocate_stock_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}


function edit_data(id)
{

	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: { mode : "preedit",  id : id},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$("#product_id").select2('data', { id:data.product_id, text: data.product_name}).trigger('change');

			setTimeout(function(){ 
			$("#edit_id").val(data.so_deallocate_trn_id);
			$('#product_base_qty').data('product_base_qty',data.product_base_qty.trim());
			$('#product_conv_qty').data('product_conv_qty',data.product_conv_qty.trim());
			$('#addrow').val('Update');
			
			$("#product_base_qty").val((data.base_qty).trim());

			$("#product_base_unit").val(data.base_unit);
			$("#product_conv_unit").val(data.conv_unit);
			$("#product_conv_qty").val((data.conv_qty).trim());
			$("#product_base_qty_hide").val((data.base_qty).trim());
			$("#product_conv_qty_hide").val((data.conv_qty).trim());

			$("#product_base_unit_name").val(data.base_unit_name);
			$("#product_conv_unit_name").val(data.conv_unit_name);
			Unloading();
			}, 500);

			}
		});
}

function load_reserve_stock_qty(product_id){
	Loading(true);
	var unit_id=$("#base_unit").val();
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: { mode : "load_stock_qty", product_id:product_id,unit_id:unit_id },
		success: function(data){
			$("#reserve_qty").val(parseFloat(data));
			Unloading();
		}		
	});
}

function open_approv_model(id,no){
	$('#preview_work_order_material_approval').modal('show');
	$('#so_deallocate_no').html(no);
	$('#so_deallocate_id').val(id);
	
	workorder_material_load();
	workorder_direct_hist();
}


function workorder_material_load(){
	var so_deallocate_id = $('#so_deallocate_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: { mode : "load_wo_direct_material_detail", so_deallocate_id:so_deallocate_id },
		success: function(resp){
		//console.log(resp);
		var resp=JSON.parse(resp);
		$('#detail_show').html(resp.detail_show);
		}
	});
}
function workorder_direct_hist(){
	var so_deallocate_id = $('#so_deallocate_id').val();

		$("#order-pocard-history-datatable").dataTable({
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
			"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
			"iDisplayLength": 5,
			"sAjaxSource": root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "load_wo_direct_hist" }, { "name": "so_deallocate_id", "value": so_deallocate_id }  );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function add_wo_apprv_hist(){
	
	var form_data = {
		mode:"add_wo_apprv_hist",
		approve_status:$('#approve_status').val(),
		approve_remark:$('#approve_remark').val(),
		so_deallocate_id:$('#so_deallocate_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: form_data,
		success: function(response)
		{
			$('#approve_status').select2("val","0");
			$('#approve_remark').val("");
			load_salesorder_deallocate_stock_datatable();
			workorder_direct_hist();
			//load_order_confirm_datatable();
			workorder_material_load();

			$('#preview_work_order_material_approval').modal('hide');
			Unloading();
		}
	});	
}


function add_product_batch_wise(){
	var isbatchwise=$("#isbatchwise").val();
	if(isbatchwise==="" || isbatchwise==="0"){
		$(".product_add_batch_wise").hide();
		$(".product_add_direct").show();
	}else{
		$(".product_add_batch_wise").show();
		$(".product_add_direct").hide();
	}
}


function open_batch_wise_qty(){
		
	load_batch_datatable();
	if($("#sales_order_id").val()===""){
		toastr.warning("Select Salesorder", "ERROR");
		$("#sales_order_id").select2('focus');
		return false;
	}else if($("#product_id").val()===""){
		toastr.warning("Select Product", "ERROR");
		$("#sales_ordertrn_id").select2('focus');
		return false;
	}else if($("#de_allocate_qty").val()===""){
		toastr.warning("Enter Quantity", "ERROR");
		$("#de_allocate_qty").focus();
		return false;
	}

	var de_allocate_qty = $("#de_allocate_qty").val();
	var reserve_stock = $("#reserve_qty").val();

	if(parseFloat(de_allocate_qty) >  parseFloat(reserve_stock)){
		toastr.warning("YOU CAN'T ENTER MORE THAN RESERVE STOCK QTY", "ERROR");
		return false;
	}
	
	$.ajax({
		type: "POST",
		url: root_domain+ inventory_domain+'app/salesorder_deallocate_stock/',
		data: { 
			mode : "batch_stock_model_open",
			sales_ordertrn_id:$("#sales_ordertrn_id").val(),
			de_allocate_qty:de_allocate_qty,
			product_id : $("#product_id").val()
		},
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$('#bs-batch_wise_stock-modal').modal('show');
			$("#batch_data").empty().html(data.html_data);	
			$(".batch_select2").select2({
				width: '100%',
			//minimumInputLength: 3
		});	
			validate_qty(0);	
		}
	});
}

function load_batch_datatable()
{
	var product_id=$('#product_id').val();
	var sales_ordertrn_id = $("#sales_ordertrn_id").val();
	var edit_id = $("#edit_id").val();
	
	datatable = $("#batch_stock_table").dataTable({
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
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + inventory_domain +'app/salesorder_deallocate_stock/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch_batch_qty" },
				{ "name": "product_id", "value": product_id },
				{ "name": "sales_ordertrn_id", "value": sales_ordertrn_id },
				{"name":"edit_id","value":edit_id} );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	}

	function add_batch_qty(){
		
		if($("#batch_id").val()==="")
		{		
			toastr.warning("Select Batch number", "ERROR")
			$("#batch_id").select2('focus')
			return false;
		}
		else if($("#qtyforbatch").val()==="")
		{		
			toastr.warning("Enter Qty", "ERROR")
			$("#qtyforbatch").focus();
			return false;
		}

		var stock_id = $("#batch_id").val();
		var batch_no = $("#batch_id").select2('data').text;
		var qty = $("#qtyforbatch").val();
		var product_id =  $("#product_id").val();
		var edit_id = $("#edit_id").val();
		var unit_id = $("#unit_id").val();
		var sales_ordertrn_id = $("#sales_ordertrn_id").val();
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/salesorder_deallocate_stock/',
			data: { 
				mode : "add_batch_qty",
				qty:qty,
				product_id:product_id,
				stock_id:stock_id,
				batch_no:batch_no,
				edit_id:edit_id,
				unit_id:unit_id,
				sales_ordertrn_id:sales_ordertrn_id
		},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				var response1=data.res;
				if(response1.trim() == "1") {
					
					toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
					$("#batch_id").select2("val","");
					$("#qtyforbatch").val("");
					$("#batch_stock").val("");
					load_batch_datatable();
					validate_qty(0);
					
				}else if(response1.trim() == "-1") {
					toastr.warning("ALREADY EXISTS", "WARNING");
					return false;
				}
				else if(response1.trim() == "0") {
					toastr.warning("SOMETHING WENT WRONG", "WARNING");
					return false;
				}
				

			}
		});
	}
	function validate_qty(qtyforbatch1){

		var product_qty =  $("#de_allocate_qty").val();
		var product_id =  $("#product_id").val();
		var edit_id = $("#edit_id").val();
		var qtyforbatch = qtyforbatch1;
		var sales_ordertrn_id = $("#sales_ordertrn_id").val();
		
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/salesorder_deallocate_stock/',
			data: { mode : "validate_qty",product_qty:product_qty,product_id:product_id,
			qtyforbatch:qtyforbatch,edit_id:edit_id,sales_ordertrn_id:sales_ordertrn_id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				var response1=data.res;

				if(response1.trim() == "0") {
					$("#qtyforbatch").val('0')
					toastr.warning("Batch Quantity can not greater Product quantity", "WARNING");
					$(".addbutton").hide();
					return false;
				}else if(response1.trim() == "1") {
					$(".addbutton").show();
				}else{
					$(".addbutton").hide();
				}
			}
		});
	}
	function get_batch_qty(id){
		/*var stock = $("#batch_id").find(':selected').attr("data-stock");
		$("#batch_stock").val(stock);*/

		var batch_no = $("#batch_id").val();
		var sales_ordertrn_id = $("#sales_ordertrn_id").val();
		// var unit_id = $("#unit_id").val();
		// var product_id = $("#product_id").val();
		// var st_godown_id = $("#godown_id").val();
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/salesorder_deallocate_stock/',
			data: { 
				mode : "get_batch_qty",
				batch_no:batch_no,
				sales_ordertrn_id :sales_ordertrn_id
				// st_godown_id:st_godown_id,
				// unit_id:unit_id,
				// product_id:product_id
			},
			success: function(response)
			{
				var stock = response.trim();
				$("#batch_stock").val(response);
				Unloading();
				validate_qty(0);
			}
		});
	}

	function delete_batch_stock_entry(batchstockid,de_allo_trn_id="",batch_no="",stock_id=""){

		$.ajax({
			type: "POST",
			url: root_domain+ inventory_domain+'app/salesorder_deallocate_stock/',
			data: { mode : "delete_batch_entry",batchstockid:batchstockid,de_allo_trn_id:de_allo_trn_id,batch_no:batch_no,stock_id:stock_id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				var response1=data.res;
				if(response1.trim() == "1") {
					toastr.success("DATA DELETED SUCCESSFULLY", "SUCCESS");
					load_batch_datatable();			
					open_batch_wise_qty();	
				}
				else if(response1.trim() == "0") {
					toastr.warning("SOMETHING WENT WRONG", "WARNING");
					return false;
				}
				validate_qty(0);
			}
		});
	}



function open_approv_model(id,no){
	$('#preview_so_deallocate_approval_modal').modal('show');
	$('#de_allo_no').html(no);
	$('#de_allo_id').val(id);
	
	so_deallocate_data_load();
	so_deallocate_appr_hist();
}


function so_deallocate_data_load(){
	var de_allo_id = $('#de_allo_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: { mode : "load_so_deallocate_detail", de_allo_id:de_allo_id },
		success: function(resp){
		//console.log(resp);
		var resp=JSON.parse(resp);
		$('#detail_show').html(resp.detail_show);
		}
	});
}
function so_deallocate_appr_hist(){
	var de_allo_id = $('#de_allo_id').val();

		$("#order-pocard-history-datatable").dataTable({
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
			"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
			"iDisplayLength": 5,
			"sAjaxSource": root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "load_so_deallocate_appr_hist" }, { "name": "de_allo_id", "value": de_allo_id }  );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function add_so_apprv_hist(){
	
	var form_data = {
		mode:"add_so_apprv_hist",
		approve_status:$('#approve_status').val(),
		approve_remark:$('#approve_remark').val(),
		de_allo_id:$('#de_allo_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/salesorder_deallocate_stock/',
		data: form_data,
		success: function(response)
		{
			$('#approve_status').select2("val","0");
			$('#approve_remark').val("");
			load_salesorder_deallocate_stock_datatable();
			so_deallocate_data_load();
			so_deallocate_appr_hist();

			$('#preview_so_deallocate_approval_modal').modal('hide');
			Unloading();
		}
	});	
}	