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
			},
			receive_datetime:{
				required: true		
			},
			// is_conversation:{
			// 	required: true			
			// }
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
			},
			receive_datetime:{
				required: "Select Date & Time"		
			},
			// is_conversation:{
			// 	required: "Select Conversation "			
			// }
		}
	}); 
});

$("#grn_add").on('submit',function(e) {
	var grn_against = $("#grn_against").val();
	// var job_work_po_trn_id = $("#job_work_po_trn_id").val();
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

		var so_stock_tmp=(document.getElementsByName('grn_qty_tmp[]'));
		var cnt=so_stock_tmp.length;
		var so_stock_tmp1=0;
		for(var i=0;i<cnt;i++)
		{
			if(so_stock_tmp[i].value > 0){
				so_stock_tmp1 += parseFloat(so_stock_tmp[i].value);
			}
		} 
		if(so_stock1<="0" && so_stock_tmp1<="0" ){
			// toastr.warning("Enter Any One", "WARNING"); 
			toastr.warning("Enter Quantity", "WARNING"); 
			  return false;
		}
var errorlog=0;
	if($("#mode").val()=="Edit"){

		// var total_qty=0;
		$('input.rate_unit').each(function(index){ 
			var cnt = index + 1;

			var batch_qty = $("#batch_total_qty"+cnt).val();

			var rate_unit = $(this).val();
			var unit_id = $("#unit_id"+cnt).val();
			var conv_unit_id = $("#conv_unit_id"+cnt).val();

			var qty = 0;
			if(rate_unit == conv_unit_id){
				qty = $("#conv_grn_qty"+cnt).val();
			}else{
				qty =$("#grn_qty"+cnt).val(); 
			}
			console.log(batch_qty + " = "+ qty);
			if(parseFloat(batch_qty) > parseFloat(qty)){
				errorlog +=parseFloat(1);
			  // toastr.warning("Please check batch qty", "WARNING"); 
			   $("#trid"+cnt+"").css("background-color", "#ffbfbf");					
			}else{
				$("#trid"+cnt+"").css("background-color", "");
			}

		});

		
	}

	if(errorlog>"0"){
			toastr.warning("Please check batch qty", "WARNING"); 
			return false;
		}
	// return false;

	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	// alert($("#mode").val())
	$.ajax({
		cache:false,
		url: root_domain+inventory_domain+'app/grn/',
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
				if(grn_against == '1' || grn_against == '8'){
					window.location=root_domain+production_domain+arr.back; 
				}else{
					window.location=root_domain+inventory_domain+arr.back; 	
				}
				
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
				window.location=root_domain+inventory_domain+'grn_list';
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
		url: root_domain+inventory_domain+'app/grn/',
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
		url: root_domain+inventory_domain+'app/grn/',
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
		"sAjaxSource": root_domain+inventory_domain+'app/grn/',
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
		url: root_domain+inventory_domain+'app/grn/',
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
	var res= confirm(" Are you want to delete ?");
	
	if(res) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/grn/',
			data: { mode:"delete_data", grn_trn_id:grn_trn_id, purchaseorder_id:purchaseorder_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					// show_data();
					load_purhcase_order_data();
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
			url: root_domain+inventory_domain+'app/grn/',
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
	var eid=$('#eid').val();vender_id
	var vender_id=$('#vender_id').val();
	var pmode=$('#pmode').val();
	var branch_id=$('#branch_id').val();
	if(branch_id==""){
		toastr.warning("Select Branch Name", "WARNING");
		return false;
	}
	/*$(".select2").select2({
				width: '100%'
			}); */
	//alert(vender_id);
	//alert(order_id);
	
	//alert(eid);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { 
			mode:"load_purhcase_order_data", 
			order_id:order_id , 
			grn_type:grn_type,
			mode1:mode1,
			eid:eid,
			vender_id:vender_id,
			branch_id:branch_id
			 },
		success: function(response){
			//alert(response);
			//console.log(response);
			var resp = 	JSON.parse(response);
			
		
			/*$('#product_id').html(resp.pro_html);
			$('#product_id').select2('val','');
			$('#product_id').select2('focus');*/
			$('#field1').empty().html(resp.pro_html);
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
			url: root_domain+inventory_domain+'app/grn/',
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
			url: root_domain+inventory_domain+'app/grn/',
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
	var grn_type = $("#grn_against").val();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { mode : "load_grn_no",grn_type:grn_type },
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
			url: root_domain+inventory_domain+'app/grn/',
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
	//alert(grn_type);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { mode : "get_order_no", grn_type:grn_type,vender_id:vender_id },
		success: function(response){
			//alert(response);
			//console.log(response);
			$('#purchaseorder_id').empty().html(response);
			if(grn_type == '5'){
				$('#outside_so_id').empty().html(response);
				$('#outside_so_id').select2("val","");
			}
			if(grn_type == 3){
				$("#godow").hide();
			}else{
				$("#godow").show();
			}
			load_purhcase_order_data();
		}
	});
}
$(document).on('keyup','.baseqty_mangement', function(){

	var base_pending_qty = $(this).attr('data-pendingcqty');
	var base_qty = $(this).val();
	if(parseFloat(base_pending_qty)<parseFloat(base_qty)){
		$(this).val(0);
		toastr.warning("Please enter your quantity less than "+base_pending_qty, "WARNING");
		return false;
	}
});

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
		
		// console.log($mini_toll);console.log($max_toll);console.log($max_toll);
		if(parseFloat($enter_val) > parseFloat($remain_qty)){
			toastr.warning("Please enter your quantity less than "+$remain_qty, "WARNING");
			$(this).attr('max', $remain_qty);
			return false;
		}		
	}else{
	/*	var conversation = $("#is_conversation").val();
	
	
	
		if(conversation == 2)
		{
			if(parseFloat($enter_val)>parseFloat($pending_qty)){
			$(this).val(0);
			toastr.warning("Please enter your quantity less than "+$pending_qty, "WARNING");
			return false;
			}
		}*/

		
		// if($("input[name='is_conversation']:checked").val() == 0)
		// {
			// alert($enter_val + ' -- ' + $pending_qty)
			if(parseFloat($enter_val)>parseFloat($pending_qty)){
			$(this).val(0).trigger('onkeyup');
			toastr.warning("Please enter your quantity less than "+$pending_qty, "WARNING");
			return false;
			}
		// }
		
	}
});

