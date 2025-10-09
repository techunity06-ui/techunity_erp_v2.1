//var datatable;
$(document).ready(function() {
	load_po_datatable();
	
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

	$("#bom_costing").validate({
		rules: {
			bom_costing_id: {
				required: true			
			},
		},
		messages: {
			bom_costing_id: {
				required: "Select BOM Costing"
			}
		}
	}); 
});
$("#purchasecard_add").on('submit',function(e) {
	if($("#price").val()=="")
	{
		toastr.warning("Enter valid amount", "ERROR")
		$("#price").focus();
		return false;
	}else if($("#rate_tolerance").val()=="")
	{
		toastr.warning("Enter tolerance", "ERROR")
		$("#rate_tolerance").focus();
		return false;
	}
	else if($("#grate").val()=="")
	{
		toastr.warning("Enter GRate", "ERROR")
		$("#grate").focus();
		return false;
	}
	else if($("#discount_percentage").val()=="")
	{
		toastr.warning("Enter discount rate", "ERROR")
		$("#discount_percentage").focus();
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
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/work_order/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PURCHASE CARD ADDED SUCCESSFULLY", "SUCCESS");
				window.location.reload();
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
				window.location.reload();
				
			}
			$('#purchasecard_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});



function reload_data()
{
	//datatable.fnReloadAjax();
	load_po_datatable();
}	
function load_po_datatable()
{
	var po_type_status=$('input[name=po_type_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id=$('#branch_id').val();
	var workorder_status=$('input[name=workorder_status]:Checked').val();
	console.log(workorder_status);
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
			"sAjaxSource": root_domain+production_domain+'app/work_order/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "po_type_status", "value": po_type_status },{ "name": "date", "value": date },{ "name": "workorder_status", "value": workorder_status },{ "name": "branch_id", "value": branch_id });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}


function get_series_no(type_id){
	$.ajax({
	type: "POST",
	url: root_domain+production_domain+'app/work_order/',
	data: { mode : "get_series_no", type_id:type_id},
	success: function(resp){
			$('#invoicetype_id').val(resp);	
			load_pono(resp);	
		}		
	});	
}

function load_pono(id)
{
	$.ajax({
	type: "POST",
	url: root_domain+production_domain+'app/work_order/',
	data: { mode : "load_invoiceno", typeid : id},
	success: function(data){
		var no = jQuery.parseJSON(data);
		$('#purchasecard_no').val(no.invoiceno);
	}
	});
}

function load_product(type_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/work_order/',
		data: { mode : "load_product", type_id : type_id},
		success: function(data){
			$('#product_id').html(data);				
			Unloading();
		}
	});
}

function get_vendor_details(id){

	var mode = "get_po_login";
	
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+production_domain+'app/work_order/',
	data: { mode : mode, id : id},
	success: function(data){
		
		$('#work_order_login').modal('show');
			$('#po_login').html(data);				
			Unloading();
		}		
	});
}

 function get_items_details(tab,product_id=null) {
 	var product_id = $('#product_id').val();
	var mode = "get_"+tab;
	if(product_id){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/work_order/',
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


function get_item_information(id=null, product_id=null, vender_id=null, type=null) {
	
	
	Loading();
 	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/work_order/',
		data: { mode : 'get_item_selected_information', product_id : product_id, vendor_id : vender_id, type : type, id : id},
		success: function(data){
		
			
			$('#table_id').val(id);
			
			var arr = jQuery.parseJSON(data);
			$('#po_req_no').val(arr.po_req_no);
			$('#po_req_date').val(arr.po_req_date);
			$('#so_no').val(arr.so_no);
			$('#so_date').val(arr.so_date);
			$('#status').val(arr.status);
			$('#vender_id').val(arr.vender_id);
			$('#vendor_po_number').val(arr.vendor_po_number);
			$('#vender_po_date').val(arr.vender_po_date);
			$('#product_type').val(arr.product_type);
			$('#item_description').val(arr.item_description);
			$('#product_id').val(arr.product_id);
			$('#order_start_date').val(arr.order_start_date);
			$('#order_delivery_date').val(arr.order_delivery_date);
			$('#ds_number').val(arr.ds_number);
			$('#bom_no').val(arr.bom_no);
			$('#bom_id').val(arr.bom_id);
			$('#order_qty').val(arr.order_qty);
			$('#remark').val(arr.remark);
			$('#vender_id').attr('data-id', arr.vendorId);
			$('#reportv').html(arr.report);
			$('#work_order_details').modal('show');
			
			
			Unloading();
		}		
	});
}  

function reports()
{
	$('#work_order_reports').modal('show');
}

function notes()
{
	$('#work_order_notes').modal('show');
}



function edit_workorder(id)
{
	
	location.href=root_domain+production_domain+'edit_workorder/'+id;
	return false;
}     

function assign_bom_costing(sp_id){
	$("#sp_id").val(sp_id)
	Loading();
		$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/work_order/',
		data: { mode : 'get_bom_costing', sp_id : sp_id},
		success: function(data){
				$("#bom_costing_id").empty().html(data);
				$("#bom_costing_id").select2({
					width : "100%",
					placeholder : "Slect BOM Costing"
				});
				$('#bom_costing_model').modal('show');
				Unloading();
			}		
		});
}

