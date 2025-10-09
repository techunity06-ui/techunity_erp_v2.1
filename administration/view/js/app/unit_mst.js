$(document).ready(function() {
	load_unit_datatable();	        
// validate vendor add form on keyup and submit
$("#unit_add").validate({
	rules: {
		unit_name: {
			required: true
		}
	},
	messages: {
		unit_name: {
			required: "Enter Unit Name"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditunit").validate({
	rules: {
	edit_unit_name: {
			required: true
		}
	},
	messages: {
		edit_unit_name: {
			required: "Enter Unit Name"			
		}
	}
});		

});
$("#unit_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#unit_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var uname=$("#unit_name").val();
	var branch_id=$("#abranch_id").val();
	var unit_code=$("#unit_code").val();

	var form_data = {
		unit_name: uname,
		branch_id: branch_id,
		unit_code: unit_code,
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/unit_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {				
				toastr.success("UNIT ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_unit_datatable();
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
			$('#unit_add').trigger('reset');
			$("#tax_id").select2("val",'');			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditunit").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditunit").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		unit_name: $("#edit_unit_name").val(),
		unit_code: $("#edit_unit_code").val(),
		tax_id: $("#edit_tax_id").val(),
		branch_id: $("#e_branch_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/unit_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("UNIT UPDATED SUCCESSFULLY", "SUCCESS");
				load_unit_datatable();
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
	load_unit_datatable();
}
function delete_unit(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/unit_mst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("UNIT DELETE SUCCESSFULLY", "SUCCESS");
						$("#tax_id").select2("val",'');			
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
function edit_unit(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/unit_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_unit_name").val(obj.unit_name);
			$("#edit_unit_code").val(obj.unit_code);
			$("#e_branch_id").select2("val", obj.branch_id);
			$("#FormEditunit").valid()
			Unloading();
		}
	});	
}

function load_unit_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#unit-table").dataTable({
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
			"sAjaxSource": root_domain+administration_domain+'app/unit_mst/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{"name": "branch_id", "value": branch_id }
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