// $(document).on('keyup','.handle_qty', function(){

// 	$enter_val = $(this).val();
// 	$exist_qty = $(this).attr('data-qty');
// 	$tolerance = $(this).attr('data-tol');
// 	$min_tolerance = $(this).attr('data-mini-tol');
// 	$max_tolerance = $(this).attr('data-max-tol');
// 	$pending_qty = $(this).attr('data-pendingqty');
// 	//alert($tolerance);
// 	if($tolerance=='1'){
// 		$minimum_tol_val = parseFloat($exist_qty)*parseFloat($min_tolerance)/100; 
// 		$mini_toll = parseFloat($exist_qty)-parseFloat($minimum_tol_val);

// 		$maximum_tol_val = parseFloat($exist_qty)*parseFloat($max_tolerance)/100; 
// 		$max_toll = parseFloat($exist_qty) + parseFloat($maximum_tol_val);

// 		$take_qty = parseFloat($exist_qty) - parseFloat($pending_qty);
// 		$remain_qty = parseFloat($max_toll) - parseFloat($take_qty)
		
// 		//console.log($mini_toll);console.log($max_toll);console.log($max_toll);
// 		if(parseFloat($enter_val) > parseFloat($remain_qty)){
// 			toastr.warning("Please enter your quantity less than "+$remain_qty, "WARNING");
// 			$(this).attr('max', $remain_qty);
// 			return false;
// 		}		
// 	}else{
// 	/*	var conversation = $("#is_conversation").val();
	
	
	
// 		if(conversation == 2)
// 		{
// 			if(parseFloat($enter_val)>parseFloat($pending_qty)){
// 			$(this).val(0);
// 			toastr.warning("Please enter your quantity less than "+$pending_qty, "WARNING");
// 			return false;
// 			}
// 		}*/

		
// 		// if($("input[name='is_conversation']:checked").val() == 0)
// 		// {
// 			// alert($enter_val + ' -- ' + $pending_qty)
// 			if(parseFloat($enter_val)>parseFloat($pending_qty)){
// 			$(this).val(0);
// 			toastr.warning("Please enter your quantity less than "+$pending_qty, "WARNING");
// 			return false;
// 			}
// 		// }
		
// 	}
// });


/*function product_convert_qty(type,cnt){
	if($("input[name='is_conversation']:checked").val() == 0){
		if(type===1){
					$("#conv_grn_qty_hide"+cnt).val($("#conv_grn_qty"+cnt).val());
				}else if(type===2){
					$("#grn_qty_hide"+cnt).val($("#grn_qty"+cnt).val());
				}
		if($("#conv_unit_id"+cnt).val() == $("#unit_id"+cnt).val()){
			if(type===1){
					$("#grn_qty"+cnt).val($("#conv_grn_qty"+cnt).val())
					$("#grn_qty_hide"+cnt).val($("#conv_grn_qty"+cnt).val());
					
				}else if(type===2){
					$("#conv_grn_qty"+cnt).val($("#grn_qty"+cnt).val());
					$("#conv_grn_qty_hide"+cnt).val($("#grn_qty"+cnt).val());
				}
		}
		return false;
	}
	if(type==2){
		var conv_qty_hide=$("#grn_qty"+cnt).val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(5);
		
		var	num=$("#grn_qty_hide"+cnt).val();
		var d=parseFloat(num);
		resultb = d.toFixed(5);
		if(resultb===results){
			return false;
		}
		var conv_grn_qty_hide=$("#conv_grn_qty_hide"+cnt).val();
	}else{
		var base_qty_hide=$("#conv_grn_qty"+cnt).val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(5);
		
		var base_qty_hidess=$("#conv_grn_qty_hide"+cnt).val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(5);
		
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
			url: root_domain+inventory_domain+'app/grn/',
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
}*/


