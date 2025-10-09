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
					"sProcessing": "<img src='"+root_domain + crm_domain +"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO Supply Place ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + crm_domain +'app/place_supply/',
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
$("#supply").validate({
	rules: {
		place_supply: {
			required: true
			
		}
	},
	messages: {
		place_supply: {
			required: "Enter Supply Place"
			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditsupply").validate({
	rules: {
		edit_place_supply: {
			required: true
			
		}		

	},
	messages: {
		edit_place_supply: {
			required: "Enter Supply Place"
			
		}
	}
});		

});
$("#supply").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#supply").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var token=  $("#token").val();		
	var form_data = {
		place_supply: $("#place_supply").val(),
		token:token,
		mode:$("#mode").val(),
		model:$("#model").val(),
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/place_supply/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				toastr.success("Supply Place ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
				
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("Supply Place ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-place-modal").modal("hide");
				$('#destination').append('<option value='+data.supply_place_id+'>'+data.place_supply+'</option>');
				$('#destination').val(data.supply_place_id);
				$("#destination").trigger('change')
				$('#supply').trigger('reset');
				Unloading();
			}
			else if(responsevalue.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#model_addbank").modal("hide");
				$('#supply').trigger('reset');
				Unloading();				
			}
			$('#supply').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditsupply").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditsupply").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
	    place_supply: $("#edit_place_supply").val(),
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/place_supply/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			
			if(response.trim() == '1') {
				toastr.success("Supply Place UPDATED SUCCESSFULLY", "SUCCESS");
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
function delete_bank(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/place_supply/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("Supply Place DELETE SUCCESSFULLY", "SUCCESS");
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
			url: root_domain + crm_domain +'app/place_supply/',
			data: { mode : "preedit", id : id },
			success: function(response)
			{
				//console.log(response);
				var obj = jQuery.parseJSON(response);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);				
				$("#edit_place_supply").val(obj.place_supply);
				Unloading();
			}
		});	
	}