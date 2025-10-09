//var datatable;
$(document).ready(function() {
	/*$('#product_amount').hover(function(){
       var pro_amt = $('#product_amount').val();
		$('#product_amount').attr("title",pro_amt);
    });*/
	load_po_datatable();
	if($('#eid').val()!=undefined && $('#eid').val()!=''){
		get_common_details('po_listing_info', '', $('#eid').val());

	}else if($('#purchase_type').val()!=undefined && $('#purchase_type').val()!=''){
		var purchase_type = $('#purchase_type').val();
		var v_or_iid='';
		if(purchase_type=='0'){
			v_or_iid = $('#vender_id').val();
		}else if(purchase_type=='1'){
			v_or_iid = $('#product_id').val();
		}
		get_common_details('po_listing_info',v_or_iid, eid=null);
	}
	/**/
	
// validate vendor add form on keyup and submit
 $("#purchasecard_add").validate({
	rules: {
		vender_id: {
			required: true			
		},
		purchasecard_no: {
			required: true			
		},
		purchaseorder_date:{
			required : true	
		}
	},
	messages: {
		vender_id: {
			required: "Select Vendor"
		},
		purchasecard_no: {
			required: "Enter P.C no"
		},
		purchasecard_date:{
			required : "Enter P.C date"
		}
	}
}); 
});
$("#purchasecard_add").on('submit',function(e) {
	if($("#currency_rate").val()=="")
	{
		toastr.warning("Enter valid amount", "ERROR")
		$("#currency_rate").focus();
		return false;
	}else if($("#p_rate").val()=="")
	{
		toastr.warning("Enter tolerance", "ERROR")
		$("#p_rate").focus();
		return false;
	}
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#purchasecard_add").valid()) {
		return false;
	}
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+purchase_domain+'app/purchase_card/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PURCHASE CARD ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+purchase_domain+arr.back;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			else if(arr.msg== 'update')
			{	
				toastr.success("PC UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+purchase_domain+'purchase_card_list';
				
			}
			$('#purchasecard_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_po(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+purchase_domain+'app/purchase_card/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("PC DELETE SUCCESSFULLY", "SUCCESS");
						load_po_datatable();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
}


function reload_data()
{
	//datatable.fnReloadAjax();
	load_po_datatable();
}	
function load_po_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	
	datatable = $("#dynamic-table").dataTable({
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
			"sAjaxSource": root_domain+purchase_domain+'app/purchase_card/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "po_type_status", "value": po_type_status },{ "name": "date", "value": date });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}


function delete_data(id,table,whereid)
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+purchase_domain+'app/purchase_card/',
				data: { mode : "delete_data",  eid : id ,table:table,whereid:whereid,purchaseorder_id:$("#eid").val() },
				success: function(response)
				{
					console.log(response)
					var data=jQuery.parseJSON(response)
					var response=data.res;
					if(response.trim() == "1") {
						toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					
						
					
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}	
}

function get_series_no(type_id){
	
	$.ajax({
	type: "POST",
	url: root_domain+purchase_domain+'app/purchase_card/',
	data: { mode : "get_series_no", type_id:type_id},
	success: function(resp){
				//console.log(resp);
				$('#invoicetype_id').val(resp);	
				load_pono(resp)	
			}		
	});	
}
function load_pono(id)
{
	
	$.ajax({
	type: "POST",
	url: root_domain+purchase_domain+'app/purchase_card/',
	data: { mode : "load_invoiceno", typeid : id},
	success: function(data){
				//console.log(data);
				var no = jQuery.parseJSON(data);
				$('#purchasecard_no').val(no.invoiceno);
				
	}
	});
}

function load_product(type_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_card/',
		data: { mode : "load_product", type_id : type_id},
		success: function(data){
			//console.log(data);
			$('#product_id').html(data);				
			Unloading();
		}
	});
}
function entry_po_req_data(purchaseorder_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_card/',
		data: { mode : "entry_po_req_data", purchaseorder_id : purchaseorder_id},
		success: function(data){
			//console.log(data);
						
			Unloading();
		}
	});
}