function product_convert_qty(type,cnt){

	if($("input[name='is_conversation']:checked").val() == 0){

		if(type===2){
					$("#conv_grn_qty_hide"+cnt).val($("#conv_grn_qty"+cnt).val());
				}else if(type===1){
					$("#grn_qty_hide"+cnt).val($("#grn_qty"+cnt).val());
				}
		if($("#conv_unit_id"+cnt).val() == $("#unit_id"+cnt).val()){
			if(type===2){
					$("#grn_qty"+cnt).val($("#conv_grn_qty"+cnt).val())
					$("#grn_qty_hide"+cnt).val($("#conv_grn_qty"+cnt).val());
					
				}else if(type===1){
					$("#conv_grn_qty"+cnt).val($("#grn_qty"+cnt).val());
					$("#conv_grn_qty_hide"+cnt).val($("#grn_qty"+cnt).val());
				}
		}
		return false;
	}


	var base_unit = $("#unit_id"+cnt).val(); 
	var conv_unit =  $("#conv_unit_id"+cnt).val();

	if(type==2){
		var conv_qty_hide=$("#conv_grn_qty"+cnt).val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var	num=$("#conv_grn_qty_hide"+cnt).val();
		var d=parseFloat(num);
		resultb = d.toFixed(3);

		/*if(resultb===results){
			return false;
		}*/
		var base_qty_hide=$("#grn_qty_hide"+cnt).val();
	}else{
		var base_qty_hide=$("#grn_qty"+cnt).val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(3);
		
		var base_qty_hidess=$("#grn_qty_hide"+cnt).val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(3);

		/*if(resultb===results){
			return false;
		}*/
		var conv_qty_hide=$("#conv_grn_qty"+cnt).val();
	}
	
	var base_qty=$("#grn_qty"+cnt).val();
	var conv_qty=$("#conv_grn_qty"+cnt).val();
	
	//var base_qty_hide=$("#grn_qty_hide").val();
	//var conv_qty_hide=$("#conv_grn_qty_hide").val();
	
	//var base_qty=$("#grn_qty").val();
	
	//var conv_qty=$("#conv_grn_qty").val();
	var product_id=$("#grn_pid"+cnt).val();
	console.log(base_unit + " = "  + conv_unit);
	if(base_unit == conv_unit){
		if(type===1){
			$("#conv_grn_qty"+cnt).val(base_qty);
			$("#conv_grn_qty_hide"+cnt).val(base_qty);
			$("#grn_qty_hide"+cnt).val(base_qty);

		}else if(type===2){
			$("#grn_qty"+cnt).val(conv_qty);
			$("#grn_qty_hide"+cnt).val(conv_qty);				
			$("#conv_grn_qty_hide"+cnt).val(conv_qty);
		}
		return false;
	}
	
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/grn/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
				//arr.show_qty
				//arr.hide_qty
				//alert(type);
				//alert(arr.show_qty);
				//alert(arr.hide_qty);
				console.log(arr);
				if(type===1){
					$("#grn_qty_hide"+cnt).val(base_qty.trim());
				}else if(type===2){
					$("#conv_grn_qty_hide"+cnt).val(conv_qty.trim());
				}
				
				if(type===1){
					$("#conv_grn_qty"+cnt).val((arr.show_qty).trim());
					$("#conv_grn_qty_hide"+cnt).val(arr.hide_qty);

				}else if(type===2){
					$("#grn_qty"+cnt).val((arr.show_qty).trim());
					$("#grn_qty_hide"+cnt).val(arr.hide_qty);				
					
				}else{
					$("#grn_qty"+cnt).val((arr.show_qty).trim());
					$("#grn_qty_hide"+cnt).val(arr.hide_qty);
					$("#conv_grn_qty"+cnt).val((arr.show_qty).trim());
					$("#conv_grn_qty_hide"+cnt).val(arr.hide_qty);
				}
			}
		});

	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#grn_qty"+cnt).val("1");
		$("#grn_qty_hide"+cnt).val("1");
		$("#conv_grn_qty"+cnt).val("1");
		$("#conv_grn_qty_hide"+cnt).val("1");
	}
}


function remove_data(count){
	var conf = confirm("Are you sure want to Remove?");
	if(conf){
		$("#trid"+count).html("");
	}
}


function remove_direct_grn_data(grn_trn_id){
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/grn/',
			data: { mode:"delete_data", grn_trn_id:grn_trn_id},
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_purhcase_order_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function remove_returnable_chalan_data(returnable_channal_trn_id){
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/grn/',
			data: { mode:"remove_returnable_chalan_data", returnable_channal_trn_id:returnable_channal_trn_id},
			success: function(response)
			{
				//console.log(response);
				// var data=jQuery.parseJSON(response);
				// var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					load_purhcase_order_data();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function show_batch_data(cnt,main_pending_qty,product_id,product_name,unit_name,unit_id,is_diff_unit,diff_qty,diff_unit_name,diff_unit_id,diff_unit_type,temp="",ptrn=0){
	
	var grn_no = $("#grn_no").val();
	
	// var qty = $("#conv_grn_qty"+cnt).val();
	var qty = $(".entered_qty"+cnt).val();
	var qty_diff = 0;
	if(diff_unit_type == 'conv'){
		qty_diff = $("#grn_qty_hide"+cnt).val();
		if(temp == "temp"){
			qty_diff = $("#grn_qty_tmp_hide"+cnt).val();
		}
		
	}else{
		qty_diff = $("#conv_grn_qty_hide"+cnt).val();
		if(temp == "temp"){
			qty_diff = $("#conv_grn_qty_tmp_hide"+cnt).val();
		}
		
	}
	console.log(qty)
	console.log(diff_qty)
	if(qty == '' || qty == 0 )
	{
		toastr.warning("Please Enter Qty Or Greater than 0 qty", "WARNING");
		return false;
	}
	
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { 
			mode : "batch_model_open",
			qty:qty,
			main_pending_qty:main_pending_qty,
			grn_no:grn_no,
			product_id:product_id,
			diff_qty : qty_diff,
			batch_unit : unit_id,
			is_diff_unit : is_diff_unit,
			diff_unit_id : diff_unit_id,
			purchaseordertrn_id : ptrn
		},
		success: function(response)
		{			
			$('#bs-batch-modal').modal('show');
			$("#main_product_qty").val(main_pending_qty);
			$("#grn_no").val(grn_no);
			$('#batch_product_name').text(product_name);
			$('#batch_qty_show').text(qty);
			$('#batch_unit_name').text(unit_name);
			$('#batch_unit_id').val(unit_id);
			$('#is_diff_unit').val(is_diff_unit);
			$('#diff_unit_type').val(diff_unit_type);

			$('#diff_batch_unit_id').val(diff_unit_id);
			$('#diff_batch_qty_show').text(qty_diff);
			$('#diff_batch_unit_name').text(diff_unit_name);
			$('#purchaseordertrn_id').val(ptrn);

			if(is_diff_unit){
				$(".diff_unit").show()
			}else{
				$(".diff_unit").hide()
			}
			
			$("#product_id").val(product_id);
			$("#selected_row_cnt").val(cnt);
			
			$("#batch_data").html(response);
				$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			}); 			
		}
	});
} 

