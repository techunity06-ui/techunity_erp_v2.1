//var datatable;
$(document).ready(function() {
	load_datatable();
	
});

function load_datatable()
{
	var process_id=$('#process_id').val();
	var process_type=$('#process_type').val();
	var product_id=$('#product_id').val();
	var branch_id=$('#branch_id').val();
	var type=$('#type').val();
	var is_store_approval=$('#is_store_approval').val(); // ADDED BY SANAT :: 21-09-2021
	
	
	//alert(process_id);
	Loading()
	
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_process_details_list/',
			data: { mode : "fetch_working",process_id:process_id,process_type:process_type,product_id:product_id,branch_id:branch_id,type:type,is_store_approval:is_store_approval },
			success: function(response)
			{
				//alert(response);
				$('#dynamic_table_working').html(response);
				Unloading();
				
			}
	}); 
}

function complite_msg_show(){
	toastr.warning("Your Process all-ready Completed", "WARNING");
}

function checkAll()
{
var checkboxes = document.getElementsByTagName('input'), val = null;   

	for (var i = 0; i < checkboxes.length; i++)
	{
		if (checkboxes[i].type == 'checkbox')
		{
		 if (val === null) val = checkboxes[i].checked;
		 checkboxes[i].checked = val;
		}
	}
}
// model wise process start code start pathik : 20-08-2021

function show_process_action_model(p_ids,product_id,process_type,store_relese_first_process){

Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_process_details_list/',
			data: { mode : "get_product_name",product_id:product_id},
			success: function(response)
			{
				$("#model_product_name").html(response);
				if(process_type == '1'){
					start_process_using_model(p_ids,product_id); 
				}else{
					end_process_using_model(p_ids,product_id,store_relese_first_process) 

				}
				Unloading();
			}
	});

}

 function start_process_using_model(p_ids,product_id) {
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_process_details_list/',
			data: { mode : "start_process_using_model",p_ids:p_ids},
			success: function(response)
			{
				//alert(response);
				$('#start_stop_data').html(response);
				$('#process_name').html('Start');
				// $("#model_product_name").html(product_name);

				$('#production_process_start_stop_model').modal('show');
				var dateNow = new Date();
				
				$(".form_datetime").datetimepicker({
				format: 'dd-mm-yyyy h:i:s',
				    ignoreReadonly: false,

				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left",
				defaultDate:dateNow,
				beforeShow: function (i) { if ($(i).attr('readonly')) { return false; } }

			});  
				Unloading();

				$('#client_id,#sales_order_id,#job_card_no,#usertype_id,#machine').select2({
						width : '100%'
					});

			}
	});
}


function check_start_validation(){
	var bomObj = {};
	bomObj.pidChecked = [];
	bomObj.start_qtyChecked = [];
	bomObj.working_qtyChecked=[];
		
		//alert("dsa");
	var total_qty=0;
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		
		if(start_qty>0){
			if(start_qty>working_qty){
				  toastr.warning("Grater Thean Qty In Jobcard No : "+jobcard_no+"", "WARNING"); 
				   $("#trid"+pid+"").css("background-color", "red");
					
			}else{
				$("#trid"+pid+"").css("background-color", "");
				bomObj.start_qtyChecked.push(start_qty);
				bomObj.pidChecked.push(pid);
				bomObj.working_qtyChecked.push(working_qty);
				total_qty += parseFloat(start_qty);
			}
			
		}
		$("#start_qty").val(total_qty);
	});
} 

