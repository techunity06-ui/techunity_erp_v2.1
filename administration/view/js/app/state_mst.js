$(document).ready(function() {
	load_state_mst_datatable();	        

// validate vendor add form on keyup and submit
$("#state_add").validate({
	rules: {
		
		countryid: {
			required: true
		},
		state_name: {
			required: true,
			minlength: 3
		}
	},
	messages: {
		countryid: {
			required: "Select Country"			
		},
		state_name: {
			required: "Enter State Name",
			minlength: "Your State Name must consist of at least 3 characters"
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditstate").validate({
	rules: {
		countryid: {
			required: true
		},
		state_name: {
			required: true,
			minlength: 3
		}

	},
	messages: {
		countryid: {
			required: "Select Country"			
		},
		state_name: {
			required: "Enter State Name",
			minlength: "Your State Name must consist of at least 3 characters"
		}
	}
});		

});
$("#state_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#state_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	
	var token=  $("#token").val();
	var country=$("#countryid").val();
	var branch_id=$("#abranch_id").val();

	var form_data = {
		state_name: $("#state_name").val(),
		gst_state_code: $("#gst_state_code").val(),
		countryid: country,
		branch_id: branch_id,
		model: $("#model").val(),	
		token:token,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/statemst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var obj=jQuery.parseJSON(response);
				response=obj.res;
			if(response.trim() == '1') {				
				toastr.success("STATE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_state_mst_datatable();
			}
			else if(response.trim() == '2') {
				toastr.success("STATE ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-state").modal("hide");
				$('#stateid').append('<option value='+obj.stateid+'>'+obj.state_name+'</option>');
				$('#stateid').select2("val",obj.stateid);
				$("#stateid").trigger('change')
				$('#state_add').trigger('reset');
				Unloading();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				$("#bs-example-modal-state").modal("hide");
				$('#state_add').trigger('reset');
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#state_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditState").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditState").valid()) {
		return false;
	}
	
	$(".error").hide();
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		countryid: $("#edit_countryid").val(),
		state_name: $("#edit_state_name").val(),
		gst_state_code: $("#edit_gst_state_code").val(),
		branch_id: $("#e_branch_id").val(), 
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/statemst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("STATE UPDATED SUCCESSFULLY", "SUCCESS");
				load_state_mst_datatable();	
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
	load_state_mst_datatable();	
}
function delete_state(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/statemst/',
			data: { mode : "delete", token :  $("#token").val(), eid : id },
			success: function(response)
			{
				
				if(response.trim() == "1") {
					toastr.success("STATE DELETE SUCCESSFULLY", "SUCCESS");
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
	Loading();
	$("#FormEditState").valid();
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/statemst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_countryid").select2("val",obj.countryid);
			$("#edit_state_name").val(obj.state_name);
			$("#edit_gst_state_code").val(obj.gst_state_code);
			$("#e_branch_id").select2("val", obj.branch_id);
			$("#FormEditState").valid();
			Unloading();
		}
	});	
}

function load_state_mst_datatable(){
	var branch_id = $('#branch_id').val();
	var countryid_search = $('#countryid_search').val();
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
					"sEmptyTable": "NO STATE ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
			"iDisplayLength": 30,
			"sAjaxSource": root_domain+administration_domain+'app/statemst/',
			"fnServerParams": function ( aoData ) {
				aoData.push( 
					{ "name": "mode", "value": "fetch" },
					{"name": "branch_id", "value": branch_id },
					{"name": "countryid_search","value":countryid_search}
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