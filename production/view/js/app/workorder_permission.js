//var datatable;
$(document).ready(function() {
	load_po_datatable();
	
// validate vendor add form on keyup and submit
/* $("#purchasecard_add").validate({
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
}); */
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
		url: root_domain+production_domain+'app/workorder_permission/',
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
			"sAjaxSource": root_domain+production_domain+'app/workorder_permission/',
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


function get_series_no(type_id){
	$.ajax({
	type: "POST",
	url: root_domain+production_domain+'app/workorder_permission/',
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
	url: root_domain+production_domain+'app/workorder_permission/',
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
		url: root_domain+production_domain+'app/workorder_permission/',
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
	url: root_domain+production_domain+'app/workorder_permission/',
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
		url: root_domain+production_domain+'app/workorder_permission/',
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
  