function process_start_using_model(){
	
	var bomObj = {};
		bomObj.pidChecked = [];
		bomObj.start_qtyChecked = [];
		bomObj.working_qtyChecked=[];
		bomObj.batch_no = [];
		bomObj.work_order_no= [];
		bomObj.work_order_id= [];
		
	var so_stock=(document.getElementsByName('start_qty1[]'));
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
	var total_qty=0;
	var errorlog=0;
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
		
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		if(start_qty>0){
			if(start_qty>working_qty){
				  toastr.warning("Grater Thean Qty In Jobcard No : "+jobcard_no+"", "WARNING"); 
				   $("#trid"+pid+"").css("background-color", "red");
					errorlog +=parseFloat(1);
			}else{
				$("#trid"+pid+"").css("background-color", "");
				bomObj.start_qtyChecked.push(start_qty);
				bomObj.pidChecked.push(pid);
				bomObj.working_qtyChecked.push(working_qty);
				total_qty += parseFloat(start_qty);
			}
		}
		
	});
	$('input.batch_no').each(function(){ 
	
	var batch_no=$(this).val();
	bomObj.batch_no.push(batch_no);

	});
	
	$('input.work_order_no').each(function(){ 
	
	var work_order_no=$(this).val();
	bomObj.work_order_no.push(work_order_no);

	});
	
	$('input.work_order_id').each(function(){ 
	
	var work_order_id=$(this).val();
	bomObj.work_order_id.push(work_order_id);

	});

	if(errorlog>"0"){
		toastr.warning("Grater Thean Qty", "WARNING"); 
		return false;
	}

	if($("#machine").val() == ""){
		toastr.warning("PLEASE SELECT MACHINE", "WARNING"); 
		return false;	
	}
		var mode=$("#mode").val();
		
		var max_available_qty=$("#max_available_qty").val();
		   var pending_qty=$("#pending_qty").val();
		   var product_base_unit=$("#product_base_unit").val();
		   var product_conv_unit = $("#product_conv_unit").val();
		   var branch_id=$("#branch_id_model").val();
		   var product_id=$("#product_id_model").val();
		   var process_id=$("#process_id").val();
		   var product_version=$("#product_version").val();
		   var remark=$("#remark").val();
		   var start_qty=$("#start_qty").val();
		   var start_stop_user_id = $("#usertype_id").val();
		   var process_rate = $("#process_rate").val();
		   var machine = $("#machine").val();

		    if($("#remark").attr("data-required") == "yes"){
		if(remark == ""){
				toastr.warning("Please enter remark", "WARNING"); 
			return false;
		}
	}

	var reorder_qty=$("#reorder_qty").val();
			var wo_qty = total_qty / reorder_qty;
			/*if(reorder_qty != "" && reorder_qty > 0){
					if(!isInteger(wo_qty)){
						toastr.warning("You Can't Process. Reorder Qauntity is " + reorder_qty, "ERROR");
						return false;	
				}
			}*/
		   
		   Loading();
    $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_process_start/',
		data: { mode : mode,pid:bomObj.pidChecked,pid_wise_start_qty:bomObj.start_qtyChecked,max_available_qty:max_available_qty,pending_qty:pending_qty,product_base_unit:product_base_unit,branch_id:branch_id,product_id:product_id,process_id:process_id,product_version:product_version,remark:remark,start_qty:start_qty,batch_no:bomObj.batch_no,work_order_no:bomObj.work_order_no,work_order_id:bomObj.work_order_id,start_stop_user_id:start_stop_user_id,process_rate:process_rate,product_conv_unit:product_conv_unit,machine:machine},
		success: function(response)
		{
			if(response == '1') {
					toastr.success("PROCESS STARTED SUCCESSFULLY", "SUCCESS");
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			$('#production_process_start_stop_model').modal('hide');
			load_datatable();

			Unloading();
		
		}
	});  
}

// model wise process start code End pathik : 20-08-2021

// model wise process End code start pathik : 20-08-2021
function end_process_using_model(p_ids,product_id,store_relese_first_process) 

{
	var process_end_time_qc = $("#process_end_time_qc").val();
	delete_temp_qc_data();
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_process_details_list/',
			data: { 
				mode : "end_process_using_model",
				p_ids:p_ids,
				process_end_time_qc:process_end_time_qc
			},
			success: function(response)
			{
				//alert(response);
				$("#end_process_desc_data").hide();
				$("#start_stop_data").show();
				$('#start_stop_data').html(response);
				$('#process_name').html('End');
				// $("#model_product_name").html(product_name);
				$('#production_process_start_stop_model').modal('show');
				$('#store_relese_first_process').val(store_relese_first_process);


				Unloading();

				$('#client_id,#sales_order_id,#job_card_no,#usertype_id,#product_scrap_id,#machine').select2({
						width : '100%'
					});

			}
	});
}