function validate_batch_data(){
	// var main_qty=$("#main_product_qty").val();	
	var main_qty=$("#enter_qty").val();	
	var diff_qty=$("#enter_diff_qty").val();	
	var is_diff_unit = $("#is_diff_unit").val();
	var product_id=$("#product_id").val();	
	var total_delivery_qty=document.getElementsByName('batch_qty[]');
	if(is_diff_unit == '1'){
		var diff_total_delivery_qty=document.getElementsByName('diff_batch_qty[]');
	}
	var total_arry_sr=document.getElementsByName('arry_sr[]');
	var cnt=total_delivery_qty.length;
	
	var grandtotal_delivery_qty=0;
	var diff_grandtotal_delivery_qty=0;
	var count=$("#count").val();

	var batch_stock = $("#batch_stock").val();
	var grn_no = $("#grn_no").val();

	
	var batch_unit_name = $("#batch_unit_name").text();
	var diff_batch_unit_name = $("#diff_batch_unit_name").text();

	var batch_no = "";
	if(batch_stock == '1'){
		var format_value = $("#format_value").val();
		var end_format_value = $("#end_format_value").val();
		var taxinvoice_start = $("#taxinvoice_start").val();

	}
	
	main_qty=parseFloat(main_qty).toFixed(5);
	var qval="0";
	for(var i=0;i<cnt;i++)
	{	
		grandtotal_delivery_qty+=parseFloat(total_delivery_qty[i].value);

		
		var grandtotal_delivery_qty_new=grandtotal_delivery_qty;
		grandtotal_delivery_qty_new=parseFloat(grandtotal_delivery_qty_new).toFixed(5);

		if(is_diff_unit == '1'){
			diff_grandtotal_delivery_qty+=parseFloat(diff_total_delivery_qty[i].value);
			var diff_grandtotal_delivery_qty_new=diff_grandtotal_delivery_qty;
			diff_grandtotal_delivery_qty_new=parseFloat(diff_grandtotal_delivery_qty_new).toFixed(5);
		}
		
		

		
		/*var count1=total_arry_sr[i].value;
		
		
		
		//alert(count1);
		//alert(qval);
		if(count1!="1"){
			if(qval==="1"){
				//alert(qval);
				//alert(count1)
				$('#field'+count1).html('');
			}
		}
		if(parseFloat(grandtotal_delivery_qty_new)>=parseFloat(main_qty)){
			qval="1";
		}else{
			qval="0";
		}*/
	}
	var total=parseFloat(grandtotal_delivery_qty).toFixed(5);
	var diff_total=parseFloat(diff_grandtotal_delivery_qty).toFixed(5);
	/*console.log('tota L ' + total)
	console.log(' main '+ main_qty)*/
	if(parseFloat(total)>parseFloat(main_qty) || parseFloat(diff_total)>parseFloat(diff_qty)){
		$("#m_addrow").hide();
	}else{
		if(parseFloat(total)<parseFloat(main_qty)){
			$("#m_addrow").hide();
			count=parseFloat(count)+parseFloat(1);
			$('#count').val(count);
			var pending_qty=parseFloat(main_qty)-parseFloat(total); 
			var readonly = "";
			if(batch_stock == '1'){
				batch_no = format_value + '' + (parseInt(taxinvoice_start)+count) + '' + end_format_value;
				readonly = "readonly";
			}

			var supplier_tc_no = $("#supplier_tc_no").val();
			var s_tc_no = "";
			if(supplier_tc_no == '1'){
				s_tc_no = '<td class="text-center;" style="vertical-align:center;"><input type="text" class="form-control supplier_tc_notemp" id="supplier_tc_no'+count+'" name="supplier_tc_no[]" placeholder="Supplier T.C. No" /></td>';
			}

			var diff_unit_type = $("#diff_unit_type").val()
			var batch_readonly = "";
			var diff_batch_input = "";
			var diff_func_name = "";
			var func_name = ""; 
			if(is_diff_unit == '1'){
				var batch_readonly = "";

				if(diff_unit_type == "conv"){
					diff_func_name = "onKeyUp='diff_batch_convert_qty(1,"+ count+");'";
					func_name = "onKeyUp='batch_convert_qty(2,"+ count+");'";
				}else{
					diff_func_name = "onKeyUp='diff_batch_convert_qty(2,"+ count+");'";
					func_name = "onKeyUp='batch_convert_qty(1,"+ count+");'";
				}
				diff_batch_input = '<input type="text" class="form-control diff_batch_qtytemp" '+ diff_func_name +' id="diff_qty'+count+'" name="diff_batch_qty[]"  onchange="validate_batch_data();" placeholder="" onkeyup="qty_wise_date_validation('+count+');" /><span style="color: green; margin-left:5px;">'+diff_batch_unit_name+'</span>';
			}
			
			$("#mix_batch_table").append('<tr id="field_'+count+'"><td class="text-center" style="vertical-align:center;">'+grn_no+'/ <input type="text" value="'+ batch_no +'" class="form-control batch_notemp" id="batch_no'+count+'" name="batch_no[]" placeholder="Batch No" onchange="qty_wise_date_validation('+count+');" ></td><td class="text-center;" style="vertical-align:center;"><div style="display:flex;">'+diff_batch_input+'<input type="text" '+ func_name +' class="form-control batch_qtytemp" id="qty'+count+'" name="batch_qty[]"  onchange="validate_batch_data();" placeholder="'+pending_qty+'" onkeyup="qty_wise_date_validation('+count+');" /><span style="color: green; margin-left:5px;">'+batch_unit_name+'</span></div></td><td   class="text-center" style="vertical-align:center;">	<input type="text" class="form-control default-date-picker valid mfg_datetemp" id="mfgdate'+count+'" name="mfg_date[]" placeholder="Mfg date" onchange="get_exp_date('+count+');"  ></td><td   class="text-center" style="vertical-align:center;"><input type="text" class="form-control exp_datetemp" id="expdate'+count+'" name="exp_date[]" placeholder="Exp date" readonly></td>'+s_tc_no+'<td class="text-center" style="vertical-align:center;" ><button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_batch_data('+count+');" id="fieldremove'+count+'"><i class="fa fa-times"></i></button><input type="hidden" name="arry_sr[]" id="arry_sr" '+ readonly +' value="'+count+'" /></td></tr>');
			
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

		}else{
			$("#m_addrow").show();
		}
	}
	if(qval==="1"){
		//validate_dilivary_date();
	}
}
function remove_batch_data(count){
	$('#field_'+count).html('');
	validate_batch_data();
}