function cancel_po_status(id, po_status) 
{
	var r= confirm(" Are you want to Change PC Status ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+purchase_domain+'app/purchase_card/',
				data: { mode : "cancel_po_status", eid:id, po_status:po_status },
				success: function(response)
				{
					console.log(response);
					var resp = JSON.parse(response);
					var response = resp.res;
					if(response.trim() == "1") {
						toastr.success("PC STATUS CHANGED SUCCESSFULLY", "SUCCESS");
						load_po_datatable();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
}

function change_po_approval_status(id, pc_approval_status) 
{
	var r= confirm(" Are you want to Change PC Approval Status ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+purchase_domain+'app/purchase_card/',
				data: { mode : "change_po_approval_status", eid:id, pc_approval_status:pc_approval_status },
				success: function(response)
				{
					console.log(response);
					var resp = JSON.parse(response);
					var response = resp.res;
					if(response.trim() == "1") {
						toastr.success("PC APPROVAL STATUS CHANGED SUCCESSFULLY", "SUCCESS");
						load_po_datatable();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}	
}

function load_product_tax(pid,tran_type)
{
	//alert(pid);
	Loading();
	
	var vendor=$('#vender_id').val();
	
	if(vendor!=''){
		
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase_card/',
			data: { mode : "load_product_tax", pid : pid,tran_type:tran_type,vendor:vendor },
			success: function(response)
			{
				//alert(response);
				
				console.log(response);
				var resp = JSON.parse(response);
				
				$('#sel_tax').val(resp.name);
				$('#formulaid').val(resp.id);
				$('#formula_tax_id').val(resp.tax_id);
				
				Unloading();
			}
		});
		
	}
	Unloading();
}

function get_vendor_details(tab){
	var vendor_id = $('#vender_id').val();
	var mode = "get_"+tab;
	var eid = $('#eid').val();
	if(vendor_id){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_card/',
		data: { mode : mode, vendor_id : vendor_id, eid : eid},
		success: function(data){
					$('#'+tab).html(data);				
					Unloading();
			}		
		});
	}else{
		$msg = "Please Select Vendor First.";
		toastr.warning($msg, "WARNING");
		$('#'+tab).html($msg);
	}
	
 }

 function get_items_details(tab,product_id=null) {
 	var product_id = $('#product_id').val();
	var mode = "get_"+tab;
	if(product_id){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_card/',
		data: { mode : mode, product_id : product_id},
		success: function(data){
				$('#'+tab).html(data);				
				Unloading();
			}		
		});
	}else{
		$msg = "Please Select Product First.";
		toastr.warning($msg, "WARNING");
		$('#'+tab).html($msg);
	}
 }

 function get_common_details(tab,v_or_iid=null, eid=null) {
 	var purchase_type = $('#purchase_type').val();
	var v_or_iid='';
	if(purchase_type=='0'){
		v_or_iid = $('#vender_id').val();
	}else if(purchase_type=='1'){
		v_or_iid = $('#product_id').val();
	}

 	var mode = "get_"+tab;
 	
 	if(product_id!='' || vendor_id!=''){
 		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_card/',
		data: { mode : mode, v_or_iid : v_or_iid, eid : eid, type : purchase_type},
		success: function(data){
				$('#'+tab).html(data);	
				$("#party_product").select2({
         	width: '100%',
         	minimumInputLength: 2,
         });		
				Unloading();
			}		
		});
 	}else{
 		$msg = "Please select vendor or product first.";
		toastr.warning($msg, "WARNING");
		$('#'+tab).html($msg);
 	}
 }

 function get_vendor_name(vid) {
 	Loading();
 	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_card/',
		data: { mode : 'set_vendor_sesion', vendor_id : vid},
		success: function(data){
				window.location.href=root_domain+purchase_domain+'purchase_card';
				Unloading();
			}		
	});
 }

  function get_item_name(iid,ptype, pname) {
  	Loading();
 	$.ajax({
		type: "POST",
		url: root_domain+purchase_domain+'app/purchase_card/',
		data: { mode : 'set_item_sesion', item_id : iid, product_type : ptype, product_name : pname},
		success: function(data){
				window.location.href=root_domain+purchase_domain+'purchase_card';
				Unloading();
			}		
	});
 }

function add_price(){
	var purchase_type = $('#purchase_type').val();
	var v_or_iid='';
	if(purchase_type=='0'){
		v_or_iid = $('#vender_id').val();
	}else if(purchase_type=='1'){
		v_or_iid = $('#product_id').val();
	}

	var new_product = $('#party_product').val();
	var price = $('#party_rate').val();

	if(new_product!=''){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+purchase_domain+'app/purchase_card/',
			data: { mode : 'set_new_item', purchase_type : purchase_type, v_or_iid : v_or_iid, new_product : new_product, price : price},
			success: function(data){
					Unloading();
					var response = jQuery.parseJSON( data );
					if(response.res=='1'){
						$msg = response.msg;
						toastr.success($msg, "SUCCESS");
						get_common_details('po_listing_info');
					}else{
						$msg = response.msg;
						toastr.warning($msg, "WARNING");
						return false;
					}
				}		
		});
	}else{
		$msg = "Please select item/vendor.";
		toastr.warning($msg, "WARNING");
		return false;
	}	
}

 $(document).on('keydown', "input[type='number']", function(event){
            if (event.shiftKey == true) {
                event.preventDefault();
            }

            if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

            } else {
                event.preventDefault();
            }
            
            if($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
                event.preventDefault();
});
       