function store_confirm_msg(){
	var bomObj = {};
		bomObj.pidChecked = [];
		bomObj.start_qtyChecked = [];
		bomObj.working_qtyChecked=[];
		
	var so_stock=(document.getElementsByName('end_qty[]'));
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
	var errorlog=0;
	var total_qty=0;
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
	
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		if(start_qty>0){
			if(start_qty>working_qty){
				  toastr.warning("Grater Thean Qty In Jobcard No : "+jobcard_no+"", "WARNING"); 
				   $("#trid"+pid+"").css("background-color", "red");
					errorlog +=parseFloat(1);
			}else{
				$("#trid"+pid+"").css("background-color", "");
				bomObj.start_qtyChecked.push(start_qty);
				bomObj.pidChecked.push(pid);
				bomObj.working_qtyChecked.push(working_qty);
				total_qty += parseFloat(start_qty);
			}
		}
		
	});
	if(errorlog>"0"){
		toastr.warning("Grater Thean Qty", "WARNING"); 
		return false;
	}

	var reorder_qty=$("#reorder_qty").val();
			var wo_qty = total_qty / reorder_qty;
			/*if(reorder_qty != "" && reorder_qty > 0){
					if(!isInteger(wo_qty)){
						toastr.warning("You Can't Process. Reorder Qauntity is " + reorder_qty, "ERROR");
						return false;	
				}
			}*/
			
   if($("#remark").attr("data-required") == "yes"){
		if(remark == ""){
				toastr.warning("Please enter remark", "WARNING"); 
			return false;
		}
	}

	$('#store_required_confirmation_modal').modal('show');
}

function save_store_confirmation(){
	var auto_store_relese = $("input[name='store_confirm']:checked").val();
	
	process_end_using_model(auto_store_relese);
}