function qty_wise_date_validation(count){
	var delivery_date=$("#delivery_date"+count).val();
	var delivery_qty=$("#delivery_qty"+count).val();
	if(delivery_date===""){
		toastr.warning("Select Date", "ERROR")
		$("#delivery_date"+count).focus();
		$("#delivery_qty"+count).val("");
	}
}

function save_batch_data()
{
	var cnt = $("#selected_row_cnt").val();
	var product_id = $("#product_id").val();
	var grn_no =$("#grn_no").val();
	var grn_date = $("#grn_date").val();
	var enter_qty = $("#enter_qty").val();
	var diff_enter_qty = $("#enter_diff_qty").val();
	var batch_unit_id = $("#batch_unit_id").val();
	var purchaseordertrn_id = $("#purchaseordertrn_id").val();
	
	var batch_no_arr_temp=[];
	var batch_qty_arr_temp=[];
	var diff_batch_qty_arr_temp=[];
	var mfg_date_arr_temp=[];	
	var exp_date_arr_temp=[];
	var supplier_tc_no_arr_temp=[];


	var batch_no_arr=[];
	var batch_qty_arr=[];
	var diff_batch_qty_arr=[];
	var mfg_date_arr=[];	
	var exp_date_arr=[];		
	var supplier_tc_no_arr=[];		
	
	var i = 0;
	var j = 0;
	var k = 0;
	var kk = 0;
	var l = 0;
	var m = 0;

	var p = 0;
	var q = 0;
	var qq = 0;
	var r = 0;
	var s = 0;
	var x = 0;

	$('input.batch_notemp').each(function(){ 	
	
		if($(this).val() != '')
		{
			batch_no_arr_temp[i]=$(this).val();		
			
		}
		i++;
	}); 
	
	var total_qty = 0;
	var diff_total_qty = 0;

	$('input.batch_qtytemp').each(function(){ 	
		if($(this).val() != '')
		{
			console.log($(this).val())
			batch_qty_arr_temp[k]=$(this).val();	
			total_qty = total_qty + parseInt($(this).val());
		}
		k++;
	}); 

	$('input.diff_batch_qtytemp').each(function(){ 	
		if($(this).val() != '')
		{
			diff_batch_qty_arr_temp[kk]=$(this).val();	
			diff_total_qty = diff_total_qty + parseInt($(this).val());
		}
		kk++;
	}); 
	
	$('input.mfg_datetemp').each(function(){ 	
		if($(this).val() != '')
		{
			mfg_date_arr_temp[l]=$(this).val();					
		}
		l++;
	}); 
	
	$('input.exp_datetemp').each(function(){ 	
		if($(this).val() != '')
		{
			exp_date_arr_temp[j]=$(this).val();					
		}
		j++;
	}); 

	$('input.supplier_tc_notemp').each(function(){ 	
		if($(this).val() != '')
		{
			supplier_tc_no_arr_temp[m]=$(this).val();					
		}
		m++;
	}); 

	$('input.batch_no').each(function(){ 	
	
		if($(this).val() != '')
		{
			batch_no_arr[p]=$(this).val();		
			
		}
		p++;
	}); 
	
	// var total_qty = 0;
	$('input.batch_qty').each(function(){ 	
		if($(this).val() != '')
		{
			batch_qty_arr[q]=$(this).val();	
			total_qty = total_qty + parseInt($(this).val());
		}
		q++;
	}); 

	$('input.diff_batch_qty').each(function(){ 	
		if($(this).val() != '')
		{
			diff_batch_qty_arr[qq]=$(this).val();	
			diff_total_qty = diff_total_qty + parseInt($(this).val());
		}
		qq++;
	}); 
	
	$('input.mfg_date').each(function(){ 	
		if($(this).val() != '')
		{
			mfg_date_arr[r]=$(this).val();					
		}
		r++;
	}); 
	
	$('input.exp_date').each(function(){ 	
		if($(this).val() != '')
		{
			exp_date_arr[s]=$(this).val();					
		}
		s++;
	});

	$('input.supplier_tc_no').each(function(){ 	
		if($(this).val() != '')
		{
			supplier_tc_no_arr[x]=$(this).val();					
		}
		x++;
	});
	var batch_id_arr=[];	
	var t = 0;
	$('input.batch_id').each(function(){ 	
		if($(this).val() != '')
		{
			batch_id_arr[t]=$(this).val();					
		}
		t++;
	});


	$('input.batch_qty').each(function(){ 	
		if($(this).val() != '')
		{
			total_qty = total_qty + parseInt($(this).val());
		}
	}); 


	$('input.diff_batch_qty').each(function(){
		if($(this).val() != '')
		{
			diff_total_qty = diff_total_qty + parseInt($(this).val());
		}
	}); 


	if(diff_total_qty > diff_enter_qty  && total_qty > enter_qty){
		toastr.warning("Batch quantity can not greater than " + diff_enter_qty + " OR " + enter_qty + ".", "WARNING");
		return false;
	}

	if(batch_qty_arr_temp.length == '0' && batch_qty_arr.length == '0'){
		toastr.warning("Please Enter Any one", "WARNING");
		return false;
	}
		
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { 
			mode : "save_batch_data",
			batch_id_arr : batch_id_arr,
			batch_no_arr:batch_no_arr,
			batch_qty_arr:batch_qty_arr,
			diff_batch_qty_arr:diff_batch_qty_arr,
			mfg_date_arr:mfg_date_arr,
			exp_date_arr:exp_date_arr,
			supplier_tc_no_arr:supplier_tc_no_arr,
			batch_no_arr_temp:batch_no_arr_temp,
			batch_qty_arr_temp:batch_qty_arr_temp,
			diff_batch_qty_arr_temp:diff_batch_qty_arr_temp,
			mfg_date_arr_temp:mfg_date_arr_temp,
			exp_date_arr_temp:exp_date_arr_temp,
			supplier_tc_no_arr_temp:supplier_tc_no_arr_temp,
			grn_no:grn_no,
			product_id:product_id,
			grn_date:grn_date,
			batch_unit_id:batch_unit_id,
			purchaseordertrn_id:purchaseordertrn_id
		},
		success: function(response)
		{

			var resp = jQuery.parseJSON(response); 
			if(resp.msg.trim() == 'true')
			{
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
				$('#bs-batch-modal').modal('hide');
				Unloading();

				$("#batch_total_qty"+cnt).val(resp.batch_total_qty);
				// load_purhcase_order_data();
			}
			else
			{
				toastr.warning("Something Went Wrong", "ERROR");
				Unloading();
				return false;
			}
			
		}
	});	
}

