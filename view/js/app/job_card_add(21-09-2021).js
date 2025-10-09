var datatable;
$(document).ready(function() {

});
function set_data(){
	
	
	var branch_id=$("#branch_id").val();
	var product_id=$("#product_id").val();
	var bom_version_id=$("#bom_version_id").val();
	var qty=$("#qty").val();
	
	if(branch_id=="")
	{		
		toastr.warning("Select Branch Id", "ERROR");
		$("#branch_id").select2("focus");
		return false;
	}
	if(product_id=="")
	{		
		toastr.warning("Select Product", "ERROR");
		$("#product_id").focus();
		return false;
	}
	if(bom_version_id=="" )
	{		
		toastr.warning("Select Version Id", "ERROR");
		$("#bom_version_id").focus();
		return false;
	}
	
	if(qty=="")
	{		
		toastr.warning("enter Qty", "ERROR");
		$("#qty").focus();
		return false;
	}
		$.ajax({
				type: "POST",
				url: root_domain+'app/job_card_add/',
				data: { mode : "set_data",product_id:product_id,qty:qty,branch_id:branch_id,bom_version_id:bom_version_id},
				success: function(response)
				{
					next_page(product_id,qty,'',bom_version_id);
				}
		});

}
function next_page(product_id,qty,request_id,bom_version_id){
	/* var product_id=$("#product_id").val();
	var qty=$("#qty").val(); */
	var branch_id=$("#branch_id").val();
	$.ajax({
			type: "POST",
			url: root_domain+'app/job_card_add/',
			data: { mode : "show_next_page",product_id:product_id,qty:qty,request_id:request_id,branch_id:branch_id,bom_version_id},
			success: function(response)
			{
				$(".second_page").show();
				$(".first_page").hide();
				$(".second_page").html(response);
				check_submit_per();
			}
	});
}
function check_already_job_card(){
	var product_id=$("#product_id").val();
	var branch_id=$("#branch_id").val();
	if(branch_id!="" && branch_id!=0){
	//alert(product_id);
		$.ajax({
				type: "POST",
				url: root_domain+'app/job_card_add/',
				data: { mode : "check_already_job_card",product_id:product_id,branch_id:branch_id},
				success: function(response)
				{
					//alert(response);
					if(response==="1"){
						next_page(product_id);
					}
				}
		});
	}else{
		toastr.warning("Selet Branch Id", "ERROR");
	}
}
function add_product_request(rp_id)
{
	var current_stock=parseFloat($('#current_stock'+rp_id).val());
	var req_qty=parseFloat($('#req_qty'+rp_id).val());
	var req_qty_one=parseFloat($('#req_qty_one'+rp_id).val());
	var res_qty=parseFloat($('#res_qty'+rp_id).val());
	var process_qty=parseFloat($('#process_qty'+rp_id).val());
	var po_qty=parseFloat($('#po_qty'+rp_id).val());
		
		current_stock=getNum(current_stock);
		req_qty=getNum(req_qty);
		req_qty_one=getNum(req_qty_one);
		res_qty=getNum(res_qty);
		process_qty=getNum(process_qty);
		po_qty=getNum(po_qty);
		
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/job_card_add/',
	data: { 
		mode : "add_product_request",
		current_stock:current_stock,
		req_qty:req_qty,
		req_qty_one:req_qty_one,
		res_qty:res_qty,
		process_qty:process_qty,
		po_qty:po_qty,
		rp_id:rp_id
	},
	success: function(data){
			
			var resp =JSON.parse(data);
			//console.log(resp);
			if(resp.trn_ids!=0)
			{
				var exp_trn_ids=(resp.trn_ids).split(",");
				var insert_id=resp.insert_id;
				
				var i;
				
				//alert('Update from : add_product_request');
				var inh_qty=Number($('#process_qty'+insert_id).val());
				
				for (i = 0; i < exp_trn_ids.length; ++i) {
					
					var chil=Number($('#req_qty_one'+exp_trn_ids[i]).val());
					var req_qty1=parseFloat(chil)*parseFloat(inh_qty);
					req_qty1 = req_qty1.toFixed(4);
					$("#req_qty"+exp_trn_ids[i]).val(req_qty1);
					$("#basic_req_qty"+exp_trn_ids[i]).val(req_qty1);
					var pq=Number($("#process_qty"+exp_trn_ids[i]).val());
					//alert(pq);
					if(pq>0){
						$("#process_qty"+exp_trn_ids[i]).val(req_qty1);
					}else{
						$("#po_qty"+exp_trn_ids[i]).val(req_qty1);
					}
					var com1='<a class="btn btn-primary dispbtn" data-original-title="" id="reqest_btn'+exp_trn_ids[i]+'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('+exp_trn_ids[i]+')" ><i class="fa fa-paper-plane"></i> Request</a>';
					$(".action"+exp_trn_ids[i]).html(com1);
				}
				
				/*$('.csb'+insert_id).prop("disabled",true).removeClass("btn-primary").addClass("btn-danger").html("Requested");*/
				var com='<a class="btn btn-danger dispbtn" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
				$(".action"+insert_id).html(com);
				
				$('#po_qty'+insert_id).attr("readonly",true);
				$('#process_qty'+insert_id).attr("readonly",true);
				$('#res_qty'+insert_id).attr("readonly",true);
				$('#req_qty'+insert_id).attr("readonly",true);
				//$('#current_stock'+insert_id).attr("readonly",true);
				//$('.submi'+trn_id).val("0");
				//alert(trn_id);
			}
			else
			{
				var com='<a class="btn btn-danger dispbtn" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
				$(".action"+resp.insert_id).html(com);
				
				$('#po_qty'+resp.insert_id).attr("readonly",true);
				$('#process_qty'+resp.insert_id).attr("readonly",true);
				$('#res_qty'+resp.insert_id).attr("readonly",true);
				$('#req_qty'+resp.insert_id).attr("readonly",true);
				//$('#current_stock'+insert_id).attr("readonly",true);
				
			}
			//work_order_submit_per();
			check_submit_per();
			Unloading();
		}		
		
	});
}
function save_all_page_data(){
	var product_id=$("#product_id").val();
	var qty=$("#req_qty").val();
	var rp_id=$("#eid").val();
	//alert(product_id);
	//alert(qty);
	//alert(rp_id);
	 $.ajax({
			type: "POST",
			url: root_domain+'app/job_card_add/',
			data: { mode : "add",product_id:product_id,qty:qty,rp_id:rp_id},
			success: function(response)
			{
				//alert(response);
				if(response==="1"){
					toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
					window.location=root_domain+'job_card_list';
				}else{
					toastr.warning("SOMETHING WRONG", "ERROR")
				}
			}
	}); 
}
function getNum(val) {
   if (isNaN(val)) {
     return 0;
   }
   return val;
}
function error_check(rp_id,rname){
	var current_stock=parseFloat($('#current_stock'+rp_id).val());
	var req_qty=parseFloat($('#req_qty'+rp_id).val());
	var req_qty_one=parseFloat($('#req_qty_one'+rp_id).val());
	var res_qty=parseFloat($('#res_qty'+rp_id).val());
	var process_qty=parseFloat($('#process_qty'+rp_id).val());
	var po_qty=parseFloat($('#po_qty'+rp_id).val());
	var basic_req_qty=parseFloat($('#basic_req_qty'+rp_id).val());
		current_stock=getNum(current_stock);
		req_qty=getNum(req_qty);
		req_qty_one=getNum(req_qty_one);
		res_qty=getNum(res_qty);
		process_qty=getNum(process_qty);
		po_qty=getNum(po_qty);
		basic_req_qty=getNum(basic_req_qty);
	var reqbu=0;var reqbu1=0;var reqbu2=0;
	if(current_stock<res_qty){
		$("#res_qty_err"+rp_id).css("display","block");
		$("#res_qty_err"+rp_id).html("Not Add "+current_stock+" < "+res_qty+"");
		//$("#reqest_btn"+rp_id).hide();
		reqbu=0;
		//return false;
	}else{
		$("#res_qty_err"+rp_id).css("display","none");
		$("#reqest_btn"+rp_id).show();
		reqbu=1;
	}
	
	var used_qty=parseFloat(res_qty) + parseFloat(process_qty) + parseFloat(po_qty);
	//alert(used_qty);
	if(req_qty<used_qty){
		$("#reqest_btn"+rp_id).hide();
		 reqbu1=0;
		$("#"+rname+"_err"+rp_id).css("display","block");
		$("#"+rname+"_err"+rp_id).html("Not Add "+req_qty+" < "+used_qty+"");
	}else if(req_qty<used_qty){
		$("#reqest_btn"+rp_id).hide();
		reqbu1=0;
		$("#"+rname+"_err"+rp_id).css("display","block");
		$("#"+rname+"_err"+rp_id).html("Not Add "+req_qty+" > "+used_qty+"");
	}else if(req_qty===used_qty){
		$("#reqest_btn"+rp_id).show();
		reqbu1=1;
		$("#"+rname+"_err"+rp_id).css("display","none");
	}
	
	if(basic_req_qty>req_qty){
		$("#req_qty_err"+rp_id).css("display","block");
		$("#req_qty_err"+rp_id).html("Enter Minimum "+basic_req_qty);
		$("#reqest_btn"+rp_id).hide();
		reqbu2=0;
	}else{
		$("#req_qty_err"+rp_id).css("display","none");
		$("#reqest_btn"+rp_id).show();
		reqbu2=1;
	}
	
	var ccc=parseFloat(reqbu2)+parseFloat(reqbu)+parseFloat(reqbu1);
	if(ccc==3){
		$("#reqest_btn"+rp_id).show();
	}else{
		$("#reqest_btn"+rp_id).hide();
	}
}
function view_process(rp_id){
	var product_name=$("#product_name").val();
	var req_qty=$("#req_qty").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/job_card_add/',
		data: { mode : "view_process",rp_id:rp_id,product_name:product_name,req_qty:req_qty },
		success: function(response)
		{
			$("#show_process").html(response);
			$("#process_id").select2({width: '100%'});
			$("#bs-update_process_data").modal("show");
			view_process_list(rp_id);
		}
	});	
}
function view_process_list(rp_id){
	$.ajax({
		type: "POST",
		url: root_domain+'app/job_card_add/',
		data: { mode : "view_process_list",rp_id:rp_id },
		success: function(response)
		{
			$("#show_process_list").html(response);
		}
	});	
}
function edit_product_process(id)
{
	//var form_mode=$("#jobwork_outward_add #mode").val();
	//alert(id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/job_card_add/',
		data: { mode : "preedit_process",  id : id },
		success: function(response)
		{
			//console.log(response);
			//alert(response);
			var data = jQuery.parseJSON(response);
			//alert(data);
			$('#process_id').select2("val",data.process_id);
			$('#process_priority').val(data.process_priority);
			$('#process_type').val(data.process_type);
			$('#process_time').val(data.process_time);
			$("#edit_id_process").val(id);
			$("#add_process").val("Update");
			
			Unloading();
		}
	});
}

