var datatable;
$(document).ready(function() {
	//show_data();
// validate the comment form when it is submitted        

});

function load_salesno(){
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/request_product/',
		data: { mode : "load_invoiceno" },
		success: function(data){
			//alert(data);
			//console.log(data);
			var no = jQuery.parseJSON(data);
			//alert(no.invoiceno);
			$('#po_req_no').val(no.invoiceno);
		}
	});
}


function add_product_request(cnt)
{
	
	if($("#req_qty"+cnt).val()=="")
	{
		toastr.warning("insert Required Qty", "ERROR")
		$("#req_qty"+cnt).focus();
		return false;
	}
	else if($("#in_process_qty"+cnt).val()==="" && $('#in_process_qty_check'+cnt).val()!='1')
	{		
		toastr.warning("insert Inward Process Qty", "ERROR")
		$("#in_process_qty"+cnt).focus();
		return false;
	}
	else if($("#out_process_qty"+cnt).val()==="" && $('#in_process_qty_check'+cnt).val()!='1')
	{		
		toastr.warning("insert Outward Qty", "ERROR")
		$("#out_process_qty"+cnt).focus();
		return false;
	}
	else if($("#po_qty"+cnt).val()==="")
	{		
		toastr.warning("insert PO Qty", "ERROR")
		$("#po_qty"+cnt).focus();
		return false;
	}
	
	
	//alert(cnt);
	var req_qty=$('#req_qty'+cnt).val();
	var in_process_qty=$('#in_process_qty'+cnt).val();
	var out_process_qty=$('#out_process_qty'+cnt).val();
	var po_qty=$('#po_qty'+cnt).val();
	var pr_id=$('#pr_id'+cnt).val();
	var po_req_no=$('#po_req_no').val();
	var parent_product=$('#eid').val();
	
	//alert(in_process_qty);
	
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/request_product/',
	data: { mode : "add_product_request",req_qty:req_qty,in_process_qty:in_process_qty,out_process_qty:out_process_qty,po_qty:po_qty,pr_id:pr_id,po_req_no:po_req_no,parent_product:parent_product },
	success: function(data){
			//alert(data);
			//console.log(data);
			 //$('#sale_productdata').html(data);
			//show_data();
			Unloading();
		}		
		
	});
	
}

$("#product_request_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#product_request_add").valid()) {
		
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
		
	$.ajax({
		cache:false,
		url: root_domain+'app/request_product/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(msg)
		{
			//alert(msg);
			//console.log(response);
			//var resp = JSON.parse(response);
			//var msg= resp.msg;
			//alert(msg.res);
			if(msg.trim() == '1') {
				toastr.success("INSERTED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain+'get_stock_detail';
				//load_complaint_datatable();
				//$("#modal-complain-add").modal("hide");
			}
			else if(msg.trim() == '2') {
				toastr.success("COMPLAINT DONE SUCCESSFULLY", "SUCCESS");
				//$("#modal-complain-add").modal("hide");
				Unloading();
			}
			else if(msg.trim() == '0') {
				toastr.error("PLEASE ENTER AT LEAST ONE OLD SPARE PART", "ERROR")
				Unloading();
			}
			else if(msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#product_request_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});


function get_bom_request_qty(x)
{
	
	//alert(x);
	var counter_tree=Number($('#counter_tree').val());
	
	var in_process_qty=$('#in_process_qty_main').val();
	
	for(var i=0;i<counter_tree;i++)
	{
		var total_qty=Number($('#total_qty'+i).val());
		
		var req_qty=total_qty*x;
		
		$('#req_qty'+i).val(req_qty);
		//alert(req_qty);
	}
	
	//alert(counter_tree);
}



function get_inhouse_request_qty(x)
{
	
	//alert(x);
	var counter_tree=Number($('#counter_tree').val());
	
	for(var i=0;i<counter_tree;i++)
	{
		var total_qty=Number($('#total_qty'+i).val());
		
		var req_qty=total_qty*x;
		
		var check_qty=Number($('#in_process_qty_check'+i).val());
		
		if(check_qty!='1')
		{
			$('#in_process_qty'+i).val(req_qty);
		}
	}
	
	//alert(counter_tree);
}

function get_outward_request_qty(x)
{
	
	//alert(x);
	var counter_tree=Number($('#counter_tree').val());
	
	for(var i=0;i<counter_tree;i++)
	{
		var total_qty=Number($('#total_qty'+i).val());
		
		var req_qty=total_qty*x;
		
		var check_qty=Number($('#in_process_qty_check'+i).val());
		
		if(check_qty!='1')
		{
		
			$('#out_process_qty'+i).val(req_qty);
			//alert(req_qty);
		}
	}
	
	//alert(counter_tree);
}

function get_po_request_qty(x)
{
	
	//alert(x);
	var counter_tree=Number($('#counter_tree').val());
	
	for(var i=0;i<counter_tree;i++)
	{
		var total_qty=Number($('#total_qty'+i).val());
		
		var req_qty=total_qty*x;
		
		var check_qty=Number($('#in_process_qty_check'+i).val());
		
		if(check_qty=='1')
		{
		
		$('#po_qty'+i).val(req_qty);
		
		}
		//alert(req_qty);
	}
	
	//alert(counter_tree);
}

function get_tree_request()
{
	var mode=$('#mode').val();
	var eid=$('#eid').val();
	var pr_type=$('#pr_type').val();
	var bom_id=$('#bom_id').val();
	
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/request_product/',
	data: { mode : 'get_tree_request',eid:eid,pr_type:pr_type,bom_id:bom_id },
	success: function(data){
			//alert(data);
			//console.log(data);
			 //$('#sale_productdata').html(data);
			//show_data();
			Unloading();
		}		
		
	});
}