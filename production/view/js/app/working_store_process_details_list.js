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
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_store_process_details_list/',
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
			url: root_domain+production_domain+'app/working_store_process_details_list/',
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

function start_process_using_model(p_ids,product_name) 
{
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_store_process_details_list/',
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


function check_start_validation(p_id){
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
		var qty_variation = $("#qty_variation"+pid).val();
		if (typeof qty_variation === "undefined") {
			qty_variation = 0;
		}

		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		
		if(start_qty>0){
			if(qty_variation == '1'){
				$("#trid"+pid+"").css("background-color", "");
					bomObj.start_qtyChecked.push(start_qty);
					bomObj.pidChecked.push(pid);
					bomObj.working_qtyChecked.push(working_qty);
					total_qty += parseFloat(start_qty);
			}else{
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
			$("#actual_qty"+pid).val(start_qty);
			
		}else{
			$("#actual_qty"+pid).val(0);
		}
		
		$("#start_qty").val(total_qty);
		$("#stop_qty").val(total_qty);
		
		
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
	show_product_details(p_ids);
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_store_process_details_list/',
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
				$("#product_detail_data").show();

				$('#client_id,#sales_order_id,#job_card_no,#usertype_id,#product_scrap_id,#machine,.qty_variation,.jobcard_close').select2({
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
		var qty_variation = $("#qty_variation"+pid).val();
		if (typeof qty_variation === "undefined") {
			qty_variation = 0;
		}
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		if(start_qty>0){
			if(qty_variation == '1'){
				$("#trid"+pid+"").css("background-color", "");
				bomObj.start_qtyChecked.push(start_qty);
				bomObj.pidChecked.push(pid);
				bomObj.working_qtyChecked.push(working_qty);
				total_qty += parseFloat(start_qty);
			}else{
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
		bomObj.material_product_id = [];
		bomObj.material_used_qty = [];
		bomObj.material_pid = [];
		bomObj.material_godown_action = [];
		bomObj.material_godown_id = [];
		bomObj.qty_variation = [];
		bomObj.jobcard_close = [];
		bomObj.actual_end_qty = [];
		bomObj.process_stock = [];
		bomObj.rp_id = [];
		bomObj.batch_wise_stock_manage = [];


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
	var total_actual_qty = 0;

	$('input.used_material_qty').each(function(index){ 
		var used_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var pro_id=$(this).attr("data-product_id");
		var godown_id=$(this).attr("data-godown_id");
		var material_qty=parseFloat($(this).attr("data-material_qty"));
		var process_stock=$(this).attr("data-process_stock");
		var rp_id=$(this).attr("data-request_id");
		var batch_wise_stock_manage = $("#batch_wise_stock_manage"+(index+1)).val();

		if(isNaN(used_qty)){ used_qty=0; }
		if(isNaN(material_qty)){ material_qty=0; }
		if(used_qty>0){
			if(used_qty>material_qty){
				  toastr.warning("Used Qty not Grater Thean Qty Material Qty", "WARNING"); 
				  errorlog +=parseFloat(1);
				  $("#total_used_qty"+(index+1)).focus()
			}else{
				var batch_ded_qty = $("#total_batch_ded_qty"+(index+1)).val();

				if(batch_wise_stock_manage == 1){
					if(batch_ded_qty == used_qty){
						bomObj.material_product_id.push(pro_id);
						bomObj.material_used_qty.push(used_qty);
						bomObj.material_pid.push(pid);
						bomObj.material_godown_id.push(godown_id);
						bomObj.process_stock.push(process_stock);
						bomObj.rp_id.push(rp_id);
						bomObj.batch_wise_stock_manage.push(batch_wise_stock_manage);
					}else{
						toastr.warning("BATCH QTY NOT MATCHED", "WARNING"); 
					 	errorlog +=parseFloat(1);
					 	return false;
					}	
				}else{
					bomObj.material_product_id.push(pro_id);
					bomObj.material_used_qty.push(used_qty);
					bomObj.material_pid.push(pid);
					bomObj.material_godown_id.push(godown_id);
					bomObj.process_stock.push(process_stock);
					bomObj.rp_id.push(rp_id);
					bomObj.batch_wise_stock_manage.push(batch_wise_stock_manage);
				}
			}
		}else if(used_qty == 0 || used_qty == ""){
			   errorlog +=parseFloat(1);
			   toastr.warning("Enter Used Material Qty", "WARNING"); 
			   return false;
		}

	});

	$('select.return_godown').each(function(){ 
		var godown=parseFloat($(this).val());
		bomObj.material_godown_action.push(godown);

	});

	
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
		var qty_variation = $("#qty_variation"+pid).val();
		var jobcard_close = $("#jobcard_close"+pid).val();
		var actual_end_qty = $("#actual_qty"+pid).val();
		if (typeof qty_variation === "undefined") {
			qty_variation = 0;
		}
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		if(start_qty>0){
			if(qty_variation == '1'){
				$("#trid"+pid+"").css("background-color", "");
					bomObj.start_qtyChecked.push(start_qty);
					bomObj.pidChecked.push(pid);
					bomObj.working_qtyChecked.push(working_qty);
					bomObj.qty_variation.push(qty_variation);
					bomObj.jobcard_close.push(jobcard_close);
					bomObj.actual_end_qty.push(actual_end_qty);
					total_qty += parseFloat(start_qty);
					total_actual_qty+= parseFloat($("#actual_qty"+pid).val());
			}else{
				if(start_qty>working_qty){
				  toastr.warning("Grater Thean Qty In Jobcard No : "+jobcard_no+"", "WARNING"); 
				   $("#trid"+pid+"").css("background-color", "red");
					errorlog +=parseFloat(1);
				}else{
					$("#trid"+pid+"").css("background-color", "");
					bomObj.start_qtyChecked.push(start_qty);
					bomObj.pidChecked.push(pid);
					bomObj.working_qtyChecked.push(working_qty);
					bomObj.qty_variation.push(qty_variation);
					bomObj.jobcard_close.push(jobcard_close);
					bomObj.actual_end_qty.push(actual_end_qty);
					total_qty += parseFloat(start_qty);
					total_actual_qty+= parseFloat($("#actual_qty"+pid).val());
				}	
			}
			
		}
		
	});
	if(errorlog>"0"){
		// toastr.warning("END QTY NOT Grater Than Working Qty", "WARNING"); 
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

		/*console.log("total_qc_qty : " + total_qc_qty)
		console.log("total_end_qty : " + total_end_qty)
		console.log("total_qty : " + total_qty)*/

		if(total_qc_qty != total_end_qty){
			toastr.warning("PLEASE CHECK END QTY MUST BE EQUAL TO QC QTY1", "WARNING"); 
		  	return false;
		}
		/*if(total_qty != total_end_qty){
			toastr.warning("PLEASE CHECK END QTY MUST BE EQUAL TO QC QTY2", "WARNING"); 
		  	return false;
		}*/
	
	}
	if(isNaN(total_actual_qty)){ 
		total_actual_qty = total_qty; 
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
			total_actual_end_qty:total_actual_qty,
			batch_no:batch_no,
			batch_man_no:batch_man_no,
			start_stop_user_id:start_stop_user_id,
			product_scrap_id:product_scrap_id,
			scrap_unit : scrap_unit,
			scrap_qty:scrap_qty,
			auto_store_relese:auto_store_relese,
			machine : machine,
			process_end_time_qc:process_end_time_qc,
			material_product_id:bomObj.material_product_id,
			material_used_qty:bomObj.material_used_qty,
			material_pid:bomObj.material_pid,
			material_godown_action:bomObj.material_godown_action,
			material_godown_id:bomObj.material_godown_id,
			qty_variation : bomObj.qty_variation,
			jobcard_close : bomObj.jobcard_close,
			actual_end_qty : bomObj.actual_end_qty,
			process_stock : bomObj.process_stock,
			rp_id : bomObj.rp_id,
			batch_wise_stock_manage : bomObj.batch_wise_stock_manage
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
			// location.reload();
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
		url: root_domain+production_domain+'app/working_store_process_details_list/',
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
		url: root_domain+production_domain+'app/working_store_process_details_list/',
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


function next_page(){
	
	var bomObj = {};
		bomObj.pidChecked = [];
		bomObj.start_qtyChecked = [];
		bomObj.working_qtyChecked=[];

	var working_qty = $("#max_available_qty").val();
	var stop_qty = $("#stop_qty").val();
		
		
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
	var total_actual_qty=0;
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
				  toastr.warning("Grater Then Qty In Jobcard No : "+jobcard_no+"", "WARNING"); 
				   $("#trid"+pid+"").css("background-color", "red");
					errorlog +=parseFloat(1);
			}else{
				$("#trid"+pid+"").css("background-color", "");
				bomObj.start_qtyChecked.push(start_qty);
				bomObj.pidChecked.push(pid);
				bomObj.working_qtyChecked.push(working_qty);
				total_qty += parseFloat(start_qty);

				total_actual_qty+= parseFloat($("#actual_qty"+pid).val());
			}
		}
		
	});
	if(errorlog>"0"){
		toastr.warning("Grater Than Qty", "WARNING"); 
		return false;
	}

	var reorder_qty=$("#reorder_qty").val();
	var wo_qty = total_qty / reorder_qty;
	if(reorder_qty != "" && reorder_qty > 0){
			if(!isInteger(wo_qty)){
				toastr.warning("You Can't Process. Reorder Quantity is " + reorder_qty, "ERROR");
				return false;	
		}
	}

	Loading();


	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/working_store_process_details_list/',
		data: {
			 mode : "show_raw_material_data",
			 p_ids:bomObj.pidChecked,
			 pid:bomObj.pidChecked,
			 pid_wise_start_qty:bomObj.start_qtyChecked,
			 working_qty : working_qty,
			 stop_qty : stop_qty
		},
		success: function(response)
		{
			//alert(response);
			$('#start_stop_data_material').html(response);
			$("#start_stop_data").hide();
			$("#end_process_desc_data").hide();
			$("#start_stop_data_material").show();
			 $("#stop_qty").val(stop_qty);
			 $(".return_godown").select2({
			 	width: "100%"
			 });
			// $("html, body").animate({ scrollTop: 0 }, "slow");
			 // $("html, body").scrollTop($("#start_stop_data_material").offset().top);
			 setTimeout(function(){
			 	$(window).scrollTop(50);
			 },300);
			 
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

	load_add_qc_view(total_end_qty);
	
}

function delete_temp_qc_data(){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/working_store_process_details_list/',
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
	var working_qty = $("#max_available_qty").val();
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
		url: root_domain+production_domain+'app/working_store_process_details_list/',
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
			unit_id : unit_id,
			working_qty:working_qty 
		},
		success: function(response)
		{	
			$("#end_process_desc_data").show();
			$("#start_stop_data").hide();
			$("#product_detail_data").hide();
			$("#end_process_desc_details").empty().html(response);


			$(".select_jobcard,.return_godown,.qty_variation").select2({
				width : "100%"
			});
			$(".jobcard_close").select2({
				width : "100%",
				readonly : true
			});
			$(".jobcard_close").select2("readonly", true);

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
	$("#product_detail_data").show();
	$("#start_stop_data").show();
	$("#end_process_desc_data").hide();
	$("#start_stop_data_material").hide();
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
	/*console.log(pending_qty)
	console.log(qty)*/
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
			url: root_domain+production_domain+'app/working_store_process_details_list/',
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
					$("#actual_qty"+sel_p_id).val(p_id_qty);
					$("#accept_qty"+sel_p_id).val(p_id_accept_qty);
					$("#reject_qty"+sel_p_id).val(p_id_reject_qty);
					$("#reprocess_qty"+sel_p_id).val(p_id_reprocess_qty);

					calculate_accept_qty(sel_p_id);

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
		url: root_domain + production_domain + 'app/working_store_process_details_list/',
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
		url: root_domain + production_domain + 'app/working_store_process_details_list/',
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
		url: root_domain + production_domain + 'app/working_store_process_details_list/',
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
		url: root_domain + production_domain + 'app/working_store_process_details_list/',
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
			url: root_domain + production_domain + 'app/working_store_process_details_list/',
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


function show_product_details(p_ids){
	Loading();

	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/working_store_process_details_list/',
		data: {
			 mode : "get_product_details_data",
			 p_ids:p_ids
		},
		success: function(response)
		{
			//alert(response);
			$("#product_detail_data").empty().html(response)
			$("#start_stop_data").show();
			$("#start_stop_data_material").hide();
			$("#machine").select2({
				width : "100%"
			})
			
			Unloading();
		}
	});
}


function convert_qty(type,id,product_id){

	if(type==2){
		var conv_qty_hide=$("#total_used_qty"+id).val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(5);

		var base_qty_hide=$("#total_used_qty2_"+id).val();
	}else{
		var base_qty_hide=$("#total_used_qty"+id).val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(5);
		
		var conv_qty_hide=$("#total_used_qty2_"+id).val();
	}

	/*console.log(base_qty_hide)
	console.log(conv_qty_hide)*/
	
	// var base_qty=$("#total_used_qty"+id).val();
	// var conv_qty=$("#total_used_qty2_"+id).val();

		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_store_process_details_list/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty_hide,conv_qty:conv_qty_hide,product_id:product_id},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
					$("#total_used_qty2_"+id).val(arr.show_qty.trim());
					// $("#total_used_qty2_"+id).val(arr.show_qty.trim());
				
			}
		});

}