function get_exp_date(cnt)
{	
	var product_id=$("#product_id").val();	
	var mfgdate=$("#mfgdate"+cnt).val();

	if(mfgdate == ""){
		$("#expdate"+cnt).val('');
		return false;
	}
	
	$.ajax({
		type: "POST",
		url:  root_domain+inventory_domain+'app/grn/',
		data: { mode : "get_exp_date_by_product",product_id:product_id,mfgdate:mfgdate},
		success: function(response)
		{
			if(response == '0')
			{
				toastr.warning("Expiry Days Not Set Please check in item master", "ERROR")
				return false;
			}
			else
			{					
				$("#expdate"+cnt).val(response);
			}
		
		}
		});
	
}

function load_product(type_id,cnt){
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { mode : "load_product", type_id : type_id},
		success: function(data){
			//alert(data);
			$('#grn_pid'+cnt).html(data);
			$(".select4").select2({
				width: '100%'
			});	
			//$('#wo_sub_product_id').html(data);				
			Unloading();
		}
	});
}

function load_product_category(pr_id,cnt){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { mode : "load_product_caegory", pr_id : pr_id},
		success: function(data){
			//alert(data);
			$('#grn_pcat'+cnt).val(data);
		
			//$('#wo_sub_product_id').html(data);				
			Unloading();
		}
	});

}
function add_data(cnt)
{
	var grn_no =$("#grn_no").val();
	var grn_date =$("#grn_date").val();
	var invoice_no =$("#invoice_no").val();
	var challan_no =$("#challan_no").val();
	var challan_no =$("#gir_no").val();
	var branch_id =$("#branch_id").val();
	var grn_against =$("#grn_against").val();
	var vender_id =$("#vender_id").val();
	
	
	alert(grn_no);	
	return false;
	
	var batch_no_arr=[];
	var batch_qty_arr=[];
	var mfg_date_arr=[];	
	var exp_date_arr=[];		
	var supplier_tc_no_arr=[];
	
	var i = 0;
	var j = 0;
	var k = 0;
	var l = 0;
	var m = 0;

	$('input.batch_no').each(function(){ 	
	
		if($(this).val() != '')
		{
			batch_no_arr[i]=$(this).val();		
			
		}
		i++;
	}); 
	
	$('input.batch_qty').each(function(){ 	
		if($(this).val() != '')
		{
			batch_qty_arr[k]=$(this).val();					
		}
		k++;
	}); 
	
	$('input.mfg_date').each(function(){ 	
		if($(this).val() != '')
		{
			mfg_date_arr[l]=$(this).val();					
		}
		l++;
	}); 
	
	$('input.exp_date').each(function(){ 	
		if($(this).val() != '')
		{
			exp_date_arr[j]=$(this).val();					
		}
		j++;
	}); 
	$('input.supplier_tc_no').each(function(){ 	
		if($(this).val() != '')
		{
			supplier_tc_no_arr[m]=$(this).val();					
		}
		m++;
	}); 
		
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { mode : "save_batch_data",batch_no_arr:batch_no_arr,batch_qty_arr:batch_qty_arr,mfg_date_arr:mfg_date_arr,exp_date_arr:exp_date_arr,grn_no:grn_no,supplier_tc_no_arr:supplier_tc_no_arr},
		success: function(response)
		{
			if(response == 'true')
			{
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
				$('#bs-batch-modal').modal('hide');
				Unloading();
			}
			else
			{
				toastr.warning("Something Went Wrong", "ERROR");
				Unloading();
				return false;
			}
			
		}
	});	
}





