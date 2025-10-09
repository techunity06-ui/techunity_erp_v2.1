$(document).ready(function() {
	load_Narration_datatable();	        
// validate vendor add form on keyup and submit
$("#Narration_add").validate({
	rules: {
		Narration_name: {
			required: true,
		}
	},
	messages: {
		Narration_name: {
			required: "Enter Narration Name"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditNarration").validate({
	rules: {
	edit_Narration_name: {
			required: true
		}
	},
	messages: {
		edit_Narration_name: {
			required: "Enter Narration Name"			
		}
	}
});		

});
$("#Narration_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#Narration_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var common_mst_id=$("#common_mst_id").val();
	var Narration_name=$("#Narration_name").val();

	var form_data = {
		common_mst_id: common_mst_id,
		Narration_name: Narration_name,
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/narration_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {				
				toastr.success("Narration ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_Narration_datatable();
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
			$('#Narration_name').val('').change();
			$("#common_mst_id").select2("val",'');			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditNarration").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditNarration").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		e_common_mst_id: $("#e_common_mst_id").val(),
		edit_Narration_name: $("#edit_Narration_name").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/narration_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("Narration UPDATED SUCCESSFULLY", "SUCCESS");
				load_Narration_datatable();
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
	load_Narration_datatable();
}
function delete_narration(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/narration_mst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("Narration DELETE SUCCESSFULLY", "SUCCESS");								
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
function edit_narration(id)
{
	$("#FormEditNarration").valid();
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/narration_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);	
			$("#e_common_mst_id").select2("val", obj.narration_voucher_id);	
			$("#edit_Narration_name").val(obj.narration_detail);
			Unloading();
		}
	});	
}

function load_Narration_datatable(){
	//var branch_id = $('#branch_id').val();

	datatable = $("#narration-table").dataTable({
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
			"sAjaxSource": root_domain+administration_domain+'app/narration_mst/',
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