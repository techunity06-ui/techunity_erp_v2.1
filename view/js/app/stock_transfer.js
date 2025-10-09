//var datatable;
$(document).ready(function() {
	load_stock_transfer_datatable();
	show_data();
//	load_purhcase_order_data($('#purchaseorder_id').val());
	
	// validate vendor add form on keyup and submit
	$("#stock_transfer_add").validate({
		rules: {
			transfer_no:{
				required: true			
			},
			transfer_date: {
				required: true			
			}
		},
		messages: {
			transfer_no:{
				required: "Enter stock_transfer No."			
			},
			transfer_date: {
				required: "Enter stock_transfer Date"
			}
		}
	}); 
});

$("#stock_transfer_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#stock_transfer_add").valid()) {
		return false;
	}
	
	
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/stock_transfer/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("Transfer SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+"stock_transfer_list"; 
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg== 'update') {
				toastr.success("Transfer UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+'stock_transfer_list';
			}
			$('#stock_transfer_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function add_field()
{
	if(!$("#product_id").val()){
		toastr.warning("Select Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#transfer_qty").val()){		
		toastr.warning("Enter Qty", "ERROR");
		$("#transfer_qty").focus();
		return false;
	}
	else if(!$("#transfer_work_order_id").val()){		
		toastr.warning("Select Transfer Work Order", "ERROR");
		$("#transfer_work_order_id").select2('focus');
		return false;
	}
	else if(!$("#work_order_id").val()){		
		toastr.warning("Select Work Order", "ERROR");
		$("#work_order_id").select2('focus');
		return false;
	}
	var promaxval=$('#transfer_qty').attr("max");
	var rese_qty=$("#reserve_qty").val();
	var trans_qty=$("#transfer_qty").val();
	if(promaxval>=rese_qty){
		if(trans_qty>rese_qty){
			toastr.warning("Please Check Transfer Qty", "ERROR")
			return false;
		}
	}else{
		if(trans_qty>promaxval){
			toastr.warning("Please Check Transfer Qty", "ERROR")
			return false;
		}
	}
	
	/* if(parseInt(promaxval)<parseInt($("#product_qty").val()))
	{
		toastr.warning("Please Check Pending Qty", "ERROR")
		return false;
	} */
	$.ajax({
		type: "POST",
		url: root_domain+'app/stock_transfer/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),work_order_transfer_id:$("#eid").val(),product_id:$("#product_id").val(),work_order_id:$("#work_order_id").val(),reserve_qty:$("#reserve_qty").val(),transfer_work_order_id:$("#transfer_work_order_id").val(),transfer_qty:$("#transfer_qty").val() },
		success: function(response)
		{
			//console.log(response);
			$("#work_order_id").select2("val","");
			$("#transfer_work_order_id").select2("val","");
			$("#product_id").select2("val","");
			$("#product_id").select2('focus');
			//$("#work_order_id").val("");
			$("#reserve_qty").val("");
			$("#transfer_qty").val("");
			$("#transfer_qty").attr("placeholder","");
			$("#edit_id").val('');
			$('#addrow').val('Add');
			Unloading();
			show_data();
		}
	});
}	
function show_data() {
	var work_order_transfer_id = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/stock_transfer/',
		data: { mode : "load_stock_transfer_trn_data", work_order_transfer_id:work_order_transfer_id },
		success: function(data){
			//console.log(data);
			$('#sale_productdata').html(data);		 
			Unloading();
		}		 
	}); 
}
function load_stock_transfer_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	
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
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/stock_transfer/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "date", "value": date });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function edit_stock_transfer_data(stock_transfer_trn_id)
{ 
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/stock_transfer/',
		data: { mode:"preedit", stock_transfer_trn_id:stock_transfer_trn_id },
		success: function(response)
		{
			//console.log(response)
			
			var data = jQuery.parseJSON(response);
			/*$('#purchaseorder_id').html(data.po_html_resp);
			$("#purchaseorder_id").select2("val",data.purchaseorder_id);*/
			$('#product_id').html(data.pro_html_resp);
			$("#product_id").select2("val",data.product_id);
			$("#unitid").select2("val",data.unit_id);
			$("#product_des").val(data.description);
			$("#product_hsn_code").val(data.product_hsn_code);
			$("#pro_entry_date").datepicker("setDate", data.pro_entry_date);
			if(data.pro_mfg_date){
				$("#pro_mfg_date").datepicker("setDate", data.pro_mfg_date);
			}
			else{
				$("#pro_mfg_date").val("");
			}
			
			if(data.pro_exp_date){
				$("#pro_exp_date").datepicker("setDate", data.pro_exp_date);
			}
			else{
				$("#pro_exp_date").val("");
			}
			
			$("#product_qty").val(data.product_qty);
			$("#edit_id").val(stock_transfer_trn_id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}
function delete_stock_transfer_data(stock_transfer_trn_id,purchaseorder_id)
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/stock_transfer/',
			data: { mode:"delete_data", stock_transfer_trn_id:stock_transfer_trn_id, purchaseorder_id:purchaseorder_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					show_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function delete_stock_transfer(stock_transfer_id,purchaseorder_id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/stock_transfer/',
			data: { mode:"delete_stock_transfer", stock_transfer_id:stock_transfer_id, purchaseorder_id:purchaseorder_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_stock_transfer_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function load_purhcase_order_data(){
	
	//alert('hii');
	//alert(order_id);
	var order_id=$('#purchaseorder_id').val();
	var stock_transfer_type=$('#stock_transfer_against').val();
	var mode1=$('#mode').val();
	var eid=$('#eid').val();
	var vender_id=$('#vender_id').val();
	var pmode=$('#pmode').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/stock_transfer/',
		data: { mode:"load_purhcase_order_data", order_id:order_id , stock_transfer_type:stock_transfer_type,mode1:mode1,eid:eid,vender_id:vender_id },
		success: function(response){
			//alert(response);
			//console.log(response);
			var resp = 	JSON.parse(response);
			/*$('#product_id').html(resp.pro_html);
			$('#product_id').select2('val','');
			$('#product_id').select2('focus');*/
			$('#field1').html(resp.pro_html);
			if(pmode==="padd"){
				$('#vender_id').val(resp.vendor_id);
				$('#vender_name').val(resp.vendor_name);
			}
			//$('#request_no').val(resp.request_id);
			Unloading();
		}
	});
		
}

function load_productdetail(product_id) {
	var purchaseorder_id = $("#purchaseorder_id").val();
	if(purchaseorder_id){
		$.ajax({
			type: "POST",
			url: root_domain+'app/stock_transfer/',
			data: { mode:"load_productdetail", product_id:product_id, purchaseorder_id:purchaseorder_id },
			success: function(response)
			{
				//console.log(response);
				var obj=jQuery.parseJSON(response);
				//alert(obj.product_qc);
				$('#product_des').val(obj.description);
				$('#product_qty').attr("placeholder",obj.pending_qty);
				$('#product_qty').attr("max",obj.pending_qty);
				
				$('#unitid').select2("val",obj.unit_id);
				$('#product_rate').val(obj.product_rate);
				$('#taxable_value').val(obj.taxable_value);
				$('#formulaid').val(obj.formulaid);
				$('#product_amount').val(obj.product_amount);
				$('#product_qc').val(obj.product_qc);
			}
		});
	}
	else{
		toastr.warning("Choose Purchase Order !!!", "WARNING");
		$('#purchaseorder_id').select2('focus');
	}
} 
function load_po_ven_wise(vender_id) {
	//alert(vender_id);
	if(vender_id){
		$.ajax({
			type: "POST",
			url: root_domain+'app/stock_transfer/',
			data: { mode:"load_po_ven_wise", vender_id:vender_id },
			success: function(response)
			{
				//alert(response);
				//console.log(response);
				var obj=jQuery.parseJSON(response);
				$('#purchaseorder_id').html(obj.pro_html);
				$('#purchaseorder_id').select2("val","");
			}
		});
	}
}

function load_stock_transfer_no() {
	$.ajax({
		type: "POST",
		url: root_domain+'app/stock_transfer/',
		data: { mode : "load_stock_transfer_no" },
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#stock_transfer_no').val(no.invoiceno);
		}
	});
}
function delete_attch(stock_transfer_attch_id) {
	var conf = confirm("Are you sure want to Delete Receipt ?");
	if(conf){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/stock_transfer/',
			data: { mode : "delete_attch", stock_transfer_attch_id:stock_transfer_attch_id },
			success: function(response){
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("ATTACHMENT DELETED SUCCESSFULLY", "SUCCESS");
					location.reload();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();
			}
		}); 
	}
}