function toggle_actual_qty_readonly (flag,p_id){
	var qty = $("#end_qty"+p_id).val();
	if(qty<="0"){
		toastr.warning("Please Add Jobcard End Qty", "WARNING"); 
		$("#qty_variation"+p_id).select2('val',0);
		return false;
	}
	// console.log(flag + ' - ' + p_id);
	var id = '#actual_qty'+p_id; 
	if(flag == '1'){
		// console.log('enable')
		$(id).prop('readOnly', false);
		$(id).removeAttr('readonly');
		$("#jobcard_close"+p_id).select2("readonly", false);
	}else{
		// console.log('readOnly')
		$(id).prop('readOnly', true);
		$(id).attr('readonly', 'readonly');
		$("#jobcard_close"+p_id).select2("readonly", true);
		$("#jobcard_close"+p_id).select2("val","0");
		$(id).val(qty);
	}
}

function calculate_accept_qty(p_id){
	var end_qty = $("#actual_qty"+p_id).val();
	var accept_qty = 0;
	var reject_qty = $("#reject_qty"+p_id).val();
	var reprocess_qty = $("#reprocess_qty"+p_id).val();


	accept_qty = end_qty - reject_qty - reprocess_qty;

	$("#accept_qty"+p_id).val(accept_qty);

}


