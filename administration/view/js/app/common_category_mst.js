$(document).ready(function() {
	load_CommonCategory_datatable();	        
// validate vendor add form on keyup and submit
$("#CommonCategory_add").validate({
	rules: {
		CommonCategory_name: {
			required: true,
		}
	},
	messages: {
		CommonCategory_name: {
			required: "Enter Common Category Name"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditCommonCategory").validate({
	rules: {
	edit_CommonCategory_name: {
			required: true
		}
	},
	messages: {
		edit_CommonCategory_name: {
			required: "Enter Common Category Name"			
		}
	}
});		

});
$("#CommonCategory_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#CommonCategory_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var CommonCategory_name=$("#CommonCategory_name").val();

	var form_data = {
		CommonCategory_name: CommonCategory_name,
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/common_category_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {				
				toastr.success("Common Category ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_CommonCategory_datatable();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#CommonCategory_add').trigger('reset');
			$("#tax_id").select2("val",'');			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditCommonCategory").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditCommonCategory").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		edit_CommonCategory_name: $("#edit_CommonCategory_name").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/common_category_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("COMMON CATEGORY UPDATED SUCCESSFULLY", "SUCCESS");
				load_CommonCategory_datatable();
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#ModalEditAccount").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_reload()
{
	load_CommonCategory_datatable();
}
function delete_common_category(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/common_category_mst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("Common Category DELETE SUCCESSFULLY", "SUCCESS");								
						delete_reload();
						Unloading();
					}
					else if(response.trim() == "0") {
						$("#tax_id").select2("val",'');			
		
					toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function edit_common_category(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/common_category_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_CommonCategory_name").val(obj.common_category_name);
			Unloading();
		}
	});	
}

function load_CommonCategory_datatable(){
	//var branch_id = $('#branch_id').val();

	datatable = $("#common-category-table").dataTable({
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
			"sAjaxSource": root_domain +administration_domain+'app/common_category_mst/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" }					
				);
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted
}