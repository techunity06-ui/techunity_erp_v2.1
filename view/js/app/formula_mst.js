$(document).ready(function() {
	load_formula_datatable();	

// validate vendor add form on keyup and submit
$("#formula_add").validate({
	rules: {
		tax_id: {
			required: true
			
		}
	},
	messages: {
		tax_id: {
			required: "Select Tax"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditformula").validate({
	rules: {
		tax_id: {
			required: true
		}
	},
	messages: {
		tax_id: {
			required: "Select Tax"			
		}
	}
});		

});
$("#formula_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#formula_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var token=  $("#token").val();
	var tax=$("#tax_id").val();
    var tax_type =$("#tax_type").val();
    var branch_id = $("#abranch_id").val();

	var form_data = {
		tax_per_id: $('#tax_per_id').val(),
		tax_cat: $('#tax_cat').val(),
        tax_type: tax_type,
        branch_id: branch_id,
		tax_id: tax,
		token:token,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/formulamst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {				
				toastr.success("formula ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location.href = root_domain+"formula_list";						
				load_formula_datatable();
				$("#tax_id").select2("val",'');			
	
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
			$('#formula_add').trigger('reset');
			$("#tax_id").select2("val",'');			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditformula").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditformula").valid()) {
		return false;
	}		
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		tax_per_id: $('#edit_tax_per_id').val(),
		tax_cat: $('#edit_tax_cat').val(),
		formula_name: $("#edit_formula_name").val(),
		tax_id: $("#edit_tax_id").val(),
        tax_type: $("#edit_tax_type").val(),
        branch_id: $("#e_branch_id").val(), 
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/formulamst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("formula UPDATED SUCCESSFULLY", "SUCCESS");
				$("#tax_id").select2("val",'');			
				window.location.href = root_domain+"formula_list";						
				load_formula_datatable();
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
	load_formula_datatable();
}
function delete_formula(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/formulamst/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("formula DELETE SUCCESSFULLY", "SUCCESS");
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
function edit_test(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/formulamst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_tax_per_id").select2("val",obj.tax_per_id);				
			$("#edit_tax_cat").select2("val",obj.tax_cat);
            $('#edit_tax_type').select2("val",obj.tax_type);
			$("#edit_tax_id").select2("val",obj.tax_id.split(","));
			$("#e_branch_id").select2("val", obj.branch_id);
			Unloading();
		}
	});	
}

function load_formula_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#dynamic-table").dataTable({
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
			"sAjaxSource": root_domain+'app/formulamst/',
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