//var datatable;
$(document).ready(function() {
	load_grn_datatable();
	//show_data();
//	load_purhcase_order_data($('#purchaseorder_id').val());

	// validate vendor add form on keyup and submit
	$("#grn_add").validate({
		rules: {
			grn_no:{
				required: true			
			},
			grn_date: {
				required: true			
			},
			vender_id: {
				required: true			
			},
			grn_file: {
				required: true			
			}
		},
		messages: {
			grn_no:{
				required: "Enter GRN No."			
			},
			grn_date: {
				required: "Enter GRN Date"
			},
			vender_id: {
				required: "Choose Vendor"
			},
			grn_file: {
				required: "Choose File"
			}
		}
	}); 
});

$("#grn_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#grn_add").valid()) {
		return false;
	}
	if($("#invoice_no").val()===""){
		if($("#challan_no").val()===""){
			toastr.warning("Please Enter Invoice no/Challan No", "ERROR")
			return false;
		}
	}
	
		var so_stock=(document.getElementsByName('grn_qty[]'));
		var cnt=so_stock.length;
		var so_stock1=0;
		for(var i=0;i<cnt;i++)
		{
			if(so_stock[i].value > 0){
				so_stock1 += parseFloat(so_stock[i].value);
			}
		} 
		if(so_stock1<="0"){
			toastr.warning("Enter Any One", "WARNING"); 
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
		url: root_domain+production_domain+'app/grn/',
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
				toastr.success("G.R.N. ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+production_domain+arr.back; 
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
				toastr.success("G.R.N. UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+production_domain+'grn_list';
			}
			$('#grn_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function add_field()
{
	if(!$("#purchaseorder_id").val()){		
		toastr.warning("Choose Purchase Order", "ERROR");
		$("#purchaseorder_id").focus();
		return false;
	}
	else if(!$("#product_id").val()){
		toastr.warning("Select Product", "ERROR");
		$("#product_id").select2('focus');
		return false;
	}
	else if(!$("#product_qty").val()){		
		toastr.warning("Enter Qty", "ERROR");
		$("#product_qty").focus();
		return false;
	}
	else if(!$("#unitid").val()){		
		toastr.warning("Select Unit", "ERROR");
		$("#unitid").select2('focus');
		return false;
	}
	var promaxval=$('#product_qty').attr("max");
	
	if(parseInt(promaxval)<parseInt($("#product_qty").val()))
	{
		toastr.warning("Please Check Pending Qty", "ERROR")
		return false;
	}
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/grn/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),purchaseorder_id:$("#purchaseorder_id").val(),product_id:$("#product_id").val(),product_des:$("#product_des").val(),pro_mfg_date:$("#pro_mfg_date").val(),pro_exp_date:$("#pro_exp_date").val(),product_qty:$("#product_qty").val(),unit_id:$("#unitid").val(),grn_id:$("#eid").val(),product_qc:$('#product_qc').val() },
		success: function(response)
		{
			//console.log(response);
			$("#product_id").select2("val","");
			$("#product_id").select2('focus');
			$("#product_des").val("");
			$("#product_qty").val("");
			$("#product_qty").attr("placeholder","");
			$("#unitid").select2('val','');
			$("#edit_id").val('');
			$('#addrow').val('Add');
			Unloading();
			show_data();
		}
	});
}	
function show_data() {
	var eid = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/grn/',
		data: { mode : "load_grn_trn_data", grn_id:eid },
		success: function(data){
			//console.log(data);
			$('#sale_productdata').html(data);		 
			Unloading();
		}		 
	}); 
}
function load_grn_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	var grn_against=$('#grn_against').val();
	
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
		"sAjaxSource": root_domain+production_domain+'app/grn/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "date", "value": date },{ "name": "grn_against", "value": grn_against });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function edit_grn_data(grn_trn_id)
{ 
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/grn/',
		data: { mode:"preedit", grn_trn_id:grn_trn_id },
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
			$("#edit_id").val(grn_trn_id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}
function delete_grn_data(grn_trn_id,purchaseorder_id)
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/grn/',
			data: { mode:"delete_data", grn_trn_id:grn_trn_id, purchaseorder_id:purchaseorder_id },
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
function delete_grn(grn_id,purchaseorder_id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/grn/',
			data: { mode:"delete_grn", grn_id:grn_id, purchaseorder_id:purchaseorder_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_grn_datatable();
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
	var grn_type=$('#grn_against').val();
	var mode1=$('#mode').val();
	var eid=$('#eid').val();
	var vender_id=$('#vender_id').val();
	var pmode=$('#pmode').val();
	var branch_id=$('#branch_id').val();
	if(branch_id==""){
		toastr.warning("Select Branch Name", "WARNING");
		return false;
	}
	//alert(vender_id);
	//alert(order_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/grn/',
		data: { mode:"load_purhcase_order_data", order_id:order_id , grn_type:grn_type,mode1:mode1,eid:eid,vender_id:vender_id,branch_id:branch_id },
		success: function(response){
			//alert(response);
			//console.log(response);
			var resp = 	JSON.parse(response);
			/*$('#product_id').html(resp.pro_html);
			$('#product_id').select2('val','');
			$('#product_id').select2('focus');*/
			$('#field1').html(resp.pro_html);
			if(pmode==="padd"){
				//$('#vender_id').val(resp.vendor_id);
				//$('#vender_name').val(resp.vendor_name);
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
			url: root_domain+production_domain+'app/grn/',
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
			url: root_domain+production_domain+'app/grn/',
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

function load_grn_no() {
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/grn/',
		data: { mode : "load_grn_no" },
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#grn_no').val(no.invoiceno);
		}
	});
}
function delete_attch(grn_attch_id) {
	var conf = confirm("Are you sure want to Delete Receipt ?");
	if(conf){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/grn/',
			data: { mode : "delete_attch", grn_attch_id:grn_attch_id },
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
	var vender_id=$("#vender_id").val();
	var grn_type=$("#grn_against").val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/grn/',
		data: { mode : "get_order_no", grn_type:grn_type,vender_id:vender_id },
		success: function(response){
			//alert(response);
			//console.log(response);
			$('#purchaseorder_id').html(response);
		}
	});
}

$(document).on('keyup','.qty_mangement', function(){
	$enter_val = $(this).val();
	$exist_qty = $(this).attr('data-qty');
	$tolerance = $(this).attr('data-tol');
	$min_tolerance = $(this).attr('data-mini-tol');
	$max_tolerance = $(this).attr('data-max-tol');
	$pending_qty = $(this).attr('data-pendingqty');
	//alert($tolerance);
	if($tolerance=='1'){
		$minimum_tol_val = parseFloat($exist_qty)*parseFloat($min_tolerance)/100; 
		$mini_toll = parseFloat($exist_qty)-parseFloat($minimum_tol_val);

		$maximum_tol_val = parseFloat($exist_qty)*parseFloat($max_tolerance)/100; 
		$max_toll = parseFloat($exist_qty) + parseFloat($maximum_tol_val);

		$take_qty = parseFloat($exist_qty) - parseFloat($pending_qty);
		$remain_qty = parseFloat($max_toll) - parseFloat($take_qty)
		
		//console.log($mini_toll);console.log($max_toll);console.log($max_toll);
		if(parseFloat($enter_val) > parseFloat($remain_qty)){
			toastr.warning("Please enter your quantity less than "+$remain_qty, "WARNING");
			$(this).attr('max', $remain_qty);
			return false;
		}
		
	}else{
		if(parseFloat($enter_val)>parseFloat($pending_qty)){
			$(this).val(0);
			toastr.warning("Please enter your quantity less than "+$pending_qty, "WARNING");
			return false;
		}
	}
});
function product_convert_qty(type,cnt){
	
	$("#")
	//alert(type);
	if(type==2){
		var conv_qty_hide=$("#grn_qty"+cnt).val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(4);
		
		var	num=$("#grn_qty_hide"+cnt).val();
		var d=parseFloat(num);
		resultb = d.toFixed(4);
		if(resultb===results){
			return false;
		}
		var conv_grn_qty_hide=$("#conv_grn_qty_hide"+cnt).val();
	}else{
		var base_qty_hide=$("#conv_grn_qty"+cnt).val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(4);
		
		var base_qty_hidess=$("#conv_grn_qty_hide"+cnt).val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(4);
		
		if(resultb===results){
			return false;
		}
		var conv_qty_hide=$("#grn_qty"+cnt).val();
	}
	
	var base_qty=$("#grn_qty"+cnt).val();
	var conv_qty=$("#conv_grn_qty"+cnt).val();
	var product_id=$("#grn_pid"+cnt).val();
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/grn/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
				if(type===1){
					$("#conv_grn_qty_hide"+cnt).val(conv_qty);
				}else if(type===2){
					$("#grn_qty_hide"+cnt).val(base_qty);
				}
				
				if(type===1){
					$("#grn_qty"+cnt).val(arr.show_qty);
					$("#grn_qty_hide"+cnt).val(arr.hide_qty);
				}else if(type===2){
					$("#conv_grn_qty"+cnt).val(arr.show_qty);
					$("#conv_grn_qty_hide"+cnt).val(arr.hide_qty);
				}else{
					$("#conv_grn_qty"+cnt).val(arr.show_qty);
					$("#conv_grn_qty_hide"+cnt).val(arr.hide_qty);
					$("#grn_qty"+cnt).val(arr.show_qty);
					$("#grn_qty_hide"+cnt).val(arr.hide_qty);
				}
			}
		});
	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#conv_grn_qty"+cnt).val("0");
		$("#conv_grn_qty_hide"+cnt).val("0");
		$("#grn_qty"+cnt).val("0");
		$("#grn_qty_hide"+cnt).val("0");
	}
}
function remove_data(count){
	var conf = confirm("Are you sure want to Remove?");
	if(conf){
		$("#trid"+count).html("");
	}
}