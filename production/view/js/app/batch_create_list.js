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
			url: root_domain+production_domain+'app/batch_create_list/',
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


function batch_generate_model(p_ids,product_name) 
{
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/batch_create_list/',
			data: { mode : "batch_generate_model",p_ids:p_ids},
			success: function(response)
			{
				$('#batch_product_name').html(product_name);
				$('#batch_generate_data').html(response);
				$('#preview_batch_generate_model').modal('show');
				Unloading();
			}
	});
}


function check_batch_validation(){
	var bomObj = {};
	bomObj.pidChecked = [];
	bomObj.batch_qtyChecked = [];
	bomObj.working_qtyChecked=[];
		
		//alert("dsa");
	var total_qty=0;
	$('input.batch_qty').each(function(){ 
     	//bbatch_qty[i++]=$(this).val();
		var batch_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-batch_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
		
		if(isNaN(batch_qty)){ batch_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		
		if(batch_qty>0){
			if(batch_qty>working_qty){
				  toastr.warning("Grater Thean Qty In Jobcard No : "+jobcard_no+"", "WARNING"); 
				   $("#trid"+pid+"").css("background-color", "red");
					
			}else{
				$("#trid"+pid+"").css("background-color", "");
				bomObj.batch_qtyChecked.push(batch_qty);
				bomObj.pidChecked.push(pid);
				bomObj.working_qtyChecked.push(working_qty);
				total_qty += parseFloat(batch_qty);
			}
			
		}
		$("#batch_qty").val(total_qty);
	});
} 



function generate_batch_using_model(){
	
	var bomObj = {};
		bomObj.pidChecked = [];
		bomObj.batch_qtyChecked = [];
		bomObj.working_qtyChecked=[];
		bomObj.batch_no = [];
		bomObj.work_order_no= [];
		bomObj.work_order_id= [];
		
	var so_stock=(document.getElementsByName('batch_qty1[]'));
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
	$('input.batch_qty').each(function(){ 
     	//bbatch_qty[i++]=$(this).val();
		var batch_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-batch_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
		
		
		if(isNaN(batch_qty)){ batch_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		if(batch_qty>0){
			if(batch_qty>working_qty){
				  toastr.warning("Grater Thean Qty In Jobcard No : "+jobcard_no+"", "WARNING"); 
				   $("#trid"+pid+"").css("background-color", "red");
					errorlog +=parseFloat(1);
			}else{
				$("#trid"+pid+"").css("background-color", "");
				bomObj.batch_qtyChecked.push(batch_qty);
				bomObj.pidChecked.push(pid);
				bomObj.working_qtyChecked.push(working_qty);
				total_qty += parseFloat(batch_qty);
			}
		}
		
	});
	$('input.batch_no').each(function(){ 
	
	var batch_no=$(this).val();
	bomObj.batch_no.push(batch_no);

	});

	if($('#batch_no').val()==""){
		toastr.warning("Enter Batch No.", "WARNING"); 
		  return false;
	}
	
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

		var mode=$("#batch_mode").val();
		
		var max_available_qty=$("#batch_max_available_qty").val();
		   var pending_qty=$("#batch_pending_qty").val();
		   var product_base_unit=$("#batch_product_base_unit").val();
		   var branch_id=$("#batch_branch_id_model").val();
		   var product_id=$("#batch_product_id_model").val();
		   var process_id=$("#batch_process_id").val();
		   var product_version=$("#batch_product_version").val();
		   
		   var batch_qty=$("#batch_qty").val();
		   var  batch_manu_no = $("#batch_no").val();

			var reorder_qty=$("#reorder_qty").val();
			var wo_qty = batch_qty / reorder_qty;
			if(reorder_qty != "" && reorder_qty > 0){
					if(!isInteger(wo_qty)){
						toastr.warning("You Can't Process. Reorder Qauntity is " + reorder_qty, "ERROR");
						return false;	
				}
			} 

		   Loading();
		   
    $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/batch_create_list/',
		data: { 
				mode : mode,
				pid:bomObj.pidChecked,
				pid_wise_batch_qty:bomObj.batch_qtyChecked,
				max_available_qty:max_available_qty,
				pending_qty:pending_qty,
				product_base_unit:product_base_unit,
				branch_id:branch_id,
				product_id:product_id,
				process_id:process_id,
				product_version:product_version,
				batch_qty:batch_qty,
				batch_no:bomObj.batch_no,
				work_order_no:bomObj.work_order_no,
				work_order_id:bomObj.work_order_id,
				batch_manu_no: batch_manu_no
			},
		success: function(response)
		{
			if(response == '1') {
					toastr.success("BATCH GENERATE SUCCESSFULLY", "SUCCESS");
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			$('#preview_batch_generate_model').modal('hide');
			load_datatable();

			Unloading();
		
		}
	});  
}

