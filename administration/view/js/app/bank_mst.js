var datatable;
$(document).ready(function() {
	load_bank_datatable();

// validate the form when it is submitted        
$("#bank_add").validate({
	rules: {
		bank_name: {
			required: true
		}
	},
	messages: {
		bank_name: {
			required: "Enter Bank Name"			
		}
	}
}); 
// validate edit form on keyup and submit
$("#FormEditBank").validate({
	rules: {
		edit_bank_name: {
			required: true
		}
	},
	messages: {
		edit_bank_name: {
			required: "Enter Bank Name"			
		}
	}
});		

});
$("#bank_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#bank_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		bank_name: $("#bank_name").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/bank_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var response = JSON.parse(response);
			var responsevalue=response.res;
			if(responsevalue.trim() == '1') {
				toastr.success("BANK ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_bank_datatable();
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("BANK ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-product-category-modal").modal("hide");
				//$('#branch_id').append('<option value='+response.branch_id+'>'+response.branch_name+'</option>');
				$('#bank_name').val(response.branch_id);
				//$("#branch_id").trigger('change');
				$('#bank_add').trigger('reset');
				$('#addprocat').hide();
				Unloading();
			}
			else if(responsevalue.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(responsevalue.trim() == '-1'){
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			$('#bank_add').trigger('reset');		
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditBank").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditBank").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid: $("#edit_id").val(),
		bank_name: $("#edit_bank_name").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/bank_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("BANK UPDATED SUCCESSFULLY", "SUCCESS");
				load_bank_datatable();
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(response.trim() == '-1'){
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			$("#ModalEditBank").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_bank(id) 
{
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/bank_mst/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("BANK DELETE SUCCESSFULLY", "SUCCESS");		
					load_bank_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}
}
function edit_bank(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/bank_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditBank").modal("show");
			$("#edit_id").val(id);				
			$("#edit_bank_name").val(obj.bank_name);
			Unloading();
		}
	});	
}
function load_bank_datatable(){
	datatable = $("#bank-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/bank_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}