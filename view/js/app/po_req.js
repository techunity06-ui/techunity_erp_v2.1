//var datatable;
$(document).ready(function() {
	$("#all_chk_box").click(function() {
		//alert("cdu");
		if($("#all_chk_box").prop("checked")==true){
			$(".chk_box").prop('checked', true);
		}
		else{
			$(".chk_box").prop('checked', false);
		}
	});
load_po_req_datatable();
});
function reload_data()
{
	load_po_req_datatable();
}	
function load_po_req_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id = $('#branch_id').val();
	//alert("cd");
	$("#po-req-table").dataTable({
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
			"sAjaxSource": root_domain+'app/purchase_order_req/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{ "name": "po_type_status", "value": po_type_status },
					{ "name": "date", "value": date },
					{ "name": "branch_id", "value": branch_id }
				);
			},
			"fnDrawCallback": function( oSettings ) {
				//alert(oSettings);
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
function req_po_to_main_po(){

	var	purchaseordertrn_id = $("input[name='purchaseordertrn_id[]']:Checked").map(function(){return $(this).val();}).get();
	//console.log(purchaseordertrn_id);
	var purchaseorder_id = $('#eid').val();
	
	if(purchaseordertrn_id.length > 0){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order_req/',
			data: { mode : "req_po_to_main_po", purchaseordertrn_id : purchaseordertrn_id, purchaseorder_id:purchaseorder_id },
			success: function(data){
				//console.log(data);
				//alert('OK');
				window.location=root_domain+'po_req/'+purchaseorder_id;
				Unloading();
			}
		});
	}
	else{
		toastr.warning("Select at least one product to create PO !!!", "WARNING");
		return false;
	}
	
}
function cancel_po_status(id, po_status) 
{
	var r= confirm(" Are you want to Change PO Status ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/purchase_order_req/',
				data: { mode : "cancel_po_status", eid:id, po_status:po_status },
				success: function(response)
				{
					//console.log(response);
					var resp = JSON.parse(response);
					var response = resp.res;
					if(response.trim() == "1"){
						toastr.success("PO STATUS CHANGED SUCCESSFULLY", "SUCCESS");
						load_po_req_datatable();
						Unloading();
					}
					else if(response.trim() == "0"){
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function close_po_status(id, po_req_status) 
{
	var r= confirm(" Are you want to Change PO Status ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/purchase_order_req/',
				data: { mode : "close_po_status", eid:id, po_req_status:po_req_status },
				success: function(response)
				{
					//console.log(response);
					var resp = JSON.parse(response);
					var response = resp.res;
					if(response.trim() == "1"){
						toastr.success("PO STATUS CHANGED SUCCESSFULLY", "SUCCESS");
						load_po_req_datatable();
					}
					else if(response.trim() == "0"){
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
			Unloading();
		}
	
}

$("#purchaseorder_req_add").on('submit',function(e) {
	var	purchaseordertrn_id = $("input[name='che_box[]']:Checked").map(function(){return $(this).val();}).get();
	if(purchaseordertrn_id==""){
		//alert("demo");
		toastr.warning("Please Select Product", "ERROR")
		return false;
	}else{
		//alert("123");
		//return false;
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#purchaseorder_req_add").valid()) {
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
		url: root_domain+'app/purchase_order_req/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("PO REQ. ADDED SUCCESSFULLY", "SUCCESS");
				//window.location=root_domain+'po_req_list';
				window.location=root_domain+'po_req/'+vender_id+'/'+branch_id;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			else if(arr.msg == 'update'){	
				toastr.success("PO REQ. UPDATED SUCCESSFULLY", "SUCCESS");		
				window.location=root_domain+'po_req_list';
			}
			$('#purchaseorder_req_add').trigger('reset');	

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
	//alert(unit);
	//alert(product);
	//alert(cnt);
	var product_qty=Number($('#product_qty'+cnt).val());
	
	Loading();
	if(unit!='')
	{
		$.ajax({
			type: "POST",
			url: root_domain+'app/purchase_order_req/',
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
	/*alert(product_qty);
	alert(alt_qty);
	alert(base_qty);
	alert(cnt);*/
	var last_qty=(product_qty*alt_qty)/base_qty;
	
	$('#product_alloc_qty'+cnt).val(last_qty.toFixed(3));
	//alert(last_qty);
	
}
function get_product(vender_id)
{
	//alert(vender_id);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/purchase_order_req/',
		data: { mode : "get_product", vender_id:vender_id},
		success: function(response)
		{
			$("#sale_productdata").html(response);
			check_submit_btn();
		}
	});
	Unloading();
}
function check_box(box_id){
	if($("#che_box"+box_id).prop("checked") == true){
		$("#check_status"+box_id).val("2");
		//$("#submi"+box_id).val("0");
	}else{
		$("#check_status"+box_id).val("1");
		//$("#submi"+box_id).val("1");
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
		//total_ltr+=parseFloat(product_qtyltr[k].value);
	}
	if(total_ltr>0){
		$("#save").show();
	}else{
		$("#save").hide();
	}
	//alert(total_ltr);
}