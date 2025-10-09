//var datatable;
$(document).ready(function() {
	load_datatable();
	
	  var modal = $('#preview_batch_wise_release_model');
	  var closeButton = modal.find('.close');
  	  var btnClose = modal.find('#batch_btn_close');
	  // Function to handle modal close event
	  function closeModal() {
	    // Hide the modal using jQuery
	   $("#preview_batch_wise_release_model").modal('hide');
	    $('#store_release_model').modal('show');
	
	  }

	  // Add a click event listener to the close button using jQuery
	  closeButton.on('click', closeModal);
	  btnClose.on('click', closeModal);

});
 function batchcloseModal() {
	    // Hide the modal using jQuery
 	$("#preview_batch_wise_release_model").modal('hide');
	    $('#store_release_model').modal('show');
				
	    // Fire a custom event when the modal is closed
	  }

function load_datatable()
{
	var product_id=$('#product_id').val();
	var branch_id=$('#branch_id').val();
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_list/',
			data: { mode : "fetch_working",product_id:product_id,branch_id:branch_id },
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
	Loading();
	$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_list/',
			data: { mode : "store_release_using_model",p_ids:p_ids},
			success: function(response)
			{
					$("#store_release_data").show();
					$("#store_request_material_detail").hide();
				//alert(response);
				$('#store_release_data').html(response);
				// get_material_details(p_ids);
				
				$('#store_release_model').modal('show');
				Unloading();
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
		
		var reorder_qty=$("#reorder_qty").val();
			var wo_qty = total_qty / reorder_qty;
			/*if(reorder_qty != "" && reorder_qty > 0){
					if(!isInteger(wo_qty)){
						toastr.warning("You Can't Process. Reorder Qauntity is " + reorder_qty, "ERROR");
						return false;	
				}
			}*/
	var p_ids = $("#p_id").val();
	var previous_process_id = $("#previous_process_id").val();		
	var process_id = $("#stock_process_id").val();
	Loading();

	$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_list/',
			data: { 
				mode : "get_store_request_material_data",
				p_ids:p_ids,
				pid:bomObj.pidChecked,
				pid_wise_start_qty:bomObj.start_qtyChecked,
				previous_process_id : previous_process_id,
				process_id : process_id
			},
			success: function(response)
			{
				//alert(response);
				$('#store_request_material_detail').html(response);
				$("#store_release_data").hide();
				$("#store_request_material_detail").show();

				$("#to_user_id").select2({
					width : "50%"
				});

				$(".godown_select,.batch_no").select2({
					width : "100%"
				});

				$("hr").css("border", "1px !important;");
    			$("hr").css("border-top", "1px solid #eee !important;");

    			$("#to_godown_id").select2({
					width : "50%",
					readonly: true
				});
				
				$("#to_godown_id").select2('readonly',true);
				Unloading();
			}
	});
}


function previous_page(){ 
	$("#store_release_data").show();
	$("#store_request_material_detail").hide();

}



