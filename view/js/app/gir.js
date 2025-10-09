//var datatable;
$(document).ready(function() {
	load_gir_datatable();
	//show_data();
//	load_purhcase_order_data($('#purchaseorder_id').val());
	
	// validate vendor add form on keyup and submit
	$("#gir_add").validate({
		rules: {
			/*gir_no:{
				required: true			
			},
			gir_date: {
				required: true			
			},
			vender_id: {
				required: true			
			},*/
			gir_file: {
				required: true			
			}
		},
		messages: {
			/*gir_no:{
				required: "Enter gir No."			
			},
			gir_date: {
				required: "Enter gir Date"
			},
			vender_id: {
				required: "Choose Vendor"
			},*/
			gir_file: {
				required: "Choose File"
			}
		}
	}); 
});

$("#gir_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#gir_add").valid()) {
		return false;
	}

		if($("#challan_no").val()===""){
			toastr.warning("Please Enter Invoice no/Challan No", "ERROR")
			return false;
		}
	
	
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain+'app/gir/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("G.I.R. ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'gir_list'; 
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
				toastr.success("G.I.R. UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+'gir_list';
			}
			$('#gir_add').trigger('reset');
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
		url: root_domain+'app/gir/',
		data: { mode : "fieldadd",edit_id:$("#edit_id").val(),purchaseorder_id:$("#purchaseorder_id").val(),product_id:$("#product_id").val(),product_des:$("#product_des").val(),pro_mfg_date:$("#pro_mfg_date").val(),pro_exp_date:$("#pro_exp_date").val(),product_qty:$("#product_qty").val(),unit_id:$("#unitid").val(),gir_id:$("#eid").val(),product_qc:$('#product_qc').val() },
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
		url: root_domain+'app/gir/',
		data: { mode : "load_gir_trn_data", gir_id:eid },
		success: function(data){
			//console.log(data);
			$('#sale_productdata').html(data);		 
			Unloading();
		}		 
	}); 
}
function load_gir_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	var gir_type=$('#gir_type').val();
	var gir_bill_type=$('#gir_bill_type').val();
	
	datatable = $("#gir-table").dataTable({
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
		"sAjaxSource": root_domain+'app/gir/',
		"fnServerParams": function ( aoData ) {
		
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "date", "value": date },{ "name": "gir_type", "value": gir_type },{ "name": "gir_bill_type", "value": gir_bill_type });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function edit_gir_data(gir_trn_id)
{ 
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/gir/',
		data: { mode:"preedit", gir_trn_id:gir_trn_id },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
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
			$("#edit_id").val(gir_trn_id);
			$('#addrow').val('Update');
			Unloading();
		}
	});
}
function delete_gir_data(gir_trn_id,purchaseorder_id)
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/gir/',
			data: { mode:"delete_data", gir_trn_id:gir_trn_id, purchaseorder_id:purchaseorder_id },
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
function delete_gir(gir_id,purchaseorder_id)
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+'app/gir/',
			data: { mode:"delete_gir", gir_id:gir_id, purchaseorder_id:purchaseorder_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_gir_datatable();
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
	var gir_type=$('#gir_against').val();
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
		url: root_domain+'app/gir/',
		data: { mode:"load_purhcase_order_data", order_id:order_id , gir_type:gir_type,mode1:mode1,eid:eid,vender_id:vender_id,branch_id:branch_id },
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
			url: root_domain+'app/gir/',
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
			url: root_domain+'app/gir/',
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

function load_gir_no() {
	$.ajax({
		type: "POST",
		url: root_domain+'app/gir/',
		data: { mode : "load_gir_no" },
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#gir_no').val(no.invoiceno);
		}
	});
}
function delete_attch(gir_attch_id) {
	var conf = confirm("Are you sure want to Delete Receipt ?");
	if(conf){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/gir/',
			data: { mode : "delete_attch", gir_attch_id:gir_attch_id },
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
	var gir_type=$("#gir_against").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/gir/',
		data: { mode : "get_order_no", gir_type:gir_type,vender_id:vender_id },
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
	if(type==2){
		var conv_qty_hide=$("#gir_qty"+cnt).val();
			var s=parseFloat(conv_qty_hide);
			results = s.toFixed(3);
			
		var	num=$("#gir_qty_hide"+cnt).val();
			var d=parseFloat(num);
			resultb = d.toFixed(3);
		if(resultb===results){
			return false;
		}
		var conv_gir_qty_hide=$("#conv_gir_qty_hide"+cnt).val();
	}else{
		var base_qty_hide=$("#conv_gir_qty"+cnt).val();
			var d=parseFloat(base_qty_hide);
			resultb = d.toFixed(3);
		
		var base_qty_hidess=$("#conv_gir_qty_hide"+cnt).val();
			var s=parseFloat(base_qty_hidess);
			results = s.toFixed(3);
	
		if(resultb===results){
			return false;
		}
		var conv_qty_hide=$("#gir_qty"+cnt).val();
	}
	
	var base_qty=$("#conv_gir_qty"+cnt).val();
	var conv_qty=$("#gir_qty"+cnt).val();
	var product_id=$("#gir_pid"+cnt).val();
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+'app/gir/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
				if(type===1){
						$("#conv_gir_qty_hide"+cnt).val(base_qty);
				}else if(type===2){
					$("#gir_qty_hide"+cnt).val(conv_qty);
				}
				
				if(type===1){
					$("#gir_qty"+cnt).val(arr.show_qty);
					$("#gir_qty_hide"+cnt).val(arr.hide_qty);
				
				}else if(type===2){
					$("#conv_gir_qty"+cnt).val(arr.show_qty);
					$("#conv_gir_qty_hide"+cnt).val(arr.hide_qty);
					
				}else{
					$("#conv_gir_qty"+cnt).val(arr.show_qty);
					$("#conv_gir_qty_hide"+cnt).val(arr.hide_qty);
					$("#gir_qty"+cnt).val(arr.show_qty);
					$("#gir_qty_hide"+cnt).val(arr.hide_qty);
				}
			}
		});
	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#conv_gir_qty"+cnt).val("0");
		$("#conv_gir_qty_hide"+cnt).val("0");
		$("#gir_qty"+cnt).val("0");
		$("#gir_qty_hide"+cnt).val("0");
	}
}
function remove_data(count){
	var conf = confirm("Are you sure want to Remove?");
	if(conf){
		$("#trid"+count).html("");
	}
}

function get_bill_type(type_id,bill_type_id ='')
{
	
	Loading(true);
	$.ajax({
	type: "POST",
	url: root_domain+'app/gir/',
	data: { mode : "get_gir_bill_type_company",  type_id : type_id,bill_type_id:bill_type_id},
	success: function(response)
	{
		$("#gir_bill_type").html(response);
		Unloading();
		return false;
	}
	});
}


function get_vender_by_bill_type(bill_type_id,vender_id='')
{	
	Loading(true);
	var type_id = $("#gir_type").select2().val();
	
	$.ajax({
	type: "POST",
	url: root_domain+'app/gir/',
	data: { mode : "get_vender_by_bill_type",  type_id : type_id,bill_type_id:bill_type_id,vender_id:vender_id},
	success: function(response)
	{
		$("#vender_id").html(response);
		Unloading();
		return false;
	}
	});
}