$("#bom_costing").on('submit',function(e) {
	if($("#bom_costing_id").val()=="")
	{
		toastr.warning("Select BOM Costing", "ERROR")
		$("#bom_costing_id").focus();
		return false;
	}

	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#bom_costing").valid()) {
		return false;
	}
		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/work_order/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				$('#bom_costing_model').modal('hide');
				Unloading();
				toastr.success("BOM COSTING ASSIGN SUCCESSFULLY", "SUCCESS");
				// window.location.reload();

			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
				load_po_datatable();
			$('#bom_costing').trigger('reset');	

		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});


function delete_workorder(sp_id){
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/work_order/',
			data: { mode : "delete",  eid : sp_id },
			success: function(response)
			{
					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("WORKORDER DELETE SUCCESSFULLY", "SUCCESS");
						
						Unloading();
					}else if(response.trim() == "2") {
						toastr.info("PLEASE UNREQUEST SUB PRODUCT", "INFO");
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
						Unloading();
					}			
					load_po_datatable();				
				}
			});	
	}
}



function workorder_shortclose(sp_id){
	var r= confirm(" Are you want to short close workorder ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/work_order/',
			data: { mode : "workorder_shortclose",  sp_id :  sp_id},
			success: function(response)
			{
					//console.log(response)
					if(response.trim() == "1") {
						toastr.success("WORKORDER SHORT CLOSE SUCCESSFULLY", "SUCCESS");
						
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
						Unloading();
					}			
					load_po_datatable();				
				}
			});	
	}
}



function show_workorder_image(work_order_id)
{
	Loading(true);
	 $.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/work_order/',
		data: { mode : "view_workorder_image", work_order_id : work_order_id },
		success: function(response)
		{
			$('#wo_preview_image_list').html(response);
			$("#Modal_preiview_wo_image").modal("show");
			Unloading();
		}
	});	
}



function delete_data_image(id,work_order_id){
	var r= confirm(" Are you want to delete attachment ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/work_order/',
			data: { mode : 'delete_image', id : id},
			success: function(data){
				if(data=='1'){
					toastr.success("IMAGE DELETE SUCCESSFULLY", "SUCCESS");
					view_workorder_image(work_order_id)
				}
				Unloading();			
			}		
				
		});
	}
 }

function open_priority_alert(sp_id){
	Swal.fire({
		title: 'You want to change workorder priority ?',
	  // text: "You won't be able to revert this!",
	  icon: 'question',
	  /*iconHtml: '<img src="https://picsum.photos/100/100">', // for custome image icon
	  customClass: {
	    icon: 'no-border'
	  }*/
	  showCancelButton: true,
	  confirmButtonColor: '#5cb85c',
	  cancelButtonColor: '#d9534f',
	  cancelButtonText: 'No',
	  confirmButtonText: 'Yes',
	  allowOutsideClick: false,
	  allowEscapeKey : false,
	  /*showClass: {
	    popup: 'animate__animated animate__fadeInDown'
	  },
	  hideClass: {
	    popup: 'animate__animated animate__fadeOutUp'
	  }*/
	  
	}).then((result) => {
		if (result.isConfirmed) {
			Swal.fire({
			title: 'Please Select Priority.',
		  // text: "You won't be able to revert this!",
		  icon: 'question',
		  /*iconHtml: '<img src="https://picsum.photos/100/100">', // for custome image icon
		  customClass: {
		    icon: 'no-border'
		  }*/
		  showCancelButton: true,
		  showDenyButton: true,
		  confirmButtonColor: '#ff0000',
		  denyButtonColor: '#ff8d8d',
		  cancelButtonColor: '#e5b8b8',
		  confirmButtonText: 'High',
		  denyButtonText: 'Medium',
		  cancelButtonText: 'Low',
		  allowOutsideClick: false,
		  allowEscapeKey : false,
		  
		}).then((result1) => {
			if (result1.isConfirmed) {
			    // High
			    change_workorder_priority(sp_id,'High');
			  } else if (result1.isDenied) {
			    // Medium
			    change_workorder_priority(sp_id,'Medium');
			  }else{
			  	// Low
			  	change_workorder_priority(sp_id,'Low');
			  }
		})
		}
	})
}


function change_workorder_priority(sp_id,priority){
	Loading();

	$.ajax({
			type: "POST",
			url: root_domain+production_domain+'app/work_order/',
			data: { mode : 'change_workorder_priority', sp_id : sp_id, priority:priority},
			success: function(data){
				if(data=='1'){
					toastr.success("WORKORDER PRIORITY HAS BEEN CHANGED SUCCESSFULLY", "SUCCESS");
				}else{
					toastr.warning("SOMETHING WRONG", "WARNING");
				}

				Unloading();
				load_po_datatable();
			}		
	});
}