function process_end_using_model(auto_store_relese = 0){
	
	var bomObj = {};
		bomObj.pidChecked = [];
		bomObj.start_qtyChecked = [];
		bomObj.working_qtyChecked=[];
		
	var so_stock=(document.getElementsByName('end_qty[]'));
	var cnt=so_stock.length;
	var so_stock1=0;
	for(var i=0;i<cnt;i++)
	{
		if(so_stock[i].value > 0){
			so_stock1 += parseFloat(so_stock[i].value);
		}
	} 
	if(so_stock1<="0"){
		toastr.warning("Enter End Qty", "WARNING"); 
		  return false;
	}
	var errorlog=0;
	var total_qty=0;
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
	
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		if(start_qty>0){
			if(start_qty>working_qty){
				  toastr.warning("Grater Thean Qty In Jobcard No : "+jobcard_no+"", "WARNING"); 
				   $("#trid"+pid+"").css("background-color", "red");
					errorlog +=parseFloat(1);
			}else{
				$("#trid"+pid+"").css("background-color", "");
				bomObj.start_qtyChecked.push(start_qty);
				bomObj.pidChecked.push(pid);
				bomObj.working_qtyChecked.push(working_qty);
				total_qty += parseFloat(start_qty);
			}
		}
		
	});
	if(errorlog>"0"){
		toastr.warning("Grater Thean Qty", "WARNING"); 
		return false;
	}

	var product_scrap_id = $('#product_scrap_id').val();
	var scrap_unit = $('#scrap_unit').val();
	var scrap_qty = $('#scrap_qty').val();
	if(product_scrap_id!=""){
		if(scrap_unit==""){
			toastr.warning("Select Scrap Unit", "WARNING"); 
		  	return false;
		}else if(scrap_qty==""){
			toastr.warning("Enter Scrap Quantity", "WARNING"); 
		  	return false;
		}

		if(scrap_qty != "" && scrap_qty > total_qty){
			toastr.warning("Scrap Quantity can not be greater than Stop Qty", "WARNING"); 
		  	return false;
		}
	}
	var store_relese_first_process=$("#store_relese_first_process").val();
	
	var mode=$("#mode").val();
	var max_available_qty=$("#max_available_qty").val();
	var pending_qty=$("#pending_qty").val();
	var product_base_unit=$("#product_base_unit").val();
	var branch_id=$("#branch_id_model").val();
	var product_id=$("#product_id_model").val();
	var process_id=$("#process_id").val();
	var product_version=$("#product_version").val();
	var remark=$("#remark").val();
	var stop_qty=$("#stop_qty").val();
	var grn_godown=$("#grn_godown").val();
	var batch_no = $("#batch_id").val();
	var batch_man_no = $("#batch_no").val();
	var start_stop_user_id = $("#usertype_id").val();
	var machine = $("#machine").val();

	var reorder_qty=$("#reorder_qty").val();
			var wo_qty = total_qty / reorder_qty;
			/*if(reorder_qty != "" && reorder_qty > 0){
					if(!isInteger(wo_qty)){
						toastr.warning("You Can't Process. Reorder Qauntity is " + reorder_qty, "ERROR");
						return false;	
				}
			}*/
			
   if($("#remark").attr("data-required") == "yes"){
		if(remark == ""){
				toastr.warning("Please enter remark", "WARNING"); 
			return false;
		}
	}
	var process_end_time_qc = $("#process_end_time_qc").val();
	if(process_end_time_qc == 1){
		var total_end_qty = 0;	
		var total_qc_qty = 0;	
		if($("#total_end_qty").val() != ""){
			total_end_qty = parseFloat($("#total_end_qty").val());
		}
	
		if($("#total_qc_qty").val() != ""){
			total_qc_qty = parseFloat($("#total_qc_qty").val());
		}
		if(total_qc_qty != total_end_qty){
			toastr.warning("PLEASE CHECK QC QTY MUST BE EQUAL TO END QTY", "WARNING"); 
		  	return false;
		}
		if(total_qty != total_end_qty){
			toastr.warning("PLEASE CHECK QC QTY MUST BE EQUAL TO END QTY", "WARNING"); 
		  	return false;
		}
	}
	

	if(process_end_time_qc == 0){
		if($("#machine").val() == ""){
			toastr.warning("PLEASE SELECT MACHINE", "WARNING"); 
			return false;	
		}
	}
	Loading();
	 $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_process_end/',
		data: { 
			mode : mode,
			pid:bomObj.pidChecked,
			pid_wise_end_qty:bomObj.start_qtyChecked,
			max_available_qty:max_available_qty,
			pending_qty:pending_qty,
			product_base_unit:product_base_unit,
			branch_id:branch_id,
			product_id:product_id,
			process_id:process_id,
			product_version:product_version,
			remark:remark,
			stop_qty:stop_qty,
			grn_godown:grn_godown,
			total_stop_qty:total_qty,
			batch_no:batch_no,
			batch_man_no:batch_man_no,
			start_stop_user_id:start_stop_user_id,
			product_scrap_id:product_scrap_id,
			scrap_unit : scrap_unit,
			scrap_qty:scrap_qty,
			auto_store_relese:auto_store_relese,
			machine : machine,
			process_end_time_qc:process_end_time_qc
		 },
		success: function(response)
		{
			if(response == '1') {
				toastr.success("PROCESS End SUCCESSFULLY", "SUCCESS");
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			$('#production_process_start_stop_model').modal('hide');
			$('#store_required_confirmation_modal').modal('hide');
			load_datatable();
			location.reload();
			Unloading();
		}
	});  
}

// model wise process End code End pathik : 20-08-2021