function get_order_no()
{
	//alert(gno);
	var product_id=$("#product_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/stock_transfer/',
		data: { mode : "get_order_no", product_id:product_id},
		success: function(response){
			//alert(response);
			console.log(response);
			$('#work_order_id').html(response);
			$('#transfer_work_order_id').html(response);
		}
	});
}
function reserve_stock_check()
{
	//alert(gno);
	var work_order_id=$("#work_order_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/stock_transfer/',
		data: { mode : "get_reserve_stock", work_order_id:work_order_id},
		success: function(response){
			//alert(response);
			//console.log(response);
			$('#reserve_qty').val(response);
		}
	});
}
function pending_reserve_stock_check()
{
	//alert(gno);
	var work_order_id=$("#transfer_work_order_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/stock_transfer/',
		data: { mode : "get_pending_reserve_stock", work_order_id:work_order_id},
		success: function(response){
			
			//console.log(response);
			//$('#reserve_qty').val(response);
			$("#transfer_qty").attr("MAX",response);
			$("#transfer_qty").attr("placeholder",response);
		}
	});
}
function edit_grn_data(work_order_transfer_trn_id)
{ 
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/grn/',
		data: { mode:"preedit", work_order_transfer_trn_id:work_order_transfer_trn_id },
		success: function(response)
		{
			//console.log(response)
			
			var data = jQuery.parseJSON(response);
			/*$('#purchaseorder_id').html(data.po_html_resp);
			$("#purchaseorder_id").select2("val",data.purchaseorder_id);*/
			//$('#product_id').html(data.pro_html_resp);
			//$("#product_id").select2("val",data.product_id);
			$("#unitid").select2("val",data.unit_id);
			$("#product_id").val(data.description);
			$("#work_order_id").val(data.product_hsn_code);
			$("#pro_entry_date").datepicker("setDate", data.pro_entry_date);
			
			
			$("#edit_id").val(grn_trn_id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}
function delete_grn_data(work_order_transfer_trn_id)
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/stock_transfer/',
			data: { mode:"delete_data", work_order_transfer_trn_id:work_order_transfer_trn_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					show_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}