function show_material_model(p_ids,product_id){
	
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_store_process_details_list/',
			data: { 
				mode : "get_material_deduct_details_data",
				p_id:p_ids,
				product_id:product_id
			},
			success: function(response)
			{

				$("#metirial_deduct_row").empty().html(response);
				$("#material_deduct_modal").modal('show');
				Unloading();
			}
	});
}


function process_material_deduct(){

	var bomObj = {};
		bomObj.material_product_id = [];
		bomObj.material_used_qty = [];
		bomObj.material_pid = [];
		bomObj.material_godown_id = [];
		bomObj.process_stock = [];
		bomObj.rp_id = [];
		bomObj.batch_wise_stock_manage = [];


	var errorlog=0;
	var total_qty=0;
	var total_actual_qty = 0;

	$('input.used_material_qty').each(function(index){

		var used_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var pro_id=$(this).attr("data-product_id");
		var godown_id=$(this).attr("data-godown_id");
		var material_qty=parseFloat($(this).attr("data-material_qty"));
		var process_stock=$(this).attr("data-process_stock");
		var rp_id=$(this).attr("data-request_id");
		var batch_wise_stock_manage = $("#batch_wise_stock_manage"+(index+1)).val();

		if(isNaN(used_qty)){ used_qty=0; }
		if(isNaN(material_qty)){ material_qty=0; }
		if(used_qty>0){
			if(used_qty>material_qty){
				  toastr.warning("Used Qty not Grater Thean Qty Material Qty", "WARNING"); 
				  errorlog +=parseFloat(1);
				  $("#total_used_qty"+(index+1)).focus()
			}else{
				
				var batch_ded_qty = $("#total_batch_ded_qty"+(index+1)).val();

				if(batch_wise_stock_manage == 1){
					if(batch_ded_qty == used_qty){
						bomObj.material_product_id.push(pro_id);
						bomObj.material_used_qty.push(used_qty);
						bomObj.material_pid.push(pid);
						bomObj.material_godown_id.push(godown_id);
						bomObj.process_stock.push(process_stock);
						bomObj.rp_id.push(rp_id);
						bomObj.batch_wise_stock_manage.push(batch_wise_stock_manage);
					}else{
						toastr.warning("BATCH QTY NOT MATCHED", "WARNING"); 
					 	errorlog +=parseFloat(1);
					 	return false;
					}	
				}else{
					bomObj.material_product_id.push(pro_id);
					bomObj.material_used_qty.push(used_qty);
					bomObj.material_pid.push(pid);
					bomObj.material_godown_id.push(godown_id);
					bomObj.process_stock.push(process_stock);
					bomObj.rp_id.push(rp_id);
					bomObj.batch_wise_stock_manage.push(batch_wise_stock_manage);
				}
			}
		}else if(used_qty == 0 || used_qty == ""){
			   errorlog +=parseFloat(1);
			   toastr.warning("Enter Used Material Qty", "WARNING"); 
			   $("#total_used_qty"+(index+1)).focus()
			   return false;
		}

	});


	if(errorlog>"0"){
		// toastr.warning("USED QTY NOT Grater Than Material Qty", "WARNING"); 
		return false;
	}

	Loading();

	 $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/working_store_process_details_list/',
		data: { 
			mode : 'material_qty_deduct_start_time',
			material_product_id:bomObj.material_product_id,
			material_used_qty:bomObj.material_used_qty,
			material_pid:bomObj.material_pid,
			material_godown_id:bomObj.material_godown_id,
			process_stock : bomObj.process_stock,
			rp_id : bomObj.rp_id,
			batch_wise_stock_manage : bomObj.batch_wise_stock_manage
		 },
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if (arr.msg == '1') {
				toastr.success("MATERIAL DEDUCT SUCCESSFULLY", "SUCCESS");
				$("#material_deduct_modal").modal('hide');
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			
			Unloading();
		}
	}); 
}


