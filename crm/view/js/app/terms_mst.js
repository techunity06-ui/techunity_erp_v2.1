$(document).ready(function() {      
	load_terms_mst_datatable();

	// validate vendor add form on keyup and submit
	$("#terms_add").validate({
		rules: {
			term_name: {
				required: true
			},
			term_priority: {
				required: true
			},
			term_category: {
				required: true
			},
			terms_details: {
				required: true
			},
		},
		messages: {
			term_name: {
				required: "Enter term Name"			
			},
			term_priority: {
				required: "Enter term Priority"	
			},
			term_category: {
				required: "Select Priority Category"	
			},
			terms_details: {
				required: "Enter Details"	
			},
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditunit").validate({
		rules: {
		edit_g_name: {
				required: true
			}
		},
		messages: {
			edit_g_name: {
				required: "Enter Product Group Name"			
			}
		}
	});		
});
$("#terms_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#terms_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);
	var token	=  $("#token").val();	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/termsmst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(responsevalue)
		{
			if(responsevalue.trim() == '1') {
				
				toastr.success("TERMS & CONDITION ADDED SUCCESSFULLY", "SUCCESS")
				$('#terms_add').trigger('reset');
				$("#term_category").select2("val","");				
				Unloading();
				window.location=root_domain + crm_domain +'terms_list';
			}
			if(responsevalue.trim() == 'update') {
				
				toastr.success("TERMS & CONDITION UPDATED SUCCESSFULLY", "SUCCESS")
				Unloading();
				setTimeout(function(){ 
					window.location=root_domain + crm_domain +'terms_list';
				}, 500);
			}
			else if(responsevalue.trim() == '0') {
				toastr.error("something wrong", "ERROR")
				$('#product_add').trigger('reset');	
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$('#product_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == '3') {
				toastr.success("TERMS & CONDITION ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_product_modal").modal("hide");
				$('#product_id').append('<option value='+data.product_id+'>'+data.product_name+'</option>'); 
				$('#product_id').select2("val",data.product_id);
				$("#product_id").trigger('change');
				Unloading();
			}
			
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
		e_a_name: $("#e_a_name").val(),
		e_a_priority: $("#e_a_priority").val(),
		e_a_detail: $("#e_a_detail").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/termsmst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("TERMS & CONDITION UPDATED SUCCESSFULLY", "SUCCESS");
				load_terms_mst_datatable();
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
	load_terms_mst_datatable();
}
function delete_data(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/termsmst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("TERMS & CONDITION DELETED SUCCESSFULLY", "SUCCESS");
						delete_reload();
						Unloading();
					}
					else if(response.trim() == "0") {
						
					toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function edit_data(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/termsmst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(obj.an_id);
			$("#e_a_name").val(obj.an_name);
			$("#e_a_priority").val(obj.an_priority);
			$("#e_a_detail").val(obj.an_detail);
			Unloading();
		}
	});	
}

function load_terms_mst_datatable(){
		var branch_id = $("#branch_id").val();
		$("#dynamic-table").dataTable({
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
			"sAjaxSource": root_domain + crm_domain +'app/termsmst/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },
					{ "name": "branch_id", "value": branch_id } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
