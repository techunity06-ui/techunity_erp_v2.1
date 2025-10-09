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
	
	//alert(process_id);
	
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_process_store_approval_detail_list/',
			data: { mode : "fetch_working",process_id:process_id,process_type:process_type,product_id:product_id,branch_id:branch_id,type:type},
			success: function(response)
			{
				//alert(response);
				$('#dynamic_table_working').html(response);
				Unloading();
				
			}
	}); 
}

// model wise process start code start pathik : 20-08-2021
function approve_using_model(p_ids) 
{
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_process_store_approval_detail_list/',
			data: { mode : "approve_using_model",p_ids:p_ids},
			success: function(response)
			{
				//alert(response);
				$('#store_approval_data').html(response);
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

function store_approve_qty_using_model(){
	
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
		   var p_id=$("#p_id").val();
    $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_store_approve/',
		data: { mode : mode,pid:bomObj.pidChecked,pid_wise_start_qty:bomObj.start_qtyChecked,max_available_qty:max_available_qty,pending_qty:pending_qty,product_base_unit:product_base_unit,branch_id:branch_id,product_id:product_id,process_id:process_id,product_version:product_version,remark:remark,start_qty:start_qty,p_id:p_id },
		success: function(response)
		{
			if(response == '1') {
					toastr.success("PROCESS APPROVED SUCCESSFULLY", "SUCCESS");
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			$('#production_process_start_stop_model').modal('hide');
			load_datatable();
		
		}
	});  
}