function load_product_detail(pro_id) {
	//alert(pro_id);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { mode : "load_productdata",eid :pro_id },
		success: function(response)
		{
			var resp = jQuery.parseJSON(response); 
			$('#product_conv_unit_name').val(resp.conv_unit_name);
			$('#product_conv_unit').val(resp.product_conv_unit);
			$('#product_conv_qty').val(resp.product_conv_qty.trim());
			$('#product_conv_qty_hide').val(resp.product_conv_qty.trim());

			$('#product_base_unit_name').val(resp.base_unit_name);
			$('#product_base_unit').val(resp.product_base_unit);
			$('#product_base_qty').val(resp.product_base_qty.trim());
			$('#product_base_qty_hide').val(resp.product_base_qty.trim());

			$('#product_spec_hid').val(resp.product_specification);
			$('#product_density').val(resp.m_type_density);
			$('#godown_id').val(resp.product_mat_center);
			
			if(resp.product_specification!=0)
			{
				if($("#edit_id").val()==""){
					$('#get_spec_div').show();
					$('#get_spec_div').empty().prepend(resp.product_specification_code);
					get_ms_kg();
					$('#product_kg').val('');
				}	
			}
			else
			{
				$('#get_spec_div').hide();
				$('#product_kg').val('');
			}		
		}
	});
}


function get_ms_kg(){
	var msid = $('#msid').val();
	var values = [];
	$('.get_ms_kg').each(function(){
		values.push({ name: this.name, value: this.value }); 

	});
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { mode : "get_product_specification_cal", values : values, msid : msid },
		success: function(response)
		{
			$('#product_kg').val(response);
		}
	});
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

$('#ourside_so_product_id').select2({
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


function hide_show_prouct_add_row(grn_against){

	if(grn_against == '4' || grn_against == '5'){
		$(".direct_grn_row").show();

		if(grn_against == '5'){
			$(".outside_jobwork").show();
			$(".outside_jobwork_hide").hide();
		}else{
			$(".outside_jobwork").hide();
			$(".outside_jobwork_hide").show();
		}
	}else{
		$(".direct_grn_row").hide();
		if(grn_against == '1'){
			$(".outside_jobwork_hide").hide();
		}else{
			$(".outside_jobwork_hide").show();
		}
	}
}


function direct_add_grn_field(){
	if(!$("#vender_id").val()){		
		toastr.warning("Choose Vendor", "ERROR");
		$("#vender_id").focus();
		return false;
	}
	else if(!$("#ourside_so_product_id").val()){
		toastr.warning("Select Product", "ERROR");
		$("#ourside_so_product_id").select2('focus');
		return false;
	}

	if($("#grn_against").val()=='5'){

		if(!$("#outside_so_id").val()){		
			toastr.warning("Select Sales Order", "ERROR");
			$("#outside_so_id").focus();
			return false;
		}	
	}

	if(!$("#rate_unit").val()){		
			toastr.warning("Select Rate Unit", "ERROR");
			$("#rate_unit").focus();
			return false;
		}	

	if(!$("#product_base_qty").val()){		
		toastr.warning("Enter Qty", "ERROR");
		$("#product_base_qty").focus();
		return false;
	}
	
	if(!$("#godown_id").val()){		
		toastr.warning("Select Godown", "ERROR");
		$("#godown_id").focus();
		return false;
	}
	

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/grn/',
		data: { 
			mode : "outside_so_fieldadd",
			product_id:$("#ourside_so_product_id").val(),
			unit_id:$("#product_base_unit").val(),
			conv_unit_id:$("#product_conv_unit").val(),
			base_qty:$("#product_base_qty").val(),
			conv_qty:$("#product_conv_qty").val(),
			godown_id:$("#godown_id").val(),
			outside_so_id:$("#outside_so_id").val(),
			grn_against:$("#grn_against").val(),
			customer_id:$("#vender_id").val(),
			branch_id: $("#branch_id").val(),
			rate_unit:$("#rate_unit").val(),
			purchaseorder_id : $("#purchaseorder_id").val()

		 },
		success: function(response)
		{
			//console.log(response);
			// $("#product_type").select2("val","");

			if(response.trim() == '1')
			{
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS");
				Unloading();
			}
			else
			{
				toastr.warning("Something Went Wrong", "ERROR");
				Unloading();
				return false;
			}
			$("#ourside_so_product_id").select2("val","");
			$("#ourside_so_product_id").select2('focus');
			$("#outside_so_id").select2("val","");
			$("#product_base_unit").val("");
			$("#product_conv_unit").val("");
			$("#product_base_qty").val("");
			$("#product_conv_qty").val("");
			$("#product_base_unit_name").val("");
			$("#product_conv_unit_name").val("");
			$("#godown_id").select2("val","");
			$("#rate_unit").empty();
			
			$('#addrow').val('Add');
			Unloading();
			load_purhcase_order_data();
		}
	});
}


