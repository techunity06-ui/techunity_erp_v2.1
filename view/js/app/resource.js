//var datatable;
$(document).ready(function() {
	load_po_datatable();
	fetch_employee_based_on_branch();
	
// validate vendor add form on keyup and submit
 $("#resource_add").validate({
	rules: {
		resource_name: {
			required: true			
		},
		working_hours: {
			required: true			
		},
		hours_cost:{
			required : true	
		},
		resource_value:{
			required : true	
		},
		maintance_period:{
			required : true	
		},
		vender_id:{
			required : true	
		}
	},
	messages: {
		resource_name: {
			required: "Enter Resource Name"
		},
		working_hours: {
			required: "Enter Working Hours"
		},
		hours_cost:{
			required : "Enter Hours Cost"
		},
		resource_value:{
			required : "Enter Resource Value"
		},
		maintance_period:{
			required : "Enter Maintance Period"
		},
		vender_id:{
			required : "Select Employee"	
		}
	}
}); 
});
$("#resource_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#resource_add").valid()) {
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
		url: root_domain+'app/resource/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("RESOURCE ADDED SUCCESSFULLY", "SUCCESS");
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
				toastr.success("RESOURCE UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location.reload();
			}
			$('#resource_add').trigger('reset');	
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
			"sAjaxSource": root_domain+'app/resource/',
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
	url: root_domain+'app/resource/',
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
	url: root_domain+'app/resource/',
	data: { mode : "load_invoiceno", typeid : id},
	success: function(data){
		var no = jQuery.parseJSON(data);
		$('#purchasecard_no').val(no.invoiceno);
	}
	});
}


function get_vendor_details(tab){
	var mode = "get_"+tab;
	var id = $('#table_id').val();
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/resource/',
	data: { mode : mode, id : id},
	success: function(data){
			$('#'+tab).html(data);				
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
		url: root_domain+'app/resource/',
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


function get_item_information(id=null, vender_id=null, branch_id=null, type=null) {

	Loading();
 	$.ajax({
		type: "POST",
		url: root_domain+'app/resource/',
		data: { mode : 'get_item_selected_information', vendor_id : vender_id, type : type, id : id, branch_id : branch_id},
		success: function(data){
			$('.nav-tabs a[href="#po_resource"]').click();
			$('#table_id').val(id);
			$('#eid').val(id);
			$('.add_btn').removeClass('hide');
			$('.cancel_btn').addClass('hide');
			$('#save').val('Update');
			$('#mode').val('edit');

			var arr = jQuery.parseJSON(data);
			$('#resource_name').val(arr.resource_name);
			$('#working_hours').val(arr.working_hours);
			$('#hours_cost').val(arr.hours_cost);
			$('#resource_value').val(arr.resource_value);
			$('#maintance_period').val(arr.maintance_period);
			$('#remark').val(arr.remark);
			
			$('#branch_id').empty().append(arr.branch_id);
			$("#branch_id").select2({
	         	width: '100%'
	        });
			$('#vender_id').empty().append(arr.vender_id);
			$("#vender_id").select2({
	         	width: '100%'
	        });	
	       // alert(arr.shift_type);
	       // $('#shift_type').empty().append(arr.shift_type);
			$("#shift_type").select2({
	        	width: '100%'
				});	
			
			
			Unloading();
		}		
	});
} 

function add_new_record(){
	Loading();
	$('.add_btn').addClass('hide');
	$('.cancel_btn').removeClass('hide');
	$('table > tbody  > tr').each(function(index, tr) { 
	   $(this).removeClass('active');
	});
	$('#save').html('Save');
	$('#mode').val('add');
	$('#table_id').val('');
	$('#eid').val('');
	
	$("#vender_id option:selected").prop("selected", false);
	$("#vender_id").select2({
     	width: '100%'
    });
    $("#shift_type option:selected").prop("selected", false);
	$("#shift_type").select2({
     	width: '100%'
    });
    $('.nav-tabs a[href="#po_resource"]').click();

	$('#resource_name').val('');
	$('#working_hours').val('');
	$('#hours_cost').val('');
	$('#resource_value').val('');
	$('#maintance_period').val('');
	Unloading();
}      

function fetch_employee_based_on_branch() {
	var branch_id = $('#branch_id').val();
	if(branch_id){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/resource/',
		data: { mode : 'fetch_employee_based_on_branch', branch_id : branch_id},
		success: function(data){
				var arr = jQuery.parseJSON(data);
				$('#vender_id').empty().append(arr.vendor_id);
				$("#vender_id").select2({
		         	width: '100%'
		        });	
			}		
		});
		Unloading();
	}
}