function product_load(){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	// var product_type=$("#product_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=production_pro_type&search=production_pro_search';
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


	$('#product_id').select2({
		data: product_load(),
		placeholder: 'search',
		multiple: false,
		width : '100%',
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

function load_process_data(p_id,type){

	var client_id = $("#client_id").val();
	var so_id = $("#sales_order_id").val();
	var job_card_no = $("#job_card_no").val();
	var process_end_time_qc = $("#process_end_time_qc").val();
	Loading();
	 $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/working_process_details_list/',
		data: { 
			mode : 'load_filter_data',
			p_id:p_id,
			client_id:client_id,
			so_id:so_id,
			job_card_no:job_card_no,
			type:type,
			process_end_time_qc : process_end_time_qc
		},
		success: function(response)
		{
			$("#tbl_filter_data").empty().html(response);
			Unloading();
		}
	});
}

function get_scrap_unit(scrap_product_id){

	if(scrap_product_id == ""){
		$(".scrap_row").hide();
	}else{
		$(".scrap_row").show();
	}

	Loading();
	 $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/working_process_details_list/',
		data: { mode : 'get_scrap_unit' ,scrap_product_id:scrap_product_id},
		success: function(response)
		{

			$("#scrap_unit").empty().html(response);
			$("#scrap_unit").select2({
				width:"100%"
			});
			Unloading();
			
		}
	});
}
function next_entry_view(){

	var total_qty_main = 0;
	var total_end_qty = 0;
	var total_accept_qty =0;
	var total_reject_qty = 0;
	var total_reprocess_qty =0;
	if($("#total_qty_main").val() != ""){
		total_qty_main = parseFloat($("#total_qty_main").val());
	}
	if($("#total_end_qty").val() != ""){
		total_end_qty = parseFloat($("#total_end_qty").val());
	}
	if($("#total_accept_qty").val() != ""){
		total_accept_qty = parseFloat($("#total_accept_qty").val());
	}
	if($("#total_reject_qty").val() != ""){
		total_reject_qty = parseFloat($("#total_reject_qty").val());
	}
	if($("#total_reprocess_qty").val() != ""){
		total_reprocess_qty = parseFloat($("#total_reprocess_qty").val());
	}

	if(total_end_qty == "" || total_end_qty == '0'){
		toastr.warning("Please Enter End Qty", "ERROR");
		return false;
	}

	if(total_end_qty > total_qty_main){
		toastr.warning("End Qty Not Grater Than Working Qty", "ERROR");
		return false;
	}
	var sum_qty = total_accept_qty + total_reject_qty + total_reprocess_qty;

	if(sum_qty != total_end_qty){
		toastr.warning("Please check Accept, Reject & Reprocess QTY Must be Equl to END Qty", "ERROR");
		return false;	
	}

	if($("#machine").val() == ""){
		toastr.warning("PLEASE SELECT MACHINE", "WARNING"); 
		return false;	
	}
	delete_temp_qc_data();

	load_add_qc_view(total_end_qty);
	
}

function delete_temp_qc_data(){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/working_process_details_list/',
		data: { mode : 'delete_temp_qc_data'},
		success: function(response)
		{
			Unloading();		
		}
	});
}

function load_add_qc_view(){
	
	var total_qty_main = 0;
	var total_end_qty = 0;
	var total_accept_qty =0;
	var total_reject_qty = 0;
	var total_reprocess_qty =0;
	if($("#total_qty_main").val() != ""){
		total_qty_main = parseFloat($("#total_qty_main").val());
	}
	if($("#total_end_qty").val() != ""){
		total_end_qty = parseFloat($("#total_end_qty").val());
	}
	if($("#total_accept_qty").val() != ""){
		total_accept_qty = parseFloat($("#total_accept_qty").val());
	}
	if($("#total_reject_qty").val() != ""){
		total_reject_qty = parseFloat($("#total_reject_qty").val());
	}
	if($("#total_reprocess_qty").val() != ""){
		total_reprocess_qty = parseFloat($("#total_reprocess_qty").val());
	}
	var client_id = $("#client_id").val();
	var so_id = $("#sales_order_id").val();
	var job_card_no = $("#job_card_no").val();
	var process_end_time_qc = $("#process_end_time_qc").val();
	var p_id = $("#p_id").val();
	var unit_id =  $("#product_base_unit").val();
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/working_process_details_list/',
		data: { 
			mode : 'load_temp_qc_detail',
			total_end_qty : total_end_qty,
			client_id : client_id,
			so_id : so_id,
			job_card_no : job_card_no,
			process_end_time_qc : process_end_time_qc,
			p_id : p_id,
			total_accept_qty:total_accept_qty,
			total_reject_qty : total_reject_qty,
			total_reprocess_qty : total_reprocess_qty,
			unit_id : unit_id 
		},
		success: function(response)
		{	
			$("#end_process_desc_data").show();
			$("#start_stop_data").hide();
			$("#end_process_desc_details").empty().html(response);
			$(".select_jobcard").select2({
				width : "100%"
			});

			setTimeout(function(){
				if(total_reject_qty > 0){
					load_reject_products();	
				}
			},500)
						
			Unloading();		
		}
	});
}



