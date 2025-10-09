$(document).ready(function() {
	load_Common_datatable();	        
// validate vendor add form on keyup and submit
$("#Common_add").validate({
	rules: {
		Common_name: {
			required: true,
		}
	},
	messages: {
		Common_name: {
			required: "Enter Common Name"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditCommon").validate({
	rules: {
	edit_Common_name: {
			required: true
		}
	},
	messages: {
		edit_Common_name: {
			required: "Enter Common Name"			
		}
	}
});		

});
$("#Common_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#Common_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var Common_name=$("#Common_name").val();
	var common_category_id=$("#common_category_id").val();

	var form_data = {
		Common_name: Common_name,
		common_category_id: common_category_id,
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/common_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {				
				toastr.success("Common ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_Common_datatable();
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
			$('#Common_add').trigger('reset');
			$("#common_category_id").select2("val",'');			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

$("#common_category_add").on('submit',function(e){
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#common_category_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		common_category_name :$("#common_category_name").val(),
		mode:'add_common_category',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/common_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var res = response.split('-');
			if(res[0].trim() == '1') {
				$("#bs-example-modal-common-category").modal("hide");
				toastr.success("COMMON CATEGORY ADDED SUCCESSFULLY", "SUCCESS");
				$('#common_category_id').append('<option value='+res[1]+'>'+res[2]+'</option>');
				$("#common_category_id").trigger('change');
				$('#common_category_id').select2("val",res[1]);
				load_Common_datatable();
				Unloading();						
			}
			else if(res[0].trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(res[0].trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#bs-example-modal-common-category").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
});

//var editReq = null;
$("#FormEditCommon").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditCommon").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		common_category_id:$("#e_common_category_id").val(),
		edit_Common_name: $("#edit_Common_name").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/common_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("COMMON UPDATED SUCCESSFULLY", "SUCCESS");
				load_Common_datatable();
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
	load_Common_datatable();
}
function delete_common(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/common_mst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("Common DELETE SUCCESSFULLY", "SUCCESS");								
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
function edit_common(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/common_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_Common_name").val(obj.common_mst_name);
			$("#e_common_category_id").select2("val", obj.common_category_id);
			Unloading();
		}
	});	
}

function add_common_category(){

	$("#bs-example-modal-common-category").modal("show");
	//$("#countryid").val($("#countryid").val());
	
}



function load_Common_datatable(){
	//var branch_id = $('#branch_id').val();

	datatable = $("#common-table").dataTable({
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
			"sAjaxSource": root_domain+'app/common_mst/',
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