function store_release(){
	
	var bomObj = {};
	bomObj.material_request_id = [];
	bomObj.pidChecked = [];
	bomObj.start_qtyChecked = [];
	bomObj.material_pid = [];
	bomObj.material_qty = [];
	bomObj.material_product_id = [];
	bomObj.material_actual_qty = [];
	// bomObj.working_qtyChecked=[];
		
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

	var id = 1;
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();
		var start_qty=parseFloat($(this).val());
		var pid=$(this).attr("data-pid");

		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
		
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		if(start_qty>0){
			
				$("#trid"+pid+"").css("background-color", "");
				bomObj.start_qtyChecked.push(start_qty);
				bomObj.pidChecked.push(pid);
				// bomObj.working_qtyChecked.push(working_qty);
				total_qty += parseFloat(start_qty);
		}


		var m_total_qty=0;	

		$('input.material_qty_'+id).each(function(){ 
			var m_start_qty=parseFloat($(this).val());
			var m_pid=$(this).attr("data-pid");
				var m_working_qty=parseFloat($(this).attr("data-req_qty"));
				var m_work_order_no=$(this).attr("data-work_order_no");
				var product_id=$(this).attr("data-product_id");
				var actual_qty = $(this).attr("data-start_qty");
				var request_id=$(this).attr("data-request_id");
				var m_start_qty= $("#total_rel_material_"+m_pid+"_"+product_id+"_"+request_id).val();
				if(isNaN(m_start_qty)){ m_start_qty=0; }
				if(isNaN(m_working_qty)){ m_working_qty=0; }
			if(m_start_qty>0){
				
				console.log(m_start_qty + ' -- ' + m_working_qty)
					if(m_start_qty<m_working_qty){
						// console.log(m_pid+' -- '+ product_id + '--' + request_id);
						$("#tr_release_row_"+m_pid+"_"+product_id).css("border", "1px solid red");	
						errorlog +=parseFloat(1);
						// toastr.warning("Less Thean Qty In Work Order No : " + m_work_order_no, "WARNING"); 
						$('#request_btn').hide();
						
					}
					m_total_qty += parseFloat(m_start_qty);
					bomObj.material_pid.push(m_pid);
					bomObj.material_qty.push(m_start_qty);
					bomObj.material_actual_qty.push(m_working_qty);
					bomObj.material_product_id.push(product_id); 
					bomObj.material_request_id.push(request_id); 
					$("#tr_release_row_"+m_pid+"_"+product_id).css("border", "none");	
			}else{
				// console.log(m_pid+' -- '+ product_id);
				errorlog +=parseFloat(1);
				// toastr.warning("Please Release Qty for Work Order No : " + m_work_order_no, "WARNING"); 
					$('#request_btn').hide();
					$("#tr_release_row_"+m_pid+"_"+product_id).css("border", "1px solid red");	
					
			}
		});
		id++;
	});
	
	if(errorlog>"0"){
		toastr.warning("Release Qty not less than required qty.", "WARNING"); 
		return false;
	}
	
	var mode=$("#mode").val();
	var max_available_qty=$("#max_available_qty").val();
	var pending_qty=$("#pending_qty").val();
	var product_base_unit=$("#product_base_unit").val();
	var branch_id=$("#branch_id_model").val();
	var product_id=$("#product_id_model").val();
	var process_id=$("#process_id").val();
	var previous_process_id=$("#previous_process_id").val();
	var product_version=$("#product_version").val();
	var remark=$("#remark").val();
	var start_qty=$("#start_qty").val();
	var issue_no=$("#issue_no").val();
	var issue_date=$("#issue_date").val();

    if($("#to_godown_id").val() == ""){
		if(remark == ""){
				toastr.warning("Please Select To Godown", "WARNING"); 
			return false;
		}
	}

	 if($("#to_user_id").val() == ""){
		if(remark == ""){
			toastr.warning("Please Select To User", "WARNING"); 
			return false;
		}
	}
		    
    if($("#remark").attr("data-required") == "yes"){
		if(remark == ""){
				toastr.warning("Please enter remark", "WARNING"); 
			return false;
		}
	}


	if(errorlog>"0"){
		toastr.warning("Enter Material Quantity", "WARNING"); 
		return false;
	}

		   Loading();

		  
    $.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/production_store_release/',
		data: { 
				mode : mode,
				pid:bomObj.pidChecked,
				pid_wise_start_qty:bomObj.start_qtyChecked,
				max_available_qty:max_available_qty,
				pending_qty:pending_qty,
				product_base_unit:product_base_unit,
				branch_id:branch_id,
				product_id:product_id,
				process_id:process_id,
				product_version:product_version,
				remark:remark,
				start_qty:start_qty,
				previous_process_id:previous_process_id,
				issue_no:issue_no,
				issue_date:issue_date,
				material_pid: bomObj.material_pid,
				material_qty: bomObj.material_qty,
				material_product_id: bomObj.material_product_id,
				material_actual_qty:bomObj.material_actual_qty,
				to_godown_id:$("#to_godown_id").val(),
				to_user_id:$("#to_user_id").val(),
				release_no:$("#release_no").val(),
				release_date:$("#release_date").val(),
				material_request_id:bomObj.material_request_id 
			},

		success: function(response)
		{
			if(response == '1') {
				toastr.success("MATERIAL RELEASE SUCCESSFULLY", "SUCCESS");
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			$('#store_release_model').modal('hide');
			load_datatable();
			Unloading();		
		}
	});  
}


