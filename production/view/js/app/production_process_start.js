//var datatable;
$(document).ready(function() {
	
});

//Start Process modal 
function show_material_list()
{
	
	var p_id=$('#p_id').val();//allocate ID
	var max_start_qty=parseFloat($('#start_qty').val());//allocate ID
	var max_available_qty=parseFloat($('#max_available_qty').val());//allocate ID
	if(isNaN(max_start_qty)){
		max_start_qty=0;
	}
	if(max_start_qty!="0"){
		if(max_start_qty <= max_available_qty ){
			Loading();
			$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/production_process_start/',
			data: { mode : 'show_material_list_new',p_id:p_id,max_start_qty:max_start_qty,pending_qty:max_available_qty},
			success: function(data){
					$('#sub_row_mat').html(data);
					$("#sp_btn").show();
					Unloading();
				}		
				
			});
		}else{
			toastr.warning("Not Enter More then Available Qty1", "ERROR");
			$("#sp_btn").hide();
			$('#sub_row_mat').html("");
		}
	}else{
			toastr.warning("Not Enter 0/blank value", "ERROR");
			$("#sp_btn").hide();
			$('#sub_row_mat').html("");
		}
}

$("#start_allocate_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	/* if (!$("#start_allocate_add").valid()) {
		return false;
	} */
	var product_qty_hid=parseFloat($("#max_available_qty").val());
	var machine_no=parseFloat($("#start_qty").val());
	//alert(machine_no);
	//alert(product_qty_hid);
	if(machine_no>product_qty_hid){
		toastr.warning("Check Process Qty", "ERROR")
		return false;
	}
	//return false;
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	var process_id_hid = $('#process_id').val();
	var process_type_hid = $('#process_type_hid').val();
	var redirect_page = $('#redirect_page').val();
	
	//alert(process_id_hid);
	//alert(process_type_hid);
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/production_process_start/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//alert(response);
			//console.log(response);	
			//var arr = jQuery.parseJSON(response);			
			if(response == '1') {
				Unloading();
				
				toastr.success("PROCESS STARTED SUCCESSFULLY", "SUCCESS");
				
				//if(redirect_page=='working_process_detail_list' || redirect_page==''){
					window.location=root_domain+production_domain+'working_process_detail_list/'+process_id_hid+'/1';
				//}else{
					//window.location=root_domain+production_domain+redirect_page;
				//}
				
				
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(response == 'update')
			{	
				toastr.success("BILL UPDATED SUCCESSFULLY", "SUCCESS");		
			
				Unloading();
				if ($("#save_print").val() == '1')
				{	
					window.location=root_domain+production_domain+'invoicereceipt/'+arr.eid+'/'+arr.printstatus;
				}
				else
				{
					window.location=root_domain+production_domain+'invoice_list';
				}		
			}
			$('#start_allocate_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function get_series_no(){
	
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/allocate_process/',
		data: { mode : "get_series_no" },
		success: function(resp){
			//console.log(resp);
			$('#invoicetype_id').val(resp);	
			load_jobcard_no(resp);
		}		
	});	
}

function load_jobcard_no(id)
{
	//alert(id);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/allocate_process/',
		data: { mode : "load_invoiceno", typeid : id},
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#pr_job_no').val(no.invoiceno);
		}
	});
}