function open_batch_wise_qty(id,type){

		var enter_base_qty = $("#total_used_qty"+id).val();
		var enter_conv_qty = $("#total_used_qty2_"+id).val();
		
		if(enter_base_qty=="" || enter_base_qty=='0')
		{		
			toastr.warning("Enter Qty", "ERROR")
			$("#total_used_qty"+id).focus();
			$("#total_used_qty"+id).focus();
			$("#total_used_qty"+id).css('border','1px solid red')
			return false;
		}else{
			$("#total_used_qty"+id).css('border','1px solid #ccc')
		}

		var bt_qty = $("#total_used_qty"+id).attr('data-material_qty');
		var p_id = $("#total_used_qty"+id).attr('data-pid');
		var product_id = $("#total_used_qty"+id).attr('data-product_id');
		var rp_id = $("#total_used_qty"+id).attr('data-request_id');
		var process_stock = $("#total_used_qty"+id).attr('data-process_stock');
		var unit_id = $("#total_used_qty"+id).attr('data-unit_id');
		var conv_unit_id = $("#total_used_qty"+id).attr('data-conv_unit_id');

		if(parseFloat(enter_base_qty) > parseFloat(bt_qty)){
			$("#total_used_qty"+id).css('border','1px solid red')
			toastr.warning("You Can't Enter Greater than material Qty", "ERROR");
			return false;
		}else{
			$("#total_used_qty"+id).css('border','1px solid #ccc')
		}

		delete_temp_batch_wise_deduct_qty(p_id,product_id);
		
		Loading()
		$.ajax({
			type: "POST",
			url: root_domain+ production_domain+'app/working_store_process_details_list/',
			data: { 
				mode : "batch_stock_model_open",
				enter_base_qty:enter_base_qty,
				enter_conv_qty:enter_conv_qty,
				product_id:product_id,
				p_id:p_id,
				rp_id:rp_id,
				process_stock:process_stock
			},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#modal_base_qty").html(enter_base_qty);
				$("#modal_conv_qty").html(enter_conv_qty);
				$("#modal_enter_base_qty").val(enter_base_qty);
				$("#modal_enter_conv_qty").val(enter_conv_qty);
				$("#modal_product_id").val(product_id);
				$("#modal_p_id").val(p_id);
				$("#modal_rp_id").val(rp_id);
				$("#modal_unit_id").val(unit_id);
				$("#modal_conv_unit_id").val(conv_unit_id);
				$("#modal_edit_id").val(id);
				$("#modal_process_stock").val(process_stock);
				$("#modal_type").val(type);
				$('#bs-batch_wise_stock-modal').modal('show');
				$("#batch_data").html(data.html_data);	
				
				Unloading();
			}
		});
	}

