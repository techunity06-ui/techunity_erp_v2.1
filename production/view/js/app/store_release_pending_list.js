//var datatable;
$(document).ready(function() {
	load_datatable();
	
});

function load_datatable()
{
	var product_id=$('#product_id').val();
	var branch_id=$('#branch_id').val();
	var process_id=$('#process_id').val();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/store_release_pending_list/',
			data: { mode : "fetch_working",product_id:product_id,branch_id:branch_id,process_id,process_id },
			success: function(response)
			{
				
				$('#dynamic_table_working').html(response);
				Unloading();
				
			}
	}); 
}

// model wise process start code start pathik : 20-08-2021
function store_release_using_model(p_ids) 
{
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/store_release_pending_list/',
			data: { mode : "store_release_using_model",p_ids:p_ids},
			success: function(response)
			{
				//alert(response);
				$('#store_release_data').html(response);
				// get_material_details(p_ids);
				
				$('#store_release_model').modal('show');
			}
	});
}


function next_page(){ 
		var bomObj = {};
		bomObj.pidChecked = [];
		bomObj.start_qtyChecked = [];
var total_qty=0;
		$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");
		var jobcard_no=$(this).attr("data-jobcardno");
		
		
		if(isNaN(start_qty)){ start_qty=0; }
		
		if(start_qty>0){
				$("#trid"+pid+"").css("background-color", "");
				bomObj.start_qtyChecked.push(start_qty);
				bomObj.pidChecked.push(pid);
				total_qty += parseFloat(start_qty);
		}
		
	});
		
	var p_ids = $("#p_id").val();

	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/store_release_pending_list/',
			data: { mode : "get_store_request_material_data",p_ids:p_ids,pid:bomObj.pidChecked,pid_wise_start_qty:bomObj.start_qtyChecked},
			success: function(response)
			{
				//alert(response);
				$('#store_request_material_detail').html(response);
				$("#store_release_data").hide();
				$("#store_request_material_detail").show();
			}
	});
}


function previous_page(){ 
	$("#store_release_data").show();
	$("#store_request_material_detail").hide();


}
/*
function get_material_details(p_ids){
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/store_release_pending_list/',
			data: { mode : "store_release_using_model",p_ids:p_ids},
			success: function(response)
			{
				//alert(response);
				$('#store_request_material_detail').html(response);

			}
	});
}*/