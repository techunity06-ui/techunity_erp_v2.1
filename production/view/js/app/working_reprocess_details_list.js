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
			url: root_domain+production_domain+'app/working_reprocess_details_list/',
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
function start_process_using_model(p_ids,product_name) 
{
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_reprocess_details_list/',
			data: { mode : "start_process_using_model",p_ids:p_ids},
			success: function(response)
			{
				//alert(response);
				$('#start_stop_data').html(response);
				$('#process_name').html('Start');
				$("#model_product_name").html(product_name);
				$('#production_process_start_stop_model').modal('show');

				Unloading();
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
	var start_qty = $("#start_qty").val();
	var max_available_qty = $("#max_available_qty").val();
	if(start_qty==""){
		toastr.warning("Enter Quantity", "WARNING"); 
		  return false;
	}

	if(parseFloat(start_qty) > parseFloat(max_available_qty)){
		toastr.warning("Grater Thean Qty", "WARNING"); 
		  return false;
	}

	var total_qty=0;
	var errorlog=0;
	
	
	var mode=$("#mode").val();
		
	// var max_available_qty=$("#max_available_qty").val();
    var pending_qty=$("#pending_qty").val();
    var product_base_unit=$("#product_base_unit").val();
    var branch_id=$("#branch_id_model").val();
    var product_id=$("#product_id_model").val();
    var process_id=$("#process_id").val();
    var product_version=$("#product_version").val();
    var remark=$("#remark").val();
    var p_id=$("#p_id").val();
    // var start_qty=$("#start_qty").val();
	   
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/working_reprocess_details_list/',
		data: { 
			mode : mode,
			p_id:p_id,
			max_available_qty:max_available_qty,
			pending_qty:pending_qty,
			product_base_unit:product_base_unit,
			branch_id:branch_id,
			product_id:product_id,
			process_id:process_id,
			remark:remark,
			start_qty:start_qty,
			
		},
		success: function(response)
		{
			if(response == '1') {
					toastr.success("REPROCESS STARTED SUCCESSFULLY", "SUCCESS");
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

function end_process_using_model(p_ids,product_name) 
{
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/working_reprocess_details_list/',
			data: { mode : "end_process_using_model",p_ids:p_ids},
			success: function(response)
			{
				//alert(response);
				$('#start_stop_data').html(response);
				$('#process_name').html('End');
				$("#model_product_name").html(product_name);
				$('#production_process_start_stop_model').modal('show');

				Unloading();
			}
	});
}
function process_end_using_model(){
var stop_qty = $("#stop_qty").val();
	var max_available_qty = $("#max_available_qty").val();
	if(stop_qty==""){
		toastr.warning("Enter Quantity", "WARNING"); 
		  return false;
	}

	if(parseFloat(stop_qty) > parseFloat(max_available_qty)){
		toastr.warning("Grater Thean Qty", "WARNING"); 
		  return false;
	}

	var errorlog=0;
	var total_qty=0;
	
	var mode=$("#mode").val();
	var max_available_qty=$("#max_available_qty").val();
	var pending_qty=$("#pending_qty").val();
	var product_base_unit=$("#product_base_unit").val();
	var branch_id=$("#branch_id_model").val();
	var product_id=$("#product_id_model").val();
	var process_id=$("#process_id").val();
	// var product_version=$("#product_version").val();
	var remark=$("#remark").val();
	var stop_qty=$("#stop_qty").val();
	var grn_godown=$("#grn_godown").val();
	// var batch_no = $("#batch_id").val();
	var p_id=$("#p_id").val();
	var batch_id=$("#batch_id").val();
	var reprocess_qc_id=$("#reprocess_qc_id").val();
	
	Loading();
		  
    $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/working_reprocess_details_list/',
		data: { 
			mode : mode,
			p_id:p_id,
			max_available_qty:max_available_qty,
			pending_qty:pending_qty,
			product_base_unit:product_base_unit,
			branch_id:branch_id,
			product_id:product_id,
			process_id:process_id,
			remark:remark,
			stop_qty:stop_qty,
			grn_godown:grn_godown,
			batch_id:batch_id,
			reprocess_qc_id:reprocess_qc_id,
			// batch_no:batch_no 
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
			load_datatable();

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