function delete_temp_batch_wise_deduct_qty(p_id,product_id){
		Loading()
				$.ajax({
					type: "POST",
					url: root_domain+ production_domain+'app/working_store_process_details_list/',
					data: { 
						mode : "delete_temp_batch_wise_deduct_qty",
						product_id:product_id,
						type :$("#modal_type").val(), //  1 for start time , 2 for end time deduct
						p_id : p_id,
					},
					success: function(response)
					{
						Unloading()
					}
				});
}
	function add_batch_wise_deduct_qty(){
		var enter_base_qty = $("#modal_enter_base_qty").val();
		var enter_conv_qty = $("#modal_enter_conv_qty").val();
		var product_id = $("#modal_product_id").val();
		
		var process_stock = $("#modal_process_stock").val();
		var base_unit = $("#modal_unit_id").val();
		var conv_unit = $("#modal_conv_unit_id").val();

		/*var p_id = $("#modal_p_id").val();
		var rp_id =	$("#modal_rp_id").val();*/
		
		var arr_p_id = [];
		var arr_rp_id = [];
		var arr_mt_trn_id = [];
		var reserve_id = [];
		var batch_qty = [];
		var batch_conv_qty = [];
		var total_batch_qty = 0;
		$('input.deduct_base_stock').each(function(index){ 
			var qty=parseFloat($(this).val());
			var conv_qty=parseFloat($("#deduct_conv_stock_"+(index+1)).val());
			var stock_id=$(this).attr("data-reserve_id");
			var p_id=$(this).attr("data-p_id");
			var rp_id=$(this).attr("data-rp_id");
			var mt_trn_id=$(this).attr("data-mt_trn_id");
			if(isNaN(qty)){ 
				qty=0; 
			}
			
			if(qty>0){
				total_batch_qty = total_batch_qty + qty;
				batch_qty.push(qty);
				batch_conv_qty.push(conv_qty);
				reserve_id.push(stock_id);
				arr_p_id.push(p_id);
				arr_rp_id.push(rp_id);
				arr_mt_trn_id.push(mt_trn_id);
			}
		});

		var totalQty = $('input.deduct_base_stock').toArray().reduce(function(total, currentElement) {
		    var qty = parseFloat($(currentElement).val()) || 0; // Parse float, default to 0 if parsing fails
		    return total + qty;
		}, 0);
		total_batch_qty = total_batch_qty.toFixed(5);	
		enter_base_qty = parseFloat(enter_base_qty);	
		enter_base_qty = enter_base_qty.toFixed(5);	
		
		
		if(parseFloat(total_batch_qty) != parseFloat(enter_base_qty)){
			toastr.warning("BATCH QTY MUST EQUAL TO DEDUCT QTY", "ERROR");
			return false;
		}

		var id = $("#modal_edit_id").val();
		
		Loading()
		$.ajax({
			type: "POST",
			url: root_domain+ production_domain+'app/working_store_process_details_list/',
			data: { 
				mode : "add_batch_wise_deduct_qty",
				product_id:product_id,
				type : $("#modal_type").val(), //  1 for start time , 2 for end time deduct
				p_id : arr_p_id,
				rp_id : arr_rp_id,
				mt_trn_id : arr_mt_trn_id,
				process_stock : process_stock,
				arr_reserve_id :reserve_id,
				arr_base_qty : batch_qty,
				arr_conv_qty : batch_conv_qty,
				base_unit : base_unit,
				conv_unit : conv_unit,
				type : $("#modal_type").val()
			},
			success: function(response)
			{
				if(response.trim() == '1'){
					toastr.success("BATCH WISE DEDUCT QTY SUCCESSFULLY", "SUCCESS");
					$("#total_batch_ded_qty"+id).val(enter_base_qty);
					$("#total_batch_ded_conv_qty"+id).val(enter_conv_qty);
				}else{
					toastr.warning("SOMETHING WRONG.!", "ERROR");
				}
				$('#bs-batch_wise_stock-modal').modal('hide');
				Unloading()
			}
		});
	}


	function reserve_stock_convert_qty(id){
	// alert('ok')
		// console.log(id)
	var base_qty = 0;
	var conv_qty = 0;
	/*if(type==2){  // take base
		conv_qty  = $("#st_stock_reserve").val();
	}else{*/
	// }
		 base_qty = $("#deduct_base_stock_"+id).val();

	var product_id=$("#modal_product_id").val();
	
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_store_process_details_list/',
			data: { mode : "convert_qty",  type : 1,base_qty:base_qty,conv_qty:conv_qty,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				
				$("#deduct_conv_stock_"+id).val(arr.hide_qty);
				
			}
		});	
	
}