function previous_page(){ 
	delete_temp_qc_data();
	$("#total_qc_qty").val(0);
	$("#lbl_qc_qty").empty().html('0');
	$("#start_stop_data").show();
	$("#end_process_desc_data").hide();
}


function add_temp_qc_field(type){
	var new_product_id = 0;
	var new_unit_id = 0;
	var new_process_id = 0;
	var pending_qty = 0;
	var new_godown_id = 0;
	var qty = 0;
	if($("#pending_"+type+"_qty").html() != ""){
		pending_qty = parseFloat($("#pending_"+type+"_qty").html());
	}
	if($("#p_id_"+type+"_qty").val() != ""){
		qty = parseFloat($("#p_id_"+type+"_qty").val());
	}
	console.log(pending_qty)
	console.log(qty)
	if($("#p_id_"+type).val() == ""){
		toastr.warning("Please select " + type +"  jobcard", "ERROR");
		return false;
	}else if(qty == "" || qty == '0'){
		toastr.warning("Please enter " + type +" qty", "ERROR");
		return false;
	}else if($("#p_id_"+type+"_reason").val() == ""){
		toastr.warning("Please enter " + type +" reason", "ERROR");
		return false;
	}else if(type == "reject" && $("#new_product_reject").val()==""){
		toastr.warning("Please Select New Product", "ERROR");
		return false;
	}else if(type == "reject" && $("#new_unit_id").val()==""){
		toastr.warning("Please Select New Product Unit", "ERROR");
		return false;
	}else if(type == "reprocess" && $("#new_process_id").val()==""){
		toastr.warning("Please Select New Process", "ERROR");
		return false;
	}else if($("#new_godown_id_"+type).val() == ""){
		toastr.warning("Please Select " + type +" Godown", "ERROR");
		return false;
	}else if(qty > 0 && qty > pending_qty){
		toastr.warning("Please check " + type +"  entered QTY NOT GREATER THAN WORKING QTY", "ERROR");
		return false;
	}else{
		var total_qc_qty = 0;
		var total_end_qty = 0;

		if($("#total_end_qty").val() != ""){
			total_end_qty = parseFloat($("#total_end_qty").val());
		}
		if($("#total_qc_qty").val() != ""){
			total_qc_qty = parseFloat($("#total_qc_qty").val());
		}
		total_qc_qty = total_qc_qty + qty;
		
		if(total_qc_qty > total_end_qty){
			toastr.warning("QTY NOT GREATER THAN END QTY", "ERROR");
			return false;
		}
		
		var sel_p_id = $("#p_id_"+type).val();
		var reason = $("#p_id_"+type+"_reason").val();
		
		var product_id = $("#product_id_model").val();
		var p_id = $("#p_id").val();
		var process_id = $("#process_id").val();
		var unit_id =  $("#product_base_unit").val();
		var p_id_qty = 0;
		var p_id_accept_qty = 0;
		var p_id_reject_qty = 0;
		var p_id_reprocess_qty = 0;
		new_godown_id = $("#new_godown_id_"+type).val();

		if($("#end_qty"+sel_p_id).val() != ""){
			p_id_qty = parseFloat($("#end_qty"+sel_p_id).val());
		}
		if($("#accept_qty"+sel_p_id).val() != ""){
			p_id_accept_qty = parseFloat($("#accept_qty"+sel_p_id).val());
		}
		if($("#reject_qty"+sel_p_id).val() != ""){
			p_id_reject_qty = parseFloat($("#reject_qty"+sel_p_id).val());
		}
		if($("#reprocess_qty"+sel_p_id).val() != ""){
			p_id_reprocess_qty = parseFloat($("#reprocess_qty"+sel_p_id).val());
		}

		p_id_qty = p_id_qty + qty;
		if(type == "accept"){
			p_id_accept_qty = p_id_accept_qty + qty;
		}
		if(type == "reject"){
			p_id_reject_qty = p_id_reject_qty + qty;	
		}
		if(type == "reprocess"){
			p_id_reprocess_qty = p_id_reprocess_qty + qty;	
		}
		
		if(type =="reject"){
			new_product_id = $("#new_product_reject").val();
			new_unit_id = $("#new_unit_id").val();
		}
		if(type =="reprocess"){
			new_process_id = $("#new_process_id").val();
		}

		Loading();

		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_process_details_list/',
			data: { 
				mode : 'add_temp_qc_field',
				sel_p_id : sel_p_id,
				qty : qty,
				reason : reason,
				product_id : product_id,
				process_id : process_id,
				unit_id : unit_id,
				new_process_id: new_process_id,
				new_product_id:new_product_id,
				new_unit_id:new_unit_id,
				new_godown_id : new_godown_id,
				type : type,
				branch_id : $("#branch_id_model").val()
			},
			success: function(response)
			{	
				if(response.trim() == "1"){
					toastr.success(type.toUpperCase() +" QTY SUCCESSFULLY ADDED", "SUCCESS");
					$("#total_qc_qty").val(total_qc_qty);
					$("#lbl_qc_qty").empty().html(total_qc_qty);
					$("#end_qty"+sel_p_id).val(p_id_qty);
					$("#accept_qty"+sel_p_id).val(p_id_accept_qty);
					$("#reject_qty"+sel_p_id).val(p_id_reject_qty);
					$("#reprocess_qty"+sel_p_id).val(p_id_reprocess_qty);

				}else{
					toastr.warning("SOMETHING WRONG", "ERROR");
				}
				load_jobcard_datatable(p_id,type);
				reset_temp_qc_form(type);

				Unloading();
			}
		});
	}
}

