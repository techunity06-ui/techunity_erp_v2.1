//var datatable;
$(document).ready(function() {
	load_po_datatable();
	
	// validate vendor add form on keyup and submit
	$("#resource_add").validate({
		rules: {
			
			resource_id:{
				required : true	
			}
		},
		messages: {
			resource_id:{
				required : "Select Resource Name"
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
		url: root_domain+planning_list+'app/resource_allocate_list/',
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
			"sAjaxSource": root_domain+production_domain+'app/resource_allocate_list/',
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


function get_vendor_details(tab){
	var mode = "get_"+tab;
	var id = $('#table_id').val();
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+production_domain+'app/resource_allocate_list/',
	data: { mode : mode, id : id},
	success: function(data){
			$('#'+tab).html(data);				
			Unloading();
		}		
	});
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


function get_item_information(id=null) {
	Loading();
 	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/resource_allocate_list/',
		data: { mode : 'get_item_selected_information',  id : id},
		success: function(data){
			$('.nav-tabs a[href="#po_resource"]').click();
			$('#table_id').val(id);
			$('#eid').val(id);
			$('.add_btn').removeClass('hide');
			$('.cancel_btn').addClass('hide');
			$('#save').val('Update');
			$('#mode').val('edit');

			var arr = jQuery.parseJSON(data);
			$('#product_name').val(arr.product_name);
			$('#work_order_no').val(arr.work_order_no);
			$('#qty').val(arr.qty);
			$('#time_per_qty').val(arr.time_per_qty);
			$('#total_time').val(arr.total_time);
			$('#resource_id').empty().append(arr.resource_id);
			$("#resource_id").select2({
	         	width: '100%'
	        });	
			
			Unloading();
		}		
	});
}     