var datatable;
$(document).ready(function() {
		datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO Content ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/contentmst/',
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
	// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#content_add").validate({
	rules: {
		content_name: {
			required: true
		}
	},
	messages: {
		content_name: {
			required: "Enter Content"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditcontent").validate({
	rules: {
	edit_content_name: {
			required: true
		}
	},
	messages: {
		edit_content_name: {
			required: "Enter Content"			
		}
	}
});		

});
$("#content_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#content_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var token=  $("#token").val();
	var content_name=$("#content_name").val();
	var form_data = {
		content_name: content_name,
		token:token,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/contentmst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {				
				toastr.success("Content ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
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
			$('#content_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditcontent").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditcontent").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		content_name: $("#edit_content_name").val(),
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/contentmst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("Content UPDATED SUCCESSFULLY", "SUCCESS");
				datatable.fnReloadAjax();
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
	datatable.fnReloadAjax();
}
function delete_content(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/contentmst/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("Content DELETE SUCCESSFULLY", "SUCCESS");
								
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
function edit_test(id)
{
		Loading(true);
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/contentmst/',
			data: { mode : "preedit", id : id },
			success: function(response)
			{
				//console.log(response);
				var obj = jQuery.parseJSON(response);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);				
				$("#edit_content_name").val(obj.content_name);
				Unloading();
			}
		});	
	}