function new_product_load(po_type=''){
	var testData = [];
	var inquiry_type=$("#inquiry_type").val();
	//$("#product_id").html("");
	var mainurl = root_domain+ crm_domain +'app/product_load/index.php?mode=product_load&inquiry_type='+inquiry_type+'&type=rejection_pro_search&search=production_pro_search&po_type='+po_type;
	$.getJSON(mainurl, function(json) {
		var arr=new Array();
		var len=json[0].length;
		//console.log(json);

		for(var i=0;i<len;i++)
		{	
			testData.push({ id:json['0'][i] ,text: json['1'][i]});
				//alert(json['1'][i]);
			}
		});

	return testData;
}

function load_reject_products(po_type = '')
{
	// po_type = $("#rejection_pro_type").val();

	// console.log("load products");
	$('#new_product_reject').select2({
		data: new_product_load(po_type),
		placeholder: 'Search Product',
		width: "100%",
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
}



function load_product_unit(product_id){
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain + production_domain + 'app/working_process_details_list/',
		data: { mode : "get_product_unit", product_id:product_id },
		success: function(data){
			$('#new_unit_id').empty().html(data);
			$('#new_unit_id').select2({
				width: "100%"
			})
			Unloading();
		}
	});
}

function get_jobcard_qty(p_id,type){
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain + production_domain + 'app/working_process_details_list/',
		data: { mode : "get_jobcard_qty", p_id:p_id },
		success: function(data){
			$("#pending_"+type+"_qty").empty().html(data.trim());
			Unloading();
		}
	});
}

function get_jobcard_process_list(p_id){
	var product_id = $("#product_id_model").val();
	var process_id = $("#process_id").val();
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain + production_domain + 'app/working_process_details_list/',
		data: { mode : "get_jobcard_process_list", p_id:p_id, current_process_id :process_id,product_id:product_id },
		success: function(data){
			$("#new_process_id").empty().html(data);	
			$("#new_process_id").select2({
				width : "100%"
			});
			Unloading();
		}
	});
}