function check_start_validation(){
	var bomObj = {};
	bomObj.pidChecked = [];
	bomObj.start_qtyChecked = [];
	bomObj.working_qtyChecked=[];
	$('#sp_btn').show();
		//alert("dsa");
	var total_qty=0;	
	$('input.start_qty').each(function(){ 
     	//bstart_qty[i++]=$(this).val();

		var start_qty=parseFloat($(this).val());
		// console.log('-->' + start_qty);
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-start_qty"));
		var jobcard_no=$(this).attr("data-jobcardno");
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		
		if(start_qty>0){
			if(start_qty>working_qty){
				  toastr.warning("Grater Thean Qty In Jobcard No : "+jobcard_no+"", "WARNING"); 
				   $("#trid"+pid+"").css("background-color", "#f5b0b0");
				   $('#sp_btn').hide();
			}else{
				
				$("#trid"+pid+"").css("background-color", "");
				bomObj.start_qtyChecked.push(start_qty);
				bomObj.pidChecked.push(pid);
				bomObj.working_qtyChecked.push(working_qty);
				total_qty += parseFloat(start_qty);
			}
			
		}
		
		$("#start_qty").val(total_qty);
		$("#total_req_qty").val(total_qty);
	});
}

function check_material_start_validation(id){
	// console.log(id);
	var total_qty=0;	
	$('input.material_txt_qty_'+id).each(function(){ 
     	
		var start_qty=parseFloat($(this).val());
		
		var pid=$(this).attr("data-pid");
		var working_qty=parseFloat($(this).attr("data-req_qty"));
		var product_name=$(this).attr("data-product");
		var work_order_no=$(this).attr("data-work_order_no");
		
		if(isNaN(start_qty)){ start_qty=0; }
		if(isNaN(working_qty)){ working_qty=0; }
		
		if(start_qty>0){
			 
			
			if(start_qty<working_qty){
				  toastr.warning("Less Thean Qty In Work Order No : " + work_order_no, "WARNING"); 
				   $('#request_btn').hide();
				      // $(this).css("border", "3px solid #ff0000");

				      	start_qty = working_qty;
				   		$(this).val(working_qty);
				   		 // $(this).css("border", "");
				      

			}else{
				   $(this).css("border", "");
			}
			total_qty += parseFloat(start_qty);
			
		}
		$("#material_total"+id).html(total_qty.toFixed(5));
	});
} 


function load_stock_qty(batch_wise_manage,product_id,unit_id,p_id,rp_id,process_stock='0',process_id=""){
	var godown_id = $('#godown_id'+p_id+'_'+product_id+'_'+rp_id).val();
	var stock_id = $('#batch_no'+p_id+'_'+product_id+'_'+rp_id).val();
	var previous_p_id = $("#previous_process_id").val();
	var batch_no = "";
	if(batch_wise_manage == '1'){
		batch_no = $('#batch_no'+p_id+'_'+product_id+'_'+rp_id).val();
	}
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_release_list/',
		data: { mode : "load_stock_qty", product_id:product_id,unit_id:unit_id,godown_id:godown_id,process_stock:process_stock,p_id:p_id,stock_id:stock_id,previous_p_id:previous_p_id },
		success: function(response){
			//console.log(data);
			var data=jQuery.parseJSON(response);
			if(godown_id != ""){
				$('#stock_qty'+p_id+'_'+product_id+'_'+rp_id).val(parseFloat(data.stock_1));
				$("#release_qty"+p_id+'_'+product_id+'_'+rp_id).attr("max",parseFloat(data.stock_1));
				$('#stock_qty2'+p_id+'_'+product_id+'_'+rp_id).val(parseFloat(data.stock_2));
				if(batch_wise_manage == '1' && batch_no == ""){
					$('#stock_qty'+p_id+'_'+product_id+'_'+rp_id).val(0);
					$("#release_qty"+p_id+'_'+product_id+'_'+rp_id).attr("max",0);
					$('#stock_qty2'+p_id+'_'+product_id+'_'+rp_id).val(0);
				}

			}else{
				$('#stock_qty'+p_id+'_'+product_id+'_'+rp_id).val(0);
				$("#release_qty"+p_id+'_'+product_id+'_'+rp_id).attr("max",0);
				$('#stock_qty2'+p_id+'_'+product_id+'_'+rp_id).val(0);
			}
			
			Unloading();
		}		
	});
}

