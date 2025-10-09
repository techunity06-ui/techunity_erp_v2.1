//var datatable;
$(document).ready(function() {
	show_material_list();
});

 $("#end_allocate_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	//alert("fadu");
	if (!$("#end_allocate_add").valid()) {
		return false;
	}
	var qq=parseFloat($("#max_available_qty").val());
	var pp=parseFloat($("#stop_qty").val());
	if(isNaN(qq)){qq=0;}
	if(isNaN(pp)){pp=0;}
	//alert(qq);
	//alert(pp);
	if(qq<pp){
		toastr.warning("Qty Not Metch", "ERROR")
		return false;
	}
	//alert("hi");
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//alert("fadu");
	var process_id_hid = $('#process_id').val();
	var process_type_hid = $('#process_type_hid').val();
	var redirect_page = $('#redirect_page').val();
	
	$.ajax({
		cache:false,
		url: root_domain+'app/production_process_end/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//alert(response);
			//var arr = jQuery.parseJSON(response);			
			if(response == '1') {
				Unloading();
				
				toastr.success("PROCESS STARTED SUCCESSFULLY", "SUCCESS");
				//alert('fdj');
				//window.location=root_domain+'process_detail_list/'+process_id_hid+'/'+process_type_hid;
				
				//if(redirect_page=='working_process_detail_list' || redirect_page==''){
					window.location=root_domain+'working_process_detail_list/'+process_id_hid+'/2';
				//}else{
					//window.location=root_domain+redirect_page;
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
					window.location=root_domain+'invoicereceipt/'+arr.eid+'/'+arr.printstatus;
				}
				else
				{
					window.location=root_domain+'invoice_list';
				}		
			}
			$('#start_allocate_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function show_material_list()
{
	
	var p_id=$('#p_id').val();//allocate ID
	var max_start_qty=parseFloat($('#stop_qty').val());//allocate ID
	var max_available_qty=parseFloat($('#max_available_qty').val());//allocate ID
	if(isNaN(max_start_qty)){
		max_start_qty=0;
	}
	if(max_start_qty!="0"){
		if(max_start_qty <= max_available_qty ){
			Loading();
			$.ajax({
			type: "POST",
			url: root_domain+'app/production_process_start/',
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

function show_material_list_23_6_21()
{
	
	var eid=$('#eid').val();//allocate ID
	var max_start_qty=$('#machine_no1').val();//allocate ID
	var pending_qty=$('#pr_p_qty1').val();//allocate ID
	var pre_alloc_id=$('#pre_alloc_id').val();
	//alert(pre_alloc_id);
	var branch_id=$('#branch_id').val();
	var pid=$('#eid').val();
	//var max_available_qty=$('#max_available_qty').val();//allocate ID
	//if(max_start_qty<=max_available_qty){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/allocate_process_end/',
		data: { mode : 'show_material_list_new',eid:eid,max_start_qty:max_start_qty,pending_qty:pending_qty,branch_id:branch_id,pre_alloc_id:pre_alloc_id,pid:pid},
		success: function(data){
				$('#sub_row_mat').html(data);
				$("#sp_btn").show();
				Unloading();
			}		
			
		});
	/* }else{
		toastr.warning("Not Enter More then Available Qty", "ERROR");
		$("#sp_btn").hide();
	} */
}
function open_scrap_entry(){
	
	var product_id=$("#product_id_hid").val();
	var process_id=$("#process_id_hid").val();
	var allo_id=$("#eid").val();
	var branch_id=$("#branch_id").val();
	var qty=$("#machine_no1").val();
	//alert(product_id);
	//alert(process_id);
	if(qty>0){
		$.ajax({
			type: "POST",
			url: root_domain+'app/allocate_process_end/',
			data: { mode : 'open_scrap_entry',product_id:product_id,process_id:process_id,allo_id:allo_id,branch_id:branch_id,qty:qty},
			success: function(data){
					$('#preview_scrap_entry_modal').modal('show');
					$('#mod_per_div_sec1').html(data);
					Unloading();
				}		
			});
	}else{
		toastr.info("Enter Prosuct Qty", "INFO");
	}
}
function scrap_save1(){
	var sallo_id=$("#sallo_id").val();
	var product_scrap_id=$("#product_scrap_id").val();
	var sproduct=$("#sproduct").val();
	var sprocess=$("#sprocess").val();
	var scrap_received_qty=$("#scrap_received_qty").val();
	var sbranch_id=$("#sbranch_id").val();
	
	if(parseFloat($("#scrap_received_qty").val()) > parseFloat($("#scrap_received_qty").attr('max'))) {		
		toastr.warning("Not Enter Max Scrap Entry", "ERROR");
		$("#scrap_received_qty").focus();
		return false;
	}
	
	if(parseFloat($("#scrap_received_qty").val()) < parseFloat($("#scrap_received_qty").attr('min'))){		
		toastr.warning("Not Enter Min Scrap Entry", "ERROR");
		$("#scrap_received_qty").focus();
		return false;
	}
	
	if(product_scrap_id!=""){
		$.ajax({
			type: "POST",
			url: root_domain+'app/allocate_process_end/',
			data: { mode : 'scrap_save',sproduct:sproduct,sprocess:sprocess,sallo_id:sallo_id,product_scrap_id:product_scrap_id,scrap_received_qty:scrap_received_qty,sbranch_id:sbranch_id},
			success: function(data){
					//$('#mod_per_div_sec1').html(data);
					Unloading();
				}		
			});
		$('#preview_scrap_entry_modal').modal('hide');
	}
} 
function scrap_rate_change(){
	//alert("hi");
	var product_scrap_id=$("#product_scrap_id").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/allocate_process_end/',
		data: { mode : "scrap_rate_change",product_scrap_id:product_scrap_id },
		success: function(data){
			//console.log(data);
			var no = jQuery.parseJSON(data);
			$('#scrap_rate').val(no.product_sale_rate);
		}
	});
}
