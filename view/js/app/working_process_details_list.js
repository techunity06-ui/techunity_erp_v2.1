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
	
	$.ajax({
			type: "POST",
			url: root_domain+'app/working_process_details_list/',
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
function start_process_using_model(p_ids) 
{
	$.ajax({
			type: "POST",
			url: root_domain+'app/working_process_details_list/',
			data: { mode : "start_process_using_model",p_ids:p_ids},
			success: function(response)
			{
				//alert(response);
				$('#start_stop_data').html(response);
				$('#production_process_start_stop_model').modal('show');
			}
	});
}


function check_start_validation(){
	var bomObj = {};
	bomObj.pidChecked = [];
	bomObj.start_qtyChecked = [];
	bomObj.working_qtyChecked=[];
		
		//alert("dsa");
	
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		var total_qty=0;
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
	var errorlog=0;
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
		var total_qty=0;
		
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
		var mode=$("#mode").val();
		var max_available_qty=$("#max_available_qty").val();
		   var pending_qty=$("#pending_qty").val();
		   var product_base_unit=$("#product_base_unit").val();
		   var branch_id=$("#branch_id_model").val();
		   var product_id=$("#product_id_model").val();
		   var process_id=$("#process_id").val();
		   var product_version=$("#product_version").val();
		   var remark=$("#remark").val();
		   var start_qty=$("#start_qty").val();
    $.ajax({
		type: "POST",
		url: root_domain+'app/production_process_start/',
		data: { mode : mode,pid:bomObj.pidChecked,pid_wise_start_qty:bomObj.start_qtyChecked,max_available_qty:max_available_qty,pending_qty:pending_qty,product_base_unit:product_base_unit,branch_id:branch_id,product_id:product_id,process_id:process_id,product_version:product_version,remark:remark,start_qty:start_qty },
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
		
		}
	});  
}

// model wise process start code End pathik : 20-08-2021

// model wise process End code start pathik : 20-08-2021

function end_process_using_model(p_ids) 
{
	$.ajax({
			type: "POST",
			url: root_domain+'app/working_process_details_list/',
			data: { mode : "end_process_using_model",p_ids:p_ids},
			success: function(response)
			{
				//alert(response);
				$('#start_stop_data').html(response);
				$('#production_process_start_stop_model').modal('show');
			}
	});
}
function process_end_using_model(){
	
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
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
		var total_qty=0;
		
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
		  
		  
    $.ajax({
		type: "POST",
		url: root_domain+'app/production_process_end/',
		data: { mode : mode,pid:bomObj.pidChecked,pid_wise_end_qty:bomObj.start_qtyChecked,max_available_qty:max_available_qty,pending_qty:pending_qty,product_base_unit:product_base_unit,branch_id:branch_id,product_id:product_id,process_id:process_id,product_version:product_version,remark:remark,stop_qty:stop_qty,grn_godown:grn_godown },
		success: function(response)
		{
			if(response == '1') {
					toastr.success("PROCESS End SUCCESSFULLY", "SUCCESS");
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			$('#production_process_start_stop_model').modal('hide');
			load_datatable();
		
		}
	});  
}

// model wise process End code End pathik : 20-08-2021