function add_field(batch_wise_manage,p_id,rp_id,parent_rp_id,product_id,process_id="")
{
	var from_godown_id = $("#from_godown_id").val();
	var to_godown_id = $("#to_godown_id").val();

	var qty = parseFloat($("#release_qty"+p_id+'_'+product_id+'_'+rp_id).val());
	var unit_id  =$("#unit_id"+p_id+'_'+product_id+'_'+rp_id).val();
	var godown_id = $("#godown_id"+p_id+'_'+product_id+'_'+rp_id).val();
	var stock_id = $("#batch_no"+p_id+'_'+product_id+'_'+rp_id).val();
	var batch_no = $("#batch_no"+p_id+'_'+product_id+'_'+rp_id).find(':selected').attr('data-batch_no');
	
	var stock_qty = parseFloat($('#stock_qty'+p_id+'_'+product_id+'_'+rp_id).val());
	
	var previous_process_id = $("#previous_process_id").val(); 

	if(!$("#godown_id"+p_id+'_'+product_id+'_'+rp_id).val()){
		toastr.warning("Select Godown", "ERROR");
		$("#godown_id"+p_id+'_'+product_id+'_'+rp_id).select2('focus');
		return false;
	}
	if(batch_wise_manage == '1'){
		if(!$("#batch_no"+p_id+'_'+product_id+'_'+rp_id).val()){
			toastr.warning("Select Batch No", "ERROR");
			$("#batch_no"+p_id+'_'+product_id+'_'+rp_id).focus();
			return false;
		}	
	}
	else if($("#release_qty"+p_id+'_'+product_id+'_'+rp_id).val() == '' || $("#release_qty"+p_id+'_'+product_id+'_'+rp_id).val() == '0'){
		toastr.warning("Enter Release Qty", "ERROR");
		$("#release_qty"+p_id+'_'+product_id+'_'+rp_id).focus();
		return false;
	}
	
	console.log(qty) ;
	console.log(stock_qty);
	if(qty > stock_qty){
		toastr.warning("RELEASE QTY NOT BE GREATER THAN STOCK QTY .", "ERROR")
		return false;		
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_release_list/',
		data: {	 
				mode : "fieldadd",
				product_id:product_id,
				rp_id : rp_id,
				parent_rp_id : parent_rp_id,
				p_id : p_id,
				unit_id:unit_id,
				qty:qty,
				godown_id:godown_id,
				stock_id:stock_id,
				batch_no:batch_no,
				process_id:process_id
			},
		success: function(response)
		{
			$("#godown_id"+p_id+'_'+product_id).val('').trigger('change') ;;
			$("#godown_id"+p_id+'_'+product_id).select2('focus');
			$("#stock_qty"+p_id+'_'+product_id).val("");
			//$("#unit_id"+p_id+'_'+product_id).val("");
			$("#release_qty"+p_id+'_'+product_id).val('');
			$("#release_qty"+p_id+'_'+product_id).attr('max',0);
			$("#release_qty2"+p_id+'_'+product_id).val('');
			$('#addrow').val('Add');
			load_material_trn_data(p_id,product_id,unit_id,previous_process_id,rp_id);
			Unloading();
		}
	});
}