function convert_qty(type){

	if(type==2){
		var conv_qty_hide=$("#product_conv_qty").val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var	num=$("#product_conv_qty_hide").val();
		var d=parseFloat(num);
		resultb = d.toFixed(3);

		if(resultb===results){
			return false;
		}
		var product_base_qty_hide=$("#product_base_qty_hide").val();
	}else{
		var base_qty_hide=$("#product_base_qty").val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(3);
		
		var base_qty_hidess=$("#product_base_qty_hide").val();
		var s=parseFloat(base_qty_hidess);
		results = s.toFixed(3);

		if(resultb===results){
			return false;
		}
		var conv_qty_hide=$("#product_conv_qty").val();
	}
	
	var base_qty=$("#product_base_qty").val();
	var conv_qty=$("#product_conv_qty").val();
	
	//var base_qty_hide=$("#product_base_qty_hide").val();
	//var conv_qty_hide=$("#product_conv_qty_hide").val();
	
	//var base_qty=$("#product_base_qty").val();
	
	//var conv_qty=$("#product_conv_qty").val();
	var product_id=$("#ourside_so_product_id").val();
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/grn/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
				//arr.show_qty
				//arr.hide_qty
				//alert(type);
				//alert(arr.show_qty);
				//alert(arr.hide_qty);
				console.log(arr);
				if(type===1){
					$("#product_base_qty_hide").val(base_qty.trim());
				}else if(type===2){
					$("#product_conv_qty_hide").val(conv_qty.trim());
				}
				
				if(type===1){
					$("#product_conv_qty").val((arr.show_qty).trim());
					$("#product_conv_qty_hide").val(arr.hide_qty);

				}else if(type===2){
					$("#product_base_qty").val((arr.show_qty).trim());
					$("#product_base_qty_hide").val(arr.hide_qty);				
					
				}else{
					$("#product_base_qty").val((arr.show_qty).trim());
					$("#product_base_qty_hide").val(arr.hide_qty);
					$("#product_conv_qty").val((arr.show_qty).trim());
					$("#product_conv_qty_hide").val(arr.hide_qty);
				}
			}
		});

	}else{
		toastr.warning("Select Product First", "WARNING");
		$("#product_base_qty").val("1");
		$("#product_base_qty_hide").val("1");
		$("#product_conv_qty").val("1");
		$("#product_conv_qty_hide").val("1");
	}
}


function load_product_unit(product_id){
	Loading();
		$.ajax({
			type: "POST",
			async: false,
			url: root_domain+ inventory_domain +'app/grn/',
			data: { mode : "load_product_unit", product_id : product_id},
			success: function(response)
			{
				// console.log(response);
				var obj=jQuery.parseJSON(response);
				
				$("#rate_unit").empty().html(obj.unit_option);
				$('#rate_unit').val(obj.product_conv_unit).trigger('change');

				Unloading();
			}
		});
	
}


function delete_batch_data(batch_id,count){
	var grn_no = $("#grn_no").val();
	var res= confirm(" Are you want to delete ?");
	
	if(res) {
	Loading();
		$.ajax({
			type: "POST",
			// async: false,
			url: root_domain+ inventory_domain +'app/grn/',
			data: { mode : "delete_batch_data", batch_id : batch_id, grn_no:grn_no},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);	
				console.log(arr);
				if(arr.msg == 'true'){
					$('#field_'+count).html('');
					$("#batch_total_qty"+count).val(arr.batch_total_qty);
				}

				Unloading();
			}
		});
	}
}


function po_short_close_for_grn(purchaseorder_id,purchaseordertrn_id){

	var res= confirm(" Are you want to short close this purchase order item?");
	
	if(res) {
		// return false;
	Loading();
		$.ajax({
			type: "POST",
			// async: false,
			url: root_domain+ inventory_domain +'app/grn/',
			data: { mode : "po_short_close_for_grn", purchaseordertrn_id : purchaseordertrn_id,purchaseorder_id:purchaseorder_id},
			success: function(response)
			{
				
				
				if(response.trim() == '1'){
					toastr.success("PURCHASE ORDER ITEM SUCCESSFULLY SHORT CLOSE", "SUCCESS");
					Unloading();
				}else{
					toastr.warning("Something Went Wrong", "ERROR");
					Unloading();
				}

				load_purhcase_order_data();
				/* var arr = jQuery.parseJSON(response);	
				if(arr.msg == '1'){
					toastr.success("PURCHASE ORDER ITEM SUCCESSFULLY SHORT CLOSE", "SUCCESS");
					Unloading();
				}else{
					toastr.warning("Something Went Wrong", "ERROR");
					Unloading();
				}*/

				Unloading();
			}
		});
	}
}

function view_attach_document(grn_id,grn_no){
	$('#view_attach_document_modal').modal('show');
	$('#ref_no').html(grn_no);
	$('#ref_ord_id').val(grn_id);
	load_attach_document();
}

function load_attach_document(){
	var grn_id=$('#ref_ord_id').val();
	
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
		"sAjaxSource": root_domain + inventory_domain + 'app/grn/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "load_attach_document"},
				{"name": "grn_id", "value": grn_id});
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function batch_convert_qty(type,cnt){
	
	var base_qty=0;
	var conv_qty=0;
	
	var product_id=$("#product_id").val();

	if(type == 2){
		conv_qty = $("#qty"+cnt).val();
	}else{
		base_qty = $("#qty"+cnt).val();
	}

	console.log(product_id)
	
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/grn/',
			data: { 
				mode : "convert_qty",
				type : type,
				base_qty:base_qty,
				conv_qty:conv_qty,
				product_id:product_id
			},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);

				$("#diff_qty"+cnt).val(arr.hide_qty);
			}
		});
	}
}


function diff_batch_convert_qty(type,cnt){
	
	var base_qty=0;
	var conv_qty=0;
	
	var product_id=$("#product_id").val();

	if(type == 2){
		conv_qty = $("#diff_qty"+cnt).val();
	}else{
		base_qty = $("#diff_qty"+cnt).val();
	}
	console.log(product_id)
	if(product_id){
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/grn/',
			data: { 
				mode : "convert_qty",
				type : type,
				base_qty:base_qty,
				conv_qty:conv_qty,
				product_id:product_id
			},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);

				$("#qty"+cnt).val(arr.hide_qty);
			}
		});
	}
}