function reset_temp_qc_form(type){
	$("#p_id_accept").val('').trigger('change');
	$("#p_id_reject").val('').trigger('change');
	$("#p_id_reprocess").val('').trigger('change');
	$("#new_godown_id_accept").val('').trigger('change');
	$("#new_godown_id_reject").val('').trigger('change');
	$("#new_godown_id_reprocess").val('').trigger('change');
	$("#p_id_accept_qty").val('');
	$("#p_id_reject_qty").val('');
	$("#p_id_reprocess_qty").val('');
	$("#p_id_accept_reason").val('');
	$("#p_id_reject_reason").val('');
	$("#p_id_reprocess_reason").val('');
	$("#pending_accept_qty").empty().html(0);
	$("#pending_reject_qty").empty().html(0);
	$("#pending_reprocess_qty").empty().html(0);
	if(type == "reject"){
		$("#new_product_reject").empty();
		load_reject_products();
		$("#new_unit_id").empty();
	}
	if(type == "reprocess"){
		$("#new_process_id").val('').trigger('change');
	}
}


function load_jobcard_datatable(p_id,type){
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain + production_domain + 'app/working_process_details_list/',
		data: { mode : "load_jobcard_datatable", p_id:p_id,type:type},
		success: function(data){
			$("#"+type+"_row_data").empty().html(data);
			Unloading();
		}
	});
}


function remove_temp_qc_data(temp_id,type,sel_p_id,qty){
	var p_id = $("#p_id").val();
	var total_qc_qty = 0;
	if($("#total_qc_qty").val() != ""){
		total_qc_qty = parseFloat($("#total_qc_qty").val());
	}
	total_qc_qty = total_qc_qty - parseFloat(qty);

	var p_id_qty = 0;
	var p_id_accept_qty = 0;
	var p_id_reject_qty = 0;
	var p_id_reprocess_qty = 0;

	if($("#end_qty"+sel_p_id).val() != ""){
		p_id_qty = parseFloat($("#end_qty"+sel_p_id).val());
	}
	if($("#accept_qty"+sel_p_id).val() != ""){
		p_id_accept_qty = parseFloat($("#accept_qty"+sel_p_id).val());
	}
	if($("#reject_qty"+sel_p_id).val() != ""){
		p_id_reject_qty = parseFloat($("#reject_qty"+sel_p_id).val());
	}
	if($("#reprocess_qty"+sel_p_id).val() != ""){
		p_id_reprocess_qty = parseFloat($("#reprocess_qty"+sel_p_id).val());
	}

	p_id_qty = p_id_qty - qty;
	if(type == "accept"){
		p_id_accept_qty = p_id_accept_qty - qty;
	}
	if(type == "reject"){
		p_id_reject_qty = p_id_reject_qty - qty;	
	}
	if(type == "reprocess"){
		p_id_reprocess_qty = p_id_reprocess_qty - qty;	
	}
	var r= confirm(" Are you want to delete ?");

		if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + production_domain + 'app/working_process_details_list/',
			data: { mode : "delete_temp_qc", temp_id:temp_id},
			success: function(data){
				if(data.trim() == '1'){
					toastr.success("QC QTY DELETE SUCCESSFULLY", "SUCCESS");
						$("#total_qc_qty").val(total_qc_qty);
						$("#lbl_qc_qty").empty().html(total_qc_qty);
						$("#end_qty"+sel_p_id).val(p_id_qty);
						$("#accept_qty"+sel_p_id).val(p_id_accept_qty);
						$("#reject_qty"+sel_p_id).val(p_id_reject_qty);
						$("#reprocess_qty"+sel_p_id).val(p_id_reprocess_qty);
						load_jobcard_datatable(p_id,type);
				}else{
					toastr.warning("SOMETHING WRONG", "ERROR");
				}
				Unloading();
			}
		});
	}
}