function load_material_trn_data(p_id,product_id,unit_id,previous_process_id,rp_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_release_list/',
		data: {	 
				mode : "load_material_tempoutward",
				product_id:product_id,
				p_id : p_id,
				unit_id:unit_id,
				previous_process_id:previous_process_id,
				rp_id : rp_id
			},
		success: function(response)
		{
			Unloading();
			$("#release_productdata"+p_id+'_'+product_id+'_'+rp_id).empty().html(response);
		}
	});
}


function delete_material_temp_data(material_trn_id,p_id,product_id,unit_id,rp_id){
	var res= confirm(" Are you want to delete ?");
	var previous_process_id = $("#previous_process_id").val() 
	
	if(res) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_list/',
			data: { mode:"delete_data", material_trn_id:material_trn_id },
			success: function(response)
			{
				//console.log(response);
				var data=jQuery.parseJSON(response);
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					// show_data();
					load_material_trn_data(p_id,product_id,unit_id,previous_process_id,rp_id);
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}


function load_batch_no(godown_id,product_id,unit_id,p_id,process_stock='0',process_id="",rp_id="",parent_rp_id=""){
	$('#stock_qty'+p_id+'_'+product_id).val('');
			$("#release_qty"+p_id+'_'+product_id).attr("max",0);
			$('#stock_qty2'+p_id+'_'+product_id).val('');
	

	var previous_process_id = $("#previous_process_id").val();		
	var process_id = $("#stock_process_id").val();
	
	show_batch_wise_stock_modal(godown_id,product_id,unit_id,p_id,previous_process_id,process_id,rp_id,parent_rp_id,unit_id)

	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_release_list/',
		data: { mode : "load_batch_no",  godwn_id : godown_id,product_id:product_id,unit_id:unit_id,previous_process_id:previous_process_id,process_id:process_id},
		success: function(responce){
			
			$('#batch_no'+p_id+'_'+product_id).html(responce);
			$('#batch_no'+p_id+'_'+product_id).select2("val","");
		}
	});
}

function show_batch_wise_stock_modal(godown_id,product_id,unit_id,p_id,previous_process_id,process_id,rp_id,parent_rp_id){
	var req_qty = $('#mt_req_qty_'+p_id+'_'+product_id).val();
	
	if(godown_id != 0){
		Loading()
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_list/',
			data: { 
				mode : "batch_wise_stock_details",  
				godwn_id : godown_id,
				product_id:product_id,
				unit_id:unit_id,
				previous_process_id:previous_process_id,
				process_id:process_id,
				req_qty : req_qty,
				rp_id : rp_id,
				parent_rp_id : parent_rp_id,
				p_id:p_id
			},
			success: function(response){
				Unloading();		
				$("#batch_wise_details").empty().html(response);
				
				$("#transfer_godown_id").select2({
					width:"100%"
				})
			$('[data-toggle="tooltip"]').tooltip();
				$('#store_release_model').modal('hide');
				$("#preview_batch_wise_release_model").modal('show');

			}
		});
	}
}


function convert_qty(type,p_id,product_id,rp_id){

	if(type==2){
		var conv_qty_hide=$("#release_qty"+p_id+'_'+product_id+'_'+rp_id).val();
		var s=parseFloat(conv_qty_hide);
		results = s.toFixed(3);

		var base_qty_hide=$("#release_qty2"+p_id+'_'+product_id+'_'+rp_id).val();
	}else{
		var base_qty_hide=$("#release_qty"+p_id+'_'+product_id+'_'+rp_id).val();
		var d=parseFloat(base_qty_hide);
		resultb = d.toFixed(3);
		
		var conv_qty_hide=$("#release_qty2"+p_id+'_'+product_id+'_'+rp_id).val();
	}
	
	// var base_qty=$("#release_qty"+p_id+'_'+product_id).val();
	// var conv_qty=$("#release_qty2"+p_id+'_'+product_id).val();


	// console.log(base_qty)
	// console.log(conv_qty)
	
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_list/',
			data: { 
				mode : "convert_qty",  
				type : type,
				base_qty:base_qty_hide,
				conv_qty:conv_qty_hide,
				product_id:product_id
			},
			success: function(response)
			{
				
				var arr = jQuery.parseJSON(response);			
					$("#release_qty2"+p_id+'_'+product_id+'_'+rp_id).val(arr.show_qty.trim());
					// $("#release_qty2"+p_id+'_'+product_id).val(arr.show_qty.trim());
				
			}
		});

}

