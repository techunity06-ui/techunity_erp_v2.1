//var datatable;
$(document).ready(function() {
	$("#all_chk_box").click(function() {
		if($("#all_chk_box").prop("checked")==true){
			
			$(".chk_box").prop('checked', true);
		}
		else{
			$(".chk_box").prop('checked', false);
		}
	});
	load_returnable_receipt_request_datatable();
});
function reload_data()
{
	load_returnable_receipt_request_datatable();
}	
function load_returnable_receipt_request_datatable()
{
	var approve_status=$('input[name=approve_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id = $('#branch_id').val();
	$("#returnable-receipt-request-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+inventory_domain+'app/returnable_receipt_request/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{ "name": "po_type_status", "value": po_type_status },
					{ "name": "date", "value": date },
					{ "name": "branch_id", "value": branch_id }
				);
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();
		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
function req_returnable_to_main_grn(){

	var	returnreceiptitem_id = $("input[name='returnreceiptitem_id[]']:Checked").map(function(){return $(this).val();}).get();
	var return_id = $('#eid').val();
	
	if(returnreceiptitem_id.length > 0){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/returnable_receipt_request/',
			data: { mode : "req_returnable_to_main_grn", returnreceiptitem_id : returnreceiptitem_id, return_id:return_id },
			success: function(data){
				window.location=root_domain+'grn_add';
				Unloading();
			}
		});
	}
	else{
		toastr.warning("Select at least one product to create GRN !!!", "WARNING");
		return false;
	}
	
}
function cancel_return_receipt_status(id, po_status) 
{
	var r= confirm(" Are you want to Change Returning Receipt Status ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+inventory_domain+'app/returnable_receipt_request/',
				data: { mode : "cancel_return_receipt_status", eid:id, status:status },
				success: function(response)
				{
					var resp = JSON.parse(response);
					var response = resp.res;
					if(response.trim() == "1"){
						toastr.success("RETURNING RECEIPT STATUS CHANGED SUCCESSFULLY", "SUCCESS");
						load_returnable_receipt_request_datatable();
						Unloading();
					}
					else if(response.trim() == "0"){
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function close_return_receipt_status(id, po_req_status) 
{
	var r= confirm(" Are you want to Change Returning Receipt Status ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+inventory_domain+'app/returnable_receipt_request/',
				data: { mode : "close_return_receipt_status", eid:id, status:status },
				success: function(response)
				{
					var resp = JSON.parse(response);
					var response = resp.res;
					if(response.trim() == "1"){
						toastr.success("RETURNING RECEIPT STATUS CHANGED SUCCESSFULLY", "SUCCESS");
						load_returnable_receipt_request_datatable();
					}
					else if(response.trim() == "0"){
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
			Unloading();
		}
	
}

$("#returnable_receipt_request_add").on('submit',function(e) {
	var	returnable_receipt_id = $("input[name='che_box[]']:Checked").map(function(){return $(this).val();}).get();
	if(returnable_receipt_id==""){
		toastr.warning("Please Select Product", "ERROR")
		return false;
	}else{
		var form = this;
		e.preventDefault();
		e.stopPropagation();	
		if (!$("#returnable_receipt_request_add").valid()) {
			return false;
		}
		form.submitted = true;	
		Loading(true);	
		$(this).attr("disabled","disabled");		
		
		var form_data=new FormData(this);	
		
		var purchaseorder_id=$('#eid').val();
		var vender_id=$('#vender_id').val();
		var branch_id=$('#branch_id').val();

		//alert(purchaseorder_id);
		$.ajax({
			cache:false,
			url: root_domain+inventory_domain+'app/returnable_receipt_request/',
			type: "POST",
			data: form_data,
			contentType: false,
			processData:false,
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);			
				if(arr.msg == '1') {
					toastr.success("RETURNING RECEIPT ADDED SUCCESSFULLY", "SUCCESS");
					window.location=root_domain+inventory_domain+'grn_add_returnable/'+vender_id;
				}
				else if(arr.msg == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
				}
				else if(arr.msg == 'update'){	
					toastr.success("RETURNING RECEIPT UPDATED SUCCESSFULLY", "SUCCESS");		
					window.location=root_domain+inventory_domain+'grn_list';
				}
				$('#returnable_receipt_request_add').trigger('reset');	

				Unloading();
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});
	}
	//return false;
	
});


function get_alt_qty(unit,product,cnt)
{
	var product_qty=Number($('#product_qty'+cnt).val());
	
	Loading();
	if(unit!='')
	{
		$.ajax({
			type: "POST",
			url: root_domain+inventory_domain+'app/returnable_receipt_request/',
			data: { mode : "get_alt_qty", unit:unit, product:product },
			success: function(response)
			{
				//alert(response);
				var data = JSON.parse(response);
				//alert(data.count);
				if(data.count>0)
				{
					$('#unit_alt_qty'+cnt).val(data.alt_qty);
					$('#unit_base_qty'+cnt).val(data.base_qty);
					get_conv_qty(data.alt_qty,data.base_qty,cnt);
				}
				else
				{
					$('#product_alloc_qty'+cnt).val(0);
				}
				//console.log(response);
				//var resp = JSON.parse(response);
			}
		});
	}
	else
	{
		$('#product_alloc_qty'+cnt).val(product_qty);
	}
	Unloading();
}

function get_conv_qty(alt_qty,base_qty,cnt)
{
	var product_qty=Number($('#product_qty'+cnt).val());
	var last_qty=(product_qty*alt_qty)/base_qty;
	
	$('#product_alloc_qty'+cnt).val(last_qty.toFixed(3));
}
function get_channal(channal_id){
	var vender_id = $("#vender_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_receipt_request/',
		data: { mode : "get_product", channal_id: channal_id, vender_id: vender_id},
		success: function(response)
		{
			$("#returnable_receipt_data").html(response);
			check_submit_btn();
		}
	});
	Unloading();
}
function get_product(vender_id)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_receipt_request/',
		data: { mode : "get_product", vender_id:vender_id},
		success: function(response)
		{
			$("#returnable_receipt_data").html(response);
			check_submit_btn();
			get_channal_data(vender_id);
			$("#channal_id").select2("val","");
		}
	});
	Unloading();
}
function get_channal_data(vender_id){
    $.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/returnable_receipt_request/',
		data: { mode : "load_channal",  vender_id : vender_id},
		success: function(responce){
			$('#channal_id').html(responce);
			$("#channal_id").select2("val",val1);
		}
	});
	
}
function check_box(box_id){
	if($("#che_box"+box_id).prop("checked") == true){
		$("#check_status"+box_id).val("2");
	}else{
		$("#check_status"+box_id).val("1");
	}
	check_submit_btn();
}
function check_all(){
	if($("#all_chk_box").prop("checked")==true){
		$(".chk_box").prop('checked', true);
		$(".chk_box_st").val(2);
	}
	else{
		$(".chk_box").prop('checked', false);
		$(".chk_box_st").val(1);
	}
	check_submit_btn();
}
function check_submit_btn(){
	var product_qtyltr=document.getElementsByName('check_status[]');
	var cnt=product_qtyltr.length;
	var total_ltr=0
	
	for(var k=0;k<cnt;k++)
	{
		if(product_qtyltr[k].value==="2"){
			total_ltr+=parseFloat(1);	
		}
	}
	if(total_ltr>0){
		$("#save").show();
	}else{
		$("#save").hide();
	}
}