function delete_data_process(id)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/job_card_add/',
				data: { mode : "delete_data_process",  eid : id },
				success: function(response)
				{
					//console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						show_product_process();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function add_process_value()
{
	if($("#process_id").val()==="")
	{		
		toastr.warning("Select Process Id", "ERROR");
		$("#process_id").select2("focus");
		return false;
	}
	if($("#process_priority").val()==="")
	{		
		toastr.warning("Enter Process value", "ERROR");
		$("#process_priority").focus();
		return false;
	}
	if($("#process_type").val()==="")
	{		
		toastr.warning("Select Process Type", "ERROR");
		$("#process_type").focus();
		return false;
	}
	var rp_id=$('#rp_id').val();
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/job_card_add/',
		data: { 
				mode : "add_process_value",
				edit_id:$("#edit_id_process").val(),
				process_id:$("#process_id").val(),
				process_priority:$("#process_priority").val(),
				process_type:$('#process_type').val(),
				process_time:$('#process_time').val(),
				rp_id:rp_id,
				process_product_id:$('#process_product_id').val() 
			},
		success: function(response)
		{
			//console.log(response);
			//$("#product_id option[value='"+$("#product_id").val()+"']").remove();
			$("#process_id").select2("val","");
			$("#process_priority").val('');
			$("#edit_id_process").val('')
			$("#process_type").val('')
			$("#process_time").val('')
			$("#add_process").val("Add");
			
			Unloading();
			
			view_process_list(rp_id);
			
		}
	});
}
function check_submit_per(){
	
	var eid=$("#eid").val();
	$.ajax({
				type: "POST",
				url: root_domain+'app/job_card_add/',
				data: { mode : "check_submit_per", eid:eid  },
				success: function(response)
				{
					
					//console.log(response)
					if(response == "1") {
						//toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
						$("#save").hide();
					}
					else if(response == "0") {
						$("#save").show();
					}							
				}
			});
}

function check_bom_version()
{
	var product_id=$("#product_id").val();
	var branch_id=$("#branch_id").val();
	
	Loading();
		$.ajax({
				type: "POST",
				url: root_domain+'app/job_card_add/',
				data: { mode : "check_bom_version_by_product",product_id:product_id,branch_id:branch_id},
				success: function(response)
				{
					
					if(response != 0)
					{	$('#bom_version_id').html('');
						$('#bom_version_id').html(response);
						$("#bom_version_id").val("10000");
						$('#bom_version_id').trigger('change');
					}
					Unloading();
					
				}
		});
	
}