function load_batch_godown(batch_wise_manage,product_id,unit_id,p_id,process_stock='0',process_id=""){
	if($('#godown_id'+p_id+'_'+product_id).val() != ""){
		return false;
	}
	var stock_id = $('#batch_no'+p_id+'_'+product_id).val();
	var previous_p_id = $("#previous_process_id").val();
	var batch_no = "";
	if(batch_wise_manage == '1'){
		batch_no = $('#batch_no'+p_id+'_'+product_id).val();
	}
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_release_list/',
		data: {
			mode : "load_batch_godown", 
			product_id:product_id,
			unit_id:unit_id,
			process_stock:process_stock,
			p_id:p_id,
			stock_id:stock_id,
			previous_p_id:previous_p_id 
		},
		success: function(response){
			//console.log(data);
			var data=jQuery.parseJSON(response);
			$('#godown_id'+p_id+'_'+product_id).select2('val',data.godown_id);
			load_stock_qty(batch_wise_manage,product_id,unit_id,p_id,process_stock,process_id);
			Unloading();
		}
	});
}

function get_stock_mfg_date(batch_wise_manage,product_id,unit_id,p_id,process_stock='0',process_id=""){
	var godown_id = $('#godown_id'+p_id+'_'+product_id).val();
	var stock_id = $('#batch_no'+p_id+'_'+product_id).val();
	var previous_p_id = $("#previous_process_id").val();
	var batch_no = "";
	if(batch_wise_manage == '1'){
		batch_no = $('#batch_no'+p_id+'_'+product_id).val();
	}
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_release_list/',
		data: { mode : "get_stock_mfg_date", product_id:product_id,unit_id:unit_id,godown_id:godown_id,process_stock:process_stock,p_id:p_id,stock_id:stock_id,previous_p_id:previous_p_id },
		success: function(response){
			//console.log(data);
			$('#mfg_date'+p_id+'_'+product_id).empty().text(response);
			Unloading();
		}		
	});
}

function material_convert_qty(type,product_id,cnt){
	// alert('ok')
	var base_qty = 0;
	var conv_qty = 0;
	if(type==2){  // take base
		conv_qty  = $("#material_qty"+cnt).val();
	}else{
		 base_qty = $("#material_qty"+cnt).val();
	}

	// var product_id=$("#product_id_model").val();
	
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/store_release_list/',
			data: { mode : "convert_qty",  type : type,base_qty:base_qty,conv_qty:conv_qty,product_id:product_id},
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				
				 $("#conv_material_qty"+cnt).val(arr.hide_qty);
				
			}
		});	

}

function toggle_res_batch_godown(val) {
	if(val == '1'){
		$(".res_batch_godown").show()
	}else{
		$(".res_batch_godown").hide()
	}
}


