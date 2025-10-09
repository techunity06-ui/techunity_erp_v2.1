var datatable;
$(document).ready(function() {
	//show_data();
// validate the comment form when it is submitted
load_salesno();   

$("#product_request_add").validate({
	
	ignore:[],
	
	rules: {
		
		po_req_no:{
			required:true
		},
		
	},
	messages: {
		po_req_no:{
			required:"enter po request no "
		},
		
	}
}); 

});

function load_salesno(){
	
	var mode=$('#mode').val();
	
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
			check_main_process_request();
		}
	});
}

function set_main_process_request_qty()
{
	//alert('hello');
	var po_req_no=$('#po_req_no').val();
	var po_req_date=$('#po_req_date').val();
	var rp_req_qty=$('#rp_req_qty').val();
	var in_process_qty_main=$('#in_process_qty_main').val();
	var rp_po_qty=$('#rp_po_qty').val();
	var eid=$('#eid').val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/request_product/',
		data: { mode : "add_main_process_request_qty",po_req_no:po_req_no,po_req_date:po_req_date,rp_req_qty:rp_req_qty,in_process_qty_main:in_process_qty_main,rp_po_qty:rp_po_qty,eid:eid },
		success: function(data){
			
			if(data==1)
			{
				get_tree_request();
			}
		}
	});
	
}

function check_main_process_request()
{
	var po_req_no=$('#po_req_no').val();
	var eid=$('#eid').val();
	//alert(po_req_no);
	$.ajax({
		type: "POST",
		url: root_domain+'app/request_product/',
		data: { mode : "check_main_process_request",po_req_no:po_req_no,eid:eid },
		success: function(response)
		{
			//alert(response);
			var data=JSON.parse(response);
			
			if(data.count>0)
			{
				$('#rp_req_qty').val(data.req_qty);
				$('#in_process_qty_main').val(data.process_qty);
				$('#rp_po_qty').val(data.po_qty);
				
				if(data.sp_status==1)
				{
					$('#rp_req_qty').attr('readonly',true);
					$('#in_process_qty_main').attr('readonly',true);
					$('#rp_po_qty').attr('readonly',true);
					$('#set_process_btn').hide();
				}
				
				get_tree_request();
			}
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
	data: { mode : "add_product_request",req_qty:req_qty,in_process_qty:in_process_qty,out_process_qty:out_process_qty,po_qty:po_qty,pr_id:pr_id,po_req_no:po_req_no,parent_product:parent_product,cnt:cnt },
	success: function(data){
			//alert(data);
			//console.log(data);
			 //$('#sale_productdata').html(data);
			//show_data();
			if(data=='0')
			{
				get_tree_request();
				get_all_requested_qty();
				lock_main_request();
				//Unloading();
			}
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


function get_inhouse_request_qty_inner(x)
{
	
	//alert(x);
	var counter_tree=Number($('#counter_tree').val());
	var counter_tree_previous=x-1;
	
	
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


function get_inhouse_request_qty_inner(cnt,pid,level)
{
	
	//alert(cnt);
	//alert(pid);
	//alert(level);
	
	
	
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
	var po_req_no=$('#po_req_no').val();
	
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/request_product/',
	data: { mode : 'get_tree_request',eid:eid,pr_type:pr_type,bom_id:bom_id,po_req_no:po_req_no },
	success: function(data){
			
			$('#show_tree_request').html(data);
			
			/* var json_response=JSON.parse(data);
			$('#show_tree_request').html(json_response.str_tree);
			$('#counter_tree').val(json_response.counter_tree); */
			get_all_requested_qty();
			
			get_inhouse_request_qty($('#in_process_qty_main').val());
			get_bom_request_qty($('#in_process_qty_main').val());
			get_po_request_qty($('#in_process_qty_main').val()); 
			Unloading();
		}		
		
	});
}

function get_all_requested_qty()
{

	var cnt=Number($('#counter_tree').val());
	var po_req_no=$('#po_req_no').val();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/request_product/',
		data: { mode : 'get_all_requested_qty',po_req_no:po_req_no },
		success: function(response){
				
				//console.log(response);
				var data=JSON.parse(response);
				console.log(data);
				var array=data.data;
				
				if(data.count>0)
				{
					for(var i=0;i< array.length;i++)
					{
						//console.log(array[i]['rp_pid']);
						var cnt_var=array[i]['row_cnt'];
						$('#req_qty'+cnt_var).val(array[i]['rp_req_qty']);
						$('#in_process_qty'+cnt_var).val(array[i]['in_process_qty']);
						$('#po_qty'+cnt_var).val(array[i]['rp_po_qty']);
						
						$('#req_qty'+cnt_var).attr('readonly',true);
						$('#in_process_qty'+cnt_var).attr('readonly',true);
						$('#po_qty'+cnt_var).attr('readonly',true);
					}
					
				}
				//alert(data.count);
				/*if(data.count>0)
				{
					//alert(data.count);
					$('#req_qty'+data.count_var).val(data.rp_req_qty);
					$('#in_process_qty'+data.count_var).val(data.in_process_qty);
					$('#po_qty'+data.count_var).val(data.rp_po_qty);
					
					$('#req_qty'+data.count_var).attr('readonly',true);
					$('#in_process_qty'+data.count_var).attr('readonly',true);
					$('#po_qty'+data.count_var).attr('readonly',true);
				}
				Unloading();*/
			}		
			
		});
	
	/*
	for(var i=0;i<cnt;i++)
	{
		//alert(cnt);
		var pr_id=$('#pr_id'+i).val();
		var po_req_no=$('#po_req_no').val();
		
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/request_product/',
		data: { mode : 'get_all_requested_qty',pr_id:pr_id,count_var:i,po_req_no:po_req_no },
		success: function(response){
				
				console.log(response);
				var data=JSON.parse(response);
				//alert(data.count);
				if(data.count>0)
				{
					//alert(data.count);
					$('#req_qty'+data.count_var).val(data.rp_req_qty);
					$('#in_process_qty'+data.count_var).val(data.in_process_qty);
					$('#po_qty'+data.count_var).val(data.rp_po_qty);
					
					$('#req_qty'+data.count_var).attr('readonly',true);
					$('#in_process_qty'+data.count_var).attr('readonly',true);
					$('#po_qty'+data.count_var).attr('readonly',true);
				}
				Unloading();
			}		
			
		});
	
	}
	*/
}

function lock_main_request()
{
	var po_req_no=$('#po_req_no').val();
	var eid=$('#eid').val();
	
	$.ajax({
	type: "POST",
	url: root_domain+'app/request_product/',
	data: { mode : 'lock_main_request',eid:eid,po_req_no:po_req_no },
	success: function(response){
			
			
			if(response==1)
			{
				
				$('#rp_req_qty').attr('readonly',true);
				$('#in_process_qty_main').attr('readonly',true);
				$('#rp_po_qty').attr('readonly',true);
				$('#set_process_btn').hide();
			
			}
			Unloading();
		}		
		
	});
}