function batch_wise_stock_add(){
	var checbox_checked_len = $('input:checkbox:checked').length;
	var errorlog=0;
	var batch_qty = 0;
	var stock_id = [];
	var qty = [];
	var batch_no = [];

	var transfer_stock_id = [];
	var transfer_batch_no = [];

	var batch_wise_p_id = $('#batch_wise_p_id').val();
	var unit_id = $('#batch_wise_unit_id').val();
	var batch_wise_rp_id = $('#batch_wise_rp_id').val();
	var previous_process_id = $('#batch_wise_previous_process_id').val();
	var batch_wise_parent_rp_id = $('#batch_wise_parent_rp_id').val();
	var batch_wise_product_id = $('#batch_wise_product_id').val();
	var batch_wise_process_id = $('#batch_wise_process_id').val();
	var batch_wise_godown_id = $("#batch_wise_godown_id").val();
	var batch_wise_parent_rp_id = $('#batch_wise_parent_rp_id').val();
	// var total_temp_batch_qty = $('total_temp_batch_qty').val();

	var godown_transfer = $('input[name="opt_godown_transfer"]:checked').val();

	var transfer_godown_id = $("#transfer_godown_id").val();

	if(godown_transfer == '1' && transfer_godown_id == ''){
		toastr.warning("SELECT GODOWN FOR TRANSFER STOCK", "ERROR")
	}
	//console.log(godown_transfer);

	if(checbox_checked_len < 1)
	{
		toastr.warning("Please Select at least 1 checkbox ", "ERROR")
		return false;
	}
	else
	{
		$("input:checkbox").each(function (index) {
			if(index > 0){
				var stock_qty = 0;
			var id = 0;
			var enter_qty = 0;
			if($(this).is(":checked")){
				id =  $(this).attr("value");
				stock_qty = $("#reserve_batch_qty"+id).attr('data-current_stock_qty');
				enter_qty = $("#reserve_batch_qty"+id).val();
				stk_id = $("#reserve_batch_qty"+id).attr('data-stock_id');
				bt_no = $("#reserve_batch_qty"+id).attr('data-batch_no');
				if(parseFloat(enter_qty) > 0){
					if(parseFloat(enter_qty) > parseFloat(stock_qty)){
						errorlog +=parseFloat(1);
						$("#reserve_batch_qty"+id).focus();
						toastr.warning("YOU CAN'T ENTER MORE THAN STOCK QTY.", "WARNING"); 
						return false;
					}else{
						// batch_qty = batch_qty + enter_qty;
						qty.push(enter_qty);
						stock_id.push(stk_id);
						batch_no.push(bt_no);
					}
				}else{
					errorlog +=parseFloat(1);
						$("#reserve_batch_qty"+id).focus();
						toastr.warning("ENTER RELEASE QTY.", "WARNING"); 
						return false;
				}
				
			}else{
				id =  $(this).attr("value");
				stk_id = $("#reserve_batch_qty"+id).attr('data-stock_id');
				bt_no = $("#reserve_batch_qty"+id).attr('data-batch_no');

				transfer_stock_id.push(stk_id);
				transfer_batch_no.push(bt_no);
			}
			}
		});   

	}

	if(errorlog>"0"){
		// toastr.warning("Release Qty not less than required qty.", "WARNING"); 
		return false;
	}

	Loading();
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'/app/store_release_list/',
			data: { 
				mode : "batch_wise_stock_fieldadd", 
				p_id : batch_wise_p_id,
				rp_id:batch_wise_rp_id,
				parent_rp_id:batch_wise_parent_rp_id,
				product_id:batch_wise_product_id,
				process_id:batch_wise_process_id,
				stock_id:stock_id,
				qty:qty,
				godown_id : batch_wise_godown_id,
				batch_no:batch_no,
				unit_id:unit_id,
				godown_transfer: godown_transfer,
				transfer_godown_id:transfer_godown_id,
				transfer_stock_id:transfer_stock_id,
				transfer_batch_no:transfer_batch_no
			},

			success: function(response){

				var arr = jQuery.parseJSON(response);			
				if(arr.msg == '1') {
					Unloading();
					toastr.success("BATCH WISE STOCK ADDED SUCCESSFULLY", "SUCCESS");
					// setTimeout(function(){
					//  window.location.reload(); 
					// },500);			
				}
				else if(arr.msg == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}			
				$("#checkAll").prop('checked', false);
				$("#preview_batch_wise_release_model").modal('hide');
				Unloading();
				load_material_trn_data(batch_wise_p_id,batch_wise_product_id,unit_id,previous_process_id,batch_wise_rp_id);
				$('#store_release_model').modal('show');
			}		 
		}); 
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